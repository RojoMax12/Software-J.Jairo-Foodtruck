<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { ShieldCheck, IceCream } from 'lucide-vue-next'
import orderService from '@/services/orderService' 
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const orderData = ref<any>(null)
const distributorData = ref<any>(null)
const productsData = ref<any[]>([]) 
const isLoading = ref(true)
const errorMessage = ref('')

// Fallbacks de seguridad
const fallbackCompany = ref('Cliente J.Jairo')
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
  
  const rawStatus = orderData.value.id_estado_pedido || orderData.value.estado_id;
  const statusId = Number(rawStatus);
  
  // Asumiendo que los estados en base de datos son 1, 2, 3 y 4
  if (statusId >= 1 && statusId <= 4) return statusId
  return 1
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
      fallbackCompany.value = userObj.nombre_empresa || 'Cliente J.Jairo'
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

    // Llamada a la API de pedidos con múltiples fallbacks para máxima resiliencia
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
    
    // Entrar al objeto 'data' que envía Laravel
    const payload = response?.data?.data || response?.data 

    if (payload) {
      // 1. Guardamos los datos base del pedido
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
        id_estado_pedido: Number(payload.id_estado_pedido || 1),
        fecha_creacion: payload.fecha || payload.fecha_creacion || payload.created_at
      }

      // 2. Guardamos el distribuidor / cliente
      distributorData.value = payload.distribuidor || payload.usuario || {}

      // 3. Adaptamos la lista de productos y extraemos ingredientes quitados y extras
      const rawDetails = payload.detalles || payload.productos || payload.items || []
      productsData.value = rawDetails.map((det: any, idx: number) => {
        const prod = det.producto || det
        const prodName = prod.nombre || prod.name || prod.nombre_producto || det.nombre_producto || det.nombre || 'Producto'
        const sizeName = det.tamaño?.nombre || det.tamano?.nombre || prod.formato || det.formato || ''
        const categoryName = prod.categoria?.nombre_categoria || prod.categoria?.nombre || (prod.id_categoria === 1 ? 'Completos' : '') || ''
        const unitPrice = Number(det.precio_unitario ?? det.precio_unitario_venta ?? det.precio ?? prod.precio ?? prod.precio_base ?? 0)
        const qty = Number(det.cantidad ?? det.quantity ?? 1)
        const subtotal = Number(det.subtotal ?? (unitPrice * qty))
        const image = prod.imagen || prod.image || det.imagen || det.image || boxPlaceholderImage

        // Exclusiones (Ingredientes quitados)
        let removed: string[] = []
        if (Array.isArray(det.ingredientes)) {
          removed = det.ingredientes
            .filter((ing: any) => {
              const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase()
              return tipo.includes('exclu') || tipo.includes('quit')
            })
            .map((ing: any) => ing.ingrediente?.nombre || ing.nombre || (typeof ing === 'string' ? ing : 'Ingrediente'))
            .filter(Boolean)
        } else if (Array.isArray(det.ingredientes_excluidos)) {
          removed = det.ingredientes_excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean)
        } else if (Array.isArray(det.excluidos)) {
          removed = det.excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean)
        } else if (Array.isArray(det.removedIngredients)) {
          removed = det.removedIngredients.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean)
        } else if (Array.isArray(det.modificaciones)) {
          removed = det.modificaciones
            .filter((m: any) => {
              const tipo = String(m.tipo || m.tipo_modificacion || '').toLowerCase()
              return tipo.includes('exclu') || tipo.includes('quit')
            })
            .map((m: any) => m.nombre || m.ingrediente?.nombre || (typeof m === 'string' ? m : ''))
            .filter(Boolean)
        }

        // Agregados / Extras
        let added: string[] = []
        if (Array.isArray(det.ingredientes)) {
          added = det.ingredientes
            .filter((ing: any) => {
              const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase()
              return tipo.includes('agre') || tipo.includes('extra')
            })
            .map((ing: any) => ing.ingrediente?.nombre || ing.nombre || (typeof ing === 'string' ? ing : 'Extra'))
            .filter(Boolean)
        } else if (Array.isArray(det.agregados)) {
          added = det.agregados.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean)
        } else if (Array.isArray(det.addedExtras)) {
          added = det.addedExtras.map((i: any) => typeof i === 'string' ? i : (i.name || i.nombre)).filter(Boolean)
        } else if (Array.isArray(det.modificaciones)) {
          added = det.modificaciones
            .filter((m: any) => {
              const tipo = String(m.tipo || m.tipo_modificacion || '').toLowerCase()
              return tipo.includes('agre') || tipo.includes('extra')
            })
            .map((m: any) => m.nombre || m.ingrediente?.nombre || (typeof m === 'string' ? m : ''))
            .filter(Boolean)
        }

        return {
          id: det.id_detalle_pedido || det.id || idx + 1,
          cantidad: qty,
          precio_unitario_venta: unitPrice,
          subtotal: subtotal,
          image: image,
          nombre: prodName,
          tamano: sizeName,
          categoria: categoryName,
          excluidos: [...new Set(removed)],
          agregados: [...new Set(added)],
          producto: {
            name: prodName,
            image: image,
            imagen: image,
            categoria: categoryName,
            formato: sizeName,
            precio: unitPrice
          }
        }
      })
    } else {
      errorMessage.value = 'No se encontró información para este pedido.'
    }
  } catch (error) {
    console.error('Error fetching pedido details:', error)
    errorMessage.value = 'Hubo un problema al conectar con el servidor.'
  } finally {
    isLoading.value = false
  }
})

const getPedidoStatusLabel = (statusId: number): string => {
  const safeId = Number(statusId)
  if (safeId === 1) return 'En cola'
  if (safeId === 2) return 'En preparación'
  if (safeId === 3) return 'Listo'
  if (safeId === 4) return 'Entregado'
  return 'En proceso'
}

const formatCurrency = (value: number) => {
  const safeValue = value ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}

const handleGoBack = () => {
  router.push('/')
}
</script>

<template>
  <div class="pedido-detail-page">

    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Pedido N° {{ String(orderData?.id_pedido ?? pedidoId).padStart(6, '0') }}</h2>
        <div class="title-line"></div>
      </div>

      <div class="state-box" v-if="isLoading">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Cargando el estado de tu pedido...</p>
      </div>
      
      <div class="state-box error" v-else-if="errorMessage">{{ errorMessage }}</div>
      
      <div class="detail-grid" v-else-if="orderData">
        
        <section class="info-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Nombre:</strong> {{ orderData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Teléfono:</strong> {{ orderData?.telefono || distributorData?.telefono || fallbackPhone }}</p>
            <p class="info-text"><strong>Método de pago:</strong> {{ orderData?.metodo_pago || 'Efectivo' }}</p>
            <p class="info-text" v-if="orderData?.notas"><strong>Notas / Instrucciones:</strong> {{ orderData.notas }}</p>
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Receptor:</strong> {{ orderData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Modalidad:</strong> Retiro en Foodtruck</p>
            <p class="info-text"><strong>Fecha:</strong> {{ orderData?.fecha_creacion ? new Date(orderData.fecha_creacion).toLocaleDateString('es-CL') : 'Hoy' }}</p>
          </div>

          <div class="amount-group">
            <div class="amount-row">
              <span class="amount-label">Monto Estimado:</span>
              <div class="amount-box-white">
                {{ formatCurrency(orderData?.monto_estimado) }}
              </div>
            </div>

            <div class="amount-row highlighted" style="margin-top: 10px;">
              <span class="amount-label">Monto Final:</span>
              <div class="amount-box-pink">
                {{ formatCurrency(orderData?.monto_final) }}
              </div>
            </div>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle productos:</h3>
          
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
              <img :src="item.image || item.producto?.image || item.producto?.imagen || boxPlaceholderImage" class="item-thumb" />
              
              <div class="item-info">
                <div class="item-header-row">
                  <span class="item-name">
                    {{ item.nombre || item.producto?.name || item.producto?.nombre || 'Producto' }}
                    <span v-if="item.tamano" class="item-size">({{ item.tamano }})</span>
                  </span>
                  <span class="item-qty">x{{ item.cantidad }}</span>
                </div>
                
                <span v-if="item.categoria" class="item-tag">
                  {{ item.categoria }}
                </span>

                <!-- 🚨 INGREDIENTES QUITADOS (EXCLUSIONES) -->
                <div v-if="item.excluidos && item.excluidos.length > 0" class="product-exclusions">
                  <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                    Sin {{ ing }}
                  </span>
                </div>

                <!-- AGREGADOS / EXTRAS -->
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

          <div class="timeline-wrapper">
            
            <div class="floating-icon-container" :class="'step-active-' + currentStep">
              <div class="icon-bubble">
                <ShieldCheck :size="24" color="white" />
              </div>
            </div>

            <div class="timeline-bar">
              <div class="timeline-progress-bar" :class="'progress-fill-' + currentStep"></div>
            </div>

            <div class="timeline-nodes-row">
              <div class="timeline-node" :class="{ active: currentStep >= 1 }">
                <div class="node-dot"></div>
                <span class="node-text">En cola</span>
              </div>
              <div class="timeline-node" :class="{ active: currentStep >= 2 }">
                <div class="node-dot"></div>
                <span class="node-text">En preparación</span>
              </div>
              <div class="timeline-node" :class="{ active: currentStep >= 3 }">
                <div class="node-dot"></div>
                <span class="node-text">Listo</span>
              </div>
              <div class="timeline-node" :class="{ active: currentStep === 4 }">
                <div class="node-dot"></div>
                <span class="node-text">Entregado</span>
              </div>
            </div>
          </div>

          <div class="status-display-box">
            Estado del pedido: <span class="capitalize-text">{{ getPedidoStatusLabel(orderData?.id_estado_pedido) }}</span>
          </div>

          <div class="action-row">
            <button class="btn-contact" style="margin-left: 15px;" @click="abrirWhatsapp">
              <span>Contáctanos</span>
              <div class="icon-whatsapp">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="#25D366">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.477-1.761-1.65-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.346.446-.52.149-.174.199-.298.298-.497.1-.198.05-.372-.025-.521-.075-.148-.675-1.628-.925-2.228-.243-.588-.495-.508-.675-.515-.174-.007-.374-.008-.573-.008-.199 0-.521.074-.794.372-.273.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.174-1.413-.074-.124-.273-.198-.57-.347z"/>
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.113.548 4.16 1.574 5.96L0 24l6.198-1.576A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22.119c-1.805 0-3.57-.484-5.116-1.405l-.367-.217-3.8.968.995-3.674-.24-.38a9.92 9.92 0 0 1-1.52-5.323c0-5.518 4.485-10.003 10.003-10.003 5.518 0 10.002 4.485 10.002 10.003 0 5.517-4.484 10.002-10.002 10.002z"/>
                </svg>
              </div>
            </button>

            <button class="btn-return-back" @click="handleGoBack">
              Volver a página principal
            </button>
          </div>
        </section>

      </div>
    </main>
  </div>
</template>

<style scoped>
.pedido-detail-page {
  background-color: var(--DC-bg-light, #fcf8f2);
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}

.detail-container {
  max-width: 1020px;
  margin: 35px auto;
  padding: 0 25px;
}

.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: var(--DC-pink, #e4869f); width: 100%; }

.detail-grid {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 55px;
}

.section-subtitle { font-size: 1.05rem; font-weight: bold; color: #1a1624; margin: 0 0 14px 0; text-align: left; }

.info-card-block {
  background-color: white;
  border-radius: 18px;
  padding: 20px 24px;
  border: 1px solid #e0dde0;
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.01);
}

.info-text {
  margin: 0;
  font-size: 0.95rem;
  color: var(--DC-gray, #322c44);
  text-align: left;
  line-height: 1.4;
}

.amount-group {
  display: flex;
  flex-direction: column;
  margin-top: 25px;
}

.amount-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}

.amount-label { font-size: 0.95rem; font-weight: bold; color: #1a1624; }

.amount-box-white {
  background-color: white;
  border: 1px solid #e0dde0;
  border-radius: 25px;
  padding: 12px 25px;
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1624;
  text-align: left;
}

.amount-box-pink {
  background-color: white;
  border: 2px solid var(--DC-pink, #e4869f);
  border-radius: 25px;
  padding: 12px 25px;
  font-size: 1.1rem;
  font-weight: 800;
  color: #1a1624;
  text-align: left;
}

.products-box-container {
  background-color: white;
  border-radius: 18px;
  padding: 16px;
  max-height: 340px;
  overflow-y: auto;
  border: 1px solid #e0dde0;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.empty-products-state {
  color: #999;
  font-size: 0.9rem;
  margin: auto;
  font-style: italic;
  padding: 20px 0;
}

.product-item-card {
  display: flex;
  gap: 14px;
  background-color: #f7f5f7;
  padding: 12px 14px;
  border-radius: 14px;
  align-items: flex-start;
  border: 1px solid #eae6ea;
  transition: all 0.2s ease;
}

.product-item-card:hover {
  background-color: #f0ecf0;
}

.item-thumb { 
  width: 70px; 
  height: 60px; 
  object-fit: cover; 
  border-radius: 10px; 
  flex-shrink: 0;
  background-color: #eee;
}

.item-info { 
  flex: 1; 
  display: flex; 
  flex-direction: column; 
  text-align: left; 
}

.item-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
}

.item-name { 
  font-size: 0.95rem; 
  font-weight: bold; 
  color: #1a1624; 
  line-height: 1.2;
}

.item-size {
  font-size: 0.85rem;
  color: #666;
  font-weight: 600;
  margin-left: 4px;
}

.item-qty { 
  font-size: 0.85rem; 
  font-weight: 800; 
  color: #322c44;
  background: #e5e0e8;
  padding: 2px 8px;
  border-radius: 6px;
  white-space: nowrap;
}

.item-tag { 
  font-size: 0.75rem; 
  font-weight: 700; 
  margin-top: 3px; 
  color: var(--DC-orange, #e28743); 
}

/* 🌟 INGREDIENTES QUITADOS / EXCLUSIONES */
.product-exclusions {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 6px;
  margin-bottom: 2px;
}

.exclusion-badge {
  background-color: #ffe3e3;
  color: #c92a2a;
  border: 1px solid #ffa8a8;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
}

/* 🌟 AGREGADOS / EXTRAS */
.product-extras {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 4px;
  margin-bottom: 2px;
}

.extra-badge {
  background-color: #e6fcf5;
  color: #0ca678;
  border: 1px solid #96f2d7;
  font-size: 0.75rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
  display: inline-flex;
  align-items: center;
}

.item-meta-row { 
  display: flex; 
  justify-content: space-between; 
  align-items: center; 
  margin-top: 8px; 
  font-size: 0.85rem;
  border-top: 1px dashed #e0dde0;
  padding-top: 5px;
}

.item-spec { 
  font-size: 0.85rem; 
  font-weight: 600; 
  color: #666; 
}

.item-subtotal {
  font-size: 0.9rem;
  font-weight: 800;
  color: #1a1624;
}

/* TIMELINE */
.timeline-wrapper {
  margin: 60px auto 30px auto;
  position: relative;
  width: 100%;
}

.timeline-bar {
  position: absolute;
  top: 6px;
  left: 35px;
  width: calc(100% - 70px);
  height: 4px;
  background-color: #e0dde0;
  z-index: 1;
}

.timeline-progress-bar {
  height: 100%;
  background-color: var(--DC-pink, #e4869f);
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
  background-color: #b5b2bc;
  border-radius: 50%;
  border: 2px solid var(--DC-bg-light, #fcf8f2);
  transition: background-color 0.3s ease;
}

.timeline-node.active .node-dot {
  background-color: var(--DC-pink, #e4869f);
}

.node-text {
  font-size: 0.75rem;
  font-weight: 700;
  color: #7c7289;
  margin-top: 8px;
  white-space: nowrap;
}

.timeline-node.active .node-text {
  color: #1a1624;
}

.floating-icon-container {
  position: absolute;
  top: -46px;
  z-index: 3;
  transform: translateX(-50%); 
  transition: left 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.step-active-1 { left: 35px; }
.step-active-2 { left: calc(35px + ((100% - 70px) * 0.3333)); }
.step-active-3 { left: calc(35px + ((100% - 70px) * 0.6666)); }
.step-active-4 { left: calc(100% - 35px); }

.icon-bubble {
  background-color: var(--DC-pink, #e4869f);
  padding: 8px;
  border-radius: 50%;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  animation: bounce 2s infinite ease-in-out;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.status-display-box {
  background-color: white;
  border: 1px solid var(--DC-pink, #e4869f);
  border-radius: 14px;
  padding: 14px 20px;
  font-size: 1.05rem;
  font-weight: bold;
  color: #1a1624;
  margin-top: 25px;
  text-align: left;
  box-shadow: 0 2px 8px rgba(228, 134, 159, 0.04);
}

.capitalize-text {
  text-transform: lowercase;
}
.capitalize-text::first-letter {
  text-transform: uppercase;
}

.action-row {
  display: flex;
  justify-content: flex-end;
  margin-top: 30px;
}

.btn-return-back {
  background-color: #322c44;
  color: white;
  border: none;
  padding: 14px 30px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 10px rgba(50, 44, 68, 0.15);
}

.btn-return-back:hover { background-color: #1a1624; }

.state-box { background: white; padding: 50px; border-radius: 18px; text-align: center; color: #7c7289; font-weight: 600; }
.state-box.error { color: #e11d48; }
.spinner { animation: rotate 2s linear infinite; margin-bottom: 10px; }
@keyframes rotate { 100% { transform: rotate(360deg); } }

.btn-contact {
  background-color: rgba(146, 146, 146, 0.849);
  color: white;
  border: none;
  padding: 14px 30px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 0.95rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  transition: all 0.2s ease;
}

.icon-whatsapp {
  margin-left: 5px;
  display: flex;
  align-items: center;
}

@media (max-width: 768px) {
  .order-detail-container {
    padding: 15px 12px;
  }

  .detail-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .timeline-card {
    padding: 24px 14px;
  }

  .node-text {
    font-size: 0.68rem;
  }

  .action-row {
    flex-direction: column-reverse;
    gap: 10px;
    margin-top: 20px;
  }

  .btn-return-back,
  .btn-contact {
    width: 100%;
    justify-content: center;
  }
}
</style>