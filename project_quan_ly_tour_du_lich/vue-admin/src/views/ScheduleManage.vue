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

    <!-- 1. PAGE HEADER HERO BANNER (MATCHING IMAGE 2) -->
    <header class="page-header-hero">
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

      <!-- CENTER TRAVEL & CALENDAR ILLUSTRATION (MATCHING IMAGE 2) -->
      <div class="hero-illustration">
        <svg width="240" height="110" viewBox="0 0 240 110" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Background soft glow circles -->
          <ellipse cx="140" cy="55" rx="85" ry="42" fill="#eff6ff" />
          <circle cx="185" cy="45" r="35" fill="#f0f9ff" />
          
          <!-- Framed Landscape Photo (Mountains & Sea) -->
          <g filter="drop-shadow(0px 4px 10px rgba(37, 99, 235, 0.08))">
            <rect x="25" y="42" width="56" height="42" rx="6" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
            <rect x="28" y="45" width="50" height="36" rx="4" fill="#60a5fa" />
            <!-- Mountain shapes -->
            <path d="M28 81l14-16 10 10 12-14 14 20H28z" fill="#3b82f6"/>
            <path d="M42 65l10 10 12-14 14 20H55z" fill="#93c5fd" opacity="0.6"/>
            <!-- Sun in photo -->
            <circle cx="68" cy="53" r="4" fill="#fef08a"/>
          </g>

          <!-- Desk Calendar Card -->
          <g filter="drop-shadow(0px 8px 16px rgba(37, 99, 235, 0.12))">
            <rect x="75" y="16" width="82" height="78" rx="8" fill="#ffffff" stroke="#dbeafe" stroke-width="1.5"/>
            <!-- Blue Top Header Bar of Calendar -->
            <path d="M75 24a8 8 0 0 1 8-8h66a8 8 0 0 1 8 8v12H75V24z" fill="#3b82f6"/>
            <!-- Calendar spiral rings -->
            <rect x="87" y="12" width="4" height="8" rx="2" fill="#94a3b8"/>
            <rect x="105" y="12" width="4" height="8" rx="2" fill="#94a3b8"/>
            <rect x="123" y="12" width="4" height="8" rx="2" fill="#94a3b8"/>
            <rect x="141" y="12" width="4" height="8" rx="2" fill="#94a3b8"/>
            <!-- Calendar grid cells -->
            <rect x="83" y="42" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="99" y="42" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="115" y="42" width="10" height="8" rx="2" fill="#3b82f6"/>
            <rect x="131" y="42" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="147" y="42" width="10" height="8" rx="2" fill="#eff6ff"/>

            <rect x="83" y="54" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="99" y="54" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="115" y="54" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="131" y="54" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="147" y="54" width="10" height="8" rx="2" fill="#eff6ff"/>

            <rect x="83" y="66" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="99" y="66" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="115" y="66" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="131" y="66" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="147" y="66" width="10" height="8" rx="2" fill="#eff6ff"/>

            <rect x="83" y="78" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="99" y="78" width="10" height="8" rx="2" fill="#eff6ff"/>
            <rect x="115" y="78" width="10" height="8" rx="2" fill="#eff6ff"/>
          </g>

          <!-- Blue Location Pin Marker -->
          <g filter="drop-shadow(0px 4px 8px rgba(37, 99, 235, 0.15))">
            <path d="M185 46c-5.5 0-10 4.5-10 10 0 7.5 10 18 10 18s10-10.5 10-18c0-5.5-4.5-10-10-10z" fill="#60a5fa"/>
            <circle cx="185" cy="56" r="4" fill="#ffffff"/>
          </g>

          <!-- Small Flying Paper Plane -->
          <path d="M198 22l14 4-8 8-1-5-5-2v-5z" fill="#3b82f6"/>
          <!-- Dotted flight trail -->
          <path d="M175 32c10-2 15-5 23-10" stroke="#93c5fd" stroke-width="1.2" stroke-dasharray="2 2"/>
        </svg>
      </div>

      <!-- RIGHT ACTIONS (MATCHING IMAGE 2) -->
      <div class="hero-right">
        <div class="current-time-display">
          <i class="bi bi-calendar-event me-2"></i>
          <span>{{ currentTimeString || 'Thứ Ba, 22/09/2026 21:48' }}</span>
        </div>
        <a href="index.php?act=lichKhoiHanh/create" class="btn-hero-create">
          <i class="bi bi-plus-lg"></i>
          <span>Tạo lịch khởi hành</span>
        </a>
      </div>
    </header>

    <!-- 2. 5 STATS CARDS (MATCHING IMAGE 2) -->
    <section class="stats-grid">
      <!-- Card 1: Tổng số lịch -->
      <div class="stat-card">
        <div class="stat-main">
          <div class="stat-icon-box bg-blue-light text-blue">
            <i class="bi bi-calendar3"></i>
          </div>
          <div class="stat-content">
            <span class="stat-label">Tổng số lịch</span>
            <strong class="stat-num">{{ stats.total }}</strong>
            <div class="stat-trend">
              <span class="trend-green">↑ +10%</span>
              <span class="sub-muted">so với tháng trước</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Blue Sparkline Curve -->
          <svg width="48" height="24" viewBox="0 0 48 24" fill="none">
            <path d="M2 20 C12 20, 16 12, 26 12 C36 12, 38 4, 46 4" stroke="#3b82f6" stroke-width="2.2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>

      <!-- Card 2: Sắp khởi hành -->
      <div class="stat-card">
        <div class="stat-main">
          <div class="stat-icon-box bg-green-light text-green">
            <i class="bi bi-person-fill"></i>
          </div>
          <div class="stat-content">
            <span class="stat-label">Sắp khởi hành</span>
            <strong class="stat-num">{{ stats.upcoming }}</strong>
            <div class="stat-trend">
              <span class="sub-muted">* Không thay đổi</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Soft Curve -->
          <svg width="48" height="24" viewBox="0 0 48 24" fill="none">
            <path d="M2 18 C12 18, 20 14, 30 14 C38 14, 40 10, 46 8" stroke="#cbd5e1" stroke-width="2.2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>

      <!-- Card 3: Đang chạy -->
      <div class="stat-card">
        <div class="stat-main">
          <div class="stat-icon-box bg-purple-light text-purple">
            <i class="bi bi-airplane-fill"></i>
          </div>
          <div class="stat-content">
            <span class="stat-label">Đang chạy</span>
            <strong class="stat-num">{{ stats.ongoing }}</strong>
            <div class="stat-trend">
              <span class="sub-muted">* Không thay đổi</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Purple Curve -->
          <svg width="48" height="24" viewBox="0 0 48 24" fill="none">
            <path d="M2 18 C12 18, 20 14, 30 14 C38 14, 40 10, 46 8" stroke="#cbd5e1" stroke-width="2.2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>

      <!-- Card 4: Đã hoàn thành -->
      <div class="stat-card">
        <div class="stat-main">
          <div class="stat-icon-box bg-amber-light text-amber">
            <i class="bi bi-clock-fill"></i>
          </div>
          <div class="stat-content">
            <span class="stat-label">Đã hoàn thành</span>
            <strong class="stat-num">{{ stats.completed }}</strong>
            <div class="stat-trend">
              <span class="trend-green">↑ +10%</span>
              <span class="sub-muted">so với tháng trước</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Mini 4-bar Chart -->
          <svg width="34" height="22" viewBox="0 0 34 22" fill="none">
            <rect x="2" y="16" width="5" height="6" rx="1.5" fill="#d1fae5"/>
            <rect x="10" y="12" width="5" height="10" rx="1.5" fill="#a7f3d0"/>
            <rect x="18" y="7" width="5" height="15" rx="1.5" fill="#6ee7b7"/>
            <rect x="26" y="2" width="5" height="20" rx="1.5" fill="#34d399"/>
          </svg>
        </div>
      </div>

      <!-- Card 5: Chờ phản hồi -->
      <div class="stat-card">
        <div class="stat-main">
          <div class="stat-icon-box bg-red-light text-red">
            <i class="bi bi-x-circle-fill"></i>
          </div>
          <div class="stat-content">
            <span class="stat-label">Chờ phản hồi</span>
            <strong class="stat-num">{{ stats.pending }}</strong>
            <div class="stat-trend">
              <span class="sub-muted">* Không thay đổi</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Soft Curve -->
          <svg width="48" height="24" viewBox="0 0 48 24" fill="none">
            <path d="M2 18 C12 18, 20 14, 30 14 C38 14, 40 10, 46 8" stroke="#cbd5e1" stroke-width="2.2" stroke-linecap="round"/>
          </svg>
        </div>
      </div>
    </section>

    <!-- 3. FILTER TOOLBAR (MATCHING IMAGE 2) -->
    <section class="filter-section">
      <div class="filter-header">
        <i class="bi bi-funnel-fill text-primary me-2"></i>Tìm kiếm &amp; Lọc
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
              placeholder="Tìm theo tên tour, mã tour, ..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Tour Dropdown Filter -->
        <div class="filter-col">
          <div class="select-icon-wrap">
            <i class="bi bi-book icon-left"></i>
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
            <i class="bi bi-person icon-left"></i>
            <select v-model="selectedHdvId" @change="onFilterChange" class="form-select-custom">
              <option value="">Tất cả hướng</option>
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

        <!-- Date Picker -->
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

    <!-- 4. SCHEDULES DATA TABLE (MATCHING IMAGE 2) -->
    <section class="schedule-section">
      <!-- Section Meta Bar -->
      <div class="section-meta-bar">
        <div class="meta-left">
          <h4 class="table-section-title">Danh sách lịch khởi hành</h4>
          <span v-if="isLoading" class="loading-tag ms-3">
            <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
          </span>
        </div>
        <div class="meta-right">
          <span class="per-page-caption">Hiển thị</span>
          <select v-model="pageSize" class="per-page-select">
            <option :value="10">10</option>
            <option :value="20">20</option>
            <option :value="50">50</option>
          </select>
          <span class="per-page-caption ms-1">bản ghi</span>
          <div class="view-toggles ms-3">
            <button class="btn-view-toggle"><i class="bi bi-grid-fill"></i></button>
            <button class="btn-view-toggle active"><i class="bi bi-list-ul"></i></button>
          </div>
        </div>
      </div>

      <!-- Data Table -->
      <div class="table-responsive glass-table-wrap">
        <table class="table-glass">
          <thead>
            <tr>
              <th class="th-checkbox text-center" style="width: 44px">
                <input type="checkbox" v-model="selectAll" class="custom-checkbox" />
              </th>
              <th style="width: 50px">#</th>
              <th style="min-width: 260px">Tên tour</th>
              <th>Khởi hành</th>
              <th>Kết thúc</th>
              <th class="text-center">Ngày khởi hành</th>
              <th class="text-center">Số lượng</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center">Trạng thái</th>
              <th class="text-center" style="width: 140px;">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in paginatedSchedules" :key="item.id" class="table-row-hover">
              <!-- Checkbox -->
              <td class="text-center td-checkbox">
                <input type="checkbox" :value="item.id" v-model="selectedSchedules" class="custom-checkbox" />
              </td>

              <!-- Number -->
              <td class="text-secondary fw-semibold">{{ (currentPage - 1) * pageSize + index + 1 }}</td>

              <!-- Tour Info: Thumbnail + Name + Code -->
              <td>
                <div class="tour-info-cell">
                  <div class="tour-thumb">
                    <img :src="getTourThumbnail(item)" alt="Tour img" @error="handleImgError" />
                  </div>
                  <div class="tour-details">
                    <div class="tour-name">{{ item.ten_tour || 'Chưa cập nhật tên tour' }}</div>
                    <div class="tour-code">{{ getTourCode(item) }}</div>
                  </div>
                </div>
              </td>

              <!-- Departure Location -->
              <td class="location-cell">
                {{ getDeparture(item) }}
              </td>

              <!-- Destination Location -->
              <td class="location-cell">
                {{ getDestination(item) }}
              </td>

              <!-- Departure Date -->
              <td class="text-center date-cell">
                {{ formatDate(item.ngay_khoi_hanh) }}
              </td>

              <!-- Occupancy (Pax / Total) -->
              <td class="text-center occupancy-cell">
                <strong>{{ item.so_khach_da_dat || 0 }}</strong>/{{ item.so_cho || 50 }}
              </td>

              <!-- Operation Status -->
              <td class="text-center">
                <span class="status-badge" :class="getStatusClass(item)">
                  {{ getStatusText(item) }}
                </span>
              </td>

              <!-- Guide/Allocation Confirmation Status -->
              <td class="text-center">
                <span class="status-badge" :class="getGuideStatusClass(item)">
                  {{ getGuideStatusText(item) }}
                </span>
              </td>

              <!-- Action Buttons -->
              <td class="actions-cell text-center">
                <button @click="openPassengersModal(item)" class="action-btn btn-view" title="Xem danh sách hành khách">
                  <i class="bi bi-eye"></i>
                </button>
                <button @click="openEditModal(item)" class="action-btn btn-edit" title="Sửa lịch khởi hành">
                  <i class="bi bi-pencil"></i>
                </button>
                <button @click="openStatusModal(item)" class="action-btn btn-more" title="Đổi trạng thái vận hành">
                  <i class="bi bi-three-dots-vertical"></i>
                </button>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="sortedAndFilteredSchedules.length === 0 && !isLoading">
              <td colspan="10" class="text-center py-5">
                <div class="empty-state">
                  <i class="bi bi-inbox fs-2 text-muted mb-2"></i>
                  <p class="mt-2 text-secondary">Không tìm thấy lịch khởi hành nào phù hợp với bộ lọc.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 5. TABLE FOOTER BAR (MATCHING IMAGE 2) -->
      <div class="table-footer-bar">
        <div class="footer-info">
          Hiển thị 1 - {{ paginatedSchedules.length }} trong tổng số {{ sortedAndFilteredSchedules.length }} lịch khởi hành
        </div>
        <div class="footer-pagination">
          <button 
            @click="changePage(currentPage - 1)" 
            :disabled="currentPage <= 1" 
            class="btn-page btn-page-prev"
          >
            <i class="bi bi-chevron-left"></i>
          </button>
          <button 
            v-for="p in visiblePages" 
            :key="p" 
            @click="changePage(p)" 
            class="btn-page"
            :class="{ 'btn-page-active': p === currentPage }"
          >
            {{ p }}
          </button>
          <button 
            @click="changePage(currentPage + 1)" 
            :disabled="currentPage >= totalPages" 
            class="btn-page btn-page-next"
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
              <i class="bi bi-arrow-repeat text-primary me-2"></i>
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
              <i class="bi bi-person-gear text-primary me-2"></i>
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
                Bạn có thể chọn HDV khác hoặc ghi đè phân bổ nếu HDV có thể hỗ trợ đoàn ghép.
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

    <!-- MODAL XEM KHÁCH ĐOÀN NHANH -->
    <transition name="fade">
      <div v-if="isPassengersModalOpen" class="modal-overlay" @click.self="isPassengersModalOpen = false">
        <div class="modal-card modal-lg">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-people-fill text-primary me-2"></i>
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
              <i class="bi bi-arrow-repeat spin fs-2 text-primary"></i>
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
              <i class="bi bi-pencil-square text-primary me-2"></i>
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
import { ref, computed, onMounted, onUnmounted } from 'vue';

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

// Clock
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

// State
const isLoading = ref(false);
const searchQuery = ref('');
const selectedStatus = ref('');
const selectedTourId = ref('');
const selectedHdvId = ref('');
const fromDate = ref('');
const toDate = ref('');
const sortBy = ref('date_asc');
const pageSize = ref(10);
const currentPage = ref(1);
const selectedSchedules = ref([]);

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

const selectAll = computed({
  get() {
    return paginatedSchedules.value.length > 0 && selectedSchedules.value.length === paginatedSchedules.value.length;
  },
  set(val) {
    if (val) {
      selectedSchedules.value = paginatedSchedules.value.map(s => s.id);
    } else {
      selectedSchedules.value = [];
    }
  }
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
    const result = await res.json();
    if (result.success && result.data) {
      schedules.value = result.data.schedules || [];
      if (result.data.toursList) toursList.value = result.data.toursList;
      if (result.data.hdvList) hdvList.value = result.data.hdvList;
    }
  } catch (err) {
    console.error('Fetch schedules error:', err);
  } finally {
    isLoading.value = false;
  }
}

function getTourThumbnail(item) {
  if (item.hinh_anh) {
    if (item.hinh_anh.startsWith('http://') || item.hinh_anh.startsWith('https://')) {
      return item.hinh_anh;
    }
    return (window.__BASE_URL__ || '') + 'public/uploads/tours/' + item.hinh_anh;
  }
  return 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=120&auto=format&fit=crop&q=80';
}

function handleImgError(e) {
  e.target.src = 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=120&auto=format&fit=crop&q=80';
}

function getTourCode(item) {
  if (item.ma_tour) return '#' + item.ma_tour;
  const tid = item.tour_id || item.id || 1;
  return '#TO0' + tid;
}

function getDeparture(item) {
  if (item.diem_tap_trung && item.diem_tap_trung.trim()) {
    return item.diem_tap_trung.length > 15 ? 'Hà Nội' : item.diem_tap_trung;
  }
  return 'Hà Nội';
}

function getDestination(item) {
  const name = (item.ten_tour || '').toLowerCase();
  if (name.includes('hạ long') || name.includes('ha long')) return 'Hạ Long';
  if (name.includes('sapa')) return 'Sapa';
  if (name.includes('đà nẵng') || name.includes('da nang')) return 'Đà Nẵng';
  if (name.includes('hội an') || name.includes('hoi an')) return 'Hội An';
  if (name.includes('phú quốc') || name.includes('phu quoc')) return 'Phú Quốc';
  if (name.includes('đà lạt') || name.includes('da lat')) return 'Đà Lạt';
  if (name.includes('nha trang')) return 'Nha Trang';
  if (name.includes('tokyo') || name.includes('phú sĩ') || name.includes('nagoya')) return 'Tokyo';
  if (name.includes('sơn tây') || name.includes('son tay')) return 'Sơn Tây';
  
  if (item.ten_tour && item.ten_tour.includes('-')) {
    const parts = item.ten_tour.split('-');
    return parts[parts.length - 1].replace(/\d+.*$/g, '').trim() || 'Hạ Long';
  }
  return 'Điểm đến';
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const parts = dateStr.split(' ')[0].split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function getStatusClass(item) {
  if (item.trang_thai === 'Huy') return 'badge-op-cancelled';
  if (Number(item.so_nhan_su) === 0 || item.trang_thai === 'ChoPhanBo') return 'badge-op-pending';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'badge-op-upcoming';
    case 'DangChay': return 'badge-op-running';
    case 'HoanThanh': return 'badge-op-completed';
    default: return 'badge-op-upcoming';
  }
}

function getStatusText(item) {
  if (item.trang_thai === 'Huy') return 'Đã hủy';
  if (Number(item.so_nhan_su) === 0 || item.trang_thai === 'ChoPhanBo') return 'Chờ khởi hành';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'Sắp khởi hành';
    case 'DangChay': return 'Đang chạy';
    case 'HoanThanh': return 'Đã hoàn thành';
    default: return item.trang_thai || 'Sắp khởi hành';
  }
}

function getGuideStatusText(item) {
  return (Number(item.so_nhan_su) > 0 || item.ten_hdv) ? 'Đã xác nhận' : 'Chưa xác nhận';
}

function getGuideStatusClass(item) {
  return (Number(item.so_nhan_su) > 0 || item.ten_hdv) ? 'badge-cf-confirmed' : 'badge-cf-unconfirmed';
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
        lich_khoi_hanh_id: currentSchedule.value.id,
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
      fetchSchedules();
    } else if (data.has_conflict && data.conflicts) {
      assignConflicts.value = data.conflicts;
      showToast('Cảnh báo: HDV đang có lịch trùng!', 'error');
    } else {
      showToast(data.message || 'Lỗi khi phân bổ HDV', 'error');
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
  isLoadingPassengers.value = true;
  passengersData.value = { bookings: [], passengers: [] };
  isPassengersModalOpen.value = true;

  try {
    const res = await fetch(`index.php?act=lichKhoiHanh/apiSchedulePassengers&id=${item.id}`, {
      headers: { 'Accept': 'application/json' }
    });
    const data = await res.json();
    if (data.success) {
      passengersData.value = {
        bookings: data.bookings || [],
        passengers: data.passengers || []
      };
    }
  } catch (err) {
    console.error('Fetch passengers error:', err);
  } finally {
    isLoadingPassengers.value = false;
  }
}

// Quick Edit Modal Handlers
function openEditModal(item) {
  currentSchedule.value = item;
  editForm.value = {
    id: item.id,
    ngay_khoi_hanh: item.ngay_khoi_hanh || '',
    gio_xuat_phat: item.gio_xuat_phat ? item.gio_xuat_phat.substring(0, 5) : '08:00',
    ngay_ket_thuc: item.ngay_ket_thuc || '',
    gio_ket_thuc: item.gio_ket_thuc ? item.gio_ket_thuc.substring(0, 5) : '18:00',
    diem_tap_trung: item.diem_tap_trung || '',
    so_cho: item.so_cho || 50,
    trang_thai: item.trang_thai || 'SapKhoiHanh'
  };
  isEditModalOpen.value = true;
}

async function submitEditSchedule() {
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

onMounted(() => {
  updateClock();
  clockInterval = setInterval(updateClock, 60000);

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

onUnmounted(() => {
  clearInterval(clockInterval);
});
</script>

<style scoped>
.vue-admin-schedule-manage {
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
  padding: 0.5rem 0 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #0f172a;
}

/* 1. PAGE HEADER HERO */
.page-header-hero {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #ffffff;
  padding: 1.6rem 2.2rem;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  position: relative;
  overflow: hidden;
}

.hero-left {
  display: flex;
  flex-direction: column;
  max-width: 450px;
}

.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.45rem;
  width: fit-content;
}

.hero-title {
  font-size: 1.65rem;
  font-weight: 800;
  margin: 0;
  color: #0f172a;
  letter-spacing: -0.02em;
}

.hero-subtitle {
  font-size: 0.88rem;
  color: #64748b;
  margin: 0.35rem 0 0;
  line-height: 1.4;
}

.badge-tour-filter {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.6rem;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.btn-clear-tour {
  background: none;
  border: none;
  color: #2563eb;
  font-size: 1rem;
  line-height: 1;
  padding: 0;
  cursor: pointer;
}

.hero-illustration {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 992px) {
  .hero-illustration {
    display: none;
  }
}

.hero-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.85rem;
}

.current-time-display {
  display: inline-flex;
  align-items: center;
  background: #f8fafc;
  color: #334155;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 0.45rem 0.9rem;
  font-size: 0.82rem;
  font-weight: 600;
}

.current-time-display i {
  color: #2563eb;
}

.btn-hero-create {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: #2563eb;
  color: #ffffff;
  padding: 0.75rem 1.4rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.92rem;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-hero-create:hover {
  background: #1d4ed8;
  color: #ffffff;
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
  transform: translateY(-1px);
}

/* 2. STATS GRID (5 CARDS) */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 1.15rem;
}

@media (max-width: 1280px) {
  .stats-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.15rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}

.stat-main {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.stat-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
}

.bg-blue-light { background: #eff6ff; }
.text-blue { color: #2563eb; }

.bg-green-light { background: #ecfdf5; }
.text-green { color: #10b981; }

.bg-purple-light { background: #f5f3ff; }
.text-purple { color: #8b5cf6; }

.bg-amber-light { background: #fffbeb; }
.text-amber { color: #f59e0b; }

.bg-red-light { background: #fef2f2; }
.text-red { color: #ef4444; }

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.15rem;
}

.stat-num {
  font-size: 1.55rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
}

.stat-trend {
  font-size: 0.76rem;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  white-space: nowrap;
}

.trend-green {
  color: #10b981;
  font-weight: 700;
}

.sub-muted {
  color: #94a3b8;
}

.stat-graphic {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.9;
}

/* 3. FILTER SECTION */
.filter-section {
  background: #ffffff;
  border-radius: 14px;
  padding: 1.25rem 1.6rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}

.filter-header {
  font-size: 0.95rem;
  font-weight: 700;
  color: #0f172a;
  margin-bottom: 0.9rem;
  display: flex;
  align-items: center;
}

.filter-grid-inline {
  display: grid;
  grid-template-columns: 2fr 1.3fr 1.3fr 1.3fr 1.2fr auto;
  gap: 0.9rem;
  align-items: center;
}

@media (max-width: 1200px) {
  .filter-grid-inline {
    grid-template-columns: 1fr 1fr;
  }
}

.input-icon-wrap,
.select-icon-wrap {
  position: relative;
  width: 100%;
}

.icon-left {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.9rem;
  pointer-events: none;
}

.form-input-custom,
.form-select-custom {
  width: 100%;
  padding: 0.65rem 0.85rem 0.65rem 2.35rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-input-custom:focus,
.form-select-custom:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.btn-clear-search {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  padding: 0;
}

.btn-refresh {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.65rem 1.1rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #ffffff;
  color: #475569;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-refresh:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
}

/* 4. SCHEDULE SECTION & TABLE */
.schedule-section {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  overflow: hidden;
}

.section-meta-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid #e2e8f0;
  background: #ffffff;
}

.table-section-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.meta-right {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.per-page-caption {
  font-size: 0.85rem;
  color: #64748b;
}

.per-page-select {
  padding: 0.35rem 0.65rem;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.85rem;
  background: #ffffff;
  color: #0f172a;
}

.view-toggles {
  display: flex;
  gap: 0.25rem;
}

.btn-view-toggle {
  width: 32px;
  height: 32px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.btn-view-toggle.active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}

.table-responsive {
  overflow-x: auto;
}

.table-glass {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}

.table-glass th {
  padding: 0.95rem 1.15rem;
  background: #f8fafc;
  color: #475569;
  font-weight: 700;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

.table-glass td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #334155;
  background: #ffffff;
}

.table-row-hover:hover td {
  background: #f8fafc;
}

.custom-checkbox {
  width: 17px;
  height: 17px;
  border-radius: 4px;
  border: 1.5px solid #cbd5e1;
  cursor: pointer;
  accent-color: #2563eb;
}

.th-checkbox,
.td-checkbox {
  width: 44px;
  padding-left: 1.25rem !important;
  padding-right: 0.5rem !important;
}

/* Tour Info Cell */
.tour-info-cell {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.tour-thumb {
  width: 52px;
  height: 38px;
  border-radius: 6px;
  overflow: hidden;
  flex-shrink: 0;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
}

.tour-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.tour-details {
  display: flex;
  flex-direction: column;
}

.tour-name {
  font-weight: 700;
  color: #0f172a;
  font-size: 0.92rem;
}

.tour-code {
  font-size: 0.78rem;
  color: #94a3b8;
  font-weight: 600;
}

.location-cell {
  color: #334155;
  font-weight: 500;
}

.date-cell {
  color: #475569;
  font-weight: 500;
  white-space: nowrap;
}

.occupancy-cell {
  color: #334155;
  white-space: nowrap;
}

.occupancy-cell strong {
  color: #0f172a;
  font-weight: 700;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.28rem 0.85rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}

.badge-op-running {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.badge-op-upcoming {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}

.badge-op-pending {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.badge-op-completed {
  background: #f1f5f9;
  color: #334155;
  border: 1px solid #cbd5e1;
}

.badge-op-cancelled {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fca5a5;
}

.badge-cf-confirmed {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.badge-cf-unconfirmed {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fde68a;
}

/* Actions Cell */
.actions-cell {
  white-space: nowrap;
}

.action-btn {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  margin: 0 0.15rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.action-btn:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #cbd5e1;
}

/* 5. TABLE FOOTER BAR */
.table-footer-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #ffffff;
  border-top: 1px solid #f1f5f9;
}

.footer-info {
  font-size: 0.85rem;
  color: #64748b;
}

.footer-pagination {
  display: flex;
  align-items: center;
  gap: 0.35rem;
}

.btn-page {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-page:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-page-active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}

/* MODALS */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.6);
  backdrop-filter: blur(4px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.modal-card {
  background: #ffffff;
  border-radius: 16px;
  width: 100%;
  max-width: 520px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
  overflow: hidden;
  color: #0f172a;
}

.modal-card.modal-lg {
  max-width: 780px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title {
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  color: #0f172a;
}

.modal-close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #94a3b8;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  max-height: 75vh;
  overflow-y: auto;
}

.schedule-summary-box {
  background: #f8fafc;
  padding: 0.9rem 1.1rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.summary-tour-title {
  font-weight: 700;
  color: #0f172a;
  font-size: 0.95rem;
}

.summary-dates {
  font-size: 0.82rem;
  color: #64748b;
  margin-top: 0.25rem;
}

.status-options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.status-option-card {
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  padding: 0.75rem;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  cursor: pointer;
  background: #ffffff;
  transition: all 0.15s;
}

.status-option-card.active {
  border-color: #2563eb;
  background: #eff6ff;
}

.opt-text strong {
  display: block;
  font-size: 0.88rem;
  color: #0f172a;
}

.opt-text p {
  margin: 0;
  font-size: 0.75rem;
  color: #64748b;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.15rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: #f8fafc;
}

.btn-modal-cancel {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  text-decoration: none;
}

.btn-modal-submit {
  background: #2563eb;
  color: #ffffff;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}

.table-manifest-bookings {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.85rem;
}

.table-manifest-bookings th {
  background: #f8fafc;
  padding: 0.65rem 0.85rem;
  border-bottom: 1px solid #e2e8f0;
  color: #475569;
  font-weight: 600;
}

.table-manifest-bookings td {
  padding: 0.75rem 0.85rem;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
}

/* Toast */
.toast-popup {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  padding: 0.85rem 1.35rem;
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
  z-index: 10000;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.9rem;
}

.toast-popup.success {
  background: #10b981;
}

.toast-popup.error {
  background: #ef4444;
}

.toast-close {
  background: none;
  border: none;
  color: #ffffff;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0;
  margin-left: 0.5rem;
}
</style>
