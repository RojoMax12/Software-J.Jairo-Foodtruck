<template>
  <Transition name="fade">
    <div 
      v-if="globalLoading" 
      class="loading-overlay" 
      role="status" 
      aria-live="polite"
      aria-label="Cargando contenido"
    >
      <div class="loading-card">
        <!-- ÍCONO ANIMADO CON ANILLO DE PULSO -->
        <div class="icon-ring">
          <span class="fast-food-icon" role="img" aria-label="Comida rápida">🍔</span>
        </div>

        <!-- MENSAJE DE ESTADO -->
        <div class="loading-text-group">
          <p class="loading-title">Preparando tu experiencia</p>
          <span class="loading-subtitle">Un momento por favor...</span>
        </div>

        <!-- BARRA INDETERMINADA DE PROGRESO -->
        <div class="loading-bar-track">
          <div class="loading-bar-fill"></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { globalLoading } from '@/composables/useLoading'
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.loading-overlay {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(35, 20, 10, 0.45);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
  padding: 1rem;
}

.loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #ffffff;
  padding: 2.25rem 2.75rem;
  border-radius: 24px;
  box-shadow: 0 20px 48px rgba(26, 14, 5, 0.16);
  border: 1px solid rgba(81, 49, 25, 0.08);
  max-width: 320px;
  width: 100%;
  text-align: center;
}

/* ANILLO DE PULSO */
.icon-ring {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(226, 135, 67, 0.12);
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  margin-bottom: 1.15rem;
  animation: pulseRing 2s ease-in-out infinite;
}

.fast-food-icon {
  font-size: 2.4rem;
  line-height: 1;
  user-select: none;
  animation: floatBounce 1.3s ease-in-out infinite alternate;
}

/* TEXTOS */
.loading-text-group {
  display: flex;
  flex-direction: column;
  gap: 3px;
  margin-bottom: 1.25rem;
}

.loading-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  line-height: 1.3;
}

.loading-subtitle {
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--DC-text-gray, #7c7468);
}

/* BARRA DE PROGRESO INDETERMINADA */
.loading-bar-track {
  width: 150px;
  height: 4px;
  background-color: var(--DC-bg-gray, #f8f6f3);
  border-radius: 999px;
  overflow: hidden;
  position: relative;
}

.loading-bar-fill {
  width: 45%;
  height: 100%;
  background: linear-gradient(90deg, var(--DC-orange, #e28743) 0%, #fed7aa 100%);
  border-radius: 999px;
  position: absolute;
  left: -45%;
  animation: loadingSlide 1.3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
}

/* KEYFRAMES */
@keyframes floatBounce {
  0% { transform: translateY(2px) scale(0.98); }
  100% { transform: translateY(-5px) scale(1.06); }
}

@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0 rgba(226, 135, 67, 0.35); }
  70% { box-shadow: 0 0 0 16px rgba(226, 135, 67, 0); }
  100% { box-shadow: 0 0 0 0 rgba(226, 135, 67, 0); }
}

@keyframes loadingSlide {
  0% { left: -45%; }
  100% { left: 100%; }
}

/* TRANSICIÓN DE ENTRADA Y SALIDA */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

@media (max-width: 480px) {
  .loading-card {
    padding: 1.85rem 1.75rem;
    border-radius: 20px;
  }

  .icon-ring {
    width: 70px;
    height: 70px;
  }

  .fast-food-icon {
    font-size: 2.1rem;
  }

  .loading-title {
    font-size: 0.94rem;
  }
}
</style>