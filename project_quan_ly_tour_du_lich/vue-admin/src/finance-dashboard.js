import { createApp } from 'vue';
import FinanceDashboard from './views/FinanceDashboard.vue';

const targetElement = document.getElementById('vue-admin-finance-dashboard');

if (targetElement) {
  const initialData = window.__ADMIN_FINANCE_INIT__ || null;
  const app = createApp(FinanceDashboard, {
    initialData: initialData
  });
  app.mount('#vue-admin-finance-dashboard');
}
