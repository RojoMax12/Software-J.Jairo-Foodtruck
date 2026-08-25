import api from './api';

export interface CashTransaction {
  id: string;
  date: string;
  type: 'ingreso' | 'egreso';
  category: string;
  amount: number;
  paymentMethod: string;
  description: string;
  status: 'completado' | 'pendiente';
  createdAt?: string;
}

const LOCAL_KEY = 'foodtruck_cashflow_custom_transactions';

export default {
  // Obtener transacciones personalizadas guardadas localmente
  getCustomTransactions(): CashTransaction[] {
    try {
      const stored = localStorage.getItem(LOCAL_KEY);
      return stored ? JSON.parse(stored) : [];
    } catch {
      return [];
    }
  },

  // Guardar transacción personalizada
  saveCustomTransaction(trx: Omit<CashTransaction, 'id' | 'date'>): CashTransaction {
    const customList = this.getCustomTransactions();
    const now = new Date();
    const dateFormatted = `${now.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' })} - ${now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })}`;

    const newTrx: CashTransaction = {
      ...trx,
      id: 'trx_' + Date.now(),
      date: dateFormatted,
      createdAt: now.toISOString()
    };

    customList.unshift(newTrx);
    localStorage.setItem(LOCAL_KEY, JSON.stringify(customList));
    return newTrx;
  },

  // Cargar transacciones combinando las órdenes del backend y las transacciones manuales
  async getCombinedTransactions(): Promise<CashTransaction[]> {
    const customTrxs = this.getCustomTransactions();
    let orderTrxs: CashTransaction[] = [];

    try {
      const response = await api.get('/pedidos');
      const orders = response.data || [];

      orderTrxs = orders.map((o: any) => {
        const orderDate = new Date(o.created_at || o.fecha || Date.now());
        const dateFormatted = `${orderDate.toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' })} - ${orderDate.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })}`;
        const total = Number(o.total || 0);

        return {
          id: `ped_${o.id_pedido}`,
          date: dateFormatted,
          type: 'ingreso' as const,
          category: 'Ventas POS',
          amount: total,
          paymentMethod: o.metodo_pago?.nombre_metodo || o.metodo_pago || 'Efectivo',
          description: `Pedido #${o.id_pedido} - Cliente: ${o.nombre_cliente || o.cliente?.nombre || 'Presencial'}`,
          status: (o.id_estado_pedido === 4 || o.estado?.nombre_estado === 'Completado' || o.estado?.nombre_estado === 'Entregado') ? 'completado' as const : 'completado' as const,
          createdAt: o.created_at || orderDate.toISOString()
        };
      });
    } catch (error) {
      console.warn('No se pudieron cargar pedidos del servidor para Flujo de Caja, mostrando locales:', error);
    }

    // Combinar y ordenar por fecha reciente
    const combined = [...customTrxs, ...orderTrxs];
    combined.sort((a, b) => {
      const dateA = a.createdAt ? new Date(a.createdAt).getTime() : 0;
      const dateB = b.createdAt ? new Date(b.createdAt).getTime() : 0;
      return dateB - dateA;
    });

    return combined;
  }
};
