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

    <Banner />
    
    <main class="content-container">
      <SearchBar 
        v-model="selectedCategory" 
        v-model:searchQuery="searchQueryText"
        :categories="categoriesList"
      />
      
      <div class="products-grid">
        <ProductCard 
          v-for="item in filteredIceCreams" 
          :key="item.name"
          :name="item.name"
          :category="item.category"
          :categoryColor="item.color"
          :image="item.image"
          @view-details="openDetails(item)"
        />
      </div>
    </main>

    <button class="floating-cart" @click="isCartOpen = true">
      <ShoppingCart :size="28" color="white" :stroke-width="2" />
    </button>
  </div>
  <div>
    <Footer />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import Banner from '@/components/Banner.vue'
import SearchBar from '@/components/SearchBar.vue'
import ProductCard from '@/components/ProductCard.vue'
import CartModal from '@/components/CartModal.vue'
import ProductDetailModal from '@/components/ProductDetailModal.vue';
import LoginNoticeModal from '@/components/LoginNoticeModal.vue';
import fotoCaja from '@/assets/caja_dicreme.jpg'
import { ShoppingCart } from 'lucide-vue-next'
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue'

// Estados reactivos
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

// Estados autenticación
const isLoggedIn = ref(false); 
const currentUser = ref<any>(null);

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
  alert('Has cerrado sesión exitosamente.');
};

// Computado para filtrar helados por categoría y búsqueda de texto
const filteredIceCreams = computed(() => {
  let results = iceCreams.value;

  // Filtro 1: por categoría
  if (selectedCategory.value !== 'Todas' && selectedCategory.value !== '') {
    if (selectedCategory.value === 'Vegano' || selectedCategory.value === 'Sin lactosa') {
      results = results.filter(
        item => item.category === 'Al agua' || item.category === 'Leche de avena'
      );
    } else {
      results = results.filter(item => item.category === selectedCategory.value);
    }
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

  // Comparamos usando el sabor exacto y el tamaño físico
  const existingItem = cartItems.value.find(
    item => item.name === purchaseItem.name && item.size === purchaseItem.size
  );

  if (existingItem) {
    // Si el helado coincide completamente en sabor y formato, incrementamos
    existingItem.quantity += purchaseItem.quantity;
  } else {
    // Si es un sabor nuevo (aunque compartan el ID de formato general), se añade como una línea independiente
    cartItems.value.push(purchaseItem);
  }
  
  console.log("Estado actual del carrito Di Creme:", cartItems.value);

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
};

// Procesar la cotización hacia la siguiente pantalla
const goToQuotation = () => {
  if (cartItems.value.length === 0) {
    alert("Tu carrito está vacío.");
    return;
  }
  
  const token = localStorage.getItem('token');  
  
  if (token) {
      isCartOpen.value = false;
      router.push('/cotizacion');
  } else {
      isNoticeOpen.value = true;
  }
};

const handleGoToLogin = () => {
  isNoticeOpen.value = false;
  isCartOpen.value = false;
  router.push('/login');
};

// Función para cargar los productos desde la API
const fetchIceCreams = async () => {
  try {
    const [productsResponse, categoriesResponse] = await Promise.all([
      productService.getProducts(),
      categoryService.getCategory()
    ]);

    if (!productsResponse || !categoriesResponse) {
      throw new Error('Error al obtener los datos');
    }

    const dbProducts = productsResponse.data;
    const dbCategories = categoriesResponse.data;

    categoriesList.value = dbCategories;

    // Diccionario de categorías para mapear IDs a nombres legibles
    const categoryMap: Record<number, string> = {};
    dbCategories.forEach((cat: any) => {
      const catId = cat.id || cat.ID;
      if (catId){
        categoryMap[catId] = cat.nombre_categoria;
      }
    });

    // Auxiliar para agrupar los formatos individuales bajo el mismo nombre de sabor
    const grouped: Record<string, any> = {};

    dbProducts.forEach((prod: any) => {
      const flavorName = prod.nombre_producto;
      const catId = prod.id_categoria || prod.ID_categoria;
      const categoryName = categoryMap[catId] || 'Sin categoría';

      if (!grouped[flavorName]){
        grouped[flavorName] = {
          name: flavorName,
          category: categoryName,
          color: 'var(--DC-pink)',
          image: fotoCaja,
          id10l: null, price10l: 'No disponible',
          id5l: null, price5l: 'No disponible',
          id25l: null, price25l: 'No disponible',
          id1l: null, price1l: 'No disponible'
        };
      }

      const rawPrice = prod.precio_producto || 0;
      const formattedPrice = `$${Number(rawPrice).toLocaleString('es-CL')}`;
      console.log(`Producto: ${flavorName}, Formato ID: ${prod.id_formato || prod.ID_formato}, Precio: ${rawPrice}`);

      const formatId = prod.id_formato || prod.ID_formato;
      
      // Asignamos el ID único de la base de datos de cada helado concreto a su respectivo formato mapeado
      if (formatId === 1) {
        grouped[flavorName].id10l = prod.id || prod.ID;
        grouped[flavorName].price10l = formattedPrice;
      } else if (formatId === 2) {
        grouped[flavorName].id5l = prod.id || prod.ID;
        grouped[flavorName].price5l = formattedPrice;
      } else if (formatId === 3) {
        grouped[flavorName].id25l = prod.id || prod.ID;
        grouped[flavorName].price25l = formattedPrice;
      } else if (formatId === 4) {
        grouped[flavorName].id1l = prod.id || prod.ID;
        grouped[flavorName].price1l = formattedPrice;
      }
    });

    iceCreams.value = Object.values(grouped);
  } catch (error) {
    console.error('Error al cargar los productos:', error);
  }  
}

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
  padding: 20px 8%;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 20px;
  justify-items: center;
}

.floating-cart {
  position: fixed;
  bottom: 30px;
  left: 30px;
  background-color: var(--DC-pink);
  color: white;
  width: 65px;
  height: 65px;
  border-radius: 50%;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(0,0,0,0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 900; 
  transition: transform 0.2s ease;
}

.floating-cart:hover {
  transform: scale(1.1);
}

.floating-cart:active {
  transform: scale(0.9);
}
</style>