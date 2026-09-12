<template>
  <div class="vue-admin-tour-list">
    <!-- PAGE HEADER -->
    <header class="tour-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-geo-alt-fill"></i>
          <span>Danh Mục Quản Trị</span>
        </div>
        <h1 class="header-title">Quản Lý Tour Du Lịch</h1>
        <p class="header-subtitle">
          Quản lý danh sách tour, cập nhật giá cơ bản, lịch khởi hành và điều hành tour
        </p>
      </div>

      <div class="header-right">
        <a href="index.php?act=tour/create" class="btn-create-tour">
          <i class="bi bi-plus-lg"></i>
          <span>Thêm Tour Mới</span>
        </a>
      </div>
    </header>

    <!-- STATS SUMMARY CARDS -->
    <section class="stats-row">
      <div class="stat-pill stat-total">
        <div class="stat-icon"><i class="bi bi-compass"></i></div>
        <div class="stat-info">
          <span class="stat-label">Tổng số tour</span>
          <strong class="stat-val">{{ formatNumber(totalTours) }}</strong>
        </div>
      </div>
      <div class="stat-pill stat-active">
        <div class="stat-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-info">
          <span class="stat-label">Đang hoạt động</span>
          <strong class="stat-val">{{ formatNumber(activeToursCount) }}</strong>
        </div>
      </div>
      <div class="stat-pill stat-paused">
        <div class="stat-icon"><i class="bi bi-pause-circle"></i></div>
        <div class="stat-info">
          <span class="stat-label">Tạm dừng / Ngừng</span>
          <strong class="stat-val">{{ formatNumber(pausedToursCount) }}</strong>
        </div>
      </div>
    </section>

    <!-- FILTER TOOLBAR -->
    <section class="filter-card">
      <div class="filter-grid">
        <!-- Search Input -->
        <div class="filter-col search-col">
          <label class="filter-label"><i class="bi bi-search me-1"></i>Tìm kiếm</label>
          <div class="input-icon-wrap">
            <i class="bi bi-search icon-left"></i>
            <input 
              type="text" 
              v-model="searchQuery" 
              @input="onSearchInput"
              placeholder="Nhập tên tour, mã tour hoặc điểm đến..." 
              class="form-control-custom"
            />
            <button 
              v-if="searchQuery" 
              @click="clearSearch" 
              class="btn-clear" 
              title="Xóa tìm kiếm"
            >
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Tour Type Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-globe2 me-1"></i>Loại tour</label>
          <select v-model="selectedType" @change="fetchTours(1)" class="form-select-custom">
            <option value="">Tất cả loại tour</option>
            <option value="TrongNuoc">Trong nước</option>
            <option value="QuocTe">Quốc tế</option>
            <option value="TheoYeuCau">Theo yêu cầu</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-shield-check me-1"></i>Trạng thái</label>
          <select v-model="selectedStatus" @change="fetchTours(1)" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="HoatDong">Đang hoạt động</option>
            <option value="TamDung">Ngừng hoạt động</option>
          </select>
        </div>

        <!-- Action Reset -->
        <div class="filter-col action-col">
          <button @click="resetFilters" class="btn-reset" title="Khôi phục bộ lọc">
            <i class="bi bi-arrow-counterclockwise me-1"></i> Làm mới
          </button>
        </div>
      </div>
    </section>

    <!-- TOURS DATA TABLE -->
    <section class="table-card">
      <div class="table-header-meta">
        <div class="meta-left">
          <span>Hiển thị <strong>{{ tours.length }}</strong> trên tổng số <strong>{{ totalTours }}</strong> tour</span>
          <span v-if="isLoading" class="loading-indicator">
            <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
          </span>
        </div>
        <div class="meta-right">
          <label class="per-page-label">Hiển thị:</label>
          <select v-model="perPage" @change="fetchTours(1)" class="per-page-select">
            <option :value="10">10 / trang</option>
            <option :value="20">20 / trang</option>
            <option :value="50">50 / trang</option>
          </select>
        </div>
      </div>

      <div class="table-responsive">
        <table class="tour-table">
          <thead>
            <tr>
              <th style="width: 80px;" class="text-center">ID</th>
              <th>Tên Tour & Lộ Trình</th>
              <th style="width: 140px;">Loại Tour</th>
              <th style="width: 160px;" class="text-end">Giá Cơ Bản</th>
              <th style="width: 140px;" class="text-center">Trạng Thái</th>
              <th style="width: 380px;" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tour in tours" :key="tour.tour_id" class="tour-row">
              <!-- ID -->
              <td class="text-center">
                <span class="id-badge">#{{ tour.tour_id }}</span>
              </td>

              <!-- Name & Route -->
              <td class="tour-main-cell">
                <div class="tour-title-wrap">
                  <a :href="'index.php?act=admin/chiTietTour&id=' + tour.tour_id" class="tour-title-link">
                    {{ tour.ten_tour }}
                  </a>
                  <div class="tour-subinfo">
                    <span v-if="tour.diem_khoi_hanh" class="route-point">
                      <i class="bi bi-geo-alt"></i> Xuất phát: {{ tour.diem_khoi_hanh }}
                    </span>
                    <span v-if="tour.thoi_gian_tour" class="duration-pill">
                      <i class="bi bi-clock"></i> {{ tour.thoi_gian_tour }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Type -->
              <td>
                <span class="type-badge" :class="getTypeBadgeClass(tour.loai_tour)">
                  <i class="bi" :class="getTypeIcon(tour.loai_tour)"></i>
                  <span>{{ formatLoaiTour(tour.loai_tour) }}</span>
                </span>
              </td>

              <!-- Price -->
              <td class="text-end">
                <span class="price-val">{{ formatCurrency(tour.gia_co_ban) }}</span>
              </td>

              <!-- Status -->
              <td class="text-center">
                <span class="status-badge" :class="tour.trang_thai === 'HoatDong' ? 'status-active' : 'status-paused'">
                  <i class="bi" :class="tour.trang_thai === 'HoatDong' ? 'bi-check-circle-fill' : 'bi-x-circle-fill'"></i>
                  {{ tour.trang_thai === 'HoatDong' ? 'Hoạt động' : 'Ngừng' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="text-center">
                <div class="action-buttons">
                  <a 
                    :href="'index.php?act=tour/update&id=' + tour.tour_id" 
                    class="btn-sm-action btn-edit"
                    title="Chỉnh sửa thông tin tour"
                  >
                    <i class="bi bi-pencil-square"></i> Sửa
                  </a>

                  <a 
                    :href="'index.php?act=admin/chiTietTour&id=' + tour.tour_id" 
                    class="btn-sm-action btn-view"
                    title="Xem hồ sơ chi tiết tour"
                  >
                    <i class="bi bi-eye"></i> Chi tiết
                  </a>

                  <a 
                    :href="'index.php?act=tour/clone&id=' + tour.tour_id" 
                    class="btn-sm-action btn-clone"
                    @click="confirmClone($event, tour)"
                    title="Nhân bản tour"
                  >
                    <i class="bi bi-files"></i> Clone
                  </a>

                  <button 
                    v-if="tour.qr_code_path" 
                    @click="openQrModal(tour)"
                    class="btn-sm-action btn-qr"
                    title="Xem mã QR thanh toán / tra cứu"
                  >
                    <i class="bi bi-qr-code"></i> QR
                  </button>

                  <a 
                    v-else 
                    :href="'index.php?act=tour/generateQr&id=' + tour.tour_id"
                    class="btn-sm-action btn-qr-gen"
                    title="Tạo mã QR cho tour này"
                  >
                    <i class="bi bi-qr-code-scan"></i> Tạo QR
                  </a>

                  <button 
                    @click="confirmDeleteTour(tour)" 
                    class="btn-sm-action btn-delete"
                    title="Xóa tour khỏi hệ thống"
                  >
                    <i class="bi bi-trash"></i> Xóa
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="tours.length === 0 && !isLoading">
              <td colspan="6" class="empty-row">
                <div class="empty-box">
                  <i class="bi bi-inbox empty-icon"></i>
                  <h4>Không tìm thấy tour nào</h4>
                  <p>Vui lòng thử thay đổi từ khóa tìm kiếm hoặc bỏ chọn các bộ lọc.</p>
                  <button @click="resetFilters" class="btn-create-tour mt-2">
                    <i class="bi bi-arrow-clockwise me-1"></i> Xóa bộ lọc
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="pagination-bar" v-if="totalPages > 1">
        <ul class="vue-pagination">
          <li :class="{ disabled: currentPageNumber <= 1 }">
            <button @click="fetchTours(currentPageNumber - 1)" :disabled="currentPageNumber <= 1">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>

          <li 
            v-for="page in visiblePages" 
            :key="page"
            :class="{ active: page === currentPageNumber }"
          >
            <button @click="fetchTours(page)">{{ page }}</button>
          </li>

          <li :class="{ disabled: currentPageNumber >= totalPages }">
            <button @click="fetchTours(currentPageNumber + 1)" :disabled="currentPageNumber >= totalPages">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
        </ul>
      </div>
    </section>

    <!-- MODAL PREVIEW QR CODE -->
    <div v-if="activeQrTour" class="modal-backdrop" @click="closeQrModal">
      <div class="modal-card" @click.stop>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-qr-code me-2"></i>Mã QR Tour #{{ activeQrTour.tour_id }}</h5>
          <button class="btn-close-modal" @click="closeQrModal">&times;</button>
        </div>
        <div class="modal-body text-center">
          <h6 class="qr-tour-title">{{ activeQrTour.ten_tour }}</h6>
          <img :src="activeQrTour.qr_url" alt="QR Code" class="qr-image" />
          <p class="qr-note">Khách hàng quét mã này để tra cứu thông tin hoặc đặt tour trực tiếp</p>
        </div>
        <div class="modal-footer">
          <a :href="activeQrTour.qr_url" download class="btn-sm-action btn-view">
            <i class="bi bi-download me-1"></i> Tải ảnh về
          </a>
          <button class="btn-reset" @click="closeQrModal">Đóng</button>
        </div>
      </div>
    </div>

    <!-- HIDDEN FORM FOR SECURE POST DELETION -->
    <form ref="deleteFormRef" method="POST" action="index.php?act=tour/delete" style="display: none;">
      <input type="hidden" name="_csrf_global" :value="csrfToken">
      <input type="hidden" name="id" :value="deleteTourId">
    </form>
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
const selectedType = ref('');
const selectedStatus = ref('');
const perPage = ref(20);
const currentPageNumber = ref(1);
const totalPages = ref(1);
const totalTours = ref(0);
const tours = ref([]);
const csrfToken = ref('');

// Modals & Deletion
const activeQrTour = ref(null);
const deleteTourId = ref(null);
const deleteFormRef = ref(null);

let searchTimeout = null;

// Stats calculations
const activeToursCount = computed(() => {
  return tours.value.filter(t => t.trang_thai === 'HoatDong').length;
});
const pausedToursCount = computed(() => {
  return tours.value.filter(t => t.trang_thai === 'TamDung').length;
});

// Visible pagination pages
const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPageNumber.value - 2);
  const end = Math.min(totalPages.value, currentPageNumber.value + 2);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

// Debounced search
function onSearchInput() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    fetchTours(1);
  }, 350);
}

function clearSearch() {
  searchQuery.value = '';
  fetchTours(1);
}

function resetFilters() {
  searchQuery.value = '';
  selectedType.value = '';
  selectedStatus.value = '';
  fetchTours(1);
}

// Fetch tour data from PHP API
async function fetchTours(page = 1) {
  isLoading.value = true;
  currentPageNumber.value = page;

  const params = new URLSearchParams({
    act: 'admin/apiTourList',
    page: String(page),
    per_page: String(perPage.value)
  });

  if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
  if (selectedType.value) params.append('loai_tour', selectedType.value);
  if (selectedStatus.value) params.append('trang_thai', selectedStatus.value);

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
    console.error('[Vue TourList] Error fetching tours:', err);
  } finally {
    isLoading.value = false;
  }
}

function applyData(data) {
  if (!data) return;
  tours.value = data.tours || [];
  totalTours.value = data.totalTours || 0;
  totalPages.value = data.totalPages || 1;
  currentPageNumber.value = data.pageNumber || 1;
  if (data.csrfToken) {
    csrfToken.value = data.csrfToken;
  }
}

// Actions
function confirmClone(event, tour) {
  if (!confirm(`Bạn có chắc chắn muốn sao chép (clone) tour "${tour.ten_tour}"?`)) {
    event.preventDefault();
  }
}

function confirmDeleteTour(tour) {
  if (confirm(`Bạn có chắc chắn muốn XÓA tour "#${tour.tour_id} - ${tour.ten_tour}"?\n\nLưu ý: Hành động này không thể hoàn tác.`)) {
    deleteTourId.value = tour.tour_id;
    setTimeout(() => {
      if (deleteFormRef.value) {
        deleteFormRef.value.submit();
      }
    }, 50);
  }
}

function openQrModal(tour) {
  const qrUrl = tour.qr_code_path.startsWith('http') 
    ? tour.qr_code_path 
    : (window.__BASE_URL__ || '') + tour.qr_code_path;
  activeQrTour.value = {
    ...tour,
    qr_url: qrUrl
  };
}

function closeQrModal() {
  activeQrTour.value = null;
}

// Formatters
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatNumber(val) {
  return (Number(val) || 0).toLocaleString('vi-VN');
}

function formatLoaiTour(type) {
  switch (type) {
    case 'TrongNuoc': return 'Trong nước';
    case 'QuocTe': return 'Quốc tế';
    case 'TheoYeuCau': return 'Theo yêu cầu';
    default: return type || 'Trong nước';
  }
}

function getTypeBadgeClass(type) {
  switch (type) {
    case 'TrongNuoc': return 'badge-domestic';
    case 'QuocTe': return 'badge-international';
    case 'TheoYeuCau': return 'badge-custom';
    default: return 'badge-domestic';
  }
}

function getTypeIcon(type) {
  switch (type) {
    case 'TrongNuoc': return 'bi-geo-alt';
    case 'QuocTe': return 'bi-airplane';
    case 'TheoYeuCau': return 'bi-stars';
    default: return 'bi-map';
  }
}

// Lifecycle
onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchTours(1);
  }
});
</script>

<style scoped>
.vue-admin-tour-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* PAGE HEADER */
.tour-header,
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
  padding: 1.75rem 2rem;
  border-radius: 1.25rem;
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
  letter-spacing: 0.05em;
  margin-bottom: 0.5rem;
}
.header-title {
  font-size: 1.75rem;
  font-weight: 800;
  margin: 0;
  letter-spacing: -0.02em;
  color: #ffffff;
}
.header-subtitle {
  font-size: 0.95rem;
  color: #94a3b8;
  margin: 0.25rem 0 0;
}
.btn-create-tour {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  padding: 0.75rem 1.4rem;
  border-radius: 0.65rem;
  font-weight: 700;
  font-size: 0.95rem;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s ease;
  border: none;
}
.btn-create-tour:hover {
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
.stat-total .stat-icon { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.stat-active .stat-icon { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.stat-paused .stat-icon { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }
.stat-info { display: flex; flex-direction: column; }
.stat-label { font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-val { font-size: 1.5rem; font-weight: 800; color: #ffffff; line-height: 1.2; }

/* FILTER TOOLBAR */
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
  grid-template-columns: 2fr 1fr 1fr auto;
  gap: 1.25rem;
  align-items: flex-end;
}
@media (max-width: 992px) {
  .filter-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 600px) {
  .filter-grid { grid-template-columns: 1fr; }
}
.filter-label {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #cbd5e1;
  margin-bottom: 0.45rem;
  letter-spacing: 0.03em;
  text-transform: none;
}
.filter-label i {
  font-style: normal;
  text-transform: none !important;
  color: #d4af37;
  display: inline-block;
}
.input-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.icon-left {
  position: absolute;
  left: 0.85rem;
  color: #94a3b8;
  font-size: 0.9rem;
}
.btn-clear {
  position: absolute;
  right: 0.75rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0;
}
.btn-clear:hover { color: #f1f5f9; }
.form-control-custom {
  width: 100%;
  padding: 0.65rem 2rem 0.65rem 2.2rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.65rem;
  font-size: 0.9rem;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
  transition: all 0.2s;
}
.form-control-custom:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}
.form-control-custom::placeholder {
  color: #64748b;
}
.form-select-custom {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.65rem;
  font-size: 0.9rem;
  background: rgba(11, 17, 32, 0.85);
  outline: none;
  color: #ffffff;
  transition: all 0.2s;
}
.form-select-custom:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}
.btn-reset {
  display: inline-flex;
  align-items: center;
  padding: 0.65rem 1.15rem;
  border-radius: 0.65rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #cbd5e1;
  font-weight: 600;
  font-size: 0.88rem;
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
.table-header-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(11, 17, 32, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.88rem;
  color: #cbd5e1;
}
.loading-indicator {
  margin-left: 1rem;
  color: #38bdf8;
  font-weight: 600;
}
.spin {
  display: inline-block;
  animation: spin 0.8s linear infinite;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

.meta-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.per-page-select {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(11, 17, 32, 0.85);
  border-radius: 0.4rem;
  padding: 0.25rem 0.5rem;
  font-size: 0.82rem;
  outline: none;
  color: #ffffff;
}

.table-responsive {
  overflow-x: auto;
}
.tour-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
}
.tour-table th {
  padding: 0.9rem 1.25rem;
  background: rgba(11, 17, 32, 0.95);
  color: #d4af37;
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.tour-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.tour-row:hover td {
  background: rgba(255, 255, 255, 0.03);
}

.id-badge {
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.25rem 0.55rem;
  border-radius: 0.4rem;
}

.tour-main-cell {
  max-width: 380px;
}
.tour-title-wrap {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}
.tour-title-link {
  font-weight: 700;
  color: #ffffff;
  text-decoration: none;
  font-size: 0.95rem;
  line-height: 1.35;
}
.tour-title-link:hover {
  color: #fde047;
}
.tour-subinfo {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  font-size: 0.8rem;
  color: #94a3b8;
}
.route-point {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
}
.duration-pill {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  padding: 0.1rem 0.45rem;
  border-radius: 0.3rem;
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
}

/* TYPE BADGES */
.type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.3rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}
.badge-domestic { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.badge-international { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.badge-custom { background: rgba(139, 92, 246, 0.18); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.35); }

.price-val {
  font-weight: 800;
  font-size: 1rem;
  color: #34d399;
}

/* STATUS BADGES */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.25rem 0.7rem;
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 700;
}
.status-active { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.status-paused { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }

/* ACTION BUTTONS */
.action-buttons {
  display: flex;
  gap: 0.4rem;
  justify-content: center;
  flex-wrap: wrap;
}
.btn-sm-action {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.35rem 0.65rem;
  border-radius: 0.45rem;
  font-size: 0.78rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.15s;
}
.btn-edit { background: rgba(255, 255, 255, 0.06); color: #cbd5e1; border-color: rgba(255, 255, 255, 0.14); }
.btn-edit:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }

.btn-view { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border-color: rgba(14, 165, 233, 0.25); }
.btn-view:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }

.btn-clone { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.25); }
.btn-clone:hover { background: rgba(245, 158, 11, 0.25); color: #fde047; }

.btn-qr { background: rgba(139, 92, 246, 0.15); color: #a78bfa; border-color: rgba(139, 92, 246, 0.25); }
.btn-qr:hover { background: rgba(139, 92, 246, 0.25); color: #ffffff; }

.btn-qr-gen { background: rgba(255, 255, 255, 0.06); color: #cbd5e1; border-color: rgba(255, 255, 255, 0.14); }
.btn-qr-gen:hover { background: rgba(139, 92, 246, 0.2); color: #a78bfa; }

.btn-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); }
.btn-delete:hover { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }

/* EMPTY ROW */
.empty-row {
  padding: 3rem !important;
}
.empty-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  color: #64748b;
}
.empty-icon {
  font-size: 3rem;
  color: #475569;
  margin-bottom: 0.75rem;
}

/* PAGINATION */
.pagination-bar {
  display: flex;
  justify-content: center;
  padding: 1.25rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(11, 17, 32, 0.9);
}
.vue-pagination {
  display: flex;
  gap: 0.4rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
.vue-pagination li button {
  min-width: 36px;
  height: 36px;
  padding: 0 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.5rem;
  font-size: 0.88rem;
  font-weight: 600;
  color: #cbd5e1;
  cursor: pointer;
  transition: all 0.15s;
}
.vue-pagination li button:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.12);
  border-color: rgba(255, 255, 255, 0.25);
  color: #ffffff;
}
.vue-pagination li.active button {
  background: #d4af37;
  color: #0b1120;
  border-color: #d4af37;
  font-weight: 700;
}
.vue-pagination li.disabled button {
  opacity: 0.4;
  cursor: not-allowed;
}

/* MODAL */
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(11, 17, 32, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.modal-card {
  background: rgba(22, 31, 46, 0.95);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 1.25rem;
  width: 90%;
  max-width: 420px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  overflow: hidden;
  backdrop-filter: blur(16px);
  animation: modalIn 0.2s ease-out;
}
@keyframes modalIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
}
.btn-close-modal {
  background: transparent;
  border: none;
  font-size: 1.5rem;
  line-height: 1;
  color: #94a3b8;
  cursor: pointer;
}
.btn-close-modal:hover { color: #ffffff; }
.modal-body {
  padding: 1.5rem;
}
.qr-tour-title {
  font-weight: 700;
  color: #f1f5f9;
  margin-bottom: 1rem;
}
.qr-image {
  width: 220px;
  height: 220px;
  object-fit: contain;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.5rem;
  padding: 0.5rem;
  background: #ffffff;
}
.qr-note {
  font-size: 0.8rem;
  color: #94a3b8;
  margin-top: 0.85rem;
  margin-bottom: 0;
}
.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: rgba(11, 17, 32, 0.9);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
</style>
