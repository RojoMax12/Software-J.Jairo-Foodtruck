<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <header class="modal-header">
        <div class="header-titles">
          <h2 class="modal-title">Pedido #{{ orderId }}</h2>
          <div class="badges-row">
            <span class="status-badge" :class="getStatusClass(localStatusId)">{{ localStatus }}</span>
            <span class="status-badge" :class="localPaymentStatusId === 2 ? 'status-paid' : 'status-unpaid'">
              {{ localPaymentStatusId === 2 ? 'PAGADO' : 'POR PAGAR' }}
            </span>
          </div>
        </div>
        <div class="header-actions">
          <button class="btn-close" @click="handleClose"><X /></button>
        </div>
      </header>

      <div class="modal-content">

        <div class="client-card">
          <div class="client-row main-client-row">
            <div><User :size="16" /> <strong>{{ distributor || rawOrder?.nombre_persona || 'Cliente' }}</strong></div>
            <a v-if="whatsappUrl" :href="whatsappUrl" target="_blank" class="btn-whatsapp" title="Enviar WhatsApp al cliente">
              <MessageCircle :size="16" /> <span>WhatsApp</span>
            </a>
          </div>
          <div class="client-row"><Phone :size="16" /> {{ customerPhone || 'Sin teléfono' }}</div>
          <div class="client-row payment-select-row">
            <DollarSign :size="16" /> 
            <span>Método de pago:</span>
            <select v-model="currentPaymentMethod" class="payment-method-select" @change="updatePaymentMethod">
              <option value="Efectivo">Efectivo</option>
              <option value="Tarjeta de Débito">Tarjeta de Débito</option>
              <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
              <option value="Transferencia">Transferencia</option>
            </select>
          </div>
          <div class="client-notes-section">
            <label><FileText :size="14" /> <strong>Notas / Instrucciones especiales:</strong></label>
            <textarea 
              v-model="orderNotes" 
              placeholder="Ej: Sin mayonesa en las papas, sin servilletas, retiro 21:30..." 
              class="order-notes-textarea"
              rows="2"
            ></textarea>
          </div>
        </div>

        <div class="products-list">
          <div class="list-header">
            <h3>Productos ({{ products.length }})</h3>
            <button class="btn-add-mini" @click="openAddModal">
              <Plus :size="14" /> Agregar
            </button>
          </div>

          <div v-for="product in products" :key="product.id" class="product-card">
            <div class="product-main">
              <div class="qty-control">
                <button :disabled="product.quantity <= 1" @click="changeQty(product, -1)">-</button>
                <span class="qty-num">{{ product.quantity }}</span>
                <button @click="changeQty(product, 1)">+</button>
              </div>
              <div class="product-info">
                <strong>{{ product.name }}</strong>
                <small>{{ formato(product.format) }}</small>
              </div>
              <span class="price">${{ formatNumber(product.subtotal) }}</span>
              <button class="btn-trash" @click="removeProduct(product.id)"><Trash2 :size="16" /></button>
            </div>

            <div v-if="(product.removedIngredients || []).length || (product.addedExtras || []).length" class="product-ingredients">
              <span v-for="ing in product.removedIngredients || []" :key="ing" class="chip chip-removed">
                Sin: {{ ing }}
                <button @click="toggleRemovedIngredient(product.id, ing)"><X :size="10" /></button>
              </span>
              <span v-for="extra in product.addedExtras || []" :key="extra.name" class="chip chip-extra">
                + {{ extra.name }} (x{{ extra.quantity }})
                <button @click="removeExtraFromProduct(product.id, extra.name)"><X :size="10" /></button>
              </span>
            </div>
            <div v-else class="product-ingredients-empty">Sin ajustes de ingredientes</div>
          </div>
        </div>
        <!-- AVISOS OPERATIVOS CLAROS PARA LOS TRABAJADORES -->
        <div v-if="localStatusId === 5" class="order-rule-banner banner-cancelled animate-fade-in">
          <AlertTriangle :size="18" class="rule-icon" />
          <div class="rule-text">
            <strong>Pedido Cancelado:</strong> Este pedido fue cancelado. No es posible registrar pagos ni reactivar su preparación.
          </div>
        </div>
        <div v-else-if="localStatusId === 4" class="order-rule-banner banner-delivered animate-fade-in">
          <CheckCircle :size="18" class="rule-icon" />
          <div class="rule-text">
            <strong>Pedido Entregado:</strong> El pedido ya fue entregado exitosamente al cliente. Por políticas del local, no se puede cancelar.
          </div>
        </div>

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

          <!-- BOTONES DE NAVEGACIÓN, PAGO Y CANCELACIÓN -->
          <div class="status-navigation">
            <button 
              class="btn-step" 
              :disabled="localStatusId <= 1 || localStatusId === 5" 
              @click="stepStatus(-1)"
            >
              <ChevronLeft :size="16" /> Anterior
            </button>

            <button 
              v-if="localPaymentStatusId !== 2" 
              class="btn-pay" 
              :disabled="localStatusId === 5"
              :class="{ 'btn-disabled-rule': localStatusId === 5 }"
              :title="localStatusId === 5 ? 'Un pedido cancelado no puede ser marcado como pagado' : 'Marcar como Pagado'"
              @click="markAsPaid"
            >
              <DollarSign :size="16" /> Marcar como Pagado
            </button>
            <span v-else class="badge-paid-confirmed">
              <CheckCircle :size="16" /> Pagado
            </span>

            <button 
              v-if="localStatusId !== 5"
              class="btn-cancel-order"
              :disabled="localStatusId === 4"
              :class="{ 'btn-disabled-rule': localStatusId === 4 }"
              :title="localStatusId === 4 ? 'Un pedido que ya ha sido entregado no puede ser cancelado' : 'Cancelar Pedido'"
              @click="cancelOrder"
            >
              <XCircle :size="16" /> Cancelar Pedido
            </button>
            <span v-else class="badge-cancelled-confirmed">
              <XCircle :size="16" /> Cancelado
            </span>

            <button 
              class="btn-step" 
              :disabled="localStatusId >= 4 || localStatusId === 5" 
              @click="stepStatus(1)"
            >
              Siguiente <ChevronRight :size="16" />
            </button>
          </div>
        </div>
      </div>


      <footer class="modal-footer">
        <div class="footer-total">
          <span>Total</span>
          <strong>${{ formatNumber(totalAmount) }}</strong>
        </div>
        <div class="footer-actions">
          <button class="btn-secondary" @click="printOrder"><Printer /></button>
        </div>
      </footer>
    </div>

    <div v-if="isAddModalOpen" class="submodal-overlay" @click.self="closeAddModal">
      <div class="submodal-card">
        <div class="submodal-header">
          <div>
            <h3>Agregar producto</h3>
            <p>Elige un producto y ajusta los ingredientes</p>
          </div>
          <button class="btn-close btn-small" @click="closeAddModal"><X :size="18" /></button>
        </div>

        <div class="submodal-body">
          <div class="picker-section">
            <p class="section-title">Selecciona un producto</p>
            <div class="filter-row">
              <input
                v-model="searchQuery"
                type="text"
                class="product-search"
                placeholder="Buscar por nombre o categoría"
              />
            </div>
            <div class="pill-group">
              <button
                v-for="category in uniqueCategories"
                :key="category"
                class="pill"
                :class="{ active: activeCategory === category }"
                @click="activeCategory = category"
              >
                {{ category === 'all' ? 'Todos' : category }}
              </button>
            </div>
            <div v-if="isLoadingCatalog" class="product-options">
              <div v-for="n in 4" :key="'cat-skel-' + n" class="product-option-skeleton">
                <div class="skeleton-pill width-120"></div>
                <div class="skeleton-pill width-70 margin-top-4"></div>
              </div>
            </div>
            <div v-else-if="filteredCatalogProducts.length" class="product-options">
              <button
                v-for="item in filteredCatalogProducts"
                :key="item.id"
                class="product-option animate-fade-in"
                :class="{ active: selectedCatalogProduct?.id === item.id }"
                @click="selectCatalogProduct(item)"
              >
                <span class="option-name">{{ item.name }}</span>
                <span class="option-meta">{{ item.category }}</span>
              </button>
            </div>
            <div v-else class="empty-products">No se encontraron productos con esos filtros.</div>
          </div>

          <div v-if="selectedCatalogProduct && selectedCatalogProduct.sizes?.length" class="picker-section">
            <p class="section-title">Tamaño</p>
            <div class="pill-group">
              <button
                v-for="size in selectedCatalogProduct.sizes"
                :key="typeof size === 'object' ? size.id || size.name : size"
                class="pill size-pill"
                :class="{ active: selectedSize === (typeof size === 'object' ? size.name : size) }"
                @click="selectedSize = typeof size === 'object' ? size.name : size"
              >
                <span class="size-name">{{ typeof size === 'object' ? size.name : size }}</span>
                <span v-if="typeof size === 'object' && size.price" class="size-price">${{ formatNumber(size.price) }}</span>
              </button>
            </div>
          </div>

          <!-- RECETA BASE (SOLO PARA PRODUCTOS ESTÁNDAR: VIANESAS, ASS, CHURRASCOS, LOMITOS, ETC.) -->
          <div v-if="selectedCatalogProduct && selectedCatalogProduct.tipo_armado !== 'Personalizable' && customizableBaseIngredients.length" class="picker-section">
            <div class="section-header-row">
              <p class="section-title">Ingredientes de la receta base</p>
              <span class="section-subtitle">
                Desmarca los ingredientes que el cliente desea excluir de este producto
              </span>
            </div>
            <div class="ingredients-grid">
              <label
                v-for="ingredient in customizableBaseIngredients"
                :key="ingredient.id || ingredient.name"
                class="ingredient-card"
                :class="{ removed: excludedIngredients.includes(ingredient.name) }"
              >
                <div class="ingredient-info">
                  <input
                    type="checkbox"
                    class="ingredient-checkbox"
                    :checked="!excludedIngredients.includes(ingredient.name)"
                    @change="toggleIngredient(ingredient.name)"
                  />
                  <span class="ingredient-name">{{ ingredient.name }}</span>
                </div>
                <span
                  class="ingredient-status-badge"
                  :class="excludedIngredients.includes(ingredient.name) ? 'status-removed' : 'status-included'"
                >
                  {{ excludedIngredients.includes(ingredient.name) ? 'Quitado' : 'Incluido' }}
                </span>
              </label>
            </div>
          </div>

          <!-- INGREDIENTES A ELECCIÓN (SOLO PARA PRODUCTOS PERSONALIZABLES: HAMBURGUESAS, PIZZAS Y FAJITAS) -->
          <div v-if="selectedCatalogProduct && selectedCatalogProduct.tipo_armado === 'Personalizable' && displayOptionalIngredients.length" class="picker-section">
            <div class="section-header-row">
              <p class="section-title">
                Ingredientes a elección
                <span v-if="selectedCatalogProduct.cantidad_incluida > 0" class="badge-included-info">
                  ({{ selectedCatalogProduct.cantidad_incluida }} incluidos gratis)
                </span>
              </p>
              <span class="section-subtitle">
                Has seleccionado {{ selectedOptionalIngredients.length }} ingrediente(s).
                <template v-if="extraChargeableCount > 0">
                  ({{ extraChargeableCount }} extra a +${{ formatNumber(selectedCatalogProduct.precio_ingrediente_extra) }} c/u)
                </template>
              </span>
            </div>

            <div class="ingredients-grid">
              <label
                v-for="ingredient in displayOptionalIngredients"
                :key="ingredient.id || ingredient.name"
                class="ingredient-card"
                :class="{ selected: selectedOptionalIngredients.includes(ingredient.name) }"
              >
                <div class="ingredient-info">
                  <input
                    type="checkbox"
                    class="ingredient-checkbox"
                    :checked="selectedOptionalIngredients.includes(ingredient.name)"
                    @change="toggleOptionalIngredient(ingredient.name)"
                  />
                  <span class="ingredient-name">{{ ingredient.name }}</span>
                </div>
                <span
                  class="ingredient-status-badge"
                  :class="selectedOptionalIngredients.includes(ingredient.name) ? 'status-included' : 'status-optional'"
                >
                  {{ selectedOptionalIngredients.includes(ingredient.name) ? 'Seleccionado' : 'Opcional' }}
                </span>
              </label>
            </div>
          </div>

          <div v-if="selectedCatalogProduct" class="picker-section">
            <p class="section-title">Cantidad</p>
            <div class="quantity-selector">
              <button class="quantity-btn" :disabled="addQuantity <= 1" @click="decreaseAddQuantity">-</button>
              <span class="quantity-value">{{ addQuantity }}</span>
              <button class="quantity-btn" @click="increaseAddQuantity">+</button>
            </div>
          </div>
        </div>

        <div class="submodal-footer">
          <div class="summary-box">
            <span>Total estimado</span>
            <strong>${{ formatNumber(previewPrice) }}</strong>
          </div>
          <button class="btn-primary" @click="confirmAddProduct">Agregar al pedido</button>
        </div>
      </div>
    </div>
  </div>

  <div class="print-only" aria-hidden="true">
    <div class="print-header">
      <div class="print-title">J.Junior FoodTruck</div>
      <div class="print-order-id">Comanda Pedido #{{ orderId }}</div>
      <div class="print-time">{{ date || '-' }} · {{ time || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' }) }}</div>
    </div>

    <div class="print-body">
      <div v-for="p in products" :key="p.id" class="print-product">
        <div class="print-product-line">
          <div class="print-product-name">
            <span class="print-qty">{{ p.quantity }}x</span>
            <strong>{{ p.name }}</strong> ({{ p.format }})
          </div>
          <span class="print-price">${{ formatNumber(p.subtotal) }}</span>
        </div>
        <div v-if="(p.removedIngredients || []).length > 0" class="print-ingredients print-removed">
          <strong>SIN:</strong> {{ (p.removedIngredients || []).join(', ') }}
        </div>
        <div v-if="(p.addedExtras || []).length > 0" class="print-ingredients print-extras">
          <strong>EXTRA:</strong> {{ p.addedExtras.map((e: any) => `${e.name} (x${e.quantity})`).join(', ') }}
        </div>
      </div>
    </div>

    <div v-if="orderNotes" class="print-notes">
      <strong>NOTAS DE COCINA:</strong> {{ orderNotes }}
    </div>

    <div class="print-total">
      TOTAL: ${{ formatNumber(totalAmount) }}
    </div>

    <div class="print-client">
      <strong>Cliente:</strong> {{ distributor || 'Sin nombre' }} · Tel: {{ customerPhone || 'Sin tel' }}
    </div>

    <div class="print-footer">
      ¡Gracias por tu preferencia!
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { X, Phone, Printer, Plus, Trash2, User, Check, CheckCircle, ChevronLeft, ChevronRight, DollarSign, XCircle, MessageCircle, FileText, AlertTriangle } from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification';
import orderService from '@/services/orderService';
import productService from '@/services/productService';
import stockService from '@/services/stockService';

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
const orderStorageKey = computed(() => `order-${props.orderId}`);

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

  const text = encodeURIComponent(`Hola ${props.distributor || 'cliente'}, tu pedido #${props.orderId} en J.Junior Foodtruck está listo! 🍔`);
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
    if (newOrder.notas !== undefined) orderNotes.value = newOrder.notas || '';

    let detailsList = Array.isArray(newOrder.detalles) ? newOrder.detalles : [];

    // Si los detalles vienen vacíos o sin ingredientes cargados, buscar pedido fresco desde la API
    const targetOrderId = props.realId || props.orderId;
    if (targetOrderId && (detailsList.length === 0 || detailsList.some((d: any) => !d.ingredientes && !d.producto))) {
      try {
        const res = await orderService.getPublicOrderById(targetOrderId).catch(() => orderService.getOrderById(targetOrderId));
        const fullOrder = res?.data?.data || res?.data;
        if (fullOrder && Array.isArray(fullOrder.detalles) && fullOrder.detalles.length > 0) {
          detailsList = fullOrder.detalles;
        }
      } catch (err) {
        console.error('Error fetching order details in modal:', err);
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

// Cargar productos del catálogo
const loadCatalogProducts = async () => {
  isLoadingCatalog.value = true;
  try {
    const response = await productService.getPublicProducts();
    const rawProducts = Array.isArray(response?.data) ? response.data : (response?.data?.data || []);
    const activeRawProducts = rawProducts.filter((p: any) => {
      const isActivo = p.activo !== false && p.activo !== 0 && p.active !== false;
      const isDisponible = p.disponible !== false && p.disponible !== 0 && p.inStock !== false;
      const isEstadoOk = p.estado !== 0;
      return isActivo && isDisponible && isEstadoOk;
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

    if (catalogProducts.value.length > 0 && !selectedCatalogProduct.value) {
      selectCatalogProduct(catalogProducts.value[0]);
    }
  } catch (error) {
    console.error('Error al cargar productos:', error);
  } finally {
    isLoadingCatalog.value = false;
  }
};

const selectedCatalogProduct = ref<any>(catalogProducts.value[0]);
const selectedSize = ref('Normal');
const excludedIngredients = ref<string[]>([]);
const addQuantity = ref(1);
const searchQuery = ref('');
const activeCategory = ref('all');

const uniqueCategories = computed(() => ['all', ...new Set(catalogProducts.value.map((product: any) => product.category))]);
const filteredCatalogProducts = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();
  return catalogProducts.value.filter((product: any) => {
    const matchesCategory = activeCategory.value === 'all' || product.category === activeCategory.value;
    const matchesQuery = !query || product.name.toLowerCase().includes(query) || product.category.toLowerCase().includes(query);
    return matchesCategory && matchesQuery;
  });
});

watch(filteredCatalogProducts, (items) => {
  if (!items.length) {
    selectedCatalogProduct.value = null;
    return;
  }

  if (!selectedCatalogProduct.value || !items.some((item: any) => item.id === selectedCatalogProduct.value?.id)) {
    selectCatalogProduct(items[0]);
  }
});

const totalAmount = computed(() => {
  return products.value.reduce((acc: number, p: any) => acc + (Number(p.subtotal) || 0), 0);
});

const formatNumber = (n: any) => {
  const num = typeof n === 'object' && n !== null && 'value' in n ? Number(n.value) : Number(n);
  return isNaN(num) ? '0' : num.toLocaleString('es-CL');
};
const formato = (f: string) => f;

const saveOrder = (showNotification = true) => {
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

  const targetId = props.realId || props.orderId;
  if (targetId) {
    const payload: any = {
      total: currentTotal,
      id_estado_pedido: localStatusId.value,
      id_estado_pago: localPaymentStatusId.value,
      metodo_pago: currentPaymentMethod.value,
      notas: orderNotes.value,
    };

    // Solo enviar el desglose de items al backend si los productos o sus ingredientes cambiaron efectivamente
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

    orderService.updateOrder(targetId, payload).catch(err => {
      console.error('Error al actualizar total de pedido en backend:', err);
    });
  }

  hasPendingChanges.value = false;
  if (showNotification) {
    notify('Pedido guardado', 'success');
  }
};

const loadSavedOrder = () => {
  const saved = localStorage.getItem(orderStorageKey.value);
  if (!saved) return;

  try {
    const parsed = JSON.parse(saved);
    if (parsed.products) {
      products.value = parsed.products;
    }
    if (parsed.status) localStatus.value = parsed.status;
    if (parsed.statusId) localStatusId.value = parsed.statusId;
    hasPendingChanges.value = false;
  } catch {
    localStorage.removeItem(orderStorageKey.value);
  }
};

const stockIngredients = ref<any[]>([]);
const extraIngredients = ref<Array<{ name: string, quantity: number, unitPrice: number }>>([]);

const defaultExtras = [
  { name: 'Extra queso', precio: 500 },
  { name: 'Tocino', precio: 600 },
  { name: 'Palta extra', precio: 500 },
  { name: 'Papas hilo', precio: 400 },
  { name: 'Huevo frito', precio: 500 },
  { name: 'Salsa BBQ', precio: 300 },
  { name: 'Cebolla caramelizada', precio: 400 },
  { name: 'Champiñón', precio: 500 }
];

const BASE_INGREDIENT_NAMES = [
  'pan', 'pan completo', 'pan frica', 'pan marraqueta', 'pan chico', 'pan grande', 'pan xl', 'pan mediano',
  'vianesa', 'carne', 'hamburguesa', 'pollo', 'lomo', 'lomito',
  'churrasco', 'masa', 'masa pizza'
];

const isBaseIngredient = (nombre: string) => {
  if (!nombre) return false;
  const lower = nombre.toLowerCase().trim();
  return lower.startsWith('pan ') ||
         lower === 'pan' ||
         lower === 'vianesa' ||
         lower === 'carne' ||
         lower === 'lomito' ||
         lower === 'pollo' ||
         lower === 'hamburguesa' ||
         lower === 'masa pizza' ||
         lower === 'sopaipilla' ||
         lower === 'empanada' ||
         BASE_INGREDIENT_NAMES.some(b => lower.includes(b));
};

const isProtectedIngredient = (name: string) => isBaseIngredient(name);

const customizableBaseIngredients = computed(() => {
  if (!selectedCatalogProduct.value) return [];
  const base = selectedCatalogProduct.value.baseIngredients || selectedCatalogProduct.value.ingredients || [];
  return base.filter((ing: any) => {
    const name = typeof ing === 'object' ? ing.name : ing;
    return !isBaseIngredient(name);
  });
});

const displayOptionalIngredients = computed(() => {
  if (!selectedCatalogProduct.value) return [];
  const optionals = selectedCatalogProduct.value.optionalIngredients || [];
  if (optionals.length > 0) {
    return optionals.filter((ing: any) => !isBaseIngredient(ing.name));
  }
  if (stockIngredients.value.length > 0) {
    const baseNames = (selectedCatalogProduct.value.baseIngredients || []).map((b: any) => b.name);
    return stockIngredients.value
      .filter((s: any) => {
        const name = s.nombre || s.name;
        return !isBaseIngredient(name) && !baseNames.includes(name);
      })
      .map((s: any) => ({
        id: s.id_ingrediente || s.id,
        name: s.nombre || s.name
      }));
  }
  return defaultExtras
    .filter(e => !isBaseIngredient(e.name))
    .map(e => ({ id: e.name, name: e.name }));
});

const selectedOptionalIngredients = ref<string[]>([]);

const toggleOptionalIngredient = (name: string) => {
  const index = selectedOptionalIngredients.value.indexOf(name);
  if (index > -1) {
    selectedOptionalIngredients.value.splice(index, 1);
  } else {
    selectedOptionalIngredients.value.push(name);
  }
};

const extraChargeableCount = computed(() => {
  if (!selectedCatalogProduct.value) return 0;
  const includedCount = selectedCatalogProduct.value.cantidad_incluida || 0;
  const selectedCount = selectedOptionalIngredients.value.length;
  return Math.max(0, selectedCount - includedCount);
});

const extrasTotalPrice = computed(() => {
  if (!selectedCatalogProduct.value) return 0;
  const pricePerExtra = selectedCatalogProduct.value.precio_ingrediente_extra || 0;
  return extraChargeableCount.value * pricePerExtra;
});

const loadStockIngredients = async () => {
  try {
    const res = await stockService.getStocks();
    const list = Array.isArray(res?.data) ? res.data : (res?.data?.data || []);
    stockIngredients.value = list;
  } catch (e) {
    console.error('Error al cargar lista de stock para opcionales:', e);
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

const handleClose = () => {
  saveOrder(false);
  emit('statusChanged');
  emit('status-changed');
  emit('close');
};

const changeQty = (p: any, delta: number) => {
  const currentQuantity = Number(p.quantity || 1);
  const nextQuantity = currentQuantity + delta;

  if (nextQuantity < 1) {
    return;
  }

  const unitPrice = currentQuantity > 0 ? (Number(p.subtotal || 0) / currentQuantity) : 0;
  p.quantity = nextQuantity;
  p.subtotal = Math.round(nextQuantity * unitPrice);
  hasPendingChanges.value = true;
  saveOrder(false);
};

const openAddModal = () => {
  isAddModalOpen.value = true;
  searchQuery.value = '';
  activeCategory.value = 'all';
  selectedCatalogProduct.value = catalogProducts.value[0];
  selectedSize.value = selectedCatalogProduct.value?.sizes?.[0] || 'Normal';
  excludedIngredients.value = [];
  selectedOptionalIngredients.value = [];
  addQuantity.value = 1;
};

const closeAddModal = () => {
  isAddModalOpen.value = false;
};

const currentSizeObj = computed(() => {
  if (!selectedCatalogProduct.value?.sizes) return null;
  return selectedCatalogProduct.value.sizes.find((s: any) => {
    const sName = typeof s === 'object' ? s.name : s;
    return sName === selectedSize.value;
  }) || selectedCatalogProduct.value.sizes[0];
});

const selectCatalogProduct = (product: any) => {
  selectedCatalogProduct.value = product;
  const firstSize = product.sizes?.[0];
  selectedSize.value = typeof firstSize === 'object' ? firstSize.name : (firstSize || 'Normal');
  excludedIngredients.value = [];
  selectedOptionalIngredients.value = [];
  addQuantity.value = 1;
};

const toggleIngredient = (name: string) => {
  const index = excludedIngredients.value.indexOf(name);
  if (index > -1) {
    excludedIngredients.value.splice(index, 1);
  } else {
    excludedIngredients.value.push(name);
  }
};

const increaseAddQuantity = () => addQuantity.value++;
const decreaseAddQuantity = () => {
  if (addQuantity.value > 1) addQuantity.value--;
};

const previewPrice = computed(() => {
  if (!selectedCatalogProduct.value) return 0;
  const sizeObj = currentSizeObj.value;
  const baseUnitPrice = typeof sizeObj === 'object' ? (sizeObj.price || 0) : (selectedCatalogProduct.value.basePrice || 0);
  const singleUnitPrice = baseUnitPrice + extrasTotalPrice.value;
  return singleUnitPrice * addQuantity.value;
});

const normalizeRemovedIngredients = (items: string[] = []) => [...new Set(items)].sort();

const confirmAddProduct = () => {
  if (!selectedCatalogProduct.value) return;

  const sizeObj = currentSizeObj.value;
  const sizeName = typeof sizeObj === 'object' ? sizeObj.name : (selectedSize.value || 'Normal');
  const baseUnitPrice = typeof sizeObj === 'object' ? (sizeObj.price || 0) : (selectedCatalogProduct.value.basePrice || 0);
  const singleUnitPrice = baseUnitPrice + extrasTotalPrice.value;
  const normalizedRemoved = normalizeRemovedIngredients(excludedIngredients.value);
  const selectedExtrasList = [...selectedOptionalIngredients.value];

  const existingProduct = products.value.find((p: any) => {
    const sameCatalog = Number(p.catalogId ?? p.id) === Number(selectedCatalogProduct.value.id);
    const sameFormat = p.format === `${sizeName}`;
    const sameRemoved = JSON.stringify(normalizeRemovedIngredients(p.removedIngredients || [])) === JSON.stringify(normalizedRemoved);
    const sameExtras = JSON.stringify(p.addedExtras || []) === JSON.stringify(selectedExtrasList.map(name => ({ name, quantity: 1, price: 0 })));
    return sameCatalog && sameFormat && sameRemoved && sameExtras;
  });

  if (existingProduct) {
    existingProduct.quantity += addQuantity.value;
    existingProduct.subtotal = Number((existingProduct.quantity * singleUnitPrice).toFixed(0));
  } else {
    products.value.push({
      id: Date.now(),
      catalogId: selectedCatalogProduct.value.id,
      name: selectedCatalogProduct.value.name,
      format: `${sizeName}`,
      quantity: addQuantity.value,
      subtotal: Number((addQuantity.value * singleUnitPrice).toFixed(0)),
      removedIngredients: normalizedRemoved,
      addedExtras: selectedExtrasList.map(name => ({ name, quantity: 1, price: 0 }))
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
  const map: any = {
    1: 'status-pending',
    2: 'status-preparation',
    3: 'status-completed',
    4: 'status-delivered',
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
    const msg = err?.response?.data?.error || err?.response?.data?.message || 'Error al marcar como pagado';
    notify(msg, 'error');
  }
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
  } catch (err: any) {
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
    const msg = err?.response?.data?.error || err?.response?.data?.message || 'Error al cambiar el estado del pedido';
    notify(msg, 'error');
  }
};

const stepStatus = (delta: number) => {
  const nextId = localStatusId.value + delta;
  if (nextId >= 1 && nextId <= 4) {
    setOrderStatus(nextId);
  }
};

const printOrder = () => {
  const printWindow = window.open('', '_blank', 'width=800,height=900');
  if (!printWindow) return;

  const content = `
    <html>
      <head>
        <title>Voucher Pedido #${props.orderId}</title>
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
            <div class="order-id">Pedido #${props.orderId}</div>
            <div class="time">${props.date || '-'} · ${props.time || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })}</div>
          </div>
          <div>
            ${products.value.map((p: any) => `
              <div class="product">
                <div class="row">
                  <div><span class="qty">${p.quantity}x</span> ${p.name}</div>
                  <div>$${formatNumber(p.subtotal)}</div>
                </div>
                ${(p.removedIngredients || []).length ? `<div class="ingredients"><strong>SIN:</strong> ${(p.removedIngredients || []).join(', ')}</div>` : ''}
              </div>
            `).join('')}
          </div>
          <div class="total">TOTAL: $${formatNumber(totalAmount.value)}</div>
          <div class="client"><strong>Cliente:</strong> ${props.distributor || 'Sin nombre'}</div>
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

const contactClient = () => window.open(`tel:${props.phone}`);
</script>

<style scoped>
.print-only {
  display: none;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(30, 27, 36, 0.65);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 24px;
}

.modal-container {
  width: min(900px, 100%);
  max-height: 90vh;
  background: #f5ebe0;
  border-radius: 24px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.18);
}

.modal-header {
  padding: 20px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
  background: white;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-contact {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 999px;
}

.modal-title {
  margin: 0;
  font-size: 1.1rem;
  color: #2f2a2a;
}

.modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px 24px 8px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.client-card,
.product-card,
.submodal-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(6px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.25s ease-out forwards;
}

.product-option-skeleton {
  padding: 12px 16px;
  background: white;
  border-radius: 12px;
  border: 1px solid #eeedee;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton-pill {
  height: 16px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-70 { width: 70px; }
.width-120 { width: 120px; }
.margin-top-4 { margin-top: 4px; }

.client-card {
  padding: 14px 16px;
  display: grid;
  gap: 6px;
}

.main-client-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-whatsapp {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 14px;
  background-color: #25d366;
  color: white;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 800;
  text-decoration: none;
  transition: transform 0.2s, background-color 0.2s;
}

.btn-whatsapp:hover {
  background-color: #128c7e;
  transform: translateY(-1px);
}

.client-notes-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-top: 8px;
  padding-top: 8px;
  border-top: 1px dashed #e9ecef;
}

.client-notes-section label {
  font-size: 0.85rem;
  color: var(--DC-gray);
  display: flex;
  align-items: center;
  gap: 6px;
}

.order-notes-textarea {
  width: 100%;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1.5px solid #dee2e6;
  font-size: 0.85rem;
  font-family: inherit;
  color: var(--DC-gray);
  box-sizing: border-box;
  resize: vertical;
  outline: none;
  transition: border-color 0.2s;
}

.order-notes-textarea:focus {
  border-color: var(--DC-orange);
}

.client-row {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #4c4646;
}

.save-state {
  margin-top: 6px;
  display: inline-flex;
  align-self: flex-start;
  padding: 6px 10px;
  border-radius: 999px;
  background: #fff3e6;
  color: #d9480f;
  font-size: 0.8rem;
  font-weight: 700;
}

.save-state.saved {
  background: #ebfbee;
  color: #2b8a3e;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 4px;
}

.list-header h3 {
  margin: 0;
  font-size: 1rem;
  color: #2f2a2a;
}

.btn-add-mini,
.btn-primary,
.btn-secondary,
.btn-close,
.quantity-btn,
.product-option,
.pill {
  border: none;
  cursor: pointer;
  transition: 0.2s ease;
}

.btn-add-mini {
  background: #fff2e8;
  color: var(--DC-orange);
  border-radius: 999px;
  padding: 8px 12px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 700;
}

.btn-add-mini:hover,
.btn-primary:hover,
.btn-secondary:hover,
.btn-close:hover,
.quantity-btn:hover,
.product-option:hover,
.pill:hover {
  transform: translateY(-1px);
}

.product-card {
  padding: 14px 16px;
}

.product-main {
  display: flex;
  align-items: center;
  gap: 10px;
}

.qty-control {
  display: flex;
  align-items: center;
  gap: 6px;
}

.qty-control button,
.quantity-btn {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f4efea;
  color: #4c4646;
}

.qty-num,
.quantity-value {
  min-width: 22px;
  text-align: center;
  font-weight: 700;
  color: #2f2a2a;
}

.product-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.product-info small {
  color: #7e7575;
}

.price {
  font-weight: 800;
  color: var(--DC-orange);
}

.btn-trash {
  border: none;
  background: transparent;
  color: #b94f4f;
  cursor: pointer;
}

.product-ingredients {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 10px;
}

.chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f7efe7;
  color: #6b4f2f;
  border-radius: 999px;
  padding: 6px 8px;
  font-size: 0.8rem;
}

.chip-removed {
  background: #fdecec;
  color: #b94f4f;
}

.product-ingredients-empty {
  margin-top: 8px;
  font-size: 0.8rem;
  color: #7e7575;
}

.chip button {
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
}

.modal-footer {
  padding: 16px 24px 20px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  display: flex;
  gap: 12px;
  align-items: center;
  background: white;
}

.footer-total {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #f7efe7;
  border-radius: 12px;
  padding: 12px 14px;
  color: #4c4646;
}

.footer-actions {
  display: flex;
  gap: 10px;
}

.btn-primary {
  padding: 12px 16px;
  background: var(--DC-orange);
  color: white;
  border-radius: 12px;
  font-weight: 800;
}

.btn-secondary {
  padding: 12px;
  background: #eee7de;
  color: #4c4646;
  border-radius: 12px;
}

.btn-close {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: #f7efe7;
  color: #4c4646;
}

.btn-small {
  width: 32px;
  height: 32px;
}

.status-badge {
  font-size: 0.72rem;
  padding: 4px 8px;
  border-radius: 999px;
  text-transform: uppercase;
  font-weight: 800;
}

.status-pending { background: #fff3e6; color: #d9480f; }
.status-preparation { background: #e7f5ff; color: #1971c2; }
.status-completed { background: #ebfbee; color: #2b8a3e; }

.submodal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(30, 27, 36, 0.72);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  z-index: 2100;
}

.submodal-card {
  width: min(560px, 100%);
  max-height: 85vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.submodal-header {
  padding: 18px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f0e7dc;
}

.submodal-header h3 {
  margin: 0 0 2px;
  color: #2f2a2a;
}

.submodal-header p {
  margin: 0;
  color: #7e7575;
  font-size: 0.9rem;
}

.submodal-body {
  padding: 16px 20px 8px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  overflow-y: auto;
}

.picker-section {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.section-title {
  margin: 0;
  font-weight: 800;
  color: #4c4646;
}

.filter-row {
  display: flex;
  width: 100%;
}

.product-search {
  width: 100%;
  border: 1px solid #ece3da;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 0.92rem;
  color: #4c4646;
  outline: none;
}

.product-search:focus {
  border-color: var(--DC-orange);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.16);
}

.product-options {
  display: grid;
  gap: 8px;
}

.empty-products {
  padding: 12px;
  border-radius: 12px;
  background: #faf5ef;
  color: #7e7575;
  text-align: center;
  font-size: 0.9rem;
}

.product-option {
  text-align: left;
  padding: 10px 12px;
  border-radius: 12px;
  background: #faf5ef;
  color: #4c4646;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-option.active {
  background: #ffe2cf;
  color: var(--DC-orange);
  font-weight: 800;
}

.option-name {
  font-weight: 700;
}

.option-meta {
  font-size: 0.8rem;
  color: #7e7575;
}

.pill-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.pill {
  padding: 8px 12px;
  border-radius: 999px;
  background: #f4efea;
  color: #4c4646;
}

.pill.active {
  background: var(--DC-orange);
  color: white;
}

.ingredients-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.ingredient-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 8px 10px;
  border-radius: 10px;
  background: #faf5ef;
}

.ingredient-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ingredient-badge {
  font-size: 0.75rem;
  color: #2f8f48;
  font-weight: 700;
}

.ingredient-badge.removed {
  color: #b94f4f;
}

.quantity-selector {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.submodal-footer {
  padding: 14px 20px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid #f0e7dc;
  background: #fffaf5;
}

.summary-box {
  display: flex;
  flex-direction: column;
  gap: 2px;
  color: #4c4646;
}

.summary-box strong {
  color: var(--DC-orange);
  font-size: 1rem;
}

@media print {
  body * {
    visibility: hidden;
  }

  .print-only,
  .print-only * {
    visibility: visible;
  }

  .print-only {
    display: block !important;
    position: absolute;
    left: 0;
    top: 0;
    width: 72mm;
    padding: 2mm;
    font-family: 'Courier New', Courier, monospace;
    color: #000;
    font-size: 12px;
  }

  .print-header {
    text-align: center;
    margin-bottom: 10px;
    border-bottom: 2px solid #000;
    padding-bottom: 8px;
  }

  .print-title {
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 1px;
    text-transform: uppercase;
  }

  .print-order-id {
    font-size: 14px;
    font-weight: 800;
    margin-top: 4px;
  }

  .print-time {
    font-size: 10px;
    color: #333;
    margin-top: 2px;
  }

  .print-product {
    margin: 8px 0;
    border-bottom: 1px dashed #000;
    padding-bottom: 5px;
  }

  .print-product-line {
    display: flex;
    justify-content: space-between;
    gap: 6px;
    align-items: flex-start;
  }

  .print-product-name {
    display: flex;
    gap: 6px;
    flex: 1;
  }

  .print-qty {
    font-weight: 900;
    color: #000;
  }

  .print-price {
    font-weight: 700;
    white-space: nowrap;
  }

  .print-ingredients {
    font-size: 10px;
    color: #444;
    margin-top: 2px;
  }

  .print-total {
    font-size: 13px;
    font-weight: 900;
    margin-top: 12px;
    border-top: 2px solid #000;
    padding-top: 5px;
  }

  .print-client {
    margin-top: 8px;
    font-size: 11px;
  }

  .print-footer {
    text-align: center;
    margin-top: 10px;
    font-size: 10px;
    border-top: 1px solid #000;
    padding-top: 6px;
  }
}

/* BADGES Y TITULOS HEADER */
.header-titles {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.badges-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.status-paid {
  background-color: #e8f5e9;
  color: #2e7d32;
  border: 1px solid #a5d6a7;
}

.status-unpaid {
  background-color: #fff3e0;
  color: #e65100;
  border: 1px solid #ffcc80;
}

/* TIMELINE Y STEPPER DE ESTADOS */
.timeline-container {
  background-color: #fcfbf9;
  border: 1px solid #eeedee;
  border-radius: 14px;
  padding: 16px;
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.timeline-steps {
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
}

.timeline-steps::before {
  content: '';
  position: absolute;
  top: 15px;
  left: 30px;
  right: 30px;
  height: 3px;
  background-color: #e0dce4;
  z-index: 1;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  z-index: 2;
  position: relative;
  transition: transform 0.2s ease;
}

.timeline-step:hover {
  transform: translateY(-2px);
}

.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background-color: white;
  border: 3px solid #cbd5e1;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.85rem;
  transition: all 0.3s ease;
}

.timeline-step.completed .step-circle {
  background-color: var(--DC-orange);
  border-color: var(--DC-orange);
  color: white;
}

.timeline-step.active .step-circle {
  background-color: white;
  border-color: var(--DC-orange);
  color: var(--DC-orange);
  box-shadow: 0 0 0 4px rgba(226, 135, 67, 0.25);
}

.step-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #64748b;
}

.timeline-step.active .step-label,
.timeline-step.completed .step-label {
  color: var(--DC-gray);
  font-weight: 900;
}

/* NAVEGACIÓN Y BOTÓN PAGAR */
.status-navigation {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding-top: 10px;
  border-top: 1px dashed #e0dce4;
}

.btn-step {
  background-color: white;
  border: 2px solid #eeedee;
  color: var(--DC-gray);
  font-weight: 800;
  font-size: 0.85rem;
  padding: 8px 14px;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.2s ease;
}

.btn-step:hover:not(:disabled) {
  border-color: var(--DC-orange);
  color: var(--DC-orange);
}

.btn-step:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-pay {
  background-color: #2e7d32;
  color: white;
  font-weight: 900;
  font-size: 0.85rem;
  padding: 8px 16px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 12px rgba(46, 125, 50, 0.25);
  transition: all 0.2s ease;
}

.btn-pay:hover {
  background-color: #1b5e20;
  transform: translateY(-1px);
}

.badge-paid-confirmed {
  background-color: #e8f5e9;
  color: #2e7d32;
  font-weight: 900;
  font-size: 0.85rem;
  padding: 6px 12px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 4px;
}

.status-cancelled {
  background-color: #ffebee;
  color: #c62828;
  border: 1px solid #ffcdd2;
}

.payment-select-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.payment-method-select {
  padding: 4px 10px;
  border: 1.5px solid var(--DC-orange);
  border-radius: 8px;
  background: white;
  color: var(--DC-brown);
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
}

.btn-cancel-order {
  background-color: #d32f2f;
  color: white;
  font-weight: 900;
  font-size: 0.85rem;
  padding: 8px 14px;
  border-radius: 10px;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  box-shadow: 0 4px 12px rgba(211, 47, 47, 0.25);
  transition: all 0.2s ease;
}

.btn-cancel-order:hover {
  background-color: #b71c1c;
  transform: translateY(-1px);
}

.badge-cancelled-confirmed {
  background-color: #ffebee;
  color: #c62828;
  font-weight: 900;
  font-size: 0.85rem;
  padding: 6px 12px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  gap: 4px;
}

/* ESTILOS DE MEJORA DE TAMAÑOS E INGREDIENTES */
.size-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px !important;
}

.size-price {
  font-size: 0.78rem;
  background: rgba(0, 0, 0, 0.07);
  padding: 2px 7px;
  border-radius: 6px;
  font-weight: 800;
}

.pill.active .size-price {
  background: rgba(255, 255, 255, 0.3);
  color: white;
}

.section-header-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
  margin-bottom: 8px;
}

.section-subtitle {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 600;
}

.ingredients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 8px;
  max-height: 220px;
  overflow-y: auto;
  padding-right: 4px;
}

.ingredient-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  border: 1.5px solid #e2e8f0;
  border-radius: 10px;
  padding: 8px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.ingredient-card:hover {
  border-color: var(--DC-orange);
  transform: translateY(-1px);
}

.ingredient-card.removed {
  background: #fef2f2;
  border-color: #fca5a5;
}

.ingredient-info {
  display: flex;
  align-items: center;
  gap: 8px;
}

.ingredient-checkbox {
  accent-color: var(--DC-orange);
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.ingredient-name {
  font-size: 0.85rem;
  font-weight: 700;
  color: #334155;
}

.ingredient-card.removed .ingredient-name {
  color: #991b1b;
  text-decoration: line-through;
}

.ingredient-status-badge {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 12px;
}

.status-included {
  background: #dcfce7;
  color: #166534;
}

.status-removed {
  background: #fee2e2;
  color: #991b1b;
}

.empty-ingredients-info {
  font-size: 0.85rem;
  color: #64748b;
  font-style: italic;
  padding: 12px;
  background: #f8fafc;
  border-radius: 8px;
  text-align: center;
}

.ingredient-card.protected {
  background: #f8fafc;
  border-color: #cbd5e1;
  cursor: not-allowed;
  opacity: 0.9;
}

.status-protected {
  background: #e2e8f0;
  color: #475569;
}

.chip-extra {
  background-color: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.extras-picker-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.extras-chips-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  max-height: 110px;
  overflow-y: auto;
}

.extra-chip-btn {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 6px 10px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  color: #334155;
  cursor: pointer;
  transition: all 0.2s ease;
}

.extra-chip-btn:hover {
  background: #e2e8f0;
  border-color: var(--DC-orange);
  color: var(--DC-orange);
}

.extra-price-tag {
  color: #059669;
  font-size: 0.72rem;
  font-weight: 800;
  background: #d1fae5;
  padding: 1px 5px;
  border-radius: 4px;
}

.selected-extras-list {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.selected-extras-title {
  font-size: 0.78rem;
  font-weight: 800;
  color: #475569;
  margin: 0;
}

.extra-selected-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}

.extra-name {
  font-size: 0.82rem;
  font-weight: 700;
  color: #1e293b;
}

.extra-controls {
  display: flex;
  align-items: center;
  gap: 6px;
}

.btn-extra-qty {
  width: 22px;
  height: 22px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: white;
  font-weight: 800;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-extra-qty:hover {
  background: #f1f5f9;
  border-color: var(--DC-orange);
}

.extra-qty-num {
  font-size: 0.82rem;
  font-weight: 800;
  min-width: 16px;
  text-align: center;
}

.extra-cost {
  font-size: 0.82rem;
  font-weight: 800;
  color: #059669;
  min-width: 45px;
  text-align: right;
}

/* 🕒 LÍNEA DE TIEMPO Y ESTADOS */
.timeline-container {
  background: white;
  border-radius: 16px;
  padding: 16px;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.timeline-steps {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 8px;
  position: relative;
}

.timeline-step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
  cursor: pointer;
  text-align: center;
  position: relative;
  transition: all 0.2s ease;
}

.step-circle {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #f1f5f9;
  border: 2px solid #cbd5e1;
  color: #64748b;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.85rem;
  font-weight: 900;
  transition: all 0.2s ease;
}

.step-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #64748b;
}

.timeline-step.active .step-circle {
  background: #ff6b00;
  border-color: #ff6b00;
  color: white;
  box-shadow: 0 0 0 4px rgba(255, 107, 0, 0.2);
}

.timeline-step.active .step-label {
  color: #ff6b00;
  font-weight: 900;
}

.timeline-step.completed .step-circle {
  background: #22c55e;
  border-color: #22c55e;
  color: white;
}

.timeline-step.completed .step-label {
  color: #15803d;
}

/* 🚦 NAVEGACIÓN Y BOTONES DE ESTADO */
.status-navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  padding-top: 12px;
  border-top: 1px dashed #e2e8f0;
}

.btn-step {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #334155;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-step:hover:not(:disabled) {
  border-color: #ff6b00;
  color: #ff6b00;
  background: #fff7ed;
}

.btn-step:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  background: #f8fafc;
}

.btn-pay {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: none;
  background: #16a34a;
  color: white;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(22, 163, 74, 0.25);
  transition: all 0.2s ease;
}

.btn-pay:hover {
  background: #15803d;
}

.badge-paid-confirmed {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #dcfce7;
  color: #166534;
  font-size: 0.82rem;
  font-weight: 800;
}

.btn-cancel-order {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1.5px solid #fecaca;
  background: white;
  color: #dc2626;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancel-order:hover:not(:disabled) {
  background: #dc2626;
  color: white;
}

.btn-disabled-rule,
.btn-cancel-order:disabled,
.btn-pay:disabled {
  opacity: 0.45 !important;
  cursor: not-allowed !important;
  pointer-events: auto !important;
}

.badge-cancelled-confirmed {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  border-radius: 999px;
  background: #fee2e2;
  color: #991b1b;
  font-size: 0.82rem;
  font-weight: 800;
}

/* 🌟 BANNERS DE REGLAS OPERATIVAS (CANCELADO / ENTREGADO) */
.order-rule-banner {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 12px;
  margin: 4px 0 10px;
  font-size: 0.85rem;
  line-height: 1.4;
}

.banner-cancelled {
  background: #fef2f2;
  border: 1.5px solid #fecaca;
  color: #991b1b;
}

.banner-cancelled .rule-icon {
  color: #dc2626;
  flex-shrink: 0;
}

.banner-delivered {
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  color: #166534;
}

.banner-delivered .rule-icon {
  color: #16a34a;
  flex-shrink: 0;
}

.rule-text strong {
  display: inline;
  font-weight: 800;
}

/* 📱 RESPONSIVO PARA MODAL EN MÓVILES */
@media (max-width: 640px) {
  .modal-overlay {
    padding: 8px;
  }

  .modal-container {
    width: 100%;
    max-height: 96vh;
    border-radius: 18px;
  }

  .modal-header {
    padding: 12px 16px;
  }

  .header-titles {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .modal-title {
    font-size: 1rem;
  }

  .badges-row {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
  }

  .modal-content {
    padding: 10px;
    gap: 10px;
  }

  .client-card {
    padding: 12px;
    gap: 8px;
  }

  .main-client-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
  }

  .btn-whatsapp {
    width: 100%;
    justify-content: center;
    box-sizing: border-box;
    padding: 8px;
  }

  .payment-select-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }

  .payment-method-select {
    width: 100%;
    box-sizing: border-box;
    padding: 8px;
  }

  .product-main {
    flex-wrap: wrap;
    gap: 8px;
  }

  .product-info {
    min-width: 100%;
    order: -1;
  }

  .price {
    font-size: 0.95rem;
    margin-left: auto;
  }

  .timeline-container {
    padding: 12px 10px;
  }

  .timeline-steps {
    gap: 4px;
  }

  .step-circle {
    width: 28px;
    height: 28px;
    font-size: 0.75rem;
  }

  .step-label {
    font-size: 0.68rem;
  }

  .status-navigation {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
  }

  .btn-step,
  .btn-pay,
  .btn-cancel-order,
  .badge-paid-confirmed,
  .badge-cancelled-confirmed {
    width: 100%;
    box-sizing: border-box;
    justify-content: center;
    padding: 10px 6px;
    font-size: 0.78rem;
  }

  .modal-footer {
    padding: 12px 14px;
    flex-direction: column;
    gap: 8px;
  }

  .footer-total {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
  }

  .footer-actions {
    width: 100%;
  }

  .btn-secondary {
    width: 100%;
    justify-content: center;
    display: flex;
  }

  /* Submodal de Agregar Producto en Móvil */
  .submodal-overlay {
    padding: 8px;
  }

  .submodal-card {
    width: 100%;
    max-height: 94vh;
    border-radius: 18px;
  }

  .submodal-header {
    padding: 12px 14px;
  }

  .submodal-body {
    padding: 12px 10px;
    gap: 12px;
  }

  .submodal-footer {
    padding: 12px 14px;
    flex-direction: column;
    gap: 10px;
  }

  .submodal-footer .summary-box {
    width: 100%;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }

  .submodal-footer .btn-primary {
    width: 100%;
    justify-content: center;
    display: flex;
  }
}
</style>