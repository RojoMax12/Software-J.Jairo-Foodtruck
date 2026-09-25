<template>
  <div class="admin-home-container">
    <!-- CABECERA PRINCIPAL -->
    <header class="admin-home-header">
      <h1 class="welcome-title">Panel de {{ userRoleName }}</h1>
      <p class="welcome-subtitle">
        Hola{{ userName ? ' ' + userName : '' }}, selecciona el módulo que deseas gestionar a continuación.
      </p>
    </header>
    
    <!-- GRILLA MODULAR DE OPCIONES -->
    <div class="admin-options-grid">

      <!-- 1. PEDIDOS -->
      <router-link v-role="[1,3]" to="/general-home/orders" class="admin-option-card">
        <div class="icon-container icon-orange">
          <ShoppingBag :size="38" />
        </div>
        <h2>Pedidos & Comandas</h2>
        <p>Monitor de cocina en tiempo real y despacho de órdenes</p>
      </router-link>

      <!-- 2. GENERAR PEDIDO -->
      <router-link v-role="[1,3]" to="/general-home/generate-quote" class="admin-option-card">
        <div class="icon-container icon-green">
          <Store :size="38" />
        </div>
        <h2>Generar Pedido</h2>
        <p>Crear un nuevo pedido de venta directa para un cliente</p>
      </router-link>

      <!-- 3. INVENTARIO -->
      <router-link v-role="[1,3]" to="/general-home/inventory" class="admin-option-card">
        <div class="icon-container icon-brown">
          <Package :size="38" />
        </div>
        <h2>Stock & Kardex</h2>
        <p>Control de insumos disponibles, mermas y movimientos</p>
      </router-link>

      <!-- 4. CAJA -->
      <router-link v-role="[1,3]" to="/general-home/admin/cash-flow" class="admin-option-card">
        <div class="icon-container icon-amber">
          <BadgeDollarSign :size="38" />
        </div>
        <h2>Caja & Turnos</h2>
        <p>Apertura, cierre de turnos, arqueos y egresos operativos</p>
      </router-link>

      <!-- 5. TRABAJADORES (SOLO ADMIN) -->
      <router-link v-role="[1]" to="/general-home/admin/worker" class="admin-option-card">
        <div class="icon-container icon-blue">
          <Users :size="38" />
        </div>
        <h2>Trabajadores</h2>
        <p>Administra cuentas, roles y accesos del personal</p>
      </router-link>

      <!-- 6. PRODUCTOS (SOLO ADMIN) -->
      <router-link v-role="[1]" to="/general-home/admin/product" class="admin-option-card">
        <div class="icon-container icon-pink">
          <PackageSearch :size="38" />
        </div>
        <h2>Catálogo & Carta</h2>
        <p>Platos, formatos, ingredientes base y precios promocionales</p>
      </router-link>

      <!-- 7. PERSONALIZACIÓN / BANNERS (SOLO ADMIN) -->
      <router-link v-role="[1]" to="/general-home/admin/banners" class="admin-option-card">
        <div class="icon-container icon-purple">
          <Palette :size="38" />
        </div>
        <h2>Banners & Avisos</h2>
        <p>Imágenes del carrusel de portada y marquesina de avisos</p>
      </router-link>

      <!-- 8. HORARIOS (SOLO ADMIN) -->
      <router-link v-role="[1]" to="/general-home/admin/schedules" class="admin-option-card">
        <div class="icon-container icon-teal">
          <Clock :size="38" />
        </div>
        <h2>Horarios & Colchón</h2>
        <p>Turnos de apertura, días de descanso y tolerancia nocturna</p>
      </router-link>

      <!-- 9. MENU BOARD DIGITAL (TV EXTERNA) -->
      <a href="/menu-board" target="_blank" rel="noopener noreferrer" class="admin-option-card menu-board-card">
        <div class="icon-container icon-tv">
          <Tv :size="38" />
        </div>
        <div class="card-title-group">
          <h2>Menu Board Digital</h2>
          <span class="external-chip">TV <ExternalLink :size="12" /></span>
        </div>
        <p>Pantalla completa de precios para proyectar en televisión</p>
      </a>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { 
  BadgeDollarSign, ShoppingBag, Package, Store, 
  Users, PackageSearch, Clock, Tv, Palette, 
  Sparkles, ExternalLink 
} from 'lucide-vue-next';

const userRoleName = ref('Administración');
const userName = ref('');

onMounted(() => {
  const userParsed = localStorage.getItem('user');
  if (userParsed) {
    try {
      const u = JSON.parse(userParsed);
      userName.value = u.nombre || u.nombre_usuario || u.name || '';
      const rId = Number(u.id_rol || 1);
      if (rId === 1) {
        userRoleName.value = 'Administración';
      } else if (rId === 3) {
        userRoleName.value = 'Trabajador';
      } else if (rId === 2) {
        userRoleName.value = 'Cliente';
      } else {
        userRoleName.value = u.rol?.nombre_rol || 'Operaciones';
      }
    } catch (e) {
      console.error('Error al leer sesión de usuario:', e);
    }
  }
});
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.admin-home-container {
  max-width: 1200px;
  margin: 2.5rem auto 4rem auto;
  padding: 0 1.5rem;
}

/* ====================================================
   HEADER & SALUDO
==================================================== */
.admin-home-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  margin-bottom: 2.5rem;
}

.welcome-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
  margin-bottom: 0.85rem;
}

.welcome-title {
  color: var(--DC-brown, #513119);
  font-size: 2.2rem;
  font-weight: 900;
  line-height: 1.15;
  margin: 0 0 0.4rem 0;
}

.welcome-subtitle {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.95rem;
  margin: 0;
  max-width: 520px;
  line-height: 1.5;
}

/* ====================================================
   GRILLA MODULAR DE TARJETAS
==================================================== */
.admin-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.25rem;
}

.admin-option-card {
  background: #ffffff;
  border-radius: 20px;
  padding: 1.75rem 1.4rem;
  text-align: center;
  text-decoration: none;
  color: var(--DC-gray, #2c2724);
  box-shadow: 0 4px 18px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
              border-color 0.25s ease, 
              box-shadow 0.25s ease;
}

.admin-option-card:hover {
  transform: translateY(-4px);
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 12px 30px rgba(226, 135, 67, 0.14);
}

/* ICONOS CON PALETA TEMÁTICA */
.icon-container {
  width: 72px;
  height: 72px;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.15rem;
  transition: transform 0.25s ease, filter 0.25s ease;
  flex-shrink: 0;
}

.admin-option-card:hover .icon-container {
  transform: scale(1.08);
}

.icon-orange  { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.icon-green   { background: #dcfce7; color: #15803d; }
.icon-brown   { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.icon-amber   { background: #fef3c7; color: #b45309; }
.icon-blue    { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.icon-pink    { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.icon-purple  { background: #f3e8ff; color: #7e22ce; }
.icon-teal    { background: #ccfbf1; color: #0f766e; }
.icon-tv      { background: #e0f2fe; color: #0369a1; }

.admin-option-card h2 {
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0 0 0.4rem 0;
}

.admin-option-card p {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.84rem;
  line-height: 1.45;
  margin: 0;
}

/* DETALLE DE MENU BOARD (TV) */
.card-title-group {
  display: flex;
  align-items: center;
  gap: 6px;
  justify-content: center;
  margin-bottom: 0.4rem;
}

.card-title-group h2 {
  margin: 0;
}

.external-chip {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 2px 7px;
  background: #e0f2fe;
  color: #0369a1;
  border-radius: 6px;
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

/* ====================================================
   RESPONSIVO
==================================================== */
@media (max-width: 768px) {
  .admin-home-container {
    margin: 1.5rem auto 3rem auto;
    padding: 0 1rem;
  }

  .welcome-title {
    font-size: 1.75rem;
  }

  .admin-options-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 0.85rem;
  }

  .admin-option-card {
    padding: 1.25rem 0.85rem;
    border-radius: 16px;
  }

  .icon-container {
    width: 60px;
    height: 60px;
    border-radius: 14px;
    margin-bottom: 0.85rem;
  }

  .admin-option-card h2 {
    font-size: 0.95rem;
  }

  .admin-option-card p {
    font-size: 0.78rem;
  }
}

@media (max-width: 480px) {
  .admin-options-grid {
    grid-template-columns: 1fr;
  }

  .admin-option-card {
    flex-direction: row;
    text-align: left;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.15rem;
  }

  .icon-container {
    margin-bottom: 0;
    width: 52px;
    height: 52px;
  }

  .card-title-group {
    justify-content: flex-start;
  }
}
</style>