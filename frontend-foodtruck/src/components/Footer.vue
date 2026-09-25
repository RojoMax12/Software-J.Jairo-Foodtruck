<template>
  <footer class="footer-container" role="contentinfo">
    <div class="footer-inner">
      <div class="footer-grid">
        <!-- 1. IDENTIDAD DE MARCA -->
        <div class="footer-col footer-brand">
          <div class="brand-title">
            <img src="@/assets/logo_jairo.webp" alt="Foodtruck J.Junior Logo" class="footer-logo" />
            <span class="brand-text-styled">J.Junior</span>
          </div>
          <p class="brand-tagline">
            Comida rápida artesanal, sándwiches, vienesas y el mejor bajón en tu mesa.
          </p>
        </div>

        <!-- 2. INFORMACIÓN Y HORARIOS -->
        <div class="footer-col footer-info">
          <h4 class="section-subtitle">Atención & Contacto</h4>
          <ul class="info-list">
            <li class="info-item">
              <Phone :size="15" class="info-icon" />
              <span>+56 9 3932 0226</span>
            </li>
            
            <li class="info-item">
              <Clock :size="15" class="info-icon" />
              <div class="schedule-lines">
                <span>Dom a Jue: 19:00 - 01:00 hrs</span>
                <span>Vie y Sáb: 19:00 - 02:00 hrs</span>
              </div>
            </li>
            
            <li class="info-item">
              <MapPin :size="15" class="info-icon" />
              <a 
                href="https://www.google.com/maps/search/?api=1&query=Pje.+Oscar+Castro+11012,+La+Pintana" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="location-link"
                title="Abrir dirección en Google Maps"
              >
                Pje. Oscar Castro 11012, La Pintana
              </a>
            </li>
          </ul>
        </div>

        <!-- 3. REDES SOCIALES -->
        <div class="footer-col footer-social">
          <h4 class="section-subtitle">Síguenos</h4>
          <p class="social-hint">Conoce nuestras promociones y combos diarios:</p>
          <div class="social-links">
            <a 
              href="https://www.instagram.com/j.junior_foodtruck/?hl=es" 
              target="_blank" 
              rel="noopener noreferrer" 
              class="social-button"
              aria-label="Instagram de J.Junior"
            >
              <Instagram :size="18" />
              <span>@j.junior_foodtruck</span>
            </a>
          </div>
        </div>
      </div>

      <!-- COPYRIGHT INFERIOR -->
      <div class="footer-bottom">
        <p>© 2026 J.Junior Foodtruck. Todos los derechos reservados.</p>
      </div>
    </div>
  </footer>
</template>

<script setup lang="ts">
import { Phone, Clock, MapPin, Instagram } from 'lucide-vue-next'
</script>

<style scoped>
*, *::before, *::after {
  box-sizing: border-box;
}

.footer-container {
  background-color: var(--DC-brown, #513119);
  background-image: linear-gradient(180deg, #513119 0%, #3e2411 100%);
  color: #f1f3f5;
  font-family: inherit;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  margin-top: auto;
  width: 100%;
  padding: 3rem 1.5rem 1.75rem;
}

.footer-inner {
  max-width: 1200px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 2.25rem;
}

.footer-grid {
  display: grid;
  grid-template-columns: 1.2fr 1fr 1fr;
  gap: 2.5rem;
  align-items: start;
}

.footer-col {
  display: flex;
  flex-direction: column;
  gap: 0.85rem;
}

/* MARCA Y LOGO */
.brand-title {
  display: flex;
  align-items: center;
  gap: 10px;
  cursor: default;
  user-select: none;
}

.footer-logo {
  height: 42px;
  width: auto;
  object-fit: contain;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
}

.brand-text-styled {
  font-size: clamp(1.4rem, 2.5vw, 1.8rem);
  font-weight: 900;
  font-style: italic;
  font-family: 'Arial Black', Impact, sans-serif;
  color: #ffffff;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0;
  white-space: nowrap;
  text-shadow: 0 2px 6px rgba(0, 0, 0, 0.45);
}

.brand-tagline {
  font-size: 0.88rem;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.78);
  margin: 0;
  max-width: 320px;
}

/* SUBTÍTULOS DE SECCIÓN */
.section-subtitle {
  font-size: 0.82rem;
  font-weight: 800;
  color: var(--DC-orange, #e28743);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  margin: 0;
}

/* LISTA DE INFORMACIÓN */
.info-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  font-size: 0.88rem;
  color: rgba(255, 255, 255, 0.88);
  line-height: 1.4;
}

.info-icon {
  color: var(--DC-orange, #e28743);
  flex-shrink: 0;
  margin-top: 2px;
}

.schedule-lines {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.location-link {
  color: rgba(255, 255, 255, 0.88);
  text-decoration: none;
  transition: color 0.15s ease;
}

.location-link:hover {
  color: var(--DC-orange, #e28743);
  text-decoration: underline;
}

/* REDES SOCIALES */
.social-hint {
  font-size: 0.84rem;
  color: rgba(255, 255, 255, 0.75);
  margin: 0;
  line-height: 1.4;
}

.social-links {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.social-button {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #ffffff;
  text-decoration: none;
  padding: 0.55rem 0.95rem;
  border-radius: 12px;
  font-size: 0.84rem;
  font-weight: 700;
  width: fit-content;
  transition: all 0.2s ease;
}

.social-button:hover {
  background: var(--DC-orange, #e28743);
  border-color: var(--DC-orange, #e28743);
  transform: translateY(-1px);
}

/* COPYRIGHT INFERIOR */
.footer-bottom {
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 1.25rem;
  text-align: center;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

.footer-bottom p {
  margin: 0;
}

/* RESPONSIVO */
@media (max-width: 860px) {
  .footer-grid {
    grid-template-columns: 1fr;
    gap: 2rem;
    text-align: center;
  }

  .footer-col {
    align-items: center;
  }

  .brand-title {
    justify-content: center;
  }

  .brand-tagline {
    max-width: 420px;
  }

  .info-item {
    justify-content: center;
  }

  .social-button {
    margin: 0 auto;
  }
}

@media (max-width: 480px) {
  .footer-container {
    padding: 2.25rem 1rem 1.5rem;
  }

  .footer-logo {
    height: 36px;
  }

  .brand-text-styled {
    font-size: 1.35rem;
  }
}
</style>