<template>
  <div class="history-page">
    <!-- ===================== HEADER ===================== -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Auditoría del Sistema & Actividad</h1>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="loadHistory" :disabled="isLoading" title="Actualizar auditoría">
          <RefreshCw :size="16" :class="{ spinning: isLoading }" />
          <span>{{ isLoading ? 'Actualizando...' : 'Actualizar' }}</span>
        </button>
      </div>
    </header>

    <!-- ===================== RESUMEN EN KPIS (SUMMARY-GRID) ===================== -->
    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <History :size="22" />
        </div>
        <div>
          <span class="summary-label">Total Registros</span>
          <strong class="summary-value">{{ historyList.length }}</strong>
          <p class="summary-helper">Auditoría global en base de datos</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <PackageCheck :size="22" />
        </div>
        <div>
          <span class="summary-label">Pedidos Entregados</span>
          <strong class="summary-value text-status-open">{{ orderDeliveriesCount }}</strong>
          <p class="summary-helper">Despachados al cliente</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-orange">
          <DollarSign :size="22" />
        </div>
        <div>
          <span class="summary-label">Pagos Confirmados</span>
          <strong class="summary-value text-orange">{{ paymentsCount }}</strong>
          <p class="summary-helper">Transacciones de venta cerradas</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <Pencil :size="22" />
        </div>
        <div>
          <span class="summary-label">Modificaciones</span>
          <strong class="summary-value text-pink">{{ editsCount }}</strong>
          <p class="summary-helper">Ajustes en pedidos, stock o menú</p>
        </div>
      </article>
    </section>

    <!-- ===================== PANEL UNIFICADO DE AUDITORÍA ===================== -->
    <section class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por comanda, cliente, producto, trabajador..."
            />
            <button v-if="search" class="clear-search-btn" @click="search = ''">
              <X :size="14" />
            </button>
          </div>

          <div class="select-box">
            <select v-model="filterType">
              <option value="">Todos los módulos</option>
              <option value="pedido">Pedidos & Despacho</option>
              <option value="producto">Productos & Menú</option>
              <option value="categoria">Categorías</option>
              <option value="tamaño">Tamaños y Formatos</option>
              <option value="oferta">Ofertas Promocionales</option>
              <option value="stock">Stock & Inventario</option>
              <option value="caja">Caja & Turnos</option>
              <option value="trabajador">Trabajadores & Personal</option>
            </select>
          </div>

          <div class="select-box">
            <select v-model="filterAction">
              <option value="">Todas las acciones</option>
              <option value="crear">Creación</option>
              <option value="entregado">Entregas</option>
              <option value="pago">Pagos</option>
              <option value="estado">⏱Cambio de Estado</option>
              <option value="editar">Modificación</option>
              <option value="cancelar">Cancelación</option>
              <option value="eliminar">Eliminación</option>
              <option value="apertura">Apertura de Caja</option>
              <option value="cierre">Cierre de Turno</option>
            </select>
          </div>

          <div class="select-box">
            <select v-model="filterDateRange">
              <option value="7days">Últimos 7 días</option>
              <option value="today">Solo Hoy</option>
              <option value="30days">Últimos 30 días</option>
              <option value="custom">Rango de Fechas</option>
              <option value="all">Todo el Historial</option>
            </select>
          </div>

          <div v-if="filterDateRange === 'custom'" class="date-custom-group">
            <input v-model="filterDateFrom" type="date" class="filter-date-input" title="Fecha inicio" />
            <span class="date-sep">a</span>
            <input v-model="filterDateTo" type="date" class="filter-date-input" title="Fecha término" />
          </div>

          <button
            v-if="hasActiveFilters"
            class="btn-reset-filters"
            type="button"
            @click="resetFilters"
            title="Limpiar filtros"
          >
            <X :size="14" />
            <span>Limpiar</span>
          </button>
        </div>

        <div class="toolbar-right">
          <span class="results-chip">{{ filteredHistory.length }} eventos</span>
        </div>
      </div>

      <!-- CONTENEDOR CON SCROLL & TIMELINE -->
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
          <History :size="42" />
          <h3>Sin registros de auditoría</h3>
          <p>No hay eventos que coincidan con los filtros o el período seleccionado.</p>
          <button v-if="hasActiveFilters" class="btn-retry" @click="resetFilters">
            Restablecer filtros
          </button>
        </div>
      </div>

      <!-- CONTROLES DE PAGINACIÓN -->
      <div v-if="totalPages > 1 || filteredHistory.length > 0" class="inventory-pagination">
        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="currentPage === 1" 
          @click="currentPage--"
        >
          <ChevronLeft :size="16" />
          <span>Anterior</span>
        </button>
        
        <div class="pagination-info">
          Mostrando <strong>{{ (currentPage - 1) * pageSize + 1 }}</strong> - <strong>{{ Math.min(currentPage * pageSize, filteredHistory.length) }}</strong> de <strong>{{ filteredHistory.length }}</strong> registros
        </div>

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
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import catalogHistoryService, { type CatalogMovement } from '@/services/catalogHistoryService'
import { 
  History, Search, X, Check, Pencil, Trash2, BadgePercent, Sparkles, 
  Clock, RefreshCw, PackageCheck, DollarSign, XCircle, 
  Unlock, Lock, User, ChevronLeft, ChevronRight 
} from 'lucide-vue-next'

const route = useRoute()

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
    const startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0)
    const endOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 23, 59, 59, 999)
    return itemDate >= startOfDay && itemDate <= endOfDay
  }

  if (filterDateRange.value === '7days') {
    const sevenDaysAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 7, 0, 0, 0, 0)
    return itemDate >= sevenDaysAgo
  }

  if (filterDateRange.value === '30days') {
    const thirtyDaysAgo = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 30, 0, 0, 0, 0)
    return itemDate >= thirtyDaysAgo
  }

  if (filterDateRange.value === 'custom') {
    if (filterDateFrom.value) {
      const from = new Date(`${filterDateFrom.value}T00:00:00`)
      if (itemDate < from) return false
    }
    if (filterDateTo.value) {
      const to = new Date(`${filterDateTo.value}T23:59:59`)
      if (itemDate > to) return false
    }
    return true
  }

  return true
}

const hasActiveFilters = computed(() => {
  return (
    Boolean(search.value.trim()) ||
    Boolean(filterType.value) ||
    Boolean(filterAction.value) ||
    filterDateRange.value !== '7days' ||
    Boolean(filterDateFrom.value) ||
    Boolean(filterDateTo.value)
  )
})

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
  window.addEventListener('foodtruck-catalog-movement', loadHistory)
  window.addEventListener('foodtruck-cash-transaction-update', loadHistory)
})
</script>

<style scoped>
.history-page {
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER SUPERIOR
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

.btn-secondary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* ====================================================
   RESUMEN KPIS (SUMMARY-GRID)
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
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
  font-size: 1.5rem;
  line-height: 1.1;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
  margin: 0;
}

.text-status-open { color: #15803d; }
.text-orange { color: var(--DC-orange, #e28743); }
.text-pink { color: var(--DC-pink, #d80056); }

/* ====================================================
   PANEL UNIFICADO & TOOLBAR
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

.select-box select {
  padding: 0.55rem 0.85rem;
  border-radius: 12px;
  border: 1px solid rgba(81, 49, 25, 0.09);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.86rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
  cursor: pointer;
  transition: border-color 0.2s;
}

.select-box select:focus {
  border-color: var(--DC-orange, #e28743);
}

.date-custom-group {
  display: flex;
  align-items: center;
  gap: 5px;
}

.filter-date-input {
  padding: 0.45rem 0.65rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
}

.filter-date-input:focus {
  border-color: var(--DC-orange, #e28743);
}

.date-sep {
  font-size: 0.76rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
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
   CONTENEDOR DE TIMELINE
==================================================== */
.history-scroll-container {
  max-height: 580px;
  overflow-y: auto;
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
}

.history-timeline {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.timeline-entry {
  display: flex;
  gap: 14px;
  position: relative;
}

.timeline-left-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  width: 34px;
}

.timeline-node-circle {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.timeline-line-connector {
  width: 2px;
  flex: 1;
  background: #ebe4dc;
  margin-top: 4px;
}

.timeline-entry:last-child .timeline-line-connector {
  display: none;
}

/* Acciones */
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
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 14px;
  padding: 1rem 1.15rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.02);
  transition: all 0.2s ease;
}

.timeline-box:hover {
  border-color: rgba(226, 135, 67, 0.35);
  transform: translateY(-1px);
}

.box-entregado { border-left: 4px solid #16a34a; }
.box-pago { border-left: 4px solid #059669; }
.box-crear { border-left: 4px solid var(--DC-orange, #e28743); }
.box-editar { border-left: 4px solid var(--DC-brown, #513119); }
.box-estado { border-left: 4px solid #eab308; }
.box-cancelar { border-left: 4px solid var(--DC-pink, #d80056); }
.box-eliminar { border-left: 4px solid #ef4444; }
.box-oferta { border-left: 4px solid var(--DC-pink, #d80056); }
.box-apertura { border-left: 4px solid #10b981; }
.box-cierre { border-left: 4px solid #64748b; }

.timeline-box-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.timeline-header-title-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.timeline-module-pill {
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.module-pedido { background: #ffedd5; color: #9a3412; }
.module-producto { background: rgba(81, 49, 25, 0.08); color: var(--DC-brown, #513119); }
.module-categoria { background: #f1f5f9; color: #334155; }
.module-tamaño { background: #fef3c7; color: #92400e; }
.module-oferta { background: rgba(216, 0, 86, 0.08); color: var(--DC-pink, #d80056); }
.module-stock { background: #eff6ff; color: #1d4ed8; }
.module-caja { background: #ecfdf5; color: #047857; }
.module-trabajador { background: #e0e7ff; color: #3730a3; }

.timeline-type-pill {
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.pill-entregado { background: #dcfce7; color: #15803d; }
.pill-pago { background: #ecfdf5; color: #047857; }
.pill-crear { background: #fff7ed; color: #c2410c; }
.pill-editar { background: rgba(81, 49, 25, 0.07); color: var(--DC-brown, #513119); }
.pill-estado { background: #fefce8; color: #a16207; }
.pill-cancelar { background: #fdf2f8; color: var(--DC-pink, #d80056); }
.pill-eliminar { background: #fee2e2; color: #dc2626; }
.pill-oferta { background: rgba(216, 0, 86, 0.08); color: var(--DC-pink, #d80056); }
.pill-apertura { background: #f0fdf4; color: #166534; }
.pill-cierre { background: #f8fafc; color: #334155; }

.timeline-heading {
  margin: 0;
  font-size: 0.88rem;
  color: var(--DC-gray, #2c2724);
  font-weight: 600;
}

.timeline-heading .highlight {
  color: var(--DC-brown, #513119);
  font-weight: 800;
}

.timeline-header-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

.timeline-amount-pill {
  background: #dcfce7;
  color: #15803d;
  font-weight: 800;
  font-size: 0.78rem;
  padding: 2px 8px;
  border-radius: 999px;
}

.timeline-rel-time {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 600;
}

.timeline-box-desc {
  margin: 0;
  font-size: 0.82rem;
  color: var(--DC-gray, #2c2724);
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 8px 12px;
  border-radius: 8px;
  line-height: 1.45;
}

.timeline-box-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
  padding-top: 8px;
  margin-top: 2px;
}

.timeline-user-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.timeline-exact-date {
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
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

/* Empty State */
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

.empty-state h3 {
  margin: 0;
  color: var(--DC-brown, #513119);
  font-size: 1.1rem;
}

.empty-state p {
  margin: 0;
  font-size: 0.86rem;
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

/* ====================================================
   RESPONSIVO
==================================================== */
@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .history-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-actions,
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
  .select-box,
  .select-box select {
    width: 100%;
    min-width: 0;
  }

  .date-custom-group {
    width: 100%;
  }

  .filter-date-input {
    flex: 1;
    width: 100%;
  }

  .timeline-box-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .timeline-header-right {
    width: 100%;
    justify-content: space-between;
  }
}
</style>