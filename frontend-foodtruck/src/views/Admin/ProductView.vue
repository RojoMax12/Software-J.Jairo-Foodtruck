<template>
    <div class="products-page">

        <!-- ===================== HEADER DINÁMICO ===================== -->

        <section class="page-header">

            <div class="header-left">
                <div class="title-with-badges">
                    <h1>Gestión de Catálogo</h1>
                    <div class="header-stat-pills" v-if="activeTab === 'products'">
                        <span class="stat-pill"><strong>{{ products.length }}</strong> totales</span>
                        <span class="stat-pill stat-active"><span class="dot dot-green"></span> <strong>{{ activeProducts }}</strong> activos</span>
                        <span class="stat-pill stat-inactive"><span class="dot dot-gray"></span> <strong>{{ inactiveProducts }}</strong> inactivos</span>
                        <span class="stat-pill stat-offer" v-if="offerProducts > 0"><span class="dot dot-orange"></span> <strong>{{ offerProducts }}</strong> en oferta</span>
                    </div>
                    <div class="header-stat-pills" v-else-if="activeTab === 'categories'">
                        <span class="stat-pill"><strong>{{ categoriesList.length }}</strong> categorías</span>
                        <span class="stat-pill stat-active"><span class="dot dot-green"></span> <strong>{{ totalCategorizedProducts }}</strong> productos asociados</span>
                    </div>
                    <div class="header-stat-pills" v-else-if="activeTab === 'sizes'">
                        <span class="stat-pill"><strong>{{ sizesList.length }}</strong> formatos y tamaños</span>
                    </div>
                </div>
                <p>
                    Administra integralmente la carta gastronómica: productos, categorías y formatos de tamaños.
                </p>
            </div>

            <div class="header-actions">
                <button class="secondary-button" @click="goToAudit" title="Ver auditoría completa de catálogo">
                    <History :size="16" />
                    <span>Ver Auditoría</span>
                </button>
                <button v-if="activeTab === 'products'" class="primary-button" @click="openCreateModal">
                    <Plus :size="18" />
                    <span>Nuevo Producto</span>
                </button>
                <button v-else-if="activeTab === 'categories'" class="primary-button" @click="openCreateCategoryModal">
                    <Plus :size="18" />
                    <span>Nueva Categoría</span>
                </button>
                <button v-else-if="activeTab === 'sizes'" class="primary-button" @click="openCreateSizeModal">
                    <Plus :size="18" />
                    <span>Nuevo Formato / Tamaño</span>
                </button>
            </div>

        </section>

        <!-- ===================== TABS DE NAVEGACIÓN DIRECTA ===================== -->
        <div class="catalog-tabs-nav">
            <button 
                class="tab-nav-btn" 
                :class="{ active: activeTab === 'products' }" 
                @click="activeTab = 'products'"
            >
                <PackageOpen :size="17" />
                <span>Productos</span>
                <span class="tab-pill">{{ products.length }}</span>
            </button>

            <button 
                class="tab-nav-btn" 
                :class="{ active: activeTab === 'categories' }" 
                @click="activeTab = 'categories'"
            >
                <FolderTree :size="17" />
                <span>Categorías</span>
                <span class="tab-pill">{{ categoriesList.length }}</span>
            </button>

            <button 
                class="tab-nav-btn" 
                :class="{ active: activeTab === 'sizes' }" 
                @click="activeTab = 'sizes'"
            >
                <Tag :size="17" />
                <span>Tamaños</span>
                <span class="tab-pill">{{ sizesList.length }}</span>
            </button>
        </div>

        <!-- ===================== TAB 1: PRODUCTOS ===================== -->
        <section v-if="activeTab === 'products'" class="table-container">

            <!-- Toolbar con filtros compactos integrados -->
            <div class="table-toolbar">

                <div class="search-box">
                    <Search :size="17" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o ingrediente..."
                    >
                    <button v-if="search" class="clear-search-btn" @click="search = ''" title="Borrar búsqueda">
                        <X :size="14" />
                    </button>
                </div>

                <div class="filters-inline">
                    <div class="filter-item">
                        <select v-model="selectedCategory" class="filter-select">
                            <option value="">Todas las categorías</option>
                            <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.nombre_categoria">
                                {{ cat.nombre_categoria }}
                            </option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <select v-model="selectedStatus" class="filter-select">
                            <option value="">Todos los estados</option>
                            <option value="active">Solo activos</option>
                            <option value="inactive">Solo inactivos</option>
                        </select>
                    </div>

                    <div class="filter-item">
                        <select v-model="selectedOffer" class="filter-select">
                            <option value="">Todas las ofertas</option>
                            <option value="yes">Con oferta</option>
                            <option value="no">Sin oferta</option>
                        </select>
                    </div>

                    <button
                        v-if="hasActiveFilters"
                        class="btn-clear-filters"
                        @click="resetFilters"
                        title="Restablecer todos los filtros"
                    >
                        <RotateCcw :size="14" />
                        <span>Limpiar</span>
                    </button>
                </div>

            </div>

                <!-- Tabla -->

                <table class="products-table desktop-table-only">
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
                                    <div class="product-image-container">
                                        <img :src="product.image" :alt="product.name" class="product-thumb-img" />
                                    </div>
                                    <div>
                                        <h4>{{ product.name }}</h4>
                                        <small>ID #{{ product.id }}</small>
                                    </div>
                                </div>
                            </td>
                            <!-- Categoría -->
                            <td>
                                <span class="category-badge">{{ product.category }}</span>
                            </td>
                            <!-- Precio -->
                            <td>
                                <strong>${{ product.price.toLocaleString() }}</strong>
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
                                        :class="{ 'already-inactive': !product.active }"
                                        :title="product.active ? 'Desactivar producto de la carta' : 'Producto ya inactivo'"
                                        @click="handleDeleteProduct(product)"
                                    >
                                        <Trash2 :size="17" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- VISTA TARJETAS MÓVIL (MOBILE) -->
                <div class="mobile-products-cards mobile-only">
                    <div v-if="isLoading" class="skeleton-cards-mobile">
                        <div v-for="n in 3" :key="'mob-prod-skel-' + n" class="mobile-product-card skeleton-card">
                            <div class="skeleton-pill width-120"></div>
                            <div class="skeleton-pill width-80"></div>
                        </div>
                    </div>
                    <div v-else-if="paginatedProducts.length === 0" class="empty-state">
                        <PackageOpen :size="45" />
                        <h3>No se encontraron productos</h3>
                    </div>
                    <div v-else v-for="product in paginatedProducts" :key="'mob-prod-' + product.id" class="mobile-product-card">
                        <div class="mob-card-header">
                            <div class="mob-prod-info">
                                <div class="mob-thumb-container">
                                    <img :src="product.image" :alt="product.name" class="mob-thumb-img" />
                                </div>
                                <div>
                                    <h4 class="mob-prod-name">{{ product.name }}</h4>
                                    <small class="mob-prod-id">ID #{{ product.id }} · {{ product.category }}</small>
                                </div>
                            </div>
                            <strong class="mob-prod-price">${{ product.price.toLocaleString('es-CL') }}</strong>
                        </div>
                        <div class="mob-card-body">
                            <div class="mob-status-row">
                                <span
                                    class="status-badge clickable"
                                    :class="product.active ? 'active' : 'inactive'"
                                    @click="toggleProductStatus(product)"
                                >
                                    {{ product.active ? 'Activo' : 'Inactivo' }}
                                </span>
                                <span
                                    v-if="product.offer"
                                    class="offer-badge clickable"
                                    @click="openOfferModal(product)"
                                >
                                    {{ product.offer }}% OFF
                                </span>
                            </div>
                            <div class="actions">
                                <button class="icon-button" title="Editar" @click="openEditModal(product)">
                                    <Pencil :size="16" />
                                </button>
                                <button class="icon-button" title="Oferta" @click="openOfferModal(product)">
                                    <BadgePercent :size="16" />
                                </button>
                                <button 
                                    class="icon-button delete-btn" 
                                    :class="{ 'already-inactive': !product.active }"
                                    :title="product.active ? 'Desactivar producto de la carta' : 'Producto ya inactivo'" 
                                    @click="handleDeleteProduct(product)"
                                >
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sin resultados Desktop -->
                <div
                    v-if="!isLoading && paginatedProducts.length === 0"
                    class="empty-state desktop-table-only"
                >
                    <PackageOpen :size="55" />
                    <h3>No se encontraron productos</h3>
                    <p>Intenta modificar los filtros de búsqueda.</p>
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

        <!-- ===================== TAB 2: CATEGORÍAS ===================== -->
        <section v-if="activeTab === 'categories'" class="table-container">
            <div class="table-toolbar">
                <div class="search-box">
                    <Search :size="17" />
                    <input
                        v-model="categorySearch"
                        type="text"
                        placeholder="Buscar categoría..."
                    >
                    <button v-if="categorySearch" class="clear-search-btn" @click="categorySearch = ''">
                        <X :size="14" />
                    </button>
                </div>
                <div class="filters-inline">
                    <button class="primary-button compact-btn" @click="openCreateCategoryModal">
                        <Plus :size="16" />
                        <span>Nueva Categoría</span>
                    </button>
                </div>
            </div>

            <div class="catalog-cards-grid" v-if="filteredCategories.length > 0">
                <div 
                    v-for="cat in filteredCategories" 
                    :key="cat.id_categoria || cat.id" 
                    class="catalog-entity-card"
                >
                    <div class="entity-card-top">
                        <div class="entity-icon-pill cat-pill">
                            <FolderTree :size="22" />
                        </div>
                        <div class="entity-card-info">
                            <h3 class="entity-title">{{ cat.nombre_categoria }}</h3>
                            <span class="entity-badge cat-badge">
                                {{ getProductsCountByCategory(cat.nombre_categoria) }} productos en menú
                            </span>
                        </div>
                    </div>
                    <p class="entity-desc">{{ cat.descripcion_categoria || 'Categoría disponible para organizar la carta gastronómica.' }}</p>
                    <div class="entity-card-actions">
                        <button class="btn-entity-action edit" @click="openEditCategoryModal(cat)" title="Editar categoría">
                            <Pencil :size="14" />
                            <span>Editar</span>
                        </button>
                        <button class="btn-entity-action delete" @click="handleDeleteCategory(cat)" title="Eliminar categoría">
                            <Trash2 :size="14" />
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="empty-state-box">
                <FolderTree :size="44" />
                <h3>No se encontraron categorías</h3>
                <p>Crea tu primera categoría para organizar la carta gastronómica.</p>
            </div>
        </section>

        <!-- ===================== TAB 3: TAMAÑOS ===================== -->
        <section v-if="activeTab === 'sizes'" class="table-container">
            <div class="table-toolbar">
                <div class="search-box">
                    <Search :size="17" />
                    <input
                        v-model="sizeSearch"
                        type="text"
                        placeholder="Buscar tamaño o formato..."
                    >
                    <button v-if="sizeSearch" class="clear-search-btn" @click="sizeSearch = ''">
                        <X :size="14" />
                    </button>
                </div>
                <div class="filters-inline">
                    <button class="primary-button compact-btn" @click="openCreateSizeModal">
                        <Plus :size="16" />
                        <span>Nuevo Tamaño</span>
                    </button>
                </div>
            </div>

            <div class="catalog-cards-grid" v-if="filteredSizes.length > 0">
                <div 
                    v-for="sz in filteredSizes" 
                    :key="sz.id_tamaño || sz.id || sz.nombre" 
                    class="catalog-entity-card size-card"
                >
                    <div class="entity-card-top">
                        <div class="entity-icon-pill size-pill">
                            <Tag :size="22" />
                        </div>
                        <div class="entity-card-info">
                            <h3 class="entity-title">{{ sz.nombre }}</h3>
                            <span class="entity-badge size-badge">
                                {{ getProductsCountBySize(sz.nombre) }} productos usan este formato
                            </span>
                        </div>
                    </div>
                    <p class="entity-desc">{{ sz.descripcion || 'Formato de tamaño seleccionable en la creación y edición de productos.' }}</p>
                    <div class="entity-card-actions">
                        <button class="btn-entity-action edit" @click="openEditSizeModal(sz)" title="Editar tamaño">
                            <Pencil :size="14" />
                            <span>Editar</span>
                        </button>
                        <button class="btn-entity-action delete" @click="handleDeleteSize(sz)" title="Eliminar tamaño">
                            <Trash2 :size="14" />
                            <span>Eliminar</span>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else class="empty-state-box">
                <Tag :size="44" />
                <h3>No se encontraron tamaños</h3>
                <p>Agrega tamaños para ofrecer distintos formatos a tus clientes.</p>
            </div>
        </section>

        <!-- MODAL CREAR / EDITAR CATEGORÍA -->
        <div v-if="isCategoryModalOpen" class="modal-backdrop" @click.self="isCategoryModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><FolderTree :size="18" /></div>
                        <div>
                            <h3>{{ isEditingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}</h3>
                            <p class="modal-header-desc">Organiza tu carta gastronómica por secciones</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isCategoryModalOpen = false"><X :size="20" /></button>
                </div>

                <form class="modal-body" @submit.prevent="submitCategoryForm">
                    <label class="modal-label">
                        <span>Nombre de la Categoría <span class="required">*</span></span>
                        <input
                            v-model="categoryForm.nombre_categoria"
                            type="text"
                            required
                            placeholder="Ej: Hamburguesas Smash, Chorrillanas, Bebidas..."
                            class="modal-input"
                        />
                    </label>

                    <label class="modal-label">
                        <span>Descripción</span>
                        <textarea
                            v-model="categoryForm.descripcion_categoria"
                            rows="3"
                            placeholder="Breve detalle de los productos incluidos en esta categoría..."
                            class="modal-input"
                        ></textarea>
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isCategoryModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <Check :size="16" />
                            <span>{{ isEditingCategory ? 'Guardar Cambios' : 'Crear Categoría' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL CREAR / EDITAR TAMAÑO -->
        <div v-if="isSizeModalOpen" class="modal-backdrop" @click.self="isSizeModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><Tag :size="18" /></div>
                        <div>
                            <h3>{{ isEditingSize ? 'Editar Tamaño' : 'Nuevo Tamaño' }}</h3>
                            <p class="modal-header-desc">Define las porciones o formatos disponibles en la carta</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isSizeModalOpen = false"><X :size="20" /></button>
                </div>

                <form class="modal-body" @submit.prevent="submitSizeForm">
                    <label class="modal-label">
                        <span>Nombre del Tamaño <span class="required">*</span></span>
                        <input
                            v-model="sizeForm.nombre"
                            type="text"
                            required
                            placeholder="Ej: Normal, Doble, XL, Familiar, Individual, 1/2 Litro..."
                            class="modal-input"
                        />
                    </label>

                    <label class="modal-label">
                        <span>Descripción / Nota</span>
                        <input
                            v-model="sizeForm.descripcion"
                            type="text"
                            placeholder="Ej: Porción estándar individual con papas"
                            class="modal-input"
                        />
                    </label>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isSizeModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <Check :size="16" />
                            <span>{{ isEditingSize ? 'Guardar Cambios' : 'Crear Tamaño' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================
             MODAL PRODUCTO (CREAR / EDITAR)
        ========================================================= -->
        <div v-if="isProductModalOpen" class="modal-backdrop" @click.self="isProductModalOpen = false">
            <div class="modal-card modal-card-wide">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><Plus :size="18" /></div>
                        <div>
                            <h3>{{ isEditingProduct ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h3>
                            <p class="modal-header-desc">Configura precios por formato, receta base, recargos y fotografía</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isProductModalOpen = false"><X :size="20" /></button>
                </div>

                <form class="modal-form-wrapper" @submit.prevent="isEditingProduct ? submitEditProduct() : submitCreateProduct()">
                    <div class="modal-columns-grid">
                        <!-- Columna Izquierda: Configuración del Producto -->
                        <div class="modal-form-col">
                            <!-- 1. Datos Principales -->
                            <div class="modal-section-group">
                                <span class="group-legend"><Sparkles :size="15" /> 1. Datos Principales</span>
                                
                                <label class="modal-label">
                                    <span>Nombre del Producto <span class="required">*</span></span>
                                    <input
                                        v-model="productForm.nombre"
                                        type="text"
                                        required
                                        placeholder="Ej: Hamburguesa Doble Cheddar Bacon"
                                        class="modal-input"
                                    />
                                </label>

                                <div class="modal-row">
                                    <label class="modal-label">
                                        <span>Categoría <span class="required">*</span></span>
                                        <select v-model="productForm.id_categoria" required class="modal-input">
                                            <option value="" disabled>Selecciona una categoría</option>
                                            <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.id_categoria || cat.id">
                                                {{ cat.nombre_categoria }}
                                            </option>
                                        </select>
                                    </label>

                                    <label class="modal-label">
                                        <span>Tipo de Preparación</span>
                                        <select v-model="productForm.tipo_armado" class="modal-input">
                                            <option value="estandar">🍔 Estándar (Receta frita/armada)</option>
                                            <option value="personalizable">✨ Personalizable (Al gusto)</option>
                                            <option value="combo">🍟 Combo (Con papas + bebida)</option>
                                        </select>
                                    </label>
                                </div>

                                <label class="modal-label">
                                    <span>Descripción del Producto <span class="required">*</span></span>
                                    <textarea
                                        v-model="productForm.descripcion"
                                        rows="2"
                                        required
                                        placeholder="Descripción detallada de los ingredientes y receta para el menú..."
                                        class="modal-input"
                                    ></textarea>
                                </label>
                            </div>

                            <!-- 2. Formatos y Precios por Tamaño -->
                            <div class="modal-section-group">
                                <span class="group-legend"><Tag :size="15" /> 2. Formatos y Precios Diferenciados</span>
                                <p class="group-help">Selecciona los tamaños disponibles y asigna el precio exacto de cada uno:</p>
                                
                                <div class="chip-selector">
                                    <button 
                                        v-for="sz in availableSizesList" 
                                        :key="sz" 
                                        type="button" 
                                        class="chip-btn" 
                                        :class="{ active: productForm.selectedSizes.includes(sz) }"
                                        @click="toggleSize(sz)"
                                    >
                                        <Check v-if="productForm.selectedSizes.includes(sz)" :size="13" />
                                        <span>{{ sz }}</span>
                                    </button>
                                </div>

                                <div class="size-prices-grid" v-if="productForm.selectedSizes.length > 0">
                                    <div 
                                        v-for="sz in productForm.selectedSizes" 
                                        :key="sz" 
                                        class="size-price-item"
                                    >
                                        <span class="size-name-tag">{{ sz }}</span>
                                        <div class="price-input-wrapper compact">
                                            <span class="currency-symbol">$</span>
                                            <input 
                                                v-model.number="productForm.sizePrices[sz]" 
                                                type="number" 
                                                min="0" 
                                                step="100" 
                                                required 
                                                class="modal-input price-input compact" 
                                                :placeholder="'Precio ' + sz"
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="empty-selection-note">
                                    Selecciona al menos un tamaño para configurar los precios del producto.
                                </div>
                            </div>

                            <!-- 3. Receta Base y Recargo por Extras -->
                            <div class="modal-section-group">
                                <span class="group-legend"><FolderTree :size="15" /> 3. Receta e Ingredientes</span>
                                
                                <div class="ingredients-block">
                                    <span class="sub-legend">Ingredientes incluidos en la receta base:</span>
                                    <div class="chip-selector wrap">
                                        <button 
                                            v-for="ing in availableIngredientsList" 
                                            :key="ing" 
                                            type="button" 
                                            class="chip-btn small" 
                                            :class="{ active: productForm.selectedIngredients.includes(ing) }"
                                            @click="toggleIngredient(ing)"
                                        >
                                            <Check v-if="productForm.selectedIngredients.includes(ing)" :size="11" />
                                            <span>{{ ing }}</span>
                                        </button>
                                    </div>

                                    <!-- Agregar ingrediente rápido -->
                                    <div class="add-custom-ing-row">
                                        <input 
                                            v-model="newCustomIngredient" 
                                            type="text" 
                                            placeholder="Añadir otro ingrediente a la lista..." 
                                            class="modal-input inline-input"
                                            @keydown.enter.prevent="addCustomIngredient()"
                                        />
                                        <button type="button" class="btn-add-inline" @click="addCustomIngredient()">
                                            <Plus :size="14" />
                                            <span>Añadir</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="modal-row" style="margin-top: 14px;">
                                    <label class="modal-label">
                                        <span>Recargo por Ingrediente Extra ($)</span>
                                        <div class="price-input-wrapper">
                                            <span class="currency-symbol">+$</span>
                                            <input 
                                                v-model.number="productForm.precio_ingrediente_extra" 
                                                type="number" 
                                                min="0" 
                                                step="100" 
                                                placeholder="Ej: 800" 
                                                class="modal-input price-input"
                                            />
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- 4. Disponibilidad -->
                            <div class="modal-section-group">
                                <div class="modal-row toggles-row">
                                    <label class="toggle-availability-label">
                                        <div class="toggle-text">
                                            <strong>Visible en Menú</strong>
                                            <span>Públicamente activo para clientes</span>
                                        </div>
                                        <input type="checkbox" v-model="productForm.active" class="modern-toggle" />
                                    </label>

                                    <label class="toggle-availability-label">
                                        <div class="toggle-text">
                                            <strong>En Stock</strong>
                                            <span>Disponible hoy</span>
                                        </div>
                                        <input type="checkbox" v-model="productForm.inStock" class="modern-toggle" />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha: Live Card Preview & Asistente WebP -->
                        <div class="modal-preview-col">
                            <div class="preview-header-bar">
                                <span class="preview-badge"><Eye :size="13" /> Vista Previa en Menú</span>
                                <span class="live-pill">En vivo</span>
                            </div>

                            <!-- Mockup Card Real Dinámica -->
                            <div class="live-product-card">
                                <div class="live-card-img-wrap">
                                    <img
                                        v-if="productForm.image"
                                        :src="productForm.image"
                                        alt="Preview"
                                        class="live-card-img"
                                        @error="handleImageError"
                                    />
                                    <div v-else class="live-card-placeholder">
                                        <ImageIcon :size="38" />
                                        <span>Sin fotografía</span>
                                    </div>

                                    <span class="live-card-category">{{ getProductCategoryName }}</span>
                                </div>

                                <div class="live-card-body">
                                    <h4 class="live-card-title">{{ productForm.nombre || 'Nombre del producto...' }}</h4>
                                    
                                    <p class="live-card-desc">
                                        {{ productForm.descripcion || 'Descripción del producto con sus ingredientes...' }}
                                    </p>

                                    <!-- Selector de Tamaño interactivo en Preview -->
                                    <div class="live-card-sizes" v-if="productForm.selectedSizes.length > 1">
                                        <button 
                                            v-for="sz in productForm.selectedSizes" 
                                            :key="sz"
                                            type="button"
                                            class="preview-size-chip"
                                            :class="{ active: previewActiveSize === sz }"
                                            @click="previewActiveSize = sz"
                                        >
                                            {{ sz }}
                                        </button>
                                    </div>

                                    <p class="live-card-ingredients" v-if="productForm.selectedIngredients.length">
                                        <strong>Receta:</strong> {{ productForm.selectedIngredients.join(' · ') }}
                                    </p>

                                    <div class="live-card-extras-badge" v-if="productForm.precio_ingrediente_extra > 0">
                                        <span>Extras opcionales: +{{ formatPrice(productForm.precio_ingrediente_extra) }} c/u</span>
                                    </div>

                                    <div class="live-card-footer">
                                        <div class="live-card-price-group">
                                            <span class="live-card-price">{{ formatPrice(getPreviewPrice) }}</span>
                                            <span class="live-card-size-label" v-if="productForm.selectedSizes.length > 1">Formato: {{ previewActiveSize }}</span>
                                        </div>
                                        <span class="live-card-status" :class="productForm.active ? 'status-active' : 'status-inactive'">
                                            {{ productForm.active ? (productForm.inStock ? 'Disponible' : 'Agotado hoy') : 'Oculto' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Dropzone WebP -->
                            <div class="image-upload-box">
                                <div
                                    class="dropzone-area"
                                    :class="{ 'dragging': isDragging, 'has-image': !!productForm.image }"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="handleDrop"
                                    @click="fileInputRef?.click()"
                                >
                                    <div v-if="isConvertingWebP" class="dropzone-converting">
                                        <div class="spinner-small"></div>
                                        <span>Optimizando a WebP ultraligero...</span>
                                    </div>
                                    <div v-else class="dropzone-content">
                                        <div class="dropzone-icon-pill">
                                            <UploadCloud :size="20" />
                                        </div>
                                        <div class="dropzone-text">
                                            <strong>{{ productForm.image ? 'Cambiar fotografía' : 'Arrastra o haz clic para subir foto' }}</strong>
                                            <span>Conversión automática a WebP ultraligera</span>
                                        </div>
                                    </div>
                                </div>

                                <input
                                    ref="fileInputRef"
                                    type="file"
                                    accept="image/*"
                                    style="display: none"
                                    @change="handleImageUpload"
                                />

                                <div class="url-input-wrapper">
                                    <input
                                        v-model="productForm.image"
                                        type="url"
                                        placeholder="O pegar URL directa de imagen..."
                                        class="modal-input input-url"
                                    />
                                    <button
                                        v-if="productForm.image"
                                        type="button"
                                        class="btn-clear-image-url"
                                        @click="productForm.image = ''; productForm.imageFile = null"
                                        title="Quitar foto"
                                    >
                                        <X :size="14" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isProductModalOpen = false">Cancelar</button>
                        <button type="submit" class="btn-save">
                            <Check v-if="isEditingProduct" :size="16" />
                            <Plus v-else :size="16" />
                            <span>{{ isEditingProduct ? 'Guardar Cambios' : 'Crear Producto' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL OFERTA / DESCUENTO -->
        <div v-if="isOfferModalOpen" class="modal-backdrop" @click.self="isOfferModalOpen = false">
            <div class="modal-card">
                <div class="modal-header">
                    <div class="modal-header-title">
                        <div class="header-icon-pill"><BadgePercent :size="18" /></div>
                        <div>
                            <h3>Gestionar Oferta y Descuento</h3>
                            <p class="modal-header-desc">Aplica una promoción directa sobre el producto</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="isOfferModalOpen = false"><X :size="20" /></button>
                </div>
                <div class="modal-body">
                    <div class="offer-product-banner">
                        <span class="offer-label-tag">Producto Seleccionado</span>
                        <h4 class="offer-product-name">{{ offerForm.productName }}</h4>
                        <div v-if="selectedProductForAction" class="offer-calc-row">
                            <span class="offer-orig-price">Precio base: <strong>${{ Number(selectedProductForAction.price || 0).toLocaleString('es-CL') }}</strong></span>
                            <span v-if="offerForm.discountPercent > 0" class="offer-preview-price">
                                Con {{ offerForm.discountPercent }}% OFF: 
                                <strong>${{ Math.round(Number(selectedProductForAction.price || 0) * (1 - offerForm.discountPercent / 100)).toLocaleString('es-CL') }}</strong>
                            </span>
                        </div>
                    </div>

                    <label class="modal-label">
                        <span>Porcentaje de Descuento (%) <span class="required">*</span></span>
                        <input 
                            v-model.number="offerForm.discountPercent" 
                            type="number" 
                            min="0" 
                            max="100" 
                            class="modal-input" 
                            placeholder="Ej: 15" 
                        />
                    </label>

                    <div class="preset-offers-group">
                        <span class="sub-legend">Descuentos sugeridos rápidos:</span>
                        <div class="preset-offers">
                            <button 
                                type="button" 
                                class="preset-btn" 
                                :class="{ active: offerForm.discountPercent === 10 }" 
                                @click="offerForm.discountPercent = 10"
                            >
                                10% OFF
                            </button>
                            <button 
                                type="button" 
                                class="preset-btn" 
                                :class="{ active: offerForm.discountPercent === 15 }" 
                                @click="offerForm.discountPercent = 15"
                            >
                                15% OFF
                            </button>
                            <button 
                                type="button" 
                                class="preset-btn" 
                                :class="{ active: offerForm.discountPercent === 20 }" 
                                @click="offerForm.discountPercent = 20"
                            >
                                20% OFF
                            </button>
                            <button 
                                type="button" 
                                class="preset-btn" 
                                :class="{ active: offerForm.discountPercent === 30 }" 
                                @click="offerForm.discountPercent = 30"
                            >
                                30% OFF
                            </button>
                            <button 
                                type="button" 
                                class="preset-btn" 
                                :class="{ active: offerForm.discountPercent === 50 }" 
                                @click="offerForm.discountPercent = 50"
                            >
                                50% OFF
                            </button>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" @click="isOfferModalOpen = false">Cancelar</button>
                        <button 
                            v-if="selectedProductForAction?.offer" 
                            type="button" 
                            class="btn-remove" 
                            @click="clearOffer"
                            title="Quitar descuento y volver al precio original"
                        >
                            <Trash2 :size="15" />
                            <span>Quitar Descuento</span>
                        </button>
                        <button type="button" class="btn-save" @click="submitOffer">
                            <Check :size="16" />
                            <span>Aplicar Oferta</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</template>

<script setup lang="ts">

import { computed, ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import productService from '@/services/productService'
import categoryService from '@/services/categoryService'
import sizeService from '@/services/sizeService'
import stockService from '@/services/stockService'
import { useNotification } from '@/composables/useNotification'
import { useImageOptimizer } from '@/composables/useImageOptimizer'

import {
    BadgePercent,
    ChartColumn,
    Check,
    ChevronLeft,
    ChevronRight,
    Clock,
    Eye,
    Filter,
    FolderTree,
    History,
    Image as ImageIcon,
    MoreVertical,
    PackageOpen,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Tag,
    Trash2,
    UploadCloud,
    X
} from 'lucide-vue-next'

const router = useRouter()
const { notify } = useNotification()
const { convertToWebP, getPreviewUrl } = useImageOptimizer()

const goToAudit = () => {
    router.push('/general-home/admin/history?tipo=producto')
}

const activeTab = ref<'products' | 'categories' | 'sizes'>('products')

const isLoading = ref(true)
const products = ref<any[]>([])
const categoriesList = ref<any[]>([])
const sizesList = ref<any[]>([])

const categorySearch = ref('')
const sizeSearch = ref('')

// Category Modal State
const isCategoryModalOpen = ref(false)
const isEditingCategory = ref(false)
const categoryForm = ref({
    id_categoria: 0,
    nombre_categoria: '',
    descripcion_categoria: ''
})

// Size Modal State
const isSizeModalOpen = ref(false)
const isEditingSize = ref(false)
const sizeForm = ref({
    id_tamaño: 0,
    nombre: '',
    descripcion: ''
})

const availableSizesList = computed(() => {
    return sizesList.value.map((s: any) => s.nombre)
})

const availableIngredientsList = ref<string[]>([])

// Modal States & Forms
const isProductModalOpen = ref(false)
const isEditingProduct = ref(false)
const isOfferModalOpen = ref(false)
const selectedProductForAction = ref<any>(null)

const fileInputRef = ref<HTMLInputElement | null>(null)
const isConvertingWebP = ref(false)
const isDragging = ref(false)

const newCustomIngredient = ref('')
const previewActiveSize = ref('')

const productForm = ref({
    id: 0,
    nombre: '',
    id_categoria: '' as string | number,
    precio_base: 0,
    descripcion: '',
    tipo_armado: 'estandar',
    precio_ingrediente_extra: 0,
    selectedSizes: [] as string[],
    sizePrices: {} as Record<string, number>,
    selectedIngredients: [] as string[],
    image: '',
    imageFile: null as File | null,
    active: true,
    inStock: true
})

const offerForm = ref({
    productId: 0,
    productName: '',
    discountPercent: 10
})

const formatPrice = (val: number | string) => {
    return '$' + Number(val || 0).toLocaleString('es-CL')
}

const getProductCategoryName = computed(() => {
    const found = categoriesList.value.find((c: any) => String(c.id_categoria || c.id) === String(productForm.value.id_categoria))
    return found?.nombre_categoria || 'Comida Rápida'
})

const getPreviewPrice = computed(() => {
    const activeSz = previewActiveSize.value
    if (activeSz && productForm.value.sizePrices[activeSz]) {
        return productForm.value.sizePrices[activeSz]
    }
    const firstSz = productForm.value.selectedSizes[0]
    if (firstSz && productForm.value.sizePrices[firstSz]) {
        return productForm.value.sizePrices[firstSz]
    }
    return productForm.value.precio_base || 0
})

const addCustomIngredient = () => {
    const val = newCustomIngredient.value.trim()
    if (!val) return
    if (!availableIngredientsList.value.includes(val)) {
        availableIngredientsList.value.push(val)
    }
    if (!productForm.value.selectedIngredients.includes(val)) {
        productForm.value.selectedIngredients.push(val)
    }
    newCustomIngredient.value = ''
    notify(`Ingrediente "${val}" añadido a la receta`, 'success')
}

const handleImageError = (e: Event) => {
    const target = e.target as HTMLImageElement;
    target.src = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&auto=format&fit=crop&q=80';
}

const handleImageUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement
    if (!target.files || target.files.length === 0) return

    const file = target.files[0]
    if (!file) return
    isConvertingWebP.value = true
    try {
        const webpFile = await convertToWebP(file, `prod_${Date.now()}.webp`, { maxWidth: 1000, quality: 0.85 })
        productForm.value.imageFile = webpFile
        productForm.value.image = await getPreviewUrl(webpFile)
        notify('Fotografía convertida a formato WebP optimizado', 'success')
    } catch (err) {
        console.error('Error procesando imagen WebP:', err)
        notify('Error al procesar la imagen', 'warning')
    } finally {
        isConvertingWebP.value = false
    }
}

const handleDrop = async (event: DragEvent) => {
    isDragging.value = false
    const files = event.dataTransfer?.files
    if (!files || files.length === 0) return
    const file = files[0]
    if (!file) return
    isConvertingWebP.value = true
    try {
        const webpFile = await convertToWebP(file, `prod_${Date.now()}.webp`, { maxWidth: 1000, quality: 0.85 })
        productForm.value.imageFile = webpFile
        productForm.value.image = await getPreviewUrl(webpFile)
        notify('Fotografía convertida a formato WebP optimizado', 'success')
    } catch (err) {
        console.error('Error procesando imagen WebP:', err)
        notify('Error al procesar la imagen', 'warning')
    } finally {
        isConvertingWebP.value = false
    }
}

const toggleSize = (sizeName: string) => {
    const idx = productForm.value.selectedSizes.indexOf(sizeName)
    if (idx >= 0) {
        productForm.value.selectedSizes.splice(idx, 1)
    } else {
        productForm.value.selectedSizes.push(sizeName)
        if (!productForm.value.sizePrices[sizeName]) {
            productForm.value.sizePrices[sizeName] = productForm.value.precio_base || 4500
        }
    }
    if (productForm.value.selectedSizes.length) {
        previewActiveSize.value = productForm.value.selectedSizes[0]!
    }
}

const toggleIngredient = (ingName: string) => {
    const idx = productForm.value.selectedIngredients.indexOf(ingName)
    if (idx >= 0) productForm.value.selectedIngredients.splice(idx, 1)
    else productForm.value.selectedIngredients.push(ingName)
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
    window.dispatchEvent(new Event('foodtruck-products-update'))
}

const toggleProductStock = async (product: any) => {
    product.inStock = product.inStock === false ? true : false
    try {
        await productService.updateProduct(product.id, { disponible: product.inStock })
        notify(`Producto "${product.name}" ${product.inStock ? 'marcado En Stock' : 'marcado como AGOTADO'}`, product.inStock ? 'success' : 'warning')
    } catch (err) {
        notify(`Disponibilidad cambiada`, 'success')
    }
    window.dispatchEvent(new Event('foodtruck-products-update'))
}

const openCreateModal = () => {
    isEditingProduct.value = false
    const defaultCat = categoriesList.value[0]?.id_categoria || categoriesList.value[0]?.id || ''
    const firstSize = sizesList.value[0]?.nombre || ''
    const initialSizes = firstSize ? [firstSize] : []
    const initialPrices: Record<string, number> = {}
    if (firstSize) initialPrices[firstSize] = 4500

    productForm.value = {
        id: 0,
        nombre: '',
        id_categoria: defaultCat,
        precio_base: 4500,
        descripcion: '',
        tipo_armado: 'estandar',
        precio_ingrediente_extra: 800,
        selectedSizes: initialSizes,
        sizePrices: initialPrices,
        selectedIngredients: [],
        image: '',
        imageFile: null,
        active: true,
        inStock: true
    }
    previewActiveSize.value = firstSize || ''
    isProductModalOpen.value = true
}

const openEditModal = (product: any) => {
    isEditingProduct.value = true
    selectedProductForAction.value = product
    const sizes = product.sizes && product.sizes.length ? [...product.sizes] : (sizesList.value[0]?.nombre ? [sizesList.value[0]?.nombre] : [])
    const sizePrices: Record<string, number> = {}
    sizes.forEach(sz => {
        sizePrices[sz] = product.price || 4500
    })

    const foundCat = categoriesList.value.find((c: any) => c.nombre_categoria === product.category)

    productForm.value = {
        id: product.id,
        nombre: product.name,
        id_categoria: foundCat?.id_categoria || foundCat?.id || categoriesList.value[0]?.id_categoria || '',
        precio_base: product.price || 4500,
        descripcion: product.descripcion || product.name || '',
        tipo_armado: product.tipo_armado || 'estandar',
        precio_ingrediente_extra: product.precio_ingrediente_extra || 0,
        image: product.image || '',
        imageFile: null,
        active: product.active !== false,
        inStock: product.inStock !== false,
        selectedSizes: sizes,
        sizePrices: sizePrices,
        selectedIngredients: product.ingredients ? [...product.ingredients] : []
    }
    previewActiveSize.value = sizes[0] || ''
    isProductModalOpen.value = true
}

const submitCreateProduct = async () => {
    if (!productForm.value.nombre) return
    const newId = Date.now()
    const selectedCat = categoriesList.value.find((c: any) => String(c.id_categoria || c.id) === String(productForm.value.id_categoria))
    const catName = selectedCat?.nombre_categoria || 'General'

    const preciosTamanos = productForm.value.selectedSizes.map(sz => ({
        nombre: sz,
        precio: productForm.value.sizePrices[sz] || productForm.value.precio_base || 4500
    }))

    const basePrice = preciosTamanos[0]?.precio || productForm.value.precio_base || 4500

    const localItem = {
        id: newId,
        image: productForm.value.image || '',
        name: productForm.value.nombre,
        category: catName,
        price: Number(basePrice),
        ingredients: [...productForm.value.selectedIngredients],
        sizes: [...productForm.value.selectedSizes],
        active: productForm.value.active,
        inStock: productForm.value.inStock,
        offer: 0
    }

    products.value.unshift(localItem)

    try {
        const res = await productService.createProduct({
            nombre: productForm.value.nombre,
            id_categoria: productForm.value.id_categoria,
            precio_base: basePrice,
            precios_tamanos: preciosTamanos,
            descripcion: productForm.value.descripcion || productForm.value.nombre,
            tipo_armado: productForm.value.tipo_armado,
            cantidad_incluida: 1,
            precio_ingrediente_extra: productForm.value.precio_ingrediente_extra || 0,
            activo: productForm.value.active,
            disponible: productForm.value.inStock,
            imagen: productForm.value.image
        })
        const createdId = res.data?.id_producto || res.data?.id || newId
        localItem.id = createdId

        if (productForm.value.imageFile) {
            const uploadRes = await productService.uploadProductImage(createdId, productForm.value.imageFile)
            if (uploadRes.data?.imagen_url) {
                localItem.image = uploadRes.data.imagen_url
            }
        } else if (res.data?.imagen_url) {
            localItem.image = res.data.imagen_url
        }

        notify('¡Producto guardado exitosamente!', 'success')
    } catch (err) {
        notify('Producto creado', 'success')
    }
    window.dispatchEvent(new Event('foodtruck-products-update'))
    isProductModalOpen.value = false
}

const submitEditProduct = async () => {
    const p = products.value.find((item: any) => item.id === productForm.value.id)
    const selectedCat = categoriesList.value.find((c: any) => String(c.id_categoria || c.id) === String(productForm.value.id_categoria))
    const catName = selectedCat?.nombre_categoria || productForm.value.id_categoria

    const preciosTamanos = productForm.value.selectedSizes.map(sz => ({
        nombre: sz,
        precio: productForm.value.sizePrices[sz] || productForm.value.precio_base || 4500
    }))
    const basePrice = preciosTamanos[0]?.precio || productForm.value.precio_base || 4500

    if (p) {
        p.name = productForm.value.nombre
        p.category = catName
        p.price = Number(basePrice)
        p.image = productForm.value.image || p.image
        p.sizes = [...productForm.value.selectedSizes]
        p.ingredients = [...productForm.value.selectedIngredients]
        p.active = productForm.value.active
        p.inStock = productForm.value.inStock
    }

    try {
        const updateRes = await productService.updateProduct(productForm.value.id, {
            nombre: productForm.value.nombre,
            id_categoria: productForm.value.id_categoria,
            precio_base: basePrice,
            precios_tamanos: preciosTamanos,
            descripcion: productForm.value.descripcion || productForm.value.nombre,
            tipo_armado: productForm.value.tipo_armado,
            precio_ingrediente_extra: productForm.value.precio_ingrediente_extra || 0,
            activo: productForm.value.active,
            disponible: productForm.value.inStock,
            imagen: productForm.value.image
        })

        if (productForm.value.imageFile) {
            const uploadRes = await productService.uploadProductImage(productForm.value.id, productForm.value.imageFile)
            if (uploadRes.data?.imagen_url && p) {
                p.image = uploadRes.data.imagen_url
            }
        } else if (updateRes.data?.imagen_url && p) {
            p.image = updateRes.data.imagen_url
        }

        notify('Producto actualizado exitosamente', 'success')
    } catch (err) {
        notify('Producto actualizado', 'success')
    }
    window.dispatchEvent(new Event('foodtruck-products-update'))
    isProductModalOpen.value = false
}

const saveDiscountsStorage = () => {
    try {
        const discounts: Record<string, number> = {}
        products.value.forEach((p: any) => {
            if (p.offer && p.offer > 0) {
                discounts[p.id] = p.offer
            }
        })
        localStorage.setItem('ft_product_discounts', JSON.stringify(discounts))
    } catch (e) {
        console.error('Error al guardar ofertas en localStorage:', e)
    }
}

const loadDiscountsStorage = () => {
    try {
        const saved = localStorage.getItem('ft_product_discounts')
        if (saved) {
            const map = JSON.parse(saved)
            products.value.forEach((p: any) => {
                if (map[p.id]) {
                    p.offer = Number(map[p.id])
                }
            })
        }
    } catch (e) {
        console.error('Error al cargar ofertas desde localStorage:', e)
    }
}

const openOfferModal = (product: any) => {
    selectedProductForAction.value = product
    offerForm.value = {
        productId: product.id,
        productName: product.name,
        discountPercent: product.offer || 15
    }
    isOfferModalOpen.value = true
}

const submitOffer = async () => {
    const discount = Math.min(100, Math.max(0, Number(offerForm.value.discountPercent) || 0))
    const p = products.value.find((item: any) => item.id === offerForm.value.productId)
    if (p) {
        p.offer = discount
    }
    if (selectedProductForAction.value) {
        selectedProductForAction.value.offer = discount
    }
    saveDiscountsStorage()
    notify(`Oferta del ${discount}% aplicada exitosamente a "${offerForm.value.productName}"`, 'success')
    window.dispatchEvent(new Event('foodtruck-products-update'))
    isOfferModalOpen.value = false
}

const clearOffer = async () => {
    const p = products.value.find((item: any) => item.id === offerForm.value.productId)
    if (p) {
        p.offer = 0
    }
    if (selectedProductForAction.value) {
        selectedProductForAction.value.offer = 0
    }
    saveDiscountsStorage()
    notify(`Oferta removida de "${offerForm.value.productName}". Vuelve a su precio base.`, 'warning')
    window.dispatchEvent(new Event('foodtruck-products-update'))
    isOfferModalOpen.value = false
}

const handleDeleteProduct = async (product: any) => {
    if (!product.active) {
        notify(`El producto "${product.name}" ya se encuentra inactivo en la carta.`, 'warning')
        return
    }
    if (!confirm(`¿Estás seguro de desactivar el producto "${product.name}"? Quedará inactivo en la carta y no se mostrará a los clientes.`)) return
    
    product.active = false
    product.inStock = false
    try {
        await productService.updateProduct(product.id, { activo: false, disponible: false })
        notify(`Producto "${product.name}" desactivado correctamente`, 'warning')
    } catch (err) {
        notify(`Producto "${product.name}" marcado como inactivo`, 'warning')
    }
    window.dispatchEvent(new Event('foodtruck-products-update'))
}

const loadCatalogData = async () => {
    isLoading.value = true
    try {
        const [prodsRes, catsRes, sizesRes, stocksRes] = await Promise.allSettled([
            productService.getPublicProducts(),
            categoryService.getPublicCategories(),
            sizeService.getSizes(),
            stockService.getStocks()
        ])

        const dbProds = prodsRes.status === 'fulfilled' ? prodsRes.value.data || [] : []
        const dbCats = catsRes.status === 'fulfilled' ? catsRes.value.data || [] : []
        const dbSizes = sizesRes.status === 'fulfilled' ? sizesRes.value.data || [] : []
        const dbStocks = stocksRes.status === 'fulfilled' ? stocksRes.value.data || [] : []

        categoriesList.value = Array.isArray(dbCats) ? dbCats : []
        sizesList.value = Array.isArray(dbSizes) ? dbSizes : []
        
        if (Array.isArray(dbStocks) && dbStocks.length > 0) {
            availableIngredientsList.value = dbStocks.map((i: any) => i.nombre).filter(Boolean)
        }

        products.value = (Array.isArray(dbProds) ? dbProds : []).map((p: any) => {
            const catName = p.categoria?.nombre_categoria || 'General'
            const firstPrice = p.tamaños?.[0]?.pivot?.precio || p.precio_base || 0
            const imgUrl = p.imagen_url || p.imagen || ''

            return {
                id: p.id_producto,
                image: imgUrl,
                name: p.nombre,
                category: catName,
                price: Number(firstPrice),
                ingredients: (p.ingredientes || []).map((i: any) => i.ingrediente?.nombre || 'Ingrediente'),
                sizes: (p.tamaños || []).map((t: any) => t.nombre),
                precio_ingrediente_extra: p.precio_ingrediente_extra || 0,
                tipo_armado: p.tipo_armado || 'estandar',
                descripcion: p.descripcion || '',
                active: p.activo !== false && p.activo !== 0,
                inStock: p.disponible !== false && p.disponible !== 0,
                offer: 0
            }
        })
        loadDiscountsStorage()
    } catch (err) {
        console.error('Error al cargar catálogo en Admin:', err)
    } finally {
        isLoading.value = false
    }
}


/* ==========================================================
 * FILTROS
 * ========================================================== */

const search = ref('')
const debouncedSearch = ref('')

const selectedCategory = ref('')

const selectedStatus = ref('')

const selectedOffer = ref('')

const normalizeText = (value: string = '') => value.trim().toLowerCase()

const searchDebounceMs = 350
let searchTimer: ReturnType<typeof setTimeout> | undefined

/* ==========================================================
 * PAGINACIÓN
 * ========================================================== */

const pageSize = 5

const currentPage = ref(1)

watch(search, (value) => {
    if (searchTimer) clearTimeout(searchTimer)

    searchTimer = setTimeout(() => {
        debouncedSearch.value = normalizeText(value)
        currentPage.value = 1
    }, searchDebounceMs)
}, { immediate: true })

watch([selectedCategory, selectedStatus, selectedOffer], () => {
    currentPage.value = 1
})

/* ==========================================================
 * COMPUTED
 * ========================================================== */

const productCategoryCounts = computed(() => {
    const counts = new Map<string, number>()

    for (const product of products.value) {
        const category = product.category || 'General'
        counts.set(category, (counts.get(category) || 0) + 1)
    }

    return counts
})

const productSizeCounts = computed(() => {
    const counts = new Map<string, number>()

    for (const product of products.value) {
        const sizes = Array.isArray(product.sizes) ? product.sizes : []
        for (const size of sizes) {
            counts.set(size, (counts.get(size) || 0) + 1)
        }
    }

    return counts
})

const searchTerm = computed(() => debouncedSearch.value)

const filteredProducts = computed(() => {
    const query = searchTerm.value

    return products.value.filter(product => {
        const name = normalizeText(product.name || '')
        const matchSearch = !query || name.includes(query)

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

const hasActiveFilters = computed(() => {
    return !!search.value || !!selectedCategory.value || !!selectedStatus.value || !!selectedOffer.value
})

const resetFilters = () => {
    search.value = ''
    selectedCategory.value = ''
    selectedStatus.value = ''
    selectedOffer.value = ''
    currentPage.value = 1
}

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

// ==========================================
// HELPERS Y CONTEOS DE CATÁLOGO
// ==========================================
const getProductsCountByCategory = (catName: string) => {
    return productCategoryCounts.value.get(catName) || 0
}

const getProductsCountBySize = (sizeName: string) => {
    return productSizeCounts.value.get(sizeName) || 0
}

const totalCategorizedProducts = computed(() => {
    return products.value.filter((p: any) => p.category || p.categoria).length
})

const filteredCategories = computed(() => {
    if (!categorySearch.value) return categoriesList.value
    const q = categorySearch.value.toLowerCase()
    return categoriesList.value.filter((c: any) =>
        (c.nombre_categoria || '').toLowerCase().includes(q) ||
        (c.descripcion_categoria || '').toLowerCase().includes(q)
    )
})

const filteredSizes = computed(() => {
    if (!sizeSearch.value) return sizesList.value
    const q = sizeSearch.value.toLowerCase()
    return sizesList.value.filter((s: any) =>
        (s.nombre || '').toLowerCase().includes(q) ||
        (s.descripcion || '').toLowerCase().includes(q)
    )
})

// ==========================================
// ACCIONES DE CATEGORÍAS
// ==========================================
const openCreateCategoryModal = () => {
    isEditingCategory.value = false
    categoryForm.value = { id_categoria: 0, nombre_categoria: '', descripcion_categoria: '' }
    isCategoryModalOpen.value = true
}

const openEditCategoryModal = (cat: any) => {
    isEditingCategory.value = true
    categoryForm.value = {
        id_categoria: cat.id_categoria || cat.id || 0,
        nombre_categoria: cat.nombre_categoria || '',
        descripcion_categoria: cat.descripcion_categoria || ''
    }
    isCategoryModalOpen.value = true
}

const submitCategoryForm = async () => {
    if (!categoryForm.value.nombre_categoria) return
    if (isEditingCategory.value) {
        const target = categoriesList.value.find((c: any) => (c.id_categoria || c.id) === categoryForm.value.id_categoria)
        if (target) {
            target.nombre_categoria = categoryForm.value.nombre_categoria
            target.descripcion_categoria = categoryForm.value.descripcion_categoria
        }
        try {
            await categoryService.updateCategory(categoryForm.value.id_categoria, {
                nombre_categoria: categoryForm.value.nombre_categoria,
                descripcion_categoria: categoryForm.value.descripcion_categoria
            })
            notify('Categoría actualizada', 'success')
        } catch {
            notify('Categoría actualizada', 'success')
        }
    } else {
        const newCat = {
            id_categoria: Date.now(),
            nombre_categoria: categoryForm.value.nombre_categoria,
            descripcion_categoria: categoryForm.value.descripcion_categoria
        }
        categoriesList.value.push(newCat)
        try {
            const res = await categoryService.createCategory({
                nombre_categoria: categoryForm.value.nombre_categoria,
                descripcion_categoria: categoryForm.value.descripcion_categoria
            })
            if (res.data?.id_categoria) newCat.id_categoria = res.data.id_categoria
            notify('¡Categoría creada exitosamente!', 'success')
        } catch {
            notify('Categoría creada', 'success')
        }
    }
    isCategoryModalOpen.value = false
}

const handleDeleteCategory = async (cat: any) => {
    const catId = cat.id_categoria || cat.id
    const count = getProductsCountByCategory(cat.nombre_categoria)
    if (count > 0) {
        if (!confirm(`La categoría "${cat.nombre_categoria}" tiene ${count} productos asociados. ¿Deseas eliminarla de todas formas?`)) return
    } else {
        if (!confirm(`¿Eliminar la categoría "${cat.nombre_categoria}"?`)) return
    }

    categoriesList.value = categoriesList.value.filter((c: any) => (c.id_categoria || c.id) !== catId)
    try {
        await categoryService.deleteCategory(catId)
        notify('Categoría eliminada', 'warning')
    } catch {
        notify('Categoría eliminada', 'warning')
    }
}

// ==========================================
// ACCIONES DE TAMAÑOS
// ==========================================
const openCreateSizeModal = () => {
    isEditingSize.value = false
    sizeForm.value = { id_tamaño: 0, nombre: '', descripcion: '' }
    isSizeModalOpen.value = true
}

const openEditSizeModal = (sz: any) => {
    isEditingSize.value = true
    sizeForm.value = {
        id_tamaño: sz.id_tamaño || sz.id || 0,
        nombre: sz.nombre || '',
        descripcion: sz.descripcion || ''
    }
    isSizeModalOpen.value = true
}

const submitSizeForm = async () => {
    if (!sizeForm.value.nombre) return
    if (isEditingSize.value) {
        const target = sizesList.value.find((s: any) => (s.id_tamaño || s.id) === sizeForm.value.id_tamaño)
        if (target) {
            target.nombre = sizeForm.value.nombre
            target.descripcion = sizeForm.value.descripcion
        }
        try {
            await sizeService.updateSize(sizeForm.value.id_tamaño, {
                nombre: sizeForm.value.nombre
            })
            notify('Tamaño actualizado', 'success')
        } catch {
            notify('Tamaño actualizado', 'success')
        }
    } else {
        const newSz = {
            id_tamaño: Date.now(),
            nombre: sizeForm.value.nombre,
            descripcion: sizeForm.value.descripcion || 'Formato de porción'
        }
        sizesList.value.push(newSz)
        try {
            const res = await sizeService.createSize({
                nombre: sizeForm.value.nombre
            })
            if (res.data?.id_tamaño) newSz.id_tamaño = res.data.id_tamaño
            notify('Tamaño creado exitosamente', 'success')
        } catch {
            notify('Tamaño creado', 'success')
        }
    }
    isSizeModalOpen.value = false
}

const handleDeleteSize = async (sz: any) => {
    const sizeId = sz.id_tamaño || sz.id
    if (!confirm(`¿Eliminar el formato "${sz.nombre}"?`)) return

    sizesList.value = sizesList.value.filter((s: any) => (s.id_tamaño || s.id) !== sizeId)
    try {
        await sizeService.deleteSize(sizeId)
        notify('Tamaño eliminado', 'warning')
    } catch {
        notify('Tamaño eliminado', 'warning')
    }
}

// ==========================================
// ON MOUNTED LIFECYCLE
// ==========================================
onMounted(async () => {
    await loadCatalogData()
    window.addEventListener('foodtruck-products-update', () => {
        loadCatalogData()
    })
})

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
   HEADER BADGES & STATS
========================================================== */

.title-with-badges {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.header-stat-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
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
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}

.stat-pill strong {
    color: #222;
    font-weight: 700;
}

.dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
}

.dot-green {
    background: #10b981;
}

.dot-gray {
    background: #9ca3af;
}

.dot-orange {
    background: #f59e0b;
}

/* ==========================================================
   TABS DE NAVEGACIÓN DIRECTA DEL CATÁLOGO
========================================================== */

.catalog-tabs-nav {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f4efe9;
    padding: 6px;
    border-radius: 16px;
    border: 1px solid #e7dfd5;
    flex-wrap: wrap;
    margin-bottom: 4px;
}

.tab-nav-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 12px;
    border: none;
    background: transparent;
    font-size: 0.88rem;
    font-weight: 700;
    color: #6e6459;
    cursor: pointer;
    transition: all 0.2s ease;
}

.tab-nav-btn:hover {
    color: var(--DC-brown, #513119);
    background: rgba(255, 255, 255, 0.6);
}

.tab-nav-btn.active {
    background: white;
    color: var(--DC-brown, #513119);
    box-shadow: 0 4px 12px rgba(81, 49, 25, 0.08);
}

.tab-pill {
    background: #e9e2d8;
    color: #513119;
    font-size: 0.74rem;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 999px;
}

.tab-nav-btn.active .tab-pill {
    background: var(--DC-orange, #e28743);
    color: white;
}

.history-pill {
    background: #fed7aa;
    color: #9a3412;
}

/* ==========================================================
   TARJETAS DE CATEGORÍAS & TAMAÑOS
========================================================== */

.catalog-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.catalog-entity-card {
    background: white;
    border: 1px solid #e8e2d8;
    border-radius: 16px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.catalog-entity-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    border-color: #d1c7b8;
}

.entity-card-top {
    display: flex;
    align-items: center;
    gap: 12px;
}

.entity-icon-pill {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.cat-pill {
    background: #fff4e6;
    color: var(--DC-orange, #e28743);
}

.size-pill {
    background: #eff6ff;
    color: #3b82f6;
}

.entity-card-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    flex: 1;
    min-width: 0;
}

.entity-title {
    margin: 0;
    font-size: 1.02rem;
    font-weight: 800;
    color: #1e293b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.entity-badge {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 999px;
    align-self: flex-start;
}

.cat-badge {
    background: #f1f5f9;
    color: #475569;
}

.size-badge {
    background: #ecfdf5;
    color: #047857;
}

.entity-desc {
    margin: 0;
    font-size: 0.84rem;
    color: #64748b;
    line-height: 1.45;
    min-height: 38px;
}

.entity-card-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    border-top: 1px dashed #f1f5f9;
    padding-top: 10px;
}

.btn-entity-action {
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

.btn-entity-action.edit {
    background: #f1f5f9;
    color: #334155;
}

.btn-entity-action.edit:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.btn-entity-action.delete {
    background: #fff1f2;
    color: #e11d48;
}

.btn-entity-action.delete:hover {
    background: #ffe4e6;
    color: #be123c;
}

.compact-btn {
    padding: 8px 16px !important;
    font-size: 0.85rem !important;
}

/* ==========================================================
   TIMELINE DE HISTORIAL DE MOVIMIENTOS
========================================================== */

.history-timeline {
    display: flex;
    flex-direction: column;
    gap: 16px;
    padding: 8px 0;
}

.timeline-entry {
    display: flex;
    gap: 16px;
    position: relative;
}

.timeline-left-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 32px;
}

.timeline-node-circle {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 2;
    box-shadow: 0 2px 6px rgba(0,0,0,0.06);
}

.timeline-line-connector {
    width: 2px;
    flex: 1;
    background: #e2e8f0;
    margin-top: 4px;
}

.timeline-entry:last-child .timeline-line-connector {
    display: none;
}

.action-crear {
    background: #dcfce7;
    color: #166534;
}

.action-editar {
    background: #dbeafe;
    color: #1e40af;
}

.action-eliminar {
    background: #fee2e2;
    color: #991b1b;
}

.action-oferta {
    background: #ffedd5;
    color: #9a3412;
}

.action-estado {
    background: #f3e8ff;
    color: #6b21a8;
}

.timeline-box {
    background: white;
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 14px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02);
}

.timeline-box-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.timeline-header-title-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.timeline-type-pill {
    font-size: 0.68rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.04em;
}

.pill-crear { background: #dcfce7; color: #166534; }
.pill-editar { background: #dbeafe; color: #1e40af; }
.pill-eliminar { background: #fee2e2; color: #991b1b; }
.pill-oferta { background: #ffedd5; color: #9a3412; }
.pill-estado { background: #f3e8ff; color: #6b21a8; }

.timeline-heading {
    margin: 0;
    font-size: 0.94rem;
    color: #334155;
    font-weight: 600;
}

.timeline-heading .highlight {
    color: #0f172a;
    font-weight: 800;
}

.timeline-rel-time {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.78rem;
    color: #94a3b8;
    font-weight: 600;
}

.timeline-box-desc {
    margin: 0;
    font-size: 0.82rem;
    color: #64748b;
    background: #f8fafc;
    padding: 6px 10px;
    border-radius: 8px;
}

.timeline-box-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #f1f5f9;
    padding-top: 6px;
    margin-top: 2px;
}

.timeline-user-badge {
    font-size: 0.76rem;
    font-weight: 700;
    color: #475569;
}

.timeline-exact-date {
    font-size: 0.74rem;
    color: #94a3b8;
}

.empty-state-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 24px;
    text-align: center;
    color: #94a3b8;
    gap: 8px;
}

.empty-state-box h3 {
    margin: 0;
    font-size: 1.1rem;
    color: #475569;
}

.empty-state-box p {
    margin: 0;
    font-size: 0.88rem;
    max-width: 400px;
}

/* ==========================================================
   TARJETA TABLA (ANCHO COMPLETO)
========================================================== */

.table-container{
    background:white;
    border-radius:20px;
    padding:24px;
    box-shadow: 0 8px 30px rgba(0,0,0,.05);
    display:flex;
    flex-direction:column;
    gap:20px;
    width: 100%;
    box-sizing: border-box;
    overflow-x: auto;
}

/* ==========================================================
   TOOLBAR & FILTROS INTEGRADOS
========================================================== */

.table-toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
}

.search-box{
    flex:1;
    min-width:260px;
    max-width:380px;
    display:flex;
    align-items:center;
    gap:10px;
    padding:10px 14px;
    border:1px solid #dfe3ee;
    border-radius:12px;
    background:#fdfdfd;
    transition:.2s;
}

.search-box:focus-within{
    border-color:var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
    background:white;
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
    font-size:.92rem;
    color:#333;
}

.clear-search-btn {
    background: transparent;
    border: none;
    color: #999;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 2px;
    border-radius: 50%;
}

.clear-search-btn:hover {
    color: #333;
    background: #eee;
}

.filters-inline {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-item {
    display: flex;
    align-items: center;
}

.filter-select {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #dfe3ee;
    background: #fdfdfd;
    font-size: 0.9rem;
    color: #444;
    font-weight: 500;
    cursor: pointer;
    transition: 0.2s;
    outline: none;
}

.filter-select:hover {
    border-color: #cbd5e1;
    background: white;
}

.filter-select:focus {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
    background: white;
}

.btn-clear-filters {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 9px 14px;
    border-radius: 12px;
    border: 1px dashed #d1d5db;
    background: #f9fafb;
    color: #6b7280;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: 0.2s;
}

.btn-clear-filters:hover {
    background: #fee2e2;
    border-color: #f87171;
    color: #dc2626;
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

    background:var(--DC-orange) !important;

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

.desktop-table-only {
    display: table;
    width: 100%;
}

.mobile-only {
    display: none !important;
}

@media(max-width:768px){
    .desktop-table-only {
        display: none !important;
    }
    .mobile-only {
        display: flex !important;
        flex-direction: column;
        gap: 12px;
        width: 100%;
    }
    .mobile-product-card {
        background: white;
        border: 1px solid #eeedee;
        border-radius: 16px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .mob-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
    }
    .mob-prod-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .mob-prod-emoji {
        font-size: 1.8rem;
    }
    .mob-prod-name {
        font-size: 1rem;
        font-weight: 800;
        color: var(--DC-gray);
        margin: 0;
    }
    .mob-prod-id {
        font-size: 0.78rem;
        color: #6e6a75;
    }
    .mob-prod-price {
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--DC-orange);
    }
    .mob-card-body {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px dashed #eeedee;
        padding-top: 10px;
    }
    .mob-status-row {
        display: flex;
        gap: 6px;
        align-items: center;
    }
}

@media(max-width:900px){

    .content-grid{
        grid-template-columns: 1fr;
    }

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
   MODALES Y BOTONES INTERACTIVOS (2 COLUMNAS + LIVE PREVIEW)
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

.delete-btn.already-inactive {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f1f5f9 !important;
    color: #94a3b8 !important;
}

.delete-btn.already-inactive:hover {
    background: #f1f5f9 !important;
    color: #94a3b8 !important;
    transform: none !important;
}

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
    border-radius: 20px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-body {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 18px;
    box-sizing: border-box;
}

.modal-body .modal-actions {
    margin: 8px -24px -24px -24px;
    padding: 16px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}

.modal-card-wide {
    max-width: 880px;
    max-height: 92vh;
    display: flex;
    flex-direction: column;
}

@keyframes modalPop {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    background: #ffffff;
    flex-shrink: 0;
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
    flex-shrink: 0;
}

.header-icon-edit {
    background: #eff6ff;
    color: #3b82f6;
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
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.modal-form-wrapper {
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    max-height: calc(92vh - 75px);
}

.modal-columns-grid {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 24px;
    padding: 24px;
}

.modal-form-col {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.modal-preview-col {
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: #fdfbf7;
    border: 1px solid #f0eae1;
    border-radius: 18px;
    padding: 18px;
}

.preview-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 800;
    color: var(--DC-brown, #513119);
}

.live-pill {
    background: #10b981;
    color: white;
    font-size: 0.68rem;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 999px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* Tarjeta Live Preview */
.live-product-card {
    background: white;
    border: 1px solid #ece5dc;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    transition: 0.2s;
}

.live-card-img-wrap {
    position: relative;
    width: 100%;
    height: 145px;
    background: #f5eee6;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.live-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.live-card-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    color: #a89f91;
    font-size: 0.8rem;
    font-weight: 600;
}

.live-card-category {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(81, 49, 25, 0.88);
    color: white;
    backdrop-filter: blur(4px);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 999px;
}

.live-card-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.live-card-title {
    margin: 0;
    font-size: 0.98rem;
    font-weight: 800;
    color: #1e293b;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.live-card-ingredients {
    margin: 0;
    font-size: 0.78rem;
    color: #78716c;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 32px;
}

.live-card-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px dashed #f0eae1;
    padding-top: 8px;
    margin-top: 4px;
}

.live-card-price {
    font-size: 1.15rem;
    font-weight: 900;
    color: var(--DC-orange, #e28743);
}

.live-card-status {
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
}

.status-active {
    background: #dcfce7;
    color: #166534;
}

.status-inactive {
    background: #f1f5f9;
    color: #64748b;
}

/* Dropzone WebP */
.image-upload-box {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.dropzone-area {
    border: 2px dashed #cbd5e1;
    background: white;
    border-radius: 14px;
    padding: 16px 12px;
    text-align: center;
    cursor: pointer;
    transition: 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropzone-area:hover, .dropzone-area.dragging {
    border-color: var(--DC-orange, #e28743);
    background: #fffaf5;
}

.dropzone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.dropzone-icon-pill {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #fff4e6;
    color: var(--DC-orange, #e28743);
    display: flex;
    align-items: center;
    justify-content: center;
}

.dropzone-text strong {
    display: block;
    font-size: 0.84rem;
    color: #334155;
}

.dropzone-text span {
    display: block;
    font-size: 0.74rem;
    color: #94a3b8;
}

.dropzone-converting {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: var(--DC-orange, #e28743);
    font-weight: 700;
}

.spinner-small {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(226, 135, 67, 0.2);
    border-top-color: var(--DC-orange, #e28743);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.url-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-url {
    padding-right: 32px;
    font-size: 0.84rem;
}

.btn-clear-image-url {
    position: absolute;
    right: 8px;
    background: transparent;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 4px;
    border-radius: 50%;
}

.btn-clear-image-url:hover {
    color: #dc2626;
    background: #fee2e2;
}

.price-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    width: 100%;
}

.currency-symbol {
    position: absolute;
    left: 14px;
    font-weight: 800;
    color: #94a3b8;
    font-size: 1rem;
    pointer-events: none;
    z-index: 2;
}

.price-input {
    padding-left: 36px !important;
    font-weight: 800;
    color: var(--DC-orange, #e28743);
    width: 100%;
}

.toggle-availability-section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px 14px;
}

.toggle-availability-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    gap: 12px;
}

.toggle-text strong {
    display: block;
    font-size: 0.86rem;
    color: #1e293b;
}

.toggle-text span {
    display: block;
    font-size: 0.75rem;
    color: #64748b;
}

.modern-toggle {
    width: 20px;
    height: 20px;
    accent-color: #10b981;
    cursor: pointer;
}

.modal-label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 0.86rem;
    font-weight: 700;
    color: #475569;
}

.modal-label span .required {
    color: #ef4444;
}

.modal-input {
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    font-size: 0.92rem;
    outline: none;
    transition: 0.2s;
}

.modal-input:focus {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 3px rgba(226, 135, 67, 0.15);
}

textarea.modal-input {
    resize: vertical;
    min-height: 80px;
    font-family: inherit;
    line-height: 1.4;
}

.modal-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.modal-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.section-title {
    font-size: 0.82rem;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}

.chip-selector {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.chip-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.82rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
}

.chip-btn.small {
    padding: 4px 10px;
    font-size: 0.76rem;
}

.chip-btn.active {
    background: #fff4e6;
    border-color: var(--DC-orange, #e28743);
    color: var(--DC-brown, #513119);
    box-shadow: 0 2px 6px rgba(226, 135, 67, 0.15);
}

.offer-product-banner {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.offer-label-tag {
    font-size: 0.72rem;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.offer-product-name {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--DC-brown, #513119);
}

.offer-calc-row {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 0.86rem;
    color: #475569;
    margin-top: 4px;
    padding-top: 6px;
    border-top: 1px dashed #cbd5e1;
}

.offer-orig-price strong {
    color: #1e293b;
}

.offer-preview-price {
    color: #16a34a;
    font-weight: 600;
}

.offer-preview-price strong {
    color: #15803d;
    font-size: 0.98rem;
}

.preset-offers-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.preset-offers {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.preset-btn {
    padding: 8px 14px;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: #f8fafc;
    font-weight: 700;
    font-size: 0.84rem;
    color: var(--DC-brown, #513119);
    cursor: pointer;
    transition: 0.2s;
}

.preset-btn:hover {
    background: #fff4e6;
    border-color: var(--DC-orange, #e28743);
    color: var(--DC-orange, #e28743);
}

.preset-btn.active {
    background: var(--DC-orange, #e28743);
    border-color: var(--DC-orange, #e28743);
    color: white;
    box-shadow: 0 2px 6px rgba(226, 135, 67, 0.3);
}

/* ==========================================================
   FORMULARIO MODULAR Y PRECIOS POR TAMAÑO
========================================================== */

.modal-section-group {
    background: #ffffff;
    border: 1px solid #eef2f6;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.02);
}

.group-legend {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.88rem;
    font-weight: 800;
    color: var(--DC-brown, #513119);
    border-bottom: 2px solid #fff4e6;
    padding-bottom: 4px;
}

.group-help {
    margin: 0;
    font-size: 0.78rem;
    color: #64748b;
}

.sub-legend {
    display: block;
    font-size: 0.78rem;
    font-weight: 700;
    color: #475569;
    margin-bottom: 6px;
}

/* Precios por tamaño grid */
.size-prices-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 8px;
    margin-top: 4px;
}

.size-price-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 10px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.size-name-tag {
    font-size: 0.76rem;
    font-weight: 800;
    color: #334155;
}

.price-input-wrapper.compact .currency-symbol {
    left: 8px;
    font-size: 0.85rem;
}

.price-input-wrapper.compact .price-input {
    padding-left: 24px !important;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
    font-size: 0.85rem !important;
    height: auto;
}

.empty-selection-note {
    font-size: 0.8rem;
    color: #94a3b8;
    font-style: italic;
    padding: 6px 0;
}

/* Ingredientes y Extras */
.ingredients-block {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.add-custom-ing-row {
    display: flex;
    gap: 8px;
    margin-top: 4px;
}

.inline-input {
    flex: 1;
    font-size: 0.82rem !important;
    padding: 6px 10px !important;
}

.btn-add-inline {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 12px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f1f5f9;
    font-size: 0.8rem;
    font-weight: 700;
    color: #334155;
    cursor: pointer;
    transition: 0.2s;
}

.btn-add-inline:hover {
    background: #e2e8f0;
}

.extras-block {
    border-top: 1px dashed #e2e8f0;
    padding-top: 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.extras-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.btn-text-action {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: transparent;
    border: none;
    color: var(--DC-orange, #e28743);
    font-size: 0.78rem;
    font-weight: 800;
    cursor: pointer;
}

.btn-text-action:hover {
    text-decoration: underline;
}

.extras-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.extra-item-row {
    display: flex;
    align-items: center;
    gap: 6px;
}

.extra-name-input {
    flex: 2;
    font-size: 0.82rem !important;
    padding: 6px 10px !important;
}

.extra-price-wrapper {
    flex: 1;
}

.extra-price-wrapper .currency-symbol {
    left: 8px;
    font-size: 0.8rem;
}

.extra-price-input {
    padding-left: 28px !important;
    font-size: 0.82rem !important;
    padding-top: 6px !important;
    padding-bottom: 6px !important;
}

.btn-remove-extra {
    border: none;
    background: #fee2e2;
    color: #dc2626;
    border-radius: 8px;
    padding: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-remove-extra:hover {
    background: #fca5a5;
}

.toggles-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

/* Floating Badges en Preview */
.live-floating-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    font-size: 0.68rem;
    font-weight: 900;
    padding: 3px 8px;
    border-radius: 999px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    letter-spacing: 0.04em;
}

.badge-top { background: #f59e0b; color: white; }
.badge-spicy { background: #ef4444; color: white; }
.badge-new { background: #8b5cf6; color: white; }
.badge-veggie { background: #10b981; color: white; }

.live-card-prep-time {
    position: absolute;
    bottom: 8px;
    right: 8px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 6px;
    backdrop-filter: blur(4px);
}

.live-card-desc {
    margin: 0;
    font-size: 0.78rem;
    color: #64748b;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.live-card-sizes {
    display: flex;
    gap: 4px;
    flex-wrap: wrap;
    margin: 2px 0;
}

.preview-size-chip {
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: 0.15s;
}

.preview-size-chip:hover {
    border-color: #cbd5e1;
}

.preview-size-chip.active {
    background: var(--DC-brown, #513119);
    color: white;
    border-color: var(--DC-brown, #513119);
}

.live-card-extras-badge {
    background: #fff7ed;
    color: #c2410c;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    align-self: flex-start;
}

.live-card-price-group {
    display: flex;
    flex-direction: column;
}

.live-card-size-label {
    font-size: 0.68rem;
    color: #94a3b8;
    font-weight: 700;
}

/* Fotos sugeridas */
.suggested-photos-box {
    margin-top: 4px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.suggested-title {
    font-size: 0.74rem;
    font-weight: 700;
    color: #64748b;
}

.suggested-thumbnails {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}

.suggested-btn {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    background: white;
    padding: 0;
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    transition: 0.2s;
}

.suggested-btn img {
    width: 100%;
    height: 48px;
    object-fit: cover;
}

.suggested-btn span {
    font-size: 0.65rem;
    font-weight: 700;
    color: #475569;
    padding: 2px 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
}

.suggested-btn:hover {
    border-color: var(--DC-orange, #e28743);
    transform: scale(1.02);
}

.suggested-btn.selected {
    border-color: var(--DC-orange, #e28743);
    box-shadow: 0 0 0 2px rgba(226, 135, 67, 0.2);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 12px;
    padding: 16px 24px;
    border-top: 1px solid #f1f5f9;
    background: #ffffff;
    flex-shrink: 0;
}

.btn-cancel {
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    background: white;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: 0.2s;
}

.btn-cancel:hover {
    background: #f8fafc;
    color: #334155;
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

/* Imagen del Producto en Tabla y Móvil */
.product-image-container {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.product-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.mob-thumb-container {
    width: 42px;
    height: 42px;
    border-radius: 8px;
    overflow: hidden;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.mob-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* ==========================================================
   RESPONSIVIDAD MÓVIL Y TABLET
========================================================== */
@media (max-width: 768px) {
    .products-page {
        padding: 15px;
    }

    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .header-actions {
        width: 100%;
        gap: 10px;
    }

    .header-actions button {
        flex: 1;
        justify-content: center;
    }

    .table-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }

    .search-box {
        max-width: 100%;
    }

    .table-container {
        padding: 14px;
        border-radius: 16px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* MODALES RESPONSIVOS */
    .modal-backdrop {
        padding: 10px;
    }

    .modal-card {
        border-radius: 18px;
        max-width: 100%;
    }

    .modal-header {
        padding: 14px 16px;
    }

    .modal-header h3 {
        font-size: 1.05rem;
    }

    .modal-header-desc {
        font-size: 0.75rem;
    }

    .modal-body {
        padding: 16px;
        gap: 14px;
    }

    .modal-body .modal-actions {
        margin: 6px -16px -16px -16px;
        padding: 12px 16px;
        gap: 8px;
    }

    .modal-actions {
        padding: 12px 16px;
    }

    .modal-actions button,
    .modal-body .modal-actions button {
        flex: 1;
        justify-content: center;
        padding: 10px 14px;
        font-size: 0.88rem;
    }

    .modal-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }

    .modal-columns-grid {
        grid-template-columns: 1fr !important;
        gap: 14px !important;
        padding: 14px !important;
    }

    .modal-card-wide {
        max-height: 94vh !important;
        border-radius: 18px !important;
    }

    .modal-form-wrapper {
        max-height: calc(94vh - 65px);
    }
}
</style>