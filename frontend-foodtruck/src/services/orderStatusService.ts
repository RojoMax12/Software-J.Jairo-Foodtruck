import api from './api';

export default {
  getOrderStatuses() {
    return api.get('/estado_pedido');
  },
  getOrderStatusById(id: number) {
    return api.get(`/estado_pedido/${id}`);
  },
  createOrderStatus(data: unknown) {
    return api.post('/estado_pedido', data);
  },
  updateOrderStatus(id: number, data: unknown) {
    return api.put(`/estado_pedido/${id}`, data);
  },
  deleteOrderStatus(id: number) {
    return api.delete(`/estado_pedido/${id}`);
  }
}