import { ref, computed } from 'vue';
import api from '@/services/api';
import imgBanner1 from '@/assets/banner1.webp';
import imgBanner2 from '@/assets/banner2.webp';
import imgBanner3 from '@/assets/banner3.webp';

export interface BannerItem {
  id: string;
  title: string;
  subtitle?: string;
  image: string; // URL o base64
  active: boolean;
  order: number;
}

export interface AnnouncementItem {
  id: string;
  badge: string;
  type: 'promo' | 'schedule' | 'new' | 'payment' | 'info';
  text: string;
  highlight?: string;
  active: boolean;
}

const STORAGE_KEY_BANNERS = 'foodtruck_marketing_banners_v1';
const STORAGE_KEY_ANNOUNCEMENTS = 'foodtruck_marketing_announcements_v1';
const STORAGE_KEY_INTERVAL = 'foodtruck_marketing_carousel_interval_v1';

const defaultBanners: BannerItem[] = [
  {
    id: 'banner-1',
    title: 'Nuestras Mejores Vianesas y Completos XXL',
    subtitle: 'El auténtico sabor tradicional de J.Junior',
    image: imgBanner1,
    active: true,
    order: 1,
  },
  {
    id: 'banner-2',
    title: 'Hamburguesas y Churrascos Premium',
    subtitle: 'Carne 100% casera y pan fresco',
    image: imgBanner2,
    active: true,
    order: 2,
  },
  {
    id: 'banner-3',
    title: 'Pizzas Artesanales y Sánguches',
    subtitle: 'Pide online y retira al instante sin filas',
    image: imgBanner3,
    active: true,
    order: 3,
  },
];

const defaultAnnouncements: AnnouncementItem[] = [
  {
    id: 'ann-1',
    badge: 'PEDIDO ONLINE',
    type: 'promo',
    text: '¡Haz tu pedido en la web y retira directo en el foodtruck sin filas!',
    highlight: '🌭 Retiro Rápido',
    active: true,
  },
  {
    id: 'ann-2',
    badge: 'HORARIOS',
    type: 'schedule',
    text: 'Atención de Lunes a Domingo de 19:00 a 00:30 hrs.',
    highlight: '🔥 ¡Abierto hoy!',
    active: true,
  },
  {
    id: 'ann-3',
    badge: 'NUEVA CARTA',
    type: 'new',
    text: 'Prueba nuestras Hamburguesas Caseras XXL y Churrascos Premium.',
    highlight: '🍔 100% Casero',
    active: true,
  },
  {
    id: 'ann-4',
    badge: 'ESTADO EN VIVO',
    type: 'info',
    text: 'Monitorea tu pedido en tiempo real desde la sección "Revisa tu pedido".',
    highlight: '⚡ En vivo',
    active: true,
  },
  {
    id: 'ann-5',
    badge: 'MEDIOS DE PAGO',
    type: 'payment',
    text: 'Aceptamos Efectivo, Tarjeta de Débito, Crédito y Transferencia directa.',
    highlight: '💳 Pago Fácil',
    active: true,
  },
];

// Estado reactivo singleton
const banners = ref<BannerItem[]>([...defaultBanners]);
const announcements = ref<AnnouncementItem[]>([...defaultAnnouncements]);
const autoPlayInterval = ref<number>(5000);
let isInitialized = false;

async function fetchFromBackend() {
  try {
    const res = await api.get('/public/marketing');
    const data = res?.data?.data || res?.data;
    if (data) {
      if (Array.isArray(data.banners) && data.banners.length > 0) {
        banners.value = data.banners;
        localStorage.setItem(STORAGE_KEY_BANNERS, JSON.stringify(data.banners));
      }
      if (Array.isArray(data.announcements) && data.announcements.length > 0) {
        announcements.value = data.announcements;
        localStorage.setItem(STORAGE_KEY_ANNOUNCEMENTS, JSON.stringify(data.announcements));
      }
      if (data.autoPlayInterval) {
        autoPlayInterval.value = Number(data.autoPlayInterval);
        localStorage.setItem(STORAGE_KEY_INTERVAL, String(data.autoPlayInterval));
      }
    }
  } catch (err) {
    loadFromLocalStorage();
  }
}

function loadFromLocalStorage() {
  try {
    const rawB = localStorage.getItem(STORAGE_KEY_BANNERS);
    if (rawB) banners.value = JSON.parse(rawB);
  } catch {}

  try {
    const rawA = localStorage.getItem(STORAGE_KEY_ANNOUNCEMENTS);
    if (rawA) announcements.value = JSON.parse(rawA);
  } catch {}

  try {
    const rawI = localStorage.getItem(STORAGE_KEY_INTERVAL);
    if (rawI) autoPlayInterval.value = Number(rawI) || 5000;
  } catch {}
}

async function syncToBackend(): Promise<void> {
  const prevBanners = localStorage.getItem(STORAGE_KEY_BANNERS);
  const prevAnnouncements = localStorage.getItem(STORAGE_KEY_ANNOUNCEMENTS);
  const prevInterval = localStorage.getItem(STORAGE_KEY_INTERVAL);

  try {
    const res = await api.post('/marketing', {
      banners: banners.value,
      announcements: announcements.value,
      autoPlayInterval: autoPlayInterval.value
    });
    const saved = res?.data?.data;
    if (saved) {
      if (Array.isArray(saved.banners)) banners.value = saved.banners;
      if (Array.isArray(saved.announcements)) announcements.value = saved.announcements;
      if (saved.autoPlayInterval) autoPlayInterval.value = Number(saved.autoPlayInterval);
    }
    localStorage.setItem(STORAGE_KEY_BANNERS, JSON.stringify(banners.value));
    localStorage.setItem(STORAGE_KEY_ANNOUNCEMENTS, JSON.stringify(announcements.value));
    localStorage.setItem(STORAGE_KEY_INTERVAL, String(autoPlayInterval.value));
    window.dispatchEvent(new Event('foodtruck-marketing-update'));
  } catch (err) {
    if (prevBanners) {
      try { banners.value = JSON.parse(prevBanners); } catch {}
    }
    if (prevAnnouncements) {
      try { announcements.value = JSON.parse(prevAnnouncements); } catch {}
    }
    if (prevInterval) {
      autoPlayInterval.value = Number(prevInterval) || 5000;
    }
    throw err;
  }
}

function saveBanners(): Promise<void> {
  return syncToBackend();
}

function saveAnnouncements(): Promise<void> {
  return syncToBackend();
}

function saveInterval(): Promise<void> {
  return syncToBackend();
}

export function useMarketingConfig() {
  if (!isInitialized) {
    loadFromLocalStorage();
    fetchFromBackend();
    if (typeof window !== 'undefined') {
      window.addEventListener('storage', loadFromLocalStorage);
      window.addEventListener('foodtruck-marketing-update', loadFromLocalStorage);
    }
    isInitialized = true;
  }

  // --- MÉTODOS PARA BANNERS ---
  function addBanner(banner: Omit<BannerItem, 'id' | 'order'>): Promise<void> {
    const newId = 'banner-' + Date.now();
    const newOrder = banners.value.length + 1;
    const item: BannerItem = {
      ...banner,
      id: newId,
      order: newOrder,
    };
    banners.value.push(item);
    return saveBanners();
  }

  function updateBanner(id: string, updated: Partial<BannerItem>): Promise<void> {
    const idx = banners.value.findIndex(b => b.id === id);
    if (idx !== -1) {
      const current = banners.value[idx];
      if (current) {
        banners.value[idx] = {
          id: current.id,
          title: updated.title ?? current.title,
          subtitle: updated.subtitle ?? current.subtitle,
          image: updated.image ?? current.image,
          active: updated.active ?? current.active,
          order: updated.order ?? current.order,
        };
        return saveBanners();
      }
    }
    return Promise.resolve();
  }

  function deleteBanner(id: string): Promise<void> {
    banners.value = banners.value.filter(b => b.id !== id);
    banners.value.forEach((b, i) => {
      b.order = i + 1;
    });
    return saveBanners();
  }

  function removeBanner(id: string): Promise<void> {
    return deleteBanner(id);
  }

  function toggleBannerActive(id: string): Promise<void> {
    const banner = banners.value.find(b => b.id === id);
    if (banner) {
      banner.active = !banner.active;
      return saveBanners();
    }
    return Promise.resolve();
  }

  function reorderBanners(newBanners: BannerItem[]): Promise<void> {
    newBanners.forEach((b, i) => {
      b.order = i + 1;
    });
    banners.value = [...newBanners];
    return saveBanners();
  }

  function resetDefaultBanners(): Promise<void> {
    banners.value = [...defaultBanners];
    return saveBanners();
  }

  // --- MÉTODOS PARA ANUNCIOS ---
  function addAnnouncement(ann: Omit<AnnouncementItem, 'id'>): Promise<void> {
    const newId = 'ann-' + Date.now();
    const item: AnnouncementItem = {
      ...ann,
      id: newId,
    };
    announcements.value.push(item);
    return saveAnnouncements();
  }

  function updateAnnouncement(id: string, updated: Partial<AnnouncementItem>): Promise<void> {
    const idx = announcements.value.findIndex(a => a.id === id);
    if (idx !== -1) {
      const current = announcements.value[idx];
      if (current) {
        announcements.value[idx] = {
          id: current.id,
          badge: updated.badge ?? current.badge,
          type: updated.type ?? current.type,
          text: updated.text ?? current.text,
          highlight: updated.highlight ?? current.highlight,
          active: updated.active ?? current.active,
        };
        return saveAnnouncements();
      }
    }
    return Promise.resolve();
  }

  function deleteAnnouncement(id: string): Promise<void> {
    announcements.value = announcements.value.filter(a => a.id !== id);
    return saveAnnouncements();
  }

  function removeAnnouncement(id: string): Promise<void> {
    return deleteAnnouncement(id);
  }

  function toggleAnnouncementActive(id: string): Promise<void> {
    const ann = announcements.value.find(a => a.id === id);
    if (ann) {
      ann.active = !ann.active;
      return saveAnnouncements();
    }
    return Promise.resolve();
  }

  function resetDefaultAnnouncements(): Promise<void> {
    announcements.value = [...defaultAnnouncements];
    return saveAnnouncements();
  }

  function setIntervalSeconds(seconds: number) {
    autoPlayInterval.value = seconds * 1000;
    saveInterval();
  }

  function setIntervalMs(ms: number) {
    autoPlayInterval.value = ms;
    saveInterval();
  }

  function resetToDefaults() {
    banners.value = [...defaultBanners];
    announcements.value = [...defaultAnnouncements];
    autoPlayInterval.value = 5000;
    syncToBackend();
  }

  const activeBanners = () => banners.value.filter(b => b.active).sort((a, b) => a.order - b.order);
  const activeAnnouncements = () => announcements.value.filter(a => a.active);

  function resolveImageUrl(imagePath?: string): string {
    if (!imagePath) return imgBanner1;
    if (imagePath.startsWith('data:') || imagePath.startsWith('http://') || imagePath.startsWith('https://')) {
      return imagePath;
    }
    if (imagePath.includes('banner1')) return imgBanner1;
    if (imagePath.includes('banner2')) return imgBanner2;
    if (imagePath.includes('banner3')) return imgBanner3;

    const normalized = imagePath.replace(/^\/?storage\/?/, '');
    if (normalized !== imagePath || imagePath.startsWith('storage/') || imagePath.startsWith('/storage/')) {
      const backendUrl = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api\/?$/, '');
      return `${backendUrl}/storage/${normalized}`;
    }

    return imagePath;
  }

  return {
    banners,
    announcements,
    autoPlayInterval,
    activeBanners,
    activeAnnouncements,
    addBanner,
    updateBanner,
    deleteBanner,
    removeBanner,
    toggleBannerActive,
    reorderBanners,
    resetDefaultBanners,
    addAnnouncement,
    updateAnnouncement,
    deleteAnnouncement,
    removeAnnouncement,
    toggleAnnouncementActive,
    resetDefaultAnnouncements,
    setIntervalSeconds,
    setIntervalMs,
    resetToDefaults,
    resolveImageUrl,
    refreshFromBackend: fetchFromBackend,
  };
}
