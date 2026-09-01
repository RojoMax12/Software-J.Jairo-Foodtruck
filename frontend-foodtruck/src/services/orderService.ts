import api from './api';

export default { 
    getOrders(params?: { fecha_inicio?: string; fecha_fin?: string; limit?: number }) {
        return api.get('/pedidos', { params });
    },

    getMyOrders(params?: { fecha_inicio?: string; fecha_fin?: string; limit?: number }) {
        return api.get('/pedidos/mis-pedidos', { params });
    },

    getOrdersByDistributor(distributorId: number, params?: { fecha_inicio?: string; fecha_fin?: string; limit?: number }) {
        return api.get(`/pedidos/${distributorId}/usuario_distribuidor`, { params })
            .catch(() => api.get('/pedidos/mis-pedidos', { params }));
    },
    
    getOrderById(id: number | string) {
        return api.get(`/pedidos/${id}`);
    },

    updateOrder(id: number | string, data: unknown) {
        return api.put(`/pedidos/${id}`, data);
    },

    deleteOrder(id: number | string) {
        return api.delete(`/pedidos/${id}`);
    },

    createOrder(data: unknown) {
        return api.post('/pedidos', data);
    },

    getOrderDetails(id: number | string) {
        return api.get(`/public/pedidos/${id}`).catch(() => api.get(`/pedidos/${id}`));
    },

    changeOrderStatus(id_pedido: number | string) {
        return api.put(`/pedidos/${id_pedido}/cambiar-estado`);
    },

    // RUTAS PÚBLICAS PARA QR Y CLIENTES SIN LOGIN
    getOrderByComanda(comanda: number | string, params?: { fecha?: string }) {
        return api.get(`/public/pedidos/comanda/${comanda}`, { params });
    },

    getPublicOrderById(id: number | string) {
        return api.get(`/public/pedidos/${id}`);
    },

    createPublicOrder(data: unknown) {
        return api.post('/public/pedidos', data);
    }
}