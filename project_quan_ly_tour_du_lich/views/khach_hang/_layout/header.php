<?php
/**
 * SHARED CUSTOMER LAYOUT — HEADER PARTIAL
 *
 * Expected variables (set before include):
 *   $pageTitle  string             Browser tab title                (default "DuLichPro")
 *   $activePage string             Which nav item is highlighted:
 *                                  dashboard | tour | yeuCauTour | profile | hoaDon | thongBao
 *   $unreadCount int               Notification badge count         (default 0)
 *   $pageHero   array|null         Hero banner data, or null        (default null → no hero)
 *         ->eyebrow   string         Eyebrow label above the title  (optional)
 *         ->icon      string         Bootstrap icon class before h1 (optional)
 *         ->title     string         Main heading
 *         ->subtitle  string         Sub-text below heading          (optional)
 *         ->actions   array          CTA buttons:
 *                       [url, icon, label, style]
 *                       style = 'main' (gold filled) | 'ghost' (outline)
 *   $extraCss   string|null        Additional inline CSS            (optional)
 *   $bodyClass  string|null        Extra class(es) on <body>        (optional)
 */

$pageTitle   = $pageTitle   ?? 'DuLichPro';
$activePage  = $activePage  ?? '';
$unreadCount = (int)($unreadCount ?? $thongBaoChuaDoc ?? 0);
$pageHero    = $pageHero    ?? null;
$extraCss    = $extraCss    ?? '';
$bodyClass   = $bodyClass   ?? '';

$navItems = [
    'dashboard'   => ['url' => 'index.php?act=khachHang/dashboard',       'icon' => 'bi-house',       'label' => 'Trang chủ'],
    'tour'        => ['url' => 'index.php?act=khachHang/danhSachTour',     'icon' => 'bi-stars',       'label' => 'Tour nổi bật'],
    'yeuCauTour'  => ['url' => 'index.php?act=khachHang/yeuCauTour',       'icon' => 'bi-suitcase2',   'label' => 'Tour đã đặt'],
    'profile'     => ['url' => 'index.php?act=khachHang/capNhatThongTin',  'icon' => 'bi-person-gear', 'label' => 'Hồ sơ'],
    'hoaDon'      => ['url' => 'index.php?act=khachHang/hoaDon',           'icon' => 'bi-receipt',     'label' => 'Hóa đơn'],
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?> — DuLichPro</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        /* ── RESET / BASE ── */
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            color: #0f172a;
            font-family: "Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(1200px 620px at -8% -12%, rgba(215,173,91,.22), transparent 58%),
                radial-gradient(900px 520px at 110% 2%,  rgba(59,130,246,.14),  transparent 56%),
                linear-gradient(180deg, #f8fbff 0%, #f2f5fb 40%, #eef3fa 100%);
            overflow-x: hidden;
        }

        /* ── PAGE SHELL ── */
        .kh-shell {
            width: min(1440px, calc(100% - 32px));
            margin: 0 auto;
            padding: 16px 0 48px;
        }

        /* ── TOPBAR ── */
        .kh-topbar {
            align-items: center;
            background: rgba(11, 18, 32, .94);
            border: 1px solid rgba(214, 178, 109, .22);
            border-radius: 18px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 12px 34px rgba(2, 6, 23, .22);
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 14px;
            padding: 10px 14px;
            flex-wrap: wrap;
        }

        /* Brand */
        .kh-brand {
            align-items: center;
            color: #fff;
            display: inline-flex;
            font-weight: 800;
            gap: 10px;
            letter-spacing: .06em;
            text-decoration: none;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .kh-brand-mark {
            align-items: center;
            background: linear-gradient(135deg, #15233b, #20365f);
            border-radius: 12px;
            color: #d6b26d;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            width: 38px;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .kh-brand span { font-family: "Playfair Display", serif; font-size: 1.1rem; }

        /* Nav area */
        .kh-nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        /* Nav pill */
        .kh-pill {
            align-items: center;
            background: rgba(255, 255, 255, .06);
            border: 1px solid rgba(214, 178, 109, .28);
            border-radius: 999px;
            color: rgba(255, 255, 255, .88);
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 7px;
            padding: 8px 14px;
            text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
            white-space: nowrap;
        }
        .kh-pill:hover {
            background: rgba(214, 178, 109, .18);
            border-color: rgba(214, 178, 109, .5);
            color: #fff;
        }
        .kh-pill.is-active {
            background: linear-gradient(135deg, #d6b26d, #e2bf78);
            border-color: rgba(214, 178, 109, .72);
            color: #132033;
            box-shadow: 0 8px 22px rgba(185, 137, 61, .26);
        }
        .kh-pill.is-active:hover { color: #0b1520; }

        /* Icon-only notification pill */
        .kh-pill-icon {
            padding: 8px 10px;
            position: relative;
        }
        .kh-notif-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background: #ef4444;
            color: #fff;
            border-radius: 999px;
            font-size: .6rem;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            line-height: 1;
            border: 1.5px solid rgba(11, 18, 32, .94);
        }

        /* Danger pill (logout) */
        .kh-pill-danger:hover {
            background: rgba(239, 68, 68, .18) !important;
            border-color: rgba(239, 68, 68, .4) !important;
            color: #fca5a5 !important;
        }

        /* ── HERO BANNER ── */
        .kh-hero {
            background:
                linear-gradient(115deg, rgba(11, 18, 32, .93), rgba(21, 35, 59, .82)),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover no-repeat;
            border: 1px solid rgba(214, 178, 109, .2);
            border-radius: 24px;
            box-shadow: 0 28px 70px rgba(2, 6, 23, .22);
            color: #fff;
            margin-bottom: 16px;
            overflow: hidden;
            padding: 32px 36px;
            position: relative;
            animation: kh-fadeup .55s ease both;
        }
        .kh-hero::after {
            background: rgba(214, 178, 109, .13);
            border-radius: 999px;
            content: "";
            height: 260px;
            position: absolute;
            right: -80px;
            top: -80px;
            width: 260px;
            pointer-events: none;
        }
        .kh-hero-inner { max-width: 780px; position: relative; z-index: 1; }
        .kh-hero-eyebrow {
            color: #d6b26d;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .16em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .kh-hero h1 {
            font-family: "Playfair Display", serif;
            font-size: clamp(1.8rem, 4vw, 3.2rem);
            line-height: 1.05;
            margin: 0 0 12px;
            letter-spacing: -.01em;
        }
        .kh-hero p {
            color: rgba(255, 255, 255, .82);
            font-size: .96rem;
            line-height: 1.7;
            margin: 0 0 20px;
            max-width: 640px;
        }
        .kh-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .kh-hero-btn {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: .88rem;
            font-weight: 700;
            gap: 7px;
            padding: .6rem 1.3rem;
            text-decoration: none;
            transition: .22s;
            border: 1.5px solid transparent;
        }
        .kh-hero-btn.main {
            background: linear-gradient(135deg, #d6b26d, #e2c07a);
            color: #1a1200;
        }
        .kh-hero-btn.main:hover { filter: brightness(1.07); transform: translateY(-1px); box-shadow: 0 8px 22px rgba(185,137,61,.35); }
        .kh-hero-btn.ghost {
            background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.28);
            color: rgba(255,255,255,.9);
        }
        .kh-hero-btn.ghost:hover { background: rgba(255,255,255,.16); border-color: rgba(255,255,255,.5); color: #fff; }

        /* ── FLASH MESSAGES ── */
        .kh-flash {
            margin-bottom: 12px;
            animation: kh-fadeup .4s ease both;
        }

        /* ── FOOTER ── */
        .kh-footer {
            background: linear-gradient(135deg, #0b1220 0%, #15233b 60%, #1e3254 100%);
            border-top: 1px solid rgba(214, 178, 109, .15);
            color: rgba(255, 255, 255, .65);
            margin-top: 56px;
            padding: 48px 0 28px;
        }
        .kh-footer .kh-footer-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .kh-footer .kh-footer-brand span { font-family: "Playfair Display", serif; font-size: 1.3rem; font-weight: 700; color: #fff; letter-spacing: .04em; }
        .kh-footer p { font-size: .91rem; line-height: 1.7; }
        .kh-footer h6 { color: #d6b26d; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; font-size: .82rem; margin-bottom: 14px; }
        .kh-footer ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px; }
        .kh-footer a { color: rgba(255,255,255,.68); text-decoration: none; font-size: .9rem; transition: color .2s; }
        .kh-footer a:hover { color: #fff; }
        .kh-footer hr { border-color: rgba(255,255,255,.1); margin: 28px 0 16px; }

        /* ── ANIMATION ── */
        @keyframes kh-fadeup {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 640px) {
            .kh-topbar { border-radius: 14px; padding: 10px 12px; }
            .kh-pill    { font-size: 12px; padding: 7px 10px; }
            .kh-hero    { padding: 22px 18px; border-radius: 18px; }
            .kh-hero h1 { font-size: 1.6rem; }
        }
        @media (max-width: 480px) {
            .kh-brand span { display: none; }
        }

        <?php if ($extraCss): ?>
        /* page-specific */
        <?php echo $extraCss; ?>
        <?php endif; ?>
    </style>
</head>
<body<?php if ($bodyClass): ?> class="<?php echo htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8'); ?>"<?php endif; ?>>

<div class="kh-shell">

    <!-- ── TOPBAR ── -->
    <header class="kh-topbar">
        <a class="kh-brand" href="index.php?act=khachHang/dashboard">
            <span class="kh-brand-mark"><i class="bi bi-star-fill"></i></span>
            <span>DuLichPro</span>
        </a>

        <nav class="kh-nav-actions" aria-label="Điều hướng khách hàng">
            <?php foreach ($navItems as $key => $item): ?>
                <a class="kh-pill<?php echo $activePage === $key ? ' is-active' : ''; ?>"
                   href="<?php echo htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi <?php echo $item['icon']; ?>"></i>
                    <?php echo htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8'); ?>
                </a>
            <?php endforeach; ?>

            <!-- Notification bell -->
            <a class="kh-pill kh-pill-icon<?php echo $activePage === 'thongBao' ? ' is-active' : ''; ?>"
               href="index.php?act=khachHang/thongBao"
               title="Thông báo" aria-label="Thông báo">
                <i class="bi bi-bell<?php echo $unreadCount > 0 ? '-fill' : ''; ?>"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="kh-notif-badge" id="kh-notif-badge"><?php echo $unreadCount; ?></span>
                <?php else: ?>
                    <span class="kh-notif-badge" id="kh-notif-badge" style="display:none">0</span>
                <?php endif; ?>
            </a>

            <!-- Logout -->
            <a class="kh-pill kh-pill-icon kh-pill-danger"
               href="index.php?act=auth/logout"
               title="Đăng xuất" aria-label="Đăng xuất">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </nav>
    </header>

    <!-- ── FLASH MESSAGES ── -->
    <?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
    <div class="kh-flash">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── PAGE HERO ── -->
    <?php if (!empty($pageHero)): ?>
    <section class="kh-hero" aria-label="Tiêu đề trang">
        <div class="kh-hero-inner">
            <?php if (!empty($pageHero['eyebrow'])): ?>
                <p class="kh-hero-eyebrow"><?php echo htmlspecialchars($pageHero['eyebrow'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <h1>
                <?php if (!empty($pageHero['icon'])): ?>
                    <i class="bi <?php echo htmlspecialchars($pageHero['icon'], ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i>
                <?php endif; ?>
                <?php echo htmlspecialchars($pageHero['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
            </h1>

            <?php if (!empty($pageHero['subtitle'])): ?>
                <p><?php echo htmlspecialchars($pageHero['subtitle'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>

            <?php if (!empty($pageHero['actions'])): ?>
            <div class="kh-hero-actions">
                <?php foreach ($pageHero['actions'] as $action): ?>
                    <a href="<?php echo htmlspecialchars($action['url'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>"
                       class="kh-hero-btn <?php echo htmlspecialchars($action['style'] ?? 'ghost', ENT_QUOTES, 'UTF-8'); ?>">
                        <?php if (!empty($action['icon'])): ?>
                            <i class="bi <?php echo htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                        <?php endif; ?>
                        <?php echo htmlspecialchars($action['label'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

<!-- NOTE: Page content goes here — close with: include '_layout/footer.php'; -->
