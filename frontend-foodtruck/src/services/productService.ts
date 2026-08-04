import api from './api';

export default {
    getProducts(){
        return api.get('/productos');
    },

    getProductById(id: number | string){
        return api.get(`/productos/${id}`);
    },

    updateProduct(id: number | string, data: unknown){
        return api.put(`/productos/${id}`, data);
    },

    deleteProduct(id: number | string){
        return api.delete(`/productos/${id}`);
    },

    createProduct(data: unknown){
        return api.post('/productos', data);
    },

    // RUTAS PÚBLICAS PARA QR Y CLIENTES
    getPublicProducts(){
        return api.get('/public/productos');
    },

    getPublicProductById(id: number | string){
        return api.get(`/public/productos/${id}`);
    }
}