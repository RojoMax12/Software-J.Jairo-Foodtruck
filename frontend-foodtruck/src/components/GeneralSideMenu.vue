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
            <img src="@/assets/logo_jairo.png" alt="Logo" class="sidebar-logo" />
            <span class="brand-name">J.Junior</span>
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
              :class="{ active: isActive('/general-home/orders') }"
              @click="navigateTo('/general-home/orders')"
              `v-role="[1,3]"`
            >
              <ShoppingBag :size="20" />
              <span>Pedidos</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/generate-quote') }"
              @click="navigateTo('/general-home/generate-quote')"
              `v-role="[1,3]"`
            >
              <FilePlus :size="20" />
              <span>Generar Pedidos</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/cash-flow') }"
              @click="navigateTo('/general-home/admin/cash-flow')"
              `v-role="[1]"`
            >
              <FilePlus :size="20" />
              <span>Caja</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/inventory') }"
              @click="navigateTo('/general-home/inventory')"
              `v-role="[1,3]"`
            >
              <FilePlus :size="20" />
              <span>Inventario</span>
            </button>


            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/product') }"
              @click="navigateTo('/general-home/admin/product')"
              `v-role="[1]"`
            >
              <FilePlus :size="20" />
              <span>Productos</span>
            </button>


            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/worker') }"
              @click="navigateTo('/general-home/admin/worker')"
             `v-role="[1]" descomentar para pruebas`
            >
              <FilePlus :size="20" />
              <span>Trabajadores</span>
            </button>
          </div>
        </nav>

        <div class="sidebar-footer">
          <span class="version-text">v1.0.0 - Panel</span>
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
  background-color: var(--button-color);
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
  border-bottom: 1px solid black;
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
  color: #ffffff;
  font-family: 'Arial Black', Impact, sans-serif;
  font-style: italic;
  
  font-size: 1.4rem; 
  
  font-weight: 900;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;

  /* Sombras reducidas de 3px a 2px para que el borde negro no aplaste el texto */
  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    -2px  0px 0 #000,  2px  0px 0 #000,  0px -2px 0 #000,  0px  2px 0 #000,
    4px  4px 0px rgba(0, 0, 0, 0.4);
}

.btn-close {
  background: none;
  border: none;
  color: var(--button-text);
  cursor: pointer;
  padding: 5px;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background-color: var(--DC-orange);
  color: var(--button-color);
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
  color: var(--button-text);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: left;
}

.nav-item:hover {
  background-color: var(--DC-orange);
  color: var(--button-color);
}

.nav-item.active {
  background-color: var(--DC-orange);
  color: var(--button-color);
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
