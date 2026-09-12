import { createApp } from 'vue';
import AutomationDashboard from './views/AutomationDashboard.vue';

const targetElement = document.getElementById('vue-admin-automation-dashboard');

if (targetElement) {
  const initialData = window.__ADMIN_AUTOMATION_INIT__ || null;
  const app = createApp(AutomationDashboard, {
    initialData: initialData
  });
  app.mount('#vue-admin-automation-dashboard');
}
