<template>
  <div class="vue-admin-schedule-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-calendar3-event"></i>
          <span>Điều Phối Lịch Trình</span>
        </div>
        <h1 class="header-title">Quản Lý Lịch Khởi Hành</h1>
        <p class="header-subtitle">
          Theo dõi tiến độ vận hành, cảnh báo xung đột HDV và phân bổ dịch vụ tour theo thời gian thực
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
        <div class="stat-icon-box"><i class="bi bi-exclamation-octagon-fill"></i></div>
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

        <!-- Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-info-circle me-1"></i>Trạng thái</label>
          <select v-model="selectedStatus" @change="fetchSchedules" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="ChoPhanBo">Đang chờ phân bổ</option>
            <option value="SapKhoiHanh">Sắp khởi hành</option>
            <option value="DangChay">Đang chạy</option>
            <option value="HoanThanh">Hoàn thành</option>
          </select>
        </div>

        <!-- Date From -->
        <div class="filter-col date-col">
          <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Từ ngày</label>
          <input 
            v-model="fromDate" 
            @change="fetchSchedules"
            type="date" 
            class="form-input-custom"
          />
        </div>

        <!-- Date To -->
        <div class="filter-col date-col">
          <label class="filter-label"><i class="bi bi-calendar-event me-1"></i>Đến ngày</label>
          <input 
            v-model="toDate" 
            @change="fetchSchedules"
            type="date" 
            class="form-input-custom"
          />
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
        <span>Tổng cộng: <strong>{{ filteredSchedules.length }}</strong> lịch trình</span>
        <span v-if="isLoading" class="loading-tag">
          <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
        </span>
      </div>

      <div class="schedules-grid">
        <div 
          v-for="item in filteredSchedules" 
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
                    {{ item.so_nhan_su || 0 }} nhân sự
                  </strong>
                </div>
              </div>
            </div>

            <!-- Card Action Footer -->
            <div class="card-footer-actions">
              <a 
                :href="'index.php?act=lichKhoiHanh/chiTiet&id=' + item.id" 
                class="btn-card btn-detail"
              >
                <i class="bi bi-eye"></i>
                <span>Xem Chi Tiết</span>
              </a>

              <a 
                v-if="item.tour_id"
                :href="'index.php?act=tour/phanBoNhanSuLichKhoiHanh&id=' + item.tour_id" 
                class="btn-card btn-assign"
              >
                <i class="bi bi-people"></i>
                <span>Phân Bổ</span>
              </a>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredSchedules.length === 0 && !isLoading" class="empty-box">
          <i class="bi bi-calendar-x fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">Không có lịch khởi hành nào</h4>
          <p class="text-secondary">Hãy thay đổi bộ lọc tìm kiếm hoặc tạo lịch khởi hành mới.</p>
        </div>
      </div>
    </section>
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

// State
const isLoading = ref(false);
const searchQuery = ref('');
const selectedStatus = ref('');
const fromDate = ref('');
const toDate = ref('');
const schedules = ref([]);
const csrfToken = ref('');

let searchTimer = null;

function onSearchInput() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchSchedules();
  }, 350);
}

function clearSearch() {
  searchQuery.value = '';
  fetchSchedules();
}

function resetFilters() {
  searchQuery.value = '';
  selectedStatus.value = '';
  fromDate.value = '';
  toDate.value = '';
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
    pending: all.filter(s => Number(s.so_nhan_su) === 0).length,
  };
});

const filteredSchedules = computed(() => {
  return schedules.value || [];
});

function applyData(data) {
  if (!data) return;
  schedules.value = data.schedules || [];
  if (data.filters) {
    if (data.filters.search) searchQuery.value = data.filters.search;
    if (data.filters.trang_thai) selectedStatus.value = data.filters.trang_thai;
    if (data.filters.tu_ngay) fromDate.value = data.filters.tu_ngay;
    if (data.filters.den_ngay) toDate.value = data.filters.den_ngay;
  }
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchSchedules() {
  isLoading.value = true;
  const params = new URLSearchParams({
    act: 'lichKhoiHanh/apiList'
  });
  if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
  if (selectedStatus.value) params.append('trang_thai', selectedStatus.value);
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
  if (Number(item.so_nhan_su) === 0) return 'st-pending';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'st-upcoming';
    case 'DangChay': return 'st-ongoing';
    case 'HoanThanh': return 'st-completed';
    default: return 'st-upcoming';
  }
}

function getStatusText(item) {
  if (Number(item.so_nhan_su) === 0) return 'Đang chờ phân bổ';
  switch (item.trang_thai) {
    case 'SapKhoiHanh': return 'Sắp khởi hành';
    case 'DangChay': return 'Đang chạy';
    case 'HoanThanh': return 'Hoàn thành';
    default: return item.trang_thai || 'Sắp khởi hành';
  }
}

function getScheduleBorderClass(item) {
  if (Number(item.so_nhan_su) === 0) return 'border-amber';
  if (item.coTrungLichHDV) return 'border-rose';
  if (item.trang_thai === 'DangChay') return 'border-emerald';
  if (item.trang_thai === 'HoanThanh') return 'border-slate';
  return 'border-sky';
}

function hasWarning(item) {
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
  if (props.initialData && Object.keys(props.initialData).length > 0) {
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

.empty-box {
  grid-column: 1 / -1;
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 4rem 2rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
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
}
</style>
