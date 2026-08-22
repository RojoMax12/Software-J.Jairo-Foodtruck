<template>
  <template v-if="!route.meta.hideNavbar">
    <template v-if="route.path.startsWith('/general-home')">
      <AdminNavbar @toggleSidebar="toggleAdminSidebar" />
      <AdminSideMenu :isOpen="isAdminSidebarOpen" @close="isAdminSidebarOpen = false" />
    </template>
    <Navbar v-else />
    <AnnouncementBar v-if="route.path === '/'" />
  </template>

  <GlobalLoader />
  <router-view v-if="!globalLoading"/>

  <div class="notification-container" aria-live="polite">
    <TransitionGroup name="toast">
      <div 
        v-for="notification in notifications" 
        :key="notification.id" 
        :class="['toast-card', `toast-${notification.type}`]"
      >
        <div class="toast-icon-container">
          <CheckCircle2 v-if="notification.type === 'success'" :size="20" class="toast-icon-svg" />
          <AlertTriangle v-else-if="notification.type === 'warning'" :size="20" class="toast-icon-svg" />
          <XCircle v-else :size="20" class="toast-icon-svg" />
        </div>
        <div class="toast-body">
          <span class="toast-title">{{ getToastTitle(notification.type) }}</span>
          <span class="toast-text">{{ notification.message }}</span>
        </div>
        <button class="toast-close-btn" @click="dismissNotification(notification.id)" title="Cerrar">
          <X :size="14" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import AnnouncementBar from './components/AnnouncementBar.vue';
import Navbar from './components/Navbar.vue';
import AdminNavbar from './components/GeneralNavbar.vue';
import AdminSideMenu from './components/GeneralSideMenu.vue';
import GlobalLoader from './components/LoadingScreen.vue';
import { useNotification } from '@/composables/useNotification';
import { globalLoading } from '@/composables/useLoading';
import { CheckCircle2, AlertTriangle, XCircle, X } from 'lucide-vue-next';

const route = useRoute();
const { notifications, dismissNotification } = useNotification();

const isAdminSidebarOpen = ref(false);

const toggleAdminSidebar = () => {
  isAdminSidebarOpen.value = !isAdminSidebarOpen.value;
};

const getToastTitle = (type: string) => {
  if (type === 'success') return 'Éxito';
  if (type === 'warning') return 'Atención';
  return 'Error';
};
</script>

<style scoped>
.notification-container {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  pointer-events: none;
  max-width: 420px;
  width: calc(100vw - 48px);
}

.toast-card {
  pointer-events: auto;
  background: rgba(255, 255, 255, 0.96);
  backdrop-filter: blur(12px);
  padding: 14px 16px;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  gap: 12px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  font-family: inherit;
  position: relative;
  overflow: hidden;
  transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-icon-container {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  flex-shrink: 0;
}

.toast-success {
  border-left: 4px solid #10b981;
}
.toast-success .toast-icon-container {
  background-color: #ecfdf5;
  color: #10b981;
}

.toast-warning {
  border-left: 4px solid #f59e0b;
}
.toast-warning .toast-icon-container {
  background-color: #fffbeb;
  color: #f59e0b;
}

.toast-error {
  border-left: 4px solid #ef4444;
}
.toast-error .toast-icon-container {
  background-color: #fef2f2;
  color: #ef4444;
}

.toast-body {
  display: flex;
  flex-direction: column;
  gap: 2px;
  flex: 1;
}

.toast-title {
  font-size: 0.85rem;
  font-weight: 800;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.toast-text {
  font-size: 0.9rem;
  font-weight: 600;
  color: #4b5563;
  line-height: 1.3;
}

.toast-close-btn {
  background: transparent;
  border: none;
  color: #9ca3af;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}
.toast-close-btn:hover {
  background-color: #f3f4f6;
  color: #374151;
}

/* ✨ ANIMACIONES SUAVES */
.toast-enter-active {
  transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-leave-active {
  transition: all 0.25s cubic-bezier(0.7, 0, 0.84, 0);
}

.toast-enter-from {
  opacity: 0;
  transform: translateY(-20px) scale(0.92);
}
.toast-leave-to {
  opacity: 0;
  transform: translateX(60px) scale(0.95);
}
</style>