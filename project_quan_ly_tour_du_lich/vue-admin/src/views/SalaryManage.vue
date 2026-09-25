<template>
  <div class="vue-admin-salary-manage">
    <!-- PAGE HEADER BANNER (MATCHING IMAGE 2) -->
    <header class="page-header">
      <div class="header-left">
        <div class="header-icon-circle">
          <i class="bi bi-wallet2"></i>
        </div>
        <div class="header-text-group">
          <div class="badge-tag">
            <span>TÀI CHÍNH &amp; NHÂN SỰ</span>
          </div>
          <h1 class="header-title">
            Quản Lý Lương Thưởng<br>Nhân Sự
          </h1>
          <p class="header-subtitle">
            Theo dõi định mức lương cứng, hoa hồng dẫn tour và quy trình duyệt thanh toán
          </p>
        </div>
      </div>

      <!-- CENTER MODERN ILLUSTRATION (MATCHING IMAGE 2) -->
      <div class="header-illustration">
        <svg width="220" height="110" viewBox="0 0 220 110" fill="none" xmlns="http://www.w3.org/2000/svg">
          <!-- Soft Background Glow -->
          <ellipse cx="110" cy="55" rx="85" ry="42" fill="#eff6ff" />
          
          <!-- Document Card -->
          <g filter="drop-shadow(0px 6px 12px rgba(37, 99, 235, 0.08))">
            <rect x="55" y="16" width="76" height="80" rx="8" fill="#ffffff" stroke="#dbeafe" stroke-width="1.5"/>
            <!-- Avatar on doc -->
            <circle cx="93" cy="36" r="10" fill="#eff6ff" stroke="#bfdbfe" stroke-width="1.5"/>
            <path d="M87 44c0-3.3 2.7-6 6-6s6 2.7 6 6" stroke="#3b82f6" stroke-width="1.5" stroke-linecap="round"/>
            <circle cx="93" cy="34" r="3.5" fill="#3b82f6"/>
            <!-- Text lines on doc -->
            <rect x="67" y="52" width="52" height="4" rx="2" fill="#e2e8f0"/>
            <rect x="67" y="60" width="38" height="4" rx="2" fill="#cbd5e1"/>
            <rect x="67" y="68" width="46" height="4" rx="2" fill="#f1f5f9"/>
            <rect x="67" y="76" width="30" height="4" rx="2" fill="#e2e8f0"/>
          </g>

          <!-- Golden Coins Stack -->
          <g filter="drop-shadow(0px 4px 8px rgba(245, 158, 11, 0.2))">
            <!-- Coin 1 bottom -->
            <ellipse cx="62" cy="85" rx="14" ry="5.5" fill="#d97706"/>
            <ellipse cx="62" cy="82" rx="14" ry="5.5" fill="#f59e0b"/>
            <ellipse cx="62" cy="80" rx="14" ry="5.5" fill="#fbbf24"/>
            <!-- Coin 2 middle -->
            <ellipse cx="62" cy="77" rx="14" ry="5.5" fill="#d97706"/>
            <ellipse cx="62" cy="74" rx="14" ry="5.5" fill="#f59e0b"/>
            <ellipse cx="62" cy="72" rx="14" ry="5.5" fill="#fbbf24"/>
            <!-- Coin 3 top -->
            <ellipse cx="62" cy="69" rx="14" ry="5.5" fill="#d97706"/>
            <ellipse cx="62" cy="66" rx="14" ry="5.5" fill="#f59e0b"/>
            <ellipse cx="62" cy="64" rx="14" ry="5.5" fill="#fbbf24"/>
            <ellipse cx="62" cy="64" rx="10" ry="3.5" fill="#fef3c7"/>
          </g>

          <!-- Rising Bar Chart Card -->
          <g filter="drop-shadow(0px 6px 14px rgba(37, 99, 235, 0.12))">
            <rect x="135" y="32" width="46" height="52" rx="6" fill="#ffffff" stroke="#e0e7ff" stroke-width="1.2"/>
            <!-- Bar 1 -->
            <rect x="143" y="62" width="6" height="16" rx="2" fill="#93c5fd"/>
            <!-- Bar 2 -->
            <rect x="153" y="52" width="6" height="26" rx="2" fill="#60a5fa"/>
            <!-- Bar 3 -->
            <rect x="163" y="42" width="6" height="36" rx="2" fill="#2563eb"/>
          </g>
        </svg>
      </div>

      <!-- RIGHT ACTION BUTTON -->
      <div class="header-actions">
        <button class="btn-create" @click="showCreateForm = !showCreateForm">
          <i class="bi" :class="showCreateForm ? 'bi-x-lg' : 'bi-plus-lg'"></i>
          <span>{{ showCreateForm ? 'Đóng form' : 'Tạo Lương/Thưởng' }}</span>
          <i v-if="!showCreateForm" class="bi bi-chevron-right ms-1"></i>
        </button>
      </div>
    </header>

    <!-- 4 KPI STAT CARDS (MATCHING IMAGE 2) -->
    <section class="stats-row">
      <!-- Card 1: Tổng lương tháng này -->
      <div class="stat-card stat-card-green">
        <div class="stat-main">
          <div class="stat-icon-circle bg-emerald-light text-emerald">
            <i class="bi bi-cash-stack"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Tổng lương tháng này</div>
            <div class="stat-val">{{ formatCurrency(totalPayroll) }}</div>
            <div class="stat-sub text-trend">
              <span class="trend-green">↑ 0%</span>
              <span class="sub-muted">so với tháng trước</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Sparkline Curve SVG -->
          <svg width="60" height="28" viewBox="0 0 60 28" fill="none">
            <path d="M2 24 C14 24, 20 16, 32 16 C44 16, 48 4, 58 4" stroke="#34d399" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>

      <!-- Card 2: Tổng nhân sự -->
      <div class="stat-card stat-card-blue">
        <div class="stat-main">
          <div class="stat-icon-circle bg-blue-light text-blue">
            <i class="bi bi-people-fill"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Tổng nhân sự</div>
            <div class="stat-val">{{ filteredList.length }}</div>
            <div class="stat-sub text-muted">
              <span>* Không thay đổi</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Outline Person SVG -->
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#93c5fd" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="7" r="4"/>
            <path d="M5.5 21a6.5 6.5 0 0 1 13 0"/>
          </svg>
        </div>
      </div>

      <!-- Card 3: Tổng tour phụ trách -->
      <div class="stat-card stat-card-amber">
        <div class="stat-main">
          <div class="stat-icon-circle bg-amber-light text-amber">
            <i class="bi bi-award-fill"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Tổng tour phụ trách</div>
            <div class="stat-val">{{ totalToursCount }}</div>
            <div class="stat-sub text-muted">
              <span>* Không thay đổi</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Outline Compass/Route SVG -->
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#fcd34d" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/>
            <line x1="9" y1="3" x2="9" y2="18"/>
            <line x1="15" y1="6" x2="15" y2="21"/>
          </svg>
        </div>
      </div>

      <!-- Card 4: Tổng trạng thái -->
      <div class="stat-card stat-card-purple">
        <div class="stat-main">
          <div class="stat-icon-circle bg-purple-light text-purple">
            <i class="bi bi-grid-fill"></i>
          </div>
          <div class="stat-content">
            <div class="stat-label">Tổng trạng thái</div>
            <div class="stat-val">{{ filteredList.length }} mục</div>
            <div class="stat-sub">
              <span class="dot-active">●</span>
              <span class="sub-muted">Đang hoạt động</span>
            </div>
          </div>
        </div>
        <div class="stat-graphic">
          <!-- Outline Card/Grid SVG -->
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#c4b5fd" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <line x1="3" y1="9" x2="21" y2="9"/>
            <line x1="9" y1="21" x2="9" y2="9"/>
          </svg>
        </div>
      </div>
    </section>

    <!-- COLLAPSIBLE FORM: TẠO LƯƠNG/THƯỞNG MỚI -->
    <transition name="slide">
      <section v-if="showCreateForm" class="create-card">
        <div class="create-card-header">
          <h4><i class="bi bi-plus-circle me-2"></i>Thêm Định Mức Lương/Thưởng Tour</h4>
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

    <!-- FILTER TOOLBAR (MATCHING IMAGE 2) -->
    <section class="filter-card">
      <div class="filter-grid">
        <!-- Search Name with Icon Inside -->
        <div class="filter-col col-search">
          <div class="search-input-box">
            <i class="bi bi-search search-icon"></i>
            <input 
              type="text" 
              v-model="searchKeyword" 
              placeholder="Nhập tên nhân sự..." 
              class="form-control-search"
            />
          </div>
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

        <!-- Period Selectors (Month + Year Dropdowns side by side) -->
        <div class="filter-col period-col">
          <label class="filter-label"><i class="bi bi-calendar me-1"></i>Kỳ lương</label>
          <div class="period-select-wrap">
            <select v-model="filterMonth" :disabled="showAll" class="form-select-custom select-month">
              <option value="">Tháng --</option>
              <option v-for="m in 12" :key="m" :value="String(m)">Tháng {{ m }}</option>
            </select>
            <select v-model="filterYear" :disabled="showAll" class="form-select-custom select-year">
              <option value="2024">2024</option>
              <option value="2025">2025</option>
              <option value="2026">2026</option>
              <option value="2027">2027</option>
            </select>
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

    <!-- DATA TABLE CARD (MATCHING IMAGE 2) -->
    <section class="table-card">
      <div class="table-responsive">
        <table class="salary-table">
          <thead>
            <tr>
              <th class="th-checkbox text-center">
                <input type="checkbox" v-model="selectAll" class="custom-checkbox" />
              </th>
              <th>NHÂN SỰ</th>
              <th class="text-center">VAI TRÒ</th>
              <th>TOUR / LỊCH KHỞI HÀNH</th>
              <th class="text-center">NGÀY KHỞI HÀNH</th>
              <th class="text-end">CỐ ĐỊNH</th>
              <th class="text-end">HOA HỒNG</th>
              <th class="text-end">TỔNG LƯƠNG</th>
              <th class="text-center">TRẠNG THÁI</th>
              <th class="text-center">CHI TIẾT</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, idx) in filteredList" :key="idx" class="salary-row">
              <!-- Checkbox -->
              <td class="text-center td-checkbox">
                <input type="checkbox" :value="row.nhan_su_id" v-model="selectedRows" class="custom-checkbox" />
              </td>

              <!-- Staff Info (Avatar + Name + ID) -->
              <td class="td-staff">
                <div class="staff-cell-flex">
                  <div class="staff-avatar-box">
                    <img v-if="row.avatar" :src="getAvatarUrl(row.avatar)" class="avatar-img" :alt="row.ho_ten" />
                    <div v-else class="avatar-fallback" :style="{ background: getAvatarBg(row.ho_ten, idx) }">
                      <!-- Render SVG avatar face based on index -->
                      <svg v-if="idx % 2 === 0" width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <circle cx="18" cy="18" r="18" fill="#e0f2fe"/>
                        <circle cx="18" cy="14" r="6" fill="#0284c7"/>
                        <path d="M8 30c0-5.5 4.5-10 10-10s10 4.5 10 10" fill="#0284c7"/>
                      </svg>
                      <svg v-else width="36" height="36" viewBox="0 0 36 36" fill="none">
                        <circle cx="18" cy="18" r="18" fill="#fce7f3"/>
                        <circle cx="18" cy="14" r="6" fill="#db2777"/>
                        <path d="M8 30c0-5.5 4.5-10 10-10s10 4.5 10 10" fill="#db2777"/>
                      </svg>
                    </div>
                  </div>
                  <div class="staff-meta">
                    <strong class="staff-name">{{ row.ho_ten }}</strong>
                    <div class="staff-id">#{{ row.nhan_su_id }}</div>
                  </div>
                </div>
              </td>

              <!-- Role Badge -->
              <td class="text-center">
                <span class="role-pill">
                  {{ row.vai_tro || 'HDV' }}
                </span>
              </td>

              <!-- Tour / Departure -->
              <td class="tour-cell">
                <div class="tour-title">{{ row.ten_tour_gan_nhat || row.ten_tour || 'N/A' }}</div>
                <div class="tour-sub" v-if="row.ngay_khoi_hanh_gan_nhat || row.ngay_khoi_hanh">
                  Khởi hành: {{ formatDate(row.ngay_khoi_hanh_gan_nhat || row.ngay_khoi_hanh) }}
                </div>
              </td>

              <!-- Departure Date -->
              <td class="text-center text-date">
                {{ formatDate(row.ngay_khoi_hanh_gan_nhat || row.ngay_khoi_hanh) }}
              </td>

              <!-- Fixed Amount -->
              <td class="text-end text-amount">
                {{ formatCurrency(row.tong_co_dinh || row.so_tien_co_dinh) }}
              </td>

              <!-- Commission Amount -->
              <td class="text-end text-amount">
                {{ formatCurrency(row.tong_hoa_hong || row.tien_hoa_hong) }}
              </td>

              <!-- Total Salary (Bold Green) -->
              <td class="text-end">
                <span class="total-salary-val">
                  {{ formatCurrency(row.tong_luong) }}
                </span>
              </td>

              <!-- Status Badge (Pending / Approved / Paid) -->
              <td class="text-center">
                <span class="status-badge" :class="getStatusClass(row.trang_thai_luong_tong_hop || row.trang_thai_luong)">
                  {{ formatStatus(row.trang_thai_luong_tong_hop || row.trang_thai_luong) }}
                </span>
              </td>

              <!-- Action Link -->
              <td class="text-center">
                <a 
                  :href="'index.php?act=admin/chiTietLuong&nhan_su_id=' + row.nhan_su_id + (filterMonth ? '&month=' + filterMonth : '') + (filterYear ? '&year=' + filterYear : '')" 
                  class="btn-detail-action"
                  title="Xem bảng chi tiết lương & thao tác duyệt"
                >
                  <i class="bi bi-eye"></i> Chi tiết
                </a>
              </td>
            </tr>

            <!-- Empty row -->
            <tr v-if="filteredList.length === 0">
              <td colspan="10" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy dữ liệu lương phù hợp với bộ lọc.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TABLE FOOTER BAR (MATCHING IMAGE 2) -->
      <div class="table-footer-bar">
        <div class="footer-info">
          Hiển thị 1 - {{ filteredList.length }} trong tổng số {{ allLuongTongHop.length }} nhân sự
        </div>
        <div class="footer-pagination">
          <button class="btn-page btn-page-prev" disabled>
            <i class="bi bi-chevron-left"></i>
          </button>
          <button class="btn-page btn-page-active">1</button>
          <button class="btn-page btn-page-next" disabled>
            <i class="bi bi-chevron-right"></i>
          </button>
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
const showCreateForm = ref(false);
const searchKeyword = ref('');
const filterNhanSu = ref('');
const filterTour = ref('');
const filterTrangThai = ref('');
const filterMonth = ref(String(new Date().getMonth() + 1));
const filterYear = ref(String(new Date().getFullYear()));
const showAll = ref(false);
const selectedRows = ref([]);

const allLuongTongHop = ref([]);
const nhanSuList = ref([]);
const tourList = ref([]);
const lichKhoiHanhList = ref([]);
const csrfToken = ref('');

// Computed List
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
    list = list.filter(r => (r.trang_thai_luong_tong_hop || r.trang_thai_luong) === filterTrangThai.value);
  }

  return list;
});

// Select All Checkbox
const selectAll = computed({
  get() {
    return filteredList.value.length > 0 && selectedRows.value.length === filteredList.value.length;
  },
  set(val) {
    if (val) {
      selectedRows.value = filteredList.value.map(r => r.nhan_su_id);
    } else {
      selectedRows.value = [];
    }
  }
});

// Summary Totals
const totalPayroll = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_luong) || 0), 0);
});

const totalFixed = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_co_dinh || r.so_tien_co_dinh) || 0), 0);
});

const totalCommission = computed(() => {
  return filteredList.value.reduce((sum, r) => sum + (Number(r.tong_hoa_hong || r.tien_hoa_hong) || 0), 0);
});

const totalToursCount = computed(() => {
  const tours = new Set();
  filteredList.value.forEach(r => {
    if (r.tour_id) tours.add(r.tour_id);
    if (r.ten_tour_gan_nhat) tours.add(r.ten_tour_gan_nhat);
  });
  return tours.size;
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

// Helpers
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatDate(str) {
  if (!str) return '--/--/----';
  const parts = str.split(' ')[0].split('-');
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
    case 'ChoDuyet': return 'badge-status-pending';
    case 'DaDuyet': return 'badge-status-approved';
    case 'DaThanhToan': return 'badge-status-paid';
    default: return 'badge-status-pending';
  }
}

function getAvatarUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return window.__BASE_URL__ ? window.__BASE_URL__ + path : path;
}

function getAvatarBg(name, idx) {
  const colors = ['#e0f2fe', '#fce7f3', '#fef3c7', '#dcfce7'];
  return colors[idx % colors.length];
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
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #0f172a;
}

/* 1. PAGE HEADER BANNER */
.page-header {
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

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.header-icon-circle {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #eff6ff;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2563eb;
  font-size: 1.8rem;
  flex-shrink: 0;
  border: 1px solid #dbeafe;
}

.header-text-group {
  display: flex;
  flex-direction: column;
}

.badge-tag {
  display: inline-flex;
  align-items: center;
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

.header-title {
  font-size: 1.65rem;
  font-weight: 800;
  margin: 0;
  color: #0f172a;
  line-height: 1.25;
  letter-spacing: -0.02em;
}

.header-subtitle {
  font-size: 0.86rem;
  color: #64748b;
  margin: 0.4rem 0 0;
  line-height: 1.4;
}

.header-illustration {
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 992px) {
  .header-illustration {
    display: none;
  }
}

.btn-create {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: #2563eb;
  color: #ffffff;
  padding: 0.75rem 1.4rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.92rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-create:hover {
  background: #1d4ed8;
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
  transform: translateY(-1px);
}

/* 2. STATS ROW (4 CARDS) */
.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

@media (max-width: 1200px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 640px) {
  .stats-row {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem 1.4rem;
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
  gap: 1rem;
}

.stat-icon-circle {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}

.bg-emerald-light { background: #ecfdf5; }
.text-emerald { color: #10b981; }

.bg-blue-light { background: #eff6ff; }
.text-blue { color: #3b82f6; }

.bg-amber-light { background: #fffbeb; }
.text-amber { color: #f59e0b; }

.bg-purple-light { background: #f5f3ff; }
.text-purple { color: #8b5cf6; }

.stat-content {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: #64748b;
  margin-bottom: 0.2rem;
}

.stat-val {
  font-size: 1.55rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
}

.stat-sub {
  font-size: 0.76rem;
  margin-top: 0.25rem;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.trend-green {
  color: #10b981;
  font-weight: 700;
}

.sub-muted {
  color: #94a3b8;
}

.dot-active {
  color: #10b981;
  font-size: 0.85rem;
}

.stat-graphic {
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.85;
}

/* 3. CREATE CARD */
.create-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 1.5rem 1.8rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
}

.create-card-header {
  margin-bottom: 1.25rem;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 0.75rem;
}

.create-card-header h4 {
  color: #2563eb;
  font-weight: 700;
  margin: 0;
}

.sub-text {
  font-size: 0.85rem;
  color: #64748b;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.1rem;
}

.form-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 700;
  color: #334155;
  margin-bottom: 0.35rem;
}

.form-control-custom,
.form-select-custom {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control-custom:focus,
.form-select-custom:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.form-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.btn-submit-salary {
  background: #2563eb;
  color: #ffffff;
  padding: 0.65rem 1.4rem;
  border-radius: 8px;
  border: none;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
  transition: all 0.2s;
}

.btn-submit-salary:hover {
  background: #1d4ed8;
}

.btn-cancel {
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  color: #475569;
  padding: 0.65rem 1.1rem;
  border-radius: 8px;
  cursor: pointer;
}

/* 4. FILTER CARD */
.filter-card {
  background: #ffffff;
  border-radius: 12px;
  padding: 1.2rem 1.5rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}

.filter-grid {
  display: grid;
  grid-template-columns: 1.8fr 1.2fr 1.2fr 1.2fr 1.6fr auto;
  gap: 1rem;
  align-items: flex-end;
}

@media (max-width: 1200px) {
  .filter-grid {
    grid-template-columns: 1fr 1fr;
  }
}

.col-search {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.search-input-box {
  position: relative;
  width: 100%;
}

.search-icon {
  position: absolute;
  left: 0.9rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.9rem;
  pointer-events: none;
}

.form-control-search {
  width: 100%;
  padding: 0.65rem 0.9rem 0.65rem 2.4rem;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control-search:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.filter-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
  font-weight: 600;
  color: #475569;
  margin-bottom: 0.35rem;
}

.period-select-wrap {
  display: flex;
  gap: 0.5rem;
}

.select-month {
  flex: 1.2;
}

.select-year {
  flex: 1;
}

.btn-reset {
  width: 42px;
  height: 42px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #ffffff;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-reset:hover {
  background: #f1f5f9;
  color: #0f172a;
  border-color: #94a3b8;
}

/* 5. TABLE CARD */
.table-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.salary-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}

.salary-table th {
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

.salary-table td {
  padding: 1.1rem 1.15rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #334155;
  background: #ffffff;
}

.salary-row:hover td {
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

/* Staff Cell */
.staff-cell-flex {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.staff-avatar-box {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

.avatar-img {
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

.staff-meta {
  display: flex;
  flex-direction: column;
}

.staff-name {
  color: #0f172a;
  font-size: 0.92rem;
  font-weight: 700;
}

.staff-id {
  font-size: 0.78rem;
  color: #94a3b8;
  font-weight: 600;
}

/* Role Badge */
.role-pill {
  display: inline-block;
  padding: 0.22rem 0.8rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  background: #e0f2fe;
  color: #0284c7;
  border: 1px solid #bae6fd;
  text-transform: uppercase;
}

/* Tour Cell */
.tour-cell {
  max-width: 280px;
}

.tour-title {
  font-weight: 600;
  color: #1e293b;
  font-size: 0.88rem;
}

.tour-sub {
  font-size: 0.78rem;
  color: #94a3b8;
  margin-top: 0.15rem;
}

.text-date {
  color: #475569;
  font-weight: 500;
}

.text-amount {
  color: #334155;
  font-weight: 600;
}

.total-salary-val {
  font-weight: 700;
  font-size: 0.95rem;
  color: #10b981;
}

/* Status Badges */
.status-badge {
  display: inline-block;
  padding: 0.3rem 0.85rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.badge-status-pending {
  background: #fef3c7;
  color: #d97706;
  border: 1px solid #fde68a;
}

.badge-status-approved {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.badge-status-paid {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
}

/* Action Button */
.btn-detail-action {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  padding: 0.35rem 0.85rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  font-size: 0.82rem;
  transition: all 0.15s ease;
}

.btn-detail-action:hover {
  background: #dbeafe;
  color: #1d4ed8;
  border-color: #93c5fd;
}

.empty-cell {
  padding: 3rem !important;
  text-align: center;
  color: #64748b;
}

/* 6. TABLE FOOTER BAR */
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
</style>
