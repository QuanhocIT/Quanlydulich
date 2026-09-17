<template>
  <div class="vue-admin-booking-manage">
    <!-- PAGE HEADER -->
    <header class="page-header">
      <div class="header-left">
        <div class="badge-tag">
          <i class="bi bi-calendar2-check"></i>
          <span>Điều Hành Đơn Đặt Tour</span>
        </div>
        <h1 class="header-title">{{ isCompletedView ? 'Booking Đã Hoàn Thành' : 'Quản Lý Đơn Đặt Tour' }}</h1>
        <p class="header-subtitle">
          Theo dõi tiến trình đặt tour, xác nhận cọc, thanh toán và phát hành vé điện tử
        </p>
      </div>

      <div class="header-actions">
        <a href="index.php?act=admin/lichSuXoaBooking" class="btn-history">
          <i class="bi bi-clock-history"></i>
          <span>Lịch Sử Xóa</span>
        </a>
        <a href="index.php?act=booking/changeRequests" class="btn-history">
          <i class="bi bi-arrow-repeat"></i>
          <span>Yêu Cầu Hủy/Đổi</span>
        </a>
        <a href="index.php?act=admin/tickets" class="btn-history">
          <i class="bi bi-ticket-detailed"></i>
          <span>Tickets Hỗ Trợ</span>
        </a>
        <a href="index.php?act=admin/quanLyYeuCauTour" class="btn-history">
          <i class="bi bi-star"></i>
          <span>Yêu Cầu Đặt Tour</span>
        </a>
        <a href="index.php?act=booking/datTourChoKhach" class="btn-create">
          <i class="bi bi-plus-lg"></i>
          <span>Đặt Tour Cho Khách</span>
        </a>
      </div>
    </header>

    <!-- VIEW MODE SWITCH TABS -->
    <div class="view-switch-tabs">
      <a 
        href="index.php?act=admin/quanLyBooking" 
        class="switch-tab" 
        :class="{ active: !isCompletedView }"
      >
        <i class="bi bi-hourglass-split"></i>
        <span>Booking Đang Xử Lý</span>
        <span class="tab-counter" v-if="stats.pending > 0">{{ stats.pending }}</span>
      </a>
      <a 
        href="index.php?act=admin/bookingDaHoanThanh" 
        class="switch-tab" 
        :class="{ active: isCompletedView }"
      >
        <i class="bi bi-check2-circle"></i>
        <span>Booking Đã Hoàn Thành</span>
      </a>
    </div>

    <!-- 5 KPI STATS CARDS -->
    <section class="kpi-grid">
      <div class="kpi-card card-amber">
        <div class="kpi-icon"><i class="bi bi-clock"></i></div>
        <div class="kpi-info">
          <span class="kpi-label">Chờ Xác Nhận</span>
          <strong class="kpi-val">{{ formatNumber(stats.pending) }}</strong>
        </div>
      </div>

      <div class="kpi-card card-blue">
        <div class="kpi-icon"><i class="bi bi-check2-square"></i></div>
        <div class="kpi-info">
          <span class="kpi-label">Đã Xác Nhận</span>
          <strong class="kpi-val">{{ formatNumber(stats.confirmed) }}</strong>
        </div>
      </div>

      <div class="kpi-card card-teal">
        <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
        <div class="kpi-info">
          <span class="kpi-label">Đã Đặt Cọc</span>
          <strong class="kpi-val">{{ formatNumber(stats.deposited) }}</strong>
        </div>
      </div>

      <div class="kpi-card card-emerald">
        <div class="kpi-icon"><i class="bi bi-flag-fill"></i></div>
        <div class="kpi-info">
          <span class="kpi-label">Đã Hoàn Thành</span>
          <strong class="kpi-val">{{ formatNumber(stats.completed) }}</strong>
        </div>
      </div>

      <div class="kpi-card card-rose">
        <div class="kpi-icon"><i class="bi bi-x-octagon"></i></div>
        <div class="kpi-info">
          <span class="kpi-label">Đã Hủy</span>
          <strong class="kpi-val">{{ formatNumber(stats.cancelled) }}</strong>
        </div>
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
              type="text" 
              v-model="searchQuery" 
              @input="onSearchInput"
              placeholder="Mã booking, tên khách, SĐT..." 
              class="form-control-custom"
            />
          </div>
        </div>

        <!-- Tour Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-compass me-1"></i>Tour</label>
          <select v-model="selectedTour" @change="fetchBookings(1)" class="form-select-custom">
            <option value="">Tất cả tour</option>
            <option v-for="t in toursList" :key="t.tour_id" :value="t.tour_id">
              {{ t.ten_tour }}
            </option>
          </select>
        </div>

        <!-- Booking Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-info-circle me-1"></i>Trạng thái</label>
          <select v-model="selectedStatus" @change="fetchBookings(1)" class="form-select-custom">
            <option value="">Tất cả trạng thái</option>
            <option value="ChoXacNhan">Chờ xác nhận</option>
            <option value="DaCoc">Đã cọc</option>
            <option value="HoanTat">Hoàn tất</option>
            <option value="Huy">Đã hủy</option>
          </select>
        </div>

        <!-- Payment Status Filter -->
        <div class="filter-col">
          <label class="filter-label"><i class="bi bi-credit-card me-1"></i>Thanh toán</label>
          <select v-model="selectedPaymentStatus" @change="fetchBookings(1)" class="form-select-custom">
            <option value="">Tất cả thanh toán</option>
            <option value="ChuaThanhToan">Chưa thanh toán</option>
            <option value="ThanhToanMotPhan">Thanh toán 1 phần</option>
            <option value="DaThanhToan">Đã thanh toán đủ</option>
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

    <!-- BOOKINGS DATA TABLE -->
    <section class="table-card">
      <div class="table-meta-bar">
        <span>Tổng cộng: <strong>{{ totalBookings }}</strong> booking</span>
        <span v-if="isLoading" class="loading-tag">
          <i class="bi bi-arrow-repeat spin"></i> Đang tải dữ liệu...
        </span>
      </div>

      <div class="table-responsive">
        <table class="booking-table">
          <thead>
            <tr>
              <th style="width: 80px;" class="text-center">#Mã</th>
              <th>Khách Hàng</th>
              <th>Tour & Lịch Trình</th>
              <th class="text-center" style="width: 100px;">Số Khách</th>
              <th class="text-end" style="width: 140px;">Tổng Tiền</th>
              <th class="text-end" style="width: 140px;">Đã Cọc / Thu</th>
              <th class="text-center" style="width: 130px;">Thanh Toán</th>
              <th class="text-center" style="width: 130px;">Trạng Thái</th>
              <th class="text-center" style="width: 160px;">Thao Tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookings" :key="b.booking_id" class="booking-row">
              <!-- ID -->
              <td class="text-center">
                <span class="id-badge">#{{ b.booking_id }}</span>
              </td>

              <!-- Customer -->
              <td>
                <strong class="cust-name">{{ b.ho_ten || 'Khách vãng lai' }}</strong>
                <div class="cust-sub">
                  <span v-if="b.so_dien_thoai"><i class="bi bi-telephone"></i> {{ b.so_dien_thoai }}</span>
                  <span v-if="b.email"><i class="bi bi-envelope"></i> {{ b.email }}</span>
                </div>
              </td>

              <!-- Tour -->
              <td>
                <strong class="tour-name">{{ b.ten_tour || 'N/A' }}</strong>
                <div class="tour-sub">
                  <span v-if="b.ngay_khoi_hanh"><i class="bi bi-calendar"></i> {{ formatDate(b.ngay_khoi_hanh) }}</span>
                </div>
              </td>

              <!-- Pax Count -->
              <td class="text-center">
                <span class="pax-badge">{{ b.so_luong_nguoi || b.so_nguoi || 1 }} người</span>
              </td>

              <!-- Total Price -->
              <td class="text-end fw-bold text-dark">
                {{ formatCurrency(b.tong_tien) }}
              </td>

              <!-- Deposit -->
              <td class="text-end">
                <span class="deposit-val text-success">{{ formatCurrency(b.tien_coc) }}</span>
              </td>

              <!-- Payment Status -->
              <td class="text-center">
                <span class="pay-pill" :class="getPayClass(b.trang_thai_thanh_toan)">
                  {{ formatPayStatus(b.trang_thai_thanh_toan) }}
                </span>
              </td>

              <!-- Booking Status -->
              <td class="text-center">
                <span class="status-pill" :class="getStatusClass(b.trang_thai)">
                  {{ formatStatus(b.trang_thai) }}
                </span>
              </td>

              <!-- Action Links -->
              <td class="text-center">
                <div class="btn-actions-group">
                  <button 
                    type="button" 
                    class="btn-act btn-passengers" 
                    @click="openPassengerModal(b)" 
                    title="Xem danh sách hành khách / Check-in"
                  >
                    <i class="bi bi-people-fill"></i> Khách
                  </button>
                  <button 
                    type="button" 
                    class="btn-act btn-quick-status" 
                    @click="openStatusModal(b)" 
                    title="Cập nhật trạng thái và tiền cọc"
                  >
                    <i class="bi bi-arrow-repeat"></i> Trạng thái
                  </button>
                  <a 
                    :href="'index.php?act=booking/chiTiet&id=' + b.booking_id" 
                    class="btn-act btn-view"
                    title="Xem chi tiết và chỉnh sửa đơn booking"
                  >
                    <i class="bi bi-eye"></i> Chi tiết
                  </a>
                  <a 
                    v-if="b.tour_id"
                    :href="'index.php?act=lichKhoiHanh/chiTietTheoBooking&id=' + b.booking_id" 
                    class="btn-act btn-assign" 
                    title="Điều hành lịch và phân bổ nhân sự/dịch vụ"
                  >
                    <i class="bi bi-signpost-split"></i> Phân bổ
                  </a>
                  <a 
                    :href="'index.php?act=booking/exportPDF&id=' + b.booking_id" 
                    class="btn-act btn-pdf" 
                    target="_blank"
                    title="Xuất vé / Phiếu xác nhận PDF"
                  >
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                  </a>
                  <form 
                    v-if="!isCompletedView && (b.trang_thai === 'HoanTat' || b.trang_thai === 'HoanThanh') && !b.is_hidden"
                    method="POST" 
                    action="index.php" 
                    style="display:inline; margin:0;"
                    @submit="confirmHideBooking($event, b)"
                  >
                    <input type="hidden" name="act" value="booking/hideCompleted">
                    <input type="hidden" name="booking_id" :value="b.booking_id">
                    <input type="hidden" name="_csrf_token" :value="csrfToken">
                    <button type="submit" class="btn-act btn-hide" title="Ẩn khỏi danh sách chính">
                      <i class="bi bi-eye-slash"></i> Ẩn
                    </button>
                  </form>
                </div>
              </td>
            </tr>

            <!-- Empty Row -->
            <tr v-if="bookings.length === 0 && !isLoading">
              <td colspan="9" class="empty-cell">
                <i class="bi bi-inbox fs-2 text-muted d-block mb-2"></i>
                Không tìm thấy đơn booking nào phù hợp với bộ lọc.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <div class="pagination-bar" v-if="totalPages > 1">
        <ul class="vue-pagination">
          <li :class="{ disabled: currentPageNumber <= 1 }">
            <button @click="fetchBookings(currentPageNumber - 1)" :disabled="currentPageNumber <= 1">
              <i class="bi bi-chevron-left"></i>
            </button>
          </li>
          <li v-for="page in visiblePages" :key="page" :class="{ active: page === currentPageNumber }">
            <button @click="fetchBookings(page)">{{ page }}</button>
          </li>
          <li :class="{ disabled: currentPageNumber >= totalPages }">
            <button @click="fetchBookings(currentPageNumber + 1)" :disabled="currentPageNumber >= totalPages">
              <i class="bi bi-chevron-right"></i>
            </button>
          </li>
        </ul>
      </div>
    </section>

    <!-- MODAL: DANH SÁCH HÀNH KHÁCH (PASSENGER MANIFEST) -->
    <div v-if="isPassengerModalOpen" class="modal-backdrop-custom" @click.self="closePassengerModal">
      <div class="modal-card modal-lg">
        <div class="modal-header-custom">
          <div class="modal-title-wrap">
            <i class="bi bi-person-lines-fill text-warning me-2 fs-5"></i>
            <div>
              <h3 class="modal-title">Danh Sách Hành Khách - {{ activeBooking?.ten_tour || 'Booking Tour' }}</h3>
              <p class="modal-subtitle">
                Mã đơn: <strong>#{{ activeBooking?.booking_id }}</strong> | 
                Khách đặt: <strong>{{ activeBooking?.customer_name || activeBooking?.ho_ten }}</strong> ({{ activeBooking?.so_dien_thoai }}) | 
                Số lượng: <strong>{{ activeBooking?.so_luong_nguoi || activeBooking?.so_nguoi || 1 }} khách</strong>
              </p>
            </div>
          </div>
          <button type="button" class="btn-close-modal" @click="closePassengerModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body-custom">
          <div v-if="isLoadingPassengers" class="loading-state p-4 text-center">
            <i class="bi bi-arrow-repeat spin fs-3 text-warning"></i>
            <p class="mt-2 text-muted">Đang tải danh sách hành khách...</p>
          </div>
          <div v-else>
            <div class="table-responsive">
              <table class="passenger-table">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">STT</th>
                    <th>Họ và tên</th>
                    <th>Số điện thoại</th>
                    <th>CCCD / Hộ chiếu</th>
                    <th class="text-center">Giới tính</th>
                    <th class="text-center">Trạng thái</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(p, idx) in passengerList" :key="p.id || idx" class="p-row">
                    <td class="text-center font-monospace">{{ idx + 1 }}</td>
                    <td>
                      <strong class="text-light">{{ p.ho_ten }}</strong>
                      <span v-if="p.is_primary" class="badge-primary-booker ms-2">Trưởng đoàn</span>
                    </td>
                    <td>{{ p.so_dien_thoai || '--' }}</td>
                    <td class="font-monospace text-secondary">{{ p.so_cmnd || '--' }}</td>
                    <td class="text-center">{{ p.gioi_tinh || '--' }}</td>
                    <td class="text-center">
                      <span class="p-status-pill" :class="p.trang_thai === 'DaCheckIn' ? 'p-checked' : 'p-unchecked'">
                        {{ p.trang_thai === 'DaCheckIn' ? 'Đã check-in' : 'Chưa check-in' }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="modal-actions-summary mt-3">
              <span class="text-muted small">
                <i class="bi bi-info-circle me-1"></i> Danh sách phục vụ khai báo danh sách đoàn, bảo hiểm du lịch và xếp phòng khách sạn.
              </span>
            </div>
          </div>
        </div>

        <div class="modal-footer-custom">
          <div class="footer-links">
            <a 
              :href="'index.php?act=admin/danhSachKhachTheoTour&booking_id=' + (activeBooking?.booking_id || 0)" 
              target="_blank"
              class="btn-footer-secondary"
            >
              <i class="bi bi-printer me-1"></i> In danh sách đoàn
            </a>
            <a 
              href="index.php?act=admin/phanPhongKhachSan" 
              target="_blank"
              class="btn-footer-secondary"
            >
              <i class="bi bi-building me-1"></i> Xếp phòng KS
            </a>
          </div>
          <button type="button" class="btn-footer-primary" @click="closePassengerModal">
            Đóng
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL: CẬP NHẬT TRẠNG THÁI & TIỀN CỌC NHANH -->
    <div v-if="isStatusModalOpen" class="modal-backdrop-custom" @click.self="closeStatusModal">
      <div class="modal-card modal-md">
        <div class="modal-header-custom">
          <div class="modal-title-wrap">
            <i class="bi bi-arrow-repeat text-warning me-2 fs-5"></i>
            <div>
              <h3 class="modal-title">Cập Nhật Trạng Thái Booking #{{ activeBooking?.booking_id }}</h3>
              <p class="modal-subtitle">
                {{ activeBooking?.ten_tour }} | Tổng tiền: <strong class="text-warning">{{ formatCurrency(activeBooking?.tong_tien) }}</strong>
              </p>
            </div>
          </div>
          <button type="button" class="btn-close-modal" @click="closeStatusModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body-custom">
          <div class="form-group-custom mb-3">
            <label class="form-label-custom"><i class="bi bi-check2-circle text-warning me-1"></i>Trạng thái đơn hàng</label>
            <div class="status-options-grid">
              <label 
                v-for="opt in statusOptions" 
                :key="opt.value" 
                class="status-option-label"
                :class="{ active: editStatus === opt.value }"
              >
                <input type="radio" v-model="editStatus" :value="opt.value" class="d-none" />
                <i :class="opt.icon"></i>
                <span>{{ opt.label }}</span>
              </label>
            </div>
          </div>

          <div class="form-group-custom mb-3" v-if="editStatus === 'DaCoc' || editStatus === 'HoanTat'">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="form-label-custom mb-0"><i class="bi bi-cash-stack text-success me-1"></i>Tiền cọc / Đã thanh toán (VNĐ)</label>
              <div class="quick-deposit-btns">
                <button type="button" class="btn-quick-pct" @click="setDepositPercent(0.3)">30%</button>
                <button type="button" class="btn-quick-pct" @click="setDepositPercent(0.5)">50%</button>
                <button type="button" class="btn-quick-pct" @click="setDepositPercent(1)">100%</button>
              </div>
            </div>
            <input 
              type="number" 
              v-model.number="editTienCoc" 
              class="form-control-custom"
              placeholder="Nhập số tiền cọc (VNĐ)"
              min="0"
              :max="activeBooking?.tong_tien || 999999999"
            />
            <span class="text-muted small mt-1 d-block">
              Còn lại: <strong class="text-warning">{{ formatCurrency(Math.max(0, (Number(activeBooking?.tong_tien) || 0) - editTienCoc)) }}</strong>
            </span>
          </div>

          <div class="form-group-custom mb-3">
            <label class="form-label-custom"><i class="bi bi-chat-left-text text-warning me-1"></i>Ghi chú nội bộ</label>
            <textarea 
              v-model="editGhiChu" 
              class="form-control-custom textarea-custom" 
              rows="3" 
              placeholder="Ghi chú xác nhận, chứng từ thanh toán, yêu cầu phát sinh..."
            ></textarea>
          </div>
        </div>

        <div class="modal-footer-custom">
          <button type="button" class="btn-footer-secondary" @click="closeStatusModal" :disabled="isSavingStatus">
            Hủy
          </button>
          <button type="button" class="btn-footer-primary" @click="saveQuickStatus" :disabled="isSavingStatus">
            <i v-if="isSavingStatus" class="bi bi-arrow-repeat spin me-1"></i>
            <i v-else class="bi bi-check2 me-1"></i>
            {{ isSavingStatus ? 'Đang lưu...' : 'Lưu thay đổi' }}
          </button>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFICATION -->
    <div v-if="toast.show" class="toast-custom" :class="'toast-' + toast.type">
      <i :class="toast.type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-exclamation-circle-fill'"></i>
      <span>{{ toast.message }}</span>
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
const isCompletedView = ref(false);
const searchQuery = ref('');
const selectedTour = ref('');
const selectedStatus = ref('');
const selectedPaymentStatus = ref('');
const currentPageNumber = ref(1);
const totalPages = ref(1);
const totalBookings = ref(0);

const stats = ref({
  pending: 0,
  confirmed: 0,
  deposited: 0,
  completed: 0,
  cancelled: 0,
});

const bookings = ref([]);
const toursList = ref([]);
const csrfToken = ref('');

// Modals State
const isPassengerModalOpen = ref(false);
const isLoadingPassengers = ref(false);
const passengerList = ref([]);

const isStatusModalOpen = ref(false);
const isSavingStatus = ref(false);
const activeBooking = ref(null);
const editStatus = ref('');
const editTienCoc = ref(0);
const editGhiChu = ref('');

const statusOptions = [
  { value: 'ChoXacNhan', label: 'Chờ xác nhận', icon: 'bi bi-clock-history' },
  { value: 'DaCoc', label: 'Đã đặt cọc', icon: 'bi bi-cash-coin' },
  { value: 'HoanTat', label: 'Hoàn tất', icon: 'bi bi-check2-all' },
  { value: 'Huy', label: 'Đã hủy', icon: 'bi bi-x-circle' },
];

// Toast State
const toast = ref({
  show: false,
  message: '',
  type: 'success',
});
let toastTimer = null;

function showToast(message, type = 'success') {
  clearTimeout(toastTimer);
  toast.value = { show: true, message, type };
  toastTimer = setTimeout(() => {
    toast.value.show = false;
  }, 3500);
}

// Open Passenger Modal
async function openPassengerModal(b) {
  activeBooking.value = b;
  isPassengerModalOpen.value = true;
  isLoadingPassengers.value = true;
  passengerList.value = [];

  try {
    const res = await fetch(`index.php?act=booking/apiBookingPassengers&id=${b.booking_id}`, {
      headers: { 'Accept': 'application/json' }
    });
    const json = await res.json();
    if (json.success && json.passengers) {
      passengerList.value = json.passengers;
    } else {
      showToast(json.message || 'Không thể tải danh sách hành khách.', 'error');
    }
  } catch (err) {
    console.error('Error loading passengers:', err);
    showToast('Lỗi khi tải danh sách hành khách.', 'error');
  } finally {
    isLoadingPassengers.value = false;
  }
}

function closePassengerModal() {
  isPassengerModalOpen.value = false;
}

// Open Status Modal
function openStatusModal(b) {
  activeBooking.value = b;
  editStatus.value = b.trang_thai || 'ChoXacNhan';
  editTienCoc.value = Number(b.tien_coc) || 0;
  editGhiChu.value = '';
  isStatusModalOpen.value = true;
}

function closeStatusModal() {
  isStatusModalOpen.value = false;
}

function setDepositPercent(pct) {
  if (!activeBooking.value) return;
  const total = Number(activeBooking.value.tong_tien) || 0;
  editTienCoc.value = Math.round(total * pct);
}

// Save Quick Status
async function saveQuickStatus() {
  if (!activeBooking.value) return;
  isSavingStatus.value = true;

  try {
    const payload = {
      booking_id: activeBooking.value.booking_id,
      trang_thai: editStatus.value,
      tien_coc: editTienCoc.value,
      ghi_chu: editGhiChu.value,
      _csrf_global: csrfToken.value,
      _csrf_token: csrfToken.value,
    };

    const res = await fetch('index.php?act=booking/apiQuickUpdateStatus', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if (json.success) {
      showToast(json.message || 'Cập nhật trạng thái thành công!', 'success');
      // Update in table
      const idx = bookings.value.findIndex(item => item.booking_id === activeBooking.value.booking_id);
      if (idx !== -1) {
        bookings.value[idx].trang_thai = editStatus.value;
        bookings.value[idx].tien_coc = editTienCoc.value;
        const tongTien = Number(bookings.value[idx].tong_tien) || 0;
        if (editTienCoc.value >= tongTien && tongTien > 0) {
          bookings.value[idx].trang_thai_thanh_toan = 'DaThanhToan';
        } else if (editTienCoc.value > 0) {
          bookings.value[idx].trang_thai_thanh_toan = 'ThanhToanMotPhan';
        }
      }
      closeStatusModal();
    } else {
      showToast(json.message || 'Cập nhật thất bại.', 'error');
    }
  } catch (err) {
    console.error('Error updating status:', err);
    showToast('Lỗi mạng hoặc máy chủ không phản hồi.', 'error');
  } finally {
    isSavingStatus.value = false;
  }
}

let searchTimer = null;

const visiblePages = computed(() => {
  const pages = [];
  const start = Math.max(1, currentPageNumber.value - 2);
  const end = Math.min(totalPages.value, currentPageNumber.value + 2);
  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});

function onSearchInput() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchBookings(1);
  }, 350);
}

function resetFilters() {
  searchQuery.value = '';
  selectedTour.value = '';
  selectedStatus.value = '';
  selectedPaymentStatus.value = '';
  fetchBookings(1);
}

function applyData(data) {
  if (!data) return;
  bookings.value = data.bookings || [];
  totalBookings.value = data.totalBookings || 0;
  totalPages.value = data.totalPages || 1;
  currentPageNumber.value = data.pageNumber || 1;
  isCompletedView.value = !!data.isCompletedView;
  if (data.stats) stats.value = { ...stats.value, ...data.stats };
  if (data.toursList) toursList.value = data.toursList;
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchBookings(page = 1) {
  isLoading.value = true;
  currentPageNumber.value = page;

  const params = new URLSearchParams({
    act: 'admin/apiBookingList',
    page: String(page),
    completed: isCompletedView.value ? '1' : '0'
  });

  if (searchQuery.value.trim()) params.append('search', searchQuery.value.trim());
  if (selectedTour.value) params.append('tour_id', selectedTour.value);
  if (selectedStatus.value) params.append('trang_thai', selectedStatus.value);
  if (selectedPaymentStatus.value) params.append('trang_thai_thanh_toan', selectedPaymentStatus.value);

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
    console.error('[Vue BookingManage] Error:', err);
  } finally {
    isLoading.value = false;
  }
}

// Formatters
function formatCurrency(val) {
  const num = Number(val) || 0;
  return num.toLocaleString('vi-VN') + ' đ';
}

function formatNumber(val) {
  return (Number(val) || 0).toLocaleString('vi-VN');
}

function formatDate(dateStr) {
  if (!dateStr) return '--/--/----';
  const parts = dateStr.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return dateStr;
}

function formatStatus(st) {
  switch (st) {
    case 'ChoXacNhan': return 'Chờ xác nhận';
    case 'DaXacNhan': return 'Đã xác nhận';
    case 'DaDatCoc':
    case 'DaCoc': return 'Đã cọc';
    case 'HoanThanh':
    case 'HoanTat': return 'Hoàn tất';
    case 'Huy': return 'Đã hủy';
    default: return st || 'Chờ xác nhận';
  }
}

function getStatusClass(st) {
  switch (st) {
    case 'ChoXacNhan': return 'st-pending';
    case 'DaXacNhan': return 'st-confirmed';
    case 'DaDatCoc':
    case 'DaCoc': return 'st-deposit';
    case 'HoanThanh':
    case 'HoanTat': return 'st-completed';
    case 'Huy': return 'st-cancelled';
    default: return 'st-pending';
  }
}

function confirmHideBooking(e, b) {
  if (!confirm(`Ẩn booking #${b.booking_id} hoàn tất này khỏi danh sách chính?`)) {
    e.preventDefault();
  }
}

function formatPayStatus(st) {
  switch (st) {
    case 'ChuaThanhToan': return 'Chưa trả';
    case 'ThanhToanMotPhan': return 'Cọc 1 phần';
    case 'DaThanhToan': return 'Đã trả đủ';
    default: return st || 'Chưa trả';
  }
}

function getPayClass(st) {
  switch (st) {
    case 'ChuaThanhToan': return 'pay-unpaid';
    case 'ThanhToanMotPhan': return 'pay-partial';
    case 'DaThanhToan': return 'pay-paid';
    default: return 'pay-unpaid';
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyData(props.initialData);
  } else {
    fetchBookings(1);
  }
});
</script>

<style scoped>
.vue-admin-booking-manage {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  padding: 0.5rem 0 2rem;
  font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #f8fafc;
}

/* HEADER */
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
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(212, 175, 55, 0.35);
  transition: all 0.2s;
}
.btn-create:hover { transform: translateY(-2px); color: #0b1120; }

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
}
.btn-history:hover { background: rgba(255, 255, 255, 0.12); }

/* VIEW SWITCH TABS */
.view-switch-tabs {
  display: flex;
  gap: 0.75rem;
}
.switch-tab {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 0.65rem;
  background: rgba(22, 31, 46, 0.75);
  border: 1px solid rgba(255, 255, 255, 0.08);
  color: #cbd5e1;
  font-weight: 700;
  font-size: 0.9rem;
  text-decoration: none;
  transition: all 0.15s;
}
.switch-tab:hover { background: rgba(22, 31, 46, 0.95); color: #ffffff; }
.switch-tab.active {
  background: #d4af37;
  color: #0b1120;
  border-color: #d4af37;
}
.tab-counter {
  background: rgba(245, 158, 11, 0.2);
  color: #fde047;
  padding: 0.15rem 0.5rem;
  border-radius: 1rem;
  font-size: 0.75rem;
  font-weight: 800;
}
.switch-tab.active .tab-counter {
  background: #0b1120;
  color: #d4af37;
}

/* KPI GRID */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.25rem;
}
.kpi-card {
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
.kpi-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
  border-color: rgba(255, 255, 255, 0.15);
}
.kpi-icon {
  width: 44px;
  height: 44px;
  border-radius: 0.65rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.35rem;
}
.card-amber .kpi-icon { background: rgba(245, 158, 11, 0.16); color: #fbbf24; }
.card-blue .kpi-icon { background: rgba(14, 165, 233, 0.16); color: #38bdf8; }
.card-teal .kpi-icon { background: rgba(20, 184, 166, 0.16); color: #2dd4bf; }
.card-emerald .kpi-icon { background: rgba(16, 185, 129, 0.16); color: #34d399; }
.card-rose .kpi-icon { background: rgba(244, 63, 94, 0.16); color: #fb7185; }
.kpi-label { font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; }
.kpi-val { font-size: 1.45rem; font-weight: 800; color: #ffffff; line-height: 1.2; }

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
  grid-template-columns: 2fr 1fr 1fr 1fr auto;
  gap: 1.25rem;
  align-items: flex-end;
}
@media (max-width: 992px) {
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
}
.form-control-custom {
  width: 100%;
  padding: 0.65rem 1rem 0.65rem 2.2rem;
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 0.65rem;
  font-size: 0.9rem;
  background: rgba(11, 17, 32, 0.85);
  color: #ffffff;
  outline: none;
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
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #cbd5e1;
  padding: 0.65rem 0.95rem;
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
.table-meta-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 1.5rem;
  background: rgba(11, 17, 32, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 0.85rem;
  color: #cbd5e1;
}
.loading-tag { color: #38bdf8; font-weight: 600; }
.spin { display: inline-block; animation: spin 0.8s linear infinite; }
@keyframes spin { 100% { transform: rotate(360deg); } }

.table-responsive { overflow-x: auto; }
.booking-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 0.88rem;
}
.booking-table th {
  padding: 0.9rem 1.15rem;
  background: rgba(11, 17, 32, 0.95);
  color: #d4af37;
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  white-space: nowrap;
}
.booking-table td {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
  color: #cbd5e1;
}
.booking-row:hover td { background: rgba(255, 255, 255, 0.03); }

.id-badge {
  background: rgba(255, 255, 255, 0.08);
  color: #94a3b8;
  font-weight: 700;
  padding: 0.2rem 0.5rem;
  border-radius: 0.35rem;
}
.cust-name { color: #ffffff; font-size: 0.92rem; font-weight: 700; }
.cust-sub { font-size: 0.78rem; color: #94a3b8; display: flex; gap: 0.75rem; }
.tour-name { color: #ffffff; font-size: 0.92rem; font-weight: 600; }
.tour-sub { font-size: 0.78rem; color: #94a3b8; }
.pax-badge { background: rgba(255, 255, 255, 0.08); color: #cbd5e1; padding: 0.2rem 0.5rem; border-radius: 0.3rem; font-weight: 600; }
.deposit-val { font-weight: 700; color: #34d399; }

.pay-pill, .status-pill {
  display: inline-block;
  padding: 0.25rem 0.65rem;
  border-radius: 2rem;
  font-size: 0.75rem;
  font-weight: 700;
  white-space: nowrap;
}
.pay-unpaid { background: rgba(239, 68, 68, 0.18); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }
.pay-partial { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.pay-paid { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }

.st-pending { background: rgba(245, 158, 11, 0.18); color: #fde68a; border: 1px solid rgba(245, 158, 11, 0.35); }
.st-confirmed { background: rgba(14, 165, 233, 0.18); color: #7dd3fc; border: 1px solid rgba(14, 165, 233, 0.35); }
.st-deposit { background: rgba(20, 184, 166, 0.18); color: #5eead4; border: 1px solid rgba(20, 184, 166, 0.35); }
.st-completed { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.35); }
.st-cancelled { background: rgba(244, 63, 94, 0.18); color: #fda4af; border: 1px solid rgba(244, 63, 94, 0.35); }

.btn-actions-group {
  display: flex;
  gap: 0.35rem;
  justify-content: center;
}
.btn-act {
  display: inline-flex;
  align-items: center;
  gap: 0.2rem;
  padding: 0.35rem 0.65rem;
  border-radius: 0.45rem;
  font-size: 0.78rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s;
}
.btn-view { background: rgba(14, 165, 233, 0.15); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.25); }
.btn-view:hover { background: rgba(14, 165, 233, 0.25); color: #ffffff; }
.btn-passengers { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.25); cursor: pointer; }
.btn-passengers:hover { background: rgba(16, 185, 129, 0.25); color: #ffffff; }
.btn-quick-status { background: rgba(245, 158, 11, 0.15); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.25); cursor: pointer; }
.btn-quick-status:hover { background: rgba(245, 158, 11, 0.25); color: #fde047; }
.btn-assign { background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border: 1px solid rgba(99, 102, 241, 0.25); }
.btn-assign:hover { background: rgba(99, 102, 241, 0.25); color: #ffffff; }
.btn-pdf { background: rgba(212, 175, 55, 0.15); color: #fbbf24; border: 1px solid rgba(212, 175, 55, 0.25); }
.btn-pdf:hover { background: rgba(212, 175, 55, 0.25); color: #fde047; }
.btn-hide { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.25); cursor: pointer; }
.btn-hide:hover { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }

.empty-cell { padding: 3rem !important; text-align: center; color: #64748b; }

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
.vue-pagination li.active button { background: #d4af37; color: #0b1120; border-color: #d4af37; font-weight: 700; }
.vue-pagination li.disabled button { opacity: 0.4; cursor: not-allowed; }

/* MODALS */
.modal-backdrop-custom {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.75);
  backdrop-filter: blur(8px);
  z-index: 1050;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1.5rem;
  animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.98); }
  to { opacity: 1; transform: scale(1); }
}

.modal-card {
  background: #111827;
  border: 1px solid rgba(212, 175, 55, 0.3);
  border-radius: 1.25rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
  width: 100%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  color: #f3f4f6;
}
.modal-md { max-width: 540px; }
.modal-lg { max-width: 820px; }

.modal-header-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.25rem 1.75rem;
  background: rgba(31, 41, 55, 0.7);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}
.modal-title-wrap {
  display: flex;
  align-items: center;
}
.modal-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #ffffff;
}
.modal-subtitle {
  margin: 0.2rem 0 0;
  font-size: 0.8rem;
  color: #9ca3af;
}
.btn-close-modal {
  background: transparent;
  border: none;
  color: #9ca3af;
  font-size: 1.25rem;
  cursor: pointer;
  padding: 0.25rem;
  border-radius: 0.35rem;
  transition: all 0.15s;
}
.btn-close-modal:hover { color: #ffffff; background: rgba(255, 255, 255, 0.1); }

.modal-body-custom {
  padding: 1.5rem 1.75rem;
  overflow-y: auto;
}

.passenger-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}
.passenger-table th {
  padding: 0.75rem 1rem;
  background: rgba(31, 41, 55, 0.9);
  color: #d4af37;
  font-weight: 700;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: left;
}
.passenger-table td {
  padding: 0.85rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
  color: #e5e7eb;
}
.p-row:hover td { background: rgba(255, 255, 255, 0.02); }

.badge-primary-booker {
  background: rgba(212, 175, 55, 0.2);
  color: #fde047;
  border: 1px solid rgba(212, 175, 55, 0.35);
  font-size: 0.7rem;
  padding: 0.15rem 0.45rem;
  border-radius: 0.3rem;
  font-weight: 700;
}

.p-status-pill {
  display: inline-block;
  padding: 0.2rem 0.55rem;
  border-radius: 1rem;
  font-size: 0.72rem;
  font-weight: 700;
}
.p-checked { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.3); }
.p-unchecked { background: rgba(156, 163, 175, 0.15); color: #d1d5db; border: 1px solid rgba(156, 163, 175, 0.25); }

.modal-footer-custom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.15rem 1.75rem;
  background: rgba(31, 41, 55, 0.7);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
}
.footer-links {
  display: flex;
  gap: 0.65rem;
}
.btn-footer-secondary {
  display: inline-flex;
  align-items: center;
  padding: 0.6rem 1.15rem;
  border-radius: 0.6rem;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #e5e7eb;
  font-weight: 600;
  font-size: 0.88rem;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.15s;
}
.btn-footer-secondary:hover {
  background: rgba(255, 255, 255, 0.15);
  color: #ffffff;
}
.btn-footer-primary {
  display: inline-flex;
  align-items: center;
  padding: 0.6rem 1.35rem;
  border-radius: 0.6rem;
  background: linear-gradient(135deg, #d4af37 0%, #b89628 100%);
  border: none;
  color: #0b1120;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(212, 175, 55, 0.3);
  transition: all 0.15s;
}
.btn-footer-primary:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(212, 175, 55, 0.4);
}

.form-group-custom {
  display: flex;
  flex-direction: column;
}
.form-label-custom {
  font-size: 0.82rem;
  font-weight: 700;
  color: #d1d5db;
  margin-bottom: 0.45rem;
}
.status-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}
.status-option-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 0.65rem;
  background: rgba(31, 41, 55, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #cbd5e1;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s;
}
.status-option-label:hover {
  background: rgba(31, 41, 55, 0.9);
  border-color: rgba(212, 175, 55, 0.3);
}
.status-option-label.active {
  background: rgba(212, 175, 55, 0.15);
  border-color: #d4af37;
  color: #fde047;
}

.quick-deposit-btns {
  display: flex;
  gap: 0.35rem;
}
.btn-quick-pct {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #34d399;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 0.15rem 0.55rem;
  border-radius: 0.35rem;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-quick-pct:hover {
  background: rgba(16, 185, 129, 0.2);
  border-color: #34d399;
}
.textarea-custom {
  resize: vertical;
  min-height: 80px;
}

/* TOAST */
.toast-custom {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  z-index: 2000;
  display: flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.85rem 1.25rem;
  border-radius: 0.75rem;
  font-size: 0.9rem;
  font-weight: 700;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
  animation: slideUp 0.25s ease-out;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(15px); }
  to { opacity: 1; transform: translateY(0); }
}
.toast-success {
  background: #064e3b;
  color: #6ee7b7;
  border: 1px solid #059669;
}
.toast-error {
  background: #7f1d1d;
  color: #fca5a5;
  border: 1px solid #dc2626;
}
</style>

