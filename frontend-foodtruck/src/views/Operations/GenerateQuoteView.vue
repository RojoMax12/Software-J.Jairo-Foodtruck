<template>
  <div class="admin-quote-wizard">
    <div class="wizard-header">
      <h1>Generar Nuevo Pedido</h1>
      <div class="steps-indicator">
        <div :class="['step', { active: currentStep >= 1 }]">1. Productos</div>
        <div class="line"></div>
        <div :class="['step', { active: currentStep >= 2 }]">2. Cliente</div>
        <div class="line"></div>
        <div :class="['step', { active: currentStep >= 3 }]">3. Resumen</div>
      </div>
    </div>

    <div v-if="currentStep === 1" class="step-container product-step">
      <!-- BARRA DE PESTAÑAS MÓVIL -->
      <div class="mobile-tabs-bar">
        <button 
          class="mobile-tab-btn" 
          :class="{ 'active': mobileTab === 'catalog' }" 
          @click="mobileTab = 'catalog'"
        >
          <Utensils :size="16" /> Catálogo
        </button>
        
        <button 
          class="mobile-tab-btn" 
          :class="{ 'active': mobileTab === 'recipe' }" 
          @click="mobileTab = 'recipe'"
        >
          <FileText :size="16" /> Receta
        </button>

        <button 
          class="mobile-tab-btn" 
          :class="{ 'active': mobileTab === 'cart' }" 
          @click="mobileTab = 'cart'"
        >
          <ShoppingCart :size="16" /> Comanda ({{ totalUnits }})
        </button>
      </div>

      <div class="product-layout" :class="`show-tab-${mobileTab}`">
        
        <div class="catalog-section">
          <div class="catalog-header">
            <h3>Selección de Productos</h3>
            <div class="filters">
              <select v-model="selectedCategory" class="dc-select">
                <option value="Todas">Todas las categorías</option>
                <option v-for="cat in categoriesList" :key="cat.id" :value="cat.nombre_categoria">
                  {{ cat.nombre_categoria }}
                </option>
              </select>
            </div>
          </div>

          <div v-if="isLoadingProducts" class="products-grid-admin">
            <div v-for="n in 6" :key="'gen-skel-' + n" class="brown-menu-card-skeleton">
              <div class="skeleton-img"></div>
              <div class="skeleton-body">
                <div class="skeleton-pill width-120"></div>
                <div class="skeleton-pill width-80 margin-top-4"></div>
              </div>
            </div>
          </div>
          <div v-else class="products-grid-admin">
            <div 
              v-for="p in filteredProducts" 
              :key="p.id" 
              class="brown-menu-card"
            >
              <img :src="p.image" :alt="p.name" />
              
              <div class="brown-card-body">
                <h3 class="card-main-title">{{ p.name }}</h3>
                
                <div v-if="p.sizes.length > 1" class="size-pills-container">
                  <button 
                    v-for="size in p.sizes" 
                    :key="size"
                    class="size-pill"
                    :class="{ 'active-pill': p.activeSize === size }"
                    @click="p.activeSize = size"
                  >
                    {{ size }}
                  </button>
                </div>
                <div v-else class="single-size-spacer"></div>

                <div class="type-section-title">Tipo / Variedad</div>

                <div class="variants-list">
                  <div 
                    v-for="tipo in p.types" 
                    :key="tipo.id"
                    class="variant-row"
                    :class="{ 'active-row': activeVariant?.type.id === tipo.id && activeVariant?.size === p.activeSize }"
                    @click="selectVariant(p, tipo)"
                  >
                    <div class="variant-left">
                      <div class="radio-circle">
                        <div class="radio-inner" v-if="activeVariant?.type.id === tipo.id && activeVariant?.size === p.activeSize"></div>
                      </div>
                      <div class="variant-texts">
                        <span class="v-name">{{ tipo.name }}</span>
                        <span class="v-desc">{{ tipo.desc }}</span>
                      </div>
                    </div>
                    <span class="v-price">${{ tipo.prices[p.activeSize] }}</span>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>

        <div class="box-ingredient-card">
          <div class="card-header">
            <FileText :size="20" />
            <span>Personalizar Receta</span>
          </div>
          
          <div class="ingredient-content">
            <template v-if="activeVariant">
              <h2 class="product-name-highlight">
                {{ activeVariant.baseName }} ({{ activeVariant.size }})
              </h2>

              <!-- MODO PERSONALIZABLE (Pizzas, Hamburguesas, Fajitas) -->
              <div v-if="isPersonalizableProduct" class="ingredient-list">
                <h4>
                  Ingredientes opcionales a elección 
                  <span class="subtitle-hint">
                    (Incluye {{ activeVariant.cantidad_incluida || 3 }} gratis, extra +${{ activeVariant.precio_ingrediente_extra || 500 }} c/u)
                  </span>
                </h4>
                
                <div class="ingredients-wrapper">
                  <label 
                    v-for="pi in optionalExtraIngredients" 
                    :key="pi.id" 
                    class="ingredient-item-row"
                    :class="{ 
                      'ingredient-added': addedExtraIngredients.includes(pi.ingrediente?.nombre),
                      'ingredient-disabled': !pi.ingrediente?.disponible
                    }"
                  >
                    <div class="ing-left">
                      <input 
                        type="checkbox" 
                        :checked="addedExtraIngredients.includes(pi.ingrediente?.nombre)"
                        @change="toggleExtraIngredient(pi.ingrediente?.nombre)"
                        :disabled="!pi.ingrediente?.disponible"
                        class="custom-checkbox"
                      />
                      <span class="ing-name">{{ pi.ingrediente?.nombre }}</span>
                    </div>

                    <span v-if="!pi.ingrediente?.disponible" class="ing-status no-stock">Sin Stock</span>
                    <span v-else-if="addedExtraIngredients.includes(pi.ingrediente?.nombre)" class="ing-status added">AGREGADO</span>
                    <span v-else class="ing-status ok">Opcional</span>
                  </label>
                </div>
              </div>

              <!-- MODO ESTANDAR (Vianesas, Churrascos, Ass, Lomitos) -->
              <div v-else class="ingredient-list">
                <h4>Ingredientes incluidos (Desmarca para quitar)</h4>
                
                <div class="ingredients-wrapper">
                  <label 
                    v-for="pi in customizableRecipeIngredients" 
                    :key="pi.id" 
                    class="ingredient-item-row"
                    :class="{ 
                      'ingredient-removed': excludedIngredients.includes(pi.ingrediente?.nombre),
                      'ingredient-disabled': !pi.ingrediente?.disponible
                    }"
                  >
                    <div class="ing-left">
                      <input 
                        type="checkbox" 
                        :checked="!excludedIngredients.includes(pi.ingrediente?.nombre)"
                        @change="toggleIngredient(pi.ingrediente?.nombre)"
                        :disabled="!pi.ingrediente?.disponible"
                        class="custom-checkbox"
                      />
                      <span class="ing-name">{{ pi.ingrediente?.nombre }}</span>
                    </div>

                    <span v-if="!pi.ingrediente?.disponible" class="ing-status no-stock">Sin Stock</span>
                    <span v-else-if="excludedIngredients.includes(pi.ingrediente?.nombre)" class="ing-status removed">QUITADO</span>
                    <span v-else class="ing-status ok">Lleva</span>
                  </label>
                </div>
              </div>

              <button class="btn-add-to-cart-large" @click="addActiveVariantToCart">
                AÑADIR A COMANDA - ${{ currentVariantUnitPrice }}
              </button>
            </template>
            
            <p v-else class="no-selection-text">
              Selecciona una variedad en las tarjetas de la izquierda para ver sus ingredientes.
            </p>
          </div>
        </div>

        <aside class="cart-summary-admin">
          <div class="cart-header">
            <ShoppingCart :size="20" />
            <span>Comanda Actual ({{ totalUnits }} {{ totalUnits === 1 ? 'unidad' : 'unidades' }})</span>
          </div>
          <div class="cart-items-list">
            <div v-if="cartItems.length === 0" class="empty-cart">No hay productos en la orden</div>
            <div v-for="(item, idx) in cartItems" :key="item.id" class="cart-item-admin">
              <div class="item-main">
                <div class="item-title-block">
                  <span class="item-name">{{ item.fullName }} ({{ item.size }})</span>
                  <span v-if="item.excluidos && item.excluidos.length > 0" class="badge-removed-items">
                    SIN: {{ item.excluidos.join(', ') }}
                  </span>
                  <span v-if="item.agregados && item.agregados.length > 0" class="badge-added-items">
                    CON: {{ item.agregados.join(', ') }}
                  </span>
                </div>
                <span class="item-price">${{ item.price * item.quantity }}</span>
              </div>
              <div class="item-controls">
                <div class="qty-btn" @click="updateQuantity(idx, -1)"><Minus :size="12" /></div>
                <span>{{ item.quantity }}</span>
                <div class="qty-btn" @click="updateQuantity(idx, 1)"><Plus :size="12" /></div>
                <button class="btn-delete" @click="removeFromCart(idx)"><Trash2 :size="14" /></button>
              </div>
            </div>
          </div>
          <div class="cart-total">
            <span>Total:</span>
            <strong>{{ totalQuote }}</strong>
          </div>
        </aside>

      </div>

      <!-- BARRA FLOTANTE MÓVIL EN LA PARTE INFERIOR -->
      <div v-if="cartItems.length > 0 && currentStep === 1" class="mobile-floating-cart-bar" @click="mobileTab = 'cart'">
        <div class="cart-bar-info">
          <span class="badge-count">{{ cartItems.reduce((acc, i) => acc + i.quantity, 0) }}</span>
          <span class="cart-bar-total">{{ totalQuote }}</span>
        </div>
        <button class="btn-checkout-mobile" @click.stop="nextStep">
          Ir a Cobrar <ArrowRight :size="16" />
        </button>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="router.push('/general-home')">Cancelar</button>
        <button class="btn btn-primary" @click="nextStep">
          Continuar a Cliente <ArrowRight :size="18" />
        </button>
      </div>
    </div>

    <div v-if="currentStep === 2" class="step-container client-step">
      <div class="selection-mode">
        <div class="section-intro">
          <h3>Datos del cliente y Pago</h3>
          <p>Completa la información para confirmar la comanda con mayor claridad.</p>
        </div>
        <form class="distributor-form">
          <div class="input-row">
            <div class="input-group">
              <label>Nombre y Apellido Cliente</label>
              <input v-model="customerForm.nombre" type="text" placeholder="Ej: Johan Neira" class="dc-input" />
            </div>
          </div>
          <div class="input-row">
            <div class="input-group">
              <label>Teléfono (Opcional)</label>
              <input v-model="customerForm.telefono" type="text" placeholder="+569..." class="dc-input" @input="handlePhoneInput"/>
            </div>
          </div>
          <div class="input-row">
            <div class="input-group">
              <label>Método de Pago</label>
              <select v-model="selectedPaymentMethod" class="dc-input">
                <option value="" disabled>Seleccione método de pago</option>
                <option v-for="m in metodosdepago" :key="m.id" :value="m.nombre">{{ m.nombre }}</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="actions">
        <button class="btn btn-secondary" @click="currentStep = 1"><ArrowLeft :size="18" /> Volver a Productos</button>
        <button class="btn btn-primary" @click="nextStep">Revisar Resumen <ArrowRight :size="18" /></button>
      </div>
    </div>

    <div v-if="currentStep === 3" class="step-container summary-step">
      <div class="section-intro">
        <h3>Resumen Final del pedido</h3>
        <p>Revisa el pedido antes de enviarlo a cocina.</p>
      </div>
      <div class="summary-grid">
        <div class="summary-section">
          <h4>Datos del cliente</h4>
          <div class="summary-card">
            <p><strong>Cliente:</strong> {{ customerForm.nombre || 'Cliente Presencial' }}</p>
            <p><strong>Teléfono:</strong> {{ customerForm.telefono || 'No registrado' }}</p>
            <p><strong>Método de Pago:</strong> {{ selectedPaymentMethod }}</p>
          </div>
        </div>
        <div class="summary-section">
          <h4>Productos Seleccionados</h4>
          <div class="summary-card products-list-final">
            <div v-for="item in cartItems" :key="item.id" class="final-item">
              <div class="final-item-meta">
                <span class="final-item-name">{{ item.quantity }}x {{ item.fullName }} ({{ item.size }})</span>
                <div v-if="item.excluidos && item.excluidos.length > 0" class="final-exclusions-box">
                  <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge-item">❌ SIN: {{ ing }}</span>
                </div>
                <div v-if="item.agregados && item.agregados.length > 0" class="final-exclusions-box">
                  <span v-for="ing in item.agregados" :key="ing" class="exclusion-badge-item added">✅ CON: {{ ing }}</span>
                </div>
              </div>
              <span class="final-item-price">${{ item.price * item.quantity }}</span>
            </div>
            <div class="final-total">
              <span>Total a Pagar</span>
              <strong>{{ totalQuote }}</strong>
            </div>
          </div>
        </div>
      </div>
      <div class="actions">
        <button class="btn btn-secondary" @click="currentStep = 2"><ArrowLeft :size="18" /> Editar Cliente</button>
        <button class="btn btn-primary" @click="confirmQuote" :disabled="isSubmitting">
          {{ isSubmitting ? 'Procesando...' : 'Confirmar y Enviar a Cocina' }}
        </button>
      </div>
    </div>
  </div>
</template>


<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Search, ArrowRight, ArrowLeft, ShoppingCart, Trash2, Plus, Minus, FileText, Utensils } from 'lucide-vue-next';
import productService from '@/services/productService';
import categoryService from '@/services/categoryService';
import orderService from '@/services/orderService';
import { useNotification } from '@/composables/useNotification';

const router = useRouter();
const { notify } = useNotification();
const currentStep = ref(1);
const isSubmitting = ref(false);

const mobileTab = ref<'catalog' | 'recipe' | 'cart'>('catalog');
const activeVariant = ref<any>(null);
const excludedIngredients = ref<string[]>([]);
const addedExtraIngredients = ref<string[]>([]);
const selectedPaymentMethod = ref('');

const customerForm = ref({ nombre: '', telefono: '+56' });

const foodProducts = ref<any[]>([]);
const categoriesList = ref<any[]>([]);
const cartItems = ref<any[]>([]);
const selectedCategory = ref('Todas');
const productSearch = ref('');
const metodosdepago = ref<any[]>([]);

const cargarMetodosSimulados = () => {
  metodosdepago.value = [
    { id: 1, nombre: 'Efectivo' }, { id: 2, nombre: 'Tarjeta de Débito' }, { id: 3, nombre: 'Tarjeta de Crédito' }, { id: 4, nombre: 'Transferencia' }
  ];
};

const isLoadingProducts = ref(true);

const fetchProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      productService.getPublicProducts(),
      categoryService.getPublicCategories()
    ]);

    const dbProducts = productsRes.data || [];
    const dbCategories = categoriesRes.data || [];

    categoriesList.value = dbCategories.map((c: any) => ({
      id: c.id_categoria,
      nombre_categoria: c.nombre_categoria
    }));

    const categoryImages: Record<string, string> = {
      'Vianesas': 'https://images.unsplash.com/photo-1612392062798-7c7e16d7f49f?w=900',
      'Ass': 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=900',
      'Churrascos': 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=900',
      'Lomitos': 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=900',
      'Hamburguesas': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=900',
      'Pizzas': 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=900',
      'Fajitas': 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=900',
      'Sándwich de Pollo': 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=900',
      'Papas & Chorrillanas': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=900',
      'Empanadas & Sopaipillas': 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=900',
      'Bebestibles & Jugos': 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=900'
    };

    foodProducts.value = dbProducts.map((prod: any) => {
      const catName = prod.categoria?.nombre_categoria || 'Varios';
      const sizesArray = (prod.tamaños || []).map((t: any) => t.nombre);
      const pricesMap: Record<string, number> = {};
      const sizesMap: Record<string, number> = {};

      (prod.tamaños || []).forEach((t: any) => {
        pricesMap[t.nombre] = Number(t.pivot?.precio || 0);
        sizesMap[t.nombre] = Number(t.id_tamaño || t.id || 1);
      });

      return {
        id: prod.id_producto,
        name: prod.nombre,
        category: catName,
        image: categoryImages[catName] || 'https://images.unsplash.com/photo-1567620812782-f461bc805b46?w=900',
        tipo_armado: prod.tipo_armado || 'Estandar',
        cantidad_incluida: prod.cantidad_incluida ?? 0,
        precio_ingrediente_extra: Number(prod.precio_ingrediente_extra || 0),
        sizes: sizesArray,
        activeSize: sizesArray[0] || 'Único',
        tamano_id: prod.tamaños?.[0]?.id_tamaño || 1,
        sizesMap: sizesMap,
        types: [
          {
            id: prod.id_producto,
            name: prod.nombre,
            desc: prod.descripcion,
            prices: pricesMap,
            producto_ingrediente: prod.ingredientes || []
          }
        ]
      };
    });
  } catch (error) {
    console.error('Error cargando productos en Generar Pedido:', error);
  } finally {
    isLoadingProducts.value = false;
  }
};

const BASE_INGREDIENT_NAMES = [
  'pan', 'pan completo', 'pan frica', 'pan marraqueta',
  'vianesa', 'carne', 'hamburguesa', 'pollo', 'lomo',
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

const isPersonalizableProduct = computed(() => {
  if (!activeVariant.value) return false;
  const p = activeVariant.value;
  const cat = (p.baseProduct?.category || '').toLowerCase();
  return p.tipo_armado === 'Personalizable' || 
         cat.includes('hamburguesa') || 
         cat.includes('pizza') || 
         cat.includes('fajita') || 
         (p.cantidad_incluida && p.cantidad_incluida > 0);
});

const customizableRecipeIngredients = computed(() => {
  if (!activeVariant.value || !activeVariant.value.type || !activeVariant.value.type.producto_ingrediente) return [];
  return activeVariant.value.type.producto_ingrediente.filter((pi: any) => {
    if (pi.incluido_por_defecto === false || pi.incluido_por_defecto === 0) return false;
    const ingName = pi.ingrediente?.nombre || '';
    return !isBaseIngredient(ingName);
  });
});

const optionalExtraIngredients = computed(() => {
  if (!activeVariant.value || !activeVariant.value.type || !activeVariant.value.type.producto_ingrediente) return [];
  const seenNames = new Set<string>();
  const result: any[] = [];

  for (const pi of activeVariant.value.type.producto_ingrediente) {
    const ingName = pi.ingrediente?.nombre || '';
    if (!ingName) continue;

    if (!seenNames.has(ingName)) {
      seenNames.add(ingName);
      result.push(pi);
    }
  }

  return result;
});

const extraIngredientsCost = computed(() => {
  if (!activeVariant.value || !isPersonalizableProduct.value) return 0;
  const count = addedExtraIngredients.value.length;
  const included = activeVariant.value.cantidad_incluida || 3;
  const extraCount = Math.max(0, count - included);
  const extraPrice = activeVariant.value.precio_ingrediente_extra || 0;
  return extraCount * extraPrice;
});

const currentVariantUnitPrice = computed(() => {
  if (!activeVariant.value) return 0;
  const basePrice = Number(activeVariant.value.price || 0);
  return basePrice + extraIngredientsCost.value;
});

const filteredProducts = computed(() => {
  let results = foodProducts.value;
  if (selectedCategory.value !== 'Todas') {
    results = results.filter(item => item.category === selectedCategory.value);
  }
  if (productSearch.value.trim() !== '') {
    const s = productSearch.value.toLowerCase();
    results = results.filter(item => item.name.toLowerCase().includes(s));
  }
  return results;
});

const selectVariant = (baseProduct: any, type: any) => {
  const sizeName = baseProduct.activeSize;
  const tamanoId = baseProduct.sizesMap?.[sizeName] || baseProduct.tamano_id || 1;

  activeVariant.value = {
    baseProduct: baseProduct,
    baseName: baseProduct.name,
    type: type,
    size: sizeName,
    tamano_id: tamanoId,
    price: type.prices[sizeName] || 0,
    tipo_armado: baseProduct.tipo_armado || 'Estandar',
    cantidad_incluida: baseProduct.cantidad_incluida ?? 0,
    precio_ingrediente_extra: Number(baseProduct.precio_ingrediente_extra || 0)
  };
  excludedIngredients.value = [];
  addedExtraIngredients.value = [];
  mobileTab.value = 'recipe';
};

const toggleIngredient = (nombreIngrediente: string) => {
  if (!nombreIngrediente || isBaseIngredient(nombreIngrediente)) return;

  const index = excludedIngredients.value.indexOf(nombreIngrediente);
  if (index > -1) { excludedIngredients.value.splice(index, 1); } 
  else { excludedIngredients.value.push(nombreIngrediente); }
};

const toggleExtraIngredient = (nombreIngrediente: string) => {
  if (!nombreIngrediente) return;

  const index = addedExtraIngredients.value.indexOf(nombreIngrediente);
  if (index > -1) { addedExtraIngredients.value.splice(index, 1); } 
  else { addedExtraIngredients.value.push(nombreIngrediente); }
};

const addActiveVariantToCart = () => {
  if (!activeVariant.value) return;

  const isPersonalizable = isPersonalizableProduct.value;
  const exclusionKey = [...excludedIngredients.value].sort().join('-');
  const additionKey = [...addedExtraIngredients.value].sort().join('-');
  const cartItemId = `${activeVariant.value.type.id}_${activeVariant.value.size}_${exclusionKey}_${additionKey}`;

  const fullProductName = `${activeVariant.value.baseName}`;
  const finalUnitPrice = currentVariantUnitPrice.value;

  const existing = cartItems.value.find(item => item.id === cartItemId);
  if (existing) {
    existing.quantity++;
  } else {
    cartItems.value.push({
      id: cartItemId,
      productId: activeVariant.value.type.id,
      name: activeVariant.value.type.name,
      fullName: fullProductName,
      size: activeVariant.value.size,
      tamano_id: activeVariant.value.tamano_id,
      price: finalUnitPrice,
      quantity: 1,
      excluidos: isPersonalizable ? [] : [...excludedIngredients.value],
      agregados: isPersonalizable ? [...addedExtraIngredients.value] : []
    });
  }
  mobileTab.value = 'catalog';
};

const removeFromCart = (index: number) => { cartItems.value.splice(index, 1); };
const updateQuantity = (index: number, change: number) => {
  cartItems.value[index].quantity += change;
  if (cartItems.value[index].quantity <= 0) removeFromCart(index);
};

const windowQuote = computed(() => cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));
const totalQuote = computed(() => `$${windowQuote.value.toLocaleString('es-CL')}`);
const totalUnits = computed(() => cartItems.value.reduce((acc, item) => acc + item.quantity, 0));

const nextStep = () => {
  if (currentStep.value === 1) {
    if (cartItems.value.length === 0) return notify('Debe añadir al menos un producto a la comanda.', 'warning');
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    if (!customerForm.value.nombre) return notify('Por favor, ingrese el Nombre del cliente.', 'warning');
    if (!selectedPaymentMethod.value) return notify('Por favor, seleccione un Método de Pago.', 'warning');
    currentStep.value = 3;
  }
};

const handlePhoneInput = () => { if (!customerForm.value.telefono.startsWith('+56')) customerForm.value.telefono = '+56'; };

const confirmQuote = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const payload = {
      nombre_persona: customerForm.value.nombre || 'Cliente Presencial',
      numero_telefono: customerForm.value.telefono || '',
      metodo_pago: selectedPaymentMethod.value || 'Efectivo',
      total: windowQuote.value,
      items: cartItems.value.map(item => ({
        id_producto: item.productId || 1,
        id_tamaño: item.tamano_id || 1,
        cantidad: item.quantity || 1,
        precio_unitario: Number(item.price || 0),
        modificaciones: (item.excluidos || []).map((ex: string) => ({
          tipo: 'Exclusión',
          precio: 0,
          ingrediente: ex
        }))
      }))
    };

    await orderService.createPublicOrder(payload);
    notify('¡Pedido registrado y enviado a cocina exitosamente!', 'success');
    currentStep.value = 1; 
    cartItems.value = [];
    activeVariant.value = null;
    router.push('/general-home/orders');
  } catch (error) {
    console.error('Error al procesar el pedido:', error); 
    notify('Error al procesar el pedido', 'warning');
  } finally { 
    isSubmitting.value = false; 
  }
};

onMounted(() => { cargarMetodosSimulados(); fetchProducts(); });
</script>

<style scoped>
/* ----------------------------------------------------
   1. CONTENEDOR PRINCIPAL
---------------------------------------------------- */
/* ----------------------------------------------------
   1. CONTENEDOR PRINCIPAL
---------------------------------------------------- */
.admin-quote-wizard { 
  width: 98%; 
  max-width: 1600px; 
  margin: 10px auto; 
  padding: 15px 20px; 
  background: white; 
  border-radius: 20px; 
  box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
}

.wizard-header { text-align: center; margin-bottom: 0.8rem; }
.wizard-header h1 { color: #1a1624; margin-bottom: 0.4rem; font-size: 1.6rem; font-weight: 900; }
.steps-indicator { display: flex; align-items: center; justify-content: center; gap: 0.8rem; flex-wrap: wrap; }
.step { padding: 0.4rem 1rem; border-radius: 20px; background: #f0f0f0; color: #888; font-weight: 800; font-size: 0.85rem; transition: all 0.3s; }
.step.active { background: #965314; color: white; box-shadow: 0 4px 10px rgba(150, 83, 20, 0.3); }
.line { height: 3px; width: 40px; background: #eee; border-radius: 2px; }

/* ----------------------------------------------------
   2. LAYOUT DE COLUMNAS (PC: 3 Columnas compactas)
---------------------------------------------------- */
.product-layout { 
  display: grid; 
  grid-template-columns: minmax(400px, 1.8fr) 300px 320px; 
  gap: 1rem; 
  align-items: stretch; 
  width: 100%; 
  max-height: calc(100vh - 140px);
}

/* ----------------------------------------------------
   3. CATÁLOGO Y TARJETAS (Marrón POS)
---------------------------------------------------- */
.catalog-section { background: #ffffff; border-radius: 16px; width: 100%; }
.catalog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.8rem; }
.catalog-header h3 { font-size: 1.15rem; margin: 0; color: #333; font-weight: 900; }
.filters { display: flex; gap: 0.8rem; flex-wrap: wrap; flex: 1; justify-content: flex-end; }
.dc-select { padding: 0.5rem 0.8rem; border: 2px solid #965314; border-radius: 10px; font-size: 0.85rem; font-weight: 700; color: #333; background: white; cursor: pointer; }
.product-search { position: relative; display: flex; align-items: center; min-width: 180px; }
.product-search input { padding: 0.5rem 0.8rem 0.5rem 2.2rem; border: 2px solid #965314; border-radius: 10px; font-size: 0.85rem; width: 100%; font-weight: 600; }
.product-search svg { position: absolute; left: 0.6rem; color: #965314; }

.products-grid-admin { 
  display: grid; 
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
  gap: 1rem; 
  max-height: calc(100vh - 230px); 
  overflow-y: auto; 
  padding-right: 5px; 
}

/* Diseño de la Tarjeta */
.brown-menu-card { 
  background: #a05a2c; 
  border-radius: 14px; 
  overflow: hidden; 
  box-shadow: 0 4px 12px rgba(0,0,0,0.08); 
  display: flex; 
  flex-direction: column; 
  transition: transform 0.2s, box-shadow 0.2s;
  border: 2px solid transparent;
}
.brown-menu-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(160, 90, 44, 0.25); }
.brown-menu-card img { width: 100%; height: 85px; object-fit: cover; }
.brown-card-body { padding: 0.8rem; display: flex; flex-direction: column; color: white; flex: 1; }
.card-main-title { margin: 0; text-align: center; font-size: 1.1rem; font-weight: 900; text-shadow: 1px 1px 3px rgba(0,0,0,0.4); letter-spacing: 0.5px; }

/* Botones de Tamaño (Pills) */
.size-pills-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 6px; margin: 10px 0; }
.size-pill { 
  background: #cba342; border: 2px solid #e1b958; color: #111; font-weight: 900; 
  border-radius: 20px; padding: 4px 12px; cursor: pointer; transition: all 0.2s; font-size: 0.8rem;
}
.size-pill:hover { background: #dfb755; }
.active-pill { background: #ffce44; border-color: #fff; transform: scale(1.05); box-shadow: 0 3px 8px rgba(0,0,0,0.3); }

/* Radios de Variedades */
.type-section-title { text-align: center; font-weight: 900; margin-bottom: 8px; font-size: 0.95rem; color: #f2c75c; text-transform: uppercase; }
.variants-list { display: flex; flex-direction: column; gap: 6px; }
.variant-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 8px; border-radius: 6px; cursor: pointer; transition: background 0.2s; }
.variant-row:hover { background: rgba(255,255,255,0.1); }
.active-row { background: rgba(255,255,255,0.15); border-left: 4px solid #ffce44; }

.variant-left { display: flex; align-items: center; gap: 8px; overflow: hidden; }
.radio-circle { width: 16px; height: 16px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.radio-inner { width: 8px; height: 8px; border-radius: 50%; background: #ffce44; }
.variant-texts { display: flex; flex-direction: column; overflow: hidden; }
.v-name { font-weight: 900; font-size: 0.9rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }
.v-desc { font-size: 0.65rem; color: #eee; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.v-price { font-weight: 900; font-size: 0.95rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); flex-shrink: 0; color: #ffce44; }

/* ----------------------------------------------------
   4. RECETA (COLUMNA CENTRAL)
---------------------------------------------------- */
.box-ingredient-card { 
  background: #fdfdfd; 
  padding: 1.2rem; 
  border-radius: 16px; 
  border: 2px solid #a05a2c; 
  display: flex; 
  flex-direction: column; 
  max-height: calc(100vh - 160px); 
  overflow: hidden; 
}
.card-header { display: flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #a05a2c; border-bottom: 2px dashed rgba(160, 90, 44, 0.2); padding-bottom: 0.8rem; margin-bottom: 0.8rem; text-transform: uppercase; font-size: 0.9rem; }
.product-name-highlight { font-size: 1.2rem; color: #222; margin-bottom: 0.8rem; font-weight: 900; line-height: 1.2; text-transform: capitalize; }
.ingredient-list { display: flex; flex-direction: column; flex: 1; overflow: hidden; }
.ingredient-list h4 { font-size: 0.75rem; color: #777; text-transform: uppercase; margin-bottom: 0.8rem; font-weight: 800; }
.ingredients-wrapper { display: flex; flex-direction: column; gap: 0.5rem; margin-bottom: 1rem; max-height: 300px; overflow-y: auto; flex: 1; padding-right: 6px; }
.ingredient-item-row { display: flex; justify-content: space-between; align-items: center; background: #f4f4f4; padding: 10px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; color: #333; cursor: pointer; border: 1px solid #eaeaea; transition: all 0.2s; }
.ingredient-item-row:hover { background: #f0edea; border-color: #a05a2c; }
.ing-left { display: flex; align-items: center; gap: 12px; }
.custom-checkbox { width: 18px; height: 18px; accent-color: #a05a2c; cursor: pointer; }

/* ----------------------------------------------------
   5. CARRITO Y COMANDA (COLUMNA DERECHA)
---------------------------------------------------- */
.cart-summary-admin { 
  background: #faf9f7; 
  border-radius: 16px; 
  padding: 1.2rem; 
  border: 2px solid #eee; 
  display: flex; 
  flex-direction: column; 
  max-height: calc(100vh - 160px); 
  overflow-y: auto; 
}
.ingredient-removed { opacity: 0.6; background-color: #fceceb; border-color: #f5c2c7; text-decoration: line-through; }
.ingredient-added { background-color: #e6fcf5; border-color: #96f2d7; }
.ingredient-disabled { opacity: 0.5; background-color: #f1f5f9; cursor: not-allowed; }
.ing-status { font-size: 0.75rem; font-weight: 900; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
.ing-status.ok { background: #e0f8f5; color: #0f9d8a; }
.ing-status.added { background: #d3f9d8; color: #2b8a3e; }
.ing-status.removed { background: #ffe5e8; color: #d62839; }
.ing-status.no-stock { background: #fee2e2; color: #dc2626; }

.btn-add-to-cart-large { width: 100%; background: #965314; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 900; font-size: 1.1rem; cursor: pointer; margin-top: auto; box-shadow: 0 6px 15px rgba(150, 83, 20, 0.3); transition: all 0.2s; }
.btn-add-to-cart-large:hover { background: #7a410f; transform: translateY(-2px); }
.btn-add-to-cart-large:active { transform: scale(0.98); }

/* ----------------------------------------------------
   5. CARRITO Y COMANDA (COLUMNA DERECHA)
---------------------------------------------------- */
.cart-summary-admin { background: #faf9f7; border-radius: 16px; padding: 1.5rem; border: 2px solid #eee; position: sticky; top: 20px; display: flex; flex-direction: column; max-height: calc(100vh - 40px); }
.cart-header { display: flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #333; margin-bottom: 1.5rem; text-transform: uppercase; }
.cart-items-list { overflow-y: auto; flex: 1; padding-right: 5px; }
.cart-item-admin { background: white; padding: 1rem; border-radius: 12px; margin-bottom: 0.8rem; border: 1px solid #eaeaea; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
.item-main { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.6rem; gap: 10px; }
.item-title-block { display: flex; flex-direction: column; gap: 4px; }
.item-name { font-weight: 900; font-size: 0.95rem; color: #222; line-height: 1.2; }
.item-price { font-size: 1rem; color: #965314; font-weight: 900; }
.badge-removed-items { font-size: 0.7rem; background-color: #ffe5e8; color: #d62839; padding: 3px 8px; border-radius: 6px; font-weight: 900; display: inline-block; width: fit-content; margin-top: 2px; }
.badge-added-items { font-size: 0.7rem; background-color: #d3f9d8; color: #2b8a3e; padding: 3px 8px; border-radius: 6px; font-weight: 900; display: inline-block; width: fit-content; margin-top: 2px; }
.exclusion-badge-item.added { background-color: #ebfbee; color: #2b8a3e; border: 1px solid #b2f2bb; }
.item-controls { display: flex; align-items: center; gap: 0.8rem; font-weight: 900; color: #333; background: #f4f4f4; padding: 4px; border-radius: 8px; width: fit-content; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #965314; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.btn-delete { margin-left: auto; background: none; border: none; color: #d62839; cursor: pointer; padding: 6px; }
.cart-total { border-top: 2px dashed #ccc; padding-top: 1.2rem; display: flex; justify-content: space-between; font-size: 1.3rem; color: #111; font-weight: 900; margin-top: 1rem; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.brown-menu-card-skeleton {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid #e0d8d0;
  display: flex;
  flex-direction: column;
}

.brown-menu-card-skeleton .skeleton-img {
  width: 100%;
  height: 160px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.brown-menu-card-skeleton .skeleton-body {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.skeleton-pill {
  height: 16px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-80 { width: 80px; }
.width-120 { width: 120px; }
.margin-top-4 { margin-top: 4px; }

/* ----------------------------------------------------
   4. DETALLE DE RECETA
---------------------------------------------------- */
.client-step,
.summary-step {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.selection-mode {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-intro {
  background: linear-gradient(135deg, #fff8f1 0%, #fff 100%);
  border: 1px solid #f0dfc8;
  border-radius: 16px;
  padding: 1rem 1.25rem;
  box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
}

.section-intro h3 {
  margin: 0 0 0.35rem;
  color: #2f2a2a;
  font-size: 1.2rem;
  font-weight: 900;
}

.section-intro p {
  margin: 0;
  color: #7e7575;
  font-size: 0.95rem;
}

.actions { display: flex; justify-content: space-between; margin-top: 2.5rem; width: 100%; border-top: 2px solid #eee; padding-top: 1.5rem; }
.btn { padding: 0.8rem 1.8rem; border-radius: 12px; font-weight: 900; cursor: pointer; border: none; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; transition: all 0.2s; }
.btn-primary { background: #965314; color: white; box-shadow: 0 4px 12px rgba(150, 83, 20, 0.2); }
.btn-primary:hover { background: #7a410f; }
.btn-secondary { background: #e0e0e0; color: #555; }
.btn-secondary:hover { background: #d0d0d0; }

.distributor-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  background: linear-gradient(135deg, #fffdfb 0%, #fcf7f1 100%);
  padding: 2rem;
  border-radius: 18px;
  border: 1px solid #efe1cf;
  box-shadow: 0 8px 22px rgba(0, 0, 0, 0.04);
}

.input-row { display: flex; gap: 2rem; flex-wrap: wrap; }
.input-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; min-width: 250px; }
.input-group label { font-weight: 900; font-size: 0.85rem; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }
.input-group .dc-input,
.input-group .dc-select {
  width: 100%;
  border: 1px solid #e5d6c0;
  border-radius: 999px;
  padding: 0.9rem 1rem;
  font-size: 0.95rem;
  font-weight: 700;
  color: #2f2a2a;
  background: #fff;
  outline: none;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.03);
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.input-group .dc-input:focus,
.input-group .dc-select:focus {
  border-color: #965314;
  box-shadow: 0 0 0 3px rgba(150, 83, 20, 0.15);
}
.input-group .dc-select {
  appearance: none;
  background-image: linear-gradient(45deg, transparent 50%, #965314 50%), linear-gradient(135deg, #965314 50%, transparent 50%);
  background-position: calc(100% - 18px) calc(50% - 2px), calc(100% - 12px) calc(50% - 2px);
  background-size: 6px 6px, 6px 6px;
  background-repeat: no-repeat;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.25rem;
}

.summary-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.summary-section h4 {
  margin: 0;
  color: #965314;
  font-size: 0.95rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.summary-card {
  background: #fff;
  border: 1px solid #efe1cf;
  border-radius: 16px;
  padding: 1.1rem 1.2rem;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
}

.summary-card p {
  margin: 0.35rem 0;
  color: #4c4646;
  line-height: 1.5;
}

.products-list-final {
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.final-item {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.7rem 0;
  border-bottom: 1px solid #f2ebdf;
}

.final-item:last-child {
  border-bottom: none;
}

.final-item-meta {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.final-item-name {
  font-weight: 800;
  color: #2f2a2a;
}

.final-exclusions-box {
  display: flex;
  flex-wrap: wrap;
  gap: 0.35rem;
}

.exclusion-badge-item {
  padding: 0.24rem 0.5rem;
  border-radius: 999px;
  background: #fdecec;
  color: #b94f4f;
  font-size: 0.72rem;
  font-weight: 800;
}

.final-item-price {
  font-weight: 900;
  color: #965314;
  white-space: nowrap;
}

.final-total {
  margin-top: 0.4rem;
  padding-top: 0.8rem;
  border-top: 1px solid #f2ebdf;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 900;
  color: #2f2a2a;
}

/* ----------------------------------------------------
   7. RESPONSIVIDAD: TABLETS Y LAPTOPS (Hasta 1200px)
---------------------------------------------------- */
@media (max-width: 1200px) {
  .product-layout {
    /* 2 Columnas: Catálogo+Receta a la izquierda, Carrito a la derecha */
    grid-template-columns: 1fr 340px; 
  }
  .catalog-section { order: 1; }
  .box-ingredient-card { order: 2; position: relative; top: 0; min-height: auto; }
  .cart-summary-admin { order: 3; grid-row: span 2; }
}

/* ----------------------------------------------------
   8. RESPONSIVIDAD: CELULARES (Hasta 768px)
---------------------------------------------------- */
@media (max-width: 768px) {
  .admin-quote-wizard { 
    box-sizing: border-box;
    width: 100%;
    max-width: 100vw;
    overflow-x: hidden; 
    
    padding: 1.5rem 1rem 2rem 1rem; 
    margin: 0; 
    border-radius: 0; 
  }
  
  .wizard-header h1 { font-size: 1.8rem; margin-bottom: 1.5rem; }
  
  .steps-indicator { gap: 0.5rem; justify-content: space-between; width: 100%; box-sizing: border-box; }
  .line { display: none; }
  .step { padding: 0.6rem 0.2rem; font-size: 0.8rem; flex: 1; text-align: center; white-space: nowrap; }
  
  .product-layout { 
    grid-template-columns: 1fr; 
    width: 100%;
    box-sizing: border-box;
  }
  
  .cart-summary-admin { position: relative; top: 0; max-height: none; margin-top: 1rem; box-sizing: border-box; }
  
  .catalog-header { flex-direction: column; align-items: stretch; gap: 10px; margin-bottom: 1rem; }
  .filters { width: 100%; justify-content: stretch; }
  .product-search { width: 100%; }

  /* Ajustamos la grilla para que sus padding no sumen ancho extra */
  .products-grid-admin { 
    grid-template-columns: 1fr; 
    max-height: none; 
    overflow-y: visible;
    box-sizing: border-box;
    width: 100%;
    padding: 0.5rem 0; /* Quitamos el padding-right original que empujaba la pantalla */
    gap: 1.5rem;
  }

  .input-row { flex-direction: column; gap: 1rem; width: 100%; }
  .summary-grid { grid-template-columns: 1fr; }
  .distributor-form { padding: 1.25rem; }
  
  .actions { flex-direction: column-reverse; gap: 1rem; margin-top: 1.5rem; }
  .btn { width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem; }
  .summary-grid { grid-template-columns: 1fr; }
}

/* Custom Scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #aaa; }

.ing-status.base {
  background-color: #e2e8f0;
  color: #475569;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.75rem;
}

.ingredient-disabled {
  opacity: 0.85;
}

/* ----------------------------------------------------
   PESTAÑAS Y CARRITO FLOTANTE EN MÓVILES (< 900px)
---------------------------------------------------- */
.mobile-tabs-bar {
  display: none;
}

.mobile-floating-cart-bar {
  display: none;
}

@media (max-width: 900px) {
  .product-step .actions {
    display: none !important;
  }

  .mobile-tabs-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    background: #f4efe9;
    padding: 6px;
    border-radius: 12px;
    margin-bottom: 12px;
  }

  .mobile-tab-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 6px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #6e6a75;
    font-weight: 800;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.2s ease;
  }

  .mobile-tab-btn.active {
    background: #965314;
    color: white;
    box-shadow: 0 2px 8px rgba(150, 83, 20, 0.25);
  }

  .product-layout {
    grid-template-columns: 1fr !important;
  }

  .product-layout.show-tab-catalog .box-ingredient-card,
  .product-layout.show-tab-catalog .cart-summary-admin {
    display: none !important;
  }

  .product-layout.show-tab-recipe .catalog-section,
  .product-layout.show-tab-recipe .cart-summary-admin {
    display: none !important;
  }

  .product-layout.show-tab-cart .catalog-section,
  .product-layout.show-tab-cart .box-ingredient-card {
    display: none !important;
  }

  .mobile-floating-cart-bar {
    position: fixed;
    bottom: 15px;
    left: 15px;
    right: 15px;
    background: #513119;
    color: white;
    padding: 10px 16px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
    z-index: 999;
    cursor: pointer;
    animation: slideUp 0.3s ease;
  }

  .cart-bar-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .badge-count {
    background: #e28743;
    color: white;
    font-weight: 900;
    font-size: 0.85rem;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .cart-bar-total {
    font-weight: 900;
    font-size: 1.1rem;
  }

  .btn-checkout-mobile {
    background: #e28743;
    color: white;
    border: none;
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 900;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
  }

  @keyframes slideUp {
    from { transform: translateY(50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
}
</style>