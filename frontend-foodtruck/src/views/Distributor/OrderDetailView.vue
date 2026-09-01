<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ShieldCheck, UtensilsCrossed, ArrowLeft, MessageCircle, Clock, CheckCircle2 } from 'lucide-vue-next'
import orderService from '@/services/orderService' 
import boxPlaceholderImage from '@/assets/logo_jairo.webp'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const orderData = ref<any>(null)
const distributorData = ref<any>(null)
const productsData = ref<any[]>([]) 
const isLoading = ref(true)
const errorMessage = ref('')

// Fallbacks de seguridad
const fallbackCompany = ref('Cliente Foodtruck')
const fallbackAddress = ref('Retiro en Foodtruck')
const fallbackPhone = ref('No registrado')
const fallbackEmail = ref('No registrado')
const fallbackName = ref('Cliente')
const fallbackComuna = ref('Local Foodtruck')

// Captura el ID del pedido directo desde los parámetros de la URL
const pedidoId = computed(() => route.params.id ? String(route.params.id) : '')

// CONTROL DE ESTADOS DE LA LÍNEA DE TIEMPO (Paso 1, 2, 3 o 4)
const currentStep = computed(() => {
  if (!orderData.value) return 1
  
  const rawStatus = orderData.value.id_estado_pedido || orderData.value.estado_id || 1;
  const statusId = Number(rawStatus);
  
  if (statusId >= 1 && statusId <= 4) return statusId
  if (statusId === 5) return 1 // Cancelado
  return 1
})

const currentStatusName = computed(() => {
  if (!orderData.value) return 'Pendiente'
  return orderData.value.estado_pedido_nombre || orderData.value.estado_pedido?.nombre || 'Pendiente'
})

const abrirWhatsapp = () => {
  window.open('https://wa.me/56977579783', '_blank');
};

// --- CARGA DE DATOS OPTIMIZADA ---
onMounted(async () => {
  if (!pedidoId.value) {
    errorMessage.value = 'ID de pedido no válido.'
    isLoading.value = false
    return
  }

  // Carga del fallback desde sesión
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      fallbackCompany.value = userObj.nombre_empresa || 'Cliente Foodtruck'
      fallbackAddress.value = userObj.direccion || 'Retiro en Foodtruck'
      fallbackPhone.value = userObj.telefono || 'No registrado'
      fallbackEmail.value = userObj.email || userObj.correo_electronico || 'No registrado'
      fallbackName.value = userObj.nombre ? `${userObj.nombre} ${userObj.apellido || ''}`.trim() : 'Cliente'
      fallbackComuna.value = userObj.comuna || userObj.nombre_comuna || 'Local Foodtruck'
    } catch (e) {
      console.error('Error parsing fallback:', e)
    }
  }

  try {
    isLoading.value = true

    let response: any = null
    try {
      response = await orderService.getPublicOrderById(pedidoId.value)
    } catch (err) {
      try {
        response = await orderService.getOrderById(pedidoId.value)
      } catch (err2) {
        response = await orderService.getOrderDetails(pedidoId.value)
      }
    }
    
    const payload = response?.data?.data || response?.data 

    if (payload) {
      const totalAmount = Number(payload.total ?? payload.total_cotizacion ?? payload.monto_final ?? 0)
      const estimatedAmount = Number(payload.total ?? payload.subtotal_cotizacion ?? payload.monto_estimado ?? totalAmount)

      orderData.value = {
        id_pedido: payload.numero_pedido_dia || payload.id_pedido || payload.id || pedidoId.value,
        real_id: payload.id_pedido || payload.id,
        persona_recibe: payload.nombre_persona || payload.persona_recibe || payload.usuario?.nombre,
        telefono: payload.numero_telefono || payload.telefono || payload.usuario?.telefono,
        metodo_pago: payload.metodo_pago || 'Efectivo',
        notas: payload.notas || '',
        monto_final: totalAmount,
        monto_estimado: estimatedAmount,
        id_estado_pedido: payload.id_estado_pedido || payload.estado_id || 1,
        estado_pedido: payload.estado_pedido || null,
        estado_pedido_nombre: payload.estado_pedido?.nombre || payload.nombre_estado || 'Pendiente',
        fecha_creacion: payload.created_at || payload.fecha || payload.fecha_creacion
      }

      // Parsear productos
      const rawDetails = payload.detalles || payload.detalle_pedidos || payload.items || []
      productsData.value = rawDetails.map((det: any) => {
        const prod = det.producto || {}
        const rawExcluidos: string[] = []
        const rawAgregados: string[] = []

        if (Array.isArray(det.ingredientes)) {
          det.ingredientes.forEach((mod: any) => {
            const name = mod.ingrediente?.nombre || mod.nombre || ''
            const tipo = (mod.tipo_modificacion || mod.tipo || '').toLowerCase()
            if (tipo.includes('exclu') || tipo.includes('quit') || tipo.includes('sin')) {
              if (name) rawExcluidos.push(name)
            } else {
              if (name) rawAgregados.push(name)
            }
          })
        }

        const unitPrice = typeof det.precio_unitario === 'string'
          ? Number(det.precio_unitario.replace(/[^0-9.]/g, ''))
          : Number(det.precio_unitario || 0)

        const qty = Number(det.cantidad || 1)

        return {
          id: det.id_producto || prod.id_producto,
          nombre: prod.nombre || det.nombre_producto || 'Producto',
          categoria: prod.categoria?.nombre || 'Foodtruck',
          tamano: det.tamano?.nombre_tamaño || det.tamaño?.nombre_tamaño || '',
          cantidad: qty,
          precio_unitario_venta: unitPrice,
          subtotal: unitPrice * qty,
          image: prod.imagen_url || prod.imagen || '',
          excluidos: rawExcluidos,
          agregados: rawAgregados
        }
      })
    } else {
      errorMessage.value = 'No se encontró la información del pedido.'
    }
  } catch (error) {
    console.error('Error al cargar datos del pedido:', error)
    errorMessage.value = 'Ocurrió un error al consultar el pedido.'
  } finally {
    isLoading.value = false
  }
})

const formatCurrency = (value: any) => {
  const safeValue = (value !== undefined && value !== null) ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}
</script>

<template>
  <div class="pedido-detail-page">

    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Pedido N° {{ String(orderData?.id_pedido ?? pedidoId).padStart(6, '0') }}</h2>
        <p class="main-subtitle">Seguimiento en vivo y detalle de tu comanda.</p>
      </div>

      <div class="state-box" v-if="isLoading">
        <div class="food-spinner">
          <UtensilsCrossed :size="36" />
        </div>
        <p>Cargando el estado de tu pedido...</p>
      </div>
      
      <div class="state-box error" v-else-if="errorMessage">{{ errorMessage }}</div>
      
      <div class="detail-grid" v-else-if="orderData">
        
        <!-- COLUMNA IZQUIERDA: DATOS DE CONTACTO Y TOTAL -->
        <section class="info-column">
          <div class="info-card-block">
            <h3 class="section-subtitle">Datos de contacto:</h3>
            <p class="info-text"><strong>Nombre:</strong> {{ orderData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Teléfono:</strong> {{ orderData?.telefono || fallbackPhone }}</p>
            <p class="info-text"><strong>Método de pago:</strong> {{ orderData?.metodo_pago || 'Efectivo' }}</p>
            <p class="info-text" v-if="orderData?.notas"><strong>Notas:</strong> {{ orderData.notas }}</p>
          </div>

          <div class="info-card-block">
            <h3 class="section-subtitle">Datos de Entrega:</h3>
            <p class="info-text"><strong>Receptor:</strong> {{ orderData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Modalidad:</strong> Retiro en Foodtruck</p>
            <p class="info-text"><strong>Fecha:</strong> {{ orderData?.fecha_creacion ? new Date(orderData.fecha_creacion).toLocaleDateString('es-CL') : 'Hoy' }}</p>
          </div>

          <div class="amount-group">
            <div class="amount-row">
              <span class="amount-label">Monto Total a Pagar:</span>
              <div class="amount-box-highlight">
                {{ formatCurrency(orderData?.monto_final) }}
              </div>
            </div>
          </div>
        </section>

        <!-- COLUMNA DERECHA: PRODUCTOS Y TIMELINE -->
        <section class="summary-column">
          <div class="products-card-wrapper">
            <h3 class="section-subtitle">Detalle de productos:</h3>
            
            <div class="products-box-container">
              <div class="empty-products-state" v-if="productsData.length === 0">
                No hay productos registrados en este pedido.
              </div>

              <div 
                class="product-item-card"
                v-else
                v-for="(item, index) in productsData" 
                :key="index" 
              >
                <img :src="item.image || boxPlaceholderImage" class="item-thumb" />
                
                <div class="item-info">
                  <div class="item-header-row">
                    <span class="item-name">
                      {{ item.nombre }}
                      <span v-if="item.tamano && item.tamano !== 'Único'" class="item-size">({{ item.tamano }})</span>
                    </span>
                    <span class="item-qty">x{{ item.cantidad }}</span>
                  </div>
                  
                  <span v-if="item.categoria" class="item-tag">
                    {{ item.categoria }}
                  </span>

                  <!-- EXCLUSIONES -->
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="product-exclusions">
                    <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                      Sin {{ ing }}
                    </span>
                  </div>

                  <!-- AGREGADOS -->
                  <div v-if="item.agregados && item.agregados.length > 0" class="product-extras">
                    <span v-for="extra in item.agregados" :key="extra" class="extra-badge">
                      + {{ extra }}
                    </span>
                  </div>
                  
                  <div class="item-meta-row">
                    <span class="item-spec">
                      {{ formatCurrency(item.precio_unitario_venta) }} c/u
                    </span>
                    <span class="item-subtotal">
                      Total: {{ formatCurrency(item.subtotal) }}
                    </span>
                  </div>
                </div>
              </div>
            </div> 
          </div>

          <!-- LÍNEA DE TIEMPO DE PREPARACIÓN -->
          <div class="timeline-card">
            <div class="timeline-wrapper">
              <div class="floating-icon-container" :class="'step-active-' + currentStep">
                <div class="icon-bubble">
                  <ShieldCheck :size="22" color="white" />
                </div>
              </div>

              <div class="timeline-bar">
                <div class="timeline-progress-bar" :class="'progress-fill-' + currentStep"></div>
              </div>

              <div class="timeline-nodes-row">
                <div class="timeline-node" :class="{ active: currentStep >= 1 }">
                  <div class="node-dot"></div>
                  <span class="node-text">Pendiente</span>
                </div>
                <div class="timeline-node" :class="{ active: currentStep >= 2 }">
                  <div class="node-dot"></div>
                  <span class="node-text">En preparación</span>
                </div>
                <div class="timeline-node" :class="{ active: currentStep >= 3 }">
                  <div class="node-dot"></div>
                  <span class="node-text">Listo</span>
                </div>
                <div class="timeline-node" :class="{ active: currentStep >= 4 }">
                  <div class="node-dot"></div>
                  <span class="node-text">Entregado</span>
                </div>
              </div>
            </div>

            <!-- ESTADO REAL DESTACADO -->
            <div class="status-display-box">
              <span class="status-label-prefix">Estado actual:</span>
              <strong class="status-real-text">{{ currentStatusName }}</strong>
            </div>
          </div>

        </section>

      </div>

      <!-- ACCIONES INFERIORES -->
      <div class="action-row">
        <button class="btn-return-back" @click="router.push('/mis-pedidos')">
          <ArrowLeft :size="18" />
          <span>Volver a Mis Pedidos</span>
        </button>

        <button class="btn-contact-whatsapp" @click="abrirWhatsapp">
          <MessageCircle :size="18" />
          <span>Consultar por WhatsApp</span>
        </button>
      </div>

    </main>

  </div>
</template>

<style scoped>
.pedido-detail-page {
  background-color: var(--DC-bg-gray, #f5ebe0);
  min-height: 100vh;
  padding: 30px 16px 80px 16px;
  font-family: var(--font-main, sans-serif);
  box-sizing: border-box;
}

.detail-container {
  max-width: 1000px;
  margin: 0 auto;
}

.title-section {
  margin-bottom: 24px;
}

.main-title {
  font-size: 1.6rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
  margin: 0 0 4px 0;
  letter-spacing: -0.3px;
}

.main-subtitle {
  font-size: 0.88rem;
  color: var(--DC-text-gray, #6e6a75);
  margin: 0;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 24px;
  align-items: start;
}

/* CARDS DE INFORMACIÓN */
.info-card-block, .products-card-wrapper, .timeline-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 20px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.04);
  margin-bottom: 18px;
}

.section-subtitle {
  font-size: 0.98rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0 0 12px 0;
}

.info-text {
  margin: 0 0 8px 0;
  font-size: 0.88rem;
  color: var(--DC-gray, #322c44);
  line-height: 1.4;
}

.info-text strong {
  color: var(--DC-brown, #513119);
}

.amount-group {
  margin-top: 14px;
}

.amount-label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--DC-text-gray, #6e6a75);
  text-transform: uppercase;
  margin-bottom: 6px;
  display: block;
}

.amount-box-highlight {
  background: var(--DC-brown, #513119);
  color: var(--DC-orange, #e28743);
  padding: 14px 20px;
  border-radius: 14px;
  font-size: 1.4rem;
  font-weight: 900;
  text-align: center;
}

/* LISTA DE PRODUCTOS */
.products-box-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: 320px;
  overflow-y: auto;
}

.product-item-card {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px;
  background: #fdfbf8;
  border-radius: 12px;
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.item-thumb {
  width: 52px;
  height: 52px;
  border-radius: 10px;
  object-fit: cover;
  background: #ffffff;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.item-header-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
}

.item-name {
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.item-size {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
}

.item-qty {
  font-size: 0.76rem;
  font-weight: 800;
  background: var(--button-color, #F4E1D2);
  color: var(--button-text, #513119);
  padding: 1px 6px;
  border-radius: 6px;
}

.item-tag {
  font-size: 0.74rem;
  color: var(--DC-orange, #e28743);
  font-weight: 700;
}

.product-exclusions, .product-extras {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 2px;
}

.exclusion-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: #fee2e2;
  color: #dc2626;
  padding: 1px 6px;
  border-radius: 6px;
}

.extra-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: #dbeafe;
  color: #1d4ed8;
  padding: 1px 6px;
  border-radius: 6px;
}

.item-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
  font-size: 0.8rem;
}

.item-spec {
  color: var(--DC-text-gray, #6e6a75);
}

.item-subtotal {
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

/* TIMELINE */
.timeline-wrapper {
  margin: 40px auto 20px auto;
  position: relative;
  width: 100%;
}

.timeline-bar {
  position: absolute;
  top: 6px;
  left: 35px;
  width: calc(100% - 70px);
  height: 4px;
  background-color: #e5ded5;
  z-index: 1;
}

.timeline-progress-bar {
  height: 100%;
  background-color: var(--DC-orange, #e28743);
  transition: width 0.4s ease;
}

.progress-fill-1 { width: 0%; }
.progress-fill-2 { width: 33.33%; }
.progress-fill-3 { width: 66.66%; }
.progress-fill-4 { width: 100%; }

.timeline-nodes-row {
  display: flex;
  justify-content: space-between;
  position: relative;
  z-index: 2;
}

.timeline-node {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 70px;
}

.node-dot {
  width: 14px;
  height: 14px;
  background-color: #d1c8be;
  border-radius: 50%;
  border: 2px solid #ffffff;
  transition: background-color 0.3s ease;
}

.timeline-node.active .node-dot {
  background-color: var(--DC-orange, #e28743);
}

.node-text {
  font-size: 0.74rem;
  font-weight: 700;
  color: var(--DC-text-gray, #6e6a75);
  margin-top: 8px;
  white-space: nowrap;
}

.timeline-node.active .node-text {
  color: var(--DC-brown, #513119);
}

.floating-icon-container {
  position: absolute;
  top: -42px;
  z-index: 3;
  transform: translateX(-50%); 
  transition: left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.step-active-1 { left: 35px; }
.step-active-2 { left: calc(35px + ((100% - 70px) * 0.3333)); }
.step-active-3 { left: calc(35px + ((100% - 70px) * 0.6666)); }
.step-active-4 { left: calc(100% - 35px); }

.icon-bubble {
  background-color: var(--DC-orange, #e28743);
  padding: 8px;
  border-radius: 50%;
  box-shadow: 0 4px 10px rgba(226, 135, 67, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
}

.status-display-box {
  background: #fdfaf6;
  border: 1.5px solid rgba(226, 135, 67, 0.3);
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 18px;
}

.status-label-prefix {
  font-size: 0.84rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
}

.status-real-text {
  font-size: 1rem;
  color: var(--DC-orange, #e28743);
  font-weight: 900;
}

/* ACCIONES */
.action-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.btn-return-back {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--button-color, #F4E1D2);
  color: var(--button-text, #513119);
  border: 1px solid rgba(81, 49, 25, 0.15);
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-return-back:hover {
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.btn-contact-whatsapp {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: #25D366;
  color: #ffffff;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.25);
}

.btn-contact-whatsapp:hover {
  background-color: #20b858;
  transform: translateY(-1px);
}

/* LOADING */
.state-box {
  background: #ffffff;
  border-radius: 20px;
  padding: 50px 20px;
  text-align: center;
  border: 1px solid rgba(81, 49, 25, 0.12);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.state-box.error { color: #dc2626; }

.food-spinner {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 1.5s ease-in-out infinite;
  margin-bottom: 12px;
}

@keyframes pulse {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.08); }
}

@media (max-width: 768px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }
  
  .action-row {
    flex-direction: column-reverse;
  }
  
  .btn-return-back, .btn-contact-whatsapp {
    width: 100%;
    justify-content: center;
  }
}
</style>