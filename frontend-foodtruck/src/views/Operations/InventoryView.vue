<template>
  <div class="inventory-view">
    <!-- ENCABEZADO SUPERIOR LIMPIO (SIN BOTÓN) -->
    <header class="inventory-header">
      <div class="header-copy">
        <h1>Inventario</h1>
      </div>
    </header>

    <!-- NAVEGACIÓN ENTRE STOCK Y KARDEX (SIN SCROLL HORIZONTAL) -->
    <div class="inventory-tabs-nav">
      <button 
        type="button"
        class="tab-nav-btn" 
        :class="{ active: currentTab === 'stock' }" 
        @click="currentTab = 'stock'"
      >
        <Boxes :size="18" class="tab-icon" />
        <span class="tab-text">Insumos & Stock Actual</span>
        <span class="tab-pill">{{ inventoryItems.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: currentTab === 'kardex' }" 
        @click="switchToKardexTab"
      >
        <ArrowLeftRight :size="18" class="tab-icon" />
        <span class="tab-text">Kardex & Movimientos</span>
        <span class="tab-pill" v-if="allMovements.length">{{ allMovements.length }}</span>
      </button>
    </div>

    <!-- ===================== TAB 1: STOCK ACTUAL ===================== -->
    <template v-if="currentTab === 'stock'">
      <!-- KPIS GENERALES -->
      <section class="summary-grid">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <Boxes :size="22" />
          </div>
          <div>
            <span class="summary-label">Total registros</span>
            <strong class="summary-value">{{ stats.total }}</strong>
            <p class="summary-helper">Items de inventario cargados</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <TrendingUp :size="22" />
          </div>
          <div>
            <span class="summary-label">Stock saludable</span>
            <strong class="summary-value">{{ stats.healthy }}</strong>
            <p class="summary-helper">Por sobre el mínimo sugerido</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-pink">
            <AlertTriangle :size="22" />
          </div>
          <div>
            <span class="summary-label">En alerta</span>
            <strong class="summary-value">{{ stats.low }}</strong>
            <p class="summary-helper">Requieren seguimiento próximo</p>
          </div>
        </article>
      </section>

      <!-- LAYOUT DE 2 COLUMNAS: TABLA PRINCIPAL + ALERTAS LATERALES -->
      <div class="inventory-split-layout">
        <!-- COLUMNA PRINCIPAL -->
        <main class="split-main-panel">
          <div class="panel-card table-unified-card">
            <!-- TOOLBAR CON BOTÓN ACTUALIZAR INTEGRADO AQUÍ -->
            <div class="panel-toolbar">
              <div class="toolbar-left">
                <div class="search-box">
                  <Search :size="18" class="search-icon" />
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Buscar por producto, categoría..."
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

                <button 
                  v-if="searchQuery || statusFilter !== 'all'" 
                  class="btn-reset-filters" 
                  type="button" 
                  @click="searchQuery = ''; statusFilter = 'all'"
                >
                  <X :size="14" />
                  <span>Limpiar</span>
                </button>
              </div>

              <!-- ACCIONES DE TABLA: RESULTADOS Y ACTUALIZAR -->
              <div class="toolbar-right">
                <span class="results-chip">{{ filteredItems.length }} resultados</span>
                <button 
                  class="btn-table-action" 
                  type="button" 
                  @click="reloadInventory" 
                  :disabled="isLoading"
                  title="Actualizar datos de stock"
                >
                  <RefreshCw :size="15" :class="{ spinning: isLoading }" />
                  <span>Actualizar</span>
                </button>
              </div>
            </div>

            <!-- TABLA ESCRITORIO -->
            <div class="table-wrapper desktop-table-only">
              <table class="inventory-table">
                <thead>
                  <tr>
                    <th class="col-product">Producto</th>
                    <th class="col-category">Categoría</th>
                    <th class="col-format">Formato</th>
                    <th class="text-center col-stock">Stock</th>
                    <th class="text-center col-min">Mín</th>
                    <th class="text-center col-status">Estado</th>
                    <th class="text-center col-disponibilidad">Disponibilidad</th>
                    <th class="col-action">Acción</th>
                    <th class="col-updated">Actualizado</th>
                  </tr>
                </thead>

                <tbody v-if="isLoading">
                  <tr v-for="n in 5" :key="'inv-skel-' + n" class="skeleton-row">
                    <td class="col-product"><div class="skeleton-pill width-120"></div></td>
                    <td class="col-category"><div class="skeleton-pill width-80"></div></td>
                    <td class="col-format"><div class="skeleton-pill width-70"></div></td>
                    <td class="text-center col-stock"><div class="skeleton-pill width-50"></div></td>
                    <td class="text-center col-min"><div class="skeleton-pill width-50"></div></td>
                    <td class="text-center col-status"><div class="skeleton-pill width-80"></div></td>
                    <td class="text-center col-disponibilidad"><div class="skeleton-pill width-80"></div></td>
                    <td class="col-action"><div class="skeleton-pill width-80"></div></td>
                    <td class="col-updated"><div class="skeleton-pill width-90"></div></td>
                  </tr>
                </tbody>

                <tbody v-else-if="errorMessage">
                  <tr>
                    <td colspan="9" class="text-center">
                      <div class="state-card error-state">
                        <AlertTriangle :size="34" />
                        <p>{{ errorMessage }}</p>
                        <button class="btn-table-action" @click="reloadInventory">Reintentar</button>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else-if="filteredItems.length === 0">
                  <tr>
                    <td colspan="9" class="text-center">
                      <div class="state-card empty-state">
                        <Package :size="40" />
                        <p>No hay coincidencias con los filtros actuales.</p>
                      </div>
                    </td>
                  </tr>
                </tbody>

                <tbody v-else>
                  <tr v-for="item in paginatedItems" :key="item.id">
                    <td class="col-product">
                      <div class="product-cell">
                        <div class="product-badge">{{ item.shortLabel }}</div>
                        <div>
                          <strong>{{ item.productName }}</strong>
                          <span>{{ item.formatName }}</span>
                        </div>
                      </div>
                    </td>
                    <td class="col-category">
                      <span class="category-pill" :class="getCategoryPillClass(item.categoryName)">{{ item.categoryName }}</span>
                    </td>
                    <td class="col-format">
                      <div class="meta-inline">
                        <Layers3 :size="15" />
                        <span>{{ item.formatName }}</span>
                      </div>
                    </td>
                    <td class="text-center col-stock stock-amount">{{ item.quantity }}</td>
                    <td class="text-center col-min">{{ item.minStock }}</td>
                    <td class="text-center col-status">
                      <span class="stock-badge" :class="item.statusClass">{{ item.statusLabel }}</span>
                    </td>
                    <td class="text-center col-disponibilidad">
                      <button 
                        type="button" 
                        class="disponible-toggle-btn"
                        :class="item.disponible ? 'is-available' : 'is-disabled'"
                        :title="item.disponible ? 'Haz clic para marcar como Agotado' : 'Haz clic para marcar como Disponible'"
                        @click="toggleIngredientAvailability(item)"
                      >
                        {{ item.disponible ? 'Disponible' : 'Agotado' }}
                      </button>
                    </td>
                    <td class="col-action">
                      <div class="col-actions-wrap">
                        <button type="button" class="stock-action-btn" @click="openStockModal(item)" title="Modificar cantidad de stock">
                          <Edit3 :size="13" />
                          <span>Stock</span>
                        </button>
                      </div>
                    </td>
                    <td class="col-updated">
                      <div class="meta-inline muted">
                        <Clock3 :size="14" />
                        <span>{{ item.updatedLabel }}</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- TARJETAS MÓVILES -->
            <div class="mobile-inventory-cards">
              <div v-for="item in paginatedItems" :key="item.id" class="mobile-stock-card">
                <div class="mobile-card-top">
                  <div class="product-badge">{{ item.shortLabel }}</div>
                  <div class="mobile-card-info">
                    <strong>{{ item.productName }}</strong>
                    <span class="category-pill" :class="getCategoryPillClass(item.categoryName)">{{ item.categoryName }}</span>
                  </div>
                  <span class="stock-badge" :class="item.statusClass">{{ item.statusLabel }}</span>
                </div>

                <div class="mobile-card-stats">
                  <div class="stat-box">
                    <span class="stat-lbl">Stock Actual</span>
                    <strong class="stat-num stock-amount">{{ item.quantity }}</strong>
                  </div>
                  <div class="stat-box">
                    <span class="stat-lbl">Mínimo</span>
                    <strong class="stat-num">{{ item.minStock }}</strong>
                  </div>
                  <div class="stat-box">
                    <span class="stat-lbl">Formato</span>
                    <span class="stat-txt">{{ item.formatName }}</span>
                  </div>
                </div>

                <div class="mobile-card-footer">
                  <div class="meta-inline muted">
                    <Clock3 :size="14" />
                    <span>Actualizado: {{ item.updatedLabel }}</span>
                  </div>
                  <div class="mobile-actions-wrap">
                    <button type="button" class="stock-action-btn" @click="openStockModal(item)">
                      <Edit3 :size="14" />
                      <span>Stock</span>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- PAGINACIÓN -->
            <div v-if="totalPages > 1" class="inventory-pagination">
              <button 
                type="button" 
                class="pagination-btn" 
                :disabled="currentPage === 1" 
                @click="currentPage--"
              >
                <ChevronLeft :size="16" />
                <span>Anterior</span>
              </button>
              
              <div class="pagination-info">
                Página <strong>{{ currentPage }}</strong> de <strong>{{ totalPages }}</strong>
              </div>

              <button 
                type="button" 
                class="pagination-btn" 
                :disabled="currentPage === totalPages" 
                @click="currentPage++"
              >
                <span>Siguiente</span>
                <ChevronRight :size="16" />
              </button>
            </div>
          </div>
        </main>

        <!-- COLUMNA DERECHA: REPOSICIÓN PRIORITARIA CON SCROLL Y STICKY -->
        <aside class="split-side-panel">
          <div class="panel-card priority-side-card">
            <div class="side-header">
              <div class="side-title-wrap">
                <span class="eyebrow-alert">Atención Requerida</span>
                <h2>Reposición prioritaria</h2>
              </div>
              <div class="priority-count-pill" :class="{ 'pill-danger': criticalItems.length > 0 }">
                {{ criticalItems.length }}
              </div>
            </div>

            <div v-if="criticalItems.length === 0" class="side-empty-state">
              <Package :size="28" />
              <p>No hay insumos críticos actualmente. El stock está en orden.</p>
            </div>

            <div v-else class="side-scrollable-list">
              <article 
                v-for="item in criticalItems" 
                :key="'crit-' + item.id" 
                class="side-alert-item"
              >
                <div class="side-item-main">
                  <div class="product-badge mini">{{ item.shortLabel }}</div>
                  <div class="side-item-details">
                    <strong :title="item.productName">{{ item.productName }}</strong>
                    <span>{{ item.categoryName }} · {{ item.formatName }}</span>
                  </div>
                </div>

                <div class="side-item-footer">
                  <div class="stock-side-nums">
                    <span class="alert-qty">{{ item.quantity }} en stock</span>
                    <span class="min-qty-hint">Mín: {{ item.minStock }}</span>
                  </div>
                  <button type="button" class="stock-action-btn compact" @click="openStockModal(item)" title="Ajustar stock">
                    <Edit3 :size="12" />
                    <span>Ajustar</span>
                  </button>
                </div>
              </article>
            </div>
          </div>
        </aside>
      </div>
    </template>

    <!-- ===================== TAB 2: KARDEX & MOVIMIENTOS ===================== -->
    <section v-else-if="currentTab === 'kardex'" class="kardex-view-section">
      <section class="summary-grid kardex-kpis">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <ArrowLeftRight :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Movimientos</span>
            <strong class="summary-value">{{ kardexStats.total }}</strong>
            <p class="summary-helper">Registros en el Kardex</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <ArrowUpRight :size="22" />
          </div>
          <div>
            <span class="summary-label">Entradas de Stock</span>
            <strong class="summary-value">{{ kardexStats.entradas }}</strong>
            <p class="summary-helper">Reposición, compras y ajustes</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-pink">
            <ArrowDownRight :size="22" />
          </div>
          <div>
            <span class="summary-label">Salidas por Cocina</span>
            <strong class="summary-value">{{ kardexStats.salidas }}</strong>
            <p class="summary-helper">Descuentos por comandas preparadas</p>
          </div>
        </article>
      </section>

      <div class="panel-card table-unified-card">
        <div class="panel-toolbar">
          <div class="toolbar-left">
            <div class="search-box">
              <Search :size="18" class="search-icon" />
              <input
                v-model="kardexSearch"
                type="text"
                placeholder="Buscar por insumo, concepto..."
              />
            </div>

            <div class="select-box">
              <Filter :size="18" class="select-icon" />
              <select v-model="kardexTypeFilter">
                <option value="">Todos los tipos</option>
                <option value="Entrada">Solo Entradas</option>
                <option value="Salida">Solo Salidas</option>
              </select>
            </div>

            <div class="select-box">
              <Package :size="18" class="select-icon" />
              <select v-model="kardexIngredientFilter">
                <option value="all">Todos los insumos</option>
                <option v-for="item in inventoryItems" :key="item.id" :value="String(item.id)">
                  {{ item.productName }}
                </option>
              </select>
            </div>

            <button 
              v-if="kardexIngredientFilter !== 'all' || kardexTypeFilter || kardexSearch" 
              class="btn-reset-filters" 
              type="button" 
              @click="resetKardexFilters"
            >
              <X :size="14" />
              <span>Limpiar filtros</span>
            </button>
          </div>

          <div class="toolbar-right">
            <span class="results-chip">{{ filteredKardexMovements.length }} movimientos</span>
            <button class="btn-table-action" type="button" @click="loadAllKardexMovements" :disabled="isLoadingKardex">
              <RefreshCw :size="15" :class="{ spinning: isLoadingKardex }" />
              <span>Actualizar</span>
            </button>
          </div>
        </div>

        <div v-if="isLoadingKardex" class="kardex-loading-panel">
          <RefreshCw :size="28" class="spinning" />
          <span>Cargando movimientos de Kardex...</span>
        </div>

        <div v-else-if="filteredKardexMovements.length === 0" class="kardex-empty-panel">
          <ArrowLeftRight :size="44" />
          <h3>No hay movimientos de stock registrados</h3>
          <p>No se encontraron entradas ni salidas que coincidan con los filtros aplicados.</p>
        </div>

        <div v-else class="kardex-table-container">
          <div class="table-wrapper desktop-table-only">
            <table class="kardex-global-table">
              <thead>
                <tr>
                  <th style="width: 25%;">Insumo / Ingrediente</th>
                  <th style="width: 15%;">Tipo Movimiento</th>
                  <th style="width: 15%; text-align: center;">Cantidad</th>
                  <th style="width: 25%;">Fecha y Hora Exacta</th>
                  <th style="width: 20%;">Concepto / Origen</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="mov in paginatedKardexMovements" :key="mov.id_movimiento">
                  <td class="col-kardex-ingrediente">
                    <strong>{{ mov.ingrediente?.nombre || getIngredientNameById(mov.id_ingrediente) }}</strong>
                    <span class="kardex-unit">{{ mov.ingrediente?.unidad_medida || getIngredientFormatById(mov.id_ingrediente) }}</span>
                  </td>
                  <td>
                    <span 
                      class="mov-badge" 
                      :class="mov.tipo_movimiento?.toLowerCase() === 'entrada' ? 'badge-entrada' : 'badge-salida'"
                    >
                      <ArrowUpRight v-if="mov.tipo_movimiento?.toLowerCase() === 'entrada'" :size="14" />
                      <ArrowDownRight v-else :size="14" />
                      {{ mov.tipo_movimiento?.toUpperCase() }}
                    </span>
                  </td>
                  <td class="text-center mov-qty" :class="mov.tipo_movimiento?.toLowerCase() === 'entrada' ? 'qty-in' : 'qty-out'">
                    <strong>{{ mov.tipo_movimiento?.toLowerCase() === 'entrada' ? '+' : '-' }}{{ Number(mov.cantidad) }}</strong>
                  </td>
                  <td class="col-kardex-datetime">
                    <div class="datetime-badge">
                      <Clock3 :size="14" />
                      <span>{{ formatMovementDateTime(mov.fecha_movimiento, mov.created_at) }}</span>
                    </div>
                  </td>
                  <td class="mov-concept">
                    <span>{{ getMovementConcept(mov) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mobile-kardex-cards">
            <div v-for="mov in paginatedKardexMovements" :key="'mob-' + mov.id_movimiento" class="mobile-kardex-card">
              <div class="mobile-kardex-header">
                <div>
                  <strong>{{ mov.ingrediente?.nombre || getIngredientNameById(mov.id_ingrediente) }}</strong>
                  <span class="kardex-unit">{{ mov.ingrediente?.unidad_medida || getIngredientFormatById(mov.id_ingrediente) }}</span>
                </div>
                <span 
                  class="mov-badge" 
                  :class="mov.tipo_movimiento?.toLowerCase() === 'entrada' ? 'badge-entrada' : 'badge-salida'"
                >
                  <ArrowUpRight v-if="mov.tipo_movimiento?.toLowerCase() === 'entrada'" :size="13" />
                  <ArrowDownRight v-else :size="13" />
                  {{ mov.tipo_movimiento?.toUpperCase() }}
                </span>
              </div>
              <div class="mobile-kardex-body">
                <div class="mobile-kardex-qty" :class="mov.tipo_movimiento?.toLowerCase() === 'entrada' ? 'qty-in' : 'qty-out'">
                  <span>Cantidad:</span>
                  <strong>{{ mov.tipo_movimiento?.toLowerCase() === 'entrada' ? '+' : '-' }}{{ Number(mov.cantidad) }}</strong>
                </div>
                <div class="datetime-badge">
                  <Clock3 :size="13" />
                  <span>{{ formatMovementDateTime(mov.fecha_movimiento, mov.created_at) }}</span>
                </div>
              </div>
              <p class="mobile-kardex-concept">{{ getMovementConcept(mov) }}</p>
            </div>
          </div>

          <div v-if="totalKardexPages > 1" class="inventory-pagination">
            <button 
              type="button" 
              class="pagination-btn" 
              :disabled="kardexCurrentPage === 1" 
              @click="kardexCurrentPage--"
            >
              <ChevronLeft :size="18" />
              <span>Anterior</span>
            </button>
            
            <div class="pagination-info">
              Página <strong>{{ kardexCurrentPage }}</strong> de <strong>{{ totalKardexPages }}</strong>
            </div>

            <button 
              type="button" 
              class="pagination-btn" 
              :disabled="kardexCurrentPage === totalKardexPages" 
              @click="kardexCurrentPage++"
            >
              <span>Siguiente</span>
              <ChevronRight :size="18" />
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL DE STOCK -->
    <Transition name="modal-fade">
      <div v-if="isStockModalOpen" class="modal-backdrop" @click.self="closeStockModal">
        <form class="stock-modal" @submit.prevent="submitStockUpdate">
          <button type="button" class="modal-close" aria-label="Cerrar" @click="closeStockModal">
            <X :size="20" />
          </button>
          <span class="eyebrow">Stock</span>
          <h2>Actualizar cantidad</h2>
          <p class="modal-product">{{ selectedStockItem?.productName }}</p>
          <label class="field-label" for="new-stock-quantity">Nueva cantidad</label>
          <input id="new-stock-quantity" v-model.number="newQuantity" type="number" min="0" class="form-input" />
          <p class="helper-text">Cantidad actual: {{ selectedStockItem?.quantity ?? 0 }} {{ selectedStockItem?.formatName }}</p>
          <button class="btn-primary" type="submit" :disabled="isSaving || newQuantity === null">
            <RefreshCw :size="18" :class="{ spinning: isSaving }" />
            <span>{{ isSaving ? 'Guardando' : 'Guardar cambio' }}</span>
          </button>
        </form>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { 
  AlertTriangle, ArrowDownRight, ArrowLeftRight, ArrowUpRight, Boxes, ChevronLeft, ChevronRight, 
  Clock3, Edit3, Filter, Layers3, Package, RefreshCw, Search, TrendingUp, X 
} from 'lucide-vue-next';
import inventoryService, { type InventoryItem, type InventoryStatus, type StockMovement } from '@/services/inventoryService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();
const inventoryItems = ref<InventoryItem[]>([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isStockModalOpen = ref(false);
const selectedStockItem = ref<InventoryItem | null>(null);

const currentTab = ref<'stock' | 'kardex'>('stock');

const allMovements = ref<StockMovement[]>([]);
const isLoadingKardex = ref(false);
const kardexSearch = ref('');
const kardexTypeFilter = ref<'' | 'Entrada' | 'Salida'>('');
const kardexIngredientFilter = ref<string>('all');
const kardexCurrentPage = ref(1);
const kardexItemsPerPage = ref(12);

watch([kardexSearch, kardexTypeFilter, kardexIngredientFilter], () => {
  kardexCurrentPage.value = 1;
});

const errorMessage = ref('');
const searchQuery = ref('');
const statusFilter = ref<'all' | InventoryStatus>('all');
const selectedStockId = ref('');
const newQuantity = ref<number | null>(null);

const currentPage = ref(1);
const itemsPerPage = ref(10);

watch([searchQuery, statusFilter], () => {
  currentPage.value = 1;
});

const getCategoryPillClass = (catName: string) => {
  if (!catName) return 'cat-varios';
  const lower = catName.toLowerCase();

  if (lower.includes('pan') || lower.includes('masa')) return 'cat-panaderia';
  if (lower.includes('prote')) return 'cat-proteinas';
  if (lower.includes('lác') || lower.includes('lac')) return 'cat-lacteos';
  if (lower.includes('fresco') || lower.includes('verdu')) return 'cat-frescos';
  if (lower.includes('salsa') || lower.includes('aderez')) return 'cat-salsas';
  if (lower.includes('acomp')) return 'cat-acomp';
  if (lower.includes('bebes') || lower.includes('bebi')) return 'cat-bebestibles';
  if (lower.includes('empaq')) return 'cat-empaques';

  return 'cat-varios';
};

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

const openStockModal = (item: InventoryItem) => {
  selectedStockItem.value = item;
  selectedStockId.value = String(item.id);
  newQuantity.value = item.quantity;
  isStockModalOpen.value = true;
};

const closeStockModal = () => {
  if (isSaving.value) return;
  isStockModalOpen.value = false;
  selectedStockItem.value = null;
  selectedStockId.value = '';
};

const switchToKardexTab = async () => {
  currentTab.value = 'kardex';
  if (allMovements.value.length === 0) {
    await loadAllKardexMovements();
  }
};

const loadAllKardexMovements = async () => {
  isLoadingKardex.value = true;
  try {
    allMovements.value = await inventoryService.getIngredientMovements(undefined, 300);
  } catch (error) {
    console.warn('Error al cargar movimientos de kardex:', error);
  } finally {
    isLoadingKardex.value = false;
  }
};

const resetKardexFilters = () => {
  kardexSearch.value = '';
  kardexTypeFilter.value = '';
  kardexIngredientFilter.value = 'all';
  kardexCurrentPage.value = 1;
};

const formatMovementDateTime = (dateStr?: string, createdAtStr?: string) => {
  const target = createdAtStr || dateStr;
  if (!target) return 'Sin fecha';
  try {
    const d = new Date(target);
    if (isNaN(d.getTime())) return target;
    const fecha = d.toLocaleDateString('es-CL', { day: '2-digit', month: '2-digit', year: 'numeric' });
    const hora = d.toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    return `${fecha} a las ${hora} hrs`;
  } catch {
    return target;
  }
};

const getMovementConcept = (mov: StockMovement) => {
  if (mov.tipo_movimiento?.toLowerCase() === 'salida') {
    return 'Descuento por cocina / preparación';
  }
  return 'Ingreso / Ajuste manual de stock';
};

const getIngredientNameById = (id: number) => {
  const found = inventoryItems.value.find(i => Number(i.id) === Number(id));
  return found?.productName || `Insumo #${id}`;
};

const getIngredientFormatById = (id: number) => {
  const found = inventoryItems.value.find(i => Number(i.id) === Number(id));
  return found?.formatName || 'unid';
};

const kardexStats = computed(() => {
  const total = allMovements.value.length;
  const entradas = allMovements.value.filter(m => m.tipo_movimiento?.toLowerCase() === 'entrada').length;
  const salidas = allMovements.value.filter(m => m.tipo_movimiento?.toLowerCase() === 'salida').length;
  return { total, entradas, salidas };
});

const filteredKardexMovements = computed(() => {
  let list = allMovements.value;

  if (kardexIngredientFilter.value !== 'all') {
    list = list.filter(m => String(m.id_ingrediente) === String(kardexIngredientFilter.value));
  }

  if (kardexTypeFilter.value) {
    list = list.filter(m => m.tipo_movimiento?.toLowerCase() === kardexTypeFilter.value.toLowerCase());
  }

  if (kardexSearch.value.trim()) {
    const q = kardexSearch.value.toLowerCase().trim();
    list = list.filter(m => 
      (m.ingrediente?.nombre || getIngredientNameById(m.id_ingrediente)).toLowerCase().includes(q) ||
      (m.tipo_movimiento || '').toLowerCase().includes(q) ||
      String(m.id_movimiento).includes(q)
    );
  }

  return list;
});

const totalKardexPages = computed(() => {
  return Math.ceil(filteredKardexMovements.value.length / kardexItemsPerPage.value) || 1;
});

const paginatedKardexMovements = computed(() => {
  const start = (kardexCurrentPage.value - 1) * kardexItemsPerPage.value;
  return filteredKardexMovements.value.slice(start, start + kardexItemsPerPage.value);
});

const toggleIngredientAvailability = async (item: InventoryItem) => {
  const nextState = !item.disponible;
  try {
    const updated = await inventoryService.toggleAvailability(item.id, nextState);
    inventoryItems.value = updated;
    notify(`Insumo "${item.productName}" ${nextState ? 'marcado como disponible' : 'marcado como AGOTADO'}`, nextState ? 'success' : 'warning');
  } catch {
    item.disponible = nextState;
    notify(`Insumo "${item.productName}" ${nextState ? 'marcado disponible' : 'marcado AGOTADO'}`, nextState ? 'success' : 'warning');
  }
};

const submitStockUpdate = async () => {
  if (!selectedStockId.value || newQuantity.value === null || Number.isNaN(Number(newQuantity.value))) {
    return;
  }

  isSaving.value = true;

  try {
    const updatedItems = await inventoryService.updateInventoryQuantity(Number(selectedStockId.value), Number(newQuantity.value));
    inventoryItems.value = updatedItems;
    notify('¡Stock de insumo actualizado correctamente!', 'success');
    newQuantity.value = null;
    closeStockModal();
  } catch (error) {
    console.error('Error al actualizar stock:', error);
    errorMessage.value = 'No se pudo actualizar el stock. Intenta nuevamente.';
    notify('No se pudo actualizar el stock', 'warning');
  } finally {
    isSaving.value = false;
  }
};

const filteredItems = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return inventoryItems.value.filter((item) => {
    const matchesQuery = !query || [item.productName, item.categoryName, item.formatName]
      .some((value) => value.toLowerCase().includes(query));
    
    let matchesStatus = statusFilter.value === 'all' || item.status === statusFilter.value;
    if (statusFilter.value === 'ok') {
      matchesStatus = item.status === 'ok' || item.status === 'over';
    }

    return matchesQuery && matchesStatus;
  });
});

const totalPages = computed(() => Math.ceil(filteredItems.value.length / itemsPerPage.value) || 1);

const paginatedItems = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  return filteredItems.value.slice(start, start + itemsPerPage.value);
});

const criticalItems = computed(() => {
  return inventoryItems.value
    .filter((item) => item.status === 'critical' || item.status === 'low');
});

const stats = computed(() => {
  return {
    total: inventoryItems.value.length,
    healthy: inventoryItems.value.filter((item) => item.status === 'ok' || item.status === 'over').length,
    low: inventoryItems.value.filter((item) => item.status === 'low' || item.status === 'critical').length,
  };
});

onMounted(() => {
  fetchInventory();
});
</script>

<style scoped>
.inventory-view {
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
}

/* ENCABEZADO SIN BOTÓN */
.inventory-header {
  margin-bottom: 1.25rem;
}

.header-copy h1 {
  color: var(--DC-brown);
  font-size: 2.2rem;
  line-height: 1;
  margin: 0;
}

/* ====================================================
   PESTAÑAS TIPO SEGMENTED CONTROL (SIN SCROLL HORIZONTAL)
==================================================== */
.inventory-tabs-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #ede6dc;
  border-radius: 14px;
  margin-bottom: 1.5rem;
  max-width: 100%;
}

.tab-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #6d6254;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-nav-btn:hover:not(.active) {
  color: var(--DC-brown);
  background: rgba(255, 255, 255, 0.4);
}

.tab-nav-btn.active {
  background: white;
  color: var(--DC-brown);
  font-weight: 800;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.08);
}

.tab-icon {
  flex-shrink: 0;
}

.tab-text {
  line-height: 1.2;
}

.tab-pill {
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(81, 49, 25, 0.08);
  color: #665b4f;
  font-weight: 800;
}

.tab-nav-btn.active .tab-pill {
  background: var(--DC-orange);
  color: white;
}

/* ====================================================
   BOTÓN ACTUALIZAR INTEGRADO EN LA TOOLBAR DE LA TABLA
==================================================== */
.btn-table-action {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown);
  padding: 0.52rem 0.85rem;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 700;
  font-size: 0.8rem;
}

.btn-table-action:hover:not(:disabled) {
  background: var(--DC-orange);
  border-color: var(--DC-orange);
  color: white;
  transform: translateY(-1px);
}

.btn-table-action:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

/* KPIS */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.panel-card,
.summary-card {
  background: white;
  border-radius: 18px;
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.summary-card {
  padding: 1.1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.summary-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray); color: var(--DC-brown); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }

.summary-label {
  display: block;
  color: var(--DC-text-gray);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray);
  font-size: 1.5rem;
  line-height: 1.1;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray);
  font-size: 0.8rem;
  margin: 0;
}

/* ====================================================
   DISTRIBUCIÓN 2 COLUMNAS (TABLA + ALERTAS DERECHA)
==================================================== */
.inventory-split-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 310px;
  gap: 1.25rem;
  align-items: start;
}

.split-main-panel {
  min-width: 0;
}

.split-side-panel {
  position: sticky;
  top: 1rem;
  min-width: 0;
}

/* PANEL LATERAL DE ALERTAS */
.priority-side-card {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.side-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
}

.side-title-wrap h2 {
  font-size: 1.15rem;
  color: var(--DC-brown);
  margin: 0.2rem 0 0;
}

.eyebrow-alert {
  font-size: 0.68rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: var(--DC-pink);
}

.priority-count-pill {
  min-width: 26px;
  height: 26px;
  border-radius: 999px;
  background: var(--DC-bg-gray);
  color: var(--DC-text-gray);
  font-size: 0.8rem;
  font-weight: 800;
  display: grid;
  place-items: center;
  padding: 0 6px;
}

.priority-count-pill.pill-danger {
  background: rgba(216, 0, 86, 0.12);
  color: var(--DC-pink);
}

.side-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1rem;
  text-align: center;
  gap: 0.6rem;
  color: var(--DC-text-gray);
  background: var(--DC-bg-gray);
  border-radius: 14px;
}

.side-empty-state p {
  margin: 0;
  font-size: 0.82rem;
  line-height: 1.4;
}

/* LISTA VERTICAL CON SCROLL */
.side-scrollable-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  max-height: 520px;
  overflow-y: auto;
  padding-right: 4px;
}

.side-scrollable-list::-webkit-scrollbar {
  width: 5px;
}

.side-scrollable-list::-webkit-scrollbar-thumb {
  background: rgba(81, 49, 25, 0.12);
  border-radius: 10px;
}

.side-alert-item {
  background: var(--DC-bg-gray);
  border: 1px solid rgba(81, 49, 25, 0.07);
  border-radius: 12px;
  padding: 0.8rem;
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
  transition: border-color 0.2s ease, transform 0.2s ease;
}

.side-alert-item:hover {
  border-color: rgba(216, 0, 86, 0.35);
  transform: translateY(-1px);
}

.side-item-main {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.product-badge.mini {
  width: 28px;
  height: 28px;
  font-size: 0.72rem;
  border-radius: 8px;
}

.side-item-details {
  display: flex;
  flex-direction: column;
  overflow: hidden;
  min-width: 0;
}

.side-item-details strong {
  font-size: 0.85rem;
  color: var(--DC-gray);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.side-item-details span {
  font-size: 0.74rem;
  color: var(--DC-text-gray);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.side-item-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 0.5rem;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
}

.stock-side-nums {
  display: flex;
  flex-direction: column;
}

.alert-qty {
  color: var(--DC-pink);
  font-size: 0.8rem;
  font-weight: 800;
  white-space: nowrap;
}

.min-qty-hint {
  font-size: 0.7rem;
  color: var(--DC-text-gray);
}

.stock-action-btn.compact {
  padding: 0.3rem 0.6rem;
  font-size: 0.72rem;
}

/* ====================================================
   TARJETA UNIFICADA (TABLA)
==================================================== */
.table-unified-card {
  padding: 0;
  overflow: hidden;
}

.panel-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1.15rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.search-box,
.select-box {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--DC-bg-gray);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 12px;
  padding: 0.55rem 0.75rem;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray);
  font-size: 0.86rem;
  min-width: 220px;
}

.select-box select {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray);
  font-size: 0.86rem;
  cursor: pointer;
  min-width: 130px;
}

.search-icon,
.select-icon {
  color: var(--DC-text-gray);
  flex-shrink: 0;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filters:hover {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #fca5a5;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown);
  font-size: 0.78rem;
  font-weight: 800;
}

/* TABLA */
.table-wrapper {
  max-height: 560px;
  overflow-y: auto;
  width: 100%;
}

.inventory-table,
.kardex-global-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.inventory-table thead th,
.kardex-global-table thead th {
  background: #faf6f0;
  color: var(--DC-brown);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.8rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.1);
  position: sticky;
  top: 0;
  z-index: 2;
  text-align: left;
}

.inventory-table tbody td,
.kardex-global-table tbody td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  font-size: 0.86rem;
}

.inventory-table thead th:first-child,
.inventory-table tbody td:first-child,
.kardex-global-table thead th:first-child,
.kardex-global-table tbody td:first-child {
  padding-left: 1.15rem;
}

.inventory-table thead th:last-child,
.inventory-table tbody td:last-child,
.kardex-global-table thead th:last-child,
.kardex-global-table tbody td:last-child {
  padding-right: 1.15rem;
}

.inventory-table tbody tr:hover,
.kardex-global-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.35);
}

.col-product { width: 22%; }
.col-category { width: 13%; }
.col-format { width: 10%; }
.col-stock { width: 8%; }
.col-min { width: 7%; }
.col-status { width: 11%; }
.col-disponibilidad { width: 11%; }
.col-action { width: 8%; }
.col-updated { width: 10%; }

.text-center { text-align: center; }

/* COMPONENTES DE FILAS */
.product-cell {
  display: flex;
  align-items: center;
  gap: 0.55rem;
}

.product-badge {
  width: 30px;
  height: 30px;
  border-radius: 9px;
  background: rgba(226, 135, 67, 0.14);
  color: var(--DC-brown);
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 0.76rem;
  flex-shrink: 0;
}

.product-cell strong {
  display: block;
  color: var(--DC-gray);
  line-height: 1.2;
}

.product-cell span,
.meta-inline span {
  color: var(--DC-text-gray);
  font-size: 0.78rem;
}

.meta-inline {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}

.meta-inline.muted {
  color: var(--DC-text-gray);
}

.category-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.6rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
}

.cat-panaderia { background: #fff3bf; color: #d97706; }
.cat-proteinas { background: #ffe3e3; color: #d6336c; }
.cat-lacteos { background: #fffbe6; color: #b58105; }
.cat-frescos { background: #dcfce7; color: #15803d; }
.cat-salsas { background: #ffedd5; color: #c2410c; }
.cat-acomp { background: #f3d9fa; color: #9c36b5; }
.cat-bebestibles { background: #d0ebff; color: #1971c2; }
.cat-empaques { background: #f1f5f9; color: #475569; }
.cat-varios { background: #ccfbf1; color: #0f766e; }

.disponible-toggle-btn {
  padding: 0.32rem 0.65rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.disponible-toggle-btn.is-available {
  background: #dcfce7;
  color: #15803d;
  border-color: #86efac;
}

.disponible-toggle-btn.is-disabled {
  background: #fee2e2;
  color: #dc2626;
  border-color: #fca5a5;
}

.stock-action-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.35rem 0.6rem;
  border: 1px solid rgba(226, 135, 67, 0.3);
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.08);
  color: var(--DC-brown);
  font-size: 0.74rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.stock-action-btn:hover {
  background: var(--DC-orange);
  color: white;
  border-color: var(--DC-orange);
}

.stock-amount {
  font-size: 1.05rem;
  font-weight: 800;
  color: var(--DC-brown);
}

.stock-badge {
  display: inline-flex;
  padding: 0.32rem 0.6rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 800;
}

.status-ok { background: rgba(62, 165, 93, 0.12); color: #2f8b4c; }
.status-low { background: rgba(226, 135, 67, 0.16); color: var(--DC-orange); }
.status-critical { background: rgba(216, 0, 86, 0.14); color: var(--DC-pink); }
.status-over { background: rgba(81, 49, 25, 0.1); color: var(--DC-brown); }

/* PAGINACIÓN */
.inventory-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.15rem;
  background: var(--DC-bg-gray);
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.9rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown);
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--DC-orange);
  border-color: var(--DC-orange);
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.pagination-info {
  color: var(--DC-text-gray);
  font-size: 0.82rem;
}

/* KARDEX */
.col-kardex-ingrediente {
  display: flex;
  flex-direction: column;
}

.kardex-unit {
  font-size: 0.75rem;
  color: #8a7e72;
}

.datetime-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 3px 8px;
  background: #f1ede7;
  border-radius: 6px;
  color: #5c5247;
  font-size: 0.78rem;
  font-weight: 700;
}

.mov-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 800;
}

.badge-entrada { background: #dcfce7; color: #15803d; }
.badge-salida { background: #fee2e2; color: #b91c1c; }
.mov-qty.qty-in { color: #15803d; font-weight: 800; }
.mov-qty.qty-out { color: #b91c1c; font-weight: 800; }

.kardex-loading-panel,
.kardex-empty-panel,
.state-card {
  padding: 3rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  text-align: center;
  color: #777;
}

.kardex-empty-panel h3 {
  margin: 0;
  color: var(--DC-brown);
}

.kardex-empty-panel p {
  margin: 0;
  font-size: 0.88rem;
}

/* MODAL */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
}

.stock-modal {
  position: relative;
  width: min(100%, 400px);
  display: grid;
  gap: 0.85rem;
  padding: 1.5rem;
  border-radius: 20px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
}

.stock-modal h2 {
  margin: 0;
  color: var(--DC-brown);
  font-size: 1.3rem;
}

.modal-product {
  margin: -0.3rem 0 0.2rem;
  color: var(--DC-text-gray);
  font-weight: 700;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 32px;
  height: 32px;
  border: 0;
  border-radius: 8px;
  background: var(--DC-bg-gray);
  color: var(--DC-brown);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.field-label {
  color: var(--DC-brown);
  font-size: 0.82rem;
  font-weight: 700;
}

.form-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 12px;
  background: var(--DC-bg-gray);
  padding: 0.75rem;
  color: var(--DC-gray);
  outline: none;
}

.form-input:focus {
  border-color: var(--DC-orange);
}

.helper-text {
  color: var(--DC-text-gray);
  font-size: 0.82rem;
  margin: 0;
}

.btn-primary {
  border: none;
  background: var(--DC-orange);
  color: white;
  padding: 0.8rem 1rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
}

/* SKELETON */
.skeleton-pill {
  height: 14px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}
.width-50 { width: 50px; }
.width-70 { width: 70px; }
.width-80 { width: 80px; }
.width-90 { width: 90px; }
.width-120 { width: 120px; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.spinning { animation: spin 0.9s linear infinite; }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

/* RESPONSIVE */
.mobile-inventory-cards,
.mobile-kardex-cards {
  display: none;
}

@media (max-width: 1200px) {
  .inventory-split-layout {
    grid-template-columns: 1fr;
  }

  .split-side-panel {
    position: static;
  }

  .side-scrollable-list {
    max-height: 300px;
  }
}

@media (max-width: 900px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }

  .desktop-table-only {
    display: none !important;
  }

  .mobile-inventory-cards,
  .mobile-kardex-cards {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem;
  }

  .mobile-stock-card,
  .mobile-kardex-card {
    background: white;
    border: 1px solid #ede7dd;
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }

  .mobile-card-top,
  .mobile-kardex-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .mobile-card-info {
    display: flex;
    flex-direction: column;
    flex: 1;
    margin: 0 0.5rem;
  }

  .mobile-card-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    background: var(--DC-bg-gray);
    padding: 8px;
    border-radius: 10px;
    text-align: center;
  }

  .stat-box { display: flex; flex-direction: column; gap: 2px; }
  .stat-lbl { font-size: 0.65rem; color: #777; font-weight: 700; text-transform: uppercase; }
  .stat-num { font-size: 1rem; font-weight: 800; }
  .stat-txt { font-size: 0.78rem; font-weight: 700; color: var(--DC-brown); }

  .mobile-card-footer,
  .mobile-kardex-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.78rem;
  }

  .toolbar-left,
  .toolbar-right {
    width: 100%;
  }

  .search-box,
  .select-box {
    width: 100%;
  }

  .search-box input,
  .select-box select {
    width: 100%;
    min-width: 0;
  }
}
</style>