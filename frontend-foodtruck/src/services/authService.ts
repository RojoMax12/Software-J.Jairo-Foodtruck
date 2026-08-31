import api from './api';

export interface RegisterData {
  nombre?: string;
  nombre_empresa?: string;
  rut_empresa?: string;
  correo_electronico: string;
  telefono?: string;
  comuna?: string;
  direccion?: string;
  contrasena: string;
  id_rol?: number;
}

export interface ProfileUpdateData {
  nombre?: string;
  apellido?: string;
  correo?: string;
  correo_electronico?: string;
  telefono?: string;
  password_actual?: string;
  nueva_password?: string;
}

export const authService = {
  async registerDistribuidor(data: RegisterData) {
    const payload = {
      ...data,
      nombre: data.nombre || data.nombre_empresa || data.correo_electronico.split('@')[0],
      nombre_empresa: data.nombre_empresa || data.nombre,
      telefono: data.telefono ? (data.telefono.startsWith('+56') ? data.telefono : `+56${data.telefono}`) : '',
      id_rol: data.id_rol ?? 2, // Rol 2 = Cliente
    };

    try {
      const response = await api.post('/auth/register', payload);
      return response.data;
    } catch (error) {
      throw error;
    }
  },

  async login(correo: string, contrasena: string) {
    const response = await api.post('/auth/login', {
      correo_electronico: correo,
      contrasena: contrasena
    });
    return response.data;
  },

  async getProfile() {
    const response = await api.get('/auth/me');
    return response.data;
  },

  async updateProfile(data: ProfileUpdateData) {
    const response = await api.put('/auth/profile', data);
    return response.data;
  }
};
