<template>
  <div class="card">
    <div v-if="hasPromotion" class="card-promo-badge">
      <span v-if="discountPercent">-{{ discountPercent }}%</span>
      <span v-else>{{ promoBadgeText || 'Oferta' }}</span>
    </div>

    <div class="card-image">
      <img :src="image" :alt="name" :style="imageStyle" />
    </div>

    <div class="card-content">
      <h3 class="product-name">{{ name }}</h3>

      <div v-if="displayHint" class="product-hint">{{ displayHint }}</div>

      <!-- Precios: Antiguo tachado y luego el de oferta si aplica -->
      <div class="price-container">
        <span v-if="hasPromotion && originalPrice" class="original-price" title="Precio original">
          {{ originalPrice }}
        </span>
        <h4 class="product-price" :class="{ 'offer-price': hasPromotion && originalPrice }" title="Precio">
          {{ displayPrice ?? price }}
        </h4>
      </div>

      <button class="details-btn" @click="$emit('view-details')">
        Ver detalles
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{
  name: string;
  category?: string;
  categoryColor?: string;
  image: string;
  imagePosition?: string;
  imageZoom?: number;
  imageFit?: 'cover' | 'contain';
  price?: string | number;
  displayPrice?: string | number;
  originalPrice?: string | number;
  hasPromotion?: boolean;
  promoBadgeText?: string;
  discountPercent?: number;
  displayHint?: string;
}>(), {
  price: 'Sin precio',
  displayPrice: undefined,
  originalPrice: undefined,
  hasPromotion: false,
  promoBadgeText: undefined,
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
.card {
  position: relative;
  background-color: var(--DC-brown, #984c05);
  border-radius: 15px;
  overflow: hidden;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
  width: 100%;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
}

.card-promo-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  background: var(--DC-orange, #e28743);
  color: white;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 6px 12px;
  border-radius: 999px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.25);
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

.product-hint {
  margin: 8px 0 2px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.04em;
  color: rgba(255,255,255,0.7);
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
  color: rgba(255, 255, 255, 0.6);
  text-decoration: line-through;
}

.product-price {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.product-price.offer-price {
  color: #ffd43b;
  font-size: 1.2rem;
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
  margin-top: auto;
  transition: background-color 0.2s;
}

.details-btn:hover {
  background-color: #cf7332;
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

  .card-promo-badge {
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

  .product-price {
    font-size: 0.9rem;
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