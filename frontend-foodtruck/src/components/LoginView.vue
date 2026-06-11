<script setup lang="ts">
import { ref } from 'vue'
import { User, Eye, EyeOff, ArrowLeft } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import { authService } from '../services/authService'

const router = useRouter()
const username = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const goBack = () => {
  router.back()
}

const handleLogin = async () => {
  if (!username.value || !password.value) {
    errorMessage.value = 'Por favor, ingresa tu correo y contraseña.'
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

<template>
  <div class="login-container">
    <div class="login-wrapper">
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="#e4869f" />
        <span>Volver</span>
      </div>

      <div class="login-card">
        <div class="logo-section">
          <img src="../assets/logo_dicreme.png" alt="DiCreme Logo" class="logo" />
        </div>

        <div class="divider"></div>

        <div class="form-section">
          <!-- Mensaje de Error -->
          <div v-if="errorMessage" class="error-banner">
            {{ errorMessage }}
          </div>

          <div class="input-group">
            <input 
              v-model="username" 
              type="email" 
              placeholder="Correo electrónico" 
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

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #eeedee;
  font-family: sans-serif;
}

.login-wrapper {
  position: relative;
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.back-button {
  position: absolute;
  left: -6.5rem;
  top: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  color: #e4869f;
  font-weight: bold;
  transition: all 0.2s ease;
}

.back-button:hover {
  transform: translateX(-5px);
}

.back-button span {
  margin-top: 0.5rem;
}

.login-card {
  background-color: white;
  padding: 2rem;
  border-radius: 1.5rem;
  width: 100%;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-section {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo {
  max-width: 200px;
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: #e4869f;
  margin-bottom: 2rem;
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
  background-color: #fff5f5;
  border: 1px solid #fa5252;
  border-radius: 0.75rem;
  color: #fa5252;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  font-size: 1rem;
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s;
}

.custom-input:focus {
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(228, 134, 159, 0.2);
}

.custom-input::placeholder {
  color: #9793a0;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
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
  padding: 0.75rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: bold;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active:not(:disabled) {
  transform: translateY(0);
  filter: brightness(0.8);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(0.5);
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}

.btn-secondary {
  background-color: #9793a0;
  color: white;
}

.forgot-password {
  color: #322c44;
  text-decoration: none;
  font-size: 0.9rem;
  margin: 0.5rem 0;
}

.forgot-password:hover {
  text-decoration: underline;
}
</style>
