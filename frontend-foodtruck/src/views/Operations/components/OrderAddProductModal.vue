<template>
  <div v-if="isOpen" class="submodal-overlay" @click.self="$emit('close')">
    <div class="submodal-card">
      <div class="submodal-header">
        <div>
          <h3>Agregar producto a la comanda</h3>
          <p>Selecciona el plato del menú y personaliza sus ingredientes</p>
        </div>
        <button type="button" class="btn-close btn-small" @click="$emit('close')">
          <X :size="18" />
        </button>
      </div>

      <div class="submodal-body">
        <div class="picker-section">
          <p class="section-title">Buscar Producto</p>
          <div class="filter-row">
            <input
              v-model="searchQuery"
              type="text"
              class="product-search"
              placeholder="Buscar por nombre o categoría..."
            />
          </div>
          <div class="pill-group">
            <button
              type="button"
              class="pill"
              :class="{ active: activeCategory === 'all' }"
              @click="activeCategory = 'all'"
            >
              Todos
            </button>
            <button
              v-if="offerProductsCount > 0"
              type="button"
              class="pill pill-offer"
              :class="{ active: activeCategory === '__offers__' }"
              @click="activeCategory = '__offers__'"
            >
              🔥 En Oferta ({{ offerProductsCount }})
            </button>
            <button
              v-for="category in uniqueCategories.filter((c) => c !== 'all')"
              :key="category"
              type="button"
              class="pill"
              :class="{ active: activeCategory === category }"
              @click="activeCategory = category"
            >
              {{ category }}
            </button>
          </div>

          <div v-if="isLoadingCatalog" class="product-options">
            <div v-for="n in 3" :key="'cat-skel-' + n" class="product-option-skeleton">
              <div class="skeleton-pill width-120"></div>
              <div class="skeleton-pill width-70 margin-top-4"></div>
            </div>
          </div>
          <div v-else-if="filteredCatalogProducts.length" class="product-options">
            <button
              v-for="item in filteredCatalogProducts"
              :key="item.id"
              type="button"
              class="product-option"
              :class="{ active: selectedCatalogProduct?.id === item.id, 'is-offer-row': isItemOffer(item) }"
              @click="selectCatalogProduct(item)"
            >
              <div class="option-left">
                <span class="option-name">{{ item.name }}</span>
                <div class="option-tags">
                  <span v-if="isItemOffer(item)" class="offer-inline-badge">OFERTA</span>
                  <span class="option-meta">{{ item.category }}</span>
                </div>
              </div>
              <div class="option-price-col">
                <span v-if="itemOriginalPrice(item) > itemDisplayPrice(item)" class="price-struck">${{ formatCLP(itemOriginalPrice(item)) }}</span>
                <strong class="price-current" :class="{ 'price-offer': isItemOffer(item) }">${{ formatCLP(itemDisplayPrice(item)) }}</strong>
              </div>
            </button>
          </div>
          <div v-else class="empty-products">No se encontraron productos coincidentes.</div>
        </div>

        <!-- TAMAÑO -->
        <div v-if="selectedCatalogProduct && selectedCatalogProduct.sizes?.length" class="picker-section">
          <p class="section-title">Tamaño / Formato</p>
          <div class="pill-group">
            <button
              v-for="size in selectedCatalogProduct.sizes"
              :key="typeof size === 'object' ? size.id || size.name : size"
              type="button"
              class="pill size-pill"
              :class="{ active: selectedSize === (typeof size === 'object' ? size.name : size) }"
              @click="selectedSize = typeof size === 'object' ? size.name : size"
            >
              <span class="size-name">{{ typeof size === 'object' ? size.name : size }}</span>
              <span v-if="typeof size === 'object' && size.hasPromotion && size.originalPrice > size.price" class="size-price-old">${{ formatCLP(size.originalPrice) }}</span>
              <span v-if="typeof size === 'object' && size.price" class="size-price">${{ formatCLP(size.price) }}</span>
            </button>
          </div>
        </div>

        <!-- INGREDIENTES BASE A EXCLUIR -->
        <div v-if="selectedCatalogProduct && selectedCatalogProduct.tipo_armado !== 'Personalizable' && customizableBaseIngredients.length" class="picker-section">
          <div class="section-header-row">
            <p class="section-title">Ingredientes de Receta Base</p>
            <span class="section-subtitle">Desmarca los ingredientes que el cliente no desea:</span>
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
                {{ excludedIngredients.includes(ingredient.name) ? 'Sin' : 'Con' }}
              </span>
            </label>
          </div>
        </div>

        <!-- INGREDIENTES A ELECCIÓN (PERSONALIZABLES) -->
        <div v-if="selectedCatalogProduct && selectedCatalogProduct.tipo_armado === 'Personalizable' && displayOptionalIngredients.length" class="picker-section">
          <div class="section-header-row">
            <p class="section-title">
              Ingredientes a elección
              <span v-if="selectedCatalogProduct.cantidad_incluida > 0" class="badge-included-info">
                ({{ selectedCatalogProduct.cantidad_incluida }} incluidos gratis)
              </span>
            </p>
            <span class="section-subtitle">
              Seleccionados: {{ selectedOptionalIngredients.length }}
              <template v-if="extraChargeableCount > 0">
                ({{ extraChargeableCount }} extra a +${{ formatCLP(selectedCatalogProduct.precio_ingrediente_extra) }} c/u)
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
                {{ selectedOptionalIngredients.includes(ingredient.name) ? 'Agregado' : 'Opcional' }}
              </span>
            </label>
          </div>
        </div>

        <!-- CANTIDAD -->
        <div v-if="selectedCatalogProduct" class="picker-section">
          <p class="section-title">Cantidad</p>
          <div class="quantity-selector">
            <button type="button" class="quantity-btn" :disabled="addQuantity <= 1" @click="decreaseAddQuantity">-</button>
            <span class="quantity-value">{{ addQuantity }}</span>
            <button type="button" class="quantity-btn" @click="increaseAddQuantity">+</button>
          </div>
        </div>
      </div>

      <div class="submodal-footer">
        <div class="summary-box">
          <span>Total agregado:</span>
          <strong>${{ formatCLP(previewPrice) }}</strong>
        </div>
        <button type="button" class="btn-primary" @click="confirmAddProduct">
          Agregar a la Comanda
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { X } from 'lucide-vue-next';
import { formatCLP } from '@/utils/format';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();
import { computeDeterministicExtrasCost, supportsAderezosGratuitos } from '@/utils/catalogPricing';

const props = defineProps<{
  isOpen: boolean;
  isLoadingCatalog: boolean;
  catalogProducts: any[];
  stockIngredients: any[];
}>();

const emit = defineEmits<{
  close: [];
  'add-product': [payload: {
    catalogId: number | string;
    name: string;
    format: string;
    quantity: number;
    singleUnitPrice: number;
    removedIngredients: string[];
    addedExtras: { name: string; quantity: number; price: number }[];
  }];
}>();

const selectedCatalogProduct = ref<any>(null);
const selectedSize = ref('Normal');
const excludedIngredients = ref<string[]>([]);
const selectedOptionalIngredients = ref<string[]>([]);
const addQuantity = ref(1);
const searchQuery = ref('');
const activeCategory = ref('all');

const isItemOffer = (item: any): boolean => {
  if (!item) return false;
  const catLow = String(item.category || '').toLowerCase();
  const nameLow = String(item.name || '').toLowerCase();
  return Boolean(
    item.en_oferta ||
    item.hasPromotion ||
    item.promocion_activa ||
    (Array.isArray(item.promociones_activas) && item.promociones_activas.length > 0) ||
    catLow.includes('promo') ||
    catLow.includes('combo') ||
    nameLow.includes('promo') ||
    (Array.isArray(item.sizes) && item.sizes.some((s: any) => s?.hasPromotion))
  );
};

const itemDisplayPrice = (item: any): number => {
  if (!item) return 0;
  if (Array.isArray(item.sizes) && item.sizes.length > 0) {
    const prices = item.sizes.map((s: any) => Number(s?.price || 0)).filter((n: number) => n > 0);
    if (prices.length > 0) return Math.min(...prices);
  }
  return Number(item.basePrice || 0);
};

const itemOriginalPrice = (item: any): number => {
  if (!item) return 0;
  if (Array.isArray(item.sizes) && item.sizes.length > 0) {
    const firstPromoSize = item.sizes.find((s: any) => s?.hasPromotion && s?.originalPrice > s?.price);
    if (firstPromoSize) return Number(firstPromoSize.originalPrice || 0);
  }
  return Number(item.originalBasePrice || itemDisplayPrice(item));
};

const offerProductsCount = computed(() => props.catalogProducts.filter(isItemOffer).length);

const uniqueCategories = computed(() => ['all', ...new Set(props.catalogProducts.map((p: any) => p.category))]);

const filteredCatalogProducts = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  return props.catalogProducts.filter((p: any) => {
    const matchesCat =
      activeCategory.value === 'all'
        ? true
        : activeCategory.value === '__offers__'
          ? isItemOffer(p)
          : p.category === activeCategory.value;
    const matchesQ = !q || p.name.toLowerCase().includes(q) || String(p.category || '').toLowerCase().includes(q);
    return matchesCat && matchesQ;
  });
});

const selectCatalogProduct = (product: any) => {
  selectedCatalogProduct.value = product;
  const firstSize = product?.sizes?.[0];
  selectedSize.value = typeof firstSize === 'object' ? firstSize.name : (firstSize || 'Normal');
  excludedIngredients.value = [];
  selectedOptionalIngredients.value = [];
  addQuantity.value = 1;
};

watch(() => props.isOpen, (open) => {
  if (open) {
    searchQuery.value = '';
    activeCategory.value = 'all';
    selectCatalogProduct(props.catalogProducts[0] || null);
  }
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

const currentSizeObj = computed(() => {
  if (!selectedCatalogProduct.value?.sizes) return null;
  return selectedCatalogProduct.value.sizes.find((s: any) => {
    const sName = typeof s === 'object' ? s.name : s;
    return sName === selectedSize.value;
  }) || selectedCatalogProduct.value.sizes[0];
});

const BASE_INGREDIENT_NAMES = [
  'pan', 'pan completo', 'pan frica', 'pan marraqueta', 'pan chico', 'pan grande', 'pan xl', 'pan mediano',
  'vianesa', 'carne', 'hamburguesa', 'pollo', 'lomo', 'lomito',
  'churrasco', 'masa', 'masa pizza',
];

const isBaseIngredient = (nombre: string) => {
  if (!nombre) return false;
  const lower = nombre.toLowerCase().trim();
  return (
    lower.startsWith('pan ') ||
    lower === 'pan' ||
    lower === 'vianesa' ||
    lower === 'carne' ||
    lower === 'lomito' ||
    lower === 'pollo' ||
    lower === 'hamburguesa' ||
    lower === 'masa pizza' ||
    lower === 'sopaipilla' ||
    lower === 'empanada' ||
    BASE_INGREDIENT_NAMES.some((b) => lower.includes(b))
  );
};

const customizableBaseIngredients = computed(() => {
  if (!selectedCatalogProduct.value) return [];
  const base = selectedCatalogProduct.value.baseIngredients || selectedCatalogProduct.value.ingredients || [];
  return base.filter((ing: any) => {
    const name = typeof ing === 'object' ? ing.name : ing;
    return !isBaseIngredient(name);
  });
});

const defaultExtras = [
  { name: 'Extra queso', precio: 500 },
  { name: 'Tocino', precio: 600 },
  { name: 'Palta extra', precio: 500 },
  { name: 'Papas hilo', precio: 400 },
  { name: 'Huevo frito', precio: 500 },
];

const displayOptionalIngredients = computed(() => {
  if (!selectedCatalogProduct.value) return [];
  const optionals = selectedCatalogProduct.value.optionalIngredients || [];
  if (optionals.length > 0) {
    return optionals.filter((ing: any) => !isBaseIngredient(ing.name));
  }
  if (props.stockIngredients.length > 0) {
    const baseNames = (selectedCatalogProduct.value.baseIngredients || []).map((b: any) => b.name);
    return props.stockIngredients
      .filter((s: any) => {
        const name = s.nombre || s.name;
        return !isBaseIngredient(name) && !baseNames.includes(name);
      })
      .map((s: any) => ({
        id: s.id_ingrediente || s.id,
        name: s.nombre || s.name,
      }));
  }
  return defaultExtras
    .filter((e) => !isBaseIngredient(e.name))
    .map((e) => ({ id: e.name, name: e.name }));
});

const toggleIngredient = (name: string) => {
  const index = excludedIngredients.value.indexOf(name);
  if (index > -1) {
    excludedIngredients.value.splice(index, 1);
  } else {
    excludedIngredients.value.push(name);
  }
};

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
  return Math.max(0, selectedOptionalIngredients.value.length - includedCount);
});

const extrasTotalPrice = computed(() => {
  if (!selectedCatalogProduct.value) return 0;
  return computeDeterministicExtrasCost({
    selectedExtras: selectedOptionalIngredients.value,
    productIngredients: selectedCatalogProduct.value.rawProductIngredients || selectedCatalogProduct.value.ingredients || [],
    includedCount: Number(selectedCatalogProduct.value.cantidad_incluida || 0),
    defaultExtraPrice: Number(selectedCatalogProduct.value.precio_ingrediente_extra || 0),
    allowFreeAderezos: supportsAderezosGratuitos(selectedCatalogProduct.value),
  });
});

const increaseAddQuantity = () => addQuantity.value++;
const decreaseAddQuantity = () => {
  if (addQuantity.value > 1) addQuantity.value--;
};

const previewPrice = computed(() => {
  if (!selectedCatalogProduct.value) return 0;
  const sizeObj = currentSizeObj.value;
  const baseUnitPrice = typeof sizeObj === 'object' ? (sizeObj.price || 0) : (selectedCatalogProduct.value.basePrice || 0);
  return (baseUnitPrice + extrasTotalPrice.value) * addQuantity.value;
});

const confirmAddProduct = () => {
  if (!selectedCatalogProduct.value) return;

  const isPersonalizable =
    selectedCatalogProduct.value.tipo_armado === 'Personalizable' ||
    (selectedCatalogProduct.value.category || '').toLowerCase().includes('hamburguesa') ||
    ((selectedCatalogProduct.value.category || '').toLowerCase().includes('pizza') &&
      (selectedCatalogProduct.value.name || '').toLowerCase().includes('familiar')) ||
    (selectedCatalogProduct.value.category || '').toLowerCase().includes('fajita');

  if (isPersonalizable) {
    const includedCount = selectedCatalogProduct.value.cantidad_incluida || 3;
    if (selectedOptionalIngredients.value.length < includedCount) {
      notify(`Debes elegir al menos ${includedCount} ingredientes para este producto. Te faltan ${includedCount - selectedOptionalIngredients.value.length}.`, 'warning');
      return;
    }
  }

  const sizeObj = currentSizeObj.value;
  const sizeName = typeof sizeObj === 'object' ? sizeObj.name : (selectedSize.value || 'Normal');
  const baseUnitPrice = typeof sizeObj === 'object' ? (sizeObj.price || 0) : (selectedCatalogProduct.value.basePrice || 0);
  const singleUnitPrice = baseUnitPrice + extrasTotalPrice.value;
  const normalizedRemoved = [...new Set(excludedIngredients.value)].sort();

  emit('add-product', {
    catalogId: selectedCatalogProduct.value.id,
    name: selectedCatalogProduct.value.name,
    format: `${sizeName}`,
    quantity: addQuantity.value,
    singleUnitPrice,
    removedIngredients: normalizedRemoved,
    addedExtras: selectedOptionalIngredients.value.map((name) => ({ name, quantity: 1, price: 0 })),
  });
};
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.submodal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(35, 20, 10, 0.52);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  z-index: 2100;
}

.submodal-card {
  width: min(540px, 100%);
  max-height: 85vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  background: #ffffff;
  border-radius: 16px;
  box-shadow: 0 16px 40px rgba(26, 14, 5, 0.2);
}

.submodal-header {
  padding: 1rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.submodal-header h3 {
  margin: 0;
  font-size: 1.05rem;
  color: var(--DC-brown, #513119);
}

.submodal-header p {
  margin: 2px 0 0;
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
}

.btn-close {
  background: transparent;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 8px;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.btn-small { width: 28px; height: 28px; }

.submodal-body {
  padding: 1.15rem;
  display: flex;
  flex-direction: column;
  gap: 12px;
  overflow-y: auto;
}

.picker-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.section-title {
  margin: 0;
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.section-header-row {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.section-subtitle {
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
}

.product-search {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.55rem 0.75rem;
  font-size: 0.86rem;
  color: var(--DC-gray, #2c2724);
  outline: none;
}

.product-search:focus {
  border-color: var(--DC-orange, #e28743);
  background: #ffffff;
}

.pill-group {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.pill {
  padding: 0.35rem 0.75rem;
  border-radius: 999px;
  border: 1px solid rgba(81, 49, 25, 0.1);
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.76rem;
  font-weight: 700;
  cursor: pointer;
}

.pill.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.product-options {
  display: grid;
  gap: 6px;
  max-height: 140px;
  overflow-y: auto;
}

.product-option {
  text-align: left;
  padding: 0.55rem 0.75rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-gray, #2c2724);
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
}

.product-option.active {
  background: #fff4e6;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  font-weight: 800;
}

.option-left { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.option-tags { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.option-name { font-size: 0.84rem; font-weight: 700; }
.option-meta { font-size: 0.74rem; color: var(--DC-text-gray, #7c7468); }
.offer-inline-badge { background: linear-gradient(135deg, #d80056 0%, #e28743 100%); color: #fff; font-size: 0.62rem; font-weight: 800; padding: 1px 6px; border-radius: 999px; letter-spacing: 0.04em; }
.option-price-col { display: flex; align-items: baseline; gap: 6px; flex-shrink: 0; }
.price-struck { font-size: 0.72rem; color: #94a3b8; text-decoration: line-through; }
.price-current { font-size: 0.85rem; color: var(--DC-brown, #513119); }
.price-current.price-offer { color: #d80056; }
.product-option.is-offer-row { border-color: rgba(216, 0, 86, 0.22); }
.pill-offer { border-color: rgba(216, 0, 86, 0.25); color: #b91c1c; background: #fff1f2; }
.pill-offer.active { background: #d80056; border-color: #d80056; color: #fff; }
.size-price-old { font-size: 0.68rem; text-decoration: line-through; opacity: 0.7; }

.empty-products {
  font-size: 0.82rem;
  color: var(--DC-text-gray, #7c7468);
  padding: 0.6rem 0;
}

.size-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.size-price {
  font-size: 0.7rem;
  background: rgba(0, 0, 0, 0.08);
  padding: 1px 5px;
  border-radius: 4px;
}

.pill.active .size-price {
  background: rgba(255, 255, 255, 0.3);
  color: #ffffff;
}

.ingredients-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 6px;
  max-height: 180px;
  overflow-y: auto;
}

.ingredient-card {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 8px;
  padding: 0.45rem 0.65rem;
  cursor: pointer;
}

.ingredient-card.removed {
  background: #fff5f5;
  border-color: #fecaca;
}

.ingredient-card.selected {
  background: #f0fdf4;
  border-color: #bbf7d0;
}

.ingredient-info {
  display: flex;
  align-items: center;
  gap: 6px;
}

.ingredient-name {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
}

.ingredient-card.removed .ingredient-name {
  color: #dc2626;
  text-decoration: line-through;
}

.ingredient-status-badge {
  font-size: 0.65rem;
  font-weight: 800;
  padding: 1px 5px;
  border-radius: 4px;
}

.status-included { background: #dcfce7; color: #15803d; }
.status-removed { background: #fee2e2; color: #b91c1c; }
.status-optional { background: #f1f5f9; color: #475569; }

.quantity-selector {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.quantity-btn {
  width: 28px;
  height: 28px;
  border-radius: 6px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  cursor: pointer;
  display: grid;
  place-items: center;
  font-weight: 800;
}

.quantity-value {
  font-size: 0.9rem;
  font-weight: 800;
  min-width: 20px;
  text-align: center;
}

.submodal-footer {
  padding: 0.85rem 1.25rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
  background: #fffdfa;
}

.summary-box {
  display: flex;
  flex-direction: column;
}

.summary-box span {
  font-size: 0.74rem;
  color: var(--DC-text-gray, #7c7468);
}

.summary-box strong {
  font-size: 1.05rem;
  color: var(--DC-orange, #e28743);
}

.btn-primary {
  padding: 0.55rem 1.15rem;
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.84rem;
  cursor: pointer;
}

.btn-primary:hover {
  background: var(--DC-brown, #513119);
}

.product-option-skeleton {
  padding: 0.55rem 0.75rem;
  background: #ffffff;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.06);
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.skeleton-pill {
  height: 12px;
  border-radius: 4px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-70 { width: 70px; }
.width-120 { width: 120px; }
.margin-top-4 { margin-top: 4px; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}
</style>

