<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <!-- ===================== HEADER ===================== -->
      <header class="modal-header">
        <div class="header-titles">
          <div class="title-with-badge">
            <span class="comanda-number">#{{ orderId }}</span>
            <h2 class="modal-title">Detalle de Comanda</h2>
          </div>
          <div class="badges-row">
            <span class="status-badge" :class="getStatusClass(localStatusId)">{{ localStatus }}</span>
            <span class="status-badge" :class="localPaymentStatusId === 2 ? 'status-paid' : 'status-unpaid'">
              {{ localPaymentStatusId === 2 ? 'PAGADO' : 'POR PAGAR' }}
            </span>
          </div>
        </div>

        <div class="header-actions">
          <button type="button" class="btn-close" @click="handleClose" title="Cerrar modal">
            <X :size="20" />
          </button>
        </div>
      </header>

      <!-- ===================== CONTENIDO ===================== -->
      <div class="modal-content">

        <!-- DATOS DEL CLIENTE -->
        <div class="client-card">
          <div class="client-row main-client-row">
            <div class="client-name-box">
              <User :size="16" class="meta-icon" />
              <strong>{{ distributor || rawOrder?.nombre_persona || 'Cliente' }}</strong>
            </div>
          </div>

          <div class="client-row">
            <Phone :size="15" class="meta-icon" />
            <span>{{ customerPhone || 'Sin teléfono registrado' }}</span>
          </div>

          <!-- MÉTODO DE PAGO Y PAGO MIXTO -->
          <div class="client-row payment-select-row">
            <DollarSign :size="15" class="meta-icon" />
            <span class="payment-lbl">Pago:</span>
            <div class="payment-method-control-group">
              <select v-model="currentPaymentMethod" class="payment-method-select" @change="onPaymentSelectChange">
                <option v-if="isCustomPaymentMethod" :value="currentPaymentMethod">{{ currentPaymentMethod }}</option>
                <option value="Efectivo">Efectivo</option>
                <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                <option value="Transferencia">Transferencia</option>
                <option value="__DIVIDIR_PAGO__">Pago Mixto (Dividir monto)...</option>
              </select>
              <button 
                type="button" 
                class="btn-split-toggle-mini" 
                :class="{ active: isSplitPaymentModalOpen }"
                title="Dividir pago entre dos métodos"
                @click="openSplitPaymentEditor"
              >
                Dividir
              </button>
            </div>
          </div>

          <!-- EDITOR DE PAGO MIXTO EXPANDIBLE -->
          <div v-if="isSplitPaymentModalOpen" class="split-payment-editor-card animate-fade-in">
            <div class="split-editor-header">
              <div class="split-title">
                <DollarSign :size="15" />
                <strong>Configurar Pago Mixto</strong>
              </div>
              <button type="button" class="btn-close-split" @click="isSplitPaymentModalOpen = false" title="Cerrar divisor">
                <X :size="14" />
              </button>
            </div>

            <div class="split-editor-body">
              <div class="split-row">
                <div class="split-col">
                  <label class="split-label">Método 1</label>
                  <select v-model="splitMethod1" class="split-select">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                  <div class="split-input-wrap">
                    <span class="currency-prefix">$</span>
                    <input 
                      v-model.number="splitAmount1" 
                      type="number" 
                      min="0" 
                      class="split-amount-input" 
                      placeholder="0"
                      @input="onSplitAmount1Input"
                    />
                  </div>
                </div>

                <div class="split-divider-plus">+</div>

                <div class="split-col">
                  <label class="split-label">Método 2</label>
                  <select v-model="splitMethod2" class="split-select">
                    <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                  <div class="split-input-wrap">
                    <span class="currency-prefix">$</span>
                    <input 
                      v-model.number="splitAmount2" 
                      type="number" 
                      min="0" 
                      class="split-amount-input" 
                      placeholder="0"
                    />
                  </div>
                </div>
              </div>

              <!-- BOTONES RÁPIDOS -->
              <div class="split-quick-actions">
                <button type="button" class="btn-split-quick" @click="setSplitFiftyFifty">
                  Dividir 50% / 50%
                </button>
                <button type="button" class="btn-split-quick" @click="autoFillSplitRemainder">
                  Completar restante
                </button>
              </div>

              <!-- RESUMEN DE CUADRATURA -->
              <div class="split-summary-bar">
                <div class="split-sum-item">
                  <span class="sum-label">Total Pedido:</span>
                  <strong class="sum-val">${{ formatNumber(totalAmount) }}</strong>
                </div>
                <div class="split-sum-item">
                  <span class="sum-label">Suma:</span>
                  <strong class="sum-val" :class="splitDiff === 0 ? 'text-ok' : 'text-warn'">${{ formatNumber(splitTotalSum) }}</strong>
                </div>
                <div class="split-diff-badge" :class="splitDiff === 0 ? 'diff-ok' : (splitDiff > 0 ? 'diff-missing' : 'diff-over')">
                  <span v-if="splitDiff === 0">✓ Cuadra</span>
                  <span v-else-if="splitDiff > 0">Faltan ${{ formatNumber(splitDiff) }}</span>
                  <span v-else>Excede ${{ formatNumber(Math.abs(splitDiff)) }}</span>
                </div>
              </div>

              <div class="split-actions-row">
                <button 
                  type="button" 
                  class="btn-apply-split" 
                  :disabled="splitDiff !== 0"
                  @click="applySplitPayment"
                >
                  Guardar Pago Mixto
                </button>
              </div>
            </div>
          </div>

          <!-- NOTAS / INSTRUCCIONES DE COCINA -->
          <div class="client-notes-section">
            <div class="notes-header-row">
              <label for="order-notes-input">
                <FileText :size="14" />
                <strong>Notas / Instrucciones especiales:</strong>
              </label>
              <div class="notes-status-wrap">
                <span v-if="isSavingNotes" class="notes-status-indicator saving">Guardando...</span>
                <span v-else-if="notesSavedStatus" class="notes-status-indicator saved">Guardado ✓</span>
                <button 
                  type="button" 
                  class="btn-save-notes-mini" 
                  :disabled="isSavingNotes" 
                  @click="saveNotesNow"
                >
                  Guardar
                </button>
              </div>
            </div>
            <textarea 
              id="order-notes-input"
              v-model="orderNotes" 
              placeholder="Ej: Sin mayonesa en las papas, sin servilletas, retiro 21:30..." 
              class="order-notes-textarea"
              rows="2"
              @input="onNotesInput"
              @blur="saveNotesNow"
            ></textarea>
          </div>
        </div>

        <!-- LISTADO DE PRODUCTOS -->
        <div class="products-list">
          <div class="list-header">
            <h3>Productos Seleccionados ({{ products.length }})</h3>
            <button type="button" class="btn-add-mini" @click="openAddModal">
              <Plus :size="14" />
              <span>Agregar Producto</span>
            </button>
          </div>

          <div v-for="product in products" :key="product.id" class="product-card">
            <div class="product-main">
              <div class="qty-control">
                <button type="button" :disabled="product.quantity <= 1" @click="changeQty(product, -1)">-</button>
                <span class="qty-num">{{ product.quantity }}</span>
                <button type="button" @click="changeQty(product, 1)">+</button>
              </div>
              <div class="product-info">
                <strong>{{ product.name }}</strong>
                <small v-if="product.format && product.format !== 'Único' && product.format !== 'Normal'">
                  {{ formato(product.format) }}
                </small>
              </div>
              <span class="price">${{ formatNumber(product.subtotal) }}</span>
              <button type="button" class="btn-trash" @click="removeProduct(product.id)" title="Eliminar ítem">
                <Trash2 :size="16" />
              </button>
            </div>

            <!-- MODIFICACIONES DE INGREDIENTES -->
            <div v-if="(product.removedIngredients || []).length || (product.addedExtras || []).length" class="product-ingredients">
              <span v-for="ing in product.removedIngredients || []" :key="ing" class="chip chip-removed">
                Sin {{ ing }}
                <button type="button" @click="toggleRemovedIngredient(product.id, ing)"><X :size="10" /></button>
              </span>
              <span v-for="extra in product.addedExtras || []" :key="extra.name" class="chip chip-extra">
                + {{ extra.name }} (x{{ extra.quantity }})
                <button type="button" @click="removeExtraFromProduct(product.id, extra.name)"><X :size="10" /></button>
              </span>
            </div>
            <div v-else class="product-ingredients-empty">Sin modificaciones de receta</div>
          </div>
        </div>

        <!-- BANNERS DE REGLAS DE NEGOCIO -->
        <div v-if="localStatusId === 5" class="order-rule-banner banner-cancelled animate-fade-in">
          <AlertTriangle :size="18" class="rule-icon" />
          <div class="rule-text">
            <strong>Pedido Cancelado:</strong> No es posible registrar pagos ni reactivar su preparación.
          </div>
        </div>
        <div v-else-if="localStatusId === 4" class="order-rule-banner banner-delivered animate-fade-in">
          <CheckCircle :size="18" class="rule-icon" />
          <div class="rule-text">
            <strong>Pedido Entregado:</strong> La orden ya fue retirada por el cliente. No puede ser cancelada.
          </div>
        </div>

        <!-- TIMELINE Y CONTROL DE ESTADOS KDS -->
        <div class="timeline-container">
          <div class="timeline-steps">
            <div 
              v-for="step in orderSteps" 
              :key="step.id"
              class="timeline-step"
              :class="{ 
                'active': localStatusId === step.id, 
                'completed': localStatusId !== 5 && localStatusId > step.id 
              }"
              @click="localStatusId !== 5 && (localStatusId !== 4 || step.id <= 4) && setOrderStatus(step.id)"
            >
              <div class="step-circle">
                <Check v-if="localStatusId !== 5 && localStatusId > step.id" :size="14" />
                <span v-else>{{ step.id }}</span>
              </div>
              <span class="step-label">{{ step.name }}</span>
            </div>
          </div>

          <!-- NAVEGACIÓN Y ACCIONES RÁPIDAS -->
          <div class="status-navigation">
            <button 
              type="button"
              class="btn-step" 
              :disabled="localStatusId <= 1 || localStatusId === 5" 
              @click="stepStatus(-1)"
            >
              <ChevronLeft :size="15" />
              <span>Anterior</span>
            </button>

            <button 
              v-if="localPaymentStatusId !== 2" 
              type="button"
              class="btn-pay" 
              :disabled="localStatusId === 5"
              :class="{ 'btn-disabled-rule': localStatusId === 5 }"
              :title="localStatusId === 5 ? 'Un pedido cancelado no puede ser pagado' : 'Marcar como Pagado'"
              @click="markAsPaid"
            >
              <DollarSign :size="15" />
              <span>Marcar Pagado</span>
            </button>
            <span v-else class="badge-paid-confirmed">
              <CheckCircle :size="15" />
              <span>Pagado</span>
            </span>

            <button 
              v-if="localStatusId !== 5"
              type="button"
              class="btn-cancel-order"
              :disabled="localStatusId === 4"
              :class="{ 'btn-disabled-rule': localStatusId === 4 }"
              :title="localStatusId === 4 ? 'Un pedido entregado no puede ser cancelado' : 'Cancelar Pedido'"
              @click="cancelOrder"
            >
              <XCircle :size="15" />
              <span>Cancelar Pedido</span>
            </button>
            <span v-else class="badge-cancelled-confirmed">
              <XCircle :size="15" />
              <span>Cancelado</span>
            </span>

            <button 
              type="button"
              class="btn-step" 
              :disabled="localStatusId >= 4 || localStatusId === 5" 
              @click="stepStatus(1)"
            >
              <span>Siguiente</span>
              <ChevronRight :size="15" />
            </button>
          </div>
        </div>
      </div>

      <!-- ===================== FOOTER ===================== -->
      <footer class="modal-footer">
        <div class="footer-total">
          <span class="footer-total-label">Total Pedido</span>
          <strong class="footer-total-val">${{ formatNumber(totalAmount) }}</strong>
        </div>
        <div class="footer-actions">
          <a 
            v-if="whatsappUrl" 
            :href="whatsappUrl" 
            target="_blank" 
            rel="noopener noreferrer" 
            class="btn-footer-wa" 
            title="Avisar por WhatsApp al cliente"
          >
            <MessageCircle :size="16" />
            <span>Avisar WhatsApp</span>
          </a>
          <button type="button" class="btn-print" @click="printOrder" title="Imprimir comanda">
            <Printer :size="17" />
            <span>Imprimir</span>
          </button>
        </div>
      </footer>
    </div>

    <!-- ===================== SUBMODAL: AGREGAR PRODUCTO ===================== -->
    <OrderAddProductModal
      :is-open="isAddModalOpen"
      :is-loading-catalog="isLoadingCatalog"
      :catalog-products="catalogProducts"
      :stock-ingredients="stockIngredients"
      @close="closeAddModal"
      @add-product="handleAddProductFromModal"
    />
  </div>

  <!-- VOUCHER IMPRIMIBLE DE COCINA -->
  <KitchenVoucher
    :order-id="orderId"
    :date="date"
    :time="time"
    :distributor="distributor"
    :customer-phone="customerPhone"
    :order-notes="orderNotes"
    :products="products"
    :total-amount="totalAmount"
  />
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { 
  X, Phone, Printer, Plus, Trash2, User, Check, 
  CheckCircle, ChevronLeft, ChevronRight, DollarSign, 
  XCircle, MessageCircle, FileText, AlertTriangle 
} from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification';
import orderService from '@/services/orderService';
import productService from '@/services/productService';
import stockService from '@/services/stockService';
import { formatCLP } from '@/utils/format';
import OrderAddProductModal from './components/OrderAddProductModal.vue';
import KitchenVoucher from './components/KitchenVoucher.vue';

const { notify } = useNotification();
const props = defineProps<{
  orderId: number | string; 
  realId?: number | string;
  distributor?: string; 
  status?: string;
  statusId?: number; 
  date?: string; 
  time?: string;
  total?: number; 
  phone?: string;
  rawOrder?: any;
}>();

const emit = defineEmits(['close', 'statusChanged', 'status-changed']);
const localStatus = ref(props.status || 'Pendiente');
const localStatusId = ref(props.statusId ? Number(props.statusId) : 1);
const localPaymentStatusId = ref(props.rawOrder?.id_estado_pago ? Number(props.rawOrder.id_estado_pago) : 1);
const currentPaymentMethod = ref(props.rawOrder?.metodo_pago || 'Efectivo');
const orderNotes = ref(props.rawOrder?.notas || '');
const products = ref<any[]>([]);
const orderStorageKey = computed(() => `order-${props.realId || props.orderId}`);

const customerPhone = computed(() => {
  return props.phone || props.rawOrder?.numero_telefono || props.rawOrder?.telefono || '';
});

const whatsappUrl = computed(() => {
  if (!customerPhone.value) return '';
  const cleanPhone = customerPhone.value.replace(/\D/g, '');
  if (!cleanPhone) return '';

  let formatted = cleanPhone;
  if (cleanPhone.length === 9 && cleanPhone.startsWith('9')) {
    formatted = `56${cleanPhone}`;
  } else if (cleanPhone.length === 8) {
    formatted = `569${cleanPhone}`;
  }

  const maxLines = 8;
  const lines = products.value.slice(0, maxLines).map((p: any) => {
    const extras = (p.addedExtras || []).map((e: any) => e.name).filter(Boolean);
    return `- ${p.quantity || 1}x ${p.name || p.nombre || 'Producto'}${extras.length ? ` (+ ${extras.join(', ')})` : ''}`;
  });
  if (products.value.length > maxLines) {
    lines.push(`... y ${products.value.length - maxLines} producto(s) más`);
  }
  const detalle = lines.join('\n');
  const total = props.total ? `\nTotal: $${formatCLP(props.total)}` : '';
  let rawMsg = `Hola ${props.distributor || 'cliente'}, tu pedido #${props.orderId} en J.Junior Foodtruck está listo! 🍔${detalle ? `\n\nDetalle:\n${detalle}` : ''}${total}`;
  let text = encodeURIComponent(rawMsg);
  if (text.length > 1600) {
    rawMsg = `Hola ${props.distributor || 'cliente'}, tu pedido #${props.orderId} en J.Junior Foodtruck está listo! 🍔${total}`;
    text = encodeURIComponent(rawMsg);
  }
  return `https://wa.me/${formatted}?text=${text}`;
});

const orderSteps = [
  { id: 1, name: 'Pendiente' },
  { id: 2, name: 'En preparación' },
  { id: 3, name: 'Listo' },
  { id: 4, name: 'Entregado' }
];

const isInitializing = ref(true);

const extractRemovedFromDet = (det: any): string[] => {
  let list: string[] = [];
  if (Array.isArray(det.ingredientes) && det.ingredientes.length > 0) {
    list = det.ingredientes
      .filter((ing: any) => {
        const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase();
        return tipo.includes('exclu') || tipo.includes('quit');
      })
      .map((ing: any) => ing.ingrediente?.nombre || ing.nombre || (typeof ing === 'string' ? ing : ''))
      .filter(Boolean);
  } else if (Array.isArray(det.removedIngredients)) {
    list = det.removedIngredients.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean);
  } else if (Array.isArray(det.ingredientes_excluidos)) {
    list = det.ingredientes_excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean);
  } else if (Array.isArray(det.excluidos)) {
    list = det.excluidos.map((i: any) => typeof i === 'string' ? i : (i.nombre || i.name)).filter(Boolean);
  } else if (Array.isArray(det.modificaciones)) {
    list = det.modificaciones
      .filter((m: any) => {
        const tipo = String(m.tipo || m.tipo_modificacion || '').toLowerCase();
        return tipo.includes('exclu') || tipo.includes('quit');
      })
      .map((m: any) => m.nombre || m.ingrediente?.nombre || (typeof m === 'string' ? m : ''))
      .filter(Boolean);
  }
  return [...new Set(list)];
};

const extractAddedFromDet = (det: any): any[] => {
  let list: any[] = [];
  if (Array.isArray(det.ingredientes) && det.ingredientes.length > 0) {
    list = det.ingredientes
      .filter((ing: any) => {
        const tipo = String(ing.tipo_modificacion || ing.tipo || '').toLowerCase();
        return tipo.includes('agre') || tipo.includes('extra');
      })
      .map((ing: any) => ({
        name: ing.ingrediente?.nombre || ing.nombre || (typeof ing === 'string' ? ing : 'Extra'),
        quantity: 1,
        price: Number(ing.precio_aplicado || 0)
      }))
      .filter((e: any) => Boolean(e.name));
  } else if (Array.isArray(det.addedExtras)) {
    list = det.addedExtras.map((i: any) => typeof i === 'string' ? { name: i, quantity: 1, price: 0 } : { name: i.name || i.nombre, quantity: i.quantity || 1, price: i.price || 0 }).filter((e: any) => Boolean(e.name));
  } else if (Array.isArray(det.agregados)) {
    list = det.agregados.map((i: any) => typeof i === 'string' ? { name: i, quantity: 1, price: 0 } : { name: i.nombre || i.name, quantity: 1, price: i.precio || 0 }).filter((e: any) => Boolean(e.name));
  } else if (Array.isArray(det.modificaciones)) {
    list = det.modificaciones
      .filter((m: any) => {
        const tipo = String(m.tipo || m.tipo_modificacion || '').toLowerCase();
        return tipo.includes('agre') || tipo.includes('extra');
      })
      .map((m: any) => ({
        name: m.nombre || m.ingrediente?.nombre || (typeof m === 'string' ? m : 'Extra'),
        quantity: 1,
        price: Number(m.precio || 0)
      }))
      .filter((e: any) => Boolean(e.name));
  }

  const uniqueMap = new Map();
  for (const item of list) {
    if (!uniqueMap.has(item.name)) {
      uniqueMap.set(item.name, item);
    }
  }
  return Array.from(uniqueMap.values());
};

watch(() => props.rawOrder, async (newOrder) => {
  if (newOrder) {
    isInitializing.value = true;
    if (newOrder.rawStatusId) localStatusId.value = Number(newOrder.rawStatusId);
    if (newOrder.status) localStatus.value = newOrder.status;
    if (newOrder.id_estado_pago) localPaymentStatusId.value = Number(newOrder.id_estado_pago);
    if (newOrder.notas !== undefined && newOrder.notas !== null) orderNotes.value = newOrder.notas || '';
    if (newOrder.metodo_pago) currentPaymentMethod.value = newOrder.metodo_pago;

    let detailsList = Array.isArray(newOrder.detalles) ? newOrder.detalles : [];

    const targetOrderId = props.realId || props.orderId;
    if (targetOrderId && (detailsList.length === 0 || detailsList.some((d: any) => !d.ingredientes && !d.producto))) {
      try {
        const res = await orderService.getPublicOrderById(targetOrderId).catch(() => orderService.getOrderById(targetOrderId));
        const fullOrder = res?.data?.data || res?.data;
        if (fullOrder && Array.isArray(fullOrder.detalles) && fullOrder.detalles.length > 0) {
          detailsList = fullOrder.detalles;
        }
        if (fullOrder && fullOrder.notas !== undefined && fullOrder.notas !== null && !orderNotes.value) {
          orderNotes.value = fullOrder.notas || '';
        }
        if (fullOrder && fullOrder.metodo_pago) {
          currentPaymentMethod.value = fullOrder.metodo_pago;
        }
      } catch (err) {
        console.error('Error cargando detalles del pedido en modal:', err);
      }
    }

    if (detailsList.length > 0) {
      products.value = detailsList.map((det: any, idx: number) => {
        const prodName = det.producto?.nombre || det.nombre || det.name || 'Producto';
        const formatName = det.tamaño?.nombre || det.tamano?.nombre || det.formato || 'Único';
        const qty = Number(det.cantidad || 1);
        const unitPrice = Number(det.precio_unitario || det.precio || 0);

        return {
          id: det.id_detalle_pedido || idx + 1,
          catalogId: det.id_producto || det.producto?.id_producto,
          name: prodName,
          format: formatName,
          quantity: qty,
          subtotal: qty * unitPrice,
          removedIngredients: extractRemovedFromDet(det),
          addedExtras: extractAddedFromDet(det)
        };
      });
    }

    setTimeout(() => {
      isInitializing.value = false;
    }, 200);
  }
}, { immediate: true });

const isAddModalOpen = ref(false);
const hasPendingChanges = ref(false);
const isLoadingCatalog = ref(false);
const catalogProducts = ref<any[]>([]);

const loadCatalogProducts = async () => {
  isLoadingCatalog.value = true;
  try {
    const response = await productService.getPublicProducts();
    const rawProducts = Array.isArray(response?.data) ? response.data : (response?.data?.data || []);
    const activeRawProducts = rawProducts.filter((p: any) => {
      const isActivo = p.activo !== false && p.activo !== 0 && p.active !== false;
      const isDisponible = p.disponible !== false && p.disponible !== 0 && p.inStock !== false;
      return isActivo && isDisponible;
    });
    
    catalogProducts.value = activeRawProducts.map((product: any) => {
      const catName = product.categoria?.nombre_categoria || product.categoria?.nombre || product.id_categoria || 'Varios';

      const sizesArray = Array.isArray(product.tamaños) ? product.tamaños : (product.sizes || []);
      const sizesFormatted = sizesArray.length > 0
        ? sizesArray.map((size: any) => ({
            id: size.id_tamaño || size.pivot?.id_tamaño || size.id || 1,
            name: size.nombre || 'Normal',
            price: Number(size.pivot?.precio || size.pivot?.precio_venta || size.precio_venta || size.precio || 0)
          }))
        : [{ id: 1, name: 'Normal', price: Number(product.precio_base || product.precio || 0) }];

      const rawIngredients = Array.isArray(product.ingredientes) 
        ? product.ingredientes 
        : (product.producto_ingrediente || []);

      const ingredientsFormatted = rawIngredients.map((item: any) => {
        const ingObj = item.ingrediente || item;
        return {
          id: ingObj.id_ingrediente || item.id_ingrediente || item.id,
          name: ingObj.nombre || item.nombre || (typeof item === 'string' ? item : 'Ingrediente'),
          cantidad: item.cantidad || 1,
          incluido_por_defecto: item.incluido_por_defecto === true || item.incluido_por_defecto === 1 || item.incluido_por_defecto === '1'
        };
      });

      return {
        id: product.id_producto || product.id,
        name: product.nombre || 'Producto',
        category: catName,
        tipo_armado: product.tipo_armado || 'Estandar',
        cantidad_incluida: Number(product.cantidad_incluida || 0),
        precio_ingrediente_extra: Number(product.precio_ingrediente_extra || 500),
        sizes: sizesFormatted,
        ingredients: ingredientsFormatted,
        basePrice: sizesFormatted[0]?.price || 0
      };
    });

  } catch (error) {
    console.error('Error al cargar catálogo en modal:', error);
  } finally {
    isLoadingCatalog.value = false;
  }
};

const totalAmount = computed(() => {
  return products.value.reduce((acc: number, p: any) => acc + (Number(p.subtotal) || 0), 0);
});

const formatNumber = (n: any) => {
  const val = typeof n === 'object' && n !== null && 'value' in n ? n.value : n;
  return formatCLP(val);
};
const formato = (f: string) => f;

const saveOrder = async (showNotification = true) => {
  if (isInitializing.value) return;

  const currentTotal = totalAmount.value;
  const snapshot = {
    status: localStatus.value,
    statusId: localStatusId.value,
    total: currentTotal,
    notas: orderNotes.value,
    products: products.value.map((p: any) => ({
      ...p,
      removedIngredients: [...(p.removedIngredients || [])]
    }))
  };

  localStorage.setItem(orderStorageKey.value, JSON.stringify(snapshot));

  const targetId = props.realId || props.rawOrder?.id_pedido || props.rawOrder?.real_id || props.orderId;
  if (targetId) {
    const payload: any = {
      total: currentTotal,
      id_estado_pedido: localStatusId.value,
      id_estado_pago: localPaymentStatusId.value,
      metodo_pago: currentPaymentMethod.value,
      notas: orderNotes.value,
    };

    if (hasPendingChanges.value) {
      payload.items = products.value.map((p: any) => ({
        id_producto: p.catalogId || p.id,
        format: p.format,
        cantidad: p.quantity,
        precio_unitario: p.quantity ? Math.round(p.subtotal / p.quantity) : p.subtotal,
        removedIngredients: p.removedIngredients || [],
        addedExtras: p.addedExtras || []
      }));
    }

    try {
      await orderService.updateOrder(targetId, payload);
    } catch (err) {
      console.error('Error al actualizar pedido en servidor:', err);
    }
  }

  hasPendingChanges.value = false;
  if (showNotification) {
    notify('Pedido guardado correctamente', 'success');
  }
};

const loadSavedOrder = () => {
  const saved = localStorage.getItem(orderStorageKey.value);
  if (!saved) return;

  try {
    const parsed = JSON.parse(saved);
    if (parsed.products) products.value = parsed.products;
    if (parsed.status) localStatus.value = parsed.status;
    if (parsed.statusId) localStatusId.value = parsed.statusId;
    if (parsed.notas !== undefined && !orderNotes.value) orderNotes.value = parsed.notas || '';
    if (parsed.metodo_pago && !currentPaymentMethod.value) currentPaymentMethod.value = parsed.metodo_pago;
    hasPendingChanges.value = false;
  } catch {
    localStorage.removeItem(orderStorageKey.value);
  }
};

const stockIngredients = ref<any[]>([]);

const loadStockIngredients = async () => {
  try {
    const res = await stockService.getStocks();
    const list = Array.isArray(res?.data) ? res.data : (res?.data?.data || []);
    stockIngredients.value = list;
  } catch (e) {
    console.error('Error al cargar lista de stock:', e);
  }
};

onMounted(() => {
  loadSavedOrder();
  loadCatalogProducts();
  loadStockIngredients();
});

watch([products, localStatus, localStatusId, localPaymentStatusId, currentPaymentMethod, orderNotes], () => {
  if (!isInitializing.value) {
    saveOrder(false);
  }
}, { deep: true });

const handleClose = async () => {
  await saveOrder(false);
  emit('statusChanged');
  emit('status-changed');
  emit('close');
};

const changeQty = (p: any, delta: number) => {
  const currentQuantity = Number(p.quantity || 1);
  const nextQuantity = currentQuantity + delta;
  if (nextQuantity < 1) return;

  const unitPrice = currentQuantity > 0 ? (Number(p.subtotal || 0) / currentQuantity) : 0;
  p.quantity = nextQuantity;
  p.subtotal = Math.round(nextQuantity * unitPrice);
  hasPendingChanges.value = true;
  saveOrder(false);
};

const openAddModal = () => {
  isAddModalOpen.value = true;
};

const closeAddModal = () => {
  isAddModalOpen.value = false;
};

const normalizeRemovedIngredients = (items: string[] = []) => [...new Set(items)].sort();

const handleAddProductFromModal = (payload: {
  catalogId: number | string;
  name: string;
  format: string;
  quantity: number;
  singleUnitPrice: number;
  removedIngredients: string[];
  addedExtras: { name: string; quantity: number; price: number }[];
}) => {
  const existingProduct = products.value.find((p: any) => {
    const sameCatalog = Number(p.catalogId ?? p.id) === Number(payload.catalogId);
    const sameFormat = p.format === payload.format;
    const sameRemoved = JSON.stringify(normalizeRemovedIngredients(p.removedIngredients || [])) === JSON.stringify(payload.removedIngredients);
    const sameExtras = JSON.stringify(p.addedExtras || []) === JSON.stringify(payload.addedExtras);
    return sameCatalog && sameFormat && sameRemoved && sameExtras;
  });

  if (existingProduct) {
    existingProduct.quantity += payload.quantity;
    existingProduct.subtotal = Number((existingProduct.quantity * payload.singleUnitPrice).toFixed(0));
  } else {
    products.value.push({
      id: Date.now(),
      catalogId: payload.catalogId,
      name: payload.name,
      format: payload.format,
      quantity: payload.quantity,
      subtotal: Number((payload.quantity * payload.singleUnitPrice).toFixed(0)),
      removedIngredients: payload.removedIngredients,
      addedExtras: payload.addedExtras,
    });
  }

  hasPendingChanges.value = true;
  saveOrder(false);
  closeAddModal();
};

const removeExtraFromProduct = (productId: number | string, extraName: string) => {
  const prod = products.value.find((p: any) => p.id === productId);
  if (prod && prod.addedExtras) {
    const idx = prod.addedExtras.findIndex((e: any) => e.name === extraName);
    if (idx > -1) {
      prod.addedExtras.splice(idx, 1);
      hasPendingChanges.value = true;
      saveOrder(false);
    }
  }
};

const removeProduct = (id: any) => {
  products.value = products.value.filter(p => p.id !== id);
  hasPendingChanges.value = true;
  saveOrder(false);
};

const toggleRemovedIngredient = (pid: any, ing: string) => {
  const p = products.value.find(x => x.id === pid);
  if (!p) return;

  const removed = p.removedIngredients || [];
  p.removedIngredients = removed.includes(ing)
    ? removed.filter((i: any) => i !== ing)
    : [...removed, ing];

  hasPendingChanges.value = true;
  saveOrder(false);
};

const getStatusClass = (id: number) => {
  const map: Record<number, string> = {
    1: 'status-pending',
    2: 'status-preparation',
    3: 'status-shipping',
    4: 'status-completed',
    5: 'status-cancelled'
  };
  return map[id] || 'status-generic';
};

const markAsPaid = async () => {
  if (localStatusId.value === 5) {
    notify('Un pedido cancelado no puede ser marcado como pagado.', 'error');
    return;
  }

  try {
    const targetId = props.realId || props.orderId;
    await orderService.updateOrder(targetId, { id_estado_pago: 2 });
    localPaymentStatusId.value = 2;
    window.dispatchEvent(new Event('foodtruck-cash-transaction-update'));
    window.dispatchEvent(new Event('foodtruck-cash-session-update'));
    notify('¡Pedido marcado como PAGADO exitosamente!', 'success');
    emit('statusChanged');
    emit('status-changed');
  } catch (err: any) {
    console.error('Error al actualizar estado de pago:', err);
    notify('Error al marcar como pagado', 'error');
  }
};

// GESTIÓN DE NOTAS
const isSavingNotes = ref(false);
const notesSavedStatus = ref(false);
let notesDebounceTimer: any = null;

const onNotesInput = () => {
  notesSavedStatus.value = false;
  if (notesDebounceTimer) clearTimeout(notesDebounceTimer);
  notesDebounceTimer = setTimeout(() => {
    saveNotesNow();
  }, 1000);
};

const saveNotesNow = async () => {
  if (isInitializing.value) return;
  if (notesDebounceTimer) clearTimeout(notesDebounceTimer);

  isSavingNotes.value = true;
  try {
    const saved = localStorage.getItem(orderStorageKey.value);
    let parsed: any = {};
    if (saved) {
      try { parsed = JSON.parse(saved); } catch { parsed = {}; }
    }
    parsed.notas = orderNotes.value;
    localStorage.setItem(orderStorageKey.value, JSON.stringify(parsed));

    const targetId = props.realId || props.rawOrder?.id_pedido || props.rawOrder?.real_id || props.orderId;
    if (targetId) {
      await orderService.updateOrder(targetId, { notas: orderNotes.value });
    }
    notesSavedStatus.value = true;
    setTimeout(() => {
      notesSavedStatus.value = false;
    }, 2500);
  } catch (err) {
    console.error('Error al guardar notas:', err);
  } finally {
    isSavingNotes.value = false;
  }
};

// PAGO MIXTO
const isSplitPaymentModalOpen = ref(false);
const splitMethod1 = ref('Efectivo');
const splitAmount1 = ref<number>(0);
const splitMethod2 = ref('Tarjeta de Débito');
const splitAmount2 = ref<number>(0);

const isCustomPaymentMethod = computed(() => {
  const standard = ['Efectivo', 'Tarjeta de Débito', 'Tarjeta de Crédito', 'Transferencia'];
  return Boolean(currentPaymentMethod.value && !standard.includes(currentPaymentMethod.value));
});

const onPaymentSelectChange = () => {
  if (currentPaymentMethod.value === '__DIVIDIR_PAGO__') {
    openSplitPaymentEditor();
  } else {
    isSplitPaymentModalOpen.value = false;
    updatePaymentMethod();
  }
};

const openSplitPaymentEditor = () => {
  isSplitPaymentModalOpen.value = !isSplitPaymentModalOpen.value;
  if (isSplitPaymentModalOpen.value) {
    const match = currentPaymentMethod.value.match(/Pago Mixto \((.+?): \$([\d\.]+)\s*\/\s*(.+?): \$([\d\.]+)\)/);
    if (match) {
      splitMethod1.value = match[1].trim();
      splitAmount1.value = parseInt(match[2].replace(/\./g, ''), 10) || 0;
      splitMethod2.value = match[3].trim();
      splitAmount2.value = parseInt(match[4].replace(/\./g, ''), 10) || 0;
    } else {
      splitMethod1.value = 'Efectivo';
      splitAmount1.value = Math.floor(totalAmount.value / 2);
      splitMethod2.value = 'Tarjeta de Débito';
      splitAmount2.value = totalAmount.value - splitAmount1.value;
    }
  }
};

const splitTotalSum = computed(() => {
  return (Number(splitAmount1.value) || 0) + (Number(splitAmount2.value) || 0);
});

const splitDiff = computed(() => {
  return totalAmount.value - splitTotalSum.value;
});

const onSplitAmount1Input = () => {
  if (splitAmount1.value > totalAmount.value) {
    splitAmount1.value = totalAmount.value;
  }
  splitAmount2.value = Math.max(0, totalAmount.value - (splitAmount1.value || 0));
};

const setSplitFiftyFifty = () => {
  const half = Math.floor(totalAmount.value / 2);
  splitAmount1.value = half;
  splitAmount2.value = totalAmount.value - half;
};

const autoFillSplitRemainder = () => {
  splitAmount2.value = Math.max(0, totalAmount.value - (Number(splitAmount1.value) || 0));
};

const applySplitPayment = async () => {
  if (splitDiff.value !== 0) {
    notify('Los montos deben sumar exactamente el total del pedido.', 'warning');
    return;
  }
  if (!splitAmount1.value || !splitAmount2.value) {
    notify('Ambos métodos deben tener un monto asignado mayor a 0.', 'warning');
    return;
  }
  const formatted = `Pago Mixto (${splitMethod1.value}: $${formatCLP(splitAmount1.value)} / ${splitMethod2.value}: $${formatCLP(splitAmount2.value)})`;
  currentPaymentMethod.value = formatted;
  isSplitPaymentModalOpen.value = false;
  await updatePaymentMethod();
};

const updatePaymentMethod = async () => {
  if (localStatusId.value === 5) {
    notify('No se puede modificar el método de pago de un pedido cancelado.', 'error');
    return;
  }

  try {
    const targetId = props.realId || props.orderId;
    await orderService.updateOrder(targetId, { metodo_pago: currentPaymentMethod.value });
    window.dispatchEvent(new Event('foodtruck-cash-transaction-update'));
    window.dispatchEvent(new Event('foodtruck-cash-session-update'));
    notify(`Método de pago cambiado a: ${currentPaymentMethod.value}`, 'success');
    emit('statusChanged');
    emit('status-changed');
  } catch (err) {
    console.error('Error al actualizar método de pago:', err);
    notify('Error al cambiar método de pago', 'error');
  }
};

const cancelOrder = async () => {
  if (localStatusId.value === 4) {
    notify('Un pedido que ya ha sido entregado no puede ser cancelado.', 'error');
    return;
  }
  if (localStatusId.value === 5) {
    notify('Este pedido ya se encuentra cancelado.', 'warning');
    return;
  }

  if (!confirm('¿Estás seguro de cancelar este pedido?')) return;
  try {
    const targetId = props.realId || props.orderId;
    await orderService.updateOrder(targetId, { id_estado_pedido: 5, total: totalAmount.value });
    localStatusId.value = 5;
    localStatus.value = 'Cancelado';
    window.dispatchEvent(new Event('foodtruck-cash-transaction-update'));
    window.dispatchEvent(new Event('foodtruck-cash-session-update'));
    notify('Pedido marcado como CANCELADO', 'success');
    emit('statusChanged');
    emit('status-changed');
  } catch (err: any) {
    console.error('Error al cancelar pedido:', err);
    const msg = err?.response?.data?.error || err?.response?.data?.message || 'Error al cancelar el pedido';
    notify(msg, 'error');
  }
};

const setOrderStatus = async (newStatusId: number) => {
  if (newStatusId === localStatusId.value) return;

  if (localStatusId.value === 4 && newStatusId === 5) {
    notify('Un pedido que ya ha sido entregado no puede ser cancelado.', 'error');
    return;
  }

  if (localStatusId.value === 5) {
    notify('El pedido está cancelado y no puede cambiar de estado.', 'error');
    return;
  }

  const statusNames: Record<number, string> = {
    1: 'Pendiente',
    2: 'En preparación',
    3: 'Listo',
    4: 'Entregado',
    5: 'Cancelado'
  };

  try {
    const targetId = props.realId || props.orderId;
    await orderService.updateOrder(targetId, { id_estado_pedido: newStatusId, total: totalAmount.value });

    localStatusId.value = newStatusId;
    localStatus.value = statusNames[newStatusId] || 'Pendiente';

    notify(`Estado actualizado a: ${localStatus.value}`, 'success');
    emit('statusChanged');
    emit('status-changed');
  } catch (err: any) {
    console.error('Error al actualizar estado:', err);
    const msg = err?.response?.data?.error || err?.response?.data?.message || 'Error al cambiar estado';
    notify(msg, 'error');
  }
};

const stepStatus = (delta: number) => {
  const nextId = localStatusId.value + delta;
  if (nextId >= 1 && nextId <= 4) {
    setOrderStatus(nextId);
  }
};

const escapeHtml = (val: unknown): string => {
  return String(val ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
};

const printOrder = () => {
  const printWindow = window.open('', '_blank', 'width=800,height=900');
  if (!printWindow) return;
  try { printWindow.opener = null; } catch {}

  const safeOrderId = escapeHtml(props.orderId);
  const safeDate = escapeHtml(props.date || '-');
  const safeTime = escapeHtml(props.time || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' }));
  const safeClient = escapeHtml(props.distributor || 'Sin nombre');

  const content = `
    <html>
      <head>
        <title>Voucher Pedido #${safeOrderId}</title>
        <style>
          body { font-family: 'Courier New', monospace; margin: 0; padding: 18px; color: #000; }
          .voucher { width: 72mm; margin: 0 auto; }
          .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px; }
          .title { font-size: 18px; font-weight: 900; text-transform: uppercase; }
          .order-id { font-size: 14px; font-weight: 800; margin-top: 4px; }
          .time { font-size: 10px; color: #333; margin-top: 2px; }
          .product { margin: 8px 0; border-bottom: 1px dashed #000; padding-bottom: 6px; }
          .row { display: flex; justify-content: space-between; gap: 8px; align-items: flex-start; }
          .qty { font-weight: 900; }
          .ingredients { font-size: 10px; color: #444; margin-top: 2px; }
          .total { font-size: 13px; font-weight: 900; margin-top: 12px; border-top: 2px solid #000; padding-top: 6px; }
          .client { margin-top: 8px; font-size: 11px; }
          .footer { text-align: center; margin-top: 10px; font-size: 10px; border-top: 1px solid #000; padding-top: 6px; }
        </style>
      </head>
      <body>
        <div class="voucher">
          <div class="header">
            <div class="title">J.Junior FoodTruck</div>
            <div class="order-id">Pedido #${safeOrderId}</div>
            <div class="time">${safeDate} · ${safeTime}</div>
          </div>
          <div>
            ${products.value.map((p: any) => `
              <div class="product">
                <div class="row">
                  <div><span class="qty">${escapeHtml(p.quantity)}x</span> ${escapeHtml(p.name)}</div>
                  <div>$${escapeHtml(formatNumber(p.subtotal))}</div>
                </div>
                ${(p.removedIngredients || []).length ? `<div class="ingredients"><strong>SIN:</strong> ${(p.removedIngredients || []).map((ing: unknown) => escapeHtml(ing)).join(', ')}</div>` : ''}
                ${(p.addedExtras || []).length ? `<div class="ingredients"><strong>EXTRA:</strong> ${p.addedExtras.map((e: any) => `${escapeHtml(e.name)} (x${escapeHtml(e.quantity)})`).join(', ')}</div>` : ''}
              </div>
            `).join('')}
          </div>
          <div class="total">TOTAL: $${escapeHtml(formatNumber(totalAmount.value))}</div>
          <div class="client"><strong>Cliente:</strong> ${safeClient}</div>
          <div class="footer">Gracias por tu preferencia</div>
        </div>
      </body>
    </html>
  `;

  printWindow.document.write(content);
  printWindow.document.close();
  printWindow.focus();
  printWindow.print();
  printWindow.close();
};
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(35, 20, 10, 0.46);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 1.5rem 1rem;
}

.modal-container {
  width: min(880px, 100%);
  max-height: 90vh;
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 20px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 16px 48px rgba(26, 14, 5, 0.2);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

/* ===================== HEADER ===================== */
.modal-header {
  padding: 1.15rem 1.4rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  background: #ffffff;
}

.header-titles {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.title-with-badge {
  display: flex;
  align-items: center;
  gap: 8px;
}

.comanda-number {
  font-size: 1rem;
  font-weight: 900;
  color: #ffffff;
  background: var(--DC-orange, #e28743);
  padding: 2px 8px;
  border-radius: 6px;
  line-height: 1.2;
}

.modal-title {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.badges-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.status-badge {
  font-size: 0.7rem;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
  font-weight: 800;
  letter-spacing: 0.03em;
}

.status-pending { background: #fff4e6; color: #fd7e14; }
.status-preparation { background: rgba(81, 49, 25, 0.1); color: var(--DC-brown, #513119); }
.status-shipping { background: #e7f5ff; color: #1c7ed6; }
.status-completed { background: #dcfce7; color: #15803d; }
.status-cancelled { background: #fee2e2; color: #b91c1c; }
.status-generic { background: #f1f5f9; color: #475569; }

.status-paid {
  background-color: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.status-unpaid {
  background-color: #fff3e0;
  color: #e65100;
  border: 1px solid #ffcc80;
}

.btn-close {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.1);
  background: #ffffff;
  color: var(--DC-text-gray, #7c7468);
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-close:hover {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

/* ===================== CONTENIDO ===================== */
.modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.25rem 1.4rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.client-card,
.product-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.03);
}

.client-card {
  padding: 1rem 1.15rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.main-client-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
}

.client-name-box {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.95rem;
  color: var(--DC-brown, #513119);
}

.meta-icon {
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
}

.btn-whatsapp {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  background-color: #25d366;
  color: #ffffff;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 800;
  text-decoration: none;
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.btn-whatsapp:hover {
  background-color: #1ea952;
  transform: translateY(-1px);
}

.client-row {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.86rem;
  color: var(--DC-gray, #2c2724);
}

.payment-select-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.payment-lbl {
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
}

.payment-method-control-group {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.payment-method-select {
  padding: 0.35rem 0.75rem;
  border: 1px solid rgba(81, 49, 25, 0.15);
  border-radius: 8px;
  background: #ffffff;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  font-size: 0.82rem;
  outline: none;
}

.payment-method-select:focus {
  border-color: var(--DC-orange, #e28743);
}

.btn-split-toggle-mini {
  background: rgba(226, 135, 67, 0.12);
  border: 1px solid rgba(226, 135, 67, 0.35);
  color: var(--DC-orange, #e28743);
  padding: 0.35rem 0.75rem;
  border-radius: 8px;
  font-size: 0.78rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-split-toggle-mini:hover,
.btn-split-toggle-mini.active {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
}

/* PAGO MIXTO INLINE */
.split-payment-editor-card {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1.5px solid rgba(226, 135, 67, 0.35);
  border-radius: 12px;
  padding: 0.85rem 1rem;
  margin-top: 0.4rem;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.split-editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px dashed rgba(81, 49, 25, 0.12);
  padding-bottom: 6px;
}

.split-title {
  display: flex;
  align-items: center;
  gap: 6px;
  color: var(--DC-brown, #513119);
  font-size: 0.84rem;
}

.btn-close-split {
  background: none;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  padding: 2px;
}

.split-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.split-col {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.split-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
  text-transform: uppercase;
}

.split-select {
  padding: 0.35rem 0.55rem;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 8px;
  font-size: 0.78rem;
  background: #ffffff;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
}

.split-input-wrap {
  display: flex;
  align-items: center;
  background: #ffffff;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 8px;
  padding: 0 6px;
}

.currency-prefix {
  font-weight: 800;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
}

.split-amount-input {
  width: 100%;
  border: none;
  outline: none;
  padding: 0.4rem 0.25rem;
  font-size: 0.88rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
}

.split-divider-plus {
  font-size: 1.1rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  padding-top: 14px;
}

.split-quick-actions {
  display: flex;
  gap: 6px;
  margin-top: 4px;
}

.btn-split-quick {
  background: #ffffff;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 6px;
  padding: 2px 7px;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
  cursor: pointer;
}

.btn-split-quick:hover {
  background: #fff4e6;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
}

.split-summary-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 6px;
  padding-top: 6px;
  border-top: 1px dashed rgba(81, 49, 25, 0.1);
  font-size: 0.78rem;
}

.split-diff-badge {
  padding: 2px 7px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 800;
}

.diff-ok { background: #dcfce7; color: #15803d; }
.diff-missing { background: #fef3c7; color: #b45309; }
.diff-over { background: #fee2e2; color: #b91c1c; }

.btn-apply-split {
  width: 100%;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  padding: 0.5rem;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  margin-top: 4px;
}

.btn-apply-split:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* NOTAS DE COCINA */
.client-notes-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 4px;
  padding-top: 6px;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
}

.notes-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notes-header-row label {
  font-size: 0.8rem;
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  gap: 5px;
}

.notes-status-wrap {
  display: flex;
  align-items: center;
  gap: 6px;
}

.notes-status-indicator {
  font-size: 0.74rem;
  font-weight: 700;
}

.notes-status-indicator.saving { color: var(--DC-text-gray, #7c7468); }
.notes-status-indicator.saved { color: #16a34a; }

.btn-save-notes-mini {
  background: #ffffff;
  border: 1px solid rgba(81, 49, 25, 0.12);
  color: var(--DC-brown, #513119);
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-save-notes-mini:hover:not(:disabled) {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.order-notes-textarea {
  width: 100%;
  padding: 0.55rem 0.75rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.84rem;
  font-family: inherit;
  color: var(--DC-gray, #2c2724);
  resize: vertical;
  outline: none;
}

.order-notes-textarea:focus {
  border-color: var(--DC-orange, #e28743);
  background: #ffffff;
}

/* LISTADO DE PRODUCTOS */
.products-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.list-header h3 {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.btn-add-mini {
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  border: none;
  border-radius: 8px;
  padding: 0.4rem 0.75rem;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-weight: 800;
  font-size: 0.78rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-mini:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
}

.product-card {
  padding: 0.85rem 1rem;
}

.product-main {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-control {
  display: flex;
  align-items: center;
  gap: 4px;
}

.qty-control button {
  width: 26px;
  height: 26px;
  border-radius: 6px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  cursor: pointer;
  display: grid;
  place-items: center;
  font-weight: 800;
}

.qty-control button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.qty-num {
  min-width: 18px;
  text-align: center;
  font-weight: 800;
  font-size: 0.86rem;
  color: var(--DC-gray, #2c2724);
}

.product-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.product-info strong {
  font-size: 0.88rem;
  color: var(--DC-gray, #2c2724);
  line-height: 1.25;
}

.product-info small {
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
}

.price {
  font-weight: 900;
  color: var(--DC-brown, #513119);
  font-size: 0.95rem;
  white-space: nowrap;
}

.btn-trash {
  border: none;
  background: transparent;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: grid;
  place-items: center;
}

.btn-trash:hover {
  background: #ffe4e6;
  color: var(--DC-pink, #d80056);
}

.product-ingredients {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 8px;
  padding-top: 6px;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  border-radius: 6px;
  padding: 2px 7px;
  font-size: 0.72rem;
  font-weight: 800;
}

.chip button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
  display: flex;
  align-items: center;
  color: inherit;
}

.chip-removed {
  background: #fee2e2;
  color: #dc2626;
}

.chip-extra {
  background: #dbeafe;
  color: #1d4ed8;
}

.product-ingredients-empty {
  margin-top: 6px;
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
  font-style: italic;
}

/* BANNERS OPERATIVOS */
.order-rule-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-size: 0.82rem;
  line-height: 1.35;
}

.banner-cancelled {
  background: #fff5f5;
  border: 1px solid #fed7d7;
  color: #c53030;
}

.banner-delivered {
  background: #f0fdf4;
  border: 1px solid #bbf7d0;
  color: #15803d;
}

.rule-icon { flex-shrink: 0; }

/* STEPPER Y NAVEGACIÓN */
.timeline-container {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  padding: 1rem 1.15rem;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.timeline-steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
  position: relative;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  text-align: center;
}

.step-circle {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 2px solid #cbd5e1;
  color: #64748b;
  display: grid;
  place-items: center;
  font-size: 0.78rem;
  font-weight: 800;
  transition: all 0.2s ease;
}

.timeline-step.completed .step-circle {
  background: #16a34a;
  border-color: #16a34a;
  color: #ffffff;
}

.timeline-step.active .step-circle {
  background: #ffffff;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.2);
}

.step-label {
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

.timeline-step.active .step-label {
  color: var(--DC-orange, #e28743);
  font-weight: 800;
}

.status-navigation {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 10px;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
}

.btn-step {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.45rem 0.85rem;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: #ffffff;
  color: var(--DC-brown, #513119);
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-step:hover:not(:disabled) {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.btn-step:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-pay {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.45rem 0.95rem;
  border-radius: 8px;
  border: none;
  background: #16a34a;
  color: #ffffff;
  font-size: 0.8rem;
  font-weight: 800;
  cursor: pointer;
}

.btn-pay:hover:not(:disabled) {
  background: #15803d;
}

.badge-paid-confirmed {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  background: #dcfce7;
  color: #15803d;
  font-size: 0.78rem;
  font-weight: 800;
}

.btn-cancel-order {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.45rem 0.85rem;
  border-radius: 8px;
  border: 1px solid #fecaca;
  background: #ffffff;
  color: #dc2626;
  font-size: 0.8rem;
  font-weight: 800;
  cursor: pointer;
}

.btn-cancel-order:hover:not(:disabled) {
  background: #dc2626;
  color: #ffffff;
}

.badge-cancelled-confirmed {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.4rem 0.85rem;
  border-radius: 999px;
  background: #fee2e2;
  color: #dc2626;
  font-size: 0.78rem;
  font-weight: 800;
}

.btn-disabled-rule {
  opacity: 0.45 !important;
  cursor: not-allowed !important;
}

/* ===================== FOOTER ===================== */
.modal-footer {
  padding: 1rem 1.4rem;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  gap: 12px;
  align-items: center;
  background: #ffffff;
}

.footer-total {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 10px;
  padding: 0.65rem 1rem;
}

.footer-total-label {
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.footer-total-val {
  font-size: 1.25rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.btn-footer-wa {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0.65rem 1.15rem;
  border-radius: 10px;
  background-color: #25d366;
  color: #ffffff;
  font-size: 0.84rem;
  font-weight: 700;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-footer-wa:hover {
  background-color: #1ea952;
  transform: translateY(-1px);
  color: #ffffff;
}

.btn-print {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0.65rem 1.15rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: #ffffff;
  color: var(--DC-brown, #513119);
  font-size: 0.84rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-print:hover {
  background: var(--DC-bg-gray, #f8f6f3);
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
}

/* ===================== RESPONSIVIDAD ===================== */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 0.5rem;
  }

  .modal-container {
    max-height: 94vh;
    border-radius: 16px;
  }

  .modal-header {
    padding: 0.85rem 1rem;
  }

  .modal-content {
    padding: 0.85rem 1rem;
    gap: 0.75rem;
  }

  .main-client-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .btn-whatsapp {
    width: 100%;
    justify-content: center;
  }

  .payment-select-row {
    flex-direction: column;
    align-items: stretch;
  }

  .payment-method-control-group {
    width: 100%;
  }

  .payment-method-select {
    flex: 1;
  }

  .split-row {
    flex-direction: column;
    gap: 6px;
  }

  .split-divider-plus {
    padding-top: 0;
  }

  .product-main {
    flex-wrap: wrap;
  }

  .product-info {
    width: 100%;
    order: -1;
  }

  .price {
    margin-left: auto;
  }

  .status-navigation {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
  }

  .btn-step, .btn-pay, .btn-cancel-order, .badge-paid-confirmed, .badge-cancelled-confirmed {
    width: 100%;
    justify-content: center;
  }

  .modal-footer {
    padding: 0.75rem 1rem;
    flex-direction: column;
    gap: 6px;
  }

  .footer-total, .footer-actions, .btn-print {
    width: 100%;
    justify-content: center;
  }
}
</style>