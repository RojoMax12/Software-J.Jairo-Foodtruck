<template>
  <div class="search-section">
    <div class="divider"></div>
    <div class="search-controls">
      
      <div class="inputs-group">
        <button
          class="badge-button color-papas"
          :class="{ 'is-active': props.modelValue === 'Papas & Chorrillanas' }"
          @click="toggleCategory('Papas & Chorrillanas')"
        >
          <span class="badge-text">Papas &<br>Chorrillanas</span>
        </button>
        
        <button
          class="badge-button color-vianesas"
          :class="{ 'is-active': props.modelValue === 'Vianesas' }"
          @click="toggleCategory('Vianesas')"
        >
          <span class="badge-text">Vianesas</span>
        </button>

        <button
          class="badge-button color-sanguches"
          :class="{ 'is-active': props.modelValue === 'Sánguches / Bajones' }"
          @click="toggleCategory('Sánguches / Bajones')"
        >
          <span class="badge-text">Sánguches /<br>Bajones</span>
        </button>

        <button
          class="badge-button color-promos"
          :class="{ 'is-active': props.modelValue === 'Promos/Combos' }"
          @click="toggleCategory('Promos/Combos')"
        >
          <span class="badge-text">Promos/<br>Combos</span>
        </button>
        
        <button
          class="badge-button color-masas"
          :class="{ 'is-active': props.modelValue === 'Masas' }"
          @click="toggleCategory('Masas')"
        >
          <span class="badge-text">Masas</span>
        </button>

        <button
          class="badge-button color-bebestibles"
          :class="{ 'is-active': props.modelValue === 'Bebestibles' }"
          @click="toggleCategory('Bebestibles')"
        >
          <span class="badge-text">Bebestibles</span>
        </button>

        <div class="search-input-wrapper">
          <input 
            type="text" 
            :value="searchQuery"
            @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
            placeholder="Busca tu bajon" 
            class="search-input" 
          />
          <Search class="search-icon" :size="20" />
        </div>
      </div>
    </div>
    <div class="divider"></div>
  </div>
</template>

<script setup lang="ts">
import { Search } from 'lucide-vue-next';

const props = defineProps<{
  modelValue: string;
  searchQuery: string; 
  categories: any[];
}>();

const emit = defineEmits(['update:modelValue', 'update:searchQuery']);

const toggleCategory = (category: string) => {
  emit('update:modelValue', props.modelValue === category ? 'Todas' : category);
};
</script>

<style scoped>

.search-section {
  margin-bottom: 30px;
  width: 100%;
}

.search-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 0;
}

.inputs-group {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap; 
  width: 100%;
}

/* 🌟 EL SECRETO: El botón usa la estrella SVG matemática como máscara de fondo.
   Esto genera un círculo de picos perfectamente simétrico que nunca se deforma. */
.badge-button {
  position: relative;
  width: 120px;
  height: 120px;
  border: none;
  background: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  transition: transform 0.2s ease;
  filter: drop-shadow(0px 4px 6px rgba(0, 0, 0, 0.3)); /* Sombra real que sigue los picos */
}

.badge-button:hover {
  transform: scale(1.08) rotate(2deg);
}

.badge-button.is-active {
  transform: scale(1.05);
  filter: drop-shadow(0px 6px 10px rgba(255, 231, 10, 0.85) );
}

.badge-button.is-active::after {
  box-shadow: inset 0 0 0 3px rgba(255, 255, 255, 0.85);
}

/* Capa del borde negro */
.badge-button::before {
  content: '';
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background-color: #000000;
  /* SVG de una estrella de 24 puntas perfecta */
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  z-index: 1;
}

/* Capa del contenido con el Degradado (un poco más pequeña para simular el contorno) */
.badge-button::after {
  content: '';
  position: absolute;
  top: 4px; left: 4px; right: 4px; bottom: 4px;
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  z-index: 2;
}

/* El texto por encima de los fondos gráficos */
.badge-text {
  position: relative;
  z-index: 3;
  color: #ffffff;
  font-family: 'Open Sans'; /* Tipografía ultra-gruesa de comida rápida */
  font-size: 0.85rem;
  font-weight: 900;
  text-align: center;
  line-height: 1.1;
  padding: 10px;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
  pointer-events: none;
}

/* 🎨 DEGRADADOS PERFECTOS (Se aplican a la capa interna ::after) */
.color-papas::after { background: linear-gradient(135deg, #e43351 0%, #f65c52 50%, #f67c46 100%); }
.color-vianesas::after { background: linear-gradient(135deg, #ff9100, #ff6d00); }
.color-sanguches::after { background: linear-gradient(135deg, #00b0ff, #0072ff); }
.color-promos::after { background: linear-gradient(135deg, #00e676, #00a200); }
.color-masas::after { background: linear-gradient(135deg, #7c4dff, #651fff); }
.color-bebestibles::after { background: linear-gradient(135deg, #d80056 0%, #e60045 50%, #f5003b 100%); }

/* Buscador e Input */
.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-left: auto;
}

.search-input {
  width: 250px; 
  padding: 10px 40px 10px 15px;
  border-radius: 20px;
  border: 2px solid #5a3614;
  background-color: white;
  outline: none;
  font-size: 0.9rem;
}

.search-icon {
  position: absolute;
  right: 12px;
  color: #5a3614;
  pointer-events: none; 
}

.divider {
  height: 3px;
  background-color: #5a3614;
  width: 100%;
  margin: 15px 0;
}

@media (max-width: 768px) {
  .search-input-wrapper {
    width: 100%;
    margin-left: 0;
    margin-top: 10px;
  }
  .search-input {
    width: 100%;
  }
  .inputs-group {
    justify-content: center;
  }
}
</style>