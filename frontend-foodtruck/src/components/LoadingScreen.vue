<template>
  <Transition name="fade">
    <div v-if="globalLoading" class="loading-overlay">
      <div class="loading-content">
        <div class="fast-food-icon">🌭</div>
        <h2 class="loading-text">Preparando tu bajón...</h2>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { globalLoading } from '@/composables/useLoading';
</script>

<style scoped>
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: var(--DC-bg-gray, #fcf8f2); 
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.loading-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

/* 🌟 Animación de latido (pulse) para el completo */
.fast-food-icon {
  font-size: 80px; /* Tamaño del completo */
  animation: pulse 0.8s infinite alternate;
  filter: drop-shadow(0px 10px 10px rgba(0,0,0,0.1)); /* Sombra para darle estilo */
}

.loading-text {
  font-size: 1.4rem;
  font-weight: 900;
  color: var(--DC-gray, #322c44);
  letter-spacing: 1px;
}

/* Keyframes para que el completo palpite como un corazón */
@keyframes pulse {
  0% { 
    transform: scale(1) translateY(0); 
  }
  100% { 
    transform: scale(1.15) translateY(-10px); 
  }
}

/* Transición suave al entrar y salir */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>