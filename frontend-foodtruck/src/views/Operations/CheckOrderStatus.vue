<template>
  <div class="status-page">
    <div class="box">
      <div class="header-icon-box">
        <UtensilsCrossed :size="32" />
      </div>
      <h1 class="main-title">Rastrea tu Pedido</h1>
      <p class="subtitle">
        Ingresa tu número de comanda (ej: <strong>#1</strong>, <strong>#5</strong>) o N° de comprobante para ver el estado de tu comida en la jornada de hoy.
      </p>

      <div class="search-section">
        <input 
          v-model="orderId" 
          type="text" 
          placeholder="Ej: #1 o 36" 
          class="dc-input" 
          @keyup.enter="handleSearch"
        />
        <button 
          class="btn-search" 
          @click="handleSearch" 
          :disabled="isLoading"
        >
          {{ isLoading ? 'Buscando...' : 'Buscar' }}
        </button>
      </div>

      <button class="btn-home" @click="router.push('/')">
        Volver a la carta
      </button>

      <Transition name="fade">
        <div v-if="errorMessage" class="error-alert">
          {{ errorMessage }}
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="orderResult" class="result-card">
          
          <div class="result-header">
            <div class="order-title-box">
              <span class="order-comanda-badge" v-if="orderResult.numero_pedido_dia">
                #{{ orderResult.numero_pedido_dia }}
              </span>
              <h3 class="order-number">Pedido N° {{ String(orderResult.id).padStart(5, '0') }}</h3>
            </div>

            <span class="status-badge" :class="'badge-' + orderResult.currentStatus">
              {{ orderResult.statusLabel }}
            </span>
          </div>

          <div class="customer-info">
            <div class="info-row">
              <span class="info-label">Receptor:</span>
              <span class="info-value">{{ orderResult.customerName }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Teléfono:</span>
              <span class="info-value">{{ orderResult.customerPhone }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Método de pago:</span>
              <span class="info-value">{{ orderResult.customerMetododepago }}</span>
            </div>
          </div>

          <div class="timeline-container">
            <div 
              v-for="(step, index) in timelineSteps" 
              :key="step.id" 
              class="timeline-step"
              :class="{ 
                'active': isStepActive(step.id), 
                'completed': isStepCompleted(step.id) 
              }"
            >
              <div class="step-icon">
                <component :is="step.icon" :size="18" />
              </div>
              <span class="step-label">{{ step.label }}</span>
              <div v-if="index < timelineSteps.length - 1" class="step-line"></div>
            </div>
          </div>

          <div class="products-summary">
            <h4 class="summary-title">Detalle del Pedido:</h4>
            <ul class="products-list">
              <li v-for="(item, index) in orderResult.items" :key="index" class="product-item">
                <span class="product-qty">{{ item.quantity }}x</span>
                <div class="product-details">
                  <span class="product-name">{{ item.name }}</span>
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="product-exclusions">
                    <span v-for="ex in item.excluidos" :key="ex" class="exclusion-tag">Sin {{ ex }}</span>
                  </div>
                  <div v-if="item.agregados && item.agregados.length > 0" class="product-exclusions">
                    <span v-for="ag in item.agregados" :key="ag" class="extra-tag">+ {{ ag }}</span>
                  </div>
                </div>
              </li>
            </ul>
          </div>

        </div>
      </Transition>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Clock, ChefHat, CheckCircle, PackageCheck, UtensilsCrossed } from 'lucide-vue-next';
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

// Definición estricta de la línea de tiempo
const timelineSteps = [
  { id: 'en_cola', label: 'Pendiente', icon: Clock },
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

// Búsqueda estricta por horario de atención y comanda del turno
const handleSearch = async () => {
  errorMessage.value = '';
  orderResult.value = null;

  const rawInput = orderId.value.trim();
  if (!rawInput) {
    errorMessage.value = 'Por favor, ingresa el número de tu comanda (ej: #1 o #4).';
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
        errorMessage.value = serverMsg || `No encontramos el pedido #${cleanQuery} en la jornada de atención actual.`;
        return;
      }
    }

    if (!data) {
      errorMessage.value = `No encontramos el pedido #${cleanQuery} en la jornada de atención actual.`;
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
    errorMessage.value = serverMsg || `No encontramos el pedido #${cleanQuery} en la jornada de atención actual.`;
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.status-page {
  background-color: var(--DC-bg-gray, #f5ebe0); 
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 30px 16px;
  font-family: var(--font-main, sans-serif);
  box-sizing: border-box;
}

.box {
  background-color: #ffffff;
  width: 100%;
  max-width: 580px; 
  border-radius: 24px;
  padding: 40px 30px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 10px 40px rgba(81, 49, 25, 0.06);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.header-icon-box {
  width: 64px;
  height: 64px;
  border-radius: 20px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 16px;
}

.main-title { 
  color: var(--DC-brown, #513119); 
  font-size: 1.7rem; 
  font-weight: 900; 
  margin: 0 0 6px 0; 
  text-align: center;
}

.subtitle { 
  color: var(--DC-text-gray, #6e6a75); 
  font-size: 0.9rem; 
  margin: 0 0 24px 0; 
  text-align: center;
  line-height: 1.45;
  max-width: 440px;
}

/* Buscador */
.search-section { 
  display: flex; 
  gap: 10px; 
  margin-bottom: 14px; 
  width: 100%;
}

.dc-input { 
  flex: 1; 
  padding: 13px 18px; 
  border: 1.5px solid rgba(81, 49, 25, 0.18); 
  border-radius: 12px; 
  font-size: 0.95rem; 
  font-weight: 700; 
  color: var(--DC-gray, #322c44); 
  outline: none; 
  transition: all 0.2s; 
  font-family: inherit;
}

.dc-input:focus { 
  border-color: var(--DC-orange, #e28743); 
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.btn-search { 
  background-color: var(--DC-orange, #e28743); 
  color: white; 
  border: none; 
  padding: 0 24px; 
  border-radius: 12px; 
  font-weight: 800; 
  font-size: 0.95rem; 
  cursor: pointer; 
  transition: all 0.2s; 
  box-shadow: 0 4px 12px rgba(226, 135, 67, 0.25);
}

.btn-search:hover:not(:disabled) { 
  background-color: var(--DC-brown, #513119); 
  transform: translateY(-1px); 
}

.btn-search:disabled { 
  opacity: 0.7; 
  cursor: not-allowed; 
}

.btn-home { 
  width: 100%; 
  background-color: var(--button-color, #F4E1D2); 
  color: var(--button-text, #513119); 
  border: 1px solid rgba(81, 49, 25, 0.15); 
  padding: 11px 16px; 
  border-radius: 12px; 
  font-weight: 800; 
  font-size: 0.88rem;
  cursor: pointer; 
  transition: all 0.2s; 
}

.btn-home:hover { 
  background-color: var(--DC-orange, #e28743); 
  color: #ffffff;
}

.error-alert { 
  width: 100%;
  background-color: #fee2e2; 
  color: #dc2626; 
  padding: 12px 16px; 
  border-radius: 12px; 
  font-size: 0.88rem; 
  font-weight: 700; 
  border: 1px solid #fecaca; 
  text-align: center; 
  margin-top: 14px;
  box-sizing: border-box;
}

/* Tarjeta de Resultado */
.result-card {
  margin-top: 24px;
  border-radius: 18px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background-color: #ffffff;
  overflow: hidden;
  width: 100%;
  box-shadow: 0 4px 16px rgba(81, 49, 25, 0.04);
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
  background-color: #fdfaf6;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.order-title-box {
  display: flex;
  align-items: center;
  gap: 8px;
}

.order-comanda-badge {
  background: var(--DC-brown, #513119);
  color: #ffffff;
  padding: 2px 8px;
  border-radius: 6px;
  font-weight: 800;
  font-size: 0.82rem;
}

.order-number { 
  margin: 0; 
  font-size: 1.05rem; 
  color: var(--DC-brown, #513119); 
  font-weight: 900; 
}

.status-badge { 
  padding: 4px 12px; 
  border-radius: 20px; 
  font-weight: 800; 
  font-size: 0.78rem; 
  text-transform: uppercase; 
}

.badge-en_cola { background-color: #fffbeb; color: #b45309; border: 1px solid #fef3c7; }
.badge-preparacion { background-color: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
.badge-listo { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
.badge-entregado { background-color: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }

/* Info Cliente */
.customer-info {
  padding: 14px 20px;
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.info-row { display: flex; justify-content: space-between; align-items: center; }
.info-label { font-size: 0.85rem; color: var(--DC-text-gray, #6e6a75); font-weight: 600; }
.info-value { font-size: 0.88rem; color: var(--DC-brown, #513119); font-weight: 800; }

/* LÍNEA DE TIEMPO (TIMELINE) */
.timeline-container {
  display: flex;
  justify-content: space-between;
  padding: 24px 20px;
  background-color: #ffffff;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  flex: 1;
  z-index: 1;
}

.step-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background-color: #f0ecf6;
  color: #a39bb3;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 6px;
  transition: all 0.3s ease;
  border: 3px solid white;
}

.step-label {
  font-size: 0.72rem;
  font-weight: 800;
  color: var(--DC-text-gray, #6e6a75);
  text-transform: uppercase;
  text-align: center;
}

.step-line {
  position: absolute;
  top: 19px;
  left: 50%;
  width: 100%;
  height: 3px;
  background-color: #ede8f4;
  z-index: -1;
}

.timeline-step.completed .step-icon { 
  background-color: var(--DC-orange, #e28743); 
  color: white; 
}
.timeline-step.completed .step-label { 
  color: var(--DC-orange, #e28743); 
}
.timeline-step.completed .step-line { 
  background-color: var(--DC-orange, #e28743); 
}

.timeline-step.active .step-icon { 
  background-color: white; 
  color: var(--DC-orange, #e28743); 
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 4px rgba(226, 135, 67, 0.2);
}
.timeline-step.active .step-label { 
  color: var(--DC-brown, #513119); 
  font-weight: 900; 
}

/* Resumen de Productos */
.products-summary { padding: 18px 20px; background-color: #fdfaf6; }
.summary-title { margin: 0 0 12px 0; font-size: 0.88rem; color: var(--DC-brown, #513119); font-weight: 800; text-transform: uppercase; }

.products-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
.product-item { display: flex; gap: 12px; align-items: flex-start; padding-bottom: 10px; border-bottom: 1px dashed rgba(81, 49, 25, 0.1); }
.product-item:last-child { border-bottom: none; padding-bottom: 0; }

.product-qty { font-size: 0.88rem; font-weight: 900; color: var(--DC-orange, #e28743); min-width: 25px; }
.product-details { display: flex; flex-direction: column; gap: 3px; }
.product-name { font-size: 0.88rem; font-weight: 800; color: var(--DC-brown, #513119); }

.product-exclusions { display: flex; flex-wrap: wrap; gap: 4px; }
.exclusion-tag { background-color: #fee2e2; color: #dc2626; font-size: 0.68rem; font-weight: 800; padding: 1px 6px; border-radius: 4px; }
.extra-tag { background-color: #dbeafe; color: #1d4ed8; font-size: 0.68rem; font-weight: 800; padding: 1px 6px; border-radius: 4px; }

/* Responsividad */
@media (max-width: 480px) {
  .box { padding: 30px 18px; }
  .search-section { flex-direction: column; }
  .btn-search { padding: 12px; }
  .step-label { font-size: 0.62rem; }
  .step-icon { width: 34px; height: 34px; }
  .step-line { top: 17px; }
}
</style>