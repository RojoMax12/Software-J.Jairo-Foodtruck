<template>
  <div class="home-page">
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
      @close="isDetailOpen = false" 
      @add-to-cart="addToCart"
    />

    <LoginNoticeModal
      :isOpen="isNoticeOpen"
      @close="isNoticeOpen = false"
      @confirm="router.push('/login')"
    />

    <Carousel :images="bannerImages" :autoPlayInterval="5000"/>
    
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
        <ProductCard 
          v-for="item in filteredIceCreams" 
          :key="item.name"
          :name="item.name"
          :category="item.category"
          :categoryColor="item.color"
          :image="item.image"
          :price="getCardPrice(item)"
          @view-details="openDetails(item)"
        />
      </div>
    </main>

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
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import CartModal from '@/components/CartModal.vue'
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import LoginNoticeModal from '@/components/LoginNoticeModal.vue';
import { ShoppingCart } from 'lucide-vue-next'
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue'
import Carousel from '@/components/Carousel.vue';
import imgBanner1 from '@/assets/banner1.webp'
import imgBanner2 from '@/assets/banner2.webp'
import imgBanner3 from '@/assets/banner3.webp'
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();




// Estados reactivos
const isLoadingProducts = ref(true);
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


const bannerImages = [
  imgBanner1,
  imgBanner2,
  imgBanner3
];

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
        console.log("Contenido real de lo que hay en 'user':", userObj);
        currentUser.value = userObj.nombre_empresa || 'Distribuidor';
      } catch (error) {
        console.error("Error al parsear el usuario:", error);
        currentUser.value = 'Distribuidor';
      }
    } else {
      currentUser.value = 'Distribuidor';
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


const getCardPrice = (product: any) => {
  if (!product.types?.length) return "Sin precio";

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

  if (product.sizes?.length > 1) {
    return `Desde $${minPrice.toLocaleString("es-CL")}`;
  }

  return `$${minPrice.toLocaleString("es-CL")}`;
};

/*
const getCardPrice = (product: any) => {
  if (!product || !product.types || product.types.length === 0) {
    return 'Sin precio';
  }

  const priceMap = product.types[0].prices || {};
  const defaultSize = Object.keys(priceMap)[0];
  const activeSize = product.activeSize || defaultSize;
  const selectedSize = activeSize ?? defaultSize;

  if (!selectedSize) {
    return 'Sin precio';
  }

  const priceValue = priceMap[selectedSize];

  if (priceValue == null) {
    return 'Sin precio';
  }

  return typeof priceValue === 'number'
    ? `$${priceValue.toLocaleString('es-CL')}`
    : String(priceValue);
};
*/

// Agregar un producto al carrito
const addToCart = (purchaseItem: any) => {
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
  isLoadingProducts.value = true;
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      productService.getPublicProducts(),
      categoryService.getPublicCategories()
    ]);

    const dbProducts = productsRes.data || [];
    const dbCategories = categoriesRes.data || [];

    // Filtrar solo productos activos para el menú público del cliente
    const activeDbProducts = dbProducts.filter((p: any) => {
      if (p.active === false || p.activo === false || p.estado === 0 || p.activo === 0) return false;
      return true;
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
      'Bebestibles & Jugos': 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=900'
    };

    const groupableCategories = ['Vianesas', 'Ass', 'Churrascos', 'Lomitos'];
    const groupedMap: Record<string, any> = {};

    dbProducts.forEach((prod: any) => {
      const catName = prod.categoria?.nombre_categoria || 'Varios';
      const isGroupable = groupableCategories.includes(catName);
      const groupKey = isGroupable ? catName : prod.nombre;

      if (!groupedMap[groupKey]) {
        groupedMap[groupKey] = {
          id: prod.id_producto,
          name: isGroupable ? catName : prod.nombre,
          category: catName,
          color: categoryColors[catName] || '#E28743',
          image: categoryImages[catName] || 'https://images.unsplash.com/photo-1567620812782-f461bc805b46?w=900',
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
        pricesMap[t.nombre] = Number(t.pivot?.precio || 0);
      });

      const isProdActive = prod.active !== false && prod.activo !== false && prod.estado !== 0 && prod.activo !== 0;

      groupedMap[groupKey].types.push({
        id: prod.id_producto,
        name: prod.nombre,
        desc: prod.descripcion,
        active: isProdActive,
        prices: pricesMap,
        tamaños_obj: prod.tamaños || [],
        producto_ingrediente: prod.ingredientes || []
      });

      (prod.tamaños || []).forEach((t: any) => {
        if (!groupedMap[groupKey].sizes.includes(t.nombre)) {
          groupedMap[groupKey].sizes.push(t.nombre);
        }
        if (!groupedMap[groupKey].tamaños_obj.some((existing: any) => existing.id_tamaño === t.id_tamaño)) {
          groupedMap[groupKey].tamaños_obj.push(t);
        }
      });
    });

    iceCreams.value = Object.values(groupedMap);
  } catch (error) {
    console.error('Error al cargar productos desde la API:', error);
  } finally {
    isLoadingProducts.value = false;
  }
};

onMounted(() => {
  fetchIceCreams();
  checkAuthStatus();

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
  transform: scale(1.1);
}

.floating-cart:active {
  transform: scale(0.9);
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
    z-index: 9999; /* Asegura que flote por ENCIMA del footer y de las tarjetas */
  }
}

</style>