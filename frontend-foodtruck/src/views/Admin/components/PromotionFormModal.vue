<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-header-title">
          <div class="header-icon-pill"><BadgePercent :size="18" /></div>
          <div>
            <h3>{{ isEditing ? 'Editar Promoción' : 'Nueva Promoción' }}</h3>
            <p class="modal-header-desc">Configura producto, fechas y precio promocional</p>
          </div>
        </div>
        <button type="button" class="close-btn" @click="$emit('close')"><X :size="18" /></button>
      </div>

      <form class="modal-body" @submit.prevent="$emit('submit')">
        <label class="modal-label">
          <span>Producto Asociado <span class="required">*</span></span>
          <select v-model.number="promotionForm.id_producto" class="modal-input" required>
            <option value="" disabled>Selecciona un producto</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.name }} (${{ formatCLP(product.price) }})
            </option>
          </select>
        </label>

        <label class="modal-label">
          <span>Tamaño al que aplica la oferta</span>
          <select v-model="promotionForm.id_tamaño" class="modal-input">
            <option :value="null">Todos los tamaños del producto (General)</option>
            <option
              v-for="sz in selectedProductSizes"
              :key="sz.id_tamaño"
              :value="sz.id_tamaño"
            >
              Solo tamaño: {{ sz.nombre }} (Normal: ${{ formatCLP(sz.pivot?.precio ?? sz.precio ?? 0) }})
            </option>
          </select>
        </label>

        <label class="modal-label">
          <span>Título de la Promoción <span class="required">*</span></span>
          <input
            v-model="promotionForm.titulo"
            class="modal-input"
            required
            maxlength="120"
            placeholder="Ej: Promo Combo Doble Cheddar"
          />
        </label>

        <div class="form-row-2">
          <label class="modal-label">
            <span>Precio Promocional ($) <span class="required">*</span></span>
            <input
              v-model.number="promotionForm.precio_promocional"
              class="modal-input"
              type="number"
              min="1"
              required
              placeholder="Ej: 2990"
            />
          </label>
          <label class="modal-label">
            <span>Estado</span>
            <select v-model="promotionForm.activo" class="modal-input">
              <option :value="true">Activa (Visible)</option>
              <option :value="false">Inactiva (Oculta)</option>
            </select>
          </label>
        </div>

        <div class="form-row-2">
          <label class="modal-label">
            <span>Fecha Inicio <span class="required">*</span></span>
            <input
              v-model="promotionForm.fecha_inicio"
              class="modal-input"
              type="date"
              required
            />
          </label>
          <label class="modal-label">
            <span>Fecha Término <span class="required">*</span></span>
            <input
              v-model="promotionForm.fecha_fin"
              class="modal-input"
              type="date"
              required
              :min="promotionForm.fecha_inicio"
            />
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="$emit('close')">Cancelar</button>
          <button type="submit" class="btn-save">
            <Check :size="16" />
            <span>{{ isEditing ? 'Guardar Cambios' : 'Crear Promoción' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { BadgePercent, Check, X } from 'lucide-vue-next';
import { formatCLP } from '@/utils/format';

defineProps<{
  isOpen: boolean;
  isEditing: boolean;
  promotionForm: {
    id: number;
    id_producto: string | number;
    id_tamaño: number | null;
    titulo: string;
    descripcion: string;
    precio_promocional: number;
    fecha_inicio: string;
    fecha_fin: string;
    activo: boolean;
  };
  products: any[];
  selectedProductSizes: any[];
}>();

defineEmits<{
  close: [];
  submit: [];
}>();
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
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

.modal-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.required { color: #dc2626; }

.modal-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.65rem 0.85rem;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  outline: none;
}

.modal-input:focus {
  border-color: var(--DC-orange, #e28743);
}

.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
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

