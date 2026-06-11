<script setup lang="ts">
import { X, Lock } from 'lucide-vue-next';

defineProps<{ isOpen: boolean }>();
const emit = defineEmits(['close', 'confirm']);
</script>

<template>
  <Transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click="emit('close')">
      <div class="prompt-card" @click.stop>
        
        <button class="close-btn" @click="emit('close')">
          <X :size="18" color="#322c44" />
        </button>

        <div class="icon-container">
          <Lock :size="32" color="#e4869f" />
        </div>

        <h3 class="modal-title">Inicio de sesión requerido</h3>
        <p class="modal-text">
          Debes iniciar sesión con tu cuenta de distribuidor para continuar con la cotización.
        </p>

        <div class="actions-row">
          <button class="btn-cancel" @click="emit('close')">
            Cancelar
          </button>
          <button class="btn-confirm" @click="emit('confirm')">
            Iniciar sesión
          </button>
        </div>

      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.prompt-card {
  background-color: white;
  padding: 30px 24px;
  border-radius: 20px;
  width: 100%;
  max-width: 350px;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  cursor: pointer;
}

.icon-container {
  background-color: var(--DC-bg-gray);
  padding: 15px;
  border-radius: 50%;
  margin-bottom: 15px;
}

.modal-title {
  font-size: 1.15rem;
  font-weight: bold;
  color: #322c44;
  margin: 0 0 10px 0;
}

.modal-text {
  font-size: 0.9rem;
  color: var(--DC-text-gray);
  text-align: center;
  line-height: 1.4;
  margin: 0 0 25px 0;
}

.actions-row {
  display: flex;
  gap: 12px;
  width: 100%;
}

.btn-cancel {
  flex: 1;
  background-color: var(--DC-bg-gray);
  color: var(--DC-text-gray);
  border: none;
  padding: 11px;
  border-radius: 10px;
  font-weight: bold;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-confirm {
  flex: 1;
  background-color: var(--DC-pink);
  color: white;
  border: none;
  padding: 11px;
  border-radius: 10px;
  font-weight: bold;
  cursor: pointer;
  font-size: 0.9rem;
  transition: background-color 0.2s;
}

.btn-confirm:hover {
  background-color: var(--DC-pink);
}

/* Transiciones */
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>