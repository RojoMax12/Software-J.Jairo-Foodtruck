<template>
  <div class="home-page">
    <AdminPreviewBar />

    <!-- MODALES -->
    <CartModal 
      :isOpen="isCartOpen"
      :cart-items="cartItems"
      :isStoreOpen="isStoreOpen"
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

    <!-- CARRUSEL DE BANNERS -->
    <Carousel />

    <!-- BANNER DE HORARIO Y ESTADO (HOMOGÉNEO CON EL SISTEMA) -->
    <div class="store-status-wrapper">
      <div class="store-status-card" :class="isStoreOpen ? 'status-card-open' : 'status-card-closed'">
        <div class="status-indicator-col">
          <span class="status-pill" :class="isStoreOpen ? 'pill-open' : 'pill-closed'">
            <span class="dot-pulse" v-if="isStoreOpen"></span>
            {{ isStoreOpen ? 'Abierto Ahora' : (shiftWindow?.es_dia_cerrado ? 'Día de Descanso' : 'Local Cerrado') }}
          </span>
        </div>

        <div class="status-info-col">
          <strong class="status-main-title">
            {{ isStoreOpen ? '¡Estamos atendiendo pedidos en vivo!' : (shiftWindow?.es_dia_cerrado ? 'Hoy no se reciben pedidos' : 'Atención fuera de horario') }}
          </strong>
          <span class="status-schedule-text">
            <template v-if="shiftWindow?.es_dia_cerrado">
              El foodtruck se encuentra en descanso programado. ¡Te esperamos en nuestra próxima jornada!
            </template>
            <template v-else>
              Horario de atención: <b>{{ shiftWindow?.hora_apertura || '19:00' }} a {{ shiftWindow?.hora_cierre || '00:30' }} hrs</b>
              <template v-if="!isStoreOpen"> · Apertura programada a las {{ shiftWindow?.hora_apertura || '19:00' }} hrs.</template>
            </template>
          </span>
        </div>
      </div>
    </div>
    
    <!-- CONTENIDO PRINCIPAL: BUSCADOR, FILTROS Y PRODUCTOS -->
    <main class="content-container">
      <SearchBar 
        v-model="selectedCategory" 
        v-model:searchQuery="searchQueryText"
        :categories="categoriesList"
        :promotions-count="activePromotionsCount"
      />

      <!-- SKELETON LOADING -->
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

      <!-- GRILLA DE PRODUCTOS -->
      <div v-else class="products-grid">
        <template v-for="item in filteredProducts" :key="item.name">
          <OfferCard
            v-if="item.kind === 'offer'"
            :name="item.name"
            :image="item.image"
            :image-position="item.imagePosition"
            :image-zoom="item.imageZoom"
            :image-fit="item.imageFit"
            :price="item.displayPrice || getCardPrice(item)"
            :display-price="item.displayPrice"
            :original-price="item.originalPrice"
            :promo-badge-text="item.promocionTitulo"
            :discount-percent="item.discountPercent"
            :display-hint="item.displayHint"
            @view-details="openDetails(item)"
          />

          <ProductCard
            v-else
            :name="item.name"
            :category="item.category"
            :categoryColor="item.color"
            :image="item.image"
            :image-position="item.imagePosition"
            :image-zoom="item.imageZoom"
            :image-fit="item.imageFit"
            :price="item.displayPrice || getCardPrice(item)"
            :display-price="item.displayPrice"
            :original-price="item.originalPrice"
            :has-promotion="item.hasPromotion"
            :promo-badge-text="item.promocionTitulo"
            :discount-percent="item.discountPercent"
            :display-hint="item.displayHint"
            @view-details="openDetails(item)"
          />
        </template>
      </div>
    </main>

    <!-- BOTÓN VOLVER ARRIBA -->
    <Transition name="fade-scale">
      <button 
        v-if="showScrollTop && !isCartOpen && !isDetailOpen" 
        class="floating-scroll-top"
        @click="scrollToTop"
        title="Volver arriba"
        aria-label="Volver arriba"
      >
        <ChevronUp :size="22" :stroke-width="2.5" />
      </button>
    </Transition>

    <!-- BOTÓN FLOTANTE DEL CARRITO -->
    <button 
      v-if="!isCartOpen && !isDetailOpen" 
      class="floating-cart" 
      @click="isCartOpen = true"
      aria-label="Abrir carrito de compras"
    >
      <ShoppingCart :size="26" color="white" :stroke-width="2.2" />
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
import SearchBar from '@/components/SearchBar.vue';
import ProductCard from '@/components/ProductCard.vue';
import OfferCard from '@/components/OfferCard.vue';
import CartModal from '@/components/CartModal.vue';
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import LoginNoticeModal from '@/components/LoginNoticeModal.vue';
import { ShoppingCart, ChevronUp, ArrowRight, Sparkles } from 'lucide-vue-next';
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue';
import Carousel from '@/components/Carousel.vue';
import AdminPreviewBar from '@/components/AdminPreviewBar.vue';
import { useNotification } from '@/composables/useNotification';
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService';

const { notify } = useNotification();
const router = useRouter();

// Estados
const showScrollTop = ref(false);
const isLoadingProducts = ref(true);
const isRefreshingProducts = ref(false);
const isCartOpen = ref(false);
const isDetailOpen = ref(false);
const isNoticeOpen = ref(false);
const selectedProduct = ref<any>(null);
const cartItems = ref<any[]>([]);
const catalogProducts = ref<any[]>([]);
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

const totalCartQuantity = computed(() => {
  return cartItems.value.reduce((total, item) => total + item.quantity, 0);
});

const activePromotionsCount = computed(() => {
  return catalogProducts.value.filter(item => Boolean(item.hasPromotion || item.kind === 'offer' || item.promocion_activa)).length;
});

// Filtro por categoría y texto
const filteredProducts = computed(() => {
  let results = catalogProducts.value;
  const selected = selectedCategory.value?.trim().toLowerCase() || '';

  if (selected && selected !== 'todas') {
    results = results.filter((item) => {
      if (selected === 'promos/combos' || selected === 'promos' || selected === 'promociones') {
        const catLow = String(item.category ?? '').toLowerCase();
        const nameLow = String(item.name ?? '').toLowerCase();
        return Boolean(item.hasPromotion || item.kind === 'offer' || item.promocion_activa || catLow.includes('promo') || nameLow.includes('promo') || catLow.includes('combo') || nameLow.includes('combo'));
      }

      const category = String(item.category ?? '').toLowerCase();
      const name = String(item.name ?? '').toLowerCase();

      const keywords: Record<string, string[]> = {
        'papas & chorrillanas': ['completo', 'papas', 'chorrillana'],
        'vianesas': ['vianesa', 'vienesa'],
        'sánguches / bajones': ['sanguche', 'bajon', 'churrasco'],
        'masas': ['masa', 'pizza'],
        'bebestibles': ['bebida', 'bebestible']
      };

      const matches = keywords[selected] || [];
      return category.includes(selected) || name.includes(selected) || matches.some(k => category.includes(k) || name.includes(k));
    });
  }

  if (searchQueryText.value.trim() !== '') {
    const q = searchQueryText.value.toLowerCase();
    results = results.filter(item => item.name.toLowerCase().includes(q));
  }

  return results;
});

const openDetails = (product: any) => {
  selectedProduct.value = product;
  isDetailOpen.value = true;
};

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
  let minOriginalPrice = Infinity;
  let minPromoPrice = Infinity;
  let minPromoOriginalPrice = Infinity;
  let hasAnyPromotion = false;
  let promoTitle = '';
  let activePromoObj: any = null;

  (product.types || []).forEach((type: any) => {
    const isPromoType = !!(type.promocion_activa || type.hasPromotion);
    if (isPromoType) {
      hasAnyPromotion = true;
      if (!activePromoObj) activePromoObj = type.promocion_activa;
      if (!promoTitle && type.promocion_activa?.titulo) promoTitle = type.promocion_activa.titulo;
    }

    Object.entries(type.prices || {}).forEach(([sizeKey, price]: [string, any]) => {
      const numeric = Number(price);
      if (!isNaN(numeric) && numeric > 0) {
        if (numeric < minPrice) minPrice = numeric;
        if (isPromoType) {
          const orig = Number(type.originalPrices?.[sizeKey] ?? numeric);
          if (orig > numeric && numeric < minPromoPrice) {
            minPromoPrice = numeric;
            minPromoOriginalPrice = orig;
          }
        }
      }
    });

    Object.values(type.originalPrices || {}).forEach((price: any) => {
      const numeric = Number(price);
      if (!isNaN(numeric) && numeric > 0 && numeric < minOriginalPrice) {
        minOriginalPrice = numeric;
      }
    });
  });

  const hasPromoDiscount = minPromoOriginalPrice < Infinity && minPromoOriginalPrice > minPromoPrice;
  const hasGeneralDiscount = minOriginalPrice < Infinity && minOriginalPrice > minPrice;
  const hasDiscount = hasAnyPromotion && (hasPromoDiscount || hasGeneralDiscount);

  const effectiveMinPrice = hasPromoDiscount ? minPromoPrice : minPrice;
  const effectiveMinOriginalPrice = hasPromoDiscount ? minPromoOriginalPrice : minOriginalPrice;

  const displayPrice = effectiveMinPrice === Infinity ? 'Sin precio' : (
    hasMultipleSizes || hasMultipleVariants
      ? `Desde $${Number(effectiveMinPrice).toLocaleString('es-CL')}`
      : `$${Number(effectiveMinPrice).toLocaleString('es-CL')}`
  );

  const originalDisplayPrice = hasDiscount && effectiveMinOriginalPrice < Infinity && effectiveMinOriginalPrice > effectiveMinPrice
    ? (hasMultipleSizes || hasMultipleVariants
        ? `Desde $${Number(effectiveMinOriginalPrice).toLocaleString('es-CL')}`
        : `$${Number(effectiveMinOriginalPrice).toLocaleString('es-CL')}`)
    : undefined;

  const discountPercent = hasDiscount && effectiveMinOriginalPrice < Infinity && effectiveMinOriginalPrice > effectiveMinPrice
    ? Math.round((1 - effectiveMinPrice / effectiveMinOriginalPrice) * 100)
    : undefined;

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
    kind: hasAnyPromotion ? 'offer' : (product.kind || 'catalog'),
    hasPromotion: hasAnyPromotion,
    promocion_activa: activePromoObj,
    promocionTitulo: promoTitle || 'Promoción',
    originalPrice: originalDisplayPrice,
    discountPercent,
    hasMultipleSizes,
    hasMultipleVariants,
    variantCount,
    displayPrice,
    displayHint,
    minPrice: effectiveMinPrice === Infinity ? null : effectiveMinPrice
  };
};

// Agregar al carrito
const addToCart = (purchaseItem: any) => {
  if (!isStoreOpen.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El foodtruck se encuentra en día de descanso. No es posible realizar pedidos hoy.'
      : 'El foodtruck se encuentra cerrado en este momento. Revisa el horario de atención.';
    notify(msg, 'warning');
    return;
  }

  // Identificación única del ítem según ID y tamaño/variante
  const existingIndex = cartItems.value.findIndex(
    item => item.id === purchaseItem.id && item.size === purchaseItem.size
  );

  if (existingIndex >= 0) {
    cartItems.value[existingIndex].quantity += purchaseItem.quantity;
  } else {
    cartItems.value.push({ ...purchaseItem });
  }

  notify(`¡${purchaseItem.fullName || purchaseItem.name || 'Producto'} añadido al pedido!`, 'success');
};

const handleUpdateQuantity = (payload: { id: number, size: string, change: number }) => {
  const targetItem = cartItems.value.find(
    item => item.id === payload.id && item.size === payload.size
  );
  
  if (targetItem) {
    targetItem.quantity += payload.change;
    if (targetItem.quantity <= 0) {
      handleRemoveItem(payload);
    }
  }
};

const handleRemoveItem = (payload: { id: number, size: string }) => {
  cartItems.value = cartItems.value.filter(
    item => !(item.id === payload.id && item.size === payload.size)
  );
  notify('Producto eliminado del pedido.', 'warning');
};

const goToQuotation = () => {
  if (!isStoreOpen.value) {
    const msg = shiftWindow.value?.es_dia_cerrado
      ? 'El foodtruck se encuentra cerrado hoy por ser día de descanso.'
      : `El foodtruck está cerrado. Horario: ${shiftWindow.value?.hora_apertura || '19:00'} a ${shiftWindow.value?.hora_cierre || '00:30'} hrs.`;
    notify(msg, 'warning');
    return;
  }

  if (cartItems.value.length === 0) {
    notify('Tu pedido está vacío.', 'warning');
    return;
  }
  
  isCartOpen.value = false;
  router.push('/cotizacion'); 
};

// Carga de productos
const fetchCatalogProducts = async () => {
  if (isRefreshingProducts.value) return;

  isRefreshingProducts.value = true;
  if (catalogProducts.value.length === 0) {
    isLoadingProducts.value = true;
  }

  try {
    const [productsRes, categoriesRes] = await Promise.all([
      productService.getPublicProducts(),
      categoryService.getPublicCategories()
    ]);

    const dbProducts = productsRes.data || [];
    const dbCategories = categoriesRes.data || [];

    const activeDbProducts = dbProducts.filter((p: any) => {
      const isActivo = p.activo !== false && p.activo !== 0 && p.active !== false;
      const isDisponible = p.disponible !== false && p.disponible !== 0 && p.inStock !== false;
      return isActivo && isDisponible;
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

    const groupableCategories = ['Vianesas', 'Ass', 'Churrascos', 'Lomitos', 'Bebidas frías', 'Bebidas calientes', 'Empanadas & Sopaipillas'];
    const groupedMap: Record<string, any> = {};

    const normalizeSizeName = (value: any) => String(value ?? '').trim();

    activeDbProducts.forEach((prod: any) => {
      const catName = prod.categoria?.nombre_categoria || 'Varios';
      const isGroupable = groupableCategories.includes(catName);
      const groupKey = isGroupable ? catName : prod.nombre;
      const prodImage = prod.imagen_url || prod.imagen || prod.image || '/src/assets/placeholder-food.webp';
      const prodImagePosition = prod.imagen_posicion || prod.imagePosition || '50% 50%';
      const prodImageZoom = Number(prod.imagen_zoom || prod.imageZoom || 1);
      const prodImageFit = prod.imagen_ajuste === 'contain' || prod.imageFit === 'contain' ? 'contain' : 'cover';

      if (!groupedMap[groupKey]) {
        groupedMap[groupKey] = {
          id: prod.id_producto,
          name: isGroupable ? catName : prod.nombre,
          category: catName,
          color: categoryColors[catName] || '#E28743',
          image: prodImage,
          imagePosition: prodImagePosition,
          imageZoom: prodImageZoom,
          imageFit: prodImageFit,
          descripcion: prod.descripcion,
          tipo_armado: prod.tipo_armado,
          cantidad_incluida: prod.cantidad_incluida,
          precio_ingrediente_extra: prod.precio_ingrediente_extra,
          sizes: [],
          tamaños_obj: [],
          types: []
        };
      }

      const originalPricesMap: Record<string, number> = {};
      const pricesMap: Record<string, number> = {};

      if (prod.tamaños && prod.tamaños.length > 0) {
        prod.tamaños.forEach((t: any) => {
          const sizeName = normalizeSizeName(t.nombre);
          if (!sizeName) return;

          const baseVal = Number(t.pivot?.precio ?? t.precio ?? 0);
          originalPricesMap[sizeName] = baseVal;
          pricesMap[sizeName] = baseVal;

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
      } else {
        const defaultSizeName = 'Normal';
        const baseVal = Number(prod.precio ?? 0);
        originalPricesMap[defaultSizeName] = baseVal;
        pricesMap[defaultSizeName] = baseVal;
        if (!groupedMap[groupKey].sizes.includes(defaultSizeName)) {
          groupedMap[groupKey].sizes.push(defaultSizeName);
        }
      }

      const activePromo = prod.promocion_activa || prod.promocionActiva || null;
      let isPromoValid = false;
      if (activePromo && activePromo.activo !== false && activePromo.activo !== 0) {
        const now = new Date();
        const start = activePromo.fecha_inicio ? new Date(activePromo.fecha_inicio) : null;
        const end = activePromo.fecha_fin ? new Date(activePromo.fecha_fin) : null;
        const startOk = !start || isNaN(start.getTime()) || start <= now;
        const endOk = !end || isNaN(end.getTime()) || end >= now;
        isPromoValid = startOk && endOk;
      }

      const promotionPrice = isPromoValid ? Number(activePromo?.precio_promocional ?? 0) : 0;
      if (promotionPrice > 0) {
        Object.keys(pricesMap).forEach(sizeName => {
          const currentBase = Number(originalPricesMap[sizeName] ?? pricesMap[sizeName] ?? 0);
          if (currentBase > 0) {
            pricesMap[sizeName] = Math.min(promotionPrice, currentBase);
          } else {
            pricesMap[sizeName] = promotionPrice;
          }
        });
      }

      const normalizedPrices = Object.fromEntries(
        groupedMap[groupKey].sizes.map((size: string) => [size, 0])
      );
      const normalizedOriginalPrices = Object.fromEntries(
        groupedMap[groupKey].sizes.map((size: string) => [size, 0])
      );

      Object.entries(pricesMap).forEach(([sizeName, value]) => {
        normalizedPrices[sizeName] = Number(value || 0);
      });
      Object.entries(originalPricesMap).forEach(([sizeName, value]) => {
        normalizedOriginalPrices[sizeName] = Number(value || 0);
      });

      groupedMap[groupKey].types.push({
        id: prod.id_producto,
        name: prod.nombre,
        desc: prod.descripcion,
        active: true,
        image: prodImage,
        imagePosition: prodImagePosition,
        imageZoom: prodImageZoom,
        imageFit: prodImageFit,
        prices: normalizedPrices,
        originalPrices: normalizedOriginalPrices,
        promocion_activa: isPromoValid ? activePromo : null,
        hasPromotion: isPromoValid && Boolean(activePromo),
        tamaños_obj: prod.tamaños || [],
        producto_ingrediente: prod.ingredientes || []
      });
    });

    catalogProducts.value = Object.values(groupedMap)
      .filter((g: any) => g.types && g.types.length > 0)
      .map((group: any) => normalizeGroupedProduct(group));
  } catch (error) {
    console.error('Error al cargar catálogo de productos:', error);
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

onMounted(() => {
  loadShiftStatus();
  fetchCatalogProducts();

  window.addEventListener('scroll', handleScroll, { passive: true });
  window.addEventListener('storage', fetchCatalogProducts);
  window.addEventListener('focus', fetchCatalogProducts);
  window.addEventListener('foodtruck-products-update', fetchCatalogProducts);

  productsRefreshTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible') {
      fetchCatalogProducts();
    }
  }, 60000);

  const savedCart = localStorage.getItem('dicreme_temp_cart');
  if (savedCart) {
    try {
      cartItems.value = JSON.parse(savedCart);
    } catch (error) {
      console.error('Error recuperando carrito:', error);
    }
  }
});

onUnmounted(() => {
  if (productsRefreshTimer) window.clearInterval(productsRefreshTimer);
  window.removeEventListener('scroll', handleScroll);
  window.removeEventListener('storage', fetchCatalogProducts);
  window.removeEventListener('focus', fetchCatalogProducts);
  window.removeEventListener('foodtruck-products-update', fetchCatalogProducts);
});

watch(
  cartItems,
  (newCart) => {
    localStorage.setItem('dicreme_temp_cart', JSON.stringify(newCart));
  },
  { deep: true }
);
</script>

<style scoped>
.home-page {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  position: relative;
  background: var(--DC-bg-gray, #f8f6f3);
}

/* ====================================================
   BANNER DE ESTADO DE ATENCIÓN (HOMOGÉNEO)
==================================================== */
.store-status-wrapper {
  max-width: 1200px;
  width: 100%;
  margin: 1.25rem auto 0 auto;
  padding: 0 1.25rem;
  box-sizing: border-box;
}

.store-status-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
  padding: 1rem 1.4rem;
  display: flex;
  align-items: center;
  gap: 1.25rem;
  transition: all 0.3s ease;
}

.status-card-open {
  border-left: 5px solid #16a34a;
}

.status-card-closed {
  border-left: 5px solid #cbd5e1;
}

.status-indicator-col {
  flex-shrink: 0;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pill-open { background: #dcfce7; color: #15803d; }
.pill-closed { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.dot-pulse {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 rgba(22, 163, 74, 0.7);
  animation: pulse-dot 1.8s infinite;
}

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.status-info-col {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.status-main-title {
  color: var(--DC-gray, #2c2724);
  font-size: 0.95rem;
}

.status-schedule-text {
  font-size: 0.82rem;
  color: var(--DC-text-gray, #7c7468);
}

/* ====================================================
   CONTENEDOR DE PRODUCTOS
==================================================== */
.content-container {
  flex: 1; 
  padding: 1.25rem;
  max-width: 1200px;
  width: 100%;
  margin: 0 auto;
  box-sizing: border-box;
}

.promo-banner-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  background: #fffdfa;
  border: 1.5px solid rgba(226, 135, 67, 0.35);
  border-radius: 14px;
  padding: 0.85rem 1.25rem;
  margin-top: 1rem;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(226, 135, 67, 0.08);
  transition: all 0.2s ease;
}

.promo-banner-strip:hover {
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

.promo-banner-left {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.promo-icon-badge {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: rgba(226, 135, 67, 0.14);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.promo-strip-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.promo-strip-text strong {
  color: var(--DC-brown, #513119);
  font-size: 0.9rem;
}

.promo-strip-text span {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
}

.promo-strip-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: var(--DC-orange, #e28743);
  color: white;
  border: none;
  padding: 0.5rem 0.9rem;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 800;
  cursor: pointer;
  flex-shrink: 0;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.25rem;
  margin-top: 1.5rem;
}

/* ====================================================
   BOTONES FLOTANTES
==================================================== */
.floating-cart {
  position: fixed;
  bottom: 2rem;
  left: 2rem;
  background: var(--DC-orange, #e28743);
  color: white;
  width: 60px;
  height: 60px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  box-shadow: 0 6px 20px rgba(226, 135, 67, 0.4);
  display: grid;
  place-items: center;
  z-index: 999;
  transition: transform 0.2s ease;
}

.floating-cart:hover {
  transform: scale(1.08);
}

.cart-badge {
  position: absolute;
  top: -3px;
  right: -3px;
  background: var(--DC-pink, #d80056);
  color: white;
  font-size: 0.78rem;
  font-weight: 900;
  min-width: 22px;
  height: 22px;
  border-radius: 999px;
  display: grid;
  place-items: center;
  border: 2px solid #ffffff;
  padding: 0 4px;
}

.floating-scroll-top {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--DC-brown, #513119);
  color: white;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.25);
  display: grid;
  place-items: center;
  z-index: 998;
  transition: all 0.2s ease;
}

.floating-scroll-top:hover {
  background: var(--DC-orange, #e28743);
  transform: translateY(-2px);
}

/* ====================================================
   SKELETON & ANIMACIONES
==================================================== */
.product-card-skeleton {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.04);
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
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-pill {
  height: 14px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-60 { width: 60px; }
.width-80 { width: 80px; }
.width-120 { width: 120px; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.25s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.8);
}

.main-footer {
  margin-top: auto;
}

/* ====================================================
   RESPONSIVO
==================================================== */
@media (max-width: 600px) {
  .store-status-wrapper {
    padding: 0 0.85rem;
  }

  .store-status-card {
    flex-direction: column;
    align-items: flex-start;
    padding: 1rem;
    gap: 0.65rem;
  }

  .content-container {
    padding: 0.85rem;
  }

  .products-grid {
    grid-template-columns: repeat(auto-fill, minmax(145px, 1fr));
    gap: 0.75rem;
  }

  .floating-cart {
    bottom: 1.25rem;
    left: 1.25rem;
    width: 52px;
    height: 52px;
  }

  .floating-scroll-top {
    bottom: 1.25rem;
    right: 1.25rem;
    width: 44px;
    height: 44px;
  }
}
</style>