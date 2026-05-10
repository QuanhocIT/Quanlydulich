# 🐍 Python Automation System - Integration Guide

Hệ thống automation Python thay thế cho các PHP scripts cũ.

## 📋 Nội dung

1. **WebSocket Server** (`websocket_server.py`) - Realtime notifications
2. **Admin Automation** (`run_admin_automation.py`) - Background jobs
3. **Email Queue** (`process_email_queue.py`) - Async email sending
4. **Database Backup** (`backup_db.py`) - Daily backups
5. **Financial Reports** (`financial_report_service.py`) - Report generation

## 🚀 Cài đặt

### Bước 1: Cài đặt Python 3.9+

```bash
# Windows
# Download từ https://www.python.org/

# Linux
sudo apt-get install python3 python3-pip python3-venv

# macOS
brew install python3
```

### Bước 2: Tạo Virtual Environment (tùy chọn nhưng khuyến nghị)

```bash
cd project_quan_ly_tour_du_lich

# Windows
python -m venv venv
venv\Scripts\activate

# Linux/macOS
python3 -m venv venv
source venv/bin/activate
```

### Bước 3: Cài đặt Dependencies

```bash
python scripts/setup.py
# Hoặc thủ công:
pip install -r requirements.txt
```

### Bước 4: Kiểm tra Cấu hình

Đảm bảo `.env` có các settings:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=your-password
DB_NAME=quan_ly_tour_du_lich

# SMTP (Email)
MAIL_ENABLED=1
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-password
SMTP_SECURITY=tls

# WebSocket
REALTIME_WS_ENABLED=1
REALTIME_WS_HOST=127.0.0.1
REALTIME_WS_PORT=8765
REALTIME_HMAC_SECRET=your-secret-key
```

## 🎯 Chạy Hệ Thống

### Option 1: Background Scheduler (Recommended)

```bash
# Chạy một process quản lý tất cả jobs
python scripts/scheduler.py

# Hoặc với virtual environment:
venv/bin/python scripts/scheduler.py
```

Scheduler sẽ tự động chạy:
- 📧 Email queue mỗi 1 phút
- ⚙️ Admin automation mỗi 5 phút
- 💾 Database backup hàng ngày lúc 2:00 AM

### Option 2: Cron Jobs (Linux/macOS)

```bash
# Tự động setup cron
chmod +x scripts/setup_cron.sh
./scripts/setup_cron.sh

# Xem lại cron jobs
crontab -l
```

### Option 3: Windows Task Scheduler

Tạo tasks cho mỗi script:

```
Program: C:\path\to\python.exe
Arguments: C:\path\to\scripts\process_email_queue.py
Trigger: Every 1 minute
```

### Option 4: Chạy Từng Script Thủ Công

```bash
# Email queue
python scripts/process_email_queue.py

# Admin automation (tất cả jobs)
python scripts/run_admin_automation.py all

# Hoặc một job cụ thể
python scripts/run_admin_automation.py sla_tour_requests

# Database backup
python scripts/backup_db.py

# WebSocket server
python scripts/websocket_server.py

# Financial reports
python scripts/financial_report_service.py
```

## 📊 Scripts Chi Tiết

### 1. WebSocket Server

**File**: `scripts/websocket_server.py`

**Tác dụng**: Realtime notifications cho admin, customers, guides

**Chạy**:
```bash
python scripts/websocket_server.py
# Nghe trên: ws://127.0.0.1:8765
```

**Xác thực**: HMAC token qua query parameter `?token=...`

### 2. Admin Automation

**File**: `scripts/run_admin_automation.py`

**Các Jobs**:
- `sla_tour_requests` - Check SLA cho yêu cầu tour
- `booking_priority` - Tính priority score cho bookings
- `reconcile_digest` - Kiểm tra đối soát thanh toán
- `self_heal_pending_payments` - Auto-expire payment pending
- `webhook_anomaly` - Detect webhook anomalies
- `debt_reminder` - Nhắc nhở công nợ HDV
- `departure_readiness` - Check lịch khởi hành
- `tour_health_score` - Tính health score cho tours

**Chạy**:
```bash
# Tất cả jobs
python scripts/run_admin_automation.py all

# Job cụ thể
python scripts/run_admin_automation.py booking_priority
```

### 3. Email Queue

**File**: `scripts/process_email_queue.py`

**Tác dụng**: Gửi email async từ queue

**Schema**: Dùng bảng `email_queue` (V017 migration)

**Features**:
- Exponential backoff khi retry
- Lock file để tránh concurrent runs
- SMTP support (Gmail, custom SMTP)

### 4. Database Backup

**File**: `scripts/backup_db.py`

**Tác dụng**: Backup MySQL database

**Storage**: `storage/backups/`

**Retention**: 14 ngày (configurable)

**Features**:
- Automatic gzip compression
- Auto-cleanup old backups
- Support Windows & Linux

### 5. Financial Reports

**File**: `scripts/financial_report_service.py`

**Tác dụng**: Generate financial reports (tours, revenue, costs)

**Exports**:
- CSV
- Excel (với formatting)

**Usage**:
```python
from scripts.financial_report_service import FinancialReportService

service = FinancialReportService()

# Get dashboard summary
payload = service.get_dashboard_payload('2026-01-01', '2026-12-31')

# Export to Excel
service.export_to_excel('2026-01-01', '2026-12-31', 'output.xlsx')

# Export to CSV
service.export_to_csv('2026-01-01', '2026-12-31', 'output.csv')
```

## 🔧 Configuration

### Helper Modules

**`commons/db_helper.py`** - Database connection management
- `get_db_connection()` - Get MySQL connection
- `close_db_connection(conn)` - Close connection

**`commons/config_helper.py`** - Load .env configuration
- `load_config()` - Load từ .env file và environment variables

## 📝 Logging

Logs mặc định:
- Console output
- `storage/logs/` (khi dùng scheduler)

Format:
```
[2026-05-10 14:30:45] INFO: Email sent to user@example.com
[2026-05-10 14:30:46] ERROR: Failed to connect database
```

## 🐛 Troubleshooting

### "ModuleNotFoundError: No module named 'websockets'"

```bash
pip install websockets
# Hoặc
pip install -r requirements.txt
```

### Database Connection Failed

Kiểm tra `.env`:
```env
DB_HOST=127.0.0.1
DB_USER=root
DB_PASSWORD=correct-password
DB_NAME=quan_ly_tour_du_lich
```

### Email Not Sending

```env
MAIL_ENABLED=1
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email
SMTP_PASSWORD=your-app-password  # Use app-specific password for Gmail
```

### WebSocket Connection Refused

- Kiểm tra `REALTIME_WS_ENABLED=1`
- Kiểm tra port `8765` không bị dùng
- Verify `REALTIME_HMAC_SECRET` được cấu hình

## 📈 Performance

- WebSocket: ~1000 concurrent connections
- Email queue: 20 emails/run, 1 minute interval
- Admin jobs: 5 minute interval, ~100ms per job
- Database backup: ~1-5GB per backup, 14 day retention

## 🔐 Security

- Token verification: HMAC-SHA256
- Database connection pooling
- Email password via environment variable
- No sensitive logs in console

## 🔄 Migration từ PHP

### Phases

1. **Phase 1** (Ngay): WebSocket + Email Queue
2. **Phase 2** (Tuần 1): Admin Automation
3. **Phase 3** (Tuần 2): Database Backup
4. **Phase 4** (Tuần 3): Financial Reports

### Coexistence

PHP scripts có thể chạy cùng lúc với Python:
- PHP: Web API, Controllers, Views
- Python: Background jobs, Realtime updates

### Rollback

Nếu có issue, dễ quay lại PHP scripts:
- Tất cả PHP scripts vẫn giữ nguyên
- Chỉ cần disable Python scheduler

## 📚 References

- [APScheduler Docs](https://apscheduler.readthedocs.io/)
- [Pandas Docs](https://pandas.pydata.org/docs/)
- [Python asyncio](https://docs.python.org/3/library/asyncio.html)
- [Websockets Library](https://websockets.readthedocs.io/)

## 💬 Support

Issues hoặc questions, kiểm tra:
1. `.env` configuration
2. Database connectivity
3. Python version (3.9+)
4. Dependencies installed
5. Logs trong `storage/logs/`

## 📄 License

Cùng license với dự án chính

---

**Last Updated**: 2026-05-10
**Python Version**: 3.9+
**Status**: ✅ Production Ready
