<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, ArrowLeft } from 'lucide-vue-next'
import api from '@/services/api'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

const email = ref('')
const isLoading = ref(false)
const errorMessage = ref('')
const emailSent = ref(false)

const goBack = () => {
  router.back()
}

const handleResetPassword = async () => {
  errorMessage.value = ''

  const correo = email.value.trim()

  // Validación básica
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
      'No fue posible enviar el correo de recuperación.'

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
  <div class="forgot-container">
    <div class="forgot-wrapper">
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="var(--DC-orange)" />
        <span>Volver</span>
      </div>

      <div class="forgot-card">
        <div class="logo-section">
          <img src="../assets/logo_jairo.webp" alt="J.Junior Logo" class="logo" />
        </div>

        <div class="divider"></div>

        <!-- FORMULARIO -->
        <template v-if="!emailSent">

          <div class="text-section">
            <h2>Recupera tu acceso</h2>

            <p>
              Ingresa tu correo electrónico y te enviaremos
              instrucciones para restablecer tu contraseña.
            </p>
          </div>

          <div class="form-section">

            <!-- Error -->
            <div v-if="errorMessage" class="error-banner">
              {{ errorMessage }}
            </div>

            <div class="input-group">
              <input
                v-model="email"
                type="email"
                placeholder="Correo electrónico"
                class="custom-input"
                :disabled="isLoading"
                @keyup.enter="handleResetPassword"
              />

              <Mail
                class="input-icon"
                :size="20"
              />
            </div>

            <button
              @click="handleResetPassword"
              class="btn btn-primary"
              :disabled="isLoading"
            >
              {{ isLoading
                ? 'ENVIANDO...'
                : 'ENVIAR INSTRUCCIONES'
              }}
            </button>

          </div>

        </template>

        <!-- CORREO ENVIADO -->
        <template v-else>

          <div class="success-section">

            <div class="success-icon">
              ✓
            </div>

            <div class="text-section">
              <h2>Revisa tu correo</h2>

              <p>
                Si el correo ingresado está registrado,
                recibirás un enlace para restablecer tu contraseña.
              </p>
            </div>

            <button
              @click="router.push('/login')"
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
.forgot-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: var(--DC-bg-gray);
  font-family: var(--font-main);
  padding: 20px;
  box-sizing: border-box;
}

.forgot-wrapper {
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

.forgot-card {
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
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.2);
}

.custom-input::placeholder {
  color: var(--DC-text-gray);
  font-weight: 500;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
  color: var(--DC-brown);
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

.success-section .text-section {
  margin-bottom: 1rem;
}

@media (max-width: 576px) {
  .forgot-card {
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