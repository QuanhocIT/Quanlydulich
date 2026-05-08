<?php
$pageTitle = 'Trung tâm hỗ trợ';
$pageHero = [
    'eyebrow' => 'HỖ TRỢ NHANH',
    'icon' => 'bi-headset',
    'title' => 'Gửi yêu cầu hỗ trợ rõ ràng, theo dõi dễ hơn.',
    'subtitle' => 'Mô tả đúng vấn đề để đội ngũ vận hành phản hồi nhanh, ưu tiên đúng mức và giữ lịch sử trao đổi thống nhất với booking của bạn.',
    'actions' => [
        ['label' => 'Quay lại trang chủ', 'url' => 'index.php?act=khachHang/dashboard', 'icon' => 'bi-arrow-left', 'style' => 'ghost'],
        ['label' => 'Xem tour đã đặt', 'url' => 'index.php?act=khachHang/yeuCauTour', 'icon' => 'bi-suitcase2'],
    ],
];
ob_start(); ?>
    .support-grid {
        display: grid;
        gap: 22px;
        grid-template-columns: minmax(0, 1.2fr) minmax(300px, .8fr);
    }
    .support-info-list {
        display: grid;
        gap: 12px;
        margin-top: 18px;
    }
    .support-info-item {
        align-items: flex-start;
        background: rgba(255,255,255,.72);
        border: 1px solid rgba(15,23,42,.08);
        border-radius: 18px;
        display: flex;
        gap: 12px;
        padding: 14px;
    }
    .support-info-item i {
        align-items: center;
        background: linear-gradient(135deg, #15233b, #20365f);
        border-radius: 14px;
        color: #f6dfab;
        display: inline-flex;
        flex: 0 0 40px;
        height: 40px;
        justify-content: center;
    }
    .support-note {
        background: rgba(214,178,109,.12);
        border: 1px solid rgba(214,178,109,.26);
        border-radius: 18px;
        color: #5b4a22;
        padding: 16px;
    }
    @media (max-width: 991px) {
        .support-grid {
            grid-template-columns: 1fr;
        }
    }
<?php
$extraCss = ob_get_clean();
include __DIR__ . '/_layout/header.php';
?>

<main class="kh-page">
    <section class="support-grid kh-section">
        <div class="kh-surface">
            <div class="kh-surface-body">
                <div class="kh-section-head">
                    <div>
                        <h2 class="kh-section-title">Tạo phiếu hỗ trợ</h2>
                        <p class="kh-section-note">Điền đủ tiêu đề, mức độ ưu tiên và mô tả chi tiết để hệ thống chuyển đúng cho bộ phận xử lý.</p>
                    </div>
                    <span class="kh-chip"><i class="bi bi-clock-history"></i> Phản hồi trong giờ hỗ trợ</span>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php?act=khachHang/guiYeuCauHoTro" class="kh-stack">
                    <div>
                        <label class="kh-label" for="tieu_de">Tiêu đề yêu cầu</label>
                        <input class="kh-form-control" id="tieu_de" type="text" name="tieu_de" value="Yêu cầu hỗ trợ" required>
                    </div>

                    <div>
                        <label class="kh-label" for="muc_do_uu_tien">Mức độ ưu tiên</label>
                        <select class="kh-form-select" id="muc_do_uu_tien" name="muc_do_uu_tien">
                            <option value="TrungBinh" selected>Trung bình</option>
                            <option value="Thap">Thấp</option>
                            <option value="Cao">Cao</option>
                            <option value="KhanCap">Khẩn cấp</option>
                        </select>
                    </div>

                    <div>
                        <label class="kh-label" for="noi_dung">Nội dung hỗ trợ</label>
                        <textarea class="kh-form-textarea" id="noi_dung" name="noi_dung" rows="8" placeholder="Mô tả rõ tình huống, mã booking liên quan, thời điểm phát sinh và điều bạn cần hỗ trợ..." required></textarea>
                        <div class="kh-help">Nên nêu rõ booking liên quan, lỗi gặp phải, thời điểm và mong muốn xử lý để giảm thời gian trao đổi lại.</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                        <a class="kh-btn-ghost" href="index.php?act=khachHang/dashboard"><i class="bi bi-arrow-left"></i> Quay lại</a>
                        <button type="submit" class="kh-btn-main border-0"><i class="bi bi-send"></i> Gửi yêu cầu hỗ trợ</button>
                    </div>
                </form>
            </div>
        </div>

        <aside class="kh-stack">
            <div class="kh-surface-soft">
                <div class="kh-surface-body">
                    <h2 class="kh-section-title" style="font-size:1.4rem;">Gửi nhanh, xử lý đúng</h2>
                    <div class="support-info-list">
                        <article class="support-info-item">
                            <i class="bi bi-receipt"></i>
                            <div>
                                <strong>Vấn đề hóa đơn hoặc thanh toán</strong>
                                <div class="kh-help mt-1">Ghi rõ mã booking, thời gian chuyển khoản và nội dung chuyển khoản thực tế.</div>
                            </div>
                        </article>
                        <article class="support-info-item">
                            <i class="bi bi-people"></i>
                            <div>
                                <strong>Thiếu thông tin người tham gia</strong>
                                <div class="kh-help mt-1">Nêu rõ booking nào đang thiếu giấy tờ hoặc dữ liệu cần cập nhật.</div>
                            </div>
                        </article>
                        <article class="support-info-item">
                            <i class="bi bi-map"></i>
                            <div>
                                <strong>Thay đổi lịch trình hoặc nhu cầu đặc biệt</strong>
                                <div class="kh-help mt-1">Mô tả yêu cầu càng cụ thể, đội điều hành càng kiểm tra và phản hồi nhanh.</div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <div class="support-note">
                <strong><i class="bi bi-info-circle me-2"></i>Mẹo gửi yêu cầu hiệu quả</strong>
                <div class="mt-2">Nếu có nhiều vấn đề khác nhau, nên tách thành từng yêu cầu riêng để hệ thống theo dõi trạng thái rõ hơn và tránh bỏ sót.</div>
            </div>
        </aside>
    </section>
</main>

<?php include __DIR__ . '/_layout/footer.php'; ?>
