<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Mail, ArrowLeft } from 'lucide-vue-next'

const router = useRouter()
const email = ref('')

const goBack = () => {
  router.back()
}

const handleResetPassword = () => {
  console.log('Reset password request for:', email.value)
  // Falta la lógica para enviar el correo de restablecimiento de contraseña :)
}
</script>

<template>
  <div class="forgot-container">
    <div class="forgot-wrapper">
      <div class="back-button" @click="goBack">
        <ArrowLeft :size="24" color="#e4869f" />
        <span>Volver</span>
      </div>

      <div class="forgot-card">
        <div class="logo-section">
          <img src="../assets/logo_dicreme.png" alt="DiCreme Logo" class="logo" />
        </div>
        
        <div class="divider"></div>

        <div class="text-section">
          <h2>¿Olvidaste tu contraseña?</h2>
          <p>Ingresa tu correo electrónico y te enviaremos instrucciones para restablecerla.</p>
        </div>

        <div class="form-section">
          <div class="input-group">
            <input 
              v-model="email" 
              type="email" 
              placeholder="Correo electrónico" 
              class="custom-input"
            />
            <Mail class="input-icon" :size="20" color="#322c44" />
          </div>

          <button @click="handleResetPassword" class="btn btn-primary">ENVIAR INSTRUCCIONES</button>
        </div>
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
  background-color: #eeedee;
  font-family: sans-serif;
}

.forgot-wrapper {
  position: relative;
  width: 100%;
  max-width: 450px;
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

.forgot-card {
  background-color: white;
  padding: 2.5rem;
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
  max-width: 180px;
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: #e4869f;
  margin-bottom: 2rem;
}

.text-section {
  text-align: center;
  margin-bottom: 2rem;
}

.text-section h2 {
  color: #322c44;
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.text-section p {
  color: #9793a0;
  font-size: 0.95rem;
  line-height: 1.4;
}

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
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
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  font-size: 1rem;
  outline: none;
  box-sizing: border-box;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
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

.btn:hover {
  transform: translateY(-2px);
  filter: brightness(0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}
</style>
