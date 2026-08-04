<template>
  <div>
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
        <button v-if="showCheckOrderButton" class="btn-login" @click="router.push('/checkorderstatus')">
          <span>Revisa tu pedido</span>
        </button>
        <button v-if="showCheckOrderButton" class="btn-login" @click="router.push('/login')">
          <span>Ingresar</span>
        </button>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()

// --- ESTADOS REACTIVOS ---
const username = ref('')
const isLoggedIn = ref(false)
const isSideMenuOpen = ref(false)

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
.dc-navbar {
  background-color: var(--DC-brown, #513119);
  height: 75px;
  padding: 0 30px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
  position: sticky;
  top: 0;
  z-index: 990; 
  font-family: var(--font-main, sans-serif);
  transition: all 0.3s ease;
  width: 100%;
  box-sizing: border-box;
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-shrink: 0; 
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
}

.brand-logo {
  height: 50px;
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
  font-size: clamp(1.3rem, 3.5vw, 2.2rem);
  font-weight: 900;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;

  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    -2px  0px 0 #000,  2px  0px 0 #000,  0px -2px 0 #000,  0px  2px 0 #000,
    4px  4px 0px rgba(0, 0, 0, 0.4);
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-login {
  background-color: #F4E1D2;
  color: #513119;
  border: none;
  padding: 8px 16px;
  border-radius: 25px;
  font-weight: 800;
  font-size: 0.82rem;
  letter-spacing: 0.3px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.btn-login:hover {
  background-color: #E28743;
  color: #ffffff;
}

/* --- MEDIAS QUERIES RESPONSIVAS --- */
@media (max-width: 768px) {
  .dc-navbar {
    height: 65px;
    padding: 0 12px;
  }

  .brand-logo {
    height: 40px;
  }

  .nav-right {
    gap: 6px;
  }

  .btn-login {
    padding: 6px 10px;
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .brand-text {
    font-size: 1.1rem;
  }

  .btn-login {
    padding: 5px 8px;
    font-size: 0.7rem;
  }
}
</style>