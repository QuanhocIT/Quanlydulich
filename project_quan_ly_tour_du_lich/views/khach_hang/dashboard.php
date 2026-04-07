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
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#tours"><i class="bi bi-stars"></i> Tour nổi bật</a></li>
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#reviews"><i class="bi bi-chat-dots"></i> Đánh giá</a></li>
                <li class="nav-item"><a class="nav-link px-3 fw-semibold" href="#support"><i class="bi bi-headset"></i> Hỗ trợ</a></li>

                <li class="nav-item">
                    <a class="nav-link px-3 fw-bold luxury-cta" href="index.php?act=khachHang/guiYeuCauTour">
                        <i class="bi bi-plus-circle"></i> Đặt tour theo yêu cầu
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
                <h5 class="text-warning fw-bold">WELCOME TO CALIFORNIA</h5>

                <h1 class="display-4 fw-bold">
                    Great location <br> even better service
                </h1>

                <p class="mt-3">
                    Enjoy the sun and the beaches of California with our friendly staff.
                    Whether you want a short weekend break or a long luxurious vacation.
                </p>

                <div class="row mt-4">
                    <div class="col-6 col-md-3"><div class="feature">⭐ Five-star quality</div></div>
                    <div class="col-6 col-md-3"><div class="feature">📍 Great location</div></div>
                    <div class="col-6 col-md-3"><div class="feature">🎁 Special offers</div></div>
                    <div class="col-6 col-md-3"><div class="feature">👨‍👩‍👧 Kids friendly</div></div>
                </div>
            </div>

            <!-- BOOKING FORM -->
            <div class="col-lg-5 offset-lg-1">
                <div class="booking-box text-white">

                    <h4 class="text-warning fw-bold mb-3">Đặt tour theo yêu cầu</h4>

                    <form method="POST" action="index.php?act=khachHang/guiYeuCauTour">
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
                </div>
            </div>

        </div>
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
    background: rgba(255,255,255,0.84);
    border: 1px solid rgba(15,23,42,0.08);
    border-radius: 999px !important;
    padding: 12px !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,0.92), 0 12px 30px rgba(15,23,42,0.08);
}

.multiTabMenu-item {
    min-width: 168px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.25s ease;
    border: 1px solid transparent;
    color: #17233c;
}

.multiTabMenu-item:hover {
    background: rgba(214,178,109,0.12);
    color: #0f2748;
    border-color: rgba(214,178,109,0.26);
    transform: translateY(-1px);
}

.multiTabMenu-item.active {
    background: linear-gradient(135deg, #15233b, #20365f) !important;
    color: #f7e5b6 !important;
    box-shadow: 0 14px 28px rgba(21,35,59,0.28);
    border-color: rgba(214,178,109,0.34);
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
            <!-- Section: Trải nghiệm cho mọi người (đặt bên ngoài, trên cùng) -->
            <div class="mt-5" id="experiences">
                <h2 class="fw-bold mb-4">Trải nghiệm cho mọi người</h2>
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="experience-card position-relative rounded-4 shadow-sm" style="height:320px;">
                            <img src="https://blog.ehl.edu/hs-fs/hubfs/1440x960-singapore-bay.jpg?width=1440&height=960&name=1440x960-singapore-bay.jpg" alt="Sing - Thái" class="w-100 h-100 object-fit-cover rounded-4">
                            <div class="experience-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 rounded-4" style="background: linear-gradient(120deg,rgba(0,123,255,0.7) 60%,rgba(0,0,0,0.2) 100%);">
                                <h3 class="fw-bold text-white mb-2">Chốt Gấp Kèo Sing - Thái</h3>
                                <div class="mb-2 text-white fs-5">Deal du lịch HOT nhất Singapore & Thái Lan</div>
                                <a href="#" class="btn btn-light rounded-pill px-4 fw-bold">Khám phá</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="experience-card position-relative rounded-4 shadow-sm" style="height:320px;">
                            <img src="https://images.unsplash.com/photo-1465101046530-73398c7f28ca?auto=format&fit=crop&w=800&q=80" alt="Càng Mua Càng Hời" class="w-100 h-100 object-fit-cover rounded-4">
                            <div class="experience-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 rounded-4" style="background: linear-gradient(120deg,rgba(255,165,0,0.7) 60%,rgba(0,0,0,0.2) 100%);">
                                <h3 class="fw-bold text-white mb-2">Càng Mua Càng Hời</h3>
                                <div class="mb-2 text-white fs-5">Ưu đãi hấp dẫn. Càng mua nhiều - càng thêm lợi.</div>
                                <a href="#" class="btn btn-light rounded-pill px-4 fw-bold">Khám phá</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="experience-card position-relative rounded-4 shadow-sm" style="height:320px;">
                            <img src="https://epacket.vn/wp-content/uploads/2023/09/Hoa-Ky1.jpg" alt="Zone Châu Âu - Hoa Kỳ" class="w-100 h-100 object-fit-cover rounded-4">
                            <div class="experience-overlay position-absolute top-0 start-0 w-100 h-100 d-flex flex-column justify-content-end p-4 rounded-4" style="background: linear-gradient(120deg,rgba(0,180,180,0.7) 60%,rgba(0,0,0,0.2) 100%);">
                                <h3 class="fw-bold text-white mb-2">Zone Châu Âu - Hoa Kỳ</h3>
                                <div class="mb-2 text-white fs-5">Gợi ý du lịch hàng đầu Châu Âu và Hoa Kỳ.</div>
                                <a href="#" class="btn btn-light rounded-pill px-4 fw-bold">Khám phá</a>
                            </div>
                        </div>
                    </div>
                </div>
                <style>
                    .experience-card {
                        transition: transform 0.2s, box-shadow 0.2s;
                        cursor: pointer;
                        height: 320px;
                        display: flex;
                        flex-direction: column;
                        justify-content: flex-end;
                    }
                    .experience-card:hover {
                        transform: translateY(-8px) scale(1.03);
                        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
                    }
                    .experience-overlay {
                        pointer-events: none;
                    }
                    .experience-overlay .btn {
                        pointer-events: auto;
                    }
                    @media (max-width: 768px) {
                        .experience-card { height: 180px; }
                        .experience-overlay { padding: 0.5rem; }
                        .experience-overlay h3 { font-size: 1.1rem; }
                        .experience-overlay .fs-5 { font-size: 0.95rem !important; }
                    }
                </style>
            </div>
                <!-- Section: Bạn muốn đi đâu chơi? (Demo tĩnh kiểu Klook) -->
                <div class="mt-5">
                    <h2 class="fw-bold mb-4 text-center">Bạn muốn đi đâu chơi?</h2>
                    <div class="container">
                        <div class="row justify-content-center g-4">
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://ik.imagekit.io/tvlk/blog/2021/11/kinh-nghiem-du-lich-thuong-hai-cover.jpg" alt="Thượng Hải" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">Thượng Hải</h5>
                                        <small class="text-light fs-6">225 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://cly.1cdn.vn/2022/02/15/thu-do-bang-coc.jpg" alt="Bangkok" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">Bangkok</h5>
                                        <small class="text-light fs-6">581 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://vietnamdiscovery.com/wp-content/uploads/2020/12/Golden-Bridge-Featured.jpg" alt="Đà Nẵng" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">Đà Nẵng</h5>
                                        <small class="text-light fs-6">146 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://tse3.mm.bing.net/th/id/OIP.1puCYdr07Y7nQ9_AhECDagHaLH?rs=1&pid=ImgDetMain&o=7&rm=3" alt="Hà Nội" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">Hà Nội</h5>
                                        <small class="text-light fs-6">154 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://st.ielts-fighter.com/src/ielts-fighter-image/2023/01/09/5e6dbb91-3dab-4500-9a28-b9599aa12949.png" alt="TP. Hồ Chí Minh" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">TP. Hồ Chí Minh</h5>
                                        <small class="text-light fs-6">240 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-4 col-6 d-flex justify-content-center">
                                <div class="destination-card position-relative rounded-4 shadow-sm w-100" style="height:260px; max-width:180px;">
                                    <img src="https://ik.imagekit.io/tvlk/blog/2022/12/du-lich-dai-bac-10.jpg?tr=dpr-2,w-675" alt="Đài Bắc" class="w-100 h-100 object-fit-cover rounded-4">
                                    <div class="destination-overlay position-absolute bottom-0 start-0 w-100 p-3 rounded-bottom-4" style="background: linear-gradient(180deg,rgba(0,0,0,0) 40%,rgba(0,0,0,0.7) 100%);">
                                        <h5 class="fw-bold text-white mb-1">Đài Bắc</h5>
                                        <small class="text-light fs-6">394 hoạt động</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <style>
                        .destination-card {
                            transition: transform 0.2s, box-shadow 0.2s;
                            cursor: pointer;
                            height: 260px;
                            display: flex;
                            flex-direction: column;
                            justify-content: flex-end;
                            max-width: 180px;
                        }
                        .destination-card:hover {
                            transform: translateY(-8px) scale(1.04);
                            box-shadow: 0 8px 32px rgba(0,0,0,0.18);
                        }
                        .destination-overlay {
                            pointer-events: none;
                        }
                        @media (max-width: 768px) {
                            .destination-card { height: 140px; max-width: 100px; }
                            .destination-overlay { padding: 0.5rem; }
                            .destination-overlay h5 { font-size: 0.95rem; }
                        }
                    </style>
                </div>
        <h2 class="mb-4 fw-bold" id="tours">Tour trong nước</h2>
        <?php if (!empty($tourTrongNuoc)): ?>
        <div class="d-flex flex-row flex-nowrap overflow-auto pb-2 luxury-scroll">
            <?php foreach ($tourTrongNuoc as $tour): ?>
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
        <h2 class="mb-4 fw-bold mt-5">Tour quốc tế</h2>
        <?php if (!empty($tourQuocTe)): ?>
        <div class="d-flex flex-row flex-nowrap overflow-auto pb-2 luxury-scroll">
            <?php foreach ($tourQuocTe as $tour): ?>
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
        <!-- Ưu đãi đặc biệt: 4 mục tĩnh đẹp -->
        <div class="special-offer-section d-flex justify-content-center align-items-stretch gap-2 my-5 flex-nowrap overflow-x-auto" style="width:100%;">
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#fffbe6"/><path d="M16 24l6 6 10-10" stroke="#ff9800" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#ff9800;">Giảm 10% cho khách mới</h3>
                <div class="offer-desc mb-2">Đăng ký tài khoản và đặt tour lần đầu để nhận ưu đãi ngay.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Áp dụng đến hết 31/12/2025.</div>
            </div>
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#e3f2fd"/><path d="M24 14v20M14 24h20" stroke="#2196f3" stroke-width="3.2" stroke-linecap="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#2196f3;">Tặng voucher 500.000đ</h3>
                <div class="offer-desc mb-2">Nhóm từ 5 người trở lên sẽ nhận voucher giảm giá khi đặt tour.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Không cộng dồn với ưu đãi khác.</div>
            </div>
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#e8f5e9"/><path d="M24 16a8 8 0 100 16 8 8 0 000-16zm0 0v8l5 3" stroke="#43a047" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#43a047;">Hoàn tiền 100% nếu hủy</h3>
                <div class="offer-desc mb-2">Hủy tour trước 7 ngày sẽ được hoàn tiền toàn bộ.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Xem chi tiết điều kiện hoàn tiền.</div>
            </div>
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#f3e5f5"/><path d="M24 16l8 8-8 8-8-8z" stroke="#8e24aa" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#8e24aa;">Tặng quà lưu niệm</h3>
                <div class="offer-desc mb-2">Nhận ngay quà tặng đặc biệt khi hoàn thành tour cùng Aventura.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Áp dụng cho mọi khách hàng, số lượng có hạn.</div>
            </div>
            <!-- New offer 5 -->
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#fffde7"/><path d="M24 18v12M18 24h12" stroke="#ffb300" stroke-width="3.2" stroke-linecap="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#ffb300;">Miễn phí tư vấn tour</h3>
                <div class="offer-desc mb-2">Đội ngũ chuyên gia hỗ trợ tư vấn miễn phí mọi tour.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Áp dụng cho mọi khách hàng.</div>
            </div>
            <!-- New offer 6 -->
            <div class="special-offer-box text-center p-4 flex-fill">
                <div class="offer-icon mb-3">
                    <svg width="48" height="48" fill="none"><circle cx="24" cy="24" r="24" fill="#e0f7fa"/><path d="M24 20l6 6-6 6-6-6z" stroke="#00bcd4" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
                <h3 class="fw-bold mb-2" style="color:#00bcd4;">Ưu đãi sinh nhật</h3>
                <div class="offer-desc mb-2">Khách hàng sinh nhật tháng này nhận ưu đãi đặc biệt.</div>
                <div class="offer-note text-muted mt-2" style="font-size: 0.98rem;">Vui lòng cung cấp thông tin sinh nhật.</div>
            </div>
        </div>
<style>
.special-offer-section {
    width: 100%;
    gap: 12px;
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: 8px;
}
.special-offer-box {
    background: linear-gradient(120deg, #fffbe6 60%, #ffe0b2 100%);
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(255,193,7,0.10);
    min-width: 200px;
    max-width: 220px;
    width: 100%;
    border: 2px solid #ffe082;
    transition: box-shadow 0.2s, transform 0.2s;
    margin-bottom: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.1rem 0.7rem;
}
.special-offer-box:nth-child(2) {
    background: linear-gradient(120deg, #e3f2fd 60%, #bbdefb 100%);
    border-color: #90caf9;
}
.special-offer-box:nth-child(3) {
    background: linear-gradient(120deg, #e8f5e9 60%, #c8e6c9 100%);
    border-color: #a5d6a7;
}
.special-offer-box:nth-child(4) {
    background: linear-gradient(120deg, #f3e5f5 60%, #e1bee7 100%);
    border-color: #ce93d8;
}
.special-offer-box:hover {
    box-shadow: 0 8px 32px rgba(212,175,55,0.18);
    transform: translateY(-3px) scale(1.01);
}
.offer-icon {
    display: flex;
    justify-content: center;
    align-items: center;
}
.offer-desc {
    font-size: 1.08rem;
    color: #b26a00;
}
.special-offer-box:nth-child(2) .offer-desc { color: #1565c0; }
.special-offer-box:nth-child(3) .offer-desc { color: #388e3c; }
.special-offer-box:nth-child(4) .offer-desc { color: #6a1b9a; }
@media (max-width: 1100px) {
    .special-offer-section { flex-direction: column; align-items: center; gap: 10px; }
    .special-offer-box { max-width: 98vw; min-width: 0; }
}
@media (max-width: 600px) {
    .special-offer-box { padding: 0.7rem; border-radius: 10px; min-width: 0; }
    .offer-desc { font-size: 0.95rem; }
}
</style>
        <div class="mt-5" id="reviews">
            <h2 class="fw-bold mb-4">Đánh giá khách hàng</h2>
            <div class="row g-4">
                <?php foreach ($danhGiaTot as $dg): ?>
                <div class="col-md-4">
                    <div class="card review-card">
                        <div class="card-body">
                            <p class="fst-italic">“<?php echo htmlspecialchars($dg['noi_dung'] ?? $dg['noi_dung'] ?? ''); ?>”</p>
                            <div class="d-flex align-items-center mt-3">
                                <img src="<?php echo htmlspecialchars($dg['anh'] ?? ($dg['anh_dai_dien'] ?? 'https://randomuser.me/api/portraits/men/1.jpg')); ?>" class="rounded-circle me-2" width="40" height="40">
                                <span class="fw-bold"><?php echo htmlspecialchars($dg['ten_khach_hang'] ?? $dg['ten'] ?? 'Ẩn danh'); ?></span>
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-info text-dark">Tiêu chí: <?php echo htmlspecialchars($dg['tieu_chi'] ?? $dg['loai_danh_gia'] ?? ''); ?></span>
                                <span class="badge bg-success ms-2">Đánh giá: <?php echo htmlspecialchars($dg['diem'] ?? $dg['diem'] ?? ''); ?>*</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Section: Địa danh nổi bật dạng grid -->
    <div class="container mt-5" id="places">
        <h2 class="fw-bold mb-4 text-center">Địa danh nổi bật</h2>
        <div class="featured-places-grid">
            <?php if (!empty($danhSachDiaDanh)): ?>
                <?php foreach ($danhSachDiaDanh as $diaDanh): ?>
                    <div class="featured-place-card">
                        <img src="<?php echo htmlspecialchars($diaDanh['hinh_anh']); ?>" alt="<?php echo htmlspecialchars($diaDanh['ten']); ?>">
                        <div class="featured-place-overlay">
                            <span class="featured-place-name"><?php echo htmlspecialchars($diaDanh['ten']); ?></span>
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
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
        transition: transform 0.18s, box-shadow 0.18s;
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
        filter: brightness(0.95);
        transition: filter 0.18s;
    }
    .featured-place-card:hover {
        transform: scale(1.03);
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    }
    .featured-place-card:hover img {
        filter: brightness(1);
    }
    .featured-place-card .featured-place-overlay {
        opacity: 0;
        transition: opacity 0.2s;
    }
    .featured-place-card:hover .featured-place-overlay {
        opacity: 1;
    }
    .featured-place-name {
        opacity: 0;
        transition: opacity 0.2s;
    }
    .featured-place-card:hover .featured-place-name {
        opacity: 1;
    }
    .featured-place-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        pointer-events: none;
    }

    .featured-place-name {
        color: #fff;
        font-size: 2rem;
        font-weight: bold;
        text-shadow: 0 2px 8px rgba(0,0,0,0.18);
        letter-spacing: 1px;
        text-transform: uppercase;
        padding: 0.5rem 1.2rem;
        border-radius: 12px;
        background: rgba(0,0,0,0.38);
        pointer-events: auto;
    }
    @media (max-width: 600px) {
        .featured-place-name { font-size: 1.1rem; padding: 0.3rem 0.7rem; }
        .featured-place-card { min-height: 120px; }
    }
    </style>

    <style id="luxury-theme">
        html{ scroll-behavior:smooth; }

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
        }
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

        body.luxury .luxury-scroll{
            gap:22px !important;
            padding-bottom:14px !important;
            scroll-snap-type:x mandatory;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior-x: contain;
            position:relative;
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

        @media (max-width: 992px){
            body.luxury .hero-hotel{ height: 720px; }
            body.luxury .hero-content{ padding-top: 96px; }
            body.luxury .hero-main-copy{ transform: translateY(-60px); }
            body.luxury .booking-box{ transform: translateY(-18px); max-width: 520px; margin: 0 auto; }
            body.luxury .featured-place-name{ font-size:1.1rem; }
        }
        @media (max-width: 576px){
            body.luxury .hero-hotel{ height: 760px; }
            body.luxury .booking-box{ transform: translateY(-12px); padding: 18px 14px; max-width: none; }
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
        }
    </style>

</main>
    
    <footer class="footer text-center" id="support">
        <div class="container">
            <p class="mb-2">&copy; 2025 DuLichPro. All rights reserved.</p>
            <a href="#" class="me-3">Chính sách bảo mật</a>
            <a href="#">Liên hệ hỗ trợ</a>
        </div>
        <!-- Section: Trải nghiệm cho mọi người -->

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
    });
    </script>
</body>
</html>


