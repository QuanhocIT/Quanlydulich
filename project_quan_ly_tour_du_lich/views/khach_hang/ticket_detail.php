<?php
$pageTitle = 'Chi tiet ticket';
$ticket = $ticket ?? [];
$messages = $messages ?? [];
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
        body { background:#f4f8ff; }
        .wrap { max-width: 980px; margin: 24px auto; padding: 0 14px; }
        .cardx { background:#fff; border:1px solid #e2e8f0; border-radius:16px; box-shadow:0 12px 28px rgba(15,23,42,.06); }
        .msg { border:1px solid #e2e8f0; border-radius:12px; padding:10px 12px; margin-bottom:10px; }
    </style>
</head>
<body>
<main class="wrap">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 mb-0">Ticket <?php echo htmlspecialchars((string)($ticket['ticket_code'] ?? '')); ?></h1>
        <a href="index.php?act=khachHang/tickets" class="btn btn-outline-secondary btn-sm">Quay lai</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <section class="cardx p-3 mb-3">
        <div><strong>Tieu de:</strong> <?php echo htmlspecialchars((string)($ticket['subject'] ?? '')); ?></div>
        <div><strong>Trang thai:</strong> <?php echo htmlspecialchars((string)($ticket['status'] ?? '')); ?></div>
        <div><strong>Do uu tien:</strong> <?php echo htmlspecialchars((string)($ticket['priority'] ?? '')); ?></div>
        <div><strong>SLA:</strong> <?php echo htmlspecialchars((string)($ticket['sla_due_at'] ?? '-')); ?></div>
    </section>

    <section class="cardx p-3 mb-3">
        <h2 class="h6 mb-3">Hoi thoai</h2>
        <?php if (empty($messages)): ?>
            <div class="alert alert-info mb-0">Chua co tin nhan nao.</div>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
                <article class="msg">
                    <div class="small text-muted mb-1"><?php echo htmlspecialchars((string)($m['sender_role'] ?? '')); ?> - <?php echo htmlspecialchars((string)($m['created_at'] ?? '')); ?></div>
                    <div><?php echo nl2br(htmlspecialchars((string)($m['message'] ?? ''))); ?></div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section class="cardx p-3">
        <h2 class="h6 mb-3">Gui phan hoi</h2>
        <form method="POST" action="index.php?act=khachHang/ticketReply">
            <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="ticket_id" value="<?php echo (int)($ticket['id'] ?? 0); ?>">
            <textarea class="form-control mb-2" name="message" rows="4" minlength="2" maxlength="2000" required placeholder="Nhap noi dung phan hoi..."></textarea>
            <div class="text-end">
                <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Gui</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
