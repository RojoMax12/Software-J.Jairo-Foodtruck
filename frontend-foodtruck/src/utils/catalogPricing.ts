/**
 * Lógica compartida de precios, promociones por tamaño, aderezos y extras.
 * Mantiene paridad con PedidoRepository.php en el backend.
 */

export const ADEREZOS_GRATUITOS = [
  'Ketchup',
  'Mostaza',
  'Salsa BBQ',
  'Mayonesa de Ajo',
  'Salsa de Queso',
  'Salsa de Ají',
] as const;

export function normalizeIngredientName(val: unknown): string {
  if (val === null || val === undefined) return '';
  return String(val)
    .trim()
    .replace(/\s+/g, ' ')
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

const ADEREZOS_NORM_SET = new Set([
  'ketchup',
  'mostaza',
  'salsa bbq',
  'bbq',
  'mayonesa de ajo',
  'salsa de queso',
  'salsa de aji',
]);

export function isAderezoGratuitoName(name: string): boolean {
  const norm = normalizeIngredientName(name);
  return norm ? ADEREZOS_NORM_SET.has(norm) : false;
}

export function supportsAderezosGratuitos(product: {
  nombre?: string;
  name?: string;
  categoria?: { nombre_categoria?: string };
  category?: string;
}): boolean {
  const cat = normalizeIngredientName(product.categoria?.nombre_categoria || product.category || '');
  const prod = normalizeIngredientName(product.nombre || product.name || '');
  return cat.includes('hamburguesa') || cat.includes('fajita') || prod.includes('hamburguesa') || prod.includes('fajita');
}

export function computeDeterministicExtrasCost(params: {
  selectedExtras: Array<any>;
  productIngredients: Array<any>;
  includedCount: number;
  defaultExtraPrice: number;
  allowFreeAderezos: boolean;
}): number {
  const { selectedExtras, productIngredients, includedCount, defaultExtraPrice, allowFreeAderezos } = params;
  if (!Array.isArray(selectedExtras) || selectedExtras.length === 0) return 0;

  const piById = new Map<number, any>();
  const piByNorm = new Map<string, any>();

  for (const pi of productIngredients || []) {
    const idIng = Number(pi.id_ingrediente || pi.ingrediente?.id_ingrediente || 0);
    if (idIng && (!piById.has(idIng) || pi.precio_extra !== null)) {
      piById.set(idIng, pi);
    }
    const norm = normalizeIngredientName(pi.ingrediente?.nombre || pi.nombre || '');
    if (norm && (!piByNorm.has(norm) || pi.precio_extra !== null)) {
      piByNorm.set(norm, pi);
    }
  }

  const billableCosts: number[] = [];

  for (const ext of selectedExtras) {
    const extName = typeof ext === 'object' ? (ext.nombre || ext.name || ext.ingrediente || '') : String(ext || '');
    const extId = typeof ext === 'object' ? Number(ext.id_ingrediente || ext.id || 0) : 0;

    if (allowFreeAderezos && isAderezoGratuitoName(extName)) {
      continue;
    }

    const pi = (extId && piById.get(extId)) || piByNorm.get(normalizeIngredientName(extName));
    const unitCost =
      pi && pi.precio_extra !== null && pi.precio_extra !== undefined
        ? Number(pi.precio_extra)
        : Number(defaultExtraPrice || 0);

    billableCosts.push(Number.isFinite(unitCost) ? unitCost : 0);
  }

  billableCosts.sort((a, b) => a - b);

  return billableCosts
    .slice(Math.max(0, includedCount))
    .reduce((acc, c) => acc + c, 0);
}

export function resolvePromoPrice(params: {
  basePrice: number;
  promociones: Array<any>;
  sizeId: number | null;
  referenceDateStr?: string;
}): number {
  const { basePrice, promociones, sizeId, referenceDateStr } = params;
  const today = referenceDateStr || new Date().toISOString().slice(0, 10);

  const activeList = (promociones || []).filter((p: any) => {
    if (p.activo === false || p.activo === 0) return false;
    const inicio = p.fecha_inicio ? String(p.fecha_inicio).slice(0, 10) : '';
    const fin = p.fecha_fin ? String(p.fecha_fin).slice(0, 10) : '';
    const afterStart = !inicio || inicio <= today;
    const beforeEnd = !fin || fin >= today;
    return afterStart && beforeEnd;
  });

  const matching =
    (sizeId
      ? activeList.find((p: any) => p.id_tamaño !== null && Number(p.id_tamaño) === Number(sizeId))
      : undefined) || activeList.find((p: any) => p.id_tamaño === null || p.id_tamaño === undefined);

  if (matching && Number(matching.precio_promocional) > 0) {
    const promoPrice = Number(matching.precio_promocional);
    return basePrice > 0 ? Math.min(basePrice, promoPrice) : promoPrice;
  }

  return basePrice;
}

