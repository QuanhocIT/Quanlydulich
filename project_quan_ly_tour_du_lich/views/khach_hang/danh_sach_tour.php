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
?>
<?php
$pageTitle  = 'Khám phá tour du lịch';
$activePage = 'tour';
$pageHero   = [
    'eyebrow'  => 'KHÁM PHÁ HÀNH TRÌNH MỚI',
    'icon'     => 'bi-stars',
    'title'    => 'Chọn tour nhanh hơn, tự tin đặt hơn.',
    'subtitle' => 'Lọc theo nhu cầu, xem thông tin cốt lõi ngay trên card và chuyển sang chi tiết hoặc thanh toán chỉ trong một lần bấm.',
];
ob_start(); ?>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: rgba(15, 23, 42, 0.12);
            --cream: #f5f6f8;
            --leaf: #15233b;
            --leaf-2: #20365f;
            --gold: #d6b26d;
            --sand: #f2dfbd;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.16), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.08), transparent 55%),
                #f5f6f8;
            min-height: 100vh;
        }
        .tour-shell { padding: 16px 0 40px; }
        .layout-shell {
            width: min(1380px, calc(100% - 36px));
            margin: 0 auto;
        }
        .topbar {
            align-items: center;
            background: rgba(11,18,32,.92);
            border: 1px solid rgba(214,178,109,.22);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-bottom: 16px;
            padding: 10px 12px;
            box-shadow: 0 12px 34px rgba(2,6,23,.18);
        }
        .brand {
            align-items: center;
            color: #fff;
            display: inline-flex;
            font-weight: 800;
            gap: 8px;
            letter-spacing: .06em;
            text-decoration: none;
            text-transform: uppercase;
        }
        .brand-mark {
            align-items: center;
            background: linear-gradient(135deg, var(--leaf), var(--leaf-2));
            border-radius: 13px;
            color: var(--gold);
            display: inline-flex;
            height: 38px;
            justify-content: center;
            width: 38px;
        }
        .nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: flex-end;
        }
        .nav-pill {
            align-items: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(214,178,109,.28);
            border-radius: 999px;
            color: rgba(255,255,255,.9);
            display: inline-flex;
            font-size: 13px;
            font-weight: 700;
            gap: 8px;
            padding: 8px 12px;
            text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
        }
        .nav-pill:hover {
            background: rgba(214,178,109,.18);
            border-color: rgba(214,178,109,.46);
            color: #fff;
        }
        .nav-pill.is-active {
            background: linear-gradient(135deg, var(--gold), #e2bf78);
            border-color: rgba(214,178,109,.72);
            color: #132033;
            box-shadow: 0 10px 24px rgba(185,137,61,.24);
        }
        .hero {
            background:
                linear-gradient(115deg, rgba(11,18,32,.93), rgba(21,35,59,.78)),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover;
            border-radius: 26px;
            box-shadow: 0 24px 62px rgba(2,6,23,.22);
            color: #fff;
            margin-bottom: 18px;
            overflow: hidden;
            padding: 32px;
            position: relative;
        }
        .hero::after {
            background: rgba(214,178,109,.14);
            border-radius: 999px;
            content: "";
            height: 220px;
            position: absolute;
            right: -70px;
            top: -70px;
            width: 220px;
        }
        .hero-content { max-width: 680px; position: relative; z-index: 1; }
        .eyebrow {
            color: var(--gold);
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: .15em;
            text-transform: uppercase;
        }
        .hero h1 {
            font-family: "Playfair Display", serif;
            font-size: clamp(2rem, 4.6vw, 4.1rem);
            line-height: .92;
            margin: 10px 0 14px;
            max-width: 620px;
            letter-spacing: -.02em;
        }
        .hero p {
            color: rgba(255,255,255,.84);
            font-size: .95rem;
            line-height: 1.65;
            max-width: 560px;
        }
        .search-panel {
            background:
                radial-gradient(circle at top left, rgba(220,176,93,.12), transparent 26%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,244,238,.96));
            border: 1px solid rgba(22,34,58,.08);
            border-radius: 18px;
            box-shadow: 0 14px 40px rgba(10,18,38,.14);
            margin-top: -46px;
            padding: 14px;
            position: relative;
            z-index: 3;
        }
        .filter-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(190px, 1.3fr) repeat(3, minmax(135px, 1fr)) auto;
        }
        .field label {
            color: var(--muted);
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .field input,
        .field select {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(24,40,70,.12);
            border-radius: 12px;
            color: var(--ink);
            font-weight: 700;
            min-height: 42px;
            padding: 8px 11px;
            font-size: 14px;
            width: 100%;
        }
        .field input:focus,
        .field select:focus {
            border-color: rgba(214,178,109,.72);
            box-shadow: 0 0 0 .2rem rgba(214,178,109,.18);
            outline: 0;
        }
        .filter-submit {
            align-self: end;
            background: linear-gradient(135deg, var(--leaf), var(--leaf-2));
            border: 0;
            border-radius: 12px;
            color: #f7e5b6;
            font-weight: 800;
            min-height: 42px;
            padding: 0 18px;
            box-shadow: 0 14px 28px rgba(21,35,59,.22);
        }
        .quick-stats {
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 20px 0;
        }
        .stat-card {
            background: rgba(255,255,255,.86);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 10px 30px rgba(2,6,23,.07);
        }
        .stat-card strong {
            display: block;
            font-size: 1.6rem;
            line-height: 1;
            font-weight: 900;
        }
        .stat-card span {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
        }
        .section-head {
            align-items: end;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin: 22px 0 14px;
        }
        .section-head h2 {
            font-family: "Playfair Display", serif;
            font-size: clamp(1.6rem, 3.4vw, 2.4rem);
            margin: 0;
            position: relative;
            padding-bottom: .25rem;
        }
        .section-head h2::after {
            content: "";
            display: block;
            width: 64px;
            height: 2px;
            margin-top: .45rem;
            background: linear-gradient(90deg, var(--gold), transparent);
            border-radius: 999px;
        }
        .section-head p {
            color: var(--muted);
            margin: 6px 0 0;
        }
        .tour-grid {
            display: grid;
            gap: 18px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .tour-card {
            background: linear-gradient(180deg,#ffffff,#fbfdfa);
            border: 1px solid rgba(15,23,42,.10);
            border-radius: 22px;
            box-shadow: 0 22px 64px rgba(2,6,23,.10);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .tour-card:hover {
            box-shadow: 0 30px 78px rgba(2,6,23,.16);
            transform: translateY(-6px);
        }
        .tour-media {
            aspect-ratio: 1.38;
            background: var(--sand);
            overflow: hidden;
            position: relative;
        }
        .tour-media img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .type-badge {
            background: rgba(23,65,59,.9);
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 999px;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            left: 16px;
            padding: 8px 12px;
            position: absolute;
            top: 16px;
        }
        .fav-heart {
            align-items: center;
            background: rgba(255,255,255,.9);
            border: 1px solid rgba(15,23,42,.12);
            border-radius: 999px;
            color: #1f2937;
            display: inline-flex;
            font-size: 14px;
            height: 38px;
            justify-content: center;
            position: absolute;
            right: 14px;
            top: 14px;
            width: 38px;
            z-index: 2;
            transition: all .18s ease;
        }
        .fav-heart:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(2,6,23,.18);
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
            padding: 16px;
        }
        .tour-title {
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.42;
            margin: 0 0 10px;
        }
        .tour-desc {
            color: var(--muted);
            display: -webkit-box;
            font-size: .85rem;
            line-height: 1.6;
            margin: 0 0 14px;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
        .meta-list {
            display: grid;
            gap: 8px;
            margin-top: auto;
        }
        .meta-item {
            align-items: center;
            color: #31423d;
            display: flex;
            font-size: 12px;
            font-weight: 700;
            gap: 8px;
        }
        .meta-item i { color: var(--leaf-2); }
        .meta-item.rating {
            font-weight: 800;
        }
        .meta-item.rating .rating-stars {
            color: var(--gold);
            letter-spacing: 1px;
        }
        .meta-item.rating .rating-text {
            color: #1e2d29;
        }
        .price-row {
            align-items: center;
            border-top: 1px solid var(--line);
            display: flex;
            gap: 12px;
            justify-content: space-between;
            margin-top: 14px;
            padding-top: 14px;
        }
        .price {
            color: #a15c08;
            font-size: 1.05rem;
            font-weight: 900;
        }
        .price span {
            color: var(--muted);
            display: block;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .tour-actions {
            display: flex;
            gap: 8px;
        }
        .btn-soft,
        .btn-main {
            align-items: center;
            border-radius: 999px;
            display: inline-flex;
            font-size: 12px;
            font-weight: 900;
            gap: 6px;
            justify-content: center;
            min-height: 36px;
            padding: 8px 11px;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-main { background: linear-gradient(135deg, var(--leaf), var(--leaf-2)); color: #fff; }
        .btn-soft { background: #f3ead8; color: #17413b; border:1px solid rgba(214,178,109,.26); }
        .empty-state {
            background: rgba(255,255,255,.86);
            border: 1px dashed rgba(214,178,109,.46);
            border-radius: 28px;
            padding: 46px 24px;
            text-align: center;
        }
        .empty-state i {
            color: var(--gold);
            font-size: 44px;
        }
        .mobile-cta {
            background: rgba(11,18,32,.92);
            border-top: 1px solid rgba(214,178,109,.24);
            bottom: 0;
            display: none;
            gap: 10px;
            left: 0;
            padding: 10px 14px;
            position: fixed;
            right: 0;
            z-index: 20;
        }
        .js-reveal {
            opacity: 0;
            transform: translateY(22px);
            transition: opacity .6s ease, transform .6s ease;
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
        @media (max-width: 1100px) {
            .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-submit { grid-column: 1 / -1; }
            .tour-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .tour-shell { padding-bottom: 86px; }
            .layout-shell { width: min(100% - 24px, 1500px); }
            .topbar { align-items: flex-start; flex-direction: column; }
            .nav-actions { justify-content: flex-start; }
            .hero { border-radius: 20px; padding: 26px 18px 64px; }
            .search-panel { margin-top: -40px; }
            .filter-grid,
            .quick-stats,
            .tour-grid { grid-template-columns: 1fr; }
            .section-head { align-items: flex-start; flex-direction: column; }
            .price-row { align-items: flex-start; flex-direction: column; }
            .tour-actions { width: 100%; }
            .tour-actions a { flex: 1; }
            .mobile-cta { display: flex; }
        }
<?php $extraCss = ob_get_clean();
include __DIR__ . '/_layout/header.php'; ?>
    <div class="tour-shell">
        <div class="layout-shell">

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
                <div class="stat-card js-reveal">
                    <strong><?php echo count($tours); ?></strong>
                    <span>Tour phù hợp bộ lọc</span>
                </div>
                <div class="stat-card js-reveal">
                    <strong><?php echo count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'TrongNuoc')); ?></strong>
                    <span>Hành trình trong nước</span>
                </div>
                <div class="stat-card js-reveal">
                    <strong><?php echo count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'QuocTe')); ?></strong>
                    <span>Hành trình quốc tế</span>
                </div>
            </section>

            <section>
                <div class="section-head js-reveal">
                    <div>
                        <h2>Tour đang mở bán</h2>
                        <p>Thông tin được rút gọn để khách so sánh nhanh trước khi vào chi tiết.</p>
                    </div>
                    <?php if (!empty(array_filter($filters))): ?>
                        <a class="nav-pill" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-x-circle"></i> Xóa lọc</a>
                    <?php endif; ?>
                </div>

                <?php if (!empty($tours)): ?>
                    <div class="tour-grid">
                        <?php foreach ($tours as $index => $tour): ?>
                            <?php
                                $tourId = (int)($tour['tour_id'] ?? 0);
                                $typeLabel = (($tour['loai_tour'] ?? '') === 'QuocTe') ? 'Quốc tế' : 'Trong nước';
                                $price = (float)($tour['gia_co_ban'] ?? 0);
                                $seats = $tour['so_cho'] ?? null;
                                $ratingAvg = (float)($tour['diem_tb'] ?? 0);
                                $ratingCount = (int)($tour['so_danh_gia'] ?? 0);
                                $ratingRounded = (int)round($ratingAvg);
                                if ($ratingRounded < 0) {
                                    $ratingRounded = 0;
                                }
                                if ($ratingRounded > 5) {
                                    $ratingRounded = 5;
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
                                    <p class="tour-desc"><?php echo htmlspecialchars((string)($tour['mo_ta'] ?? 'Hành trình đang được cập nhật mô tả chi tiết.')); ?></p>
                                    <div class="meta-list">
                                        <div class="meta-item"><i class="bi bi-calendar-event"></i> Khởi hành: <?php echo htmlspecialchars($formatDate($tour['ngay_khoi_hanh_gan_nhat'] ?? null)); ?></div>
                                        <div class="meta-item"><i class="bi bi-geo-alt"></i> Điểm tập trung: <?php echo htmlspecialchars((string)($tour['diem_tap_trung'] ?: 'Đang cập nhật')); ?></div>
                                        <div class="meta-item"><i class="bi bi-people"></i> Số chỗ: <?php echo $seats !== null ? (int)$seats . ' khách' : 'Đang cập nhật'; ?></div>
                                        <div class="meta-item rating">
                                            <i class="bi bi-star-fill"></i>
                                            <?php if ($ratingCount > 0): ?>
                                                <span class="rating-stars" aria-hidden="true">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <?php echo $i <= $ratingRounded ? '★' : '☆'; ?>
                                                    <?php endfor; ?>
                                                </span>
                                                <span class="rating-text"><?php echo number_format($ratingAvg, 1); ?>/5 (<?php echo $ratingCount; ?> đánh giá)</span>
                                            <?php else: ?>
                                                <span class="rating-text">Chưa có đánh giá</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="price-row">
                                        <div class="price">
                                            <span>Giá từ</span>
                                            <?php echo number_format($price); ?>đ
                                        </div>
                                        <div class="tour-actions">
                                            <a class="btn-soft" href="index.php?act=khachHang/chiTietTour&id=<?php echo $tourId; ?>"><i class="bi bi-info-circle"></i> Chi tiết</a>
                                            <a class="btn-main" href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tourId; ?>"><i class="bi bi-cart-check"></i> Đặt ngay</a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state js-reveal">
                        <i class="bi bi-map"></i>
                        <h3 class="mt-3">Chưa tìm thấy tour phù hợp</h3>
                        <p class="text-muted mb-4">Bạn có thể xóa bộ lọc hoặc gửi yêu cầu riêng để đội tư vấn thiết kế hành trình phù hợp hơn.</p>
                        <a class="btn-main" href="index.php?act=khachHang/dashboard#home"><i class="bi bi-magic"></i> Gửi yêu cầu tour</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <div class="mobile-cta">
        <a class="btn-soft flex-fill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
        <a class="btn-main flex-fill" href="#q"><i class="bi bi-search"></i> Tìm tour</a>
    </div>

    <script>
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
