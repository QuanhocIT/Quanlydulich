<?php
$pageTitle = 'Dong thoi gian cua toi';
$activePage = 'timeline';
$typeFilter = $typeFilter ?? 'all';
$timeline = $timeline ?? [];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="<?php echo BASE_URL; ?>public/assets/bootstrap/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>public/assets/bootstrap-icons/bootstrap-icons.min.css">
    <style>
        body { background:#f5f8fc; color:#0f172a; }
        .wrap { max-width: 1100px; margin: 24px auto; padding: 0 14px; }
        .hero {
            background: linear-gradient(120deg, #0f172a, #1d4ed8);
            color: #fff;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 18px 40px rgba(15,23,42,.22);
        }
        .cardx {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 28px rgba(15,23,42,.06);
        }
        .timeline-item {
            border-left: 3px solid #cbd5e1;
            padding-left: 14px;
            margin-left: 4px;
            margin-bottom: 14px;
        }
        .timeline-dot {
            width: 10px;
            height: 10px;
            background: #2563eb;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .badge-soft {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            font-weight: 600;
        }
    </style>
</head>
<body>
<main class="wrap">
    <section class="hero mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-1"><i class="bi bi-clock-history me-2"></i>Dong thoi gian cua toi</h1>
                <div class="opacity-75">Theo doi lich su dat tour, thanh toan va cac tuong tac cham soc.</div>
            </div>
            <a class="btn btn-light" href="index.php?act=khachHang/dashboard"><i class="bi bi-arrow-left"></i> Ve dashboard</a>
        </div>
    </section>

    <section class="cardx p-3 mb-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="act" value="khachHang/timeline">
            <div class="col-md-3">
                <label class="form-label">Loai su kien</label>
                <select class="form-select" name="type">
                    <option value="all" <?php echo $typeFilter === 'all' ? 'selected' : ''; ?>>Tat ca</option>
                    <option value="booking" <?php echo $typeFilter === 'booking' ? 'selected' : ''; ?>>Booking</option>
                    <option value="thanh_toan" <?php echo $typeFilter === 'thanh_toan' ? 'selected' : ''; ?>>Thanh toan</option>
                    <option value="tuong_tac" <?php echo $typeFilter === 'tuong_tac' ? 'selected' : ''; ?>>Tuong tac</option>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i> Loc</button>
            </div>
        </form>
    </section>

    <section class="cardx p-3">
        <?php if (empty($timeline)): ?>
            <div class="alert alert-info mb-0">Chua co su kien nao de hien thi.</div>
        <?php else: ?>
            <?php foreach ($timeline as $item): ?>
                <?php
                    $type = (string)($item['type'] ?? '');
                    $data = (array)($item['data'] ?? []);
                    $date = trim((string)($item['date'] ?? ''));
                    $typeLabel = match ($type) {
                        'booking' => 'Booking',
                        'thanh_toan' => 'Thanh toan',
                        'tuong_tac' => 'Tuong tac',
                        default => 'Su kien'
                    };

                    $title = '';
                    if ($type === 'booking') {
                        $title = 'Dat tour #' . (int)($data['booking_id'] ?? 0) . ' - ' . (string)($data['ten_tour'] ?? '');
                    } elseif ($type === 'thanh_toan') {
                        $title = 'Giao dich #' . (int)($data['id'] ?? 0) . ' - ' . number_format((float)($data['so_tien'] ?? 0)) . ' VND';
                    } else {
                        $title = (string)($data['loai_hoat_dong'] ?? 'Tuong tac') . ': ' . (string)($data['noi_dung'] ?? '');
                    }
                ?>
                <article class="timeline-item">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                        <div>
                            <span class="timeline-dot"></span>
                            <strong><?php echo htmlspecialchars($title); ?></strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge badge-soft"><?php echo htmlspecialchars($typeLabel); ?></span>
                            <small class="text-muted"><?php echo $date !== '' ? date('d/m/Y H:i', strtotime($date)) : '-'; ?></small>
                        </div>
                    </div>
                    <?php if (!empty($data['mo_ta'])): ?>
                        <div class="text-muted"><?php echo htmlspecialchars((string)$data['mo_ta']); ?></div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
