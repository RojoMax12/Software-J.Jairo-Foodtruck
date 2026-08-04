import api from './api';

export default {
  getStocks() {
    return api.get('/ingredientes');
  },

  getStockById(id: number | string) {
    return api.get(`/ingredientes/${id}`);
  },

  createStock(data: unknown) {
    return api.post('/ingredientes', data);
  },

  updateStock(id: number | string, data: unknown) {
    return api.put(`/ingredientes/${id}`, data);
  },

  deleteStock(id: number | string) {
    return api.delete(`/ingredientes/${id}`);
  },
};