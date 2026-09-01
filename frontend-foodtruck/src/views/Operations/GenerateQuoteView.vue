<template>
  <div class="pos-quote-wizard">
    <!-- HEADER & STEPS INDICATOR -->
    <header class="wizard-header">
      <div class="header-brand">
        <div class="brand-title-row">
          <Utensils :size="22" class="brand-icon" />
          <h1>Generar Pedido</h1>
          <span 
            v-if="shiftWindow"
            class="pos-shift-badge" 
            :class="shiftWindow.es_jornada_activa ? 'badge-shift-live' : 'badge-shift-off'"
            :title="`Horario: ${shiftWindow.hora_apertura} a ${shiftWindow.hora_cierre}`"
          >
            {{ shiftWindow.es_jornada_activa ? '🟢 Turno Activo' : '⚪ Fuera de Horario' }}
          </span>
        </div>
        <p class="header-subtitle">
          Punto de Venta · J.Jairo Foodtruck
          <span v-if="shiftWindow" class="header-shift-hours"> (Horario: {{ shiftWindow.hora_apertura }} - {{ shiftWindow.hora_cierre }} hrs)</span>
        </p>
      </div>

      <div class="steps-indicator">
        <div 
          class="step-pill" 
          :class="{ active: currentStep === 1, completed: currentStep > 1 }" 
          @click="currentStep > 1 && (currentStep = 1)"
        >
          <div class="step-num">
            <Check v-if="currentStep > 1" :size="13" />
            <span v-else>1</span>
          </div>
          <span class="step-text">Menú</span>
        </div>
        
        <div class="step-line" :class="{ active: currentStep >= 2 }"></div>
        
        <div 
          class="step-pill" 
          :class="{ active: currentStep === 2, completed: currentStep > 2 }" 
          @click="currentStep > 2 && (currentStep = 2)"
        >
          <div class="step-num">
            <Check v-if="currentStep > 2" :size="13" />
            <span v-else>2</span>
          </div>
          <span class="step-text">Cliente & Pago</span>
        </div>
        
        <div class="step-line" :class="{ active: currentStep >= 3 }"></div>
        
        <div class="step-pill" :class="{ active: currentStep === 3 }">
          <div class="step-num">3</div>
          <span class="step-text">Confirmar</span>
        </div>
      </div>
    </header>

    <!-- AVISO DE FUERA DE HORARIO PARA EL PERSONAL -->
    <div v-if="shiftWindow && !shiftWindow.es_jornada_activa" class="pos-outside-shift-banner">
      <AlertTriangle :size="16" class="warning-icon" />
      <span>
        Atención: Estás operando <strong>fuera del horario de turno oficial ({{ shiftWindow.hora_apertura }} - {{ shiftWindow.hora_cierre }} hrs)</strong>. Las comandas ingresadas pertenecerán a la jornada del turno actual.
      </span>
    </div>

    <!-- STEP 1: PRODUCT SELECTION, RECIPE CUSTOMIZATION & CART -->
    <div v-if="currentStep === 1" class="step-container product-step">
      <div class="product-layout">
        
        <!-- COLUMNA 1: CATÁLOGO DE PRODUCTOS (VISIBLE EN ESCRITORIO Y MÓVIL) -->
        <div class="catalog-section">
          <div class="catalog-toolbar">
            <!-- BUSCADOR -->
            <div class="search-input-box">
              <Search :size="17" class="search-icon" />
              <input
                v-model="productSearch"
                type="text"
                placeholder="Buscar completo, hamburguesa, bebida..."
                class="input-search-product"
              />
              <button v-if="productSearch" class="btn-clear-search" @click="productSearch = ''">
                <X :size="14" />
              </button>
            </div>

            <!-- CATEGORY CHIPS (SCROLL HORIZONTAL) -->
            <div class="categories-scroll-row">
              <button
                class="category-chip"
                :class="{ active: selectedCategory === 'Todas' }"
                @click="selectedCategory = 'Todas'"
              >
                ✨ Todas
              </button>
              <button
                v-for="cat in categoriesList"
                :key="cat.id"
                class="category-chip"
                :class="{ active: selectedCategory === cat.nombre_categoria }"
                @click="selectedCategory = cat.nombre_categoria"
              >
                {{ getCategoryEmoji(cat.nombre_categoria) }} {{ cat.nombre_categoria }}
              </button>
            </div>
          </div>

          <!-- SKELETON / PRODUCTS GRID -->
          <div v-if="isLoadingProducts" class="products-grid-admin">
            <div v-for="n in 6" :key="'gen-skel-' + n" class="pos-card-skeleton">
              <div class="skeleton-img"></div>
              <div class="skeleton-body">
                <div class="skeleton-pill width-120"></div>
                <div class="skeleton-pill width-80 margin-top-4"></div>
              </div>
            </div>
          </div>

          <div v-else-if="filteredProducts.length === 0" class="empty-products-box">
            <Utensils :size="36" class="empty-icon" />
            <p>No se encontraron productos con estos filtros.</p>
            <button class="btn-reset-filters" @click="selectedCategory = 'Todas'; productSearch = ''">
              Ver todos los productos
            </button>
          </div>

          <div v-else class="products-grid-admin">
            <div 
              v-for="p in filteredProducts" 
              :key="p.id" 
              class="pos-product-card"
              :class="{ 'is-selected': activeVariant?.baseProduct?.id === p.id && activeVariant?.size === p.activeSize }"
              @click="openProductCustomizer(p, p.types[0])"
            >
              <div class="card-image-wrap">
                <img :src="p.image" :alt="p.name" loading="lazy" />
                <span class="category-badge">{{ p.category }}</span>
              </div>
              
              <div class="pos-card-body">
                <h3 class="pos-product-title">{{ p.name }}</h3>

                <!-- SELECTOR DE TAMAÑO / FORMATO EN TARJETA (Desktop) -->
                <div v-if="p.sizes.length > 1" class="sizes-chips-row desktop-only-sizes" @click.stop>
                  <button 
                    v-for="size in p.sizes" 
                    :key="size"
                    class="size-btn"
                    :class="{ 'active-size': p.activeSize === size }"
                    @click="p.activeSize = size; selectVariant(p, p.types[0], false)"
                  >
                    {{ size }}
                  </button>
                </div>
                <div v-else class="single-size-tag desktop-only-sizes">
                  Formato {{ p.activeSize || 'Normal' }}
                </div>

                <!-- INDICADOR COMPACTO DE FORMATOS (Móvil) -->
                <div class="mobile-size-badge-indicator">
                  <span v-if="p.sizes.length > 1" class="multi-sizes-tag">
                    {{ p.sizes.length }} tamaños
                  </span>
                  <span v-else class="single-format-tag">
                    {{ p.activeSize || 'Normal' }}
                  </span>
                </div>

                <div class="card-footer-row">
                  <span class="product-card-price">
                    ${{ formatNumber(p.types[0]?.prices[p.activeSize] || 0) }}
                  </span>
                  
                  <button class="btn-select-product" @click.stop="openProductCustomizer(p, p.types[0])">
                    <span>Elegir</span>
                    <Plus :size="14" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- COLUMNA 2 (SOLO ESCRITORIO): RECETA            -->
        <!-- ============================================== -->
        <div class="recipe-customizer-card desktop-only-pane">
          <div class="customizer-header">
            <div class="header-left-title">
              <FileText :size="18" />
              <span>Personalizar Receta</span>
            </div>
          </div>
          
          <div class="customizer-body">
            <template v-if="activeVariant">
              <div class="selected-product-banner">
                <div class="product-banner-info">
                  <h3 class="banner-title">{{ activeVariant.baseName }}</h3>
                  <span class="banner-size-badge">{{ activeVariant.size }}</span>
                </div>
                <span class="banner-base-price">${{ formatNumber(currentVariantUnitPrice) }}</span>
              </div>

              <!-- MODO PERSONALIZABLE -->
              <div v-if="isPersonalizableProduct" class="ingredients-section">
                <div class="ingredients-section-header">
                  <h4>Ingredientes a elección</h4>
                  <span class="section-hint">
                    {{ activeVariant.cantidad_incluida || 3 }} incluidos gratis
                    <template v-if="extraIngredientsCost > 0">
                      · (+${{ formatNumber(extraIngredientsCost) }} extras)
                    </template>
                  </span>
                </div>
                
                <div class="ingredients-chips-grid">
                  <button 
                    v-for="pi in optionalExtraIngredients" 
                    :key="pi.id" 
                    class="ingredient-chip-toggle"
                    :class="{ 
                      'is-added': addedExtraIngredients.includes(pi.ingrediente?.nombre),
                      'is-disabled': !pi.ingrediente?.disponible
                    }"
                    :disabled="!pi.ingrediente?.disponible"
                    @click="toggleExtraIngredient(pi.ingrediente?.nombre)"
                  >
                    <div class="chip-status-icon">
                      <Check v-if="addedExtraIngredients.includes(pi.ingrediente?.nombre)" :size="13" />
                      <Plus v-else :size="13" />
                    </div>
                    <span class="chip-label">{{ pi.ingrediente?.nombre }}</span>
                  </button>
                </div>
              </div>

              <!-- MODO ESTÁNDAR -->
              <div v-else class="ingredients-section">
                <div class="ingredients-section-header">
                  <h4>Ingredientes de la receta</h4>
                  <span class="section-hint">Toca para quitar ingredientes</span>
                </div>
                
                <div class="ingredients-chips-grid">
                  <button 
                    v-for="pi in customizableRecipeIngredients" 
                    :key="pi.id" 
                    class="ingredient-chip-toggle standard-chip"
                    :class="{ 
                      'is-removed': excludedIngredients.includes(pi.ingrediente?.nombre),
                      'is-disabled': !pi.ingrediente?.disponible
                    }"
                    :disabled="!pi.ingrediente?.disponible"
                    @click="toggleIngredient(pi.ingrediente?.nombre)"
                  >
                    <div class="chip-status-icon">
                      <X v-if="excludedIngredients.includes(pi.ingrediente?.nombre)" :size="13" />
                      <Check v-else :size="13" />
                    </div>
                    <span class="chip-label">
                      {{ excludedIngredients.includes(pi.ingrediente?.nombre) ? 'Sin ' : '' }}{{ pi.ingrediente?.nombre }}
                    </span>
                  </button>
                </div>
              </div>

              <div class="customizer-actions">
                <button class="btn-add-to-cart-pos" @click="addActiveVariantToCart">
                  <Plus :size="18" />
                  <span>Añadir a Comanda • ${{ formatNumber(currentVariantUnitPrice) }}</span>
                </button>
              </div>
            </template>
            
            <div v-else class="empty-selection-placeholder">
              <div class="placeholder-icon-box">
                <Utensils :size="32" />
              </div>
              <h4>Ningún producto seleccionado</h4>
              <p>Selecciona un producto del catálogo para ver y ajustar su receta.</p>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- COLUMNA 3 (SOLO ESCRITORIO): COMANDA           -->
        <!-- ============================================== -->
        <aside class="pos-cart-sidebar desktop-only-pane">
          <div class="pos-cart-header">
            <div class="cart-title-row">
              <ShoppingCart :size="18" />
              <span>Comanda Actual</span>
            </div>
            <span class="cart-units-pill">{{ totalUnits }} {{ totalUnits === 1 ? 'ítem' : 'ítems' }}</span>
          </div>

          <div class="cart-items-scroll">
            <div v-if="cartItems.length === 0" class="empty-cart-state">
              <ShoppingCart :size="38" class="empty-cart-icon" />
              <p>Comanda vacía</p>
              <small>Agrega productos del catálogo para comenzar la orden.</small>
            </div>

            <div v-for="(item, idx) in cartItems" :key="item.id" class="pos-cart-item">
              <div class="cart-item-info">
                <span class="cart-item-title">{{ item.fullName }} ({{ item.size }})</span>
                
                <div v-if="item.excluidos && item.excluidos.length > 0" class="cart-badges-wrap">
                  <span v-for="ex in item.excluidos" :key="ex" class="badge-tag-removed">
                    Sin {{ ex }}
                  </span>
                </div>

                <div v-if="item.agregados && item.agregados.length > 0" class="cart-badges-wrap">
                  <span v-for="ag in item.agregados" :key="ag" class="badge-tag-added">
                    + {{ ag }}
                  </span>
                </div>

                <div class="cart-item-pricing">
                  <span class="cart-item-unit-price">${{ formatNumber(item.price) }} c/u</span>
                  <span class="cart-item-subtotal">${{ formatNumber(item.price * item.quantity) }}</span>
                </div>
              </div>

              <div class="cart-item-stepper">
                <button class="btn-stepper" @click="updateQuantity(idx, -1)" title="Reducir">
                  <Minus :size="13" />
                </button>
                <span class="stepper-count">{{ item.quantity }}</span>
                <button class="btn-stepper" @click="updateQuantity(idx, 1)" title="Aumentar">
                  <Plus :size="13" />
                </button>
                <button class="btn-trash-item" @click="removeFromCart(idx)" title="Eliminar">
                  <Trash2 :size="15" />
                </button>
              </div>
            </div>
          </div>

          <div class="pos-cart-footer">
            <div class="total-breakdown-row">
              <span>Total a Cobrar</span>
              <strong>{{ totalQuote }}</strong>
            </div>

            <button 
              class="btn-continue-checkout" 
              :disabled="cartItems.length === 0" 
              @click="goToStep2"
            >
              <span>Continuar a Cliente</span>
              <ArrowRight :size="18" />
            </button>
          </div>
        </aside>

      </div>

      <!-- ============================================================== -->
      <!-- MODAL / BOTTOM SHEET FLOTANTE: PERSONALIZAR RECETA EN MÓVIL    -->
      <!-- ============================================================== -->
      <Transition name="modal-fade">
        <div v-if="isMobileRecipeOpen && activeVariant" class="mobile-modal-overlay">
          <div class="mobile-modal-backdrop" @click="isMobileRecipeOpen = false"></div>
          
          <div class="mobile-sheet-card">
            <div class="sheet-drag-pill"></div>
            
            <!-- HEADER MODAL -->
            <div class="sheet-header">
              <div class="sheet-title-left">
                <FileText :size="18" />
                <span>Personalizar Receta</span>
              </div>
              <button class="btn-close-modal" @click="isMobileRecipeOpen = false">
                <X :size="18" />
              </button>
            </div>

            <!-- CUERPO CON SCROLL -->
            <div class="sheet-scroll-body">
              <div class="selected-product-banner">
                <div class="product-banner-info">
                  <h3 class="banner-title">{{ activeVariant.baseName }}</h3>
                  <span class="banner-size-badge">{{ activeVariant.size }}</span>
                </div>
                <span class="banner-base-price">${{ formatNumber(currentVariantUnitPrice) }}</span>
              </div>

              <!-- SELECTOR DE TAMAÑO EN MODAL -->
              <div v-if="activeVariant.baseProduct?.sizes?.length > 1" class="modal-sizes-section">
                <label class="sizes-label">Seleccionar Tamaño:</label>
                <div class="modal-sizes-row">
                  <button 
                    v-for="s in activeVariant.baseProduct.sizes" 
                    :key="s"
                    class="modal-size-pill"
                    :class="{ 'active': activeVariant.size === s }"
                    @click="changeActiveSize(s)"
                  >
                    {{ s }} · ${{ formatNumber(activeVariant.type.prices[s] || 0) }}
                  </button>
                </div>
              </div>

              <!-- MODO PERSONALIZABLE -->
              <div v-if="isPersonalizableProduct" class="ingredients-section">
                <div class="ingredients-section-header">
                  <h4>Ingredientes a elección</h4>
                  <span class="section-hint">
                    {{ activeVariant.cantidad_incluida || 3 }} incluidos gratis
                    <template v-if="extraIngredientsCost > 0">
                      · (+${{ formatNumber(extraIngredientsCost) }} extras)
                    </template>
                  </span>
                </div>
                
                <div class="ingredients-chips-grid">
                  <button 
                    v-for="pi in optionalExtraIngredients" 
                    :key="pi.id" 
                    class="ingredient-chip-toggle"
                    :class="{ 
                      'is-added': addedExtraIngredients.includes(pi.ingrediente?.nombre),
                      'is-disabled': !pi.ingrediente?.disponible
                    }"
                    :disabled="!pi.ingrediente?.disponible"
                    @click="toggleExtraIngredient(pi.ingrediente?.nombre)"
                  >
                    <div class="chip-status-icon">
                      <Check v-if="addedExtraIngredients.includes(pi.ingrediente?.nombre)" :size="13" />
                      <Plus v-else :size="13" />
                    </div>
                    <span class="chip-label">{{ pi.ingrediente?.nombre }}</span>
                  </button>
                </div>
              </div>

              <!-- MODO ESTÁNDAR -->
              <div v-else class="ingredients-section">
                <div class="ingredients-section-header">
                  <h4>Ingredientes de la receta</h4>
                  <span class="section-hint">Toca para quitar ingredientes</span>
                </div>
                
                <div class="ingredients-chips-grid">
                  <button 
                    v-for="pi in customizableRecipeIngredients" 
                    :key="pi.id" 
                    class="ingredient-chip-toggle standard-chip"
                    :class="{ 
                      'is-removed': excludedIngredients.includes(pi.ingrediente?.nombre),
                      'is-disabled': !pi.ingrediente?.disponible
                    }"
                    :disabled="!pi.ingrediente?.disponible"
                    @click="toggleIngredient(pi.ingrediente?.nombre)"
                  >
                    <div class="chip-status-icon">
                      <X v-if="excludedIngredients.includes(pi.ingrediente?.nombre)" :size="13" />
                      <Check v-else :size="13" />
                    </div>
                    <span class="chip-label">
                      {{ excludedIngredients.includes(pi.ingrediente?.nombre) ? 'Sin ' : '' }}{{ pi.ingrediente?.nombre }}
                    </span>
                  </button>
                </div>
              </div>
            </div>

            <!-- BOTÓN FIJO INFERIOR EN EL MODAL -->
            <div class="sheet-sticky-bottom-bar">
              <button class="btn-add-to-cart-pos" @click="addActiveVariantToCart">
                <Plus :size="18" />
                <span>Añadir a Comanda • ${{ formatNumber(currentVariantUnitPrice) }}</span>
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- ============================================================== -->
      <!-- MODAL / BOTTOM SHEET FLOTANTE: COMANDA (CARRITO) EN MÓVIL      -->
      <!-- ============================================================== -->
      <Transition name="modal-fade">
        <div v-if="isMobileCartOpen" class="mobile-modal-overlay">
          <div class="mobile-modal-backdrop" @click="isMobileCartOpen = false"></div>
          
          <div class="mobile-sheet-card">
            <div class="sheet-drag-pill"></div>

            <div class="sheet-header">
              <div class="sheet-title-left">
                <ShoppingCart :size="18" />
                <span>Comanda Actual</span>
              </div>
              <div class="header-cart-right">
                <span class="cart-units-pill">{{ totalUnits }} {{ totalUnits === 1 ? 'ítem' : 'ítems' }}</span>
                <button class="btn-close-modal" @click="isMobileCartOpen = false">
                  <X :size="18" />
                </button>
              </div>
            </div>

            <div class="sheet-scroll-body">
              <div v-if="cartItems.length === 0" class="empty-cart-state">
                <ShoppingCart :size="38" class="empty-cart-icon" />
                <p>Comanda vacía</p>
                <small>Agrega productos del catálogo para comenzar la orden.</small>
              </div>

              <div v-for="(item, idx) in cartItems" :key="item.id" class="pos-cart-item">
                <div class="cart-item-info">
                  <span class="cart-item-title">{{ item.fullName }} ({{ item.size }})</span>
                  
                  <div v-if="item.excluidos && item.excluidos.length > 0" class="cart-badges-wrap">
                    <span v-for="ex in item.excluidos" :key="ex" class="badge-tag-removed">
                      Sin {{ ex }}
                    </span>
                  </div>

                  <div v-if="item.agregados && item.agregados.length > 0" class="cart-badges-wrap">
                    <span v-for="ag in item.agregados" :key="ag" class="badge-tag-added">
                      + {{ ag }}
                    </span>
                  </div>

                  <div class="cart-item-pricing">
                    <span class="cart-item-unit-price">${{ formatNumber(item.price) }} c/u</span>
                    <span class="cart-item-subtotal">${{ formatNumber(item.price * item.quantity) }}</span>
                  </div>
                </div>

                <div class="cart-item-stepper">
                  <button class="btn-stepper" @click="updateQuantity(idx, -1)" title="Reducir">
                    <Minus :size="13" />
                  </button>
                  <span class="stepper-count">{{ item.quantity }}</span>
                  <button class="btn-stepper" @click="updateQuantity(idx, 1)" title="Aumentar">
                    <Plus :size="13" />
                  </button>
                  <button class="btn-trash-item" @click="removeFromCart(idx)" title="Eliminar">
                    <Trash2 :size="15" />
                  </button>
                </div>
              </div>
            </div>

            <div class="sheet-sticky-bottom-bar pos-cart-footer">
              <div class="total-breakdown-row">
                <span>Total a Cobrar</span>
                <strong>{{ totalQuote }}</strong>
              </div>

              <button 
                class="btn-continue-checkout" 
                :disabled="cartItems.length === 0" 
                @click="goToStep2"
              >
                <span>Continuar a Cliente y Pago</span>
                <ArrowRight :size="18" />
              </button>
            </div>
          </div>
        </div>
      </Transition>

      <!-- BARRA FLOTANTE MÓVIL EN LA PARTE INFERIOR (SOLO CUANDO NO HAY MODAL ABIERTO) -->
      <div 
        v-if="cartItems.length > 0 && !isMobileRecipeOpen && !isMobileCartOpen" 
        class="mobile-floating-cart-bar" 
        @click="isMobileCartOpen = true"
      >
        <div class="cart-bar-info">
          <div class="cart-bubble-icon">
            <ShoppingCart :size="18" />
            <span class="badge-count">{{ totalUnits }}</span>
          </div>
          <div class="cart-bar-text-group">
            <span class="cart-bar-sub">Comanda</span>
            <strong class="cart-bar-total">{{ totalQuote }}</strong>
          </div>
        </div>
        <button class="btn-checkout-mobile" @click.stop="isMobileCartOpen = true">
          Ver Comanda <ArrowRight :size="16" />
        </button>
      </div>
    </div>

    <!-- STEP 2: CLIENT & PAYMENT METHOD -->
    <div v-if="currentStep === 2" class="step-container client-step">
      <div class="client-step-card">
        <div class="section-intro">
          <h2>Datos del Cliente y Pago</h2>
          <p>Indica el nombre del cliente y el método de pago acordado.</p>
        </div>

        <form class="client-data-form" @submit.prevent="nextStep">
          <div class="form-grid">
            <div class="form-field-group">
              <label><User :size="15" /> Nombre del Cliente *</label>
              <input 
                v-model="customerForm.nombre" 
                type="text" 
                placeholder="Ej: Juan Pérez" 
                class="pos-form-input" 
                required
                autofocus
              />
            </div>

            <div class="form-field-group">
              <label><Phone :size="15" /> Teléfono WhatsApp (Opcional)</label>
              <input 
                v-model="customerForm.telefono" 
                type="tel" 
                placeholder="+569..." 
                class="pos-form-input" 
                @input="handlePhoneInput"
              />
            </div>
          </div>

          <div class="payment-method-selector-section">
            <label class="section-label"><DollarSign :size="16" /> Método de Pago *</label>
            <div class="payment-methods-grid">
              <button
                v-for="m in metodosdepago"
                :key="m.id"
                type="button"
                class="payment-method-btn"
                :class="{ active: selectedPaymentMethod === m.nombre }"
                @click="selectedPaymentMethod = m.nombre"
              >
                <div class="payment-icon-box">
                  <Banknote v-if="m.nombre === 'Efectivo'" :size="22" />
                  <CreditCard v-else-if="m.nombre.includes('Débito')" :size="22" />
                  <CreditCard v-else-if="m.nombre.includes('Crédito')" :size="22" />
                  <Smartphone v-else :size="22" />
                </div>
                <span class="payment-btn-label">{{ m.nombre }}</span>
                <Check v-if="selectedPaymentMethod === m.nombre" class="payment-check-icon" :size="16" />
              </button>
            </div>
          </div>

          <div class="step-nav-actions">
            <button type="button" class="btn-pos-secondary" @click="currentStep = 1">
              <ArrowLeft :size="18" /> Volver al Menú
            </button>
            <button type="submit" class="btn-pos-primary">
              Revisar Resumen <ArrowRight :size="18" />
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- STEP 3: ORDER SUMMARY & CONFIRMATION -->
    <div v-if="currentStep === 3" class="step-container summary-step">
      <div class="summary-receipt-card">
        <div class="receipt-header">
          <CheckCircle :size="36" class="receipt-icon" />
          <h2>Confirmar Comanda</h2>
          <p>Verifica los detalles antes de enviar el pedido a cocina.</p>
        </div>

        <div class="receipt-grid">
          <div class="receipt-section client-info-box">
            <h3>Datos de Atención</h3>
            <div class="client-meta-row">
              <span>Cliente:</span>
              <strong>{{ customerForm.nombre || 'Cliente Presencial' }}</strong>
            </div>
            <div class="client-meta-row">
              <span>Teléfono:</span>
              <strong>{{ customerForm.telefono || 'Sin teléfono' }}</strong>
            </div>
            <div class="client-meta-row">
              <span>Método de Pago:</span>
              <strong class="payment-badge-highlight">{{ selectedPaymentMethod }}</strong>
            </div>
          </div>

          <div class="receipt-section products-summary-box">
            <h3>Productos ({{ totalUnits }})</h3>
            <div class="receipt-products-list">
              <div v-for="item in cartItems" :key="item.id" class="receipt-item-row">
                <div class="receipt-item-left">
                  <span class="receipt-qty">{{ item.quantity }}x</span>
                  <div class="receipt-item-names">
                    <strong>{{ item.fullName }} ({{ item.size }})</strong>
                    <div v-if="item.excluidos && item.excluidos.length > 0" class="receipt-tags">
                      <span class="tag-red">Sin {{ item.excluidos.join(', ') }}</span>
                    </div>
                    <div v-if="item.agregados && item.agregados.length > 0" class="receipt-tags">
                      <span class="tag-blue">+ {{ item.agregados.join(', ') }}</span>
                    </div>
                  </div>
                </div>
                <span class="receipt-price">${{ formatNumber(item.price * item.quantity) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="receipt-total-banner">
          <span>Total a Pagar</span>
          <strong>{{ totalQuote }}</strong>
        </div>

        <div class="receipt-actions">
          <button type="button" class="btn-pos-secondary" :disabled="isSubmitting" @click="currentStep = 2">
            <ArrowLeft :size="18" /> Modificar Datos
          </button>
          <button type="button" class="btn-pos-primary btn-confirm-order" :disabled="isSubmitting" @click="confirmQuote">
            <CheckCircle :size="18" />
            <span>{{ isSubmitting ? 'Enviando a Cocina...' : 'Enviar Pedido a Cocina' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { 
  Search, ArrowRight, ArrowLeft, ShoppingCart, Trash2, Plus, Minus, 
  FileText, Utensils, Check, X, CreditCard, Banknote, Smartphone, 
  DollarSign, User, Phone, CheckCircle, AlertTriangle
} from 'lucide-vue-next';
import productService from '@/services/productService';
import categoryService from '@/services/categoryService';
import orderService from '@/services/orderService';
import { useNotification } from '@/composables/useNotification';
import cashFlowService, { type ShiftWindow } from '@/services/cashFlowService';

const router = useRouter();
const { notify } = useNotification();
const currentStep = ref(1);
const isSubmitting = ref(false);
const shiftWindow = ref<ShiftWindow | null>(null);

const isMobileRecipeOpen = ref(false);
const isMobileCartOpen = ref(false);

const activeVariant = ref<any>(null);
const excludedIngredients = ref<string[]>([]);
const addedExtraIngredients = ref<string[]>([]);
const selectedPaymentMethod = ref('Efectivo');

const customerForm = ref({ nombre: '', telefono: '+56' });

const foodProducts = ref<any[]>([]);
const categoriesList = ref<any[]>([]);
const cartItems = ref<any[]>([]);
const selectedCategory = ref('Todas');
const productSearch = ref('');
const metodosdepago = ref<any[]>([]);

const formatNumber = (num: any) => {
  const n = Number(num || 0);
  return isNaN(n) ? '0' : n.toLocaleString('es-CL');
};

const getCategoryEmoji = (categoryName: string) => {
  const cat = (categoryName || '').toLowerCase();
  if (cat.includes('completo') || cat.includes('vianesa')) return '🌭';
  if (cat.includes('hamburguesa')) return '🍔';
  if (cat.includes('churrasco') || cat.includes('lomito')) return '🥪';
  if (cat.includes('ass')) return '🥩';
  if (cat.includes('pizza')) return '🍕';
  if (cat.includes('fajita')) return '🌮';
  if (cat.includes('papa') || cat.includes('chorrillana')) return '🍟';
  if (cat.includes('empanada') || cat.includes('sopaipilla')) return '🥟';
  if (cat.includes('bebestible') || cat.includes('jugo') || cat.includes('bebida')) return '🥤';
  return '🍽️';
};

const cargarMetodosSimulados = () => {
  metodosdepago.value = [
    { id: 1, nombre: 'Efectivo' }, 
    { id: 2, nombre: 'Tarjeta de Débito' }, 
    { id: 3, nombre: 'Tarjeta de Crédito' }, 
    { id: 4, nombre: 'Transferencia' }
  ];
};

const isLoadingProducts = ref(true);

const fetchProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const [productsRes, categoriesRes] = await Promise.all([
      productService.getPublicProducts(),
      categoryService.getPublicCategories()
    ]);

    const dbProducts = productsRes.data || [];
    const dbCategories = categoriesRes.data || [];

    categoriesList.value = dbCategories.map((c: any) => ({
      id: c.id_categoria,
      nombre_categoria: c.nombre_categoria
    }));

    const categoryImages: Record<string, string> = {
      'Vianesas': 'https://images.unsplash.com/photo-1612392062798-7c7e16d7f49f?w=900',
      'Ass': 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=900',
      'Churrascos': 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?w=900',
      'Lomitos': 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=900',
      'Hamburguesas': 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=900',
      'Pizzas': 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=900',
      'Fajitas': 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=900',
      'Sándwich de Pollo': 'https://images.unsplash.com/photo-1606755962773-d324e0a13086?w=900',
      'Papas & Chorrillanas': 'https://images.unsplash.com/photo-1573080496219-bb080dd4f877?w=900',
      'Empanadas & Sopaipillas': 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=900',
      'Bebestibles & Jugos': 'https://images.unsplash.com/photo-1581009146145-b5ef050c2e1e?w=900'
    };

    const activeDbProducts = dbProducts.filter((p: any) => {
      const isActivo = p.activo !== false && p.activo !== 0 && p.active !== false;
      const isDisponible = p.disponible !== false && p.disponible !== 0 && p.inStock !== false;
      const isEstadoOk = p.estado !== 0;
      return isActivo && isDisponible && isEstadoOk;
    });

    foodProducts.value = activeDbProducts.map((prod: any) => {
      const catName = prod.categoria?.nombre_categoria || 'Varios';
      const sizesArray = (prod.tamaños || []).map((t: any) => t.nombre);
      const pricesMap: Record<string, number> = {};
      const sizesMap: Record<string, number> = {};

      (prod.tamaños || []).forEach((t: any) => {
        pricesMap[t.nombre] = Number(t.pivot?.precio || 0);
        sizesMap[t.nombre] = Number(t.id_tamaño || t.id || 1);
      });

      const prodImage = prod.imagen_url || prod.imagen || prod.image || categoryImages[catName] || 'https://images.unsplash.com/photo-1567620812782-f461bc805b46?w=900';

      return {
        id: prod.id_producto,
        name: prod.nombre,
        category: catName,
        image: prodImage,
        tipo_armado: prod.tipo_armado || 'Estandar',
        cantidad_incluida: prod.cantidad_incluida ?? 0,
        precio_ingrediente_extra: Number(prod.precio_ingrediente_extra || 0),
        sizes: sizesArray.length ? sizesArray : ['Normal'],
        activeSize: sizesArray[0] || 'Normal',
        tamano_id: prod.tamaños?.[0]?.id_tamaño || 1,
        sizesMap: sizesMap,
        types: [
          {
            id: prod.id_producto,
            name: prod.nombre,
            desc: prod.descripcion,
            image: prodImage,
            prices: pricesMap,
            producto_ingrediente: prod.ingredientes || []
          }
        ]
      };
    });

    if (foodProducts.value.length > 0 && !activeVariant.value) {
      selectVariant(foodProducts.value[0], foodProducts.value[0].types[0], false);
    }
  } catch (error) {
    console.error('Error cargando productos en Generar Pedido:', error);
  } finally {
    isLoadingProducts.value = false;
  }
};

const BASE_INGREDIENT_NAMES = [
  'pan', 'pan completo', 'pan frica', 'pan marraqueta',
  'vianesa', 'carne', 'hamburguesa', 'pollo', 'lomo',
  'churrasco', 'masa', 'masa pizza'
];

const isBaseIngredient = (nombre: string) => {
  if (!nombre) return false;
  const lower = nombre.toLowerCase().trim();
  return lower.startsWith('pan ') ||
         lower === 'pan' ||
         lower === 'vianesa' ||
         lower === 'carne' ||
         lower === 'lomito' ||
         lower === 'pollo' ||
         lower === 'hamburguesa' ||
         lower === 'masa pizza' ||
         lower === 'sopaipilla' ||
         lower === 'empanada' ||
         BASE_INGREDIENT_NAMES.some(b => lower.includes(b));
};

const isPersonalizableProduct = computed(() => {
  if (!activeVariant.value) return false;
  const p = activeVariant.value;
  const cat = (p.baseProduct?.category || '').toLowerCase();
  return p.tipo_armado === 'Personalizable' || 
         cat.includes('hamburguesa') || 
         cat.includes('pizza') || 
         cat.includes('fajita') || 
         (p.cantidad_incluida && p.cantidad_incluida > 0);
});

const customizableRecipeIngredients = computed(() => {
  if (!activeVariant.value || !activeVariant.value.type || !activeVariant.value.type.producto_ingrediente) return [];
  return activeVariant.value.type.producto_ingrediente.filter((pi: any) => {
    if (pi.incluido_por_defecto === false || pi.incluido_por_defecto === 0) return false;
    const ingName = pi.ingrediente?.nombre || '';
    return !isBaseIngredient(ingName);
  });
});

const optionalExtraIngredients = computed(() => {
  if (!activeVariant.value || !activeVariant.value.type || !activeVariant.value.type.producto_ingrediente) return [];
  const seenNames = new Set<string>();
  const result: any[] = [];

  for (const pi of activeVariant.value.type.producto_ingrediente) {
    const ingName = pi.ingrediente?.nombre || '';
    if (!ingName) continue;

    if (!seenNames.has(ingName)) {
      seenNames.add(ingName);
      result.push(pi);
    }
  }

  return result;
});

const extraIngredientsCost = computed(() => {
  if (!activeVariant.value || !isPersonalizableProduct.value) return 0;
  const includedCount = activeVariant.value.cantidad_incluida || 3;
  const selectedCount = addedExtraIngredients.value.length;
  const extraCount = Math.max(0, selectedCount - includedCount);
  const extraPrice = activeVariant.value.precio_ingrediente_extra || 500;
  return extraCount * extraPrice;
});

const currentVariantUnitPrice = computed(() => {
  if (!activeVariant.value) return 0;
  const basePrice = Number(activeVariant.value.price || 0);
  return basePrice + extraIngredientsCost.value;
});

const filteredProducts = computed(() => {
  let results = foodProducts.value;
  if (selectedCategory.value !== 'Todas') {
    results = results.filter(item => item.category === selectedCategory.value);
  }
  if (productSearch.value.trim()) {
    const s = productSearch.value.toLowerCase();
    results = results.filter(item => item.name.toLowerCase().includes(s));
  }
  return results;
});

const selectVariant = (baseProduct: any, type: any, openMobile = false) => {
  const sizeName = baseProduct.activeSize;
  const tamanoId = baseProduct.sizesMap?.[sizeName] || baseProduct.tamano_id || 1;

  activeVariant.value = {
    baseProduct: baseProduct,
    baseName: baseProduct.name,
    type: type,
    size: sizeName,
    tamano_id: tamanoId,
    price: type.prices[sizeName] || 0,
    tipo_armado: baseProduct.tipo_armado || 'Estandar',
    cantidad_incluida: baseProduct.cantidad_incluida ?? 0,
    precio_ingrediente_extra: Number(baseProduct.precio_ingrediente_extra || 0)
  };
  excludedIngredients.value = [];
  addedExtraIngredients.value = [];

  if (openMobile && typeof window !== 'undefined' && window.innerWidth <= 1024) {
    isMobileRecipeOpen.value = true;
  }
};

const openProductCustomizer = (baseProduct: any, type: any) => {
  selectVariant(baseProduct, type, true);
};

const changeActiveSize = (sizeName: string) => {
  if (!activeVariant.value || !activeVariant.value.baseProduct) return;
  activeVariant.value.baseProduct.activeSize = sizeName;
  const tamanoId = activeVariant.value.baseProduct.sizesMap?.[sizeName] || activeVariant.value.baseProduct.tamano_id || 1;
  activeVariant.value.size = sizeName;
  activeVariant.value.tamano_id = tamanoId;
  activeVariant.value.price = activeVariant.value.type.prices[sizeName] || 0;
};

const toggleIngredient = (nombreIngrediente: string) => {
  if (!nombreIngrediente || isBaseIngredient(nombreIngrediente)) return;

  const index = excludedIngredients.value.indexOf(nombreIngrediente);
  if (index > -1) { 
    excludedIngredients.value.splice(index, 1); 
  } else { 
    excludedIngredients.value.push(nombreIngrediente); 
  }
};

const toggleExtraIngredient = (nombreIngrediente: string) => {
  if (!nombreIngrediente) return;

  const index = addedExtraIngredients.value.indexOf(nombreIngrediente);
  if (index > -1) { 
    addedExtraIngredients.value.splice(index, 1); 
  } else { 
    addedExtraIngredients.value.push(nombreIngrediente); 
  }
};

const addActiveVariantToCart = () => {
  if (!activeVariant.value) return;

  const isPersonalizable = isPersonalizableProduct.value;
  const exclusionKey = [...new Set(excludedIngredients.value)].sort().join('-');
  const additionKey = [...new Set(addedExtraIngredients.value)].sort().join('-');
  const cartItemId = `${activeVariant.value.type.id}_${activeVariant.value.size}_${exclusionKey}_${additionKey}`;

  const fullProductName = `${activeVariant.value.baseName}`;
  const finalUnitPrice = currentVariantUnitPrice.value;

  const existing = cartItems.value.find(item => item.id === cartItemId);
  if (existing) {
    existing.quantity++;
  } else {
    cartItems.value.push({
      id: cartItemId,
      productId: activeVariant.value.type.id,
      name: activeVariant.value.type.name,
      fullName: fullProductName,
      size: activeVariant.value.size,
      tamano_id: activeVariant.value.tamano_id,
      price: finalUnitPrice,
      quantity: 1,
      excluidos: isPersonalizable ? [] : [...new Set(excludedIngredients.value)],
      agregados: isPersonalizable ? [...new Set(addedExtraIngredients.value)] : []
    });
  }

  notify(`¡${fullProductName} añadido a la comanda!`, 'success');
  isMobileRecipeOpen.value = false;
};

const removeFromCart = (index: number) => { cartItems.value.splice(index, 1); };
const updateQuantity = (index: number, change: number) => {
  cartItems.value[index].quantity += change;
  if (cartItems.value[index].quantity <= 0) removeFromCart(index);
};

const windowQuote = computed(() => cartItems.value.reduce((sum, item) => sum + (item.price * item.quantity), 0));
const totalQuote = computed(() => `$${windowQuote.value.toLocaleString('es-CL')}`);
const totalUnits = computed(() => cartItems.value.reduce((acc, item) => acc + item.quantity, 0));

const goToStep2 = () => {
  isMobileCartOpen.value = false;
  currentStep.value = 2;
};

const nextStep = () => {
  if (currentStep.value === 1) {
    if (cartItems.value.length === 0) return notify('Debe añadir al menos un producto a la comanda.', 'warning');
    currentStep.value = 2;
  } else if (currentStep.value === 2) {
    if (!customerForm.value.nombre.trim()) return notify('Por favor, ingrese el Nombre del cliente.', 'warning');
    if (!selectedPaymentMethod.value) return notify('Por favor, seleccione un Método de Pago.', 'warning');
    currentStep.value = 3;
  }
};

const handlePhoneInput = () => { 
  if (!customerForm.value.telefono.startsWith('+56')) customerForm.value.telefono = '+56'; 
};

const confirmQuote = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;
  try {
    const payload = {
      nombre_persona: customerForm.value.nombre.trim() || 'Cliente Presencial',
      numero_telefono: customerForm.value.telefono || '',
      metodo_pago: selectedPaymentMethod.value || 'Efectivo',
      total: windowQuote.value,
      items: cartItems.value.map(item => ({
        id_producto: item.productId || 1,
        id_tamaño: item.tamano_id || 1,
        cantidad: Number(item.quantity || 1),
        precio_unitario: Number(item.price || 0),
        precio: Number(item.price || 0),
        subtotal: Number(item.price || 0) * Number(item.quantity || 1),
        format: item.size || 'Normal',
        nombre: item.fullName || item.name,
        excluidos: item.excluidos || [],
        agregados: item.agregados || [],
        modificaciones: [
          ...(item.excluidos || []).map((ex: string) => ({
            tipo: 'Exclusión',
            precio: 0,
            ingrediente: ex
          })),
          ...(item.agregados || []).map((ag: string) => ({
            tipo: 'Agregado',
            precio: 0,
            ingrediente: ag
          }))
        ]
      }))
    };

    try {
      await orderService.createOrder(payload);
    } catch {
      await orderService.createPublicOrder(payload);
    }
    notify('¡Pedido registrado y enviado a cocina exitosamente!', 'success');
    currentStep.value = 1; 
    cartItems.value = [];
    activeVariant.value = null;
    router.push('/general-home/orders');
  } catch (error: any) {
    console.error('Error al procesar el pedido:', error); 
    const msg = error.response?.data?.message || 'Error al procesar el pedido';
    notify(msg, 'warning');
  } finally { 
    isSubmitting.value = false; 
  }
};

onMounted(async () => { 
  cargarMetodosSimulados(); 
  fetchProducts(); 
  try {
    shiftWindow.value = await cashFlowService.fetchShiftWindowFromBackend();
  } catch (e) {
    console.warn('Error al cargar horario en POS:', e);
  }
});
</script>

<style scoped>
/* ----------------------------------------------------
   1. CONTENEDOR PRINCIPAL POS
---------------------------------------------------- */
.pos-quote-wizard {
  width: 100%;
  max-width: 1680px;
  margin: 0 auto;
  padding: 18px 24px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  box-sizing: border-box;
}

/* ----------------------------------------------------
   2. HEADER & PASOS
---------------------------------------------------- */
.wizard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 16px;
  background: white;
  padding: 16px 24px;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.brand-title-row {
  display: flex;
  align-items: center;
  gap: 10px;
}

.pos-shift-badge {
  font-size: 0.75rem;
  font-weight: 800;
  padding: 3px 10px;
  border-radius: 999px;
  margin-left: 6px;
}

.badge-shift-live {
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #86efac;
}

.badge-shift-off {
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #cbd5e1;
}

.header-shift-hours {
  font-weight: 700;
  color: #ff6b00;
}

.pos-outside-shift-banner {
  display: flex;
  align-items: center;
  gap: 10px;
  background: #fff7ed;
  border: 1.5px solid #fed7aa;
  padding: 10px 16px;
  border-radius: 12px;
  font-size: 0.85rem;
  color: #9a3412;
  font-weight: 600;
}

.pos-outside-shift-banner .warning-icon {
  color: #ea580c;
  flex-shrink: 0;
}

.brand-icon {
  color: #ff6b00;
}

.header-brand h1 {
  font-size: 1.4rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0;
}

.header-subtitle {
  font-size: 0.82rem;
  color: #64748b;
  margin: 2px 0 0;
  font-weight: 600;
}

.steps-indicator {
  display: flex;
  align-items: center;
  gap: 8px;
}

.step-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 14px;
  border-radius: 999px;
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  color: #64748b;
  font-size: 0.85rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.step-num {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.78rem;
  font-weight: 900;
}

.step-pill.active {
  background: #fff7ed;
  border-color: #ff6b00;
  color: #ea580c;
  box-shadow: 0 2px 8px rgba(255, 107, 0, 0.18);
}

.step-pill.active .step-num {
  background: #ff6b00;
  color: white;
}

.step-pill.completed {
  background: #f0fdf4;
  border-color: #86efac;
  color: #16a34a;
}

.step-pill.completed .step-num {
  background: #22c55e;
  color: white;
}

.step-line {
  width: 24px;
  height: 2px;
  background: #e2e8f0;
  border-radius: 2px;
}

.step-line.active {
  background: #ff6b00;
}

/* ----------------------------------------------------
   3. LAYOUT 3 COLUMNAS POS (ESCRITORIO)
---------------------------------------------------- */
.product-layout {
  display: grid;
  grid-template-columns: minmax(500px, 2.6fr) minmax(320px, 1.2fr) minmax(310px, 1.1fr);
  gap: 18px;
  align-items: start;

  height: 100vh;
}

/* ----------------------------------------------------
   4. COLUMNA 1: CATÁLOGO
---------------------------------------------------- */
.catalog-section {
  /* MUY IMPORTANTE */
  height: 100%;
  min-height: 0;
  overflow: hidden;
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  padding: 18px;

  display: flex;
  flex-direction: column;
  gap: 14px;

  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}


.catalog-toolbar {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.search-input-box {
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 14px;
  color: #94a3b8;
  pointer-events: none;
}

.input-search-product {
  width: 100%;
  padding: 10px 38px 10px 40px;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.88rem;
  color: #1e293b;
  background: #f8fafc;
  outline: none;
  font-family: inherit;
  transition: all 0.2s ease;
}

.input-search-product:focus {
  border-color: #ff6b00;
  background: white;
  box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
}

.btn-clear-search {
  position: absolute;
  right: 12px;
  background: #e2e8f0;
  border: none;
  border-radius: 50%;
  width: 20px;
  height: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #475569;
  cursor: pointer;
}

.categories-scroll-row {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  padding-bottom: 4px;
  scrollbar-width: thin;
  -webkit-overflow-scrolling: touch;
}

.category-chip {
  white-space: nowrap;
  padding: 7px 14px;
  border-radius: 999px;
  background: #f1f5f9;
  border: 1.5px solid #e2e8f0;
  color: #475569;
  font-size: 0.82rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.category-chip:hover {
  border-color: #ff6b00;
  color: #ff6b00;
  background: #fff7ed;
}

.category-chip.active {
  background: #ff6b00;
  border-color: #ff6b00;
  color: white;
  box-shadow: 0 2px 8px rgba(255, 107, 0, 0.25);
}

.products-grid-admin {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;

  flex: 1;
  min-height: 0;

  overflow-y: auto;
  overflow-x: hidden;

  padding: 4px 6px 10px 0;
  align-content: start;
}

.products-grid-admin::-webkit-scrollbar {
  width: 6px;
}

.products-grid-admin::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.products-grid-admin::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.products-grid-admin::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Tarjetas de Producto */
.pos-product-card {
  background: white;
  border-radius: 14px;
  border: 1.5px solid #e2e8f0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  min-height: 260px;
}

.pos-product-card:hover {
  transform: translateY(-2px);
  border-color: #ff9800;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.pos-product-card.is-selected {
  border-color: #ff6b00;
  background: #fffaf5;
  box-shadow: 0 0 0 2px #ff6b00, 0 8px 22px rgba(255, 107, 0, 0.14);
}

.card-image-wrap {
  position: relative;
  width: 100%;
  height: 130px;
  overflow: hidden;
  background: #f1f5f9;
  display: block;
}

.card-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.3s ease;
}

.pos-product-card:hover .card-image-wrap img {
  transform: scale(1.05);
}

.category-badge {
  position: absolute;
  top: 8px;
  left: 8px;
  background: rgba(15, 23, 42, 0.75);
  backdrop-filter: blur(4px);
  color: white;
  font-size: 0.7rem;
  font-weight: 800;
  padding: 3px 8px;
  border-radius: 6px;
}

.pos-card-body {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  flex: 1;
}

.pos-product-title {
  font-size: 0.95rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0;
  line-height: 1.3;
}

.sizes-chips-row {
  display: flex;
  gap: 4px;
  flex-wrap: wrap;
}

.size-btn {
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid #cbd5e1;
  background: #f8fafc;
  color: #475569;
  font-size: 0.72rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.15s ease;
}

.size-btn.active-size {
  background: #ff6b00;
  border-color: #ff6b00;
  color: white;
}

.single-size-tag {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 700;
}

.desktop-only-sizes {
  display: flex;
}

.mobile-size-badge-indicator {
  display: none;
}

.card-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: auto;
  padding-top: 6px;
}

.product-card-price {
  font-size: 1.05rem;
  font-weight: 900;
  color: #059669;
}

.btn-select-product {
  padding: 6px 12px;
  border-radius: 8px;
  border: 1.5px solid #ff6b00;
  background: white;
  color: #ff6b00;
  font-size: 0.78rem;
  font-weight: 900;
  display: flex;
  align-items: center;
  gap: 4px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.pos-product-card:hover .btn-select-product,
.pos-product-card.is-selected .btn-select-product {
  background: #ff6b00;
  color: white;
}

/* ----------------------------------------------------
   5. COLUMNA 2 (DESKTOP): RECETA
---------------------------------------------------- */
.recipe-customizer-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.customizer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1ece7;
  padding-bottom: 12px;
}

.header-left-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 900;
  font-size: 0.95rem;
  color: #1e293b;
}

.customizer-body {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.selected-product-banner {
  background: #fff7ed;
  border: 1.5px solid #ffedd5;
  border-radius: 12px;
  padding: 12px 14px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.banner-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 900;
  color: #c2410c;
}

.banner-size-badge {
  display: inline-block;
  background: #ffedd5;
  color: #9a3412;
  font-size: 0.72rem;
  font-weight: 800;
  padding: 2px 6px;
  border-radius: 4px;
  margin-top: 2px;
}

.banner-base-price {
  font-size: 1.25rem;
  font-weight: 900;
  color: #ea580c;
}

.modal-sizes-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sizes-label {
  font-size: 0.78rem;
  font-weight: 800;
  color: #475569;
  text-transform: uppercase;
}

.modal-sizes-row {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.modal-size-pill {
  padding: 6px 12px;
  border-radius: 8px;
  border: 1.5px solid #cbd5e1;
  background: #f8fafc;
  color: #334155;
  font-size: 0.8rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
}

.modal-size-pill.active {
  background: #ff6b00;
  border-color: #ff6b00;
  color: white;
}

.ingredients-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.ingredients-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.ingredients-section-header h4 {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 900;
  color: #1e293b;
}

.section-hint {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 700;
}

.ingredients-chips-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.ingredient-chip-toggle {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 7px 12px;
  border-radius: 999px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #334155;
  font-size: 0.82rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.2s ease;
  user-select: none;
}

.chip-status-icon {
  width: 18px;
  height: 18px;
  border-radius: 50%;
  background: #e2e8f0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* MODO ESTÁNDAR */
.standard-chip {
  background: #f0fdf4;
  border-color: #bbf7d0;
  color: #15803d;
}

.standard-chip .chip-status-icon {
  background: #22c55e;
  color: white;
}

.standard-chip.is-removed {
  background: #fef2f2;
  border-color: #fecaca;
  color: #dc2626;
  text-decoration: line-through;
  opacity: 0.75;
}

.standard-chip.is-removed .chip-status-icon {
  background: #ef4444;
  color: white;
}

/* MODO PERSONALIZABLE */
.ingredient-chip-toggle.is-added {
  background: #eff6ff;
  border-color: #bfdbfe;
  color: #1d4ed8;
}

.ingredient-chip-toggle.is-added .chip-status-icon {
  background: #3b82f6;
  color: white;
}

.btn-add-to-cart-pos {
  width: 100%;
  padding: 13px;
  background: #ff6b00;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 900;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(255, 107, 0, 0.3);
}

.btn-add-to-cart-pos:hover {
  background: #e8590c;
  transform: translateY(-1px);
}

.empty-selection-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 30px 10px;
  color: #94a3b8;
}

.placeholder-icon-box {
  width: 55px;
  height: 55px;
  border-radius: 50%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 12px;
  color: #cbd5e1;
}

.empty-selection-placeholder h4 {
  margin: 0 0 4px;
  color: #475569;
  font-size: 0.95rem;
}

.empty-selection-placeholder p {
  font-size: 0.8rem;
  margin: 0;
}

/* ----------------------------------------------------
   6. COLUMNA 3 (DESKTOP): COMANDA
---------------------------------------------------- */
.pos-cart-sidebar {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  display: flex;
  flex-direction: column;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  position: sticky;
  top: 16px;
}

.pos-cart-header {
  padding: 14px 16px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1ece7;
}

.header-cart-right {
  display: flex;
  align-items: center;
  gap: 8px;
}

.cart-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 900;
  font-size: 0.95rem;
  color: #1e293b;
}

.cart-units-pill {
  background: #fff7ed;
  color: #ea580c;
  border: 1px solid #ffedd5;
  padding: 2px 8px;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 800;
}

.cart-items-scroll {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  max-height: calc(100vh - 350px);
  overflow-y: auto;
}

.empty-cart-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 35px 15px;
  color: #94a3b8;
}

.empty-cart-icon {
  margin-bottom: 8px;
  color: #cbd5e1;
}

.empty-cart-state p {
  margin: 0;
  font-weight: 800;
  color: #475569;
}

.empty-cart-state small {
  font-size: 0.75rem;
  margin-top: 4px;
}

.pos-cart-item {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 10px 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.cart-item-title {
  font-size: 0.88rem;
  font-weight: 900;
  color: #1e293b;
}

.cart-badges-wrap {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 2px;
}

.badge-tag-removed {
  font-size: 0.7rem;
  background: #fee2e2;
  color: #b91c1c;
  padding: 1px 6px;
  border-radius: 4px;
  font-weight: 800;
}

.badge-tag-added {
  font-size: 0.7rem;
  background: #eff6ff;
  color: #1d4ed8;
  padding: 1px 6px;
  border-radius: 4px;
  font-weight: 800;
}

.cart-item-pricing {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 4px;
}

.cart-item-unit-price {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 700;
}

.cart-item-subtotal {
  font-size: 0.95rem;
  font-weight: 900;
  color: #059669;
}

.cart-item-stepper {
  display: flex;
  align-items: center;
  gap: 8px;
  border-top: 1px dashed #e2e8f0;
  padding-top: 8px;
}

.btn-stepper {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  border: 1px solid #cbd5e1;
  background: white;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-stepper:hover {
  background: #f1f5f9;
  border-color: #ff6b00;
  color: #ff6b00;
}

.stepper-count {
  font-size: 0.88rem;
  font-weight: 900;
  min-width: 20px;
  text-align: center;
  color: #1e293b;
}

.btn-trash-item {
  margin-left: auto;
  background: transparent;
  border: none;
  color: #ef4444;
  cursor: pointer;
  padding: 4px;
  border-radius: 6px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background 0.15s ease;
}

.btn-trash-item:hover {
  background: #fee2e2;
}

.pos-cart-footer {
  padding: 14px 16px;
  border-top: 1px solid #f1ece7;
  display: flex;
  flex-direction: column;
  gap: 12px;
  background: #faf7f4;
  border-radius: 0 0 16px 16px;
}

.total-breakdown-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.total-breakdown-row span {
  font-size: 0.85rem;
  font-weight: 800;
  color: #475569;
}

.total-breakdown-row strong {
  font-size: 1.35rem;
  font-weight: 900;
  color: #059669;
}

.btn-continue-checkout {
  width: 100%;
  padding: 12px;
  background: #ff6b00;
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 0.92rem;
  font-weight: 900;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(255, 107, 0, 0.25);
}

.btn-continue-checkout:hover:not(:disabled) {
  background: #e8590c;
  transform: translateY(-1px);
}

.btn-continue-checkout:disabled {
  background: #cbd5e1;
  box-shadow: none;
  cursor: not-allowed;
}

/* ----------------------------------------------------
   7. MODALES / BOTTOM SHEETS PARA MÓVIL
---------------------------------------------------- */
.mobile-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
}

.mobile-modal-backdrop {
  position: absolute;
  inset: 0;
  background: rgba(15, 23, 42, 0.65);
  backdrop-filter: blur(4px);
}

.mobile-sheet-card {
  position: relative;
  background: white;
  width: 100%;
  max-height: 86vh;
  border-radius: 22px 22px 0 0;
  display: flex;
  flex-direction: column;
  box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.25);
  z-index: 10001;
  overflow: hidden;
}

.sheet-drag-pill {
  width: 38px;
  height: 4px;
  background: #cbd5e1;
  border-radius: 999px;
  margin: 10px auto 4px auto;
  flex-shrink: 0;
}

.sheet-header {
  padding: 10px 16px 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #f1ece7;
  flex-shrink: 0;
}

.sheet-title-left {
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 900;
  font-size: 0.95rem;
  color: #1e293b;
}

.btn-close-modal {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  background: #f1f5f9;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}

.sheet-scroll-body {
  padding: 14px 16px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 14px;
  flex: 1;
}

.sheet-sticky-bottom-bar {
  padding: 12px 16px;
  background: white;
  border-top: 1px solid #f1ece7;
  box-shadow: 0 -4px 14px rgba(0, 0, 0, 0.05);
  flex-shrink: 0;
}

.sheet-sticky-bottom-bar.pos-cart-footer {
  border-radius: 0;
}

/* Animaciones del Modal Móvil */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.25s ease;
}

.modal-fade-enter-active .mobile-sheet-card,
.modal-fade-leave-active .mobile-sheet-card {
  transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .mobile-sheet-card,
.modal-fade-leave-to .mobile-sheet-card {
  transform: translateY(100%);
}

.mobile-floating-cart-bar {
  display: none;
}

/* ----------------------------------------------------
   8. PASO 2: CLIENTE Y MÉTODO DE PAGO
---------------------------------------------------- */
.client-step-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  max-width: 750px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
}

.section-intro {
  margin-bottom: 20px;
  border-bottom: 1px solid #f1ece7;
  padding-bottom: 14px;
}

.section-intro h2 {
  font-size: 1.35rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0 0 4px;
}

.section-intro p {
  color: #64748b;
  font-size: 0.85rem;
  margin: 0;
}

.client-data-form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.form-field-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-field-group label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.82rem;
  font-weight: 800;
  color: #334155;
}

.pos-form-input {
  padding: 12px 14px;
  border: 1.5px solid #e2e8f0;
  border-radius: 12px;
  font-size: 0.95rem;
  color: #1e293b;
  background: #f8fafc;
  outline: none;
  font-family: inherit;
  transition: all 0.2s ease;
}

.pos-form-input:focus {
  border-color: #ff6b00;
  background: white;
  box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.12);
}

.payment-method-selector-section {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.section-label {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 0.85rem;
  font-weight: 900;
  color: #1e293b;
}

.payment-methods-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.payment-method-btn {
  background: #f8fafc;
  border: 1.5px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
  position: relative;
  user-select: none;
}

.payment-icon-box {
  color: #475569;
  transition: transform 0.2s;
}

.payment-btn-label {
  font-size: 0.82rem;
  font-weight: 800;
  color: #334155;
}

.payment-method-btn:hover {
  border-color: #ff6b00;
  background: #fffaf5;
}

.payment-method-btn.active {
  background: #fff7ed;
  border-color: #ff6b00;
  box-shadow: 0 4px 14px rgba(255, 107, 0, 0.2);
}

.payment-method-btn.active .payment-icon-box,
.payment-method-btn.active .payment-btn-label {
  color: #ea580c;
}

.payment-check-icon {
  position: absolute;
  top: 8px;
  right: 8px;
  color: #ff6b00;
}

.step-nav-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  border-top: 1px solid #f1ece7;
  padding-top: 18px;
  margin-top: 6px;
}

.btn-pos-primary {
  padding: 12px 22px;
  border-radius: 12px;
  border: none;
  background: #ff6b00;
  color: white;
  font-size: 0.9rem;
  font-weight: 900;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 14px rgba(255, 107, 0, 0.25);
}

.btn-pos-primary:hover {
  background: #e8590c;
  transform: translateY(-1px);
}

.btn-pos-secondary {
  padding: 12px 20px;
  border-radius: 12px;
  border: 1.5px solid #cbd5e1;
  background: white;
  color: #475569;
  font-size: 0.9rem;
  font-weight: 800;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.2s ease;
}

.btn-pos-secondary:hover {
  border-color: #94a3b8;
  background: #f8fafc;
}

/* ----------------------------------------------------
   9. PASO 3: CONFIRMACIÓN DE COMANDA
---------------------------------------------------- */
.summary-receipt-card {
  background: white;
  border-radius: 16px;
  border: 1px solid #f1ece7;
  padding: 24px;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
  max-width: 750px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.receipt-header {
  text-align: center;
  border-bottom: 1px solid #f1ece7;
  padding-bottom: 14px;
}

.receipt-icon {
  color: #16a34a;
  margin-bottom: 6px;
}

.receipt-header h2 {
  font-size: 1.4rem;
  font-weight: 900;
  color: #1e293b;
  margin: 0 0 4px;
}

.receipt-header p {
  color: #64748b;
  font-size: 0.85rem;
  margin: 0;
}

.receipt-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.receipt-section {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.receipt-section h3 {
  margin: 0;
  font-size: 0.88rem;
  font-weight: 900;
  color: #1e293b;
  border-bottom: 1px solid #e2e8f0;
  padding-bottom: 6px;
}

.client-meta-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.85rem;
}

.client-meta-row span {
  color: #64748b;
}

.client-meta-row strong {
  color: #1e293b;
}

.payment-badge-highlight {
  background: #ffedd5;
  color: #c2410c;
  padding: 2px 8px;
  border-radius: 6px;
  font-size: 0.8rem;
}

.receipt-products-list {
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 250px;
  overflow-y: auto;
}

.receipt-item-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  font-size: 0.85rem;
  gap: 8px;
}

.receipt-item-left {
  display: flex;
  gap: 8px;
}

.receipt-qty {
  font-weight: 900;
  color: #ff6b00;
}

.receipt-item-names {
  display: flex;
  flex-direction: column;
}

.receipt-tags {
  margin-top: 2px;
}

.tag-red {
  color: #dc2626;
  font-size: 0.72rem;
  font-weight: 800;
}

.tag-blue {
  color: #2563eb;
  font-size: 0.72rem;
  font-weight: 800;
}

.receipt-price {
  font-weight: 900;
  color: #059669;
}

.receipt-total-banner {
  background: #f0fdf4;
  border: 1.5px solid #bbf7d0;
  border-radius: 14px;
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.receipt-total-banner span {
  font-size: 0.95rem;
  font-weight: 900;
  color: #166534;
}

.receipt-total-banner strong {
  font-size: 1.5rem;
  font-weight: 900;
  color: #15803d;
}

.receipt-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
}

.btn-confirm-order {
  flex: 1;
  justify-content: center;
  font-size: 1rem;
  padding: 14px;
}

/* Skeletons y utilidades */
.pos-card-skeleton {
  background: white;
  border-radius: 14px;
  border: 1.5px solid #e2e8f0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.skeleton-img {
  height: 110px;
  background: linear-gradient(90deg, #f1ece7 25%, #f8f6f3 50%, #f1ece7 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-body {
  padding: 12px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.skeleton-pill {
  height: 16px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f1ece7 25%, #f8f6f3 50%, #f1ece7 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.width-80 { width: 80px; }
.width-120 { width: 120px; }
.margin-top-4 { margin-top: 4px; }

@keyframes shimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

.empty-products-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 16px;
  color: #94a3b8;
  text-align: center;
  gap: 8px;
}

.empty-icon {
  color: #cbd5e1;
}

.btn-reset-filters {
  margin-top: 8px;
  padding: 8px 16px;
  border-radius: 8px;
  border: 1.5px solid #ff6b00;
  background: white;
  color: #ff6b00;
  font-weight: 800;
  font-size: 0.85rem;
  cursor: pointer;
}

/* ----------------------------------------------------
   10. RESPONSIVO MÓVIL & TABLET
---------------------------------------------------- */
@media (min-width: 1025px) and (max-width: 1200px) {
  .product-layout {
    grid-template-columns:
      minmax(400px, 2.6fr)
      minmax(250px, 1.2fr)
      minmax(250px, 1.1fr);

    gap: 12px;
  }
}

@media (max-width: 1024px) {
  .pos-quote-wizard {
    padding: 8px 10px 90px 10px;
    gap: 10px;
    max-width: 100vw;
  }

  .wizard-header {
    padding: 10px 12px;
    flex-direction: column;
    align-items: stretch;
    gap: 8px;
    border-radius: 14px;
  }

  .header-brand {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
  }

  .header-brand h1 {
    font-size: 1.15rem;
  }

  .header-subtitle {
    display: none;
  }

  .steps-indicator {
    justify-content: space-between;
    width: 100%;
    gap: 4px;
  }

  .step-pill {
    padding: 5px 8px;
    font-size: 0.74rem;
    gap: 4px;
    flex: 1;
    justify-content: center;
  }

  .step-pill .step-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .step-line {
    width: 8px;
    flex-shrink: 0;
  }

  .product-layout {
    display: block;
    width: 100%;
  }

  /* Ocultamos las columnas fijas de escritorio en móvil */
  .desktop-only-pane {
    display: none !important;
  }

  .catalog-section {
    min-height: 0;
  }

  .catalog-toolbar {
    position: relative;
    padding: 0;
    gap: 8px;
  }

  .search-input-box {
    background: white;
    border-radius: 12px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
  }

  .input-search-product {
    background: transparent;
    border: none;
    padding: 10px 34px 10px 38px;
    font-size: 0.88rem;
  }

  .categories-scroll-row {
    display: flex;
    gap: 6px;
    overflow-x: auto;
    padding: 2px 0 4px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
    -webkit-overflow-scrolling: touch;
  }

  .categories-scroll-row::-webkit-scrollbar {
    display: none;
  }

  .category-chip {
    padding: 6px 14px;
    font-size: 0.8rem;
    font-weight: 800;
    flex-shrink: 0;
    background: white;
    border: 1.5px solid #e2e8f0;
    color: #475569;
    border-radius: 999px;
  }

  .category-chip.active {
    background: #ff6b00;
    border-color: #ff6b00;
    color: white;
    box-shadow: 0 3px 10px rgba(255, 107, 0, 0.35);
  }

  .desktop-only-sizes {
    display: none !important;
  }

  .mobile-size-badge-indicator {
    display: block;
    margin: 2px 0;
  }

  .multi-sizes-tag {
    display: inline-block;
    font-size: 0.7rem;
    color: #ea580c;
    background: #fff7ed;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 800;
  }

  .single-format-tag {
    font-size: 0.7rem;
    color: #94a3b8;
    font-weight: 700;
  }

  .products-grid-admin {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    max-height: none;
    overflow-y: visible;
    padding: 0 0 20px 0;
  }

  .pos-product-card {
    border-radius: 14px;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
  }

  .card-image-wrap {
    height: 100px;
  }

  .pos-card-body {
    padding: 8px 10px;
    gap: 4px;
  }

  .pos-product-title {
    font-size: 0.85rem;
    font-weight: 900;
    color: #1e293b;
    line-height: 1.2;
  }

  .card-footer-row {
    margin-top: auto;
    padding-top: 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .product-card-price {
    font-size: 0.92rem;
    font-weight: 900;
    color: #059669;
  }

  .btn-select-product {
    padding: 5px 10px;
    font-size: 0.74rem;
    font-weight: 900;
    background: #ff6b00;
    color: white;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 3px;
  }

  /* MODALES Y BOTTOM SHEETS EN MÓVIL */
  .mobile-modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
  }

  .mobile-sheet-card {
    max-height: 90vh;
    border-radius: 22px 22px 0 0;
    background: white;
    box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.25);
  }

  .sheet-header {
    padding: 12px 16px;
  }

  .sheet-scroll-body {
    padding: 12px 14px;
    gap: 12px;
    overflow-y: auto;
  }

  .ingredients-chips-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }

  .ingredient-chip-toggle {
    padding: 8px 10px;
    font-size: 0.78rem;
    border-radius: 10px;
    min-height: 38px;
  }

  .modal-sizes-row {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  .modal-size-pill {
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 0.8rem;
    font-weight: 800;
  }

  .sheet-sticky-bottom-bar {
    padding: 12px 14px;
    background: white;
    border-top: 1px solid #f1f5f9;
    box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.05);
  }

  .btn-add-to-cart-pos {
    padding: 14px;
    font-size: 0.95rem;
    font-weight: 900;
    border-radius: 12px;
  }

  /* BARRA FLOTANTE MÓVIL INFERIOR */
  .mobile-floating-cart-bar {
    position: fixed;
    bottom: 12px;
    left: 12px;
    right: 12px;
    background: #1e293b;
    color: white;
    padding: 10px 14px;
    border-radius: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
    z-index: 990;
    cursor: pointer;
  }

  .cart-bar-info {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .cart-bubble-icon {
    position: relative;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffb076;
  }

  .cart-bubble-icon .badge-count {
    position: absolute;
    top: -4px;
    right: -4px;
    background: #ff6b00;
    color: white;
    font-size: 0.7rem;
    font-weight: 900;
    padding: 1px 5px;
    border-radius: 999px;
  }

  .cart-bar-text-group {
    display: flex;
    flex-direction: column;
  }

  .cart-bar-sub {
    font-size: 0.7rem;
    color: #94a3b8;
    text-transform: uppercase;
    font-weight: 700;
  }

  .cart-bar-total {
    font-size: 1.05rem;
    font-weight: 900;
    color: #ffffff;
  }

  .btn-checkout-mobile {
    padding: 9px 14px;
    background: #ff6b00;
    border: none;
    border-radius: 10px;
    color: white;
    font-weight: 900;
    font-size: 0.82rem;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
  }

  /* Paso 2 */
  .client-step-card {
    padding: 16px 14px;
  }

  .form-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .pos-form-input {
    font-size: 16px;
    padding: 12px;
  }

  .payment-methods-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
  }

  .payment-method-btn {
    padding: 12px 8px;
  }

  .step-nav-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn-pos-primary,
  .btn-pos-secondary {
    width: 100%;
    justify-content: center;
    padding: 14px;
    box-sizing: border-box;
  }

  /* Paso 3 */
  .summary-receipt-card {
    padding: 16px 14px;
  }

  .receipt-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .receipt-actions {
    flex-direction: column-reverse;
    gap: 8px;
  }

  .btn-confirm-order {
    width: 100%;
    justify-content: center;
    padding: 14px;
    box-sizing: border-box;
  }
}

@media (max-width: 420px) {
  .step-pill .step-text {
    display: none;
  }

  .step-pill {
    padding: 6px 10px;
    flex: 0 0 auto;
  }

  .step-line {
    flex: 1;
    min-width: 14px;
  }
}

@media (max-width: 360px) {
  .products-grid-admin {
    grid-template-columns: 1fr;
  }

  .ingredients-chips-grid {
    grid-template-columns: 1fr;
  }
}
</style>