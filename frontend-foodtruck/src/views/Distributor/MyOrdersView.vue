<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { IceCream } from 'lucide-vue-next' 
import orderService from '@/services/orderService'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const ordersList = ref<any[]>([])
const isLoading = ref(true)
const errorMessage = ref('')

// Almacena los datos del distribuidor para usarlos como respaldo en la vista
const fallbackUserCompany = ref('Distribuidor Di Creme')
const fallbackUserAddress = ref('Dirección Registrada')

// Rescata los pedidos procesados del distribuidor activo
const fetchDistributorOrders = async () => {
  try {
    isLoading.value = true
    const userParsed = localStorage.getItem('user')
    
    if (!userParsed) {
      errorMessage.value = 'No se encontró una sesión activa.'
      return
    }
    
    const userObj = JSON.parse(userParsed)
    const distributorId = userObj.id
    
    // Guardamos los datos de sesión para usarlos si las columnas del backend vienen vacías
    fallbackUserCompany.value = userObj.nombre_empresa || userObj.nombre || 'Distribuidor Di Creme'
    fallbackUserAddress.value = userObj.direccion || 'Dirección Registrada'

    // Llamada en vivo usando tu servicio de Axios
    const response = await orderService.getOrdersByDistributor(distributorId)
    ordersList.value = response.data || []
    
    console.log('Orders logs fetched successfully:', ordersList.value)

  } catch (error) {
    console.error('Error fetching orders logs:', error)
    
  } finally {
    isLoading.value = false
  }
}

// Mapea el id_estado_pedido a un string legible en español
const getOrderStatusLabel = (statusId: number): string => {
  if (statusId === 1) return 'Validación'
  if (statusId === 2) return 'En preparación'
  if (statusId === 3) return 'En despacho'
  if (statusId === 4) return 'Entregado'
  return 'Pendiente'
}

onMounted(() => {
  fetchDistributorOrders()
})

// Formatea las fechas al estándar chileno (DD/MM/AAAA)
const formatDate = (dateString: string) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('es-CL')
}

// Formatea los valores numéricos a dinero con separador de miles
const formatCurrency = (value: any) => {
  const safeValue = (value !== undefined && value !== null) ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}
</script>

<template>
  <div class="my-orders-page">
    <main class="history-container">
      <div class="header-section">
        <h2 class="page-title">Mis Pedidos Históricos</h2>
        <p class="page-subtitle">Revisa el estado y el detalle de tus compras procesadas.</p>
      </div>

      <div v-if="isLoading" class="loading-state">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p class="loading-text">Buscando tus pedidos...</p>
      </div>

      <div v-else-if="errorMessage" class="state-message error">{{ errorMessage }}</div>
      <div v-else-if="ordersList.length === 0" class="state-message empty">
        Aún no tienes pedidos registrados en tu cuenta.
      </div>

      <div v-else class="orders-grid">
        <div 
          v-for="order in ordersList" 
          :key="order.id" 
          class="order-card"
          @click="router.push(`/pedido/${order.id}`)"
        >
          <div class="card-header">
            <span class="order-id">Pedido N° {{ String(order.id).padStart(6, '0') }}</span>
            <span class="order-date">{{ formatDate(order.fecha_creacion ?? order.created_at) }}</span>
          </div>

          <div class="card-body">
            <div class="info-row">
              <span class="info-label">Estado:</span>
              <span class="status-badge" :class="'status-' + (order.id_estado_pedido ?? 1)">
                {{ order.estado_nombre ?? getOrderStatusLabel(order.id_estado_pedido) }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">Receptor:</span>
              <span class="info-value">
                {{ order.nombre_receptor ?? order.receptor ?? fallbackUserCompany }}
              </span>
            </div>

            <div class="info-row">
              <span class="info-label">Dirección:</span>
              <span class="info-value text-truncate">
                {{ order.direccion_despacho ?? order.direccion ?? fallbackUserAddress }}
              </span>
            </div>
          </div>

          <div class="card-footer">
            <span class="total-label">Total del Pedido:</span>
            <span class="total-price">
              {{ formatCurrency(order.monto_final ?? order.total ?? order.monto ?? order.total_cotizacion ?? 0) }}
            </span>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.my-orders-page {
  background-color: #eeedee;
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}

.decorative-banner {
  height: 40px;
  background-color: #322c44;
  opacity: 0.85;
}

.history-container {
  max-width: 1100px;
  margin: 35px auto;
  padding: 0 20px;
}

.header-section {
  text-align: left;
  margin-bottom: 30px;
  border-bottom: 2px solid #e4869f;
  padding-bottom: 10px;
}

.page-title {
  font-size: 1.4rem;
  font-weight: 800;
  color: #1a1624;
  margin: 0 0 4px 0;
}

.page-subtitle {
  font-size: 0.9rem;
  color: #7c7289;
  margin: 0;
}

.loading-state {
  background-color: white;
  border-radius: 20px;
  padding: 50px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 15px;
}

.loading-text {
  color: #7c7289;
  font-size: 1rem;
  margin: 0;
  font-weight: 600;
}

.spinner {
  animation: rotate 2s linear infinite;
}

@keyframes rotate {
  100% { transform: rotate(360deg); }
}

.state-message {
  text-align: center;
  padding: 50px;
  background: white;
  border-radius: 18px;
  color: #7c7289;
  font-size: 1rem;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.state-message.error { color: #e11d48; }

.orders-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 25px;
}

.order-card {
  background: white;
  border-radius: 18px;
  border: 1px solid #e0dde0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(228, 134, 159, 0.12);
}

.card-header {
  background-color: #322c44;
  padding: 14px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  color: white;
}

.order-id { font-weight: 700; font-size: 0.95rem; }
.order-date { font-size: 0.85rem; color: #b5b2bc; font-weight: 600; }

.card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
  text-align: left;
  flex: 1;
}

.info-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  gap: 15px;
}

.info-label { color: #7c7289; font-weight: 600; min-width: 80px; }
.info-value { color: #1a1624; font-weight: 700; text-align: right; }
.text-truncate {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 200px;
}

.status-badge {
  padding: 4px 14px;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 700;
}

/* 🚨 Clases mapeadas con id_estado_pedido (1, 2, 3, 4) */
.status-1 { 
  background-color: #fffbeb; 
  color: #d97706; 
  border: 1px solid #fef3c7;
} 

.status-2 { 
  background-color: #fef2f2; 
  color: #dc2626; 
  border: 1px solid #fee2e2;
} 

.status-3 { 
  background-color: #eff6ff; 
  color: #2563eb; 
  border: 1px solid #dbeafe;
}

.status-4 { 
  background-color: #f0fdf4; 
  color: #16a34a; 
  border: 1px solid #dcfce7;
}

.card-footer {
  background-color: #fff0f3;
  border-top: 1px solid #fad2dc;
  padding: 14px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-label { font-size: 0.95rem; font-weight: bold; color: #322c44; }
.total-price { font-size: 1.1rem; font-weight: 800; color: #e4869f; }
</style>