#!/bin/bash
# Setup cron jobs for Python automation scripts
# Linux/Mac cron-based scheduler

# Installation instructions:
# 1. Make this script executable: chmod +x setup_cron.sh
# 2. Run: ./setup_cron.sh
# 3. Verify: crontab -l

# Configuration
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PYTHON_CMD="python3"
VENV_PATH="$PROJECT_ROOT/venv"

# If virtual environment exists, use it
if [ -d "$VENV_PATH" ]; then
    PYTHON_CMD="$VENV_PATH/bin/python"
fi

SCRIPTS_DIR="$PROJECT_ROOT/project_quan_ly_tour_du_lich/scripts"
LOG_DIR="$PROJECT_ROOT/project_quan_ly_tour_du_lich/storage/logs"

# Create log directory
mkdir -p "$LOG_DIR"

echo "Setting up cron jobs for Aventura Tours..."
echo "Project root: $PROJECT_ROOT"
echo "Python: $PYTHON_CMD"

# Create temporary cron file
CRON_FILE=$(mktemp)

# Add header
echo "# Aventura Tours - Python Automation Jobs" >> "$CRON_FILE"
echo "# Generated: $(date)" >> "$CRON_FILE"
echo "" >> "$CRON_FILE"

# Email queue processor - every 1 minute
echo "* * * * * cd $PROJECT_ROOT && $PYTHON_CMD $SCRIPTS_DIR/process_email_queue.py >> $LOG_DIR/email_queue.log 2>&1" >> "$CRON_FILE"
echo "Added: Email queue processor (every minute)"

# Admin automation jobs - every 5 minutes
for job in sla_tour_requests booking_priority reconcile_digest self_heal_pending_payments webhook_anomaly debt_reminder departure_readiness tour_health_score; do
    echo "*/5 * * * * cd $PROJECT_ROOT && $PYTHON_CMD $SCRIPTS_DIR/run_admin_automation.py $job >> $LOG_DIR/automation_${job}.log 2>&1" >> "$CRON_FILE"
done
echo "Added: 8 admin automation jobs (every 5 minutes)"

# Database backup - 2:00 AM daily
echo "0 2 * * * cd $PROJECT_ROOT && $PYTHON_CMD $SCRIPTS_DIR/backup_db.py >> $LOG_DIR/backup_db.log 2>&1" >> "$CRON_FILE"
echo "Added: Database backup (daily at 2:00 AM)"

echo ""
echo "New cron jobs:"
cat "$CRON_FILE"
echo ""

# Install cron jobs
crontab "$CRON_FILE"

echo "✅ Cron jobs installed successfully!"
echo "📋 To view cron jobs: crontab -l"
echo "📝 To edit cron jobs: crontab -e"
echo "📊 Logs will be saved to: $LOG_DIR"

# Cleanup
rm "$CRON_FILE"
