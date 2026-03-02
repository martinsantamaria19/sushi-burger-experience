# Box Center

Proyecto Laravel con Docker (PHP 8.4-FPM, Nginx, Vite).

## Requisitos

- Docker y Docker Compose
- Red Docker `mysql_net` creada (para conectar con MySQL si lo usas):  
  `docker network create mysql_net`

## Inicio rápido

```bash
# Construir y levantar servicios
make build
make up

# Generar APP_KEY si es la primera vez (ya viene generada en backend)
# Configurar .env en backend/ (DB, etc.) si usas MySQL

# Permisos de storage (Laravel)
make fix-permissions

# Desarrollo con Vite en vivo
make dev
```

- **App:** http://localhost:8080  
- **Vite (assets):** se sirve vía Nginx; el HMR usa el puerto 5173.

## Comandos útiles

| Comando | Descripción |
|---------|-------------|
| `make up` | Levanta los contenedores |
| `make down` | Para los contenedores |
| `make dev` | Levanta y ejecuta `npm run dev` (Vite) |
| `make php` | Entra al shell del contenedor PHP |
| `make migrate` | Ejecuta migraciones Laravel |
| `make fix-permissions` | Arregla permisos de `storage` y `bootstrap/cache` |

## Estructura

- `backend/` — Aplicación Laravel (document root: `backend/public`)
- `docker/` — Configuración de Nginx
- El servicio `app` monta `./backend` en `/var/www`; Nginx usa `/var/www/public` como root.
