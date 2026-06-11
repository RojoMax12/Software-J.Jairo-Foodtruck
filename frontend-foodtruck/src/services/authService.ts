import api from './api';

export interface RegisterData {
  rut_empresa: string;
  nombre_empresa: string;
  correo_electronico: string;
  telefono: string;
  comuna: string;
  direccion: string;
  contrasena: string;
}

export const authService = {
  async registerDistribuidor(data: RegisterData) {
    // Aquí preparamos el objeto final para el backend
    const payload = {
      ...data,
      telefono: `+56${data.telefono}`, // Concatenamos el prefijo para la base de datos
      id_rol: 3, // Asignamos el rol de distribuidor por defecto
    };

    try {
      const response = await api.post('/usuarios_distribuidores', payload);
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
  }
};
