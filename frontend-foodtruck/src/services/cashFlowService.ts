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
  sessionId?: string;
}

export interface CashShiftSummary {
  totalSalesCash: number;
  totalSalesDebit: number;
  totalSalesTransfer: number;
  totalSales: number;
  totalExpenses: number;
  expectedCashInDrawer: number;
  actualCashCounted: number;
  difference: number; // actual - expected (>0 sobrante, <0 faltante, =0 cuadrada)
  notes?: string;
}

export interface CashRegisterSession {
  id: string | number;
  id_caja?: number;
  openedAt: string;
  openedTimestamp?: number;
  closedAt?: string;
  cashierName: string;
  initialCash: number; // Fondo inicial para vuelto
  isOpen: boolean;
  summary?: CashShiftSummary;
}

const LOCAL_KEY_TRX = 'foodtruck_cashflow_custom_transactions_v2';
const LOCAL_KEY_SESSION = 'foodtruck_cash_active_session_v2';

export function parseDateTimeString(dateString?: string): { formatted: string; timestamp: number } {
  if (!dateString) {
    const n = new Date();
    return {
      formatted: `${String(n.getDate()).padStart(2, '0')}/${String(n.getMonth() + 1).padStart(2, '0')}/${n.getFullYear()} ${String(n.getHours()).padStart(2, '0')}:${String(n.getMinutes()).padStart(2, '0')}`,
      timestamp: n.getTime()
    };
  }

  // Si viene en formato "YYYY-MM-DD HH:MM:SS" o "YYYY-MM-DDTHH:MM:SS"
  const clean = dateString.replace('T', ' ').split('.')[0];
  const parts = clean ? clean.split(' ') : [];
  
  if (parts.length >= 2) {
    const dateParts = parts[0] ? parts[0].split('-') : [];
    const timeParts = parts[1] ? parts[1].split(':') : [];
    
    if (dateParts.length === 3 && timeParts.length >= 2) {
      const year = parseInt(dateParts[0] || '0', 10);
      const month = parseInt(dateParts[1] || '0', 10);
      const day = parseInt(dateParts[2] || '0', 10);
      const hour = parseInt(timeParts[0] || '0', 10);
      const minute = parseInt(timeParts[1] || '0', 10);
      const second = timeParts[2] ? parseInt(timeParts[2], 10) : 0;

      const dateObj = new Date(year, month - 1, day, hour, minute, second);
      return {
        formatted: `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year} ${String(hour).padStart(2, '0')}:${String(minute).padStart(2, '0')}`,
        timestamp: dateObj.getTime()
      };
    }
  }

  const d = new Date(dateString);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');

  return {
    formatted: `${day}/${month}/${year} ${hours}:${minutes}`,
    timestamp: isNaN(d.getTime()) ? Date.now() : d.getTime()
  };
}

export function getShiftStartTimestamp(referenceDate: Date = new Date()): number {
  const now = referenceDate;
  const currentHour = now.getHours();
  const shiftDate = new Date(now);

  if (currentHour >= 0 && currentHour < 6) {
    // Madrugada: el turno de atención comenzó ayer por la tarde (18:00 hrs)
    shiftDate.setDate(shiftDate.getDate() - 1);
    shiftDate.setHours(18, 0, 0, 0);
  } else if (currentHour >= 6 && currentHour < 18) {
    // Durante el día: desde las 06:00 hrs
    shiftDate.setHours(6, 0, 0, 0);
  } else {
    // Noche (>= 18:00 hrs): desde hoy a las 18:00 hrs
    shiftDate.setHours(18, 0, 0, 0);
  }

  return shiftDate.getTime();
}

export default {
  // Obtener sesión de caja actual (Turno) sincronizada con el backend
  async fetchCurrentSessionFromBackend(): Promise<CashRegisterSession> {
    try {
      const response = await api.get('/cajas');
      const cajas = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      
      if (cajas.length > 0) {
        // La última caja registrada
        const latest = cajas[cajas.length - 1];
        const isClosed = latest.estado === 'cerrada' || !!latest.fecha_cierre || Number(latest.total_recaudado || 0) > 0;

        if (!isClosed) {
          const parsed = parseDateTimeString(latest.fecha_apertura || latest.created_at);
          const activeSession: CashRegisterSession = {
            id: latest.id_caja,
            id_caja: latest.id_caja,
            openedAt: parsed.formatted,
            openedTimestamp: parsed.timestamp,
            cashierName: latest.usuario?.nombre || 'Administrador',
            initialCash: Number(latest.monto_inicial || 0),
            isOpen: true
          };
          localStorage.setItem(LOCAL_KEY_SESSION, JSON.stringify(activeSession));
          return activeSession;
        }
      }
    } catch (e) {
      console.warn('Error al conectar con cajas del backend:', e);
    }

    return this.getCurrentSession();
  },

  // Obtener historial de arqueos y turnos cerrados directamente de la BD
  async fetchClosedSessionsFromBackend(): Promise<CashRegisterSession[]> {
    try {
      const response = await api.get('/cajas');
      const cajas = Array.isArray(response.data) ? response.data : (response.data?.data || []);
      
      const closedList: CashRegisterSession[] = cajas
        .filter((c: any) => c.estado === 'cerrada' || !!c.fecha_cierre || Number(c.total_recaudado || 0) > 0 || Number(c.total_ventas || 0) > 0)
        .map((c: any) => {
          const salesCash = Number(c.ventas_efectivo || 0);
          const salesDebit = Number(c.ventas_debito || 0);
          const salesTransfer = Number(c.ventas_transferencia || 0);
          const totalSales = Number(c.total_ventas || (salesCash + salesDebit + salesTransfer));
          const expenses = Number(c.gastos_efectivo || 0);
          const initial = Number(c.monto_inicial || 0);
          const expected = Number(c.efectivo_esperado || (initial + salesCash - expenses));
          const counted = Number(c.total_recaudado || 0);
          const diff = Number(c.diferencia !== undefined && c.diferencia !== null ? c.diferencia : (counted - expected));

          const parsedOpen = parseDateTimeString(c.fecha_apertura || c.created_at);
          const parsedClose = parseDateTimeString(c.fecha_cierre || c.updated_at || c.fecha_apertura);

          return {
            id: c.id_caja,
            id_caja: c.id_caja,
            openedAt: parsedOpen.formatted,
            openedTimestamp: parsedOpen.timestamp,
            closedAt: parsedClose.formatted,
            cashierName: c.usuario?.nombre || 'Administrador',
            initialCash: initial,
            isOpen: false,
            summary: {
              totalSalesCash: salesCash,
              totalSalesDebit: salesDebit,
              totalSalesTransfer: salesTransfer,
              totalSales: totalSales,
              totalExpenses: expenses,
              expectedCashInDrawer: expected,
              actualCashCounted: counted,
              difference: diff,
              notes: c.observaciones || 'Arqueo guardado en base de datos'
            }
          };
        })
        .reverse();

      return closedList;
    } catch (e) {
      console.warn('Error al obtener historial de arqueos de BD:', e);
      return [];
    }
  },

  getCurrentSession(): CashRegisterSession {
    try {
      const stored = localStorage.getItem(LOCAL_KEY_SESSION);
      if (stored) {
        return JSON.parse(stored);
      }
    } catch {}
    
    // Por defecto, la caja se inicia CERRADA hasta que el usuario la abra
    const closedDefault: CashRegisterSession = {
      id: '',
      openedAt: 'No iniciada',
      openedTimestamp: 0,
      cashierName: 'Sin turno activo',
      initialCash: 0,
      isOpen: false
    };
    return closedDefault;
  },

  // Abrir nuevo turno de caja con fondo inicial en BD
  async openSession(initialCash: number, cashierName: string = 'Administrador'): Promise<CashRegisterSession> {
    const parsedNow = parseDateTimeString();
    let backendId: number | string = 'turno_' + Date.now();

    try {
      const response = await api.post('/cajas', {
        monto_inicial: Math.max(0, Number(initialCash || 0)),
        total_ventas: 0,
        total_recaudado: 0,
        estado: 'abierta'
      });
      if (response.data?.id_caja) {
        backendId = response.data.id_caja;
      }
    } catch (err) {
      console.warn('No se pudo guardar la apertura de caja en backend:', err);
    }

    const newSession: CashRegisterSession = {
      id: backendId,
      id_caja: typeof backendId === 'number' ? backendId : undefined,
      openedAt: parsedNow.formatted,
      openedTimestamp: parsedNow.timestamp,
      cashierName,
      initialCash: Math.max(0, Number(initialCash || 0)),
      isOpen: true
    };

    localStorage.setItem(LOCAL_KEY_SESSION, JSON.stringify(newSession));
    window.dispatchEvent(new Event('foodtruck-cash-session-update'));
    return newSession;
  },

  // Cerrar turno y realizar Arqueo / Cuadratura en BD
  async closeSession(actualCashCounted: number, summary: Omit<CashShiftSummary, 'actualCashCounted' | 'difference'>, notes: string = ''): Promise<CashRegisterSession> {
    const current = this.getCurrentSession();
    const parsedNow = parseDateTimeString();

    const diff = Number(actualCashCounted) - Number(summary.expectedCashInDrawer);

    current.closedAt = parsedNow.formatted;
    current.isOpen = false;
    current.summary = {
      ...summary,
      actualCashCounted: Number(actualCashCounted),
      difference: diff,
      notes: notes || 'Arqueo de cierre'
    };

    if (current.id_caja || (typeof current.id === 'number')) {
      const cajaId = current.id_caja || current.id;
      try {
        await api.put(`/cajas/${cajaId}`, {
          total_ventas: Math.round(summary.totalSales || 0),
          total_recaudado: Math.round(actualCashCounted || 0),
          efectivo_esperado: Math.round(summary.expectedCashInDrawer || 0),
          diferencia: Math.round(diff || 0),
          ventas_efectivo: Math.round(summary.totalSalesCash || 0),
          ventas_debito: Math.round(summary.totalSalesDebit || 0),
          ventas_transferencia: Math.round(summary.totalSalesTransfer || 0),
          gastos_efectivo: Math.round(summary.totalExpenses || 0),
          observaciones: notes || 'Arqueo de cierre',
          estado: 'cerrada'
        });
      } catch (err) {
        console.warn('Error al actualizar cierre de caja en BD:', err);
      }
    }

    localStorage.setItem(LOCAL_KEY_SESSION, JSON.stringify(current));
    window.dispatchEvent(new Event('foodtruck-cash-session-update'));
    return current;
  },

  // Obtener transacciones personalizadas guardadas
  getCustomTransactions(): CashTransaction[] {
    try {
      const stored = localStorage.getItem(LOCAL_KEY_TRX);
      return stored ? JSON.parse(stored) : [];
    } catch {
      return [];
    }
  },

  // Guardar transacción personalizada (Gasto, Ingreso extra, etc.)
  saveCustomTransaction(trx: Omit<CashTransaction, 'id' | 'date'>): CashTransaction {
    const customList = this.getCustomTransactions();
    const now = new Date();
    const parsed = parseDateTimeString();

    const currentSession = this.getCurrentSession();

    const newTrx: CashTransaction = {
      ...trx,
      id: 'trx_' + Date.now(),
      date: parsed.formatted,
      createdAt: now.toISOString(),
      sessionId: String(currentSession?.id)
    };

    customList.unshift(newTrx);
    localStorage.setItem(LOCAL_KEY_TRX, JSON.stringify(customList));
    window.dispatchEvent(new Event('foodtruck-cash-transaction-update'));
    return newTrx;
  },

  // Cargar transacciones combinando las órdenes del backend y las transacciones manuales
  async getCombinedTransactions(): Promise<CashTransaction[]> {
    const customTrxs = this.getCustomTransactions();
    let orderTrxs: CashTransaction[] = [];

    try {
      const response = await api.get('/pedidos');
      const orders = response.data || [];

      // Filtrar órdenes válidas para caja: excluir cancelados
      const validOrders = orders.filter((o: any) => {
        const statusId = Number(o.id_estado_pedido || 1);
        const statusName = String(o.estado_pedido?.nombre || '').toLowerCase();
        return statusId !== 5 && !statusName.includes('cancel');
      });

      orderTrxs = validOrders.map((o: any) => {
        const dateRaw = o.fecha || o.created_at;
        const parsed = parseDateTimeString(dateRaw);
        const total = Number(o.total || 0);

        let rawMethod = String(o.metodo_pago?.nombre_metodo || o.metodo_pago || 'Efectivo').toLowerCase();
        let normalizedMethod = 'Efectivo';
        if (rawMethod.includes('deb') || rawMethod.includes('tarj') || rawMethod.includes('pos') || rawMethod.includes('cred')) {
          normalizedMethod = 'Débito / Tarjeta';
        } else if (rawMethod.includes('transf')) {
          normalizedMethod = 'Transferencia';
        }

        const isPaid = Number(o.id_estado_pago) === 2 || String(o.estado_pago?.nombre || '').toLowerCase().includes('pagad');

        return {
          id: `ped_${o.id_pedido}`,
          date: parsed.formatted,
          type: 'ingreso' as const,
          category: isPaid ? 'Ventas Pedidos (Pagado)' : 'Ventas Pedidos (Pendiente)',
          amount: total,
          paymentMethod: normalizedMethod,
          description: `Pedido #${o.numero_pedido_dia || o.id_pedido} - Cliente: ${o.nombre_persona || o.usuario?.nombre || 'Presencial'} (${isPaid ? 'Pagado' : 'Por Cobrar'})`,
          status: isPaid ? ('completado' as const) : ('pendiente' as const),
          createdAt: new Date(parsed.timestamp).toISOString()
        };
      });
    } catch (error) {
      console.warn('No se pudieron cargar pedidos del servidor para Flujo de Caja:', error);
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
