import api from './api';

export default {
    getFormats() {
        return api.get('/formatos');
    },
    getFormatById(id: number) {
        return api.get(`/formatos/${id}`);
    }
}
