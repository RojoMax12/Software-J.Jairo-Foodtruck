import api from './api';
import stockService from './stockService';
import productService from './productService';
import categoryService from './productCategoryService';
import formatService from './productFormatService';

export type InventoryStatus = 'ok' | 'low' | 'critical' | 'over';

export interface StockMovement {
  id_movimiento: number;
  id_ingrediente: number;
  cantidad: number;
  tipo_movimiento: 'Entrada' | 'Salida' | string;
  fecha_movimiento: string;
  created_at?: string;
  ingrediente?: {
    id_ingrediente: number;
    nombre: string;
    unidad_medida?: string;
  };
}

export type InventoryTrackingType = 'unidad' | 'pack' | 'no_cuantitativo';

export interface InventoryItem {
  id: number | string;
  productName: string;
  shortLabel: string;
  categoryName: string;
  formatName: string;
  tipoInventario: InventoryTrackingType;
  unidadesPorPack: number;
  esCuantificable: boolean;
  packsCount: number;
  remainderUnits: number;
  stockDisplayLabel: string;
  quantity: number;
  minStock: number;
  disponible: boolean;
  status: InventoryStatus;
  statusLabel: string;
  statusClass: string;
  updatedLabel: string;
}

const toArray = (response: any) => {
  if (Array.isArray(response?.data)) return response.data;
  if (Array.isArray(response?.data?.data)) return response.data.data;
  if (Array.isArray(response)) return response;
  return [];
};

const formatDateLabel = (value: string | undefined) => {
  if (!value) return 'Sin fecha';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value.slice(0, 10);

  return new Intl.DateTimeFormat('es-CL', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(date);
};

const buildStatus = (quantity: number, minStock: number): InventoryStatus => {
  if (quantity <= 0) return 'critical';
  if (quantity < minStock) return 'low';
  if (minStock > 0 && quantity > minStock * 15) return 'over';
  return 'ok';
};

const statusLabelByType: Record<InventoryStatus, string> = {
  ok: 'Saludable',
  low: 'En alerta',
  critical: 'Crítico',
  over: 'Sobre stock',
};

const detectCategoryName = (name: string): string => {
  if (!name) return 'Insumos Varios';
  const lower = name.toLowerCase();

  if (lower.includes('pan') || lower.includes('masa')) return 'Panadería & Masas';
  if (lower.includes('carne') || lower.includes('vianesa') || lower.includes('lomo') || lower.includes('churrasco') || lower.includes('pollo') || lower.includes('huevo') || lower.includes('tocino')) return 'Proteínas';
  if (lower.includes('queso') || lower.includes('leche') || lower.includes('crema')) return 'Lácteos';
  if (lower.includes('tomate') || lower.includes('palta') || lower.includes('chucrut') || lower.includes('cebolla') || lower.includes('pepinillo') || lower.includes('lechuga') || lower.includes('choclo')) return 'Frescos & Verduras';
  if (lower.includes('mayo') || lower.includes('ketchup') || lower.includes('mostaza') || lower.includes('salsa') || lower.includes('ají')) return 'Salsas & Aderezos';
  if (lower.includes('papa')) return 'Acompañamientos';
  if (lower.includes('bebida') || lower.includes('jugo')) return 'Bebestibles';
  if (lower.includes('envase') || lower.includes('bolsa') || lower.includes('servilleta') || lower.includes('caja')) return 'Empaques';

  return 'Insumos Varios';
};

const getInventoryItems = async (): Promise<InventoryItem[]> => {
  try {
    const stocksRes = await stockService.getStocks();
    const rawStocks = toArray(stocksRes);

    return rawStocks.map((ingrediente: any, index: number) => {
      const stockId = ingrediente.id_ingrediente ?? index + 1;
      const quantity = Number(ingrediente.cantidad_actual ?? 0);
      const minStock = Number(ingrediente.cantidad_minima ?? 5);
      const rawTipo = String(ingrediente.tipo_inventario || '');
      const esCuantificable = ingrediente.es_cuantificable !== undefined
        ? Boolean(ingrediente.es_cuantificable)
        : rawTipo !== 'no_cuantitativo';
      const tipoInventario: InventoryTrackingType = !esCuantificable
        ? 'no_cuantitativo'
        : (rawTipo === 'pack' ? 'pack' : 'unidad');
      const unidadesPorPack = tipoInventario === 'pack' ? Math.max(1, Number(ingrediente.unidades_por_pack || 1)) : 1;
      const packsCount = tipoInventario === 'pack' ? Math.floor(quantity / unidadesPorPack) : 0;
      const remainderUnits = tipoInventario === 'pack' ? (quantity % unidadesPorPack) : 0;

      const formatName = tipoInventario === 'pack'
        ? `Pack × ${unidadesPorPack} unid.`
        : (tipoInventario === 'no_cuantitativo' ? 'Porción libre (Disp.)' : 'Unidad');

      const stockDisplayLabel = !esCuantificable
        ? (ingrediente.disponible !== false ? 'Disponible' : 'Agotado')
        : tipoInventario === 'pack'
          ? (remainderUnits > 0
              ? `${packsCount} pk + ${remainderUnits} u (${quantity} unid.)`
              : `${packsCount} pack${packsCount === 1 ? '' : 's'} (${quantity} unid.)`)
          : `${quantity} unid.`;

      const status = !esCuantificable
        ? (ingrediente.disponible !== false ? 'ok' : 'critical')
        : buildStatus(quantity, minStock);

      const ingName = ingrediente.nombre || `Ingrediente #${stockId}`;
      const isAvailable = esCuantificable
        ? (ingrediente.disponible !== false && quantity > 0)
        : (ingrediente.disponible !== false);

      return {
        id: stockId,
        productName: ingName,
        shortLabel: ingName.toString().slice(0, 2).toUpperCase(),
        categoryName: detectCategoryName(ingName),
        formatName,
        tipoInventario,
        unidadesPorPack,
        esCuantificable,
        packsCount,
        remainderUnits,
        stockDisplayLabel,
        quantity,
        minStock,
        disponible: isAvailable,
        status,
        statusLabel: !esCuantificable ? (isAvailable ? 'Disponible' : 'Agotado') : statusLabelByType[status],
        statusClass: `status-${status}`,
        updatedLabel: formatDateLabel(ingrediente.updated_at || ingrediente.created_at),
      };
    });
  } catch (error) {
    throw error;
  }
};

export default {
  getInventoryItems,
  async createInventoryItem(data: {
    nombre: string;
    descripcion?: string;
    cantidad_actual: number;
    cantidad_minima: number;
    cantidad_packs?: number;
    tipo_inventario?: InventoryTrackingType;
    unidades_por_pack?: number;
    es_cuantificable?: boolean;
    fecha_de_ingreso?: string;
    disponible?: boolean;
  }) {
    const res = await stockService.createStock(data);
    return res.data;
  },
  async updateInventoryQuantity(
    stockId: number,
    quantity: number,
    extra?: { tipo_inventario?: InventoryTrackingType; unidades_por_pack?: number; es_cuantificable?: boolean }
  ) {
    await stockService.updateStock(stockId, {
      cantidad_actual: quantity,
      cantidad_stock: quantity,
      ...(extra || {}),
    });

    return getInventoryItems();
  },
  async toggleAvailability(stockId: number | string, disponible: boolean) {
    await stockService.updateStock(stockId, { disponible });
    return getInventoryItems();
  },
  async getIngredientMovements(idIngrediente?: number | string, limit = 100): Promise<StockMovement[]> {
    try {
      const params: Record<string, any> = { limit };
      if (idIngrediente) {
        params.id_ingrediente = idIngrediente;
      }
      const response = await api.get('/movimientos', { params });
      return Array.isArray(response.data) ? response.data : (response.data?.data || []);
    } catch (error) {
      console.warn('Error al cargar movimientos de stock:', error);
      return [];
    }
  },
};