/**
 * Utilidad reutilizable para calcular las iniciales de un usuario a partir de su nombre o correo.
 * Ejemplo: "Juan Pérez" -> "JP", "Administrador Jairo" -> "AJ", "Cajera" -> "CA", "admin@foodtruck.test" -> "AD"
 */

export function getUserInitials(nameOrEmail?: string | null): string {
  if (!nameOrEmail || typeof nameOrEmail !== 'string') {
    return 'JJ';
  }

  const clean = nameOrEmail.trim();
  if (!clean) return 'JJ';

  // Si es correo electrónico
  if (clean.includes('@')) {
    const atParts = clean.split('@');
    const firstPart = atParts[0] || '';
    const userPart = firstPart.replace(/[^a-zA-Z0-9]/g, ' ');
    const parts = userPart.trim().split(/\s+/).filter(Boolean);
    const p0 = parts[0] || '';
    const p1 = parts[1] || '';
    if (p0 && p1) {
      return (p0.charAt(0) + p1.charAt(0)).toUpperCase();
    }
    return userPart.slice(0, 2).toUpperCase() || 'JJ';
  }

  // Si son nombres con palabras
  const words = clean.split(/\s+/).filter(Boolean);
  const w0 = words[0] || '';
  const w1 = words[1] || '';
  if (w0 && !w1) {
    return w0.slice(0, 2).toUpperCase();
  }
  if (w0 && w1) {
    return (w0.charAt(0) + w1.charAt(0)).toUpperCase();
  }

  return 'JJ';
}

