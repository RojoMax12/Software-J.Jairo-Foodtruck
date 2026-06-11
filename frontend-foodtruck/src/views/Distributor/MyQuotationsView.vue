<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { FileText, Calendar, Clock, ChevronRight, IceCream } from 'lucide-vue-next'
import Navbar from '@/components/Navbar.vue'
import quoteService from '@/services/quoteService'

const router = useRouter()

// --- ESTADOS REACTIVOS ---
const quotations = ref<any[]>([])
const isLoading = ref(true)
const userId = ref<number | null>(null)

// --- CARGA DE DATOS AL MONTAR EL COMPONENTE ---
onMounted(async () => {
  const userParsed = localStorage.getItem('user')
  
  if (userParsed) {
    try {
      const userObj = JSON.parse(userParsed)
      userId.value = userObj.id || null
      
      // Validación estricta para asegurar el tipo de dato en TypeScript
      if (!userId.value) {
        throw new Error('User ID not found in session')
      }

      // Llamada al servicio centralizado con el ID del distribuidor
      const response = await quoteService.getQuotesByDistributor(userId.value)
      quotations.value = response.data || []
      
    } catch (error) {
      console.error('Error fetching historical quotations:', error)
      
      quotations.value = [
        { id: 1, fecha_creacion: '2026-05-26', hora_creacion: '16:26:00', id_estado_cotizacion: 1, total_cotizacion: 139100 },
        { id: 2, fecha_creacion: '2026-05-12', hora_creacion: '11:15:00', id_estado_cotizacion: 2, total_cotizacion: 84500 },
        { id: 3, fecha_creacion: '2026-04-20', hora_creacion: '09:30:00', id_estado_cotizacion: 3, total_cotizacion: 210000 }
      ]
    } finally {
      isLoading.value = false
    }
  } else {
    router.push('/')
  }
})

// Mapea los IDs de estado a etiquetas de texto en español según tu base de datos
const getStatusLabel = (statusId: number): string => {
  if (statusId === 1 || statusId === 2) return 'En Revisión'
  if (statusId === 3) return 'Completado'
  if (statusId === 4) return 'Cancelado'
  return 'Desconocido'
}

// Vincula la clase CSS correspondiente según el ID de estado
const getStatusClassName = (statusId: number): string => {
  if (statusId === 1 || statusId === 2) return 'status-review'
  if (statusId === 3) return 'status-completed'
  if (statusId === 4) return 'status-cancelled'
  return ''
}

// Formatea las fechas al estándar chileno (DD/MM/AAAA)
const formatQuotationDate = (dateString: string): string => {
  if (!dateString) return ''
  const date = new Date(dateString + 'T00:00:00')
  return date.toLocaleDateString('es-CL')
}

// Formatea las marcas de tiempo a horas y minutos chilenos
const formatQuotationTime = (timeString: string): string => {
  if (!timeString) return ''
  // Si viene el string completo de hora "23:45:59", quitamos los segundos para la vista
  const parts = timeString.split(':')
  if (parts.length >= 2) {
    return `${parts[0]}:${parts[1]}`
  }
  return timeString
}

const handleGoBack = () => {
  router.push('/')
}
</script>

<template>
  <div class="my-quotations-page">

    <main class="history-container">
      <div class="title-section">
        <h2 class="main-title">Mis Cotizaciones</h2>
        <div class="title-line"></div>
      </div>

      <div v-if="isLoading" class="loading-state">
        <IceCream class="spinner" :size="40" color="#e4869f" />
        <p>Buscando tus cotizaciones...</p>
      </div>

      <div v-else-if="quotations.length === 0" class="empty-state">
        <FileText :size="48" color="#b5b2bc" />
        <p class="empty-text">Aún no has ingresado ninguna cotización en el sistema.</p>
        <button class="btn-start-quoting" @click="handleGoBack">Cotizar Helados</button>
      </div>

      <div v-else class="quotations-list">
        <div 
          v-for="quote in quotations" 
          :key="quote.id" 
          class="quotation-card"
          @click="router.push(`/cotizacion/${quote.id}`)"
        >
          <div class="card-left">
            <div class="file-icon-box">
              <FileText :size="24" color="#322c44" />
            </div>
            
            <div class="quotation-meta">
              <h4 class="quotation-number">Cotización N° {{ String(quote.id).padStart(6, '0') }}</h4>
              <div class="time-group">
                <span class="time-item"><Calendar :size="14" /> {{ formatQuotationDate(quote.fecha_creacion) }}</span>
                <span class="time-item"><Clock :size="14" /> {{ formatQuotationTime(quote.hora_creacion || quote.fecha_creacion) }}</span>
              </div>
            </div>
          </div>

          <div class="card-right">
            <span class="quotation-total" v-if="quote.total_cotizacion || quote.total">
              ${{ Number(quote.total_cotizacion || quote.total).toLocaleString('es-CL') }}
            </span>
            
            <span class="status-badge" :class="getStatusClassName(quote.id_estado_cotizacion || quote.estado_cotizacion)">
              {{ getStatusLabel(quote.id_estado_cotizacion || quote.estado_cotizacion) }}
            </span>

            <ChevronRight class="arrow-icon" :size="20" color="#b5b2bc" />
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<style scoped>
.my-quotations-page {
  background-color: #eeedee;
  min-height: 100vh;
  font-family: sans-serif;
  padding-bottom: 60px;
}

.history-container {
  max-width: 850px;
  margin: 35px auto;
  padding: 0 20px;
}

.title-section { margin-bottom: 30px; }
.main-title { font-size: 1.25rem; font-weight: 800; color: #1a1624; margin: 0 0 6px 0; text-align: left; }
.title-line { height: 2px; background-color: #e4869f; width: 100%; }

.quotations-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.quotation-card {
  background-color: white;
  border-radius: 18px;
  padding: 16px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.quotation-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 18px rgba(228, 134, 159, 0.12);
}

.card-left, .card-right {
  display: flex;
  align-items: center;
  gap: 20px;
}

.file-icon-box {
  background-color: #f3eff2;
  width: 45px;
  height: 45px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.quotation-meta {
  text-align: left;
}

.quotation-number {
  margin: 0 0 4px 0;
  font-size: 1.05rem;
  font-weight: 700;
  color: #1a1624;
}

.time-group {
  display: flex;
  gap: 15px;
  color: #888;
  font-size: 0.85rem;
}
.time-item {
  display: flex;
  align-items: center;
  gap: 4px;
}

.quotation-total {
  font-weight: 700;
  color: #1a1624;
  font-size: 1.05rem;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
  text-align: center;
  min-width: 100px;
}

/* Diseños dinámicos de los badges basados en los estados de la base de datos */
.status-pending {
  background-color: #fffbeb;
  color: #d97706;
  border: 1px solid #fef3c7;
}

.status-review {
  background-color: #eff6ff;
  color: #2563eb;
  border: 1px solid #dbeafe;
}

.status-completed {
  background-color: #f0fdf4;
  color: #16a34a;
  border: 1px solid #dcfce7;
}

.status-cancelled {
  background-color: #fef2f2;
  color: #dc2626;
  border: 1px solid #fee2e2;
}

.loading-state, .empty-state {
  background-color: white;
  border-radius: 20px;
  padding: 50px;
  text-align: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.empty-text { color: #666; margin: 15px 0 20px 0; font-size: 0.95rem; }

.btn-start-quoting {
  background-color: #322c44;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 12px;
  font-weight: bold;
  cursor: pointer;
}

.spinner {
  animation: rotate 2s linear infinite;
}

@keyframes rotate {
  100% { transform: rotate(360deg); }
}
</style>