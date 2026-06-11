<template>
  <div class="orders-container">
    <div class="filter-card">
      <div class="filter-top">
        <div class="switch-container">
          <div class="switch-slider" :class="{ 'slide-right': activeFilter === 'completed' }"></div>
          <button 
            class="switch-btn" 
            :class="{ active: activeFilter === 'actual' }"
            @click="activeFilter = 'actual'"
          >
            <span>Cotizaciones actuales</span>
            <span class="count-badge">{{ counts.actual }}</span>
          </button>
          <button 
            class="switch-btn" 
            :class="{ active: activeFilter === 'completed' }"
            @click="activeFilter = 'completed'"
          >
            <span>Cotizaciones completadas</span>
            <span class="count-badge">{{ counts.completed }}</span>
          </button>
        </div>

        <div class="filter-actions">
          <div class="search-box">
            <Search :size="18" class="search-icon" />
            <input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Buscar por Distribuidor o ID..."
            />
          </div>
          
          <div class="dropdown-container">
            <button class="btn-filter" @click.stop="toggleStatusDropdown">
              <Filter :size="18" />
              <span>{{ statusFilter === 'all' ? 'Todos los estados' : statusFilter }}</span>
              <ChevronDown :size="16" />
            </button>
            
            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus('Por Tomar')">Por Tomar</div>
              <div class="dropdown-item" @click="selectStatus('En Revision')">En Revision</div>
              <div class="dropdown-item" @click="selectStatus('Completado')">Completado</div>
            </div>
          </div>
        </div>
      </div>

      <div class="summary-cards">
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-pending">
              <ClockAlert :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">Pendientes de revisión</div>
              <div class="summary-description">Cotizaciones sin asignar</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.pending }}</span>
        </div>
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-review">
              <FileSearch :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">En revisión</div>
              <div class="summary-description">Cotizaciones asignadas</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.inReview }}</span>
        </div>
        <div class="summary-card">
          <div class="summary-left">
            <div class="summary-icon-box bg-total">
              <LayoutGrid :size="20" />
            </div>
            <div class="summary-text">
              <div class="summary-label">Total actuales</div>
              <div class="summary-description">Cotizaciones activas</div>
            </div>
          </div>
          <span class="summary-value">{{ stats.totalActual }}</span>
        </div>
      </div>
    </div>
    
    <div class="table-card">
      <table class="orders-table">
        <thead>
          <tr>
            <th @click="sortBy('id')">
              <div class="header-content">
                ID <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'id' }" />
              </div>
            </th>
            <th @click="sortBy('distributor')">
              <div class="header-content">
                DISTRIBUIDOR <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'distributor' }" />
              </div>
            </th>
            <th @click="sortBy('managedBy')">
              <div class="header-content">
                GESTIONADO POR <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'managedBy' }" />
              </div>
            </th>
            <th @click="sortBy('status')">
              <div class="header-content">
                ESTADO <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'status' }" />
              </div>
            </th>
            <th @click="sortBy('total')">
              <div class="header-content">
                TOTAL <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'total' }" />
              </div>
            </th>
            <th @click="sortBy('date')">
              <div class="header-content">
                FECHA <ChevronsUpDown :size="16" class="sort-icon" :class="{ 'active-sort': sortConfig.key === 'date' }" />
              </div>
            </th>
            <th class="text-center">ACCIONES</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="order in sortedOrders" :key="order.id">
            <td class="bold-text">#{{ order.id }}</td>
            <td class="bold-text">{{ order.distributor }}</td>
            <td>
              <div class="user-content">
                <div 
                  class="user-icon-circle"
                  :class="{
                    'case-unassigned': !order.managedBy,
                    'case-others': order.managedBy && order.managedBy.id !== currentUser.id,
                    'case-me': order.managedBy && order.managedBy.id === currentUser.id
                  }"
                >
                  <User :size="14" />
                </div>
                <span>{{ order.managedBy ? order.managedBy.name : 'Sin asignar' }}</span>
              </div>
            </td>
            <td>
              <span class="status-badge" :class="getStatusClass(order.status)">
                {{ order.status }}
              </span>
            </td>
            <td class="bold-text">${{ order.total }}</td>
            <td>
              <div class="date-content">
                <Calendar :size="18" class="date-icon" />
                <div class="date-time">
                  <span class="date">{{ order.date }}</span>
                  <span class="time">{{ order.time }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="actions-content">
                <button 
                  class="btn-action btn-take" 
                  :disabled="order.status !== 'Por Tomar'"
                  @click="takeQuote(order.id)"
                >
                  <UserPlus :size="18" />
                  <span>Tomar</span>
                </button>
                <button class="btn-action btn-detail" @click="openModal(order.id)">
                  <Eye :size="18" />
                  <span>Detalle</span>
                </button>
              </div>
            </td>
          </tr>
        </tbody>

        <tfoot>
          <tr>
            <td colspan="7">
              <div class="footer-content">
                <div class="results-info">
                  Mostrando {{ sortedOrders.length }} de {{ activeFilter === 'actual' ? counts.actual : counts.completed }} resultados
                </div>
                <div class="pagination">
                  <button 
                    class="btn-pagination" 
                    :disabled="currentPage === 1"
                  >
                    <ChevronLeft :size="18" />
                  </button>
                  <div class="page-numbers">
                    <span class="page-num active">1</span>
                  </div>
                  <button 
                    class="btn-pagination" 
                    :disabled="currentPage === totalPages"
                  >
                    <ChevronRight :size="18" />
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tfoot>
      </table>
    </div>

    <QuotesDetailModal 
      v-if="isModalOpen" 
      :order-id="selectedOrderId" 
      :distributor="selectedOrder?.distributor"
      :managed-by="selectedOrder?.managedBy?.name"
      :date="selectedOrder?.date"
      :time="selectedOrder?.time"
      @close="closeModal" 
      @cancel="handleModalCancel"
      @complete="handleModalComplete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import QuotesDetailModal from './QuotesDetailModal.vue';
import quoteService from '@/services/quoteService';
import distributorService from '@/services/distributorService';
import userService from '@/services/userService';
import quotationStatusService from '@/services/quotationStatusService';
import { 
  ChevronsUpDown, 
  UserPlus, 
  Eye, 
  User, 
  Calendar, 
  ChevronLeft, 
  ChevronRight,
  Search,
  Filter,
  ChevronDown,
  ClockAlert,
  FileSearch,
  LayoutGrid
} from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification'; // Importamos el composable de notificaciones


// Pagination logic
const itemsPerPage = 10;
const currentPage = ref(1);
const totalPages = computed(() => Math.max(1, Math.ceil(totalResults.value / itemsPerPage)));

// Filter logic
const activeFilter = ref('actual'); // 'actual' or 'completed'
const searchQuery = ref('');
const statusFilter = ref('all');
const isStatusDropdownOpen = ref(false);


// Modal state
const isModalOpen = ref(false);
const selectedOrderId = ref<number | string>('');

const { notify } = useNotification(); // Extraemos la función de notificación del composable


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

const handleModalCancel = async () => {
  closeModal();
  await fetchQuotes();
};

const handleModalComplete = async () => {
  closeModal();
  await fetchQuotes();
};

const toggleStatusDropdown = () => {
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
};

const selectStatus = (status: string) => {
  statusFilter.value = status;
  isStatusDropdownOpen.value = false;
};

// Close dropdown on click outside
const closeDropdown = (e: MouseEvent) => {
  const target = e.target as HTMLElement;
  if (!target.closest('.dropdown-container')) {
    isStatusDropdownOpen.value = false;
  }
};

const currentUser = ref({ id: 0, name: '' });
const orders = ref<any[]>([]);
const isLoading = ref(true);

const fetchQuotes = async () => {
  isLoading.value = true;
  try {
    const [quotesRes, distsRes, usersRes, statsRes] = await Promise.all([
      quoteService.getQuotes(),
      distributorService.getDistributors(),
      userService.getUsers(),
      quotationStatusService.getStatuses()
    ]);

    const distMap = new Map(distsRes.data.map((d: any) => [d.id, d.nombre_empresa]));
    const userMap = new Map(usersRes.data.map((u: any) => [u.id, u.nombre_usuario]));
    const statMap = new Map(statsRes.data.map((s: any) => [s.id, s.nombre_estado]));

    orders.value = quotesRes.data.map((q: any) => ({
      id: q.id,
      distributor: distMap.get(q.id_distribuidor) || 'Desconocido',
      managedBy: q.id_usuario_dicreme ? { id: q.id_usuario_dicreme, name: userMap.get(q.id_usuario_dicreme) } : null,
      status: statMap.get(q.id_estado_cotizacion) || 'Por Tomar',
      total: Number(q.total_cotizacion).toLocaleString('es-CL'),
      date: formatDate(q.fecha_creacion),
      time: q.hora_creacion ? q.hora_creacion.substring(0, 5) : '',
      rawStatus: q.id_estado_cotizacion
    }));
  } catch (error) {
    console.error('Error fetching data:', error);
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const [year, month, day] = dateString.split('-');
  return `${day}/${month}/${year}`;
};

const takeQuote = async (quoteId: number) => {
  try {
    const responseValidacion = await quoteService.takeQuote(quoteId, currentUser.value.id);
    notify(responseValidacion.data.message, 'success');


    await fetchQuotes();
  } catch (error) {
    
    notify(error.response?.data?.message || 'Error', 'error');
  } 
};

onMounted(async () => {
  window.addEventListener('click', closeDropdown);
  
  const userStored = localStorage.getItem('user');
  if (userStored) {
    const userObj = JSON.parse(userStored);
    currentUser.value = {
      id: userObj.id,
      name: userObj.nombre_usuario || userObj.name
    };
  }
  
  await fetchQuotes();
});

onUnmounted(() => {
  window.removeEventListener('click', closeDropdown);
});

const counts = computed(() => {
  const actual = orders.value.filter((o: any) => ['Por Tomar', 'En Revision'].includes(o.status)).length;
  const completed = orders.value.filter((o: any) => o.status === 'Completado').length;
  return { actual, completed };
});

const stats = computed(() => {
  const pending = orders.value.filter((o: any) => o.status === 'Por Tomar').length;
  const inReview = orders.value.filter((o: any) => o.status === 'En Revision').length;
  const totalActual = pending + inReview;
  return { pending, inReview, totalActual };
});

const filteredOrders = computed(() => {
  let result = orders.value;

  // 1. Category Filter (Actual vs Completed)
  if (activeFilter.value === 'actual') {
    result = result.filter((o: any) => ['Por Tomar', 'En Revision'].includes(o.status));
  } else {
    result = result.filter((o: any) => o.status === 'Completado');
  }

  // 2. Search Query (ID or Distributor)
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((o: any) => 
      o.id.toString().includes(query) || 
      o.distributor.toLowerCase().includes(query)
    );
  }

  // 3. Status Dropdown Filter
  if (statusFilter.value !== 'all') {
    result = result.filter((o: any) => o.status === statusFilter.value);
  }

  return result;
});

const totalResults = computed(() => filteredOrders.value.length);


// Sorting logic
const sortConfig = ref({
  key: 'id',
  direction: 'desc'
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

    if (sortConfig.value.key === 'managedBy') {
      aValue = aValue ? aValue.name : 'Sin asignar';
      bValue = bValue ? bValue.name : 'Sin asignar';
    }

    if (sortConfig.value.key === 'total') {
      aValue = parseFloat(aValue.replace(/\./g, ''));
      bValue = parseFloat(bValue.replace(/\./g, ''));
    }

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

const getStatusClass = (status: string) => {
  switch (status) {
    case 'Por Tomar': return 'status-pending';
    case 'En Revision': return 'status-review';
    case 'Completado': return 'status-completed';
    default: return '';
  }
};
</script>

<style scoped>
.orders-container {
  padding: 40px 20px;
  font-family: 'Inter', sans-serif;
  background-color: #eeedee;
  min-height: 100vh;
}

.filter-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  border: 1px solid #ddd;
  max-width: 1200px;
  margin: 0 auto 24px auto;
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.filter-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.filter-actions {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-box {
  position: relative;
  display: flex;
  align-items: center;
  background-color: #f1f3f4;
  border-radius: 10px;
  padding: 0 12px;
  border: 1px solid #ddd;
  width: 300px;
}

.search-icon {
  color: #777;
}

.search-box input {
  border: none;
  background: transparent;
  padding: 10px 8px;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  color: #322c44;
  width: 100%;
  outline: none;
}

.btn-filter {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 16px;
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 10px;
  color: #322c44;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-filter:hover {
  background-color: #f8f9fa;
  border-color: #ccc;
}

.dropdown-container {
  position: relative;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  border: 1px solid #ddd;
  width: 200px;
  z-index: 100;
  overflow: hidden;
  padding: 6px;
}

.dropdown-item {
  padding: 10px 16px;
  font-size: 0.85rem;
  font-weight: 500;
  color: #322c44;
  cursor: pointer;
  border-radius: 8px;
  transition: all 0.2s ease;
}

.dropdown-item:hover {
  background-color: #fce4ec;
  color: #e4869f;
}

.dropdown-divider {
  height: 1px;
  background-color: #eee;
  margin: 6px 0;
}

.switch-container {
  display: flex;
  background-color: #ddd;
  padding: 4px;
  border-radius: 12px;
  gap: 0;
  position: relative;
  min-width: 500px;
}

.switch-slider {
  position: absolute;
  top: 4px;
  left: 4px;
  bottom: 4px;
  width: calc(50% - 4px);
  background-color: #fce4ec;
  border: 1px solid #e4869f;
  border-radius: 8px;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  z-index: 0;
}

.switch-slider.slide-right {
  transform: translateX(100%);
}

.switch-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  border: none;
  background: transparent !important;
  cursor: pointer;
  font-family: 'Inter', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  color: #322c44;
  position: relative;
  z-index: 1;
  transition: color 0.3s ease;
  white-space: nowrap;
}

.switch-btn.active {
  color: #e4869f;
}

.count-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 24px;
  height: 24px;
  padding: 0 8px;
  border-radius: 8px;
  font-size: 0.75rem;
  font-weight: 700;
  transition: all 0.3s ease;
}

.count-badge {
  background-color: #000;
  color: #fff;
}

.switch-btn.active .count-badge {
  background-color: #e4869f;
  color: #fff;
}

.summary-cards {
  display: flex;
  gap: 16px;
}

.summary-card {
  flex: 1;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 16px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.summary-text {
  display: flex;
  flex-direction: column;
}

.summary-icon-box {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bg-pending { 
  background-color: #fff9db; 
  color: #f08c00; 
}
.bg-review { 
  background-color: #e7f5ff; 
  color: #1c7ed6; 
}
.bg-total { 
  background-color: #e5dbff; 
  color: #7950f2; 
}

.summary-label {
  font-size: 0.9rem;
  font-weight: 800;
  color: #000;
  line-height: 1.2;
}

.summary-description {
  font-size: 0.8rem;
  font-weight: 500;
  color: #999;
  margin-top: 2px;
}

.summary-value {
  font-size: 1.75rem;
  font-weight: 800;
  color: #322c44;
}

.table-card {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow: hidden;
  border: 1px solid #ddd;
  max-width: 1200px;
  margin: 0 auto;
}

.orders-table {
  width: 100%;
  border-collapse: collapse;
}

.orders-table th {
  padding: 16px 20px;
  text-align: left;
  border-bottom: 2px solid #ddd;
  background-color: #e5e5e5 !important;
  color: #777777 !important;
  font-weight: 700 !important;
  cursor: pointer;
  user-select: none;
  font-size: 0.75rem;
}

.orders-table th.text-center {
  text-align: center;
}

.orders-table td {
  padding: 24px 20px;
  text-align: left;
  border-bottom: 1px solid #ddd;
  font-size: 0.9rem;
}

.bold-text {
  font-weight: 700;
  color: #322c44;
}

.user-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.user-icon-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid transparent;
}

.case-unassigned {
  background-color: #e5e5e5;
  color: #777777;
}

.case-others {
  background-color: transparent;
  border-color: #1c7ed6;
  color: #1c7ed6;
}

.case-me {
  background-color: transparent;
  border-color: #e4869f;
  color: #e4869f;
}

.date-content {
  display: flex;
  align-items: center;
  gap: 10px;
}

.date-icon {
  color: #777777;
}

.date-time {
  display: flex;
  flex-direction: column;
}

.time {
  font-size: 0.75rem;
  color: #9793a0;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
  border: 1px solid transparent;
}

.status-pending {
  background-color: #fff9db;
  color: #f08c00;
  border-color: #f08c00;
}

.status-review {
  background-color: #e7f5ff;
  color: #1c7ed6;
  border-color: #1c7ed6;
}

.status-completed {
  background-color: #ebfbee;
  color: #37b24d;
  border-color: #37b24d;
}

.header-content {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
}

.sort-icon {
  color: #999;
}

.actions-content {
  display: flex;
  justify-content: center;
  gap: 8px;
}

.btn-action {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 8px;
  border: none;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-take {
  background-color: #e4869f;
  color: white;
}

.btn-take:hover:not(:disabled) {
  background-color: #d1758e;
}

.btn-take:disabled {
  background-color: #e5e5e5;
  color: #777777;
  cursor: not-allowed;
  border: 1px solid #ddd;
}

.btn-detail {
  background-color: #e5e5e5;
  color: #777777;
  border: 1px solid #ddd;
}

.btn-detail:hover {
  background-color: #d8d8d8;
}

.orders-table tr:last-child td {
  border-bottom: none;
}

.orders-table tfoot td {
  border-top: 1px solid #ddd;
  padding: 16px 20px;
}

.footer-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.results-info {
  color: #777777;
  font-size: 0.85rem;
  font-weight: 500;
}

.pagination {
  display: flex;
  align-items: center;
  gap: 12px;
}

.btn-pagination {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  border: 1px solid #ddd;
  background-color: white;
  color: #322c44;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-pagination:hover:not(:disabled) {
  background-color: #f8f9fa;
  border-color: #ccc;
}

.btn-pagination:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  gap: 4px;
}

.page-num {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #777777;
  cursor: pointer;
  transition: all 0.2s ease;
}

.page-num:hover {
  background-color: #f8f9fa;
}

.page-num.active {
  background-color: #e4869f;
  color: white;
}
</style>
