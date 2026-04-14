<?php
$pageTitle = $pageTitle ?? 'Trung tâm Tự động hóa Admin';
$currentPage = $currentPage ?? 'automation';
ob_start();
?>

<style>
.auto-shell {
    --auto-panel: linear-gradient(165deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
    --auto-border: rgba(212, 175, 55, 0.18);
    --auto-muted: rgba(222, 226, 230, 0.78);
}

.auto-hero {
    margin-bottom: 18px;
    padding: 24px;
    border-radius: 20px;
    border: 1px solid var(--auto-border);
    background:
        radial-gradient(circle at 88% 18%, rgba(212, 175, 55, 0.24), transparent 36%),
        radial-gradient(circle at 12% 85%, rgba(16, 185, 129, 0.14), transparent 38%),
        linear-gradient(145deg, rgba(11, 17, 28, 0.94), rgba(19, 26, 37, 0.86));
    box-shadow: 0 18px 40px rgba(0, 0, 0, 0.22);
    position: relative;
    overflow: hidden;
}

.auto-hero::after {
    content: '';
    position: absolute;
    right: -55px;
    top: -55px;
    width: 160px;
    height: 160px;
    border-radius: 50%;
    background: rgba(212, 175, 55, 0.07);
}

.auto-hero-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.auto-kicker {
    margin: 0 0 8px;
    color: #f6d365;
    letter-spacing: 0.06em;
    font-size: 0.78rem;
    text-transform: uppercase;
    font-weight: 700;
}

.auto-title {
    margin: 0;
    color: #f8fafc;
    font-size: 1.8rem;
    line-height: 1.2;
}

.auto-desc {
    margin: 10px 0 0;
    max-width: 850px;
    color: var(--auto-muted);
    font-size: 0.98rem;
}

.auto-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 999px;
    border: 1px solid rgba(34, 197, 94, 0.32);
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
    font-size: 0.84rem;
    font-weight: 700;
    white-space: nowrap;
}

.auto-metrics {
    margin-top: 16px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}

.auto-metric {
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 14px;
    padding: 12px 14px;
    background: rgba(8, 13, 20, 0.34);
}

.auto-metric-label {
    font-size: 0.78rem;
    color: #c9d3e0;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.auto-metric-value {
    margin-top: 6px;
    font-size: 1.42rem;
    color: #f8fafc;
    font-weight: 700;
}

.auto-layout {
    display: grid;
    grid-template-columns: 1.1fr 1.9fr;
    gap: 16px;
    align-items: start;
    margin-bottom: 16px;
}

.auto-panel {
    border: 1px solid var(--auto-border);
    border-radius: 18px;
    padding: 18px;
    background: var(--auto-panel);
    box-shadow: 0 14px 28px rgba(0,0,0,0.12);
}

.auto-panel h4 {
    margin: 0 0 10px;
    color: var(--accent-gold);
    font-size: 1.02rem;
}

.auto-panel-sub {
    color: var(--auto-muted);
    font-size: 0.9rem;
    margin: 0 0 12px;
}

.auto-job-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: end;
}

.auto-job-form label {
    display: block;
    margin-bottom: 6px;
    color: #d6d9e0;
    font-size: 0.86rem;
}

.auto-job-form select,
.inline-form select {
    min-width: 220px;
    height: 39px;
    padding: 6px 10px;
    border-radius: 10px;
    border: 1px solid rgba(212, 175, 55, 0.22);
    background: rgba(8, 13, 20, 0.64);
    color: #f8fafc;
}

.auto-btn {
    height: 39px;
    border: 1px solid rgba(212, 175, 55, 0.32);
    border-radius: 10px;
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.28), rgba(212, 175, 55, 0.14));
    color: #f8fafc;
    font-weight: 700;
    padding: 0 16px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.auto-btn:hover {
    transform: translateY(-1px);
    border-color: rgba(212, 175, 55, 0.55);
    background: linear-gradient(135deg, rgba(212, 175, 55, 0.34), rgba(212, 175, 55, 0.18));
}

.auto-btn-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid rgba(59, 130, 246, 0.34);
    color: #bfdbfe;
    border-radius: 10px;
    padding: 8px 12px;
    text-decoration: none;
    font-size: 0.86rem;
    background: rgba(59, 130, 246, 0.12);
}

.auto-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.auto-card {
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.12);
    background: rgba(8, 13, 20, 0.32);
    padding: 12px;
}

.auto-card h5 {
    margin: 0;
    font-size: 0.86rem;
    color: #c4d0de;
}

.auto-card-value {
    margin-top: 8px;
    color: #f8fafc;
    font-size: 1.45rem;
    font-weight: 700;
}

.auto-card-note {
    margin-top: 4px;
    color: #9db0c4;
    font-size: 0.8rem;
}

.auto-table-panel {
    border: 1px solid var(--auto-border);
    border-radius: 18px;
    background: var(--auto-panel);
    margin-bottom: 16px;
    overflow: hidden;
}

.auto-table-header {
    padding: 14px 16px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}

.auto-table-header h4 {
    margin: 0;
    color: var(--accent-gold);
    font-size: 0.98rem;
}

.auto-table-meta {
    color: #a5b4c4;
    font-size: 0.82rem;
}

.auto-table-wrap {
    overflow: auto;
}

.auto-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 760px;
}

.auto-table th,
.auto-table td {
    padding: 11px 12px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    font-size: 0.84rem;
    vertical-align: top;
}

.auto-table th {
    color: #f8e7b0;
    text-align: left;
    background: rgba(0,0,0,0.14);
}

.auto-table tr:hover td {
    background: rgba(255,255,255,0.03);
}

.auto-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: 3px 10px;
    font-size: 0.76rem;
    font-weight: 700;
    text-transform: uppercase;
}

.auto-badge.high {
    background: rgba(239, 68, 68, 0.2);
    color: #fecaca;
}

.auto-badge.medium {
    background: rgba(245, 158, 11, 0.2);
    color: #fde68a;
}

.auto-badge.low {
    background: rgba(59, 130, 246, 0.2);
    color: #bfdbfe;
}

.auto-badge.ok {
    background: rgba(34, 197, 94, 0.22);
    color: #86efac;
}

.auto-badge.error {
    background: rgba(239, 68, 68, 0.2);
    color: #fecaca;
}

.inline-form {
    display: inline-flex;
    gap: 8px;
    align-items: center;
}

.auto-empty {
    padding: 16px;
    color: #a7b7c8;
}

@media (max-width: 1180px) {
    .auto-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .auto-hero {
        padding: 18px;
    }

    .auto-hero-top {
        flex-direction: column;
    }

    .auto-title {
        font-size: 1.45rem;
    }

    .auto-job-form select,
    .inline-form select {
        min-width: 0;
        width: 100%;
    }

    .auto-job-form,
    .inline-form {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .auto-btn {
        width: 100%;
    }
}
</style>

<?php
$eventsCount = count($events ?? []);
$priorityCount = count($priorityBookings ?? []);
$tourHealthCount = count($tourHealth ?? []);
$decisionCount = count($decisionAssist ?? []);
$highSeverityCount = 0;
foreach (($events ?? []) as $eventItem) {
    if ((string)($eventItem['severity'] ?? '') === 'high') {
        $highSeverityCount++;
    }
}
$latestRun = $jobRuns[0] ?? null;
$latestRunText = 'Chưa có dữ liệu chạy job';
$latestRunStatusClass = 'error';
if (!empty($latestRun)) {
    $latestRunOk = ((int)($latestRun['is_success'] ?? 0) === 1);
    $latestRunStatusClass = $latestRunOk ? 'ok' : 'error';
    $latestRunText = ($latestRunOk ? 'Lần chạy gần nhất: OK' : 'Lần chạy gần nhất: ERROR')
        . ' · ' . htmlspecialchars((string)($latestRun['job_name'] ?? ''), ENT_QUOTES, 'UTF-8')
        . ' · ' . htmlspecialchars((string)($latestRun['created_at'] ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="auto-shell">
    <section class="auto-hero">
        <div class="auto-hero-top">
            <div>
                <p class="auto-kicker">Automation Command Center</p>
                <h2 class="auto-title">Trung tâm Tự động hóa Admin</h2>
                <p class="auto-desc">Theo dõi sức khỏe hệ thống theo thời gian gần thực, chạy job ngay khi cần, và xử lý cảnh báo vận hành trên cùng một màn hình.</p>
            </div>
            <span class="auto-chip" id="autoLiveChip">
                <i class="bi bi-activity"></i>
                <span id="autoLiveLabel">Đang tải...</span>
            </span>
        </div>

        <div class="auto-metrics">
            <div class="auto-metric">
                <div class="auto-metric-label">Sự kiện gần đây</div>
                <div class="auto-metric-value" id="am-eventsCount"><?php echo $eventsCount; ?></div>
            </div>
            <div class="auto-metric">
                <div class="auto-metric-label">Cảnh báo mức cao</div>
                <div class="auto-metric-value" id="am-highSeverity"><?php echo $highSeverityCount; ?></div>
            </div>
            <div class="auto-metric">
                <div class="auto-metric-label">Decision Assist mở</div>
                <div class="auto-metric-value" id="am-decisionCount"><?php echo $decisionCount; ?></div>
            </div>
            <div class="auto-metric">
                <div class="auto-metric-label">Tour watch/critical</div>
                <div class="auto-metric-value" id="am-tourHealth"><?php echo $tourHealthCount; ?></div>
            </div>
        </div>
    </section>

    <div class="auto-layout">
        <section class="auto-panel">
            <h4>Chạy Job Ngay</h4>
            <p class="auto-panel-sub">Chọn job để thực thi thủ công khi cần xử lý nhanh hoặc xác thực trạng thái automation.</p>
            <form method="post" action="index.php?act=admin/runAutomationJob" class="auto-job-form">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('admin_form'), ENT_QUOTES, 'UTF-8'); ?>">
                <div style="flex:1;min-width:220px;">
                    <label for="job">Chọn job</label>
                    <select id="job" name="job">
                        <?php foreach (($availableJobs ?? []) as $job): ?>
                            <option value="<?php echo htmlspecialchars($job, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($job, ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="auto-btn">Run Job</button>
            </form>

            <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <a class="auto-btn-link" href="index.php?act=admin/automationDashboard"><i class="bi bi-arrow-clockwise"></i> Refresh Data</a>
                <span style="color:#a5b4c4;font-size:0.82rem;" id="am-countdown">Đang kết nối...</span>
            </div>
        </section>

        <section class="auto-panel">
            <h4>Tổng Quan Vận Hành</h4>
            <p class="auto-panel-sub">Trạng thái nhanh để ưu tiên xử lý trong ngày.</p>
            <div class="auto-grid">
                <div class="auto-card">
                    <h5>Booking ưu tiên cao</h5>
                    <div class="auto-card-value" id="am-priority"><?php echo $priorityCount; ?></div>
                    <div class="auto-card-note">Theo bảng booking_priority</div>
                </div>
                <div class="auto-card">
                    <h5>Decision Assist open</h5>
                    <div class="auto-card-value" id="am-decision2"><?php echo $decisionCount; ?></div>
                    <div class="auto-card-note">Cần quyết định từ admin</div>
                </div>
                <div class="auto-card">
                    <h5>Tour watch/critical</h5>
                    <div class="auto-card-value" id="am-tourHealth2"><?php echo $tourHealthCount; ?></div>
                    <div class="auto-card-note">Theo tour_health_score</div>
                </div>
                <div class="auto-card">
                    <h5>Sự kiện mới nhất</h5>
                    <div class="auto-card-value" id="am-events2"><?php echo $eventsCount; ?></div>
                    <div class="auto-card-note">Top event gần đây</div>
                </div>
            </div>

            <div style="margin-top:12px;">
                <span class="auto-badge <?php echo $latestRunStatusClass; ?>" id="am-latestRun"><?php echo $latestRunText; ?></span>
            </div>
        </section>
    </div>

    <section class="auto-table-panel">
        <div class="auto-table-header">
            <h4>Lịch Sử Chạy Job</h4>
            <span class="auto-table-meta">Theo dõi độ ổn định và thời gian xử lý</span>
        </div>
        <div class="auto-table-wrap">
            <table class="auto-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job</th>
                        <th>Success</th>
                        <th>Affected</th>
                        <th>Duration(ms)</th>
                        <th>Message</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($jobRuns)): ?>
                    <?php foreach ($jobRuns as $run): ?>
                        <tr>
                            <td><?php echo (int)($run['run_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars((string)($run['job_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?php if (((int)($run['is_success'] ?? 0) === 1)): ?>
                                    <span class="auto-badge ok">OK</span>
                                <?php else: ?>
                                    <span class="auto-badge error">ERROR</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo (int)($run['affected_count'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars((string)($run['duration_ms'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($run['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($run['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="auto-empty" colspan="7">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="auto-table-panel">
        <div class="auto-table-header">
            <h4>Sự Kiện Tự Động Hóa</h4>
            <span class="auto-table-meta">Danh sách cảnh báo để xử lý theo mức độ ưu tiên</span>
        </div>
        <div class="auto-table-wrap">
            <table class="auto-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Job</th>
                        <th>Severity</th>
                        <th>Title</th>
                        <th>Message</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($events)): ?>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?php echo (int)($event['event_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars((string)($event['job_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><span class="auto-badge <?php echo htmlspecialchars((string)($event['severity'] ?? 'low'), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars((string)($event['severity'] ?? 'low'), ENT_QUOTES, 'UTF-8'); ?></span></td>
                            <td><?php echo htmlspecialchars((string)($event['title'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($event['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($event['created_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="auto-empty" colspan="6">Chưa có dữ liệu.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="auto-table-panel">
        <div class="auto-table-header">
            <h4>Decision Assist (Open)</h4>
            <span class="auto-table-meta">Cập nhật trạng thái xử lý gợi ý</span>
        </div>
        <div class="auto-table-wrap">
            <table class="auto-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Entity</th>
                        <th>Recommendation</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($decisionAssist)): ?>
                    <?php foreach ($decisionAssist as $assist): ?>
                        <tr>
                            <td><?php echo (int)($assist['assist_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars((string)($assist['entity_type'] ?? ''), ENT_QUOTES, 'UTF-8'); ?> #<?php echo (int)($assist['entity_id'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars((string)($assist['recommendation_text'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($assist['status'] ?? 'open'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars((string)($assist['updated_at'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <form method="post" action="index.php?act=admin/updateDecisionAssistStatus" class="inline-form">
                                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('admin_form'), ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="assist_id" value="<?php echo (int)($assist['assist_id'] ?? 0); ?>">
                                    <select name="status">
                                        <option value="open">open</option>
                                        <option value="done">done</option>
                                        <option value="ignored">ignored</option>
                                    </select>
                                    <button class="auto-btn" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="auto-empty" colspan="6">Không có gợi ý đang mở.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
(function () {
    'use strict';

    const POLL_INTERVAL = 30; // giây
    const STATUS_URL = 'index.php?act=admin/automationStatus';
    const SCHEDULER_INTERVAL_MIN = 15;

    let secondsToNextRefresh = POLL_INTERVAL;
    let countdownTimer = null;
    let nextScheduledRun = null; // khởi tạo từ dữ liệu server

    // Tính thời điểm chạy scheduler tiếp theo dựa trên chu kỳ 15 phút
    function calcNextScheduledRun() {
        const now = new Date();
        const minsElapsed = now.getMinutes() % SCHEDULER_INTERVAL_MIN;
        const secElapsed = minsElapsed * 60 + now.getSeconds();
        const secsToNext = SCHEDULER_INTERVAL_MIN * 60 - secElapsed;
        return secsToNext;
    }

    function formatSecsToTime(secs) {
        const m = Math.floor(secs / 60);
        const s = secs % 60;
        return m > 0 ? `${m}p ${String(s).padStart(2, '0')}s` : `${s}s`;
    }

    function updateDown() {
        const countEl = document.getElementById('am-countdown');
        const liveLabel = document.getElementById('autoLiveLabel');
        const secsToScheduler = calcNextScheduledRun();

        if (countEl) {
            countEl.textContent = `Cập nhật sau ${formatSecsToTime(secondsToNextRefresh)} · Job tự động sau ${formatSecsToTime(secsToScheduler)}`;
        }
        if (liveLabel) {
            liveLabel.textContent = `Auto • ${formatSecsToTime(secondsToNextRefresh)}`;
        }
        secondsToNextRefresh--;
        if (secondsToNextRefresh < 0) {
            secondsToNextRefresh = POLL_INTERVAL;
            fetchStatus();
        }
    }

    function setVal(id, val) {
        const el = document.getElementById(id);
        if (el) {
            if (el.textContent !== String(val)) {
                el.textContent = val;
                el.style.transition = 'color 0.3s';
                el.style.color = '#f6d365';
                setTimeout(() => { el.style.color = ''; }, 1200);
            }
        }
    }

    function fetchStatus() {
        fetch(STATUS_URL, { credentials: 'same-origin' })
            .then(r => r.json())
            .then(function (d) {
                setVal('am-eventsCount', d.eventsCount);
                setVal('am-highSeverity', d.highSeverityCount);
                setVal('am-decisionCount', d.decisionCount);
                setVal('am-tourHealth', d.tourHealthCount);
                setVal('am-priority', d.priorityCount);
                setVal('am-decision2', d.decisionCount);
                setVal('am-tourHealth2', d.tourHealthCount);
                setVal('am-events2', d.eventsCount);

                const runEl = document.getElementById('am-latestRun');
                if (runEl && d.latestRun) {
                    const ok = d.latestRun.is_success == 1;
                    runEl.textContent = (ok ? 'Lần chạy gần nhất: OK' : 'Lần chạy gần nhất: ERROR')
                        + ' · ' + (d.latestRun.job_name || '')
                        + ' · ' + (d.latestRun.created_at || '');
                    runEl.className = 'auto-badge ' + (ok ? 'ok' : 'error');
                }

                const chip = document.getElementById('autoLiveChip');
                if (chip) {
                    chip.style.borderColor = 'rgba(34,197,94,0.55)';
                    chip.style.background = 'rgba(34,197,94,0.18)';
                }
            })
            .catch(function () {
                const chip = document.getElementById('autoLiveChip');
                if (chip) {
                    chip.style.borderColor = 'rgba(239,68,68,0.5)';
                    chip.style.background = 'rgba(239,68,68,0.14)';
                }
            });
    }

    // Khởi động polling ngay khi trang load
    document.addEventListener('DOMContentLoaded', function () {
        fetchStatus();
        countdownTimer = setInterval(updateDown, 1000);
        updateDown();
    });
})();
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
