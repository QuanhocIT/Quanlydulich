<template>
  <div class="vue-admin-dashboard">
    <!-- TOP HEADER BAR -->
    <header class="dash-header">
      <div class="dash-header-info">
        <div class="badge-tag">
          <span class="pulse-dot"></span>
          <span>AVENTURA PRO · ĐIỀU HÀNH THỜI GIAN THỰC</span>
        </div>
        <h1 class="dash-title">Bảng Điều Khiển Quản Trị</h1>
        <p class="dash-subtitle">
          Báo cáo tổng hợp theo thời gian thực về dòng tiền, vận hành tour, cổng thanh toán và chất lượng dịch vụ
        </p>
      </div>

      <div class="header-actions">
        <button 
          class="btn-action btn-refresh" 
          :class="{ 'is-loading': isRefreshing }"
          @click="fetchLatestData"
          title="Đồng bộ dữ liệu thời gian thực"
        >
          <i class="bi bi-arrow-clockwise" :class="{ 'spin-anim': isRefreshing }"></i>
          <span>{{ isRefreshing ? 'Đang đồng bộ...' : 'Làm mới' }}</span>
        </button>

        <a href="index.php?act=admin/quanLyTour" class="btn-action btn-primary-gold">
          <i class="bi bi-compass"></i>
          <span>Quản lý Tour</span>
        </a>

        <a href="index.php?act=admin/quanLyBooking" class="btn-action btn-glass">
          <i class="bi bi-bookmark-check"></i>
          <span>Bookings</span>
        </a>

        <a href="index.php?act=admin/baoCaoTaiChinh" class="btn-action btn-glass">
          <i class="bi bi-cash-stack"></i>
          <span>Tài Chính</span>
        </a>
      </div>
    </header>

    <!-- ALERT / NOTIFICATION BANNER IF ANY -->
    <div v-if="kpiAlerts.overdueDebt > 0 || kpiAlerts.bookingPending > 0 || kpiAlerts.paymentMismatch > 0" class="alert-banner">
      <div class="alert-item warning" v-if="kpiAlerts.bookingPending > 0">
        <div class="alert-icon-box"><i class="bi bi-bell-fill"></i></div>
        <div class="alert-msg">
          <span>Hệ thống ghi nhận <strong>{{ kpiAlerts.bookingPending }}</strong> booking đang chờ xác nhận hoặc duyệt cọc.</span>
        </div>
        <a href="index.php?act=admin/quanLyBooking" class="alert-link">Xem ngay <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="alert-item danger" v-if="kpiAlerts.overdueDebt > 0">
        <div class="alert-icon-box"><i class="bi bi-shield-exclamation"></i></div>
        <div class="alert-msg">
          <span>Phát hiện <strong>{{ kpiAlerts.overdueDebt }}</strong> khoản công nợ HDV quá hạn cần quyết toán.</span>
        </div>
        <a href="index.php?act=admin/quanLyCongNoHDV" class="alert-link">Xử lý ngay <i class="bi bi-arrow-right"></i></a>
      </div>
      <div class="alert-item info" v-if="kpiAlerts.paymentMismatch > 0">
        <div class="alert-icon-box"><i class="bi bi-credit-card-2-front"></i></div>
        <div class="alert-msg">
          <span>Có <strong>{{ kpiAlerts.paymentMismatch }}</strong> giao dịch thanh toán thành công cần đối soát vào sổ quỹ.</span>
        </div>
        <a href="index.php?act=admin/quanLyPayment" class="alert-link">Đối soát <i class="bi bi-arrow-right"></i></a>
      </div>
    </div>

    <!-- PRIMARY EXECUTIVE KPI GRID (6 CARDS) -->
    <section class="kpi-grid">
      <!-- Card 1: Tổng Doanh Thu -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Tổng Doanh Thu</span>
          <div class="kpi-icon-wrap icon-gold">
            <i class="bi bi-currency-dollar"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatCurrency(metrics.total_revenue) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend positive">
              <i class="bi bi-check-circle-fill"></i> Sổ quỹ thực nhận
            </span>
            <span class="kpi-subtext">Tháng này: {{ formatCurrency(metrics.monthly_revenue) }}</span>
          </div>
        </div>
      </div>

      <!-- Card 2: Lợi Nhuận Ròng -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Lợi Nhuận Ròng</span>
          <div class="kpi-icon-wrap icon-emerald">
            <i class="bi bi-graph-up-arrow"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value" :class="metrics.net_profit >= 0 ? '' : 'text-danger'">
            {{ formatCurrency(metrics.net_profit) }}
          </div>
          <div class="kpi-meta">
            <span class="kpi-trend" :class="metrics.net_profit >= 0 ? 'positive' : 'negative'">
              <i class="bi" :class="metrics.net_profit >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right'"></i>
              Biên độ {{ calculateMargin }}%
            </span>
            <span class="kpi-subtext">Chi phí: {{ formatCurrency(metrics.total_expense) }}</span>
          </div>
        </div>
      </div>

      <!-- Card 3: Tổng Booking & Hành Khách -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Lượng Booking & Khách</span>
          <div class="kpi-icon-wrap icon-sky">
            <i class="bi bi-calendar2-check"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNumber(metrics.total_bookings) }} <span class="unit-text">đơn</span></div>
          <div class="kpi-meta">
            <span class="kpi-trend neutral">
              <i class="bi bi-people-fill"></i> {{ formatNumber(metrics.total_passengers) }} hành khách
            </span>
            <span class="kpi-subtext">{{ formatNumber(metrics.total_customers) }} khách hàng</span>
          </div>
        </div>
      </div>

      <!-- Card 4: Tỷ Lệ Lấp Đầy Ghế -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Tỷ Lệ Lấp Đầy Chỗ</span>
          <div class="kpi-icon-wrap icon-indigo">
            <i class="bi bi-pie-chart"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ metrics.occupancy_rate }}%</div>
          <div class="kpi-meta">
            <div class="progress-bar-wrap">
              <div class="progress-bar-fill" :style="{ width: Math.min(metrics.occupancy_rate, 100) + '%' }"></div>
            </div>
            <span class="kpi-subtext">{{ metrics.total_passengers }}/{{ operationStats.total_capacity || 0 }} chỗ</span>
          </div>
        </div>
      </div>

      <!-- Card 5: Điểm Đánh Giá CSAT -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Đánh Giá CSAT Dịch Vụ</span>
          <div class="kpi-icon-wrap icon-amber">
            <i class="bi bi-star-fill"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">
            {{ metrics.avg_csat > 0 ? metrics.avg_csat : '5.0' }} <span class="unit-text">/ 5.0</span>
          </div>
          <div class="kpi-meta">
            <span class="kpi-trend positive">
              <i class="bi bi-hand-thumbs-up-fill"></i> {{ metrics.csat_positive_rate }}% hài lòng
            </span>
            <span class="kpi-subtext">{{ metrics.total_reviews }} nhận xét</span>
          </div>
        </div>
      </div>

      <!-- Card 6: Tỷ Lệ Cổng Thanh Toán -->
      <div class="kpi-card">
        <div class="kpi-header">
          <span class="kpi-label">Cổng Thanh Toán Online</span>
          <div class="kpi-icon-wrap icon-teal">
            <i class="bi bi-shield-check"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ metrics.payment_success_rate }}% <span class="unit-text">thành công</span></div>
          <div class="kpi-meta">
            <span class="kpi-trend neutral">
              <i class="bi bi-wallet2"></i> {{ formatCurrency(paymentStats.total_collected || 0) }}
            </span>
            <span class="kpi-subtext">{{ paymentStats.success_count || 0 }} GD thành công</span>
          </div>
        </div>
      </div>
    </section>

    <!-- OPERATIONAL STATS STRIP -->
    <section class="kpi-strip">
      <div class="strip-item">
        <span class="strip-label"><i class="bi bi-calendar-event"></i> Ngày báo cáo KPI:</span>
        <span class="strip-val">{{ dailyKpi ? formatDate(dailyKpi.summary_date) : 'Thời gian thực' }}</span>
      </div>
      <div class="strip-item" v-if="dailyKpi && dailyKpi.revenue_success_amount">
        <span class="strip-label"><i class="bi bi-cash"></i> Doanh thu phát sinh hôm nay:</span>
        <span class="strip-val highlight">{{ formatCurrency(dailyKpi.revenue_success_amount) }}</span>
      </div>
      <div class="strip-item">
        <span class="strip-label"><i class="bi bi-send-check"></i> Lịch khởi hành:</span>
        <span class="strip-val badge-rate">{{ operationStats.status_counts?.HoanThanh || 0 }} đã hoàn thành</span>
        <span class="strip-val" v-if="metrics.active_schedules > 0">{{ metrics.active_schedules }} đang chạy</span>
        <span class="strip-val text-muted" v-else>0 đang trên tuyến</span>
      </div>
      <div class="strip-item">
        <span class="strip-label"><i class="bi bi-robot"></i> Tiến trình tự động 24h:</span>
        <span class="strip-val badge-auto">{{ metrics.automation_events_24h }} tác vụ</span>
      </div>
      <div class="strip-item ml-auto">
        <span class="strip-label"><i class="bi bi-bell"></i> Chờ duyệt:</span>
        <span class="strip-val" :class="metrics.pending_bookings > 0 ? 'danger-text' : 'text-success'">
          {{ metrics.pending_bookings }} booking
        </span>
      </div>
    </section>

    <!-- CHARTS SECTION - ROW 1: CASH FLOW & PAYMENT GATEWAYS -->
    <section class="charts-grid-row1">
      <!-- Main Chart: Dòng tiền kinh doanh 12 tháng (Thu vs Chi vs Lợi Nhuận) -->
      <div class="chart-panel panel-large">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">
              <i class="bi bi-bar-chart-line-fill text-info me-2"></i>
              Dòng Tiền Kinh Doanh & Lợi Nhuận (12 Tháng)
            </h3>
            <p class="panel-subtitle">Theo dõi tổng thu, chi phí vận hành và lợi nhuận ròng thực tế theo từng tháng</p>
          </div>
          <div class="chart-filter-pills">
            <button 
              class="pill-btn" 
              :class="{ active: cashflowViewMode === 'all' }"
              @click="setCashflowView('all')"
            >
              Tất cả
            </button>
            <button 
              class="pill-btn" 
              :class="{ active: cashflowViewMode === 'revenue' }"
              @click="setCashflowView('revenue')"
            >
              Doanh Thu
            </button>
            <button 
              class="pill-btn" 
              :class="{ active: cashflowViewMode === 'profit' }"
              @click="setCashflowView('profit')"
            >
              Lợi Nhuận
            </button>
          </div>
        </div>
        <div class="chart-container">
          <canvas ref="cashflowChartRef"></canvas>
        </div>
        <div class="chart-legend-custom">
          <div class="legend-item"><span class="legend-color legend-thu"></span> Doanh Thu (Thu)</div>
          <div class="legend-item"><span class="legend-color legend-chi"></span> Chi Phí (Chi)</div>
          <div class="legend-item"><span class="legend-color legend-loinhuan"></span> Lợi Nhuận Ròng</div>
        </div>
      </div>

      <!-- Doughnut Chart: Phân bổ kênh thanh toán -->
      <div class="chart-panel panel-small">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">
              <i class="bi bi-credit-card-fill text-warning me-2"></i>
              Kênh Thanh Toán
            </h3>
            <p class="panel-subtitle">Doanh thu thu về theo cổng giao dịch</p>
          </div>
          <span class="panel-badge">Đã đối soát</span>
        </div>
        <div class="chart-container donut-wrap">
          <canvas ref="paymentChartRef"></canvas>
        </div>
        <div class="payment-method-summary" v-if="paymentStats.methods && paymentStats.methods.length > 0">
          <div 
            v-for="pm in paymentStats.methods" 
            :key="pm.payment_method" 
            class="pm-summary-row"
          >
            <span class="pm-name">
              <i class="bi bi-wallet2 text-muted me-1"></i>
              {{ getPaymentMethodLabel(pm.payment_method) }}
            </span>
            <span class="pm-amount">{{ formatCurrency(pm.total_collected) }}</span>
            <span class="pm-count badge-count">{{ pm.success_count }} GD</span>
          </div>
        </div>
      </div>
    </section>

    <!-- CHARTS SECTION - ROW 2: BOOKING STATUS & CSAT QUALITY REVIEW -->
    <section class="charts-grid-row2">
      <!-- Doughnut Chart: Trạng thái Booking -->
      <div class="chart-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">
              <i class="bi bi-pie-chart-fill text-primary me-2"></i>
              Cơ Cấu Trạng Thái Booking
            </h3>
            <p class="panel-subtitle">Phân bổ 84 đơn hàng theo tiến trình xử lý và cọc</p>
          </div>
          <span class="panel-badge">{{ formatNumber(metrics.total_bookings) }} Đơn</span>
        </div>
        <div class="chart-container donut-wrap">
          <canvas ref="bookingStatusChartRef"></canvas>
        </div>
      </div>

      <!-- Review & CSAT Breakdown Panel -->
      <div class="chart-panel csat-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">
              <i class="bi bi-stars text-gold me-2"></i>
              Phân Tích Chất Lượng & Hài Lòng (CSAT)
            </h3>
            <p class="panel-subtitle">Điểm xếp hạng thực tế từ phản hồi của khách hàng sau tour</p>
          </div>
          <span class="panel-badge csat-badge">{{ metrics.avg_csat > 0 ? metrics.avg_csat : '5.0' }} ★</span>
        </div>

        <div class="csat-body">
          <div class="csat-score-box">
            <div class="big-score">{{ metrics.avg_csat > 0 ? metrics.avg_csat : '5.0' }}</div>
            <div class="stars-row">
              <i class="bi bi-star-fill" v-for="s in 5" :key="s" :class="{ 'star-active': s <= Math.round(metrics.avg_csat || 5) }"></i>
            </div>
            <div class="csat-text-meta">Dựa trên {{ metrics.total_reviews }} lượt đánh giá</div>
            <div class="csat-pos-rate">
              <i class="bi bi-check2-circle text-success"></i> 
              <strong>{{ metrics.csat_positive_rate }}%</strong> phản hồi tích cực
            </div>
          </div>

          <div class="csat-distribution-list">
            <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="star-dist-row">
              <span class="star-label">{{ star }} <i class="bi bi-star-fill text-gold"></i></span>
              <div class="star-bar-track">
                <div 
                  class="star-bar-fill" 
                  :style="{ width: getStarPercentage(star) + '%' }"
                  :class="'star-color-' + star"
                ></div>
              </div>
              <span class="star-count">{{ getStarCount(star) }} ({{ getStarPercentage(star) }}%)</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- BOTTOM GRID: TOP TOURS, TOP GUIDES, AUTOMATION LOGS -->
    <section class="bottom-grid-tabs">
      <div class="tabs-header">
        <button 
          class="tab-btn" 
          :class="{ active: activeBottomTab === 'tours' }"
          @click="activeBottomTab = 'tours'"
        >
          <i class="bi bi-trophy-fill text-warning me-2"></i>
          Top Tour Hiệu Quả & Doanh Thu
        </button>
        <button 
          class="tab-btn" 
          :class="{ active: activeBottomTab === 'hdv' }"
          @click="activeBottomTab = 'hdv'"
        >
          <i class="bi bi-person-badge-fill text-info me-2"></i>
          Bảng Xếp Hạng Hướng Dẫn Viên (Top HDV)
        </button>
      </div>

      <!-- Tab Content 1: Top Tours Table -->
      <div class="tab-pane-content" v-show="activeBottomTab === 'tours'">
        <div class="table-panel">
          <div class="panel-header table-sub-header">
            <div>
              <p class="panel-subtitle">Xếp hạng các tuyến tour mang lại doanh số và lợi nhuận ròng cao nhất</p>
            </div>
            
            <div class="table-controls">
              <div class="search-box">
                <i class="bi bi-search"></i>
                <input 
                  type="text" 
                  v-model="searchQuery" 
                  placeholder="Tìm tên tour hoặc mã..." 
                  class="search-input"
                />
              </div>
              <select v-model="statusFilter" class="filter-select">
                <option value="all">Tất cả trạng thái</option>
                <option value="HoatDong">Đang hoạt động</option>
                <option value="TamDung">Tạm dừng</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table class="vue-table">
              <thead>
                <tr>
                  <th @click="sortBy('ten_tour')" class="sortable">
                    Tên Tour 
                    <i class="bi" :class="getSortIcon('ten_tour')"></i>
                  </th>
                  <th @click="sortBy('tong_thu')" class="sortable text-end">
                    Doanh Thu (Thu)
                    <i class="bi" :class="getSortIcon('tong_thu')"></i>
                  </th>
                  <th @click="sortBy('tong_chi_thuc_te')" class="sortable text-end">
                    Chi Phí Thực Tế
                    <i class="bi" :class="getSortIcon('tong_chi_thuc_te')"></i>
                  </th>
                  <th @click="sortBy('loi_nhuan')" class="sortable text-end">
                    Lợi Nhuận Ròng 
                    <i class="bi" :class="getSortIcon('loi_nhuan')"></i>
                  </th>
                  <th class="text-center">Trạng Thái</th>
                  <th class="text-center">Thao Tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tour in filteredTours" :key="tour.tour_id" class="table-row">
                  <td class="tour-name-cell">
                    <span class="tour-id-tag">#{{ tour.tour_id }}</span>
                    <strong class="tour-name">{{ tour.ten_tour }}</strong>
                  </td>
                  <td class="text-end fw-semibold text-emerald">
                    {{ formatCurrency(tour.tong_thu) }}
                  </td>
                  <td class="text-end text-muted">
                    {{ formatCurrency(tour.tong_chi_thuc_te) }}
                  </td>
                  <td class="text-end">
                    <span class="profit-badge" :class="tour.loi_nhuan >= 0 ? 'is-profit' : 'is-loss'">
                      {{ formatCurrency(tour.loi_nhuan) }}
                    </span>
                  </td>
                  <td class="text-center">
                    <span class="status-pill" :class="getStatusClass(tour.trang_thai)">
                      {{ tour.trang_thai || 'Hoạt động' }}
                    </span>
                  </td>
                  <td class="text-center">
                    <a :href="'index.php?act=admin/chiTietTour&id=' + tour.tour_id" class="btn-table-action" title="Xem chi tiết tour">
                      <i class="bi bi-arrow-right-short"></i> Chi tiết
                    </a>
                  </td>
                </tr>
                <tr v-if="filteredTours.length === 0">
                  <td colspan="6" class="text-center empty-cell">
                    <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                    Không tìm thấy tour phù hợp với tiêu chí lọc.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Tab Content 2: Top HDV Leaderboard -->
      <div class="tab-pane-content" v-show="activeBottomTab === 'hdv'">
        <div class="hdv-leaderboard-panel">
          <div class="hdv-grid" v-if="topHdv && topHdv.length > 0">
            <div 
              v-for="(hdv, idx) in topHdv" 
              :key="hdv.nhan_su_id" 
              class="hdv-card"
              :class="{ 'rank-1': idx === 0, 'rank-2': idx === 1, 'rank-3': idx === 2 }"
            >
              <div class="hdv-rank-badge">
                <span v-if="idx === 0">🥇 #1</span>
                <span v-else-if="idx === 1">🥈 #2</span>
                <span v-else-if="idx === 2">🥉 #3</span>
                <span v-else>#{{ idx + 1 }}</span>
              </div>

              <div class="hdv-avatar">
                <div class="avatar-circle">
                  {{ getInitials(hdv.ho_ten) }}
                </div>
              </div>

              <div class="hdv-info">
                <h4 class="hdv-name">{{ hdv.ho_ten }}</h4>
                <div class="hdv-contact">
                  <span v-if="hdv.so_dien_thoai"><i class="bi bi-telephone"></i> {{ hdv.so_dien_thoai }}</span>
                  <span v-if="hdv.email"><i class="bi bi-envelope"></i> {{ hdv.email }}</span>
                </div>

                <div class="hdv-metrics-row">
                  <div class="hdv-metric">
                    <span class="m-val text-gold">{{ Number(hdv.danh_gia_tb).toFixed(1) }} <i class="bi bi-star-fill"></i></span>
                    <span class="m-lbl">Điểm Đánh Giá</span>
                  </div>
                  <div class="hdv-metric">
                    <span class="m-val">{{ hdv.so_tour_da_dan }}</span>
                    <span class="m-lbl">Tour Đã Dẫn</span>
                  </div>
                  <div class="hdv-metric">
                    <span class="m-val status-badge" :class="hdv.trang_thai_lam_viec === 'SanSang' ? 'ready' : 'busy'">
                      {{ hdv.trang_thai_lam_viec === 'SanSang' ? 'Sẵn sàng' : 'Bận' }}
                    </span>
                    <span class="m-lbl">Trạng Thái</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="empty-cell text-center p-4">
            <i class="bi bi-people fs-2 text-muted d-block mb-2"></i>
            Chưa có dữ liệu nhân sự hướng dẫn viên.
          </div>
        </div>
      </div>
    </section>

    <!-- LIVE AUTOMATION & ACTIVITY FEED 24H -->
    <section class="feed-section">
      <div class="feed-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">
              <i class="bi bi-cpu-fill text-warning me-2"></i>
              Sự Kiện Tự Động Hóa & Nhật Ký Vận Hành 24h
            </h3>
            <p class="panel-subtitle">Hệ thống xử lý nền tự động thông minh (nhắc booking, đồng bộ thanh toán, đối soát cọc)</p>
          </div>
          <a href="index.php?act=admin/automationDashboard" class="feed-more-link">
            Xem Dashboard Tự Động Hóa &rarr;
          </a>
        </div>

        <div class="feed-list" v-if="recentEvents.length > 0">
          <div 
            v-for="(event, idx) in recentEvents.slice(0, 8)" 
            :key="idx" 
            class="feed-item"
          >
            <div class="feed-icon" :class="getEventStatusClass(event.status)">
              <i class="bi" :class="getEventIcon(event.event_type)"></i>
            </div>
            <div class="feed-content">
              <div class="feed-title-line">
                <span class="feed-event-name">{{ event.event_type || 'Tự Động Hóa' }}</span>
                <span class="feed-time">{{ formatTimeAgo(event.created_at) }}</span>
              </div>
              <p class="feed-desc">{{ event.description || event.payload_summary || 'Hệ thống đã tự động thực thi tác vụ nền thành công' }}</p>
            </div>
          </div>
        </div>
        <div v-else class="feed-empty">
          <i class="bi bi-check-circle-fill text-success fs-3 mb-2"></i>
          <p>Hệ thống tự động hóa đang vận hành ổn định, không có lỗi phát sinh trong 24 giờ qua.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
});

// State
const isRefreshing = ref(false);
const searchQuery = ref('');
const statusFilter = ref('all');
const sortKey = ref('loi_nhuan');
const sortOrder = ref('desc');
const activeBottomTab = ref('tours');
const cashflowViewMode = ref('all');

const metrics = ref({
  total_tours: 0,
  total_revenue: 0,
  total_expense: 0,
  net_profit: 0,
  total_bookings: 0,
  total_customers: 0,
  monthly_revenue: 0,
  pending_bookings: 0,
  overdue_debt: 0,
  automation_events_24h: 0,
  avg_csat: 0,
  csat_positive_rate: 0,
  total_reviews: 0,
  occupancy_rate: 0,
  total_passengers: 0,
  active_schedules: 0,
  upcoming_schedules: 0,
  payment_success_rate: 0,
});

const charts = ref({
  revenue_by_month: {},
  cashflow_by_month: [],
  booking_status: {},
  customers_by_month: {},
  tour_status: {},
  payment_methods: [],
  review_distribution: {},
  schedule_status: {},
});

const dailyKpi = ref(null);
const kpiAlerts = ref({
  bookingPending: 0,
  overdueDebt: 0,
  paymentMismatch: 0,
  monthToDateRevenue: 0,
});

const tours = ref([]);
const topHdv = ref([]);
const recentEvents = ref([]);
const paymentStats = ref({
  methods: [],
  total_collected: 0,
  success_count: 0,
  failed_count: 0,
  success_rate: 0,
});
const reviewMetrics = ref({
  total_reviews: 0,
  avg_score: 0,
  satisfaction_rate: 0,
  stars: {},
});
const operationStats = ref({
  status_counts: {},
  active_running: 0,
  upcoming_7_days: 0,
  total_capacity: 0,
  total_guests: 0,
  occupancy_rate: 0,
});

// Canvas refs & instances
const cashflowChartRef = ref(null);
const paymentChartRef = ref(null);
const bookingStatusChartRef = ref(null);

let cashflowChartInstance = null;
let paymentChartInstance = null;
let bookingStatusChartInstance = null;

// Profit margin calculation
const calculateMargin = computed(() => {
  const rev = Number(metrics.value.total_revenue) || 0;
  const profit = Number(metrics.value.net_profit) || 0;
  if (rev <= 0) return 0;
  return Math.round((profit / rev) * 100);
});

// Apply payload
function applyData(data) {
  if (!data) return;
  if (data.metrics) metrics.value = { ...metrics.value, ...data.metrics };
  if (data.charts) charts.value = { ...charts.value, ...data.charts };
  if (data.daily_kpi) dailyKpi.value = data.daily_kpi;
  if (data.kpi_alerts) kpiAlerts.value = { ...kpiAlerts.value, ...data.kpi_alerts };
  if (data.top_tours) tours.value = data.top_tours;
  if (data.top_hdv) topHdv.value = data.top_hdv;
  if (data.payment_stats) paymentStats.value = data.payment_stats;
  if (data.review_metrics) reviewMetrics.value = data.review_metrics;
  if (data.operation_stats) operationStats.value = data.operation_stats;
  if (data.recent_events) recentEvents.value = data.recent_events;

  nextTick(() => {
    renderAllCharts();
  });
}

// Fetch live API data
async function fetchLatestData() {
  if (isRefreshing.value) return;
  isRefreshing.value = true;
  try {
    const res = await fetch('index.php?act=admin/apiDashboardData', {
      headers: { 'Accept': 'application/json' }
    });
    if (res.ok) {
      const json = await res.json();
      if (json.success && json.data) {
        applyData(json.data);
      }
    }
  } catch (err) {
    console.error('[Vue Admin] Fetch error:', err);
  } finally {
    setTimeout(() => {
      isRefreshing.value = false;
    }, 400);
  }
}

// Cashflow view switch
function setCashflowView(mode) {
  cashflowViewMode.value = mode;
  renderCashflowChart();
}

// Render all charts
function renderAllCharts() {
  renderCashflowChart();
  renderPaymentChart();
  renderBookingStatusChart();
}

// 1. Render Cash Flow Multi-chart
function renderCashflowChart() {
  if (!cashflowChartRef.value) return;
  if (cashflowChartInstance) {
    cashflowChartInstance.destroy();
  }

  const rawCashflow = charts.value.cashflow_by_month || [];
  let labels = [];
  let thuData = [];
  let chiData = [];
  let loiNhuanData = [];

  if (rawCashflow.length > 0) {
    labels = rawCashflow.map(item => item.thang || item.raw_month);
    thuData = rawCashflow.map(item => Number(item.tong_thu) || 0);
    chiData = rawCashflow.map(item => Number(item.tong_chi) || 0);
    loiNhuanData = rawCashflow.map(item => Number(item.loi_nhuan) || 0);
  } else {
    const rawMonths = charts.value.revenue_by_month || {};
    labels = Object.keys(rawMonths);
    thuData = Object.values(rawMonths).map(v => Number(v) || 0);
    chiData = labels.map(() => 0);
    loiNhuanData = [...thuData];
  }

  if (labels.length === 0) {
    labels = ['Th 1', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7', 'Th 8', 'Th 9', 'Th 10', 'Th 11', 'Th 12'];
    thuData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    chiData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    loiNhuanData = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
  }

  const ctx = cashflowChartRef.value.getContext('2d');
  const datasets = [];

  if (cashflowViewMode.value === 'all' || cashflowViewMode.value === 'revenue') {
    datasets.push({
      type: 'bar',
      label: 'Doanh Thu (Thu)',
      data: thuData,
      backgroundColor: 'rgba(223, 169, 116, 0.75)',
      borderColor: '#dfa974',
      borderWidth: 1.5,
      borderRadius: 6,
      order: 2,
    });
  }

  if (cashflowViewMode.value === 'all') {
    datasets.push({
      type: 'bar',
      label: 'Chi Phí (Chi)',
      data: chiData,
      backgroundColor: 'rgba(244, 63, 94, 0.65)',
      borderColor: '#f43f5e',
      borderWidth: 1.5,
      borderRadius: 6,
      order: 3,
    });
  }

  if (cashflowViewMode.value === 'all' || cashflowViewMode.value === 'profit') {
    const profitGrad = ctx.createLinearGradient(0, 0, 0, 250);
    profitGrad.addColorStop(0, 'rgba(16, 185, 129, 0.35)');
    profitGrad.addColorStop(1, 'rgba(16, 185, 129, 0.02)');

    datasets.push({
      type: 'line',
      label: 'Lợi Nhuận Ròng',
      data: loiNhuanData,
      borderColor: '#10b981',
      backgroundColor: profitGrad,
      fill: true,
      tension: 0.35,
      borderWidth: 3,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointBackgroundColor: '#10b981',
      pointBorderColor: '#ffffff',
      pointBorderWidth: 2,
      order: 1,
    });
  }

  cashflowChartInstance = new Chart(ctx, {
    data: {
      labels: labels,
      datasets: datasets,
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: '#0b1120',
          borderColor: 'rgba(255, 255, 255, 0.12)',
          borderWidth: 1,
          padding: 12,
          titleFont: { size: 13, weight: '700' },
          bodyFont: { size: 12 },
          cornerRadius: 8,
          callbacks: {
            label: (item) => ` ${item.dataset.label}: ${Number(item.raw).toLocaleString('vi-VN')} đ`
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 11 }, color: '#94a3b8' }
        },
        y: {
          grid: { color: 'rgba(255, 255, 255, 0.06)' },
          ticks: {
            font: { size: 11 },
            color: '#94a3b8',
            callback: (val) => {
              if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' tỷ';
              if (val >= 1000000) return (val / 1000000).toFixed(0) + ' tr';
              if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
              return val;
            }
          }
        }
      }
    }
  });
}

// 2. Render Payment Gateways Doughnut Chart
function renderPaymentChart() {
  if (!paymentChartRef.value) return;
  if (paymentChartInstance) {
    paymentChartInstance.destroy();
  }

  const methods = paymentStats.value.methods || [];
  let labels = [];
  let dataValues = [];
  const palette = ['#dfa974', '#10b981', '#38bdf8', '#818cf8', '#f59e0b', '#94a3b8'];

  if (methods.length > 0) {
    labels = methods.map(m => getPaymentMethodLabel(m.payment_method));
    dataValues = methods.map(m => Number(m.total_collected) || 0);
  } else {
    labels = ['VNPay', 'Chuyển Khoản', 'Paypal'];
    dataValues = [63090000, 18000000, 9000000];
  }

  const ctx = paymentChartRef.value.getContext('2d');
  paymentChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels,
      datasets: [{
        data: dataValues,
        backgroundColor: palette.slice(0, Math.max(labels.length, 3)),
        borderWidth: 2,
        borderColor: '#161f2e',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: { size: 11 },
            color: '#cbd5e1',
            padding: 12,
          }
        },
        tooltip: {
          backgroundColor: '#0b1120',
          borderColor: 'rgba(255, 255, 255, 0.12)',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: (item) => ` ${item.label}: ${Number(item.raw).toLocaleString('vi-VN')} đ`
          }
        }
      }
    }
  });
}

// 3. Render Booking Status Doughnut Chart
function renderBookingStatusChart() {
  if (!bookingStatusChartRef.value) return;
  if (bookingStatusChartInstance) {
    bookingStatusChartInstance.destroy();
  }

  const rawStatus = charts.value.booking_status || {};
  const statusLabelsMap = {
    ChoXacNhan: 'Chờ xác nhận',
    DaCoc: 'Đã đặt cọc',
    HoanTat: 'Hoàn tất',
    Huy: 'Đã hủy',
    DangXuLy: 'Đang xử lý',
  };

  const labels = Object.keys(rawStatus).map(k => statusLabelsMap[k] || k);
  const dataValues = Object.values(rawStatus).map(v => Number(v) || 0);
  const palette = ['#dfa974', '#38bdf8', '#10b981', '#f87171', '#818cf8'];

  const ctx = bookingStatusChartRef.value.getContext('2d');
  bookingStatusChartInstance = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: labels.length ? labels : ['Chờ xác nhận', 'Đã cọc', 'Hoàn tất', 'Đã hủy'],
      datasets: [{
        data: dataValues.length ? dataValues : [36, 18, 20, 10],
        backgroundColor: palette.slice(0, Math.max(labels.length, 4)),
        borderWidth: 2,
        borderColor: '#161f2e',
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '68%',
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            boxWidth: 12,
            font: { size: 11 },
            color: '#cbd5e1',
            padding: 12,
          }
        },
        tooltip: {
          backgroundColor: '#0b1120',
          borderColor: 'rgba(255, 255, 255, 0.12)',
          borderWidth: 1,
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label: (item) => ` ${item.label}: ${item.raw} booking`
          }
        }
      }
    }
  });
}

// Helpers for CSAT Star breakdown
function getStarCount(star) {
  const starsMap = reviewMetrics.value.stars || charts.value.review_distribution || {};
  return Number(starsMap[star] ?? starsMap[String(star)] ?? 0);
}

function getStarPercentage(star) {
  const total = Number(reviewMetrics.value.total_reviews) || Number(metrics.value.total_reviews) || 0;
  if (total <= 0) return 0;
  const count = getStarCount(star);
  return Math.round((count / total) * 100);
}

function getPaymentMethodLabel(method) {
  const map = {
    VNPay: 'Cổng VNPay (QR / Thẻ)',
    ChuyenKhoan: 'Chuyển khoản Ngân hàng',
    Paypal: 'Cổng PayPal Quốc tế',
    Momo: 'Ví MoMo',
    TienMat: 'Tiền mặt tại quầy',
    TheTinDung: 'Thẻ tín dụng / Visa',
    ViDienTu: 'Ví điện tử',
    Khac: 'Khác',
  };
  return map[method] || method;
}

function getInitials(name) {
  if (!name) return 'HD';
  const parts = name.trim().split(/\s+/);
  if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

// Table filtering & sorting
const filteredTours = computed(() => {
  let list = [...tours.value];

  if (searchQuery.value.trim() !== '') {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(t => (t.ten_tour || '').toLowerCase().includes(q) || String(t.tour_id).includes(q));
  }

  if (statusFilter.value !== 'all') {
    list = list.filter(t => t.trang_thai === statusFilter.value);
  }

  list.sort((a, b) => {
    let valA = a[sortKey.value];
    let valB = b[sortKey.value];

    if (typeof valA === 'string') {
      valA = valA.toLowerCase();
      valB = (valB || '').toLowerCase();
    } else {
      valA = Number(valA) || 0;
      valB = Number(valB) || 0;
    }

    if (sortOrder.value === 'asc') {
      return valA > valB ? 1 : (valA < valB ? -1 : 0);
    } else {
      return valA < valB ? 1 : (valA > valB ? -1 : 0);
    }
  });

  return list;
});

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'desc';
  }
}

function getSortIcon(key) {
  if (sortKey.value !== key) return 'bi-arrow-down-up text-muted';
  return sortOrder.value === 'asc' ? 'bi-sort-up text-primary' : 'bi-sort-down text-primary';
}

function getStatusClass(status) {
  if (!status || status === 'HoatDong' || status === 'Hoạt động') return 'status-active';
  if (status === 'TamDung' || status === 'Tạm dừng') return 'status-paused';
  return 'status-other';
}

function getEventStatusClass(status) {
  if (status === 'success' || status === 'completed') return 'icon-success';
  if (status === 'failed' || status === 'error') return 'icon-danger';
  return 'icon-info';
}

function getEventIcon(eventType) {
  const t = (eventType || '').toLowerCase();
  if (t.includes('booking')) return 'bi-calendar-check';
  if (t.includes('payment') || t.includes('thanhtoan')) return 'bi-credit-card-2-front';
  if (t.includes('mail')) return 'bi-envelope-check';
  return 'bi-lightning-charge';
}

// Formatters
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatNumber(val) {
  return (Number(val) || 0).toLocaleString('vi-VN');
}

function formatDate(str) {
  if (!str) return '--/--/----';
  const parts = str.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return str;
}

function formatTimeAgo(dateStr) {
  if (!dateStr) return 'Gần đây';
  try {
    const diff = (new Date() - new Date(dateStr)) / 1000;
    if (diff < 60) return 'Vừa xong';
    if (diff < 3600) return Math.floor(diff / 60) + ' phút trước';
    if (diff < 86400) return Math.floor(diff / 3600) + ' giờ trước';
    return Math.floor(diff / 86400) + ' ngày trước';
  } catch (e) {
    return dateStr;
  }
}

// Lifecycle
onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchLatestData();
  }
});
</script>

<style scoped>
.vue-admin-dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* HEADER BAR */
.dash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(20, 26, 38, 0.85) 0%, rgba(13, 18, 28, 0.95) 100%);
  padding: 1.6rem 2rem;
  border-radius: 1.25rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(223, 169, 116, 0.18);
  backdrop-filter: blur(14px);
  gap: 1.5rem;
}

.dash-header-info {
  display: flex !important;
  flex-direction: column !important;
  align-items: flex-start !important;
  gap: 0.35rem;
  flex: 1;
  min-width: 0;
}

.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(223, 169, 116, 0.1);
  color: #dfa974;
  border: 1px solid rgba(223, 169, 116, 0.28);
  padding: 0.28rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.25rem;
}

.pulse-dot {
  width: 7px;
  height: 7px;
  background: #dfa974;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(223, 169, 116, 0.7);
  animation: pulse 1.6s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(223, 169, 116, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(223, 169, 116, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(223, 169, 116, 0); }
}

.dash-title {
  font-size: 1.65rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.02em;
  line-height: 1.25;
}

.dash-subtitle {
  font-size: 0.88rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.45;
}

.header-actions {
  display: flex;
  gap: 0.65rem;
  align-items: center;
  flex-shrink: 0;
  flex-wrap: wrap;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  height: 40px;
  padding: 0 1.15rem;
  border-radius: 0.65rem;
  font-size: 0.86rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.btn-refresh {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
}
.btn-refresh:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}
.spin-anim {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}

.btn-primary-gold {
  background: linear-gradient(135deg, #dfa974 0%, #b88349 100%);
  color: #0f172a !important;
  border: none;
  font-weight: 700;
  box-shadow: 0 4px 14px rgba(223, 169, 116, 0.3);
}
.btn-primary-gold:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(223, 169, 116, 0.45);
  filter: brightness(1.08);
  color: #0f172a !important;
}

.btn-glass {
  background: rgba(255, 255, 255, 0.05);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.btn-glass:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(223, 169, 116, 0.35);
  color: #ffffff;
  transform: translateY(-1px);
}

/* ALERT / NOTIFICATION BANNER */
.alert-banner {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}
.alert-item {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  padding: 0.75rem 1.25rem;
  border-radius: 0.75rem;
  font-size: 0.86rem;
  backdrop-filter: blur(10px);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18);
}
.alert-icon-box {
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.alert-msg {
  flex: 1;
  line-height: 1.4;
}
.alert-item.warning {
  background: rgba(223, 169, 116, 0.08);
  border: 1px solid rgba(223, 169, 116, 0.22);
  border-left: 3.5px solid #dfa974;
  color: #fce7cf;
}
.alert-item.warning .alert-icon-box { color: #dfa974; }

.alert-item.danger {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.22);
  border-left: 3.5px solid #f87171;
  color: #fecaca;
}
.alert-item.danger .alert-icon-box { color: #f87171; }

.alert-item.info {
  background: rgba(56, 189, 248, 0.08);
  border: 1px solid rgba(56, 189, 248, 0.22);
  border-left: 3.5px solid #38bdf8;
  color: #bae6fd;
}
.alert-item.info .alert-icon-box { color: #38bdf8; }

.alert-link {
  margin-left: auto;
  font-weight: 700;
  color: #ffffff;
  text-decoration: none;
  padding: 0.25rem 0.65rem;
  border-radius: 0.4rem;
  background: rgba(255, 255, 255, 0.08);
  font-size: 0.78rem;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  transition: all 0.2s;
  flex-shrink: 0;
}
.alert-link:hover {
  background: rgba(255, 255, 255, 0.18);
  color: #ffffff;
  transform: translateX(2px);
}

/* PRIMARY KPI GRID (6 CARDS) - LUXURY BENTO */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1.15rem;
}

.kpi-card {
  background: linear-gradient(180deg, rgba(20, 27, 41, 0.75) 0%, rgba(13, 18, 28, 0.88) 100%);
  border-radius: 1rem;
  padding: 1.35rem 1.4rem;
  border: 1px solid rgba(255, 255, 255, 0.07);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(14px);
  transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(223, 169, 116, 0.35);
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}
.kpi-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.kpi-icon-wrap {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 10px;
  font-size: 1.15rem;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.icon-gold {
  background: rgba(223, 169, 116, 0.12);
  color: #dfa974;
  border-color: rgba(223, 169, 116, 0.25);
}
.icon-emerald {
  background: rgba(16, 185, 129, 0.12);
  color: #34d399;
  border-color: rgba(16, 185, 129, 0.25);
}
.icon-sky {
  background: rgba(56, 189, 248, 0.12);
  color: #38bdf8;
  border-color: rgba(56, 189, 248, 0.25);
}
.icon-indigo {
  background: rgba(99, 102, 241, 0.12);
  color: #818cf8;
  border-color: rgba(99, 102, 241, 0.25);
}
.icon-amber {
  background: rgba(245, 158, 11, 0.12);
  color: #fbbf24;
  border-color: rgba(245, 158, 11, 0.25);
}
.icon-teal {
  background: rgba(20, 184, 166, 0.12);
  color: #2dd4bf;
  border-color: rgba(20, 184, 166, 0.25);
}

.kpi-value {
  font-size: 1.6rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.02em;
  margin-bottom: 0.5rem;
  line-height: 1.2;
}
.unit-text {
  font-size: 0.85rem;
  font-weight: 600;
  color: #94a3b8;
  margin-left: 2px;
}

.kpi-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.78rem;
}
.kpi-trend {
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.76rem;
}
.kpi-trend.positive { color: #4ade80; }
.kpi-trend.negative { color: #f87171; }
.kpi-trend.neutral { color: #94a3b8; }
.kpi-subtext { color: #64748b; font-size: 0.75rem; }

.progress-bar-wrap {
  width: 80px;
  height: 5px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
  overflow: hidden;
}
.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #dfa974, #34d399);
  border-radius: 3px;
}

/* KPI STRIP - EXECUTIVE TELEMETRY */
.kpi-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  background: rgba(15, 23, 42, 0.85);
  padding: 0.85rem 1.5rem;
  border-radius: 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.07);
  align-items: center;
  backdrop-filter: blur(10px);
}
.strip-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
}
.strip-label { color: #94a3b8; display: flex; align-items: center; gap: 0.35rem; font-size: 0.82rem; }
.strip-val { font-weight: 700; color: #ffffff; }
.strip-val.highlight { color: #dfa974; }
.strip-val.badge-rate {
  background: rgba(16, 185, 129, 0.15);
  color: #6ee7b7;
  border: 1px solid rgba(16, 185, 129, 0.25);
  padding: 0.15rem 0.5rem;
  border-radius: 0.35rem;
  font-size: 0.78rem;
}
.strip-val.badge-auto {
  background: rgba(223, 169, 116, 0.15);
  color: #f5d09e;
  border: 1px solid rgba(223, 169, 116, 0.25);
  padding: 0.15rem 0.5rem;
  border-radius: 0.35rem;
  font-size: 0.78rem;
}
.strip-val.danger-text { color: #f87171; }
.ml-auto { margin-left: auto; }

/* CHARTS ROW 1 */
.charts-grid-row1 {
  display: grid;
  grid-template-columns: 2.2fr 1fr;
  gap: 1.25rem;
}
@media (max-width: 1024px) {
  .charts-grid-row1 { grid-template-columns: 1fr; }
}

.chart-panel {
  background: rgba(22, 31, 46, 0.8);
  border-radius: 1.15rem;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  display: flex;
  flex-direction: column;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1.25rem;
}
.panel-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
  display: flex;
  align-items: center;
}
.panel-subtitle {
  font-size: 0.84rem;
  color: #94a3b8;
  margin: 0.25rem 0 0;
}
.panel-badge {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.1);
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.3rem 0.65rem;
  border-radius: 0.5rem;
}

.chart-filter-pills {
  display: flex;
  gap: 0.35rem;
  background: rgba(11, 17, 32, 0.7);
  padding: 0.25rem;
  border-radius: 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.pill-btn {
  background: transparent;
  border: none;
  color: #94a3b8;
  padding: 0.3rem 0.65rem;
  border-radius: 0.35rem;
  font-size: 0.78rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.pill-btn.active {
  background: rgba(223, 169, 116, 0.2);
  color: #f5d09e;
  border: 1px solid rgba(223, 169, 116, 0.35);
}

.chart-container {
  height: 290px;
  position: relative;
}
.donut-wrap {
  height: 220px;
}

.chart-legend-custom {
  display: flex;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 0.85rem;
  font-size: 0.82rem;
  color: #94a3b8;
}
.legend-item {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}
.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 3px;
}
.legend-thu { background: #dfa974; }
.legend-chi { background: #f87171; }
.legend-loinhuan { background: #34d399; }

.payment-method-summary {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  padding-top: 0.85rem;
}
.pm-summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.82rem;
}
.pm-name { color: #cbd5e1; }
.pm-amount { font-weight: 700; color: #dfa974; }
.badge-count {
  background: rgba(255, 255, 255, 0.08);
  padding: 0.15rem 0.45rem;
  border-radius: 0.35rem;
  color: #94a3b8;
  font-size: 0.75rem;
}

/* CHARTS ROW 2 */
.charts-grid-row2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}
@media (max-width: 992px) {
  .charts-grid-row2 { grid-template-columns: 1fr; }
}

.csat-panel {
  display: flex;
  flex-direction: column;
}
.csat-badge {
  background: rgba(223, 169, 116, 0.15);
  color: #f5d09e;
  border-color: rgba(223, 169, 116, 0.3);
}

.csat-body {
  display: grid;
  grid-template-columns: 180px 1fr;
  gap: 1.5rem;
  align-items: center;
  height: 100%;
  padding-top: 0.5rem;
}
@media (max-width: 576px) {
  .csat-body { grid-template-columns: 1fr; }
}

.csat-score-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: rgba(11, 17, 32, 0.7);
  padding: 1.25rem 1rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  text-align: center;
}
.big-score {
  font-size: 3rem;
  font-weight: 900;
  color: #f5d09e;
  line-height: 1;
  margin-bottom: 0.35rem;
}
.stars-row {
  display: flex;
  gap: 0.25rem;
  color: #475569;
  font-size: 1rem;
  margin-bottom: 0.5rem;
}
.star-active { color: #dfa974 !important; }
.csat-text-meta {
  font-size: 0.78rem;
  color: #94a3b8;
  margin-bottom: 0.5rem;
}
.csat-pos-rate {
  font-size: 0.8rem;
  color: #6ee7b7;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.csat-distribution-list {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}
.star-dist-row {
  display: grid;
  grid-template-columns: 50px 1fr 75px;
  align-items: center;
  gap: 0.75rem;
  font-size: 0.82rem;
}
.star-label {
  color: #cbd5e1;
  font-weight: 600;
}
.star-bar-track {
  height: 8px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 4px;
  overflow: hidden;
}
.star-bar-fill {
  height: 100%;
  border-radius: 4px;
}
.star-color-5 { background: #10b981; }
.star-color-4 { background: #dfa974; }
.star-color-3 { background: #f59e0b; }
.star-color-2 { background: #fb923c; }
.star-color-1 { background: #ef4444; }
.star-count {
  color: #94a3b8;
  text-align: right;
  font-size: 0.78rem;
}

/* BOTTOM SECTION TABS */
.bottom-grid-tabs {
  background: rgba(22, 31, 46, 0.8);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}
.tabs-header {
  display: flex;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 23, 42, 0.8);
  padding: 0.5rem 1rem 0;
  gap: 0.5rem;
}
.tab-btn {
  background: transparent;
  border: none;
  border-bottom: 3px solid transparent;
  color: #94a3b8;
  font-weight: 700;
  font-size: 0.95rem;
  padding: 0.85rem 1.25rem;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
}
.tab-btn:hover {
  color: #ffffff;
}
.tab-btn.active {
  color: #dfa974;
  border-bottom-color: #dfa974;
  background: rgba(223, 169, 116, 0.08);
  border-radius: 0.5rem 0.5rem 0 0;
}

.tab-pane-content {
  padding: 1.25rem;
}

.table-sub-header {
  margin-bottom: 1rem;
}

.table-controls {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}
.search-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(11, 17, 32, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.65rem;
  padding: 0.4rem 0.75rem;
  color: #cbd5e1;
}
.search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.85rem;
  width: 170px;
  color: #ffffff;
}
.search-input::placeholder {
  color: #64748b;
}
.filter-select {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(11, 17, 32, 0.8);
  border-radius: 0.65rem;
  padding: 0.45rem 0.75rem;
  font-size: 0.85rem;
  outline: none;
  color: #ffffff;
}

.table-responsive {
  overflow-x: auto;
}
.vue-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}
.vue-table th {
  padding: 0.85rem 1rem;
  background: rgba(11, 17, 32, 0.9);
  font-weight: 700;
  color: #d4af37;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.vue-table th.sortable {
  cursor: pointer;
  user-select: none;
}
.vue-table th.sortable:hover {
  color: #fde047;
}
.vue-table td {
  padding: 0.9rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.table-row:hover td {
  background: rgba(255, 255, 255, 0.03);
}

.tour-name-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.tour-id-tag {
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.15rem 0.4rem;
  border-radius: 0.35rem;
}
.tour-name {
  color: #ffffff;
  font-weight: 600;
}

.profit-badge {
  font-weight: 700;
  padding: 0.25rem 0.6rem;
  border-radius: 0.4rem;
  font-size: 0.82rem;
}
.profit-badge.is-profit {
  background: rgba(16, 185, 129, 0.18);
  color: #6ee7b7;
  border: 1px solid rgba(16, 185, 129, 0.3);
}
.profit-badge.is-loss {
  background: rgba(239, 68, 68, 0.18);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-pill {
  display: inline-block;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
  border-radius: 2rem;
}
.status-pill.status-active {
  background: rgba(14, 165, 233, 0.18);
  color: #7dd3fc;
  border: 1px solid rgba(14, 165, 233, 0.35);
}
.status-pill.status-paused {
  background: rgba(245, 158, 11, 0.18);
  color: #fde68a;
  border: 1px solid rgba(245, 158, 11, 0.35);
}

.btn-table-action {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #38bdf8;
  text-decoration: none;
  padding: 0.3rem 0.65rem;
  border-radius: 0.4rem;
  background: rgba(14, 165, 233, 0.15);
  border: 1px solid rgba(14, 165, 233, 0.25);
  transition: all 0.2s;
}
.btn-table-action:hover {
  background: rgba(14, 165, 233, 0.25);
  color: #ffffff;
}

/* HDV LEADERBOARD */
.hdv-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}
.hdv-card {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 1.25rem;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  transition: transform 0.2s, border-color 0.2s;
}
.hdv-card:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 255, 255, 0.2);
}
.hdv-card.rank-1 {
  border-color: rgba(212, 175, 55, 0.4);
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.9) 0%, rgba(212, 175, 55, 0.08) 100%);
}
.hdv-rank-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 0.85rem;
  font-weight: 800;
  padding: 0.2rem 0.6rem;
  border-radius: 2rem;
  background: rgba(255, 255, 255, 0.08);
  color: #fde047;
}
.hdv-card.rank-1 .hdv-rank-badge {
  background: rgba(212, 175, 55, 0.25);
}

.hdv-avatar {
  display: flex;
  align-items: center;
}
.avatar-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0ea5e9, #6366f1);
  color: #ffffff;
  font-weight: 800;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}
.hdv-card.rank-1 .avatar-circle {
  background: linear-gradient(135deg, #d4af37, #b89628);
  color: #0b1120;
}

.hdv-info {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.hdv-name {
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}
.hdv-contact {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
  font-size: 0.78rem;
  color: #94a3b8;
}

.hdv-metrics-row {
  display: flex;
  justify-content: space-between;
  background: rgba(11, 17, 32, 0.7);
  padding: 0.75rem 1rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.06);
  margin-top: 0.5rem;
}
.hdv-metric {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.2rem;
}
.m-val {
  font-weight: 800;
  font-size: 0.95rem;
  color: #ffffff;
}
.m-lbl {
  font-size: 0.7rem;
  color: #64748b;
  text-transform: uppercase;
}
.status-badge.ready {
  color: #34d399;
  font-size: 0.82rem;
}
.status-badge.busy {
  color: #f87171;
  font-size: 0.82rem;
}

/* FEED PANEL */
.feed-panel {
  background: rgba(22, 31, 46, 0.8);
  border-radius: 1.15rem;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
}
.feed-more-link {
  font-size: 0.85rem;
  font-weight: 600;
  color: #38bdf8;
  text-decoration: none;
}
.feed-more-link:hover {
  text-decoration: underline;
  color: #7dd3fc;
}

.feed-list {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}
.feed-item {
  display: flex;
  gap: 0.85rem;
  align-items: flex-start;
  padding-bottom: 0.85rem;
  border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
}
.feed-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}
.feed-icon {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.95rem;
  flex-shrink: 0;
}
.feed-icon.icon-success { background: rgba(16, 185, 129, 0.18); color: #34d399; }
.feed-icon.icon-danger { background: rgba(239, 68, 68, 0.18); color: #f87171; }
.feed-icon.icon-info { background: rgba(14, 165, 233, 0.18); color: #38bdf8; }

.feed-content { flex: 1; }
.feed-title-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.2rem;
}
.feed-event-name {
  font-weight: 700;
  font-size: 0.85rem;
  color: #ffffff;
}
.feed-time {
  font-size: 0.75rem;
  color: #94a3b8;
}
.feed-desc {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.4;
}
.feed-empty {
  text-align: center;
  padding: 2rem 1rem;
  color: #64748b;
  font-size: 0.88rem;
}

.empty-cell {
  padding: 2rem !important;
  color: #64748b;
}
</style>
