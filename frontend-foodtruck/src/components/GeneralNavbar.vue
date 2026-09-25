<template>
  <header class="navbar-wrapper">
    <nav class="admin-navbar">
      <!-- LADO IZQUIERDO: MENÚ HAMBURGUESA + LOGO -->
      <div class="nav-left">
        <button 
          type="button" 
          class="btn-menu" 
          @click="toggleSidebar" 
          title="Abrir menú lateral"
          aria-label="Abrir menú lateral"
        >
          <Menu :size="22" />
        </button>
        
        <div class="brand-group" @click="goToHome" title="Ir al panel principal">
          <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">J.Junior</span>
          </div>
        </div>
      </div>

      <!-- LADO DERECHO: ACCIONES Y SESIÓN -->
      <div class="nav-right">
        <!-- BOTÓN VER TIENDA PÚBLICA -->
        <button 
          type="button" 
          class="btn-nav-outline" 
          @click="goToPreview" 
          title="Ver cómo ve la tienda el cliente en vivo"
        >
          <Eye :size="15" />
          <span class="preview-btn-label">Ver Tienda</span>
        </button>

        <!-- INSIGNIA DE USUARIO / SESIÓN -->
        <div class="session-display" :title="username">
          <div class="user-avatar">
            <span class="avatar-initials">{{ userInitials }}</span>
          </div>
          <div class="user-details">
            <span class="user-role">{{ userRoleName }}</span>
            <span class="user-name">{{ username }}</span>
          </div>
        </div>

        <!-- BOTÓN CERRAR SESIÓN -->
        <button 
          type="button" 
          class="btn-logout-icon" 
          @click="handleLogout" 
          title="Cerrar Sesión del sistema"
          aria-label="Cerrar sesión"
        >
          <LogOut :size="16" />
        </button>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { LogOut, Eye, Menu } from 'lucide-vue-next'
import { getUserInitials } from '@/composables/useUserInitials'

const router = useRouter()
const route = useRoute()
const username = ref('Usuario')
const userRoleName = ref('Administrador') 
const roleId = ref<number | null>(null)

const emit = defineEmits(['toggleSidebar'])

const checkAuth = () => {
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre || userObj.nombre_usuario || userObj.nombre_empresa || userObj.name || userObj.correo || 'Usuario'
      const rId = Number(userObj.id_rol || 1)
      roleId.value = rId
      
      if (rId === 1) {
        userRoleName.value = 'Administrador'
      } else if (rId === 2) {
        userRoleName.value = 'Cliente'
      } else if (rId === 3) {
        userRoleName.value = 'Trabajador'
      } else {
        userRoleName.value = userObj.rol?.nombre_rol || 'Staff'
      }
    } catch (e) {
      console.error('Error parseando sesión de usuario en AdminNavbar:', e)
    }
  }
}

onMounted(() => {
  checkAuth()
  window.addEventListener('storage', checkAuth)
})

onUnmounted(() => {
  window.removeEventListener('storage', checkAuth)
})

watch(() => route.path, () => {
  checkAuth()
})

const userInitials = computed(() => getUserInitials(username.value))

const goToPreview = () => {
  router.push('/')
}

const handleLogout = () => {
  localStorage.clear()
  sessionStorage.clear()
  router.push('/')
}

const goToHome = () => {
  router.push('/general-home')
}

const toggleSidebar = () => {
  emit('toggleSidebar')
}
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.navbar-wrapper {
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
}

.admin-navbar {
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

/* LADO IZQUIERDO */
.nav-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
  min-width: 0;
}

.btn-menu {
  background: transparent;
  border: none;
  color: #ffffff;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 8px;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-menu:hover {
  background-color: rgba(255, 255, 255, 0.14);
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  user-select: none;
  flex-shrink: 0;
  transition: transform 0.2s ease;
}

.brand-group:hover {
  transform: translateY(-1px);
}

.brand-logo {
  height: 42px;
  width: auto;
  object-fit: contain;
  flex-shrink: 0;
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

/* LADO DERECHO */
.nav-right {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-shrink: 0;
}

/* BOTÓN PREVIEW TIENDA */
.btn-nav-outline {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background-color: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1.5px solid rgba(255, 255, 255, 0.25);
  backdrop-filter: blur(8px);
  padding: 0.45rem 0.95rem;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-nav-outline:hover {
  background-color: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

/* INSIGNIA DE SESIÓN */
.session-display {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: rgba(255, 255, 255, 0.12);
  border: 1.5px solid rgba(255, 255, 255, 0.22);
  padding: 4px 12px 4px 5px;
  border-radius: 999px;
  backdrop-filter: blur(8px);
}

.user-avatar {
  width: 28px;
  height: 28px;
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  font-size: 0.74rem;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  flex-shrink: 0;
}

.avatar-initials {
  text-transform: uppercase;
  color: #ffffff;
}

.user-details {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-role { 
  font-size: 0.65rem; 
  color: var(--DC-orange, #e28743); 
  font-weight: 800; 
  text-transform: uppercase; 
  letter-spacing: 0.04em;
  line-height: 1;
}

.user-name { 
  font-size: 0.82rem; 
  font-weight: 800; 
  color: #ffffff; 
  white-space: nowrap; 
  max-width: 120px;
  overflow: hidden;
  text-overflow: ellipsis;
  margin-top: 2px;
}

/* BOTÓN LOGOUT */
.btn-logout-icon {
  background-color: rgba(255, 255, 255, 0.1);
  color: #ffffff;
  border: 1.5px solid rgba(255, 255, 255, 0.22);
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-logout-icon:hover {
  background-color: var(--DC-pink, #d80056);
  border-color: var(--DC-pink, #d80056);
  transform: translateY(-1px);
}

/* ====================================================
   RESPONSIVIDAD MÓVIL
==================================================== */
@media (max-width: 768px) {
  .admin-navbar {
    padding: 0 1rem;
    height: 60px;
  }
  
  .nav-left { 
    gap: 8px;
  } 

  .brand-logo { 
    height: 36px; 
  }

  .brand-text {
    font-size: 1.15rem;
  }

  .nav-right {
    gap: 6px;
  }

  .preview-btn-label {
    display: none;
  }

  .btn-nav-outline {
    padding: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    justify-content: center;
  }

  .user-name {
    max-width: 75px;
  }

  .btn-logout-icon {
    width: 34px;
    height: 34px;
  }
}

@media (max-width: 600px) {
  .user-details {
    display: none;
  }

  .session-display {
    padding: 3px;
    background: transparent;
    border: none;
  }

  .brand-group {
    gap: 6px;
  }
}

@media (max-width: 420px) {
  .admin-navbar {
    padding: 0 0.75rem;
    height: 56px;
  }

  .brand-logo {
    height: 28px;
  }

  .brand-text {
    font-size: 1.05rem;
  }

  .btn-menu {
    padding: 4px;
  }

  .btn-nav-outline,
  .btn-logout-icon {
    width: 32px;
    height: 32px;
  }
}
</style>