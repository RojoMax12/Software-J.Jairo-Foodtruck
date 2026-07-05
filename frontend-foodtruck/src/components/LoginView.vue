<template>
  <div class="login-container">
    <div class="login-wrapper">

      <div class="login-card">
        <div class="back-button" @click="goBack">
          <ArrowLeft :size="24" color="var(--DC-orange)" />
          <span>Volver</span>
        </div>
        <div class="logo-section">
          <img src="../assets/logo_jairo.webp" alt="J.Jairo Logo" class="logo" />
        </div>

        <div class="divider"></div>

        <div class="form-section">

          <div v-if="errorMessage" class="error-banner">
            {{ errorMessage }}
          </div>

          <div class="input-group">
            <input 
              v-model="correo" 
              placeholder="Correo" 
              class="custom-input"
              :disabled="isLoading"
            />
            <User class="input-icon" :size="20" color="#322c44" />
          </div>

          <div class="input-group">
            <input 
              v-model="password" 
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Contraseña" 
              class="custom-input"
              :disabled="isLoading"
              @keyup.enter="handleLogin"
            />
            <div class="icon-wrapper" @click="showPassword = !showPassword">
              <Eye v-if="!showPassword" class="input-icon clickable" :size="20" color="#322c44" />
              <EyeOff v-else class="input-icon clickable" :size="20" color="#322c44" />
            </div>
          </div>

          <button 
            @click="handleLogin" 
            class="btn btn-primary"
            :disabled="isLoading"
          >
            {{ isLoading ? 'INGRESANDO...' : 'INGRESAR' }}
          </button>

          <router-link to="/forgot-password" class="forgot-password">
            ¿Olvidaste tu contraseña?
          </router-link>

          <router-link to="/register" style="width: 100%;">
            <button class="btn btn-secondary" :disabled="isLoading">CREA TU CUENTA</button>
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>



<script setup lang="ts">
import { ref } from 'vue'
import { User, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'

const router = useRouter()
const correo = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const goBack = () => {
  router.back()
}

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'Por favor, ingresa tu usuario y contraseña.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await authService.login(username.value, password.value)

    // Guardamos el token y la info del usuario
    localStorage.setItem('token', data.access_token || data.token)
    localStorage.setItem('user', JSON.stringify(data.user))

    if(data.user.id_rol == 1){
      router.push('/admin')
    }
    else if(data.user.id_rol == 2){

    }
    else{
      router.push('/')
    }
    
  } catch (error: any) {
    console.error('Login error:', error)
    errorMessage.value = error.response?.data?.error || error.response?.data?.message || 'Credenciales incorrectas o error de conexión.'
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray); /* Corregido: antes tenías una variable de fuente aquí */
  font-family: var(--font-main);
  padding: 20px; /* 🌟 CLAVE: Esto evita que la tarjeta choque con los bordes en el celular */
  box-sizing: border-box;
}

.login-wrapper {
  position: relative;
  width: 100%;
  max-width: 420px; /* Ancho máximo controlado para PC */
  display: flex;
  flex-direction: column;
  align-items: center;
}

.login-card {
  position: relative;
  background-color: white;
  padding: 3.5rem 2rem 2.5rem 2rem;
  border-radius: 1.5rem;
  width: 100%;
  box-sizing: border-box;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid #eeedee;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.back-button {
  position: absolute;
  left: 20px;
  top: 20px;
  display: flex;
  align-items: center;
  gap: 5px;
  cursor: pointer;
  color: var(--DC-orange);
  font-weight: 800;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  z-index: 10;
}

.back-button:hover {
  transform: translateX(-5px);
  color: var(--DC-brown);
}

.logo-section {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo {
  max-width: 180px; /* Un poco más contenido para que no desborde */
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: var(--DC-brown);
  margin-bottom: 2rem;
  opacity: 0.2;
}

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: center;
}

.error-banner {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #fff0f3;
  border: 2px solid var(--DC-pink);
  border-radius: 0.75rem;
  color: var(--DC-pink);
  font-size: 0.9rem;
  font-weight: 800;
  text-align: center;
  box-sizing: border-box;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.custom-input {
  width: 100%;
  padding: 0.9rem 2.5rem 0.9rem 1.2rem;
  background-color: #fcfbf9;
  border: 2px solid #eeedee;
  border-radius: 0.75rem;
  font-size: 1rem;
  font-weight: 600;
  color: var(--DC-gray);
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s;
}

.custom-input:focus {
  background-color: #fff;
  border-color: var(--DC-orange);
  /* Sombra de enfoque naranja, alineada a tu marca */
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.2); 
}

.custom-input::placeholder {
  color: #9793a0;
  font-weight: 500;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
  color: var(--DC-brown);
}

.icon-wrapper {
  position: absolute;
  right: 0;
  height: 100%;
  width: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.clickable {
  pointer-events: auto;
}

.btn {
  width: 100%;
  padding: 0.9rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: 900;
  cursor: pointer;
  font-size: 1.05rem;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.btn-primary {
  background-color: var(--DC-orange);
  color: white;
  box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3);
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  background-color: var(--DC-brown);
  box-shadow: 0 6px 20px rgba(81, 49, 25, 0.3);
}

.btn:active:not(:disabled) {
  transform: translateY(0);
}

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  background-color: #ccc;
  box-shadow: none;
  color: #666;
}
</style>