<template>
  <div class="cashflow-view">
    <!-- CABECERA -->
    <header class="cash-header">
      <div class="header-copy">
        <span class="eyebrow">Gestión Financiera & Punto de Venta</span>
        <h1>Caja & Arqueo de Turnos</h1>
        <p>Control de apertura de turno, fondo de caja, desglose por medios de pago, registro de gastos y cuadratura de caja.</p>
      </div>

      <div class="header-actions">
        <button class="btn-refresh" @click="loadData" :disabled="isLoading">
          <RefreshCw :size="18" :class="{ spinning: isLoading }" />
          <span>{{ isLoading ? 'Cargando...' : 'Actualizar' }}</span>
        </button>
      </div>
    </header>

    <!-- BANNER DE ESTADO DEL TURNO -->
    <section class="shift-banner" :class="session.isOpen ? 'banner-open' : 'banner-closed'">
      <div class="shift-status-info">
        <div class="status-indicator">
          <span class="pulse-dot" v-if="session.isOpen"></span>
          <span class="status-badge" :class="session.isOpen ? 'badge-open' : 'badge-closed'">
            {{ session.isOpen ? 'CAJA ABIERTA (TURNO ACTIVO)' : 'CAJA CERRADA' }}
          </span>
        </div>

        <div class="shift-meta-details" v-if="session.isOpen">
          <span><strong>Cajero:</strong> {{ session.cashierName }}</span>
          <span><strong>Apertura:</strong> {{ session.openedAt }}</span>
          <span><strong>Fondo Inicial (Vuelto):</strong> {{ formatCurrency(session.initialCash) }}</span>
        </div>
        <div class="shift-meta-details shift-closed-prompt" v-else>
          <span><strong>Estado:</strong> La caja está cerrada. Abre un turno para comenzar la jornada y registrar el fondo inicial.</span>
        </div>
      </div>

      <div class="shift-banner-actions">
        <button v-if="session.isOpen" class="btn-shift-close" @click="openCloseShiftModal">
          <Lock :size="17" />
          <span>Cerrar Turno & Arqueo</span>
        </button>
        <button v-else class="btn-shift-open" @click="openOpenShiftModal">
          <Unlock :size="17" />
          <span>Abrir Nuevo Turno</span>
        </button>
      </div>
    </section>

    <!-- TARJETAS DE RESUMEN / KPIS DEL TURNO -->
    <section class="kpis-grid">
      <!-- Fondo Inicial -->
      <div class="kpi-card">
        <div class="kpi-icon-box icon-amber">
          <Coins :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Fondo Inicial</span>
          <strong class="kpi-val">{{ formatCurrency(session.initialCash) }}</strong>
          <small class="kpi-hint">Base para dar vuelto</small>
        </div>
      </div>

      <!-- Ventas Totales -->
      <div class="kpi-card">
        <div class="kpi-icon-box icon-green">
          <TrendingUp :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Ventas del Turno</span>
          <strong class="kpi-val val-green">{{ formatCurrency(shiftSummary.totalSales) }}</strong>
          <small class="kpi-hint">{{ shiftSalesCount }} pedidos cobrados</small>
        </div>
      </div>

      <!-- Efectivo Esperado en Gaveta -->
      <div class="kpi-card highlight-card">
        <div class="kpi-icon-box icon-orange">
          <Wallet :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Efectivo en Gaveta</span>
          <strong class="kpi-val val-orange">{{ formatCurrency(shiftSummary.expectedCashInDrawer) }}</strong>
          <small class="kpi-hint">Fondo + Ventas Efectivo - Egresos</small>
        </div>
      </div>

      <!-- Débito / Tarjetas -->
      <div class="kpi-card">
        <div class="kpi-icon-box icon-blue">
          <CreditCard :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Débito / Tarjetas</span>
          <strong class="kpi-val">{{ formatCurrency(shiftSummary.totalSalesDebit) }}</strong>
          <small class="kpi-hint">Voucher Transbank / POS</small>
        </div>
      </div>

      <!-- Transferencias -->
      <div class="kpi-card">
        <div class="kpi-icon-box icon-purple">
          <Send :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Transferencias</span>
          <strong class="kpi-val">{{ formatCurrency(shiftSummary.totalSalesTransfer) }}</strong>
          <small class="kpi-hint">Comprobantes bancarios</small>
        </div>
      </div>

      <!-- Gastos / Retiros -->
      <div class="kpi-card">
        <div class="kpi-icon-box icon-red">
          <TrendingDown :size="22" />
        </div>
        <div class="kpi-info">
          <span class="kpi-label">Gastos / Egresos</span>
          <strong class="kpi-val val-red">{{ formatCurrency(shiftSummary.totalExpenses) }}</strong>
          <small class="kpi-hint">Compras de insumos / retiros</small>
        </div>
      </div>
    </section>

    <!-- BARRA DE ACCIÓN Y PESTAÑAS -->
    <section class="main-content-layout">
      <div class="tabs-header-bar">
        <div class="view-tabs">
          <button
            class="tab-btn"
            :class="{ active: currentTab === 'current-shift' }"
            @click="currentTab = 'current-shift'"
          >
            <ReceiptText :size="17" />
            <span>Movimientos del Turno ({{ currentShiftTransactions.length }})</span>
          </button>

          <button
            class="tab-btn"
            :class="{ active: currentTab === 'history' }"
            @click="currentTab = 'history'"
          >
            <History :size="17" />
            <span>Historial de Arqueos ({{ closedSessions.length }})</span>
          </button>
        </div>

        <div class="quick-action-buttons">
          <button class="btn-quick-expense" @click="openQuickExpenseModal">
            <MinusCircle :size="16" />
            <span>Registrar Gasto / Egreso</span>
          </button>
          <button class="btn-quick-income" @click="openQuickIncomeModal">
            <PlusCircle :size="16" />
            <span>Registrar Ingreso Extra</span>
          </button>
        </div>
      </div>

      <!-- ================= PESTAÑA 1: MOVIMIENTOS DEL TURNO ACTUAL ================= -->
      <div v-if="currentTab === 'current-shift'" class="tab-pane animate-fade-in">
        <!-- Barra de filtros -->
        <div class="filter-toolbar">
          <div class="search-wrap">
            <Search :size="16" class="search-icon" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por descripción, cliente, pedido..."
              class="search-input"
            />
          </div>

          <div class="filter-selects">
            <select v-model="filterType" class="filter-select">
              <option value="all">Todos los tipos</option>
              <option value="ingreso">Solo Ingresos / Ventas</option>
              <option value="egreso">Solo Egresos / Gastos</option>
            </select>

            <select v-model="filterMethod" class="filter-select">
              <option value="all">Todos los medios de pago</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Débito / Tarjeta">Débito / Tarjetas</option>
              <option value="Transferencia">Transferencias</option>
            </select>
          </div>
        </div>

        <!-- Tabla de Movimientos -->
        <div class="table-responsive">
          <table class="cash-table">
            <thead>
              <tr>
                <th>Fecha / Hora</th>
                <th>Tipo</th>
                <th>Concepto / Descripción</th>
                <th>Medio de Pago</th>
                <th>Categoría</th>
                <th class="text-right">Monto</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredCurrentTransactions.length === 0">
                <td colspan="6" class="empty-table-cell">
                  <div class="empty-state">
                    <ReceiptText :size="38" />
                    <p>No se encontraron movimientos registrados en este turno.</p>
                  </div>
                </td>
              </tr>
              <tr
                v-for="trx in filteredCurrentTransactions"
                :key="trx.id"
                :class="trx.type === 'ingreso' ? 'row-income' : 'row-expense'"
              >
                <td class="col-date">{{ trx.date }}</td>
                <td>
                  <span class="type-pill" :class="trx.type === 'ingreso' ? 'pill-income' : 'pill-expense'">
                    {{ trx.type === 'ingreso' ? '+ Ingreso' : '- Egreso' }}
                  </span>
                </td>
                <td class="col-desc">
                  <strong>{{ trx.description }}</strong>
                </td>
                <td>
                  <span class="payment-method-tag" :class="getMethodClass(trx.paymentMethod)">
                    {{ trx.paymentMethod }}
                  </span>
                </td>
                <td class="col-category">{{ trx.category }}</td>
                <td class="text-right col-amount" :class="trx.type === 'ingreso' ? 'text-green' : 'text-red'">
                  <strong>{{ trx.type === 'ingreso' ? '+' : '-' }}{{ formatCurrency(trx.amount) }}</strong>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ================= PESTAÑA 2: HISTORIAL DE ARQUEOS ================= -->
      <div v-else class="tab-pane animate-fade-in">
        <div class="table-responsive">
          <table class="cash-table history-table">
            <thead>
              <tr>
                <th>Turno ID</th>
                <th>Apertura</th>
                <th>Cierre</th>
                <th>Cajero</th>
                <th>Fondo Inicial</th>
                <th>Total Ventas</th>
                <th>Efectivo Esperado</th>
                <th>Efectivo Contado</th>
                <th>Diferencia / Cuadratura</th>
                <th>Notas</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="closedSessions.length === 0">
                <td colspan="10" class="empty-table-cell">
                  <div class="empty-state">
                    <History :size="38" />
                    <p>Aún no hay turnos cerrados ni arqueos guardados.</p>
                  </div>
                </td>
              </tr>
              <tr v-for="h in closedSessions" :key="h.id">
                <td><code>{{ h.id }}</code></td>
                <td>{{ h.openedAt }}</td>
                <td>{{ h.closedAt || '-' }}</td>
                <td><strong>{{ h.cashierName }}</strong></td>
                <td>{{ formatCurrency(h.initialCash) }}</td>
                <td class="text-green"><strong>{{ formatCurrency(h.summary?.totalSales || 0) }}</strong></td>
                <td>{{ formatCurrency(h.summary?.expectedCashInDrawer || 0) }}</td>
                <td><strong>{{ formatCurrency(h.summary?.actualCashCounted || 0) }}</strong></td>
                <td>
                  <span
                    class="diff-badge"
                    :class="getDiffClass(h.summary?.difference || 0)"
                  >
                    {{ formatDiff(h.summary?.difference || 0) }}
                  </span>
                </td>
                <td class="col-notes">{{ h.summary?.notes || 'Sin observaciones' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- ================= MODAL: ARQUEO Y CIERRE DE TURNO ================= -->
    <div v-if="isCloseModalOpen" class="modal-backdrop" @click.self="isCloseModalOpen = false">
      <div class="modal-card modal-arqueo animate-scale-up">
        <div class="modal-header modal-header-dark">
          <div class="header-icon-title">
            <Lock :size="22" />
            <div>
              <h3>Arqueo de Caja & Cierre de Turno</h3>
              <p>Compara el efectivo calculado por el sistema con el dinero real contado en la gaveta.</p>
            </div>
          </div>
          <button class="close-btn" @click="isCloseModalOpen = false"><X :size="20" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitCloseShift">
          <!-- Desglose de Caja -->
          <div class="arqueo-breakdown">
            <div class="breakdown-row">
              <span>(+) Fondo Inicial de Vuelto:</span>
              <strong>{{ formatCurrency(session.initialCash) }}</strong>
            </div>
            <div class="breakdown-row">
              <span>(+) Ventas en Efectivo:</span>
              <strong class="text-green">+{{ formatCurrency(shiftSummary.totalSalesCash) }}</strong>
            </div>
            <div class="breakdown-row">
              <span>(-) Gastos / Egresos de Caja:</span>
              <strong class="text-red">-{{ formatCurrency(shiftSummary.totalExpenses) }}</strong>
            </div>
            <div class="breakdown-row total-expected-row">
              <span>(=) Efectivo Esperado en Gaveta:</span>
              <strong class="expected-amount">{{ formatCurrency(shiftSummary.expectedCashInDrawer) }}</strong>
            </div>
          </div>

          <!-- Otros medios de pago (Informativo) -->
          <div class="other-payments-info">
            <div class="other-col">
              <small>Débito / Tarjetas (POS):</small>
              <span>{{ formatCurrency(shiftSummary.totalSalesDebit) }}</span>
            </div>
            <div class="other-col">
              <small>Transferencias:</small>
              <span>{{ formatCurrency(shiftSummary.totalSalesTransfer) }}</span>
            </div>
            <div class="other-col">
              <small>Total Facturado:</small>
              <strong>{{ formatCurrency(shiftSummary.totalSales) }}</strong>
            </div>
          </div>

          <!-- Input Conteo Real -->
          <div class="cash-count-section">
            <label class="input-label-large">
              <span>Dinero en Efectivo Real Contado en Gaveta ($)</span>
              <div class="amount-input-box">
                <span class="currency-symbol">$</span>
                <input
                  v-model.number="countedCashInput"
                  type="number"
                  min="0"
                  required
                  placeholder="Ej: 145000"
                  class="input-counted-amount"
                />
              </div>
            </label>

            <!-- Resultado de Cuadratura en Vivo -->
            <div
              v-if="countedCashInput !== null && countedCashInput !== undefined"
              class="cuadratura-alert"
              :class="liveDiffClass"
            >
              <div class="alert-icon">
                <CheckCircle2 v-if="liveDifference === 0" :size="24" />
                <AlertTriangle v-else :size="24" />
              </div>
              <div class="alert-content">
                <strong>{{ liveDiffTitle }}</strong>
                <p>{{ liveDiffMessage }}</p>
              </div>
            </div>
          </div>

          <!-- Observaciones / Notas -->
          <label class="modal-label">
            Observaciones / Notas del Arqueo (Opcional)
            <textarea
              v-model="closeNotesInput"
              rows="2"
              placeholder="Ej: Cuadratura perfecta. Se entrega turno sin incidencias..."
              class="modal-textarea"
            ></textarea>
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isCloseModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-confirm-close">
              <Lock :size="16" />
              <span>Confirmar Cierre de Turno</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= MODAL: APERTURA DE NUEVO TURNO ================= -->
    <div v-if="isOpenModalOpen" class="modal-backdrop" @click.self="isOpenModalOpen = false">
      <div class="modal-card animate-scale-up">
        <div class="modal-header modal-header-green">
          <div class="header-icon-title">
            <Unlock :size="22" />
            <div>
              <h3>Apertura de Nuevo Turno de Caja</h3>
              <p>Ingresa el monto del fondo base con el que iniciarás el turno.</p>
            </div>
          </div>
          <button class="close-btn" @click="isOpenModalOpen = false"><X :size="20" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitOpenShift">
          <label class="modal-label">
            Nombre del Cajero / Responsable
            <input
              v-model="openCashierName"
              type="text"
              required
              placeholder="Ej: Juan Pérez / Admin"
              class="modal-input"
            />
          </label>

          <label class="modal-label">
            Fondo Inicial en Efectivo para Vuelto ($)
            <div class="amount-input-box">
              <span class="currency-symbol">$</span>
              <input
                v-model.number="openInitialCash"
                type="number"
                min="0"
                required
                placeholder="50000"
                class="modal-input"
              />
            </div>
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isOpenModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save-green">
              <Unlock :size="16" />
              <span>Abrir Turno de Caja</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ================= MODAL: REGISTRO DE MOVIMIENTO RÁPIDO ================= -->
    <div v-if="isQuickModalOpen" class="modal-backdrop" @click.self="isQuickModalOpen = false">
      <div class="modal-card animate-scale-up">
        <div class="modal-header" :class="quickForm.type === 'egreso' ? 'modal-header-red' : 'modal-header-blue'">
          <div class="header-icon-title">
            <MinusCircle v-if="quickForm.type === 'egreso'" :size="22" />
            <PlusCircle v-else :size="22" />
            <div>
              <h3>{{ quickForm.type === 'egreso' ? 'Registrar Gasto / Egreso de Caja' : 'Registrar Ingreso Extra' }}</h3>
              <p>{{ quickForm.type === 'egreso' ? 'Registra compras de pan, verduras, gas o retiros.' : 'Registra aportes o ingresos no provenientes de pedidos.' }}</p>
            </div>
          </div>
          <button class="close-btn" @click="isQuickModalOpen = false"><X :size="20" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitQuickMovement">
          <div class="grid-2-cols">
            <label class="modal-label">
              Categoría
              <select v-model="quickForm.category" required class="modal-input">
                <option v-if="quickForm.type === 'egreso'" value="Insumos Cocina">Insumos Cocina (Pan, Verduras)</option>
                <option v-if="quickForm.type === 'egreso'" value="Bebidas & Stock">Bebidas & Stock</option>
                <option v-if="quickForm.type === 'egreso'" value="Gas / Combustible">Gas / Combustible</option>
                <option v-if="quickForm.type === 'egreso'" value="Retiro de Efectivo">Retiro de Efectivo</option>
                <option v-if="quickForm.type === 'egreso'" value="Otros Gastos">Otros Gastos</option>
                <option v-if="quickForm.type === 'ingreso'" value="Aporte de Cambio">Aporte de Cambio / Vuelto</option>
                <option v-if="quickForm.type === 'ingreso'" value="Ingreso Extra">Ingreso Extra</option>
              </select>
            </label>

            <label class="modal-label">
              Medio de Pago
              <select v-model="quickForm.paymentMethod" required class="modal-input">
                <option value="Efectivo">Efectivo (Gaveta)</option>
                <option value="Débito / Tarjeta">Débito / Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </label>
          </div>

          <label class="modal-label">
            Monto ($)
            <div class="amount-input-box">
              <span class="currency-symbol">$</span>
              <input
                v-model.number="quickForm.amount"
                type="number"
                min="1"
                required
                placeholder="Ej: 15000"
                class="modal-input"
              />
            </div>
          </label>

          <label class="modal-label">
            Descripción / Motivo
            <input
              v-model="quickForm.description"
              type="text"
              required
              placeholder="Ej: Compra de 5 bolsas de pan marraqueta"
              class="modal-input"
            />
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isQuickModalOpen = false">Cancelar</button>
            <button
              type="submit"
              :class="quickForm.type === 'egreso' ? 'btn-save-red' : 'btn-save-blue'"
            >
              {{ quickForm.type === 'egreso' ? 'Registrar Gasto' : 'Registrar Ingreso' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import {
  RefreshCw, Lock, Unlock, Coins, TrendingUp, TrendingDown,
  Wallet, CreditCard, Send, ReceiptText, History, MinusCircle,
  PlusCircle, Search, CheckCircle2, AlertTriangle, X
} from 'lucide-vue-next';
import cashFlowService, {
  type CashTransaction,
  type CashRegisterSession,
  type CashShiftSummary,
  type ShiftWindow,
  getShiftStartTimestamp,
  fetchShiftWindowFromBackend
} from '@/services/cashFlowService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();

// Estados Reactivos
const isLoading = ref(false);
const currentTab = ref<'current-shift' | 'history'>('current-shift');

const session = ref<CashRegisterSession>(cashFlowService.getCurrentSession());
const closedSessions = ref<CashRegisterSession[]>([]);
const allTransactions = ref<CashTransaction[]>([]);
const shiftWindow = ref<ShiftWindow | null>(null);

// Filtros
const searchQuery = ref('');
const filterType = ref<'all' | 'ingreso' | 'egreso'>('all');
const filterMethod = ref('all');

// Modales
const isCloseModalOpen = ref(false);
const isOpenModalOpen = ref(false);
const isQuickModalOpen = ref(false);

// Form Arqueo
const countedCashInput = ref<number | null>(null);
const closeNotesInput = ref('');

// Form Apertura
const openCashierName = ref('Administrador');
const openInitialCash = ref(50000);

// Form Movimiento Rápido
const quickForm = ref({
  type: 'egreso' as 'ingreso' | 'egreso',
  category: 'Insumos Cocina',
  paymentMethod: 'Efectivo',
  amount: 0,
  description: ''
});

// Cargar Datos
const loadData = async () => {
  isLoading.value = true;
  try {
    const [sess, closed, trxs, sw] = await Promise.all([
      cashFlowService.fetchCurrentSessionFromBackend(),
      cashFlowService.fetchClosedSessionsFromBackend(),
      cashFlowService.getCombinedTransactions(),
      cashFlowService.fetchShiftWindowFromBackend()
    ]);
    session.value = sess;
    closedSessions.value = closed;
    allTransactions.value = trxs;
    shiftWindow.value = sw;
  } catch (err) {
    console.error('Error cargando transacciones de caja:', err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(async () => {
  await loadData();
  window.addEventListener('foodtruck-cash-session-update', loadData);
  window.addEventListener('foodtruck-cash-transaction-update', loadData);
});

onUnmounted(() => {
  window.removeEventListener('foodtruck-cash-session-update', loadData);
  window.removeEventListener('foodtruck-cash-transaction-update', loadData);
});

// Movimientos del Turno Actual
const currentShiftTransactions = computed(() => {
  if (!session.value.isOpen) {
    return [];
  }
  const shiftStart = shiftWindow.value?.start_timestamp || getShiftStartTimestamp();
  const openedTime = session.value.openedTimestamp || shiftStart;
  const filterThreshold = Math.min(shiftStart, openedTime);

  return allTransactions.value.filter(t => {
    const tTime = t.createdAt ? new Date(t.createdAt).getTime() : 0;
    return tTime >= filterThreshold;
  });
});

const shiftSalesCount = computed(() => {
  return currentShiftTransactions.value.filter(t => t.type === 'ingreso' && t.status === 'completado' && t.category.includes('Ventas')).length;
});

// Resumen / KPIs del Turno
const shiftSummary = computed<CashShiftSummary>(() => {
  let salesCash = 0;
  let salesDebit = 0;
  let salesTransfer = 0;
  let manualExpenses = 0;

  currentShiftTransactions.value.forEach(t => {
    const amt = Number(t.amount || 0);
    const method = String(t.paymentMethod || '').toLowerCase();

    // Solo contabilizar ingresos pagados / completados
    if (t.type === 'ingreso' && t.status === 'completado') {
      if (method.includes('deb') || method.includes('tarj') || method.includes('pos') || method.includes('cred')) {
        salesDebit += amt;
      } else if (method.includes('transf')) {
        salesTransfer += amt;
      } else {
        salesCash += amt;
      }
    } else if (t.type === 'egreso') {
      if (method.includes('efect')) {
        manualExpenses += amt;
      }
    }
  });

  const totalSales = salesCash + salesDebit + salesTransfer;
  const initial = Number(session.value.initialCash || 0);
  const expectedCashInDrawer = initial + salesCash - manualExpenses;

  return {
    totalSalesCash: salesCash,
    totalSalesDebit: salesDebit,
    totalSalesTransfer: salesTransfer,
    totalSales,
    totalExpenses: manualExpenses,
    expectedCashInDrawer,
    actualCashCounted: 0,
    difference: 0
  };
});

// Transacciones filtradas para la tabla
const filteredCurrentTransactions = computed(() => {
  return currentShiftTransactions.value.filter(t => {
    if (filterType.value !== 'all' && t.type !== filterType.value) return false;
    if (filterMethod.value !== 'all' && t.paymentMethod !== filterMethod.value) return false;
    if (searchQuery.value.trim()) {
      const q = searchQuery.value.toLowerCase();
      const matchDesc = t.description.toLowerCase().includes(q);
      const matchCat = t.category.toLowerCase().includes(q);
      const matchMethod = t.paymentMethod.toLowerCase().includes(q);
      if (!matchDesc && !matchCat && !matchMethod) return false;
    }
    return true;
  });
});

// Cálculo en vivo para el modal de Arqueo
const liveDifference = computed(() => {
  if (countedCashInput.value === null || countedCashInput.value === undefined) return 0;
  return Number(countedCashInput.value) - shiftSummary.value.expectedCashInDrawer;
});

const liveDiffClass = computed(() => {
  if (liveDifference.value === 0) return 'diff-ok';
  if (liveDifference.value > 0) return 'diff-surplus';
  return 'diff-deficit';
});

const liveDiffTitle = computed(() => {
  if (liveDifference.value === 0) return '✓ CAJA CUADRADA PERFECTAMENTE';
  if (liveDifference.value > 0) return `SOBRANTE EN CAJA: +${formatCurrency(liveDifference.value)}`;
  return `FALTANTE EN CAJA: -${formatCurrency(Math.abs(liveDifference.value))}`;
});

const liveDiffMessage = computed(() => {
  if (liveDifference.value === 0) return 'El dinero físico en gaveta coincide exactamente con el total calculado del sistema.';
  if (liveDifference.value > 0) return 'Hay más dinero en la gaveta del registrado. Verifica propinas o cobros extras.';
  return 'Falta dinero en la gaveta respecto al registro. Verifica si hubo gastos no anotados o vueltos erróneos.';
});

// Métodos de Acciones
const openCloseShiftModal = () => {
  countedCashInput.value = shiftSummary.value.expectedCashInDrawer;
  closeNotesInput.value = '';
  isCloseModalOpen.value = true;
};

const submitCloseShift = () => {
  if (countedCashInput.value === null || countedCashInput.value === undefined) {
    notify('Debes ingresar el monto contado en gaveta', 'warning');
    return;
  }

  cashFlowService.closeSession(
    Number(countedCashInput.value),
    shiftSummary.value,
    closeNotesInput.value
  );

  notify('Turno cerrado y arqueo guardado exitosamente', 'success');
  isCloseModalOpen.value = false;
  loadData();
};

const openOpenShiftModal = () => {
  openCashierName.value = 'Administrador';
  openInitialCash.value = 50000;
  isOpenModalOpen.value = true;
};

const submitOpenShift = () => {
  cashFlowService.openSession(openInitialCash.value, openCashierName.value);
  notify('Nuevo turno de caja iniciado con éxito', 'success');
  isOpenModalOpen.value = false;
  loadData();
};

const openQuickExpenseModal = () => {
  if (!session.value.isOpen) {
    notify('Debes abrir un turno de caja antes de registrar gastos.', 'warning');
    return;
  }
  quickForm.value = {
    type: 'egreso',
    category: 'Insumos Cocina',
    paymentMethod: 'Efectivo',
    amount: 0,
    description: ''
  };
  isQuickModalOpen.value = true;
};

const openQuickIncomeModal = () => {
  if (!session.value.isOpen) {
    notify('Debes abrir un turno de caja antes de registrar ingresos extra.', 'warning');
    return;
  }
  quickForm.value = {
    type: 'ingreso',
    category: 'Aporte de Cambio',
    paymentMethod: 'Efectivo',
    amount: 0,
    description: ''
  };
  isQuickModalOpen.value = true;
};

const submitQuickMovement = () => {
  if (quickForm.value.amount <= 0 || !quickForm.value.description.trim()) {
    notify('Por favor completa el monto y la descripción', 'warning');
    return;
  }

  cashFlowService.saveCustomTransaction({
    type: quickForm.value.type,
    category: quickForm.value.category,
    amount: Number(quickForm.value.amount),
    paymentMethod: quickForm.value.paymentMethod,
    description: quickForm.value.description,
    status: 'completado'
  });

  notify(
    `${quickForm.value.type === 'egreso' ? 'Gasto' : 'Ingreso'} de ${formatCurrency(quickForm.value.amount)} registrado correctamente`,
    'success'
  );
  isQuickModalOpen.value = false;
  loadData();
};

// Formato y estilos auxiliares
const formatCurrency = (val: number = 0) => {
  return `$${Number(val || 0).toLocaleString('es-CL')}`;
};

const getMethodClass = (method: string) => {
  const m = method.toLowerCase();
  if (m.includes('efect')) return 'tag-cash';
  if (m.includes('deb') || m.includes('tarj') || m.includes('pos')) return 'tag-debit';
  if (m.includes('transf')) return 'tag-transfer';
  return 'tag-default';
};

const getDiffClass = (diff: number) => {
  if (diff === 0) return 'diff-badge-ok';
  if (diff > 0) return 'diff-badge-surplus';
  return 'diff-badge-deficit';
};

const formatDiff = (diff: number) => {
  if (diff === 0) return '✓ Cuadrada';
  if (diff > 0) return `+${formatCurrency(diff)} (Sobrante)`;
  return `-${formatCurrency(Math.abs(diff))} (Faltante)`;
};
</script>

<style scoped>
.cashflow-view {
  padding: 24px 30px;
  max-width: 1450px;
  margin: 0 auto;
  font-family: var(--font-main, sans-serif);
  color: #1e293b;
}

/* Cabecera */
.cash-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 14px;
}

.eyebrow {
  font-size: 0.75rem;
  font-weight: 800;
  text-transform: uppercase;
  color: #e28743;
  letter-spacing: 0.5px;
}

.cash-header h1 {
  font-size: 1.8rem;
  font-weight: 900;
  color: #513119;
  margin: 2px 0 6px 0;
}

.cash-header p {
  color: #64748b;
  margin: 0;
  font-size: 0.92rem;
}

.btn-refresh {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 18px;
  border-radius: 12px;
  border: 1px solid #cbd5e1;
  background: white;
  color: #513119;
  font-weight: 800;
  font-size: 0.88rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-refresh:hover {
  background: #f8fafc;
  border-color: #513119;
}

.spinning {
  animation: spin 1s linear infinite;
}

/* Banner de Estado del Turno */
.shift-banner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  border-radius: 16px;
  margin-bottom: 24px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
  flex-wrap: wrap;
  gap: 16px;
}

.banner-open {
  background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
  border: 1.5px solid #86efac;
}

.banner-closed {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1.5px solid #cbd5e1;
}

.shift-status-info {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.status-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
}

.pulse-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #22c55e;
  box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
  animation: pulse 1.6s infinite;
}

@keyframes pulse {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}

.status-badge {
  font-size: 0.8rem;
  font-weight: 900;
  letter-spacing: 0.5px;
  padding: 4px 12px;
  border-radius: 999px;
}

.badge-open {
  background: #166534;
  color: white;
}

.badge-closed {
  background: #475569;
  color: white;
}

.shift-meta-details {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  font-size: 0.88rem;
  color: #334155;
}

.btn-shift-close {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #dc2626;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
}

.btn-shift-close:hover {
  background: #b91c1c;
  transform: translateY(-1px);
}

.btn-shift-open {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: #16a34a;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
}

.btn-shift-open:hover {
  background: #15803d;
  transform: translateY(-1px);
}

/* Grid KPIs */
.kpis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
  gap: 16px;
  margin-bottom: 26px;
}

.kpi-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 16px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 14px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
}

.kpi-card.highlight-card {
  border: 1.5px solid #e28743;
  background: #fffcf9;
}

.kpi-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.icon-amber { background: #fef3c7; color: #b45309; }
.icon-green { background: #dcfce7; color: #15803d; }
.icon-orange { background: #ffedd5; color: #c2410c; }
.icon-blue { background: #dbeafe; color: #1d4ed8; }
.icon-purple { background: #f3e8ff; color: #7e22ce; }
.icon-red { background: #fee2e2; color: #b91c1c; }

.kpi-info {
  display: flex;
  flex-direction: column;
}

.kpi-label {
  font-size: 0.78rem;
  font-weight: 700;
  color: #64748b;
  text-transform: uppercase;
}

.kpi-val {
  font-size: 1.25rem;
  font-weight: 900;
  color: #0f172a;
}

.val-green { color: #16a34a; }
.val-orange { color: #ea580c; }
.val-red { color: #dc2626; }

.kpi-hint {
  font-size: 0.72rem;
  color: #94a3b8;
  font-weight: 600;
}

/* Pestañas y Layout */
.main-content-layout {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  padding: 24px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
}

.tabs-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 18px;
  margin-bottom: 20px;
}

.view-tabs {
  display: flex;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 12px;
  gap: 4px;
}

.tab-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 18px;
  border: none;
  background: transparent;
  color: #64748b;
  font-weight: 800;
  font-size: 0.85rem;
  border-radius: 9px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab-btn.active {
  background: #513119;
  color: white;
  box-shadow: 0 2px 8px rgba(81, 49, 25, 0.2);
}

.quick-action-buttons {
  display: flex;
  gap: 10px;
}

.btn-quick-expense {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #fff1f2;
  color: #e11d48;
  border: 1.5px solid #fecdd3;
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.84rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-quick-expense:hover {
  background: #ffe4e6;
}

.btn-quick-income {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: #f0fdf4;
  color: #16a34a;
  border: 1.5px solid #bbf7d0;
  padding: 8px 16px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.84rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-quick-income:hover {
  background: #dcfce7;
}

/* Barra de Filtros */
.filter-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
}

.search-wrap {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-input {
  width: 100%;
  padding: 9px 12px 9px 36px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
  font-size: 0.88rem;
  box-sizing: border-box;
}

.filter-selects {
  display: flex;
  gap: 10px;
}

.filter-select {
  padding: 9px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

/* Tabla de Movimientos */
.table-responsive {
  overflow-x: auto;
}

.cash-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}

.cash-table th {
  background: #f8fafc;
  padding: 12px 14px;
  text-align: left;
  font-weight: 800;
  color: #475569;
  border-bottom: 2px solid #e2e8f0;
  font-size: 0.78rem;
  text-transform: uppercase;
}

.cash-table td {
  padding: 12px 14px;
  border-bottom: 1px solid #f1f5f9;
}

.row-income { background: #ffffff; }
.row-expense { background: #fffcfc; }

.col-date { font-size: 0.8rem; color: #64748b; white-space: nowrap; }
.col-desc { color: #1e293b; }
.col-category { font-size: 0.82rem; color: #475569; }
.col-amount { font-size: 0.95rem; white-space: nowrap; }
.text-right { text-align: right; }
.text-green { color: #16a34a; }
.text-red { color: #dc2626; }

.type-pill {
  font-size: 0.72rem;
  font-weight: 900;
  padding: 3px 8px;
  border-radius: 999px;
  text-transform: uppercase;
}

.pill-income { background: #dcfce7; color: #15803d; }
.pill-expense { background: #fee2e2; color: #b91c1c; }

.payment-method-tag {
  font-size: 0.75rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 6px;
}

.tag-cash { background: #ffedd5; color: #c2410c; }
.tag-debit { background: #dbeafe; color: #1d4ed8; }
.tag-transfer { background: #f3e8ff; color: #7e22ce; }
.tag-default { background: #f1f5f9; color: #475569; }

.empty-table-cell {
  text-align: center;
  padding: 40px !important;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  color: #94a3b8;
}

/* Historial */
.history-table th, .history-table td {
  white-space: nowrap;
}

.diff-badge {
  font-size: 0.75rem;
  font-weight: 900;
  padding: 4px 10px;
  border-radius: 999px;
}

.diff-badge-ok { background: #dcfce7; color: #166534; }
.diff-badge-surplus { background: #dbeafe; color: #1e40af; }
.diff-badge-deficit { background: #fee2e2; color: #991b1b; }

.col-notes {
  color: #64748b;
  font-size: 0.8rem;
  max-width: 180px;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Modales */
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; width: 100vw; height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(3px);
  z-index: 3000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  box-sizing: border-box;
}

.modal-card {
  background: white;
  width: 100%;
  max-width: 520px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-card.modal-arqueo {
  max-width: 580px;
}

.modal-header {
  padding: 18px 24px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-header-dark { background: #513119; }
.modal-header-green { background: #15803d; }
.modal-header-red { background: #dc2626; }
.modal-header-blue { background: #2563eb; }

.header-icon-title {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon-title h3 {
  margin: 0;
  font-size: 1.15rem;
}

.header-icon-title p {
  margin: 2px 0 0 0;
  font-size: 0.78rem;
  opacity: 0.9;
}

.close-btn {
  background: transparent;
  border: none;
  color: white;
  cursor: pointer;
}

.modal-body {
  padding: 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-weight: 700;
  font-size: 0.88rem;
  color: #334155;
}

.modal-input {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
  font-size: 0.9rem;
}

.modal-textarea {
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid #cbd5e1;
  font-family: inherit;
  font-size: 0.88rem;
}

.grid-2-cols {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

/* Arqueo Específico */
.arqueo-breakdown {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.breakdown-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.88rem;
  color: #475569;
}

.total-expected-row {
  border-top: 1.5px dashed #cbd5e1;
  padding-top: 8px;
  margin-top: 4px;
  font-size: 1rem;
  color: #0f172a;
}

.expected-amount {
  font-size: 1.15rem;
  color: #ea580c;
}

.other-payments-info {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  background: #f1f5f9;
  border-radius: 10px;
  padding: 10px;
  gap: 10px;
  text-align: center;
}

.other-col {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.other-col small {
  font-size: 0.72rem;
  color: #64748b;
  font-weight: 700;
}

.other-col span, .other-col strong {
  font-size: 0.85rem;
}

.cash-count-section {
  background: #fffcf9;
  border: 1.5px solid #fed7aa;
  border-radius: 14px;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.input-label-large {
  display: flex;
  flex-direction: column;
  gap: 6px;
  font-weight: 800;
  font-size: 0.92rem;
  color: #9a3412;
}

.amount-input-box {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 14px;
  font-weight: 800;
  color: #64748b;
}

.amount-input-box input {
  padding-left: 28px !important;
}

.input-counted-amount {
  width: 100%;
  padding: 12px 14px 12px 28px;
  border-radius: 10px;
  border: 2px solid #e28743;
  font-size: 1.2rem;
  font-weight: 900;
  color: #513119;
  box-sizing: border-box;
}

.cuadratura-alert {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 10px;
}

.alert-content strong {
  font-size: 0.9rem;
  display: block;
  margin-bottom: 2px;
}

.alert-content p {
  margin: 0;
  font-size: 0.78rem;
}

.diff-ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.diff-surplus { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.diff-deficit { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 10px;
}

.btn-cancel {
  padding: 10px 18px;
  border: 1px solid #cbd5e1;
  background: white;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
}

.btn-confirm-close {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}

.btn-save-green {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 10px 22px;
  background: #16a34a;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}

.btn-save-red {
  padding: 10px 22px;
  background: #dc2626;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}

.btn-save-blue {
  padding: 10px 22px;
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 800;
  cursor: pointer;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@keyframes scaleUp {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}

.animate-scale-up {
  animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-fade-in {
  animation: fadeIn 0.25s ease forwards;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@media (max-width: 768px) {
  .cashflow-view {
    padding: 15px 12px;
  }

  .cash-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .header-actions {
    width: 100%;
  }

  .btn-refresh {
    width: 100%;
    justify-content: center;
  }

  .shift-banner {
    flex-direction: column;
    align-items: stretch;
    padding: 16px;
    gap: 14px;
  }

  .shift-banner-actions button {
    width: 100%;
    justify-content: center;
  }

  .kpis-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .tabs-header-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .view-tabs {
    flex-direction: column;
    width: 100%;
  }

  .tab-btn {
    width: 100%;
    justify-content: center;
  }

  .quick-action-buttons {
    flex-direction: column;
    width: 100%;
  }

  .btn-quick-expense,
  .btn-quick-withdraw {
    width: 100%;
    justify-content: center;
  }

  .grid-2-cols {
    grid-template-columns: 1fr;
  }

  .other-payments-info {
    grid-template-columns: 1fr;
  }
}
</style>