<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;


class OrderController extends Controller
{
    /**
     * 📋 Lấy danh sách đơn hàng (có lọc & tìm kiếm)
     */
    public function index(Request $request)
    {
        $query = Order::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if (($status = $request->input('status')) !== null && $status !== '') {
            $query->where('status', $status);
        }

        $query->orderBy('created_at', 'desc');
        $perPage = $request->input('per_page', 10);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }
    public function cancel($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }

        if ($order->status != 2) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể hủy đơn đang xử lý.'], 400);
        }

        $order->status = 0;
        $order->updated_at = now();
        $order->save();

        return response()->json(['success' => true, 'message' => 'Đơn hàng đã được hủy thành công.']);
    }

    /**
     * 🛒 Tạo đơn hàng mới (bao gồm chi tiết giỏ hàng)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'note' => 'nullable|string',
            'cart_items' => 'required|array',
            'cart_items.*.product_id' => 'required|integer',
            'cart_items.*.price' => 'required|numeric',
            'cart_items.*.qty' => 'required|integer|min:1',
            'cart_items.*.discount' => 'nullable|numeric',
            'cart_items.*.attributes' => 'nullable',
        ]);

        try {
            DB::beginTransaction();

            // 🧾 Tạo đơn hàng
            $order = Order::create([
                'user_id' => $validated['user_id'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'],
                'note' => $validated['note'] ?? null,
                'created_by' => $validated['user_id'],
                'status' => 2, // 2 đang xử lí
            ]);

            // 🧩 Tạo chi tiết đơn hàng từ payload
            foreach ($validated['cart_items'] as $item) {
                // Lấy sản phẩm từ DB
                $product = Product::find($item['product_id']);
                if (!$product) {
                    throw new \Exception("Sản phẩm ID {$item['product_id']} không tồn tại");
                }

                // Lấy kho của sản phẩm (giả sử store mặc định là store_id = 1, chỉ lấy kho active)
                $productStore = DB::table('product_store')
                    ->where('product_id', $item['product_id'])
                    ->where('status', 1)
                    ->orderBy('id', 'asc') // nếu có nhiều kho, ưu tiên kho đầu tiên
                    ->first();

                if (!$productStore) {
                    throw new \Exception("Sản phẩm {$item['product_id']} không có kho tương ứng");
                }

                // Kiểm tra tồn kho
                if ($productStore->qty < $item['qty']) {
                    throw new \Exception("Sản phẩm {$product->name} chỉ còn {$productStore->qty} trong kho");
                }

                // Trừ kho
                DB::table('product_store')
                    ->where('id', $productStore->id)
                    ->update([
                        'qty' => $productStore->qty - $item['qty'],
                        'updated_at' => now(),
                        'updated_by' => $order->user_id, // hoặc user hiện tại
                    ]);

                // Tạo chi tiết đơn hàng
                $attributes = [];
                if (isset($item['attributes'])) {
                    // Chuyển object lồng thành array PHP chuẩn
                    $attributes = json_decode(json_encode($item['attributes']), true);
                }

                OrderDetail::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'price' => $item['price'],
                    'qty' => $item['qty'],
                    'amount' => $item['price'] * $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'attributes' => $attributes, // Laravel tự convert array -> JSON
                ]);


            }

            // 🧹 Xóa giỏ hàng thực tế trong DB (nếu tồn tại)
            $cart = Cart::where('user_id', $validated['user_id'])->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->delete();
            }

            DB::commit();

            // 📧 Gửi mail xác nhận đơn hàng
            Mail::send('emails.order_success', ['order' => $order], function ($message) use ($order) {
                $message->to($order->email)
                    ->subject('Xác nhận đơn hàng #' . $order->id);
            });

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công',
                'data' => $order->load('details')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * 🔍 Xem chi tiết 1 đơn hàng
     */
    public function show($id)
    {
        $order = Order::with('details.product')->find($id);

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * ✏️ Cập nhật đơn hàng
     */
    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
            'note' => 'nullable|string',
            'status' => 'sometimes|integer'
        ]);

        $order->update(array_merge($validated, [
            'updated_by' => $request->user_id ?? 1
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật đơn hàng thành công',
            'data' => $order
        ]);
    }

    /**
     * 🗑️ Xóa đơn hàng
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        $order->delete();
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa đơn hàng thành công'
        ]);
    }




    public function myOrders()
    {


        $user = Auth::user();
        \Log::info('User in myOrders', ['user' => $user]);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng chưa đăng nhập',
            ], 401);
        }

        $orders = Order::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders, // danh sách đơn hàng của user
        ]);
    }






}
