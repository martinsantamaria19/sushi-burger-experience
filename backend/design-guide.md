Documentación Completa de Diseño - Cartify Web
📋 Índice
Visión General del Diseño
Sistema de Colores
Tipografía
Espaciado y Layout
Componentes UI
Secciones de la Página
Animaciones y Efectos
Responsive Design
Glassmorphism y Efectos Visuales
🎨 Visión General del Diseño
Estilo Visual: Dark Future / Premium Dark Mode
Estética: Moderna, minimalista, con efectos glassmorphism y gradientes vibrantes
Objetivo: Crear una experiencia visual premium que impresione al usuario desde el primer momento

Características Principales del Diseño:
Fondo negro profundo (#050505) para máximo contraste
Gradientes violeta-rosa como colores de marca
Efectos de glassmorphism (vidrio esmerilado) en tarjetas y header
Animaciones sutiles y micro-interacciones
Diseño completamente responsive (mobile-first)
Efectos de glow (resplandor) en elementos interactivos
🎨 Sistema de Colores
Paleta de Colores Base
/* Fondos */
--color-bg: #050505;              /* Negro profundo - Fondo principal */
--color-surface: #101010;         /* Negro menos intenso - Superficies/tarjetas */
--color-surface-hover: #1a1a1a;   /* Estado hover de superficies */
/* Textos */
--color-text: #ffffff;            /* Blanco puro - Texto principal */
--color-text-muted: #a3a3a3;      /* Gris claro - Texto secundario */
/* Colores de Marca */
--color-primary: #7c3aed;         /* Violeta 600 - Color primario */
--color-primary-light: #a78bfa;   /* Violeta claro - Variante clara */
--color-accent: #db2777;          /* Rosa 600 - Color de acento */
/* Bordes y Glass */
--color-border: rgba(255, 255, 255, 0.1);     /* Bordes sutiles */
--glass-bg: rgba(10, 10, 10, 0.7);            /* Fondo glassmorphism */
--glass-border: rgba(255, 255, 255, 0.08);    /* Borde glassmorphism */
Gradientes
/* Gradiente de Marca Principal */
--gradient-brand: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
/* Uso: Botones primarios, textos destacados, elementos de marca */
/* Gradiente de Resplandor */
--gradient-glow: radial-gradient(circle, rgba(124, 58, 237, 0.15) 0%, transparent 70%);
/* Uso: Efectos de fondo, halos de luz */
Aplicación de Colores por Contexto
Fondos:

Página principal: #050505
Tarjetas/Cards: #101010
Footer: #030303 (aún más oscuro)
Inputs: rgba(0, 0, 0, 0.2)
Textos:

Títulos principales (h1, h2): #ffffff
Párrafos y descripciones: #a3a3a3
Enlaces hover: #ffffff
Elementos Interactivos:

Botón primario: Gradiente violeta-rosa
Botón secundario: rgba(255, 255, 255, 0.05) con borde
Hover: Incrementar brillo y elevar (translateY)
📝 Tipografía
Fuentes
/* Fuentes de Google Fonts */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@500;600;700;800&display=swap');
--font-heading: 'Outfit', sans-serif;  /* Títulos */
--font-body: 'Inter', sans-serif;      /* Cuerpo de texto */
Jerarquía Tipográfica
Títulos (Headings)
h1, h2, h3, h4, h5, h6 {
  font-family: 'Outfit', sans-serif;
  margin: 0 0 1rem;
  line-height: 1.1;
  color: #ffffff;
}
/* H1 - Hero Principal */
h1 {
  font-size: 3rem;           /* Mobile */
  font-size: 4rem;           /* Desktop (min-width: 768px) */
  font-weight: 800;
  letter-spacing: -0.03em;   /* Tracking ajustado */
  line-height: 1.1;
}
/* H2 - Títulos de Sección */
h2 {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 1rem;
}
/* H3 - Subtítulos de Cards */
h3 {
  font-size: 1.25rem;
  font-weight: 600;
  margin-bottom: 0.75rem;
}
Cuerpo de Texto
body {
  font-family: 'Inter', sans-serif;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;  /* Suavizado de fuente */
}
p {
  color: #a3a3a3;
  margin-bottom: 1.5rem;
  font-size: 1rem;          /* Base */
  font-size: 1.125rem;      /* Hero y secciones importantes */
}
Elementos Especiales
/* Logo */
.logo {
  font-size: 1.5rem;        /* Header */
  font-size: 2rem;          /* Footer */
  font-weight: 700;
  font-family: 'Outfit', sans-serif;
  letter-spacing: -0.02em;
}
/* Badges */
.badge {
  font-size: 0.875rem;
  font-weight: 500;
}
/* Botones */
.btn {
  font-weight: 600;
  font-size: 0.95rem;
}
📐 Espaciado y Layout
Sistema de Espaciado
:root {
  --spacing-container: 1200px;  /* Ancho máximo del contenedor */
  --spacing-section: 6rem;      /* Padding vertical de secciones */
}
/* Contenedor Principal */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 1.5rem;
}
Grid System
Hero Section:

.hero-content {
  display: grid;
  grid-template-columns: 1fr;           /* Mobile */
  grid-template-columns: 1fr 1fr;       /* Desktop (min-width: 992px) */
  gap: 4rem;
  align-items: center;
}
Features Grid:

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}
Contact Section:

.contact-wrapper {
  display: grid;
  grid-template-columns: 1fr;           /* Mobile */
  grid-template-columns: 1fr 1fr;       /* Desktop (min-width: 992px) */
  gap: 4rem;
  align-items: center;
}
Padding y Margins
Secciones:

section {
  padding: 6rem 0;  /* var(--spacing-section) */
}
.hero {
  padding-top: 10px;
  padding-bottom: 10px;
  min-height: 100vh;
}
Cards:

.feature-card {
  padding: 2rem;
}
.pricing-card {
  padding: 3rem;
}
.contact-form-card {
  padding: 2.5rem;
}
🧩 Componentes UI
1. Botones
Botón Primario
.btn-primary {
  background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;  /* Completamente redondeado */
  font-weight: 600;
  font-size: 0.95rem;
  border: 1px solid transparent;
  box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
  transition: all 0.2s ease;
  cursor: pointer;
}
.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(124, 58, 237, 0.5);
}
Botón Secundario
.btn-secondary {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: white;
  padding: 0.75rem 1.5rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}
.btn-secondary:hover {
  background: rgba(255, 255, 255, 0.1);
  border-color: white;
}
Variantes de Tamaño
.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}
.btn-block {
  width: 100%;
  justify-content: center;
  padding: 1rem;
  font-size: 1.125rem;
}
2. Cards
Card Base
.card {
  background: #101010;
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 16px;
  overflow: hidden;
}
Feature Card (con efecto hover glow)
.feature-card {
  background: #101010;
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 2rem;
  border-radius: 20px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}
/* Efecto de resplandor en hover */
.feature-card::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: radial-gradient(
    800px circle at var(--mouse-x, 0) var(--mouse-y, 0),
    rgba(255, 255, 255, 0.06),
    transparent 40%
  );
  z-index: 1;
  opacity: 0;
  transition: opacity 0.5s;
  pointer-events: none;
}
.feature-card:hover::before {
  opacity: 1;
}
.feature-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255, 255, 255, 0.2);
  box-shadow: 
    0 20px 40px -20px rgba(0, 0, 0, 0.7),
    0 0 20px -5px rgba(124, 58, 237, 0.2);
}
Pricing Card
.pricing-card {
  background: radial-gradient(circle at top, #1e1e1e, #101010);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 24px;
  padding: 3rem;
  max-width: 480px;
  position: relative;
  box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.5);
  overflow: hidden;
}
/* Efecto spotlight */
.spotlight {
  position: absolute;
  top: -100px;
  left: 50%;
  transform: translateX(-50%);
  width: 200px;
  height: 200px;
  background: #7c3aed;
  filter: blur(80px);
  opacity: 0.3;
  pointer-events: none;
}
3. Badges
.badge {
  display: inline-block;
  padding: 0.5rem 1rem;
  background: rgba(124, 58, 237, 0.1);
  border: 1px solid rgba(124, 58, 237, 0.2);
  border-radius: 9999px;
  font-size: 0.875rem;
  color: #a78bfa;
  font-weight: 500;
  backdrop-filter: blur(8px);
}
.popular-badge {
  position: absolute;
  top: 20px;
  right: 20px;
  background: #7c3aed;
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 999px;
  text-transform: uppercase;
}
4. Icon Box
.icon-box {
  width: 56px;
  height: 56px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #a78bfa;
  transition: all 0.3s ease;
}
.feature-card:hover .icon-box {
  background: #7c3aed;
  color: white;
  border-color: #7c3aed;
  transform: scale(1.1) rotate(3deg);
}
5. Formularios
Inputs y Textareas
input, textarea {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.875rem 1rem;
  border-radius: 12px;
  color: white;
  font-family: 'Inter', sans-serif;
  font-size: 1rem;
  transition: all 0.2s ease;
}
input:focus, textarea:focus {
  outline: none;
  border-color: #7c3aed;
  background: rgba(124, 58, 237, 0.05);
  box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
}
Labels
label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #a3a3a3;
}
6. Texto con Gradiente
.t-gradient {
  background: linear-gradient(135deg, #7c3aed 0%, #db2777 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  display: inline-block;
}
📄 Secciones de la Página
1. Header (Navegación Fija)
Estructura:

Logo a la izquierda
Navegación central (desktop)
Botones de acción a la derecha
Estilos:

.header {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 100;
  background: rgba(10, 10, 10, 0.7);
  backdrop-filter: blur(16px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  height: 70px;
}
Efecto de Navegación:

.nav-desktop a {
  font-size: 0.95rem;
  font-weight: 500;
  color: #a3a3a3;
  position: relative;
  padding-bottom: 4px;
  transition: color 0.3s ease;
}
/* Underline animado */
.nav-desktop a::after {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 1px;
  background: linear-gradient(90deg, #7c3aed, #db2777);
  transform: scaleX(0);
  transform-origin: right;
  transition: transform 0.3s ease;
  border-radius: 2px;
}
.nav-desktop a:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}
Responsive:

Desktop (≥768px): Navegación visible
Mobile (<768px): Solo logo y botón "Empezar"
2. Hero Section
Layout:

Dos columnas en desktop (texto + mockup)
Una columna en mobile (centrado)
Altura mínima: 100vh
Elementos:

Fondos Animados:

.hero-bg-glow {
  position: absolute;
  width: 600px;
  height: 600px;
  background: radial-gradient(
    circle,
    rgba(124, 58, 237, 0.4) 0%,
    rgba(0, 0, 0, 0) 70%
  );
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: -1;
  animation: pulseAndMove 15s infinite alternate ease-in-out;
}
@keyframes pulseAndMove {
  0% {
    transform: translate(-50%, -50%) scale(1);
    opacity: 0.15;
  }
  33% {
    transform: translate(-30%, -60%) scale(1.1);
    opacity: 0.25;
  }
  66% {
    transform: translate(-70%, -40%) scale(0.9);
    opacity: 0.2;
  }
  100% {
    transform: translate(-50%, -50%) scale(1.2);
    opacity: 0.3;
  }
}
Mockup de Teléfono 3D:

.phone-mockup {
  position: relative;
  width: 300px;
  height: 600px;
  transform: rotateY(-10deg) rotateX(5deg);
  transform-style: preserve-3d;
  transition: transform 0.5s ease;
}
.hero-visual:hover .phone-mockup {
  transform: rotateY(0deg) rotateX(0deg);
}
.phone-frame {
  width: 100%;
  height: 100%;
  background: #111;
  border-radius: 40px;
  border: 8px solid #222;
  padding: 0.75rem;
  box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
}
Tarjetas Flotantes:

.floating-card {
  position: absolute;
  background: rgba(30, 30, 30, 0.9);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 0.75rem 1.25rem;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.875rem;
  box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
  transform: translateZ(50px);
}
.card-1 {
  top: 15%;
  right: -30px;
  border-left: 3px solid #22c55e;  /* Verde */
}
.card-2 {
  bottom: 20%;
  left: -30px;
  border-left: 3px solid #7c3aed;  /* Violeta */
}
3. Features Section
Grid Responsivo:

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}
Interacción con Mouse (Glow Effect):

// JavaScript para tracking del mouse
document.querySelectorAll(".feature-card").forEach((card) => {
  card.addEventListener("mousemove", (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    card.style.setProperty("--mouse-x", `${x}px`);
    card.style.setProperty("--mouse-y", `${y}px`);
  });
});
4. Pricing Section
Diseño Centrado:

Una sola tarjeta de pricing centrada
Máximo ancho: 480px
Badge "30% OFF" en esquina superior derecha
Lista de Features:

.features-list {
  list-style: none;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.features-list li {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  color: #ffffff;
}
.check-icon {
  color: #db2777;  /* Rosa de acento */
}
Precio:

.price {
  display: flex;
  align-items: baseline;
  justify-content: center;
  margin: 1rem 0;
  color: white;
}
.amount {
  font-size: 4rem;
  font-weight: 800;
  line-height: 1;
}
5. Contact Section
Layout de Dos Columnas:

Izquierda: Información de contacto
Derecha: Formulario
Formulario con Glassmorphism:

.contact-form-card {
  background: rgba(22, 22, 22, 0.6);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  padding: 2.5rem;
  border-radius: 24px;
  box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
}
Estados del Formulario:

.form-message.success {
  background: rgba(34, 197, 94, 0.1);
  color: #22c55e;
  border: 1px solid rgba(34, 197, 94, 0.2);
}
.form-message.error {
  background: rgba(239, 68, 68, 0.1);
  color: #ef4444;
  border: 1px solid rgba(239, 68, 68, 0.2);
}
6. Footer
Diseño Centrado:

Todo el contenido centrado
Fondo más oscuro (#030303)
Borde superior con gradiente animado
Borde Animado:

.footer-border-glow {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 1px;
  background: linear-gradient(
    90deg,
    transparent 0%,
    transparent 40%,
    rgba(124, 58, 237, 0.8) 50%,
    transparent 60%,
    transparent 100%
  );
  background-size: 200% 100%;
  animation: borderGlowMove 4s linear infinite;
}
@keyframes borderGlowMove {
  0% { background-position: 100% 0; }
  100% { background-position: -100% 0; }
}
✨ Animaciones y Efectos
1. Animación de Entrada (Fade In Up)
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-in {
  animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
  opacity: 0;
}
/* Delays escalonados */
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
2. Efectos Hover
Botones:

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 0 30px rgba(124, 58, 237, 0.5);
}
Cards:

.feature-card:hover {
  transform: translateY(-5px);
  border-color: rgba(255, 255, 255, 0.2);
}
Enlaces de Navegación:

.nav-desktop a:hover::after {
  transform: scaleX(1);
  transform-origin: left;
}
3. Transiciones
Estándar:

transition: all 0.2s ease;
Suaves:

transition: all 0.3s ease;
Opacidad:

transition: opacity 0.5s;
📱 Responsive Design
Breakpoints
/* Mobile First Approach */
/* Small devices (landscape phones, 480px and up) */
@media (min-width: 480px) {
  .cta-group {
    flex-direction: row;
  }
}
/* Medium devices (tablets, 768px and up) */
@media (min-width: 768px) {
  .nav-desktop {
    display: flex;
  }
  
  h1 {
    font-size: 4rem;
  }
}
/* Large devices (desktops, 992px and up) */
@media (min-width: 992px) {
  .hero-content {
    grid-template-columns: 1fr 1fr;
    text-align: left;
  }
  
  .contact-wrapper {
    grid-template-columns: 1fr 1fr;
  }
}
Comportamiento Mobile
Header:

Ocultar navegación central
Ocultar botón "Ingresar"
Mantener solo logo y botón "Empezar"
Hero:

Layout de una columna
Texto centrado
Mockup centrado debajo del texto
Features:

Grid automático con mínimo 300px por columna
Se adapta automáticamente al ancho disponible
Pricing:

Tarjeta ocupa todo el ancho disponible
Máximo 480px
Contact:

Formulario debajo de la información
Layout de una columna
🔮 Glassmorphism y Efectos Visuales
Glassmorphism (Vidrio Esmerilado)
Receta Base:

background: rgba(10, 10, 10, 0.7);
backdrop-filter: blur(16px);
border: 1px solid rgba(255, 255, 255, 0.08);
Aplicaciones:

Header fijo
Tarjetas flotantes
Formulario de contacto
Badges
Efectos de Resplandor (Glow)
Glow en Botones:

box-shadow: 0 0 20px rgba(124, 58, 237, 0.3);
/* Hover */
box-shadow: 0 0 30px rgba(124, 58, 237, 0.5);
Glow en Cards:

box-shadow: 
  0 20px 40px -20px rgba(0, 0, 0, 0.7),
  0 0 20px -5px rgba(124, 58, 237, 0.2);
Glow de Fondo:

background: radial-gradient(
  circle,
  rgba(124, 58, 237, 0.4) 0%,
  rgba(0, 0, 0, 0) 70%
);
Sombras
Elevación Baja:

box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
Elevación Media:

box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.5);
Elevación Alta:

box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.8);
🎯 Detalles Específicos por Componente
Logo
.logo {
  font-family: 'Outfit', sans-serif;
  font-weight: 700;
  letter-spacing: -0.02em;
}
.dot {
  color: #7c3aed;  /* Punto violeta */
}
Ejemplo: Cartify.

Mockup de Teléfono
Estructura:

.phone-mockup - Contenedor con transformación 3D
.phone-frame - Marco del teléfono (#111 con borde #222)
.phone-screen - Pantalla interna (#000)
Elementos de UI simulados:
.app-header - Barra superior
.app-hero - Banner principal
.app-grid - Grid de tarjetas 2x2
.app-fab - Botón flotante violeta
Colores del Mockup:

Frame: #111
Border: #222
Screen: #000
UI Elements: #1a1a1a, #333
FAB: #7c3aed
Dividers
.divider {
  height: 1px;
  background: rgba(255, 255, 255, 0.1);
  margin: 2rem 0;
}
🌐 SEO y Accesibilidad
Meta Tags
<meta charset="UTF-8" />
<meta name="description" content="Digitaliza tu menú con Cartify..." />
<meta name="viewport" content="width=device-width" />
Estructura Semántica
<header> para navegación
<main> para contenido principal
<section> para cada sección
<footer> para pie de página
Uso correcto de h1, h2, h3
Accesibilidad
aria-label en iconos sociales
Labels asociados a inputs
Contraste adecuado (WCAG AA)
Focus states visibles
📦 Assets y Recursos
Iconos
Fuente: Lucide Icons (SVG inline)
Stroke width: 2
Tamaño estándar: 20px-24px
Favicon
Ubicación: 
/public/favicon.svg
Fuentes
Google Fonts (preconnect para performance)
Inter: 400, 500, 600
Outfit: 500, 600, 700, 800
🔧 Configuración Técnica
Scroll Behavior
html {
  scroll-behavior: smooth;
}
Font Smoothing
html {
  -webkit-font-smoothing: antialiased;
}
Overflow
body {
  overflow-x: hidden;  /* Prevenir scroll horizontal */
}
📝 Notas de Implementación
Orden de Carga
Reset/normalize implícito
Variables CSS (:root)
Estilos base (html, body)
Tipografía
Utilidades
Componentes
Animaciones
Performance
Usar will-change con precaución
Limitar backdrop-filter a elementos necesarios
Optimizar animaciones (usar transform y opacity)
Lazy load de imágenes si se agregan
Browser Support
Glassmorphism requiere soporte de backdrop-filter
Gradientes en texto requieren -webkit-background-clip
Transformaciones 3D requieren transform-style: preserve-3d
🎨 Paleta de Colores Completa (Referencia Rápida)
Nombre	Hex/RGBA	Uso
Deep Black	#050505	Fondo principal
Surface Black	#101010	Tarjetas
Surface Hover	#1a1a1a	Hover de superficies
Pure White	#ffffff	Texto principal
Muted Gray	#a3a3a3	Texto secundario
Primary Violet	#7c3aed	Color de marca
Light Violet	#a78bfa	Variante clara
Accent Pink	#db2777	Acento
Border	rgba(255,255,255,0.1)	Bordes
Glass BG	rgba(10,10,10,0.7)	Glassmorphism
Success Green	#22c55e	Mensajes exitosos
Error Red	#ef4444	Mensajes de error
🚀 Checklist de Implementación
Al recrear este diseño, asegúrate de:

 Importar fuentes Google (Inter + Outfit)
 Definir todas las variables CSS en :root
 Implementar glassmorphism con backdrop-filter
 Agregar animaciones fadeInUp con delays
 Configurar grid responsivo en Hero, Features y Contact
 Implementar efecto glow en hover de feature cards
 Agregar tracking de mouse para efecto de luz
 Configurar transformación 3D del mockup
 Implementar gradientes de fondo animados
 Agregar borde animado en footer
 Configurar estados de formulario (success/error)
 Implementar navegación con underline animado
 Asegurar responsive en todos los breakpoints
 Optimizar para dark mode (ya está en dark)
 Validar contraste de colores (accesibilidad)
Versión: 1.0
Fecha: 2026
Proyecto: Cartify - Landing Page
Stack: Astro + Vanilla CSS