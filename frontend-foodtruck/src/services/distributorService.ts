import api from './api';

export default {
    getDistributors() {
        return api.get('/usuarios_distribuidores');
    },
    getDistributorById(id: number) {
        return api.get(`/usuarios_distribuidores/${id}`);
    }
}
