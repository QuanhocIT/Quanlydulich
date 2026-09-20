<template>
  <div class="vue-admin-schedule-manage">
    <!-- FLASH TOAST NOTIFICATION -->
    <transition name="fade">
      <div v-if="toast.visible" class="toast-popup" :class="toast.type">
        <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
        <span>{{ toast.message }}</span>
        <button @click="toast.visible = false" class="toast-close">&times;</button>
      </div>
    </transition>

    <!-- PAGE HEADER HERO BANNER -->
    <header class="page-header-hero">
      <div class="hero-bg"></div>
      <div class="hero-content">
        <div class="hero-left">
          <div class="badge-tag">
            <i class="bi bi-compass-fill"></i>
            <span>AVENTURA • TOUR MANAGEMENT</span>
          </div>
          <h1 class="hero-title">Quản lý lịch khởi hành</h1>
          <p class="hero-subtitle">
            Theo dõi, quản lý và sắp xếp lịch khởi hành tour một cách hiệu quả.
            <span v-if="selectedTourId" class="badge-tour-filter ms-2">
              Đang lọc Tour #{{ selectedTourId }}
              <button type="button" class="btn-clear-tour" @click="clearTourFilter" title="Bỏ lọc theo tour">&times;</button>
            </span>
          </p>
        </div>
        <div class="hero-right">
          <div class="current-time-display">
            <i class="bi bi-clock-history text-cyan me-2"></i>
            <span>{{ currentTimeString || 'Đang cập nhật giờ...' }}</span>
          </div>
          <a href="index.php?act=lichKhoiHanh/create" class="btn-hero-create">
            <i class="bi bi-plus-lg"></i>
            <span>Tạo lịch khởi hành</span>
          </a>
        </div>
      </div>
    </header>

    <!-- 5 STATS CARDS -->
    <section class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon-box icon-blue">
          <i class="bi bi-calendar-event"></i>
        </div>
        <div class="stat-content">
          <span class="stat-label">Tổng số lịch</span>
          <strong class="stat-num">{{ stats.total }}</strong>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i> <span>+0% so với tháng trước</span>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box icon-green">
          <i class="bi bi-airplane-engines-fill"></i>
        </div>
        <div class="stat-content">
          <span class="stat-label">Sắp khởi hành</span>
          <strong class="stat-num">{{ stats.upcoming }}</strong>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i> <span>+0% so với tháng trước</span>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box icon-purple">
          <i class="bi bi-check-circle-fill"></i>
        </div>
        <div class="stat-content">
          <span class="stat-label">Đang chạy</span>
          <strong class="stat-num">{{ stats.ongoing }}</strong>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i> <span>+0% so với tháng trước</span>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box icon-orange">
          <i class="bi bi-clock-fill"></i>
        </div>
        <div class="stat-content">
          <span class="stat-label">Đã hoàn thành</span>
          <strong class="stat-num">{{ stats.completed }}</strong>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i> <span>+0% so với tháng trước</span>
          </div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon-box icon-red">
          <i class="bi bi-x-circle-fill"></i>
        </div>
        <div class="stat-content">
          <span class="stat-label">Chờ phân bổ</span>
          <strong class="stat-num">{{ stats.pending }}</strong>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i> <span>+0% so với tháng trước</span>
          </div>
        </div>
      </div>
    </section>

    <!-- FILTER TOOLBAR -->
    <section class="filter-section">
      <div class="filter-header">
        <i class="bi bi-funnel-fill text-cyan me-2"></i>Tìm kiếm & Lọc
      </div>
      <div class="filter-grid-inline">
        <!-- Search Keyword -->
        <div class="filter-col search-col">
          <div class="input-icon-wrap">
            <i class="bi bi-search icon-left"></i>
            <input 
              v-model="searchQuery" 
              @input="onSearchInput"
              type="text" 
              class="form-input-custom" 
              placeholder="Tìm theo tên tour, mã tour, điểm đến..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Tour Dropdown Filter -->
        <div class="filter-col">
          <div class="select-icon-wrap">
            <i class="bi bi-map icon-left"></i>
            <select v-model="selectedTourId" @change="onFilterChange" class="form-select-custom">
              <option value="">Tất cả tour</option>
              <option v-for="t in toursList" :key="t.tour_id" :value="t.tour_id">
                {{ t.ten_tour }}
              </option>
            </select>
          </div>
        </div>

        <!-- Guide Dropdown Filter -->
        <div class="filter-col">
          <div class="select-icon-wrap">
            <i class="bi bi-person-badge icon-left"></i>
            <select v-model="selectedHdvId" @change="onFilterChange" class="form-select-custom">
              <option value="">Tất cả hướng dẫn viên</option>
              <option value="unassigned">Chưa phân bổ HDV</option>
              <option v-for="h in hdvList" :key="h.nhan_su_id" :value="h.nhan_su_id">
                {{ h.ho_ten }}
              </option>
            </select>
          </div>
        </div>

        <!-- Status Filter -->
        <div class="filter-col">
          <div class="select-icon-wrap">
            <i class="bi bi-shield-check icon-left"></i>
            <select v-model="selectedStatus" @change="onFilterChange" class="form-select-custom">
              <option value="">Tất cả trạng thái</option>
              <option value="ChoPhanBo">Đang chờ phân bổ</option>
              <option value="SapKhoiHanh">Sắp khởi hành</option>
              <option value="DangChay">Đang chạy</option>
              <option value="HoanThanh">Hoàn thành</option>
              <option value="Huy">Đã hủy</option>
            </select>
          </div>
        </div>

        <!-- Date To -->
        <div class="filter-col date-col">
          <div class="input-icon-wrap">
            <i class="bi bi-calendar-event icon-left"></i>
            <input 
              v-model="toDate" 
              @change="onFilterChange"
              type="date" 
              class="form-input-custom" 
            />
          </div>
        </div>

        <!-- Reset Button -->
        <div class="filter-col action-col">
          <button @click="resetFilters" class="btn-refresh" title="Làm mới bộ lọc">
            <i class="bi bi-arrow-clockwise me-1"></i> Làm mới
          </button>
        </div>
      </div>
    </section>

    <!-- SCHEDULES DATA TABLE -->
    <section class="schedule-section">
      <div class="section-meta-bar">
        <div class="meta-left">
          <span class="live-dot-pulse"></span>
          Danh sách lịch khởi hành (<strong>{{ sortedAndFilteredSchedules.length }}</strong> kết quả)
          <span v-if="isLoading" class="loading-tag ms-3">
            <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
          </span>
        </div>
        <div class="meta-right">
          <label class="per-page-label">Hiển thị</label>
          <select v-model="pageSize" class="per-page-select">
            <option :value="10">10 bản ghi</option>
            <option :value="20">20 bản ghi</option>
            <option :value="50">50 bản ghi</option>
          </select>
          <div class="view-toggles ms-3">
            <button class="btn-view-toggle"><i class="bi bi-grid-fill"></i></button>
            <button class="btn-view-toggle active"><i class="bi bi-list-ul"></i></button>
          </div>
        </div>
      </div>

      <div class="table-responsive glass-table-wrap">
        <table class="table-glass">
          <thead>
            <tr>
              <th style="width: 50px">#</th>
              <th style="min-width: 250px">Tour</th>
              <th>Mã lịch</th>
              <th>Ngày khởi hành</th>
              <th>Ngày về</th>
              <th>Hướng dẫn viên</th>
              <th>Trạng thái</th>
              <th>Số khách</th>
              <th style="text-align: right; padding-right: 20px;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedSchedules" :key="item.id" class="table-row-hover">
              <td class="text-secondary">{{ (currentPage - 1) * pageSize + index + 1 }}</td>
              <td>
                <div class="tour-info-cell">
                  <div class="tour-thumb">
                    <img :src="getTourThumbnail(item)" alt="Tour img" @error="handleImgError" />
                  </div>
                  <div class="tour-details">
                    <div class="tour-name">{{ item.ten_tour || 'Chưa cập nhật tên tour' }}</div>
                    <div class="tour-dest">
                      <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                      <span>{{ item.diem_tap_trung || 'Chưa có điểm tập trung' }}</span>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <span class="badge-code">#{{ item.id }}</span>
              </td>
              <td class="date-cell">
                <i class="bi bi-calendar2-check text-cyan me-1"></i> {{ formatDate(item.ngay_khoi_hanh) }}
              </td>
              <td class="date-cell">
                <i class="bi bi-calendar2-x text-orange me-1"></i> {{ formatDate(item.ngay_ket_thuc) }}
              </td>
              <td>
                <div v-if="item.ten_hdv" class="hdv-cell">
                  <div class="hdv-name">{{ item.ten_hdv }}</div>
                  <a href="#" class="hdv-phone" @click.prevent><i class="bi bi-telephone-fill me-1"></i>{{ item.dien_thoai_hdv || 'Đang cập nhật' }}</a>
                </div>
                <div v-else class="hdv-cell text-muted">
                  <span class="badge-unassigned">Chưa phân bổ</span>
                </div>
              </td>
              <td>
                <span class="status-badge" :class="getStatusClass(item)">
                  {{ getStatusText(item) }}
                </span>
              </td>
              <td>
                <div class="occupancy-cell">
                  <strong>{{ item.so_khach_da_dat || 0 }}</strong><span class="text-secondary">/{{ item.so_cho || 50 }}</span>
                </div>
              </td>
              <td class="actions-cell">
                <button @click="openPassengersModal(item)" class="action-btn btn-view" title="Khách Đoàn">
                  <i class="bi bi-eye"></i>
                </button>
                <button @click="openEditModal(item)" class="action-btn btn-edit" title="Sửa Lịch">
                  <i class="bi bi-pencil-square"></i>
                </button>
                <button @click="openStatusModal(item)" class="action-btn btn-more" title="Đổi trạng thái">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
              </td>
            </tr>
            <tr v-if="sortedAndFilteredSchedules.length === 0 && !isLoading">
              <td colspan="9" class="text-center py-5">
                <div class="empty-state">
                  <i class="bi bi-search" style="font-size: 2.5rem; color: rgba(255,255,255,0.2);"></i>
                  <p class="mt-3 text-secondary">Không tìm thấy lịch khởi hành nào phù hợp.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION BAR -->
      <div v-if="totalPages > 1" class="schedule-pagination-bar mt-4">
        <div class="pagination-info">
          Trang <strong>{{ currentPage }}</strong> / <strong>{{ totalPages }}</strong> 
        </div>
        <div class="pagination-controls">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage <= 1" 
            class="btn-pag-nav"
          >
            <i class="bi bi-chevron-left"></i>
          </button>
          <button 
            v-for="p in visiblePages" 
            :key="p" 
            @click="changePage(p)" 
            class="btn-pag-num"
            :class="{ active: p === currentPage }"
          >
            {{ p }}
          </button>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage >= totalPages" 
            class="btn-pag-nav"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- MODAL ĐỔI TRẠNG THÁI VẬN HÀNH -->
    <transition name="fade">
      <div v-if="isStatusModalOpen" class="modal-overlay" @click.self="isStatusModalOpen = false">
        <div class="modal-card">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-arrow-repeat text-gold me-2"></i>
              Đổi Trạng Thái Lịch Khởi Hành #{{ currentSchedule?.id }}
            </h4>
            <button @click="isStatusModalOpen = false" class="modal-close-btn">&times;</button>
          </div>

          <div class="modal-body">
            <div class="schedule-summary-box">
              <div class="summary-tour-title">{{ currentSchedule?.ten_tour }}</div>
              <div class="summary-dates">
                <i class="bi bi-calendar-event me-1"></i>
                Khởi hành: <strong>{{ formatDate(currentSchedule?.ngay_khoi_hanh) }}</strong>
                ({{ currentSchedule?.gio_xuat_phat || '00:00' }})
              </div>
            </div>

            <div class="form-group mt-3">
              <label class="form-label">Chọn trạng thái vận hành mới:</label>
              <div class="status-options-grid">
                <label 
                  class="status-option-card"
                  :class="{ active: newStatus === 'ChoPhanBo' }"
                >
                  <input type="radio" v-model="newStatus" value="ChoPhanBo" class="d-none" />
                  <div class="opt-icon text-warning"><i class="bi bi-hourglass-split"></i></div>
                  <div class="opt-text">
                    <strong>Chờ Phân Bổ</strong>
                    <p>Chưa hoàn tất HDV</p>
                  </div>
                </label>

                <label 
                  class="status-option-card"
                  :class="{ active: newStatus === 'SapKhoiHanh' }"
                >
                  <input type="radio" v-model="newStatus" value="SapKhoiHanh" class="d-none" />
                  <div class="opt-icon text-info"><i class="bi bi-clock-history"></i></div>
                  <div class="opt-text">
                    <strong>Sắp Khởi Hành</strong>
                    <p>Sẵn sàng trước ngày đi</p>
                  </div>
                </label>

                <label 
                  class="status-option-card"
                  :class="{ active: newStatus === 'DangChay' }"
                >
                  <input type="radio" v-model="newStatus" value="DangChay" class="d-none" />
                  <div class="opt-icon text-success"><i class="bi bi-play-circle-fill"></i></div>
                  <div class="opt-text">
                    <strong>Đang Chạy</strong>
                    <p>Đoàn đang trên tour</p>
                  </div>
                </label>

                <label 
                  class="status-option-card"
                  :class="{ active: newStatus === 'HoanThanh' }"
                >
                  <input type="radio" v-model="newStatus" value="HoanThanh" class="d-none" />
                  <div class="opt-icon text-primary"><i class="bi bi-check2-all"></i></div>
                  <div class="opt-text">
                    <strong>Hoàn Thành</strong>
                    <p>Kết thúc chuyến đi</p>
                  </div>
                </label>

                <label 
                  class="status-option-card"
                  :class="{ active: newStatus === 'Huy' }"
                >
                  <input type="radio" v-model="newStatus" value="Huy" class="d-none" />
                  <div class="opt-icon text-danger"><i class="bi bi-x-circle-fill"></i></div>
                  <div class="opt-text">
                    <strong>Hủy Chuyến</strong>
                    <p>Ngừng tổ chức chuyến đi này</p>
                  </div>
                </label>
              </div>
            </div>

            <!-- CASCADING OPTIONS -->
            <div class="cascading-options-wrap mt-3" v-if="newStatus === 'HoanThanh' || newStatus === 'Huy'">
              <div class="form-check custom-check mb-2">
                <input class="form-check-input" type="checkbox" id="checkCascade" v-model="cascadeBookings">
                <label class="form-check-label" for="checkCascade">
                  <span v-if="newStatus === 'HoanThanh'">Tự động hoàn tất các đơn booking hợp lệ và chốt hoa hồng HDV</span>
                  <span v-else>Tự động cập nhật các đơn booking liên quan sang trạng thái "Đã hủy"</span>
                </label>
              </div>

              <div class="form-check custom-check">
                <input class="form-check-input" type="checkbox" id="checkNotify" v-model="notifyPassengers">
                <label class="form-check-label" for="checkNotify">
                  <span v-if="newStatus === 'HoanThanh'">Gửi thông báo mời khách hàng đánh giá trải nghiệm chuyến đi</span>
                  <span v-else>Gửi thông báo hủy chuyến và hướng dẫn hỗ trợ cho du khách</span>
                </label>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="isStatusModalOpen = false" class="btn-modal-cancel">Đóng</button>
            <button @click="submitUpdateStatus" class="btn-modal-submit" :disabled="isUpdatingStatus">
              <i class="bi" :class="isUpdatingStatus ? 'bi-hourglass-split' : 'bi-check-lg'"></i>
              <span>{{ isUpdatingStatus ? 'Đang lưu...' : 'Cập Nhật Trạng Thái' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL GÁN HDV NHANH (QUICK ASSIGN GUIDE) -->
    <transition name="fade">
      <div v-if="isAssignModalOpen" class="modal-overlay" @click.self="isAssignModalOpen = false">
        <div class="modal-card">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-person-gear text-gold me-2"></i>
              Phân Bổ Hướng Dẫn Viên Cho Lịch #{{ currentSchedule?.id }}
            </h4>
            <button @click="isAssignModalOpen = false" class="modal-close-btn">&times;</button>
          </div>

          <div class="modal-body">
            <div class="schedule-summary-box">
              <div class="summary-tour-title">{{ currentSchedule?.ten_tour }}</div>
              <div class="summary-dates">
                <i class="bi bi-calendar-event me-1"></i>
                Khởi hành: <strong>{{ formatDate(currentSchedule?.ngay_khoi_hanh) }}</strong>
                - Kết thúc: <strong>{{ formatDate(currentSchedule?.ngay_ket_thuc) }}</strong>
              </div>
            </div>

            <!-- Conflict Alert Box -->
            <div v-if="assignConflicts.length > 0" class="conflict-alert-box mt-3">
              <div class="conflict-header">
                <i class="bi bi-exclamation-octagon-fill text-danger me-2"></i>
                <strong>Cảnh Báo Xung Đột Trùng Lịch:</strong>
              </div>
              <p class="conflict-desc">Hướng dẫn viên này đang được phân công ở các tour sau:</p>
              <ul class="conflict-list">
                <li v-for="c in assignConflicts" :key="c.id">
                  <strong>#{{ c.id }}</strong> - {{ c.ten_tour }} ({{ formatDate(c.ngay_khoi_hanh) }} → {{ formatDate(c.ngay_ket_thuc) }})
                </li>
              </ul>
              <div class="conflict-note text-danger small">
                Bạn có thể chọn HDV khác hoặc tích chọn bên dưới để ghi đè phân bổ nếu HDV có thể hỗ trợ đoàn ghép.
              </div>
            </div>

            <div class="form-group mt-3">
              <label class="form-label">Chọn Hướng Dẫn Viên:</label>
              <select v-model="selectedAssignHdvId" class="form-select-custom">
                <option :value="0">-- Chưa phân bổ (Bỏ gán) --</option>
                <option v-for="h in hdvList" :key="h.nhan_su_id" :value="h.nhan_su_id">
                  {{ h.ho_ten }} (HDV #{{ h.nhan_su_id }})
                </option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="isAssignModalOpen = false" class="btn-modal-cancel">Hủy</button>
            <button 
              v-if="assignConflicts.length > 0" 
              @click="submitAssignHdv(true)" 
              class="btn-modal-warning" 
              :disabled="isAssigningHdv"
            >
              <i class="bi bi-shield-exclamation"></i>
              <span>Vẫn Gán HDV (Bỏ Qua Cảnh Báo)</span>
            </button>
            <button 
              v-else 
              @click="submitAssignHdv(false)" 
              class="btn-modal-submit" 
              :disabled="isAssigningHdv"
            >
              <i class="bi" :class="isAssigningHdv ? 'bi-hourglass-split' : 'bi-check-lg'"></i>
              <span>{{ isAssigningHdv ? 'Đang lưu...' : 'Lưu Phân Bổ' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL XEM KHÁCH ĐOÀN NHANH (PASSENGER MANIFEST PREVIEW) -->
    <transition name="fade">
      <div v-if="isPassengersModalOpen" class="modal-overlay" @click.self="isPassengersModalOpen = false">
        <div class="modal-card modal-lg">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-people-fill text-gold me-2"></i>
              Danh Sách Hành Khách Lịch #{{ currentSchedule?.id }}
            </h4>
            <button @click="isPassengersModalOpen = false" class="modal-close-btn">&times;</button>
          </div>

          <div class="modal-body">
            <div class="schedule-summary-box mb-3">
              <div class="summary-tour-title">{{ currentSchedule?.ten_tour }}</div>
              <div class="summary-dates">
                Khởi hành: <strong>{{ formatDate(currentSchedule?.ngay_khoi_hanh) }}</strong>
                | Số khách đã đặt: <strong>{{ passengersData.bookings.reduce((sum, b) => sum + Number(b.so_nguoi || 1), 0) }} khách</strong>
              </div>
            </div>

            <div v-if="isLoadingPassengers" class="p-4 text-center text-muted">
              <i class="bi bi-arrow-repeat spin fs-2 text-warning"></i>
              <p class="mt-2">Đang tải danh sách hành khách...</p>
            </div>

            <div v-else-if="passengersData.passengers.length === 0 && passengersData.bookings.length === 0" class="empty-box p-4">
              <i class="bi bi-inbox fs-2 text-muted mb-2"></i>
              <p class="text-secondary mb-0">Chưa có khách nào đặt chỗ cho chuyến đi này.</p>
            </div>

            <div v-else class="manifest-content">
              <!-- Bookings summary list -->
              <h5 class="fw-bold mb-2 fs-6 text-dark"><i class="bi bi-journal-text me-1"></i>Các đơn đặt chỗ ({{ passengersData.bookings.length }} đơn):</h5>
              <div class="table-responsive mb-3">
                <table class="table-manifest-bookings">
                  <thead>
                    <tr>
                      <th>Mã Đơn</th>
                      <th>Khách Đặt</th>
                      <th>Số Điện Thoại</th>
                      <th>Số Khách</th>
                      <th>Tổng Tiền</th>
                      <th>Trạng Thái</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="b in passengersData.bookings" :key="b.booking_id">
                      <td><span class="badge-bk">#{{ b.booking_id }}</span></td>
                      <td><strong>{{ b.ho_ten || 'Khách vãng lai' }}</strong></td>
                      <td>{{ b.so_dien_thoai || '--' }}</td>
                      <td>{{ b.so_nguoi }} người</td>
                      <td>{{ Number(b.tong_tien || 0).toLocaleString('vi-VN') }} đ</td>
                      <td><span class="badge-status-sm">{{ b.trang_thai }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Passengers table -->
              <div v-if="passengersData.passengers.length > 0">
                <h5 class="fw-bold mb-2 fs-6 text-dark"><i class="bi bi-person-check me-1"></i>Danh sách hành khách chi tiết ({{ passengersData.passengers.length }} người):</h5>
                <div class="table-responsive">
                  <table class="table-manifest-bookings">
                    <thead>
                      <tr>
                        <th style="width: 45px">STT</th>
                        <th>Họ Và Tên</th>
                        <th>Số Điện Thoại</th>
                        <th>CCCD / Hộ Chiếu</th>
                        <th>Giới Tính</th>
                        <th>Trạng Thái Điểm Danh</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(p, idx) in passengersData.passengers" :key="p.id || idx">
                        <td>{{ idx + 1 }}</td>
                        <td><strong>{{ p.ho_ten }}</strong></td>
                        <td>{{ p.so_dien_thoai || '--' }}</td>
                        <td><code>{{ p.so_cmnd || '--' }}</code></td>
                        <td>{{ p.gioi_tinh || '--' }}</td>
                        <td>
                          <span :class="p.trang_thai === 'DaCheckIn' ? 'badge-checkin-yes' : 'badge-checkin-no'">
                            {{ p.trang_thai === 'DaCheckIn' ? 'Đã Điểm Danh' : 'Chưa Điểm Danh' }}
                          </span>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <a 
              :href="'index.php?act=admin/danhSachKhachTheoTour&lich_id=' + currentSchedule?.id" 
              target="_blank" 
              class="btn-modal-cancel"
            >
              <i class="bi bi-printer me-1"></i> In Danh Sách Đầy Đủ
            </a>
            <button @click="isPassengersModalOpen = false" class="btn-modal-submit">Đóng</button>
          </div>
        </div>
      </div>
    </transition>

    <!-- MODAL SỬA NHANH LỊCH KHỞI HÀNH -->
    <transition name="fade">
      <div v-if="isEditModalOpen" class="modal-overlay" @click.self="isEditModalOpen = false">
        <div class="modal-card modal-lg">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-pencil-square text-gold me-2"></i>
              Sửa Nhanh Lịch Khởi Hành #{{ editForm.id }}
            </h4>
            <button @click="isEditModalOpen = false" class="modal-close-btn">&times;</button>
          </div>

          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Ngày khởi hành:</label>
                <input type="date" v-model="editForm.ngay_khoi_hanh" class="form-input-custom" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Giờ xuất phát:</label>
                <input type="time" v-model="editForm.gio_xuat_phat" class="form-input-custom" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Ngày kết thúc:</label>
                <input type="date" v-model="editForm.ngay_ket_thuc" class="form-input-custom" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Giờ kết thúc:</label>
                <input type="time" v-model="editForm.gio_ket_thuc" class="form-input-custom" />
              </div>

              <div class="col-md-8">
                <label class="form-label">Điểm tập trung xuất phát:</label>
                <input type="text" v-model="editForm.diem_tap_trung" class="form-input-custom" placeholder="Ví dụ: Cổng số 1 Sân bay Nội Bài..." />
              </div>

              <div class="col-md-4">
                <label class="form-label">Sức chứa (Số chỗ tối đa):</label>
                <input type="number" v-model="editForm.so_cho" min="1" max="500" class="form-input-custom" />
              </div>
            </div>
          </div>

          <div class="modal-footer mt-4">
            <button @click="isEditModalOpen = false" class="btn-modal-cancel">Hủy</button>
            <button @click="submitEditSchedule" class="btn-modal-submit" :disabled="isSavingEdit">
              <i class="bi" :class="isSavingEdit ? 'bi-hourglass-split' : 'bi-check-lg'"></i>
              <span>{{ isSavingEdit ? 'Đang lưu...' : 'Lưu Thay Đổi' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue';
import { onUnmounted } from 'vue';

const currentTimeString = ref('');
let clockInterval = null;

const updateClock = () => {
  const now = new Date();
  const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
  const dayName = days[now.getDay()];
  const day = now.getDate().toString().padStart(2, '0');
  const month = (now.getMonth() + 1).toString().padStart(2, '0');
  const year = now.getFullYear();
  const hours = now.getHours().toString().padStart(2, '0');
  const minutes = now.getMinutes().toString().padStart(2, '0');
  currentTimeString.value = `${dayName}, ${day}/${month}/${year} ${hours}:${minutes}`;
};

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 60000);
});

onUnmounted(() => {
  clearInterval(clockInterval);
});

function getTourThumbnail(item) {
  if (item.hinh_anh) {
    return (window.__BASE_URL__ || '') + 'public/uploads/tours/' + item.hinh_anh;
  }
  return (window.__BASE_URL__ || '') + 'public/images/dashboard/halong_banner.jpg';
}

function handleImgError(e) {
  e.target.src = (window.__BASE_URL__ || '') + 'public/images/dashboard/halong_banner.jpg';
}


const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
});

// Toast notification state
const toast = ref({
  visible: false,
  message: '',
  type: 'success'
});
let toastTimer = null;
function showToast(msg, type = 'success') {
  clearTimeout(toastTimer);
  toast.value = { visible: true, message: msg, type };
  toastTimer = setTimeout(() => {
    toast.value.visible = false;
  }, 3500);
}

// State
const isLoading = ref(false);
const searchQuery = ref('');
const selectedStatus = ref('');
const selectedTourId = ref('');
const selectedHdvId = ref('');
const fromDate = ref('');
const toDate = ref('');
const sortBy = ref('date_asc');
const pageSize = ref(12);
const currentPage = ref(1);

const schedules = ref([]);
const toursList = ref([]);
const hdvList = ref([]);
const csrfToken = ref('');
const csrfGlobal = ref('');

// Status Modal State
const isStatusModalOpen = ref(false);
const currentSchedule = ref(null);
const newStatus = ref('SapKhoiHanh');
const cascadeBookings = ref(true);
const notifyPassengers = ref(true);
const isUpdatingStatus = ref(false);

// Quick Edit Modal State
const isEditModalOpen = ref(false);
const isSavingEdit = ref(false);
const editForm = ref({
  id: 0,
  ngay_khoi_hanh: '',
  gio_xuat_phat: '08:00',
  ngay_ket_thuc: '',
  gio_ket_thuc: '18:00',
  diem_tap_trung: '',
  so_cho: 50,
  trang_thai: 'SapKhoiHanh'
});

// Quick Assign Guide Modal State
const isAssignModalOpen = ref(false);
const selectedAssignHdvId = ref(0);
const assignConflicts = ref([]);
const isAssigningHdv = ref(false);

// Passenger Manifest Modal State
const isPassengersModalOpen = ref(false);
const isLoadingPassengers = ref(false);
const passengersData = ref({ bookings: [], passengers: [] });

let searchTimer = null;

function onSearchInput() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    currentPage.value = 1;
    fetchSchedules();
  }, 350);
}

function clearSearch() {
  searchQuery.value = '';
  currentPage.value = 1;
  fetchSchedules();
}

function clearTourFilter() {
  selectedTourId.value = '';
  currentPage.value = 1;
  fetchSchedules();
}

function onFilterChange() {
  currentPage.value = 1;
  fetchSchedules();
}

function resetFilters() {
  searchQuery.value = '';
  selectedStatus.value = '';
  selectedTourId.value = '';
  selectedHdvId.value = '';
  fromDate.value = '';
  toDate.value = '';
  sortBy.value = 'date_asc';
  currentPage.value = 1;
  fetchSchedules();
}

// Computed stats
const stats = computed(() => {
  const all = schedules.value || [];
  return {
    total: all.length,
    upcoming: all.filter(s => s.trang_thai === 'SapKhoiHanh' && Number(s.so_nhan_su) > 0).length,
    ongoing: all.filter(s => s.trang_thai === 'DangChay').length,
    completed: all.filter(s => s.trang_thai === 'HoanThanh').length,
    pending: all.filter(s => Number(s.so_nhan_su) === 0 || s.trang_thai === 'ChoPhanBo').length,
  };
});

const sortedAndFilteredSchedules = computed(() => {
  let list = [...schedules.value];

  // Client-side HDV filtering
  if (selectedHdvId.value) {
    if (selectedHdvId.value === 'unassigned') {
      list = list.filter(s => !s.ten_hdv || Number(s.so_nhan_su) === 0);
    } else {
      const targetId = Number(selectedHdvId.value);
      list = list.filter(s => {
        if (Number(s.nhan_su_id) === targetId) return true;
        if (s.nhan_su_ids && Array.isArray(s.nhan_su_ids) && s.nhan_su_ids.includes(targetId)) return true;
        return false;
      });
    }
  }

  // Sorting
  list.sort((a, b) => {
    if (sortBy.value === 'date_asc') {
      return (a.ngay_khoi_hanh || '').localeCompare(b.ngay_khoi_hanh || '');
    } else if (sortBy.value === 'date_desc') {
      return (b.ngay_khoi_hanh || '').localeCompare(a.ngay_khoi_hanh || '');
    } else if (sortBy.value === 'occupancy_desc') {
      return Number(b.ty_le_lap_day || 0) - Number(a.ty_le_lap_day || 0);
    } else if (sortBy.value === 'occupancy_asc') {
      return Number(a.ty_le_lap_day || 0) - Number(b.ty_le_lap_day || 0);
    }
    return 0;
  });

  return list;
});

const totalPages = computed(() => {
  if (pageSize.value === 0) return 1;
  return Math.ceil(sortedAndFilteredSchedules.value.length / pageSize.value) || 1;
});

const paginatedSchedules = computed(() => {
  if (pageSize.value === 0) return sortedAndFilteredSchedules.value;
  const start = (currentPage.value - 1) * pageSize.value;
  return sortedAndFilteredSchedules.value.slice(start, start + pageSize.value);
});

const visiblePages = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const curr = currentPage.value;
  const start = Math.max(1, curr - 2);
  const end = Math.min(total, curr + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

function changePage(p) {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p;
    window.scrollTo({ top: 250, behavior: 'smooth' });
  }
}

function applyData(data) {
  if (!data) return;
  schedules.value = data.schedules || [];
  if (data.toursList) toursList.value = data.toursList;
  if (data.hdvList) hdvList.value = data.hdvList;
  if (data.filters) {
    if (data.filters.search) searchQuery.value = data.filters.search;
    if (data.filters.trang_thai) selectedStatus.value = data.filters.trang_thai;
    if (data.filters.tour_id) selectedTourId.value = data.filters.tour_id;
    if (data.filters.tu_ngay) fromDate.value = data.filters.tu_ngay;
    if (data.filters.den_ngay) toDate.value = data.filters.den_ngay;
  }
  if (data.csrfToken) csrfToken.value = data.csrfToken;
  if (data.csrfGlobal) csrfGlobal.value = data.csrfGlobal;
}

async function fetchSchedules() {
  isLoading.value = true;
  const params = new URLSearchParams({
    act: 'lichKhoiHanh/apiList'
  });
  if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
  if (selectedStatus.value) params.append('trang_thai', selectedStatus.value);
  if (selectedTourId.value) params.append('tour_id', selectedTourId.value);
  if (fromDate.value) params.append('tu_ngay', fromDate.value);
  if (toDate.value) params.append('den_ngay', toDate.value);

  try {
    const res = await fetch('index.php?' + params.toString(), {
      headers: { 'Accept': 'application/json' }
    });
    if (res.ok) {
      const json = await res.json();
      if (json.success && json.data) {
        applyData(json.data);
      }
    }
  } catch (err) {
    console.error('[Vue ScheduleManage] Error fetching schedules:', err);
  } finally {
    isLoading.value = false;
  }
}

// Occupancy helpers
function getOccupancyClass(pct) {
  const p = Number(pct || 0);
  if (p >= 100) return 'pct-full';
  if (p >= 80) return 'pct-high';
  if (p >= 40) return 'pct-med';
  return 'pct-low';
}

function getOccupancyBarClass(pct) {
  const p = Number(pct || 0);
  if (p >= 100) return 'bar-rose';
  if (p >= 80) return 'bar-amber';
  if (p >= 40) return 'bar-sky';
  return 'bar-emerald';
}

// Status Modal Handlers
function openStatusModal(item) {
  currentSchedule.value = item;
  newStatus.value = item.trang_thai || 'SapKhoiHanh';
  cascadeBookings.value = true;
  notifyPassengers.value = true;
  isStatusModalOpen.value = true;
}

async function submitUpdateStatus() {
  if (!currentSchedule.value || isUpdatingStatus.value) return;
  isUpdatingStatus.value = true;

  try {
    const res = await fetch('index.php?act=lichKhoiHanh/apiUpdateStatus', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        id: currentSchedule.value.id,
        trang_thai: newStatus.value,
        cascade_bookings: cascadeBookings.value ? 1 : 0,
        notify_passengers: notifyPassengers.value ? 1 : 0,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const data = await res.json();
    if (data.success) {
      currentSchedule.value.trang_thai = newStatus.value;
      showToast(data.message || 'Cập nhật trạng thái thành công!');
      isStatusModalOpen.value = false;
      fetchSchedules();
    } else {
      showToast(data.message || 'Lỗi khi cập nhật trạng thái', 'error');
    }
  } catch (err) {
    showToast('Lỗi mạng: ' + err.message, 'error');
  } finally {
    isUpdatingStatus.value = false;
  }
}

// Quick Assign Guide Modal Handlers
function openAssignModal(item) {
  currentSchedule.value = item;
  selectedAssignHdvId.value = Number(item.nhan_su_id) || 0;
  assignConflicts.value = [];
  isAssignModalOpen.value = true;
}

async function submitAssignHdv(force = false) {
  if (!currentSchedule.value || isAssigningHdv.value) return;
  isAssigningHdv.value = true;

  try {
    const res = await fetch('index.php?act=lichKhoiHanh/apiAssignHdv', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        lich_id: currentSchedule.value.id,
        nhan_su_id: selectedAssignHdvId.value,
        force: force ? 1 : 0,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const data = await res.json();
    if (data.success) {
      showToast(data.message || 'Phân bổ hướng dẫn viên thành công!');
      isAssignModalOpen.value = false;
      assignConflicts.value = [];
      fetchSchedules();
    } else {
      if (data.has_conflict && data.conflicts) {
        assignConflicts.value = data.conflicts;
        showToast(data.message || 'Phát hiện trùng lịch hướng dẫn viên!', 'error');
      } else {
        showToast(data.message || 'Lỗi khi phân bổ HDV', 'error');
      }
    }
  } catch (err) {
    showToast('Lỗi mạng: ' + err.message, 'error');
  } finally {
    isAssigningHdv.value = false;
  }
}

// Passenger Manifest Modal Handlers
async function openPassengersModal(item) {
  currentSchedule.value = item;
  passengersData.value = { bookings: [], passengers: [] };
  isLoadingPassengers.value = true;
  isPassengersModalOpen.value = true;

  try {
    const res = await fetch(`index.php?act=lichKhoiHanh/apiSchedulePassengers&lich_id=${item.id}`, {
      headers: { 'Accept': 'application/json' }
    });
    if (res.ok) {
      const json = await res.json();
      if (json.success && json.data) {
        passengersData.value = json.data;
      } else {
        showToast(json.message || 'Không tải được danh sách khách', 'error');
      }
    }
  } catch (err) {
    showToast('Lỗi kết nối khi tải danh sách: ' + err.message, 'error');
  } finally {
    isLoadingPassengers.value = false;
  }
}

function openEditModal(item) {
  editForm.value = {
    id: item.id,
    ngay_khoi_hanh: item.ngay_khoi_hanh || '',
    gio_xuat_phat: item.gio_xuat_phat || '08:00',
    ngay_ket_thuc: item.ngay_ket_thuc || '',
    gio_ket_thuc: item.gio_ket_thuc || '18:00',
    diem_tap_trung: item.diem_tap_trung || '',
    so_cho: item.so_cho || 50,
    trang_thai: item.trang_thai || 'SapKhoiHanh'
  };
  isEditModalOpen.value = true;
}

async function submitQuickEdit() {
  if (isSavingEdit.value) return;
  isSavingEdit.value = true;

  try {
    const res = await fetch('index.php?act=lichKhoiHanh/apiQuickUpdate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        ...editForm.value,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const data = await res.json();
    if (data.success) {
      showToast(data.message || 'Lưu lịch khởi hành thành công!');
      isEditModalOpen.value = false;
      fetchSchedules();
    } else {
      showToast(data.message || 'Lỗi khi lưu lịch khởi hành', 'error');
    }
  } catch (err) {
    showToast('Lỗi mạng: ' + err.message, 'error');
  } finally {
    isSavingEdit.value = false;
  }
}

// Formatters
function formatDay(dateStr) {
  if (!dateStr) return '--';
  const parts = dateStr.split('-');
  return parts[2] ? parts[2].substring(0, 2) : '--';
}

function formatMonth(dateStr) {
  if (!dateStr) return 'N/A';
  const parts = dateStr.split('-');
  return parts[1] ? `Thg ${Number(parts[1])}` : 'N/A';
}

function formatYear(dateStr) {
  if (!dateStr) return '';
  const parts = dateStr.split('-');
  return parts[0] || '';
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function getStatusClass(item) {
  if (item.trang_thai === 'Huy') return 'st-cancelled';
  if (Number(item.so_nhan_su) === 0 || item.trang_thai === 'ChoPhanBo') return 'st-pending';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'st-upcoming';
    case 'DangChay': return 'st-ongoing';
    case 'HoanThanh': return 'st-completed';
    default: return 'st-upcoming';
  }
}

function getStatusText(item) {
  if (item.trang_thai === 'Huy') return 'Đã hủy chuyến';
  if (Number(item.so_nhan_su) === 0 || item.trang_thai === 'ChoPhanBo') return 'Đang chờ phân bổ';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'Sắp khởi hành';
    case 'DangChay': return 'Đang chạy';
    case 'HoanThanh': return 'Hoàn thành';
    default: return item.trang_thai || 'Sắp khởi hành';
  }
}

function getScheduleBorderClass(item) {
  if (item.trang_thai === 'Huy') return 'border-rose';
  if (Number(item.so_nhan_su) === 0 || item.trang_thai === 'ChoPhanBo') return 'border-amber';
  if (item.coTrungLichHDV) return 'border-rose';
  if (item.trang_thai === 'DangChay') return 'border-emerald';
  if (item.trang_thai === 'HoanThanh') return 'border-slate';
  return 'border-sky';
}

function hasWarning(item) {
  if (item.trang_thai === 'Huy' || item.trang_thai === 'HoanThanh') return false;
  if (Number(item.so_nhan_su) === 0) return true;
  if (item.coTrungLichHDV) return true;
  if (Number(item.so_dich_vu) === 0) return true;
  return false;
}

function getWarningText(item) {
  const tags = [];
  if (Number(item.so_nhan_su) === 0) tags.push('Thiếu HDV');
  if (item.coTrungLichHDV) tags.push('Trùng lịch HDV');
  if (Number(item.so_dich_vu) === 0) tags.push('Thiếu Dịch vụ');
  return tags.join(' • ') || 'Cảnh báo';
}

function getWarningTooltip(item) {
  const tags = [];
  if (Number(item.so_nhan_su) === 0) tags.push('Chưa phân bổ hướng dẫn viên');
  if (item.coTrungLichHDV) tags.push(`HDV bị trùng ${item.soLichTrungHDV || 0} lịch khác`);
  if (Number(item.so_dich_vu) === 0) tags.push('Chưa đặt dịch vụ nhà cung cấp');
  return tags.join(', ');
}

onMounted(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const tourIdParam = urlParams.get('tour_id');
  if (tourIdParam) {
    selectedTourId.value = tourIdParam;
  }
  if (props.initialData && Object.keys(props.initialData).length > 0 && !tourIdParam) {
    applyData(props.initialData);
  } else {
    fetchSchedules();
  }
});
</script>

<style scoped>
/* ==========================================================================
   GLASSMORPHISM THEME - SCHEDULE MANAGE
   ========================================================================== */
.vue-admin-schedule-manage {
  --app-bg: #0b1120;
  --panel-bg: rgba(17, 25, 40, 0.75);
  --panel-border: rgba(255, 255, 255, 0.08);
  --text-main: #f1f5f9;
  --text-muted: #94a3b8;
  --primary-color: #3b82f6;
  --primary-hover: #2563eb;
  --success-color: #10b981;
  --warning-color: #f59e0b;
  --danger-color: #ef4444;
  --info-color: #0ea5e9;
  --gold-color: #eab308;
  --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);

  background-color: var(--app-bg);
  min-height: 100vh;
  padding: 0 0 40px 0;
  color: var(--text-main);
  font-family: 'Inter', sans-serif;
}

/* ==========================================================================
   HERO BANNER
   ========================================================================== */
.page-header-hero {
  position: relative;
  height: 280px;
  border-radius: 0 0 24px 24px;
  overflow: hidden;
  margin-bottom: -60px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.hero-bg {
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
  background-size: cover;
  background-position: center 30%;
  z-index: 1;
}

.hero-bg::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.6) 50%, rgba(15, 23, 42, 0.3) 100%);
  z-index: 2;
}

.hero-content {
  position: relative;
  z-index: 3;
  height: 100%;
  padding: 40px 50px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.4);
  color: #60a5fa;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 0.5px;
  margin-bottom: 15px;
  backdrop-filter: blur(4px);
}

.hero-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #fff;
  margin: 0 0 10px 0;
  text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.hero-subtitle {
  font-size: 1.1rem;
  color: rgba(255,255,255,0.8);
  max-width: 600px;
  line-height: 1.5;
}

.badge-tour-filter {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(245, 158, 11, 0.2);
  border: 1px solid rgba(245, 158, 11, 0.5);
  color: #fcd34d;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
}

.btn-clear-tour {
  background: none;
  border: none;
  color: #fcd34d;
  font-size: 1.2rem;
  line-height: 1;
  padding: 0 2px;
  cursor: pointer;
}

.hero-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 20px;
}

.current-time-display {
  display: flex;
  align-items: center;
  background: rgba(255,255,255,0.1);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  padding: 10px 20px;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,0.15);
  color: #fff;
  font-weight: 500;
  font-size: 0.95rem;
}

.btn-hero-create {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--primary-color);
  color: #fff;
  padding: 14px 24px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 1rem;
  text-decoration: none;
  border: 1px solid rgba(255,255,255,0.2);
  box-shadow: 0 8px 20px rgba(59,130,246,0.4);
  transition: all 0.3s ease;
}

.btn-hero-create:hover {
  background: var(--primary-hover);
  transform: translateY(-2px);
  color: #fff;
}

/* ==========================================================================
   STATS GRID
   ========================================================================== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 20px;
  padding: 0 40px;
  position: relative;
  z-index: 10;
  margin-bottom: 30px;
}

.stat-card {
  background: var(--panel-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--panel-border);
  border-radius: 16px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 15px;
  box-shadow: var(--glass-shadow);
  transition: transform 0.3s ease, border-color 0.3s ease;
}

.stat-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255,255,255,0.2);
}

.stat-icon-box {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}

.stat-icon-box.icon-blue { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
.stat-icon-box.icon-green { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.stat-icon-box.icon-purple { background: rgba(168,85,247,0.15); color: #c084fc; border: 1px solid rgba(168,85,247,0.3); }
.stat-icon-box.icon-orange { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
.stat-icon-box.icon-red { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-label {
  color: var(--text-muted);
  font-size: 0.85rem;
  font-weight: 500;
  margin-bottom: 4px;
}

.stat-num {
  font-size: 1.5rem;
  color: var(--text-main);
  line-height: 1.2;
  margin-bottom: 5px;
}

.stat-trend {
  font-size: 0.75rem;
  display: flex;
  align-items: center;
  gap: 2px;
}

.trend-positive { color: #34d399; }
.trend-negative { color: #f87171; }

/* ==========================================================================
   FILTER SECTION
   ========================================================================== */
.filter-section {
  background: var(--panel-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--panel-border);
  border-radius: 16px;
  margin: 0 40px 30px;
  padding: 20px;
  box-shadow: var(--glass-shadow);
}

.filter-header {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 15px;
  color: #fff;
  display: flex;
  align-items: center;
}

.filter-grid-inline {
  display: grid;
  grid-template-columns: 2fr 1.5fr 1.5fr 1.2fr 1.2fr auto;
  gap: 15px;
  align-items: center;
}

.input-icon-wrap, .select-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.icon-left {
  position: absolute;
  left: 12px;
  color: var(--text-muted);
  font-size: 1rem;
  pointer-events: none;
}

.form-input-custom, .form-select-custom {
  width: 100%;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255,255,255,0.1);
  color: var(--text-main);
  padding: 10px 12px 10px 38px;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.3s ease;
  outline: none;
}

.form-input-custom:focus, .form-select-custom:focus {
  border-color: rgba(59, 130, 246, 0.6);
  background: rgba(15, 23, 42, 0.8);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

.btn-clear-search {
  position: absolute;
  right: 10px;
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  padding: 0;
  font-size: 1.2rem;
}

.btn-clear-search:hover { color: #fff; }

.btn-refresh {
  background: rgba(59, 130, 246, 0.15);
  border: 1px solid rgba(59, 130, 246, 0.4);
  color: #60a5fa;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 42px;
}

.btn-refresh:hover {
  background: rgba(59, 130, 246, 0.3);
  color: #fff;
}

/* ==========================================================================
   DATA TABLE SECTION
   ========================================================================== */
.schedule-section {
  background: var(--panel-bg);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  border: 1px solid var(--panel-border);
  border-radius: 16px;
  margin: 0 40px;
  padding: 20px;
  box-shadow: var(--glass-shadow);
}

.section-meta-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.meta-left {
  display: flex;
  align-items: center;
  font-size: 1.05rem;
  font-weight: 500;
  color: #fff;
}

.live-dot-pulse {
  width: 10px;
  height: 10px;
  background-color: var(--primary-color);
  border-radius: 50%;
  margin-right: 10px;
  box-shadow: 0 0 0 rgba(59, 130, 246, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
  70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
  100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
}

.meta-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.per-page-label {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.per-page-select {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255,255,255,0.1);
  color: #fff;
  padding: 6px 10px;
  border-radius: 6px;
  font-size: 0.9rem;
  outline: none;
}

.view-toggles {
  display: flex;
  background: rgba(0,0,0,0.2);
  border-radius: 6px;
  padding: 2px;
  border: 1px solid rgba(255,255,255,0.05);
}

.btn-view-toggle {
  background: transparent;
  border: none;
  color: var(--text-muted);
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-view-toggle.active {
  background: rgba(255,255,255,0.1);
  color: #fff;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.table-responsive {
  overflow-x: auto;
}

.table-glass {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  text-align: left;
}

.table-glass th {
  padding: 15px;
  color: var(--text-muted);
  font-weight: 500;
  font-size: 0.85rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  background: rgba(0,0,0,0.1);
}

.table-glass td {
  padding: 15px;
  vertical-align: middle;
  border-bottom: 1px solid rgba(255,255,255,0.05);
  font-size: 0.95rem;
  transition: background 0.2s ease;
}

.table-row-hover:hover td {
  background: rgba(255,255,255,0.03);
}

.tour-info-cell {
  display: flex;
  align-items: center;
  gap: 15px;
}

.tour-thumb {
  width: 50px;
  height: 50px;
  border-radius: 8px;
  overflow: hidden;
  flex-shrink: 0;
  border: 1px solid rgba(255,255,255,0.1);
}

.tour-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.tour-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.tour-name {
  font-weight: 600;
  color: #fff;
  font-size: 0.95rem;
}

.tour-dest {
  font-size: 0.8rem;
  color: var(--text-muted);
}

.badge-code {
  background: rgba(255,255,255,0.1);
  padding: 4px 8px;
  border-radius: 6px;
  font-family: monospace;
  font-size: 0.85rem;
  color: #cbd5e1;
}

.date-cell {
  font-size: 0.9rem;
  color: #e2e8f0;
}

.hdv-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.hdv-name {
  font-weight: 500;
  color: #fff;
}

.hdv-phone {
  font-size: 0.8rem;
  color: #60a5fa;
  text-decoration: none;
}

.badge-unassigned {
  display: inline-block;
  padding: 4px 8px;
  background: rgba(248, 113, 113, 0.1);
  color: #fca5a5;
  border-radius: 12px;
  font-size: 0.8rem;
  border: 1px solid rgba(248, 113, 113, 0.2);
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 600;
}

.status-green, .st-completed { background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.status-yellow, .st-pending { background: rgba(245,158,11,0.15); color: #fbbf24; border: 1px solid rgba(245,158,11,0.3); }
.status-blue, .st-upcoming, .st-ongoing { background: rgba(59,130,246,0.15); color: #60a5fa; border: 1px solid rgba(59,130,246,0.3); }
.status-red, .st-cancelled { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

.occupancy-cell {
  background: rgba(0,0,0,0.2);
  padding: 6px 10px;
  border-radius: 6px;
  text-align: center;
  border: 1px solid rgba(255,255,255,0.05);
}

.actions-cell {
  text-align: right;
  display: flex;
  gap: 8px;
  justify-content: flex-end;
}

.action-btn {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 1px solid rgba(255,255,255,0.1);
  background: rgba(255,255,255,0.05);
  color: var(--text-muted);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.action-btn:hover {
  background: rgba(255,255,255,0.15);
  color: #fff;
}

.btn-view:hover { color: #34d399; border-color: rgba(52, 211, 153, 0.4); }
.btn-edit:hover { color: #60a5fa; border-color: rgba(96, 165, 250, 0.4); }

/* ==========================================================================
   PAGINATION
   ========================================================================== */
.schedule-pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.05);
}

.pagination-controls {
  display: flex;
  gap: 5px;
}

.btn-pag-nav, .btn-pag-num {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.1);
  color: var(--text-muted);
  width: 36px;
  height: 36px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pag-nav:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-pag-num.active, .btn-pag-nav:not(:disabled):hover, .btn-pag-num:hover {
  background: var(--primary-color);
  color: #fff;
  border-color: var(--primary-hover);
}

/* ==========================================================================
   MODALS (Keep existing structural logic, apply glass theme)
   ========================================================================== */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(5px);
  display: flex; justify-content: center; align-items: center;
  z-index: 1050;
}
.modal-card {
  background: rgba(17, 25, 40, 0.95);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 16px;
  width: 90%; max-width: 500px;
  box-shadow: 0 20px 50px rgba(0,0,0,0.5);
  overflow: hidden;
}
.modal-card.modal-lg { max-width: 800px; }
.modal-header {
  padding: 20px 24px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
  display: flex; justify-content: space-between; align-items: center;
  background: rgba(0,0,0,0.2);
}
.modal-title { font-size: 1.2rem; font-weight: 600; color: #fff; margin: 0; }
.modal-close-btn { background: none; border: none; color: #94a3b8; font-size: 1.5rem; cursor: pointer; }
.modal-close-btn:hover { color: #fff; }
.modal-body { padding: 24px; max-height: 70vh; overflow-y: auto; }
.modal-footer {
  padding: 20px 24px; border-top: 1px solid rgba(255,255,255,0.1);
  display: flex; justify-content: flex-end; gap: 15px;
  background: rgba(0,0,0,0.2);
}
.btn-modal-cancel {
  background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);
  color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer;
}
.btn-modal-submit {
  background: var(--primary-color); border: none;
  color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; gap: 8px;
}
.btn-modal-submit:disabled { opacity: 0.7; cursor: not-allowed; }
.schedule-summary-box {
  background: rgba(0,0,0,0.2); padding: 15px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 20px;
}
.summary-tour-title { font-weight: 600; color: #60a5fa; margin-bottom: 5px; }
.summary-dates { color: #cbd5e1; font-size: 0.9rem; }
.form-label { color: #cbd5e1; font-size: 0.9rem; margin-bottom: 6px; display: block; }
.status-options-grid { display: flex; flex-direction: column; gap: 10px; }
.status-option-card {
  display: flex; align-items: center; gap: 15px; padding: 12px 15px;
  background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
  border-radius: 10px; cursor: pointer; transition: all 0.2s ease;
}
.status-option-card:hover { background: rgba(255,255,255,0.1); }
.status-option-card.active {
  background: rgba(59, 130, 246, 0.1); border-color: rgba(59, 130, 246, 0.5); box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.5);
}
.opt-icon { font-size: 1.5rem; width: 40px; text-align: center; }
.opt-text strong { color: #fff; display: block; font-size: 1rem; margin-bottom: 2px; }
.opt-text p { color: #94a3b8; font-size: 0.85rem; margin: 0; }
.custom-check label { color: #cbd5e1; font-size: 0.9rem; cursor: pointer; }
.table-manifest-bookings { width: 100%; border-collapse: collapse; }
.table-manifest-bookings th, .table-manifest-bookings td {
  padding: 10px; border: 1px solid rgba(255,255,255,0.1); text-align: left; font-size: 0.9rem;
}
.table-manifest-bookings th { background: rgba(0,0,0,0.3); color: #94a3b8; }
.badge-checkin-yes { background: rgba(16,185,129,0.2); color: #34d399; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; }
.badge-checkin-no { background: rgba(245,158,11,0.2); color: #fcd34d; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; }

/* Toast */
.toast-popup {
  position: fixed; top: 24px; right: 24px; z-index: 9999;
  padding: 14px 22px; border-radius: 12px;
  display: flex; align-items: center; gap: 10px;
  font-weight: 500; font-size: 0.95rem;
  box-shadow: 0 10px 30px rgba(0,0,0,0.4);
  color: #fff;
}
.toast-popup.success { background: rgba(16,185,129,0.9); }
.toast-popup.error { background: rgba(239,68,68,0.9); }
.toast-close { background: none; border: none; color: #fff; font-size: 1.3rem; cursor: pointer; margin-left: 10px; }

/* Fade Transition */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

/* Spin Animation */
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin 1s linear infinite; }

/* Text Helpers */
.text-cyan { color: #22d3ee !important; }
.text-orange { color: #fb923c !important; }
.text-gold { color: #eab308 !important; }
.text-danger { color: #ef4444 !important; }
.text-secondary { color: #94a3b8 !important; }

/* Warning Modal Button */
.btn-modal-warning {
  background: rgba(245,158,11,0.8); border: none;
  color: #fff; padding: 10px 20px; border-radius: 8px; cursor: pointer;
  display: flex; align-items: center; gap: 8px;
}

/* Empty State */
.empty-state { display: flex; flex-direction: column; align-items: center; }

/* Loading Tag */
.loading-tag { color: #fbbf24; font-size: 0.9rem; }

/* Conflict Alert */
.conflict-alert-box {
  background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3);
  border-radius: 10px; padding: 15px;
}
.conflict-header { display: flex; align-items: center; margin-bottom: 8px; color: #f87171; font-size: 0.95rem; }
.conflict-desc { color: #cbd5e1; font-size: 0.9rem; margin: 5px 0; }
.conflict-list { color: #e2e8f0; font-size: 0.85rem; padding-left: 20px; margin: 5px 0; }
.conflict-note { margin-top: 8px; }

/* Badge BK */
.badge-bk { background: rgba(59,130,246,0.15); color: #60a5fa; padding: 3px 8px; border-radius: 6px; font-family: monospace; font-size: 0.85rem; }
.badge-status-sm { background: rgba(255,255,255,0.1); color: #cbd5e1; padding: 3px 8px; border-radius: 6px; font-size: 0.8rem; }

/* Bootstrap Grid helpers for modals */
.row { display: flex; flex-wrap: wrap; margin: 0 -6px; }
.col-md-4 { flex: 0 0 33.333%; max-width: 33.333%; padding: 0 6px; }
.col-md-6 { flex: 0 0 50%; max-width: 50%; padding: 0 6px; }
.col-md-8 { flex: 0 0 66.666%; max-width: 66.666%; padding: 0 6px; }
.g-3 { gap: 12px; }

/* Modal form inputs (no left-icon padding) */
.modal-body .form-input-custom {
  padding-left: 12px;
}
</style>

