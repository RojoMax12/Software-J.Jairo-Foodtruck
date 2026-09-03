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
        <h2 class="main-title">Resumen de tu Pedido</h2>
        <p class="main-subtitle">Revisa tus datos y productos antes de confirmar.</p>
      </div>

      <!-- AVISO DE LOCAL CERRADO / FUERA DE HORARIO -->
      <div v-if="isStoreClosed" class="store-closed-checkout-banner">
        <div class="closed-banner-left">
          <AlertTriangle :size="24" class="closed-icon" />
          <div class="closed-text-box">
            <strong>Foodtruck cerrado en este momento</strong>
            <span>Nuestro horario de atención es de <strong>{{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }} hrs</strong>. No es posible procesar pedidos fuera de turno. ¡Te esperamos en nuestro próximo turno!</span>
          </div>
        </div>
      </div>

      <div class="quotation-grid">
        <!-- COLUMNA IZQUIERDA: DATOS DEL CLIENTE Y MÉTODO DE PAGO -->
        <section class="forms-column">
          <!-- AVISO DE SESIÓN INICIADA O INVITACIÓN -->
          <div v-if="isLoggedIn" class="user-session-card">
            <div class="session-badge">
              <CheckCircle2 :size="16" class="session-icon" />
              <span>Sesión activa: <strong>{{ loggedUserName }}</strong></span>
            </div>
            <span class="session-hint">Datos autocompletados. Tu compra se acumulará en tu historial.</span>
          </div>

          <div v-else class="user-login-prompt-banner">
            <div class="prompt-content">
              <Sparkles :size="16" class="prompt-icon" />
              <span>
                ¿Ya tienes cuenta? 
                <router-link to="/login" class="prompt-link">Inicia sesión</router-link> 
                para autocompletar tus datos y ver tu historial.
              </span>
            </div>
          </div>

          <!-- DATOS DE CONTACTO -->
          <div class="form-card">
            <h3 class="card-title">
              <User :size="18" class="title-icon" />
              <span>¿Quién recibe el pedido?</span>
            </h3>

            <div class="name-inputs-grid">
              <div class="input-field">
                <label class="input-label">Nombre</label>
                <input 
                  v-model="firstName" 
                  type="text" 
                  placeholder="Tu nombre" 
                  class="friendly-input" 
                  @input="handleFirstNameCacheSync" 
                />
              </div>

              <div class="input-field">
                <label class="input-label">Apellido</label>
                <input 
                  v-model="lastName" 
                  type="text" 
                  placeholder="Tu apellido" 
                  class="friendly-input" 
                  @input="handleLastNameCacheSync" 
                />
              </div>
            </div>

            <div class="input-field phone-field">
              <label class="input-label">Teléfono de contacto</label>
              <div class="phone-input-box">
                <span class="phone-prefix-tag">+56 9</span>
                <input 
                  v-model="phone" 
                  type="tel" 
                  placeholder="1234 5678" 
                  class="phone-real-input" 
                  maxlength="8"
                />
              </div>
            </div>
          </div>

          <!-- SELECCIÓN DE MÉTODO DE PAGO -->
          <div class="form-card">
            <h3 class="card-title">
              <CreditCard :size="18" class="title-icon" />
              <span>Método de pago</span>
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

        <!-- COLUMNA DERECHA: DETALLE DEL PEDIDO Y CONFIRMACIÓN -->
        <section class="summary-column">
          <div class="summary-card">
            <h3 class="card-title">
              <ShoppingBag :size="18" class="title-icon" />
              <span>Tus productos seleccionados</span>
            </h3>

            <div class="cart-box-container">
              <div v-if="quotationItems.length === 0" class="empty-box-state">
                No tienes productos en el carrito.
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
                    <span class="item-price-tag">${{ (item.price * item.quantity).toLocaleString('es-CL') }}</span>
                  </div>
                  
                  <div class="item-tags-row">
                    <span class="item-qty-badge">x{{ item.quantity }}</span>
                    <span v-if="item.size && item.size !== 'Único'" class="item-size-tag">{{ item.size }}</span>
                  </div>

                  <!-- EXCLUSIONES -->
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="exclusions-box">
                    <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                      Sin {{ ing }}
                    </span>
                  </div>

                  <!-- AGREGADOS -->
                  <div v-if="item.agregados && item.agregados.length > 0" class="additions-box">
                    <span v-for="ing in item.agregados" :key="ing" class="addition-badge">
                      + {{ ing }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- TOTAL DISPLAY -->
            <div class="total-display-box">
              <span class="total-label">Total a pagar:</span>
              <span class="total-value">{{ totalEstimated }}</span>
            </div>

            <!-- NOTA DE PRIVACIDAD LEY 21.719 -->
            <div class="checkout-privacy-note">
              <ShieldCheck :size="15" class="privacy-icon" />
              <span>
                Tus datos son protegidos conforme a la 
                <button type="button" class="btn-privacy-link" @click="showPrivacyModal = true">
                  Ley N° 21.719
                </button>.
              </span>
            </div>

            <!-- BOTONES DE ACCIÓN -->
            <div class="action-row">
              <button 
                class="btn-confirm-cotizacion" 
                :class="{ 'btn-disabled-closed': isStoreClosed }"
                @click="handleConfirmQuotation"
                :disabled="isLoading || quotationItems.length === 0 || isStoreClosed"
                :title="isStoreClosed ? 'El local se encuentra cerrado' : ''"
              >
                <span>
                  {{ isStoreClosed 
                      ? (shiftWindow?.es_dia_cerrado ? 'Local Cerrado Hoy (Día de Descanso)' : 'Local Cerrado (Fuera de Horario)')
                      : isLoading 
                        ? 'Enviando comanda...' 
                        : `Confirmar Pedido • ${totalEstimated}` 
                  }}
                </span>
              </button>

              <button 
                class="btn-cancel-cotizacion" 
                @click="handleCancelQuotation"
                :disabled="isLoading"
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
  Smartphone, ShoppingBag 
} from 'lucide-vue-next'
import boxPlaceholderImage from '@/assets/logo_jairo.webp'
import quoteService from '@/services/quoteService'
import orderService from '@/services/orderService'
import TermsAndPrivacyModal from '@/components/TermsAndPrivacyModal.vue'
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const { notify } = useNotification()

// --- ESTADOS REACTIVOS ---
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
const errorMessage = ref('')
const showToast = ref(false)

const triggerAlert = (message: string) => {
  errorMessage.value = message
  showToast.value = true
  setTimeout(() => { showToast.value = false }, 4000)
}

// Extrae exactamente los 8 dígitos requeridos para el input (el badge tiene '+56 9')
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

onMounted(async () => {
  const savedCart = localStorage.getItem('dicreme_temp_cart')
  if (savedCart) {
    try {
      quotationItems.value = JSON.parse(savedCart)
    } catch (e) {
      console.error('Error parseando carrito:', e)
    }
  }
  
  // 1. Cargar datos de usuario autenticado
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      userId.value = userObj.id_usuario ?? userObj.id ?? null
      loggedUserName.value = userObj.nombre || userObj.nombre_empresa || 'Cliente'
      isLoggedIn.value = true

      // Autocompletar nombre y apellido
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

      // Autocompletar teléfono quitando el '9' inicial y '+56'
      const rawUserPhone = userObj.telefono || ''
      if (rawUserPhone) {
        phone.value = extract8DigitPhone(rawUserPhone)
      }
    } catch (error) {
      console.error('Error parseando sesión de usuario:', error)
    }
  }

  // 2. Si no había sesión, cargar de caché temporal si existe
  if (!isLoggedIn.value) {
    const cachedFirstName = localStorage.getItem('dicreme_temp_first_name')
    const cachedLastName = localStorage.getItem('dicreme_temp_last_name')
    if (cachedFirstName !== null && !firstName.value) firstName.value = cachedFirstName
    if (cachedLastName !== null && !lastName.value) lastName.value = cachedLastName
  }

  // 3. Consultar horario de turno
  try {
    shiftWindow.value = await cashFlowService.fetchShiftWindowFromBackend()
  } catch (e) {
    console.warn('Error al obtener horario en checkout:', e)
  }
})

const totalEstimated = computed(() => {
  const totalRaw = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price || 0)
    return sum + (cleanPrice * (item.quantity || 1))
  }, 0)
  return `$${totalRaw.toLocaleString('es-CL')}`
})

const handleCancelQuotation = () => {
  router.push('/')
}

const handleConfirmQuotation = async () => {
  if (isStoreClosed.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El Foodtruck se encuentra cerrado hoy por ser día de descanso programado. No es posible realizar pedidos.'
      : `El Foodtruck se encuentra cerrado en este momento. Horario de atención: ${shiftWindow.value?.hora_apertura || '19:00'} a ${shiftWindow.value?.hora_cierre || '00:30'} hrs.`;
    triggerAlert(msg);
    return;
  }

  if (!firstName.value.trim()) { triggerAlert('Por favor, ingresa tu nombre.'); return; }
  if (!lastName.value.trim()) { triggerAlert('Por favor, ingresa tu apellido.'); return; }
  if (!phone.value.trim()) { triggerAlert('Por favor, ingresa tu número telefónico.'); return; }

  const cleanPhone = sanitizePhoneForDB(phone.value);
  if (!cleanPhone || cleanPhone.length !== 9 || !cleanPhone.startsWith('9')) {
    triggerAlert('Por favor, ingresa un número de teléfono válido de 8 dígitos.');
    return;
  }

  if (!selectedPaymentMethod.value) { triggerAlert('Selecciona un método de pago.'); return; }

  isLoading.value = true;

  const calculatedTotal = quotationItems.value.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string'
      ? Number(item.price.replace(/[^0-9]/g, ''))
      : Number(item.price || 0);
    return sum + (cleanPrice * (item.quantity || 1));
  }, 0);

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
        : Number(item.price || 0);

      const rawExcluidosList = item.excluidos || item.exclusiones || item.ingredientesRemovidos || [];
      const rawAgregadosList = item.agregados || item.extras || [];
      const cleanProdId = item.id_producto 
        ? Number(item.id_producto) 
        : (typeof item.id === 'number' 
            ? item.id 
            : parseInt(String(item.id || '1').split('_')[0] || '1', 10) || 1);

      const cleanTamanoId = item.id_tamaño 
        ? Number(item.id_tamaño)
        : (typeof item.tamano_id === 'number'
            ? item.tamano_id
            : (typeof item.id_tamano === 'number'
                ? item.id_tamano
                : parseInt(String(item.tamano_id || item.id_tamaño || item.id_tamano || '1').split('_')[0] || '1', 10) || 1));

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
          ...(item.tamaño ? [{ tipo: 'Tamaño', valor: item.tamaño }] : []),
          ...(item.size ? [{ tipo: 'Tamaño', valor: item.size }] : []),
          ...(item.exclusiones ? item.exclusiones.map((ex: string) => ({ tipo: 'Exclusión', ingrediente: ex })) : []),
          ...(item.ingredientesRemovidos ? item.ingredientesRemovidos.map((ex: string) => ({ tipo: 'Sin', ingrediente: ex })) : []),
          ...(item.agregadosDetails ? item.agregadosDetails.map((ag: any) => ({
            id_ingrediente: ag.id_ingrediente || null,
            tipo: 'Agregado',
            precio: 0,
            ingrediente: ag.nombre || ag.name || (typeof ag === 'string' ? ag : '')
          })) : []),
          ...((item.agregados && (!item.agregadosDetails || !item.agregadosDetails.length)) ? item.agregados.map((ag: string) => ({
            tipo: 'Agregado',
            precio: 0,
            ingrediente: ag
          })) : []),
          ...(item.excluidosDetails && item.excluidosDetails.length
            ? item.excluidosDetails.map((ex: any) => ({
                id_ingrediente: ex.id_ingrediente || null,
                tipo: 'Exclusión',
                precio: 0,
                ingrediente: ex.nombre || ex.name || (typeof ex === 'string' ? ex : '')
              }))
            : rawExcluidosList.map((ex: any) => ({
                id_ingrediente: typeof ex === 'object' ? (ex.id_ingrediente || ex.id || null) : null,
                tipo: 'Exclusión',
                precio: 0,
                ingrediente: typeof ex === 'object' ? (ex.nombre || ex.name || '') : String(ex)
              }))
          ),
          ...(item.agregadosDetails && item.agregadosDetails.length
            ? item.agregadosDetails.map((ag: any) => ({
                id_ingrediente: ag.id_ingrediente || null,
                tipo: 'Agregado',
                precio: Number(ag.precio || ag.price || 0),
                ingrediente: ag.nombre || ag.name || (typeof ag === 'string' ? ag : '')
              }))
            : rawAgregadosList.map((ag: any) => ({
                id_ingrediente: typeof ag === 'object' ? (ag.id_ingrediente || ag.id || null) : null,
                tipo: 'Agregado',
                precio: typeof ag === 'object' ? Number(ag.precio || ag.price || 0) : 0,
                ingrediente: typeof ag === 'object' ? (ag.nombre || ag.name || '') : String(ag)
              }))
          )
        ]
      };
    })
  };

  try {
    let res: any;
    if (userId.value) {
      res = await orderService.createOrder(orderPayload).catch(() => orderService.createPublicOrder(orderPayload));
    } else {
      res = await orderService.createPublicOrder(orderPayload);
    }

    const createdOrder = res?.data?.data || res?.data || {};

    localStorage.removeItem('dicreme_temp_cart');
    localStorage.removeItem('dicreme_temp_first_name');
    localStorage.removeItem('dicreme_temp_last_name');

    const now = new Date();
    router.push({
      path: '/cotizacion-exitosa',
      query: {
        id: (createdOrder.numero_pedido_dia || createdOrder.id_pedido || createdOrder.id || '').toString(),
        fecha: now.toLocaleDateString('es-CL'),
        hora: now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
      }
    });
  } catch (err: any) {
    console.error('Error enviando pedido:', err);
    const apiMsg = err.response?.data?.message || 'Hubo un problema al procesar tu pedido. Por favor intenta de nuevo.';
    triggerAlert(apiMsg);
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.store-closed-checkout-banner {
  background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
  border: 2px solid #fed7aa;
  border-radius: 16px;
  padding: 16px 20px;
  margin-bottom: 24px;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.08);
}

.closed-banner-left {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}

.closed-icon {
  color: #c2410c;
  flex-shrink: 0;
  margin-top: 2px;
}

.closed-text-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.closed-text-box strong {
  font-size: 1rem;
  color: #9a3412;
}

.closed-text-box span {
  font-size: 0.88rem;
  color: #7c2d12;
  line-height: 1.4;
}

.btn-disabled-closed {
  background-color: #94a3b8 !important;
  cursor: not-allowed !important;
  opacity: 0.85;
}

.quotation-page {
  background-color: var(--DC-bg-gray, #f5ebe0);
  min-height: 100vh;
  font-family: var(--font-main, sans-serif);
  padding: 30px 16px 80px 16px;
  box-sizing: border-box;
}

.quotation-container {
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

.quotation-grid {
  display: grid;
  grid-template-columns: 1.1fr 1fr;
  gap: 24px;
  align-items: start;
}

/* CARDS GENERALES */
.form-card, .summary-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 20px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.04);
  margin-bottom: 18px;
}

.card-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 1rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0 0 16px 0;
}

.title-icon {
  color: var(--DC-orange, #e28743);
}

/* SESIÓN */
.user-session-card {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  border-radius: 14px;
  padding: 12px 16px;
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.session-badge {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.86rem;
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

.user-login-prompt-banner {
  background: #fff7ed;
  border: 1px solid #fed7aa;
  border-radius: 14px;
  padding: 12px 16px;
  margin-bottom: 16px;
}

.prompt-content {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.84rem;
  color: #9a3412;
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

/* INPUTS DE FORMULARIO */
.name-inputs-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
  margin-bottom: 12px;
}

.input-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.input-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.friendly-input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.18);
  font-size: 0.88rem;
  font-family: inherit;
  outline: none;
  transition: all 0.2s;
  color: var(--DC-gray, #322c44);
}

.friendly-input:focus {
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.phone-field {
  margin-bottom: 4px;
}

.phone-input-box {
  display: flex;
  align-items: center;
  border: 1px solid rgba(81, 49, 25, 0.18);
  border-radius: 10px;
  background: #ffffff;
  overflow: hidden;
  transition: all 0.2s;
}

.phone-input-box:focus-within {
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.phone-prefix-tag {
  background: rgba(81, 49, 25, 0.08);
  padding: 12px 18px;
  min-width: 72px;
  text-align: center;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  border-right: 1.5px solid rgba(81, 49, 25, 0.15);
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}

.phone-real-input {
  flex: 1;
  padding: 12px 16px;
  border: none;
  outline: none;
  font-size: 1rem;
  font-family: inherit;
  letter-spacing: 1.5px;
  color: var(--DC-gray, #322c44);
}

/* MÉTODO DE PAGO EN TARJETAS */
.payment-options-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
}

.payment-option-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 14px 10px;
  border-radius: 12px;
  background: #ffffff;
  border: 1.5px solid rgba(81, 49, 25, 0.15);
  cursor: pointer;
  transition: all 0.2s ease;
}

.payment-option-card:hover {
  background: #fff8f3;
  border-color: var(--DC-orange, #e28743);
}

.payment-option-card.active {
  background: #fff8f3;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  box-shadow: 0 2px 10px rgba(226, 135, 67, 0.2);
}

.pay-icon {
  color: var(--DC-text-gray, #6e6a75);
}

.payment-option-card.active .pay-icon {
  color: var(--DC-orange, #e28743);
}

.pay-title {
  font-size: 0.84rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.payment-option-card.active .pay-title {
  color: var(--DC-orange, #e28743);
}

/* PRODUCTOS EN RESUMEN */
.cart-box-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 16px;
  max-height: 380px;
  overflow-y: auto;
  padding-right: 4px;
}

.checkout-item-card {
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
  gap: 3px;
  min-width: 0;
}

.item-name-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 8px;
  min-width: 0;
}

.item-name {
  font-size: 0.9rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  min-width: 0;
  word-break: break-word;
}

.item-price-tag {
  font-size: 0.9rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
  white-space: nowrap;
}

.item-tags-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.item-qty-badge {
  font-size: 0.76rem;
  font-weight: 800;
  background: var(--button-color, #F4E1D2);
  color: var(--button-text, #513119);
  padding: 1px 6px;
  border-radius: 6px;
}

.item-size-tag {
  font-size: 0.76rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
}

.exclusions-box, .additions-box {
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

.addition-badge {
  font-size: 0.7rem;
  font-weight: 700;
  background: #dbeafe;
  color: #1d4ed8;
  padding: 1px 6px;
  border-radius: 6px;
}

.total-display-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 14px 16px;
  background: var(--DC-brown, #513119);
  border-radius: 12px;
  color: #ffffff;
  margin-bottom: 14px;
}

.total-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #eedccf;
}

.total-value {
  font-size: 1.4rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.checkout-privacy-note {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.76rem;
  color: var(--DC-text-gray, #6e6a75);
  margin-bottom: 14px;
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

.action-row {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.btn-confirm-cotizacion {
  padding: 14px;
  border-radius: 12px;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  font-size: 1rem;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
}

.btn-confirm-cotizacion:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(81, 49, 25, 0.25);
}

.btn-cancel-cotizacion {
  padding: 10px;
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #6e6a75);
  font-size: 0.86rem;
  font-weight: 700;
  cursor: pointer;
  transition: color 0.2s;
}

.btn-cancel-cotizacion:hover {
  color: var(--DC-brown, #513119);
}

/* TOAST */
.dc-toast-alert {
  position: fixed;
  top: 24px;
  right: 24px;
  z-index: 10000;
  background: #dc2626;
  color: #ffffff;
  padding: 12px 18px;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}

.toast-content {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 0.88rem;
  font-weight: 700;
}

/* RESPONSIVE */
@media (max-width: 820px) {
  .quotation-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}

@media (max-width: 600px) {
  .quotation-page {
    padding: 16px 12px 60px 12px;
  }

  .title-section {
    margin-bottom: 16px;
  }

  .main-title {
    font-size: 1.35rem;
  }

  .main-subtitle {
    font-size: 0.82rem;
  }

  .store-closed-checkout-banner {
    padding: 14px;
    border-radius: 14px;
    margin-bottom: 16px;
  }

  .closed-banner-left {
    gap: 10px;
  }

  .closed-text-box strong {
    font-size: 0.92rem;
  }

  .closed-text-box span {
    font-size: 0.82rem;
  }

  .form-card, 
  .summary-card {
    padding: 16px 14px;
    border-radius: 14px;
    margin-bottom: 14px;
  }

  .card-title {
    font-size: 0.92rem;
    margin-bottom: 12px;
  }

  .name-inputs-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .phone-prefix-tag {
    padding: 10px 14px;
    min-width: 64px;
    font-size: 0.9rem;
  }

  .phone-real-input {
    padding: 10px 14px;
    font-size: 0.95rem;
  }

  .total-display-box {
    padding: 12px 14px;
  }

  .total-label {
    font-size: 0.88rem;
  }

  .total-value {
    font-size: 1.25rem;
  }

  .btn-confirm-cotizacion {
    padding: 13px;
    font-size: 0.95rem;
  }
}

@media (max-width: 400px) {
  .payment-options-grid {
    gap: 6px;
  }

  .payment-option-card {
    padding: 10px 6px;
    gap: 4px;
  }

  .pay-title {
    font-size: 0.76rem;
  }

  .item-thumb {
    width: 44px;
    height: 44px;
  }

  .checkout-item-card {
    padding: 8px;
    gap: 8px;
  }
}
</style>