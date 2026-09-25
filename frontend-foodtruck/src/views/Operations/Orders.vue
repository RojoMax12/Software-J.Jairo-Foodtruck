<template>
  <div class="orders-container">
    <!-- ===================== HEADER ===================== -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Gestión de Pedidos & Comandas</h1>
      </div>

      <div class="header-actions">
        <button 
          class="btn-secondary" 
          :class="{ 'btn-live-active': autoRefresh }" 
          @click="autoRefresh = !autoRefresh"
          :title="autoRefresh ? 'Pausar auto-actualización' : 'Activar auto-actualización en vivo'"
        >
          <RefreshCw :size="16" :class="{ spinning: isLoading || isRefreshingBackground }" />
          <span>{{ autoRefresh ? `En vivo (${secondsCountdown}s)` : 'En vivo Pausado' }}</span>
        </button>

        <button class="btn-primary" @click="() => fetchOrders(false)" :disabled="isLoading">
          <RefreshCw :size="16" :class="{ spinning: isLoading }" />
          <span>Actualizar</span>
        </button>
      </div>
    </header>

    <!-- ===================== BANNER DE ESTADO DEL TURNO ===================== -->
    <section class="shift-status-card" :class="shiftWindow?.es_jornada_activa ? 'shift-active' : 'shift-inactive'">
      <div class="shift-status-main">
        <div class="shift-indicator-row">
          <span class="status-pill" :class="shiftWindow?.es_jornada_activa ? 'pill-open' : 'pill-closed'">
            <span class="dot-pulse" v-if="shiftWindow?.es_jornada_activa"></span>
            {{ shiftWindow?.es_jornada_activa ? 'Turno en Curso' : (shiftWindow?.es_dia_cerrado ? 'Día de Descanso' : 'Fuera de Horario') }}
          </span>
          <span class="shift-day-badge">{{ shiftWindow?.dia || 'Hoy' }}</span>
          <span class="shift-date-hint">Fecha del turno: {{ shiftDateFormatted }}</span>
        </div>

        <div class="shift-data-chips">
          <div class="meta-chip">
            <span class="chip-lbl">Horario Oficial:</span>
            <strong>{{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }} hrs</strong>
          </div>
          <div class="meta-chip highlight">
            <span class="chip-lbl">Rango de Comandas:</span>
            <strong>#1 — #{{ shiftOrdersCount }}</strong>
          </div>
        </div>
      </div>
    </section>

    <!-- ===================== RESUMEN EN KPIS (SUMMARY-GRID) ===================== -->
    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <ClipboardCheck :size="22" />
        </div>
        <div>
          <span class="summary-label">Total Pedidos</span>
          <strong class="summary-value">{{ stats.totalOrders }}</strong>
          <p class="summary-helper">Registrados en este turno</p>
        </div>
      </article>

      <article class="summary-card highlight-metric">
        <div class="summary-icon-box bg-summary-orange">
          <TrendingUp :size="22" />
        </div>
        <div>
          <span class="summary-label">Venta Total</span>
          <strong class="summary-value text-orange">${{ formatPrice(stats.totalAmount) }}</strong>
          <p class="summary-helper">Facturado en el turno</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <DollarSign :size="22" />
        </div>
        <div>
          <span class="summary-label">Total Recaudado</span>
          <strong class="summary-value text-status-open">${{ formatPrice(stats.totalPaid) }}</strong>
          <p class="summary-helper">{{ stats.paid }} pedidos pagados</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-blue">
          <CheckCircle :size="22" />
        </div>
        <div>
          <span class="summary-label">Pagados</span>
          <strong class="summary-value">{{ stats.paid }}</strong>
          <p class="summary-helper">Con comprobante listo</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <Truck :size="22" />
        </div>
        <div>
          <span class="summary-label">Entregados</span>
          <strong class="summary-value text-pink">{{ stats.delivered }}</strong>
          <p class="summary-helper">Despachados al cliente</p>
        </div>
      </article>
    </section>

    <!-- ===================== PESTAÑAS RÁPIDAS DE ESTADO (KDS) ===================== -->
    <div class="inventory-tabs-nav">
      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'all' }" 
        @click="selectStatus('all')"
      >
        <span class="tab-text">Todos</span>
        <span class="tab-pill">{{ filteredByShiftOrders.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'Pendiente' }" 
        @click="selectStatus('Pendiente')"
      >
        <span class="tab-text">Pendientes</span>
        <span class="tab-pill">{{ countByStatus(1) }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'En preparación' }" 
        @click="selectStatus('En preparación')"
      >
        <span class="tab-text">En Preparación</span>
        <span class="tab-pill">{{ countByStatus(2) }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'Listo' }" 
        @click="selectStatus('Listo')"
      >
        <span class="tab-text">Listos</span>
        <span class="tab-pill">{{ countByStatus(3) }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'Entregado' }" 
        @click="selectStatus('Entregado')"
      >
        <span class="tab-text">Entregados</span>
        <span class="tab-pill">{{ countByStatus(4) }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: statusFilter === 'Cancelado' }" 
        @click="selectStatus('Cancelado')"
      >
        <span class="tab-text">Cancelados</span>
        <span class="tab-pill">{{ countByStatus(5) }}</span>
      </button>
    </div>

    <!-- ===================== TABLA PRINCIPAL UNIFICADA ===================== -->
    <section class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <!-- Modo de turno -->
          <div class="shift-mode-selector">
            <button 
              type="button" 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'current' }" 
              @click="setShiftMode('current')"
              title="Ver pedidos del turno en curso"
            >
              <Zap :size="14" />
              <span>Turno Actual</span>
            </button>
            <button 
              type="button" 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'previous' }" 
              @click="setShiftMode('previous')"
              title="Ver pedidos del turno anterior"
            >
              <History :size="14" />
              <span>Turno Anterior</span>
            </button>
            <button 
              type="button" 
              class="btn-shift-mode" 
              :class="{ active: shiftMode === 'custom' }" 
              @click="setShiftMode('custom')"
              title="Elegir fecha específica"
            >
              <CalendarIcon :size="14" />
              <span>Por Fecha</span>
            </button>
          </div>

          <!-- Buscador -->
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Buscar comanda, cliente, fono..."
            />
            <button v-if="searchQuery" class="clear-search-btn" @click="searchQuery = ''">
              <X :size="14" />
            </button>
          </div>

          <!-- Fecha si aplica -->
          <div class="date-filter-box" v-if="shiftMode === 'custom' || canEditDate">
            <CalendarIcon :size="16" class="date-filter-icon" />
            <input 
              v-model="selectedDate" 
              type="date" 
              class="filter-date-input"
              :disabled="!canEditDate"
              :class="{ 'picker-disabled': !canEditDate }"
              :title="canEditDate ? 'Buscar por fecha' : 'Solo lectura'"
            />
          </div>
        </div>

        <div class="toolbar-right">
          <span class="results-chip">{{ filteredOrders.length }} pedidos</span>
        </div>
      </div>

      <!-- TABLA ESCRITORIO -->
      <div class="table-wrapper desktop-table-only">
        <table class="orders-table">
          <thead>
            <tr>
              <th style="width: 14%;" @click="sortBy('id')">
                <div class="header-content">
                  <span>Comanda</span>
                  <ChevronsUpDown :size="14" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
                </div>
              </th>
              <th style="width: 25%;" @click="sortBy('distributor')">
                <div class="header-content">
                  <span>Cliente</span>
                  <ChevronsUpDown :size="14" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
                </div>
              </th>
              <th style="width: 22%;" @click="sortBy('status')">
                <div class="header-content">
                  <span>Estado & Pago</span>
                  <ChevronsUpDown :size="14" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
                </div>
              </th>
              <th style="width: 16%;" @click="sortBy('date')">
                <div class="header-content">
                  <span>Hora / Espera</span>
                  <ChevronsUpDown :size="14" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
                </div>
              </th>
              <th style="width: 11%;" @click="sortBy('total')">
                <div class="header-content">
                  <span>Total</span>
                  <ChevronsUpDown :size="14" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'total' }" />
                </div>
              </th>
              <th style="width: 12%; text-align: center;">Acción</th>
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
              <td colspan="6" class="text-center">
                <div class="state-card empty-state">
                  <Package :size="40" />
                  <p>No se encontraron pedidos para este turno o filtros.</p>
                  <button @click="() => fetchOrders()" class="btn-retry">Actualizar datos</button>
                </div>
              </td>
            </tr>

            <tr v-else v-for="order in paginatedOrders" :key="order.id">
              <td>
                <div class="comanda-cell">
                  <span class="comanda-badge">#{{ order.id }}</span>
                </div>
              </td>
              <td>
                <div class="client-cell">
                  <strong class="client-name">{{ order.distributor }}</strong>
                  <span v-if="order.phone" class="client-phone"><Phone :size="11" /> {{ order.phone }}</span>
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
                    <Clock :size="13" class="time-icon" />
                    <strong>{{ order.time }}</strong>
                  </div>
                  <div class="date-secondary">
                    <span class="elapsed-badge" :class="getElapsedBadgeClass(order.elapsedMinutes)">
                      {{ order.elapsedMinutes }}m
                    </span>
                  </div>
                </div>
              </td>
              <td class="bold-text">${{ formatPrice(order.total) }}</td>
              <td>
                <div class="actions">
                  <button class="icon-button detail-action" @click="openModal(order.id)" title="Ver comanda">
                    <Eye :size="15" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- VISTA TARJETAS MÓVIL -->
      <div class="mobile-cards-view mobile-only">
        <div v-if="isLoading" class="mobile-skeleton-container">
          <div v-for="n in 3" :key="'mob-skel-' + n" class="mobile-order-card skeleton-card">
            <div class="skeleton-pill width-80"></div>
            <div class="skeleton-pill width-120"></div>
          </div>
        </div>

        <div v-else-if="sortedOrders.length === 0" class="empty-state">
          <Package :size="40" />
          <p>No se encontraron pedidos para este turno.</p>
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
                <span class="comanda-badge">#{{ order.id }}</span>
                <span class="elapsed-badge" :class="getElapsedBadgeClass(order.elapsedMinutes)">
                  <Clock :size="11" /> {{ order.time }} ({{ order.elapsedMinutes }}m)
                </span>
              </div>
              <div class="badges-cell">
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
                <User :size="14" class="mobile-icon" />
                <span class="mobile-client-name">{{ order.distributor }}</span>
              </div>
              <div v-if="order.phone" class="mobile-phone-line">
                <Phone :size="12" class="mobile-icon" />
                <span>{{ order.phone }}</span>
              </div>
            </div>

            <div class="mobile-card-footer">
              <div class="mobile-price-section">
                <span class="price-title">Total</span>
                <strong class="price-value">${{ formatPrice(order.total) }}</strong>
              </div>

              <div class="mobile-actions-row" @click.stop>
                <button class="btn-action-mobile" @click="openModal(order.id)">
                  <Eye :size="14" />
                  <span>Detalle</span>
                </button>

                <button 
                  v-if="Number(order.rawStatusId) === 1" 
                  class="btn-advance-mobile step-1" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>A Cocina</span>
                  <ArrowRight :size="12" />
                </button>
                <button 
                  v-else-if="Number(order.rawStatusId) === 2" 
                  class="btn-advance-mobile step-2" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>Listo</span>
                  <ArrowRight :size="12" />
                </button>
                <button 
                  v-else-if="Number(order.rawStatusId) === 3" 
                  class="btn-advance-mobile step-3" 
                  @click="advanceOrderStatus(order)"
                >
                  <span>Entregar</span>
                  <CheckCircle :size="12" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- PAGINACIÓN ESTANDARIZADA -->
      <div v-if="totalPages > 1 || sortedOrders.length > 0" class="inventory-pagination">
        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="currentPage === 1" 
          @click="prevPage"
        >
          <ChevronLeft :size="16" />
          <span>Anterior</span>
        </button>
        
        <div class="pagination-info">
          Mostrando <strong>{{ paginationInfo.start }}</strong> - <strong>{{ paginationInfo.end }}</strong> de <strong>{{ paginationInfo.total }}</strong> pedidos
        </div>

        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="currentPage >= totalPages" 
          @click="nextPage"
        >
          <span>Siguiente</span>
          <ChevronRight :size="16" />
        </button>
      </div>
    </section>

    <!-- MODAL DETALLE DE PEDIDO -->
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
  ClipboardCheck, Package, Truck, CheckCircle, Search,
  Calendar as CalendarIcon, Eye, ChevronsUpDown,
  RefreshCw, Clock, ArrowRight, ChevronLeft, ChevronRight, User, Phone,
  Zap, History, TrendingUp, DollarSign, X
} from 'lucide-vue-next';
import orderService from '@/services/orderService';
import { type ShiftWindow, fetchShiftWindowFromBackend } from '@/services/cashFlowService';

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
    1: 2,
    2: 3,
    3: 4
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
        notas: o.notas || '',
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

const filteredByShiftOrders = computed(() => {
  let result = orders.value;
  if (selectedDate.value) {
    const [year, month, day] = selectedDate.value.split('-');
    const formattedSelectedDate = `${day}/${month}/${year}`;
    result = result.filter((o: any) => o.shiftDate === selectedDate.value || o.date === formattedSelectedDate);
  }
  return result;
});

const shiftOrdersCount = computed(() => filteredByShiftOrders.value.length);

const countByStatus = (statusId: number) => {
  return filteredByShiftOrders.value.filter((o: any) => Number(o.rawStatusId) === Number(statusId)).length;
};

const searchQuery = ref('');
const statusFilter = ref('all');

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

const formatPrice = (price: number) => price.toLocaleString('es-CL');

const getStatusClass = (status: string, statusId?: number) => {
  if (statusId) {
    switch (Number(statusId)) {
      case 1: return 'status-validation';
      case 2: return 'status-preparation';
      case 3: return 'status-shipping';
      case 4: return 'status-completed';
      case 5: return 'status-cancelled';
    }
  }
  switch (status) {
    case 'En preparación': return 'status-preparation';
    case 'Listo': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    case 'Pendiente': return 'status-validation';
    case 'Cancelado': return 'status-cancelled';
    default: return 'status-generic';
  }
};

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

const selectStatus = (status: string) => {
  statusFilter.value = status;
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

const currentPage = ref(1);
const itemsPerPage = ref(10);

const totalPages = computed(() => Math.ceil(sortedOrders.value.length / itemsPerPage.value) || 1);

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

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

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
  document.removeEventListener('visibilitychange', handleVisibilityChange);
  if (refreshInterval.value) clearInterval(refreshInterval.value);
  if (countdownTimer.value) clearInterval(countdownTimer.value);
});
</script>

<style scoped>
.orders-container {
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER Y ACCIONES PRINCIPALES
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

.btn-secondary.btn-live-active {
  background: #f0fdf4;
  border-color: #86efac;
  color: #15803d;
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

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ====================================================
   BANNER DEL TURNO OPERATIVO
==================================================== */
.shift-status-card {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 16px;
  padding: 1.15rem 1.4rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
}

.shift-status-card.shift-active {
  border-left: 5px solid #16a34a;
}

.shift-status-card.shift-inactive {
  border-left: 5px solid #cbd5e1;
}

.shift-status-main {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  width: 100%;
}

.shift-indicator-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pill-open { background: #dcfce7; color: #15803d; }
.pill-closed { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.dot-pulse {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 rgba(22, 163, 74, 0.7);
  animation: pulse-dot 1.8s infinite;
}

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.shift-day-badge {
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 3px 10px;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.shift-date-hint {
  font-size: 0.8rem;
  color: var(--DC-text-gray, #7c7468);
}

.shift-data-chips {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.82rem;
}

.meta-chip.highlight {
  background: #fff4e6;
  border: 1px solid #fed7aa;
}

.meta-chip.highlight strong {
  color: var(--DC-orange, #e28743);
}

.chip-lbl { color: var(--DC-text-gray, #7c7468); }

/* ====================================================
   KPIS SUMMARY-GRID (5 COLUMNAS)
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 1rem;
}

.summary-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
  border: 1px solid rgba(81, 49, 25, 0.08);
  padding: 1rem 1.15rem;
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.summary-card.highlight-metric {
  border-color: var(--DC-orange, #e28743);
  background: #fffdfa;
}

.summary-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
.bg-summary-blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.74rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #2c2724);
  font-size: 1.35rem;
  line-height: 1.15;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.76rem;
  margin: 0;
}

.text-status-open { color: #15803d; }
.text-orange { color: var(--DC-orange, #e28743); }
.text-pink { color: var(--DC-pink, #d80056); }

/* ====================================================
   PESTAÑAS RÁPIDAS DE ESTADO (SEGMENTED)
==================================================== */
.inventory-tabs-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #ede6dc;
  border-radius: 14px;
  max-width: 100%;
  overflow-x: auto;
}

.tab-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #6d6254;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.tab-nav-btn:hover:not(.active) {
  color: var(--DC-brown, #513119);
  background: rgba(255, 255, 255, 0.4);
}

.tab-nav-btn.active {
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.08);
}

.tab-pill {
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(81, 49, 25, 0.08);
  color: #665b4f;
  font-weight: 800;
}

.tab-nav-btn.active .tab-pill {
  background: var(--DC-orange, #e28743);
  color: white;
}

/* ====================================================
   PANEL DE TABLA UNIFICADO & TOOLBAR
==================================================== */
.table-unified-card {
  background: white;
  border-radius: 18px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.panel-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1.15rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.shift-mode-selector {
  display: inline-flex;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 3px;
  border-radius: 10px;
  gap: 3px;
}

.btn-shift-mode {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-shift-mode.active {
  background: white;
  color: var(--DC-orange, #e28743);
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 12px;
  padding: 0.55rem 0.75rem;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  min-width: 220px;
}

.search-icon {
  color: var(--DC-text-gray, #7c7468);
  flex-shrink: 0;
}

.clear-search-btn {
  background: transparent;
  border: none;
  color: #999;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
}

.date-filter-box {
  position: relative;
  display: flex;
  align-items: center;
}

.date-filter-icon {
  position: absolute;
  left: 10px;
  color: var(--DC-text-gray, #7c7468);
  pointer-events: none;
}

.filter-date-input {
  padding: 0.45rem 0.65rem 0.45rem 32px;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
}

.picker-disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #513119);
  font-size: 0.78rem;
  font-weight: 800;
}

/* ====================================================
   TABLA DESKTOP
==================================================== */
.table-wrapper {
  max-height: 560px;
  overflow-y: auto;
  width: 100%;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.orders-table thead th {
  background: #faf6f0;
  color: var(--DC-brown, #513119);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.8rem 0.6rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.1);
  position: sticky;
  top: 0;
  z-index: 2;
  text-align: left;
}

.orders-table tbody td {
  padding: 0.75rem 0.6rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  font-size: 0.86rem;
}

.orders-table thead th:first-child,
.orders-table tbody td:first-child {
  padding-left: 1.15rem;
}

.orders-table thead th:last-child,
.orders-table tbody td:last-child {
  padding-right: 1.15rem;
}

.orders-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.35);
}

.header-content {
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
}

.sort-icon {
  color: #adb5bd;
}

.sort-icon.active-sort {
  color: var(--DC-orange, #e28743);
}

.comanda-cell {
  display: flex;
  align-items: center;
}

.comanda-badge {
  font-size: 0.88rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  background: #fff7ed;
  padding: 2px 7px;
  border-radius: 6px;
  border: 1px solid #fed7aa;
}

.client-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.client-name {
  color: var(--DC-gray, #2c2724);
  font-size: 0.88rem;
}

.client-phone {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
}

.badges-cell {
  display: flex;
  align-items: center;
  gap: 5px;
  flex-wrap: wrap;
}

.status-badge {
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 800;
  text-transform: uppercase;
}

.status-validation { background: #fff4e6; color: #fd7e14; }
.status-preparation { background: rgba(81, 49, 25, 0.1); color: var(--DC-brown, #513119); }
.status-shipping { background: #e7f5ff; color: #1c7ed6; }
.status-completed { background: #dcfce7; color: #15803d; }
.status-cancelled { background: #fee2e2; color: #b91c1c; }
.status-generic { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.status-paid {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.status-unpaid {
  background: #fff3e0;
  color: #e65100;
  border: 1px solid #ffcc80;
}

.date-content {
  display: flex;
  align-items: center;
  gap: 8px;
}

.time-primary {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.85rem;
  color: var(--DC-gray, #2c2724);
}

.time-icon { color: var(--DC-text-gray, #7c7468); }

.elapsed-badge {
  display: inline-flex;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 800;
}

.elapsed-ok { background: #f1f5f9; color: #475569; }
.elapsed-warning { background: #fef3c7; color: #b45309; }
.elapsed-danger { background: #fee2e2; color: #b91c1c; }

.bold-text {
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.actions {
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-button {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-button:hover {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

/* ====================================================
   PAGINACIÓN
==================================================== */
.inventory-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.15rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.9rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.pagination-info {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.82rem;
}

/* ====================================================
   SKELETON & EMPTY STATES
==================================================== */
.skeleton-row td {
  padding: 16px 20px;
}

.skeleton-pill {
  height: 14px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
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
  gap: 0.65rem;
  padding: 3.5rem 1rem;
  color: var(--DC-text-gray, #7c7468);
  text-align: center;
}

.empty-state p {
  margin: 0;
  font-size: 0.88rem;
}

.btn-retry {
  padding: 8px 18px;
  background: var(--DC-orange, #e28743);
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-retry:hover {
  background: var(--DC-brown, #513119);
}

.text-center { text-align: center; }

/* ====================================================
   RESPONSIVO MÓVIL
==================================================== */
.mobile-only {
  display: none !important;
}

@media (max-width: 900px) {
  .desktop-table-only {
    display: none !important;
  }

  .mobile-only {
    display: flex !important;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem;
  }

  .mobile-order-card {
    background: white;
    border: 1px solid rgba(81, 49, 25, 0.08);
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    cursor: pointer;
  }

  .mobile-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
  }

  .mobile-id-box {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .mobile-card-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
    border-top: 1px dashed rgba(81, 49, 25, 0.08);
    border-bottom: 1px dashed rgba(81, 49, 25, 0.08);
    padding: 6px 0;
  }

  .mobile-client-line {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.9rem;
    font-weight: 800;
    color: var(--DC-gray, #2c2724);
  }

  .mobile-phone-line {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.76rem;
    color: var(--DC-text-gray, #7c7468);
  }

  .mobile-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
  }

  .price-title {
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    color: var(--DC-text-gray, #7c7468);
  }

  .price-value {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--DC-brown, #513119);
  }

  .mobile-actions-row {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .btn-action-mobile {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 8px;
    border: 1px solid rgba(81, 49, 25, 0.15);
    background: white;
    font-size: 0.76rem;
    font-weight: 700;
    color: var(--DC-brown, #513119);
  }

  .btn-advance-mobile {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    border-radius: 8px;
    border: none;
    font-size: 0.76rem;
    font-weight: 800;
    color: white;
  }

  .btn-advance-mobile.step-1 { background: #fd7e14; }
  .btn-advance-mobile.step-2 { background: #0ca678; }
  .btn-advance-mobile.step-3 { background: #1c7ed6; }
}

@media (max-width: 768px) {
  .orders-container {
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

  .header-actions button {
    width: 100%;
    justify-content: center;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .panel-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar-left, .toolbar-right {
    width: 100%;
    flex-direction: column;
  }

  .search-box, .shift-mode-selector, .date-filter-box {
    width: 100%;
    min-width: 0;
  }

  .filter-date-input {
    width: 100%;
    box-sizing: border-box;
  }
}
</style>