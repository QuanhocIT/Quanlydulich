<?php
$pageTitle = 'Hồ Sơ & Bảo Mật Quản Trị Viên';
$currentPage = 'profile';

$userObj = $user ?? [];
$userName = htmlspecialchars((string)($userObj['ho_ten'] ?? $_SESSION['user_name'] ?? 'Admin'));
$userLogin = htmlspecialchars((string)($userObj['ten_dang_nhap'] ?? ''));
$userEmail = htmlspecialchars((string)($userObj['email'] ?? ''));
$userPhone = htmlspecialchars((string)($userObj['so_dien_thoai'] ?? ''));
$userRole = (string)($userObj['vai_tro'] ?? 'Admin');
$userStatus = (string)($userObj['trang_thai'] ?? 'HoatDong');
$is2FA = !empty($twoFactorEnabled);
$isSuperAdmin = !empty($userObj['quyen_cap_cao']);
$userInitial = mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8');

ob_start();
?>

<div class="aventura-content" style="max-width: 1100px; margin: 0 auto; padding-bottom: 40px;">
    <!-- Header Title -->
    <div class="aventura-header" style="margin-bottom: 24px;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 class="aventura-title" style="font-size: 26px; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-shield-lock" style="color: #dfa974;"></i> Trung Tâm Hồ Sơ & Bảo Mật Admin
                </h1>
                <p style="margin: 6px 0 0; color: rgba(255, 255, 255, 0.6); font-size: 14px;">
                    Quản lý thông tin định danh quản trị, đổi mật khẩu và thiết lập xác thực đa yếu tố bảo vệ hệ thống.
                </p>
            </div>
            <div>
                <a href="index.php?act=admin/quanLyNguoiDung" class="aventura-btn aventura-btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; border-radius: 8px; font-size: 13px;">
                    <i class="bi bi-people"></i> Quản lý người dùng
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Alerts -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div style="padding: 14px 18px; border-radius: 10px; background: rgba(30, 127, 79, 0.2); border: 1px solid rgba(46, 204, 113, 0.4); color: #8be0b6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <i class="bi bi-check-circle-fill" style="font-size: 18px;"></i>
            <div><?php echo htmlspecialchars((string)$_SESSION['success']); unset($_SESSION['success']); ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div style="padding: 14px 18px; border-radius: 10px; background: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.4); color: #ff9b9b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 14px;">
            <i class="bi bi-exclamation-triangle-fill" style="font-size: 18px;"></i>
            <div><?php echo htmlspecialchars((string)$_SESSION['error']); unset($_SESSION['error']); ?></div>
        </div>
    <?php endif; ?>

    <!-- Layout 2 Columns: Sidebar Summary & Main Settings -->
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 24px; align-items: start;" class="admin-profile-grid">
        <!-- Col Left: Profile Identity Badge -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="aventura-card" style="padding: 26px 20px; text-align: center; border-radius: 14px; background: rgba(20, 24, 33, 0.85); border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);">
                <!-- Avatar Circle -->
                <div style="width: 86px; height: 86px; margin: 0 auto 16px; border-radius: 50%; background: linear-gradient(135deg, #dfa974, #b27a3c); display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; color: #10141d; box-shadow: 0 4px 16px rgba(223, 169, 116, 0.35); border: 3px solid rgba(255, 255, 255, 0.15);">
                    <?php echo $userInitial; ?>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0 0 6px;"><?php echo $userName; ?></h3>
                <div style="font-size: 13px; color: rgba(255, 255, 255, 0.55); margin-bottom: 14px;">@<?php echo $userLogin; ?></div>

                <!-- Badges -->
                <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 20px;">
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(223, 169, 116, 0.2); color: #dfa974; border: 1px solid rgba(223, 169, 116, 0.4);">
                        <i class="bi bi-shield-check"></i> <?php echo $userRole; ?>
                    </span>
                    <?php if ($isSuperAdmin): ?>
                        <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(142, 68, 173, 0.25); color: #c39bd3; border: 1px solid rgba(142, 68, 173, 0.45);">
                            <i class="bi bi-star-fill"></i> Quyền cấp cao
                        </span>
                    <?php endif; ?>
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid rgba(46, 204, 113, 0.4);">
                        <i class="bi bi-circle-fill" style="font-size: 7px;"></i> <?php echo $userStatus === 'HoatDong' ? 'Hoạt động' : 'Bị khóa'; ?>
                    </span>
                </div>

                <div style="text-align: left; border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255, 255, 255, 0.5);"><i class="bi bi-envelope"></i> Email:</span>
                        <span style="color: #fff; font-weight: 500; max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo $userEmail ?: 'Chưa cập nhật'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255, 255, 255, 0.5);"><i class="bi bi-telephone"></i> SĐT:</span>
                        <span style="color: #fff; font-weight: 500;"><?php echo $userPhone ?: 'Chưa cập nhật'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: rgba(255, 255, 255, 0.5);"><i class="bi bi-shield-lock"></i> 2FA TOTP:</span>
                        <span style="font-weight: 600; color: <?php echo $is2FA ? '#2ecc71' : '#e74c3c'; ?>;">
                            <?php echo $is2FA ? 'Đã kích hoạt' : 'Chưa bật'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Navigation Menu -->
            <div class="aventura-card" style="padding: 16px; border-radius: 12px; background: rgba(20, 24, 33, 0.7); border: 1px solid rgba(255, 255, 255, 0.06);">
                <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: rgba(255, 255, 255, 0.4); margin-bottom: 10px; letter-spacing: 0.5px;">Mục quản lý</div>
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <a href="#info-section" class="profile-nav-link active" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; color: #fff; text-decoration: none; font-size: 13px; transition: all 0.2s; background: rgba(255, 255, 255, 0.05);">
                        <i class="bi bi-person-lines-fill" style="color: #dfa974;"></i> Thông tin cá nhân
                    </a>
                    <a href="#password-section" class="profile-nav-link" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; color: rgba(255, 255, 255, 0.75); text-decoration: none; font-size: 13px; transition: all 0.2s;">
                        <i class="bi bi-key" style="color: #3498db;"></i> Đổi mật khẩu
                    </a>
                    <a href="#security-section" class="profile-nav-link" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 8px; color: rgba(255, 255, 255, 0.75); text-decoration: none; font-size: 13px; transition: all 0.2s;">
                        <i class="bi bi-shield-shaded" style="color: #2ecc71;"></i> Bảo mật 2FA & Phiên
                    </a>
                </div>
            </div>
        </div>

        <!-- Col Right: Forms and Security Controls -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Section 1: Thông tin cá nhân -->
            <div id="info-section" class="aventura-card" style="padding: 24px; border-radius: 14px; background: rgba(20, 24, 33, 0.85); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 14px;">
                    <i class="bi bi-person-badge" style="font-size: 22px; color: #dfa974;"></i>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Thông Tin Tài Khoản</h2>
                        <div style="font-size: 13px; color: rgba(255, 255, 255, 0.5);">Cập nhật tên hiển thị, địa chỉ email liên lạc và số điện thoại công tác.</div>
                    </div>
                </div>

                <form method="post" action="index.php?act=admin/updateProfile">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('admin_form')); ?>">
                    <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form')); ?>">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Tên đăng nhập</label>
                            <input type="text" value="<?php echo $userLogin; ?>" disabled style="width: 100%; padding: 10px 14px; border-radius: 8px; background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); color: rgba(255, 255, 255, 0.5); font-size: 14px; cursor: not-allowed;">
                            <span style="font-size: 11px; color: rgba(255, 255, 255, 0.4); margin-top: 4px; display: block;">Tên đăng nhập cố định không thể chỉnh sửa</span>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Họ và tên hiển thị <span style="color: #e74c3c;">*</span></label>
                            <input type="text" name="ho_ten" required value="<?php echo $userName; ?>" placeholder="Nhập họ và tên..." style="width: 100%; padding: 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none; transition: border-color 0.2s;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 22px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Email liên hệ <span style="color: #e74c3c;">*</span></label>
                            <input type="email" name="email" required value="<?php echo $userEmail; ?>" placeholder="admin@domain.com" style="width: 100%; padding: 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Số điện thoại</label>
                            <input type="text" name="so_dien_thoai" value="<?php echo $userPhone; ?>" placeholder="0901234567" style="width: 100%; padding: 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none;">
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="aventura-btn aventura-btn-gold" style="padding: 10px 22px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border: none; cursor: pointer;">
                            <i class="bi bi-save"></i> Lưu thông tin hồ sơ
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 2: Đổi Mật Khẩu -->
            <div id="password-section" class="aventura-card" style="padding: 24px; border-radius: 14px; background: rgba(20, 24, 33, 0.85); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 14px;">
                    <i class="bi bi-shield-lock" style="font-size: 22px; color: #3498db;"></i>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Đổi Mật Khẩu Quản Trị</h2>
                        <div style="font-size: 13px; color: rgba(255, 255, 255, 0.5);">Đổi mật khẩu định kỳ để nâng cao tính an toàn và ngăn chặn truy cập trái phép.</div>
                    </div>
                </div>

                <form method="post" action="index.php?act=admin/changePassword" id="changePasswordForm">
                    <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(csrfToken('admin_form')); ?>">
                    <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form')); ?>">

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Mật khẩu hiện tại <span style="color: #e74c3c;">*</span></label>
                        <div style="position: relative;">
                            <input type="password" name="current_password" id="current_password" required placeholder="Nhập mật khẩu hiện tại của bạn" style="width: 100%; padding: 10px 42px 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none;">
                            <button type="button" onclick="togglePasswordVisibility('current_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255, 255, 255, 0.5); cursor: pointer; font-size: 16px;">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 18px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Mật khẩu mới (tối thiểu 8 ký tự) <span style="color: #e74c3c;">*</span></label>
                            <div style="position: relative;">
                                <input type="password" name="new_password" id="new_password" required minlength="8" placeholder="Nhập mật khẩu mới" oninput="checkPasswordStrength(this.value)" style="width: 100%; padding: 10px 42px 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none;">
                                <button type="button" onclick="togglePasswordVisibility('new_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255, 255, 255, 0.5); cursor: pointer; font-size: 16px;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <!-- Strength Indicator -->
                            <div id="pwd-strength-bar" style="height: 4px; border-radius: 2px; background: rgba(255, 255, 255, 0.1); margin-top: 8px; overflow: hidden;">
                                <div id="pwd-strength-fill" style="width: 0%; height: 100%; transition: width 0.3s, background-color 0.3s;"></div>
                            </div>
                            <span id="pwd-strength-text" style="font-size: 11px; color: rgba(255, 255, 255, 0.4); margin-top: 4px; display: block;">Độ mạnh mật khẩu</span>
                        </div>

                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 600; color: rgba(255, 255, 255, 0.8); margin-bottom: 6px;">Xác nhận mật khẩu mới <span style="color: #e74c3c;">*</span></label>
                            <div style="position: relative;">
                                <input type="password" name="confirm_password" id="confirm_password" required minlength="8" placeholder="Nhập lại mật khẩu mới" style="width: 100%; padding: 10px 42px 10px 14px; border-radius: 8px; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; font-size: 14px; outline: none;">
                                <button type="button" onclick="togglePasswordVisibility('confirm_password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: rgba(255, 255, 255, 0.5); cursor: pointer; font-size: 16px;">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end;">
                        <button type="submit" class="aventura-btn" style="padding: 10px 22px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; background: #3498db; color: #fff; border: none; cursor: pointer;">
                            <i class="bi bi-check2-circle"></i> Cập nhật mật khẩu mới
                        </button>
                    </div>
                </form>
            </div>

            <!-- Section 3: Bảo Mật 2FA & Phiên Làm Việc -->
            <div id="security-section" class="aventura-card" style="padding: 24px; border-radius: 14px; background: rgba(20, 24, 33, 0.85); border: 1px solid rgba(255, 255, 255, 0.08);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 14px;">
                    <i class="bi bi-shield-check" style="font-size: 22px; color: #2ecc71;"></i>
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #fff; margin: 0;">Bảo Mật Hai Lớp (2FA) & Phiên Làm Việc</h2>
                        <div style="font-size: 13px; color: rgba(255, 255, 255, 0.5);">Bảo vệ tài khoản chống lại việc rò rỉ mật khẩu bằng mã OTP thời gian thực.</div>
                    </div>
                </div>

                <!-- 2FA Status Box -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px; border-radius: 10px; background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.07); margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; background: <?php echo $is2FA ? 'rgba(46, 204, 113, 0.2)' : 'rgba(231, 76, 60, 0.2)'; ?>; color: <?php echo $is2FA ? '#2ecc71' : '#e74c3c'; ?>;">
                            <i class="bi bi-<?php echo $is2FA ? 'shield-fill-check' : 'shield-fill-exclamation'; ?>"></i>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #fff; font-size: 14px;">
                                Trạng thái xác thực 2FA:
                                <span style="color: <?php echo $is2FA ? '#2ecc71' : '#e74c3c'; ?>;"><?php echo $is2FA ? 'Đã kích hoạt' : 'Chưa thiết lập'; ?></span>
                            </div>
                            <div style="font-size: 12px; color: rgba(255, 255, 255, 0.5); margin-top: 2px;">
                                <?php echo $is2FA ? 'Tài khoản của bạn được bảo vệ bởi TOTP Authenticator.' : 'Khuyến nghị kích hoạt 2FA ngay để đảm bảo an toàn tuyệt đối cho quyền quản trị.'; ?>
                            </div>
                        </div>
                    </div>

                    <div>
                        <a href="index.php?act=auth/setup2fa" class="aventura-btn <?php echo $is2FA ? 'aventura-btn-outline' : 'aventura-btn-gold'; ?>" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: 8px; font-size: 13px; font-weight: 600;">
                            <i class="bi bi-qr-code-scan"></i> <?php echo $is2FA ? 'Cài đặt lại 2FA' : 'Kích hoạt 2FA ngay'; ?>
                        </a>
                    </div>
                </div>

                <!-- Session & Security Info -->
                <div style="border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 18px;">
                    <div style="font-size: 13px; font-weight: 700; color: rgba(255, 255, 255, 0.7); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                        <i class="bi bi-activity"></i> Thông tin phiên làm việc hiện tại
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; font-size: 13px;">
                        <div style="padding: 12px; border-radius: 8px; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.05);">
                            <div style="color: rgba(255, 255, 255, 0.5); font-size: 11px;">Địa chỉ IP truy cập</div>
                            <div style="color: #fff; font-weight: 600; margin-top: 3px; font-family: monospace;"><?php echo htmlspecialchars((string)($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1')); ?></div>
                        </div>
                        <div style="padding: 12px; border-radius: 8px; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.05);">
                            <div style="color: rgba(255, 255, 255, 0.5); font-size: 11px;">Mã định danh User ID</div>
                            <div style="color: #fff; font-weight: 600; margin-top: 3px;"><?php echo (int)($userObj['id'] ?? $_SESSION['user_id'] ?? 0); ?></div>
                        </div>
                        <div style="padding: 12px; border-radius: 8px; background: rgba(0, 0, 0, 0.25); border: 1px solid rgba(255, 255, 255, 0.05);">
                            <div style="color: rgba(255, 255, 255, 0.5); font-size: 11px;">Trình duyệt / Hệ điều hành</div>
                            <div style="color: #fff; font-weight: 500; margin-top: 3px; font-size: 12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars((string)($_SERVER['HTTP_USER_AGENT'] ?? '')); ?>">
                                <?php echo htmlspecialchars(substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'Trình duyệt Web'), 0, 35) . '...'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?php echo defined('CSP_NONCE') ? CSP_NONCE : ''; ?>">
function togglePasswordVisibility(fieldId) {
    const input = document.getElementById(fieldId);
    if (!input) return;
    const btn = input.nextElementSibling;
    const icon = btn ? btn.querySelector('i') : null;
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}

function checkPasswordStrength(val) {
    const fill = document.getElementById('pwd-strength-fill');
    const text = document.getElementById('pwd-strength-text');
    if (!fill || !text) return;

    if (!val || val.length === 0) {
        fill.style.width = '0%';
        fill.style.backgroundColor = 'transparent';
        text.innerText = 'Độ mạnh mật khẩu';
        text.style.color = 'rgba(255,255,255,0.4)';
        return;
    }

    let score = 0;
    if (val.length >= 8) score++;
    if (val.length >= 12) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    if (score <= 1) {
        fill.style.width = '25%';
        fill.style.backgroundColor = '#e74c3c';
        text.innerText = 'Yếu - hãy thêm số, chữ hoa hoặc ký tự đặc biệt';
        text.style.color = '#e74c3c';
    } else if (score === 2 || score === 3) {
        fill.style.width = '65%';
        fill.style.backgroundColor = '#f39c12';
        text.innerText = 'Khá - tốt hơn khi có độ dài > 10 ký tự';
        text.style.color = '#f39c12';
    } else {
        fill.style.width = '100%';
        fill.style.backgroundColor = '#2ecc71';
        text.innerText = 'Rất mạnh - bảo vệ tối đa';
        text.style.color = '#2ecc71';
    }
}

// Client-side confirmation on password form
document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
    const newP = document.getElementById('new_password')?.value || '';
    const confP = document.getElementById('confirm_password')?.value || '';
    if (newP !== confP) {
        e.preventDefault();
        alert('Mật khẩu mới và mật khẩu xác nhận không khớp nhau. Vui lòng kiểm tra lại.');
    }
});
</script>

<style>
@media (max-width: 860px) {
    .admin-profile-grid {
        grid-template-columns: 1fr !important;
    }
}
.profile-nav-link:hover {
    background: rgba(255, 255, 255, 0.08) !important;
    color: #fff !important;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
