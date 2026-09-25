<script setup lang="ts">
import { 
  BadgeDollarSign, 
  ShoppingBag, 
  X, 
  Package, 
  Store, 
  Users, 
  PackageSearch, 
  Palette,
  History,
  Clock,
  Tv,
  ExternalLink
} from 'lucide-vue-next'
import { useRouter, useRoute } from 'vue-router'

defineProps<{
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
    <!-- Backdrop oscuro -->
    <Transition name="fade">
      <div v-if="isOpen" class="sidebar-overlay" @click="emit('close')"></div>
    </Transition>

    <!-- Sidebar lateral -->
    <Transition name="slide">
      <aside v-if="isOpen" class="admin-sidebar">
        <div class="sidebar-header">
          <div class="brand-group" @click="navigateTo('/general-home')">
            <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="sidebar-logo" />
            <span class="brand-name">J.Junior</span>
          </div>
          <button class="btn-close" @click="emit('close')" title="Cerrar menú">
            <X :size="20" />
          </button>
        </div>

        <nav class="sidebar-nav">
          <!-- SECCIÓN: OPERACIONES -->
          <div class="nav-section">
            <span class="section-title">Operaciones Diarias</span>
            
            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/orders') }"
              @click="navigateTo('/general-home/orders')"
              v-role="[1,3]"
            >
              <ShoppingBag :size="18" class="nav-icon" />
              <span>Pedidos & Comandas</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/generate-quote') }"
              @click="navigateTo('/general-home/generate-quote')"
              v-role="[1,3]"
            >
              <Store :size="18" class="nav-icon" />
              <span>Generar Pedido</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/cash-flow') }"
              @click="navigateTo('/general-home/admin/cash-flow')"
              v-role="[1,3]"
            >
              <BadgeDollarSign :size="18" class="nav-icon" />
              <span>Caja & Turnos</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/inventory') }"
              @click="navigateTo('/general-home/inventory')"
              v-role="[1,3]"
            >
              <Package :size="18" class="nav-icon" />
              <span>Stock & Kardex</span>
            </button>
          </div>

          <!-- SECCIÓN: CATÁLOGO Y PERSONALIZACIÓN -->
          <div class="nav-section" v-role="[1]">
            <span class="section-title">Catálogo & Tienda</span>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/product') }"
              @click="navigateTo('/general-home/admin/product')"
            >
              <PackageSearch :size="18" class="nav-icon" />
              <span>Catálogo & Carta</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/banners') }"
              @click="navigateTo('/general-home/admin/banners')"
            >
              <Palette :size="18" class="nav-icon" />
              <span>Banners & Avisos</span>
            </button>
          </div>

          <!-- SECCIÓN: ADMINISTRACIÓN Y CONTROL -->
          <div class="nav-section" v-role="[1]">
            <span class="section-title">Configuración & Control</span>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/worker') }"
              @click="navigateTo('/general-home/admin/worker')"
            >
              <Users :size="18" class="nav-icon" />
              <span>Trabajadores</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/schedules') }"
              @click="navigateTo('/general-home/admin/schedules')"
            >
              <Clock :size="18" class="nav-icon" />
              <span>Horarios de Atención</span>
            </button>

            <button 
              class="nav-item" 
              :class="{ active: isActive('/general-home/admin/history') }"
              @click="navigateTo('/general-home/admin/history')"
            >
              <History :size="18" class="nav-icon" />
              <span>Auditoría del Sistema</span>
            </button>

            <a 
              href="/menu-board" 
              target="_blank" 
              rel="noopener noreferrer"
              class="nav-item menu-tv-item"
            >
              <Tv :size="18" class="nav-icon" />
              <span>Menu Board (TV)</span>
              <ExternalLink :size="13" class="external-icon" />
            </a>
          </div>
        </nav>

        <div class="sidebar-footer">
          <span class="version-text">J.Junior 1.0</span>
        </div>
      </aside>
    </Transition>
  </div>
</template>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.sidebar-overlay {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(35, 20, 10, 0.55);
  backdrop-filter: blur(3px);
  z-index: 1001;
}

.admin-sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 290px;
  height: 100vh;
  background-color: #ffffff;
  z-index: 1002;
  display: flex;
  flex-direction: column;
  box-shadow: 8px 0 30px rgba(26, 14, 5, 0.16);
  border-right: 1px solid rgba(81, 49, 25, 0.08);
}

/* HEADER DEL SIDEBAR */
.sidebar-header {
  padding: 1.15rem 1.25rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: var(--DC-brown, #513119);
  color: #ffffff;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
}

.sidebar-logo {
  height: 36px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25));
}

.brand-name {
  color: #ffffff;
  font-family: 'Arial Black', Impact, sans-serif;
  font-style: italic;
  font-size: 1.35rem; 
  font-weight: 900;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
}

.btn-close {
  background: rgba(255, 255, 255, 0.12);
  border: none;
  color: #ffffff;
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background-color: var(--DC-orange, #e28743);
  transform: rotate(90deg);
}

/* NAVEGACIÓN Y SECCIONES */
.sidebar-nav {
  flex: 1;
  padding: 1.25rem 0.85rem;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  scrollbar-width: thin;
  scrollbar-color: rgba(81, 49, 25, 0.2) transparent;
}

.sidebar-nav::-webkit-scrollbar {
  width: 5px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background-color: rgba(81, 49, 25, 0.2);
  border-radius: 999px;
}

.nav-section {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.section-title {
  font-size: 0.72rem;
  font-weight: 800;
  color: var(--DC-text-gray, #7c7468);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  padding: 0 0.75rem;
  margin-bottom: 4px;
}

.nav-item {
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.65rem 0.85rem;
  border-radius: 12px;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  text-align: left;
  text-decoration: none;
  width: 100%;
}

.nav-icon {
  color: var(--DC-brown, #513119);
  transition: transform 0.2s ease, color 0.2s ease;
  flex-shrink: 0;
}

.nav-item:hover {
  background-color: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-orange, #e28743);
  transform: translateX(3px);
}

.nav-item:hover .nav-icon {
  color: var(--DC-orange, #e28743);
}

.nav-item.active {
  background-color: #fff4e6;
  color: var(--DC-orange, #e28743);
  font-weight: 800;
}

.nav-item.active .nav-icon {
  color: var(--DC-orange, #e28743);
}

.menu-tv-item {
  position: relative;
  justify-content: flex-start;
}

.external-icon {
  margin-left: auto;
  opacity: 0.55;
}

/* FOOTER */
.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
  text-align: center;
  background-color: #fffdfa;
}

.version-text {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

/* TRANSICIONES */
.fade-enter-active, 
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from, 
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active, 
.slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-enter-from, 
.slide-leave-to {
  transform: translateX(-100%);
}
</style>