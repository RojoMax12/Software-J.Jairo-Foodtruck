import api from './api';

export default { 

    getQuotes() {
        return api.get('/pedidos');
    },

    getQuotesByDistributor(idDistribuidor: number | string) {
        return api.get(`/pedidos/usuario/${idDistribuidor}`).catch(() => api.get('/pedidos'));
    },
    
    getQuoteById(id: number | string) {
        return api.get(`/public/pedidos/${id}`).catch(() => api.get(`/pedidos/${id}`));
    },

    getQuoteDetails(id: number | string) {
        return api.get(`/public/pedidos/${id}`).catch(() => api.get(`/pedidos/${id}`));
    },

    updateQuote(id: number | string, data: unknown) {
        return api.put(`/pedidos/${id}`, data);
    },

    deleteQuote(id: number | string) {
        return api.delete(`/pedidos/${id}`);
    },

    createQuote(data: unknown) {
        return api.post('/public/pedidos', data).catch(() => api.post('/pedidos', data));
    },

    getQuoteProducts(idCotizacion: number | string) {
        return api.get(`/public/pedidos/${idCotizacion}`).catch(() => api.get(`/pedidos/${idCotizacion}`));
    },

    transformQuoteToOrder(idCotizacion: number | string) {
        return api.put(`/pedidos/${idCotizacion}`, { id_estado_pedido: 2 });
    },

    takeQuote(id: number | string, idadmin: number | string) {
        return api.put(`/pedidos/${id}`, { id_estado_pedido: 2, id_usuario: idadmin });
    },

    validateQuote(id: number | string, idadmin: number | string, discountData?: any) {
        return api.put(`/pedidos/${id}`, { id_estado_pedido: 3, ...discountData });
    },

    cancelQuote(id: number | string, iduser?: number | string) {
        return api.put(`/pedidos/${id}`, { id_estado_pedido: 5 });
    },

    add_productos_to_cotizacion(idCotizacion: number | string, payload: { id_producto: number, cantidad: number }) {
        return api.post(`/detalle-pedidos`, { id_pedido: idCotizacion, ...payload });
    },

    remove_productos_to_cotizacion(idCotizacion: number | string, payload: { id_producto: number, cantidad: number }) {
        return api.delete(`/detalle-pedidos/${payload.id_producto}`);
    },

    force_remove_producto(idCotizacion: number | string, payload: { id_producto: number }) {
        return api.delete(`/detalle-pedidos/${payload.id_producto}`);
    }

}