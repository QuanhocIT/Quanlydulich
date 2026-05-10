# 🐍 Python Scripts Automation

Tất cả background jobs và automation scripts cho Aventura Tours.

## 📂 File Structure

```
scripts/
├── websocket_server.py          # WebSocket realtime notifications
├── run_admin_automation.py       # Admin automation jobs
├── process_email_queue.py        # Email queue processor
├── backup_db.py                  # Database backup
├── financial_report_service.py   # Financial report generator
├── scheduler.py                  # APScheduler for all jobs
├── setup.py                      # Environment setup
├── setup_cron.sh                 # Linux/Mac cron setup
└── requirements.txt              # Python dependencies

commons/
├── __init__.py
├── db_helper.py                  # Database connection helper
├── config_helper.py              # .env config loader
```

## 🚀 Quick Start

### 1. Setup Environment

```bash
python scripts/setup.py
```

This will:
- Check Python version
- Install dependencies
- Verify database connection
- Check configuration

### 2. Run Scheduler

```bash
python scripts/scheduler.py
```

Or use cron (Linux/macOS):

```bash
./scripts/setup_cron.sh
```

## 📋 Available Scripts

### WebSocket Server
```bash
python scripts/websocket_server.py
# Listens on ws://127.0.0.1:8765
```

### Admin Automation
```bash
# Run all jobs
python scripts/run_admin_automation.py all

# Run specific job
python scripts/run_admin_automation.py booking_priority
```

### Email Queue
```bash
python scripts/process_email_queue.py
```

### Database Backup
```bash
python scripts/backup_db.py
```

### Financial Reports
```bash
python scripts/financial_report_service.py
```

## 🛠️ Configuration

Edit `.env`:

```env
# Database
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=password
DB_NAME=quan_ly_tour_du_lich

# Email
MAIL_ENABLED=1
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=email@gmail.com
SMTP_PASSWORD=password

# WebSocket
REALTIME_WS_ENABLED=1
REALTIME_WS_HOST=127.0.0.1
REALTIME_WS_PORT=8765
REALTIME_HMAC_SECRET=secret
```

## 📊 Job Schedule

| Job | Interval | File |
|-----|----------|------|
| Email Queue | Every 1 min | process_email_queue.py |
| SLA Check | Every 5 min | run_admin_automation.py sla_tour_requests |
| Booking Priority | Every 5 min | run_admin_automation.py booking_priority |
| Reconcile | Every 5 min | run_admin_automation.py reconcile_digest |
| Webhook Anomaly | Every 5 min | run_admin_automation.py webhook_anomaly |
| Debt Reminder | Every 5 min | run_admin_automation.py debt_reminder |
| Departure Check | Every 5 min | run_admin_automation.py departure_readiness |
| Tour Health | Every 5 min | run_admin_automation.py tour_health_score |
| Database Backup | Daily 2 AM | backup_db.py |

## 🔍 Troubleshooting

### Import Error
```bash
pip install -r requirements.txt
```

### Database Connection Failed
Check `.env` settings and MySQL running

### WebSocket Not Connecting
- Verify `REALTIME_WS_ENABLED=1`
- Check port `8765` available
- Verify HMAC secret configured

## 📚 For More Information

See `PYTHON_INTEGRATION.md` for detailed documentation.

---
**Status**: ✅ Production Ready
**Python**: 3.9+
**Last Updated**: 2026-05-10
