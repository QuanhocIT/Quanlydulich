import { createApp } from 'vue';
import ReviewManage from './views/ReviewManage.vue';

const targetElement = document.getElementById('vue-admin-review-manage');

if (targetElement) {
  const initialData = window.__ADMIN_DANH_GIA_INIT__ || null;
  const app = createApp(ReviewManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-review-manage');
}
