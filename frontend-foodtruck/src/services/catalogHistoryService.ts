import api from './api';

export interface CatalogMovement {
    id: string | number;
    tipo: 'producto' | 'categoria' | 'tamaño' | 'oferta' | 'precio' | 'stock' | 'pedido' | 'caja';
    accion: 'crear' | 'editar' | 'eliminar' | 'estado' | 'oferta' | 'entregado' | 'cancelar' | 'pago' | 'apertura' | 'cierre';
    descripcion: string;
    entidad: string;
    detalle?: string;
    usuario: string;
    fecha: string;
    monto?: number;
    badgeClass?: string;
}

const STORAGE_KEY = 'foodtruck_catalog_history_cache_v1';

export const catalogHistoryService = {
    // 1. Obtener historial directamente desde la base de datos del Backend
    async fetchMovementsFromBackend(tipo?: string, search?: string): Promise<CatalogMovement[]> {
        try {
            const params: Record<string, any> = { limit: 250 };
            if (tipo) params.tipo = tipo;
            if (search) params.search = search;

            const res = await api.get('/historial_movimientos', { params });
            const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);

            if (data.length > 0) {
                const backendMovements: CatalogMovement[] = data.map((m: any) => ({
                    id: m.id_historial || m.id,
                    tipo: m.tipo || 'producto',
                    accion: m.accion || 'crear',
                    descripcion: m.descripcion || 'Movimiento registrado',
                    entidad: m.entidad || '',
                    detalle: m.detalle || '',
                    usuario: m.usuario || 'Administrador',
                    monto: m.monto ? Number(m.monto) : undefined,
                    fecha: m.fecha || m.created_at || new Date().toISOString()
                }));

                // Guardar en cache local solo para redundancia offline
                try {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(backendMovements.slice(0, 100)));
                } catch {}

                return backendMovements;
            }
        } catch (err) {
            console.warn('Error al conectar con /historial_movimientos en backend, usando cache:', err);
        }

        return this.getCachedMovements();
    },

    // 2. Cache local como fallback
    getCachedMovements(): CatalogMovement[] {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                return JSON.parse(raw);
            }
        } catch (e) {
            console.error('Error reading catalog history cache', e);
        }
        return [];
    },

    getMovements(): CatalogMovement[] {
        return this.getCachedMovements();
    },

    // 3. Registrar un nuevo movimiento en la base de datos del backend
    async recordMovement(movement: Omit<CatalogMovement, 'id' | 'fecha'>): Promise<CatalogMovement> {
        const nowIso = new Date().toISOString();
        let createdId: number | string = 'm-' + Date.now();

        // Enviar al Backend para persistencia permanente en BDD
        try {
            const response = await api.post('/historial_movimientos', {
                tipo: movement.tipo,
                accion: movement.accion,
                descripcion: movement.descripcion,
                entidad: movement.entidad,
                detalle: movement.detalle || null,
                usuario: movement.usuario || 'Administrador',
                fecha: nowIso
            });

            if (response.data?.id_historial) {
                createdId = response.data.id_historial;
            }
        } catch (err) {
            console.warn('No se pudo guardar el movimiento en backend /historial_movimientos:', err);
        }

        const newRecord: CatalogMovement = {
            id: createdId,
            fecha: nowIso,
            ...movement
        };

        // Actualizar cache local
        try {
            const cache = this.getCachedMovements();
            cache.unshift(newRecord);
            if (cache.length > 100) cache.splice(100);
            localStorage.setItem(STORAGE_KEY, JSON.stringify(cache));
        } catch {}

        window.dispatchEvent(new Event('foodtruck-catalog-movement'));
        return newRecord;
    },

    // 4. Vaciar historial tanto en backend como local
    async clearHistory(): Promise<void> {
        try {
            await api.delete('/historial_movimientos-clear');
        } catch (err) {
            console.warn('Error al vaciar historial en backend:', err);
        }

        try {
            localStorage.removeItem(STORAGE_KEY);
        } catch {}

        window.dispatchEvent(new Event('foodtruck-catalog-movement'));
    }
};

export default catalogHistoryService;
