# Roadmap 30-60-90 ngay - Du an Quan ly Tour Du lich

Ngay cap nhat: 2026-04-07
Pham vi: On dinh nen tang, giam no ky thuat, va mo rong san pham theo kha nang van hanh thuc te.

## 1) Muc tieu tong quan

- 30 ngay dau: Lam chac nen (bao mat, migration, test smoke, logging).
- 60 ngay: Giam no ky thuat lon (tach controller, service layer, toi uu truy van).
- 90 ngay: Nang cap van hanh va kha nang scale (job nen, API noi bo, CI/CD co ban).

## 2) Nguyen tac uu tien

- Uu tien luong tien va booking truoc (do rui ro nghiep vu cao nhat).
- Moi thay doi lon deu co test hoi quy toi thieu.
- Chuan hoa truoc khi mo rong tinh nang moi.
- Tranh sua rong; chia nho theo task co do luong duoc.

## 3) Ke hoach 30 ngay (Thang 1)

### 3.1 Muc tieu

- Chuan hoa phan quyen va session security.
- Dua migration vao co che versioned.
- Co bo test smoke cho flow quan trong.
- Co log du lieu thanh toan/webhook/mail de truy vet su co.

### 3.2 Cong viec chinh

1. Authz hardening
- Chuyen cac diem check role thu cong sang helper thong nhat.
- Bo sung gate cho cac action dang comment guard.
- Kiem tra lai cac route admin, khach hang, HDV, nha cung cap.

2. Session hardening
- Regenerate session id sau login.
- Timeout session inactivity (de xuat 30 phut).
- Bat cookie httponly, secure (khi HTTPS), samesite.

3. Migration versioned
- Tao bang schema_migrations.
- Tao script runner migrate.php (up/status).
- Chuyen cac SQL quan trong vao thu muc migrations co thu tu.

4. Logging toi thieu
- Chuan hoa log thanh toan, webhook, mail theo format JSON line.
- Tach log theo file/domain (payment, webhook, mail, app).
- Them correlation id cho request quan trong neu co the.

5. Test smoke
- Tao bo test co ban cho: login, create booking, callback thanh toan, bank webhook.
- Chay test moi lan merge nhanh (co the bat dau bang script don gian).

### 3.3 KPI ket thuc Thang 1

- 0 action nhay cam dung check role thu cong.
- 100% migration moi qua runner versioned.
- Co toi thieu 20 test case smoke/regression cho flow cot loi.
- Co log de truy vet day du cho payment va webhook.

## 4) Ke hoach 60 ngay (Thang 2)

### 4.1 Muc tieu

- Giam coupling va kich thuoc khoi controller lon.
- Chuan hoa luong xu ly loi va response.
- Cai thien hieu nang truy van cho dashboard/bao cao.

### 4.2 Cong viec chinh

1. Tach controller lon theo domain
- Tach AdminController thanh: TourAdmin, BookingAdmin, FinanceAdmin, NotificationAdmin, UserAdmin.
- Moi controller co pham vi ro rang, gioi han do dai hop ly.

2. Dua business logic vao service
- BookingService: tao/sua/trang thai/lich su.
- PaymentService: doi soat, confirm, idempotency.
- NotificationService: stream/count/mark read.

3. Chuan hoa error handling
- Dinh nghia mot format loi thong nhat cho endpoint JSON.
- Tranh die() truc tiep o luong nghiep vu.
- Flash message thong nhat cho web flow.

4. Toi uu truy van
- Ra soat N+1 o dashboard va bao cao tai chinh.
- Bo sung index con thieu theo truy van thuc te.
- Theo doi top slow query va action cham nhat.

### 4.3 KPI ket thuc Thang 2

- Controller lon nhat giam >= 40% do dai.
- Thoi gian tai dashboard giam 30-50% tren dataset lon.
- Loi hoi quy giam ro sau moi dot release.

## 5) Ke hoach 90 ngay (Thang 3)

### 5.1 Muc tieu

- Tach tac vu nang sang xu ly nen.
- Co API noi bo de san sang mo rong frontend/mobile.
- Co pipeline CI co ban de giam loi truoc deploy.

### 5.2 Cong viec chinh

1. Job nen
- Dua gui mail, xuat invoice PDF, doi soat dinh ky sang background job.
- Co retry va dead-letter strategy toi thieu.

2. API noi bo
- Tao cac endpoint v1 cho tours/bookings/payments.
- Co auth va validate thong nhat.
- Tai lieu API toi thieu (OpenAPI hoac markdown spec).

3. CI co ban
- Kiem tra syntax/lint.
- Chay bo smoke test tu dong.
- Kiem tra migration status truoc deploy.

4. Release checklist
- Checklist pre-deploy/post-deploy/rollback.
- Xac minh callback thanh toan va webhook sau release.

### 5.3 KPI ket thuc Thang 3

- >= 80% tac vu nang khong chay truc tiep trong request user.
- Co pipeline CI chay moi lan push/PR.
- Thoi gian xu ly su co production giam nho log + checklist.

## 6) Backlog theo tuan (goi y)

### Tuan 1
- Kiem ke toan bo diem phan quyen trong controllers.
- Chot policy session/cookie.
- Chot format log JSON line.

### Tuan 2
- Sua authz + session hardening.
- Them schema_migrations + migrate runner ban dau.
- Bat dau bo test smoke login + booking.

### Tuan 3
- Bo sung test payment callback + bank webhook.
- Chuan hoa log payment/webhook/mail.
- Dashboard nho hien thi thong ke slow request.

### Tuan 4
- Don no ky thuat nho con lai.
- Chot bao cao KPI Thang 1.
- Chuan bi ke hoach tach controller cho Thang 2.

### Tuan 5-8
- Tach AdminController theo domain.
- Dua logic vao service layer.
- Toi uu truy van va index theo so lieu do duoc.

### Tuan 9-12
- Dua mail/pdf/reconcile vao job nen.
- Dung API v1 cho domain cot loi.
- Setup CI + release checklist.

## 7) Rui ro va giam thieu

1. Rui ro: Refactor gay loi hoi quy
- Giam thieu: Lam theo batched PR nho + smoke test bat buoc.

2. Rui ro: Migration gay lech schema giua moi truong
- Giam thieu: Runner versioned + script status + backup truoc migrate.

3. Rui ro: Luong payment callback/webhook race condition
- Giam thieu: Idempotency key + lock transaction + audit log day du.

4. Rui ro: Team qua tai khi vua sua no ky thuat vua them feature
- Giam thieu: Quy uoc 70% no ky thuat, 30% feature trong 2 thang dau.

## 8) De xuat phan bo nguon luc toi thieu

- 1 nguoi backend chinh: authz, payment, migration.
- 1 nguoi backend ho tro: test, logging, reporting query.
- 1 nguoi frontend/fullstack: dashboard UX + form flow.
- 1 PM/lead part-time: tracking KPI, release checklist, risk control.

## 9) Definition of Done (DoD) cho moi task quan trong

- Co test (hoac checklist test thu cong neu chua co framework).
- Co log day du cho su kien quan trong.
- Khong pha vo route/flow hien co.
- Co tai lieu ngan mo ta thay doi va cach rollback.

## 10) Cac buoc bat dau ngay

1. Chot owner tung hang muc trong 30 ngay dau.
2. Tao board sprint voi cac task tu Tuan 1-2.
3. Uu tien chay task authz + session + migration truoc.
4. Chot lich release nho hang tuan de giam rui ro.

## 11) Danh sach hang muc cu the de dua cho AI phat trien

Muc tieu cua section nay: ban co the copy tung block prompt, dua cho AI de lam theo sprint nho, de review va merge dan.

### 11.1 Nhom A - Lam chac nen tang (nen lam truoc)

1. A1 - Chuan hoa phan quyen toan bo controllers
- Output can co:
	- Danh sach tat ca action chua co gate role ro rang.
	- Code sua de tat ca action nhay cam deu dung helper thong nhat.
	- Bao cao route nao doi hanh vi truy cap.
- File du kien tac dong:
	- index.php
	- controllers/*.php
	- commons/function.php
- Acceptance criteria:
	- Khong con check role thu cong cho action nhay cam.
	- Cac action admin khong the truy cap boi role khac.
	- Khong pha vo luong dang nhap/dang xuat hien tai.
- Prompt mau cho AI:
	- "Khao sat toan bo controllers va index router, liet ke action dang check role thu cong hoac chua check role. Sau do refactor de dung helper phan quyen thong nhat. Giu nguyen URL route cu. Tao bang truoc/sau cho tung action va them test smoke cho 5 route quan trong nhat."

2. A2 - Session security hardening
- Output can co:
	- Regenerate session id sau login.
	- Timeout inactivity.
	- Cookie flag an toan theo moi truong.
- File du kien tac dong:
	- index.php
	- controllers/AuthController.php
	- commons/env.php
	- commons/function.php
- Acceptance criteria:
	- Session id thay doi sau login thanh cong.
	- Session het han khi qua inactivity timeout.
	- Cookie co httponly va samesite; secure khi HTTPS.
- Prompt mau cho AI:
	- "Them hardening cho session login: regenerate session id, inactivity timeout 30 phut, cookie httponly/samesite va secure khi HTTPS. Khong thay doi giao dien. Bao cao cac file da sua va cach test thu cong."

3. A3 - Migration runner versioned
- Output can co:
	- Thu muc migrations versioned.
	- Script migrate up/status.
	- Bang schema_migrations.
- File du kien tac dong:
	- scripts/migrate.php (moi)
	- migrations/*.sql (moi)
	- commons/function.php hoac commons/env.php (neu can helper DB)
- Acceptance criteria:
	- Chay duoc lenh status va up khong loi.
	- Moi migration chi chay 1 lan.
	- Co huong dan su dung trong README.
- Prompt mau cho AI:
	- "Tao migration system don gian cho du an PHP thuan: schema_migrations, scripts/migrate.php ho tro lenh status va up, doc file SQL theo thu tu Vxxx__name.sql. Cap nhat README phan huong dan migrate."

4. A4 - Chuan hoa logging payment/webhook/mail
- Output can co:
	- Logger helper theo JSON line.
	- Tach file log theo domain.
	- Them request_id/correlation_id cho event quan trong.
- File du kien tac dong:
	- commons/ (tao them logger helper)
	- controllers/PaymentGatewayController.php
	- controllers/BankWebhookController.php
	- commons/mail.php
- Acceptance criteria:
	- Log format thong nhat JSON line.
	- Moi su kien payment quan trong deu co request_id.
	- Co tai lieu mapping event -> y nghia.
- Prompt mau cho AI:
	- "Refactor logging ve mot helper chung theo JSON line. Ap dung cho payment gateway, bank webhook, va mail helper. Them request_id de trace su co xuyen suot. Khong doi business flow hien tai."

### 11.2 Nhom B - Giam no ky thuat lon

5. B1 - Tach AdminController theo domain
- Output can co:
	- Bo controller moi theo domain.
	- Router map lai nhung giu nguyen act cu.
	- Bao cao mapping method cu -> method moi.
- File du kien tac dong:
	- controllers/AdminController.php
	- controllers/*Admin*.php (moi)
	- index.php
- Acceptance criteria:
	- Khong doi URL act hien co.
	- Chuc nang cu van chay.
	- Do dai file AdminController giam manh.
- Prompt mau cho AI:
	- "Tach AdminController lon thanh nhieu controller theo domain (booking, tour, finance, notification, users) nhung giu nguyen route act de khong anh huong frontend. Tao bang mapping method cu sang method moi va cap nhat router."

6. B2 - Tao service layer cho booking/payment
- Output can co:
	- BookingService va PaymentService.
	- Controller chi giu vai tro nhan request va tra response.
- File du kien tac dong:
	- services/BookingService.php (moi)
	- services/PaymentService.php (moi)
	- controllers/BookingController.php
	- controllers/PaymentController.php
- Acceptance criteria:
	- Business logic chinh duoc dua vao service.
	- Controller gon hon, de doc hon.
	- Co test smoke cho luong booking va payment sau refactor.
- Prompt mau cho AI:
	- "Tach business logic trong BookingController va PaymentController sang service layer. Giu nguyen hanh vi hien tai va route hien tai. Bo sung test smoke de dam bao khong regression."

7. B3 - Toi uu truy van va index theo thuc te
- Output can co:
	- Danh sach truy van cham top.
	- De xuat index + migration index.
	- So sanh truoc/sau (thoi gian xu ly).
- File du kien tac dong:
	- models/*.php
	- controllers/BaoCaoTaiChinhController.php
	- storage/migrations index moi
- Acceptance criteria:
	- Dashboard/bao cao nhanh hon ro rang tren du lieu mau lon.
	- Khong doi ket qua nghiep vu.
- Prompt mau cho AI:
	- "Phan tich truy van cham o dashboard va bao cao tai chinh, de xuat va tao migration index toi uu. Giu nguyen ket qua nghiep vu, bao cao benchmark truoc/sau."

### 11.3 Nhom C - Mo rong san pham de tang gia tri

8. C1 - Dynamic pricing co ban
- Output can co:
	- Bang pricing_rules.
	- Logic tinh gia theo ngay/mua/so nguoi.
	- UI admin quan ly rule.
- File du kien tac dong:
	- models/Tour.php + model moi
	- controllers/TourController.php hoac admin controller lien quan
	- views/admin/*
- Acceptance criteria:
	- Gia tour tinh dung theo rule dang active.
	- Co fallback ve gia_co_ban neu khong co rule.
- Prompt mau cho AI:
	- "Bo sung tinh nang dynamic pricing: tao pricing_rules, tinh gia tour theo dieu kien (mua, ngay, so nguoi), va giao dien admin quan ly rule. Dam bao fallback gia co ban khi khong co rule."

9. C2 - Loyalty cho khach hang
- Output can co:
	- Tich diem theo booking hoan tat.
	- Lich su diem va cap hang co ban.
	- Hien thi diem tren dashboard khach hang.
- File du kien tac dong:
	- models/KhachHang.php + model loyalty moi
	- controllers/KhachHangController.php
	- views/khach_hang/dashboard.php
- Acceptance criteria:
	- Diem tang dung khi booking hoan tat.
	- Co log lich su diem, truy vet duoc.
- Prompt mau cho AI:
	- "Tao loyalty system co ban cho khach hang: tich diem khi booking hoan tat, cap hang thanh vien, va hien thi diem trong dashboard. Them lich su diem de doi soat."

10. C3 - Cong self-service thanh toan/hoa don
- Output can co:
	- Trang lich su thanh toan cua khach.
	- Trang tai hoa don PDF.
	- Trang tra cuu trang thai payment.
- File du kien tac dong:
	- controllers/KhachHangController.php
	- controllers/InvoicePDFController.php
	- views/khach_hang/*
- Acceptance criteria:
	- Khach chi xem duoc du lieu cua chinh minh.
	- Tai PDF thanh cong va co phan quyen dung.
- Prompt mau cho AI:
	- "Bo sung khu vuc self-service cho khach hang: lich su thanh toan, tai hoa don PDF, va tra cuu trang thai payment. Bat buoc phan quyen dung theo user dang nhap."

## 12) Mau template giao viec cho AI (copy nhanh)

Su dung template nay de giao task cho AI theo tung PR nho:

""
Ban la ky su PHP cho du an MVC thu cong.

Muc tieu:
- [ghi ro task, vd: chuan hoa phan quyen route admin]

Pham vi file duoc sua:
- [liet ke file/folder duoc phep sua]

Rang buoc:
- Khong doi URL act hien co.
- Khong doi schema cu neu chua co migration di kem.
- Khong sua giao dien ngoai pham vi task.

Yeu cau ket qua:
- Liet ke file da sua va ly do.
- Tao test/checklist test thu cong.
- Bao cao rui ro regression va cach rollback.

Acceptance criteria:
- [3-5 tieu chi nghiem thu cu the]
""

## 13) Cach dung an toan khi dua task cho AI

1. Chi giao 1 task nho moi lan (toi da 3-5 file neu co the).
2. Yeu cau AI in ro truoc/sau hanh vi nghiep vu.
3. Luon yeu cau test/checklist sau moi task.
4. Merge theo PR nho, khong gom nhieu domain trong 1 PR.
5. Neu dung migration, bat buoc co rollback notes.

## 14) Chuc nang phat trien them co tinh thuc te cao

Phan nay tap trung vao tinh nang co tac dong truc tiep den doanh thu, giam loi van hanh, va tang trai nghiem khach hang.

### 14.1 Nhom doanh thu (Revenue)

1. Dynamic pricing theo mua/ngay le/so nguoi
- Gia tri: tang bien loi nhuan va linh hoat ban gia.
- Do kho: Trung binh.
- KPI: tang doanh thu trung binh moi booking 5-12%.

2. Upsell goi dich vu bo sung sau dat tour
- Vi du: bao hiem, xe dua don, nang cap phong.
- Do kho: Trung binh.
- KPI: ty le mua them >= 15% booking.

3. Loyalty + voucher tai dat
- Tich diem khi hoan tat tour, doi voucher cho lan sau.
- Do kho: Trung binh.
- KPI: tang ty le quay lai 10-20%.

### 14.2 Nhom van hanh (Operations)

4. Auto-assign HDV theo lich trong + ky nang
- Gia tri: giam xung dot lich va thao tac tay.
- Do kho: Trung binh.
- KPI: giam 50% thoi gian dieu pho iHDV.

5. Canh bao overbooking theo thoi gian thuc
- Kiem tra so cho con lai ngay luc dat.
- Do kho: Thap-Trung binh.
- KPI: 0 su co overbooking do he thong.

6. Doi soat payment tu dong hang ngay
- Tu dong danh dau lech trang thai payment/finance.
- Do kho: Trung binh.
- KPI: giam 70% ticket doi soat thu cong.

### 14.3 Nhom trai nghiem khach hang (CX)

7. Cong self-service cho khach
- Xem booking, payment, tai hoa don PDF, lich khoi hanh.
- Do kho: Trung binh.
- KPI: giam 30% yeu cau CSKH lap lai.

8. Check-in online truoc chuyen di
- Khach nhap thong tin truoc ngay khoi hanh.
- Do kho: Trung binh.
- KPI: giam thoi gian check-in tai diem tap trung 40%.

9. Nhac viec truoc chuyen di (checklist tu dong)
- Giay to, hanh ly, gio tap trung, luu y tour.
- Do kho: Thap.
- KPI: giam no-show va giam cuoc goi hoi thong tin.

## 15) Cac buoc tu dong hoa nen lam ngay

### 15.1 Tu dong hoa nghiep vu

1. Auto gui hoa don sau payment ThanhCong
- Trigger: payment callback/webhook thanh cong.
- Action: tao PDF -> gui mail -> ghi log ket qua.

2. Auto nhac thanh toan coc
- Trigger: booking ChoXacNhan sap den han coc.
- Action: gui nhac qua email (sau nay co the them Zalo/SMS).

3. Auto nhac cong no HDV/Nha cung cap
- Trigger: den han thanh toan.
- Action: tao thong bao admin + email nhac.

4. Auto dong ticket doi soat payment
- Trigger: sau khi doi soat khop.
- Action: cap nhat trang thai warning -> resolved.

### 15.2 Tu dong hoa ky thuat

5. Auto run migration khi deploy
- Co che: chay scripts/migrate.php up trong release step.

6. Auto backup DB hang dem + verify restore
- Co che: dump DB theo lich + test restore tren DB tam.

7. Auto smoke test tren CI
- Trigger: moi pull request.
- Scope: login, booking, payment callback, webhook.

8. Auto alert loi quan trong
- Trigger: webhook fail, payment mismatch, mail fail lien tiep.
- Kenh: Telegram/Slack/Email cho admin.

## 16) Uu tien de xuat theo ROI (lam theo thu tu)

1. Auto gui hoa don sau payment ThanhCong.
2. Auto nhac coc chua thanh toan.
3. Doi soat payment tu dong + canh bao lech.
4. Cong self-service payment/hoa don cho khach.
5. Auto-assign HDV co ban theo lich trong.
6. CI smoke test + migration auto khi deploy.

## 17) Goi task san sang giao AI (copy nhanh)

### Goi P1 - Auto gui hoa don
- Prompt:
	- "Bo sung luong tu dong tao va gui hoa don PDF ngay khi payment ThanhCong (tu callback va webhook). Neu gui mail that bai, ghi log va tao co che retry toi da 3 lan. Giu nguyen route hien tai."
- Acceptance criteria:
	- Payment thanh cong => co invoice PDF + co log gui mail.
	- Retry hoat dong khi lan gui dau that bai.

### Goi P2 - Nhac coc tu dong
- Prompt:
	- "Tao job dinh ky nhac thanh toan coc cho booking gan den han. Gui email theo template va ghi log ket qua. Tranh gui trung trong cung 24h cho cung booking."
- Acceptance criteria:
	- Booking den han coc duoc nhac dung lich.
	- Khong gui lap trong 24h.

### Goi P3 - Doi soat payment tu dong
- Prompt:
	- "Tao job doi soat payment hang ngay, phat hien cac truong hop lech giua payments va giao_dich_tai_chinh, tao danh sach warning va cho phep admin repair nhanh."
- Acceptance criteria:
	- Co danh sach warning moi ngay.
	- Co thao tac repair va co audit log.

### Goi P4 - Self-service cho khach hang
- Prompt:
	- "Bo sung trang self-service cho khach: lich su thanh toan, tai hoa don PDF, va trang thai payment. Bat buoc phan quyen de khach chi thay du lieu cua minh."
- Acceptance criteria:
	- Khach dang nhap xem duoc payment cua minh.
	- Khong xem duoc du lieu cua user khac.

### Goi P5 - CI smoke test
- Prompt:
	- "Thiet lap CI co ban: check syntax PHP, chay smoke tests cho login/booking/payment/webhook, va fail pipeline neu co test fail."
- Acceptance criteria:
	- Moi PR deu chay pipeline.
	- Pipeline fail khi test fail.

## 18) KPI do luong hieu qua tu dong hoa

1. Ty le xu ly booking khong can can thiep tay.
2. Thoi gian trung binh tu payment thanh cong den gui hoa don.
3. Ty le warning payment duoc resolve trong 24h.
4. So ticket CSKH lien quan den thanh toan/hoa don moi tuan.
5. Ty le loi phat hien truoc deploy qua CI.
