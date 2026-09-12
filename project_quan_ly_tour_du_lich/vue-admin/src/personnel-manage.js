import { createApp } from 'vue';
import PersonnelManage from './views/PersonnelManage.vue';

const targetElement = document.getElementById('vue-admin-personnel-manage');

if (targetElement) {
  const initialData = window.__ADMIN_PERSONNEL_MANAGE_INIT__ || null;
  const app = createApp(PersonnelManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-personnel-manage');
}
