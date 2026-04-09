<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // Lấy giỏ hàng của user hiện tại
    public function index()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        // Lấy toàn bộ item + product
        $items = $cart->items()->with('product')->get();

        // Format dữ liệu
        $cartItems = $items->map(function ($item) {
            // Tổng số lượng tồn kho trong bảng product_store (chỉ tính status = 1)
            $stock = \App\Models\ProductStore::where('product_id', $item->product_id)
                ->where('status', 1)
                ->sum('qty');

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name ?? '',
                'thumbnail' => $item->product->thumbnail ?? '',
                'price' => $item->price,
                'quantity' => $item->quantity,
                'attributes' => $item->attributes,
                'stock' => $stock, // ✅ thêm tồn kho
            ];
        });

        return response()->json([
            'success' => true,
            'cart_id' => $cart->id,
            'cart_items' => $cartItems,
        ]);
    }



    // Thêm sản phẩm vào giỏ hàng
// CartController.php
    public function merge(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        $cartItems = $request->input('cart_items', []);
        if (empty($cartItems)) {
            return response()->json([
                'success' => true,
                'message' => 'Không có sản phẩm nào để merge'
            ]);
        }

        // ✅ Đảm bảo user có giỏ hàng
        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        foreach ($cartItems as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'] ?? 1;

            $product = Product::find($productId);
            if (!$product)
                continue;

            // ✅ Tìm sản phẩm đã có trong cart_items
            $existing = $cart->items()->where('product_id', $productId)->first();

            if ($existing) {
                // Cộng dồn số lượng
                $existing->quantity += $quantity;
                $existing->save();
            } else {
                // ✅ Thêm mới vào cart_items
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $item['price'] ?? $product->price_buy,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã merge cart local lên server thành công',
            'cart' => $cart->load('items.product'),
        ]);
    }



    public function add(Request $request)
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:product,id',
                'quantity' => 'nullable|integer|min:1',
                'attributes' => 'nullable|array' // nhận attributes từ client
            ]);

            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
            }

            $cart = Cart::firstOrCreate(['user_id' => $user->id]);
            $product = Product::findOrFail($request->product_id);

            $attributes = $request->input('attributes', []);
            $quantity = $request->input('quantity', 1);

            // Lấy tất cả items của cart và so sánh mảng attributes trực tiếp
            $item = $cart->items->first(function ($i) use ($product, $attributes) {
                return $i->product_id == $product->id && $i->attributes == $attributes;
            });

            if ($item) {
                $item->quantity += $quantity;
                $item->save();
            } else {
                $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price_buy,
                    'attributes' => $attributes,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm vào giỏ hàng thành công 🛒',
                'cart' => $cart->load('items.product')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    //lấy số lượng sản phẩm trong giỏ hàng của user hiện tại
    public function count()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0]);
        }

        $cart = Cart::where('user_id', $user->id)->first();
        if (!$cart) {
            return response()->json(['count' => 0]);
        }

        $count = $cart->items()->sum('quantity');

        return response()->json(['count' => $count]);
    }

    // Cập nhật số lượng
    public function updateQuantity(Request $request, $itemId)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
            }

            $validated = $request->validate([
                'quantity' => 'required|integer|min:1',
            ]);

            $item = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $user->id))
                ->findOrFail($itemId);

            $item->update(['quantity' => $validated['quantity']]);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật số lượng thành công ✅',
                'item' => $item,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi: ' . $e->getMessage(),
            ], 500);
        }
    }
    // Cập nhật giỏ hàng
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $user = Auth::user();
        $item = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->findOrFail($itemId);
        $item->quantity = $request->quantity;
        $item->save();

        return response()->json(['success' => true, 'message' => 'Cart updated']);
    }

    // Xóa item
    public function remove($itemId)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $item = CartItem::whereHas('cart', fn($q) => $q->where('user_id', $user->id))
            ->findOrFail($itemId);

        $cart = $item->cart; // Lấy cart của item
        $item->delete(); // Xóa item

        // Nếu giỏ hàng không còn item nào -> xóa luôn cart
        if ($cart && $cart->items()->count() === 0) {
            $cart->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!'
        ]);
    }

    public function clear()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Chưa đăng nhập!'], 401);
        }

        $cart = Cart::where('user_id', $user->id)->first();
        if ($cart) {
            $cart->items()->delete();
            $cart->delete();
        }


        return response()->json(['success' => true, 'message' => 'Đã xóa toàn bộ giỏ hàng']);
    }

}
