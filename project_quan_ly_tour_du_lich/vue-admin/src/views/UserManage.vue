<template>
  <div class="vue-admin-user-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-person-gear"></i>
          <span>Phân Quyền & Tài Khoản</span>
        </div>
        <h1 class="header-title">Quản Lý Người Dùng</h1>
        <p class="header-subtitle">
          Quản lý danh sách tài khoản, kiểm soát trạng thái hoạt động, đổi mật khẩu và phân quyền người dùng trong hệ thống
        </p>
      </div>
      <div class="header-actions">
        <button @click="openCreateModal" class="btn-action-primary">
          <i class="bi bi-person-plus-fill"></i>
          <span>Thêm Người Dùng</span>
        </button>
        <a href="index.php?act=admin/nhanSu" class="btn-action-outline">
          <i class="bi bi-people"></i>
          <span>Quản Lý Nhân Sự</span>
        </a>
      </div>
    </header>

    <!-- NOTIFICATION FLASH -->
    <transition name="fade">
      <div v-if="toastMessage" class="toast-alert" :class="'toast-' + toastType">
        <i class="bi" :class="toastType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
        <span>{{ toastMessage }}</span>
      </div>
    </transition>

    <!-- 7 STATS CARDS -->
    <section class="stats-grid">
      <div class="stat-card stat-total">
        <div class="stat-content">
          <span class="stat-label">Tổng người dùng</span>
          <strong class="stat-num">{{ stats.total }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-people-fill"></i></div>
      </div>

      <div class="stat-card stat-active">
        <div class="stat-content">
          <span class="stat-label">Đang hoạt động</span>
          <strong class="stat-num">{{ stats.active }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-person-check-fill"></i></div>
      </div>

      <div class="stat-card stat-locked">
        <div class="stat-content">
          <span class="stat-label">Bị khóa</span>
          <strong class="stat-num">{{ stats.locked }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-person-x-fill"></i></div>
      </div>

      <div class="stat-card stat-admin">
        <div class="stat-content">
          <span class="stat-label">Quản trị viên (Admin)</span>
          <strong class="stat-num">{{ stats.roles.Admin || 0 }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-shield-lock-fill"></i></div>
      </div>

      <div class="stat-card stat-hdv">
        <div class="stat-content">
          <span class="stat-label">Hướng dẫn viên (HDV)</span>
          <strong class="stat-num">{{ stats.roles.HDV || 0 }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-compass-fill"></i></div>
      </div>

      <div class="stat-card stat-cust">
        <div class="stat-content">
          <span class="stat-label">Khách hàng</span>
          <strong class="stat-num">{{ stats.roles.KhachHang || 0 }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-person-heart"></i></div>
      </div>

      <div class="stat-card stat-supplier">
        <div class="stat-content">
          <span class="stat-label">Nhà cung cấp</span>
          <strong class="stat-num">{{ stats.roles.NhaCungCap || 0 }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-building"></i></div>
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
              placeholder="Tên đăng nhập, họ tên, email, SĐT..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <!-- Role Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-person-badge me-1"></i>Vai trò</label>
          <select v-model="selectedRole" @change="onFilterChange" class="form-select-custom">
            <option value="">Tất cả vai trò</option>
            <option value="Admin">Admin</option>
            <option value="HDV">HDV</option>
            <option value="KhachHang">Khách hàng</option>
            <option value="NhaCungCap">Nhà cung cấp</option>
          </select>
        </div>

        <!-- Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-toggle-on me-1"></i>Trạng thái</label>
          <select v-model="selectedStatus" @change="onFilterChange" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="HoatDong">Hoạt động</option>
            <option value="BiKhoa">Bị khóa</option>
          </select>
        </div>

        <!-- Reset Button -->
        <div class="filter-col action-col">
          <button @click="resetFilters" class="btn-reset" title="Khôi phục bộ lọc">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- USERS TABLE -->
    <section class="table-card">
      <div class="table-meta-bar">
        <div class="meta-left">
          <span>Tổng cộng: <strong>{{ filteredUsers.length }}</strong> tài khoản</span>
          <span v-if="isLoading" class="loading-tag ms-3">
            <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
          </span>
        </div>
        <div class="meta-right">
          <label class="page-size-label">
            <span>Hiển thị</span>
            <select v-model.number="pageSize" @change="currentPage = 1" class="page-size-select">
              <option :value="10">10</option>
              <option :value="15">15</option>
              <option :value="25">25</option>
              <option :value="50">50</option>
            </select>
            <span>dòng/trang</span>
          </label>
        </div>
      </div>

      <div class="table-responsive">
        <table class="user-table">
          <thead>
            <tr>
              <th style="width: 65px;" class="text-center">ID</th>
              <th>Tài Khoản</th>
              <th>Họ & Tên</th>
              <th>Liên Hệ</th>
              <th style="width: 130px;" class="text-center">Vai Trò</th>
              <th style="width: 120px;" class="text-center">Trạng Thái</th>
              <th style="width: 130px;">Ngày Tạo</th>
              <th style="width: 230px;" class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in paginatedUsers" :key="u.id" class="user-row">
              <td class="text-center">
                <span class="id-badge">#{{ u.id }}</span>
              </td>

              <!-- Username & Avatar -->
              <td>
                <div class="user-cell">
                  <div class="user-avatar" :class="'avatar-' + (u.vai_tro || 'KhachHang')">
                    {{ (u.ho_ten || u.ten_dang_nhap || 'U').charAt(0).toUpperCase() }}
                  </div>
                  <div class="user-info">
                    <strong class="user-username">{{ u.ten_dang_nhap }}</strong>
                    <span v-if="u.quyen_cap_cao" class="super-admin-tag">Cấp cao</span>
                  </div>
                </div>
              </td>

              <!-- Full Name -->
              <td>
                <span class="user-name">{{ u.ho_ten || '---' }}</span>
              </td>

              <!-- Email & Phone -->
              <td>
                <div class="contact-info">
                  <span class="contact-item" v-if="u.email" :title="u.email">
                    <i class="bi bi-envelope"></i> {{ u.email }}
                  </span>
                  <span class="contact-item" v-if="u.so_dien_thoai">
                    <i class="bi bi-telephone"></i> {{ u.so_dien_thoai }}
                  </span>
                  <span v-if="!u.email && !u.so_dien_thoai" class="text-muted">Chưa cập nhật</span>
                </div>
              </td>

              <!-- Role -->
              <td class="text-center">
                <span class="role-badge" :class="'role-' + (u.vai_tro || 'KhachHang')">
                  {{ formatRole(u.vai_tro) }}
                </span>
              </td>

              <!-- Status -->
              <td class="text-center">
                <span class="status-pill" :class="u.trang_thai === 'BiKhoa' ? 'st-locked' : 'st-active'">
                  {{ u.trang_thai === 'BiKhoa' ? 'Bị khóa' : 'Hoạt động' }}
                </span>
              </td>

              <!-- Created Date -->
              <td>
                <span class="date-text">{{ formatDate(u.ngay_tao) }}</span>
              </td>

              <!-- Actions -->
              <td class="text-center">
                <div class="action-cell">
                  <!-- Tag if self -->
                  <span v-if="Number(u.id) === Number(currentUserId)" class="current-user-tag me-1" title="Tài khoản đang đăng nhập">
                    <i class="bi bi-person-check-fill me-1"></i>Bạn
                  </span>

                  <!-- Edit Button -->
                  <button 
                    @click="openEditModal(u)" 
                    class="btn-action-sm btn-edit" 
                    title="Chỉnh sửa thông tin tài khoản"
                  >
                    <i class="bi bi-pencil-square"></i>
                    <span>Sửa</span>
                  </button>

                  <!-- Reset Password Button -->
                  <button 
                    @click="openResetModal(u)" 
                    class="btn-action-sm btn-pwd" 
                    title="Cấp đổi mật khẩu mới"
                  >
                    <i class="bi bi-key-fill"></i>
                    <span>Đổi MK</span>
                  </button>

                  <!-- Lock / Unlock Toggle Button (not allowed for self) -->
                  <button 
                    v-if="Number(u.id) !== Number(currentUserId)"
                    @click="toggleUserStatus(u)"
                    :disabled="u._isUpdating"
                    class="btn-action-sm"
                    :class="u.trang_thai === 'BiKhoa' ? 'btn-unlock' : 'btn-lock'"
                    :title="u.trang_thai === 'BiKhoa' ? 'Mở khóa tài khoản này' : 'Khóa tài khoản này'"
                  >
                    <i class="bi" :class="u.trang_thai === 'BiKhoa' ? 'bi-unlock' : 'bi-lock'"></i>
                    <span>{{ u.trang_thai === 'BiKhoa' ? 'Mở' : 'Khóa' }}</span>
                  </button>

                  <!-- Delete Button (not allowed for self) -->
                  <button 
                    v-if="Number(u.id) !== Number(currentUserId)"
                    @click="deleteUser(u)"
                    :disabled="u._isUpdating"
                    class="btn-action-sm btn-delete"
                    title="Xóa tài khoản này"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-if="paginatedUsers.length === 0 && !isLoading">
              <td colspan="8" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy người dùng nào phù hợp với điều kiện tìm kiếm.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION BAR -->
      <div class="table-pagination-bar" v-if="filteredUsers.length > 0">
        <div class="pagination-info">
          Hiển thị <strong>{{ (currentPage - 1) * pageSize + 1 }}</strong> - 
          <strong>{{ Math.min(currentPage * pageSize, filteredUsers.length) }}</strong> 
          trong tổng số <strong>{{ filteredUsers.length }}</strong> tài khoản
        </div>

        <div class="pagination-controls" v-if="totalPages > 1">
          <button 
            class="page-btn" 
            :disabled="currentPage === 1" 
            @click="changePage(1)" 
            title="Trang đầu"
          >
            <i class="bi bi-chevron-double-left"></i>
          </button>
          <button 
            class="page-btn" 
            :disabled="currentPage === 1" 
            @click="changePage(currentPage - 1)" 
            title="Trang trước"
          >
            <i class="bi bi-chevron-left"></i>
          </button>

          <template v-for="page in visiblePages" :key="page">
            <span v-if="page === '...'" class="page-ellipsis">...</span>
            <button 
              v-else 
              class="page-btn" 
              :class="{ active: currentPage === page }" 
              @click="changePage(page)"
            >
              {{ page }}
            </button>
          </template>

          <button 
            class="page-btn" 
            :disabled="currentPage === totalPages" 
            @click="changePage(currentPage + 1)" 
            title="Trang tiếp"
          >
            <i class="bi bi-chevron-right"></i>
          </button>
          <button 
            class="page-btn" 
            :disabled="currentPage === totalPages" 
            @click="changePage(totalPages)" 
            title="Trang cuối"
          >
            <i class="bi bi-chevron-double-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- MODAL 1: TẠO NGƯỜI DÙNG MỚI -->
    <div v-if="isCreateModalOpen" class="modal-backdrop" @click.self="isCreateModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="bi bi-person-plus-fill me-2" style="color: #dfa974;"></i>
            Thêm Người Dùng Mới
          </h3>
          <button type="button" @click="isCreateModalOpen = false" class="btn-close-modal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form @submit.prevent="submitCreateUser" class="modal-body">
          <div class="modal-form-grid">
            <div class="modal-form-group">
              <label class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
              <input 
                v-model="createForm.ten_dang_nhap" 
                required 
                type="text" 
                class="modal-input" 
                placeholder="VD: admin_nhansu, hdv_nam..."
              />
              <span class="form-help">Chữ thường, số, dấu gạch dưới, tối thiểu 3 ký tự</span>
            </div>

            <div class="modal-form-group">
              <label class="form-label">Mật khẩu khởi tạo <span class="text-danger">*</span></label>
              <div class="input-password-wrap">
                <input 
                  v-model="createForm.mat_khau" 
                  :type="showCreatePassword ? 'text' : 'password'"
                  required 
                  minlength="6"
                  class="modal-input" 
                  placeholder="Tối thiểu 6 ký tự..."
                />
                <button type="button" class="btn-pwd-eye" @click="showCreatePassword = !showCreatePassword">
                  <i class="bi" :class="showCreatePassword ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
              </div>
            </div>
          </div>

          <div class="modal-form-group">
            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input 
              v-model="createForm.ho_ten" 
              required 
              type="text" 
              class="modal-input" 
              placeholder="VD: Nguyễn Văn A"
            />
          </div>

          <div class="modal-form-grid">
            <div class="modal-form-group">
              <label class="form-label">Email liên hệ <span class="text-danger">*</span></label>
              <input 
                v-model="createForm.email" 
                required 
                type="email" 
                class="modal-input" 
                placeholder="user@example.com"
              />
            </div>

            <div class="modal-form-group">
              <label class="form-label">Số điện thoại</label>
              <input 
                v-model="createForm.so_dien_thoai" 
                type="text" 
                class="modal-input" 
                placeholder="0912345678"
              />
            </div>
          </div>

          <div class="modal-form-grid">
            <div class="modal-form-group">
              <label class="form-label">Vai trò <span class="text-danger">*</span></label>
              <select v-model="createForm.vai_tro" required class="modal-select">
                <option value="KhachHang">Khách hàng</option>
                <option value="HDV">Hướng dẫn viên (HDV)</option>
                <option value="NhaCungCap">Nhà cung cấp</option>
                <option value="Admin">Quản trị viên (Admin)</option>
              </select>
            </div>

            <div class="modal-form-group">
              <label class="form-label">Trạng thái tài khoản</label>
              <select v-model="createForm.trang_thai" class="modal-select">
                <option value="HoatDong">Hoạt động ngay</option>
                <option value="BiKhoa">Tạm khóa</option>
              </select>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="isCreateModalOpen = false" class="btn-cancel">Hủy</button>
            <button type="submit" :disabled="isSubmitting" class="btn-submit">
              <i class="bi" :class="isSubmitting ? 'bi-arrow-repeat spin' : 'bi-check-lg'"></i>
              <span>{{ isSubmitting ? 'Đang tạo...' : 'Tạo Tài Khoản' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 2: CHỈNH SỬA NGƯỜI DÙNG -->
    <div v-if="isEditModalOpen" class="modal-backdrop" @click.self="isEditModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="bi bi-pencil-square me-2" style="color: #38bdf8;"></i>
            Chỉnh Sửa Người Dùng: @{{ editForm.ten_dang_nhap }}
          </h3>
          <button type="button" @click="isEditModalOpen = false" class="btn-close-modal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form @submit.prevent="submitEditUser" class="modal-body">
          <div class="modal-form-group">
            <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
            <input 
              v-model="editForm.ho_ten" 
              required 
              type="text" 
              class="modal-input" 
              placeholder="Nhập họ và tên..."
            />
          </div>

          <div class="modal-form-grid">
            <div class="modal-form-group">
              <label class="form-label">Email liên hệ <span class="text-danger">*</span></label>
              <input 
                v-model="editForm.email" 
                required 
                type="email" 
                class="modal-input" 
                placeholder="user@example.com"
              />
            </div>

            <div class="modal-form-group">
              <label class="form-label">Số điện thoại</label>
              <input 
                v-model="editForm.so_dien_thoai" 
                type="text" 
                class="modal-input" 
                placeholder="0912345678"
              />
            </div>
          </div>

          <div class="modal-form-grid">
            <div class="modal-form-group">
              <label class="form-label">Vai trò hệ thống</label>
              <select 
                v-model="editForm.vai_tro" 
                :disabled="Number(editForm.id) === Number(currentUserId)"
                class="modal-select"
              >
                <option value="Admin">Quản trị viên (Admin)</option>
                <option value="HDV">Hướng dẫn viên (HDV)</option>
                <option value="KhachHang">Khách hàng</option>
                <option value="NhaCungCap">Nhà cung cấp</option>
              </select>
              <span v-if="Number(editForm.id) === Number(currentUserId)" class="form-help">Không thể tự hạ vai trò Admin của chính bạn</span>
            </div>

            <div class="modal-form-group">
              <label class="form-label">Trạng thái hoạt động</label>
              <select 
                v-model="editForm.trang_thai" 
                :disabled="Number(editForm.id) === Number(currentUserId)"
                class="modal-select"
              >
                <option value="HoatDong">Hoạt động</option>
                <option value="BiKhoa">Bị khóa</option>
              </select>
              <span v-if="Number(editForm.id) === Number(currentUserId)" class="form-help">Không thể tự khóa tài khoản của chính bạn</span>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" @click="isEditModalOpen = false" class="btn-cancel">Hủy</button>
            <button type="submit" :disabled="isSubmitting" class="btn-submit btn-submit-blue">
              <i class="bi" :class="isSubmitting ? 'bi-arrow-repeat spin' : 'bi-save'"></i>
              <span>{{ isSubmitting ? 'Đang lưu...' : 'Lưu Thay Đổi' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL 3: CẤP ĐỔI MẬT KHẨU -->
    <div v-if="isResetModalOpen" class="modal-backdrop" @click.self="isResetModalOpen = false">
      <div class="modal-card modal-sm">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="bi bi-key-fill me-2" style="color: #f59e0b;"></i>
            Cấp Đổi Mật Khẩu
          </h3>
          <button type="button" @click="isResetModalOpen = false" class="btn-close-modal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <form @submit.prevent="submitResetPassword" class="modal-body">
          <div class="reset-target-box">
            <div class="r-avatar">{{ resetTargetUser.ho_ten ? resetTargetUser.ho_ten.charAt(0).toUpperCase() : 'U' }}</div>
            <div class="r-info">
              <div class="r-name">{{ resetTargetUser.ho_ten || resetTargetUser.ten_dang_nhap }}</div>
              <div class="r-user">@{{ resetTargetUser.ten_dang_nhap }} • {{ formatRole(resetTargetUser.vai_tro) }}</div>
            </div>
          </div>

          <div class="modal-form-group">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label mb-0">Mật khẩu mới <span class="text-danger">*</span></label>
              <button type="button" @click="generateRandomPassword" class="btn-inline-gen">
                <i class="bi bi-magic me-1"></i>Tạo ngẫu nhiên
              </button>
            </div>
            <div class="input-password-wrap">
              <input 
                v-model="resetForm.new_password" 
                :type="showResetPassword ? 'text' : 'password'"
                required 
                minlength="6"
                class="modal-input" 
                placeholder="Nhập tối thiểu 6 ký tự..."
              />
              <button type="button" class="btn-pwd-eye" @click="showResetPassword = !showResetPassword">
                <i class="bi" :class="showResetPassword ? 'bi-eye-slash' : 'bi-eye'"></i>
              </button>
            </div>
          </div>

          <div v-if="resetForm.new_password" class="pwd-copy-bar">
            <span>Mật khẩu: <strong class="text-gold font-monospace">{{ resetForm.new_password }}</strong></span>
            <button type="button" @click="copyGeneratedPassword" class="btn-copy">
              <i class="bi" :class="hasCopied ? 'bi-check-circle-fill text-success' : 'bi-clipboard'"></i>
              {{ hasCopied ? 'Đã sao chép' : 'Sao chép' }}
            </button>
          </div>

          <div class="modal-footer">
            <button type="button" @click="isResetModalOpen = false" class="btn-cancel">Hủy</button>
            <button type="submit" :disabled="isSubmitting" class="btn-submit btn-submit-gold">
              <i class="bi" :class="isSubmitting ? 'bi-arrow-repeat spin' : 'bi-shield-check'"></i>
              <span>{{ isSubmitting ? 'Đang cập nhật...' : 'Xác Nhận Đổi MK' }}</span>
            </button>
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
const isLoading = ref(false);
const isSubmitting = ref(false);
const searchQuery = ref('');
const selectedRole = ref('');
const selectedStatus = ref('');
const currentUserId = ref(0);
const csrfToken = ref('');
const csrfGlobal = ref('');

// Pagination State
const pageSize = ref(15);
const currentPage = ref(1);

// Modals State
const isCreateModalOpen = ref(false);
const showCreatePassword = ref(false);
const createForm = ref({
  ten_dang_nhap: '',
  mat_khau: '',
  ho_ten: '',
  email: '',
  so_dien_thoai: '',
  vai_tro: 'KhachHang',
  trang_thai: 'HoatDong'
});

const isEditModalOpen = ref(false);
const editForm = ref({
  id: 0,
  ten_dang_nhap: '',
  ho_ten: '',
  email: '',
  so_dien_thoai: '',
  vai_tro: 'KhachHang',
  trang_thai: 'HoatDong'
});

const isResetModalOpen = ref(false);
const showResetPassword = ref(true);
const hasCopied = ref(false);
const resetTargetUser = ref({});
const resetForm = ref({
  user_id: 0,
  new_password: ''
});

// Toast
const toastMessage = ref('');
const toastType = ref('success');

const users = ref([]);
const stats = ref({
  total: 0,
  active: 0,
  locked: 0,
  roles: {
    Admin: 0,
    HDV: 0,
    KhachHang: 0,
    NhaCungCap: 0,
  }
});

let searchDebounce = null;

function onSearchInput() {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    currentPage.value = 1;
    fetchUsers();
  }, 350);
}

function onFilterChange() {
  currentPage.value = 1;
  fetchUsers();
}

function clearSearch() {
  searchQuery.value = '';
  currentPage.value = 1;
  fetchUsers();
}

function resetFilters() {
  searchQuery.value = '';
  selectedRole.value = '';
  selectedStatus.value = '';
  currentPage.value = 1;
  fetchUsers();
}

function showToast(msg, type = 'success') {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4500);
}

const filteredUsers = computed(() => {
  return users.value || [];
});

const totalPages = computed(() => {
  const count = filteredUsers.value.length;
  return Math.ceil(count / pageSize.value) || 1;
});

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value;
  return filteredUsers.value.slice(start, start + pageSize.value);
});

const visiblePages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  if (total <= 7) {
    return Array.from({ length: total }, (_, i) => i + 1);
  }
  const pages = [];
  pages.push(1);
  if (current > 3) pages.push('...');
  const start = Math.max(2, current - 1);
  const end = Math.min(total - 1, current + 1);
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  if (current < total - 2) pages.push('...');
  pages.push(total);
  return pages;
});

function changePage(p) {
  if (p >= 1 && p <= totalPages.value) {
    currentPage.value = p;
  }
}

function applyData(data) {
  if (!data) return;
  users.value = data.users || [];
  if (data.userStats) {
    stats.value = {
      total: data.userStats.total || 0,
      active: data.userStats.active || 0,
      locked: data.userStats.locked || 0,
      roles: data.userStats.roles || {},
    };
  }
  if (data.currentUserId) currentUserId.value = data.currentUserId;
  if (data.csrfToken) csrfToken.value = data.csrfToken;
  if (data.csrfGlobal) csrfGlobal.value = data.csrfGlobal;
}

async function fetchUsers() {
  isLoading.value = true;
  const params = new URLSearchParams({
    act: 'admin/apiNguoiDungList'
  });
  if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
  if (selectedRole.value) params.append('role', selectedRole.value);
  if (selectedStatus.value) params.append('status', selectedStatus.value);

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
    console.error('[Vue UserManage] Error fetching users:', err);
  } finally {
    isLoading.value = false;
  }
}

// Modal handlers
function openCreateModal() {
  createForm.value = {
    ten_dang_nhap: '',
    mat_khau: '',
    ho_ten: '',
    email: '',
    so_dien_thoai: '',
    vai_tro: 'KhachHang',
    trang_thai: 'HoatDong'
  };
  showCreatePassword.value = false;
  isCreateModalOpen.value = true;
}

async function submitCreateUser() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const res = await fetch('index.php?act=admin/apiCreateUser', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        ...createForm.value,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const json = await res.json();
    if (res.ok && json.success) {
      showToast(json.message || 'Tạo người dùng mới thành công!');
      isCreateModalOpen.value = false;
      fetchUsers();
    } else {
      showToast(json.message || 'Không thể tạo người dùng.', 'error');
    }
  } catch (e) {
    showToast('Lỗi kết nối khi tạo người dùng.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

function openEditModal(u) {
  editForm.value = {
    id: u.id,
    ten_dang_nhap: u.ten_dang_nhap || '',
    ho_ten: u.ho_ten || '',
    email: u.email || '',
    so_dien_thoai: u.so_dien_thoai || '',
    vai_tro: u.vai_tro || 'KhachHang',
    trang_thai: u.trang_thai || 'HoatDong'
  };
  isEditModalOpen.value = true;
}

async function submitEditUser() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const res = await fetch('index.php?act=admin/apiUpdateUser', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        ...editForm.value,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const json = await res.json();
    if (res.ok && json.success) {
      showToast(json.message || 'Cập nhật tài khoản thành công!');
      isEditModalOpen.value = false;
      fetchUsers();
    } else {
      showToast(json.message || 'Không thể cập nhật tài khoản.', 'error');
    }
  } catch (e) {
    showToast('Lỗi kết nối khi cập nhật tài khoản.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

function openResetModal(u) {
  resetTargetUser.value = u;
  resetForm.value = {
    user_id: u.id,
    new_password: ''
  };
  hasCopied.value = false;
  showResetPassword.value = true;
  generateRandomPassword();
  isResetModalOpen.value = true;
}

function generateRandomPassword() {
  const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
  let pass = '';
  for (let i = 0; i < 10; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  resetForm.value.new_password = pass;
  hasCopied.value = false;
}

function copyGeneratedPassword() {
  if (!resetForm.value.new_password) return;
  navigator.clipboard.writeText(resetForm.value.new_password).then(() => {
    hasCopied.value = true;
    setTimeout(() => { hasCopied.value = false; }, 3000);
  }).catch(() => {
    showToast('Không thể sao chép vào clipboard.', 'error');
  });
}

async function submitResetPassword() {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  try {
    const res = await fetch('index.php?act=admin/apiResetPassword', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: resetForm.value.user_id,
        new_password: resetForm.value.new_password,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const json = await res.json();
    if (res.ok && json.success) {
      showToast(json.message || 'Đổi mật khẩu thành công!');
      isResetModalOpen.value = false;
    } else {
      showToast(json.message || 'Không thể đổi mật khẩu.', 'error');
    }
  } catch (e) {
    showToast('Lỗi kết nối khi đổi mật khẩu.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

async function toggleUserStatus(u) {
  const newStatus = u.trang_thai === 'BiKhoa' ? 'HoatDong' : 'BiKhoa';
  const actionText = newStatus === 'BiKhoa' ? 'KHÓA' : 'MỞ KHÓA';
  
  if (!confirm(`Bạn có chắc muốn ${actionText} tài khoản "${u.ten_dang_nhap}"?`)) {
    return;
  }

  u._isUpdating = true;
  try {
    const res = await fetch('index.php?act=admin/apiToggleUserStatus', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: u.id,
        status: newStatus,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value,
      })
    });

    if (res.ok) {
      const json = await res.json();
      if (json.success) {
        u.trang_thai = newStatus;
        showToast(json.message || `Đã ${actionText.toLowerCase()} tài khoản thành công.`);
        fetchUsers();
      } else {
        showToast(json.message || 'Thao tác thất bại.', 'error');
      }
    } else {
      showToast('Lỗi máy chủ khi cập nhật trạng thái.', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối khi cập nhật trạng thái.', 'error');
  } finally {
    u._isUpdating = false;
  }
}

async function deleteUser(u) {
  if (!confirm(`CẢNH BÁO: Bạn có chắc chắn muốn xóa tài khoản "${u.ten_dang_nhap}" (${u.ho_ten || '---'})?\n\nTài khoản sẽ được chuyển sang trạng thái đã xóa an toàn.`)) {
    return;
  }

  u._isUpdating = true;
  try {
    const res = await fetch('index.php?act=admin/apiDeleteUser', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: u.id,
        _csrf_token: csrfToken.value,
        _csrf_global: csrfGlobal.value
      })
    });

    const json = await res.json();
    if (res.ok && json.success) {
      showToast(json.message || 'Xóa tài khoản thành công!');
      fetchUsers();
    } else {
      showToast(json.message || 'Không thể xóa tài khoản.', 'error');
    }
  } catch (e) {
    showToast('Lỗi kết nối khi xóa tài khoản.', 'error');
  } finally {
    u._isUpdating = false;
  }
}

function formatRole(role) {
  switch (role) {
    case 'Admin': return 'Admin';
    case 'HDV': return 'HDV';
    case 'KhachHang': return 'Khách hàng';
    case 'NhaCungCap': return 'Nhà cung cấp';
    default: return role || 'Khách hàng';
  }
}

function formatDate(dateStr) {
  if (!dateStr) return '---';
  const parts = dateStr.split(' ');
  const d = parts[0].split('-');
  if (d.length === 3) return `${d[2]}/${d[1]}/${d[0]}`;
  return dateStr;
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchUsers();
  }
});
</script>

<style scoped>
.vue-admin-user-manage {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* HEADER */
.page-header {
  background: linear-gradient(135deg, rgba(22, 31, 46, 0.85) 0%, rgba(15, 23, 42, 0.95) 100%);
  padding: 1.75rem 2rem;
  border-radius: 1.25rem;
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
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

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-action-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #dfa974 0%, #b27a3c 100%);
  color: #0f172a;
  padding: 0.75rem 1.25rem;
  border-radius: 0.65rem;
  font-weight: 700;
  font-size: 0.9rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(223, 169, 116, 0.3);
  transition: all 0.2s ease;
}
.btn-action-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(223, 169, 116, 0.45);
  filter: brightness(1.08);
}

.btn-action-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: rgba(255, 255, 255, 0.06);
  color: #f8fafc;
  padding: 0.75rem 1.15rem;
  border-radius: 0.65rem;
  font-weight: 600;
  font-size: 0.9rem;
  text-decoration: none;
  border: 1px solid rgba(255, 255, 255, 0.12);
  transition: all 0.2s;
}
.btn-action-outline:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }

/* TOAST ALERT */
.toast-alert {
  padding: 0.9rem 1.25rem;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  font-weight: 600;
  font-size: 0.92rem;
  backdrop-filter: blur(8px);
}
.toast-success { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
.toast-error { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

/* STATS GRID */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 1rem;
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
.stat-active { border-left-color: #10b981; }
.stat-locked { border-left-color: #ef4444; }
.stat-admin { border-left-color: #f43f5e; }
.stat-hdv { border-left-color: #0ea5e9; }
.stat-cust { border-left-color: #14b8a6; }
.stat-supplier { border-left-color: #f59e0b; }

.stat-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 0.35rem; letter-spacing: 0.03em; }
.stat-num { font-size: 1.75rem; font-weight: 800; color: #ffffff; line-height: 1; }
.stat-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
}
.stat-total .stat-icon-box { background: rgba(99, 102, 241, 0.16); color: #818cf8; }
.stat-active .stat-icon-box { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.stat-locked .stat-icon-box { background: rgba(239, 68, 68, 0.16); color: #f87171; }
.stat-admin .stat-icon-box { background: rgba(244, 63, 94, 0.16); color: #fb7185; }
.stat-hdv .stat-icon-box { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.stat-cust .stat-icon-box { background: rgba(20, 184, 166, 0.16); color: #2dd4bf; }
.stat-supplier .stat-icon-box { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }

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
  grid-template-columns: 1.8fr 1fr 1fr auto;
  gap: 1rem;
  align-items: flex-end;
}
.filter-col { display: flex; flex-direction: column; gap: 0.4rem; }
.filter-label { font-size: 0.82rem; font-weight: 700; color: #cbd5e1; }
.input-icon-wrap { position: relative; display: flex; align-items: center; }
.icon-left { position: absolute; left: 0.85rem; color: #94a3b8; font-size: 0.9rem; }
.form-input-custom {
  width: 100%;
  height: 44px;
  padding: 0 2.2rem 0 2.5rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.9rem;
  color: #ffffff;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  transition: all 0.2s;
}
.form-input-custom:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
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
.form-select-custom {
  height: 44px;
  padding: 0 1rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.9rem;
  color: #ffffff;
  outline: none;
  background-color: rgba(11, 17, 32, 0.85);
  transition: all 0.2s;
}
.form-select-custom:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
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

/* TABLE CARD */
.table-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  overflow: hidden;
}
.table-meta-bar {
  padding: 1rem 1.5rem;
  background: rgba(11, 17, 32, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #cbd5e1;
  flex-wrap: wrap;
  gap: 12px;
}
.page-size-label {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  color: #94a3b8;
}
.page-size-select {
  padding: 3px 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 0.82rem;
  outline: none;
}
.loading-tag { color: #38bdf8; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

.table-responsive { overflow-x: auto; }
.user-table { width: 100%; border-collapse: collapse; text-align: left; }
.user-table th {
  background: rgba(11, 17, 32, 0.95);
  padding: 0.85rem 1rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #d4af37;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.user-table td {
  padding: 0.85rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.user-row:hover td { background: rgba(255, 255, 255, 0.03); }

.id-badge { background: rgba(255, 255, 255, 0.08); color: #94a3b8; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.82rem; }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.92rem;
  color: #ffffff;
  flex-shrink: 0;
}
.avatar-Admin { background: linear-gradient(135deg, #ef4444, #f43f5e); }
.avatar-HDV { background: linear-gradient(135deg, #0284c7, #0ea5e9); }
.avatar-KhachHang { background: linear-gradient(135deg, #10b981, #14b8a6); }
.avatar-NhaCungCap { background: linear-gradient(135deg, #f59e0b, #d97706); }

.user-username { color: #ffffff; font-size: 0.9rem; font-weight: 700; }
.super-admin-tag {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  color: #fde047;
  background: rgba(212, 175, 55, 0.2);
  padding: 1px 6px;
  border-radius: 10px;
  margin-left: 6px;
}
.user-name { font-weight: 600; color: #cbd5e1; }

.contact-info { display: flex; flex-direction: column; gap: 0.2rem; font-size: 0.8rem; color: #94a3b8; max-width: 170px; }
.contact-item { display: inline-flex; align-items: center; gap: 0.35rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}
.role-Admin { background: rgba(244, 63, 94, 0.18); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.35); }
.role-HDV { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.role-KhachHang { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.role-NhaCungCap { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }

.status-pill {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}
.st-active { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.st-locked { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }

.date-text { font-size: 0.82rem; color: #94a3b8; }

.action-cell { display: flex; justify-content: center; align-items: center; gap: 5px; flex-wrap: nowrap; }
.current-user-tag {
  background: rgba(223, 169, 116, 0.2);
  color: #dfa974;
  border: 1px solid rgba(223, 169, 116, 0.35);
  font-size: 0.74rem;
  font-weight: 700;
  padding: 0.25rem 0.55rem;
  border-radius: 0.4rem;
}

.btn-action-sm {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.32rem 0.6rem;
  border-radius: 0.45rem;
  font-size: 0.76rem;
  font-weight: 600;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.18s;
  white-space: nowrap;
}

.btn-edit { background: rgba(56, 189, 248, 0.12); color: #38bdf8; border-color: rgba(56, 189, 248, 0.25); }
.btn-edit:hover { background: rgba(56, 189, 248, 0.22); color: #7dd3fc; }

.btn-pwd { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border-color: rgba(245, 158, 11, 0.25); }
.btn-pwd:hover { background: rgba(245, 158, 11, 0.22); color: #fde68a; }

.btn-lock { background: rgba(239, 68, 68, 0.12); color: #f87171; border-color: rgba(239, 68, 68, 0.25); }
.btn-lock:hover:not(:disabled) { background: rgba(239, 68, 68, 0.22); color: #fca5a5; }

.btn-unlock { background: rgba(16, 185, 129, 0.12); color: #34d399; border-color: rgba(16, 185, 129, 0.25); }
.btn-unlock:hover:not(:disabled) { background: rgba(16, 185, 129, 0.22); color: #6ee7b7; }

.btn-delete { background: rgba(255, 255, 255, 0.05); color: #94a3b8; border-color: rgba(255, 255, 255, 0.12); }
.btn-delete:hover:not(:disabled) { background: rgba(239, 68, 68, 0.2); color: #ef4444; border-color: rgba(239, 68, 68, 0.35); }

.btn-action-sm:disabled { opacity: 0.45; cursor: wait; }

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }

/* PAGINATION BAR */
.table-pagination-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: rgba(11, 17, 32, 0.85);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  flex-wrap: wrap;
  gap: 12px;
}
.pagination-info { font-size: 0.82rem; color: #94a3b8; }
.pagination-controls { display: flex; align-items: center; gap: 4px; }
.page-btn {
  min-width: 32px;
  height: 32px;
  padding: 0 6px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.15s;
}
.page-btn:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.14);
  color: #fff;
  border-color: rgba(255, 255, 255, 0.25);
}
.page-btn.active {
  background: #dfa974;
  color: #0f172a;
  border-color: #dfa974;
  font-weight: 700;
}
.page-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.page-ellipsis { color: #64748b; padding: 0 4px; font-size: 0.82rem; }

/* MODALS */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.7);
  backdrop-filter: blur(8px);
  z-index: 1200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.2s ease-out;
}
.modal-card {
  width: 100%;
  max-width: 580px;
  background: #151b28;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 1.15rem;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.6);
  overflow: hidden;
  animation: slideUp 0.22s ease-out;
}
.modal-card.modal-sm { max-width: 460px; }

.modal-header {
  padding: 1.25rem 1.5rem;
  background: rgba(255, 255, 255, 0.03);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-title { font-size: 1.15rem; font-weight: 700; color: #fff; margin: 0; display: flex; align-items: center; }
.btn-close-modal {
  background: transparent;
  border: none;
  color: #94a3b8;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 6px;
  transition: all 0.15s;
}
.btn-close-modal:hover { color: #fff; background: rgba(255, 255, 255, 0.08); }

.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.modal-form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.82rem; font-weight: 600; color: #cbd5e1; }
.form-help { font-size: 0.72rem; color: #94a3b8; }
.modal-input, .modal-select {
  width: 100%;
  height: 42px;
  padding: 0 0.85rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(11, 17, 32, 0.85);
  color: #fff;
  font-size: 0.88rem;
  outline: none;
  transition: all 0.2s;
}
.modal-input:focus, .modal-select:focus {
  border-color: #dfa974;
  box-shadow: 0 0 0 3px rgba(223, 169, 116, 0.2);
}
.modal-input:disabled, .modal-select:disabled {
  background: rgba(0, 0, 0, 0.3);
  color: #64748b;
  cursor: not-allowed;
}

.input-password-wrap { position: relative; display: flex; align-items: center; }
.btn-pwd-eye {
  position: absolute;
  right: 0.6rem;
  background: none;
  border: none;
  color: #94a3b8;
  cursor: pointer;
  font-size: 1rem;
}

.reset-target-box {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  margin-bottom: 0.5rem;
}
.r-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #0f172a;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}
.r-info { line-height: 1.3; }
.r-name { font-weight: 700; color: #fff; font-size: 0.95rem; }
.r-user { font-size: 0.78rem; color: #94a3b8; }

.btn-inline-gen {
  background: none;
  border: none;
  color: #dfa974;
  font-size: 0.76rem;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
  text-decoration: underline;
}
.pwd-copy-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 8px 12px;
  border-radius: 8px;
  background: rgba(223, 169, 116, 0.1);
  border: 1px dashed rgba(223, 169, 116, 0.35);
  font-size: 0.82rem;
  color: #cbd5e1;
}
.text-gold { color: #fde047; }
.btn-copy {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #fff;
  font-size: 0.75rem;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  padding-top: 0.5rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.btn-cancel {
  padding: 0.65rem 1.15rem;
  border-radius: 0.55rem;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  color: #cbd5e1;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #fff; }

.btn-submit {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.65rem 1.35rem;
  border-radius: 0.55rem;
  background: linear-gradient(135deg, #dfa974 0%, #b27a3c 100%);
  border: none;
  color: #0f172a;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.18s;
}
.btn-submit:hover:not(:disabled) { filter: brightness(1.08); }
.btn-submit-blue { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; }
.btn-submit-gold { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #0f172a; }
.btn-submit:disabled { opacity: 0.6; cursor: wait; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

@media (max-width: 1100px) {
  .filter-grid { grid-template-columns: 1fr 1fr; }
  .stats-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
}
@media (max-width: 650px) {
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .header-actions { width: 100%; flex-wrap: wrap; }
  .filter-grid { grid-template-columns: 1fr; }
  .modal-form-grid { grid-template-columns: 1fr; }
}
</style>
