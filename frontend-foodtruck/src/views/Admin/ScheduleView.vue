<template>
  <div class="schedule-admin-page">
    <!-- Header Principal -->
    <header class="page-header">
      <div class="header-left">
        <div class="header-title-row">
          <div class="header-icon-box">
            <Clock :size="28" />
          </div>
          <div>
            <h1>Horarios de Trabajo y Tiempo de Colchón</h1>
            <p>Configura las horas de apertura, cierre y el margen de tolerancia nocturno para cada día de la semana.</p>
          </div>
        </div>
      </div>

      <div class="header-actions">
        <button class="btn-audit" @click="goToAudit" title="Ver auditoría de cambios">
          <History :size="16" />
          <span>Ver Auditoría</span>
        </button>
        <button class="btn-primary" :disabled="isSavingAll" @click="saveAllSchedules">
          <Save :size="16" />
          <span>{{ isSavingAll ? 'Guardando...' : 'Guardar Todos los Días' }}</span>
        </button>
      </div>
    </header>

    <!-- Tarjetas de Resumen Superior -->
    <div class="stats-cards">
      <!-- 1. Estado en vivo -->
      <div class="stat-card" :class="currentShift?.es_jornada_activa ? 'border-active' : 'border-inactive'">
        <div class="stat-left">
          <div class="icon-circle" :class="currentShift?.es_jornada_activa ? 'bg-green' : 'bg-gray'">
            <span class="pulse-dot" v-if="currentShift?.es_jornada_activa"></span>
            <Store :size="22" />
          </div>
          <div class="stat-info">
            <span class="stat-label">Estado en Vivo</span>
            <strong class="stat-value" :class="currentShift?.es_jornada_activa ? 'text-green' : 'text-gray'">
              {{ currentShift?.es_jornada_activa ? '🟢 Abierto Ahora' : '⚪ Cerrado Ahora' }}
            </strong>
          </div>
        </div>
        <div class="stat-subtext">
          <span>{{ currentShift?.dia || 'Hoy' }} · {{ currentShift?.hora_apertura || '--:--' }} a {{ currentShift?.hora_cierre || '--:--' }}</span>
        </div>
      </div>

      <!-- 2. Días Operativos -->
      <div class="stat-card">
        <div class="stat-left">
          <div class="icon-circle bg-orange">
            <CalendarCheck :size="22" />
          </div>
          <div class="stat-info">
            <span class="stat-label">Días Operativos</span>
            <strong class="stat-value text-orange">
              {{ activeDaysCount }} de 7 días
            </strong>
          </div>
        </div>
        <div class="stat-subtext">
          <span>{{ 7 - activeDaysCount }} día(s) de descanso programado</span>
        </div>
      </div>

      <!-- 3. Colchón promedio -->
      <div class="stat-card">
        <div class="stat-left">
          <div class="icon-circle bg-amber">
            <Hourglass :size="22" />
          </div>
          <div class="stat-info">
            <span class="stat-label">Colchón Nocturno</span>
            <strong class="stat-value text-amber">
              {{ todaySchedule?.minuto_colchon || 30 }} min
            </strong>
          </div>
        </div>
        <div class="stat-subtext">
          <span>Tolerancia de hoy hasta: {{ calculateExtendedClosingTime(todaySchedule?.hora_cierre, todaySchedule?.minuto_colchon) }}</span>
        </div>
      </div>
    </div>

    <!-- Banner Informativo sobre Horario de Colchón -->
    <div class="info-banner">
      <div class="info-icon">
        <Info :size="20" />
      </div>
      <div class="info-content">
        <strong>¿Cómo funciona el Horario de Colchón?</strong>
        <p>
          El <b>colchón</b> es el tiempo extra (por defecto 30 minutos) posterior a la hora oficial de cierre. Permite que clientes de última hora sean atendidos y asegura que los pedidos emitidos en la madrugada queden contablemente agrupados en el <b>mismo turno de trabajo</b> sin reiniciar comandas a medianoche.
        </p>
      </div>
    </div>

    <!-- Barra de Herramientas y Acciones Rápidas -->
    <div class="quick-actions-bar">
      <div class="quick-title">
        <Sparkles :size="16" />
        <span>Plantillas rápidas:</span>
      </div>
      <div class="quick-buttons">
        <button class="btn-quick" @click="applyWeekdayPreset">
          📅 Aplicar Lun-Jue (19:00 a 00:30 · 30m)
        </button>
        <button class="btn-quick" @click="applyWeekendPreset">
          🔥 Aplicar Vie-Sáb (19:00 a 01:30 · 30m)
        </button>
        <button class="btn-quick" @click="applySundayPreset">
          🌙 Aplicar Domingo (19:00 a 00:30 · 30m)
        </button>
      </div>
    </div>

    <!-- Lista de Días de la Semana -->
    <div v-if="isLoading" class="loading-box">
      <RefreshCw :size="24" class="spin-icon" />
      <span>Cargando configuración de horarios...</span>
    </div>

    <div v-else class="schedules-grid">
      <div 
        v-for="item in sortedSchedules" 
        :key="item.id_horario_atencion"
        class="schedule-card"
        :class="{ 'card-today': isToday(item.dia_semana), 'card-disabled': !item.activo }"
      >
        <!-- Cabecera del Día -->
        <div class="day-header">
          <div class="day-title-wrap">
            <span class="day-name">{{ getDayName(item.dia_semana) }}</span>
            <span v-if="isToday(item.dia_semana)" class="badge-today">HOY</span>
          </div>

          <!-- Switch Activo / Cerrado -->
          <div class="toggle-wrap">
            <label class="switch">
              <input 
                type="checkbox" 
                v-model="item.activo"
                @change="markDirty(item)"
              />
              <span class="slider"></span>
            </label>
            <span class="toggle-label" :class="item.activo ? 'label-open' : 'label-closed'">
              {{ item.activo ? 'Abierto' : 'Cerrado' }}
            </span>
          </div>
        </div>

        <!-- Contenido si está Activo -->
        <div v-if="item.activo" class="schedule-body">
          <div class="form-row">
            <!-- Hora Apertura -->
            <div class="form-group">
              <label>
                <Sun :size="14" />
                <span>Hora de Apertura:</span>
              </label>
              <input 
                type="time" 
                v-model="item.hora_apertura" 
                class="time-input"
                @input="markDirty(item)"
              />
            </div>

            <!-- Hora Cierre -->
            <div class="form-group">
              <label>
                <Moon :size="14" />
                <span>Hora de Cierre:</span>
              </label>
              <input 
                type="time" 
                v-model="item.hora_cierre" 
                class="time-input"
                @input="markDirty(item)"
              />
            </div>
          </div>

          <!-- Minuto de Colchón -->
          <div class="colchon-section">
            <div class="colchon-header">
              <label>
                <Hourglass :size="14" />
                <span>Minutos de Colchón:</span>
              </label>
              <span class="colchon-badge">{{ item.minuto_colchon || 0 }} min</span>
            </div>

            <!-- Chips de acceso rápido -->
            <div class="chips-row">
              <button 
                v-for="min in [15, 30, 45, 60]" 
                :key="min"
                class="chip-btn"
                :class="{ active: item.minuto_colchon === min }"
                @click="setColchon(item, min)"
              >
                {{ min }} min
              </button>
              <div class="custom-colchon">
                <input 
                  type="number" 
                  min="0" 
                  max="180" 
                  v-model.number="item.minuto_colchon"
                  class="colchon-number-input"
                  placeholder="Otro"
                  @input="markDirty(item)"
                />
                <span class="unit">m</span>
              </div>
            </div>
          </div>

          <!-- Resumen explicativo del turno -->
          <div class="shift-preview-box">
            <Clock :size="14" />
            <span>
              Turno: <b>{{ formatTimeShort(item.hora_apertura) }}</b> a <b>{{ formatTimeShort(item.hora_cierre) }}</b> hrs. 
              Tolerancia colchón hasta las <b>{{ calculateExtendedClosingTime(item.hora_cierre, item.minuto_colchon) }} hrs</b>.
            </span>
          </div>
        </div>

        <!-- Mensaje si está Desactivado -->
        <div v-else class="schedule-disabled-body">
          <Coffee :size="32" class="disabled-icon" />
          <strong>Día de descanso programado</strong>
          <p>El foodtruck no atenderá pedidos este día.</p>
        </div>

        <!-- Footer con Botón Guardar Día -->
        <div class="card-footer">
          <span v-if="dirtyMap[item.id_horario_atencion]" class="dirty-hint">
            ⚠️ Cambios sin guardar
          </span>
          <span v-else class="saved-hint">
            ✓ Guardado
          </span>

          <button 
            class="btn-save-day" 
            :disabled="savingId === item.id_horario_atencion || !dirtyMap[item.id_horario_atencion]"
            @click="saveDay(item)"
          >
            <Check :size="15" />
            <span>{{ savingId === item.id_horario_atencion ? 'Guardando...' : 'Guardar Día' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Toast de Notificación Flotante -->
    <Transition name="fade-slide">
      <div v-if="toastMessage" class="toast-notification" :class="'toast-' + toastType">
        <CheckCircle2 v-if="toastType === 'success'" :size="18" />
        <AlertCircle v-else :size="18" />
        <span>{{ toastMessage }}</span>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { 
  Clock, History, Save, Store, CalendarCheck, Hourglass, Info, 
  Sun, Moon, Check, Coffee, Sparkles, RefreshCw, CheckCircle2, AlertCircle 
} from 'lucide-vue-next';
import scheduleService, { type ScheduleItem, type CurrentShiftInfo } from '@/services/scheduleService';

const router = useRouter();

const schedules = ref<ScheduleItem[]>([]);
const currentShift = ref<CurrentShiftInfo | null>(null);
const isLoading = ref(true);
const savingId = ref<number | null>(null);
const isSavingAll = ref(false);
const dirtyMap = ref<Record<number, boolean>>({});

// Toast
const toastMessage = ref('');
const toastType = ref<'success' | 'error'>('success');
let toastTimer: any = null;

const showToast = (msg: string, type: 'success' | 'error' = 'success') => {
  toastMessage.value = msg;
  toastType.value = type;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toastMessage.value = '';
  }, 3500);
};

// Días de la semana en orden de visualización: Lunes (1) a Domingo (0)
const dayNames: Record<number, string> = {
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
  0: 'Domingo',
};

// Orden deseado de presentación: Lunes a Domingo
const presentationOrder = [1, 2, 3, 4, 5, 6, 0];

const sortedSchedules = computed(() => {
  const map: Record<number, ScheduleItem> = {};
  schedules.value.forEach(s => {
    map[s.dia_semana] = s;
  });
  return presentationOrder
    .map(dia => map[dia])
    .filter((s): s is ScheduleItem => !!s);
});

const activeDaysCount = computed(() => {
  return schedules.value.filter(s => s.activo).length;
});

// Día actual de hoy (0 a 6 en JS)
const todayDayIndex = computed(() => new Date().getDay());

const todaySchedule = computed(() => {
  return schedules.value.find(s => s.dia_semana === todayDayIndex.value);
});

const isToday = (dia: number) => dia === todayDayIndex.value;
const getDayName = (dia: number) => dayNames[dia] || `Día ${dia}`;

const formatTimeShort = (timeStr?: string) => {
  if (!timeStr) return '--:--';
  return timeStr.slice(0, 5);
};

// Cálculo visual de cierre extendido sumando los minutos de colchón
const calculateExtendedClosingTime = (cierreStr?: string, colchonMin: number = 30) => {
  if (!cierreStr) return '--:--';
  const parts = cierreStr.split(':');
  const h = parseInt(parts[0] || '0', 10);
  const m = parseInt(parts[1] || '0', 10);
  const totalMin = (h * 60 + m + Number(colchonMin || 0)) % (24 * 60);

  const finalH = Math.floor(totalMin / 60).toString().padStart(2, '0');
  const finalM = (totalMin % 60).toString().padStart(2, '0');
  return `${finalH}:${finalM}`;
};

const markDirty = (item: ScheduleItem) => {
  dirtyMap.value[item.id_horario_atencion] = true;
};

const setColchon = (item: ScheduleItem, min: number) => {
  item.minuto_colchon = min;
  markDirty(item);
};

// Cargar datos del servidor
const loadData = async () => {
  isLoading.value = true;
  try {
    const [schedRes, shiftRes] = await Promise.all([
      scheduleService.getSchedules(),
      scheduleService.getCurrentShift().catch(() => null),
    ]);

    const rawSchedules = schedRes.data || [];
    // Formatear horas a HH:mm para los inputs type="time"
    schedules.value = rawSchedules.map((s: any) => ({
      ...s,
      hora_apertura: formatTimeShort(s.hora_apertura),
      hora_cierre: formatTimeShort(s.hora_cierre),
      minuto_colchon: Number(s.minuto_colchon ?? 30),
      activo: Boolean(s.activo),
    }));

    if (shiftRes && shiftRes.data) {
      currentShift.value = shiftRes.data;
    }

    dirtyMap.value = {};
  } catch (err) {
    console.error('Error al cargar horarios:', err);
    showToast('Error al conectar con el servidor para cargar los horarios.', 'error');
  } finally {
    isLoading.value = false;
  }
};

// Guardar un solo día
const saveDay = async (item: ScheduleItem) => {
  savingId.value = item.id_horario_atencion;
  try {
    const payload = {
      hora_apertura: item.hora_apertura,
      hora_cierre: item.hora_cierre,
      minuto_colchon: item.minuto_colchon,
      activo: item.activo,
    };
    await scheduleService.updateSchedule(item.id_horario_atencion, payload);
    dirtyMap.value[item.id_horario_atencion] = false;
    const shiftRes = await scheduleService.getCurrentShift();
    if (shiftRes?.data) {
      currentShift.value = shiftRes.data;
    }
    showToast(`Horario de ${getDayName(item.dia_semana)} guardado correctamente.`);
  } catch (err: any) {
    console.error('Error al guardar día:', err);
    showToast('No se pudo guardar el horario. Revisa los valores e inténtalo nuevamente.', 'error');
  } finally {
    savingId.value = null;
  }
};

// Guardar todos los días modificados
const saveAllSchedules = async () => {
  isSavingAll.value = true;
  let savedCount = 0;
  try {
    for (const item of schedules.value) {
      const payload = {
        hora_apertura: item.hora_apertura,
        hora_cierre: item.hora_cierre,
        minuto_colchon: item.minuto_colchon,
        activo: item.activo,
      };
      await scheduleService.updateSchedule(item.id_horario_atencion, payload);
      dirtyMap.value[item.id_horario_atencion] = false;
      savedCount++;
    }
    const shiftRes = await scheduleService.getCurrentShift();
    if (shiftRes?.data) {
      currentShift.value = shiftRes.data;
    }
    showToast(`¡Todos los horarios de la semana (${savedCount} días) han sido actualizados con éxito!`);
  } catch (err: any) {
    console.error('Error al guardar todos los horarios:', err);
    showToast('Ocurrió un problema al guardar algunos horarios.', 'error');
  } finally {
    isSavingAll.value = false;
  }
};

// Plantillas Rápidas
const applyWeekdayPreset = () => {
  // Lunes (1), Martes (2), Miércoles (3), Jueves (4)
  [1, 2, 3, 4].forEach(dia => {
    const found = schedules.value.find(s => s.dia_semana === dia);
    if (found) {
      found.hora_apertura = '19:00';
      found.hora_cierre = '00:30';
      found.minuto_colchon = 30;
      found.activo = true;
      markDirty(found);
    }
  });
  showToast('Plantilla Lun-Jue (19:00 - 00:30) aplicada. Recuerda guardar los cambios.');
};

const applyWeekendPreset = () => {
  // Viernes (5) y Sábado (6)
  [5, 6].forEach(dia => {
    const found = schedules.value.find(s => s.dia_semana === dia);
    if (found) {
      found.hora_apertura = '19:00';
      found.hora_cierre = '01:30';
      found.minuto_colchon = 30;
      found.activo = true;
      markDirty(found);
    }
  });
  showToast('Plantilla Vie-Sáb (19:00 - 01:30) aplicada. Recuerda guardar los cambios.');
};

const applySundayPreset = () => {
  // Domingo (0)
  const found = schedules.value.find(s => s.dia_semana === 0);
  if (found) {
    found.hora_apertura = '19:00';
    found.hora_cierre = '00:30';
    found.minuto_colchon = 30;
    found.activo = true;
    markDirty(found);
  }
  showToast('Plantilla Domingo (19:00 - 00:30) aplicada.');
};

const goToAudit = () => {
  router.push('/general-home/admin/history');
};

onMounted(() => {
  loadData();
});
</script>

<style scoped>
.schedule-admin-page {
  padding: 24px;
  max-width: 1300px;
  margin: 0 auto;
  min-height: 100vh;
  font-family: inherit;
  color: #2c2523;
}

/* Page Header */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 24px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(152, 76, 5, 0.15);
}

.header-title-row {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon-box {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, #e28743 0%, #984c05 100%);
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 6px 14px rgba(152, 76, 5, 0.25);
}

.header-left h1 {
  font-size: 1.5rem;
  font-weight: 800;
  color: #1e1b24;
  margin: 0 0 4px 0;
}

.header-left p {
  font-size: 0.9rem;
  color: #6b635b;
  margin: 0;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-audit {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  background: #ffffff;
  border: 1.5px solid #dcd3cb;
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 600;
  color: #554d46;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-audit:hover {
  background: #f7f2ed;
  border-color: #984c05;
  color: #984c05;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 22px;
  background: linear-gradient(135deg, #e28743 0%, #984c05 100%);
  border: none;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 700;
  color: #ffffff;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(152, 76, 5, 0.25);
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(152, 76, 5, 0.35);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Stats Cards */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
  margin-bottom: 22px;
}

.stat-card {
  background: #ffffff;
  border-radius: 14px;
  padding: 18px 20px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  border: 1px solid #ebe4dc;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 12px;
}

.stat-card.border-active {
  border-left: 4px solid #16a34a;
}

.stat-card.border-inactive {
  border-left: 4px solid #94a3b8;
}

.stat-left {
  display: flex;
  align-items: center;
  gap: 14px;
}

.icon-circle {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.bg-green { background: #dcfce7; color: #16a34a; }
.bg-gray { background: #f1f5f9; color: #64748b; }
.bg-orange { background: #ffedd5; color: #ea580c; }
.bg-amber { background: #fef3c7; color: #d97706; }

.pulse-dot {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7);
  animation: pulse-green 1.8s infinite;
}

@keyframes pulse-green {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.stat-info {
  display: flex;
  flex-direction: column;
}

.stat-label {
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #78716c;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 800;
}

.text-green { color: #15803d; }
.text-gray { color: #475569; }
.text-orange { color: #c2410c; }
.text-amber { color: #b45309; }

.stat-subtext {
  font-size: 0.8rem;
  color: #78716c;
  border-top: 1px dashed #f0eae4;
  padding-top: 8px;
}

/* Info Banner */
.info-banner {
  background: #fbf5ee;
  border: 1px solid #eddcd0;
  border-left: 4px solid #e28743;
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  gap: 14px;
  margin-bottom: 22px;
}

.info-icon {
  color: #e28743;
  flex-shrink: 0;
  margin-top: 2px;
}

.info-content strong {
  display: block;
  font-size: 0.92rem;
  color: #984c05;
  margin-bottom: 4px;
}

.info-content p {
  margin: 0;
  font-size: 0.85rem;
  line-height: 1.45;
  color: #5c5249;
}

/* Quick Actions Bar */
.quick-actions-bar {
  background: #ffffff;
  border-radius: 12px;
  padding: 12px 18px;
  border: 1px solid #ebe4dc;
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 24px;
}

.quick-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #78716c;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.quick-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.btn-quick {
  background: #faf6f2;
  border: 1px solid #e4d7cc;
  padding: 7px 14px;
  border-radius: 8px;
  font-size: 0.82rem;
  font-weight: 600;
  color: #5c5249;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-quick:hover {
  background: #f2e4d6;
  border-color: #984c05;
  color: #984c05;
}

/* Loading Box */
.loading-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 60px 0;
  color: #984c05;
  font-weight: 600;
}

.spin-icon {
  animation: spin 1.2s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* Grid de Horarios por Día */
.schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 20px;
}

.schedule-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #ebe4dc;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.schedule-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

.schedule-card.card-today {
  border: 2px solid #e28743;
  box-shadow: 0 4px 16px rgba(226, 135, 67, 0.18);
}

.schedule-card.card-disabled {
  background: #faf8f6;
  opacity: 0.85;
}

/* Day Header */
.day-header {
  padding: 16px 18px;
  background: #fdfaf6;
  border-bottom: 1px solid #f0eae4;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.day-title-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}

.day-name {
  font-size: 1.15rem;
  font-weight: 800;
  color: #1e1b24;
}

.badge-today {
  background: #e28743;
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 10px;
  letter-spacing: 0.04em;
}

/* Switch Toggle */
.toggle-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .25s;
  border-radius: 24px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .25s;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

input:checked + .slider {
  background-color: #16a34a;
}

input:checked + .slider:before {
  transform: translateX(20px);
}

.toggle-label {
  font-size: 0.8rem;
  font-weight: 700;
  text-transform: uppercase;
}

.label-open { color: #16a34a; }
.label-closed { color: #94a3b8; }

/* Schedule Body */
.schedule-body {
  padding: 18px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  flex-grow: 1;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  color: #6b635b;
}

.time-input {
  width: 100%;
  padding: 8px 10px;
  border: 1.5px solid #dcd3cb;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 700;
  color: #1e1b24;
  background: #ffffff;
  outline: none;
  transition: border-color 0.2s ease;
}

.time-input:focus {
  border-color: #e28743;
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

/* Colchón Section */
.colchon-section {
  background: #fdfaf6;
  border: 1px solid #f0eae4;
  border-radius: 10px;
  padding: 12px;
}

.colchon-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 8px;
}

.colchon-header label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.8rem;
  font-weight: 700;
  color: #6b635b;
}

.colchon-badge {
  background: #fef3c7;
  color: #b45309;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
}

.chips-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 6px;
}

.chip-btn {
  background: #ffffff;
  border: 1px solid #e0d5cb;
  padding: 5px 10px;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 700;
  color: #5c5249;
  cursor: pointer;
  transition: all 0.15s ease;
}

.chip-btn.active {
  background: #e28743;
  border-color: #e28743;
  color: #ffffff;
}

.custom-colchon {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: auto;
}

.colchon-number-input {
  width: 68px;
  padding: 5px 22px 5px 8px;
  border: 1px solid #dcd3cb;
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 700;
  text-align: right;
  outline: none;
}

.custom-colchon .unit {
  position: absolute;
  right: 6px;
  font-size: 0.75rem;
  color: #8c8279;
  font-weight: 600;
  pointer-events: none;
}

/* Shift Preview Box */
.shift-preview-box {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  padding: 10px 12px;
  background: #fff8f0;
  border-radius: 8px;
  font-size: 0.78rem;
  line-height: 1.4;
  color: #7c4414;
  border: 1px dashed #e8cbb4;
}

/* Disabled Body */
.schedule-disabled-body {
  padding: 32px 18px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  flex-grow: 1;
  color: #8c8279;
}

.disabled-icon {
  color: #cbd5e1;
  margin-bottom: 4px;
}

.schedule-disabled-body strong {
  font-size: 0.95rem;
  color: #64748b;
}

.schedule-disabled-body p {
  margin: 0;
  font-size: 0.82rem;
}

/* Card Footer */
.card-footer {
  padding: 12px 18px;
  background: #fdfaf6;
  border-top: 1px solid #f0eae4;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dirty-hint {
  font-size: 0.75rem;
  font-weight: 700;
  color: #d97706;
}

.saved-hint {
  font-size: 0.75rem;
  font-weight: 600;
  color: #16a34a;
}

.btn-save-day {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 14px;
  background: #984c05;
  border: none;
  border-radius: 8px;
  color: #ffffff;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-save-day:hover:not(:disabled) {
  background: #7a3a03;
}

.btn-save-day:disabled {
  background: #d1c7bd;
  cursor: not-allowed;
}

/* Toast */
.toast-notification {
  position: fixed;
  bottom: 24px;
  right: 24px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  border-radius: 10px;
  color: #ffffff;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
  z-index: 9999;
}

.toast-success { background: #15803d; }
.toast-error { background: #b91c1c; }

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(16px);
}

/* Responsive */
@media (max-width: 768px) {
  .schedule-admin-page {
    padding: 16px;
  }

  .header-left h1 {
    font-size: 1.25rem;
  }

  .schedules-grid {
    grid-template-columns: 1fr;
  }

  .header-actions {
    width: 100%;
    justify-content: space-between;
  }

  .btn-primary, .btn-audit {
    flex: 1;
    justify-content: center;
  }
}
</style>
