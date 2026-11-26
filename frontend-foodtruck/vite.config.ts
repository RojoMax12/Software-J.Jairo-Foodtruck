import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig(({ mode }) => ({
  base: '/',
  plugins: [
    vue(),
    ...(mode === 'development' ? [vueDevTools()] : []),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  build: {
    rollupOptions: {
      output: {
        manualChunks(id: string) {
          if (id.includes('node_modules')) {
            if (id.includes('lucide-vue-next')) return 'vendor-icons';
            if (id.includes('axios')) return 'vendor-http';
            if (
              id.includes('vue-router') ||
              id.includes('/vue/') ||
              id.includes('\\vue\\') ||
              id.includes('@vue')
            ) {
              return 'vendor-vue';
            }
          }
        },
      },
    },
  },
}))
