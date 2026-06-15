<template>
  <footer class="footer-container">
    <div class="footer-grid">
      <!-- 1. Sección Marca -->
      <div class="footer-brand">
        <h3 class="brand-title">
          <div class="footer-logo-box">
            <img src="@/assets/logo_jairo.png" alt="Logo" class="footer-logo"/>
          </div>
          <span class="brand-text-styled">J.Junior</span>
        </h3>
        <p class="brand-description">
          ¡Te esperamos para tu bajón!
        </p>
      </div>

      <!-- 2. Sección Soporte (Compactada en una sola lista estructural) -->
      <div class="footer-info-section">
        <h4 class="section-subtitle">Información</h4>
        <ul class="info-list">
          <!-- Soporte -->
          <li class="info-item">
            <strong>Teléfono:</strong> +569 39320226
          </li>
          
          <!-- Horarios -->
          <li class="info-item item-group">
            <strong>Horarios de atención:</strong>
            <span>Dom a Jue: 19:00 - 1:00</span>
            <span>Vie y Sáb: 19:00 - 2:00</span>
          </li>
          
          <!-- Ubicación -->
          <li class="info-item">
            <strong>Ubicación:</strong> Psj Oscar Castro 11012, La Pintana
          </li>
        </ul>
      </div>

      <!-- 3. Sección Redes Sociales -->
      <div class="footer-social-section">
        <h4 class="section-subtitle">Síguenos</h4>
        <div class="social-links">
          <a href="https://www.instagram.com/j.junior_foodtruck/?hl=es" target="_blank" class="social-link">
            <div class="social-item">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="social-icon">
                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
              </svg>
              <span>Instagram</span>
            </div>
          </a>
        </div>
      </div>
    </div>

    <!-- Línea final de derechos de autor -->
    <div class="footer-bottom">
      <p>© 2026 J.Jairo. Todos los derechos reservados.</p>
    </div>
  </footer>
</template>

<style scoped>
.footer-container {
  background-color: #5a3614; 
  color: #f1f3f5;
  font-family: 'Inter', sans-serif;
  padding: 3rem 1.5rem 1.5rem 1.5rem;
  border-top: 1px solid #eeedee;
  margin-top: auto; 
  width: 100%;
  box-sizing: border-box;
}

.footer-grid {
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 40px;
  padding-bottom: 20px;
  max-width: 100%;
  overflow-x: hidden;
}

.footer-brand,
.footer-info-section,
.footer-social-section {
  display: flex;
  flex-direction: column;
  align-items: center; 
  text-align: center;  
}

.brand-title {
  margin: 0 0 12px 0;
  display: flex;
  align-items: center;
  gap: 10px; 
}

.brand-text-styled {
  font-size: clamp(1.4rem, 4vw, 1.8rem);
  font-weight: 900;
  font-style: italic;
  font-family: 'Arial Black', Impact, sans-serif;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 1px;
  text-shadow: 
    -2px -2px 0 #000,  2px -2px 0 #000, -2px  2px 0 #000,  2px  2px 0 #000,
    -2px  0px 0 #000,  2px  0px 0 #000,  0px -2px 0 #000,  0px  2px 0 #000;
}

.brand-description {
  font-size: 0.95rem;
  line-height: 1.6;
  color: #ffffff;
  margin: 0;
  max-width: 280px;
}

/* Título de sección unificado y limpio */
.section-subtitle {
  font-size: 1.1rem;
  font-weight: 750;
  color: #F4E1D2; 
  margin: 0 0 12px 0; /* Espaciado inferior controlado */
}

/* Lista limpia y compacta */
.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center; 
  gap: 14px; /* Separación uniforme entre bloques de información */
  font-size: 0.95rem;
  color: #f1f3f5;
}

.info-item {
  display: flex;
  color: #dddddd;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}

/* Agrupación especial para que los horarios no se dispersen */
.item-group span {
  font-size: 0.9rem;
  color: #dddddd;
}

.info-list strong {
  color: #E28743; 
}

.social-links {
  display: flex;
  flex-direction: column;
  align-items: center; 
}

.social-link {
  color: #ffffff;
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 600;
  transition: all 0.2s ease;
  width: fit-content;
}

.social-item {
  display: flex;
  align-items: center;
  gap: 8px;
}

.social-link:hover {
  color: #E28743;
  transform: translateY(-2px); 
}

.social-icon {
  stroke: currentColor;
}

.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  border-top: 1px solid rgba(255, 255, 255, 0.15);
  padding-top: 15px;
  text-align: center;
  font-size: 0.85rem;
  color: #dddddd;
}

.footer-logo-box {
  display: flex;
  align-items: center;
}

.footer-logo {
  width: 45px;
  height: 45px;
  object-fit: contain;
}

@media (max-width: 768px) {
  .footer-container {
    padding: 2rem 1rem 1.5rem 1rem;
  }
  
  .footer-grid {
    gap: 25px; /* Menor separación en móviles */
  }
}
</style>