# Comandos para Crear Modelos - Sistema de Suscripciones

✅ **MIGRACIONES YA CREADAS** - Los archivos de migración ya están creados con el contenido completo.

Ahora necesitas crear los modelos y ejecutar las migraciones.

## 📋 Modelos a Crear

Ejecuta estos comandos dentro del contenedor usando `make php` y luego los comandos artisan:

---

### 1. Modelo SubscriptionPlan
```bash
php artisan make:model SubscriptionPlan
```

### 2. Modelo Subscription
```bash
php artisan make:model Subscription
```

### 3. Modelo SubscriptionPayment
```bash
php artisan make:model SubscriptionPayment
```

### 4. Modelo WebhookLog
```bash
php artisan make:model WebhookLog
```

---

## 🌱 Seeder a Crear

### 1. Seeder de Planes de Suscripción
```bash
php artisan make:seeder SubscriptionPlanSeeder
```

---

## 🌱 Seeder a Crear

### Seeder de Planes de Suscripción
```bash
php artisan make:seeder SubscriptionPlanSeeder
```

---

## 📦 Ejecutar Todo (orden recomendado)

```bash
# 1. Crear modelos (las migraciones ya están creadas)
php artisan make:model SubscriptionPlan
php artisan make:model Subscription
php artisan make:model SubscriptionPayment
php artisan make:model WebhookLog

# 2. Crear seeder
php artisan make:seeder SubscriptionPlanSeeder

# 3. Ejecutar migraciones (después de que editemos los modelos)
php artisan migrate

# 4. Ejecutar seeder de planes (después de crear el contenido del seeder)
php artisan db:seed --class=SubscriptionPlanSeeder
```

---

## ✅ Archivos Creados (COMPLETADOS)

### Migraciones (✅ Ya creadas con contenido completo)
- ✅ `2026_01_11_100000_create_subscription_plans_table.php`
- ✅ `2026_01_11_100001_create_subscriptions_table.php`
- ✅ `2026_01_11_100002_create_subscription_payments_table.php`
- ✅ `2026_01_11_100003_create_webhook_logs_table.php`
- ✅ `2026_01_11_100004_add_subscription_fields_to_companies_table.php`
- ✅ `2026_01_11_100005_assign_free_plan_to_existing_companies.php` (migración de datos)

**Ubicación:** `backend/database/migrations/`

### Modelos (✅ Ya creados con contenido completo)
- ✅ `SubscriptionPlan.php`
- ✅ `Subscription.php`
- ✅ `SubscriptionPayment.php`
- ✅ `WebhookLog.php`

**Ubicación:** `backend/app/Models/`

### Trait (✅ Ya creado)
- ✅ `HasSubscriptionLimits.php`

**Ubicación:** `backend/app/Traits/`

### Seeder (✅ Ya creado)
- ✅ `SubscriptionPlanSeeder.php`
- ✅ `DatabaseSeeder.php` (actualizado para incluir SubscriptionPlanSeeder)

**Ubicación:** `backend/database/seeders/`

### Modelo Company (✅ Ya actualizado)
- ✅ Relaciones agregadas
- ✅ Trait HasSubscriptionLimits agregado

---

## 🚀 Comandos para Ejecutar Ahora

### 1. Ejecutar Migraciones

Dentro del contenedor (`make php`):

```bash
php artisan migrate
```

Esto ejecutará todas las migraciones en orden:
1. Creará las tablas de suscripciones
2. Modificará la tabla companies
3. Asignará plan FREE a companies existentes

### 2. Ejecutar Seeder de Planes

```bash
php artisan db:seed --class=SubscriptionPlanSeeder
```

O ejecutar todos los seeders:

```bash
php artisan db:seed
```

---

## ✅ Estado Actual - FASE 1 COMPLETADA

- ✅ Migraciones creadas
- ✅ Modelos creados con relaciones y métodos
- ✅ Trait HasSubscriptionLimits creado
- ✅ Modelo Company actualizado
- ✅ Seeder de planes creado
- ✅ Migración para asignar plan FREE a usuarios existentes

### Próximas Fases

**Fase 2:** Integración con MercadoPago
- Instalar SDK de MercadoPago
- Crear servicio MercadoPagoService
- Configurar variables de entorno
- Crear controladores de suscripción y webhooks

**Fase 3:** Middleware y Validaciones
- Crear middleware CheckSubscriptionLimits
- Crear middleware RequiresPremium
- Aplicar middleware en rutas correspondientes

**Fase 4:** Sistema de Limitaciones
- Implementar validaciones en controladores
- Agregar mensajes de error con requires_upgrade

**Fase 5:** UI/UX para usuarios Free
- Crear modal de upgrade
- Crear badges y banners
- Crear página de pricing

---

## 📝 Notas Importantes

1. **Migración de Usuarios Existentes:** La migración `2026_01_11_100005_assign_free_plan_to_existing_companies.php` automáticamente asignará el plan FREE a todas las companies existentes cuando ejecutes `php artisan migrate`.

2. **Orden de Ejecución:** Asegúrate de ejecutar las migraciones en orden. Laravel lo hace automáticamente, pero si hay algún problema, puedes ejecutar cada migración individualmente.

3. **Seeder de Planes:** El seeder creará los planes FREE y PREMIUM. El precio del plan PREMIUM está configurado en 29.99 (puedes ajustarlo en el seeder antes de ejecutarlo).

