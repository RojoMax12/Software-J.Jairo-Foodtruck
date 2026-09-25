<template>
  <div class="quotation-page">
    <main class="quotation-container">
      <!-- CABECERA -->
      <div class="title-section">
        <h1 class="main-title">Resumen de tu Pedido</h1>
        <p class="main-subtitle">Revisa tus datos de contacto y productos antes de enviar la comanda a cocina.</p>
      </div>

      <!-- AVISO DE LOCAL CERRADO / FUERA DE HORARIO -->
      <section v-if="isStoreClosed" class="store-closed-card">
        <div class="closed-indicator-bubble">
          <AlertTriangle :size="20" />
        </div>
        <div class="closed-content-text">
          <strong>Foodtruck cerrado en este momento</strong>
          <p>
            Nuestro horario de atención oficial es de 
            <b>{{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }} hrs</b>. 
            No es posible enviar comandas fuera de turno. ¡Te esperamos en nuestra próxima jornada!
          </p>
        </div>
      </section>

      <div class="quotation-grid">
        <!-- COLUMNA IZQUIERDA: DATOS DEL CLIENTE Y MÉTODO DE PAGO -->
        <section class="forms-column">
          <!-- AVISO DE SESIÓN O INVITACIÓN -->
          <div v-if="isLoggedIn" class="session-state-card active">
            <div class="session-badge">
              <CheckCircle2 :size="16" class="session-icon" />
              <span>Sesión activa: <strong>{{ loggedUserName }}</strong></span>
            </div>
            <span class="session-hint">Tus datos se autocompletaron y este pedido se guardará en tu cuenta.</span>
          </div>

          <div v-else class="session-state-card prompt">
            <div class="prompt-content">
              <Sparkles :size="16" class="prompt-icon" />
              <span>
                ¿Ya tienes cuenta? 
                <router-link to="/login" class="prompt-link">Inicia sesión</router-link> 
                para acumular tus compras en tu historial.
              </span>
            </div>
          </div>

          <!-- DATOS DE CONTACTO -->
          <div class="form-card">
            <h3 class="card-title">
              <User :size="17" class="title-icon" />
              <span>¿Quién recibe el pedido?</span>
            </h3>

            <div class="name-inputs-grid">
              <div class="form-field">
                <label class="field-label" for="quote-first-name">Nombre <span class="required">*</span></label>
                <input 
                  id="quote-first-name"
                  v-model="firstName" 
                  type="text" 
                  placeholder="Tu nombre" 
                  class="custom-input" 
                  @input="handleFirstNameCacheSync" 
                />
              </div>

              <div class="form-field">
                <label class="field-label" for="quote-last-name">Apellido <span class="required">*</span></label>
                <input 
                  id="quote-last-name"
                  v-model="lastName" 
                  type="text" 
                  placeholder="Tu apellido" 
                  class="custom-input" 
                  @input="handleLastNameCacheSync" 
                />
              </div>
            </div>

            <div class="form-field phone-field">
              <label class="field-label" for="quote-phone">Teléfono Móvil (WhatsApp) <span class="required">*</span></label>
              <div class="phone-input-group">
                <span class="phone-prefix">+56 9</span>
                <input 
                  id="quote-phone"
                  v-model="phone" 
                  type="tel" 
                  placeholder="1234 5678" 
                  class="custom-input phone-real-input" 
                  maxlength="8"
                />
              </div>
              <span class="field-hint">Te notificaremos por WhatsApp cuando tu pedido esté listo.</span>
            </div>
          </div>

          <!-- SELECCIÓN DE MÉTODO DE PAGO -->
          <div class="form-card">
            <h3 class="card-title">
              <CreditCard :size="17" class="title-icon" />
              <span>Método de pago al recibir</span>
            </h3>

            <div class="payment-options-grid">
              <button 
                type="button"
                class="payment-option-card"
                :class="{ active: selectedPaymentMethod === 'Efectivo' }"
                @click="selectedPaymentMethod = 'Efectivo'"
              >
                <Banknote :size="20" class="pay-icon" />
                <span class="pay-title">Efectivo</span>
              </button>

              <button 
                type="button"
                class="payment-option-card"
                :class="{ active: selectedPaymentMethod === 'Tarjeta de Débito' }"
                @click="selectedPaymentMethod = 'Tarjeta de Débito'"
              >
                <CreditCard :size="20" class="pay-icon" />
                <span class="pay-title">Débito</span>
              </button>

              <button 
                type="button"
                class="payment-option-card"
                :class="{ active: selectedPaymentMethod === 'Tarjeta de Crédito' }"
                @click="selectedPaymentMethod = 'Tarjeta de Crédito'"
              >
                <CreditCard :size="20" class="pay-icon" />
                <span class="pay-title">Crédito</span>
              </button>

              <button 
                type="button"
                class="payment-option-card"
                :class="{ active: selectedPaymentMethod === 'Transferencia' }"
                @click="selectedPaymentMethod = 'Transferencia'"
              >
                <Smartphone :size="20" class="pay-icon" />
                <span class="pay-title">Transferencia</span>
              </button>
            </div>
          </div>
        </section>

        <!-- COLUMNA DERECHA: RESUMEN DEL PEDIDO -->
        <section class="summary-column">
          <div class="form-card summary-card">
            <h3 class="card-title">
              <ShoppingBag :size="17" class="title-icon" />
              <span>Tus productos seleccionados ({{ totalItemsCount }})</span>
            </h3>

            <div class="cart-box-container">
              <div v-if="quotationItems.length === 0" class="empty-box-state">
                <p>No tienes productos en el carrito.</p>
                <router-link to="/" class="btn-return-link">Volver a la carta</router-link>
              </div>
              
              <div 
                v-else 
                v-for="item in quotationItems" 
                :key="getItemKey(item)" 
                class="checkout-item-card"
              >
                <img :src="item.image || boxPlaceholderImage" :alt="item.name" class="item-thumb" />
                
                <div class="item-info">
                  <div class="item-name-row">
                    <span class="item-name">{{ item.fullName || item.name }}</span>
                    <strong class="item-price-tag">${{ formatPrice((item.price || 0) * (item.quantity || 1)) }}</strong>
                  </div>
                  
                  <div class="item-tags-row">
                    <span class="item-qty-badge">x{{ item.quantity }}</span>
                    <span v-if="item.size && item.size !== 'Único' && item.size !== 'Normal'" class="item-size-tag">
                      {{ item.size }}
                    </span>
                  </div>

                  <!-- EXCLUSIONES -->
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="customizations-row">
                    <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                      Sin {{ ing }}
                    </span>
                  </div>

                  <!-- AGREGADOS -->
                  <div v-if="item.agregados && item.agregados.length > 0" class="customizations-row">
                    <span v-for="ing in item.agregados" :key="ing" class="addition-badge">
                      + {{ ing }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL ESTIMADO -->
            <div class="total-display-box">
              <span class="total-label">Total a pagar:</span>
              <strong class="total-value">{{ totalEstimated }}</strong>
            </div>

            <!-- NOTA DE PRIVACIDAD LEY 21.719 -->
            <div class="checkout-privacy-note">
              <ShieldCheck :size="15" class="privacy-icon" />
              <span>
                Tus datos personales están protegidos conforme a la 
                <button type="button" class="btn-privacy-link" @click="showPrivacyModal = true">
                  Ley N° 21.719
                </button>.
              </span>
            </div>

            <!-- BOTONES DE CONFIRMACIÓN -->
            <div class="action-row">
              <button 
                type="button"
                class="btn-confirm-cotizacion" 
                :class="{ 'btn-disabled-closed': isStoreClosed }"
                :disabled="isLoading || quotationItems.length === 0 || isStoreClosed"
                @click="handleConfirmQuotation"
              >
                <RefreshCw v-if="isLoading" :size="18" class="spinning" />
                <span>
                  {{ isStoreClosed 
                      ? (shiftWindow?.es_dia_cerrado ? 'Local Cerrado Hoy (Descanso)' : 'Local Cerrado (Fuera de Horario)')
                      : isLoading 
                        ? 'Enviando comanda a cocina...' 
                        : `Confirmar Pedido • ${totalEstimated}` 
                  }}
                </span>
              </button>

              <button 
                type="button"
                class="btn-cancel-cotizacion" 
                :disabled="isLoading"
                @click="handleCancelQuotation"
              >
                Volver a la carta
              </button>
            </div>
          </div>
        </section>
      </div>
    </main>

    <TermsAndPrivacyModal 
      :isOpen="showPrivacyModal" 
      @close="showPrivacyModal = false" 
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  AlertTriangle, ShieldCheck, CheckCircle2, 
  Sparkles, User, CreditCard, Banknote, 
  Smartphone, ShoppingBag, RefreshCw 
} from 'lucide-vue-next'
import boxPlaceholderImage from '@/assets/logo_jairo.webp'
import orderService from '@/services/orderService'
import TermsAndPrivacyModal from '@/components/TermsAndPrivacyModal.vue'
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

// Estados reactivos
const phone = ref('')
const firstName = ref('')
const lastName = ref('')
const selectedPaymentMethod = ref('Efectivo')
const showPrivacyModal = ref(false)
const userId = ref<number | null>(null)
const isLoading = ref(false)
const isLoggedIn = ref(false)
const loggedUserName = ref('')

const shiftWindow = ref<ShiftWindow | null>(null)
const isStoreClosed = computed(() => shiftWindow.value !== null && shiftWindow.value.es_jornada_activa === false)
const quotationItems = ref<any[]>([])

const formatPrice = (price: number | string) => {
  const num = typeof price === 'string' ? Number(price.replace(/[^0-9]/g, '')) : Number(price || 0)
  return num.toLocaleString('es-CL')
}

const totalItemsCount = computed(() => {
  return quotationItems.value.reduce((acc, item) => acc + (Number(item.quantity) || 1), 0)
})

const totalEstimated = computed(() => {
  const totalRaw = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price || 0)
    return sum + (cleanPrice * (Number(item.quantity) || 1))
  }, 0)
  return `$${totalRaw.toLocaleString('es-CL')}`
})

const extract8DigitPhone = (rawPhone: any): string => {
  if (!rawPhone) return ''
  let digits = String(rawPhone).replace(/\D/g, '')

  if (digits.startsWith('56') && digits.length >= 10) {
    digits = digits.slice(2)
  }
  if (digits.startsWith('9') && (digits.length === 9 || digits.length > 8)) {
    digits = digits.slice(1)
  }
  if (digits.length > 8) {
    digits = digits.slice(-8)
  }
  return digits
}

const sanitizePhoneForDB = (rawPhone: string): string => {
  if (!rawPhone) return ''
  let digits = String(rawPhone).replace(/\D/g, '')

  if (digits.length === 8) {
    return '9' + digits
  }
  if (digits.length === 9 && digits.startsWith('9')) {
    return digits
  }
  if (digits.startsWith('56') && digits.length >= 11) {
    return digits.slice(2)
  }
  return digits
}

const handleFirstNameCacheSync = () => localStorage.setItem('dicreme_temp_first_name', firstName.value.trim())
const handleLastNameCacheSync = () => localStorage.setItem('dicreme_temp_last_name', lastName.value.trim())

const getItemKey = (item: any) => {
  const ex = Array.isArray(item.excluidos) ? item.excluidos.join('-') : ''
  const ag = Array.isArray(item.agregados) ? item.agregados.join('-') : ''
  return `${item.id}-${item.size || 'default'}-${ex}-${ag}`
}

onMounted(async () => {
  const savedCart = localStorage.getItem('dicreme_temp_cart')
  if (savedCart) {
    try {
      quotationItems.value = JSON.parse(savedCart)
    } catch (e) {
      console.error('Error parseando carrito guardado:', e)
    }
  }

  // 1. Cargar usuario en sesión
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      userId.value = userObj.id_usuario ?? userObj.id ?? null
      loggedUserName.value = userObj.nombre || userObj.nombre_empresa || 'Cliente'
      isLoggedIn.value = true

      const fullName = (userObj.nombre || userObj.nombre_empresa || '').trim()
      if (fullName) {
        const parts = fullName.split(' ')
        if (parts.length > 1) {
          firstName.value = parts[0]
          lastName.value = parts.slice(1).join(' ')
        } else {
          firstName.value = fullName
        }
      }

      const rawUserPhone = userObj.telefono || ''
      if (rawUserPhone) {
        phone.value = extract8DigitPhone(rawUserPhone)
      }
    } catch (error) {
      console.error('Error parseando sesión de usuario:', error)
    }
  }

  // 2. Caché para usuarios sin cuenta
  if (!isLoggedIn.value) {
    const cachedFirstName = localStorage.getItem('dicreme_temp_first_name')
    const cachedLastName = localStorage.getItem('dicreme_temp_last_name')
    if (cachedFirstName && !firstName.value) firstName.value = cachedFirstName
    if (cachedLastName && !lastName.value) lastName.value = cachedLastName
  }

  // 3. Consultar turno y horario
  try {
    shiftWindow.value = await cashFlowService.fetchShiftWindowFromBackend()
  } catch (e) {
    console.warn('Error al obtener horario en checkout:', e)
  }
})

const handleCancelQuotation = () => {
  router.push('/')
}

const handleConfirmQuotation = async () => {
  if (isStoreClosed.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El foodtruck se encuentra cerrado hoy por ser día de descanso programado.'
      : `El foodtruck está cerrado. Horario de atención: ${shiftWindow.value?.hora_apertura || '19:00'} a ${shiftWindow.value?.hora_cierre || '00:30'} hrs.`
    notify(msg, 'warning')
    return
  }

  if (!firstName.value.trim()) { notify('Por favor, ingresa tu nombre.', 'warning'); return }
  if (!lastName.value.trim()) { notify('Por favor, ingresa tu apellido.', 'warning'); return }
  if (!phone.value.trim()) { notify('Por favor, ingresa tu número telefónico.', 'warning'); return }

  const cleanPhone = sanitizePhoneForDB(phone.value)
  if (!cleanPhone || cleanPhone.length !== 9 || !cleanPhone.startsWith('9')) {
    notify('Ingresa un número telefónico móvil válido de 8 dígitos.', 'warning')
    return
  }

  if (!selectedPaymentMethod.value) {
    notify('Selecciona un método de pago para recibir.', 'warning')
    return
  }

  if (quotationItems.value.length === 0) {
    notify('Tu carrito está vacío.', 'warning')
    return
  }

  isLoading.value = true

  const calculatedTotal = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price || 0)
    return sum + (cleanPrice * (Number(item.quantity) || 1))
  }, 0)

  const orderPayload = {
    nombre_persona: `${firstName.value.trim()} ${lastName.value.trim()}`,
    numero_telefono: cleanPhone,
    id_usuario: userId.value || null,
    total: calculatedTotal,
    metodo_pago: selectedPaymentMethod.value,
    id_estado_pago: 1, // Por pagar
    id_estado_pedido: 1, // Pendiente
    detalles: quotationItems.value.map(item => {
      const unitPrice = typeof item.price === 'string'
        ? Number(item.price.replace(/[^0-9]/g, ''))
        : Number(item.price || 0)

      const rawExcluidosList = item.excluidos || item.exclusiones || []
      const rawAgregadosList = item.agregados || item.extras || []
      
      const cleanProdId = item.id_producto 
        ? Number(item.id_producto) 
        : (typeof item.id === 'number' 
            ? item.id 
            : parseInt(String(item.id || '1').split('_')[0] || '1', 10) || 1)

      const cleanTamanoId = item.id_tamaño 
        ? Number(item.id_tamaño)
        : (typeof item.tamano_id === 'number'
            ? item.tamano_id
            : (typeof item.id_tamano === 'number'
                ? item.id_tamano
                : parseInt(String(item.tamano_id || item.id_tamaño || item.id_tamano || '1').split('_')[0] || '1', 10) || 1))

      return {
        id_producto: cleanProdId,
        id_tamaño: cleanTamanoId,
        nombre_producto: item.name || item.nombre || 'Producto',
        cantidad: Number(item.quantity || 1),
        precio_unitario: unitPrice,
        subtotal: unitPrice * Number(item.quantity || 1),
        excluidos: rawExcluidosList,
        agregados: rawAgregadosList,
        opciones_seleccionadas: [
          ...(item.size ? [{ tipo: 'Tamaño', valor: item.size }] : []),
          ...rawExcluidosList.map((ex: any) => ({
            tipo: 'Exclusión',
            ingrediente: typeof ex === 'object' ? (ex.nombre || ex.name || '') : String(ex)
          })),
          ...rawAgregadosList.map((ag: any) => ({
            tipo: 'Agregado',
            precio: typeof ag === 'object' ? Number(ag.precio || ag.price || 0) : 0,
            ingrediente: typeof ag === 'object' ? (ag.nombre || ag.name || '') : String(ag)
          }))
        ]
      }
    })
  }

  try {
    let res: any
    if (userId.value) {
      res = await orderService.createOrder(orderPayload).catch(() => orderService.createPublicOrder(orderPayload))
    } else {
      res = await orderService.createPublicOrder(orderPayload)
    }

    const createdOrder = res?.data?.data || res?.data || {}

    localStorage.removeItem('dicreme_temp_cart')
    localStorage.removeItem('dicreme_temp_first_name')
    localStorage.removeItem('dicreme_temp_last_name')

    const now = new Date()
    notify('¡Pedido enviado a cocina con éxito!', 'success')
    router.push({
      path: '/cotizacion-exitosa',
      query: {
        id: (createdOrder.numero_pedido_dia || createdOrder.id_pedido || createdOrder.id || '').toString(),
        fecha: now.toLocaleDateString('es-CL'),
        hora: now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
      }
    })
  } catch (err: any) {
    console.error('Error enviando pedido:', err)
    const apiMsg = err.response?.data?.message || 'Hubo un problema al procesar tu pedido. Intenta nuevamente.'
    notify(apiMsg, 'error')
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.quotation-page {
  background-color: var(--DC-bg-gray, #f8f6f3);
  min-height: 100vh;
  padding: 2rem 1.25rem 4rem;
}

.quotation-container {
  max-width: 1060px;
  margin: 0 auto;
}

/* CABECERA */
.title-section {
  margin-bottom: 1.5rem;
}

.main-title {
  font-size: 1.85rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
  margin: 0 0 0.35rem 0;
  line-height: 1.15;
}

.main-subtitle {
  font-size: 0.92rem;
  color: var(--DC-text-gray, #7c7468);
  margin: 0;
}

/* AVISO LOCAL CERRADO */
.store-closed-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-left: 5px solid var(--DC-orange, #e28743);
  padding: 1rem 1.25rem;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.04);
}

.closed-indicator-bubble {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(226, 135, 67, 0.15);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.closed-content-text strong {
  display: block;
  font-size: 0.95rem;
  color: var(--DC-brown, #513119);
  margin-bottom: 0.2rem;
}

.closed-content-text p {
  margin: 0;
  font-size: 0.84rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.45;
}

/* LAYOUT EN 2 COLUMNAS */
.quotation-grid {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 1.5rem;
  align-items: start;
}

/* TARJETAS FORMULARIO & RESUMEN */
.form-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.4rem;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 18px rgba(26, 14, 5, 0.04);
  margin-bottom: 1.25rem;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0 0 1.15rem 0;
}

.title-icon {
  color: var(--DC-orange, #e28743);
}

/* ESTADOS DE SESIÓN */
.session-state-card {
  border-radius: 14px;
  padding: 0.85rem 1.1rem;
  margin-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.session-state-card.active {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
}

.session-state-card.prompt {
  background: #fffdfa;
  border: 1.5px solid rgba(226, 135, 67, 0.3);
}

.session-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.84rem;
  font-weight: 700;
  color: #15803d;
}

.session-icon {
  color: #16a34a;
}

.session-hint {
  font-size: 0.76rem;
  color: #166534;
}

.prompt-content {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.84rem;
  color: var(--DC-brown, #513119);
}

.prompt-icon {
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
}

.prompt-link {
  color: var(--DC-orange, #e28743);
  font-weight: 800;
  text-decoration: underline;
}

/* INPUTS */
.name-inputs-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.85rem;
  margin-bottom: 0.85rem;
}

.form-field {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
}

.field-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.field-label .required {
  color: #dc2626;
}

.field-hint {
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
  margin-top: 2px;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  background-color: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.9rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
  transition: all 0.2s ease;
  font-family: inherit;
}

.custom-input:focus {
  background-color: #ffffff;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.16);
}

.phone-input-group {
  display: flex;
  align-items: center;
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  border-radius: 12px;
  background: var(--DC-bg-gray, #f8f6f3);
  overflow: hidden;
  transition: all 0.2s ease;
}

.phone-input-group:focus-within {
  background-color: #ffffff;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.16);
}

.phone-prefix {
  background: rgba(81, 49, 25, 0.08);
  padding: 0.75rem 1rem;
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  border-right: 1.5px solid rgba(81, 49, 25, 0.12);
  user-select: none;
}

.phone-real-input {
  border: none;
  background: transparent;
  padding: 0.75rem 1rem;
  letter-spacing: 1.5px;
}

.phone-real-input:focus {
  box-shadow: none;
}

/* OPCIONES DE PAGO */
.payment-options-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.75rem;
}

.payment-option-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 1rem 0.75rem;
  border-radius: 12px;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1.5px solid rgba(81, 49, 25, 0.1);
  cursor: pointer;
  transition: all 0.2s ease;
}

.payment-option-card:hover {
  background: #ffffff;
  border-color: var(--DC-orange, #e28743);
}

.payment-option-card.active {
  background: #fffdfa;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.18);
}

.pay-icon {
  color: var(--DC-text-gray, #7c7468);
  transition: color 0.2s ease;
}

.payment-option-card.active .pay-icon {
  color: var(--DC-orange, #e28743);
}

.pay-title {
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
}

.payment-option-card.active .pay-title {
  color: var(--DC-orange, #e28743);
}

/* LISTADO DE ITEMS */
.cart-box-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 1.25rem;
  max-height: 380px;
  overflow-y: auto;
  padding-right: 4px;
  scrollbar-width: thin;
  scrollbar-color: rgba(81, 49, 25, 0.2) transparent;
}

.cart-box-container::-webkit-scrollbar {
  width: 5px;
}

.cart-box-container::-webkit-scrollbar-thumb {
  background: rgba(81, 49, 25, 0.2);
  border-radius: 999px;
}

.empty-box-state {
  text-align: center;
  padding: 2.5rem 1rem;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.88rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
}

.btn-return-link {
  color: var(--DC-orange, #e28743);
  font-weight: 800;
  text-decoration: underline;
  font-size: 0.84rem;
}

.checkout-item-card {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.75rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.item-thumb {
  width: 54px;
  height: 54px;
  border-radius: 10px;
  object-fit: cover;
  background: #ffffff;
  flex-shrink: 0;
}

.item-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.item-name-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
}

.item-name {
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
  line-height: 1.25;
  overflow-wrap: anywhere;
}

.item-price-tag {
  font-size: 0.92rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  white-space: nowrap;
}

.item-tags-row {
  display: flex;
  align-items: center;
  gap: 5px;
  margin-top: 2px;
}

.item-qty-badge {
  font-size: 0.72rem;
  font-weight: 900;
  background: rgba(226, 135, 67, 0.15);
  color: var(--DC-orange, #e28743);
  padding: 1px 6px;
  border-radius: 6px;
}

.item-size-tag {
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
}

.customizations-row {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.exclusion-badge {
  font-size: 0.65rem;
  font-weight: 800;
  background: #fee2e2;
  color: #dc2626;
  padding: 1px 6px;
  border-radius: 4px;
}

.addition-badge {
  background-color: #dbeafe;
  color: #1d4ed8;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 4px;
}

/* TOTAL */
.total-display-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: var(--DC-brown, #513119);
  border-radius: 14px;
  color: #ffffff;
  margin-bottom: 1rem;
}

.total-label {
  font-size: 0.88rem;
  font-weight: 700;
  color: #eedccf;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.total-value {
  font-size: 1.45rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  line-height: 1;
}

/* NOTA LEY 21.719 */
.checkout-privacy-note {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
  margin-bottom: 1.15rem;
  line-height: 1.4;
}

.privacy-icon {
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
}

.btn-privacy-link {
  background: none;
  border: none;
  padding: 0;
  color: var(--DC-orange, #e28743);
  font-weight: 700;
  text-decoration: underline;
  cursor: pointer;
}

/* ACCIONES DE CONFIRMACIÓN */
.action-row {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.btn-confirm-cotizacion {
  padding: 0.95rem 1.25rem;
  border-radius: 12px;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
  transition: all 0.2s ease;
}

.btn-confirm-cotizacion:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(81, 49, 25, 0.25);
}

.btn-confirm-cotizacion:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.btn-disabled-closed {
  background-color: #cbd5e1 !important;
  color: #64748b !important;
}

.btn-cancel-cotizacion {
  padding: 0.65rem;
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  transition: color 0.15s ease;
}

.btn-cancel-cotizacion:hover {
  color: var(--DC-brown, #513119);
}

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* RESPONSIVO */
@media (max-width: 860px) {
  .quotation-grid {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
}

@media (max-width: 580px) {
  .quotation-page {
    padding: 1.25rem 0.85rem 3rem;
  }

  .name-inputs-grid {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .form-card {
    padding: 1.15rem 1rem;
  }

  .payment-options-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
  }

  .payment-option-card {
    padding: 0.75rem 0.5rem;
  }

  .total-display-box {
    padding: 0.85rem 1rem;
  }

  .total-value {
    font-size: 1.25rem;
  }
}
</style>