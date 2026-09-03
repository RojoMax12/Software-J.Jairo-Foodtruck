<template>
  <div class="inventory-view">
    <header class="inventory-header">
      <div class="header-copy">
        <span class="eyebrow">Operaciones</span>
        <h1>Inventario</h1>
        <p>Revisa el stock disponible, detecta alertas y consulta el Kardex de entradas y salidas.</p>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="currentTab === 'stock' ? reloadInventory() : loadAllKardexMovements()" :disabled="isLoading || isLoadingKardex">
          <RefreshCw :size="18" :class="{ spinning: isLoading || isLoadingKardex }" />
          <span>{{ (isLoading || isLoadingKardex) ? 'Actualizando' : 'Actualizar' }}</span>
        </button>
      </div>
    </header>

    <!-- PESTAÑAS PRINCIPALES: STOCK vs KARDEX -->
    <div class="inventory-tabs-nav">
      <button 
        type="button"
        class="tab-nav-btn" 
        :class="{ active: currentTab === 'stock' }" 
        @click="currentTab = 'stock'"
      >
        <Boxes :size="18" />
        <span>Insumos & Stock Actual</span>
        <span class="tab-pill">{{ inventoryItems.length }}</span>
      </button>

      <button 
        type="button"
        class="tab-nav-btn" 
        :class="{ active: currentTab === 'kardex' }" 
        @click="switchToKardexTab"
      >
        <ArrowLeftRight :size="18" />
        <span>Kardex & Movimientos de Stock</span>
        <span class="tab-pill kardex-pill" v-if="allMovements.length">{{ allMovements.length }}</span>
      </button>
    </div>

    <!-- ===================== TAB 1: STOCK ACTUAL ===================== -->
    <template v-if="currentTab === 'stock'">
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
         
        <!-- CAJA 1: ENCABEZADO DE COLUMNAS APARTE -->
        <div class="panel-card header-card desktop-table-only">
          <div class="header-grid">
            <div class="header-col col-product">Producto</div>
            <div class="header-col col-category">Categoría</div>
            <div class="header-col col-format">Formato</div>
            <div class="header-col col-center col-stock">Stock</div>
            <div class="header-col col-center col-min">Mínimo</div>
            <div class="header-col col-center col-status">Estado</div>
            <div class="header-col col-center col-disponibilidad">Disponibilidad</div>
            <div class="header-col col-action">Acción</div>
            <div class="header-col col-updated">Última actualización</div>
          </div>
        </div>

        <!-- CAJA 2: CONTENEDOR DE PRODUCTOS Y PAGINACIÓN -->
        <div class="panel-card table-card">
          <!-- VISTA TABLA PARA PC / TABLET -->
          <div class="table-wrapper desktop-table-only">
            <table class="inventory-table">
              <!-- ESTADO DE CARGA (SKELETON) -->
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

              <!-- ESTADO DE ERROR -->
              <tbody v-else-if="errorMessage">
                <tr>
                    <td colspan="9" class="text-center">
                    <div class="state-card error-state">
                      <AlertTriangle :size="34" />
                      <p>{{ errorMessage }}</p>
                      <button class="btn-secondary" @click="reloadInventory">Reintentar</button>
                    </div>
                  </td>
                </tr>
              </tbody>

              <!-- ESTADO SIN RESULTADOS -->
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

              <!-- LISTADO DE DATOS -->
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
                      <Layers3 :size="16" />
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
                      {{ item.disponible ? '🟢 Disponible' : '🔴 Agotado' }}
                    </button>
                  </td>
                  <td class="col-action">
                    <div class="col-actions-wrap">
                      <button type="button" class="stock-action-btn" @click="openStockModal(item)" title="Modificar cantidad de stock">
                        <Edit3 :size="14" />
                        <span>Stock</span>
                      </button>
                    </div>
                  </td>
                  <td class="col-updated">
                    <div class="meta-inline muted">
                      <Clock3 :size="16" />
                      <span>{{ item.updatedLabel }}</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- VISTA TARJETAS COMPACTAS PARA CELULARES -->
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

          <!-- CONTROLES DE PAGINACIÓN -->
          <div v-if="totalPages > 1" class="inventory-pagination">
            <button 
              type="button"
              class="pagination-btn" 
              :disabled="currentPage === 1" 
              @click="currentPage--"
            >
              <ChevronLeft :size="18" />
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
              <ChevronRight :size="18" />
            </button>
          </div>
        </div>
      </div>

      <aside class="side-panel">
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

          <div v-else class="alert-list" :class="{ 'has-scroll': criticalItems.length >= 5 }">
            <article v-for="item in criticalItems" :key="item.id" class="alert-item">
              <div>
                <strong>{{ item.productName }}</strong>
                <p>{{ item.categoryName }} · {{ item.formatName }}</p>
              </div>
              <span class="alert-qty">{{ item.quantity }} en stock</span>
            </article>
          </div>
        </div>

      </aside>
    </section>
  </template>

  <!-- ===================== TAB 2: KARDEX & MOVIMIENTOS ===================== -->
  <section v-else-if="currentTab === 'kardex'" class="kardex-view-section">
    <!-- KPIS DE KARDEX -->
    <section class="summary-grid kardex-kpis">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <ArrowLeftRight :size="24" />
        </div>
        <div>
          <span class="summary-label">Total Movimientos</span>
          <strong class="summary-value">{{ kardexStats.total }}</strong>
          <p class="summary-helper">Registros en el Kardex</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <ArrowUpRight :size="24" />
        </div>
        <div>
          <span class="summary-label">Entradas de Stock</span>
          <strong class="summary-value">{{ kardexStats.entradas }}</strong>
          <p class="summary-helper">Reposición, compras y ajustes</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <ArrowDownRight :size="24" />
        </div>
        <div>
          <span class="summary-label">Salidas por Cocina</span>
          <strong class="summary-value">{{ kardexStats.salidas }}</strong>
          <p class="summary-helper">Descuentos por comandas preparadas</p>
        </div>
      </article>
    </section>

    <!-- BARRA DE HERRAMIENTAS KARDEX -->
    <div class="panel-card toolbar-card">
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
          <X :size="15" />
          <span>Limpiar filtros</span>
        </button>
      </div>

      <div class="toolbar-right">
        <span class="results-chip">{{ filteredKardexMovements.length }} movimientos</span>
        <button class="btn-refresh-kardex" type="button" @click="loadAllKardexMovements" :disabled="isLoadingKardex">
          <RefreshCw :size="15" :class="{ spinning: isLoadingKardex }" />
          <span>Refrescar</span>
        </button>
      </div>
    </div>

    <!-- TABLA KARDEX -->
    <div class="panel-card table-card">
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

        <!-- VISTA CARDS KARDEX PARA MÓVILES -->
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

        <!-- CONTROLES DE PAGINACIÓN KARDEX -->
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
  Clock3, Edit3, Filter, History, Layers3, Package, RefreshCw, Search, TrendingUp, X 
} from 'lucide-vue-next';
import inventoryService, { type InventoryItem, type InventoryStatus, type StockMovement } from '@/services/inventoryService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();
const inventoryItems = ref<InventoryItem[]>([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isStockModalOpen = ref(false);
const selectedStockItem = ref<InventoryItem | null>(null);

// Pestañas de Navegación
const currentTab = ref<'stock' | 'kardex'>('stock');

// Estado de Kardex Global
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
const itemsPerPage = ref(8);

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

// Acciones de Kardex en Pestaña
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

const viewKardexForItem = async (item: InventoryItem) => {
  currentTab.value = 'kardex';
  kardexIngredientFilter.value = String(item.id);
  kardexSearch.value = '';
  kardexTypeFilter.value = '';
  kardexCurrentPage.value = 1;
  await loadAllKardexMovements();
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
  } catch (err) {
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
  const activeFormats = new Set(inventoryItems.value.map((item) => item.formatName).filter(Boolean));

  return {
    total: inventoryItems.value.length,
    healthy: inventoryItems.value.filter((item) => item.status === 'ok' || item.status === 'over').length,
    low: inventoryItems.value.filter((item) => item.status === 'low' || item.status === 'critical').length,
    formats: activeFormats.size,
  };
});

onMounted(() => {
  fetchInventory();
});
</script>

<style scoped>
.inventory-view {
  max-width: 1600px;
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
  grid-template-columns: minmax(0, 1fr) 260px;
  gap: 1.25rem;
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

.header-card {
  padding: 0.9rem 1.25rem;
  background: linear-gradient(180deg, #fffefa 0%, #faf4ed 100%);
  border-radius: 18px;
  box-shadow: 0 5px 18px rgba(26, 14, 5, 0.05);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.header-grid {
  display: grid;
  grid-template-columns: 20fr 13fr 10fr 7fr 7fr 10fr 12fr 10fr 11fr;
  column-gap: 0.35rem;
  align-items: center;
  width: 100%;
}

.header-col {
  color: var(--DC-brown);
  font-size: 0.76rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  white-space: normal;
  overflow-wrap: anywhere;
  line-height: 1.15;
}

.header-col.col-center {
  text-align: center;
}

.header-grid .header-col {
  width: auto;
}

.col-product { width: 20%; flex-shrink: 0; }
.col-category { width: 13%; flex-shrink: 0; }
.col-format { width: 10%; flex-shrink: 0; }
.col-stock { width: 7%; flex-shrink: 0; }
.col-min { width: 7%; flex-shrink: 0; }
.col-status { width: 10%; flex-shrink: 0; }
.col-disponibilidad { width: 12%; flex-shrink: 0; }
.col-action { width: 10%; flex-shrink: 0; }
.col-updated { width: 11%; flex-shrink: 0; }

.table-card {
  padding: 0;
  overflow: hidden;
  background: white;
  border-radius: 22px;
  box-shadow: 0 10px 30px rgba(26, 14, 5, 0.06);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.table-wrapper {
  width: 100%;
  overflow-x: hidden;
  scrollbar-width: none;
}

.table-wrapper::-webkit-scrollbar {
  display: none;
}

.inventory-table {
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
}

.inventory-table tbody td {
  padding: 0.85rem 0.6rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  box-sizing: border-box;
}

.inventory-table tbody td:first-child {
  padding-left: 1.25rem;
}

.inventory-table tbody td:last-child {
  padding-right: 1.25rem;
}

.inventory-table tbody tr:last-child td {
  border-bottom: none;
}

.inventory-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.45);
}

.inventory-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  background: var(--DC-bg-gray);
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.65rem 1.25rem;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown);
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.25s ease;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.04);
}

.pagination-btn:hover:not(:disabled) {
  background: var(--DC-orange);
  border-color: var(--DC-orange);
  color: var(--DC-brown);
  transform: translateY(-1px);
  box-shadow: 0 6px 18px rgba(226, 135, 67, 0.25);
}

.pagination-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  border-color: rgba(81, 49, 25, 0.08);
  box-shadow: none;
}

.pagination-info {
  color: var(--DC-text-gray);
  font-size: 0.9rem;
  font-weight: 600;
}

.pagination-info strong {
  color: var(--DC-brown);
  font-weight: 800;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.product-badge {
  width: 36px;
  height: 36px;
  border-radius: 12px;
  background: rgba(226, 135, 67, 0.14);
  color: var(--DC-brown);
  display: grid;
  place-items: center;
  font-weight: 800;
  font-size: 0.82rem;
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
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
  border: 1px solid transparent;
  transition: all 0.2s ease;
}

.cat-panaderia {
  background-color: #fff3bf;
  color: #d97706;
  border-color: #ffe066;
}

.cat-proteinas {
  background-color: #ffe3e3;
  color: #d6336c;
  border-color: #ffc9c9;
}

.cat-lacteos {
  background-color: #fffbe6;
  color: #b58105;
  border-color: #ffe58f;
}

.cat-frescos {
  background-color: #dcfce7;
  color: #15803d;
  border-color: #bbf7d0;
}

.cat-salsas {
  background-color: #ffedd5;
  color: #c2410c;
  border-color: #fed7aa;
}

.cat-acomp {
  background-color: #f3d9fa;
  color: #9c36b5;
  border-color: #eebefa;
}

.cat-bebestibles {
  background-color: #d0ebff;
  color: #1971c2;
  border-color: #a5d8ff;
}

.cat-empaques {
  background-color: #f1f5f9;
  color: #475569;
  border-color: #cbd5e1;
}

.cat-varios {
  background-color: #ccfbf1;
  color: #0f766e;
  border-color: #99f6e4;
}

.disponible-toggle-btn {
  padding: 0.45rem 0.8rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 800;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.2s ease;
}

.disponible-toggle-btn.is-available {
  background-color: #dcfce7;
  color: #15803d;
  border-color: #86efac;
}

.disponible-toggle-btn.is-available:hover {
  background-color: #bbf7d0;
}

.disponible-toggle-btn.is-disabled {
  background-color: #fee2e2;
  color: #dc2626;
  border-color: #fca5a5;
}

.disponible-toggle-btn.is-disabled:hover {
  background-color: #fecaca;
}

.stock-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  max-width: 100%;
  padding: 0.45rem 0.7rem;
  border: 1px solid rgba(226, 135, 67, 0.35);
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.1);
  color: var(--DC-brown);
  font-size: 0.75rem;
  font-weight: 800;
  cursor: pointer;
  transition: background 0.2s ease, border-color 0.2s ease;
}

.stock-action-btn:hover {
  background: var(--DC-orange);
  border-color: var(--DC-orange);
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

.alert-list.has-scroll {
  max-height: 22rem;
  overflow-y: auto;
  padding-right: 0.35rem;
  scrollbar-width: none;
}

.alert-list.has-scroll::-webkit-scrollbar {
  display: none;
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
  min-width: 0;
  overflow-wrap: anywhere;
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

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 20;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
}

.stock-modal {
  position: relative;
  width: min(100%, 420px);
  display: grid;
  gap: 0.85rem;
  padding: 1.5rem;
  border: 1px solid rgba(81, 49, 25, 0.1);
  border-radius: 22px;
  background: white;
  box-shadow: 0 24px 70px rgba(26, 14, 5, 0.24);
}

.stock-modal h2 {
  margin: 0;
  color: var(--DC-brown);
  font-size: 1.35rem;
}

.modal-product {
  margin: -0.35rem 0 0.25rem;
  color: var(--DC-text-gray);
  font-weight: 700;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  display: grid;
  place-items: center;
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 10px;
  background: var(--DC-bg-gray);
  color: var(--DC-brown);
  cursor: pointer;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.2s ease;
}

.modal-fade-enter-active .stock-modal,
.modal-fade-leave-active .stock-modal {
  transition: transform 0.2s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .stock-modal,
.modal-fade-leave-to .stock-modal {
  transform: translateY(10px) scale(0.98);
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.skeleton-row td {
  padding: 16px 20px;
}

.skeleton-pill {
  height: 16px;
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
    order: 0;
  }

  .table-wrapper {
    overflow-x: hidden;
  }
}

/* ----------------------------------------------------
   ESTILOS VISTA MÓVIL TARJETAS DE INVENTARIO
---------------------------------------------------- */
.mobile-inventory-cards {
  display: none;
}

@media (max-width: 768px) {
  .desktop-table-only {
    display: none !important;
  }

  .mobile-inventory-cards {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }

  .mobile-stock-card {
    background: #ffffff;
    border: 1px solid #eaeaea;
    border-radius: 16px;
    padding: 1rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
  }

  .mobile-card-top {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex-wrap: wrap;
    border-bottom: 1px dashed #eee;
    padding-bottom: 0.75rem;
  }

  .mobile-card-info {
    display: flex;
    flex-direction: column;
    gap: 2px;
    flex: 1;
    overflow: hidden;
    min-width: 0;
  }

  .mobile-card-info strong {
    font-size: 1rem;
    color: var(--DC-gray);
    white-space: normal;
    overflow-wrap: anywhere;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .mobile-card-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    background: var(--DC-bg-gray);
    padding: 10px;
    border-radius: 12px;
    text-align: center;
  }

  .stat-box {
    display: flex;
    flex-direction: column;
    gap: 2px;
  }

  .stat-lbl {
    font-size: 0.7rem;
    color: #777;
    font-weight: 700;
    text-transform: uppercase;
  }

  .stat-num {
    font-size: 1.1rem;
    font-weight: 900;
    color: var(--DC-gray);
  }

  .stat-txt {
    font-size: 0.8rem;
    font-weight: 800;
    color: var(--DC-brown);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .mobile-card-footer {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.7rem;
    justify-content: flex-end;
    font-size: 0.75rem;
  }

  .mobile-card-footer .meta-inline {
    min-width: 0;
    overflow-wrap: anywhere;
  }

  .mobile-card-footer .stock-action-btn {
    margin-left: auto;
  }
}

/* ----------------------------------------------------
   ESTILOS DE PAGINACIÓN DE INVENTARIO
---------------------------------------------------- */
.table-wrapper {
  max-height: 500px;
  overflow: hidden;
  overflow-y: auto;
  scrollbar-width: none;
}

.table-wrapper::-webkit-scrollbar {
  display: none;
}

.inventory-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-top: 1rem;
  margin-top: 1rem;
  border-top: 1px solid #eaeaea;
}

.pagination-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 10px;
  border: 1.5px solid var(--DC-orange);
  background: white;
  color: var(--DC-brown);
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--DC-orange);
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.4;
  cursor: not-allowed;
  border-color: #ddd;
}

.pagination-info {
  font-size: 0.9rem;
  color: #666;
}

.pagination-info strong {
  color: var(--DC-brown);
}

/* ----------------------------------------------------
   ESTILOS DE KARDEX Y MOVIMIENTOS DE STOCK
---------------------------------------------------- */
.col-actions-wrap,
.mobile-actions-wrap {
  display: flex;
  align-items: center;
  gap: 0.4rem;
}

.kardex-action-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.35rem;
  padding: 0.45rem 0.7rem;
  border: 1px solid rgba(59, 130, 246, 0.35);
  border-radius: 999px;
  background: rgba(59, 130, 246, 0.08);
  color: #1d4ed8;
  font-size: 0.75rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.kardex-action-btn:hover {
  background: #2563eb;
  color: white;
  border-color: #2563eb;
}

/* ----------------------------------------------------
   PESTAÑAS DE INVENTARIO (STOCK vs KARDEX)
---------------------------------------------------- */
.inventory-tabs-nav {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 24px;
  border-bottom: 2px solid #eee5d8;
  padding-bottom: 4px;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
}

.inventory-tabs-nav::-webkit-scrollbar {
  display: none;
}

.tab-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  padding: 12px 22px;
  border: none;
  background: transparent;
  color: #7c7468;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  border-radius: 12px 12px 0 0;
  position: relative;
  white-space: nowrap;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.tab-nav-btn:hover {
  color: var(--DC-brown);
  background: rgba(255, 140, 0, 0.06);
}

.tab-nav-btn.active {
  color: var(--DC-brown);
  font-weight: 800;
}

.tab-nav-btn.active::after {
  content: '';
  position: absolute;
  bottom: -6px;
  left: 0;
  right: 0;
  height: 3px;
  background: var(--DC-orange);
  border-radius: 3px 3px 0 0;
}

.tab-pill {
  font-size: 0.75rem;
  padding: 2px 8px;
  border-radius: 999px;
  background: #f0eae1;
  color: #665b4f;
  font-weight: 800;
}

.tab-nav-btn.active .tab-pill {
  background: var(--DC-orange);
  color: white;
}

.tab-pill.kardex-pill {
  background: rgba(59, 130, 246, 0.15);
  color: #1d4ed8;
}

.tab-nav-btn.active .tab-pill.kardex-pill {
  background: #2563eb;
  color: white;
}

/* ----------------------------------------------------
   ESTILOS DE KARDEX GLOBAL
---------------------------------------------------- */
.kardex-view-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.bg-summary-green {
  background: rgba(22, 163, 74, 0.12);
  color: #16a34a;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 8px 12px;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filters:hover {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #fca5a5;
}

.btn-refresh-kardex {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 14px;
  border-radius: 8px;
  border: 1.5px solid var(--DC-orange);
  background: white;
  color: var(--DC-brown);
  font-weight: 800;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh-kardex:hover:not(:disabled) {
  background: var(--DC-orange);
  color: white;
}

.kardex-loading-panel,
.kardex-empty-panel {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.8rem;
  padding: 4rem 1rem;
  color: #777;
  text-align: center;
}

.kardex-empty-panel h3 {
  margin: 0;
  color: var(--DC-brown);
  font-size: 1.2rem;
}

.kardex-empty-panel p {
  margin: 0;
  font-size: 0.9rem;
  color: #888;
}

.kardex-global-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.kardex-global-table th {
  background: #faf8f5;
  padding: 14px 16px;
  color: #7c7468;
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 2px solid #ede7dd;
  text-align: left;
}

.kardex-global-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #f2ede4;
  color: #3e3832;
  vertical-align: middle;
}

.kardex-global-table tbody tr:hover {
  background: rgba(255, 140, 0, 0.04);
}

.col-kardex-ingrediente {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.kardex-unit {
  font-size: 0.75rem;
  color: #8a7e72;
  font-weight: 600;
}

.col-kardex-datetime {
  white-space: nowrap;
}

.datetime-badge {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 4px 10px;
  background: #f1ede7;
  border-radius: 8px;
  color: #5c5247;
  font-size: 0.8rem;
  font-weight: 700;
}

.mov-concept {
  font-size: 0.85rem;
  color: #6b6257;
}

.mov-badge {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.02em;
}

.badge-entrada {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.badge-salida {
  background: #fee2e2;
  color: #b91c1c;
  border: 1px solid #fecaca;
}

.mov-qty.qty-in {
  color: #15803d;
  font-weight: 800;
}

.mov-qty.qty-out {
  color: #b91c1c;
  font-weight: 800;
}

/* CARDS KARDEX PARA DISPOSITIVOS MÓVILES */
.mobile-kardex-cards {
  display: none;
}

@media (max-width: 768px) {
  .mobile-kardex-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 10px 0;
  }

  .mobile-kardex-card {
    background: white;
    border: 1px solid #ede7dd;
    border-radius: 14px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
  }

  .mobile-kardex-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .mobile-kardex-body {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-top: 1px dashed #f0eae1;
    border-bottom: 1px dashed #f0eae1;
  }

  .mobile-kardex-qty {
    display: flex;
    gap: 6px;
    font-size: 0.9rem;
  }

  .mobile-kardex-concept {
    margin: 0;
    font-size: 0.8rem;
    color: #888;
  }
}
</style>