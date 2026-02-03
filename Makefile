# Makefile para el proyecto Sushi Burger Experience

# Definición de variables
DOCKER_COMPOSE = docker compose
PHP_SERVICE = app
PHP_CONTAINER = sushiburgerexperience_app

.PHONY: php up down restart migrate fresh seed build dev fix-permissions

# Comando para entrar a la terminal del contenedor PHP
php:
	@docker exec -it $(PHP_CONTAINER) sh

# Levantar entorno completo de desarrollo
dev:
	@$(DOCKER_COMPOSE) up -d
	@echo ""
	@echo "✅ Servicios levantados:"
	@echo "   📱 Laravel app: http://localhost:8080"
	@echo "   ⚡ Vite dev server: http://localhost:5173 (para assets)"
	@echo ""
	@echo "🚀 Levantando Vite para cambios en tiempo real..."
	@echo "   (Accede a http://localhost:8080 para ver la aplicación)"
	@docker exec -it $(PHP_CONTAINER) npm run dev

# Atajos comunes para desarrollo
up:
	@$(DOCKER_COMPOSE) up -d
	@echo "🔧 Arreglando permisos..."
	@sleep 2
	@docker exec $(PHP_CONTAINER) sh -c "mkdir -p /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/logs /var/www/bootstrap/cache && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache" 2>/dev/null || echo "⚠️  Contenedor no está corriendo aún, ejecuta 'make fix-permissions' después"

down:
	@$(DOCKER_COMPOSE) down

restart:
	@$(DOCKER_COMPOSE) restart

build:
	@$(DOCKER_COMPOSE) build

# Comandos de Laravel (ejecutados dentro del contenedor)
migrate:
	@docker exec -it $(PHP_CONTAINER) php artisan migrate

fresh:
	@docker exec -it $(PHP_CONTAINER) php artisan migrate:fresh

seed:
	@docker exec -it $(PHP_CONTAINER) php artisan db:seed

# Arreglar permisos de storage y bootstrap/cache
fix-permissions:
	@echo "🔧 Arreglando permisos de storage y bootstrap/cache..."
	@docker exec $(PHP_CONTAINER) sh -c "mkdir -p /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/logs /var/www/bootstrap/cache && chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && chmod -R 775 /var/www/storage /var/www/bootstrap/cache"
	@echo "✅ Permisos arreglados correctamente"
