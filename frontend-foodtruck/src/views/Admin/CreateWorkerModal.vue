<template>
    <Transition name="fade">
        <div v-if="isOpen" class="modal-overlay">
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title">
                        <UserPlus :size="22" />
                        <h2>Nuevo trabajador</h2>
                    </div>

                    <button type="button" class="close-button" @click="closeModal">
                        ×
                    </button>
                </div>

                <div class="modal-body">
                    <form class="worker-form">

                        <!-- Nombre completo -->
                        <div class="form-group">
                            <label for="name">
                                Nombre completo
                            </label>

                            <input
                                id="name"
                                v-model="fullName"
                                type="text"
                                placeholder="Ej. Juan Pérez"
                                @blur="touchedName = true"
                            >

                            <p 
                                v-if="touchedName && fullName.trim() === ''"
                                class="field-error"
                            >
                                El nombre es obligatorio
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                Correo electrónico
                            </label>

                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                placeholder="Ej. juan@test.cl"
                                @blur="touchedEmail = true"
                            >

                            <p
                                v-if="touchedEmail && email.trim() === ''"
                                class="field-error"
                            >
                                El correo electrónico es obligatorio
                            </p>

                            <p 
                                v-if="email.length > 0 && !isEmailValid"
                                class="field-error"
                            >
                                El correo electrónico no es válido
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="role">
                                Rol
                            </label>

                            <div class="select-wrapper">
                                <select id="role" v-model="role" @blur="touchedRole = true">
                                    <option disabled :value="null">
                                        Seleccione un rol
                                    </option>

                                    <option :value="3">
                                        Trabajador
                                    </option>

                                    <option :value="1">
                                        Administrador
                                    </option>
                                </select>
                                <ChevronDown :size="18" class="select-icon" />
                            </div>

                            <p
                                v-if="touchedRole && role === null"
                                class="field-error"
                            >
                                Debes seleccionar un rol
                            </p>
                        </div>

                        <div class="form-group">
                            <label for="password">
                                Contraseña
                            </label>

                            <div v-if="password.length > 0" class="password-requirements">
                                <p :class="{ valid: passwordRequirements.minLength }">
                                    {{ passwordRequirements.minLength ? '✓' : '✗' }}
                                    Mínimo 8 caracteres
                                </p>

                                <p :class="{ valid: passwordRequirements.upperCase }">
                                    {{ passwordRequirements.upperCase ? '✓' : '✗' }}
                                    Una letra mayúscula
                                </p>

                                <p :class="{ valid: passwordRequirements.lowerCase }">
                                    {{ passwordRequirements.lowerCase ? '✓' : '✗' }}
                                    Una letra minúscula
                                </p>

                                <p :class="{ valid: passwordRequirements.number }">
                                    {{ passwordRequirements.number ? '✓' : '✗' }}
                                    Un número
                                </p>
                            </div>

                            <input 
                                id="password" 
                                v-model="password" 
                                type="password" 
                                placeholder="Ingrese una contraseña"
                            />
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-secondary" @click="closeModal">
                        Cancelar
                    </button>

                    <button
                        class="btn-primary"
                        :disabled="!isFormValid"
                        @click="createWorker"
                    >
                        Crear trabajador
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useNotification } from '@/composables/useNotification'
import { ChevronDown, UserPlus } from 'lucide-vue-next'
import userService from '@/services/userService'
import type { CreateWorkerRequest } from '@/services/userService'

const { notify } = useNotification()

const touchedName = ref(false)
const touchedEmail = ref(false)
const touchedRole = ref(false)

const fullName = ref('')
const email = ref('')
const role = ref<1 | 3 | null>(null)
const password = ref('')

const props = defineProps<{
    isOpen: boolean
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'workerCreated'): void
}>()

const isFormValid = computed(() => {
    return (
        fullName.value.trim() !== '' &&
        isEmailValid.value &&
        role.value !== null &&
        isPasswordValid.value
    )
})

const isEmailValid = computed(() => {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)
})

const passwordRequirements = computed(() => ({
    minLength: password.value.length >= 8,
    upperCase: /[A-Z]/.test(password.value),
    lowerCase: /[a-z]/.test(password.value),
    number: /[0-9]/.test(password.value),
}))

const isPasswordValid = computed(() => {
    return (
        passwordRequirements.value.minLength &&
        passwordRequirements.value.upperCase &&
        passwordRequirements.value.lowerCase &&
        passwordRequirements.value.number
    )
})

const createWorker = async () => {
    if (role.value === null) {
        return
    }

    try {
        const worker: CreateWorkerRequest = {
            id_rol: role.value,
            nombre: fullName.value,
            correo: email.value,
            estado: true,
            contrasena: password.value
        }

        await userService.createUser(worker)

        notify(
            'El trabajador fue creado correctamente.',
            'success'
        )

        closeModal()

        emit('workerCreated')

    } catch (error: any) {
        if (error.response?.status === 409) {
            notify(
                error.response.data.message,
                'error'
            )
        } else {
            notify(
                'No se pudo crear el trabajador.',
                'error'
            )
        }
    }
}

const resetForm = () => {
    fullName.value = ''
    email.value = ''
    role.value = null
    password.value = ''

    touchedName.value = false
    touchedEmail.value = false
    touchedRole.value = false
}

const closeModal = () => {
    resetForm()
    emit('close')
}
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
    overflow:hidden;
}

/* 1. Contenedor interno */
.modal {
    width: 500px;
    max-width: 90vw;
    max-height: 90vh;

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow: 0 20px 50px rgba(0, 0, 0, .2);

    display: flex;
    flex-direction: column;
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
    overflow-y: auto;
    overscroll-behavior: contain;
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

.field-error {
    margin: 5px 0 0;
    font-size: 0.8rem;
    font-weight: 600;
    color: #dc3545;
}

.password-requirements {
    margin-top: 8px;
}

.password-requirements p {
    margin: 3px 0;
    font-size: 0.78rem;
    color: #dc3545;
}

.password-requirements p.valid {
    color: #198754;
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
    flex-shrink: 0;
    padding: 16px 24px;
    border-top: 2px solid #ececec;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
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

@media (max-width: 600px) {
    .modal-container {
        width: 95vw;
        max-height: 90vh;
        margin: 10px;
    }

    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 14px 16px;
    }

    .modal-footer {
        flex-direction: column-reverse;
        gap: 8px;
    }

    .modal-footer button {
        width: 100%;
        justify-content: center;
    }
}
</style>