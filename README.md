# Quản Lý Du Lịch

Hệ thống quản lý du lịch xây dựng trên **Laravel 11** + **Tailwind CSS**.

## Tính năng

- 🗺️ Quản lý điểm đến (Destinations)
- 🏝️ Quản lý tour du lịch (Tours) với lọc theo điểm đến, loại tour, giá, số ngày
- 📅 Đặt tour trực tuyến (Bookings) với mã đặt tour tự động
- ⭐ Đánh giá tour (Reviews)
- 👤 Xác thực người dùng (Laravel Breeze)
- 🔧 Trang quản trị Admin

## Cài đặt

```bash
# 1. Cài dependencies
composer install
npm install

# 2. Cấu hình môi trường
cp .env.example .env
php artisan key:generate

# 3. Cài đặt database (SQLite mặc định)
php artisan migrate --seed

# 4. Build frontend
npm run build

# 5. Chạy server
php artisan serve
```

## Tài khoản mặc định

| Role  | Email                     | Password |
|-------|---------------------------|----------|
| Admin | admin@quanlydulich.vn     | password |

## Cấu trúc

```
app/
  Models/         - Destination, Tour, Booking, Review, User
  Http/Controllers/
    Admin/        - Quản lý tours, điểm đến, đặt tour (admin)
    TourController, BookingController, DestinationController
database/
  migrations/     - Bảng destinations, tours, bookings, reviews
  seeders/        - Dữ liệu mẫu 10 điểm đến + 6 tour Việt Nam
resources/views/
  welcome.blade.php   - Trang chủ
  tours/              - Danh sách & chi tiết tour
  bookings/           - Đặt tour & lịch sử
  admin/              - Giao diện quản trị
```
