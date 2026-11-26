/**
 * Utilidades compartidas de jornada operativa (cutoff 06:00 AM) y parseo de fechas backend.
 */

export function parseDateTimeString(dateString?: string): { formatted: string; timestamp: number } {
  if (!dateString) {
    const n = new Date();
    return {
      formatted: `${String(n.getDate()).padStart(2, '0')}/${String(n.getMonth() + 1).padStart(2, '0')}/${n.getFullYear()} ${String(n.getHours()).padStart(2, '0')}:${String(n.getMinutes()).padStart(2, '0')}`,
      timestamp: n.getTime(),
    };
  }

  const trimmed = String(dateString).trim();
  const hasIsoTimezone = /([Zz]|[+-]\d{2}:?\d{2})$/.test(trimmed);

  if (hasIsoTimezone) {
    const parsed = new Date(trimmed);
    if (!isNaN(parsed.getTime())) {
      const day = String(parsed.getDate()).padStart(2, '0');
      const month = String(parsed.getMonth() + 1).padStart(2, '0');
      const year = parsed.getFullYear();
      const hours = String(parsed.getHours()).padStart(2, '0');
      const minutes = String(parsed.getMinutes()).padStart(2, '0');
      return {
        formatted: `${day}/${month}/${year} ${hours}:${minutes}`,
        timestamp: parsed.getTime(),
      };
    }
  }

  const clean = trimmed.replace('T', ' ').split('.')[0];
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
        timestamp: dateObj.getTime(),
      };
    }
  }

  const d = new Date(trimmed);
  const day = String(d.getDate()).padStart(2, '0');
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const year = d.getFullYear();
  const hours = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');

  return {
    formatted: `${day}/${month}/${year} ${hours}:${minutes}`,
    timestamp: isNaN(d.getTime()) ? Date.now() : d.getTime(),
  };
}

export function getShiftDateString(inputDate?: string | Date): string {
  let dateObj: Date;
  if (!inputDate) {
    dateObj = new Date();
  } else if (typeof inputDate === 'string') {
    const dateToParse = inputDate.includes(' ') && !inputDate.includes('T')
      ? inputDate.replace(' ', 'T')
      : inputDate;
    dateObj = new Date(dateToParse);
  } else {
    dateObj = new Date(inputDate);
  }

  if (isNaN(dateObj.getTime())) {
    dateObj = new Date();
  }

  if (dateObj.getHours() < 6) {
    dateObj.setDate(dateObj.getDate() - 1);
  }

  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
}
