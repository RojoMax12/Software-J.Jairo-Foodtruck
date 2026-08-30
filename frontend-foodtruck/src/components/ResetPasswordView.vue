<script setup lang="ts">
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ArrowLeft, Eye, EyeOff } from 'lucide-vue-next'
import api from '@/services/api'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const route = useRoute()
const { notify } = useNotification()

// --------------------------------------------------
// Datos provenientes del enlace de recuperación
// --------------------------------------------------

const token = computed(() => {
  const value = route.query.token
  return typeof value === 'string' ? value : ''
})

const correo = computed(() => {
  const value = route.query.correo
  return typeof value === 'string' ? value : ''
})

// --------------------------------------------------
// Formulario
// --------------------------------------------------

const password = ref('')
const passwordConfirmation = ref('')

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const isLoading = ref(false)
const resetSuccess = ref(false)

const errorMessage = ref('')

// --------------------------------------------------
// Validaciones de contraseña
// --------------------------------------------------

const hasMinLength = computed(() =>
  password.value.length >= 8
)

const hasUppercase = computed(() =>
  /[A-Z]/.test(password.value)
)

const hasLowercase = computed(() =>
  /[a-z]/.test(password.value)
)

const hasNumber = computed(() =>
  /\d/.test(password.value)
)

const passwordIsValid = computed(() =>
  hasMinLength.value &&
  hasUppercase.value &&
  hasLowercase.value &&
  hasNumber.value
)

const passwordsMatch = computed(() =>
  passwordConfirmation.value.length > 0 &&
  password.value === passwordConfirmation.value
)

const isFormValid = computed(() =>
  passwordIsValid.value &&
  passwordsMatch.value
)

// --------------------------------------------------
// Mensaje de confirmación
// --------------------------------------------------

const confirmationError = computed(() => {
  if (!passwordConfirmation.value) {
    return ''
  }

  if (passwordConfirmation.value !== password.value) {
    return 'Las contraseñas no coinciden.'
  }

  return ''
})

// --------------------------------------------------
// Navegación
// --------------------------------------------------

const goBack = () => {
  router.back()
}

const goToLogin = () => {
  router.push('/login')
}

// --------------------------------------------------
// Restablecer contraseña
// --------------------------------------------------

const handleResetPassword = async () => {
  errorMessage.value = ''

  // Verificar que el enlace contenga los parámetros necesarios
  if (!token.value || !correo.value) {
    errorMessage.value =
      'El enlace de recuperación no es válido o está incompleto.'

    notify(
      errorMessage.value,
      'error'
    )

    return
  }

  // Validación del formulario
  if (!isFormValid.value) {
    errorMessage.value =
      'Por favor, verifica que las contraseñas cumplan todos los requisitos.'

    return
  }

  isLoading.value = true

  try {
    await api.post('/auth/reset-password', {
      token: token.value,
      correo: correo.value,
      password: password.value,
      password_confirmation: passwordConfirmation.value
    })

    resetSuccess.value = true

    notify(
      'Contraseña restablecida correctamente.',
      'success'
    )

  } catch (error: any) {
    console.error('Reset password error:', error)

    errorMessage.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'No fue posible restablecer la contraseña.'

    notify(
      errorMessage.value,
      'error'
    )

  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="reset-container">
    <div class="reset-wrapper">

      <!-- Volver -->
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="var(--DC-orange)" />
        <span>Volver</span>
      </div>

      <div class="reset-card">

        <!-- Logo -->
        <div class="logo-section">
          <img
            src="../assets/logo_jairo.webp"
            alt="J.Jairo Logo"
            class="logo"
          />
        </div>

        <div class="divider"></div>

        <!-- FORMULARIO -->
        <template v-if="!resetSuccess">

          <div class="text-section">
            <h2>Restablece tu contraseña</h2>

            <p>
              Ingresa una nueva contraseña para recuperar
              el acceso a tu cuenta.
            </p>
          </div>

          <div class="form-section">

            <!-- Error general -->
            <div
              v-if="errorMessage"
              class="error-banner"
            >
              {{ errorMessage }}
            </div>

            <!-- Requisitos -->
            <div class="password-rules">

              <span :class="{ valid: hasMinLength }">
                {{ hasMinLength ? '✓' : '•' }}
                Mínimo 8 caracteres
              </span>

              <span :class="{ valid: hasUppercase }">
                {{ hasUppercase ? '✓' : '•' }}
                Una letra mayúscula
              </span>

              <span :class="{ valid: hasLowercase }">
                {{ hasLowercase ? '✓' : '•' }}
                Una letra minúscula
              </span>

              <span :class="{ valid: hasNumber }">
                {{ hasNumber ? '✓' : '•' }}
                Un número
              </span>

            </div>

            <!-- Nueva contraseña -->
            <div class="input-group">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Nueva contraseña"
                class="custom-input"
                :disabled="isLoading"
              />

              <div
                class="icon-wrapper"
                @click="showPassword = !showPassword"
              >
                <Eye
                  v-if="!showPassword"
                  class="clickable"
                  :size="20"
                  color="#322c44"
                />

                <EyeOff
                  v-else
                  class="clickable"
                  :size="20"
                  color="#322c44"
                />
              </div>
            </div>

            <!-- Confirmar contraseña -->
            <div class="input-group">
              <input
                v-model="passwordConfirmation"
                :type="
                  showPasswordConfirmation
                    ? 'text'
                    : 'password'
                "
                placeholder="Confirmar nueva contraseña"
                class="custom-input"
                :disabled="isLoading"
              />

              <div
                class="icon-wrapper"
                @click="
                  showPasswordConfirmation =
                    !showPasswordConfirmation
                "
              >
                <Eye
                  v-if="!showPasswordConfirmation"
                  class="clickable"
                  :size="20"
                  color="#322c44"
                />

                <EyeOff
                  v-else
                  class="clickable"
                  :size="20"
                  color="#322c44"
                />
              </div>
            </div>

            <!-- Error confirmación -->
            <div
              v-if="confirmationError"
              class="field-error"
            >
              {{ confirmationError }}
            </div>

            <!-- Botón -->
            <button
              @click="handleResetPassword"
              class="btn btn-primary"
              :disabled="isLoading || !isFormValid"
            >
              {{
                isLoading
                  ? 'CAMBIANDO CONTRASEÑA...'
                  : 'CAMBIAR CONTRASEÑA'
              }}
            </button>

          </div>

        </template>

        <!-- ÉXITO -->
        <template v-else>

          <div class="success-section">

            <div class="success-icon">
              ✓
            </div>

            <div class="text-section">
              <h2>Contraseña actualizada</h2>

              <p>
                Tu contraseña ha sido restablecida correctamente.
                Ya puedes iniciar sesión con tu nueva contraseña.
              </p>
            </div>

            <button
              @click="goToLogin"
              class="btn btn-primary"
            >
              VOLVER AL LOGIN
            </button>

          </div>

        </template>

      </div>
    </div>
  </div>
</template>

<style scoped>
.reset-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray);
  font-family: var(--font-main);
  padding: 20px;
  box-sizing: border-box;
}

.reset-wrapper {
  position: relative;
  width: 100%;
  max-width: 420px;
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

.reset-card {
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

.logo-section {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo {
  max-width: 180px;
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: var(--DC-brown);
  margin-bottom: 2rem;
  opacity: 0.2;
}

.text-section {
  text-align: center;
  margin-bottom: 2rem;
}

.text-section h2 {
  color: var(--DC-gray);
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.text-section p {
  color: var(--DC-text-gray);
  font-size: 0.95rem;
  line-height: 1.5;
}

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: center;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.custom-input {
  width: 100%;
  padding: 0.9rem 3rem 0.9rem 1.2rem;
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
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.2);
}

.custom-input::placeholder {
  color: var(--DC-text-gray);
  font-weight: 500;
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

.password-rules {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
  margin-top: -0.8rem;
  font-size: 0.78rem;
  color: #999;
}

.password-rules span.valid {
  color: #3b8f5c;
  font-weight: 700;
}

.field-error {
  width: 100%;
  margin-top: -0.8rem;
  color: var(--DC-pink);
  font-size: 0.8rem;
  font-weight: 700;
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

.btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  background-color: #ccc;
  box-shadow: none;
  color: #666;
}

.success-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.success-section .text-section {
  margin-bottom: 1rem;
}

.success-icon {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background-color: #e8f6ed;
  color: #3b8f5c;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  font-weight: 900;
  margin-bottom: 1.5rem;
}

@media (max-width: 576px) {
  .reset-card {
    padding: 3rem 1rem 1.5rem;
  }

  .back-button {
    top: 12px;
    left: 12px;
    font-size: 0.85rem;
  }

  .text-section h2 {
    font-size: 1.3rem;
  }
}
</style>