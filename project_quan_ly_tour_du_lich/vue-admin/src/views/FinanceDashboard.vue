<template>
  <div class="vue-admin-finance-dashboard">
    <!-- TOAST POPUP -->
    <transition name="fade">
      <div v-if="toast.visible" class="toast-popup" :class="toast.type">
        <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
        <span>{{ toast.message }}</span>
        <button @click="toast.visible = false" class="toast-close">&times;</button>
      </div>
    </transition>

    <!-- PAGE HERO -->
    <section class="finance-hero">
      <div class="hero-top">
        <div class="hero-title-group">
          <div class="kicker-pill">
            <i class="bi bi-wallet2 text-gold me-1"></i> FINANCIAL ANALYTICS & REVENUE COMMAND
          </div>
          <h1 class="hero-title">Báo Cáo Tài Chính & Hiệu Quả Kinh Doanh</h1>
          <p class="hero-desc">
            Bao quát toàn cảnh doanh thu, chi phí, lợi nhuận ròng và các chỉ số hiệu quả kinh doanh toàn diện
          </p>
        </div>

        <!-- DATE RANGE SELECTOR & QUICK FILTERS -->
        <div class="date-filter-box">
          <div class="preset-buttons">
            <button 
              v-for="p in presets" 
              :key="p.id" 
              @click="applyPreset(p.id)"
              class="btn-preset"
              :class="{ active: selectedPreset === p.id }"
            >
              {{ p.label }}
            </button>
          </div>

          <div class="custom-range">
            <input v-model="tuNgay" type="date" class="range-date-input" title="Từ ngày" />
            <span class="range-sep">→</span>
            <input v-model="denNgay" type="date" class="range-date-input" title="Đến ngày" />
            <button @click="fetchData(true)" class="btn-apply-range" :class="{ rotating: isFetching }" title="Cập nhật dữ liệu">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- KEY FINANCIAL KPI CARDS -->
      <div class="kpi-grid">
        <!-- TỔNG THU -->
        <div class="kpi-card card-revenue">
          <div class="kpi-inner">
            <div class="kpi-info">
              <div class="kpi-label">TỔNG THU THỰC TẾ</div>
              <div class="kpi-val text-gold">{{ formatCurrency(tongThu) }}</div>
              <div class="kpi-sub">
                <i class="bi bi-calendar3 me-1"></i> {{ formatDateRange(tuNgay, denNgay) }}
              </div>
            </div>
            <div class="kpi-icon-wrap icon-gold">
              <i class="bi bi-cash-stack"></i>
            </div>
          </div>
        </div>

        <!-- TỔNG CHI -->
        <div class="kpi-card card-expense">
          <div class="kpi-inner">
            <div class="kpi-info">
              <div class="kpi-label">TỔNG CHI PHÍ VẬN HÀNH</div>
              <div class="kpi-val text-red">{{ formatCurrency(tongChi) }}</div>
              <div class="kpi-sub">Chi phí dịch vụ NCC & vận hành tour</div>
            </div>
            <div class="kpi-icon-wrap icon-red">
              <i class="bi bi-arrow-down-right-circle-fill"></i>
            </div>
          </div>
        </div>

        <!-- LỢI NHUẬN RÒNG -->
        <div class="kpi-card card-profit" :class="loiNhuan >= 0 ? 'border-profit-pos' : 'border-profit-neg'">
          <div class="kpi-inner">
            <div class="kpi-info">
              <div class="kpi-label">LỢI NHUẬN THUẦN</div>
              <div class="kpi-val" :class="loiNhuan >= 0 ? 'text-green' : 'text-danger'">
                {{ formatCurrency(loiNhuan) }}
              </div>
              <div class="kpi-sub">
                <span :class="loiNhuan >= 0 ? 'text-green' : 'text-danger'">
                  <i class="bi" :class="loiNhuan >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow'"></i>
                  Tỷ suất: {{ profitMargin }}%
                </span>
                <span class="ms-1 text-muted">trên doanh thu</span>
              </div>
            </div>
            <div class="kpi-icon-wrap" :class="loiNhuan >= 0 ? 'icon-green' : 'icon-red'">
              <i class="bi" :class="loiNhuan >= 0 ? 'bi-trophy-fill' : 'bi-exclamation-triangle-fill'"></i>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- CONTENT LAYOUT: TOP TOURS & QUICK NAVIGATION -->
    <div class="dashboard-content-grid">
      <!-- LEFT: TOP REVENUE TOURS -->
      <div class="panel-card top-tours-panel">
        <div class="panel-header">
          <div class="panel-title-wrap">
            <h3 class="panel-title">
              <i class="bi bi-stars text-gold me-2"></i>
              Top Tour Doanh Thu Cao Nhất
            </h3>
            <span class="badge-counter">{{ topTours.length }} Tour</span>
          </div>
          <a href="index.php?act=admin/laiLoTour" class="panel-link">
            <span>Chi tiết lãi lỗ</span>
            <i class="bi bi-arrow-right"></i>
          </a>
        </div>

        <div class="panel-body">
          <div v-if="topTours.length > 0" class="top-tours-list">
            <div 
              v-for="(item, idx) in topTours" 
              :key="idx" 
              class="tour-rank-row"
              :class="'rank-' + (idx + 1)"
            >
              <div class="rank-badge-wrap">
                <span class="rank-badge" :class="'badge-' + (idx + 1)">#{{ idx + 1 }}</span>
              </div>

              <div class="tour-details">
                <div class="tour-title-row">
                  <h4 class="tour-name-text">{{ item.tour?.ten_tour || 'Tour #' + (idx + 1) }}</h4>
                  <span class="tour-amount-text">{{ formatCurrency(item.doanh_thu) }}</span>
                </div>

                <!-- Progress Bar comparison -->
                <div class="tour-bar-wrap">
                  <div class="tour-bar-fill" :style="{ width: calcTourShare(item.doanh_thu) + '%' }"></div>
                </div>

                <div class="tour-meta-sub">
                  <span>Chiếm {{ calcTourShare(item.doanh_thu) }}% tổng thu</span>
                  <a :href="'index.php?act=admin/giaoDichTheoTour&tour_id=' + (item.tour?.tour_id || 0)" class="link-view-gd" v-if="item.tour?.tour_id">
                    <i class="bi bi-receipt"></i> Giao dịch tour
                  </a>
                </div>
              </div>
            </div>
          </div>

          <div v-else class="empty-placeholder">
            <i class="bi bi-inbox font-xl text-muted d-block mb-2"></i>
            <p class="text-muted mb-0">Chưa có dữ liệu giao dịch trong khoảng thời gian này.</p>
          </div>
        </div>
      </div>

      <!-- RIGHT: QUICK ACCESS CARDS -->
      <div class="panel-card quick-access-panel">
        <div class="panel-header">
          <div class="panel-title-wrap">
            <h3 class="panel-title">
              <i class="bi bi-grid-fill text-cyan me-2"></i>
              Bản Đồ Nghiệp Vụ Tài Chính
            </h3>
          </div>
        </div>

        <div class="panel-body">
          <div class="quick-links-grid">
            <a href="index.php?act=admin/lichSuGiaoDich" class="quick-nav-card">
              <div class="quick-nav-icon icon-cyan">
                <i class="bi bi-clock-history"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">Lịch Sử Giao Dịch</strong>
                <span class="quick-nav-desc">Tra cứu chi tiết từng phiếu thu, chi ngân quỹ</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>

            <a href="index.php?act=admin/thuChiTour" class="quick-nav-card">
              <div class="quick-nav-icon icon-gold">
                <i class="bi bi-map-fill"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">Thu Chi Từng Tour</strong>
                <span class="quick-nav-desc">Báo cáo cân đối dòng tiền theo chuyến đi</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>

            <a href="index.php?act=admin/congNo" class="quick-nav-card">
              <div class="quick-nav-icon icon-amber">
                <i class="bi bi-credit-card-2-front-fill"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">Quản Lý Công Nợ</strong>
                <span class="quick-nav-desc">Công nợ khách hàng, nhà cung cấp & HDV</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>

            <a href="index.php?act=admin/laiLoTour" class="quick-nav-card">
              <div class="quick-nav-icon icon-green">
                <i class="bi bi-graph-up-arrow"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">Lãi Lỗ Từng Tour</strong>
                <span class="quick-nav-desc">Phân tích biên lợi nhuận kinh doanh từng tuyến</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>

            <a href="index.php?act=admin/duToanTour" class="quick-nav-card">
              <div class="quick-nav-icon icon-blue">
                <i class="bi bi-calculator-fill"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">Dự Toán Chi Phí Tour</strong>
                <span class="quick-nav-desc">Lập kế hoạch ngân sách và phê duyệt hạn mức</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>

            <a href="index.php?act=admin/soSanhDuToan" class="quick-nav-card">
              <div class="quick-nav-icon icon-purple">
                <i class="bi bi-sliders2-vertical"></i>
              </div>
              <div class="quick-nav-text">
                <strong class="quick-nav-title">So Sánh Dự Toán vs Thực Tế</strong>
                <span class="quick-nav-desc">Kiểm soát chênh lệch chi phí, phát hiện vượt định mức</span>
              </div>
              <i class="bi bi-chevron-right arrow-indicator"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
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
const tuNgay = ref('');
const denNgay = ref('');
const tongThu = ref(0);
const tongChi = ref(0);
const loiNhuan = ref(0);
const topTours = ref([]);
const isFetching = ref(false);
const selectedPreset = ref('this_month');
const toast = ref({ visible: false, message: '', type: 'success' });

const presets = [
  { id: 'this_month', label: 'Tháng Này' },
  { id: 'last_month', label: 'Tháng Trước' },
  { id: 'this_quarter', label: 'Quý Này' },
  { id: 'this_year', label: 'Năm Nay' }
];

const profitMargin = computed(() => {
  if (!tongThu.value || tongThu.value <= 0) return '0.0';
  const margin = (loiNhuan.value / tongThu.value) * 100;
  return margin.toFixed(1);
});

function showToast(msg, type = 'success') {
  toast.value = { visible: true, message: msg, type };
  setTimeout(() => {
    if (toast.value.message === msg) {
      toast.value.visible = false;
    }
  }, 4000);
}

function formatCurrency(val) {
  const n = Number(val) || 0;
  return n.toLocaleString('vi-VN') + ' đ';
}

function formatDateRange(start, end) {
  if (!start || !end) return '';
  const s = new Date(start);
  const e = new Date(end);
  return `Từ ${s.toLocaleDateString('vi-VN')} đến ${e.toLocaleDateString('vi-VN')}`;
}

function calcTourShare(amount) {
  if (!tongThu.value || tongThu.value <= 0) return 0;
  const pct = ((Number(amount) || 0) / tongThu.value) * 100;
  return Math.min(100, Math.max(1, Number(pct.toFixed(1))));
}

function applyPreset(presetId) {
  selectedPreset.value = presetId;
  const now = new Date();
  const y = now.getFullYear();
  const m = now.getMonth();

  if (presetId === 'this_month') {
    const firstDay = new Date(y, m, 1);
    const lastDay = new Date(y, m + 1, 0);
    tuNgay.value = formatDateToISO(firstDay);
    denNgay.value = formatDateToISO(lastDay);
  } else if (presetId === 'last_month') {
    const firstDay = new Date(y, m - 1, 1);
    const lastDay = new Date(y, m, 0);
    tuNgay.value = formatDateToISO(firstDay);
    denNgay.value = formatDateToISO(lastDay);
  } else if (presetId === 'this_quarter') {
    const q = Math.floor(m / 3);
    const firstDay = new Date(y, q * 3, 1);
    const lastDay = new Date(y, q * 3 + 3, 0);
    tuNgay.value = formatDateToISO(firstDay);
    denNgay.value = formatDateToISO(lastDay);
  } else if (presetId === 'this_year') {
    tuNgay.value = `${y}-01-01`;
    denNgay.value = `${y}-12-31`;
  }

  fetchData(true);
}

function formatDateToISO(d) {
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function applyPayload(data) {
  if (!data) return;
  if (data.tuNgay) tuNgay.value = data.tuNgay;
  if (data.denNgay) denNgay.value = data.denNgay;
  if (data.tongThu !== undefined) tongThu.value = Number(data.tongThu);
  if (data.tongChi !== undefined) tongChi.value = Number(data.tongChi);
  if (data.loiNhuan !== undefined) loiNhuan.value = Number(data.loiNhuan);
  if (Array.isArray(data.topTours)) topTours.value = data.topTours;
}

async function fetchData(manual = false) {
  if (isFetching.value) return;
  isFetching.value = true;
  try {
    const params = new URLSearchParams();
    if (tuNgay.value) params.append('tu_ngay', tuNgay.value);
    if (denNgay.value) params.append('den_ngay', denNgay.value);

    const res = await fetch('index.php?act=admin/apiBaoCaoTaiChinh&' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Mã lỗi mạng: ' + res.status);
    const data = await res.json();
    if (data.success) {
      applyPayload(data);
      if (manual) showToast('Đã cập nhật số liệu tài chính thành công');
    }
  } catch (err) {
    showToast('Lỗi khi tải dữ liệu: ' + err.message, 'error');
  } finally {
    isFetching.value = false;
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyPayload(props.initialData);
  } else if (window.__ADMIN_FINANCE_INIT__) {
    applyPayload(window.__ADMIN_FINANCE_INIT__);
  } else {
    // Default this month
    const now = new Date();
    tuNgay.value = formatDateToISO(new Date(now.getFullYear(), now.getMonth(), 1));
    denNgay.value = formatDateToISO(new Date(now.getFullYear(), now.getMonth() + 1, 0));
    fetchData();
  }
});
</script>

<style scoped>
.vue-admin-finance-dashboard {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  color: #f8fafc;
  font-family: inherit;
}

/* Toast */
.toast-popup {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.92rem;
  box-shadow: 0 12px 30px rgba(0,0,0,0.35);
}
.toast-popup.success {
  background: #14532d;
  color: #86efac;
  border: 1px solid rgba(34, 197, 94, 0.4);
}
.toast-popup.error {
  background: #7f1d1d;
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.4);
}
.toast-close {
  background: none;
  border: none;
  color: inherit;
  font-size: 1.2rem;
  cursor: pointer;
}

/* Hero Section */
.finance-hero {
  padding: 26px 30px;
  border-radius: 20px;
  background: radial-gradient(circle at 90% 20%, rgba(212, 175, 55, 0.2), transparent 40%),
              radial-gradient(circle at 10% 80%, rgba(34, 197, 94, 0.12), transparent 40%),
              linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
  border: 1px solid rgba(212, 175, 55, 0.22);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
}
.hero-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  flex-wrap: wrap;
  margin-bottom: 24px;
}
.kicker-pill {
  display: inline-flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #d4af37;
  margin-bottom: 8px;
}
.hero-title {
  font-size: 1.85rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
}
.hero-desc {
  font-size: 0.94rem;
  color: #94a3b8;
  max-width: 650px;
  margin: 0;
}

/* Date Filters */
.date-filter-box {
  background: rgba(11, 17, 32, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 10px 14px;
  border-radius: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.preset-buttons {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
}
.btn-preset {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-preset:hover {
  background: rgba(212, 175, 55, 0.15);
  border-color: rgba(212, 175, 55, 0.3);
  color: #fde047;
}
.btn-preset.active {
  background: rgba(212, 175, 55, 0.25);
  border-color: #d4af37;
  color: #fde047;
}
.custom-range {
  display: flex;
  align-items: center;
  gap: 8px;
}
.range-date-input {
  background: rgba(15, 23, 42, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  padding: 6px 10px;
  border-radius: 8px;
  font-size: 0.82rem;
  outline: none;
}
.range-date-input:focus {
  border-color: #d4af37;
}
.range-sep {
  color: #94a3b8;
  font-weight: 700;
}
.btn-apply-range {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  background: rgba(212, 175, 55, 0.2);
  border: 1px solid rgba(212, 175, 55, 0.4);
  color: #fde047;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
}
.btn-apply-range:hover {
  background: rgba(212, 175, 55, 0.35);
  color: #ffffff;
}
.rotating i { animation: spin 1s infinite linear; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}
.kpi-card {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 22px;
  backdrop-filter: blur(12px);
  border-left: 4px solid;
  transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(0,0,0,0.25);
}
.card-revenue { border-left-color: #d4af37; }
.card-expense { border-left-color: #f87171; }
.border-profit-pos { border-left-color: #22c55e; }
.border-profit-neg { border-left-color: #ef4444; }

.kpi-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.kpi-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.06em;
  margin-bottom: 6px;
}
.kpi-val {
  font-size: 1.85rem;
  font-weight: 800;
  line-height: 1.2;
}
.kpi-sub {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 4px;
}

.kpi-icon-wrap {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.6rem;
  flex-shrink: 0;
}
.icon-gold { background: rgba(212, 175, 55, 0.16); color: #fde047; }
.icon-red { background: rgba(239, 68, 68, 0.16); color: #fca5a5; }
.icon-green { background: rgba(34, 197, 94, 0.16); color: #86efac; }

/* Dashboard Content Grid */
.dashboard-content-grid {
  display: grid;
  grid-template-columns: 1.15fr 0.85fr;
  gap: 1.5rem;
}
@media (max-width: 1024px) {
  .dashboard-content-grid { grid-template-columns: 1fr; }
}

.panel-card {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  overflow: hidden;
  backdrop-filter: blur(12px);
}
.panel-header {
  padding: 18px 24px;
  background: rgba(11, 17, 32, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.panel-title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.panel-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
}
.badge-counter {
  background: rgba(212, 175, 55, 0.18);
  color: #fde047;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
}
.panel-link {
  color: #d4af37;
  text-decoration: none;
  font-size: 0.84rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: color 0.2s;
}
.panel-link:hover { color: #fde047; }
.panel-body {
  padding: 20px 24px;
}

/* Top Tours List */
.top-tours-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.tour-rank-row {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  border-radius: 12px;
  background: rgba(11, 17, 32, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.05);
  transition: all 0.2s;
}
.tour-rank-row:hover {
  background: rgba(255, 255, 255, 0.03);
  border-color: rgba(212, 175, 55, 0.2);
  transform: translateX(4px);
}
.rank-badge-wrap {
  flex-shrink: 0;
}
.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.95rem;
  background: rgba(255, 255, 255, 0.06);
  color: #94a3b8;
}
.badge-1 { background: linear-gradient(135deg, #d4af37 0%, #b89320 100%); color: #0b1120; }
.badge-2 { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); color: #0b1120; }
.badge-3 { background: linear-gradient(135deg, #b45309 0%, #78350f 100%); color: #ffffff; }

.tour-details {
  flex: 1;
}
.tour-title-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  margin-bottom: 6px;
}
.tour-name-text {
  font-size: 0.94rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}
.tour-amount-text {
  font-size: 0.98rem;
  font-weight: 800;
  color: #d4af37;
  white-space: nowrap;
}
.tour-bar-wrap {
  height: 6px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  overflow: hidden;
  margin-bottom: 6px;
}
.tour-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #d4af37, #f59e0b);
  border-radius: 999px;
  transition: width 0.5s ease-out;
}
.tour-meta-sub {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.78rem;
  color: #94a3b8;
}
.link-view-gd {
  color: #93c5fd;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 4px;
}
.link-view-gd:hover { color: #bfdbfe; }
.empty-placeholder {
  text-align: center;
  padding: 40px;
}

/* Quick Navigation Grid */
.quick-links-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}
.quick-nav-card {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 16px;
  background: rgba(11, 17, 32, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.06);
  border-radius: 12px;
  text-decoration: none;
  color: #cbd5e1;
  transition: all 0.2s;
}
.quick-nav-card:hover {
  background: rgba(212, 175, 55, 0.1);
  border-color: rgba(212, 175, 55, 0.35);
  transform: translateX(4px);
  color: #ffffff;
}
.quick-nav-icon {
  width: 44px;
  height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.icon-cyan { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }
.icon-gold { background: rgba(212, 175, 55, 0.15); color: #fde047; }
.icon-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.icon-green { background: rgba(34, 197, 94, 0.15); color: #86efac; }
.icon-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.icon-purple { background: rgba(168, 85, 247, 0.15); color: #c084fc; }

.quick-nav-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.quick-nav-title {
  font-size: 0.92rem;
  color: #ffffff;
}
.quick-nav-desc {
  font-size: 0.78rem;
  color: #94a3b8;
}
.arrow-indicator {
  color: #64748b;
  font-size: 0.9rem;
  transition: transform 0.2s, color 0.2s;
}
.quick-nav-card:hover .arrow-indicator {
  transform: translateX(3px);
  color: #d4af37;
}

/* Helpers */
.text-gold { color: #d4af37 !important; }
.text-red { color: #f87171 !important; }
.text-green { color: #4ade80 !important; }
.text-cyan { color: #22d3ee !important; }
.font-xl { font-size: 2rem; }
</style>
