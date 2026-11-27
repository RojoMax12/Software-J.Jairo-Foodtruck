import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
const REFRESH_THRESHOLD_SECONDS = 900; // Renovar proactivamente si quedan menos de 15 min

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

let refreshPromise: Promise<string | null> | null = null;

function decodeJwtExp(token: string): number | null {
  try {
    const parts = token.split('.');
    if (parts.length !== 3 || !parts[1]) return null;
    const base64 = parts[1].replace(/-/g, '+').replace(/_/g, '/');
    const padded = base64.padEnd(base64.length + ((4 - (base64.length % 4)) % 4), '=');
    const payload = JSON.parse(atob(padded));
    return typeof payload?.exp === 'number' ? payload.exp : null;
  } catch {
    return null;
  }
}

function shouldSkipRefresh(url?: string): boolean {
  if (!url) return false;
  return (
    url.includes('/public/') ||
    url.includes('/auth/login') ||
    url.includes('/auth/register') ||
    url.includes('/auth/refresh') ||
    url.includes('/auth/forgot-password') ||
    url.includes('/auth/reset-password') ||
    url.includes('/auth/logout')
  );
}

async function ensureFreshToken(currentToken: string): Promise<string> {
  const exp = decodeJwtExp(currentToken);
  if (!exp) return currentToken;

  const remainingSeconds = exp - Math.floor(Date.now() / 1000);
  if (remainingSeconds <= 0 || remainingSeconds >= REFRESH_THRESHOLD_SECONDS) {
    return currentToken;
  }

  if (!refreshPromise) {
    refreshPromise = axios
      .post(
        `${API_BASE_URL}/auth/refresh`,
        {},
        {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            Authorization: `Bearer ${currentToken}`,
          },
        }
      )
      .then((res) => {
        const nextToken = res.data?.access_token || res.data?.token;
        if (typeof nextToken === 'string' && nextToken.length > 0) {
          localStorage.setItem('token', nextToken);
          if (res.data?.user) {
            localStorage.setItem('user', JSON.stringify(res.data.user));
          }
          return nextToken;
        }
        return null;
      })
      .catch(() => null)
      .finally(() => {
        refreshPromise = null;
      });
  }

  const refreshed = await refreshPromise;
  return refreshed || localStorage.getItem('token') || currentToken;
}

api.interceptors.request.use(
  async (config) => {
    const storedToken = localStorage.getItem('token');
    if (storedToken && config.headers) {
      const tokenToUse = shouldSkipRefresh(config.url)
        ? storedToken
        : await ensureFreshToken(storedToken);
      config.headers.Authorization = `Bearer ${tokenToUse}`;
    }

    return config;
  },
  (error) => Promise.reject(error)
);

api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const status = error.response?.status;
    const originalConfig = error.config as (typeof error.config & { _retriedWithFreshToken?: boolean }) | undefined;

    if ((status === 401 || status === 419) && originalConfig && !originalConfig._retriedWithFreshToken && !shouldSkipRefresh(originalConfig.url)) {
      const latestToken = localStorage.getItem('token');
      const sentHeader = String(originalConfig.headers?.Authorization || '');
      if (latestToken && sentHeader !== `Bearer ${latestToken}`) {
        originalConfig._retriedWithFreshToken = true;
        if (originalConfig.headers) {
          originalConfig.headers.Authorization = `Bearer ${latestToken}`;
        }
        return api.request(originalConfig);
      }
    }

    if (status === 401 || status === 419) {
      const hadToken = !!localStorage.getItem('token');
      localStorage.removeItem('token');
      localStorage.removeItem('user');

      const currentPath = window.location.pathname;
      const publicPaths = ['/', '/login', '/register', '/forgot-password', '/reset-password', '/checkorderstatus', '/menu-board', '/tv-menu', '/cotizacion', '/cotizacion-exitosa'];
      if (hadToken && !publicPaths.includes(currentPath)) {
        window.location.href = '/login?expired=1';
      }
    }

    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error);
  }
);

export default api;
