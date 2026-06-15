<template>
  <div>
    <nav class="dc-navbar">
      <div class="nav-left">
        <div class="brand-group" @click="goToHome">
          <img src="@/assets/logo_jairo.png" alt="Di Creme Logo" class="brand-logo" />
          <div class="brand-info">
            <span class="brand-text">J. Junior</span>
          </div>
        </div>
      </div>
      
      <!-- Corregido: Clase cambiada a nav-right para consistencia con tus estilos -->
      <div class="nav-right">
        <button class="btn-login" @click="router.push('/checkorderstatus')">
          <span>Revisa tu pedido aquí</span>
        </button>
        <button class="btn-login" @click="router.push('/login')">
          <span>Ingresar</span>
        </button>
      </div>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const username = ref('')
const isLoggedIn = ref(false)
const isSideMenuOpen = ref(false)

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

<style scoped>
.dc-navbar {
  background-color: #5a3614;
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
  transition: all 0.3s ease; /* Transición suave al cambiar tamaño */
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 20px;
  /* Evita que el contenedor del logo colapse en pantallas pequeñas */
  flex-shrink: 0; 
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
  
  /* CLAVE RESPONSIVA: Escala dinámicamente entre 1.5rem y 2.5rem según el ancho de pantalla */
  font-size: clamp(1.5rem, 4vw, 2.5rem);
  
  font-weight: 900;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap; /* Evita que el texto del logo salte a dos líneas */

  /* Múltiples sombras para el borde negro grueso */
  text-shadow: 
    -3px -3px 0 #000,  3px -3px 0 #000, -3px  3px 0 #000,  3px  3px 0 #000,
    -3px  0px 0 #000,  3px  0px 0 #000,  0px -3px 0 #000,  0px  3px 0 #000,
    5px  5px 0px rgba(0, 0, 0, 0.4);
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 12px; /* Reducido un poco para dar más aire en pantallas medianas */
}

/* --- ESTILO PARA VISITANTES --- */
.btn-login {
  background-color: #F4E1D2;
  color: #513119;
  border: none; /* Corregido de "border: 5px;" que era inválido */
  padding: 10px 20px;
  border-radius: 25px;
  font-weight: bold;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap; /* Evita que el texto de los botones salte de línea */
}

.btn-login:hover {
  background-color: #E28743;
  color: #ffffff;
}

/* --- MEDIAS QUERIES RESPONSIVAS --- */

/* 1. Tablets y Pantallas Medianas */
@media (max-width: 900px) {
  .dc-navbar {
    padding: 0 20px;
  }
  .brand-logo {
    height: 45px; /* Achicamos ligeramente el logo de la izquierda */
  }
}

/* 2. Celulares (Modo Vertical y pantallas chicas) */
@media (max-width: 600px) {
  .dc-navbar {
    height: auto; /* Permite que el navbar crezca si los elementos se apilan */
    padding: 15px 10px;
    flex-direction: column; /* Apila el logo arriba y los botones abajo */
    gap: 12px;
  }

  .nav-left {
    width: 100%;
    justify-content: center; /* Centra el logo en celulares */
  }

  .nav-right {
    width: 100%;
    justify-content: space-evenly; /* Distribuye equitativamente ambos botones */
    gap: 8px;
  }

  .btn-login {
    flex: 1; /* Hace que ambos botones ocupen el mismo ancho proporcional en el móvil */
    padding: 8px 12px;
    font-size: 0.8rem;
    text-align: center;
  }
}
</style>