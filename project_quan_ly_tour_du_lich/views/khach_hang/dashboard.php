<?php
/** @var array $danhGiaTot */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang khách hàng - Du lịch</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        /* NAVBAR */
        .navbar { 
            background: rgba(255,255,255,0.9); 
            backdrop-filter: blur(6px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .navbar-nav .nav-link {
            transition: 0.25s;
            border-radius: 30px;
        }
        .navbar-nav .nav-link:hover, .navbar-nav .nav-link.active {
            background: #e0f2fe;
            color: #0d6efd !important;
            box-shadow: 0 2px 8px rgba(13,110,253,0.15);
        }

        /* HERO BANNER */
        .hero-hotel {
            height: 600px;
            background: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?q=80&w=1600') center/cover no-repeat;
            position: relative;
            border-radius: 0 0 20px 20px;
        }
        .hero-overlay {
            position: absolute;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.55);
            border-radius: 0 0 20px 20px;
        }
        .hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            align-items: center;
        }

        /* BOOKING BOX */
        .booking-box {
            background: rgba(0,0,0,0.75);
            padding: 30px;
            border-radius: 14px;
            backdrop-filter: blur(5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.45);
        }
        .feature {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            margin-bottom: 10px;
        }

        /* TAB MENU */
        .multiTabMenu-bar { 
            font-size: 1.1rem; 
            background: #fff; 
            border-radius: 12px; 
        }
        .multiTabMenu-item { 
            transition: 0.2s; 
        }
        .multiTabMenu-item.active { 
            color: #0d6efd !important; 
            background: #f1f8ff; 
            border-bottom: 3px solid #0d6efd; 
        }
        .multiTabMenu-item:hover { 
            color: #0d6efd !important; 
            background: #e6f3ff; 
        }

        /* HEIGHT FIX FOR ALL TABS */
        #tab-content-currency,
        #tab-content-hotel,
        #tab-content-combo,
        #tab-content-visa {
            min-height: 150px; padding-bottom: 20px;
        }
    </style>
</head>

<body class="luxury">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg luxury-nav navbar-dark py-3">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2 fs-3" href="#">
            <i class="bi bi-star-fill brand-mark"></i>
            <span class="brand-name">DuLichPro</span>
        </a>

        <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link px-3 fw-semibold active" href="#home"><i class="bi bi-house-door"></i> Trang chủ</a></li>
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="index.php?act=khachHang/danhSachTour"><i class="bi bi-stars"></i> Tour nổi bật</a></li>
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#reviews"><i class="bi bi-chat-dots"></i> Đánh giá</a></li>
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#support"><i class="bi bi-headset"></i> Hỗ trợ</a></li>

                <li class="nav-item">
                    <a class="nav-link px-3 fw-bold luxury-cta" href="index.php?act=khachHang/guiYeuCauTour">
                        <i class="bi bi-plus-circle"></i> Tour của tôi
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link nav-icon-link nav-notify-link" href="index.php?act=khachHang/thongBao" title="Thông báo" aria-label="Thông báo">
                        <i class="bi bi-bell"></i>
                        <span id="customerNotificationBadge" class="customer-notification-badge" <?php if (($thongBaoChuaDoc ?? 0) <= 0): ?>style="display:none"<?php endif; ?>><?php echo (int)($thongBaoChuaDoc ?? 0); ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-icon-link" href="index.php?act=khachHang/capNhatThongTin" title="Hồ sơ tài khoản" aria-label="Hồ sơ tài khoản">
                        <i class="bi bi-person-circle"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link nav-icon-link nav-icon-danger" href="index.php?act=auth/logout" title="Đăng xuất" aria-label="Đăng xuất">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<?php if (isset($_SESSION['success']) || isset($_SESSION['error'])): ?>
<div class="position-fixed top-0 start-50 translate-middle-x p-3 js-flash-wrap" style="z-index:1200; margin-top:76px; width:min(680px, calc(100% - 20px));">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success shadow-sm mb-2"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger shadow-sm mb-0"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- HERO BANNER -->
<div class="hero-hotel" id="home">
    <div class="hero-overlay"></div>

    <div class="container hero-content">
        <div class="row align-items-center">

            <!-- LEFT CONTENT -->
            <div class="col-lg-6 text-white hero-main-copy">
                <h5 class="text-warning fw-bold" style="letter-spacing:.18em; text-transform:uppercase;">Hành trình đẳng cấp &mdash; Trải nghiệm đích thực</h5>

                <h1 class="display-4 fw-bold">
                    Khám phá thế giới <br> cùng DuLichPro
                </h1>

                <p class="mt-3" style="font-size:1.08rem; line-height:1.75; color:rgba(255,255,255,.86);">
                    Hàng trăm tour trong nước và quốc tế chất lượng cao. Đội ngũ hướng dẫn viên chuyên nghiệp,
                    dịch vụ tận tâm &mdash; đồng hành cùng bạn trên mỗi hành trình.
                </p>

                <div class="row mt-4">
                    <div class="col-6 col-md-3"><div class="feature"><i class="bi bi-patch-check-fill me-1" style="color:#f6dfab;"></i> Dịch vụ 5 sao</div></div>
                    <div class="col-6 col-md-3"><div class="feature"><i class="bi bi-geo-alt-fill me-1" style="color:#f6dfab;"></i> Điểm đến đa dạng</div></div>
                    <div class="col-6 col-md-3"><div class="feature"><i class="bi bi-gift-fill me-1" style="color:#f6dfab;"></i> Ưu đãi hấp dẫn</div></div>
                    <div class="col-6 col-md-3"><div class="feature"><i class="bi bi-people-fill me-1" style="color:#f6dfab;"></i> Phù hợp gia đình</div></div>
                </div>
            </div>

            <!-- BOOKING FORM -->
            <div class="col-lg-5 offset-lg-1">
                <div class="booking-box text-white">

                    <h4 class="text-warning fw-bold mb-1">Gửi yêu cầu tour</h4>
                    <p class="small" style="color:rgba(255,255,255,.68); margin-bottom:.9rem;">Điền thông tin &mdash; chúng tôi tư vấn hành trình phù hợp nhất cho bạn.</p>

                    <form method="POST" action="index.php?act=khachHang/guiYeuCauTour" id="quickTourRequestForm">
                        <input type="hidden" name="_csrf_global" value="<?php echo htmlspecialchars(csrfToken('global_form'), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="redirect_to" value="index.php?act=khachHang/dashboard">
                        <div class="mb-3">
                            <label class="form-label text-light">Địa điểm mong muốn</label>
                            <input type="text" name="dia_diem" class="form-control" placeholder="VD: Đà Nẵng, Hội An" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label text-light">Ngày đi</label>
                                <input type="date" name="arrival_date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label text-light">Ngày về</label>
                                <input type="date" name="departure_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Số lượng người</label>
                            <input type="number" name="so_nguoi" min="1" class="form-control" placeholder="VD: 2" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Yêu cầu đặc biệt</label>
                            <textarea name="yeu_cau_dac_biet" class="form-control" rows="2" placeholder="Nếu có"></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 fw-bold">GỬI YÊU CẦU</button>
                    </form>
                    <div id="quickTourRequestFeedback" class="small mt-2" style="display:none;"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="container d-md-none">
    <div class="mobile-home-shortcuts" aria-label="Lối tắt nhanh">
        <a href="index.php?act=khachHang/danhSachTour" class="mobile-shortcut-card">
            <span class="mobile-shortcut-icon"><i class="bi bi-stars"></i></span>
            <span>
                <strong>Xem tour</strong>
                <small>Danh sách tour nổi bật</small>
            </span>
        </a>
        <a href="#reviews" class="mobile-shortcut-card">
            <span class="mobile-shortcut-icon"><i class="bi bi-chat-quote"></i></span>
            <span>
                <strong>Đánh giá</strong>
                <small>Ý kiến từ khách hàng</small>
            </span>
        </a>
        <a href="#places" class="mobile-shortcut-card">
            <span class="mobile-shortcut-icon"><i class="bi bi-geo-alt"></i></span>
            <span>
                <strong>Địa danh</strong>
                <small>Gợi ý điểm đến nhanh</small>
            </span>
        </a>
        <a href="#support" class="mobile-shortcut-card">
            <span class="mobile-shortcut-icon"><i class="bi bi-headset"></i></span>
            <span>
                <strong>Hỗ trợ</strong>
                <small>Liên hệ ngay khi cần</small>
            </span>
        </a>
    </div>
</div>

<!-- CURRENCY CONVERTER + TABS -->
<style>
/* CARD */
.enhanced-card {
    background:
        radial-gradient(circle at top left, rgba(220,176,93,0.16), transparent 26%),
        linear-gradient(180deg, rgba(255,255,255,0.98), rgba(247,244,238,0.96));
    border-radius: 34px !important;
    border: 1px solid rgba(22,34,58,0.08) !important;
    box-shadow: 0 26px 70px rgba(10,18,38,0.16);
    overflow: hidden;
}

/* TAB MENU */
.multiTabMenu-bar {
    background: linear-gradient(135deg, rgba(11,18,32,.96), rgba(21,35,59,.92));
    border: 1px solid rgba(214,178,109,.18);
    border-radius: 999px !important;
    padding: 12px !important;
    box-shadow: 0 16px 36px rgba(2,6,23,.18);
}

.multiTabMenu-item {
    min-width: 168px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.25s ease;
    border: 1px solid rgba(214,178,109,.16);
    color: rgba(255,255,255,.86);
    border-radius: 999px !important;
    background: rgba(255,255,255,.04);
    font-size: .92rem;
    font-weight: 800;
}

.multiTabMenu-item:hover {
    background: rgba(214,178,109,0.14);
    color: #fff;
    border-color: rgba(214,178,109,0.34);
    transform: translateY(-1px);
}

.multiTabMenu-item.active {
    background: linear-gradient(135deg, #d6b26d, #e2bf78) !important;
    color: #132033 !important;
    box-shadow: 0 12px 26px rgba(185,137,61,.24);
    border-color: rgba(214,178,109,0.62);
}

/* INPUT + SELECT */
.form-select, .form-control {
    border-radius: 18px;
    padding: 16px 18px;
    min-height: 62px;
    border: 1px solid rgba(24,40,70,0.12);
    background: rgba(255,255,255,0.92);
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.8);
    transition: all 0.2s ease;
}

.form-select:focus, .form-control:focus {
    border-color: rgba(214,178,109,0.72);
    box-shadow: 0 0 0 0.2rem rgba(214,178,109,0.18);
}

/* RESULT BOX */
.result-box {
    background:
        radial-gradient(circle at top right, rgba(214,178,109,0.24), transparent 30%),
        linear-gradient(135deg, #13213a, #1f355c);
    color: #f8f4eb;
    padding: 24px 26px;
    border-radius: 24px;
    font-size: 1.65rem;
    font-weight: 800;
    box-shadow: 0 20px 44px rgba(19,33,58,0.22);
    min-height: 116px;
    display: flex;
    align-items: center;
}

/* SWAP BUTTON */
.swap-btn {
    width: 62px;
    height: 62px;
    background: linear-gradient(135deg, #15233b, #20365f);
    border: none;
    color: #f6dfab;
    font-size: 1.35rem;
    border-radius: 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    margin-top: 30px;
    box-shadow: 0 16px 32px rgba(21,35,59,0.22);
}

.swap-btn:hover {
    transform: translateY(-2px) rotate(180deg);
    box-shadow: 0 20px 34px rgba(21,35,59,0.28);
}

#tools {
    margin-top: -64px !important;
    position: relative;
    z-index: 4;
}

#tab-content-currency {
    background: linear-gradient(145deg, #ffffff, #fbf7ef);
    border: 1px solid rgba(214,178,109,0.22);
    border-radius: 28px;
    padding: 28px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.84);
}

#tab-content-currency h3 {
    color: #13213a !important;
    font-family: "Playfair Display", Georgia, serif;
    font-size: clamp(2rem, 3vw, 3rem);
    line-height: 1.08;
    margin-bottom: 10px;
}

#tab-content-currency h3 i {
    color: #d6b26d;
}

#tab-content-currency::after {
    content: "Tỉ giá tham khảo cho hành trình cao cấp";
    display: block;
    margin-top: -2px;
    margin-bottom: 18px;
    color: #64748b;
    font-size: 1.02rem;
}

#tab-content-currency .form-label {
    color: #43536d;
    font-weight: 700;
    margin-bottom: 10px;
}

#tab-content-hotel,
#tab-content-combo,
#tab-content-visa {
    background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(246,241,232,0.96));
    border: 1px solid rgba(214,178,109,0.18);
    border-radius: 28px;
    padding: 28px;
    box-shadow: 0 16px 34px rgba(15,23,42,0.08);
}

#tab-content-hotel h3,
#tab-content-combo h3,
#tab-content-visa h3 {
    color: #16243c !important;
    font-family: "Playfair Display", Georgia, serif;
}

#tab-content-hotel .alert,
#tab-content-combo .alert,
#tab-content-visa .alert {
    border-radius: 18px;
    border: 1px solid rgba(214,178,109,0.18);
    background: rgba(255,255,255,0.82);
    color: #6b7280;
}

@media (max-width: 991.98px) {
    #tools {
        margin-top: 28px !important;
    }

    .multiTabMenu-item {
        min-width: 0;
        width: 100%;
    }

    .multiTabMenu-bar {
        border-radius: 28px !important;
    }

    #tab-content-currency {
        padding: 22px;
    }
}

/* ===== MOBILE: thu nhỏ bảng quy đổi tỉ giá ===== */
@media (max-width: 767.98px) {
    #tab-content-currency {
        padding: 14px 12px !important;
    }
    #tab-content-currency h3 {
        font-size: 1.15rem !important;
        margin-bottom: 2px !important;
    }
    #tab-content-currency::after {
        font-size: 0.78rem !important;
        margin-bottom: 10px !important;
    }
    #tab-content-currency .form-label {
        font-size: 0.8rem !important;
        margin-bottom: 4px !important;
    }
    #tab-content-currency .form-select,
    #tab-content-currency .form-control {
        min-height: 44px !important;
        padding: 8px 12px !important;
        font-size: 0.88rem !important;
        border-radius: 12px !important;
    }
    .swap-btn {
        width: 44px !important;
        height: 44px !important;
        font-size: 1rem !important;
        margin-top: 22px !important;
        border-radius: 14px !important;
    }
    .result-box {
        font-size: 0.98rem !important;
        min-height: 58px !important;
        padding: 10px 14px !important;
        border-radius: 14px !important;
    }
    #tab-content-currency .mt-4 {
        margin-top: 12px !important;
    }
}
</style>

<div class="container mt-5 mb-5 luxury-float" id="tools">
    <div class="card enhanced-card p-4">

        <!-- TAB MENU -->
        <nav class="multiTabMenu-bar d-flex justify-content-center gap-2 py-2 mb-4 border-bottom">

            <a class="multiTabMenu-item fw-semibold px-3 py-2 rounded-3 active" 
                id="tab-currency" href="#" onclick="showTab('currency'); return false;">
                <i class="bi bi-currency-exchange me-1"></i> Quy đổi tiền
            </a>

            <a class="multiTabMenu-item fw-semibold px-3 py-2 rounded-3" 
                id="tab-hotel" href="#" onclick="showTab('hotel'); return false;">
                <i class="bi bi-building me-1"></i> Đặt phòng
            </a>

            <a class="multiTabMenu-item fw-semibold px-3 py-2 rounded-3" 
                id="tab-combo" href="#" onclick="showTab('combo'); return false;">
                <i class="bi bi-box-seam me-1"></i> Combo
            </a>

            <a class="multiTabMenu-item fw-semibold px-3 py-2 rounded-3" 
                id="tab-visa" href="#" onclick="showTab('visa'); return false;">
                <i class="bi bi-passport me-1"></i> Visa
            </a>
        </nav>


        <!-- TAB: CURRENCY -->
        <section id="tab-content-currency">
            <h3 class="fw-bold text-primary">
                <i class="bi bi-currency-exchange me-2"></i> Quy đổi tỉ giá ngoại tệ
            </h3>

            <form class="row g-3 mt-3">
                <div class="col-md-4">
                    <label class="form-label">Từ loại tiền</label>
                    <select class="form-select" id="from-currency">
                        <option value="USD">USD - Đô la Mỹ</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="JPY">JPY - Yên Nhật</option>
                        <option value="KRW">KRW - Won Hàn</option>
                        <option value="VND">VND - Việt Nam Đồng</option>
                    </select>
                </div>

                <!-- SWAP BUTTON -->
                <div class="col-md-1 text-center">
                    <button type="button" class="swap-btn" onclick="swapCurrency()">
                        <i class="bi bi-arrow-left-right"></i>
                    </button>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Sang loại tiền</label>
                    <select class="form-select" id="to-currency">
                        <option value="VND">VND - Việt Nam Đồng</option>
                        <option value="USD">USD - Đô la Mỹ</option>
                        <option value="EUR">EUR - Euro</option>
                        <option value="JPY">JPY - Yên Nhật</option>
                        <option value="KRW">KRW - Won Hàn</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Số tiền</label>
                    <input type="number" class="form-control" id="amount" value="1">
                </div>
            </form>

            <div class="mt-4">
                <div class="result-box" id="converted-result">Kết quả sẽ hiển thị ở đây.</div>
                <small class="text-muted">Tỉ giá mang tính tham khảo.</small>
            </div>
        </section>


        <!-- TAB: HOTEL -->
        <section id="tab-content-hotel" style="display:none;">
            <h3 class="fw-bold text-primary"><i class="bi bi-building me-2"></i> Đặt phòng khách sạn</h3>
            <div class="alert alert-warning mt-2">Chức năng sẽ cập nhật sau.</div>
        </section>

        <!-- TAB: COMBO -->
        <section id="tab-content-combo" style="display:none;">
            <h3 class="fw-bold text-primary"><i class="bi bi-box-seam me-2"></i> Combo du lịch</h3>
            <div class="alert alert-warning mt-2">Chức năng sẽ cập nhật sau.</div>
        </section>

        <!-- TAB: VISA -->
        <section id="tab-content-visa" style="display:none;">
            <h3 class="fw-bold text-primary"><i class="bi bi-passport me-2"></i> Visa</h3>
            <div class="alert alert-warning mt-2">Chức năng sẽ cập nhật sau.</div>
        </section>

    </div>
</div>

<script>
/* TỈ GIÁ NGOẠI TỆ */
const rates = {
    USD: { VND: 24500, EUR: 0.91, JPY: 161.5, KRW: 1300 },
    EUR: { VND: 26800, USD: 1.1, JPY: 177.5, KRW: 1420 },
    JPY: { VND: 170, USD: 0.0062, EUR: 0.0056, KRW: 8 },
    KRW: { VND: 19, USD: 0.00077, EUR: 0.00070, JPY: 0.13 },
    VND: { USD: 1/24500, EUR: 1/26800, JPY: 1/170, KRW: 1/19 }
};

function convertCurrency(from, to, amount) {
    if (from === to) return amount;
    if (rates[from] && rates[from][to]) return amount * rates[from][to];
    if (rates[from]?.VND && rates.VND[to]) return amount * rates[from].VND * rates.VND[to];
    return 0;
}

function updateResult() {
    const from = document.getElementById('from-currency').value;
    const to = document.getElementById('to-currency').value;
    const amount = parseFloat(document.getElementById('amount').value) || 0;
    const result = convertCurrency(from, to, amount);

    document.getElementById('converted-result').innerHTML =
        `<span class="d-block text-uppercase mb-2" style="font-size:.76rem; letter-spacing:.12em; color:rgba(247,229,182,.76);">Live Preview</span>
         <span>${amount} ${from} = <b>${result.toLocaleString(undefined, {maximumFractionDigits: 2})} ${to}</b></span>`;
}

function swapCurrency() {
    let from = document.getElementById("from-currency");
    let to = document.getElementById("to-currency");
    let temp = from.value;
    from.value = to.value;
    to.value = temp;
    updateResult();
}

document.getElementById('from-currency').addEventListener('change', updateResult);
document.getElementById('to-currency').addEventListener('change', updateResult);
document.getElementById('amount').addEventListener('input', updateResult);
updateResult();

/* TAB MENU */
function showTab(tab) {
    document.querySelectorAll("[id^='tab-content-']").forEach(el => el.style.display = "none");
    document.querySelectorAll(".multiTabMenu-item").forEach(el => el.classList.remove("active"));

    document.getElementById("tab-content-" + tab).style.display = "block";
    document.getElementById("tab-" + tab).classList.add("active");
}
</script>

<main class="luxury-content">
    <div class="container mt-5">
            <!-- Section: Trải nghiệm cho mọi người -->
            <div class="mt-5" id="experiences">
                <div class="d-flex align-items-flex-end justify-content-between mb-2 flex-wrap gap-2">
                    <div>
                        <h2 class="fw-bold mb-1">Trải nghiệm cho mọi người</h2>
                        <p class="text-muted mb-0" style="font-size:.95rem;">Những hành trình được yêu thích nhất &mdash; dành cho mọi phong cách du lịch</p>
                    </div>
                </div>

                <div class="mobile-inline-hint mobile-exp-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt ngang để xem nhanh các gợi ý</div>
                <div class="exp-grid mt-4">
                    <!-- Card 1: lớn bên trái -->
                    <a href="index.php?act=khachHang/danhSachTour" class="exp-card exp-card--large">
                        <img src="https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=900&q=80" alt="Singapore & Thái Lan" loading="lazy">
                        <div class="exp-card-overlay">
                            <span class="exp-badge"><i class="bi bi-fire me-1"></i>HOT</span>
                            <div class="exp-card-body">
                                <div class="exp-tag">SINGAPORE &amp; THÁI LAN</div>
                                <h3 class="exp-title">Chốt Gấp Kèo<br>Sing &ndash; Thái</h3>
                                <p class="exp-desc">Deal du lịch hot nhất, giá ưu đãi có hạn</p>
                                <span class="exp-cta">Khám phá <i class="bi bi-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>

                    <!-- Card 2: nhỏ trên phải -->
                    <a href="index.php?act=khachHang/danhSachTour" class="exp-card exp-card--small exp-card--amber">
                        <img src="https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=700&q=80" alt="Càng Mua Càng Hời" loading="lazy">
                        <div class="exp-card-overlay">
                            <span class="exp-badge exp-badge--gold"><i class="bi bi-tag-fill me-1"></i>ƯU ĐÃI</span>
                            <div class="exp-card-body">
                                <div class="exp-tag">KHUYẾN MÃI</div>
                                <h3 class="exp-title">Càng Mua<br>Càng Hời</h3>
                                <p class="exp-desc">Nhóm từ 2 người &mdash; giảm ngay 15%</p>
                                <span class="exp-cta">Xem ưu đãi <i class="bi bi-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>

                    <!-- Card 3: nhỏ dưới phải -->
                    <a href="index.php?act=khachHang/danhSachTour" class="exp-card exp-card--small exp-card--teal">
                        <img src="https://images.unsplash.com/photo-1499856871958-5b9627545d1a?w=700&q=80" alt="Châu Âu - Hoa Kỳ" loading="lazy">
                        <div class="exp-card-overlay">
                            <span class="exp-badge exp-badge--teal"><i class="bi bi-globe2 me-1"></i>QUỐC TẾ</span>
                            <div class="exp-card-body">
                                <div class="exp-tag">CHÂU ÂU &amp; HOA KỲ</div>
                                <h3 class="exp-title">Zone Tây<br>Phương</h3>
                                <p class="exp-desc">Paris, Rome, New York &mdash; trải nghiệm đỉnh cao</p>
                                <span class="exp-cta">Khám phá ngay <i class="bi bi-arrow-right ms-1"></i></span>
                            </div>
                        </div>
                    </a>
                </div>

                <style>
                .exp-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    grid-template-rows: 220px 220px;
                    gap: 16px;
                }
                .exp-card--large {
                    grid-row: 1 / 3;
                }
                .exp-card {
                    display: block;
                    position: relative;
                    border-radius: 24px;
                    overflow: hidden;
                    text-decoration: none;
                    background: #0f1f1b;
                    box-shadow: 0 12px 40px rgba(2,6,23,.13);
                    transition: transform .28s ease, box-shadow .28s ease;
                }
                .exp-card:hover {
                    transform: translateY(-6px);
                    box-shadow: 0 24px 64px rgba(2,6,23,.22);
                }
                .exp-card img {
                    position: absolute;
                    inset: 0;
                    width: 100%; height: 100%;
                    object-fit: cover;
                    display: block;
                    transition: transform .5s ease, filter .35s ease;
                    filter: brightness(.78) saturate(1.05);
                }
                .exp-card:hover img {
                    transform: scale(1.055);
                    filter: brightness(.68) saturate(1.12);
                }
                .exp-card-overlay {
                    position: absolute;
                    inset: 0;
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-end;
                    padding: 26px;
                    background: linear-gradient(180deg, rgba(0,0,0,0) 30%, rgba(11,18,32,.88) 100%);
                    pointer-events: none;
                }
                .exp-card--small .exp-card-overlay {
                    padding: 20px;
                }
                .exp-badge {
                    position: absolute;
                    top: 18px; left: 18px;
                    background: rgba(239,68,68,.88);
                    color: #fff;
                    font-size: .72rem;
                    font-weight: 800;
                    letter-spacing: .07em;
                    padding: 5px 12px;
                    border-radius: 999px;
                    text-transform: uppercase;
                    backdrop-filter: blur(4px);
                    pointer-events: none;
                }
                .exp-badge--gold { background: rgba(214,178,109,.92); color: #132033; }
                .exp-badge--teal { background: rgba(4,122,120,.92); color: #fff; }
                .exp-tag {
                    font-size: .68rem;
                    font-weight: 800;
                    letter-spacing: .14em;
                    color: rgba(214,178,109,.90);
                    text-transform: uppercase;
                    margin-bottom: 6px;
                }
                .exp-title {
                    font-family: "Playfair Display", Georgia, serif;
                    font-size: 1.55rem;
                    font-weight: 700;
                    color: #fff;
                    line-height: 1.18;
                    margin: 0 0 8px;
                }
                .exp-card--small .exp-title { font-size: 1.2rem; }
                .exp-desc {
                    font-size: .84rem;
                    color: rgba(255,255,255,.78);
                    margin: 0 0 14px;
                    line-height: 1.5;
                }
                .exp-card--small .exp-desc { display: none; }
                .exp-cta {
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                    background: rgba(255,255,255,.15);
                    border: 1px solid rgba(255,255,255,.3);
                    color: #fff;
                    font-size: .8rem;
                    font-weight: 800;
                    padding: 8px 16px;
                    border-radius: 999px;
                    backdrop-filter: blur(6px);
                    pointer-events: auto;
                    transition: background .2s, border-color .2s;
                    text-decoration: none;
                }
                .exp-card:hover .exp-cta {
                    background: rgba(214,178,109,.88);
                    border-color: rgba(214,178,109,.9);
                    color: #132033;
                }

                @media (max-width: 767px) {
                    .exp-grid {
                        grid-template-columns: 1fr;
                        grid-template-rows: 280px 200px 200px;
                    }
                    .exp-card--large { grid-row: 1; }
                    .exp-card--small .exp-desc { display: block; }
                }
                </style>
            </div>
                <!-- Section: Bạn muốn đi đâu chơi? -->
                <div class="mt-5 dest-section">
                    <div class="d-flex align-items-flex-end justify-content-between mb-4 flex-wrap gap-2">
                        <div>
                            <h2 class="fw-bold mb-1">Bạn muốn đi đâu chơi?</h2>
                            <p class="text-muted mb-0" style="font-size:.95rem;">Chọn điểm đến &mdash; chúng tôi lo phần còn lại</p>
                        </div>
                        <a href="index.php?act=khachHang/danhSachTour" class="dest-see-all-btn">
                            Xem tất cả <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="mobile-inline-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt để xem thêm điểm đến</div>
                    <div class="dest-scroll-track">
                        <?php
                        $destinations = [
                            ['name'=>'Thượng Hải',     'count'=>'225 tour',  'tag'=>'Quốc tế',   'img'=>'https://images.unsplash.com/photo-1548919973-5cef591cdbc9?w=600&q=80'],
                            ['name'=>'Bangkok',         'count'=>'581 tour',  'tag'=>'Quốc tế',   'img'=>'https://images.unsplash.com/photo-1563492065599-3520f775eeed?w=600&q=80'],
                            ['name'=>'Đà Nẵng',        'count'=>'146 tour',  'tag'=>'Trong nước','img'=>'https://images.unsplash.com/photo-1559592413-7cec4d0cae2b?w=600&q=80'],
                            ['name'=>'Hà Nội',         'count'=>'154 tour',  'tag'=>'Trong nước','img'=>'https://images.unsplash.com/photo-1555921015-5532091f6026?w=600&q=80'],
                            ['name'=>'TP. Hồ Chí Minh','count'=>'240 tour', 'tag'=>'Trong nước','img'=>'https://images.unsplash.com/photo-1583417319070-4a69db38a482?w=600&q=80'],
                            ['name'=>'Đài Bắc',        'count'=>'394 tour',  'tag'=>'Quốc tế',   'img'=>'https://images.unsplash.com/photo-1470004914212-05527e49370b?w=600&q=80'],
                            ['name'=>'Phú Quốc',       'count'=>'88 tour',   'tag'=>'Trong nước','img'=>'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=600&q=80'],
                            ['name'=>'Hội An',         'count'=>'112 tour',  'tag'=>'Trong nước','img'=>'https://images.unsplash.com/photo-1528127269322-539801943592?w=600&q=80'],
                        ];
                        foreach ($destinations as $d): ?>
                        <a href="index.php?act=khachHang/danhSachTour" class="dest-card" aria-label="<?php echo htmlspecialchars($d['name']); ?>">
                            <div class="dest-card-img">
                                <img src="<?php echo htmlspecialchars($d['img']); ?>" alt="<?php echo htmlspecialchars($d['name']); ?>" loading="lazy">
                                <span class="dest-tag"><?php echo htmlspecialchars($d['tag']); ?></span>
                            </div>
                            <div class="dest-card-body">
                                <div class="dest-name"><?php echo htmlspecialchars($d['name']); ?></div>
                                <div class="dest-count"><i class="bi bi-compass me-1"></i><?php echo htmlspecialchars($d['count']); ?></div>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <style>
                .dest-section { position: relative; }

                .dest-see-all-btn {
                    display: inline-flex;
                    align-items: center;
                    gap: 4px;
                    padding: 9px 20px;
                    border-radius: 999px;
                    border: 1.5px solid rgba(214,178,109,.55);
                    color: #9a6e1c;
                    font-size: .88rem;
                    font-weight: 700;
                    text-decoration: none;
                    background: rgba(214,178,109,.08);
                    transition: background .2s, color .2s, border-color .2s;
                    white-space: nowrap;
                }
                .dest-see-all-btn:hover {
                    background: #d6b26d;
                    color: #132033;
                    border-color: #d6b26d;
                }

                .dest-scroll-track {
                    display: flex;
                    gap: 16px;
                    overflow-x: auto;
                    overflow-y: hidden;
                    scroll-snap-type: x mandatory;
                    -webkit-overflow-scrolling: touch;
                    overscroll-behavior-x: contain;
                    padding-bottom: 12px;
                }
                .dest-scroll-track::-webkit-scrollbar { height: 6px; }
                .dest-scroll-track::-webkit-scrollbar-track { background: rgba(15,23,42,.07); border-radius: 999px; }
                .dest-scroll-track::-webkit-scrollbar-thumb { background: rgba(214,178,109,.55); border-radius: 999px; }

                .dest-card {
                    flex: 0 0 200px;
                    scroll-snap-align: start;
                    border-radius: 20px;
                    overflow: hidden;
                    text-decoration: none;
                    display: flex;
                    flex-direction: column;
                    background: #fff;
                    border: 1px solid rgba(15,23,42,.07);
                    box-shadow: 0 8px 28px rgba(2,6,23,.09);
                    transition: transform .25s ease, box-shadow .25s ease;
                }
                .dest-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 20px 52px rgba(2,6,23,.17);
                }
                .dest-card-img {
                    position: relative;
                    height: 220px;
                    overflow: hidden;
                    background: #e8edf3;
                }
                .dest-card-img img {
                    width: 100%; height: 100%;
                    object-fit: cover;
                    display: block;
                    transition: transform .4s ease;
                }
                .dest-card:hover .dest-card-img img {
                    transform: scale(1.06);
                }
                .dest-tag {
                    position: absolute;
                    top: 12px; left: 12px;
                    background: rgba(11,18,32,.72);
                    color: #f7e5b6;
                    font-size: .72rem;
                    font-weight: 800;
                    letter-spacing: .06em;
                    padding: 5px 11px;
                    border-radius: 999px;
                    backdrop-filter: blur(4px);
                    text-transform: uppercase;
                }
                .dest-card-body {
                    padding: 14px 16px;
                    background: #fff;
                }
                .dest-name {
                    font-family: "Playfair Display", Georgia, serif;
                    font-size: 1.05rem;
                    font-weight: 700;
                    color: #0f1f1b;
                    margin-bottom: 5px;
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }
                .dest-count {
                    font-size: .8rem;
                    font-weight: 700;
                    color: #047a78;
                }

                @media (max-width: 576px) {
                    .dest-card { flex: 0 0 160px; }
                    .dest-card-img { height: 170px; }
                    .dest-name { font-size: .92rem; }
                }
                </style>

        <div class="home-section-head mb-4" id="tours">
            <div>
                <h2 class="mb-1 fw-bold">Tour trong nước</h2>
                <p class="text-muted mb-0 home-section-copy">Khám phá vẻ đẹp quê hương qua từng hành trình</p>
            </div>
            <a class="home-section-link" href="index.php?act=khachHang/danhSachTour&loai_tour=TrongNuoc">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <?php
        $renderHomeTourCard = static function ($tour, $fallbackImage, $typeLabel) {
            $tourId = (int)($tour['tour_id'] ?? $tour['id'] ?? 0);
            $image = trim((string)($tour['hinh_anh'] ?? ''));
            if ($image === '') {
                $image = $fallbackImage;
            }
            $name = (string)($tour['ten_tour'] ?? 'Tour đang cập nhật');
            $description = (string)($tour['mo_ta_ngan'] ?? $tour['mo_ta'] ?? 'Thông tin tour đang được cập nhật.');
            $description = mb_strlen($description) > 155 ? mb_substr($description, 0, 155) . '...' : $description;
            $price = (float)($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0);
            $startDate = !empty($tour['ngay_khoi_hanh_gan_nhat']) ? date('d/m/Y', strtotime((string)$tour['ngay_khoi_hanh_gan_nhat'])) : 'Linh hoạt';
            $meetingPoint = trim((string)($tour['diem_tap_trung'] ?? ''));
            $seats = $tour['so_cho'] ?? null;
            $rating = $tour['rating'] ?? ['diem_tb' => 0, 'so_danh_gia' => 0];
            $ratingCount = (int)($rating['so_danh_gia'] ?? 0);
            $ratingText = $ratingCount > 0
                ? number_format((float)($rating['diem_tb'] ?? 0), 1) . '/5 (' . $ratingCount . ' đánh giá)'
                : 'Chưa có đánh giá';
            ?>
            <article class="home-tour-card">
                <div class="home-tour-media">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($name); ?>" loading="lazy">
                    <span class="home-tour-badge"><?php echo htmlspecialchars($typeLabel); ?></span>
                </div>
                <div class="home-tour-body">
                    <h3><?php echo htmlspecialchars($name); ?></h3>
                    <p class="home-tour-desc"><?php echo htmlspecialchars($description); ?></p>
                    <div class="home-tour-meta">
                        <div><i class="bi bi-calendar-event"></i><span>Khởi hành: <b><?php echo htmlspecialchars($startDate); ?></b></span></div>
                        <div><i class="bi bi-geo-alt"></i><span>Điểm tập trung: <b><?php echo htmlspecialchars($meetingPoint !== '' ? $meetingPoint : 'Đang cập nhật'); ?></b></span></div>
                        <div><i class="bi bi-people"></i><span>Số chỗ: <b><?php echo $seats !== null ? (int)$seats . ' khách' : 'Đang cập nhật'; ?></b></span></div>
                        <div><i class="bi bi-star-fill"></i><span><b><?php echo htmlspecialchars($ratingText); ?></b></span></div>
                    </div>
                    <div class="home-tour-footer">
                        <div>
                            <div class="home-tour-price-label">Giá từ</div>
                            <div class="home-tour-price"><?php echo number_format($price); ?>&#273;</div>
                        </div>
                        <div class="home-tour-actions">
                            <a class="home-tour-detail" href="index.php?act=khachHang/chiTietTour&id=<?php echo $tourId; ?>"><i class="bi bi-info-circle"></i> Chi tiết</a>
                            <a class="home-tour-book" href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tourId; ?>"><i class="bi bi-cart-check"></i> Đặt ngay</a>
                        </div>
                    </div>
                </div>
            </article>
            <?php
        };
        ?>
        <?php if (!empty($tourTrongNuoc)): ?>
        <div class="mobile-inline-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt để duyệt tour trong nước</div>
        <div class="d-flex flex-row flex-nowrap overflow-x-auto overflow-y-hidden pb-2 luxury-scroll">
            <?php foreach ($tourTrongNuoc as $tour): ?>
            <?php $renderHomeTourCard($tour, 'https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=900&q=80', 'Trong nước'); continue; ?>
            <div class="tour-card card shadow-sm" style="min-width:320px; max-width:340px;">
                <img src="<?php echo htmlspecialchars($tour['hinh_anh'] ?? 'https://images.unsplash.com/photo-1465156799763-2c087c332922?auto=format&fit=crop&w=600&q=80'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['ten_tour']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($tour['ten_tour']); ?></h5>
                    <?php $gia = isset($tour['gia_tour']) && $tour['gia_tour'] !== null ? $tour['gia_tour'] : (isset($tour['gia_co_ban']) && $tour['gia_co_ban'] !== null ? $tour['gia_co_ban'] : 0); ?>
                    <?php 
                        $moTa = $tour['mo_ta_ngan'] ?? $tour['mo_ta'] ?? '';
                        $moTaRutGon = mb_strlen($moTa) > 80 ? mb_substr($moTa, 0, 80) . '...' : $moTa;
                    ?>
                    <p class="card-text">
                        <i class="bi bi-geo-alt-fill text-primary me-1"></i> <?php echo htmlspecialchars($moTaRutGon); ?><br>
                        <i class="bi bi-cash-coin text-success me-1"></i> Giá chỉ từ <b><?php echo number_format((float)$gia); ?>đ</b>
                    </p>
                    <?php $urlDatTour = "index.php?act=khachHang/datTour&id=" . ($tour['tour_id'] ?? ''); ?>
                    <?php $urlThanhToan = "index.php?act=khachHang/thanhToanTour&id=" . ($tour['tour_id'] ?? ''); ?>
                    <a href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['tour_id'] ?? ''; ?>" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i> Đặt ngay & Thanh toán</a>
                    <!-- Section: Chi tiết tour -->
                    <div class="mt-3">
                        <a href="index.php?act=khachHang/chiTietTour&id=<?php echo $tour['tour_id']; ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-info-circle me-1"></i> Xem chi tiết tour</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Hiện chưa có tour trong nước nào.</div>
        <?php endif; ?>
        <div class="home-section-head mt-5 mb-4">
            <div>
                <h2 class="mb-1 fw-bold">Tour quốc tế</h2>
                <p class="text-muted mb-0 home-section-copy">Bay cao, đi xa &mdash; trải nghiệm tinh hoa thế giới</p>
            </div>
            <a class="home-section-link" href="index.php?act=khachHang/danhSachTour&loai_tour=QuocTe">Xem tất cả <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <?php if (!empty($tourQuocTe)): ?>
        <div class="mobile-inline-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt để duyệt tour quốc tế</div>
        <div class="d-flex flex-row flex-nowrap overflow-x-auto overflow-y-hidden pb-2 luxury-scroll">
            <?php foreach ($tourQuocTe as $tour): ?>
            <?php $renderHomeTourCard($tour, 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80', 'Quốc tế'); continue; ?>
            <div class="tour-card card shadow-sm" style="min-width:320px; max-width:340px;">
                <img src="<?php echo htmlspecialchars($tour['hinh_anh'] ?? 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=600&q=80'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['ten_tour']); ?>">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($tour['ten_tour']); ?></h5>
                    <?php 
                        $moTaQT = $tour['mo_ta_ngan'] ?? $tour['mo_ta'] ?? '';
                        $moTaRutGonQT = mb_strlen($moTaQT) > 80 ? mb_substr($moTaQT, 0, 80) . '...' : $moTaQT;
                    ?>
                    <p class="card-text">
                        <i class="bi bi-geo-alt-fill text-primary me-1"></i> <?php echo htmlspecialchars($moTaRutGonQT); ?><br>
                        <i class="bi bi-cash-coin text-success me-1"></i> Giá chỉ từ <b><?php echo number_format($tour['gia_tour'] ?? $tour['gia_co_ban'] ?? 0); ?>đ</b>
                    </p>
                        <?php $urlDatTourQT = "index.php?act=khachHang/datTour&id=" . ($tour['id'] ?? ''); ?>
                        <?php $urlThanhToanQT = "index.php?act=khachHang/thanhToanTour&id=" . ($tour['id'] ?? $tour['tour_id']); ?>
                        <a href="index.php?act=khachHang/thanhToanTour&id=<?php echo $tour['id'] ?? $tour['tour_id']; ?>" class="btn btn-primary"><i class="bi bi-cart-plus me-1"></i> Đặt ngay & Thanh toán</a>
                    <!-- Section: Chi tiết tour quốc tế -->
                    <?php
                    $lichTrinhList = $tour['lich_trinh'] ?? [];
                    $lichKhoiHanhList = $tour['lich_khoi_hanh'] ?? [];
                    $hinhAnhList = $tour['hinh_anh_list'] ?? [];
                    ?>
                    <div class="mt-3">
                        <a href="index.php?act=khachHang/chiTietTour&id=<?php echo $tour['id'] ?? $tour['tour_id']; ?>" class="btn btn-outline-info btn-sm"><i class="bi bi-info-circle me-1"></i> Xem chi tiết tour</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">Hiện chưa có tour quốc tế nào.</div>

        <?php endif; ?>
        <!-- Ưu đãi đặc biệt -->
        <div class="benefit-bar my-5">
            <div class="benefit-item">
                <div class="benefit-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <div class="benefit-title">Giảm 10% cho khách mới</div>
                    <div class="benefit-desc">Đặt tour lần đầu &mdash; nhận ưu đãi ngay</div>
                </div>
            </div>
            <div class="benefit-divider"></div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="bi bi-people-fill"></i></div>
                <div>
                    <div class="benefit-title">Voucher 500.000đ nhóm</div>
                    <div class="benefit-desc">Từ 5 người trở lên &mdash; áp dụng tức thì</div>
                </div>
            </div>
            <div class="benefit-divider"></div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                <div>
                    <div class="benefit-title">Hoàn tiền 100% nếu hủy</div>
                    <div class="benefit-desc">Hủy trước 7 ngày &mdash; hoàn toàn bộ</div>
                </div>
            </div>
            <div class="benefit-divider"></div>
            <div class="benefit-item">
                <div class="benefit-icon"><i class="bi bi-headset"></i></div>
                <div>
                    <div class="benefit-title">Tư vấn miễn phí 7/7</div>
                    <div class="benefit-desc">Chuyên gia hỗ trợ từ 7:00 &ndash; 22:00</div>
                </div>
            </div>
        </div>
<style>
.benefit-bar {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    background: transparent;
    border-radius: 0;
    padding: 0;
    border: 0;
    box-shadow: none;
}
.benefit-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: linear-gradient(180deg, rgba(255,255,255,.94), rgba(250,247,241,.96));
    border: 1px solid rgba(15,23,42,.08);
    border-radius: 22px;
    padding: 20px 18px;
    min-height: 108px;
    box-shadow: 0 12px 34px rgba(2,6,23,.08);
    transition: transform .22s ease, box-shadow .22s ease;
}
.benefit-item:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 48px rgba(2,6,23,.12);
}
.benefit-icon {
    width: 54px;
    height: 54px;
    flex-shrink: 0;
    background: linear-gradient(135deg, rgba(214,178,109,.18), rgba(214,178,109,.1));
    border: 1px solid rgba(214,178,109,.34);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    color: #d6b26d;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.68);
}
.benefit-title {
    font-family: "Playfair Display", Georgia, serif;
    font-size: 1.02rem;
    font-weight: 700;
    color: #132033;
    line-height: 1.3;
    margin-bottom: 5px;
}
.benefit-desc {
    font-size: .84rem;
    color: #64748b;
    font-weight: 600;
    line-height: 1.5;
}
.benefit-divider {
    display: none;
}
@media (max-width: 1100px) {
    .benefit-bar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 576px) {
    .benefit-bar { grid-template-columns: 1fr; gap: 12px; }
    .benefit-item { min-height: 0; padding: 16px 15px; border-radius: 18px; }
    .benefit-icon { width: 48px; height: 48px; border-radius: 14px; }
    .benefit-title { font-size: .96rem; }
}
</style>
        <div class="mt-5" id="reviews">
            <div class="d-flex align-items-flex-end justify-content-between mb-2 flex-wrap gap-2">
                <div>
                    <h2 class="fw-bold mb-1">Đánh giá khách hàng</h2>
                    <p class="text-muted mb-0" style="font-size:.95rem;">Chia sẻ chân thực từ những hành khách đã đồng hành cùng chúng tôi</p>
                </div>
            </div>
            <div class="mobile-inline-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt để xem thêm đánh giá</div>
            <div class="row g-4 mt-1 reviews-mobile-track">
                <?php foreach ($danhGiaTot as $dg): ?>
                <?php
                    $diem = (int)($dg['diem'] ?? 0);
                    $ten  = htmlspecialchars($dg['ten_khach_hang'] ?? $dg['ten'] ?? 'Ẩn danh');
                    $anh  = htmlspecialchars($dg['anh'] ?? ($dg['anh_dai_dien'] ?? 'https://ui-avatars.com/api/?name=' . urlencode($dg['ten_khach_hang'] ?? 'User') . '&background=d6b26d&color=132033&size=80'));
                    $noi  = htmlspecialchars($dg['noi_dung'] ?? '');
                    $loai = htmlspecialchars($dg['tieu_chi'] ?? $dg['loai_danh_gia'] ?? '');
                ?>
                <div class="col-md-4 rv-mobile-item">
                    <div class="rv-card">
                        <div class="rv-quote">&ldquo;</div>
                        <p class="rv-text"><?php echo $noi; ?></p>
                        <div class="rv-stars">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <i class="bi <?php echo $s <= $diem ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                            <?php endfor; ?>
                            <span class="rv-score"><?php echo $diem; ?>/5</span>
                        </div>
                        <div class="rv-footer">
                            <img class="rv-avatar" src="<?php echo $anh; ?>" alt="<?php echo $ten; ?>" width="44" height="44">
                            <div>
                                <div class="rv-name"><?php echo $ten; ?></div>
                                <?php if ($loai): ?><div class="rv-tag"><?php echo $loai; ?></div><?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
        .rv-card {
            background: #fff;
            border: 1px solid rgba(15,23,42,.08);
            border-radius: 22px;
            padding: 28px 26px 22px;
            box-shadow: 0 10px 36px rgba(2,6,23,.08);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform .22s ease, box-shadow .22s ease;
        }
        .rv-card:hover { transform: translateY(-5px); box-shadow: 0 22px 56px rgba(2,6,23,.14); }
        .rv-quote {
            font-size: 5rem;
            line-height: .7;
            color: rgba(214,178,109,.28);
            font-family: Georgia, serif;
            font-weight: 900;
            margin-bottom: 8px;
            user-select: none;
        }
        .rv-text { font-size: .95rem; color: #334155; line-height: 1.7; flex: 1; margin-bottom: 16px; }
        .rv-stars { display: flex; align-items: center; gap: 3px; margin-bottom: 18px; color: #d6b26d; font-size: .95rem; }
        .rv-score { font-size: .78rem; font-weight: 800; color: #94a3b8; margin-left: 6px; }
        .rv-footer { display: flex; align-items: center; gap: 12px; border-top: 1px solid rgba(15,23,42,.07); padding-top: 16px; }
        .rv-avatar { border-radius: 999px; object-fit: cover; outline: 2px solid rgba(214,178,109,.4); outline-offset: 2px; }
        .rv-name { font-weight: 800; font-size: .92rem; color: #0f172a; }
        .rv-tag { font-size: .75rem; font-weight: 700; color: #d6b26d; text-transform: uppercase; letter-spacing: .07em; margin-top: 2px; }
        @media (max-width: 767.98px) {
            .reviews-mobile-track {
                flex-wrap: nowrap;
                overflow-x: auto;
                overflow-y: hidden;
                scroll-snap-type: x proximity;
                -webkit-overflow-scrolling: touch;
                padding-bottom: 6px;
                margin-right: -12px;
            }
            .reviews-mobile-track::-webkit-scrollbar { display: none; }
            .rv-mobile-item {
                flex: 0 0 84%;
                max-width: 84%;
                scroll-snap-align: start;
            }
            .rv-card {
                border-radius: 20px;
                padding: 20px 18px 18px;
                min-height: 100%;
            }
            .rv-quote {
                font-size: 3.4rem;
                margin-bottom: 4px;
            }
            .rv-text {
                font-size: .9rem;
                line-height: 1.6;
                margin-bottom: 12px;
            }
            .rv-stars {
                margin-bottom: 14px;
            }
            .rv-footer {
                gap: 10px;
                padding-top: 14px;
            }
            .rv-avatar {
                width: 40px;
                height: 40px;
            }
            .rv-name { font-size: .88rem; }
            .rv-tag {
                font-size: .7rem;
                letter-spacing: .05em;
            }
        }
        @media (max-width: 420px) {
            .rv-mobile-item {
                flex-basis: 88%;
                max-width: 88%;
            }
        }
        </style>
    </div>

    <!-- Section: Địa danh nổi bật dạng grid -->
    <div class="container mt-5" id="places">
        <div class="home-section-head mb-4">
            <div>
                <h2 class="fw-bold mb-1">Địa danh nổi bật</h2>
                <p class="text-muted mb-0 home-section-copy">Những điểm đến tạo cảm hứng cho hành trình tiếp theo của bạn</p>
            </div>
            <a class="home-section-link" href="index.php?act=khachHang/danhSachTour">Khám phá tour <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="mobile-inline-hint mobile-places-hint d-md-none"><i class="bi bi-arrow-left-right"></i> Vuốt để khám phá địa danh nổi bật</div>
        <div class="featured-places-grid">
            <?php if (!empty($danhSachDiaDanh)): ?>
                <?php foreach ($danhSachDiaDanh as $index => $diaDanh): ?>
                    <div class="featured-place-card">
                        <img src="<?php echo htmlspecialchars($diaDanh['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($diaDanh['ten']); ?>">
                        <div class="featured-place-overlay">
                            <span class="featured-place-badge">Điểm đến <?php echo (int)$index + 1; ?></span>
                            <div class="featured-place-copy">
                                <span class="featured-place-name"><?php echo htmlspecialchars($diaDanh['ten']); ?></span>
                                <span class="featured-place-sub">Khám phá hành trình cảm hứng</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-muted text-center">Chưa có dữ liệu địa danh.</div>
            <?php endif; ?>
        </div>
    </div>
    <style>
    .featured-places-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
        grid-auto-rows: 220px;
    }
    /* Masonry style for first 6 items */
    .featured-place-card:nth-child(1) {
        grid-row: span 2;
        grid-column: span 1;
        min-height: 440px;
    }
    .featured-place-card:nth-child(2),
    .featured-place-card:nth-child(3) {
        grid-row: span 1;
        grid-column: span 1;
        min-height: 220px;
    }
    .featured-place-card:nth-child(4),
    .featured-place-card:nth-child(5) {
        grid-row: span 1;
        grid-column: span 1;
        min-height: 220px;
    }
    .featured-place-card:nth-child(6) {
        grid-row: span 2;
        grid-column: span 1;
        min-height: 440px;
    }
    @media (max-width: 900px) {
        .featured-place-card:nth-child(1), .featured-place-card:nth-child(6) {
            min-height: 220px;
            grid-row: span 1;
        }
    }
    
    .featured-place-card {
        position: relative;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 18px 54px rgba(2,6,23,.10);
        transition: transform 0.22s, box-shadow 0.22s;
        cursor: pointer;
        min-height: 220px;
        background: #eee;
    }
    .featured-place-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        min-height: 220px;
        filter: brightness(0.9);
        transition: transform .35s ease, filter .22s ease;
    }
    .featured-place-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 26px 72px rgba(2,6,23,.16);
    }
    .featured-place-card:hover img {
        transform: scale(1.04);
        filter: brightness(1);
    }
    .featured-place-card .featured-place-overlay {
        opacity: 1;
        transition: opacity 0.2s;
    }
    .featured-place-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        padding: 18px;
        pointer-events: none;
        background: linear-gradient(180deg, rgba(0,0,0,0) 34%, rgba(11,18,32,.82) 100%);
    }

    .featured-place-badge {
        position: absolute;
        left: 18px;
        top: 18px;
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        background: rgba(23,65,59,.92);
        color: #fff;
        padding: 7px 12px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        border: 1px solid rgba(255,255,255,.18);
        box-shadow: 0 10px 26px rgba(0,0,0,.16);
    }
    .featured-place-copy {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .featured-place-name {
        color: #fff;
        font-size: 1.7rem;
        font-weight: 700;
        font-family: "Playfair Display", Georgia, serif;
        text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        letter-spacing: .2px;
        text-transform: none;
        padding: 0;
        border-radius: 0;
        background: transparent;
        pointer-events: auto;
    }
    .featured-place-sub {
        color: rgba(255,255,255,.72);
        font-size: .88rem;
        font-weight: 600;
    }
    @media (max-width: 600px) {
        .featured-place-name { font-size: 1.1rem; }
        .featured-place-sub { font-size: .76rem; }
        .featured-place-card { min-height: 120px; }
    }
    @media (max-width: 767.98px) {
        .home-section-head {
            align-items: flex-start;
            gap: 10px;
        }
        .home-section-copy {
            max-width: 34ch;
        }
        .home-section-link {
            padding: 9px 16px;
            font-size: .82rem;
        }
        .featured-places-grid {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x proximity;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 18px;
            padding-bottom: 6px;
        }
        .featured-places-grid::-webkit-scrollbar { display: none; }
        .featured-place-card,
        .featured-place-card:nth-child(1),
        .featured-place-card:nth-child(2),
        .featured-place-card:nth-child(3),
        .featured-place-card:nth-child(4),
        .featured-place-card:nth-child(5),
        .featured-place-card:nth-child(6) {
            flex: 0 0 84%;
            min-height: 240px;
            grid-row: auto;
            grid-column: auto;
            scroll-snap-align: start;
            border-radius: 22px;
        }
        .featured-place-card img,
        .featured-place-card:nth-child(1) img,
        .featured-place-card:nth-child(6) img {
            min-height: 240px;
        }
        .featured-place-overlay {
            padding: 16px;
        }
        .featured-place-badge {
            left: 14px;
            top: 14px;
            padding: 6px 11px;
            font-size: .68rem;
        }
        .featured-place-name {
            font-size: 1.25rem;
        }
        .featured-place-sub {
            font-size: .78rem;
        }
    }
    @media (max-width: 420px) {
        .featured-place-card,
        .featured-place-card:nth-child(1),
        .featured-place-card:nth-child(2),
        .featured-place-card:nth-child(3),
        .featured-place-card:nth-child(4),
        .featured-place-card:nth-child(5),
        .featured-place-card:nth-child(6) {
            flex-basis: 88%;
            min-height: 220px;
        }
        .featured-place-card img {
            min-height: 220px;
        }
    }
    </style>

    <style id="luxury-theme">
        html{ scroll-behavior:smooth; }

        @keyframes luxurySoftIn {
            from { opacity: 0; filter: blur(10px); }
            to { opacity: 1; filter: blur(0); }
        }

        @keyframes luxuryChatPulse {
            0% { opacity: .45; transform: scale(.82); }
            70% { opacity: 0; transform: scale(1.45); }
            100% { opacity: 0; transform: scale(1.45); }
        }

        :root{
            --lx-bg:#0b1220;
            --lx-bg2:#070d18;
            --lx-ink:#0f172a;
            --lx-muted:#64748b;
            --lx-gold:#d6b26d;
            --lx-gold2:#b9893d;
            --lx-line:rgba(255,255,255,.16);
            --lx-bg-img: url('https://images.unsplash.com/photo-1483729558449-99ef09a8c325?auto=format&fit=crop&w=2400&q=60');
        }

        body.luxury{
            color:var(--lx-ink);
            font-family:"Manrope", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Arial, sans-serif;
            background:#f5f6f8;
            position:relative;
            min-height:100vh;
        }
        body.luxury::before{
            content:"";
            position:fixed;
            inset:-60px;
            z-index:-2;
            background-image:var(--lx-bg-img);
            background-size:cover;
            background-position:center;
            filter: blur(26px) saturate(1.05);
            transform: scale(1.08);
            opacity:.28;
        }
        body.luxury::after{
            content:"";
            position:fixed;
            inset:0;
            z-index:-1;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(214,178,109,.16), transparent 60%),
                radial-gradient(900px 520px at 85% 0%, rgba(11,18,32,.10), transparent 55%),
                rgba(245,246,248,.92);
        }
        body.luxury h1, body.luxury h2, body.luxury h3, body.luxury .navbar-brand{
            font-family:"Playfair Display", ui-serif, Georgia, "Times New Roman", Times, serif;
        }
        body.luxury h2.fw-bold{
            letter-spacing:.2px;
            position:relative;
            padding-bottom:.25rem;
        }
        body.luxury h2.fw-bold::after{
            content:"";
            display:block;
            width:64px;
            height:2px;
            margin-top:.5rem;
            background:linear-gradient(90deg,var(--lx-gold), transparent);
            border-radius:999px;
        }
        body.luxury .home-section-head{
            display:flex;
            align-items:flex-end;
            justify-content:space-between;
            gap:14px;
            flex-wrap:wrap;
        }
        body.luxury .home-section-head h2{
            margin:0;
        }
        body.luxury .home-section-head h2::after{
            margin-top:.45rem;
        }
        body.luxury .home-section-copy{
            font-size:.95rem;
            color:var(--lx-muted) !important;
            margin-top:.15rem;
        }
        body.luxury .home-section-link{
            display:inline-flex;
            align-items:center;
            gap:4px;
            padding:10px 18px;
            border-radius:999px;
            border:1px solid rgba(214,178,109,.44);
            color:#9a6e1c;
            text-decoration:none;
            font-size:.86rem;
            font-weight:800;
            background:rgba(214,178,109,.08);
            transition:background .2s, border-color .2s, color .2s;
            white-space:nowrap;
        }
        body.luxury .home-section-link:hover{
            background:#d6b26d;
            border-color:#d6b26d;
            color:#132033;
        }

        body.luxury .luxury-nav{
            position:absolute;
            top:0; left:0; right:0;
            z-index:50;
            background:linear-gradient(to bottom, rgba(11,18,32,.92), rgba(11,18,32,.35), transparent);
            box-shadow:none !important;
            backdrop-filter: blur(10px);
        }
        body.luxury .luxury-nav.is-scrolled{
            background:rgba(11,18,32,.92);
            box-shadow:0 12px 42px rgba(2,6,23,.35) !important;
            border-bottom:1px solid rgba(214,178,109,.14);
        }

        #home, #tools, #experiences, #tours, #reviews, #places, #support{
            scroll-margin-top: 96px;
        }
        body.luxury .brand-mark{ color:var(--lx-gold); }
        body.luxury .brand-name{
            color:#fff;
            letter-spacing:.6px;
        }
        body.luxury .luxury-nav .nav-link{
            color:rgba(255,255,255,.82) !important;
            font-size:.88rem;
            text-transform:uppercase;
            letter-spacing:.12em;
            border-radius:999px;
            padding:.55rem .85rem;
            border:1px solid transparent;
            transition: .2s;
        }
        body.luxury .luxury-nav .nav-link i{ opacity:.9; }
        body.luxury .luxury-nav .nav-link:hover,
        body.luxury .luxury-nav .nav-link.active{
            background:rgba(214,178,109,.14);
            border-color:rgba(214,178,109,.38);
            color:#fff !important;
        }
        body.luxury .luxury-cta{
            background:linear-gradient(135deg,var(--lx-gold),var(--lx-gold2));
            color:#132033 !important;
            border:none !important;
            box-shadow:0 10px 26px rgba(185,137,61,.28);
        }
        body.luxury .luxury-cta:hover{
            filter:brightness(1.02);
            box-shadow:0 14px 36px rgba(185,137,61,.34);
        }
        body.luxury .luxury-nav .nav-icon-link{
            width:42px;
            height:42px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 !important;
            border:1px solid rgba(255,255,255,.20);
            border-radius:999px;
            background:rgba(255,255,255,.06);
            text-transform:none;
            letter-spacing:0;
            font-size:1.05rem;
            position:relative;
        }
        body.luxury .luxury-nav .customer-notification-badge{
            position:absolute;
            top:-6px;
            right:-6px;
            min-width:18px;
            height:18px;
            border-radius:999px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            padding:0 5px;
            background:#ef4444;
            color:#fff;
            font-size:.66rem;
            font-weight:800;
            border:1px solid rgba(255,255,255,.78);
            box-shadow:0 8px 18px rgba(239,68,68,.35);
            line-height:1;
        }
        body.luxury .luxury-nav .nav-icon-link:hover,
        body.luxury .luxury-nav .nav-icon-link.active{
            background:rgba(214,178,109,.20);
            border-color:rgba(214,178,109,.44);
            color:#fff !important;
        }
        body.luxury .luxury-nav .nav-icon-link.nav-icon-danger:hover,
        body.luxury .luxury-nav .nav-icon-link.nav-icon-danger.active{
            background:rgba(220,53,69,.22);
            border-color:rgba(220,53,69,.48);
        }

        body.luxury .hero-hotel{
            height:680px;
            border-radius:0;
        }
        body.luxury .hero-overlay{
            border-radius:0;
            background:
                linear-gradient(90deg, rgba(11,18,32,.94) 0%, rgba(11,18,32,.74) 45%, rgba(11,18,32,.28) 100%),
                radial-gradient(900px 520px at 15% 20%, rgba(214,178,109,.14), transparent 55%);
        }
        body.luxury .hero-content{
            padding-top:110px;
        }
        body.luxury .hero-hotel h5{
            letter-spacing:.18em;
            text-transform:uppercase;
            color:rgba(214,178,109,.95) !important;
        }
        body.luxury .hero-hotel h1{
            letter-spacing:.3px;
        }
        body.luxury .hero-main-copy{
            transform: translateY(-80px);
            animation: luxurySoftIn .8s ease both;
        }

        body.luxury .booking-box{
            background:rgba(13,20,33,.72);
            border:1px solid rgba(214,178,109,.22);
            box-shadow:0 22px 80px rgba(2,6,23,.55);
            transform: translateY(-48px);
            max-width: 550px;
            margin-left: auto;
            padding: 22px 20px;
            border-radius: 16px;
            animation: luxurySoftIn .9s .12s ease both;
        }
        body.luxury .booking-box h4{
            font-size: 1.85rem;
            margin-bottom: .45rem !important;
        }
        body.luxury .booking-box p.small{
            font-size: .95rem;
            margin-bottom: .9rem;
        }
        body.luxury .booking-box .mb-3{
            margin-bottom: .72rem !important;
        }
        body.luxury .booking-box .form-label{
            font-size: .98rem;
            margin-bottom: .28rem;
        }
        body.luxury .booking-box .form-control,
        body.luxury .booking-box .form-select{
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.16);
            color:#fff;
            min-height: 42px;
            padding: .45rem .7rem;
            font-size: .98rem;
        }
        body.luxury .booking-box .input-icon-wrap{
            position:relative;
        }
        body.luxury .booking-box .input-icon{
            position:absolute;
            left:14px;
            top:50%;
            transform:translateY(-50%);
            color:rgba(214,178,109,.92);
            font-size:1rem;
            pointer-events:none;
            z-index:2;
        }
        body.luxury .booking-box .has-icon{
            padding-left:2.3rem;
        }
        body.luxury .booking-box .textarea-wrap .textarea-icon{
            top:16px;
            transform:none;
        }
        body.luxury .booking-box .textarea-has-icon{
            padding-left:2.3rem;
            padding-top:.8rem;
        }
        body.luxury .booking-box form > .mb-3 > .form-label::before,
        body.luxury .booking-box form .row.g-2 .form-label::before{
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:1.15rem;
            margin-right:.45rem;
            color:rgba(214,178,109,.95);
            font-size:.95rem;
            font-weight:400;
        }
        body.luxury .booking-box form > .mb-3:nth-of-type(1) > .form-label::before{
            content:"📍";
        }
        body.luxury .booking-box form .row.g-2 .col-6:first-child .form-label::before{
            content:"📅";
        }
        body.luxury .booking-box form .row.g-2 .col-6:last-child .form-label::before{
            content:"🗓";
        }
        body.luxury .booking-box form > .mb-3:nth-of-type(2) > .form-label::before{
            content:"👥";
        }
        body.luxury .booking-box form > .mb-3:nth-of-type(3) > .form-label::before{
            content:"✦";
        }
        body.luxury .booking-box input[type="date"]{
            padding-right:2.5rem;
            background-image: linear-gradient(transparent, transparent);
            background-repeat:no-repeat;
            background-position:0 0;
            background-size:100% 100%;
        }
        body.luxury .booking-box .form-control::placeholder{ color: rgba(255,255,255,.65); }
        body.luxury .booking-box .form-control:focus,
        body.luxury .booking-box .form-select:focus{
            border-color:rgba(214,178,109,.60);
            box-shadow:0 0 0 .2rem rgba(214,178,109,.18);
        }
        body.luxury .booking-box textarea.form-control{
            min-height: 74px;
        }
        body.luxury .booking-box .btn{
            background:linear-gradient(135deg,var(--lx-gold),var(--lx-gold2));
            border:none;
            color:#132033;
            min-height: 44px;
            font-size: 1rem;
        }
        body.luxury .booking-box .btn:hover{ filter:brightness(1.03); }
        body.luxury .feature{
            background:rgba(255,255,255,.06);
            border:1px solid rgba(255,255,255,.14);
            border-radius:14px;
            padding:12px 12px;
        }

        body.luxury .enhanced-card{
            border-radius:28px !important;
            background:
                radial-gradient(circle at top left, rgba(220,176,93,.16), transparent 26%),
                linear-gradient(180deg, rgba(255,255,255,.98), rgba(247,244,238,.96));
            border:1px solid rgba(22,34,58,.08) !important;
            backdrop-filter: blur(14px);
            box-shadow:0 20px 52px rgba(10,18,38,.14);
            padding:1rem !important;
        }
        body.luxury .luxury-float{
            position:relative;
            margin-top:-48px !important;
            z-index:4;
        }
        body.luxury .multiTabMenu-bar{
            background:rgba(255,255,255,.84);
            border-radius:999px;
            padding:.5rem;
            margin-bottom:1rem !important;
            border:1px solid rgba(15,23,42,.08);
            box-shadow:inset 0 1px 0 rgba(255,255,255,.92), 0 12px 30px rgba(15,23,42,.08);
        }
        body.luxury .multiTabMenu-item{
            border-radius:999px !important;
            color:#17233c;
            padding:.65rem 1rem !important;
            min-width:138px;
            font-weight:700;
            font-size:.96rem;
            letter-spacing:.01em;
            transition:all .25s ease;
            border:1px solid transparent;
            justify-content:center;
        }
        body.luxury .multiTabMenu-item:hover{
            background:rgba(214,178,109,.12);
            color:#0f2748 !important;
            border-color:rgba(214,178,109,.26);
            transform:translateY(-1px);
        }
        body.luxury .multiTabMenu-item.active{
            background:linear-gradient(135deg,#15233b,#20365f) !important;
            color:#f7e5b6 !important;
            border-color:rgba(214,178,109,.34) !important;
            box-shadow:0 14px 28px rgba(21,35,59,.28);
        }
        body.luxury .multiTabMenu-item.active i{
            color:rgba(214,178,109,.95);
        }
        body.luxury #tab-content-currency{
            background:linear-gradient(145deg,#ffffff,#fbf7ef);
            border:1px solid rgba(214,178,109,.22);
            border-radius:22px;
            padding:16px 18px;
            box-shadow:inset 0 1px 0 rgba(255,255,255,.84);
        }
        body.luxury #tab-content-currency h3{
            color:#13213a !important;
            font-family:"Playfair Display", Georgia, serif;
            font-size:clamp(1.35rem,2vw,1.95rem);
            line-height:1.08;
            margin-bottom:4px;
        }
        body.luxury #tab-content-currency h3 i{
            color:#d6b26d !important;
        }
        body.luxury #tab-content-currency::after{
            content:"Tỉ giá tham khảo cho hành trình cao cấp";
            display:block;
            margin-top:-2px;
            margin-bottom:10px;
            color:#64748b;
            font-size:.84rem;
        }
        body.luxury #tab-content-currency .form-label{
            color:#43536d;
            font-weight:700;
            margin-bottom:6px;
            font-size:.86rem;
        }
        body.luxury #tab-content-currency .form-control,
        body.luxury #tab-content-currency .form-select{
            min-height:52px;
            padding:12px 16px;
        }
        body.luxury .result-box{
            background:
                radial-gradient(circle at top right, rgba(214,178,109,.24), transparent 30%),
                linear-gradient(135deg,#13213a,#1f355c);
            border:1px solid rgba(214,178,109,.18);
            box-shadow:0 20px 44px rgba(19,33,58,.22);
            padding:14px 18px;
            border-radius:18px;
            min-height:70px;
            display:flex;
            align-items:center;
            color:#f8f4eb;
            font-size:1.1rem;
            font-weight:800;
            animation: luxurySoftIn .7s ease both;
        }
        body.luxury .feature:nth-child(1){ animation-delay:.12s; }
        body.luxury .feature:nth-child(2){ animation-delay:.2s; }
        body.luxury .feature:nth-child(3){ animation-delay:.28s; }
        body.luxury .feature:nth-child(4){ animation-delay:.36s; }
        body.luxury .swap-btn{
            background:linear-gradient(135deg,#15233b,#20365f);
            border:1px solid rgba(214,178,109,.18);
            color:#f6dfab;
            width:52px;
            height:52px;
            border-radius:16px;
            box-shadow:0 12px 24px rgba(21,35,59,.2);
            font-size:1.15rem;
        }
        body.luxury .swap-btn:hover{
            filter:none;
            transform:translateY(-2px) rotate(180deg);
            box-shadow:0 20px 34px rgba(21,35,59,.28);
        }
        body.luxury #tab-content-hotel,
        body.luxury #tab-content-combo,
        body.luxury #tab-content-visa{
            background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(246,241,232,.96));
            border:1px solid rgba(214,178,109,.18);
            border-radius:22px;
            padding:16px 18px;
            box-shadow:0 16px 34px rgba(15,23,42,.08);
        }
        body.luxury #tab-content-hotel h3,
        body.luxury #tab-content-combo h3,
        body.luxury #tab-content-visa h3{
            color:#16243c !important;
            font-family:"Playfair Display", Georgia, serif;
        }
        body.luxury #tab-content-hotel .alert,
        body.luxury #tab-content-combo .alert,
        body.luxury #tab-content-visa .alert{
            border-radius:18px;
            border:1px solid rgba(214,178,109,.18);
            background:rgba(255,255,255,.82);
            color:#6b7280;
        }

        body.luxury .experience-card,
        body.luxury .destination-card,
        body.luxury .featured-place-card,
        body.luxury .tour-card{
            box-shadow:0 18px 60px rgba(2,6,23,.12) !important;
        }
        body.luxury .tour-card{
            border:1px solid rgba(15,23,42,.08);
            border-radius:18px;
            overflow:hidden;
        }
        body.luxury .tour-card .card-img-top{
            height:210px;
            object-fit:cover;
        }
        body.luxury .tour-card .btn-primary{
            background:linear-gradient(135deg,var(--lx-bg), #142644);
            border:none;
        }
        body.luxury .tour-card .btn-primary:hover{ filter:brightness(1.04); }
        body.luxury .tour-card .btn-outline-info{
            border-color:rgba(214,178,109,.55);
            color:var(--lx-gold2);
        }
        body.luxury .tour-card .btn-outline-info:hover{
            background:rgba(214,178,109,.10);
            color:#7a561f;
        }

        body.luxury .home-tour-card{
            min-width:415px;
            max-width:415px;
            width:415px;
            background:linear-gradient(180deg,#ffffff,#fbfdfa);
            border:1px solid rgba(15,23,42,.08);
            border-radius:28px;
            overflow:hidden;
            box-shadow:0 24px 70px rgba(2,6,23,.12);
            scroll-snap-align:start;
            display:flex;
            flex-direction:column;
        }
        body.luxury .home-tour-media{
            height:235px;
            position:relative;
            overflow:hidden;
            background:#eef2f7;
        }
        body.luxury .home-tour-media img{
            width:100%;
            height:100%;
            object-fit:cover;
            display:block;
            transition: transform .5s ease, filter .35s ease;
        }
        body.luxury .home-tour-card:hover .home-tour-media img{
            transform: scale(1.045);
            filter: saturate(1.05) contrast(1.02);
        }
        body.luxury .home-tour-badge{
            position:absolute;
            left:16px;
            top:16px;
            border-radius:16px;
            background:rgba(29,42,39,.9);
            color:#fff;
            padding:9px 15px;
            font-weight:900;
            font-size:.82rem;
            box-shadow:0 10px 24px rgba(0,0,0,.18);
        }
        body.luxury .home-tour-body{
            padding:20px;
            display:flex;
            flex:1;
            flex-direction:column;
        }
        body.luxury .home-tour-body h3{
            margin:0 0 10px;
            color:#0f1f1b;
            font-family:"Manrope", sans-serif;
            font-size:1.08rem;
            font-weight:900;
            line-height:1.32;
        }
        body.luxury .home-tour-desc{
            color:#62716d;
            font-size:.88rem;
            line-height:1.58;
            margin:0 0 15px;
            min-height:66px;
            display:-webkit-box;
            overflow:hidden;
            -webkit-box-orient:vertical;
            -webkit-line-clamp:3;
            line-clamp:3;
        }
        body.luxury .home-tour-meta{
            display:grid;
            gap:8px;
            margin-top:auto;
        }
        body.luxury .home-tour-meta div{
            display:flex;
            gap:9px;
            align-items:flex-start;
            color:#243632;
            font-size:.84rem;
            font-weight:700;
        }
        body.luxury .home-tour-meta i{
            color:#047a78;
            font-size:.86rem;
            min-width:17px;
            transform:translateY(1px);
        }
        body.luxury .home-tour-footer{
            border-top:1px solid rgba(15,23,42,.12);
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:12px;
            margin-top:18px;
            padding-top:18px;
        }
        body.luxury .home-tour-footer > div:first-child{
            flex:1 1 auto;
            min-width:0;
        }
        body.luxury .home-tour-price-label{
            color:#64706d;
            font-size:.68rem;
            font-weight:900;
            letter-spacing:.12em;
            text-transform:uppercase;
        }
        body.luxury .home-tour-price{
            color:#a15c08;
            font-size:1.14rem;
            font-weight:900;
            letter-spacing:.04em;
            white-space:nowrap;
        }
        body.luxury .home-tour-actions{
            display:flex;
            gap:8px;
            align-items:center;
            flex:0 0 auto;
            justify-content:flex-end;
            min-width:0;
        }
        body.luxury .home-tour-detail,
        body.luxury .home-tour-book{
            min-height:46px;
            border-radius:999px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            gap:7px;
            padding:9px 13px;
            text-decoration:none;
            font-weight:900;
            white-space:nowrap;
            font-size:.82rem;
        }
        body.luxury .home-tour-detail{
            min-width:112px;
        }
        body.luxury .home-tour-book{
            min-width:116px;
        }
        body.luxury .home-tour-detail{
            background:#f3ead8;
            color:#17413b;
        }
        body.luxury .home-tour-book{
            background:#17413b;
            color:#fff;
            box-shadow:0 14px 30px rgba(23,65,59,.22);
        }
        body.luxury .home-tour-card:hover,
        body.luxury .special-offer-box:hover,
        body.luxury .review-card:hover,
        body.luxury .featured-place-card:hover{
            will-change: transform;
        }

        body.luxury .js-reveal{
            opacity:0;
            transform: translateY(22px);
            transition: opacity .62s ease, transform .62s ease;
        }
        body.luxury .js-reveal.is-visible{
            opacity:1;
            transform: translateY(0);
        }

        body.luxury .luxury-scroll{
            gap:22px !important;
            padding-bottom:14px !important;
            overflow-x:auto;
            overflow-y:hidden !important;
            scroll-snap-type:x mandatory;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            position:relative;
        }
        body.luxury .luxury-scroll:has(.home-tour-card){
            gap:22px !important;
        }
        /* Remove edge fade overlay (looked like a "blur" on cards) */
        body.luxury .luxury-scroll::before,
        body.luxury .luxury-scroll::after{
            content:none;
        }
        body.luxury .luxury-scroll .tour-card{
            scroll-snap-align:start;
        }
        body.luxury .luxury-scroll::-webkit-scrollbar{ height:10px; }
        body.luxury .luxury-scroll::-webkit-scrollbar-track{
            background:rgba(15,23,42,.08);
            border-radius:999px;
        }
        body.luxury .luxury-scroll::-webkit-scrollbar-thumb{
            background:linear-gradient(90deg, rgba(11,18,32,.75), rgba(20,38,68,.78));
            border-radius:999px;
            border:2px solid rgba(245,246,248,.92);
        }

        body.luxury .review-card{
            border-radius:18px;
            border:1px solid rgba(15,23,42,.10);
            background:rgba(255,255,255,.72);
            backdrop-filter: blur(10px);
            box-shadow:0 18px 60px rgba(2,6,23,.10);
            transition: transform .18s, box-shadow .18s;
        }
        body.luxury .review-card:hover{
            transform: translateY(-4px);
            box-shadow:0 22px 80px rgba(2,6,23,.14);
        }
        body.luxury .review-card img{
            outline:2px solid rgba(214,178,109,.35);
            outline-offset:2px;
        }

        body.luxury .featured-place-card{
            border:1px solid rgba(255,255,255,.20);
        }
        body.luxury .featured-place-card .featured-place-overlay{
            opacity:1 !important;
            align-items:flex-end;
            justify-content:flex-start;
            padding:18px;
            background:linear-gradient(180deg, rgba(0,0,0,0) 40%, rgba(11,18,32,.78) 100%);
        }
        body.luxury .featured-place-name{
            opacity:1 !important;
            text-transform:none;
            letter-spacing:.2px;
            font-size:1.35rem;
            background:rgba(0,0,0,.0);
            padding:0;
        }

        body.luxury .special-offer-box{
            background:linear-gradient(180deg,#ffffff,#fbfaf7) !important;
            border:1px solid rgba(214,178,109,.38) !important;
            box-shadow:0 18px 60px rgba(2,6,23,.10) !important;
        }
        body.luxury .special-offer-box h3{
            color:#132033 !important;
        }
        body.luxury .offer-desc{
            color:#334155 !important;
        }
        body.luxury .offer-note{
            color:rgba(100,116,139,.9) !important;
        }

        body.luxury .footer{
            margin-top:70px;
            padding:46px 0;
            background:linear-gradient(180deg,var(--lx-bg), var(--lx-bg2));
            color:rgba(255,255,255,.78);
        }
        body.luxury .footer a{
            color:rgba(214,178,109,.90);
            text-decoration:none;
        }
        body.luxury .footer a:hover{ color:#fff; }

        body.luxury .customer-chatbot {
            position: fixed;
            right: 24px;
            bottom: 24px;
            z-index: 1300;
            font-family: "Manrope", sans-serif;
        }
        body.luxury .chatbot-toggle {
            width: 64px;
            height: 64px;
            border: 1px solid rgba(214,178,109,.45);
            border-radius: 22px;
            background: linear-gradient(135deg, #d6b26d, #f2cf83);
            color: #101827;
            box-shadow: 0 18px 46px rgba(8,13,26,.32);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.55rem;
            cursor: pointer;
            position: relative;
        }
        body.luxury .chatbot-toggle::before {
            content: "";
            position: absolute;
            inset: -8px;
            border-radius: 28px;
            border: 2px solid rgba(214,178,109,.55);
            animation: luxuryChatPulse 1.8s ease-out 3;
        }
        body.luxury .chatbot-toggle::after {
            content: "";
            position: absolute;
            right: 8px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: #22c55e;
            border: 2px solid #fff;
        }
        body.luxury .chatbot-panel {
            position: absolute;
            right: 0;
            bottom: 82px;
            width: min(380px, calc(100vw - 28px));
            max-height: min(640px, calc(100vh - 130px));
            display: none;
            overflow: hidden;
            border-radius: 28px;
            border: 1px solid rgba(214,178,109,.28);
            background: rgba(12,18,32,.94);
            color: #f8f4eb;
            box-shadow: 0 30px 90px rgba(0,0,0,.36);
            backdrop-filter: blur(18px);
        }
        body.luxury .customer-chatbot.is-open .chatbot-panel {
            display: flex;
            flex-direction: column;
        }
        body.luxury .chatbot-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            background: linear-gradient(135deg, rgba(214,178,109,.18), rgba(255,255,255,.05));
            border-bottom: 1px solid rgba(255,255,255,.1);
        }
        body.luxury .chatbot-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 900;
        }
        body.luxury .chatbot-subtitle {
            margin: 3px 0 0;
            color: rgba(255,255,255,.66);
            font-size: .82rem;
        }
        body.luxury .chatbot-close {
            border: 0;
            width: 36px;
            height: 36px;
            border-radius: 14px;
            background: rgba(255,255,255,.1);
            color: #fff;
        }
        body.luxury .chatbot-messages {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            padding: 18px;
        }
        body.luxury .chat-msg {
            width: fit-content;
            max-width: 88%;
            border-radius: 18px;
            padding: 10px 13px;
            font-size: .92rem;
            line-height: 1.5;
        }
        body.luxury .chat-msg.bot {
            background: rgba(255,255,255,.1);
            border-top-left-radius: 6px;
        }
        body.luxury .chat-msg.user {
            align-self: flex-end;
            background: linear-gradient(135deg, #d6b26d, #f2cf83);
            color: #101827;
            border-top-right-radius: 6px;
            font-weight: 700;
        }
        body.luxury .chatbot-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            padding: 0 18px 16px;
        }
        body.luxury .chat-suggestion {
            border: 1px solid rgba(214,178,109,.36);
            border-radius: 999px;
            background: rgba(255,255,255,.08);
            color: #f7dfac;
            font-size: .82rem;
            font-weight: 800;
            padding: 8px 11px;
        }
        body.luxury .chatbot-form {
            display: flex;
            gap: 8px;
            padding: 14px;
            border-top: 1px solid rgba(255,255,255,.1);
            background: rgba(0,0,0,.16);
        }
        body.luxury .chatbot-form input {
            flex: 1;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 16px;
            background: rgba(255,255,255,.08);
            color: #fff;
            outline: none;
            padding: 11px 13px;
        }
        body.luxury .chatbot-form input::placeholder { color: rgba(255,255,255,.5); }
        body.luxury .chatbot-form button {
            border: 0;
            border-radius: 16px;
            background: #d6b26d;
            color: #101827;
            font-weight: 900;
            padding: 0 14px;
        }
        body.luxury .mobile-home-shortcuts,
        body.luxury .mobile-inline-hint{
            display:none;
        }

        @media (max-width: 992px){
            /* Keep sections in normal flow on tablet/mobile to avoid overlap. */
            body.luxury .hero-hotel{ height: auto; min-height: 0; }
            body.luxury .hero-content{ padding-top: 96px; padding-bottom: 22px; }
            body.luxury .hero-main-copy{ transform: none; margin-bottom: 14px; }
            body.luxury .booking-box{ transform: none; max-width: 520px; margin: 0 auto; }
            body.luxury .luxury-float{ margin-top: 20px !important; }
            body.luxury .featured-place-name{ font-size:1.1rem; }
        }
        @media (max-width: 767.98px){
            body.luxury .container{
                padding-left:18px;
                padding-right:18px;
            }
            body.luxury .luxury-content > .container.mt-5{
                margin-top:32px !important;
            }
            body.luxury .mobile-home-shortcuts{
                display:grid;
                grid-template-columns:repeat(2, minmax(0, 1fr));
                gap:12px;
                margin-top:16px;
                margin-bottom:6px;
            }
            body.luxury .mobile-shortcut-card{
                display:flex;
                align-items:flex-start;
                gap:12px;
                padding:14px 13px;
                border-radius:20px;
                text-decoration:none;
                color:#132033;
                background:linear-gradient(180deg, rgba(255,255,255,.96), rgba(248,244,236,.96));
                border:1px solid rgba(214,178,109,.18);
                box-shadow:0 14px 32px rgba(2,6,23,.08);
            }
            body.luxury .mobile-shortcut-card strong{
                display:block;
                font-size:.9rem;
                font-weight:900;
                line-height:1.2;
                margin-bottom:3px;
            }
            body.luxury .mobile-shortcut-card small{
                display:block;
                color:#66758d;
                font-size:.74rem;
                line-height:1.45;
            }
            body.luxury .mobile-shortcut-icon{
                width:40px;
                height:40px;
                flex:0 0 40px;
                border-radius:14px;
                display:flex;
                align-items:center;
                justify-content:center;
                color:#9a6e1c;
                background:rgba(214,178,109,.14);
                border:1px solid rgba(214,178,109,.22);
                box-shadow:inset 0 1px 0 rgba(255,255,255,.65);
            }
            body.luxury .mobile-inline-hint{
                display:flex;
                align-items:center;
                gap:8px;
                margin:4px 0 12px;
                color:#7a8498;
                font-size:.76rem;
                font-weight:800;
                letter-spacing:.03em;
                text-transform:uppercase;
            }
            body.luxury .mobile-inline-hint i{
                color:#b58839;
            }
            body.luxury .luxury-nav{
                padding-top:.8rem !important;
                padding-bottom:.8rem !important;
            }
            body.luxury .navbar-brand{
                font-size:1.35rem !important;
            }
            body.luxury .navbar-toggler{
                padding:.45rem .7rem;
                border-radius:16px;
                background:rgba(255,255,255,.08);
            }
            body.luxury .luxury-nav .navbar-collapse{
                margin-top:12px;
                padding:14px;
                border-radius:24px;
                background:rgba(11,18,32,.92);
                border:1px solid rgba(214,178,109,.18);
                box-shadow:0 20px 44px rgba(2,6,23,.24);
            }
            body.luxury .luxury-nav .navbar-nav{
                align-items:stretch !important;
                gap:8px !important;
            }
            body.luxury .luxury-nav .nav-link{
                justify-content:flex-start;
                width:100%;
                min-height:46px;
                padding:12px 14px !important;
                border-radius:16px;
            }
            body.luxury .luxury-nav .nav-icon-link{
                min-height:46px;
                justify-content:center;
            }
            body.luxury .luxury-cta{
                justify-content:center;
            }
            body.luxury .hero-hotel{
                border-radius:0 0 28px 28px;
            }
            body.luxury .home-section-head{
                align-items:flex-start;
                gap:10px;
            }
            body.luxury .hero-content{
                min-height:calc(100svh - 24px);
                padding-top:94px;
                padding-bottom:26px;
                align-items:flex-end;
            }
            body.luxury .hero-main-copy{
                margin-bottom:16px;
            }
            body.luxury .hero-hotel h5{
                font-size:.74rem;
                letter-spacing:.16em;
                margin-bottom:10px;
            }
            body.luxury .hero-hotel h1{
                font-size:clamp(2rem, 8vw, 2.7rem);
                line-height:1.06;
                margin-bottom:10px;
            }
            body.luxury .hero-main-copy p{
                font-size:.95rem !important;
                line-height:1.65 !important;
                margin-top:0 !important;
            }
            body.luxury .feature{
                min-height:52px;
                padding:10px 12px;
                border-radius:14px;
                font-size:.84rem;
                display:flex;
                align-items:center;
            }
            body.luxury .booking-box{
                border-radius:24px;
                padding:18px 16px;
                box-shadow:0 20px 42px rgba(0,0,0,.34);
            }
            body.luxury .booking-box h4{
                font-size:1.12rem;
            }
            body.luxury .booking-box p.small{
                font-size:.82rem;
                line-height:1.55;
            }
            body.luxury .booking-box .form-label{
                font-size:.8rem;
                margin-bottom:6px;
            }
            body.luxury .booking-box .form-control,
            body.luxury .booking-box .form-select{
                min-height:48px;
                padding:11px 14px;
                border-radius:15px;
                font-size:.92rem;
            }
            body.luxury .booking-box textarea.form-control{
                min-height:88px;
            }
            body.luxury .booking-box .btn{
                min-height:48px;
                border-radius:15px;
                font-size:.92rem;
            }
            body.luxury .enhanced-card{
                border-radius:24px !important;
            }
            body.luxury #tools .card{
                padding:18px 14px !important;
            }
            body.luxury .home-section-copy{
                max-width:34ch;
            }
            body.luxury .home-section-link{
                padding:9px 16px;
                font-size:.82rem;
            }
            body.luxury #experiences,
            body.luxury .dest-section,
            body.luxury #tours,
            body.luxury #reviews,
            body.luxury #places,
            body.luxury #support{
                scroll-margin-top:92px;
            }
            body.luxury h2.fw-bold{
                font-size:clamp(1.75rem, 7vw, 2.25rem);
                line-height:1.08;
            }
            body.luxury .exp-grid{
                display:grid;
                grid-template-columns:minmax(0, 1.2fr) minmax(0, .8fr);
                grid-template-rows:150px 150px;
                gap:10px;
            }
            body.luxury .exp-card,
            body.luxury .exp-card--large,
            body.luxury .exp-card--small{
                min-height:0;
                border-radius:20px;
            }
            body.luxury .exp-card--large{
                grid-row:1 / 3;
            }
            body.luxury .exp-card-overlay,
            body.luxury .exp-card--small .exp-card-overlay{
                padding:14px;
            }
            body.luxury .exp-title,
            body.luxury .exp-card--small .exp-title{
                font-size:1.02rem;
                line-height:1.1;
            }
            body.luxury .exp-desc,
            body.luxury .exp-card--small .exp-desc{
                display:block;
                font-size:.72rem;
                line-height:1.4;
                margin-bottom:8px;
            }
            body.luxury .exp-tag{
                font-size:.58rem;
                letter-spacing:.1em;
                margin-bottom:4px;
            }
            body.luxury .exp-badge{
                top:12px;
                left:12px;
                font-size:.6rem;
                padding:4px 9px;
            }
            body.luxury .exp-cta{
                padding:6px 12px;
                font-size:.72rem;
            }
            body.luxury .mobile-exp-hint{
                display:none;
            }
            body.luxury .dest-see-all-btn{
                padding:8px 16px;
                font-size:.82rem;
            }
            body.luxury .dest-scroll-track{
                gap:14px;
                padding-bottom:8px;
            }
            body.luxury .dest-card{
                flex:0 0 70vw;
                border-radius:18px;
            }
            body.luxury .dest-card-img{
                height:190px;
            }
            body.luxury .dest-card-body{
                padding:12px 14px;
            }
            body.luxury .reviews-mobile-track{
                flex-wrap:nowrap;
                overflow-x:auto;
                overflow-y:hidden;
                scroll-snap-type:x proximity;
                -webkit-overflow-scrolling:touch;
                padding-bottom:6px;
                margin-right:-12px;
            }
            body.luxury .reviews-mobile-track::-webkit-scrollbar{
                display:none;
            }
            body.luxury .rv-mobile-item{
                flex:0 0 84%;
                max-width:84%;
                scroll-snap-align:start;
            }
            body.luxury .rv-card{
                border-radius:20px;
                padding:20px 18px 18px;
            }
            body.luxury .rv-quote{
                font-size:3.4rem;
                margin-bottom:4px;
            }
            body.luxury .rv-text{
                font-size:.9rem;
                line-height:1.6;
                margin-bottom:12px;
            }
            body.luxury .rv-stars{
                margin-bottom:14px;
            }
            body.luxury .rv-footer{
                gap:10px;
                padding-top:14px;
            }
            body.luxury .featured-places-grid{
                display:grid;
                grid-template-columns:repeat(2, minmax(0, 1fr));
                gap:12px;
                margin-bottom:18px;
                padding-bottom:0;
            }
            body.luxury .featured-place-card,
            body.luxury .featured-place-card:nth-child(1),
            body.luxury .featured-place-card:nth-child(2),
            body.luxury .featured-place-card:nth-child(3),
            body.luxury .featured-place-card:nth-child(4),
            body.luxury .featured-place-card:nth-child(5),
            body.luxury .featured-place-card:nth-child(6){
                min-height:180px;
                grid-row:auto;
                grid-column:auto;
                border-radius:18px;
            }
            body.luxury .featured-place-card img{
                min-height:180px;
            }
            body.luxury .featured-place-card .featured-place-overlay{
                padding:12px;
            }
            body.luxury .featured-place-badge{
                left:12px;
                top:12px;
                padding:5px 9px;
                font-size:.6rem;
            }
            body.luxury .featured-place-name{
                font-size:.98rem;
                line-height:1.18;
            }
            body.luxury .featured-place-sub{
                font-size:.7rem;
                line-height:1.35;
            }
            body.luxury .mobile-places-hint{
                display:none;
            }
            body.luxury .benefit-bar{
                grid-template-columns:1fr;
                gap:12px;
                margin-top:28px !important;
                margin-bottom:28px !important;
            }
            body.luxury .benefit-item{
                min-height:0;
                padding:16px 15px;
                border-radius:18px;
            }
            body.luxury .benefit-icon{
                width:46px;
                height:46px;
                border-radius:14px;
            }
            body.luxury footer.footer{
                margin-top:42px;
                padding:36px 0 26px;
            }
            body.luxury footer.footer .container{
                padding-left:20px;
                padding-right:20px;
            }
            body.luxury footer.footer h6{
                margin-bottom:10px !important;
            }
            body.luxury .chatbot-panel{
                width:min(100vw - 20px, 380px);
                max-height:min(72vh, 560px);
                border-radius:24px;
            }
        }
        @media (max-width: 576px){
            body.luxury .hero-content{ padding-top: 88px; padding-bottom: 18px; }
            body.luxury .booking-box{ transform: none; padding: 18px 14px; max-width: none; }
            body.luxury .luxury-float{ margin-top: 14px !important; }
            body.luxury .luxury-scroll{ gap:16px !important; }

            /* Keep the quick-tools tabs readable on very small screens. */
            body.luxury .multiTabMenu-bar{
                justify-content:flex-start !important;
                flex-wrap:nowrap;
                overflow-x:auto;
                overflow-y:hidden;
                -webkit-overflow-scrolling:touch;
                scrollbar-width:none;
                gap:.4rem !important;
                padding:.45rem !important;
                border-radius:20px;
            }
            body.luxury .multiTabMenu-bar::-webkit-scrollbar{
                display:none;
            }
            body.luxury .multiTabMenu-item{
                flex:0 0 auto;
                width:auto !important;
                min-width:112px;
                padding:.52rem .78rem !important;
                font-size:.84rem;
                white-space:nowrap;
            }
            body.luxury .home-tour-card{
                min-width:86vw;
                max-width:86vw;
                width:86vw;
            }
            body.luxury .exp-grid{
                grid-template-columns:minmax(0, 1.1fr) minmax(0, .9fr);
                grid-template-rows:136px 136px;
                gap:8px;
            }
            body.luxury .exp-card,
            body.luxury .exp-card--large,
            body.luxury .exp-card--small{
                border-radius:18px;
            }
            body.luxury .mobile-home-shortcuts{
                gap:10px;
            }
            body.luxury .mobile-shortcut-card{
                padding:13px 12px;
                border-radius:18px;
            }
            body.luxury .mobile-shortcut-icon{
                width:36px;
                height:36px;
                flex-basis:36px;
                border-radius:12px;
            }
            body.luxury .dest-card{
                flex-basis:78vw;
            }
            body.luxury .dest-card-img{
                height:178px;
            }
            body.luxury .rv-mobile-item{
                flex-basis:88%;
                max-width:88%;
            }
            body.luxury .featured-place-card,
            body.luxury .featured-place-card:nth-child(1),
            body.luxury .featured-place-card:nth-child(2),
            body.luxury .featured-place-card:nth-child(3),
            body.luxury .featured-place-card:nth-child(4),
            body.luxury .featured-place-card:nth-child(5),
            body.luxury .featured-place-card:nth-child(6){
                min-height:165px;
            }
            body.luxury .featured-place-card img{
                min-height:165px;
            }
            body.luxury .home-tour-media{
                height:220px;
            }
            body.luxury .home-tour-body{
                padding:20px;
            }
            body.luxury .home-tour-body h3{
                font-size:1rem;
            }
            body.luxury .home-tour-desc{
                font-size:.84rem;
                min-height:0;
            }
            body.luxury .home-tour-meta div{
                font-size:.8rem;
            }
            body.luxury .home-tour-footer{
                align-items:flex-start;
                flex-direction:column;
            }
            body.luxury .home-tour-actions{
                width:100%;
            }
            body.luxury .home-tour-actions a{
                flex:1;
            }
            body.luxury .customer-chatbot {
                right: 14px;
                bottom: 14px;
            }
            body.luxury .chatbot-toggle{
                width:76px;
                height:76px;
            }
            body.luxury .chatbot-panel{
                right:-2px;
                width:min(100vw - 16px, 360px);
            }
        }

        @media (prefers-reduced-motion: reduce){
            html{ scroll-behavior:auto; }
            body.luxury *,
            body.luxury *::before,
            body.luxury *::after{
                animation:none !important;
                transition:none !important;
            }
            body.luxury .js-reveal{
                opacity:1;
                transform:none;
            }
        }
    </style>

</main>

    <div class="customer-chatbot" id="customerChatbot">
        <div class="chatbot-panel" role="dialog" aria-modal="false" aria-labelledby="chatbotTitle">
            <div class="chatbot-head">
                <div>
                    <h3 class="chatbot-title" id="chatbotTitle">Trợ lý DuLichPro</h3>
                    <p class="chatbot-subtitle">Gợi ý nhanh tour, thanh toán và hỗ trợ.</p>
                </div>
                <button type="button" class="chatbot-close" id="chatbotClose" aria-label="Đóng chatbot">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages" aria-live="polite">
                <div class="chat-msg bot">Xin chào, mình có thể hỗ trợ bạn tìm tour, xem booking, hóa đơn hoặc gửi yêu cầu hỗ trợ.</div>
            </div>
            <div class="chatbot-suggestions" aria-label="Câu hỏi nhanh">
                <button type="button" class="chat-suggestion" data-chat-question="Tôi muốn tìm tour">Tìm tour</button>
                <button type="button" class="chat-suggestion" data-chat-question="Tôi muốn xem tour đã đặt">Tour đã đặt</button>
                <button type="button" class="chat-suggestion" data-chat-question="Tôi muốn thanh toán">Thanh toán</button>
                <button type="button" class="chat-suggestion" data-chat-question="Tôi cần hỗ trợ">Hỗ trợ</button>
            </div>
            <form class="chatbot-form" id="chatbotForm">
                <input type="text" id="chatbotInput" autocomplete="off" placeholder="Nhập câu hỏi của bạn...">
                <button type="submit" aria-label="Gửi câu hỏi"><i class="bi bi-send-fill"></i></button>
            </form>
        </div>
        <button type="button" class="chatbot-toggle" id="chatbotToggle" aria-label="Mở chatbot hỗ trợ">
            <i class="bi bi-chat-dots-fill"></i>
        </button>
    </div>

    <footer class="footer" id="support">
        <div class="container">
            <div class="row gy-4 text-start">
                <div class="col-md-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-star-fill" style="color:#d6b26d; font-size:1.3rem;"></i>
                        <span style="font-family:'Playfair Display',serif; font-size:1.35rem; font-weight:700; color:#fff; letter-spacing:.5px;">DuLichPro</span>
                    </div>
                    <p style="color:rgba(255,255,255,.65); font-size:.92rem; line-height:1.7; margin:0;">Hành trình đẳng cấp &mdash; trải nghiệm đích thực. Chúng tôi đồng hành cùng bạn trên mọi nẻo đường.</p>
                </div>
                <div class="col-md-4">
                    <h6 style="color:#d6b26d; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:14px;">Liên kết nhanh</h6>
                    <ul class="list-unstyled mb-0" style="display:flex; flex-direction:column; gap:8px;">
                        <li><a href="#home"><i class="bi bi-chevron-right me-1" style="font-size:.75rem;"></i>Trang chủ</a></li>
                        <li><a href="index.php?act=khachHang/danhSachTour"><i class="bi bi-chevron-right me-1" style="font-size:.75rem;"></i>Tour nổi bật</a></li>
                        <li><a href="#reviews"><i class="bi bi-chevron-right me-1" style="font-size:.75rem;"></i>Đánh giá</a></li>
                        <li><a href="#"><i class="bi bi-chevron-right me-1" style="font-size:.75rem;"></i>Chính sách bảo mật</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h6 style="color:#d6b26d; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:14px;">Liên hệ hỗ trợ</h6>
                    <ul class="list-unstyled mb-0" style="display:flex; flex-direction:column; gap:10px;">
                        <li style="color:rgba(255,255,255,.72); font-size:.92rem;"><i class="bi bi-telephone-fill me-2" style="color:#d6b26d;"></i><a href="tel:0346858035">0346 858 035</a></li>
                        <li style="color:rgba(255,255,255,.72); font-size:.92rem;"><i class="bi bi-facebook me-2" style="color:#d6b26d;"></i><a href="https://www.facebook.com/quan.le.703104" target="_blank" rel="noopener">Trang Facebook</a></li>
                        <li style="color:rgba(255,255,255,.72); font-size:.92rem;"><i class="bi bi-clock-fill me-2" style="color:#d6b26d;"></i>Hỗ trợ 7:00 &ndash; 22:00 hàng ngày</li>
                    </ul>
                </div>
            </div>
            <hr style="border-color:rgba(255,255,255,.12); margin:28px 0 18px;">
            <p class="mb-0 text-center" style="color:rgba(255,255,255,.45); font-size:.84rem;">&copy; 2026 DuLichPro &mdash; Bản quyền thuộc về DuLichPro. Thiết kế với <i class="bi bi-heart-fill" style="color:#d6b26d;"></i> tại Việt Nam.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var myCarousel = document.querySelector('#bannerCarousel');
        if (myCarousel) {
            var carousel = new bootstrap.Carousel(myCarousel, {
                interval: 1800,
                ride: 'carousel'
            });
        }

        // Make navbar feel premium on scroll.
        var nav = document.querySelector('.luxury-nav');
        if (nav) {
            var onScroll = function() {
                if (window.scrollY > 20) nav.classList.add('is-scrolled');
                else nav.classList.remove('is-scrolled');
            };
            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });
        }

        var navbarCollapseEl = document.getElementById('navbarNav');
        if (navbarCollapseEl && window.bootstrap) {
            var collapseInstance = bootstrap.Collapse.getOrCreateInstance(navbarCollapseEl, { toggle: false });
            Array.prototype.slice.call(navbarCollapseEl.querySelectorAll('a[href]')).forEach(function (link) {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992 && navbarCollapseEl.classList.contains('show')) {
                        collapseInstance.hide();
                    }
                });
            });
        }

        // Auto-hide flash messages after 3 seconds.
        var flashWrap = document.querySelector('.js-flash-wrap');
        if (flashWrap) {
            window.setTimeout(function () {
                flashWrap.style.transition = 'opacity .25s ease';
                flashWrap.style.opacity = '0';
                window.setTimeout(function () {
                    if (flashWrap && flashWrap.parentNode) {
                        flashWrap.parentNode.removeChild(flashWrap);
                    }
                }, 260);
            }, 3000);
        }

        var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var revealItems = document.querySelectorAll('#tools, #experiences, #tours, #reviews, #places, #support, .home-tour-card, .review-card, .featured-place-card, .special-offer-box');
        revealItems.forEach(function (item, index) {
            item.classList.add('js-reveal');
            item.style.transitionDelay = Math.min(index % 6, 5) * 70 + 'ms';
        });

        if (reduceMotion || !('IntersectionObserver' in window)) {
            revealItems.forEach(function (item) {
                item.classList.add('is-visible');
                item.style.transitionDelay = '';
            });
        } else {
            var revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.14, rootMargin: '0px 0px -8% 0px' });

            revealItems.forEach(function (item) {
                revealObserver.observe(item);
            });
        }

        var sectionLinks = Array.prototype.slice.call(document.querySelectorAll('.luxury-nav .nav-link[href^="#"]'));
        var sections = sectionLinks
            .map(function (link) {
                var target = document.querySelector(link.getAttribute('href'));
                return target ? { link: link, target: target } : null;
            })
            .filter(Boolean);

        if (sections.length) {
            var setActiveSectionLink = function (activeLink) {
                sectionLinks.forEach(function (link) {
                    link.classList.toggle('active', link === activeLink);
                });
            };

            var updateActiveSectionLink = function () {
                var triggerLine = 120;
                var current = sections[0];

                sections.forEach(function (item) {
                    if (item.target.getBoundingClientRect().top <= triggerLine) {
                        current = item;
                    }
                });

                setActiveSectionLink(current.link);
            };

            updateActiveSectionLink();
            window.addEventListener('scroll', updateActiveSectionLink, { passive: true });
            window.addEventListener('resize', updateActiveSectionLink);
            window.addEventListener('hashchange', updateActiveSectionLink);
        }

        var chatbot = document.getElementById('customerChatbot');
        var chatbotToggle = document.getElementById('chatbotToggle');
        var chatbotClose = document.getElementById('chatbotClose');
        var chatbotForm = document.getElementById('chatbotForm');
        var chatbotInput = document.getElementById('chatbotInput');
        var chatbotMessages = document.getElementById('chatbotMessages');
        var quickTourRequestForm = document.getElementById('quickTourRequestForm');
        var quickTourRequestFeedback = document.getElementById('quickTourRequestFeedback');
        var customerNotificationBadge = document.getElementById('customerNotificationBadge');
        var customerNotificationTimerId = null;
        var customerVisiblePollingMs = 5000;
        var customerHiddenPollingMs = 20000;

        var chatLinks = {
            tours: 'index.php?act=khachHang/danhSachTour',
            bookings: 'index.php?act=khachHang/yeuCauTour',
            invoices: 'index.php?act=khachHang/hoaDon',
            notifications: 'index.php?act=khachHang/thongBao',
            support: 'index.php?act=khachHang/guiYeuCauHoTro',
            request: 'index.php?act=khachHang/dashboard#home'
        };

        function addChatMessage(text, sender) {
            if (!chatbotMessages) return;
            var msg = document.createElement('div');
            msg.className = 'chat-msg ' + sender;
            msg.innerHTML = text;
            chatbotMessages.appendChild(msg);
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }

        function normalizeChatText(value) {
            return (value || '')
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd')
                .replace(/Đ/g, 'D');
        }

        function detectTourType(normalized) {
            if (normalized.includes('quoc te') || normalized.includes('nuoc ngoai') || normalized.includes('oversea')) {
                return 'QuocTe';
            }
            if (normalized.includes('trong nuoc') || normalized.includes('noi dia') || normalized.includes('viet nam')) {
                return 'TrongNuoc';
            }
            return '';
        }

        function detectPriceRange(normalized) {
            if (normalized.match(/duoi\s*5|duoi\s*nam|<\s*5/)) return 'under5';
            if (normalized.match(/5\s*(den|-|toi)\s*10|nam\s*(den|-|toi)\s*muoi/)) return '5to10';
            if (normalized.match(/10\s*(den|-|toi)\s*20|muoi\s*(den|-|toi)\s*hai muoi/)) return '10to20';
            if (normalized.match(/tren\s*20|hon\s*20|>\s*20|hai muoi tro len/)) return 'over20';
            return '';
        }

        function detectDestination(question, normalized) {
            var knownDestinations = [
                ['da nang', 'Đà Nẵng'],
                ['hoi an', 'Hội An'],
                ['phu quoc', 'Phú Quốc'],
                ['nha trang', 'Nha Trang'],
                ['da lat', 'Đà Lạt'],
                ['sa pa', 'Sa Pa'],
                ['sapa', 'Sa Pa'],
                ['ha long', 'Hạ Long'],
                ['hue', 'Huế'],
                ['ha noi', 'Hà Nội'],
                ['sai gon', 'Sài Gòn'],
                ['nhat ban', 'Nhật Bản'],
                ['tokyo', 'Tokyo'],
                ['nagoya', 'Nagoya'],
                ['han quoc', 'Hàn Quốc'],
                ['thai lan', 'Thái Lan'],
                ['singapore', 'Singapore']
            ];
            for (var i = 0; i < knownDestinations.length; i++) {
                if (normalized.includes(knownDestinations[i][0])) {
                    return knownDestinations[i][1];
                }
            }

            var cleaned = question
                .replace(/tôi|toi|mình|minh|muốn|muon|cần|can|cho tôi|cho toi/gi, ' ')
                .replace(/tìm|tim|kiếm|kiem|gợi ý|goi y|tour|du lịch|du lich|đi|di|giá|gia|triệu|trieu/gi, ' ')
                .replace(/trong nước|trong nuoc|quốc tế|quoc te|nước ngoài|nuoc ngoai/gi, ' ')
                .replace(/dưới|duoi|trên|tren|hơn|hon|từ|tu|đến|den|tới|toi|\d+/gi, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            return cleaned.length >= 3 && cleaned.length <= 40 ? cleaned : '';
        }

        function buildTourSearchUrl(question) {
            var normalized = normalizeChatText(question);
            var params = new URLSearchParams();
            var destination = detectDestination(question, normalized);
            var tourType = detectTourType(normalized);
            var priceRange = detectPriceRange(normalized);

            params.set('act', 'khachHang/danhSachTour');
            if (destination) params.set('q', destination);
            if (tourType) params.set('loai_tour', tourType);
            if (priceRange) params.set('price_range', priceRange);
            if (normalized.includes('sap khoi hanh') || normalized.includes('gan nhat')) {
                params.set('sort', 'upcoming');
            }

            return 'index.php?' + params.toString();
        }

        function describeTourFilters(question) {
            var normalized = normalizeChatText(question);
            var parts = [];
            var destination = detectDestination(question, normalized);
            var tourType = detectTourType(normalized);
            var priceRange = detectPriceRange(normalized);

            if (destination) parts.push('điểm đến "' + destination + '"');
            if (tourType === 'QuocTe') parts.push('tour quốc tế');
            if (tourType === 'TrongNuoc') parts.push('tour trong nước');
            if (priceRange === 'under5') parts.push('giá dưới 5 triệu');
            if (priceRange === '5to10') parts.push('giá 5 - 10 triệu');
            if (priceRange === '10to20') parts.push('giá 10 - 20 triệu');
            if (priceRange === 'over20') parts.push('giá trên 20 triệu');

            return parts.length ? parts.join(', ') : 'nhu cầu của bạn';
        }

        function buildBotReply(question) {
            var normalized = normalizeChatText(question);
            if (normalized.includes('tour') || normalized.includes('tim') || normalized.includes('kham pha') || normalized.includes('du lich') || normalized.includes('di ')) {
                var tourSearchUrl = buildTourSearchUrl(question);
                return 'Mình đã hiểu ' + describeTourFilters(question) + '. Bạn bấm vào <a href="' + tourSearchUrl + '" style="color:#f7dfac;font-weight:900;">xem tour phù hợp</a> để mở trang Khám phá tour với bộ lọc sẵn.';
            }
            if (normalized.includes('booking') || normalized.includes('da dat') || normalized.includes('lich trinh')) {
                return 'Bạn vào <a href="' + chatLinks.bookings + '" style="color:#f7dfac;font-weight:900;">Tour đã đặt</a> để xem trạng thái booking, người tham gia và nhắc nhở khởi hành.';
            }
            if (normalized.includes('thanh toan') || normalized.includes('hoa don')) {
                return 'Bạn có thể kiểm tra <a href="' + chatLinks.invoices + '" style="color:#f7dfac;font-weight:900;">Hóa đơn & thanh toán</a>. Nếu vừa chuyển khoản, hãy copy đúng nội dung thanh toán để hệ thống đối soát nhanh hơn.';
            }
            if (normalized.includes('thong bao')) {
                return 'Bạn xem các cập nhật mới tại <a href="' + chatLinks.notifications + '" style="color:#f7dfac;font-weight:900;">Thông báo</a>.';
            }
            if (normalized.includes('ho tro') || normalized.includes('lien he')) {
                return 'Bạn có thể gửi yêu cầu qua <a href="' + chatLinks.support + '" style="color:#f7dfac;font-weight:900;">Trung tâm hỗ trợ</a> hoặc gọi hotline ở cuối trang.';
            }
            if (normalized.includes('yeu cau') || normalized.includes('thiet ke')) {
                return 'Nếu muốn thiết kế tour riêng, bạn điền form <a href="' + chatLinks.request + '" style="color:#f7dfac;font-weight:900;">Tour của tôi</a> ngay trên trang chủ.';
            }
            return 'Mình có thể hỗ trợ nhanh các mục: <a href="' + chatLinks.tours + '" style="color:#f7dfac;font-weight:900;">tìm tour</a>, <a href="' + chatLinks.bookings + '" style="color:#f7dfac;font-weight:900;">tour đã đặt</a>, <a href="' + chatLinks.invoices + '" style="color:#f7dfac;font-weight:900;">thanh toán</a> hoặc <a href="' + chatLinks.support + '" style="color:#f7dfac;font-weight:900;">hỗ trợ</a>.';
        }

        function handleChatQuestion(question) {
            var cleanQuestion = (question || '').trim();
            if (!cleanQuestion) return;
            addChatMessage(cleanQuestion.replace(/[<>&]/g, function (char) {
                return {'<': '&lt;', '>': '&gt;', '&': '&amp;'}[char];
            }), 'user');
            window.setTimeout(function () {
                addChatMessage(buildBotReply(cleanQuestion), 'bot');
            }, 250);
        }

        function renderCustomerNotificationBadge(count) {
            if (!customerNotificationBadge) return;
            var safeCount = Number.isFinite(count) ? Math.max(0, Math.floor(count)) : 0;
            if (safeCount > 0) {
                customerNotificationBadge.textContent = safeCount;
                customerNotificationBadge.style.display = 'inline-flex';
            } else {
                customerNotificationBadge.textContent = '0';
                customerNotificationBadge.style.display = 'none';
            }
        }

        async function fetchCustomerNotificationCounts() {
            try {
                var response = await fetch('index.php?act=khachHang/notificationCounts&_ts=' + Date.now(), {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!response.ok) return;
                var data = await response.json();
                if (!data || data.success !== true) return;
                renderCustomerNotificationBadge(Number(data.unread || 0));
            } catch (error) {
                // Bo qua loi mang tam thoi.
            }
        }

        function restartCustomerNotificationTimer() {
            if (customerNotificationTimerId) {
                window.clearInterval(customerNotificationTimerId);
                customerNotificationTimerId = null;
            }

            var intervalMs = document.hidden ? customerHiddenPollingMs : customerVisiblePollingMs;
            customerNotificationTimerId = window.setInterval(fetchCustomerNotificationCounts, intervalMs);
        }

        if (quickTourRequestForm) {
            quickTourRequestForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                var submitButton = quickTourRequestForm.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.dataset.originalText = submitButton.textContent || '';
                    submitButton.textContent = 'ĐANG GỬI...';
                }

                if (quickTourRequestFeedback) {
                    quickTourRequestFeedback.style.display = 'none';
                    quickTourRequestFeedback.textContent = '';
                    quickTourRequestFeedback.className = 'small mt-2';
                }

                try {
                    var response = await fetch(quickTourRequestForm.action, {
                        method: 'POST',
                        body: new FormData(quickTourRequestForm),
                        credentials: 'same-origin',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });

                    var data = await response.json();
                    if (data && data.success) {
                        if (quickTourRequestFeedback) {
                            quickTourRequestFeedback.style.display = 'block';
                            quickTourRequestFeedback.className = 'small mt-2 text-success';
                            quickTourRequestFeedback.textContent = data.message || 'Gửi yêu cầu thành công.';
                        }
                        quickTourRequestForm.reset();
                        fetchCustomerNotificationCounts();
                    } else if (quickTourRequestFeedback) {
                        quickTourRequestFeedback.style.display = 'block';
                        quickTourRequestFeedback.className = 'small mt-2 text-danger';
                        quickTourRequestFeedback.textContent = (data && data.message) ? data.message : 'Không thể gửi yêu cầu. Vui lòng thử lại.';
                    }
                } catch (error) {
                    if (quickTourRequestFeedback) {
                        quickTourRequestFeedback.style.display = 'block';
                        quickTourRequestFeedback.className = 'small mt-2 text-danger';
                        quickTourRequestFeedback.textContent = 'Lỗi kết nối. Vui lòng kiểm tra mạng và thử lại.';
                    }
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = submitButton.dataset.originalText || 'GỬI YÊU CẦU';
                    }
                }
            });
        }

        if (chatbot && chatbotToggle) {
            chatbotToggle.addEventListener('click', function () {
                chatbot.classList.toggle('is-open');
                if (chatbot.classList.contains('is-open') && chatbotInput) {
                    window.setTimeout(function () { chatbotInput.focus(); }, 120);
                }
            });
        }
        if (chatbotClose && chatbot) {
            chatbotClose.addEventListener('click', function () {
                chatbot.classList.remove('is-open');
            });
        }
        if (chatbotForm) {
            chatbotForm.addEventListener('submit', function (event) {
                event.preventDefault();
                handleChatQuestion(chatbotInput ? chatbotInput.value : '');
                if (chatbotInput) chatbotInput.value = '';
            });
        }
        document.querySelectorAll('[data-chat-question]').forEach(function (button) {
            button.addEventListener('click', function () {
                handleChatQuestion(button.getAttribute('data-chat-question') || '');
            });
        });

        fetchCustomerNotificationCounts();
        restartCustomerNotificationTimer();
        document.addEventListener('visibilitychange', function () {
            restartCustomerNotificationTimer();
            if (!document.hidden) {
                fetchCustomerNotificationCounts();
            }
        });
    });
    </script>
<?php if (function_exists('realtimeWebSocketEnabled') && realtimeWebSocketEnabled() && !empty($_SESSION['user_id'])): ?>
<?php
$_khWsToken = buildRealtimeAuthToken((int)$_SESSION['user_id'], 'KhachHang');
$_khWsUrl   = realtimeWebSocketPublicUrl() . '?token=' . rawurlencode($_khWsToken);
?>
<script>
(function() {
    var wsUrl = <?php echo json_encode($_khWsUrl, JSON_UNESCAPED_UNICODE); ?>;
    var ws = null;
    var wsActive = false;
    var reconnectTimer = null;

    function connect() {
        if (ws) return;
        ws = new WebSocket(wsUrl);
        ws.onopen = function() {
            wsActive = true;
            // When WS is active, slow down polling to reduce server load
            if (typeof customerNotificationTimerId !== 'undefined' && customerNotificationTimerId) {
                window.clearInterval(customerNotificationTimerId);
            }
        };
        ws.onmessage = function(e) {
            try {
                var packet = JSON.parse(e.data);
                if (packet.type === 'ping') {
                    ws.send(JSON.stringify({ type: 'pong', payload: { ts: packet.payload && packet.payload.ts } }));
                    return;
                }
                if (packet.type !== 'notification' || !packet.payload || packet.payload.success !== true) return;
                if (typeof renderCustomerNotificationBadge === 'function') {
                    renderCustomerNotificationBadge(Number(packet.payload.unread || 0));
                }
            } catch (err) {}
        };
        ws.onclose = function() {
            ws = null;
            wsActive = false;
            // Restore polling when WS disconnects
            if (typeof restartCustomerNotificationTimer === 'function') {
                restartCustomerNotificationTimer();
            }
            reconnectTimer = window.setTimeout(connect, 5000);
        };
        ws.onerror = function() { ws.close(); };
    }

    connect();
})();
</script>
<?php endif; ?>
</body>
</html>
