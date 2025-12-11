<?php
$pageTitle = 'Chi tiết HDV - ' . htmlspecialchars($hdv['ho_ten'] ?? 'N/A');
$currentPage = 'nhanSu';
ob_start();
?>
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css' rel='stylesheet' />
<style>
        .stat-card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-left: 4px solid;
            border-radius: 8px;
            backdrop-filter: blur(10px);
            transition: all 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            background: rgba(45, 45, 45, 0.6);
        }
        .stat-card.primary { border-left-color: #0d6efd; }
        .stat-card.success { border-left-color: #198754; }
        .stat-card.warning { border-left-color: #ffc107; }
        .stat-card.info { border-left-color: #0dcaf0; }
        
        .rating-stars {
            color: #ffc107;
            font-size: 1.5rem;
        }
        
        .profile-header {
            background: rgba(102, 126, 234, 0.3);
            border: 1px solid rgba(102, 126, 234, 0.5);
            color: var(--text-light);
            padding: 2rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(45, 45, 45, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--accent-gold);
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        
        #calendar {
            max-width: 100%;
        }
        
        .timeline-item {
            border-left: 2px solid rgba(255, 255, 255, 0.2);
            padding-left: 1.5rem;
            padding-bottom: 1.5rem;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            width: 12px;
            height: 12px;
            background: #4da3ff;
            border-radius: 50%;
            position: absolute;
            left: -7px;
            top: 0;
        }
        
        .review-card {
            background: rgba(255, 193, 7, 0.1);
            border-left: 3px solid #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card {
            background: rgba(45, 45, 45, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: rgba(45, 45, 45, 0.7);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        .card-body {
            color: var(--text-light);
        }
        .table {
            color: var(--text-light);
        }
        .table th {
            background: rgba(45, 45, 45, 0.7);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .table td {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        .bg-info {
            background: rgba(0, 123, 255, 0.3) !important;
            color: #4da3ff !important;
        }
        .bg-warning {
            background: rgba(255, 193, 7, 0.3) !important;
            color: #ffc107 !important;
        }
        .bg-light {
            background: rgba(248, 249, 250, 0.1) !important;
            color: var(--text-light) !important;
        }
        .text-muted {
            color: var(--text-muted) !important;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
        }
        .btn-light {
            background: rgba(248, 249, 250, 0.2);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-light:hover {
            background: rgba(248, 249, 250, 0.3);
        }
        .btn-outline-light {
            background: transparent;
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .nav-tabs {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .nav-tabs .nav-link {
            color: var(--text-muted);
            border: 1px solid transparent;
        }
        .nav-tabs .nav-link.active {
            background: rgba(45, 45, 45, 0.5);
            border-color: rgba(255, 255, 255, 0.1) rgba(255, 255, 255, 0.1) transparent;
            color: var(--text-light);
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-warning {
            background: rgba(255, 193, 7, 0.2);
            border: 1px solid rgba(255, 193, 7, 0.5);
            color: #ffc107;
        }
    </style>

<div style="padding: 20px;">
        <?php if (!empty($hdv)): ?>
        
        <!-- Profile Header -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="profile-avatar">
                            <i class="bi bi-person-circle"></i>
                        </div>
                    </div>
                    <div class="col">
                        <h2 class="mb-1"><?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?></h2>
                        <p class="mb-2">
                            <span class="badge bg-light text-dark me-2">
                                <i class="bi bi-briefcase"></i> <?php echo htmlspecialchars($hdv['vai_tro'] ?? 'N/A'); ?>
                            </span>
                            <?php 
                            // Auto-classify HDV type
                            $loai_hdv = 'Nội địa';
                            $ngon_ngu = strtolower($hdv['ngon_ngu'] ?? '');
                            $kinh_nghiem = strtolower($hdv['kinh_nghiem'] ?? '');
                            
                            if (strpos($ngon_ngu, 'anh') !== false || strpos($ngon_ngu, 'nhật') !== false || 
                                strpos($ngon_ngu, 'hàn') !== false || strpos($ngon_ngu, 'trung') !== false) {
                                $loai_hdv = 'Quốc tế';
                            } elseif (strpos($kinh_nghiem, 'chuyên') !== false) {
                                $loai_hdv = 'Chuyên tuyến';
                            } elseif (strpos($kinh_nghiem, 'đoàn') !== false) {
                                $loai_hdv = 'Khách đoàn';
                            }
                            ?>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star"></i> <?php echo $loai_hdv; ?>
                            </span>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-envelope me-2"></i> <?php echo htmlspecialchars($hdv['email'] ?? 'N/A'); ?>
                            <i class="bi bi-phone ms-3 me-2"></i> <?php echo htmlspecialchars($hdv['so_dien_thoai'] ?? 'N/A'); ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="index.php?act=admin/nhanSu" class="btn btn-light">
                            <i class="bi bi-arrow-left"></i> Quay lại
                        </a>
                        <a href="index.php?act=admin/hdv_advanced" class="btn btn-outline-light">
                            <i class="bi bi-calendar-check"></i> Quản lý lịch
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Tổng tour đã dẫn</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $total_tours = is_array($lich_lam_viec) ? count($lich_lam_viec) : 0;
                                    echo $total_tours;
                                    ?>
                                </h3>
                            </div>
                            <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Đánh giá trung bình</h6>
                                <h3 class="mb-0">
                                    <?php 
                                    $avg_rating = 0;
                                    if (!empty($danh_gia_list)) {
                                        $sum = array_sum(array_column($danh_gia_list, 'diem'));
                                        $avg_rating = $sum / count($danh_gia_list);
                                    }
                                    echo number_format($avg_rating, 1);
                                    ?>
                                    <i class="bi bi-star-fill text-warning" style="font-size: 1.2rem;"></i>
                                </h3>
                            </div>
                            <i class="bi bi-star-fill text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Số đánh giá</h6>
                                <h3 class="mb-0">
                                    <?php echo is_array($danh_gia_list) ? count($danh_gia_list) : 0; ?>
                                </h3>
                            </div>
                            <i class="bi bi-chat-dots-fill text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Nhật ký</h6>
                                <h3 class="mb-0">
                                    <?php echo is_array($nhat_ky_list) ? count($nhat_ky_list) : 0; ?>
                                </h3>
                            </div>
                            <i class="bi bi-journal-text text-info" style="font-size: 2.5rem; opacity: 0.3;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-info">
                    <i class="bi bi-info-circle"></i> Thông tin
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-calendar">
                    <i class="bi bi-calendar"></i> Lịch làm việc
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reviews">
                    <i class="bi bi-star"></i> Đánh giá
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-diary">
                    <i class="bi bi-journal"></i> Nhật ký
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Tab: Info -->
            <div class="tab-pane fade show active" id="tab-info">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">
                                <i class="bi bi-person-vcard"></i> Thông tin cá nhân
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Họ tên:</th>
                                        <td><?php echo htmlspecialchars($hdv['ho_ten'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo htmlspecialchars($hdv['email'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Điện thoại:</th>
                                        <td><?php echo htmlspecialchars($hdv['so_dien_thoai'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Vai trò:</th>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($hdv['vai_tro'] ?? 'N/A'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <th>Loại HDV:</th>
                                        <td><span class="badge bg-warning text-dark"><?php echo $loai_hdv; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-success text-white">
                                <i class="bi bi-mortarboard"></i> Kỹ năng & Chứng chỉ
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="40%">Ngôn ngữ:</th>
                                        <td><?php echo htmlspecialchars($hdv['ngon_ngu'] ?? 'N/A'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kinh nghiệm:</th>
                                        <td><?php echo nl2br(htmlspecialchars($hdv['kinh_nghiem'] ?? 'N/A')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Chứng chỉ:</th>
                                        <td><?php echo nl2br(htmlspecialchars($hdv['chung_chi'] ?? 'N/A')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Sức khỏe:</th>
                                        <td><?php echo htmlspecialchars($hdv['suc_khoe'] ?? 'N/A'); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Calendar -->
            <div class="tab-pane fade" id="tab-calendar">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-calendar3"></i> Lịch làm việc
                    </div>
                    <div class="card-body">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>

            <!-- Tab: Reviews -->
            <div class="tab-pane fade" id="tab-reviews">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-star-fill"></i> Đánh giá từ khách hàng
                    </div>
                    <div class="card-body">
                        <?php if (!empty($danh_gia_list)): ?>
                        <div class="row">
                            <?php foreach ($danh_gia_list as $dg): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card review-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($dg['ten_tour'] ?? 'Tour'); ?></h6>
                                                <small class="text-muted">
                                                    <?php 
                                                    if (!empty($dg['ngay_danh_gia'])) {
                                                        $date = new DateTime($dg['ngay_danh_gia']);
                                                        echo $date->format('d/m/Y H:i');
                                                    }
                                                    ?>
                                                </small>
                                            </div>
                                            <div class="rating-stars">
                                                <?php 
                                                $stars = intval($dg['diem'] ?? 0);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $stars ? '★' : '☆';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($dg['noi_dung'] ?? '')); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-4">Chưa có đánh giá nào</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Diary -->
            <div class="tab-pane fade" id="tab-diary">
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-journal-text"></i> Nhật ký hoạt động
                    </div>
                    <div class="card-body">
                        <?php if (!empty($nhat_ky_list)): ?>
                        <div class="timeline">
                            <?php foreach ($nhat_ky_list as $nk): ?>
                            <div class="timeline-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($nk['ten_tour'] ?? 'Tour'); ?></h6>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i>
                                            <?php 
                                            if (!empty($nk['ngay_ghi'])) {
                                                $date = new DateTime($nk['ngay_ghi']);
                                                echo $date->format('d/m/Y H:i');
                                            }
                                            ?>
                                        </small>
                                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($nk['noi_dung'] ?? '')); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="text-muted text-center py-4">Chưa có nhật ký nào</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle"></i> Không tìm thấy thông tin HDV
        </div>
        <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js'></script>
    <script>
        // Initialize FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'vi',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth'
                },
                buttonText: {
                    today: 'Hôm nay',
                    month: 'Tháng',
                    week: 'Tuần',
                    list: 'Danh sách'
                },
                events: function(info, successCallback, failureCallback) {
                    fetch('index.php?act=admin/hdv_get_schedule&hdv_id=<?php echo $hdv["nhan_su_id"] ?? 0; ?>')
                        .then(response => response.json())
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error loading events:', error);
                            failureCallback(error);
                        });
                },
                eventClick: function(info) {
                    alert('Tour: ' + info.event.title + '\n' +
                          'Từ: ' + info.event.start.toLocaleDateString('vi-VN') + '\n' +
                          'Đến: ' + (info.event.end ? info.event.end.toLocaleDateString('vi-VN') : 'N/A'));
                }
            });
            
            calendar.render();
        });
    </script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/aventura.php';
?>
