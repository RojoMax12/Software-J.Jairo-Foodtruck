<template>
  <div 
    v-if="isVisible && announcements.length > 0" 
    class="marquee-announcement-bar" 
    role="region" 
    aria-label="Avisos del restaurante"
  >
    <!-- Gradiente de desvanecimiento izquierdo -->
    <div class="marquee-fade-left" aria-hidden="true"></div>

    <div class="marquee-track">
      <!-- Primer bloque de anuncios -->
      <div class="marquee-content">
        <div 
          v-for="(item, idx) in announcements" 
          :key="'m1-' + (item.id || idx)" 
          class="marquee-item"
        >
          <span class="marquee-badge" :class="'badge-' + item.type">
            <Flame v-if="item.type === 'promo'" :size="12" />
            <Clock v-else-if="item.type === 'schedule'" :size="12" />
            <Sparkles v-else-if="item.type === 'new'" :size="12" />
            <CreditCard v-else-if="item.type === 'payment'" :size="12" />
            <Megaphone v-else :size="12" />
            <span>{{ item.badge }}</span>
          </span>
          <span class="marquee-text">{{ item.text }}</span>
          <span v-if="item.highlight" class="marquee-highlight">{{ item.highlight }}</span>
          <span class="marquee-separator" aria-hidden="true">✦</span>
        </div>
      </div>

      <!-- Segundo bloque duplicado para ciclo infinito continuo -->
      <div class="marquee-content" aria-hidden="true">
        <div 
          v-for="(item, idx) in announcements" 
          :key="'m2-' + (item.id || idx)" 
          class="marquee-item"
        >
          <span class="marquee-badge" :class="'badge-' + item.type">
            <Flame v-if="item.type === 'promo'" :size="12" />
            <Clock v-else-if="item.type === 'schedule'" :size="12" />
            <Sparkles v-else-if="item.type === 'new'" :size="12" />
            <CreditCard v-else-if="item.type === 'payment'" :size="12" />
            <Megaphone v-else :size="12" />
            <span>{{ item.badge }}</span>
          </span>
          <span class="marquee-text">{{ item.text }}</span>
          <span v-if="item.highlight" class="marquee-highlight">{{ item.highlight }}</span>
          <span class="marquee-separator" aria-hidden="true">✦</span>
        </div>
      </div>
    </div>

    <!-- Gradiente de desvanecimiento derecho -->
    <div class="marquee-fade-right" aria-hidden="true"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Megaphone, Flame, Clock, Sparkles, CreditCard } from 'lucide-vue-next';
import { useMarketingConfig } from '@/composables/useMarketingConfig';

const { activeAnnouncements } = useMarketingConfig();

const isVisible = ref(true);

const announcements = computed(() => {
  const active = typeof activeAnnouncements === 'function' ? activeAnnouncements() : [];
  return Array.isArray(active) ? active : [];
});
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.marquee-announcement-bar {
  background: var(--DC-brown, #513119);
  background-image: linear-gradient(90deg, #3d220e 0%, #513119 50%, #3d220e 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(0, 0, 0, 0.35);
  color: #ffffff;
  overflow: hidden;
  position: relative;
  height: 36px;
  display: flex;
  align-items: center;
  z-index: 998;
  user-select: none;
  width: 100%;
}

/* Difuminado suave en extremos */
.marquee-fade-left {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  width: 48px;
  background: linear-gradient(90deg, #3d220e 20%, rgba(61, 34, 14, 0) 100%);
  pointer-events: none;
  z-index: 3;
}

.marquee-fade-right {
  position: absolute;
  top: 0;
  bottom: 0;
  right: 0;
  width: 48px;
  background: linear-gradient(270deg, #3d220e 20%, rgba(61, 34, 14, 0) 100%);
  pointer-events: none;
  z-index: 3;
}

/* Riel continuo */
.marquee-track {
  display: flex;
  width: max-content;
  will-change: transform;
  animation: marquee-scroll 45s linear infinite;
}

.marquee-track:hover {
  animation-play-state: paused;
}

@keyframes marquee-scroll {
  0% {
    transform: translate3d(0, 0, 0);
  }
  100% {
    transform: translate3d(-50%, 0, 0);
  }
}

.marquee-content {
  display: flex;
  align-items: center;
  flex-shrink: 0;
}

.marquee-item {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 0 18px;
  white-space: nowrap;
}

/* Badges temáticos */
.marquee-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  padding: 2px 7px;
  border-radius: 999px;
  line-height: 1.1;
}

.badge-promo {
  background: rgba(226, 135, 67, 0.25);
  border: 1px solid rgba(226, 135, 67, 0.6);
  color: #ffcba4;
}

.badge-schedule {
  background: rgba(59, 130, 246, 0.2);
  border: 1px solid rgba(59, 130, 246, 0.5);
  color: #93c5fd;
}

.badge-new {
  background: rgba(16, 185, 129, 0.2);
  border: 1px solid rgba(16, 185, 129, 0.5);
  color: #6ee7b7;
}

.badge-payment {
  background: rgba(168, 85, 247, 0.2);
  border: 1px solid rgba(168, 85, 247, 0.5);
  color: #d8b4fe;
}

.badge-info {
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.28);
  color: #f1f5f9;
}

.marquee-text {
  font-size: 0.78rem;
  font-weight: 700;
  color: #f8fafc;
  letter-spacing: 0.01em;
}

.marquee-highlight {
  font-size: 0.72rem;
  font-weight: 800;
  color: #fed7aa;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(254, 215, 170, 0.25);
  padding: 1px 6px;
  border-radius: 4px;
}

.marquee-separator {
  color: var(--DC-orange, #e28743);
  font-size: 0.68rem;
  margin-left: 8px;
  opacity: 0.75;
}

/* Responsivo en móviles */
@media (max-width: 768px) {
  .marquee-announcement-bar {
    height: 32px;
  }

  .marquee-track {
    animation-duration: 28s;
  }

  .marquee-fade-left,
  .marquee-fade-right {
    width: 28px;
  }

  .marquee-text {
    font-size: 0.72rem;
  }

  .marquee-badge {
    font-size: 0.58rem;
    padding: 1px 5px;
  }

  .marquee-item {
    padding: 0 10px;
    gap: 6px;
  }
}
</style>