# Checklist QA - B3 Dong bo Payment va Doi soat

## 1. Scope
Tai lieu nay dung de test cac thay doi B3:
- Dong bo payment voi giao dich tai chinh.
- Quy trinh sua loi doi soat co kiem soat va audit trail.
- Bao cao canh bao mismatch hang ngay.

## 2. Tu dong tao giao dich Thu khi payment ThanhCong
### 2.1 Manual confirm
- Admin xac nhan payment thanh cong.
- Ky vong:
  - Co giao dich Thu tai chinh neu truoc do chua co.
  - Khong tao trung lap giao dich Thu khi xac nhan lai.

### 2.2 Gateway/IPN/Webhook
- Chay callback/IPN/webhook cho payment thanh cong.
- Ky vong:
  - Dong bo payment va booking dung trang thai.
  - Giao dich Thu duoc tao mot lan duy nhat.

## 3. Quy trinh sua loi doi soat co kiem soat
### 3.1 Repair missing finance bat buoc ly do
- Tai man doi soat, thu submit repair ma khong nhap ly do >= 10 ky tu.
- Ky vong:
  - He thong tu choi, thong bao loi hop le.

### 3.2 Repair missing finance thanh cong
- Chon payment ThanhCong nhung chua co giao dich Thu.
- Nhap ly do hop le va submit.
- Ky vong:
  - Tao but toan Thu bo sung.
  - Co log REPAIR_FINANCE trong payment_logs.
  - Co record audit trong payment_reconcile_audit (before/after/reason/performed_by).

### 3.3 Khong cho repair sai dieu kien
- Thu repair voi payment khong phai ThanhCong hoac da co giao dich Thu.
- Ky vong:
  - He thong chan thao tac.
  - Khong tao du lieu tai chinh moi.

## 4. Bao cao mismatch hang ngay
### 4.1 Daily report card
- Mo man hinh admin/paymentReconcile.
- Ky vong:
  - Hien thi card Bao cao ngay, Tong payment/ngay, Canh bao/ngay, Thieu thu/ngay, Thua thu/ngay, Lech tien/ngay.

### 4.2 Cache bao cao
- Tai lai trang nhieu lan trong ngay.
- Ky vong:
  - Du lieu report nhat quan.
  - Co file cache storage/cache/payment_mismatch_daily_report.json.

## 5. Auto reconcile tick
- Trigger runAutoReconcileTick qua trang payment/reconcile.
- Ky vong:
  - Van tiep tuc canh bao mismatch nhu cu.
  - Khong gay loi khi refresh report daily.

## 6. Go/No-Go
- [ ] Payment ThanhCong duoc dong bo giao dich Thu dung logic kiem tra trung.
- [ ] Repair flow bat buoc ly do va co audit trail day du.
- [ ] Bao cao mismatch hang ngay hien thi dung tren man doi soat.
- [ ] Khong co regression cho trang admin/payments va admin/paymentReconcile.
