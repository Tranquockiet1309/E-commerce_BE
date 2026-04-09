<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Mail;
use App\Models\Product;
class MomoController extends Controller
{
    public function createPayment(Request $request)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        $partnerCode = "MOMO";
        $accessKey = "F8BBA842ECF85";
        $secretKey = "K951B6PE1waDMi640xX08PD3vg6EkVlz";


        $orderId = time();
        $orderInfo = "Thanh toán đơn hàng #$orderId";
        $amount = $request->amount;
        $redirectUrl = "http://localhost:3000/payment/success";
        $ipnUrl = "http://127.0.0.1:8000/api/payment/momo/ipn";

        // ✅ Gắn extraData TRƯỚC khi tạo signature
        $extraData = base64_encode(json_encode([
            'user_id' => $request->user_id,
            'form' => $request->form,
            'cart_items' => $request->cart_items,
        ]));

        $rawHash = "accessKey=$accessKey&amount=$amount&extraData=$extraData&ipnUrl=$ipnUrl&orderId=$orderId&orderInfo=$orderInfo&partnerCode=$partnerCode&redirectUrl=$redirectUrl&requestId=$orderId&requestType=captureWallet";
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => "MoMo Test",
            'storeId' => "MomoTestStore",
            'requestId' => $orderId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => 'captureWallet',
            'signature' => $signature
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return response()->json(json_decode($result, true));
    }

    public function ipn(Request $request)
    {
        Log::info('📩 IPN MoMo nhận được:', $request->all());

        $data = $request->all();

        // ✅ Kiểm tra trạng thái thanh toán
        if (!isset($data['resultCode']) || $data['resultCode'] != 0) {
            Log::warning('❌ Thanh toán thất bại hoặc bị hủy', $data);
            return response()->json(['message' => 'Payment failed or cancelled'], 200);
        }

        // ✅ Nếu thanh toán thành công
        try {
            DB::beginTransaction();

            // Giả định bạn lưu `user_id` và `cart_items` trong extraData (frontend gửi vào khi gọi /momo)
            $extraData = json_decode(base64_decode($data['extraData'] ?? ''), true);
            if (!$extraData || !isset($extraData['user_id']) || !isset($extraData['cart_items'])) {
                Log::error('Thiếu extraData từ MoMo', $extraData);
                return response()->json(['message' => 'Missing user/cart info'], 400);
            }

            $user_id = $extraData['user_id'];
            $cart_items = $extraData['cart_items'];
            $form = $extraData['form'] ?? [];

            // 🧾 Tạo đơn hàng (trạng thái: đã thanh toán MoMo)
            $order = Order::create([
                'user_id' => $user_id,
                'name' => $form['name'] ?? 'Khách hàng MoMo',
                'email' => $form['email'] ?? 'noemail@example.com',
                'phone' => $form['phone'] ?? '',
                'address' => $form['address'] ?? '',
                'note' => $form['note'] ?? '',
                'status' => 3, // 3 = Đã thanh toán
                'created_by' => $user_id,
            ]);

            // 🧩 Lưu chi tiết đơn hàng
            foreach ($cart_items as $item) {
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

            // 🧹 Xóa giỏ hàng của user
            $cart = Cart::where('user_id', $user_id)->first();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)->delete();
                $cart->delete();
            }

            DB::commit();

            // 📧 Gửi mail xác nhận đơn hàng
            Mail::send('emails.order_success', ['order' => $order], function ($message) use ($order) {
                $message->to($order->email)->subject('Xác nhận đơn hàng MoMo #' . $order->id);
            });

            Log::info('✅ Đã tạo đơn hàng sau khi thanh toán MoMo thành công', ['order_id' => $order->id]);

            return response()->json([
                'message' => 'Order created successfully after MoMo payment',
                'order_id' => $order->id,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Lỗi khi xử lý IPN MoMo', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error processing payment', 'error' => $e->getMessage()], 500);
        }
    }

}
