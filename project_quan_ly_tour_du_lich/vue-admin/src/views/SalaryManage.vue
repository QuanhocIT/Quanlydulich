<template>
  <div class="vue-admin-salary-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-wallet2"></i>
          <span>Tài Chính & Nhân Sự</span>
        </div>
        <h1 class="header-title">Quản Lý Lương Thưởng Nhân Sự</h1>
        <p class="header-subtitle">
          Theo dõi định mức lương cứng, hoa hồng dẫn tour và quy trình duyệt thanh toán
        </p>
      </div>

      <div class="header-actions">
        <button class="btn-create" @click="showCreateForm = !showCreateForm">
          <i class="bi" :class="showCreateForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
          <span>{{ showCreateForm ? 'Đóng form' : 'Tạo Lương/Thưởng' }}</span>
        </button>
      </div>
    </header>

    <!-- SUMMARY KPI STATS -->
    <section class="stats-row">
      <div class="stat-pill stat-total">
        <div class="stat-icon"><i class="bi bi-cash-stack"></i></div>
        <div class="stat-info">
          <span class="stat-label">Tổng Quỹ Lương</span>
          <strong class="stat-val text-success">{{ formatCurrency(totalPayroll) }}</strong>
        </div>
      </div>

      <div class="stat-pill stat-fixed">
        <div class="stat-icon"><i class="bi bi-bank"></i></div>
        <div class="stat-info">
          <span class="stat-label">Lương Cố Định</span>
          <strong class="stat-val text-primary">{{ formatCurrency(totalFixed) }}</strong>
        </div>
      </div>

      <div class="stat-pill stat-commission">
        <div class="stat-icon"><i class="bi bi-award"></i></div>
        <div class="stat-info">
          <span class="stat-label">Tổng Hoa Hồng</span>
          <strong class="stat-val text-warning">{{ formatCurrency(totalCommission) }}</strong>
        </div>
      </div>

      <div class="stat-pill stat-records">
        <div class="stat-icon"><i class="bi bi-people"></i></div>
        <div class="stat-info">
          <span class="stat-label">Bản Ghi Lương</span>
          <strong class="stat-val">{{ filteredList.length }} mục</strong>
        </div>
      </div>
    </section>

    <!-- COLLAPSIBLE FORM: TẠO LƯƠNG/THƯỞNG MỚI -->
    <transition name="slide">
      <section v-if="showCreateForm" class="create-card">
        <div class="create-card-header">
          <h4 class="m-0"><i class="bi bi-plus-circle me-2"></i>Thêm Định Mức Lương/Thưởng Tour</h4>
          <span class="sub-text">Thiết lập mức thù lao cố định hoặc % hoa hồng cho nhân sự dẫn tour</span>
        </div>

        <form method="post" action="index.php?act=admin/taoLuongThuong" class="create-form">
          <input type="hidden" name="_csrf_global" :value="csrfToken">

          <div class="form-grid">
            <div class="form-col">
              <label class="form-label">Nhân sự <span class="text-danger">*</span></label>
              <select name="nhan_su_id" required class="form-select-custom">
                <option value="">-- Chọn nhân sự --</option>
                <option v-for="ns in nhanSuList" :key="ns.nhan_su_id" :value="ns.nhan_su_id">
                  {{ ns.ho_ten }} ({{ ns.vai_tro || 'Nhân sự' }})
                </option>
              </select>
            </div>

            <div class="form-col">
              <label class="form-label">Lịch khởi hành <span class="text-danger">*</span></label>
              <select name="lich_khoi_hanh_id" required class="form-select-custom">
                <option value="">-- Chọn lịch tour --</option>
                <option v-for="lk in lichKhoiHanhList" :key="lk.id" :value="lk.id">
                  #{{ lk.id }} - {{ lk.ten_tour }} ({{ formatDate(lk.ngay_khoi_hanh) }})
                </option>
              </select>
            </div>

            <div class="form-col">
              <label class="form-label">Loại lương</label>
              <select name="loai_luong" class="form-select-custom">
                <option value="CoDinh">Cố định</option>
                <option value="PhanTram">Phần trăm hoa hồng</option>
                <option value="KetHop">Kết hợp (Cố định + %)</option>
              </select>
            </div>

            <div class="form-col">
              <label class="form-label">Lương cố định (VNĐ)</label>
              <input type="number" name="so_tien_co_dinh" min="0" value="0" step="10000" class="form-control-custom">
            </div>

            <div class="form-col">
              <label class="form-label">% Hoa hồng doanh thu</label>
              <input type="number" name="phan_tram_hoa_hong" min="0" max="100" step="0.1" value="0" class="form-control-custom">
            </div>

            <div class="form-col">
              <label class="form-label">Ghi chú</label>
              <input type="text" name="ghi_chu" maxlength="255" placeholder="Ghi chú thêm..." class="form-control-custom">
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn-submit-salary">
              <i class="bi bi-check-lg me-1"></i> Xác Nhận Tạo Lương
            </button>
            <button type="button" @click="showCreateForm = false" class="btn-cancel">Hủy</button>
          </div>
        </form>
      </section>
    </transition>

    <!-- FILTER TOOLBAR -->
    <section class="filter-card">
      <div class="filter-grid">
        <!-- Search Name -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-search me-1"></i>Tìm nhanh nhân sự</label>
          <input 
            type="text" 
            v-model="searchKeyword" 
            placeholder="Nhập tên nhân sự..." 
            class="form-control-custom"
          />
        </div>

        <!-- Filter Staff Dropdown -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-person me-1"></i>Nhân sự</label>
          <select v-model="filterNhanSu" class="form-select-custom">
            <option value="">Tất cả nhân sự</option>
            <option v-for="ns in nhanSuList" :key="ns.nhan_su_id" :value="ns.nhan_su_id">
              {{ ns.ho_ten }}
            </option>
          </select>
        </div>

        <!-- Filter Tour Dropdown -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-compass me-1"></i>Tour</label>
          <select v-model="filterTour" class="form-select-custom">
            <option value="">Tất cả tour</option>
            <option v-for="t in tourList" :key="t.tour_id" :value="t.tour_id">
              {{ t.ten_tour }}
            </option>
          </select>
        </div>

        <!-- Filter Status -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-shield-check me-1"></i>Trạng thái</label>
          <select v-model="filterTrangThai" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="ChoDuyet">Chờ duyệt</option>
            <option value="DaDuyet">Đã duyệt</option>
            <option value="DaThanhToan">Đã thanh toán</option>
          </select>
        </div>

        <!-- Period Selectors -->
        <div class="filter-col period-col">
          <label class="filter-label"><i class="bi bi-calendar me-1"></i>Kỳ lương</label>
          <div class="d-flex gap-2">
            <select v-model="filterMonth" :disabled="showAll" class="form-select-custom">
              <option value="">Tháng --</option>
              <option v-for="m in 12" :key="m" :value="String(m)">Tháng {{ m }}</option>
            </select>
            <input 
              type="number" 
              v-model="filterYear" 
              :disabled="showAll" 
              min="2020" 
              max="2030" 
              class="form-control-custom" 
              style="width: 90px;"
            />
          </div>
        </div>

        <!-- Reset Button -->
        <div class="filter-col btn-col">
          <button @click="resetFilters" class="btn-reset" title="Khôi phục bộ lọc">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- DATA TABLE -->
    <section class="table-card">
      <div class="table-responsive">
        <table class="salary-table">
          <thead>
            <tr>
              <th>Nhân Sự</th>
              <th>Vai Trò</th>
              <th>Tour / Lịch Khởi Hành</th>
              <th class="text-center">Ngày Khởi Hành</th>
              <th class="text-end">Cố Định</th>
              <th class="text-end">Hoa Hồng</th>
              <th class="text-end">Tổng Lương</th>
              <th class="text-center">Trạng Thái</th>
              <th class="text-center">Chi Tiết</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in filteredList" :key="idx" class="salary-row">
              <!-- Staff -->
              <td>
                <strong class="staff-name">{{ row.ho_ten }}</strong>
                <div class="staff-id-tag">#{{ row.nhan_su_id }}</div>
              </td>

              <!-- Role -->
              <td>
                <span class="role-pill" :class="row.vai_tro === 'HDV' ? 'pill-hdv' : 'pill-other'">
                  {{ row.vai_tro || 'Nhân sự' }}
                </span>
              </td>

              <!-- Tour -->
              <td class="tour-name-cell">
                <div class="tour-text">{{ row.ten_tour || 'N/A' }}</div>
                <small v-if="row.lich_khoi_hanh_id" class="text-muted">Lịch #{{ row.lich_khoi_hanh_id }}</small>
              </td>

              <!-- Date -->
              <td class="text-center text-muted">
                {{ formatDate(row.ngay_khoi_hanh) }}
              </td>

              <!-- Fixed Amount -->
              <td class="text-end fw-semibold">
                {{ formatCurrency(row.tong_co_dinh || row.so_tien_co_dinh) }}
              </td>

              <!-- Commission Amount -->
              <td class="text-end text-warning fw-semibold">
                {{ formatCurrency(row.tong_hoa_hong || row.tien_hoa_hong) }}
              </td>

              <!-- Total Salary -->
              <td class="text-end">
                <span class="total-salary-badge">
                  {{ formatCurrency(row.tong_luong) }}
                </span>
              </td>

              <!-- Status -->
              <td class="text-center">
                <span class="status-pill" :class="getStatusClass(row.trang_thai_luong)">
                  {{ formatStatus(row.trang_thai_luong) }}
                </span>
              </td>

              <!-- Action Link -->
              <td class="text-center">
                <a 
                  :href="'index.php?act=admin/chiTietLuong&nhan_su_id=' + row.nhan_su_id + (filterMonth ? '&month=' + filterMonth : '') + (filterYear ? '&year=' + filterYear : '')" 
                  class="btn-detail"
                  title="Xem bảng chi tiết lương & thao tác duyệt"
                >
                  <i class="bi bi-eye"></i> Chi tiết
                </a>
              </td>
            </tr>

            <!-- Empty row -->
            <tr v-if="filteredList.length === 0">
              <td colspan="9" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy dữ liệu lương phù hợp với bộ lọc.
              </td>
            </tr>
          </tbody>
        </table>
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
const showCreateForm = ref(false);
const searchKeyword = ref('');
const filterNhanSu = ref('');
const filterTour = ref('');
const filterTrangThai = ref('');
const filterMonth = ref(String(new Date().getMonth() + 1));
const filterYear = ref(String(new Date().getFullYear()));
const showAll = ref(false);

const allLuongTongHop = ref([]);
const nhanSuList = ref([]);
const tourList = ref([]);
const lichKhoiHanhList = ref([]);
const csrfToken = ref('');

// Computed Totals
const filteredList = computed(() => {
  let list = [...allLuongTongHop.value];

  if (searchKeyword.value.trim()) {
    const q = searchKeyword.value.toLowerCase().trim();
    list = list.filter(r => (r.ho_ten || '').toLowerCase().includes(q));
  }

  if (filterNhanSu.value) {
    list = list.filter(r => String(r.nhan_su_id) === String(filterNhanSu.value));
  }

  if (filterTour.value) {
    list = list.filter(r => String(r.tour_id) === String(filterTour.value));
  }

  if (filterTrangThai.value) {
    list = list.filter(r => r.trang_thai_luong === filterTrangThai.value);
  }

  return list;
});

const totalPayroll = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_luong) || 0), 0);
});

const totalFixed = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_co_dinh || r.so_tien_co_dinh) || 0), 0);
});

const totalCommission = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_hoa_hong || r.tien_hoa_hong) || 0), 0);
});

function applyData(data) {
  if (!data) return;
  allLuongTongHop.value = data.allLuongTongHop || [];
  nhanSuList.value = data.nhanSuList || [];
  tourList.value = data.tourList || [];
  lichKhoiHanhList.value = data.lichKhoiHanhList || [];
  csrfToken.value = data.csrfToken || '';
  if (data.filterMonth) filterMonth.value = String(data.filterMonth);
  if (data.filterYear) filterYear.value = String(data.filterYear);
  if (data.filterNhanSu) filterNhanSu.value = String(data.filterNhanSu);
  if (data.filterTour) filterTour.value = String(data.filterTour);
  if (data.filterTrangThaiLuong) filterTrangThai.value = String(data.filterTrangThaiLuong);
  showAll.value = !!data.showAll;
}

function resetFilters() {
  searchKeyword.value = '';
  filterNhanSu.value = '';
  filterTour.value = '';
  filterTrangThai.value = '';
}

// Formatters
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatDate(str) {
  if (!str) return '--/--/----';
  const parts = str.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return str;
}

function formatStatus(status) {
  switch (status) {
    case 'ChoDuyet': return 'Chờ duyệt';
    case 'DaDuyet': return 'Đã duyệt';
    case 'DaThanhToan': return 'Đã thanh toán';
    default: return status || 'Chờ duyệt';
  }
}

function getStatusClass(status) {
  switch (status) {
    case 'ChoDuyet': return 'status-pending';
    case 'DaDuyet': return 'status-approved';
    case 'DaThanhToan': return 'status-paid';
    default: return 'status-pending';
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  }
});
</script>

<style scoped>
.vue-admin-salary-manage {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* PAGE HEADER */
.page-header {
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
}
.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  background: rgba(212, 175, 55, 0.15);
  color: #fde047;
  border: 1px solid rgba(212, 175, 55, 0.3);
  padding: 0.3rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.5rem;
}
.header-title {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  color: #ffffff;
}
.header-subtitle {
  font-size: 0.95rem;
  color: #94a3b8;
  margin: 0.25rem 0 0;
}
.btn-create {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  padding: 0.75rem 1.4rem;
  border-radius: 0.65rem;
  font-weight: 700;
  font-size: 0.95rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s;
}
.btn-create:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.45);
  color: #0b1120;
}

/* STATS ROW */
.stats-row {
  display: flex;
  gap: 1.25rem;
  flex-wrap: wrap;
}
.stat-pill {
  flex: 1;
  min-width: 200px;
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.15rem 1.35rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.stat-pill:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(255, 255, 255, 0.15);
}
.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
}
.stat-total .stat-icon { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.stat-fixed .stat-icon { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.stat-commission .stat-icon { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }
.stat-records .stat-icon { background: rgba(139, 92, 246, 0.16); color: #a78bfa; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-val { font-size: 1.45rem; font-weight: 800; color: #ffffff; line-height: 1.2; }
.stat-val.text-success { color: #34d399 !important; }
.stat-val.text-primary { color: #38bdf8 !important; }
.stat-val.text-warning { color: #fbbf24 !important; }

/* CREATE CARD */
.create-card {
  background: rgba(22, 31, 46, 0.92);
  border-radius: 1.15rem;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.35);
  backdrop-filter: blur(16px);
  color: #ffffff;
}
.create-card-header {
  margin-bottom: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding-bottom: 0.75rem;
}
.create-card-header h4 {
  color: #d4af37;
  font-weight: 700;
}
.sub-text { font-size: 0.85rem; color: #94a3b8; }
.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}
.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 700;
  color: #cbd5e1;
  margin-bottom: 0.35rem;
}
.form-control-custom, .form-select-custom {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.65rem;
  font-size: 0.88rem;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
  outline: none;
  transition: all 0.2s;
}
.form-control-custom:focus, .form-select-custom:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}
.form-control-custom::placeholder {
  color: #64748b;
}
.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
}
.btn-submit-salary {
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  padding: 0.65rem 1.35rem;
  border-radius: 0.55rem;
  border: none;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s;
}
.btn-submit-salary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45);
}
.btn-cancel {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #cbd5e1;
  padding: 0.65rem 1rem;
  border-radius: 0.55rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-cancel:hover {
  background: rgba(255, 255, 255, 0.12);
  color: #ffffff;
}

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
  grid-template-columns: 1.5fr 1fr 1fr 1fr 1.2fr auto;
  gap: 1rem;
  align-items: flex-end;
}
@media (max-width: 1024px) {
  .filter-grid { grid-template-columns: 1fr 1fr; }
}
.filter-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #cbd5e1;
  margin-bottom: 0.4rem;
  letter-spacing: 0.03em;
  text-transform: none; /* Do not uppercase icon glyphs! */
}
.filter-label i {
  font-style: normal;
  text-transform: none !important;
  color: #d4af37;
  display: inline-block;
}
.btn-reset {
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #cbd5e1;
  padding: 0.6rem 0.85rem;
  border-radius: 0.65rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-reset:hover {
  background: rgba(212, 175, 55, 0.2);
  border-color: #d4af37;
  color: #fde047;
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
.table-responsive { overflow-x: auto; }
.salary-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}
.salary-table th {
  padding: 0.9rem 1.15rem;
  background: rgba(11, 17, 32, 0.9);
  color: #d4af37;
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.salary-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.salary-row:hover td { background: rgba(255, 255, 255, 0.03); }

.staff-name { color: #ffffff; font-size: 0.92rem; font-weight: 700; }
.staff-id-tag { font-size: 0.75rem; color: #94a3b8; }

.role-pill {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
}
.pill-hdv { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.pill-other { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.12); }

.tour-name-cell { max-width: 260px; }
.tour-text { font-weight: 600; color: #f1f5f9; }

.total-salary-badge {
  font-weight: 800;
  font-size: 0.95rem;
  color: #34d399;
}

.status-pill {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 700;
}
.status-pending { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.status-approved { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.status-paid { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }

.btn-detail {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  background: rgba(14, 165, 233, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(14, 165, 233, 0.25);
  padding: 0.35rem 0.75rem;
  border-radius: 0.45rem;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.8rem;
  transition: all 0.15s;
}
.btn-detail:hover {
  background: rgba(14, 165, 233, 0.25);
  color: #ffffff;
}

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }
</style>
