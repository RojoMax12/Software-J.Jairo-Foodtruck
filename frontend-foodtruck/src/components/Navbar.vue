<template>
  <header class="navbar-wrapper">
    <nav class="dc-navbar">
      <div class="nav-left">
        <div class="brand-group" @click="goToHome">
          <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Jairo Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">J. Jairo</span>
          </div>
        </div>
      </div>

      <div class="nav-right">
        <button 
          v-if="showCheckOrderButton" 
          class="btn-nav-action btn-check-order" 
          @click="router.push('/checkorderstatus')"
          title="Consultar estado de mi pedido"
        >
          <Search :size="15" />
          <span class="btn-label">Revisa tu pedido</span>
        </button>

        <button 
          v-if="showCheckOrderButton" 
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
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Search, LogIn } from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// --- ESTADOS REACTIVOS ---
const username = ref('')
const isLoggedIn = ref(false)

const showCheckOrderButton = computed(() => route.path !== '/checkorderstatus')

const checkAuth = () => {
  const userParsed = localStorage.getItem('user')
  const token = localStorage.getItem('token')
  
  if (userParsed && token) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre_empresa || userObj.nombre_usuario || userObj.nombre || 'Usuario'
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

const goToHome = () => {
  router.push('/')
}
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

@media (max-width: 480px) {
  .brand-text {
    font-size: 1.05rem;
  }

  .brand-logo {
    height: 32px;
  }

  .btn-nav-action {
    padding: 6px 10px;
    font-size: 0.72rem;
  }
}

@media (max-width: 360px) {
  .btn-check-order .btn-label {
    display: none;
  }
  
  .btn-check-order {
    padding: 7px;
    border-radius: 50%;
  }
}
</style>