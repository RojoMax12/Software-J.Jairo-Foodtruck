<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <header class="modal-header">
        <h2 class="modal-title">Detalle de pedido #{{ orderId }}</h2>
        <div class="header-actions">
          <button class="btn-export">
            <Upload :size="18" />
            <span>Exportar</span>
          </button>
          <button class="btn-close" @click="$emit('close')">
            <X :size="24" />
          </button>
        </div>
      </header>
      
      <div class="modal-content">
        <div class="section-header">
          <div class="section-title-box">
            <ClipboardList :size="22" class="text-pink" />
            <h3 class="section-title">Detalle de productos ({{ products.length }})</h3>
          </div>
        </div>

        <div class="modal-body-layout">
          <div class="products-column">
            <div class="table-wrapper">
              <table class="products-table">
                <thead>
                  <tr>
                    <th>PRODUCTO</th>
                    <th>FORMATO</th>
                    <th>CATEGORÍA</th>
                    <th>CANTIDAD</th>
                    <th>PRECIO UNIT.</th>
                    <th class="text-right">SUBTOTAL</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="product in products" :key="product.id">
                    <td class="bold-text">{{ product.name }}</td>
                    <td>{{ formato(product.format) }}</td>
                    <td>
                      <span class="category-badge">{{ category(product.category) }}</span>
                    </td>
                    <td>{{ product.quantity }}</td>
                    <td>${{ formatNumber(product.price) }}</td>
                    <td class="text-right bold-text">${{ formatNumber(product.subtotal) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <div class="info-column">
            <div class="order-meta">
              <div class="meta-item">
                <div class="meta-icon-box">
                  <Building2 :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Distribuidor</span>
                  <span class="meta-value">{{ distributor || 'Cargando...' }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon-box">
                  <Calendar :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Fecha de ingreso</span>
                  <span class="meta-value">{{ date }} {{ time ? '- ' + time : '' }}</span>
                </div>
              </div>

              <div class="meta-item">
                <div class="meta-icon-box">
                  <LayoutGrid :size="20" />
                </div>
                <div class="meta-text">
                  <span class="meta-label">Estado</span>
                  <span class="status-badge" :class="getStatusClass(localStatus, localStatusId)">
                    {{ localStatus }}
                  </span>
                </div>
              </div>
            </div>

            <div class="totals-summary-card">
              <div class="summary-header">
                <h4 class="summary-title">Resumen de pago</h4>
              </div>
              <div class="summary-divider"></div>
              <div class="summary-content">
                <div class="summary-line">
                  <span class="summary-label">Subtotal</span>
                  <span class="summary-value">${{ formatNumber(subtotalAmount) }}</span>
                </div>
                <div class="summary-line">
                  <span class="summary-label">Descuento total aplicado</span>
                  <span class="summary-value text-pink">- ${{ formatNumber(currentDiscount || 0) }}</span>
                </div>
                <div class="summary-line highlight">
                  <span class="summary-label">Total Final</span>
                  <span class="summary-total">${{ formatNumber(totalAmount) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class = "modal-actions-footer">
        <button class="btn-change-status" @click="changeStatus">
          Cambiar estado
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { 
  X, Upload, Building2, Calendar, 
  ClipboardList, LayoutGrid
} from 'lucide-vue-next';
import orderService from '@/services/orderService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();
const backendSubtotal = ref(0);
const backendDiscount = ref(0);
const backendTotal = ref(0);


const props = defineProps<{
  orderId: number | string;
  distributor?: string;
  status?: string;
  statusId?: number;
  date?: string;
  time?: string;
  total?: number;
  discount?: number;
}>();

const emit = defineEmits(['close', 'statusChanged']);
const localStatus = ref(props.status);
const localStatusId = ref(props.statusId ? Number(props.statusId) : 1);

const products = ref<any[]>([]);


const nombresEstados: Record<number, string> = {
  1: 'En validación',
  2: 'En preparación',
  3: 'En despacho',
  4: 'Entregado',
  5: 'Pendiente',
  6: 'Por pagar',
  7: 'Pagada',
  8: 'Cancelado'
};

const changeStatus = async () => {
  try {
    // Viaja al backend, actualiza PostgreSQL y retorna con éxito
    const result = await orderService.changeOrderStatus(Number(props.orderId));
    notify(result.data.message, 'success');

    // ✨ OPERACIÓN ELECTRÓNICA EN VUE:
    // Si el estado actual es menor a 4 (no está entregado), lo avanzamos manualmente en la interfaz
    if (localStatusId.value < 4) {
      localStatusId.value = localStatusId.value + 1; // Avanza el ID (Ej: de 2 a 3)
      localStatus.value = nombresEstados[localStatusId.value]; // Cambia el texto (Ej: "En despacho")
    }

    // 🚀 LE AVISAMOS AL PADRE: Ejecuta fetchOrders() en segundo plano para actualizar la tabla y contadores
    emit('statusChanged');

  } catch (error: any) {
    console.error('Error al cambiar el estado del pedido:', error);
    notify(error.response?.data?.message || 'Error', 'error');
  }
};

const subtotalAmount = computed(() => {
  if (products.value.length > 0) {
    return products.value.reduce((acc, curr) => acc + curr.subtotal, 0);
  }
  return backendSubtotal.value; // Fallback al valor crudo del backend
});

// Capturamos el descuento global que necesitas pintar
const currentDiscount = computed(() => {
  return props.discount || backendDiscount.value || 0;
});

// El total final neto de la operación
const totalAmount = computed(() => {
  if (props.total) return props.total;
  if (backendTotal.value) return backendTotal.value;
  return subtotalAmount.value - currentDiscount.value;
});

const formatNumber = (num: number) => {
  return new Intl.NumberFormat('es-CL').format(Math.round(num));
};

const formato = (CategoryId: number) => {
  switch (CategoryId) {
    case 1: return '10L';
    case 2: return '5L';
    case 3: return '2.5L';
    case 4: return '1L';
  }
};

const category = (CategoryId: number) =>
{
  switch (CategoryId) {
    case 1: return 'Al agua';
    case 2: return 'Leche de avena';
    case 3: return 'Tradicional ';
    case 4: return 'Sin azúcar';
  }
};

const getproducts = async () => {
  try {
    const response = await orderService.getOrderDetails(Number(props.orderId));
    const orderData = response.data?.data; 

    console.log('Objeto interno de la cotización/pedido:', orderData);

    if (orderData) {
      backendSubtotal.value = Number(orderData.subtotal_cotizacion || 0);
      backendDiscount.value = Number(orderData.descuento_total || 0);
      backendTotal.value = Number(orderData.total_cotizacion || 0);

      if (orderData.productos) {
        products.value = orderData.productos.map((p: any) => {
          const cantidadItem = Number(p.cantidad || p.pivot?.cantidad || p.quantity || 0);
          const precioItem = Number(p.precio_unitario_venta || p.pivot?.precio_unitario_venta || p.price || 0);
          
          return {
            id: p.id_producto || p.id,
            name: p.nombre_producto || 'Producto desconocido',
            format: p.nombre_formato || p.format || p.id_formato || '-',
            category: p.nombre_categoria || p.category || p.id_categoria || '-',
            quantity: cantidadItem,
            price: precioItem,
            subtotal: cantidadItem * precioItem,
            
            discountType: 'none',
            discountValue: 0,
          };
        });
      }
    } else {
      console.warn('No se encontraron datos dentro de response.data.data:', orderData);
    }

    console.log('Resultado final del Proxy de Vue ya mapeado:', products.value);

  } catch (error) {
    console.error('Error al obtener los productos del pedido/cotización:', error);
    notify('No se pudieron cargar los productos del detalle.', 'error');
  }
};

const getStatusClass = (status: string | undefined) => {
  switch (status) {
    case 'Por pagar': return 'status-unpaid';
    case 'Pagada': return 'status-paid';
    case 'En preparación': return 'status-preparation';
    case 'En despacho': return 'status-shipping';
    case 'Entregado': return 'status-completed';
    case 'En validación': return 'status-validation';
    case 'Pendiente': return 'status-pending';
    case 'Cancelado': return 'status-cancelled';
    default: return 'status-generic';
  }
};

onMounted(() => {
  getproducts();
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  backdrop-filter: blur(4px);
}

.modal-container {
  background-color: white;
  width: 95%;
  max-width: 1100px;
  border-radius: 20px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
  max-height: 95vh;
}

.modal-header {
  padding: 12px 32px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #eee;
}

.modal-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 800;
  color: #322c44;
  font-family: 'Inter', sans-serif;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 16px;
}

.btn-export {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  background-color: #f8f9fa;
  border: 1px solid #ddd;
  border-radius: 12px;
  color: #322c44;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-export:hover {
  background-color: #eee;
  border-color: #ccc;
}

.btn-close {
  background: none;
  border: none;
  color: #9793a0;
  cursor: pointer;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s ease;
}

.btn-close:hover {
  color: #322c44;
}

.modal-content {
  padding: 24px 32px 32px 32px;
  overflow-y: auto;
}

.section-header {
  margin-bottom: 20px;
}

.section-title-box {
  display: flex;
  align-items: center;
  gap: 10px;
}

.text-pink {
  color: #e4869f !important;
}

.section-title {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 800;
  color: #322c44;
}

.modal-body-layout {
  display: flex;
  gap: 32px;
  align-items: flex-start;
}

.products-column {
  flex: 1.8;
}

.info-column {
  flex: 1.2;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.order-meta {
  background-color: #f8f9fa;
  padding: 24px;
  border-radius: 16px;
  border: 1px solid #eee;
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.meta-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.meta-icon-box {
  width: 40px;
  height: 40px;
  background-color: white;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #777777;
  border: 1px solid #eee;
}

.meta-text {
  display: flex;
  flex-direction: column;
}

.meta-label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #9793a0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.meta-value {
  font-size: 0.95rem;
  font-weight: 700;
  color: #322c44;
}

.status-badge {
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  display: inline-block;
  margin-top: 4px;
}

.status-unpaid { background-color: #fff0f3; color: #e4869f; border: 1px solid #e4869f; }
.status-paid { background-color: #ebfbee; color: #37b24d; border: 1px solid #37b24d; }
.status-preparation { background-color: #e7f5ff; color: #1c7ed6; border: 1px solid #1c7ed6; }
.status-shipping { background-color: #dcd5ff; color: #6741d9; border: 1px solid #6741d9; }
.status-completed { background-color: #e6fffa; color: #087f5b; border: 1px solid #087f5b; }

.table-wrapper {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  overflow-y: auto;
  height: 450px;
}

.products-table {
  width: 100%;
  border-collapse: collapse;
}

.products-table th {
  background-color: #e5e5e5;
  padding: 16px;
  text-align: left;
  font-size: 0.7rem;
  font-weight: 700;
  color: #777777;
  text-transform: uppercase;
  border-bottom: 1px solid #ddd;
  position: sticky;
  top: 0;
  z-index: 10;
}

.products-table td {
  padding: 24px 16px;
  font-size: 0.85rem;
  color: #322c44;
  border-bottom: 1px solid #ddd;
}

.bold-text {
  font-weight: 700;
}

.text-right {
  text-align: right;
}

.category-badge {
  background-color: #f3f0ff;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: #7950f2;
}

.totals-summary-card {
  background-color: white;
  border: 1px solid #ddd;
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.summary-title {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: #322c44;
}

.summary-divider {
  height: 1px;
  background-color: #eee;
  margin: 16px 0;
}

.summary-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.summary-line {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #777;
}

.summary-value {
  font-size: 1rem;
  font-weight: 700;
  color: #322c44;
}

.highlight {
  margin-top: 8px;
  padding-top: 16px;
  border-top: 2px solid #fce4ec;
}

.highlight .summary-label {
  font-size: 1.1rem;
  font-weight: 800;
  color: #322c44;
}

.summary-total {
  font-size: 1.5rem;
  font-weight: 900;
  color: #322c44;
}

.modal-actions-footer {
  display: flex;
  justify-content: flex-end; 
  padding: 0 32px 24px 32px; 
}

.btn-change-status {
  padding: 12px 20px;
  background-color: #e4869f;
  border: 1px solid #ddd;
  border-radius: 12px;
  color: #ffffff;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-change-status:hover {
  background-color: #928f8f;
  border-color: #ccc;
}

</style>