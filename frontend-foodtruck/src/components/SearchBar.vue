<template>
  <div class="search-section">
    <div class="divider"></div>
    
    <div class="search-controls">
      <!-- 🔍 FILA SUPERIOR: BUSCADOR DE TEXTO + SELECTOR RÁPIDO DE CATEGORÍAS -->
      <div class="search-top-row">
        <div class="search-input-wrapper">
          <Search class="search-icon" :size="18" />
          <input 
            type="text" 
            :value="searchQuery"
            @input="onSearchInput"
            placeholder="¿Qué se te antoja hoy? Busca por nombre o ingrediente..." 
            class="search-input" 
            aria-label="Buscar producto o ingrediente"
          />
          <button 
            v-if="searchQuery" 
            type="button"
            class="clear-search-btn" 
            @click="clearSearch"
            title="Limpiar búsqueda"
            aria-label="Limpiar búsqueda"
          >
            <X :size="15" />
          </button>
        </div>

        <!-- 📂 SELECTOR DIRECTO DE CATEGORÍAS (DROPDOWN) -->
        <div class="category-dropdown-wrapper">
          <button 
            type="button" 
            class="category-dropdown-trigger"
            :class="{ 'is-active-filter': props.modelValue !== 'Todas' }"
            @click.stop="toggleDropdown"
            :aria-expanded="isCategoryDropdownOpen"
            title="Ver y filtrar todas las categorías"
          >
            <Filter :size="15" class="dropdown-filter-icon" />
            <span class="dropdown-trigger-text">{{ currentSelectedLabel }}</span>
            <ChevronDown :size="14" class="dropdown-arrow-icon" :class="{ 'is-flipped': isCategoryDropdownOpen }" />
          </button>

          <!-- MENÚ DESPLEGABLE DE TODAS LAS CATEGORÍAS -->
          <Transition name="fade-slide">
            <div v-if="isCategoryDropdownOpen" class="category-dropdown-popover" @click.stop>
              <div class="dropdown-popover-header">
                <span>Categorías de la carta</span>
                <button type="button" class="close-popover-btn" @click="closeDropdown" title="Cerrar">
                  <X :size="13" />
                </button>
              </div>

              <div class="dropdown-items-list">
                <!-- Opción: Todas las categorías -->
                <button
                  type="button"
                  class="dropdown-item"
                  :class="{ 'is-selected': props.modelValue === 'Todas' }"
                  @click="selectCategoryFromDropdown('Todas')"
                >
                  <div class="dropdown-item-info">
                    <span class="cat-dot-icon">✨</span>
                    <span class="cat-item-name">Todas las categorías</span>
                  </div>
                  <Check v-if="props.modelValue === 'Todas'" :size="14" class="cat-item-check" />
                </button>

                <!-- Opción: Promos / Combos -->
                <button
                  type="button"
                  class="dropdown-item promo-dropdown-item"
                  :class="{ 'is-selected': props.modelValue === 'Promos' || props.modelValue === 'Promos/Combos' }"
                  @click="selectCategoryFromDropdown('Promos')"
                >
                  <div class="dropdown-item-info">
                    <span class="cat-dot-icon">🔥</span>
                    <span class="cat-item-name">Promos / Combos</span>
                    <span v-if="(props.promotionsCount || 0) > 0" class="promo-count-pill">
                      {{ props.promotionsCount }}
                    </span>
                  </div>
                  <Check v-if="props.modelValue === 'Promos' || props.modelValue === 'Promos/Combos'" :size="14" class="cat-item-check" />
                </button>

                <div class="dropdown-divider"></div>

                <!-- Lista de categorías de la BD -->
                <button
                  v-for="(cat, idx) in displayCategories"
                  :key="'dd-cat-' + (cat.id || cat.nombre_categoria)"
                  type="button"
                  class="dropdown-item"
                  :class="{ 'is-selected': props.modelValue === cat.nombre_categoria }"
                  @click="selectCategoryFromDropdown(cat.nombre_categoria)"
                >
                  <div class="dropdown-item-info">
                    <span 
                      class="cat-color-dot"
                      :style="{ background: getCategoryGradient(cat, idx) }"
                    ></span>
                    <span class="cat-item-name">{{ cat.nombre_categoria }}</span>
                  </div>
                  <Check v-if="props.modelValue === cat.nombre_categoria" :size="14" class="cat-item-check" />
                </button>
              </div>
            </div>
          </Transition>
        </div>
      </div>

      <!-- 💡 CHIPS DE SUGERENCIA CUANDO EL TEXTO COINCIDE CON UNA CATEGORÍA -->
      <Transition name="fade-slide">
        <div v-if="categorySuggestions.length > 0" class="category-suggestions-bar">
          <span class="suggestions-label">🎯 Filtrar categoría:</span>
          <div class="suggestions-chips">
            <button
              v-for="sug in categorySuggestions"
              :key="'sug-' + sug.nombre_categoria"
              type="button"
              class="suggestion-chip"
              @click="applyCategorySuggestion(sug.nombre_categoria)"
            >
              <span>{{ sug.nombre_categoria }}</span>
            </button>
          </div>
        </div>
      </Transition>

      <!-- 🏷️ BARRA DE FILTRO ACTIVO -->
      <Transition name="fade-slide">
        <div v-if="props.modelValue && props.modelValue !== 'Todas'" class="active-filter-bar">
          <span class="active-filter-text">
            Categoría activa: <strong>{{ props.modelValue }}</strong>
          </span>
          <button type="button" class="clear-filter-btn" @click="toggleCategory('Todas')">
            <X :size="13" />
            <span>Ver todas las categorías</span>
          </button>
        </div>
      </Transition>

      <!-- 🍟 CARRUSEL DE CATEGORÍAS EN FILA ÚNICA CON NAVEGACIÓN DESKTOP Y SWIPE MÓVIL -->
      <div 
        class="categories-carousel-wrapper"
        :class="{
          'has-scroll-left': canScrollLeft,
          'has-scroll-right': canScrollRight
        }"
      >
        <!-- Flecha Anterior (Desktop) -->
        <Transition name="fade-scale">
          <button
            v-if="canScrollLeft"
            type="button"
            class="carousel-nav-btn prev-btn"
            aria-label="Ver categorías anteriores"
            title="Categorías anteriores"
            @click="scrollCategories(-1)"
          >
            <ChevronLeft :size="20" :stroke-width="2.5" />
          </button>
        </Transition>

        <!-- Track horizontal de 1 sola fila con scroll suave -->
        

        <!-- Flecha Siguiente (Desktop) -->
        <Transition name="fade-scale">
          <button
            v-if="canScrollRight"
            type="button"
            class="carousel-nav-btn next-btn"
            aria-label="Ver más categorías"
            title="Más categorías"
            @click="scrollCategories(1)"
          >
            <ChevronRight :size="20" :stroke-width="2.5" />
          </button>
        </Transition>
      </div>
    </div>

    <div class="divider"></div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import { Search, X, ChevronLeft, ChevronRight, Filter, ChevronDown, Check } from 'lucide-vue-next';

const props = defineProps<{
  modelValue: string;
  searchQuery: string; 
  categories: any[];
  promotionsCount?: number;
  availableCategoryNames?: string[] | Set<string>;
}>();

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'update:searchQuery', value: string): void;
}>();

const scrollContainerRef = ref<HTMLElement | null>(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

const updateScrollButtons = () => {
  const el = scrollContainerRef.value;
  if (!el) return;
  canScrollLeft.value = el.scrollLeft > 6;
  canScrollRight.value = el.scrollLeft + el.clientWidth < el.scrollWidth - 6;
};

const scrollCategories = (direction: number) => {
  const el = scrollContainerRef.value;
  if (!el) return;
  const scrollAmount = Math.max(260, Math.floor(el.clientWidth * 0.65)) * direction;
  el.scrollBy({ left: scrollAmount, behavior: 'smooth' });
};

const scrollToActiveCategory = () => {
  nextTick(() => {
    const el = scrollContainerRef.value;
    if (!el) return;
    const activeBtn = el.querySelector('.badge-button.is-active') as HTMLElement | null;
    if (activeBtn) {
      activeBtn.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
  });
};

// Excluir categoría 'Promos / Combos' y categorías sin productos activos del carrusel
const displayCategories = computed(() => {
  if (!props.categories || !props.categories.length) return [];
  const filterNames = props.availableCategoryNames;

  return props.categories.filter((cat: any) => {
    const rawName = String(cat.nombre_categoria || '').trim();
    const nameLow = rawName.toLowerCase();
    if (nameLow.includes('promo') || nameLow.includes('combo')) return false;

    if (filterNames) {
      if (filterNames instanceof Set) {
        return filterNames.has(nameLow) || filterNames.has(rawName);
      }
      if (Array.isArray(filterNames) && filterNames.length > 0) {
        return filterNames.some((c: string) => c.toLowerCase() === nameLow);
      }
    }

    return true;
  });
});

// Paleta de gradientes conocidos para categorías estándar
const KNOWN_GRADIENTS: Record<string, string> = {
  'papas & chorrillanas': 'linear-gradient(135deg, #e43351 0%, #f65c52 50%, #f67c46 100%)',
  'vianesas': 'linear-gradient(135deg, #ff9100, #ff6d00)',
  'as': 'linear-gradient(135deg, #c0392b, #962d22)',
  'churrascos': 'linear-gradient(135deg, #00b0ff, #0072ff)',
  'lomitos': 'linear-gradient(135deg, #8e44ad, #6c3483)',
  'hamburguesas': 'linear-gradient(135deg, #27ae60, #1e8449)',
  'pizzas': 'linear-gradient(135deg, #8b5cf6, #6d28d9)',
  'fajitas': 'linear-gradient(135deg, #16a085, #117a65)',
  'sándwiches de pollo': 'linear-gradient(135deg, #2980b9, #1f618d)',
  'handrolls & arrollados': 'linear-gradient(135deg, #06b6d4, #0891b2)',
  'empanadas & sopaipillas': 'linear-gradient(135deg, #e67e22, #d35400)',
  'bebidas frías': 'linear-gradient(135deg, #ec4899 0%, #d80056 100%)',
  'bebidas calientes': 'linear-gradient(135deg, #e74c3c, #c0392b)',
};

// Paleta dinámica para cualquier nueva categoría creada por API/Admin
const DYNAMIC_PALETTE = [
  'linear-gradient(135deg, #ff9100, #ff6d00)',
  'linear-gradient(135deg, #00b0ff, #0072ff)',
  'linear-gradient(135deg, #e43351 0%, #f65c52 50%, #f67c46 100%)',
  'linear-gradient(135deg, #8b5cf6, #6d28d9)',
  'linear-gradient(135deg, #ec4899 0%, #d80056 100%)',
  'linear-gradient(135deg, #10b981, #059669)',
  'linear-gradient(135deg, #27ae60, #1e8449)',
  'linear-gradient(135deg, #f59e0b, #d97706)',
  'linear-gradient(135deg, #06b6d4, #0891b2)',
  'linear-gradient(135deg, #6366f1, #4338ca)',
  'linear-gradient(135deg, #14b8a6, #0f766e)',
  'linear-gradient(135deg, #f43f5e, #be123c)'
];

const getCategoryGradient = (cat: any, idx: number): string => {
  const nameLow = String(cat.nombre_categoria || '').toLowerCase().trim();
  const known = KNOWN_GRADIENTS[nameLow];
  if (known) {
    return known;
  }
  const id = Number(cat.id ?? cat.id_categoria);
  if (!isNaN(id) && id > 0) {
    return DYNAMIC_PALETTE[(id - 1) % DYNAMIC_PALETTE.length] ?? DYNAMIC_PALETTE[0]!;
  }
  return DYNAMIC_PALETTE[idx % DYNAMIC_PALETTE.length] ?? DYNAMIC_PALETTE[0]!;
};

// Títulos amigables y legibles para los badges redondos
const SHORT_NAMES: Record<string, string> = {
  'Papas & Chorrillanas': 'Papas',
  'Empanadas & Sopaipillas': 'Empanadas',
  'Sándwiches de Pollo': 'Pollo',
  'Bebidas frías': 'Bebidas Frías',
  'Bebidas calientes': 'Bebidas Calientes',
  'Handrolls & Arrollados': 'Handrolls'
};

const formatCategoryName = (name: string): string => {
  if (!name) return '';
  const clean = name.trim();
  return SHORT_NAMES[clean] || clean;
};

const toggleCategory = (category: string) => {
  emit('update:modelValue', props.modelValue === category ? 'Todas' : category);
};

// 📂 Estado y acciones del menú desplegable de categorías
const isCategoryDropdownOpen = ref(false);

const toggleDropdown = () => {
  isCategoryDropdownOpen.value = !isCategoryDropdownOpen.value;
};

const closeDropdown = () => {
  isCategoryDropdownOpen.value = false;
};

const currentSelectedLabel = computed(() => {
  if (!props.modelValue || props.modelValue === 'Todas') {
    return `Categorías (${displayCategories.value.length})`;
  }
  if (props.modelValue === 'Promos' || props.modelValue === 'Promos/Combos') {
    return '🔥 Promos';
  }
  return formatCategoryName(props.modelValue);
});

const selectCategoryFromDropdown = (catName: string) => {
  emit('update:modelValue', catName);
  isCategoryDropdownOpen.value = false;
  if (catName !== 'Todas') {
    scrollToActiveCategory();
  }
};

// 🎯 Sugerencias de categorías mientras el usuario escribe en el buscador
const categorySuggestions = computed(() => {
  const query = props.searchQuery?.trim().toLowerCase();
  if (!query || query.length < 2) return [];

  const currentCat = (props.modelValue || '').toLowerCase();
  return displayCategories.value.filter((cat: any) => {
    const catName = String(cat.nombre_categoria || '').toLowerCase();
    const isAlreadySelected = currentCat === catName;
    return !isAlreadySelected && catName.includes(query);
  }).slice(0, 3);
});

const applyCategorySuggestion = (catName: string) => {
  emit('update:modelValue', catName);
  emit('update:searchQuery', '');
  scrollToActiveCategory();
};

const onDocumentClick = (e: MouseEvent) => {
  const target = e.target as HTMLElement;
  if (!target.closest('.category-dropdown-wrapper')) {
    isCategoryDropdownOpen.value = false;
  }
};

// Búsqueda con debounce para evitar re-renderizados continuos en cada tecla
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

const onSearchInput = (event: Event) => {
  const value = (event.target as HTMLInputElement).value;
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    emit('update:searchQuery', value);
  }, 200);
};

const clearSearch = () => {
  if (debounceTimer) clearTimeout(debounceTimer);
  emit('update:searchQuery', '');
};

// Auto-centrar la categoría activa al cambiar
watch(() => props.modelValue, () => {
  scrollToActiveCategory();
});

// Actualizar estado de las flechas cuando cambia la lista de categorías
watch(() => displayCategories.value, () => {
  nextTick(() => {
    updateScrollButtons();
  });
}, { immediate: true });

onMounted(() => {
  nextTick(() => {
    updateScrollButtons();
    scrollToActiveCategory();
  });
  window.addEventListener('resize', updateScrollButtons);
  document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
  if (debounceTimer) clearTimeout(debounceTimer);
  window.removeEventListener('resize', updateScrollButtons);
  document.removeEventListener('click', onDocumentClick);
});
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.search-section {
  margin-bottom: 1.5rem;
  width: 100%;
}

.search-controls {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.15rem;
  padding: 0.65rem 0;
  width: 100%;
}

/* 🔍 FILA SUPERIOR: BUSCADOR + SELECTOR DE CATEGORÍAS */
.search-top-row {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  width: 100%;
  max-width: 760px;
  margin: 0 auto;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1;
  min-width: 0;
}

.search-input {
  width: 100%; 
  padding: 0.82rem 2.75rem 0.82rem 2.85rem;
  border-radius: 999px;
  border: 1.5px solid rgba(81, 49, 25, 0.2);
  background-color: #ffffff;
  outline: none;
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--DC-gray, #2c2724);
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.05);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  font-family: inherit;
}

.search-input:focus {
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.18), 0 6px 20px rgba(226, 135, 67, 0.12);
}

.search-input::placeholder {
  color: var(--DC-text-gray, #7c7468);
  font-weight: 500;
  opacity: 0.85;
}

.search-icon {
  position: absolute;
  left: 1.15rem;
  color: var(--DC-brown, #513119);
  pointer-events: none; 
  transition: color 0.2s ease;
}

.search-input:focus ~ .search-icon {
  color: var(--DC-orange, #e28743);
}

.clear-search-btn {
  position: absolute;
  right: 0.85rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.1);
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--DC-brown, #513119);
  cursor: pointer;
  transition: all 0.2s ease;
}

.clear-search-btn:hover {
  background-color: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
  transform: scale(1.08);
}

/* 📂 SELECTOR DIRECTO DE CATEGORÍAS (DROPDOWN) */
.category-dropdown-wrapper {
  position: relative;
  flex-shrink: 0;
}

.category-dropdown-trigger {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: #ffffff;
  border: 1.5px solid rgba(81, 49, 25, 0.2);
  border-radius: 999px;
  padding: 0.78rem 1.15rem;
  font-size: 0.88rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.05);
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  white-space: nowrap;
}

.category-dropdown-trigger:hover {
  border-color: var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(226, 135, 67, 0.15);
}

.category-dropdown-trigger.is-active-filter {
  background: #fff4ea;
  border-color: var(--DC-orange, #e28743);
  color: #c0392b;
}

.dropdown-filter-icon {
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
}

.dropdown-trigger-text {
  max-width: 150px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.dropdown-arrow-icon {
  color: var(--DC-text-gray, #7c7468);
  transition: transform 0.2s ease;
  flex-shrink: 0;
}

.dropdown-arrow-icon.is-flipped {
  transform: rotate(180deg);
}

.category-dropdown-popover {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  width: 290px;
  max-height: 400px;
  background: #ffffff;
  border-radius: 16px;
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 16px 45px rgba(26, 14, 5, 0.18);
  z-index: 100;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.dropdown-popover-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem 1rem 0.6rem 1rem;
  font-size: 0.8rem;
  font-weight: 800;
  color: var(--DC-text-gray, #7c7468);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  background: #faf8f5;
}

.close-popover-btn {
  background: none;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: flex;
  padding: 4px;
  border-radius: 6px;
  transition: all 0.15s ease;
}

.close-popover-btn:hover {
  background: rgba(81, 49, 25, 0.08);
  color: var(--DC-brown, #513119);
}

.dropdown-items-list {
  overflow-y: auto;
  max-height: 330px;
  padding: 0.45rem;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.dropdown-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.55rem 0.75rem;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
  width: 100%;
  text-align: left;
}

.dropdown-item:hover {
  background: #fdf5ec;
  color: var(--DC-orange, #e28743);
}

.dropdown-item.is-selected {
  background: #fff0e2;
  color: #c0392b;
  font-weight: 800;
}

.dropdown-item-info {
  display: flex;
  align-items: center;
  gap: 9px;
  flex: 1;
  min-width: 0;
}

.cat-dot-icon {
  font-size: 0.95rem;
  flex-shrink: 0;
}

.cat-color-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.cat-item-name {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.cat-item-check {
  color: #c0392b;
  flex-shrink: 0;
}

.promo-count-pill {
  background: var(--DC-pink, #d80056);
  color: #ffffff;
  font-size: 0.65rem;
  font-weight: 900;
  padding: 1px 6px;
  border-radius: 999px;
  margin-left: 4px;
}

.dropdown-divider {
  height: 1px;
  background: rgba(81, 49, 25, 0.08);
  margin: 4px 6px;
}

/* 🎯 CHIPS DE SUGERENCIA DE CATEGORÍA */
.category-suggestions-bar {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
  width: 100%;
  max-width: 760px;
  margin: -0.4rem auto 0 auto;
  padding: 0 0.5rem;
}

.suggestions-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

.suggestions-chips {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  flex-wrap: wrap;
}

.suggestion-chip {
  display: inline-flex;
  align-items: center;
  background: #ffffff;
  border: 1.5px solid var(--DC-orange, #e28743);
  color: var(--DC-brown, #513119);
  padding: 0.25rem 0.7rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 6px rgba(226, 135, 67, 0.12);
  transition: all 0.18s ease;
}

.suggestion-chip:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  transform: translateY(-1px);
  box-shadow: 0 4px 10px rgba(226, 135, 67, 0.25);
}

/* 🏷️ BARRA DE FILTRO ACTIVO */
.active-filter-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.75rem;
  background-color: #fffdfa;
  border: 1.5px solid rgba(226, 135, 67, 0.35);
  padding: 0.55rem 1.15rem;
  border-radius: 999px;
  font-size: 0.84rem;
  color: var(--DC-brown, #513119);
  width: 100%;
  max-width: 580px;
}

.active-filter-text strong {
  color: var(--DC-orange, #e28743);
  font-weight: 800;
}

.clear-filter-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: #ffffff;
  border: 1.5px solid var(--DC-orange, #e28743);
  color: var(--DC-orange, #e28743);
  padding: 0.3rem 0.8rem;
  border-radius: 999px;
  font-weight: 800;
  font-size: 0.74rem;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
}

.clear-filter-btn:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
  transform: translateY(-1px);
}

/* 🍟 CARRUSEL DE CATEGORÍAS EN FILA ÚNICA */
.categories-carousel-wrapper {
  position: relative;
  width: 100%;
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  align-items: center;
  padding: 0 0.5rem;
}

.carousel-nav-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 12;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #ffffff;
  border: 1.5px solid rgba(81, 49, 25, 0.15);
  box-shadow: 0 4px 14px rgba(26, 14, 5, 0.16);
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.carousel-nav-btn:hover {
  background-color: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
  transform: translateY(-50%) scale(1.1);
  box-shadow: 0 6px 18px rgba(226, 135, 67, 0.4);
}

.carousel-nav-btn:active {
  transform: translateY(-50%) scale(0.94);
}

.carousel-nav-btn.prev-btn {
  left: -12px;
}

.carousel-nav-btn.next-btn {
  right: -12px;
}

.categories-scroll-container {
  width: 100%;
  display: flex;
  overflow-x: auto;
  overflow-y: hidden;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  padding: 0.5rem 0.25rem 0.75rem 0.25rem;
}

.categories-scroll-container::-webkit-scrollbar {
  display: none;
}

.inputs-group {
  display: flex;
  align-items: center;
  justify-content: safe center;
  gap: 0.85rem;
  flex-wrap: nowrap; /* FILA ÚNICA: NUNCA SE EXTIENDE HACIA ABAJO */
  width: max-content;
  min-width: 100%;
  padding: 0 0.5rem;
}

.badge-button {
  position: relative;
  width: 82px;
  height: 82px;
  border: none;
  background: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  flex-shrink: 0;
  transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
  filter: drop-shadow(0 4px 10px rgba(26, 14, 5, 0.18));
}

.badge-button:hover {
  transform: translateY(-4px) scale(1.05) rotate(1deg);
  filter: drop-shadow(0 8px 16px rgba(26, 14, 5, 0.25));
}

.badge-button.is-active {
  transform: scale(1.06);
  filter: drop-shadow(0 6px 16px rgba(226, 135, 67, 0.65));
}

.badge-button::before {
  content: '';
  position: absolute;
  inset: 0;
  background-color: #1a0f05;
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  z-index: 1;
}

.badge-button::after {
  content: '';
  position: absolute;
  inset: 4px;
  mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  -webkit-mask: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><path d='M50 0 L55 9 L65 3 L67 13 L77 10 L76 21 L86 21 L83 31 L92 34 L87 43 L94 49 L87 56 L92 65 L83 68 L86 78 L76 78 L77 89 L67 86 L65 96 L55 90 L50 100 L45 90 L35 96 L33 86 L23 89 L24 78 L14 78 L17 68 L8 65 L13 56 L6 49 L13 43 L8 34 L17 31 L14 21 L24 21 L23 10 L33 13 L35 3 L45 9 Z' /></svg>") no-repeat center / contain;
  background: var(--badge-gradient, linear-gradient(135deg, #ff9100, #ff6d00));
  z-index: 2;
}

.badge-text {
  position: relative;
  z-index: 3;
  color: #ffffff;
  font-family: inherit;
  font-size: 0.72rem;
  font-weight: 800;
  text-align: center;
  line-height: 1.15;
  padding: 4px;
  text-shadow: 0 1px 3px rgba(0, 0, 0, 0.6);
  pointer-events: none;
  user-select: none;
  word-break: break-word;
  max-width: 74px;
}

.badge-fire-tag {
  position: absolute;
  top: -5px;
  right: -3px;
  z-index: 10;
  background: var(--DC-pink, #d80056);
  color: #ffffff;
  font-size: 0.68rem;
  font-weight: 900;
  padding: 2px 7px;
  border-radius: 999px;
  box-shadow: 0 2px 6px rgba(216, 0, 86, 0.4);
  border: 1.5px solid #ffffff;
  animation: pulse-fire 2s infinite ease-in-out;
}

@keyframes pulse-fire {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}

.color-promos::after { 
  background: linear-gradient(135deg, #10b981, #059669); 
}

.divider {
  height: 1.5px;
  background-color: rgba(81, 49, 25, 0.12);
  width: 100%;
  margin: 0.75rem 0;
}

/* ANIMACIONES */
.fade-slide-enter-active, 
.fade-slide-leave-active { 
  transition: opacity 0.2s ease, transform 0.2s ease; 
}

.fade-slide-enter-from, 
.fade-slide-leave-to { 
  opacity: 0; 
  transform: translateY(-4px); 
}

.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0; 
  transform: translateY(-50%) scale(0.7);
}

/* 📱 RESPONSIVO MÓVIL */
@media (max-width: 768px) {
  .carousel-nav-btn {
    display: none !important;
  }

  .categories-carousel-wrapper {
    padding: 0;
    max-width: 100%;
  }

  .search-controls {
    gap: 0.85rem;
    padding: 0.35rem 0;
  }

  .search-top-row {
    flex-direction: column;
    width: 100%;
    gap: 0.6rem;
  }

  .category-dropdown-wrapper {
    width: 100%;
  }

  .category-dropdown-trigger {
    width: 100%;
    justify-content: center;
    padding: 0.65rem 1rem;
  }

  .dropdown-trigger-text {
    max-width: none;
  }

  .category-dropdown-popover {
    right: 0;
    left: 0;
    width: 100%;
  }

  .search-input {
    padding: 0.75rem 2.5rem 0.75rem 2.6rem;
    font-size: 0.9rem;
  }

  .search-icon {
    left: 1rem;
    width: 16px;
    height: 16px;
  }

  .categories-scroll-container {
    width: 100vw;
    margin-left: calc(-50vw + 50%);
    overflow-x: auto;
    justify-content: flex-start;
    padding: 0.35rem 1rem 0.75rem 1rem;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }

  .categories-scroll-container::-webkit-scrollbar {
    display: none;
  }

  .inputs-group {
    flex-wrap: nowrap;
    justify-content: flex-start;
    width: max-content;
    gap: 0.55rem;
  }

  .badge-button {
    width: 72px;
    height: 72px;
  }

  .badge-text {
    font-size: 0.64rem;
    padding: 2px;
    max-width: 62px;
  }

  .active-filter-bar {
    max-width: 100%;
    border-radius: 14px;
    padding: 0.45rem 0.85rem;
    font-size: 0.78rem;
  }

  .divider {
    margin: 0.5rem 0;
  }
}
</style>