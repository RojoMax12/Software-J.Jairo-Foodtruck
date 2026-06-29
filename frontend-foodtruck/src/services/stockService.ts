import api from './api';

export default {
  getStocks() {
    return api.get('/stocks');
  },

  getStockById(id: number) {
    return api.get(`/stocks/${id}`);
  },

  createStock(data: unknown) {
    return api.post('/stocks', data);
  },

  updateStock(id: number, data: unknown) {
    return api.put(`/stocks/${id}`, data);
  },

  deleteStock(id: number) {
    return api.delete(`/stocks/${id}`);
  },
};