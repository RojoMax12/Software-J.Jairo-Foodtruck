<template>
  <Transition name="fade">
    <div v-if="isOpen" class="modal-overlay" @click="emit('close')">
      <Transition name="slide">
        <div v-if="isOpen" class="modal-content" @click.stop>
          
          <!-- CABECERA DEL CARRITO -->
          <div class="cart-header">
            <button 
              type="button" 
              class="close-btn" 
              @click="emit('close')" 
              title="Cerrar carrito"
              aria-label="Cerrar carrito"
            >
              <X :size="20" />
            </button>
            
            <div class="header-info">
              <div class="cart-icon-bubble">
                <ShoppingCart :size="24" />
              </div>
              <div class="header-text-group">
                <h2 class="cart-title">Mi Pedido</h2>
                <span class="cart-subtitle">
                  {{ cartItems.length === 0 ? 'Sin productos' : `${totalCartItemsCount} ${totalCartItemsCount === 1 ? 'ítem seleccionado' : 'ítems seleccionados'}` }}
                </span>
              </div>
            </div>
          </div>

          <!-- BANNER DE LOCAL CERRADO EN CARRITO -->
          <div v-if="isStoreOpen === false && cartItems.length > 0" class="store-closed-banner">
            <Clock :size="16" class="closed-banner-icon" />
            <div class="closed-banner-text">
              <strong>Local cerrado en este momento</strong>
              <span>Revisa el horario en portada antes de cotizar.</span>
            </div>
          </div>

          <!-- CUERPO / LISTADO DE ÍTEMS -->
          <div class="cart-body">
            <div v-if="cartItems.length === 0" class="empty-state">
              <div class="empty-cart-icon">
                <ShoppingBag :size="38" />
              </div>
              <h3>Tu carrito está vacío</h3>
              <p>Explora la carta y agrega tus sándwiches o promociones favoritas.</p>
              <button type="button" class="btn-browse-menu" @click="emit('close')">
                Ver Menú
              </button>
            </div>
            
            <div v-else class="cart-items-list">
              <div 
                v-for="item in cartItems" 
                :key="getItemKey(item)" 
                class="cart-item"
              >
                <img :src="item.image || '/src/assets/placeholder-food.webp'" :alt="item.name" class="item-img" />
                
                <div class="item-details">
                  <div class="item-header-row">
                    <h4 class="item-name">{{ item.fullName || item.name }}</h4>
                    <button 
                      type="button" 
                      class="delete-btn" 
                      @click="emit('remove-item', { id: item.id, size: item.size })"
                      title="Eliminar producto"
                      aria-label="Eliminar producto"
                    >
                      <Trash2 :size="15" />
                    </button>
                  </div>
                  
                  <span v-if="item.size && item.size !== 'Normal' && item.size !== 'Único'" class="item-size-tag">
                    {{ item.size }}
                  </span>

                  <!-- MODIFICACIONES DE INGREDIENTES -->
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="customizations-row">
                    <span v-for="ing in item.excluidos" :key="ing" class="exclusion-badge">
                      Sin {{ ing }}
                    </span>
                  </div>

                  <div v-if="item.agregados && item.agregados.length > 0" class="customizations-row">
                    <span v-for="ing in item.agregados" :key="ing" class="addition-badge">
                      + {{ ing }}
                    </span>
                  </div>
                  
                  <div class="item-action-row">
                    <span class="item-price-info">${{ formatPrice(item.price) }}</span>
                    
                    <div class="item-quantity-selector">
                      <button 
                        type="button" 
                        class="qty-btn" 
                        @click="emit('update-quantity', { id: item.id, size: item.size, change: -1 })"
                        title="Disminuir cantidad"
                      >
                        <Minus :size="12" />
                      </button>
                      <span class="qty-value">{{ item.quantity }}</span>
                      <button 
                        type="button" 
                        class="qty-btn" 
                        @click="emit('update-quantity', { id: item.id, size: item.size, change: 1 })"
                        title="Aumentar cantidad"
                      >
                        <Plus :size="12" />
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <!-- FOOTER / TOTALES -->
          <div class="cart-footer">
            <template v-if="cartItems.length > 0">
              <div class="total-row">
                <span class="total-label">Total Estimado</span>
                <strong class="total-amount">{{ cartTotal }}</strong>
              </div>

              <button 
                type="button" 
                class="btn-checkout" 
                :class="{ 'btn-checkout-disabled': isStoreOpen === false }"
                :disabled="isStoreOpen === false"
                @click="emit('checkout')"
              >
                <span>{{ isStoreOpen === false ? 'Local Cerrado' : 'Finalizar Pedido' }}</span>
                <ArrowRight v-if="isStoreOpen !== false" :size="16" />
              </button>
            </template>

            <button 
              v-else 
              type="button" 
              class="btn-checkout-empty" 
              @click="emit('close')"
            >
              Seguir Explorando
            </button>
          </div>

        </div>
      </Transition>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { X, Trash2, Plus, Minus, ShoppingCart, ShoppingBag, Clock, ArrowRight } from 'lucide-vue-next';

const props = withDefaults(
  defineProps<{
    isOpen: boolean;
    cartItems: any[];
    isStoreOpen?: boolean;
  }>(),
  {
    isStoreOpen: true
  }
);

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'update-quantity', payload: { id: number; size: string; change: number }): void;
  (e: 'remove-item', payload: { id: number; size: string }): void;
  (e: 'checkout'): void;
}>();

const totalCartItemsCount = computed(() => {
  return props.cartItems.reduce((acc, item) => acc + (Number(item.quantity) || 1), 0);
});

const formatPrice = (price: number | string) => {
  const num = typeof price === 'string' ? Number(price.replace(/[^0-9]/g, '')) : Number(price || 0);
  return num.toLocaleString('es-CL');
};

const cartTotal = computed(() => {
  const totalRaw = props.cartItems.reduce((sum, item) => {
    const cleanPrice = typeof item.price === 'string' 
      ? Number(item.price.replace(/[^0-9]/g, '')) 
      : Number(item.price || 0);
      
    return sum + (cleanPrice * (Number(item.quantity) || 1));
  }, 0);

  return `$${totalRaw.toLocaleString('es-CL')}`;
});

const getItemKey = (item: any) => {
  const ex = Array.isArray(item.excluidos) ? item.excluidos.join('-') : '';
  const ag = Array.isArray(item.agregados) ? item.agregados.join('-') : '';
  return `${item.id}-${item.size || 'default'}-${ex}-${ag}`;
};
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(35, 20, 10, 0.52);
  backdrop-filter: blur(3px);
  z-index: 2000;
  display: flex;
}

.modal-content {
  width: 410px;
  max-width: 100vw;
  height: 100%;
  background-color: var(--DC-bg-gray, #f8f6f3); 
  display: flex;
  flex-direction: column;
  position: relative;
  box-shadow: 8px 0 32px rgba(26, 14, 5, 0.25);
  border-right: 1px solid rgba(81, 49, 25, 0.08);
}

/* HEADER DEL CARRITO */
.cart-header {
  background-color: var(--DC-brown, #513119);
  padding: 1.25rem 1.4rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: relative;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.header-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.cart-icon-bubble {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: rgba(226, 135, 67, 0.18);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.header-text-group {
  display: flex;
  flex-direction: column;
}

.cart-title {
  color: #ffffff;
  font-size: 1.25rem;
  font-weight: 900;
  margin: 0;
  line-height: 1.1;
}

.cart-subtitle {
  font-size: 0.76rem;
  color: rgba(255, 255, 255, 0.75);
  margin-top: 2px;
}

.close-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  color: #ffffff;
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: var(--DC-orange, #e28743);
  transform: rotate(90deg);
}

/* AVISO DE LOCAL CERRADO */
.store-closed-banner {
  background: #fff4e6;
  border-bottom: 1px solid #fed7aa;
  padding: 0.65rem 1rem;
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.closed-banner-icon {
  color: #c2410c;
  flex-shrink: 0;
}

.closed-banner-text {
  display: flex;
  flex-direction: column;
}

.closed-banner-text strong {
  font-size: 0.78rem;
  color: #9a3412;
}

.closed-banner-text span {
  font-size: 0.72rem;
  color: #c2410c;
}

/* CUERPO DEL CARRITO */
.cart-body {
  flex: 1;
  padding: 1rem 1.15rem;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: rgba(81, 49, 25, 0.2) transparent;
}

.cart-body::-webkit-scrollbar {
  width: 5px;
}

.cart-body::-webkit-scrollbar-thumb {
  background: rgba(81, 49, 25, 0.2);
  border-radius: 999px;
}

/* ESTADO VACÍO */
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  height: 70%;
  color: var(--DC-text-gray, #7c7468);
  padding: 1.5rem;
}

.empty-cart-icon {
  width: 68px;
  height: 68px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid rgba(81, 49, 25, 0.08);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
  margin-bottom: 0.85rem;
  box-shadow: 0 4px 12px rgba(26, 14, 5, 0.04);
}

.empty-state h3 {
  color: var(--DC-brown, #513119);
  font-size: 1.15rem;
  font-weight: 800;
  margin: 0 0 0.35rem 0;
}

.empty-state p {
  font-size: 0.84rem;
  line-height: 1.45;
  margin: 0 0 1.25rem 0;
  max-width: 240px;
}

.btn-browse-menu {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  padding: 0.65rem 1.25rem;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.84rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-browse-menu:hover {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
}

/* LISTADO DE ITEMS */
.cart-items-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.cart-item {
  display: flex;
  gap: 0.75rem;
  background-color: #ffffff; 
  padding: 0.75rem;
  border-radius: 14px;
  align-items: flex-start;
  border: 1px solid rgba(81, 49, 25, 0.08); 
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.03);
}

.item-img {
  width: 64px;
  height: 64px;
  object-fit: cover;
  border-radius: 10px;
  flex-shrink: 0;
  background-color: var(--DC-bg-gray, #f8f6f3);
}

.item-details {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.item-header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 6px;
}

.item-name {
  margin: 0;
  font-size: 0.88rem;
  color: var(--DC-gray, #2c2724);
  font-weight: 800; 
  line-height: 1.25;
  overflow-wrap: anywhere;
}

.delete-btn {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  padding: 2px;
  border-radius: 6px;
  display: grid;
  place-items: center;
  transition: all 0.15s ease;
  flex-shrink: 0;
}

.delete-btn:hover {
  color: var(--DC-pink, #d80056);
  background: #ffe4e6;
}

.item-size-tag {
  display: inline-block;
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  margin: 2px 0 4px 0;
}

.customizations-row {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin: 3px 0 6px 0;
}

.exclusion-badge {
  background-color: #fee2e2;
  color: #dc2626;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 4px;
}

.addition-badge {
  background-color: #dbeafe;
  color: #1d4ed8;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 4px;
}

.item-action-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
  padding-top: 4px;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
}

.item-price-info {
  font-size: 0.95rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743); 
}

.item-quantity-selector {
  display: flex;
  align-items: center;
  gap: 6px;
  background-color: var(--DC-bg-gray, #f8f6f3); 
  padding: 2px 6px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.qty-btn {
  background: transparent;
  border: none;
  cursor: pointer;
  display: grid;
  place-items: center;
  color: var(--DC-brown, #513119);
  padding: 2px;
  border-radius: 4px;
  transition: background-color 0.15s ease;
}

.qty-btn:hover {
  background-color: rgba(81, 49, 25, 0.1);
}

.qty-value {
  font-size: 0.82rem;
  font-weight: 800;
  min-width: 16px;
  text-align: center;
  color: var(--DC-gray, #2c2724);
}

/* FOOTER Y TOTALES */
.cart-footer {
  padding: 1.15rem 1.25rem;
  border-top: 1px solid rgba(81, 49, 25, 0.08); 
  background-color: #ffffff;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
}

.total-label {
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.total-amount {
  font-size: 1.45rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
  line-height: 1;
}

.btn-checkout {
  width: 100%;
  background-color: var(--DC-orange, #e28743); 
  color: #ffffff;
  border: none;
  padding: 0.85rem 1rem;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.95rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
  transition: all 0.2s ease;
}

.btn-checkout:hover:not(:disabled) {
  background-color: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(81, 49, 25, 0.25);
}

.btn-checkout-disabled,
.btn-checkout:disabled {
  background-color: #cbd5e1 !important;
  color: #64748b !important;
  cursor: not-allowed !important;
  box-shadow: none !important;
  transform: none !important;
}

.btn-checkout-empty {
  width: 100%;
  background-color: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  border: 1px solid rgba(81, 49, 25, 0.12);
  padding: 0.75rem 1rem;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-checkout-empty:hover {
  background-color: #ffffff;
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
}

/* ANIMACIONES */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.slide-enter-from,
.slide-leave-to {
  transform: translateX(-100%);
}

@media (max-width: 480px) {
  .modal-content {
    width: 100vw;
  }
  
  .cart-header {
    padding: 1rem;
  }

  .cart-body {
    padding: 0.85rem;
  }

  .cart-footer {
    padding: 1rem;
  }
}
</style>