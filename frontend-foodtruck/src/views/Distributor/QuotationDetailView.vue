<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { FileSearch, CheckCircle2, IceCream } from 'lucide-vue-next'
import quoteService from '@/services/quoteService'
import orderService from '@/services/orderService'
import userService from '@/services/userService'
import distributorService from '@/services/distributorService'
import quoteProductService from '@/services/quoteProductService'
import productService from '@/services/productService'
import productCategoryService from '@/services/productCategoryService' 
import boxPlaceholderImage from '@/assets/logo_jairo.webp'

const route = useRoute()
const router = useRouter()

// --- ESTADOS REACTIVOS ---
const quotationData = ref<any>(null)
const distributorData = ref<any>(null)
const usuarioDicremeData = ref<any>(null)
const productsData = ref<any[]>([]) 
const isLoading = ref(true)
const errorMessage = ref('')

// Fallbacks de seguridad basados en la sesión activa por si algún campo viene null
const fallbackCompany = ref('Distribuidor Di Creme')
const fallbackAddress = ref('Dirección registrada')
const fallbackPhone = ref('No registrado')
const fallbackEmail = ref('No registrado')
const fallbackName = ref('Recibidor')
const fallbackComuna = ref('Comuna Registrada')

// Captura el ID de la cotización directo desde los parámetros de la URL
const quotationId = computed(() => Number(route.params.id))

// CONTROL DE ESTADOS DE LA LÍNEA DE TIEMPO (Paso 1 o Paso 2)
const currentStep = computed(() => {
  if (!quotationData.value) return 1
  
  // Intenta obtener el ID desde distintas propiedades comunes por seguridad
  const rawStatus = quotationData.value.id_estado_cotizacion 
                 || quotationData.value.estado_id 
                 || quotationData.value.id_estado;
                 
  const statusId = Number(rawStatus);

  // Si el estado es 3 (Completado), vamos al paso 2. Si no, nos quedamos en el 1.
  return statusId === 3 ? 2 : 1
})

const abrirWhatsapp = () => {
  // Aquí sí puedes usar window con total libertad porque estamos en JavaScript puro
  window.open('https://wa.me/56977579783', '_blank');
};

// --- CARGA DE DATOS EN CADENA Y PARALELO ---
onMounted(async () => {
  if (!quotationId.value) {
    errorMessage.value = 'ID de cotización no válido.'
    isLoading.value = false
    return
  }

  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      fallbackCompany.value = userObj.nombre_empresa || 'Distribuidor Di Creme'
      fallbackAddress.value = userObj.direccion || 'Dirección registrada'
      fallbackPhone.value = userObj.telefono || 'No registrado'
      fallbackEmail.value = userObj.email || userObj.correo_electronico || 'No registrado'
      fallbackName.value = userObj.nombre ? `${userObj.nombre} ${userObj.apellido || ''}` : 'No registrado'
      fallbackComuna.value = userObj.comuna || userObj.nombre_comuna || 'Comuna Registrada'
    } catch (e) {
      console.error('Error parsing user session storage fallback:', e)
    }
  }

  try {
    isLoading.value = true

    // llamada a la API
    let response: any = null;
    try {
      response = await quoteService.getQuoteDetails(quotationId.value);
    } catch (e) {
      try {
        response = await orderService.getPublicOrderById(quotationId.value);
      } catch (e2) {
        response = await orderService.getOrderById(quotationId.value);
      }
    }

    const payload = response?.data?.data || response?.data;

    if (payload) {
      quotationData.value = payload;

      distributorData.value = payload.distribuidor || payload.usuario || {};

      const parsePrice = (val: any): number => {
        if (typeof val === 'number') return isNaN(val) ? 0 : val;
        if (typeof val === 'string') {
          const cleaned = val.replace(/[^0-9.-]/g, '');
          const num = parseFloat(cleaned);
          return isNaN(num) ? 0 : num;
        }
        return 0;
      };

      const rawProds = payload.productos || payload.detalles || payload.items || [];
      productsData.value = rawProds.map((prod: any) => {
        const p = prod.producto || prod;
        const pName = p.nombre || p.name || prod.nombre_producto || prod.nombre || 'Producto';
        const pSize = prod.tamaño?.nombre || prod.tamano?.nombre || p.formato || prod.formato || '';
        const pCat = p.categoria?.nombre_categoria || p.categoria?.nombre || (typeof p.categoria === 'string' ? p.categoria : '');
        const pQty = parsePrice(prod.cantidad ?? prod.quantity ?? 1) || 1;
        
        let pPrice = parsePrice(
          prod.precio_unitario ?? 
          prod.precio_unitario_venta ?? 
          prod.precio ?? 
          p.precio_unitario ?? 
          p.precio_base ?? 
          p.precio ?? 
          0
        );

        let subtotal = parsePrice(prod.subtotal);
        if (subtotal === 0 && pPrice > 0) {
          subtotal = pPrice * pQty;
        } else if (pPrice === 0 && subtotal > 0) {
          pPrice = Math.round(subtotal / pQty);
        }

        let excluidosList: string[] = [];
        if (Array.isArray(prod.ingredientes)) {
          excluidosList = prod.ingredientes
            .filter((ing: any) => {
              const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase();
              return tipo.includes('exclu') || tipo.includes('quit');
            })
            .map((ing: any) => ing.ingrediente?.nombre || ing.nombre || (typeof ing === 'string' ? ing : ''))
            .filter(Boolean);
        } else if (Array.isArray(prod.ingredientes_excluidos)) {
          excluidosList = prod.ingredientes_excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean);
        } else if (Array.isArray(prod.excluidos)) {
          excluidosList = prod.excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean);
        }

        return {
          cantidad: pQty,
          precio_unitario_venta: pPrice,
          subtotal: subtotal,
          excluidos: excluidosList,
          producto: {
            name: pName,
            categoria: pCat,
            formato: pSize,
            precio: pPrice,
            image: p.imagen || p.image || prod.imagen || prod.image || boxPlaceholderImage
          }
        };
      });
    } else {
      errorMessage.value = 'No se encontraron los detalles de la cotización.';
    }

  } catch (error) {
    console.error('Error fetching quotation details:', error);
    errorMessage.value = 'Hubo un problema al conectar con el servidor.';
  } finally {
    isLoading.value = false;
  }
});

const getQuoteStatusLabel = (statusId: number): string => {
  const safeId = Number(statusId)
  if (safeId === 1) return 'En revisión'
  if (safeId === 3) return 'Completado'
  return 'En proceso'
}

const formatCurrency = (value: number) => {
  const safeValue = value ? Number(value) : 0
  return `$${safeValue.toLocaleString('es-CL')}`
}

const handleGoBack = () => {
  router.push('/mis-cotizaciones')
}
</script>

<template>
  <div class="quotation-detail-page">

    <main class="detail-container">
      <div class="title-section">
        <h2 class="main-title">Resumen Cotización N° {{ String(quotationId).padStart(6, '0') }}</h2>
        <div class="title-line"></div>
      </div>

      <div class="state-box" v-if="isLoading">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Cargando el estado de tu cotización...</p>
      </div>
      
      <div class="state-box error" v-else-if="errorMessage">{{ errorMessage }}</div>
      
      <div class="detail-grid" v-else-if="quotationData">
        
        <section class="info-column">
          <h3 class="section-subtitle">Datos de contacto:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Teléfono:</strong> {{ distributorData?.telefono || fallbackPhone }}</p>
            <p class="info-text"><strong>Correo:</strong> {{ distributorData?.email || distributorData?.correo_electronico || fallbackEmail }}</p>
          </div>

          <h3 class="section-subtitle" style="margin-top: 25px;">Datos de Entrega:</h3>
          <div class="info-card-block">
            <p class="info-text"><strong>Nombre y Apellido:</strong> {{ quotationData?.persona_recibe || fallbackName }}</p>
            <p class="info-text"><strong>Empresa:</strong> {{ distributorData?.nombre_empresa || fallbackCompany }}</p>
            <p class="info-text"><strong>Rut empresa:</strong> {{ distributorData?.rut_empresa || distributorData?.rut || 'N/A' }}</p>
            <p class="info-text"><strong>Dirección:</strong> {{ distributorData?.direccion || fallbackAddress }}</p>
            <p class="info-text"><strong>Comuna:</strong> {{ distributorData?.comuna || fallbackComuna }}</p>
          </div>

          <div class="amount-group">
            <div class="amount-row highlighted">
              <span class="amount-label">Monto Total:</span>
              <div class="amount-box-pink">
                {{ formatCurrency(quotationData?.total_cotizacion ?? quotationData?.total ?? quotationData?.monto_final ?? productsData.reduce((acc, p) => acc + (p.subtotal || 0), 0)) }}
              </div>
            </div>
          </div>
        </section>

        <section class="summary-column">
          <h3 class="section-subtitle">Detalle productos:</h3>
          
          <div class="products-box-container">
            <div class="empty-products-state" v-if="productsData.length === 0">
              Cargando el detalle de los productos...
            </div>

            <div 
              class="product-item-card"
              v-else
              v-for="(item, index) in productsData" 
              :key="index" 
            >
              <img :src="item.producto?.image ?? item.producto?.imagen ?? boxPlaceholderImage" class="item-thumb" />
              
              <div class="item-info">
                <div class="item-header-row">
                  <span class="item-name">
                    {{ item.producto?.name ?? 'Producto' }}
                    <span v-if="item.producto?.formato" class="item-size">({{ item.producto?.formato }})</span>
                  </span>
                  <span class="item-qty">x{{ item.cantidad }}</span>
                </div>
                
                <span v-if="item.producto?.categoria" class="item-tag">
                  {{ item.producto?.categoria }}
                </span>

                <div v-if="item.excluidos && item.excluidos.length > 0" class="product-exclusions">
                  <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                    Sin {{ ing }}
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
                <FileSearch v-if="currentStep === 1" :size="24" color="white" />
                <CheckCircle2 v-else :size="24" color="white" />
              </div>
            </div>

            <div class="timeline-bar">
              <div class="timeline-progress-bar" :class="'progress-fill-' + currentStep"></div>
            </div>

            <div class="timeline-nodes-row">
              <div class="timeline-node" :class="{ active: currentStep >= 1 }">
                <div class="node-dot"></div>
                <span class="node-text">En revisión</span>
              </div>
              <div class="timeline-node" :class="{ active: currentStep === 2 }">
                <div class="node-dot"></div>
                <span class="node-text">Completado</span>
              </div>
            </div>
          </div>

          <div class="status-display-box">
            Estado de la cotización: <span class="capitalize-text">{{ quotationData?.estado_nombre ?? getQuoteStatusLabel(quotationData?.id_estado_cotizacion) }}</span>
          </div>

          <div class="action-row">
            
            <button class="btn-contact" style="margin-left: 15px;" @click="abrirWhatsapp">
              <span>Contáctanos</span>
              <div class="icon-whatsapp"> <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="#25D366"> <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.477-1.761-1.65-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.346.446-.52.149-.174.199-.298.298-.497.1-.198.05-.372-.025-.521-.075-.148-.675-1.628-.925-2.228-.243-.588-.495-.508-.675-.515-.174-.007-.374-.008-.573-.008-.199 0-.521.074-.794.372-.273.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.174-1.413-.074-.124-.273-.198-.57-.347z"/>
                  <path d="M12 0C5.373 0 0 5.373 0 12c0 2.113.548 4.16 1.574 5.96L0 24l6.198-1.576A11.95 11.95 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22.119c-1.805 0-3.57-.484-5.116-1.405l-.367-.217-3.8.968.995-3.674-.24-.38a9.92 9.92 0 0 1-1.52-5.323c0-5.518 4.485-10.003 10.003-10.003 5.518 0 10.002 4.485 10.002 10.003 0 5.517-4.484 10.002-10.002 10.002z"/>
                </svg>
              </div>
            </button>

            <button class="btn-return-back" @click="handleGoBack">
              Volver a mis cotizaciones
            </button>

            
          </div>
        </section>

      </div>
    </main>
  </div>
</template>

<style scoped>
.cotizacion-detail-page {
  background-color: var(--DC-bg-gray, #f5ebe0);
  min-height: 100vh;
  font-family: var(--font-main, sans-serif);
  padding: 30px 16px 80px 16px;
  box-sizing: border-box;
}

.detail-container {
  max-width: 1000px;
  margin: 0 auto;
}

.title-section { margin-bottom: 24px; }
.main-title { font-size: 1.6rem; font-weight: 900; color: var(--DC-brown, #513119); margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: var(--DC-orange, #e28743); width: 100%; }

.detail-grid {
  display: grid;
  grid-template-columns: 1.15fr 1fr;
  gap: 30px;
}

.section-subtitle { font-size: 1rem; font-weight: 800; color: var(--DC-brown, #513119); margin: 0 0 14px 0; text-align: left; }

.info-card-block {
  background-color: white;
  border-radius: 18px;
  padding: 20px 24px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  display: flex;
  flex-direction: column;
  gap: 10px;
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.04);
}

.info-text {
  margin: 0;
  font-size: 0.9rem;
  color: var(--DC-gray, #322c44);
  text-align: left;
  line-height: 1.4;
}

.amount-group {
  display: flex;
  flex-direction: column;
  gap: 15px;
  margin-top: 20px;
}

.amount-row {
  display: flex;
  flex-direction: column;
  gap: 6px;
  text-align: left;
}

.amount-row.highlighted .amount-box-pink {
  background-color: var(--DC-brown, #513119);
  border: none;
  border-radius: 14px;
  padding: 14px 20px;
  font-size: 1.3rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  text-align: center;
}

.products-box-container {
  background-color: white;
  border-radius: 18px;
  padding: 16px;
  max-height: 280px;
  overflow-y: auto;
  border: 1px solid rgba(81, 49, 25, 0.12);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.empty-products-state {
  color: var(--DC-text-gray, #6e6a75);
  font-size: 0.9rem;
  margin: auto;
  font-style: italic;
}

.product-item-card {
  display: flex;
  gap: 12px;
  background-color: #fdfbf8;
  padding: 10px 12px;
  border-radius: 12px;
  align-items: flex-start;
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.item-thumb { width: 52px; height: 52px; object-fit: cover; border-radius: 10px; flex-shrink: 0; }
.item-info { flex: 1; display: flex; flex-direction: column; text-align: left; }
.item-header-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
.item-name { font-size: 0.9rem; font-weight: 800; color: var(--DC-brown, #513119); line-height: 1.2; }
.item-size { font-size: 0.78rem; color: var(--DC-text-gray, #6e6a75); font-weight: 600; margin-left: 4px; }
.item-qty { font-size: 0.76rem; font-weight: 800; color: var(--button-text, #513119); background: var(--button-color, #F4E1D2); padding: 1px 6px; border-radius: 6px; white-space: nowrap; }
.item-tag { font-size: 0.74rem; font-weight: 700; margin-top: 2px; color: var(--DC-orange, #e28743); }
.product-exclusions { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; margin-bottom: 2px; }
.exclusion-badge { background-color: #fee2e2; color: #dc2626; font-size: 0.7rem; font-weight: 800; padding: 1px 6px; border-radius: 6px; display: inline-flex; align-items: center; }
.item-meta-row { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; font-size: 0.8rem; border-top: 1px dashed rgba(81, 49, 25, 0.12); padding-top: 4px; }
.item-spec { font-size: 0.8rem; font-weight: 600; color: var(--DC-text-gray, #6e6a75); }
.item-subtotal { font-size: 0.88rem; font-weight: 800; color: var(--DC-brown, #513119); }

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
.progress-fill-2 { width: 100%; }

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
  width: 80px;
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

.step-active-1 { 
  left: 35px; 
}
.step-active-2 { 
  left: calc(100% - 35px); 
}

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
  background-color: #fdfaf6;
  border: 1.5px solid rgba(226, 135, 67, 0.3);
  border-radius: 12px;
  padding: 12px 16px;
  font-size: 1rem;
  font-weight: bold;
  color: var(--DC-brown, #513119);
  margin-top: 18px;
  text-align: left;
}

.action-row {
  display: flex;
  justify-content: space-between;
  margin-top: 24px;
  gap: 12px;
}

.btn-return-back {
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

.btn-contact {
  background-color: #25D366;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.state-box { background: white; padding: 50px; border-radius: 18px; text-align: center; color: var(--DC-text-gray, #6e6a75); font-weight: 600; }
.state-box.error { color: #dc2626; }
.spinner { animation: rotate 2s linear infinite; margin-bottom: 10px; }
@keyframes rotate { 100% { transform: rotate(360deg); } }

@media (max-width: 768px) {
  .detail-grid {
    grid-template-columns: 1fr;
  }

  .action-row {
    flex-direction: column-reverse;
  }

  .btn-return-back,
  .btn-contact {
    width: 100%;
  }
}
</style>