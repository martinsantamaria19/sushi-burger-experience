# BoxCenter Uruguay - Sitio web

Sitio institucional para BoxCenter (depósitos y boxes de almacenamiento) con panel de administración para editar contenidos.

## Stack

- **Laravel 12** (PHP 8.4) + **Blade** + **Tailwind (Vite)**
- Estética alineada con [boxcenter.com.uy/boxs](https://www.boxcenter.com.uy/boxs)

## Cómo correr el proyecto

1. **Levantar servicios** (requiere red Docker `mysql_net` y base de datos configurada en `.env`):
   ```bash
   make up
   ```

2. **Migraciones y datos iniciales** (primera vez):
   ```bash
   make migrate
   make seed
   make storage-link
   ```

   O todo junto: `make setup`

3. **Estilos**: Si no vas a usar `make dev`, compilá los assets una vez:
   ```bash
   make build-assets
   ```
   Así se genera `public/build/` con el CSS. Si preferís recargar estilos en caliente, usá `make dev` (Vite en modo desarrollo).

4. **Acceso**
   - Sitio público: http://localhost:8080
   - Admin: http://localhost:8080/admin/login  
   - Usuario por defecto: `admin@boxcenter.com.uy` / `password` (cambiar en producción)

## Contenidos editables (admin)

En **Contenidos del sitio** el cliente puede modificar:

- **hero**: título, subtítulo y descripción de la portada
- **features**: textos de “Acceso 24/7”, “Vigilancia 24hs”, “Doble cerradura”
- **quote**: título y subtítulo del bloque de cotización
- **solutions**: textos e imágenes de Particulares, Empresas, Oficinas y showrooms
- **installations**: textos de instalaciones (boxes, perímetro, showroom, cámaras)
- **location**: estadísticas y tiempos (Centro, Pocitos, Aeropuerto)
- **contact**: título, email, teléfono, dirección
- **meta**: nombre del sitio y descripción SEO

Las imágenes se suben desde el admin y se guardan en `storage/app/public/site/` (enlace público en `public/storage`).

## Formularios públicos

- **Contacto** (`/contacto`): guarda en `contact_submissions` y muestra mensaje de éxito.
- **Cotización** (`/cotizar`): guarda en `quote_submissions` y muestra mensaje de éxito.

Ambos listados se ven en el panel admin (Mensajes de contacto y Cotizaciones).

## Comandos útiles

| Comando | Descripción |
|--------|-------------|
| `make up` | Levantar contenedores |
| `make down` | Bajar contenedores |
| `make migrate` | Ejecutar migraciones |
| `make seed` | Ejecutar seeders |
| `make storage-link` | Crear enlace `public/storage` |
| `make fix-permissions` | Ajustar permisos de `storage` y `bootstrap/cache` |
| `make php` | Entrar al shell del contenedor PHP |
