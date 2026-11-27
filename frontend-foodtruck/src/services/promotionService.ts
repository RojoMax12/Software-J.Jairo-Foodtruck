import api from './api';

export default {
    getPromotions() {
        return api.get('/promociones');
    },
    getPublicPromotions() {
        return api.get('/public/promociones');
    },
    createPromotion(data: unknown) {
        return api.post('/promociones', data);
    },
    createPromotionsBatch(data: { reemplazar_conflictos?: boolean; items: unknown[] }) {
        return api.post('/promociones/batch', data);
    },
    updatePromotion(id: number | string, data: unknown) {
        return api.put(`/promociones/${id}`, data);
    },
    deletePromotion(id: number | string) {
        return api.delete(`/promociones/${id}`);
    }
};