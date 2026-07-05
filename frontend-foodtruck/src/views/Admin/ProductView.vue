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

                <button class="primary-button">
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
                        <option value="Completos">Completos</option>
                        <option value="Hamburguesas">Hamburguesas</option>
                        <option value="Bebidas">Bebidas</option>
                        <option value="Acompañamientos">
                            Acompañamientos
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

                    <tbody>

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
                                        v-for="ingredient in product.ingredients.slice(0,3)"
                                        :key="ingredient"
                                        class="ingredient-tag"
                                    >

                                        {{ ingredient }}

                                    </span>

                                    <span
                                        v-if="product.ingredients.length > 3"
                                        class="ingredient-more"
                                    >

                                        +{{ product.ingredients.length - 3 }}

                                    </span>

                                </div>

                            </td>

                            <!-- Estado -->

                            <td>

                                <span
                                    class="status-badge"
                                    :class="product.active ? 'active' : 'inactive'"
                                >

                                    {{ product.active ? 'Activo' : 'Inactivo' }}

                                </span>

                            </td>

                            <!-- Oferta -->

                            <td>

                                <span
                                    v-if="product.offer"
                                    class="offer-badge"
                                >

                                    {{ product.offer }}%

                                </span>

                                <span
                                    v-else
                                    class="no-offer"
                                >

                                    Sin oferta

                                </span>

                            </td>

                            <!-- Acciones -->

                            <td>

                                <div class="actions">

                                    <button
                                        class="icon-button"
                                        title="Editar"
                                    >
                                        <Pencil :size="17" />
                                    </button>

                                    <button
                                        class="icon-button"
                                        title="Oferta"
                                    >
                                        <BadgePercent :size="17" />
                                    </button>

                                    <button
                                        class="icon-button"
                                        title="Más opciones"
                                    >
                                        <MoreVertical :size="17" />
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

                        <button>
                            <ChevronLeft :size="18" />
                        </button>

                        <button class="current-page">
                            {{ currentPage }}
                        </button>

                        <button>
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

    </div>

</template>

<script setup>

import { computed, ref } from 'vue'

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
    Search
} from 'lucide-vue-next'

/* ==========================================================
 * DATOS DUMMY
 * ========================================================== */

const products = ref([
    {
        id: 1,
        image: '🌭',
        name: 'Completo Italiano',
        category: 'Completos',
        price: 3500,
        ingredients: [
            'Pan',
            'Vienesa',
            'Tomate',
            'Palta',
            'Mayonesa'
        ],
        active: true,
        offer: 10
    },
    {
        id: 2,
        image: '🍔',
        name: 'Hamburguesa Clásica',
        category: 'Hamburguesas',
        price: 4200,
        ingredients: [
            'Carne',
            'Queso',
            'Lechuga',
            'Tomate',
            'Pepinillo'
        ],
        active: true,
        offer: null
    },
    {
        id: 3,
        image: '🍟',
        name: 'Papas Fritas',
        category: 'Acompañamientos',
        price: 1800,
        ingredients: [
            'Papa',
            'Sal'
        ],
        active: false,
        offer: null
    },
    {
        id: 4,
        image: '🥤',
        name: 'Limonada Menta',
        category: 'Bebidas',
        price: 2000,
        ingredients: [
            'Limón',
            'Menta',
            'Azúcar'
        ],
        active: true,
        offer: 15
    },
    {
        id: 5,
        image: '🌭',
        name: 'Completo Dinámico',
        category: 'Completos',
        price: 3900,
        ingredients: [
            'Pan',
            'Vienesa',
            'Queso',
            'Tomate',
            'Palta',
            'Tocino'
        ],
        active: true,
        offer: null
    },
    {
        id: 6,
        image: '🥤',
        name: 'Bebida 500cc',
        category: 'Bebidas',
        price: 1700,
        ingredients: [
            'Bebida'
        ],
        active: true,
        offer: null
    },
    {
        id: 7,
        image: '🍔',
        name: 'Hamburguesa BBQ',
        category: 'Hamburguesas',
        price: 5200,
        ingredients: [
            'Carne',
            'Queso',
            'BBQ',
            'Cebolla',
            'Tocino'
        ],
        active: false,
        offer: 20
    }
])

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

.products-page{
    display:flex;
    flex-direction:column;
    gap:28px;
    padding:32px;
    background:#f7f8fc;
    min-height:100vh;
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

    background:#7c3aed;
    color:white;

    cursor:pointer;

    font-weight:600;
    font-size:.92rem;

    transition:.25s;
}

.primary-button:hover{
    background:#6d28d9;
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

    grid-template-columns:
        minmax(0,3fr)
        320px;

    gap:26px;

    align-items:start;

}

/* ==========================================================
   TARJETA TABLA
========================================================== */

.table-container{

    background:white;

    border-radius:22px;

    padding:26px;

    box-shadow:
        0 8px 30px rgba(0,0,0,.06);

    display:flex;

    flex-direction:column;

    gap:24px;

    overflow:hidden;

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

    border-color:#7c3aed;

    box-shadow:
        0 0 0 4px
        rgba(124,58,237,.12);

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

    border-color:#7c3aed;

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

    color:#7c3aed;

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

    background:#7c3aed;

    color:white;

    cursor:pointer;

    font-weight:600;

    transition:.25s;

}

.apply-button:hover{

    background:#6d28d9;

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
    background:#fafbfe;
}

.products-table th{
    padding:18px 16px;
    text-align:left;
    font-size:.82rem;
    font-weight:700;
    color:#6f7583;
    text-transform:uppercase;
    letter-spacing:.05em;
    border-bottom:1px solid #eceff6;
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

    background:#f4f0ff;
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

    background:#ede9fe;
    color:#6d28d9;

    font-size:.82rem;
    font-weight:600;
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

    background:#ede9fe;

    color:#6d28d9;

    font-size:.78rem;
    font-weight:600;
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

    background:#ede9fe;

    color:#6d28d9;

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
   FIN
========================================================== */

</style>