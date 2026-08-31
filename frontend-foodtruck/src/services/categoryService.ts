import api from './api';

export default {
    getCategories () {
        return api.get('/categorias');
    },
    getPublicCategories () {
        return api.get('/public/categorias');
    },
    createCategory (data: unknown) {
        return api.post('/categorias', data);
    },
    updateCategory (id: number | string, data: unknown) {
        return api.put(`/categorias/${id}`, data);
    },
    deleteCategory (id: number | string) {
        return api.delete(`/categorias/${id}`);
    }
}