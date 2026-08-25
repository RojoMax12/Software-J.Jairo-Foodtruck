<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Extracción reactiva y segura de parámetros
const rawOrderId = computed(() => {
  const param = route.query.id
  if (Array.isArray(param)) return param[0] || ''
  return param ? String(param) : ''
})

const orderDate = computed(() => {
  const param = route.query.fecha
  const val = Array.isArray(param) ? param[0] : param
  return val || '--/--/----'
})

const orderTime = computed(() => {
  const param = route.query.hora
  const val = Array.isArray(param) ? param[0] : param
  return val || '--:--'
})

const formattedOrderId = computed(() => {
  if (!rawOrderId.value) return '000000'
  return /^\d+$/.test(rawOrderId.value) ? rawOrderId.value.padStart(6, '0') : rawOrderId.value
})

const goToCheckStatus = () => {
  router.push({
    path: '/checkorderstatus',
    query: rawOrderId.value ? { id: rawOrderId.value } : {}
  })
}
</script>

<template>
  <div class="success-page">
    <main class="success-container">
      <div class="success-card">
        <div class="check-icon-wrapper">
          <span class="check-mark">✓</span>
        </div>

        <h2 class="success-title">Tu Pedido ha sido Ingresado</h2>
        <p class="success-subtitle">¡Gracias por tu preferencia J.Junior!</p>
        
        <div class="divider-line"></div>
        
        <p class="info-text sub-link">
          Puedes revisar el detalle y el estado haciendo <a href="#" @click.prevent="goToCheckStatus">click aquí.</a>
        </p>

        <!-- Bloque de datos dinámicos inyectados -->
        <div class="data-table">
          <div class="data-row">
            <span class="data-label">N° de Pedido:</span>
            <span class="data-value font-mono">{{ formattedOrderId }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Fecha:</span>
            <span class="data-value">{{ orderDate }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Hora:</span>
            <span class="data-value">{{ orderTime }}</span>
          </div>
        </div>

        <button class="btn-home" @click="router.push('/')">
          Volver al inicio
        </button>
      </div>
    </main>
  </div>
</template>

<style scoped>
.success-page { background-color: var(--DC-bg-gray); min-height: 100vh; font-family: var(--font-main); display: flex; align-items: center; justify-content: center; }
.success-container { padding: 20px; width: 100%; display: flex; justify-content: center; }
.success-card { background-color: white; border-radius: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 40px 45px; width: 100%; max-width: 480px; text-align: center; position: relative; }
.check-icon-wrapper { width: 70px; height: 70px; background-color: var(--DC-orange); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: -75px auto 20px auto; border: 4px solid #eeedee; }
.check-mark { color: white; font-size: 2.2rem; font-weight: bold; }
.success-title { color: var(--DC-orange); font-size: 1.4rem; font-weight: 700; margin-bottom: 5px; }
.success-subtitle { color: #555; font-size: 1.05rem; font-weight: 600; margin-top: 0; }
.divider-line { height: 1px; background-color: var(--DC-brown); width: 100%; margin: 20px 0; }
.info-text { color: #666; font-size: 0.95rem; line-height: 1.5; margin: 10px 0; }
.sub-link a { color: #1a1624; font-weight: bold; text-decoration: underline; }
.data-table { margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; display: flex; flex-direction: column; gap: 8px; }
.data-row { display: flex; justify-content: space-between; font-size: 0.95rem; color: #333; }
.data-label { color: #666; }
.data-value { font-weight: 600; }
.font-mono { font-family: monospace; font-size: 1.05rem; }
.btn-home { width: 100%; margin-top: 24px; background-color: var(--DC-orange); color: white; border: none; padding: 12px 16px; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s; }
.btn-home:hover { background-color: var(--DC-brown); transform: translateY(-1px); }

@media (max-width: 480px) {
  .success-card {
    padding: 30px 18px;
    border-radius: 20px;
  }

  .success-title {
    font-size: 1.2rem;
  }
}
</style>