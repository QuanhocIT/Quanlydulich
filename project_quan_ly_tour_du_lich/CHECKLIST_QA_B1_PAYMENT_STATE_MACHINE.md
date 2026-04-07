# Checklist QA - B1 Payment State Machine

## 1. Scope
Tai lieu nay dung de test cac thay doi B1:
- Payment state machine tap trung.
- Guard transition trang thai thanh toan.
- Dong bo transition giua Admin, Gateway callback/IPN, webhook ngan hang va luong KhachHang.
- Ho tro day du cac state: TaoMoi, DangXuLy, ThanhCong, ThatBai, HetHan, DaDoiSoat.

## 2. Dinh nghia state va transition hop le
Danh sach state:
- TaoMoi
- DangXuLy
- ThanhCong
- ThatBai
- HetHan
- DaDoiSoat

Transition hop le:
- TaoMoi -> DangXuLy / HetHan / ThatBai
- DangXuLy -> ThanhCong / ThatBai / HetHan
- ThanhCong -> DaDoiSoat
- ThatBai -> DangXuLy
- HetHan -> DangXuLy
- DaDoiSoat -> (khong cho transition tiep)

## 3. Test tao payment
### 3.1 Tao payment moi qua redirect gateway
- Trigger flow thanh toan online moi.
- Ky vong:
  - Payment duoc tao o state TaoMoi.
  - He thong transition sang DangXuLy truoc khi redirect gateway.
  - Co log STATE_TRANSITION va CREATE/REDIRECT trong payment_logs.

### 3.2 Khong cho transition sai ngay luc tao
- Gia lap loi o buoc transition TaoMoi -> DangXuLy.
- Ky vong:
  - Flow bi chan an toan.
  - Khong co update trang thai sai.
  - Co log STATE_TRANSITION_BLOCKED.

## 4. Test callback/IPN/query VNPay
### 4.1 Callback thanh cong
- Callback hop le voi responseCode=00, transactionStatus=00.
- Ky vong:
  - Payment DangXuLy -> ThanhCong.
  - Booking cap nhat DaCoc neu chua DaCoc/HoanTat.
  - Tao giao dich tai chinh Thu neu chua ton tai.

### 4.2 Callback that bai
- Callback hop le voi transaction that bai.
- Ky vong:
  - Payment DangXuLy -> ThatBai.
  - Khong tao giao dich tai chinh Thu moi.

### 4.3 VNPay query thanh cong
- Chay truy van VNPay cho payment DangXuLy.
- Ky vong:
  - Payment transition sang ThanhCong neu VNPay xac nhan thanh cong.
  - Co log transition ly do vnpay_query_confirm.

### 4.4 VNPay query non-success
- VNPay tra ve transStatus khong thanh cong.
- Ky vong:
  - Payment transition sang ThatBai hoac giu DangXuLy theo mapping.
  - Co log transition ly do vnpay_query_non_success.

## 5. Test webhook ngan hang
### 5.1 Webhook auto confirm
- Gui webhook transfer inbound hop le, dung amount.
- Ky vong:
  - Payment DangXuLy/TaoMoi -> ThanhCong.
  - Booking cap nhat DaCoc.
  - Co giao dich Thu tai chinh neu truoc do chua co.

### 5.2 Webhook queue consume
- Co du lieu unmatched queue sau do match duoc booking.
- Ky vong:
  - Payment transition sang ThanhCong qua flow consume queue.
  - Queue record duoc mark processed.

## 6. Test auto-timeout
### 6.1 Timeout payment tre
- Tao payment DangXuLy qua thoi gian timeout.
- Ky vong:
  - Payment transition DangXuLy -> HetHan.
  - Co log AUTO_TIMEOUT + STATE_TRANSITION.

### 6.2 Retry sau timeout
- Thu transition HetHan -> DangXuLy (retry flow).
- Ky vong:
  - Transition hop le va duoc chap nhan.

## 7. Test doi soat
### 7.1 Auto doi soat sang DaDoiSoat
- Payment ThanhCong, du lieu tai chinh khop, khong issue.
- Ky vong:
  - Transition ThanhCong -> DaDoiSoat.
  - Co log ly do auto_reconcile_ok.

### 7.2 DaDoiSoat la terminal state
- Thu force transition tu DaDoiSoat sang state khac.
- Ky vong:
  - Bi chan.
  - Co log STATE_TRANSITION_BLOCKED.

## 8. Test UI admin
### 8.1 Payment list
- Mo trang admin/payments.
- Ky vong:
  - Badge hien dung 6 state moi.
  - Co summary card theo tung state.

### 8.2 Reconcile filter
- Loc theo tung payment_status: TaoMoi, DangXuLy, ThanhCong, ThatBai, HetHan, DaDoiSoat.
- Ky vong:
  - Tra ve dung dataset theo state da chon.

## 9. Security va idempotency
- Chay lap callback/IPN cho cung payment.
- Ky vong:
  - Khong tao giao dich tai chinh trung lap.
  - Khong pha vo state machine.
- Kiem tra payment_logs:
  - Co day du action STATE_TRANSITION, STATE_TRANSITION_NOOP, STATE_TRANSITION_BLOCKED.

## 10. Go/No-Go
- [ ] Tat ca transition state machine dung theo matrix.
- [ ] Khong con cap nhat trang thai payment truc tiep bo qua guard.
- [ ] Gateway callback/IPN/query va webhook ngan hang dong bo ket qua trang thai.
- [ ] Auto-timeout sang HetHan hoat dong dung.
- [ ] Auto doi soat sang DaDoiSoat hoat dong dung.
- [ ] UI admin hien dung state moi va bo loc.
- [ ] Khong co regression cho luong thanh toan hien tai.
