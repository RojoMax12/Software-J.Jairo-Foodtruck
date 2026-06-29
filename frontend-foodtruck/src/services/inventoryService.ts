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

const getInventoryItems = async (): Promise<InventoryItem[]> => {
  try {
    const [stocksRes, productsRes, categoriesRes, formatsRes] = await Promise.all([
      stockService.getStocks(),
      productService.getProducts(),
      categoryService.getCategory(),
      formatService.getFormats(),
    ]);

    const rawStocks = toArray(stocksRes);
    const rawProducts = toArray(productsRes);
    const rawCategories = toArray(categoriesRes);
    const rawFormats = toArray(formatsRes);

    if (rawStocks.length === 0) {
      return dummyInventoryItems;
    }

    const productMap = new Map<number, any>();
    rawProducts.forEach((product: any) => {
      const productId = Number(product.id ?? product.ID);
      if (productId) productMap.set(productId, product);
    });

    const categoryMap = new Map<number, string>();
    rawCategories.forEach((category: any) => {
      const categoryId = Number(category.id ?? category.ID);
      if (categoryId) categoryMap.set(categoryId, category.nombre_categoria || category.nombre || `Categoría #${categoryId}`);
    });

    const formatMap = new Map<number, string>();
    rawFormats.forEach((format: any) => {
      const formatId = Number(format.id ?? format.ID);
      if (formatId) formatMap.set(formatId, format.nombre_formato || format.nombre || `Formato #${formatId}`);
    });

    return rawStocks.map((stock: any, index: number) => {
      const stockId = stock.id ?? stock.ID ?? index + 1;
      const quantity = Number(stock.cantidad_stock ?? stock.cantidad ?? 0);
      const productId = Number(stock.id_producto ?? stock.product_id ?? stock.producto?.id ?? 0);
      const product = productMap.get(productId);

      const categoryId = Number(product?.id_categoria ?? product?.ID_categoria ?? stock.id_categoria ?? 0);
      const formatId = Number(product?.id_formato ?? product?.ID_formato ?? stock.id_formato ?? 0);
      const minStock = Number(stock.stock_minimo ?? stock.min_stock ?? product?.stock_minimo ?? 10);
      const status = buildStatus(quantity, minStock);

      return {
        id: stockId,
        productName: product?.nombre_producto || stock.nombre_producto || `Producto #${productId || stockId}`,
        shortLabel: (product?.nombre_producto || stock.nombre_producto || 'PX').toString().slice(0, 2).toUpperCase(),
        categoryName: categoryMap.get(categoryId) || stock.nombre_categoria || 'Sin categoría',
        formatName: formatMap.get(formatId) || stock.nombre_formato || 'Formato no definido',
        quantity,
        minStock,
        status,
        statusLabel: statusLabelByType[status],
        statusClass: `status-${status}`,
        updatedLabel: formatDateLabel(stock.updated_at || stock.created_at),
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
      cantidad_stock: quantity,
    });

    return getInventoryItems();
  },
};