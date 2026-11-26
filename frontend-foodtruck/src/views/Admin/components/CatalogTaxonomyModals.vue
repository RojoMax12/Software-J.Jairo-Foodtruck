<template>
  <!-- MODAL CATEGORÍA -->
  <div v-if="isCategoryOpen" class="modal-backdrop" @click.self="$emit('close-category')">
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-header-title">
          <div class="header-icon-pill"><FolderTree :size="18" /></div>
          <div>
            <h3>{{ isEditingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
            <p class="modal-header-desc">Organiza tu carta gastronómica por secciones</p>
          </div>
        </div>
        <button type="button" class="close-btn" @click="$emit('close-category')"><X :size="18" /></button>
      </div>

      <form class="modal-body" @submit.prevent="$emit('submit-category')">
        <label class="modal-label">
          <span>Nombre de la Categoría <span class="required">*</span></span>
          <input
            v-model="categoryForm.nombre_categoria"
            type="text"
            required
            placeholder="Ej: Hamburguesas Smash, Chorrillanas, Bebidas..."
            class="modal-input"
          />
        </label>

        <label class="modal-label">
          <span>Descripción</span>
          <textarea
            v-model="categoryForm.descripcion_categoria"
            rows="3"
            placeholder="Breve detalle de los productos incluidos en esta categoría..."
            class="modal-input"
          ></textarea>
        </label>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="$emit('close-category')">Cancelar</button>
          <button type="submit" class="btn-save">
            <Check :size="16" />
            <span>{{ isEditingCategory ? 'Guardar Cambios' : 'Crear Categoría' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL TAMAÑO -->
  <div v-if="isSizeOpen" class="modal-backdrop" @click.self="$emit('close-size')">
    <div class="modal-card">
      <div class="modal-header">
        <div class="modal-header-title">
          <div class="header-icon-pill"><Tag :size="18" /></div>
          <div>
            <h3>{{ isEditingSize ? 'Editar Tamaño' : 'Nuevo Tamaño' }}</h3>
            <p class="modal-header-desc">Define las porciones o formatos disponibles en la carta</p>
          </div>
        </div>
        <button type="button" class="close-btn" @click="$emit('close-size')"><X :size="18" /></button>
      </div>

      <form class="modal-body" @submit.prevent="$emit('submit-size')">
        <label class="modal-label">
          <span>Nombre del Tamaño <span class="required">*</span></span>
          <input
            v-model="sizeForm.nombre"
            type="text"
            required
            placeholder="Ej: Normal, Doble, XL, Familiar, Individual..."
            class="modal-input"
          />
        </label>

        <label class="modal-label">
          <span>Descripción / Nota</span>
          <input
            v-model="sizeForm.descripcion"
            type="text"
            placeholder="Ej: Porción estándar individual con papas"
            class="modal-input"
          />
        </label>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="$emit('close-size')">Cancelar</button>
          <button type="submit" class="btn-save">
            <Check :size="16" />
            <span>{{ isEditingSize ? 'Guardar Cambios' : 'Crear Tamaño' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Check, FolderTree, Tag, X } from 'lucide-vue-next';

defineProps<{
  isCategoryOpen: boolean;
  isEditingCategory: boolean;
  categoryForm: {
    id_categoria: number;
    nombre_categoria: string;
    descripcion_categoria: string;
  };
  isSizeOpen: boolean;
  isEditingSize: boolean;
  sizeForm: {
    id_tamaño: number;
    nombre: string;
    descripcion: string;
  };
}>();

defineEmits<{
  'close-category': [];
  'submit-category': [];
  'close-size': [];
  'submit-size': [];
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

