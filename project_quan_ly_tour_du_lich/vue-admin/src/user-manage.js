import { createApp } from 'vue';
import UserManage from './views/UserManage.vue';

const targetElement = document.getElementById('vue-admin-user-manage');

if (targetElement) {
  const initialData = window.__ADMIN_USER_MANAGE_INIT__ || null;
  const app = createApp(UserManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-user-manage');
}
