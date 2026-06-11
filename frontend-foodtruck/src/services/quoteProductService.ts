import api from './api';

export default {
  getAll() {
    return api.get('/cotizacion_producto');
  },
  getById(id: number) {
    return api.get(`/cotizacion_producto/${id}`);
  },
  getByQuotationId(idCotizacion: number) {
    return api.get(`/cotizacion_producto/cotizacion/${idCotizacion}`);
  },
  getByProductId(idProducto: number) {
    return api.get(`/cotizacion_producto/producto/${idProducto}`);
  },
  create(payload: any) {
    return api.post('/cotizacion_producto', payload);
  },
  update(id: number, payload: any) {
    return api.put(`/cotizacion_producto/${id}`, payload);
  },
  delete(id: number) {
    return api.delete(`/cotizacion_producto/${id}`);
  }
}