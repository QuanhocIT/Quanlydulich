<?php
$pageTitle = 'Yeu cau huy/doi lich booking';
$currentPage = 'booking';
$requests = $requests ?? [];
$status = trim((string)($status ?? ''));
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
        body { background: #f3f6fb; color: #0f172a; }
        .wrap { max-width: 1400px; margin: 24px auto; padding: 0 14px; }
        .panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 10px 28px rgba(15,23,42,.06); }
        .panel-head { padding: 18px 20px; border-bottom: 1px solid #e2e8f0; }
        .panel-body { padding: 18px 20px; }
        .table td, .table th { vertical-align: middle; }
        .reason-cell { max-width: 360px; white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
<main class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Yeu cau huy/doi lich booking</h1>
            <div class="text-muted">Admin duyet hoac tu choi yeu cau tu khach hang.</div>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary" href="index.php?act=admin/quanLyBooking">
                <i class="bi bi-arrow-left"></i> Quay lai booking
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="panel mb-3">
        <div class="panel-body">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                <input type="hidden" name="act" value="booking/changeRequests">
                <div class="col-md-3">
                    <label class="form-label">Trang thai</label>
                    <select name="trang_thai" class="form-select">
                        <option value="">Tat ca</option>
                        <?php foreach (['MoiTao','TuDongDuyet','DaDuyet','TuChoi'] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $status === $st ? 'selected' : ''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-funnel"></i> Loc</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-head d-flex justify-content-between align-items-center">
            <strong>Danh sach yeu cau</strong>
            <span class="badge text-bg-secondary"><?php echo count($requests); ?> ban ghi</span>
        </div>
        <div class="panel-body">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info mb-0">Chua co yeu cau nao.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Booking</th>
                            <th>Khach hang</th>
                            <th>Loai</th>
                            <th>Lich hien tai</th>
                            <th>Lich moi</th>
                            <th>Phi huy</th>
                            <th>Ly do</th>
                            <th>Trang thai</th>
                            <th>Xu ly</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($requests as $r): ?>
                            <?php
                                $st = (string)($r['trang_thai'] ?? 'MoiTao');
                                $isClosed = in_array($st, ['DaDuyet', 'TuChoi'], true);
                            ?>
                            <tr>
                                <td><?php echo (int)($r['id'] ?? 0); ?></td>
                                <td>
                                    #<?php echo (int)($r['booking_id'] ?? 0); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars((string)($r['ten_tour'] ?? '')); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars((string)($r['ho_ten'] ?? '')); ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars((string)($r['so_dien_thoai'] ?? '')); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars((string)($r['loai_yeu_cau'] ?? '')); ?></td>
                                <td><?php echo !empty($r['booking_ngay_khoi_hanh']) ? htmlspecialchars((string)$r['booking_ngay_khoi_hanh']) : '-'; ?></td>
                                <td><?php echo !empty($r['ngay_khoi_hanh_moi']) ? htmlspecialchars((string)$r['ngay_khoi_hanh_moi']) : '-'; ?></td>
                                <td><?php echo number_format((float)($r['phi_huy'] ?? 0)); ?> VND</td>
                                <td class="reason-cell"><?php echo htmlspecialchars((string)($r['ly_do'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($st); ?></td>
                                <td style="min-width: 270px;">
                                    <?php if ($isClosed): ?>
                                        <div class="small text-muted">Da xu ly</div>
                                        <?php if (!empty($r['ghi_chu_xu_ly'])): ?>
                                            <div class="small mt-1"><?php echo htmlspecialchars((string)$r['ghi_chu_xu_ly']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <form method="POST" action="index.php?act=booking/processChangeRequest" class="d-grid gap-2">
                                            <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="request_id" value="<?php echo (int)($r['id'] ?? 0); ?>">
                                            <textarea name="ghi_chu_xu_ly" class="form-control form-control-sm" rows="2" placeholder="Ghi chu xu ly (tu chon)"></textarea>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-sm btn-success" type="submit" name="action" value="approve">
                                                    <i class="bi bi-check-circle"></i> Duyet
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" type="submit" name="action" value="reject">
                                                    <i class="bi bi-x-circle"></i> Tu choi
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
