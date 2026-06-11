import api from './api';

export default {
    getUsers() {
        return api.get('/usuarios_dicreme');
    },
    getUserById(id: number) {
        return api.get(`/usuarios_dicreme/${id}`);
    }
}
