<template>
  <div 
    class="menu-board-container" 
    :class="{ 'hide-cursor': isCursorHidden }"
    @mousemove="handleUserActivity"
    @click="handleUserActivity"
  >
    <!-- ===================== TOP HEADER ===================== -->
    <header class="board-header">
      <div class="header-brand">
        <img :src="logoImg" alt="Foodtruck J.Junior Logo" class="brand-logo" />
        <div class="brand-titles">
          <h1 class="brand-name">FOODTRUCK J.JUNIOR</h1>
          <span class="brand-tagline">TABLERO DIGITAL DE MENÚ & PRECIOS</span>
        </div>
      </div>

      <!-- TÍTULO DE LA CATEGORÍA / SLIDE ACTUAL -->
      <div class="header-slide-info">
        <div class="slide-category-badge">
          <Sparkles :size="16" class="badge-icon" />
          <span>{{ currentSlideTitle }}</span>
        </div>
      </div>

      <!-- ESTADO DEL LOCAL & RELOJ EN VIVO -->
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
          type="button"
          class="fullscreen-toggle" 
          @click="toggleFullscreen" 
          :title="isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa (F11)'"
          aria-label="Alternar pantalla completa"
        >
          <Minimize2 v-if="isFullscreen" :size="18" />
          <Maximize2 v-else :size="18" />
        </button>
      </div>
    </header>

    <!-- ===================== MAIN DISPLAY AREA ===================== -->
    <main class="board-main">
      <Transition name="slide-fade" mode="out-in">
        <!-- VISTA: GRILLA DE PRODUCTOS -->
        <div 
          v-if="currentSlide?.type === 'products'" 
          :key="'slide-' + currentSlideIndex" 
          class="products-grid-layout"
        >
          <article 
            v-for="item in currentSlide.items" 
            :key="item.id || item.name" 
            class="tv-product-card"
          >
            <div class="card-img-wrap">
              <img :src="item.image" :alt="item.name" class="product-img" loading="lazy" />
              <span v-if="item.isOffer" class="badge-offer">¡PROMO!</span>
            </div>

            <div class="card-body">
              <div class="card-title-row">
                <h3 class="product-title">{{ item.name }}</h3>
                <span class="category-pill">{{ item.category }}</span>
              </div>

              <p v-if="item.description" class="product-desc">{{ item.description }}</p>

              <!-- CONTENEDOR DE PRECIOS -->
              <div class="price-container">
                <template v-if="item.hasMultiplePrices">
                  <div class="sizes-price-grid">
                    <div v-for="sp in item.sizePrices" :key="sp.size" class="size-chip">
                      <span class="size-name">{{ sp.size }}</span>
                      <strong class="size-amount">${{ Number(sp.price).toLocaleString('es-CL') }}</strong>
                    </div>
                  </div>
                </template>
                <template v-else>
                  <div class="single-price-box">
                    <strong class="single-amount">${{ Number(item.singlePrice || 0).toLocaleString('es-CL') }}</strong>
                  </div>
                </template>
              </div>
            </div>
          </article>
        </div>

        <!-- VISTA: BANNERS / COMBOS DESTACADOS -->
        <div 
          v-else-if="currentSlide?.type === 'banner'" 
          :key="'banner-' + currentSlideIndex" 
          class="banner-slide-layout"
        >
          <div class="banner-full-wrap">
            <img :src="currentSlide.banner.image" :alt="currentSlide.banner.title || 'Promoción Especial'" class="banner-big-img" />
            <div v-if="currentSlide.banner.title || currentSlide.banner.subtitle" class="banner-overlay-text">
              <h2 v-if="currentSlide.banner.title">{{ currentSlide.banner.title }}</h2>
              <p v-if="currentSlide.banner.subtitle">{{ currentSlide.banner.subtitle }}</p>
            </div>
          </div>
        </div>
      </Transition>
    </main>

    <!-- ===================== BOTTOM FOOTER ===================== -->
    <footer class="board-footer">
      <!-- BLOQUE QR PARA PEDIR ONLINE -->
      <div class="footer-qr-block">
        <div class="qr-code-box">
          <img :src="qrCodeUrl" alt="Código QR para pedir online" class="qr-image" />
        </div>
        <div class="qr-text">
          <strong class="qr-heading">¡PIDE DESDE TU MÓVIL!</strong>
          <span class="qr-subtext">Escanea el código QR y ordena sin hacer filas</span>
        </div>
      </div>

      <!-- MARQUESINA CONTINUA DE AVISOS SIN SOLAPAMIENTO -->
      <div class="footer-ticker" role="region" aria-label="Avisos en vivo">
        <div class="ticker-fade-left" aria-hidden="true"></div>

        <div class="ticker-track">
          <!-- Bloque primario -->
          <div class="ticker-content">
            <span class="ticker-item">🍔 <b>100% Calidad & Sabor</b> · Preparado fresco al momento</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span class="ticker-item">💳 <b>Medios de Pago:</b> Efectivo, Débito, Crédito y Transferencia</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span class="ticker-item">📍 <b>Foodtruck J.Junior</b> · ¡Gracias por tu preferencia!</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span v-if="customAnnouncement" class="ticker-item announcement-highlight">
              📢 {{ customAnnouncement }}
              <span class="ticker-separator" aria-hidden="true">✦</span>
            </span>
          </div>

          <!-- Bloque clonado para bucle continuo -->
          <div class="ticker-content" aria-hidden="true">
            <span class="ticker-item">🍔 <b>100% Calidad & Sabor</b> · Preparado fresco al momento</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span class="ticker-item">💳 <b>Medios de Pago:</b> Efectivo, Débito, Crédito y Transferencia</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span class="ticker-item">📍 <b>Foodtruck J.Junior</b> · ¡Gracias por tu preferencia!</span>
            <span class="ticker-separator" aria-hidden="true">✦</span>
            <span v-if="customAnnouncement" class="ticker-item announcement-highlight">
              📢 {{ customAnnouncement }}
              <span class="ticker-separator" aria-hidden="true">✦</span>
            </span>
          </div>
        </div>

        <div class="ticker-fade-right" aria-hidden="true"></div>
      </div>

      <!-- CONTROLES Y BARRA DE PROGRESO DE ROTACIÓN -->
      <div class="footer-slide-controls">
        <div class="progress-bar-wrap" title="Progreso de pantalla">
          <div class="progress-bar-fill" :style="{ width: progressPercent + '%' }"></div>
        </div>

        <div class="dots-row">
          <button 
            v-for="(_, idx) in slides" 
            :key="'dot-' + idx"
            type="button"
            class="slide-dot" 
            :class="{ active: currentSlideIndex === idx }"
            @click="goToSlide(idx)"
            :aria-label="`Ir a pantalla ${idx + 1}`"
          ></button>
          <span class="slide-counter">{{ currentSlideIndex + 1 }} / {{ Math.max(1, slides.length) }}</span>
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
  if (shiftWindow.value?.es_jornada_activa) return 'LOCAL ABIERTO';
  if (shiftWindow.value?.es_dia_cerrado) return 'DÍA DE DESCANSO';
  return `CERRADO (APERTURA ${shiftWindow.value?.hora_apertura || '19:00'})`;
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

const SLIDE_DURATION_SECONDS = 12;
let progressInterval: ReturnType<typeof setInterval> | null = null;
let clockInterval: ReturnType<typeof setInterval> | null = null;
let autoSyncInterval: ReturnType<typeof setInterval> | null = null;
let cursorTimeout: ReturnType<typeof setTimeout> | null = null;

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

// Generación de QR dinámico
const qrCodeUrl = computed(() => {
  const host = window.location.origin;
  return `https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=${encodeURIComponent(host)}&bgcolor=1a1412&color=f8f6f3&margin=1`;
});

const customAnnouncement = computed(() => {
  const list = typeof activeAnnouncements === 'function' ? activeAnnouncements() : [];
  return Array.isArray(list) && list.length > 0 
    ? list.map((a: any) => a.text || a.mensaje || '').filter(Boolean).join(' · ') 
    : '';
});

const currentSlide = computed(() => slides.value[currentSlideIndex.value] || null);
const currentSlideTitle = computed(() => currentSlide.value?.title || 'CARTA & PROMOCIONES');

// Carga y estructuración de productos para Smart TV
const loadProductsForTv = async () => {
  try {
    const res = await productService.getPublicProducts();
    const rawProducts = Array.isArray(res?.data) ? res.data : (res?.data?.data || []);

    const activeProducts = rawProducts.filter((p: any) => {
      return p.activo !== false && p.activo !== 0 && p.disponible !== false && p.disponible !== 0;
    });

    const mapped: ProductDisplayItem[] = activeProducts.map((p: any) => {
      const catName = p.categoria?.nombre_categoria || p.category || 'Carta';
      const prodImage = p.imagen_url || p.imagen || p.image || logoImg;

      const sizePrices: { size: string; price: number }[] = [];
      const rawSizes = p.tamaños || p.tamanos || p.sizes || [];

      if (Array.isArray(rawSizes) && rawSizes.length > 0) {
        rawSizes.forEach((t: any) => {
          const sName = String(t.nombre || t.name || '').trim();
          const sPrice = Number(t.pivot?.precio ?? t.precio ?? t.price ?? 0);
          if (sPrice > 0) {
            sizePrices.push({ size: sName || 'Normal', price: sPrice });
          }
        });
      }

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

      const promotionPrice = Number(p.promocion_activa?.precio_promocional ?? 0);
      if (promotionPrice > 0) {
        sizePrices.forEach(sp => { sp.price = promotionPrice; });
      }

      if (sizePrices.length === 0) {
        const flat = Number(p.precio || p.price || 0);
        if (flat > 0) {
          sizePrices.push({ size: 'Normal', price: flat });
        }
      }

      const isOnlyUnico = sizePrices.length === 1 && (sizePrices[0]?.size.toLowerCase() === 'único' || sizePrices[0]?.size.toLowerCase() === 'unico');
      const hasMultiplePrices = sizePrices.length > 1 && !isOnlyUnico;
      const singlePrice = sizePrices.length > 0 && sizePrices[0] ? sizePrices[0].price : 0;

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
        isOffer: Boolean(p.en_oferta || p.is_offer || promotionPrice > 0)
      };
    });

    const ITEMS_PER_SLIDE = 6;
    const newSlides: SlideItem[] = [];

    const categoriesMap = new Map<string, ProductDisplayItem[]>();
    mapped.forEach(item => {
      const cat = item.category || 'Varios';
      if (!categoriesMap.has(cat)) categoriesMap.set(cat, []);
      categoriesMap.get(cat)!.push(item);
    });

    categoriesMap.forEach((items, catName) => {
      for (let i = 0; i < items.length; i += ITEMS_PER_SLIDE) {
        const chunk = items.slice(i, i + ITEMS_PER_SLIDE);
        const partLabel = items.length > ITEMS_PER_SLIDE ? ` (${Math.floor(i / ITEMS_PER_SLIDE) + 1})` : '';
        newSlides.push({
          id: `cat-${catName}-${i}`,
          title: `${catName.toUpperCase()}${partLabel}`,
          type: 'products',
          items: chunk
        });
      }
    });

    const bannersList = typeof activeBanners === 'function' ? activeBanners() : [];
    if (Array.isArray(bannersList) && bannersList.length > 0) {
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
    console.error('Error cargando catálogo en Menu Board:', err);
  }
};

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
  if (progressInterval) {
    clearInterval(progressInterval);
    progressInterval = null;
  }
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

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen().catch(() => {});
  } else {
    document.exitFullscreen().catch(() => {});
  }
};

const handleFullscreenChange = () => {
  isFullscreen.value = Boolean(document.fullscreenElement);
};

const handleUserActivity = () => {
  isCursorHidden.value = false;
  if (cursorTimeout) clearTimeout(cursorTimeout);
  cursorTimeout = setTimeout(() => {
    isCursorHidden.value = true;
  }, 3500);
};

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

  autoSyncInterval = setInterval(() => {
    loadProductsForTv();
    loadShiftWindow();
  }, 60000);

  document.addEventListener('fullscreenchange', handleFullscreenChange);
  window.addEventListener('keydown', handleKeyDown);
  handleUserActivity();
});

onUnmounted(() => {
  stopRotation();
  if (clockInterval) clearInterval(clockInterval);
  if (autoSyncInterval) clearInterval(autoSyncInterval);
  if (cursorTimeout) clearTimeout(cursorTimeout);
  document.removeEventListener('fullscreenchange', handleFullscreenChange);
  window.removeEventListener('keydown', handleKeyDown);
});
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.menu-board-container {
  position: fixed;
  inset: 0;
  width: 100vw;
  height: 100vh;
  background: radial-gradient(circle at top right, #241b18 0%, #15100e 100%);
  color: #ffffff;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  font-family: inherit;
  user-select: none;
  z-index: 99999;
}

.menu-board-container.hide-cursor {
  cursor: none !important;
}

/* ========================================================
   HEADER DE LA PANTALLA
   ======================================================== */
.board-header {
  height: 78px;
  background: rgba(26, 20, 18, 0.92);
  backdrop-filter: blur(12px);
  border-bottom: 2px solid rgba(226, 135, 67, 0.45);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  flex-shrink: 0;
}

.header-brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  height: 48px;
  width: auto;
  filter: drop-shadow(0 2px 8px rgba(226, 135, 67, 0.35));
}

.brand-titles {
  display: flex;
  flex-direction: column;
}

.brand-name {
  font-size: 1.35rem;
  font-weight: 900;
  letter-spacing: 0.05em;
  margin: 0;
  background: linear-gradient(135deg, #ffffff 0%, #fed7aa 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.brand-tagline {
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.14em;
  color: var(--DC-orange, #e28743);
}

.header-slide-info {
  display: flex;
  align-items: center;
}

.slide-category-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, var(--DC-orange, #e28743) 0%, #9a3412 100%);
  padding: 0.45rem 1.4rem;
  border-radius: 999px;
  font-size: 1.05rem;
  font-weight: 900;
  letter-spacing: 0.06em;
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

.header-status {
  display: flex;
  align-items: center;
  gap: 1.25rem;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.04em;
}

.status-indicator.status-open {
  background: rgba(34, 197, 94, 0.14);
  border: 1px solid rgba(34, 197, 94, 0.45);
  color: #4ade80;
}

.status-indicator.status-day-off {
  background: rgba(239, 68, 68, 0.16);
  border: 1px solid rgba(239, 68, 68, 0.5);
  color: #f87171;
}

.status-indicator.status-closed {
  background: rgba(148, 163, 184, 0.12);
  border: 1px solid rgba(148, 163, 184, 0.3);
  color: #94a3b8;
}

.pulse-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
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

.live-clock {
  display: flex;
  flex-direction: column;
  text-align: right;
}

.clock-time {
  font-size: 1.25rem;
  font-weight: 900;
  color: #ffffff;
  font-variant-numeric: tabular-nums;
  line-height: 1.1;
}

.clock-date {
  font-size: 0.72rem;
  color: #cbd5e1;
  font-weight: 600;
}

.fullscreen-toggle {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.18);
  color: #cbd5e1;
  width: 36px;
  height: 36px;
  border-radius: 9px;
  display: grid;
  place-items: center;
  cursor: pointer;
  transition: all 0.2s ease;
}

.fullscreen-toggle:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  border-color: var(--DC-orange, #e28743);
}

/* ========================================================
   ÁREA PRINCIPAL
   ======================================================== */
.board-main {
  flex: 1;
  padding: 1.25rem 2rem;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Grid de Productos: 3 columnas x 2 filas fijas */
.products-grid-layout {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  grid-template-rows: repeat(2, minmax(0, 1fr));
  gap: 1.25rem;
  width: 100%;
  height: 100%;
}

.tv-product-card {
  background: linear-gradient(135deg, rgba(46, 36, 33, 0.75) 0%, rgba(26, 20, 18, 0.88) 100%);
  border: 1.5px solid rgba(226, 135, 67, 0.25);
  border-radius: 18px;
  padding: 1rem 1.15rem;
  display: flex;
  gap: 1rem;
  align-items: center;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
  overflow: hidden;
  min-width: 0;
}

.card-img-wrap {
  width: 120px;
  height: 120px;
  flex-shrink: 0;
  border-radius: 12px;
  overflow: hidden;
  position: relative;
  background: #15100e;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.badge-offer {
  position: absolute;
  top: 6px;
  left: 6px;
  background: var(--DC-pink, #d80056);
  color: #ffffff;
  font-size: 0.62rem;
  font-weight: 900;
  padding: 2px 6px;
  border-radius: 5px;
  letter-spacing: 0.04em;
}

.card-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  height: 100%;
  min-width: 0;
}

.card-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 6px;
}

.product-title {
  font-size: 1.2rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.category-pill {
  font-size: 0.64rem;
  font-weight: 800;
  color: var(--DC-orange, #e28743);
  text-transform: uppercase;
  background: rgba(226, 135, 67, 0.14);
  padding: 2px 7px;
  border-radius: 6px;
  white-space: nowrap;
}

.product-desc {
  font-size: 0.8rem;
  color: #cbd5e1;
  margin: 4px 0;
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
  font-size: 1.55rem;
  font-weight: 900;
  color: #fed7aa;
  letter-spacing: 0.02em;
}

.sizes-price-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.size-chip {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 6px;
  padding: 2px 8px;
  display: inline-flex;
  align-items: baseline;
  gap: 5px;
}

.size-name {
  font-size: 0.7rem;
  font-weight: 700;
  color: #cbd5e1;
  text-transform: uppercase;
}

.size-amount {
  font-size: 0.95rem;
  font-weight: 900;
  color: #fed7aa;
}

/* Vista de Banner / Promoción Full */
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
  max-height: calc(100vh - 180px);
  border-radius: 20px;
  overflow: hidden;
  position: relative;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.5);
  border: 2px solid rgba(226, 135, 67, 0.35);
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
  padding: 2rem 2.5rem;
  background: linear-gradient(to top, rgba(18, 14, 12, 0.92) 0%, transparent 100%);
}

.banner-overlay-text h2 {
  font-size: 2.2rem;
  font-weight: 900;
  color: #ffffff;
  margin: 0 0 6px 0;
}

.banner-overlay-text p {
  font-size: 1.15rem;
  color: #fed7aa;
  margin: 0;
}

/* ========================================================
   FOOTER
   ======================================================== */
.board-footer {
  height: 90px;
  background: rgba(22, 17, 15, 0.96);
  backdrop-filter: blur(12px);
  border-top: 2px solid rgba(226, 135, 67, 0.45);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 2rem;
  gap: 1.5rem;
  flex-shrink: 0;
}

/* QR Code Block */
.footer-qr-block {
  display: flex;
  align-items: center;
  gap: 12px;
  background: rgba(255, 255, 255, 0.05);
  padding: 6px 14px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  flex-shrink: 0;
}

.qr-code-box {
  width: 62px;
  height: 62px;
  border-radius: 8px;
  background: #ffffff;
  padding: 3px;
  display: grid;
  place-items: center;
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
  font-size: 0.85rem;
  font-weight: 900;
  color: #fed7aa;
  letter-spacing: 0.03em;
}

.qr-subtext {
  font-size: 0.7rem;
  color: #cbd5e1;
  font-weight: 600;
}

/* ========================================================
   MARQUESINA / TICKER FOOTER (CORREGIDO SIN SOLAPAMIENTO)
   ======================================================== */
.footer-ticker {
  flex: 1;
  position: relative;
  overflow: hidden;
  background: rgba(0, 0, 0, 0.4);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 999px;
  height: 44px;
  display: flex;
  align-items: center;
  min-width: 0;
}

/* Difuminados en bordes para entrada y salida suave */
.ticker-fade-left,
.ticker-fade-right {
  position: absolute;
  top: 0;
  bottom: 0;
  width: 32px;
  pointer-events: none;
  z-index: 2;
}

.ticker-fade-left {
  left: 0;
  background: linear-gradient(90deg, rgba(22, 17, 15, 0.95) 0%, transparent 100%);
}

.ticker-fade-right {
  right: 0;
  background: linear-gradient(270deg, rgba(22, 17, 15, 0.95) 0%, transparent 100%);
}

/* Riel con animación lineal acelerada por hardware */
.ticker-track {
  display: flex;
  width: max-content;
  will-change: transform;
  animation: ticker-continuous 30s linear infinite;
}

@keyframes ticker-continuous {
  0% {
    transform: translate3d(0, 0, 0);
  }
  100% {
    transform: translate3d(-50%, 0, 0);
  }
}

.ticker-content {
  display: flex;
  align-items: center;
  flex-shrink: 0;
  white-space: nowrap;
}

.ticker-item {
  display: inline-flex;
  align-items: center;
  font-size: 0.86rem;
  color: #e2e8f0;
  font-weight: 500;
  letter-spacing: 0.01em;
}

.ticker-item b {
  color: #fed7aa;
  font-weight: 800;
}

.ticker-separator {
  display: inline-flex;
  align-items: center;
  margin: 0 1.25rem;
  color: var(--DC-orange, #e28743);
  font-size: 0.78rem;
  opacity: 0.85;
}

.announcement-highlight {
  color: #38bdf8;
  font-weight: 700;
}

/* Controles de Slide */
.footer-slide-controls {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 6px;
  flex-shrink: 0;
  width: 160px;
}

.progress-bar-wrap {
  width: 100%;
  height: 5px;
  background: rgba(255, 255, 255, 0.12);
  border-radius: 999px;
  overflow: hidden;
}

.progress-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, var(--DC-orange, #e28743) 0%, #fed7aa 100%);
  transition: width 0.1s linear;
}

.dots-row {
  display: flex;
  align-items: center;
  gap: 6px;
}

.slide-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  padding: 0;
}

.slide-dot.active {
  width: 18px;
  border-radius: 4px;
  background: var(--DC-orange, #e28743);
}

.slide-counter {
  font-size: 0.72rem;
  font-weight: 800;
  color: #94a3b8;
  margin-left: 4px;
  font-variant-numeric: tabular-nums;
}

/* Transiciones entre diapositivas */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: opacity 0.4s ease, transform 0.4s ease;
}

.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(24px) scale(0.98);
}

.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-24px) scale(0.98);
}

/* Responsividad para pantallas pequeñas / vista previa web */
@media (max-width: 1200px) {
  .products-grid-layout {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    grid-template-rows: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .board-header {
    height: 68px;
    padding: 0 1rem;
  }
  .brand-name {
    font-size: 1.05rem;
  }
  .products-grid-layout {
    grid-template-columns: 1fr;
    grid-template-rows: auto;
    overflow-y: auto;
  }
  .board-footer {
    height: auto;
    flex-wrap: wrap;
    padding: 0.85rem 1rem;
  }
  .footer-ticker {
    order: 3;
    width: 100%;
  }
}
</style>