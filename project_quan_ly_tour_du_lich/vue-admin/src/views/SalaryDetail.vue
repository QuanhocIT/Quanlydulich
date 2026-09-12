<template>
  <div class="vue-admin-salary-detail">
    <!-- TOP NAVIGATION & HEADER -->
    <div class="top-nav-bar">
      <a href="index.php?act=admin/quanLyLuongThuong" class="btn-back">
        <i class="bi bi-arrow-left"></i>
        <span>Quản lý lương thưởng</span>
      </a>

      <div class="period-switcher">
        <label class="period-label"><i class="bi bi-calendar-event me-1"></i>Kỳ lương:</label>
        <select v-model="selectedMonth" @change="onPeriodChange" class="select-period" :disabled="showAll">
          <option v-for="m in 12" :key="m" :value="m">Tháng {{ m }}</option>
        </select>
        <select v-model="selectedYear" @change="onPeriodChange" class="select-period" :disabled="showAll">
          <option v-for="y in availableYears" :key="y" :value="y">Năm {{ y }}</option>
        </select>
        <button 
          class="btn-toggle-all" 
          :class="{ active: showAll }"
          @click="toggleShowAll"
        >
          {{ showAll ? 'Xem theo tháng' : 'Xem tất cả' }}
        </button>
      </div>
    </div>

    <!-- EMPLOYEE INFO & KPI SUMMARY -->
    <header class="staff-header-card" v-if="nhanSu">
      <div class="staff-info-col">
        <div class="avatar-wrap">
          <i class="bi bi-person-badge"></i>
        </div>
        <div class="staff-details">
          <div class="role-badge" :class="nhanSu.vai_tro === 'HDV' ? 'role-hdv' : 'role-staff'">
            <i class="bi" :class="nhanSu.vai_tro === 'HDV' ? 'bi-compass' : 'bi-person'"></i>
            <span>{{ nhanSu.vai_tro || 'Nhân sự' }}</span>
          </div>
          <h2 class="staff-name">{{ nhanSu.ho_ten }}</h2>
          <div class="staff-subinfo">
            <span v-if="nhanSu.so_dien_thoai"><i class="bi bi-telephone"></i> {{ nhanSu.so_dien_thoai }}</span>
            <span v-if="nhanSu.email"><i class="bi bi-envelope"></i> {{ nhanSu.email }}</span>
            <span><i class="bi bi-hash"></i> Mã NS: #{{ nhanSu.nhan_su_id }}</span>
          </div>
        </div>
      </div>

      <!-- 3 SUMMARY STATS -->
      <div class="salary-kpi-group">
        <div class="kpi-mini-card">
          <span class="kpi-mini-label">Lương Cố Định</span>
          <strong class="kpi-mini-val text-primary">{{ formatCurrency(tongCoDinh) }}</strong>
          <span class="kpi-mini-sub">Lương cứng / chuyến cố định</span>
        </div>

        <div class="kpi-mini-card">
          <span class="kpi-mini-label">Hoa Hồng Tour</span>
          <strong class="kpi-mini-val text-warning">{{ formatCurrency(tongHoaHong) }}</strong>
          <span class="kpi-mini-sub">Tổng tiền thưởng doanh thu</span>
        </div>

        <div class="kpi-mini-card highlight-card">
          <span class="kpi-mini-label">Tổng Thực Nhận</span>
          <strong class="kpi-mini-val text-success">{{ formatCurrency(tongLuong) }}</strong>
          <span class="kpi-mini-sub">
            {{ showAll ? 'Toàn bộ thời gian' : `Kỳ Tháng ${selectedMonth}/${selectedYear}` }}
          </span>
        </div>
      </div>
    </header>

    <!-- ACTION TOOLBAR -->
    <section class="actions-strip" v-if="!showAll && selectedMonth && selectedYear">
      <!-- HDV Base salary update form -->
      <div class="base-salary-form" v-if="nhanSu && nhanSu.vai_tro === 'HDV'">
        <form method="post" action="index.php?act=admin/capNhatLuongCoBan" class="d-flex align-items-center gap-2">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="redirect" :value="currentUrl">
          <label class="base-label"><i class="bi bi-cash-stack me-1"></i>Lương cứng HDV:</label>
          <input 
            type="number" 
            name="luong_co_ban" 
            min="0" 
            step="100000" 
            v-model="editBaseSalary" 
            class="input-base-salary"
          />
          <button type="submit" class="btn-action-sm btn-save-base">
            <i class="bi bi-check2"></i> Lưu
          </button>
        </form>
      </div>

      <!-- Status workflow buttons -->
      <div class="workflow-buttons ms-auto">
        <!-- Recalculate -->
        <form method="post" action="index.php?act=admin/tinhLaiLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-action-sm btn-recalc">
            <i class="bi bi-arrow-repeat"></i> Tính lại hoa hồng
          </button>
        </form>

        <!-- Approve -->
        <form method="post" action="index.php?act=admin/duyetLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-action-sm btn-approve">
            <i class="bi bi-check2-circle"></i> Duyệt lương tháng
          </button>
        </form>

        <!-- Mark as Paid -->
        <form method="post" action="index.php?act=admin/thanhToanLuongNhanSu" class="d-inline">
          <input type="hidden" name="_csrf_global" :value="csrfToken">
          <input type="hidden" name="nhan_su_id" :value="nhanSu.nhan_su_id">
          <input type="hidden" name="month" :value="selectedMonth">
          <input type="hidden" name="year" :value="selectedYear">
          <input type="hidden" name="redirect" :value="currentUrl">
          <button type="submit" class="btn-action-sm btn-paid">
            <i class="bi bi-cash-coin"></i> Xác nhận đã thanh toán
          </button>
        </form>
      </div>
    </section>

    <!-- TOUR ALLOCATION TABLE -->
    <section class="table-card">
      <div class="table-card-header">
        <div>
          <h3 class="card-title">Bảng Kê Chi Tiết Lương Theo Chuyến Tour</h3>
          <p class="card-subtitle">Chi tiết số tiền cố định, tỷ lệ hoa hồng và thực nhận từng lịch khởi hành</p>
        </div>
        <span class="badge-count">{{ luongChiTiet.length }} chuyến đi</span>
      </div>

      <div class="table-responsive">
        <table class="detail-table">
          <thead>
            <tr>
              <th style="width: 80px;" class="text-center">#Lịch</th>
              <th>Tên Tour Du Lịch</th>
              <th style="width: 130px;" class="text-center">Ngày KH</th>
              <th style="width: 120px;" class="text-center">Loại Lương</th>
              <th style="width: 140px;" class="text-end">Cố Định</th>
              <th style="width: 90px;" class="text-center">% Hoa Hồng</th>
              <th style="width: 140px;" class="text-end">Tiền Hoa Hồng</th>
              <th style="width: 150px;" class="text-end">Tổng Thu Nhập</th>
              <th style="width: 130px;" class="text-center">Trạng Thái</th>
              <th style="width: 100px;" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in luongChiTiet" :key="row.id" class="detail-row">
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

            <!-- Empty Row -->
            <tr v-if="luongChiTiet.length === 0">
              <td colspan="10" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không có dữ liệu phân bổ lương cho nhân sự trong kỳ này.
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
            <i class="bi bi-sliders me-2"></i>Cập Nhật Lương Chuyến #{{ editingRow.lich_khoi_hanh_id }}
          </h5>
          <button class="btn-close" @click="closeEditModal">&times;</button>
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

// Formatters
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatDate(dateStr) {
  if (!dateStr) return '--/--/----';
  const parts = dateStr.split('-');
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
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* TOP NAV */
.top-nav-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}
.btn-back {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: rgba(22, 31, 46, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  padding: 0.55rem 1.15rem;
  border-radius: 0.65rem;
  font-size: 0.9rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
  backdrop-filter: blur(8px);
}
.btn-back:hover {
  background: rgba(212, 175, 55, 0.15);
  color: #d4af37;
  border-color: rgba(212, 175, 55, 0.35);
}
.period-switcher {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  background: rgba(22, 31, 46, 0.75);
  padding: 0.45rem 0.85rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(8px);
}
.period-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #94a3b8;
}
.select-period {
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.5rem;
  padding: 0.4rem 0.75rem;
  font-size: 0.85rem;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
}
.btn-toggle-all {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  font-size: 0.82rem;
  font-weight: 600;
  padding: 0.4rem 0.85rem;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-toggle-all:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}
.btn-toggle-all.active {
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  font-weight: 700;
  border-color: #d4af37;
}

/* STAFF HEADER CARD */
.staff-header-card {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
  padding: 1.75rem 2rem;
  border-radius: 1.25rem;
  color: #ffffff;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(14px);
  gap: 2rem;
  flex-wrap: wrap;
}
.staff-info-col {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}
.avatar-wrap {
  width: 68px;
  height: 68px;
  border-radius: 1rem;
  background: rgba(212, 175, 55, 0.15);
  border: 1px solid rgba(212, 175, 55, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  color: #d4af37;
}
.staff-details {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.role-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.2rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  width: fit-content;
}
.role-hdv { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.3); }
.role-staff { background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border: 1px solid rgba(148, 163, 184, 0.25); }

.staff-name {
  font-size: 1.65rem;
  font-weight: 800;
  margin: 0;
  color: #ffffff;
}
.staff-subinfo {
  display: flex;
  gap: 1rem;
  font-size: 0.85rem;
  color: #94a3b8;
  flex-wrap: wrap;
}
.staff-subinfo i { color: #d4af37; }

/* SALARY KPI GROUP */
.salary-kpi-group {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}
.kpi-mini-card {
  background: rgba(11, 17, 32, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 1rem 1.35rem;
  border-radius: 0.85rem;
  display: flex;
  flex-direction: column;
  min-width: 170px;
}
.highlight-card {
  background: rgba(16, 185, 129, 0.12);
  border-color: rgba(16, 185, 129, 0.3);
}
.kpi-mini-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #94a3b8;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}
.kpi-mini-val {
  font-size: 1.4rem;
  font-weight: 800;
  margin: 0.25rem 0;
  color: #ffffff;
}
.kpi-mini-val.text-primary { color: #38bdf8 !important; }
.kpi-mini-val.text-warning { color: #fde047 !important; }
.kpi-mini-val.text-success { color: #34d399 !important; }
.kpi-mini-sub {
  font-size: 0.75rem;
  color: #64748b;
}

/* ACTIONS STRIP */
.actions-strip {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(22, 31, 46, 0.75);
  padding: 1.15rem 1.5rem;
  border-radius: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(12px);
  flex-wrap: wrap;
  gap: 1rem;
}
.base-salary-form {
  display: flex;
  align-items: center;
}
.base-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #cbd5e1;
  margin: 0;
}
.input-base-salary {
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.55rem;
  padding: 0.45rem 0.85rem;
  font-size: 0.9rem;
  width: 140px;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
}
.input-base-salary:focus {
  border-color: #d4af37;
  box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
}
.btn-action-sm {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.55rem 0.95rem;
  border-radius: 0.55rem;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-save-base {
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
}
.btn-save-base:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3); }

.btn-recalc {
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  border: 1px solid rgba(255, 255, 255, 0.12);
}
.btn-recalc:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }

.btn-approve {
  background: rgba(245, 158, 11, 0.18);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.35);
}
.btn-approve:hover { background: rgba(245, 158, 11, 0.3); }

.btn-paid {
  background: rgba(16, 185, 129, 0.18);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.35);
}
.btn-paid:hover { background: rgba(16, 185, 129, 0.3); }

.workflow-buttons {
  display: flex;
  gap: 0.6rem;
  flex-wrap: wrap;
}

/* TABLE CARD */
.table-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  overflow: hidden;
}
.table-card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(11, 17, 32, 0.6);
}
.card-title { font-size: 1.15rem; font-weight: 800; color: #ffffff; margin: 0; }
.card-subtitle { font-size: 0.85rem; color: #94a3b8; margin: 0.2rem 0 0; }
.badge-count {
  background: rgba(212, 175, 55, 0.15);
  color: #d4af37;
  border: 1px solid rgba(212, 175, 55, 0.25);
  font-weight: 700;
  font-size: 0.8rem;
  padding: 0.35rem 0.75rem;
  border-radius: 2rem;
}

.table-responsive { overflow-x: auto; }
.detail-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}
.detail-table th {
  padding: 0.95rem 1.15rem;
  background: rgba(11, 17, 32, 0.85);
  color: #94a3b8;
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}
.detail-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.detail-row:hover td { background: rgba(255, 255, 255, 0.02); }

.schedule-badge {
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  font-size: 0.8rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}
.tour-title { color: #ffffff; font-size: 0.92rem; }
.row-note { font-size: 0.78rem; color: #64748b; margin-top: 0.2rem; }

.scheme-badge {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 0.35rem;
  font-size: 0.78rem;
  font-weight: 700;
}
.scheme-fixed { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }
.scheme-percent { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.scheme-combo { background: rgba(168, 85, 247, 0.15); color: #c084fc; }

.percent-badge {
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  font-weight: 700;
  padding: 0.2rem 0.45rem;
  border-radius: 0.3rem;
  font-size: 0.8rem;
}
.income-val {
  font-size: 0.98rem;
  font-weight: 800;
  color: #34d399;
}

.salary-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 700;
}
.status-pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); }
.status-approved { background: rgba(56, 189, 248, 0.15); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.25); }
.status-paid { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }

.btn-edit-row {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  width: 32px;
  height: 32px;
  border-radius: 0.45rem;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-edit-row:hover:not(:disabled) { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
.btn-edit-row:disabled { opacity: 0.3; cursor: not-allowed; }

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }

/* MODAL */
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.modal-card {
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 1.15rem;
  width: 90%;
  max-width: 440px;
  box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  color: #f8fafc;
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(11, 17, 32, 0.85);
}
.modal-title { font-size: 1.1rem; font-weight: 800; color: #ffffff; margin: 0; }
.btn-close { background: transparent; border: none; font-size: 1.5rem; color: #94a3b8; cursor: pointer; }
.btn-close:hover { color: #ffffff; }
.modal-body { padding: 1.5rem; }
.form-label { font-size: 0.85rem; font-weight: 700; color: #cbd5e1; margin-bottom: 0.35rem; display: block; }
.form-control-custom {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.55rem;
  font-size: 0.9rem;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
}
.form-control-custom:focus {
  border-color: #d4af37;
  box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.2);
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(11, 17, 32, 0.85);
}
.btn-cancel {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  padding: 0.55rem 1rem;
  border-radius: 0.55rem;
  font-weight: 600;
  cursor: pointer;
}
.btn-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
.btn-submit {
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  border: none;
  color: #0b1120;
  padding: 0.55rem 1.25rem;
  border-radius: 0.55rem;
  font-weight: 700;
  cursor: pointer;
}
.btn-submit:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(212, 175, 55, 0.35); }
</style>
