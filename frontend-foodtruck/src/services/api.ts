import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// 🚨 INTERCEPTOR DE PETICIÓN: Inyecta el token automáticamente en cada llamada
api.interceptors.request.use(
  (config) => {
    // Buscamos el token tal cual lo guardas en tu Login y Navbar
    const token = localStorage.getItem('token');
    
    // Si el token existe, se lo pegamos al encabezado Authorization
    if (token && config.headers) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor para manejar errores globales
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error.response?.data || error.message);
    return Promise.reject(error);
  }
);

export default api;
