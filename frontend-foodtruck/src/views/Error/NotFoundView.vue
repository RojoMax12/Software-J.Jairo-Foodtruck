<template>
  <div class="not-found-container">
    <div class="not-found-card">
      <div class="icon-wrapper">
        <div class="badge-404">404</div>
        <UtensilsCrossed :size="64" class="not-found-icon" />
      </div>

      <span class="eyebrow">Página no encontrada</span>
      <h1>¡Ups! Te has salido del menú</h1>
      <p>
        La página o sección que intentas visitar no existe, ha sido movida o la ruta ingresada es incorrecta.
      </p>

      <div class="actions">
        <button class="btn-primary" @click="goHome">
          <Home :size="20" />
          <span>Volver al Inicio</span>
        </button>

        <button class="btn-secondary" @click="goBack">
          <ArrowLeft :size="20" />
          <span>Regresar</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { UtensilsCrossed, Home, ArrowLeft } from 'lucide-vue-next';

const router = useRouter();

const goHome = () => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    try {
      const user = JSON.parse(userStr);
      if (user.id_rol === 1 || user.id_rol === 3) {
        router.push('/general-home');
        return;
      }
    } catch (e) {
      console.error(e);
    }
  }
  router.push('/');
};

const goBack = () => {
  if (window.history.length > 1) {
    router.back();
  } else {
    goHome();
  }
};
</script>

<style scoped>
.not-found-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: radial-gradient(circle at top right, #fdf8f3 0%, #f4e8dc 100%);
  padding: 2rem 1.5rem;
  font-family: inherit;
}

.not-found-card {
  background: white;
  max-width: 540px;
  width: 100%;
  border-radius: 28px;
  padding: 3rem 2.5rem;
  text-align: center;
  box-shadow: 0 20px 50px rgba(81, 49, 25, 0.1);
  border: 1px solid rgba(81, 49, 25, 0.08);
  animation: fadeInScale 0.4s ease-out forwards;
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.94) translateY(12px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.icon-wrapper {
  position: relative;
  width: 110px;
  height: 110px;
  margin: 0 auto 1.5rem;
  background: rgba(226, 135, 67, 0.12);
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.not-found-icon {
  color: var(--DC-orange, #e28743);
  transform: rotate(-10deg);
}

.badge-404 {
  position: absolute;
  top: -10px;
  right: -10px;
  background: var(--DC-brown, #513119);
  color: white;
  font-weight: 900;
  font-size: 0.85rem;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  box-shadow: 0 4px 12px rgba(81, 49, 25, 0.25);
}

.eyebrow {
  display: inline-block;
  color: var(--DC-orange, #e28743);
  font-weight: 800;
  font-size: 0.82rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  margin-bottom: 0.5rem;
}

.not-found-card h1 {
  color: var(--DC-brown, #513119);
  font-size: 2.1rem;
  line-height: 1.25;
  margin: 0 0 1rem;
  font-weight: 800;
}

.not-found-card p {
  color: #6b7280;
  font-size: 1rem;
  line-height: 1.6;
  margin: 0 0 2rem;
}

.actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: var(--DC-brown, #513119);
  padding: 0.85rem 1.6rem;
  border-radius: 16px;
  font-weight: 800;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 8px 20px rgba(226, 135, 67, 0.25);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 28px rgba(226, 135, 67, 0.35);
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  padding: 0.85rem 1.6rem;
  border-radius: 16px;
  font-weight: 800;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  transition: all 0.25s ease;
}

.btn-secondary:hover {
  background: #fdf8f3;
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-2px);
}
</style>
