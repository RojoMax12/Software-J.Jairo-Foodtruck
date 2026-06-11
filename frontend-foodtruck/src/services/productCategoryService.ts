import api from './api';

export default { 
    getCategory(){
        return api.get('/categorias');
    },

    getCategoryById(id: number){
        return api.get(`/categorias/${id}`);
    },

    updateCategory(id: number, data: unknown){
        return api.put(`/categorias/${id}`, data);
    },

    deleteCategory(id: number){
        return api.delete(`/categorias/${id}`);
    },

    createCategory(data: unknown){
        return api.post('/categorias', data);
    }
}