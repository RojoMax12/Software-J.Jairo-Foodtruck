<template>
  <div class="card">
    <div class="card-image">
      <img :src="image" :alt="name" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>

      <h4 class="product-price">{{ price }}</h4>

      <button class="details-btn" @click="$emit('view-details')">
        Ver detalles
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  name: string;
  category?: string;
  categoryColor?: string;
  image: string;
  price: string | number; // 🌟 Nueva propiedad añadida
}>();

defineEmits(['view-details']);
</script>

<style scoped>
.card {
  background-color: var(--DC-brown, #984c05); /* Uso tu variable si existe, sino el fallback */
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  width: 100%; /* 🌟 CLAVE: Ahora la tarjeta ocupa el 100% de la celda de la grilla */
  display: flex;
  flex-direction: column; /* Para empujar el botón hacia abajo */
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-image {
  width: 100%;
  height: 150px;
  overflow: hidden;
}

.card-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.card-content {
  padding: 15px;
  text-align: left;
  display: flex;
  flex-direction: column;
  flex-grow: 1; /* Permite que el contenido ocupe todo el espacio restante */
}

.product-name {
  margin: 0;
  font-size: 1.1rem;
  color: #ffffff;
  font-weight: 800;
  line-height: 1.2;
}

.product-category {
  margin: 4px 0;
  font-size: 0.8rem;
  font-weight: 600;
  color: #eeeeee;
  text-transform: uppercase;
}

.product-price {
  margin: 8px 0 15px 0;
  font-size: 1rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743); /* Destacamos el precio */
}

.details-btn {
  background-color: var(--DC-orange, #e28743);
  color: #fff;
  border: none;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 800;
  width: 100%;
  margin-top: auto; /* 🌟 CLAVE: Empuja el botón siempre al fondo de la tarjeta */
  transition: background-color 0.2s;
}

.details-btn:hover {
  background-color: #cf7332;
}

/* 📱 RESPONSIVO PARA CELULARES */
@media (max-width: 600px) {
  .card-image {
    height: 120px; /* Hacemos la imagen un poco más baja para ahorrar espacio en móviles */
  }

  .card-content {
    padding: 12px;
  }

  .product-name {
    font-size: 1rem; /* Achicamos ligeramente el título */
  }

  .product-price {
    font-size: 0.9rem;
    margin: 6px 0 12px 0;
  }

  .details-btn {
    padding: 12px; /* Botón un poco más alto en móvil para que sea más fácil tocarlo con el dedo */
    font-size: 0.9rem;
  }
}
</style>