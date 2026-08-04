<template>
  <div class="orders-container">
    <header class="orders-header">
      <h1 class="orders-title">Pedidos</h1>
      <p class="orders-description">Gestiona y Monitorea todos los pedidos ingresados.</p>
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
          <div class="icon-box bg-paid">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Total: $</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ formatPrice(stats.totalAmount) }}</span>
          <span class="card-subtext">Pesos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-paid">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Total Pagados: $</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ formatPrice(stats.totalPaid) }}</span>
          <span class="card-subtext">Pesos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-preparation">
            <Package :size="24" />
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

    <div class="main-table-card">
      <div class="table-actions">
        <div class="actions-left">
          <div class="search-box">
            <Search :size="18" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Busca por ID de pedido o distribuidor..."
            />
          </div>

          <div class="date-filter-box">
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

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleStatusDropdown">
              <Filter :size="18" />
              <span>{{ statusFilter === 'all' ? 'Todos los estados' : statusFilter }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Pendiente')">Pendiente</div>
              <div class="dropdown-item" @click="selectStatus('Pagado')">Pagado</div>
              <div class="dropdown-item" @click="selectStatus('Anulado')">Anulado</div>
              <div class="dropdown-item" @click="selectStatus('En preparación')">En preparación</div>
              <div class="dropdown-item" @click="selectStatus('Entregado')">Entregado</div>              
            </div>
          </div>

          <button 
            class="btn-live-toggle" 
            :class="{ active: autoRefresh }" 
            @click="autoRefresh = !autoRefresh"
            :title="autoRefresh ? 'Auto-actualización en vivo activa (cada 20s)' : 'Auto-actualización pausada'"
          >
            <RefreshCw :size="16" :class="{ spinning: isLoading }" />
            <span>{{ autoRefresh ? `En vivo (${secondsCountdown}s)` : 'En vivo Pausado' }}</span>
          </button>
        </div>
      </div>

      <div class="table-responsive">
        <table class="orders-table">
          <thead>
            <tr>
              <th @click="sortBy('id')">
                <div class="header-content">
                  ID pedido <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
                </div>
              </th>
              <th @click="sortBy('distributor')">
                <div class="header-content">
                  Nombre <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
                </div>
              </th>
              <th @click="sortBy('status')">
                <div class="header-content">
                  Estado <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
                </div>
              </th>
              <th @click="sortBy('date')">
                <div class="header-content">
                  Fecha <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
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
                  <p>No se encontraron pedidos para esta fecha o filtros.</p>
                  <button @click="fetchOrders" class="btn-retry">Actualizar datos</button>
                </div>
              </td>
            </tr>
            <tr v-else v-for="order in sortedOrders" :key="order.id">
              <td class="bold-text">#{{ order.id }}</td>
              <td class="bold-text">{{ order.distributor }}</td>
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
                  <CalendarIcon :size="18" class="date-icon" />
                  <div class="date-time">
                    <span class="date">{{ order.date }}</span>
                    <span class="time">{{ order.time }}</span>
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
      @status-changed="fetchOrders"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import OrdersDetailModal from './OrdersDetailModal.vue';
import {
  ClipboardCheck, Package, Truck, CheckCircle, Search, Filter,
  ChevronDown, Calendar as CalendarIcon, Eye, ChevronsUpDown,
  RefreshCw, Clock, ArrowRight
} from 'lucide-vue-next';
import orderService  from '@/services/orderService';


const orders = ref<any[]>([]);
const isLoading = ref(true);
const autoRefresh = ref(true);
const secondsCountdown = ref(20);
const refreshInterval = ref<any>(null);
const countdownTimer = ref<any>(null);

const userRole = ref<number | null>(null);

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
    await fetchOrders();
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
]))

const fetchOrders = async () => {
  isLoading.value = true;

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
      const shiftDate = getShiftDateString(o.fecha || o.created_at);
  
      return {
        id: o.numero_pedido_dia || o.id_pedido,
        real_id: o.id_pedido,
        distributor: customerName,
        customer: customerName,
        phone: o.numero_telefono || o.telefono || '',
        status: o.estado_pedido?.nombre || DEFAULT_NAMES[statusId] || `Estado #${statusId}`,
        total: Number(o.total || 0),
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
    console.log(orders.value)
  } catch (error) {
    console.error('Error al cargar pedidos desde API:', error);
  } finally {
    isLoading.value = false;
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

// 🌟 Lógica de Filtros Aplicados
const filteredOrders = computed(() => {
  let result = orders.value;

  if (selectedDate.value) {
    const [year, month, day] = selectedDate.value.split('-');
    const formattedSelectedDate = `${day}/${month}/${year}`;
    result = result.filter((o: any) => o.shiftDate === selectedDate.value || o.date === formattedSelectedDate);
  }

  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => {
      const name = `${o.distributor || o.customer || ''}`.toLowerCase();
      return o.id.toString().includes(query) || name.includes(query);
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
  fetchOrders();
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

onMounted(async () => {
  checkUserRole();
  window.addEventListener('click', closeDropdowns);
  await fetchOrders();

  refreshInterval.value = setInterval(() => {
    if (autoRefresh.value && !isModalOpen.value) {
      fetchOrders();
      secondsCountdown.value = 20;
    }
  }, 20000);

  countdownTimer.value = setInterval(() => {
    if (autoRefresh.value && !isModalOpen.value) {
      secondsCountdown.value = secondsCountdown.value > 1 ? secondsCountdown.value - 1 : 20;
    }
  }, 1000);
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
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
  margin: 0 auto 30px auto;
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

/* 📱 RESPONSIVO: ESTILO APP NATIVA PARA CELULARES */
@media (max-width: 768px) {
  .orders-container { 
    padding: 15px 10px; 
    min-height: auto;
  }
  .orders-header { margin-bottom: 20px; }
  .orders-title { font-size: 1.5rem; }
  .orders-description { font-size: 0.85rem; }

  .status-cards { 
    display: flex; 
    overflow-x: auto; 
    scroll-snap-type: x mandatory; 
    gap: 12px; 
    margin: 0 -10px 20px -10px; 
    padding: 0 10px 10px 10px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; 
  }
  .status-cards::-webkit-scrollbar { display: none; }
  
  .status-card { 
    min-width: 250px; 
    scroll-snap-align: center; 
    padding: 12px 15px;
  }
  .card-count { font-size: 1.5rem; }
  .icon-box { width: 38px; height: 38px; }

  .tabs-outer-container { margin-bottom: 15px; }
  .switch-container { width: 100%; }
  .switch-btn { 
    flex: 1; 
    padding: 8px 5px; 
    font-size: 0.85rem; 
    justify-content: center;
  }

  .table-actions { 
    padding: 15px 10px; 
    flex-direction: column; 
    gap: 12px; 
  }
  .actions-left { 
    display: flex; 
    flex-direction: column; 
    gap: 10px; 
    width: 100%; 
  }
  .search-box { 
    width: 100%; 
    max-width: 100%; 
  }
  .search-box input { padding: 10px 10px 10px 38px; font-size: 0.85rem; }

  .date-filter-box {
    width: 100%;
  }

  .mobile-date-input {
    width: 100%;
    box-sizing: border-box;
    font-size: 16px;
  }

  .card-date-picker {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
    font-size: 16px;
  }
  
  .dropdown-container { width: 100%; }
  .btn-secondary { 
    width: 100%; 
    padding: 10px 8px; 
    font-size: 0.85rem; 
    justify-content: space-between; 
  }
  .btn-secondary span {
    white-space: nowrap; 
    overflow: hidden; 
    text-overflow: ellipsis; 
  }
  .dropdown-menu { width: 100%; min-width: 0; } 

  .actions-right { width: 100%; }
  .btn-export { width: 100%; justify-content: center; padding: 10px; font-size: 0.85rem; }

  .main-table-card { 
    border-radius: 12px; 
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
  }
  .orders-table { 
    display: block; 
    overflow-x: auto; 
    -webkit-overflow-scrolling: touch; 
  }
  .orders-table th, .orders-table td { 
    padding: 12px 10px; 
    font-size: 0.85rem; 
    white-space: nowrap; 
  }
}
</style>