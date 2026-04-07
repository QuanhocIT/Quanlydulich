# Checklist QA - A3 Session Security va CSRF

## 1. Scope
Tai lieu nay dung de UAT cac nang cap A3:
- Session timeout, session rotation, logout an toan.
- CSRF token cho cac form POST quan trong.
- OAuth state cho dang nhap Google.
- Security logging trong `storage/security.log`.

## 2. Dieu kien test
- Moi truong staging co HTTPS (neu co).
- Co tai khoan test cho cac role: Admin, HDV, KhachHang, NhaCungCap.
- Co tai khoan khach hang dang dung mat khau mac dinh (de test force change).

## 3. Session Security
### 3.1 Login thanh cong
- Dang nhap bang user hop le.
- Ky vong:
  - Dang nhap thanh cong, redirect dung dashboard theo role.
  - Session moi duoc tao (session id thay doi sau login).

### 3.2 Session idle timeout
- Dang nhap, de im khong thao tac vuot nguong timeout.
- Gui request vao route can login.
- Ky vong:
  - Bi day ve trang login.
  - Co thong bao phien het han.
  - `storage/security.log` co event timeout.

### 3.3 Session absolute timeout
- Dang nhap va duy tri thao tac nhe den khi qua nguong absolute timeout.
- Ky vong:
  - Bi yeu cau dang nhap lai.
  - Log co event absolute timeout.

### 3.4 Logout
- Dang nhap roi logout.
- Dung nut Back trinh duyet quay ve trang admin/hdv.
- Ky vong:
  - Khong truy cap duoc trang yeu cau login.
  - Phai dang nhap lai.

## 4. CSRF
### 4.1 POST co token hop le
- Test cac form POST quan trong (booking, payment, admin thao tac nhay cam, cong no HDV).
- Ky vong:
  - Submit thanh cong khi token hop le.

### 4.2 POST thieu token
- Xoa `_csrf_token` va `_csrf_global` bang DevTools, submit lai.
- Ky vong:
  - Request bi reject.
  - Co thong bao CSRF.
  - `storage/security.log` co event `csrf_validation_failed`.

### 4.3 POST token sai
- Sua token thanh gia tri ngau nhien, submit lai.
- Ky vong:
  - Request bi reject.
  - Khong thay doi du lieu DB.
  - Co log CSRF fail.

## 5. Google OAuth state
### 5.1 Dang nhap Google binh thuong
- Dang nhap qua Google thanh cong.
- Ky vong:
  - Redirect dung dashboard theo role.

### 5.2 State bi thieu/sai
- Goi truc tiep callback voi `state` thieu hoac sai.
- Ky vong:
  - He thong chan dang nhap.
  - Bao loi phien Google khong hop le.
  - Co log oauth state fail.

## 6. Password policy lien quan A4 (smoke)
### 6.1 Dang ky
- Thu dang ky voi mat khau yeu (`123456`, `abcdefg`, `Abcdefgh`).
- Ky vong:
  - Reject, hien thong bao policy.

### 6.2 Force change password
- Dang nhap user co mat khau mac dinh.
- Ky vong:
  - Bi redirect den `auth/forcePasswordChange`.
  - Khong truy cap route khac cho den khi doi mat khau.

## 7. Security log verification
- Sau moi testcase fail, kiem tra file `storage/security.log`.
- Ky vong:
  - Co event voi thong tin route/method/ip va context.

## 8. Go/No-Go
- [ ] Tat ca testcase critical pass.
- [ ] Khong co thay doi DB sai khi CSRF fail.
- [ ] Session timeout/force-login-lai hoat dong dung.
- [ ] OAuth state fail duoc chan 100%.
- [ ] Log bao mat ghi nhan day du.
