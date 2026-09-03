<template>
  <Transition name="pop">
    <div v-if="isOpen && product" class="modal-overlay" @click="$emit('close')">
      <div class="modal-wrapper" @click.stop>
        
        <button class="close-btn" @click="$emit('close')">
          <X :size="24" />
        </button>

        <div class="modal-grid">
          <div class="product-img-box">
            <img :src="selectedProductImage" :alt="selectedType?.name || product.name" class="main-product-img" />
          </div>
          
          <div class="product-info-box">
            <div class="header-info">
              <span class="tag">{{ product.category }}</span>
              <h2 class="product-title">{{ product.name }}</h2>
            </div>
            
            <div class="scrollable-config">
              <!-- 1. Elige el Tamaño con Precios -->
              <div v-if="product.sizes && product.sizes.length > 1" class="config-section">
                <p class="section-subtitle">1. Elige el tamaño:</p>
                <div class="size-pills">
                  <button 
                    v-for="size in product.sizes" 
                    :key="size"
                    class="size-pill"
                    :class="{ 'active': selectedSize === size }"
                    @click="selectedSize = size"
                  >
                    <span>{{ size }}</span>
                    <span v-if="getSizePrice(size)" class="pill-price">(${{ getSizePrice(size) }})</span>
                  </button>
                </div>
              </div>

              <!-- 2. Variedad / Producto de la Categoría -->
              <div v-if="activeTypes && activeTypes.length > 0" class="config-section">
                <p class="section-subtitle">{{ product.sizes?.length > 1 ? '2.' : '1.' }} Variedad / Producto:</p>
                <div class="types-list">
                  <div 
                    v-for="tipo in activeTypes" 
                    :key="tipo.id"
                    class="type-row"
                    :class="{ 
                      'active': selectedType?.id === tipo.id,
                      'disabled': !isTypeAvailable(tipo)
                    }"
                    @click="selectType(tipo)"
                  >
                    <div class="type-info">
                      <div class="radio-circle">
                        <div class="radio-inner" v-if="selectedType?.id === tipo.id"></div>
                      </div>
                      <div class="type-texts">
                        <span class="t-name">{{ tipo.name }}</span>
                        <span class="t-desc">{{ tipo.desc }}</span>
                      </div>
                    </div>
                    <div class="type-price-box">
                      <span v-if="!isTypeAvailable(tipo)" class="status-badge no-stock">🚫 Desactivado</span>
                      <span v-else class="t-price">${{ Number(tipo.prices[selectedSize] || 0).toLocaleString('es-CL') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- 3. Ingredientes de la Receta (Desmarca para quitar) -->
              <div v-if="visibleRecipeIngredients.length > 0" class="config-section">
                <p class="section-subtitle">Ingredientes de la receta (Desmarca para quitar):</p>
                <div class="ingredients-list">
                  <label 
                    v-for="pi in visibleRecipeIngredients" 
                    :key="pi.id" 
                    class="ingredient-item"
                    :class="{ 'removed': excludedIngredients.includes(pi.ingrediente.nombre), 'out-of-stock': !isIngredientAvailable(pi) }"
                  >
                    <div class="ing-left">
                      <input 
                        type="checkbox" 
                        :checked="!excludedIngredients.includes(pi.ingrediente.nombre) && isIngredientAvailable(pi)"
                        @change="toggleIngredient(pi)"
                        :disabled="!isIngredientAvailable(pi)"
                        class="custom-checkbox"
                      />
                      <span class="ing-name">{{ pi.ingrediente.nombre }}</span>
                    </div>
                    <span v-if="!isIngredientAvailable(pi)" class="status-badge no-stock">🚫 Sin Stock</span>
                    <span v-else-if="excludedIngredients.includes(pi.ingrediente.nombre)" class="status-badge removed">Quitado</span>
                  </label>
                </div>
              </div>

              <!-- 4. Agregados Extra / Opcionales (Solo para productos personalizables) -->
              <div v-if="optionalExtraIngredients.length > 0" class="config-section">
                <p class="section-subtitle">
                  Agregados opcionales 
                  <span v-if="product.cantidad_incluida > 0" class="subtitle-hint">
                    (Incluye {{ product.cantidad_incluida }} gratis, extra +${{ Number(product.precio_ingrediente_extra).toLocaleString('es-CL') }})
                  </span>
                  <span v-else-if="product.precio_ingrediente_extra > 0" class="subtitle-hint">
                    (+$${{ Number(product.precio_ingrediente_extra).toLocaleString('es-CL') }} c/u)
                  </span>:
                </p>
                <div class="ingredients-list">
                  <label 
                    v-for="pi in optionalExtraIngredients" 
                    :key="pi.id" 
                    class="ingredient-item"
                    :class="{ 'added': addedExtraIngredients.includes(pi.ingrediente.nombre), 'out-of-stock': !isIngredientAvailable(pi) }"
                  >
                    <div class="ing-left">
                      <input 
                        type="checkbox" 
                        :checked="addedExtraIngredients.includes(pi.ingrediente.nombre) && isIngredientAvailable(pi)"
                        @change="toggleExtraIngredient(pi)"
                        :disabled="!isIngredientAvailable(pi)"
                        class="custom-checkbox"
                      />
                      <span class="ing-name">{{ pi.ingrediente.nombre }}</span>
                    </div>
                    <span v-if="!isIngredientAvailable(pi)" class="status-badge no-stock">🚫 Sin Stock</span>
                    <span v-else-if="addedExtraIngredients.includes(pi.ingrediente.nombre)" class="status-badge added">Agregado</span>
                  </label>
                </div>
              </div>

            </div>

            <!-- Footer con Selector de Cantidad y Botón de Añadir -->
            <div class="purchase-actions">
              <div class="quantity-selector">
                <button class="quantity-btn" @click="decreaseQuantity">
                  <Minus :size="18" />
                </button>
                <span class="quantity-value">{{ quantity }}</span>
                <button class="quantity-btn" @click="increaseQuantity">
                  <Plus :size="18" />
                </button>
              </div>

              <button 
                class="add-to-cart-btn" 
                :disabled="!selectedType || !isTypeAvailable(selectedType)"
                @click="handleAddToCart"
              >
                <span class="btn-text">{{ isTypeAvailable(selectedType) ? 'AÑADIR' : 'DESACTIVADO' }}</span>
                <span class="btn-total">${{ totalPriceFormatted }}</span>
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { X, Plus, Minus } from 'lucide-vue-next';

const props = defineProps<{
  isOpen: boolean;
  product: any; 
}>();

const emit = defineEmits(['close', 'add-to-cart']);

const selectedSize = ref('');
const selectedType = ref<any>(null);
const excludedIngredients = ref<string[]>([]);
const addedExtraIngredients = ref<string[]>([]);
const quantity = ref(1);

const selectedProductImage = computed(() => {
  return selectedType.value?.image || props.product?.image || '';
});

const activeTypes = computed(() => {
  if (!props.product || !props.product.types) return [];
  return props.product.types.filter((t: any) => {
    if (t.active === false || t.activo === false || t.estado === 0 || t.activo === 0) return false;
    return true;
  });
});

const getEffectiveSizeForType = (tipo: any) => {
  if (!tipo || !tipo.prices) return '';

  const keys = Object.keys(tipo.prices || {});
  const preferred = selectedSize.value || newProductFallbackSize.value || '';

  if (preferred && tipo.prices[preferred] != null) {
    return preferred;
  }

  const positiveSize = keys.find((sizeName: string) => Number(tipo.prices[sizeName] ?? 0) > 0);
  return positiveSize || keys[0] || '';
};

const newProductFallbackSize = computed(() => {
  if (!props.product?.sizes?.length) return '';
  return String(props.product.sizes[0]);
});

const isTypeAvailable = (tipo: any) => {
  if (!tipo) return false;
  if (tipo.active === false || tipo.activo === false || tipo.estado === 0 || tipo.activo === 0) return false;

  const effectiveSize = selectedSize.value || getEffectiveSizeForType(tipo);
  if (effectiveSize && tipo.prices) {
    const priceForSelectedSize = Number(tipo.prices[effectiveSize] ?? 0);
    if (priceForSelectedSize <= 0) {
      return false;
    }
  }

  return true;
};

// Al cambiar el producto reseteamos estados
watch(() => props.product, (newProduct) => {
  if (newProduct && newProduct.types) {
    const firstAvailable = newProduct.types.find((t: any) => isTypeAvailable(t));
    const fallbackSize = firstAvailable
      ? (newProduct.sizes?.[0] || getEffectiveSizeForType(firstAvailable) || '')
      : (newProduct.sizes?.[0] || '');

    selectedSize.value = fallbackSize;
    selectedType.value = firstAvailable || newProduct.types[0] || null;
    excludedIngredients.value = [];
    addedExtraIngredients.value = [];
    quantity.value = 1;
  }
}, { immediate: true });

const getSizePrice = (sizeName: string) => {
  if (!selectedType.value || !selectedType.value.prices) return '';
  const val = selectedType.value.prices[sizeName];
  if (val == null) return '';
  return Number(val).toLocaleString('es-CL');
};

const selectType = (tipo: any) => {
  if (!isTypeAvailable(tipo)) return;
  selectedType.value = tipo;
  excludedIngredients.value = [];
  addedExtraIngredients.value = [];
};

const isIngredientAvailable = (pi: any) => {
  if (!pi || !pi.ingrediente) return true;
  const ing = pi.ingrediente;
  if (ing.disponible === false || ing.disponible === 0) return false;
  if (ing.cantidad_actual !== undefined && Number(ing.cantidad_actual) <= 0) return false;
  return true;
};

const toggleIngredient = (piOrName: any) => {
  if (typeof piOrName === 'object' && !isIngredientAvailable(piOrName)) return;
  const nombre = typeof piOrName === 'object' ? piOrName.ingrediente?.nombre : piOrName;
  if (!nombre) return;
  const index = excludedIngredients.value.indexOf(nombre);
  if (index > -1) {
    excludedIngredients.value.splice(index, 1);
  } else {
    excludedIngredients.value.push(nombre);
  }
};

const toggleExtraIngredient = (piOrName: any) => {
  if (typeof piOrName === 'object' && !isIngredientAvailable(piOrName)) return;
  const nombre = typeof piOrName === 'object' ? piOrName.ingrediente?.nombre : piOrName;
  if (!nombre) return;
  const index = addedExtraIngredients.value.indexOf(nombre);
  if (index > -1) {
    addedExtraIngredients.value.splice(index, 1);
  } else {
    addedExtraIngredients.value.push(nombre);
  }
};

const isBaseIngredient = (name: string) => {
  if (!name) return false;
  const lower = name.toLowerCase();
  return lower.startsWith('pan ') ||
         lower === 'vianesa' ||
         lower === 'carne' ||
         lower === 'lomito' ||
         lower === 'pollo' ||
         lower === 'hamburguesa' ||
         lower === 'masa pizza' ||
         lower === 'sopaipilla' ||
         lower === 'empanada'||
         lower === 'papas fritas' ||
         lower === 'queso gauda';
};

// 1. Ingredientes de Receta Base (incluido_por_defecto !== false)
const visibleRecipeIngredients = computed(() => {
  if (!selectedType.value || !selectedType.value.producto_ingrediente) return [];

  const currentSizeObj = props.product.tamaños_obj?.find((t: any) => t.nombre === selectedSize.value);
  const currentSizeId = currentSizeObj ? currentSizeObj.id_tamaño : null;

  const seenNames = new Set<string>();
  const result: any[] = [];

  for (const pi of selectedType.value.producto_ingrediente) {
    // Solo receta base
    if (pi.incluido_por_defecto === false || pi.incluido_por_defecto === 0) continue;

    const ingName = pi.ingrediente?.nombre || '';
    if (isBaseIngredient(ingName)) continue;

    if (pi.id_tamaño && currentSizeId && pi.id_tamaño !== currentSizeId) {
      continue;
    }

    if (!seenNames.has(ingName)) {
      seenNames.add(ingName);
      result.push(pi);
    }
  }

  return result;
});

// 2. Ingredientes Extra Opcionales (incluido_por_defecto === false)
const optionalExtraIngredients = computed(() => {
  if (!selectedType.value || !selectedType.value.producto_ingrediente) return [];

  const seenNames = new Set<string>();
  const result: any[] = [];

  for (const pi of selectedType.value.producto_ingrediente) {
    if (pi.incluido_por_defecto !== false && pi.incluido_por_defecto !== 0) continue;

    const ingName = pi.ingrediente?.nombre || '';

    if (!seenNames.has(ingName)) {
      seenNames.add(ingName);
      result.push(pi);
    }
  }

  return result;
});

const increaseQuantity = () => quantity.value++;
const decreaseQuantity = () => { if (quantity.value > 1) quantity.value--; };

// Cálculo del costo adicional por agregados opcionales
const extraIngredientsCost = computed(() => {
  const extraCount = addedExtraIngredients.value.length;
  const includedCount = props.product?.cantidad_incluida || 0;
  const billableExtras = Math.max(0, extraCount - includedCount);
  const extraPrice = Number(props.product?.precio_ingrediente_extra || 0);
  return billableExtras * extraPrice;
});

const currentPrice = computed(() => {
  if (!selectedType.value) return 0;
  const effectiveSize = selectedSize.value || getEffectiveSizeForType(selectedType.value);
  if (!effectiveSize) return 0;
  const basePrice = Number(selectedType.value.prices[effectiveSize] || 0);
  return basePrice + extraIngredientsCost.value;
});

const totalPriceFormatted = computed(() => {
  return (currentPrice.value * quantity.value).toLocaleString('es-CL');
});

const selectedSizeId = computed(() => {
  if (!selectedType.value) return 1;
  const effectiveSize = selectedSize.value || getEffectiveSizeForType(selectedType.value);
  const list = selectedType.value.tamaños_obj || props.product?.tamaños_obj || [];
  const found = list.find((t: any) => t.nombre === effectiveSize);
  return found?.id_tamaño || found?.id || 1;
});

const handleAddToCart = () => {
  if (!selectedType.value || !isTypeAvailable(selectedType.value)) return;

  const effectiveSize = selectedSize.value || getEffectiveSizeForType(selectedType.value);
  const exclusionKey = [...excludedIngredients.value].sort().join('-');
  const additionKey = [...addedExtraIngredients.value].sort().join('-');
  const cartItemId = `${selectedType.value.id}_${effectiveSize}_${exclusionKey}_${additionKey}`;

  const excluidosDetails = excludedIngredients.value.map((name: string) => {
    const found = selectedType.value?.producto_ingrediente?.find(
      (pi: any) => pi.ingrediente?.nombre === name
    );
    return {
      nombre: name,
      id_ingrediente: found?.id_ingrediente || found?.ingrediente?.id_ingrediente || null
    };
  });

  const agregadosDetails = addedExtraIngredients.value.map((name: string) => {
    const found = selectedType.value?.producto_ingrediente?.find(
      (pi: any) => pi.ingrediente?.nombre === name
    );
    return {
      nombre: name,
      id_ingrediente: found?.id_ingrediente || found?.ingrediente?.id_ingrediente || null
    };
  });

  emit('add-to-cart', {
    id: cartItemId,
    id_producto: Number(selectedType.value.id),
    id_tamaño: Number(selectedSizeId.value),
    productId: Number(selectedType.value.id),
    name: selectedType.value.name,
    fullName: props.product.types.length > 1 
      ? `${props.product.name} ${selectedType.value.name}`
      : props.product.name,
    category: props.product.category,
    image: props.product.image,
    size: effectiveSize,
    quantity: quantity.value,
    price: currentPrice.value,
    excluidos: [...excludedIngredients.value],
    agregados: [...addedExtraIngredients.value],
    excluidosDetails,
    agregadosDetails
  });

  emit('close');
};
</script>

<style scoped>
/* ESTILOS BASE Y MODAL */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(30, 27, 36, 0.7);
  display: flex; align-items: center; justify-content: center;
  z-index: 2500;
  backdrop-filter: blur(4px);
}

.modal-wrapper {
  background-color: #f5ebe0;
  width: 850px;
  max-width: 95%;
  height: 85vh;
  max-height: 700px;
  border-radius: 20px;
  position: relative;
  box-shadow: 0 15px 40px rgba(0,0,0,0.2);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.close-btn {
  position: absolute;
  top: 15px; right: 15px;
  background: white; border: none; cursor: pointer;
  color: var(--DC-gray);
  width: 40px; height: 40px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  z-index: 10;
  transition: all 0.2s;
}
.close-btn:hover { background: var(--DC-orange); color: white; transform: scale(1.05); }

/* GRILLA DIVIDIDA */
.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1.3fr;
  height: 100%;
}

.product-img-box {
  background-color: white;
  height: 100%;
}

.main-product-img {
  width: 100%; height: 100%; object-fit: cover;
}

/* PANEL DERECHO (CONFIGURADOR) */
.product-info-box {
  padding: 30px;
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: white;
  overflow: hidden;
}

.header-info { margin-bottom: 20px; border-bottom: 2px solid #eeedee; padding-bottom: 15px;}
.tag { font-size: 0.85rem; font-weight: 900; color: var(--DC-orange); text-transform: uppercase; letter-spacing: 1px;}
.product-title { margin: 5px 0 0 0; font-size: 2rem; color: var(--DC-gray); font-weight: 900; line-height: 1.1;}

/* SCROLL INTERNO PARA OPCIONES */
.scrollable-config {
  flex: 1;
  overflow-y: auto;
  padding-right: 10px;
  display: flex;
  flex-direction: column;
  gap: 25px;
  min-height: 0;
}

.scrollable-config::-webkit-scrollbar { width: 6px; }
.scrollable-config::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.scrollable-config::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

.section-subtitle { font-size: 1rem; font-weight: 800; color: var(--DC-gray); margin-bottom: 12px; }
.subtitle-hint { font-size: 0.8rem; font-weight: 600; color: var(--DC-orange); }

/* 1. TAMAÑOS (PILLS) */
.size-pills { display: flex; flex-wrap: wrap; gap: 10px; }
.size-pill {
  padding: 8px 16px; border-radius: 20px; font-weight: 800; font-size: 0.9rem;
  background: white; border: 2px solid #eeedee; color: var(--DC-text-gray);
  cursor: pointer; transition: all 0.2s; display: flex; gap: 6px; align-items: center;
}
.size-pill.active { background: var(--DC-orange); border-color: var(--DC-orange); color: white; }
.pill-price { opacity: 0.9; font-weight: 700; font-size: 0.85rem; }

/* 2. TIPOS (RADIOS) */
.types-list { display: flex; flex-direction: column; gap: 10px; }
.type-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 15px; border-radius: 12px; border: 2px solid #eeedee;
  cursor: pointer; transition: all 0.2s; background: white;
}
.type-row:hover { border-color: #ccc; }
.type-row.active { border-color: var(--DC-orange); background-color: rgba(226, 135, 67, 0.05); }
.type-row.disabled {
  opacity: 0.55;
  background-color: #f1f5f9;
  border-color: #cbd5e1;
  cursor: not-allowed !important;
}
.type-row.disabled .t-name { color: #94a3b8; }
.type-price-box { display: flex; align-items: center; gap: 8px; }

.type-info { display: flex; align-items: center; gap: 12px; }
.radio-circle { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #ccc; display: flex; align-items: center; justify-content: center; }
.type-row.active .radio-circle { border-color: var(--DC-orange); }
.radio-inner { width: 10px; height: 10px; border-radius: 50%; background: var(--DC-orange); }
.type-texts { display: flex; flex-direction: column; }
.t-name { font-weight: 800; color: var(--DC-gray); font-size: 1rem;}
.t-desc { font-size: 0.75rem; color: var(--DC-text-gray); font-weight: 600;}
.t-price { font-weight: 900; color: var(--DC-orange); font-size: 1.1rem; }

/* 3. INGREDIENTES */
.ingredients-list { display: flex; flex-direction: column; gap: 8px; }
.ingredient-item {
  display: flex; justify-content: space-between; align-items: center;
  background: #f8f9fa; padding: 12px 15px; border-radius: 10px;
  border: 1px solid #eeedee; cursor: pointer; transition: all 0.2s;
}
.ingredient-item:hover { background: #f1f3f5; }
.ingredient-item.removed { opacity: 0.7; background-color: #fff0f3; border-color: #ffc9c9; }
.ingredient-item.added { background-color: #ebfbee; border-color: #b2f2bb; }
.ingredient-item.out-of-stock {
  opacity: 0.55;
  background-color: #f1f5f9;
  border-color: #cbd5e1;
  cursor: not-allowed !important;
}
.ingredient-item.out-of-stock .ing-name {
  color: #94a3b8;
  text-decoration: line-through;
}
.ing-left { display: flex; align-items: center; gap: 12px; }
.custom-checkbox { width: 18px; height: 18px; accent-color: var(--DC-orange); cursor: pointer; }
.custom-checkbox:disabled { cursor: not-allowed; }
.ingredient-item.removed .ing-name { text-decoration: line-through; color: var(--DC-text-gray); }
.ing-name { font-weight: 700; color: var(--DC-gray); font-size: 0.9rem; }

.status-badge { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; }
.status-badge.removed { background: #ffc9c9; color: #c92a2a; }
.status-badge.added { background: #b2f2bb; color: #2b8a3e; }
.status-badge.no-stock { background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; }

/* FOOTER COMPRAS */
.purchase-actions {
  display: flex; gap: 15px; align-items: center;
  margin-top: 20px; padding-top: 20px;
  border-top: 2px solid #eeedee;
}

.quantity-selector {
  display: flex; align-items: center; justify-content: space-between;
  border: 2px solid #eeedee; border-radius: 12px; padding: 5px;
  background-color: white; width: 130px;
}
.quantity-btn {
  background: #f1f3f5; border: none; width: 35px; height: 35px;
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: var(--DC-gray); transition: background-color 0.2s;
}
.quantity-btn:hover { background: #e9ecef; }
.quantity-value { font-size: 1.1rem; font-weight: 900; color: var(--DC-gray); }

.add-to-cart-btn {
  flex: 1; background-color: var(--DC-brown); color: white;
  border: none; padding: 15px 20px; border-radius: 12px;
  display: flex; justify-content: space-between; align-items: center;
  cursor: pointer; box-shadow: 0 4px 15px rgba(81, 49, 25, 0.3);
  transition: all 0.2s;
}
.add-to-cart-btn:hover { background-color: var(--DC-orange); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(226, 135, 67, 0.3); }
.add-to-cart-btn:active { transform: translateY(0); }
.add-to-cart-btn:disabled {
  background-color: #cbd5e1 !important;
  color: #94a3b8 !important;
  box-shadow: none !important;
  cursor: not-allowed !important;
  transform: none !important;
}
.btn-text { font-weight: 900; font-size: 1rem; letter-spacing: 1px;}
.btn-total { font-weight: 900; font-size: 1.2rem; }

/* ANIMACIONES */
.pop-enter-active, .pop-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.pop-enter-from, .pop-leave-to { opacity: 0; }
.pop-enter-from .modal-wrapper, .pop-leave-to .modal-wrapper { transform: scale(0.95) translateY(20px); }

/* RESPONSIVIDAD */
@media (max-width: 768px) {
  .modal-wrapper { 
    width: 95vw;
    height: 92vh; 
    max-height: 92vh; 
    border-radius: 18px; 
  }

  .close-btn {
    top: 10px;
    right: 10px;
    width: 34px;
    height: 34px;
  }

  .modal-grid { 
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
  }

  .product-img-box {
    height: 130px;
    width: 100%;
    flex-shrink: 0;
  }

  .main-product-img {
    height: 100%;
    object-fit: cover;
  }

  .product-info-box { 
    padding: 14px 12px; 
    display: flex;
    flex-direction: column;
    flex: 1;
    overflow: hidden;
    min-height: 0;
  }

  .header-info {
    margin-bottom: 10px;
    padding-bottom: 8px;
  }

  .product-title {
    font-size: 1.4rem;
  }

  .scrollable-config {
    gap: 12px;
    flex: 1;
    overflow-y: auto;
    min-height: 0;
    padding-bottom: 8px;
  }

  .purchase-actions { 
    flex-direction: row; 
    margin-top: 8px;
    padding-top: 10px;
    gap: 10px;
    border-top: 2px solid #eeedee;
    background-color: white;
    flex-shrink: 0;
  }
  .quantity-selector { width: 110px; padding: 4px; box-sizing: border-box; }
  .quantity-btn { width: 30px; height: 30px; }
  .add-to-cart-btn { flex: 1; padding: 10px 14px; box-sizing: border-box; }
}

@media (max-height: 600px) {
  .modal-wrapper {
    height: 96vh;
    max-height: 96vh;
  }
  .product-img-box {
    height: 80px;
  }
}
</style>