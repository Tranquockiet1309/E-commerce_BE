# E-commerce Backend API (TranQuocKiet_2123110364)

Đây là hệ thống Backend API cho dự án E-commerce chuyên nghiệp, được phát triển trên nền tảng framework Laravel. Hệ thống cung cấp đầy đủ các RESTful APIs phục vụ cho ứng dụng frontend (SPA như ReactJS, VueJS) hoặc ứng dụng di động (Mobile App).

## 🚀 Công nghệ sử dụng
- **Framework:** Laravel 12.x (yêu cầu PHP ^8.2)
- **Cơ sở dữ liệu:** MySQL / PostgreSQL / SQLite
- **Xác thực:** Laravel Sanctum (Xác thực với Token - Stateless Authentication)
- **Tích hợp thanh toán:** Ví điện tử MOMO (Momo API)

## 📦 Chức năng chính
- **Xác thực Người dùng (Authentication):** Đăng nhập, đăng ký, đăng xuất, đổi mật khẩu và cập nhật thông tin profile (sử dụng Sanctum middleware).
- **Quản lý Sản phẩm (Products):** Xem toàn bộ sản phẩm, chi tiết sản phẩm, lọc sản phẩm (thuộc tính), sản phẩm mới, sản phẩm khuyến mãi, lấy sản phẩm theo danh mục.
- **Giỏ hàng (Cart):** Thêm, sửa, xóa, lấy số lượng, làm trống và gộp giỏ hàng dành cho user đã đăng nhập.
- **Đơn hàng (Orders):** Đặt hàng, theo dõi lịch sử đơn hàng (`/api/my-orders`), hủy đơn.
- **Thanh toán:** Tích hợp quy trình thanh toán MOMO (`/api/payment/momo` & IPN Handler).
- **Nội dung Website (CMS):** Bài viết (Posts), chủ đề bài viết (Topics), Banners quảng cáo, Menu động, Thông tin cửa hàng (Contact & Settings).
- **Quản lý Cửa hàng/Kho (Store):** Import dữ liệu vào kho (`/api/productstore/import`), Quản lý sale (`ProductSale`).

## 🛠 Hướng dẫn Cài đặt & Khởi động dự án

### 1. Yêu cầu hệ thống
- **PHP** >= 8.2
- **Composer** (Quản lý các gói của PHP)
- **Node.js & npm** (để quản lý / build frontend assets bằng Vite nếu cần)
- Phầm mềm CSDL: MySQL, MariaDB, hoặc SQLite

### 2. Trình tự cài đặt

**Bước 1: Tải dự án và di chuyển vào thư mục code**
```bash
git clone <repository-url>
cd backend_cdtt
```

**Bước 2: Cài đặt các thư viện (Dependencies)**
```bash
composer install
npm install
```

**Bước 3: Cấu hình môi trường (.env)**
Nhân bản cấu hình mẫu để thiết lập các biến môi trường:
```bash
cp .env.example .env
```
Mở file `.env` và cập nhật thông tin về kết nối Cơ sở dữ liệu:
```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ten_database_cua_ban
DB_USERNAME=root
DB_PASSWORD=
```

*(Lưu ý: Bạn cũng cần cấu hình các tham số về Email và thông tin kết nối MOMO API nếu sử dụng tính năng thực tế).*

**Bước 4: Thiết lập App Key**
```bash
php artisan key:generate
```

**Bước 5: Chạy Migration & Khởi tạo dữ liệu (Seeder)**
```bash
php artisan migrate --seed
```

**Bước 6: Khởi động Server**
```bash
# Lệnh chạy môi trường phát triển (bao gồm PHP Server & Vite)
composer run dev
```
HOẶC chạy Backend độc lập:
```bash
php artisan serve
```
Mặc định hệ thống sẽ khởi chạy tại: `http://localhost:8000`

---
## 🗺 Cấu trúc Endpoints Cơ bản
Hệ thống kết nối theo chuẩn RESTful. Phía Client dùng prefix `/api` cho các request.

- **Quản lý Auth:** 
  - `POST /api/login` - Đăng nhập
  - `POST /api/register` - Đăng ký
  - `POST /api/logout` - Đăng xuất (Yêu cầu Token)
- **Tương tác cốt lõi:**
  - `GET /api/product-all` - Danh sách sản phẩm
  - `GET /api/categories` - Danh sách danh mục
  - `GET /api/cart` - Quản lý giỏ hàng hiện tại (Yêu cầu Token)
  - `POST /api/payment/momo` - Giao dịch với ví Momo
- Chi tiết đầy đủ xem trực tiếp trong file: `routes/api.php` và các Router Controllers kết nối (Ví dụ: `app/Http/Controllers`).

## ✍🏻 Tác giả & Giấy phép
Dự án được xây dựng và quản lý bởi TranQuocKiet_2123110364.
Được phát triển dựa theo Laravel framework (Giấy phép MIT).
