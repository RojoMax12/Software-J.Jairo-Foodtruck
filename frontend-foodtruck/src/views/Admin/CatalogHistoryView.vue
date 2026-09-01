<template>
    <div class="history-page">
        <!-- ===================== HEADER ===================== -->
        <section class="page-header">
            <div class="header-left">
                <div class="title-with-badges">
                    <h1>Historial de Movimientos y Auditoría</h1>
                    <div class="header-stat-pills">
                        <span class="stat-pill"><strong>{{ historyList.length }}</strong> registros de auditoría</span>
                    </div>
                </div>
                <p>
                    Auditoría en tiempo real de todos los movimientos: creación de productos, cambios de precios, ofertas, modificaciones de categorías y tamaños.
                </p>
            </div>

            <div class="header-actions">
                <button class="btn-refresh" @click="loadHistory" :disabled="isLoading" title="Actualizar desde base de datos">
                    <RefreshCw :size="16" :class="{ spinning: isLoading }" />
                    <span>{{ isLoading ? 'Cargando...' : 'Actualizar' }}</span>
                </button>
                <button class="secondary-button" @click="clearHistory" title="Vaciar todo el registro en base de datos">
                    <RotateCcw :size="16" />
                    <span>Vaciar Auditoría</span>
                </button>
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
                        placeholder="Buscar por producto, categoría, usuario, acción..."
                    >
                    <button v-if="search" class="clear-search-btn" @click="search = ''">
                        <X :size="14" />
                    </button>
                </div>

                <div class="filters-inline">
                    <div class="filter-item">
                        <select v-model="filterType" class="filter-select">
                            <option value="">Todos los tipos</option>
                            <option value="producto">Solo Productos</option>
                            <option value="categoria">Solo Categorías</option>
                            <option value="tamaño">Solo Tamaños</option>
                            <option value="oferta">Solo Ofertas</option>
                            <option value="stock">Solo Stock / Estado</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Timeline de Movimientos -->
            <div v-if="filteredHistory.length > 0" class="history-timeline">
                <div 
                    v-for="mov in filteredHistory" 
                    :key="mov.id" 
                    class="timeline-entry"
                >
                    <div class="timeline-left-node">
                        <div class="timeline-node-circle" :class="`action-${mov.accion}`">
                            <Check v-if="mov.accion === 'crear'" :size="15" />
                            <Pencil v-else-if="mov.accion === 'editar'" :size="15" />
                            <Trash2 v-else-if="mov.accion === 'eliminar'" :size="15" />
                            <BadgePercent v-else-if="mov.accion === 'oferta'" :size="15" />
                            <Sparkles v-else :size="15" />
                        </div>
                        <div class="timeline-line-connector"></div>
                    </div>

                    <div class="timeline-box">
                        <div class="timeline-box-header">
                            <div class="timeline-header-title-group">
                                <span class="timeline-type-pill" :class="`pill-${mov.accion}`">
                                    {{ formatActionName(mov.accion) }} · {{ mov.tipo.toUpperCase() }}
                                </span>
                                <h4 class="timeline-heading">{{ mov.descripcion }}: <span class="highlight">{{ mov.entidad }}</span></h4>
                            </div>
                            <span class="timeline-rel-time"><Clock :size="13" /> {{ formatRelativeTime(mov.fecha) }}</span>
                        </div>

                        <p v-if="mov.detalle" class="timeline-box-desc">{{ mov.detalle }}</p>

                        <div class="timeline-box-footer">
                            <span class="timeline-user-badge">👤 {{ mov.usuario }}</span>
                            <span class="timeline-exact-date">{{ formatExactDate(mov.fecha) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <History :size="48" />
                <h3>Sin movimientos registrados</h3>
                <p>Cualquier cambio realizado en productos, categorías, formatos u ofertas quedará registrado aquí en tiempo real.</p>
            </div>
        </section>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import catalogHistoryService, { type CatalogMovement } from '@/services/catalogHistoryService'
import { useNotification } from '@/composables/useNotification'
import { History, Search, X, Check, Pencil, Trash2, BadgePercent, Sparkles, Clock, RotateCcw, RefreshCw } from 'lucide-vue-next'

const { notify } = useNotification()

const historyList = ref<CatalogMovement[]>([])
const search = ref('')
const filterType = ref('')
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

const formatActionName = (action: string) => {
    switch (action) {
        case 'crear': return 'CREACIÓN'
        case 'editar': return 'MODIFICACIÓN'
        case 'eliminar': return 'ELIMINACIÓN'
        case 'oferta': return 'OFERTA'
        case 'estado': return 'ESTADO'
        default: return 'ACCIÓN'
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
        return new Date(dateStr).toLocaleString('es-CL', {
            day: '2-digit', month: '2-digit', year: 'numeric',
            hour: '2-digit', minute: '2-digit'
        })
    } catch {
        return dateStr
    }
}

const filteredHistory = computed(() => {
    let list = historyList.value
    if (filterType.value) {
        list = list.filter((m: CatalogMovement) => m.tipo === filterType.value)
    }
    if (search.value) {
        const q = search.value.toLowerCase()
        list = list.filter((m: CatalogMovement) =>
            (m.entidad || '').toLowerCase().includes(q) ||
            (m.descripcion || '').toLowerCase().includes(q) ||
            (m.detalle || '').toLowerCase().includes(q) ||
            (m.usuario || '').toLowerCase().includes(q)
        )
    }
    return list
})

const clearHistory = async () => {
    if (!confirm('¿Deseas vaciar el historial de auditoría de catálogo en la base de datos?')) return
    await catalogHistoryService.clearHistory()
    historyList.value = []
    notify('Historial de movimientos vaciado en la base de datos', 'warning')
}

onMounted(() => {
    loadHistory()
    window.addEventListener('foodtruck-catalog-movement', () => {
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
    background: #f8f6f2;
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
    max-width: 600px;
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

.table-container {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,.05);
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
    border: 1px solid #dfe3ee;
    border-radius: 12px;
    background: #fdfdfd;
    width: 100%;
    max-width: 400px;
}

.search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 0.9rem;
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
}

.filter-select {
    padding: 9px 14px;
    border-radius: 10px;
    border: 1px solid #dfe3ee;
    background: white;
    font-size: 0.88rem;
    color: #475569;
    font-weight: 600;
    outline: none;
}

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
    width: 32px;
}

.timeline-node-circle {
    width: 32px;
    height: 32px;
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
    background: #e2e8f0;
    margin-top: 4px;
}

.timeline-entry:last-child .timeline-line-connector {
    display: none;
}

.action-crear {
    background: #dcfce7;
    color: #166534;
}

.action-editar {
    background: #dbeafe;
    color: #1e40af;
}

.action-eliminar {
    background: #fee2e2;
    color: #991b1b;
}

.action-oferta {
    background: #ffedd5;
    color: #9a3412;
}

.action-estado {
    background: #f3e8ff;
    color: #6b21a8;
}

.timeline-box {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 14px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

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
    gap: 10px;
    flex-wrap: wrap;
}

.timeline-type-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.04em;
}

.pill-crear { background: #dcfce7; color: #166534; }
.pill-editar { background: #dbeafe; color: #1e40af; }
.pill-eliminar { background: #fee2e2; color: #991b1b; }
.pill-oferta { background: #ffedd5; color: #9a3412; }
.pill-estado { background: #f3e8ff; color: #6b21a8; }

.timeline-heading {
    margin: 0;
    font-size: 0.94rem;
    color: #334155;
    font-weight: 600;
}

.timeline-heading .highlight {
    color: #0f172a;
    font-weight: 800;
}

.timeline-rel-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #94a3b8;
    font-weight: 600;
}

.timeline-box-desc {
    margin: 0;
    font-size: 0.82rem;
    color: #64748b;
    background: #f8fafc;
    padding: 6px 10px;
    border-radius: 8px;
}

.timeline-box-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #f1f5f9;
    padding-top: 6px;
    margin-top: 2px;
}

.timeline-user-badge {
    font-size: 0.76rem;
    font-weight: 700;
    color: #475569;
}

.timeline-exact-date {
    font-size: 0.74rem;
    color: #94a3b8;
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
</style>

