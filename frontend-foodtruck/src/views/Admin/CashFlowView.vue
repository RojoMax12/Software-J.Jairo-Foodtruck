<template>
  <div class="cashflow-view">
    <header class="inventory-header">
      <div class="header-copy">
        <span class="eyebrow">Operaciones</span>
        <h1>Flujo de Caja</h1>
        <p>Analiza tus ingresos y egresos diarios, revisa el saldo actual y sigue la misma línea visual de la aplicación.</p>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="reloadTransactions" :disabled="isLoading">
          <RefreshCw :size="18" :class="{ spinning: isLoading }" />
          <span>{{ isLoading ? 'Actualizando' : 'Actualizar' }}</span>
        </button>
      </div>
    </header>

    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <Wallet :size="24" />
        </div>
        <div>
          <span class="summary-label">Saldo Total</span>
          <strong class="summary-value">{{ formatCurrency(stats.balance) }}</strong>
          <p class="summary-helper">Saldo disponible al momento</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <TrendingUp :size="24" />
        </div>
        <div>
          <span class="summary-label">Ingresos de hoy</span>
          <strong class="summary-value">{{ formatCurrency(stats.income) }}</strong>
          <p class="summary-helper">Ventas registradas</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <TrendingDown :size="24" />
        </div>
        <div>
          <span class="summary-label">Egresos de hoy</span>
          <strong class="summary-value">{{ formatCurrency(stats.expense) }}</strong>
          <p class="summary-helper">Gastos registrados</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-orange">
          <ArrowRightLeft :size="24" />
        </div>
        <div>
          <span class="summary-label">Flujo Neto</span>
          <strong class="summary-value">{{ stats.net > 0 ? '+' : '' }}{{ formatCurrency(stats.net) }}</strong>
          <p class="summary-helper">Ingresos vs Egresos diarios</p>
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
                placeholder="Buscar por fecha, categoría o descripción..."
              />
            </div>

            <div class="select-box">
              <Filter :size="18" class="select-icon" />
              <select v-model="typeFilter">
                <option value="all">Todos los movimientos</option>
                <option value="ingreso">Ingresos</option>
                <option value="egreso">Egresos</option>
              </select>
            </div>
          </div>

          <div class="toolbar-right">
            <span class="results-chip">{{ filteredTransactions.length }} resultados</span>
          </div>
        </div>

        <div class="panel-card table-card">
          <div v-if="isLoading" class="state-card">
            <div class="spinner"></div>
            <p>Cargando movimientos...</p>
          </div>

          <div v-else-if="filteredTransactions.length === 0" class="state-card empty-state">
            <FileText :size="40" />
            <p>No hay movimientos que coincidan con los filtros.</p>
          </div>

          <div v-else class="table-wrapper desktop-table-only">
            <table class="inventory-table">
              <thead>
                <tr>
                  <th>Fecha/Hora</th>
                  <th>Tipo</th>
                  <th>Categoría</th>
                  <th class="text-right">Monto</th>
                  <th>Método Pago</th>
                  <th>Descripción</th>
                  <th class="text-center">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="trx in filteredTransactions" :key="trx.id">
                  <td>
                    <div class="meta-inline">
                      <Clock3 :size="16" />
                      <strong>{{ trx.date }}</strong>
                    </div>
                  </td>
                  <td>
                    <span class="stock-badge" :class="trx.type === 'ingreso' ? 'status-ok' : 'status-critical'">
                      {{ trx.type === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                    </span>
                  </td>
                  <td>
                    <span class="category-pill">{{ trx.category }}</span>
                  </td>
                  <td class="text-right stock-amount">
                    {{ formatCurrency(trx.amount) }}
                  </td>
                  <td>
                    <span>{{ trx.paymentMethod }}</span>
                  </td>
                  <td>
                    <span class="description-text">{{ trx.description }}</span>
                  </td>
                  <td class="text-center">
                    <span class="stock-badge" :class="trx.status === 'completado' ? 'status-ok' : 'status-low'">
                      {{ trx.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- VISTA TARJETAS MÓVIL (MOBILE TRANSACTIONS) -->
          <div v-if="!isLoading && filteredTransactions.length > 0" class="mobile-transactions-cards mobile-only">
            <div v-for="trx in filteredTransactions" :key="'mob-trx-' + trx.id" class="mobile-trx-card">
              <div class="mob-trx-header">
                <div class="mob-trx-meta">
                  <Clock3 :size="14" />
                  <span>{{ trx.date }}</span>
                </div>
                <span class="stock-badge" :class="trx.type === 'ingreso' ? 'status-ok' : 'status-critical'">
                  {{ trx.type === 'ingreso' ? 'Ingreso' : 'Egreso' }}
                </span>
              </div>
              <div class="mob-trx-body">
                <div class="mob-trx-main">
                  <span class="category-pill">{{ trx.category }}</span>
                  <strong class="mob-trx-amount" :class="trx.type === 'ingreso' ? 'text-green' : 'text-red'">
                    {{ trx.type === 'ingreso' ? '+' : '-' }}{{ formatCurrency(trx.amount) }}
                  </strong>
                </div>
                <p v-if="trx.description" class="mob-trx-desc">{{ trx.description }}</p>
                <div class="mob-trx-footer">
                  <small class="mob-trx-method">Pago: {{ trx.paymentMethod }}</small>
                  <span class="stock-badge" :class="trx.status === 'completado' ? 'status-ok' : 'status-low'">
                    {{ trx.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <aside class="side-panel">
        <div class="panel-card quick-update-card">
          <button class="dropdown-trigger" type="button" @click="toggleNewTransactionMenu">
            <div class="side-title-row no-margin">
              <div>
                <span class="eyebrow">Caja</span>
                <h2>Registrar Movimiento</h2>
              </div>
              <ChevronDown :size="22" class="title-icon dropdown-icon" :class="{ open: isNewTransactionOpen }" />
            </div>
          </button>

          <Transition name="dropdown-fade">
            <form v-if="isNewTransactionOpen" class="quick-update-form" @submit.prevent="submitTransaction">
              <label class="field-label">
                Tipo de movimiento
                <select v-model="form.type" class="form-input">
                  <option value="ingreso">Ingreso</option>
                  <option value="egreso">Egreso</option>
                </select>
              </label>

              <label class="field-label">
                Categoría
                <select v-model="form.category" class="form-input">
                  <option value="Ventas" v-if="form.type === 'ingreso'">Ventas</option>
                  <option value="Caja Inicial" v-if="form.type === 'ingreso'">Caja Inicial</option>
                  <option value="Proveedores" v-if="form.type === 'egreso'">Proveedores</option>
                  <option value="Mantenimiento" v-if="form.type === 'egreso'">Mantenimiento</option>
                  <option value="Servicios" v-if="form.type === 'egreso'">Servicios básicos</option>
                </select>
              </label>

              <div class="grid-2-cols">
                <label class="field-label">
                  Monto
                  <input
                    v-model.number="form.amount"
                    type="number"
                    min="1"
                    class="form-input"
                    placeholder="0"
                  />
                </label>

                <label class="field-label">
                  Método de pago
                  <select v-model="form.paymentMethod" class="form-input">
                    <option value="Efectivo">Efectivo</option>
                    <option value="Débito">Débito</option>
                    <option value="Transferencia">Transferencia</option>
                  </select>
                </label>
              </div>

              <label class="field-label">
                Descripción
                <textarea
                  v-model="form.description"
                  class="form-input"
                  placeholder="Ej. Pago a proveedor de verduras..."
                  rows="2"
                ></textarea>
              </label>

              <button class="btn-primary" type="submit" :disabled="isSaving || !isValidForm">
                <RefreshCw :size="18" :class="{ spinning: isSaving }" v-if="isSaving" />
                <span>{{ isSaving ? 'Registrando...' : 'Guardar movimiento' }}</span>
              </button>
            </form>
          </Transition>
        </div>
      </aside>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { 
  Wallet, TrendingUp, TrendingDown, ArrowRightLeft, 
  RefreshCw, Search, Filter, FileText, Clock3, ChevronDown 
} from 'lucide-vue-next';
import cashFlowService, { type CashTransaction } from '@/services/cashFlowService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();

// Tipos adaptados para Flujo de Caja
type TransactionType = 'ingreso' | 'egreso';
type TransactionStatus = 'completado' | 'pendiente';

interface Transaction {
  id: string;
  date: string;
  type: TransactionType;
  category: string;
  amount: number;
  paymentMethod: string;
  description: string;
  status: TransactionStatus;
}

// Estados
const transactions = ref<Transaction[]>([]);
const isLoading = ref(true);
const isSaving = ref(false);
const isNewTransactionOpen = ref(true);
const searchQuery = ref('');
const typeFilter = ref<'all' | TransactionType>('all');

// Estado del formulario
const form = ref({
  type: 'ingreso' as TransactionType,
  category: 'Ventas',
  amount: null as number | null,
  paymentMethod: 'Débito',
  description: ''
});

// Carga real de movimientos y ventas
const fetchTransactions = async () => {
  isLoading.value = true;
  try {
    const combined = await cashFlowService.getCombinedTransactions();
    transactions.value = combined;
  } catch (error) {
    console.error('Error al cargar movimientos de caja:', error);
    notify('Error al cargar los datos de caja', 'warning');
  } finally {
    isLoading.value = false;
  }
};

const reloadTransactions = () => {
  fetchTransactions();
  notify('Datos de caja actualizados', 'info');
};

const toggleNewTransactionMenu = () => {
  isNewTransactionOpen.value = !isNewTransactionOpen.value;
};

// Utilidad para formato de moneda (CLP)
const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP' }).format(value);
};

const isValidForm = computed(() => {
  return form.value.amount && form.value.amount > 0 && form.value.category !== '';
});

const submitTransaction = async () => {
  if (!isValidForm.value) return;

  isSaving.value = true;
  try {
    const newTrx = cashFlowService.saveCustomTransaction({
      type: form.value.type,
      category: form.value.category,
      amount: Number(form.value.amount || 0),
      paymentMethod: form.value.paymentMethod,
      description: form.value.description || 'Movimiento manual',
      status: 'completado'
    });

    transactions.value.unshift(newTrx);
    notify(`¡${form.value.type === 'ingreso' ? 'Ingreso' : 'Egreso'} registrado correctamente!`, 'success');
    
    // Reset formulario
    form.value.amount = null;
    form.value.description = '';
  } catch (error) {
    console.error('Error al guardar movimiento:', error);
    notify('Error al guardar el movimiento', 'warning');
  } finally {
    isSaving.value = false;
  }
};

// Filtros y KPIs
const filteredTransactions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase();

  return transactions.value.filter((trx) => {
    const matchesQuery = !query || [trx.category, trx.description, trx.paymentMethod]
      .some((value) => value.toLowerCase().includes(query));
    const matchesType = typeFilter.value === 'all' || trx.type === typeFilter.value;

    return matchesQuery && matchesType;
  });
});

const stats = computed(() => {
  let income = 0;
  let expense = 0;

  // En un caso real estos datos podrían venir pre-calculados del backend
  transactions.value.forEach(trx => {
    if (trx.type === 'ingreso') income += trx.amount;
    if (trx.type === 'egreso') expense += trx.amount;
  });

  return {
    balance: 1250000, // Simulación de saldo en cuenta/caja general
    income,
    expense,
    net: income - expense
  };
});

onMounted(() => {
  fetchTransactions();
});
</script>

<style scoped>
/* Aprovechamos toda tu estructura CSS previa y añadimos las variables necesarias para Flujo de Caja */

.cashflow-view {
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
  color: var(--DC-brown, #4a3424);
  font-size: 2.6rem;
  line-height: 1;
  margin: 0.35rem 0 0.6rem;
}

.header-copy p {
  max-width: 720px;
  color: var(--DC-text-gray, #a39183);
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
  color: var(--DC-brown, #4a3424);
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
  color: var(--DC-brown, #4a3424);
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
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 10px 24px rgba(226, 135, 67, 0.12);
}

.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1rem;
}

.summary-card, .panel-card {
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

/* Colores de las tarjetas */
.bg-summary-brown { background: var(--DC-bg-gray, #f4ece4); color: var(--DC-brown, #4a3424); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.14); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.12); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(62, 165, 93, 0.12); color: #2f8b4c; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #a39183);
  font-size: 0.82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #1a1a1a);
  font-size: 1.65rem;
  line-height: 1.1;
  margin: 0.2rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #a39183);
  font-size: 0.85rem;
}

.inventory-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.8fr) minmax(320px, 0.9fr);
  gap: 1rem;
  align-items: start;
}

.main-panel, .side-panel {
  display: grid;
  gap: 1rem;
}

.panel-card { padding: 1rem; }

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

.search-box, .select-box {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  background: var(--DC-bg-gray, #f4ece4);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 16px;
  padding: 0.8rem 0.95rem;
}

.search-box input, .select-box select {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray, #1a1a1a);
  font-size: 0.95rem;
  min-width: 250px;
}

.select-box select { min-width: 180px; cursor: pointer; }
.search-icon, .select-icon { color: var(--DC-text-gray, #a39183); flex-shrink: 0; }

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.55rem 0.85rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #4a3424);
  font-size: 0.85rem;
  font-weight: 700;
}

/* Tabla */
.table-card { padding: 0; overflow: hidden; }
.table-wrapper { overflow-x: auto; }
.inventory-table { width: 100%; border-collapse: collapse; }

.inventory-table thead th {
  background: var(--DC-bg-gray, #f4ece4);
  color: var(--DC-brown, #4a3424);
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

.meta-inline { display: inline-flex; align-items: center; gap: 0.45rem; color: var(--DC-text-gray, #a39183); }
.meta-inline strong { color: var(--DC-gray, #1a1a1a); }
.text-right { text-align: right; }
.text-center { text-align: center; }

.category-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.7rem;
  border-radius: 999px;
  background: rgba(81, 49, 25, 0.06);
  color: var(--DC-brown, #4a3424);
  font-size: 0.78rem;
  font-weight: 700;
}

.stock-amount {
  font-size: 1.15rem;
  font-weight: 800;
  color: var(--DC-brown, #4a3424);
}

.description-text {
  font-size: 0.88rem;
  color: var(--DC-text-gray, #a39183);
  max-width: 250px;
  display: inline-block;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stock-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 800;
  text-transform: capitalize;
}

.status-ok { background: rgba(62, 165, 93, 0.12); color: #2f8b4c; }
.status-critical { background: rgba(216, 0, 86, 0.14); color: var(--DC-pink, #d80056); }
.status-low { background: rgba(226, 135, 67, 0.16); color: var(--DC-orange, #e28743); }

/* Formulario */
.quick-update-card { display: grid; gap: 1rem; }
.side-title-row {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1rem;
}
.side-title-row h2 { color: var(--DC-brown, #4a3424); font-size: 1.2rem; margin-top: 0.35rem; }
.no-margin { margin-bottom: 0; }
.dropdown-trigger { border: none; background: transparent; padding: 0; text-align: left; cursor: pointer; }
.dropdown-icon { transition: transform 0.2s ease; color: var(--DC-orange, #e28743); }
.dropdown-icon.open { transform: rotate(180deg); }

.quick-update-form { display: grid; gap: 0.9rem; }
.grid-2-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }

.field-label {
  display: grid;
  gap: 0.45rem;
  color: var(--DC-brown, #4a3424);
  font-size: 0.85rem;
  font-weight: 700;
}

.form-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 14px;
  background: var(--DC-bg-gray, #f4ece4);
  padding: 0.85rem 0.95rem;
  color: var(--DC-gray, #1a1a1a);
  outline: none;
  font-family: inherit;
  resize: vertical;
}

.form-input:focus {
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.12);
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  padding: 0.9rem 1rem;
  border-radius: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  cursor: pointer;
  font-weight: 800;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  margin-top: 0.5rem;
}
.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(226, 135, 67, 0.22);
}
.btn-primary:disabled { opacity: 0.65; cursor: not-allowed; }

.mobile-only {
  display: none !important;
}

/* ==========================================================
   RESPONSIVIDAD MÓVIL Y TABLET
========================================================== */
@media (max-width: 1024px) {
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .inventory-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .cashflow-view {
    padding: 15px;
  }

  .inventory-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .header-actions {
    width: 100%;
  }

  .btn-secondary {
    width: 100%;
    justify-content: center;
  }

  .summary-grid {
    grid-template-columns: 1fr;
    gap: 10px;
  }

  .toolbar-card {
    flex-direction: column;
    align-items: stretch;
    gap: 10px;
  }

  .toolbar-left {
    flex-direction: column;
    width: 100%;
    gap: 8px;
  }

  .search-box, .select-box {
    width: 100%;
  }

  .search-box input, .select-box select {
    width: 100%;
  }

  .table-card {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    padding: 10px;
  }

  .grid-2-cols {
    grid-template-columns: 1fr;
  }

  .desktop-table-only {
    display: none !important;
  }

  .mobile-only {
    display: flex !important;
    flex-direction: column;
    gap: 10px;
    width: 100%;
  }

  .mobile-trx-card {
    background: white;
    border: 1px solid #eeedee;
    border-radius: 14px;
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.04);
  }

  .mob-trx-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .mob-trx-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: #6e6a75;
    font-weight: 700;
  }

  .mob-trx-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .mob-trx-main {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .mob-trx-amount {
    font-size: 1.1rem;
    font-weight: 900;
  }

  .mob-trx-amount.text-green { color: #16a34a; }
  .mob-trx-amount.text-red { color: #dc2626; }

  .mob-trx-desc {
    font-size: 0.88rem;
    color: #322c44;
    margin: 0;
    line-height: 1.3;
  }

  .mob-trx-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #eeedee;
    padding-top: 8px;
    font-size: 0.78rem;
    color: #6e6a75;
  }
}
</style>