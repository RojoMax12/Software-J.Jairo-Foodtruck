<template>
  <div class="banners-admin-page">
    <header class="page-header">
      <div class="header-left">
        <h1>Gestión de Banners y Avisos</h1>
        <p>Personaliza las imágenes del carrusel principal y los anuncios dinámicos de la marquesina en tiempo real.</p>
      </div>

      <div class="header-tabs">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'banners' }"
          @click="activeTab = 'banners'"
        >
          <Images :size="18" />
          <span>Banners del Carrusel</span>
        </button>

        <button
          class="tab-btn"
          :class="{ active: activeTab === 'announcements' }"
          @click="activeTab = 'announcements'"
        >
          <Megaphone :size="18" />
          <span>Barra de Avisos</span>
        </button>
      </div>
    </header>

    <!-- ==================== SECCIÓN BANNERS ==================== -->
    <section v-if="activeTab === 'banners'" class="tab-content animate-fade-in">
      <div class="section-top-bar">
        <div>
          <h2>Banners del Carrusel ({{ banners.length }})</h2>
          <p class="subtitle">Arrastra o sube imágenes en formato WebP optimizado para la portada.</p>
        </div>

        <div class="top-actions">
          <button class="btn-secondary" @click="resetBanners">
            <RotateCcw :size="16" />
            <span>Restablecer Predeterminados</span>
          </button>
          <button class="btn-primary" @click="openCreateBannerModal">
            <Plus :size="18" />
            <span>Nuevo Banner</span>
          </button>
        </div>
      </div>

      <!-- Preview en vivo del Carrusel -->
      <div class="live-preview-box">
        <div class="preview-header">
          <span class="preview-tag"><Eye :size="14" /> Vista Previa del Carrusel</span>
          <div class="interval-control">
            <label>Auto-avance:</label>
            <select :value="autoPlayInterval" @change="handleIntervalChange">
              <option :value="3000">3 segundos</option>
              <option :value="5000">5 segundos (Recomendado)</option>
              <option :value="7000">7 segundos</option>
              <option :value="10000">10 segundos</option>
            </select>
          </div>
        </div>

        <div class="preview-carousel-wrapper">
          <div v-if="activeBannersList.length === 0" class="empty-preview">
            <p>No hay banners activos para mostrar.</p>
          </div>
          <div v-else class="preview-slide-container">
            <img :src="resolveImageUrl(activeBannersList[previewIndex]?.image)" class="preview-slide-img" />
            <div class="preview-slide-overlay">
              <h3>{{ activeBannersList[previewIndex]?.title }}</h3>
              <p v-if="activeBannersList[previewIndex]?.subtitle">{{ activeBannersList[previewIndex]?.subtitle }}</p>
            </div>
            <div class="preview-dots">
              <span
                v-for="(_, idx) in activeBannersList"
                :key="'dot-' + idx"
                class="p-dot"
                :class="{ active: previewIndex === idx }"
                @click="previewIndex = idx"
              ></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Banners -->
      <div class="banners-grid">
        <div
          v-for="(banner, index) in banners"
          :key="banner.id"
          class="banner-card"
          :class="{ inactive: !banner.active }"
        >
          <div class="banner-card-img">
            <img :src="resolveImageUrl(banner.image)" :alt="banner.title" />
            <span class="order-badge">#{{ index + 1 }}</span>
            <span class="status-pill" :class="banner.active ? 'status-active' : 'status-inactive'">
              {{ banner.active ? 'Activo' : 'Oculto' }}
            </span>
          </div>

          <div class="banner-card-body">
            <h4 class="banner-title">{{ banner.title }}</h4>
            <p v-if="banner.subtitle" class="banner-sub">{{ banner.subtitle }}</p>

            <div class="banner-actions">
              <label class="toggle-switch" title="Activar / Desactivar">
                <input
                  type="checkbox"
                  :checked="banner.active"
                  @change="toggleBannerActive(banner)"
                />
                <span class="slider"></span>
              </label>

              <button class="icon-btn" title="Editar" @click="openEditBannerModal(banner)">
                <Pencil :size="16" />
              </button>
              <button class="icon-btn delete-btn" title="Eliminar" @click="deleteBanner(banner.id)">
                <Trash2 :size="16" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== SECCIÓN AVISOS (MARQUEE) ==================== -->
    <section v-else class="tab-content animate-fade-in">
      <div class="section-top-bar">
        <div>
          <h2>Avisos de la Marquesina ({{ announcements.length }})</h2>
          <p class="subtitle">Mensajes rotativos que aparecen en la barra superior de la tienda.</p>
        </div>

        <div class="top-actions">
          <button class="btn-secondary" @click="resetAnnouncements">
            <RotateCcw :size="16" />
            <span>Restablecer Predeterminados</span>
          </button>
          <button class="btn-primary" @click="openCreateAnnouncementModal">
            <Plus :size="18" />
            <span>Nuevo Aviso</span>
          </button>
        </div>
      </div>

      <!-- Lista de Avisos -->
      <div class="announcements-list">
        <div
          v-for="ann in announcements"
          :key="ann.id"
          class="ann-card"
          :class="{ inactive: !ann.active }"
        >
          <div class="ann-badge-col">
            <span class="ann-badge" :class="'badge-' + ann.type">
              {{ ann.badge }}
            </span>
          </div>

          <div class="ann-content-col">
            <p class="ann-text">{{ ann.text }}</p>
            <span v-if="ann.highlight" class="ann-highlight">{{ ann.highlight }}</span>
          </div>

          <div class="ann-actions-col">
            <label class="toggle-switch" title="Activar / Desactivar">
              <input
                type="checkbox"
                :checked="ann.active"
                @change="toggleAnnouncementActive(ann)"
              />
              <span class="slider"></span>
            </label>

            <button class="icon-btn" title="Editar" @click="openEditAnnouncementModal(ann)">
              <Pencil :size="16" />
            </button>
            <button class="icon-btn delete-btn" title="Eliminar" @click="deleteAnnouncement(ann.id)">
              <Trash2 :size="16" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MODAL BANNER ==================== -->
    <div v-if="isBannerModalOpen" class="modal-backdrop" @click.self="isBannerModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>
            <Images :size="20" />
            <span>{{ isEditingBanner ? 'Editar Banner' : 'Nuevo Banner' }}</span>
          </h3>
          <button class="close-btn" @click="isBannerModalOpen = false"><X :size="20" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitBannerForm">
          <label class="modal-label">
            Título del Banner
            <input v-model="bannerForm.title" type="text" required placeholder="Ej: Nuevas Hamburguesas Dobles" class="modal-input" />
          </label>

          <label class="modal-label">
            Subtítulo (Opcional)
            <input v-model="bannerForm.subtitle" type="text" placeholder="Ej: Prueba nuestro pan casero recién horneado" class="modal-input" />
          </label>

          <div class="image-upload-section">
            <span class="section-title">Fotografía del Banner (.webp optimizado)</span>
            
            <div class="upload-options">
              <!-- Subir archivo local -->
              <div class="file-drop-area" @click="fileInputRef?.click()">
                <UploadCloud :size="30" class="upload-icon" />
                <span class="upload-title">{{ isConvertingWebP ? 'Convirtiendo a WebP...' : 'Haz clic para subir imagen' }}</span>
                <span class="upload-hint">PNG, JPG, JPEG o WEBP (se optimiza a .webp automáticamente)</span>
                <input
                  ref="fileInputRef"
                  type="file"
                  accept="image/*"
                  style="display: none"
                  @change="handleFileUpload"
                />
              </div>

              <!-- O pegar URL -->
              <div class="or-separator"><span>O bien</span></div>

              <label class="modal-label">
                Pegar URL de Imagen
                <input v-model="bannerForm.image" type="url" placeholder="https://ejemplo.com/banner.webp" class="modal-input" />
              </label>
            </div>

            <!-- Preview imagen -->
            <div v-if="bannerForm.image" class="banner-form-preview">
              <span class="preview-label">Previsualización:</span>
              <img :src="bannerForm.image" alt="Preview Banner" />
            </div>
          </div>

          <label class="checkbox-label">
            <input v-model="bannerForm.active" type="checkbox" />
            <span>Activar banner inmediatamente en la portada</span>
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isBannerModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="!bannerForm.image">
              {{ isEditingBanner ? 'Guardar Cambios' : 'Crear Banner' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL ANUNCIO ==================== -->
    <div v-if="isAnnModalOpen" class="modal-backdrop" @click.self="isAnnModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <h3>
            <Megaphone :size="20" />
            <span>{{ isEditingAnn ? 'Editar Aviso' : 'Nuevo Aviso' }}</span>
          </h3>
          <button class="close-btn" @click="isAnnModalOpen = false"><X :size="20" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitAnnouncementForm">
          <div class="grid-2-cols">
            <label class="modal-label">
              Texto del Badge
              <input v-model="annForm.badge" type="text" required placeholder="Ej: PROMO, HORARIO, NUEVO" class="modal-input" />
            </label>

            <label class="modal-label">
              Tipo de Badge
              <select v-model="annForm.type" class="modal-input">
                <option value="promo">Promo (Naranja)</option>
                <option value="schedule">Horario (Azul)</option>
                <option value="new">Nuevo (Verde)</option>
                <option value="payment">Medios de Pago (Morado)</option>
                <option value="info">Informativo (Blanco/Gris)</option>
              </select>
            </label>
          </div>

          <label class="modal-label">
            Texto Principal del Aviso
            <textarea v-model="annForm.text" required rows="2" placeholder="Ej: ¡2x1 en Churrascos todos los martes!" class="modal-input"></textarea>
          </label>

          <label class="modal-label">
            Texto Destacado (Opcional)
            <input v-model="annForm.highlight" type="text" placeholder="Ej: 🔥 Solo por hoy" class="modal-input" />
          </label>

          <label class="checkbox-label">
            <input v-model="annForm.active" type="checkbox" />
            <span>Mostrar aviso en la marquesina</span>
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isAnnModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save">
              {{ isEditingAnn ? 'Guardar Aviso' : 'Crear Aviso' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
  Images, Megaphone, Plus, Pencil, Trash2, RotateCcw, 
  Eye, X, UploadCloud 
} from 'lucide-vue-next';
import { useMarketingConfig, type BannerItem, type AnnouncementItem } from '@/composables/useMarketingConfig';
import { useImageOptimizer } from '@/composables/useImageOptimizer';
import { useNotification } from '@/composables/useNotification';

const { 
  banners, announcements, autoPlayInterval,
  addBanner, updateBanner, removeBanner, resetDefaultBanners,
  addAnnouncement, updateAnnouncement, removeAnnouncement, resetDefaultAnnouncements,
  setIntervalMs, resolveImageUrl
} = useMarketingConfig();

const { convertToWebP, getPreviewUrl } = useImageOptimizer();
const { notify } = useNotification();

const activeTab = ref<'banners' | 'announcements'>('banners');

// Preview carousel loop
const previewIndex = ref(0);
let previewTimer: any = null;

const activeBannersList = computed(() => banners.value.filter(b => b.active));

const startPreviewLoop = () => {
  if (previewTimer) clearInterval(previewTimer);
  previewTimer = setInterval(() => {
    if (activeBannersList.value.length > 0) {
      previewIndex.value = (previewIndex.value + 1) % activeBannersList.value.length;
    }
  }, autoPlayInterval.value);
};

onMounted(() => {
  startPreviewLoop();
});

onUnmounted(() => {
  if (previewTimer) clearInterval(previewTimer);
});

const handleIntervalChange = (e: Event) => {
  const val = Number((e.target as HTMLSelectElement).value);
  setIntervalMs(val);
  startPreviewLoop();
  notify(`Auto-avance fijado en ${val / 1000}s`, 'success');
};

// Modales Banners
const isBannerModalOpen = ref(false);
const isEditingBanner = ref(false);
const editingBannerId = ref('');
const fileInputRef = ref<HTMLInputElement | null>(null);
const isConvertingWebP = ref(false);

const bannerForm = ref({
  title: '',
  subtitle: '',
  image: '',
  active: true
});

const openCreateBannerModal = () => {
  isEditingBanner.value = false;
  editingBannerId.value = '';
  bannerForm.value = {
    title: '',
    subtitle: '',
    image: '',
    active: true
  };
  isBannerModalOpen.value = true;
};

const openEditBannerModal = (b: BannerItem) => {
  isEditingBanner.value = true;
  editingBannerId.value = b.id;
  bannerForm.value = {
    title: b.title,
    subtitle: b.subtitle || '',
    image: b.image,
    active: b.active
  };
  isBannerModalOpen.value = true;
};

const handleFileUpload = async (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (!target.files || target.files.length === 0) return;

  const file = target.files[0];
  if (!file) return;
  isConvertingWebP.value = true;
  try {
    const webpFile = await convertToWebP(file, `banner_${Date.now()}.webp`, { maxWidth: 1400, quality: 0.88 });
    const previewDataUrl = await getPreviewUrl(webpFile);
    bannerForm.value.image = previewDataUrl;
    notify('Imagen convertida a formato WebP optimizado', 'success');
  } catch (err) {
    console.error('Error procesando imagen WebP:', err);
    notify('Error al procesar la imagen', 'warning');
  } finally {
    isConvertingWebP.value = false;
  }
};

const submitBannerForm = () => {
  if (!bannerForm.value.image) return;

  if (isEditingBanner.value) {
    updateBanner(editingBannerId.value, {
      title: bannerForm.value.title,
      subtitle: bannerForm.value.subtitle,
      image: bannerForm.value.image,
      active: bannerForm.value.active
    });
    notify('Banner actualizado correctamente', 'success');
  } else {
    addBanner({
      title: bannerForm.value.title,
      subtitle: bannerForm.value.subtitle,
      image: bannerForm.value.image,
      active: bannerForm.value.active
    });
    notify('Nuevo banner añadido al carrusel', 'success');
  }
  isBannerModalOpen.value = false;
};

const toggleBannerActive = (banner: BannerItem) => {
  updateBanner(banner.id, { active: !banner.active });
  notify(`Banner ${!banner.active ? 'activado' : 'desactivado'}`, 'success');
};

const deleteBanner = (id: string) => {
  if (confirm('¿Eliminar este banner del carrusel?')) {
    removeBanner(id);
    notify('Banner eliminado', 'warning');
  }
};

const resetBanners = () => {
  if (confirm('¿Restablecer los banners originales del sistema?')) {
    resetDefaultBanners();
    notify('Banners restablecidos', 'success');
  }
};

// Modales Anuncios
const isAnnModalOpen = ref(false);
const isEditingAnn = ref(false);
const editingAnnId = ref('');

const annForm = ref<Omit<AnnouncementItem, 'id'>>({
  badge: 'PROMO',
  type: 'promo',
  text: '',
  highlight: '',
  active: true
});

const openCreateAnnouncementModal = () => {
  isEditingAnn.value = false;
  editingAnnId.value = '';
  annForm.value = {
    badge: 'PROMO',
    type: 'promo',
    text: '',
    highlight: '',
    active: true
  };
  isAnnModalOpen.value = true;
};

const openEditAnnouncementModal = (ann: AnnouncementItem) => {
  isEditingAnn.value = true;
  editingAnnId.value = ann.id;
  annForm.value = {
    badge: ann.badge,
    type: ann.type,
    text: ann.text,
    highlight: ann.highlight || '',
    active: ann.active
  };
  isAnnModalOpen.value = true;
};

const submitAnnouncementForm = () => {
  if (isEditingAnn.value) {
    updateAnnouncement(editingAnnId.value, { ...annForm.value });
    notify('Aviso actualizado', 'success');
  } else {
    addAnnouncement({ ...annForm.value });
    notify('Nuevo aviso añadido a la marquesina', 'success');
  }
  isAnnModalOpen.value = false;
};

const toggleAnnouncementActive = (ann: AnnouncementItem) => {
  updateAnnouncement(ann.id, { active: !ann.active });
  notify(`Aviso ${!ann.active ? 'activado' : 'desactivado'}`, 'success');
};

const deleteAnnouncement = (id: string) => {
  if (confirm('¿Eliminar este aviso de la marquesina?')) {
    removeAnnouncement(id);
    notify('Aviso eliminado', 'warning');
  }
};

const resetAnnouncements = () => {
  if (confirm('¿Restablecer los avisos originales del sistema?')) {
    resetDefaultAnnouncements();
    notify('Avisos restablecidos', 'success');
  }
};
</script>

<style scoped>
.banners-admin-page {
  padding: 30px;
  max-width: 1350px;
  margin: 0 auto;
  font-family: var(--font-main, sans-serif);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 20px;
  margin-bottom: 25px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 20px;
}

.header-left h1 {
  font-size: 1.8rem;
  font-weight: 900;
  color: #513119;
  margin: 0 0 6px 0;
}

.header-left p {
  color: #64748b;
  margin: 0;
  font-size: 0.95rem;
}

.header-tabs {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 12px;
  gap: 4px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 20px;
  border: none;
  background: transparent;
  color: #64748b;
  font-weight: 800;
  font-size: 0.88rem;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: #513119;
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(81, 49, 25, 0.25);
}

.section-top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 15px;
}

.section-top-bar h2 {
  margin: 0 0 4px 0;
  color: #1e293b;
  font-size: 1.35rem;
}

.subtitle {
  color: #64748b;
  margin: 0;
  font-size: 0.88rem;
}

.top-actions {
  display: flex;
  gap: 12px;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #e28743;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: #d3732c;
  transform: translateY(-1px);
}

.btn-secondary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #ffffff;
  color: #513119;
  border: 1.5px solid #cbd5e1;
  padding: 10px 18px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary:hover {
  background: #f8fafc;
  border-color: #513119;
}

/* Live preview */
.live-preview-box {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  margin-bottom: 30px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 14px;
}

.preview-tag {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 800;
  font-size: 0.82rem;
  color: #513119;
  text-transform: uppercase;
  background: #f4e1d2;
  padding: 4px 10px;
  border-radius: 999px;
}

.interval-control {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 700;
  color: #475569;
}

.interval-control select {
  padding: 5px 10px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  font-weight: 600;
}

.preview-carousel-wrapper {
  position: relative;
  width: 100%;
  height: 220px;
  border-radius: 12px;
  overflow: hidden;
  background: #0f172a;
}

.preview-slide-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.preview-slide-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-slide-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 16px 24px;
  background: linear-gradient(transparent, rgba(0, 0, 0, 0.75));
  color: white;
}

.preview-slide-overlay h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 900;
}

.preview-slide-overlay p {
  margin: 4px 0 0 0;
  font-size: 0.85rem;
  color: #cbd5e1;
}

.preview-dots {
  position: absolute;
  bottom: 12px;
  right: 20px;
  display: flex;
  gap: 6px;
}

.p-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
}

.p-dot.active {
  background: #e28743;
  transform: scale(1.2);
}

.empty-preview {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100%;
  color: #94a3b8;
}

/* Grid de banners */
.banners-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.banner-card {
  background: #ffffff;
  border-radius: 16px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
}

.banner-card.inactive {
  opacity: 0.65;
  filter: grayscale(40%);
}

.banner-card-img {
  position: relative;
  width: 100%;
  height: 160px;
  background: #0f172a;
}

.banner-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.order-badge {
  position: absolute;
  top: 10px;
  left: 10px;
  background: rgba(0, 0, 0, 0.75);
  color: white;
  font-weight: 800;
  font-size: 0.75rem;
  padding: 3px 8px;
  border-radius: 6px;
}

.status-pill {
  position: absolute;
  top: 10px;
  right: 10px;
  font-weight: 800;
  font-size: 0.72rem;
  padding: 3px 8px;
  border-radius: 999px;
  text-transform: uppercase;
}

.status-active {
  background: #dcfce7;
  color: #166534;
}

.status-inactive {
  background: #f1f5f9;
  color: #475569;
}

.banner-card-body {
  padding: 16px;
}

.banner-title {
  margin: 0 0 4px 0;
  font-size: 1rem;
  font-weight: 800;
  color: #1e293b;
}

.banner-sub {
  margin: 0 0 14px 0;
  font-size: 0.84rem;
  color: #64748b;
}

.banner-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 10px;
}

/* Anuncios */
.announcements-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.ann-card {
  display: grid;
  grid-template-columns: 140px 1fr 120px;
  align-items: center;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 20px;
  gap: 16px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.ann-card.inactive {
  opacity: 0.6;
}

.ann-badge {
  display: inline-flex;
  align-items: center;
  font-size: 0.7rem;
  font-weight: 900;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 999px;
}

.badge-promo { background: #ffedd5; color: #c2410c; }
.badge-schedule { background: #dbeafe; color: #1d4ed8; }
.badge-new { background: #d1fae5; color: #047857; }
.badge-payment { background: #f3e8ff; color: #7e22ce; }
.badge-info { background: #f1f5f9; color: #334155; }

.ann-content-col {
  display: flex;
  align-items: center;
  gap: 10px;
}

.ann-text {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #1e293b;
}

.ann-highlight {
  font-size: 0.78rem;
  font-weight: 800;
  background: #fef3c7;
  color: #92400e;
  padding: 2px 8px;
  border-radius: 6px;
}

.ann-actions-col {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

.icon-btn {
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  cursor: pointer;
  transition: all 0.2s ease;
}

.icon-btn:hover {
  background: #f1f5f9;
  color: #513119;
}

.icon-btn.delete-btn:hover {
  background: #fee2e2;
  color: #dc2626;
  border-color: #fca5a5;
}

/* Switch */
.toggle-switch {
  position: relative;
  display: inline-block;
  width: 40px;
  height: 22px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0; left: 0; right: 0; bottom: 0;
  background-color: #cbd5e1;
  transition: .2s;
  border-radius: 22px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 16px;
  width: 16px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: .2s;
  border-radius: 50%;
}

input:checked + .slider {
  background-color: #e28743;
}

input:checked + .slider:before {
  transform: translateX(18px);
}

/* Modales */
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  z-index: 2000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.modal-card {
  background: white;
  width: 100%;
  max-width: 540px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
  padding: 18px 24px;
  background: #513119;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.15rem;
  display: flex;
  align-items: center;
  gap: 8px;
}

.close-btn {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-weight: 700;
  font-size: 0.88rem;
  color: #334155;
}

.modal-input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
  font-size: 0.9rem;
}

.grid-2-cols {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.upload-options {
  border: 1.5px dashed #cbd5e1;
  border-radius: 12px;
  padding: 16px;
  text-align: center;
  background: #f8fafc;
}

.file-drop-area {
  cursor: pointer;
  padding: 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 6px;
}

.file-drop-area:hover {
  background: #f1f5f9;
  border-radius: 8px;
}

.upload-icon {
  color: #e28743;
}

.upload-title {
  font-weight: 800;
  color: #1e293b;
  font-size: 0.9rem;
}

.upload-hint {
  font-size: 0.76rem;
  color: #64748b;
}

.or-separator {
  margin: 10px 0;
  position: relative;
  text-align: center;
}

.or-separator span {
  background: #f8fafc;
  padding: 0 10px;
  font-size: 0.75rem;
  color: #94a3b8;
  font-weight: 700;
}

.banner-form-preview {
  margin-top: 10px;
  border-radius: 8px;
  overflow: hidden;
  max-height: 140px;
}

.banner-form-preview img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 8px;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
  cursor: pointer;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn-cancel {
  padding: 10px 18px;
  border: 1px solid #cbd5e1;
  background: white;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.btn-save {
  padding: 10px 22px;
  background: #e28743;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}

.btn-save:hover {
  background: #d3732c;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .banners-admin-page {
    padding: 15px;
  }
  
  .ann-card {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .ann-actions-col {
    justify-content: flex-start;
  }
}
</style>
