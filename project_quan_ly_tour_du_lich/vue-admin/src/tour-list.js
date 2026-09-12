import { createApp } from 'vue';
import TourList from './views/TourList.vue';

const targetElement = document.getElementById('vue-admin-tour-list');

if (targetElement) {
  const initialData = window.__ADMIN_TOUR_LIST_INIT__ || null;
  const app = createApp(TourList, {
    initialData: initialData
  });
  app.mount('#vue-admin-tour-list');
}
