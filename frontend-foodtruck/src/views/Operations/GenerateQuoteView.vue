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

    <!-- Paso 2: Selección de Productos -->
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
              <div class="product-search">
                <Search :size="18" />
                <input v-model="productSearch" type="text" placeholder="Buscar producto..." />
              </div>
            </div>
          </div>

          <div class="products-grid-admin">
            <div v-for="p in filteredIceCreams" :key="p.name" class="product-card-admin">
              <img :src="p.image" :alt="p.name" />
              <div class="p-info">
                <h4>{{ p.name }}</h4>
                <span class="p-cat">{{ p.category }}</span>
                <div class="formats-list">
                  <div v-for="f in p.formats" :key="f.id" class="format-row">
                    <span>{{ f.size }} - {{ f.formattedPrice }}</span>
                    <button class="btn-add-small" @click="addToCart(p, f)">
                      <Plus :size="14" />
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="box-ingredient-card">
          <div class="text-product">
              
          </div>
        </div>

        <aside class="cart-summary-admin">
          <div class="cart-header">
            <ShoppingCart :size="20" />
            <span>Resumen Parcial</span>
          </div>
          <div class="cart-items-list">
            <div v-if="cartItems.length === 0" class="empty-cart">
              No hay productos
            </div>
            <div v-for="(item, idx) in cartItems" :key="item.id" class="cart-item-admin">
              <div class="item-main">
                <span class="item-name">{{ item.name }} ({{ item.size }})</span>
                <span class="item-price">{{ item.formattedPrice }}</span>
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
        <button class="btn btn-secondary" @click="router.push('/general-home')">
          Cancelar
        </button>
        <button class="btn btn-primary" @click="nextStep">
          Continuar a datos del cliente <ArrowRight :size="18" />
        </button>
        
      </div>
    </div>

        <!-- Paso 2: Datos del Cliente -->
    <div v-if="currentStep === 2" class="step-container client-step">
      <div class="selection-mode">
        <h3>Seleccione o ingrese los datos del distribuidor</h3>
        
        <div class="search-box">
          <Search class="search-icon" :size="20" />
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar distribuidor por nombre o RUT..." 
            class="dc-input"
            @input="showDropdown = true"
          />
          
          <div v-if="showDropdown && filteredDistributors.length > 0" class="search-dropdown">
            <div 
              v-for="d in filteredDistributors" 
              :key="d.id" 
              class="dropdown-item"
              @click="selectDistributor(d)"
            >
              <strong>{{ d.nombre_empresa }}</strong> ({{ d.rut_empresa }})
            </div>
          </div>
        </div>

        <div class="divider">
          <span>O ingresar datos manualmente</span>
        </div>

        <form class="distributor-form">
          <div class="input-row">
            <div class="input-group">
              <label>RUT Empresa</label>
              <input v-model="distributorForm.rut_empresa" type="text" placeholder="12.345.678-9" class="dc-input" />
            </div>
            <div class="input-group">
              <label>Nombre Empresa</label>
              <input v-model="distributorForm.nombre_empresa" type="text" class="dc-input" />
            </div>
          </div>

          <div class="input-row">
            <div class="input-group">
              <label>Correo Electrónico</label>
              <input v-model="distributorForm.correo_electronico" type="email" placeholder="cliente@correo.com" class="dc-input" />
            </div>
            <div class="input-group">
              <label>Teléfono</label>
              <input 
                v-model="distributorForm.telefono" 
                type="text" 
                placeholder="+569..." 
                class="dc-input" 
                @input="handlePhoneInput"
              />
            </div>
          </div>

          <div class="input-row">
            <div class="input-group">
              <label>Dirección</label>
              <input v-model="distributorForm.direccion" type="text" class="dc-input" />
            </div>
            <div class="input-group">
              <label>Comuna</label>
              <input v-model="distributorForm.comuna" type="text"  class="dc-input" />
            </div>
          </div>
          
          <div class="input-group">
            <label>Persona que recibe (Opcional)</label>
            <input v-model="distributorForm.persona_recibe" type="text" placeholder="Nombre Apellido" class="dc-input" />
          </div>
        </form>
      </div>

      <div class="actions">

        <button class="btn btn-secondary" @click="currentStep = 1">
          <ArrowLeft :size="18" /> Volver a Productos
        </button>
      </div>
    </div>

    <!-- Paso 3: Revisión y Envío -->
    <div v-if="currentStep === 3" class="step-container summary-step">
      <h3>Resumen Final de la Cotización</h3>
      
      <div class="summary-grid">
        <div class="summary-section">
          <h4>Datos del Distribuidor</h4>
          <div class="summary-card">
            <p><strong>Empresa:</strong> {{ distributorForm.nombre_empresa }}</p>
            <p><strong>RUT:</strong> {{ distributorForm.rut_empresa }}</p>
            <p><strong>Correo:</strong> {{ distributorForm.correo_electronico }}</p>
            <p><strong>Teléfono:</strong> {{ distributorForm.telefono }}</p>
            <p><strong>Dirección:</strong> {{ distributorForm.direccion }}, {{ distributorForm.comuna }}</p>
            <p><strong>Persona Recibe:</strong> {{ distributorForm.persona_recibe || 'N/A' }}</p>
          </div>
        </div>

        <div class="summary-section">
          <h4>Productos Seleccionados</h4>
          <div class="summary-card products-list-final">
            <div v-for="item in cartItems" :key="item.id" class="final-item">
              <span>{{ item.quantity }}x {{ item.name }} ({{ item.size }})</span>
              <span>{{ item.formattedPrice }} c/u</span>
            </div>
            <div class="final-total">
              <span>Total Estimado</span>
              <strong>{{ totalQuote }}</strong>
            </div>
          </div>
        </div>
      </div>

      <div class="actions">
        <button class="btn btn-secondary" @click="currentStep = 2">
          <ArrowLeft :size="18" /> Editar Productos
        </button>
        <button class="btn btn-primary" @click="confirmQuote" :disabled="isSubmitting">
          {{ isSubmitting ? 'Procesando...' : 'Confirmar y Enviar Cotización' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { Search, ArrowRight, ArrowLeft, ShoppingCart, Trash2, Plus, Minus } from 'lucide-vue-next';
import distributorService from '@/services/distributorService';
import productService from '@/services/productService';
import categoryService from '@/services/productCategoryService';
import quoteService from '@/services/quoteService';
import { authService } from '@/services/authService';
import fotoCaja from '@/assets/caja_dicreme.jpg';

const router = useRouter();
const currentStep = ref(1);
const isSubmitting = ref(false);


const distributors = ref<any[]>([]);
const searchQuery = ref('');
const showDropdown = ref(false);
const distributorForm = ref({
  id: null,
  nombre_empresa: '',
  rut_empresa: '',
  correo_electronico: '',
  telefono: '+56',
  direccion: '',
  comuna: '',
  persona_recibe: ''
});


const iceCreams = ref<any[]>([]);
const categoriesList = ref<any[]>([]);
const cartItems = ref<any[]>([]);
const productSearch = ref('');
const selectedCategory = ref('Todas');

const fetchDistributors = async () => {
  try {
    const response = await distributorService.getDistributors();
    distributors.value = response.data;
  } catch (error) {
    console.error('Error fetching distributors:', error);
  }
};

const fetchIceCreams = async () => {
  try {
    const [productsResponse, categoriesResponse] = await Promise.all([
      productService.getProducts(),
      categoryService.getCategory()
    ]);

    const dbProducts = productsResponse.data;
    const dbCategories = categoriesResponse.data;
    categoriesList.value = dbCategories;

    const categoryMap: Record<number, string> = {};
    dbCategories.forEach((cat: any) => {
      categoryMap[cat.id || cat.ID] = cat.nombre_categoria;
    });

    const grouped: Record<string, any> = {};
    dbProducts.forEach((prod: any) => {
      const flavorName = prod.nombre_producto;
      const catId = prod.id_categoria || prod.ID_categoria;
      const categoryName = categoryMap[catId] || 'Sin categoría';
      
      const uniqueKey = `${flavorName}_${catId}`;

      if (!grouped[uniqueKey]){
        grouped[uniqueKey] = {
          name: flavorName,
          category: categoryName,
          image: fotoCaja,
          formats: []
        };
      }
      grouped[uniqueKey].formats.push({
        id: prod.id || prod.ID,
        formatId: prod.id_formato || prod.ID_formato,
        size: prod.id_formato === 1 ? '10L' : prod.id_formato === 2 ? '5L' : prod.id_formato === 3 ? '2.5L' : '1L',
        price: prod.precio_producto,
        formattedPrice: `$${Number(prod.precio_producto).toLocaleString('es-CL')}`
      });
    });
    iceCreams.value = Object.values(grouped);
  } catch (error) {
    console.error('Error fetching products:', error);
  }
};

const filteredDistributors = computed(() => {
  if (!searchQuery.value) return [];
  const query = searchQuery.value.toLowerCase();
  return distributors.value.filter(d => 
    d.nombre_empresa.toLowerCase().includes(query) || 
    d.rut_empresa.toLowerCase().includes(query)
  ).slice(0, 5);
});

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

const selectDistributor = (d: any) => {
  let phone = d.telefono || '';
  if (phone && !phone.startsWith('+56')) {
    phone = '+56' + phone.replace(/^56/, '');
  } else if (!phone) {
    phone = '+56';
  }

  distributorForm.value = {
    id: d.id,
    nombre_empresa: d.nombre_empresa,
    rut_empresa: d.rut_empresa,
    correo_electronico: d.correo_electronico,
    telefono: phone,
    direccion: d.direccion,
    comuna: d.comuna,
    persona_recibe: d.nombre_empresa || ''
  };
  searchQuery.value = d.nombre_empresa;
  showDropdown.value = false;
};

const handlePhoneInput = () => {
  if (!distributorForm.value.telefono.startsWith('+56')) {
    distributorForm.value.telefono = '+56';
  }
};

const addToCart = (product: any, format: any) => {
  const existing = cartItems.value.find(item => item.id === format.id);
  if (existing) {
    existing.quantity++;
  } else {
    cartItems.value.push({
      id: format.id,
      name: product.name,
      size: format.size,
      price: format.price,
      formattedPrice: format.formattedPrice,
      category: product.category,
      quantity: 1
    });
  }
};

const removeFromCart = (index: number) => {
  cartItems.value.splice(index, 1);
};

const updateQuantity = (index: number, change: number) => {
  cartItems.value[index].quantity += change;
  if (cartItems.value[index].quantity <= 0) {
    removeFromCart(index);
  }
};

const totalQuote = computed(() => {
  const total = cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0);
  return `$${total.toLocaleString('es-CL')}`;
});

const nextStep = () => {
  if (currentStep.value === 1) {
    if (cartItems.value.length === 0) {
      alert('Debe añadir al menos un producto a la cotización.');
      return;
    }
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    if (!distributorForm.value.nombre_empresa || !distributorForm.value.rut_empresa) {
      alert('Por favor ingrese al menos el nombre y RUT de la empresa.');
      return;
      }
    }
    currentStep.value = 3;
};

const confirmQuote = async () => {
  if (isSubmitting.value) return;
  
  isSubmitting.value = true;
  try {
    let distributorId = distributorForm.value.id;

    if (!distributorId) {
      const cleanPhone = distributorForm.value.telefono.replace(/\+56/g, '').trim();
      const registerData = {
        rut_empresa: distributorForm.value.rut_empresa,
        nombre_empresa: distributorForm.value.nombre_empresa,
        correo_electronico: distributorForm.value.correo_electronico || `manual_${Date.now()}@dicreme.cl`,
        telefono: cleanPhone || '900000000', 
        comuna: distributorForm.value.comuna || 'Santiago',
        direccion: distributorForm.value.direccion || 'Dirección manual',
        contrasena: 'DiCreme2026', 
      };

      const response = await authService.registerDistribuidor(registerData);
      distributorId = response.id || response.ID || (response.data && (response.data.id || response.data.ID));
      
      if (!distributorId) throw new Error("No se pudo registrar al cliente automáticamente.");
    }

    const payload = {
      id_distribuidor: Number(distributorId),
      id_estado_cotizacion: 1,
      persona_recibe: distributorForm.value.persona_recibe || distributorForm.value.nombre_empresa,
      total_cotizacion: cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0),
      cotizacion_productos: cartItems.value.map(item => ({
        id_producto: item.id,
        cantidad: item.quantity,
        precio_unitario_venta: item.price
      }))
    };

    await quoteService.createQuote(payload);
    alert('Cotización generada exitosamente.');
    router.push('/admin/quotes');
  } catch (error: any) {
    console.error('Error en el flujo:', error);
    let msg = 'Error al procesar la solicitud.';
    if (error.response?.data?.errors) {
       msg = Object.values(error.response.data.errors).flat().join('\n');
    } else if (error.response?.data?.message) {
       msg = error.response.data.message;
    }
    alert(`Error: ${msg}`);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  fetchDistributors();
  fetchIceCreams();
});
</script>

<style scoped>
.admin-quote-wizard {
  max-width: 1100px;
  margin: 2rem auto;
  padding: 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  font-family: sans-serif;
}

.wizard-header {
  text-align: center;
  margin-bottom: 2rem;
}

.wizard-header h1 {
  color: #1a1624;
  margin-bottom: 1.5rem;
}

.steps-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
}

.step {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  background: #f0f0f0;
  color: #999;
  font-weight: bold;
}

.step.active {
  background: var(--DC-pink);
  color: white;
}

.line {
  height: 2px;
  width: 30px;
  background: #eee;
}

.step-container {
  padding: 1rem 0;
}

.search-box {
  position: relative;
  margin-bottom: 1.5rem;
}

.search-icon {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #999;
}

.search-box .dc-input {
  padding-left: 3rem;
}

.search-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  z-index: 10;
  margin-top: 5px;
}

.dropdown-item {
  padding: 0.75rem 1rem;
  cursor: pointer;
}

.dropdown-item:hover {
  background: #f8f8f8;
}

.divider {
  display: flex;
  align-items: center;
  text-align: center;
  margin: 2rem 0;
}

.divider::before, .divider::after {
  content: '';
  flex: 1;
  border-bottom: 1px solid #eee;
}

.divider span {
  padding: 0 1rem;
  color: #999;
  font-size: 0.9rem;
}

.distributor-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.input-row {
  display: flex;
  gap: 1.5rem;
}

.input-group {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.input-group label {
  font-weight: bold;
  font-size: 0.9rem;
  color: #555;
}

.dc-input, .dc-select {
  width: 100%;
  padding: 0.8rem 1.2rem;
  border: 1px solid #e4869f;
  border-radius: 12px;
  font-size: 1rem;
  outline: none;
  background: white;
}

.product-layout {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: 2rem;
  min-height: 500px;
}

.catalog-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.filters {
  display: flex;
  gap: 1rem;
}

.product-search {
  position: relative;
  display: flex;
  align-items: center;
}

.product-search input {
  padding: 0.5rem 1rem 0.5rem 2.5rem;
  border: 1px solid #ddd;
  border-radius: 8px;
}

.product-search svg {
  position: absolute;
  left: 0.8rem;
  color: #999;
}

.products-grid-admin {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1.5rem;
  max-height: 600px;
  overflow-y: auto;
  padding-right: 10px;
}

.product-card-admin {
  background: #fdfdfd;
  border: 1px solid #eee;
  border-radius: 12px;
  overflow: hidden;
}

.product-card-admin img {
  width: 100%;
  height: 120px;
  object-fit: cover;
}

.p-info {
  padding: 1rem;
}

.p-info h4 { margin: 0; font-size: 1rem; }
.p-cat { font-size: 0.8rem; color: var(--DC-pink); font-weight: bold; }

.formats-list {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.format-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
  background: #fff;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #f0f0f0;
}

.btn-add-small {
  background: var(--DC-pink);
  color: white;
  border: none;
  border-radius: 4px;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.cart-summary-admin {
  background: #f9f9f9;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.cart-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: #333;
}

.cart-items-list {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1rem;
}

.empty-cart {
  color: #999;
  text-align: center;
  margin-top: 2rem;
}

.cart-item-admin {
  background: white;
  padding: 0.8rem;
  border-radius: 8px;
  margin-bottom: 0.8rem;
  border: 1px solid #eee;
}

.item-main {
  display: flex;
  flex-direction: column;
  margin-bottom: 0.5rem;
}

.item-name { font-weight: bold; font-size: 0.9rem; }
.item-price { font-size: 0.85rem; color: #666; }

.item-controls {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.qty-btn {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #eee;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.btn-delete {
  margin-left: auto;
  background: none;
  border: none;
  color: #ff4d4f;
  cursor: pointer;
}

.cart-total {
  border-top: 2px solid #eee;
  padding-top: 1rem;
  display: flex;
  justify-content: space-between;
  font-size: 1.1rem;
}

.summary-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 2rem;
}

.summary-card {
  background: #f9f9f9;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #eee;
}

.box-ingredient-card {
  background: #f9f9f9;
  padding: 1.5rem;
  border-radius: 12px;
  border: 1px solid #eee;
}

.summary-card p { margin: 0.5rem 0; }

.products-list-final {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.final-item {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #eee;
  padding-bottom: 0.5rem;
  font-size: 0.95rem;
}

.final-total {
  margin-top: 1rem;
  display: flex;
  justify-content: space-between;
  font-size: 1.2rem;
  color: var(--DC-pink);
}

.actions {
  display: flex;
  justify-content: space-between;
  margin-top: 3rem;
}

.btn {
  padding: 0.8rem 2rem;
  border-radius: 12px;
  font-weight: bold;
  cursor: pointer;
  border: none;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s;
}

.btn-primary {
  background: var(--DC-pink);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  filter: brightness(0.9);
  transform: translateY(-2px);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #f0f0f0;
  color: #666;
}

.btn-secondary:hover {
  background: #e5e5e5;
}
</style>
