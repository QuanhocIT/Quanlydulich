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
          Quản lý danh sách tài khoản, kiểm soát trạng thái hoạt động và vai trò người dùng trong hệ thống
        </p>
      </div>
      <div class="header-actions">
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
          <select v-model="selectedRole" @change="fetchUsers" class="form-select-custom">
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
          <select v-model="selectedStatus" @change="fetchUsers" class="form-select-custom">
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
        <span>Tổng cộng: <strong>{{ filteredUsers.length }}</strong> tài khoản</span>
        <span v-if="isLoading" class="loading-tag">
          <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
        </span>
      </div>

      <div class="table-responsive">
        <table class="user-table">
          <thead>
            <tr>
              <th style="width: 70px;" class="text-center">ID</th>
              <th>Tài Khoản</th>
              <th>Họ & Tên</th>
              <th>Liên Hệ</th>
              <th style="width: 140px;" class="text-center">Vai Trò</th>
              <th style="width: 130px;" class="text-center">Trạng Thái</th>
              <th style="width: 150px;">Ngày Tạo</th>
              <th style="width: 170px;" class="text-center">Hành Động</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="u in filteredUsers" :key="u.id" class="user-row">
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
                  <span class="contact-item" v-if="u.email">
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

              <!-- Action -->
              <td class="text-center">
                <div class="action-cell">
                  <span v-if="Number(u.id) === Number(currentUserId)" class="current-user-tag">
                    <i class="bi bi-person-check-fill me-1"></i>Bạn
                  </span>
                  <button 
                    v-else
                    @click="toggleUserStatus(u)"
                    :disabled="u._isUpdating"
                    class="btn-toggle"
                    :class="u.trang_thai === 'BiKhoa' ? 'btn-unlock' : 'btn-lock'"
                    :title="u.trang_thai === 'BiKhoa' ? 'Mở khóa tài khoản này' : 'Khóa tài khoản này'"
                  >
                    <i class="bi" :class="u.trang_thai === 'BiKhoa' ? 'bi-unlock' : 'bi-lock'"></i>
                    <span>{{ u.trang_thai === 'BiKhoa' ? 'Mở Khóa' : 'Khóa' }}</span>
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-if="filteredUsers.length === 0 && !isLoading">
              <td colspan="8" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy người dùng nào phù hợp với điều kiện tìm kiếm.
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
const isLoading = ref(false);
const searchQuery = ref('');
const selectedRole = ref('');
const selectedStatus = ref('');
const currentUserId = ref(0);
const csrfToken = ref('');
const csrfGlobal = ref('');

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
    fetchUsers();
  }, 350);
}

function clearSearch() {
  searchQuery.value = '';
  fetchUsers();
}

function resetFilters() {
  searchQuery.value = '';
  selectedRole.value = '';
  selectedStatus.value = '';
  fetchUsers();
}

function showToast(msg, type = 'success') {
  toastMessage.value = msg;
  toastType.value = type;
  setTimeout(() => {
    toastMessage.value = '';
  }, 4000);
}

const filteredUsers = computed(() => {
  return users.value;
});

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
        // Refresh stats
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
}
.loading-tag { color: #38bdf8; font-weight: 600; display: inline-flex; align-items: center; gap: 0.4rem; }
.spin { animation: spin 1s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

.table-responsive { overflow-x: auto; }
.user-table { width: 100%; border-collapse: collapse; text-align: left; }
.user-table th {
  background: rgba(11, 17, 32, 0.95);
  padding: 0.85rem 1.15rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #d4af37;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.user-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.user-row:hover td { background: rgba(255, 255, 255, 0.03); }

.id-badge { background: rgba(255, 255, 255, 0.08); color: #94a3b8; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.82rem; }

.user-cell { display: flex; align-items: center; gap: 0.75rem; }
.user-avatar {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.95rem;
  color: #ffffff;
  flex-shrink: 0;
}
.avatar-Admin { background: linear-gradient(135deg, #ef4444, #f43f5e); }
.avatar-HDV { background: linear-gradient(135deg, #0284c7, #0ea5e9); }
.avatar-KhachHang { background: linear-gradient(135deg, #10b981, #14b8a6); }
.avatar-NhaCungCap { background: linear-gradient(135deg, #f59e0b, #d97706); }

.user-username { color: #ffffff; font-size: 0.92rem; font-weight: 700; }
.user-name { font-weight: 600; color: #cbd5e1; }

.contact-info { display: flex; flex-direction: column; gap: 0.2rem; font-size: 0.82rem; color: #94a3b8; }
.contact-item { display: inline-flex; align-items: center; gap: 0.35rem; }

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.7rem;
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

.date-text { font-size: 0.85rem; color: #94a3b8; }

.action-cell { display: flex; justify-content: center; align-items: center; }
.current-user-tag {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 0.35rem 0.75rem;
  border-radius: 0.45rem;
}

.btn-toggle {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.4rem 0.85rem;
  border-radius: 0.45rem;
  font-size: 0.82rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-lock { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); }
.btn-lock:hover:not(:disabled) { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }
.btn-unlock { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); }
.btn-unlock:hover:not(:disabled) { background: rgba(16, 185, 129, 0.25); color: #6ee7b7; }
.btn-toggle:disabled { opacity: 0.5; cursor: wait; }

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }

@media (max-width: 1100px) {
  .filter-grid { grid-template-columns: 1fr 1fr; }
  .stats-grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); }
}
@media (max-width: 650px) {
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
  .filter-grid { grid-template-columns: 1fr; }
}
</style>
