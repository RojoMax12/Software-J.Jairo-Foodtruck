<script setup lang="ts">
import { ref } from 'vue';
import { useRoute } from 'vue-router';
import Navbar from './components/Navbar.vue';
import AdminNavbar from './components/GeneralNavbar.vue';
import AdminSideMenu from './components/GeneralSideMenu.vue';
// 1. Importamos el estado global de notificaciones
import { useNotification } from '@/composables/useNotification';

const route = useRoute();
// 2. Extraemos el arreglo de notificaciones reactivas
const { notifications } = useNotification();

const isAdminSidebarOpen = ref(false);

const toggleAdminSidebar = () => {
  isAdminSidebarOpen.value = !isAdminSidebarOpen.value;
};
</script>

<template>
  <template v-if="!route.meta.hideNavbar">
    <template v-if="route.path.startsWith('/general-home')">
      <AdminNavbar @toggleSidebar="toggleAdminSidebar" />
      <AdminSideMenu :isOpen="isAdminSidebarOpen" @close="isAdminSidebarOpen = false" />
    </template>
    
    <Navbar v-else />
  </template>
  
  <router-view/>

  <div class="notification-container">
    <TransitionGroup name="toast">
      <div 
        v-for="notification in notifications" 
        :key="notification.id" 
        :class="['toast-card', `toast-${notification.type}`]"
      >
        <span class="toast-icon">{{ notification.type === 'success' ? '✅' : '❌' }}</span>
        <span class="toast-text">{{ notification.message }}</span>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
/* 🎨 4. ESTILOS DE LOS TOASTS FLOTANTES */

/* Contenedor fijo arriba a la derecha */
.notification-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999; /* Un número alto para que flote por encima de cualquier modal */
  display: flex;
  flex-direction: column;
  gap: 10px; /* Espacio entre alertas apiladas */
  pointer-events: none; /* Evita que el contenedor invisible bloquee clics en la app */
}

/* Tarjeta base de la notificación */
.toast-card {
  pointer-events: auto; /* Permite hacer clic en la alerta si fuera necesario */
  background-color: #fff;
  padding: 14px 20px;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 280px;
  max-width: 400px;
  border-left: 5px solid #ccc;
  font-family: 'Inter', sans-serif;
}

/* Colores según el tipo de alerta (Sincronizado con DiCreme) */
.toast-success {
  border-left-color: #2ec4b6;
  background-color: #f0fdfa;
  color: #115e59;
}

.toast-error {
  border-left-color: #e63946;
  background-color: #fff5f5;
  color: #9b1c1c;
}

.toast-icon {
  font-size: 1.2rem;
}

.toast-text {
  font-size: 0.95rem;
  font-weight: 500;
}

/* ✨ ANIMACIONES (Efecto deslizar desde la derecha) */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.4s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(50px) scale(0.9);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(100px);
}
</style>