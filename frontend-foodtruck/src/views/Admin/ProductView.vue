<template>
  <div class="products-page">

    <!-- ===================== HEADER ===================== -->
    <header class="page-header">
      <div class="header-copy">
        <h1>Gestión de Catálogo</h1>
      </div>

      <div class="header-actions">
        <button class="btn-secondary" @click="goToAudit" title="Ver auditoría completa de catálogo">
          <History :size="16" />
          <span>Ver Auditoría</span>
        </button>

        <button v-if="activeTab === 'products'" class="btn-primary" @click="openCreateModal">
          <Plus :size="18" />
          <span>Nuevo Producto</span>
        </button>
        <button v-else-if="activeTab === 'categories'" class="btn-primary" @click="openCreateCategoryModal">
          <Plus :size="18" />
          <span>Nueva Categoría</span>
        </button>
        <button v-else-if="activeTab === 'sizes'" class="btn-primary" @click="openCreateSizeModal">
          <Plus :size="18" />
          <span>Nuevo Tamaño</span>
        </button>
      </div>
    </header>

    <!-- ===================== TABS SEGMENTED (MISMO ESTILO QUE INVENTARIO) ===================== -->
    <div class="inventory-tabs-nav">
      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: activeTab === 'products' }" 
        @click="activeTab = 'products'"
      >
        <PackageOpen :size="17" class="tab-icon" />
        <span class="tab-text">Productos</span>
        <span class="tab-pill">{{ products.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: activeTab === 'categories' }" 
        @click="activeTab = 'categories'"
      >
        <FolderTree :size="17" class="tab-icon" />
        <span class="tab-text">Categorías</span>
        <span class="tab-pill">{{ categoriesList.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: activeTab === 'sizes' }" 
        @click="activeTab = 'sizes'"
      >
        <Tag :size="17" class="tab-icon" />
        <span class="tab-text">Tamaños</span>
        <span class="tab-pill">{{ sizesList.length }}</span>
      </button>

      <button 
        type="button" 
        class="tab-nav-btn" 
        :class="{ active: activeTab === 'promotions' }" 
        @click="activeTab = 'promotions'"
      >
        <BadgePercent :size="17" class="tab-icon" />
        <span class="tab-text">Promociones</span>
        <span class="tab-pill">{{ promotions.length }}</span>
      </button>
    </div>

    <!-- ===================== KPIS RESUMEN SEGÚN PESTAÑA ===================== -->
    <section class="summary-grid">
      <template v-if="activeTab === 'products'">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <PackageOpen :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Productos</span>
            <strong class="summary-value">{{ products.length }}</strong>
            <p class="summary-helper">Registros en catálogo</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <Check :size="22" />
          </div>
          <div>
            <span class="summary-label">Activos en Carta</span>
            <strong class="summary-value">{{ activeProducts }}</strong>
            <p class="summary-helper">Visibles para pedidos</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <Sparkles :size="22" />
          </div>
          <div>
            <span class="summary-label">En Promoción / Oferta</span>
            <strong class="summary-value">{{ offerProducts }}</strong>
            <p class="summary-helper">{{ inactiveProducts }} productos inactivos</p>
          </div>
        </article>
      </template>

      <template v-else-if="activeTab === 'categories'">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <FolderTree :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Categorías</span>
            <strong class="summary-value">{{ categoriesList.length }}</strong>
            <p class="summary-helper">Familias gastronómicas</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <PackageOpen :size="22" />
          </div>
          <div>
            <span class="summary-label">Productos Asociados</span>
            <strong class="summary-value">{{ totalCategorizedProducts }}</strong>
            <p class="summary-helper">Asignados a categorías</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-pink">
            <Tag :size="22" />
          </div>
          <div>
            <span class="summary-label">Formatos Disponibles</span>
            <strong class="summary-value">{{ sizesList.length }}</strong>
            <p class="summary-helper">Tamaños globales</p>
          </div>
        </article>
      </template>

      <template v-else-if="activeTab === 'sizes'">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <Tag :size="22" />
          </div>
          <div>
            <span class="summary-label">Formatos de Tamaño</span>
            <strong class="summary-value">{{ sizesList.length }}</strong>
            <p class="summary-helper">Definidos en la carta</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <PackageOpen :size="22" />
          </div>
          <div>
            <span class="summary-label">Productos Totales</span>
            <strong class="summary-value">{{ products.length }}</strong>
            <p class="summary-helper">Disponibles para asignar</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <FolderTree :size="22" />
          </div>
          <div>
            <span class="summary-label">Categorías Activas</span>
            <strong class="summary-value">{{ categoriesList.length }}</strong>
            <p class="summary-helper">En el catálogo</p>
          </div>
        </article>
      </template>

      <template v-else-if="activeTab === 'promotions'">
        <article class="summary-card">
          <div class="summary-icon-box bg-summary-brown">
            <BadgePercent :size="22" />
          </div>
          <div>
            <span class="summary-label">Total Promociones</span>
            <strong class="summary-value">{{ promotions.length }}</strong>
            <p class="summary-helper">Programadas en catálogo</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-green">
            <CalendarDays :size="22" />
          </div>
          <div>
            <span class="summary-label">Activas este Mes</span>
            <strong class="summary-value">{{ activePromotionsThisMonthCount }}</strong>
            <p class="summary-helper">Vigencia en el período actual</p>
          </div>
        </article>

        <article class="summary-card">
          <div class="summary-icon-box bg-summary-orange">
            <Sparkles :size="22" />
          </div>
          <div>
            <span class="summary-label">Vigentes Hoy</span>
            <strong class="summary-value">{{ todayPromotionsCount }}</strong>
            <p class="summary-helper">Disponibles ahora en caja/menú</p>
          </div>
        </article>
      </template>
    </section>

    <!-- ===================== TAB 1: PRODUCTOS ===================== -->
    <section v-if="activeTab === 'products'" class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input
              v-model="search"
              type="text"
              placeholder="Buscar por nombre o ingrediente..."
            />
            <button v-if="search" class="clear-search-btn" @click="search = ''" title="Borrar búsqueda">
              <X :size="14" />
            </button>
          </div>

          <div class="select-box">
            <Filter :size="16" class="select-icon" />
            <select v-model="selectedCategory">
              <option value="">Todas las categorías</option>
              <option v-for="cat in categoriesList" :key="cat.id_categoria || cat.id" :value="cat.nombre_categoria">
                {{ cat.nombre_categoria }}
              </option>
            </select>
          </div>

          <div class="select-box">
            <select v-model="selectedStatus">
              <option value="">Todos los estados</option>
              <option value="active">Solo activos</option>
              <option value="inactive">Solo inactivos</option>
            </select>
          </div>

          <div class="select-box">
            <select v-model="selectedOffer">
              <option value="">Todas las ofertas</option>
              <option value="yes">Con oferta</option>
              <option value="no">Sin oferta</option>
            </select>
          </div>

          <button
            v-if="hasActiveFilters"
            class="btn-reset-filters"
            @click="resetFilters"
            title="Restablecer todos los filtros"
          >
            <X :size="14" />
            <span>Limpiar</span>
          </button>
        </div>

        <div class="toolbar-right">
          <span class="results-chip">{{ filteredProducts.length }} productos</span>
        </div>
      </div>

      <!-- Tabla Desktop -->
      <div class="table-wrapper desktop-table-only">
        <table class="products-table">
          <thead>
            <tr>
              <th style="width: 26%;">Producto</th>
              <th style="width: 14%;">Categoría</th>
              <th style="width: 11%;">Precio</th>
              <th style="width: 21%;">Ingredientes</th>
              <th style="width: 10%;">Estado</th>
              <th style="width: 10%;">Oferta</th>
              <th style="width: 8%; text-align: center;">Acciones</th>
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
          <tbody v-else-if="paginatedProducts.length === 0">
            <tr>
              <td colspan="7" class="text-center">
                <div class="state-card empty-state">
                  <PackageOpen :size="40" />
                  <p>No se encontraron productos con los filtros aplicados.</p>
                </div>
              </td>
            </tr>
          </tbody>
          <tbody v-else>
            <tr v-for="product in paginatedProducts" :key="product.id">
              <td>
                <div class="product-info">
                  <div class="product-image-container">
                    <img :src="product.image" :alt="product.name" class="product-thumb-img" @error="handleImageError" />
                  </div>
                  <div>
                    <h4>{{ product.name }}</h4>
                    <small>ID #{{ product.id }}</small>
                  </div>
                </div>
              </td>
              <td>
                <span class="category-pill">{{ product.category }}</span>
              </td>
              <td>
                <strong class="product-price">${{ Number(product.price).toLocaleString('es-CL') }}</strong>
              </td>
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
              <td>
                <div v-if="getProductActivePromotion(product.id)" class="promo-cell-active">
                  <span 
                    class="promo-pill-badge clickable" 
                    :title="'Promoción: $' + Number(getProductActivePromotion(product.id)?.precio_promocional || 0).toLocaleString('es-CL') + ' (Clic para ver/editar)'"
                  >
                    ${{ Number(getProductActivePromotion(product.id)?.precio_promocional || 0).toLocaleString('es-CL') }}
                  </span>
                </div>
                <span 
                  v-else 
                  class="no-offer clickable" 
                  title="Click para crear promoción"
                  @click="openCreatePromotionForProduct(product)"
                >
                  Sin promo
                </span>
              </td>
              <td>
                <div class="actions">
                  <button
                    class="icon-button"
                    title="Editar producto"
                    @click="openEditModal(product)"
                  >
                    <Pencil :size="15" />
                  </button>
                  <button
                    class="icon-button delete-btn"
                    :class="{ 'already-inactive': !product.active }"
                    :title="product.active ? 'Desactivar producto de la carta' : 'Producto ya inactivo'"
                    @click="handleDeleteProduct(product)"
                  >
                    <Trash2 :size="15" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Vista Tarjetas Móvil -->
      <div class="mobile-products-cards mobile-only">
        <div v-if="isLoading" class="skeleton-cards-mobile">
          <div v-for="n in 3" :key="'mob-prod-skel-' + n" class="mobile-product-card skeleton-card">
            <div class="skeleton-pill width-120"></div>
            <div class="skeleton-pill width-80"></div>
          </div>
        </div>
        <div v-else-if="paginatedProducts.length === 0" class="empty-state">
          <PackageOpen :size="40" />
          <h3>No se encontraron productos</h3>
        </div>
        <div v-else v-for="product in paginatedProducts" :key="'mob-prod-' + product.id" class="mobile-product-card">
          <div class="mob-card-header">
            <div class="mob-prod-info">
              <div class="mob-thumb-container">
                <img :src="product.image" :alt="product.name" class="mob-thumb-img" @error="handleImageError" />
              </div>
              <div>
                <h4 class="mob-prod-name">{{ product.name }}</h4>
                <small class="mob-prod-id">ID #{{ product.id }} · {{ product.category }}</small>
              </div>
            </div>
            <strong class="mob-prod-price">${{ Number(product.price).toLocaleString('es-CL') }}</strong>
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
                v-if="getProductActivePromotion(product.id)"
                class="promo-pill-badge clickable"
                @click="openEditPromotionModal(getProductActivePromotion(product.id)!)"
              >
                🔥 ${{ Number(getProductActivePromotion(product.id)!.precio_promocional).toLocaleString('es-CL') }}
              </span>
              <button
                v-else
                class="promo-add-btn-mob"
                @click="openCreatePromotionForProduct(product)"
              >
                + Promo
              </button>
            </div>
            <div class="actions">
              <button class="icon-button" title="Editar" @click="openEditModal(product)">
                <Pencil :size="15" />
              </button>
              <button 
                class="icon-button delete-btn" 
                :class="{ 'already-inactive': !product.active }"
                @click="handleDeleteProduct(product)"
              >
                <Trash2 :size="15" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="totalPages > 1" class="inventory-pagination">
        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="currentPage === 1" 
          @click="currentPage--"
        >
          <ChevronLeft :size="16" />
          <span>Anterior</span>
        </button>
        
        <div class="pagination-info">
          Página <strong>{{ currentPage }}</strong> de <strong>{{ totalPages }}</strong>
        </div>

        <button 
          type="button" 
          class="pagination-btn" 
          :disabled="currentPage >= totalPages" 
          @click="currentPage++"
        >
          <span>Siguiente</span>
          <ChevronRight :size="16" />
        </button>
      </div>
    </section>

    <!-- ===================== TAB 2: CATEGORÍAS ===================== -->
    <section v-if="activeTab === 'categories'" class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input
              v-model="categorySearch"
              type="text"
              placeholder="Buscar categoría..."
            />
            <button v-if="categorySearch" class="clear-search-btn" @click="categorySearch = ''">
              <X :size="14" />
            </button>
          </div>
        </div>
        <div class="toolbar-right">
          <button class="btn-primary compact-btn" @click="openCreateCategoryModal">
            <Plus :size="16" />
            <span>Nueva Categoría</span>
          </button>
        </div>
      </div>

      <div class="cards-grid-wrapper">
        <div class="catalog-cards-grid" v-if="filteredCategories.length > 0">
          <div 
            v-for="cat in filteredCategories" 
            :key="cat.id_categoria || cat.id" 
            class="catalog-entity-card"
          >
            <div class="entity-card-top">
              <div class="entity-icon-pill cat-pill">
                <FolderTree :size="20" />
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
                <Pencil :size="13" />
                <span>Editar</span>
              </button>
              <button class="btn-entity-action delete" @click="handleDeleteCategory(cat)" title="Eliminar categoría">
                <Trash2 :size="13" />
                <span>Eliminar</span>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="empty-state-box">
          <FolderTree :size="40" />
          <h3>No se encontraron categorías</h3>
          <p>Crea tu primera categoría para estructurar los platos y bebestibles.</p>
        </div>
      </div>
    </section>

    <!-- ===================== TAB 3: TAMAÑOS ===================== -->
    <section v-if="activeTab === 'sizes'" class="panel-card table-unified-card">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input
              v-model="sizeSearch"
              type="text"
              placeholder="Buscar tamaño o formato..."
            />
            <button v-if="sizeSearch" class="clear-search-btn" @click="sizeSearch = ''">
              <X :size="14" />
            </button>
          </div>
        </div>
        <div class="toolbar-right">
          <button class="btn-primary compact-btn" @click="openCreateSizeModal">
            <Plus :size="16" />
            <span>Nuevo Tamaño</span>
          </button>
        </div>
      </div>

      <div class="cards-grid-wrapper">
        <div class="catalog-cards-grid" v-if="filteredSizes.length > 0">
          <div 
            v-for="sz in filteredSizes" 
            :key="sz.id_tamaño || sz.id || sz.nombre" 
            class="catalog-entity-card size-card"
          >
            <div class="entity-card-top">
              <div class="entity-icon-pill size-pill">
                <Tag :size="20" />
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
                <Pencil :size="13" />
                <span>Editar</span>
              </button>
              <button class="btn-entity-action delete" @click="handleDeleteSize(sz)" title="Eliminar tamaño">
                <Trash2 :size="13" />
                <span>Eliminar</span>
              </button>
            </div>
          </div>
        </div>
        <div v-else class="empty-state-box">
          <Tag :size="40" />
          <h3>No se encontraron tamaños</h3>
          <p>Agrega tamaños para ofrecer distintos formatos a tus clientes.</p>
        </div>
      </div>
    </section>

    <!-- ===================== TAB 4: PROMOCIONES ===================== -->
    <section v-if="activeTab === 'promotions'" class="panel-card table-unified-card promotions-section">
      <div class="panel-toolbar">
        <div class="toolbar-left">
          <div class="search-box">
            <Search :size="17" class="search-icon" />
            <input v-model="promotionSearch" type="text" placeholder="Buscar promoción o producto...">
            <button v-if="promotionSearch" class="clear-search-btn" @click="promotionSearch = ''"><X :size="14" /></button>
          </div>

          <div class="view-mode-toggle">
            <button 
              type="button" 
              class="view-toggle-btn" 
              :class="{ active: promotionViewMode === 'calendar' }"
              @click="promotionViewMode = 'calendar'"
              title="Vista Calendario interactivo"
            >
              <CalendarDays :size="15" />
              <span>Calendario</span>
            </button>
          </div>
        </div>

        <div class="toolbar-right">
          <button class="btn-primary compact-btn" @click="openCreatePromotionForDate()">
            <Plus :size="16" />
            <span>Nueva Promoción</span>
          </button>
        </div>
      </div>

      <div class="promo-content-wrapper">
        <!-- VISTA CALENDARIO -->
        <div v-if="promotionViewMode === 'calendar'" class="promo-calendar-wrapper">
          <div class="calendar-nav-bar">
            <div class="calendar-month-nav">
              <button class="cal-nav-btn" @click="prevCalendarMonth" title="Mes anterior">
                <ChevronLeft :size="16" />
              </button>
              <h2 class="cal-current-month">{{ calendarMonthName }}</h2>
              <button class="cal-nav-btn" @click="nextCalendarMonth" title="Mes siguiente">
                <ChevronRight :size="16" />
              </button>
              <button class="cal-today-btn" @click="goToTodayCalendar">Hoy</button>
            </div>
          </div>

          <div class="calendar-main-layout">
            <div class="calendar-grid-container">
              <div class="calendar-weekdays-header">
                <span>Lun</span>
                <span>Mar</span>
                <span>Mié</span>
                <span>Jue</span>
                <span>Vie</span>
                <span>Sáb</span>
                <span>Dom</span>
              </div>

              <div class="calendar-days-grid">
                <div 
                  v-for="day in calendarDays" 
                  :key="day.dateStr" 
                  class="cal-day-cell"
                  :class="{
                    'other-month': !day.isCurrentMonth,
                    'is-today': day.isToday,
                    'is-selected': day.isSelected,
                    'has-promos': day.promotions.length > 0
                  }"
                  @click="selectCalendarDay(day.dateStr)"
                >
                  <div class="cal-day-top">
                    <span class="cal-day-number">{{ day.dayNumber }}</span>
                    <span v-if="day.isToday" class="today-tag">Hoy</span>
                    <button 
                      type="button" 
                      class="cal-add-promo-btn" 
                      @click.stop="openCreatePromotionForDate(day.dateStr)" 
                      title="Añadir promoción en este día"
                    >
                      <Plus :size="12" />
                    </button>
                  </div>

                  <div class="cal-promos-container">
                    <div 
                      v-for="promo in day.promotions.slice(0, 3)" 
                      :key="promo.id_promocion + '-' + day.dateStr"
                      class="cal-promo-chip"
                      :class="{ 'promo-inactive': !promo.activo }"
                      @click.stop="openEditPromotionModal(promo)"
                    >
                      <BadgePercent :size="11" />
                      <span class="promo-chip-title">{{ promo.titulo }}</span>
                      <span class="promo-chip-price">{{ formatPrice(promo.precio_promocional) }}</span>
                    </div>
                    <div v-if="day.promotions.length > 3" class="cal-promo-more">
                      +{{ day.promotions.length - 3 }} más
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Panel de detalle del día seleccionado -->
            <div class="calendar-selected-day-panel">
              <div class="panel-header">
                <div class="panel-header-title">
                  <Calendar :size="16" />
                  <div>
                    <h4>{{ formatDate(selectedCalendarDay) }}</h4>
                    <small>{{ selectedDayPromotions.length }} promoción(es)</small>
                  </div>
                </div>
                <button 
                  type="button" 
                  class="btn-panel-add" 
                  @click="openCreatePromotionForDate(selectedCalendarDay)"
                >
                  <Plus :size="13" />
                  <span>Añadir</span>
                </button>
              </div>

              <div class="panel-body">
                <div v-if="selectedDayPromotions.length === 0" class="panel-empty-state">
                  <BadgePercent :size="30" />
                  <p>No hay promociones programadas para este día.</p>
                </div>
                <div v-else class="panel-promos-list">
                  <div 
                    v-for="promo in selectedDayPromotions" 
                    :key="'sel-panel-' + promo.id_promocion"
                    class="panel-promo-card"
                    :class="{ 'card-inactive': !promo.activo }"
                  >
                    <div class="panel-promo-card-top">
                      <div>
                        <h5>{{ promo.titulo }}</h5>
                        <span class="panel-product-tag">{{ promo.producto?.nombre || promo.productName || 'Producto' }}</span>
                      </div>
                      <strong class="panel-promo-price">{{ formatPrice(promo.precio_promocional) }}</strong>
                    </div>
                    <p v-if="promo.descripcion" class="panel-promo-desc">{{ promo.descripcion }}</p>
                    <div class="panel-promo-dates">
                      <Clock :size="12" />
                      <span>Del {{ formatDate(promo.fecha_inicio) }} al {{ formatDate(promo.fecha_fin) }}</span>
                    </div>
                    <div class="panel-promo-actions">
                      <button class="btn-entity-action toggle" @click="togglePromotionStatus(promo)">
                        <Power :size="13" />
                        <span>{{ promo.activo ? 'Desactivar' : 'Activar' }}</span>
                      </button>
                      <button class="btn-entity-action edit" @click="openEditPromotionModal(promo)">
                        <Pencil :size="13" />
                        <span>Editar</span>
                      </button>
                      <button class="btn-entity-action delete" @click="handleDeletePromotion(promo)">
                        <Trash2 :size="13" />
                        <span>Eliminar</span>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="empty-state-box">
          <BadgePercent :size="40" />
          <h3>No hay promociones configuradas</h3>
          <p>Crea una promoción con producto, fechas, precio y un detalle opcional.</p>
        </div>
      </div>
    </section>

    <!-- ===================== MODALES (CATEGORÍA, TAMAÑO, PRODUCTO, PROMO, CUSTOMIZER) ===================== -->
    <!-- MODAL CATEGORÍA -->
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
          <button class="close-btn" @click="isCategoryModalOpen = false"><X :size="18" /></button>
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

    <!-- MODAL TAMAÑO -->
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
          <button class="close-btn" @click="isSizeModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitSizeForm">
          <label class="modal-label">
            <span>Nombre del Tamaño <span class="required">*</span></span>
            <input
              v-model="sizeForm.nombre"
              type="text"
              required
              placeholder="Ej: Normal, Doble, XL, Familiar, Individual..."
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

    <!-- MODAL PRODUCTO -->
    <!-- MODAL PRODUCTO / VARIEDAD REDISEÑADO -->
    <div v-if="isProductModalOpen" class="modal-backdrop" @click.self="isProductModalOpen = false">
      <div class="modal-card modal-card-wide product-editor-modal">
        <!-- Header -->
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><Plus :size="18" /></div>
            <div>
              <div class="header-title-row">
                <h3>{{ isEditingProduct ? 'Editar Producto / Variedad' : 'Crear Nuevo Producto / Variedad' }}</h3>
                <span v-if="productForm.es_agrupado" class="mode-badge-pill grouped">
                  Variedad de: {{ productForm.grupo || getProductCategoryName }}
                </span>
                <span v-else class="mode-badge-pill individual">
                  Tarjeta Individual
                </span>
              </div>
              <p class="modal-header-desc">Configura visualización en la carta, formatos, precios e ingredientes</p>
            </div>
          </div>
          <button class="close-btn" @click="isProductModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-form-wrapper" @submit.prevent="isEditingProduct ? submitEditProduct() : submitCreateProduct()">
          <div class="modal-columns-grid">
            <!-- Columna Izquierda: Formulario -->
            <div class="modal-form-col">
              
              <!-- 1. Datos Principales -->
              <div class="modal-section-group">
                <span class="group-legend"><Sparkles :size="15" /> 1. Datos Principales</span>
                
                <label class="modal-label">
                  <span>Nombre del Producto / Sabor <span class="required">*</span></span>
                  <input
                    v-model="productForm.nombre"
                    type="text"
                    required
                    placeholder="Ej: Italiano, Doble Cheddar Bacon, Napolitana..."
                    class="modal-input"
                  />
                </label>

                <div class="modal-row">
                  <label class="modal-label">
                    <span>Categoría de la Carta <span class="required">*</span></span>
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
                      <option value="estandar">🍔 Estándar</option>
                      <option value="personalizable">✨ Personalizable</option>
                      <option value="combo">🍟 Combo</option>
                    </select>
                  </label>
                </div>

                <label class="modal-label">
                  <span>Descripción del Producto <span class="required">*</span></span>
                  <textarea
                    v-model="productForm.descripcion"
                    rows="2"
                    required
                    placeholder="Describe los ingredientes principales o características de esta variedad..."
                    class="modal-input"
                  ></textarea>
                </label>
              </div>

              <!-- 2. Agrupación en el Menú (LA MEJORA SOLICITADA) -->
              <div class="modal-section-group grouping-section">
                <span class="group-legend"><Layers :size="15" /> 2. Visualización en la Carta de Clientes</span>
                <p class="section-guide-text">
                  Decide si este producto crea su propia tarjeta independiente en la portada o si se agrupa como variedad dentro de otra tarjeta existente:
                </p>

                <div class="grouping-toggle-grid">
                  <button
                    type="button"
                    class="grouping-choice-btn"
                    :class="{ active: !productForm.es_agrupado }"
                    @click="setGroupedMode(false)"
                  >
                    <div class="choice-icon">🎴</div>
                    <div class="choice-texts">
                      <strong>Tarjeta Individual Propia</strong>
                      <span>Aparece como una tarjeta independiente con su propia foto en la portada.</span>
                    </div>
                  </button>

                  <button
                    type="button"
                    class="grouping-choice-btn"
                    :class="{ active: productForm.es_agrupado }"
                    @click="setGroupedMode(true)"
                  >
                    <div class="choice-icon">📚</div>
                    <div class="choice-texts">
                      <strong>Agrupar como Variedad</strong>
                      <span>Se une dentro de una tarjeta existente (no crea una tarjeta nueva).</span>
                    </div>
                  </button>
                </div>

                <!-- Sub-panel al elegir Agrupar como Variedad -->
                <Transition name="fade-slide">
                  <div v-if="productForm.es_agrupado" class="group-select-subpanel">
                    <label class="modal-label">
                      <span>Tarjeta o Grupo del Menú donde se mostrará:</span>
                      <div class="group-input-combobox">
                        <select 
                          v-model="productForm.grupo" 
                          class="modal-input select-group-box"
                        >
                          <option value="" disabled>Selecciona tarjeta existente...</option>
                          <option 
                            v-for="grp in availableCardGroups" 
                            :key="grp" 
                            :value="grp"
                          >
                            Tarjeta: "{{ grp }}"
                          </option>
                        </select>
                        <input
                          v-model="productForm.grupo"
                          type="text"
                          placeholder="O escribe nuevo nombre de tarjeta..."
                          class="modal-input custom-group-input"
                        />
                      </div>
                    </label>

                    <div class="grouping-info-hint">
                      <Sparkles :size="14" />
                      <span>
                        Los clientes verán una única tarjeta llamada <strong>"{{ productForm.grupo || getProductCategoryName }}"</strong> y al abrirla podrán elegir <strong>"{{ productForm.nombre || 'esta variedad' }}"</strong> entre sus opciones.
                      </span>
                    </div>
                  </div>
                </Transition>
              </div>

              <!-- 3. Formatos y Precios -->
              <div class="modal-section-group">
                <span class="group-legend"><Tag :size="15" /> 3. Formatos y Precios</span>
                <p class="section-guide-text">Selecciona los tamaños disponibles para este producto y define su precio:</p>
                <div class="chip-selector">
                  <button 
                    v-for="sz in availableSizesList" 
                    :key="sz" 
                    type="button" 
                    class="chip-btn" 
                    :class="{ active: productForm.selectedSizes.includes(sz) }"
                    @click="toggleSize(sz)"
                  >
                    <Check v-if="productForm.selectedSizes.includes(sz)" :size="12" />
                    <span>{{ sz }}</span>
                  </button>
                </div>

                <div class="size-prices-grid" v-if="productForm.selectedSizes.length > 0">
                  <div v-for="sz in productForm.selectedSizes" :key="sz" class="size-price-item">
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
              </div>

              <!-- 4. Receta e Ingredientes -->
              <div class="modal-section-group">
                <span class="group-legend"><FolderTree :size="15" /> 4. Receta e Ingredientes</span>
                <div class="chip-selector wrap">
                  <button 
                    v-for="ing in availableIngredientsList" 
                    :key="ing" 
                    type="button" 
                    class="chip-btn small" 
                    :class="{ active: productForm.selectedIngredients.includes(ing) }"
                    @click="toggleIngredient(ing)"
                  >
                    <Check v-if="productForm.selectedIngredients.includes(ing)" :size="10" />
                    <span>{{ ing }}</span>
                  </button>
                </div>

                <div class="add-custom-ing-row">
                  <input 
                    v-model="newCustomIngredient" 
                    type="text" 
                    placeholder="Añadir otro ingrediente a la lista..." 
                    class="modal-input inline-input"
                    @keydown.enter.prevent="addCustomIngredient()"
                  />
                  <button type="button" class="btn-add-inline" @click="addCustomIngredient()">
                    <Plus :size="13" />
                    <span>Añadir</span>
                  </button>
                </div>

                <div class="modal-row" style="margin-top: 10px;">
                  <label class="modal-label">
                    <span>Recargo por Ingrediente Extra ($)</span>
                    <div class="price-input-wrapper">
                      <span class="currency-symbol">+$</span>
                      <input 
                        v-model.number="productForm.precio_ingrediente_extra" 
                        type="number" 
                        min="0" 
                        step="100" 
                        placeholder="Ej: 500" 
                        class="modal-input price-input"
                      />
                    </div>
                  </label>

                  <label v-if="productForm.tipo_armado === 'personalizable'" class="modal-label">
                    <span>Ingredientes Incluidos a Elección</span>
                    <input 
                      v-model.number="productForm.cantidad_incluida" 
                      type="number" 
                      min="1" 
                      max="10" 
                      placeholder="Ej: 3" 
                      class="modal-input"
                    />
                  </label>
                </div>
              </div>

              <!-- 5. Disponibilidad y Stock -->
              <div class="modal-section-group">
                <span class="group-legend"><Eye :size="15" /> 5. Disponibilidad</span>
                <div class="modal-row toggles-row">
                  <label class="toggle-availability-label">
                    <div class="toggle-text">
                      <strong>Visible en Menú</strong>
                      <span>Público para los clientes</span>
                    </div>
                    <input type="checkbox" v-model="productForm.active" class="modern-toggle" />
                  </label>

                  <label class="toggle-availability-label">
                    <div class="toggle-text">
                      <strong>En Stock</strong>
                      <span>Disponible hoy para pedidos</span>
                    </div>
                    <input type="checkbox" v-model="productForm.inStock" class="modern-toggle" />
                  </label>
                </div>
              </div>
            </div>

            <!-- Columna Derecha: Vista Previa Realista y Fotografía -->
            <div class="modal-preview-col">
              <div class="preview-header-bar">
                <span class="preview-badge"><Eye :size="13" /> Vista Previa en Menú</span>
                <span class="live-pill">En vivo</span>
              </div>

              <!-- Tarjeta idéntica a la del Home -->
              <div class="live-product-card">
                <div class="live-card-img-wrap">
                  <img
                    v-if="productForm.image"
                    :src="productForm.image"
                    alt="Preview"
                    class="live-card-img"
                    :style="{ objectPosition: productForm.imagePosition, objectFit: productForm.imageFit, transform: `scale(${Math.max(1, productForm.imageZoom)})` }"
                    @error="handleImageError"
                  />
                  <div v-else class="live-card-placeholder">
                    <ImageIcon :size="36" />
                    <span>Sin fotografía</span>
                  </div>
                  <span class="live-card-category">{{ getProductCategoryName }}</span>
                  <span v-if="productForm.es_agrupado" class="live-card-group-badge">
                    Variedad en "{{ productForm.grupo || getProductCategoryName }}"
                  </span>
                </div>

                <div class="live-card-body">
                  <h4 class="live-card-title">{{ productForm.nombre || 'Nombre del producto...' }}</h4>
                  <p class="live-card-desc">{{ productForm.descripcion || 'Descripción del producto...' }}</p>
                  
                  <div v-if="productForm.selectedSizes.length > 0" class="live-card-sizes-pills">
                    <span 
                      v-for="sz in productForm.selectedSizes" 
                      :key="sz" 
                      class="mini-size-pill"
                      :class="{ active: previewActiveSize === sz }"
                      @click="previewActiveSize = sz"
                    >
                      {{ sz }}
                    </span>
                  </div>

                  <div class="live-card-footer">
                    <span class="live-card-price">{{ formatPrice(getPreviewPrice) }}</span>
                    <span class="live-card-status" :class="productForm.active ? 'status-active' : 'status-inactive'">
                      {{ productForm.active ? (productForm.inStock ? 'Disponible' : 'Agotado hoy') : 'Oculto' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Controles de Imagen -->
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
                    <span>Optimizando a WebP...</span>
                  </div>
                  <div v-else class="dropzone-content">
                    <UploadCloud :size="22" />
                    <span class="dropzone-title">{{ productForm.image ? 'Cambiar fotografía' : 'Subir foto del producto' }}</span>
                    <span class="dropzone-sub">Arrastra o haz clic (Se optimiza a WebP)</span>
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
                  >
                    <X :size="13" />
                  </button>
                </div>

                <button
                  v-if="productForm.image"
                  type="button"
                  class="btn-customize-image"
                  @click="openImageCustomizer"
                >
                  <SlidersHorizontal :size="15" />
                  <span>Personalizar encuadre y zoom</span>
                </button>
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

    <!-- MODAL PROMOCIÓN -->
    <div v-if="isPromotionModalOpen" class="modal-backdrop" @click.self="isPromotionModalOpen = false">
      <div class="modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><BadgePercent :size="18" /></div>
            <div>
              <h3>{{ isEditingPromotion ? 'Editar Promoción' : 'Nueva Promoción' }}</h3>
              <p class="modal-header-desc">Configura producto, fechas y precio promocional</p>
            </div>
          </div>
          <button class="close-btn" @click="isPromotionModalOpen = false"><X :size="18" /></button>
        </div>

        <form class="modal-body" @submit.prevent="submitPromotion">
          <label class="modal-label">
            <span>Producto Asociado <span class="required">*</span></span>
            <select v-model.number="promotionForm.id_producto" class="modal-input" required>
              <option value="" disabled>Selecciona un producto</option>
              <option v-for="product in products" :key="product.id" :value="product.id">
                {{ product.name }} (${{ Number(product.price).toLocaleString('es-CL') }})
              </option>
            </select>
          </label>

          <label class="modal-label">
            <span>Título de la Promoción <span class="required">*</span></span>
            <input 
              v-model="promotionForm.titulo" 
              class="modal-input" 
              required 
              maxlength="120" 
              placeholder="Ej: Promo Combo Doble Cheddar"
            />
          </label>

          <div class="form-row-2">
            <label class="modal-label">
              <span>Precio Promocional ($) <span class="required">*</span></span>
              <input 
                v-model.number="promotionForm.precio_promocional" 
                class="modal-input" 
                type="number" 
                min="1" 
                required 
                placeholder="Ej: 2990"
              />
            </label>
            <label class="modal-label">
              <span>Estado</span>
              <select v-model="promotionForm.activo" class="modal-input">
                <option :value="true">Activa (Visible)</option>
                <option :value="false">Inactiva (Oculta)</option>
              </select>
            </label>
          </div>

          <div class="form-row-2">
            <label class="modal-label">
              <span>Fecha Inicio <span class="required">*</span></span>
              <input 
                v-model="promotionForm.fecha_inicio" 
                class="modal-input" 
                type="date" 
                required
              />
            </label>
            <label class="modal-label">
              <span>Fecha Término <span class="required">*</span></span>
              <input 
                v-model="promotionForm.fecha_fin" 
                class="modal-input" 
                type="date" 
                required 
                :min="promotionForm.fecha_inicio"
              />
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="isPromotionModalOpen = false">Cancelar</button>
            <button type="submit" class="btn-save">
              <Check :size="16" />
              <span>{{ isEditingPromotion ? 'Guardar Cambios' : 'Crear Promoción' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL CUSTOMIZER IMAGEN -->
    <div v-if="isImageCustomizerOpen" class="modal-backdrop customizer-backdrop" @click.self="cancelImageCustomization">
      <div class="modal-card customizer-modal-card">
        <div class="modal-header">
          <div class="modal-header-title">
            <div class="header-icon-pill"><SlidersHorizontal :size="18" /></div>
            <div>
              <h3>Personalizar Imagen</h3>
              <p class="modal-header-desc">Ajusta encuadre y zoom en pantalla</p>
            </div>
          </div>
          <button class="close-btn" @click="cancelImageCustomization"><X :size="18" /></button>
        </div>

        <div class="customizer-body">
          <div class="customizer-columns">
            <div class="customizer-preview-large">
              <img
                :src="productForm.image"
                alt="Preview"
                class="customizer-preview-img"
                :style="{
                  objectPosition: productForm.imagePosition,
                  objectFit: productForm.imageFit,
                  transform: 'scale(' + Math.max(1, productForm.imageZoom) + ')'
                }"
              />
            </div>

            <div class="customizer-controls">
              <div class="customizer-control-group">
                <span class="sub-legend">Ajuste de Marco</span>
                <div class="control-options-grid">
                  <button
                    type="button"
                    class="control-option-btn"
                    :class="{ active: productForm.imageFit === 'cover' }"
                    @click="productForm.imageFit = 'cover'"
                  >
                    Llenar marco
                  </button>
                  <button
                    type="button"
                    class="control-option-btn"
                    :class="{ active: productForm.imageFit === 'contain' }"
                    @click="productForm.imageFit = 'contain'"
                  >
                    Completa
                  </button>
                </div>
              </div>

              <div class="customizer-control-group">
                <span class="sub-legend">Zoom ({{ Math.round(productForm.imageZoom * 100) }}%)</span>
                <input
                  v-model.number="productForm.imageZoom"
                  type="range"
                  min="1"
                  max="1.6"
                  step="0.05"
                  class="customizer-zoom-range"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button type="button" class="btn-cancel" @click="cancelImageCustomization">Cancelar</button>
          <button type="button" class="btn-save" @click="applyImageCustomization">
            <Check :size="16" />
            <span>Aplicar</span>
          </button>
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
import promotionService from '@/services/promotionService'
import { useNotification } from '@/composables/useNotification'
import { useImageOptimizer } from '@/composables/useImageOptimizer'

import {
  BadgePercent,
  Calendar,
  CalendarDays,
  Check,
  ChevronLeft,
  ChevronRight,
  Clock,
  Eye,
  Filter,
  FolderTree,
  History,
  Image as ImageIcon,
  LayoutGrid,
  Layers,
  PackageOpen,
  Pencil,
  Plus,
  Power,
  Search,
  SlidersHorizontal,
  Sparkles,
  Square,
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

const activeTab = ref<'products' | 'categories' | 'sizes' | 'promotions'>('products')

const isLoading = ref(true)
const products = ref<any[]>([])
const categoriesList = ref<any[]>([])
const sizesList = ref<any[]>([])
const promotions = ref<any[]>([])

const categorySearch = ref('')
const sizeSearch = ref('')
const promotionSearch = ref('')

// Modales de Categoría y Tamaño
const isCategoryModalOpen = ref(false)
const isEditingCategory = ref(false)
const categoryForm = ref({
  id_categoria: 0,
  nombre_categoria: '',
  descripcion_categoria: ''
})

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

// Modales de Producto y Promociones
const isProductModalOpen = ref(false)
const isEditingProduct = ref(false)
const isPromotionModalOpen = ref(false)
const isEditingPromotion = ref(false)
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
  es_agrupado: false,
  grupo: '',
  cantidad_incluida: 0,
  precio_base: 0,
  descripcion: '',
  tipo_armado: 'estandar',
  precio_ingrediente_extra: 0,
  selectedSizes: [] as string[],
  sizePrices: {} as Record<string, number>,
  selectedIngredients: [] as string[],
  image: '',
  imagePosition: '50% 50%',
  imageZoom: 1,
  imageFit: 'cover' as 'cover' | 'contain',
  imageFile: null as File | null,
  active: true,
  inStock: true
})

const availableCardGroups = computed(() => {
  const selectedCat = categoriesList.value.find((c: any) => String(c.id_categoria || c.id) === String(productForm.value.id_categoria))
  const catName = selectedCat?.nombre_categoria || ''
  const groups = new Set<string>()
  if (catName) {
    groups.add(catName)
  }
  products.value.forEach((p: any) => {
    if (!catName || p.category === catName) {
      if (p.grupo && String(p.grupo).trim()) {
        groups.add(String(p.grupo).trim())
      }
    }
  })
  return Array.from(groups)
})

const setGroupedMode = (grouped: boolean) => {
  productForm.value.es_agrupado = grouped
  if (grouped && !productForm.value.grupo) {
    productForm.value.grupo = getProductCategoryName.value
  }
}

const promotionForm = ref({
  id: 0,
  id_producto: '' as string | number,
  titulo: '',
  descripcion: '',
  precio_promocional: 0,
  fecha_inicio: '',
  fecha_fin: '',
  activo: true
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
  const target = e.target as HTMLImageElement
  target.src = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=800&auto=format&fit=crop&q=80'
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

const toggleProductStatus = async (product: any) => {
  product.active = !product.active
  try {
    await productService.updateProduct(product.id, { activo: product.active })
    notify(`Producto "${product.name}" ${product.active ? 'activado' : 'desactivado'}`, 'success')
  } catch {
    notify(`Estado cambiado a ${product.active ? 'Activo' : 'Inactivo'}`, 'success')
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
    es_agrupado: false,
    grupo: '',
    cantidad_incluida: 0,
    precio_base: 4500,
    descripcion: '',
    tipo_armado: 'estandar',
    precio_ingrediente_extra: 800,
    selectedSizes: initialSizes,
    sizePrices: initialPrices,
    selectedIngredients: [],
    image: '',
    imagePosition: '50% 50%',
    imageZoom: 1,
    imageFit: 'cover',
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
  const hasGroup = Boolean(product.grupo && String(product.grupo).trim() !== '')

  productForm.value = {
    id: product.id,
    nombre: product.name,
    id_categoria: foundCat?.id_categoria || foundCat?.id || categoriesList.value[0]?.id_categoria || '',
    es_agrupado: hasGroup,
    grupo: product.grupo || '',
    cantidad_incluida: Number(product.cantidad_incluida || 0),
    precio_base: product.price || 4500,
    descripcion: product.descripcion || product.name || '',
    tipo_armado: product.tipo_armado || 'estandar',
    precio_ingrediente_extra: product.precio_ingrediente_extra || 0,
    image: product.image || '',
    imagePosition: product.imagePosition || '50% 50%',
    imageZoom: Number(product.imageZoom || 1),
    imageFit: product.imageFit || 'cover',
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

  const assignedGroup = productForm.value.es_agrupado
    ? (productForm.value.grupo?.trim() || catName)
    : ''

  const includedCount = Number(
    productForm.value.cantidad_incluida || (productForm.value.tipo_armado === 'personalizable' ? 3 : 0)
  )

  const localItem = {
    id: newId,
    image: productForm.value.image || '',
    imagePosition: productForm.value.imagePosition,
    imageZoom: productForm.value.imageZoom,
    imageFit: productForm.value.imageFit,
    name: productForm.value.nombre,
    category: catName,
    grupo: assignedGroup,
    cantidad_incluida: includedCount,
    tipo_armado: productForm.value.tipo_armado,
    precio_ingrediente_extra: productForm.value.precio_ingrediente_extra || 0,
    descripcion: productForm.value.descripcion,
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
      grupo: assignedGroup || null,
      precio_base: basePrice,
      precios_tamanos: preciosTamanos,
      descripcion: productForm.value.descripcion || productForm.value.nombre,
      tipo_armado: productForm.value.tipo_armado,
      cantidad_incluida: includedCount,
      precio_ingrediente_extra: productForm.value.precio_ingrediente_extra || 0,
      activo: productForm.value.active,
      disponible: productForm.value.inStock,
      imagen: productForm.value.image,
      imagen_posicion: productForm.value.imagePosition,
      imagen_zoom: productForm.value.imageZoom,
      imagen_ajuste: productForm.value.imageFit,
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
  } catch {
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

  const assignedGroup = productForm.value.es_agrupado
    ? (productForm.value.grupo?.trim() || catName)
    : ''

  const includedCount = Number(
    productForm.value.cantidad_incluida || (productForm.value.tipo_armado === 'personalizable' ? 3 : 0)
  )

  if (p) {
    p.name = productForm.value.nombre
    p.category = catName
    p.grupo = assignedGroup
    p.cantidad_incluida = includedCount
    p.tipo_armado = productForm.value.tipo_armado
    p.descripcion = productForm.value.descripcion
    p.precio_ingrediente_extra = productForm.value.precio_ingrediente_extra || 0
    p.price = Number(basePrice)
    p.image = productForm.value.image || p.image
    p.imagePosition = productForm.value.imagePosition
    p.imageZoom = productForm.value.imageZoom
    p.imageFit = productForm.value.imageFit
    p.sizes = [...productForm.value.selectedSizes]
    p.ingredients = [...productForm.value.selectedIngredients]
    p.active = productForm.value.active
    p.inStock = productForm.value.inStock
  }

  try {
    const updateRes = await productService.updateProduct(productForm.value.id, {
      nombre: productForm.value.nombre,
      id_categoria: productForm.value.id_categoria,
      grupo: assignedGroup || null,
      precio_base: basePrice,
      precios_tamanos: preciosTamanos,
      descripcion: productForm.value.descripcion || productForm.value.nombre,
      tipo_armado: productForm.value.tipo_armado,
      cantidad_incluida: includedCount,
      precio_ingrediente_extra: productForm.value.precio_ingrediente_extra || 0,
      activo: productForm.value.active,
      disponible: productForm.value.inStock,
      imagen: productForm.value.image,
      imagen_posicion: productForm.value.imagePosition,
      imagen_zoom: productForm.value.imageZoom,
      imagen_ajuste: productForm.value.imageFit,
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
  } catch {
    notify('Producto actualizado', 'success')
  }
  window.dispatchEvent(new Event('foodtruck-products-update'))
  isProductModalOpen.value = false
}

const getProductActivePromotion = (productId: number | string) => {
  const today = getLocalDateString()
  return promotions.value.find((p: any) => {
    if (!p.activo) return false
    if (Number(p.id_producto) !== Number(productId)) return false
    const start = String(p.fecha_inicio || '').slice(0, 10)
    const end = String(p.fecha_fin || '').slice(0, 10)
    return today >= start && today <= end
  })
}

const openCreatePromotionForProduct = (product: any) => {
  isEditingPromotion.value = false
  const today = getLocalDateString()  
promotionForm.value = {
    id: 0,
    id_producto: product.id,
    titulo: `Promo ${product.name}`,
    descripcion: '',
    precio_promocional: Math.round(Number(product.price || 0) * 0.85),
    fecha_inicio: today,
    fecha_fin: today,
    activo: true
  }
  isPromotionModalOpen.value = true
}

const formatDate = (value: string) => {
  if (!value) return 'Sin fecha'
  return new Date(`${value}T00:00:00`).toLocaleDateString('es-CL', { day: '2-digit', month: 'short', year: 'numeric' })
}

const openCreatePromotionModal = () => {
  isEditingPromotion.value = false
  const today = getLocalDateString()
  promotionForm.value = {
    id: 0,
    id_producto: products.value[0]?.id || '',
    titulo: '',
    descripcion: '',
    precio_promocional: 0,
    fecha_inicio: today,
    fecha_fin: today,
    activo: true
  }
  isPromotionModalOpen.value = true
}

const openEditPromotionModal = (promotion: any) => {
  isEditingPromotion.value = true
  promotionForm.value = {
    id: promotion.id_promocion,
    id_producto: promotion.id_producto,
    titulo: promotion.titulo,
    descripcion: promotion.descripcion || '',
    precio_promocional: Number(promotion.precio_promocional || 0),
    fecha_inicio: String(promotion.fecha_inicio || '').slice(0, 10),
    fecha_fin: String(promotion.fecha_fin || '').slice(0, 10),
    activo: promotion.activo !== false
  }
  isPromotionModalOpen.value = true
}

const submitPromotion = async () => {
  if (promotionForm.value.fecha_fin < promotionForm.value.fecha_inicio) {
    notify('La fecha de término no puede ser anterior al inicio', 'warning')
    return
  }

  const payload = {
    id_producto: Number(promotionForm.value.id_producto),
    titulo: promotionForm.value.titulo.trim(),
    descripcion: promotionForm.value.descripcion.trim() || null,
    precio_promocional: Number(promotionForm.value.precio_promocional),
    fecha_inicio: promotionForm.value.fecha_inicio,
    fecha_fin: promotionForm.value.fecha_fin,
    activo: promotionForm.value.activo
  }

  try {
    const response = isEditingPromotion.value
      ? await promotionService.updatePromotion(promotionForm.value.id, payload)
      : await promotionService.createPromotion(payload)
    const saved = response.data
    if (isEditingPromotion.value) {
      const index = promotions.value.findIndex(item => item.id_promocion === promotionForm.value.id)
      if (index >= 0) promotions.value[index] = saved
    } else {
      promotions.value.unshift(saved)
    }
    notify(isEditingPromotion.value ? 'Promoción actualizada' : 'Promoción creada exitosamente', 'success')
    window.dispatchEvent(new Event('foodtruck-products-update'))
    isPromotionModalOpen.value = false
  } catch (error: any) {
    notify(error?.response?.data?.message || 'No se pudo guardar la promoción', 'warning')
  }
}

const togglePromotionStatus = async (promotion: any) => {
  const nextStatus = !promotion.activo
  const payload = {
    id_producto: Number(promotion.id_producto),
    titulo: promotion.titulo,
    descripcion: promotion.descripcion || null,
    precio_promocional: Number(promotion.precio_promocional),
    fecha_inicio: String(promotion.fecha_inicio).slice(0, 10),
    fecha_fin: String(promotion.fecha_fin).slice(0, 10),
    activo: nextStatus
  }

  try {
    const response = await promotionService.updatePromotion(promotion.id_promocion, payload)
    const index = promotions.value.findIndex(item => item.id_promocion === promotion.id_promocion)
    if (index >= 0) promotions.value[index] = response.data
    window.dispatchEvent(new Event('foodtruck-products-update'))
    notify(nextStatus ? 'Promoción activada' : 'Promoción desactivada', 'success')
  } catch (error: any) {
    notify(error?.response?.data?.message || 'No se pudo cambiar el estado de la promoción', 'warning')
  }
}

const handleDeletePromotion = async (promotion: any) => {
  if (!confirm(`¿Eliminar la promoción "${promotion.titulo}"?`)) return
  try {
    await promotionService.deletePromotion(promotion.id_promocion)
    promotions.value = promotions.value.filter(item => item.id_promocion !== promotion.id_promocion)
    window.dispatchEvent(new Event('foodtruck-products-update'))
    notify('Promoción eliminada', 'warning')
  } catch {
    notify('No se pudo eliminar la promoción', 'warning')
  }
}

const getLocalDateString = (d: Date = new Date()): string => {
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Calendario
const promotionViewMode = ref<'calendar' | 'cards'>('calendar')
const calendarCurrentDate = ref(new Date())
const selectedCalendarDay = ref<string>(getLocalDateString())

const calendarMonthName = computed(() => {
  const str = calendarCurrentDate.value.toLocaleDateString('es-CL', { month: 'long', year: 'numeric' })
  return str.charAt(0).toUpperCase() + str.slice(1)
})

const getPromotionsForDate = (dateStr: string) => {
  return promotions.value.filter((p: any) => {
    const start = String(p.fecha_inicio || '').slice(0, 10)
    const end = String(p.fecha_fin || '').slice(0, 10)
    if (!start || !end) return false
    return dateStr >= start && dateStr <= end
  })
}

const activePromotionsThisMonthCount = computed(() => {
  const year = calendarCurrentDate.value.getFullYear()
  const month = calendarCurrentDate.value.getMonth()
  const startOfMonth = new Date(year, month, 1).toISOString().slice(0, 10)
  const endOfMonth = new Date(year, month + 1, 0).toISOString().slice(0, 10)

  return promotions.value.filter((p: any) => {
    if (!p.activo) return false
    const start = String(p.fecha_inicio || '').slice(0, 10)
    const end = String(p.fecha_fin || '').slice(0, 10)
    return start <= endOfMonth && end >= startOfMonth
  }).length
})

const todayPromotionsCount = computed(() => {

  const today = getLocalDateString()
  return promotions.value.filter((p: any) => {
    if (!p.activo) return false
    const start = String(p.fecha_inicio || '').slice(0, 10)
    const end = String(p.fecha_fin || '').slice(0, 10)
    return today >= start && today <= end
  }).length
})
const calendarDays = computed(() => {
  const year = calendarCurrentDate.value.getFullYear()
  const month = calendarCurrentDate.value.getMonth()

  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)

  let startDayOfWeek = firstDay.getDay() - 1
  if (startDayOfWeek < 0) startDayOfWeek = 6

  const days: Array<any> = []
  const todayStr = getLocalDateString()
  const prevMonthLastDay = new Date(year, month, 0).getDate()

  for (let i = startDayOfWeek - 1; i >= 0; i--) {
    const d = prevMonthLastDay - i
    const prevDate = new Date(year, month - 1, d)
    const y = prevDate.getFullYear()
    const m = String(prevDate.getMonth() + 1).padStart(2, '0')
    const dayFormatted = String(d).padStart(2, '0')
    const dateStr = `${y}-${m}-${dayFormatted}`
    days.push({
      dateStr,
      dayNumber: d,
      isCurrentMonth: false,
      isToday: dateStr === todayStr,
      isSelected: dateStr === selectedCalendarDay.value,
      promotions: getPromotionsForDate(dateStr)
    })
  }

  for (let d = 1; d <= lastDay.getDate(); d++) {
    const curDate = new Date(year, month, d)
    const y = curDate.getFullYear()
    const m = String(curDate.getMonth() + 1).padStart(2, '0')
    const dayFormatted = String(d).padStart(2, '0')
    const dateStr = `${y}-${m}-${dayFormatted}`

    days.push({
      dateStr,
      dayNumber: d,
      isCurrentMonth: true,
      isToday: dateStr === todayStr,
      isSelected: dateStr === selectedCalendarDay.value,
      promotions: getPromotionsForDate(dateStr)
    })
  }

  const remaining = (7 - (days.length % 7)) % 7
  for (let i = 1; i <= remaining; i++) {
    const nextDate = new Date(year, month + 1, i)
    const y = nextDate.getFullYear()
    const m = String(nextDate.getMonth() + 1).padStart(2, '0')
    const dayFormatted = String(i).padStart(2, '0')
    const dateStr = `${y}-${m}-${dayFormatted}`
    days.push({
      dateStr,
      dayNumber: i,
      isCurrentMonth: false,
      isToday: dateStr === todayStr,
      isSelected: dateStr === selectedCalendarDay.value,
      promotions: getPromotionsForDate(dateStr)
    })
  }

  return days
})



const selectedDayPromotions = computed(() => {
  if (!selectedCalendarDay.value) return []
  return getPromotionsForDate(selectedCalendarDay.value)
})

const selectCalendarDay = (dateStr: string) => {
  selectedCalendarDay.value = dateStr
}

const prevCalendarMonth = () => {
  const d = new Date(calendarCurrentDate.value)
  d.setMonth(d.getMonth() - 1)
  calendarCurrentDate.value = d
}

const nextCalendarMonth = () => {
  const d = new Date(calendarCurrentDate.value)
  d.setMonth(d.getMonth() + 1)
  calendarCurrentDate.value = d
}

const goToTodayCalendar = () => {
  calendarCurrentDate.value = new Date()
  selectedCalendarDay.value = getLocalDateString()
}

const openCreatePromotionForDate = (dateStr?: string) => {
  isEditingPromotion.value = false
  const targetDate = dateStr || selectedCalendarDay.value || new Date().toISOString().slice(0, 10)
  promotionForm.value = {
    id: 0,
    id_producto: products.value[0]?.id || '',
    titulo: '',
    descripcion: '',
    precio_promocional: 0,
    fecha_inicio: targetDate,
    fecha_fin: targetDate,
    activo: true
  }
  isPromotionModalOpen.value = true
}

const handleDeleteProduct = async (product: any) => {
  if (!product.active) {
    notify(`El producto "${product.name}" ya se encuentra inactivo.`, 'warning')
    return
  }
  if (!confirm(`¿Estás seguro de desactivar el producto "${product.name}"?`)) return
  
  product.active = false
  product.inStock = false
  try {
    await productService.updateProduct(product.id, { activo: false, disponible: false })
    notify(`Producto "${product.name}" desactivado correctamente`, 'warning')
  } catch {
    notify(`Producto "${product.name}" marcado como inactivo`, 'warning')
  }
  window.dispatchEvent(new Event('foodtruck-products-update'))
}

const loadCatalogData = async () => {
  isLoading.value = true
  try {
    const [prodsRes, catsRes, sizesRes, stocksRes, promotionsRes] = await Promise.allSettled([
      productService.getPublicProducts(),
      categoryService.getPublicCategories(),
      sizeService.getSizes(),
      stockService.getStocks(),
      promotionService.getPromotions()
    ])

    const dbProds = prodsRes.status === 'fulfilled' ? prodsRes.value.data || [] : []
    const dbCats = catsRes.status === 'fulfilled' ? catsRes.value.data || [] : []
    const dbSizes = sizesRes.status === 'fulfilled' ? sizesRes.value.data || [] : []
    const dbStocks = stocksRes.status === 'fulfilled' ? stocksRes.value.data || [] : []
    const dbPromotions = promotionsRes.status === 'fulfilled' ? promotionsRes.value.data || [] : []

    categoriesList.value = Array.isArray(dbCats) ? dbCats : []
    sizesList.value = Array.isArray(dbSizes) ? dbSizes : []
    promotions.value = Array.isArray(dbPromotions) ? dbPromotions : []
    
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
        imagePosition: p.imagen_posicion || '50% 50%',
        imageZoom: Number(p.imagen_zoom || 1),
        imageFit: p.imagen_ajuste || 'cover',
        name: p.nombre,
        category: catName,
        grupo: p.grupo || '',
        cantidad_incluida: Number(p.cantidad_incluida || 0),
        price: Number(firstPrice),
        ingredients: (p.ingredientes || []).map((i: any) => i.ingrediente?.nombre || 'Ingrediente'),
        sizes: (p.tamaños || []).map((t: any) => t.nombre),
        precio_ingrediente_extra: p.precio_ingrediente_extra || 0,
        tipo_armado: p.tipo_armado || 'estandar',
        descripcion: p.descripcion || '',
        active: p.activo !== false && p.activo !== 0,
        inStock: p.disponible !== false && p.disponible !== 0
      }
    })
  } catch (err) {
    console.error('Error al cargar catálogo en Admin:', err)
  } finally {
    isLoading.value = false
  }
}

const search = ref('')
const debouncedSearch = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const selectedOffer = ref('')

const normalizeText = (value: string = '') => value.trim().toLowerCase()
let searchTimer: ReturnType<typeof setTimeout> | undefined

const pageSize = 8
const currentPage = ref(1)

watch(search, (value) => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    debouncedSearch.value = normalizeText(value)
    currentPage.value = 1
  }, 300)
}, { immediate: true })

watch([selectedCategory, selectedStatus, selectedOffer], () => {
  currentPage.value = 1
})

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

const filteredProducts = computed(() => {
  const query = debouncedSearch.value
  return products.value.filter(product => {
    const name = normalizeText(product.name || '')
    const matchSearch = !query || name.includes(query)
    const matchCategory = !selectedCategory.value || product.category === selectedCategory.value
    const matchStatus = !selectedStatus.value ||
      (selectedStatus.value === 'active' && product.active) ||
      (selectedStatus.value === 'inactive' && !product.active)
    const matchOffer = !selectedOffer.value ||
      (selectedOffer.value === 'yes' && product.offer) ||
      (selectedOffer.value === 'no' && !product.offer)

    return matchSearch && matchCategory && matchStatus && matchOffer
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

const totalPages = computed(() => Math.max(1, Math.ceil(filteredProducts.value.length / pageSize)))

const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * pageSize
  return filteredProducts.value.slice(start, start + pageSize)
})

const activeProducts = computed(() => products.value.filter(product => product.active).length)
const inactiveProducts = computed(() => products.value.filter(product => !product.active).length)
const offerProducts = computed(() => products.value.filter(product => product.offer).length)

const getProductsCountByCategory = (catName: string) => productCategoryCounts.value.get(catName) || 0
const getProductsCountBySize = (sizeName: string) => productSizeCounts.value.get(sizeName) || 0

const totalCategorizedProducts = computed(() => products.value.filter((p: any) => p.category || p.categoria).length)

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

const filteredPromotions = computed(() => {
  const query = promotionSearch.value.trim().toLowerCase()
  if (!query) return promotions.value
  return promotions.value.filter((promotion: any) =>
    `${promotion.titulo} ${promotion.descripcion || ''} ${promotion.producto?.nombre || ''}`.toLowerCase().includes(query)
  )
})

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

const isImageCustomizerOpen = ref(false)
const imageCustomizerSnapshot = ref({
  imagePosition: '50% 50%',
  imageZoom: 1,
  imageFit: 'cover' as 'cover' | 'contain'
})

const openImageCustomizer = () => {
  imageCustomizerSnapshot.value = {
    imagePosition: productForm.value.imagePosition,
    imageZoom: productForm.value.imageZoom,
    imageFit: productForm.value.imageFit
  }
  isImageCustomizerOpen.value = true
}

const applyImageCustomization = () => {
  isImageCustomizerOpen.value = false
  notify('Personalización de imagen aplicada', 'success')
}

const cancelImageCustomization = () => {
  productForm.value.imagePosition = imageCustomizerSnapshot.value.imagePosition
  productForm.value.imageZoom = imageCustomizerSnapshot.value.imageZoom
  productForm.value.imageFit = imageCustomizerSnapshot.value.imageFit as 'cover' | 'contain'
  isImageCustomizerOpen.value = false
}

onMounted(async () => {
  await loadCatalogData()
  window.addEventListener('foodtruck-products-update', () => {
    loadCatalogData()
  })
})
</script>

<style scoped>
.products-page {
  max-width: 1650px;
  margin: 0 auto;
  padding: 1.5rem 1.5rem 3rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ====================================================
   HEADER Y ACCIONES (ESTILO INVENTARIO)
==================================================== */
.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: wrap;
}

.header-copy h1 {
  color: var(--DC-brown, #513119);
  font-size: 2.2rem;
  line-height: 1.1;
  margin: 0 0 0.4rem 0;
}

.header-copy p {
  margin: 0;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.92rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.btn-secondary {
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  padding: 0.65rem 1.1rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
  font-weight: 700;
  font-size: 0.88rem;
}

.btn-secondary:hover:not(:disabled) {
  transform: translateY(-1px);
  border-color: var(--DC-orange, #e28743);
  box-shadow: 0 4px 14px rgba(226, 135, 67, 0.15);
}

.btn-primary {
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
  font-weight: 800;
  font-size: 0.88rem;
  transition: all 0.2s ease;
}

.btn-primary:hover {
  background: var(--DC-brown, #513119);
  transform: translateY(-1px);
  box-shadow: 0 4px 14px rgba(81, 49, 25, 0.2);
}

.compact-btn {
  padding: 0.55rem 1rem !important;
  font-size: 0.82rem !important;
}

/* ====================================================
   PESTAÑAS SEGMENTADAS (IDÉNTICO A INVENTARIO)
==================================================== */
.inventory-tabs-nav {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px;
  background: #ede6dc;
  border-radius: 14px;
  max-width: 100%;
  overflow-x: auto;
}

.tab-nav-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: #6d6254;
  font-weight: 700;
  font-size: 0.88rem;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  white-space: nowrap;
}

.tab-nav-btn:hover:not(.active) {
  color: var(--DC-brown, #513119);
  background: rgba(255, 255, 255, 0.4);
}

.tab-nav-btn.active {
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 800;
  box-shadow: 0 2px 8px rgba(26, 14, 5, 0.08);
}

.tab-pill {
  font-size: 0.72rem;
  padding: 2px 7px;
  border-radius: 999px;
  background: rgba(81, 49, 25, 0.08);
  color: #665b4f;
  font-weight: 800;
}

.tab-nav-btn.active .tab-pill {
  background: var(--DC-orange, #e28743);
  color: white;
}

/* ====================================================
   KPIS GENERALES
==================================================== */
.summary-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 1rem;
}

.panel-card,
.summary-card {
  background: white;
  border-radius: 18px;
  box-shadow: 0 4px 20px rgba(26, 14, 5, 0.04);
  border: 1px solid rgba(81, 49, 25, 0.08);
}

.summary-card {
  padding: 1.1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.summary-icon-box {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.bg-summary-brown { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.bg-summary-orange { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.bg-summary-pink { background: rgba(216, 0, 86, 0.1); color: var(--DC-pink, #d80056); }
.bg-summary-green { background: rgba(22, 163, 74, 0.12); color: #16a34a; }

.summary-label {
  display: block;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.summary-value {
  display: block;
  color: var(--DC-gray, #2c2724);
  font-size: 1.5rem;
  line-height: 1.1;
  margin: 0.15rem 0;
}

.summary-helper {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.8rem;
  margin: 0;
}

/* ====================================================
   PANEL DE TABLA UNIFICADO
==================================================== */
.table-unified-card {
  padding: 0;
  overflow: hidden;
}

.panel-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.85rem 1.15rem;
  background: #fffdfa;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  flex-wrap: wrap;
}

.toolbar-left {
  display: flex;
  align-items: center;
  gap: 0.65rem;
  flex-wrap: wrap;
}

.toolbar-right {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.search-box,
.select-box {
  display: flex;
  align-items: center;
  gap: 0.45rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.09);
  border-radius: 12px;
  padding: 0.55rem 0.75rem;
}

.search-box input {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  min-width: 220px;
}

.select-box select {
  border: none;
  outline: none;
  background: transparent;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  cursor: pointer;
  min-width: 130px;
}

.search-icon,
.select-icon {
  color: var(--DC-text-gray, #7c7468);
  flex-shrink: 0;
}

.clear-search-btn {
  background: transparent;
  border: none;
  color: #999;
  cursor: pointer;
  display: flex;
  align-items: center;
  padding: 2px;
}

.btn-reset-filters {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.5rem 0.75rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-reset-filters:hover {
  background: #fee2e2;
  color: #b91c1c;
  border-color: #fca5a5;
}

.results-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.45rem 0.75rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #513119);
  font-size: 0.78rem;
  font-weight: 800;
}

/* ====================================================
   TABLA
==================================================== */
.table-wrapper {
  max-height: 560px;
  overflow-y: auto;
  width: 100%;
}

.products-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.products-table thead th {
  background: #faf6f0;
  color: var(--DC-brown, #513119);
  font-size: 0.72rem;
  font-weight: 800;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  padding: 0.8rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.1);
  position: sticky;
  top: 0;
  z-index: 2;
  text-align: left;
}

.products-table tbody td {
  padding: 0.75rem 0.5rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.07);
  vertical-align: middle;
  font-size: 0.86rem;
}

.products-table thead th:first-child,
.products-table tbody td:first-child {
  padding-left: 1.15rem;
}

.products-table thead th:last-child,
.products-table tbody td:last-child {
  padding-right: 1.15rem;
}

.products-table tbody tr:hover {
  background: rgba(245, 235, 224, 0.35);
}

.product-info {
  display: flex;
  align-items: center;
  gap: 0.6rem;
}

.product-image-container {
  width: 36px;
  height: 36px;
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

.product-info h4 {
  margin: 0;
  color: var(--DC-gray, #2c2724);
  font-size: 0.9rem;
  font-weight: 800;
  line-height: 1.2;
}

.product-info small {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.75rem;
}

.product-price {
  color: var(--DC-brown, #513119);
  font-weight: 800;
  font-size: 0.95rem;
}

.category-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.3rem 0.65rem;
  border-radius: 999px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-brown, #513119);
  font-size: 0.74rem;
  font-weight: 800;
}

.ingredients {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
}

.ingredient-tag {
  padding: 2px 7px;
  border-radius: 6px;
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.72rem;
  font-weight: 600;
}

.ingredient-more {
  padding: 2px 6px;
  border-radius: 6px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  font-size: 0.72rem;
  font-weight: 800;
}

.status-badge {
  display: inline-flex;
  padding: 0.32rem 0.6rem;
  border-radius: 999px;
  font-size: 0.74rem;
  font-weight: 800;
}

.status-badge.active { background: rgba(62, 165, 93, 0.12); color: #2f8b4c; }
.status-badge.inactive { background: rgba(216, 0, 86, 0.14); color: var(--DC-pink, #d80056); }

.promo-pill-badge {
  display: inline-flex;
  padding: 0.32rem 0.6rem;
  border-radius: 999px;
  background: #fef3c7;
  color: #b45309;
  font-size: 0.74rem;
  font-weight: 800;
}

.no-offer {
  color: #9ca3af;
  font-size: 0.78rem;
  font-weight: 600;
}

.actions {
  display: flex;
  align-items: center;
  gap: 5px;
  justify-content: center;
}

.icon-button {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.2s;
}

.icon-button:hover {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.icon-button.delete-btn:hover {
  background: #fee2e2;
  border-color: #fca5a5;
  color: #dc2626;
}

/* ====================================================
   PAGINACIÓN
==================================================== */
.inventory-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.85rem 1.15rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.pagination-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.5rem 0.9rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-brown, #513119);
  font-weight: 700;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pagination-btn:hover:not(:disabled) {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.pagination-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.pagination-info {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.82rem;
}

/* ====================================================
   TARJETAS ENTIDADES (CATEGORÍAS, TAMAÑOS)
==================================================== */
.cards-grid-wrapper {
  padding: 1.25rem;
}

.catalog-cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1rem;
}

.catalog-entity-card {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 14px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
  transition: transform 0.2s ease, border-color 0.2s ease;
}

.catalog-entity-card:hover {
  transform: translateY(-2px);
  border-color: var(--DC-orange, #e28743);
}

.entity-card-top {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.entity-icon-pill {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.cat-pill { background: rgba(226, 135, 67, 0.12); color: var(--DC-orange, #e28743); }
.size-pill { background: #eff6ff; color: #3b82f6; }
.promotion-pill { background: #fff7ed; color: #ea580c; }

.entity-card-info {
  flex: 1;
  min-width: 0;
}

.entity-title {
  margin: 0 0 2px 0;
  font-size: 0.95rem;
  font-weight: 800;
  color: var(--DC-gray, #2c2724);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.entity-badge {
  font-size: 0.72rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 999px;
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-text-gray, #7c7468);
}

.entity-desc {
  margin: 0;
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.4;
  min-height: 34px;
}

.entity-card-actions {
  display: flex;
  justify-content: flex-end;
  gap: 6px;
  border-top: 1px dashed rgba(81, 49, 25, 0.08);
  padding-top: 0.6rem;
}

.btn-entity-action {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 5px 10px;
  border-radius: 8px;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: 0.15s;
}

.btn-entity-action.edit { background: var(--DC-bg-gray, #f8f6f3); color: var(--DC-brown, #513119); }
.btn-entity-action.edit:hover { background: #e2e8f0; }
.btn-entity-action.delete { background: #fee2e2; color: #b91c1c; }
.btn-entity-action.delete:hover { background: #fca5a5; }
.btn-entity-action.toggle { background: #f1f5f9; color: #475569; }

/* ====================================================
   PROMOCIONES Y CALENDARIO
==================================================== */
.view-mode-toggle {
  display: inline-flex;
  align-items: center;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 3px;
  border-radius: 10px;
  gap: 3px;
}

.view-toggle-btn {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
}

.view-toggle-btn.active {
  background: white;
  color: var(--DC-brown, #513119);
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.promo-content-wrapper {
  padding: 1rem;
}

.calendar-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.calendar-month-nav {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cal-nav-btn {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.cal-current-month {
  margin: 0;
  font-size: 1rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  min-width: 150px;
  text-align: center;
}

.cal-today-btn {
  padding: 4px 10px;
  border-radius: 6px;
  border: 1px solid rgba(226, 135, 67, 0.3);
  background: #fff7ed;
  color: var(--DC-orange, #e28743);
  font-size: 0.74rem;
  font-weight: 700;
  cursor: pointer;
}

.calendar-main-layout {
  display: grid;
  grid-template-columns: 1fr 310px;
  gap: 1rem;
  align-items: start;
}

.calendar-grid-container {
  background: white;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  overflow: hidden;
}

.calendar-weekdays-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  background: var(--DC-bg-gray, #f8f6f3);
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
  text-align: center;
  padding: 8px 0;
  font-size: 0.74rem;
  font-weight: 800;
  color: var(--DC-text-gray, #7c7468);
}

.calendar-days-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
}

.cal-day-cell {
  border-right: 1px solid #f1f5f9;
  border-bottom: 1px solid #f1f5f9;
  padding: 6px;
  min-height: 85px;
  display: flex;
  flex-direction: column;
  gap: 4px;
  cursor: pointer;
}

.cal-day-cell.is-selected {
  background: #fff9f2;
  outline: 2px solid var(--DC-orange, #e28743);
  outline-offset: -2px;
}

.cal-day-cell.other-month { opacity: 0.35; }

.cal-day-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cal-day-number {
  font-size: 0.78rem;
  font-weight: 800;
}

.is-today .cal-day-number {
  background: var(--DC-orange, #e28743);
  color: white;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cal-add-promo-btn {
  opacity: 0;
  width: 18px;
  height: 18px;
  border-radius: 50%;
  border: 1px solid #ddd;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.cal-day-cell:hover .cal-add-promo-btn { opacity: 1; }

.cal-promos-container {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.cal-promo-chip {
  padding: 2px 4px;
  border-radius: 4px;
  background: #dcfce7;
  color: #15803d;
  font-size: 0.65rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 3px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.calendar-selected-day-panel {
  background: white;
  border-radius: 14px;
  border: 1px solid rgba(81, 49, 25, 0.08);
  overflow: hidden;
}

.panel-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 12px;
  background: var(--DC-bg-gray, #f8f6f3);
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.panel-header-title {
  display: flex;
  align-items: center;
  gap: 6px;
}

.panel-header-title h4 {
  margin: 0;
  font-size: 0.85rem;
  color: var(--DC-brown, #513119);
}

.panel-header-title small {
  color: var(--DC-text-gray, #7c7468);
  font-size: 0.72rem;
}

.btn-panel-add {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  padding: 4px 8px;
  border-radius: 6px;
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  font-size: 0.72rem;
  font-weight: 700;
  cursor: pointer;
}

.panel-body {
  padding: 10px;
  max-height: 480px;
  overflow-y: auto;
}

.panel-promo-card {
  padding: 10px;
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.06);
  display: flex;
  flex-direction: column;
  gap: 6px;
  margin-bottom: 8px;
}

.panel-promo-card-top {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.panel-promo-card-top h5 {
  margin: 0;
  font-size: 0.82rem;
  color: var(--DC-gray, #2c2724);
}

.panel-product-tag {
  font-size: 0.68rem;
  color: var(--DC-text-gray, #7c7468);
}

.panel-promo-price {
  color: #16a34a;
  font-size: 0.9rem;
}

.panel-promo-dates {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 0.7rem;
  color: var(--DC-text-gray, #7c7468);
}

.promotion-price {
  font-size: 1.15rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.promotion-dates {
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
  padding: 4px 8px;
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 6px;
}

/* ====================================================
   MODALES (ESTILO HOMOGÉNEO)
==================================================== */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  display: grid;
  place-items: center;
  padding: 1rem;
  background: rgba(35, 20, 10, 0.46);
}

.modal-card {
  position: relative;
  width: min(100%, 440px);
  border-radius: 18px;
  background: white;
  box-shadow: 0 20px 60px rgba(26, 14, 5, 0.25);
  overflow: hidden;
}

.modal-card-wide {
  width: min(96vw, 1020px);
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.product-editor-modal {
  width: min(96vw, 1020px);
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.1rem 1.25rem;
  border-bottom: 1px solid rgba(81, 49, 25, 0.08);
}

.modal-header-title {
  display: flex;
  align-items: center;
  gap: 0.65rem;
}

.header-icon-pill {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  background: rgba(226, 135, 67, 0.12);
  color: var(--DC-orange, #e28743);
  display: grid;
  place-items: center;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.1rem;
  color: var(--DC-brown, #513119);
}

.modal-header-desc {
  margin: 0;
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
}

.close-btn {
  background: transparent;
  border: none;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
  display: grid;
  place-items: center;
}

.modal-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.modal-form-wrapper {
  overflow-y: auto;
  max-height: calc(90vh - 65px);
}

.modal-columns-grid {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 1.25rem;
  padding: 1.25rem;
}

.modal-form-col {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-section-group {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 14px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.group-legend {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.modal-label {
  display: flex;
  flex-direction: column;
  gap: 4px;
  font-size: 0.8rem;
  font-weight: 700;
  color: var(--DC-brown, #513119);
}

.modal-input {
  width: 100%;
  border: 1px solid rgba(81, 49, 25, 0.12);
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  padding: 0.65rem 0.85rem;
  color: var(--DC-gray, #2c2724);
  font-size: 0.86rem;
  outline: none;
}

.modal-input:focus {
  border-color: var(--DC-orange, #e28743);
}

.modal-row,
.form-row-2 {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.65rem;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.65rem;
  padding: 1rem 1.25rem;
  background: #fffdfa;
  border-top: 1px solid rgba(81, 49, 25, 0.08);
}

.btn-cancel {
  padding: 0.6rem 1rem;
  border-radius: 10px;
  border: 1px solid rgba(81, 49, 25, 0.15);
  background: white;
  color: var(--DC-text-gray, #7c7468);
  font-weight: 700;
  font-size: 0.82rem;
  cursor: pointer;
}

.btn-save {
  padding: 0.6rem 1.25rem;
  border-radius: 10px;
  border: none;
  background: var(--DC-orange, #e28743);
  color: white;
  font-weight: 800;
  font-size: 0.82rem;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.chip-selector {
  display: flex;
  gap: 5px;
  flex-wrap: wrap;
}

.chip-btn {
  padding: 4px 10px;
  border-radius: 8px;
  border: 1px solid rgba(81, 49, 25, 0.1);
  background: var(--DC-bg-gray, #f8f6f3);
  font-size: 0.76rem;
  font-weight: 700;
  color: var(--DC-text-gray, #7c7468);
  cursor: pointer;
}

.chip-btn.active {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  color: white;
}

.size-prices-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
  gap: 6px;
  margin-top: 4px;
}

.size-price-item {
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 8px;
  padding: 6px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.price-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.currency-symbol {
  position: absolute;
  left: 8px;
  color: #999;
  font-size: 0.8rem;
  font-weight: 700;
}

.price-input {
  padding-left: 20px !important;
}

.modal-preview-col {
  background: #fdfbf9;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 14px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

.preview-header-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.preview-badge {
  font-size: 0.78rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
  display: flex;
  align-items: center;
  gap: 4px;
}

.live-pill {
  font-size: 0.65rem;
  background: #10b981;
  color: white;
  padding: 2px 6px;
  border-radius: 999px;
  font-weight: 800;
}

.live-product-card {
  background: white;
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 12px;
  overflow: hidden;
}

.live-card-img-wrap {
  position: relative;
  height: 120px;
  background: #f5eee6;
  display: flex;
  align-items: center;
  justify-content: center;
}

.live-card-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.live-card-category {
  position: absolute;
  top: 6px;
  left: 6px;
  background: rgba(81, 49, 25, 0.85);
  color: white;
  font-size: 0.65rem;
  padding: 2px 8px;
  border-radius: 999px;
  font-weight: 700;
}

.live-card-body {
  padding: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.live-card-title {
  margin: 0;
  font-size: 0.9rem;
  font-weight: 800;
}

.live-card-desc {
  margin: 0;
  font-size: 0.72rem;
  color: #777;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.live-card-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
  padding-top: 4px;
  border-top: 1px dashed #eee;
}

.live-card-price {
  font-size: 1rem;
  font-weight: 900;
  color: var(--DC-orange, #e28743);
}

.dropzone-area {
  border: 2px dashed rgba(81, 49, 25, 0.15);
  border-radius: 10px;
  padding: 12px;
  text-align: center;
  cursor: pointer;
  background: white;
}

.dropzone-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  font-size: 0.75rem;
  color: var(--DC-text-gray, #7c7468);
}

.btn-customize-image {
  border: 1px solid rgba(81, 49, 25, 0.12);
  background: white;
  border-radius: 8px;
  padding: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  cursor: pointer;
}

/* ====================================================
   SECCIÓN DE AGRUPACIÓN Y VISTA PREVIA MEJORADA
==================================================== */
.grouping-section {
  border: 1.5px solid rgba(226, 135, 67, 0.25) !important;
  background: linear-gradient(180deg, #fffdfa 0%, #ffffff 100%) !important;
}

.header-title-row {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  flex-wrap: wrap;
}

.section-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.mode-badge-pill {
  font-size: 0.7rem;
  font-weight: 800;
  padding: 3px 9px;
  border-radius: 999px;
  letter-spacing: 0.2px;
}

.mode-solo,
.mode-badge-pill.individual {
  background: rgba(81, 49, 25, 0.08);
  color: var(--DC-brown, #513119);
}

.mode-grouped,
.mode-badge-pill.grouped {
  background: rgba(226, 135, 67, 0.15);
  color: var(--DC-orange, #e28743);
  border: 1px solid rgba(226, 135, 67, 0.35);
}

.section-subtext {
  font-size: 0.78rem;
  color: var(--DC-text-gray, #7c7468);
  margin: 0;
  line-height: 1.35;
}

.grouping-toggle-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.grouping-choice-btn {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  padding: 0.85rem;
  background: white;
  border: 1.5px solid rgba(81, 49, 25, 0.12);
  border-radius: 12px;
  cursor: pointer;
  text-align: left;
  transition: all 0.2s ease;
}

.grouping-choice-btn:hover {
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(81, 49, 25, 0.06);
}

.grouping-choice-btn.active {
  border-color: var(--DC-orange, #e28743);
  background: #fff8f2;
  box-shadow: 0 0 0 2px rgba(226, 135, 67, 0.2);
}

.choice-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  background: var(--DC-bg-gray, #f8f6f3);
  color: var(--DC-brown, #513119);
  display: grid;
  place-items: center;
  flex-shrink: 0;
  transition: all 0.2s ease;
}

.grouping-choice-btn.active .choice-icon {
  background: var(--DC-orange, #e28743);
  color: white;
}

.choice-texts {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.choice-title {
  font-size: 0.84rem;
  font-weight: 800;
  color: var(--DC-brown, #513119);
}

.choice-desc {
  font-size: 0.72rem;
  color: var(--DC-text-gray, #7c7468);
  line-height: 1.25;
}

.group-select-subpanel {
  margin-top: 0.25rem;
  padding: 0.85rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border-radius: 10px;
  border: 1px dashed rgba(226, 135, 67, 0.35);
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.group-input-combobox {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  margin-top: 0.25rem;
}

.select-group-box {
  background: white;
  font-weight: 600;
}

.custom-group-input {
  background: white;
}

.grouping-info-hint {
  margin: 0;
  font-size: 0.73rem;
  color: var(--DC-brown, #513119);
  opacity: 0.85;
  line-height: 1.3;
}

.live-card-group-badge {
  position: absolute;
  bottom: 6px;
  left: 6px;
  background: rgba(226, 135, 67, 0.95);
  color: white;
  font-size: 0.65rem;
  font-weight: 800;
  padding: 2px 7px;
  border-radius: 6px;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.live-card-sizes-pills {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

.mini-size-pill {
  font-size: 0.68rem;
  font-weight: 700;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.1);
  border-radius: 6px;
  padding: 2px 6px;
  color: var(--DC-brown, #513119);
}

.toggles-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
}

.toggle-availability-label {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.65rem 0.85rem;
  background: var(--DC-bg-gray, #f8f6f3);
  border: 1px solid rgba(81, 49, 25, 0.08);
  border-radius: 10px;
  cursor: pointer;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.toggle-availability-label:hover {
  background: #f3efe9;
}

.toggle-text {
  display: flex;
  flex-direction: column;
  gap: 1px;
}

.toggle-text strong {
  font-size: 0.8rem;
  color: var(--DC-brown, #513119);
}

.toggle-text span {
  font-size: 0.68rem;
  color: var(--DC-text-gray, #7c7468);
}

.modern-toggle {
  width: 18px;
  height: 18px;
  accent-color: var(--DC-orange, #e28743);
  cursor: pointer;
}

.chip-selector.wrap {
  flex-wrap: wrap;
  max-height: 110px;
  overflow-y: auto;
  padding: 2px;
}

.chip-btn.small {
  padding: 3px 8px;
  font-size: 0.72rem;
}

.add-custom-ing-row {
  display: flex;
  gap: 6px;
  margin-top: 6px;
}

.inline-input {
  flex: 1;
}

.btn-add-inline {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 0.45rem 0.85rem;
  border-radius: 8px;
  background: var(--DC-brown, #513119);
  color: white;
  border: none;
  font-size: 0.75rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-add-inline:hover {
  background: var(--DC-orange, #e28743);
}

.url-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-top: 6px;
}

.btn-clear-image-url {
  position: absolute;
  right: 6px;
  background: transparent;
  border: none;
  color: #999;
  cursor: pointer;
  display: grid;
  place-items: center;
  padding: 4px;
}

.customizer-preview-large {
  width: 100%;
  height: 220px;
  border-radius: 12px;
  overflow: hidden;
  background: #f5eee6;
}

.customizer-preview-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Skeleton & Utilidades */
.skeleton-pill {
  height: 14px;
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

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.empty-state,
.empty-state-box {
  padding: 3rem 1rem;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  color: var(--DC-text-gray, #7c7468);
  text-align: center;
}

.empty-state h3,
.empty-state-box h3 {
  margin: 0;
  color: var(--DC-brown, #513119);
  font-size: 1.1rem;
}

.empty-state p,
.empty-state-box p {
  margin: 0;
  font-size: 0.85rem;
}

/* Móvil */
.mobile-only { display: none !important; }

@media (max-width: 900px) {
  .summary-grid {
    grid-template-columns: 1fr;
  }
  .desktop-table-only {
    display: none !important;
  }
  .mobile-only {
    display: flex !important;
    flex-direction: column;
    gap: 0.85rem;
    padding: 1rem;
  }
  .mobile-product-card {
    background: white;
    border: 1px solid rgba(81, 49, 25, 0.08);
    border-radius: 14px;
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .mob-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .mob-prod-info {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .mob-card-body {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-top: 1px dashed #eee;
    padding-top: 8px;
  }
  .calendar-main-layout {
    grid-template-columns: 1fr;
  }
  .modal-columns-grid {
    grid-template-columns: 1fr;
  }
  .grouping-toggle-grid {
    grid-template-columns: 1fr;
  }
  .toggles-row {
    grid-template-columns: 1fr;
  }
}
</style>