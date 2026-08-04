import stockService from './stockService';
import productService from './productService';
import categoryService from './productCategoryService';
import formatService from './productFormatService';

export type InventoryStatus = 'ok' | 'low' | 'critical' | 'over';

export interface InventoryItem {
  id: number | string;
  productName: string;
  shortLabel: string;
  categoryName: string;
  formatName: string;
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
  if (quantity > minStock * 3) return 'over';
  return 'ok';
};

const statusLabelByType: Record<InventoryStatus, string> = {
  ok: 'Saludable',
  low: 'En alerta',
  critical: 'Crítico',
  over: 'Sobre stock',
};

const dummyInventoryItems: InventoryItem[] = [
  {
    id: 1,
    productName: 'Pan brioche burger',
    shortLabel: 'PB',
    categoryName: 'Panadería',
    formatName: 'Unidad',
    quantity: 78,
    minStock: 30,
    status: 'ok',
    statusLabel: statusLabelByType.ok,
    statusClass: 'status-ok',
    updatedLabel: '15 jun 2026',
  },
  {
    id: 2,
    productName: 'Carne smash 150g',
    shortLabel: 'CS',
    categoryName: 'Proteínas',
    formatName: 'Kg',
    quantity: 11,
    minStock: 18,
    status: 'low',
    statusLabel: statusLabelByType.low,
    statusClass: 'status-low',
    updatedLabel: '15 jun 2026',
  },
  {
    id: 3,
    productName: 'Papas prefritas',
    shortLabel: 'PP',
    categoryName: 'Acompañamientos',
    formatName: 'Bolsa',
    quantity: 0,
    minStock: 6,
    status: 'critical',
    statusLabel: statusLabelByType.critical,
    statusClass: 'status-critical',
    updatedLabel: '14 jun 2026',
  },
  {
    id: 4,
    productName: 'Envases para hamburguesa',
    shortLabel: 'EH',
    categoryName: 'Empaques',
    formatName: 'Caja',
    quantity: 96,
    minStock: 25,
    status: 'over',
    statusLabel: statusLabelByType.over,
    statusClass: 'status-over',
    updatedLabel: '15 jun 2026',
  },
  {
    id: 5,
    productName: 'Queso cheddar',
    shortLabel: 'QC',
    categoryName: 'Lácteos',
    formatName: 'Kg',
    quantity: 23,
    minStock: 12,
    status: 'ok',
    statusLabel: statusLabelByType.ok,
    statusClass: 'status-ok',
    updatedLabel: '13 jun 2026',
  },
  {
    id: 6,
    productName: 'Bebidas cola lata',
    shortLabel: 'BC',
    categoryName: 'Empaques',
    formatName: 'Pack',
    quantity: 7,
    minStock: 12,
    status: 'low',
    statusLabel: statusLabelByType.low,
    statusClass: 'status-low',
    updatedLabel: '12 jun 2026',
  },
];

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

    if (rawStocks.length === 0) {
      return dummyInventoryItems;
    }

    return rawStocks.map((ingrediente: any, index: number) => {
      const stockId = ingrediente.id_ingrediente ?? index + 1;
      const quantity = Number(ingrediente.cantidad_actual ?? 0);
      const minStock = Number(ingrediente.cantidad_minima ?? 5);
      const status = buildStatus(quantity, minStock);
      const ingName = ingrediente.nombre || `Ingrediente #${stockId}`;

      const isAvailable = ingrediente.disponible !== false && quantity > 0;

      return {
        id: stockId,
        productName: ingName,
        shortLabel: ingName.toString().slice(0, 2).toUpperCase(),
        categoryName: detectCategoryName(ingName),
        formatName: 'Unidad',
        quantity,
        minStock,
        disponible: isAvailable,
        status: status,
        statusLabel: statusLabelByType[status],
        statusClass: `status-${status}`,
        updatedLabel: formatDateLabel(ingrediente.updated_at || ingrediente.created_at),
      };
    });
  } catch (error) {
    console.warn('Usando datos dummy de inventario por error en API:', error);
    return dummyInventoryItems;
  }
};

export default {
  getInventoryItems,
  async updateInventoryQuantity(stockId: number, quantity: number) {
    await stockService.updateStock(stockId, {
      cantidad_actual: quantity,
      cantidad_stock: quantity,
    });

    return getInventoryItems();
  },
  async toggleAvailability(stockId: number | string, disponible: boolean) {
    await stockService.updateStock(stockId, { disponible });
    return getInventoryItems();
  },
};