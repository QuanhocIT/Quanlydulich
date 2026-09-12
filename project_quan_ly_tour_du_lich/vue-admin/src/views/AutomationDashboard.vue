<template>
  <div class="vue-admin-automation">
    <!-- FLASH TOAST NOTIFICATION -->
    <transition name="fade">
      <div v-if="toast.visible" class="toast-popup" :class="toast.type">
        <i class="bi" :class="toast.type === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'"></i>
        <span>{{ toast.message }}</span>
        <button @click="toast.visible = false" class="toast-close">&times;</button>
      </div>
    </transition>

    <!-- HERO SECTION -->
    <section class="auto-hero">
      <div class="hero-top">
        <div class="hero-title-group">
          <div class="kicker-pill">
            <i class="bi bi-cpu-fill text-gold me-1"></i> AUTOMATION COMMAND CENTER
          </div>
          <h1 class="hero-title">Trung Tâm Tự Động Hóa Admin</h1>
          <p class="hero-desc">
            Giám sát độ ổn định toàn hệ thống theo thời gian thực, kích hoạt job tức thì và đưa ra quyết định vận hành chính xác.
          </p>
        </div>

        <div class="hero-meta">
          <div 
            class="live-chip" 
            :class="automationEnabled ? 'active' : 'inactive'"
            :title="automationEnabled ? 'Automation đang hoạt động' : 'Automation đang tạm tắt'"
          >
            <span class="live-dot"></span>
            <span class="live-text">{{ automationEnabled ? 'Hệ thống • Đang chạy' : 'Hệ thống • Đang tạm dừng' }}</span>
          </div>

          <div class="countdown-badge">
            <i class="bi bi-stopwatch"></i>
            <span>{{ nextRefreshText }}</span>
          </div>
        </div>
      </div>

      <!-- KPI METRIC CARDS -->
      <div class="kpi-grid">
        <div class="kpi-card" :class="{ 'highlight-amber': highSeverityCount > 0 }">
          <div class="kpi-icon-wrap icon-amber">
            <i class="bi bi-shield-exclamation"></i>
          </div>
          <div class="kpi-body">
            <div class="kpi-label">Cảnh báo nghiêm trọng</div>
            <div class="kpi-value text-danger">{{ highSeverityCount }}</div>
            <div class="kpi-sub">Trong tổng số {{ events.length }} sự kiện</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon-wrap icon-blue">
            <i class="bi bi-lightbulb-fill"></i>
          </div>
          <div class="kpi-body">
            <div class="kpi-label">Decision Assist cần duyệt</div>
            <div class="kpi-value text-gold">{{ decisionAssistList.length }}</div>
            <div class="kpi-sub">Gợi ý AI & quy tắc vận hành</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon-wrap icon-red">
            <i class="bi bi-heart-pulse-fill"></i>
          </div>
          <div class="kpi-body">
            <div class="kpi-label">Tour Watch / Critical</div>
            <div class="kpi-value text-warning">{{ tourHealthList.length }}</div>
            <div class="kpi-sub">Cần rà soát chi phí / tỷ lệ lấp đầy</div>
          </div>
        </div>

        <div class="kpi-card">
          <div class="kpi-icon-wrap icon-cyan">
            <i class="bi bi-bookmark-star-fill"></i>
          </div>
          <div class="kpi-body">
            <div class="kpi-label">Booking ưu tiên cao</div>
            <div class="kpi-value text-cyan">{{ priorityBookingsList.length }}</div>
            <div class="kpi-sub">Cần chăm sóc & xác nhận sớm</div>
          </div>
        </div>
      </div>
    </section>

    <!-- CONTROL PANEL & SYSTEM RUNNER -->
    <div class="control-grid">
      <!-- PANEL 1: SYSTEM SWITCH & MANUAL RUNNER -->
      <div class="control-card panel-runner">
        <div class="card-header-clean">
          <div>
            <h3 class="card-heading"><i class="bi bi-terminal-dash me-2 text-gold"></i>Điều Khiển & Thực Thi Job</h3>
            <p class="card-sub">Bật tắt toàn hệ thống hoặc chạy thủ công từng module</p>
          </div>
          <button @click="fetchData(true)" class="btn-refresh" :class="{ rotating: isFetching }" title="Làm mới dữ liệu">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>

        <!-- Banner status -->
        <div class="status-banner" :class="automationEnabled ? 'banner-on' : 'banner-off'">
          <div class="banner-icon">
            <i class="bi" :class="automationEnabled ? 'bi-check-circle-fill' : 'bi-pause-circle-fill'"></i>
          </div>
          <div class="banner-info">
            <strong>{{ automationEnabled ? 'Tự động hóa đang kích hoạt' : 'Tự động hóa đang tạm tắt' }}</strong>
            <p>{{ automationEnabled ? 'Scheduler cron và các tác vụ nền đang kiểm tra định kỳ mỗi 15 phút.' : 'Tất cả tác vụ nền và job tự động đang tạm dừng.' }}</p>
            <span v-if="automationUpdatedAt" class="banner-meta">Cập nhật lần cuối: {{ automationUpdatedAt }}</span>
          </div>
          <button 
            @click="toggleSystem" 
            :disabled="isToggling" 
            class="btn-toggle-action"
            :class="automationEnabled ? 'btn-pause' : 'btn-resume'"
          >
            <i class="bi" :class="automationEnabled ? 'bi-power' : 'bi-play-circle-fill'"></i>
            <span>{{ isToggling ? 'Đang cập nhật...' : (automationEnabled ? 'Tắt Automation' : 'Bật Automation') }}</span>
          </button>
        </div>

        <!-- Manual Job Form -->
        <div class="job-runner-form">
          <div class="form-group-job">
            <label class="form-label">Chọn tác vụ muốn chạy:</label>
            <div class="job-input-group">
              <select v-model="selectedJob" class="form-select-gold" :disabled="!automationEnabled || isRunningJob">
                <option v-for="j in availableJobs" :key="j" :value="j">
                  {{ jobLabels[j] || j }}
                </option>
              </select>
              <button 
                @click="runJob" 
                class="btn-run-job" 
                :disabled="!automationEnabled || isRunningJob"
              >
                <i class="bi" :class="isRunningJob ? 'bi-hourglass-split' : 'bi-play-fill'"></i>
                <span>{{ isRunningJob ? 'Đang chạy...' : 'Run Job' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Latest Run Status -->
        <div v-if="latestRun" class="latest-run-box" :class="latestRun.is_success == 1 ? 'latest-ok' : 'latest-fail'">
          <div class="latest-badge">
            <i class="bi" :class="latestRun.is_success == 1 ? 'bi-check2' : 'bi-x-lg'"></i>
            {{ latestRun.is_success == 1 ? 'LẦN CHẠY GẦN NHẤT: THÀNH CÔNG' : 'LẦN CHẠY GẦN NHẤT: CÓ LỖI' }}
          </div>
          <div class="latest-details">
            <span class="latest-name"><strong>Job:</strong> {{ latestRun.job_name }}</span>
            <span class="latest-time"><i class="bi bi-clock me-1"></i>{{ latestRun.created_at }}</span>
            <span class="latest-duration"><i class="bi bi-stopwatch me-1"></i>{{ latestRun.duration_ms }} ms</span>
            <span class="latest-count"><i class="bi bi-layers me-1"></i>Tác động: {{ latestRun.affected_count }}</span>
          </div>
          <p v-if="latestRun.message" class="latest-msg">{{ latestRun.message }}</p>
        </div>
      </div>

      <!-- PANEL 2: HEALTH SUMMARY & DECISION SHORTCUTS -->
      <div class="control-card panel-summary">
        <div class="card-header-clean">
          <div>
            <h3 class="card-heading"><i class="bi bi-speedometer2 me-2 text-cyan"></i>Trạng Thái Vận Hành Nhanh</h3>
            <p class="card-sub">Tổng kết nhanh những việc cần can thiệp trong ngày</p>
          </div>
        </div>

        <div class="summary-chips-grid">
          <div class="summary-chip">
            <div class="chip-top">
              <span class="chip-label">Độ sẵn sàng khởi hành</span>
              <i class="bi bi-airplane-engines text-gold"></i>
            </div>
            <div class="chip-val">{{ departureReadinessCount }}</div>
            <div class="chip-status text-muted">Tour khởi hành trong 7 ngày tới</div>
          </div>

          <div class="summary-chip">
            <div class="chip-top">
              <span class="chip-label">Cảnh báo Webhook / Cổng TT</span>
              <i class="bi bi-hdd-network text-cyan"></i>
            </div>
            <div class="chip-val">{{ webhookAnomalyCount }}</div>
            <div class="chip-status text-muted">Lỗi đồng bộ hoặc timeout</div>
          </div>

          <div class="summary-chip">
            <div class="chip-top">
              <span class="chip-label">Nhắc công nợ HDV / KH</span>
              <i class="bi bi-cash-coin text-warning"></i>
            </div>
            <div class="chip-val">{{ debtReminderCount }}</div>
            <div class="chip-status text-muted">Cần rà soát hoàn tất thanh toán</div>
          </div>

          <div class="summary-chip">
            <div class="chip-top">
              <span class="chip-label">SLA Yêu Cầu Tour</span>
              <i class="bi bi-alarm text-danger"></i>
            </div>
            <div class="chip-val">{{ slaRequestsCount }}</div>
            <div class="chip-status text-muted">Chưa phân công quá 24h</div>
          </div>
        </div>
      </div>
    </div>

    <!-- TABBED EXPLORATION SECTION -->
    <div class="tabs-container">
      <div class="tabs-bar">
        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'runs' }" 
          @click="activeTab = 'runs'"
        >
          <i class="bi bi-clock-history me-1"></i>
          <span>Lịch Sử Chạy Job</span>
          <span class="tab-count">{{ jobRuns.length }}</span>
        </button>

        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'events' }" 
          @click="activeTab = 'events'"
        >
          <i class="bi bi-bell-fill me-1"></i>
          <span>Sự Kiện & Cảnh Báo</span>
          <span class="tab-count" :class="{ 'count-danger': highSeverityCount > 0 }">{{ events.length }}</span>
        </button>

        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'decision' }" 
          @click="activeTab = 'decision'"
        >
          <i class="bi bi-patch-question-fill me-1"></i>
          <span>Decision Assist</span>
          <span class="tab-count count-gold">{{ decisionAssistList.length }}</span>
        </button>

        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'priority' }" 
          @click="activeTab = 'priority'"
        >
          <i class="bi bi-star-half me-1"></i>
          <span>Booking Ưu Tiên</span>
          <span class="tab-count">{{ priorityBookingsList.length }}</span>
        </button>

        <button 
          class="tab-btn" 
          :class="{ active: activeTab === 'health' }" 
          @click="activeTab = 'health'"
        >
          <i class="bi bi-heart-pulse me-1"></i>
          <span>Sức Khỏe Tour</span>
          <span class="tab-count">{{ tourHealthList.length }}</span>
        </button>
      </div>

      <!-- TAB 1: JOB RUNS -->
      <div v-if="activeTab === 'runs'" class="tab-content">
        <div class="filter-toolbar">
          <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input v-model="filterJobName" type="text" placeholder="Lọc theo tên job hoặc ghi chú..." class="table-search-input">
          </div>
          <div class="filter-group">
            <select v-model="filterJobStatus" class="table-filter-select">
              <option value="">Tất cả trạng thái</option>
              <option value="ok">Thành công (OK)</option>
              <option value="error">Thất bại (ERROR)</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 70px;">#ID</th>
                <th>Tên Tác Vụ (Job)</th>
                <th style="width: 130px;">Trạng Thái</th>
                <th style="width: 110px;">Bản Ghi</th>
                <th style="width: 120px;">Thời Lượng</th>
                <th>Thông Điệp Chi Tiết</th>
                <th style="width: 160px;">Thời Điểm</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="run in filteredJobRuns" :key="run.run_id" :class="run.is_success == 1 ? 'row-ok' : 'row-error'">
                <td><span class="code-id">#{{ run.run_id }}</span></td>
                <td>
                  <span class="job-tag">{{ run.job_name }}</span>
                  <span class="job-friendly-sub">{{ jobLabels[run.job_name] || '' }}</span>
                </td>
                <td>
                  <span class="badge-status" :class="run.is_success == 1 ? 'status-ok' : 'status-error'">
                    <i class="bi" :class="run.is_success == 1 ? 'bi-check-circle' : 'bi-x-circle'"></i>
                    {{ run.is_success == 1 ? 'SUCCESS' : 'FAILED' }}
                  </span>
                </td>
                <td><span class="metric-num">{{ run.affected_count }}</span></td>
                <td><span class="duration-pill">{{ run.duration_ms }} ms</span></td>
                <td><span class="msg-text">{{ run.message || '—' }}</span></td>
                <td><span class="time-text">{{ run.created_at }}</span></td>
              </tr>
              <tr v-if="filteredJobRuns.length === 0">
                <td colspan="7" class="empty-cell">
                  <i class="bi bi-inbox"></i> Không có lịch sử chạy job phù hợp bộ lọc.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 2: EVENTS -->
      <div v-if="activeTab === 'events'" class="tab-content">
        <div class="filter-toolbar">
          <div class="search-wrap">
            <i class="bi bi-search"></i>
            <input v-model="filterEventText" type="text" placeholder="Tìm kiếm tiêu đề, thông điệp sự kiện..." class="table-search-input">
          </div>
          <div class="filter-group">
            <select v-model="filterSeverity" class="table-filter-select">
              <option value="">Tất cả mức độ</option>
              <option value="high">Nghiêm trọng (High)</option>
              <option value="medium">Trung bình (Medium)</option>
              <option value="low">Thông tin (Low)</option>
            </select>
          </div>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 70px;">#ID</th>
                <th style="width: 160px;">Nguồn Tác Vụ</th>
                <th style="width: 120px;">Mức Độ</th>
                <th>Tiêu Đề Cảnh Báo</th>
                <th>Chi Tiết Nội Dung</th>
                <th style="width: 160px;">Thời Gian</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="ev in filteredEvents" :key="ev.event_id" :class="'severity-' + ev.severity">
                <td><span class="code-id">#{{ ev.event_id }}</span></td>
                <td><span class="job-tag">{{ ev.job_name }}</span></td>
                <td>
                  <span class="badge-severity" :class="'sev-' + ev.severity">
                    <i class="bi" :class="getSeverityIcon(ev.severity)"></i>
                    {{ ev.severity.toUpperCase() }}
                  </span>
                </td>
                <td><strong class="event-title">{{ ev.title }}</strong></td>
                <td><span class="event-msg">{{ ev.message }}</span></td>
                <td><span class="time-text">{{ ev.created_at }}</span></td>
              </tr>
              <tr v-if="filteredEvents.length === 0">
                <td colspan="6" class="empty-cell">
                  <i class="bi bi-shield-check"></i> Không có sự kiện cảnh báo nào phù hợp.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 3: DECISION ASSIST -->
      <div v-if="activeTab === 'decision'" class="tab-content">
        <div class="decision-header-info">
          <i class="bi bi-info-circle text-gold me-2"></i>
          <span>Các đề xuất tự động từ hệ thống trợ lý vận hành. Cập nhật trạng thái sau khi đã xử lý hoặc ghi nhận.</span>
        </div>

        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 70px;">#ID</th>
                <th style="width: 150px;">Đối Tượng</th>
                <th>Khuyến Nghị Vận Hành</th>
                <th style="width: 110px;">Trạng Thái</th>
                <th style="width: 160px;">Cập Nhật</th>
                <th style="width: 220px; text-align: center;">Hành Động Admin</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in decisionAssistList" :key="item.assist_id" class="decision-row">
                <td><span class="code-id">#{{ item.assist_id }}</span></td>
                <td>
                  <span class="entity-badge">
                    <i class="bi bi-folder2-open me-1"></i>
                    {{ item.entity_type }} #{{ item.entity_id }}
                  </span>
                </td>
                <td>
                  <div class="recommendation-content">
                    {{ item.recommendation_text }}
                  </div>
                </td>
                <td>
                  <span class="badge-decision" :class="'decision-' + item.status">
                    {{ item.status }}
                  </span>
                </td>
                <td><span class="time-text">{{ item.updated_at }}</span></td>
                <td style="text-align: center;">
                  <div class="action-btn-group">
                    <button 
                      @click="updateDecision(item.assist_id, 'done')" 
                      class="btn-act-done" 
                      title="Đã xử lý xong"
                      :disabled="isUpdatingAssist === item.assist_id"
                    >
                      <i class="bi bi-check-lg"></i> Done
                    </button>
                    <button 
                      @click="updateDecision(item.assist_id, 'ignored')" 
                      class="btn-act-ignore" 
                      title="Bỏ qua gợi ý này"
                      :disabled="isUpdatingAssist === item.assist_id"
                    >
                      <i class="bi bi-dash-circle"></i> Bỏ qua
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="decisionAssistList.length === 0">
                <td colspan="6" class="empty-cell">
                  <i class="bi bi-check2-all text-success"></i> Không có khuyến nghị nào đang mở!
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 4: PRIORITY BOOKINGS -->
      <div v-if="activeTab === 'priority'" class="tab-content">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 90px;">Booking</th>
                <th style="width: 140px;">Mức Độ</th>
                <th style="width: 120px;">Điểm Priority</th>
                <th>Ngày Khởi Hành</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái Đơn</th>
                <th style="width: 110px; text-align: center;">Chi Tiết</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="b in priorityBookingsList" :key="b.booking_id">
                <td><span class="code-id">#{{ b.booking_id }}</span></td>
                <td>
                  <span class="priority-chip" :class="b.priority_label === 'High' ? 'p-high' : 'p-norm'">
                    <i class="bi bi-flag-fill me-1"></i> {{ b.priority_label }}
                  </span>
                </td>
                <td>
                  <div class="score-progress-wrap">
                    <span class="score-num">{{ b.score }}</span>
                    <div class="score-bar">
                      <div class="score-fill" :style="{ width: Math.min(100, b.score) + '%' }"></div>
                    </div>
                  </div>
                </td>
                <td>{{ b.ngay_khoi_hanh || 'Chưa định ngày' }}</td>
                <td><strong class="text-gold">{{ formatCurrency(b.tong_tien) }}</strong></td>
                <td><span class="badge-bk-status">{{ b.trang_thai || 'Chờ xử lý' }}</span></td>
                <td style="text-align: center;">
                  <a :href="'index.php?act=admin/chiTietBooking&id=' + b.booking_id" class="btn-detail-link" target="_blank">
                    <i class="bi bi-arrow-up-right-circle"></i> Xem
                  </a>
                </td>
              </tr>
              <tr v-if="priorityBookingsList.length === 0">
                <td colspan="7" class="empty-cell">
                  <i class="bi bi-bookmark-check"></i> Hiện không có booking nào cần ưu tiên khẩn.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB 5: TOUR HEALTH -->
      <div v-if="activeTab === 'health'" class="tab-content">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width: 80px;">#Tour</th>
                <th>Tên Tour Tuyến</th>
                <th style="width: 150px;">Mức Cảnh Báo</th>
                <th style="width: 160px;">Chỉ Số Sức Khỏe (Score)</th>
                <th style="width: 170px;">Thời Điểm Đánh Giá</th>
                <th style="width: 120px; text-align: center;">Quản Lý</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in tourHealthList" :key="t.tour_id">
                <td><span class="code-id">#{{ t.tour_id }}</span></td>
                <td><strong class="tour-name">{{ t.ten_tour || ('Tour #' + t.tour_id) }}</strong></td>
                <td>
                  <span class="badge-health" :class="'health-' + (t.health_level ? t.health_level.toLowerCase() : 'watch')">
                    <i class="bi bi-heart-pulse me-1"></i> {{ t.health_level }}
                  </span>
                </td>
                <td>
                  <div class="score-progress-wrap">
                    <span class="score-num" :class="t.score < 50 ? 'text-danger' : 'text-warning'">{{ t.score }} / 100</span>
                    <div class="score-bar">
                      <div class="score-fill bg-danger" :style="{ width: Math.max(5, t.score) + '%' }"></div>
                    </div>
                  </div>
                </td>
                <td><span class="time-text">{{ t.computed_at }}</span></td>
                <td style="text-align: center;">
                  <a :href="'index.php?act=admin/quanLyTour'" class="btn-detail-link">
                    <i class="bi bi-gear-fill"></i> Xem Tour
                  </a>
                </td>
              </tr>
              <tr v-if="tourHealthList.length === 0">
                <td colspan="6" class="empty-cell">
                  <i class="bi bi-check-circle-fill text-success"></i> Tuyệt vời! Tất cả các tour đều có sức khỏe đạt chuẩn.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
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

// UI State
const activeTab = ref('runs');
const isFetching = ref(false);
const isToggling = ref(false);
const isRunningJob = ref(false);
const isUpdatingAssist = ref(null);
const toast = ref({ visible: false, message: '', type: 'success' });

// Job Mapping & Labels
const jobLabels = {
  'all': 'Tất cả tác vụ (Run All)',
  'sla_tour_requests': 'Kiểm tra SLA Yêu Cầu Tour',
  'booking_priority': 'Chấm điểm ưu tiên Booking',
  'reconcile_digest': 'Đối soát giao dịch tài chính',
  'self_heal_pending_payments': 'Tự động phục hồi thanh toán treo',
  'webhook_anomaly': 'Rà soát bất thường Webhook',
  'debt_reminder': 'Nhắc nhở công nợ HDV / Khách hàng',
  'departure_readiness': 'Kiểm tra độ sẵn sàng khởi hành',
  'tour_health_score': 'Tính điểm sức khỏe Tour (Health)',
  'admin_inbox_digest': 'Tổng hợp hộp thư thông báo Admin',
  'decision_assist': 'Tạo đề xuất trợ lý quyết định'
};

// Data State
const jobRuns = ref([]);
const events = ref([]);
const priorityBookingsList = ref([]);
const tourHealthList = ref([]);
const decisionAssistList = ref([]);
const automationEnabled = ref(true);
const automationUpdatedAt = ref('');
const availableJobs = ref([
  'all',
  'sla_tour_requests',
  'booking_priority',
  'reconcile_digest',
  'self_heal_pending_payments',
  'webhook_anomaly',
  'debt_reminder',
  'departure_readiness',
  'tour_health_score',
  'admin_inbox_digest',
  'decision_assist'
]);
const selectedJob = ref('all');
const csrfToken = ref('');

// Filters
const filterJobName = ref('');
const filterJobStatus = ref('');
const filterEventText = ref('');
const filterSeverity = ref('');

// Timers & Auto-refresh (30s)
const POLL_INTERVAL = 30;
const secondsLeft = ref(POLL_INTERVAL);
let countdownInterval = null;

const highSeverityCount = computed(() => {
  return events.value.filter(e => (e.severity || '').toLowerCase() === 'high').length;
});

const latestRun = computed(() => {
  return jobRuns.value.length > 0 ? jobRuns.value[0] : null;
});

const nextRefreshText = computed(() => {
  return `Tự làm mới sau ${secondsLeft.value}s`;
});

// Quick operational counts
const departureReadinessCount = computed(() => {
  return events.value.filter(e => e.job_name === 'departure_readiness').length;
});
const webhookAnomalyCount = computed(() => {
  return events.value.filter(e => e.job_name === 'webhook_anomaly').length;
});
const debtReminderCount = computed(() => {
  return events.value.filter(e => e.job_name === 'debt_reminder').length;
});
const slaRequestsCount = computed(() => {
  return events.value.filter(e => e.job_name === 'sla_tour_requests').length;
});

// Filtered Lists
const filteredJobRuns = computed(() => {
  return jobRuns.value.filter(run => {
    if (filterJobName.value) {
      const q = filterJobName.value.toLowerCase();
      const matchName = (run.job_name || '').toLowerCase().includes(q);
      const matchMsg = (run.message || '').toLowerCase().includes(q);
      if (!matchName && !matchMsg) return false;
    }
    if (filterJobStatus.value) {
      const isOk = Number(run.is_success) === 1;
      if (filterJobStatus.value === 'ok' && !isOk) return false;
      if (filterJobStatus.value === 'error' && isOk) return false;
    }
    return true;
  });
});

const filteredEvents = computed(() => {
  return events.value.filter(ev => {
    if (filterSeverity.value) {
      if ((ev.severity || '').toLowerCase() !== filterSeverity.value.toLowerCase()) return false;
    }
    if (filterEventText.value) {
      const q = filterEventText.value.toLowerCase();
      const matchTitle = (ev.title || '').toLowerCase().includes(q);
      const matchMsg = (ev.message || '').toLowerCase().includes(q);
      const matchJob = (ev.job_name || '').toLowerCase().includes(q);
      if (!matchTitle && !matchMsg && !matchJob) return false;
    }
    return true;
  });
});

function showToast(msg, type = 'success') {
  toast.value = { visible: true, message: msg, type };
  setTimeout(() => {
    if (toast.value.message === msg) {
      toast.value.visible = false;
    }
  }, 4500);
}

function getSeverityIcon(sev) {
  switch ((sev || '').toLowerCase()) {
    case 'high': return 'bi-exclamation-octagon-fill';
    case 'medium': return 'bi-exclamation-triangle-fill';
    default: return 'bi-info-circle-fill';
  }
}

function formatCurrency(val) {
  const n = Number(val) || 0;
  return n.toLocaleString('vi-VN') + ' đ';
}

function applyPayload(data) {
  if (!data) return;
  if (Array.isArray(data.jobRuns)) jobRuns.value = data.jobRuns;
  if (Array.isArray(data.events)) events.value = data.events;
  if (Array.isArray(data.priorityBookings)) priorityBookingsList.value = data.priorityBookings;
  if (Array.isArray(data.tourHealth)) tourHealthList.value = data.tourHealth;
  if (Array.isArray(data.decisionAssist)) decisionAssistList.value = data.decisionAssist;
  if (data.automationEnabled !== undefined) automationEnabled.value = Boolean(data.automationEnabled);
  if (data.automationUpdatedAt) automationUpdatedAt.value = data.automationUpdatedAt;
  if (Array.isArray(data.availableJobs) && data.availableJobs.length > 0) availableJobs.value = data.availableJobs;
  if (data.csrfToken) csrfToken.value = data.csrfToken;
}

async function fetchData(manual = false) {
  if (isFetching.value) return;
  isFetching.value = true;
  try {
    const res = await fetch('index.php?act=admin/apiAutomationData', {
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    });
    if (!res.ok) throw new Error('Mã lỗi mạng: ' + res.status);
    const data = await res.json();
    if (data.success) {
      applyPayload(data);
      secondsLeft.value = POLL_INTERVAL;
      if (manual) showToast('Đã làm mới dữ liệu tự động hóa thành công!');
    }
  } catch (err) {
    if (manual) showToast('Không thể làm mới dữ liệu: ' + err.message, 'error');
  } finally {
    isFetching.value = false;
  }
}

async function toggleSystem() {
  if (isToggling.value) return;
  const nextState = !automationEnabled.value;
  isToggling.value = true;
  try {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken.value);
    formData.append('enabled', nextState ? '1' : '0');

    const res = await fetch('index.php?act=admin/toggleAutomation', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      automationEnabled.value = nextState;
      showToast(result.message || (nextState ? 'Đã bật tự động hóa' : 'Đã tắt tự động hóa'));
      fetchData();
    } else {
      showToast(result.message || 'Thao tác không thành công', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối khi thay đổi trạng thái: ' + err.message, 'error');
  } finally {
    isToggling.value = false;
  }
}

async function runJob() {
  if (isRunningJob.value || !automationEnabled.value) return;
  isRunningJob.value = true;
  try {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken.value);
    formData.append('job', selectedJob.value);

    const res = await fetch('index.php?act=admin/runAutomationJob', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message || 'Đã chạy job thành công!');
      fetchData();
    } else {
      showToast(result.message || 'Chạy job thất bại', 'error');
    }
  } catch (err) {
    showToast('Lỗi khi gửi yêu cầu chạy job: ' + err.message, 'error');
  } finally {
    isRunningJob.value = false;
  }
}

async function updateDecision(assistId, status) {
  if (isUpdatingAssist.value === assistId) return;
  isUpdatingAssist.value = assistId;
  try {
    const formData = new FormData();
    formData.append('_csrf_token', csrfToken.value);
    formData.append('assist_id', assistId);
    formData.append('status', status);

    const res = await fetch('index.php?act=admin/updateDecisionAssistStatus', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      body: formData
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message || 'Đã cập nhật gợi ý');
      decisionAssistList.value = decisionAssistList.value.filter(d => d.assist_id !== assistId);
    } else {
      showToast(result.message || 'Lỗi cập nhật gợi ý', 'error');
    }
  } catch (err) {
    showToast('Lỗi kết nối: ' + err.message, 'error');
  } finally {
    isUpdatingAssist.value = null;
  }
}

onMounted(() => {
  if (props.initialData && Object.keys(props.initialData).length > 0) {
    applyPayload(props.initialData);
  } else if (window.__ADMIN_AUTOMATION_INIT__) {
    applyPayload(window.__ADMIN_AUTOMATION_INIT__);
  } else {
    fetchData();
  }

  countdownInterval = setInterval(() => {
    secondsLeft.value--;
    if (secondsLeft.value <= 0) {
      secondsLeft.value = POLL_INTERVAL;
      fetchData();
    }
  }, 1000);
});

onUnmounted(() => {
  if (countdownInterval) clearInterval(countdownInterval);
});
</script>

<style scoped>
.vue-admin-automation {
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
  padding: 0 4px;
}

/* Hero Section */
.auto-hero {
  padding: 26px 30px;
  border-radius: 20px;
  background: radial-gradient(circle at 88% 18%, rgba(212, 175, 55, 0.18), transparent 40%),
              radial-gradient(circle at 10% 85%, rgba(6, 182, 212, 0.12), transparent 40%),
              linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
  border: 1px solid rgba(212, 175, 55, 0.22);
  box-shadow: 0 16px 36px rgba(0, 0, 0, 0.28);
}
.hero-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1.5rem;
  flex-wrap: wrap;
  margin-bottom: 22px;
}
.kicker-pill {
  display: inline-flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  color: #d4af37;
  margin-bottom: 8px;
}
.hero-title {
  font-size: 1.85rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0 0 6px;
  line-height: 1.25;
}
.hero-desc {
  font-size: 0.94rem;
  color: #94a3b8;
  max-width: 780px;
  margin: 0;
}
.hero-meta {
  display: flex;
  align-items: center;
  gap: 12px;
}
.live-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 0.84rem;
  font-weight: 700;
}
.live-chip.active {
  background: rgba(34, 197, 94, 0.15);
  border: 1px solid rgba(34, 197, 94, 0.45);
  color: #86efac;
}
.live-chip.inactive {
  background: rgba(239, 68, 68, 0.15);
  border: 1px solid rgba(239, 68, 68, 0.45);
  color: #fca5a5;
}
.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
  box-shadow: 0 0 8px currentColor;
}
.countdown-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.12);
  padding: 8px 14px;
  border-radius: 999px;
  font-size: 0.82rem;
  color: #cbd5e1;
}

/* KPI Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 16px;
}
.kpi-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  background: rgba(15, 23, 42, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 14px;
  backdrop-filter: blur(10px);
  transition: transform 0.2s, border-color 0.2s;
}
.kpi-card:hover {
  transform: translateY(-2px);
  border-color: rgba(212, 175, 55, 0.35);
}
.kpi-icon-wrap {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}
.icon-amber { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
.icon-blue { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.icon-red { background: rgba(239, 68, 68, 0.15); color: #f87171; }
.icon-cyan { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }

.kpi-label { font-size: 0.82rem; color: #94a3b8; font-weight: 500; }
.kpi-value { font-size: 1.6rem; font-weight: 800; line-height: 1.2; }
.kpi-sub { font-size: 0.76rem; color: #64748b; margin-top: 2px; }

/* Control Grid */
.control-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 1.5rem;
}
@media (max-width: 1024px) {
  .control-grid { grid-template-columns: 1fr; }
}

.control-card {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  padding: 22px;
  backdrop-filter: blur(12px);
}
.card-header-clean {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
}
.card-heading {
  font-size: 1.12rem;
  font-weight: 700;
  color: #f8fafc;
  margin: 0 0 4px;
}
.card-sub {
  font-size: 0.84rem;
  color: #94a3b8;
  margin: 0;
}
.btn-refresh {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.14);
  color: #e2e8f0;
  width: 38px;
  height: 38px;
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

/* Status Banner */
.status-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 18px;
  border-radius: 14px;
  margin-bottom: 18px;
}
.banner-on {
  background: rgba(20, 83, 45, 0.25);
  border: 1px solid rgba(34, 197, 94, 0.35);
}
.banner-off {
  background: rgba(127, 29, 29, 0.25);
  border: 1px solid rgba(239, 68, 68, 0.35);
}
.banner-icon { font-size: 1.6rem; }
.banner-on .banner-icon { color: #4ade80; }
.banner-off .banner-icon { color: #f87171; }
.banner-info { flex: 1; font-size: 0.88rem; }
.banner-info strong { display: block; margin-bottom: 2px; color: #ffffff; }
.banner-info p { margin: 0; color: #cbd5e1; font-size: 0.82rem; }
.banner-meta { font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 2px; }

.btn-toggle-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s;
}
.btn-pause {
  background: rgba(239, 68, 68, 0.25);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: #fca5a5;
}
.btn-pause:hover { background: rgba(239, 68, 68, 0.4); }
.btn-resume {
  background: rgba(34, 197, 94, 0.25);
  border: 1px solid rgba(34, 197, 94, 0.5);
  color: #86efac;
}
.btn-resume:hover { background: rgba(34, 197, 94, 0.4); }

/* Job Runner Form */
.job-runner-form {
  margin-bottom: 18px;
}
.form-label {
  display: block;
  font-size: 0.84rem;
  color: #cbd5e1;
  margin-bottom: 8px;
  font-weight: 600;
}
.job-input-group {
  display: flex;
  gap: 10px;
}
.form-select-gold {
  flex: 1;
  background: rgba(15, 23, 42, 0.85);
  border: 1px solid rgba(212, 175, 55, 0.35);
  color: #ffffff;
  padding: 10px 14px;
  border-radius: 10px;
  font-size: 0.9rem;
  outline: none;
}
.form-select-gold:focus {
  border-color: #d4af37;
  box-shadow: 0 0 0 2px rgba(212, 175, 55, 0.25);
}
.btn-run-job {
  background: linear-gradient(135deg, #d4af37 0%, #b89320 100%);
  color: #0b1120;
  font-weight: 800;
  font-size: 0.9rem;
  padding: 0 22px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s;
}
.btn-run-job:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(212, 175, 55, 0.35);
}
.btn-run-job:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Latest Run Box */
.latest-run-box {
  padding: 14px 16px;
  border-radius: 12px;
  font-size: 0.84rem;
}
.latest-ok {
  background: rgba(34, 197, 94, 0.08);
  border: 1px solid rgba(34, 197, 94, 0.22);
}
.latest-fail {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.22);
}
.latest-badge {
  font-weight: 800;
  font-size: 0.78rem;
  letter-spacing: 0.04em;
  margin-bottom: 6px;
}
.latest-ok .latest-badge { color: #86efac; }
.latest-fail .latest-badge { color: #fca5a5; }
.latest-details {
  display: flex;
  gap: 14px;
  flex-wrap: wrap;
  color: #cbd5e1;
}
.latest-msg {
  margin: 6px 0 0;
  color: #94a3b8;
}

/* Summary Chips Grid */
.summary-chips-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
}
.summary-chip {
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.06);
  padding: 14px;
  border-radius: 12px;
}
.chip-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.8rem;
  color: #94a3b8;
  margin-bottom: 4px;
}
.chip-val {
  font-size: 1.4rem;
  font-weight: 800;
  color: #ffffff;
}
.chip-status {
  font-size: 0.74rem;
  margin-top: 2px;
}

/* Tabs */
.tabs-container {
  background: rgba(15, 23, 42, 0.7);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 18px;
  overflow: hidden;
  backdrop-filter: blur(12px);
}
.tabs-bar {
  display: flex;
  background: rgba(11, 17, 32, 0.9);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  overflow-x: auto;
}
.tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 16px 22px;
  background: none;
  border: none;
  color: #94a3b8;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  border-bottom: 2px solid transparent;
  white-space: nowrap;
  transition: all 0.2s;
}
.tab-btn:hover {
  color: #f8fafc;
  background: rgba(255, 255, 255, 0.03);
}
.tab-btn.active {
  color: #d4af37;
  border-bottom-color: #d4af37;
  background: rgba(212, 175, 55, 0.06);
}
.tab-count {
  background: rgba(255, 255, 255, 0.1);
  color: #e2e8f0;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
}
.count-gold { background: rgba(212, 175, 55, 0.2); color: #fde047; }
.count-danger { background: rgba(239, 68, 68, 0.25); color: #fca5a5; }

.tab-content {
  padding: 20px;
}

/* Filter Toolbar */
.filter-toolbar {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}
.search-wrap {
  flex: 1;
  min-width: 260px;
  position: relative;
}
.search-wrap i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}
.table-search-input {
  width: 100%;
  padding: 10px 14px 10px 38px;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: #ffffff;
  font-size: 0.88rem;
}
.table-search-input:focus {
  border-color: #d4af37;
  outline: none;
}
.table-filter-select {
  padding: 10px 14px;
  background: rgba(15, 23, 42, 0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 10px;
  color: #ffffff;
  font-size: 0.88rem;
}

/* Tables */
.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.86rem;
  color: #cbd5e1;
}
.data-table th {
  background: rgba(15, 23, 42, 0.95);
  color: #d4af37;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.76rem;
  letter-spacing: 0.05em;
  padding: 12px 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: left;
}
.data-table td {
  padding: 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  vertical-align: middle;
}
.data-table tr:hover td {
  background: rgba(255, 255, 255, 0.02);
}

.code-id {
  font-family: monospace;
  font-weight: 700;
  color: #94a3b8;
}
.job-tag {
  font-weight: 700;
  color: #f8fafc;
  display: block;
}
.job-friendly-sub {
  font-size: 0.76rem;
  color: #94a3b8;
}
.badge-status {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 800;
}
.status-ok { background: rgba(34, 197, 94, 0.15); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.3); }
.status-error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

.duration-pill {
  background: rgba(255, 255, 255, 0.06);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-family: monospace;
}
.msg-text {
  color: #94a3b8;
  font-size: 0.82rem;
}
.time-text {
  color: #94a3b8;
  font-size: 0.8rem;
  white-space: nowrap;
}
.empty-cell {
  text-align: center;
  padding: 40px !important;
  color: #64748b;
  font-size: 0.95rem;
}

/* Severity Badges */
.badge-severity {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.74rem;
  font-weight: 800;
}
.sev-high { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
.sev-medium { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.sev-low { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.4); }

.event-title { color: #f8fafc; font-size: 0.9rem; }
.event-msg { color: #cbd5e1; font-size: 0.82rem; }

/* Decision Assist */
.decision-header-info {
  background: rgba(212, 175, 55, 0.1);
  border: 1px solid rgba(212, 175, 55, 0.25);
  padding: 12px 16px;
  border-radius: 10px;
  margin-bottom: 16px;
  font-size: 0.86rem;
  color: #fef08a;
  display: flex;
  align-items: center;
}
.entity-badge {
  background: rgba(255, 255, 255, 0.08);
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 600;
}
.recommendation-content {
  font-size: 0.88rem;
  color: #f1f5f9;
  line-height: 1.4;
}
.badge-decision {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
}
.decision-open { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.decision-done { background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(34, 197, 94, 0.4); }
.decision-ignored { background: rgba(148, 163, 184, 0.2); color: #94a3b8; border: 1px solid rgba(148, 163, 184, 0.4); }

.action-btn-group {
  display: inline-flex;
  gap: 6px;
}
.btn-act-done {
  background: rgba(34, 197, 94, 0.2);
  border: 1px solid rgba(34, 197, 94, 0.45);
  color: #86efac;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-act-done:hover { background: rgba(34, 197, 94, 0.35); }
.btn-act-ignore {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #cbd5e1;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}
.btn-act-ignore:hover { background: rgba(255, 255, 255, 0.16); }

/* Priority & Health */
.priority-chip {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-weight: 700;
  font-size: 0.78rem;
}
.p-high { background: rgba(239, 68, 68, 0.2); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.4); }
.p-norm { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

.score-progress-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.score-num {
  font-weight: 800;
  font-size: 0.88rem;
  min-width: 32px;
}
.score-bar {
  flex: 1;
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 999px;
  overflow: hidden;
  max-width: 80px;
}
.score-fill {
  height: 100%;
  background: linear-gradient(90deg, #d4af37, #f59e0b);
  border-radius: 999px;
}
.badge-health {
  display: inline-flex;
  align-items: center;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 800;
}
.health-watch { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.4); }
.health-critical { background: rgba(239, 68, 68, 0.25); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.5); }

.btn-detail-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 8px;
  background: rgba(212, 175, 55, 0.14);
  border: 1px solid rgba(212, 175, 55, 0.35);
  color: #fde047;
  font-size: 0.8rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-detail-link:hover {
  background: rgba(212, 175, 55, 0.25);
  color: #ffffff;
}

/* Helpers */
.text-gold { color: #d4af37 !important; }
.text-cyan { color: #22d3ee !important; }
.text-danger { color: #f87171 !important; }
.text-warning { color: #fbbf24 !important; }
.text-success { color: #4ade80 !important; }
</style>
