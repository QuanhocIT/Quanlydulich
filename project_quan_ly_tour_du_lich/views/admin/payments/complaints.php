<?php
$pageTitle = 'Khiếu nại thanh toán';
$currentPage = 'payments';

ob_start();
?>
<div class="aventura-content">
    <style>
        .complaint-tools {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .complaint-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .complaint-form .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .complaint-form label {
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }
        .complaint-form input,
        .complaint-form select {
            border: 1px solid var(--border-color);
            background: rgba(255,255,255,.02);
            color: var(--text-color);
            border-radius: 10px;
            padding: 8px 10px;
        }
        .complaint-actions {
            display: flex;
            gap: 8px;
            align-items: end;
            flex-wrap: wrap;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(150px, 1fr));
            gap: 10px;
            margin-bottom: 16px;
        }
        .summary-card {
            background: rgba(255,255,255,.03);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 10px;
        }
        .summary-card .label {
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 4px;
        }
        .summary-card .value {
            font-size: 20px;
            font-weight: 700;
        }
        .value.warn { color: #f59e0b; }

        .note-box {
            max-width: 520px;
            white-space: pre-wrap;
            word-break: break-word;
            color: var(--text-light);
            font-size: 12px;
            line-height: 1.5;
        }
        .detail-cell {
            text-align: center;
            vertical-align: middle;
            min-width: 92px;
        }
        .detail-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            min-width: 78px;
        }

        .complaint-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 1200;
        }
        .complaint-modal.is-open {
            display: flex;
        }
        .complaint-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(2px);
        }
        .complaint-modal-panel {
            position: relative;
            width: min(920px, 100%);
            max-height: calc(100vh - 40px);
            overflow: auto;
            background: #15171b;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 18px 45px rgba(0, 0, 0, 0.45);
            padding: 16px;
        }
        .complaint-modal-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }
        .complaint-modal-title {
            margin: 0;
            font-size: 20px;
            color: var(--text-color);
        }
        .complaint-meta-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(160px, 1fr));
            gap: 10px;
            margin-bottom: 12px;
        }
        .complaint-meta-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 8px 10px;
        }
        .complaint-meta-item .label {
            display: block;
            color: var(--text-muted);
            font-size: 12px;
            margin-bottom: 2px;
        }
        .complaint-meta-item .value {
            color: var(--text-color);
            font-weight: 600;
            word-break: break-word;
        }
        .complaint-content-box {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 12px;
            background: rgba(255, 255, 255, 0.02);
        }
        .complaint-content-box h4 {
            margin: 0 0 8px 0;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .complaint-content-raw {
            margin: 0;
            white-space: pre-wrap;
            color: var(--text-light);
            font-size: 13px;
            line-height: 1.5;
            word-break: break-word;
        }
        .complaint-content-parsed {
            margin: 0;
            padding-left: 18px;
            color: var(--text-light);
            font-size: 13px;
            line-height: 1.5;
        }

        @media (max-width: 900px) {
            .complaint-form { grid-template-columns: 1fr; }
            .summary-grid { grid-template-columns: 1fr; }
            .complaint-meta-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="aventura-header">
        <h1 class="aventura-title"><i class="bi bi-exclamation-diamond"></i> Khiếu nại thanh toán</h1>
    </div>

    <div class="complaint-tools">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="index.php?act=admin/paymentReconcile" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-left"></i> Quay lại đối soát</a>
            <a href="index.php?act=admin/payments" class="aventura-btn aventura-btn-outline"><i class="bi bi-list"></i> Danh sách thanh toán</a>
        </div>
    </div>

    <form method="GET" action="index.php" class="complaint-form">
        <input type="hidden" name="act" value="admin/paymentComplaints">
        <div class="field">
            <label>Trạng thái</label>
            <select name="trang_thai">
                <option value="">Tất cả</option>
                <option value="DaGui" <?php echo (($filters['trang_thai'] ?? '') === 'DaGui') ? 'selected' : ''; ?>>Đã gửi</option>
                <option value="ChuaGui" <?php echo (($filters['trang_thai'] ?? '') === 'ChuaGui') ? 'selected' : ''; ?>>Chưa gửi</option>
                <option value="Loi" <?php echo (($filters['trang_thai'] ?? '') === 'Loi') ? 'selected' : ''; ?>>Lỗi</option>
            </select>
        </div>
        <div class="field">
            <label>Tìm kiếm</label>
            <input type="text" name="search" value="<?php echo htmlspecialchars((string)($filters['search'] ?? '')); ?>" placeholder="Tên KH / SĐT / booking / payment...">
        </div>
        <div class="complaint-actions">
            <button type="submit" class="aventura-btn aventura-btn-gold"><i class="bi bi-funnel"></i> Lọc</button>
            <a href="index.php?act=admin/paymentComplaints" class="aventura-btn aventura-btn-outline"><i class="bi bi-arrow-counterclockwise"></i> Xóa lọc</a>
        </div>
    </form>

    <div class="summary-grid">
        <div class="summary-card"><div class="label">Tổng khiếu nại</div><div class="value"><?php echo (int)$totalComplaints; ?></div></div>
        <div class="summary-card"><div class="label">Chờ xử lý</div><div class="value warn"><?php echo (int)$pendingComplaints; ?></div></div>
    </div>

    <div class="aventura-table-wrapper">
        <table class="aventura-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tiêu đề</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th>Nội dung tóm tắt</th>
                    <th>Chi tiết</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($complaints)): ?>
                    <tr><td colspan="7" style="text-align:center;color:var(--text-muted)">Chưa có khiếu nại thanh toán.</td></tr>
                <?php else: ?>
                    <?php foreach ($complaints as $item): ?>
                        <tr>
                            <td>#<?php echo (int)($item['id'] ?? 0); ?></td>
                            <td>
                                <div style="font-weight:600;"><?php echo htmlspecialchars((string)($item['nguoi_gui_ten'] ?? 'N/A')); ?></div>
                                <div style="color:var(--text-muted);font-size:12px;"><?php echo htmlspecialchars((string)($item['nguoi_gui_phone'] ?? '')); ?></div>
                            </td>
                            <td><?php echo htmlspecialchars((string)($item['tieu_de'] ?? '')); ?></td>
                            <td><?php echo !empty($item['created_at']) ? htmlspecialchars(date('d/m/Y H:i', strtotime((string)$item['created_at']))) : 'N/A'; ?></td>
                            <td><?php echo htmlspecialchars((string)($item['trang_thai'] ?? 'N/A')); ?></td>
                            <td>
                                <div class="note-box"><?php echo htmlspecialchars(mb_substr((string)($item['noi_dung'] ?? ''), 0, 260)); ?><?php echo mb_strlen((string)($item['noi_dung'] ?? '')) > 260 ? '...' : ''; ?></div>
                            </td>
                            <td class="detail-cell">
                                <?php
                                    $complaintPayload = [
                                        'id' => (int)($item['id'] ?? 0),
                                        'nguoi_gui_ten' => (string)($item['nguoi_gui_ten'] ?? 'N/A'),
                                        'nguoi_gui_phone' => (string)($item['nguoi_gui_phone'] ?? ''),
                                        'nguoi_gui_email' => (string)($item['nguoi_gui_email'] ?? ''),
                                        'tieu_de' => (string)($item['tieu_de'] ?? ''),
                                        'trang_thai' => (string)($item['trang_thai'] ?? ''),
                                        'created_at' => (string)($item['created_at'] ?? ''),
                                        'noi_dung' => (string)($item['noi_dung'] ?? ''),
                                    ];
                                    $payloadJson = htmlspecialchars(
                                        (string)json_encode($complaintPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                ?>
                                <button type="button" class="aventura-btn-sm aventura-btn-outline detail-btn" data-complaint="<?php echo $payloadJson; ?>" onclick="openComplaintDetail(this)">
                                    <i class="bi bi-eye"></i> Xem
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="complaintDetailModal" class="complaint-modal" aria-hidden="true">
        <div class="complaint-modal-backdrop" onclick="closeComplaintDetail()"></div>
        <div class="complaint-modal-panel" role="dialog" aria-modal="true" aria-labelledby="complaintDetailTitle">
            <div class="complaint-modal-head">
                <h3 id="complaintDetailTitle" class="complaint-modal-title">Chi tiết khiếu nại</h3>
                <button type="button" class="aventura-btn-sm aventura-btn-outline" onclick="closeComplaintDetail()">
                    <i class="bi bi-x-lg"></i> Đóng
                </button>
            </div>

            <div class="complaint-meta-grid" id="complaintDetailMeta"></div>

            <div class="complaint-content-box">
                <h4>Nội dung bóc tách</h4>
                <ul id="complaintDetailParsed" class="complaint-content-parsed"></ul>
            </div>

            <div class="complaint-content-box">
                <h4>Toàn bộ nội dung gốc</h4>
                <pre id="complaintDetailRaw" class="complaint-content-raw"></pre>
            </div>
        </div>
    </div>
</div>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function normalizeComplaintKey(input) {
        return String(input || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/đ/g, 'd')
            .replace(/[^a-z0-9]+/g, ' ')
            .trim();
    }

    function parseComplaintContent(content) {
        var rows = String(content || '').split(/\r?\n/);
        var targetMap = {
            'thoi gian chuyen khoan': 'Thời gian chuyển khoản',
            'ma giao dich tham chieu': 'Mã giao dịch/tham chiếu',
            'noi dung khiu nai': 'Nội dung khiếu nại'
        };

        var extracted = {
            'Thời gian chuyển khoản': '[Không cung cấp]',
            'Mã giao dịch/tham chiếu': '[Không cung cấp]',
            'Nội dung khiếu nại': '[Không cung cấp]'
        };

        for (var i = 0; i < rows.length; i++) {
            var line = rows[i].trim();
            if (!line) {
                continue;
            }

            var delimiterPos = line.indexOf(':');
            if (delimiterPos <= 0) {
                continue;
            }

            var key = line.slice(0, delimiterPos).trim();
            var value = line.slice(delimiterPos + 1).trim();
            var normalizedKey = normalizeComplaintKey(key);
            var mappedKey = targetMap[normalizedKey] || null;

            if (mappedKey && value !== '') {
                extracted[mappedKey] = value;
            }
        }

        return [
            { key: 'Thời gian chuyển khoản', value: extracted['Thời gian chuyển khoản'] },
            { key: 'Mã giao dịch/tham chiếu', value: extracted['Mã giao dịch/tham chiếu'] },
            { key: 'Nội dung khiếu nại', value: extracted['Nội dung khiếu nại'] }
        ];
    }

    function buildMetaHtml(data) {
        var createdAt = data.created_at ? new Date(data.created_at.replace(' ', 'T')) : null;
        var displayTime = (createdAt && !isNaN(createdAt.getTime()))
            ? createdAt.toLocaleString('vi-VN')
            : (data.created_at || 'N/A');

        var list = [
            { label: 'ID', value: '#' + (data.id || 0) },
            { label: 'Tiêu đề', value: data.tieu_de || 'N/A' },
            { label: 'Trạng thái', value: data.trang_thai || 'N/A' },
            { label: 'Thời gian', value: displayTime },
            { label: 'Khách hàng', value: data.nguoi_gui_ten || 'N/A' },
            { label: 'SĐT', value: data.nguoi_gui_phone || 'N/A' },
            { label: 'Email', value: data.nguoi_gui_email || 'N/A' }
        ];

        var html = '';
        for (var i = 0; i < list.length; i++) {
            html += '<div class="complaint-meta-item">'
                + '<span class="label">' + escapeHtml(list[i].label) + '</span>'
                + '<div class="value">' + escapeHtml(list[i].value) + '</div>'
                + '</div>';
        }
        return html;
    }

    function openComplaintDetail(button) {
        if (!button) {
            return;
        }

        var rawPayload = button.getAttribute('data-complaint') || '{}';
        var data;

        try {
            data = JSON.parse(rawPayload);
        } catch (e) {
            data = {};
        }

        var modal = document.getElementById('complaintDetailModal');
        var metaContainer = document.getElementById('complaintDetailMeta');
        var parsedList = document.getElementById('complaintDetailParsed');
        var rawContent = document.getElementById('complaintDetailRaw');

        metaContainer.innerHTML = buildMetaHtml(data);

        var parsedItems = parseComplaintContent(data.noi_dung || '');
        if (parsedItems.length === 0) {
            parsedList.innerHTML = '<li>Không có dữ liệu bóc tách.</li>';
        } else {
            var parsedHtml = '';
            for (var i = 0; i < parsedItems.length; i++) {
                parsedHtml += '<li><strong>' + escapeHtml(parsedItems[i].key) + ':</strong> ' + escapeHtml(parsedItems[i].value) + '</li>';
            }
            parsedList.innerHTML = parsedHtml;
        }

        rawContent.textContent = String(data.noi_dung || '');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeComplaintDetail() {
        var modal = document.getElementById('complaintDetailModal');
        if (!modal) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeComplaintDetail();
        }
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/aventura.php';
?>