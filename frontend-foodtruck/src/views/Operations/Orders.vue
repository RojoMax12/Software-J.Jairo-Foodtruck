<template>
  <div class="orders-container">
    <header class="orders-header">
      <div class="header-main-info">
        <h1 class="orders-title">Gestión de Pedidos & KDS</h1>
        <p class="orders-description">Monitorea y despacha pedidos en tiempo real organizados por turnos operativos.</p>
      </div>

      <!-- BANNER DE ESTADO DEL TURNO OPERATIVO -->
      <div class="shift-status-banner" :class="shiftWindow?.es_jornada_activa ? 'banner-active' : 'banner-inactive'">
        <div class="shift-banner-left">
          <div class="shift-live-indicator">
            <span class="live-dot" v-if="shiftWindow?.es_jornada_activa"></span>
            <strong>{{ shiftWindow?.es_jornada_activa ? '🟢 TURNO EN VIVO' : (shiftWindow?.es_dia_cerrado ? '🔴 DÍA CERRADO (DESCANSO)' : '⚪ FUERA DE HORARIO') }}</strong>
            <span class="shift-day-tag">{{ shiftWindow?.dia || 'Hoy' }}</span>
          </div>
          <div class="shift-schedule-info">
            <span>🕒 Horario: <strong>{{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }}</strong></span>
            <span>📅 Fecha Turno: <strong>{{ shiftDateFormatted }}</strong></span>
          </div>
        </div>
        <div class="shift-banner-right">
          <div class="shift-comandas-summary">
            <span class="comandas-label">Comandas del Turno</span>
            <strong class="comandas-val">#1 - #{{ shiftOrdersCount }}</strong>
          </div>
        </div>
      </div>
    </header>

    <div class="status-cards">
      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-unpaid">
            <ClipboardCheck :size="24" />
          </div>
          <span class="card-label">Total pedidos</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.totalOrders }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-unpaid">
            <TrendingUp :size="24" />
          </div>
          <span class="card-label">Venta Total ($)</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ formatPrice(stats.totalAmount) }}</span>
          <span class="card-subtext">Pesos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-paid">
            <DollarSign :size="24" />
          </div>
          <span class="card-label">Total Recaudado ($)</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ formatPrice(stats.totalPaid) }}</span>
          <span class="card-subtext">Pesos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-paid">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Total Pagados</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.paid }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-shipping">
            <Truck :size="24" />
          </div>
          <span class="card-label">Total Entregados</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.delivered }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>
    </div>

    <!-- PESTAÑAS RÁPIDAS DE ESTADO KDS -->
    <div class="status-quick-tabs">
      <button 
        class="status-tab-btn" 
        :class="{ active: statusFilter === 'all' }" 
        @click="selectStatus('all')"
      >
        <span>Todos</span>
        <span class="tab-count-pill">{{ filteredByShiftOrders.length }}</span>
      </button>
      <button 
        class="status-tab-btn tab-pending" 
        :class="{ active: statusFilter === 'Pendiente' }" 
        @click="selectStatus('Pendiente')"
      >
        <span>🟡 Pendientes</span>
        <span class="tab-count-pill">{{ countByStatus(1) }}</span>
      </button>
      <button 
        class="status-tab-btn tab-prep" 
        :class="{ active: statusFilter === 'En preparación' }" 
        @click="selectStatus('En preparación')"
      >
        <span>🔵 En Preparación</span>
        <span class="tab-count-pill">{{ countByStatus(2) }}</span>
      </button>
      <button 
        class="status-tab-btn tab-ready" 
        :class="{ active: statusFilter === 'Listo' }" 
        @click="selectStatus('Listo')"
      >
        <span>🟢 Listos</span>
        <span class="tab-count-pill">{{ countByStatus(3) }}</span>
      </button>
      <button 
        class="status-tab-btn tab-delivered" 
        :class="{ active: statusFilter === 'Entregado' }" 
        @click="selectStatus('Entregado')"
      >
        <span>✓ Entregados</span>
        <span class="tab-count-pill">{{ countByStatus(4) }}</span>
      </button>
      <button 
        class="status-tab-btn tab-cancelled" 
        :class="{ active: statusFilter === 'Cancelado' }" 
        @click="selectStatus('Cancelado')"
      >
        <span>❌ Cancelados</span>
        <span class="tab-count-pill">{{ countByStatus(5) }}</span>
      </button>
    </div>

    <div class="main-table-card">
      <div class="table-actions">
        <div class="actions-left">
          <!-- SELECTOR DE MODO DE TURNO -->
          <div class="shift-mode-selector">
            <button 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'current' }" 
              @click="setShiftMode('current')"
              title="Ver pedidos del turno en curso"
            >
              <Zap :size="15" />
              <span>Turno Actual</span>
            </button>
            <button 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'previous' }" 
              @click="setShiftMode('previous')"
              title="Ver pedidos del turno anterior"
            >
              <History :size="15" />
              <span>Turno Anterior</span>
            </button>
            <button 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'custom' }" 
              @click="setShiftMode('custom')"
              title="Ver todos o elegir fecha específica"
            >
              <CalendarIcon :size="15" />
              <span>Por Fecha</span>
            </button>
          </div>

          <div class="search-box">
            <Search :size="18" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Buscar por comanda, nombre, teléfono..."
            />
          </div>

          <div class="date-filter-box" v-if="shiftMode === 'custom' || canEditDate">
            <CalendarIcon :size="18" class="date-filter-icon" />
            <input 
              type="date" 
              v-model="selectedDate" 
              class="mobile-date-input"
              :disabled="!canEditDate"
              :class="{ 'picker-disabled': !canEditDate }"
              :title="canEditDate ? 'Buscar por fecha' : 'Solo ver pedidos de hoy'"
            />
          </div>

          <button 
            class="btn-live-toggle" 
            :class="{ active: autoRefresh }" 
            @click="autoRefresh = !autoRefresh"
            :title="autoRefresh ? 'Auto-actualización en vivo activa (cada 20s)' : 'Auto-actualización pausada'"
          >
            <RefreshCw :size="16" :class="{ spinning: isLoading || isRefreshingBackground }" />
            <span>{{ autoRefresh ? `En vivo (${secondsCountdown}s)` : 'En vivo Pausado' }}</span>
          </button>
        </div>
      </div>

      <!-- VISTA TABLA (ESCRITORIO & TABLET) -->
      <div class="table-responsive desktop-table-view">
        <table class="orders-table">
          <thead>
            <tr>
              <th @click="sortBy('id')">
                <div class="header-content">
                  Comanda / Turno <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
                </div>
              </th>
              <th @click="sortBy('distributor')">
                <div class="header-content">
                  Cliente <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
                </div>
              </th>
              <th @click="sortBy('status')">
                <div class="header-content">
                  Estado <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
                </div>
              </th>
              <th @click="sortBy('date')">
                <div class="header-content">
                  Hora / Tiempo <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
                </div>
              </th>
              <th @click="sortBy('total')">
                <div class="header-content">
                  Total <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'total' }" />
                </div>
              </th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading" v-for="n in 5" :key="'skel-' + n" class="skeleton-row">
              <td><div class="skeleton-pill width-50"></div></td>
              <td><div class="skeleton-pill width-120"></div></td>
              <td><div class="skeleton-pill width-100"></div></td>
              <td><div class="skeleton-pill width-80"></div></td>
              <td><div class="skeleton-pill width-70"></div></td>
              <td><div class="skeleton-pill width-90"></div></td>
            </tr>
            <tr v-else-if="sortedOrders.length === 0">
              <td colspan="6" class="text-center padding-large">
                <div class="empty-state">
                  <Package :size="48" class="empty-icon" />
                  <p>No se encontraron pedidos para este turno o filtros.</p>
                  <button @click="() => fetchOrders()" class="btn-retry">Actualizar datos</button>
                </div>
              </td>
            </tr>
            <tr v-else v-for="order in paginatedOrders" :key="order.id">
              <td>
                <div class="comanda-cell">
                  <span class="comanda-badge">Comanda #{{ order.id }}</span>
                  <small class="order-real-id">ID #{{ order.real_id }}</small>
                </div>
              </td>
              <td>
                <div class="client-cell">
                  <strong class="client-name">{{ order.distributor }}</strong>
                  <span v-if="order.phone" class="client-phone"><Phone :size="12" /> {{ order.phone }}</span>
                </div>
              </td>
              <td>
                <div class="badges-cell">
                  <span class="status-badge" :class="getStatusClass(order.status, order.rawStatusId)">
                    {{ order.status }}
                  </span>
                  <span class="status-badge" :class="Number(order.id_estado_pago) === 2 ? 'status-paid' : 'status-unpaid'">
                    {{ Number(order.id_estado_pago) === 2 ? 'PAGADO' : 'POR PAGAR' }}
                  </span>
                </div>
              </td>
              <td>
                <div class="date-content">
                  <div class="time-primary">
                    <Clock :size="14" class="time-icon" />
                    <strong>{{ order.time }}</strong>
                  </div>
                  <div class="date-secondary">
                    <span class="elapsed-badge" :class="getElapsedBadgeClass(order.elapsedMinutes)" :title="`Ingresó hace ${order.elapsedMinutes} minutos`">
                      {{ order.elapsedMinutes }}m
                    </span>
                    <span class="date-text">{{ order.date }}</span>
                  </div>
                </div>
              </td>
              <td class="bold-text">${{ formatPrice(order.total) }}</td>
              <td>
                <div class="actions-content">
                  <button class="btn-action btn-detail" @click="openModal(order.id)">
                    <Eye :size="18" />
                    <span>Detalle</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- VISTA TARJETAS MÓVILES (SMARTPHONES) -->
      <div class="mobile-cards-view">
        <div v-if="isLoading" class="mobile-skeleton-container">
          <div v-for="n in 4" :key="'mob-skel-' + n" class="mobile-card-skeleton">
            <div class="skeleton-pill width-80"></div>
            <div class="skeleton-pill width-120 margin-top-4"></div>
            <div class="skeleton-pill width-100 margin-top-4"></div>
          </div>
        </div>

        <div v-else-if="sortedOrders.length === 0" class="mobile-empty-state">
          <Package :size="40" class="empty-icon" />
          <p>No se encontraron pedidos para esta fecha o filtros.</p>
          <button @click="() => fetchOrders()" class="btn-retry">Actualizar datos</button>
        </div>

        <div v-else class="mobile-cards-list">
          <div 
            v-for="order in paginatedOrders" 
            :key="'mob-' + order.id" 
            class="mobile-order-card"
            @click="openModal(order.id)"
          >
            <div class="mobile-card-top">
              <div class="mobile-id-box">
                <span class="mobile-order-id">Comanda #{{ order.id }}</span>
                <span class="mobile-real-id">ID #{{ order.real_id }}</span>
                <span class="elapsed-badge" :class="getElapsedBadgeClass(order.elapsedMinutes)">
                  <Clock :size="11" /> {{ order.time }} ({{ order.elapsedMinutes }}m)
                </span>
              </div>
              <div class="mobile-card-badges">
                <span class="status-badge" :class="getStatusClass(order.status, order.rawStatusId)">
                  {{ order.status }}
                </span>
                <span class="status-badge" :class="Number(order.id_estado_pago) === 2 ? 'status-paid' : 'status-unpaid'">
                  {{ Number(order.id_estado_pago) === 2 ? 'PAGADO' : 'POR PAGAR' }}
                </span>
              </div>
            </div>

            <div class="mobile-card-body">
              <div class="mobile-client-line">
                <User :size="15" class="mobile-icon" />
                <span class="mobile-client-name">{{ order.distributor || 'Cliente' }}</span>
              </div>
              <div v-if="order.phone" class="mobile-phone-line">
                <Phone :size="13" class="mobile-icon" />
                <span>{{ order.phone }}</span>
              </div>
            </div>

            <div class="mobile-card-footer">
              <div class="mobile-price-section">
                <span class="price-title">Total</span>
                <strong class="price-value">${{ formatPrice(order.total) }}</strong>
              </div>

              <div class="mobile-actions-row" @click.stop>
                <button class="btn-mobile-detail" @click="openModal(order.id)">
                  <Eye :size="15" /> <span>Detalle</span>
                </button>

                <button 
                  v-if="Number(order.rawStatusId) === 1" 
                  class="btn-mobile-advance advance-step-1" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>A Cocina</span> <ArrowRight :size="13" />
                </button>
                <button 
                  v-else-if="Number(order.rawStatusId) === 2" 
                  class="btn-mobile-advance advance-step-2" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>Listo</span> <ArrowRight :size="13" />
                </button>
                <button 
                  v-else-if="Number(order.rawStatusId) === 3" 
                  class="btn-mobile-advance advance-step-3" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>Entregar</span> <CheckCircle :size="13" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Control de Paginación -->
      <div v-if="sortedOrders.length > 0" class="pagination-footer">
        <div class="pagination-info">
          <span>Mostrando <strong>{{ paginationInfo.start }}</strong> - <strong>{{ paginationInfo.end }}</strong> de <strong>{{ paginationInfo.total }}</strong> pedidos</span>
        </div>

        <div class="pagination-controls">
          <div class="page-size-selector">
            <span>Mostrar:</span>
            <select v-model="itemsPerPage" class="select-page-size">
              <option v-for="size in pageSizeOptions" :key="size" :value="size">{{ size }} por pág.</option>
            </select>
          </div>

          <div class="page-buttons">
            <button 
              class="btn-page-nav" 
              :disabled="currentPage === 1" 
              @click="prevPage" 
              title="Página anterior"
            >
              <ChevronLeft :size="16" />
            </button>

            <button
              v-for="(page, idx) in displayedPages"
              :key="idx"
              class="btn-page-num"
              :class="{ 'active': currentPage === page, 'ellipsis': page === '...' }"
              :disabled="page === '...'"
              @click="goToPage(page)"
            >
              {{ page }}
            </button>

            <button 
              class="btn-page-nav" 
              :disabled="currentPage === totalPages" 
              @click="nextPage" 
              title="Página siguiente"
            >
              <ChevronRight :size="16" />
            </button>
          </div>
        </div>
      </div>
    </div>

    <OrdersDetailModal 
      v-if="isModalOpen" 
      :order-id="selectedOrderId" 
      :real-id="selectedOrder?.real_id"
      :distributor="selectedOrder?.distributor"
      :phone="selectedOrder?.phone"
      :status="selectedOrder?.status"
      :status-id="selectedOrder?.rawStatusId"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      :total="selectedOrder?.total"
      :raw-order="selectedOrder"
      @close="closeModal" 
      @status-changed="() => fetchOrders(true)"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import OrdersDetailModal from './OrdersDetailModal.vue';
import {
  ClipboardCheck, Package, Truck, CheckCircle, Search, Filter,
  ChevronDown, Calendar as CalendarIcon, Eye, ChevronsUpDown,
  RefreshCw, Clock, ArrowRight, ChevronLeft, ChevronRight, User, Phone,
  Zap, History, TrendingUp, DollarSign
} from 'lucide-vue-next';
import orderService  from '@/services/orderService';
import cashFlowService, { type ShiftWindow, fetchShiftWindowFromBackend } from '@/services/cashFlowService';

const orders = ref<any[]>([]);
const isLoading = ref(true);
const isRefreshingBackground = ref(false);
const autoRefresh = ref(true);
const secondsCountdown = ref(20);
const refreshInterval = ref<any>(null);
const countdownTimer = ref<any>(null);

const userRole = ref<number | null>(null);
const shiftWindow = ref<ShiftWindow | null>(null);
const shiftMode = ref<'current' | 'previous' | 'custom'>('current');

const getElapsedMinutes = (rawDateStr?: string) => {
  if (!rawDateStr) return 0;
  let dateToParse = rawDateStr;
  if (rawDateStr.includes(' ') && !rawDateStr.includes('T')) {
    dateToParse = rawDateStr.replace(' ', 'T');
  }
  const orderTime = new Date(dateToParse).getTime();
  if (isNaN(orderTime)) return 0;
  const diffMs = Date.now() - orderTime;
  return Math.max(0, Math.floor(diffMs / 60000));
};

const getElapsedBadgeClass = (minutes: number) => {
  if (minutes > 30) return 'elapsed-danger';
  if (minutes > 15) return 'elapsed-warning';
  return 'elapsed-ok';
};

const advanceOrderStatus = async (order: any) => {
  const nextStatusMap: Record<number, number> = {
    1: 2, // Pendiente -> En preparación
    2: 3, // En preparación -> Listo
    3: 4  // Listo -> Entregado
  };

  const nextId = nextStatusMap[Number(order.rawStatusId)];
  if (!nextId) return;

  try {
    await orderService.updateOrder(order.real_id, { id_estado_pedido: nextId });
    await fetchOrders(true);
  } catch (err) {
    console.error('Error al avanzar estado de pedido:', err);
  }
};

const getTodayString = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const getShiftDateString = (inputDate?: string | Date) => {
  let dateObj: Date;
  if (!inputDate) {
    dateObj = new Date();
  } else if (typeof inputDate === 'string') {
    let dateToParse = inputDate;
    if (inputDate.includes(' ') && !inputDate.includes('T')) {
      dateToParse = inputDate.replace(' ', 'T');
    }
    dateObj = new Date(dateToParse);
  } else {
    dateObj = new Date(inputDate);
  }

  if (isNaN(dateObj.getTime())) {
    dateObj = new Date();
  }

  // Si la hora es de madrugada (00:00 AM - 05:59 AM), la jornada pertenece al turno que comenzó ayer
  if (dateObj.getHours() < 6) {
    dateObj.setDate(dateObj.getDate() - 1);
  }

  const year = dateObj.getFullYear();
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const day = String(dateObj.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`;
};

const selectedDate = ref(getShiftDateString());

const loadShiftWindow = async () => {
  try {
    const sw = await fetchShiftWindowFromBackend();
    shiftWindow.value = sw;
    if (shiftMode.value === 'current' && sw?.shift_date) {
      selectedDate.value = sw.shift_date;
    }
  } catch (e) {
    console.warn('Error al cargar horario de turno en Orders:', e);
  }
};

const shiftDateFormatted = computed(() => {
  const d = selectedDate.value || shiftWindow.value?.shift_date;
  if (!d) return 'Hoy';
  const parts = d.split('-');
  if (parts.length === 3) {
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }
  return d;
});

const setShiftMode = (mode: 'current' | 'previous' | 'custom') => {
  shiftMode.value = mode;
  if (mode === 'current') {
    selectedDate.value = shiftWindow.value?.shift_date || getShiftDateString();
  } else if (mode === 'previous') {
    const d = new Date();
    d.setDate(d.getDate() - 1);
    selectedDate.value = getShiftDateString(d);
  }
};

const checkUserRole = () => {
  const userParsed = localStorage.getItem('user');
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed);
      userRole.value = userObj.id_rol;
    } catch (e) {
      console.error('Error parseando usuario:', e);
    }
  }
};

const canEditDate = computed(() => userRole.value === 1);

// Mapa reactivo BDD
const statusMap = ref<Map<number, string>>(new Map([
  [1, 'En validación'],
  [2, 'En preparación'],
  [3, 'En despacho'],
  [4, 'Entregado'],
  [5, 'Pendiente'],
  [6, 'Por pagar'],
  [7, 'Pagada'],
  [8, 'Cancelado']
]));

const fetchOrders = async (silent: boolean | unknown = false) => {
  const isSilent = silent === true;
  if (!isSilent && orders.value.length === 0) {
    isLoading.value = true;
  } else {
    isRefreshingBackground.value = true;
  }

  try {
    const res = await orderService.getOrders();
    const rawOrders = res.data || [];

    const DEFAULT_NAMES: Record<number, string> = {
      1: 'Pendiente',
      2: 'En preparación',
      3: 'Listo',
      4: 'Entregado',
      5: 'Cancelado'
    };

    orders.value = rawOrders.map((o: any) => {
      const statusId = Number(o.id_estado_pedido || 1);
      const customerName = o.nombre_persona || (o.usuario?.nombre_empresa) || 'Cliente Anónimo';
      const dt = parseDateTime(o.fecha || o.created_at);
      const shiftDate = o.shift_date || getShiftDateString(o.fecha || o.created_at);
      
      const orderTotal = Number(o.total || 0) > 0
        ? Number(o.total)
        : (o.detalles || []).reduce((acc: number, d: any) => acc + (Number(d.cantidad || 1) * Number(d.precio_unitario || d.precio || 0)), 0);
  
      return {
        id: o.numero_pedido_dia || o.id_pedido,
        real_id: o.id_pedido,
        distributor: customerName,
        customer: customerName,
        phone: o.numero_telefono || o.telefono || '',
        status: o.estado_pedido?.nombre || DEFAULT_NAMES[statusId] || `Estado #${statusId}`,
        total: orderTotal,
        date: dt.date,
        time: dt.time,
        shiftDate: shiftDate,
        rawStatusId: statusId,
        elapsedMinutes: getElapsedMinutes(o.fecha || o.created_at),
        id_estado_pago: Number(o.id_estado_pago || 1),
        metodo_pago: o.metodo_pago || 'Efectivo',
        detalles: o.detalles || []
      };
    });

  } catch (error) {
    console.error('Error al cargar pedidos desde API:', error);
  } finally {
    isLoading.value = false;
    isRefreshingBackground.value = false;
  }
};

const parseDateTime = (dateString?: string) => {
  if (!dateString) return { date: 'Sin fecha', time: '00:00' };

  let dateToParse = dateString;
  if (dateString.includes(' ') && !dateString.includes('T')) {
    dateToParse = dateString.replace(' ', 'T');
  }

  const dateObj = new Date(dateToParse);

  if (isNaN(dateObj.getTime())) {
    return { date: 'Sin fecha', time: '00:00' };
  }

  const day = String(dateObj.getDate()).padStart(2, '0');
  const month = String(dateObj.getMonth() + 1).padStart(2, '0');
  const year = dateObj.getFullYear();

  const hours = String(dateObj.getHours()).padStart(2, '0');
  const minutes = String(dateObj.getMinutes()).padStart(2, '0');

  return {
    date: `${day}/${month}/${year}`,
    time: `${hours}:${minutes}`
  };
};

const formatDate = (dateString?: string) => {
  return parseDateTime(dateString).date;
};

// Pedidos pertenecientes a la jornada/turno seleccionado
const filteredByShiftOrders = computed(() => {
  let result = orders.value;
  if (selectedDate.value) {
    const [year, month, day] = selectedDate.value.split('-');
    const formattedSelectedDate = `${day}/${month}/${year}`;
    result = result.filter((o: any) => o.shiftDate === selectedDate.value || o.date === formattedSelectedDate);
  }
  return result;
});

const shiftOrdersCount = computed(() => {
  return filteredByShiftOrders.value.length;
});

const countByStatus = (statusId: number) => {
  return filteredByShiftOrders.value.filter((o: any) => Number(o.rawStatusId) === Number(statusId)).length;
};

// 🌟 Lógica de Filtros Aplicados
const filteredOrders = computed(() => {
  let result = filteredByShiftOrders.value;

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => {
      const name = `${o.distributor || o.customer || ''}`.toLowerCase();
      const phone = (o.phone || '').toLowerCase();
      return o.id.toString().includes(query) || name.includes(query) || phone.includes(query);
    });
  }

  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  return result;
});

const stats = computed(() => {
  const visibleOrders = filteredOrders.value;
  return {
    totalOrders: visibleOrders.length,
    totalAmount: visibleOrders.reduce((sum: number, o: any) => sum + Number(o.total || 0), 0),
    totalPaid: visibleOrders.filter((o: any) => Number(o.id_estado_pago) === 2).reduce((sum: number, o: any) => sum + Number(o.total || 0), 0), 
    paid: visibleOrders.filter((o: any) => Number(o.id_estado_pago) === 2).length,
    delivered: visibleOrders.filter((o: any) => Number(o.rawStatusId) === 4).length
  };
});

const formatPrice = (price: number) => {
  return price.toLocaleString('es-CL');
};

const getStatusClass = (status: string, statusId?: number) => {
  if (statusId) {
    switch (Number(statusId)) {
      case 1: return 'status-validation';
      case 2: return 'status-preparation';
      case 3: return 'status-shipping';
      case 4: return 'status-completed';
      case 5: return 'status-pending';
      case 6: return 'status-unpaid';
      case 7: return 'status-paid';
      case 8: return 'status-cancelled';
    }
  }
  switch (status) {
    case 'Por pagar': return 'status-unpaid';
    case 'Pagada': return 'status-paid';
    case 'En preparación': return 'status-preparation';
    case 'En despacho': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    case 'En validación': return 'status-validation';
    case 'Pendiente': return 'status-pending';
    case 'Cancelado': return 'status-cancelled';
    default: return 'status-generic';
  }
};

const searchQuery = ref('');
const statusFilter = ref('all');
const selectedCard = ref<'all' | 'amount' | 'paid' | 'delivered' | 'amount_paid'>('all');
const isStatusDropdownOpen = ref(false);

const isModalOpen = ref(false);
const selectedOrderId = ref<number | string>('');

const selectedOrder = computed(() => {
  return orders.value.find((o: any) => o.id === selectedOrderId.value);
});

const openModal = (id: number | string) => {
  selectedOrderId.value = id;
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  fetchOrders(true);
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
};

const applySummaryFilter = (filter: 'all' | 'amount' | 'paid' | 'delivered') => {
  selectedCard.value = filter;

  switch (filter) {
    case 'paid':
      statusFilter.value = 'Pagada';
      break;
    case 'delivered':
      statusFilter.value = 'Entregado';
      break;
    default:
      statusFilter.value = 'all';
  }
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  selectedCard.value = status === 'Pagada' ? 'paid' : status === 'Entregado' ? 'delivered' : 'all';
  isStatusDropdownOpen.value = false;
};

const closeDropdowns = () => {
  isStatusDropdownOpen.value = false;
};

const sortConfig = ref({ key: '', direction: 'asc' });

const sortBy = (key: string) => {
  if (sortConfig.value.key === key) {
    sortConfig.value.direction = sortConfig.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sortConfig.value.key = key;
    sortConfig.value.direction = 'asc';
  }
};

const sortedOrders = computed(() => {
  const dataToSort = filteredOrders.value;
  if (!sortConfig.value.key) return dataToSort;

  return [...dataToSort].sort((a: any, b: any) => {
    let aValue = a[sortConfig.value.key];
    let bValue = b[sortConfig.value.key];

    if (sortConfig.value.key === 'date') {
      const parseDate = (d: string, t?: string) => {
        const [day = 1, month = 1, year = 2000] = d.split('/').map(Number);
        const [hours = 0, minutes = 0] = (t || '00:00').split(':').map(Number);
        return new Date(year, month - 1, day, hours, minutes).getTime();
      };
      aValue = parseDate(a.date, a.time);
      bValue = parseDate(b.date, b.time);
    }

    if (aValue < bValue) return sortConfig.value.direction === 'asc' ? -1 : 1;
    if (aValue > bValue) return sortConfig.value.direction === 'asc' ? 1 : -1;
    return 0;
  });
});

// 📄 Estados y Lógica de Paginación
const currentPage = ref(1);
const itemsPerPage = ref(10);
const pageSizeOptions = [10, 25, 50, 100];

const totalPages = computed(() => {
  return Math.ceil(sortedOrders.value.length / itemsPerPage.value) || 1;
});

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return sortedOrders.value.slice(start, start + itemsPerPage.value);
});

const paginationInfo = computed(() => {
  const total = sortedOrders.value.length;
  if (total === 0) return { start: 0, end: 0, total: 0 };
  const start = (currentPage.value - 1) * itemsPerPage.value + 1;
  const end = Math.min(currentPage.value * itemsPerPage.value, total);
  return { start, end, total };
});

const displayedPages = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2;
  const range: (number | string)[] = [];

  for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
    range.push(i);
  }

  if (current - delta > 2) {
    range.unshift('...');
  }
  range.unshift(1);

  if (current + delta < total - 1) {
    range.push('...');
  }
  if (total > 1) {
    range.push(total);
  }

  return range;
});

const goToPage = (page: number | string) => {
  if (typeof page === 'number' && page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

// Reiniciar a la primera página si cambian los filtros o el tamaño de página
watch([searchQuery, statusFilter, selectedDate, itemsPerPage], () => {
  currentPage.value = 1;
});

const handleVisibilityChange = () => {
  if (!document.hidden && autoRefresh.value && !isModalOpen.value) {
    fetchOrders(true);
    secondsCountdown.value = 20;
  }
};

onMounted(async () => {
  checkUserRole();
  window.addEventListener('click', closeDropdowns);
  document.addEventListener('visibilitychange', handleVisibilityChange);
  await Promise.all([
    loadShiftWindow(),
    fetchOrders()
  ]);

  refreshInterval.value = setInterval(() => {
    if (autoRefresh.value && !isModalOpen.value && !document.hidden) {
      fetchOrders(true);
      secondsCountdown.value = 20;
    }
  }, 20000);

  countdownTimer.value = setInterval(() => {
    if (autoRefresh.value && !isModalOpen.value && !document.hidden) {
      secondsCountdown.value = secondsCountdown.value > 1 ? secondsCountdown.value - 1 : 20;
    }
  }, 1000);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
  document.removeEventListener('visibilitychange', handleVisibilityChange);
  if (refreshInterval.value) clearInterval(refreshInterval.value);
  if (countdownTimer.value) clearInterval(countdownTimer.value);
});
</script>

<style scoped>
.orders-container {
  padding: 40px 20px;
  background-color: transparent;
  min-height: calc(100vh - 80px);
}

.orders-header {
  max-width: 1200px;
  margin: 0 auto 25px auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.header-main-info {
  display: flex;
  flex-direction: column;
}

/* BANNER DE TURNO OPERATIVO */
.shift-status-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 14px 20px;
  border-radius: 16px;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
}

.banner-active {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 1.5px solid #86efac;
}

.banner-inactive {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1.5px solid #cbd5e1;
}

.shift-banner-left {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.shift-live-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.92rem;
  color: #166534;
}

.banner-inactive .shift-live-indicator {
  color: #475569;
}

.live-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #22c55e;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  animation: pulse-dot 1.6s infinite;
}

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.shift-day-tag {
  background: white;
  padding: 2px 10px;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 800;
  border: 1px solid #bbf7d0;
  color: #15803d;
}

.shift-schedule-info {
  display: flex;
  flex-wrap: wrap;
  gap: 14px;
  font-size: 0.84rem;
  color: #334155;
}

.shift-banner-right {
  display: flex;
  align-items: center;
}

.shift-comandas-summary {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  background: white;
  padding: 6px 14px;
  border-radius: 12px;
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.comandas-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.comandas-val {
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

/* PESTAÑAS RÁPIDAS DE ESTADO KDS */
.status-quick-tabs {
  display: flex;
  gap: 8px;
  max-width: 1200px;
  margin: 0 auto 20px auto;
  overflow-x: auto;
  padding-bottom: 4px;
}

.status-tab-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 999px;
  font-weight: 700;
  font-size: 0.85rem;
  color: #475569;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.status-tab-btn:hover {
  background: #f8fafc;
  border-color: #cbd5e1;
}

.status-tab-btn.active {
  background: var(--DC-brown, #513119);
  color: white;
  border-color: var(--DC-brown, #513119);
}

.tab-count-pill {
  background: rgba(0, 0, 0, 0.08);
  padding: 1px 7px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 800;
}

.status-tab-btn.active .tab-count-pill {
  background: rgba(255, 255, 255, 0.25);
  color: white;
}

/* SELECTOR DE MODO DE TURNO */
.shift-mode-selector {
  display: inline-flex;
  background: #f1f5f9;
  padding: 3px;
  border-radius: 12px;
  gap: 3px;
}

.btn-shift-mode {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 9px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 0.82rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-shift-mode.active {
  background: white;
  color: var(--DC-orange, #e28743);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
}

/* COMANDAS Y CELDAS KDS */
.comanda-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.comanda-badge {
  font-size: 0.95rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  background: #fff7ed;
  padding: 3px 8px;
  border-radius: 8px;
  border: 1px solid #fed7aa;
  display: inline-block;
  width: fit-content;
}

.order-real-id {
  font-size: 0.72rem;
  color: #94a3b8;
  font-weight: 600;
}

.client-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.client-name {
  font-size: 0.92rem;
  color: #1e293b;
}

.client-phone {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.76rem;
  color: #64748b;
}

.time-primary {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.92rem;
  color: #0f172a;
}

.date-secondary {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.75rem;
  color: #64748b;
  margin-top: 2px;
}

.elapsed-badge {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 6px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 800;
}

.elapsed-ok {
  background: #dcfce7;
  color: #15803d;
}

.elapsed-warning {
  background: #fef3c7;
  color: #b45309;
}

.elapsed-danger {
  background: #fee2e2;
  color: #b91c1c;
  animation: pulse-danger 1.5s infinite;
}

@keyframes pulse-danger {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.75; }
}

.mobile-real-id {
  font-size: 0.72rem;
  color: #94a3b8;
  font-weight: 600;
}

.orders-title {
  font-size: 2rem;
  font-weight: 900;
  color: var(--DC-gray);
  margin: 0;
  text-transform: uppercase;
}

.orders-description {
  font-size: 1rem;
  color: var(--DC-text-gray);
  margin-top: 4px;
  font-weight: 600;
}

/* TARJETAS SUPERIORES */
.status-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 15px;
  margin: 0 auto 40px auto;
  max-width: 1200px;
}

.status-card {
  background-color: white;
  border-radius: 16px;
  padding: 15px;
  display: flex;
  justify-content: space-between;
  align-items: stretch;
  gap: 12px;
  flex-wrap: wrap;
  min-height: 118px;
  height: 100%;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 2px solid transparent;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.status-card:hover {
  transform: translateY(-4px);
  border-color: var(--DC-orange);
}

.status-card.card-interactive {
  cursor: pointer;
}

.status-card.card-active {
  border-color: var(--DC-orange);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.16);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1 1 160px;
  min-width: 0;
}

.icon-box {
  width: 45px;
  height: 45px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Colores de las tarjetas alineados al tema */
.bg-unpaid { background-color: rgba(226, 135, 67, 0.1); color: var(--DC-orange); }
.bg-paid { background-color: rgba(46, 196, 182, 0.1); color: #2ec4b6; }
.bg-preparation { background-color: rgba(81, 49, 25, 0.1); color: var(--DC-brown); }
.bg-shipping { background-color: rgba(216, 0, 86, 0.1); color: var(--DC-pink); }
.bg-delivered { background-color: #f1f3f5; color: var(--DC-gray); }
.bg-generic { background-color: #f1f3f5; color: var(--DC-text-gray); }

.card-label { font-size: 0.85rem; font-weight: 800; color: var(--DC-gray); text-transform: uppercase; }
.card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  justify-content: center;
  min-width: 0;
  text-align: right;
}
.card-count { font-size: 1.2rem; font-weight: 900; color: var(--DC-brown); line-height: 1; }
.card-subtext { font-size: 0.75rem; font-weight: 700; color: var(--DC-text-gray); margin-top: 4px; text-transform: uppercase; }

/* CALENDARIO ESTILOS */
.card-date-picker {
  border: 2px solid #eeedee;
  background-color: #fcfbf9;
  padding: 8px 10px 8px 12px;
  border-radius: 10px;
  color: var(--DC-gray);
  font-weight: 800;
  font-size: 0.9rem;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
  min-width: 165px;
  width: 165px;
}

.card-date-picker:hover:not(:disabled) {
  background-color: white;
  border-color: #ced4da;
}

.card-date-picker:focus {
  border-color: var(--DC-orange);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.2);
}

::-webkit-calendar-picker-indicator { cursor: pointer; opacity: 0.6; transition: 0.2s; }
::-webkit-calendar-picker-indicator:hover { opacity: 1; }

.picker-disabled {
  background-color: #f1f3f5;
  border-color: #dee2e6;
  color: #adb5bd;
  cursor: not-allowed;
  opacity: 0.8;
}
.picker-disabled::-webkit-calendar-picker-indicator { display: none; }

/* TABS / SWITCH DE PAGOS - PEDIDOS */
.main-table-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  border: 1px solid #eeedee;
  overflow: visible;
  max-width: 1200px;
  margin: 0 auto;
}

.tabs-outer-container { max-width: 1200px; margin: 0 auto 15px auto; display: flex; justify-content: flex-start; }

.switch-container {
  display: flex;
  background-color: white;
  padding: 6px;
  border-radius: 12px;
  width: fit-content;
  position: relative;
  box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.switch-slider {
  position: absolute;
  top: 6px; left: 6px;
  width: calc(50% - 6px); height: calc(100% - 12px);
  background-color: var(--DC-orange);
  border-radius: 8px;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
}

.switch-slider.slide-right { transform: translateX(100%); }

.switch-btn {
  background: none; border: none; padding: 10px 24px; border-radius: 8px;
  font-size: 0.95rem; font-weight: 800; color: var(--DC-text-gray);
  cursor: pointer; position: relative; z-index: 2; transition: color 0.3s ease;
  display: flex; align-items: center; gap: 10px;
}

.switch-btn.active { color: white; }

.count-badge { background-color: #f1f3f5; color: var(--DC-text-gray); padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 900;}
.switch-btn.active .count-badge { background-color: white; color: var(--DC-orange); }

/* CONTROLES DE LA TABLA */
.table-actions { padding: 24px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px; }
.actions-left { display: flex; gap: 12px; flex: 1; flex-wrap: wrap; align-items: flex-start;}

.search-box { position: relative; width: 100%; max-width: 400px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--DC-brown); }
.search-box input {
  width: 100%; padding: 12px 12px 12px 42px; border-radius: 10px;
  border: 2px solid #eeedee; font-size: 0.95rem; color: var(--DC-gray); font-weight: 600; outline: none; transition: all 0.2s;
}
.search-box input:focus { border-color: var(--DC-orange); }

.dropdown-container { position: relative; }
.btn-secondary { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background-color: white; border: 2px solid #eeedee; border-radius: 10px; color: var(--DC-gray); font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-secondary:hover { border-color: var(--DC-brown); }

.dropdown-menu { position: absolute; top: calc(100% + 8px); left: 0; background-color: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); border: 2px solid var(--DC-brown); min-width: 220px; z-index: 100; padding: 8px; }
.dropdown-item { padding: 10px 16px; font-size: 0.85rem; font-weight: 700; color: var(--DC-gray); cursor: pointer; border-radius: 8px; transition: all 0.2s; }
.dropdown-item:hover { background-color: var(--DC-bg-gray); color: var(--DC-orange); }
.dropdown-divider { height: 1px; background-color: #eeedee; margin: 6px 0; }

.btn-export { display: flex; align-items: center; gap: 10px; padding: 12px 20px; background-color: white; border: 2px solid var(--DC-brown); border-radius: 10px; color: var(--DC-brown); font-size: 0.9rem; font-weight: 900; cursor: pointer; transition: all 0.2s; }
.btn-export:hover { background-color: var(--DC-brown); color: white; }

/* TABLA DE PEDIDOS */
.orders-table { width: 100%; border-collapse: collapse; }
.orders-table th { padding: 16px 20px; text-align: left; background-color: var(--DC-brown) !important; color: white !important; font-weight: 900 !important; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; user-select: none; }
.orders-table th.text-center { text-align: center; }
.orders-table td { padding: 20px; text-align: left; border-bottom: 1px solid #eeedee; font-size: 0.95rem; color: var(--DC-gray); }

.bold-text { font-weight: 900; color: var(--DC-gray); font-size: 1rem;}

/* BADGES DE ESTADO (Alineados a los colores de J.Junior) */
.status-badge { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; display: inline-block; border: 2px solid transparent;}
.status-unpaid { background-color: rgba(226, 135, 67, 0.1); color: var(--DC-orange); border-color: var(--DC-orange); }
.status-paid { background-color: rgba(46, 196, 182, 0.1); color: #2ec4b6; border-color: #2ec4b6; }
.status-preparation { background-color: rgba(81, 49, 25, 0.1); color: var(--DC-brown); border-color: var(--DC-brown); }
.status-shipping { background-color: rgba(216, 0, 86, 0.1); color: var(--DC-pink); border-color: var(--DC-pink); }
.status-completed { background-color: #f1f3f5; color: var(--DC-gray); border-color: var(--DC-gray); }
.status-validation { background-color: #fff4e6; color: #fd7e14; border-color: #fd7e14; }
.status-pending { background-color: #f8f9fa; color: #495057; border-color: #ced4da; }
.status-cancelled { background-color: #f8d7da; color: #e63946; border-color: #e63946; }
.status-generic { background-color: #f1f3f5; color: var(--DC-text-gray); border-color: #ced4da; }

.date-content { display: flex; align-items: center; gap: 10px; }
.date-time { display: flex; flex-direction: column; }
.date { font-weight: 800; color: var(--DC-gray); }
.time { font-size: 0.75rem; color: var(--DC-text-gray); font-weight: 700;}
.date-icon { color: var(--DC-orange); }

.badges-cell {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}

.status-paid {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.status-unpaid {
  background-color: #fff3e0;
  color: #e65100;
  border: 1px solid #ffcc80;
}

.btn-live-toggle {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  background-color: #fcfbf9;
  border: 2px solid #eeedee;
  border-radius: 10px;
  color: var(--DC-gray);
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}
.btn-live-toggle.active {
  background-color: #e8f5e9;
  border-color: #a5d6a7;
  color: #2e7d32;
}
.spinning {
  animation: spin 1s linear infinite;
}

.elapsed-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 800;
}
.elapsed-ok {
  background-color: #f1f3f5;
  color: #495057;
}
.elapsed-warning {
  background-color: #fff3bf;
  color: #f59f00;
  border: 1px solid #ffe066;
}
.elapsed-danger {
  background-color: #ffe3e3;
  color: #e03131;
  border: 1px solid #ffc9c9;
  animation: pulse-danger 1.5s infinite;
}
@keyframes pulse-danger {
  0% { opacity: 1; }
  50% { opacity: 0.6; }
  100% { opacity: 1; }
}

.btn-quick-advance {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 800;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}
.advance-step-1 {
  background-color: #fff4e6;
  color: #fd7e14;
  border: 1px solid #ffe8cc;
}
.advance-step-1:hover {
  background-color: #fd7e14;
  color: white;
}
.advance-step-2 {
  background-color: #e6fcf5;
  color: #0ca678;
  border: 1px solid #c3fae8;
}
.advance-step-2:hover {
  background-color: #0ca678;
  color: white;
}
.advance-step-3 {
  background-color: #e7f5ff;
  color: #1c7ed6;
  border: 1px solid #d0ebff;
}
.advance-step-3:hover {
  background-color: #1c7ed6;
  color: white;
}

.actions-content { display: flex; justify-content: center; }
.btn-action { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; border: 2px solid var(--DC-orange); background-color: white; color: var(--DC-orange); font-size: 0.85rem; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-action:hover { background-color: var(--DC-orange); color: white; }

.padding-large { padding: 60px !important; }
.loading-container { display: flex; flex-direction: column; align-items: center; gap: 15px; color: var(--DC-brown); font-weight: 900; text-transform: uppercase; }
.spinner { width: 40px; height: 40px; border: 4px solid var(--DC-bg-gray); border-top: 4px solid var(--DC-orange); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.skeleton-row td {
  padding: 18px 20px;
}

.skeleton-pill {
  height: 18px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-50 { width: 50px; }
.width-70 { width: 70px; }
.width-80 { width: 80px; }
.width-90 { width: 90px; }
.width-100 { width: 100px; }
.width-120 { width: 120px; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 15px;
  color: var(--DC-text-gray);
  font-weight: 700;
  min-height: 240px;
  padding: 24px;
  box-sizing: border-box;
  text-align: center;
}
.empty-icon { color: var(--DC-brown); opacity: 0.5; }
.btn-retry { padding: 10px 24px; background-color: var(--DC-orange); color: white; border: none; border-radius: 8px; font-weight: 900; cursor: pointer; transition: background-color 0.2s; }
.btn-retry:hover { background-color: var(--DC-brown); }

.header-content { display: flex; align-items: center; justify-content: flex-start; gap: 8px; cursor: pointer; }
.sort-icon { color: rgba(255,255,255,0.5); transition: color 0.2s; }
.active-sort { color: var(--DC-orange) !important; }
.orders-table th:hover .sort-icon { color: white; }

.date-filter-box {
  position: relative;
  display: flex;
  align-items: center;
}

.date-filter-icon {
  position: absolute;
  left: 12px;
  color: var(--DC-brown);
  pointer-events: none;
}

.mobile-date-input {
  padding: 10px 12px 10px 38px;
  border: 2px solid #eeedee;
  border-radius: 10px;
  font-size: 0.9rem;
  font-weight: 800;
  color: var(--DC-gray);
  background-color: white;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
}

.mobile-date-input:focus {
  border-color: var(--DC-orange);
}

/* 📄 PAGINACIÓN */
.pagination-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  padding: 16px 24px;
  background-color: white;
  border-top: 1px solid #eeedee;
}

.pagination-info {
  font-size: 0.85rem;
  color: var(--DC-brown);
  font-weight: 600;
}

.pagination-info strong {
  color: var(--DC-gray);
}

.pagination-controls {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
}

.page-size-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  color: var(--DC-brown);
  font-weight: 700;
}

.select-page-size {
  padding: 6px 10px;
  border-radius: 8px;
  border: 1.5px solid #e2e8f0;
  background-color: #f8fafc;
  color: var(--DC-gray);
  font-size: 0.85rem;
  font-weight: 700;
  cursor: pointer;
  outline: none;
  font-family: inherit;
  transition: all 0.2s ease;
}

.select-page-size:focus,
.select-page-size:hover {
  border-color: var(--DC-orange);
  background-color: white;
}

.page-buttons {
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-page-nav,
.btn-page-num {
  min-width: 34px;
  height: 34px;
  padding: 0 6px;
  border-radius: 8px;
  border: 1.5px solid #eeedee;
  background-color: white;
  color: var(--DC-gray);
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  user-select: none;
}

.btn-page-nav:hover:not(:disabled),
.btn-page-num:hover:not(:disabled):not(.ellipsis) {
  border-color: var(--DC-orange);
  color: var(--DC-orange);
  background-color: #fffaf5;
  transform: translateY(-1px);
}

.btn-page-num.active {
  background-color: var(--DC-orange);
  border-color: var(--DC-orange);
  color: white;
  box-shadow: 0 2px 6px rgba(255, 107, 0, 0.3);
}

.btn-page-nav:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background-color: #f8fafc;
  border-color: #e2e8f0;
}

.btn-page-num.ellipsis {
  cursor: default;
  border: none;
  background: transparent;
  color: #94a3b8;
}

/* 💻 VISTAS RESPONSIVAS: ESCRITORIO vs MÓVIL */
.desktop-table-view {
  display: block;
}

.mobile-cards-view {
  display: none;
}

/* 📱 RESPONSIVO: ESTILO APP NATIVA PARA CELULARES Y TABLETS */
@media (max-width: 768px) {
  .desktop-table-view {
    display: none !important;
  }

  .mobile-cards-view {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 12px;
  }

  .mobile-cards-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .mobile-order-card {
    background: white;
    border: 1.5px solid #eeedee;
    border-radius: 16px;
    padding: 14px 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    gap: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .mobile-order-card:active {
    transform: scale(0.99);
    border-color: var(--DC-orange);
  }

  .mobile-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
  }

  .mobile-id-box {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .mobile-order-id {
    font-size: 1.05rem;
    font-weight: 900;
    color: #1e293b;
  }

  .mobile-order-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 700;
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 6px;
  }

  .mobile-card-badges {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .mobile-card-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px 0;
    border-top: 1px dashed #f1ece7;
    border-bottom: 1px dashed #f1ece7;
  }

  .mobile-client-line {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    font-weight: 800;
    color: #1e293b;
  }

  .mobile-client-name {
    color: #1e293b;
  }

  .mobile-phone-line {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 600;
  }

  .mobile-icon {
    color: var(--DC-orange);
    flex-shrink: 0;
  }

  .mobile-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }

  .mobile-price-section {
    display: flex;
    flex-direction: column;
  }

  .price-title {
    font-size: 0.72rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
  }

  .price-value {
    font-size: 1.2rem;
    font-weight: 900;
    color: #059669;
  }

  .mobile-actions-row {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .btn-mobile-detail {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1.5px solid var(--DC-orange);
    background: white;
    color: var(--DC-orange);
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
  }

  .btn-mobile-detail:hover {
    background: var(--DC-orange);
    color: white;
  }

  .btn-mobile-advance {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 12px;
    border-radius: 8px;
    border: none;
    font-size: 0.82rem;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
  }

  .mobile-skeleton-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .mobile-card-skeleton {
    background: white;
    border-radius: 16px;
    border: 1px solid #eeedee;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .mobile-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 30px 16px;
    color: #64748b;
    gap: 12px;
  }

  .orders-container { 
    padding: 12px 10px; 
    min-height: auto;
  }
  .orders-header { margin-bottom: 16px; }
  .orders-title { font-size: 1.4rem; }
  .orders-description { font-size: 0.82rem; }

  .status-cards { 
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 0 0 16px 0;
    padding: 0;
    overflow-x: visible;
    width: 100%;
  }
  
  .status-card { 
    width: 100%;
    min-width: 0;
    min-height: auto;
    box-sizing: border-box;
    padding: 12px 16px;
    border-radius: 14px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    flex-wrap: nowrap;
    gap: 12px;
  }

  .card-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1 1 auto;
    min-width: 0;
  }

  .card-label {
    font-size: 0.85rem;
    font-weight: 800;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    flex-shrink: 0;
  }

  .card-count { font-size: 1.25rem; font-weight: 900; }
  .icon-box { width: 40px; height: 40px; flex-shrink: 0; }

  .table-actions { 
    padding: 12px 10px; 
    flex-direction: column; 
    gap: 10px; 
  }
  .actions-left { 
    display: flex; 
    flex-direction: column; 
    gap: 8px; 
    width: 100%; 
  }
  .search-box { 
    width: 100%; 
    max-width: 100%; 
  }
  .search-box input { 
    padding: 10px 10px 10px 38px; 
    font-size: 0.88rem; 
  }

  .date-filter-box {
    width: 100%;
  }

  .mobile-date-input {
    width: 100%;
    box-sizing: border-box;
    font-size: 0.88rem;
    padding: 10px 12px 10px 38px;
  }
  
  .dropdown-container { width: 100%; }
  .btn-secondary { 
    width: 100%; 
    padding: 10px 12px; 
    font-size: 0.85rem; 
    justify-content: space-between; 
  }

  .btn-live-toggle {
    width: 100%;
    justify-content: center;
    padding: 10px;
    font-size: 0.85rem;
  }

  .main-table-card { 
    border-radius: 16px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }

  .pagination-footer {
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 14px 12px;
  }

  .pagination-info {
    text-align: center;
    font-size: 0.8rem;
  }

  .pagination-controls {
    flex-direction: column;
    width: 100%;
    align-items: center;
    gap: 10px;
  }

  .page-size-selector {
    justify-content: center;
  }

  .page-buttons {
    flex-wrap: wrap;
    justify-content: center;
  }
}
</style>