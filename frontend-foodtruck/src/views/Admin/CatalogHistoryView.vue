<template>
    <div class="history-page">
        <!-- ===================== HEADER ===================== -->
        <section class="page-header">
            <div class="header-left">
                <div class="title-with-badges">
                    <h1>Auditoría del Sistema & Actividad</h1>
                    <div class="header-stat-pills">
                        <span class="stat-pill"><strong>{{ historyList.length }}</strong> registros totales</span>
                    </div>
                </div>
                <p>
                    Trazabilidad integral en tiempo real: creación, cambios de estado, entregas al cliente, cobros, catálogo gastronómico, control de inventario y sesiones de caja.
                </p>
            </div>

            <div class="header-actions">
                <button class="btn-refresh" @click="loadHistory" :disabled="isLoading" title="Actualizar auditoría desde el servidor">
                    <RefreshCw :size="16" :class="{ spinning: isLoading }" />
                    <span>{{ isLoading ? 'Cargando...' : 'Actualizar' }}</span>
                </button>
            </div>
        </section>

        <!-- ===================== RESUMEN DE AUDITORÍA ===================== -->
        <section class="audit-summary-grid">
            <div class="summary-kpi-card">
                <div class="kpi-icon-box bg-summary-brown">
                    <History :size="22" />
                </div>
                <div class="kpi-info">
                    <span class="kpi-label">Total Registros</span>
                    <strong class="kpi-val">{{ historyList.length }}</strong>
                    <small class="kpi-hint">Auditoría global registrada</small>
                </div>
            </div>

            <div class="summary-kpi-card">
                <div class="kpi-icon-box bg-summary-green">
                    <PackageCheck :size="22" />
                </div>
                <div class="kpi-info">
                    <span class="kpi-label">Pedidos & Entregas</span>
                    <strong class="kpi-val val-green">{{ orderDeliveriesCount }}</strong>
                    <small class="kpi-hint">Despachados al cliente</small>
                </div>
            </div>

            <div class="summary-kpi-card">
                <div class="kpi-icon-box bg-summary-orange">
                    <DollarSign :size="22" />
                </div>
                <div class="kpi-info">
                    <span class="kpi-label">Pagos Confirmados</span>
                    <strong class="kpi-val val-orange">{{ paymentsCount }}</strong>
                    <small class="kpi-hint">Transacciones cobradas</small>
                </div>
            </div>

            <div class="summary-kpi-card">
                <div class="kpi-icon-box bg-summary-pink">
                    <Pencil :size="22" />
                </div>
                <div class="kpi-info">
                    <span class="kpi-label">Modificaciones</span>
                    <strong class="kpi-val val-pink">{{ editsCount }}</strong>
                    <small class="kpi-hint">Ajustes en pedidos o menú</small>
                </div>
            </div>
        </section>

        <!-- ===================== TABLA Y TIMELINE ===================== -->
        <section class="table-container">
            <div class="table-toolbar">
                <div class="search-box">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por comanda, cliente, producto, trabajador, acción..."
                    >
                    <button v-if="search" class="clear-search-btn" @click="search = ''">
                        <X :size="14" />
                    </button>
                </div>

                <div class="filters-inline">
                    <div class="filter-item">
                        <select v-model="filterType" class="filter-select">
                            <option value="">Todos los módulos</option>
                            <option value="pedido">📦 Pedidos & Despacho</option>
                            <option value="producto">🍔 Productos & Menú</option>
                            <option value="categoria">📁 Categorías</option>
                            <option value="tamaño">🏷️ Tamaños y Formatos</option>
                            <option value="oferta">🔥 Ofertas Promocionales</option>
                            <option value="stock">📊 Stock & Inventario</option>
                            <option value="caja">💵 Caja & Turnos</option>
                            <option value="trabajador">👥 Trabajadores & Personal</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <select v-model="filterAction" class="filter-select">
                            <option value="">Todas las acciones</option>
                            <option value="crear">➕ Creación</option>
                            <option value="entregado">🚀 Entregas</option>
                            <option value="pago">💳 Pagos</option>
                            <option value="estado">⏱️ Cambio de Estado</option>
                            <option value="editar">✏️ Modificación</option>
                            <option value="cancelar">❌ Cancelación</option>
                            <option value="eliminar">🗑️ Eliminación</option>
                            <option value="apertura">🔓 Apertura de Caja</option>
                            <option value="cierre">🔒 Cierre de Turno</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <select v-model="filterDateRange" class="filter-select">
                            <option value="7days">🗓️ Últimos 7 días</option>
                            <option value="today">📅 Solo Hoy</option>
                            <option value="30days">📆 Últimos 30 días</option>
                            <option value="custom">🎯 Rango de Fechas</option>
                            <option value="all">🌐 Todo el Historial</option>
                        </select>
                    </div>

                    <div v-if="filterDateRange === 'custom'" class="date-custom-group">
                        <input v-model="filterDateFrom" type="date" class="filter-date-input" title="Fecha inicio" />
                        <span class="date-sep">a</span>
                        <input v-model="filterDateTo" type="date" class="filter-date-input" title="Fecha término" />
                    </div>
                </div>
            </div>

            <!-- Contenedor con Scroll & Timeline de Movimientos -->
            <div class="history-scroll-container">
                <div v-if="paginatedHistory.length > 0" class="history-timeline">
                    <div 
                        v-for="mov in paginatedHistory" 
                        :key="mov.id" 
                        class="timeline-entry"
                    >
                        <div class="timeline-left-node">
                            <div class="timeline-node-circle" :class="`action-${mov.accion}`">
                                <PackageCheck v-if="mov.accion === 'entregado'" :size="16" />
                                <DollarSign v-else-if="mov.accion === 'pago'" :size="16" />
                                <XCircle v-else-if="mov.accion === 'cancelar'" :size="16" />
                                <Check v-else-if="mov.accion === 'crear'" :size="15" />
                                <Pencil v-else-if="mov.accion === 'editar'" :size="15" />
                                <Trash2 v-else-if="mov.accion === 'eliminar'" :size="15" />
                                <BadgePercent v-else-if="mov.accion === 'oferta'" :size="15" />
                                <Clock v-else-if="mov.accion === 'estado'" :size="15" />
                                <Unlock v-else-if="mov.accion === 'apertura'" :size="15" />
                                <Lock v-else-if="mov.accion === 'cierre'" :size="15" />
                                <Sparkles v-else :size="15" />
                            </div>
                            <div class="timeline-line-connector"></div>
                        </div>

                        <div class="timeline-box" :class="`box-${mov.accion}`">
                            <div class="timeline-box-header">
                                <div class="timeline-header-title-group">
                                    <span class="timeline-module-pill" :class="`module-${mov.tipo}`">
                                        {{ formatTypeName(mov.tipo) }}
                                    </span>
                                    <span class="timeline-type-pill" :class="`pill-${mov.accion}`">
                                        {{ formatActionName(mov.accion) }}
                                    </span>
                                    <h4 class="timeline-heading">
                                        {{ mov.descripcion }}: 
                                        <span class="highlight">{{ mov.entidad }}</span>
                                    </h4>
                                </div>
                                
                                <div class="timeline-header-right">
                                    <span v-if="mov.monto && Number(mov.monto) > 0" class="timeline-amount-pill">
                                        ${{ formatPrice(mov.monto) }}
                                    </span>
                                    <span class="timeline-rel-time">
                                        <Clock :size="13" /> {{ formatRelativeTime(mov.fecha) }}
                                    </span>
                                </div>
                            </div>

                            <p v-if="mov.detalle" class="timeline-box-desc">
                                {{ mov.detalle }}
                            </p>

                            <div class="timeline-box-footer">
                                <div class="timeline-user-badge">
                                    <User :size="13" />
                                    <span>{{ mov.usuario }}</span>
                                </div>
                                <span class="timeline-exact-date">
                                    🕒 {{ formatExactDate(mov.fecha) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <History :size="48" />
                    <h3>Sin registros de auditoría</h3>
                    <p>No hay registros de actividad para los filtros o el rango de fechas seleccionado.</p>
                    <button v-if="search || filterType || filterAction || filterDateRange !== '7days'" class="btn-reset-filters" @click="resetFilters">
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- CONTROLES DE PAGINACIÓN -->
            <div v-if="totalPages > 1" class="history-pagination-bar">
                <div class="pagination-info">
                    Mostrando <strong>{{ (currentPage - 1) * pageSize + 1 }}</strong> - <strong>{{ Math.min(currentPage * pageSize, filteredHistory.length) }}</strong> de <strong>{{ filteredHistory.length }}</strong> movimientos
                </div>
                <div class="pagination-controls">
                    <button 
                        type="button" 
                        class="pagination-btn" 
                        :disabled="currentPage === 1" 
                        @click="currentPage--"
                    >
                        <ChevronLeft :size="16" />
                        <span>Anterior</span>
                    </button>
                    <span class="pagination-page-label">Página {{ currentPage }} de {{ totalPages }}</span>
                    <button 
                        type="button" 
                        class="pagination-btn" 
                        :disabled="currentPage >= totalPages" 
                        @click="currentPage++"
                    >
                        <span>Siguiente</span>
                        <ChevronRight :size="16" />
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import catalogHistoryService, { type CatalogMovement } from '@/services/catalogHistoryService'
import { useNotification } from '@/composables/useNotification'
import { 
    History, Search, X, Check, Pencil, Trash2, BadgePercent, Sparkles, 
    Clock, RefreshCw, PackageCheck, DollarSign, XCircle, 
    Unlock, Lock, User, ChevronLeft, ChevronRight, Calendar
} from 'lucide-vue-next'

const route = useRoute()
const { notify } = useNotification()

const historyList = ref<CatalogMovement[]>([])
const search = ref('')
const filterType = ref('')
const filterAction = ref('')
const filterDateRange = ref<'7days' | 'today' | '30days' | 'custom' | 'all'>('7days')
const filterDateFrom = ref('')
const filterDateTo = ref('')
const currentPage = ref(1)
const pageSize = ref(15)
const isLoading = ref(false)

const loadHistory = async () => {
    isLoading.value = true
    try {
        historyList.value = await catalogHistoryService.fetchMovementsFromBackend()
    } catch (e) {
        console.error('Error cargando historial de movimientos:', e)
    } finally {
        isLoading.value = false
    }
}

const orderDeliveriesCount = computed(() => {
    return historyList.value.filter(m => m.accion === 'entregado').length
})

const paymentsCount = computed(() => {
    return historyList.value.filter(m => m.accion === 'pago').length
})

const editsCount = computed(() => {
    return historyList.value.filter(m => m.accion === 'editar' || m.accion === 'estado').length
})

const formatPrice = (price: number | string) => {
    const num = Number(price || 0)
    return num.toLocaleString('es-CL')
}

const formatTypeName = (tipo: string) => {
    switch (tipo) {
        case 'pedido': return 'PEDIDO'
        case 'producto': return 'PRODUCTO'
        case 'categoria': return 'CATEGORÍA'
        case 'tamaño': return 'TAMAÑO'
        case 'oferta': return 'OFERTA'
        case 'stock': return 'INVENTARIO'
        case 'caja': return 'CAJA'
        case 'trabajador': return 'TRABAJADOR'
        default: return (tipo || 'GENERAL').toUpperCase()
    }
}

const formatActionName = (action: string) => {
    switch (action) {
        case 'crear': return 'CREACIÓN'
        case 'entregado': return 'ENTREGADO'
        case 'pago': return 'PAGO CONFIRMADO'
        case 'editar': return 'MODIFICACIÓN'
        case 'eliminar': return 'ELIMINACIÓN'
        case 'oferta': return 'OFERTA'
        case 'estado': return 'ESTADO'
        case 'cancelar': return 'CANCELADO'
        case 'apertura': return 'APERTURA'
        case 'cierre': return 'CIERRE'
        default: return (action || 'ACCIÓN').toUpperCase()
    }
}

const formatRelativeTime = (dateStr: string) => {
    try {
        const diffMs = Date.now() - new Date(dateStr).getTime()
        const diffSecs = Math.floor(diffMs / 1000)
        if (diffSecs < 60) return 'Hace un momento'
        const diffMins = Math.floor(diffSecs / 60)
        if (diffMins < 60) return `Hace ${diffMins} min`
        const diffHours = Math.floor(diffMins / 60)
        if (diffHours < 24) return `Hace ${diffHours} h`
        const diffDays = Math.floor(diffHours / 24)
        return `Hace ${diffDays} d`
    } catch {
        return 'Reciente'
    }
}

const formatExactDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr)
        if (isNaN(d.getTime())) return dateStr
        const fecha = d.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' })
        const hora = d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' })
        return `${fecha} a las ${hora} hrs`
    } catch {
        return dateStr
    }
}

const isDateInRange = (dateStr: string) => {
    if (!dateStr) return true
    const itemDate = new Date(dateStr)
    if (isNaN(itemDate.getTime())) return true

    const now = new Date()

    if (filterDateRange.value === 'today') {
        const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate())
        return itemDate >= startOfDay
    }

    if (filterDateRange.value === '7days') {
        const sevenDaysAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
        return itemDate >= sevenDaysAgo
    }

    if (filterDateRange.value === '30days') {
        const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
        return itemDate >= thirtyDaysAgo
    }

    if (filterDateRange.value === 'custom') {
        if (filterDateFrom.value) {
            const from = new Date(filterDateFrom.value + 'T00:00:00')
            if (itemDate < from) return false
        }
        if (filterDateTo.value) {
            const to = new Date(filterDateTo.value + 'T23:59:59')
            if (itemDate > to) return false
        }
        return true
    }

    return true // 'all'
}

const resetFilters = () => {
    search.value = ''
    filterType.value = ''
    filterAction.value = ''
    filterDateRange.value = '7days'
    filterDateFrom.value = ''
    filterDateTo.value = ''
    currentPage.value = 1
}

const filteredHistory = computed(() => {
    let list = historyList.value
    if (filterType.value) {
        list = list.filter((m: CatalogMovement) => m.tipo === filterType.value)
    }
    if (filterAction.value) {
        list = list.filter((m: CatalogMovement) => m.accion === filterAction.value)
    }
    if (filterDateRange.value !== 'all') {
        list = list.filter((m: CatalogMovement) => isDateInRange(m.fecha))
    }
    if (search.value) {
        const q = search.value.toLowerCase().trim()
        list = list.filter((m: CatalogMovement) =>
            (m.entidad || '').toLowerCase().includes(q) ||
            (m.descripcion || '').toLowerCase().includes(q) ||
            (m.detalle || '').toLowerCase().includes(q) ||
            (m.usuario || '').toLowerCase().includes(q)
        )
    }
    return list
})

const totalPages = computed(() => {
    return Math.max(1, Math.ceil(filteredHistory.value.length / pageSize.value))
})

const paginatedHistory = computed(() => {
    const start = (currentPage.value - 1) * pageSize.value
    return filteredHistory.value.slice(start, start + pageSize.value)
})

watch([search, filterType, filterAction, filterDateRange, filterDateFrom, filterDateTo], () => {
    currentPage.value = 1
})

onMounted(() => {
    if (route.query.tipo) {
        filterType.value = String(route.query.tipo)
    }
    loadHistory()
    window.addEventListener('foodtruck-catalog-movement', () => {
        loadHistory()
    })
    window.addEventListener('foodtruck-cash-transaction-update', () => {
        loadHistory()
    })
})
</script>

<style scoped>
.history-page {
    padding: 30px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.title-with-badges {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.title-with-badges h1 {
    font-size: 1.85rem;
    font-weight: 900;
    color: var(--DC-brown, #513119);
    margin: 0;
}

.header-stat-pills {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    color: #555;
    border: 1px solid #e2ded8;
}

.page-header p {
    margin: 0;
    color: #78716c;
    font-size: 0.92rem;
    max-width: 650px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: white;
    font-weight: 800;
    color: var(--DC-brown, #513119);
    cursor: pointer;
    transition: 0.2s;
}

.btn-refresh:hover {
    background: #f8fafc;
    border-color: var(--DC-orange, #e28743);
}

.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.secondary-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: white;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
}

.secondary-button:hover {
    background: #f8fafc;
    color: #334155;
}

/* ===================== RESUMEN KPIS ===================== */
.audit-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px;
}

.summary-kpi-card {
    background: white;
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid rgba(0,0,0,0.03);
}

.kpi-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bg-summary-brown { background: rgba(81, 49, 25, 0.1); color: var(--DC-brown, #513119); }
.bg-summary-green { background: #dcfce7; color: #15803d; }
.bg-summary-orange { background: rgba(226, 135, 67, 0.14); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }

.kpi-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.kpi-label {
    font-size: 0.78rem;
    font-weight: 700;
    color: #78716c;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.kpi-val {
    font-size: 1.45rem;
    font-weight: 900;
    color: var(--DC-gray, #322c44);
    line-height: 1.1;
}

.val-green { color: #15803d; }
.val-orange { color: var(--DC-orange, #e28743); }
.val-pink { color: var(--DC-pink, #d80056); }

.kpi-hint {
    font-size: 0.74rem;
    color: #94a3b8;
}

/* ===================== CONTENEDOR & FILTROS ===================== */
.table-container {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(81, 49, 25, 0.04);
    border: 1px solid #eee7dd;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1.5px solid #e7ded3;
    border-radius: 12px;
    background: white;
    width: 100%;
    max-width: 450px;
    transition: all 0.2s ease;
}

.search-box:focus-within {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 0.9rem;
    color: var(--DC-gray, #322c44);
    font-weight: 600;
}

.clear-search-btn {
    background: transparent;
    border: none;
    color: #999;
    cursor: pointer;
}

.filters-inline {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.filter-select {
    padding: 10px 14px;
    border-radius: 10px;
    border: 1.5px solid #e7ded3;
    background: white;
    font-size: 0.88rem;
    color: var(--DC-gray, #322c44);
    font-weight: 700;
    outline: none;
    transition: all 0.2s ease;
}

.filter-select:focus {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.date-custom-group {
    display: flex;
    align-items: center;
    gap: 6px;
}

.filter-date-input {
    padding: 8px 10px;
    border-radius: 10px;
    border: 1.5px solid #e7ded3;
    background: white;
    font-size: 0.84rem;
    color: var(--DC-gray, #322c44);
    font-weight: 600;
    outline: none;
    transition: 0.2s;
}

.filter-date-input:focus {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.date-sep {
    font-size: 0.82rem;
    font-weight: 700;
    color: #94a3b8;
}

.history-scroll-container {
    max-height: 640px;
    overflow-y: auto;
    padding-right: 6px;
    scrollbar-width: thin;
    scrollbar-color: #e28743 #f1f5f9;
}

.history-scroll-container::-webkit-scrollbar {
    width: 6px;
}

.history-scroll-container::-webkit-scrollbar-track {
    background: #f8fafc;
    border-radius: 6px;
}

.history-scroll-container::-webkit-scrollbar-thumb {
    background: #e28743;
    border-radius: 6px;
}

/* ===================== PAGINACIÓN ===================== */
.history-pagination-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 4px 0 4px;
    border-top: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 12px;
}

.pagination-info {
    font-size: 0.85rem;
    color: #64748b;
}

.pagination-info strong {
    color: #1e293b;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: white;
    font-weight: 700;
    font-size: 0.84rem;
    color: var(--DC-brown, #513119);
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
    background: #fff4e6;
    border-color: var(--DC-orange, #e28743);
    color: var(--DC-orange, #e28743);
}

.pagination-btn:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.pagination-page-label {
    font-size: 0.84rem;
    font-weight: 700;
    color: #475569;
}

/* ===================== TIMELINE ===================== */
.history-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 8px 0;
}

.timeline-entry {
    display: flex;
    gap: 16px;
    position: relative;
}

.timeline-left-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 36px;
}

.timeline-node-circle {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.timeline-line-connector {
    width: 2px;
    flex: 1;
    background: #e7dfd5;
    margin-top: 4px;
}

.timeline-entry:last-child .timeline-line-connector {
    display: none;
}

/* Colores de Iconos por Acción */
.action-entregado { background: #dcfce7; color: #15803d; border: 2px solid #86efac; }
.action-pago { background: #ecfdf5; color: #047857; border: 2px solid #6ee7b7; }
.action-crear { background: #fff7ed; color: #c2410c; border: 2px solid #fdba74; }
.action-editar { background: rgba(81, 49, 25, 0.08); color: var(--DC-brown, #513119); border: 2px solid rgba(81, 49, 25, 0.25); }
.action-estado { background: #fefce8; color: #a16207; border: 2px solid #fef08a; }
.action-cancelar { background: #fdf2f8; color: var(--DC-pink, #d80056); border: 2px solid #fbcfe8; }
.action-eliminar { background: #fee2e2; color: #dc2626; border: 2px solid #fca5a5; }
.action-oferta { background: rgba(216, 0, 86, 0.08); color: var(--DC-pink, #d80056); border: 2px solid rgba(216, 0, 86, 0.25); }
.action-apertura { background: #f0fdf4; color: #15803d; border: 2px solid #bbf7d0; }
.action-cierre { background: #f8fafc; color: #475569; border: 2px solid #cbd5e1; }

.timeline-box {
    background: white;
    border: 1px solid #ede7dd;
    border-radius: 16px;
    padding: 16px 20px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(81, 49, 25, 0.03);
    transition: all 0.2s ease;
}

.timeline-box:hover {
    box-shadow: 0 6px 18px rgba(81, 49, 25, 0.08);
    border-color: #d6cbbe;
}

.box-entregado { border-left: 5px solid #16a34a; }
.box-pago { border-left: 5px solid #059669; }
.box-crear { border-left: 5px solid var(--DC-orange, #e28743); }
.box-editar { border-left: 5px solid var(--DC-brown, #513119); }
.box-estado { border-left: 5px solid #eab308; }
.box-cancelar { border-left: 5px solid var(--DC-pink, #d80056); }
.box-eliminar { border-left: 5px solid #ef4444; }
.box-oferta { border-left: 5px solid var(--DC-pink, #d80056); }
.box-apertura { border-left: 5px solid #10b981; }
.box-cierre { border-left: 5px solid #64748b; }

.timeline-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.timeline-header-title-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

/* Pastillas de Módulo (Tipo) */
.timeline-module-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 6px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.module-pedido { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
.module-producto { background: rgba(81, 49, 25, 0.08); color: var(--DC-brown, #513119); border: 1px solid rgba(81, 49, 25, 0.2); }
.module-categoria { background: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
.module-tamaño { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.module-oferta { background: rgba(216, 0, 86, 0.08); color: var(--DC-pink, #d80056); border: 1px solid rgba(216, 0, 86, 0.2); }
.module-stock { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
.module-caja { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.module-trabajador { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }

/* Pastillas de Acción */
.timeline-type-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 3px 9px;
    border-radius: 6px;
    letter-spacing: 0.03em;
    text-transform: uppercase;
}

.pill-entregado { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
.pill-pago { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
.pill-crear { background: #fff7ed; color: #c2410c; border: 1px solid #fdba74; }
.pill-editar { background: rgba(81, 49, 25, 0.07); color: var(--DC-brown, #513119); border: 1px solid rgba(81, 49, 25, 0.2); }
.pill-estado { background: #fefce8; color: #a16207; border: 1px solid #fef08a; }
.pill-cancelar { background: #fdf2f8; color: var(--DC-pink, #d80056); border: 1px solid #fbcfe8; }
.pill-eliminar { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }
.pill-oferta { background: rgba(216, 0, 86, 0.08); color: var(--DC-pink, #d80056); border: 1px solid rgba(216, 0, 86, 0.2); }
.pill-apertura { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.pill-cierre { background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; }

.timeline-heading {
    margin: 0;
    font-size: 0.95rem;
    color: var(--DC-gray, #322c44);
    font-weight: 600;
}

.timeline-heading .highlight {
    color: var(--DC-brown, #513119);
    font-weight: 900;
}

.timeline-header-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.timeline-amount-pill {
    background: #dcfce7;
    color: #15803d;
    font-weight: 900;
    font-size: 0.85rem;
    padding: 3px 10px;
    border-radius: 999px;
    border: 1px solid #bbf7d0;
}

.timeline-rel-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #8c827a;
    font-weight: 600;
}

.timeline-box-desc {
    margin: 0;
    font-size: 0.86rem;
    color: #4a4239;
    background: #faf8f5;
    padding: 10px 14px;
    border-radius: 10px;
    border: 1px solid #f0eae1;
    line-height: 1.5;
}

.timeline-box-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #eee7dd;
    padding-top: 10px;
    margin-top: 2px;
    flex-wrap: wrap;
    gap: 8px;
}

.timeline-user-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.78rem;
    font-weight: 700;
    color: var(--DC-brown, #513119);
    background: rgba(81, 49, 25, 0.08);
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid rgba(81, 49, 25, 0.15);
}

.timeline-exact-date {
    font-size: 0.78rem;
    color: #5c5247;
    font-weight: 600;
    background: #f5ebe0;
    padding: 4px 10px;
    border-radius: 8px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px;
    text-align: center;
    color: #94a3b8;
    gap: 8px;
}

.btn-reset-filters {
    margin-top: 8px;
    padding: 9px 18px;
    background: var(--DC-orange, #e28743);
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 800;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-reset-filters:hover {
    background: #cb6f2b;
}

@media (max-width: 640px) {
    .history-page {
        padding: 16px 12px;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    .header-actions {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .btn-refresh,
    .secondary-button {
        width: 100%;
        justify-content: center;
    }
    .audit-summary-grid {
        grid-template-columns: 1fr;
    }
    .table-container {
        padding: 16px 12px;
    }
    .table-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .search-box {
        max-width: 100%;
        width: 100%;
    }
    .filters-inline {
        width: 100%;
        flex-direction: column;
    }
    .filter-item,
    .filter-select {
        width: 100%;
    }
    .timeline-box-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
    }
    .timeline-header-right {
        width: 100%;
        justify-content: space-between;
    }
    .date-custom-group {
        width: 100%;
        justify-content: space-between;
    }
    .filter-date-input {
        flex: 1;
        width: 100%;
    }
    .history-pagination-bar {
        flex-direction: column;
        align-items: center;
        gap: 10px;
        text-align: center;
    }
    .pagination-controls {
        width: 100%;
        justify-content: space-between;
    }
}
</style>

