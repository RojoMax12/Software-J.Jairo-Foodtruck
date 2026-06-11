<script setup lang="ts">
import { ref, watch } from 'vue';
import { X, Plus, Minus } from 'lucide-vue-next';

const props = defineProps<{
  isOpen: boolean;
  product: any; 
}>();

// Definimos el evento para añadir al carrito
const emit = defineEmits(['close', 'add-to-cart']);

const activeImage = ref('');
const selectedSize = ref<'10L' | '5L' | '2.5L' | '1L'>('10L'); // Formato seleccionado por defecto
const quantity = ref(1); // Cantidad por defecto

// Resetear estados cuando se abre un producto diferente
watch(() => props.product, (newProduct) => {
  if (newProduct) {
    activeImage.value = newProduct.image10l || newProduct.image;
    selectedSize.value = '10L';
    quantity.value = 1;
  }
}, { immediate: true });

// Funciones para el contador de cantidad
const increaseQuantity = () => {
  quantity.value++;
};

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--;
  }
};

// Función para enviar el producto armado al carrito
const handleAddToCart = () => {
  // Determinamos el precio correcto según el formato seleccionado
  let chosenPrice = props.product.price10l;
  if (selectedSize.value === '5L') chosenPrice = props.product.price5l;
  if (selectedSize.value === '2.5L') chosenPrice = props.product.price25l;
  if (selectedSize.value === '1L') chosenPrice = props.product.price1l;

  emit('add-to-cart', {
    id: props.product.id,
    name: props.product.name,
    category: props.product.category,
    color: props.product.color,
    image: props.product.image,
    size: selectedSize.value,
    quantity: quantity.value,
    price: chosenPrice
  });

  emit('close'); // Cerramos el modal tras añadir
};
</script>

<template>
  <Transition name="pop">
    <div v-if="isOpen && product" class="modal-overlay" @click="$emit('close')">
      <div class="modal-wrapper" @click.stop>
        
        <button class="close-btn" @click="$emit('close')">
          <X :size="22" />
        </button>

        <div class="modal-grid">
          <div class="product-img-box">
            <img :src="activeImage" :alt="product.name" class="main-product-img" />
          </div>
          
          <div class="product-info-box">
            <span class="tag" :style="{ color: product.color }">- {{ product.category }}</span>
            <h2 class="product-title">{{ product.name }}</h2>
            
            <p class="description">
              Selecciona un formato e ingresa la cantidad deseada para agregar a tu cotización.
            </p>
            
            <div class="liters-section">
              <p class="section-subtitle">Formatos disponibles:</p>
              
              <div class="liters-buttons-container">
                <button 
                  class="liter-btn"
                  :class="{ active: selectedSize === '10L' }"
                  @mouseenter="activeImage = product.image10l || product.image"
                  @click="selectedSize = '10L'"
                >
                  <div class="btn-main-row">
                    <span class="btn-title">10L</span>
                    <span class="btn-price">{{ product.price10l }}</span>
                  </div>
                </button>

                <button 
                  class="liter-btn"
                  :class="{ active: selectedSize === '5L' }"
                  @mouseenter="activeImage = product.image5l || product.image"
                  @click="selectedSize = '5L'"
                >
                  <div class="btn-main-row">
                    <span class="btn-title">5L</span>
                    <span class="btn-price">{{ product.price5l }}</span>
                  </div>
                </button>

                <button 
                  class="liter-btn"
                  :class="{ active: selectedSize === '2.5L' }"
                  @mouseenter="activeImage = product.image25l || product.image"
                  @click="selectedSize = '2.5L'"
                >
                  <div class="btn-main-row">
                    <span class="btn-title">2.5L</span>
                    <span class="btn-price">{{ product.price25l }}</span>
                  </div>
                </button>

                <button 
                  class="liter-btn"
                  :class="{ active: selectedSize === '1L' }"
                  @mouseenter="activeImage = product.image1l || product.image"
                  @click="selectedSize = '1L'"
                >
                  <div class="btn-main-row">
                    <span class="btn-title">1L</span>
                    <span class="btn-price">{{ product.price1l }}</span>
                  </div>
                </button>
              </div>
            </div>

            <div class="purchase-actions">
              <div class="quantity-selector">
                <button class="quantity-btn" @click="decreaseQuantity">
                  <Minus :size="16" />
                </button>
                <span class="quantity-value">{{ quantity }}</span>
                <button class="quantity-btn" @click="increaseQuantity">
                  <Plus :size="16" />
                </button>
              </div>

              <button class="add-to-cart-btn" @click="handleAddToCart">
                Agregar al carrito
              </button>
            </div>

          </div>
        </div>

      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2500;
}

.modal-wrapper {
  background-color: white;
  width: 720px;
  max-width: 95%;
  border-radius: 20px;
  padding: 35px;
  position: relative;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: none;
  border: none;
  cursor: pointer;
  color: var(--DC-text-gray);
}

.modal-grid {
  display: grid;
  grid-template-columns: 1fr 1.2fr;
  gap: 30px;
  align-items: center;
}

.product-img-box {
  width: 100%;
  height: 320px;
  border-radius: 15px;
  overflow: hidden;
  border: 1px solid #eee;
}

.main-product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: all 0.3s ease;
}

.product-title {
  margin: 5px 0 10px 0;
  font-size: 1.6rem;
  color: var(--DC-gray);
  font-weight: 800;
}

.tag {
  font-size: 0.9rem;
  font-weight: bold;
}

.description {
  font-size: 0.85rem;
  color: var(--DC-text-gray);
  margin-bottom: 20px;
  line-height: 1.4;
}

.section-subtitle {
  font-size: 0.85rem;
  font-weight: bold;
  color: var(--DC-gray);
  margin-bottom: 8px;
}

.liters-buttons-container {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-bottom: 20px;
}

.liter-btn {
  width: 100%;
  background-color: white;
  border: 2px solid var(--DC-bg-gray);
  border-radius: 12px;
  padding: 10px 18px;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-main-row {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.btn-title { font-size: 1rem; font-weight: 800; color: var(--DC-gray); }
.btn-price { font-size: 1rem; font-weight: 700; color: var(--DC-text-gray); transition: color 0.2s; }
.btn-desc { font-size: 0.75rem; color: var(--DC-text-gray); margin-top: 2px; }

/* ESTADO ACTIVO (CUANDO SE SELECCIONA) */
.liter-btn.active, .liter-btn:hover {
  border-color: var(--DC-pink);
  background-color: rgba(228, 134, 159, 0.03);
}

.liter-btn.active .btn-price {
  color: var(--DC-pink); /* Destaca el precio del formato seleccionado */
}

/* --- NUEVOS ESTILOS CONTADOR Y ACCIÓN --- */
.purchase-actions {
  display: flex;
  gap: 15px;
  align-items: center;
  margin-top: 15px;
}

.quantity-selector {
  display: flex;
  align-items: center;
  border: 2px solid var(--DC-bg-gray);
  border-radius: 25px;
  padding: 4px;
  background-color: white;
}

.quantity-btn {
  background: none;
  border: none;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  color: var(--DC-gray);
  border-radius: 50%;
  transition: background-color 0.2s;
}

.quantity-btn:hover {
  background-color: var(--DC-bg-gray);
}

.quantity-value {
  font-size: 1rem;
  font-weight: 800;
  width: 30px;
  text-align: center;
  color: var(--DC-gray);
}

.add-to-cart-btn {
  flex: 1;
  background-color: var(--DC-pink);
  color: white;
  border: none;
  padding: 12px;
  border-radius: 25px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 10px rgba(228, 134, 159, 0.3);
  transition: transform 0.2s, filter 0.2s;
}

.add-to-cart-btn:hover {
  filter: brightness(1.05);
  transform: translateY(-2px);
}

.add-to-cart-btn:active {
  transform: translateY(0);
}

/* ANIMACIONES POP */
.pop-enter-active, .pop-leave-active { transition: all 0.3s ease; }
.pop-enter-from, .pop-leave-to { opacity: 0; }
.pop-enter-from .modal-wrapper, .pop-leave-to .modal-wrapper { transform: scale(0.95); }
</style>