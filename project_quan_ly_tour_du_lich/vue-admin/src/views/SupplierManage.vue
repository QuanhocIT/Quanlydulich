<template>
  <div class="vue-admin-supplier-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-building"></i>
          <span>Mạng Lưới Đối Tác</span>
        </div>
        <h1 class="header-title">Quản Lý Nhà Cung Cấp</h1>
        <p class="header-subtitle">
          Quản lý đối tác khách sạn, nhà hàng, vận chuyển, vé, visa, bảo hiểm và đánh giá chất lượng dịch vụ
        </p>
      </div>
      <div class="header-actions">
        <a href="index.php?act=admin/lichSuXoaNhaCungCap" class="btn-history">
          <i class="bi bi-clock-history"></i>
          <span>Lịch Sử Xóa</span>
        </a>
        <button @click="openCreateModal" class="btn-create">
          <i class="bi bi-plus-lg"></i>
          <span>Thêm Đối Tác</span>
        </button>
      </div>
    </header>

    <!-- TWO COLUMN MASTER-DETAIL LAYOUT -->
    <div class="master-detail-layout">
      <!-- LEFT: SUPPLIERS LIST -->
      <aside class="sidebar-panel">
        <div class="panel-header">
          <div class="panel-title-wrap">
            <h3 class="panel-title"><i class="bi bi-card-list me-1"></i>Danh Sách Đối Tác</h3>
            <span class="badge-counter">{{ filteredSuppliers.length }}</span>
          </div>

          <!-- Search Input -->
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Tìm tên đối tác, địa chỉ..." 
              class="panel-search"
            />
          </div>

          <!-- Service Type Filter Pills -->
          <div class="service-pills">
            <button 
              @click="filterType = ''" 
              class="type-pill" 
              :class="{ active: filterType === '' }"
            >
              Tất cả
            </button>
            <button 
              v-for="(label, key) in serviceTypeMap" 
              :key="key"
              @click="filterType = key" 
              class="type-pill" 
              :class="{ active: filterType === key }"
            >
              {{ label }}
            </button>
          </div>
        </div>

        <!-- Supplier Items List -->
        <div class="supplier-items-scroll">
          <div 
            v-for="s in filteredSuppliers" 
            :key="s.id_nha_cung_cap"
            @click="selectSupplier(s.id_nha_cung_cap)"
            class="supplier-card"
            :class="{ active: currentSelectedId == s.id_nha_cung_cap }"
          >
            <div class="card-head">
              <strong class="supplier-name">{{ s.ten_don_vi }}</strong>
              <span class="type-tag" :class="'type-' + s.loai_dich_vu">
                {{ formatServiceType(s.loai_dich_vu) }}
              </span>
            </div>
            <div class="card-meta">
              <span class="meta-item"><i class="bi bi-geo-alt"></i> {{ s.dia_chi || 'Chưa cập nhật' }}</span>
              <span class="meta-item" v-if="s.lien_he"><i class="bi bi-telephone"></i> {{ s.lien_he }}</span>
            </div>
            <div class="card-footer" v-if="s.danh_gia_tb">
              <span class="rating-badge">
                <i class="bi bi-star-fill"></i> {{ Number(s.danh_gia_tb).toFixed(1) }}/5
              </span>
            </div>
          </div>

          <div v-if="filteredSuppliers.length === 0" class="empty-list">
            <i class="bi bi-inbox fs-3 text-muted d-block mb-2"></i>
            Không tìm thấy nhà cung cấp nào.
          </div>
        </div>
      </aside>

      <!-- RIGHT: SELECTED SUPPLIER DETAILS -->
      <main class="detail-panel">
        <div v-if="selectedSupplier" class="detail-content">
          <!-- Detail Header Card -->
          <div class="supplier-main-card">
            <div class="main-card-header">
              <div class="header-info">
                <div class="title-row">
                  <h2 class="supplier-title">{{ selectedSupplier.ten_don_vi }}</h2>
                  <span class="type-tag-lg" :class="'type-' + selectedSupplier.loai_dich_vu">
                    {{ formatServiceType(selectedSupplier.loai_dich_vu) }}
                  </span>
                </div>
                <div class="supplier-contacts">
                  <span class="contact-pill" v-if="selectedSupplier.dia_chi">
                    <i class="bi bi-geo-alt-fill text-danger"></i> {{ selectedSupplier.dia_chi }}
                  </span>
                  <span class="contact-pill" v-if="selectedSupplier.lien_he">
                    <i class="bi bi-telephone-fill text-success"></i> {{ selectedSupplier.lien_he }}
                  </span>
                  <span class="contact-pill" v-if="selectedSupplier.danh_gia_tb">
                    <i class="bi bi-star-fill text-warning"></i> Đánh giá TB: {{ Number(selectedSupplier.danh_gia_tb).toFixed(1) }} / 5.0
                  </span>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="main-card-actions">
                <button @click="openEditModal" class="btn-action-edit">
                  <i class="bi bi-pencil-square"></i>
                  <span>Sửa Thông Tin</span>
                </button>
                <form 
                  method="POST" 
                  action="index.php?act=admin/deleteNhaCungCap" 
                  style="display:inline; margin:0;"
                  @submit="confirmDeleteSupplier"
                >
                  <input type="hidden" name="_csrf_token" :value="csrfToken">
                  <input type="hidden" name="id_nha_cung_cap" :value="selectedSupplier.id_nha_cung_cap">
                  <button type="submit" class="btn-action-delete">
                    <i class="bi bi-trash"></i>
                    <span>Xóa Đối Tác</span>
                  </button>
                </form>
              </div>
            </div>

            <!-- Description & Service Categories -->
            <div class="supplier-description-box" v-if="selectedSupplier.mo_ta">
              <h4 class="box-title"><i class="bi bi-info-circle me-1"></i>Mô Tả Năng Lực Cung Ứng</h4>
              <p class="description-text">{{ selectedSupplier.mo_ta }}</p>
            </div>

            <!-- Distinct Service Categories -->
            <div class="categories-box" v-if="serviceTypes.length > 0">
              <h4 class="box-title"><i class="bi bi-tags me-1"></i>Danh Mục Dịch Vụ Đã Cung Ứng</h4>
              <div class="tags-row">
                <span v-for="t in serviceTypes" :key="t" class="service-category-tag">
                  <i class="bi bi-check2"></i> {{ formatServiceType(t) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Services List Section -->
          <div class="services-list-card">
            <div class="card-head-bar">
              <h3 class="card-heading">
                <i class="bi bi-layers-fill text-primary me-2"></i>
                Danh Sách Dịch Vụ Cung Cấp
              </h3>
              <span class="badge-total-services">{{ supplierServices.length }} dịch vụ</span>
            </div>

            <div class="table-responsive">
              <table class="services-table">
                <thead>
                  <tr>
                    <th>Tên Dịch Vụ</th>
                    <th style="width: 140px;">Loại Dịch Vụ</th>
                    <th style="width: 150px;" class="text-end">Đơn Giá</th>
                    <th style="width: 140px;" class="text-center">Trạng Thái</th>
                    <th style="width: 120px;" class="text-center">Chi Tiết</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="sv in supplierServices" :key="sv.id_dich_vu" class="service-row">
                    <td>
                      <strong class="service-name">{{ sv.ten_dich_vu || 'Dịch vụ #' + sv.id_dich_vu }}</strong>
                      <span v-if="sv.mo_ta" class="service-desc">{{ sv.mo_ta }}</span>
                    </td>
                    <td>
                      <span class="service-type-badge">{{ formatServiceType(sv.loai_dich_vu) }}</span>
                    </td>
                    <td class="text-end">
                      <strong class="service-price">{{ formatCurrency(sv.gia_mac_dinh || sv.don_gia) }}</strong>
                    </td>
                    <td class="text-center">
                      <span class="service-status-pill" :class="sv.trang_thai === 'HoatDong' ? 'st-active' : 'st-paused'">
                        {{ sv.trang_thai === 'HoatDong' ? 'Hoạt động' : 'Tạm dừng' }}
                      </span>
                    </td>
                    <td class="text-center">
                      <a :href="'index.php?act=admin/chiTietDichVu&id=' + sv.id_dich_vu" class="btn-view-service">
                        <i class="bi bi-eye"></i> Xem
                      </a>
                    </td>
                  </tr>

                  <tr v-if="supplierServices.length === 0">
                    <td colspan="5" class="empty-cell">
                      <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                      Đối tác này chưa có danh mục dịch vụ cụ thể nào được khai báo.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- No Supplier Selected State -->
        <div v-else class="empty-selection-box">
          <i class="bi bi-building fs-1 text-muted d-block mb-3"></i>
          <h4 class="fw-bold text-dark">Chưa chọn nhà cung cấp nào</h4>
          <p class="text-secondary">Vui lòng chọn một đối tác ở danh sách bên trái để xem hồ sơ và dịch vụ.</p>
        </div>
      </main>
    </div>

    <!-- CREATE MODAL -->
    <div v-if="isCreateModalOpen" class="modal-backdrop" @click.self="isCreateModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title"><i class="bi bi-plus-circle me-1"></i>Thêm Nhà Cung Cấp Mới</h3>
          <button @click="isCreateModalOpen = false" class="btn-close-modal"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?act=admin/addNhacungcap" class="modal-body">
          <input type="hidden" name="_csrf_token" :value="csrfToken">

          <div class="modal-form-group">
            <label class="form-label">Tên Đơn Vị / Đối Tác</label>
            <input name="ten_don_vi" required type="text" class="modal-input" placeholder="VD: Khách sạn Mường Thanh..." />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Loại Dịch Vụ Chính</label>
            <select name="loai_dich_vu" required class="modal-select">
              <option value="KhachSan">Khách sạn / Lưu trú</option>
              <option value="NhaHang">Nhà hàng / Ẩm thực</option>
              <option value="Xe">Xe vận chuyển du lịch</option>
              <option value="Ve">Vé máy bay / Tàu hỏa</option>
              <option value="Visa">Dịch vụ Visa</option>
              <option value="BaoHiem">Bảo hiểm du lịch</option>
              <option value="Khac">Dịch vụ khác</option>
            </select>
          </div>

          <div class="modal-form-group">
            <label class="form-label">Địa Chỉ Trụ Sở</label>
            <input name="dia_chi" type="text" class="modal-input" placeholder="Số nhà, đường, quận/huyện, tỉnh/thành..." />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Thông Tin Liên Hệ (SĐT / Email)</label>
            <input name="lien_he" type="text" class="modal-input" placeholder="VD: 0912345678 / contact@partner.com" />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Mô Tả Năng Lực</label>
            <textarea name="mo_ta" rows="3" class="modal-textarea" placeholder="Giới thiệu năng lực, chính sách chiết khấu..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" @click="isCreateModalOpen = false" class="btn-cancel">Hủy</button>
            <button type="submit" class="btn-submit">Thêm Mới</button>
          </div>
        </form>
      </div>
    </div>

    <!-- EDIT MODAL -->
    <div v-if="isEditModalOpen && selectedSupplier" class="modal-backdrop" @click.self="isEditModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3 class="modal-title"><i class="bi bi-pencil me-1"></i>Sửa Nhà Cung Cấp</h3>
          <button @click="isEditModalOpen = false" class="btn-close-modal"><i class="bi bi-x-lg"></i></button>
        </div>
        <form method="POST" action="index.php?act=admin/updateNhaCungCap" class="modal-body">
          <input type="hidden" name="_csrf_token" :value="csrfToken">
          <input type="hidden" name="id_nha_cung_cap" :value="selectedSupplier.id_nha_cung_cap">

          <div class="modal-form-group">
            <label class="form-label">Tên Đơn Vị</label>
            <input v-model="editForm.ten_don_vi" name="ten_don_vi" required type="text" class="modal-input" />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Loại Dịch Vụ</label>
            <select v-model="editForm.loai_dich_vu" name="loai_dich_vu" required class="modal-select">
              <option value="KhachSan">Khách sạn</option>
              <option value="NhaHang">Nhà hàng</option>
              <option value="Xe">Xe vận chuyển</option>
              <option value="Ve">Vé máy bay / Tàu</option>
              <option value="Visa">Visa</option>
              <option value="BaoHiem">Bảo hiểm</option>
              <option value="Khac">Khác</option>
            </select>
          </div>

          <div class="modal-form-group">
            <label class="form-label">Địa Chỉ</label>
            <input v-model="editForm.dia_chi" name="dia_chi" type="text" class="modal-input" />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Liên Hệ</label>
            <input v-model="editForm.lien_he" name="lien_he" type="text" class="modal-input" />
          </div>

          <div class="modal-form-group">
            <label class="form-label">Mô Tả</label>
            <textarea v-model="editForm.mo_ta" name="mo_ta" rows="3" class="modal-textarea"></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" @click="isEditModalOpen = false" class="btn-cancel">Hủy</button>
            <button type="submit" class="btn-submit">Lưu Thay Đổi</button>
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

const serviceTypeMap = {
  KhachSan: 'Khách sạn',
  NhaHang: 'Nhà hàng',
  Xe: 'Vận chuyển',
  Ve: 'Vé máy bay/tàu',
  Visa: 'Visa',
  BaoHiem: 'Bảo hiểm',
  Khac: 'Khác'
};

// State
const searchQuery = ref('');
const filterType = ref('');
const suppliers = ref([]);
const currentSelectedId = ref(null);
const selectedSupplier = ref(null);
const serviceTypes = ref([]);
const supplierServices = ref([]);
const csrfToken = ref('');

// Modals
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const editForm = ref({
  ten_don_vi: '',
  loai_dich_vu: 'KhachSan',
  dia_chi: '',
  lien_he: '',
  mo_ta: ''
});

const filteredSuppliers = computed(() => {
  let list = suppliers.value || [];
  if (filterType.value) {
    list = list.filter(s => s.loai_dich_vu === filterType.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(s => {
      const name = (s.ten_don_vi || '').toLowerCase();
      const addr = (s.dia_chi || '').toLowerCase();
      return name.includes(q) || addr.includes(q);
    });
  }
  return list;
});

function applyData(data) {
  if (!data) return;
  suppliers.value = data.suppliers || [];
  currentSelectedId.value = data.selectedId || (suppliers.value[0]?.id_nha_cung_cap ?? null);
  selectedSupplier.value = data.selectedSupplier || null;
  serviceTypes.value = data.serviceTypes || [];
  supplierServices.value = data.supplierServices || [];
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function selectSupplier(id) {
  currentSelectedId.value = id;
  try {
    const res = await fetch(`index.php?act=admin/apiNhaCungCapData&id=${id}`, {
      headers: { 'Accept': 'application/json' }
    });
    if (res.ok) {
      const json = await res.json();
      if (json.success && json.data) {
        selectedSupplier.value = json.data.selectedSupplier;
        serviceTypes.value = json.data.serviceTypes || [];
        supplierServices.value = json.data.supplierServices || [];
      }
    }
  } catch (err) {
    console.error(err);
  }
}

function openCreateModal() {
  isCreateModalOpen.value = true;
}

function openEditModal() {
  if (!selectedSupplier.value) return;
  editForm.value = {
    ten_don_vi: selectedSupplier.value.ten_don_vi || '',
    loai_dich_vu: selectedSupplier.value.loai_dich_vu || 'KhachSan',
    dia_chi: selectedSupplier.value.dia_chi || '',
    lien_he: selectedSupplier.value.lien_he || '',
    mo_ta: selectedSupplier.value.mo_ta || ''
  };
  isEditModalOpen.value = true;
}

function confirmDeleteSupplier(e) {
  if (!confirm(`Bạn có chắc chắn muốn xóa đối tác "${selectedSupplier.value?.ten_don_vi}"?`)) {
    e.preventDefault();
  }
}

function formatServiceType(t) {
  return serviceTypeMap[t] || t || 'Khác';
}

function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    selectSupplier(currentSelectedId.value);
  }
});
</script>

<style scoped>
.vue-admin-supplier {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* PAGE HEADER */
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

.btn-create {
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
.btn-create:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(212, 175, 55, 0.45); color: #0b1120; }

.btn-history {
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
.btn-history:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }

/* MASTER-DETAIL LAYOUT */
.master-detail-layout {
  display: grid;
  grid-template-columns: 360px 1fr;
  gap: 1.5rem;
  align-items: flex-start;
}

/* SIDEBAR PANEL */
.sidebar-panel {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.panel-header {
  padding: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  background: rgba(11, 17, 32, 0.9);
}
.panel-title-wrap { display: flex; justify-content: space-between; align-items: center; }
.panel-title { font-size: 1rem; font-weight: 800; margin: 0; color: #ffffff; }
.badge-counter { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; font-size: 0.75rem; font-weight: 700; padding: 0.2rem 0.5rem; border-radius: 1rem; }

.search-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
}
.search-input-wrap i { position: absolute; left: 0.75rem; color: #94a3b8; }
.panel-search {
  width: 100%;
  height: 38px;
  padding: 0 0.75rem 0 2.2rem;
  border-radius: 0.55rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  font-size: 0.85rem;
  outline: none;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
  transition: all 0.2s;
}
.panel-search:focus { border-color: #d4af37; background: rgba(11, 17, 32, 0.95); }
.panel-search::placeholder { color: #64748b; }

.service-pills {
  display: flex;
  gap: 0.35rem;
  overflow-x: auto;
  padding-bottom: 0.2rem;
}
.type-pill {
  padding: 0.25rem 0.6rem;
  border-radius: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  font-size: 0.75rem;
  font-weight: 600;
  white-space: nowrap;
  cursor: pointer;
  transition: all 0.2s;
}
.type-pill:hover { background: rgba(255, 255, 255, 0.12); color: #ffffff; }
.type-pill.active { background: #d4af37; color: #0b1120; border-color: #d4af37; font-weight: 700; }

.supplier-items-scroll {
  max-height: 750px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.supplier-card {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}
.supplier-card:hover { background: rgba(255, 255, 255, 0.03); }
.supplier-card.active { background: rgba(212, 175, 55, 0.1); border-left: 4px solid #d4af37; }

.card-head { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; }
.supplier-name { color: #ffffff; font-size: 0.92rem; font-weight: 700; }
.type-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
  white-space: nowrap;
}
.card-meta { display: flex; flex-direction: column; gap: 0.2rem; font-size: 0.78rem; color: #94a3b8; }
.meta-item { display: inline-flex; align-items: center; gap: 0.3rem; }
.rating-badge { font-size: 0.75rem; font-weight: 700; color: #fbbf24; }

/* TYPE TAG COLORS */
.type-KhachSan { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.type-NhaHang { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.type-Xe { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.type-Ve { background: rgba(99, 102, 241, 0.18); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.35); }
.type-Visa { background: rgba(244, 63, 94, 0.18); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.35); }
.type-BaoHiem { background: rgba(20, 184, 166, 0.18); color: #5eead4; border: 1px solid rgba(20, 184, 166, 0.35); }
.type-Khac { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; border: 1px solid rgba(255, 255, 255, 0.12); }

/* DETAIL PANEL */
.detail-panel { display: flex; flex-direction: column; gap: 1.5rem; }
.supplier-main-card, .services-list-card {
  background: rgba(22, 31, 46, 0.75);
  border-radius: 1.15rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  backdrop-filter: blur(12px);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}
.main-card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 1rem;
  padding-bottom: 1.25rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.title-row { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
.supplier-title { font-size: 1.5rem; font-weight: 800; color: #ffffff; margin: 0; }
.type-tag-lg { font-size: 0.82rem; font-weight: 700; padding: 0.3rem 0.75rem; border-radius: 2rem; }

.supplier-contacts { display: flex; flex-wrap: wrap; gap: 0.85rem; }
.contact-pill {
  font-size: 0.85rem;
  color: #cbd5e1;
  background: rgba(11, 17, 32, 0.8);
  padding: 0.35rem 0.75rem;
  border-radius: 0.5rem;
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.main-card-actions { display: flex; gap: 0.5rem; }
.btn-action-edit {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.5rem 0.95rem;
  border-radius: 0.5rem;
  background: rgba(14, 165, 233, 0.15);
  color: #38bdf8;
  font-size: 0.85rem;
  font-weight: 700;
  border: 1px solid rgba(14, 165, 233, 0.25);
  cursor: pointer;
}
.btn-action-edit:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }
.btn-action-delete {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.5rem 0.95rem;
  border-radius: 0.5rem;
  background: rgba(239, 68, 68, 0.15);
  color: #f87171;
  font-size: 0.85rem;
  font-weight: 700;
  border: 1px solid rgba(239, 68, 68, 0.25);
  cursor: pointer;
}
.btn-action-delete:hover { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }

.box-title { font-size: 0.95rem; font-weight: 700; color: #ffffff; margin: 0 0 0.5rem; }
.description-text { font-size: 0.9rem; line-height: 1.6; color: #94a3b8; margin: 0; }

.tags-row { display: flex; flex-wrap: wrap; gap: 0.45rem; }
.service-category-tag {
  background: rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  font-size: 0.82rem;
  font-weight: 600;
  padding: 0.3rem 0.7rem;
  border-radius: 0.45rem;
}

/* SERVICES TABLE */
.card-head-bar { display: flex; justify-content: space-between; align-items: center; }
.card-heading { font-size: 1.15rem; font-weight: 800; color: #ffffff; margin: 0; }
.badge-total-services { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; font-size: 0.8rem; font-weight: 700; padding: 0.25rem 0.65rem; border-radius: 1rem; }

.table-responsive { overflow-x: auto; }
.services-table { width: 100%; border-collapse: collapse; text-align: left; }
.services-table th {
  background: rgba(11, 17, 32, 0.95);
  padding: 0.85rem 1rem;
  font-size: 0.8rem;
  font-weight: 700;
  color: #d4af37;
  text-transform: uppercase;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.services-table td {
  padding: 0.95rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.service-name { display: block; color: #ffffff; font-size: 0.92rem; font-weight: 600; }
.service-desc { font-size: 0.78rem; color: #94a3b8; }
.service-type-badge { font-size: 0.75rem; font-weight: 600; color: #cbd5e1; background: rgba(255, 255, 255, 0.08); padding: 0.2rem 0.5rem; border-radius: 0.35rem; }
.service-price { color: #34d399; font-size: 0.92rem; font-weight: 700; }

.service-status-pill {
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.2rem 0.55rem;
  border-radius: 2rem;
}
.st-active { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.st-paused { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }

.btn-view-service {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.35rem 0.65rem;
  border-radius: 0.4rem;
  background: rgba(14, 165, 233, 0.15);
  color: #38bdf8;
  border: 1px solid rgba(14, 165, 233, 0.25);
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
}
.btn-view-service:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }

.empty-cell, .empty-list, .empty-selection-box {
  padding: 3rem 1.5rem;
  text-align: center;
  color: #64748b;
}

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
  max-width: 520px;
  box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(16px);
  overflow: hidden;
}
.modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(11, 17, 32, 0.9);
}
.modal-title { font-size: 1.15rem; font-weight: 700; margin: 0; color: #ffffff; }
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

@media (max-width: 960px) {
  .master-detail-layout { grid-template-columns: 1fr; }
  .page-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
}
</style>
