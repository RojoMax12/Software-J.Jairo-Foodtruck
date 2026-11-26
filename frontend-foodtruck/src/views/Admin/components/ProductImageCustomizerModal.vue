<template>
  <div v-if="isOpen" class="modal-backdrop customizer-backdrop" @click.self="$emit('cancel')">
    <div class="modal-card customizer-modal-card">
      <div class="modal-header">
        <div class="modal-header-title">
          <div class="header-icon-pill"><SlidersHorizontal :size="18" /></div>
          <div>
            <h3>Personalizar Imagen</h3>
            <p class="modal-header-desc">Ajusta encuadre y zoom en pantalla</p>
          </div>
        </div>
        <button type="button" class="close-btn" @click="$emit('cancel')"><X :size="18" /></button>
      </div>

      <div class="customizer-body">
        <div class="customizer-columns">
          <div class="customizer-preview-large">
            <img
              :src="productForm.image"
              alt="Preview"
              class="customizer-preview-img"
              :style="{
                objectPosition: productForm.imagePosition,
                objectFit: productForm.imageFit,
                transform: 'scale(' + Math.max(1, productForm.imageZoom) + ')'
              }"
            />
          </div>

          <div class="customizer-controls">
            <div class="customizer-control-group">
              <span class="sub-legend">Ajuste de Marco</span>
              <div class="control-options-grid">
                <button
                  type="button"
                  class="control-option-btn"
                  :class="{ active: productForm.imageFit === 'cover' }"
                  @click="productForm.imageFit = 'cover'"
                >
                  Llenar marco
                </button>
                <button
                  type="button"
                  class="control-option-btn"
                  :class="{ active: productForm.imageFit === 'contain' }"
                  @click="productForm.imageFit = 'contain'"
                >
                  Completa
                </button>
              </div>
            </div>

            <div class="customizer-control-group">
              <span class="sub-legend">Zoom ({{ Math.round(productForm.imageZoom * 100) }}%)</span>
              <input
                v-model.number="productForm.imageZoom"
                type="range"
                min="1"
                max="1.6"
                step="0.05"
                class="customizer-zoom-range"
              />
            </div>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancel" @click="$emit('cancel')">Cancelar</button>
        <button type="button" class="btn-save" @click="$emit('apply')">
          <Check :size="16" />
          <span>Aplicar</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Check, SlidersHorizontal, X } from 'lucide-vue-next';

defineProps<{
  isOpen: boolean;
  productForm: {
    image: string;
    imagePosition: string;
    imageFit: 'cover' | 'contain';
    imageZoom: number;
  };
}>();

defineEmits<{
  cancel: [];
  apply: [];
}>();
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
  overflow-y: auto;
}

.modal-card {
  position: relative;
  width: min(100%, 440px);
  border-radius: 18px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.modal-header-title {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.header-icon-pill {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--DC-brown, #513119);
}

.modal-header-desc {
  margin: 0;
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.customizer-body {
  padding: 1.25rem;
}

.customizer-columns {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.customizer-preview-large {
  width: 100%;
  height: 220px;
  border-radius: 12px;
  overflow: hidden;
  background: #f5eee6;
}

.customizer-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.customizer-controls {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.customizer-control-group {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
}

.sub-legend {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.control-options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.control-option-btn {
  padding: 0.5rem 0.75rem;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
  cursor: pointer;
}

.control-option-btn.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.customizer-zoom-range {
  width: 100%;
  accent-color: var(--DC-orange, #e28743);
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 1rem 1.25rem;
  background: #fffdfa;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.btn-cancel {
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
}

.btn-save {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}
</style>

