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
          <span class="card-label">Por pagar</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.unpaid }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-paid">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Pagada</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.paid }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-preparation">
            <Package :size="24" />
          </div>
          <span class="card-label">En preparación</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.preparation }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-shipping">
            <Truck :size="24" />
          </div>
          <span class="card-label">En despacho</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.shipping }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-delivered">
            <CheckCircle :size="24" />
          </div>
          <span class="card-label">Entregado</span>
        </div>
        <div class="card-right">
          <span class="card-count">{{ stats.delivered }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>
    </div>

    <div class="tabs-outer-container">
      <div class="switch-container">
        <div class="switch-slider" :class="{ 'slide-right': activeTab === 'pedidos' }"></div>
        <button 
          class="switch-btn" 
          :class="{ active: activeTab === 'pagos' }"
          @click="activeTab = 'pagos'"
        >
          <span>Pagos</span>
          <span class="count-badge">{{ counts.pagos }}</span>
        </button>
        <button 
          class="switch-btn" 
          :class="{ active: activeTab === 'pedidos' }"
          @click="activeTab = 'pedidos'"
        >
          <span>Pedidos</span>
          <span class="count-badge">{{ counts.pedidos }}</span>
        </button>
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

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleStatusDropdown">
              <Filter :size="18" />
              <span>{{ statusFilter === 'all' ? 'Todos los estados' : statusFilter }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Por pagar')">Por pagar</div>
              <div class="dropdown-item" @click="selectStatus('Pagada')">Pagada</div>
              <div class="dropdown-item" @click="selectStatus('En preparación')">En preparación</div>
              <div class="dropdown-item" @click="selectStatus('En despacho')">En despacho</div>
              <div class="dropdown-item" @click="selectStatus('Entregado')">Entregado</div>
            </div>
          </div>

          <div class="dropdown-container">
            <button class="btn-secondary" @click.stop="toggleDateDropdown">
              <Calendar :size="18" />
              <span>{{ dateFilterLabel }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isDateDropdownOpen">
              <div class="dropdown-item" @click="selectDateFilter('last30', 'Fecha: Últimos 30 días')">Últimos 30 días</div>
              <div class="dropdown-item" @click="selectDateFilter('last3months', 'Fecha: Últimos 3 meses')">Últimos 3 meses</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectDateFilter('custom', 'Rango personalizado')">Rango personalizado</div>
            </div>
          </div>
        </div>

        <div class="actions-right">
          <button class="btn-export">
            <Download :size="18" />
            <span>Exportar</span>
          </button>
        </div>
      </div>

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
                Distribuidor <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
              </div>
            </th>
            <th @click="sortBy('status')">
              <div class="header-content">
                Estado <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
              </div>
            </th>
            <th @click="sortBy('date')">
              <div class="header-content">
                Fecha de ingreso <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
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
          <tr v-if="isLoading">
            <td colspan="6" class="text-center padding-large">
              <div class="loading-container">
                <div class="spinner"></div>
                <span>Cargando pedidos...</span>
              </div>
            </td>
          </tr>
          <tr v-else-if="sortedOrders.length === 0">
            <td colspan="6" class="text-center padding-large">
              <div class="empty-state">
                <Package :size="48" class="empty-icon" />
                <p>No se encontraron pedidos en esta sección.</p>
                <button @click="fetchOrders" class="btn-retry">Reintentar carga</button>
              </div>
            </td>
          </tr>
          <tr v-else v-for="order in sortedOrders" :key="order.id">
            <td class="bold-text">#{{ order.id }}</td>
            <td class="bold-text">{{ order.distributor }}</td>
            <td>
              <span class="status-badge" :class="getStatusClass(order.status, order.rawStatusId)">
                {{ order.status }}
              </span>
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

    <OrdersDetailModal 
      v-if="isModalOpen" 
      :order-id="selectedOrderId" 
      :distributor="selectedOrder?.distributor"
      :status="selectedOrder?.status"
      :status-id="selectedOrder?.rawStatusId"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      :total="selectedOrder?.total"
      @close="closeModal" 
      @status-changed="fetchOrders"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import OrdersDetailModal from './OrdersDetailModal.vue';
import orderService from '@/services/orderService';
import distributorService from '@/services/distributorService';
import orderStatusService from '@/services/orderStatusService';
import { 
  ClipboardCheck, 
  Package, 
  Truck, 
  CheckCircle,
  Search,
  Filter,
  ChevronDown,
  Calendar,
  Calendar as CalendarIcon,
  Download,
  Eye,
  ChevronsUpDown
} from 'lucide-vue-next';

const orders = ref<any[]>([]);
const isLoading = ref(true);
const activeTab = ref('pedidos');

// Mapa reactivo que se llenará EXCLUSIVAMENTE desde la BDD
const statusMap = ref<Map<number, string>>(new Map());

const fetchOrders = async () => {
  isLoading.value = true;
  console.log('--- [DEBUG] INICIANDO CARGA COMPLETA ---');
  
  try {
    let ordersRes, distsRes, statsRes;
    
    try { ordersRes = await orderService.getOrders(); } catch (e) { console.error(e); }
    try { distsRes = await distributorService.getDistributors(); } catch (e) { console.error(e); }
    try { statsRes = await orderStatusService.getOrderStatuses(); } catch (e) { console.error(e); }

    const rawOrders = Array.isArray(ordersRes?.data) ? ordersRes.data : (ordersRes?.data?.data || []);
    const rawDists = Array.isArray(distsRes?.data) ? distsRes.data : (distsRes?.data?.data || []);
    const rawStats = Array.isArray(statsRes?.data) ? statsRes.data : (statsRes?.data?.data || []);

    statusMap.value = new Map(rawStats.map((s: any) => [Number(s.id), s.nombre_estado || s.nombre_estado_pedido]));
    const distMap = new Map(rawDists.map((d: any) => [Number(d.id), d.nombre_empresa]));

    const DEFAULT_NAMES: Record<number, string> = {
      1: 'En validación', 2: 'En preparación', 3: 'En despacho', 
      4: 'Entregado', 5: 'Pendiente', 6: 'Por pagar', 7: 'Pagada', 8: 'Cancelado'
    };

    orders.value = rawOrders.map((o: any) => {
      // 🛡️ Buscamos el ID tolerando si viene en formato string o número desde Laravel
      const statusId = Number(o.id_estado_pedido || o.id_estado || 1);
      const distId = Number(o.id_usuario_distribuidor || o.id_distribuidor || 0);
      
      return {
        id: o.id,
        distributor: distMap.get(distId) || `Distribuidor #${distId}`,
        // Sincronización perfecta de strings:
        status: statusMap.value.get(statusId) || DEFAULT_NAMES[statusId] || `Estado #${statusId}`,
        total: Number(o.monto_final || o.total_pedido || o.total_cotizacion || 0),
        date: formatDate(o.fecha_creacion || o.fecha_pedido || o.created_at),
        time: (o.hora_creacion || o.created_at || '').substring(0, 5),
        rawStatusId: statusId
      };
    });

    console.log('Mapeo de grilla general refrescado con éxito:', orders.value.length);

  } catch (error) {
    console.error('Error crítico al refrescar la grilla:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Sin fecha';
  // Si viene con T (ISO), tomamos solo la parte de la fecha
  const cleanDate = dateString.includes('T') ? dateString.split('T')[0] : dateString;
  const parts = cleanDate.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return cleanDate;
};

const counts = computed(() => {
  // Pagos: Por pagar (6) y Pagada (7)
  const pagos = orders.value.filter((o: any) => [6, 7].includes(o.rawStatusId)).length;
  // Pedidos: Todo lo demás + Pagada (7) para que no se pierda el rastro
  const pedidos = orders.value.filter((o: any) => [1, 2, 3, 4, 5, 7, 8].includes(o.rawStatusId)).length;
  return { pagos, pedidos };
});

const stats = computed(() => {
  return {
    unpaid: orders.value.filter((o: any) => o.rawStatusId === 6).length,
    paid: orders.value.filter((o: any) => o.rawStatusId === 7).length,
    preparation: orders.value.filter((o: any) => o.rawStatusId === 2).length,
    shipping: orders.value.filter((o: any) => o.rawStatusId === 3).length,
    delivered: orders.value.filter((o: any) => o.rawStatusId === 4).length
  };
});

const formatPrice = (price: number) => {
  return price.toLocaleString('es-CL');
};

const getStatusClass = (status: string, statusId?: number) => {
  // Si viene el ID numérico directo de PostgreSQL, es mucho más rápido y seguro validar por número:
  if (statusId) {
    switch (Number(statusId)) {
      case 1: return 'status-validation';  // En validación
      case 2: return 'status-preparation'; // En preparación
      case 3: return 'status-shipping';    // En despacho
      case 4: return 'status-completed';   // Entregado
      case 5: return 'status-pending';     // Pendiente
      case 6: return 'status-unpaid';      // Por pagar
      case 7: return 'status-paid';        // Pagada
      case 8: return 'status-cancelled';   // Cancelado
    }
  }

  // Respaldo por si evalúa mediante el string de texto:
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
const dateFilterLabel = ref('Fecha: Últimos 30 días');

const isStatusDropdownOpen = ref(false);
const isDateDropdownOpen = ref(false);

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
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
  isDateDropdownOpen.value = false;
};

const toggleDateDropdown = () => {
  isDateDropdownOpen.value = !isDateDropdownOpen.value;
  isStatusDropdownOpen.value = false;
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  isStatusDropdownOpen.value = false;
};

const selectDateFilter = (type: string, label: string) => {
  dateFilterLabel.value = label;
  isDateDropdownOpen.value = false;
};

const closeDropdowns = () => {
  isStatusDropdownOpen.value = false;
  isDateDropdownOpen.value = false;
};

// Filtering logic
const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Tab Filter
  if (activeTab.value === 'pagos') {
    result = result.filter((o: any) => [6, 7].includes(o.rawStatusId));
  } else {
    // Pedidos incluye todo excepto "Por pagar" (6), pero incluye "Pagada" (7)
    result = result.filter((o: any) => [1, 2, 3, 4, 5, 7, 8].includes(o.rawStatusId));
  }

  // 2. Search Query (ID or Distributor)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      o.distributor.toLowerCase().includes(query)
    );
  }

  // 3. Status Filter
  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  return result;
});

const sortConfig = ref({
  key: '',
  direction: 'asc'
});

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
      const parseDate = (d: string, t: string) => {
        const [day, month, year] = d.split('/').map(Number);
        const [hours, minutes] = t.split(':').map(Number);
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
  window.addEventListener('click', closeDropdowns);
  await fetchOrders();
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdowns);
});
</script>

<style scoped>
.orders-container {
  padding: 40px 20px;
  font-family: 'Inter', sans-serif;
  background-color: #f8f9fa;
  min-height: calc(100vh - 80px);
}

.orders-header {
  max-width: 1200px;
  margin: 0 auto 30px auto;
}

.orders-title {
  font-size: 1.8rem;
  font-weight: 800;
  color: #322c44;
  margin: 0;
}

.orders-description {
  font-size: 0.95rem;
  color: #666;
  margin-top: 4px;
}

.status-cards {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
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
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.status-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
}

.card-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-unpaid { background-color: #fff0f3; color: #e4869f; }
.bg-paid { background-color: #ebfbee; color: #37b24d; }
.bg-preparation { background-color: #e7f5ff; color: #1c7ed6; }
.bg-shipping { background-color: #dcd5ff; color: #6741d9; }
.bg-delivered { background-color: #e6fffa; color: #087f5b; }

.card-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: #322c44;
}

.card-right {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.card-count {
  font-size: 1.6rem;
  font-weight: 800;
  color: #322c44;
  line-height: 1;
}

.card-subtext {
  font-size: 0.7rem;
  font-weight: 500;
  color: #868e96;
  margin-top: 2px;
}

.main-table-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  overflow: hidden;
  max-width: 1200px;
  margin: 0 auto;
}

.tabs-outer-container {
  max-width: 1200px;
  margin: 0 auto 10px auto;
  display: flex;
  justify-content: flex-start;
}

.switch-container {
  display: flex;
  background-color: #f1f3f5;
  padding: 4px;
  border-radius: 12px;
  width: fit-content;
  position: relative;
}

.switch-slider {
  position: absolute;
  top: 4px;
  left: 4px;
  width: calc(50% - 4px);
  height: calc(100% - 8px);
  background-color: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 1;
}

.switch-slider.slide-right {
  transform: translateX(100%);
}

.switch-btn {
  background: none;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 700;
  color: #868e96;
  cursor: pointer;
  position: relative;
  z-index: 2;
  transition: color 0.3s ease;
  display: flex;
  align-items: center;
  gap: 10px;
}

.switch-btn.active {
  color: #e4869f;
}

.count-badge {
  background-color: #e9ecef;
  color: #495057;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
}

.switch-btn.active .count-badge {
  background-color: #fff0f3;
  color: #e4869f;
}

.table-actions {
  padding: 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.actions-left {
  display: flex;
  gap: 12px;
  flex: 1;
}

.search-box {
  position: relative;
  width: 100%;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #adb5bd;
}

.search-box input {
  width: 100%;
  padding: 12px 12px 12px 42px;
  border-radius: 10px;
  border: 1px solid #dee2e6;
  font-size: 0.9rem;
  color: #495057;
  outline: none;
  transition: border-color 0.2s;
}

.search-box input:focus {
  border-color: #e4869f;
}

.dropdown-container {
  position: relative;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  background-color: white;
  border: 1px solid #dee2e6;
  border-radius: 10px;
  color: #495057;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-secondary:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid #e9ecef;
  min-width: 220px;
  z-index: 100;
  padding: 8px;
}

.dropdown-item {
  padding: 10px 16px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #495057;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background-color: #fff0f3;
  color: #e4869f;
}

.dropdown-divider {
  height: 1px;
  background-color: #e9ecef;
  margin: 6px 0;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 20px;
  background-color: white;
  border: 1.5px solid #e4869f;
  border-radius: 10px;
  color: #e4869f;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-export:hover {
  background-color: #fff0f3;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  padding: 16px 20px;
  text-align: left;
  border-top: 1px solid #dee2e6;
  border-bottom: 2px solid #ddd;
  background-color: #e5e5e5 !important;
  color: #777777 !important;
  font-weight: 700 !important;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  user-select: none;
}

.orders-table th.text-center {
  text-align: center;
}

.orders-table td {
  padding: 20px;
  text-align: left;
  border-bottom: 1px solid #f1f3f5;
  font-size: 0.9rem;
  color: #495057;
}

.bold-text {
  font-weight: 700;
  color: #322c44;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
}

.status-unpaid { background-color: #fff0f3; color: #e4869f; border: 1px solid #e4869f; }
.status-paid { background-color: #ebfbee; color: #37b24d; border: 1px solid #37b24d; }
.status-preparation { background-color: #e7f5ff; color: #1c7ed6; border: 1px solid #1c7ed6; }
.status-shipping { background-color: #dcd5ff; color: #6741d9; border: 1px solid #6741d9; }
.status-completed { background-color: #e6fffa; color: #087f5b; border: 1px solid #087f5b; }
.status-validation { background-color: #fff4e6; color: #fd7e14; border: 1px solid #fd7e14; }
.status-pending { background-color: #f8f9fa; color: #495057; border: 1px solid #ced4da; }
.status-cancelled { background-color: #f8d7da; color: #fa5252; border: 1px solid #fa5252; }
.status-generic { background-color: #f1f3f5; color: #868e96; border: 1px solid #ced4da; }

.date-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-time {
  display: flex;
  flex-direction: column;
}

.date {
  font-weight: 600;
  color: #495057;
}

.time {
  font-size: 0.75rem;
  color: #9793a0;
}

.date-icon {
  color: #adb5bd;
}

.actions-content {
  display: flex;
  justify-content: center;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  border: 1px solid #dee2e6;
  background-color: white;
  color: #495057;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-action:hover {
  background-color: #f8f9fa;
  border-color: #ced4da;
}

.padding-large {
  padding: 60px !important;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  color: #9793a0;
  font-weight: 600;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #eeedee;
  border-top: 4px solid #e4869f;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
  color: #9793a0;
}

.empty-icon {
  color: #eeedee;
}

.btn-retry {
  padding: 8px 20px;
  background-color: #e4869f;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: background-color 0.2s;
}

.btn-retry:hover {
  background-color: #d6758e;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  cursor: pointer;
}

.sort-icon {
  color: #999;
  transition: color 0.2s;
}

.active-sort {
  color: #322c44 !important;
}

.orders-table th:hover .sort-icon {
  color: #666;
}
</style>