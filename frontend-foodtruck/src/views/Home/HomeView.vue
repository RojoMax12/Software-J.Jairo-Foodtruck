<template>
  <div class="home-page">
    <AdminPreviewBar />

    <CartModal 
      :isOpen="isCartOpen"
      :cart-items="cartItems"
      @close="isCartOpen = false" 
      @update-quantity="handleUpdateQuantity"
      @remove-item="handleRemoveItem"
      @checkout="goToQuotation"
    />

    <ProductDetailModal 
      :isOpen="isDetailOpen" 
      :product="selectedProduct" 
      :isStoreOpen="isStoreOpen"
      @close="closeDetails" 
      @add-to-cart="addToCart"
    />

    <LoginNoticeModal
      :isOpen="isNoticeOpen"
      @close="isNoticeOpen = false"
      @confirm="router.push('/login')"
    />

    <Carousel />

    <!-- BANNER DE HORARIO Y ESTADO DE ATENCIÓN PÚBLICO -->
    <div class="store-status-wrapper">
      <div class="store-status-bar" :class="isStoreOpen ? 'store-open' : 'store-closed'">
        <div class="store-status-content">
          <span class="store-status-dot"></span>
          <div class="store-status-text">
            <strong v-if="isStoreOpen">
              🟢 ¡Estamos atendiendo en vivo!
            </strong>
            <strong v-else-if="shiftWindow?.es_dia_cerrado">
              🔴 Foodtruck cerrado hoy (Día de descanso)
            </strong>
            <strong v-else>
              ⚪ Foodtruck cerrado en este momento
            </strong>
            <span class="store-hours-info">
              <template v-if="shiftWindow?.es_dia_cerrado">
                Hoy no se reciben pedidos. ¡Te esperamos en nuestro próximo día laboral!
              </template>
              <template v-else>
                🕒 Horario: {{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }} hrs
                <template v-if="!isStoreOpen"> · ¡Te esperamos hoy a las {{ shiftWindow?.hora_apertura || '19:00' }}!</template>
              </template>
            </span>
          </div>
        </div>
      </div>
    </div>
    
    <main class="content-container">
      <SearchBar 
        v-model="selectedCategory" 
        v-model:searchQuery="searchQueryText"
        :categories="categoriesList"
      />
      
      <div v-if="isLoadingProducts" class="products-grid">
        <div v-for="n in 8" :key="'prod-skel-' + n" class="product-card-skeleton">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-pill width-80"></div>
            <div class="skeleton-pill width-120"></div>
            <div class="skeleton-pill width-60"></div>
          </div>
        </div>
      </div>
      <div v-else class="products-grid">
        <template v-for="item in filteredIceCreams" :key="item.name">
          <OfferCard
            v-if="item.kind === 'offer'"
            :name="item.name"
            :image="item.image"
            :price="item.displayPrice || getCardPrice(item)"
            :display-hint="item.displayHint"
            @view-details="openDetails(item)"
          />

          <ProductCard
            v-else
            :name="item.name"
            :category="item.category"
            :categoryColor="item.color"
            :image="item.image"
            :price="item.displayPrice || getCardPrice(item)"
            :display-hint="item.displayHint"
            @view-details="openDetails(item)"
          />
        </template>
      </div>
    </main>

    <!-- Botón Flotante para Subir -->
    <Transition name="fade-scale">
      <button 
        v-if="showScrollTop && !isCartOpen && !isDetailOpen" 
        class="floating-scroll-top"
        @click="scrollToTop"
        title="Volver arriba"
        aria-label="Volver arriba"
      >
        <ChevronUp :size="24" :stroke-width="2.5" />
      </button>
    </Transition>

    <button 
      v-if="!isCartOpen && !isDetailOpen" 
      class="floating-cart" 
      @click="isCartOpen = true"
    >
      <ShoppingCart :size="28" color="black" :stroke-width="2" />
      
      <span v-if="totalCartQuantity > 0" class="cart-badge">
        {{ totalCartQuantity }}
      </span>
    </button>
    <Footer class="main-footer" />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import OfferCard from '@/components/OfferCard.vue'
import CartModal from '@/components/CartModal.vue'
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import LoginNoticeModal from '@/components/LoginNoticeModal.vue';
import { ShoppingCart, ChevronUp } from 'lucide-vue-next'
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue'
import Carousel from '@/components/Carousel.vue';
import AdminPreviewBar from '@/components/AdminPreviewBar.vue';
import { useNotification } from '@/composables/useNotification';
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService';

const { notify } = useNotification();

// Estados reactivos
const showScrollTop = ref(false);
const isLoadingProducts = ref(true);
const isRefreshingProducts = ref(false);
const isCartOpen = ref(false);
const isDetailOpen = ref(false);
const isNoticeOpen = ref(false);
const selectedProduct = ref<any>(null);
const cartItems = ref<any[]>([]);
const iceCreams = ref<any[]>([]);
const router = useRouter();
const categoriesList = ref<any[]>([]);
const selectedCategory = ref<string>('Todas');
const searchQueryText = ref<string>('');

const shiftWindow = ref<ShiftWindow | null>(null);
const isStoreOpen = computed(() => shiftWindow.value?.es_jornada_activa ?? false);

const loadShiftStatus = async () => {
  try {
    shiftWindow.value = await cashFlowService.fetchShiftWindowFromBackend();
  } catch (e) {
    console.warn('Error al consultar horario de atención en HomeView:', e);
  }
};

// Estados autenticación
const isLoggedIn = ref(false); 
const currentUser = ref<any>(null);

  

const totalCartQuantity = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

// Revisar el estado de autenticación
const checkAuthStatus = () => {
  const token = localStorage.getItem('token');
  const userParsed = localStorage.getItem('user');

  if (token){
    isLoggedIn.value = true;
    if (userParsed) {
      try {
        const userObj = JSON.parse(userParsed);

        currentUser.value = userObj.nombre || 'Cliente';
      } catch (error) {
        console.error("Error al parsear el usuario:", error);
        currentUser.value = 'Cliente';
      }
    } else {
      currentUser.value = 'Cliente';
    }
  } else {
    isLoggedIn.value = false;
    currentUser.value = null;
  }
};

watch(() => router.currentRoute.value.path, () => {
  checkAuthStatus();
});

// Función para cerrar sesión
const handleLogout = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  isLoggedIn.value = false;
  currentUser.value = null;
  notify('Has cerrado sesión exitosamente.', 'success');
};

// Computado para filtrar productos por categoría y búsqueda de texto
const filteredIceCreams = computed(() => {
  let results = iceCreams.value;

  const selected = selectedCategory.value?.trim().toLowerCase() || '';

  // Filtro 1: por categoría
  if (selected && selected !== 'todas') {
    results = results.filter((item) => {
      const category = String(item.category ?? '').toLowerCase();
      const name = String(item.name ?? '').toLowerCase();

      const keywords: Record<string, string[]> = {
        'papas & chorrillanas': ['completo', 'papas', 'chorrillana'],
        'vianesas': ['vianesa', 'vienesa'],
        'sánguches / bajones': ['sanguche', 'bajon', 'churrasco'],
        'promos/combos': ['promo', 'combo', 'pizza'],
        'masas': ['masa', 'pizza'],
        'bebestibles': ['bebida', 'bebestible']
      };

      const matches = keywords[selected] || [];
      return category.includes(selected) || name.includes(selected) || matches.some(keyword => category.includes(keyword) || name.includes(keyword));
    });
  }

  // Filtro 2: por texto de búsqueda
  if (searchQueryText.value.trim() !== '') {
    const searchLow = searchQueryText.value.toLowerCase();
    results = results.filter(item => 
      item.name.toLowerCase().includes(searchLow)
    );
  }

  return results;
});



// Abrir el modal de detalles
const openDetails = (iceCream: any) => {
  selectedProduct.value = iceCream;
  isDetailOpen.value = true;
};

// Cerrar el modal de detalles y resetear producto seleccionado
const closeDetails = () => {
  isDetailOpen.value = false;
  selectedProduct.value = null;
};


const getCardPrice = (product: any) => {
  if (!product.types?.length) return "Sin precio";

  const uniqueSizes = new Set(
    (product.sizes || []).map((size: any) => String(size || '').trim()).filter(Boolean)
  );
  const hasMultipleVariants = product.types.length > 1 || uniqueSizes.size > 1;

  let minPrice = Infinity;
  product.types.forEach((t: any) => {
    Object.values(t.prices || {}).forEach((p: any) => {
      const num = Number(p);
      if (!isNaN(num) && num > 0 && num < minPrice) {
        minPrice = num;
      }
    });
  });

  if (minPrice === Infinity) return "Sin precio";

  if (hasMultipleVariants) {
    return `Desde $${minPrice.toLocaleString("es-CL")}`;
  }

  return `$${minPrice.toLocaleString("es-CL")}`;
};

const normalizeGroupedProduct = (product: any) => {
  const uniqueSizes = Array.from(new Set(
    (product.sizes || []).map((size: any) => String(size || '').trim()).filter(Boolean)
  ));

  const variantCount = product.types?.length || 0;
  const hasMultipleSizes = uniqueSizes.length > 1;
  const hasMultipleVariants = variantCount > 1;

  let minPrice = Infinity;
  (product.types || []).forEach((type: any) => {
    Object.values(type.prices || {}).forEach((price: any) => {
      const numeric = Number(price);
      if (!isNaN(numeric) && numeric > 0 && numeric < minPrice) {
        minPrice = numeric;
      }
    });
  });

  const displayPrice = minPrice === Infinity ? 'Sin precio' : (
    hasMultipleSizes || hasMultipleVariants
      ? `Desde $${Number(minPrice).toLocaleString('es-CL')}`
      : `$${Number(minPrice).toLocaleString('es-CL')}`
  );

  let displayHint = '';
  if (hasMultipleVariants && !hasMultipleSizes) {
    displayHint = `${variantCount} variedades`;
  } else if (hasMultipleSizes && !hasMultipleVariants) {
    displayHint = `${uniqueSizes.length} tamaños`;
  } else if (hasMultipleVariants && hasMultipleSizes) {
    displayHint = `${variantCount} variedades · ${uniqueSizes.length} tamaños`;
  }

  return {
    ...product,
    kind: product.kind || 'catalog',
    hasMultipleSizes,
    hasMultipleVariants,
    variantCount,
    displayPrice,
    displayHint,
    minPrice: minPrice === Infinity ? null : minPrice
  };
};


// Agregar un producto al carrito
const addToCart = (purchaseItem: any) => {
  if (!isStoreOpen.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El Foodtruck se encuentra cerrado hoy por ser día de descanso. No es posible realizar pedidos.'
      : 'El Foodtruck se encuentra cerrado en este momento. No es posible agregar productos al pedido.';
    notify(msg, 'warning');
    return;
  }

  const baseProduct = iceCreams.value.find(p => p.name === purchaseItem.name);

  if (baseProduct && !purchaseItem.id) {
    // Le inyectamos el ID exacto dependiendo del tamaño que eligió el usuario
    if (purchaseItem.size === '10L') purchaseItem.id = baseProduct.id10l;
    else if (purchaseItem.size === '5L') purchaseItem.id = baseProduct.id5l;
    else if (purchaseItem.size === '2.5L') purchaseItem.id = baseProduct.id25l;
    else if (purchaseItem.size === '1L') purchaseItem.id = baseProduct.id1l;
  }

  // Buscamos un item por su ID único cuando hay exclusiones específicas.
  const existingItem = cartItems.value.find(
    item => item.id === purchaseItem.id
  );

  if (existingItem) {
    existingItem.quantity += purchaseItem.quantity;
  } else {
    cartItems.value.push(purchaseItem);
  }

  notify(`¡${purchaseItem.fullName || 'Producto'} añadido al carrito!`, 'success');
}

// Función para cambiar cantidades desde el carrito lateral
const handleUpdateQuantity = (payload: { id: number, size: string, change: number }) => {
  // Buscamos al item específico por su ID único de producto y su formato
  const targetItem = cartItems.value.find(
    item => item.id === payload.id && item.size === payload.size
  );
  
  if (targetItem) {
    targetItem.quantity += payload.change;
    // Si la cantidad llega a cero, lo sacamos del carrito
    if (targetItem.quantity <= 0) {
      handleRemoveItem(payload);
    }
  }
};

// Función para eliminar un producto del carrito
const handleRemoveItem = (payload: { id: number, size: string }) => {
  cartItems.value = cartItems.value.filter(
    item => !(item.id === payload.id && item.size === payload.size)
  );
  notify('Producto eliminado del carrito.', 'warning');
};

const goToQuotation = () => {
  if (!isStoreOpen.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El Foodtruck se encuentra cerrado hoy por ser día de descanso. No es posible realizar pedidos.'
      : `El Foodtruck se encuentra cerrado en este momento. Horario de atención: ${shiftWindow.value?.hora_apertura || '19:00'} a ${shiftWindow.value?.hora_cierre || '00:30'} hrs.`;
    notify(msg, 'warning');
    return;
  }

  if (cartItems.value.length === 0) {
    notify('Tu carrito está vacío.', 'warning');
    return;
  }
  
  // Cerramos el carrito y enviamos directo a la cotización sin preguntar
  isCartOpen.value = false;
  router.push('/cotizacion'); 
};

const handleGoToLogin = () => {
  isNoticeOpen.value = false;
  isCartOpen.value = false;
  router.push('/login');
};

// Función para cargar los productos desde la API
const fetchIceCreams = async () => {
  if (isRefreshingProducts.value) return;

  isRefreshingProducts.value = true;
  isLoadingProducts.value = true;

  try {
    const [productsRes, categoriesRes] = await Promise.all([
      productService.getPublicProducts(),
      categoryService.getPublicCategories()
    ]);

    const dbProducts = productsRes.data || [];
    const dbCategories = categoriesRes.data || [];

    // Filtrar estrictamente solo productos activos y disponibles
    const activeDbProducts = dbProducts.filter((p: any) => {
      const isActivo = p.activo !== false && p.activo !== 0 && p.active !== false;
      const isDisponible = p.disponible !== false && p.disponible !== 0 && p.inStock !== false;
      const isEstadoOk = p.estado !== 0;
      return isActivo && isDisponible && isEstadoOk;
    });

    categoriesList.value = dbCategories.map((c: any) => ({
      id: c.id_categoria,
      nombre_categoria: c.nombre_categoria
    }));

    const categoryColors: Record<string, string> = {
      'Vianesas': '#E28743',
      'Ass': '#C0392B',
      'Churrascos': '#D35400',
      'Lomitos': '#8E44AD',
      'Hamburguesas': '#27AE60',
      'Pizzas': '#F39C12',
      'Fajitas': '#16A085',
      'Sándwich de Pollo': '#2980B9',
      'Papas & Chorrillanas': '#F1C40F',
      'Empanadas & Sopaipillas': '#E67E22',
      'Bebidas frías': '#3498DB',
      'Bebidas calientes': '#E74C3C',
      'Bebestibles & Jugos': '#3498DB'
    };

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
      'Bebidas frías': 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=900',
      'Bebidas calientes': 'https://images.unsplash.com/photo-1497636577773-f1231844b336?w=900'
    };

    const groupableCategories = ['Vianesas', 'Ass', 'Churrascos', 'Lomitos', 'Bebidas frías', 'Bebidas calientes', 'Empanadas & Sopaipillas'];
    const groupedMap: Record<string, any> = {};

    const normalizeSizeName = (value: any) => String(value ?? '').trim();

    activeDbProducts.forEach((prod: any) => {
      const catName = prod.categoria?.nombre_categoria || 'Varios';
      const isGroupable = groupableCategories.includes(catName);
      const groupKey = isGroupable ? catName : prod.nombre;
      const prodImage = prod.imagen_url || prod.imagen || prod.image || categoryImages[catName] || '/src/assets/placeholder-food.webp';

      if (!groupedMap[groupKey]) {
        groupedMap[groupKey] = {
          id: prod.id_producto,
          name: isGroupable ? catName : prod.nombre,
          category: catName,
          color: categoryColors[catName] || '#E28743',
          image: prodImage,
          descripcion: prod.descripcion,
          tipo_armado: prod.tipo_armado,
          cantidad_incluida: prod.cantidad_incluida,
          precio_ingrediente_extra: prod.precio_ingrediente_extra,
          sizes: [],
          tamaños_obj: [],
          types: []
        };
      }

      const pricesMap: Record<string, number> = {};
      (prod.tamaños || []).forEach((t: any) => {
        const sizeName = normalizeSizeName(t.nombre);
        if (!sizeName) return;

        pricesMap[sizeName] = Number(t.pivot?.precio ?? t.precio ?? 0);

        const hasSameSize = groupedMap[groupKey].sizes.some((existing: string) => existing.toLowerCase() === sizeName.toLowerCase());
        if (!hasSameSize) {
          groupedMap[groupKey].sizes.push(sizeName);
        }

        const sizeIdentifier = String(t.id_tamaño ?? sizeName).toLowerCase();
        const hasSameTamaño = groupedMap[groupKey].tamaños_obj.some((existing: any) => {
          const existingId = String(existing.id_tamaño ?? existing.nombre ?? '').toLowerCase();
          return existingId === sizeIdentifier || normalizeSizeName(existing.nombre).toLowerCase() === sizeName.toLowerCase();
        });

        if (!hasSameTamaño) {
          groupedMap[groupKey].tamaños_obj.push(t);
        }
      });

      const normalizedPrices = Object.fromEntries(
        groupedMap[groupKey].sizes.map((size: string) => [size, 0])
      );

      Object.entries(pricesMap).forEach(([sizeName, value]) => {
        normalizedPrices[sizeName] = Number(value || 0);
      });

      groupedMap[groupKey].types.push({
        id: prod.id_producto,
        name: prod.nombre,
        desc: prod.descripcion,
        active: true,
        image: prodImage,
        prices: normalizedPrices,
        tamaños_obj: prod.tamaños || [],
        producto_ingrediente: prod.ingredientes || []
      });
    });

    iceCreams.value = Object.values(groupedMap)
      .filter((g: any) => g.types && g.types.length > 0)
      .map((group: any) => normalizeGroupedProduct(group));
  } catch (error) {
    console.error('Error al cargar productos desde la API:', error);
  } finally {
    isLoadingProducts.value = false;
    isRefreshingProducts.value = false;
  }
};

const handleScroll = () => {
  showScrollTop.value = window.scrollY > 280;
};

const scrollToTop = () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
};

let productsRefreshTimer: number | undefined;

const resetProductsRefresh = () => {
  if (productsRefreshTimer) {
    window.clearInterval(productsRefreshTimer);
  }

  productsRefreshTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible') {
      fetchIceCreams();
    }
  }, 60000);
};

watch(
  () => router.currentRoute.value.path,
  (currentPath) => {
    checkAuthStatus();

    if (currentPath === '/' || currentPath === '/home') {
      fetchIceCreams();
    }
  },
  { immediate: true }
);

onMounted(() => {
  loadShiftStatus();
  fetchIceCreams();
  resetProductsRefresh();
  checkAuthStatus();
  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('storage', fetchIceCreams);
  window.addEventListener('focus', fetchIceCreams);
  window.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
      fetchIceCreams();
    }
  });
  window.addEventListener('foodtruck-products-update', fetchIceCreams);

  // Recuperación segura del estado persistido del carrito temporal
  const savedCart = localStorage.getItem('dicreme_temp_cart');
  if (savedCart) {
    try {
      cartItems.value = JSON.parse(savedCart);
    } catch (error) {
      console.error('Error al cargar el carrito guardado:', error);
    }
  }
});

onUnmounted(() => {
  if (productsRefreshTimer) {
    window.clearInterval(productsRefreshTimer);
  }

  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('storage', fetchIceCreams);
  window.removeEventListener('focus', fetchIceCreams);
  window.removeEventListener('visibilitychange', fetchIceCreams);
  window.removeEventListener('foodtruck-products-update', fetchIceCreams);
});

// Guardado reactivo profundo en LocalStorage para no perder la persistencia de compra
watch(
  cartItems,
  (newCart) => {
    localStorage.setItem('dicreme_temp_cart', JSON.stringify(newCart));
  },
  { deep: true }
);
</script>

<style scoped>
.store-status-wrapper {
  max-width: 1200px;
  margin: 15px auto 0 auto;
  padding: 0 16px;
}

.store-status-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 18px;
  border-radius: 14px;
  transition: all 0.3s ease;
}

.store-open {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 1.5px solid #86efac;
}

.store-closed {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1.5px solid #cbd5e1;
}

.store-status-content {
  display: flex;
  align-items: center;
  gap: 12px;
}

.store-status-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  flex-shrink: 0;
}

.store-open .store-status-dot {
  background-color: #22c55e;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  animation: pulse-dot-home 1.6s infinite;
}

.store-closed .store-status-dot {
  background-color: #94a3b8;
}

@keyframes pulse-dot-home {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.store-status-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.store-open .store-status-text strong {
  color: #166534;
  font-size: 0.95rem;
}

.store-closed .store-status-text strong {
  color: #475569;
  font-size: 0.95rem;
}

.store-hours-info {
  font-size: 0.82rem;
  color: #64748b;
  font-weight: 600;
}

.content-container {
  flex: 1; 
  padding: 20px;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  justify-items: center;
  margin-top: 20px;
}

.floating-cart {
  position: fixed;
  bottom: 30px;
  left: 30px;
  padding: 15px;
  background-color: #E28743;
  color: rgb(0, 0, 0);
  width: 65px;
  height: 65px;
  border-radius: 50%;
  border: 12px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999; 
  transition: transform 0.2s ease;
}

.floating-cart:hover {
  transform: scale(1.08);
}

.floating-cart:active {
  transform: scale(0.92);
}

/* Botón Flotante Volver Arriba */
.floating-scroll-top {
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 52px;
  height: 52px;
  border-radius: 50%;
  background: var(--DC-brown, #513119);
  color: #ffffff;
  border: 2px solid rgba(255, 255, 255, 0.35);
  cursor: pointer;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 998;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.floating-scroll-top:hover {
  background-color: var(--DC-orange, #e28743);
  transform: translateY(-4px) scale(1.06);
  box-shadow: 0 8px 22px rgba(226, 135, 67, 0.45);
}

.floating-scroll-top:active {
  transform: scale(0.92);
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.7) translateY(10px);
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.product-card-skeleton {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
}

.skeleton-img {
  width: 100%;
  height: 180px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-body {
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

.width-60 { width: 60px; }
.width-80 { width: 80px; }
.width-120 { width: 120px; }

.home-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh; /* Ocupa el 100% de la pantalla del usuario */
  position: relative;
}

.main-footer {
  margin-top: auto; /* Garantía de empuje si la grilla de productos se vacía */
  width: 100%;
}

.cart-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background-color: #e11d48; /* Rojo llamativo */
  color: white;
  font-size: 0.85rem;
  font-weight: 900;
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  border: 2px solid #f5ebe0; /* Borde del color de tu fondo para resaltarlo */
}

@media (max-width: 600px) {
  .content-container {
    padding: 10px; /* Reducimos el margen para ganar espacio en los lados */
  }

  .products-grid {
    /* Permite tarjetas más compactas en pantallas pequeñas */
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); 
    gap: 12px; /* Juntamos un poco más los productos */
  }

  .floating-cart {
    bottom: 20px;
    right: 20px;   /* Lo posiciona a la derecha en móviles */
    left: auto;    /* 🔥 CRÍTICO: Cancela el "left: 30px" del diseño de escritorio */
    width: 55px;
    height: 55px;  /* Un tamaño ligeramente menor para no tapar tanto contenido */
    z-index: 999;  /* Por encima de tarjetas pero por debajo de modales (z-index: 2000+) */
  }

  .floating-scroll-top {
    bottom: 85px;  /* Ubicado justo arriba del carrito flotante en móviles */
    right: 20px;
    width: 44px;
    height: 44px;
    z-index: 998;
  }
}
</style>