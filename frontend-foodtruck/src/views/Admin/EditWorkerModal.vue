<template>
    <Transition name="fade">
        <div
            v-if="isOpen && worker"
            class="modal-overlay"
        >
            <div class="modal">
                <div class="modal-header">
                    <div class="modal-title">

                        <Edit3 :size="22" />

                        <h2>Editar trabajador</h2>
                        
                    </div>

                    <button
                        class="close-button"
                        @click="emit('close')"
                    >
                        ×
                    </button>
                </div>

                <div class="modal-body">
                    <form class="worker-form">

                        <!-- Nombre -->

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

                        <!-- Correo -->

                        <div class="form-group">
                            <label for="email">
                                Correo electrónico
                            </label>

                            <input
                                id="email"
                                v-model="email"
                                type="email"
                                placeholder="ejemplo@correo.com"
                                @blur="touchedEmail = true"
                            >

                            <p
                                v-if="touchedEmail && email.trim() === ''"
                                class="field-error"
                            >
                                El correo electrónico es obligatorio
                            </p>

                            <p
                                v-else-if="email.length > 0 && !isEmailValid"
                                class="field-error"
                            >
                                El correo electrónico no es válido
                            </p>
                        </div>

                        <!-- Rol -->

                        <div class="form-group">
                            <label for="role">
                                Rol
                            </label>

                            <div class="select-wrapper">

                                <select
                                    id="role"
                                    v-model="roleId"
                                    @blur="touchedRole = true"
                                >
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

                                <ChevronDown
                                    :size="18"
                                    class="select-icon"
                                />

                            </div>

                            <p
                                v-if="touchedRole && roleId === null"
                                class="field-error"
                            >
                                Debes seleccionar un rol
                            </p>
                        </div>

                        <!-- Contraseña -->

                        <div class="form-group">
                            <label for="password">
                                Nueva contraseña (opcional)
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
                                placeholder="Dejar vacío para mantener la actual"
                            >
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <button
                        class="btn-secondary"
                        @click="emit('close')"
                    >
                        Cancelar
                    </button>

                    <button 
                        class="btn-primary"
                        :disabled="!isFormValid || !hasChanges"
                        @click="saveChanges"
                    >
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useNotification } from '@/composables/useNotification'
import type { Worker, UpdateWorkerRequest } from '@/services/userService'
import { ChevronDown, Edit3 } from 'lucide-vue-next'

const { notify } = useNotification()

const fullName = ref('')
const email = ref('')
const roleId = ref<1 | 3 | null>(null)
const password = ref('')

const touchedName = ref(false)
const touchedEmail = ref(false)
const touchedRole = ref(false)
const touchedPassword = ref(false)

interface Props {
    isOpen: boolean
    worker: Worker | null
}

const props = defineProps<Props>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'save', data: UpdateWorkerRequest): void
}>()

watch(() => props.worker,
    (worker) => {
        if (!worker) return

        fullName.value = worker.nombre
        email.value = worker.correo
        roleId.value = worker.id_rol
        password.value = ''

        touchedName.value = false
        touchedEmail.value = false
        touchedRole.value = false
        touchedPassword.value = false
    },
    { immediate: true }
)

const hasChanges = computed(() => {
    if (!props.worker) return false

    return (
        fullName.value.trim() !== props.worker.nombre ||
        email.value.trim() !== props.worker.correo ||
        roleId.value !== props.worker.id_rol ||
        password.value.trim() !== ''
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
    if (password.value === '') {
        return true
    }

    return (
        passwordRequirements.value.minLength &&
        passwordRequirements.value.upperCase &&
        passwordRequirements.value.lowerCase &&
        passwordRequirements.value.number
    )
})

const isFormValid = computed(() => {
    return (
        fullName.value.trim() !== '' &&
        isEmailValid.value &&
        roleId.value !== null &&
        isPasswordValid.value
    )
})

const saveChanges = () => {
    const payload: UpdateWorkerRequest = {
        nombre: fullName.value.trim(),
        correo: email.value.trim(),
        id_rol: roleId.value!
    }

    if (password.value.trim() !== '') {
        payload.contrasena = password.value.trim()
    }

    emit('save', payload)
}

</script>

<style scoped>

/* Fondo */

.modal-overlay{

    position:fixed;
    inset:0;

    display:flex;
    justify-content:center;
    align-items:center;

    background:rgba(0,0,0,.45);

    z-index:1000;
}

/* Modal */

.modal{

    width:100%;
    max-width:520px;
    max-height: 90vh;

    display:flex;
    flex-direction:column;

    background:white;

    border-radius:16px;

    overflow:hidden;

    box-shadow:0 18px 45px rgba(0,0,0,.18);

}

/* Header */
.modal-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 24px;
    border-bottom:2px solid #eeedee;
}

.modal-header h2{
    margin:0;
    font-size:1.2rem;
    font-weight:800;
    color:var(--DC-gray);
}

.modal-title{
    display:flex;
    align-items:center;
    gap:10px;
}

.modal-title svg{
    color:#4f46e5;
    flex-shrink:0;
}

.close-button{
    width:36px;
    height:36px;
    border:none;
    border-radius:10px;
    background:transparent;
    font-size:1.4rem;
    cursor:pointer;
    transition:.2s;
}

.close-button:hover{
    background:var(--DC-bg-gray);
}

/* Body */

.modal-body{
    padding:24px;

    flex:1;
    min-height:0;

    overflow-y:auto;
    overscroll-behavior: contain;
}
.modal-body p{
    margin-top:0;
}

/* 3.1 Formulario de creacion */
.worker-form{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.form-group label{
    font-size:.95rem;
    font-weight:600;
    color:#374151;
}

.form-group input,
.form-group select{
    padding:12px 14px;
    border:1px solid #d1d5db;
    border-radius:12px;
    font-size:.95rem;
    transition:.2s;
    outline:none;
}

.form-group input:focus,
.form-group select:focus{
    border-color:#4f46e5;
    box-shadow:0 0 0 3px rgba(79,70,229,.15);
}

/* 3.1.1 Boton de despliegue roles */
.select-wrapper{
    position:relative;
}

.select-wrapper select{
    width:100%;
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    padding:12px 42px 12px 14px;
    cursor:pointer;
}

.select-icon{
    position:absolute;
    right:14px;
    top:50%;
    transform:translateY(-50%);
    pointer-events:none;
    color:#6b7280;
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

/* Footer */

.modal-footer{
    display:flex;
    justify-content:flex-end;
    gap:12px;
    padding:20px 24px;
    border-top:2px solid #eeedee;
}

/* Botones */

.btn-secondary{
    padding:12px 18px;
    border:2px solid #eeedee;
    border-radius:10px;
    background:white;
    color:var(--DC-gray);
    font-weight:700;
    cursor:pointer;
    transition:.2s;
}

.btn-secondary:hover{
    border-color:var(--DC-brown);
}

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
.fade-leave-active{
    transition:.2s;
}

.fade-enter-from,
.fade-leave-to{
    opacity:0;
}

@media (max-width: 600px) {
    .modal {
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