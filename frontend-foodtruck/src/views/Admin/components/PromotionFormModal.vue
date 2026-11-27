<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="$emit('close')">
    <div class="modal-card" :class="{ 'modal-card-wide': !isEditing && mode === 'batch' }">
      <div class="modal-header">
        <div class="modal-header-title">
          <div class="header-icon-pill"><BadgePercent :size="18" /></div>
          <div>
            <h3>{{ isEditing ? 'Editar Promoción' : (mode === 'batch' ? 'Crear Múltiples Promociones' : 'Nueva Promoción') }}</h3>
            <p class="modal-header-desc">
              {{ !isEditing && mode === 'batch'
                ? 'Selecciona varios productos, define un descuento o precio y aplícalos todos de una vez'
                : 'Configura producto, tamaño, fechas y precio promocional' }}
            </p>
          </div>
        </div>
        <button type="button" class="close-btn" @click="$emit('close')"><X :size="18" /></button>
      </div>

      <!-- Selector Individual vs Múltiple solo al crear -->
      <div v-if="!isEditing" class="mode-switch-bar">
        <button
          type="button"
          class="mode-tab-btn"
          :class="{ active: mode === 'single' }"
          @click="mode = 'single'"
        >
          Individual (1 producto)
        </button>
        <button
          type="button"
          class="mode-tab-btn"
          :class="{ active: mode === 'batch' }"
          @click="mode = 'batch'"
        >
          ⚡ Creación Múltiple (Varios productos)
        </button>
      </div>

      <!-- ==================== MODO INDIVIDUAL ==================== -->
      <form v-if="isEditing || mode === 'single'" class="modal-body" @submit.prevent="$emit('submit')">
        <label class="modal-label">
          <span>Producto Asociado <span class="required">*</span></span>
          <select v-model.number="promotionForm.id_producto" class="modal-input" required>
            <option value="" disabled>Selecciona un producto</option>
            <option v-for="product in products" :key="product.id" :value="product.id">
              {{ product.name }} (${{ formatCLP(product.price) }})
            </option>
          </select>
        </label>

        <label class="modal-label">
          <span>Tamaño al que aplica la oferta</span>
          <select v-model="promotionForm.id_tamaño" class="modal-input">
            <option :value="null">Todos los tamaños del producto (General)</option>
            <option
              v-for="sz in selectedProductSizes"
              :key="sz.id_tamaño"
              :value="sz.id_tamaño"
            >
              Solo tamaño: {{ sz.nombre }} (Normal: ${{ formatCLP(sz.pivot?.precio ?? sz.precio ?? 0) }})
            </option>
          </select>
        </label>

        <label class="modal-label">
          <span>Título de la Promoción <span class="required">*</span></span>
          <input
            v-model="promotionForm.titulo"
            class="modal-input"
            required
            maxlength="120"
            placeholder="Ej: Promo Combo Doble Cheddar"
          />
        </label>

        <div class="form-row-2">
          <label class="modal-label">
            <span>Precio Promocional ($) <span class="required">*</span></span>
            <input
              v-model.number="promotionForm.precio_promocional"
              class="modal-input"
              type="number"
              min="1"
              required
              placeholder="Ej: 2990"
            />
          </label>
          <label class="modal-label">
            <span>Estado</span>
            <select v-model="promotionForm.activo" class="modal-input">
              <option :value="true">Activa (Visible)</option>
              <option :value="false">Inactiva (Oculta)</option>
            </select>
          </label>
        </div>

        <div class="form-row-2">
          <label class="modal-label">
            <span>Fecha Inicio <span class="required">*</span></span>
            <input
              v-model="promotionForm.fecha_inicio"
              class="modal-input"
              type="date"
              required
            />
          </label>
          <label class="modal-label">
            <span>Fecha Término <span class="required">*</span></span>
            <input
              v-model="promotionForm.fecha_fin"
              class="modal-input"
              type="date"
              required
              :min="promotionForm.fecha_inicio"
            />
          </label>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="$emit('close')">Cancelar</button>
          <button type="submit" class="btn-save">
            <Check :size="16" />
            <span>{{ isEditing ? 'Guardar Cambios' : 'Crear Promoción' }}</span>
          </button>
        </div>
      </form>

      <!-- ==================== MODO CREACIÓN MÚLTIPLE ==================== -->
      <form v-else class="modal-body batch-body" @submit.prevent="submitBatch">
        <div class="batch-config-grid">
          <label class="modal-label">
            <span>Título Base (opcional, si queda vacío usa "Promo [Producto]")</span>
            <input
              v-model="batchTitlePrefix"
              class="modal-input"
              maxlength="80"
              placeholder="Ej: Promo Finde ·"
            />
          </label>

          <label class="modal-label">
            <span>Estado Inicial</span>
            <select v-model="batchActive" class="modal-input">
              <option :value="true">Activas inmediatamente</option>
              <option :value="false">Borrador (Inactivas)</option>
            </select>
          </label>
        </div>

        <div class="form-row-2">
          <label class="modal-label">
            <span>Fecha Inicio <span class="required">*</span></span>
            <input v-model="batchStartDate" class="modal-input" type="date" required />
          </label>
          <label class="modal-label">
            <span>Fecha Término <span class="required">*</span></span>
            <input v-model="batchEndDate" class="modal-input" type="date" required :min="batchStartDate" />
          </label>
        </div>

        <!-- Estrategia de Descuento Automático -->
        <div class="strategy-box">
          <div class="strategy-controls">
            <label class="modal-label">
              <span>Estrategia de Descuento</span>
              <select v-model="discountType" class="modal-input">
                <option value="percent">Porcentaje (% OFF sobre precio normal)</option>
                <option value="amount">Rebaja fija ($ menos en cada producto)</option>
                <option value="fixed">Mismo precio promocional ($) para todos</option>
              </select>
            </label>

            <label class="modal-label">
              <span>{{ discountType === 'percent' ? 'Porcentaje (%)' : (discountType === 'amount' ? 'Descuento ($)' : 'Precio Oferta ($)') }}</span>
              <div class="strategy-value-row">
                <input
                  v-model.number="discountValue"
                  class="modal-input"
                  type="number"
                  min="1"
                  :max="discountType === 'percent' ? 95 : 999999"
                />
                <button type="button" class="btn-apply-rule" @click="applyDiscountStrategyToSelected">
                  Recalcular
                </button>
              </div>
            </label>
          </div>
          <label class="replace-checkbox-label">
            <input type="checkbox" v-model="replaceConflicts" />
            <span>Reemplazar automáticamente promociones activas previas en las mismas fechas si existen</span>
          </label>
        </div>

        <!-- Selector Múltiple de Productos -->
        <div class="batch-picker-box">
          <div class="batch-picker-toolbar">
            <input
              v-model="batchSearch"
              type="text"
              class="modal-input batch-search-input"
              placeholder="Filtrar productos por nombre o categoría..."
            />
            <select v-model="batchCategoryFilter" class="modal-input batch-cat-select">
              <option value="">Todas las categorías</option>
              <option v-for="cat in availableCategories" :key="cat" :value="cat">{{ cat }}</option>
            </select>
            <button type="button" class="btn-mini-action" @click="selectAllFiltered">
              Sel. visibles ({{ filteredBatchProducts.length }})
            </button>
            <button type="button" class="btn-mini-action muted" @click="clearBatchSelection" v-if="batchRows.length > 0">
              Limpiar ({{ batchRows.length }})
            </button>
          </div>

          <div class="batch-products-checklist">
            <div
              v-for="prod in filteredBatchProducts"
              :key="'chk-' + prod.id"
              class="batch-prod-check-item"
              :class="{ selected: isSelectedProduct(prod.id), 'has-sizes': Array.isArray(prod.sizes_obj) && prod.sizes_obj.length > 0 }"
            >
              <label class="batch-prod-main-row">
                <input
                  type="checkbox"
                  :checked="isSelectedProduct(prod.id)"
                  @change="toggleProductInBatch(prod)"
                />
                <span class="chk-name">{{ prod.name }}</span>
                <span class="chk-cat">{{ prod.category }}</span>
                <strong class="chk-price">${{ formatCLP(prod.price) }}</strong>
              </label>
              <div v-if="Array.isArray(prod.sizes_obj) && prod.sizes_obj.length > 0" class="chk-sizes-pills">
                <button
                  type="button"
                  class="size-chip-btn"
                  :class="{ active: hasRowForProductSize(prod.id, null) }"
                  @click.stop="toggleProductSizeInBatch(prod, null)"
                >
                  Todos
                </button>
                <button
                  v-for="sz in prod.sizes_obj"
                  :key="'sz-pill-' + prod.id + '-' + sz.id_tamaño"
                  type="button"
                  class="size-chip-btn"
                  :class="{ active: hasRowForProductSize(prod.id, sz.id_tamaño) }"
                  @click.stop="toggleProductSizeInBatch(prod, sz.id_tamaño)"
                >
                  {{ sz.nombre }} (${{ formatCLP(sz.pivot?.precio ?? sz.precio ?? 0) }})
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Tabla Previa Editable -->
        <div v-if="batchRows.length > 0" class="batch-preview-table-wrap">
          <table class="batch-preview-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th>Tamaño</th>
                <th>Título en Promo</th>
                <th>Normal</th>
                <th>Precio Oferta ($)</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in batchRows" :key="row.rowKey" :class="{ 'row-invalid': row.precio_promocional >= row.normal_price || row.precio_promocional <= 0 }">
                <td class="col-prod-name">{{ row.product_name }}</td>
                <td>
                  <div class="row-size-cell">
                    <select v-model="row.id_tamaño" class="mini-input" @change="onRowSizeChange(row)">
                      <option :value="null" :disabled="hasOtherRowForSize(row, null)">Todos</option>
                      <option
                        v-for="sz in row.sizes_obj"
                        :key="sz.id_tamaño"
                        :value="sz.id_tamaño"
                        :disabled="hasOtherRowForSize(row, sz.id_tamaño)"
                      >
                        {{ sz.nombre }} (${{ formatCLP(sz.pivot?.precio ?? sz.precio ?? 0) }})
                      </option>
                    </select>
                    <button
                      v-if="hasAvailableExtraSize(row)"
                      type="button"
                      class="btn-add-size-row"
                      @click="addAnotherSizeRow(row)"
                      title="Agregar promoción para otro tamaño de este producto"
                    >
                      +Tamaño
                    </button>
                  </div>
                </td>
                <td>
                  <input v-model="row.titulo" type="text" class="mini-input" maxlength="120" required />
                </td>
                <td class="col-normal">${{ formatCLP(row.normal_price) }}</td>
                <td>
                  <input v-model.number="row.precio_promocional" type="number" min="1" class="mini-input price-mini" required />
                </td>
                <td>
                  <button type="button" class="btn-remove-row" @click="removeRow(row.rowKey)" title="Quitar">×</button>
                </td>
              </tr>
            </tbody>
          </table>
          <p v-if="hasInvalidBatchRows" class="batch-warning-msg">
            Revisa las filas marcadas en rojo: el precio promocional debe ser mayor a $0 y menor al precio normal.
          </p>
        </div>
        <div v-else class="batch-empty-hint">
          Marca uno o más productos arriba para configurar sus precios promocionales en lote.
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="$emit('close')">Cancelar</button>
          <button
            type="submit"
            class="btn-save"
            :disabled="batchRows.length === 0 || hasInvalidBatchRows"
          >
            <Check :size="16" />
            <span>Crear {{ batchRows.length }} Promoci{{ batchRows.length === 1 ? 'ón' : 'ones' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { BadgePercent, Check, X } from 'lucide-vue-next';
import { formatCLP } from '@/utils/format';

interface BatchRow {
  rowKey: string;
  id_producto: number;
  product_name: string;
  id_tamaño: number | null;
  size_name: string;
  sizes_obj: any[];
  base_min_price: number;
  normal_price: number;
  titulo: string;
  precio_promocional: number;
}

const props = defineProps<{
  isOpen: boolean;
  isEditing: boolean;
  promotionForm: {
    id: number;
    id_producto: string | number;
    id_tamaño: number | null;
    titulo: string;
    descripcion: string;
    precio_promocional: number;
    fecha_inicio: string;
    fecha_fin: string;
    activo: boolean;
  };
  products: any[];
  selectedProductSizes: any[];
}>();

const emit = defineEmits<{
  close: [];
  submit: [];
  'submit-batch': [payload: {
    reemplazar_conflictos: boolean;
    items: Array<{
      id_producto: number;
      id_tamaño: number | null;
      titulo: string;
      descripcion: string | null;
      precio_promocional: number;
      fecha_inicio: string;
      fecha_fin: string;
      activo: boolean;
    }>;
  }];
}>();

const mode = ref<'single' | 'batch'>('single');
const batchTitlePrefix = ref('');
const batchActive = ref(true);
const batchStartDate = ref(new Date().toISOString().slice(0, 10));
const batchEndDate = ref(new Date().toISOString().slice(0, 10));
const discountType = ref<'percent' | 'amount' | 'fixed'>('percent');
const discountValue = ref<number>(15);
const replaceConflicts = ref(true);
const batchSearch = ref('');
const batchCategoryFilter = ref('');
const batchRows = ref<BatchRow[]>([]);

watch(() => props.isOpen, (open) => {
  if (open) {
    mode.value = 'single';
    batchStartDate.value = props.promotionForm.fecha_inicio || new Date().toISOString().slice(0, 10);
    batchEndDate.value = props.promotionForm.fecha_fin || batchStartDate.value;
    batchRows.value = [];
    batchSearch.value = '';
    batchCategoryFilter.value = '';
  }
});

const availableCategories = computed(() => {
  const cats = new Set<string>();
  props.products.forEach((p: any) => {
    if (p.category) cats.add(String(p.category));
  });
  return Array.from(cats);
});

const filteredBatchProducts = computed(() => {
  const q = batchSearch.value.trim().toLowerCase();
  return props.products.filter((p: any) => {
    if (p.active === false) return false;
    const matchesCat = !batchCategoryFilter.value || String(p.category || '') === batchCategoryFilter.value;
    const matchesQ = !q || String(p.name || '').toLowerCase().includes(q) || String(p.category || '').toLowerCase().includes(q);
    return matchesCat && matchesQ;
  });
});

const makeRowKey = (productId: number | string, sizeId: number | null): string => {
  return `${Number(productId)}:${sizeId !== null && sizeId !== undefined ? Number(sizeId) : 'all'}`;
};

const isSelectedProduct = (id: number | string) => {
  return batchRows.value.some((r) => Number(r.id_producto) === Number(id));
};

const hasRowForProductSize = (productId: number | string, sizeId: number | null): boolean => {
  const key = makeRowKey(productId, sizeId);
  return batchRows.value.some((r) => r.rowKey === key);
};

const hasOtherRowForSize = (row: BatchRow, candidateSizeId: number | null): boolean => {
  const candidateNorm = candidateSizeId !== null && candidateSizeId !== undefined ? Number(candidateSizeId) : null;
  return batchRows.value.some(
    (r) =>
      r !== row &&
      r.id_producto === row.id_producto &&
      ((r.id_tamaño === null && candidateNorm === null) ||
        (r.id_tamaño !== null && candidateNorm !== null && Number(r.id_tamaño) === candidateNorm))
  );
};

const computePromotionalPrice = (normalPrice: number): number => {
  const val = Number(discountValue.value || 0);
  if (discountType.value === 'percent') {
    const pct = Math.min(95, Math.max(1, val));
    return Math.max(1, Math.round((normalPrice * (1 - pct / 100)) / 10) * 10);
  }
  if (discountType.value === 'amount') {
    return Math.max(1, normalPrice - val);
  }
  return Math.max(1, Math.round(val));
};

const getNormalPriceForSize = (prod: any, sizeId: number | null): number => {
  const sizesObj = Array.isArray(prod.sizes_obj) ? prod.sizes_obj : [];
  if (sizeId !== null && sizesObj.length > 0) {
    const found = sizesObj.find((s: any) => Number(s.id_tamaño) === Number(sizeId));
    if (found) return Number(found.pivot?.precio ?? found.precio ?? prod.price ?? 0);
  }
  if (sizesObj.length > 0) {
    const mins = sizesObj.map((s: any) => Number(s.pivot?.precio ?? s.precio ?? 0)).filter((n: number) => n > 0);
    if (mins.length > 0) return Math.min(...mins);
  }
  return Number(prod.price || 0);
};

const buildPromoTitle = (productName: string, sizeName: string): string => {
  const prefix = batchTitlePrefix.value.trim();
  const baseName = prefix ? (prefix.includes(productName) ? prefix : `${prefix} ${productName}`) : `Promo ${productName}`;
  return (sizeName ? `${baseName} (${sizeName})` : baseName).slice(0, 120);
};

const buildBatchRow = (prod: any, sizeId: number | null = null): BatchRow => {
  const sizesObj = Array.isArray(prod.sizes_obj) ? prod.sizes_obj : [];
  const resolvedSizeId = sizeId !== null && sizeId !== undefined ? Number(sizeId) : null;
  const matchedSize = resolvedSizeId !== null ? sizesObj.find((s: any) => Number(s.id_tamaño) === resolvedSizeId) : null;
  const sizeName = matchedSize?.nombre ? String(matchedSize.nombre) : '';
  const normal = getNormalPriceForSize(prod, resolvedSizeId);

  return {
    rowKey: makeRowKey(prod.id, resolvedSizeId),
    id_producto: Number(prod.id),
    product_name: prod.name,
    id_tamaño: resolvedSizeId,
    size_name: sizeName,
    sizes_obj: sizesObj,
    base_min_price: normal,
    normal_price: normal,
    titulo: buildPromoTitle(prod.name, sizeName),
    precio_promocional: computePromotionalPrice(normal),
  };
};

const toggleProductInBatch = (prod: any) => {
  const id = Number(prod.id);
  if (isSelectedProduct(id)) {
    batchRows.value = batchRows.value.filter((r) => r.id_producto !== id);
  } else {
    batchRows.value.push(buildBatchRow(prod, null));
  }
};

const toggleProductSizeInBatch = (prod: any, sizeId: number | null) => {
  const id = Number(prod.id);
  const normSize = sizeId !== null && sizeId !== undefined ? Number(sizeId) : null;
  const key = makeRowKey(id, normSize);

  if (batchRows.value.some((r) => r.rowKey === key)) {
    batchRows.value = batchRows.value.filter((r) => r.rowKey !== key);
    return;
  }

  // No mezclar "Todos" con tamaños específicos del mismo producto
  if (normSize === null) {
    batchRows.value = batchRows.value.filter((r) => r.id_producto !== id);
  } else {
    batchRows.value = batchRows.value.filter((r) => !(r.id_producto === id && r.id_tamaño === null));
  }

  batchRows.value.push(buildBatchRow(prod, normSize));
};

const hasAvailableExtraSize = (row: BatchRow): boolean => {
  if (!Array.isArray(row.sizes_obj) || row.sizes_obj.length === 0) return false;
  return row.sizes_obj.some((sz: any) => !hasRowForProductSize(row.id_producto, Number(sz.id_tamaño)));
};

const addAnotherSizeRow = (row: BatchRow) => {
  const prod = props.products.find((p: any) => Number(p.id) === Number(row.id_producto));
  if (!prod) return;
  const nextSz = (row.sizes_obj || []).find((sz: any) => !hasRowForProductSize(row.id_producto, Number(sz.id_tamaño)));
  if (!nextSz) return;
  if (row.id_tamaño === null && row.sizes_obj.length > 0) {
    const firstSz = row.sizes_obj.find((sz: any) => Number(sz.id_tamaño) !== Number(nextSz.id_tamaño)) || nextSz;
    row.id_tamaño = Number(firstSz.id_tamaño);
    onRowSizeChange(row);
    if (firstSz === nextSz) return;
  }
  const insertAt = batchRows.value.indexOf(row) + 1;
  batchRows.value.splice(insertAt, 0, buildBatchRow(prod, Number(nextSz.id_tamaño)));
};

const selectAllFiltered = () => {
  filteredBatchProducts.value.forEach((prod: any) => {
    if (!isSelectedProduct(prod.id)) {
      batchRows.value.push(buildBatchRow(prod, null));
    }
  });
};

const clearBatchSelection = () => {
  batchRows.value = [];
};

const removeRow = (rowKey: string) => {
  batchRows.value = batchRows.value.filter((r) => r.rowKey !== rowKey);
};

const onRowSizeChange = (row: BatchRow) => {
  const prod = props.products.find((p: any) => Number(p.id) === Number(row.id_producto));
  if (!prod) return;
  const normSize = row.id_tamaño !== null && row.id_tamaño !== undefined ? Number(row.id_tamaño) : null;
  if (normSize === null) {
    batchRows.value = batchRows.value.filter((r) => r === row || r.id_producto !== row.id_producto);
  }
  const matchedSize = normSize !== null ? (row.sizes_obj || []).find((s: any) => Number(s.id_tamaño) === normSize) : null;
  row.size_name = matchedSize?.nombre ? String(matchedSize.nombre) : '';
  row.rowKey = makeRowKey(row.id_producto, normSize);
  row.normal_price = getNormalPriceForSize(prod, normSize);
  row.precio_promocional = computePromotionalPrice(row.normal_price);
  row.titulo = buildPromoTitle(row.product_name, row.size_name);
};

const applyDiscountStrategyToSelected = () => {
  batchRows.value.forEach((row) => {
    row.precio_promocional = computePromotionalPrice(row.normal_price);
    if (batchTitlePrefix.value.trim()) {
      row.titulo = buildPromoTitle(row.product_name, row.size_name);
    }
  });
};

const hasInvalidBatchRows = computed(() => {
  return batchRows.value.some((r) => !r.titulo.trim() || Number(r.precio_promocional) <= 0 || (r.normal_price > 0 && Number(r.precio_promocional) >= r.normal_price));
});

const submitBatch = () => {
  if (batchRows.value.length === 0 || hasInvalidBatchRows.value) return;
  if (batchEndDate.value < batchStartDate.value) return;

  emit('submit-batch', {
    reemplazar_conflictos: replaceConflicts.value,
    items: batchRows.value.map((r) => ({
      id_producto: r.id_producto,
      id_tamaño: r.id_tamaño ? Number(r.id_tamaño) : null,
      titulo: r.titulo.trim(),
      descripcion: null,
      precio_promocional: Math.round(Number(r.precio_promocional)),
      fecha_inicio: batchStartDate.value,
      fecha_fin: batchEndDate.value,
      activo: batchActive.value,
    })),
  });
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
  overflow-y: auto;
}

.modal-card {
  position: relative;
  width: min(100%, 460px);
  max-height: 92vh;
  border-radius: 18px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.modal-card.modal-card-wide {
  width: min(100%, 860px);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.modal-header-title {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.header-icon-pill {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--DC-brown, #513119);
}

.modal-header-desc {
  margin: 0;
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.mode-switch-bar {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 6px;
  padding: 0.65rem 1.25rem 0;
}

.mode-tab-btn {
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  border-radius: 10px;
  padding: 0.5rem 0.75rem;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.mode-tab-btn.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.modal-body {
  padding: 1.15rem 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
  overflow-y: auto;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.required { color: #dc2626; }

.modal-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.6rem 0.8rem;
  color: var(--DC-gray, #2c2724);
  font-size: 0.85rem;
  outline: none;
}

.modal-input:focus {
  border-color: var(--DC-orange, #e28743);
}

.form-row-2,
.batch-config-grid,
.strategy-controls {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

.strategy-box {
  background: #fff9f2;
  border: 1px solid rgba(226, 135, 67, 0.22);
  border-radius: 12px;
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.strategy-value-row {
  display: flex;
  gap: 6px;
}

.btn-apply-rule {
  padding: 0 0.85rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-brown, #513119);
  color: #fff;
  font-weight: 700;
  font-size: 0.78rem;
  cursor: pointer;
  white-space: nowrap;
}

.replace-checkbox-label {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.76rem;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
}

.batch-picker-box {
  border: 1px solid rgba(81, 49, 25, 0.1);
  border-radius: 12px;
  padding: 0.7rem;
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
}

.batch-picker-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}

.batch-search-input {
  flex: 1 1 190px;
}

.batch-cat-select {
  flex: 0 1 170px;
}

.btn-mini-action {
  padding: 0.5rem 0.75rem;
  border-radius: 9px;
  border: 1px solid rgba(226, 135, 67, 0.3);
  background: #fff4e6;
  color: var(--DC-brown, #513119);
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
}

.btn-mini-action.muted {
  background: #f1f5f9;
  border-color: #cbd5e1;
  color: #475569;
}

.batch-products-checklist {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 6px;
  max-height: 155px;
  overflow-y: auto;
  padding-right: 4px;
}

.batch-prod-check-item {
  display: flex;
  flex-direction: column;
  gap: 5px;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 9px;
  padding: 0.45rem 0.6rem;
  font-size: 0.76rem;
}

.batch-prod-main-row {
  display: flex;
  align-items: center;
  gap: 6px;
  cursor: pointer;
}

.chk-sizes-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  padding-left: 18px;
}

.size-chip-btn {
  border: 1px solid rgba(81, 49, 25, 0.16);
  background: #ffffff;
  color: var(--DC-brown, #513119);
  border-radius: 999px;
  padding: 2px 7px;
  font-size: 0.68rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.15s ease;
}

.size-chip-btn.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: #ffffff;
}

.row-size-cell {
  display: flex;
  align-items: center;
  gap: 4px;
}

.btn-add-size-row {
  flex-shrink: 0;
  border: 1px dashed rgba(226, 135, 67, 0.6);
  background: #fff9f2;
  color: var(--DC-brown, #513119);
  border-radius: 6px;
  padding: 0.25rem 0.4rem;
  font-size: 0.68rem;
  font-weight: 800;
  cursor: pointer;
  white-space: nowrap;
}

.batch-prod-check-item.selected {
  background: #fff4e6;
  border-color: var(--DC-orange, #e28743);
}

.chk-name {
  flex: 1;
  font-weight: 700;
  color: var(--DC-brown, #513119);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.chk-cat {
  font-size: 0.68rem;
  color: var(--DC-text-gray, #7c7468);
}

.chk-price {
  color: var(--DC-orange, #e28743);
}

.batch-preview-table-wrap {
  max-height: 195px;
  overflow: auto;
  border: 1px solid rgba(81, 49, 25, 0.1);
  border-radius: 10px;
}

.batch-preview-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.78rem;
}

.batch-preview-table th,
.batch-preview-table td {
  padding: 0.45rem 0.55rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.06);
  text-align: left;
}

.batch-preview-table th {
  background: #faf7f4;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  position: sticky;
  top: 0;
}

.batch-preview-table tr.row-invalid {
  background: #fef2f2;
}

.col-prod-name {
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.col-normal {
  color: #64748b;
  white-space: nowrap;
}

.mini-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.15);
  border-radius: 7px;
  padding: 0.3rem 0.45rem;
  font-size: 0.76rem;
  background: #fff;
}

.price-mini {
  width: 86px;
  font-weight: 700;
}

.btn-remove-row {
  border: none;
  background: transparent;
  color: #dc2626;
  font-size: 1rem;
  font-weight: 800;
  cursor: pointer;
}

.batch-warning-msg {
  margin: 0.45rem 0.65rem;
  font-size: 0.74rem;
  font-weight: 700;
  color: #dc2626;
}

.batch-empty-hint {
  text-align: center;
  padding: 0.9rem;
  font-size: 0.8rem;
  color: var(--DC-text-gray, #7c7468);
  background: #faf7f4;
  border-radius: 10px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 0.85rem 1.25rem;
  background: #fffdfa;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.btn-cancel {
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
}

.btn-save {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-save:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

@media (max-width: 640px) {
  .form-row-2,
  .batch-config-grid,
  .strategy-controls {
    grid-template-columns: 1fr;
  }
}
</style>
