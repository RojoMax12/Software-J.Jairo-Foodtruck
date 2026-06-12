<template>
  <div class="carousel-container">
    <div class="carousel-track-container">
      <div 
        class="carousel-track" 
        :style="{ transform: `translateX(-${currentSlide * 100}%)` }"
      >
        <div 
          v-for="(image, index) in images" 
          :key="index" 
          class="carousel-slide"
        >
          <img :src="image" alt="Banner Di Creme" class="carousel-image" />
        </div>
      </div>
    </div>

    <button class="carousel-button prev" @click="prevSlide">◀</button>
    <button class="carousel-button next" @click="nextSlide">▶</button>

    <div class="carousel-indicators">
      <button 
        v-for="(_, index) in images" 
        :key="index"
        :class="['indicator-dot', { active: currentSlide === index }]"
        @click="goToSlide(index)"
      ></button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps<{
  images: string[];
  autoPlayInterval?: number; // Tiempo opcional para el auto-avance
}>();

const currentSlide = ref(0);
let autoPlayTimer: any = null;

const nextSlide = () => {
  if (currentSlide.value < props.images.length - 1) {
    currentSlide.value++;
  } else {
    currentSlide.value = 0;
  }
};

const prevSlide = () => {
  if (currentSlide.value > 0) {
    currentSlide.value--;
  } else {
    currentSlide.value = props.images.length - 1;
  }
};

const goToSlide = (index: number) => {
  currentSlide.value = index;
};

onMounted(() => {
  if (props.autoPlayInterval) {
    autoPlayTimer = setInterval(nextSlide, props.autoPlayInterval);
  }
});

onUnmounted(() => {
  if (autoPlayTimer) clearInterval(autoPlayTimer);
});
</script>

<style scoped>
.carousel-container {
  position: relative;
  width: 100vw;         /* Rompe el contenedor padre y toma todo el ancho visible */
  left: 50%;
  right: 50%;
  margin-left: -50vw;
  margin-right: -50vw;  /* Truco de CSS para forzar diseño de borde a borde */
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.carousel-track-container {
  width: 100%;
  height: 460px;        /* Altura panorámica ideal para monitores y pantallas de escritorio */
}

.carousel-track {
  display: flex;
  width: 100%;
  height: 100%;
  transition: transform 0.5s ease-in-out; /* Animación fluida de deslizamiento */
}

.carousel-slide {
  min-width: 100%;
  height: 100%;
  flex-shrink: 0;
}

.carousel-image {
  width: 100%;
  height: 100%;
  object-fit: cover;     /* Mantiene la proporción de la foto sin deformarla ni estirarla feo */
  object-position: center; /* Centra la imagen para que el recorte sea simétrico */
}

/* Flechas de navegación */
.carousel-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.4);
  color: white;
  border: none;
  padding: 14px 18px;
  cursor: pointer;
  border-radius: 50%;
  z-index: 10;
  transition: background 0.2s;
}

.carousel-button:hover {
  background: rgba(0, 0, 0, 0.7);
}

.prev { left: 25px; }
.next { right: 25px; }

/* Puntitos indicadores */
.carousel-indicators {
  position: absolute;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 10px;
  z-index: 10;
}

.indicator-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: all 0.2s ease;
}

.indicator-dot.active {
  background: #E28743; /* Tu color naranja corporativo */
  transform: scale(1.2);
}

/* 📱 Optimización estricta para celulares */
@media (max-width: 600px) {
  .carousel-track-container {
    height: 220px;     /* Reducimos la altura en móviles para equilibrar el espacio */
  }
  
  .carousel-button {
    padding: 8px 12px; /* Flechas más pequeñas para no estorbar el contenido */
  }
  
  .prev { left: 10px; }
  .next { right: 10px; }
}
</style>