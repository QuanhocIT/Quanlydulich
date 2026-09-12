<template>
  <div class="vue-admin-review-manage">
    <!-- FLASH TOAST NOTIFICATION -->
    <transition name="fade">
      <div v-if="toast.visible" class="toast-popup" :class="toast.type">
        <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
        <span>{{ toast.message }}</span>
        <button @click="toast.visible = false" class="toast-close">&times;</button>
      </div>
    </transition>

    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-star-half"></i>
          <span>Trải Nghiệm Khách Hàng</span>
        </div>
        <h1 class="header-title">Quản Lý Đánh Giá & Phản Hồi</h1>
        <p class="header-subtitle">
          Theo dõi mức độ hài lòng của du khách, đối tác nhà cung cấp và đội ngũ nhân sự tour
        </p>
      </div>
      <div class="header-actions">
        <a href="index.php?act=admin/danhGia/baoCao" class="btn-report">
          <i class="bi bi-file-earmark-bar-graph"></i>
          <span>Báo Cáo Tổng Hợp</span>
        </a>
      </div>
    </header>

    <!-- STATS GRID -->
    <div class="stats-grid">
      <div class="stat-card border-blue">
        <div class="stat-card-inner">
          <div class="stat-info">
            <div class="stat-label">Tổng Đánh Giá</div>
            <div class="stat-value text-blue">{{ Number(stats.tong_danh_gia || 0).toLocaleString('vi-VN') }}</div>
            <div class="stat-sub">Phản hồi đã tiếp nhận</div>
          </div>
          <div class="stat-icon-wrap bg-blue-dim text-blue">
            <i class="bi bi-chat-quote-fill"></i>
          </div>
        </div>
      </div>

      <div class="stat-card border-gold">
        <div class="stat-card-inner">
          <div class="stat-info">
            <div class="stat-label">Điểm Trung Bình</div>
            <div class="stat-value text-gold">
              {{ Number(stats.diem_trung_binh || 0).toFixed(1) }} <i class="bi bi-star-fill text-gold font-sm"></i>
            </div>
            <div class="stat-sub">Thang điểm chuẩn 5.0</div>
          </div>
          <div class="stat-icon-wrap bg-gold-dim text-gold">
            <i class="bi bi-award-fill"></i>
          </div>
        </div>
      </div>

      <div class="stat-card border-green">
        <div class="stat-card-inner">
          <div class="stat-info">
            <div class="stat-label">Hài Lòng (≥ 4★)</div>
            <div class="stat-value text-green">{{ Number(stats.hai_long || 0).toLocaleString('vi-VN') }}</div>
            <div class="stat-sub">
              Chiếm {{ stats.tong_danh_gia > 0 ? ((stats.hai_long / stats.tong_danh_gia) * 100).toFixed(1) : 0 }}% tổng số
            </div>
          </div>
          <div class="stat-icon-wrap bg-green-dim text-green">
            <i class="bi bi-emoji-smile-fill"></i>
          </div>
        </div>
      </div>

      <div class="stat-card border-red">
        <div class="stat-card-inner">
          <div class="stat-info">
            <div class="stat-label">Cần Cải Thiện (≤ 2★)</div>
            <div class="stat-value text-red">{{ Number(stats.khong_hai_long || 0).toLocaleString('vi-VN') }}</div>
            <div class="stat-sub">
              Chiếm {{ stats.tong_danh_gia > 0 ? ((stats.khong_hai_long / stats.tong_danh_gia) * 100).toFixed(1) : 0 }}% tổng số
            </div>
          </div>
          <div class="stat-icon-wrap bg-red-dim text-red">
            <i class="bi bi-exclamation-octagon-fill"></i>
          </div>
        </div>
      </div>
    </div>

    <!-- FILTER TOOLBAR -->
    <div class="filter-panel">
      <div class="filter-header">
        <h5><i class="bi bi-funnel-fill text-gold me-2"></i>Bộ Lọc & Tìm Kiếm Đánh Giá</h5>
        <button @click="resetFilters" class="btn-reset-filter" title="Xóa bộ lọc">
          <i class="bi bi-arrow-counterclockwise me-1"></i> Đặt lại
        </button>
      </div>

      <div class="filter-grid">
        <div class="filter-item">
          <label>Loại đánh giá</label>
          <select v-model="filters.loai" @change="fetchReviews" class="filter-select">
            <option value="">Tất cả loại</option>
            <option value="Tour">Tour du lịch</option>
            <option value="NhaCungCap">Nhà cung cấp đối tác</option>
            <option value="NhanSu">Nhân sự hướng dẫn viên</option>
          </select>
        </div>

        <div class="filter-item">
          <label>Điểm tối thiểu</label>
          <select v-model="filters.diem_min" @change="fetchReviews" class="filter-select">
            <option value="">Tất cả</option>
            <option v-for="s in 5" :key="'min-' + s" :value="s">{{ s }} ★ trở lên</option>
          </select>
        </div>

        <div class="filter-item">
          <label>Điểm tối đa</label>
          <select v-model="filters.diem_max" @change="fetchReviews" class="filter-select">
            <option value="">Tất cả</option>
            <option v-for="s in 5" :key="'max-' + s" :value="s">{{ s }} ★ trở xuống</option>
          </select>
        </div>

        <div class="filter-item">
          <label>Từ ngày</label>
          <input v-model="filters.tu_ngay" @change="fetchReviews" type="date" class="filter-input" />
        </div>

        <div class="filter-item">
          <label>Đến ngày</label>
          <input v-model="filters.den_ngay" @change="fetchReviews" type="date" class="filter-input" />
        </div>

        <div class="filter-item filter-search-col">
          <label>Tìm kiếm khách / nội dung</label>
          <div class="search-input-wrap">
            <i class="bi bi-search"></i>
            <input 
              v-model="filters.search" 
              @keyup.enter="fetchReviews" 
              type="text" 
              placeholder="Tên khách, email, nội dung..." 
              class="filter-input input-with-icon" 
            />
          </div>
        </div>
      </div>
    </div>

    <!-- REVIEWS TABLE PANEL -->
    <div class="table-card">
      <div class="table-header">
        <div class="table-title-wrap">
          <h3 class="table-title">
            <i class="bi bi-chat-left-quote-fill text-gold me-2"></i>
            Danh Sách Đánh Giá
          </h3>
          <span class="badge-count">{{ reviewsList.length }}</span>
        </div>
        <div class="table-actions">
          <button @click="fetchReviews(true)" class="btn-refresh" :class="{ rotating: isFetching }" title="Tải lại dữ liệu">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width: 110px;">Ngày</th>
              <th style="width: 220px;">Khách Hàng</th>
              <th style="width: 140px;">Phân Loại</th>
              <th style="width: 200px;">Đối Tượng</th>
              <th style="width: 100px; text-align: center;">Điểm</th>
              <th>Nội Dung Đánh Giá</th>
              <th style="width: 140px; text-align: center;">Trạng Thái</th>
              <th style="width: 150px; text-align: center;">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="item in reviewsList" 
              :key="item.danh_gia_id" 
              class="review-row"
              :class="getScoreRowClass(item.diem)"
            >
              <td>
                <span class="date-text">{{ formatDate(item.ngay_danh_gia) }}</span>
              </td>
              <td>
                <div class="customer-block">
                  <span class="cust-name">{{ item.ten_khach_hang || 'N/A' }}</span>
                  <span class="cust-email" v-if="item.email_khach_hang">{{ item.email_khach_hang }}</span>
                </div>
              </td>
              <td>
                <span class="badge-category" :class="getCategoryBadgeClass(item.loai_danh_gia)">
                  {{ getCategoryText(item.loai_danh_gia) }}
                </span>
              </td>
              <td>
                <div class="target-name">
                  <template v-if="item.loai_danh_gia === 'Tour'">
                    <i class="bi bi-geo-alt text-gold me-1"></i>
                    <strong>{{ item.ten_tour || 'Tour #' + item.tour_id }}</strong>
                  </template>
                  <template v-else-if="item.loai_danh_gia === 'NhaCungCap'">
                    <i class="bi bi-building text-blue me-1"></i>
                    <span>Nhà cung cấp #{{ item.nha_cung_cap_id }}</span>
                  </template>
                  <template v-else-if="item.loai_danh_gia === 'NhanSu'">
                    <i class="bi bi-person-badge text-green me-1"></i>
                    <span>Nhân sự #{{ item.nhan_su_id }}</span>
                  </template>
                </div>
              </td>
              <td style="text-align: center;">
                <span class="star-pill" :class="getScoreBadgeClass(item.diem)">
                  {{ item.diem }} <i class="bi bi-star-fill font-xs"></i>
                </span>
              </td>
              <td>
                <div class="content-box">
                  <p class="review-text">{{ item.noi_dung }}</p>
                  <div v-if="item.phan_hoi_admin" class="admin-reply-box">
                    <span class="reply-badge"><i class="bi bi-reply-fill"></i> Phản hồi của Admin:</span>
                    <p class="reply-content">{{ item.phan_hoi_admin }}</p>
                  </div>
                </div>
              </td>
              <td style="text-align: center;">
                <span v-if="item.phan_hoi_admin" class="badge-reply replied">
                  <i class="bi bi-check-circle-fill"></i> Đã trả lời
                </span>
                <span v-else class="badge-reply pending">
                  <i class="bi bi-clock-fill"></i> Chờ phản hồi
                </span>
              </td>
              <td style="text-align: center;">
                <div class="row-actions">
                  <button 
                    @click="openReplyModal(item)" 
                    class="btn-act btn-reply" 
                    title="Trả lời / Sửa phản hồi"
                  >
                    <i class="bi bi-reply"></i>
                  </button>
                  <a 
                    :href="'index.php?act=admin/danhGia/chiTiet&id=' + item.danh_gia_id" 
                    class="btn-act btn-view" 
                    title="Xem chi tiết"
                  >
                    <i class="bi bi-eye"></i>
                  </a>
                  <button 
                    @click="deleteReview(item)" 
                    class="btn-act btn-del" 
                    title="Xóa đánh giá"
                    :disabled="isDeleting === item.danh_gia_id"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="reviewsList.length === 0">
              <td colspan="8" class="empty-row">
                <i class="bi bi-chat-square-dots font-xl d-block mb-2"></i>
                <span>Không tìm thấy đánh giá nào phù hợp với điều kiện tìm kiếm.</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL REPLY -->
    <transition name="fade">
      <div v-if="isReplyModalOpen" class="modal-overlay" @click.self="isReplyModalOpen = false">
        <div class="modal-card">
          <div class="modal-header">
            <h4 class="modal-title">
              <i class="bi bi-reply-fill text-gold me-2"></i>
              Phản Hồi Đánh Giá #{{ currentReview?.danh_gia_id }}
            </h4>
            <button @click="isReplyModalOpen = false" class="modal-close-btn">&times;</button>
          </div>

          <div class="modal-body">
            <div class="customer-quote-box">
              <div class="quote-author">
                <strong>{{ currentReview?.ten_khach_hang }}</strong>
                <span class="star-pill-sm ms-2">{{ currentReview?.diem }} ★</span>
              </div>
              <p class="quote-text">{{ currentReview?.noi_dung }}</p>
            </div>

            <div class="form-group mt-3">
              <label class="form-label">Nội dung phản hồi từ Ban Quản Trị:</label>
              <textarea 
                v-model="replyText" 
                rows="5" 
                placeholder="Nhập nội dung cảm ơn, giải thích hoặc hướng xử lý của ban quản lý..."
                class="form-textarea"
              ></textarea>
            </div>
          </div>

          <div class="modal-footer">
            <button @click="isReplyModalOpen = false" class="btn-modal-cancel">Hủy bỏ</button>
            <button @click="submitReply" class="btn-modal-submit" :disabled="isSubmittingReply">
              <i class="bi" :class="isSubmittingReply ? 'bi-hourglass-split' : 'bi-send-fill'"></i>
              <span>{{ isSubmittingReply ? 'Đang gửi...' : 'Gửi Phản Hồi' }}</span>
            </button>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({})
  }
});

// State
const reviewsList = ref([]);
const stats = ref({
  tong_danh_gia: 0,
  diem_trung_binh: 0,
  hai_long: 0,
  khong_hai_long: 0
});
const filters = ref({
  loai: '',
  diem_min: '',
  diem_max: '',
  tu_ngay: '',
  den_ngay: '',
  search: ''
});
const csrfToken = ref('');

// UI Interaction State
const isFetching = ref(false);
const isDeleting = ref(null);
const toast = ref({ visible: false, message: '', type: 'success' });

// Modal State
const isReplyModalOpen = ref(false);
const currentReview = ref(null);
const replyText = ref('');
const isSubmittingReply = ref(false);

function showToast(msg, type = 'success') {
  toast.value = { visible: true, message: msg, type };
  setTimeout(() => {
    if (toast.value.message === msg) {
      toast.value.visible = false;
    }
  }, 4000);
}

function formatDate(dateStr) {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  if (isNaN(d.getTime())) return dateStr;
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function getScoreRowClass(score) {
  const s = Number(score);
  if (s >= 4) return 'row-border-green';
  if (s <= 2) return 'row-border-red';
  return 'row-border-gold';
}

function getScoreBadgeClass(score) {
  const s = Number(score);
  if (s >= 4) return 'pill-green';
  if (s <= 2) return 'pill-red';
  return 'pill-gold';
}

function getCategoryBadgeClass(loai) {
  switch (loai) {
    case 'Tour': return 'badge-cat-tour';
    case 'NhaCungCap': return 'badge-cat-ncc';
    case 'NhanSu': return 'badge-cat-staff';
    default: return 'badge-cat-other';
  }
}

function getCategoryText(loai) {
  switch (loai) {
    case 'Tour': return 'Tour Du Lịch';
    case 'NhaCungCap': return 'Nhà Cung Cấp';
    case 'NhanSu': return 'Nhân Sự Hướng Dẫn';
    default: return loai || 'Khác';
  }
}

function applyPayload(data) {
  if (!data) return;
  if (Array.isArray(data.danhGiaList)) reviewsList.value = data.danhGiaList;
  if (data.stats) stats.value = data.stats;
  if (data.filters) {
    filters.value = { ...filters.value, ...data.filters };
  }
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchReviews(manual = false) {
  if (isFetching.value) return;
  isFetching.value = true;
  try {
    const params = new URLSearchParams();
    if (filters.value.loai) params.append('loai', filters.value.loai);
    if (filters.value.diem_min) params.append('diem_min', filters.value.diem_min);
    if (filters.value.diem_max) params.append('diem_max', filters.value.diem_max);
    if (filters.value.tu_ngay) params.append('tu_ngay', filters.value.tu_ngay);
    if (filters.value.den_ngay) params.append('den_ngay', filters.value.den_ngay);
    if (filters.value.search) params.append('search', filters.value.search);

    const res = await fetch('index.php?act=admin/apiDanhGiaList&' + params.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Mã lỗi mạng: ' + res.status);
    const data = await res.json();
    if (data.success) {
      applyPayload(data);
      if (manual) showToast('Đã làm mới danh sách đánh giá');
    }
  } catch (err) {
    showToast('Lỗi khi tải danh sách: ' + err.message, 'error');
  } finally {
    isFetching.value = false;
  }
}

function resetFilters() {
  filters.value = {
    loai: '',
    diem_min: '',
    diem_max: '',
    tu_ngay: '',
    den_ngay: '',
    search: ''
  };
  fetchReviews();
}

function openReplyModal(item) {
  currentReview.value = item;
  replyText.value = item.phan_hoi_admin || '';
  isReplyModalOpen.value = true;
}

async function submitReply() {
  if (!currentReview.value || isSubmittingReply.value) return;
  isSubmittingReply.value = true;
  try {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken.value);
    formData.append('id', currentReview.value.danh_gia_id);
    formData.append('phan_hoi_admin', replyText.value);

    const res = await fetch('index.php?act=admin/danhGia/traLoi', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message || 'Đã lưu phản hồi thành công');
      // Update local item
      const found = reviewsList.value.find(r => r.danh_gia_id == currentReview.value.danh_gia_id);
      if (found) {
        found.phan_hoi_admin = replyText.value;
      }
      isReplyModalOpen.value = false;
    } else {
      showToast(result.message || 'Lỗi khi lưu phản hồi', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối: ' + err.message, 'error');
  } finally {
    isSubmittingReply.value = false;
  }
}

async function deleteReview(item) {
  if (!confirm(`Bạn có chắc chắn muốn xóa đánh giá của khách "${item.ten_khach_hang}"?`)) return;
  isDeleting.value = item.danh_gia_id;
  try {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken.value);
    formData.append('id', item.danh_gia_id);

    const res = await fetch('index.php?act=admin/danhGia/xoa', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message || 'Đã xóa đánh giá');
      reviewsList.value = reviewsList.value.filter(r => r.danh_gia_id !== item.danh_gia_id);
      if (stats.value.tong_danh_gia > 0) stats.value.tong_danh_gia--;
    } else {
      showToast(result.message || 'Không thể xóa đánh giá', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối: ' + err.message, 'error');
  } finally {
    isDeleting.value = null;
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyPayload(props.initialData);
  } else if (window.__ADMIN_DANH_GIA_INIT__) {
    applyPayload(window.__ADMIN_DANH_GIA_INIT__);
  } else {
    fetchReviews();
  }
});
</script>

<style scoped>
.vue-admin-review-manage {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  color: #f8fafc;
  font-family: inherit;
}

/* Toast */
.toast-popup {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 14px 20px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.92rem;
  box-shadow: 0 12px 30px rgba(0,0,0,0.35);
}
.toast-popup.success {
  background: #14532d;
  color: #86efac;
  border: 1px solid rgba(34, 197, 94, 0.4);
}
.toast-popup.error {
  background: #7f1d1d;
  color: #fca5a5;
  border: 1px solid rgba(239, 68, 68, 0.4);
}
.toast-close {
  background: none;
  border: none;
  color: inherit;
  font-size: 1.2rem;
  cursor: pointer;
}

/* Page Header */
.page-header {
  padding: 26px 30px;
  border-radius: 20px;
  background: radial-gradient(circle at 90% 20%, rgba(212, 175, 55, 0.18), transparent 40%),
              radial-gradient(circle at 10% 80%, rgba(13, 202, 240, 0.12), transparent 40%),
              linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
  border: 1px solid rgba(212, 175, 55, 0.22);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1.5rem;
}
.badge-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #d4af37;
  margin-bottom: 8px;
}
.header-title {
  font-size: 1.85rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
}
.header-subtitle {
  font-size: 0.94rem;
  color: #94a3b8;
  margin: 0;
}
.btn-report {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 22px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.25), rgba(212, 175, 55, 0.12));
  border: 1px solid rgba(212, 175, 55, 0.45);
  color: #fde047;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-report:hover {
  background: linear-gradient(135deg, rgba(212, 175, 55, 0.38), rgba(212, 175, 55, 0.2));
  transform: translateY(-2px);
  color: #ffffff;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}
.stat-card {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 16px;
  padding: 20px;
  backdrop-filter: blur(12px);
  border-left: 4px solid;
  transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-2px); }
.border-blue { border-left-color: #3b82f6; }
.border-gold { border-left-color: #d4af37; }
.border-green { border-left-color: #22c55e; }
.border-red { border-left-color: #ef4444; }

.stat-card-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.stat-label { font-size: 0.8rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px; }
.stat-value { font-size: 1.8rem; font-weight: 800; line-height: 1.2; }
.stat-sub { font-size: 0.76rem; color: #64748b; margin-top: 4px; }
.stat-icon-wrap {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.bg-blue-dim { background: rgba(59, 130, 246, 0.15); }
.bg-gold-dim { background: rgba(212, 175, 55, 0.15); }
.bg-green-dim { background: rgba(34, 197, 94, 0.15); }
.bg-red-dim { background: rgba(239, 68, 68, 0.15); }

/* Filter Panel */
.filter-panel {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 20px 24px;
  backdrop-filter: blur(12px);
}
.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}
.filter-header h5 {
  margin: 0;
  font-size: 1rem;
  font-weight: 700;
  color: #ffffff;
}
.btn-reset-filter {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #cbd5e1;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-reset-filter:hover {
  background: rgba(255, 255, 255, 0.08);
  color: #ffffff;
}
.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 14px;
  align-items: end;
}
.filter-search-col {
  grid-column: span 2;
}
@media (max-width: 900px) {
  .filter-search-col { grid-column: span 1; }
}
.filter-item label {
  display: block;
  font-size: 0.78rem;
  font-weight: 600;
  color: #94a3b8;
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}
.filter-select,
.filter-input {
  width: 100%;
  height: 42px;
  padding: 8px 12px;
  background: rgba(11, 17, 32, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 10px;
  color: #ffffff;
  font-size: 0.88rem;
  outline: none;
  transition: border-color 0.2s;
}
.filter-select:focus,
.filter-input:focus {
  border-color: #d4af37;
}
.search-input-wrap {
  position: relative;
}
.search-input-wrap i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}
.input-with-icon {
  padding-left: 36px;
}

/* Table Card */
.table-card {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  overflow: hidden;
  backdrop-filter: blur(12px);
}
.table-header {
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: rgba(11, 17, 32, 0.85);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.table-title-wrap {
  display: flex;
  align-items: center;
  gap: 12px;
}
.table-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #ffffff;
}
.badge-count {
  background: rgba(212, 175, 55, 0.18);
  color: #fde047;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
}
.btn-refresh {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-refresh:hover {
  background: rgba(212, 175, 55, 0.2);
  border-color: #d4af37;
  color: #fde047;
}
.rotating i { animation: spin 1s infinite linear; }
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Table Elements */
.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
  color: #cbd5e1;
  min-width: 1000px;
}
.data-table th {
  background: rgba(11, 17, 32, 0.95);
  color: #d4af37;
  font-weight: 700;
  font-size: 0.78rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 14px 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: left;
}
.data-table td {
  padding: 16px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: top;
}
.review-row {
  border-left: 4px solid transparent;
  transition: background-color 0.2s;
}
.review-row:hover {
  background: rgba(255, 255, 255, 0.02);
}
.row-border-green { border-left-color: #22c55e; }
.row-border-red { border-left-color: #ef4444; }
.row-border-gold { border-left-color: #f59e0b; }

.date-text {
  font-size: 0.84rem;
  color: #94a3b8;
  white-space: nowrap;
}
.customer-block {
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.cust-name {
  font-weight: 700;
  color: #ffffff;
  font-size: 0.92rem;
}
.cust-email {
  font-size: 0.78rem;
  color: #94a3b8;
}

/* Category Badges */
.badge-category {
  display: inline-flex;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.76rem;
  font-weight: 700;
  text-transform: uppercase;
}
.badge-cat-tour { background: rgba(212, 175, 55, 0.16); color: #fde047; border: 1px solid rgba(212, 175, 55, 0.35); }
.badge-cat-ncc { background: rgba(59, 130, 246, 0.16); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); }
.badge-cat-staff { background: rgba(34, 197, 94, 0.16); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.35); }
.badge-cat-other { background: rgba(148, 163, 184, 0.16); color: #cbd5e1; }

.target-name {
  font-size: 0.88rem;
  color: #e2e8f0;
}

/* Star Pills */
.star-pill {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.84rem;
}
.pill-green { background: rgba(34, 197, 94, 0.18); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.4); }
.pill-red { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4); }
.pill-gold { background: rgba(245, 158, 11, 0.18); color: #fde047; border: 1px solid rgba(245, 158, 11, 0.4); }

.content-box {
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.review-text {
  margin: 0;
  line-height: 1.45;
  color: #f1f5f9;
}
.admin-reply-box {
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(212, 175, 55, 0.25);
  padding: 10px 14px;
  border-radius: 8px;
}
.reply-badge {
  font-size: 0.74rem;
  font-weight: 800;
  color: #d4af37;
  display: block;
  margin-bottom: 4px;
  text-transform: uppercase;
}
.reply-content {
  margin: 0;
  font-size: 0.84rem;
  color: #cbd5e1;
}

/* Reply Status Badge */
.badge-reply {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
}
.badge-reply.replied { background: rgba(34, 197, 94, 0.15); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.35); }
.badge-reply.pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.35); }

/* Row Actions */
.row-actions {
  display: inline-flex;
  gap: 6px;
}
.btn-act {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  border: none;
  text-decoration: none;
  font-size: 0.86rem;
  transition: all 0.2s;
}
.btn-reply { background: rgba(212, 175, 55, 0.15); color: #fde047; border: 1px solid rgba(212, 175, 55, 0.35); }
.btn-reply:hover { background: rgba(212, 175, 55, 0.3); color: #ffffff; }
.btn-view { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.35); }
.btn-view:hover { background: rgba(59, 130, 246, 0.3); color: #ffffff; }
.btn-del { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }
.btn-del:hover { background: rgba(239, 68, 68, 0.3); color: #ffffff; }
.empty-row {
  text-align: center;
  padding: 40px !important;
  color: #64748b;
  font-size: 0.95rem;
}

/* Modal */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.modal-card {
  background: #0f172a;
  border: 1px solid rgba(212, 175, 55, 0.35);
  border-radius: 20px;
  width: 100%;
  max-width: 580px;
  overflow: hidden;
  box-shadow: 0 24px 48px rgba(0, 0, 0, 0.5);
}
.modal-header {
  padding: 18px 24px;
  background: rgba(11, 17, 32, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.modal-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: #ffffff;
}
.modal-close-btn {
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 1.5rem;
  cursor: pointer;
}
.modal-body {
  padding: 22px 24px;
}
.customer-quote-box {
  background: rgba(255, 255, 255, 0.04);
  border-left: 3px solid #d4af37;
  padding: 12px 16px;
  border-radius: 8px;
}
.quote-author {
  font-size: 0.88rem;
  color: #f8fafc;
  margin-bottom: 4px;
}
.star-pill-sm {
  background: rgba(212, 175, 55, 0.2);
  color: #fde047;
  padding: 1px 6px;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 700;
}
.quote-text {
  margin: 0;
  font-size: 0.85rem;
  color: #cbd5e1;
}
.form-label {
  display: block;
  font-size: 0.84rem;
  font-weight: 600;
  color: #cbd5e1;
  margin-bottom: 8px;
}
.form-textarea {
  width: 100%;
  background: rgba(11, 17, 32, 0.85);
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  color: #ffffff;
  padding: 12px;
  font-size: 0.9rem;
  outline: none;
}
.form-textarea:focus {
  border-color: #d4af37;
}
.modal-footer {
  padding: 16px 24px;
  background: rgba(11, 17, 32, 0.9);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: flex-end;
  gap: 12px;
}
.btn-modal-cancel {
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #cbd5e1;
  padding: 10px 18px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.88rem;
}
.btn-modal-submit {
  background: linear-gradient(135deg, #d4af37 0%, #b89320 100%);
  color: #0b1120;
  border: none;
  padding: 10px 22px;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-modal-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Helpers */
.text-gold { color: #d4af37 !important; }
.text-blue { color: #60a5fa !important; }
.text-green { color: #4ade80 !important; }
.text-red { color: #f87171 !important; }
.font-sm { font-size: 0.85rem; }
.font-xs { font-size: 0.72rem; }
.font-xl { font-size: 1.8rem; }
.mt-3 { margin-top: 1rem; }
</style>
