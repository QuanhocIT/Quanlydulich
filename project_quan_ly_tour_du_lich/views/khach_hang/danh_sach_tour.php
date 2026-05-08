<?php
$tours = isset($tours) && is_array($tours) ? $tours : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$favoriteTourIds = isset($favoriteTourIds) && is_array($favoriteTourIds) ? $favoriteTourIds : [];

$fallbackImages = [
    'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
];

$formatDate = static function ($date) {
    return !empty($date) ? date('d/m/Y', strtotime((string)$date)) : 'Linh hoạt';
};

$formatDuration = static function ($tour) {
    $duration = trim((string)($tour['thoi_gian'] ?? ''));
    if ($duration !== '') {
        return $duration;
    }

    $start = !empty($tour['ngay_khoi_hanh_gan_nhat']) ? strtotime((string)$tour['ngay_khoi_hanh_gan_nhat']) : false;
    $end = !empty($tour['ngay_ket_thuc_gan_nhat']) ? strtotime((string)$tour['ngay_ket_thuc_gan_nhat']) : false;
    if ($start && $end && $end >= $start) {
        $days = (int)floor(($end - $start) / 86400) + 1;
        return $days . ' ngày';
    }

    return '1 ngày';
};

$getImage = static function ($tour, $index) use ($fallbackImages) {
    $image = trim((string)($tour['hinh_anh'] ?? ''));
    if ($image === '') {
        return $fallbackImages[$index % count($fallbackImages)];
    }
    if (preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }
    return rtrim(BASE_URL, '/') . '/' . ltrim($image, '/');
};

$domesticCount = count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'TrongNuoc'));
$internationalCount = count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'QuocTe'));
$customerTrustCount = max(560, count($tours) * 48);

$pageTitle = 'Khám phá tour du lịch';
$activePage = 'tour';
$pageHero = [
    'eyebrow' => 'Khám phá hành trình mới',
    'title' => 'Chọn tour nhanh hơn, tự tin đặt hơn.',
    'subtitle' => 'Lọc theo nhu cầu, xem thông tin cốt lõi ngay trên card và chuyển sang chi tiết hoặc thanh toán chỉ trong một lần bấm.',
];

ob_start();
?>
        :root {
            --tour-ink: #152238;
            --tour-muted: #667085;
            --tour-line: rgba(15, 23, 42, .10);
            --tour-gold: #d69a2d;
            --tour-gold-strong: #c98916;
            --tour-navy: #08162f;
            --tour-navy-2: #112546;
        }
        .tour-shell {
            padding: 6px 0 40px;
        }
        .tour-layout {
            margin: 0 auto;
            width: min(1400px, calc(100% - 32px));
        }
        .kh-topbar {
            background: linear-gradient(135deg, rgba(8,22,47,.98), rgba(14,34,65,.96));
            border-radius: 18px 18px 0 0;
            border: 0;
            box-shadow: none;
            margin-bottom: 0;
            padding: 12px 20px;
        }
        .kh-brand-mark {
            background: transparent;
            border: 1px solid rgba(214,154,45,.35);
            border-radius: 10px;
            height: 30px;
            width: 30px;
        }
        .kh-brand span {
            font-family: "Manrope", sans-serif;
            font-size: .98rem;
            letter-spacing: .04em;
        }
        .kh-nav-actions {
            gap: 4px;
        }
        .kh-pill {
            background: transparent;
            border: 1px solid transparent;
            color: rgba(255,255,255,.88);
            font-size: 14px;
            font-weight: 700;
            padding: 8px 12px;
        }
        .kh-pill:hover {
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.08);
            color: #fff;
        }
        .kh-pill.is-active {
            background: rgba(214,154,45,.16);
            border-color: rgba(214,154,45,.24);
            box-shadow: none;
            color: #ffe6b3;
        }
        .kh-pill-icon {
            border-radius: 999px;
            padding: 8px 10px;
        }
        .kh-hero {
            background:
                linear-gradient(110deg, rgba(6,17,37,.94) 8%, rgba(10,26,50,.78) 46%, rgba(5,17,37,.72) 100%),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover no-repeat;
            border: 0;
            border-radius: 0 0 22px 22px;
            box-shadow: 0 24px 48px rgba(2,6,23,.16);
            margin-bottom: 0;
            min-height: 310px;
            padding: 28px 30px 86px;
        }
        .kh-hero::after,
        .kh-hero h1 i,
        .kh-hero-actions {
            display: none !important;
        }
        .kh-hero-inner {
            max-width: 620px;
        }
        .kh-hero-eyebrow {
            color: #e1ab43;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0;
            margin-bottom: 12px;
            text-transform: none;
        }
        .kh-hero h1 {
            font-size: clamp(2.6rem, 5vw, 4.35rem);
            line-height: 1.06;
            margin: 0 0 14px;
        }
        .kh-hero p {
            color: rgba(255,255,255,.9);
            font-size: 1.04rem;
            line-height: 1.76;
            margin: 0;
            max-width: 560px;
        }
        .search-panel {
            background: rgba(255,255,255,.985);
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 18px;
            box-shadow: 0 12px 30px rgba(15,23,42,.08);
            margin: -42px auto 0;
            padding: 12px;
            position: relative;
            z-index: 3;
        }
        .filter-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(220px, 1.5fr) repeat(3, minmax(145px, 1fr)) 164px;
        }
        .field label {
            color: #2c3750;
            display: block;
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 7px;
        }
        .field input,
        .field select {
            background: #fff;
            border: 1px solid rgba(15,23,42,.12);
            border-radius: 12px;
            color: var(--tour-ink);
            font-size: 14px;
            font-weight: 600;
            min-height: 48px;
            padding: 10px 13px;
            width: 100%;
        }
        .field input:focus,
        .field select:focus {
            border-color: rgba(214,154,45,.6);
            box-shadow: 0 0 0 .2rem rgba(214,154,45,.14);
            outline: 0;
        }
        .filter-submit {
            align-self: end;
            background: linear-gradient(135deg, var(--tour-gold-strong), #e2ab45);
            border: 0;
            border-radius: 12px;
            box-shadow: 0 12px 24px rgba(214,154,45,.22);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            min-height: 48px;
            padding: 0 18px;
        }
        .quick-stats {
            display: grid;
            gap: 16px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            margin: 16px 0 22px;
        }
        .stat-card {
            align-items: center;
            background: rgba(255,255,255,.94);
            border: 1px solid var(--tour-line);
            border-radius: 18px;
            box-shadow: 0 10px 26px rgba(15,23,42,.06);
            display: flex;
            gap: 14px;
            padding: 18px;
        }
        .stat-icon {
            align-items: center;
            border-radius: 14px;
            display: inline-flex;
            flex: 0 0 48px;
            font-size: 1.32rem;
            height: 48px;
            justify-content: center;
            width: 48px;
        }
        .stat-icon.is-blue { background: rgba(59,130,246,.12); color: #3b82f6; }
        .stat-icon.is-green { background: rgba(34,197,94,.12); color: #22c55e; }
        .stat-icon.is-purple { background: rgba(139,92,246,.12); color: #8b5cf6; }
        .stat-icon.is-orange { background: rgba(249,115,22,.14); color: #f97316; }
        .stat-copy strong {
            color: var(--tour-ink);
            display: block;
            font-size: 2rem;
            font-weight: 900;
            line-height: 1;
        }
        .stat-copy span {
            color: var(--tour-muted);
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-top: 6px;
        }
        .section-head {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin: 18px 0 16px;
        }
        .section-head h2 {
            color: var(--tour-ink);
            font-family: "Manrope", sans-serif;
            font-size: clamp(1.9rem, 3vw, 2.25rem);
            font-weight: 900;
            line-height: 1.1;
            margin: 0;
            padding-bottom: 6px;
            position: relative;
        }
        .section-head h2::after {
            background: linear-gradient(90deg, var(--tour-gold), transparent);
            border-radius: 999px;
            content: "";
            display: block;
            height: 3px;
            margin-top: 8px;
            width: 58px;
        }
        .section-head p {
            color: var(--tour-muted);
            margin: 6px 0 0;
        }
        .section-link {
            color: var(--tour-gold-strong);
            font-size: 14px;
            font-weight: 800;
            text-decoration: none;
        }
        .section-link:hover {
            text-decoration: underline;
        }
        .tour-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        .tour-card {
            background: #fff;
            border: 1px solid rgba(15,23,42,.10);
            border-radius: 18px;
            box-shadow: 0 14px 32px rgba(15,23,42,.07);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .tour-card:hover {
            box-shadow: 0 18px 40px rgba(15,23,42,.12);
            transform: translateY(-4px);
        }
        .tour-media {
            aspect-ratio: 1.3;
            overflow: hidden;
            position: relative;
        }
        .tour-media img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .type-badge {
            background: rgba(255,249,236,.94);
            border: 1px solid rgba(214,154,45,.22);
            border-radius: 999px;
            color: #725625;
            font-size: 11px;
            font-weight: 800;
            left: 10px;
            padding: 6px 10px;
            position: absolute;
            top: 10px;
        }
        .fav-heart {
            align-items: center;
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(15,23,42,.12);
            border-radius: 999px;
            color: #22304b;
            display: inline-flex;
            font-size: 14px;
            height: 36px;
            justify-content: center;
            position: absolute;
            right: 10px;
            top: 10px;
            transition: all .18s ease;
            width: 36px;
            z-index: 2;
        }
        .fav-heart:hover {
            box-shadow: 0 8px 20px rgba(15,23,42,.12);
            transform: translateY(-1px);
        }
        .fav-heart.is-active {
            background: #ffe3ea;
            border-color: #ef476f;
            color: #d62839;
        }
        .tour-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 14px 15px 12px;
        }
        .tour-title {
            color: var(--tour-ink);
            font-size: 1.12rem;
            font-weight: 800;
            line-height: 1.35;
            margin: 0 0 8px;
        }
        .tour-meta-top {
            color: var(--tour-muted);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        .tour-rating {
            align-items: center;
            color: #49566c;
            display: flex;
            font-size: 13px;
            font-weight: 700;
            gap: 6px;
            margin-top: auto;
        }
        .tour-rating i {
            color: #ffb300;
        }
        .price-row {
            align-items: center;
            display: flex;
            justify-content: space-between;
            margin-top: 12px;
        }
        .tour-price {
            color: var(--tour-gold-strong);
            font-size: 1.5rem;
            font-weight: 900;
            line-height: 1;
        }
        .tour-link {
            color: var(--tour-gold-strong);
            font-size: 1.08rem;
        }
        .empty-state {
            background: rgba(255,255,255,.9);
            border: 1px dashed rgba(214,154,45,.38);
            border-radius: 24px;
            padding: 44px 24px;
            text-align: center;
        }
        .empty-state i {
            color: var(--tour-gold);
            font-size: 2.2rem;
        }
        .mobile-cta {
            background: rgba(11,18,32,.94);
            border-top: 1px solid rgba(214,154,45,.2);
            bottom: 0;
            display: none;
            gap: 10px;
            left: 0;
            padding: 10px 14px;
            position: fixed;
            right: 0;
            z-index: 20;
        }
        .mobile-cta .kh-btn-soft,
        .mobile-cta .kh-btn-main {
            flex: 1;
        }
        .js-reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .55s ease, transform .55s ease;
        }
        .js-reveal.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        @media (prefers-reduced-motion: reduce) {
            .js-reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }
        }
        @media (max-width: 1180px) {
            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .filter-submit {
                grid-column: 1 / -1;
            }
            .quick-stats,
            .tour-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .tour-shell {
                padding-bottom: 86px;
            }
            .tour-layout {
                width: min(100% - 20px, 1500px);
            }
            .kh-topbar {
                border-radius: 18px 18px 0 0;
                padding: 12px 14px;
            }
            .kh-hero {
                border-radius: 0 0 20px 20px;
                min-height: auto;
                padding: 24px 18px 74px;
            }
            .kh-hero h1 {
                font-size: 2.5rem;
            }
            .search-panel {
                margin-top: -34px;
            }
            .filter-grid,
            .quick-stats {
                grid-template-columns: 1fr;
            }
            .tour-grid {
                grid-auto-columns: minmax(270px, 1fr);
                grid-auto-flow: column;
                grid-template-columns: none;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
            }
            .tour-grid::-webkit-scrollbar {
                display: none;
            }
            .tour-card {
                scroll-snap-align: start;
            }
            .section-head {
                align-items: flex-start;
                flex-direction: column;
            }
            .mobile-cta {
                display: flex;
            }
        }
<?php
$extraCss = ob_get_clean();
include __DIR__ . '/_layout/header.php';
?>

<div class="tour-shell">
    <div class="tour-layout">
        <form class="search-panel js-reveal" method="GET" action="index.php">
            <input type="hidden" name="act" value="khachHang/danhSachTour">
            <div class="filter-grid">
                <div class="field">
                    <label for="q">Bạn muốn đi đâu?</label>
                    <input id="q" type="search" name="q" value="<?php echo htmlspecialchars((string)($filters['q'] ?? '')); ?>" placeholder="VD: Đà Nẵng, Nhật Bản, Phú Quốc">
                </div>
                <div class="field">
                    <label for="loai_tour">Loại tour</label>
                    <select id="loai_tour" name="loai_tour">
                        <option value="">Tất cả</option>
                        <option value="TrongNuoc" <?php echo (($filters['loai_tour'] ?? '') === 'TrongNuoc') ? 'selected' : ''; ?>>Trong nước</option>
                        <option value="QuocTe" <?php echo (($filters['loai_tour'] ?? '') === 'QuocTe') ? 'selected' : ''; ?>>Quốc tế</option>
                    </select>
                </div>
                <div class="field">
                    <label for="price_range">Khoảng giá</label>
                    <select id="price_range" name="price_range">
                        <option value="">Tất cả mức giá</option>
                        <option value="under5" <?php echo (($filters['price_range'] ?? '') === 'under5') ? 'selected' : ''; ?>>Dưới 5 triệu</option>
                        <option value="5to10" <?php echo (($filters['price_range'] ?? '') === '5to10') ? 'selected' : ''; ?>>5 - 10 triệu</option>
                        <option value="10to20" <?php echo (($filters['price_range'] ?? '') === '10to20') ? 'selected' : ''; ?>>10 - 20 triệu</option>
                        <option value="over20" <?php echo (($filters['price_range'] ?? '') === 'over20') ? 'selected' : ''; ?>>Trên 20 triệu</option>
                    </select>
                </div>
                <div class="field">
                    <label for="sort">Sắp xếp</label>
                    <select id="sort" name="sort">
                        <option value="newest" <?php echo (($filters['sort'] ?? '') === 'newest') ? 'selected' : ''; ?>>Mới nhất</option>
                        <option value="upcoming" <?php echo (($filters['sort'] ?? '') === 'upcoming') ? 'selected' : ''; ?>>Sắp khởi hành</option>
                        <option value="price_asc" <?php echo (($filters['sort'] ?? '') === 'price_asc') ? 'selected' : ''; ?>>Giá tăng dần</option>
                        <option value="price_desc" <?php echo (($filters['sort'] ?? '') === 'price_desc') ? 'selected' : ''; ?>>Giá giảm dần</option>
                    </select>
                </div>
                <button class="filter-submit" type="submit"><i class="bi bi-search me-1"></i> Tìm tour</button>
            </div>
        </form>

        <section class="quick-stats" aria-label="Tổng quan tour">
            <article class="stat-card js-reveal">
                <span class="stat-icon is-blue"><i class="bi bi-bag"></i></span>
                <div class="stat-copy">
                    <strong><?php echo count($tours); ?></strong>
                    <span>Tour phù hợp bộ lọc</span>
                </div>
            </article>
            <article class="stat-card js-reveal">
                <span class="stat-icon is-green"><i class="bi bi-airplane"></i></span>
                <div class="stat-copy">
                    <strong><?php echo $domesticCount; ?></strong>
                    <span>Hành trình trong nước</span>
                </div>
            </article>
            <article class="stat-card js-reveal">
                <span class="stat-icon is-purple"><i class="bi bi-globe2"></i></span>
                <div class="stat-copy">
                    <strong><?php echo $internationalCount; ?></strong>
                    <span>Hành trình quốc tế</span>
                </div>
            </article>
            <article class="stat-card js-reveal">
                <span class="stat-icon is-orange"><i class="bi bi-people-fill"></i></span>
                <div class="stat-copy">
                    <strong><?php echo number_format($customerTrustCount, 0, ',', '.'); ?>+</strong>
                    <span>Khách hàng tin tưởng</span>
                </div>
            </article>
        </section>

        <section>
            <div class="section-head js-reveal">
                <div>
                    <h2>Tour đang mở bán</h2>
                </div>
                <?php if (!empty(array_filter($filters))): ?>
                    <a class="section-link" href="index.php?act=khachHang/danhSachTour">Xóa lọc</a>
                <?php else: ?>
                    <a class="section-link" href="#q">Xem tất cả <i class="bi bi-chevron-right"></i></a>
                <?php endif; ?>
            </div>

            <?php if (!empty($tours)): ?>
                <div class="tour-grid">
                    <?php foreach ($tours as $index => $tour): ?>
                        <?php
                            $tourId = (int)($tour['tour_id'] ?? 0);
                            $typeLabel = (($tour['loai_tour'] ?? '') === 'QuocTe') ? 'Quốc tế' : 'Trong nước';
                            $price = (float)($tour['gia_co_ban'] ?? 0);
                            $ratingAvg = (float)($tour['diem_tb'] ?? 0);
                            $ratingCount = (int)($tour['so_danh_gia'] ?? 0);
                            $durationLabel = $formatDuration($tour);
                            $locationLabel = trim((string)($tour['diem_tap_trung'] ?? ''));
                            if ($locationLabel === '') {
                                $locationLabel = trim((string)($tour['dia_diem'] ?? 'Đang cập nhật'));
                            }
                        ?>
                        <article class="tour-card js-reveal">
                            <div class="tour-media">
                                <img src="<?php echo htmlspecialchars($getImage($tour, $index)); ?>" alt="<?php echo htmlspecialchars((string)($tour['ten_tour'] ?? 'Tour')); ?>" loading="lazy">
                                <span class="type-badge"><?php echo htmlspecialchars($typeLabel); ?></span>
                                <button
                                    type="button"
                                    class="fav-heart js-favorite-toggle<?php echo !empty($favoriteTourIds[$tourId]) ? ' is-active' : ''; ?>"
                                    data-tour-id="<?php echo $tourId; ?>"
                                    aria-label="Yêu thích tour"
                                    title="Thêm vào tour yêu thích"
                                >
                                    <i class="bi <?php echo !empty($favoriteTourIds[$tourId]) ? 'bi-heart-fill' : 'bi-heart'; ?>"></i>
                                </button>
                            </div>
                            <div class="tour-body">
                                <h3 class="tour-title"><?php echo htmlspecialchars((string)($tour['ten_tour'] ?? 'Tour đang cập nhật')); ?></h3>
                                <div class="tour-meta-top"><?php echo htmlspecialchars($durationLabel); ?> &nbsp; | &nbsp; <?php echo htmlspecialchars($locationLabel); ?></div>
                                <div class="tour-rating">
                                    <i class="bi bi-star-fill"></i>
                                    <?php if ($ratingCount > 0): ?>
                                        <span><?php echo number_format($ratingAvg, 1); ?> (<?php echo $ratingCount; ?>)</span>
                                    <?php else: ?>
                                        <span>Chưa có đánh giá</span>
                                    <?php endif; ?>
                                </div>
                                <div class="price-row">
                                    <div class="tour-price"><?php echo number_format($price, 0, ',', '.'); ?>đ</div>
                                    <a class="tour-link" href="index.php?act=khachHang/chiTietTour&id=<?php echo $tourId; ?>" aria-label="Xem chi tiết tour">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state js-reveal">
                    <i class="bi bi-map"></i>
                    <h3 class="mt-3">Chưa tìm thấy tour phù hợp</h3>
                    <p class="text-muted mb-4">Bạn có thể điều chỉnh bộ lọc hoặc gửi yêu cầu riêng để đội ngũ tư vấn đề xuất hành trình phù hợp hơn.</p>
                    <a class="kh-btn-main" href="index.php?act=khachHang/dashboard#home"><i class="bi bi-magic"></i> Gửi yêu cầu tour</a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div class="mobile-cta">
    <a class="kh-btn-soft" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
    <a class="kh-btn-main" href="#q"><i class="bi bi-search"></i> Tìm tour</a>
</div>

<script nonce="<?= defined('CSP_NONCE') ? CSP_NONCE : '' ?>">
    (function () {
        var revealEls = document.querySelectorAll('.js-reveal');
        if (!revealEls.length) return;

        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (reduced || !('IntersectionObserver' in window)) {
            revealEls.forEach(function (el) { el.classList.add('is-visible'); });
            return;
        }

        var observer = new IntersectionObserver(function (entries, io) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

        revealEls.forEach(function (el) { observer.observe(el); });
    })();

    (function () {
        var token = <?php echo json_encode(csrfToken('global_form'), JSON_UNESCAPED_UNICODE); ?>;
        var buttons = document.querySelectorAll('.js-favorite-toggle');
        if (!buttons.length) return;

        var updateButton = function (button, isFavorite) {
            var icon = button.querySelector('i');
            button.classList.toggle('is-active', !!isFavorite);
            if (icon) {
                icon.className = 'bi ' + (isFavorite ? 'bi-heart-fill' : 'bi-heart');
            }
        };

        buttons.forEach(function (button) {
            button.addEventListener('click', async function (event) {
                event.preventDefault();
                event.stopPropagation();

                var tourId = Number(button.getAttribute('data-tour-id') || 0);
                if (!Number.isFinite(tourId) || tourId <= 0 || button.disabled) return;

                button.disabled = true;
                try {
                    var body = new URLSearchParams();
                    body.set('_csrf_global', token);
                    body.set('tour_id', String(tourId));

                    var response = await fetch('index.php?act=khachHang/toggleYeuThich', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: body.toString()
                    });

                    var data = await response.json();
                    if (!response.ok || !data || data.success !== true) {
                        throw new Error('Toggle favorite failed');
                    }

                    updateButton(button, !!data.is_favorite);
                } catch (error) {
                    // Bo qua thong bao loi de khong chan trai nghiem xem tour.
                } finally {
                    button.disabled = false;
                }
            });
        });
    })();
</script>
<?php include __DIR__ . '/_layout/footer.php'; ?>
