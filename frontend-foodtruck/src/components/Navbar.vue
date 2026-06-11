<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut, User as UserIcon, Menu } from 'lucide-vue-next'
import DistributorSideMenu from '@/components/DistributorSideMenu.vue'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const username = ref('')
const isLoggedIn = ref(false)
const isSideMenuOpen = ref(false) // Controla la barra lateral

// Carga el nombre real del usuario logueado
const checkAuth = () => {
  const userParsed = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  
  if (userParsed && token) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre_empresa || userObj.nombre_usuario || userObj.nombre || 'Distribuidor'
      isLoggedIn.value = true
    } catch (e) {
      console.error('Error parsing user session inside Navbar:', e)
      isLoggedIn.value = false
    }
  } else {
    isLoggedIn.value = false
    username.value = ''
  }
}

onMounted(() => {
  checkAuth()
})

// Cierra la sesión y redirige al catálogo
const handleLogout = () => {
  localStorage.clear()
  isLoggedIn.value = false
  username.value = ''
  isSideMenuOpen.value = false
  router.push('/')
}

const goToHome = () => {
  router.push('/')
}
</script>

<template>
  <div>
    <DistributorSideMenu 
      v-if="isLoggedIn"
      :isOpen="isSideMenuOpen" 
      @close="isSideMenuOpen = false" 
    />

    <nav class="dc-navbar">
      <div class="nav-left">
        <button 
          v-if="isLoggedIn" 
          class="btn-menu" 
          @click="isSideMenuOpen = true" 
          title="Menú lateral"
        >
          <Menu :size="24" />
        </button>
        
        <div class="brand-group" @click="goToHome">
          <img src="@/assets/logo_dicreme.png" alt="Di Creme Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">Di Creme</span>
          </div>
        </div>
      </div>

      <div class="nav-right">
        <template v-if="isLoggedIn">
          <div class="session-display">
            <div class="user-avatar">
              <UserIcon :size="20" />
            </div>
            <div class="user-details">
              <span class="user-role">Sesión Distribuidor</span>
              <span class="user-name">{{ username }}</span>
            </div>
          </div>

          <button class="btn-logout-icon" @click="handleLogout" title="Cerrar Sesión">
            <LogOut :size="20" />
          </button>
        </template>

        <template v-else>
          <button class="btn-login" @click="router.push('/login')">
            <span>INGRESAR</span>
          </button>
        </template>
      </div>
    </nav>
  </div>
</template>

<style scoped>
.dc-navbar {
  background-color: white;
  height: 80px;
  padding: 0 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 990; 
  font-family: sans-serif;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 20px;
}

.btn-menu {
  background: none;
  border: none;
  color: #322c44;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
}

.btn-menu:hover {
  background-color: #f6f4f6;
  color: #e4869f;
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
}

.brand-logo {
  height: 55px;
  object-fit: contain;
}

.brand-info {
  display: flex;
  flex-direction: column;
}

.brand-text {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1a1624;
  font-style: italic;
  line-height: 1;
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 24px;
}

.session-display {
  display: flex;
  align-items: center;
  gap: 12px;
  background-color: #f8f7f8;
  padding: 8px 16px;
  border-radius: 50px;
  border: 1px solid #eeedee;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background-color: #e4869f;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.user-details {
  display: flex;
  flex-direction: column;
}

.user-role {
  font-size: 0.7rem;
  color: #9793a0;
  font-weight: 600;
  text-transform: uppercase;
}

.user-name {
  font-size: 0.95rem;
  font-weight: 700;
  color: #322c44;
}

.btn-logout-icon {
  background-color: #3b354d;
  color: white;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-logout-icon:hover {
  background-color: #e4869f;
  transform: scale(1.05);
}

/* --- ESTILO PARA VISITANTES --- */
.btn-login {
  background-color: #3b354d;
  color: white;
  border: none;
  padding: 11px 26px;
  border-radius: 25px;
  font-weight: bold;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.btn-login:hover {
  background-color: #1a1624;
}

@media (max-width: 768px) {
  .dc-navbar {
    padding: 0 20px;
  }
  .user-details {
    display: none;
  }
}
</style>