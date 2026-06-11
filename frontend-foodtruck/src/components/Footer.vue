<template>
  <footer class="footer-container">
    <div class="footer-grid">
      <div class="footer-brand">
        <h3 class="brand-title">
          <div class="footer-logo-box">
            <img src="@/assets/logo_dicreme.png" alt="Logo" class="footer-logo"/>
          </div>
          <span>Di Creme</span>
        </h3>
        <p class="brand-description">
          Tu aliado confiable para el éxito en la distribución de helados artesanal.
        </p>
      </div>

      <div class="footer-info-section">
        <h4 class="section-subtitle">Soporte y Contacto</h4>
        <ul class="info-list">
          <li><strong>Email:</strong> info@dicreme.com</li>
          <li><strong>Teléfono:</strong> +56 9 1234 5678</li>
        </ul>
      </div>

      <div class="footer-social-section">
        <h4 class="section-subtitle">Síguenos</h4>
        <div class="social-links">
          <a href="https://www.facebook.com/p/Helader%C3%ADa-Di-Creme-100083180564881" target="_blank" class="social-link">
            <div class="social-item">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="social-icon">
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
              </svg>
              <span>Facebook</span>
            </div>
          </a>

          <a href="https://www.instagram.com/dicreme.cl/" target="_blank" class="social-link">
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

    <div class="footer-bottom">
      <p>© 2026 Di Creme. Todos los derechos reservados.</p>
    </div>
  </footer>
</template>

<style scoped>
.footer-container {
  background-color: #322c44; 
  color: #f1f3f5;
  font-family: 'Inter', sans-serif;
  padding: 40px 32px 20px 32px;
  border-top: 1px solid #eeedee;
  margin-top: auto; 
}

.footer-grid {
  max-width: 1200px;
  margin: 0 auto;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 40px;
  padding-bottom: 30px;
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
  font-size: 1.6rem;
  font-weight: 800;
  color: #e4869f; 
  margin: 0 0 12px 0;
  letter-spacing: -0.5px;
  display: flex;
  align-items: center;
  gap: 10px; 
}

.brand-description {
  font-size: 0.9rem;
  line-height: 1.6;
  color: #9793a0;
  margin: 0;
  max-width: 280px;
}

.section-subtitle {
  font-size: 1rem;
  font-weight: 750;
  color: #ffffff;
  margin: 0 0 16px 0;
}

.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center; 
  gap: 10px;
  font-size: 0.9rem;
  color: #ced4da;
}

.info-list strong {
  color: #e4869f;
}

.social-links {
  display: flex;
  flex-direction: column;
  align-items: center; 
  gap: 12px;
}

.social-link {
  color: #ced4da;
  text-decoration: none;
  font-size: 0.9rem;
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
  color: #e4869f;
  transform: scale(1.05); 
}

.social-icon {
  transition: stroke 0.2s ease;
}

.footer-bottom {
  max-width: 1200px;
  margin: 0 auto;
  border-top: 1px solid #495057;
  padding-top: 20px;
  text-align: center;
  font-size: 0.8rem;
  color: #868e96;
}

@media (max-width: 768px) {
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 30px;
  }
}

.footer-logo-box {
  display: flex;
  align-items: center;
}

.footer-logo {
  width: 40px;
  height: 40px;
  object-fit: contain;
}
</style>