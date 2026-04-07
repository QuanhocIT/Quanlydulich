<?php
$pageTitle = 'Cài đặt thông báo';
$currentPage = 'notificationSettings';
ob_start();
?>
<div class="aventura-content" style="max-width: 880px; margin: 0 auto;">
    <div class="aventura-header" style="margin-bottom: 18px;">
        <h1 class="aventura-title"><i class="bi bi-bell"></i> Cài đặt thông báo</h1>
        <p style="margin:8px 0 0;color:var(--text-muted)">Thiết lập âm báo cho thông báo mới trên sidebar Admin.</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div style="padding:12px 14px;border-radius:10px;background:rgba(30,127,79,.2);color:#8be0b6;margin-bottom:14px;">
            <?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <div class="aventura-card" style="padding:20px; border-radius:12px; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.08);">
        <form method="post" action="index.php?act=admin/saveNotificationSettings">
            <label style="display:flex; align-items:center; gap:10px; margin-bottom:14px; cursor:pointer; user-select:none;">
                <input type="checkbox" name="sound_enabled" value="1" <?php echo !empty($soundEnabled) ? 'checked' : ''; ?>>
                <span>Bật âm báo khi có thanh toán hoặc đánh giá mới</span>
            </label>

            <button type="submit" class="aventura-btn aventura-btn-gold">
                <i class="bi bi-check2-circle"></i> Lưu cài đặt
            </button>
        </form>

        <hr style="border:none;border-top:1px solid rgba(255,255,255,.08); margin:16px 0;">

        <form method="post" action="index.php?act=admin/markNotificationsReadAll">
            <button type="submit" class="aventura-btn aventura-btn-outline">
                <i class="bi bi-check2-all"></i> Đánh dấu đã xem tất cả
            </button>
        </form>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
