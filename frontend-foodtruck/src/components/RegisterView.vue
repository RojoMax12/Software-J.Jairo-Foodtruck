<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Mail, Phone, ArrowLeft, 
  Eye, EyeOff, Check, User, Lock, 
  UserPlus, AlertTriangle, RefreshCw, LogIn
} from 'lucide-vue-next'
import { authService } from '../services/authService'
import SuccessAccountModal from './SuccessAccountModal.vue'
import TermsAndPrivacyModal from './TermsAndPrivacyModal.vue'

const router = useRouter()

const form = ref({
  nombre: '',
  correo_electronico: '',
  telefono: '',
  contrasena: '',
  confirmPassword: ''
})

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

const showSuccessModal = ref(false)
const acceptTerms = ref(false)
const showTermsModal = ref(false)

// Requisitos de seguridad de contraseña (Ley N° 21.719)
const hasMinLength = computed(() => form.value.contrasena.length >= 8)
const hasUppercase = computed(() => /[A-Z]/.test(form.value.contrasena))
const hasNumber = computed(() => /\d/.test(form.value.contrasena))
const hasSpecialChar = computed(() => /[@$!%*?&_#\-+=~`^().]/.test(form.value.contrasena))
const isPasswordValid = computed(() => hasMinLength.value && hasUppercase.value && hasNumber.value && hasSpecialChar.value)

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const goBack = () => {
  if (window.history.length > 1) {
    router.back()
  } else {
    router.push('/login')
  }
}

const handleRegister = async () => {
  if (retrySecondsLeft.value > 0 || isLoading.value) return

  if (!form.value.nombre.trim() || !form.value.correo_electronico.trim() || !form.value.contrasena.trim()) {
    errorMessage.value = 'Por favor, completa todos los campos obligatorios.'
    return
  }

  if (form.value.contrasena !== form.value.confirmPassword) {
    errorMessage.value = 'Las contraseñas no coinciden.'
    return
  }

  if (!isPasswordValid.value) {
    errorMessage.value = 'La contraseña no cumple con los requisitos de seguridad requeridos.'
    return
  }

  if (!acceptTerms.value) {
    errorMessage.value = 'Debes aceptar los Términos, Condiciones y Política de Privacidad para continuar.'
    return
  }

  errorMessage.value = ''
  isLoading.value = true

  try {
    const res = await authService.registerDistribuidor({
      nombre: form.value.nombre.trim(),
      correo_electronico: form.value.correo_electronico.trim().toLowerCase(),
      telefono: form.value.telefono.trim(),
      contrasena: form.value.contrasena
    })

    const data = res.data || res
    if (data?.token || data?.access_token) {
      localStorage.setItem('token', data.token || data.access_token)
      if (data.user) {
        localStorage.setItem('user', JSON.stringify({
          ...data.user,
          telefono: form.value.telefono.trim()
        }))
      }
    }

    showSuccessModal.value = true
  } catch (error: any) {
    if (error.response?.status === 429) {
      const waitSec = Number(error.response?.data?.retry_after || error.response?.headers?.['retry-after'] || 60)
      startCountdown(waitSec)
      errorMessage.value = error.response?.data?.message || `Demasiados intentos. Espera ${waitSec} segundos para continuar.`
    } else {
      errorMessage.value = error.response?.data?.message || error.response?.data?.error || 'No fue posible crear la cuenta. Intenta nuevamente.'
    }
  } finally {
    isLoading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="register-container">
    <div class="register-wrapper">
      <div class="register-card">
        <!-- BOTÓN VOLVER -->
        <button type="button" class="back-button" @click="goBack" v-if="!showSuccessModal" title="Volver atrás">
          <ArrowLeft :size="18" />
          <span>Volver</span>
        </button>

        <!-- SECCIÓN LOGO & BRANDING -->
        <div class="logo-section">
          <div class="auth-badge">
            <UserPlus :size="13" />
            <span>Registro de Cliente</span>
          </div>
          <img src="../assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="brand-logo" />
          <h2 class="auth-title">Crea tu Cuenta</h2>
          <p class="auth-subtitle">Regístrate para pedir más rápido y seguir tus compras</p>
        </div>

        <div class="divider"></div>

        <!-- FORMULARIO DE REGISTRO -->
        <form class="form-section" @submit.prevent="handleRegister">
          <!-- BANNER DE ERROR -->
          <Transition name="fade">
            <div v-if="errorMessage" class="error-banner">
              <AlertTriangle :size="18" class="error-icon" />
              <span>{{ errorMessage }}</span>
            </div>
          </Transition>

          <!-- NOMBRE COMPLETO -->
          <div class="form-field">
            <label class="field-label" for="reg-name">Nombre y Apellido <span class="required">*</span></label>
            <div class="input-group">
              <input
                id="reg-name"
                v-model="form.nombre"
                type="text"
                placeholder="Ej: Juan Pérez"
                class="custom-input"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="name"
                required
              />
              <User class="input-icon" :size="18" />
            </div>
          </div>

          <!-- CORREO ELECTRÓNICO -->
          <div class="form-field">
            <label class="field-label" for="reg-email">Correo Electrónico <span class="required">*</span></label>
            <div class="input-group">
              <input
                id="reg-email"
                v-model="form.correo_electronico"
                type="email"
                placeholder="ejemplo@correo.cl"
                class="custom-input"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="email"
                required
              />
              <Mail class="input-icon" :size="18" />
            </div>
          </div>

          <!-- TELÉFONO DE CONTACTO -->
          <div class="form-field">
            <label class="field-label" for="reg-phone">Teléfono Móvil (WhatsApp)</label>
            <div class="phone-input-group">
              <span class="phone-prefix">+56</span>
              <div class="input-group">
                <input
                  id="reg-phone"
                  v-model="form.telefono"
                  type="tel"
                  placeholder="9 1234 5678"
                  class="custom-input"
                  maxlength="9"
                  :disabled="isLoading || retrySecondsLeft > 0"
                  autocomplete="tel"
                />
                <Phone class="input-icon" :size="18" />
              </div>
            </div>
          </div>

          <!-- CONTRASEÑA -->
          <div class="form-field">
            <label class="field-label" for="reg-password">Contraseña <span class="required">*</span></label>
            <div class="input-group">
              <input
                id="reg-password"
                v-model="form.contrasena"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Crea una contraseña segura"
                class="custom-input with-toggle"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="new-password"
                required
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

          <!-- REQUISITOS DE CONTRASEÑA LEY N° 21.719 -->
          <div v-if="form.contrasena" class="password-requirements-card">
            <span class="requirements-title">
              <Lock :size="12" />
              Requisitos de seguridad (Ley N° 21.719):
            </span>
            <div class="req-grid">
              <div class="req-item" :class="{ met: hasMinLength }">
                <Check v-if="hasMinLength" :size="13" class="req-icon" />
                <span v-else class="req-dot"></span>
                <span>Mínimo 8 caracteres</span>
              </div>
              <div class="req-item" :class="{ met: hasUppercase }">
                <Check v-if="hasUppercase" :size="13" class="req-icon" />
                <span v-else class="req-dot"></span>
                <span>Al menos 1 letra mayúscula (A-Z)</span>
              </div>
              <div class="req-item" :class="{ met: hasNumber }">
                <Check v-if="hasNumber" :size="13" class="req-icon" />
                <span v-else class="req-dot"></span>
                <span>Al menos 1 número (0-9)</span>
              </div>
              <div class="req-item" :class="{ met: hasSpecialChar }">
                <Check v-if="hasSpecialChar" :size="13" class="req-icon" />
                <span v-else class="req-dot"></span>
                <span>Al menos 1 símbolo especial (@, $, !, %, *, #)</span>
              </div>
            </div>
          </div>

          <!-- CONFIRMAR CONTRASEÑA -->
          <div class="form-field">
            <label class="field-label" for="reg-confirm">Confirmar Contraseña <span class="required">*</span></label>
            <div class="input-group">
              <input
                id="reg-confirm"
                v-model="form.confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Repite tu contraseña"
                class="custom-input with-toggle"
                :disabled="isLoading || retrySecondsLeft > 0"
                autocomplete="new-password"
                required
              />
              <button 
                type="button" 
                class="icon-toggle-btn" 
                @click="showConfirmPassword = !showConfirmPassword"
                :title="showConfirmPassword ? 'Ocultar' : 'Ver'"
                tabindex="-1"
              >
                <Eye v-if="!showConfirmPassword" :size="18" />
                <EyeOff v-else :size="18" />
              </button>
            </div>
          </div>

          <!-- CHECKBOX TÉRMINOS Y PRIVACIDAD -->
          <div class="terms-container">
            <label class="terms-label">
              <input 
                type="checkbox" 
                v-model="acceptTerms" 
                class="modern-checkbox" 
                :disabled="isLoading" 
              />
              <span class="terms-text">
                He leído y acepto los 
                <button type="button" class="terms-link-btn" @click="showTermsModal = true">
                  Términos, Condiciones y Política de Privacidad (Ley N° 21.719)
                </button>
              </span>
            </label>
          </div>

          <!-- BOTÓN REGISTRAR -->
          <button
            type="submit"
            class="btn-primary-auth"
            :disabled="isLoading || !acceptTerms || retrySecondsLeft > 0"
          >
            <RefreshCw v-if="isLoading" :size="18" class="spinning" />
            <UserPlus v-else-if="retrySecondsLeft === 0" :size="18" />
            <span>
              {{ retrySecondsLeft > 0 ? `Espera (${retrySecondsLeft}s)` : (isLoading ? 'Creando cuenta...' : 'Crear Cuenta') }}
            </span>
          </button>

          <!-- ENLACE AL LOGIN -->
          <div class="login-prompt">
            <span>¿Ya tienes una cuenta registrada?</span>
            <router-link to="/login" class="btn-secondary-auth">
              <LogIn :size="16" />
              <span>Iniciar Sesión</span>
            </router-link>
          </div>
        </form>
      </div>
    </div>

    <SuccessAccountModal
      v-if="showSuccessModal"
      @accept="goToLogin"
    />

    <TermsAndPrivacyModal 
      :isOpen="showTermsModal" 
      @close="showTermsModal = false" 
      @accept="acceptTerms = true" 
    />
  </div>
</template>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.register-container {
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray, #f8f6f3);
  padding: 1.5rem 1rem;
}

.register-wrapper {
  width: 100%;
  max-width: 460px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.register-card {
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

/* LOGO & BRANDING */
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
  gap: 1.1rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
}

.field-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.field-label .required {
  color: #dc2626;
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

/* TELÉFONO INTEGRADO */
.phone-input-group {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
}

.phone-prefix {
  background-color: var(--DC-bg-gray, #f8f6f3);
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  border-radius: 12px;
  padding: 0.75rem 0.9rem;
  font-weight: 800;
  font-size: 0.9rem;
  color: var(--DC-orange, #e28743);
  user-select: none;
  flex-shrink: 0;
}

/* TARJETA REQUISITOS CONTRASEÑA */
.password-requirements-card {
  background-color: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  padding: 0.75rem 0.95rem;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.requirements-title {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.74rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.req-grid {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.req-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.73rem;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 600;
  transition: color 0.2s ease;
}

.req-dot {
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background-color: #cbd5e1;
  flex-shrink: 0;
}

.req-icon {
  color: #16a34a;
  flex-shrink: 0;
}

.req-item.met {
  color: #15803d;
  font-weight: 700;
}

/* CHECKBOX TÉRMINOS */
.terms-container {
  width: 100%;
  margin-top: 0.25rem;
}

.terms-label {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  cursor: pointer;
  user-select: none;
}

.modern-checkbox {
  margin-top: 2px;
  width: 16px;
  height: 16px;
  accent-color: var(--DC-orange, #e28743);
  cursor: pointer;
  flex-shrink: 0;
}

.terms-text {
  font-size: 0.78rem;
  line-height: 1.45;
  color: var(--DC-text-gray, #7c7468);
}

.terms-link-btn {
  background: none;
  border: none;
  padding: 0;
  color: var(--DC-orange, #e28743);
  font-weight: 800;
  font-size: 0.78rem;
  text-decoration: underline;
  cursor: pointer;
  display: inline;
  text-align: left;
}

.terms-link-btn:hover {
  color: var(--DC-brown, #513119);
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

/* LOGIN PROMPT */
.login-prompt {
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
  .register-card {
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