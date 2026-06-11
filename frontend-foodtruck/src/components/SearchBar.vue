<script setup lang="ts">
import { Search } from 'lucide-vue-next';

// 1. Recibimos la categoría, el texto de búsqueda y la lista de Laravel
defineProps<{
  modelValue: string;
  searchQuery: string; // Sincroniza el texto escrito desde el HomeView
  categories: any[];
}>();

// 2. Definimos los emisores para actualizar de forma reactiva al padre
const emit = defineEmits(['update:modelValue', 'update:searchQuery']);
</script>

<template>
  <div class="search-section">
    <div class="search-controls">
      <h2 class="title">Cotiza con DiCreme</h2>
      
      <div class="inputs-group">
        <select 
          :value="modelValue" 
          @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
          class="category-select"
        >
          <option value="Todas">Categoría</option>
          <option value="Vegano">Vegano</option>
          <option value="Sin lactosa">Sin lactosa</option>
          <option 
            v-for="cat in categories" 
            :key="cat.id || cat.ID" 
            :value="cat.nombre_categoria"
          >
            {{ cat.nombre_categoria }}
          </option>
        </select>
        
        <div class="search-input-wrapper">
          <input 
            type="text" 
            :value="searchQuery"
            @input="emit('update:searchQuery', ($event.target as HTMLInputElement).value)"
            placeholder="Busca tu helado" 
            class="search-input" 
          />
          <Search class="search-icon" :size="20" />
        </div>
      </div>
    </div>
    <div class="pink-divider"></div>
  </div>
</template>

<style scoped>
.search-section {
  margin-bottom: 30px;
}

.search-controls {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 10px 0;
}

.title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--DC-gray);
}

.inputs-group {
  display: flex;
  gap: 15px;
}

.category-select, .search-input {
  padding: 8px 35px 8px 15px;
  border-radius: 20px;
  border: 1px solid var(--DC-text-gray);
  background-color: white;
  outline: none;
  font-size: 0.9rem;
  cursor: pointer;
}

.search-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  width: 250px; 
  padding-right: 40px; 
}

.search-icon {
  position: absolute;
  right: 12px;
  color: var(--DC-text-gray);
  pointer-events: none; 
}

.pink-divider {
  height: 2px;
  background-color: var(--DC-pink);
  width: 100%;
  margin-top: 5px;
}
</style>