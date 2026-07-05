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
      
      <div class="products-grid">
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
import fotoCaja from '@/assets/caja_dicreme.jpg'
import { ShoppingCart } from 'lucide-vue-next'
import categoryService from '@/services/productCategoryService';
import productService from '@/services/productService';
import Footer from '@/components/Footer.vue'
import Carousel from '@/components/Carousel.vue';
import imgBanner1 from '@/assets/banner1.webp'
import imgBanner2 from '@/assets/banner2.webp'
import imgBanner3 from '@/assets/banner3.webp'




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
  alert('Has cerrado sesión exitosamente.');
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

  const firstType = product.types[0];

  const firstSize = product.sizes?.[0];

  if (!firstSize) return "Sin precio";

  const price = firstType.prices[firstSize];

  return `$${price.toLocaleString("es-CL")}`;
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
/*const goToQuotation = () => {
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
};*/

const goToQuotation = () => {
  if (cartItems.value.length === 0) {
    alert("Tu carrito está vacío.");
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
/*const fetchIceCreams = async () => {
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
}*/

const fetchIceCreams = async () => {
  categoriesList.value = [
    { id: 1, nombre_categoria: "Completos" },
    { id: 2, nombre_categoria: "Papas" },
    { id: 3, nombre_categoria: "Churrascos" },
    { id: 4, nombre_categoria: "Pizzas" },
    { id: 5, nombre_categoria: "Bebidas" }
  ];

  iceCreams.value = [

    //---------------------------------------
    // COMPLETO
    //---------------------------------------
    {
      name: "Completo",
      category: "Completos",
      color: "#E28743",
      image: "https://images.unsplash.com/photo-1612392062798-7c7e16d7f49f?w=900",
      sizes: ["Normal", "XL"],

      types: [

        {
          id: 101,
          name: "Italiano",
          desc: "Palta, tomate y mayo",

          prices: {
            Normal: 2500,
            XL: 3300
          },

          producto_ingrediente: [

            {
              id: 1,
              ingrediente: {
                nombre: "Vienesa",
                disponible: true
              }
            },

            {
              id: 2,
              ingrediente: {
                nombre: "Palta",
                disponible: true
              }
            },

            {
              id: 3,
              ingrediente: {
                nombre: "Tomate",
                disponible: true
              }
            },

            {
              id: 4,
              ingrediente: {
                nombre: "Mayonesa",
                disponible: true
              }
            }

          ]
        },

        {
          id: 102,
          name: "Dinámico",
          desc: "Palta, tomate, americana y mayo",

          prices: {
            Normal: 2700,
            XL: 3600
          },

          producto_ingrediente: [

            {
              id: 5,
              ingrediente: {
                nombre: "Vienesa",
                disponible: true
              }
            },

            {
              id: 6,
              ingrediente: {
                nombre: "Palta",
                disponible: true
              }
            },

            {
              id: 7,
              ingrediente: {
                nombre: "Tomate",
                disponible: true
              }
            },

            {
              id: 8,
              ingrediente: {
                nombre: "Americana",
                disponible: true
              }
            },

            {
              id: 9,
              ingrediente: {
                nombre: "Mayonesa",
                disponible: true
              }
            }

          ]
        },

        {
          id: 103,
          name: "Luco",
          desc: "Queso fundido",

          prices: {
            Normal: 2800,
            XL: 3700
          },

          producto_ingrediente: [

            {
              id: 10,
              ingrediente: {
                nombre: "Vienesa",
                disponible: true
              }
            },

            {
              id: 11,
              ingrediente: {
                nombre: "Queso",
                disponible: true
              }
            }

          ]
        }

      ]
    },

    //---------------------------------------
    // PAPAS
    //---------------------------------------

    {
      name: "Papas Fritas",
      category: "Papas",
      color: "#F9B233",
      image: "https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=900",

      sizes: [
        "Pequeña",
        "Mediana",
        "Grande"
      ],

      types: [

        {
          id: 201,
          name: "Tradicional",
          desc: "Sólo papas",

          prices: {
            Pequeña: 2500,
            Mediana: 3500,
            Grande: 4800
          },

          producto_ingrediente: [
            {
              id: 20,
              ingrediente: {
                nombre: "Sal",
                disponible: true
              }
            }
          ]
        },

        {
          id: 202,
          name: "Papas Queso",
          desc: "Con queso cheddar",

          prices: {
            Pequeña: 3200,
            Mediana: 4300,
            Grande: 5800
          },

          producto_ingrediente: [

            {
              id: 21,
              ingrediente: {
                nombre: "Papas",
                disponible: true
              }
            },

            {
              id: 22,
              ingrediente: {
                nombre: "Queso Cheddar",
                disponible: true
              }
            }

          ]
        }

      ]
    },

    //---------------------------------------
    // CHURRASCOS
    //---------------------------------------

    {
      name: "Churrasco",
      category: "Churrascos",
      color: "#9C6644",
      image: "https://images.unsplash.com/photo-1550317138-10000687a72b?w=900",

      sizes: [
        "Normal"
      ],

      types: [

        {
          id: 301,
          name: "Barros Luco",
          desc: "Queso fundido",

          prices: {
            Normal: 6500
          },

          producto_ingrediente: [

            {
              id: 30,
              ingrediente: {
                nombre: "Carne",
                disponible: true
              }
            },

            {
              id: 31,
              ingrediente: {
                nombre: "Queso",
                disponible: true
              }
            }

          ]
        },

        {
          id: 302,
          name: "Italiano",
          desc: "Palta, tomate y mayo",

          prices: {
            Normal: 6900
          },

          producto_ingrediente: [

            {
              id: 32,
              ingrediente: {
                nombre: "Carne",
                disponible: true
              }
            },

            {
              id: 33,
              ingrediente: {
                nombre: "Palta",
                disponible: true
              }
            },

            {
              id: 34,
              ingrediente: {
                nombre: "Tomate",
                disponible: true
              }
            },

            {
              id: 35,
              ingrediente: {
                nombre: "Mayonesa",
                disponible: true
              }
            }

          ]
        }

      ]
    },

    //---------------------------------------
    // PIZZA
    //---------------------------------------

    {
      name: "Pizza",
      category: "Pizzas",
      color: "#D1495B",
      image: "https://images.unsplash.com/photo-1513104890138-7c749659a591?w=900",

      sizes: [
        "Mediana",
        "Familiar"
      ],

      types: [

        {
          id: 401,
          name: "Napolitana",
          desc: "Jamón, tomate",

          prices: {
            Mediana: 8900,
            Familiar: 11900
          },

          producto_ingrediente: [

            {
              id: 40,
              ingrediente: {
                nombre: "Jamón",
                disponible: true
              }
            },

            {
              id: 41,
              ingrediente: {
                nombre: "Tomate",
                disponible: true
              }
            },

            {
              id: 42,
              ingrediente: {
                nombre: "Queso",
                disponible: true
              }
            }

          ]
        },

        {
          id: 402,
          name: "Pepperoni",
          desc: "Pepperoni extra",

          prices: {
            Mediana: 9500,
            Familiar: 12500
          },

          producto_ingrediente: [

            {
              id: 43,
              ingrediente: {
                nombre: "Pepperoni",
                disponible: true
              }
            },

            {
              id: 44,
              ingrediente: {
                nombre: "Queso",
                disponible: true
              }
            }

          ]
        }

      ]
    }

  ];
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