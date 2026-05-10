# CHECKLIST QA PERF PHASE 2

## Scope
- Validate performance gains after V020 indexes and KPI query rewrite.
- Measure both DB query latency and endpoint latency.

## Prerequisites
- App is running on local web server.
- Database schema is up to date.
- Test with representative data volume.

## Step 1 - Apply migration
1. Run migration up:
   - `php scripts/migrate.php up`
2. Verify V020 applied:
   - Check output contains `Applying V020 - hot query indexes phase2 ... DONE`.

## Step 2 - Verify indexes exist
Run in MySQL:

```sql
SHOW INDEX FROM booking;
SHOW INDEX FROM thong_bao;
SHOW INDEX FROM giao_dich_tai_chinh;
SHOW INDEX FROM payment_logs;
SHOW INDEX FROM payment_idempotency;
```

Expected new indexes:
- booking: idx_booking_status_ngaydat, idx_booking_lich_status, idx_booking_tour_ngay_ngaydat
- thong_bao: idx_tb_role_title_status_created
- giao_dich_tai_chinh: idx_gd_loai_ngay
- payment_logs: idx_pl_action_logtime
- payment_idempotency: idx_pidem_status_created

## Step 3 - DB plan check (EXPLAIN)
Run EXPLAIN for hot queries:

```sql
EXPLAIN SELECT COUNT(*)
FROM booking
WHERE trang_thai = 'ChoXacNhan';

EXPLAIN SELECT COALESCE(SUM(so_tien), 0)
FROM giao_dich_tai_chinh
WHERE loai = 'Thu'
  AND ngay_giao_dich >= DATE_SUB(NOW(), INTERVAL 12 MONTH);

EXPLAIN SELECT COUNT(*)
FROM thong_bao
WHERE vai_tro_nhan = 'Admin'
  AND tieu_de = 'Yêu cầu tour theo mong muốn'
  AND trang_thai = 'DaGui'
  AND created_at <= DATE_SUB(NOW(), INTERVAL 2 HOUR);

EXPLAIN SELECT COUNT(*)
FROM payment_logs
WHERE action IN ('AUTO_RECONCILE_WARN', 'STATE_TRANSITION_BLOCKED')
  AND log_time >= DATE_SUB(NOW(), INTERVAL 1 DAY);

EXPLAIN SELECT COUNT(*)
FROM payment_idempotency
WHERE status = 'failed'
  AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

Expected:
- `type` should improve from ALL to ref/range where applicable.
- `rows` estimate should decrease significantly versus baseline.

## Step 4 - Endpoint latency benchmark
Run each endpoint 30 requests and record p50/p95:
- admin dashboard: `index.php?act=admin/dashboard`
- dashboard kpi snapshot: `index.php?act=admin/dashboardKpiSnapshot`

PowerShell sample:

```powershell
$urls = @(
  'http://localhost/Quanlydulich-main/project_quan_ly_tour_du_lich/index.php?act=admin/dashboard',
  'http://localhost/Quanlydulich-main/project_quan_ly_tour_du_lich/index.php?act=admin/dashboardKpiSnapshot'
)
foreach ($u in $urls) {
  $times = @()
  1..30 | ForEach-Object {
    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    curl.exe -s -o NUL -L $u | Out-Null
    $sw.Stop()
    $times += $sw.ElapsedMilliseconds
  }
  $sorted = $times | Sort-Object
  $p50 = $sorted[[int]([math]::Floor($sorted.Count * 0.50))]
  $p95 = $sorted[[int]([math]::Floor($sorted.Count * 0.95))]
  Write-Output ("$u => p50=${p50}ms p95=${p95}ms")
}
```

## Step 5 - Automation job runtime check
Measure these jobs before/after (from logs or run directly):
- payment_anomaly_alert
- daily_kpi_summary
- admin_inbox_digest

Command sample:
- `python scripts/run_admin_automation.py daily_kpi_summary`
- `python scripts/run_admin_automation.py payment_anomaly_alert`

Record `duration_ms` in output and compare.

## Acceptance criteria
- No migration errors, no duplicate index failure.
- Dashboard and snapshot endpoints return successfully.
- p95 latency improves by at least 20% on representative dataset.
- Daily KPI job runtime decreases after query rewrite.
