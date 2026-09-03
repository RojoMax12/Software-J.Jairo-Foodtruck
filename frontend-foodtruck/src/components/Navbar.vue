<template>
  <header class="navbar-wrapper">
    <nav class="dc-navbar">
      <div class="nav-left">
        <div class="brand-group" @click="goToHome">
          <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">J.Junior</span>
          </div>
        </div>
      </div>

      <div class="nav-right">
        <!-- BOTÓN DIRECTO "MIS PEDIDOS" CUANDO ESTÁ AUTENTICADO -->
        <button 
          v-if="isLoggedIn" 
          class="btn-nav-action btn-my-orders" 
          @click="router.push('/mis-pedidos')"
          title="Ver mi historial de pedidos anteriores"
        >
          <Receipt :size="15" />
          <span class="btn-label">Mis Pedidos</span>
        </button>

        <!-- BOTÓN "REVISA TU PEDIDO" (SEGUIMIENTO POR NÚMERO) -->
        <button 
          v-if="showCheckOrderButton && !isLoggedIn" 
          class="btn-nav-action btn-check-order" 
          @click="router.push('/checkorderstatus')"
          title="Consultar estado de mi pedido"
        >
          <Search :size="15" />
          <span class="btn-label">Revisa tu pedido</span>
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
                <button class="dropdown-item" @click="navigateTo('/mis-pedidos')">
                  <Receipt :size="16" class="item-icon" />
                  <span>Mis Pedidos Anteriores</span>
                </button>

                <button class="dropdown-item" @click="navigateTo('/checkorderstatus')">
                  <Search :size="16" class="item-icon" />
                  <span>Buscar Pedido por N°</span>
                </button>

                <button class="dropdown-item" @click="navigateTo('/mi-perfil')">
                  <User :size="16" class="item-icon" />
                  <span>Mi Perfil / Mis Datos</span>
                </button>

                <button v-if="isAdminOrStaff" class="dropdown-item staff-item" @click="navigateTo('/general-home')">
                  <LayoutDashboard :size="16" class="item-icon" />
                  <span>Panel de Administración</span>
                </button>
              </div>

              <div class="dropdown-divider"></div>

              <div class="dropdown-footer">
                <button class="dropdown-item logout-item" @click="handleLogout">
                  <LogOut :size="16" class="item-icon" />
                  <span>Cerrar Sesión</span>
                </button>
              </div>
            </div>
          </Transition>
        </div>

        <!-- BOTÓN "INGRESAR" CUANDO NO ESTÁ AUTENTICADO -->
        <button 
          v-else-if="showCheckOrderButton" 
          class="btn-nav-action btn-login" 
          @click="router.push('/login')"
          title="Iniciar sesión en el sistema"
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

// --- ESTADOS REACTIVOS ---
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
      console.error('Error parsing user session inside Navbar:', e)
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
.navbar-wrapper {
  position: static;
  top: 0;
  z-index: 999;
  width: 100%;
}

.dc-navbar {
  background-color: var(--DC-brown, #513119);
  height: 72px;
  padding: 0 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
  font-family: var(--font-main, sans-serif);
  transition: all 0.3s ease;
  width: 100%;
  box-sizing: border-box;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 12px;
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
  transform: scale(1.02);
}

.brand-logo {
  height: 48px;
  width: auto;
  object-fit: contain;
  transition: height 0.3s ease;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-text {
  color: #ffffff;
  font-family: 'Arial Black', Impact, sans-serif;
  font-style: italic;
  font-size: clamp(1.25rem, 3vw, 2rem);
  font-weight: 900;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;

  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    -2px  0px 0 #000,  2px  0px 0 #000,  0px -2px 0 #000,  0px  2px 0 #000,
    3px  3px 0px rgba(0, 0, 0, 0.4);
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-shrink: 0;
}

.btn-nav-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  border: none;
  padding: 8px 16px;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-my-orders {
  background-color: var(--DC-orange, #eb6e30);
  color: #ffffff;
  box-shadow: 0 2px 10px rgba(235, 110, 48, 0.3);
}

.btn-my-orders:hover {
  background-color: #d95d20;
  transform: translateY(-1px);
}

.btn-check-order {
  background-color: rgba(255, 255, 255, 0.12);
  color: #ffffff;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
}

.btn-check-order:hover {
  background-color: #ff6b00;
  border-color: #ff6b00;
  color: #ffffff;
  transform: translateY(-1px);
}

.btn-login {
  background-color: #F4E1D2;
  color: #513119;
}

.btn-login:hover {
  background-color: #E28743;
  color: #ffffff;
  transform: translateY(-1px);
}

/* USER MENU & DROPDOWN */
.user-menu-container {
  position: relative;
}

.client-user-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #F4E1D2;
  padding: 5px 12px 5px 6px;
  border-radius: 999px;
  border: 1px solid #513119;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.client-user-badge:hover {
  background-color: #ffe6d4;
  transform: translateY(-1px);
}

.client-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background-color: #513119;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.client-name {
  font-size: 0.84rem;
  font-weight: 800;
  color: #513119;
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-arrow {
  color: #513119;
  transition: transform 0.2s ease;
}

.dropdown-arrow.rotate {
  transform: rotate(180deg);
}

.user-dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 230px;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.18);
  border: 1px solid #efeaf5;
  padding: 10px 0;
  z-index: 1001;
  overflow: hidden;
  animation: scaleDown 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-header {
  padding: 8px 16px 10px 16px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dropdown-user-name {
  font-weight: 800;
  font-size: 0.9rem;
  color: #2b213a;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-user-role {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--DC-orange, #eb6e30);
  text-transform: uppercase;
}

.dropdown-divider {
  height: 1px;
  background: #f0edf6;
  margin: 6px 0;
}

.dropdown-items, .dropdown-footer {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 9px 16px;
  background: transparent;
  border: none;
  color: #4a415a;
  font-size: 0.84rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  text-align: left;
}

.dropdown-item:hover {
  background: #faf8fd;
  color: var(--DC-orange, #eb6e30);
}

.dropdown-item .item-icon {
  color: #8c849c;
  transition: color 0.15s;
}

.dropdown-item:hover .item-icon {
  color: var(--DC-orange, #eb6e30);
}

.dropdown-item.staff-item {
  color: #1e1b4b;
  font-weight: 700;
}

.dropdown-item.logout-item {
  color: #dc2626;
}

.dropdown-item.logout-item:hover {
  background: #fef2f2;
  color: #b91c1c;
}

.dropdown-item.logout-item .item-icon {
  color: #dc2626;
}

/* TRANSITIONS */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}

/* --- MEDIAS QUERIES RESPONSIVAS --- */
@media (max-width: 768px) {
  .dc-navbar {
    height: 60px;
    padding: 0 12px;
  }

  .brand-logo {
    height: 38px;
  }

  .brand-text {
    font-size: 1.2rem;
  }

  .nav-right {
    gap: 6px;
  }

  .btn-nav-action {
    padding: 7px 12px;
    font-size: 0.76rem;
    gap: 4px;
  }
}

@media (max-width: 600px) {
  .brand-group {
    gap: 8px;
  }

  .brand-logo {
    height: 34px;
  }

  .brand-text {
    font-size: 1.15rem;
    letter-spacing: 0.5px;
  }

  .btn-check-order .btn-label, 
  .btn-my-orders .btn-label,
  .btn-login .btn-label {
    display: none;
  }
  
  .btn-check-order, 
  .btn-my-orders,
  .btn-login {
    padding: 0;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    justify-content: center;
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
    padding: 0 10px;
    height: 56px;
  }

  .brand-group {
    gap: 6px;
  }

  .brand-logo {
    height: 30px;
  }

  .brand-text {
    font-size: 1.05rem;
  }

  .btn-check-order, 
  .btn-my-orders,
  .btn-login {
    width: 32px;
    height: 32px;
  }
}
</style>