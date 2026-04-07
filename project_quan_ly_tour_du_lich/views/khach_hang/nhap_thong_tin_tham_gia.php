<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhap thong tin nguoi tham gia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fb; }
        .card-soft { border: none; border-radius: 16px; box-shadow: 0 12px 32px rgba(0,0,0,.08); }
        .participant-box { border: 1px solid #e7ebf3; border-radius: 12px; padding: 14px; background: #fff; }
    </style>
</head>
<body>
<div class="container py-4 py-md-5">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <h3 class="mb-0">Nhap thong tin nguoi tham gia</h3>
        <a href="index.php?act=khachHang/hoaDon&booking_id=<?php echo (int)$bookingId; ?>" class="btn btn-outline-secondary btn-sm">Quay lai hoa don</a>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <div><strong>Booking:</strong> #<?php echo (int)$bookingId; ?></div>
            <div><strong>Tour:</strong> <?php echo htmlspecialchars((string)($booking['ten_tour'] ?? 'N/A')); ?></div>
            <div><strong>So nguoi can khai bao:</strong> <?php echo (int)$requiredCount; ?></div>
        </div>
    </div>

    <div class="card card-soft mb-3">
        <div class="card-body">
            <div class="fw-semibold mb-2">Nguoi dai dien dat tour</div>
            <div class="row g-2 small">
                <div class="col-md-4"><strong>Ho ten:</strong> <?php echo htmlspecialchars((string)($booking['ho_ten'] ?? 'Chua cap nhat')); ?></div>
                <div class="col-md-4"><strong>So dien thoai:</strong> <?php echo htmlspecialchars((string)($booking['so_dien_thoai'] ?? 'Chua cap nhat')); ?></div>
                <div class="col-md-4"><strong>Email:</strong> <?php echo htmlspecialchars((string)($booking['email'] ?? 'Chua cap nhat')); ?></div>
                <div class="col-md-12"><strong>Dia chi:</strong> <?php echo htmlspecialchars((string)($booking['dia_chi'] ?? 'Chua cap nhat')); ?></div>
            </div>
            <div class="text-muted small mt-2">Thong tin nay chi de doi chieu. Danh sach ben duoi la thong tin thuc te cua tat ca nguoi tham gia chuyen di.</div>
        </div>
    </div>

    <?php
    $sourceRows = [];
    if (!empty($draft) && is_array($draft)) {
        foreach ($draft as $row) {
            if (is_array($row)) {
                $sourceRows[] = $row;
            }
        }
    } elseif (!empty($existingRows) && is_array($existingRows)) {
        $sourceRows = $existingRows;
    }

    while (count($sourceRows) < (int)$requiredCount) {
        $sourceRows[] = [];
    }

    if (!isset($sourceRows[0]) || !is_array($sourceRows[0])) {
        $sourceRows[0] = [];
    }
    $sourceRows[0] = array_merge([
        'ho_ten' => (string)($booking['ho_ten'] ?? ''),
        'so_dien_thoai' => (string)($booking['so_dien_thoai'] ?? ''),
        'email' => (string)($booking['email'] ?? ''),
        'dia_chi' => (string)($booking['dia_chi'] ?? ''),
        'quoc_tich' => 'Việt Nam',
        'gioi_tinh' => 'Khac',
    ], $sourceRows[0]);
    ?>

    <form method="POST" enctype="multipart/form-data" action="index.php?act=khachHang/nhapThongTinThamGia&booking_id=<?php echo (int)$bookingId; ?>">
        <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">

        <div class="card card-soft">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <div class="fw-semibold">Danh sach nguoi tham gia</div>
                        <div class="text-muted small">Khai bao day du thong tin cho tung hanh khach de luu vao bang tour_checkin.</div>
                    </div>
                    <span class="badge text-bg-light border">Tong: <?php echo (int)$requiredCount; ?> nguoi</span>
                </div>
                <?php for ($i = 0; $i < (int)$requiredCount; $i++):
                    $p = $sourceRows[$i] ?? [];
                    $rowErrors = $participantErrors[$i] ?? [];
                ?>
                    <div class="participant-box mb-3">
                        <div class="fw-semibold mb-2"><?php echo $i === 0 ? 'Nguoi tham gia 1 (de xuat: nguoi dai dien)' : ('Nguoi tham gia ' . ($i + 1)); ?></div>
                        <?php if ($i === 0): ?>
                            <div class="small text-muted mb-2">Dong nay duoc dien san theo thong tin nguoi dat tour. Co the giu nguyen neu nguoi dat tour cung la hanh khach.</div>
                        <?php endif; ?>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Ho ten *</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][ho_ten]" required value="<?php echo htmlspecialchars((string)($p['ho_ten'] ?? '')); ?>">
                                <?php if (!empty($rowErrors['ho_ten'])): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars((string)$rowErrors['ho_ten']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ngay sinh</label>
                                <input type="date" class="form-control" name="participants[<?php echo $i; ?>][ngay_sinh]" value="<?php echo htmlspecialchars((string)($p['ngay_sinh'] ?? '')); ?>">
                                <?php if (!empty($rowErrors['ngay_sinh'])): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars((string)$rowErrors['ngay_sinh']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Gioi tinh</label>
                                <?php $gt = (string)($p['gioi_tinh'] ?? 'Khac'); ?>
                                <select class="form-select" name="participants[<?php echo $i; ?>][gioi_tinh]">
                                    <option value="Nam" <?php echo $gt === 'Nam' ? 'selected' : ''; ?>>Nam</option>
                                    <option value="Nữ" <?php echo $gt === 'Nữ' ? 'selected' : ''; ?>>Nu</option>
                                    <option value="Khac" <?php echo $gt === 'Khac' ? 'selected' : ''; ?>>Khac</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">So dien thoai</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][so_dien_thoai]" value="<?php echo htmlspecialchars((string)($p['so_dien_thoai'] ?? '')); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="participants[<?php echo $i; ?>][email]" value="<?php echo htmlspecialchars((string)($p['email'] ?? '')); ?>">
                                <?php if (!empty($rowErrors['email'])): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars((string)$rowErrors['email']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Quoc tich</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][quoc_tich]" value="<?php echo htmlspecialchars((string)($p['quoc_tich'] ?? 'Việt Nam')); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">So CMND/CCCD</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][so_cmnd]" value="<?php echo htmlspecialchars((string)($p['so_cmnd'] ?? '')); ?>">
                                <input type="hidden" name="participants[<?php echo $i; ?>][existing_anh_cccd]" value="<?php echo htmlspecialchars((string)($p['anh_cccd'] ?? '')); ?>">
                                <div class="small text-muted mt-1">Nhap 9 hoac 12 chu so neu co.</div>
                                <?php if (!empty($rowErrors['so_cmnd'])): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars((string)$rowErrors['so_cmnd']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">So Passport</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][so_passport]" value="<?php echo htmlspecialchars((string)($p['so_passport'] ?? '')); ?>">
                                <input type="hidden" name="participants[<?php echo $i; ?>][existing_anh_passport]" value="<?php echo htmlspecialchars((string)($p['anh_passport'] ?? '')); ?>">
                                <div class="small text-muted mt-1">Chi gom chu va so, dai 6-12 ky tu neu co.</div>
                                <?php if (!empty($rowErrors['so_passport'])): ?><div class="text-danger small mt-1"><?php echo htmlspecialchars((string)$rowErrors['so_passport']); ?></div><?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Anh CCCD/CMND</label>
                                <input type="file" class="form-control" name="participants[<?php echo $i; ?>][anh_cccd]" accept="image/*,.pdf">
                                <?php if (!empty($p['anh_cccd'])): ?>
                                    <div class="small mt-1">Da co file: <a href="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim((string)$p['anh_cccd'], '/')); ?>" target="_blank" rel="noopener">Xem anh CCCD/CMND</a></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Anh Passport</label>
                                <input type="file" class="form-control" name="participants[<?php echo $i; ?>][anh_passport]" accept="image/*,.pdf">
                                <?php if (!empty($p['anh_passport'])): ?>
                                    <div class="small mt-1">Da co file: <a href="<?php echo htmlspecialchars(rtrim(BASE_URL, '/') . '/' . ltrim((string)$p['anh_passport'], '/')); ?>" target="_blank" rel="noopener">Xem anh passport</a></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Dia chi</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][dia_chi]" value="<?php echo htmlspecialchars((string)($p['dia_chi'] ?? '')); ?>">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Ghi chu</label>
                                <input type="text" class="form-control" name="participants[<?php echo $i; ?>][ghi_chu]" value="<?php echo htmlspecialchars((string)($p['ghi_chu'] ?? '')); ?>">
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Luu thong tin nguoi tham gia</button>
                    <a class="btn btn-outline-secondary" href="index.php?act=khachHang/hoaDon&booking_id=<?php echo (int)$bookingId; ?>">De sau</a>
                </div>
            </div>
        </div>
    </form>
</div>
</body>
</html>
