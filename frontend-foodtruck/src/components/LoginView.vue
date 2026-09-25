<template>
  <div class="login-container">
    <div class="login-wrapper">
      <div class="login-card">
        <!-- BOTÓN VOLVER -->
        <button type="button" class="back-button" @click="goBack" title="Volver atrás">
          <ArrowLeft :size="18" />
          <span>Volver</span>
        </button>

        <!-- SECCIÓN LOGO & BRANDING -->
        <div class="logo-section">
          <div class="auth-badge">
            <Lock :size="13" />
            <span>Acceso al Sistema</span>
          </div>
          <img src="../assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <h2 class="auth-title">Bienvenido</h2>
          <p class="auth-subtitle">Ingresa tus credenciales para continuar</p>
        </div>

        <div class="divider"></div>

        <!-- FORMULARIO DE ACCESO -->
        <form class="form-section" @submit.prevent="handleLogin">
          <!-- BANNER DE ERROR -->
          <Transition name="fade">
            <div v-if="errorMessage" class="error-banner">
              <AlertTriangle :size="18" class="error-icon" />
              <span>{{ errorMessage }}</span>
            </div>
          </Transition>

          <!-- CAMPO: CORREO / USUARIO -->
          <div class="form-field">
            <label class="field-label" for="login-email">Correo o Usuario</label>
            <div class="input-group">
              <input 
                id="login-email"
                v-model="correo" 
                type="text"
                placeholder="ejemplo@correo.cl" 
                class="custom-input"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="username"
              />
              <User class="input-icon" :size="18" />
            </div>
          </div>

          <!-- CAMPO: CONTRASEÑA -->
          <div class="form-field">
            <div class="field-label-row">
              <label class="field-label" for="login-password">Contraseña</label>
              <router-link to="/forgot-password" class="forgot-link">
                ¿Olvidaste tu contraseña?
              </router-link>
            </div>
            <div class="input-group">
              <input 
                id="login-password"
                v-model="password" 
                :type="showPassword ? 'text' : 'password'" 
                placeholder="Ingresa tu contraseña" 
                class="custom-input with-toggle"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="current-password"
              />
              <button 
                type="button" 
                class="icon-toggle-btn" 
                @click="showPassword = !showPassword"
                :title="showPassword ? 'Ocultar contraseña' : 'Ver contraseña'"
                tabindex="-1"
              >
                <Eye v-if="!showPassword" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
          </div>

          <!-- BOTÓN PRINCIPAL: INGRESAR -->
          <button 
            type="submit" 
            class="btn-primary-auth"
            :disabled="isLoading || retrySecondsLeft > 0"
          >
            <RefreshCw v-if="isLoading" :size="18" class="spinning" />
            <LogIn v-else-if="retrySecondsLeft === 0" :size="18" />
            <span>
              {{ retrySecondsLeft > 0 ? `Espera (${retrySecondsLeft}s)` : (isLoading ? 'Ingresando...' : 'Iniciar Sesión') }}
            </span>
          </button>

          <!-- SECCIÓN DE REGISTRO -->
          <div class="register-prompt">
            <span>¿Aún no tienes una cuenta?</span>
            <router-link to="/register" class="btn-secondary-auth">
              <UserPlus :size="16" />
              <span>Crear Cuenta Nueva</span>
            </router-link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { 
  User, Eye, EyeOff, ArrowLeft, Lock, AlertTriangle, 
  LogIn, UserPlus, RefreshCw 
} from 'lucide-vue-next'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/authService'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const route = useRoute()
const { notify } = useNotification()

const correo = ref('')
const password = ref('')
const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')
const retrySecondsLeft = ref(0)
let countdownTimer: any = null

const startCountdown = (seconds: number) => {
  if (countdownTimer) clearInterval(countdownTimer)
  retrySecondsLeft.value = seconds
  countdownTimer = setInterval(() => {
    retrySecondsLeft.value--
    if (retrySecondsLeft.value <= 0) {
      clearInterval(countdownTimer)
      countdownTimer = null
      retrySecondsLeft.value = 0
      errorMessage.value = ''
    }
  }, 1000)
}

onMounted(() => {
  if (route.query.expired === '1') {
    notify('Tu sesión ha expirado. Por favor, ingresa nuevamente.', 'warning')
  }
})

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/')
  }
}

const handleLogin = async () => {
  if (retrySecondsLeft.value > 0 || isLoading.value) return

  if (!correo.value.trim() || !password.value.trim()) {
    errorMessage.value = 'Por favor, ingresa tu correo/usuario y contraseña.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    const data = await authService.login(correo.value.trim().toLowerCase(), password.value.trim())

    const token = data.access_token || data.token
    const user = data.user || {}

    localStorage.setItem('token', token)
    localStorage.setItem('user', JSON.stringify(user))

    notify(`¡Bienvenido/a, ${user.nombre || 'Usuario'}!`, 'success')

    const rolId = Number(user.id_rol)
    if (rolId === 1 || rolId === 3) {
      router.push('/general-home')
    } else {
      router.push('/')
    }
  } catch (error: any) {
    console.error('Login error:', error)
    if (error.response?.status === 429) {
      const waitSec = Number(error.response?.data?.retry_after || error.response?.headers?.['retry-after'] || 60)
      startCountdown(waitSec)
      errorMessage.value = error.response?.data?.message || `Demasiados intentos. Espera ${waitSec} segundos para continuar.`
    } else {
      errorMessage.value = error.response?.data?.error 
        || error.response?.data?.message 
        || 'Credenciales incorrectas o problema de conexión con el servidor.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.login-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray, #f8f6f3);
  padding: 1.5rem 1rem;
}

.login-wrapper {
  width: 100%;
  max-width: 440px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.login-card {
  position: relative;
  background-color: #ffffff;
  padding: 2.5rem 2rem 2rem 2rem;
  border-radius: 22px;
  width: 100%;
  box-shadow: 0 12px 36px rgba(26, 14, 5, 0.06);
  border: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
}

/* BOTÓN VOLVER */
.back-button {
  position: absolute;
  left: 1.25rem;
  top: 1.25rem;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: transparent;
  border: none;
  cursor: pointer;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  font-size: 0.85rem;
  transition: all 0.2s ease;
  padding: 0.35rem 0.6rem;
  border-radius: 8px;
}

.back-button:hover {
  color: var(--DC-brown, #513119);
  background: var(--DC-bg-gray, #f8f6f3);
  transform: translateX(-3px);
}

/* LOGO & BRAND */
.logo-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  margin-top: 0.5rem;
  text-align: center;
}

.auth-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0.3rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  font-size: 0.74rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  margin-bottom: 0.85rem;
}

.brand-logo {
  height: 58px;
  width: auto;
  object-fit: contain;
  margin-bottom: 0.65rem;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.1));
}

.auth-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.6rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
}

.auth-subtitle {
  margin: 0;
  font-size: 0.88rem;
  color: var(--DC-text-gray, #7c7468);
}

.divider {
  width: 100%;
  height: 1px;
  background-color: rgba(81, 49, 25, 0.08);
  margin: 1.5rem 0;
}

/* FORMULARIO */
.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  width: 100%;
}

.field-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.field-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.forgot-link {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-orange, #e28743);
  text-decoration: none;
  transition: color 0.15s ease;
}

.forgot-link:hover {
  color: var(--DC-brown, #513119);
  text-decoration: underline;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 2.6rem 0.75rem 1rem;
  background-color: var(--DC-bg-gray, #f8f6f3);
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  border-radius: 12px;
  font-size: 0.92rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.custom-input:focus {
  background-color: #ffffff;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.16);
}

.custom-input::placeholder {
  color: #a89f95;
  font-weight: 500;
}

.custom-input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.input-icon {
  position: absolute;
  right: 0.9rem;
  color: var(--DC-brown, #513119);
  pointer-events: none;
}

.icon-toggle-btn {
  position: absolute;
  right: 0.4rem;
  height: 32px;
  width: 32px;
  background: transparent;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--DC-text-gray, #7c7468);
  border-radius: 8px;
  transition: color 0.15s ease, background-color 0.15s ease;
}

.icon-toggle-btn:hover {
  color: var(--DC-brown, #513119);
  background: rgba(81, 49, 25, 0.05);
}

/* ALERTA DE ERROR */
.error-banner {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #fff5f5;
  border: 1px solid #fed7d7;
  border-radius: 12px;
  color: #c53030;
  font-size: 0.84rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.55rem;
  text-align: left;
  line-height: 1.4;
  overflow-wrap: anywhere;
}

.error-icon {
  flex-shrink: 0;
}

/* BOTÓN PRINCIPAL */
.btn-primary-auth {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  font-weight: 800;
  font-size: 0.95rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
  margin-top: 0.25rem;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
}

.btn-primary-auth:hover:not(:disabled) {
  background-color: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(81, 49, 25, 0.25);
}

.btn-primary-auth:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  box-shadow: none;
}

/* SECCIÓN REGISTRO */
.register-prompt {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.65rem;
  margin-top: 0.5rem;
  padding-top: 1.25rem;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
  font-size: 0.84rem;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 600;
}

.btn-secondary-auth {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1.5px solid rgba(81, 49, 25, 0.15);
  background-color: #ffffff;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  font-size: 0.88rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.45rem;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-secondary-auth:hover {
  background-color: var(--DC-bg-gray, #f8f6f3);
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 480px) {
  .login-card {
    padding: 2.25rem 1.25rem 1.75rem 1.25rem;
    border-radius: 18px;
  }

  .auth-title {
    font-size: 1.45rem;
  }

  .brand-logo {
    height: 48px;
  }
}
</style>