<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Calendar, Search, RotateCcw, RotateCw, 
  Receipt, Clock, ShoppingBag, Eye, 
  CheckCircle2, AlertCircle, ChevronDown, 
  Sparkles, ArrowRight, Utensils
} from 'lucide-vue-next' 
import orderService from '@/services/orderService'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

// --- ESTADOS REACTIVOS ---
const ordersList = ref<any[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

// Filtros simplificados y limpios
const selectedPreset = ref<'all' | 'today' | '7days' | 'this_month' | 'custom'>('all')
const showCustomDates = ref(false)
const fechaInicio = ref('')
const fechaFin = ref('')
const searchQuery = ref('')
const selectedStatusFilter = ref<string>('all')

const fallbackUserCompany = ref('Cliente')

// Carga optimizada de pedidos
const fetchOrders = async () => {
  try {
    isLoading.value = true
    errorMessage.value = ''

    const params: { fecha_inicio?: string; fecha_fin?: string; limit: number } = {
      limit: 50
    }

    if (fechaInicio.value) params.fecha_inicio = fechaInicio.value
    if (fechaFin.value) params.fecha_fin = fechaFin.value

    const userParsed = localStorage.getItem('user')
    let distributorId: number | null = null

    if (userParsed) {
      try {
        const userObj = JSON.parse(userParsed)
        distributorId = userObj.id_usuario ?? userObj.id ?? null
        fallbackUserCompany.value = userObj.nombre || userObj.nombre_empresa || 'Cliente'
      } catch (e) {
        console.error('Error parsing user session:', e)
      }
    }

    let response: any
    if (distributorId) {
      response = await orderService.getOrdersByDistributor(distributorId, params)
    } else {
      response = await orderService.getMyOrders(params)
    }

    const data = response?.data?.data || response?.data || []
    ordersList.value = Array.isArray(data) ? data : []

  } catch (error: any) {
    console.error('Error fetching client orders:', error)
    errorMessage.value = 'No pudimos cargar tus pedidos. Por favor, intenta de nuevo.'
  } finally {
    isLoading.value = false
  }
}

// Aplicar preset rápido
const applyPreset = (preset: 'all' | 'today' | '7days' | 'this_month' | 'custom') => {
  selectedPreset.value = preset
  const today = new Date()
  const formatDateForInput = (d: Date): string => d.toISOString().slice(0, 10)

  if (preset === 'all') {
    showCustomDates.value = false
    fechaInicio.value = ''
    fechaFin.value = ''
  } else if (preset === 'today') {
    showCustomDates.value = false
    const todayStr = formatDateForInput(today)
    fechaInicio.value = todayStr
    fechaFin.value = todayStr
  } else if (preset === '7days') {
    showCustomDates.value = false
    const past7 = new Date()
    past7.setDate(today.getDate() - 7)
    fechaInicio.value = formatDateForInput(past7)
    fechaFin.value = formatDateForInput(today)
  } else if (preset === 'this_month') {
    showCustomDates.value = false
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
    fechaInicio.value = formatDateForInput(firstDay)
    fechaFin.value = formatDateForInput(today)
  } else if (preset === 'custom') {
    showCustomDates.value = !showCustomDates.value
    return
  }

  fetchOrders()
}

const handleCustomDateChange = () => {
  fetchOrders()
}

const resetFilters = () => {
  selectedPreset.value = 'all'
  showCustomDates.value = false
  fechaInicio.value = ''
  fechaFin.value = ''
  searchQuery.value = ''
  selectedStatusFilter.value = 'all'
  fetchOrders()
}

// Paginación y scroll progresivo
const visibleCount = ref(6)

// Filtrado reactivo en frontend
const filteredOrders = computed(() => {
  return ordersList.value.filter(order => {
    if (selectedStatusFilter.value !== 'all') {
      const orderStatusId = String(order.id_estado_pedido || order.estado_id || 1)
      if (orderStatusId !== selectedStatusFilter.value) return false
    }

    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase().trim()
      const idMatch = String(order.numero_pedido_dia || order.id_pedido || order.id || '').includes(q)
      const personaMatch = (order.nombre_persona || order.nombre_receptor || '').toLowerCase().includes(q)
      const notesMatch = (order.notas || '').toLowerCase().includes(q)
      const productsMatch = (order.detalles || []).some((det: any) => 
        (det.producto?.nombre || det.nombre_producto || '').toLowerCase().includes(q)
      )
      return idMatch || personaMatch || notesMatch || productsMatch
    }

    return true
  })
})

// Lista mostrada con límite progresivo para scroll óptimo
const displayedOrders = computed(() => {
  return filteredOrders.value.slice(0, visibleCount.value)
})

// Mapeo amigable con el estado REAL de la base de datos
const getOrderStatusInfo = (order: any) => {
  const realName = order?.estado_pedido?.nombre || order?.estado_nombre || order?.nombre_estado || ''
  const statusId = Number(order?.id_estado_pedido || order?.estado_id || 1)

  if (realName) {
    const lower = realName.toLowerCase()
    if (lower.includes('prep') || lower.includes('cocin')) {
      return { label: realName, class: 'status-cooking' }
    }
    if (lower.includes('list') || lower.includes('despach') || lower.includes('camin')) {
      return { label: realName, class: 'status-ready' }
    }
    if (lower.includes('entreg') || lower.includes('complet')) {
      return { label: realName, class: 'status-delivered' }
    }
    if (lower.includes('canc') || lower.includes('rechaz')) {
      return { label: realName, class: 'status-cancelled' }
    }
    return { label: realName, class: 'status-pending' }
  }

  // Fallback por ID si no viene el nombre
  switch (statusId) {
    case 1:
      return { label: 'Pendiente', class: 'status-pending' }
    case 2:
      return { label: 'En preparación', class: 'status-cooking' }
    case 3:
      return { label: 'Listo', class: 'status-ready' }
    case 4:
      return { label: 'Entregado', class: 'status-delivered' }
    case 5:
      return { label: 'Cancelado', class: 'status-cancelled' }
    default:
      return { label: 'Pendiente', class: 'status-pending' }
  }
}

// Formateo de fecha amigable (ej: "Hoy a las 21:30" o "30 Ago, 21:30")
const formatFriendlyDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  const today = new Date()
  const isToday = date.toDateString() === today.toDateString()

  const hours = date.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })

  if (isToday) {
    return `Hoy, ${hours}`
  }

  const day = date.getDate()
  const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic']
  const month = monthNames[date.getMonth()]
  return `${day} ${month}, ${hours}`
}

const formatCurrency = (value: any) => {
  const safeValue = (value !== undefined && value !== null) ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}

// 🔁 REPETIR PEDIDO EN 1 CLIC
const repeatOrder = (order: any) => {
  if (!order || !order.detalles || order.detalles.length === 0) {
    notify('Este pedido no tiene productos para repetir.', 'warning')
    return
  }

  const reorderedItems = order.detalles.map((det: any) => {
    const prod = det.producto || {}
    const rawExcluidos: string[] = []
    const rawAgregados: string[] = []
    const excluidosDetails: any[] = []
    const agregadosDetails: any[] = []

    if (Array.isArray(det.ingredientes)) {
      det.ingredientes.forEach((mod: any) => {
        const ingName = mod.ingrediente?.nombre || mod.nombre || ''
        const tipo = (mod.tipo_modificacion || mod.tipo || '').toLowerCase()
        if (tipo.includes('exclu') || tipo.includes('quit') || tipo.includes('sin')) {
          if (ingName) rawExcluidos.push(ingName)
          excluidosDetails.push({ id_ingrediente: mod.id_ingrediente, nombre: ingName })
        } else {
          if (ingName) rawAgregados.push(ingName)
          agregadosDetails.push({ id_ingrediente: mod.id_ingrediente, nombre: ingName })
        }
      })
    }

    const unitPrice = typeof det.precio_unitario === 'string'
      ? Number(det.precio_unitario.replace(/[^0-9.]/g, ''))
      : Number(det.precio_unitario || 0)

    const sizeName = det.tamano?.nombre_tamaño || det.tamaño?.nombre_tamaño || (det.id_tamaño === 1 ? 'Único' : '')

    return {
      id: det.id_producto || prod.id_producto,
      id_producto: det.id_producto || prod.id_producto,
      productId: det.id_producto || prod.id_producto,
      name: prod.nombre || 'Producto',
      fullName: `${prod.nombre || 'Producto'} ${sizeName}`.trim(),
      category: prod.categoria?.nombre || 'Foodtruck',
      price: unitPrice,
      quantity: Number(det.cantidad || 1),
      size: sizeName,
      id_tamaño: det.id_tamaño || 1,
      tamano_id: det.id_tamaño || 1,
      image: prod.imagen_url || prod.imagen || '',
      excluidos: rawExcluidos,
      excluidosDetails: excluidosDetails,
      agregados: rawAgregados,
      agregadosDetails: agregadosDetails
    }
  })

  localStorage.setItem('dicreme_temp_cart', JSON.stringify(reorderedItems))
  notify('¡Pedido cargado al carrito! Listo para confirmar.', 'success')
  router.push('/cotizacion')
}

onMounted(() => {
  fetchOrders()
})
</script>

<template>
  <div class="my-orders-view">
    <div class="orders-container">
      <!-- HEADER LIMPIO -->
      <div class="view-header">
        <div class="header-text">
          <h1 class="title">Mis Pedidos</h1>
          <p class="subtitle">Consulta tus compras anteriores o repite tu pedido favorito en 1 clic.</p>
        </div>
      </div>

      <!-- BARRA DE FILTROS AMIGABLE -->
      <div class="filters-bar">
        <!-- CHIPS DE ACCESO RÁPIDO -->
        <div class="chips-scroll">
          <button 
            type="button" 
            class="filter-chip" 
            :class="{ active: selectedPreset === 'all' && !showCustomDates }"
            @click="applyPreset('all')"
          >
            Todos
          </button>
          <button 
            type="button" 
            class="filter-chip" 
            :class="{ active: selectedPreset === 'today' && !showCustomDates }"
            @click="applyPreset('today')"
          >
            Hoy
          </button>
          <button 
            type="button" 
            class="filter-chip" 
            :class="{ active: selectedPreset === '7days' && !showCustomDates }"
            @click="applyPreset('7days')"
          >
            Últimos 7 días
          </button>
          <button 
            type="button" 
            class="filter-chip" 
            :class="{ active: selectedPreset === 'this_month' && !showCustomDates }"
            @click="applyPreset('this_month')"
          >
            Este mes
          </button>
          <button 
            type="button" 
            class="filter-chip chip-custom" 
            :class="{ active: showCustomDates }"
            @click="applyPreset('custom')"
          >
            <Calendar :size="14" />
            <span>Filtrar fechas</span>
            <ChevronDown :size="14" :class="{ 'rotate': showCustomDates }" />
          </button>
        </div>

        <!-- RANGO PERSONALIZADO DESPLEGABLE (SOLO SI SE ACTIVA) -->
        <Transition name="expand">
          <div v-if="showCustomDates" class="custom-date-drawer">
            <div class="date-input-group">
              <label>Desde:</label>
              <input type="date" v-model="fechaInicio" class="date-picker" @change="handleCustomDateChange" />
            </div>
            <div class="date-input-group">
              <label>Hasta:</label>
              <input type="date" v-model="fechaFin" class="date-picker" @change="handleCustomDateChange" />
            </div>
            <button type="button" class="btn-clear-date" @click="resetFilters">
              Limpiar
            </button>
          </div>
        </Transition>
      </div>

      <!-- ESTADO DE CARGA -->
      <div v-if="isLoading" class="loading-state">
        <div class="food-icon-pulse">
          <Utensils :size="32" />
        </div>
        <p>Cargando tus pedidos...</p>
      </div>

      <!-- ESTADO DE ERROR -->
      <div v-else-if="errorMessage" class="state-card error-card">
        <AlertCircle :size="32" class="state-icon error-icon" />
        <p class="state-msg">{{ errorMessage }}</p>
        <button class="btn-action-primary" @click="fetchOrders">Reintentar</button>
      </div>

      <!-- ESTADO VACÍO -->
      <div v-else-if="filteredOrders.length === 0" class="state-card empty-card">
        <div class="empty-icon-circle">
          <Receipt :size="40" />
        </div>
        <h3 class="empty-title">No hay pedidos en este periodo</h3>
        <p class="empty-desc">No encontramos compras registradas con los filtros seleccionados.</p>
        <button class="btn-action-primary" @click="resetFilters">Ver todos mis pedidos</button>
      </div>

      <!-- LISTA AMIGABLE DE PEDIDOS CON SCROLL Y CARGA PROGRESIVA -->
      <div v-else class="orders-list">
        <div 
          v-for="order in displayedOrders" 
          :key="order.id_pedido ?? order.id" 
          class="order-card"
        >
          <!-- CABECERA DE LA TARJETA -->
          <div class="card-header">
            <div class="order-main-info">
              <span class="order-chip-num" v-if="order.numero_pedido_dia">
                #{{ order.numero_pedido_dia }}
              </span>
              <span class="order-date-text">
                <Clock :size="13" class="clock-icon" />
                {{ formatFriendlyDate(order.created_at ?? order.fecha_creacion) }}
              </span>
            </div>

            <!-- BADGE DE ESTADO REAL -->
            <span class="status-pill" :class="getOrderStatusInfo(order).class">
              {{ getOrderStatusInfo(order).label }}
            </span>
          </div>

          <!-- CUERPO CON PRODUCTOS -->
          <div class="card-body">
            <div class="items-summary-list">
              <div 
                v-for="(item, idx) in order.detalles" 
                :key="idx" 
                class="item-summary-row"
              >
                <div class="item-name-group">
                  <span class="item-quantity">{{ item.cantidad }}x</span>
                  <span class="item-product-name">{{ item.producto?.nombre ?? item.nombre_producto ?? 'Producto' }}</span>
                  <span class="item-size-badge" v-if="item.tamano?.nombre_tamaño && item.tamano.nombre_tamaño !== 'Único'">
                    ({{ item.tamano.nombre_tamaño }})
                  </span>
                </div>

                <!-- MODIFICACIONES -->
                <div v-if="item.ingredientes && item.ingredientes.length > 0" class="mods-chips-row">
                  <span 
                    v-for="mod in item.ingredientes" 
                    :key="mod.id_ingrediente"
                    class="mini-mod-chip"
                    :class="(mod.tipo_modificacion || '').toLowerCase().includes('exclu') ? 'mod-sin' : 'mod-con'"
                  >
                    {{ (mod.tipo_modificacion || '').toLowerCase().includes('exclu') ? 'Sin' : '+' }} 
                    {{ mod.ingrediente?.nombre || mod.nombre }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- PIE DE TARJETA CON TOTAL Y BOTÓN DE REPETIR -->
          <div class="card-footer">
            <div class="footer-total">
              <span class="total-caption">Total:</span>
              <span class="total-price">{{ formatCurrency(order.total ?? order.monto_final ?? 0) }}</span>
            </div>

            <div class="footer-actions">
              <!-- BOTÓN REPETIR PEDIDO (DESTACADO Y RÁPIDO) -->
              <button 
                type="button" 
                class="btn-repeat-action" 
                @click="repeatOrder(order)"
                title="Hacer este mismo pedido ahora"
              >
                <RotateCw :size="15" />
                <span>Pedir de nuevo</span>
              </button>

              <!-- LINK VER SEGUIMIENTO -->
              <button 
                type="button" 
                class="btn-tracking-link" 
                @click="router.push(`/pedido/${order.id_pedido ?? order.id}`)"
                title="Ver estado en vivo"
              >
                <span>Detalle</span>
                <ArrowRight :size="14" />
              </button>
            </div>
          </div>
        </div>

        <!-- BOTÓN MOSTRAR MÁS PEDIDOS (SI LA LISTA CRECE) -->
        <div v-if="filteredOrders.length > visibleCount" class="load-more-container">
          <button type="button" class="btn-load-more" @click="visibleCount += 6">
            <span>Mostrar más pedidos (viendo {{ displayedOrders.length }} de {{ filteredOrders.length }})</span>
            <ChevronDown :size="16" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.my-orders-view {
  background-color: var(--DC-bg-gray, #f5ebe0);
  min-height: 100vh;
  padding: 24px 16px 80px 16px;
  font-family: var(--font-main, sans-serif);
  box-sizing: border-box;
}

.orders-container {
  max-width: 760px;
  margin: 0 auto;
}

/* HEADER */
.view-header {
  margin-bottom: 20px;
}

.title {
  font-size: 1.6rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
  margin: 0 0 4px 0;
  letter-spacing: -0.3px;
}

.subtitle {
  font-size: 0.88rem;
  color: var(--DC-text-gray, #6e6a75);
  margin: 0;
}

/* FILTROS RÁPIDOS (CHIPS) */
.filters-bar {
  margin-bottom: 24px;
}

.chips-scroll {
  display: flex;
  align-items: center;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 6px;
  scrollbar-width: none;
}

.chips-scroll::-webkit-scrollbar {
  display: none;
}

.filter-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 999px;
  background: #ffffff;
  border: 1.5px solid rgba(81, 49, 25, 0.15);
  color: var(--DC-brown, #513119);
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.filter-chip:hover {
  background: #fff8f3;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-brown, #513119);
}

.filter-chip.active {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 3px 10px rgba(226, 135, 67, 0.3);
}

.chip-custom {
  padding-right: 12px;
}

.chip-custom .rotate {
  transform: rotate(180deg);
}

/* CAJÓN DE FECHAS PERSONALIZADAS */
.custom-date-drawer {
  background: #ffffff;
  border-radius: 14px;
  padding: 14px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  box-shadow: 0 4px 12px rgba(81, 49, 25, 0.05);
}

.date-input-group {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  color: var(--DC-brown, #513119);
  font-weight: 700;
}

.date-picker {
  padding: 6px 10px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.2);
  font-size: 0.82rem;
  outline: none;
  font-family: inherit;
  color: var(--DC-brown, #513119);
}

.date-picker:focus {
  border-color: var(--DC-orange, #e28743);
}

.btn-clear-date {
  background: var(--button-color, #F4E1D2);
  border: none;
  padding: 6px 12px;
  border-radius: 8px;
  color: var(--button-text, #513119);
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-clear-date:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
}

/* ESTADOS */
.loading-state, .state-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 50px 20px;
  text-align: center;
  border: 1px solid rgba(81, 49, 25, 0.12);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin-top: 10px;
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.04);
}

.food-icon-pulse {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: softPulse 1.6s ease-in-out infinite;
  margin-bottom: 14px;
}

@keyframes softPulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(1.08); opacity: 0.75; }
}

.empty-icon-circle {
  width: 72px;
  height: 72px;
  border-radius: 50%;
  background: #fdfaf6;
  color: var(--DC-orange, #e28743);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 14px;
}

.empty-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0 0 6px 0;
}

.empty-desc {
  font-size: 0.86rem;
  color: var(--DC-text-gray, #6e6a75);
  margin: 0 0 18px 0;
  max-width: 360px;
}

.btn-action-primary {
  padding: 10px 20px;
  border-radius: 12px;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  font-weight: 800;
  font-size: 0.86rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-action-primary:hover {
  background: var(--DC-brown, #513119);
}

/* LISTA Y TARJETAS DE PEDIDOS */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.order-card {
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.04);
  overflow: hidden;
  transition: all 0.2s ease;
}

.order-card:hover {
  box-shadow: 0 8px 24px rgba(81, 49, 25, 0.08);
  border-color: rgba(226, 135, 67, 0.35);
}

.card-header {
  padding: 14px 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-bottom: 1px solid rgba(81, 49, 25, 0.06);
}

.order-main-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.order-chip-num {
  background: var(--DC-brown, #513119);
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
}

.order-date-text {
  font-size: 0.82rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.clock-icon {
  color: var(--DC-orange, #e28743);
}

/* STATUS PILLS */
.status-pill {
  font-size: 0.76rem;
  font-weight: 800;
  padding: 4px 12px;
  border-radius: 999px;
}

.status-cooking {
  background: #fff7ed;
  color: #c2410c;
  border: 1px solid #ffedd5;
}

.status-ready {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #dbeafe;
}

.status-delivered {
  background: #f0fdf4;
  color: #15803d;
  border: 1px solid #dcfce7;
}

.status-pending {
  background: #fffbeb;
  color: #b45309;
  border: 1px solid #fef3c7;
}

.status-cancelled {
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid #fee2e2;
}

/* BODY */
.card-body {
  padding: 16px 18px;
}

.items-summary-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 220px;
  overflow-y: auto;
  padding-right: 4px;
}

.items-summary-list::-webkit-scrollbar {
  width: 4px;
}

.items-summary-list::-webkit-scrollbar-thumb {
  background: rgba(226, 135, 67, 0.4);
  border-radius: 4px;
}

.item-summary-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.item-name-group {
  display: flex;
  align-items: baseline;
  gap: 6px;
  font-size: 0.94rem;
  color: var(--DC-gray, #322c44);
}

.item-quantity {
  font-weight: 800;
  color: var(--DC-orange, #e28743);
  font-size: 0.88rem;
}

.item-product-name {
  font-weight: 700;
}

.item-size-badge {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
}

.mods-chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 2px;
  padding-left: 20px;
}

.mini-mod-chip {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 1px 7px;
  border-radius: 6px;
}

.mod-sin {
  background: #fee2e2;
  color: #dc2626;
}

.mod-con {
  background: #dbeafe;
  color: #1d4ed8;
}

/* FOOTER */
.card-footer {
  padding: 14px 18px;
  background: #fcf9f5;
  border-top: 1px solid rgba(81, 49, 25, 0.06);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  flex-wrap: wrap;
}

.footer-total {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.total-caption {
  font-size: 0.8rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
}

.total-price {
  font-size: 1.2rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
}

.footer-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-repeat-action {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 16px;
  border-radius: 10px;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  font-size: 0.84rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(226, 135, 67, 0.25);
}

.btn-repeat-action:hover {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(81, 49, 25, 0.3);
}

.btn-tracking-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 8px 12px;
  border-radius: 10px;
  background: var(--button-color, #F4E1D2);
  border: 1px solid rgba(81, 49, 25, 0.15);
  color: var(--button-text, #513119);
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-tracking-link:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
}

/* TRANSITIONS */
.expand-enter-active,
.expand-leave-active {
  transition: all 0.25s ease-out;
  max-height: 100px;
  opacity: 1;
  overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
  max-height: 0;
  opacity: 0;
  padding-top: 0;
  padding-bottom: 0;
  margin-top: 0;
}

/* BOTÓN CARGAR MÁS */
.load-more-container {
  display: flex;
  justify-content: center;
  margin-top: 14px;
}

.btn-load-more {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  background: #ffffff;
  border: 1.5px solid rgba(81, 49, 25, 0.18);
  border-radius: 999px;
  color: var(--DC-brown, #513119);
  font-size: 0.88rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.05);
  transition: all 0.2s ease;
}

.btn-load-more:hover {
  background: #fff8f3;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

/* RESPONSIVE */
@media (max-width: 540px) {
  .card-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .footer-actions {
    display: grid;
    grid-template-columns: 1fr auto;
    width: 100%;
  }

  .btn-repeat-action {
    justify-content: center;
  }
}
</style>