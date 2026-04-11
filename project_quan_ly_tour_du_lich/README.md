# Hệ thống Quản lý Tour Du lịch

Hệ thống quản lý tour du lịch được xây dựng bằng PHP thuần với kiến trúc MVC.

## Tính năng

- **Quản lý Tour**: Thêm, sửa, xóa tour
- **Quản lý Booking**: Đặt tour, xem hóa đơn
- **Quản lý Người dùng**: Phân quyền Admin, HDV, Khách hàng, Nhà cung cấp
- **Báo cáo Tài chính**: Thống kê doanh thu
- **Đánh giá Tour**: Khách hàng đánh giá tour

## Yêu cầu hệ thống

- PHP >= 7.4
- MySQL >= 5.7
- Apache/Nginx với mod_rewrite
- Composer (tùy chọn)

## Cài đặt

1. Clone repository:
```bash
git clone <repository-url>
cd project_quan_ly_tour_du_lich
```

2. Cấu hình database:
- Tạo database MySQL
- Sao chép file `.env.example` thành `.env`
- Điền thông tin database vào file `.env`

3. Import database:
```sql
-- Tạo các bảng cần thiết
-- (Cần tạo script SQL để import)
```

4. Import database:
- Import file `quan_ly_tour_du_lich.sql` vào MySQL

5. Chay migration versioned:
```bash
php scripts/migrate.php status
php scripts/migrate.php up
```

6. Khởi chạy:
- Truy cập `http://localhost/project_quan_ly_tour_du_lich/` hoặc URL đã cấu hình
- Hoặc sử dụng: `http://localhost/project_quan_ly_tour_du_lich/index.php?act=tour/index`

## Migration versioned

- Thu muc migration: `migrations/`
- Quy uoc ten file: `Vxxx__ten_migration.sql`
- Script chay migration: `scripts/migrate.php`

Lenh ho tro:

```bash
php scripts/migrate.php status
php scripts/migrate.php up
php scripts/migrate.php up --step=1
```

Ghi chu:

- Script tao bang `schema_migrations` tu dong neu chua ton tai.
- Nen backup DB truoc khi chay migration tren production.

## Cấu hình môi trường (.env)

1. Tạo file `.env` từ mẫu `.env.example`.
2. Cập nhật thông tin database.
3. Cấu hình môi trường chạy:
- `APP_ENV=local`: bật hiển thị lỗi để debug.
- `APP_ENV=production`: tắt hiển thị lỗi ra màn hình, ghi log lỗi vào file.
3. Chọn chế độ thanh toán:
- `PAYMENT_MODE=vnpay`: tự động xác nhận qua callback VNPay.
- `PAYMENT_MODE=manual_qr`: xác nhận thủ công theo chuyển khoản QR.
- `PAYMENT_MODE=mock`: giả lập local để test nhanh.
4. Cấu hình email nếu cần gửi hóa đơn/tài liệu booking từ hệ thống:
- `MAIL_ENABLED=1`: bật gửi mail thật.
- `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME`: địa chỉ và tên người gửi.
- `MAIL_REPLY_TO`, `MAIL_REPLY_TO_NAME`: địa chỉ phản hồi.
- `SMTP_HOST`, `SMTP_PORT`, `SMTP_USERNAME`, `SMTP_PASSWORD`: cấu hình SMTP.
- `SMTP_ENCRYPTION=tls` và `SMTP_AUTH=1`: cấu hình phổ biến cho hầu hết nhà cung cấp SMTP.
- Hệ thống ưu tiên gửi qua SMTP bằng PHPMailer. Nếu chưa cấu hình SMTP, helper sẽ rơi về `mail()` gốc của PHP.

## Tối ưu hiệu năng khi dữ liệu lớn

1. Chạy migration index:
- File: `storage/migrate_performance_indexes.sql`
- Mục tiêu: tăng tốc các truy vấn booking, payment, báo cáo tài chính, lịch khởi hành.

2. Bật OPcache trên production (php.ini khuyến nghị):
```ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=192
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

3. Khởi động lại PHP-FPM/Apache sau khi đổi cấu hình OPcache hoặc deploy bản mới.

## VNPay Go-live Checklist

1. Điền đủ 4 biến VNPay trong `.env`:
- `VNPAY_TMN_CODE`
- `VNPAY_HASH_SECRET`
- `VNPAY_URL`
- `VNPAY_RETURN_URL`

2. Cấu hình `VNPAY_RETURN_URL` đúng domain public HTTPS khi triển khai thật.

3. Trên cổng quản trị VNPay, khai báo return URL trùng khớp chính xác với `VNPAY_RETURN_URL`.

4. Chạy test thanh toán sandbox:
- Kiểm tra callback cập nhật `payments.status = ThanhCong`.
- Kiểm tra booking được cập nhật `DaCoc`.
- Kiểm tra có bản ghi `Thu` trong `giao_dich_tai_chinh`.

5. Nếu `PAYMENT_MODE=vnpay` nhưng thiếu config merchant, hệ thống sẽ báo lỗi cấu hình và không fallback sang mock.

6. Trước khi production:
- Xóa/đổi toàn bộ secret sandbox.
- Chuyển `VNPAY_URL` sang endpoint production.
- Bật HTTPS và kiểm tra firewall/reverse proxy cho callback URL.

## Auto-confirm voi webhook ngan hang (Casso/SePay)

Neu ban chua dung VNPay ma muon he thong tu dong xac nhan khi tien vao MB, co the dung webhook:

1. Bat bien trong `.env`:
- `BANK_WEBHOOK_ENABLED=1`
- `BANK_WEBHOOK_PROVIDER=casso` (hoac `sepay`)
- `BANK_WEBHOOK_SECRET=<token-bi-mat>`

2. Cau hinh webhook URL tren dich vu ben thu 3:
- `https://<domain-cua-ban>/index.php?act=payment/bankWebhook`

3. Header xac thuc ho tro:
- `X-Webhook-Secret: <BANK_WEBHOOK_SECRET>`
hoac
- `Authorization: Bearer <BANK_WEBHOOK_SECRET>`

4. Yeu cau noi dung chuyen khoan:
- Co token booking, vi du: `BOOKING_224_0934xxxxxx`

5. Rule auto-confirm hien tai:
- Tim payment `DangXuLy` theo `BOOKING_ID` trich tu noi dung.
- So tien nhan >= so tien can thu (co the cho phep overpay theo cau hinh).
- Thanh cong thi cap nhat `payments`, `booking`, va ghi `Thu` vao `giao_dich_tai_chinh`.

## Cấu trúc thư mục

```
project_quan_ly_tour_du_lich/
├── index.php           # Entry point chính
├── commons/            # File chung (env.php, function.php)
├── controllers/        # Controllers
├── models/             # Models
├── views/              # Views
├── public/             # Public files (CSS, JS, images)
├── uploads/            # Upload files
├── storage/            # Logs, backups, cache
└── database.sql        # File SQL để tạo database
```

## Sử dụng

### Đăng nhập
- Admin: Quản lý toàn bộ hệ thống
- HDV: Quản lý lịch làm việc và tour
- Khách hàng: Xem và đặt tour
- Nhà cung cấp: Quản lý dịch vụ và hợp đồng

## License

MIT


