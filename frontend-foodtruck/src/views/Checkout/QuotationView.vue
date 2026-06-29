<template>
  <div class="quotation-page">
    
    <Transition name="toast-fade">
      <div v-if="showToast" class="dc-toast-alert">
        <div class="toast-content">
          <AlertTriangle class="toast-icon-vec" color="white" :size="22" />
          <span class="toast-text">{{ errorMessage }}</span>
        </div>
      </div>
    </Transition>

    <main class="quotation-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Pedido</h2>
        <div class="title-line"></div>
      </div>

      <div class="quotation-grid">
        <section class="forms-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          
          <div class="input-group-full">
            <input 
              v-model="firstName" 
              type="text" 
              placeholder="Nombre" 
              class="dc-input" 
              @input="handleFirstNameCacheSync" 
            />
          </div>
          
          <div class="input-group-full">
            <input 
              v-model="lastName" 
              type="text" 
              placeholder="Apellido" 
              class="dc-input" 
              @input="handleLastNameCacheSync" 
            />
          </div>

          <div class="input-group-full">
            <input 
              v-model="phone" 
              type="text" 
              placeholder="Teléfono" 
              class="dc-input" 
            />
          </div>

          <div class="input-group-full">
            <select v-model="selectedPaymentMethod" class="dc-input">
              <option value="" disabled selected>Método de pago...</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Transferencia">Transferencia</option>
              <option value="Tarjeta de Débito">Tarjeta de Débito</option>
              <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
            </select>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle pedido:</h3>
          <div class="cart-box-container">
            <div v-if="quotationItems.length === 0" class="empty-box-state">
              No hay productos cargados en el pedido.
            </div>
            
            <div 
              v-else 
              v-for="item in quotationItems" 
              :key="item.id + '-' + item.size" 
              class="checkout-item-card"
            >
              <img :src="item.image || boxPlaceholderImage" :alt="item.name" class="item-thumb" />
              
              <div class="item-info">
                <div class="item-name-row">
                  <span class="item-name">{{ item.fullName || item.name }}</span>
                </div>
                
                <div class="item-tags-row">
                  <span class="item-tag" :style="{ color: item.color || 'var(--DC-orange)' }">
                    {{ item.category }}
                  </span>
                  <span v-if="item.size" class="item-size-tag">{{ item.size }}</span>
                </div>

                <div v-if="item.excluidos && item.excluidos.length > 0" class="exclusions-box">
                  <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                    Sin {{ ing }}
                  </span>
                </div>

                <div class="item-meta-row">
                  <span class="item-spec">${{ item.price.toLocaleString('es-CL') }}</span>
                  <span class="item-qty">x{{ item.quantity }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="total-display-box">
            <span class="total-label">Total Estimado:</span>
            <span class="total-value">{{ totalEstimated }}</span>
          </div>

          <div class="action-row">
            <button 
              class="btn-confirm-cotizacion" 
              @click="handleConfirmQuotation"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Procesando...' : 'Confirmar pedido' }}
            </button>

            <button 
              class="btn-cancel-cotizacion" 
              @click="handleCancelQuotation"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Cancelando...' : 'Cancelar pedido' }}
            </button>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'
import { AlertTriangle } from 'lucide-vue-next'
import quoteService from '@/services/quoteService'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const phone = ref('')
const firstName = ref('')
const lastName = ref('')
const selectedPaymentMethod = ref('')
const userId = ref<number | null>(null)
const isLoading = ref(false)

const quotationItems = ref<any[]>([])
const errorMessage = ref('')
const showToast = ref(false)

const triggerAlert = (message: string) => {
  errorMessage.value = message
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 4000)
}

const handleFirstNameCacheSync = () => localStorage.setItem('dicreme_temp_first_name', firstName.value.trim())
const handleLastNameCacheSync = () => localStorage.setItem('dicreme_temp_last_name', lastName.value.trim())

onMounted(() => {
  const savedCart = localStorage.getItem('dicreme_temp_cart')
  if (savedCart) quotationItems.value = JSON.parse(savedCart)
  
  const cachedFirstName = localStorage.getItem('dicreme_temp_first_name')
  const cachedLastName = localStorage.getItem('dicreme_temp_last_name')
  if (cachedFirstName !== null) firstName.value = cachedFirstName
  if (cachedLastName !== null) lastName.value = cachedLastName

  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      userId.value = userObj.id || null
      phone.value = userObj.telefono || ''
      if (!firstName.value) firstName.value = userObj.nombre || ''
    } catch (error) {
      console.error('Error parseando usuario:', error)
    }
  }
})

const totalEstimated = computed(() => {
  const totalRaw = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price)
    return sum + (cleanPrice * item.quantity)
  }, 0)
  return `$${totalRaw.toLocaleString('es-CL')}`
})

/*const handleConfirmQuotation = async () => {
  // --- VALIDACIONES DE ENTRADA CORREGIDAS ---
  if (!firstName.value.trim()) { triggerAlert('Por favor, ingresa tu nombre.'); return; }
  if (!lastName.value.trim()) { triggerAlert('Por favor, ingresa tu apellido.'); return; }
  if (!phone.value.trim()) { triggerAlert('Por favor, ingresa un teléfono.'); return; }
  if (!selectedPaymentMethod.value) { triggerAlert('Selecciona un método de pago.'); return; }

  const now = new Date()
  const dateString = now.toISOString().split('T')[0] 
  const timeString = now.toTimeString().split(' ')[0] 

  const resolveProductId = (item: any): number => {
    if (item.id_producto) return Number(item.id_producto)
    if (item.producto_id) return Number(item.producto_id)
    if (item.id) return Number(item.id)
    return 0;
  }

  const calculatedTotal = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price)
    return sum + (cleanPrice * item.quantity)
  }, 0)

  const quotationPayload = {
    id_distribuidor: Number(userId.value) || 0, // 0 si es invitado
    id_usuario_dicreme: null,
    id_estado_cotizacion: 1,
    persona_recibe: `${firstName.value.trim()} ${lastName.value.trim()}`,
    telefono: phone.value.trim(),
    fecha_creacion: dateString,
    hora_creacion: timeString,
    total_cotizacion: calculatedTotal,
    metodo_pago: selectedPaymentMethod.value,

    cotizacion_productos: quotationItems.value.map(item => {
      const resolvedId = resolveProductId(item)
      const resolvedPrice = typeof item.price === 'string' 
        ? Number(item.price.replace(/[^0-9]/g, '')) 
        : Number(item.price || item.precio_unitario_venta || 0)

      return {
        id_producto: resolvedId,
        cantidad: Number(item.quantity || item.cantidad || 1),
        precio_unitario_venta: resolvedPrice,
        ingredientes_excluidos: item.excluidos || [] // Se envía a BDD
      }
    })
  }

  try {
    const response = await quoteService.createQuote(quotationPayload)
    const result = response.data
    alert(`¡Pedido confirmado exitosamente! N°: ${result.id || result.ID || 'Generado'}`)
    
    localStorage.removeItem('dicreme_temp_cart')
    localStorage.removeItem('dicreme_temp_first_name')
    localStorage.removeItem('dicreme_temp_last_name')

    router.push({
      path: '/cotizacion-exitosa',
      query: {
        id: result.id || result.ID || null,
        fecha: now.toLocaleDateString('es-CL'), 
        hora: now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
      }
    })

  } catch (error) {
    console.error('Error al enviar:', error)
    triggerAlert('Hubo un problema al conectar con el servidor.')
  }
}*/

const handleCancelQuotation = async () =>{
  router.push({
    path: '/'
})
}
const handleConfirmQuotation = async () => {
  // 1. Validaciones
  if (!firstName.value.trim()) { triggerAlert('Por favor, ingresa tu nombre.'); return; }
  if (!lastName.value.trim()) { triggerAlert('Por favor, ingresa tu apellido.'); return; }
  if (!phone.value.trim()) { triggerAlert('Por favor, ingresa un teléfono.'); return; }
  if (!selectedPaymentMethod.value) { triggerAlert('Selecciona un método de pago.'); return; }

  isLoading.value = true;

  // 2. Construir el objeto que se enviaría (El Payload)
  const quotationPayload = {
    persona_recibe: `${firstName.value.trim()} ${lastName.value.trim()}`,
    telefono: phone.value.trim(),
    metodo_pago: selectedPaymentMethod.value,
    items: quotationItems.value, // Tus productos
    total: totalEstimated.value
  };

  // 3. Simular tiempo de espera
  await new Promise(resolve => setTimeout(resolve, 1500));

  // 4. Resultado Dummy
  const dummyResult = {
    id: Math.floor(Math.random() * 1000) + 1
  };

  console.log("--- [DUMMY] Payload que se enviaría ---");
  console.log(JSON.stringify(quotationPayload, null, 2));

  // 5. Limpiar caché
  localStorage.removeItem('dicreme_temp_cart');
  localStorage.removeItem('dicreme_temp_first_name');
  localStorage.removeItem('dicreme_temp_last_name');

  // 6. Redirigir
  const now = new Date();
  router.push({
    path: '/cotizacion-exitosa',
    query: {
      id: dummyResult.id.toString(),
      fecha: now.toLocaleDateString('es-CL'),
      hora: now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
    }
  });

  isLoading.value = false;
};


</script>

<style scoped>
/* ALERTAS (TOAST) */
.dc-toast-alert { position: fixed; top: 30px; right: 30px; background-color: var(--DC-pink); padding: 14px 24px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); z-index: 1000; max-width: 350px; }
.toast-content { display: flex; align-items: center; gap: 12px; }
.toast-text { color: white; font-size: 0.9rem; font-weight: 700; text-align: left; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: all 0.3s ease; }
.toast-fade-enter-from, .toast-fade-leave-to { opacity: 0; transform: translateY(-10px); }

/* CONTENEDORES GLOBALES */
.quotation-page { background-color: var(--DC-bg-gray); min-height: 100vh; font-family: var(--font-main); padding: 40px 0 60px 0; }
.quotation-container { max-width: 1020px; margin: 0 auto; padding: 0 25px; }

/* TÍTULO */
.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.8rem; font-weight: 900; color: var(--DC-gray); margin: 0 0 6px 0; text-transform: uppercase; }
.title-line { height: 4px; background-color: var(--DC-orange); width: 60px; border-radius: 2px; }

/* GRILLA */
.quotation-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
.section-subtitle { font-size: 1.1rem; font-weight: 800; color: var(--DC-gray); margin: 0 0 16px 0; text-transform: uppercase; }

/* FORMULARIO */
.forms-column { display: flex; flex-direction: column; gap: 15px; }
.input-group-full { width: 100%; }
.dc-input { width: 100%; padding: 14px 20px; background-color: white; border: 2px solid #eeedee; border-radius: 12px; font-size: 1rem; font-weight: 600; outline: none; box-sizing: border-box; color: var(--DC-gray); transition: border-color 0.2s; }
.dc-input:focus { border-color: var(--DC-orange); }
.dc-input::placeholder { color: #b5b2bc; font-weight: 500; }

select.dc-input {
  appearance: none;
  background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23e28743' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  background-size: 1.2em;
  cursor: pointer;
}

/* DETALLE DEL PEDIDO (CARRITO) */
.summary-column { display: flex; flex-direction: column; }
.cart-box-container { background-color: white; border-radius: 16px; padding: 16px; height: 350px; overflow-y: auto; border: 2px solid #eeedee; display: flex; flex-direction: column; gap: 12px; }
.empty-box-state { color: var(--DC-text-gray); font-size: 0.95rem; margin: auto; font-weight: 600; }

/* Custom Scrollbar */
.cart-box-container::-webkit-scrollbar { width: 6px; }
.cart-box-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }

.checkout-item-card { display: flex; gap: 15px; background-color: #f8f9fa; padding: 12px; border-radius: 12px; align-items: flex-start; border: 1px solid #eeedee; }
.item-thumb { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; }
.item-info { flex: 1; display: flex; flex-direction: column; }

.item-name-row { margin-bottom: 2px; }
.item-name { font-size: 1rem; font-weight: 800; color: var(--DC-gray); line-height: 1.2;}

.item-tags-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
.item-tag { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; }
.item-size-tag { font-size: 0.75rem; font-weight: 700; color: var(--DC-text-gray); background: #eeedee; padding: 2px 6px; border-radius: 4px;}

/* 🌟 BADGES DE EXCLUSIONES */
.exclusions-box { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
.exclusion-badge { background-color: #fff0f3; color: #c92a2a; font-size: 0.65rem; font-weight: 800; padding: 3px 6px; border-radius: 4px; text-transform: uppercase; border: 1px solid #ffc9c9; }

.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: auto; }
.item-spec { font-size: 1.05rem; font-weight: 900; color: var(--DC-orange); }
.item-qty { font-size: 0.9rem; font-weight: 800; color: var(--DC-gray); background: white; padding: 2px 8px; border-radius: 6px; border: 1px solid #ccc;}

/* TOTALES Y BOTÓN */
.total-display-box { background-color: white; border: 2px solid var(--DC-orange); border-radius: 16px; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; width: 100%; box-sizing: border-box; }
.total-label { font-size: 1.1rem; font-weight: 800; color: var(--DC-gray); }
.total-value { font-size: 1.4rem; font-weight: 900; color: var(--DC-orange); }

.action-row { display: flex; margin-top: 20px; flex-direction: column; }
.btn-confirm-cotizacion { width: 100%; background-color: var(--DC-orange); color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); text-transform: uppercase; }
.btn-confirm-cotizacion:hover { background-color: var(--DC-brown); transform: translateY(-2px); }
.btn-confirm-cotizacion:active { transform: translateY(0); }


.btn-cancel-cotizacion {margin-top: 10px;   width: 100%; background-color: var(--DC-orange); color: white; border: none; padding: 16px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 15px rgba(226, 135, 67, 0.3); text-transform: uppercase; }
.btn-cancel-cotizacion:hover { background-color: var(--DC-brown); transform: translateY(-2px); }
.btn-cancel-cotizacion:active { transform: translateY(0); }


/* RESPONSIVE */
@media (max-width: 768px) {
  .quotation-grid { grid-template-columns: 1fr; gap: 30px; }
  .cart-box-container { height: auto; max-height: 400px; }
}
</style>