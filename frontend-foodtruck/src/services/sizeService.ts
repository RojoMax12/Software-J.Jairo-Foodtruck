import api from './api';

export const sizeService = {
    getSizes () {
        return api.get('/tamaños');
    },
    createSize (data: unknown) {
        return api.post('/tamaños', data);
    },
    updateSize (id: number | string, data: unknown) {
        return api.put(`/tamaños/${id}`, data);
    },
    deleteSize (id: number | string) {
        return api.delete(`/tamaños/${id}`);
    }
};

export default sizeService;

