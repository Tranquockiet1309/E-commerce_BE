<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Xác nhận đơn hàng</title>
</head>

<body>
    <h2>🎉 Cảm ơn {{ $order->name }} đã đặt hàng tại NetTech Pro!</h2>

    <p>Đơn hàng của bạn (#{{ $order->id }}) đã được ghi nhận.</p>
    <p>Chúng tôi sẽ sớm liên hệ qua số điện thoại <strong>{{ $order->phone }}</strong> để xác nhận giao hàng.</p>

    <h3>Thông tin giao hàng:</h3>
    <ul>
        <li><strong>Tên:</strong> {{ $order->name }}</li>
        <li><strong>Email:</strong> {{ $order->email }}</li>
        <li><strong>Địa chỉ:</strong> {{ $order->address }}</li>
        <li><strong>Ghi chú:</strong> {{ $order->note ?? 'Không có' }}</li>
    </ul>

    <p style="margin-top: 20px;">Trân trọng,<br>Đội ngũ NetTech Pro 💻</p>
</body>

</html>
