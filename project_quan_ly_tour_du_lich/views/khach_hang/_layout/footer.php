<?php
/**
 * SHARED CUSTOMER LAYOUT — FOOTER PARTIAL
 *
 * Include at the very end of every customer page view.
 * Closes the .kh-shell div opened in header.php, renders the footer,
 * loads Bootstrap JS, and closes </body></html>.
 *
 * Optional variables:
 *   $extraJs  string|null   Additional inline JS to inject before </body>
 */
$extraJs = $extraJs ?? '';
?>

</div><!-- /.kh-shell -->

<!-- ── FOOTER ── -->
<footer class="kh-footer" id="support">
    <div class="container">
        <div class="row gy-4 text-start">

            <!-- Brand column -->
            <div class="col-md-4">
                <div class="kh-footer-brand">
                    <i class="bi bi-star-fill" style="color:#d6b26d; font-size:1.3rem;"></i>
                    <span>DuLichPro</span>
                </div>
                <p>Hành trình đẳng cấp &mdash; trải nghiệm đích thực.<br>
                Chúng tôi đồng hành cùng bạn trên mọi nẻo đường.</p>
            </div>

            <!-- Quick links -->
            <div class="col-md-4">
                <h6>Liên kết nhanh</h6>
                <ul>
                    <li><a href="index.php?act=khachHang/dashboard"><i class="bi bi-chevron-right me-1" style="font-size:.72rem;"></i>Trang chủ</a></li>
                    <li><a href="index.php?act=khachHang/danhSachTour"><i class="bi bi-chevron-right me-1" style="font-size:.72rem;"></i>Tour nổi bật</a></li>
                    <li><a href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-chevron-right me-1" style="font-size:.72rem;"></i>Tour đã đặt</a></li>
                    <li><a href="#"><i class="bi bi-chevron-right me-1" style="font-size:.72rem;"></i>Chính sách bảo mật</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-4">
                <h6>Liên hệ hỗ trợ</h6>
                <ul>
                    <li><i class="bi bi-telephone-fill me-2" style="color:#d6b26d;"></i><a href="tel:0346858035">0346 858 035</a></li>
                    <li><i class="bi bi-facebook me-2" style="color:#d6b26d;"></i><a href="https://www.facebook.com/quan.le.703104" target="_blank" rel="noopener">Trang Facebook</a></li>
                    <li><i class="bi bi-clock-fill me-2" style="color:#d6b26d;"></i><span>Hỗ trợ 7:00 &ndash; 22:00 hàng ngày</span></li>
                </ul>
            </div>
        </div>

        <hr>
        <p class="mb-0 text-center" style="font-size:.84rem;">
            &copy; 2026 DuLichPro &mdash; Bản quyền thuộc về DuLichPro.
            Thiết kế với <i class="bi bi-heart-fill" style="color:#d6b26d;"></i> tại Việt Nam.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Notification badge live updater (works on all pages) -->
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
(function () {
    var badge = document.getElementById('kh-notif-badge');
    if (!badge) return;

    function refreshBadge() {
        fetch('index.php?act=khachHang/notificationUnreadCount&_ts=' + Date.now(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.ok ? r.json() : null; })
        .then(function (data) {
            if (!data || data.success !== true) return;
            var n = Number(data.unread || 0);
            badge.textContent = n;
            badge.style.display = n > 0 ? '' : 'none';
        })
        .catch(function () {});
    }

    // Refresh every 30 seconds; skip if already on the notifications page.
    if (!window.location.search.includes('act=khachHang/thongBao')) {
        setTimeout(refreshBadge, 5000);
        setInterval(refreshBadge, 30000);
    }
})();
</script>

<?php if ($extraJs): ?>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
<?php echo $extraJs; ?>
</script>
<?php endif; ?>

</body>
</html>
