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
              <ShoppingCart :size="42" color="var(--DC-orange)" stroke-width="1.5" />
              <h2 class="cart-title">Mi Carrito</h2>
            </div>
          </div>

          <div class="cart-body">
            <div v-if="cartItems.length === 0" class="empty-state">
               <p>No tienes productos en tu carrito</p>
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
                  
                  <p class="item-category" :style="{ color: item.color || 'var(--DC-pink)' }">- {{ item.category }}</p>
                  
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

            <button 
              v-if="cartItems.length > 0" 
              class="btn-checkout" 
              @click="emit('checkout')"
            >
              Finalizar Pedido
            </button>

            <button 
              v-else 
              class="btn-checkout" 
              @click="emit('close')"
            >
              Seguir comprando
            </button>
          </div>

        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { X, Trash2, Plus, Minus, ShoppingCart } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
  isOpen: boolean;
  cartItems: any[]; 
}>();

const emit = defineEmits(['close', 'update-quantity', 'remove-item', 'checkout']);

const cartTotal = computed(() => {
  const totalRaw = props.cartItems.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string' 
      ? Number(item.price.replace(/[^0-9]/g, '')) 
      : Number(item.price);
      
    return sum + (cleanPrice * item.quantity);
  }, 0);

  return `$${totalRaw.toLocaleString('es-CL')}`;
});
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.55); /* Un fondo oscuro un poco más denso */
  z-index: 2000;
  display: flex;
}

.modal-content {
  width: 380px;
  height: 100%;
  background-color: var(--DC-bg-gray); /* 🎨 Cambiado: Ahora usa tu fondo crema bajonero */
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 5px 0 25px rgba(0, 0, 0, 0.3);
}

.cart-header {
  background-color: var(--DC-brown); /* El café oscuro corporativo */
  padding: 20px;
  border-bottom-left-radius: 20px;
  border-bottom-right-radius: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
}

.close-btn {
  position: absolute;
  top: 18px;
  right: 18px;
  background: none;
  border: none;
  cursor: pointer;
  transition: transform 0.2s ease;
}

.close-btn:hover {
  transform: scale(1.15);
}

.header-info {
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 12px;
  margin-top: 5px;
}

.cart-title {
  color: var(--DC-orange);
  font-family: var(--font-main);
  font-size: 1.5rem;
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
  margin-top: 60px;
  color: var(--DC-text-gray);
  font-weight: 600;
}

/* --- ELEMENTOS DE LA LISTA DE PRODUCTOS --- */
.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.cart-item {
  display: flex;
  gap: 12px;
  background-color: #ffffff; /* 🎨 Las tarjetas blancas resaltan perfecto sobre el fondo crema */
  padding: 12px;
  border-radius: 15px;
  align-items: center;
  border: 1px solid rgba(90, 54, 20, 0.08); /* Mini borde café ultra sutil */
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
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
  font-weight: 800; /* Texto bien marcado */
}

.delete-btn {
  background: none;
  border: none;
  color: #cc5a71;
  cursor: pointer;
  padding: 2px;
  transition: color 0.2s;
}

.delete-btn:hover {
  color: #ff0033;
}

.item-category {
  font-size: 0.8rem;
  margin: 3px 0 8px 0;
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
  color: var(--DC-brown); /* Usamos el café para los precios */
}

.item-quantity-selector {
  display: flex;
  align-items: center;
  gap: 10px;
  background-color: var(--DC-bg-gray); /* Fondo crema para el selector */
  padding: 4px 8px;
  border-radius: 12px;
  border: 1px solid rgba(0,0,0,0.05);
}

.qty-btn {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  color: var(--DC-brown);
  font-weight: bold;
}

.qty-value {
  font-size: 0.85rem;
  font-weight: 800;
  min-width: 15px;
  text-align: center;
  color: var(--DC-gray);
}

/* --- FOOTER Y SECCIÓN TOTAL --- */
.cart-footer {
  padding: 20px;
  border-top: 2px dashed rgba(0, 0, 0, 0.15); /* Línea discontinua estilo boleta de comida */
  background-color: var(--font-main);
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
  color: var(--DC-text-gray);
}

.total-amount {
  font-size: 1.4rem;
  font-weight: 900;
  color: var(--DC-pink); /* Fucsia intenso para resaltar el valor total */
}

.btn-checkout {
  width: 100%;
  background-color: var(--DC-orange); /* 🎨 Corregido: Usa tu naranja de botón principal */
  color: var(--DC-brown);
  border: none;
  padding: 15px;
  border-radius: 12px;
  font-family: var(--font-main);
  font-weight: 800;
  font-size: 1.05rem;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(226, 135, 67, 0.3);
  transition: all 0.2s ease;
}

.btn-checkout:hover {
  background-color: #cf7332; /* Oscurecimiento controlado al pasar el mouse */
  transform: translateY(-1px);
}

.btn-checkout:active {
  transform: translateY(1px);
}

/* ANIMACIONES */
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-enter-active, .slide-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-enter-from, .slide-leave-to { transform: translateX(-100%); }
</style>