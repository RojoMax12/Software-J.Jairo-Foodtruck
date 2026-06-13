<template>
  <div class="admin-home-container">
    <div class="admin-home-header">
      <h1>Panel de Administración</h1>
      <p>Seleccione el módulo que desea gestionar</p>
    </div>
    
    <div class="admin-options-grid">

      <router-link `v-role="[1,3]"` to="/general-home/orders" class="admin-option-card">
        <div class="icon-container">
          <ShoppingBag :size="48" />
        </div>
        <h2>Pedidos</h2>
        <p>Administra los pedidos</p>
      </router-link>

      <router-link `v-role="[1,3]"` to="/general-home/generate-quote" class="admin-option-card">
        <div class="icon-container">
          <FilePlus :size="48" />
        </div>
        <h2>Generar Pedido</h2>
        <p>Crear una nueva Pedido para un cliente (existente o nuevo)</p>
      </router-link>

      <router-link `v-role="[1,3]"` to="/general-home/inventory" class="admin-option-card">
        <div class="icon-container">
          <FileText :size="48" />
        </div>
        <h2>Inventario</h2>
        <p>Gestiona y revisa los insumos disponibles </p>
      </router-link>

      <router-link `v-role="[1]"` to="/general-home/admin/cash-flow" class="admin-option-card">
        <div class="icon-container">
          <FileText :size="48" />
        </div>
        <h2>Caja</h2>
        <p>Gestiona y revisa tu flujo de dinero</p>
      </router-link>


      <router-link `v-role="[1]"` to="/general-home/admin/worker" class="admin-option-card">
        <div class="icon-container">
          <FileText :size="48" />
        </div>
        <h2>Trabajadores</h2>
        <p>Gestiona y revisa el estado de tus trabajadores</p>
      </router-link>

      <router-link `v-role="[1]"` to="/general-home/admin/product" class="admin-option-card">
        <div class="icon-container">
          <FileText :size="48" />
        </div>
        <h2>Productos</h2>
        <p>Gestiona, revisa y pon los productos disponible en oferta</p>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FileText, ShoppingBag, FilePlus } from 'lucide-vue-next';
</script>

<style scoped>
.admin-home-container {
  max-width: 900px;
  margin: 3rem auto;
  padding: 0 1.5rem;
}

.admin-home-header {
  text-align: center;
  margin-bottom: 2.5rem;
}

.admin-home-header h1 {
  color: var(--DC-brown);
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.admin-home-header p {
  color: var(--DC-text-gray);
  font-size: 1.1rem;
}

.admin-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.admin-option-card {
  background: white;
  border-radius: 20px; /* Bordes más redondeados para un look más moderno */
  padding: 2rem 1.5rem;
  text-align: center;
  text-decoration: none;
  color: var(--DC-gray);
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  border: 2px solid transparent;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.admin-option-card:hover {
  transform: translateY(-5px);
  border-color: var(--DC-orange);
  box-shadow: 0 10px 15px rgba(226, 135, 67, 0.15);
}

.icon-container {
  width: 80px; /* Reducimos el tamaño del círculo del icono */
  height: 80px;
  background: var(--DC-bg-gray);
  border-radius: 20px; /* Cambiamos a bordes redondeados tipo iOS */
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
  color: var(--DC-brown);
  transition: all 0.3s ease;
}

.admin-option-card:hover .icon-container {
  background: var(--DC-orange);
  color: white;
}

.admin-option-card h2 {
  font-size: 1.2rem; /* Título un poco más pequeño para que no domine */
  margin-bottom: 0.5rem;
}

.admin-option-card p {
  color: var(--DC-text-gray);
  font-size: 0.85rem; /* Texto descriptivo más compacto */
  line-height: 1.4;
}

/* Ajustes para móvil */
@media (max-width: 640px) {
  .admin-home-container {
    margin: 1.5rem auto;
  }
  .admin-options-grid {
    grid-template-columns: repeat(2, 1fr); /* En celular que queden 2 columnas para que no sea una lista vertical infinita */
    gap: 1rem;
  }
  .admin-option-card {
    padding: 1.5rem 1rem;
  }
}
</style>
