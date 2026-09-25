<template>
  <div class="offer-card">
    <div class="offer-badge">
      <span v-if="promoBadgeText && promoBadgeText !== 'Promoción' && promoBadgeText !== 'Oferta'">
        {{ promoBadgeText }} <template v-if="discountPercent">(-{{ discountPercent }}%)</template>
      </span>
      <span v-else-if="discountPercent">-{{ discountPercent }}%</span>
      <span v-else>{{ promoBadgeText || 'Oferta' }}</span>
    </div>

    <div class="card-image">
      <img :src="image" :alt="name" :style="imageStyle" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>

      <div v-if="displayHint" class="product-hint">{{ displayHint }}</div>

      <!-- Precios: Antiguo tachado y luego el de oferta -->
      <div class="price-container">
        <span v-if="originalPrice" class="original-price" title="Precio original">
          {{ originalPrice }}
        </span>
        <h4 class="product-price offer-price" title="Precio de oferta">
          {{ displayPrice ?? price }}
        </h4>
      </div>

      <button class="details-btn" @click="$emit('view-details')">
        Ver oferta
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  name: string;
  image: string;
  imagePosition?: string;
  imageZoom?: number;
  imageFit?: 'cover' | 'contain';
  price?: string | number;
  displayPrice?: string | number;
  originalPrice?: string | number;
  promoBadgeText?: string;
  discountPercent?: number;
  displayHint?: string;
}>(), {
  price: 'Sin precio',
  displayPrice: undefined,
  originalPrice: undefined,
  promoBadgeText: 'Oferta',
  discountPercent: undefined,
  displayHint: undefined,
  imagePosition: '50% 50%',
  imageZoom: 1,
  imageFit: 'cover',
});

const imageStyle = computed(() => ({
  objectPosition: props.imagePosition,
  objectFit: props.imageFit,
  transform: `scale(${Math.max(1, props.imageZoom)})`,
}));

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
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 6px 12px;
  border-radius: 999px;
  box-shadow: 0 2px 6px rgba(226, 135, 67, 0.4);
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

.price-container {
  display: flex;
  align-items: baseline;
  gap: 8px;
  flex-wrap: wrap;
  margin: 4px 0 15px 0;
}

.original-price {
  font-size: 0.85rem;
  font-weight: 700;
  color: #8c857b;
  text-decoration: line-through;
}

.product-price {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 900;
  color: #b85a00;
}

.product-price.offer-price {
  color: #d9480f;
  font-size: 1.25rem;
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
  transition: background-color 0.2s;
}

.details-btn:hover {
  background-color: #7a3900;
}

/* 📱 RESPONSIVO PARA CELULARES */
@media (max-width: 600px) {
  .card-image {
    height: 120px;
  }

  .card-content {
    padding: 12px;
  }

  .product-name {
    font-size: 1rem;
  }

  .offer-badge {
    padding: 4px 8px;
    font-size: 0.7rem;
    top: 8px;
    left: 8px;
  }

  .price-container {
    gap: 6px;
    margin: 4px 0 10px 0;
  }

  .original-price {
    font-size: 0.78rem;
  }

  .product-price.offer-price {
    font-size: 1.05rem;
  }

  .details-btn {
    padding: 12px;
    font-size: 0.9rem;
  }
}
</style>
