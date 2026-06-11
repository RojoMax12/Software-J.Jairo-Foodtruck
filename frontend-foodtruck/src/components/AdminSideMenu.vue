<script setup lang="ts">
import { FileText, ShoppingBag, FilePlus, X } from 'lucide-vue-next'
import { useRouter, useRoute } from 'vue-router'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits(['close'])

const router = useRouter()
const route = useRoute()

const navigateTo = (path: string) => {
  router.push(path)
  emit('close')
}

const isActive = (path: string) => {
  return route.path === path
}
</script>

<template>
  <div>
    <!-- Overlay -->
    <Transition name="fade">
      <div v-if="isOpen" class="sidebar-overlay" @click="emit('close')"></div>
    </Transition>

    <!-- Sidebar -->
    <Transition name="slide">
      <aside v-if="isOpen" class="admin-sidebar">
        <div class="sidebar-header">
          <div class="brand-group">
            <img src="@/assets/logo_dicreme.png" alt="Logo" class="sidebar-logo" />
            <span class="brand-name">Di Creme</span>
          </div>
          <button class="btn-close" @click="emit('close')">
            <X :size="24" />
          </button>
        </div>

        <nav class="sidebar-nav">
          <div class="nav-section">
            <span class="section-title">Navegación</span>
            
            <button 
              class="nav-item" 
              :class="{ active: isActive('/admin/quotes') }"
              @click="navigateTo('/admin/quotes')"
            >
              <FileText :size="20" />
              <span>Cotizaciones</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/admin/orders') }"
              @click="navigateTo('/admin/orders')"
            >
              <ShoppingBag :size="20" />
              <span>Pedidos</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/admin/generate-quote') }"
              @click="navigateTo('/admin/generate-quote')"
            >
              <FilePlus :size="20" />
              <span>Generar Cotización</span>
            </button>
          </div>
        </nav>

        <div class="sidebar-footer">
          <span class="version-text">v1.0.0 - Admin Panel</span>
        </div>
      </aside>
    </Transition>
  </div>
</template>

<style scoped>
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(2px);
  z-index: 1001;
}

.admin-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 280px;
  height: 100vh;
  background-color: white;
  z-index: 1002;
  display: flex;
  flex-direction: column;
  box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid #eeedee;
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sidebar-logo {
  height: 40px;
}

.brand-name {
  font-weight: 800;
  font-size: 1.2rem;
  color: #1a1624;
  font-style: italic;
}

.btn-close {
  background: none;
  border: none;
  color: #9793a0;
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background-color: #fff0f3;
  color: #e4869f;
}

.sidebar-nav {
  flex: 1;
  padding: 20px 0;
}

.nav-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
  padding: 0 12px;
}

.section-title {
  font-size: 0.75rem;
  font-weight: 700;
  color: #9793a0;
  text-transform: uppercase;
  margin-left: 12px;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

.nav-item {
  background: none;
  border: none;
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  color: #322c44;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.nav-item:hover {
  background-color: #f8f7f8;
  color: #e4869f;
}

.nav-item.active {
  background-color: #fff0f3;
  color: #e4869f;
}

.sidebar-footer {
  padding: 20px;
  border-top: 1px solid #eeedee;
  text-align: center;
}

.version-text {
  font-size: 0.75rem;
  color: #9793a0;
}

/* Transitions */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-enter-from, .slide-leave-to {
  transform: translateX(-100%);
}
</style>
