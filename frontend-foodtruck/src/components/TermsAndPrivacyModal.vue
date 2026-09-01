<template>
  <div v-if="isOpen" class="modal-backdrop" @click.self="closeModal">
    <div class="modal-card">
      <div class="modal-header">
        <div class="header-icon-box">
          <ShieldCheck :size="24" class="header-icon" />
        </div>
        <div class="header-text">
          <h3>Términos, Condiciones y Privacidad</h3>
          <p>Conforme a la Ley N° 21.719 sobre Protección de Datos Personales (Chile)</p>
        </div>
        <button class="btn-close" @click="closeModal" title="Cerrar">
          <X :size="20" />
        </button>
      </div>

      <div class="nav-tabs">
        <button 
          type="button" 
          class="tab-btn" 
          :class="{ active: activeTab === 'privacidad' }"
          @click="activeTab = 'privacidad'"
        >
          <Lock :size="16" />
          <span>Protección de Datos (Ley 21.719)</span>
        </button>
        <button 
          type="button" 
          class="tab-btn" 
          :class="{ active: activeTab === 'terminos' }"
          @click="activeTab = 'terminos'"
        >
          <FileText :size="16" />
          <span>Términos del Servicio</span>
        </button>
      </div>

      <div class="modal-body-scrollable">
        <!-- TAB 1: POLÍTICA DE PRIVACIDAD LEY 21.719 -->
        <div v-if="activeTab === 'privacidad'" class="legal-content">
          <div class="badge-compliance">
            <span class="compliance-dot"></span>
            <span>Cumplimiento Legal Ley N° 21.719 de la República de Chile</span>
          </div>

          <h4>1. Responsable del Tratamiento de Datos</h4>
          <p>
            El responsable del tratamiento de los datos personales recopilados a través de esta plataforma web es 
            <strong>Foodtruck J.Jairo</strong>. Nos comprometemos a resguardar la privacidad, confidencialidad e integridad de la información de nuestros clientes y trabajadores.
          </p>

          <h4>2. Finalidad del Tratamiento</h4>
          <p>Los datos personales (nombre, correo electrónico, teléfono, comuna/dirección y pedidos realizados) son tratados única y exclusivamente para los siguientes fines:</p>
          <ul>
            <li>Procesamiento, preparación y despacho oportuno de pedidos gastronómicos.</li>
            <li>Emisión de comprobantes de pago, boletas y facturas según la legislación tributaria chilena.</li>
            <li>Comunicación directa sobre el estado del pedido o contingencias en la atención.</li>
            <li>Gestión de accesos autenticados para trabajadores y administradores del local.</li>
          </ul>

          <h4>3. Medidas de Seguridad de la Información</h4>
          <p>
            En estricto apego al deber de seguridad establecido en la Ley N° 21.719, nuestra infraestructura implementa:
          </p>
          <ul>
            <li><strong>Sanitización profunda y anti-inyecciones:</strong> Filtros contra ataques de inyección SQL y Cross-Site Scripting (XSS).</li>
            <li><strong>Cifrado de contraseñas:</strong> Almacenamiento mediante algoritmos de hashing criptográfico unidireccional (Bcrypt/Argon2).</li>
            <li><strong>Pistas de Auditoría y Trazabilidad:</strong> Registro de logs de auditoría en servidor con enmascaramiento estricto de datos confidenciales.</li>
            <li><strong>Encabezados HTTP de seguridad:</strong> Políticas de protección contra clickjacking, MIME sniffing y ejecución no autorizada de scripts.</li>
          </ul>

          <h4>4. Derechos del Titular (Derechos ARCO)</h4>
          <p>
            Conforme a la legislación vigente, usted como titular de datos personales cuenta con los derechos de:
          </p>
          <ul>
            <li><strong>Acceso:</strong> Solicitar información sobre qué datos personales suyos mantenemos registrados.</li>
            <li><strong>Rectificación:</strong> Corregir datos inexactos, desactualizados o incompletos.</li>
            <li><strong>Cancelación / Supresión:</strong> Solicitar la eliminación de sus datos cuando ya no sean necesarios para los fines contratados.</li>
            <li><strong>Oposición:</strong> Oponerse al tratamiento de sus datos para fines específicos no esenciales.</li>
          </ul>

          <h4>5. Confidencialidad y Terceros</h4>
          <p>
            Foodtruck J.Jairo <strong>no vende, arrienda ni cede</strong> sus datos personales a terceros con fines publicitarios o comerciales ajenos a la operación de nuestro servicio.
          </p>
        </div>

        <!-- TAB 2: TÉRMINOS Y CONDICIONES GENERALES -->
        <div v-else class="legal-content">
          <h4>1. Aceptación de los Términos</h4>
          <p>
            Al registrar una cuenta de usuario o realizar pedidos a través de nuestra plataforma, el usuario declara haber leído, comprendido y aceptado en su totalidad los presentes Términos y Condiciones.
          </p>

          <h4>2. Registro y Seguridad de la Cuenta</h4>
          <p>
            El usuario es responsable de mantener la confidencialidad de sus credenciales de acceso y de toda actividad realizada desde su cuenta. Debe suministrar información verídica y actualizada al momento del registro.
          </p>

          <h4>3. Pedidos, Preparación y Precios</h4>
          <p>
            Todos los pedidos están sujetos a la disponibilidad de ingredientes y confirmación en tiempo real. Los precios indicados en el menú incluyen los impuestos correspondientes en moneda nacional (Pesos Chilenos - CLP).
          </p>

          <h4>4. Cancelaciones y Modificaciones</h4>
          <p>
            Debido a la naturaleza perecible de los alimentos preparados al instante, una vez que el pedido pase a estado <em>"En preparación"</em> en comanda de cocina, no se podrán realizar cancelaciones ni reembolsos salvo caso de fuerza mayor o error atribuible al local.
          </p>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn-accept" @click="handleAccept">
          <Check :size="18" />
          <span>He leído y entendido</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { ShieldCheck, Lock, FileText, X, Check } from 'lucide-vue-next'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'accept'): void
}>()

const activeTab = ref<'privacidad' | 'terminos'>('privacidad')

const closeModal = () => {
  emit('close')
}

const handleAccept = () => {
  emit('accept')
  emit('close')
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 12, 28, 0.65);
  backdrop-filter: blur(5px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  animation: fadeIn 0.2s ease-out;
}

.modal-card {
  background: #ffffff;
  border-radius: 20px;
  width: 100%;
  max-width: 650px;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
  animation: scaleUp 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-header {
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 16px;
  border-bottom: 1px solid #f0edf6;
  background: linear-gradient(135deg, #faf9fc 0%, #ffffff 100%);
}

.header-icon-box {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: rgba(235, 110, 48, 0.12);
  color: #eb6e30;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.header-text h3 {
  margin: 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: #2b213a;
}

.header-text p {
  margin: 2px 0 0 0;
  font-size: 0.82rem;
  color: #7b738c;
}

.btn-close {
  margin-left: auto;
  background: #f4f2f8;
  border: none;
  width: 34px;
  height: 34px;
  border-radius: 10px;
  color: #7b738c;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #ebe7f3;
  color: #2b213a;
}

.nav-tabs {
  display: flex;
  gap: 8px;
  padding: 12px 24px;
  background: #faf9fd;
  border-bottom: 1px solid #f0edf6;
}

.tab-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid transparent;
  background: transparent;
  font-size: 0.88rem;
  font-weight: 600;
  color: #6a627a;
  cursor: pointer;
  transition: all 0.2s;
}

.tab-btn:hover {
  background: #f1eef8;
  color: #2b213a;
}

.tab-btn.active {
  background: #ffffff;
  color: #eb6e30;
  border-color: #f0edf6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.modal-body-scrollable {
  padding: 24px;
  overflow-y: auto;
  font-size: 0.92rem;
  line-height: 1.6;
  color: #4a415a;
}

.badge-compliance {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 6px 12px;
  border-radius: 20px;
  background: #ecfdf5;
  color: #065f46;
  font-size: 0.78rem;
  font-weight: 700;
  margin-bottom: 16px;
}

.compliance-dot {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: #10b981;
}

.legal-content h4 {
  margin: 18px 0 8px 0;
  font-size: 0.98rem;
  font-weight: 700;
  color: #2b213a;
}

.legal-content h4:first-of-type {
  margin-top: 0;
}

.legal-content p {
  margin: 0 0 10px 0;
}

.legal-content ul {
  margin: 0 0 16px 0;
  padding-left: 20px;
}

.legal-content li {
  margin-bottom: 6px;
}

.modal-footer {
  padding: 16px 24px;
  border-top: 1px solid #f0edf6;
  background: #faf9fc;
  display: flex;
  justify-content: flex-end;
}

.btn-accept {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 11px 22px;
  border-radius: 12px;
  background: #eb6e30;
  color: #ffffff;
  border: none;
  font-size: 0.92rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s;
  box-shadow: 0 4px 12px rgba(235, 110, 48, 0.25);
}

.btn-accept:hover {
  background: #d95d20;
  transform: translateY(-1px);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes scaleUp {
  from { transform: scale(0.96); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>

