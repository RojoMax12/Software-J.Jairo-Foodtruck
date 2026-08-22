import api from './api';

export default { 
    getOrders() {
        return api.get('/pedidos');
    },

    getOrdersByDistributor(distributorId: number) {
        return api.get(`/pedidos/${distributorId}/usuario_distribuidor`);
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
        return api.get(`/pedidos/${id}/details`);
    },

    changeOrderStatus(id_pedido: number | string) {
        return api.put(`/pedidos/${id_pedido}/cambiar-estado`);
    },

    // RUTAS PÚBLICAS PARA QR Y CLIENTES SIN LOGIN
    getPublicOrderById(id: number | string) {
        return api.get(`/public/pedidos/${id}`);
    },

    createPublicOrder(data: unknown) {
        return api.post('/public/pedidos', data);
    }
}