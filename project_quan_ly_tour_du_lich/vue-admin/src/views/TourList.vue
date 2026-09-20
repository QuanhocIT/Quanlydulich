<template>
  <div class="vue-admin-tour-list">
    <!-- HERO HEADER BANNER (Matching Reference Aesthetic) -->
    <header class="tour-hero-banner">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <div class="hero-left">
          <div class="kicker-pill">
            <i class="bi bi-compass-fill"></i>
            <span>AVENTURA • TOUR MANAGEMENT</span>
          </div>
          <h1 class="hero-title">Quản Lý Tour Du Lịch</h1>
          <p class="hero-subtitle">
            Quản lý danh sách tour, cập nhật biểu giá cơ bản, lịch khởi hành và điều phối dịch vụ toàn hệ thống.
          </p>
        </div>

        <div class="hero-right">
          <div class="hero-live-pill">
            <span class="live-dot-green"></span>
            <span class="live-date-text">{{ currentDateFormatted }}</span>
            <span class="live-badge-status">{{ totalTours }} Tours</span>
          </div>
          <a href="index.php?act=tour/create" class="btn-hero-create" title="Thêm tour mới">
            <i class="bi bi-plus-lg"></i>
            <span>Thêm Tour Mới</span>
          </a>
        </div>
      </div>
    </header>

    <!-- 4 KPI STATS METRIC CARDS (Exact Match to Reference Style) -->
    <section class="kpi-metric-grid">
      <!-- KPI 1: Tổng tour (Blue) -->
      <div class="kpi-card kpi-blue">
        <div class="kpi-icon-wrap icon-blue">
          <i class="bi bi-compass"></i>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Tổng số tour</div>
          <div class="kpi-value">{{ formatNumber(totalTours) }}</div>
          <div class="kpi-trend trend-positive">
            <i class="bi bi-arrow-up-short"></i>
            <span>100% dữ liệu đồng bộ</span>
          </div>
        </div>
        <div class="kpi-bg-deco">
          <i class="bi bi-map"></i>
        </div>
      </div>

      <!-- KPI 2: Đang hoạt động (Green) -->
      <div class="kpi-card kpi-green">
        <div class="kpi-icon-wrap icon-green">
          <i class="bi bi-check2-circle"></i>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Đang hoạt động</div>
          <div class="kpi-value">{{ formatNumber(activeToursCount) }}</div>
          <div class="kpi-trend trend-positive">
            <i class="bi bi-check-circle-fill me-1"></i>
            <span>Mở bán & nhận booking</span>
          </div>
        </div>
        <div class="kpi-bg-deco">
          <i class="bi bi-airplane"></i>
        </div>
      </div>

      <!-- KPI 3: Tạm dừng / Ngừng (Orange) -->
      <div class="kpi-card kpi-orange">
        <div class="kpi-icon-wrap icon-orange">
          <i class="bi bi-pause-circle"></i>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Tạm dừng / Ngừng</div>
          <div class="kpi-value">{{ formatNumber(pausedToursCount) }}</div>
          <div class="kpi-trend" :class="pausedToursCount > 0 ? 'trend-warning' : 'trend-neutral'">
            <i :class="pausedToursCount > 0 ? 'bi bi-exclamation-circle' : 'bi bi-shield-check'"></i>
            <span>{{ pausedToursCount > 0 ? 'Cần rà soát lộ trình' : 'Tất cả tour ổn định' }}</span>
          </div>
        </div>
        <div class="kpi-bg-deco">
          <i class="bi bi-sliders"></i>
        </div>
      </div>

      <!-- KPI 4: Phân loại Tuyến (Purple) -->
      <div class="kpi-card kpi-purple">
        <div class="kpi-icon-wrap icon-purple">
          <i class="bi bi-globe-americas"></i>
        </div>
        <div class="kpi-body">
          <div class="kpi-label">Tuyến Trong nước / Quốc tế</div>
          <div class="kpi-value">{{ domesticToursCount }} <span class="kpi-slash">/</span> {{ internationalToursCount }}</div>
          <div class="kpi-trend trend-positive">
            <i class="bi bi-compass"></i>
            <span>Đa dạng vùng miền</span>
          </div>
        </div>
        <div class="kpi-bg-deco">
          <i class="bi bi-globe2"></i>
        </div>
      </div>
    </section>

    <!-- QUICK FILTER TABS (Modern Segmented Navigation) -->
    <section class="quick-filter-tabs-row">
      <div class="segmented-tabs">
        <button 
          class="seg-tab-btn" 
          :class="{ active: currentQuickTab === 'all' }"
          @click="setQuickTab('all')"
        >
          <i class="bi bi-grid-fill me-1"></i>
          <span>Tất cả</span>
          <span class="tab-badge">{{ totalTours }}</span>
        </button>

        <button 
          class="seg-tab-btn" 
          :class="{ active: currentQuickTab === 'domestic' }"
          @click="setQuickTab('domestic')"
        >
          <i class="bi bi-geo-alt-fill me-1"></i>
          <span>Trong nước</span>
          <span class="tab-badge">{{ domesticToursCount }}</span>
        </button>

        <button 
          class="seg-tab-btn" 
          :class="{ active: currentQuickTab === 'international' }"
          @click="setQuickTab('international')"
        >
          <i class="bi bi-airplane-fill me-1"></i>
          <span>Quốc tế</span>
          <span class="tab-badge">{{ internationalToursCount }}</span>
        </button>

        <button 
          class="seg-tab-btn" 
          :class="{ active: currentQuickTab === 'active' }"
          @click="setQuickTab('active')"
        >
          <i class="bi bi-check-circle-fill me-1 text-emerald"></i>
          <span>Đang hoạt động</span>
          <span class="tab-badge badge-emerald">{{ activeToursCount }}</span>
        </button>

        <button 
          class="seg-tab-btn" 
          :class="{ active: currentQuickTab === 'paused' }"
          @click="setQuickTab('paused')"
        >
          <i class="bi bi-pause-circle-fill me-1 text-amber"></i>
          <span>Tạm dừng</span>
          <span class="tab-badge badge-amber">{{ pausedToursCount }}</span>
        </button>
      </div>

      <div class="quick-right-actions">
        <button @click="fetchTours(1)" class="btn-glass-action" :class="{ rotating: isLoading }" title="Làm mới danh sách">
          <i class="bi bi-arrow-clockwise"></i>
          <span>Làm mới</span>
        </button>
      </div>
    </section>

    <!-- FILTER TOOLBAR (Modern Glass Card) -->
    <section class="filter-panel-card">
      <div class="filter-flex-row">
        <!-- Search Input with Shortcut Chip -->
        <div class="filter-group search-filter-group">
          <label class="filter-field-label">
            <i class="bi bi-search text-cyan"></i>
            <span>Tìm kiếm nhanh</span>
          </label>
          <div class="search-input-wrapper">
            <i class="bi bi-search search-lead-icon"></i>
            <input 
              ref="searchInputRef"
              type="text" 
              v-model="searchQuery" 
              @input="onSearchInput"
              placeholder="Nhập tên tour, mã tour hoặc điểm đến..." 
              class="search-input-field"
            />
            <span class="kbd-hint" v-if="!searchQuery">Ctrl + K</span>
            <button 
              v-if="searchQuery" 
              @click="clearSearch" 
              class="btn-clear-search" 
              title="Xóa tìm kiếm"
            >
              <i class="bi bi-x-lg"></i>
            </button>
          </div>
        </div>

        <!-- Tour Type Filter -->
        <div class="filter-group select-filter-group">
          <label class="filter-field-label">
            <i class="bi bi-globe2 text-gold"></i>
            <span>Loại tour</span>
          </label>
          <div class="select-wrapper">
            <select v-model="selectedType" @change="onSelectFilterChange" class="custom-select-field">
              <option value="">Tất cả loại tour</option>
              <option value="TrongNuoc">Trong nước</option>
              <option value="QuocTe">Quốc tế</option>
              <option value="TheoYeuCau">Theo yêu cầu</option>
            </select>
            <i class="bi bi-chevron-down select-chevron"></i>
          </div>
        </div>

        <!-- Status Filter -->
        <div class="filter-group select-filter-group">
          <label class="filter-field-label">
            <i class="bi bi-shield-check text-emerald"></i>
            <span>Trạng thái</span>
          </label>
          <div class="select-wrapper">
            <select v-model="selectedStatus" @change="onSelectFilterChange" class="custom-select-field">
              <option value="">Tất cả trạng thái</option>
              <option value="HoatDong">Đang hoạt động</option>
              <option value="TamDung">Ngừng hoạt động</option>
            </select>
            <i class="bi bi-chevron-down select-chevron"></i>
          </div>
        </div>

        <!-- Reset Button -->
        <div class="filter-group reset-filter-group">
          <button @click="resetFilters" class="btn-filter-reset" title="Khôi phục toàn bộ bộ lọc">
            <i class="bi bi-arrow-counterclockwise"></i>
            <span>Đặt lại bộ lọc</span>
          </button>
        </div>
      </div>
    </section>

    <!-- TOURS DATA TABLE (Ultra-Modern Glass Table matching Reference) -->
    <section class="table-container-card">
      <div class="table-top-meta">
        <div class="meta-indicator">
          <span class="live-dot-pulse"></span>
          <span>Hiển thị <strong>{{ tours.length }}</strong> trên tổng số <strong>{{ totalTours }}</strong> tour</span>
          <span v-if="isLoading" class="loading-tag">
            <i class="bi bi-arrow-repeat spin-fast"></i> Đang tải dữ liệu...
          </span>
        </div>

        <div class="meta-per-page">
          <label class="per-page-caption">Hiển thị:</label>
          <div class="per-page-select-wrapper">
            <select v-model="perPage" @change="fetchTours(1)" class="per-page-custom-select">
              <option :value="10">10 / trang</option>
              <option :value="20">20 / trang</option>
              <option :value="50">50 / trang</option>
            </select>
            <i class="bi bi-chevron-down per-page-chevron"></i>
          </div>
        </div>
      </div>

      <div class="table-scroll-wrapper">
        <table class="modern-tour-table">
          <thead>
            <tr>
              <th style="width: 80px;" class="col-center">ID</th>
              <th style="min-width: 280px;">Tên Tour & Lộ Trình</th>
              <th style="width: 150px;" class="col-center">Loại Tour</th>
              <th style="width: 170px;" class="col-right">Giá Cơ Bản</th>
              <th style="width: 150px;" class="col-center">Trạng Thái</th>
              <th style="width: 380px;" class="col-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tour in tours" :key="tour.tour_id" class="tour-data-row">
              <!-- ID -->
              <td class="col-center">
                <span class="tour-code-badge">#{{ tour.tour_id }}</span>
              </td>

              <!-- Name & Route -->
              <td class="tour-info-cell">
                <div class="tour-info-block">
                  <a :href="'index.php?act=admin/chiTietTour&id=' + tour.tour_id" class="tour-name-link" :title="tour.ten_tour">
                    {{ tour.ten_tour }}
                  </a>
                  <div class="tour-chips-row">
                    <span v-if="tour.diem_khoi_hanh" class="chip-item chip-departure" title="Điểm xuất phát">
                      <i class="bi bi-geo-alt-fill"></i>
                      <span>{{ tour.diem_khoi_hanh }}</span>
                    </span>
                    <span v-if="tour.thoi_gian_tour" class="chip-item chip-duration" title="Thời gian tour">
                      <i class="bi bi-clock-fill"></i>
                      <span>{{ tour.thoi_gian_tour }}</span>
                    </span>
                  </div>
                </div>
              </td>

              <!-- Type -->
              <td class="col-center">
                <span class="type-pill-badge" :class="getTypeBadgeClass(tour.loai_tour)">
                  <i class="bi" :class="getTypeIcon(tour.loai_tour)"></i>
                  <span>{{ formatLoaiTour(tour.loai_tour) }}</span>
                </span>
              </td>

              <!-- Price -->
              <td class="col-right">
                <div class="price-display-wrapper">
                  <span class="price-number">{{ formatCurrency(tour.gia_co_ban) }}</span>
                </div>
              </td>

              <!-- Status -->
              <td class="col-center">
                <span 
                  class="status-pill-badge" 
                  :class="tour.trang_thai === 'HoatDong' ? 'status-active' : 'status-paused'"
                >
                  <span class="status-indicator-dot"></span>
                  <span>{{ tour.trang_thai === 'HoatDong' ? 'Hoạt động' : 'Tạm dừng' }}</span>
                </span>
              </td>

              <!-- Actions -->
              <td class="col-center">
                <div class="action-btn-cluster">
                  <a 
                    :href="'index.php?act=tour/update&id=' + tour.tour_id" 
                    class="btn-act-chip btn-act-edit"
                    title="Chỉnh sửa thông tin tour"
                  >
                    <i class="bi bi-pencil-square"></i>
                    <span>Sửa</span>
                  </a>

                  <a 
                    :href="'index.php?act=admin/chiTietTour&id=' + tour.tour_id" 
                    class="btn-act-chip btn-act-detail"
                    title="Xem chi tiết hồ sơ tour"
                  >
                    <i class="bi bi-eye"></i>
                    <span>Chi tiết</span>
                  </a>

                  <a 
                    :href="'index.php?act=lichKhoiHanh/index&tour_id=' + tour.tour_id" 
                    class="btn-act-chip btn-act-schedule"
                    title="Quản lý lịch khởi hành tour này"
                  >
                    <i class="bi bi-calendar3"></i>
                    <span>Lịch</span>
                  </a>

                  <a 
                    :href="'index.php?act=tour/clone&id=' + tour.tour_id" 
                    class="btn-act-chip btn-act-clone"
                    @click="confirmClone($event, tour)"
                    title="Sao chép (clone) tour mới"
                  >
                    <i class="bi bi-copy"></i>
                    <span>Clone</span>
                  </a>

                  <button 
                    v-if="tour.qr_code_path" 
                    @click="openQrModal(tour)"
                    class="btn-act-chip btn-act-qr"
                    title="Xem mã QR thanh toán / tra cứu"
                  >
                    <i class="bi bi-qr-code"></i>
                    <span>QR</span>
                  </button>

                  <a 
                    v-else 
                    :href="'index.php?act=tour/generateQr&id=' + tour.tour_id"
                    class="btn-act-chip btn-act-qr-gen"
                    title="Tạo mã QR tra cứu"
                  >
                    <i class="bi bi-qr-code-scan"></i>
                    <span>Tạo QR</span>
                  </a>

                  <button 
                    @click="confirmDeleteTour(tour)" 
                    class="btn-act-chip btn-act-delete"
                    title="Xóa tour khỏi hệ thống"
                  >
                    <i class="bi bi-trash3"></i>
                    <span>Xóa</span>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="tours.length === 0 && !isLoading">
              <td colspan="6" class="empty-state-cell">
                <div class="empty-state-content">
                  <div class="empty-icon-box">
                    <i class="bi bi-search"></i>
                  </div>
                  <h4 class="empty-state-title">Không tìm thấy tour nào phù hợp</h4>
                  <p class="empty-state-desc">Hãy thử thay đổi từ khóa tìm kiếm hoặc chọn lại các bộ lọc bên trên.</p>
                  <button @click="resetFilters" class="btn-empty-reset">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Xóa bộ lọc & Tải lại
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION (Matching Reference Modern Bar) -->
      <div class="pagination-footer-bar" v-if="totalPages > 1">
        <div class="pagination-summary">
          Trang <strong>{{ currentPageNumber }}</strong> / <strong>{{ totalPages }}</strong>
        </div>
        <ul class="modern-pagination-list">
          <li :class="{ disabled: currentPageNumber <= 1 }">
            <button 
              @click="fetchTours(currentPageNumber - 1)" 
              :disabled="currentPageNumber <= 1"
              class="page-nav-btn"
              title="Trang trước"
            >
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>

          <li 
            v-for="page in visiblePages" 
            :key="page"
            :class="{ active: page === currentPageNumber }"
          >
            <button @click="fetchTours(page)" class="page-num-btn">{{ page }}</button>
          </li>

          <li :class="{ disabled: currentPageNumber >= totalPages }">
            <button 
              @click="fetchTours(currentPageNumber + 1)" 
              :disabled="currentPageNumber >= totalPages"
              class="page-nav-btn"
              title="Trang sau"
            >
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
        </ul>
      </div>
    </section>

    <!-- MODAL PREVIEW QR CODE -->
    <div v-if="activeQrTour" class="qr-modal-overlay" @click="closeQrModal">
      <div class="qr-modal-dialog" @click.stop>
        <div class="qr-modal-header">
          <div class="qr-modal-title-wrap">
            <i class="bi bi-qr-code text-cyan me-2"></i>
            <h5 class="qr-modal-title">Mã QR Tra Cứu Tour #{{ activeQrTour.tour_id }}</h5>
          </div>
          <button class="btn-close-qr" @click="closeQrModal">&times;</button>
        </div>
        <div class="qr-modal-body">
          <h6 class="qr-modal-tour-name">{{ activeQrTour.ten_tour }}</h6>
          <div class="qr-img-frame">
            <img :src="activeQrTour.qr_url" alt="QR Code" class="qr-img-asset" />
          </div>
          <p class="qr-help-note">
            <i class="bi bi-info-circle me-1"></i> Khách hàng hoặc điều hành có thể quét mã QR để mở trang tra cứu hoặc đặt tour trực tuyến.
          </p>
        </div>
        <div class="qr-modal-footer">
          <a :href="activeQrTour.qr_url" download class="btn-download-qr">
            <i class="bi bi-download me-1"></i> Tải ảnh QR
          </a>
          <button class="btn-close-qr-footer" @click="closeQrModal">Đóng</button>
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
const currentQuickTab = ref('all');
const searchInputRef = ref(null);

// Modals & Deletion
const activeQrTour = ref(null);
const deleteTourId = ref(null);
const deleteFormRef = ref(null);

let searchTimeout = null;

// Date formatter for header
const currentDateFormatted = computed(() => {
  const now = new Date();
  const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
  const dayName = days[now.getDay()];
  const date = now.getDate();
  const month = now.getMonth() + 1;
  const year = now.getFullYear();
  return `${dayName}, ${date} thg ${month}, ${year}`;
});

// Stats calculations
const activeToursCount = computed(() => {
  return tours.value.filter(t => t.trang_thai === 'HoatDong').length;
});
const pausedToursCount = computed(() => {
  return tours.value.filter(t => t.trang_thai === 'TamDung').length;
});
const domesticToursCount = computed(() => {
  return tours.value.filter(t => t.loai_tour === 'TrongNuoc').length;
});
const internationalToursCount = computed(() => {
  return tours.value.filter(t => t.loai_tour === 'QuocTe').length;
});

// Quick tabs helper
function setQuickTab(tab) {
  currentQuickTab.value = tab;
  if (tab === 'all') {
    selectedType.value = '';
    selectedStatus.value = '';
  } else if (tab === 'domestic') {
    selectedType.value = 'TrongNuoc';
    selectedStatus.value = '';
  } else if (tab === 'international') {
    selectedType.value = 'QuocTe';
    selectedStatus.value = '';
  } else if (tab === 'active') {
    selectedStatus.value = 'HoatDong';
    selectedType.value = '';
  } else if (tab === 'paused') {
    selectedStatus.value = 'TamDung';
    selectedType.value = '';
  }
  fetchTours(1);
}

function onSelectFilterChange() {
  currentQuickTab.value = 'custom';
  fetchTours(1);
}

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
  currentQuickTab.value = 'all';
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
    case 'TrongNuoc': return 'badge-type-domestic';
    case 'QuocTe': return 'badge-type-international';
    case 'TheoYeuCau': return 'badge-type-custom';
    default: return 'badge-type-domestic';
  }
}

function getTypeIcon(type) {
  switch (type) {
    case 'TrongNuoc': return 'bi-geo-alt-fill';
    case 'QuocTe': return 'bi-airplane-fill';
    case 'TheoYeuCau': return 'bi-stars';
    default: return 'bi-map-fill';
  }
}

// Keyboard shortcuts (Ctrl+K for search)
onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchTours(1);
  }

  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      if (searchInputRef.value) {
        searchInputRef.value.focus();
      }
    }
  });
});
</script>

<style scoped>
/* -------------------------------------------------------------
   MAIN CONTAINER - LUXURY MIDNIGHT BLUE PALETTE (Matching Photo 2)
   ------------------------------------------------------------- */
.vue-admin-tour-list {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  padding: 0.25rem 0 2.5rem;
  font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f1f5f9;
}

/* -------------------------------------------------------------
   1. HERO HEADER BANNER (Landscape Glass & Gradient)
   ------------------------------------------------------------- */
.tour-hero-banner {
  position: relative;
  background: linear-gradient(135deg, #0f172a 0%, #111d38 50%, #0b1328 100%);
  border: 1px solid rgba(255, 255, 255, 0.09);
  border-radius: 1.15rem;
  padding: 1.75rem 2rem;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.45);
  overflow: hidden;
}

.tour-hero-banner::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 50%;
  height: 100%;
  background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent 70%);
  pointer-events: none;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><path d="M0 0h80v80H0z" fill="none"/><path d="M0 40h80M40 0v80" stroke="rgba(255,255,255,0.015)" stroke-width="1"/></svg>') repeat;
  opacity: 0.7;
  pointer-events: none;
}

.hero-content {
  position: relative;
  z-index: 2;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.hero-left {
  max-width: 680px;
}

.kicker-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.28rem 0.75rem;
  border-radius: 999px;
  background: rgba(223, 169, 116, 0.14);
  border: 1px solid rgba(223, 169, 116, 0.35);
  color: #dfa974;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 0.65rem;
}

.hero-title {
  font-size: 1.85rem;
  font-weight: 800;
  color: #ffffff;
  margin: 0 0 0.45rem;
  letter-spacing: -0.02em;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 0.92rem;
  color: #94a3b8;
  margin: 0;
  line-height: 1.5;
}

.hero-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.85rem;
}

.hero-live-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.4rem 0.95rem;
  border-radius: 999px;
  background: rgba(11, 19, 38, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
  font-size: 0.82rem;
  color: #cbd5e1;
}

.live-dot-green {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.25);
}

.live-badge-status {
  background: rgba(59, 130, 246, 0.2);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
  padding: 0.1rem 0.45rem;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.72rem;
}

.btn-hero-create {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  background: linear-gradient(135deg, #dfa974 0%, #b8860b 100%);
  color: #090e1a;
  padding: 0.72rem 1.45rem;
  border-radius: 0.65rem;
  font-size: 0.95rem;
  font-weight: 800;
  text-decoration: none;
  box-shadow: 0 6px 18px rgba(223, 169, 116, 0.35);
  transition: all 0.25s ease;
  border: none;
  letter-spacing: 0.02em;
}

.btn-hero-create:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 26px rgba(223, 169, 116, 0.45);
  color: #050811;
}

/* -------------------------------------------------------------
   2. 4 KPI STATS METRIC CARDS (Exact Match to Photo 2)
   ------------------------------------------------------------- */
.kpi-metric-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.15rem;
}

@media (max-width: 1200px) {
  .kpi-metric-grid { grid-template-columns: repeat(2, 1fr); }
}

@media (max-width: 640px) {
  .kpi-metric-grid { grid-template-columns: 1fr; }
}

.kpi-card {
  position: relative;
  background: #111a2f;
  background: linear-gradient(145deg, #13203c 0%, #0d1629 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 1.25rem 1.35rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
  display: flex;
  align-items: flex-start;
  gap: 1.1rem;
  overflow: hidden;
  transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}

.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 32px rgba(0, 0, 0, 0.45);
  border-color: rgba(255, 255, 255, 0.16);
}

.kpi-icon-wrap {
  width: 48px;
  height: 48px;
  min-width: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
}

.icon-blue { background: rgba(59, 130, 246, 0.18); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.35); }
.icon-green { background: rgba(16, 185, 129, 0.18); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.35); }
.icon-orange { background: rgba(245, 158, 11, 0.18); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }
.icon-purple { background: rgba(168, 85, 247, 0.18); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.35); }

.kpi-body {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
  flex: 1;
}

.kpi-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: #94a3b8;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.kpi-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #ffffff;
  line-height: 1.15;
  letter-spacing: -0.02em;
}

.kpi-slash {
  font-size: 1.25rem;
  color: #64748b;
  font-weight: 400;
}

.kpi-trend {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.74rem;
  font-weight: 600;
  margin-top: 0.35rem;
}

.trend-positive { color: #34d399; }
.trend-warning { color: #f87171; }
.trend-neutral { color: #94a3b8; }

.kpi-bg-deco {
  position: absolute;
  right: -8px;
  bottom: -10px;
  font-size: 4.5rem;
  opacity: 0.04;
  color: #ffffff;
  pointer-events: none;
  user-select: none;
}

/* -------------------------------------------------------------
   3. QUICK FILTER TABS (Segmented bar)
   ------------------------------------------------------------- */
.quick-filter-tabs-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
}

.segmented-tabs {
  display: inline-flex;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 4px;
  border-radius: 12px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
  gap: 4px;
}

.seg-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.5rem 0.95rem;
  border-radius: 8px;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.seg-tab-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.05);
}

.seg-tab-btn.active {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.tab-badge {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 1px 6px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
}

.badge-emerald { background: rgba(16, 185, 129, 0.2); color: #34d399; }
.badge-amber { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }

.btn-glass-action {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.55rem 1rem;
  border-radius: 10px;
  background: rgba(22, 32, 54, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-glass-action:hover {
  background: rgba(30, 41, 68, 0.95);
  border-color: rgba(223, 169, 116, 0.4);
  color: #f1f5f9;
}

.rotating i {
  animation: spin 0.8s linear infinite;
}

/* -------------------------------------------------------------
   4. FILTER TOOLBAR (Modern Glass Card)
   ------------------------------------------------------------- */
.filter-panel-card {
  background: #101a30;
  background: linear-gradient(145deg, #132242 0%, #0c1527 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1rem;
  padding: 1.15rem 1.35rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
}

.filter-flex-row {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.search-filter-group {
  flex: 2;
  min-width: 260px;
}

.select-filter-group {
  flex: 1;
  min-width: 170px;
}

.reset-filter-group {
  display: flex;
  align-items: flex-end;
}

.filter-field-label {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.78rem;
  font-weight: 700;
  color: #cbd5e1;
  letter-spacing: 0.03em;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-lead-icon {
  position: absolute;
  left: 0.95rem;
  color: #64748b;
  font-size: 0.95rem;
  pointer-events: none;
}

.search-input-field {
  width: 100%;
  padding: 0.65rem 5rem 0.65rem 2.4rem;
  border-radius: 10px;
  background: rgba(11, 18, 35, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.2s ease;
}

.search-input-field:focus {
  border-color: #3b82f6;
  background: rgba(11, 18, 35, 0.98);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.search-input-field::placeholder {
  color: #64748b;
}

.kbd-hint {
  position: absolute;
  right: 0.75rem;
  font-size: 0.7rem;
  font-weight: 700;
  color: #64748b;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 2px 6px;
  border-radius: 5px;
  pointer-events: none;
}

.btn-clear-search {
  position: absolute;
  right: 0.75rem;
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 0.85rem;
  cursor: pointer;
  padding: 4px;
}

.btn-clear-search:hover {
  color: #ffffff;
}

.select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.custom-select-field {
  width: 100%;
  appearance: none;
  -webkit-appearance: none;
  padding: 0.65rem 2rem 0.65rem 0.95rem;
  border-radius: 10px;
  background: rgba(11, 18, 35, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #ffffff;
  font-size: 0.9rem;
  outline: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.custom-select-field:focus {
  border-color: #3b82f6;
  background: rgba(11, 18, 35, 0.98);
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.select-chevron {
  position: absolute;
  right: 0.85rem;
  color: #64748b;
  font-size: 0.8rem;
  pointer-events: none;
}

.btn-filter-reset {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.15rem;
  border-radius: 10px;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-filter-reset:hover {
  background: rgba(223, 169, 116, 0.18);
  border-color: #dfa974;
  color: #dfa974;
}

/* -------------------------------------------------------------
   5. TOURS DATA TABLE (Ultra-Modern Glass Table)
   ------------------------------------------------------------- */
.table-container-card {
  background: #101a30;
  background: linear-gradient(145deg, #132242 0%, #0c1527 100%);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 1.15rem;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.35);
  overflow: hidden;
}

.table-top-meta {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(11, 18, 35, 0.95);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.88rem;
  color: #cbd5e1;
}

.meta-indicator {
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
}

.live-dot-pulse {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
}

.loading-tag {
  margin-left: 0.75rem;
  color: #38bdf8;
  font-weight: 600;
  font-size: 0.82rem;
}

.spin-fast {
  display: inline-block;
  animation: spin 0.7s linear infinite;
}

.meta-per-page {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.per-page-caption {
  font-size: 0.82rem;
  color: #94a3b8;
}

.per-page-select-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.per-page-custom-select {
  appearance: none;
  -webkit-appearance: none;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 7px;
  padding: 0.3rem 1.6rem 0.3rem 0.65rem;
  color: #ffffff;
  font-size: 0.82rem;
  outline: none;
  cursor: pointer;
}

.per-page-chevron {
  position: absolute;
  right: 0.5rem;
  font-size: 0.65rem;
  color: #94a3b8;
  pointer-events: none;
}

.table-scroll-wrapper {
  overflow-x: auto;
}

.modern-tour-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.9rem;
}

.modern-tour-table th {
  padding: 0.95rem 1.25rem;
  background: #0d1629;
  color: #94a3b8;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  white-space: nowrap;
}

.modern-tour-table td {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
  transition: background 0.18s ease;
}

.tour-data-row:hover td {
  background: rgba(255, 255, 255, 0.035);
}

.col-center { text-align: center; }
.col-right { text-align: right; }

.tour-code-badge {
  display: inline-block;
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
  border: 1px solid rgba(59, 130, 246, 0.3);
  font-size: 0.8rem;
  font-weight: 800;
  padding: 0.25rem 0.6rem;
  border-radius: 7px;
  font-family: monospace, sans-serif;
}

.tour-info-cell {
  max-width: 420px;
}

.tour-info-block {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.tour-name-link {
  font-size: 0.96rem;
  font-weight: 700;
  color: #ffffff;
  text-decoration: none;
  line-height: 1.35;
  transition: color 0.2s ease;
}

.tour-name-link:hover {
  color: #dfa974;
}

.tour-chips-row {
  display: flex;
  gap: 0.65rem;
  align-items: center;
  flex-wrap: wrap;
}

.chip-item {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.78rem;
  color: #94a3b8;
}

.chip-departure {
  color: #94a3b8;
}

.chip-departure i {
  color: #38bdf8;
  font-size: 0.8rem;
}

.chip-duration {
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  padding: 0.12rem 0.5rem;
  border-radius: 5px;
}

.chip-duration i {
  color: #dfa974;
}

/* TYPE PILL BADGES */
.type-pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.32rem 0.8rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}

.badge-type-domestic {
  background: rgba(59, 130, 246, 0.16);
  color: #93c5fd;
  border: 1px solid rgba(59, 130, 246, 0.35);
}

.badge-type-international {
  background: rgba(16, 185, 129, 0.16);
  color: #6ee7b7;
  border: 1px solid rgba(16, 185, 129, 0.35);
}

.badge-type-custom {
  background: rgba(168, 85, 247, 0.16);
  color: #d8b4fe;
  border: 1px solid rgba(168, 85, 247, 0.35);
}

/* PRICE DISPLAY */
.price-display-wrapper {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.price-number {
  font-size: 1.05rem;
  font-weight: 800;
  color: #34d399;
  letter-spacing: -0.01em;
}

/* STATUS PILL BADGE */
.status-pill-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.28rem 0.8rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}

.status-active {
  background: rgba(16, 185, 129, 0.16);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.35);
}

.status-paused {
  background: rgba(239, 68, 68, 0.16);
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.35);
}

.status-indicator-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: currentColor;
}

/* ACTION BUTTONS CLUSTER */
.action-btn-cluster {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  flex-wrap: wrap;
  justify-content: center;
}

.btn-act-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.28rem;
  padding: 0.35rem 0.65rem;
  border-radius: 7px;
  font-size: 0.78rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  border: 1px solid transparent;
  transition: all 0.18s ease;
  white-space: nowrap;
}

.btn-act-edit {
  background: rgba(59, 130, 246, 0.12);
  color: #93c5fd;
  border-color: rgba(59, 130, 246, 0.25);
}
.btn-act-edit:hover {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
  transform: translateY(-1px);
}

.btn-act-detail {
  background: rgba(14, 165, 233, 0.12);
  color: #7dd3fc;
  border-color: rgba(14, 165, 233, 0.25);
}
.btn-act-detail:hover {
  background: #0284c7;
  color: #ffffff;
  border-color: #0284c7;
  transform: translateY(-1px);
}

.btn-act-schedule {
  background: rgba(223, 169, 116, 0.15);
  color: #fde047;
  border-color: rgba(223, 169, 116, 0.3);
}
.btn-act-schedule:hover {
  background: #d4af37;
  color: #0b1120;
  border-color: #d4af37;
  transform: translateY(-1px);
}

.btn-act-clone {
  background: rgba(168, 85, 247, 0.12);
  color: #d8b4fe;
  border-color: rgba(168, 85, 247, 0.25);
}
.btn-act-clone:hover {
  background: #9333ea;
  color: #ffffff;
  border-color: #9333ea;
  transform: translateY(-1px);
}

.btn-act-qr,
.btn-act-qr-gen {
  background: rgba(20, 184, 166, 0.12);
  color: #5eead4;
  border-color: rgba(20, 184, 166, 0.25);
}
.btn-act-qr:hover,
.btn-act-qr-gen:hover {
  background: #0d9488;
  color: #ffffff;
  border-color: #0d9488;
  transform: translateY(-1px);
}

.btn-act-delete {
  background: rgba(239, 68, 68, 0.12);
  color: #fca5a5;
  border-color: rgba(239, 68, 68, 0.25);
}
.btn-act-delete:hover {
  background: #dc2626;
  color: #ffffff;
  border-color: #dc2626;
  transform: translateY(-1px);
}

/* EMPTY STATE */
.empty-state-cell {
  padding: 4rem 1.5rem !important;
  text-align: center;
}

.empty-state-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  max-width: 400px;
  margin: 0 auto;
}

.empty-icon-box {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #64748b;
  margin-bottom: 1rem;
}

.empty-state-title {
  font-size: 1.15rem;
  font-weight: 700;
  color: #f1f5f9;
  margin: 0 0 0.35rem;
}

.empty-state-desc {
  font-size: 0.88rem;
  color: #94a3b8;
  margin: 0 0 1.25rem;
  line-height: 1.5;
}

.btn-empty-reset {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.6rem 1.2rem;
  border-radius: 9px;
  background: linear-gradient(135deg, #dfa974 0%, #b8860b 100%);
  color: #0b1120;
  font-weight: 700;
  font-size: 0.88rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(223, 169, 116, 0.3);
}

/* PAGINATION FOOTER */
.pagination-footer-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.5rem;
  background: rgba(11, 18, 35, 0.95);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  flex-wrap: wrap;
  gap: 1rem;
}

.pagination-summary {
  font-size: 0.85rem;
  color: #94a3b8;
}

.modern-pagination-list {
  display: flex;
  gap: 0.35rem;
  list-style: none;
  margin: 0;
  padding: 0;
}

.page-num-btn,
.page-nav-btn {
  min-width: 36px;
  height: 36px;
  padding: 0 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #cbd5e1;
  cursor: pointer;
  transition: all 0.18s ease;
}

.page-num-btn:hover:not(:disabled),
.page-nav-btn:hover:not(:disabled) {
  background: rgba(59, 130, 246, 0.25);
  border-color: #3b82f6;
  color: #ffffff;
}

.modern-pagination-list li.active .page-num-btn {
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: #ffffff;
  border-color: #3b82f6;
  font-weight: 700;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.modern-pagination-list li.disabled .page-nav-btn {
  opacity: 0.35;
  cursor: not-allowed;
}

/* -------------------------------------------------------------
   6. QR MODAL DIALOG
   ------------------------------------------------------------- */
.qr-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(7, 12, 23, 0.85);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.qr-modal-dialog {
  background: #111d38;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 1.25rem;
  width: 90%;
  max-width: 440px;
  box-shadow: 0 24px 60px rgba(0, 0, 0, 0.6);
  overflow: hidden;
  animation: modalIn 0.22s ease-out;
}

.qr-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: #0d1629;
}

.qr-modal-title-wrap {
  display: flex;
  align-items: center;
}

.qr-modal-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #ffffff;
}

.btn-close-qr {
  background: transparent;
  border: none;
  font-size: 1.4rem;
  line-height: 1;
  color: #94a3b8;
  cursor: pointer;
}
.btn-close-qr:hover { color: #ffffff; }

.qr-modal-body {
  padding: 1.5rem;
  text-align: center;
}

.qr-modal-tour-name {
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 1.25rem;
  line-height: 1.4;
}

.qr-img-frame {
  display: inline-block;
  padding: 0.65rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
}

.qr-img-asset {
  width: 200px;
  height: 200px;
  object-fit: contain;
  display: block;
}

.qr-help-note {
  font-size: 0.8rem;
  color: #94a3b8;
  margin: 1.15rem 0 0;
  line-height: 1.4;
}

.qr-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  background: #0d1629;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.btn-download-qr {
  display: inline-flex;
  align-items: center;
  padding: 0.55rem 1rem;
  border-radius: 8px;
  background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
}

.btn-close-qr-footer {
  padding: 0.55rem 1rem;
  border-radius: 8px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-weight: 600;
  font-size: 0.85rem;
  cursor: pointer;
}

@keyframes spin { 100% { transform: rotate(360deg); } }
@keyframes modalIn {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
