<template>
  <div class="confirm-overlay" @click.self="handleOverlayClick">
    <div class="confirm-card">
      <template v-if="!isSuccess">
        <div class="confirm-icon-box">
          <component :is="displayIcon" :size="32" :class="displayIconClass" />
        </div>
        <h3 class="confirm-title">{{ displayTitle }}</h3>
        <p class="confirm-description">{{ displayDescription }}</p>
        <div class="confirm-actions">
          <button class="btn-confirm btn-yes" :class="displayConfirmBtnClass" @click="$emit('confirm')">
            {{ displayConfirmText }}
          </button>
          <button class="btn-confirm btn-no" @click="$emit('cancel')">
            {{ displayCancelText }}
          </button>
        </div>
      </template>

      <template v-else>
        <div class="confirm-icon-box success-bg">
          <component :is="displaySuccessIcon" :size="32" class="text-green" />
        </div>
        <h3 class="confirm-title">{{ displaySuccessTitle }}</h3>
        <p class="confirm-description">{{ displaySuccessDescription }}</p>
        <div class="confirm-actions">
          <button class="btn-confirm btn-yes bg-green" @click="$emit('accept')">
            {{ displaySuccessBtnText }}
          </button>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import { ClockAlert, CheckCircle2 } from 'lucide-vue-next';

interface Props {
  type?: 'cancel' | 'complete' | 'generic';
  orderId?: number | string;
  
  // Custom overrides
  title?: string;
  description?: string;
  confirmText?: string;
  cancelText?: string;
  icon?: Component;
  iconClass?: string;
  confirmBtnClass?: string;
  
  isSuccess?: boolean;
  successTitle?: string;
  successDescription?: string;
  successBtnText?: string;
  successIcon?: Component;
}

const props = withDefaults(defineProps<Props>(), {
  type: 'generic',
  isSuccess: false
});

const emit = defineEmits(['confirm', 'cancel', 'accept']);

// Logic to determine what to display based on type or props
const displayTitle = computed(() => {
  if (props.title) return props.title;
  if (props.type === 'cancel') return '¿Estás seguro de que quieres cancelar esta cotización?';
  return '¿Estás seguro?';
});

const displayDescription = computed(() => {
  if (props.description) return props.description;
  if (props.type === 'cancel') return 'Esta operación no se puede deshacer. Podrás seguir viendo el detalle de la cotización en la ventana de cotizaciones.';
  return '';
});

const displayConfirmText = computed(() => {
  if (props.confirmText) return props.confirmText;
  if (props.type === 'cancel') return 'Sí, cancelar cotización';
  return 'Confirmar';
});

const displayCancelText = computed(() => {
  if (props.cancelText) return props.cancelText;
  if (props.type === 'cancel') return 'No cancelar';
  return 'Cancelar';
});

const displayIcon = computed(() => {
  if (props.icon) return props.icon;
  return ClockAlert;
});

const displayIconClass = computed(() => {
  if (props.iconClass) return props.iconClass;
  return 'text-red';
});

const displayConfirmBtnClass = computed(() => {
  if (props.confirmBtnClass) return props.confirmBtnClass;
  return 'bg-red';
});

// Success state computeds
const displaySuccessTitle = computed(() => {
  if (props.successTitle) return props.successTitle;
  if (props.type === 'cancel') return `Cotización número #${props.orderId} cancelada con éxito`;
  return 'Operación completada';
});

const displaySuccessDescription = computed(() => {
  if (props.successDescription) return props.successDescription;
  if (props.type === 'cancel') return 'El distribuidor recibirá una notificación sobre su cotización.';
  return '';
});

const displaySuccessBtnText = computed(() => {
  if (props.successBtnText) return props.successBtnText;
  return 'Aceptar';
});

const displaySuccessIcon = computed(() => {
  if (props.successIcon) return props.successIcon;
  return CheckCircle2;
});

const handleOverlayClick = () => {
  if (props.isSuccess) {
    emit('accept');
  } else {
    emit('cancel');
  }
};
</script>

<style scoped>
.confirm-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.4);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000;
  backdrop-filter: blur(2px);
}

.confirm-card {
  background-color: white;
  width: 90%;
  max-width: 450px;
  border-radius: 16px;
  padding: 32px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.confirm-icon-box {
  width: 64px;
  height: 64px;
  background-color: #f8f9fa;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
}

.success-bg {
  background-color: #ebfbee;
}

.text-red {
  color: #fa5252;
}

.text-green {
  color: #40c057;
}

.confirm-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: #322c44;
  line-height: 1.4;
}

.confirm-description {
  margin: 0;
  font-size: 0.9rem;
  color: #777;
  line-height: 1.5;
  font-weight: 500;
}

.confirm-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
  width: 100%;
  margin-top: 12px;
}

.btn-confirm {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: none;
  font-family: 'Inter', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.bg-red {
  background-color: #fa5252;
  color: white;
}

.bg-red:hover {
  background-color: #e03131;
}

.bg-green {
  background-color: #40c057;
  color: white;
}

.bg-green:hover {
  background-color: #2f9e44;
}

.btn-no {
  background-color: #f8f9fa;
  color: #777;
  border: 1px solid #ddd;
}

.btn-no:hover {
  background-color: #eee;
  color: #322c44;
}
</style>