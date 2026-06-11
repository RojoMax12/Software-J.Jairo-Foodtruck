import api from './api';

export default {
    getStatuses() {
        return api.get('/estado_cotizacion');
    },
    getStatusById(id: number) {
        return api.get(`/estado_cotizacion/${id}`);
    }
}
