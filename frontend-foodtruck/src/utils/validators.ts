export const CHILEAN_PHONE_REGEX = /^(?:\+?56\s?9\s?\d{4}\s?\d{4}|9\s?\d{4}\s?\d{4})$/;
export const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export interface PasswordChecks {
  minLength: boolean;
  upperCase: boolean;
  lowerCase: boolean;
  number: boolean;
  specialChar: boolean;
}

export function checkPasswordRequirements(password: string): PasswordChecks {
  const value = String(password || '');
  return {
    minLength: value.length >= 8,
    upperCase: /[A-Z]/.test(value),
    lowerCase: /[a-z]/.test(value),
    number: /\d/.test(value),
    specialChar: /[^A-Za-z0-9]/.test(value),
  };
}

export function isStrongPassword(password: string): boolean {
  const c = checkPasswordRequirements(password);
  return c.minLength && c.upperCase && c.lowerCase && c.number && c.specialChar;
}

export function isValidChileanPhone(phone: string): boolean {
  return CHILEAN_PHONE_REGEX.test(String(phone || '').trim());
}

export function isValidEmail(email: string): boolean {
  return EMAIL_REGEX.test(String(email || '').trim());
}

