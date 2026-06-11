import { create } from 'axios';
import api from './api';

export default {
    getProducts(){
        return api.get('/productos');
    },

    getProductById(id: number){
        return api.get(`/productos/${id}`);
    },

    updateProduct(id: number, data: unknown){
        return api.put(`/productos/${id}`, data);
    },

    deleteProduct(id: number){
        return api.delete(`/productos/${id}`);
    },

    createProduct(data: unknown){
        return api.post('/productos', data);

    },

}