# Bao cao ra soat du an - 2026-04-09

## 1. Muc tieu tai lieu

Tai lieu nay tong hop nhanh hien trang du an de tiep tuc phat trien. Danh gia duoc lap tu viec doc code, tai lieu, cau truc thu muc, va ra soat cac diem de vo/no ky thuat trong he thong. Day la bao cao static review, chua bao gom test runtime tren DB that, browser that, hoac load test.

## 2. Nhan xet tong quan

- Du an da co pham vi nghiep vu kha day du: tour, booking, lich khoi hanh, thanh toan, hoa don, bao cao, HDV, nha cung cap.
- Luong payment da co nhieu nang cap tot hon mat bang chung cua mot du an PHP thuan: state machine, idempotency, webhook, doi soat.
- Tuy nhien, he thong van phu thuoc rat nhieu vao controller lon, schema tu sua trong runtime, va chua co test tu dong.
- Neu tiep tuc them feature ma khong siet nen tang, rui ro gay hoi quy o cac module booking/payment/admin se rat cao.

## 3. Cac phan dang khong hoat dong hoac chua hoan thien

### 3.1 Tinh nang xuat bao cao tai chinh chua hoat dong thuc te

- File: controllers/BaoCaoTaiChinhController.php
- Ham: xuatBaoCao()
- Hien trang: moi redirect va set thong bao "Chuc nang xuat bao cao dang duoc phat trien".
- Ket luan: giao dien da co nhung chuc nang chua implement.

### 3.2 Xuat PDF bao cao danh gia chua hoat dong

- File: controllers/DanhGiaController.php
- Ham: exportPDF()
- Hien trang: set header Content-Type la application/pdf nhung sau do lai redirect ve trang bao cao va thong bao dung Excel.
- Rui ro: hanh vi response khong sach, de gay loi header/redirect tuy moi truong.

### 3.3 Cap nhat tien coc co nhanh "tam thoi thanh cong"

- File: controllers/BookingController.php
- Doan code quanh viec cap nhat tien coc.
- Hien trang: neu bang booking chua co cot tien_coc thi code dat `$result = true` va tiep tuc nhu thanh cong.
- Ket luan: day la false positive, co the thong bao thanh cong trong khi du lieu khong duoc ghi dung.

### 3.4 Kiem tra quyen HDV dang de tam thoi

- File: controllers/BookingController.php
- Ham: checkPermissionToUpdate()
- Hien trang: neu la HDV thi `return true; // Tam thoi cho phep`.
- Ket luan: logic quyen chua day du, co nguy co HDV sua booking ngoai pham vi duoc phan cong.

### 3.5 Nhieu noi van dua vao tao bang/cot ngay trong request user

- File: controllers/KhachHangController.php
  - tao bang payments, payment_logs
  - ALTER TABLE booking them cot trang_thai_hanh_khach
- File: controllers/PaymentGatewayController.php
  - tao bang payments, payment_logs
- File: models/CheckinKhach.php
  - ALTER TABLE tour_checkin them anh_cccd, anh_passport
- File: commons/function.php
  - tao bang admin_notification_state
- File: services/PaymentReconcileService.php
  - tao bang payment_reconcile_audit
- Ket luan: he thong van dung runtime schema mutation thay cho migration chuan. Day khong phai "hong" ngay lap tuc, nhung la nguon gay su co kho doan tren production.

### 3.6 Debug code va log debug van con trong luong chinh

- File: controllers/DanhGiaController.php
  - error_log trong index()
- File: controllers/HDVController.php
  - nhieu dong `HDV Checkin Debug ...`
- File: controllers/PaymentGatewayController.php
  - payment_redirect_debug.log va IPN debug file
- Ket luan: mot so debug log la hop ly cho payment, nhung phan con lai nen duoc chuan hoa bang logger va bat/tat theo moi truong.

## 4. Cac diem dang yeu, de vo, hoac chua toi uu

### 4.1 Controller qua lon, coupling cao

Kich thuoc controller hien tai:

- AdminController.php: 129.3 KB
- HDVController.php: 106.4 KB
- KhachHangController.php: 79.3 KB
- BookingController.php: 60.9 KB
- LichKhoiHanhController.php: 50.8 KB
- PaymentGatewayController.php: 43.4 KB
- BaoCaoTaiChinhController.php: 41.5 KB

Tac dong:

- Kho test, kho review, kho tach loi.
- Business logic va SQL bi tron vao luong controller.
- Moi lan sua mot file de cham vao nhieu domain cung luc.

Khuyen nghi:

- Tach AdminController thanh cac controller con: BookingAdmin, FinanceAdmin, UserAdmin, NotificationAdmin, TourAdmin.
- Tach logic nghiep vu thanh service ro domain, khong tiep tuc nhet them vao controller.

### 4.2 Chua co test tu dong

- Khong co thu muc tests/
- Khong co CI workflow trong .github/workflows/
- Khong co smoke test tu dong cho login, booking, payment callback, webhook

Tac dong:

- Moi thay doi deu phu thuoc test tay.
- Refactor payment/booking rat de gay hoi quy ma khong biet.

Khuyen nghi:

- Bat dau bang smoke test cap do script cho 4 flow: login, create booking, callback payment, bank webhook.
- Sau do moi nang cap len PHPUnit/Pest neu muon di duong dai.

### 4.3 Van con qua nhieu `die()` / `exit()` trong controller

- Xuat hien day trong KhachHangController, BookingController, AdminController, HDVController, DanhGiaController...
- Dieu nay lam flow xu ly loi khong dong nhat, kho test, kho tai su dung, va de xay ra response dang do.

Khuyen nghi:

- Chuan hoa 2 kieu response: web redirect + flash message, va JSON error response.
- Giam dan `die/exit` trong nghiep vu, giu lai o diem cuoi response neu can.

### 4.4 Runtime DDL trong request la mot no ky thuat lon

Van de nay dang lap lai o nhieu noi, cho thay migration chua duoc version hoa dung nghia.

Tac dong:

- Request dau tien sau deploy co the bi cham bat thuong.
- DDL co the implicit commit, anh huong transaction.
- Schema giua cac moi truong de lech nhau.

Khuyen nghi:

- Dung runtime `CREATE TABLE IF NOT EXISTS` va `ALTER TABLE` trong controller/model.
- Dua toan bo schema change vao migration scripts co thu tu ro rang.
- Tao runner migrate.php va bang schema_migrations.

### 4.5 Su dung `@file_put_contents` va cache file qua nhieu

Xuat hien o:

- commons/SessionSecurity.php
- commons/function.php
- commons/perf.php
- commons/mail.php
- controllers/PaymentGatewayController.php
- controllers/BankWebhookController.php
- services/PaymentReconcileService.php

Tac dong:

- Suppress loi I/O, khi het disk/quyen ghi sai thi he thong mat log ma khong biet.
- Cache dang dua vao JSON file cuc bo, kho scale neu sau nay tach app/process.

Khuyen nghi:

- Chuyen qua helper logger chung.
- Khong suppress loi im lang o cac luong quan trong nhu payment/webhook/security.
- Neu chua dung Redis, it nhat phai co wrapper log/cache co kiem tra loi.

### 4.6 Session security da co, nhung fingerprint user-agent hoi cung

- File: commons/SessionSecurity.php
- Dang hash toan bo user agent de rang buoc session.

Tac dong:

- Co the gay logout ngoai y muon tren mot so trinh duyet/mobile/proxy.

Khuyen nghi:

- Giam do chat: thay vi rang buoc cung 100% user-agent, co the ket hop session rotation + idle timeout + login event log.

### 4.7 Xu ly loi ket noi DB hien van lo thong tin

- File: commons/env.php
- Ham getPDOConnection() dang `die("Ket noi that bai: ...")`.

Tac dong:

- Moi truong local thi chap nhan duoc, nhung production khong nen lo chi tiet loi DB ra response.

Khuyen nghi:

- Production: log loi vao file va tra thong diep chung.

### 4.8 README va thuc te chua khop hoan toan

Bat nhat da thay:

- README noi import `database.sql`, nhung file thuc te la `quan_ly_tour_du_lich.sql`.
- README mo ta mot so buoc setup chua chot ro migration hien tai.
- Dung luong docs upgrade/roadmap tot hon docs setup chay moi truong moi.

Khuyen nghi:

- Cap nhat README theo ten file SQL thuc te.
- Them muc "sau khi clone thi chay gi" theo thu tu ro rang.

### 4.9 Model lon va gom nhieu trach nhiem

Model lon nhat hien tai:

- Booking.php: 30.4 KB
- PhanBoNhanSu.php: 29.7 KB
- Tour.php: 21.9 KB
- HDVManagement.php: 19.2 KB
- ThongBao.php: 15.9 KB
- YeuCauDacBiet.php: 15.7 KB
- DanhGia.php: 15.4 KB

Khuyen nghi:

- Tiep tuc tach query doc/ghi theo use case.
- Cac model domain lon nen tach them repository/query object neu tiep tuc phat trien bao cao va dashboard.

## 5. Cac diem manh nen giu va phat huy

- Da co state machine cho payment trong models/Payment.php.
- Da co idempotency cho callback/webhook.
- Da co bank webhook flow va doi soat payment.
- Da co .env.example va tach config kha ro.
- Da co roadmap 30/60/90 va cac checklist QA cho payment/security.

Day la nen tang tot de tiep tuc nang cap bai ban, khong nen dap di lam lai.

## 6. Uu tien ky thuat de lam tiep

### P0 - Nen lam ngay

1. Bo runtime schema changes ra khoi request
- Tao migration versioned.
- Xoa dan `CREATE TABLE IF NOT EXISTS` va `ALTER TABLE` trong controller/model.

2. Sua cac tinh nang dang "gia hoat dong"
- BaoCaoTaiChinhController::xuatBaoCao()
- DanhGiaController::exportPDF()
- BookingController nhanh cap nhat tien coc dang `return true` tam thoi
- BookingController checkPermissionToUpdate() cho HDV

3. Chuan hoa error handling va logging
- Giam `die/exit`
- Tao logger chung cho payment, webhook, app, security
- Bo `@file_put_contents` o cac diem nghiep vu quan trong

4. Tao bo smoke test toi thieu
- Login
- Tao booking
- Tao payment pending
- Callback/IPN
- Bank webhook

### P1 - Nen lam trong 2-4 tuan

1. Tach controller lon
- AdminController truoc
- Sau do toi BookingController va KhachHangController

2. Tach service layer ro hon
- BookingService
- PaymentService
- NotificationService

3. Chuan hoa route va response
- Response JSON thong nhat cho AJAX/API
- Flash message thong nhat cho web flow

4. Don debug code
- Goi debug theo APP_ENV hoac logger level
- Xoa log debug tay khong con can thiet

### P2 - Nen lam trong 1-3 thang

1. Them CI co ban
- PHP syntax check
- smoke test
- kiem tra migration status

2. Tinh lai cache strategy
- Dashboard
- Bao cao tai chinh
- Danh sach payment/reconcile

3. API hoa cac domain cot loi
- tours
- bookings
- payments
- notifications

## 7. Goi y chuc nang co the phat trien tiep

### 7.1 Job nen cho tac vu nang

Nen dua cac tac vu sau ra khoi request user:

- gui mail hoa don
- xuat PDF
- doi soat dinh ky
- thong bao he thong

Loi ich:

- giam thoi gian cho cua nguoi dung
- de retry khi loi mail/PDF
- de scale sau nay

### 7.2 Dashboard canh bao thoi gian thuc cho admin

He thong da co data payment/review/notification, co the phat trien them:

- canh bao payment mismatch
- canh bao booking cho xu ly
- canh bao cong no qua han
- thong bao co khiu nai thanh toan moi

Neu chua muon dung WebSocket, co the bat dau tu poll nhe hoac SSE.

### 7.3 Timeline booking 360 do

Cho moi booking, hien thi timeline gom:

- tao booking
- dat coc/thanh toan
- check-in
- invoice
- thong bao
- danh gia/khieu nai

Tinh nang nay rat hop voi domain hien tai va giup support/admin xu ly nhanh.

### 7.4 Cong thong tin khach hang tu phuc vu

Co the mo rong cho khach hang:

- theo doi trang thai thanh toan ro hon
- bo sung thong tin hanh khach online
- tai hoa don/tai lieu
- gui khieu nai co ma theo doi
- yeu cau doi lich/hoan/huy co workflow

### 7.5 Cong thong tin nha cung cap/HDV day du hon

Da co module HDV va nha cung cap, nhung co the nang cap them:

- xac nhan nhan viec
- cap nhat chi phi thuc te tai hien truong
- upload chung tu
- chat/phan hoi noi bo theo tour
- KPI va lich su hop tac

### 7.6 API noi bo cho mobile/frontend sau nay

Neu du an muon di xa hon, nen co API noi bo cho:

- app khach hang
- app HDV
- dashboard finance tach rieng

## 8. Lo trinh goi y thuc te

### Dot 1 - On dinh nen tang

- Chot migration runner
- Sua 4 diem chua hoan thien o muc 6 P0
- Cap nhat README setup
- Tao smoke test toi thieu

### Dot 2 - Giam no ky thuat lon

- Tach AdminController
- Tach Booking/Payment service
- Chuan hoa log va error response

### Dot 3 - Mo rong tinh nang

- Job nen
- dashboard canh bao real-time
- booking timeline
- cong thong tin khach hang/HDV/nha cung cap nang cao

## 9. Danh sach backlog de co the giao tiep cho AI/dev ngay

1. Tao he thong migration versioned cho du an PHP thuan, thay toan bo runtime DDL trong controller/model bang migration scripts.
2. Implement chuc nang xuat bao cao tai chinh thuc su (Excel truoc, PDF sau) thay vi redirect thong bao dang phat trien.
3. Sua DanhGiaController::exportPDF() de dung dompdf thay vi set header roi redirect.
4. Sua BookingController de neu thieu cot `tien_coc`, `trang_thai_coc`, `so_tien_con_lai` thi bao loi ro rang hoac migration schema, khong duoc fake thanh cong.
5. Sua BookingController::checkPermissionToUpdate() de HDV chi duoc sua booking thuoc lich/tour minh duoc phan cong.
6. Tao helper logger chung cho app/payment/webhook/security, loai bo dan `@file_put_contents` o cac luong quan trong.
7. Tach AdminController thanh nhieu controller con theo domain.
8. Tao smoke test cho login, booking, payment callback, bank webhook.
9. Cap nhat README theo file SQL thuc te `quan_ly_tour_du_lich.sql` va bo sung huong dan setup day du.
10. Don debug log thu cong trong DanhGiaController va HDVController, chuyen sang logger co level theo moi truong.

## 10. Ket luan

Du an khong phai la mot nen tang "hong nhieu" theo kieu khong dung duoc. Nguoc lai, nghiep vu da rat day du va da co nhieu nang cap dung huong, nhat la payment. Van de lon nhat hien tai la do on dinh de phat trien tiep: controller qua lon, schema mutation trong runtime, va khong co test tu dong.

Neu muc tieu la tiep tuc mo rong he thong ma khong bi sa vao vong lap sua loi, thu tu hop ly nhat la:

1. on dinh nen tang
2. giam no ky thuat lon
3. moi them feature moi

Neu lam dung thu tu nay, du an co the phat trien tiep kha ben vung ma khong can viet lai tu dau.