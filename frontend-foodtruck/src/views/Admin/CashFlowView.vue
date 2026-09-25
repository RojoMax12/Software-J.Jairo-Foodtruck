<template>
  <div class="cashflow-view">
    <!-- ===================== HEADER ===================== -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Caja & Arqueo de Turnos</h1>
        <p>Control de apertura y cierre de turnos, arqueo de dinero en gaveta, ventas y egresos operativos.</p>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="loadData" :disabled="isLoading" title="Actualizar datos">
          <RefreshCw :size="16" :class="{ spinning: isLoading }" />
          <span>{{ isLoading ? 'Cargando...' : 'Actualizar' }}</span>
        </button>

        <button v-if="session.isOpen" class="btn-danger-action" @click="openCloseShiftModal">
          <Lock :size="16" />
          <span>Cerrar Turno & Arqueo</span>
        </button>
        <button v-else class="btn-primary" @click="openOpenShiftModal">
          <Unlock :size="16" />
          <span>Abrir Turno de Caja</span>
        </button>
      </div>
    </header>

    <!-- ===================== ESTADO DEL TURNO (BANNER) ===================== -->
    <section class="shift-status-card" :class="session.isOpen ? 'shift-active' : 'shift-inactive'">
      <div class="shift-status-main">
        <div class="shift-indicator-row">
          <span class="status-pill" :class="session.isOpen ? 'pill-open' : 'pill-closed'">
            <span class="dot-pulse" v-if="session.isOpen"></span>
            {{ session.isOpen ? 'Turno en Curso' : 'Caja Cerrada' }}
          </span>
          <span v-if="session.isOpen" class="shift-time-hint">Abierto a las {{ session.openedAt }}</span>
        </div>

        <div class="shift-data-chips" v-if="session.isOpen">
          <div class="meta-chip">
            <span class="chip-lbl">Responsable:</span>
            <strong>{{ session.cashierName }}</strong>
          </div>
          <div class="meta-chip">
            <span class="chip-lbl">Fondo Base (Vuelto):</span>
            <strong>{{ formatCurrency(session.initialCash) }}</strong>
          </div>
          <div class="meta-chip highlight">
            <span class="chip-lbl">Efectivo en Gaveta:</span>
            <strong>{{ formatCurrency(shiftSummary.expectedCashInDrawer) }}</strong>
          </div>
        </div>
        <div class="shift-data-chips" v-else>
          <span class="closed-desc-text">Inicia un nuevo turno para registrar el fondo de vuelto y habilitar cobros en efectivo.</span>
        </div>
      </div>
    </section>

    <!-- ===================== RESUMEN EN KPIS (SUMMARY-GRID) ===================== -->
    <section class="summary-grid">
      <article class="summary-card">
        <div class="summary-icon-box bg-summary-brown">
          <Coins :size="22" />
        </div>
        <div>
          <span class="summary-label">Fondo Inicial</span>
          <strong class="summary-value">{{ formatCurrency(session.initialCash) }}</strong>
          <p class="summary-helper">Base inicial para vuelto</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-green">
          <TrendingUp :size="22" />
        </div>
        <div>
          <span class="summary-label">Ventas Totales</span>
          <strong class="summary-value text-status-open">{{ formatCurrency(shiftSummary.totalSales) }}</strong>
          <p class="summary-helper">{{ shiftSalesCount }} pedidos cobrados</p>
        </div>
      </article>

      <article class="summary-card highlight-metric">
        <div class="summary-icon-box bg-summary-orange">
          <Wallet :size="22" />
        </div>
        <div>
          <span class="summary-label">Efectivo en Gaveta</span>
          <strong class="summary-value text-orange">{{ formatCurrency(shiftSummary.expectedCashInDrawer) }}</strong>
          <p class="summary-helper">Fondo + Ventas Efectivo - Egresos</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-blue">
          <CreditCard :size="22" />
        </div>
        <div>
          <span class="summary-label">Tarjetas & POS</span>
          <strong class="summary-value">{{ formatCurrency(shiftSummary.totalSalesDebit) }}</strong>
          <p class="summary-helper">Débito / Crédito Transbank</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-purple">
          <Send :size="22" />
        </div>
        <div>
          <span class="summary-label">Transferencias</span>
          <strong class="summary-value">{{ formatCurrency(shiftSummary.totalSalesTransfer) }}</strong>
          <p class="summary-helper">Comprobantes bancarios</p>
        </div>
      </article>

      <article class="summary-card">
        <div class="summary-icon-box bg-summary-pink">
          <TrendingDown :size="22" />
        </div>
        <div>
          <span class="summary-label">Gastos / Egresos</span>
          <strong class="summary-value text-pink">{{ formatCurrency(shiftSummary.totalExpenses) }}</strong>
          <p class="summary-helper">Compras de insumos / retiros</p>
        </div>
      </article>
    </section>

    <!-- ===================== PESTAÑAS SEGMENTADAS ===================== -->
    <div class="inventory-tabs-nav">
      <button
        type="button"
        class="tab-nav-btn"
        :class="{ active: currentTab === 'current-shift' }"
        @click="currentTab = 'current-shift'"
      >
        <ReceiptText :size="17" class="tab-icon" />
        <span class="tab-text">Movimientos del Turno</span>
        <span class="tab-pill">{{ currentShiftTransactions.length }}</span>
      </button>

      <button
        type="button"
        class="tab-nav-btn"
        :class="{ active: currentTab === 'history' }"
        @click="currentTab = 'history'"
        v-role="[1]"
      >
        <History :size="17" class="tab-icon" />
        <span class="tab-text">Historial de Arqueos</span>
        <span class="tab-pill">{{ closedSessions.length }}</span>
      </button>
    </div>

    <!-- ===================== CONTENEDOR PRINCIPAL ===================== -->
    <section class="panel-card table-unified-card">
      <!-- Toolbar Movimientos -->
      <div v-if="currentTab === 'current-shift'" class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Buscar por descripción, cliente, pedido..."
            />
            <button v-if="searchQuery" class="clear-search-btn" @click="searchQuery = ''">
              <X :size="14" />
            </button>
          </div>

          <div class="select-box">
            <select v-model="filterType">
              <option value="all">Todos los tipos</option>
              <option value="ingreso">Solo Ingresos / Ventas</option>
              <option value="egreso">Solo Egresos / Gastos</option>
            </select>
          </div>

          <div class="select-box">
            <select v-model="filterMethod">
              <option value="all">Todos los medios</option>
              <option value="Efectivo">Efectivo</option>
              <option value="Débito / Tarjeta">Débito / Tarjeta</option>
              <option value="Transferencia">Transferencia</option>
            </select>
          </div>

          <button 
            v-if="searchQuery || filterType !== 'all' || filterMethod !== 'all'"
            class="btn-reset-filters" 
            type="button" 
            @click="searchQuery = ''; filterType = 'all'; filterMethod = 'all'"
          >
            <X :size="14" />
            <span>Limpiar</span>
          </button>
        </div>

        <div class="toolbar-right">
          <button v-role="[1]" class="btn-pill-action expense" @click="openQuickExpenseModal">
            <MinusCircle :size="15" />
            <span>Registrar Gasto</span>
          </button>
          <button v-role="[1]" class="btn-pill-action income" @click="openQuickIncomeModal">
            <PlusCircle :size="15" />
            <span>Ingreso Extra</span>
          </button>
        </div>
      </div>

      <!-- Toolbar Historial -->
      <div v-else class="panel-toolbar">
        <div class="toolbar-left">
          <span class="panel-section-title">
            <History :size="16" />
            <span>Registro de Arqueos y Cuadraturas Guardadas</span>
          </span>
        </div>
        <div class="toolbar-right">
          <span class="results-chip">{{ closedSessions.length }} turnos archivados</span>
        </div>
      </div>

      <!-- TAB 1: TABLA MOVIMIENTOS -->
      <div v-if="currentTab === 'current-shift'" class="table-wrapper desktop-table-only">
        <table class="cash-table">
          <thead>
            <tr>
              <th style="width: 14%;">Fecha / Hora</th>
              <th style="width: 12%;">Tipo</th>
              <th style="width: 32%;">Concepto / Descripción</th>
              <th style="width: 15%;">Medio de Pago</th>
              <th style="width: 15%;">Categoría</th>
              <th style="width: 12%; text-align: right;">Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="filteredCurrentTransactions.length === 0">
              <td colspan="6" class="text-center">
                <div class="state-card empty-state">
                  <ReceiptText :size="40" />
                  <p>No se encontraron movimientos registrados en este turno.</p>
                </div>
              </td>
            </tr>
            <tr
              v-else
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
              <td><strong>{{ trx.description }}</strong></td>
              <td>
                <span class="payment-tag" :class="getMethodClass(trx.paymentMethod)">
                  {{ trx.paymentMethod }}
                </span>
              </td>
              <td class="col-category">{{ trx.category }}</td>
              <td class="text-right col-amount" :class="trx.type === 'ingreso' ? 'text-status-open' : 'text-pink'">
                <strong>{{ trx.type === 'ingreso' ? '+' : '-' }}{{ formatCurrency(trx.amount) }}</strong>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- TAB 2: TABLA HISTORIAL -->
      <div v-else class="table-wrapper desktop-table-only">
        <table class="cash-table history-table">
          <thead>
            <tr>
              <th style="width: 8%;">ID</th>
              <th style="width: 12%;">Apertura</th>
              <th style="width: 12%;">Cierre</th>
              <th style="width: 14%;">Cajero</th>
              <th style="width: 11%;">Fondo Base</th>
              <th style="width: 11%;">Ventas</th>
              <th style="width: 11%;">Esperado</th>
              <th style="width: 11%;">Contado</th>
              <th style="width: 10%;">Cuadratura</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="closedSessions.length === 0">
              <td colspan="9" class="text-center">
                <div class="state-card empty-state">
                  <History :size="40" />
                  <p>Aún no hay turnos cerrados ni arqueos guardados.</p>
                </div>
              </td>
            </tr>
            <tr v-else v-for="h in closedSessions" :key="h.id">
              <td><code>#{{ h.id }}</code></td>
              <td>{{ h.openedAt }}</td>
              <td>{{ h.closedAt || '-' }}</td>
              <td><strong>{{ h.cashierName }}</strong></td>
              <td>{{ formatCurrency(h.initialCash) }}</td>
              <td class="text-status-open"><strong>{{ formatCurrency(h.summary?.totalSales || 0) }}</strong></td>
              <td>{{ formatCurrency(h.summary?.expectedCashInDrawer || 0) }}</td>
              <td><strong>{{ formatCurrency(h.summary?.actualCashCounted || 0) }}</strong></td>
              <td>
                <span class="diff-badge" :class="getDiffClass(h.summary?.difference || 0)">
                  {{ formatDiff(h.summary?.difference || 0) }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===================== MODAL: CIERRE Y ARQUEO ===================== -->
    <div v-if="isCloseModalOpen" class="modal-backdrop" @click.self="isCloseModalOpen = false">
      <div class="modal-card modal-arqueo">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill bg-danger"><Lock :size="18" /></div>
            <div>
              <h3>Arqueo de Caja & Cierre de Turno</h3>
              <p class="modal-header-desc">Cuadratura física de gaveta vs registro del sistema</p>
            </div>
          </div>
          <button class="close-btn" @click="isCloseModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitCloseShift">
          <!-- Desglose de Caja -->
          <div class="arqueo-breakdown-card">
            <div class="breakdown-line">
              <span>(+) Fondo Inicial de Vuelto:</span>
              <strong>{{ formatCurrency(session.initialCash) }}</strong>
            </div>
            <div class="breakdown-line">
              <span>(+) Ventas en Efectivo:</span>
              <strong class="text-status-open">+{{ formatCurrency(shiftSummary.totalSalesCash) }}</strong>
            </div>
            <div class="breakdown-line">
              <span>(-) Egresos / Gastos de Caja:</span>
              <strong class="text-pink">-{{ formatCurrency(shiftSummary.totalExpenses) }}</strong>
            </div>
            <div class="breakdown-line total-line">
              <span>(=) Efectivo Esperado en Gaveta:</span>
              <strong class="total-expected-val">{{ formatCurrency(shiftSummary.expectedCashInDrawer) }}</strong>
            </div>
          </div>

          <!-- Otros Pagos Informativos -->
          <div class="aux-payments-grid">
            <div class="aux-col">
              <small>POS / Tarjetas:</small>
              <span>{{ formatCurrency(shiftSummary.totalSalesDebit) }}</span>
            </div>
            <div class="aux-col">
              <small>Transferencias:</small>
              <span>{{ formatCurrency(shiftSummary.totalSalesTransfer) }}</span>
            </div>
            <div class="aux-col">
              <small>Venta Total:</small>
              <strong>{{ formatCurrency(shiftSummary.totalSales) }}</strong>
            </div>
          </div>

          <!-- Input Conteo Real -->
          <div class="cash-count-group">
            <label class="modal-label">
              <span>Efectivo Real Contado en Gaveta ($) <span class="required">*</span></span>
              <div class="price-input-wrapper">
                <span class="currency-symbol">$</span>
                <input
                  v-model.number="countedCashInput"
                  type="number"
                  min="0"
                  required
                  placeholder="Ej: 145000"
                  class="modal-input price-input-lg"
                />
              </div>
            </label>

            <!-- Alerta Cuadratura -->
            <div
              v-if="countedCashInput !== null && countedCashInput !== undefined"
              class="cuadratura-status-box"
              :class="liveDiffClass"
            >
              <div class="status-box-content">
                <strong>{{ liveDiffTitle }}</strong>
                <p>{{ liveDiffMessage }}</p>
              </div>
            </div>
          </div>

          <label class="modal-label">
            <span>Observaciones del Turno (Opcional)</span>
            <textarea
              v-model="closeNotesInput"
              rows="2"
              placeholder="Notas sobre faltantes, propinas o detalles del arqueo..."
              class="modal-input"
            ></textarea>
          </label>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isCloseModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-danger-submit">
              <Lock :size="16" />
              <span>Confirmar Cierre de Turno</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===================== MODAL: APERTURA DE TURNO ===================== -->
    <div v-if="isOpenModalOpen" class="modal-backdrop" @click.self="isOpenModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><Unlock :size="18" /></div>
            <div>
              <h3>Apertura de Nuevo Turno</h3>
              <p class="modal-header-desc">Ingresa el monto del fondo base de gaveta</p>
            </div>
          </div>
          <button class="close-btn" @click="isOpenModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitOpenShift">
          <label class="modal-label">
            <span>Nombre del Cajero / Responsable <span class="required">*</span></span>
            <input
              v-model="openCashierName"
              type="text"
              required
              placeholder="Ej: Juan Pérez / Administrador"
              class="modal-input"
            />
          </label>

          <label class="modal-label">
            <span>Fondo Inicial de Vuelto ($) <span class="required">*</span></span>
            <div class="price-input-wrapper">
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
            <button type="submit" class="btn-save">
              <Unlock :size="16" />
              <span>Iniciar Turno de Caja</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ===================== MODAL: GASTO / INGRESO RÁPIDO ===================== -->
    <div v-if="isQuickModalOpen" class="modal-backdrop" @click.self="isQuickModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill" :class="quickForm.type === 'egreso' ? 'bg-danger' : 'bg-success'">
              <MinusCircle v-if="quickForm.type === 'egreso'" :size="18" />
              <PlusCircle v-else :size="18" />
            </div>
            <div>
              <h3>{{ quickForm.type === 'egreso' ? 'Registrar Gasto de Caja' : 'Registrar Ingreso Extra' }}</h3>
              <p class="modal-header-desc">
                {{ quickForm.type === 'egreso' ? 'Compras de pan, insumos, gas o retiros directos' : 'Aportes de sencillo o entradas extras' }}
              </p>
            </div>
          </div>
          <button class="close-btn" @click="isQuickModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitQuickMovement">
          <div class="modal-row">
            <label class="modal-label">
              <span>Categoría <span class="required">*</span></span>
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
              <span>Medio de Pago <span class="required">*</span></span>
              <select v-model="quickForm.paymentMethod" required class="modal-input">
                <option value="Efectivo">Efectivo (Gaveta)</option>
                <option value="Débito / Tarjeta">Débito / Tarjeta</option>
                <option value="Transferencia">Transferencia</option>
              </select>
            </label>
          </div>

          <label class="modal-label">
            <span>Monto ($) <span class="required">*</span></span>
            <div class="price-input-wrapper">
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
            <span>Descripción / Motivo <span class="required">*</span></span>
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
              class="btn-save"
              :class="quickForm.type === 'egreso' ? 'btn-danger-submit' : ''"
            >
              <span>{{ quickForm.type === 'egreso' ? 'Registrar Gasto' : 'Registrar Ingreso' }}</span>
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
  PlusCircle, Search, X
} from 'lucide-vue-next';
import cashFlowService, {
  type CashTransaction,
  type CashRegisterSession,
  type CashShiftSummary,
  type ShiftWindow,
  getShiftStartTimestamp
} from '@/services/cashFlowService';
import { useNotification } from '@/composables/useNotification';

const { notify } = useNotification();

const isLoading = ref(false);
const currentTab = ref<'current-shift' | 'history'>('current-shift');

const session = ref<CashRegisterSession>(cashFlowService.getCurrentSession());
const closedSessions = ref<CashRegisterSession[]>([]);
const allTransactions = ref<CashTransaction[]>([]);
const shiftWindow = ref<ShiftWindow | null>(null);

const searchQuery = ref('');
const filterType = ref<'all' | 'ingreso' | 'egreso'>('all');
const filterMethod = ref('all');

const isCloseModalOpen = ref(false);
const isOpenModalOpen = ref(false);
const isQuickModalOpen = ref(false);

const countedCashInput = ref<number | null>(null);
const closeNotesInput = ref('');

const openCashierName = ref('Administrador');
const openInitialCash = ref(50000);

const quickForm = ref({
  type: 'egreso' as 'ingreso' | 'egreso',
  category: 'Insumos Cocina',
  paymentMethod: 'Efectivo',
  amount: 0,
  description: ''
});

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

const currentShiftTransactions = computed(() => {
  if (!session.value.isOpen) return [];
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

const shiftSummary = computed<CashShiftSummary>(() => {
  let salesCash = 0;
  let salesDebit = 0;
  let salesTransfer = 0;
  let manualExpenses = 0;

  currentShiftTransactions.value.forEach(t => {
    const amt = Number(t.amount || 0);
    const method = String(t.paymentMethod || '').toLowerCase();

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
  if (liveDifference.value === 0) return '✓ CAJA CUADRADA EXACTAMENTE';
  if (liveDifference.value > 0) return `SOBRANTE EN CAJA: +${formatCurrency(liveDifference.value)}`;
  return `FALTANTE EN CAJA: -${formatCurrency(Math.abs(liveDifference.value))}`;
});

const liveDiffMessage = computed(() => {
  if (liveDifference.value === 0) return 'El dinero físico en gaveta coincide con el cálculo del sistema.';
  if (liveDifference.value > 0) return 'Hay más dinero físico en gaveta del registrado. Verifica propinas o cobros extras.';
  return 'Falta dinero en gaveta. Verifica gastos no anotados o vueltos entregados erróneamente.';
});

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
    `${quickForm.value.type === 'egreso' ? 'Gasto' : 'Ingreso'} de ${formatCurrency(quickForm.value.amount)} registrado`,
    'success'
  );
  isQuickModalOpen.value = false;
  loadData();
};

const formatCurrency = (val: number = 0) => `$${Number(val || 0).toLocaleString('es-CL')}`;

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
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER Y ACCIONES PRINCIPALES
==================================================== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-copy h1 {
  color: var(--DC-brown, #513119);
  font-size: 2.2rem;
  line-height: 1.1;
  margin: 0 0 0.4rem 0;
}

.header-copy p {
  margin: 0;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.92rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  padding: 0.65rem 1.1rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 700;
  font-size: 0.88rem;
}

.btn-secondary:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.15);
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.btn-primary:hover:not(:disabled) {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.2);
}

.btn-danger-action {
  border: none;
  background: var(--DC-pink, #d80056);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.btn-danger-action:hover {
  background: #b00046;
  transform: translateY(-1px);
}

/* ====================================================
   ESTADO DEL TURNO (CARD DE ESTADO)
==================================================== */
.shift-status-card {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 16px;
  padding: 1.15rem 1.4rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
}

.shift-status-card.shift-active {
  border-left: 5px solid #16a34a;
}

.shift-status-card.shift-inactive {
  border-left: 5px solid #cbd5e1;
}

.shift-status-main {
  display: flex;
  flex-direction: column;
  gap: 0.65rem;
}

.shift-indicator-row {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.74rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 999px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.pill-open { background: #dcfce7; color: #15803d; }
.pill-closed { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.dot-pulse {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #16a34a;
  box-shadow: 0 0 0 rgba(22, 163, 74, 0.7);
  animation: pulse-dot 1.8s infinite;
}

@keyframes pulse-dot {
  0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
  70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(22, 163, 74, 0); }
  100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(22, 163, 74, 0); }
}

.shift-time-hint {
  font-size: 0.8rem;
  color: var(--DC-text-gray, #7c7468);
}

.shift-data-chips {
  display: flex;
  align-items: center;
  gap: 0.85rem;
  flex-wrap: wrap;
}

.meta-chip {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.82rem;
}

.meta-chip.highlight {
  background: #fff4e6;
  border: 1px solid #fed7aa;
}

.meta-chip.highlight strong {
  color: var(--DC-orange, #e28743);
}

.chip-lbl { color: var(--DC-text-gray, #7c7468); }
.closed-desc-text { font-size: 0.84rem; color: var(--DC-text-gray, #7c7468); }

/* ====================================================
   KPIS SUMMARY-GRID (6 COLUMNAS)
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
}

.summary-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 4px 16px rgba(26, 14, 5, 0.03);
  border: 1px solid rgba(81, 49, 25, 0.08);
  padding: 1rem 1.15rem;
  display: flex;
  align-items: center;
  gap: 0.85rem;
}

.summary-card.highlight-metric {
  border-color: var(--DC-orange, #e28743);
  background: #fffdfa;
}

.summary-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }
.bg-summary-blue { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.bg-summary-purple { background: rgba(147, 51, 234, 0.12); color: #9333ea; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.74rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #2c2724);
  font-size: 1.35rem;
  line-height: 1.15;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.76rem;
  margin: 0;
}

.text-status-open { color: #15803d; }
.text-orange { color: var(--DC-orange, #e28743); }
.text-pink { color: var(--DC-pink, #d80056); }

/* ====================================================
   PESTAÑAS SEGMENTADAS
==================================================== */
.inventory-tabs-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #ede6dc;
  border-radius: 14px;
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
  white-space: nowrap;
}

.tab-nav-btn:hover:not(.active) {
  color: var(--DC-brown, #513119);
  background: rgba(255, 255, 255, 0.4);
}

.tab-nav-btn.active {
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.08);
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
  background: var(--DC-orange, #e28743);
  color: white;
}

/* ====================================================
   PANEL UNIFICADO & TOOLBAR
==================================================== */
.table-unified-card {
  background: white;
  border-radius: 18px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  overflow: hidden;
  display: flex;
  flex-direction: column;
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

.panel-section-title {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.86rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.search-box {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 12px;
  padding: 0.55rem 0.75rem;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  min-width: 220px;
}

.search-icon {
  color: var(--DC-text-gray, #7c7468);
  flex-shrink: 0;
}

.clear-search-btn {
  background: transparent;
  border: none;
  color: #999;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
}

.select-box select {
  padding: 0.55rem 0.85rem;
  border-radius: 12px;
  border: 1px solid rgba(81, 49, 25, 0.09);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.86rem;
  font-weight: 700;
  color: var(--DC-gray, #2c2724);
  outline: none;
  cursor: pointer;
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

.btn-pill-action {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 0.5rem 0.85rem;
  border-radius: 10px;
  font-size: 0.8rem;
  font-weight: 800;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-pill-action.expense {
  background: #ffe4e6;
  color: var(--DC-pink, #d80056);
}

.btn-pill-action.expense:hover {
  background: #fecdd3;
}

.btn-pill-action.income {
  background: #dcfce7;
  color: #15803d;
}

.btn-pill-action.income:hover {
  background: #bbf7d0;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #513119);
  font-size: 0.78rem;
  font-weight: 800;
}

/* ====================================================
   TABLAS Y FILAS
==================================================== */
.table-wrapper {
  max-height: 560px;
  overflow-y: auto;
  width: 100%;
}

.cash-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.cash-table thead th {
  background: #faf6f0;
  color: var(--DC-brown, #513119);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.8rem 0.6rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.1);
  position: sticky;
  top: 0;
  z-index: 2;
  text-align: left;
}

.cash-table tbody td {
  padding: 0.75rem 0.6rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  font-size: 0.86rem;
}

.cash-table thead th:first-child,
.cash-table tbody td:first-child {
  padding-left: 1.15rem;
}

.cash-table thead th:last-child,
.cash-table tbody td:last-child {
  padding-right: 1.15rem;
}

.cash-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.35);
}

.col-date {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
  white-space: nowrap;
}

.type-pill {
  font-size: 0.68rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
  text-transform: uppercase;
}

.pill-income { background: #dcfce7; color: #15803d; }
.pill-expense { background: #fee2e2; color: #b91c1c; }

.payment-tag {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 6px;
}

.tag-cash { background: #ffedd5; color: #c2410c; }
.tag-debit { background: #dbeafe; color: #1d4ed8; }
.tag-transfer { background: #f3e8ff; color: #7e22ce; }
.tag-default { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-text-gray, #7c7468); }

.col-category {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
}

.text-right { text-align: right; }
.col-amount { font-weight: 800; }

.diff-badge {
  font-size: 0.72rem;
  font-weight: 800;
  padding: 2px 8px;
  border-radius: 999px;
}

.diff-badge-ok { background: #dcfce7; color: #166534; }
.diff-badge-surplus { background: #dbeafe; color: #1e40af; }
.diff-badge-deficit { background: #fee2e2; color: #991b1b; }

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.65rem;
  padding: 3.5rem 1rem;
  color: var(--DC-text-gray, #7c7468);
  text-align: center;
}

.empty-state p {
  margin: 0;
  font-size: 0.88rem;
}

/* ====================================================
   MODALES (ESTILO ESTÁNDAR)
==================================================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
}

.modal-card {
  position: relative;
  width: min(100%, 460px);
  border-radius: 18px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
  overflow: hidden;
}

.modal-card.modal-arqueo {
  width: min(100%, 540px);
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

.header-icon-pill.bg-danger { background: #fee2e2; color: #dc2626; }
.header-icon-pill.bg-success { background: #dcfce7; color: #16a34a; }

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

.modal-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.modal-label span .required { color: #ef4444; }

.modal-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.65rem 0.85rem;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  outline: none;
}

.modal-input:focus { border-color: var(--DC-orange, #e28743); }

.price-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 10px;
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
}

.price-input-wrapper input { padding-left: 24px !important; }

.price-input-lg {
  font-size: 1.1rem !important;
  font-weight: 900 !important;
  color: var(--DC-orange, #e28743) !important;
}

.modal-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

/* Desglose Arqueo */
.arqueo-breakdown-card {
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  padding: 0.85rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.breakdown-line {
  display: flex;
  justify-content: space-between;
  font-size: 0.82rem;
  color: var(--DC-text-gray, #7c7468);
}

.breakdown-line.total-line {
  border-top: 1px dashed rgba(81, 49, 25, 0.15);
  padding-top: 6px;
  margin-top: 2px;
  font-size: 0.92rem;
  color: var(--DC-brown, #513119);
}

.total-expected-val {
  font-size: 1.05rem;
  color: var(--DC-orange, #e28743);
}

.aux-payments-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 6px;
  background: #fffdfa;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 10px;
  padding: 8px;
  text-align: center;
}

.aux-col small {
  display: block;
  font-size: 0.68rem;
  color: var(--DC-text-gray, #7c7468);
}

.aux-col span, .aux-col strong {
  font-size: 0.8rem;
  color: var(--DC-gray, #2c2724);
}

.cash-count-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.cuadratura-status-box {
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 0.8rem;
}

.status-box-content strong {
  display: block;
  font-size: 0.82rem;
  margin-bottom: 2px;
}

.status-box-content p {
  margin: 0;
  font-size: 0.74rem;
}

.diff-ok { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
.diff-surplus { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
.diff-deficit { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 1rem 1.25rem;
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

.btn-danger-submit {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-pink, #d80056);
  color: white;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-danger-submit:hover { background: #b00046; }

.spinning {
  animation: spin 0.9s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .cashflow-view {
    padding: 1rem;
  }

  .page-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }

  .header-actions {
    flex-direction: column;
    width: 100%;
  }

  .header-actions button {
    width: 100%;
    justify-content: center;
  }

  .shift-status-card {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }

  .summary-grid {
    grid-template-columns: 1fr;
  }

  .panel-toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .toolbar-left, .toolbar-right {
    width: 100%;
    flex-direction: column;
  }

  .search-box, .select-box, .select-box select, .btn-pill-action {
    width: 100%;
    min-width: 0;
    justify-content: center;
  }

  .desktop-table-only {
    display: block;
    overflow-x: auto;
  }

  .modal-row, .aux-payments-grid {
    grid-template-columns: 1fr;
  }
}
</style>