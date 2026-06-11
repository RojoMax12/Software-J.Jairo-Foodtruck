<script setup lang="ts">
import { CheckCircle2 } from 'lucide-vue-next'

defineProps<{
  title?: string
  description?: string
  buttonText?: string
}>()

const emit = defineEmits(['accept'])
</script>

<template>
  <div class="modal-overlay" @click.self="emit('accept')">
    <div class="modal-card">
      <div class="icon-container">
        <div class="icon-bg"></div>
        <CheckCircle2 :size="64" color="#e4869f" class="check-icon" />
      </div>
      
      <h2 class="modal-title">{{ title || '¡Cuenta creada con éxito!' }}</h2>
      <p class="modal-description">
        {{ description || 'Tu registro como distribuidor ha sido procesado. Ahora puedes iniciar sesión con tus credenciales.' }}
      </p>

      <button class="accept-button" @click="emit('accept')">
        {{ buttonText || 'IR AL LOGIN' }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(50, 44, 68, 0.4);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 3000;
  animation: fadeIn 0.3s ease-out;
}

.modal-card {
  background-color: white;
  width: 90%;
  max-width: 400px;
  padding: 2.5rem 2rem;
  border-radius: 2rem;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  position: relative;
  overflow: hidden;
  animation: scaleUp 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.icon-container {
  position: relative;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-bg {
  position: absolute;
  width: 80px;
  height: 80px;
  background-color: #fff0f3;
  border-radius: 50%;
  z-index: 0;
  animation: pulse 2s infinite;
}

.check-icon {
  position: relative;
  z-index: 1;
  animation: checkDraw 0.5s ease-out forwards;
}

.modal-title {
  color: #322c44;
  font-size: 1.5rem;
  font-weight: 800;
  margin-bottom: 1rem;
}

.modal-description {
  color: #666;
  font-size: 1rem;
  line-height: 1.5;
  margin-bottom: 2rem;
}

.accept-button {
  background-color: #e4869f;
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: 1rem;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  box-shadow: 0 4px 12px rgba(228, 134, 159, 0.3);
}

.accept-button:hover {
  background-color: #d6758e;
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(228, 134, 159, 0.4);
}

.accept-button:active {
  transform: translateY(0);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleUp {
  from { opacity: 0; transform: scale(0.8); }
  to { opacity: 1; transform: scale(1); }
}

@keyframes pulse {
  0% { transform: scale(1); opacity: 0.5; }
  50% { transform: scale(1.2); opacity: 0.2; }
  100% { transform: scale(1); opacity: 0.5; }
}

@keyframes checkDraw {
  0% { transform: scale(0) rotate(-45deg); opacity: 0; }
  70% { transform: scale(1.2) rotate(0deg); }
  100% { transform: scale(1) rotate(0deg); opacity: 1; }
}
</style>
