<template>
  <header class="navbar-wrapper">
    <nav class="dc-navbar">
      <!-- LADO IZQUIERDO: LOGO Y MARCA -->
      <div class="nav-left">
        <div class="brand-group" @click="goToHome" title="Ir al inicio">
          <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">J.Junior</span>
          </div>
        </div>
      </div>

      <!-- LADO DERECHO: ACCIONES Y PERFIL -->
      <div class="nav-right">
        <!-- BOTÓN "MIS PEDIDOS" (LOGUEADO) -->
        <button 
          v-if="isLoggedIn" 
          type="button"
          class="btn-nav-action btn-nav-primary" 
          @click="router.push('/mis-pedidos')"
          title="Ver mis pedidos anteriores"
        >
          <Receipt :size="15" />
          <span class="btn-label">Mis Pedidos</span>
        </button>

        <!-- BOTÓN "REVISA TU PEDIDO" (INVITADO) -->
        <button 
          v-if="showCheckOrderButton && !isLoggedIn" 
          type="button"
          class="btn-nav-action btn-nav-outline" 
          @click="router.push('/checkorderstatus')"
          title="Consultar estado de mi pedido con número de comanda"
        >
          <Search :size="15" />
          <span class="btn-label btn-label-full">Revisa tu pedido</span>
          <span class="btn-label btn-label-short">Rastrear</span>
        </button>

        <!-- PERFIL DE USUARIO / MENÚ DESPLEGABLE -->
        <div v-if="isLoggedIn" class="user-menu-container">
          <div class="client-user-badge" @click="toggleUserMenu" title="Opciones de cuenta">
            <div class="client-avatar">
              <span>{{ userInitials }}</span>
            </div>
            <span class="client-name">{{ username }}</span>
            <ChevronDown :size="14" class="dropdown-arrow" :class="{ 'rotate': isUserMenuOpen }" />
          </div>

          <!-- MENÚ DESPLEGABLE FLOTANTE -->
          <Transition name="dropdown-fade">
            <div v-if="isUserMenuOpen" class="user-dropdown-menu" @click.stop>
              <div class="dropdown-header">
                <span class="dropdown-user-name">{{ username }}</span>
                <span class="dropdown-user-role">{{ roleName }}</span>
              </div>

              <div class="dropdown-divider"></div>

              <div class="dropdown-items">
                <button type="button" class="dropdown-item" @click="navigateTo('/mis-pedidos')">
                  <Receipt :size="15" class="item-icon" />
                  <span>Mis Pedidos Anteriores</span>
                </button>

                <button type="button" class="dropdown-item" @click="navigateTo('/checkorderstatus')">
                  <Search :size="15" class="item-icon" />
                  <span>Buscar por N° de Comanda</span>
                </button>

                <button type="button" class="dropdown-item" @click="navigateTo('/mi-perfil')">
                  <User :size="15" class="item-icon" />
                  <span>Mi Perfil y Datos</span>
                </button>

                <button v-if="isAdminOrStaff" type="button" class="dropdown-item staff-item" @click="navigateTo('/general-home')">
                  <LayoutDashboard :size="15" class="item-icon" />
                  <span>Panel de Administración</span>
                </button>
              </div>

              <div class="dropdown-divider"></div>

              <div class="dropdown-footer">
                <button type="button" class="dropdown-item logout-item" @click="handleLogout">
                  <LogOut :size="15" class="item-icon" />
                  <span>Cerrar Sesión</span>
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- BOTÓN "INGRESAR" (INVITADO) -->
        <button 
          v-else-if="showCheckOrderButton" 
          type="button"
          class="btn-nav-action btn-nav-secondary" 
          @click="router.push('/login')"
          title="Iniciar sesión en tu cuenta"
        >
          <LogIn :size="15" />
          <span class="btn-label">Ingresar</span>
        </button>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { 
  Search, LogIn, Receipt, ChevronDown, 
  LogOut, LayoutDashboard, User 
} from 'lucide-vue-next'
import { getUserInitials } from '@/composables/useUserInitials'

const router = useRouter()
const route = useRoute()

const username = ref('')
const roleId = ref<number | null>(null)
const isLoggedIn = ref(false)
const isUserMenuOpen = ref(false)

const userInitials = computed(() => getUserInitials(username.value))
const showCheckOrderButton = computed(() => route.path !== '/checkorderstatus')
const isAdminOrStaff = computed(() => roleId.value === 1 || roleId.value === 3)

const roleName = computed(() => {
  if (roleId.value === 1) return 'Administrador'
  if (roleId.value === 3) return 'Trabajador'
  return 'Cliente'
})

const checkAuth = () => {
  const userParsed = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  
  if (userParsed && token) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre || userObj.nombre_empresa || userObj.nombre_usuario || 'Usuario'
      roleId.value = Number(userObj.id_rol || 2)
      isLoggedIn.value = true
    } catch (e) {
      console.error('Error parseando usuario en Navbar:', e)
      isLoggedIn.value = false
    }
  } else {
    isLoggedIn.value = false
    username.value = ''
    roleId.value = null
  }
}

const toggleUserMenu = () => {
  isUserMenuOpen.value = !isUserMenuOpen.value
}

const closeUserMenu = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.user-menu-container')) {
    isUserMenuOpen.value = false
  }
}

const navigateTo = (path: string) => {
  isUserMenuOpen.value = false
  router.push(path)
}

const handleLogout = () => {
  isUserMenuOpen.value = false
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem('access_token')
  sessionStorage.clear()
  isLoggedIn.value = false
  username.value = ''
  roleId.value = null
  router.push('/')
}

const goToHome = () => {
  router.push('/')
}

onMounted(() => {
  checkAuth()
  window.addEventListener('click', closeUserMenu)
  window.addEventListener('storage', checkAuth)
})

onUnmounted(() => {
  window.removeEventListener('click', closeUserMenu)
  window.removeEventListener('storage', checkAuth)
})

watch(() => route.path, () => {
  checkAuth()
  isUserMenuOpen.value = false
})
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.navbar-wrapper {
  position: sticky;
  top: 0;
  z-index: 999;
  width: 100%;
}

.dc-navbar {
  background-color: var(--DC-brown, #513119);
  height: 68px;
  padding: 0 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 18px rgba(26, 14, 5, 0.16);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  width: 100%;
}

/* BRANDING */
.nav-left {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  transition: transform 0.2s ease;
}

.brand-group:hover {
  transform: translateY(-1px);
}

.brand-logo {
  height: 44px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.25));
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-text {
  color: #ffffff;
  font-family: 'Arial Black', Impact, sans-serif;
  font-style: italic;
  font-size: clamp(1.2rem, 2.5vw, 1.7rem);
  font-weight: 900;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.45);
}

/* ACCIONES NAVEGACIÓN */
.nav-right {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-shrink: 0;
}

.btn-nav-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  padding: 0.55rem 1rem;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.02em;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-label-short {
  display: none;
}

/* Botón primario (Mis pedidos) */
.btn-nav-primary {
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  box-shadow: 0 2px 10px rgba(226, 135, 67, 0.35);
}

.btn-nav-primary:hover {
  background-color: #d1752f;
  transform: translateY(-1px);
}

/* Botón outline (Rastrear pedido) */
.btn-nav-outline {
  background-color: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}

.btn-nav-outline:hover {
  background-color: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

/* Botón secundario suave (Ingresar) */
.btn-nav-secondary {
  background-color: #fdfaf6;
  color: var(--DC-brown, #513119);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.btn-nav-secondary:hover {
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  transform: translateY(-1px);
}

/* USER MENU Y BADGE */
.user-menu-container {
  position: relative;
}

.client-user-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(255, 255, 255, 0.12);
  border: 1.5px solid rgba(255, 255, 255, 0.22);
  padding: 4px 12px 4px 5px;
  border-radius: 999px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
  backdrop-filter: blur(8px);
}

.client-user-badge:hover {
  background-color: rgba(255, 255, 255, 0.2);
  border-color: rgba(255, 255, 255, 0.4);
}

.client-avatar {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 0.74rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  flex-shrink: 0;
}

.client-name {
  font-size: 0.82rem;
  font-weight: 800;
  color: #ffffff;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-arrow {
  color: rgba(255, 255, 255, 0.7);
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-arrow.rotate {
  transform: rotate(180deg);
}

/* MENÚ DESPLEGABLE */
.user-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 230px;
  background: #ffffff;
  border-radius: 14px;
  box-shadow: 0 12px 32px rgba(26, 14, 5, 0.18);
  border: 1px solid rgba(81, 49, 25, 0.1);
  padding: 8px 0;
  z-index: 1001;
  overflow: hidden;
}

.dropdown-header {
  padding: 8px 16px 6px 16px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dropdown-user-name {
  font-weight: 800;
  font-size: 0.88rem;
  color: var(--DC-gray, #2c2724);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-user-role {
  font-size: 0.7rem;
  font-weight: 800;
  color: var(--DC-orange, #e28743);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.dropdown-divider {
  height: 1px;
  background: rgba(81, 49, 25, 0.08);
  margin: 6px 0;
}

.dropdown-items,
.dropdown-footer {
  display: flex;
  flex-direction: column;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 9px;
  width: 100%;
  padding: 8px 16px;
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.15s ease, color 0.15s ease;
  text-align: left;
}

.dropdown-item:hover {
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
}

.dropdown-item .item-icon {
  color: #a89f95;
  transition: color 0.15s;
  flex-shrink: 0;
}

.dropdown-item:hover .item-icon {
  color: var(--DC-orange, #e28743);
}

.dropdown-item.staff-item {
  color: var(--DC-brown, #513119);
  font-weight: 800;
}

.dropdown-item.logout-item {
  color: var(--DC-pink, #d80056);
}

.dropdown-item.logout-item .item-icon {
  color: var(--DC-pink, #d80056);
}

.dropdown-item.logout-item:hover {
  background: #fff5f5;
  color: #be123c;
}

/* TRANSICIONES DROPDOWN */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

/* RESPONSIVO */
@media (max-width: 768px) {
  .dc-navbar {
    height: 60px;
    padding: 0 1rem;
  }

  .brand-logo {
    height: 38px;
  }

  .btn-nav-action {
    padding: 0.45rem 0.85rem;
    font-size: 0.78rem;
  }
}

@media (max-width: 600px) {
  .brand-group {
    gap: 8px;
  }

  .brand-logo {
    height: 34px;
  }

  .btn-check-order .btn-label-full, 
  .btn-nav-primary .btn-label,
  .btn-nav-secondary .btn-label {
    display: none;
  }

  .btn-check-order .btn-label-short {
    display: inline;
    font-size: 0.74rem;
  }
  
  .btn-nav-primary,
  .btn-nav-secondary {
    padding: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    justify-content: center;
  }

  .btn-nav-outline {
    padding: 0.4rem 0.75rem;
    height: 34px;
    font-size: 0.74rem;
  }

  .client-name {
    display: none;
  }

  .client-user-badge {
    padding: 3px;
  }
}

@media (max-width: 420px) {
  .dc-navbar {
    padding: 0 0.75rem;
    height: 56px;
  }

  .brand-logo {
    height: 28px;
  }

  .btn-nav-primary,
  .btn-nav-secondary {
    width: 32px;
    height: 32px;
  }

  .btn-nav-outline {
    padding: 0.35rem 0.65rem;
    height: 32px;
    font-size: 0.7rem;
  }
}
</style>