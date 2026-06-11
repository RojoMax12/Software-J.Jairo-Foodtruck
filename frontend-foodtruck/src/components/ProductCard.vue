<template>
  <div class="card">
    <div class="card-image">
      <img :src="image" :alt="name" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>
      <p class="product-category" :style="{ color: categoryColor }">
        - {{ category }}
      </p>

      <button class="details-btn" @click="$emit('view-details')">
        Ver detalles
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  name: string;
  category: string;
  categoryColor: string;
  image: string;
}>();

defineEmits(['view-details']);
</script>

<style scoped>
.card {
  background-color: #fff;
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  width: 220px; 
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.card-image {
  width: 100%;
  height: 140px;
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
}

.product-name {
  margin: 0;
  font-size: 1.1rem;
  color: #333;
  font-weight: 700;
}

.product-category {
  margin: 2px 0 15px 0;
  font-size: 0.85rem;
  font-weight: 500;
}

.details-btn {
  background-color: var(--DC-pink);
  color: #fff;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 0.9rem;
  width: 100%;
}
</style>