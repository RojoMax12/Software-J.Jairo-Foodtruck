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
      <div class="product-layout">
        
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

          <div class="products-grid-admin">
            <div 
              v-for="p in filteredIceCreams" 
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
                {{ activeVariant.baseName }} {{ activeVariant.type.name }} ({{ activeVariant.size }})
              </h2>
              
              <div class="ingredient-list">
                <h4>Ingredientes incluidos (Desmarca para quitar)</h4>
                
                <div class="ingredients-wrapper">
                  <label 
                    v-for="pi in activeVariant.type.producto_ingrediente" 
                    :key="pi.id" 
                    class="ingredient-item-row"
                    :class="{ 'ingredient-removed': excludedIngredients.includes(pi.ingrediente.nombre) }"
                  >
                    <div class="ing-left">
                      <input 
                        type="checkbox" 
                        :checked="!excludedIngredients.includes(pi.ingrediente.nombre)"
                        @change="toggleIngredient(pi.ingrediente.nombre)"
                        :disabled="!pi.ingrediente.disponible"
                        class="custom-checkbox"
                      />
                      <span class="ing-name">{{ pi.ingrediente.nombre }}</span>
                    </div>

                    <span v-if="!pi.ingrediente.disponible" class="ing-status no-stock">Sin Stock</span>
                    <span v-else-if="excludedIngredients.includes(pi.ingrediente.nombre)" class="ing-status removed">QUITADO</span>
                    <span v-else class="ing-status ok">Lleva</span>
                  </label>
                </div>
              </div>

              <button class="btn-add-to-cart-large" @click="addActiveVariantToCart">
                AÑADIR A COMANDA - ${{ activeVariant.price }}
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
            <span>Comanda Actual</span>
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

      <div class="actions">
        <button class="btn btn-secondary" @click="router.push('/general-home')">Cancelar</button>
        <button class="btn btn-primary" @click="nextStep">
          Continuar a Cliente <ArrowRight :size="18" />
        </button>
      </div>
    </div>

    <div v-if="currentStep === 2" class="step-container client-step">
      <div class="selection-mode">
        <h3>Datos del cliente y Pago</h3>
        <form class="distributor-form">
          <div class="input-row">
            <div class="input-group">
              <label>Nombre y Apellido Cliente</label>
              <input v-model="distributorForm.nombre_empresa" type="text" placeholder="Ej: Johan Neira" class="dc-input" />
            </div>
          </div>
          <div class="input-row">
            <div class="input-group">
              <label>Teléfono (Opcional)</label>
              <input v-model="distributorForm.telefono" type="text" placeholder="+569..." class="dc-input" @input="handlePhoneInput"/>
            </div>
          </div>
          <div class="input-row">
            <div class="input-group">
              <label>Método de Pago</label>
              <select v-model="selectedPaymentMethod" class="dc-select">
                <option value="" disabled selected>Seleccione método de pago...</option>
                <option v-for="metodo in metodosdepago" :key="metodo.id" :value="metodo.nombre">
                  {{ metodo.nombre }}
                </option>
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
      <h3>Resumen Final del pedido</h3>
      <div class="summary-grid">
        <div class="summary-section">
          <h4>Datos del cliente</h4>
          <div class="summary-card">
            <p><strong>Cliente:</strong> {{ distributorForm.nombre_empresa }}</p>
            <p><strong>Teléfono:</strong> {{ distributorForm.telefono || 'No registrado' }}</p>
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
import { Search, ArrowRight, ArrowLeft, ShoppingCart, Trash2, Plus, Minus, FileText } from 'lucide-vue-next';
import quoteService from '@/services/quoteService';

const router = useRouter();
const currentStep = ref(1);
const isSubmitting = ref(false);

const activeVariant = ref<any>(null);
const excludedIngredients = ref<string[]>([]);
const selectedPaymentMethod = ref('');

const distributorForm = ref({ id: null, nombre_empresa: '', rut_empresa: '11.111.111-1', correo_electronico: '', telefono: '+56', direccion: '', comuna: '', persona_recibe: '' });

const iceCreams = ref<any[]>([]);
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

const fetchIceCreams = async () => {
  categoriesList.value = [
    { id: 1, nombre_categoria: 'Completos' },
    { id: 2, nombre_categoria: 'Ass' },
    { id: 3, nombre_categoria: 'Churrascos' }
  ];

  iceCreams.value = [
    {
      id: 1,
      name: "Completo",
      category: "Completos",
      image: "https://images.unsplash.com/photo-1627059318424-5890de49c3c7?q=80&w=400&auto=format&fit=crop",
      sizes: ["Chicos", "Grandes", "XXL"],
      activeSize: "Chicos",
      types: [
        {
          id: 101, name: "Italiano", desc: "Palta - Tomate - Mayo",
          prices: { "Chicos": 1400, "Grandes": 1850, "XXL": 2600 },
          producto_ingrediente: [
            { id: 1, ingrediente: { nombre: "Palta", disponible: true } },
            { id: 2, ingrediente: { nombre: "Tomate", disponible: true } },
            { id: 3, ingrediente: { nombre: "Mayo", disponible: true } }
          ]
        },
        {
          id: 102, name: "Completo", desc: "Chucrut - Americana - Tomate - Mayo",
          prices: { "Chicos": 1400, "Grandes": 1850, "XXL": 2600 },
          producto_ingrediente: [
            { id: 4, ingrediente: { nombre: "Chucrut", disponible: true } },
            { id: 5, ingrediente: { nombre: "Salsa Americana", disponible: true } },
            { id: 2, ingrediente: { nombre: "Tomate", disponible: true } },
            { id: 3, ingrediente: { nombre: "Mayo", disponible: true } }
          ]
        },
        {
          id: 103, name: "Dinámico", desc: "Chucrut - Americana - Tomate - Palta - Mayo",
          prices: { "Chicos": 1500, "Grandes": 1900, "XXL": 2600 },
          producto_ingrediente: [
            { id: 4, ingrediente: { nombre: "Chucrut", disponible: true } },
            { id: 5, ingrediente: { nombre: "Salsa Americana", disponible: true } },
            { id: 2, ingrediente: { nombre: "Tomate", disponible: true } },
            { id: 1, ingrediente: { nombre: "Palta", disponible: true } },
            { id: 3, ingrediente: { nombre: "Mayo", disponible: true } }
          ]
        }
      ]
    },
    {
      id: 2,
      name: "Ass",
      category: "Ass",
      image: "https://images.unsplash.com/photo-1553979459-d2229ba7433b?q=80&w=400&auto=format&fit=crop",
      sizes: ["Chicos", "Grandes"],
      activeSize: "Chicos",
      types: [
        {
          id: 201, name: "Italiano", desc: "Palta - Tomate - Mayo",
          prices: { "Chicos": 2800, "Grandes": 3200 },
          producto_ingrediente: [
            { id: 20, ingrediente: { nombre: "Churrasco", disponible: true } },
            { id: 1, ingrediente: { nombre: "Palta", disponible: true } },
            { id: 2, ingrediente: { nombre: "Tomate", disponible: true } },
            { id: 3, ingrediente: { nombre: "Mayo", disponible: true } }
          ]
        }
      ]
    },
    {
      id: 3,
      name: "Churrasco",
      category: "Churrascos",
      image: "https://images.unsplash.com/photo-1553979459-d2229ba7433b?q=80&w=400&auto=format&fit=crop",
      sizes: ["Normal"],
      activeSize: "Normal",
      types: [
        {
          id: 301, name: "Italiano", desc: "Churrasco - Palta - Tomate - Mayo",
          prices: { "Normal": 3700 },
          producto_ingrediente: [
            { id: 50, ingrediente: { nombre: "Churrasco", disponible: true } },
            { id: 1, ingrediente: { nombre: "Palta", disponible: true } },
            { id: 2, ingrediente: { nombre: "Tomate", disponible: true } },
            { id: 3, ingrediente: { nombre: "Mayo", disponible: true } }
          ]
        },
        {
          id: 303, name: "Barros Luco", desc: "Churrasco - Queso Fundido",
          prices: { "Normal": 3700 },
          producto_ingrediente: [
            { id: 50, ingrediente: { nombre: "Churrasco", disponible: true } },
            { id: 53, ingrediente: { nombre: "Queso Fundido", disponible: true } }
          ]
        }
      ]
    }
  ];
};

const filteredIceCreams = computed(() => {
  let results = iceCreams.value;
  if (selectedCategory.value !== 'Todas') {
    results = results.filter(item => item.category === selectedCategory.value);
  }
  if (productSearch.value.trim() !== '') {
    const s = productSearch.value.toLowerCase();
    results = results.filter(item => item.name.toLowerCase().includes(s));
  }
  return results;
});

// 🌟 Aquí guardamos el 'name' base (p.name) para usarlo en la columna central
const selectVariant = (baseProduct: any, type: any) => {
  activeVariant.value = {
    baseProduct: baseProduct,
    baseName: baseProduct.name, // Ej: "Completo" o "Churrasco"
    type: type,
    size: baseProduct.activeSize,
    price: type.prices[baseProduct.activeSize]
  };
  excludedIngredients.value = [];
};

const toggleIngredient = (nombreIngrediente: string) => {
  const index = excludedIngredients.value.indexOf(nombreIngrediente);
  if (index > -1) { excludedIngredients.value.splice(index, 1); } 
  else { excludedIngredients.value.push(nombreIngrediente); }
};

const addActiveVariantToCart = () => {
  if (!activeVariant.value) return;

  const exclusionKey = [...excludedIngredients.value].sort().join('-');
  const cartItemId = `${activeVariant.value.type.id}_${activeVariant.value.size}_${exclusionKey}`;

  // 🌟 Armamos el nombre completo (Ej: "Completo Italiano" o "Churrasco Luco")
  const fullProductName = `${activeVariant.value.baseName} ${activeVariant.value.type.name}`;

  const existing = cartItems.value.find(item => item.id === cartItemId);
  if (existing) {
    existing.quantity++;
  } else {
    cartItems.value.push({
      id: cartItemId,
      productId: activeVariant.value.type.id,
      name: activeVariant.value.type.name,
      fullName: fullProductName, // Guardamos el nombre combinado
      size: activeVariant.value.size,
      price: activeVariant.value.price,
      quantity: 1,
      excluidos: [...excludedIngredients.value] 
    });
  }
};

const removeFromCart = (index: number) => { cartItems.value.splice(index, 1); };
const updateQuantity = (index: number, change: number) => {
  cartItems.value[index].quantity += change;
  if (cartItems.value[index].quantity <= 0) removeFromCart(index);
};

const windowQuote = computed(() => cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));
const totalQuote = computed(() => `$${windowQuote.value.toLocaleString('es-CL')}`);

const nextStep = () => {
  if (currentStep.value === 1) {
    if (cartItems.value.length === 0) return alert('Debe añadir al menos un producto a la comanda.');
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    if (!distributorForm.value.nombre_empresa) return alert('Por favor, ingrese el Nombre del cliente.');
    if (!selectedPaymentMethod.value) return alert('Por favor, seleccione un Método de Pago.');
    currentStep.value = 3;
  }
};

const handlePhoneInput = () => { if (!distributorForm.value.telefono.startsWith('+56')) distributorForm.value.telefono = '+56'; };

const confirmQuote = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const payload = {
      id_distribuidor: 1,
      id_estado_cotizacion: 1,
      persona_recibe: distributorForm.value.nombre_empresa,
      total_cotizacion: windowQuote.value,
      metodo_pago: selectedPaymentMethod.value, 
      cotizacion_productos: cartItems.value.map(item => ({
        id_producto: item.productId, 
        cantidad: item.quantity, 
        precio_unitario_venta: item.price, 
        ingredientes_excluidos: item.excluidos 
      }))
    };
    await quoteService.createQuote(payload);
    alert('Comanda registrada y enviada a cocina.');
    currentStep.value = 1; cartItems.value = [];
    activeVariant.value = null;
  } catch (error) {
    console.error(error); alert('Error al procesar la comanda.');
  } finally { isSubmitting.value = false; }
};

onMounted(() => { cargarMetodosSimulados(); fetchIceCreams(); });
</script>

<style scoped>
/* ----------------------------------------------------
   1. CONTENEDOR PRINCIPAL
---------------------------------------------------- */
.admin-quote-wizard { 
  width: 95%; /* Ocupa casi todo el ancho pero con un pequeño margen lateral */
  max-width: 1500px; 
  margin: 20px auto; /* Centrado automático */
  padding: 20px; 
  background: white; 
  border-radius: 20px; 
  box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
}

.wizard-header { text-align: center; margin-bottom: 2.5rem; }
.wizard-header h1 { color: #1a1624; margin-bottom: 1rem; font-size: 2.2rem; font-weight: 900; }
.steps-indicator { display: flex; align-items: center; justify-content: center; gap: 1rem; flex-wrap: wrap; }
.step { padding: 0.5rem 1.2rem; border-radius: 20px; background: #f0f0f0; color: #888; font-weight: 800; font-size: 0.9rem; transition: all 0.3s; }
.step.active { background: #965314; color: white; box-shadow: 0 4px 10px rgba(150, 83, 20, 0.3); }
.line { height: 3px; width: 40px; background: #eee; border-radius: 2px; }

/* ----------------------------------------------------
   2. LAYOUT DE COLUMNAS (PC: 3 Columnas)
---------------------------------------------------- */
.product-layout { 
  display: grid; 
  /* Damos más espacio al catálogo (min 450px), y fijamos las barras laterales */
  grid-template-columns: minmax(450px, 1.8fr) 320px 320px; 
  gap: 1.5rem; 
  align-items: start; 
  width: 100%; 
}

/* ----------------------------------------------------
   3. CATÁLOGO Y TARJETAS (Marrón POS)
---------------------------------------------------- */
.catalog-section { background: #ffffff; border-radius: 16px; width: 100%; }
.catalog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.catalog-header h3 { font-size: 1.3rem; margin: 0; color: #333; font-weight: 900; }
.filters { display: flex; gap: 0.8rem; flex-wrap: wrap; flex: 1; justify-content: flex-end; }
.dc-select { padding: 0.7rem 1rem; border: 2px solid #965314; border-radius: 10px; font-size: 0.9rem; font-weight: 700; color: #333; background: white; cursor: pointer; }
.product-search { position: relative; display: flex; align-items: center; min-width: 200px; }
.product-search input { padding: 0.7rem 1rem 0.7rem 2.5rem; border: 2px solid #965314; border-radius: 10px; font-size: 0.9rem; width: 100%; font-weight: 600; }
.product-search svg { position: absolute; left: 0.8rem; color: #965314; }

.products-grid-admin { 
  display: grid; 
  /* Auto-fill asegura que las tarjetas no se aplasten, mínimo 220px */
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
  gap: 1.2rem; 
  max-height: 700px; 
  overflow-y: auto; 
  padding-right: 5px; 
}

/* Diseño de la Tarjeta */
.brown-menu-card { 
  background: #a05a2c; 
  border-radius: 16px; 
  overflow: hidden; 
  box-shadow: 0 6px 15px rgba(0,0,0,0.1); 
  display: flex; 
  flex-direction: column; 
  transition: transform 0.2s, box-shadow 0.2s;
  border: 2px solid transparent;
}
.brown-menu-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(160, 90, 44, 0.3); }
.brown-menu-card img { width: 100%; height: 130px; object-fit: cover; }
.brown-card-body { padding: 1.2rem; display: flex; flex-direction: column; color: white; flex: 1; }
.card-main-title { margin: 0; text-align: center; font-size: 1.2rem; font-weight: 900; text-shadow: 1px 1px 3px rgba(0,0,0,0.4); letter-spacing: 0.5px; }

/* Botones de Tamaño (Pills) */
.size-pills-container { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin: 15px 0; }
.size-pill { 
  background: #cba342; border: 2px solid #e1b958; color: #111; font-weight: 900; 
  border-radius: 20px; padding: 5px 14px; cursor: pointer; transition: all 0.2s; font-size: 0.85rem;
}
.size-pill:hover { background: #dfb755; }
.active-pill { background: #ffce44; border-color: #fff; transform: scale(1.08); box-shadow: 0 4px 10px rgba(0,0,0,0.3); }

/* Radios de Variedades */
.type-section-title { text-align: center; font-weight: 900; margin-bottom: 10px; font-size: 1.05rem; color: #f2c75c; text-transform: uppercase; }
.variants-list { display: flex; flex-direction: column; gap: 8px; }
.variant-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
.variant-row:hover { background: rgba(255,255,255,0.1); }
.active-row { background: rgba(255,255,255,0.15); border-left: 4px solid #ffce44; }

.variant-left { display: flex; align-items: center; gap: 10px; overflow: hidden; }
.radio-circle { width: 18px; height: 18px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.radio-inner { width: 10px; height: 10px; border-radius: 50%; background: #ffce44; }
.variant-texts { display: flex; flex-direction: column; overflow: hidden; }
.v-name { font-weight: 900; font-size: 1rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); }
.v-desc { font-size: 0.65rem; color: #eee; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.v-price { font-weight: 900; font-size: 1.05rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5); flex-shrink: 0; color: #ffce44; }

/* ----------------------------------------------------
   4. RECETA (COLUMNA CENTRAL)
---------------------------------------------------- */
.box-ingredient-card { background: #fdfdfd; padding: 1.5rem; border-radius: 16px; border: 2px solid #a05a2c; position: sticky; top: 20px; display: flex; flex-direction: column; min-height: 350px; }
.card-header { display: flex; align-items: center; gap: 0.5rem; font-weight: 900; color: #a05a2c; border-bottom: 2px dashed rgba(160, 90, 44, 0.2); padding-bottom: 1rem; margin-bottom: 1rem; text-transform: uppercase; }
.product-name-highlight { font-size: 1.4rem; color: #222; margin-bottom: 1rem; font-weight: 900; line-height: 1.2; text-transform: capitalize; }
.ingredient-list h4 { font-size: 0.8rem; color: #777; text-transform: uppercase; margin-bottom: 1rem; font-weight: 800; }
.ingredients-wrapper { display: flex; flex-direction: column; gap: 0.6rem; margin-bottom: 1.5rem; overflow-y: auto; max-height: 300px; padding-right: 5px; }
.ingredient-item-row { display: flex; justify-content: space-between; align-items: center; background: #f4f4f4; padding: 12px 14px; border-radius: 10px; font-size: 0.9rem; font-weight: 700; color: #333; cursor: pointer; border: 1px solid #eaeaea; transition: all 0.2s; }
.ingredient-item-row:hover { background: #f0edea; border-color: #a05a2c; }
.ing-left { display: flex; align-items: center; gap: 12px; }
.custom-checkbox { width: 18px; height: 18px; accent-color: #a05a2c; cursor: pointer; }
.ingredient-removed { opacity: 0.6; background-color: #fceceb; border-color: #f5c2c7; text-decoration: line-through; }
.ing-status { font-size: 0.75rem; font-weight: 900; text-transform: uppercase; padding: 2px 6px; border-radius: 4px; }
.ing-status.ok { background: #e0f8f5; color: #0f9d8a; }
.ing-status.removed { background: #ffe5e8; color: #d62839; }

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
.item-controls { display: flex; align-items: center; gap: 0.8rem; font-weight: 900; color: #333; background: #f4f4f4; padding: 4px; border-radius: 8px; width: fit-content; }
.qty-btn { width: 26px; height: 26px; border-radius: 6px; background: white; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #965314; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.btn-delete { margin-left: auto; background: none; border: none; color: #d62839; cursor: pointer; padding: 6px; }
.cart-total { border-top: 2px dashed #ccc; padding-top: 1.2rem; display: flex; justify-content: space-between; font-size: 1.3rem; color: #111; font-weight: 900; margin-top: 1rem; }

/* ----------------------------------------------------
   6. GLOBALES Y FORMS
---------------------------------------------------- */
.actions { display: flex; justify-content: space-between; margin-top: 2.5rem; width: 100%; border-top: 2px solid #eee; padding-top: 1.5rem; }
.btn { padding: 0.8rem 1.8rem; border-radius: 12px; font-weight: 900; cursor: pointer; border: none; display: flex; align-items: center; gap: 0.5rem; font-size: 1rem; transition: all 0.2s; }
.btn-primary { background: #965314; color: white; box-shadow: 0 4px 12px rgba(150, 83, 20, 0.2); }
.btn-primary:hover { background: #7a410f; }
.btn-secondary { background: #e0e0e0; color: #555; }
.btn-secondary:hover { background: #d0d0d0; }

.distributor-form { display: flex; flex-direction: column; gap: 1.5rem; background: #faf9f7; padding: 2.5rem; border-radius: 16px; border: 1px solid #eaeaea; }
.input-row { display: flex; gap: 2rem; flex-wrap: wrap; }
.input-group { flex: 1; display: flex; flex-direction: column; gap: 0.5rem; min-width: 250px; }
.input-group label { font-weight: 900; font-size: 0.85rem; color: #555; text-transform: uppercase; letter-spacing: 0.5px; }

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
  
  .actions { flex-direction: column-reverse; gap: 1rem; margin-top: 1.5rem; }
  .btn { width: 100%; justify-content: center; padding: 1rem; font-size: 1.1rem; }
  .summary-grid { grid-template-columns: 1fr; }
}

/* Custom Scrollbar */
::-webkit-scrollbar { width: 8px; }
::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: #aaa; }
</style>