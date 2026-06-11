<script setup lang="ts">
import { X, Trash2, Plus, Minus, ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';

// Recibimos la lista de productos agregados desde el Home
const props = defineProps<{
  isOpen: boolean;
  cartItems: any[]; 
}>();

// Definimos los eventos incluyendo 'checkout' para que el HomeView procese la cotización
const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'checkout']);

// LÓGICA: Calcula el valor total de los productos en el carrito dinámicamente
const cartTotal = computed(() => {
  const totalRaw = props.cartItems.reduce((sum, item) => {
    // Si el precio viene como String (ej: "$45.990"), limpiamos caracteres no numéricos
    const cleanPrice = typeof item.price === 'string' 
      ? Number(item.price.replace(/[^0-9]/g, '')) 
      : Number(item.price);
      
    return sum + (cleanPrice * item.quantity);
  }, 0);

  // Retorna el string formateado como moneda en Chile (ej: $91.980)
  return `$${totalRaw.toLocaleString('es-CL')}`;
});
</script>

<template>
  <Transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click="emit('close')">
      
      <Transition name="slide">
        <div v-if="isOpen" class="modal-content" @click.stop>
          
          <div class="cart-header">
            <button class="close-btn" @click="emit('close')">
              <X :size="24" color="white" />
            </button>
            
            <div class="header-info">
              <ShoppingCart :size="48" color="white" stroke-width="1.5" />
              <h2 class="cart-title">Mi Carrito</h2>
            </div>
          </div>

          <div class="cart-body">
            <div v-if="cartItems.length === 0" class="empty-state">
               <p>Tu carrito está listo para recibir helados</p>
            </div>
            
            <div v-else class="cart-items-list">
              <div v-for="item in cartItems" :key="item.id + '-' + item.size" class="cart-item">
                
                <img :src="item.image" :alt="item.name" class="item-img" />
                
                <div class="item-details">
                  <div class="item-header-row">
                    <h4 class="item-name">{{ item.name }}</h4>
                    <button class="delete-btn" @click="emit('remove-item', { id: item.id, size: item.size })">
                      <Trash2 :size="16" />
                    </button>
                  </div>
                  
                  <p class="item-category" :style="{ color: item.color }">- {{ item.category }}</p>
                  
                  <div class="item-action-row">
                    <span class="item-price-info">{{ item.size }} - {{ item.price }}</span>
                    
                    <div class="item-quantity-selector">
                      <button class="qty-btn" @click="emit('update-quantity', { id: item.id, size: item.size, change: -1 })">
                        <Minus :size="12" />
                      </button>
                      <span class="qty-value">{{ item.quantity }}</span>
                      <button class="qty-btn" @click="emit('update-quantity', { id: item.id, size: item.size, change: 1 })">
                        <Plus :size="12" />
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="cart-footer">
            <div v-if="cartItems.length > 0" class="total-row">
              <span class="total-label">Total estimado:</span>
              <span class="total-amount">{{ cartTotal }}</span>
            </div>

            <button class="btn-checkout" @click="emit('checkout')">
              Finalizar Cotización
            </button>
          </div>

        </div>
      </Transition>
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
  background-color: rgba(0, 0, 0, 0.4);
  z-index: 2000;
  display: flex;
}

.modal-content {
  width: 380px;
  height: 100%;
  background-color: white;
  display: flex;
  flex-direction: column;
  position: relative;
}

.cart-header {
  background-color: var(--DC-pink);
  padding: 15px 20px;
  border-bottom-left-radius: 25px;
  border-bottom-right-radius: 25px;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  cursor: pointer;
}

.header-info {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 10px;
}

.cart-title {
  color: white;
  font-size: 1.4rem;
  font-weight: 800;
  margin: 0;
}

.cart-body {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

.empty-state {
  text-align: center;
  margin-top: 50px;
  color: #999;
}

/* --- ESTILOS DE LA LISTA DE PRODUCTOS (MOCKUP) --- */
.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.cart-item {
  display: flex;
  gap: 12px;
  background-color: #f5f4f5;
  padding: 12px;
  border-radius: 15px;
  align-items: center;
}

.item-img {
  width: 70px;
  height: 70px;
  object-fit: cover;
  border-radius: 10px;
}

.item-details {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.item-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-name {
  margin: 0;
  font-size: 0.95rem;
  color: var(--DC-gray);
  font-weight: 700;
}

.delete-btn {
  background: none;
  border: none;
  color: #cc5a71;
  cursor: pointer;
  padding: 2px;
}

.item-category {
  font-size: 0.8rem;
  margin: 2px 0 8px 0;
  font-weight: bold;
}

.item-action-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.item-price-info {
  font-size: 0.9rem;
  font-weight: 800;
  color: var(--DC-gray);
}

.item-quantity-selector {
  display: flex;
  align-items: center;
  gap: 8px;
  background-color: white;
  padding: 2px 6px;
  border-radius: 12px;
  border: 1px solid #ddd;
}

.qty-btn {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  color: var(--DC-text-gray);
}

.qty-value {
  font-size: 0.85rem;
  font-weight: 800;
  min-width: 15px;
  text-align: center;
}

/* --- FOOTER Y SECCIÓN TOTAL --- */
.cart-footer {
  padding: 20px;
  border-top: 1px solid #eee;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding: 0 5px;
}

.total-label {
  font-size: 1rem;
  font-weight: 700;
  color: #555;
}

.total-amount {
  font-size: 1.3rem;
  font-weight: 800;
  color: var(--DC-pink);
}

.btn-checkout {
  width: 100%;
  background-color: var(--DC-gray);
  color: white;
  border: none;
  padding: 15px;
  border-radius: 12px;
  font-weight: bold;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-checkout:hover {
  background-color: var(--DC-gray);
}

/* ANIMACIONES */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>