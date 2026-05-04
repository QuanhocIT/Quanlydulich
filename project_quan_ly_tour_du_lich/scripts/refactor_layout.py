#!/usr/bin/env python3
"""
Refactor customer-facing pages to use shared _layout/header.php + footer.php partials.
Run from the project root: python scripts/refactor_layout.py
"""
import os
import sys

BASE = os.path.join(os.path.dirname(__file__), '..', 'views', 'khach_hang')

def read(name):
    path = os.path.join(BASE, name)
    with open(path, 'r', encoding='utf-8') as f:
        return f.read()

def write(name, content):
    path = os.path.join(BASE, name)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print(f"  Written: {name}")

def replace_once(content, old, new, label=""):
    if old not in content:
        print(f"  WARNING: old string not found for {label!r}")
        print(f"    First 60 chars: {old[:60]!r}")
        return content
    result = content.replace(old, new, 1)
    print(f"  OK replaced: {label}")
    return result

# ────────────────────────────────────────────────────────────────────
# danh_sach_tour.php
# ────────────────────────────────────────────────────────────────────
def refactor_danh_sach_tour():
    print("\n== danh_sach_tour.php ==")
    c = read('danh_sach_tour.php')

    # 1. Replace DOCTYPE+head+<style> opener with PHP vars + ob_start
    old_head = ('?>\n'
                '<!DOCTYPE html>\n'
                '<html lang="vi">\n'
                '<head>\n'
                '    <meta charset="UTF-8">\n'
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n'
                '    <title>Khám phá tour - Khách hàng</title>\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">\n'
                '    <link rel="preconnect" href="https://fonts.googleapis.com">\n'
                '    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n'
                '    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">\n'
                '    <style>')
    new_head = ('?>\n'
                '<?php\n'
                "$pageTitle  = 'Khám phá tour du lịch';\n"
                "$activePage = 'tour';\n"
                "$pageHero   = [\n"
                "    'eyebrow'  => 'KHÁM PHÁ HÀNH TRÌNH MỚI',\n"
                "    'icon'     => 'bi-stars',\n"
                "    'title'    => 'Chọn tour nhanh hơn, tự tin đặt hơn.',\n"
                "    'subtitle' => 'Lọc theo nhu cầu, xem thông tin cốt lõi ngay trên card và chuyển sang chi tiết hoặc thanh toán chỉ trong một lần bấm.',\n"
                "];\n"
                'ob_start(); ?>')
    c = replace_once(c, old_head, new_head, 'head block')

    # 2. Replace </style></head><body>...topbar...hero with layout include
    old_nav = ('    </style>\n'
               '</head>\n'
               '<body>\n'
               '    <main class="tour-shell">\n'
               '        <div class="layout-shell">\n'
               '            <header class="topbar">\n'
               '                <a class="brand" href="index.php?act=khachHang/dashboard">\n'
               '                    <span class="brand-mark"><i class="bi bi-compass"></i></span>\n'
               '                    <span>DuLichPro</span>\n'
               '                </a>\n'
               '                <nav class="nav-actions" aria-label="Điều hướng khách hàng">\n'
               '                    <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>\n'
               '                    <a class="nav-pill is-active" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a>\n'
               '                    <a class="nav-pill" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>\n'
               '                    <a class="nav-pill" href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person-gear"></i> Hồ sơ</a>\n'
               '                    <a class="nav-pill" href="index.php?act=khachHang/hoaDon"><i class="bi bi-receipt"></i> Hóa đơn</a>\n'
               '                </nav>\n'
               '            </header>\n'
               '\n'
               '            <section class="hero js-reveal">\n'
               '                <div class="hero-content">\n'
               '                    <div class="eyebrow">Khám phá hành trình mới</div>\n'
               '                    <h1>Chọn tour nhanh hơn, tự tin đặt hơn.</h1>\n'
               '                    <p>Lọc theo nhu cầu, xem thông tin cốt lõi ngay trên card và chuyển sang chi tiết hoặc thanh toán chỉ trong một lần bấm.</p>\n'
               '                </div>\n'
               '            </section>\n')
    new_nav = ('<?php $extraCss = ob_get_clean();\n'
               "include __DIR__ . '/_layout/header.php'; ?>\n"
               '    <div class="tour-shell">\n'
               '        <div class="layout-shell">\n')
    c = replace_once(c, old_nav, new_nav, 'nav+hero block')

    # 3. Replace </div></main> with </div></div>
    old_close = ('        </div>\n'
                 '    </main>\n')
    new_close = ('        </div>\n'
                 '    </div>\n')
    c = replace_once(c, old_close, new_close, 'main close')

    # 4. Remove bootstrap script + replace </body></html> with footer include
    old_end = ('    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n'
               '    <script>\n'
               '        (function () {\n'
               '            var revealEls = document.querySelectorAll(\'.js-reveal\');\n'
               '            if (!revealEls.length) return;\n')
    new_end = ('    <script>\n'
               '        (function () {\n'
               '            var revealEls = document.querySelectorAll(\'.js-reveal\');\n'
               '            if (!revealEls.length) return;\n')
    c = replace_once(c, old_end, new_end, 'remove bootstrap script')

    old_tail = ('    </script>\n'
                '</body>\n'
                '</html>')
    new_tail = ("    </script>\n"
                "<?php include __DIR__ . '/_layout/footer.php'; ?>")
    c = replace_once(c, old_tail, new_tail, 'footer include')

    write('danh_sach_tour.php', c)


# ────────────────────────────────────────────────────────────────────
# yeu_cau_tour.php
# ────────────────────────────────────────────────────────────────────
def refactor_yeu_cau_tour():
    print("\n== yeu_cau_tour.php ==")
    c = read('yeu_cau_tour.php')

    # Find the exact end of PHP prelude before DOCTYPE
    old_head = ('?>\n'
                '<!DOCTYPE html>\n'
                '<html lang="vi">\n'
                '<head>\n'
                '    <meta charset="UTF-8">\n'
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n'
                '    <title>Theo dõi tour đã đặt</title>\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">\n'
                '    <link rel="preconnect" href="https://fonts.googleapis.com">\n'
                '    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n'
                '    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800;900&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">\n'
                '    <style>')
    new_head = ('?>\n'
                '<?php\n'
                "$pageTitle  = 'Theo dõi tour đã đặt';\n"
                "$activePage = 'yeuCauTour';\n"
                "$pageHero   = [\n"
                "    'icon'     => 'bi-suitcase2',\n"
                "    'title'    => 'Theo dõi tour đã đặt',\n"
                "    'subtitle' => 'Quản lý booking, thông tin người tham gia, hóa đơn và nhắc nhở khởi hành trong một màn hình rõ ràng hơn.',\n"
                "];\n"
                'ob_start(); ?>')
    c = replace_once(c, old_head, new_head, 'head block')

    # 2. Remove </style></head><body>...topbar...hero+flash
    old_nav = ('    </style>\n'
               '</head>\n'
               '<body class="trk">\n'
               '    <main class="page-shell">\n'
               '        <header class="topbar">\n'
               '            <a class="brand" href="index.php?act=khachHang/dashboard">\n'
               '                <span class="brand-mark"><i class="bi bi-compass"></i></span>\n'
               '                <span>DuLichPro</span>\n'
               '            </a>\n'
               '            <nav class="nav-actions" aria-label="Điều hướng khách hàng">\n'
               '                <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>\n'
               '                <a class="nav-pill" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a>\n'
               '                <a class="nav-pill is-active" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>\n'
               '                <a class="nav-pill" href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person-gear"></i> Hồ sơ</a>\n'
               '                <a class="nav-pill" href="index.php?act=khachHang/hoaDon"><i class="bi bi-receipt"></i> Hóa đơn</a>\n'
               '            </nav>\n'
               '        </header>\n'
               '\n'
               '        <section class="hero">\n'
               '            <h1>Theo dõi tour đã đặt</h1>\n'
               '            <p>Quản lý booking, thông tin người tham gia, hóa đơn và nhắc nhở khởi hành trong một màn hình rõ ràng hơn.</p>\n'
               '        </section>\n'
               '\n'
               '        <?php if (isset($_SESSION[\'success\'])): ?>\n'
               '            <div class="alert alert-success mt-3 mb-0">\n'
               '                <?php echo htmlspecialchars($_SESSION[\'success\']); unset($_SESSION[\'success\']); ?>\n'
               '            </div>\n'
               '        <?php endif; ?>\n'
               '\n'
               '        <?php if (isset($_SESSION[\'error\'])): ?>\n'
               '            <div class="alert alert-danger mt-3 mb-0">\n'
               '                <?php echo htmlspecialchars($_SESSION[\'error\']); unset($_SESSION[\'error\']); ?>\n'
               '            </div>\n'
               '        <?php endif; ?>\n')
    new_nav = ('<?php $extraCss = ob_get_clean();\n'
               "include __DIR__ . '/_layout/header.php'; ?>\n"
               '    <main class="page-shell">\n')
    c = replace_once(c, old_nav, new_nav, 'nav+hero+flash block')

    # 3. Replace bootstrap script + footer
    old_end = ('    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n'
               '</body>\n'
               '</html>\n')
    new_end = ("<?php include __DIR__ . '/_layout/footer.php'; ?>\n")
    c = replace_once(c, old_end, new_end, 'bootstrap+footer')

    write('yeu_cau_tour.php', c)


# ────────────────────────────────────────────────────────────────────
# thong_bao.php
# ────────────────────────────────────────────────────────────────────
def refactor_thong_bao():
    print("\n== thong_bao.php ==")
    c = read('thong_bao.php')

    # 1. Replace DOCTYPE+head+<style> opener with PHP vars + ob_start
    old_head = ('<!DOCTYPE html>\n'
                '<html lang="vi">\n'
                '<head>\n'
                '    <meta charset="UTF-8">\n'
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n'
                '    <title>Thông báo - DuLichPro</title>\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n'
                '    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">\n'
                '    <link rel="preconnect" href="https://fonts.googleapis.com">\n'
                '    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>\n'
                '    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">\n'
                '    <style>')
    new_head = ('<?php\n'
                "$pageTitle  = 'Thông báo của bạn';\n"
                "$activePage = 'thongBao';\n"
                "$pageHero   = [\n"
                "    'eyebrow'  => 'TRUNG TÂM THÔNG BÁO',\n"
                "    'icon'     => 'bi-bell',\n"
                "    'title'    => 'Thông báo của bạn',\n"
                "    'subtitle' => 'Cập nhật mọi tin tức, đặt tour và ưu đãi từ DuLichPro.',\n"
                "];\n"
                'ob_start(); ?>')
    c = replace_once(c, old_head, new_head, 'head block')

    # 2. Replace </style></head><body>...PHP vars...luxury-nav...flash...hero with layout include
    old_nav = ('    </style>\n'
               '</head>\n'
               '<body>\n'
               '<?php\n'
               '$thongBaoChuaDoc = (int)($thongBaoChuaDoc ?? 0);\n'
               'if (!isset($thongBaoList) || !is_array($thongBaoList)) {\n'
               '    $thongBaoList = [];\n'
               '}\n'
               '\n'
               '$khRealtimeWsEnabled = realtimeWebSocketEnabled() && isLoggedIn() && hasRole(\'KhachHang\');\n'
               '$khRealtimeWsUrl   = $khRealtimeWsEnabled ? realtimeWebSocketPublicUrl() : \'\';\n'
               '$khRealtimeWsToken = $khRealtimeWsEnabled\n'
               '    ? buildRealtimeAuthToken((int)($_SESSION[\'user_id\'] ?? 0), \'KhachHang\', \'notifications\')\n'
               '    : \'\';\n'
               '\n'
               '$totalCount  = count($thongBaoList);\n'
               '$unreadCount = 0;\n'
               '$readCount   = 0;\n'
               'foreach ($thongBaoList as $_tb) {\n'
               '    if (empty($_tb[\'da_doc\']) || (int)$_tb[\'da_doc\'] === 0) $unreadCount++;\n'
               '    else $readCount++;\n'
               '}\n'
               '?>')
    new_nav = ('<?php $extraCss = ob_get_clean();\n'
               '$thongBaoChuaDoc = (int)($thongBaoChuaDoc ?? 0);\n'
               'if (!isset($thongBaoList) || !is_array($thongBaoList)) { $thongBaoList = []; }\n'
               '$khRealtimeWsEnabled = realtimeWebSocketEnabled() && isLoggedIn() && hasRole(\'KhachHang\');\n'
               '$khRealtimeWsUrl   = $khRealtimeWsEnabled ? realtimeWebSocketPublicUrl() : \'\';\n'
               '$khRealtimeWsToken = $khRealtimeWsEnabled\n'
               '    ? buildRealtimeAuthToken((int)($_SESSION[\'user_id\'] ?? 0), \'KhachHang\', \'notifications\') : \'\';\n'
               '$totalCount  = count($thongBaoList);\n'
               '$unreadCount = 0; $readCount = 0;\n'
               'foreach ($thongBaoList as $_tb) {\n'
               '    if (empty($_tb[\'da_doc\']) || (int)$_tb[\'da_doc\'] === 0) $unreadCount++;\n'
               '    else $readCount++;\n'
               '}\n'
               '$unreadCount = $thongBaoChuaDoc;\n'
               "include __DIR__ . '/_layout/header.php'; ?>")
    c = replace_once(c, old_nav, new_nav, 'nav PHP vars block')

    # Find and remove the luxury navbar HTML block
    old_luxury_nav = ('\n'
                      '<!-- ── NAVBAR ── -->\n'
                      '<nav class="navbar navbar-expand-lg luxury-nav py-3">\n'
                      '    <div class="container">\n'
                      '        <a class="navbar-brand d-flex align-items-center gap-2 fs-3" href="index.php?act=khachHang/dashboard">\n'
                      '            <i class="bi bi-star-fill brand-mark"></i>\n'
                      '            <span class="brand-name">DuLichPro</span>\n'
                      '        </a>\n'
                      '\n'
                      '        <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">\n'
                      '            <span class="navbar-toggler-icon"></span>\n'
                      '        </button>\n'
                      '\n'
                      '        <div class="collapse navbar-collapse" id="navbarMain">\n'
                      '            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">\n'
                      '                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="index.php?act=khachHang/dashboard"><i class="bi bi-house-door me-1"></i> Trang chủ</a></li>\n'
                      '                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars me-1"></i> Tour</a></li>\n'
                      '                <li class="nav-item"><a class="nav-link px-3 fw-semibold luxury-cta" href="index.php?act=khachHang/guiYeuCauTour"><i class="bi bi-plus-circle me-1"></i> Tour của tôi</a></li>\n'
                      '                <li class="nav-item">\n'
                      '                    <a class="nav-link nav-icon-link nav-notify-link active" href="index.php?act=khachHang/thongBao" title="Thông báo">\n'
                      '                        <i class="bi bi-bell-fill"></i>\n'
                      '                        <span id="customerNotificationBadge" class="customer-notification-badge" <?php if ($thongBaoChuaDoc <= 0): ?>style="display:none"<?php endif; ?>><?php echo $thongBaoChuaDoc; ?></span>\n'
                      '                    </a>\n'
                      '                </li>\n'
                      '                <li class="nav-item"><a class="nav-link nav-icon-link" href="index.php?act=khachHang/capNhatThongTin" title="Hồ sơ"><i class="bi bi-person-circle"></i></a></li>\n'
                      '                <li class="nav-item"><a class="nav-link nav-icon-link nav-icon-danger" href="index.php?act=auth/logout" title="Đăng xuất"><i class="bi bi-box-arrow-right"></i></a></li>\n'
                      '            </ul>\n'
                      '        </div>\n'
                      '    </div>\n'
                      '</nav>\n'
                      '\n'
                      '<!-- ── FLASH MESSAGES ── -->\n'
                      '<?php if (isset($_SESSION[\'success\']) || isset($_SESSION[\'error\'])): ?>\n'
                      '<div class="position-fixed top-0 start-50 translate-middle-x p-3" style="z-index:1200; margin-top:78px; width:min(640px,calc(100% - 20px))">\n'
                      '    <?php if (isset($_SESSION[\'success\'])): ?>\n'
                      '        <div class="alert alert-success shadow-sm mb-2 alert-dismissible fade show">\n'
                      '            <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION[\'success\']); unset($_SESSION[\'success\']); ?>\n'
                      '            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>\n'
                      '        </div>\n'
                      '    <?php endif; ?>\n'
                      '    <?php if (isset($_SESSION[\'error\'])): ?>\n'
                      '        <div class="alert alert-danger shadow-sm mb-0 alert-dismissible fade show">\n'
                      '            <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION[\'error\']); unset($_SESSION[\'error\']); ?>\n'
                      '            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>\n'
                      '        </div>\n'
                      '    <?php endif; ?>\n'
                      '</div>\n'
                      '<?php endif; ?>\n'
                      '\n'
                      '<!-- ── PAGE HERO ── -->\n'
                      '<section class="page-hero">\n'
                      '    <div class="container">\n'
                      '        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">\n'
                      '            <div>\n'
                      '                <span class="hero-badge mb-2"><i class="bi bi-bell"></i> Trung tâm thông báo</span>\n'
                      '                <h1 class="mt-2 mb-1" style="font-size:clamp(1.5rem,4vw,2.2rem)">Thông báo của bạn</h1>\n'
                      '                <p class="mb-0">Cập nhật mọi tin tức, đặt tour và ưu đãi từ DuLichPro.</p>\n'
                      '            </div>\n'
                      '            <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-light btn-sm px-4 rounded-pill">\n'
                      '                <i class="bi bi-arrow-left me-1"></i> Quay lại trang chủ\n'
                      '            </a>\n'
                      '        </div>\n'
                      '    </div>\n'
                      '</section>\n')
    c = replace_once(c, old_luxury_nav, '\n', 'luxury nav + flash + hero removal')

    # 4. Replace bootstrap script + body/html closing with footer include
    old_bs = '    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n'
    c = replace_once(c, old_bs, '', 'remove bootstrap script')

    old_tail = ('    </script>\n'
                '</body>\n'
                '</html>\n'
                '\n')
    new_tail = ("    </script>\n"
                "<?php include __DIR__ . '/_layout/footer.php'; ?>\n")
    c = replace_once(c, old_tail, new_tail, 'footer include')

    write('thong_bao.php', c)


# ────────────────────────────────────────────────────────────────────
# lich_su_thanh_toan.php
# ────────────────────────────────────────────────────────────────────
def refactor_lich_su_thanh_toan():
    print("\n== lich_su_thanh_toan.php ==")
    c = read('lich_su_thanh_toan.php')

    old_head = ('<!DOCTYPE html>\n'
                '<html lang="vi">\n'
                '<head>\n'
                '    <meta charset="UTF-8">\n'
                '    <meta name="viewport" content="width=device-width, initial-scale=1.0">\n'
                '    <title>Lich su thanh toan</title>\n'
                '    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">\n'
                '    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">\n'
                '    <style>')
    new_head = ('<?php\n'
                "$pageTitle  = 'Lịch sử thanh toán';\n"
                "$activePage = 'hoaDon';\n"
                "$pageHero   = [\n"
                "    'icon'     => 'bi-clock-history',\n"
                "    'title'    => 'Lịch sử thanh toán',\n"
                "    'subtitle' => 'Xem lại toàn bộ giao dịch thanh toán online của bạn.',\n"
                "];\n"
                'ob_start(); ?>')
    c = replace_once(c, old_head, new_head, 'head block')

    old_body = ('    </style>\n'
                '</head>\n'
                '<body>\n')
    new_body = ("<?php $extraCss = ob_get_clean();\n"
                "include __DIR__ . '/_layout/header.php'; ?>\n")
    c = replace_once(c, old_body, new_body, 'style+body replacement')

    old_bs = '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>\n'
    c = replace_once(c, old_bs, '', 'remove bootstrap script')

    old_tail = ('</body>\n'
                '</html>\n')
    new_tail = ("<?php include __DIR__ . '/_layout/footer.php'; ?>\n")
    c = replace_once(c, old_tail, new_tail, 'footer include')

    write('lich_su_thanh_toan.php', c)


# ────────────────────────────────────────────────────────────────────
# yeu_cau_tour.php
# ────────────────────────────────────────────────────────────────────
if __name__ == '__main__':
    refactor_danh_sach_tour()
    refactor_yeu_cau_tour()
    refactor_thong_bao()
    refactor_lich_su_thanh_toan()
    print("\nDone!")
