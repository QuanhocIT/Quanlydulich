<template>
  <div class="vue-admin-salary-detail">
    <!-- 1. TOP NAVIGATION & HEADER (MATCHING IMAGE 2) -->
    <div class="top-nav-bar">
      <div class="top-nav-left">
        <a href="index.php?act=admin/quanLyLuongThuong" class="btn-back-square" title="Quay lại Quản lý lương thưởng">
          <i class="bi bi-arrow-left"></i>
        </a>
        <h2 class="page-nav-title">Chi tiết lương thưởng nhân sự</h2>
      </div>

      <div class="period-switcher">
        <!-- Month Selector with Calendar Icon Inside -->
        <div class="select-icon-box">
          <i class="bi bi-calendar3 icon-cal"></i>
          <select v-model="selectedMonth" @change="onPeriodChange" class="select-period-month" :disabled="showAll">
            <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
          </select>
        </div>

        <!-- Year Selector -->
        <select v-model="selectedYear" @change="onPeriodChange" class="select-period-year" :disabled="showAll">
          <option v-for="y in availableYears" :key="y" :value="y">Năm {{ y }}</option>
        </select>

        <!-- Toggle All Button -->
        <button 
          class="btn-toggle-all" 
          :class="{ active: showAll }"
          @click="toggleShowAll"
        >
          {{ showAll ? 'Xem theo tháng' : 'Xem tất cả' }}
        </button>
      </div>
    </div>

    <!-- 2. EMPLOYEE INFO & 3 SUMMARY CARDS (MATCHING IMAGE 2) -->
    <section class="overview-grid" v-if="nhanSu">
      <!-- Staff Profile Card (Left) -->
      <div class="staff-profile-card">
        <div class="staff-avatar-wrap">
          <img v-if="nhanSu.avatar" :src="getAvatarUrl(nhanSu.avatar)" alt="Avatar" class="avatar-photo" />
          <div v-else class="avatar-fallback">
            <!-- Realistic Avatar SVG matching Image 2 -->
            <svg width="68" height="68" viewBox="0 0 68 68" fill="none">
              <circle cx="34" cy="34" r="34" fill="#e0f2fe"/>
              <!-- Collar & shirt -->
              <path d="M16 64c0-10 8-18 18-18s18 8 18 18" fill="#3b82f6"/>
              <path d="M28 46l6 8 6-8-3-4h-6l-3 4z" fill="#ffffff"/>
              <!-- Neck -->
              <rect x="30" y="36" width="8" height="10" rx="3" fill="#fbd5b5"/>
              <!-- Face -->
              <ellipse cx="34" cy="28" rx="11" ry="13" fill="#fcd9bd"/>
              <!-- Hair -->
              <path d="M22 24c0-8 5-14 12-14s12 6 12 14c-3-2-6-2-12-1-4 1-8 0-12 1z" fill="#1e293b"/>
              <path d="M22 24c-1 3-1 6 0 8 1-2 1-4 1-5l-1-3z" fill="#1e293b"/>
              <path d="M46 24c1 3 1 6 0 8-1-2-1-4-1-5l1-3z" fill="#1e293b"/>
              <!-- Eyes & Smile -->
              <circle cx="30" cy="28" r="1.2" fill="#334155"/>
              <circle cx="38" cy="28" r="1.2" fill="#334155"/>
              <path d="M31 33c1.5 1.5 4.5 1.5 6 0" stroke="#334155" stroke-width="1.2" stroke-linecap="round"/>
            </svg>
          </div>
        </div>

        <div class="staff-profile-info">
          <div class="role-badge-hdv">
            <span>{{ nhanSu.vai_tro || 'HDV' }}</span>
          </div>
          <div class="staff-name-row">
            <h3 class="staff-title">{{ nhanSu.ho_ten }}</h3>
            <span class="badge-working-status">
              <span class="dot-green">●</span> Đang làm việc
            </span>
          </div>
          <div class="staff-contact-row">
            <span><i class="bi bi-telephone me-1"></i>{{ nhanSu.so_dien_thoai || '0955555555' }}</span>
            <span><i class="bi bi-envelope me-1"></i>{{ nhanSu.email || 'hdvtestfull@test.com' }}</span>
          </div>
          <div class="staff-id-row">
            <i class="bi bi-person-badge me-1"></i>Mã NS: #{{ nhanSu.nhan_su_id }}
          </div>
        </div>
      </div>

      <!-- KPI Card 1: Lương Cơ Bản -->
      <div class="kpi-card">
        <div class="kpi-icon-circle bg-blue-light text-blue">
          <i class="bi bi-wallet2"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">LƯƠNG CƠ BẢN</div>
          <div class="kpi-val">{{ formatCurrency(tongCoDinh) }}</div>
          <div class="kpi-sub">Lương cứng / chuyến, cố định</div>
        </div>
      </div>

      <!-- KPI Card 2: Hoa Hồng Tour -->
      <div class="kpi-card">
        <div class="kpi-icon-circle bg-amber-light text-amber">
          <i class="bi bi-shop"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">HOA HỒNG TOUR</div>
          <div class="kpi-val">{{ formatCurrency(tongHoaHong) }}</div>
          <div class="kpi-sub">Tổng tiền thưởng doanh thu</div>
        </div>
      </div>

      <!-- KPI Card 3: Tổng Thực Nhận -->
      <div class="kpi-card">
        <div class="kpi-icon-circle bg-emerald-light text-emerald">
          <i class="bi bi-clipboard2-check"></i>
        </div>
        <div class="kpi-content">
          <div class="kpi-label">TỔNG THỰC NHẬN</div>
          <div class="kpi-val">{{ formatCurrency(tongLuong) }}</div>
          <div class="kpi-sub">
            {{ showAll ? 'Toàn bộ thời gian' : `Tháng ${selectedMonth}/${selectedYear}` }}
          </div>
        </div>
      </div>
    </section>

    <!-- 3. ACTION TOOLBAR / SALARY SETTINGS BAR (MATCHING IMAGE 2) -->
    <section class="actions-strip" v-if="!showAll && selectedMonth && selectedYear">
      <!-- Base salary update form -->
      <div class="base-salary-form" v-if="nhanSu && nhanSu.vai_tro === 'HDV'">
        <form method="post" action="index.php?act=admin/capNhatLuongCoBan" class="d-flex align-items-center">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="redirect" :value="currentUrl">
          
          <label class="base-label">Lương cứng HDV</label>
          <div class="salary-input-wrapper">
            <input 
              type="number" 
              name="luong_co_ban" 
              min="0" 
              step="100000" 
              v-model="editBaseSalary" 
              class="input-salary-field"
            />
            <span class="currency-tag">đ</span>
          </div>
          <button type="submit" class="btn-save-salary">
            <i class="bi bi-floppy me-1"></i> Lưu
          </button>
        </form>
      </div>

      <!-- Workflow buttons on right -->
      <div class="workflow-buttons ms-auto">
        <!-- Recalculate -->
        <form method="post" action="index.php?act=admin/tinhLaiLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-wf btn-recalc">
            <i class="bi bi-arrow-repeat"></i>
            <span>Tính lại hoa hồng</span>
          </button>
        </form>

        <!-- Approve -->
        <form method="post" action="index.php?act=admin/duyetLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-wf btn-approve">
            <i class="bi bi-check2"></i>
            <span>Duyệt lương tháng</span>
          </button>
        </form>

        <!-- Mark as Paid -->
        <form method="post" action="index.php?act=admin/thanhToanLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-wf btn-paid">
            <i class="bi bi-check2-circle"></i>
            <span>Xác nhận đã thanh toán</span>
          </button>
        </form>
      </div>
    </section>

    <!-- 4. TOUR ALLOCATION TABLE CARD (MATCHING IMAGE 2) -->
    <section class="table-card">
      <div class="table-card-header">
        <div class="table-header-left">
          <div class="table-icon-circle">
            <i class="bi bi-receipt"></i>
          </div>
          <div class="table-header-text">
            <h3 class="card-title">Bảng Kê Chi Tiết Lương Theo Chuyến Tour</h3>
            <p class="card-subtitle">Chi tiết số tiền được định kỳ bảo hoa hồng và thực nhận từng lịch khởi hành.</p>
          </div>
        </div>
        <span class="badge-tour-count">{{ luongChiTiet.length }} chuyến đi</span>
      </div>

      <div class="table-responsive">
        <table class="detail-table">
          <thead>
            <tr>
              <th style="width: 70px;" class="text-center">#</th>
              <th>TÊN TOUR DU LỊCH</th>
              <th style="width: 130px;" class="text-center">NGÀY KH</th>
              <th style="width: 120px;" class="text-center">LOẠI LƯƠNG</th>
              <th style="width: 140px;" class="text-end">CỐ ĐỊNH</th>
              <th style="width: 100px;" class="text-center">% HOA HỒNG</th>
              <th style="width: 140px;" class="text-end">TIỀN HOA HỒNG</th>
              <th style="width: 150px;" class="text-end">TỔNG THU NHẬP</th>
              <th style="width: 130px;" class="text-center">TRẠNG THÁI</th>
              <th style="width: 100px;" class="text-center">THAO TÁC</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in luongChiTiet" :key="row.id || idx" class="detail-row">
              <td class="text-center">
                <span class="schedule-badge">#{{ row.lich_khoi_hanh_id }}</span>
              </td>
              <td>
                <strong class="tour-title">{{ row.ten_tour || 'Chưa cập nhật' }}</strong>
                <div v-if="row.ghi_chu" class="row-note">
                  <i class="bi bi-chat-left-text"></i> {{ row.ghi_chu }}
                </div>
              </td>
              <td class="text-center text-muted">
                {{ formatDate(row.ngay_khoi_hanh) }}
              </td>
              <td class="text-center">
                <span class="scheme-badge" :class="getSchemeBadgeClass(row.loai_luong)">
                  {{ formatScheme(row.loai_luong) }}
                </span>
              </td>
              <td class="text-end fw-semibold">
                {{ formatCurrency(row.so_tien_co_dinh) }}
              </td>
              <td class="text-center">
                <span class="percent-badge">{{ row.phan_tram_hoa_hong || 0 }}%</span>
              </td>
              <td class="text-end text-warning fw-semibold">
                {{ formatCurrency(row.tien_hoa_hong) }}
              </td>
              <td class="text-end">
                <span class="income-val">{{ formatCurrency(row.tong_luong) }}</span>
              </td>
              <td class="text-center">
                <span class="salary-status-badge" :class="getStatusBadgeClass(row.trang_thai_luong)">
                  <i class="bi" :class="getStatusIcon(row.trang_thai_luong)"></i>
                  {{ formatSalaryStatus(row.trang_thai_luong) }}
                </span>
              </td>
              <td class="text-center">
                <button 
                  class="btn-edit-row" 
                  @click="openEditModal(row)"
                  :disabled="row.trang_thai_luong === 'DaThanhToan'"
                  :title="row.trang_thai_luong === 'DaThanhToan' ? 'Đã thanh toán (không thể sửa)' : 'Chỉnh sửa định mức lương'"
                >
                  <i class="bi bi-pencil"></i>
                </button>
              </td>
            </tr>

            <!-- Empty Row with Graphic matching Image 2 -->
            <tr v-if="luongChiTiet.length === 0">
              <td colspan="10" class="empty-state-cell">
                <div class="empty-content">
                  <!-- Empty Blue Doc SVG -->
                  <div class="empty-graphic">
                    <svg width="68" height="68" viewBox="0 0 68 68" fill="none">
                      <rect x="14" y="8" width="40" height="52" rx="8" fill="#eff6ff" stroke="#bfdbfe" stroke-width="1.8"/>
                      <rect x="22" y="20" width="24" height="3" rx="1.5" fill="#93c5fd"/>
                      <rect x="22" y="28" width="20" height="3" rx="1.5" fill="#bfdbfe"/>
                      <rect x="22" y="36" width="16" height="3" rx="1.5" fill="#bfdbfe"/>
                      <!-- Magnifying glass -->
                      <circle cx="42" cy="46" r="10" fill="#3b82f6"/>
                      <circle cx="42" cy="46" r="6" stroke="#ffffff" stroke-width="1.8"/>
                      <line x1="47" y1="51" x2="53" y2="57" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                  </div>
                  <h4 class="empty-title">Chưa có dữ liệu</h4>
                  <p class="empty-subtitle">Hiện tại chưa có chuyến tour nào trong tháng này.</p>
                  <button @click="onPeriodChange" class="btn-empty-reload">
                    <i class="bi bi-arrow-clockwise me-1"></i> Tải lại
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- MODAL EDIT TOUR SALARY ROW -->
    <div v-if="editingRow" class="modal-backdrop" @click="closeEditModal">
      <div class="modal-card" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="bi bi-sliders me-2 text-primary"></i>Cập Nhật Lương Chuyến #{{ editingRow.lich_khoi_hanh_id }}
          </h5>
          <button class="btn-close-modal" @click="closeEditModal">&times;</button>
        </div>
        
        <form method="post" action="index.php?act=admin/capNhatLuongThuong">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="id" :value="editingRow.id">
          <input type="hidden" name="redirect" :value="currentUrl">

          <div class="modal-body">
            <div class="form-group mb-3">
              <label class="form-label">Loại lương:</label>
              <select name="loai_luong" v-model="editForm.loai_luong" class="form-control-custom">
                <option value="CoDinh">Cố định</option>
                <option value="PhanTram">Phần trăm</option>
                <option value="KetHop">Kết hợp</option>
              </select>
            </div>

            <div class="form-group mb-3">
              <label class="form-label">Số tiền cố định (VNĐ):</label>
              <input 
                type="number" 
                name="so_tien_co_dinh" 
                v-model="editForm.so_tien_co_dinh" 
                min="0" 
                step="10000"
                class="form-control-custom"
              />
            </div>

            <div class="form-group mb-3">
              <label class="form-label">% Hoa hồng:</label>
              <input 
                type="number" 
                name="phan_tram_hoa_hong" 
                v-model="editForm.phan_tram_hoa_hong" 
                min="0" 
                max="100" 
                step="0.1"
                class="form-control-custom"
              />
            </div>

            <div class="form-group mb-3">
              <label class="form-label">Trạng thái lương:</label>
              <select name="trang_thai_luong" v-model="editForm.trang_thai_luong" class="form-control-custom">
                <option value="ChoDuyet">Chờ duyệt</option>
                <option value="DaDuyet">Đã duyệt</option>
                <option value="DaThanhToan">Đã thanh toán</option>
              </select>
            </div>

            <div class="form-group mb-2">
              <label class="form-label">Ghi chú:</label>
              <input 
                type="text" 
                name="ghi_chu" 
                v-model="editForm.ghi_chu" 
                placeholder="Ghi chú thêm..." 
                class="form-control-custom"
              />
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeEditModal">Hủy</button>
            <button type="submit" class="btn-submit">Lưu thay đổi</button>
          </div>
        </form>
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
const nhanSu = ref(null);
const month = ref('');
const year = ref('');
const showAll = ref(false);
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());
const tongCoDinh = ref(0);
const tongHoaHong = ref(0);
const tongLuong = ref(0);
const luongChiTiet = ref([]);
const csrfToken = ref('');
const editBaseSalary = ref(0);

// Modal state
const editingRow = ref(null);
const editForm = ref({
  loai_luong: 'CoDinh',
  so_tien_co_dinh: 0,
  phan_tram_hoa_hong: 0,
  trang_thai_luong: 'ChoDuyet',
  ghi_chu: '',
});

const currentUrl = computed(() => {
  return window.location.href;
});

const availableYears = computed(() => {
  const cur = new Date().getFullYear();
  return [cur - 2, cur - 1, cur, cur + 1];
});

function applyData(data) {
  if (!data) return;
  nhanSu.value = data.nhanSu || null;
  month.value = data.month || '';
  year.value = data.year || '';
  showAll.value = !!data.showAll;
  if (data.month) selectedMonth.value = Number(data.month);
  if (data.year) selectedYear.value = Number(data.year);
  tongCoDinh.value = data.tongCoDinh || 0;
  tongHoaHong.value = data.tongHoaHong || 0;
  tongLuong.value = data.tongLuong || 0;
  luongChiTiet.value = data.luongChiTiet || [];
  csrfToken.value = data.csrfToken || '';
  if (data.nhanSu && data.nhanSu.luong_co_ban) {
    editBaseSalary.value = data.nhanSu.luong_co_ban;
  }
}

function onPeriodChange() {
  const url = new URL(window.location.href);
  url.searchParams.set('act', 'admin/chiTietLuong');
  url.searchParams.set('nhan_su_id', nhanSu.value.nhan_su_id);
  url.searchParams.set('month', selectedMonth.value);
  url.searchParams.set('year', selectedYear.value);
  url.searchParams.delete('all');
  window.location.href = url.toString();
}

function toggleShowAll() {
  const url = new URL(window.location.href);
  url.searchParams.set('act', 'admin/chiTietLuong');
  url.searchParams.set('nhan_su_id', nhanSu.value.nhan_su_id);
  if (showAll.value) {
    url.searchParams.delete('all');
    url.searchParams.set('month', selectedMonth.value);
    url.searchParams.set('year', selectedYear.value);
  } else {
    url.searchParams.set('all', '1');
    url.searchParams.delete('month');
    url.searchParams.delete('year');
  }
  window.location.href = url.toString();
}

function openEditModal(row) {
  editingRow.value = row;
  editForm.value = {
    loai_luong: row.loai_luong || 'CoDinh',
    so_tien_co_dinh: Number(row.so_tien_co_dinh) || 0,
    phan_tram_hoa_hong: Number(row.phan_tram_hoa_hong) || 0,
    trang_thai_luong: row.trang_thai_luong || 'ChoDuyet',
    ghi_chu: row.ghi_chu || '',
  };
}

function closeEditModal() {
  editingRow.value = null;
}

// Helpers
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatDate(dateStr) {
  if (!dateStr) return '--/--/----';
  const parts = dateStr.split(' ')[0].split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function formatScheme(scheme) {
  switch (scheme) {
    case 'CoDinh': return 'Cố định';
    case 'PhanTram': return 'Phần trăm';
    case 'KetHop': return 'Kết hợp';
    default: return scheme || 'Cố định';
  }
}

function getSchemeBadgeClass(scheme) {
  switch (scheme) {
    case 'CoDinh': return 'scheme-fixed';
    case 'PhanTram': return 'scheme-percent';
    case 'KetHop': return 'scheme-combo';
    default: return 'scheme-fixed';
  }
}

function formatSalaryStatus(status) {
  switch (status) {
    case 'ChoDuyet': return 'Chờ duyệt';
    case 'DaDuyet': return 'Đã duyệt';
    case 'DaThanhToan': return 'Đã thanh toán';
    default: return status || 'Chờ duyệt';
  }
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'ChoDuyet': return 'status-pending';
    case 'DaDuyet': return 'status-approved';
    case 'DaThanhToan': return 'status-paid';
    default: return 'status-pending';
  }
}

function getStatusIcon(status) {
  switch (status) {
    case 'ChoDuyet': return 'bi-clock-history';
    case 'DaDuyet': return 'bi-check-circle';
    case 'DaThanhToan': return 'bi-cash-coin';
    default: return 'bi-circle';
  }
}

function getAvatarUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return window.__BASE_URL__ ? window.__BASE_URL__ + path : path;
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  }
});
</script>

<style scoped>
.vue-admin-salary-detail {
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
  padding: 0.5rem 0 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #0f172a;
}

/* 1. TOP NAV BAR */
.top-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: transparent;
  padding: 0.25rem 0;
}

.top-nav-left {
  display: flex;
  align-items: center;
}

.btn-back-square {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
  text-decoration: none;
  font-size: 1.1rem;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
  transition: all 0.15s ease;
}

.btn-back-square:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #2563eb;
}

.page-nav-title {
  font-size: 1.25rem;
  font-weight: 800;
  margin: 0 0 0 1rem;
  color: #0f172a;
  letter-spacing: -0.01em;
}

.period-switcher {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.select-icon-box {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.icon-cal {
  position: absolute;
  left: 0.85rem;
  color: #64748b;
  font-size: 0.88rem;
  pointer-events: none;
}

.select-period-month {
  padding: 0.55rem 1.1rem 0.55rem 2.2rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.88rem;
  color: #0f172a;
  outline: none;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
  transition: border-color 0.2s;
}

.select-period-year {
  padding: 0.55rem 1.1rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.88rem;
  color: #0f172a;
  outline: none;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
  transition: border-color 0.2s;
}

.select-period-month:focus,
.select-period-year:focus {
  border-color: #2563eb;
}

.btn-toggle-all {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  color: #475569;
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  padding: 0.55rem 1.1rem;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
  transition: all 0.15s ease;
}

.btn-toggle-all:hover {
  background: #f8fafc;
  color: #0f172a;
}

.btn-toggle-all.active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}

/* 2. OVERVIEW GRID (STAFF + 3 KPI CARDS) */
.overview-grid {
  display: grid;
  grid-template-columns: 1.5fr 1fr 1fr 1fr;
  gap: 1.25rem;
}

@media (max-width: 1200px) {
  .overview-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .overview-grid {
    grid-template-columns: 1fr;
  }
}

.staff-profile-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem 1.6rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}

.staff-avatar-wrap {
  width: 68px;
  height: 68px;
  border-radius: 50%;
  overflow: hidden;
  border: 3px solid #eff6ff;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-photo {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-fallback {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.staff-profile-info {
  display: flex;
  flex-direction: column;
}

.role-badge-hdv {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
  font-size: 0.72rem;
  font-weight: 700;
  border-radius: 999px;
  padding: 0.15rem 0.65rem;
  text-transform: uppercase;
  width: fit-content;
  margin-bottom: 0.35rem;
}

.staff-name-row {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.staff-title {
  font-size: 1.35rem;
  font-weight: 800;
  margin: 0;
  color: #0f172a;
}

.badge-working-status {
  background: #ecfdf5;
  color: #10b981;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 999px;
  padding: 0.2rem 0.6rem;
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
}

.dot-green {
  color: #10b981;
  font-size: 0.8rem;
}

.staff-contact-row {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.82rem;
  color: #64748b;
  margin-top: 0.4rem;
}

.staff-id-row {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 0.2rem;
  font-weight: 600;
}

/* KPI Mini Cards */
.kpi-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.kpi-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}

.kpi-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}

.bg-blue-light { background: #eff6ff; }
.text-blue { color: #2563eb; }

.bg-amber-light { background: #fffbeb; }
.text-amber { color: #f59e0b; }

.bg-emerald-light { background: #ecfdf5; }
.text-emerald { color: #10b981; }

.kpi-content {
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 0.76rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.kpi-val {
  font-size: 1.55rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
  margin: 0.2rem 0;
}

.kpi-sub {
  font-size: 0.76rem;
  color: #94a3b8;
}

/* 3. ACTIONS STRIP */
.actions-strip {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  flex-wrap: wrap;
  gap: 1rem;
}

.base-salary-form form {
  gap: 0.85rem;
}

.base-label {
  font-size: 0.9rem;
  font-weight: 700;
  color: #0f172a;
  white-space: nowrap;
}

.salary-input-wrapper {
  position: relative;
  display: inline-flex;
  align-items: center;
}

.input-salary-field {
  width: 170px;
  padding: 0.6rem 2rem 0.6rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.88rem;
  color: #0f172a;
  outline: none;
  text-align: right;
  font-weight: 600;
}

.input-salary-field:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.currency-tag {
  position: absolute;
  right: 0.75rem;
  color: #64748b;
  font-size: 0.85rem;
  pointer-events: none;
}

.btn-save-salary {
  background: #2563eb;
  color: #ffffff;
  border: none;
  padding: 0.6rem 1.25rem;
  border-radius: 8px;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
  transition: all 0.15s ease;
  white-space: nowrap;
}

.btn-save-salary:hover {
  background: #1d4ed8;
}

.workflow-buttons {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.btn-wf {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  white-space: nowrap;
  border: none;
}

.btn-recalc {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #334155;
}

.btn-recalc:hover {
  background: #f1f5f9;
  border-color: #94a3b8;
  color: #0f172a;
}

.btn-approve {
  background: #f59e0b;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
}

.btn-approve:hover {
  background: #d97706;
}

.btn-paid {
  background: #10b981;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.25);
}

.btn-paid:hover {
  background: #059669;
}

/* 4. TABLE CARD */
.table-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  overflow: hidden;
}

.table-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.2rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.table-header-left {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.table-icon-circle {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  flex-shrink: 0;
}

.card-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.card-subtitle {
  font-size: 0.82rem;
  color: #64748b;
  margin: 0.2rem 0 0;
}

.badge-tour-count {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
  font-weight: 700;
  font-size: 0.78rem;
  border-radius: 999px;
  padding: 0.3rem 0.85rem;
}

.table-responsive {
  overflow-x: auto;
}

.detail-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}

.detail-table th {
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

.detail-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #334155;
  background: #ffffff;
}

.detail-row:hover td {
  background: #f8fafc;
}

.schedule-badge {
  color: #64748b;
  font-weight: 600;
  font-size: 0.82rem;
}

.tour-title {
  color: #0f172a;
  font-size: 0.92rem;
}

.row-note {
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 0.2rem;
}

.scheme-badge {
  display: inline-block;
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
}

.scheme-fixed { background: #eff6ff; color: #2563eb; }
.scheme-percent { background: #fffbeb; color: #d97706; }
.scheme-combo { background: #f5f3ff; color: #7c3aed; }

.percent-badge {
  color: #475569;
  font-weight: 600;
}

.income-val {
  font-weight: 700;
  color: #10b981;
  font-size: 0.95rem;
}

.salary-status-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
}

.status-pending { background: #fef3c7; color: #d97706; }
.status-approved { background: #dcfce7; color: #15803d; }
.status-paid { background: #eff6ff; color: #2563eb; }

.btn-edit-row {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-edit-row:hover:not(:disabled) {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #cbd5e1;
}

.btn-edit-row:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Empty State */
.empty-state-cell {
  padding: 3.5rem 1rem !important;
  text-align: center;
}

.empty-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.empty-graphic {
  margin-bottom: 0.75rem;
}

.empty-title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.35rem;
}

.empty-subtitle {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 1.25rem;
}

.btn-empty-reload {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.45rem 1.25rem;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-empty-reload:hover {
  background: #dbeafe;
}

/* Modal */
.modal-backdrop {
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
  max-width: 480px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 20px 40px rgba(15, 23, 42, 0.2);
  overflow: hidden;
  color: #0f172a;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.modal-title {
  font-size: 1.1rem;
  font-weight: 700;
  margin: 0;
  color: #0f172a;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 1.4rem;
  color: #94a3b8;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
}

.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 0.35rem;
}

.form-control-custom {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
  outline: none;
}

.form-control-custom:focus {
  border-color: #2563eb;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1.15rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: #f8fafc;
}

.btn-cancel {
  background: #ffffff;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

.btn-submit {
  background: #2563eb;
  color: #ffffff;
  border: none;
  padding: 0.6rem 1.4rem;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
}
</style>
