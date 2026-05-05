<?php
$pageTitle = 'Xuất tài liệu - Booking #' . (isset($booking['booking_id']) ? $booking['booking_id'] : 'N/A');
$currentPage = 'xuat_tai_lieu';
ob_start();
?>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    var BOOKING_ID = <?php echo isset($booking['booking_id']) ? (int)$booking['booking_id'] : 0; ?>;
    var BOOKING_EMAIL = <?php echo json_encode(isset($booking['email']) ? $booking['email'] : ''); ?>;
    
    function showDocument(type) {
        try {
            var noDoc = document.getElementById('no-document-selected');
            if (noDoc) noDoc.style.display = 'none';
            
            var allDocs = document.querySelectorAll('[id$="-content"]');
            for (var i = 0; i < allDocs.length; i++) {
                allDocs[i].style.display = 'none';
            }
            
            var content = document.getElementById(type + '-content');
            if (content) {
                content.style.display = 'block';
                setTimeout(function() {
                    content.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);
            } else {
                alert('Không tìm thấy tài liệu!');
            }
        } catch (e) {
            alert('Lỗi: ' + e.message);
        }
    }

    function sendEmail(type) {
        try {
            if (!BOOKING_EMAIL) {
                alert('Không có địa chỉ email của khách hàng!');
                return;
            }
            
            if (!BOOKING_ID) {
                alert('Không có booking ID!');
                return;
            }
            
            var docName = getDocumentName(type);
            if (confirm('Gửi ' + docName + ' đến ' + BOOKING_EMAIL + '?')) {
                var btn = window.event ? window.event.target : null;
                if (btn) {
                    btn = btn.closest ? btn.closest('button') : btn;
                    var originalHTML = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Đang gửi...';
                    btn.disabled = true;
                }
                
                var url = 'index.php?act=booking/sendEmail&id=' + BOOKING_ID + '&type=' + type;
                
                fetch(url)
                    .then(function(response) { return response.text(); })
                    .then(function(text) {
                        try {
                            var data = JSON.parse(text);
                            if (btn) {
                                btn.innerHTML = originalHTML;
                                btn.disabled = false;
                            }
                            
                            if (data.success) {
                                alert('✅ Đã gửi email thành công!');
                            } else {
                                alert('❌ Lỗi: ' + (data.message || 'Không thể gửi email'));
                            }
                        } catch (e) {
                            if (btn) {
                                btn.innerHTML = originalHTML;
                                btn.disabled = false;
                            }
                            alert('❌ Lỗi server. Kiểm tra Console để xem chi tiết.');
                            console.error('Response:', text);
                        }
                    })
                    .catch(function(error) {
                        if (btn) {
                            btn.innerHTML = originalHTML;
                            btn.disabled = false;
                        }
                        alert('❌ Lỗi kết nối: ' + error.message);
                    });
            }
        } catch (e) {
            alert('Lỗi: ' + e.message);
        }
    }

    function getDocumentName(type) {
        var names = {
            'bao-gia': 'Báo giá',
            'hop-dong': 'Hợp đồng',
            'hoa-don': 'Hóa đơn'
        };
        return names[type] || type;
    }
</script>

<style>
        .export-page {
            padding: 24px 18px;
        }
        .export-wrap {
            max-width: 1200px;
            margin: 0 auto;
        }
        .page-header-section {
            padding: 18px 18px;
            border-radius: 12px;
            background: rgba(45, 45, 45, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(10px);
        }
        .document-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        .document-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.3);
        }
        .document-card .card-body {
            padding: 2rem;
            color: var(--text-light);
        }
        .document-icon {
            width: 5rem;
            height: 5rem;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: 0 auto 1.5rem;
        }
        .document-icon.bg-primary {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
        }
        .document-icon.bg-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
        }
        .document-icon.bg-warning {
            background: rgba(255, 193, 7, 0.3);
            color: #ffc107;
        }
        .doc-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: stretch;
            max-width: 220px;
            margin: 0 auto;
        }
        .action-buttons .btn {
            justify-content: center;
            width: 100%;
        }
        .preview-section {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2rem;
            border-radius: 12px;
            backdrop-filter: blur(10px);
            color: var(--text-light);
        }
        .company-header {
            text-align: center;
            border-bottom: 3px double rgba(255, 255, 255, 0.3);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .document-title {
            text-align: center;
            font-size: 1.75rem;
            font-weight: bold;
            margin: 2rem 0;
            text-transform: uppercase;
            color: var(--text-light);
        }
        .info-table {
            width: 100%;
            margin-bottom: 1.5rem;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .info-table td:first-child {
            font-weight: 600;
            width: 30%;
            background: rgba(45, 45, 45, 0.7);
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        .detail-table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            padding: 0.75rem;
            text-align: left;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .detail-table td {
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .total-section {
            text-align: right;
            margin-top: 2rem;
            font-size: 1.1rem;
            color: var(--text-light);
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 3rem;
        }
        .signature-box {
            text-align: center;
            width: 45%;
            color: var(--text-light);
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background: var(--accent-gold);
            color: #000;
        }
        .btn-primary:hover {
            background: #ffd700;
        }
        .btn-success {
            background: rgba(40, 167, 69, 0.3);
            color: #5cb85c;
            border: 1px solid rgba(40, 167, 69, 0.5);
        }
        .btn-success:hover {
            background: rgba(40, 167, 69, 0.5);
        }
        .btn-info {
            background: rgba(0, 123, 255, 0.3);
            color: #4da3ff;
            border: 1px solid rgba(0, 123, 255, 0.5);
        }
        .btn-info:hover {
            background: rgba(0, 123, 255, 0.5);
        }
        .btn-light {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(255, 255, 255, 0.2);
        }
        .btn-lg {
            padding: 12px 24px;
            font-size: 1rem;
        }
        .spinner-border {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border 0.75s linear infinite;
        }
        .spinner-border-sm {
            width: 0.875rem;
            height: 0.875rem;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
        @media print {
            body * {
                visibility: hidden;
            }
            #printContent, #printContent * {
                visibility: visible;
            }
            #printContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-left: -15px;
            margin-right: -15px;
        }
        .row > * {
            padding-left: 15px;
            padding-right: 15px;
        }
        .col-md-4 { width: 33.333333%; }
        .g-4 { gap: 1.5rem; }
        .mb-4 { margin-bottom: 1.5rem; }
        .text-center { text-align: center; }
        .text-muted { color: var(--text-muted) !important; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .h-100 { height: 100%; }
        @media (max-width: 768px) {
            .doc-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .document-card .card-body { padding: 1.5rem; }
            .col-md-4 { width: 100%; }
        }
        @media (max-width: 520px) {
            .doc-grid { grid-template-columns: 1fr; }
        }
    </style>

<div class="export-page">
<div class="export-wrap">
    <div class="page-header-section no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px; margin-bottom: 18px;">
        <div>
            <h1 style="margin: 0 0 10px 0; font-size: 2rem; color: var(--text-light);">
                <i class="bi bi-file-earmark-pdf-fill" style="color: var(--accent-gold);"></i> Xuất Tài Liệu
            </h1>
            <p style="margin: 0; opacity: 0.8; color: var(--text-light);">Booking #<?php echo $booking['booking_id']; ?> - <?php echo htmlspecialchars($booking['ho_ten']); ?></p>
        </div>
        <a href="index.php?act=booking/chiTiet&id=<?php echo $booking['booking_id']; ?>" style="background: rgba(255, 255, 255, 0.08); color: var(--text-light); padding: 12px 16px; border-radius: 10px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; border: 1px solid rgba(255, 255, 255, 0.16);">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Document Type Selection -->
    <div class="doc-grid no-print">
                <div class="document-card card">
                    <div class="card-body text-center">
                        <div class="document-icon bg-primary bg-opacity-10 text-primary mx-auto">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h4 class="mb-3">Báo Giá</h4>
                        <p class="text-muted mb-4">Tài liệu báo giá chi tiết gửi cho khách hàng</p>
                        <div class="action-buttons">
                            <button onclick="showDocument('bao-gia')" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Xem trước
                            </button>
                            <a href="index.php?act=booking/exportPDF&id=<?php echo $booking['booking_id']; ?>&type=bao-gia" class="btn btn-success">
                                <i class="bi bi-download"></i> Tải PDF
                            </a>
                            <button onclick="sendEmail('bao-gia')" class="btn btn-info">
                                <i class="bi bi-envelope"></i> Gửi email
                            </button>
                        </div>
                    </div>
                </div>

                <div class="document-card card">
                    <div class="card-body text-center">
                        <div class="document-icon bg-success bg-opacity-10 text-success mx-auto">
                            <i class="bi bi-file-earmark-check"></i>
                        </div>
                        <h4 class="mb-3">Hợp Đồng</h4>
                        <p class="text-muted mb-4">Hợp đồng dịch vụ du lịch giữa hai bên</p>
                        <div class="action-buttons">
                            <button onclick="showDocument('hop-dong')" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Xem trước
                            </button>
                            <a href="index.php?act=booking/exportPDF&id=<?php echo $booking['booking_id']; ?>&type=hop-dong" class="btn btn-success">
                                <i class="bi bi-download"></i> Tải PDF
                            </a>
                            <button onclick="sendEmail('hop-dong')" class="btn btn-info">
                                <i class="bi bi-envelope"></i> Gửi email
                            </button>
                        </div>
                    </div>
                </div>

                <div class="document-card card">
                    <div class="card-body text-center">
                        <div class="document-icon bg-warning bg-opacity-10 text-warning mx-auto">
                            <i class="bi bi-receipt"></i>
                        </div>
                        <h4 class="mb-3">Hóa Đơn</h4>
                        <p class="text-muted mb-4">Hóa đơn VAT thanh toán dịch vụ</p>
                        <div class="action-buttons">
                            <button onclick="showDocument('hoa-don')" class="btn btn-primary">
                                <i class="bi bi-eye"></i> Xem trước
                            </button>
                            <a href="index.php?act=booking/exportPDF&id=<?php echo $booking['booking_id']; ?>&type=hoa-don" class="btn btn-success">
                                <i class="bi bi-download"></i> Tải PDF
                            </a>
                            <button onclick="sendEmail('hoa-don')" class="btn btn-info">
                                <i class="bi bi-envelope"></i> Gửi email
                            </button>
                        </div>
                    </div>
                </div>
        </div>

        <!-- Document Preview -->
        <div class="preview-section" id="printContent">
            <div class="text-center text-muted py-5" id="no-document-selected">
                <i class="bi bi-file-earmark-text" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Chọn loại tài liệu để xem trước</h4>
                <p>Click vào nút "Xem trước" ở trên để hiển thị tài liệu</p>
            </div>

            <div id="bao-gia-content" style="display: none;">
                <?php 
                try {
                    include 'templates/bao_gia_template.php'; 
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <div id="hop-dong-content" style="display: none;">
                <?php 
                try {
                    include 'templates/hop_dong_template.php'; 
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>

            <div id="hoa-don-content" style="display: none;">
                <?php 
                try {
                    include 'templates/hoa_don_template.php'; 
                } catch (Exception $e) {
                    echo '<div class="alert alert-danger">Lỗi: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                ?>
            </div>
        </div>

    <!-- Print Button (Fixed) -->
    <div class="no-print" style="position: fixed; bottom: 2rem; right: 2rem;">
        <button onclick="window.print()" style="background: var(--accent-gold); color: #000; padding: 15px 25px; border-radius: 8px; border: none; cursor: pointer; font-weight: 600; font-size: 1rem; box-shadow: 0 4px 12px rgba(0,0,0,0.3);">
            <i class="bi bi-printer"></i> In tài liệu
        </button>
    </div>
</div>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    // Check on page load
    window.addEventListener('DOMContentLoaded', function() {
        console.log('=== PAGE READY ===');
        console.log('Booking ID:', BOOKING_ID, 'Email:', BOOKING_EMAIL);
        console.log('Functions:', typeof showDocument, typeof sendEmail, typeof getDocumentName);
        
        var docs = ['bao-gia-content', 'hop-dong-content', 'hoa-don-content'];
        for (var i = 0; i < docs.length; i++) {
            var el = document.getElementById(docs[i]);
            console.log(docs[i] + ':', el ? 'EXISTS' : 'NOT FOUND');
        }
    });
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
