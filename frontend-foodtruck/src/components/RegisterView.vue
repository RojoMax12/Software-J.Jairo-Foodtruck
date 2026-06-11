<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { 
  Mail, Lock, Building2, Phone, ArrowLeft, 
  Eye, EyeOff, MapPin, Fingerprint, 
  ChevronDown, Search, X
} from 'lucide-vue-next'
import { authService } from '../services/authService'
import SuccessAccountModal from './SuccessAccountModal.vue'

const router = useRouter()

const form = ref({
  rut_empresa: '',
  nombre_empresa: '',
  correo_electronico: '',
  telefono: '',
  comuna: '',
  direccion: '',
  contrasena: '',
  confirmPassword: ''
})

const isLoading = ref(false)
const errorMessage = ref('')
const showSuccessModal = ref(false)

const comunasSantiago = [
  'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central', 'Huechuraba', 
  'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana', 'La Reina', 
  'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 
  'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 
  'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón', 'Santiago', 'Vitacura',
  'Puente Alto', 'Pirque', 'San José de Maipo', 'San Bernardo', 'Buin', 'Calera de Tango', 
  'Paine', 'Colina', 'Lampa', 'Tiltil', 'Talagante', 'El Monte', 'Isla de Maipo', 
  'Padre Hurtado', 'Peñaflor', 'Melipilla', 'Alhué', 'Curacaví', 'María Pinto', 'San Pedro'
].sort()

// Searchable dropdown logic
const isDropdownOpen = ref(false)
const searchQuery = ref('')

const filteredComunas = computed(() => {
  if (!searchQuery.value) return comunasSantiago
  return comunasSantiago.filter(c => 
    c.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const selectComuna = (comuna: string) => {
  form.value.comuna = comuna
  searchQuery.value = ''
  isDropdownOpen.value = false
}

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
  if (isDropdownOpen.value) {
    searchQuery.value = ''
  }
}

const closeDropdown = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  if (!target.closest('.custom-dropdown-container')) {
    isDropdownOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
  window.removeEventListener('click', closeDropdown)
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const goBack = () => {
  router.back()
}

const handleRegister = async () => {
  // 1. Validaciones básicas antes de enviar
  if (!form.value.rut_empresa || !form.value.nombre_empresa || !form.value.correo_electronico || !form.value.contrasena) {
    errorMessage.value = 'Por favor, completa todos los campos obligatorios.'
    return
  }

  if (form.value.contrasena !== form.value.confirmPassword) {
    errorMessage.value = 'Las contraseñas no coinciden.'
    return
  }

  errorMessage.value = ''
  isLoading.value = true

  try {
    // 2. Llamada al backend
    await authService.registerDistribuidor({
      rut_empresa: form.value.rut_empresa,
      nombre_empresa: form.value.nombre_empresa,
      correo_electronico: form.value.correo_electronico,
      telefono: form.value.telefono,
      comuna: form.value.comuna,
      direccion: form.value.direccion,
      contrasena: form.value.contrasena
    })

    // 3. Éxito: Mostramos el modal
    showSuccessModal.value = true
  } catch (error: any) {
    // 4. Error: Capturamos mensajes del backend (ej. "El correo_electronico ya existe")
    errorMessage.value = error.response?.data?.message || 'Error al crear la cuenta. Intenta nuevamente.'
  } finally {
    isLoading.value = false
  }
}

const goToLogin = () => {
  router.push('/login')
}
</script>

<template>
  <div class="register-container">
    <div class="register-wrapper">
      <div class="back-button" @click="goBack" v-if="!showSuccessModal">
        <ArrowLeft :size="24" color="#e4869f" />
        <span>Volver</span>
      </div>

      <div class="register-card">
        <div class="logo-section">
          <img src="../assets/logo_dicreme.png" alt="DiCreme Logo" class="logo" />
        </div>
        
        <div class="divider"></div>

        <div class="form-section">
          <!-- Mensaje de Error -->
          <div v-if="errorMessage" class="error-banner">
            {{ errorMessage }}
          </div>

          <!-- Fila 1: RUT Empresa -->
          <div class="input-group">
            <input 
              v-model="form.rut_empresa" 
              type="text" 
              placeholder="RUT Empresa" 
              class="custom-input"
              :disabled="isLoading"
            />
            <Fingerprint class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Fila 2: Nombre de Empresa -->
          <div class="input-group">
            <input 
              v-model="form.nombre_empresa" 
              type="text" 
              placeholder="Nombre de Empresa" 
              class="custom-input"
              :disabled="isLoading"
            />
            <Building2 class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Fila 3: Correo electrónico -->
          <div class="input-group">
            <input 
              v-model="form.correo_electronico" 
              type="email" 
              placeholder="Correo electrónico" 
              class="custom-input"
              :disabled="isLoading"
            />
            <Mail class="input-icon" :size="20" color="#322c44" />
          </div>

          <!-- Fila 4: Teléfono -->
          <div class="input-group">
            <div class="phone-input-wrapper">
              <div class="phone-prefix-outside">+56</div>
              <div class="phone-input-container">
                <input 
                  v-model="form.telefono" 
                  type="tel" 
                  placeholder="Teléfono" 
                  class="custom-input phone-input"
                  maxlength="9"
                  :disabled="isLoading"
                />
                <Phone class="input-icon" :size="20" color="#322c44" />
              </div>
            </div>
          </div>

          <!-- Fila 5: Comuna | Dirección -->
          <div class="input-row">
            <div class="input-group half custom-dropdown-container">
              <div 
                class="custom-input clickable-input" 
                :class="{ 'disabled-input': isLoading, 'placeholder-text': !form.comuna }"
                @click.stop="!isLoading && toggleDropdown()"
              >
                <span>
                  {{ form.comuna || 'Comuna' }}
                </span>
                <ChevronDown class="input-icon" :size="20" color="#322c44" :class="{ 'rotated': isDropdownOpen }" />
              </div>
              
              <!-- Dropdown Menu -->
              <div v-if="isDropdownOpen" class="dropdown-menu">
                <div class="search-inner" @click.stop>
                  <Search :size="16" class="inner-search-icon" />
                  <input 
                    v-model="searchQuery" 
                    type="text" 
                    placeholder="Buscar comuna..." 
                    class="inner-search-input"
                    autofocus
                  />
                  <X v-if="searchQuery" :size="16" class="clear-icon" @click="searchQuery = ''" />
                </div>
                <div class="options-list">
                  <div 
                    v-for="comuna in filteredComunas" 
                    :key="comuna" 
                    class="option-item"
                    :class="{ 'selected': form.comuna === comuna }"
                    @click="selectComuna(comuna)"
                  >
                    {{ comuna }}
                  </div>
                  <div v-if="filteredComunas.length === 0" class="no-results">
                    No se encontraron resultados
                  </div>
                </div>
              </div>
            </div>

            <div class="input-group half">
              <input 
                v-model="form.direccion" 
                type="text" 
                placeholder="Dirección" 
                class="custom-input"
                :disabled="isLoading"
              />
              <MapPin class="input-icon" :size="20" color="#322c44" />
            </div>
          </div>
          
          <!-- Fila 6: Contraseña -->
          <div class="input-group">
            <input 
              v-model="form.contrasena" 
              :type="showPassword ? 'text' : 'password'" 
              placeholder="Contraseña" 
              class="custom-input"
              :disabled="isLoading"
            />
            <div class="icon-wrapper" @click="showPassword = !showPassword">
              <Eye v-if="!showPassword" class="input-icon clickable" :size="20" color="#322c44" />
              <EyeOff v-else class="input-icon clickable" :size="20" color="#322c44" />
            </div>
          </div>

          <!-- Fila 7: Confirmar Contraseña -->
          <div class="input-group">
            <input 
              v-model="form.confirmPassword" 
              :type="showConfirmPassword ? 'text' : 'password'" 
              placeholder="Confirmar contraseña" 
              class="custom-input"
              :disabled="isLoading"
            />
            <div class="icon-wrapper" @click="showConfirmPassword = !showConfirmPassword">
              <Eye v-if="!showConfirmPassword" class="input-icon clickable" :size="20" color="#322c44" />
              <EyeOff v-else class="input-icon clickable" :size="20" color="#322c44" />
            </div>
          </div>

          <button 
            @click="handleRegister" 
            class="btn btn-primary"
            :disabled="isLoading"
          >
            {{ isLoading ? 'PROCESANDO...' : 'CREAR CUENTA' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessAccountModal
      v-if="showSuccessModal"
      @accept="goToLogin"
    />
  </div>
</template>

<style scoped>
.register-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #eeedee;
  font-family: sans-serif;
  padding: 2rem 0;
}

.register-wrapper {
  position: relative;
  width: 100%;
  max-width: 450px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.back-button {
  position: absolute;
  left: -6.5rem;
  top: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  cursor: pointer;
  color: #e4869f;
  font-weight: bold;
  transition: all 0.2s ease;
}

.back-button:hover {
  transform: translateX(-5px);
}

.back-button span {
  margin-top: 0.5rem;
}

.register-card {
  background-color: white;
  padding: 2rem;
  border-radius: 1.5rem;
  width: 100%;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  align-items: center;
}

.logo-section {
  width: 100%;
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.logo {
  max-width: 180px;
  height: auto;
}

.divider {
  width: 80%;
  height: 2px;
  background-color: #e4869f;
  margin-bottom: 2rem;
}

.form-section {
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: center;
}

.error-banner {
  width: 100%;
  padding: 0.75rem 1rem;
  background-color: #fff5f5;
  border: 1px solid #fa5252;
  border-radius: 0.75rem;
  color: #fa5252;
  font-size: 0.9rem;
  font-weight: 600;
  text-align: center;
}

.input-row {
  width: 100%;
  display: flex;
  gap: 1rem;
}

.input-group {
  width: 100%;
  position: relative;
  display: flex;
  align-items: center;
}

.input-group.half {
  flex: 1;
}

.custom-input {
  width: 100%;
  padding: 0.75rem 2.5rem 0.75rem 1rem;
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  font-size: 1rem;
  outline: none;
  box-sizing: border-box;
  transition: all 0.2s;
}

.custom-input:focus {
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(228, 134, 159, 0.2);
}

.disabled-input {
  opacity: 0.7;
  cursor: not-allowed;
}

.custom-input::placeholder {
  color: #9793a0;
}

.custom-select {
  appearance: none;
  cursor: pointer;
  color: #322c44;
}

/* Phone Prefix Styles */
.phone-input-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
}

.phone-prefix-outside {
  background-color: #e6e6e6;
  border: 1px solid #e4869f;
  border-radius: 0.75rem;
  padding: 0.75rem 0.8rem;
  font-weight: 600;
  color: #e4869f;
  font-size: 1rem;
  user-select: none;
  min-width: 55px;
  text-align: center;
}

.phone-input-container {
  flex: 1;
  position: relative;
  display: flex;
  align-items: center;
}

.phone-input {
  padding-left: 1rem !important;
}

/* Custom Dropdown Styles */
.custom-dropdown-container {
  position: relative;
}

.clickable-input {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.placeholder-text {
  color: #9793a0 !important;
}

.rotated {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  width: 100%;
  background-color: white;
  border: 1px solid #e4869f;
  border-radius: 1rem;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  z-index: 1000;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.search-inner {
  padding: 12px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid #f0f0f0;
  background-color: #fafafa;
}

.inner-search-icon {
  color: #e4869f;
}

.inner-search-input {
  border: none;
  background: transparent;
  outline: none;
  font-size: 0.95rem;
  width: 100%;
  color: #322c44;
  font-weight: 500;
}

.clear-icon {
  color: #9793a0;
  cursor: pointer;
  padding: 2px;
  border-radius: 50%;
  transition: background-color 0.2s;
}

.clear-icon:hover {
  background-color: #eee;
}

.options-list {
  max-height: 220px;
  overflow-y: auto;
  padding: 6px 0;
}

.options-list::-webkit-scrollbar {
  width: 6px;
}

.options-list::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.options-list::-webkit-scrollbar-thumb {
  background: #e4869f;
  border-radius: 10px;
}

.option-item {
  padding: 12px 20px;
  font-size: 0.95rem;
  color: #495057;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
}

.option-item:hover {
  background-color: #fff0f3;
  color: #e4869f;
  padding-left: 25px;
}

.option-item.selected {
  background-color: #e4869f;
  color: white;
  font-weight: 600;
}

.no-results {
  padding: 20px;
  text-align: center;
  font-size: 0.9rem;
  color: #9793a0;
  font-style: italic;
}

.input-icon {
  position: absolute;
  right: 1rem;
  pointer-events: none;
  transition: transform 0.3s ease;
}

.icon-wrapper {
  position: absolute;
  right: 0;
  height: 100%;
  width: 3rem;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.clickable {
  pointer-events: auto;
}

.btn {
  width: 100%;
  padding: 0.75rem;
  border: none;
  border-radius: 0.75rem;
  font-weight: bold;
  cursor: pointer;
  font-size: 1rem;
  transition: all 0.2s ease;
  margin-top: 0.5rem;
}

.btn:hover:not(:disabled) {
  transform: translateY(-2px);
  filter: brightness(0.9);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn:active:not(:disabled) {
  transform: translateY(0);
  filter: brightness(0.8);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  filter: grayscale(0.5);
}

.btn-primary {
  background-color: #e4869f;
  color: white;
}
</style>