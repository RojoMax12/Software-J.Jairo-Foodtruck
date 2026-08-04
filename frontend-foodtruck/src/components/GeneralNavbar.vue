<template>
  <nav class="admin-navbar">
    <div class="nav-left">
      <button class="btn-menu" @click="toggleSidebar" title="Menú lateral">
        <Menu :size="24" />
      </button>
      
      <div class="brand-group" @click="goToHome">
        <img src="@/assets/logo_jairo.webp" alt="J.Jairo Logo" class="brand-logo" />
        <div class="brand-info">
          <span class="brand-text">J.Junior</span>
        </div>
      </div>
    </div>

    <div class="nav-right">
      <div class="session-display">
        <div class="user-avatar">
          <UserIcon :size="18" />
        </div>
        <div class="user-details">
          <span class="user-role">Sesión {{ userRoleName }}</span>
          <span class="user-name">{{ username }}</span>
        </div>
      </div>

      <button class="btn-logout-icon" @click="handleLogout" title="Cerrar Sesión">
        <LogOut :size="18" />
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { LogOut, User as UserIcon, Menu } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()
const username = ref('Usuario')
const userRoleName = ref('Administrador') 
const roleId = ref<number | null>(null)   

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
        userRoleName.value = 'Trabajador'
      } else if (rId === 3) {
        userRoleName.value = 'Cliente'
      } else {
        userRoleName.value = 'Staff'
      }
    } catch (e) {
      console.error('Error parsing user session:', e)
    }
  }
}

onMounted(() => {
  checkAuth()
  window.addEventListener('storage', checkAuth)
})

watch(() => route.path, () => {
  checkAuth()
})

const emit = defineEmits(['toggleSidebar'])

const handleLogout = () => {
  localStorage.clear()
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
.admin-navbar {
  background-color: var(--DC-brown, #513119);
  height: 75px;
  padding: 0 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%;
  box-sizing: border-box;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-shrink: 0;
}

.btn-menu {
  background: none;
  border: none;
  color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
  flex-shrink: 0;
}

.btn-menu:hover {
  background-color: rgba(255, 255, 255, 0.15);
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: pointer;
  flex-shrink: 0;
}

.brand-logo {
  height: 48px;
  object-fit: contain;
  flex-shrink: 0;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-text {
  color: #ffffff;
  font-family: 'Arial Black', Impact, sans-serif;
  font-style: italic;
  font-size: clamp(1.2rem, 3vw, 2.2rem); 
  font-weight: 900;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;

  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    4px  4px 0px rgba(0, 0, 0, 0.4);
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-shrink: 0;
}

.session-display {
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: #F4E1D2;
  padding: 6px 14px;
  border-radius: 50px;
  border: 1px solid #513119;
}

.user-avatar {
  width: 32px;
  height: 32px;
  background-color: var(--DC-brown, #513119);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-role { font-size: 0.65rem; color: #7a410f; font-weight: 800; text-transform: uppercase; }
.user-name { font-size: 0.88rem; font-weight: 800; color: #513119; white-space: nowrap; }

.btn-logout-icon {
  background-color: #F4E1D2;
  color: var(--DC-brown, #513119);
  border: 1px solid #513119;
  width: 36px;
  height: 36px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-logout-icon:hover {
  background-color: var(--DC-orange, #e28743);
  color: white;
  transform: scale(1.05);
}

@media (max-width: 768px) {
  .admin-navbar {
    padding: 0 10px;
    height: 65px;
  }
  
  .nav-left { 
    gap: 6px;
  } 

  .brand-logo { 
    height: 36px; 
  }

  .brand-text {
    font-size: 1.1rem;
  }
  
  .session-display {
    padding: 4px 10px;
    border-radius: 20px;
  }

  .user-role {
    font-size: 0.55rem;
  }

  .user-name {
    font-size: 0.75rem;
    max-width: 90px;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .btn-logout-icon {
    width: 34px;
    height: 34px;
  }
}
</style>
