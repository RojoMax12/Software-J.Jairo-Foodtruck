import { formatPriceCLP } from '@/utils/format';

export const KNOWN_CATEGORY_COLORS: Record<string, string> = {
  'Vianesas': '#E28743',
  'As': '#C0392B',
  'Churrascos': '#D35400',
  'Lomitos': '#8E44AD',
  'Hamburguesas': '#27AE60',
  'Pizzas': '#F39C12',
  'Fajitas': '#16A085',
  'Sándwiches de Pollo': '#2980B9',
  'Papas & Chorrillanas': '#F1C40F',
  'Empanadas & Sopaipillas': '#E67E22',
  'Handrolls & Arrollados': '#06B6D4',
  'Bebidas frías': '#3498DB',
  'Bebidas calientes': '#E74C3C',
  'Promos / Combos': '#3498DB',
};

const COLOR_PALETTE = [
  '#E28743', '#C0392B', '#D35400', '#8E44AD', '#27AE60',
  '#F39C12', '#16A085', '#2980B9', '#F1C40F', '#E67E22',
  '#3498DB', '#E74C3C', '#06B6D4', '#8B5CF6', '#EC4899',
];

export function resolveCategoryColor(catName: string, catId?: number): string {
  const knownColor = KNOWN_CATEGORY_COLORS[catName];
  if (knownColor) return knownColor;

  if (catId && !isNaN(catId) && catId > 0) {
    return COLOR_PALETTE[(catId - 1) % COLOR_PALETTE.length] ?? '#E28743';
  }

  let hash = 0;
  const str = String(catName || '');
  for (let i = 0; i < str.length; i++) {
    hash = str.charCodeAt(i) + ((hash << 5) - hash);
  }
  return COLOR_PALETTE[Math.abs(hash) % COLOR_PALETTE.length] ?? '#E28743';
}

export function normalizeSizeName(value: unknown): string {
  return String(value ?? '').trim();
}

export function getShiftDateStringLocal(now: Date = new Date()): string {
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const todayStr = `${year}-${month}-${day}`;

  if (now.getHours() < 6) {
    const yesterday = new Date(now.getTime() - 24 * 60 * 60 * 1000);
    return `${yesterday.getFullYear()}-${String(yesterday.getMonth() + 1).padStart(2, '0')}-${String(yesterday.getDate()).padStart(2, '0')}`;
  }

  return todayStr;
}

export function isPromotionActive(promo: any): boolean {
  if (!promo) return false;
  if (promo.activo === false || promo.activo === 0 || promo.activo === '0') return false;

  const now = new Date();
  const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
  const shiftDateStr = getShiftDateStringLocal(now);

  const startStr = String(promo.fecha_inicio || '').trim().slice(0, 10);
  const endStr = String(promo.fecha_fin || '').trim().slice(0, 10);

  const matchesToday = (!startStr || startStr <= todayStr) && (!endStr || endStr >= todayStr);
  const matchesShift = (!startStr || startStr <= shiftDateStr) && (!endStr || endStr >= shiftDateStr);

  return matchesToday || matchesShift;
}

export function extractValidPromotions(prod: any): any[] {
  const rawPromosList = Array.isArray(prod.promociones_activas || prod.promocionesActivas)
    ? (prod.promociones_activas || prod.promocionesActivas)
    : (prod.promocion_activa || prod.promocionActiva ? [prod.promocion_activa || prod.promocionActiva] : []);
  return rawPromosList.filter(isPromotionActive);
}

export function buildSizePricingMaps(prod: any, validPromos: any[]) {
  const originalPricesMap: Record<string, number> = {};
  const pricesMap: Record<string, number> = {};
  const sizesMap: Record<string, number> = {};
  const sizePromosMap: Record<string, any> = {};
  const sizes: string[] = [];
  const tamaños_obj: any[] = [];

  if (prod.tamaños && prod.tamaños.length > 0) {
    prod.tamaños.forEach((t: any) => {
      const sizeName = normalizeSizeName(t.nombre);
      if (!sizeName) return;

      const baseVal = Number(t.pivot?.precio ?? t.precio ?? 0);
      originalPricesMap[sizeName] = baseVal;
      pricesMap[sizeName] = baseVal;
      sizesMap[sizeName] = Number(t.id_tamaño || t.id || 1);

      const matchingPromo =
        validPromos.find((pr: any) => pr.id_tamaño != null && Number(pr.id_tamaño) === Number(t.id_tamaño)) ||
        validPromos.find((pr: any) => pr.id_tamaño == null) ||
        null;

      if (matchingPromo && Number(matchingPromo.precio_promocional ?? 0) > 0) {
        const promoVal = Number(matchingPromo.precio_promocional);
        pricesMap[sizeName] = baseVal > 0 ? Math.min(promoVal, baseVal) : promoVal;
        sizePromosMap[sizeName] = matchingPromo;
      }

      if (!sizes.some((s) => s.toLowerCase() === sizeName.toLowerCase())) {
        sizes.push(sizeName);
      }

      const sizeIdentifier = String(t.id_tamaño ?? sizeName).toLowerCase();
      const hasSameTamaño = tamaños_obj.some((existing: any) => {
        const existingId = String(existing.id_tamaño ?? existing.nombre ?? '').toLowerCase();
        return existingId === sizeIdentifier || normalizeSizeName(existing.nombre).toLowerCase() === sizeName.toLowerCase();
      });

      if (!hasSameTamaño) {
        tamaños_obj.push(t);
      }
    });
  } else {
    const defaultSizeName = 'Normal';
    const baseVal = Number(prod.precio ?? prod.precio_base ?? 0);
    originalPricesMap[defaultSizeName] = baseVal;
    pricesMap[defaultSizeName] = baseVal;
    sizesMap[defaultSizeName] = 1;
    sizes.push(defaultSizeName);

    const generalPromo = validPromos.find((pr: any) => pr.id_tamaño == null) || validPromos[0] || null;
    if (generalPromo && Number(generalPromo.precio_promocional ?? 0) > 0) {
      const promoVal = Number(generalPromo.precio_promocional);
      pricesMap[defaultSizeName] = baseVal > 0 ? Math.min(promoVal, baseVal) : promoVal;
      sizePromosMap[defaultSizeName] = generalPromo;
    }
  }

  return {
    originalPricesMap,
    pricesMap,
    sizesMap,
    sizePromosMap,
    sizes,
    tamaños_obj,
  };
}

export function normalizeGroupedProduct(product: any) {
  const uniqueSizes = Array.from(
    new Set((product.sizes || []).map((size: any) => String(size || '').trim()).filter(Boolean))
  );

  const variantCount = product.types?.length || 0;
  const hasMultipleSizes = uniqueSizes.length > 1;
  const hasMultipleVariants = variantCount > 1;

  let minPrice = Infinity;
  let minOriginalPrice = Infinity;
  let minPromoPrice = Infinity;
  let minPromoOriginalPrice = Infinity;
  let hasAnyPromotion = false;
  let promoTitle = '';
  let activePromoObj: any = null;

  (product.types || []).forEach((type: any) => {
    const sizePromos = type.sizePromos || {};
    const hasSizePromo = Object.keys(sizePromos).length > 0;
    const isPromoType = Boolean(type.promocion_activa || type.hasPromotion || hasSizePromo);
    if (isPromoType) {
      hasAnyPromotion = true;
      const firstSizePromo = Object.values(sizePromos)[0] as any;
      if (!activePromoObj) activePromoObj = firstSizePromo || type.promocion_activa;
      if (!promoTitle && (firstSizePromo?.titulo || type.promocion_activa?.titulo)) {
        promoTitle = firstSizePromo?.titulo || type.promocion_activa?.titulo;
      }
    }

    Object.entries(type.prices || {}).forEach(([sizeKey, price]: [string, any]) => {
      const numeric = Number(price);
      if (!isNaN(numeric) && numeric > 0) {
        if (numeric < minPrice) minPrice = numeric;
        const orig = Number(type.originalPrices?.[sizeKey] ?? numeric);
        const sizeIsDiscounted = Boolean(sizePromos[sizeKey]) || (isPromoType && orig > numeric);
        if (sizeIsDiscounted && numeric < minPromoPrice) {
          minPromoPrice = numeric;
          minPromoOriginalPrice = orig;
        }
      }
    });

    Object.values(type.originalPrices || {}).forEach((price: any) => {
      const numeric = Number(price);
      if (!isNaN(numeric) && numeric > 0 && numeric < minOriginalPrice) {
        minOriginalPrice = numeric;
      }
    });
  });

  const catLow = String(product.category ?? '').toLowerCase();
  const nameLow = String(product.name ?? '').toLowerCase();
  const isCategoryPromo =
    catLow.includes('promo') || catLow.includes('combo') || nameLow.includes('promo') || nameLow.includes('combo');

  const hasPromoDiscount = minPromoOriginalPrice < Infinity && minPromoOriginalPrice > minPromoPrice;
  const hasGeneralDiscount = minOriginalPrice < Infinity && minOriginalPrice > minPrice;
  const hasDiscount = (hasAnyPromotion || isCategoryPromo) && (hasPromoDiscount || hasGeneralDiscount);

  const effectiveMinPrice = hasPromoDiscount ? minPromoPrice : minPrice;
  const effectiveMinOriginalPrice = hasPromoDiscount ? minPromoOriginalPrice : minOriginalPrice;

  const displayPrice =
    effectiveMinPrice === Infinity
      ? 'Sin precio'
      : hasMultipleSizes || hasMultipleVariants
        ? `Desde ${formatPriceCLP(effectiveMinPrice)}`
        : formatPriceCLP(effectiveMinPrice);

  const originalDisplayPrice =
    hasDiscount && effectiveMinOriginalPrice < Infinity && effectiveMinOriginalPrice > effectiveMinPrice
      ? hasMultipleSizes || hasMultipleVariants
        ? `Desde ${formatPriceCLP(effectiveMinOriginalPrice)}`
        : formatPriceCLP(effectiveMinOriginalPrice)
      : undefined;

  const discountPercent =
    hasDiscount && effectiveMinOriginalPrice < Infinity && effectiveMinOriginalPrice > effectiveMinPrice
      ? Math.round((1 - effectiveMinPrice / effectiveMinOriginalPrice) * 100)
      : undefined;

  let displayHint = '';
  if (hasMultipleVariants && !hasMultipleSizes) {
    displayHint = `${variantCount} variedades`;
  } else if (hasMultipleSizes && !hasMultipleVariants) {
    displayHint = `${uniqueSizes.length} tamaños`;
  } else if (hasMultipleVariants && hasMultipleSizes) {
    displayHint = `${variantCount} variedades · ${uniqueSizes.length} tamaños`;
  }

  return {
    ...product,
    sizes: uniqueSizes,
    variantCount,
    hasMultipleSizes,
    hasMultipleVariants,
    isPromo: hasAnyPromotion || isCategoryPromo,
    hasDiscount,
    discountPercent,
    promoTitle,
    promocion_activa: activePromoObj,
    displayPrice,
    originalDisplayPrice,
    displayHint,
    minPrice: effectiveMinPrice === Infinity ? null : effectiveMinPrice,
  };
}

