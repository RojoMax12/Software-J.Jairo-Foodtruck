<template>
  <div class="banners-admin-page">
    <!-- ===================== HEADER ===================== -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Gestión de Banners y Avisos</h1>
        <p>Configura las imágenes del carrusel principal y los mensajes promocionales de la marquesina.</p>
      </div>

      <div class="header-actions">
        <button 
          v-if="activeTab === 'banners'" 
          class="btn-secondary" 
          @click="resetBanners"
          title="Restablecer banners de fábrica"
        >
          <RotateCcw :size="16" />
          <span>Restablecer</span>
        </button>
        <button 
          v-else 
          class="btn-secondary" 
          @click="resetAnnouncements"
          title="Restablecer avisos de fábrica"
        >
          <RotateCcw :size="16" />
          <span>Restablecer</span>
        </button>

        <button 
          v-if="activeTab === 'banners'" 
          class="btn-primary" 
          @click="openCreateBannerModal"
        >
          <Plus :size="18" />
          <span>Nuevo Banner</span>
        </button>
        <button 
          v-else 
          class="btn-primary" 
          @click="openCreateAnnouncementModal"
        >
          <Plus :size="18" />
          <span>Nuevo Aviso</span>
        </button>
      </div>
    </header>

    <!-- ===================== PESTAÑAS SEGMENTADAS ===================== -->
    <div class="inventory-tabs-nav">
      <button
        type="button"
        class="tab-nav-btn"
        :class="{ active: activeTab === 'banners' }"
        @click="activeTab = 'banners'"
      >
        <Images :size="17" class="tab-icon" />
        <span class="tab-text">Banners del Carrusel</span>
        <span class="tab-pill">{{ banners.length }}</span>
      </button>

      <button
        type="button"
        class="tab-nav-btn"
        :class="{ active: activeTab === 'announcements' }"
        @click="activeTab = 'announcements'"
      >
        <Megaphone :size="17" class="tab-icon" />
        <span class="tab-text">Barra de Avisos</span>
        <span class="tab-pill">{{ announcements.length }}</span>
      </button>
    </div>

    <!-- ===================== RESUMEN EN KPIS (SUMMARY-GRID) ===================== -->
    <section class="summary-grid">
      <template v-if="activeTab === 'banners'">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <Images :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Banners</span>
            <strong class="summary-value">{{ banners.length }}</strong>
            <p class="summary-helper">Imágenes en configuración</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <Eye :size="22" />
          </div>
          <div>
            <span class="summary-label">Banners Activos</span>
            <strong class="summary-value text-status-open">{{ activeBannersList.length }}</strong>
            <p class="summary-helper">Visibles en la portada</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <Clock :size="22" />
          </div>
          <div>
            <span class="summary-label">Auto-Avance</span>
            <strong class="summary-value text-orange">{{ autoPlayInterval / 1000 }}s</strong>
            <p class="summary-helper">Velocidad de rotación actual</p>
          </div>
        </article>
      </template>

      <template v-else>
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <Megaphone :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Avisos</span>
            <strong class="summary-value">{{ announcements.length }}</strong>
            <p class="summary-helper">Mensajes en rotación</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <Eye :size="22" />
          </div>
          <div>
            <span class="summary-label">Avisos Activos</span>
            <strong class="summary-value text-status-open">{{ activeAnnouncementsCount }}</strong>
            <p class="summary-helper">Mostrándose en marquesina</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-pink">
            <Sparkles :size="22" />
          </div>
          <div>
            <span class="summary-label">Destacados</span>
            <strong class="summary-value text-pink">{{ highlightedAnnouncementsCount }}</strong>
            <p class="summary-helper">Con texto especial resaltado</p>
          </div>
        </article>
      </template>
    </section>

    <!-- ==================== SECCIÓN 1: BANNERS ==================== -->
    <section v-if="activeTab === 'banners'" class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <span class="panel-section-title">
            <Images :size="16" />
            <span>Galería del Carrusel Principal</span>
          </span>
        </div>

        <div class="toolbar-right">
          <div class="interval-control-group">
            <label>Velocidad:</label>
            <div class="select-box">
              <select :value="autoPlayInterval" @change="handleIntervalChange">
                <option :value="3000">3 segundos</option>
                <option :value="5000">5 segundos (Recomendado)</option>
                <option :value="7000">7 segundos</option>
                <option :value="10000">10 segundos</option>
              </select>
            </div>
          </div>
          <span class="results-chip">{{ banners.length }} banners</span>
        </div>
      </div>

      <!-- Preview interactiva del carrusel -->
      <div class="preview-container-block">
        <div class="preview-top-row">
          <span class="preview-badge">
            <Eye :size="13" />
            <span>Previsualización en Portada</span>
          </span>
          <span class="live-pill">En vivo</span>
        </div>

        <div class="preview-carousel-screen">
          <div v-if="activeBannersList.length === 0" class="empty-preview-state">
            <Images :size="38" />
            <p>No hay banners activos para mostrar en el carrusel.</p>
          </div>
          <div v-else class="preview-slide-container">
            <img :src="resolveImageUrl(activeBannersList[previewIndex]?.image)" class="preview-slide-img" alt="Banner Preview" />
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

      <!-- Cuadrícula de gestión de banners -->
      <div class="banners-grid-wrapper">
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
              <div class="banner-card-info">
                <h4 class="banner-title">{{ banner.title }}</h4>
                <p v-if="banner.subtitle" class="banner-sub">{{ banner.subtitle }}</p>
              </div>

              <div class="banner-card-actions">
                <label class="status-switch" :class="banner.active ? 'active' : 'inactive'" title="Mostrar / Ocultar en carrusel">
                  <input
                    type="checkbox"
                    :checked="banner.active"
                    @change="toggleBannerActive(banner)"
                  />
                  <span class="slider"></span>
                  <span class="status-text">{{ banner.active ? 'Activo' : 'Oculto' }}</span>
                </label>

                <div class="actions">
                  <button class="icon-button edit-action" title="Editar banner" @click="openEditBannerModal(banner)">
                    <Pencil :size="15" />
                  </button>
                  <button class="icon-button delete-btn" title="Eliminar banner" @click="deleteBanner(banner.id)">
                    <Trash2 :size="15" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== SECCIÓN 2: AVISOS ==================== -->
    <section v-else class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <span class="panel-section-title">
            <Megaphone :size="16" />
            <span>Avisos de la Marquesina Superior</span>
          </span>
        </div>

        <div class="toolbar-right">
          <span class="results-chip">{{ announcements.length }} avisos</span>
        </div>
      </div>

      <!-- Lista de avisos estructurada -->
      <div class="announcements-wrapper">
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
              <label class="status-switch" :class="ann.active ? 'active' : 'inactive'" title="Activar / Desactivar">
                <input
                  type="checkbox"
                  :checked="ann.active"
                  @change="toggleAnnouncementActive(ann)"
                />
                <span class="slider"></span>
                <span class="status-text">{{ ann.active ? 'Activo' : 'Oculto' }}</span>
              </label>

              <div class="actions">
                <button class="icon-button edit-action" title="Editar aviso" @click="openEditAnnouncementModal(ann)">
                  <Pencil :size="15" />
                </button>
                <button class="icon-button delete-btn" title="Eliminar aviso" @click="deleteAnnouncement(ann.id)">
                  <Trash2 :size="15" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MODAL BANNER ==================== -->
    <div v-if="isBannerModalOpen" class="modal-backdrop" @click.self="isBannerModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><Images :size="18" /></div>
            <div>
              <h3>{{ isEditingBanner ? 'Editar Banner' : 'Nuevo Banner' }}</h3>
              <p class="modal-header-desc">Configura título, subtítulo e imagen de alta resolución</p>
            </div>
          </div>
          <button class="close-btn" @click="isBannerModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitBannerForm">
          <label class="modal-label">
            <span>Título del Banner <span class="required">*</span></span>
            <input v-model="bannerForm.title" type="text" required placeholder="Ej: Nuevas Hamburguesas Smash" class="modal-input" />
          </label>

          <label class="modal-label">
            <span>Subtítulo (Opcional)</span>
            <input v-model="bannerForm.subtitle" type="text" placeholder="Ej: Con pan brioche artesanal recién horneado" class="modal-input" />
          </label>

          <div class="image-upload-box">
            <span class="sub-legend">Fotografía del Banner</span>
            
            <div class="dropzone-area" @click="fileInputRef?.click()">
              <UploadCloud :size="24" class="upload-icon" />
              <div class="dropzone-text">
                <strong>{{ isConvertingWebP ? 'Optimizando a WebP...' : 'Haz clic para subir imagen' }}</strong>
                <span>Formato recomendado en proporción 16:9 o panorámica</span>
              </div>
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                style="display: none"
                @change="handleFileUpload"
              />
            </div>

            <div class="url-input-wrapper">
              <input 
                v-model="bannerForm.image" 
                type="url" 
                placeholder="O pegar URL directa de imagen..." 
                class="modal-input" 
              />
            </div>

            <!-- Preview imagen -->
            <div v-if="bannerForm.image" class="banner-form-preview">
              <img :src="bannerForm.image" alt="Preview Banner" />
            </div>
          </div>

          <label class="toggle-availability-label">
            <div class="toggle-text">
              <strong>Activar inmediatamente</strong>
              <span>Visible en el carrusel de inicio</span>
            </div>
            <input type="checkbox" v-model="bannerForm.active" class="modern-toggle" />
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isBannerModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save" :disabled="!bannerForm.image">
              <Check :size="16" />
              <span>{{ isEditingBanner ? 'Guardar Cambios' : 'Crear Banner' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ==================== MODAL AVISO ==================== -->
    <div v-if="isAnnModalOpen" class="modal-backdrop" @click.self="isAnnModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><Megaphone :size="18" /></div>
            <div>
              <h3>{{ isEditingAnn ? 'Editar Aviso' : 'Nuevo Aviso' }}</h3>
              <p class="modal-header-desc">Configura el mensaje rotativo de la marquesina</p>
            </div>
          </div>
          <button class="close-btn" @click="isAnnModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitAnnouncementForm">
          <div class="modal-row">
            <label class="modal-label">
              <span>Texto de la Etiqueta <span class="required">*</span></span>
              <input v-model="annForm.badge" type="text" required placeholder="Ej: PROMO, HORARIO, NUEVO" class="modal-input" />
            </label>

            <label class="modal-label">
              <span>Tipo de Distintivo</span>
              <select v-model="annForm.type" class="modal-input">
                <option value="promo">Promo (Naranja)</option>
                <option value="schedule">Horario (Azul)</option>
                <option value="new">Nuevo (Verde)</option>
                <option value="payment">Medios de Pago (Morado)</option>
                <option value="info">Informativo (Gris)</option>
              </select>
            </label>
          </div>

          <label class="modal-label">
            <span>Texto Principal del Aviso <span class="required">*</span></span>
            <textarea v-model="annForm.text" required rows="2" placeholder="Ej: ¡2x1 en completos todos los martes!" class="modal-input"></textarea>
          </label>

          <label class="modal-label">
            <span>Texto Resaltado (Opcional)</span>
            <input v-model="annForm.highlight" type="text" placeholder="Ej: 🔥 Solo por hoy" class="modal-input" />
          </label>

          <label class="toggle-availability-label">
            <div class="toggle-text">
              <strong>Activar en marquesina</strong>
              <span>Incluir en la rotación continua superior</span>
            </div>
            <input type="checkbox" v-model="annForm.active" class="modern-toggle" />
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isAnnModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save">
              <Check :size="16" />
              <span>{{ isEditingAnn ? 'Guardar Aviso' : 'Crear Aviso' }}</span>
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
  Eye, X, UploadCloud, Clock, Sparkles, Check 
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
const activeAnnouncementsCount = computed(() => announcements.value.filter(a => a.active).length);
const highlightedAnnouncementsCount = computed(() => announcements.value.filter(a => Boolean(a.highlight)).length);

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
  notify(`Velocidad fijada en ${val / 1000}s`, 'success');
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
    notify('Imagen convertida a WebP optimizado', 'success');
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
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER Y ACCIONES
==================================================== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-copy h1 {
  color: var(--DC-brown, #513119);
  font-size: 2.2rem;
  line-height: 1.1;
  margin: 0 0 0.4rem 0;
}

.header-copy p {
  margin: 0;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.92rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  padding: 0.65rem 1.1rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 700;
  font-size: 0.88rem;
}

.btn-secondary:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.15);
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.2);
}

/* ====================================================
   PESTAÑAS SEGMENTADAS (ESTILO ESTÁNDAR)
==================================================== */
.inventory-tabs-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #ede6dc;
  border-radius: 14px;
  max-width: 100%;
}

.tab-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #6d6254;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.tab-nav-btn:hover:not(.active) {
  color: var(--DC-brown, #513119);
  background: rgba(255, 255, 255, 0.4);
}

.tab-nav-btn.active {
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.08);
}

.tab-pill {
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(81, 49, 25, 0.08);
  color: #665b4f;
  font-weight: 800;
}

.tab-nav-btn.active .tab-pill {
  background: var(--DC-orange, #e28743);
  color: white;
}

/* ====================================================
   KPIS SUMMARY-GRID
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.summary-card {
  background: white;
  border-radius: 18px;
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
  padding: 1.1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.summary-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #2c2724);
  font-size: 1.45rem;
  line-height: 1.15;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
  margin: 0;
}

.text-status-open { color: #15803d; }
.text-orange { color: var(--DC-orange, #e28743); }
.text-pink { color: var(--DC-pink, #d80056); }

/* ====================================================
   PANEL UNIFICADO & TOOLBAR
==================================================== */
.table-unified-card {
  background: white;
  border-radius: 18px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.panel-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1.15rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.panel-section-title {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.86rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.interval-control-group {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

.select-box select {
  padding: 0.45rem 0.75rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
  cursor: pointer;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #513119);
  font-size: 0.78rem;
  font-weight: 800;
}

/* ====================================================
   PREVIEW DE CARRUSEL
==================================================== */
.preview-container-block {
  padding: 1.15rem;
  background: #fffdf9;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.preview-top-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.preview-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.live-pill {
  font-size: 0.65rem;
  background: #10b981;
  color: white;
  padding: 2px 7px;
  border-radius: 999px;
  font-weight: 800;
}

.preview-carousel-screen {
  position: relative;
  width: 100%;
  height: 220px;
  border-radius: 14px;
  overflow: hidden;
  background: #1a1614;
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
  padding: 16px 20px;
  background: linear-gradient(transparent, rgba(26, 14, 5, 0.85));
  color: white;
}

.preview-slide-overlay h3 {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 900;
}

.preview-slide-overlay p {
  margin: 3px 0 0 0;
  font-size: 0.82rem;
  color: #eedcd0;
}

.preview-dots {
  position: absolute;
  bottom: 12px;
  right: 18px;
  display: flex;
  gap: 6px;
}

.p-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.45);
  cursor: pointer;
  transition: all 0.2s ease;
}

.p-dot.active {
  background: var(--DC-orange, #e28743);
  transform: scale(1.3);
}

.empty-preview-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
  gap: 8px;
  color: #a89f95;
  font-size: 0.85rem;
}

/* ====================================================
   LISTADO Y TARJETAS DE BANNERS
==================================================== */
.banners-grid-wrapper {
  padding: 1.25rem;
}

.banners-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 1.25rem;
}

.banner-card {
  background: white;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.banner-card:hover {
  transform: translateY(-2px);
  border-color: rgba(226, 135, 67, 0.35);
}

.banner-card.inactive {
  opacity: 0.7;
}

.banner-card-img {
  position: relative;
  width: 100%;
  height: 150px;
  background: #1a1614;
}

.banner-card-img img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.order-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: rgba(30, 20, 10, 0.75);
  color: white;
  font-weight: 800;
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 6px;
  backdrop-filter: blur(4px);
}

.status-pill {
  position: absolute;
  top: 8px;
  right: 8px;
  font-weight: 800;
  font-size: 0.68rem;
  padding: 2px 8px;
  border-radius: 999px;
  text-transform: uppercase;
}

.status-active { background: #dcfce7; color: #166534; }
.status-inactive { background: #f1f5f9; color: #475569; }

.banner-card-body {
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  flex-grow: 1;
  justify-content: space-between;
}

.banner-title {
  margin: 0 0 2px 0;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
}

.banner-sub {
  margin: 0;
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.4;
}

.banner-card-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
  padding-top: 0.65rem;
}

/* ====================================================
   LISTADO Y TARJETAS DE AVISOS
==================================================== */
.announcements-wrapper {
  padding: 1.25rem;
}

.announcements-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.ann-card {
  display: grid;
  grid-template-columns: 130px 1fr auto;
  align-items: center;
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  padding: 0.85rem 1.15rem;
  gap: 1rem;
  transition: border-color 0.2s ease;
}

.ann-card:hover {
  border-color: rgba(226, 135, 67, 0.35);
}

.ann-card.inactive {
  opacity: 0.65;
}

.ann-badge {
  display: inline-flex;
  align-items: center;
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  padding: 3px 9px;
  border-radius: 999px;
}

.badge-promo { background: #ffedd5; color: #c2410c; }
.badge-schedule { background: #dbeafe; color: #1d4ed8; }
.badge-new { background: #d1fae5; color: #047857; }
.badge-payment { background: #f3e8ff; color: #7e22ce; }
.badge-info { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.ann-content-col {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.ann-text {
  margin: 0;
  font-size: 0.86rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
}

.ann-highlight {
  font-size: 0.75rem;
  font-weight: 800;
  background: #fef3c7;
  color: #92400e;
  padding: 2px 7px;
  border-radius: 6px;
}

.ann-actions-col {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

/* ====================================================
   SWITCH & ACCIONES ESTANDARIZADAS
==================================================== */
.status-switch {
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 700;
  cursor: pointer;
  user-select: none;
  transition: all 0.2s ease;
}

.status-switch input { display: none; }

.slider {
  position: relative;
  width: 32px;
  height: 18px;
  border-radius: 999px;
  transition: all 0.25s ease;
  flex-shrink: 0;
}

.slider::before {
  content: "";
  position: absolute;
  top: 2px;
  left: 2px;
  width: 14px;
  height: 14px;
  border-radius: 50%;
  background: white;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 0.25s ease;
}

.status-switch.active {
  color: #15803d;
  background: rgba(62, 165, 93, 0.12);
}

.status-switch.active .slider {
  background: #16a34a;
}

.status-switch.active .slider::before {
  transform: translateX(14px);
}

.status-switch.inactive {
  color: var(--DC-text-gray, #7c7468);
  background: var(--DC-bg-gray, #f8f6f3);
}

.status-switch.inactive .slider {
  background: #cbd5e1;
}

.status-switch.inactive .slider::before {
  transform: translateX(0);
}

.actions {
  display: flex;
  align-items: center;
  gap: 4px;
}

.icon-button {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-button:hover {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.icon-button.delete-btn:hover {
  background: #fee2e2;
  border-color: #fca5a5;
  color: #dc2626;
}

/* ====================================================
   MODALES (ESTILO HOMOGÉNEO)
==================================================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
}

.modal-card {
  position: relative;
  width: min(100%, 480px);
  border-radius: 18px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
  overflow: hidden;
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.modal-header-title {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.header-icon-pill {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--DC-brown, #513119);
}

.modal-header-desc {
  margin: 0;
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.modal-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.modal-label span .required {
  color: #ef4444;
}

.modal-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.65rem 0.85rem;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  outline: none;
}

.modal-input:focus {
  border-color: var(--DC-orange, #e28743);
}

.modal-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

.image-upload-box {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  padding: 0.85rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.sub-legend {
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.dropzone-area {
  border: 2px dashed rgba(81, 49, 25, 0.15);
  background: white;
  border-radius: 10px;
  padding: 1rem;
  text-align: center;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  transition: all 0.2s;
}

.dropzone-area:hover {
  border-color: var(--DC-orange, #e28743);
}

.upload-icon {
  color: var(--DC-orange, #e28743);
}

.dropzone-text strong {
  display: block;
  font-size: 0.82rem;
  color: var(--DC-gray, #2c2724);
}

.dropzone-text span {
  display: block;
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
}

.banner-form-preview {
  border-radius: 8px;
  overflow: hidden;
  height: 100px;
}

.banner-form-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.toggle-availability-label {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  gap: 12px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.75rem;
  border-radius: 10px;
}

.toggle-text strong {
  display: block;
  font-size: 0.82rem;
  color: var(--DC-brown, #513119);
}

.toggle-text span {
  display: block;
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
}

.modern-toggle {
  width: 18px;
  height: 18px;
  accent-color: #10b981;
  cursor: pointer;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 1rem 1.25rem;
  background: #fffdfa;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.btn-cancel {
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
}

.btn-save {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-save:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* ====================================================
   RESPONSIVO
==================================================== */
@media (max-width: 960px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .banners-admin-page {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-actions {
    flex-direction: column;
    width: 100%;
  }

  .btn-primary, .btn-secondary {
    width: 100%;
    justify-content: center;
  }

  .ann-card {
    grid-template-columns: 1fr;
    gap: 0.75rem;
  }

  .ann-actions-col {
    justify-content: space-between;
    border-top: 1px dashed rgba(81, 49, 25, 0.08);
    padding-top: 0.65rem;
  }
}
</style>