<template>
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="modal-overlay"
        >
            <div class="modal">

                <!-- Header -->
                <div class="modal-header">
                    <div
                        class="modal-title"
                        :class="{
                            activating: isActivating,
                            deactivating: !isActivating
                        }"
                    >

                        <UserCheck
                            v-if="isActivating"
                            :size="22"
                        />

                        <UserX
                            v-else
                            :size="22"
                        />

                        <h2>
                            {{ isActivating
                                ? 'Activar trabajador'
                                : 'Desactivar trabajador'
                            }}
                        </h2>

                    </div>

                    <button
                        class="close-button"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">

                    <p>
                        {{
                            isActivating
                                ? '¿Estás seguro de que deseas activar este trabajador?'
                                : '¿Estás seguro de que deseas desactivar este trabajador?'
                        }}
                    </p>

                    <p class="modal-warning">
                        {{
                            isActivating
                                ? 'El trabajador podrá volver a utilizar el sistema.'
                                : 'El trabajador no podrá utilizar el sistema mientras esté inactivo.'
                        }}
                    </p>

                </div>

                <!-- Footer -->
                <div class="modal-footer">

                    <button
                        class="btn-secondary"
                        @click="emit('close')"
                    >
                        Cancelar
                    </button>

                    <button
                        class="btn-primary"
                        :class="{
                            activating: isActivating,
                            deactivating: !isActivating
                        }"
                        @click="emit('confirm')"
                    >
                        {{ isActivating ? 'Activar' : 'Desactivar' }}
                    </button>

                </div>

            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { UserCheck, UserX } from 'lucide-vue-next'

interface Props {
    isOpen: boolean
    isActivating: boolean
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'confirm'): void
}>()
</script>

<style scoped>

/* Overlay */

.modal-overlay {
    position: fixed;
    inset: 0;

    display: flex;
    justify-content: center;
    align-items: center;

    background: rgba(0, 0, 0, .45);

    z-index: 1100;
}

/* Modal */

.modal {
    width: 100%;
    max-width: 480px;

    background: white;

    border-radius: 16px;

    overflow: hidden;

    box-shadow: 0 18px 45px rgba(0, 0, 0, .18);
}

/* Header */

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 20px 24px;

    border-bottom: 2px solid #eeedee;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-title.activating svg {
    color: #198754;
}

.modal-title.deactivating svg {
    color: #dc3545;
}

.modal-header h2 {
    margin: 0;

    font-size: 1.2rem;
    font-weight: 800;

    color: var(--DC-gray);
}

/* Close */

.close-button {
    width: 36px;
    height: 36px;

    border: none;
    border-radius: 10px;

    background: transparent;

    font-size: 1.4rem;

    cursor: pointer;

    transition: .2s;
}

.close-button:hover {
    background: var(--DC-bg-gray);
}

/* Body */

.modal-body {
    padding: 24px;
    overscroll-behavior: contain;
}

.modal-body p {
    margin: 0;
    color: var(--DC-gray);
    line-height: 1.5;
}

.modal-warning {
    margin-top: 12px !important;

    font-size: .9rem;
    color: #6b7280 !important;
}

/* Footer */

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;

    padding: 20px 24px;

    border-top: 2px solid #eeedee;
}

/* Buttons */

.btn-secondary {
    padding: 12px 18px;

    border: 2px solid #eeedee;
    border-radius: 10px;

    background: white;
    color: var(--DC-gray);

    font-weight: 700;

    cursor: pointer;

    transition: .2s;
}

.btn-secondary:hover {
    border-color: var(--DC-brown);
}

.btn-primary {
    display: flex;
    align-items: center;
    justify-content: center;

    padding: 12px 20px;

    border: none;
    border-radius: 12px;

    color: white;

    font-size: .95rem;
    font-weight: 600;

    cursor: pointer;

    transition:
        background .2s,
        transform .15s,
        box-shadow .2s;
}

/* Activar */

.btn-primary.activating {
    background: #198754;
}

.btn-primary.activating:hover {
    background: #157347;

    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(25, 135, 84, .25);
}

/* Desactivar */

.btn-primary.deactivating {
    background: #dc3545;
}

.btn-primary.deactivating:hover {
    background: #bb2d3b;

    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(220, 53, 69, .25);
}

.btn-primary:active {
    transform: translateY(0);
}

/* Animación */

.fade-enter-active,
.fade-leave-active {
    transition: .2s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

</style>