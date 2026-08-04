import api from './api';

export default {
    getCategories () {
        return api.get('/categorias');
    },
    getPublicCategories () {
        return api.get('/public/categorias');
    }
}