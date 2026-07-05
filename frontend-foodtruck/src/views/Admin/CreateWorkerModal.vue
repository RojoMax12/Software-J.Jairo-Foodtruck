<template>

    <Transition name="fade">

        <div v-if="isOpen" class="modal-overlay" @click.self="emit('close')">

            <div class="modal">

                <div class="modal-header">

                    <div class="modal-title">

                        <UserPlus :size="22" />

                        <h2>Nuevo trabajador</h2>

                    </div>

                    <button class="close-button" @click="emit('close')">
                        ×
                    </button>

                </div>

                <div class="modal-body">

                    <form class="worker-form">

                        <div class="form-group">

                            <label for="name">
                                Nombre completo
                            </label>

                            <input v-model="fullName" type="text" placeholder="Ej. Juan Pérez">

                        </div>

                        <div class="form-group">

                            <label for="role">
                                Rol
                            </label>

                            <div class="select-wrapper">

                                <select v-model="role">

                                    <option disabled value="">
                                        Seleccione un rol
                                    </option>

                                    <option value="TRABAJADOR">
                                        Trabajador
                                    </option>

                                    <option value="ADMINISTRADOR">
                                        Administrador
                                    </option>

                                </select>

                                <ChevronDown :size="18" class="select-icon" />
                            </div>

                        </div>

                        <div class="form-group">

                            <label for="password">
                                Contraseña
                            </label>

                            <input v-model="password" type="password" placeholder="Ingrese una contraseña">

                        </div>

                    </form>

                </div>

                <div class="modal-footer">

                    <button class="btn-secondary" @click="emit('close')">
                        Cancelar
                    </button>

                    <button class="btn-primary" :disabled="!isFormValid">
                        Crear trabajador
                    </button>

                </div>

            </div>

        </div>

    </Transition>

</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { ChevronDown, UserPlus } from 'lucide-vue-next'

const fullName = ref('')
const role = ref('')
const password = ref('')

defineProps<{
    isOpen: boolean
}>()

const emit = defineEmits<{
    (e: 'close'): void
}>()

const isFormValid = computed(() => {

    return (
        fullName.value.trim() !== '' &&
        role.value !== '' &&
        password.value.trim() !== ''
    )

})

</script>

<style scoped>
/* 0. Contenedor principal */
.modal-overlay {
    position: fixed;
    inset: 0;

    display: flex;
    justify-content: center;
    align-items: center;

    background: rgba(0, 0, 0, .45);

    z-index: 1000;
}

/* 1. Contenedor interno */
.modal {
    width: 500px;
    max-width: 90vw;

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow: 0 20px 50px rgba(0, 0, 0, .2);
}

/* 2. Encabezado modal */
.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 2px solid #ececec;
}

/* 2.1 Formato titulo */
.modal-header h2 {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 800;
    color: var(--DC-gray);
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-title svg {
    color: #4f46e5;
    flex-shrink: 0;
}

/* 2.2 Botón de cierre */
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

/* 3. Cuerpo modal */
.modal-body {
    padding: 24px;
    min-height: 150px;
}

/* 3.1 Formulario de creacion */
.worker-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-size: .95rem;
    font-weight: 600;
    color: #374151;
}

.form-group input,
.form-group select {
    padding: 12px 14px;
    border: 1px solid #d1d5db;
    border-radius: 12px;
    font-size: .95rem;
    transition: .2s;
    outline: none;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, .15);
}

/* 3.1.1 Boton de despliegue roles */
.select-wrapper {
    position: relative;
}

.select-wrapper select {
    width: 100%;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    padding: 12px 42px 12px 14px;
    cursor: pointer;
}

.select-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6b7280;
}

/* 4. Pie modal */
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding: 20px 24px;
    border-top: 2px solid #ececec;
}

/* 4.1 Boton cancelar */
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

/* 4.2 Boton crear trabajador */
.btn-primary {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 12px;
    background: #4f46e5;
    color: white;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    transition:
        background .2s,
        transform .15s,
        box-shadow .2s;
}

.btn-primary:hover:not(:disabled) {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px rgba(79, 70, 229, .25);
}

.btn-primary:active:not(:disabled) {
    transform: translateY(0);
}

.btn-primary:disabled {
    background: #c7d2fe;
    color: #ffffff;
    cursor: not-allowed;
    box-shadow: none;
    transform: none;
    opacity: .8;
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