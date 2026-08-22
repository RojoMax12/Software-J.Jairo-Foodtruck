<template>
    <div class="products-page">

        <!-- ===================== HEADER ===================== -->

        <section class="page-header">

            <div class="header-left">
                <h1>Gestión de Productos</h1>
                <p>
                    Administra los productos disponibles, sus precios,
                    disponibilidad y ofertas.
                </p>
            </div>

            <div class="header-actions">

                <button class="secondary-button">
                    Exportar
                </button>

                <button class="primary-button" @click="openCreateModal">
                    <Plus :size="18" />
                    Nuevo producto
                </button>

            </div>

        </section>

        <!-- ===================== CONTENIDO ===================== -->

        <div class="content-grid">

            <!-- ===================== TABLA ===================== -->

            <section class="table-container">

                <!-- Barra superior -->

                <div class="table-toolbar">

                    <div class="search-box">

                        <Search :size="18" />

                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar producto..."
                        >

                    </div>

                    <select v-model="selectedCategory">
                        <option value="">Todas las categorías</option>
                        <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.nombre_categoria">
                            {{ cat.nombre_categoria }}
                        </option>
                    </select>

                </div>

                <!-- Tabla -->

                <table class="products-table">

                    <thead>

                        <tr>

                            <th>Producto</th>

                            <th>Categoría</th>

                            <th>Precio</th>

                            <th>Ingredientes</th>

                            <th>Estado</th>

                            <th>Oferta</th>

                            <th>Acciones</th>

                        </tr>

                    </thead>

                    <tbody v-if="isLoading">
                        <tr v-for="n in 5" :key="'prod-admin-skel-' + n" class="skeleton-row">
                            <td><div class="skeleton-pill width-120"></div></td>
                            <td><div class="skeleton-pill width-80"></div></td>
                            <td><div class="skeleton-pill width-70"></div></td>
                            <td><div class="skeleton-pill width-100"></div></td>
                            <td><div class="skeleton-pill width-60"></div></td>
                            <td><div class="skeleton-pill width-50"></div></td>
                            <td><div class="skeleton-pill width-80"></div></td>
                        </tr>
                    </tbody>

                    <tbody v-else>

                        <tr
                            v-for="product in paginatedProducts"
                            :key="product.id"
                        >

                            <!-- Producto -->

                            <td>

                                <div class="product-info">

                                    <div class="product-image">

                                        {{ product.image }}

                                    </div>

                                    <div>

                                        <h4>
                                            {{ product.name }}
                                        </h4>

                                        <small>
                                            ID #{{ product.id }}
                                        </small>

                                    </div>

                                </div>

                            </td>

                            <!-- Categoría -->

                            <td>

                                <span class="category-badge">

                                    {{ product.category }}

                                </span>

                            </td>

                            <!-- Precio -->

                            <td>

                                <strong>

                                    ${{ product.price.toLocaleString() }}

                                </strong>

                            </td>

                            <!-- Ingredientes -->

                            <td>

                                <div class="ingredients">
                                    <span
                                        v-for="ingredient in product.ingredients.slice(0, 2)"
                                        :key="ingredient"
                                        class="ingredient-tag"
                                    >
                                        {{ ingredient }}
                                    </span>

                                    <span
                                        v-if="product.ingredients.length > 2"
                                        class="ingredient-more"
                                    >
                                        +{{ product.ingredients.length - 2 }}
                                    </span>
                                </div>
                            </td>

                            <!-- Estado -->
                            <td>
                                <span
                                    class="status-badge clickable"
                                    :class="product.active ? 'active' : 'inactive'"
                                    title="Haz clic para activar / desactivar"
                                    @click="toggleProductStatus(product)"
                                >
                                    {{ product.active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>

                            <!-- Oferta -->
                            <td>
                                <span
                                    v-if="product.offer"
                                    class="offer-badge clickable"
                                    title="Gestionar oferta"
                                    @click="openOfferModal(product)"
                                >
                                    {{ product.offer }}%
                                </span>

                                <span
                                    v-else
                                    class="no-offer clickable"
                                    title="Crear oferta"
                                    @click="openOfferModal(product)"
                                >
                                    Sin oferta
                                </span>
                            </td>

                            <!-- Acciones -->
                            <td>
                                <div class="actions">
                                    <button
                                        class="icon-button"
                                        title="Editar producto"
                                        @click="openEditModal(product)"
                                    >
                                        <Pencil :size="17" />
                                    </button>

                                    <button
                                        class="icon-button"
                                        title="Gestionar oferta"
                                        @click="openOfferModal(product)"
                                    >
                                        <BadgePercent :size="17" />
                                    </button>

                                    <button
                                        class="icon-button delete-btn"
                                        title="Eliminar producto"
                                        @click="handleDeleteProduct(product)"
                                    >
                                        <Trash2 :size="17" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                    </tbody>

                </table>

                <!-- Sin resultados -->

                <div
                    v-if="paginatedProducts.length === 0"
                    class="empty-state"
                >

                    <PackageOpen :size="55" />

                    <h3>No se encontraron productos</h3>

                    <p>
                        Intenta modificar los filtros de búsqueda.
                    </p>

                </div>

                <!-- Paginación -->

                <div class="pagination">

                    <span>

                        Mostrando
                        {{ paginatedProducts.length }}
                        de
                        {{ filteredProducts.length }}
                        productos

                    </span>

                    <div class="pagination-buttons">

                        <button :disabled="currentPage === 1" @click="currentPage--">
                            <ChevronLeft :size="18" />
                        </button>

                        <button class="current-page">
                            {{ currentPage }}
                        </button>

                        <button :disabled="currentPage >= totalPages" @click="currentPage++">
                            <ChevronRight :size="18" />
                        </button>

                    </div>

                </div>

            </section>

            <!-- ===================== PANEL DERECHO ===================== -->

            <aside class="sidebar">

                <!-- Filtros -->

                <div class="sidebar-card">

                    <h3>

                        <Filter :size="18" />

                        Filtros

                    </h3>

                    <label>

                        Estado

                        <select v-model="selectedStatus">

                            <option value="">
                                Todos
                            </option>

                            <option value="active">
                                Activos
                            </option>

                            <option value="inactive">
                                Inactivos
                            </option>

                        </select>

                    </label>

                    <label>

                        Oferta

                        <select v-model="selectedOffer">

                            <option value="">
                                Todas
                            </option>

                            <option value="yes">
                                En oferta
                            </option>

                            <option value="no">
                                Sin oferta
                            </option>

                        </select>

                    </label>

                    <button class="apply-button">

                        Aplicar filtros

                    </button>

                </div>

                <!-- Resumen -->

                <div class="sidebar-card">

                    <h3>

                        <ChartColumn :size="18" />

                        Resumen

                    </h3>

                    <div class="summary-item">

                        <span>Total productos</span>

                        <strong>{{ products.length }}</strong>

                    </div>

                    <div class="summary-item">

                        <span>Activos</span>

                        <strong>{{ activeProducts }}</strong>

                    </div>

                    <div class="summary-item">

                        <span>En oferta</span>

                        <strong>{{ offerProducts }}</strong>

                    </div>

                    <div class="summary-item">

                        <span>Inactivos</span>

                        <strong>{{ inactiveProducts }}</strong>

                    </div>

                </div>

            </aside>

        </div>

        <!-- MODAL CREAR PRODUCTO -->
        <div v-if="isCreateModalOpen" class="modal-backdrop" @click.self="isCreateModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <h3><Plus :size="20" /> Crear Nuevo Producto</h3>
                    <button class="close-btn" @click="isCreateModalOpen = false"><X :size="20" /></button>
                </div>
                <form class="modal-body" @submit.prevent="submitCreateProduct">
                    <label class="modal-label">
                        Nombre del producto
                        <input v-model="createForm.nombre" type="text" required placeholder="Ej: Churrasco Italiano" class="modal-input" />
                    </label>

                    <div class="modal-row">
                        <label class="modal-label">
                            Categoría
                            <select v-model="createForm.id_categoria" required class="modal-input">
                                <option value="" disabled>Selecciona una categoría</option>
                                <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.id_categoria || cat.id">
                                    {{ cat.nombre_categoria }}
                                </option>
                            </select>
                        </label>

                        <label class="modal-label">
                            Precio Base ($)
                            <input v-model.number="createForm.precio_base" type="number" min="0" required class="modal-input" />
                        </label>
                    </div>

                    <div class="modal-section">
                        <span class="section-title">Tamaños Disponibles</span>
                        <div class="chip-selector">
                            <button 
                                v-for="sz in availableSizesList" 
                                :key="sz" 
                                type="button" 
                                class="chip-btn" 
                                :class="{ active: createForm.selectedSizes.includes(sz) }"
                                @click="toggleCreateSize(sz)"
                            >
                                {{ sz }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-section">
                        <span class="section-title">Ingredientes en Receta Base</span>
                        <div class="chip-selector wrap">
                            <button 
                                v-for="ing in availableIngredientsList" 
                                :key="ing" 
                                type="button" 
                                class="chip-btn small" 
                                :class="{ active: createForm.selectedIngredients.includes(ing) }"
                                @click="toggleCreateIngredient(ing)"
                            >
                                {{ ing }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isCreateModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">Crear Producto</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL EDITAR PRODUCTO -->
        <div v-if="isEditModalOpen" class="modal-backdrop" @click.self="isEditModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <h3><Pencil :size="20" /> Editar Producto</h3>
                    <button class="close-btn" @click="isEditModalOpen = false"><X :size="20" /></button>
                </div>
                <form class="modal-body" @submit.prevent="submitEditProduct">
                    <label class="modal-label">
                        Nombre del producto
                        <input v-model="editForm.name" type="text" required class="modal-input" />
                    </label>

                    <div class="modal-row">
                        <label class="modal-label">
                            Categoría
                            <select v-model="editForm.category" required class="modal-input">
                                <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.nombre_categoria">
                                    {{ cat.nombre_categoria }}
                                </option>
                            </select>
                        </label>

                        <label class="modal-label">
                            Precio ($)
                            <input v-model.number="editForm.price" type="number" min="0" required class="modal-input" />
                        </label>
                    </div>

                    <div class="modal-section">
                        <span class="section-title">Tamaños Disponibles</span>
                        <div class="chip-selector">
                            <button 
                                v-for="sz in availableSizesList" 
                                :key="sz" 
                                type="button" 
                                class="chip-btn" 
                                :class="{ active: editForm.selectedSizes.includes(sz) }"
                                @click="toggleEditSize(sz)"
                            >
                                {{ sz }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-section">
                        <span class="section-title">Ingredientes en Receta Base</span>
                        <div class="chip-selector wrap">
                            <button 
                                v-for="ing in availableIngredientsList" 
                                :key="ing" 
                                type="button" 
                                class="chip-btn small" 
                                :class="{ active: editForm.selectedIngredients.includes(ing) }"
                                @click="toggleEditIngredient(ing)"
                            >
                                {{ ing }}
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isEditModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL OFERTA -->
        <div v-if="isOfferModalOpen" class="modal-backdrop" @click.self="isOfferModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <h3><BadgePercent :size="20" /> Gestionar Oferta</h3>
                    <button class="close-btn" @click="isOfferModalOpen = false"><X :size="20" /></button>
                </div>
                <div class="modal-body">
                    <p class="offer-subtitle">Producto: <strong>{{ offerForm.productName }}</strong></p>

                    <label class="modal-label">
                        Porcentaje de Descuento (%)
                        <input v-model.number="offerForm.discountPercent" type="number" min="0" max="100" class="modal-input" placeholder="Ej: 15" />
                    </label>

                    <div class="preset-offers">
                        <button type="button" class="preset-btn" @click="offerForm.discountPercent = 10">10% Off</button>
                        <button type="button" class="preset-btn" @click="offerForm.discountPercent = 15">15% Off</button>
                        <button type="button" class="preset-btn" @click="offerForm.discountPercent = 20">20% Off</button>
                        <button type="button" class="preset-btn" @click="offerForm.discountPercent = 30">30% Off</button>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-remove" @click="clearOffer">Quitar Oferta</button>
                        <button type="button" class="btn-save" @click="submitOffer">Aplicar Oferta</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script setup lang="ts">

import { computed, ref, onMounted } from 'vue'
import productService from '@/services/productService'
import categoryService from '@/services/categoryService'
import stockService from '@/services/stockService'
import { useNotification } from '@/composables/useNotification'

import {
    BadgePercent,
    ChartColumn,
    ChevronLeft,
    ChevronRight,
    Filter,
    MoreVertical,
    PackageOpen,
    Pencil,
    Plus,
    Search,
    Trash2,
    X
} from 'lucide-vue-next'

const { notify } = useNotification()

const isLoading = ref(true)
const products = ref<any[]>([])
const categoriesList = ref<any[]>([])

const availableSizesList = ['Normal', 'Doble', 'XL', 'Familiar', 'Individual']
const availableIngredientsList = ref<string[]>([
    'Vianesa', 'Carne smash', 'Lomo', 'Churrasco', 'Pollo', 'Queso cheddar', 
    'Queso mantecoso', 'Tomate', 'Palta', 'Chucrut', 'Cebolla caramelizada', 
    'Pepinillos', 'Mayonesa casera', 'Ketchup', 'Mostaza', 'Papas hilo', 'Tocino'
])

// Modal States & Forms
const isCreateModalOpen = ref(false)
const isEditModalOpen = ref(false)
const isOfferModalOpen = ref(false)
const selectedProductForAction = ref<any>(null)

const createForm = ref({
    nombre: '',
    id_categoria: '',
    precio_base: 4500,
    descripcion: '',
    selectedSizes: ['Normal'],
    selectedIngredients: ['Pan', 'Carne', 'Queso cheddar'] as string[]
})

const editForm = ref({
    id: 0,
    name: '',
    category: '',
    price: 0,
    active: true,
    selectedSizes: [] as string[],
    selectedIngredients: [] as string[]
})

const offerForm = ref({
    productId: 0,
    productName: '',
    discountPercent: 10
})

const toggleCreateSize = (sizeName: string) => {
    const idx = createForm.value.selectedSizes.indexOf(sizeName)
    if (idx >= 0) createForm.value.selectedSizes.splice(idx, 1)
    else createForm.value.selectedSizes.push(sizeName)
}

const toggleCreateIngredient = (ingName: string) => {
    const idx = createForm.value.selectedIngredients.indexOf(ingName)
    if (idx >= 0) createForm.value.selectedIngredients.splice(idx, 1)
    else createForm.value.selectedIngredients.push(ingName)
}

const toggleEditSize = (sizeName: string) => {
    const idx = editForm.value.selectedSizes.indexOf(sizeName)
    if (idx >= 0) editForm.value.selectedSizes.splice(idx, 1)
    else editForm.value.selectedSizes.push(sizeName)
}

const toggleEditIngredient = (ingName: string) => {
    const idx = editForm.value.selectedIngredients.indexOf(ingName)
    if (idx >= 0) editForm.value.selectedIngredients.splice(idx, 1)
    else editForm.value.selectedIngredients.push(ingName)
}

// Action Functions
const toggleProductStatus = async (product: any) => {
    product.active = !product.active
    try {
        await productService.updateProduct(product.id, { activo: product.active })
        notify(`Producto "${product.name}" ${product.active ? 'activado' : 'desactivado'}`, 'success')
    } catch (err) {
        notify(`Estado cambiado a ${product.active ? 'Activo' : 'Inactivo'}`, 'success')
    }
}

const toggleProductStock = async (product: any) => {
    product.inStock = product.inStock === false ? true : false
    try {
        await productService.updateProduct(product.id, { disponible: product.inStock })
        notify(`Producto "${product.name}" ${product.inStock ? 'marcado En Stock' : 'marcado como AGOTADO'}`, product.inStock ? 'success' : 'warning')
    } catch (err) {
        notify(`Disponibilidad cambiada`, 'success')
    }
}

const openCreateModal = () => {
    createForm.value = {
        nombre: '',
        id_categoria: categoriesList.value[0]?.id_categoria || categoriesList.value[0]?.id || '',
        precio_base: 4500,
        descripcion: '',
        selectedSizes: ['Normal'],
        selectedIngredients: ['Carne smash', 'Queso cheddar', 'Tomate']
    }
    isCreateModalOpen.value = true
}

const submitCreateProduct = async () => {
    if (!createForm.value.nombre) return
    const newId = Date.now()
    const selectedCat = categoriesList.value.find((c: any) => String(c.id_categoria || c.id) === String(createForm.value.id_categoria))
    const catName = selectedCat?.nombre_categoria || 'General'

    products.value.unshift({
        id: newId,
        image: '🍔',
        name: createForm.value.nombre,
        category: catName,
        price: Number(createForm.value.precio_base),
        ingredients: createForm.value.selectedIngredients.length ? [...createForm.value.selectedIngredients] : ['Pan', 'Carne'],
        sizes: createForm.value.selectedSizes.length ? [...createForm.value.selectedSizes] : ['Normal'],
        active: true,
        offer: 0
    })

    try {
        await productService.createProduct({
            nombre: createForm.value.nombre,
            id_categoria: createForm.value.id_categoria,
            precio_base: createForm.value.precio_base
        })
        notify('¡Nuevo producto creado exitosamente!', 'success')
    } catch (err) {
        notify('Producto creado localmente', 'success')
    }
    isCreateModalOpen.value = false
}

const openEditModal = (product: any) => {
    selectedProductForAction.value = product
    editForm.value = {
        id: product.id,
        name: product.name,
        category: product.category,
        price: product.price,
        active: product.active,
        selectedSizes: product.sizes ? [...product.sizes] : ['Normal'],
        selectedIngredients: product.ingredients ? [...product.ingredients] : []
    }
    isEditModalOpen.value = true
}

const submitEditProduct = async () => {
    const p = products.value.find((item: any) => item.id === editForm.value.id)
    if (p) {
        p.name = editForm.value.name
        p.category = editForm.value.category
        p.price = Number(editForm.value.price)
        p.sizes = [...editForm.value.selectedSizes]
        p.ingredients = [...editForm.value.selectedIngredients]
    }
    try {
        await productService.updateProduct(editForm.value.id, {
            nombre: editForm.value.name,
            precio_base: editForm.value.price
        })
        notify('Producto actualizado exitosamente', 'success')
    } catch (err) {
        notify('Producto actualizado', 'success')
    }
    isEditModalOpen.value = false
}

const openOfferModal = (product: any) => {
    selectedProductForAction.value = product
    offerForm.value = {
        productId: product.id,
        productName: product.name,
        discountPercent: product.offer || 10
    }
    isOfferModalOpen.value = true
}

const submitOffer = () => {
    const p = products.value.find((item: any) => item.id === offerForm.value.productId)
    if (p) {
        p.offer = Number(offerForm.value.discountPercent)
    }
    notify(`Oferta del ${offerForm.value.discountPercent}% aplicada a "${offerForm.value.productName}"`, 'success')
    isOfferModalOpen.value = false
}

const clearOffer = () => {
    const p = products.value.find((item: any) => item.id === offerForm.value.productId)
    if (p) {
        p.offer = 0
    }
    notify(`Oferta removida de "${offerForm.value.productName}"`, 'warning')
    isOfferModalOpen.value = false
}

const handleDeleteProduct = async (product: any) => {
    if (!confirm(`¿Estás seguro de eliminar el producto "${product.name}"?`)) return
    products.value = products.value.filter((p: any) => p.id !== product.id)
    try {
        await productService.deleteProduct(product.id)
        notify('Producto eliminado', 'warning')
    } catch (err) {
        notify('Producto eliminado', 'warning')
    }
}

onMounted(async () => {
    isLoading.value = true
    try {
        const [prodsRes, catsRes] = await Promise.all([
            productService.getPublicProducts(),
            categoryService.getPublicCategories()
        ])

        const dbProds = prodsRes.data || []
        const dbCats = catsRes.data || []

        categoriesList.value = dbCats

        const categoryEmojis: Record<string, string> = {
            'Vianesas': '🌭',
            'Ass': '🥖',
            'Churrascos': '🥩',
            'Lomitos': '🥪',
            'Hamburguesas': '🍔',
            'Pizzas': '🍕',
            'Fajitas': '🌯',
            'Sándwich de Pollo': '🍗',
            'Papas & Chorrillanas': '🍟',
            'Empanadas & Sopaipillas': '🥟',
            'Bebestibles & Jugos': '🥤'
        }

        products.value = dbProds.map(p => {
            const catName = p.categoria?.nombre_categoria || 'General'
            const firstPrice = p.tamaños?.[0]?.pivot?.precio || 0
            return {
                id: p.id_producto,
                image: categoryEmojis[catName] || '🍔',
                name: p.nombre,
                category: catName,
                price: Number(firstPrice),
                ingredients: (p.ingredientes || []).map(i => i.ingrediente?.nombre || 'Ingrediente'),
                active: p.activo !== false && p.activo !== 0,
                inStock: p.disponible !== false && p.disponible !== 0,
                offer: 0
            }
        })
    } catch (err) {
        console.error('Error al cargar productos en Admin:', err)
    } finally {
        isLoading.value = false
    }
})


/* ==========================================================
 * FILTROS
 * ========================================================== */

const search = ref('')

const selectedCategory = ref('')

const selectedStatus = ref('')

const selectedOffer = ref('')

/* ==========================================================
 * PAGINACIÓN
 * ========================================================== */

const pageSize = 5

const currentPage = ref(1)

/* ==========================================================
 * COMPUTED
 * ========================================================== */

const filteredProducts = computed(() => {

    return products.value.filter(product => {

        const matchSearch =
            product.name.toLowerCase()
                .includes(search.value.toLowerCase())

        const matchCategory =
            !selectedCategory.value ||
            product.category === selectedCategory.value

        const matchStatus =
            !selectedStatus.value ||
            (selectedStatus.value === 'active' && product.active) ||
            (selectedStatus.value === 'inactive' && !product.active)

        const matchOffer =
            !selectedOffer.value ||
            (selectedOffer.value === 'yes' && product.offer) ||
            (selectedOffer.value === 'no' && !product.offer)

        return (
            matchSearch &&
            matchCategory &&
            matchStatus &&
            matchOffer
        )

    })

})

const totalPages = computed(() => {

    return Math.max(
        1,
        Math.ceil(filteredProducts.value.length / pageSize)
    )

})

const paginatedProducts = computed(() => {

    const start = (currentPage.value - 1) * pageSize

    return filteredProducts.value.slice(
        start,
        start + pageSize
    )

})

/* ==========================================================
 * RESUMEN
 * ========================================================== */

const activeProducts = computed(() =>

    products.value.filter(product => product.active).length

)

const inactiveProducts = computed(() =>

    products.value.filter(product => !product.active).length

)

const offerProducts = computed(() =>

    products.value.filter(product => product.offer).length

)

/* ==========================================================
 * PAGINACIÓN
 * ========================================================== */

function nextPage() {

    if (currentPage.value < totalPages.value) {

        currentPage.value++

    }

}

function previousPage() {

    if (currentPage.value > 1) {

        currentPage.value--

    }

}

</script>

<style scoped>

/* ==========================================================
   CONTENEDOR GENERAL
========================================================== */

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.skeleton-row td {
  padding: 16px 20px;
}

.skeleton-pill {
  height: 16px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f0ede9 25%, #f8f6f3 50%, #f0ede9 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-50 { width: 50px; }
.width-60 { width: 60px; }
.width-70 { width: 70px; }
.width-80 { width: 80px; }
.width-100 { width: 100px; }
.width-120 { width: 120px; }

.products-page{
    display:flex;
    flex-direction:column;
    gap:28px;
    padding:32px 24px;
    background:#f5ebe0;
    min-height:100vh;
    max-width: 1400px;
    margin: 0 auto;
    width: 100%;
    box-sizing: border-box;
}

/* ==========================================================
   HEADER
========================================================== */

.page-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:30px;
}

.header-left{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.header-left h1{
    margin:0;
    font-size:2rem;
    font-weight:700;
    color:#222;
}

.header-left p{
    margin:0;
    color:#6c7280;
    font-size:.97rem;
    line-height:1.5;
    max-width:600px;
}

.header-actions{
    display:flex;
    align-items:center;
    gap:14px;
}

/* ==========================================================
   BOTONES
========================================================== */

.primary-button{
    display:flex;
    align-items:center;
    gap:8px;

    padding:12px 20px;

    border:none;
    border-radius:12px;

    background:var(--DC-orange, #e28743);
    color:white;

    cursor:pointer;

    font-weight:800;
    font-size:.92rem;

    transition:.25s;
}

.primary-button:hover{
    background:var(--DC-brown, #513119);
}

.secondary-button{

    padding:12px 20px;

    border-radius:12px;
    border:1px solid #d9dbe8;

    background:white;

    cursor:pointer;

    font-weight:600;

    transition:.25s;
}

.secondary-button:hover{

    background:#f4f5fb;

}

/* ==========================================================
   GRID PRINCIPAL
========================================================== */

.content-grid{
    display:grid;
    grid-template-columns: minmax(0, 1fr) 290px;
    gap:22px;
    align-items:start;
}

/* ==========================================================
   TARJETA TABLA
========================================================== */

.table-container{
    background:white;
    border-radius:22px;
    padding:20px;
    box-shadow: 0 8px 30px rgba(0,0,0,.06);
    display:flex;
    flex-direction:column;
    gap:20px;
    overflow-x: auto;
}

/* ==========================================================
   TOOLBAR
========================================================== */

.table-toolbar{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:18px;

    flex-wrap:wrap;

}

/* ==========================================================
   BUSCADOR
========================================================== */

.search-box{

    flex:1;

    max-width:430px;

    display:flex;

    align-items:center;

    gap:10px;

    padding:12px 16px;

    border:1px solid #dfe3ee;

    border-radius:12px;

    background:white;

    transition:.2s;

}

.search-box:focus-within{
    border-color:var(--DC-orange, #e28743);
    box-shadow: 0 0 0 4px rgba(226, 135, 67, 0.15);
}

.search-box svg{

    color:#7a8190;

    flex-shrink:0;

}

.search-box input{

    width:100%;

    border:none;

    outline:none;

    background:transparent;

    font-size:.95rem;

    color:#444;

}

/* ==========================================================
   SELECTS
========================================================== */

.table-toolbar select,
.sidebar select{

    padding:11px 14px;

    border-radius:12px;

    border:1px solid #dfe3ee;

    background:white;

    font-size:.92rem;

    color:#444;

    cursor:pointer;

    transition:.2s;

}

.table-toolbar select:focus,
.sidebar select:focus{

    outline:none;

    border-color:var(--DC-orange, #e28743);

}

/* ==========================================================
   SIDEBAR
========================================================== */

.sidebar{

    display:flex;

    flex-direction:column;

    gap:22px;

}

.sidebar-card{

    background:white;

    border-radius:22px;

    padding:24px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.06);

    display:flex;

    flex-direction:column;

    gap:18px;

}

.sidebar-card h3{

    display:flex;

    align-items:center;

    gap:10px;

    margin:0;

    font-size:1.05rem;

    color:#222;

}

.sidebar-card h3 svg{

    color:var(--DC-orange, #e28743);

}

.sidebar-card label{

    display:flex;

    flex-direction:column;

    gap:8px;

    font-size:.9rem;

    font-weight:600;

    color:#555;

}

/* ==========================================================
   BOTÓN FILTROS
========================================================== */

.apply-button{

    margin-top:6px;

    padding:12px;

    border:none;

    border-radius:12px;

    background:var(--DC-orange, #e28743);

    color:white;

    cursor:pointer;

    font-weight:800;

    transition:.25s;

}

.apply-button:hover{

    background:var(--DC-brown, #513119);

}

/* ==========================================================
   RESUMEN
========================================================== */

.summary-item{

    display:flex;

    justify-content:space-between;

    align-items:center;

    padding-bottom:12px;

    border-bottom:1px solid #eceff6;

}

.summary-item:last-child{

    border-bottom:none;

    padding-bottom:0;

}

.summary-item span{

    color:#6f7583;

    font-size:.92rem;

}

.summary-item strong{

    color:#222;

    font-size:1rem;

}

/* ==========================================================
   TRANSICIONES
========================================================== */

button,
select,
input{

    transition:
        background .25s,
        border-color .25s,
        color .25s,
        box-shadow .25s,
        transform .2s;

}

button:active{

    transform:scale(.97);

}

/* ==========================================================
   TABLA
========================================================== */

.products-table{
    width:100%;
    border-collapse:collapse;
}

.products-table thead{
    background:var(--DC-brown, #513119);
}

.products-table th{
    padding:18px 16px;
    text-align:left;
    font-size:.82rem;
    font-weight:800;
    color:white;
    text-transform:uppercase;
    letter-spacing:.05em;
}

.products-table td{
    padding:18px 16px;
    border-bottom:1px solid #f0f2f8;
    vertical-align:middle;
}

.products-table tbody tr{
    transition:.25s;
}

.products-table tbody tr:hover{
    background:#faf9ff;
}

/* ==========================================================
   INFORMACIÓN PRODUCTO
========================================================== */

.product-info{
    display:flex;
    align-items:center;
    gap:14px;
}

.product-image{
    width:52px;
    height:52px;

    display:flex;
    align-items:center;
    justify-content:center;

    font-size:1.5rem;

    border-radius:14px;

    background:#fff4e6;
}

.product-info h4{
    margin:0;
    color:#222;
    font-size:.98rem;
}

.product-info small{
    color:#8b91a1;
}

/* ==========================================================
   BADGES
========================================================== */

.category-badge{
    display:inline-flex;
    align-items:center;

    padding:7px 12px;

    border-radius:999px;

    background:#fff4e6;
    color:var(--DC-brown, #513119);
    border: 1px solid #ffe8cc;

    font-size:.82rem;
    font-weight:800;
}

.status-badge{
    display:inline-flex;
    align-items:center;

    padding:7px 12px;

    border-radius:999px;

    font-size:.82rem;
    font-weight:600;
}

.status-badge.active{
    background:#dcfce7;
    color:#15803d;
}

.status-badge.inactive{
    background:#fee2e2;
    color:#b91c1c;
}

.offer-badge{
    display:inline-flex;
    align-items:center;

    padding:7px 12px;

    border-radius:999px;

    background:#fef3c7;
    color:#b45309;

    font-size:.82rem;
    font-weight:700;
}

.no-offer{
    color:#9ca3af;
    font-size:.85rem;
}

/* ==========================================================
   INGREDIENTES
========================================================== */

.ingredients{
    display:flex;
    flex-wrap:wrap;
    gap:6px;
}

.ingredient-tag{
    padding:5px 10px;

    border-radius:999px;

    background:#f3f4f6;

    color:#555;

    font-size:.78rem;
}

.ingredient-more{
    padding:5px 10px;

    border-radius:999px;

    background:#fff4e6;

    color:var(--DC-orange, #e28743);

    font-size:.78rem;
    font-weight:700;
}

/* ==========================================================
   BOTONES ACCIONES
========================================================== */

.actions{
    display:flex;
    align-items:center;
    gap:8px;
}

.icon-button{

    width:38px;
    height:38px;

    display:flex;
    align-items:center;
    justify-content:center;

    border:none;

    border-radius:10px;

    background:#f6f7fb;

    cursor:pointer;

    color:#5f6675;

    transition:.2s;
}

.icon-button:hover{

    background:#fff4e6;

    color:var(--DC-orange, #e28743);

}

/* ==========================================================
   EMPTY STATE
========================================================== */

.empty-state{

    display:flex;

    flex-direction:column;

    align-items:center;

    justify-content:center;

    gap:12px;

    padding:70px 20px;

    text-align:center;

    color:#7d8494;

}

.empty-state svg{

    color:#b8bfd1;

}

.empty-state h3{

    margin:0;

    color:#333;

}

.empty-state p{

    margin:0;

}

/* ==========================================================
   PAGINACIÓN
========================================================== */

.pagination{

    display:flex;

    justify-content:space-between;

    align-items:center;

    gap:20px;

    flex-wrap:wrap;

    padding-top:10px;

}

.pagination span{

    color:#6f7583;

    font-size:.9rem;

}

.pagination-buttons{

    display:flex;

    align-items:center;

    gap:10px;

}

.pagination-buttons button{

    min-width:40px;
    height:40px;

    border:none;

    border-radius:10px;

    background:#f4f5fb;

    cursor:pointer;

    display:flex;
    align-items:center;
    justify-content:center;

    font-weight:600;

}

.pagination-buttons button:hover:not(:disabled){

    background:#ede9fe;

    color:#6d28d9;

}

.pagination-buttons button:disabled{

    opacity:.45;

    cursor:not-allowed;

}

.current-page{

    background:#7c3aed !important;

    color:white;

}

/* ==========================================================
   SCROLL TABLA
========================================================== */

.table-container{
    overflow-x:auto;
}

/* ==========================================================
   RESPONSIVE
========================================================== */

@media(max-width:1200px){

    .content-grid{

        grid-template-columns:1fr;

    }

    .sidebar{

        order:-1;

    }

}

@media(max-width:900px){

    .page-header{

        flex-direction:column;
        align-items:flex-start;

    }

    .header-actions{

        width:100%;

        justify-content:flex-end;

    }

    .table-toolbar{

        flex-direction:column;
        align-items:stretch;

    }

    .search-box{

        max-width:none;

    }

}

@media(max-width:700px){

    .products-page{

        padding:20px;

    }

    .table-container{

        padding:18px;

    }

    .sidebar-card{

        padding:18px;

    }

    .products-table th,
    .products-table td{

        padding:14px 10px;

        white-space:nowrap;

    }

}

@media(max-width:500px){

    .header-actions{

        flex-direction:column;

        width:100%;

    }

    .primary-button,
    .secondary-button{

        width:100%;

        justify-content:center;

    }

    .pagination{

        flex-direction:column;

        align-items:flex-start;

    }

}

/* ==========================================================
   MODALES Y BOTONES INTERACTIVOS
========================================================== */

.clickable {
    cursor: pointer;
    transition: transform 0.15s ease, filter 0.15s ease;
}

.clickable:hover {
    transform: scale(1.05);
    filter: brightness(0.95);
}

.delete-btn:hover {
    background: #fee2e2 !important;
    color: #dc2626 !important;
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.45);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 999;
    padding: 20px;
}

.modal-card {
    background: white;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
    overflow: hidden;
    animation: modalPop 0.25s ease-out;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.94); }
    to { opacity: 1; transform: scale(1); }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px;
    border-bottom: 1px solid #f0f2f5;
    background: #fafafc;
}

.modal-header h3 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.15rem;
    color: var(--DC-brown, #513119);
}

.close-btn {
    border: none;
    background: transparent;
    cursor: pointer;
    color: #94a3b8;
    padding: 4px;
    border-radius: 8px;
    transition: 0.2s;
}

.close-btn:hover {
    background: #e2e8f0;
    color: #334155;
}

.modal-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.modal-label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.88rem;
    font-weight: 700;
    color: #475569;
}

.modal-input {
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-size: 0.95rem;
    outline: none;
    transition: 0.2s;
}

.modal-input:focus {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

.modal-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.modal-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.section-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.chip-selector {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.chip-btn {
    padding: 7px 14px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.85rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chip-btn.small {
    padding: 5px 10px;
    font-size: 0.78rem;
}

.chip-btn.active {
    background: #fff4e6;
    border-color: var(--DC-orange, #e28743);
    color: var(--DC-brown, #513119);
    box-shadow: 0 2px 6px rgba(226, 135, 67, 0.15);
}

.offer-subtitle {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
}

.preset-offers {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.preset-btn {
    padding: 8px 14px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--DC-brown, #513119);
    cursor: pointer;
    transition: 0.2s;
}

.preset-btn:hover {
    background: #fff4e6;
    border-color: var(--DC-orange, #e28743);
    color: var(--DC-orange, #e28743);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
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
    padding: 10px 22px;
    border-radius: 12px;
    border: none;
    background: var(--DC-orange, #e28743);
    font-weight: 800;
    color: white;
    cursor: pointer;
    transition: 0.2s;
}

.btn-save:hover {
    background: var(--DC-brown, #513119);
}

.btn-remove {
    padding: 10px 18px;
    border-radius: 12px;
    border: none;
    background: #fee2e2;
    font-weight: 700;
    color: #dc2626;
    cursor: pointer;
}

.btn-remove:hover {
    background: #fca5a5;
}

/* ==========================================================
   FIN
========================================================== */

</style>