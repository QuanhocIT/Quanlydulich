<?php
$tours = isset($tours) && is_array($tours) ? $tours : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khám phá tour - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #12211d;
            --muted: #63716d;
            --line: rgba(18, 33, 29, 0.12);
            --cream: #fbf5e8;
            --leaf: #245247;
            --leaf-2: #0f766e;
            --gold: #d99f3e;
            --sand: #f2dfbd;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            font-family: "Manrope", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(217,159,62,.22), transparent 28rem),
                linear-gradient(145deg, #fffaf0 0%, #eef8f5 42%, #f8fbff 100%);
            min-height: 100vh;
        }
        .tour-shell { padding: 24px 0 56px; }
        .topbar {
            align-items: center;
            display: flex;
            gap: 16px;
            justify-content: space-between;
            margin-bottom: 22px;
        }
        .brand {
            align-items: center;
            color: var(--ink);
            display: inline-flex;
            font-weight: 800;
            gap: 10px;
            letter-spacing: .08em;
            text-decoration: none;
            text-transform: uppercase;
        }
        .brand-mark {
            align-items: center;
            background: var(--leaf);
            border-radius: 16px;
            color: #fff;
            display: inline-flex;
            height: 44px;
            justify-content: center;
            width: 44px;
        }
        .nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }
        .nav-pill {
            align-items: center;
            background: rgba(255,255,255,.72);
            border: 1px solid var(--line);
            border-radius: 999px;
            color: var(--ink);
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            padding: 10px 15px;
            text-decoration: none;
        }
        .hero {
            background:
                linear-gradient(115deg, rgba(18,33,29,.9), rgba(36,82,71,.74)),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover;
            border-radius: 34px;
            box-shadow: 0 30px 80px rgba(36,82,71,.22);
            color: #fff;
            margin-bottom: 26px;
            overflow: hidden;
            padding: 48px;
            position: relative;
        }
        .hero::after {
            background: rgba(255,255,255,.13);
            border-radius: 999px;
            content: "";
            height: 280px;
            position: absolute;
            right: -90px;
            top: -90px;
            width: 280px;
        }
        .hero-content { max-width: 760px; position: relative; z-index: 1; }
        .eyebrow {
            color: #f9d287;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .18em;
            text-transform: uppercase;
        }
        .hero h1 {
            font-family: "Playfair Display", serif;
            font-size: clamp(2.5rem, 6vw, 5.4rem);
            line-height: .94;
            margin: 12px 0 18px;
            max-width: 720px;
        }
        .hero p {
            color: rgba(255,255,255,.84);
            font-size: 1.05rem;
            line-height: 1.75;
            max-width: 640px;
        }
        .search-panel {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(255,255,255,.72);
            border-radius: 24px;
            box-shadow: 0 18px 50px rgba(18,33,29,.12);
            margin-top: -62px;
            padding: 18px;
            position: relative;
            z-index: 3;
        }
        .filter-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: minmax(220px, 1.4fr) repeat(3, minmax(150px, 1fr)) auto;
        }
        .field label {
            color: var(--muted);
            display: block;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .08em;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .field input,
        .field select {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 15px;
            color: var(--ink);
            font-weight: 700;
            min-height: 48px;
            padding: 10px 13px;
            width: 100%;
        }
        .filter-submit {
            align-self: end;
            background: var(--leaf);
            border: 0;
            border-radius: 15px;
            color: #fff;
            font-weight: 800;
            min-height: 48px;
            padding: 0 22px;
        }
        .quick-stats {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin: 26px 0;
        }
        .stat-card {
            background: rgba(255,255,255,.76);
            border: 1px solid var(--line);
            border-radius: 22px;
            padding: 18px;
        }
        .stat-card strong {
            display: block;
            font-size: 28px;
            line-height: 1;
        }
        .stat-card span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }
        .section-head {
            align-items: end;
            display: flex;
            gap: 18px;
            justify-content: space-between;
            margin: 28px 0 18px;
        }
        .section-head h2 {
            font-family: "Playfair Display", serif;
            font-size: clamp(2rem, 4vw, 3rem);
            margin: 0;
        }
        .section-head p {
            color: var(--muted);
            margin: 6px 0 0;
        }
        .tour-grid {
            display: grid;
            gap: 22px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .tour-card {
            background: rgba(255,255,255,.82);
            border: 1px solid var(--line);
            border-radius: 28px;
            box-shadow: 0 18px 50px rgba(18,33,29,.08);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .tour-card:hover {
            box-shadow: 0 28px 70px rgba(18,33,29,.14);
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
            background: rgba(18,33,29,.78);
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
        .tour-body {
            display: flex;
            flex: 1;
            flex-direction: column;
            padding: 20px;
        }
        .tour-title {
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.35;
            margin: 0 0 10px;
        }
        .tour-desc {
            color: var(--muted);
            display: -webkit-box;
            font-size: 14px;
            line-height: 1.65;
            margin: 0 0 16px;
            overflow: hidden;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            line-clamp: 3;
        }
        .meta-list {
            display: grid;
            gap: 9px;
            margin-top: auto;
        }
        .meta-item {
            align-items: center;
            color: #31423d;
            display: flex;
            font-size: 13px;
            font-weight: 700;
            gap: 9px;
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
            margin-top: 18px;
            padding-top: 18px;
        }
        .price {
            color: #a15c08;
            font-size: 1.2rem;
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
            font-size: 13px;
            font-weight: 900;
            gap: 6px;
            justify-content: center;
            min-height: 40px;
            padding: 9px 13px;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-main { background: var(--leaf); color: #fff; }
        .btn-soft { background: #f4ead7; color: var(--leaf); }
        .empty-state {
            background: rgba(255,255,255,.82);
            border: 1px dashed rgba(36,82,71,.32);
            border-radius: 28px;
            padding: 46px 24px;
            text-align: center;
        }
        .empty-state i {
            color: var(--gold);
            font-size: 44px;
        }
        .mobile-cta {
            background: rgba(255,255,255,.94);
            border-top: 1px solid var(--line);
            bottom: 0;
            display: none;
            gap: 10px;
            left: 0;
            padding: 10px 14px;
            position: fixed;
            right: 0;
            z-index: 20;
        }
        @media (max-width: 1100px) {
            .filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .filter-submit { grid-column: 1 / -1; }
            .tour-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 768px) {
            .tour-shell { padding-bottom: 86px; }
            .topbar { align-items: flex-start; flex-direction: column; }
            .nav-actions { justify-content: flex-start; }
            .hero { border-radius: 24px; padding: 34px 22px 78px; }
            .search-panel { margin-top: -52px; }
            .filter-grid,
            .quick-stats,
            .tour-grid { grid-template-columns: 1fr; }
            .section-head { align-items: flex-start; flex-direction: column; }
            .price-row { align-items: flex-start; flex-direction: column; }
            .tour-actions { width: 100%; }
            .tour-actions a { flex: 1; }
            .mobile-cta { display: flex; }
        }
    </style>
</head>
<body>
    <main class="tour-shell">
        <div class="container">
            <header class="topbar">
                <a class="brand" href="index.php?act=khachHang/dashboard">
                    <span class="brand-mark"><i class="bi bi-compass"></i></span>
                    <span>DuLichPro</span>
                </a>
                <nav class="nav-actions" aria-label="Điều hướng khách hàng">
                    <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
                    <a class="nav-pill" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>
                    <a class="nav-pill" href="index.php?act=khachHang/dashboard#home"><i class="bi bi-magic"></i> Tour theo yêu cầu</a>
                </nav>
            </header>

            <section class="hero">
                <div class="hero-content">
                    <div class="eyebrow">Khám phá hành trình mới</div>
                    <h1>Chọn tour nhanh hơn, tự tin đặt hơn.</h1>
                    <p>Lọc theo nhu cầu, xem thông tin cốt lõi ngay trên card và chuyển sang chi tiết hoặc thanh toán chỉ trong một lần bấm.</p>
                </div>
            </section>

            <form class="search-panel" method="GET" action="index.php">
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
                <div class="stat-card">
                    <strong><?php echo count($tours); ?></strong>
                    <span>Tour phù hợp bộ lọc</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'TrongNuoc')); ?></strong>
                    <span>Hành trình trong nước</span>
                </div>
                <div class="stat-card">
                    <strong><?php echo count(array_filter($tours, static fn($tour) => ($tour['loai_tour'] ?? '') === 'QuocTe')); ?></strong>
                    <span>Hành trình quốc tế</span>
                </div>
            </section>

            <section>
                <div class="section-head">
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
                            <article class="tour-card">
                                <div class="tour-media">
                                    <img src="<?php echo htmlspecialchars($getImage($tour, $index)); ?>" alt="<?php echo htmlspecialchars((string)($tour['ten_tour'] ?? 'Tour')); ?>" loading="lazy">
                                    <span class="type-badge"><?php echo htmlspecialchars($typeLabel); ?></span>
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
                    <div class="empty-state">
                        <i class="bi bi-map"></i>
                        <h3 class="mt-3">Chưa tìm thấy tour phù hợp</h3>
                        <p class="text-muted mb-4">Bạn có thể xóa bộ lọc hoặc gửi yêu cầu riêng để đội tư vấn thiết kế hành trình phù hợp hơn.</p>
                        <a class="btn-main" href="index.php?act=khachHang/dashboard#home"><i class="bi bi-magic"></i> Gửi yêu cầu tour</a>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <div class="mobile-cta">
        <a class="btn-soft flex-fill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
        <a class="btn-main flex-fill" href="#q"><i class="bi bi-search"></i> Tìm tour</a>
    </div>
</body>
</html>
