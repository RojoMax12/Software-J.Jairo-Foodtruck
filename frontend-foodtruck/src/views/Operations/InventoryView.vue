<template>
  <div class="inventory-view">
    <header class="inventory-header">
      <div class="header-copy">
        <span class="eyebrow">Operaciones</span>
        <h1>Inventario</h1>
        <p>Revisa el stock disponible, detecta alertas y sigue la misma línea visual de la aplicación.</p>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="reloadInventory" :disabled="isLoading">
          <RefreshCw :size="18" :class="{ spinning: isLoading }" />
          <span>{{ isLoading ? 'Actualizando' : 'Actualizar' }}</span>
        </button>
      </div>
    </header>

    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <Boxes :size="24" />
        </div>
        <div>
          <span class="summary-label">Total registros</span>
          <strong class="summary-value">{{ stats.total }}</strong>
          <p class="summary-helper">Items de inventario cargados</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-orange">
          <TrendingUp :size="24" />
        </div>
        <div>
          <span class="summary-label">Stock saludable</span>
          <strong class="summary-value">{{ stats.healthy }}</strong>
          <p class="summary-helper">Por sobre el mínimo sugerido</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <AlertTriangle :size="24" />
        </div>
        <div>
          <span class="summary-label">En alerta</span>
          <strong class="summary-value">{{ stats.low }}</strong>
          <p class="summary-helper">Requieren seguimiento próximo</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <Layers3 :size="24" />
        </div>
        <div>
          <span class="summary-label">Formatos activos</span>
          <strong class="summary-value">{{ stats.formats }}</strong>
          <p class="summary-helper">Variantes detectadas en el stock</p>
        </div>
      </article>
    </section>

    <section class="inventory-layout">
      <div class="main-panel">
        <div class="panel-card toolbar-card">
          <div class="toolbar-left">
            <div class="search-box">
              <Search :size="18" class="search-icon" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar por producto, categoría o formato..."
              />
            </div>

            <div class="select-box">
              <Filter :size="18" class="select-icon" />
              <select v-model="statusFilter">
                <option value="all">Todos los estados</option>
                <option value="ok">Saludable</option>
                <option value="low">En alerta</option>
                <option value="critical">Crítico</option>
                <option value="over">Sobre stock</option>
              </select>
            </div>
          </div>

          <div class="toolbar-right">
            <span class="results-chip">{{ filteredItems.length }} resultados</span>
          </div>
        </div>

        <div class="panel-card table-card">
          <div v-if="isLoading" class="state-card">
            <div class="spinner"></div>
            <p>Cargando inventario...</p>
          </div>

          <div v-else-if="errorMessage" class="state-card error-state">
            <AlertTriangle :size="34" />
            <p>{{ errorMessage }}</p>
            <button class="btn-secondary" @click="reloadInventory">Reintentar</button>
          </div>

          <div v-else-if="filteredItems.length === 0" class="state-card empty-state">
            <Package :size="40" />
            <p>No hay coincidencias con los filtros actuales.</p>
          </div>

          <div v-else class="table-wrapper">
            <table class="inventory-table">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th>Categoría</th>
                  <th>Formato</th>
                  <th class="text-center">Stock</th>
                  <th class="text-center">Mínimo</th>
                  <th class="text-center">Estado</th>
                  <th>Última actualización</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in filteredItems" :key="item.id">
                  <td>
                    <div class="product-cell">
                      <div class="product-badge">{{ item.shortLabel }}</div>
                      <div>
                        <strong>{{ item.productName }}</strong>
                        <span>{{ item.formatName }}</span>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="category-pill">{{ item.categoryName }}</span>
                  </td>
                  <td>
                    <div class="meta-inline">
                      <Layers3 :size="16" />
                      <span>{{ item.formatName }}</span>
                    </div>
                  </td>
                  <td class="text-center stock-amount">{{ item.quantity }}</td>
                  <td class="text-center">{{ item.minStock }}</td>
                  <td class="text-center">
                    <span class="stock-badge" :class="item.statusClass">{{ item.statusLabel }}</span>
                  </td>
                  <td>
                    <div class="meta-inline muted">
                      <Clock3 :size="16" />
                      <span>{{ item.updatedLabel }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <aside class="side-panel">
        <div class="panel-card quick-update-card">
          <button class="dropdown-trigger" type="button" @click="toggleQuickUpdateMenu">
            <div class="side-title-row no-margin">
              <div>
                <span class="eyebrow">Stock</span>
                <h2>Actualizar producto</h2>
              </div>
              <ChevronDown :size="22" class="title-icon dropdown-icon" :class="{ open: isQuickUpdateOpen }" />
            </div>
          </button>

          <Transition name="dropdown-fade">
            <form v-if="isQuickUpdateOpen" class="quick-update-form" @submit.prevent="submitStockUpdate">
              <label class="field-label">
                Producto
                <select v-model="selectedStockId" class="form-input">
                  <option value="" disabled>Selecciona un producto</option>
                  <option v-for="item in inventoryItems" :key="item.id" :value="String(item.id)">
                    {{ item.productName }} · actual: {{ item.quantity }}
                  </option>
                </select>
              </label>

              <label class="field-label">
                Nueva cantidad
                <input
                  v-model.number="newQuantity"
                  type="number"
                  min="0"
                  class="form-input"
                  placeholder="0"
                />
              </label>

              <p class="helper-text">Selecciona un producto y define la nueva cantidad para actualizar el stock.</p>

              <button class="btn-primary" type="submit" :disabled="isSaving || !selectedStockId">
                <RefreshCw :size="18" :class="{ spinning: isSaving }" />
                <span>{{ isSaving ? 'Guardando' : 'Guardar cambio' }}</span>
              </button>
            </form>
          </Transition>
        </div>

        <div class="panel-card alert-card">
          <div class="side-title-row">
            <div>
              <span class="eyebrow">Alertas</span>
              <h2>Reposición prioritaria</h2>
            </div>
            <AlertTriangle :size="22" class="title-icon" />
          </div>

          <div v-if="criticalItems.length === 0" class="side-empty">
            <p>No hay productos críticos en este momento.</p>
          </div>

          <div v-else class="alert-list">
            <article v-for="item in criticalItems" :key="item.id" class="alert-item">
              <div>
                <strong>{{ item.productName }}</strong>
                <p>{{ item.categoryName }} · {{ item.formatName }}</p>
              </div>
              <span class="alert-qty">{{ item.quantity }} en stock</span>
            </article>
          </div>
        </div>

        <div class="panel-card alert-card">
          <div class="side-title-row">
            <div>
              <span class="eyebrow">Categorías</span>
              <h2>Con más movimiento</h2>
            </div>
            <Boxes :size="22" class="title-icon" />
          </div>

          <div class="warehouse-list">
            <div v-for="category in categorySummary" :key="category.name" class="warehouse-row">
              <div>
                <strong>{{ category.name }}</strong>
                <p>{{ category.items }} registros</p>
              </div>
              <span>{{ category.total }} uds.</span>
            </div>
          </div>
        </div>
      </aside>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { AlertTriangle, Boxes, ChevronDown, Clock3, Filter, Layers3, Package, RefreshCw, Search, TrendingUp } from 'lucide-vue-next';
import inventoryService, { type InventoryItem, type InventoryStatus } from '@/services/inventoryService';

const inventoryItems = ref<InventoryItem[]>([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isQuickUpdateOpen = ref(true);
const errorMessage = ref('');
const searchQuery = ref('');
const statusFilter = ref<'all' | InventoryStatus>('all');
const selectedStockId = ref('');
const newQuantity = ref<number | null>(null);

const fetchInventory = async () => {
  isLoading.value = true;
  errorMessage.value = '';

  try {
    inventoryItems.value = await inventoryService.getInventoryItems();
  } catch (error: any) {
    console.error('Error al cargar inventario:', error);
    errorMessage.value = error?.response?.data?.message || 'No se pudo cargar el inventario. Intenta nuevamente.';
    inventoryItems.value = [];
  } finally {
    isLoading.value = false;
  }
};

const reloadInventory = () => {
  fetchInventory();
};

const toggleQuickUpdateMenu = () => {
  isQuickUpdateOpen.value = !isQuickUpdateOpen.value;
};

const submitStockUpdate = async () => {
  if (!selectedStockId.value || newQuantity.value === null || Number.isNaN(Number(newQuantity.value))) {
    return;
  }

  isSaving.value = true;

  try {
    const updatedItems = await inventoryService.updateInventoryQuantity(Number(selectedStockId.value), Number(newQuantity.value));
    inventoryItems.value = updatedItems;
    newQuantity.value = null;
  } catch (error) {
    console.error('Error al actualizar stock:', error);
    errorMessage.value = 'No se pudo actualizar el stock. Intenta nuevamente.';
  } finally {
    isSaving.value = false;
  }
};

const filteredItems = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return inventoryItems.value.filter((item) => {
    const matchesQuery = !query || [item.productName, item.categoryName, item.formatName]
      .some((value) => value.toLowerCase().includes(query));
    const matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value;

    return matchesQuery && matchesStatus;
  });
});

const criticalItems = computed(() => {
  return inventoryItems.value
    .filter((item) => item.status === 'critical' || item.status === 'low')
    .slice(0, 5);
});

const stats = computed(() => {
  const activeFormats = new Set(inventoryItems.value.map((item) => item.formatName).filter(Boolean));

  return {
    total: inventoryItems.value.length,
    healthy: inventoryItems.value.filter((item) => item.status === 'ok').length,
    low: inventoryItems.value.filter((item) => item.status === 'low' || item.status === 'critical').length,
    formats: activeFormats.size,
  };
});

const categorySummary = computed(() => {
  const summary = new Map<string, { name: string; total: number; items: number }>();

  inventoryItems.value.forEach((item) => {
    const current = summary.get(item.categoryName) || { name: item.categoryName, total: 0, items: 0 };
    current.total += item.quantity;
    current.items += 1;
    summary.set(item.categoryName, current);
  });

  return Array.from(summary.values())
    .sort((a, b) => b.total - a.total)
    .slice(0, 4);
});

onMounted(() => {
  fetchInventory();
});
</script>

<style scoped>
.inventory-view {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem 1.5rem 3rem;
}

.inventory-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
}

.header-copy h1 {
  color: var(--DC-brown);
  font-size: 2.6rem;
  line-height: 1;
  margin: 0.35rem 0 0.6rem;
}

.header-copy p {
  max-width: 720px;
  color: var(--DC-text-gray);
  font-size: 1.02rem;
  line-height: 1.55;
}

.eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown);
  font-weight: 700;
  font-size: 0.75rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.header-actions {
  flex-shrink: 0;
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown);
  padding: 0.8rem 1rem;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  gap: 0.6rem;
  cursor: pointer;
  transition: all 0.25s ease;
  font-weight: 700;
}

.btn-secondary:hover:not(:disabled) {
  transform: translateY(-2px);
  border-color: var(--DC-orange);
  box-shadow: 0 10px 24px rgba(226, 135, 67, 0.12);
}

.btn-secondary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.summary-card,
.panel-card {
  background: white;
  border-radius: 22px;
  box-shadow: 0 10px 30px rgba(26, 14, 5, 0.06);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.summary-card {
  padding: 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.95rem;
}

.summary-icon-box {
  width: 54px;
  height: 54px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown {
  background: var(--DC-bg-gray);
  color: var(--DC-brown);
}

.bg-summary-orange {
  background: rgba(226, 135, 67, 0.14);
  color: var(--DC-orange);
}

.bg-summary-pink {
  background: rgba(216, 0, 86, 0.12);
  color: var(--DC-pink);
}

.summary-label {
  display: block;
  color: var(--DC-text-gray);
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray);
  font-size: 1.65rem;
  line-height: 1.1;
  margin: 0.2rem 0;
}

.summary-helper {
  color: var(--DC-text-gray);
  font-size: 0.85rem;
}

.inventory-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(290px, 0.75fr);
  gap: 1rem;
  align-items: start;
}

.main-panel,
.side-panel {
  display: grid;
  gap: 1rem;
}

.quick-update-card {
  display: grid;
  gap: 1rem;
}

.dropdown-trigger {
  border: none;
  background: transparent;
  padding: 0;
  text-align: left;
  cursor: pointer;
}

.no-margin {
  margin-bottom: 0;
}

.dropdown-icon {
  transition: transform 0.2s ease;
}

.dropdown-icon.open {
  transform: rotate(180deg);
}

.quick-update-form {
  display: grid;
  gap: 0.9rem;
}

.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.field-label {
  display: grid;
  gap: 0.45rem;
  color: var(--DC-brown);
  font-size: 0.85rem;
  font-weight: 700;
}

.form-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 14px;
  background: var(--DC-bg-gray);
  padding: 0.85rem 0.95rem;
  color: var(--DC-gray);
  outline: none;
}

.form-input:focus {
  border-color: var(--DC-orange);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.12);
}

.helper-text {
  color: var(--DC-text-gray);
  font-size: 0.85rem;
  line-height: 1.45;
}

.btn-primary {
  border: none;
  background: var(--DC-orange);
  color: var(--DC-brown);
  padding: 0.9rem 1rem;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  cursor: pointer;
  font-weight: 800;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(226, 135, 67, 0.22);
}

.btn-primary:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.panel-card {
  padding: 1rem;
}

.toolbar-card {
  display: flex;
  gap: 1rem;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
}

.search-box,
.select-box {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  background: var(--DC-bg-gray);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 16px;
  padding: 0.8rem 0.95rem;
}

.search-box input,
.select-box select {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray);
  font-size: 0.95rem;
  min-width: 230px;
}

.select-box select {
  min-width: 180px;
  cursor: pointer;
}

.search-icon,
.select-icon {
  color: var(--DC-text-gray);
  flex-shrink: 0;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.55rem 0.85rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown);
  font-size: 0.85rem;
  font-weight: 700;
}

.table-card {
  padding: 0;
  overflow: hidden;
}

.table-wrapper {
  overflow-x: auto;
}

.inventory-table {
  width: 100%;
  border-collapse: collapse;
}

.inventory-table thead th {
  background: var(--DC-bg-gray);
  color: var(--DC-brown);
  text-align: left;
  font-size: 0.75rem;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 1rem 1rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.inventory-table tbody td {
  padding: 1rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
}

.inventory-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.45);
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.product-badge {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: rgba(226, 135, 67, 0.14);
  color: var(--DC-brown);
  display: grid;
  place-items: center;
  font-weight: 800;
  flex-shrink: 0;
}

.product-cell strong {
  display: block;
  color: var(--DC-gray);
  margin-bottom: 0.15rem;
}

.product-cell span,
.meta-inline span,
.warehouse-row p,
.alert-item p,
.side-empty p,
.state-card p {
  color: var(--DC-text-gray);
  font-size: 0.88rem;
}

.meta-inline {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
}

.meta-inline.muted {
  color: var(--DC-text-gray);
}

.category-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.7rem;
  border-radius: 999px;
  background: rgba(216, 0, 86, 0.1);
  color: var(--DC-pink);
  font-size: 0.78rem;
  font-weight: 700;
}

.stock-amount {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--DC-brown);
}

.stock-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 800;
}

.status-ok {
  background: rgba(62, 165, 93, 0.12);
  color: #2f8b4c;
}

.status-low {
  background: rgba(226, 135, 67, 0.16);
  color: var(--DC-orange);
}

.status-critical {
  background: rgba(216, 0, 86, 0.14);
  color: var(--DC-pink);
}

.status-over {
  background: rgba(81, 49, 25, 0.1);
  color: var(--DC-brown);
}

.state-card {
  min-height: 320px;
  display: grid;
  place-items: center;
  text-align: center;
  gap: 0.8rem;
  padding: 2rem;
}

.error-state {
  color: var(--DC-pink);
}

.empty-state,
.side-empty {
  color: var(--DC-text-gray);
}

.spinner {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 4px solid rgba(226, 135, 67, 0.18);
  border-top-color: var(--DC-orange);
  animation: spin 0.9s linear infinite;
}

.side-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}

.side-title-row h2 {
  color: var(--DC-brown);
  font-size: 1.2rem;
  margin-top: 0.35rem;
}

.title-icon {
  color: var(--DC-orange);
  flex-shrink: 0;
}

.alert-list,
.warehouse-list {
  display: grid;
  gap: 0.85rem;
}

.alert-item,
.warehouse-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  padding: 0.85rem 0.95rem;
  border-radius: 16px;
  background: var(--DC-bg-gray);
}

.alert-item strong,
.warehouse-row strong {
  color: var(--DC-gray);
}

.alert-qty {
  color: var(--DC-pink);
  font-size: 0.8rem;
  font-weight: 800;
  white-space: nowrap;
}

.warehouse-row span {
  color: var(--DC-brown);
  font-weight: 800;
  white-space: nowrap;
}

.spinning {
  animation: spin 0.9s linear infinite;
}

.text-center {
  text-align: center;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 1180px) {
  .summary-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .inventory-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 760px) {
  .inventory-view {
    padding: 1.2rem 1rem 2rem;
  }

  .inventory-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .header-copy h1 {
    font-size: 2.1rem;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    align-items: stretch;
  }

  .toolbar-left,
  .search-box,
  .select-box {
    width: 100%;
  }

  .search-box input,
  .select-box select {
    min-width: 0;
    width: 100%;
  }

  .results-chip {
    width: 100%;
    justify-content: center;
  }

  .side-panel {
    order: -1;
  }
}
</style>