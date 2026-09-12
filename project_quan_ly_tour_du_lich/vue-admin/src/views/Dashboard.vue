<template>
  <div class="vue-admin-dashboard">
    <!-- TOP HEADER BAR -->
    <header class="dash-header">
      <div class="header-left">
        <div class="badge-tag">
          <span class="pulse-dot"></span>
          <span>Vue 3 Real-time Engine</span>
        </div>
        <h1 class="dash-title">Bảng Điều Khiển Quản Trị</h1>
        <p class="dash-subtitle">
          Tổng quan hoạt động kinh doanh, chỉ số vận hành và tự động hóa hệ thống du lịch
        </p>
      </div>

      <div class="header-actions">
        <button 
          class="btn-action btn-refresh" 
          :class="{ 'is-loading': isRefreshing }"
          @click="fetchLatestData"
          title="Làm mới dữ liệu tức thì"
        >
          <i class="bi bi-arrow-clockwise" :class="{ 'spin-anim': isRefreshing }"></i>
          <span>{{ isRefreshing ? 'Đang đồng bộ...' : 'Làm mới' }}</span>
        </button>

        <a href="index.php?act=admin/quanLyTour" class="btn-action btn-primary-grad">
          <i class="bi bi-plus-circle"></i>
          <span>Quản lý Tour</span>
        </a>

        <a href="index.php?act=admin/quanLyBooking" class="btn-action btn-secondary-outline">
          <i class="bi bi-bookmark-check"></i>
          <span>Bookings</span>
        </a>
      </div>
    </header>

    <!-- ALERT / NOTIFICATION BANNER IF ANY -->
    <div v-if="kpiAlerts.overdueDebt > 0 || kpiAlerts.bookingPending > 0" class="alert-banner">
      <div class="alert-item" v-if="kpiAlerts.bookingPending > 0">
        <i class="bi bi-exclamation-circle-fill text-warning"></i>
        <span>Có <strong>{{ kpiAlerts.bookingPending }}</strong> booking đang chờ xác nhận từ khách hàng.</span>
        <a href="index.php?act=admin/quanLyBooking" class="alert-link">Xem ngay &rarr;</a>
      </div>
      <div class="alert-item" v-if="kpiAlerts.overdueDebt > 0">
        <i class="bi bi-shield-exclamation text-danger"></i>
        <span>Phát hiện <strong>{{ kpiAlerts.overdueDebt }}</strong> công nợ HDV quá hạn cần xử lý.</span>
        <a href="index.php?act=admin/quanLyCongNoHDV" class="alert-link">Kiểm tra &rarr;</a>
      </div>
    </div>

    <!-- METRIC KPI CARDS -->
    <section class="kpi-grid">
      <!-- Card 1: Tổng Tour -->
      <div class="kpi-card card-teal">
        <div class="kpi-header">
          <span class="kpi-label">Tổng Tour Hệ Thống</span>
          <div class="kpi-icon-wrap">
            <i class="bi bi-compass"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNumber(metrics.total_tours) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend positive">
              <i class="bi bi-arrow-up-right"></i> Đang theo dõi
            </span>
            <span class="kpi-subtext">Danh mục tour hoạt động</span>
          </div>
        </div>
      </div>

      <!-- Card 2: Doanh Thu Tháng -->
      <div class="kpi-card card-emerald">
        <div class="kpi-header">
          <span class="kpi-label">Doanh Thu Trong Tháng</span>
          <div class="kpi-icon-wrap">
            <i class="bi bi-currency-dollar"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatCurrency(metrics.monthly_revenue) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend positive">
              <i class="bi bi-graph-up-arrow"></i> Tháng này
            </span>
            <span class="kpi-subtext">Tổng thu thực nhận</span>
          </div>
        </div>
      </div>

      <!-- Card 3: Tổng Booking -->
      <div class="kpi-card card-blue">
        <div class="kpi-header">
          <span class="kpi-label">Tổng Đơn Đặt Tour</span>
          <div class="kpi-icon-wrap">
            <i class="bi bi-calendar2-check"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNumber(metrics.total_bookings) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend neutral">
              <i class="bi bi-check2-all"></i> Hoàn thành & xác nhận
            </span>
            <span class="kpi-subtext">Khách đã đăng ký</span>
          </div>
        </div>
      </div>

      <!-- Card 4: Khách Hàng -->
      <div class="kpi-card card-purple">
        <div class="kpi-header">
          <span class="kpi-label">Khách Hàng Mới</span>
          <div class="kpi-icon-wrap">
            <i class="bi bi-people"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNumber(metrics.total_customers) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend positive">
              <i class="bi bi-person-plus"></i> Thành viên
            </span>
            <span class="kpi-subtext">Khách hàng ghi nhận</span>
          </div>
        </div>
      </div>

      <!-- Card 5: Chờ Xử Lý -->
      <div class="kpi-card card-amber">
        <div class="kpi-header">
          <span class="kpi-label">Booking Chờ Duyệt</span>
          <div class="kpi-icon-wrap">
            <i class="bi bi-clock-history"></i>
          </div>
        </div>
        <div class="kpi-body">
          <div class="kpi-value">{{ formatNumber(metrics.pending_bookings) }}</div>
          <div class="kpi-meta">
            <span class="kpi-trend warning" v-if="metrics.pending_bookings > 0">
              <i class="bi bi-bell"></i> Cần xử lý sớm
            </span>
            <span class="kpi-trend positive" v-else>
              <i class="bi bi-check-circle"></i> Đã sạch đơn
            </span>
            <span class="kpi-subtext">Đơn chờ phản hồi</span>
          </div>
        </div>
      </div>
    </section>

    <!-- OPERATIONAL KPI STRIP -->
    <section class="kpi-strip" v-if="dailyKpi">
      <div class="strip-item">
        <span class="strip-label">Ngày báo cáo KPI:</span>
        <span class="strip-val">{{ formatDate(dailyKpi.summary_date) }}</span>
      </div>
      <div class="strip-item">
        <span class="strip-label">Doanh thu ngày:</span>
        <span class="strip-val highlight">{{ formatCurrency(dailyKpi.revenue_success_amount) }}</span>
      </div>
      <div class="strip-item">
        <span class="strip-label">Booking mới hôm nay:</span>
        <span class="strip-val">{{ dailyKpi.booking_new_count || 0 }} đơn</span>
      </div>
      <div class="strip-item">
        <span class="strip-label">Tỷ lệ chuyển đổi:</span>
        <span class="strip-val badge-rate">{{ dailyKpi.conversion_rate_pct || 0 }}%</span>
      </div>
      <div class="strip-item">
        <span class="strip-label">Lượt hủy:</span>
        <span class="strip-val danger-text">{{ dailyKpi.booking_cancel_count || 0 }}</span>
      </div>
    </section>

    <!-- CHARTS SECTION -->
    <section class="charts-grid">
      <!-- Line Chart: Doanh thu 12 tháng -->
      <div class="chart-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Xu Hướng Doanh Thu (12 Tháng Gần Nhất)</h3>
            <p class="panel-subtitle">Theo dõi tổng thu thực tế theo từng tháng trong năm</p>
          </div>
          <span class="panel-badge">Doanh thu (VNĐ)</span>
        </div>
        <div class="chart-container">
          <canvas ref="revenueChartRef"></canvas>
        </div>
      </div>

      <!-- Doughnut Chart: Trạng thái Booking -->
      <div class="chart-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Phân Bổ Trạng Thái Booking</h3>
            <p class="panel-subtitle">Tỷ lệ đơn hàng theo trạng thái xử lý</p>
          </div>
          <span class="panel-badge">Trực quan hóa</span>
        </div>
        <div class="chart-container donut-wrap">
          <canvas ref="bookingStatusChartRef"></canvas>
        </div>
      </div>
    </section>

    <!-- TOP TOURS TABLE & AUTOMATION FEED -->
    <section class="bottom-grid">
      <!-- Top Tours Table -->
      <div class="table-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Top Tour Hiệu Quả & Lợi Nhuận</h3>
            <p class="panel-subtitle">Xếp hạng tour mang lại doanh thu và lợi nhuận cao nhất</p>
          </div>
          
          <div class="table-controls">
            <div class="search-box">
              <i class="bi bi-search"></i>
              <input 
                type="text" 
                v-model="searchQuery" 
                placeholder="Tìm kiếm tour..." 
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
                  Tổng Thu 
                  <i class="bi" :class="getSortIcon('tong_thu')"></i>
                </th>
                <th @click="sortBy('tong_chi_thuc_te')" class="sortable text-end">
                  Chi Phí 
                  <i class="bi" :class="getSortIcon('tong_chi_thuc_te')"></i>
                </th>
                <th @click="sortBy('loi_nhuan')" class="sortable text-end">
                  Lợi Nhuận 
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
                <td class="text-end fw-semibold text-success">
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

      <!-- Live Automation & Activity Feed -->
      <div class="feed-panel">
        <div class="panel-header">
          <div>
            <h3 class="panel-title">Sự Kiện Tự Động Hóa 24h</h3>
            <p class="panel-subtitle">Nhật ký xử lý thông minh gần đây</p>
          </div>
          <a href="index.php?act=admin/automationDashboard" class="feed-more-link">
            Xem tất cả &rarr;
          </a>
        </div>

        <div class="feed-list" v-if="recentEvents.length > 0">
          <div 
            v-for="(event, idx) in recentEvents.slice(0, 7)" 
            :key="idx" 
            class="feed-item"
          >
            <div class="feed-icon" :class="getEventStatusClass(event.status)">
              <i class="bi" :class="getEventIcon(event.event_type)"></i>
            </div>
            <div class="feed-content">
              <div class="feed-title-line">
                <span class="feed-event-name">{{ event.event_type || 'Event' }}</span>
                <span class="feed-time">{{ formatTimeAgo(event.created_at) }}</span>
              </div>
              <p class="feed-desc">{{ event.description || event.payload_summary || 'Hệ thống đã tự động thực thi tiến trình' }}</p>
            </div>
          </div>
        </div>
        <div v-else class="feed-empty">
          <i class="bi bi-check-circle-fill text-success fs-3 mb-2"></i>
          <p>Hệ thống tự động hóa đang hoạt động ổn định, không có lỗi phát sinh.</p>
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

const metrics = ref({
  total_tours: 0,
  total_revenue: 0,
  total_bookings: 0,
  total_customers: 0,
  monthly_revenue: 0,
  pending_bookings: 0,
  overdue_debt: 0,
  automation_events_24h: 0,
});

const charts = ref({
  revenue_by_month: {},
  booking_status: {},
  customers_by_month: {},
  tour_status: {},
});

const dailyKpi = ref(null);
const kpiAlerts = ref({
  bookingPending: 0,
  overdueDebt: 0,
  paymentMismatch: 0,
  monthToDateRevenue: 0,
});

const tours = ref([]);
const recentEvents = ref([]);

// Canvas refs
const revenueChartRef = ref(null);
const bookingStatusChartRef = ref(null);
let revenueChartInstance = null;
let bookingStatusChartInstance = null;

// Apply payload
function applyData(data) {
  if (!data) return;
  if (data.metrics) metrics.value = { ...metrics.value, ...data.metrics };
  if (data.charts) charts.value = { ...charts.value, ...data.charts };
  if (data.daily_kpi) dailyKpi.value = data.daily_kpi;
  if (data.kpi_alerts) kpiAlerts.value = { ...kpiAlerts.value, ...data.kpi_alerts };
  if (data.top_tours) tours.value = data.top_tours;
  if (data.recent_events) recentEvents.value = data.recent_events;

  nextTick(() => {
    renderCharts();
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

// Charts rendering
function renderCharts() {
  // 1. Revenue trend chart
  if (revenueChartRef.value) {
    if (revenueChartInstance) {
      revenueChartInstance.destroy();
    }

    const rawMonths = charts.value.revenue_by_month || {};
    const labels = Object.keys(rawMonths);
    const dataValues = Object.values(rawMonths).map(v => Number(v) || 0);

    const ctx = revenueChartRef.value.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(14, 165, 233, 0.45)');
    gradient.addColorStop(1, 'rgba(14, 165, 233, 0.02)');

    revenueChartInstance = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels.length ? labels : ['Th 1', 'Th 2', 'Th 3', 'Th 4', 'Th 5', 'Th 6', 'Th 7', 'Th 8', 'Th 9', 'Th 10', 'Th 11', 'Th 12'],
        datasets: [{
          label: 'Doanh Thu (VNĐ)',
          data: dataValues.length ? dataValues : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
          borderColor: '#0284c7',
          borderWidth: 3,
          backgroundColor: gradient,
          fill: true,
          tension: 0.35,
          pointRadius: 4,
          pointHoverRadius: 6,
          pointBackgroundColor: '#0284c7',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#0b1120',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 12,
            titleFont: { size: 13, weight: '600' },
            bodyFont: { size: 12 },
            cornerRadius: 8,
            callbacks: {
              label: (item) => ' Doanh thu: ' + Number(item.raw).toLocaleString('vi-VN') + ' đ'
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
                if (val >= 1000000000) return (val / 1000000000).toFixed(1) + 'B';
                if (val >= 1000000) return (val / 1000000).toFixed(0) + 'M';
                if (val >= 1000) return (val / 1000).toFixed(0) + 'K';
                return val;
              }
            }
          }
        }
      }
    });
  }

  // 2. Booking status chart
  if (bookingStatusChartRef.value) {
    if (bookingStatusChartInstance) {
      bookingStatusChartInstance.destroy();
    }

    const rawStatus = charts.value.booking_status || {};
    const labels = Object.keys(rawStatus);
    const dataValues = Object.values(rawStatus).map(v => Number(v) || 0);

    const palette = ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'];

    const ctx = bookingStatusChartRef.value.getContext('2d');
    bookingStatusChartInstance = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels.length ? labels : ['Chờ xử lý', 'Đã duyệt', 'Đã cọc', 'Hoàn tất', 'Đã hủy'],
        datasets: [{
          data: dataValues.length ? dataValues : [1, 1, 1, 1, 1],
          backgroundColor: palette.slice(0, Math.max(labels.length, 5)),
          borderWidth: 2,
          borderColor: '#161f2e',
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '70%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              font: { size: 12 },
              color: '#cbd5e1',
              padding: 14
            }
          },
          tooltip: {
            backgroundColor: '#0b1120',
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: (item) => ' ' + item.label + ': ' + item.raw + ' booking'
            }
          }
        }
      }
    });
  }
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
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
  padding: 1.5rem 2rem;
  border-radius: 1.25rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(14px);
}

.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(212, 175, 55, 0.15);
  color: #fde047;
  border: 1px solid rgba(212, 175, 55, 0.3);
  padding: 0.35rem 0.85rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}

.pulse-dot {
  width: 8px;
  height: 8px;
  background: #d4af37;
  border-radius: 50%;
  box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7);
  animation: pulse 1.6s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(212, 175, 55, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(212, 175, 55, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(212, 175, 55, 0); }
}

.dash-title {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0;
  letter-spacing: -0.02em;
}

.dash-subtitle {
  font-size: 0.95rem;
  color: #94a3b8;
  margin: 0.25rem 0 0;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
  align-items: center;
}

.btn-action {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.15rem;
  border-radius: 0.65rem;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #f1f5f9;
}
.btn-refresh:hover {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}
.spin-anim {
  animation: spin 0.8s linear infinite;
}
@keyframes spin {
  100% { transform: rotate(360deg); }
}

.btn-primary-grad {
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  font-weight: 700;
  border: none;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
}
.btn-primary-grad:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45);
  color: #0b1120;
}

.btn-secondary-outline {
  background: rgba(255, 255, 255, 0.05);
  color: #f1f5f9;
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.btn-secondary-outline:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}

/* ALERT BANNER */
.alert-banner {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.alert-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: rgba(245, 158, 11, 0.12);
  border: 1px solid rgba(245, 158, 11, 0.3);
  padding: 0.85rem 1.25rem;
  border-radius: 0.75rem;
  font-size: 0.9rem;
  color: #fde68a;
  backdrop-filter: blur(8px);
}
.alert-item .text-danger {
  color: #f87171 !important;
}
.alert-link {
  margin-left: auto;
  font-weight: 700;
  color: #fde047;
  text-decoration: none;
}
.alert-link:hover {
  text-decoration: underline;
}

/* KPI GRID */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
}

.kpi-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.35rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  position: relative;
  overflow: hidden;
}
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(255, 255, 255, 0.15);
}
.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
}
.card-teal::before { background: linear-gradient(90deg, #14b8a6, #0d9488); }
.card-emerald::before { background: linear-gradient(90deg, #10b981, #059669); }
.card-blue::before { background: linear-gradient(90deg, #0ea5e9, #0284c7); }
.card-purple::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
.card-amber::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
}
.kpi-label {
  font-size: 0.82rem;
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
  border-radius: 0.65rem;
  font-size: 1.15rem;
}
.card-teal .kpi-icon-wrap { background: rgba(20, 184, 166, 0.16); color: #2dd4bf; }
.card-emerald .kpi-icon-wrap { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.card-blue .kpi-icon-wrap { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.card-purple .kpi-icon-wrap { background: rgba(139, 92, 246, 0.16); color: #a78bfa; }
.card-amber .kpi-icon-wrap { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }

.kpi-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.03em;
  margin-bottom: 0.5rem;
}
.kpi-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.8rem;
}
.kpi-trend {
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}
.kpi-trend.positive { color: #34d399; }
.kpi-trend.neutral { color: #38bdf8; }
.kpi-trend.warning { color: #fbbf24; }
.kpi-subtext { color: #64748b; }

/* KPI STRIP */
.kpi-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
  background: rgba(15, 23, 42, 0.75);
  padding: 1rem 1.5rem;
  border-radius: 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  align-items: center;
  backdrop-filter: blur(10px);
}
.strip-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.88rem;
}
.strip-label { color: #94a3b8; }
.strip-val { font-weight: 700; color: #ffffff; }
.strip-val.highlight { color: #38bdf8; font-size: 1rem; }
.strip-val.badge-rate {
  background: rgba(16, 185, 129, 0.18);
  color: #6ee7b7;
  border: 1px solid rgba(16, 185, 129, 0.3);
  padding: 0.2rem 0.55rem;
  border-radius: 0.35rem;
}
.strip-val.danger-text { color: #f87171; }

/* CHARTS GRID */
.charts-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.25rem;
}
@media (max-width: 992px) {
  .charts-grid { grid-template-columns: 1fr; }
}

.chart-panel {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
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
}
.panel-subtitle {
  font-size: 0.85rem;
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

.chart-container {
  height: 280px;
  position: relative;
}
.donut-wrap {
  height: 260px;
}

/* BOTTOM GRID */
.bottom-grid {
  display: grid;
  grid-template-columns: 2.2fr 1fr;
  gap: 1.25rem;
}
@media (max-width: 1024px) {
  .bottom-grid { grid-template-columns: 1fr; }
}

.table-panel, .feed-panel {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
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
  width: 150px;
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

.empty-cell {
  padding: 2rem !important;
  color: #64748b;
}

/* FEED PANEL */
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
  gap: 1rem;
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

.feed-content {
  flex: 1;
}
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
</style>
