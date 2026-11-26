<template>
  <div class="step-container summary-step">
    <div class="summary-receipt-card">
      <div class="receipt-header">
        <CheckCircle :size="36" class="receipt-icon" />
        <h2>Confirmar Comanda</h2>
        <p>Verifica los detalles antes de enviar el pedido a cocina.</p>
      </div>

      <div class="receipt-grid">
        <div class="receipt-section client-info-box">
          <h3>Datos de Atención</h3>
          <div class="client-meta-row">
            <span>Cliente:</span>
            <strong>{{ customerName || 'Cliente Presencial' }}</strong>
          </div>
          <div class="client-meta-row">
            <span>Teléfono:</span>
            <strong>{{ customerPhone || 'Sin teléfono' }}</strong>
          </div>
          <div class="client-meta-row">
            <span>Método de Pago:</span>
            <strong class="payment-badge-highlight">{{ effectivePaymentMethod }}</strong>
          </div>
          <div v-if="customerNotes" class="client-meta-row notes-summary-row">
            <span>Notas:</span>
            <strong class="notes-text-highlight">{{ customerNotes }}</strong>
          </div>
        </div>

        <div class="receipt-section products-summary-box">
          <h3>Productos ({{ totalUnits }})</h3>
          <div class="receipt-products-list">
            <div v-for="item in cartItems" :key="item.id" class="receipt-item-row">
              <div class="receipt-item-left">
                <span class="receipt-qty">{{ item.quantity }}x</span>
                <div class="receipt-item-names">
                  <div class="receipt-item-name-header">
                    <strong>{{ item.fullName }} ({{ item.size }})</strong>
                    <span v-if="item.hasPromotion" class="receipt-promo-tag">-{{ item.discountPercent }}%</span>
                  </div>
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="receipt-tags">
                    <span class="tag-red">Sin {{ item.excluidos.join(', ') }}</span>
                  </div>
                  <div v-if="item.agregados && item.agregados.length > 0" class="receipt-tags">
                    <span class="tag-blue">+ {{ item.agregados.join(', ') }}</span>
                  </div>
                </div>
              </div>
              <div class="receipt-item-pricing-col">
                <span v-if="item.hasPromotion && item.originalPrice > item.price" class="receipt-original-subtotal">
                  ${{ formatCLP(item.originalPrice * item.quantity) }}
                </span>
                <span class="receipt-price">${{ formatCLP(item.price * item.quantity) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="totalSavings > 0" class="receipt-savings-strip">
        <span class="savings-text">Ahorro total por promociones aplicadas:</span>
        <strong class="savings-amount">-${{ formatCLP(totalSavings) }}</strong>
      </div>

      <div class="receipt-total-banner">
        <span>Total a Pagar</span>
        <strong>{{ totalQuote }}</strong>
      </div>

      <div class="receipt-actions">
        <button type="button" class="btn-pos-secondary" :disabled="isSubmitting" @click="$emit('back')">
          <ArrowLeft :size="18" /> Modificar Datos
        </button>
        <button
          type="button"
          class="btn-pos-primary btn-confirm-order"
          :disabled="isSubmitting || esDiaCerrado"
          @click="$emit('confirm')"
        >
          <CheckCircle :size="18" />
          <span>{{ esDiaCerrado ? 'DÍA CERRADO (NO DISPONIBLE)' : (isSubmitting ? 'Enviando a Cocina...' : 'Enviar Pedido a Cocina') }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ArrowLeft, CheckCircle } from 'lucide-vue-next';
import { formatCLP } from '@/utils/format';

defineProps<{
  customerName: string;
  customerPhone: string;
  customerNotes: string;
  effectivePaymentMethod: string;
  cartItems: any[];
  totalUnits: number;
  totalSavings: number;
  totalQuote: string;
  isSubmitting: boolean;
  esDiaCerrado: boolean;
}>();

defineEmits<{
  back: [];
  confirm: [];
}>();
</script>

<style scoped>
.step-container {
  animation: fadeSlideUp 0.25s ease-out;
}

@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.summary-receipt-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  max-width: 750px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.receipt-header {
  text-align: center;
  border-bottom: 1px solid #f1ece7;
  padding-bottom: 14px;
}

.receipt-icon {
  color: #16a34a;
  margin-bottom: 6px;
}

.receipt-header h2 {
  font-size: 1.4rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0 0 4px;
}

.receipt-header p {
  color: #64748b;
  font-size: 0.85rem;
  margin: 0;
}

.receipt-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.receipt-section {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.receipt-section h3 {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 900;
  color: #1e293b;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 6px;
}

.client-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.client-meta-row span {
  color: #64748b;
}

.client-meta-row strong {
  color: #1e293b;
}

.payment-badge-highlight {
  background: #ffedd5;
  color: #c2410c;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
}

.notes-summary-row {
  margin-top: 4px;
}

.notes-text-highlight {
  font-size: 0.88rem;
  color: #0f172a;
  background: #f1f5f9;
  padding: 4px 8px;
  border-radius: 6px;
  font-weight: 600;
}

.receipt-products-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 250px;
  overflow-y: auto;
}

.receipt-item-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  font-size: 0.85rem;
  gap: 8px;
}

.receipt-item-left {
  display: flex;
  gap: 8px;
}

.receipt-qty {
  font-weight: 900;
  color: #ff6b00;
}

.receipt-item-names {
  display: flex;
  flex-direction: column;
}

.receipt-item-name-header {
  display: flex;
  align-items: center;
  gap: 6px;
  flex-wrap: wrap;
}

.receipt-promo-tag {
  background: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 1px 6px;
  border-radius: 4px;
}

.receipt-tags {
  margin-top: 2px;
}

.tag-red {
  color: #dc2626;
  font-size: 0.72rem;
  font-weight: 800;
}

.tag-blue {
  color: #2563eb;
  font-size: 0.72rem;
  font-weight: 800;
}

.receipt-item-pricing-col {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.receipt-original-subtotal {
  font-size: 0.78rem;
  color: #94a3b8;
  text-decoration: line-through;
  font-weight: 700;
}

.receipt-price {
  font-weight: 900;
  color: #059669;
}

.receipt-savings-strip {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: #fef2f2;
  border: 1.5px solid #fecaca;
  padding: 12px 18px;
  border-radius: 12px;
  margin-bottom: 14px;
}

.savings-text {
  color: #b91c1c;
  font-weight: 800;
  font-size: 0.95rem;
}

.savings-amount {
  color: #dc2626;
  font-weight: 900;
  font-size: 1.15rem;
}

.receipt-total-banner {
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.receipt-total-banner span {
  font-size: 0.95rem;
  font-weight: 900;
  color: #166534;
}

.receipt-total-banner strong {
  font-size: 1.5rem;
  font-weight: 900;
  color: #15803d;
}

.receipt-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.btn-pos-primary {
  background: #ff6b00;
  color: white;
  border: none;
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.95rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(255, 107, 0, 0.2);
}

.btn-pos-primary:hover:not(:disabled) {
  background: #e66000;
}

.btn-pos-primary:disabled {
  background: #cbd5e1;
  box-shadow: none;
  cursor: not-allowed;
}

.btn-pos-secondary {
  background: white;
  border: 1.5px solid #cbd5e1;
  color: #475569;
  padding: 12px 20px;
  border-radius: 12px;
  font-weight: 800;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.btn-pos-secondary:hover:not(:disabled) {
  border-color: #94a3b8;
  background: #f8fafc;
}

.btn-confirm-order {
  flex: 1;
  justify-content: center;
  font-size: 1rem;
  padding: 14px;
}

@media (max-width: 768px) {
  .summary-receipt-card {
    padding: 16px 14px;
  }

  .receipt-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .receipt-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn-pos-primary,
  .btn-pos-secondary,
  .btn-confirm-order {
    width: 100%;
    justify-content: center;
    padding: 14px;
    box-sizing: border-box;
  }
}
</style>

