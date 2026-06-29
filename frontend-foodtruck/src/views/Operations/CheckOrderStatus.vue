<template>
  <div class="status-page">
    <div class="box">
      <h1 class="main-title">Rastrea tu Pedido</h1>
      <p class="subtitle">Ingresa el número de tu comprobante para ver en qué etapa está tu comida.</p>

      <div class="search-section">
        <input 
          v-model="orderId" 
          type="text" 
          placeholder="Ej: 1024" 
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
        Volver al inicio
      </button>

      <Transition name="fade">
        <div v-if="errorMessage" class="error-alert">
          {{ errorMessage }}
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="orderResult" class="result-card">
          
          <div class="result-header">
            <h3 class="order-number">Pedido #{{ orderResult.id }}</h3>
            <span class="status-badge" :class="'badge-' + orderResult.currentStatus">
              {{ getStatusName(orderResult.currentStatus) }}
            </span>
          </div>

          <div class="customer-info">
            <div class="info-row">
              <span class="info-label">Retira:</span>
              <span class="info-value">{{ orderResult.customerName }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Teléfono:</span>
              <span class="info-value">{{ orderResult.customerPhone }}</span>
            </div>
            <div class="info-row">
              <span class="info-label">Metodo de pago:</span>
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
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { Clock, ChefHat, CheckCircle, PackageCheck } from 'lucide-vue-next'; // Asegúrate de tener lucide-vue-next instalado

const router = useRouter();
const orderId = ref('');
const isLoading = ref(false);
const errorMessage = ref('');
const orderResult = ref<any>(null);

// Definición estricta de la línea de tiempo
const timelineSteps = [
  { id: 'en_cola', label: 'En Cola', icon: Clock },
  { id: 'preparacion', label: 'Cocinando', icon: ChefHat },
  { id: 'listo', label: 'Listo', icon: CheckCircle },
  { id: 'entregado', label: 'Entregado', icon: PackageCheck }
];

// Nombres amigables para el badge superior
const getStatusName = (statusId: string) => {
  const step = timelineSteps.find(s => s.id === statusId);
  return step ? step.label : 'Desconocido';
};

// Lógica visual para la línea de tiempo
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

// Función de Búsqueda (Simulada)
// Función de Búsqueda (Simulada)
const handleSearch = async () => {
  errorMessage.value = '';
  orderResult.value = null;

  if (!orderId.value.trim()) {
    errorMessage.value = 'Por favor, ingresa el número de tu pedido.';
    return;
  }

  isLoading.value = true;
  await new Promise(resolve => setTimeout(resolve, 800)); // Simula latencia de red

  // Lógica Dummy para pruebas de diferentes estados
  if (orderId.value === '1024') {
    orderResult.value = {
      id: '1024',
      customerName: 'Juan Pérez',
      customerPhone: '+569 1234 5678',
      customerMetododepago: 'Efectivo', // 🌟 AGREGADO AQUÍ
      currentStatus: 'preparacion', 
      items: [
        { quantity: 2, name: 'Chorrillana Mediana', excluidos: ['Cebolla'] },
        { quantity: 1, name: 'Bebida 1.5L', excluidos: [] }
      ]
    };
  } else if (orderId.value === '1025') {
    orderResult.value = {
      id: '1025',
      customerName: 'María Soto',
      customerPhone: '+569 8765 4321',
      customerMetododepago: 'Tarjeta de Débito', // 🌟 AGREGADO AQUÍ
      currentStatus: 'listo',
      items: [
        { quantity: 1, name: 'Completo Italiano', excluidos: ['Mayo'] }
      ]
    };
  } else {
    errorMessage.value = `No encontramos el pedido #${orderId.value}.`;
  }

  isLoading.value = false;
};

</script>

<style scoped>
/* Contenedor y Caja Base */
.status-page {
  background-color: var(--DC-bg-gray, #fcf8f2); 
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  font-family: var(--font-main, 'Open Sans', sans-serif);
}

.box {
  background-color: white;
  width: 100%;
  max-width: 550px; 
  border-radius: 20px;
  padding: 40px 30px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
}

.main-title { color: var(--DC-gray, #322c44); font-size: 1.8rem; font-weight: 900; margin-bottom: 8px; text-align: center;}
.subtitle { color: var(--DC-text-gray, #9793a0); font-size: 0.95rem; margin-bottom: 30px; text-align: center;}

/* Buscador */
.search-section { display: flex; gap: 10px; margin-bottom: 20px; }
.dc-input { flex: 1; padding: 14px 20px; border: 2px solid #eeedee; border-radius: 12px; font-size: 1rem; font-weight: 600; color: var(--DC-gray, #322c44); outline: none; transition: border-color 0.2s; }
.dc-input:focus { border-color: var(--DC-orange, #e28743); }
.btn-search { background-color: var(--DC-orange, #e28743); color: white; border: none; padding: 0 25px; border-radius: 12px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s; }
.btn-search:hover:not(:disabled) { background-color: var(--DC-brown, #5a3614); transform: translateY(-2px); }
.btn-search:disabled { opacity: 0.7; cursor: not-allowed; }
.btn-home { width: 100%; margin-top: 12px; background-color: #f4e1d2; color: var(--DC-brown, #5a3614); border: none; padding: 12px 16px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-home:hover { background-color: #e8cfb8; transform: translateY(-1px); }

.error-alert { background-color: #fff0f3; color: #c92a2a; padding: 12px; border-radius: 10px; font-size: 0.9rem; font-weight: 700; border: 1px solid #ffc9c9; text-align: center; }

/* Tarjeta de Resultado */
.result-card {
  margin-top: 25px;
  border-radius: 16px;
  border: 2px solid #eeedee;
  background-color: #fafafa;
  overflow: hidden;
}

.result-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  background-color: white;
  border-bottom: 1px solid #eeedee;
}

.order-number { margin: 0; font-size: 1.3rem; color: var(--DC-gray, #322c44); font-weight: 900; }

.status-badge { padding: 6px 12px; border-radius: 20px; font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;}
.badge-en_cola { background-color: #e9ecef; color: #495057; border: 1px solid #ced4da; }
.badge-preparacion { background-color: #fff4e6; color: var(--DC-orange, #e28743); border: 1px solid #ffe8cc; }
.badge-listo { background-color: #ebfbee; color: #2b8a3e; border: 1px solid #b2f2bb; }
.badge-entregado { background-color: #e3fafc; color: #0ca678; border: 1px solid #96f2d7; }

/* Info Cliente */
.customer-info {
  padding: 15px 20px;
  background-color: #f8f9fa;
  display: flex;
  flex-direction: column;
  gap: 8px;
  border-bottom: 1px solid #eeedee;
}

.info-row { display: flex; justify-content: space-between; align-items: center; }
.info-label { font-size: 0.9rem; color: var(--DC-text-gray, #9793a0); font-weight: 600; }
.info-value { font-size: 0.95rem; color: var(--DC-gray, #322c44); font-weight: 800; }

/* LÍNEA DE TIEMPO (TIMELINE) */
.timeline-container {
  display: flex;
  justify-content: space-between;
  padding: 25px 20px;
  background-color: white;
  border-bottom: 1px solid #eeedee;
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
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background-color: #eeedee;
  color: #adb5bd;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 8px;
  transition: all 0.3s ease;
  border: 3px solid white;
}

.step-label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #adb5bd;
  text-transform: uppercase;
  text-align: center;
  transition: all 0.3s ease;
}

.step-line {
  position: absolute;
  top: 20px; /* Mitad del icono */
  left: 50%;
  width: 100%;
  height: 3px;
  background-color: #eeedee;
  z-index: -1;
  transition: all 0.3s ease;
}

/* Estados de la Línea de Tiempo */
.timeline-step.completed .step-icon { background-color: var(--DC-orange, #e28743); color: white; }
.timeline-step.completed .step-label { color: var(--DC-orange, #e28743); }
.timeline-step.completed .step-line { background-color: var(--DC-orange, #e28743); }

.timeline-step.active .step-icon { 
  background-color: white; 
  color: var(--DC-orange, #e28743); 
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 4px rgba(226, 135, 67, 0.15); /* Resplandor naranja */
}
.timeline-step.active .step-label { color: var(--DC-gray, #322c44); font-weight: 900; }

/* Resumen de Productos */
.products-summary { padding: 20px; background-color: white; }
.summary-title { margin: 0 0 15px 0; font-size: 1rem; color: var(--DC-gray, #322c44); font-weight: 800; text-transform: uppercase; }

.products-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px; }
.product-item { display: flex; gap: 15px; align-items: flex-start; padding-bottom: 12px; border-bottom: 1px dashed #eeedee; }
.product-item:last-child { border-bottom: none; padding-bottom: 0; }

.product-qty { font-size: 1rem; font-weight: 900; color: var(--DC-orange, #e28743); min-width: 25px; }
.product-details { display: flex; flex-direction: column; gap: 4px; }
.product-name { font-size: 0.95rem; font-weight: 800; color: var(--DC-gray, #322c44); }

.product-exclusions { display: flex; flex-wrap: wrap; gap: 4px; }
.exclusion-tag { background-color: #fff0f3; color: #c92a2a; border: 1px solid #ffc9c9; font-size: 0.65rem; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }

/* Responsividad */
@media (max-width: 480px) {
  .box { padding: 30px 20px; }
  .search-section { flex-direction: column; }
  .btn-search { padding: 15px; }
  .step-label { font-size: 0.65rem; }
  .step-icon { width: 35px; height: 35px; }
  .step-line { top: 17.5px; }
}
</style>