<template>
  <div class="vue-admin-personnel-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-people-fill"></i>
          <span>Hồ Sơ & Điều Hành</span>
        </div>
        <h1 class="header-title">Quản Lý Nhân Sự</h1>
        <p class="header-subtitle">
          Quản lý đội ngũ hướng dẫn viên, nhân viên điều hành và các đối tác nhân sự trong hệ thống
        </p>
      </div>
      <div class="header-actions">
        <a href="index.php?act=admin/hdv_advanced" class="btn-action-outline">
          <i class="bi bi-compass"></i>
          <span>Quản Lý HDV</span>
        </a>
        <button @click="openCreateModal" class="btn-action-primary">
          <i class="bi bi-plus-lg"></i>
          <span>Thêm Nhân Sự</span>
        </button>
      </div>
    </header>

    <!-- 4 STATS CARDS -->
    <section class="stats-grid">
      <div class="stat-card stat-total">
        <div class="stat-content">
          <span class="stat-label">Tổng nhân sự</span>
          <strong class="stat-num">{{ stats.total }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-people-fill"></i></div>
      </div>

      <div class="stat-card stat-hdv">
        <div class="stat-content">
          <span class="stat-label">Hướng dẫn viên</span>
          <strong class="stat-num">{{ stats.hdv }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-compass-fill"></i></div>
      </div>

      <div class="stat-card stat-dieuhanh">
        <div class="stat-content">
          <span class="stat-label">Điều hành tour</span>
          <strong class="stat-num">{{ stats.dieuHanh }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-briefcase-fill"></i></div>
      </div>

      <div class="stat-card stat-supplier">
        <div class="stat-content">
          <span class="stat-label">Nhà cung cấp</span>
          <strong class="stat-num">{{ stats.nhaCungCap }}</strong>
        </div>
        <div class="stat-icon-box"><i class="bi bi-building-fill"></i></div>
      </div>
    </section>

    <!-- ROLE FILTER TABS -->
    <div class="role-tabs-bar">
      <button 
        @click="setRoleFilter('')" 
        class="role-tab" 
        :class="{ active: currentRoleFilter === '' }"
      >
        <i class="bi bi-grid-fill"></i>
        <span>Tất Cả</span>
        <span class="tab-badge">{{ stats.total }}</span>
      </button>
      <button 
        v-for="r in availableRoles" 
        :key="r"
        @click="setRoleFilter(r)" 
        class="role-tab" 
        :class="{ active: currentRoleFilter === r }"
      >
        <i class="bi" :class="getRoleIcon(r)"></i>
        <span>{{ formatRole(r) }}</span>
        <span class="tab-badge" v-if="getRoleCount(r) > 0">{{ getRoleCount(r) }}</span>
      </button>
    </div>

    <!-- FILTER TOOLBAR -->
    <section class="filter-card">
      <div class="filter-grid">
        <div class="filter-col search-col">
          <label class="filter-label"><i class="bi bi-search me-1"></i>Tìm kiếm nhân sự</label>
          <div class="input-icon-wrap">
            <i class="bi bi-search icon-left"></i>
            <input 
              v-model="searchQuery" 
              @input="onSearchInput"
              type="text" 
              class="form-input-custom" 
              placeholder="Tìm theo họ tên, email, số điện thoại, vai trò..."
            />
            <button v-if="searchQuery" @click="clearSearch" class="btn-clear-search">
              <i class="bi bi-x"></i>
            </button>
          </div>
        </div>

        <div class="filter-col action-col">
          <button @click="resetFilters" class="btn-reset" title="Khôi phục bộ lọc">
            <i class="bi bi-arrow-counterclockwise"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- PERSONNEL TABLE -->
    <section class="table-card">
      <div class="table-meta-bar">
        <span>Tổng cộng: <strong>{{ displayList.length }}</strong> nhân sự</span>
        <span v-if="isLoading" class="loading-tag">
          <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
        </span>
      </div>

      <div class="table-responsive">
        <table class="personnel-table">
          <thead>
            <tr>
              <th style="width: 70px;" class="text-center">ID</th>
              <th>Nhân Sự</th>
              <th style="width: 150px;" class="text-center">Vai Trò</th>
              <th>Thông Tin Liên Hệ</th>
              <th>Chuyên Môn & Kỹ Năng</th>
              <th style="width: 200px;" class="text-center">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="ns in displayList" :key="ns.nhan_su_id" class="personnel-row">
              <!-- ID -->
              <td class="text-center">
                <span class="id-badge">#{{ ns.nhan_su_id }}</span>
              </td>

              <!-- Name & Avatar -->
              <td>
                <div class="personnel-cell">
                  <div class="personnel-avatar" :class="'avatar-' + (ns.vai_tro || 'Khac')">
                    {{ (ns.ho_ten || 'N').charAt(0).toUpperCase() }}
                  </div>
                  <div class="personnel-info">
                    <strong class="personnel-name">{{ ns.ho_ten }}</strong>
                    <span class="personnel-sub" v-if="ns.ten_dang_nhap">
                      <i class="bi bi-person"></i> {{ ns.ten_dang_nhap }}
                    </span>
                  </div>
                </div>
              </td>

              <!-- Role -->
              <td class="text-center">
                <span class="role-badge" :class="'role-' + (ns.vai_tro || 'Khac')">
                  {{ formatRole(ns.vai_tro) }}
                </span>
              </td>

              <!-- Contact -->
              <td>
                <div class="contact-stack">
                  <span class="contact-line" v-if="ns.email">
                    <i class="bi bi-envelope"></i> {{ ns.email }}
                  </span>
                  <span class="contact-line" v-if="ns.so_dien_thoai">
                    <i class="bi bi-telephone"></i> {{ ns.so_dien_thoai }}
                  </span>
                  <span v-if="!ns.email && !ns.so_dien_thoai" class="text-muted">Chưa cập nhật</span>
                </div>
              </td>

              <!-- Skills & Experience -->
              <td>
                <div class="skill-stack">
                  <div v-if="ns.ngon_ngu" class="skill-item">
                    <i class="bi bi-translate"></i>
                    <span>{{ ns.ngon_ngu }}</span>
                  </div>
                  <div v-if="ns.kinh_nghiem" class="skill-item text-secondary">
                    <i class="bi bi-award"></i>
                    <span>{{ ns.kinh_nghiem }}</span>
                  </div>
                  <span v-if="!ns.ngon_ngu && !ns.kinh_nghiem" class="text-muted small">---</span>
                </div>
              </td>

              <!-- Actions -->
              <td class="text-center">
                <div class="action-buttons-group">
                  <!-- View profile -->
                  <a 
                    :href="'index.php?act=admin/nhanSu_chi_tiet&id=' + ns.nhan_su_id" 
                    class="btn-act btn-view" 
                    title="Xem chi tiết lý lịch"
                  >
                    <i class="bi bi-eye"></i>
                  </a>

                  <!-- View Salary -->
                  <a 
                    :href="'index.php?act=admin/chiTietLuong&nhan_su_id=' + ns.nhan_su_id" 
                    class="btn-act btn-salary" 
                    title="Bảng lương chi tiết"
                  >
                    <i class="bi bi-cash-stack"></i>
                  </a>

                  <!-- Edit -->
                  <button 
                    @click="openEditModal(ns)" 
                    class="btn-act btn-edit" 
                    title="Chỉnh sửa thông tin"
                  >
                    <i class="bi bi-pencil"></i>
                  </button>

                  <!-- Delete form -->
                  <form 
                    method="POST" 
                    action="index.php?act=admin/nhanSu_delete" 
                    style="display:inline; margin:0;"
                    @submit="confirmDelete($event, ns)"
                  >
                    <input type="hidden" name="_csrf_token" :value="csrfToken">
                    <input type="hidden" name="id" :value="ns.nhan_su_id">
                    <button type="submit" class="btn-act btn-delete" title="Xóa nhân sự">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-if="displayList.length === 0 && !isLoading">
              <td colspan="6" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy nhân sự nào phù hợp với bộ lọc.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- CREATE / EDIT MODAL -->
    <div v-if="isModalOpen" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title">
            <i class="bi" :class="editingId ? 'bi-pencil-square' : 'bi-person-plus-fill'"></i>
            {{ editingId ? 'Cập Nhật Nhân Sự' : 'Thêm Nhân Sự Mới' }}
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
              placeholder="VD: Tiếng Anh (IELTS 7.0), Tiếng Trung (HSK 5)..."
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
const currentRoleFilter = ref('');
const nhanSuList = ref([]);
const availableRoles = ref([]);
const dataByRole = ref({});
const csrfToken = ref('');

// Modal
const isModalOpen = ref(false);
const editingId = ref(null);
const formRole = ref('HDV');
const formNgonNgu = ref('');
const formKinhNghiem = ref('');
const formChungChi = ref('');
const availableUsers = ref([]);

let searchDebounce = null;

function onSearchInput() {
  clearTimeout(searchDebounce);
  searchDebounce = setTimeout(() => {
    fetchPersonnel();
  }, 350);
}

function clearSearch() {
  searchQuery.value = '';
  fetchPersonnel();
}

function setRoleFilter(role) {
  currentRoleFilter.value = role;
  fetchPersonnel();
}

function resetFilters() {
  searchQuery.value = '';
  currentRoleFilter.value = '';
  fetchPersonnel();
}

// Stats computed
const stats = computed(() => {
  const all = nhanSuList.value || [];
  return {
    total: all.length,
    hdv: all.filter(n => n.vai_tro === 'HDV').length,
    dieuHanh: all.filter(n => n.vai_tro === 'DieuHanh').length,
    nhaCungCap: all.filter(n => n.vai_tro === 'NhaCungCap').length,
  };
});

function getRoleCount(r) {
  return (nhanSuList.value || []).filter(n => n.vai_tro === r).length;
}

const displayList = computed(() => {
  let list = nhanSuList.value || [];
  if (currentRoleFilter.value) {
    list = list.filter(n => n.vai_tro === currentRoleFilter.value);
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

function applyData(data) {
  if (!data) return;
  nhanSuList.value = data.nhanSuList || [];
  if (data.roles) availableRoles.value = data.roles;
  if (data.dataByRole) dataByRole.value = data.dataByRole;
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchPersonnel() {
  isLoading.value = true;
  const params = new URLSearchParams({
    act: 'admin/apiNhanSuList'
  });
  if (searchQuery.value.trim()) params.append('q', searchQuery.value.trim());
  if (currentRoleFilter.value) params.append('role', currentRoleFilter.value);

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

  // Load available users
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
    case 'HDV': return 'Hướng Dẫn Viên';
    case 'DieuHanh': return 'Điều Hành';
    case 'NhaCungCap': return 'Nhà Cung Cấp';
    case 'Khac': return 'Khác';
    default: return role || 'Khác';
  }
}

function getRoleIcon(role) {
  switch (role) {
    case 'HDV': return 'bi-compass';
    case 'DieuHanh': return 'bi-briefcase';
    case 'NhaCungCap': return 'bi-building';
    default: return 'bi-person';
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchPersonnel();
  }
});
</script>

<style scoped>
.vue-admin-personnel-manage {
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
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.35);
  border: 1px solid rgba(255, 255, 255, 0.08);
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
.header-actions { display: flex; gap: 0.75rem; align-items: center; }

.btn-action-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  padding: 0.75rem 1.25rem;
  border-radius: 0.65rem;
  font-weight: 700;
  font-size: 0.9rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s;
}
.btn-action-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45); color: #0b1120; }

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

/* STATS GRID */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1.15rem;
}
.stat-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.35rem;
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
.stat-hdv { border-left-color: #0ea5e9; }
.stat-dieuhanh { border-left-color: #10b981; }
.stat-supplier { border-left-color: #f59e0b; }

.stat-label { font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: #94a3b8; display: block; margin-bottom: 0.35rem; letter-spacing: 0.03em; }
.stat-num { font-size: 1.85rem; font-weight: 800; color: #ffffff; line-height: 1; }
.stat-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.45rem;
}
.stat-total .stat-icon-box { background: rgba(99, 102, 241, 0.16); color: #818cf8; }
.stat-hdv .stat-icon-box { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.stat-dieuhanh .stat-icon-box { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.stat-supplier .stat-icon-box { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }

/* ROLE TABS BAR */
.role-tabs-bar {
  display: flex;
  gap: 0.65rem;
  overflow-x: auto;
  padding-bottom: 0.25rem;
}
.role-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.15rem;
  border-radius: 0.65rem;
  background: rgba(22, 31, 46, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s;
}
.role-tab:hover { background: rgba(22, 31, 46, 0.95); border-color: rgba(255, 255, 255, 0.2); color: #ffffff; }
.role-tab.active {
  background: #d4af37;
  border-color: #d4af37;
  color: #0b1120;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
}
.tab-badge {
  background: rgba(0, 0, 0, 0.15);
  font-size: 0.75rem;
  padding: 0.15rem 0.45rem;
  border-radius: 1rem;
}
.role-tab.active .tab-badge { background: #0b1120; color: #d4af37; }

/* FILTER CARD */
.filter-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  padding: 1.15rem 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
}
.filter-grid {
  display: flex;
  gap: 1rem;
  align-items: flex-end;
}
.filter-col { display: flex; flex-direction: column; gap: 0.4rem; }
.search-col { flex: 1; }
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
.personnel-table { width: 100%; border-collapse: collapse; text-align: left; }
.personnel-table th {
  background: rgba(11, 17, 32, 0.95);
  padding: 0.85rem 1.15rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #d4af37;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.personnel-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.personnel-row:hover td { background: rgba(255, 255, 255, 0.03); }

.id-badge { background: rgba(255, 255, 255, 0.08); color: #94a3b8; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 0.35rem; font-size: 0.82rem; }

.personnel-cell { display: flex; align-items: center; gap: 0.75rem; }
.personnel-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  color: #ffffff;
  flex-shrink: 0;
}
.avatar-HDV { background: linear-gradient(135deg, #0284c7, #0ea5e9); }
.avatar-DieuHanh { background: linear-gradient(135deg, #10b981, #059669); }
.avatar-NhaCungCap { background: linear-gradient(135deg, #f59e0b, #d97706); }
.avatar-Khac { background: linear-gradient(135deg, #64748b, #475569); }

.personnel-name { color: #ffffff; font-size: 0.95rem; font-weight: 700; }
.personnel-sub { font-size: 0.78rem; color: #94a3b8; display: block; }

.role-badge {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: 2rem;
  font-size: 0.78rem;
  font-weight: 700;
  white-space: nowrap;
}
.role-HDV { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.role-DieuHanh { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.role-NhaCungCap { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.role-Khac { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.12); }

.contact-stack { display: flex; flex-direction: column; gap: 0.2rem; font-size: 0.82rem; color: #94a3b8; }
.contact-line { display: inline-flex; align-items: center; gap: 0.35rem; }

.skill-stack { display: flex; flex-direction: column; gap: 0.25rem; font-size: 0.82rem; color: #cbd5e1; }
.skill-item { display: inline-flex; align-items: center; gap: 0.35rem; }

.action-buttons-group { display: flex; gap: 0.35rem; justify-content: center; }
.btn-act {
  width: 34px;
  height: 34px;
  border-radius: 0.45rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  border: none;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-view { background: rgba(14, 165, 233, 0.15); color: #38bdf8; }
.btn-view:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }
.btn-salary { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.btn-salary:hover { background: rgba(16, 185, 129, 0.25); color: #ffffff; }
.btn-edit { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.btn-edit:hover { background: rgba(245, 158, 11, 0.25); color: #fde047; }
.btn-delete { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.btn-delete:hover { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }

/* MODAL */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(11, 17, 32, 0.8);
  backdrop-filter: blur(8px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
  padding: 1.5rem;
}
.modal-card {
  background: rgba(22, 31, 46, 0.95);
  border-radius: 1.25rem;
  width: 100%;
  max-width: 540px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(16px);
  overflow: hidden;
  animation: modalIn 0.2s ease-out;
}
@keyframes modalIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }

.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(11, 17, 32, 0.9);
}
.modal-title { font-size: 1.15rem; font-weight: 700; margin: 0; color: #ffffff; display: flex; align-items: center; gap: 0.5rem; }
.btn-close-modal { background: transparent; border: none; font-size: 1.1rem; color: #94a3b8; cursor: pointer; }
.btn-close-modal:hover { color: #ffffff; }

.modal-body { padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; }
.modal-form-group { display: flex; flex-direction: column; gap: 0.35rem; }
.form-label { font-size: 0.85rem; font-weight: 700; color: #cbd5e1; }
.modal-input, .modal-select, .modal-textarea {
  width: 100%;
  padding: 0.65rem 0.85rem;
  border-radius: 0.65rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.9rem;
  color: #ffffff;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  transition: all 0.2s;
}
.modal-input:focus, .modal-select:focus, .modal-textarea:focus {
  border-color: #d4af37;
  background: rgba(11, 17, 32, 0.95);
  box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
}
.modal-input::placeholder, .modal-textarea::placeholder {
  color: #64748b;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.btn-cancel {
  padding: 0.65rem 1.15rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  font-weight: 600;
  cursor: pointer;
}
.btn-cancel:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
.btn-submit {
  padding: 0.65rem 1.35rem;
  border-radius: 0.55rem;
  border: none;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  color: #0b1120;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
}
.btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45); }
</style>
