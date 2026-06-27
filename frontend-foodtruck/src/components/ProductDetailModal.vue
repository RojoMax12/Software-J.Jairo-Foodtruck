<template>
  <Transition name="pop">
    <div v-if="isOpen && product" class="modal-overlay" @click="$emit('close')">
      <div class="modal-wrapper" @click.stop>
        
        <button class="close-btn" @click="$emit('close')">
          <X :size="24" />
        </button>

        <div class="modal-grid">
          <div class="product-img-box">
            <img :src="product.image" :alt="product.name" class="main-product-img" />
          </div>
          
          <div class="product-info-box">
            <div class="header-info">
              <span class="tag">{{ product.category }}</span>
              <h2 class="product-title">{{ product.name }}</h2>
            </div>
            
            <div class="scrollable-config">
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
                    {{ size }}
                  </button>
                </div>
              </div>

              <div v-if="product.types && product.types.length > 0" class="config-section">
                <p class="section-subtitle">{{ product.sizes?.length > 1 ? '2.' : '1.' }} Variedad:</p>
                <div class="types-list">
                  <div 
                    v-for="tipo in product.types" 
                    :key="tipo.id"
                    class="type-row"
                    :class="{ 'active': selectedType?.id === tipo.id }"
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
                    <span class="t-price">${{ tipo.prices[selectedSize] }}</span>
                  </div>
                </div>
              </div>

              <div v-if="selectedType && selectedType.producto_ingrediente?.length > 0" class="config-section">
                <p class="section-subtitle">Ingredientes (Desmarca para quitar):</p>
                <div class="ingredients-list">
                  <label 
                    v-for="pi in selectedType.producto_ingrediente" 
                    :key="pi.id" 
                    class="ingredient-item"
                    :class="{ 'removed': excludedIngredients.includes(pi.ingrediente.nombre) }"
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
                    <span v-if="!pi.ingrediente.disponible" class="status-badge no-stock">Sin Stock</span>
                    <span v-else-if="excludedIngredients.includes(pi.ingrediente.nombre)" class="status-badge removed">Quitado</span>
                  </label>
                </div>
              </div>
            </div>

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

              <button class="add-to-cart-btn" @click="handleAddToCart">
                <span class="btn-text">AÑADIR</span>
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
const quantity = ref(1);

// Cuando se abre el modal o cambia el producto, reseteamos los valores
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    selectedSize.value = newProduct.sizes?.[0] || '';
    selectedType.value = newProduct.types?.[0] || null;
    excludedIngredients.value = [];
    quantity.value = 1;
  }
}, { immediate: true });

// Cambiar de variedad (ej: de Italiano a Luco)
const selectType = (tipo: any) => {
  selectedType.value = tipo;
  excludedIngredients.value = []; // Si cambia de variedad, reseteamos los ingredientes quitados
};

// Marcar/Desmarcar ingredientes
const toggleIngredient = (nombre: string) => {
  const index = excludedIngredients.value.indexOf(nombre);
  if (index > -1) {
    excludedIngredients.value.splice(index, 1); // Lo vuelve a agregar
  } else {
    excludedIngredients.value.push(nombre); // Lo quita
  }
};

// Controles de cantidad
const increaseQuantity = () => quantity.value++;
const decreaseQuantity = () => { if (quantity.value > 1) quantity.value--; };

// Cálculos de precio en tiempo real
const currentPrice = computed(() => {
  if (!selectedType.value || !selectedSize.value) return 0;
  return selectedType.value.prices[selectedSize.value] || 0;
});

const totalPriceFormatted = computed(() => {
  return (currentPrice.value * quantity.value).toLocaleString('es-CL');
});

// Enviar al carrito
const handleAddToCart = () => {
  if (!selectedType.value) return;

  // Creamos un ID único para el carrito basado en lo que excluyó para que no se mezclen
  const exclusionKey = [...excludedIngredients.value].sort().join('-');
  const cartItemId = `${selectedType.value.id}_${selectedSize.value}_${exclusionKey}`;

  emit('add-to-cart', {
    id: cartItemId,
    productId: selectedType.value.id,
    name: selectedType.value.name,
    fullName: `${props.product.name} ${selectedType.value.name}`, // Ej: Completo Italiano
    category: props.product.category,
    image: props.product.image,
    size: selectedSize.value,
    quantity: quantity.value,
    price: currentPrice.value,
    excluidos: [...excludedIngredients.value]
  });

  emit('close');
};
</script>

<style scoped>
/* ESTILOS BASE Y MODAL */
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background-color: rgba(30, 27, 36, 0.7); /* Gris oscuro de tu paleta con transparencia */
  display: flex; align-items: center; justify-content: center;
  z-index: 2500;
  backdrop-filter: blur(4px);
}

.modal-wrapper {
  background-color: #f5ebe0; /* Fondo cálido DiCreme/J.Junior */
  width: 850px;
  max-width: 95%;
  height: 85vh; /* Altura máxima para no salir de pantalla */
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
/* PANEL DERECHO (CONFIGURADOR) */
.product-info-box {
  padding: 30px;
  display: flex;
  flex-direction: column;
  height: 100%;
  background-color: white;
  overflow: hidden; /* 🌟 CLAVE 1: Evita que la caja crezca infinitamente */
}

/* SCROLL INTERNO PARA OPCIONES */


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
  min-height: 0; /* 🌟 CLAVE 2: Le avisa a Flexbox que aquí debe empezar el scroll */
}

/* Custom Scrollbar */
.scrollable-config::-webkit-scrollbar { width: 6px; }
.scrollable-config::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.scrollable-config::-webkit-scrollbar-thumb { background: #ccc; border-radius: 4px; }

.section-subtitle { font-size: 1rem; font-weight: 800; color: var(--DC-gray); margin-bottom: 12px; }

/* 1. TAMAÑOS (PILLS) */
.size-pills { display: flex; flex-wrap: wrap; gap: 10px; }
.size-pill {
  padding: 8px 16px; border-radius: 20px; font-weight: 800; font-size: 0.9rem;
  background: white; border: 2px solid #eeedee; color: var(--DC-text-gray);
  cursor: pointer; transition: all 0.2s;
}
.size-pill.active { background: var(--DC-orange); border-color: var(--DC-orange); color: white; }

/* 2. TIPOS (RADIOS) */
.types-list { display: flex; flex-direction: column; gap: 10px; }
.type-row {
  display: flex; justify-content: space-between; align-items: center;
  padding: 12px 15px; border-radius: 12px; border: 2px solid #eeedee;
  cursor: pointer; transition: all 0.2s; background: white;
}
.type-row:hover { border-color: #ccc; }
.type-row.active { border-color: var(--DC-orange); background-color: rgba(226, 135, 67, 0.05); }

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
.ingredient-item.removed { opacity: 0.6; background-color: #fff0f3; border-color: #ffc9c9; }
.ing-left { display: flex; align-items: center; gap: 12px; }
.custom-checkbox { width: 18px; height: 18px; accent-color: var(--DC-orange); cursor: pointer; }
.ingredient-item.removed .ing-name { text-decoration: line-through; color: var(--DC-text-gray); }
.ing-name { font-weight: 700; color: var(--DC-gray); font-size: 0.9rem; }

.status-badge { font-size: 0.7rem; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 6px; }
.status-badge.removed { background: #ffc9c9; color: #c92a2a; }
.status-badge.no-stock { background: #e9ecef; color: #868e96; }

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
.btn-text { font-weight: 900; font-size: 1rem; letter-spacing: 1px;}
.btn-total { font-weight: 900; font-size: 1.2rem; }

/* ANIMACIONES */
.pop-enter-active, .pop-leave-active { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.pop-enter-from, .pop-leave-to { opacity: 0; }
.pop-enter-from .modal-wrapper, .pop-leave-to .modal-wrapper { transform: scale(0.95) translateY(20px); }

/* 📱 RESPONSIVIDAD */
@media (max-width: 768px) {
  .modal-wrapper { 
    height: 95vh; 
    max-height: none; 
    border-radius: 15px; 
  }

  .modal-grid { 
    grid-template-columns: 1fr; 
    /* 🌟 CLAVE: Usamos 'auto' para la imagen para que solo ocupe lo que necesita,
       y '1fr' para que la info ocupe todo el resto del espacio disponible */
    grid-template-rows: auto 1fr; 
  }

  .product-img-box {
    height: 180px; /* 🌟 AJUSTE: Reducimos de 250px a 180px */
    width: 100%;
  }

  .main-product-img {
    height: 100%;
    object-fit: cover;
  }

  .product-info-box { 
    padding: 20px; 
    overflow-y: auto; /* 🌟 Esto permite que el contenido scrollée dentro de la caja */
  }

  /* Aseguramos que la sección scrollable no empuje los botones fuera de la pantalla */
  .scrollable-config {
    gap: 15px; /* Un poco más compacto en móvil */
  }

  .purchase-actions { 
    flex-direction: column; 
    margin-top: 10px;
  }
  .quantity-selector { width: 100%; padding: 8px; }
  .add-to-cart-btn { width: 100%; padding: 12px; }
}
</style>