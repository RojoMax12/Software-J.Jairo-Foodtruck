<template>
  <div class="print-only" aria-hidden="true">
    <div class="print-header">
      <div class="print-title">J.Junior Foodtruck</div>
      <div class="print-order-id">Comanda Pedido #{{ orderId }}</div>
      <div class="print-time">
        {{ date || '-' }} · {{ time || currentFallbackTime }}
      </div>
    </div>

    <div class="print-body">
      <div v-for="p in products" :key="p.id" class="print-product">
        <div class="print-product-line">
          <div class="print-product-name">
            <span class="print-qty">{{ p.quantity }}x</span>
            <strong>{{ p.name }}</strong> ({{ p.format }})
          </div>
          <span class="print-price">${{ formatCLP(p.subtotal) }}</span>
        </div>
        <div v-if="(p.removedIngredients || []).length > 0" class="print-ingredients print-removed">
          <strong>SIN:</strong> {{ (p.removedIngredients || []).join(', ') }}
        </div>
        <div v-if="(p.addedExtras || []).length > 0" class="print-ingredients print-extras">
          <strong>EXTRA:</strong> {{ p.addedExtras.map((e: any) => `${e.name} (x${e.quantity})`).join(', ') }}
        </div>
      </div>
    </div>

    <div v-if="orderNotes" class="print-notes">
      <strong>NOTAS DE COCINA:</strong> {{ orderNotes }}
    </div>

    <div class="print-total">
      TOTAL: ${{ formatCLP(totalAmount) }}
    </div>

    <div class="print-client">
      <strong>Cliente:</strong> {{ distributor || 'Sin nombre' }} · Tel: {{ customerPhone || 'Sin teléfono' }}
    </div>

    <div class="print-footer">
      ¡Gracias por tu preferencia!
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatCLP } from '@/utils/format';

defineProps<{
  orderId: number | string;
  date?: string;
  time?: string;
  distributor?: string;
  customerPhone?: string;
  orderNotes?: string;
  products: any[];
  totalAmount: number;
}>();

const currentFallbackTime = computed(() =>
  new Date().toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit' })
);
</script>

<style scoped>
.print-only {
  display: none;
}
</style>

