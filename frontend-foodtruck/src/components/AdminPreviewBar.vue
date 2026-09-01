<template>
  <div v-if="isPreviewActive" class="admin-preview-bar animate-slide-down">
    <div class="preview-info">
      <span class="preview-badge">
        <Eye :size="14" />
        <span>Modo Previsualización</span>
      </span>
      <span class="preview-text">Estás viendo la tienda como cliente. Los cambios se actualizan en vivo.</span>
    </div>

    <div class="preview-actions">
      <button class="btn-return-admin" @click="returnToAdmin">
        <ArrowLeft :size="15" />
        <span>Volver al Panel</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Eye, ArrowLeft } from 'lucide-vue-next';

const router = useRouter();
const route = useRoute();
const isPreviewActive = ref(false);

const checkPreview = () => {
  try {
    const userStr = localStorage.getItem('user');
    const token = localStorage.getItem('token');

    if (!userStr || !token) {
      isPreviewActive.value = false;
      return;
    }

    const user = JSON.parse(userStr);
    const roleId = Number(user.id_rol);

    // SOLAMENTE el Administrador (id_rol === 1) tiene acceso al Modo Previsualización
    if (roleId !== 1) {
      isPreviewActive.value = false;
      sessionStorage.removeItem('admin_preview_mode');
      return;
    }

    // Comprobar si se activó explícitamente el modo previsualización
    const inPreviewSession = sessionStorage.getItem('admin_preview_mode') === 'true';
    const hasPreviewQuery = route.query.preview === '1' || route.query.preview === 'true';

    if (hasPreviewQuery) {
      sessionStorage.setItem('admin_preview_mode', 'true');
      isPreviewActive.value = true;
    } else {
      isPreviewActive.value = inPreviewSession;
    }
  } catch {
    isPreviewActive.value = false;
  }
};

onMounted(() => {
  checkPreview();
  window.addEventListener('storage', checkPreview);
});

watch(() => route.fullPath, () => {
  checkPreview();
});

const returnToAdmin = () => {
  sessionStorage.removeItem('admin_preview_mode');
  router.push('/general-home');
};
</script>

<style scoped>
.admin-preview-bar {
  background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%);
  border-bottom: 2px solid #e28743;
  color: #ffffff;
  padding: 8px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-family: inherit;
  font-size: 0.85rem;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.preview-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.preview-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #e28743;
  color: #ffffff;
  font-weight: 800;
  font-size: 0.75rem;
  padding: 3px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.preview-text {
  color: #94a3b8;
  font-weight: 500;
}

.preview-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.btn-return-admin {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #ffffff;
  padding: 5px 12px;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-return-admin:hover {
  background: #e28743;
  border-color: #e28743;
  transform: translateX(-2px);
}

@keyframes slideDown {
  from {
    transform: translateY(-100%);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.animate-slide-down {
  animation: slideDown 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@media (max-width: 640px) {
  .admin-preview-bar {
    flex-direction: column;
    gap: 8px;
    padding: 10px 16px;
    text-align: center;
  }

  .preview-info {
    flex-direction: column;
    gap: 4px;
  }

  .preview-text {
    font-size: 0.75rem;
  }
}
</style>
