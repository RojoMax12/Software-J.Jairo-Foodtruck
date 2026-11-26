/**
 * Utilidades compartidas de formateo para el frontend de Foodtruck J.Junior.
 */

export const formatCLP = (value: number | string | null | undefined): string => {
  const num =
    typeof value === 'string'
      ? Number(value.replace(/[^0-9.-]/g, ''))
      : Number(value ?? 0);
  if (!Number.isFinite(num)) return '0';
  return Math.round(num).toLocaleString('es-CL');
};

export const formatPriceCLP = (value: number | string | null | undefined): string => {
  return `$${formatCLP(value)}`;
};

export const parseNumericPrice = (value: number | string | null | undefined): number => {
  if (typeof value === 'number') return Number.isFinite(value) ? value : 0;
  if (typeof value === 'string') {
    const num = Number(value.replace(/[^0-9.-]/g, ''));
    return Number.isFinite(num) ? num : 0;
  }
  return 0;
};

