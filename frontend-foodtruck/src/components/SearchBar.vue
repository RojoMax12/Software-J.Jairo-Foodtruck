<template>
  <div class="search-section">
    <div class="divider"></div>
    
    <div class="search-controls">
      
      <!-- 🔍 BUSCADOR DE TEXTO PRINCIPAL -->
      <div class="search-input-wrapper">
        <Search class="search-icon" :size="20" />
        <input 
          type="text" 
          :value="searchQuery"
          @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
          placeholder="¿Qué se te antoja hoy? Busca por nombre o ingrediente..." 
          class="search-input" 
        />
        <button 
          v-if="searchQuery" 
          class="clear-search-btn" 
          @click="emit('update:searchQuery', '')"
          title="Limpiar búsqueda"
          aria-label="Limpiar búsqueda"
        >
          <X :size="16" />
        </button>
      </div>

      <!-- 🏷️ BARRA DE FILTRO ACTIVO -->
      <Transition name="fade-slide">
        <div v-if="props.modelValue && props.modelValue !== 'Todas'" class="active-filter-bar">
          <span class="active-filter-text">
            Categoría activa: <strong>{{ props.modelValue }}</strong>
          </span>
          <button class="clear-filter-btn" @click="toggleCategory('Todas')">
            <X :size="14" />
            <span>Ver todas las categorías</span>
          </button>
        </div>
      </Transition>

      <!-- 🍟 CATEGORÍAS (EN PC: FILA CENTRADA Y ELEGANTE. EN MÓVIL: CARRUSEL TÁCTIL) -->
      <div class="categories-scroll-container">
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
        </div>
      </div>

    </div>

    <div class="divider"></div>
  </div>
</template>

<script setup lang="ts">
import { Search, X } from 'lucide-vue-next';

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
  margin-bottom: 25px;
  width: 100%;
}

.search-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 18px;
  padding: 10px 0;
  width: 100%;
  box-sizing: border-box;
}

/* 🔍 BUSCADOR DE TEXTO PRINCIPAL */
.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  width: 100%;
  max-width: 560px;
  margin: 0 auto;
}

.search-input {
  width: 100%; 
  padding: 14px 44px 14px 48px;
  border-radius: 50px;
  border: 2px solid #5a3614;
  background-color: #ffffff;
  outline: none;
  font-size: 1rem;
  font-weight: 600;
  color: var(--DC-gray, #322c44);
  box-shadow: 0 4px 14px rgba(90, 54, 20, 0.08);
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  box-sizing: border-box;
}

.search-input:focus {
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 6px 20px rgba(226, 135, 67, 0.25);
}

.search-input::placeholder {
  color: #a09aa8;
  font-weight: 500;
}

.search-icon {
  position: absolute;
  left: 18px;
  color: #5a3614;
  pointer-events: none; 
  transition: color 0.2s;
}

.search-input:focus ~ .search-icon {
  color: var(--DC-orange, #e28743);
}

.clear-search-btn {
  position: absolute;
  right: 14px;
  background: #f1ede8;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #513119;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-search-btn:hover {
  background-color: var(--DC-orange, #e28743);
  color: white;
  transform: scale(1.1);
}

/* 🏷️ BARRA DE FILTRO ACTIVO */
.active-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #fff6ee;
  border: 1.5px solid #fcd3b2;
  padding: 8px 18px;
  border-radius: 50px;
  font-size: 0.88rem;
  color: #513119;
  width: 100%;
  max-width: 560px;
  box-sizing: border-box;
}

.active-filter-text strong {
  color: var(--DC-orange, #e28743);
  font-weight: 800;
}

.clear-filter-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: white;
  border: 1.5px solid #e28743;
  color: #e28743;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 800;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-filter-btn:hover {
  background: #e28743;
  color: white;
  transform: scale(1.04);
}

/* 🍟 CONTENEDOR DE CATEGORÍAS EN PC (FILA ESPACIOSA Y CENTRADA) */
.categories-scroll-container {
  width: 100%;
  display: flex;
  justify-content: center;
  overflow: visible;
  padding-top: 4px;
}

.inputs-group {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  flex-wrap: wrap;
  width: 100%;
}

.badge-button {
  position: relative;
  width: 115px;
  height: 115px;
  border: none;
  background: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  filter: drop-shadow(0px 4px 8px rgba(0, 0, 0, 0.2));
}

.badge-button:hover {
  transform: translateY(-4px) scale(1.06) rotate(1.5deg);
  filter: drop-shadow(0px 8px 16px rgba(0, 0, 0, 0.3));
}

.badge-button.is-active {
  transform: scale(1.06);
  filter: drop-shadow(0px 6px 14px rgba(255, 200, 10, 0.95));
}

.badge-button::before {
  content: '';
  position: absolute;
  top: 0; left: 0; width: 100%; height: 100%;
  background-color: #000000;
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  z-index: 1;
}

.badge-button::after {
  content: '';
  position: absolute;
  top: 4px; left: 4px; right: 4px; bottom: 4px;
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  z-index: 2;
}

.badge-text {
  position: relative;
  z-index: 3;
  color: #ffffff;
  font-family: 'Open Sans', sans-serif;
  font-size: 0.85rem;
  font-weight: 900;
  text-align: center;
  line-height: 1.1;
  padding: 8px;
  text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);
  pointer-events: none;
}

.color-papas::after { background: linear-gradient(135deg, #e43351 0%, #f65c52 50%, #f67c46 100%); }
.color-vianesas::after { background: linear-gradient(135deg, #ff9100, #ff6d00); }
.color-sanguches::after { background: linear-gradient(135deg, #00b0ff, #0072ff); }
.color-promos::after { background: linear-gradient(135deg, #00e676, #00a200); }
.color-masas::after { background: linear-gradient(135deg, #7c4dff, #651fff); }
.color-bebestibles::after { background: linear-gradient(135deg, #d80056 0%, #e60045 50%, #f5003b 100%); }

.divider {
  height: 3px;
  background-color: #5a3614;
  width: 100%;
  margin: 12px 0;
}

/* ANIMACIONES */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.25s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(-6px); }

/* 📱 DISEÑO MÓVIL (PANTALLAS PEQUEÑAS < 768px): BUSCADOR 100% Y CARRUSEL TÁCTIL ABAJO */
@media (max-width: 768px) {
  .search-controls {
    gap: 12px;
    padding: 6px 0;
  }

  .search-input-wrapper {
    max-width: 100%;
  }

  .search-input {
    padding: 13px 40px 13px 44px;
    font-size: 0.94rem;
    border-radius: 20px;
  }

  .search-icon {
    left: 14px;
    width: 18px;
    height: 18px;
  }

  .categories-scroll-container {
    width: 100%;
    overflow-x: auto;
    justify-content: flex-start;
    padding: 2px 0 6px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
  }

  .categories-scroll-container::-webkit-scrollbar {
    display: none;
  }

  .inputs-group {
    flex-wrap: nowrap;
    justify-content: flex-start;
    width: max-content;
    gap: 10px;
  }

  .badge-button {
    width: 82px;
    height: 82px;
  }

  .badge-text {
    font-size: 0.68rem;
    padding: 4px;
    line-height: 1.05;
  }

  .active-filter-bar {
    max-width: 100%;
    border-radius: 16px;
    padding: 6px 12px;
    font-size: 0.82rem;
  }

  .divider {
    margin: 8px 0;
  }
}
</style>