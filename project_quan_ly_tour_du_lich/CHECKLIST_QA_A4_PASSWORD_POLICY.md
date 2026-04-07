# Checklist QA - A4 Password Policy va Account Hardening

## 1. Scope
Tai lieu nay dung de test cac thay doi A4:
- Password policy (do manh mat khau).
- Loai bo fallback plaintext.
- Buoc doi mat khau bat buoc cho tai khoan mat khau mac dinh cu.
- Tai khoan tao moi noi bo khong dung mat khau co dinh.

## 2. Password policy
Yeu cau mat khau hop le:
- Toi thieu 8 ky tu.
- Co it nhat 1 chu hoa.
- Co it nhat 1 chu thuong.
- Co it nhat 1 chu so.
- Co it nhat 1 ky tu dac biet.

## 3. Test Auth Register
### 3.1 Mat khau yeu
- Dang ky voi: `12345678`, `abcdefgh`, `ABCDEFGH`, `Abcdefgh`, `Abcdefg1`.
- Ky vong:
  - Bi reject.
  - Hien thong bao policy ro rang.

### 3.2 Mat khau manh
- Dang ky voi vi du: `Abcdef1!`.
- Ky vong:
  - Dang ky thanh cong.
  - Dang nhap thanh cong.

## 4. Test Login hardening
### 4.1 Tai khoan luu password khong phai hash
- Tao 1 account test trong DB voi `mat_khau` plaintext (chi tren staging).
- Dang nhap bang account do.
- Ky vong:
  - Bi chan dang nhap.
  - Co thong bao can dat lai mat khau.
  - Co security log lien quan login fail legacy.

### 4.2 Tai khoan hash hop le
- Dang nhap account binh thuong.
- Ky vong:
  - Dang nhap thanh cong.

## 5. Force password change
### 5.1 Account mac dinh cu
- Dang nhap account van dung password mac dinh cu.
- Ky vong:
  - Bi redirect den `auth/forcePasswordChange`.
  - Khong vao duoc route khac truoc khi doi mat khau.

### 5.2 Doi mat khau bat buoc
- Tai form force change:
  - Sai current password.
  - New password khong dat policy.
  - Confirm khong khop.
  - New password trung current password.
  - New password hop le.
- Ky vong:
  - Cac case sai bi reject dung.
  - Case hop le doi thanh cong va redirect ve home theo role.

## 6. KhachHang cap nhat thong tin doi mat khau
- Vao `khachHang/capNhatThongTin` doi mat khau.
- Ky vong:
  - Mat khau khong dat policy bi reject.
  - Mat khau dat policy duoc update.

## 7. Tai khoan tao moi noi bo
### 7.1 Admin them khach vao lich khoi hanh
- Tao moi user qua man hinh admin them khach.
- Ky vong:
  - Khong dung mat khau co dinh `123456`.
  - He thong sinh mat khau tam thoi ngau nhien va thong bao cho admin.

### 7.2 Google OAuth tao account lan dau
- Dang nhap Google voi email chua co account.
- Ky vong:
  - Account duoc tao thanh cong.
  - Local password khong phai gia tri co dinh de doan.

## 8. Security log
- Kiem tra `storage/security.log` sau cac case login fail/force change.
- Ky vong:
  - Co event lien quan den login fail va doi mat khau.

## 9. Go/No-Go
- [ ] Password policy hoat dong dung tren register va change password.
- [ ] Fallback plaintext da bi loai bo.
- [ ] Force password change flow hoat dong end-to-end.
- [ ] Tai khoan tao moi noi bo khong dung mat khau co dinh.
- [ ] Khong co regression dang nhap/dang ky tren cac role.
