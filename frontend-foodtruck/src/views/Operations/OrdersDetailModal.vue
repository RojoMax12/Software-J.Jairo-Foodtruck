<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container">
      <header class="modal-header">
        <div>
          <h2 class="modal-title">Pedido #{{ orderId }}</h2>
          <span class="status-badge" :class="getStatusClass(localStatusId)">{{ localStatus }}</span>
        </div>
        <div class="header-actions">
          <button class="btn-secondary btn-contact" @click="contactClient">
            <Phone :size="16" /> Contactar
          </button>
          <button class="btn-close" @click="handleClose"><X /></button>
        </div>
      </header>

      <div class="modal-content">
        <div class="client-card">
          <div class="client-row"><User :size="16" /> <strong>{{ distributor }}</strong></div>
          <div class="client-row"><Phone :size="16" /> {{ phone || 'Sin teléfono' }}</div>
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
                <button @click="changeQty(product, -1)">-</button>
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

            <div v-if="(product.removedIngredients || []).length" class="product-ingredients">
              <span v-for="ing in product.removedIngredients || []" :key="ing" class="chip chip-removed">
                Sin: {{ ing }}
                <button @click="toggleRemovedIngredient(product.id, ing)"><X :size="10" /></button>
              </span>
            </div>
            <div v-else class="product-ingredients-empty">Sin ajustes de ingredientes</div>
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
          <button class="btn-primary" @click="changeStatus">Cambiar Estado</button>
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
            <div v-if="filteredCatalogProducts.length" class="product-options">
              <button
                v-for="item in filteredCatalogProducts"
                :key="item.id"
                class="product-option"
                :class="{ active: selectedCatalogProduct?.id === item.id }"
                @click="selectCatalogProduct(item)"
              >
                <span class="option-name">{{ item.name }}</span>
                <span class="option-meta">{{ item.category }}</span>
              </button>
            </div>
            <div v-else class="empty-products">No se encontraron productos con esos filtros.</div>
          </div>

          <div v-if="selectedCatalogProduct" class="picker-section">
            <p class="section-title">Tamaño</p>
            <div class="pill-group">
              <button
                v-for="size in selectedCatalogProduct.sizes"
                :key="size"
                class="pill"
                :class="{ active: selectedSize === size }"
                @click="selectedSize = size"
              >
                {{ size }}
              </button>
            </div>
          </div>

          <div v-if="selectedCatalogProduct" class="picker-section">
            <p class="section-title">Ingredientes</p>
            <div class="ingredients-list">
              <label
                v-for="ingredient in selectedCatalogProduct.ingredients"
                :key="ingredient.name"
                class="ingredient-item"
              >
                <div class="ingredient-left">
                  <input
                    type="checkbox"
                    :checked="!excludedIngredients.includes(ingredient.name)"
                    @change="toggleIngredient(ingredient.name)"
                  />
                  <span>{{ ingredient.name }}</span>
                </div>
                <span class="ingredient-badge" :class="{ removed: excludedIngredients.includes(ingredient.name) }">
                  {{ excludedIngredients.includes(ingredient.name) ? 'Quitado' : 'Incluido' }}
                </span>
              </label>
            </div>
          </div>

          <div v-if="selectedCatalogProduct" class="picker-section">
            <p class="section-title">Cantidad</p>
            <div class="quantity-selector">
              <button class="quantity-btn" @click="decreaseAddQuantity">-</button>
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
      <div class="print-title">J.Jairo FoodTruck</div>
      <div class="print-order-id">Pedido #{{ orderId }}</div>
      <div class="print-time">{{ date || '-' }} · {{ time || new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' }) }}</div>
    </div>

    <div class="print-body">
      <div v-for="p in products" :key="p.id" class="print-product">
        <div class="print-product-line">
          <div class="print-product-name">
            <span class="print-qty">{{ p.quantity }}x</span>
            <span>{{ p.name }}</span>
          </div>
          <span class="print-price">${{ formatNumber(p.subtotal) }}</span>
        </div>
        <div v-if="(p.removedIngredients || []).length > 0" class="print-ingredients">
          <strong>SIN:</strong> {{ (p.removedIngredients || []).join(', ') }}
        </div>
      </div>
    </div>

    <div class="print-total">
      TOTAL: ${{ formatNumber(totalAmount) }}
    </div>

    <div class="print-client">
      <strong>Cliente:</strong> {{ distributor || 'Sin nombre' }}
    </div>

    <div class="print-footer">
      Gracias por tu preferencia
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { X, Phone, Printer, Plus, Trash2, User } from 'lucide-vue-next';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();
const props = defineProps<{
  orderId: number | string; distributor?: string; status?: string;
  statusId?: number; date?: string; time?: string;
  total?: number; phone?: string;
}>();

const emit = defineEmits(['close', 'statusChanged']);
const localStatus = ref(props.status || 'En preparación');
const localStatusId = ref(props.statusId ? Number(props.statusId) : 2);
const products = ref([
  { id: 101, catalogId: 1, name: 'Completo Italiano', format: 'Normal', quantity: 2, subtotal: 3000, removedIngredients: ['Palta', 'Tomate'] },
  { id: 102, catalogId: 2, name: 'Churrasco Luco', format: 'Normal', quantity: 1, subtotal: 4500, removedIngredients: ['Churrasco', 'Queso'] }
]);

const isAddModalOpen = ref(false);
const hasPendingChanges = ref(false);
const orderStorageKey = computed(() => `order-${props.orderId}`);
const catalogProducts = ref([
  {
    id: 1,
    name: 'Completo Italiano',
    category: 'Clásico',
    sizes: ['Normal', 'Doble'],
    basePrice: 2600,
    ingredients: [
      { name: 'Palta' },
      { name: 'Tomate' },
      { name: 'Mayonesa' },
      { name: 'Chucrut' }
    ]
  },
  {
    id: 2,
    name: 'Churrasco Luco',
    category: 'Especial',
    sizes: ['Normal'],
    basePrice: 4200,
    ingredients: [
      { name: 'Churrasco' },
      { name: 'Queso' },
      { name: 'Tomate' },
      { name: 'Cebolla' }
    ]
  },
  {
    id: 3,
    name: 'Barros Luco',
    category: 'Caliente',
    sizes: ['Normal', 'Grande'],
    basePrice: 3800,
    ingredients: [
      { name: 'Carne' },
      { name: 'Queso' },
      { name: 'Tomate' }
    ]
  }
]);
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

const formatNumber = (n: number) => n.toLocaleString('es-CL');
const formato = (f: string) => f;

const saveOrder = (showNotification = true) => {
  const snapshot = {
    status: localStatus.value,
    statusId: localStatusId.value,
    products: products.value.map((p: any) => ({
      ...p,
      removedIngredients: [...(p.removedIngredients || [])]
    }))
  };

  localStorage.setItem(orderStorageKey.value, JSON.stringify(snapshot));
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

onMounted(() => {
  loadSavedOrder();
});

const handleClose = () => {
  if (hasPendingChanges.value) {
    notify('Guarda el pedido antes de cerrar', 'success');
    return;
  }
  emit('close');
};

const changeQty = (p: any, delta: number) => {
  p.quantity = Math.max(1, p.quantity + delta);
  p.subtotal = p.quantity * (p.subtotal / (p.quantity - delta || 1));
  hasPendingChanges.value = true;
};

const openAddModal = () => {
  isAddModalOpen.value = true;
  searchQuery.value = '';
  activeCategory.value = 'all';
  selectedCatalogProduct.value = catalogProducts.value[0];
  selectedSize.value = selectedCatalogProduct.value?.sizes?.[0] || 'Normal';
  excludedIngredients.value = [];
  addQuantity.value = 1;
};

const closeAddModal = () => {
  isAddModalOpen.value = false;
};

const selectCatalogProduct = (product: any) => {
  selectedCatalogProduct.value = product;
  selectedSize.value = product.sizes?.[0] || 'Normal';
  excludedIngredients.value = [];
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
  const basePrice = selectedCatalogProduct.value.basePrice || 0;
  return basePrice * addQuantity.value;
});

const normalizeRemovedIngredients = (items: string[] = []) => [...new Set(items)].sort();

const confirmAddProduct = () => {
  if (!selectedCatalogProduct.value) return;

  const normalizedRemoved = normalizeRemovedIngredients(excludedIngredients.value);
  const existingProduct = products.value.find((p: any) => {
    const sameCatalog = Number(p.catalogId ?? p.id) === Number(selectedCatalogProduct.value.id);
    const sameFormat = p.format === `${selectedSize.value}`;
    const sameRemoved = JSON.stringify(normalizeRemovedIngredients(p.removedIngredients || [])) === JSON.stringify(normalizedRemoved);
    return sameCatalog && sameFormat && sameRemoved;
  });

  if (existingProduct) {
    const previousQuantity = existingProduct.quantity;
    const unitPrice = existingProduct.subtotal / previousQuantity;
    existingProduct.quantity += addQuantity.value;
    existingProduct.subtotal = Number((existingProduct.quantity * unitPrice).toFixed(0));
  } else {
    products.value = [
      ...products.value,
      {
        id: Date.now(),
        catalogId: selectedCatalogProduct.value.id,
        name: selectedCatalogProduct.value.name,
        format: `${selectedSize.value}`,
        quantity: addQuantity.value,
        subtotal: previewPrice.value,
        removedIngredients: normalizedRemoved
      }
    ];
  }

  hasPendingChanges.value = true;
  saveOrder(false);
  notify('Producto agregado y pedido guardado', 'success');
  closeAddModal();
};

const removeProduct = (id: any) => {
  products.value = products.value.filter(p => p.id !== id);
  hasPendingChanges.value = true;
};
const toggleRemovedIngredient = (pid: any, ing: string) => {
  const p = products.value.find(x => x.id === pid);
  if (!p) return;

  const removed = p.removedIngredients || [];
  p.removedIngredients = removed.includes(ing)
    ? removed.filter(i => i !== ing)
    : [...removed, ing];

  hasPendingChanges.value = true;
};

const totalAmount = computed(() => products.value.reduce((acc, p) => acc + p.subtotal, 0));

const getStatusClass = (id: number) => {
  const map: any = { 1: 'status-pending', 2: 'status-preparation', 3: 'status-completed' };
  return map[id] || 'status-generic';
};

const changeStatus = async () => {
  localStatusId.value = (localStatusId.value % 3) + 1;
  hasPendingChanges.value = true;
  notify('Estado actualizado', 'success');
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
            <div class="title">J.Jairo FoodTruck</div>
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

.client-card {
  padding: 14px 16px;
  display: grid;
  gap: 6px;
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
</style>