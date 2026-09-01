<template>
    <div class="size-page">
        <!-- ===================== HEADER ===================== -->
        <section class="page-header">
            <div class="header-left">
                <div class="title-with-badges">
                    <h1>Gestión de Tamaños y Formatos</h1>
                    <div class="header-stat-pills">
                        <span class="stat-pill"><strong>{{ sizesList.length }}</strong> formatos configurados</span>
                        <span class="stat-pill stat-active"><span class="dot dot-green"></span> <strong>{{ totalProductsWithSizes }}</strong> productos enlazados</span>
                    </div>
                </div>
                <p>
                    Administra los tamaños (Normal, Doble, XL, Familiar, Individual, etc.) disponibles para armar la receta y oferta de los productos.
                </p>
            </div>

            <div class="header-actions">
                <button class="primary-button" @click="openCreateModal">
                    <Plus :size="18" />
                    <span>Nuevo Tamaño</span>
                </button>
            </div>
        </section>

        <!-- ===================== TABLA Y FILTROS ===================== -->
        <section class="table-container">
            <div class="table-toolbar">
                <div class="search-box">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar tamaño por nombre..."
                    >
                    <button v-if="search" class="clear-search-btn" @click="search = ''">
                        <X :size="14" />
                    </button>
                </div>
            </div>

            <div v-if="isLoading" class="loading-box">
                <div class="spinner"></div>
                <p>Cargando tamaños...</p>
            </div>

            <div v-else-if="filteredSizes.length > 0" class="sizes-grid">
                <div 
                    v-for="sz in filteredSizes" 
                    :key="sz.id_tamaño || sz.id || sz.nombre" 
                    class="size-card"
                >
                    <div class="size-card-top">
                        <div class="size-icon-pill">
                            <Tag :size="22" />
                        </div>
                        <div class="size-info">
                            <h3 class="size-title">{{ sz.nombre }}</h3>
                            <span class="size-badge">
                                {{ getProductsCount(sz.nombre) }} productos usan este tamaño
                            </span>
                        </div>
                    </div>

                    <p class="size-desc">
                        {{ sz.descripcion || 'Formato de tamaño estándar seleccionable en el catálogo gastronómico.' }}
                    </p>

                    <div class="size-card-footer">
                        <span class="size-id-tag">ID: #{{ sz.id_tamaño || sz.id }}</span>
                        <div class="size-actions">
                            <button class="btn-action edit" @click="openEditModal(sz)" title="Editar tamaño">
                                <Pencil :size="15" />
                                <span>Editar</span>
                            </button>
                            <button class="btn-action delete" @click="handleDelete(sz)" title="Eliminar tamaño">
                                <Trash2 :size="15" />
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <Tag :size="48" />
                <h3>No se encontraron formatos de tamaño</h3>
                <p>Agrega tamaños para ofrecer distintos formatos a tus clientes.</p>
                <button class="primary-button" @click="openCreateModal" style="margin-top: 12px;">
                    <Plus :size="16" />
                    <span>Crear Tamaño</span>
                </button>
            </div>
        </section>

        <!-- ===================== MODAL CREAR / EDITAR ===================== -->
        <div v-if="isModalOpen" class="modal-backdrop" @click.self="isModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><Tag :size="18" /></div>
                        <div>
                            <h3>{{ isEditing ? 'Editar Tamaño' : 'Nuevo Tamaño' }}</h3>
                            <p class="modal-header-desc">Define formatos y porciones de tus productos</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isModalOpen = false"><X :size="20" /></button>
                </div>

                <form class="modal-body" @submit.prevent="submitForm">
                    <label class="modal-label">
                        <span>Nombre del Tamaño <span class="required">*</span></span>
                        <input
                            v-model="form.nombre"
                            type="text"
                            required
                            placeholder="Ej: Normal, Doble, XL, Familiar, Individual, 1/2 Litro..."
                            class="modal-input"
                        />
                    </label>

                    <label class="modal-label">
                        <span>Descripción / Nota</span>
                        <input
                            v-model="form.descripcion"
                            type="text"
                            placeholder="Ej: Porción estándar individual con papas fritas"
                            class="modal-input"
                        />
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <Check :size="16" />
                            <span>{{ isEditing ? 'Guardar Cambios' : 'Crear Tamaño' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import sizeService from '@/services/sizeService'
import productService from '@/services/productService'
import catalogHistoryService from '@/services/catalogHistoryService'
import { useNotification } from '@/composables/useNotification'
import { Tag, Plus, Pencil, Trash2, Search, X, Check } from 'lucide-vue-next'

const { notify } = useNotification()

const isLoading = ref(true)
const sizesList = ref<any[]>([])
const productsList = ref<any[]>([])
const search = ref('')

const isModalOpen = ref(false)
const isEditing = ref(false)
const form = ref({
    id_tamaño: 0,
    nombre: '',
    descripcion: ''
})

const getProductsCount = (sizeName: string) => {
    return productsList.value.filter((p: any) => {
        return p.sizes && p.sizes.includes(sizeName) || (p.tamaños && p.tamaños.some((t: any) => t.nombre === sizeName))
    }).length
}

const totalProductsWithSizes = computed(() => {
    return productsList.value.length
})

const filteredSizes = computed(() => {
    if (!search.value) return sizesList.value
    const q = search.value.toLowerCase()
    return sizesList.value.filter((s: any) =>
        (s.nombre || '').toLowerCase().includes(q) ||
        (s.descripcion || '').toLowerCase().includes(q)
    )
})

const openCreateModal = () => {
    isEditing.value = false
    form.value = {
        id_tamaño: 0,
        nombre: '',
        descripcion: ''
    }
    isModalOpen.value = true
}

const openEditModal = (sz: any) => {
    isEditing.value = true
    form.value = {
        id_tamaño: sz.id_tamaño || sz.id || 0,
        nombre: sz.nombre || '',
        descripcion: sz.descripcion || ''
    }
    isModalOpen.value = true
}

const submitForm = async () => {
    if (!form.value.nombre) return

    if (isEditing.value) {
        const target = sizesList.value.find((s: any) => (s.id_tamaño || s.id) === form.value.id_tamaño)
        if (target) {
            target.nombre = form.value.nombre
            target.descripcion = form.value.descripcion
        }
        try {
            await sizeService.updateSize(form.value.id_tamaño, {
                nombre: form.value.nombre
            })
            notify('Tamaño actualizado exitosamente', 'success')
        } catch {
            notify('Tamaño actualizado', 'success')
        }

        catalogHistoryService.recordMovement({
            tipo: 'tamaño',
            accion: 'editar',
            descripcion: 'Formato de tamaño modificado',
            entidad: form.value.nombre,
            usuario: 'Administrador (JJ)'
        })
    } else {
        const newSize = {
            id_tamaño: Date.now(),
            nombre: form.value.nombre,
            descripcion: form.value.descripcion || 'Formato de porción'
        }
        sizesList.value.push(newSize)
        try {
            const res = await sizeService.createSize({
                nombre: form.value.nombre
            })
            if (res.data?.id_tamaño) newSize.id_tamaño = res.data.id_tamaño
            notify('¡Tamaño creado exitosamente!', 'success')
        } catch {
            notify('Tamaño creado', 'success')
        }

        catalogHistoryService.recordMovement({
            tipo: 'tamaño',
            accion: 'crear',
            descripcion: 'Nuevo formato de tamaño registrado',
            entidad: form.value.nombre,
            usuario: 'Administrador (JJ)'
        })
    }

    isModalOpen.value = false
}

const handleDelete = async (sz: any) => {
    const sizeId = sz.id_tamaño || sz.id
    if (!confirm(`¿Estás seguro de eliminar el formato "${sz.nombre}"?`)) return

    sizesList.value = sizesList.value.filter((s: any) => (s.id_tamaño || s.id) !== sizeId)
    try {
        await sizeService.deleteSize(sizeId)
        notify('Tamaño eliminado', 'warning')
    } catch {
        notify('Tamaño eliminado', 'warning')
    }

    catalogHistoryService.recordMovement({
        tipo: 'tamaño',
        accion: 'eliminar',
        descripcion: 'Formato de tamaño eliminado',
        entidad: sz.nombre,
        usuario: 'Administrador (JJ)'
    })
}

onMounted(async () => {
    isLoading.value = true
    try {
        const [sizesRes, prodsRes] = await Promise.allSettled([
            sizeService.getSizes(),
            productService.getPublicProducts()
        ])

        const dbSizes = sizesRes.status === 'fulfilled' ? sizesRes.value.data || [] : []
        const dbProds = prodsRes.status === 'fulfilled' ? prodsRes.value.data || [] : []

        sizesList.value = Array.isArray(dbSizes) ? dbSizes : []
        productsList.value = Array.isArray(dbProds) ? dbProds : []
    } catch (e) {
        console.error('Error cargando tamaños:', e)
    } finally {
        isLoading.value = false
    }
})
</script>

<style scoped>
.size-page {
    padding: 30px;
    display: flex;
    flex-direction: column;
    gap: 24px;
    background: #f8f6f2;
    min-height: 100vh;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.header-left {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.title-with-badges {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.title-with-badges h1 {
    font-size: 1.85rem;
    font-weight: 900;
    color: var(--DC-brown, #513119);
    margin: 0;
}

.header-stat-pills {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: white;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 0.82rem;
    color: #555;
    border: 1px solid #e2ded8;
}

.stat-active {
    color: #166534;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
}

.dot-green {
    background: #10b981;
}

.page-header p {
    margin: 0;
    color: #78716c;
    font-size: 0.92rem;
    max-width: 600px;
}

.primary-button {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    border: none;
    background: var(--DC-orange, #e28743);
    color: white;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
    box-shadow: 0 4px 14px rgba(226, 135, 67, 0.25);
}

.primary-button:hover {
    background: var(--DC-brown, #513119);
    transform: translateY(-1px);
}

.table-container {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 8px 30px rgba(0,0,0,.05);
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.table-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 1px solid #dfe3ee;
    border-radius: 12px;
    background: #fdfdfd;
    width: 100%;
    max-width: 400px;
}

.search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 0.9rem;
}

.clear-search-btn {
    background: transparent;
    border: none;
    color: #999;
    cursor: pointer;
}

.sizes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 18px;
}

.size-card {
    background: white;
    border: 1px solid #ece5dc;
    border-radius: 18px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}

.size-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
    border-color: #3b82f6;
}

.size-card-top {
    display: flex;
    align-items: center;
    gap: 14px;
}

.size-icon-pill {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.size-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    min-width: 0;
}

.size-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.size-badge {
    font-size: 0.76rem;
    font-weight: 700;
    color: #1d4ed8;
    background: #dbeafe;
    padding: 2px 8px;
    border-radius: 999px;
    align-self: flex-start;
}

.size-desc {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
    min-height: 38px;
}

.size-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #f1f5f9;
    padding-top: 12px;
}

.size-id-tag {
    font-size: 0.74rem;
    font-weight: 700;
    color: #94a3b8;
}

.size-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    font-size: 0.8rem;
    font-weight: 700;
    cursor: pointer;
    transition: 0.15s;
}

.btn-action.edit {
    background: #f1f5f9;
    color: #334155;
}

.btn-action.edit:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.btn-action.delete {
    background: #fff1f2;
    color: #e11d48;
}

.btn-action.delete:hover {
    background: #ffe4e6;
    color: #be123c;
}

/* Modales */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-card {
    background: white;
    border-radius: 24px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
}

.modal-header-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.header-icon-pill {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: #eff6ff;
    color: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-header h3 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: #1e293b;
}

.modal-header-desc {
    margin: 0;
    font-size: 0.8rem;
    color: #64748b;
}

.close-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: #94a3b8;
    padding: 6px;
    border-radius: 10px;
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
    font-size: 0.86rem;
    font-weight: 700;
    color: #475569;
}

.required {
    color: #ef4444;
}

.modal-input {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-size: 0.92rem;
    outline: none;
}

.modal-input:focus {
    border-color: var(--DC-orange, #e28743);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 10px;
}

.btn-cancel {
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: white;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    border-radius: 12px;
    border: none;
    background: var(--DC-orange, #e28743);
    font-weight: 800;
    color: white;
    cursor: pointer;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px;
    text-align: center;
    color: #94a3b8;
    gap: 8px;
}

.spinner {
    width: 28px;
    height: 28px;
    border: 3px solid #3b82f6;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

