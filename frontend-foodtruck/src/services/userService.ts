import api from './api';

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
    }
}
