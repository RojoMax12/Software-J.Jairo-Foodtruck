<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Mail, ArrowLeft, KeyRound, CheckCircle2, 
  AlertTriangle, RefreshCw, Send, LogIn 
} from 'lucide-vue-next'
import api from '@/services/api'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

const email = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const emailSent = ref(false)

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/login')
  }
}

const handleResetPassword = async () => {
  errorMessage.value = ''
  const correo = email.value.trim()

  if (!correo) {
    errorMessage.value = 'Por favor, ingresa tu correo electrónico.'
    return
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(correo)) {
    errorMessage.value = 'Ingresa un correo electrónico válido.'
    return
  }

  isLoading.value = true

  try {
    await api.post('/auth/forgot-password', {
      correo: correo
    })

    emailSent.value = true
    notify(
      'Si el correo está registrado, recibirás las instrucciones de recuperación.',
      'success'
    )
  } catch (error: any) {
    console.error('Forgot password error:', error)
    errorMessage.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'No fue posible enviar el correo de recuperación. Intenta nuevamente.'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="forgot-container">
    <div class="forgot-wrapper">
      <div class="forgot-card">
        <!-- BOTÓN VOLVER -->
        <button type="button" class="back-button" @click="goBack" title="Volver atrás">
          <ArrowLeft :size="18" />
          <span>Volver</span>
        </button>

        <!-- HEADER CON LOGO Y BADGE -->
        <div class="logo-section">
          <div class="auth-badge">
            <KeyRound :size="13" />
            <span>Seguridad de Cuenta</span>
          </div>
          <img src="../assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <h2 class="auth-title">Recupera tu Acceso</h2>
          <p class="auth-subtitle">Te ayudaremos a restablecer tu contraseña</p>
        </div>

        <div class="divider"></div>

        <!-- FORMULARIO DE ENVÍO -->
        <form v-if="!emailSent" class="form-section" @submit.prevent="handleResetPassword">
          <p class="form-instruction">
            Ingresa el correo electrónico asociado a tu cuenta para recibir el enlace de recuperación.
          </p>

          <!-- BANNER DE ERROR -->
          <Transition name="fade">
            <div v-if="errorMessage" class="error-banner">
              <AlertTriangle :size="18" class="error-icon" />
              <span>{{ errorMessage }}</span>
            </div>
          </Transition>

          <div class="form-field">
            <label class="field-label" for="reset-email">Correo Electrónico</label>
            <div class="input-group">
              <input
                id="reset-email"
                v-model="email"
                type="email"
                placeholder="ejemplo@correo.cl"
                class="custom-input"
                :disabled="isLoading"
                autocomplete="email"
              />
              <Mail class="input-icon" :size="18" />
            </div>
          </div>

          <button
            type="submit"
            class="btn-primary-auth"
            :disabled="isLoading"
          >
            <RefreshCw v-if="isLoading" :size="18" class="spinning" />
            <Send v-else :size="16" />
            <span>{{ isLoading ? 'Enviando instrucciones...' : 'Enviar Instrucciones' }}</span>
          </button>
        </form>

        <!-- PANTALLA DE CONFIRMACIÓN DE ENVÍO -->
        <div v-else class="success-section">
          <div class="success-icon-box">
            <CheckCircle2 :size="32" />
          </div>

          <div class="success-text-box">
            <h3 class="success-title">¡Revisa tu bandeja de entrada!</h3>
            <p class="success-desc">
              Si el correo <strong>{{ email }}</strong> coincide con una cuenta activa, recibirás en breve un enlace para crear una nueva contraseña.
            </p>
            <span class="spam-hint">Si no lo encuentras en unos minutos, revisa tu carpeta de spam o correo no deseado.</span>
          </div>

          <button
            type="button"
            class="btn-primary-auth"
            @click="router.push('/login')"
          >
            <LogIn :size="16" />
            <span>Volver a Iniciar Sesión</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.forgot-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray, #f8f6f3);
  padding: 1.5rem 1rem;
}

.forgot-wrapper {
  width: 100%;
  max-width: 440px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.forgot-card {
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
  height: 56px;
  width: auto;
  object-fit: contain;
  margin-bottom: 0.65rem;
  filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.1));
}

.auth-title {
  margin: 0 0 0.25rem 0;
  font-size: 1.55rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
}

.auth-subtitle {
  margin: 0;
  font-size: 0.86rem;
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

.form-instruction {
  margin: 0;
  font-size: 0.86rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.45;
  text-align: center;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  width: 100%;
}

.field-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
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

/* BOTÓN DE ACCIÓN */
.btn-primary-auth {
  width: 100%;
  padding: 0.85rem 1.25rem;
  border: none;
  border-radius: 12px;
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  font-weight: 800;
  font-size: 0.92rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
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

/* CONFIRMACIÓN EXITOSA */
.success-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.25rem;
}

.success-icon-box {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: rgba(34, 197, 94, 0.12);
  color: #16a34a;
  display: flex;
  align-items: center;
  justify-content: center;
}

.success-text-box {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.success-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
}

.success-desc {
  margin: 0;
  font-size: 0.88rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.45;
}

.success-desc strong {
  color: var(--DC-gray, #2c2724);
}

.spam-hint {
  margin-top: 0.25rem;
  font-size: 0.78rem;
  color: #94a3b8;
  font-style: italic;
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
  .forgot-card {
    padding: 2.25rem 1.25rem 1.75rem 1.25rem;
    border-radius: 18px;
  }

  .auth-title {
    font-size: 1.4rem;
  }

  .brand-logo {
    height: 48px;
  }
}
</style>