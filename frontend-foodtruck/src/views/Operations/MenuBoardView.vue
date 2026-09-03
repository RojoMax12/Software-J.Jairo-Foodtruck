<template>
  <div 
    class="menu-board-container" 
    :class="{ 'hide-cursor': isCursorHidden }"
    @mousemove="handleUserActivity"
    @click="handleUserActivity"
  >
    <!-- TOP HEADER -->
    <header class="board-header">
      <div class="header-brand">
        <img :src="logoImg" alt="Foodtruck J.Junior" class="brand-logo" />
        <div class="brand-titles">
          <h1 class="brand-name">FOODTRUCK J.JUNIOR</h1>
          <span class="brand-tagline">TABLERO DE MENÚ & PRECIOS</span>
        </div>
      </div>

      <!-- Slide Title / Category Indicator -->
      <div class="header-slide-info">
        <span class="slide-category-badge">
          <Sparkles :size="18" class="badge-icon" />
          {{ currentSlideTitle }}
        </span>
      </div>

      <!-- Live Clock & Status -->
      <div class="header-status">
        <div 
          class="status-indicator" 
          :class="{
            'status-open': shiftWindow?.es_jornada_activa,
            'status-day-off': shiftWindow?.es_dia_cerrado,
            'status-closed': !shiftWindow?.es_jornada_activa && !shiftWindow?.es_dia_cerrado
          }"
        >
          <span 
            class="pulse-dot" 
            :class="{
              'dot-green': shiftWindow?.es_jornada_activa,
              'dot-red': shiftWindow?.es_dia_cerrado,
              'dot-gray': !shiftWindow?.es_jornada_activa && !shiftWindow?.es_dia_cerrado
            }"
          ></span>
          <span class="status-text">{{ statusLabel }}</span>
        </div>
        <div class="live-clock">
          <span class="clock-time">{{ currentTimeStr }}</span>
          <span class="clock-date">{{ currentDateStr }}</span>
        </div>
        <button 
          class="fullscreen-toggle" 
          @click="toggleFullscreen" 
          :title="isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa (F11)'"
        >
          <Minimize2 v-if="isFullscreen" :size="20" />
          <Maximize2 v-else :size="20" />
        </button>
      </div>
    </header>

    <!-- MAIN DISPLAY AREA -->
    <main class="board-main">
      <Transition name="slide-fade" mode="out-in">
        <!-- VISTA DE PRODUCTOS (Categoría / Cuadrícula) -->
        <div 
          v-if="currentSlide?.type === 'products'" 
          :key="'slide-' + currentSlideIndex" 
          class="products-grid-layout"
        >
          <div 
            v-for="item in currentSlide.items" 
            :key="item.id || item.name" 
            class="tv-product-card"
          >
            <div class="card-img-wrap">
              <img :src="item.image" :alt="item.name" class="product-img" loading="lazy" />
              <span v-if="item.isOffer" class="badge-offer">¡OFERTA!</span>
            </div>

            <div class="card-body">
              <div class="card-title-row">
                <h3 class="product-title">{{ item.name }}</h3>
                <span class="category-pill">{{ item.category }}</span>
              </div>

              <p v-if="item.description" class="product-desc">{{ item.description }}</p>

              <!-- Precios / Variantes -->
              <div class="price-container">
                <template v-if="item.hasMultiplePrices">
                  <div class="sizes-price-grid">
                    <div v-for="sp in item.sizePrices" :key="sp.size" class="size-chip">
                      <span class="size-name">{{ sp.size }}</span>
                      <span class="size-amount">${{ Number(sp.price).toLocaleString('es-CL') }}</span>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="single-price-box">
                    <span class="single-amount">${{ Number(item.singlePrice || 0).toLocaleString('es-CL') }}</span>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>

        <!-- VISTA DE BANNERS / PROMOCIONES DESTACADAS -->
        <div 
          v-else-if="currentSlide?.type === 'banner'" 
          :key="'banner-' + currentSlideIndex" 
          class="banner-slide-layout"
        >
          <div class="banner-full-wrap">
            <img :src="currentSlide.banner.image" :alt="currentSlide.banner.title || 'Promoción'" class="banner-big-img" />
            <div v-if="currentSlide.banner.title || currentSlide.banner.subtitle" class="banner-overlay-text">
              <h2 v-if="currentSlide.banner.title">{{ currentSlide.banner.title }}</h2>
              <p v-if="currentSlide.banner.subtitle">{{ currentSlide.banner.subtitle }}</p>
            </div>
          </div>
        </div>
      </Transition>
    </main>

    <!-- BOTTOM FOOTER -->
    <footer class="board-footer">
      <!-- QR para Pedir desde el Celular -->
      <div class="footer-qr-block">
        <div class="qr-code-box">
          <img :src="qrCodeUrl" alt="Escanea para pedir" class="qr-image" />
        </div>
        <div class="qr-text">
          <span class="qr-heading">¡PIDE DESDE TU CELULAR!</span>
          <span class="qr-subtext">Escanea el código QR y ordena sin filas</span>
        </div>
      </div>

      <!-- Marquesina de Noticias / Anuncios -->
      <div class="footer-ticker">
        <div class="ticker-content">
          <span class="ticker-item">🍔 <b>100% Calidad y Sabor</b> · Preparado fresco en el momento</span>
          <span class="ticker-separator">✦</span>
          <span class="ticker-item">💳 <b>Métodos de pago</b>: Efectivo, Débito, Crédito y Transferencia</span>
          <span class="ticker-separator">✦</span>
          <span class="ticker-item">📍 <b>Foodtruck J.Junior</b> · ¡Gracias por tu preferencia!</span>
          <span class="ticker-separator">✦</span>
          <span v-if="customAnnouncement" class="ticker-item announcement-highlight">
            📢 {{ customAnnouncement }}
          </span>
        </div>
      </div>

      <!-- Controles de Slide & Barra de Progreso -->
      <div class="footer-slide-controls">
        <div class="progress-bar-wrap" title="Tiempo para el próximo cambio de pantalla">
          <div class="progress-bar-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>

        <div class="dots-row">
          <button 
            v-for="(_, idx) in slides" 
            :key="'dot-' + idx"
            class="slide-dot" 
            :class="{ active: currentSlideIndex === idx }"
            @click="goToSlide(idx)"
          ></button>
          <span class="slide-counter">{{ currentSlideIndex + 1 }} / {{ slides.length }}</span>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Sparkles, Maximize2, Minimize2 } from 'lucide-vue-next';
import logoImg from '@/assets/logo_jairo.webp';
import productService from '@/services/productService';
import { useMarketingConfig } from '@/composables/useMarketingConfig';
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService';

const { activeBanners, activeAnnouncements, resolveImageUrl } = useMarketingConfig();

const shiftWindow = ref<ShiftWindow | null>(null);

const loadShiftWindow = async () => {
  try {
    shiftWindow.value = await cashFlowService.fetchShiftWindowFromBackend();
  } catch (e) {
    console.warn('Error al cargar horario en TV:', e);
  }
};

const statusLabel = computed(() => {
  if (shiftWindow.value?.es_jornada_activa) return 'ABIERTO EN VIVO';
  if (shiftWindow.value?.es_dia_cerrado) return 'CERRADO HOY (DESCANSO)';
  return `CERRADO (ABRE ${shiftWindow.value?.hora_apertura || '19:00'})`;
});

interface ProductDisplayItem {
  id: string | number;
  name: string;
  category: string;
  image: string;
  description?: string;
  hasMultiplePrices: boolean;
  singlePrice?: number;
  sizePrices?: { size: string; price: number }[];
  isOffer?: boolean;
}

interface SlideItem {
  id: string;
  title: string;
  type: 'products' | 'banner';
  items?: ProductDisplayItem[];
  banner?: any;
}

const slides = ref<SlideItem[]>([]);
const currentSlideIndex = ref(0);
const progressPercent = ref(0);
const isFullscreen = ref(false);
const isCursorHidden = ref(false);

const SLIDE_DURATION_SECONDS = 12; // 12 segundos por pantalla
let slideInterval: any = null;
let progressInterval: any = null;
let clockInterval: any = null;
let autoSyncInterval: any = null;
let cursorTimeout: any = null;

// Reloj en vivo
const currentTimeStr = ref('');
const currentDateStr = ref('');

const updateClock = () => {
  const now = new Date();
  currentTimeStr.value = now.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  const dayName = now.toLocaleDateString('es-CL', { weekday: 'long' });
  const dayNum = now.getDate();
  const monthName = now.toLocaleDateString('es-CL', { month: 'short' });
  currentDateStr.value = `${dayName.charAt(0).toUpperCase() + dayName.slice(1)}, ${dayNum} ${monthName}`;
};

// QR Code dinámico
const qrCodeUrl = computed(() => {
  const host = window.location.origin;
  return `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(host)}&bgcolor=1c1822&color=f5ebe0&margin=1`;
});

const customAnnouncement = computed(() => {
  const list = activeAnnouncements();
  return list.length > 0 ? list.map((a: any) => a.text || a.mensaje || '').filter(Boolean).join(' · ') : '';
});

const currentSlide = computed(() => slides.value[currentSlideIndex.value] || null);

const currentSlideTitle = computed(() => {
  return currentSlide.value?.title || 'CARTA Y PROMOCIONES';
});

// Cargar y estructurar productos para TV
const loadProductsForTv = async () => {
  try {
    const res = await productService.getPublicProducts();
    const rawProducts = res.data || [];

    // Filtrar solo activos y disponibles
    const activeProducts = rawProducts.filter((p: any) => {
      return p.activo !== false && p.activo !== 0 && p.disponible !== false && p.disponible !== 0 && p.estado !== 0;
    });

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
      'Bebidas calientes': 'https://images.unsplash.com/photo-1497636577773-f1231844b336?w=900',
      'Bebestibles & Jugos': 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=900'
    };

    // Mapear productos
    const mapped: ProductDisplayItem[] = activeProducts.map((p: any) => {
      const catName = p.categoria?.nombre_categoria || p.category || 'Carta';
      const prodImage = p.imagen_url || p.imagen || p.image || categoryImages[catName] || logoImg;

      const sizePrices: { size: string; price: number }[] = [];
      const rawSizes = p.tamaños || p.tamanos || p.sizes || [];

      if (Array.isArray(rawSizes) && rawSizes.length > 0) {
        rawSizes.forEach((t: any) => {
          const sName = String(t.nombre || t.name || '').trim();
          const sPrice = Number(t.pivot?.precio ?? t.precio ?? t.price ?? 0);
          if (sPrice > 0) {
            sizePrices.push({
              size: sName || 'Normal',
              price: sPrice
            });
          }
        });
      }

      // Si no vinieron en tamaños, revisar types o precio plano
      if (sizePrices.length === 0 && Array.isArray(p.types)) {
        p.types.forEach((t: any) => {
          Object.entries(t.prices || {}).forEach(([sz, pr]) => {
            const num = Number(pr);
            if (!isNaN(num) && num > 0) {
              sizePrices.push({ size: sz, price: num });
            }
          });
        });
      }

      if (sizePrices.length === 0) {
        const flat = Number(p.precio || p.price || 0);
        if (flat > 0) {
          sizePrices.push({ size: 'Normal', price: flat });
        }
      }

      // ¿Múltiples precios reales? (Ignoramos si solo dice "Único" o si solo hay 1 precio)
      const isOnlyUnico = sizePrices.length === 1 && (sizePrices[0]?.size.toLowerCase() === 'único' || sizePrices[0]?.size.toLowerCase() === 'unico');
      const hasMultiplePrices = sizePrices.length > 1 && !isOnlyUnico;
      const singlePrice = sizePrices.length > 0 && sizePrices[0] ? sizePrices[0].price : 0;

      // Descripción o ingredientes destacados
      const ingredients = (p.ingredientes || p.types?.[0]?.producto_ingrediente || [])
        .map((pi: any) => pi.ingrediente?.nombre || pi.nombre)
        .filter(Boolean)
        .filter((name: string) => !name.toLowerCase().startsWith('pan '))
        .slice(0, 4)
        .join(', ');

      return {
        id: p.id || p.id_producto,
        name: p.nombre || p.name || 'Producto',
        category: catName,
        image: prodImage,
        description: p.descripcion || (ingredients ? `Con: ${ingredients}` : ''),
        hasMultiplePrices,
        singlePrice,
        sizePrices,
        isOffer: Boolean(p.en_oferta || p.is_offer)
      };
    });

    // Agrupar por bloques de 6 u 8 items por pantalla
    const ITEMS_PER_SLIDE = 6;
    const newSlides: SlideItem[] = [];

    // Agrupar por categoría
    const categoriesMap = new Map<string, ProductDisplayItem[]>();
    mapped.forEach(item => {
      const cat = item.category || 'Varios';
      if (!categoriesMap.has(cat)) categoriesMap.set(cat, []);
      categoriesMap.get(cat)!.push(item);
    });

    // Generar slides por categoría
    categoriesMap.forEach((items, catName) => {
      for (let i = 0; i < items.length; i += ITEMS_PER_SLIDE) {
        const chunk = items.slice(i, i + ITEMS_PER_SLIDE);
        const partLabel = items.length > ITEMS_PER_SLIDE ? ` (Parte ${Math.floor(i / ITEMS_PER_SLIDE) + 1})` : '';
        newSlides.push({
          id: `cat-${catName}-${i}`,
          title: `${catName.toUpperCase()}${partLabel}`,
          type: 'products',
          items: chunk
        });
      }
    });

    // Agregar slide de Banners si existen
    const bannersList = activeBanners();
    if (bannersList && bannersList.length > 0) {
      bannersList.forEach((b: any, index: number) => {
        newSlides.push({
          id: `banner-${b.id || index}`,
          title: '🔥 PROMO DESTACADA',
          type: 'banner',
          banner: {
            ...b,
            image: resolveImageUrl(b.image)
          }
        });
      });
    }

    if (newSlides.length > 0) {
      slides.value = newSlides;
    }
  } catch (err) {
    console.error('Error cargando productos en Menu Board:', err);
  }
};

// Control de rotación
const startRotation = () => {
  stopRotation();

  const stepMs = 100;
  const totalSteps = (SLIDE_DURATION_SECONDS * 1000) / stepMs;
  let currentStep = 0;

  progressInterval = setInterval(() => {
    currentStep++;
    progressPercent.value = Math.min(100, (currentStep / totalSteps) * 100);

    if (currentStep >= totalSteps) {
      currentStep = 0;
      nextSlide();
    }
  }, stepMs);
};

const stopRotation = () => {
  if (progressInterval) clearInterval(progressInterval);
  if (slideInterval) clearInterval(slideInterval);
};

const nextSlide = () => {
  if (slides.value.length === 0) return;
  currentSlideIndex.value = (currentSlideIndex.value + 1) % slides.value.length;
  progressPercent.value = 0;
};

const prevSlide = () => {
  if (slides.value.length === 0) return;
  currentSlideIndex.value = (currentSlideIndex.value - 1 + slides.value.length) % slides.value.length;
  progressPercent.value = 0;
};

const goToSlide = (idx: number) => {
  currentSlideIndex.value = idx;
  progressPercent.value = 0;
};

// Pantalla completa
const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
    isFullscreen.value = true;
  } else {
    document.exitFullscreen().catch(() => {});
    isFullscreen.value = false;
  }
};

// Ocultar cursor en inactividad
const handleUserActivity = () => {
  isCursorHidden.value = false;
  if (cursorTimeout) clearTimeout(cursorTimeout);
  cursorTimeout = setTimeout(() => {
    isCursorHidden.value = true;
  }, 3500);
};

// Teclado
const handleKeyDown = (e: KeyboardEvent) => {
  if (e.key === 'ArrowRight' || e.key === 'PageDown') {
    nextSlide();
  } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
    prevSlide();
  } else if (e.key === 'f' || e.key === 'F') {
    toggleFullscreen();
  }
};

onMounted(async () => {
  updateClock();
  clockInterval = setInterval(updateClock, 1000);

  await Promise.all([
    loadProductsForTv(),
    loadShiftWindow()
  ]);
  startRotation();

  // Sincronización silenciosa en background cada 60s
  autoSyncInterval = setInterval(() => {
    loadProductsForTv();
    loadShiftWindow();
  }, 60000);

  window.addEventListener('keydown', handleKeyDown);
  handleUserActivity();
});

onUnmounted(() => {
  stopRotation();
  if (clockInterval) clearInterval(clockInterval);
  if (autoSyncInterval) clearInterval(autoSyncInterval);
  if (cursorTimeout) clearTimeout(cursorTimeout);
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
/* RESET & FULLSCREEN CONTAINER */
.menu-board-container {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: radial-gradient(circle at top right, #251d2e 0%, #15121b 100%);
  color: #ffffff;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  font-family: 'Montserrat', system-ui, -apple-system, sans-serif;
  user-select: none;
  z-index: 99999;
}

.menu-board-container.hide-cursor {
  cursor: none !important;
}

/* ========================================================
   HEADER
   ======================================================== */
.board-header {
  height: 80px;
  background: rgba(21, 18, 27, 0.85);
  backdrop-filter: blur(10px);
  border-bottom: 2px solid rgba(226, 135, 67, 0.4);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  flex-shrink: 0;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 16px;
}

.brand-logo {
  height: 52px;
  width: auto;
  filter: drop-shadow(0 2px 8px rgba(226, 135, 67, 0.35));
}

.brand-titles {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.45rem;
  font-weight: 900;
  letter-spacing: 0.06em;
  margin: 0;
  background: linear-gradient(135deg, #ffffff 0%, #fcd34d 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.brand-tagline {
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  color: #e28743;
}

/* Slide Category Badge */
.header-slide-info {
  display: flex;
  align-items: center;
}

.slide-category-badge {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  background: linear-gradient(135deg, #e28743 0%, #984c05 100%);
  padding: 8px 24px;
  border-radius: 30px;
  font-size: 1.15rem;
  font-weight: 900;
  letter-spacing: 0.08em;
  color: #ffffff;
  box-shadow: 0 4px 16px rgba(226, 135, 67, 0.35);
  text-transform: uppercase;
}

.badge-icon {
  color: #fef08a;
  animation: pulse-icon 2s infinite ease-in-out;
}

@keyframes pulse-icon {
  0%, 100% { transform: scale(1); opacity: 0.9; }
  50% { transform: scale(1.15); opacity: 1; }
}

/* Header Status & Clock */
.header-status {
  display: flex;
  align-items: center;
  gap: 24px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(34, 197, 94, 0.12);
  border: 1px solid rgba(34, 197, 94, 0.4);
  padding: 6px 14px;
  border-radius: 20px;
  transition: all 0.3s ease;
}

.status-indicator.status-open {
  background: rgba(34, 197, 94, 0.12);
  border-color: rgba(34, 197, 94, 0.4);
}
.status-indicator.status-open .status-text {
  color: #4ade80;
}

.status-indicator.status-day-off {
  background: rgba(239, 68, 68, 0.15);
  border-color: rgba(239, 68, 68, 0.5);
}
.status-indicator.status-day-off .status-text {
  color: #f87171;
}

.status-indicator.status-closed {
  background: rgba(148, 163, 184, 0.12);
  border-color: rgba(148, 163, 184, 0.3);
}
.status-indicator.status-closed .status-text {
  color: #94a3b8;
}

.pulse-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #22c55e;
}

.pulse-dot.dot-green {
  background: #22c55e;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  animation: pulse-green 1.8s infinite;
}

.pulse-dot.dot-red {
  background: #ef4444;
  box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
  animation: pulse-red 1.8s infinite;
}

.pulse-dot.dot-gray {
  background: #94a3b8;
  box-shadow: none;
  animation: none;
}

@keyframes pulse-green {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

@keyframes pulse-red {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.status-text {
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.05em;
}

.live-clock {
  display: flex;
  flex-direction: column;
  text-align: right;
}

.clock-time {
  font-size: 1.35rem;
  font-weight: 900;
  color: #ffffff;
  letter-spacing: 0.05em;
  font-variant-numeric: tabular-nums;
}

.clock-date {
  font-size: 0.75rem;
  color: #cbd5e1;
  font-weight: 600;
}

.fullscreen-toggle {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #cbd5e1;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.fullscreen-toggle:hover {
  background: rgba(226, 135, 67, 0.3);
  color: #ffffff;
}

/* ========================================================
   MAIN DISPLAY AREA
   ======================================================== */
.board-main {
  flex-grow: 1;
  padding: 24px 32px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Grid de Productos (3 columnas x 2 filas para 6 productos en 1080p) */
.products-grid-layout {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-template-rows: repeat(2, 1fr);
  gap: 20px;
  width: 100%;
  height: 100%;
}

.tv-product-card {
  background: linear-gradient(135deg, rgba(42, 35, 52, 0.7) 0%, rgba(28, 24, 34, 0.85) 100%);
  border: 1.5px solid rgba(226, 135, 67, 0.25);
  border-radius: 18px;
  padding: 16px;
  display: flex;
  gap: 16px;
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
  position: relative;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.tv-product-card:hover {
  border-color: rgba(226, 135, 67, 0.6);
  box-shadow: 0 12px 30px rgba(226, 135, 67, 0.2);
}

.card-img-wrap {
  width: 140px;
  height: 140px;
  flex-shrink: 0;
  border-radius: 14px;
  overflow: hidden;
  position: relative;
  background: #110e16;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.badge-offer {
  position: absolute;
  top: 8px;
  left: 8px;
  background: #dc2626;
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 6px;
  letter-spacing: 0.05em;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}

.card-body {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  padding: 4px 0;
}

.card-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 8px;
}

.product-title {
  font-size: 1.35rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0;
  line-height: 1.2;
}

.category-pill {
  font-size: 0.68rem;
  font-weight: 700;
  color: #e28743;
  text-transform: uppercase;
  background: rgba(226, 135, 67, 0.12);
  padding: 3px 8px;
  border-radius: 8px;
  white-space: nowrap;
}

.product-desc {
  font-size: 0.85rem;
  color: #cbd5e1;
  margin: 6px 0;
  line-height: 1.35;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Precios */
.price-container {
  margin-top: auto;
}

.single-price-box {
  display: flex;
  align-items: baseline;
}

.single-amount {
  font-size: 1.65rem;
  font-weight: 900;
  color: #fcd34d;
  letter-spacing: 0.02em;
}

.sizes-price-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.size-chip {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  padding: 4px 10px;
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.size-name {
  font-size: 0.75rem;
  font-weight: 700;
  color: #cbd5e1;
  text-transform: uppercase;
}

.size-amount {
  font-size: 1.05rem;
  font-weight: 900;
  color: #fcd34d;
}

/* Banner Layout */
.banner-slide-layout {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.banner-full-wrap {
  width: 100%;
  height: 100%;
  max-height: calc(100vh - 200px);
  border-radius: 24px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
  border: 2px solid rgba(226, 135, 67, 0.4);
}

.banner-big-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.banner-overlay-text {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 40px 50px;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9) 0%, transparent 100%);
}

.banner-overlay-text h2 {
  font-size: 2.5rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 8px 0;
}

.banner-overlay-text p {
  font-size: 1.25rem;
  color: #fcd34d;
  margin: 0;
}

/* ========================================================
   FOOTER
   ======================================================== */
.board-footer {
  height: 96px;
  background: rgba(18, 15, 23, 0.95);
  backdrop-filter: blur(10px);
  border-top: 2px solid rgba(226, 135, 67, 0.4);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  gap: 24px;
  flex-shrink: 0;
  position: relative;
}

/* QR Block */
.footer-qr-block {
  display: flex;
  align-items: center;
  gap: 14px;
  background: rgba(255, 255, 255, 0.05);
  padding: 8px 16px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  flex-shrink: 0;
}

.qr-code-box {
  width: 68px;
  height: 68px;
  border-radius: 8px;
  background: #ffffff;
  padding: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.qr-image {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.qr-text {
  display: flex;
  flex-direction: column;
}

.qr-heading {
  font-size: 0.92rem;
  font-weight: 900;
  color: #fcd34d;
  letter-spacing: 0.04em;
}

.qr-subtext {
  font-size: 0.72rem;
  color: #cbd5e1;
  font-weight: 600;
}

/* Ticker / Marquesina */
.footer-ticker {
  flex-grow: 1;
  overflow: hidden;
  white-space: nowrap;
  position: relative;
  background: rgba(0, 0, 0, 0.25);
  padding: 10px 20px;
  border-radius: 30px;
  border: 1px solid rgba(255, 255, 255, 0.05);
  min-width: 0;
  max-width: 55%;
}

.ticker-content {
  display: inline-block;
  animation: marquee-scroll 40s linear infinite;
}

@keyframes marquee-scroll {
  0% { transform: translateX(50%); }
  100% { transform: translateX(-100%); }
}

.ticker-item {
  font-size: 0.88rem;
  color: #e2e8f0;
  font-weight: 500;
}

.ticker-item b {
  color: #fcd34d;
}

.ticker-separator {
  margin: 0 18px;
  color: #e28743;
  font-size: 0.9rem;
}

.announcement-highlight {
  color: #38bdf8;
  font-weight: 700;
}

/* Controls & Progress */
.footer-slide-controls {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 8px;
  flex-shrink: 0;
  width: 170px;
}

.progress-bar-wrap {
  width: 100%;
  height: 6px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #e28743 0%, #fcd34d 100%);
  transition: width 0.1s linear;
}

.dots-row {
  display: flex;
  align-items: center;
  gap: 8px;
}

.slide-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.slide-dot.active {
  width: 22px;
  border-radius: 6px;
  background: #e28743;
}

.slide-counter {
  font-size: 0.75rem;
  font-weight: 800;
  color: #94a3b8;
  margin-left: 6px;
  font-variant-numeric: tabular-nums;
}

/* ========================================================
   ANIMACIONES DE TRANSICIÓN DE SLIDES
   ======================================================== */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(30px) scale(0.98);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-30px) scale(0.98);
}

/* ========================================================
   RESPONSIVE / AJUSTES PARA PANTALLAS MENORES
   ======================================================== */
@media (max-width: 1200px) {
  .products-grid-layout {
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(3, 1fr);
  }
}

@media (max-width: 768px) {
  .board-header {
    height: 70px;
    padding: 0 16px;
  }
  .brand-name {
    font-size: 1.1rem;
  }
  .products-grid-layout {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
    overflow-y: auto;
  }
  .board-footer {
    height: auto;
    flex-wrap: wrap;
    padding: 12px 16px;
  }
  .footer-ticker {
    order: 3;
    width: 100%;
  }
}
</style>
