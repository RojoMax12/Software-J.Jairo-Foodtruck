import api from './api';

export interface CatalogMovement {
    id: string | number;
    tipo: 'producto' | 'categoria' | 'tamaño' | 'oferta' | 'precio' | 'stock';
    accion: 'crear' | 'editar' | 'eliminar' | 'estado' | 'oferta';
    descripcion: string;
    entidad: string;
    detalle?: string;
    usuario: string;
    fecha: string;
    badgeClass?: string;
}

const STORAGE_KEY = 'foodtruck_catalog_history_v1';

export const catalogHistoryService = {
    async fetchMovementsFromBackend(): Promise<CatalogMovement[]> {
        try {
            const res = await api.get('/movimientos');
            const data = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            if (data.length > 0) {
                const backendMovements: CatalogMovement[] = data.map((m: any) => ({
                    id: m.id_movimiento,
                    tipo: 'stock',
                    accion: m.tipo_movimiento === 'Entrada' ? 'crear' : 'editar',
                    descripcion: `Movimiento de inventario: ${m.tipo_movimiento || 'Ajuste'}`,
                    entidad: m.ingrediente?.nombre || `Ingrediente #${m.id_ingrediente}`,
                    detalle: `Cantidad: ${m.cantidad} unidades`,
                    usuario: 'Sistema / Cocina',
                    fecha: m.created_at || m.fecha_movimiento || new Date().toISOString()
                }));

                const local = this.getMovements();
                const combined = [...local.filter(l => typeof l.id === 'string' && l.id.startsWith('m-')), ...backendMovements];
                combined.sort((a, b) => new Date(b.fecha).getTime() - new Date(a.fecha).getTime());
                localStorage.setItem(STORAGE_KEY, JSON.stringify(combined.slice(0, 150)));
                return combined;
            }
        } catch (err) {
            console.warn('Usando historial local:', err);
        }
        return this.getMovements();
    },

    getMovements(): CatalogMovement[] {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                return JSON.parse(raw);
            }
        } catch (e) {
            console.error('Error reading catalog history', e);
        }
        const initial: CatalogMovement[] = [
            {
                id: 'm-1',
                tipo: 'producto',
                accion: 'crear',
                descripcion: 'Nuevo producto creado',
                entidad: 'Hamburguesa Doble Smash',
                detalle: 'Precio base: $6.500 · Categoría: Hamburguesas · Tamaños: Normal, Doble',
                usuario: 'Administrador (JJ)',
                fecha: new Date(Date.now() - 1000 * 60 * 30).toISOString()
            },
            {
                id: 'm-2',
                tipo: 'categoria',
                accion: 'crear',
                descripcion: 'Nueva categoría registrada',
                entidad: 'Papas & Acompañamientos',
                detalle: 'Para porciones de papas fritas y salsas especiales',
                usuario: 'Administrador (JJ)',
                fecha: new Date(Date.now() - 1000 * 60 * 120).toISOString()
            },
            {
                id: 'm-3',
                tipo: 'tamaño',
                accion: 'crear',
                descripcion: 'Nuevo tamaño configurado',
                entidad: 'Familiar XL',
                detalle: 'Habilitado para sándwiches y porciones para compartir',
                usuario: 'Administrador (JJ)',
                fecha: new Date(Date.now() - 1000 * 60 * 240).toISOString()
            },
            {
                id: 'm-4',
                tipo: 'oferta',
                accion: 'oferta',
                descripcion: 'Descuento especial activado',
                entidad: 'Completo Italiano Clásico',
                detalle: '15% OFF aplicado por promoción de la semana',
                usuario: 'Administrador (JJ)',
                fecha: new Date(Date.now() - 1000 * 60 * 480).toISOString()
            }
        ];
        localStorage.setItem(STORAGE_KEY, JSON.stringify(initial));
        return initial;
    },

    recordMovement(movement: Omit<CatalogMovement, 'id' | 'fecha'>) {
        const history = this.getMovements();
        const newRecord: CatalogMovement = {
            id: 'm-' + Date.now(),
            fecha: new Date().toISOString(),
            ...movement
        };
        history.unshift(newRecord);
        if (history.length > 150) history.splice(150);
        localStorage.setItem(STORAGE_KEY, JSON.stringify(history));

        window.dispatchEvent(new Event('foodtruck-catalog-movement'));
        return newRecord;
    },

    clearHistory() {
        localStorage.removeItem(STORAGE_KEY);
        window.dispatchEvent(new Event('foodtruck-catalog-movement'));
    }
};

export default catalogHistoryService;
