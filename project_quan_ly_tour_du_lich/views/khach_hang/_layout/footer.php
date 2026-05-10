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

<script src="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.bundle.min.js"></script>

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

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
(function () {
    var rowsPerPage = 10;
    var tables = document.querySelectorAll('table');

    tables.forEach(function (table, tableIndex) {
        if (!table || table.dataset.autoPagination === '1') return;
        if (table.classList.contains('auto-pagination-skip')) return;

        var wrapper = table.closest('.table-responsive') || table;
        var siblingCursor = wrapper.nextElementSibling;
        var hasExistingPagination = false;
        var steps = 0;
        while (siblingCursor && steps < 4) {
            if (
                siblingCursor.classList.contains('pagination') ||
                siblingCursor.classList.contains('table-auto-pagination') ||
                siblingCursor.querySelector('.pagination')
            ) {
                hasExistingPagination = true;
                break;
            }
            siblingCursor = siblingCursor.nextElementSibling;
            steps += 1;
        }
        if (hasExistingPagination) return;

        var tbody = table.tBodies && table.tBodies.length > 0 ? table.tBodies[0] : null;
        if (!tbody) return;

        var rows = Array.prototype.slice.call(tbody.rows).filter(function (row) {
            return !row.classList.contains('auto-pagination-ignore');
        });
        if (rows.length <= rowsPerPage) return;

        table.dataset.autoPagination = '1';
        var currentPage = 1;
        var totalPages = Math.ceil(rows.length / rowsPerPage);

        var shell = document.createElement('div');
        shell.className = 'table-auto-pagination d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2';

        var summary = document.createElement('small');
        summary.className = 'text-muted';

        var nav = document.createElement('ul');
        nav.className = 'pagination pagination-sm mb-0';

        shell.appendChild(summary);
        shell.appendChild(nav);

        if (wrapper.parentNode) {
            wrapper.parentNode.insertBefore(shell, wrapper.nextSibling);
        }

        function createPageItem(label, targetPage, active, disabled) {
            var item = document.createElement('li');
            item.className = 'page-item';
            if (active) item.classList.add('active');
            if (disabled) item.classList.add('disabled');

            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'page-link';
            button.textContent = label;
            if (active) button.setAttribute('aria-current', 'page');
            if (disabled) button.disabled = true;

            button.addEventListener('click', function () {
                if (targetPage < 1 || targetPage > totalPages || targetPage === currentPage) return;
                currentPage = targetPage;
                render();
            });

            item.appendChild(button);
            return item;
        }

        function render() {
            var startIndex = (currentPage - 1) * rowsPerPage;
            var endIndex = startIndex + rowsPerPage;

            rows.forEach(function (row, index) {
                row.style.display = (index >= startIndex && index < endIndex) ? '' : 'none';
            });

            var from = startIndex + 1;
            var to = Math.min(endIndex, rows.length);
            summary.textContent = 'Hiển thị ' + from + '-' + to + ' / ' + rows.length + ' mục (10 mục/trang)';

            nav.innerHTML = '';
            nav.appendChild(createPageItem('Trước', currentPage - 1, false, currentPage === 1));

            var maxNumericButtons = 5;
            var startPage = Math.max(1, currentPage - 2);
            var endPage = Math.min(totalPages, startPage + maxNumericButtons - 1);
            startPage = Math.max(1, endPage - maxNumericButtons + 1);

            for (var page = startPage; page <= endPage; page += 1) {
                nav.appendChild(createPageItem(String(page), page, page === currentPage, false));
            }

            nav.appendChild(createPageItem('Sau', currentPage + 1, false, currentPage === totalPages));
        }

        shell.dataset.tablePaginationId = 'kh-table-pager-' + String(tableIndex + 1);
        render();
    });
})();
</script>

<?php if ($extraJs): ?>
<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
<?php echo $extraJs; ?>
</script>
<?php endif; ?>

</body>
</html>
