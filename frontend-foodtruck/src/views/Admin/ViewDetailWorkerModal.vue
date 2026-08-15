<template>
    <div v-if="show && worker" class="modal-overlay" @click.self="closeModal">
        <div class="modal-container">

            <!-- Header -->
            <div class="modal-header">
                <div class="modal-title">
                    <Eye :size="22" />
                    <h2>Detalle del trabajador</h2>
                </div>

                <button
                    class="close-button"
                    @click="closeModal"
                >
                    ×
                </button>
            </div>

            <!-- Contenido -->
            <div class="modal-body">

                <div class="worker-info">

                    <div class="info-item">
                        <span class="info-label">ID</span>
                        <span class="info-value">
                            {{ worker.id_usuario }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Nombre</span>
                        <span class="info-value">
                            {{ worker.nombre }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Correo</span>
                        <span class="info-value">
                            {{ worker.correo }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Rol</span>
                        <span class="info-value">
                            {{ getRoleName(worker.id_rol) }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Estado</span>

                        <span
                            class="status"
                            :class="worker.estado ? 'active' : 'inactive'"
                        >
                            {{ worker.estado ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Fecha de creación</span>
                        <span class="info-value">
                            {{ formatDate(worker.created_at) }}
                        </span>
                    </div>

                    <div class="info-item">
                        <span class="info-label">Última actualización</span>
                        <span class="info-value">
                            {{ formatDate(worker.updated_at) }}
                        </span>
                    </div>

                </div>

            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button
                    class="btn-secondary"
                    @click="closeModal"
                >
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</template>

<script setup lang="ts">
import type { Worker } from '@/services/userService';
import { Eye } from 'lucide-vue-next';

defineProps<{
    show: boolean
    worker: Worker | null
}>()

const emit = defineEmits<{
    close: []
}>()

const closeModal = () => {
    emit('close')
}

const formatDate = (date: string) => {
    return new Date(date).toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const getRoleName = (idRol: number) => {
    switch (idRol) {
        case 1:
            return 'Administrador'
        case 2:
            return 'Cliente'
        case 3:
            return 'Trabajador'
        default:
            return 'Desconocido'
    }
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);

    display: flex;
    align-items: center;
    justify-content: center;

    z-index: 1000;
}

.modal-container {
    width: 500px;
    max-width: 90%;

    background: white;
    border-radius: 12px;

    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;

    padding: 20px;
    border-bottom: 1px solid #eee;
}

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

.close-button {
    border: none;
    background: transparent;

    font-size: 24px;
    cursor: pointer;
}

.modal-body {
    padding: 20px;
}

.worker-info {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-size: 13px;
    color: #777;
}

.info-value {
    font-size: 15px;
    font-weight: 500;
}

.status {
    width: fit-content;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
}

.status.active {
    background: #dcfce7;
    color: #166534;
}

.status.inactive {
    background: #fee2e2;
    color: #991b1b;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;

    padding: 16px 20px;
    border-top: 1px solid #eee;
}

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
</style>