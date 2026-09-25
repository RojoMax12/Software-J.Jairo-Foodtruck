<template>
  <div class="status-page">
    <div class="tracker-layout">

      <!-- HERO / BUSCADOR -->
      <section class="tracker-hero-card">
        <div class="hero-badge">
          <UtensilsCrossed :size="14" />
          <span>Seguimiento en Vivo</span>
        </div>

        <h1 class="main-title">Rastrea tu Pedido</h1>
        <p class="subtitle">
          Ingresa tu número de comanda (ej: <strong>#1</strong>, <strong>#5</strong>) o N° de comprobante para ver el estado de tu comida en tiempo real.
        </p>

        <div class="search-bar-unified">
          <div class="search-input-wrap">
            <span class="prefix-hash">#</span>
            <input 
              v-model="orderId" 
              type="text" 
              placeholder="Ej: 1, 5, 36..." 
              class="tracker-input" 
              @keyup.enter="handleSearch"
            />
          </div>
          <button 
            class="btn-track-submit" 
            @click="handleSearch" 
            :disabled="isLoading"
          >
            <Search v-if="!isLoading" :size="16" />
            <RefreshCw v-else :size="16" class="spinning" />
            <span>{{ isLoading ? 'Buscando...' : 'Rastrear' }}</span>
          </button>
        </div>

        <div class="hero-footer-row">
          <button class="btn-return-menu" @click="router.push('/')">
            <ArrowLeft :size="14" />
            <span>Volver a la carta</span>
          </button>
          <span class="live-status-pill">
            <span class="pulse-dot"></span>
            Cocina Operativa
          </span>
        </div>

        <Transition name="fade">
          <div v-if="errorMessage" class="error-alert-box">
            <AlertTriangle :size="18" class="error-icon" />
            <span>{{ errorMessage }}</span>
          </div>
        </Transition>
      </section>

      <!-- TARJETA TICKET DE RESULTADO -->
      <Transition name="slide-up">
        <section v-if="orderResult" class="tracker-ticket-card">
          
          <!-- Encabezado del Ticket -->
          <div class="ticket-header">
            <div class="ticket-title-group">
              <span class="ticket-comanda-giant" v-if="orderResult.numero_pedido_dia">
                #{{ orderResult.numero_pedido_dia }}
              </span>
              <div class="ticket-meta">
                <span class="meta-label">Comprobante de compra</span>
                <h3 class="order-code">Pedido N° {{ String(orderResult.id).padStart(5, '0') }}</h3>
              </div>
            </div>

            <span class="status-badge-lg" :class="'badge-' + orderResult.currentStatus">
              {{ orderResult.statusLabel }}
            </span>
          </div>

          <!-- STEPPER / LÍNEA DE TIEMPO (SIN DESBORDES) -->
          <div class="timeline-stepper-box">
            <div 
              v-for="(step, index) in timelineSteps" 
              :key="step.id" 
              class="stepper-step"
              :class="{ 
                'active': isStepActive(step.id), 
                'completed': isStepCompleted(step.id) 
              }"
            >
              <div class="step-head">
                <div class="node-circle">
                  <component :is="step.icon" :size="16" />
                  <span class="pulse-ring" v-if="isStepActive(step.id)"></span>
                </div>
                <!-- Conector contenido dentro del paso (no se desborda en el último) -->
                <div v-if="index < timelineSteps.length - 1" class="node-line"></div>
              </div>
              <span class="node-label">{{ step.label }}</span>
            </div>
          </div>

          <!-- DATOS DEL CLIENTE -->
          <div class="ticket-client-grid">
            <div class="client-meta-card">
              <span class="meta-card-title">Cliente / Receptor</span>
              <strong class="meta-card-val" :title="orderResult.customerName">{{ orderResult.customerName }}</strong>
            </div>
            <div class="client-meta-card">
              <span class="meta-card-title">Teléfono</span>
              <strong class="meta-card-val">{{ orderResult.customerPhone }}</strong>
            </div>
            <div class="client-meta-card">
              <span class="meta-card-title">Método de Pago</span>
              <strong class="meta-card-val">{{ orderResult.customerMetododepago }}</strong>
            </div>
          </div>

          <!-- DETALLE DE PRODUCTOS -->
          <div class="ticket-items-box">
            <div class="items-header">
              <Utensils :size="14" />
              <span>Contenido de tu Comanda</span>
            </div>

            <ul class="items-list">
              <li v-for="(item, index) in orderResult.items" :key="index" class="item-row">
                <div class="item-qty-badge">{{ item.quantity }}x</div>
                <div class="item-info">
                  <strong class="item-name">{{ item.name }}</strong>
                  
                  <div v-if="(item.excluidos && item.excluidos.length > 0) || (item.agregados && item.agregados.length > 0)" class="item-customizations">
                    <span v-for="ex in item.excluidos" :key="ex" class="tag-exclusion">
                      Sin {{ ex }}
                    </span>
                    <span v-for="ag in item.agregados" :key="ag" class="tag-extra">
                      + {{ ag }}
                    </span>
                  </div>
                </div>
              </li>
            </ul>
          </div>

        </section>
      </Transition>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { 
  Clock, ChefHat, CheckCircle, PackageCheck, UtensilsCrossed, 
  Search, ArrowLeft, RefreshCw, AlertTriangle, Utensils 
} from 'lucide-vue-next';
import orderService from '@/services/orderService';

const router = useRouter();
const route = useRoute();
const orderId = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const orderResult = ref<any>(null);

onMounted(() => {
  const queryId = route.query.id;
  if (queryId) {
    const rawVal = Array.isArray(queryId) ? queryId[0] : String(queryId);
    if (rawVal) {
      orderId.value = rawVal;
      handleSearch();
    }
  }
});

const timelineSteps = [
  { id: 'en_cola', label: 'En Cola', icon: Clock },
  { id: 'preparacion', label: 'Cocinando', icon: ChefHat },
  { id: 'listo', label: 'Listo', icon: CheckCircle },
  { id: 'entregado', label: 'Entregado', icon: PackageCheck }
];

const isStepCompleted = (stepId: string) => {
  if (!orderResult.value) return false;
  const statusOrder = timelineSteps.map(s => s.id);
  const currentIndex = statusOrder.indexOf(orderResult.value.currentStatus);
  const stepIndex = statusOrder.indexOf(stepId);
  return stepIndex < currentIndex;
};

const isStepActive = (stepId: string) => {
  if (!orderResult.value) return false;
  return orderResult.value.currentStatus === stepId;
};

const handleSearch = async () => {
  errorMessage.value = '';
  orderResult.value = null;

  const rawInput = orderId.value.trim();
  if (!rawInput) {
    errorMessage.value = 'Ingresa el número de tu comanda (ej: #1 o #4).';
    return;
  }

  const cleanQuery = rawInput.replace(/^#/, '');
  isLoading.value = true;

  try {
    let data: any = null;

    try {
      const responseComanda = await orderService.getOrderByComanda(cleanQuery);
      data = responseComanda?.data?.data || responseComanda?.data;
    } catch (errComanda: any) {
      try {
        const responseId = await orderService.getPublicOrderById(cleanQuery);
        data = responseId?.data?.data || responseId?.data;
      } catch (errId: any) {
        const serverMsg = errComanda?.response?.data?.message || errId?.response?.data?.message;
        errorMessage.value = serverMsg || `No encontramos el pedido #${cleanQuery} en el turno actual.`;
        return;
      }
    }

    if (!data) {
      errorMessage.value = `No encontramos el pedido #${cleanQuery} en el turno actual.`;
      return;
    }

    const statusId = Number(data.id_estado_pedido || data.estado_id || 1);

    const statusStepMap: Record<number, string> = {
      1: 'en_cola',
      2: 'preparacion',
      3: 'listo',
      4: 'entregado'
    };

    const statusName = data.estado_pedido?.nombre || (
      statusId === 1 ? 'Pendiente' :
      statusId === 2 ? 'En preparación' :
      statusId === 3 ? 'Listo' :
      statusId === 4 ? 'Entregado' : 'Cancelado'
    );

    const itemsMapped = (data.detalles || []).map((det: any) => {
      const prodName = det.producto?.nombre || det.nombre_producto || 'Producto';
      const sizeName = det.tamano?.nombre_tamaño || det.tamaño?.nombre_tamaño || '';

      let excluidosList: string[] = [];
      let agregadosList: string[] = [];

      if (Array.isArray(det.ingredientes)) {
        det.ingredientes.forEach((ing: any) => {
          const name = ing.ingrediente?.nombre || ing.nombre || '';
          const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase();
          if (tipo.includes('exclu') || tipo.includes('quit') || tipo.includes('sin')) {
            if (name) excluidosList.push(name);
          } else {
            if (name) agregadosList.push(name);
          }
        });
      }

      return {
        quantity: det.cantidad || 1,
        name: sizeName && sizeName !== 'Único' ? `${prodName} (${sizeName})` : prodName,
        excluidos: [...new Set(excluidosList)],
        agregados: [...new Set(agregadosList)]
      };
    });

    orderResult.value = {
      id: data.id_pedido || data.id,
      numero_pedido_dia: data.numero_pedido_dia || null,
      customerName: data.nombre_persona || 'Cliente',
      customerPhone: data.numero_telefono || 'Sin teléfono',
      customerMetododepago: data.metodo_pago || 'Efectivo',
      currentStatus: statusStepMap[statusId] || 'en_cola',
      statusLabel: statusName,
      items: itemsMapped
    };
  } catch (error: any) {
    console.error('Error al buscar pedido:', error);
    const serverMsg = error?.response?.data?.message;
    errorMessage.value = serverMsg || `No encontramos el pedido #${cleanQuery} en el turno actual.`;
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.status-page {
  background: var(--DC-bg-gray, #f8f6f3);
  min-height: 100vh;
  width: 100%;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  padding: 2.5rem 1rem 4rem;
  overflow-x: hidden;
}

.tracker-layout {
  width: 100%;
  max-width: 620px;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  min-width: 0;
}

/* ====================================================
   HERO / BUSCADOR
==================================================== */
.tracker-hero-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 1.75rem 1.5rem;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 8px 24px rgba(26, 14, 5, 0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  width: 100%;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 0.85rem;
}

.main-title {
  color: var(--DC-brown, #513119);
  font-size: 1.85rem;
  font-weight: 900;
  line-height: 1.15;
  margin: 0 0 0.4rem 0;
  overflow-wrap: anywhere;
}

.subtitle {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.88rem;
  line-height: 1.45;
  margin: 0 0 1.5rem 0;
  max-width: 480px;
}

.search-bar-unified {
  width: 100%;
  display: flex;
  align-items: center;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  border-radius: 14px;
  padding: 0.3rem 0.35rem 0.3rem 0.85rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-bar-unified:focus-within {
  background: #ffffff;
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.search-input-wrap {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 4px;
  min-width: 0;
}

.prefix-hash {
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.tracker-input {
  width: 100%;
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
  font-family: inherit;
  min-width: 0;
}

.btn-track-submit {
  border: none;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  padding: 0.65rem 1.25rem;
  border-radius: 10px;
  font-size: 0.88rem;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  transition: all 0.2s ease;
  flex-shrink: 0;
  white-space: nowrap;
}

.btn-track-submit:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
}

.btn-track-submit:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.hero-footer-row {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 1.15rem;
  padding-top: 0.85rem;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
  font-size: 0.8rem;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.btn-return-menu {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  padding: 0;
}

.btn-return-menu:hover {
  color: var(--DC-brown, #513119);
}

.live-status-pill {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-weight: 800;
  color: #15803d;
  font-size: 0.74rem;
  white-space: nowrap;
}

.pulse-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 rgba(22, 163, 74, 0.7);
  animation: pulse-dot 1.8s infinite;
  flex-shrink: 0;
}

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.error-alert-box {
  width: 100%;
  background: #fff5f5;
  border: 1px solid #fed7d7;
  color: #c53030;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-size: 0.84rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1rem;
  text-align: left;
  overflow-wrap: anywhere;
}

.error-icon {
  flex-shrink: 0;
}

/* ====================================================
   TARJETA TICKET DE RESULTADO
==================================================== */
.tracker-ticket-card {
  background: #ffffff;
  border-radius: 20px;
  border: 1px solid rgba(81, 49, 25, 0.1);
  box-shadow: 0 12px 32px rgba(26, 14, 5, 0.06);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  width: 100%;
}

.ticket-header {
  padding: 1.25rem 1.5rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.ticket-title-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 0;
}

.ticket-comanda-giant {
  font-size: 1.4rem;
  font-weight: 900;
  color: #ffffff;
  background: var(--DC-brown, #513119);
  padding: 0.2rem 0.75rem;
  border-radius: 10px;
  line-height: 1.1;
  flex-shrink: 0;
}

.ticket-meta {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.meta-label {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--DC-text-gray, #7c7468);
}

.order-code {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--DC-gray, #2c2724);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.status-badge-lg {
  padding: 0.4rem 1rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  white-space: nowrap;
}

.badge-en_cola { background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2; }
.badge-preparacion { background: rgba(226, 135, 67, 0.15); color: var(--DC-orange, #e28743); border: 1px solid rgba(226, 135, 67, 0.3); }
.badge-listo { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
.badge-entregado { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }

/* ====================================================
   LÍNEA DE TIEMPO / STEPPER PROTEGIDO CONTRA DESBORDE
==================================================== */
.timeline-stepper-box {
  display: flex;
  align-items: flex-start;
  padding: 1.5rem 1.25rem 1.25rem;
  background: #ffffff;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  width: 100%;
}

.stepper-step {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-width: 0;
}

/* El último paso no expande barra */
.stepper-step:last-child {
  flex: 0 0 auto;
  min-width: 60px;
}

.step-head {
  display: flex;
  align-items: center;
  width: 100%;
  position: relative;
}

.node-circle {
  position: relative;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 2px solid #e7dfd5;
  color: #a89f95;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  z-index: 2;
  transition: all 0.25s ease;
}

.node-line {
  flex: 1;
  height: 3px;
  background: #ede6dc;
  transition: background 0.25s ease;
  margin: 0 -2px; /* Superposición limpia con el borde */
}

.node-label {
  font-size: 0.72rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.02em;
  color: var(--DC-text-gray, #7c7468);
  margin-top: 0.45rem;
  text-align: center;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  max-width: 100%;
}

/* Estados completados y activos */
.stepper-step.completed .node-circle {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.stepper-step.completed .node-line {
  background: var(--DC-orange, #e28743);
}

.stepper-step.completed .node-label {
  color: var(--DC-brown, #513119);
}

.stepper-step.active .node-circle {
  background: #ffffff;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  box-shadow: 0 2px 10px rgba(226, 135, 67, 0.3);
}

.stepper-step.active .node-label {
  color: var(--DC-orange, #e28743);
  font-weight: 900;
}

.pulse-ring {
  position: absolute;
  inset: -5px;
  border-radius: 50%;
  border: 2px solid var(--DC-orange, #e28743);
  opacity: 0.6;
  animation: pulse-ring 1.8s infinite;
}

@keyframes pulse-ring {
  0% { transform: scale(0.9); opacity: 0.8; }
  100% { transform: scale(1.25); opacity: 0; }
}

/* ====================================================
   DATOS DEL CLIENTE (MALLA CON WRAP)
==================================================== */
.ticket-client-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.65rem;
  padding: 1rem 1.5rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.client-meta-card {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.06);
  border-radius: 12px;
  padding: 0.55rem 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.meta-card-title {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: var(--DC-text-gray, #7c7468);
}

.meta-card-val {
  font-size: 0.86rem;
  color: var(--DC-gray, #2c2724);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* ====================================================
   DETALLE DE PRODUCTOS
==================================================== */
.ticket-items-box {
  padding: 1.25rem 1.5rem;
  background: #ffffff;
}

.items-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.78rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--DC-brown, #513119);
  margin-bottom: 0.85rem;
}

.items-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.item-row {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding-bottom: 0.75rem;
  border-bottom: 1px dashed rgba(81, 49, 25, 0.1);
  min-width: 0;
}

.item-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.item-qty-badge {
  font-size: 0.82rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  background: rgba(226, 135, 67, 0.12);
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  line-height: 1;
  flex-shrink: 0;
}

.item-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  flex: 1;
  min-width: 0;
}

.item-name {
  font-size: 0.92rem;
  color: var(--DC-gray, #2c2724);
  overflow-wrap: anywhere;
}

.item-customizations {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.tag-exclusion {
  font-size: 0.7rem;
  font-weight: 800;
  background: #fee2e2;
  color: #dc2626;
  padding: 1px 6px;
  border-radius: 4px;
}

.tag-extra {
  font-size: 0.7rem;
  font-weight: 800;
  background: #dbeafe;
  color: #1d4ed8;
  padding: 1px 6px;
  border-radius: 4px;
}

/* Animaciones */
.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  opacity: 0;
  transform: translateY(12px);
}

/* ====================================================
   RESPONSIVO CELULAR (MENOR A 560px)
==================================================== */
@media (max-width: 560px) {
  .status-page {
    padding: 1.5rem 0.75rem 3rem;
  }

  .tracker-hero-card {
    padding: 1.5rem 1rem;
  }

  .main-title {
    font-size: 1.5rem;
  }

  .search-bar-unified {
    flex-direction: column;
    padding: 0.5rem;
    gap: 0.5rem;
  }

  .search-input-wrap {
    width: 100%;
    padding: 0 0.25rem;
  }

  .btn-track-submit {
    width: 100%;
    justify-content: center;
    padding: 0.75rem;
  }

  .ticket-header {
    padding: 1rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.65rem;
  }

  .ticket-client-grid {
    grid-template-columns: 1fr;
    padding: 0.85rem 1rem;
  }

  .timeline-stepper-box {
    padding: 1.25rem 0.75rem;
  }

  .node-circle {
    width: 32px;
    height: 32px;
  }

  .node-label {
    font-size: 0.62rem;
  }

  .ticket-items-box {
    padding: 1rem;
  }
}
</style>