<?php
$pageTitle = 'Trung tam ho tro';
$tickets = $tickets ?? [];
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
        body { background:#f4f8ff; color:#0f172a; }
        .wrap { max-width: 1100px; margin: 24px auto; padding: 0 14px; }
        .cardx { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.06); }
    </style>
</head>
<body>
<main class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Trung tam ho tro</h1>
            <div class="text-muted">Tao ticket, theo doi SLA va phan hoi 2 chieu voi admin.</div>
        </div>
        <a href="index.php?act=khachHang/dashboard" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="cardx p-3 mb-3">
        <h2 class="h6 mb-3">Tao ticket moi</h2>
        <form method="POST" action="index.php?act=khachHang/ticketCreate" class="row g-2">
            <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="col-md-6">
                <label class="form-label">Tieu de</label>
                <input class="form-control" name="subject" required minlength="5" maxlength="255" placeholder="Mo ta ngan gon van de...">
            </div>
            <div class="col-md-3">
                <label class="form-label">Do uu tien</label>
                <select class="form-select" name="priority">
                    <option value="TrungBinh">Trung binh</option>
                    <option value="Thap">Thap</option>
                    <option value="Cao">Cao</option>
                    <option value="KhanCap">Khan cap</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Booking (neu co)</label>
                <input class="form-control" name="booking_id" type="number" min="0" placeholder="#booking">
            </div>
            <div class="col-12">
                <label class="form-label">Noi dung</label>
                <textarea class="form-control" name="message" rows="4" required minlength="10" maxlength="2000" placeholder="Mo ta chi tiet tinh huong can ho tro..."></textarea>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Tao ticket</button>
            </div>
        </form>
    </section>

    <section class="cardx p-3">
        <h2 class="h6 mb-3">Danh sach ticket</h2>
        <?php if (empty($tickets)): ?>
            <div class="alert alert-info mb-0">Ban chua co ticket nao.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Ma ticket</th>
                        <th>Tieu de</th>
                        <th>Muc do</th>
                        <th>Trang thai</th>
                        <th>SLA den</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)($t['ticket_code'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['subject'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['priority'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['status'] ?? '')); ?></td>
                            <td><?php echo !empty($t['sla_due_at']) ? htmlspecialchars((string)$t['sla_due_at']) : '-'; ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="index.php?act=khachHang/ticketDetail&id=<?php echo (int)($t['id'] ?? 0); ?>">Chi tiet</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
