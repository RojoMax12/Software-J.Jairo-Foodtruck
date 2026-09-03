<template>
  <div class="offer-card">
    <div class="offer-badge">Oferta</div>

    <div class="card-image">
      <img :src="image" :alt="name" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>

      <div v-if="displayHint" class="product-hint">{{ displayHint }}</div>
      <h4 class="product-price">{{ displayPrice ?? price }}</h4>

      <button class="details-btn" @click="$emit('view-details')">
        Ver oferta
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
withDefaults(defineProps<{
  name: string;
  image: string;
  price?: string | number;
  displayPrice?: string | number;
  displayHint?: string;
}>(), {
  price: 'Sin precio',
  displayPrice: undefined,
  displayHint: undefined,
});

defineEmits(['view-details']);
</script>

<style scoped>
.offer-card {
  position: relative;
  background: linear-gradient(135deg, #fff4e6 0%, #f7ead8 100%);
  border: 2px solid rgba(226, 135, 67, 0.8);
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(152, 76, 5, 0.12);
  width: 100%;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
}

.offer-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 18px rgba(152, 76, 5, 0.18);
}

.offer-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 1;
  background: #e28743;
  color: white;
  font-size: 0.7rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 6px 10px;
  border-radius: 999px;
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
  flex-grow: 1;
}

.product-name {
  margin: 0;
  font-size: 1.1rem;
  color: #4a2a0d;
  font-weight: 800;
  line-height: 1.2;
}

.product-hint {
  margin: 8px 0 2px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: #8e5b1c;
  text-transform: uppercase;
}

.product-price {
  margin: 0 0 15px 0;
  font-size: 1rem;
  font-weight: 900;
  color: #b85a00;
}

.details-btn {
  margin-top: auto;
  background-color: #984c05;
  color: white;
  border: none;
  padding: 10px 12px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.95rem;
  font-weight: 800;
  width: 100%;
}

.details-btn:hover {
  background-color: #7a3900;
}
</style>
