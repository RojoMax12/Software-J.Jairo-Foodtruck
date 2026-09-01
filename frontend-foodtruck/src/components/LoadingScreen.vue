<template>
  <Transition name="fade">
    <div v-if="globalLoading" class="loading-overlay">
      <div class="loading-card">
        <div class="icon-ring">
          <span class="fast-food-icon">🍔</span>
        </div>
        <div class="loading-bar-track">
          <div class="loading-bar-fill"></div>
        </div>
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
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(252, 248, 242, 0.88); 
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 99999;
}

.loading-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  background: white;
  padding: 36px 44px;
  border-radius: 24px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.icon-ring {
  width: 84px;
  height: 84px;
  border-radius: 50%;
  background: #fff4e6;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  box-shadow: 0 0 0 8px rgba(226, 135, 67, 0.1);
  animation: pulseRing 1.8s ease-in-out infinite;
}

.fast-food-icon {
  font-size: 42px;
  animation: floatBounce 1.2s ease-in-out infinite alternate;
}

.loading-text {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--DC-gray, #322c44);
  margin: 0;
  letter-spacing: 0.3px;
}

.loading-bar-track {
  width: 160px;
  height: 4px;
  background-color: #f1f3f5;
  border-radius: 99px;
  overflow: hidden;
  position: relative;
}

.loading-bar-fill {
  width: 50%;
  height: 100%;
  background: linear-gradient(90deg, var(--DC-orange, #e28743), #f59f00);
  border-radius: 99px;
  position: absolute;
  left: -50%;
  animation: loadingSlide 1.4s ease-in-out infinite;
}

@keyframes floatBounce {
  0% { transform: translateY(2px) scale(1); }
  100% { transform: translateY(-6px) scale(1.08); }
}

@keyframes pulseRing {
  0% { box-shadow: 0 0 0 0px rgba(226, 135, 67, 0.25); }
  70% { box-shadow: 0 0 0 16px rgba(226, 135, 67, 0); }
  100% { box-shadow: 0 0 0 0px rgba(226, 135, 67, 0); }
}

@keyframes loadingSlide {
  0% { left: -50%; }
  100% { left: 100%; }
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>