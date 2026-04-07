# Checklist QA - Validation Phase 1

## 1. Scope
Checklist nay dung de test regression cho cac action POST da duoc harden validation + CSRF + sanitize.

## 2. Cach test chung
- Dang nhap dung role phu hop truoc khi test (Admin/HDV/KhachHang).
- Moi action test toi thieu 4 nhom case:
  - Missing required field.
  - Sai kieu du lieu (id khong phai so, email sai format, so tien am...).
  - Sai enum value.
  - Du lieu hop le.
- Kiem tra ket qua:
  - Redirect dung trang.
  - Flash error/success dung noi dung.
  - Khong co warning/fatal error.
  - Du lieu DB chi thay doi khi case hop le.
- Kiem tra log:
  - File `storage/security.log` co ghi `validation_failed` cho case sai.

## 3. Auth
### 3.1 `auth/register`
File: `controllers/AuthController.php`

Case can test:
- Thieu `email`.
- `email` sai format (`abc`, `a@`, `a@a`).
- `password` < 6 ky tu.
- `ho_ten` rong.
- `so_dien_thoai` sai format.
- Du lieu hop le.

Ky vong:
- Case sai: o lai trang register, thong bao loi.
- Case dung: tao user + login session + redirect `tour/index`.

## 4. Booking
### 4.1 `booking/create`
File: `controllers/BookingController.php`

Case can test:
- Thieu `tour_id`.
- `tour_id` la chuoi khong phai so.
- `ngay_khoi_hanh` sai format (khong phai `Y-m-d`).
- `so_nguoi` <= 0 hoac khong phai so.
- `tien_coc` am.
- `tong_tien` am.
- Du lieu hop le.

Ky vong:
- Case sai: redirect `tour/index` + bao loi.
- Case dung: tao booking thanh cong, redirect `booking/show&id=...`.

## 5. Payment
### 5.1 `admin/confirm_payment_received`
File: `controllers/PaymentController.php`

Case can test:
- Thieu `received_amount`.
- `received_amount` <= 0.
- Thieu `transfer_note`.
- `transfer_note` < 3 ky tu.
- `transfer_note` khong khop booking/sdt (expect reject theo business).
- Du lieu hop le.

Ky vong:
- Case sai validation: redirect ve `admin/show_payment&id=...` + thong bao loi.
- Case dung: cap nhat payment `ThanhCong`, tao log + giao dich tai chinh neu chua co.

## 6. Admin - Luong
### 6.1 `admin/capNhatLuongCoBan`
### 6.2 `admin/taoLuongThuong`
File: `controllers/AdminController.php`

Case can test:
- `nhan_su_id` thieu/sai.
- `luong_co_ban` am.
- `lich_khoi_hanh_id` thieu/sai.
- `loai_luong` ngoai enum `CoDinh|PhanTram|KetHop`.
- `phan_tram_hoa_hong` > 100.
- Du lieu hop le.

Ky vong:
- Case sai: redirect ve quan ly luong, thong bao loi.
- Case dung: cap nhat/tao ban ghi luong dung.

## 7. Admin - Yeu cau dac biet
### 7.1 `admin/capNhatYeuCauDacBiet`
### 7.2 `admin/themYeuCauDacBiet`
File: `controllers/AdminController.php`

Case can test:
- Thieu `yeu_cau_id` (update) hoac `booking_id` (create).
- `ghi_chu_hdv` qua dai.
- `tieu_de` qua dai.
- Du lieu hop le.

Ky vong:
- Case sai: quay lai trang `admin/yeuCauDacBiet` + loi.
- Case dung: cap nhat/tao request thanh cong.

## 8. Admin - Nha cung cap
### 8.1 `admin/deleteNhaCungCap`
### 8.2 `admin/supplierServiceAction`
File: `controllers/AdminController.php`

Case can test:
- Thieu `id_nha_cung_cap`.
- Thieu `mat_khau`.
- `dich_vu_id` sai.
- `action` ngoai enum `approve|reject|update_price`.
- `gia_tien` <= 0 cho approve/update_price.
- Du lieu hop le.

Ky vong:
- Case sai: khong thay doi DB.
- Case dung: thao tac thanh cong, thong bao dung.

## 9. HDV - Checkin
### 9.1 `hdv/updateCheckInKhach`
### 9.2 `hdv/save_diem_checkin`
### 9.3 `hdv/deleteDiemCheckin`
### 9.4 `hdv/save_checkin_khach`
File: `controllers/HDVController.php`

Case can test:
- Missing required ids.
- `trang_thai` khong hop le.
- `ten_diem` rong.
- CSRF token sai.
- Du lieu hop le.

Ky vong:
- Case sai: reject dung flow (redirect/json fail).
- Case dung: cap nhat checkin thanh cong.

## 10. HDV - Yeu cau dac biet
### 10.1 `hdv/updateYeuCauDacBiet`
### 10.2 `hdv/save_yeu_cau`
### 10.3 `hdv/delete_yeu_cau`
File: `controllers/HDVController.php`

Case can test:
- Missing ids.
- `noi_dung` qua ngan.
- `tieu_de` qua ngan.
- CSRF sai.
- Du lieu hop le.

Ky vong:
- Chi HDV duoc phan cong moi sua duoc.
- Case dung luu du lieu thanh cong.

## 11. HDV - Nhat ky
### 11.1 `hdv/save_nhat_ky`
### 11.2 `hdv/delete_nhat_ky`
File: `controllers/HDVController.php`

Case can test:
- Missing `tour_id`/`id`.
- `tieu_de` rong.
- `noi_dung` rong.
- Upload file loi type/so luong (neu co quy dinh frontend).
- Du lieu hop le.

Ky vong:
- Case sai: khong ghi DB.
- Case dung: them/sua/xoa nhat ky dung.

## 12. HDV - Phan hoi
### 12.1 `hdv/save_phan_hoi`
### 12.2 `hdv/delete_phan_hoi`
File: `controllers/HDVController.php`

Case can test:
- Missing `tour_id`.
- `diem_danh_gia` ngoai [1..5].
- `tieu_de` rong.
- `noi_dung` rong.
- CSRF sai.
- Du lieu hop le.

Ky vong:
- Case sai: reject, co thong bao.
- Case dung: luu/xoa phan hoi thanh cong.

## 13. HDV - Profile
### 13.1 `hdv/update_profile`
File: `controllers/HDVController.php`

Case can test:
- `email` sai format.
- `so_dien_thoai` sai format.
- Chuoi thong tin qua dai.
- CSRF sai.
- Du lieu hop le.

Ky vong:
- Case sai: khong update DB.
- Case dung: update bang `nguoi_dung` + `nhan_su` thanh cong.

## 14. Route-level validation middleware
File: `index.php`, `commons/function.php`

Case can test:
- Gui truc tiep POST sai field den route da khai bao schema.
- Confirm bi chan truoc khi vao business logic.
- Confirm redirect ve trang route va co session error.

## 15. Go-live checklist
- [ ] Chay full checklist tren staging.
- [ ] So sanh security.log truoc/sau test.
- [ ] Kiem tra khong co regression luong thanh toan va booking.
- [ ] Chot release note cho team van hanh.
