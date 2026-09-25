<template>
  <div class="dashboard">
    <!-- ENCABEZADO SUPERIOR -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Gestión de Trabajadores</h1>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="goToAudit" title="Ver auditoría de movimientos de trabajadores">
          <History :size="16" />
          <span>Ver Auditoría</span>
        </button>

        <button class="btn-primary" @click="openCreateWorkerModal">
          <Plus :size="18" />
          <span>Nuevo Trabajador</span>
        </button>
      </div>
    </header>

    <!-- TARJETAS ESTADÍSTICAS (ESTILO HOMOGÉNEO SUMMARY-GRID) -->
    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-blue">
          <ShieldCheck :size="22" />
        </div>
        <div>
          <span class="summary-label">Administradores</span>
          <strong class="summary-value">{{ adminWorkers }}</strong>
          <p class="summary-helper">Control total del sistema</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <Users :size="22" />
        </div>
        <div>
          <span class="summary-label">Trabajadores</span>
          <strong class="summary-value">{{ regularWorkers }}</strong>
          <p class="summary-helper">Personal operativo y cocina</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <UserCheck :size="22" />
        </div>
        <div>
          <span class="summary-label">Cuentas Activas</span>
          <strong class="summary-value">{{ activeWorkers }}</strong>
          <p class="summary-helper">Con acceso al sistema</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <UserX :size="22" />
        </div>
        <div>
          <span class="summary-label">Inactivos</span>
          <strong class="summary-value">{{ inactiveWorkers }}</strong>
          <p class="summary-helper">Acceso suspendido</p>
        </div>
      </article>
    </section>

    <!-- TABLA PRINCIPAL UNIFICADA -->
    <section class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Buscar por nombre de trabajador..."
            />
            <button v-if="searchQuery" class="clear-search-btn" @click="searchQuery = ''">
              <X :size="14" />
            </button>
          </div>

          <!-- Filtro Rol -->
          <div ref="roleDropdownRef" class="dropdown-container">
            <button class="filter-dropdown-btn" @click.stop="toggleRoleDropdown">
              <Users :size="16" />
              <span>
                {{ selectedRole === 'all' ? 'Todos los roles' : selectedRole }}
              </span>
              <ChevronDown :size="14" />
            </button>

            <div class="dropdown-menu" v-if="isRoleDropdownOpen">
              <div class="dropdown-item" @click="selectRole('all')">Todos los roles</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectRole('Administrador')">Administrador</div>
              <div class="dropdown-item" @click="selectRole('Trabajador')">Trabajador</div>
            </div>
          </div>

          <!-- Filtro Estado -->
          <div ref="statusDropdownRef" class="dropdown-container">
            <button class="filter-dropdown-btn" @click.stop="toggleStatusDropdown">
              <CircleDot :size="16" />
              <span>{{ selectedStatusLabel }}</span>
              <ChevronDown :size="14" />
            </button>

            <div class="dropdown-menu" v-if="isStatusDropdownOpen">
              <div class="dropdown-item" @click="selectStatus('all')">Todos los estados</div>
              <div class="dropdown-divider"></div>
              <div class="dropdown-item" @click="selectStatus(true)">Solo Activos</div>
              <div class="dropdown-item" @click="selectStatus(false)">Solo Inactivos</div>
            </div>
          </div>

          <button 
            v-if="searchQuery || selectedRole !== 'all' || selectedStatus !== 'all'"
            class="btn-reset-filters" 
            type="button" 
            @click="searchQuery = ''; selectedRole = 'all'; selectedStatus = 'all'"
          >
            <X :size="14" />
            <span>Limpiar</span>
          </button>
        </div>

        <div class="toolbar-right">
          <span class="results-chip">{{ filteredWorkers.length }} trabajadores</span>
        </div>
      </div>

      <!-- TABLA ESCRITORIO -->
      <div class="table-wrapper desktop-table-only">
        <table class="workers-table">
          <thead>
            <tr>
              <th style="width: 10%;">ID</th>
              <th style="width: 32%;">Nombre y Cuenta</th>
              <th style="width: 20%;">Rol de Acceso</th>
              <th style="width: 20%;">Estado de Cuenta</th>
              <th style="width: 18%; text-align: center;">Acciones</th>
            </tr>
          </thead>

          <tbody v-if="isLoading">
            <tr v-for="n in 4" :key="'work-skel-' + n" class="skeleton-row">
              <td><div class="skeleton-pill width-50"></div></td>
              <td><div class="skeleton-pill width-120"></div></td>
              <td><div class="skeleton-pill width-80"></div></td>
              <td><div class="skeleton-pill width-70"></div></td>
              <td><div class="skeleton-pill width-60"></div></td>
            </tr>
          </tbody>

          <tbody v-else>
            <tr v-if="paginatedWorkers.length === 0">
              <td colspan="5" class="text-center">
                <div class="state-card empty-state">
                  <UserRoundX :size="42" />
                  <p>No se encontraron trabajadores que coincidan con los filtros.</p>
                  <button @click="loadWorkers" class="btn-retry">Actualizar datos</button>
                </div>
              </td>
            </tr>

            <tr v-else v-for="worker in paginatedWorkers" :key="worker.id_usuario">
              <td>
                <span class="user-id-badge">#{{ worker.id_usuario }}</span>
              </td>
              <td>
                <div class="user-cell-info">
                  <strong>{{ worker.nombre }}</strong>
                  <span v-if="isSelfUser(worker)" class="self-user-pill">Tu cuenta</span>
                </div>
              </td>
              <td>
                <span class="role-pill" :class="worker.id_rol === 1 ? 'role-admin' : 'role-worker'">
                  {{ getRoleName(worker.id_rol) }}
                </span>
              </td>
              <td>
                <label
                  class="status-switch"
                  :class="{ 
                    active: worker.estado, 
                    inactive: !worker.estado,
                    'switch-disabled': isSelfUser(worker)
                  }"
                  :title="isSelfUser(worker) ? 'No puedes desactivar tu propia cuenta' : (worker.estado ? 'Desactivar trabajador' : 'Activar trabajador')"
                >
                  <input
                    type="checkbox"
                    :checked="worker.estado"
                    :disabled="isSelfUser(worker)"
                    @change="toggleWorker(worker)"
                  />
                  <span class="slider"></span>
                  <span class="status-text">
                    {{ worker.estado ? "Activo" : "Inactivo" }}
                  </span>
                </label>
              </td>
              <td>
                <div class="actions">
                  <button 
                    class="icon-button detail-action" 
                    @click="openWorkerDetail(worker)"
                    title="Ver perfil completo"
                  >
                    <Eye :size="15" />
                  </button>
                  <button 
                    class="icon-button edit-action" 
                    @click="openEditWorkerModal(worker)"
                    title="Editar información"
                  >
                    <SquarePen :size="15" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- VISTA TARJETAS MÓVIL -->
      <div class="mobile-workers-cards mobile-only">
        <div v-if="isLoading" class="skeleton-cards-mobile">
          <div v-for="n in 3" :key="'work-skel-mob-' + n" class="mobile-worker-card skeleton-card">
            <div class="skeleton-pill width-120"></div>
            <div class="skeleton-pill width-80"></div>
          </div>
        </div>

        <div v-else-if="paginatedWorkers.length === 0" class="empty-state">
          <UserRoundX :size="40" />
          <p>No se encontraron trabajadores</p>
        </div>

        <div v-else v-for="worker in paginatedWorkers" :key="'mob-w-' + worker.id_usuario" class="mobile-worker-card">
          <div class="mob-worker-header">
            <div>
              <h4 class="mob-worker-name">{{ worker.nombre }}</h4>
              <small class="mob-worker-role">ID #{{ worker.id_usuario }} · {{ getRoleName(worker.id_rol) }}</small>
            </div>
            <span v-if="isSelfUser(worker)" class="self-user-pill">Tu cuenta</span>
          </div>

          <div class="mob-worker-body">
            <label
              class="status-switch"
              :class="{ 
                active: worker.estado, 
                inactive: !worker.estado,
                'switch-disabled': isSelfUser(worker)
              }"
            >
              <input
                type="checkbox"
                :checked="worker.estado"
                :disabled="isSelfUser(worker)"
                @change="toggleWorker(worker)"
              />
              <span class="slider"></span>
              <span class="status-text">{{ worker.estado ? "Activo" : "Inactivo" }}</span>
            </label>
            
            <div class="actions">
              <button class="icon-button detail-action" @click="openWorkerDetail(worker)">
                <Eye :size="15" />
              </button>
              <button class="icon-button edit-action" @click="openEditWorkerModal(worker)">
                <SquarePen :size="15" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- PAGINACIÓN -->
      <div v-if="totalPages > 1 || totalWorkers > 0" class="inventory-pagination">
        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="totalWorkers === 0 || currentPage === 1" 
          @click="previousPage"
        >
          <ChevronLeft :size="16" />
          <span>Anterior</span>
        </button>
        
        <div class="pagination-info">
          Página <strong>{{ currentPage }}</strong> de <strong>{{ totalPages }}</strong>
          <span class="footer-count-hint">({{ totalWorkers }} trabajadores)</span>
        </div>

        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="totalWorkers === 0 || currentPage === totalPages" 
          @click="nextPage"
        >
          <span>Siguiente</span>
          <ChevronRight :size="16" />
        </button>
      </div>
    </section>

    <!-- MODALES -->
    <CreateWorkerModal
      :isOpen="isCreateWorkerModalOpen"
      @close="closeCreateWorkerModal"
      @workerCreated="loadWorkers"
    />

    <ConfirmStatusWorkerModal
      :isOpen="isConfirmStatusModalOpen"
      :isActivating="workerToToggle?.estado === false"
      @close="closeConfirmStatusModal"
      @confirm="confirmToggleWorker"
    />

    <ViewDetailWorkerModal
      :show="showDetailWorkerModal"
      :worker="selectedWorker"
      @close="closeWorkerDetail"
    />

    <EditWorkerModal
      :isOpen="isEditWorkerModalOpen"
      :worker="selectedWorker"
      @close="closeEditWorkerModal"
      @save="updateWorker"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useNotification } from '@/composables/useNotification';
import { 
  SquarePen, Search, ShieldCheck, Users, UserCheck, UserX, CircleDot, 
  ChevronDown, ChevronLeft, ChevronRight, Plus, Eye, UserRoundX, History, X 
} from 'lucide-vue-next';
import userService from '@/services/userService';
import type { Worker, UpdateWorkerRequest } from '@/services/userService';
import CreateWorkerModal from '@/views/Admin/CreateWorkerModal.vue';
import EditWorkerModal from '@/views/Admin/EditWorkerModal.vue';
import ViewDetailWorkerModal from '@/views/Admin/ViewDetailWorkerModal.vue';
import ConfirmStatusWorkerModal from '@/views/Admin/ConfirmStatusWorkerModal.vue';
import { useModalScrollLock } from '@/composables/useModalScrollLock';

const router = useRouter();
const goToAudit = () => {
  router.push('/general-home/admin/history?tipo=trabajador');
};

type RoleFilter = 'all' | 'Administrador' | 'Trabajador';
type StatusFilter = 'all' | true | false;

const { notify } = useNotification();
const isLoading = ref(true);
const workers = ref<Worker[]>([]);

const isRoleDropdownOpen = ref(false);
const isStatusDropdownOpen = ref(false);
const isCreateWorkerModalOpen = ref(false);
const isEditWorkerModalOpen = ref(false);
const showDetailWorkerModal = ref(false);
const isConfirmStatusModalOpen = ref(false);
const workerToToggle = ref<Worker | null>(null);
const searchQuery = ref("");
const roleDropdownRef = ref<HTMLElement | null>(null);
const statusDropdownRef = ref<HTMLElement | null>(null);
const selectedRole = ref<RoleFilter>('all');
const selectedStatus = ref<StatusFilter>('all');
const selectedWorker = ref<Worker | null>(null);
const currentPage = ref(1);
const pageSize = 10;
const currentUserId = ref<number | null>(null);

const loadCurrentUser = () => {
  try {
    const userParsed = localStorage.getItem('user');
    if (userParsed) {
      const userObj = JSON.parse(userParsed);
      currentUserId.value = Number(userObj.id_usuario || userObj.id || null);
    }
  } catch (e) {
    console.error('Error parsing user session:', e);
  }
};

const isSelfUser = (worker: Worker) => {
  return currentUserId.value !== null && Number(worker.id_usuario) === Number(currentUserId.value);
};

const isAnyModalOpen = computed(() =>
  isCreateWorkerModalOpen.value ||
  isEditWorkerModalOpen.value ||
  showDetailWorkerModal.value ||
  isConfirmStatusModalOpen.value
);

useModalScrollLock(isAnyModalOpen);

const loadWorkers = async () => {
  isLoading.value = true;
  try {
    const response = await userService.getWorkers();
    workers.value = response.data;
  } catch (error) {
    console.error('Error cargando trabajadores:', error);
  } finally {
    isLoading.value = false;
  }
};

const adminWorkers = computed(() => {
  return (workers.value || []).filter(worker => worker.id_rol === 1).length;
});

const regularWorkers = computed(() => {
  return (workers.value || []).filter(worker => worker.id_rol === 3).length;
});

const activeWorkers = computed(() =>
  workers.value.filter(worker => worker.estado).length
);

const inactiveWorkers = computed(() =>
  workers.value.filter(worker => !worker.estado).length
);

const filteredWorkers = computed(() => {
  return workers.value.filter(worker => {
    const matchesName = worker.nombre
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());

    const matchesRole =
      selectedRole.value === 'all' ||
      getRoleName(worker.id_rol) === selectedRole.value;

    const matchesStatus =
      selectedStatus.value === 'all' ||
      worker.estado === selectedStatus.value;

    return matchesName && matchesRole && matchesStatus;
  });
});

watch([searchQuery, selectedRole, selectedStatus], () => {
  currentPage.value = 1;
});

const toggleRoleDropdown = () => {
  isStatusDropdownOpen.value = false;
  isRoleDropdownOpen.value = !isRoleDropdownOpen.value;
};

const toggleStatusDropdown = () => {
  isRoleDropdownOpen.value = false;
  isStatusDropdownOpen.value = !isStatusDropdownOpen.value;
};

const closeDropdowns = () => {
  isRoleDropdownOpen.value = false;
  isStatusDropdownOpen.value = false;
};

const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as Node;
  const clickedRole = roleDropdownRef.value?.contains(target);
  const clickedStatus = statusDropdownRef.value?.contains(target);

  if (!clickedRole && !clickedStatus) {
    closeDropdowns();
  }
};

const selectRole = (role: RoleFilter) => {
  selectedRole.value = role;
  isRoleDropdownOpen.value = false;
};

const selectStatus = (status: StatusFilter) => {
  selectedStatus.value = status;
  isStatusDropdownOpen.value = false;
};

const selectedStatusLabel = computed(() => {
  if (selectedStatus.value === "all") return "Todos los estados";
  return selectedStatus.value ? "Solo Activos" : "Solo Inactivos";
});

const getRoleName = (idRol: number) => {
  return idRol === 1 ? 'Administrador' : 'Trabajador';
};

const openWorkerDetail = (worker: Worker) => {
  selectedWorker.value = worker;
  showDetailWorkerModal.value = true;
};

const closeWorkerDetail = () => {
  showDetailWorkerModal.value = false;
  selectedWorker.value = null;
};

const openCreateWorkerModal = () => {
  closeDropdowns();
  isCreateWorkerModalOpen.value = true;
};

const closeCreateWorkerModal = () => {
  isCreateWorkerModalOpen.value = false;
};

const toggleWorker = (worker: Worker) => {
  if (isSelfUser(worker)) {
    notify('No puedes desactivar tu propia cuenta de administrador.', 'warning');
    return;
  }
  workerToToggle.value = worker;
  isConfirmStatusModalOpen.value = true;
};

const closeConfirmStatusModal = () => {
  isConfirmStatusModalOpen.value = false;
  workerToToggle.value = null;
};

const confirmToggleWorker = async () => {
  if (!workerToToggle.value) return;

  const worker = workerToToggle.value;
  if (isSelfUser(worker)) {
    notify('No puedes desactivar tu propia cuenta de administrador.', 'warning');
    closeConfirmStatusModal();
    return;
  }

  const newStatus = !worker.estado;
  const previousStatus = worker.estado;
  worker.estado = newStatus;
  closeConfirmStatusModal();

  try {
    await userService.updateUser(worker.id_usuario, { estado: newStatus });
    notify(
      newStatus
        ? 'El trabajador fue activado correctamente.'
        : 'El trabajador fue desactivado correctamente.',
      'success'
    );
  } catch (error) {
    worker.estado = previousStatus;
    notify('No se pudo actualizar el estado del trabajador.', 'error');
    console.error('Error cambiando estado:', error);
  }
};

const openEditWorkerModal = (worker: Worker) => {
  selectedWorker.value = worker;
  isEditWorkerModalOpen.value = true;
};

const closeEditWorkerModal = () => {
  isEditWorkerModalOpen.value = false;
  selectedWorker.value = null;
};

const updateWorker = async (workerData: UpdateWorkerRequest) => {
  if (!selectedWorker.value) return;
  try {
    await userService.updateUser(selectedWorker.value.id_usuario, workerData);
    await loadWorkers();
    notify('El trabajador fue actualizado correctamente.', 'success');
    closeEditWorkerModal();
  } catch (error: any) {
    console.error('Error al actualizar trabajador:', error);
    if (error.response?.status === 409) {
      notify(error.response.data.message, 'error');
    } else {
      notify('No se pudo actualizar el trabajador.', 'error');
    }
  }
};

const totalWorkers = computed(() => filteredWorkers.value.length);

const paginatedWorkers = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredWorkers.value.slice(start, start + pageSize);
});

const previousPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

const totalPages = computed(() =>
  Math.max(1, Math.ceil(totalWorkers.value / pageSize))
);

onMounted(() => {
  loadCurrentUser();
  document.addEventListener('click', handleClickOutside);
  loadWorkers();
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.dashboard {
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

.btn-primary:hover {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.2);
}

/* ====================================================
   KPIS SUMMARY-GRID (4 COLUMNAS HOMOGÉNEAS)
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
}

.panel-card,
.summary-card {
  background: white;
  border-radius: 18px;
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.summary-card {
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

.bg-summary-blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }

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
  font-size: 1.5rem;
  line-height: 1.1;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
  margin: 0;
}

/* ====================================================
   TARJETA DE TABLA UNIFICADA & TOOLBAR
==================================================== */
.table-unified-card {
  padding: 0;
  overflow: hidden;
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
  min-width: 240px;
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

.dropdown-container {
  position: relative;
}

.filter-dropdown-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.55rem 0.85rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 12px;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.filter-dropdown-btn:hover {
  border-color: var(--DC-orange, #e28743);
  background: white;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
  border: 1px solid rgba(81, 49, 25, 0.12);
  min-width: 180px;
  z-index: 100;
  padding: 6px;
}

.dropdown-item {
  padding: 8px 12px;
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  cursor: pointer;
  border-radius: 8px;
  transition: background 0.15s, color 0.15s;
}

.dropdown-item:hover {
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-orange, #e28743);
}

.dropdown-divider {
  height: 1px;
  background: rgba(81, 49, 25, 0.08);
  margin: 4px 0;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filters:hover {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #fca5a5;
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

.workers-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.workers-table thead th {
  background: #faf6f0;
  color: var(--DC-brown, #513119);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.8rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.1);
  position: sticky;
  top: 0;
  z-index: 2;
  text-align: left;
}

.workers-table tbody td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  font-size: 0.86rem;
}

.workers-table thead th:first-child,
.workers-table tbody td:first-child {
  padding-left: 1.15rem;
}

.workers-table thead th:last-child,
.workers-table tbody td:last-child {
  padding-right: 1.15rem;
}

.workers-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.35);
}

.user-id-badge {
  font-weight: 800;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
}

.user-cell-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.user-cell-info strong {
  color: var(--DC-gray, #2c2724);
  font-size: 0.9rem;
}

.self-user-pill {
  font-size: 0.68rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: #dcfce7;
  color: #15803d;
  font-weight: 800;
  border: 1px solid #bbf7d0;
}

.role-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 800;
}

.role-admin {
  background: rgba(59, 130, 246, 0.12);
  color: #1d4ed8;
}

.role-worker {
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

/* ====================================================
   SWITCH DE ESTADO REFINADO
==================================================== */
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

.status-switch input {
  display: none;
}

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
  color: var(--DC-pink, #d80056);
  background: rgba(216, 0, 86, 0.12);
}

.status-switch.inactive .slider {
  background: var(--DC-pink, #d80056);
}

.status-switch.inactive .slider::before {
  transform: translateX(0);
}

.status-switch.switch-disabled {
  opacity: 0.45;
  cursor: not-allowed !important;
}

/* ====================================================
   BOTONES DE ACCIÓN
==================================================== */
.actions {
  display: flex;
  align-items: center;
  gap: 5px;
  justify-content: center;
}

.icon-button {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.detail-action {
  color: #2563eb;
}

.detail-action:hover {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
}

.edit-action {
  color: var(--DC-orange, #e28743);
}

.edit-action:hover {
  background: #fff7ed;
  border-color: #fed7aa;
  color: #c2410c;
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

.footer-count-hint {
  color: #9ca3af;
  margin-left: 4px;
}

/* ====================================================
   SKELETON & EMPTY STATES
==================================================== */
@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

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

.width-50 { width: 50px; }
.width-60 { width: 60px; }
.width-70 { width: 70px; }
.width-80 { width: 80px; }
.width-120 { width: 120px; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
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

.text-center {
  text-align: center;
}

/* ====================================================
   RESPONSIVO MÓVIL
==================================================== */
.mobile-only {
  display: none !important;
}

@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .dashboard {
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

  .toolbar-left {
    flex-direction: column;
    width: 100%;
  }

  .search-box,
  .dropdown-container,
  .filter-dropdown-btn {
    width: 100%;
  }

  .search-box input {
    min-width: 0;
    width: 100%;
  }

  .desktop-table-only {
    display: none !important;
  }

  .mobile-only {
    display: flex !important;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem;
  }

  .mobile-worker-card {
    background: white;
    border: 1px solid rgba(81, 49, 25, 0.08);
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .mob-worker-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
  }

  .mob-worker-name {
    margin: 0;
    font-size: 0.95rem;
    font-weight: 800;
    color: var(--DC-gray, #2c2724);
  }

  .mob-worker-role {
    font-size: 0.75rem;
    color: var(--DC-text-gray, #7c7468);
  }

  .mob-worker-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px dashed rgba(81, 49, 25, 0.08);
    padding-top: 0.65rem;
  }
}
</style>