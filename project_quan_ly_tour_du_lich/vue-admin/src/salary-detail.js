import { createApp } from 'vue';
import SalaryDetail from './views/SalaryDetail.vue';

const targetElement = document.getElementById('vue-admin-salary-detail');

if (targetElement) {
  const initialData = window.__ADMIN_SALARY_DETAIL_INIT__ || null;
  const app = createApp(SalaryDetail, {
    initialData: initialData
  });
  app.mount('#vue-admin-salary-detail');
}
