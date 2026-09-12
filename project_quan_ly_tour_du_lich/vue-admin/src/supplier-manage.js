import { createApp } from 'vue';
import SupplierManage from './views/SupplierManage.vue';

const targetElement = document.getElementById('vue-admin-supplier-manage');

if (targetElement) {
  const initialData = window.__ADMIN_SUPPLIER_MANAGE_INIT__ || null;
  const app = createApp(SupplierManage, {
    initialData: initialData
  });
  app.mount('#vue-admin-supplier-manage');
}
