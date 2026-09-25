<template>
  <div class="vue-admin-personnel-manage">
    <!-- 1. HERO BANNER WITH TRAVEL BACKGROUND (MATCHING IMAGE 2) -->
    <header 
      class="hero-banner"
      :style="{
        backgroundImage: `linear-gradient(90deg, #ffffff 0%, rgba(255, 255, 255, 0.95) 45%, rgba(255, 255, 255, 0.25) 100%), url('${bannerUrl}')`
      }"
    >
      <div class="hero-content">
        <div class="hero-left">
          <div class="kicker-pill">
            <span>'TỔNG QUAN HỆ THỐNG</span>
          </div>
          <h1 class="hero-title">Chào mừng trở lại!</h1>
          <p class="hero-subtitle">
            Quản lý và theo dõi hoạt động của hệ thống du lịch một cách dễ dàng và hiệu quả.
          </p>
        </div>

        <div class="hero-right">
          <!-- Realtime Date & Clock Badge -->
          <div class="live-clock-badge">
            <i class="bi bi-calendar3 me-1"></i>
            <span>{{ currentDateTimeFormatted }}</span>
          </div>

          <!-- Add Personnel Button -->
          <button @click="openCreateModal" class="btn-create-personnel">
            <i class="bi bi-plus-lg"></i>
            <span>Thêm nhân sự</span>
          </button>
        </div>
      </div>
    </header>

    <!-- 2. 4 KPI STATS METRIC CARDS (MATCHING IMAGE 2) -->
    <section class="stats-metric-grid">
      <!-- Card 1: Tổng nhân sự -->
      <div class="stat-card">
        <div class="stat-icon-wrap bg-blue-subtle text-primary">
          <i class="bi bi-people"></i>
        </div>
        <div class="stat-main">
          <span class="stat-label">Tổng nhân sự</span>
          <div class="stat-value">{{ stats.total }}</div>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-right"></i>
            <span>+33% so với tháng trước</span>
          </div>
          <div class="stat-meta">
            Đang hoạt động: {{ stats.active }} | Ngừng hoạt động: {{ stats.inactive }}
          </div>
        </div>
      </div>

      <!-- Card 2: Hướng dẫn viên -->
      <div class="stat-card">
        <div class="stat-icon-wrap bg-blue-subtle text-primary">
          <i class="bi bi-person-fill"></i>
        </div>
        <div class="stat-main">
          <span class="stat-label">Hướng dẫn viên</span>
          <div class="stat-value">{{ stats.hdv }}</div>
          <div class="stat-trend trend-positive">
            <i class="bi bi-arrow-up-right"></i>
            <span>+33% so với tháng trước</span>
          </div>
          <div class="stat-meta">
            Đang hoạt động: {{ stats.hdvActive }} | Ngừng hoạt động: {{ stats.hdvInactive }}
          </div>
        </div>
      </div>

      <!-- Card 3: Điều hành tour -->
      <div class="stat-card">
        <div class="stat-icon-wrap bg-purple-subtle text-purple">
          <i class="bi bi-briefcase-fill"></i>
        </div>
        <div class="stat-main">
          <span class="stat-label">Điều hành tour</span>
          <div class="stat-value">{{ stats.dieuHanh }}</div>
          <div class="stat-trend trend-neutral">
            <span>* Không thay đổi</span>
          </div>
          <div class="stat-meta">
            Đang hoạt động: {{ stats.dieuHanhActive }} | Ngừng hoạt động: {{ stats.dieuHanhInactive }}
          </div>
        </div>
      </div>

      <!-- Card 4: Nhà cung cấp -->
      <div class="stat-card">
        <div class="stat-icon-wrap bg-cyan-subtle text-cyan">
          <i class="bi bi-building"></i>
        </div>
        <div class="stat-main">
          <span class="stat-label">Nhà cung cấp</span>
          <div class="stat-value">{{ stats.nhaCungCap }}</div>
          <div class="stat-trend trend-neutral">
            <span>* Không thay đổi</span>
          </div>
          <div class="stat-meta">
            Đang hoạt động: {{ stats.nhaCungCapActive }} | Ngừng hoạt động: {{ stats.nhaCungCapInactive }}
          </div>
        </div>
      </div>
    </section>

    <!-- 3. TWO-COLUMN LAYOUT: CONTENT TABLE (LEFT) + SIDEBAR WIDGETS (RIGHT) -->
    <div class="layout-grid">
      <!-- LEFT COLUMN (72%): FILTERS + RECENT PERSONNEL TABLE -->
      <div class="left-column">
        <!-- FILTER TOOLBAR -->
        <section class="filter-strip-card">
          <!-- Search box -->
          <div class="filter-search-box">
            <i class="bi bi-search search-icon"></i>
            <input 
              v-model="searchQuery" 
              @input="onSearchInput"
              type="text" 
              class="filter-search-input" 
              placeholder="Tìm theo tên, email, số điện thoại, vai trò..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-input" title="Xóa tìm kiếm">
              <i class="bi bi-x"></i>
            </button>
          </div>

          <!-- Role Select -->
          <div class="filter-field-box">
            <label class="field-title">Vai trò</label>
            <select v-model="filterRole" class="filter-select">
              <option value="">Tất cả vai trò</option>
              <option value="HDV">Hướng dẫn viên</option>
              <option value="DieuHanh">Điều hành tour</option>
              <option value="NhaCungCap">Nhà cung cấp</option>
              <option value="Khac">Khác</option>
            </select>
          </div>

          <!-- Status Select -->
          <div class="filter-field-box">
            <label class="field-title">Trạng thái</label>
            <select v-model="filterStatus" class="filter-select">
              <option value="">Tất cả trạng thái</option>
              <option value="HoatDong">Đang hoạt động</option>
              <option value="NgungHoatDong">Ngừng hoạt động</option>
            </select>
          </div>

          <!-- Month Period Picker -->
          <div class="filter-field-box">
            <label class="field-title">Kỳ tháng</label>
            <div class="period-input-wrap">
              <i class="bi bi-calendar3 input-cal-icon"></i>
              <select v-model="filterPeriod" class="filter-select select-period">
                <option value="all">Tất cả kỳ</option>
                <option value="9/2026">Tháng 9/2026</option>
                <option value="8/2026">Tháng 8/2026</option>
                <option value="7/2026">Tháng 7/2026</option>
              </select>
            </div>
          </div>

          <!-- Filter Action Buttons -->
          <div class="filter-actions-box">
            <button @click="applyFilter" class="btn-filter-submit">
              <i class="bi bi-funnel-fill me-1"></i> Lọc
            </button>
            <button @click="resetFilters" class="btn-filter-reset" title="Khôi phục mặc định">
              <i class="bi bi-arrow-clockwise"></i>
            </button>
          </div>
        </section>

        <!-- RECENT PERSONNEL TABLE CARD -->
        <section class="table-card">
          <!-- Card Header -->
          <div class="table-card-header">
            <div class="header-title-group">
              <div class="header-icon-circle">
                <i class="bi bi-people"></i>
              </div>
              <h3 class="card-heading">Danh sách nhân sự gần đây</h3>
            </div>
            <button @click="resetFilters" class="link-view-all">
              <span>Xem tất cả</span>
              <i class="bi bi-arrow-right ms-1"></i>
            </button>
          </div>

          <!-- Table Content -->
          <div class="table-responsive">
            <table class="personnel-data-table">
              <thead>
                <tr>
                  <th style="width: 50px;" class="text-center">#</th>
                  <th>Họ và tên</th>
                  <th style="width: 140px;" class="text-center">Vai trò</th>
                  <th>Email</th>
                  <th>Số điện thoại</th>
                  <th style="width: 120px;" class="text-center">Ngày tham gia</th>
                  <th style="width: 140px;" class="text-center">Trạng thái</th>
                  <th style="width: 120px;" class="text-center">Thao tác</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(ns, index) in paginatedList" :key="ns.nhan_su_id" class="table-data-row">
                  <!-- Index -->
                  <td class="text-center text-muted font-monospace">
                    {{ (currentPage - 1) * pageSize + index + 1 }}
                  </td>

                  <!-- Name with Avatar -->
                  <td>
                    <div class="user-profile-cell">
                      <div class="avatar-photo-wrap">
                        <img 
                          v-if="ns.avatar" 
                          :src="getAvatarUrl(ns.avatar)" 
                          :alt="ns.ho_ten" 
                          class="avatar-photo"
                        />
                        <div v-else class="avatar-svg-fallback">
                          <svg width="38" height="38" viewBox="0 0 38 38" fill="none">
                            <circle cx="19" cy="19" r="19" :fill="getAvatarBg(index)"/>
                            <path d="M9 34c0-5.5 4.5-10 10-10s10 4.5 10 10" :fill="getAvatarShirt(index)"/>
                            <ellipse cx="19" cy="16" rx="6.5" ry="7.5" fill="#fcd9bd"/>
                            <path d="M12.5 14c0-4.5 3-8 6.5-8s6.5 3.5 6.5 8c-1.5-1-3-1.2-6.5-0.6-2 0.4-4 0-6.5 0.6z" fill="#1e293b"/>
                          </svg>
                        </div>
                      </div>
                      <span class="user-fullname">{{ ns.ho_ten }}</span>
                    </div>
                  </td>

                  <!-- Role Badge -->
                  <td class="text-center">
                    <span class="role-pill" :class="getRoleClass(ns.vai_tro)">
                      {{ formatRole(ns.vai_tro) }}
                    </span>
                  </td>

                  <!-- Email -->
                  <td class="text-secondary font-monospace text-nowrap">
                    {{ ns.email || 'Chưa cập nhật' }}
                  </td>

                  <!-- Phone -->
                  <td class="text-secondary font-monospace text-nowrap">
                    {{ ns.so_dien_thoai || 'Chưa cập nhật' }}
                  </td>

                  <!-- Join Date -->
                  <td class="text-center text-muted text-nowrap">
                    {{ formatDate(ns.ngay_tao) }}
                  </td>

                  <!-- Status -->
                  <td class="text-center">
                    <span class="status-pill" :class="isPersonActive(ns) ? 'status-active' : 'status-inactive'">
                      {{ isPersonActive(ns) ? 'Đang hoạt động' : 'Ngừng hoạt động' }}
                    </span>
                  </td>

                  <!-- Actions -->
                  <td class="text-center">
                    <div class="row-actions-group">
                      <!-- View Detail -->
                      <a 
                        :href="'index.php?act=admin/nhanSu_chi_tiet&id=' + ns.nhan_su_id" 
                        class="btn-icon-square" 
                        title="Xem chi tiết lý lịch"
                      >
                        <i class="bi bi-eye"></i>
                      </a>

                      <!-- Edit -->
                      <button 
                        @click="openEditModal(ns)" 
                        class="btn-icon-square" 
                        title="Chỉnh sửa thông tin"
                      >
                        <i class="bi bi-pencil"></i>
                      </button>

                      <!-- More actions menu -->
                      <div class="dropdown-action-wrap">
                        <button 
                          @click="toggleRowMenu(ns.nhan_su_id)" 
                          class="btn-icon-square" 
                          title="Tùy chọn khác"
                        >
                          <i class="bi bi-three-dots"></i>
                        </button>
                        <div v-if="activeRowMenuId === ns.nhan_su_id" class="row-popup-menu">
                          <a 
                            :href="'index.php?act=admin/chiTietLuong&nhan_su_id=' + ns.nhan_su_id" 
                            class="popup-menu-item"
                          >
                            <i class="bi bi-cash-stack me-2 text-primary"></i> Bảng lương chi tiết
                          </a>
                          <form 
                            method="POST" 
                            action="index.php?act=admin/nhanSu_delete" 
                            @submit="confirmDelete($event, ns)"
                          >
                            <input type="hidden" name="_csrf_token" :value="csrfToken">
                            <input type="hidden" name="id" :value="ns.nhan_su_id">
                            <button type="submit" class="popup-menu-item text-danger">
                              <i class="bi bi-trash me-2"></i> Xóa nhân sự
                            </button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>

                <!-- Empty State Row -->
                <tr v-if="filteredList.length === 0">
                  <td colspan="8" class="empty-state-cell">
                    <div class="empty-inner">
                      <i class="bi bi-people empty-icon"></i>
                      <h4 class="empty-title">Không tìm thấy nhân sự phù hợp</h4>
                      <p class="empty-sub">Hãy thử điều chỉnh lại bộ lọc tìm kiếm hoặc từ khóa.</p>
                      <button @click="resetFilters" class="btn-empty-reset">
                        <i class="bi bi-arrow-clockwise me-1"></i> Khôi phục bộ lọc
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Table Footer Pagination -->
          <div class="table-card-footer" v-if="filteredList.length > 0">
            <span class="pagination-info">
              Hiển thị {{ paginationStart }} - {{ paginationEnd }} trong tổng số {{ filteredList.length }} nhân sự
            </span>

            <div class="pagination-controls" v-if="totalPages > 1">
              <button 
                class="btn-page" 
                :disabled="currentPage === 1" 
                @click="currentPage--"
              >
                <i class="bi bi-chevron-left"></i>
              </button>

              <button 
                v-for="p in totalPages" 
                :key="p" 
                class="btn-page" 
                :class="{ active: p === currentPage }"
                @click="currentPage = p"
              >
                {{ p }}
              </button>

              <button 
                class="btn-page" 
                :disabled="currentPage === totalPages" 
                @click="currentPage++"
              >
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>
          </div>
        </section>
      </div>

      <!-- RIGHT COLUMN (28%): INFO WIDGETS (MATCHING IMAGE 2) -->
      <aside class="right-column">
        <!-- WIDGET 1: Quản lý nhân sự hiệu quả -->
        <div class="info-guide-card">
          <!-- Illustration graphic with 3 team avatars -->
          <div class="guide-illustration-wrap">
            <div class="team-avatars-cluster">
              <div class="team-avatar av-left">
                <svg width="42" height="42" viewBox="0 0 42 42" fill="none">
                  <circle cx="21" cy="21" r="21" fill="#e0f2fe"/>
                  <path d="M10 38c0-6 5-11 11-11s11 5 11 11" fill="#38bdf8"/>
                  <ellipse cx="21" cy="18" rx="7" ry="8" fill="#fcd9bd"/>
                  <path d="M14 16c0-5 3.5-9 7-9s7 4 7 9c-2-1-3-1-7-0.5-2 0.3-4 0-7 0.5z" fill="#334155"/>
                </svg>
              </div>
              <div class="team-avatar av-center">
                <svg width="50" height="50" viewBox="0 0 50 50" fill="none">
                  <circle cx="25" cy="25" r="25" fill="#dbeafe"/>
                  <path d="M12 45c0-7 6-13 13-13s13 6 13 13" fill="#2563eb"/>
                  <ellipse cx="25" cy="21" rx="8" ry="9.5" fill="#fcd9bd"/>
                  <path d="M16 19c0-6 4-10 9-10s9 4 9 10c-2.5-1-4-1.2-9-0.6-2.5 0.4-5 0-9 0.6z" fill="#0f172a"/>
                </svg>
              </div>
              <div class="team-avatar av-right">
                <svg width="42" height="42" viewBox="0 0 42 42" fill="none">
                  <circle cx="21" cy="21" r="21" fill="#f3e8ff"/>
                  <path d="M10 38c0-6 5-11 11-11s11 5 11 11" fill="#a855f7"/>
                  <ellipse cx="21" cy="18" rx="7" ry="8" fill="#fcd9bd"/>
                  <path d="M14 16c0-5 3.5-9 7-9s7 4 7 9c-2-1-3-1-7-0.5-2 0.3-4 0-7 0.5z" fill="#475569"/>
                </svg>
              </div>
            </div>
          </div>

          <h4 class="guide-heading">Quản lý nhân sự hiệu quả</h4>
          <p class="guide-sub">Phân quyền rõ ràng, theo dõi dễ dàng, tối ưu hiệu suất làm việc.</p>

          <!-- Checklist -->
          <ul class="guide-checklist">
            <li>
              <i class="bi bi-check-circle-fill check-icon"></i>
              <span>Quản lý thông tin nhân sự</span>
            </li>
            <li>
              <i class="bi bi-check-circle-fill check-icon"></i>
              <span>Phân quyền theo vai trò</span>
            </li>
            <li>
              <i class="bi bi-check-circle-fill check-icon"></i>
              <span>Theo dõi hoạt động</span>
            </li>
            <li>
              <i class="bi bi-check-circle-fill check-icon"></i>
              <span>Báo cáo chi tiết</span>
            </li>
          </ul>

          <a href="index.php?act=admin/profile" class="btn-guide-action">
            <i class="bi bi-book me-1"></i> Xem hướng dẫn
          </a>
        </div>

        <!-- WIDGET 2: Mẹo nhỏ -->
        <div class="tips-box-card">
          <div class="tips-title">
            <i class="bi bi-lightbulb-fill tips-icon"></i>
            <span>Mẹo nhỏ</span>
          </div>
          <p class="tips-text">
            Bạn có thể sử dụng bộ lọc để tìm nhanh nhân sự theo vai trò hoặc trạng thái hoạt động.
          </p>
        </div>
      </aside>
    </div>

    <!-- 4. CREATE / EDIT MODAL -->
    <div v-if="isModalOpen" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="bi" :class="editingId ? 'bi-pencil-square text-primary' : 'bi-person-plus text-primary'"></i>
            <span>{{ editingId ? 'Cập Nhật Nhân Sự' : 'Thêm Nhân Sự Mới' }}</span>
          </h3>
          <button @click="closeModal" class="btn-close-modal"><i class="bi bi-x-lg"></i></button>
        </div>

        <form 
          method="POST" 
          :action="editingId ? 'index.php?act=admin/nhanSu_update' : 'index.php?act=admin/nhanSu_create'" 
          class="modal-body"
        >
          <input type="hidden" name="_csrf_token" :value="csrfToken">
          <input v-if="editingId" type="hidden" name="nhan_su_id" :value="editingId">

          <!-- Select User if creating -->
          <div v-if="!editingId" class="modal-form-group">
            <label class="form-label">Chọn Tài Khoản Người Dùng</label>
            <select name="nguoi_dung_id" required class="modal-select">
              <option value="">-- Chọn tài khoản chưa gán nhân sự --</option>
              <option v-for="u in availableUsers" :key="u.id" :value="u.id">
                {{ u.ho_ten }} ({{ u.ten_dang_nhap }} - {{ u.email }})
              </option>
            </select>
          </div>

          <!-- Role -->
          <div class="modal-form-group">
            <label class="form-label">Vai Trò Chuyên Môn</label>
            <select v-model="formRole" name="vai_tro" required class="modal-select">
              <option value="HDV">Hướng Dẫn Viên (HDV)</option>
              <option value="DieuHanh">Điều Hành Tour</option>
              <option value="NhaCungCap">Nhà Cung Cấp</option>
              <option value="Khac">Khác</option>
            </select>
          </div>

          <!-- Languages -->
          <div class="modal-form-group">
            <label class="form-label">Ngoại Ngữ</label>
            <input 
              v-model="formNgonNgu" 
              name="ngon_ngu" 
              type="text" 
              class="modal-input" 
              placeholder="VD: Tiếng Anh (IELTS 7.0), Tiếng Trung..."
            />
          </div>

          <!-- Experience -->
          <div class="modal-form-group">
            <label class="form-label">Kinh Nghiệm Làm Việc</label>
            <textarea 
              v-model="formKinhNghiem" 
              name="kinh_nghiem" 
              class="modal-textarea" 
              rows="3" 
              placeholder="Mô tả số năm kinh nghiệm, tuyến tour sở trường..."
            ></textarea>
          </div>

          <!-- Certificate -->
          <div class="modal-form-group">
            <label class="form-label">Chứng Chỉ Hành Nghề</label>
            <input 
              v-model="formChungChi" 
              name="chung_chi" 
              type="text" 
              class="modal-input" 
              placeholder="VD: Thẻ HDV quốc tế, chứng chỉ điều hành du lịch..."
            />
          </div>

          <div class="modal-footer">
            <button type="button" @click="closeModal" class="btn-cancel">Hủy Bỏ</button>
            <button type="submit" class="btn-submit">
              <i class="bi bi-check2 me-1"></i>{{ editingId ? 'Lưu Thay Đổi' : 'Thêm Mới' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
});

// State
const isLoading = ref(false);
const searchQuery = ref('');
const filterRole = ref('');
const filterStatus = ref('');
const filterPeriod = ref('all');
const nhanSuList = ref([]);
const availableRoles = ref([]);
const csrfToken = ref('');

// Pagination
const currentPage = ref(1);
const pageSize = 10;

// Active Row Menu Dropdown
const activeRowMenuId = ref(null);

// Modal
const isModalOpen = ref(false);
const editingId = ref(null);
const formRole = ref('HDV');
const formNgonNgu = ref('');
const formKinhNghiem = ref('');
const formChungChi = ref('');
const availableUsers = ref([]);

// Realtime Clock & Banner Image
const bannerUrl = computed(() => {
  return (window.__BASE_URL__ || '') + 'public/images/dashboard/halong_banner.jpg';
});
const currentDateTimeFormatted = ref('');
let timerId = null;

function updateClock() {
  const now = new Date();
  const days = ['Chủ Nhật', 'Thứ Hai', 'Thứ Ba', 'Thứ Tư', 'Thứ Năm', 'Thứ Sáu', 'Thứ Bảy'];
  const dayName = days[now.getDay()];
  const dd = String(now.getDate()).padStart(2, '0');
  const mm = String(now.getMonth() + 1).padStart(2, '0');
  const yyyy = now.getFullYear();
  const hh = String(now.getHours()).padStart(2, '0');
  const min = String(now.getMinutes()).padStart(2, '0');
  currentDateTimeFormatted.value = `${dayName}, ${dd}/${mm}/${yyyy}  ${hh}:${min}`;
}

let searchDebounce = null;
function onSearchInput() {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    currentPage.value = 1;
  }, 250);
}

function clearSearch() {
  searchQuery.value = '';
  currentPage.value = 1;
}

function applyFilter() {
  currentPage.value = 1;
}

function resetFilters() {
  searchQuery.value = '';
  filterRole.value = '';
  filterStatus.value = '';
  filterPeriod.value = 'all';
  currentPage.value = 1;
}

function toggleRowMenu(id) {
  if (activeRowMenuId.value === id) {
    activeRowMenuId.value = null;
  } else {
    activeRowMenuId.value = id;
  }
}

// Stats computed
const stats = computed(() => {
  const all = nhanSuList.value || [];
  const hdvList = all.filter(n => n.vai_tro === 'HDV');
  const dieuHanhList = all.filter(n => n.vai_tro === 'DieuHanh');
  const nccList = all.filter(n => n.vai_tro === 'NhaCungCap');

  const activeAll = all.filter(isPersonActive).length;
  const hdvActive = hdvList.filter(isPersonActive).length;
  const dieuHanhActive = dieuHanhList.filter(isPersonActive).length;
  const nccActive = nccList.filter(isPersonActive).length;

  return {
    total: all.length,
    active: activeAll,
    inactive: all.length - activeAll,
    hdv: hdvList.length,
    hdvActive: hdvActive,
    hdvInactive: hdvList.length - hdvActive,
    dieuHanh: dieuHanhList.length,
    dieuHanhActive: dieuHanhActive,
    dieuHanhInactive: dieuHanhList.length - dieuHanhActive,
    nhaCungCap: nccList.length,
    nhaCungCapActive: nccActive,
    nhaCungCapInactive: nccList.length - nccActive,
  };
});

function isPersonActive(ns) {
  if (ns.trang_thai_tai_khoan === 'BiKhoa') return false;
  if (ns.trang_thai_lam_viec === 'TamNghi') return false;
  return true;
}

const filteredList = computed(() => {
  let list = nhanSuList.value || [];

  if (filterRole.value) {
    list = list.filter(n => n.vai_tro === filterRole.value);
  }

  if (filterStatus.value) {
    if (filterStatus.value === 'HoatDong') {
      list = list.filter(isPersonActive);
    } else {
      list = list.filter(n => !isPersonActive(n));
    }
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(n => {
      const name = (n.ho_ten || '').toLowerCase();
      const email = (n.email || '').toLowerCase();
      const phone = (n.so_dien_thoai || '').toLowerCase();
      const role = (n.vai_tro || '').toLowerCase();
      return name.includes(q) || email.includes(q) || phone.includes(q) || role.includes(q);
    });
  }

  return list;
});

const totalPages = computed(() => {
  return Math.ceil(filteredList.value.length / pageSize) || 1;
});

const paginatedList = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredList.value.slice(start, start + pageSize);
});

const paginationStart = computed(() => {
  if (filteredList.value.length === 0) return 0;
  return (currentPage.value - 1) * pageSize + 1;
});

const paginationEnd = computed(() => {
  return Math.min(currentPage.value * pageSize, filteredList.value.length);
});

function applyData(data) {
  if (!data) return;
  nhanSuList.value = data.nhanSuList || [];
  if (data.roles) availableRoles.value = data.roles;
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchPersonnel() {
  isLoading.value = true;
  try {
    const res = await fetch('index.php?act=admin/apiNhanSuList', {
      headers: { 'Accept': 'application/json' }
    });
    if (res.ok) {
      const json = await res.json();
      if (json.success && json.data) {
        applyData(json.data);
      }
    }
  } catch (err) {
    console.error('[Vue PersonnelManage] Error fetching data:', err);
  } finally {
    isLoading.value = false;
  }
}

async function openCreateModal() {
  editingId.value = null;
  formRole.value = 'HDV';
  formNgonNgu.value = '';
  formKinhNghiem.value = '';
  formChungChi.value = '';
  isModalOpen.value = true;

  try {
    const res = await fetch('index.php?act=admin/nhanSu_get_users');
    if (res.ok) {
      const json = await res.json();
      availableUsers.value = json.users || [];
    }
  } catch (err) {
    console.error(err);
  }
}

function openEditModal(ns) {
  editingId.value = ns.nhan_su_id;
  formRole.value = ns.vai_tro || 'HDV';
  formNgonNgu.value = ns.ngon_ngu || '';
  formKinhNghiem.value = ns.kinh_nghiem || '';
  formChungChi.value = ns.chung_chi || '';
  isModalOpen.value = true;
}

function closeModal() {
  isModalOpen.value = false;
  editingId.value = null;
}

function confirmDelete(e, ns) {
  if (!confirm(`Bạn có chắc chắn muốn xóa nhân sự "${ns.ho_ten}"?`)) {
    e.preventDefault();
  }
}

function formatRole(role) {
  switch (role) {
    case 'HDV': return 'Hướng dẫn viên';
    case 'DieuHanh': return 'Điều hành tour';
    case 'NhaCungCap': return 'Nhà cung cấp';
    case 'Khac': return 'Khác';
    default: return role || 'Khác';
  }
}

function getRoleClass(role) {
  switch (role) {
    case 'HDV': return 'pill-role-blue';
    case 'NhaCungCap': return 'pill-role-purple';
    case 'DieuHanh': return 'pill-role-amber';
    default: return 'pill-role-gray';
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '20/09/2026';
  const parts = dateStr.split(' ')[0].split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function getAvatarUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  return window.__BASE_URL__ ? window.__BASE_URL__ + path : path;
}

const avatarBgs = ['#e0f2fe', '#fef3c7', '#f3e8ff', '#ecfdf5', '#ffedd5'];
const avatarShirts = ['#3b82f6', '#f59e0b', '#a855f7', '#10b981', '#ea580c'];

function getAvatarBg(idx) {
  return avatarBgs[idx % avatarBgs.length];
}

function getAvatarShirt(idx) {
  return avatarShirts[idx % avatarShirts.length];
}

function handleGlobalClick(e) {
  if (!e.target.closest('.dropdown-action-wrap')) {
    activeRowMenuId.value = null;
  }
}

onMounted(() => {
  updateClock();
  timerId = setInterval(updateClock, 30000);
  document.addEventListener('click', handleGlobalClick);

  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchPersonnel();
  }
});

onUnmounted(() => {
  if (timerId) clearInterval(timerId);
  document.removeEventListener('click', handleGlobalClick);
});
</script>

<style scoped>
.vue-admin-personnel-manage {
  display: flex;
  flex-direction: column;
  gap: 1.35rem;
  padding: 0.5rem 0 2rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: #0f172a;
}

/* 1. HERO BANNER */
.hero-banner {
  background-color: #ffffff;
  background-position: right center;
  background-repeat: no-repeat;
  background-size: cover;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.04);
  padding: 2rem 2.25rem;
  position: relative;
  overflow: hidden;
}

.hero-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  z-index: 2;
  gap: 1.5rem;
}

.kicker-pill {
  display: inline-flex;
  align-items: center;
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.25rem 0.75rem;
  border-radius: 999px;
  letter-spacing: 0.04em;
  margin-bottom: 0.45rem;
}

.hero-title {
  font-size: 1.85rem;
  font-weight: 800;
  margin: 0;
  color: #0f172a;
  letter-spacing: -0.02em;
}

.hero-subtitle {
  font-size: 0.92rem;
  color: #64748b;
  margin: 0.4rem 0 0;
}

.hero-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.75rem;
}

.live-clock-badge {
  background: rgba(255, 255, 255, 0.92);
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 0.45rem 0.9rem;
  font-size: 0.84rem;
  font-weight: 600;
  color: #334155;
  box-shadow: 0 2px 6px rgba(15, 23, 42, 0.02);
  white-space: nowrap;
}

.btn-create-personnel {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: #2563eb;
  color: #ffffff;
  padding: 0.65rem 1.35rem;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.88rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-create-personnel:hover {
  background: #1d4ed8;
  transform: translateY(-1px);
}

/* 2. STATS METRIC GRID */
.stats-metric-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.25rem;
}

@media (max-width: 1100px) {
  .stats-metric-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 600px) {
  .stats-metric-grid {
    grid-template-columns: 1fr;
  }
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.25rem 1.4rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
}

.stat-icon-wrap {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  flex-shrink: 0;
}

.bg-blue-subtle { background: #eff6ff; }
.bg-purple-subtle { background: #f5f3ff; }
.bg-cyan-subtle { background: #f0f9ff; }

.text-primary { color: #2563eb; }
.text-purple { color: #8b5cf6; }
.text-cyan { color: #0284c7; }

.stat-main {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.88rem;
  font-weight: 600;
  color: #64748b;
}

.stat-value {
  font-size: 1.65rem;
  font-weight: 800;
  color: #0f172a;
  line-height: 1.2;
  margin: 0.15rem 0 0.35rem;
}

.stat-trend {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.78rem;
  font-weight: 700;
  margin-bottom: 0.35rem;
}

.trend-positive { color: #10b981; }
.trend-neutral { color: #94a3b8; }

.stat-meta {
  font-size: 0.75rem;
  color: #94a3b8;
  white-space: nowrap;
}

/* 3. TWO-COLUMN LAYOUT */
.layout-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: 1.5rem;
  align-items: start;
}

@media (max-width: 1024px) {
  .layout-grid {
    grid-template-columns: 1fr;
  }
}

/* FILTER STRIP CARD */
.filter-strip-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
  margin-bottom: 1.25rem;
  flex-wrap: wrap;
}

.filter-search-box {
  position: relative;
  flex: 1.8;
  min-width: 220px;
}

.search-icon {
  position: absolute;
  left: 0.85rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.95rem;
}

.filter-search-input {
  width: 100%;
  padding: 0.6rem 2.2rem 0.6rem 2.3rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.86rem;
  color: #0f172a;
  outline: none;
  transition: border-color 0.2s;
}

.filter-search-input:focus {
  border-color: #2563eb;
}

.btn-clear-input {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
}

.filter-field-box {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.field-title {
  font-size: 0.72rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.filter-select {
  padding: 0.55rem 0.9rem;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.85rem;
  color: #0f172a;
  outline: none;
  cursor: pointer;
}

.period-input-wrap {
  position: relative;
}

.input-cal-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 0.85rem;
  pointer-events: none;
}

.select-period {
  padding-left: 2rem;
}

.filter-actions-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: auto;
}

.btn-filter-submit {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  background: #2563eb;
  color: #ffffff;
  padding: 0.55rem 1.15rem;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  transition: background 0.15s;
}

.btn-filter-submit:hover {
  background: #1d4ed8;
}

.btn-filter-reset {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-filter-reset:hover {
  background: #f1f5f9;
  color: #0f172a;
}

/* RECENT PERSONNEL TABLE CARD */
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
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid #f1f5f9;
}

.header-title-group {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.header-icon-circle {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #eff6ff;
  color: #2563eb;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
}

.card-heading {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0;
}

.link-view-all {
  background: none;
  border: none;
  color: #2563eb;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  cursor: pointer;
}

.link-view-all:hover {
  text-decoration: underline;
}

.personnel-data-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}

.personnel-data-table th {
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

.personnel-data-table td {
  padding: 0.95rem 1.15rem;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: middle;
  color: #334155;
  background: #ffffff;
}

.table-data-row:hover td {
  background: #f8fafc;
}

.user-profile-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.avatar-photo-wrap {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  overflow: hidden;
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

.user-fullname {
  font-weight: 700;
  color: #0f172a;
}

/* Role Pills */
.role-pill {
  display: inline-block;
  padding: 0.2rem 0.75rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
}

.pill-role-blue {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
}

.pill-role-purple {
  background: #f5f3ff;
  color: #7c3aed;
  border: 1px solid #ede9fe;
}

.pill-role-amber {
  background: #fffbeb;
  color: #d97706;
  border: 1px solid #fef3c7;
}

.pill-role-gray {
  background: #f1f5f9;
  color: #475569;
}

/* Status Pills */
.status-pill {
  display: inline-block;
  padding: 0.25rem 0.8rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
}

.status-active {
  background: #ecfdf5;
  color: #10b981;
}

.status-inactive {
  background: #fef2f2;
  color: #ef4444;
}

/* Actions Group */
.row-actions-group {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.4rem;
}

.btn-icon-square {
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
  text-decoration: none;
  font-size: 0.95rem;
  transition: all 0.15s;
}

.btn-icon-square:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #0f172a;
}

/* Dropdown Action Menu */
.dropdown-action-wrap {
  position: relative;
}

.row-popup-menu {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 4px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12);
  min-width: 170px;
  z-index: 100;
  overflow: hidden;
  padding: 0.35rem 0;
}

.popup-menu-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 0.55rem 1rem;
  font-size: 0.84rem;
  font-weight: 600;
  color: #334155;
  background: none;
  border: none;
  text-align: left;
  text-decoration: none;
  cursor: pointer;
}

.popup-menu-item:hover {
  background: #f8fafc;
}

/* Table Footer */
.table-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.5rem;
  border-top: 1px solid #f1f5f9;
  background: #ffffff;
}

.pagination-info {
  font-size: 0.84rem;
  color: #64748b;
}

.pagination-controls {
  display: flex;
  gap: 0.35rem;
}

.btn-page {
  min-width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #ffffff;
  color: #475569;
  font-size: 0.84rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
}

.btn-page:hover:not(:disabled) {
  background: #f8fafc;
  color: #0f172a;
}

.btn-page.active {
  background: #2563eb;
  color: #ffffff;
  border-color: #2563eb;
}

.btn-page:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

/* Empty State */
.empty-state-cell {
  padding: 3rem 1rem !important;
  text-align: center;
}

.empty-inner {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.empty-icon {
  font-size: 2.5rem;
  color: #cbd5e1;
  margin-bottom: 0.65rem;
}

.empty-title {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.25rem;
}

.empty-sub {
  font-size: 0.85rem;
  color: #64748b;
  margin: 0 0 1rem;
}

.btn-empty-reset {
  background: #eff6ff;
  color: #2563eb;
  border: 1px solid #bfdbfe;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 0.45rem 1.25rem;
  border-radius: 8px;
  cursor: pointer;
}

/* RIGHT COLUMN WIDGETS */
.right-column {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.info-guide-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 1.5rem;
  box-shadow: 0 2px 10px rgba(15, 23, 42, 0.02);
}

.guide-illustration-wrap {
  display: flex;
  justify-content: center;
  margin-bottom: 1.25rem;
}

.team-avatars-cluster {
  display: flex;
  align-items: center;
  justify-content: center;
}

.team-avatar {
  border-radius: 50%;
  border: 3px solid #ffffff;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.08);
}

.av-left {
  transform: translateX(12px);
  z-index: 1;
}

.av-center {
  z-index: 2;
}

.av-right {
  transform: translateX(-12px);
  z-index: 1;
}

.guide-heading {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
  margin: 0 0 0.4rem;
}

.guide-sub {
  font-size: 0.82rem;
  color: #64748b;
  line-height: 1.45;
  margin: 0 0 1rem;
}

.guide-checklist {
  list-style: none;
  padding: 0;
  margin: 0 0 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.guide-checklist li {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  font-size: 0.84rem;
  font-weight: 600;
  color: #334155;
}

.check-icon {
  color: #10b981;
  font-size: 1rem;
}

.btn-guide-action {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  padding: 0.65rem 1rem;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #ffffff;
  color: #1e293b;
  font-weight: 700;
  font-size: 0.85rem;
  text-decoration: none;
  transition: all 0.15s;
}

.btn-guide-action:hover {
  background: #f8fafc;
  border-color: #94a3b8;
  color: #0f172a;
}

.tips-box-card {
  background: #f0f7ff;
  border: 1px solid #bfdbfe;
  border-radius: 14px;
  padding: 1.25rem;
}

.tips-title {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  color: #1d4ed8;
  font-weight: 700;
  font-size: 0.95rem;
}

.tips-icon {
  color: #3b82f6;
}

.tips-text {
  font-size: 0.84rem;
  color: #334155;
  line-height: 1.5;
  margin: 0.45rem 0 0;
}

/* MODAL */
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
  max-width: 520px;
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
  font-size: 1.15rem;
  font-weight: 700;
  margin: 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #0f172a;
}

.btn-close-modal {
  background: none;
  border: none;
  font-size: 1.2rem;
  color: #94a3b8;
  cursor: pointer;
}

.modal-body {
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-form-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.form-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: #334155;
}

.modal-select,
.modal-input,
.modal-textarea {
  width: 100%;
  padding: 0.6rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.88rem;
  background: #ffffff;
  color: #0f172a;
  outline: none;
}

.modal-select:focus,
.modal-input:focus,
.modal-textarea:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
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
