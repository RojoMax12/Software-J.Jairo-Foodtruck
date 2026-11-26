import api from './api';

export default {
    getProducts(params?: { summary?: 0 | 1 }) {
        return api.get('/productos', { params });
    },

    getProductById(id: number | string) {
        return api.get(`/productos/${id}`);
    },

    updateProduct(id: number | string, data: unknown) {
        return api.put(`/productos/${id}`, data);
    },

    deleteProduct(id: number | string) {
        return api.delete(`/productos/${id}`);
    },

    createProduct(data: unknown) {
        return api.post('/productos', data);
    },

    uploadProductImage(id: number | string, file: File) {
        const formData = new FormData();
        formData.append('imagen', file);
        return api.post(`/productos/${id}/imagen`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
    },

    // RUTAS PÚBLICAS PARA QR Y CLIENTES
    getPublicProducts(params?: { summary?: 0 | 1 }) {
        return api.get('/public/productos', { params });
    },

    getPublicProductById(id: number | string) {
        return api.get(`/public/productos/${id}`);
    }
};
