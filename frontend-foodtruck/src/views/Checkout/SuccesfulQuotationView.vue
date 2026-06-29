<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Rescatamos los parámetros de la URL enviados tras la inserción en la base de datos
const idCotizacion = route.query.id || '000000'
const fechaCotizacion = route.query.fecha || '--/--/----'
const horaCotizacion = route.query.hora || '--:--'
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
          Puedes revisar el detalle y el estado haciendo <a href="#" @click.prevent="router.push('/checkorderstatus')">click aquí.</a>
        </p>

        <!-- Bloque de datos dinámicos inyectados -->
        <div class="data-table">
          <div class="data-row">
            <span class="data-label">N° de Pedido:</span>
            <span class="data-value font-mono">{{ String(idCotizacion).padStart(6, '0') }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Fecha:</span>
            <span class="data-value">{{ fechaCotizacion }}</span>
          </div>
          <div class="data-row">
            <span class="data-label">Hora:</span>
            <span class="data-value">{{ horaCotizacion }}</span>
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
.success-page { background-color: var(--button-color); min-height: 100vh; font-family: sans-serif; display: flex; align-items: center; justify-content: center; }
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
</style>