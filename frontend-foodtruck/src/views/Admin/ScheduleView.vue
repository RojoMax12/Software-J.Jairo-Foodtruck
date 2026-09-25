<template>
  <div class="schedule-admin-page">
    <!-- ENCABEZADO SUPERIOR -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Horarios de Atención y Colchón</h1>
        <p>Configura los turnos de apertura, horarios de cierre y tolerancia nocturna para pedidos continuos.</p>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="goToAudit" title="Ver auditoría de cambios en horarios">
          <History :size="16" />
          <span>Ver Auditoría</span>
        </button>
        <button class="btn-primary" :disabled="isSavingAll" @click="saveAllSchedules">
          <Save :size="16" />
          <span>{{ isSavingAll ? 'Guardando...' : 'Guardar Todos los Días' }}</span>
        </button>
      </div>
    </header>

    <!-- TARJETAS DE RESUMEN SUPERIOR (HOMOGÉNEAS CON SUMMARY-GRID) -->
    <section class="summary-grid">
      <!-- 1. Estado en Vivo -->
      <article class="summary-card">
        <div 
          class="summary-icon-box" 
          :class="currentShift?.es_jornada_activa ? 'bg-summary-green' : 'bg-summary-brown'"
        >
          <Store :size="22" />
        </div>
        <div>
          <span class="summary-label">Estado en Vivo</span>
          <strong 
            class="summary-value" 
            :class="currentShift?.es_jornada_activa ? 'text-status-open' : 'text-status-closed'"
          >
            {{ currentShift?.es_jornada_activa ? 'Abierto Ahora' : 'Cerrado Ahora' }}
          </strong>
          <p class="summary-helper">
            {{ currentShift?.dia || 'Hoy' }}: {{ currentShift?.hora_apertura || '--:--' }} a {{ currentShift?.hora_cierre || '--:--' }}
          </p>
        </div>
      </article>

      <!-- 2. Días Operativos -->
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-orange">
          <CalendarCheck :size="22" />
        </div>
        <div>
          <span class="summary-label">Días Operativos</span>
          <strong class="summary-value">{{ activeDaysCount }} / 7 días</strong>
          <p class="summary-helper">{{ 7 - activeDaysCount }} día(s) de descanso semanal</p>
        </div>
      </article>

      <!-- 3. Colchón Promedio / Hoy -->
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <Hourglass :size="22" />
        </div>
        <div>
          <span class="summary-label">Colchón Nocturno</span>
          <strong class="summary-value">{{ todaySchedule?.minuto_colchon || 30 }} min</strong>
          <p class="summary-helper">
            Tolerancia hoy hasta: {{ calculateExtendedClosingTime(todaySchedule?.hora_cierre, todaySchedule?.minuto_colchon) }} hrs
          </p>
        </div>
      </article>
    </section>

    <!-- BANNER INFORMATIVO SOBRE HORARIO DE COLCHÓN -->
    <div class="info-banner-panel">
      <div class="info-icon-badge">
        <Info :size="20" />
      </div>
      <div class="info-content">
        <strong>¿Cómo funciona el Horario de Colchón?</strong>
        <p>
          El <b>colchón</b> es una tolerancia posterior al horario oficial de cierre. Permite seguir procesando pedidos de última hora y asegura que las ventas emitidas pasada la medianoche queden agrupadas contablemente en el <b>mismo turno de trabajo</b> sin reiniciar comandas.
        </p>
      </div>
    </div>

    <!-- BARRA DE ACCIONES RÁPIDAS Y PLANTILLAS -->
    <div class="quick-presets-bar">
      <div class="quick-title">
        <Sparkles :size="16" />
        <span>Plantillas rápidas:</span>
      </div>
      <div class="quick-chips-group">
        <button type="button" class="preset-chip" @click="applyWeekdayPreset">
          Lun-Jue (19:00 a 00:30 · 30m)
        </button>
        <button type="button" class="preset-chip" @click="applyWeekendPreset">
          Vie-Sáb (19:00 a 01:30 · 30m)
        </button>
        <button type="button" class="preset-chip" @click="applySundayPreset">
          Domingo (19:00 a 00:30 · 30m)
        </button>
      </div>
    </div>

    <!-- ESTADO DE CARGA -->
    <div v-if="isLoading" class="loading-state-box">
      <RefreshCw :size="28" class="spinning" />
      <span>Cargando configuración de horarios...</span>
    </div>

    <!-- GRILLA DE DÍAS -->
    <div v-else class="schedules-grid">
      <div 
        v-for="item in sortedSchedules" 
        :key="item.id_horario_atencion"
        class="schedule-card"
        :class="{ 'card-today': isToday(item.dia_semana), 'card-disabled': !item.activo }"
      >
        <!-- Cabecera del Día -->
        <div class="day-card-header">
          <div class="day-title-wrap">
            <span class="day-name">{{ getDayName(item.dia_semana) }}</span>
            <span v-if="isToday(item.dia_semana)" class="today-pill">HOY</span>
          </div>

          <!-- Switch Activo / Cerrado -->
          <label class="status-switch" :class="item.activo ? 'active' : 'inactive'">
            <input 
              type="checkbox" 
              v-model="item.activo"
              @change="markDirty(item)"
            />
            <span class="slider"></span>
            <span class="status-text">
              {{ item.activo ? 'Abierto' : 'Cerrado' }}
            </span>
          </label>
        </div>

        <!-- Cuerpo del Horario -->
        <div v-if="item.activo" class="day-card-body">
          <div class="times-row">
            <div class="time-field">
              <label>
                <Sun :size="13" />
                <span>Apertura:</span>
              </label>
              <input 
                type="time" 
                v-model="item.hora_apertura" 
                class="time-input"
                @input="markDirty(item)"
              />
            </div>

            <div class="time-field">
              <label>
                <Moon :size="13" />
                <span>Cierre:</span>
              </label>
              <input 
                type="time" 
                v-model="item.hora_cierre" 
                class="time-input"
                @input="markDirty(item)"
              />
            </div>
          </div>

          <!-- Minutos de Colchón -->
          <div class="colchon-box">
            <div class="colchon-label-row">
              <label>
                <Hourglass :size="13" />
                <span>Minutos de Colchón:</span>
              </label>
              <span class="colchon-tag">{{ item.minuto_colchon || 0 }} min</span>
            </div>

            <div class="chips-selector-row">
              <button 
                v-for="min in [15, 30, 45, 60]" 
                :key="min"
                type="button"
                class="minute-chip"
                :class="{ active: item.minuto_colchon === min }"
                @click="setColchon(item, min)"
              >
                {{ min }}m
              </button>
              <div class="custom-minute-input-wrap">
                <input 
                  type="number" 
                  min="0" 
                  max="180" 
                  v-model.number="item.minuto_colchon"
                  class="custom-min-input"
                  placeholder="Otro"
                  @input="markDirty(item)"
                />
                <span class="input-unit">m</span>
              </div>
            </div>
          </div>

          <!-- Resumen de Turno -->
          <div class="shift-preview-note">
            <Clock :size="14" />
            <span>
              Turno: <b>{{ formatTimeShort(item.hora_apertura) }}</b> a <b>{{ formatTimeShort(item.hora_cierre) }}</b> hrs. 
              Tolerancia hasta las <b>{{ calculateExtendedClosingTime(item.hora_cierre, item.minuto_colchon) }} hrs</b>.
            </span>
          </div>
        </div>

        <!-- Estado Inactivo / Descanso -->
        <div v-else class="day-disabled-body">
          <Coffee :size="32" class="disabled-icon" />
          <strong>Día de descanso programado</strong>
          <p>El local permanecerá cerrado para atención y pedidos.</p>
        </div>

        <!-- Pie de Tarjeta -->
        <div class="day-card-footer">
          <span v-if="dirtyMap[item.id_horario_atencion]" class="dirty-hint">
            ⚠️ Cambios sin guardar
          </span>
          <span v-else class="saved-hint">
            ✓ Guardado
          </span>

          <button 
            type="button"
            class="btn-save-day" 
            :disabled="savingId === item.id_horario_atencion || !dirtyMap[item.id_horario_atencion]"
            @click="saveDay(item)"
          >
            <Check :size="14" />
            <span>{{ savingId === item.id_horario_atencion ? 'Guardando...' : 'Guardar Día' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- TOAST DE NOTIFICACIÓN -->
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

const dayNames: Record<number, string> = {
  1: 'Lunes',
  2: 'Martes',
  3: 'Miércoles',
  4: 'Jueves',
  5: 'Viernes',
  6: 'Sábado',
  0: 'Domingo',
};

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

const loadData = async () => {
  isLoading.value = true;
  try {
    const [schedRes, shiftRes] = await Promise.all([
      scheduleService.getSchedules(),
      scheduleService.getCurrentShift().catch(() => null),
    ]);

    const rawSchedules = schedRes.data || [];
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
    showToast('No se pudo guardar el horario. Intenta nuevamente.', 'error');
  } finally {
    savingId.value = null;
  }
};

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
    showToast(`¡Todos los horarios de la semana (${savedCount} días) fueron actualizados!`);
  } catch (err: any) {
    console.error('Error al guardar todos los horarios:', err);
    showToast('Ocurrió un problema al guardar algunos horarios.', 'error');
  } finally {
    isSavingAll.value = false;
  }
};

const applyWeekdayPreset = () => {
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
  showToast('Plantilla Lun-Jue aplicada. Recuerda guardar los cambios.');
};

const applyWeekendPreset = () => {
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
  showToast('Plantilla Vie-Sáb aplicada. Recuerda guardar los cambios.');
};

const applySundayPreset = () => {
  const found = schedules.value.find(s => s.dia_semana === 0);
  if (found) {
    found.hora_apertura = '19:00';
    found.hora_cierre = '00:30';
    found.minuto_colchon = 30;
    found.activo = true;
    markDirty(found);
  }
  showToast('Plantilla Domingo aplicada. Recuerda guardar los cambios.');
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
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER PRINCIPAL
==================================================== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-copy h1 {
  color: var(--DC-brown, #513119);
  font-size: 2.2rem;
  line-height: 1.1;
  margin: 0 0 0.4rem 0;
}

.header-copy p {
  margin: 0;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.92rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  padding: 0.65rem 1.1rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 700;
  font-size: 0.88rem;
}

.btn-secondary:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.15);
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.2);
}

.btn-primary:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* ====================================================
   RESUMEN SUPERIOR (SUMMARY-GRID HOMOGÉNEO)
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.summary-card {
  background: white;
  border-radius: 18px;
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
  padding: 1.1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.summary-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #2c2724);
  font-size: 1.45rem;
  line-height: 1.15;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
  margin: 0;
}

.text-status-open { color: #15803d; }
.text-status-closed { color: var(--DC-text-gray, #7c7468); }

/* ====================================================
   BANNER INFORMATIVO Y PRESETS
==================================================== */
.info-banner-panel {
  background: #fffdfa;
  border: 1px solid rgba(226, 135, 67, 0.25);
  border-radius: 16px;
  padding: 1rem 1.25rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
}

.info-icon-badge {
  color: var(--DC-orange, #e28743);
  margin-top: 2px;
  flex-shrink: 0;
}

.info-content strong {
  display: block;
  font-size: 0.9rem;
  color: var(--DC-brown, #513119);
  margin-bottom: 0.25rem;
}

.info-content p {
  margin: 0;
  font-size: 0.84rem;
  line-height: 1.45;
  color: var(--DC-text-gray, #7c7468);
}

.quick-presets-bar {
  background: white;
  border-radius: 16px;
  padding: 0.85rem 1.25rem;
  border: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.02);
}

.quick-title {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.quick-chips-group {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.preset-chip {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.1);
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  cursor: pointer;
  transition: all 0.2s ease;
}

.preset-chip:hover {
  background: rgba(226, 135, 67, 0.12);
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-brown, #513119);
}

/* ====================================================
   TARJETAS DE DÍAS Y HORARIOS
==================================================== */
.schedules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 1.25rem;
}

.schedule-card {
  background: white;
  border-radius: 16px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.schedule-card:hover {
  transform: translateY(-2px);
  border-color: rgba(226, 135, 67, 0.35);
}

.schedule-card.card-today {
  border: 2px solid var(--DC-orange, #e28743);
  box-shadow: 0 6px 20px rgba(226, 135, 67, 0.15);
}

.schedule-card.card-disabled {
  background: #fdfaf7;
  opacity: 0.85;
}

/* Day Header */
.day-card-header {
  padding: 1rem 1.15rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.day-title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}

.day-name {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
}

.today-pill {
  background: var(--DC-orange, #e28743);
  color: white;
  font-size: 0.68rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 999px;
  letter-spacing: 0.05em;
}

/* Switch de Estado */
.status-switch {
  display: inline-flex;
  align-items: center;
  gap: 0.65rem;
  padding: 0.28rem 0.65rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.status-switch input { display: none; }

.slider {
  position: relative;
  width: 34px;
  height: 18px;
  border-radius: 999px;
  transition: all 0.25s ease;
  flex-shrink: 0;
}

.slider::before {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 0.25s ease;
}

.status-switch.active {
  color: #15803d;
  background: rgba(62, 165, 93, 0.12);
}

.status-switch.active .slider {
  background: #16a34a;
}

.status-switch.active .slider::before {
  transform: translateX(16px);
}

.status-switch.inactive {
  color: var(--DC-text-gray, #7c7468);
  background: var(--DC-bg-gray, #f8f6f3);
}

.status-switch.inactive .slider {
  background: #cbd5e1;
}

.status-switch.inactive .slider::before {
  transform: translateX(0);
}

/* Day Body */
.day-card-body {
  padding: 1.15rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex-grow: 1;
}

.times-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.time-field {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.time-field label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.time-input {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  font-size: 0.92rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
  background: var(--DC-bg-gray, #f8f6f3);
  outline: none;
  transition: border-color 0.2s ease;
}

.time-input:focus {
  border-color: var(--DC-orange, #e28743);
}

/* Colchón Box */
.colchon-box {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.colchon-label-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.colchon-label-row label {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.76rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

.colchon-tag {
  background: #fff4e6;
  color: var(--DC-orange, #e28743);
  font-size: 0.74rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
}

.chips-selector-row {
  display: flex;
  align-items: center;
  gap: 5px;
}

.minute-chip {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.12);
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  transition: all 0.15s ease;
}

.minute-chip.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.custom-minute-input-wrap {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: auto;
}

.custom-min-input {
  width: 60px;
  padding: 3px 18px 3px 6px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  text-align: right;
  background: white;
  outline: none;
}

.input-unit {
  position: absolute;
  right: 5px;
  font-size: 0.72rem;
  color: #999;
  pointer-events: none;
}

/* Shift Preview */
.shift-preview-note {
  display: flex;
  align-items: flex-start;
  gap: 6px;
  padding: 0.55rem 0.75rem;
  background: #fff9f2;
  border-radius: 10px;
  font-size: 0.76rem;
  line-height: 1.4;
  color: var(--DC-brown, #513119);
  border: 1px dashed rgba(226, 135, 67, 0.3);
}

.shift-preview-note svg {
  flex-shrink: 0;
  margin-top: 1px;
}

/* Estado Descanso */
.day-disabled-body {
  padding: 2.5rem 1rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  flex-grow: 1;
  color: var(--DC-text-gray, #7c7468);
}

.disabled-icon {
  color: #cbd5e1;
}

.day-disabled-body strong {
  font-size: 0.9rem;
  color: var(--DC-gray, #2c2724);
}

.day-disabled-body p {
  margin: 0;
  font-size: 0.78rem;
}

/* Footer de Tarjeta */
.day-card-footer {
  padding: 0.75rem 1.15rem;
  background: #fffdfa;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.dirty-hint {
  font-size: 0.72rem;
  font-weight: 700;
  color: #d97706;
}

.saved-hint {
  font-size: 0.72rem;
  font-weight: 700;
  color: #16a34a;
}

.btn-save-day {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.4rem 0.85rem;
  background: var(--DC-brown, #513119);
  border: none;
  border-radius: 8px;
  color: white;
  font-size: 0.78rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-save-day:hover:not(:disabled) {
  background: #3e2411;
}

.btn-save-day:disabled {
  background: #e2deda;
  color: #9c948e;
  cursor: not-allowed;
}

/* Loading Box */
.loading-state-box {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 4rem 1rem;
  color: var(--DC-orange, #e28743);
  font-weight: 700;
}

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
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
  border-radius: 12px;
  color: white;
  font-weight: 700;
  font-size: 0.88rem;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
  z-index: 9999;
}

.toast-success { background: #15803d; }
.toast-error { background: #b91c1c; }

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.25s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* Responsive */
@media (max-width: 960px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .schedule-admin-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-actions {
    flex-direction: column;
    width: 100%;
  }

  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }

  .schedules-grid {
    grid-template-columns: 1fr;
  }
}
</style>