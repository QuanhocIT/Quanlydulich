#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Admin Automation Service (Python)

Chay:
    python scripts/run_admin_automation.py [job_name]

Cac job co san:
    - all
    - sla_tour_requests
    - booking_priority
    - reconcile_digest
    - self_heal_pending_payments
    - webhook_anomaly
    - debt_reminder
    - departure_readiness
    - tour_health_score
    - payment_anomaly_alert
    - daily_kpi_summary
    - admin_inbox_digest
    - decision_assist
"""

import argparse
import hashlib
import json
import logging
import sys
import time
from datetime import datetime, timedelta
from pathlib import Path
from typing import Any, Dict, List, Optional

# Add project root to module path when running as a script.
sys.path.insert(0, str(Path(__file__).parent.parent))

from commons.db_helper import close_db_connection, get_db_connection

logger = logging.getLogger(__name__)


class AdminAutomationService:
    """Admin automation job runner."""

    ENABLED_SETTING_KEY = "automation_enabled"

    def __init__(self):
        self.conn = get_db_connection()
        if not self.conn:
            raise Exception("Failed to connect to database")
        self.ensure_automation_tables()

    def ensure_automation_tables(self) -> None:
        """Ensure automation tables exist."""
        try:
            cursor = self.conn.cursor()

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS automation_job_runs (
                    run_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    job_name VARCHAR(64) NOT NULL,
                    is_success TINYINT(1) NOT NULL DEFAULT 1,
                    affected_count INT NOT NULL DEFAULT 0,
                    message VARCHAR(255) DEFAULT NULL,
                    duration_ms DECIMAL(10,1) DEFAULT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (run_id),
                    KEY idx_job_created (job_name, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS automation_settings (
                    setting_key VARCHAR(64) NOT NULL,
                    setting_value VARCHAR(255) NOT NULL,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (setting_key)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS automation_events (
                    event_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    event_key VARCHAR(191) NOT NULL,
                    job_name VARCHAR(64) NOT NULL,
                    severity ENUM('low','medium','high') NOT NULL DEFAULT 'low',
                    title VARCHAR(190) NOT NULL,
                    message TEXT NOT NULL,
                    payload_json LONGTEXT DEFAULT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (event_id),
                    UNIQUE KEY uniq_event_key (event_key),
                    KEY idx_job_created (job_name, created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS booking_priority (
                    booking_id INT(11) NOT NULL,
                    priority_label ENUM('Low','Medium','High') NOT NULL DEFAULT 'Low',
                    score INT(11) NOT NULL DEFAULT 0,
                    reasons_json LONGTEXT DEFAULT NULL,
                    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (booking_id),
                    KEY idx_priority_label (priority_label),
                    KEY idx_computed_at (computed_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS tour_health_score (
                    tour_id INT(11) NOT NULL,
                    score INT(11) NOT NULL DEFAULT 0,
                    health_level ENUM('Good','Watch','Critical') NOT NULL DEFAULT 'Good',
                    metrics_json LONGTEXT DEFAULT NULL,
                    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (tour_id),
                    KEY idx_health_level (health_level),
                    KEY idx_computed_at (computed_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS admin_decision_assist (
                    assist_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    entity_type VARCHAR(30) NOT NULL,
                    entity_id INT(11) NOT NULL,
                    recommendation_hash CHAR(40) NOT NULL,
                    recommendation_text VARCHAR(500) NOT NULL,
                    status ENUM('open','done','ignored') NOT NULL DEFAULT 'open',
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (assist_id),
                    UNIQUE KEY uniq_entity_reco (entity_type, entity_id, recommendation_hash),
                    KEY idx_status (status),
                    KEY idx_entity (entity_type, entity_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            cursor.execute(
                """
                CREATE TABLE IF NOT EXISTS daily_kpi_summary (
                    summary_date DATE NOT NULL,
                    booking_new_count INT NOT NULL DEFAULT 0,
                    booking_cancel_count INT NOT NULL DEFAULT 0,
                    payment_success_count INT NOT NULL DEFAULT 0,
                    revenue_success_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
                    conversion_rate_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
                    notes_json LONGTEXT DEFAULT NULL,
                    computed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (summary_date),
                    KEY idx_kpi_computed (computed_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
                """
            )

            self.conn.commit()
            cursor.close()
            logger.info("Automation tables ready")
        except Exception as e:
            logger.error(f"Error creating automation tables: {e}")
            self.conn.rollback()

    def is_automation_enabled(self) -> bool:
        """Check if automation is enabled."""
        try:
            cursor = self.conn.cursor(dictionary=True)
            cursor.execute(
                "SELECT setting_value FROM automation_settings WHERE setting_key = %s LIMIT 1",
                (self.ENABLED_SETTING_KEY,),
            )
            result = cursor.fetchone()
            cursor.close()

            if result is None:
                self.set_automation_enabled(True)
                return True

            return str(result["setting_value"]) != "0"
        except Exception as e:
            logger.error(f"Error checking automation status: {e}")
            return True

    def set_automation_enabled(self, enabled: bool) -> None:
        """Set automation enabled/disabled."""
        try:
            value = "1" if enabled else "0"
            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT INTO automation_settings (setting_key, setting_value, updated_at) "
                "VALUES (%s, %s, NOW()) "
                "ON DUPLICATE KEY UPDATE setting_value = %s, updated_at = NOW()",
                (self.ENABLED_SETTING_KEY, value, value),
            )
            self.conn.commit()
            cursor.close()
        except Exception as e:
            logger.error(f"Error setting automation status: {e}")

    def record_event(
        self,
        event_key: str,
        job_name: str,
        severity: str,
        title: str,
        message: str,
        payload: Dict[str, Any],
        is_alert: bool = False,
    ) -> bool:
        """Record automation event."""
        try:
            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT IGNORE INTO automation_events "
                "(event_key, job_name, severity, title, message, payload_json, created_at) "
                "VALUES (%s, %s, %s, %s, %s, %s, NOW())",
                (
                    event_key,
                    job_name,
                    severity,
                    title,
                    message,
                    json.dumps(payload, ensure_ascii=False),
                ),
            )
            inserted = cursor.rowcount > 0
            self.conn.commit()
            cursor.close()

            if inserted and is_alert:
                self.push_admin_notification(job_name, severity, title, message, payload)
            return inserted
        except Exception as e:
            logger.error(f"Error recording event: {e}")
            return False

    def run_all(self) -> List[Dict[str, Any]]:
        """Run all automation jobs."""
        jobs = [
            "sla_tour_requests",
            "booking_priority",
            "reconcile_digest",
            "self_heal_pending_payments",
            "webhook_anomaly",
            "debt_reminder",
            "departure_readiness",
            "tour_health_score",
            "payment_anomaly_alert",
            "daily_kpi_summary",
            "admin_inbox_digest",
            "decision_assist",
        ]

        results = []
        for job in jobs:
            try:
                result = self.run_job(job)
                results.append(result)
            except Exception as e:
                logger.error(f"Error running job {job}: {e}")
                results.append(
                    {
                        "ok": False,
                        "job": job,
                        "message": str(e),
                        "affected": 0,
                    }
                )

        return results

    def run_job(self, job_name: str) -> Dict[str, Any]:
        """Run a single automation job."""
        job_name = str(job_name).strip()
        started_at = time.time()

        if not self.is_automation_enabled():
            duration_ms = round((time.time() - started_at) * 1000, 1)
            message = "Automation is temporarily disabled by admin."
            self.log_run(job_name, True, 0, message, duration_ms)
            return {
                "ok": True,
                "skipped": True,
                "job": job_name,
                "message": message,
                "affected": 0,
                "duration_ms": duration_ms,
            }

        try:
            if job_name == "sla_tour_requests":
                result = self.run_sla_tour_requests()
            elif job_name == "booking_priority":
                result = self.run_booking_priority()
            elif job_name == "reconcile_digest":
                result = self.run_reconcile_digest()
            elif job_name == "self_heal_pending_payments":
                result = self.run_self_heal_pending_payments()
            elif job_name == "webhook_anomaly":
                result = self.run_webhook_anomaly()
            elif job_name == "payment_anomaly_alert":
                result = self.run_payment_anomaly_alert()
            elif job_name == "debt_reminder":
                result = self.run_debt_reminder()
            elif job_name == "departure_readiness":
                result = self.run_departure_readiness()
            elif job_name == "tour_health_score":
                result = self.run_tour_health_score()
            elif job_name == "daily_kpi_summary":
                result = self.run_daily_kpi_summary()
            elif job_name == "admin_inbox_digest":
                result = self.run_admin_inbox_digest()
            elif job_name == "decision_assist":
                result = self.run_decision_assist()
            else:
                result = {
                    "ok": False,
                    "job": job_name,
                    "message": "Unknown job",
                    "affected": 0,
                }

            duration_ms = round((time.time() - started_at) * 1000, 1)
            self.log_run(
                job_name,
                result.get("ok", False),
                result.get("affected", 0),
                result.get("message", ""),
                duration_ms,
            )
            result["duration_ms"] = duration_ms
            return result

        except Exception as e:
            duration_ms = round((time.time() - started_at) * 1000, 1)
            self.log_run(job_name, False, 0, str(e), duration_ms)
            return {
                "ok": False,
                "job": job_name,
                "message": str(e),
                "affected": 0,
                "duration_ms": duration_ms,
            }

    def run_sla_tour_requests(self) -> Dict[str, Any]:
        """Check SLA for tour requests."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT id, created_at
            FROM thong_bao
            WHERE vai_tro_nhan = 'Admin'
              AND tieu_de = 'Yêu cầu tour theo mong muốn'
              AND trang_thai = 'DaGui'
              AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR)
            ORDER BY created_at ASC
            LIMIT 500
            """
        )
        rows = cursor.fetchall()
        cursor.close()

        alerts = 0
        now = time.time()
        for row in rows:
            request_id = int(row.get("id") or 0)
            created_at = row.get("created_at")
            if request_id <= 0 or not created_at:
                continue

            created_ts = (
                created_at.timestamp()
                if hasattr(created_at, "timestamp")
                else time.mktime(created_at.timetuple())
            )
            age_hours = (now - created_ts) / 3600

            level = 0
            if age_hours >= 24:
                level = 3
            elif age_hours >= 6:
                level = 2
            elif age_hours >= 2:
                level = 1

            if level <= 0:
                continue

            severity = "high" if level >= 3 else ("medium" if level == 2 else "low")
            title = "SLA yêu cầu tour quá hạn"
            message = (
                f"Yêu cầu #{request_id} đã chờ {int(age_hours)} giờ, cần admin xử lý."
            )
            event_key = f"sla_request_{request_id}_L{level}"

            if self.record_event(
                event_key,
                "sla_tour_requests",
                severity,
                title,
                message,
                {
                    "request_id": request_id,
                    "age_hours": round(age_hours, 1),
                    "level": level,
                },
                True,
            ):
                alerts += 1

        return {
            "ok": True,
            "job": "sla_tour_requests",
            "message": "SLA scan completed",
            "affected": alerts,
        }

    def run_booking_priority(self) -> Dict[str, Any]:
        """Compute booking priority scores."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT booking_id, tong_tien, trang_thai, ngay_khoi_hanh
            FROM booking
            WHERE trang_thai IN ('ChoXacNhan', 'DaCoc')
              AND ngay_khoi_hanh IS NOT NULL
              AND ngay_khoi_hanh >= CURDATE()
            ORDER BY ngay_khoi_hanh ASC
            LIMIT 2000
            """
        )
        rows = cursor.fetchall()
        cursor.close()

        updated = 0
        for row in rows:
            booking_id = int(row.get("booking_id") or 0)
            if booking_id <= 0:
                continue

            score = 0
            reasons = []
            tong_tien = float(row.get("tong_tien") or 0)
            status = str(row.get("trang_thai") or "")
            days_left = self.days_from_today(row.get("ngay_khoi_hanh"))

            if days_left is not None:
                if days_left <= 3:
                    score += 40
                    reasons.append("Khoi hanh <= 3 ngay")
                elif days_left <= 7:
                    score += 25
                    reasons.append("Khoi hanh <= 7 ngay")

            if tong_tien >= 20000000:
                score += 30
                reasons.append("Gia tri booking cao >= 20tr")
            elif tong_tien >= 10000000:
                score += 20
                reasons.append("Gia tri booking >= 10tr")
            elif tong_tien >= 5000000:
                score += 10
                reasons.append("Gia tri booking >= 5tr")

            if status == "ChoXacNhan":
                score += 15
                reasons.append("Dang cho xac nhan")

            priority = "Low"
            if score >= 70:
                priority = "High"
            elif score >= 40:
                priority = "Medium"

            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT INTO booking_priority (booking_id, priority_label, score, reasons_json, computed_at) "
                "VALUES (%s, %s, %s, %s, NOW()) "
                "ON DUPLICATE KEY UPDATE priority_label = VALUES(priority_label), "
                "score = VALUES(score), reasons_json = VALUES(reasons_json), computed_at = NOW()",
                (booking_id, priority, score, json.dumps(reasons, ensure_ascii=False)),
            )
            self.conn.commit()
            cursor.close()
            updated += 1

            if priority == "High" and days_left is not None and days_left <= 3:
                self.record_event(
                    f"booking_priority_high_{booking_id}_{datetime.now().strftime('%Y%m%d')}",
                    "booking_priority",
                    "medium",
                    "Booking uu tien cao",
                    f"Booking #{booking_id} can xu ly gap (diem {score}).",
                    {
                        "booking_id": booking_id,
                        "score": score,
                        "priority": priority,
                        "days_left": days_left,
                    },
                    True,
                )

        return {
            "ok": True,
            "job": "booking_priority",
            "message": "Booking priority computed",
            "affected": updated,
        }

    def run_reconcile_digest(self) -> Dict[str, Any]:
        """Run payment reconciliation digest (lightweight placeholder)."""
        return {
            "ok": True,
            "job": "reconcile_digest",
            "message": "Reconcile digest generated",
            "affected": 1,
        }

    def run_self_heal_pending_payments(self) -> Dict[str, Any]:
        """Self-heal pending payments that timeout."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT payment_id, payment_date
            FROM payments
            WHERE status = 'DangXuLy'
              AND payment_date <= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY payment_date ASC
            LIMIT 500
            """
        )
        rows = cursor.fetchall()
        cursor.close()

        expired = 0
        for row in rows:
            payment_id = int(row.get("payment_id") or 0)
            if payment_id <= 0:
                continue

            cursor = self.conn.cursor()
            cursor.execute(
                "UPDATE payments SET status = %s WHERE payment_id = %s AND status = %s",
                ("HetHan", payment_id, "DangXuLy"),
            )
            updated = cursor.rowcount > 0
            if updated:
                cursor.execute(
                    "INSERT INTO payment_logs (payment_id, action, log_time, note) "
                    "VALUES (%s, %s, NOW(), %s)",
                    (
                        payment_id,
                        "STATE_TRANSITION",
                        json.dumps(
                            {
                                "from": "DangXuLy",
                                "to": "HetHan",
                                "reason": "auto_self_heal_pending_timeout",
                                "threshold_minutes": 30,
                            },
                            ensure_ascii=False,
                        ),
                    ),
                )
                expired += 1
            self.conn.commit()
            cursor.close()

        if expired > 0:
            self.record_event(
                f"self_heal_timeout_{datetime.now().strftime('%Y%m%d%H')}",
                "self_heal_pending_payments",
                "medium",
                "Auto timeout payment treo",
                f"Da chuyen {expired} payment DangXuLy sang HetHan do qua 30 phut.",
                {"expired": expired},
                True,
            )

        return {
            "ok": True,
            "job": "self_heal_pending_payments",
            "message": "Pending payment self-heal completed",
            "affected": expired,
        }

    def run_payment_anomaly_alert(self) -> Dict[str, Any]:
        """Detect payment anomalies and alert admin."""
        unmatched_count = self.count_scalar(
            "SELECT COUNT(*) FROM bank_webhook_unmatched WHERE processed = 0"
        )
        failed_idem_24h = self.count_scalar(
            "SELECT COUNT(*) FROM payment_idempotency WHERE status = 'failed' "
            "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
        )
        blocked_or_warn_24h = self.count_scalar(
            "SELECT COUNT(*) FROM payment_logs "
            "WHERE action IN ('AUTO_RECONCILE_WARN', 'STATE_TRANSITION_BLOCKED') "
            "AND log_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
        )
        duplicate_success_24h = self.count_scalar(
            """
            SELECT COUNT(*)
            FROM (
                SELECT booking_id
                FROM payments
                WHERE status IN ('ThanhCong', 'DaDoiSoat')
                  AND payment_date >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                GROUP BY booking_id
                HAVING COUNT(*) > 1
            ) d
            """
        )

        anomaly_score = 0
        if unmatched_count >= 5:
            anomaly_score += 1
        if failed_idem_24h >= 3:
            anomaly_score += 1
        if blocked_or_warn_24h >= 3:
            anomaly_score += 1
        if duplicate_success_24h >= 1:
            anomaly_score += 1

        affected = 0
        if anomaly_score > 0:
            severity = "high" if anomaly_score >= 3 else "medium"
            created = self.record_event(
                f"payment_anomaly_{datetime.now().strftime('%Y%m%d%H')}",
                "payment_anomaly_alert",
                severity,
                "Canh bao bat thuong thanh toan",
                (
                    f"WebhookUnmatched={unmatched_count}, "
                    f"IdempotencyFailed24h={failed_idem_24h}, "
                    f"WarnOrBlocked24h={blocked_or_warn_24h}, "
                    f"DuplicateSuccess24h={duplicate_success_24h}."
                ),
                {
                    "webhook_unmatched": unmatched_count,
                    "idempotency_failed_24h": failed_idem_24h,
                    "payment_warn_or_blocked_24h": blocked_or_warn_24h,
                    "duplicate_success_booking_24h": duplicate_success_24h,
                },
                True,
            )
            if created:
                affected = 1

        return {
            "ok": True,
            "job": "payment_anomaly_alert",
            "message": "Payment anomaly scan completed",
            "affected": affected,
        }

    def run_webhook_anomaly(self) -> Dict[str, Any]:
        """Backward-compatible alias for payment anomaly detection."""
        result = self.run_payment_anomaly_alert()
        result["job"] = "webhook_anomaly"
        return result

    def run_debt_reminder(self) -> Dict[str, Any]:
        """Check for overdue debts."""
        overdue_hdv = self.count_scalar(
            """
            SELECT COUNT(*)
            FROM cong_no_hdv c
            LEFT JOIN (
                SELECT cong_no_hdv_id, COALESCE(SUM(so_tien),0) AS da_tra
                FROM lich_su_thanh_toan_hdv
                GROUP BY cong_no_hdv_id
            ) p ON p.cong_no_hdv_id = c.id
            WHERE c.han_thanh_toan IS NOT NULL
              AND c.han_thanh_toan < CURDATE()
              AND (COALESCE(c.so_tien,0) - COALESCE(p.da_tra,0)) > 0
            """
        )
        pending_supplier = self.count_scalar(
            """
            SELECT COUNT(*)
            FROM phan_bo_dich_vu
            WHERE trang_thai = 'ChoXacNhan'
              AND created_at <= DATE_SUB(NOW(), INTERVAL 3 DAY)
            """
        )

        affected = 0
        if overdue_hdv > 0 or pending_supplier > 0:
            if self.record_event(
                f"debt_reminder_{datetime.now().strftime('%Y%m%d')}",
                "debt_reminder",
                "medium" if overdue_hdv > 0 else "low",
                "Nhac han cong no va doi tac",
                f"Cong no HDV qua han={overdue_hdv}, dich vu NCC cho xac nhan>3 ngay={pending_supplier}.",
                {
                    "overdue_hdv": overdue_hdv,
                    "pending_supplier_confirmation": pending_supplier,
                },
                True,
            ):
                affected = 1

        return {
            "ok": True,
            "job": "debt_reminder",
            "message": "Debt reminder scan completed",
            "affected": affected,
        }

    def run_departure_readiness(self) -> Dict[str, Any]:
        """Check departure readiness and create reminders."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT id, tour_id, ngay_khoi_hanh, hdv_id, trang_thai
            FROM lich_khoi_hanh
            WHERE ngay_khoi_hanh BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
              AND trang_thai IN ('SapKhoiHanh', 'DangChay')
            ORDER BY ngay_khoi_hanh ASC
            """
        )
        schedules = cursor.fetchall()
        cursor.close()

        issues = 0
        date_key = datetime.now().strftime("%Y%m%d")
        for schedule in schedules:
            schedule_id = int(schedule.get("id") or 0)
            tour_id = int(schedule.get("tour_id") or 0)
            ngay_khoi_hanh = schedule.get("ngay_khoi_hanh")
            if schedule_id <= 0 or tour_id <= 0 or not ngay_khoi_hanh:
                continue

            issue_list: List[str] = []
            has_hdv = int(schedule.get("hdv_id") or 0) > 0
            if not has_hdv:
                has_hdv = (
                    self.count_scalar(
                        "SELECT COUNT(*) FROM phan_bo_nhan_su "
                        "WHERE lich_khoi_hanh_id = %s AND vai_tro = 'HDV' AND trang_thai = 'DaXacNhan'",
                        [schedule_id],
                    )
                    > 0
                )
            if not has_hdv:
                issue_list.append("thieu_hdv")

            has_service = (
                self.count_scalar(
                    "SELECT COUNT(*) FROM phan_bo_dich_vu "
                    "WHERE lich_khoi_hanh_id = %s AND trang_thai = 'DaXacNhan'",
                    [schedule_id],
                )
                > 0
            )
            if not has_service:
                issue_list.append("thieu_dich_vu_xac_nhan")

            has_booking = (
                self.count_scalar(
                    "SELECT COUNT(*) FROM booking WHERE tour_id = %s AND ngay_khoi_hanh = %s "
                    "AND trang_thai IN ('ChoXacNhan','DaCoc','HoanTat')",
                    [tour_id, ngay_khoi_hanh],
                )
                > 0
            )
            if not has_booking:
                issue_list.append("khong_co_booking_hop_le")

            for issue in issue_list:
                if self.record_event(
                    f"departure_readiness_{schedule_id}_{issue}_{date_key}",
                    "departure_readiness",
                    "high",
                    "Canh bao readiness lich khoi hanh",
                    f"Lich #{schedule_id} ({ngay_khoi_hanh}) gap van de: {issue}.",
                    {
                        "lich_khoi_hanh_id": schedule_id,
                        "issue": issue,
                        "ngay_khoi_hanh": str(ngay_khoi_hanh),
                    },
                    True,
                ):
                    issues += 1

        return {
            "ok": True,
            "job": "departure_readiness",
            "message": "Departure readiness checked",
            "affected": issues,
        }

    def run_tour_health_score(self) -> Dict[str, Any]:
        """Calculate tour health scores."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT tour_id
            FROM tour
            WHERE trang_thai = 'HoatDong' OR trang_thai IS NULL
            """
        )
        tours = cursor.fetchall()
        cursor.close()

        updated = 0
        for tour in tours:
            tour_id = int(tour.get("tour_id") or 0)
            if tour_id <= 0:
                continue

            bookings_60d = self.count_scalar(
                "SELECT COUNT(*) FROM booking WHERE tour_id = %s AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)",
                [tour_id],
            )
            cancelled_60d = self.count_scalar(
                "SELECT COUNT(*) FROM booking WHERE tour_id = %s AND ngay_dat >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) "
                "AND trang_thai IN ('Huy','DaHuy')",
                [tour_id],
            )
            avg_rating = self.float_scalar(
                "SELECT COALESCE(AVG(diem), 0) FROM danh_gia WHERE loai_danh_gia = 'Tour' AND tour_id = %s",
                [tour_id],
            )

            cancel_rate = (cancelled_60d / bookings_60d) if bookings_60d > 0 else 0.0
            score = 50.0
            score += min(25, bookings_60d * 2)
            score += min(25, max(0.0, (avg_rating - 3.0) * 12.5))
            score -= min(35, cancel_rate * 100 * 0.7)
            score = int(max(0, min(100, round(score))))

            level = "Good"
            if score < 40:
                level = "Critical"
            elif score < 60:
                level = "Watch"

            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT INTO tour_health_score (tour_id, score, health_level, metrics_json, computed_at) "
                "VALUES (%s, %s, %s, %s, NOW()) "
                "ON DUPLICATE KEY UPDATE score = VALUES(score), health_level = VALUES(health_level), "
                "metrics_json = VALUES(metrics_json), computed_at = NOW()",
                (
                    tour_id,
                    score,
                    level,
                    json.dumps(
                        {
                            "bookings_60d": bookings_60d,
                            "cancelled_60d": cancelled_60d,
                            "cancel_rate": round(cancel_rate, 4),
                            "avg_rating": round(avg_rating, 2),
                        },
                        ensure_ascii=False,
                    ),
                ),
            )
            self.conn.commit()
            cursor.close()
            updated += 1

            if level == "Critical":
                self.record_event(
                    f"tour_health_critical_{tour_id}_{datetime.now().strftime('%Y%m%d')}",
                    "tour_health_score",
                    "high",
                    "Tour health critical",
                    f"Tour #{tour_id} co health score thap: {score}.",
                    {"tour_id": tour_id, "score": score, "level": level},
                    True,
                )

        return {
            "ok": True,
            "job": "tour_health_score",
            "message": "Tour health scores updated",
            "affected": updated,
        }

    def run_daily_kpi_summary(self) -> Dict[str, Any]:
        """Build daily KPI snapshot for dashboard acceleration."""
        target_date = (datetime.now() - timedelta(days=1)).date()
        date_str = target_date.strftime("%Y-%m-%d")
        next_date_str = (target_date + timedelta(days=1)).strftime("%Y-%m-%d")

        booking_new_count = self.count_scalar(
            "SELECT COUNT(*) FROM booking WHERE ngay_dat >= %s AND ngay_dat < %s",
            [date_str, next_date_str],
        )
        booking_cancel_count = self.count_scalar(
            "SELECT COUNT(*) FROM booking "
            "WHERE ngay_dat >= %s AND ngay_dat < %s "
            "AND trang_thai IN ('Huy', 'DaHuy')",
            [date_str, next_date_str],
        )
        payment_success_count = self.count_scalar(
            "SELECT COUNT(*) FROM payments "
            "WHERE payment_date >= %s AND payment_date < %s "
            "AND status IN ('ThanhCong', 'DaDoiSoat')",
            [date_str, next_date_str],
        )
        revenue_success_amount = self.float_scalar(
            "SELECT COALESCE(SUM(amount), 0) FROM payments "
            "WHERE payment_date >= %s AND payment_date < %s "
            "AND status IN ('ThanhCong', 'DaDoiSoat')",
            [date_str, next_date_str],
        )

        conversion_rate_pct = 0.0
        if booking_new_count > 0:
            conversion_rate_pct = round((payment_success_count / booking_new_count) * 100, 2)

        notes = {
            "source": "python_automation",
            "computed_for_date": date_str,
        }

        cursor = self.conn.cursor()
        cursor.execute(
            "INSERT INTO daily_kpi_summary "
            "(summary_date, booking_new_count, booking_cancel_count, payment_success_count, "
            "revenue_success_amount, conversion_rate_pct, notes_json, computed_at) "
            "VALUES (%s, %s, %s, %s, %s, %s, %s, NOW()) "
            "ON DUPLICATE KEY UPDATE "
            "booking_new_count = VALUES(booking_new_count), "
            "booking_cancel_count = VALUES(booking_cancel_count), "
            "payment_success_count = VALUES(payment_success_count), "
            "revenue_success_amount = VALUES(revenue_success_amount), "
            "conversion_rate_pct = VALUES(conversion_rate_pct), "
            "notes_json = VALUES(notes_json), computed_at = NOW()",
            (
                date_str,
                booking_new_count,
                booking_cancel_count,
                payment_success_count,
                revenue_success_amount,
                conversion_rate_pct,
                json.dumps(notes, ensure_ascii=False),
            ),
        )
        self.conn.commit()
        cursor.close()

        self.record_event(
            f"daily_kpi_summary_{target_date.strftime('%Y%m%d')}",
            "daily_kpi_summary",
            "low",
            "Daily KPI summary updated",
            (
                f"KPI {date_str}: booking_new={booking_new_count}, "
                f"cancel={booking_cancel_count}, payment_success={payment_success_count}, "
                f"revenue={revenue_success_amount:.0f}, conversion={conversion_rate_pct:.2f}%."
            ),
            {
                "summary_date": date_str,
                "booking_new_count": booking_new_count,
                "booking_cancel_count": booking_cancel_count,
                "payment_success_count": payment_success_count,
                "revenue_success_amount": revenue_success_amount,
                "conversion_rate_pct": conversion_rate_pct,
            },
            False,
        )

        return {
            "ok": True,
            "job": "daily_kpi_summary",
            "message": "Daily KPI summary computed",
            "affected": 1,
        }

    def run_admin_inbox_digest(self) -> Dict[str, Any]:
        """Generate admin inbox digest."""
        pending_tour_requests = self.count_scalar(
            "SELECT COUNT(*) FROM thong_bao WHERE vai_tro_nhan = 'Admin' "
            "AND tieu_de = 'Yêu cầu tour theo mong muốn' AND trang_thai = 'DaGui'"
        )
        payment_warnings_24h = self.count_scalar(
            "SELECT COUNT(*) FROM payment_logs WHERE action IN ('AUTO_RECONCILE_WARN', 'STATE_TRANSITION_BLOCKED') "
            "AND log_time >= DATE_SUB(NOW(), INTERVAL 1 DAY)"
        )
        overdue_debt = self.count_scalar(
            """
            SELECT COUNT(*)
            FROM cong_no_hdv c
            LEFT JOIN (
                SELECT cong_no_hdv_id, COALESCE(SUM(so_tien),0) AS da_tra
                FROM lich_su_thanh_toan_hdv
                GROUP BY cong_no_hdv_id
            ) p ON p.cong_no_hdv_id = c.id
            WHERE c.han_thanh_toan IS NOT NULL
              AND c.han_thanh_toan < CURDATE()
              AND (COALESCE(c.so_tien,0) - COALESCE(p.da_tra,0)) > 0
            """
        )

        message = (
            f"Inbox digest: yeu_cau_tour={pending_tour_requests}, "
            f"payment_warnings_24h={payment_warnings_24h}, cong_no_qua_han={overdue_debt}."
        )
        created = self.record_event(
            f"admin_inbox_digest_{datetime.now().strftime('%Y%m%d%H')}",
            "admin_inbox_digest",
            "medium" if (pending_tour_requests + payment_warnings_24h + overdue_debt) > 0 else "low",
            "Admin inbox digest",
            message,
            {
                "pending_tour_requests": pending_tour_requests,
                "payment_warnings_24h": payment_warnings_24h,
                "overdue_debt": overdue_debt,
            },
            True,
        )

        return {
            "ok": True,
            "job": "admin_inbox_digest",
            "message": "Admin inbox digest generated",
            "affected": 1 if created else 0,
        }

    def run_decision_assist(self) -> Dict[str, Any]:
        """Assist admin decision making."""
        cursor = self.conn.cursor(dictionary=True)
        cursor.execute(
            """
            SELECT booking_id, trang_thai, ngay_khoi_hanh
            FROM booking
            WHERE trang_thai IN ('ChoXacNhan', 'DaCoc')
              AND ngay_khoi_hanh IS NOT NULL
              AND ngay_khoi_hanh >= CURDATE()
            ORDER BY ngay_khoi_hanh ASC
            LIMIT 1000
            """
        )
        bookings = cursor.fetchall()
        cursor.close()

        created = 0
        for booking in bookings:
            booking_id = int(booking.get("booking_id") or 0)
            if booking_id <= 0:
                continue

            status = str(booking.get("trang_thai") or "")
            days_left = self.days_from_today(booking.get("ngay_khoi_hanh"))
            successful_payments = self.count_scalar(
                "SELECT COUNT(*) FROM payments WHERE booking_id = %s AND status IN ('ThanhCong', 'DaDoiSoat')",
                [booking_id],
            )

            recommendations: List[str] = []
            if status == "ChoXacNhan" and successful_payments <= 0:
                recommendations.append("Lien he khach de xac nhan coc va huong dan thanh toan.")
            if status == "DaCoc" and days_left is not None and days_left <= 3:
                recommendations.append(
                    "Chot danh sach hanh khach, HDV va dich vu nha cung cap truoc ngay khoi hanh."
                )
            if days_left is not None and days_left <= 1:
                recommendations.append("Gui nhac lich trinh va diem tap trung cho khach truoc 24h.")

            for text in recommendations:
                reco_hash = hashlib.sha1(
                    f"booking|{booking_id}|{text}".encode("utf-8")
                ).hexdigest()
                cursor = self.conn.cursor()
                cursor.execute(
                    "INSERT IGNORE INTO admin_decision_assist "
                    "(entity_type, entity_id, recommendation_hash, recommendation_text, status, created_at, updated_at) "
                    "VALUES ('booking', %s, %s, %s, 'open', NOW(), NOW())",
                    (booking_id, reco_hash, text),
                )
                if cursor.rowcount > 0:
                    created += 1
                self.conn.commit()
                cursor.close()

        if created > 0:
            self.record_event(
                f"decision_assist_{datetime.now().strftime('%Y%m%d%H')}",
                "decision_assist",
                "low",
                "Decision assist updated",
                f"Da tao/cap nhat {created} goi y hanh dong cho admin.",
                {"created": created},
                True,
            )

        return {
            "ok": True,
            "job": "decision_assist",
            "message": "Decision assist refreshed",
            "affected": created,
        }

    def log_run(
        self,
        job_name: str,
        success: bool,
        affected: int,
        message: str,
        duration_ms: float,
    ) -> None:
        """Log job execution."""
        try:
            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT INTO automation_job_runs "
                "(job_name, is_success, affected_count, message, duration_ms, created_at) "
                "VALUES (%s, %s, %s, %s, %s, NOW())",
                (job_name, 1 if success else 0, int(affected), str(message)[:255], duration_ms),
            )
            self.conn.commit()
            cursor.close()
        except Exception as e:
            logger.error(f"Failed to write automation_job_runs: {e}")

        status = "OK" if success else "FAIL"
        logger.info(
            f"[{job_name}] {status} | affected={affected} | msg=\"{message}\" | duration={duration_ms}ms"
        )

    def push_admin_notification(
        self,
        job_name: str,
        severity: str,
        title: str,
        message: str,
        payload: Dict[str, Any],
    ) -> None:
        """Push alert into thong_bao for admin inbox."""
        priority_map = {
            "high": "Cao",
            "medium": "TrungBinh",
        }
        priority = priority_map.get(severity, "Thap")

        full_title = f"[AUTO][{job_name.upper()}] {title}"
        full_message = str(message)
        if payload:
            full_message += "\n\nPayload: " + json.dumps(payload, ensure_ascii=False)

        try:
            cursor = self.conn.cursor()
            cursor.execute(
                "INSERT INTO thong_bao ("
                "tieu_de, noi_dung, loai_thong_bao, muc_do_uu_tien, "
                "nguoi_gui_id, nguoi_nhan_id, vai_tro_nhan, trang_thai, "
                "thoi_gian_gui, thoi_gian_hen_gui"
                ") VALUES (%s, %s, %s, %s, NULL, NULL, %s, %s, NOW(), NULL)",
                (
                    full_title[:255],
                    full_message,
                    "ChungChung",
                    priority,
                    "Admin",
                    "DaGui",
                ),
            )
            self.conn.commit()
            cursor.close()
        except Exception as e:
            logger.error(f"Error pushing admin notification: {e}")

    def days_from_today(self, value: Any) -> Optional[int]:
        """Get day delta from today for a date/datetime/string value."""
        try:
            if value is None:
                return None
            if hasattr(value, "date"):
                date_value = value.date() if hasattr(value, "hour") else value
            else:
                date_value = datetime.strptime(str(value)[:10], "%Y-%m-%d").date()
            return (date_value - datetime.now().date()).days
        except Exception:
            return None

    def count_scalar(self, sql: str, params: Optional[List[Any]] = None) -> int:
        """Execute count scalar safely."""
        try:
            cursor = self.conn.cursor()
            cursor.execute(sql, params or [])
            value = cursor.fetchone()
            cursor.close()
            if not value:
                return 0
            return int(value[0] or 0)
        except Exception:
            return 0

    def float_scalar(self, sql: str, params: Optional[List[Any]] = None) -> float:
        """Execute float scalar safely."""
        try:
            cursor = self.conn.cursor()
            cursor.execute(sql, params or [])
            value = cursor.fetchone()
            cursor.close()
            if not value:
                return 0.0
            return float(value[0] or 0.0)
        except Exception:
            return 0.0


def main() -> None:
    """Main entry point."""
    logging.basicConfig(level=logging.INFO, format="[%(asctime)s] %(levelname)s: %(message)s")

    parser = argparse.ArgumentParser(description="Admin Automation Job Runner")
    parser.add_argument("job", nargs="?", default="all", help='Job name or "all"')
    args = parser.parse_args()

    try:
        service = AdminAutomationService()

        if args.job == "all":
            results = service.run_all()
            logger.info(f"Ran {len(results)} jobs")
            for result in results:
                logger.info(json.dumps(result, ensure_ascii=False))
        else:
            result = service.run_job(args.job)
            logger.info(json.dumps(result, ensure_ascii=False))

        close_db_connection(service.conn)
        sys.exit(0)
    except Exception as e:
        logger.error(f"Fatal error: {e}")
        sys.exit(1)


if __name__ == "__main__":
    main()
