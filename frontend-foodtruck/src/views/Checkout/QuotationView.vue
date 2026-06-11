<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import boxPlaceholderImage from '@/assets/caja_dicreme.jpg'
import { AlertTriangle } from 'lucide-vue-next'
import quoteService from '@/services/quoteService'

const router = useRouter()

// --- ESTADOS REACTIVOS DEL COMPONENTE ---
const email = ref('')
const phone = ref('')
const firstName = ref('')
const lastName = ref('')
const company = ref('')
const companyRut = ref('')
const address = ref('')
const commune = ref('')
const region = ref('')

const userId = ref<number | null>(null)
const quotationItems = ref<any[]>([])
const errorMessage = ref('')
const showToast = ref(false)

// Dispara la alerta integrada en la interfaz
const triggerAlert = (message: string) => {
  errorMessage.value = message
  showToast.value = true
  
  setTimeout(() => {
    showToast.value = false
  }, 4000)
}

// Sincroniza las cajas de texto con el localStorage para evitar pérdidas de datos al recargar
const handleFirstNameCacheSync = () => {
  localStorage.setItem('dicreme_temp_first_name', firstName.value.trim())
}

const handleLastNameCacheSync = () => {
  localStorage.setItem('dicreme_temp_last_name', lastName.value.trim())
}

// Ciclo de vida para recuperar los datos del carrito y del distribuidor
onMounted(() => {
  // 1. Cargar helados guardados en el almacenamiento temporal
  const savedCart = localStorage.getItem('dicreme_temp_cart')
  if (savedCart) {
    quotationItems.value = JSON.parse(savedCart)
  }
  
  // 2. Extraer textos de los inputs respaldados (por si el usuario presionó 'Atrás')
  const cachedFirstName = localStorage.getItem('dicreme_temp_first_name')
  const cachedLastName = localStorage.getItem('dicreme_temp_last_name')

  if (cachedFirstName !== null) firstName.value = cachedFirstName
  if (cachedLastName !== null) lastName.value = cachedLastName

  // 3. Autorrellenar parámetros del perfil desde las credenciales de la sesión activa
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      
      userId.value = userObj.id || null
      company.value = userObj.nombre_empresa || ''
      companyRut.value = userObj.rut_empresa || ''
      address.value = userObj.direccion || ''
      email.value = userObj.email || userObj.correo_electronico || ''
      phone.value = userObj.telefono || ''
      commune.value = userObj.comuna || ''
      region.value = userObj.region || ''

      // Si no hay caché de nombre, usa el nombre oficial de la cuenta
      if (!firstName.value) {
        firstName.value = userObj.nombre || ''
      }

    } catch (error) {
      console.error('Error autofilling user session parameters:', error)
    }
  }
})

// Calcula la sumatoria estimada multiplicando precios y cantidades
const totalEstimated = computed(() => {
  const totalRaw = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price)
    return sum + (cleanPrice * item.quantity)
  }, 0)
  
  return `$${totalRaw.toLocaleString('es-CL')}`
})

// Envía el payload plano y corregido rumbo al endpoint del backend
const   handleConfirmQuotation = async () => {
  
  // --- VALIDACIONES DE ENTRADA VISUAL ---
  if (!email.value.trim()) { triggerAlert('Por favor, ingresa el correo electrónico.'); return; }
  if (!phone.value.trim()) { triggerAlert('Por favor, ingresa el teléfono.'); return; }
  if (!firstName.value.trim()) { triggerAlert('Por favor, ingresa el nombre de quien recibe.'); return; }
  if (!lastName.value.trim()) { triggerAlert('Por favor, ingresa el apellido.'); return; }
  if (!company.value.trim()) { triggerAlert('Por favor, ingresa el nombre de la empresa.'); return; }
  if (!companyRut.value.trim()) { triggerAlert('Por favor, ingresa el RUT de la empresa.'); return; }
  if (!address.value.trim()) { triggerAlert('Por favor, ingresa la dirección de despacho.'); return; }
  if (!commune.value.trim()) { triggerAlert('Por favor, ingresa la comuna.'); return; }

  // --- GENERACIÓN DE MARCAS DE TIEMPO ---
  const now = new Date()
  const dateString = now.toISOString().split('T')[0] 
  const timeString = now.toTimeString().split(' ')[0] 

  const getFormatIdBySize = (sizeString: string): number => {
    if (!sizeString) return 1
    const cleanSize = sizeString.toUpperCase().trim()
    if (cleanSize.includes('10L')) return 1
    if (cleanSize.includes('5L')) return 2
    if (cleanSize.includes('3L')) return 3
    if (cleanSize.includes('1L')) return 4
    return 1
  }

  // Fallback indexing database mapping layer to prevent NaN values from reaching the server
  const resolveProductId = (item: any): number => {
    console.log("🔍 INSPECCIONANDO PRODUCTO DEL CARRITO:", item)
    
    // If the cart item already contains an ID, use it directly
    if (item.id_producto) return Number(item.id_producto)
    if (item.producto_id) return Number(item.producto_id)
    if (item.id) return Number(item.id)

    console.error('No se pudo resolver el ID del producto para el item:', item)
    return 0;
  }

  // --- CÁLCULO ARITMÉTICO DEL TOTAL ENTERO ---
  const calculatedTotal = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price)
    return sum + (cleanPrice * item.quantity)
  }, 0)

  // --- MAPEADO DE PAYLOAD ---
  const quotationPayload = {
    id_distribuidor: Number(userId.value),
    id_usuario_dicreme: null,
    id_estado_cotizacion: 1,
    persona_recibe: `${firstName.value.trim()} ${lastName.value.trim()}`,
    fecha_creacion: dateString,
    hora_creacion: timeString,
    total_cotizacion: calculatedTotal,

    cotizacion_productos: quotationItems.value.map(item => {
      console.log('Enviando helado:', item.name, '| Claves de ID detectadas:', { 
        id_producto: item.id_producto, 
        id: item.id, 
        producto_id: item.producto_id 
      })
      
      const resolvedId = resolveProductId(item)
      const resolvedPrice = typeof item.price === 'string' 
        ? Number(item.price.replace(/[^0-9]/g, '')) 
        : Number(item.price || item.precio_unitario_venta || 0)

      return {
        id_producto: resolvedId, // Never returns NaN or undefined
        cantidad: Number(item.quantity || item.cantidad || 1),
        precio_unitario_venta: resolvedPrice
      }
    })
  }

  // --- DISPARO DE PETICIÓN ÚNICA MEDIANTE REST API ---
  try {

    console.log("Payload que se va a Laravel:", JSON.stringify(quotationPayload, null, 2))
    const response = await quoteService.createQuote(quotationPayload)
    const result = response.data

    alert(`¡Cotización confirmada exitosamente! N°: ${result.id || result.ID || 'Generada'}`)
    
    const quotationId = result.id || result.ID || null

    // Eliminar datos del caché local al concretar el guardado con éxito
    localStorage.removeItem('dicreme_temp_cart')
    localStorage.removeItem('dicreme_temp_first_name')
    localStorage.removeItem('dicreme_temp_last_name')

    // Viaje a la vista de éxito inyectando los datos de tiempo de la máquina
    router.push({
      path: '/cotizacion-exitosa',
      query: {
        id: quotationId,
        fecha: now.toLocaleDateString('es-CL'), 
        hora: now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
      }
    })

  } catch (error) {
    console.error('Error dispatching payload structurally:', error)
    triggerAlert('Hubo un problema al conectar con el servidor.')
  }
}
</script>

<template>
  <div class="quotation-page">
    <div class="banner-dicreme"></div>

    <Transition name="toast-fade">
      <div v-if="showToast" class="dc-toast-alert">
        <div class="toast-content">
          <AlertTriangle class="toast-icon-vec" color="#e11d48" :size="22" />
          <span class="toast-text">{{ errorMessage }}</span>
        </div>
      </div>
    </Transition>

    <main class="quotation-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Cotización</h2>
        <div class="title-line"></div>
      </div>

      <div class="quotation-grid">
        <section class="forms-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="input-group-full">
            <input v-model="email" type="email" placeholder="Correo" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="phone" type="text" placeholder="Teléfono" class="dc-input" />
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="input-row">
            <input 
              v-model="firstName" 
              @input="handleFirstNameCacheSync" 
              type="text" 
              placeholder="Nombre" 
              class="dc-input half-width" 
            />
            <input 
              v-model="lastName" 
              @input="handleLastNameCacheSync" 
              type="text" 
              placeholder="Apellido" 
              class="dc-input half-width" 
            />
          </div>
          <div class="input-group-full">
            <input v-model="company" type="text" placeholder="Empresa" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="companyRut" type="text" placeholder="Rut Empresa" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="address" type="text" placeholder="Dirección" class="dc-input" />
          </div>
          <div class="input-group-full">
            <input v-model="commune" type="text" placeholder="Comuna" class="dc-input" />
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle cotización:</h3>
          <div class="cart-box-container">
            <div v-if="quotationItems.length === 0" class="empty-box-state">
              No hay productos cargados en la cotización.
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
                  <span class="item-name">{{ item.name }}</span>
                </div>
                <span class="item-tag" :style="{ color: item.color || 'var(--DC-pink)' }">
                  - {{ item.category }}
                </span>
                <div class="item-meta-row">
                  <span class="item-spec">{{ item.size }} - {{ item.price }}</span>
                  <span class="item-qty">X{{ item.quantity }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="total-display-box">
            <span class="total-label">Total Estimado:</span>
            <span class="total-value">{{ totalEstimated }}</span>
          </div>

          <div class="action-row">
            <button class="btn-confirm-cotizacion" @click="handleConfirmQuotation">
              Confirmar cotización
            </button>
          </div>
        </section>
      </div>
    </main>
  </div>
</template>

<style scoped>
.dc-toast-alert { position: fixed; top: 30px; right: 30px; background-color: #fff0f3; border: 1px solid #fad2dc; padding: 14px 24px; border-radius: 25px; box-shadow: 0 4px 15px rgba(228, 134, 159, 0.15); z-index: 1000; max-width: 350px; }
.toast-content { display: flex; align-items: center; gap: 12px; }
.toast-icon-vec { margin-top: -2px; }
.toast-text { color: #322c44; font-size: 0.9rem; font-weight: 600; text-align: left; }
.toast-fade-enter-active, .toast-fade-leave-active { transition: all 0.3s ease; }
.toast-fade-enter-from { opacity: 0; transform: translateY(-10px); }
.toast-fade-leave-to { opacity: 0; transform: translateY(-10px); }
.quotation-page { background-color: #eeedee; min-height: 100vh; font-family: sans-serif; padding-bottom: 60px; }
.banner-dicreme { height: 60px; background-image: url('@/assets/banner_dicreme.png'); background-size: cover; background-position: center; filter: brightness(0.75); }
.quotation-container { max-width: 1020px; margin: 35px auto; padding: 0 25px; }
.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; }
.quotation-grid { display: grid; grid-template-columns: 1.15fr 1fr; gap: 55px; }
.section-subtitle { font-size: 1.05rem; font-weight: bold; color: #1a1624; margin: 0 0 14px 0; text-align: left; }
.forms-column { display: flex; flex-direction: column; gap: 12px; }
.input-group-full { width: 100%; }
.input-row { display: flex; gap: 15px; width: 100%; }
.half-width { flex: 1; }
.dc-input { width: 100%; padding: 11px 22px; background-color: white; border: 1px solid #e4869f; border-radius: 25px; font-size: 0.95rem; outline: none; box-sizing: border-box; color: #333; }
.dc-input::placeholder { color: #b5b2bc; font-weight: 600; }
.summary-column { display: flex; flex-direction: column; }
.cart-box-container { background-color: white; border-radius: 18px; padding: 16px; height: 250px; overflow-y: auto; border: 1px solid #e0dde0; display: flex; flex-direction: column; gap: 12px; box-shadow: inset 0 2px 5px rgba(0,0,0,0.02); }
.empty-box-state { color: #999; font-size: 0.9rem; margin: auto; font-style: italic; }
.checkout-item-card { display: flex; gap: 15px; background-color: #e2dee2; padding: 10px 14px; border-radius: 14px; align-items: center; }
.item-thumb { width: 75px; height: 55px; object-fit: cover; border-radius: 10px; }
.item-info { flex: 1; display: flex; flex-direction: column; text-align: left; }
.item-name { font-size: 0.95rem; font-weight: bold; color: #1a1624; }
.item-tag { font-size: 0.75rem; font-weight: 700; margin-top: 1px; }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 4px; }
.item-spec { font-size: 0.95rem; font-weight: 800; color: #1a1624; }
.item-qty { font-size: 0.95rem; font-weight: 800; color: #444; }
.cart-box-container::-webkit-scrollbar { width: 6px; }
.cart-box-container::-webkit-scrollbar-thumb { background-color: #ccc; border-radius: 4px; }
.total-display-box { background-color: white; border: 1px solid #e4869f; border-radius: 25px; padding: 12px 25px; display: flex; align-items: center; gap: 8px; margin-top: 25px; width: 100%; box-sizing: border-box; }
.total-label { font-size: 1.05rem; font-weight: bold; color: #1a1624; }
.total-value { font-size: 1.05rem; font-weight: bold; color: #1a1624; }
.action-row { display: flex; justify-content: flex-end; margin-top: 30px; }
.btn-confirm-cotizacion { background-color: #322c44; color: white; border: none; padding: 14px 38px; border-radius: 12px; font-weight: bold; font-size: 0.95rem; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 10px rgba(50, 44, 68, 0.2); }
.btn-confirm-cotizacion:hover { background-color: #1a1624; }
</style>