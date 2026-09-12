import { createApp } from 'vue';
import SalaryManage from './views/SalaryManage.vue';

const targetElement = document.getElementById('vue-admin-salary-manage');

if (targetElement) {
  const initialData = window.__ADMIN_SALARY_MANAGE_INIT__ || null;
  const app = createApp(SalaryManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-salary-manage');
}
