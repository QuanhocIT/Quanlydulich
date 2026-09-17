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

    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-calendar3-event"></i>
          <span>Điều Phối Lịch Trình</span>
        </div>
        <p class="header-subtitle">
          Theo dõi tiến độ vận hành, tỷ lệ lấp đầy chỗ, cảnh báo HDV và phân bổ dịch vụ theo thời gian thực
          <span v-if="selectedTourId" class="badge-tour-filter ms-2">
            Đang lọc Tour #{{ selectedTourId }}
            <button type="button" class="btn-clear-tour" @click="clearTourFilter" title="Bỏ lọc theo tour">&times;</button>
          </span>
        </p>
      </div>
      <div class="header-actions">
        <a href="index.php?act=lichKhoiHanh/create" class="btn-create">
          <i class="bi bi-plus-lg"></i>
          <span>Thêm Lịch Khởi Hành</span>
        </a>
      </div>
    </header>

    <!-- 5 STATS CARDS -->
    <section class="stats-grid">
      <div class="stat-card stat-total">
        <div class="stat-content">
          <span class="stat-label">Tổng số lịch</span>
          <strong class="stat-num">{{ stats.total }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-calendar-check-fill"></i></div>
      </div>

      <div class="stat-card stat-upcoming">
        <div class="stat-content">
          <span class="stat-label">Sắp khởi hành</span>
          <strong class="stat-num">{{ stats.upcoming }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-clock-history"></i></div>
      </div>

      <div class="stat-card stat-ongoing">
        <div class="stat-content">
          <span class="stat-label">Đang chạy</span>
          <strong class="stat-num">{{ stats.ongoing }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-play-circle-fill"></i></div>
      </div>

      <div class="stat-card stat-completed">
        <div class="stat-content">
          <span class="stat-label">Đã hoàn thành</span>
          <strong class="stat-num">{{ stats.completed }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-check2-all"></i></div>
      </div>

      <div class="stat-card stat-pending">
        <div class="stat-content">
          <span class="stat-label">Chờ phân bổ</span>
          <strong class="stat-num">{{ stats.pending }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-person-exclamation"></i></div>
      </div>
    </section>

    <!-- FILTER TOOLBAR -->
    <section class="filter-card">
      <div class="filter-grid">
        <!-- Search Keyword -->
        <div class="filter-col search-col">
          <label class="filter-label"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
          <div class="input-icon-wrap">
            <i class="bi bi-search icon-left"></i>
            <input 
              v-model="searchQuery" 
              @input="onSearchInput"
              type="text" 
              class="form-input-custom" 
              placeholder="Tên tour, điểm tập trung..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Tour Dropdown Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-map me-1"></i>Tour</label>
          <select v-model="selectedTourId" @change="onFilterChange" class="form-select-custom">
            <option value="">Tất cả tour</option>
            <option v-for="t in toursList" :key="t.tour_id" :value="t.tour_id">
              {{ t.ten_tour }}
            </option>
          </select>
        </div>

        <!-- Guide Dropdown Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-person-badge me-1"></i>Hướng Dẫn Viên</label>
          <select v-model="selectedHdvId" @change="onFilterChange" class="form-select-custom">
            <option value="">Tất cả HDV</option>
            <option value="unassigned">Chưa phân bổ HDV</option>
            <option v-for="h in hdvList" :key="h.nhan_su_id" :value="h.nhan_su_id">
              {{ h.ho_ten }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-info-circle me-1"></i>Trạng thái</label>
          <select v-model="selectedStatus" @change="onFilterChange" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="ChoPhanBo">Đang chờ phân bổ</option>
            <option value="SapKhoiHanh">Sắp khởi hành</option>
            <option value="DangChay">Đang chạy</option>
            <option value="HoanThanh">Hoàn thành</option>
            <option value="Huy">Đã hủy</option>
          </select>
        </div>

        <!-- Date From -->
        <div class="filter-col date-col">
          <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Từ ngày</label>
          <input 
            v-model="fromDate" 
            @change="onFilterChange"
            type="date" 
            class="form-input-custom" 
          />
        </div>

        <!-- Date To -->
        <div class="filter-col date-col">
          <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Đến ngày</label>
          <input 
            v-model="toDate" 
            @change="onFilterChange"
            type="date" 
            class="form-input-custom" 
          />
        </div>

        <!-- Sort Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-arrow-down-up me-1"></i>Sắp xếp</label>
          <select v-model="sortBy" class="form-select-custom">
            <option value="date_asc">Khởi hành sớm nhất</option>
            <option value="date_desc">Khởi hành muộn nhất</option>
            <option value="occupancy_desc">Tỷ lệ lấp đầy cao nhất</option>
            <option value="occupancy_asc">Tỷ lệ lấp đầy thấp nhất</option>
          </select>
        </div>

        <!-- Page Size -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-grid-3x3 me-1"></i>Hiển thị</label>
          <select v-model="pageSize" class="form-select-custom">
            <option :value="12">12 lịch / trang</option>
            <option :value="24">24 lịch / trang</option>
            <option :value="48">48 lịch / trang</option>
            <option :value="0">Tất cả</option>
          </select>
        </div>

        <!-- Reset Button -->
        <div class="filter-col action-col">
          <button @click="resetFilters" class="btn-reset" title="Khôi phục bộ lọc">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- SCHEDULES GRID -->
    <section class="schedule-section">
      <div class="section-meta-bar">
        <span>Tổng cộng: <strong>{{ sortedAndFilteredSchedules.length }}</strong> lịch trình</span>
        <span v-if="isLoading" class="loading-tag">
          <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
        </span>
      </div>

      <div class="schedules-grid">
        <div 
          v-for="item in paginatedSchedules" 
          :key="item.id" 
          class="schedule-card"
          :class="getScheduleBorderClass(item)"
        >
          <!-- Date Badge -->
          <div class="date-badge-box">
            <span class="date-day">{{ formatDay(item.ngay_khoi_hanh) }}</span>
            <span class="date-month">{{ formatMonth(item.ngay_khoi_hanh) }}</span>
            <span class="date-year">{{ formatYear(item.ngay_khoi_hanh) }}</span>
          </div>

          <!-- Main Info -->
          <div class="schedule-content">
            <div class="card-top-bar">
              <div class="tour-id-group">
                <span class="tour-id-pill">#{{ item.id }}</span>
                <span v-if="item.tour_id" class="tour-code-pill">Tour #{{ item.tour_id }}</span>
              </div>

              <div class="status-warning-group">
                <!-- Status Pill -->
                <span class="status-pill" :class="getStatusClass(item)">
                  {{ getStatusText(item) }}
                </span>

                <!-- Warning Pill -->
                <span 
                  v-if="hasWarning(item)" 
                  class="warning-pill"
                  :title="getWarningTooltip(item)"
                >
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  {{ getWarningText(item) }}
                </span>
              </div>
            </div>

            <!-- Tour Title -->
            <h3 class="tour-title">{{ item.ten_tour || 'Chưa cập nhật tên tour' }}</h3>

            <!-- Details Grid -->
            <div class="details-grid">
              <div class="detail-item">
                <i class="bi bi-box-arrow-right text-primary"></i>
                <div>
                  <span class="detail-label">Khởi hành:</span>
                  <strong class="detail-val">{{ formatDate(item.ngay_khoi_hanh) }} ({{ item.gio_xuat_phat || '00:00' }})</strong>
                </div>
              </div>

              <div class="detail-item">
                <i class="bi bi-box-arrow-in-left text-success"></i>
                <div>
                  <span class="detail-label">Kết thúc:</span>
                  <strong class="detail-val">{{ formatDate(item.ngay_ket_thuc) }} ({{ item.gio_ket_thuc || '00:00' }})</strong>
                </div>
              </div>

              <div class="detail-item full-width" v-if="item.diem_tap_trung">
                <i class="bi bi-geo-alt-fill text-danger"></i>
                <div>
                  <span class="detail-label">Tập trung:</span>
                  <strong class="detail-val">{{ item.diem_tap_trung }}</strong>
                </div>
              </div>

              <div class="detail-item">
                <i class="bi bi-people text-info"></i>
                <div>
                  <span class="detail-label">Sức chứa:</span>
                  <strong class="detail-val">{{ item.so_cho || 50 }} chỗ</strong>
                </div>
              </div>

              <div class="detail-item">
                <i class="bi bi-person-badge text-warning"></i>
                <div>
                  <span class="detail-label">Nhân sự HDV:</span>
                  <strong class="detail-val" :class="Number(item.so_nhan_su) > 0 ? 'text-success' : 'text-danger'">
                    {{ item.ten_hdv || (Number(item.so_nhan_su) > 0 ? (item.so_nhan_su + ' nhân sự') : 'Chưa phân bổ') }}
                  </strong>
                </div>
              </div>
            </div>

            <!-- OCCUPANCY PROGRESS BAR -->
            <div class="occupancy-wrap">
              <div class="occupancy-labels">
                <span class="occupancy-title">
                  <i class="bi bi-people-fill text-gold me-1"></i>Tình trạng chỗ:
                  <strong>{{ item.so_khach_da_dat || 0 }} / {{ item.so_cho || 50 }} khách</strong>
                </span>
                <span class="occupancy-pct" :class="getOccupancyClass(item.ty_le_lap_day)">
                  {{ item.ty_le_lap_day || 0 }}% lấp đầy
                </span>
              </div>
              <div class="occupancy-bar-track">
                <div 
                  class="occupancy-bar-fill" 
                  :class="getOccupancyBarClass(item.ty_le_lap_day)"
                  :style="{ width: Math.min(100, item.ty_le_lap_day || 0) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Card Action Footer -->
            <div class="card-footer-actions">
              <button 
                @click="openPassengersModal(item)" 
                class="btn-card btn-detail"
                title="Xem danh sách khách và điểm danh nhanh"
              >
                <i class="bi bi-people-fill"></i>
                <span>Khách Đoàn</span>
              </button>

              <button 
                @click="openAssignModal(item)" 
                class="btn-card btn-assign"
                title="Gán hoặc thay đổi hướng dẫn viên nhanh"
              >
                <i class="bi bi-person-gear"></i>
                <span>Gán HDV</span>
              </button>

              <button 
                @click="openStatusModal(item)"
                class="btn-card btn-status"
                title="Cập nhật nhanh trạng thái chuyến đi"
              >
                <i class="bi bi-arrow-repeat"></i>
                <span>Trạng Thái</span>
              </button>

              <button 
                @click="openEditModal(item)"
                class="btn-card btn-edit"
                title="Sửa nhanh thông tin khởi hành, điểm tập trung, số chỗ"
              >
                <i class="bi bi-pencil"></i>
                <span>Sửa</span>
              </button>

              <a 
                :href="'index.php?act=admin/danhSachKhachTheoTour&lich_id=' + item.id" 
                class="btn-card btn-manifest"
                title="In danh sách hành khách đi tour"
                target="_blank"
              >
                <i class="bi bi-printer"></i>
                <span>In Đoàn</span>
              </a>

              <a 
                :href="'index.php?act=admin/phanPhongKhachSan&lich_khoi_hanh_id=' + item.id" 
                class="btn-card btn-room"
                title="Phân bổ phòng khách sạn cho đoàn"
              >
                <i class="bi bi-building"></i>
                <span>Phòng</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="sortedAndFilteredSchedules.length === 0 && !isLoading" class="empty-box">
          <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">Không có lịch khởi hành nào</h4>
          <p class="text-secondary">Hãy thay đổi bộ lọc tìm kiếm hoặc tạo lịch khởi hành mới.</p>
        </div>
      </div>

      <!-- PAGINATION BAR -->
      <div v-if="totalPages > 1" class="schedule-pagination-bar">
        <div class="pagination-info">
          Hiển thị trang <strong>{{ currentPage }}</strong> / <strong>{{ totalPages }}</strong> 
          (Tổng số <strong>{{ sortedAndFilteredSchedules.length }}</strong> lịch trình)
        </div>
        <div class="pagination-controls">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage <= 1" 
            class="btn-pag-nav"
          >
            <i class="bi bi-chevron-left"></i> Trước
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
            Sau <i class="bi bi-chevron-right"></i>
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
                    <p>Chưa hoàn tất HDV hoặc dịch vụ</p>
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
                    <p>Đã sẵn sàng trước ngày xuất phát</p>
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
                    <p>Đoàn đang di chuyển trên tour</p>
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
                    <p>Kết thúc tour, chuyển sang quyết toán</p>
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

          <div class="modal-footer">
            <button @click="isEditModalOpen = false" class="btn-modal-cancel">Hủy bỏ</button>
            <button @click="submitQuickEdit" class="btn-modal-submit" :disabled="isSavingEdit">
              <i class="bi" :class="isSavingEdit ? 'bi-hourglass-split' : 'bi-save-fill'"></i>
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
.vue-admin-schedule-manage {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* PAGE HEADER */
.page-header {
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
  padding: 1.75rem 2rem;
  border-radius: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(14px);
  position: relative;
  overflow: hidden;
}
.header-left { position: relative; z-index: 1; }
.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(212, 175, 55, 0.15);
  color: #fde047;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.3rem 0.65rem;
  border-radius: 2rem;
  border: 1px solid rgba(212, 175, 55, 0.3);
  margin-bottom: 0.5rem;
}
.header-title { font-size: 1.75rem; font-weight: 800; margin: 0; color: #ffffff; }
.header-subtitle { font-size: 0.95rem; color: #94a3b8; margin: 0.25rem 0 0; }

.badge-tour-filter {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: rgba(212, 175, 55, 0.2);
  color: #fde047;
  border: 1px solid rgba(212, 175, 55, 0.4);
  padding: 0.2rem 0.55rem;
  border-radius: 1rem;
  font-size: 0.78rem;
  font-weight: 700;
}
.btn-clear-tour {
  background: transparent;
  border: none;
  color: #fde047;
  font-size: 1rem;
  line-height: 1;
  padding: 0;
  cursor: pointer;
  opacity: 0.75;
  transition: opacity 0.15s;
}
.btn-clear-tour:hover { opacity: 1; }

.btn-create {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  padding: 0.75rem 1.25rem;
  border-radius: 0.65rem;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s;
}
.btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45); color: #0b1120; }

/* STATS GRID */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1.15rem;
}
.stat-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-left-width: 4px;
  backdrop-filter: blur(12px);
  transition: transform 0.2s, box-shadow 0.2s;
}
.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(255, 255, 255, 0.15);
}
.stat-total { border-left-color: #6366f1; }
.stat-upcoming { border-left-color: #0284c7; }
.stat-ongoing { border-left-color: #10b981; }
.stat-completed { border-left-color: #64748b; }
.stat-pending { border-left-color: #f59e0b; }

.stat-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 0.35rem; letter-spacing: 0.03em; }
.stat-num { font-size: 1.75rem; font-weight: 800; color: #ffffff; line-height: 1; }
.stat-icon-box {
  width: 46px;
  height: 46px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}
.stat-total .stat-icon-box { background: rgba(99, 102, 241, 0.16); color: #818cf8; }
.stat-upcoming .stat-icon-box { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.stat-ongoing .stat-icon-box { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.stat-completed .stat-icon-box { background: rgba(255, 255, 255, 0.08); color: #94a3b8; }
.stat-pending .stat-icon-box { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }

/* FILTER CARD */
.filter-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.25rem 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
}
.filter-grid {
  display: grid;
  grid-template-columns: 2fr 1.2fr 1fr 1fr auto;
  gap: 1rem;
  align-items: flex-end;
}
.filter-col { display: flex; flex-direction: column; gap: 0.4rem; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: #cbd5e1; }
.input-icon-wrap { position: relative; display: flex; align-items: center; }
.icon-left { position: absolute; left: 0.85rem; color: #94a3b8; font-size: 0.9rem; }
.form-input-custom, .form-select-custom {
  width: 100%;
  height: 44px;
  padding: 0 0.85rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.9rem;
  color: #ffffff;
  outline: none;
  background-color: rgba(11, 17, 32, 0.85);
  transition: all 0.2s;
}
.search-col .form-input-custom { padding-left: 2.5rem; padding-right: 2.2rem; }
.form-input-custom:focus, .form-select-custom:focus {
  border-color: #d4af37;
  background-color: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}
.form-input-custom::placeholder {
  color: #64748b;
}
.btn-clear-search {
  position: absolute;
  right: 0.6rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.1rem;
  cursor: pointer;
}
.btn-reset {
  height: 44px;
  width: 44px;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-reset:hover {
  background: rgba(212, 175, 55, 0.2);
  border-color: #d4af37;
  color: #fde047;
}

/* SCHEDULE SECTION */
.schedule-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.section-meta-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #cbd5e1;
  padding: 0 0.5rem;
}
.loading-tag { color: #38bdf8; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* SCHEDULES GRID */
.schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(460px, 1fr));
  gap: 1.25rem;
}

.schedule-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  display: flex;
  overflow: hidden;
  border-left-width: 4px;
  backdrop-filter: blur(12px);
  transition: transform 0.2s, box-shadow 0.2s;
}
.schedule-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(255, 255, 255, 0.15);
}

.border-sky { border-left-color: #0284c7; }
.border-emerald { border-left-color: #10b981; }
.border-amber { border-left-color: #f59e0b; }
.border-rose { border-left-color: #f43f5e; }
.border-slate { border-left-color: #64748b; }

/* Date Box */
.date-badge-box {
  width: 95px;
  background: rgba(11, 17, 32, 0.9);
  border-right: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  flex-shrink: 0;
}
.date-day { font-size: 1.85rem; font-weight: 800; color: #ffffff; line-height: 1; }
.date-month { font-size: 0.85rem; font-weight: 700; color: #d4af37; text-transform: uppercase; margin-top: 0.25rem; }
.date-year { font-size: 0.72rem; color: #94a3b8; }

/* Content */
.schedule-content {
  flex: 1;
  padding: 1.25rem 1.35rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}
.card-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.tour-id-group { display: flex; gap: 0.4rem; align-items: center; }
.tour-id-pill {
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  font-weight: 700;
  font-size: 0.78rem;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
}
.tour-code-pill {
  background: rgba(14, 165, 233, 0.18);
  color: #7dd3fc;
  font-weight: 600;
  font-size: 0.78rem;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  border: 1px solid rgba(14, 165, 233, 0.35);
}

.status-warning-group { display: flex; gap: 0.4rem; align-items: center; flex-wrap: wrap; }
.status-pill {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}
.st-upcoming { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.st-ongoing { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.st-completed { background: rgba(255, 255, 255, 0.08); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.12); }
.st-pending { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }

.warning-pill {
  display: inline-flex;
  align-items: center;
  background: rgba(239, 68, 68, 0.18);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.35);
  font-size: 0.73rem;
  font-weight: 700;
  padding: 0.25rem 0.55rem;
  border-radius: 2rem;
}

.tour-title {
  font-size: 1.05rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  line-height: 1.35;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem 1rem;
  background: rgba(11, 17, 32, 0.6);
  padding: 0.85rem 1rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.06);
}
.detail-item {
  display: flex;
  align-items: flex-start;
  gap: 0.45rem;
  font-size: 0.82rem;
}
.detail-item.full-width { grid-column: 1 / -1; }
.detail-label { color: #94a3b8; display: block; font-size: 0.75rem; }
.detail-val { color: #cbd5e1; }

.card-footer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  margin-top: auto;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.06);
}
.btn-card {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.45rem 0.85rem;
  border-radius: 0.45rem;
  font-size: 0.82rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-detail { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.25); }
.btn-detail:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }
.btn-assign { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }
.btn-assign:hover { background: rgba(16, 185, 129, 0.25); color: #ffffff; }
.btn-status { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); cursor: pointer; }
.btn-status:hover { background: rgba(245, 158, 11, 0.25); color: #ffffff; }
.btn-edit { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.25); cursor: pointer; }
.btn-edit:hover { background: rgba(139, 92, 246, 0.25); color: #ffffff; }
.btn-manifest { background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.25); }
.btn-manifest:hover { background: rgba(6, 182, 212, 0.25); color: #ffffff; }
.btn-room { background: rgba(212, 175, 55, 0.15); color: #fde047; border: 1px solid rgba(212, 175, 55, 0.25); }
.btn-room:hover { background: rgba(212, 175, 55, 0.25); color: #ffffff; }

/* OCCUPANCY BAR */
.occupancy-wrap {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  background: rgba(11, 17, 32, 0.4);
  padding: 0.65rem 0.85rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
}
.occupancy-labels {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
}
.occupancy-title { color: #94a3b8; }
.occupancy-title strong { color: #f8fafc; font-weight: 700; margin-left: 0.25rem; }
.occupancy-pct { font-weight: 800; font-size: 0.78rem; }
.pct-full { color: #f43f5e; }
.pct-high { color: #f59e0b; }
.pct-med { color: #38bdf8; }
.pct-low { color: #34d399; }

.occupancy-bar-track {
  width: 100%;
  height: 6px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  overflow: hidden;
}
.occupancy-bar-fill {
  height: 100%;
  border-radius: 1rem;
  transition: width 0.3s ease;
}
.bar-rose { background: linear-gradient(90deg, #f43f5e, #fb7185); }
.bar-amber { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.bar-sky { background: linear-gradient(90deg, #0284c7, #38bdf8); }
.bar-emerald { background: linear-gradient(90deg, #059669, #34d399); }

.st-cancelled { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }

/* TOAST NOTIFICATION */
.toast-popup {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1.25rem;
  border-radius: 0.75rem;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(16px);
}
.toast-popup.success {
  background: rgba(6, 78, 59, 0.92);
  border: 1px solid #10b981;
  color: #ecfdf5;
}
.toast-popup.error {
  background: rgba(127, 29, 29, 0.92);
  border: 1px solid #ef4444;
  color: #fef2f2;
}
.toast-close {
  background: transparent;
  border: none;
  color: inherit;
  font-size: 1.15rem;
  cursor: pointer;
  padding: 0 0.25rem;
}

/* MODAL OVERLAY & CARD */
.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 9000;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal-card {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 1.25rem;
  width: 100%;
  max-width: 520px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}
.modal-card.modal-lg { max-width: 720px; }
.modal-header {
  padding: 1.25rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-title { font-size: 1.15rem; font-weight: 800; color: #ffffff; margin: 0; }
.modal-close-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
}
.modal-body { padding: 1.5rem; }
.modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}
.btn-modal-cancel {
  padding: 0.6rem 1.15rem;
  border-radius: 0.6rem;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background: rgba(255, 255, 255, 0.05);
  color: #cbd5e1;
  font-weight: 600;
  cursor: pointer;
}
.btn-modal-submit {
  padding: 0.6rem 1.25rem;
  border-radius: 0.6rem;
  border: none;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.btn-modal-submit:disabled { opacity: 0.6; cursor: not-allowed; }

.schedule-summary-box {
  background: rgba(11, 17, 32, 0.6);
  padding: 1rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.06);
}
.summary-tour-title { font-weight: 800; color: #f8fafc; font-size: 1rem; margin-bottom: 0.25rem; }
.summary-dates { font-size: 0.85rem; color: #94a3b8; }

.status-options-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.65rem;
  margin-top: 0.5rem;
}
.status-option-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem 1rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(11, 17, 32, 0.5);
  cursor: pointer;
  transition: all 0.2s;
}
.status-option-card:hover { border-color: rgba(212, 175, 55, 0.4); background: rgba(11, 17, 32, 0.8); }
.status-option-card.active { border-color: #d4af37; background: rgba(212, 175, 55, 0.12); }
.opt-icon { font-size: 1.5rem; }
.opt-text strong { display: block; font-size: 0.92rem; color: #ffffff; }
.opt-text p { margin: 0; font-size: 0.78rem; color: #94a3b8; }

.empty-box {
  grid-column: 1 / -1;
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 4rem 2rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
}

/* PAGINATION BAR */
.schedule-pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: rgba(22, 31, 46, 0.8);
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  margin-top: 1rem;
}
.pagination-info {
  color: #94a3b8;
  font-size: 0.9rem;
}
.pagination-info strong {
  color: #f1f5f9;
}
.pagination-controls {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.btn-pag-nav, .btn-pag-num {
  padding: 0.45rem 0.85rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  font-weight: 600;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(15, 23, 42, 0.6);
  color: #cbd5e1;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-pag-nav:hover:not(:disabled), .btn-pag-num:hover {
  border-color: #d4af37;
  color: #d4af37;
  background: rgba(212, 175, 55, 0.12);
}
.btn-pag-num.active {
  background: #d4af37;
  color: #0f172a;
  border-color: #d4af37;
  font-weight: 800;
}
.btn-pag-nav:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* CONFLICT ALERT BOX */
.conflict-alert-box {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.4);
  border-radius: 0.75rem;
  padding: 1rem;
}
.conflict-header {
  color: #fca5a5;
  font-size: 0.95rem;
  margin-bottom: 0.35rem;
}
.conflict-desc {
  font-size: 0.85rem;
  color: #fecaca;
  margin-bottom: 0.5rem;
}
.conflict-list {
  margin: 0 0 0.5rem 1.25rem;
  padding: 0;
  font-size: 0.82rem;
  color: #fee2e2;
}
.btn-modal-warning {
  padding: 0.6rem 1.25rem;
  border-radius: 0.6rem;
  border: none;
  background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);
  color: #ffffff;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
}
.btn-modal-warning:disabled { opacity: 0.6; cursor: not-allowed; }

/* PASSENGER MANIFEST TABLE */
.table-manifest-bookings {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}
.table-manifest-bookings th {
  background: rgba(15, 23, 42, 0.85);
  color: #94a3b8;
  padding: 0.65rem 0.85rem;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-weight: 700;
}
.table-manifest-bookings td {
  padding: 0.65rem 0.85rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  color: #e2e8f0;
}
.badge-bk {
  background: rgba(56, 189, 248, 0.2);
  color: #38bdf8;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  font-weight: 700;
  font-size: 0.75rem;
}
.badge-status-sm {
  background: rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  font-size: 0.75rem;
}
.badge-checkin-yes {
  background: rgba(34, 197, 94, 0.2);
  color: #4ade80;
  padding: 0.25rem 0.6rem;
  border-radius: 0.35rem;
  font-weight: 700;
  font-size: 0.75rem;
}
.badge-checkin-no {
  background: rgba(245, 158, 11, 0.2);
  color: #fcd34d;
  padding: 0.25rem 0.6rem;
  border-radius: 0.35rem;
  font-weight: 600;
  font-size: 0.75rem;
}
.btn-assign {
  background: rgba(212, 175, 55, 0.15);
  color: #fde047;
  border: 1px solid rgba(212, 175, 55, 0.3);
}
.btn-assign:hover {
  background: rgba(212, 175, 55, 0.3);
  color: #ffffff;
}
.cascading-options-wrap {
  background: rgba(11, 17, 32, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 0.65rem;
  padding: 0.85rem 1rem;
}

@media (max-width: 992px) {
  .filter-grid { grid-template-columns: 1fr 1fr; }
  .schedules-grid { grid-template-columns: 1fr; }
}
@media (max-width: 600px) {
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .filter-grid { grid-template-columns: 1fr; }
  .schedule-card { flex-direction: column; }
  .date-badge-box { width: 100%; flex-direction: row; gap: 0.5rem; border-right: none; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
  .details-grid { grid-template-columns: 1fr; }
  .card-footer-actions { flex-wrap: wrap; }
}
</style>
