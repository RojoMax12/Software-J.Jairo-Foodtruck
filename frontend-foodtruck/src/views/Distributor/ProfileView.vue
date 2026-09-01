<template>
  <div class="profile-page">
    <div class="profile-container">
      
      <!-- ENCABEZADO -->
      <div class="profile-header">
        <button class="btn-back-link" @click="router.push('/')">
          <ArrowLeft :size="18" />
          <span>Volver a la carta</span>
        </button>
        <h1 class="main-title">Mi Perfil</h1>
        <p class="main-subtitle">Gestiona tus datos de contacto y seguridad para agilizar tus pedidos.</p>
      </div>

      <!-- ALERTAS -->
      <Transition name="fade">
        <div v-if="successMessage" class="alert-box success-box">
          <CheckCircle2 :size="20" />
          <span>{{ successMessage }}</span>
        </div>
      </Transition>

      <Transition name="fade">
        <div v-if="errorMessage" class="alert-box error-box">
          <AlertCircle :size="20" />
          <span>{{ errorMessage }}</span>
        </div>
      </Transition>

      <!-- TARJETA PRINCIPAL DE DATOS -->
      <form @submit.prevent="handleSubmit" class="profile-card">
        
        <div class="card-section">
          <div class="section-title-row">
            <User :size="20" class="section-icon" />
            <h2 class="section-title">Información Personal</h2>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="nombre">Nombre completo</label>
              <input 
                id="nombre"
                v-model="form.nombre" 
                type="text" 
                placeholder="Ej: Juan Pérez"
                class="dc-input"
                required
              />
            </div>

            <div class="form-group">
              <label for="correo">Correo electrónico</label>
              <input 
                id="correo"
                v-model="form.correo" 
                type="email" 
                placeholder="ejemplo@correo.cl"
                class="dc-input"
                required
              />
            </div>

            <div class="form-group full-width">
              <label for="telefono">Teléfono de contacto</label>
              <div class="phone-input-wrapper">
                <span class="phone-prefix">+56 9</span>
                <input 
                  id="telefono"
                  v-model="rawPhone" 
                  type="tel" 
                  placeholder="1234 5678"
                  maxlength="8"
                  class="dc-input phone-input"
                  @input="handlePhoneInput"
                />
              </div>
              <span class="input-hint">Utilizado para coordinar la entrega o aviso de tu pedido.</span>
            </div>
          </div>
        </div>

        <div class="divider"></div>

        <!-- SECCIÓN DE SEGURIDAD (CAMBIO DE CONTRASEÑA) -->
        <div class="card-section">
          <div class="security-header" @click="showPasswordFields = !showPasswordFields">
            <div class="section-title-row">
              <Lock :size="20" class="section-icon" />
              <div>
                <h2 class="section-title">Cambiar Contraseña</h2>
                <span class="section-desc">Opcional: Cambia tu clave de acceso</span>
              </div>
            </div>
            <button type="button" class="btn-toggle-password">
              {{ showPasswordFields ? 'Cancelar cambio' : 'Modificar clave' }}
            </button>
          </div>

          <Transition name="slide-fade">
            <div v-if="showPasswordFields" class="password-fields-box">
              <div class="form-group">
                <label for="password_actual">Contraseña actual</label>
                <div class="input-icon-wrap">
                  <input 
                    id="password_actual"
                    v-model="form.password_actual" 
                    :type="showCurrentPass ? 'text' : 'password'" 
                    placeholder="Ingresa tu contraseña actual"
                    class="dc-input"
                  />
                  <button type="button" class="btn-eye" @click="showCurrentPass = !showCurrentPass">
                    <EyeOff v-if="showCurrentPass" :size="18" />
                    <Eye v-else :size="18" />
                  </button>
                </div>
              </div>

              <div class="form-grid">
                <div class="form-group">
                  <label for="nueva_password">Nueva contraseña</label>
                  <div class="input-icon-wrap">
                    <input 
                      id="nueva_password"
                      v-model="form.nueva_password" 
                      :type="showNewPass ? 'text' : 'password'" 
                      placeholder="Mínimo 8 caracteres"
                      class="dc-input"
                    />
                    <button type="button" class="btn-eye" @click="showNewPass = !showNewPass">
                      <EyeOff v-if="showNewPass" :size="18" />
                      <Eye v-else :size="18" />
                    </button>
                  </div>
                </div>

                <div class="form-group">
                  <label for="confirmar_password">Confirmar nueva contraseña</label>
                  <div class="input-icon-wrap">
                    <input 
                      id="confirmar_password"
                      v-model="form.confirmar_password" 
                      :type="showNewPass ? 'text' : 'password'" 
                      placeholder="Repite la nueva contraseña"
                      class="dc-input"
                    />
                  </div>
                </div>
              </div>

              <p class="password-rules">
                La contraseña debe contener al menos 8 caracteres, combinando mayúsculas, minúsculas, números y símbolos.
              </p>
            </div>
          </Transition>
        </div>

        <!-- BOTÓN GUARDAR -->
        <div class="card-actions">
          <button 
            type="submit" 
            class="btn-save-primary"
            :disabled="isSaving"
          >
            <Save :size="18" />
            <span>{{ isSaving ? 'Guardando cambios...' : 'Guardar Cambios' }}</span>
          </button>
        </div>

      </form>

    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ArrowLeft, User, Lock, Save, CheckCircle2, AlertCircle, Eye, EyeOff } from 'lucide-vue-next'
import { authService } from '@/services/authService'

const router = useRouter()

const form = ref({
  nombre: '',
  correo: '',
  telefono: '',
  password_actual: '',
  nueva_password: '',
  confirmar_password: ''
})

const rawPhone = ref('')
const isSaving = ref(false)
const showPasswordFields = ref(false)
const showCurrentPass = ref(false)
const showNewPass = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

onMounted(async () => {
  const userParsed = localStorage.getItem('user')
  if (userParsed) {
    try {
      const u = JSON.parse(userParsed)
      form.value.nombre = u.nombre || u.name || ''
      form.value.correo = u.correo || u.correo_electronico || u.email || ''
      
      const phoneStr = String(u.telefono || '').replace(/\D/g, '')
      if (phoneStr.startsWith('569') && phoneStr.length === 11) {
        rawPhone.value = phoneStr.slice(3)
      } else if (phoneStr.startsWith('9') && phoneStr.length === 9) {
        rawPhone.value = phoneStr.slice(1)
      } else {
        rawPhone.value = phoneStr.slice(-8)
      }
    } catch (e) {
      console.error(e)
    }
  }

  // Carga fresca desde backend
  try {
    const res = await authService.getProfile()
    const u = res?.user || res
    if (u) {
      form.value.nombre = u.nombre || form.value.nombre
      form.value.correo = u.correo || u.correo_electronico || form.value.correo
      const phoneStr = String(u.telefono || '').replace(/\D/g, '')
      if (phoneStr.startsWith('569') && phoneStr.length === 11) {
        rawPhone.value = phoneStr.slice(3)
      } else if (phoneStr.startsWith('9') && phoneStr.length === 9) {
        rawPhone.value = phoneStr.slice(1)
      } else {
        rawPhone.value = phoneStr.slice(-8)
      }
    }
  } catch (err) {
    // Continuar con fallback de sesión
  }
})

const handlePhoneInput = (e: Event) => {
  const input = e.target as HTMLInputElement
  rawPhone.value = input.value.replace(/\D/g, '').slice(0, 8)
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!form.value.nombre.trim()) {
    errorMessage.value = 'Por favor ingresa tu nombre completo.'
    return
  }

  if (!form.value.correo.trim()) {
    errorMessage.value = 'Por favor ingresa un correo electrónico válido.'
    return
  }

  if (rawPhone.value && rawPhone.value.length !== 8) {
    errorMessage.value = 'El número de teléfono debe tener exactamente 8 dígitos (ej: 12345678).'
    return
  }

  if (showPasswordFields.value && form.value.nueva_password) {
    if (!form.value.password_actual) {
      errorMessage.value = 'Debes ingresar tu contraseña actual para poder cambiarla.'
      return
    }
    if (form.value.nueva_password.length < 8) {
      errorMessage.value = 'La nueva contraseña debe tener al menos 8 caracteres.'
      return
    }
    if (form.value.nueva_password !== form.value.confirmar_password) {
      errorMessage.value = 'La nueva contraseña y su confirmación no coinciden.'
      return
    }
  }

  isSaving.value = true

  try {
    const payload: any = {
      nombre: form.value.nombre.trim(),
      correo: form.value.correo.trim(),
      correo_electronico: form.value.correo.trim(),
      telefono: rawPhone.value ? `+569${rawPhone.value}` : ''
    }

    if (showPasswordFields.value && form.value.nueva_password) {
      payload.password_actual = form.value.password_actual
      payload.nueva_password = form.value.nueva_password
    }

    const response = await authService.updateProfile(payload)

    if (response?.user) {
      // Actualizar localStorage para que todas las vistas reflejen el cambio
      const currentUser = JSON.parse(localStorage.getItem('user') || '{}')
      const updatedUser = {
        ...currentUser,
        ...response.user,
        nombre: response.user.nombre,
        correo: response.user.correo,
        telefono: response.user.telefono
      }
      localStorage.setItem('user', JSON.stringify(updatedUser))
    }

    successMessage.value = response?.message || '¡Tus datos se han actualizado con éxito!'
    
    // Limpiar campos de contraseña
    form.value.password_actual = ''
    form.value.nueva_password = ''
    form.value.confirmar_password = ''
    showPasswordFields.value = false

    setTimeout(() => {
      successMessage.value = ''
    }, 4000)
  } catch (err: any) {
    console.error('Error al actualizar perfil:', err)
    errorMessage.value = err?.response?.data?.message || 'Ocurrió un error al guardar los cambios.'
  } finally {
    isSaving.value = false
  }
}
</script>

<style scoped>
.profile-page {
  background-color: var(--DC-bg-gray, #f5ebe0);
  min-height: 100vh;
  padding: 30px 16px 80px 16px;
  font-family: var(--font-main, sans-serif);
  box-sizing: border-box;
}

.profile-container {
  max-width: 680px;
  margin: 0 auto;
}

.profile-header {
  margin-bottom: 24px;
}

.btn-back-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: none;
  border: none;
  color: var(--DC-brown, #513119);
  font-size: 0.88rem;
  font-weight: 800;
  cursor: pointer;
  padding: 0;
  margin-bottom: 12px;
  transition: color 0.2s;
}

.btn-back-link:hover {
  color: var(--DC-orange, #e28743);
}

.main-title {
  font-size: 1.8rem;
  font-weight: 900;
  color: var(--DC-brown, #513119);
  margin: 0 0 6px 0;
  letter-spacing: -0.4px;
}

.main-subtitle {
  font-size: 0.9rem;
  color: var(--DC-text-gray, #6e6a75);
  margin: 0;
}

/* ALERTAS */
.alert-box {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 18px;
  border-radius: 14px;
  font-size: 0.88rem;
  font-weight: 700;
  margin-bottom: 20px;
}

.success-box {
  background-color: #f0fdf4;
  color: #15803d;
  border: 1px solid #bbf7d0;
}

.error-box {
  background-color: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

/* TARJETA */
.profile-card {
  background: #ffffff;
  border-radius: 24px;
  padding: 30px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  box-shadow: 0 8px 30px rgba(81, 49, 25, 0.05);
}

.card-section {
  margin-bottom: 24px;
}

.section-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
}

.section-icon {
  color: var(--DC-orange, #e28743);
}

.section-title {
  font-size: 1.1rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  margin: 0;
}

.section-desc {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
  display: block;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.full-width {
  grid-column: 1 / -1;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.dc-input {
  width: 100%;
  padding: 12px 16px;
  border: 1.5px solid rgba(81, 49, 25, 0.18);
  border-radius: 12px;
  font-size: 0.92rem;
  font-weight: 600;
  color: var(--DC-gray, #322c44);
  background-color: #fdfaf6;
  outline: none;
  transition: all 0.2s;
  box-sizing: border-box;
  font-family: inherit;
}

.dc-input:focus {
  border-color: var(--DC-orange, #e28743);
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.phone-input-wrapper {
  display: flex;
  align-items: center;
  background-color: #fdfaf6;
  border: 1.5px solid rgba(81, 49, 25, 0.18);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.2s;
}

.phone-input-wrapper:focus-within {
  border-color: var(--DC-orange, #e28743);
  background-color: #ffffff;
  box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.phone-prefix {
  padding: 12px 18px;
  min-width: 72px;
  text-align: center;
  font-weight: 800;
  font-size: 0.95rem;
  color: var(--DC-brown, #513119);
  background: rgba(81, 49, 25, 0.08);
  border-right: 1.5px solid rgba(81, 49, 25, 0.15);
  user-select: none;
  display: flex;
  align-items: center;
  justify-content: center;
  box-sizing: border-box;
}

.phone-input {
  flex: 1;
  padding: 12px 16px !important;
  border: none !important;
  background: transparent !important;
  box-shadow: none !important;
  font-size: 1rem !important;
  letter-spacing: 1.5px;
}

.input-hint {
  font-size: 0.76rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
  margin-top: 2px;
}

.divider {
  height: 1px;
  background: rgba(81, 49, 25, 0.1);
  margin: 24px 0;
}

.security-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.btn-toggle-password {
  background: var(--button-color, #F4E1D2);
  color: var(--button-text, #513119);
  border: 1px solid rgba(81, 49, 25, 0.15);
  padding: 8px 14px;
  border-radius: 10px;
  font-weight: 800;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-toggle-password:hover {
  background: var(--DC-orange, #e28743);
  color: #ffffff;
}

.password-fields-box {
  margin-top: 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  padding: 18px;
  background: #fdfbf8;
  border-radius: 16px;
  border: 1px dashed rgba(81, 49, 25, 0.15);
}

.input-icon-wrap {
  position: relative;
  display: flex;
  align-items: center;
}

.btn-eye {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: var(--DC-text-gray, #6e6a75);
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 4px;
}

.password-rules {
  font-size: 0.76rem;
  color: var(--DC-text-gray, #6e6a75);
  font-weight: 600;
  margin: 4px 0 0 0;
}

.card-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 30px;
}

.btn-save-primary {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background-color: var(--DC-orange, #e28743);
  color: #ffffff;
  border: none;
  padding: 14px 28px;
  border-radius: 14px;
  font-size: 0.95rem;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.3);
  transition: all 0.2s ease;
}

.btn-save-primary:hover:not(:disabled) {
  background-color: var(--DC-brown, #513119);
  transform: translateY(-1px);
}

.btn-save-primary:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .profile-card {
    padding: 22px 18px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .security-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 12px;
  }

  .btn-save-primary {
    width: 100%;
    justify-content: center;
  }
}
</style>

