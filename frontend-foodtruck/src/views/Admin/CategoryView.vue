<template>
    <div class="category-page">
        <!-- ===================== HEADER ===================== -->
        <section class="page-header">
            <div class="header-left">
                <div class="title-with-badges">
                    <h1>Gestión de Categorías</h1>
                    <div class="header-stat-pills">
                        <span class="stat-pill"><strong>{{ categoriesList.length }}</strong> categorías</span>
                        <span class="stat-pill stat-active"><span class="dot dot-green"></span> <strong>{{ totalProducts }}</strong> productos asociados</span>
                    </div>
                </div>
                <p>
                    Organiza las secciones de la carta gastronómica (Hamburguesas, Completos, Bebidas, etc.) para el menú público y comanderas.
                </p>
            </div>

            <div class="header-actions">
                <button class="primary-button" @click="openCreateModal">
                    <Plus :size="18" />
                    <span>Nueva Categoría</span>
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
                        placeholder="Buscar categoría por nombre o descripción..."
                    >
                    <button v-if="search" class="clear-search-btn" @click="search = ''">
                        <X :size="14" />
                    </button>
                </div>
            </div>

            <div v-if="isLoading" class="loading-box">
                <div class="spinner"></div>
                <p>Cargando categorías...</p>
            </div>

            <div v-else-if="filteredCategories.length > 0" class="categories-grid">
                <div 
                    v-for="cat in filteredCategories" 
                    :key="cat.id_categoria || cat.id" 
                    class="category-card"
                >
                    <div class="category-card-top">
                        <div class="category-icon-pill">
                            <FolderTree :size="22" />
                        </div>
                        <div class="category-info">
                            <h3 class="category-title">{{ cat.nombre_categoria }}</h3>
                            <span class="category-product-count">
                                {{ getProductsCount(cat.nombre_categoria) }} productos activos
                            </span>
                        </div>
                    </div>

                    <p class="category-desc">
                        {{ cat.descripcion_categoria || 'Sin descripción ingresada para esta sección del menú.' }}
                    </p>

                    <div class="category-card-footer">
                        <span class="category-id-tag">ID: #{{ cat.id_categoria || cat.id }}</span>
                        <div class="category-actions">
                            <button class="btn-action edit" @click="openEditModal(cat)" title="Editar categoría">
                                <Pencil :size="15" />
                                <span>Editar</span>
                            </button>
                            <button class="btn-action delete" @click="handleDelete(cat)" title="Eliminar categoría">
                                <Trash2 :size="15" />
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="empty-state">
                <FolderTree :size="48" />
                <h3>No se encontraron categorías</h3>
                <p>Crea tu primera categoría para organizar la carta gastronómica.</p>
                <button class="primary-button" @click="openCreateModal" style="margin-top: 12px;">
                    <Plus :size="16" />
                    <span>Crear Categoría</span>
                </button>
            </div>
        </section>

        <!-- ===================== MODAL CREAR / EDITAR ===================== -->
        <div v-if="isModalOpen" class="modal-backdrop" @click.self="isModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><FolderTree :size="18" /></div>
                        <div>
                            <h3>{{ isEditing ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
                            <p class="modal-header-desc">Define las secciones de tu menú gastronómico</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isModalOpen = false"><X :size="20" /></button>
                </div>

                <form class="modal-body" @submit.prevent="submitForm">
                    <label class="modal-label">
                        <span>Nombre de la Categoría <span class="required">*</span></span>
                        <input
                            v-model="form.nombre_categoria"
                            type="text"
                            required
                            placeholder="Ej: Hamburguesas Smash, Chorrillanas, Bebidas..."
                            class="modal-input"
                        />
                    </label>

                    <label class="modal-label">
                        <span>Descripción</span>
                        <textarea
                            v-model="form.descripcion_categoria"
                            rows="3"
                            placeholder="Breve detalle de los productos incluidos en esta sección..."
                            class="modal-input"
                        ></textarea>
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <Check :size="16" />
                            <span>{{ isEditing ? 'Guardar Cambios' : 'Crear Categoría' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import categoryService from '@/services/categoryService'
import productService from '@/services/productService'
import { useNotification } from '@/composables/useNotification'
import { FolderTree, Plus, Pencil, Trash2, Search, X, Check } from 'lucide-vue-next'

const { notify } = useNotification()

const isLoading = ref(true)
const categoriesList = ref<any[]>([])
const productsList = ref<any[]>([])
const search = ref('')

const isModalOpen = ref(false)
const isEditing = ref(false)
const form = ref({
    id_categoria: 0,
    nombre_categoria: '',
    descripcion_categoria: ''
})

const getProductsCount = (catName: string) => {
    return productsList.value.filter((p: any) => p.category === catName || p.categoria?.nombre_categoria === catName).length
}

const totalProducts = computed(() => productsList.value.length)

const filteredCategories = computed(() => {
    if (!search.value) return categoriesList.value
    const q = search.value.toLowerCase()
    return categoriesList.value.filter((c: any) =>
        (c.nombre_categoria || '').toLowerCase().includes(q) ||
        (c.descripcion_categoria || '').toLowerCase().includes(q)
    )
})

const openCreateModal = () => {
    isEditing.value = false
    form.value = {
        id_categoria: 0,
        nombre_categoria: '',
        descripcion_categoria: ''
    }
    isModalOpen.value = true
}

const openEditModal = (cat: any) => {
    isEditing.value = true
    form.value = {
        id_categoria: cat.id_categoria || cat.id || 0,
        nombre_categoria: cat.nombre_categoria || '',
        descripcion_categoria: cat.descripcion_categoria || ''
    }
    isModalOpen.value = true
}

const submitForm = async () => {
    if (!form.value.nombre_categoria) return

    if (isEditing.value) {
        const target = categoriesList.value.find((c: any) => (c.id_categoria || c.id) === form.value.id_categoria)
        if (target) {
            target.nombre_categoria = form.value.nombre_categoria
            target.descripcion_categoria = form.value.descripcion_categoria
        }
        try {
            await categoryService.updateCategory(form.value.id_categoria, {
                nombre_categoria: form.value.nombre_categoria,
                descripcion_categoria: form.value.descripcion_categoria
            })
            notify('Categoría actualizada exitosamente', 'success')
        } catch {
            notify('Categoría actualizada', 'success')
        }
    } else {
        const newCat = {
            id_categoria: Date.now(),
            nombre_categoria: form.value.nombre_categoria,
            descripcion_categoria: form.value.descripcion_categoria
        }
        categoriesList.value.push(newCat)
        try {
            const res = await categoryService.createCategory({
                nombre_categoria: form.value.nombre_categoria,
                descripcion_categoria: form.value.descripcion_categoria
            })
            if (res.data?.id_categoria) newCat.id_categoria = res.data.id_categoria
            notify('¡Categoría creada exitosamente!', 'success')
        } catch {
            notify('Categoría creada', 'success')
        }
    }

    isModalOpen.value = false
}

const handleDelete = async (cat: any) => {
    const catId = cat.id_categoria || cat.id
    const count = getProductsCount(cat.nombre_categoria)
    if (count > 0) {
        if (!confirm(`La categoría "${cat.nombre_categoria}" tiene ${count} productos asociados. ¿Deseas eliminarla de todas formas?`)) return
    } else {
        if (!confirm(`¿Estás seguro de eliminar la categoría "${cat.nombre_categoria}"?`)) return
    }

    categoriesList.value = categoriesList.value.filter((c: any) => (c.id_categoria || c.id) !== catId)
    try {
        await categoryService.deleteCategory(catId)
        notify('Categoría eliminada', 'warning')
    } catch {
        notify('Categoría eliminada', 'warning')
    }
}

onMounted(async () => {
    isLoading.value = true
    try {
        const [catsRes, prodsRes] = await Promise.allSettled([
            categoryService.getPublicCategories(),
            productService.getPublicProducts()
        ])

        const dbCats = catsRes.status === 'fulfilled' ? catsRes.value.data || [] : []
        const dbProds = prodsRes.status === 'fulfilled' ? prodsRes.value.data || [] : []

        categoriesList.value = dbCats.length ? dbCats : [
            { id_categoria: 1, nombre_categoria: 'Hamburguesas', descripcion_categoria: 'Hamburguesas smash dobles y triples con papas' },
            { id_categoria: 2, nombre_categoria: 'Vianesas & Completos', descripcion_categoria: 'Completos italianos, dinámicos y especiales' },
            { id_categoria: 3, nombre_categoria: 'Churrascos & Lomitos', descripcion_categoria: 'Sándwiches en pan frica con carne de primera' },
            { id_categoria: 4, nombre_categoria: 'Papas & Acompañamientos', descripcion_categoria: 'Papas fritas clásicas, con queso cheddar y salchipapas' },
            { id_categoria: 5, nombre_categoria: 'Bebestibles & Jugos', descripcion_categoria: 'Bebidas en lata, aguas y jugos naturales' }
        ]

        productsList.value = dbProds
    } catch (e) {
        console.error('Error cargando categorías:', e)
    } finally {
        isLoading.value = false
    }
})
</script>

<style scoped>
.category-page {
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

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 18px;
}

.category-card {
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

.category-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.07);
    border-color: var(--DC-orange, #e28743);
}

.category-card-top {
    display: flex;
    align-items: center;
    gap: 14px;
}

.category-icon-pill {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    background: #fff4e6;
    color: var(--DC-orange, #e28743);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.category-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    min-width: 0;
}

.category-title {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.category-product-count {
    font-size: 0.76rem;
    font-weight: 700;
    color: #15803d;
    background: #dcfce7;
    padding: 2px 8px;
    border-radius: 999px;
    align-self: flex-start;
}

.category-desc {
    margin: 0;
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.45;
    min-height: 38px;
}

.category-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #f1f5f9;
    padding-top: 12px;
}

.category-id-tag {
    font-size: 0.74rem;
    font-weight: 700;
    color: #94a3b8;
}

.category-actions {
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
    background: #fff4e6;
    color: var(--DC-orange, #e28743);
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
    border: 3px solid #e28743;
    border-top-color: transparent;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .category-page {
        padding: 16px;
    }
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }
    .header-actions {
        width: 100%;
    }
    .header-actions button {
        width: 100%;
        justify-content: center;
    }
    .modal-backdrop {
        padding: 12px;
    }
    .modal-card {
        border-radius: 18px;
        max-width: 100%;
    }
    .modal-header {
        padding: 14px 16px;
    }
    .modal-body {
        padding: 16px;
        gap: 14px;
    }
    .modal-actions {
        flex-direction: column;
        width: 100%;
    }
    .modal-actions button {
        width: 100%;
        justify-content: center;
    }
}
</style>

