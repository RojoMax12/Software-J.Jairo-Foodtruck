import api from './api';

export interface Worker {
    id_usuario: number
    id_rol: 1 | 3
    nombre: string
    correo: string
    estado: boolean
    created_at: string
    updated_at: string
}

export interface CreateWorkerRequest {
    id_rol: 1 | 3
    nombre: string
    correo: string
    estado: boolean
    contrasena: string
}

export interface UpdateWorkerRequest {
    id_rol?: 1 | 3
    nombre?: string
    correo?: string
    estado?: boolean
    contrasena?: string
}

export default {
    getUsers() {
        return api.get('/usuarios');
    },
    getUserById(id: number | string) {
        return api.get(`/usuarios/${id}`);
    },
    createUser(data: unknown) {
        return api.post('/usuarios', data);
    },
    updateUser(id: number | string, data: unknown) {
        return api.put(`/usuarios/${id}`, data);
    },
    deleteUser(id: number | string) {
        return api.delete(`/usuarios/${id}`);
    },
    getWorkers() {
        return api.get('/usuarios-administrativos');
    }
};