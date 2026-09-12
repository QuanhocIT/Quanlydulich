import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  define: {
    'process.env.NODE_ENV': JSON.stringify('production')
  },
  build: {
    outDir: resolve(__dirname, '../public/dist/admin'),
    emptyOutDir: false,
    rollupOptions: {
      input: {
        dashboard: resolve(__dirname, 'src/main.js'),
        'tour-list': resolve(__dirname, 'src/tour-list.js'),
        'salary-detail': resolve(__dirname, 'src/salary-detail.js'),
        'salary-manage': resolve(__dirname, 'src/salary-manage.js'),
        'booking-manage': resolve(__dirname, 'src/booking-manage.js'),
        'user-manage': resolve(__dirname, 'src/user-manage.js'),
        'personnel-manage': resolve(__dirname, 'src/personnel-manage.js'),
        'schedule-manage': resolve(__dirname, 'src/schedule-manage.js'),
        'supplier-manage': resolve(__dirname, 'src/supplier-manage.js'),
        'automation-dashboard': resolve(__dirname, 'src/automation-dashboard.js'),
        'review-manage': resolve(__dirname, 'src/review-manage.js'),
        'finance-dashboard': resolve(__dirname, 'src/finance-dashboard.js')
      },
      output: {
        format: 'es',
        entryFileNames: '[name].js',
        chunkFileNames: 'chunks/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return '[name].css';
          }
          return 'assets/[name].[ext]';
        }
      }
    }
  }
});
