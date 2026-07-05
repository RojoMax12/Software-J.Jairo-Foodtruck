<template>
  <nav class="admin-navbar">
    <div class="nav-left">
      <button class="btn-menu" @click="toggleSidebar" title="Menú lateral">
        <Menu :size="24" />
      </button>
      
      <div class="brand-group" @click="goToHome">
        <img src="@/assets/logo_jairo.webp" alt="J.jairo logo" class="brand-logo" />
        <div class="brand-info">
          <span class="brand-text">J.Junior</span>
        </div>
      </div>
    </div>

    <div class="nav-right">
      <div class="session-display">
        <div class="user-avatar">
          <UserIcon :size="20" />
        </div>
        <div class="user-details">
          <span class="user-role">Sesión {{ userRoleName }}</span>
          <span class="user-name">{{ username }}</span>
        </div>
      </div>

      <button class="btn-logout-icon" @click="handleLogout" title="Cerrar Sesión">
        <LogOut :size="20" />
      </button>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { LogOut, User as UserIcon, Menu } from 'lucide-vue-next'

const router = useRouter()
const username = ref('')
const userRoleName = ref('Administrador') // Variable para el texto del rol
const roleId = ref<number | null>(null)   // Para guardar el ID y enrutar

const checkAuth = () => {
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      username.value = userObj.nombre_usuario || userObj.nombre || 'Usuario'
      roleId.value = userObj.id_rol
      
      // Asignamos el nombre del rol visualmente según el ID
      if (userObj.id_rol == 1) {
        userRoleName.value = 'Administrador'
      } else if (userObj.id_rol == 2) {
        userRoleName.value = 'Trabajador' // O "Cajero", según prefieras
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
})

const emit = defineEmits(['toggleSidebar'])

const handleLogout = () => {
  localStorage.clear()
  router.push('/')
}

// Enrutamiento dinámico al presionar el logo
const goToHome = () => {
    router.push('/general-home')
}

const toggleSidebar = () => {
  emit('toggleSidebar')
}
</script>

<style scoped>
.admin-navbar {
  background-color: var(--DC-brown);
  height: 80px;
  padding: 0 40px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  position: sticky;
  top: 0;
  z-index: 1000;
  width: 100%; /* Asegura que no sobrepase el ancho */
  box-sizing: border-box; /* Previene el desbordamiento horizontal */
}

.nav-left {
  display: flex;
  align-items: center;
  gap: 20px;
  overflow: hidden; /* Evita que el logo estire el contenedor */
}

.btn-menu {
  background: none;
  border: none;
  color: var(--button-color);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 8px;
  border-radius: 8px;
  transition: background-color 0.2s ease;
  flex-shrink: 0; /* Evita que el botón se aplaste */
}

.btn-menu:hover {
  background-color: var(--button-hover);
  color: var(--DC-brown);
}

.brand-group {
  display: flex;
  align-items: center;
  gap: 15px;
  cursor: pointer;
  flex-shrink: 1; /* Permite que se comprima si es necesario */
}

.brand-logo {
  height: 55px;
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
  font-size: clamp(1.2rem, 3vw, 2.5rem); 
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
  white-space: nowrap;
  
  padding-right: 8px; 

  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    5px  5px 0px rgba(0, 0, 0, 0.4);
}

.nav-right {
  display: flex;
  align-items: center;
  gap: 24px;
  flex-shrink: 0;
}

.session-display {
  display: flex;
  align-items: center;
  gap: 12px;
  background-color: var(--button-color);
  padding: 8px 16px;
  border-radius: 50px;
  border: 1px solid #000000;
}

.user-avatar {
  width: 36px;
  height: 36px;
  background-color: black;
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

.user-role { font-size: 0.7rem; color: var(--DC-brown); font-weight: 600; text-transform: uppercase; }
.user-name { font-size: 0.95rem; font-weight: 700; color: #322c44; white-space: nowrap; }

.btn-logout-icon {
  background-color: var(--button-color);
  color: var(--DC-brown);
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.btn-logout-icon:hover {
  background-color: var(--button-hover);
  transform: scale(1.05);
}

@media (max-width: 768px) {
  .admin-navbar {
    padding: 0 10px;
    height: 70px;
  }
  
  .nav-left { 
    gap: 6px; /* Bajamos de 8px a 6px */
    max-width: 60%; /* Evita que la izquierda intente tomar toda la pantalla */
  } 
  
  .brand-group { 
    gap: 4px; /* Bajamos de 6px a 4px */
  } 
  
  .brand-logo { 
    height: 35px; 
  }
  
  /* Ocultamos el contenedor de la info de texto de la marca si la pantalla es diminuta */
  .brand-info {
    min-width: 0; 
  }
  
  .nav-right { 
    gap: 6px; 
  }
  
  .user-details { display: none; } 
  
  .session-display {
    padding: 6px;
    border-radius: 50%;
    width: 40px;  
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    border: 1px solid #000;
  }

  .user-avatar {
    width: 100%;
    height: 100%;
    margin: 0;
  }

  /* 🌟 NUEVA PROTECCIÓN EXTRA PARA PANTALLAS MENORES A 360px */
  @media (max-width: 360px) {
    .brand-logo {
      display: none; /* Escondemos el logo de imagen para priorizar la lectura de "J.Junior" */
    }
  }
}
</style>
