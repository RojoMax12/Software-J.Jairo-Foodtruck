<template>
  <div v-if="isVisible" class="marquee-announcement-bar" role="banner">
    <!-- Efectos de desvanecimiento en los bordes -->
    <div class="marquee-fade-left"></div>

    <div class="marquee-track">
      <!-- Primer conjunto de mensajes -->
      <div class="marquee-content">
        <div v-for="(item, idx) in announcements" :key="'m1-' + idx" class="marquee-item">
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
          <span class="marquee-separator">✦</span>
        </div>
      </div>

      <!-- Segundo conjunto duplicado para scroll infinito y continuo -->
      <div class="marquee-content" aria-hidden="true">
        <div v-for="(item, idx) in announcements" :key="'m2-' + idx" class="marquee-item">
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
          <span class="marquee-separator">✦</span>
        </div>
      </div>
    </div>

    <div class="marquee-fade-right"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Megaphone, Flame, Clock, Sparkles, CreditCard } from 'lucide-vue-next';
import { useMarketingConfig } from '@/composables/useMarketingConfig';

const { activeAnnouncements } = useMarketingConfig();

const isVisible = ref(true);

const announcements = computed(() => {
  const active = activeAnnouncements();
  return active.length > 0 ? active : [];
});
</script>

<style scoped>
.marquee-announcement-bar {
  background-color: var(--DC-brown, #513119);
  background: linear-gradient(90deg, #442813 0%, #513119 25%, #5d381c 50%, #513119 75%, #442813 100%);
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  border-bottom: 1px solid rgba(0, 0, 0, 0.25);
  color: #ffffff;
  font-family: var(--font-main, sans-serif);
  overflow: hidden;
  position: relative;
  height: 36px;
  display: flex;
  align-items: center;
  z-index: 998;
  user-select: none;
}

/* Efectos de gradiente en los extremos */
.marquee-fade-left,
.marquee-fade-right {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 35px;
  pointer-events: none;
  z-index: 5;
}

/* Riel de marquesina continua */
.marquee-track {
  display: flex;
  width: max-content;
  animation: marquee-infinite 60s linear infinite;
}

.marquee-track:hover {
  animation-play-state: paused;
}

@keyframes marquee-infinite {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
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
  padding: 0 16px;
  white-space: nowrap;
}

/* Badges temáticos */
.marquee-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.65rem;
  font-weight: 900;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  padding: 2px 7px;
  border-radius: 999px;
}

.badge-promo {
  background: rgba(226, 135, 67, 0.25);
  border: 1px solid rgba(226, 135, 67, 0.6);
  color: #ffcaa3;
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
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #f1f5f9;
}

.marquee-text {
  font-size: 0.78rem;
  font-weight: 600;
  color: #f8fafc;
}

.marquee-highlight {
  font-size: 0.74rem;
  font-weight: 800;
  color: #fed7aa;
  background: rgba(255, 255, 255, 0.12);
  padding: 1px 6px;
  border-radius: 4px;
}

.marquee-separator {
  color: rgba(226, 135, 67, 0.7);
  font-size: 0.7rem;
  margin-left: 8px;
}

/* Botón cerrar */
.btn-dismiss-marquee {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(68, 40, 19, 0.9);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: rgba(255, 255, 255, 0.7);
  width: 22px;
  height: 22px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  z-index: 10;
  transition: all 0.2s ease;
}

.btn-dismiss-marquee:hover {
  background: #ff6b00;
  border-color: #ff6b00;
  color: #ffffff;
  transform: translateY(-50%) scale(1.1);
}

/* 📱 RESPONSIVO */
@media (max-width: 768px) {
  .marquee-announcement-bar {
    height: 32px;
  }

  .marquee-track {
    animation-duration: 28s;
  }

  .marquee-text {
    font-size: 0.74rem;
  }

  .marquee-badge {
    font-size: 0.6rem;
    padding: 1px 5px;
  }

  .marquee-item {
    padding: 0 10px;
    gap: 6px;
  }
}
</style>
