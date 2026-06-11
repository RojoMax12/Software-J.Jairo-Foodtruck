import api from './api';

export default { 
    getOrders() {
        return api.get('/pedidos');
    },

    getOrdersByDistributor(distributorId: number) {
        return api.get(`/pedidos/${distributorId}/usuario_distribuidor`);
    },
    
    getOrderById(id: number) {
        return api.get(`/pedidos/${id}`);
    },

    updateOrder(id: number, data: unknown) {
        return api.put(`/pedidos/${id}`, data);
    },

    deleteOrder(id: number) {
        return api.delete(`/pedidos/${id}`);
    },

    createOrder(data: unknown) {
        return api.post('/pedidos', data);
    },

    getOrderDetails(id: number) {
        return api.get(`/pedidos/${id}/details`);
    },

    changeOrderStatus(id_pedido: number) {
        return api.put(`/pedidos/${id_pedido}/cambiar-estado`);
    }
}