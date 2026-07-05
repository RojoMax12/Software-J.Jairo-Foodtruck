<template>
  <div class="dashboard">
    <div class="header">
        <div class="header-icon">
            <UserRoundCogIcon />
        </div>

        <div class="header-text">
            <h1>Gestión de trabajadores</h1>
            <p>Administración trabajadores de Foodtruck J.Junior</p>
        </div>
    </div>

    <div class="cards">
        <div class="card">

            <div class="card-left">
                <div class="icon-box bg-admin">
                    <ShieldCheck :size="24"/>
                </div>
                <span class="card-label">
                    Administradores
                </span>
            </div>

            <div class="card-right">
                <span class="card-count">
                    {{ adminWorkers }}
                </span>
                <span class="card-subtext">
                    Registrados
                </span>
            </div>

        </div>

        <div class="card">

            <div class="card-left">
                <div class="icon-box bg-workers">
                    <Users :size="24"/>
                </div>
                <span class="card-label">
                    Trabajadores
                </span>
            </div>

            <div class="card-right">
                <span class="card-count">
                    {{ regularWorkers }}
                </span>
                <span class="card-subtext">
                    Registrados
                </span>
            </div>

        </div>

        <div class="card">

            <div class="card-left">
                <div class="icon-box bg-active">
                    <UserCheck :size="24"/>
                </div>
                <span class="card-label">
                    Activos
                </span>
            </div>

            <div class="card-right">
                <span class="card-count">
                    {{ activeWorkers }}
                </span>
                <span class="card-subtext">
                    Disponibles
                </span>
            </div>

        </div>

        <div class="card">

            <div class="card-left">
                <div class="icon-box bg-inactive">
                    <UserX :size="24"/>
                </div>
                <span class="card-label">
                    Inactivos
                </span>
            </div>

            <div class="card-right">
                <span class="card-count">
                    {{ inactiveWorkers }}
                </span>
                <span class="card-subtext">
                    Suspendidos
                </span>
            </div>

        </div>

    </div>

    <div class="workers-container">

        <div class="table-toolbar">

            <div class="search-worker">
                <Search :size="18" class="search-icon" />
                <input v-model="searchQuery" placeholder="Buscar trabajador">
            </div>

            <!-- Filtro Rol -->
            <div ref="roleDropdownRef" class="dropdown-container">

                <button
                    class="btn-secondary"
                    @click.stop="toggleRoleDropdown"
                >
                    <Users :size="18"/>

                    <span>
                        {{
                            selectedRole === 'all'
                                ? 'Todos los roles'
                                : selectedRole
                        }}
                    </span>

                    <ChevronDown :size="16"/>
                </button>

                <div
                    class="dropdown-menu"
                    v-if="isRoleDropdownOpen"
                >
                    <div
                        class="dropdown-item"
                        @click="selectRole('all')"
                    >
                        Todos
                    </div>

                    <div class="dropdown-divider"></div>

                    <div
                        class="dropdown-item"
                        @click="selectRole('Administrador')"
                    >
                        Administrador
                    </div>

                    <div
                        class="dropdown-item"
                        @click="selectRole('Trabajador')"
                    >
                        Trabajador
                    </div>

                </div>

            </div>

            <!-- Filtro Estado -->
            <div ref="statusDropdownRef" class="dropdown-container">

                <button
                    class="btn-secondary"
                    @click.stop="toggleStatusDropdown"
                >
                    <CircleDot :size="18"/>

                    <span>{{ selectedStatusLabel }}</span>

                    <ChevronDown :size="16"/>
                </button>

                <div
                    class="dropdown-menu"
                    v-if="isStatusDropdownOpen"
                >
                    <div
                        class="dropdown-item"
                        @click="selectStatus('all')"
                    >
                        Todos
                    </div>

                    <div class="dropdown-divider"></div>

                    <div
                        class="dropdown-item"
                        @click="selectStatus(true)"
                    >
                        Activo
                    </div>

                    <div
                        class="dropdown-item"
                        @click="selectStatus(false)"
                    >
                        Inactivo
                    </div>

                </div>

            </div>

            <button
                class="new-worker"
                @click="openCreateWorkerModal"
            >
                <Plus :size="18" />
                <span>Nuevo trabajador</span>
            </button>

            <CreateWorkerModal
                :isOpen="isCreateWorkerModalOpen"
                @close="closeCreateWorkerModal"
            />

        </div>

        <div class="table-content">

            <table class="workers-table">

                <thead class="table-header">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody class="table-body">

                    <tr
                        v-if="paginatedWorkers.length === 0"
                        class="empty-row"
                    >
                        <td colspan="5">
                            No se encontraron resultados
                        </td>
                    </tr>

                    <tr
                        v-else
                        v-for="worker in paginatedWorkers"
                        :key="worker.id"
                        class="table-row"
                    >

                        <td>{{ worker.id }}</td>

                        <td>{{ worker.nombre }}</td>

                        <td>{{ worker.rol.nombre }}</td>

                        <td>
                            <label
                                class="status-switch"
                                :class="{ active: worker.activo, inactive: !worker.activo }"
                            >
                                <input
                                    type="checkbox"
                                    :checked="worker.activo"
                                    @change="toggleWorker(worker)"
                                />

                                <span class="slider"></span>

                                <span class="status-text">
                                    {{ worker.activo ? "Activo" : "Inactivo" }}
                                </span>
                            </label>
                        </td>

                        <td class="actions-column">

                            <button class="action-icon" @click="openEditWorkerModal(worker)">
                                <SquarePen :size="18" />
                            </button>

                        </td>

                    </tr>

                </tbody>

            </table>

            <EditWorkerModal
                :isOpen="isEditWorkerModalOpen"
                :worker="selectedWorker"
                @close="closeEditWorkerModal"
            />

        </div>

        <div class="table-footer">

            <div class="footer-info">

                <span v-if="totalWorkers > 0">
                    {{ startIndex }}–{{ endIndex }}
                    de
                    {{ totalWorkers }}
                    trabajadores
                </span>

                <span v-else>
                    0 trabajadores
                </span>

            </div>

            <div class="footer-actions">

                <button
                    class="btn secondary"
                    :disabled="totalWorkers === 0 || currentPage === 1"
                    @click="previousPage"
                >
                    Anterior
                </button>

                <span>{{ currentPage }} / {{ totalPages }}</span>

                <button
                    class="btn secondary"
                    :disabled="totalWorkers === 0 || currentPage === totalPages"
                    @click="nextPage"
                >
                    Siguiente
                </button>

            </div>

        </div>

    </div>

  </div>

</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { UserRoundCogIcon, Eye, SquarePen, Search, ShieldCheck, Users, UserCheck, UserX, BriefcaseBusiness, CircleDot, ChevronDown, Plus } from 'lucide-vue-next';
import type { Worker } from "@/views/Admin/worker"
import CreateWorkerModal from '@/views/Admin/CreateWorkerModal.vue';
import EditWorkerModal from '@/views/Admin/EditWorkerModal.vue';

const workers: Worker[] = [

    { id: 1, nombre: "Juan Pérez", rol: { id: 1, nombre: "Administrador" }, activo: true },
    { id: 2, nombre: "María Soto", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 3, nombre: "Carlos Rojas", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 4, nombre: "Ana González", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 5, nombre: "Pedro Muñoz", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 6, nombre: "Camila Torres", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 7, nombre: "Diego Herrera", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 8, nombre: "Fernanda Díaz", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 9, nombre: "José Castillo", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 10, nombre: "Valentina Flores", rol: { id: 2, nombre: "Trabajador" }, activo: false },

    { id: 11, nombre: "Luis Pérez", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 12, nombre: "Daniela Soto", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 13, nombre: "Ignacio Morales", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 14, nombre: "Paula Rojas", rol: { id: 1, nombre: "Administrador" }, activo: true },
    { id: 15, nombre: "Sebastián Vega", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 16, nombre: "Constanza Fuentes", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 17, nombre: "Ricardo Silva", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 18, nombre: "Patricia Núñez", rol: { id: 2, nombre: "Trabajador" }, activo: true },
    { id: 19, nombre: "Tomás Pérez", rol: { id: 2, nombre: "Trabajador" }, activo: false },
    { id: 20, nombre: "Francisca Herrera", rol: { id: 1, nombre: "Administrador" }, activo: true }

]

/* 1. Variables reactivas */
const isRoleDropdownOpen = ref(false)
const isStatusDropdownOpen = ref(false)
const isCreateWorkerModalOpen = ref(false)
const isEditWorkerModalOpen = ref(false)
const searchQuery = ref("")
const roleDropdownRef = ref<HTMLElement | null>(null)
const statusDropdownRef = ref<HTMLElement | null>(null)
const selectedRole = ref('all')
const selectedStatus = ref<"all" | boolean>("all")
const selectedWorker = ref<Worker | null>(null)
const currentPage = ref(1)
const pageSize = 10

/* 2. Funciones para tarjetas estadisticas */
const adminWorkers = computed(() => {
    return workers.filter(
        worker => worker.rol.nombre === 'Administrador'
    ).length
})

const regularWorkers = computed(() => {
    return workers.filter(
        worker => worker.rol.nombre !== 'Administrador'
    ).length
})

const activeWorkers = computed(() =>
    workers.filter(worker => worker.activo).length
)

const inactiveWorkers = computed(() =>
    workers.filter(worker => !worker.activo).length
)

/* 3. Funciones para filtros */
const filteredWorkers = computed(() => {

    return workers.filter(worker => {

        const matchesName =
            worker.nombre
                .toLowerCase()
                .includes(searchQuery.value.toLowerCase())

        const matchesRole =
            selectedRole.value === "all" ||
            worker.rol.nombre === selectedRole.value

        const matchesStatus =
            selectedStatus.value === "all" ||
            worker.activo === selectedStatus.value

        return (
            matchesName &&
            matchesRole &&
            matchesStatus
        )

    })

})

watch(
    [searchQuery, selectedRole, selectedStatus],
    () => {
        currentPage.value = 1
    }
)

const toggleRoleDropdown = () => {
    isStatusDropdownOpen.value = false
    isRoleDropdownOpen.value = !isRoleDropdownOpen.value
}

const toggleStatusDropdown = () => {
    isRoleDropdownOpen.value = false
    isStatusDropdownOpen.value = !isStatusDropdownOpen.value
}

const closeDropdowns = () => {
    isRoleDropdownOpen.value = false
    isStatusDropdownOpen.value = false
}

const handleClickOutside = (event: MouseEvent) => {

    const target = event.target as Node

    const clickedRole =
        roleDropdownRef.value?.contains(target)

    const clickedStatus =
        statusDropdownRef.value?.contains(target)

    if (!clickedRole && !clickedStatus) {
        closeDropdowns()
    }
}

const selectRole = (role: string) => {
    selectedRole.value = role
    isRoleDropdownOpen.value = false
}

const selectStatus = (status: typeof selectedStatus.value) => {
    selectedStatus.value = status
    isStatusDropdownOpen.value = false
}

const selectedStatusLabel = computed(() => {

    if (selectedStatus.value === "all") {
        return "Todos los estados"
    }

    return selectedStatus.value
        ? "Activo"
        : "Inactivo"

})

/* 4. Funciones para controlar el modal de creacion de trabajadores */
const openCreateWorkerModal = () => {
    closeDropdowns()
    isCreateWorkerModalOpen.value = true
}

const closeCreateWorkerModal = () => {
    isCreateWorkerModalOpen.value = false
}

/* 5. Funciones para cambiar el estado de un trabajador */
const toggleWorker = (worker: Worker) => {
    console.log("Cambiar estado de:", worker.id);
};

/* 6. Funciones para controlar el modal de edicion de trabajadores */
const openEditWorkerModal = (worker: Worker) => {
    selectedWorker.value = worker
    isEditWorkerModalOpen.value = true
}

const closeEditWorkerModal = () => {
    isEditWorkerModalOpen.value = false
    selectedWorker.value = null
}

/* 5. Funciones para paginacion */
const totalWorkers = computed(() => filteredWorkers.value.length)

const paginatedWorkers = computed(() => {

    const start = (currentPage.value - 1) * pageSize
    const end = start + pageSize

    return filteredWorkers.value.slice(start, end)

})

const startIndex = computed(() => {
    if (totalWorkers.value === 0)
        return 0

    return (currentPage.value - 1) * pageSize + 1
})

const endIndex = computed(() => {
    return Math.min(
        currentPage.value * pageSize,
        totalWorkers.value
    )
})

const previousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--
    }
}

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
    }
}

const totalPages = computed(() =>
    Math.max(
        1,
        Math.ceil(totalWorkers.value / pageSize)
    )
)

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

</script>

<style scoped>
/* 0. Contenedor principal */
.dashboard{
    display:grid;
    grid-template-columns: 1fr;
    gap: 20px;
    padding: 20px;
}

/* 1. Contenedor titulo */
.header{
    background:#ffffff;
    min-height:50px;

    display:flex;
    align-items: center;
    gap: 20px;
    padding: 20px;
    border: 2px solid lightgrey;
    border-radius: 20px;
}

/* 1.1 Texto titulo */
.header-text{
    display:flex;
    flex-direction:column;
}

/* 2. Contenedor de tarjetas */
.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

/* 2.1 Tarjetas */
.card{
    background:white;
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:16px;
    border-radius:16px;
    box-shadow:0 4px 15px rgba(0,0,0,.05);
    border:2px solid lightgray;
    transition:
        transform .2s ease,
        border-color .2s ease;
}

.card:hover{
    transform:translateY(-4px);
    border-color:var(--DC-orange);
}

/* 2.1.1 Tarjeta seccion izquierda */
.card-left {
    display: flex;
    align-items: center;
    gap: 12px;

    flex:1;
    min-width: 0;
}

/* 2.1.1.1 Icono tarjeta */
.icon-box {
    width: 45px;
    height: 45px;
    border-radius: 12px;

    flex-shrink: 0;

    display: flex;
    align-items: center;
    justify-content: center;
}

/* 2.1.1.2 Nombre tarjeta */
.card-label {
    font-size: 0.85rem;
    font-weight: 800;
    color: var(--DC-gray);
    text-transform: uppercase;
}

/* 2.1.2 Tarjeta seccion derecha */
.card-right {
    display: flex;
    flex-direction: column;
    align-items: flex-end;

    flex-shrink: 0;
}

/* 2.1.2.1 Contador tarjeta */
.card-count {
    font-size: 1.8rem;
    font-weight: 900;
    color: var(--DC-brown);
    line-height: 1;
}

/* 2.1.2.2 Descripcion contador */
.card-subtext {
    font-size: .75rem;
    font-weight:700;
    color:var(--DC-text-gray);
    margin-top:4px;
    text-transform:uppercase;
}

/* 2.1.3 Colores fondo iconos */
.bg-admin{
    background:rgba(59,130,246,.10);
    color:#2563eb;
}

.bg-workers{
    background:rgba(168,85,247,.10);
    color:#9333ea;
}

.bg-active{
    background:rgba(46,196,182,.10);
    color:#2ec4b6;
}

.bg-inactive{
    background:rgba(216,0,86,.10);
    color:var(--DC-pink);
}

/* 3. Contenedor tabla */
.workers-container{
    display:flex;
    flex-direction: column;
    background:#ffffff;
    min-height:350px;
    border: 2px solid lightgray;
    border-radius: 20px;
    overflow: hidden;
}

/* 3.1 Barra de herramientas */
.table-toolbar{
    flex:1;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    padding: 15px;
    background: #ffffff;
}

/* 3.1.1 Barra de busqueda */
.search-worker { position: relative; width: 100%; max-width: 400px; }
.search-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--DC-brown); }
.search-worker input {
  width: 100%; padding: 12px 12px 12px 42px; border-radius: 10px;
  border: 2px solid #eeedee; font-size: 0.95rem; color: var(--DC-gray); font-weight: 600; outline: none; transition: all 0.2s;
}
.search-worker input:focus { border-color: var(--DC-orange); }

/* 3.1.2 Filtros de busqueda */
.dropdown-container { position: relative; }
.btn-secondary { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background-color: white; border: 2px solid #eeedee; border-radius: 10px; color: var(--DC-gray); font-size: 0.9rem; font-weight: 800; cursor: pointer; transition: border-color .2s, background-color .2s, color .2s; }
.btn-secondary:hover { border-color: var(--DC-brown); }

.dropdown-menu { position: absolute; top: calc(100% + 8px); left: 0; background-color: white; border-radius: 12px; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1); border: 2px solid var(--DC-brown); width: 100%; min-width: 220px; z-index: 100; padding: 8px; }
.dropdown-item { padding: 10px 16px; font-size: 0.85rem; font-weight: 700; color: var(--DC-gray); cursor: pointer; border-radius: 8px; transition: background-color .2s, color .2s; }
.dropdown-item:hover { background-color: var(--DC-bg-gray); color: var(--DC-orange); }
.dropdown-divider { height: 1px; background-color: #eeedee; margin: 6px 0; }

/* 3.1.3 Boton de nuevo trabajador */
.new-worker{
    margin-left:auto;

    display:flex;
    align-items:center;
    gap:10px;

    padding:12px 18px;

    border:none;
    border-radius:10px;

    background:var(--DC-orange);
    color:white;

    font-size:.9rem;
    font-weight:800;

    cursor:pointer;

    transition:
        background-color .2s,
        transform .15s,
        box-shadow .2s;
}

.new-worker:hover{
    background:var(--DC-brown);
    box-shadow:0 6px 16px rgba(0,0,0,.12);
}

.new-worker:active{
    transform:scale(.98);
}

.new-worker svg{
    flex-shrink:0;
}

/* 3.2 Contenedor contenido tabla */
.table-content {
    flex: 6;

    width: 100%;
    overflow-x: auto;
    max-height: 220px;
    overflow-y: auto;
}

/* 3.2.1 Contenido tabla */
.workers-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
}

.workers-table th:last-child,
.workers-table td:last-child {
    text-align: center;
}

/* 3.2.1.1 Encabezado de tabla */
.table-header {
    background-color: var(--DC-brown);
}

/* 3.2.1.1.1 Bloques de header */
.table-header th {
    padding: 1rem;
    text-align: left;
    font-size: 0.95rem;
    font-weight: 600;
    color: white;

    position: sticky;
    top: 0;
    z-index: 2;

    background-color: var(--DC-brown);
}

/* 3.2.1.2 Cuerpo de tabla */
.table-body {
    background-color: white;
}

/* 3.2.1.2.1 Filas de la tabla */
.table-row {
    transition: background-color 0.2s ease;
}

.table-row:hover td{
    background-color: #f8f9fa;
}

/* 3.2.1.2.2 Bloques de las filas */
.table-row td {
    padding:1rem;
    border-bottom: 1px solid #e9ecef;
    color: #343a40;
}

/* 3.2.1.2.3 Seccion de switch de estado */
.status-switch {
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;

    padding: 0.35rem 0.8rem;

    border-radius: 999px;

    font-size: 0.85rem;
    font-weight: 600;

    cursor: pointer;
    user-select: none;

    transition: all 0.2s ease;
}

.status-switch input {
    display: none;
}

/* 3.2.1.2.3.1 Texto */
.status-text {
    min-width: 60px;
}

/* 3.2.1.2.3.2 Switch */
.slider {
    position: relative;

    width: 40px;
    height: 22px;

    border-radius: 999px;

    transition: all 0.25s ease;
}

/* 3.2.1.2.3.3 Círculo del switch */
.slider::before {
    content: "";

    position: absolute;
    top: 3px;
    left: 3px;

    width: 16px;
    height: 16px;

    border-radius: 50%;
    background: white;

    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);

    transition: transform 0.25s ease;
}

/* 3.2.1.2.3.4 Color activo */
.status-switch.active {
    color: #198754;
    background: #d1e7dd;
}

.status-switch.active .slider {
    background: #198754;
}

.status-switch.active .slider::before {
    transform: translateX(18px);
}

/* 3.2.1.2.3.5 Color inactivo */
.status-switch.inactive {
    color: #dc3545;
    background: #f8d7da;
}

.status-switch.inactive .slider {
    background: #dc3545;
}

.status-switch.inactive .slider::before {
    transform: translateX(0);
}

/* 3.2.1.2.3.6 Hover */
.status-switch:hover {
    filter: brightness(0.98);
}

.status-switch input:focus-visible + .slider {
    outline: 3px solid rgba(79, 70, 229, 0.2);
    outline-offset: 2px;
}

/* 3.2.1.2.4 Acciones */
.actions-column{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: .5rem;
}

/* 3.2.1.2.4.1 Boton de editar */
.action-icon {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 36px;
    height: 36px;

    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;

    color: #4f46e5;
    cursor: pointer;

    transition:
        background-color 0.2s ease,
        border-color 0.2s ease,
        color 0.2s ease,
        transform 0.15s ease,
        box-shadow 0.2s ease;
}

.action-icon:hover {
    background: #eef2ff;
    border-color: #c7d2fe;
    color: #4338ca;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.12);
}

.action-icon:active {
    transform: scale(0.95);
}

.action-icon:focus-visible {
    outline: none;
    box-shadow:
        0 0 0 3px rgba(79, 70, 229, 0.18),
        0 4px 10px rgba(79, 70, 229, 0.12);
}

/* 3.3 Pie de tabla */
.table-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:20px 24px;
    border-top:2px solid #eeedee;
}

/* 3.3.1 Informacion (total trabajadores) */
.footer-info{
    font-size:.9rem;
    font-weight:700;
    color:var(--DC-gray);
}

/* 3.3.2 Acciones de paginacion */
.footer-actions{
    display:flex;
    align-items:center;
    gap:14px;
}

.footer-actions span{
    min-width:72px;
    padding:10px 14px;
    text-align:center;
    border:2px solid #eeedee;
    border-radius:10px;
    background:white;
    color:var(--DC-gray);
    font-size:.9rem;
    font-weight:800;
}

.footer-actions button{
    display:flex;
    align-items:center;
    justify-content:center;
    min-width:110px;
    padding:12px 18px;
    border:2px solid #eeedee;
    border-radius:10px;
    background:white;
    color:var(--DC-gray);
    font-size:.9rem;
    font-weight:800;
    cursor:pointer;
    transition:
        border-color .2s,
        background-color .2s,
        color .2s;
}

.footer-actions button:hover:not(:disabled){
    border-color:var(--DC-brown);
}

.footer-actions button:disabled{
    opacity:.45;
    cursor:not-allowed;
}

@media (max-width:768px){

    .dashboard{
        grid-template-columns:1fr;
        padding: 15px;
    }

    .header{
        grid-column:1;
    }

    .cards{
        grid-column:1;
    }

    .table-toolbar {
        flex-direction: column;
        align-items: stretch;
    }

    .new-worker {
        margin-left: 0;
    }

}
</style>