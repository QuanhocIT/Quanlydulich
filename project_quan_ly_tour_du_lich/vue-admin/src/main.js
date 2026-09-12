import { createApp } from 'vue';
import Dashboard from './views/Dashboard.vue';

const targetElement = document.getElementById('vue-admin-dashboard');

if (targetElement) {
  const initialData = window.__ADMIN_DASHBOARD_INIT__ || null;
  const app = createApp(Dashboard, {
    initialData: initialData
  });
  app.mount('#vue-admin-dashboard');
}
