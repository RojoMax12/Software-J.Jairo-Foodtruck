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
          <span class="card-count">{{ stats.unpaid }}</span> 
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
          <span class="card-count">{{ stats.paid }}</span>
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
          <span class="card-count">{{ stats.preparation }}</span>
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
          <span class="card-count">{{ stats.shipping }}</span>
          <span class="card-subtext">Pedidos</span>
        </div>
      </div>

      <div class="status-card">
        <div class="card-left">
          <div class="icon-box bg-generic">
            <CalendarIcon :size="24" />
          </div>
          <span class="card-label">Buscar Fecha</span>
        </div>
        <div class="card-right">
          <input 
            type="date" 
            v-model="selectedDate" 
            class="card-date-picker"
            :disabled="!canEditDate"
            :title="canEditDate ? 'Cambiar fecha de búsqueda' : 'Solo puedes ver los pedidos de hoy'"
            :class="{ 'picker-disabled': !canEditDate }"
          />
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
                <p>No se encontraron pedidos para esta fecha o filtros.</p>
                <button @click="fetchOrders" class="btn-retry">Actualizar datos</button>
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
  ClipboardCheck, Package, Truck, CheckCircle, Search, Filter, 
  ChevronDown, Calendar as CalendarIcon, Download, Eye, ChevronsUpDown
} from 'lucide-vue-next';

const orders = ref<any[]>([]);
const isLoading = ref(true);
const activeTab = ref('pedidos');

const userRole = ref<number | null>(null);

const getTodayString = () => {
  const today = new Date();
  const year = today.getFullYear();
  const month = String(today.getMonth() + 1).padStart(2, '0');
  const day = String(today.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const selectedDate = ref(getTodayString());

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
const statusMap = ref<Map<number, string>>(new Map());

const fetchOrders = async () => {
  isLoading.value = true;
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
      const statusId = Number(o.id_estado_pedido || o.id_estado || 1);
      const distId = Number(o.id_usuario_distribuidor || o.id_distribuidor || 0);
      
      return {
        id: o.id,
        distributor: distMap.get(distId) || `Distribuidor #${distId}`,
        status: statusMap.value.get(statusId) || DEFAULT_NAMES[statusId] || `Estado #${statusId}`,
        total: Number(o.monto_final || o.total_pedido || o.total_cotizacion || 0),
        date: formatDate(o.fecha_creacion || o.fecha_pedido || o.created_at),
        time: (o.hora_creacion || o.created_at || '').substring(0, 5),
        rawStatusId: statusId
      };
    });
  } catch (error) {
    console.error('Error crítico al refrescar la grilla:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'Sin fecha';
  const cleanDate = dateString.includes('T') ? dateString.split('T')[0] : dateString;
  const parts = cleanDate.split('-');
  if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
  return cleanDate;
};

// 🌟 Lógica de Filtros Aplicados
const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Filtro de Fecha (Aplica SIEMPRE según el input calendar)
  if (selectedDate.value) {
    const [year, month, day] = selectedDate.value.split('-');
    const formattedSelectedDate = `${day}/${month}/${year}`;
    result = result.filter((o: any) => o.date === formattedSelectedDate);
  }

  // 2. Tab Filter
  if (activeTab.value === 'pagos') {
    result = result.filter((o: any) => [6, 7].includes(o.rawStatusId));
  } else {
    result = result.filter((o: any) => [1, 2, 3, 4, 5, 7, 8].includes(o.rawStatusId));
  }

  // 3. Search Query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      o.distributor.toLowerCase().includes(query)
    );
  }

  // 4. Status Filter
  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  return result;
});

// Estadísticas basadas EN LOS PEDIDOS FILTRADOS (Para que las tarjetas reflejen el día seleccionado)
const counts = computed(() => {
  const pagos = filteredOrders.value.filter((o: any) => [6, 7].includes(o.rawStatusId)).length;
  const pedidos = filteredOrders.value.filter((o: any) => [1, 2, 3, 4, 5, 7, 8].includes(o.rawStatusId)).length;
  return { pagos, pedidos };
});

const stats = computed(() => {
  return {
    unpaid: filteredOrders.value.filter((o: any) => o.rawStatusId === 6).length,
    paid: filteredOrders.value.filter((o: any) => o.rawStatusId === 7).length,
    preparation: filteredOrders.value.filter((o: any) => o.rawStatusId === 2).length,
    shipping: filteredOrders.value.filter((o: any) => o.rawStatusId === 3).length,
    delivered: filteredOrders.value.filter((o: any) => o.rawStatusId === 4).length
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
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
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
  checkUserRole(); // Verificamos rol
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
  align-items: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 2px solid transparent;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.status-card:hover {
  transform: translateY(-4px);
  border-color: var(--DC-orange);
}

.card-left { display: flex; align-items: center; gap: 12px; }

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
.card-right { display: flex; flex-direction: column; align-items: flex-end; }
.card-count { font-size: 1.8rem; font-weight: 900; color: var(--DC-brown); line-height: 1; }
.card-subtext { font-size: 0.75rem; font-weight: 700; color: var(--DC-text-gray); margin-top: 4px; text-transform: uppercase; }

/* CALENDARIO ESTILOS */
.card-date-picker {
  border: 2px solid #eeedee;
  background-color: #fcfbf9;
  padding: 8px 12px;
  border-radius: 10px;
  color: var(--DC-gray);
  font-weight: 800;
  font-size: 0.9rem;
  outline: none;
  cursor: pointer;
  font-family: inherit;
  transition: all 0.2s ease;
  width: 140px;
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
  overflow: hidden;
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
.table-actions { padding: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
.actions-left { display: flex; gap: 12px; flex: 1; flex-wrap: wrap;}

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

.actions-content { display: flex; justify-content: center; }
.btn-action { display: flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; border: 2px solid var(--DC-orange); background-color: white; color: var(--DC-orange); font-size: 0.85rem; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-action:hover { background-color: var(--DC-orange); color: white; }

.padding-large { padding: 60px !important; }
.loading-container { display: flex; flex-direction: column; align-items: center; gap: 15px; color: var(--DC-brown); font-weight: 900; text-transform: uppercase; }
.spinner { width: 40px; height: 40px; border: 4px solid var(--DC-bg-gray); border-top: 4px solid var(--DC-orange); border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.empty-state { display: flex; flex-direction: column; align-items: center; gap: 15px; color: var(--DC-text-gray); font-weight: 700;}
.empty-icon { color: var(--DC-brown); opacity: 0.5; }
.btn-retry { padding: 10px 24px; background-color: var(--DC-orange); color: white; border: none; border-radius: 8px; font-weight: 900; cursor: pointer; transition: background-color 0.2s; }
.btn-retry:hover { background-color: var(--DC-brown); }

.header-content { display: flex; align-items: center; justify-content: flex-start; gap: 8px; cursor: pointer; }
.sort-icon { color: rgba(255,255,255,0.5); transition: color 0.2s; }
.active-sort { color: var(--DC-orange) !important; }
.orders-table th:hover .sort-icon { color: white; }

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
    min-width: 220px; 
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
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 10px; 
    width: 100%; 
  }
  .search-box { 
    grid-column: 1 / -1; 
    max-width: 100%; 
  }
  .search-box input { padding: 10px 10px 10px 38px; font-size: 0.85rem; }
  
  .dropdown-container { width: 100%; }
  .btn-secondary { 
    width: 100%; 
    padding: 10px 8px; 
    font-size: 0.75rem; 
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