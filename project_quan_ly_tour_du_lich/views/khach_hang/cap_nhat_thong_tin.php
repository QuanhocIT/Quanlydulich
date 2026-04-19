<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập nhật thông tin - Khách hàng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --pf-ink: #0f1c33;
            --pf-muted: #5c6b84;
            --pf-gold: #d7ad5b;
            --pf-gold-dark: #b88834;
            --pf-sky: #dceeff;
            --pf-border: rgba(16, 31, 56, 0.12);
            --pf-card: rgba(255, 255, 255, 0.88);
        }

        body {
            min-height: 100vh;
            color: var(--pf-ink);
            font-family: "Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(1200px 620px at -8% -12%, rgba(215, 173, 91, 0.26), transparent 58%),
                radial-gradient(900px 520px at 110% 2%, rgba(59, 130, 246, 0.18), transparent 56%),
                linear-gradient(180deg, #f8fbff 0%, #f2f5fb 40%, #eef3fa 100%);
            position: relative;
            overflow-x: hidden;
        }

        .profile-wrap {
            width: min(1500px, calc(100% - 48px));
            margin: 0 auto;
            padding: 24px 0 56px;
        }

        .topbar {
            background: rgba(11,18,32,.92);
            border: 1px solid rgba(214,178,109,.22);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            padding: 12px 14px;
            box-shadow: 0 12px 34px rgba(2,6,23,.18);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
            animation: fadeUp .55s ease both;
        }

        .topbar-title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: #fff;
            text-decoration: none;
        }

        .topbar-title i {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #15233b, #20365f);
            color: #d6b26d;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.14);
        }

        .nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        .nav-pill {
            align-items: center;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(214,178,109,.28);
            border-radius: 999px;
            color: rgba(255,255,255,.9);
            display: inline-flex;
            font-size: 14px;
            font-weight: 700;
            gap: 8px;
            padding: 10px 15px;
            text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
        }

        .nav-pill:hover {
            background: rgba(214,178,109,.18);
            border-color: rgba(214,178,109,.46);
            color: #fff;
        }

        .nav-pill.is-active {
            background: linear-gradient(135deg, #d6b26d, #e2bf78);
            border-color: rgba(214,178,109,.72);
            color: #132033;
            box-shadow: 0 10px 24px rgba(185,137,61,.24);
        }

        .hero {
            background:
                linear-gradient(115deg, rgba(11,18,32,.93), rgba(21,35,59,.78)),
                url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1800&q=80') center/cover;
            border: 1px solid rgba(214,178,109,.2);
            border-radius: 32px;
            box-shadow: 0 30px 80px rgba(2,6,23,.22);
            color: #fff;
            margin-bottom: 16px;
            overflow: hidden;
            padding: 34px 30px;
            position: relative;
            animation: fadeUp .55s ease both;
        }

        .hero::after {
            background: rgba(214,178,109,.14);
            border-radius: 999px;
            content: "";
            height: 260px;
            position: absolute;
            right: -86px;
            top: -86px;
            width: 260px;
        }

        .hero-content {
            max-width: 820px;
            position: relative;
            z-index: 1;
        }

        .hero-title {
            margin: 0 0 10px;
            font-family: "Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
            font-size: clamp(2rem, 4vw, 3.35rem);
            line-height: 1.05;
            letter-spacing: .2px;
            display: inline-flex;
            align-items: center;
            gap: .65rem;
        }

        .hero-subtitle {
            margin: 0;
            color: rgba(255,255,255,.84);
            font-size: 1rem;
            line-height: 1.75;
            max-width: 740px;
        }

        .btn-back {
            border-radius: 999px;
            border: 1px solid var(--pf-border);
            color: var(--pf-ink);
            background: rgba(255, 255, 255, 0.74);
            padding: .56rem .95rem;
            font-weight: 700;
            text-decoration: none;
            transition: .2s ease;
            backdrop-filter: blur(6px);
        }

        .btn-back:hover {
            transform: translateY(-1px);
            color: #0b152a;
            border-color: rgba(183, 136, 52, 0.42);
            box-shadow: 0 14px 30px rgba(16, 31, 56, 0.12);
        }

        .status-alert {
            border: 1px solid transparent;
            border-radius: 16px;
            backdrop-filter: blur(6px);
            box-shadow: 0 10px 26px rgba(15, 23, 42, 0.1);
            animation: fadeUp .6s ease both;
        }

        .status-alert.alert-success {
            background: rgba(16, 185, 129, 0.14);
            border-color: rgba(16, 185, 129, 0.35);
            color: #0f5132;
        }

        .status-alert.alert-danger {
            background: rgba(239, 68, 68, 0.14);
            border-color: rgba(239, 68, 68, 0.35);
            color: #7f1d1d;
        }

        .layout-grid {
            margin-top: 16px;
            row-gap: 18px;
        }

        .profile-side,
        .profile-card {
            border-radius: 22px;
            border: 1px solid var(--pf-border);
            background: var(--pf-card);
            backdrop-filter: blur(12px);
            box-shadow: 0 18px 52px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            animation: fadeUp .65s ease both;
        }

        .profile-side {
            position: sticky;
            top: 24px;
        }

        .side-banner {
            height: 156px;
            background:
                radial-gradient(circle at 18% 24%, rgba(215, 173, 91, 0.30), transparent 46%),
                linear-gradient(120deg, rgba(9, 18, 34, 0.96), rgba(26, 47, 82, 0.86));
            position: relative;
        }

        .side-banner::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 18% 20%, rgba(215, 173, 91, 0.35), transparent 45%);
        }

        .side-body {
            padding: 0 20px 22px;
        }

        .avatar {
            width: 74px;
            height: 74px;
            border-radius: 999px;
            border: 3px solid rgba(255, 255, 255, 0.86);
            margin: -38px 0 10px;
            background: linear-gradient(145deg, #15243d, #2e4b7a);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: .6px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.24);
        }

        .side-name {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 800;
        }

        .side-note {
            color: var(--pf-muted);
            font-size: .92rem;
            margin-top: .35rem;
        }

        .tip-list {
            list-style: none;
            padding: 0;
            margin: 14px 0 0;
            display: grid;
            gap: 10px;
        }

        .tip-list li {
            display: flex;
            align-items: start;
            gap: .55rem;
            padding: 10px 11px;
            border-radius: 12px;
            background: rgba(220, 238, 255, 0.42);
            border: 1px solid rgba(127, 168, 221, 0.2);
            color: #193152;
            font-size: .9rem;
        }

        .tip-list i {
            color: #2c65af;
        }

        .profile-card {
            padding: 22px;
        }

        .section-title {
            font-family: "Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
            margin-bottom: 10px;
            font-size: clamp(1.2rem, 2vw, 1.6rem);
            letter-spacing: .15px;
        }

        .section-subtitle {
            font-size: .92rem;
            color: var(--pf-muted);
            margin-bottom: 20px;
        }

        .form-shell {
            border: 1px solid rgba(15, 23, 42, 0.1);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.6);
            padding: 18px;
            margin-bottom: 16px;
        }

        .form-block-title {
            font-size: 1.02rem;
            font-weight: 800;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: .45rem;
        }

        .form-label {
            font-size: .9rem;
            font-weight: 700;
            color: #24344f;
            margin-bottom: .4rem;
        }

        .form-control,
        .form-select {
            border-radius: 12px;
            border: 1px solid rgba(15, 23, 42, 0.14);
            background: rgba(255, 255, 255, 0.86);
            padding: .65rem .8rem;
            transition: .2s ease;
        }

        .form-control:hover,
        .form-select:hover {
            border-color: rgba(184, 136, 52, 0.48);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(184, 136, 52, 0.62);
            box-shadow: 0 0 0 .25rem rgba(215, 173, 91, 0.18);
            background: #fff;
        }

        .helper-text {
            color: var(--pf-muted);
            font-size: .83rem;
            margin-top: .25rem;
        }

        .pw-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
        }

        .btn-save {
            border: none;
            border-radius: 14px;
            background: linear-gradient(130deg, var(--pf-gold), var(--pf-gold-dark));
            color: #15253d;
            font-weight: 800;
            padding: .75rem 1.3rem;
            box-shadow: 0 14px 30px rgba(184, 136, 52, 0.32);
            transition: .2s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-save::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(95deg, transparent 30%, rgba(255, 255, 255, 0.28) 50%, transparent 70%);
            transform: translateX(-100%);
            animation: shine 3.5s infinite;
        }

        .btn-save:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 36px rgba(184, 136, 52, 0.38);
            color: #0f1c31;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            35% { transform: translateX(100%); }
            100% { transform: translateX(100%); }
        }

        @media (max-width: 991.98px) {
            .profile-side {
                position: static;
            }

            .profile-wrap {
                width: min(100% - 24px, 1500px);
                padding-top: 18px;
            }
        }

        @media (max-width: 575.98px) {
            .topbar {
                align-items: start;
                flex-direction: column;
            }

            .hero {
                border-radius: 24px;
                padding: 28px 20px;
            }

            .nav-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .btn-back {
                width: 100%;
                text-align: center;
            }

            .profile-card,
            .side-body {
                padding: 16px;
            }

            .form-shell {
                padding: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body>
    <?php
    $tenHienThi = trim((string)($nguoiDung['ho_ten'] ?? 'Khách hàng'));
    $kyTuDaiDien = 'U';
    if ($tenHienThi !== '') {
        if (function_exists('mb_substr')) {
            $kyTuDaiDien = strtoupper((string)mb_substr($tenHienThi, 0, 1, 'UTF-8'));
        } else {
            $kyTuDaiDien = strtoupper(substr($tenHienThi, 0, 1));
        }
    }
    ?>

    <div class="profile-wrap">
        <div class="topbar">
            <a class="topbar-title" href="index.php?act=khachHang/dashboard"><i class="bi bi-star-fill"></i> DuLichPro</a>
            <nav class="nav-actions" aria-label="Điều hướng khách hàng">
                <a class="nav-pill" href="index.php?act=khachHang/dashboard"><i class="bi bi-house"></i> Trang chủ</a>
                <a class="nav-pill" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a>
                <a class="nav-pill" href="index.php?act=khachHang/yeuCauTour"><i class="bi bi-suitcase2"></i> Tour đã đặt</a>
                <a class="nav-pill is-active" href="index.php?act=khachHang/capNhatThongTin"><i class="bi bi-person-gear"></i> Hồ sơ</a>
                <a class="nav-pill" href="index.php?act=khachHang/hoaDon"><i class="bi bi-receipt"></i> Hóa đơn</a>
            </nav>
        </div>

        <section class="hero">
            <div class="hero-content">
                <h1 class="hero-title"><i class="bi bi-person-gear"></i> Cập nhật thông tin cá nhân</h1>
                <p class="hero-subtitle">Quản lý hồ sơ để nhận đề xuất tour chính xác hơn và đồng bộ trải nghiệm đặt dịch vụ trong một màn hình thống nhất.</p>
            </div>
        </section>

        <div id="profileUpdateFeedback">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show status-alert">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show status-alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        </div>

        <div class="row layout-grid">
            <div class="col-lg-4">
                <aside class="profile-side">
                    <div class="side-body"><br><br>
                        <div class="avatar"><?php echo htmlspecialchars($kyTuDaiDien); ?></div>
                        <h3 class="side-name"><?php echo htmlspecialchars($tenHienThi); ?></h3>
                        <p class="side-note mb-0">Quản lý hồ sơ để nhận đề xuất tour chính xác hơn và đồng bộ thông tin đặt dịch vụ.</p>

                        <ul class="tip-list">
                            <li><i class="bi bi-shield-check"></i><span>Thông tin của bạn được lưu trữ bảo mật trong hệ thống.</span></li>
                            <li><i class="bi bi-clock-history"></i><span>Cập nhật số điện thoại và email để nhận thông báo nhanh hơn.</span></li>
                            <li><i class="bi bi-lock"></i><span>Chỉ đổi mật khẩu khi bạn thực sự muốn thay đổi đăng nhập.</span></li>
                        </ul>
                    </div>
                </aside>
            </div>

            <div class="col-lg-8">
                <div class="profile-card">
                    <h4 class="section-title mb-2">Hồ sơ tài khoản</h4>
                    <div class="section-subtitle">Cập nhật chính xác thông tin cá nhân để đặt tour nhanh và thuận tiện hơn.</div>

                    <form method="POST" action="index.php?act=khachHang/capNhatThongTin" id="profileUpdateForm">
                        <div class="form-shell">
                            <div class="form-block-title"><i class="bi bi-person-vcard"></i> Thông tin liên hệ</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="ho_ten" value="<?php echo htmlspecialchars($nguoiDung['ho_ten'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($nguoiDung['email'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="so_dien_thoai" value="<?php echo htmlspecialchars($nguoiDung['so_dien_thoai'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Giới tính</label>
                                    <select class="form-select" name="gioi_tinh">
                                        <option value="">Chọn giới tính</option>
                                        <option value="Nam" <?php echo (isset($khachHang['gioi_tinh']) && $khachHang['gioi_tinh'] === 'Nam') ? 'selected' : ''; ?>>Nam</option>
                                        <option value="Nu" <?php echo (isset($khachHang['gioi_tinh']) && $khachHang['gioi_tinh'] === 'Nu') ? 'selected' : ''; ?>>Nữ</option>
                                        <option value="Khac" <?php echo (isset($khachHang['gioi_tinh']) && $khachHang['gioi_tinh'] === 'Khac') ? 'selected' : ''; ?>>Khác</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày sinh</label>
                                    <input type="date" class="form-control" name="ngay_sinh" value="<?php echo !empty($khachHang['ngay_sinh']) ? date('Y-m-d', strtotime($khachHang['ngay_sinh'])) : ''; ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" name="dia_chi" rows="2"><?php echo htmlspecialchars($khachHang['dia_chi'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-shell">
                            <div class="form-block-title"><i class="bi bi-key"></i> Đổi mật khẩu</div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" class="form-control" name="mat_khau_moi" placeholder="Để trống nếu không đổi">
                                    <div class="helper-text">Chỉ điền nếu muốn đổi mật khẩu.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Xác nhận mật khẩu</label>
                                    <input type="password" class="form-control" name="xac_nhan_mat_khau" placeholder="Nhập lại mật khẩu mới">
                                </div>
                            </div>
                        </div>

                        <div class="pw-actions">
                            <button type="submit" class="btn-save" id="profileUpdateSubmitBtn">
                                <i class="bi bi-check-circle me-2"></i>Cập nhật thông tin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var profileUpdateForm = document.getElementById('profileUpdateForm');
        var profileUpdateSubmitBtn = document.getElementById('profileUpdateSubmitBtn');
        var profileUpdateFeedback = document.getElementById('profileUpdateFeedback');
        var profileNameNode = document.querySelector('.side-name');

        function renderFeedback(message, type) {
            if (!profileUpdateFeedback) return;
            var safeType = type === 'success' ? 'success' : 'danger';
            profileUpdateFeedback.innerHTML =
                '<div class="alert alert-' + safeType + ' alert-dismissible fade show status-alert" role="alert">' +
                message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                '</div>';
        }

        if (!profileUpdateForm) return;

        profileUpdateForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (profileUpdateSubmitBtn) {
                profileUpdateSubmitBtn.disabled = true;
                profileUpdateSubmitBtn.dataset.originalHtml = profileUpdateSubmitBtn.innerHTML;
                profileUpdateSubmitBtn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i>Đang cập nhật...';
            }

            try {
                var response = await fetch(profileUpdateForm.action, {
                    method: 'POST',
                    body: new FormData(profileUpdateForm),
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                var data = await response.json();
                if (data && data.success) {
                    renderFeedback(data.message || 'Cập nhật thông tin thành công', 'success');
                    if (profileNameNode && data.display_name) {
                        profileNameNode.textContent = data.display_name;
                    }
                } else {
                    renderFeedback((data && data.message) ? data.message : 'Không thể cập nhật. Vui lòng thử lại.', 'danger');
                }
            } catch (error) {
                renderFeedback('Lỗi kết nối. Vui lòng kiểm tra mạng và thử lại.', 'danger');
            } finally {
                if (profileUpdateSubmitBtn) {
                    profileUpdateSubmitBtn.disabled = false;
                    profileUpdateSubmitBtn.innerHTML = profileUpdateSubmitBtn.dataset.originalHtml || '<i class="bi bi-check-circle me-2"></i>Cập nhật thông tin';
                }
            }
        });
    });
    </script>
</body>
</html>


