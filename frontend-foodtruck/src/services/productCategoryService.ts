import api from './api';

export default { 
    getCategory(){
        return api.get('/categorias');
    },

    getCategoryById(id: number | string){
        return api.get(`/categorias/${id}`);
    },

    updateCategory(id: number | string, data: unknown){
        return api.put(`/categorias/${id}`, data);
    },

    deleteCategory(id: number | string){
        return api.delete(`/categorias/${id}`);
    },

    createCategory(data: unknown){
        return api.post('/categorias', data);
    },

    // RUTAS PÚBLICAS PARA QR Y CLIENTES
    getPublicCategories(){
        return api.get('/public/categorias');
    }
}