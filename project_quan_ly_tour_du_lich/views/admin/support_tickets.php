<?php
$pageTitle = 'Quan ly ticket ho tro';
$tickets = $tickets ?? [];
$status = $status ?? '';
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
        body { background:#f4f6fb; }
        .wrap { max-width: 1300px; margin: 24px auto; padding: 0 14px; }
        .cardx { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.06); }
    </style>
</head>
<body>
<main class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h4 mb-1">Quan ly ticket ho tro</h1>
            <div class="text-muted">Theo doi SLA va xu ly phan hoi khach hang.</div>
        </div>
        <a href="index.php?act=admin/dashboard" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="cardx p-3 mb-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="act" value="admin/tickets">
            <div class="col-md-3">
                <label class="form-label">Trang thai</label>
                <select class="form-select" name="status">
                    <option value="">Tat ca</option>
                    <?php foreach (['Open','InProgress','WaitingCustomer','Resolved','Closed'] as $s): ?>
                        <option value="<?php echo $s; ?>" <?php echo $status === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">Loc</button>
            </div>
        </form>
    </section>

    <section class="cardx p-3">
        <?php if (empty($tickets)): ?>
            <div class="alert alert-info mb-0">Khong co ticket nao.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th>Ticket</th>
                        <th>Khach hang</th>
                        <th>Tieu de</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>SLA</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tickets as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars((string)($t['ticket_code'] ?? '')); ?></td>
                            <td>
                                <?php echo htmlspecialchars((string)($t['ho_ten'] ?? '')); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars((string)($t['so_dien_thoai'] ?? '')); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars((string)($t['subject'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['priority'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['status'] ?? '')); ?></td>
                            <td><?php echo htmlspecialchars((string)($t['sla_due_at'] ?? '-')); ?></td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="index.php?act=admin/ticketDetail&id=<?php echo (int)($t['id'] ?? 0); ?>">Xu ly</a>
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
