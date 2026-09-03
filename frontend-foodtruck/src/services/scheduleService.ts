import api from './api';

export interface ScheduleItem {
  id_horario_atencion: number;
  dia_semana: number; // 0: Domingo, 1: Lunes, ..., 6: Sábado
  hora_apertura: string;
  hora_cierre: string;
  minuto_colchon: number;
  activo: boolean;
  id_usuario?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface CurrentShiftInfo {
  start: string;
  end: string;
  start_timestamp: number;
  end_timestamp: number;
  hora_apertura: string;
  hora_cierre: string;
  dia: string;
  es_jornada_activa: boolean;
  es_dia_cerrado?: boolean;
  shift_date: string;
}

export default {
  getSchedules() {
    return api.get<ScheduleItem[]>('/horario_atenciones');
  },

  getScheduleById(id: number | string) {
    return api.get<ScheduleItem>(`/horario_atenciones/${id}`);
  },

  updateSchedule(id: number | string, data: Partial<ScheduleItem>) {
    return api.put<ScheduleItem>(`/horario_atenciones/${id}`, data);
  },

  getCurrentShift(date?: string) {
    return api.get<CurrentShiftInfo>('/horarios/turno-actual', {
      params: date ? { date } : undefined,
    });
  },
};
