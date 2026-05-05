<?php
/** @var array $user */
/** @var string|null $pendingSecret */
$pendingSecret = $pendingSecret ?? null;

$pageTitle   = 'Cài đặt 2FA';
$currentPage = 'settings';

$twoFactorEnabled  = !empty($user['two_factor_enabled']);
$email             = htmlspecialchars((string)($user['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$hoTen             = htmlspecialchars((string)($user['ho_ten'] ?? ''), ENT_QUOTES, 'UTF-8');
$showSetupQr       = !$twoFactorEnabled && !empty($pendingSecret);
$otpauthUri        = $showSetupQr ? Totp::otpauthUri((string)$pendingSecret, $email, 'QuanLyTour') : '';
$qrUrl             = $showSetupQr ? Totp::qrCodeUrl($otpauthUri) : '';

ob_start();
?>
<div class="container-fluid px-4 py-4" style="max-width:680px;">

    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="index.php?act=admin/dashboard" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Dashboard
        </a>
        <h4 class="mb-0 ms-2"><i class="bi bi-shield-lock me-2"></i>Xác thực 2 bước (2FA)</h4>
    </div>

    <?php if (!empty($info)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-1"></i><?php echo htmlspecialchars($info); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Trạng thái hiện tại -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body d-flex align-items-center gap-3 py-3">
            <?php if ($twoFactorEnabled): ?>
                <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-shield-fill-check me-1"></i>Đã bật</span>
                <div>
                    <div class="fw-semibold">2FA đang hoạt động</div>
                    <div class="text-muted small">Mỗi lần đăng nhập sẽ yêu cầu mã từ ứng dụng Authenticator.</div>
                </div>
            <?php else: ?>
                <span class="badge bg-secondary fs-6 px-3 py-2"><i class="bi bi-shield me-1"></i>Chưa bật</span>
                <div>
                    <div class="fw-semibold">2FA chưa được kích hoạt</div>
                    <div class="text-muted small">Bật 2FA để tăng cường bảo mật cho tài khoản Admin.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$twoFactorEnabled): ?>
    <!-- ── Bật 2FA ───────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent fw-semibold">
            <i class="bi bi-qr-code me-1"></i> Bật xác thực 2 bước
        </div>
        <div class="card-body">
            <ol class="mb-3 ps-3 small text-muted">
                <li class="mb-1">Cài ứng dụng <strong>Google Authenticator</strong>, <strong>Authy</strong> hoặc tương tự.</li>
                <li class="mb-1">Quét mã QR bên dưới bằng ứng dụng.</li>
                <li class="mb-1">Nhập mã 6 chữ số hiển thị trong ứng dụng vào ô xác nhận.</li>
            </ol>

            <?php if ($showSetupQr): ?>
                <div class="text-center mb-3">
                    <img src="<?php echo htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8'); ?>"
                         alt="QR Code 2FA" width="220" height="220"
                         class="border rounded p-1 bg-white" loading="lazy">
                    <div class="mt-2 text-muted small">Không quét được? Nhập thủ công key:</div>
                    <code class="d-block text-center mt-1 fs-6 user-select-all letter-spacing-2">
                        <?php echo htmlspecialchars((string)$pendingSecret, ENT_QUOTES, 'UTF-8'); ?>
                    </code>
                </div>

                <form method="POST" action="index.php?act=auth/setup2fa">
                    <?php echo csrfField('auth_2fa_setup'); ?>
                    <input type="hidden" name="action" value="enable">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mã xác thực (6 chữ số)</label>
                        <input type="text" name="totp_code" class="form-control form-control-lg text-center fw-bold"
                               placeholder="000000" maxlength="6" inputmode="numeric"
                               pattern="[0-9]{6}" autocomplete="one-time-code" autofocus required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-check-circle me-1"></i> Xác nhận & Bật 2FA
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <!-- ── Tắt 2FA ───────────────────────────────────────────────────── -->
    <div class="card border-0 shadow-sm border-danger mb-4">
        <div class="card-header bg-transparent text-danger fw-semibold">
            <i class="bi bi-shield-x me-1"></i> Tắt xác thực 2 bước
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Tắt 2FA sẽ làm giảm bảo mật. Bạn cần xác nhận mật khẩu để tiếp tục.
            </p>
            <form method="POST" action="index.php?act=auth/setup2fa">
                <?php echo csrfField('auth_2fa_setup'); ?>
                <input type="hidden" name="action" value="disable">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nhập mật khẩu để xác nhận</label>
                    <input type="password" name="password" class="form-control" placeholder="Mật khẩu hiện tại" required>
                </div>
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-shield-x me-1"></i> Tắt 2FA
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

</div>
<style>
.letter-spacing-2 { letter-spacing:.25rem; }
</style>
<?php
$content = ob_get_clean();
require 'views/layouts/aventura.php';
