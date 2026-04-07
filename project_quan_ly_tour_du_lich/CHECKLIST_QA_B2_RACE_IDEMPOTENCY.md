# Checklist QA - B2 Race Condition va Idempotency

## 1. Scope
Tai lieu nay dung de test cac thay doi B2:
- Chong race condition khi cap nhat payment/booking.
- Idempotency cho callback va webhook.
- Chan tao giao dich thanh toan trung lap khi request gui lai.

## 2. Callback idempotency
### 2.1 Callback gui lai cung payload
- Gui callback cung booking/payment/gateway_ref 2-3 lan lien tiep.
- Ky vong:
  - Chi lan dau duoc xu ly day du.
  - Lan sau bi bo qua an toan (duplicate completed/processing).
  - Khong tao them giao_dich_tai_chinh trung lap.

### 2.2 Callback song song
- Ban 2 request callback cung luc (co the dung Postman Runner / script).
- Ky vong:
  - Khong deadlock.
  - Khong update sai trang thai.
  - Khong nhan ban giao dich thu.

## 3. VNPay IPN idempotency
### 3.1 IPN resend
- Gui lai cung txnRef/gatewayRef/amount.
- Ky vong:
  - He thong nhan duplicate va tra ket qua an toan.
  - Khong lap giao dich tai chinh.

### 3.2 IPN amount mismatch
- Gui IPN sai amount.
- Ky vong:
  - Bi tu choi dung (RspCode 04).
  - Co log amount mismatch.
  - Idempotency record duoc danh dau completed.

## 4. Bank webhook idempotency
### 4.1 Webhook resend
- Gui lai cung payload webhook (provider/gateway_ref/amount/description).
- Ky vong:
  - Request dau xu ly binh thuong.
  - Request sau tra duplicate, khong tao lai thu tai chinh.

### 4.2 Webhook race
- Gui 2 webhook cung payload gan nhu dong thoi.
- Ky vong:
  - Chi 1 request duoc owner xu ly.
  - Request con lai tra processing/duplicate.

## 5. Lock transaction
### 5.1 Lock booking row
- Tao tinh huong callback va webhook cung cap nhat 1 booking.
- Ky vong:
  - Khong co cap nhat trang thai xung dot.
  - Khong co duplicate insert vao giao_dich_tai_chinh.

### 5.2 Lock payment row
- Tao tinh huong 2 request cung update 1 payment.
- Ky vong:
  - Trang thai cuoi cung hop le theo state machine.
  - Khong co du lieu dang doi (dirty write).

## 6. Chan tao payment trung khi retry
### 6.1 Nhan nut thanh toan nhieu lan
- Trigger redirect thanh toan lap lai khi payment dang TaoMoi/DangXuLy.
- Ky vong:
  - Tai su dung payment dang mo (REUSE_INFLIGHT).
  - Khong tao payment moi trung lap.

## 7. Kiem tra bang payment_idempotency
- Xac minh co record theo scope:
  - gateway_callback
  - vnpay_ipn
  - bank_webhook_receive
- Kiem tra status:
  - processing/completed/failed phan anh dung ket qua xu ly.

## 8. Go/No-Go
- [ ] Callback duplicate khong gay xu ly lap.
- [ ] IPN duplicate khong gay xu ly lap.
- [ ] Webhook duplicate khong gay xu ly lap.
- [ ] Khong tao trung giao_dich_tai_chinh khi request gui lai.
- [ ] Khong tao trung payment khi user retry redirect.
- [ ] Khong phat sinh loi race condition ro rang trong test song song.
