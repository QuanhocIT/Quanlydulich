#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Scheduler for all automation jobs (APScheduler-based)
Thay thế cho Task Scheduler/Cron

Chạy:
    python scripts/scheduler.py

Các jobs được lên lịch:
    - Email queue: mỗi 1 phút
    - Admin automation: mỗi 5 phút (riêng departure_readiness: 15 phút)
    - Daily KPI summary: mỗi ngày 00:05
    - Database backup: hàng ngày lúc 2:00 sáng
"""

import logging
import sys
from pathlib import Path

from apscheduler.schedulers.background import BackgroundScheduler
from apscheduler.triggers.cron import CronTrigger
from apscheduler.triggers.interval import IntervalTrigger

# Add project root to path
sys.path.insert(0, str(Path(__file__).parent.parent))

from scripts.process_email_queue import main as process_email_queue
from scripts.run_admin_automation import AdminAutomationService
from scripts.backup_db import main as backup_db

logging.basicConfig(
    level=logging.INFO,
    format='[%(asctime)s] %(levelname)s: %(message)s'
)
logger = logging.getLogger(__name__)


def email_queue_job():
    """Process email queue."""
    try:
        logger.info('🔔 Starting email queue processor...')
        process_email_queue()
    except Exception as e:
        logger.error(f'Email queue job error: {e}')


def admin_automation_job(job_name: str = 'all'):
    """Run admin automation job."""
    try:
        logger.info(f'⚙️  Starting admin automation: {job_name}...')
        service = AdminAutomationService()
        result = service.run_job(job_name)
        logger.info(f'✅ {job_name}: {result}')
    except Exception as e:
        logger.error(f'Admin automation job error: {e}')


def backup_job():
    """Backup database."""
    try:
        logger.info('💾 Starting database backup...')
        backup_db()
    except Exception as e:
        logger.error(f'Backup job error: {e}')


def start_scheduler():
    """Start background scheduler."""
    scheduler = BackgroundScheduler(daemon=True)

    # Email queue: every 1 minute
    scheduler.add_job(
        email_queue_job,
        IntervalTrigger(minutes=1),
        id='email_queue_processor',
        name='Email Queue Processor',
        replace_existing=True,
        misfire_grace_time=60,
    )
    logger.info('📧 Scheduled: Email queue processor (every 1 minute)')

    # Admin automation jobs: every 5 minutes
    automation_jobs_5m = [
        'sla_tour_requests',
        'booking_priority',
        'reconcile_digest',
        'self_heal_pending_payments',
        'webhook_anomaly',
        'payment_anomaly_alert',
        'debt_reminder',
        'tour_health_score',
    ]

    for job_name in automation_jobs_5m:
        scheduler.add_job(
            admin_automation_job,
            IntervalTrigger(minutes=5),
            args=[job_name],
            id=f'automation_{job_name}',
            name=f'Admin Automation: {job_name}',
            replace_existing=True,
            misfire_grace_time=300,
        )
    logger.info(f'⚙️  Scheduled: {len(automation_jobs_5m)} admin automation jobs (every 5 minutes)')

    # Departure readiness reminders: every 15 minutes
    scheduler.add_job(
        admin_automation_job,
        IntervalTrigger(minutes=15),
        args=['departure_readiness'],
        id='automation_departure_readiness_15m',
        name='Admin Automation: departure_readiness (15m)',
        replace_existing=True,
        misfire_grace_time=600,
    )
    logger.info('🧭 Scheduled: departure readiness reminders (every 15 minutes)')

    # Daily KPI summary: 00:05 AM daily (for previous day snapshot)
    scheduler.add_job(
        admin_automation_job,
        CronTrigger(hour=0, minute=5),
        args=['daily_kpi_summary'],
        id='automation_daily_kpi_summary',
        name='Admin Automation: daily_kpi_summary',
        replace_existing=True,
        misfire_grace_time=7200,
    )
    logger.info('📊 Scheduled: daily KPI summary (daily at 00:05)')

    # Database backup: 2:00 AM daily
    scheduler.add_job(
        backup_job,
        CronTrigger(hour=2, minute=0),
        id='database_backup',
        name='Database Backup',
        replace_existing=True,
        misfire_grace_time=3600,
    )
    logger.info('💾 Scheduled: Database backup (daily at 2:00 AM)')

    # Start scheduler
    scheduler.start()
    logger.info('✅ Scheduler started. Press Ctrl+C to stop.')

    try:
        # Keep the scheduler running
        while True:
            import time
            time.sleep(1)
    except KeyboardInterrupt:
        logger.info('Shutting down scheduler...')
        scheduler.shutdown()
        sys.exit(0)


if __name__ == '__main__':
    start_scheduler()
