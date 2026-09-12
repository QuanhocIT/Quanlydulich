import { createApp } from 'vue';
import ScheduleManage from './views/ScheduleManage.vue';

const targetElement = document.getElementById('vue-admin-schedule-manage');

if (targetElement) {
  const initialData = window.__ADMIN_SCHEDULE_MANAGE_INIT__ || null;
  const app = createApp(ScheduleManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-schedule-manage');
}
