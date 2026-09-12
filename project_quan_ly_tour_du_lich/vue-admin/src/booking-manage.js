import { createApp } from 'vue';
import BookingManage from './views/BookingManage.vue';

const targetElement = document.getElementById('vue-admin-booking-manage');

if (targetElement) {
  const initialData = window.__ADMIN_BOOKING_MANAGE_INIT__ || null;
  const app = createApp(BookingManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-booking-manage');
}
