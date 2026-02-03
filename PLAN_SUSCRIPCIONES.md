# Plan de Implementación: Sistema de Suscripciones Cartify

## 📋 Resumen Ejecutivo

Implementación completa de un sistema de suscripciones con integración a MercadoPago para Cartify. El sistema contempla dos planes (Free y Premium) con limitaciones específicas y funcionalidades premium.

---

## 🎯 Objetivos

- Implementar sistema de suscripciones con MercadoPago
- Limitar funcionalidades según plan del usuario
- Generar ingresos recurrentes mediante suscripciones mensuales
- Mejorar experiencia de usuario con upgrade paths claros

---

## 📊 Planes y Límites

### Plan FREE

- **1 Restaurant** máximo
- **1 Usuario** máximo
- **2 Códigos QR** máximo
- ❌ Sin Personalización de marca
- ❌ Sin Analytics & Reports

### Plan PREMIUM

- **Restaurants** ilimitados
- **Usuarios** ilimitados
- **Códigos QR** ilimitados
- ✅ Personalización de marca completa
- ✅ Analytics & Reports completos

---

## 🏗️ Arquitectura del Sistema

### Estructura de Base de Datos

#### Tabla: `subscription_plans`
Almacena los planes disponibles en el sistema.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Nombre del plan (free, premium) |
| `slug` | string | Identificador único del plan |
| `price` | decimal(10,2) | Precio mensual (NULL para free) |
| `mp_subscription_id` | string | ID de suscripción en MercadoPago |
| `features` | json | Features disponibles en el plan |
| `limits` | json | Límites del plan (restaurants, users, qr_codes) |
| `is_active` | boolean | Si el plan está activo |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tabla: `subscriptions`
Almacena las suscripciones activas de cada company.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `company_id` | bigint | FK a companies |
| `plan_id` | bigint | FK a subscription_plans |
| `status` | enum | active, cancelled, expired, past_due |
| `mp_subscription_id` | string | ID en MercadoPago |
| `mp_preapproval_id` | string | ID de preapproval en MP |
| `current_period_start` | date | Inicio del período actual |
| `current_period_end` | date | Fin del período actual |
| `trial_ends_at` | timestamp | Fin del período de prueba (nullable) |
| `cancelled_at` | timestamp | Fecha de cancelación (nullable) |
| `ends_at` | timestamp | Fecha de finalización efectiva (nullable) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Tabla: `subscription_payments`
Historial de pagos de suscripciones.

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `subscription_id` | bigint | FK a subscriptions |
| `mp_payment_id` | string | ID del pago en MercadoPago |
| `amount` | decimal(10,2) | Monto del pago |
| `currency` | string(3) | Moneda del pago |
| `status` | enum | pending, approved, rejected, refunded |
| `payment_date` | date | Fecha del pago |
| `metadata` | json | Datos adicionales del pago |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

#### Modificaciones a `companies`

Agregar campos:
- `subscription_id` (nullable, FK a subscriptions)
- `plan_id` (default: free plan id, FK a subscription_plans)

---

## 🔄 Fase 1: Estructura de Base de Datos y Modelos

### 1.1. Migraciones

- ✅ `create_subscription_plans_table.php`
- ✅ `create_subscriptions_table.php`
- ✅ `create_subscription_payments_table.php`
- ✅ `add_subscription_fields_to_companies_table.php`

### 1.2. Modelos Eloquent

**SubscriptionPlan Model:**
- Relaciones: `hasMany(Subscription)`
- Métodos: `getFreePlan()`, `getPremiumPlan()`, `getLimits()`, `getFeatures()`

**Subscription Model:**
- Relaciones: `belongsTo(Company)`, `belongsTo(SubscriptionPlan)`, `hasMany(SubscriptionPayment)`
- Scopes: `active()`, `cancelled()`, `expired()`, `pastDue()`
- Métodos: `isActive()`, `isCancelled()`, `isExpired()`, `renew()`, `cancel()`, `reactivate()`

**SubscriptionPayment Model:**
- Relaciones: `belongsTo(Subscription)`
- Scopes: `approved()`, `pending()`, `rejected()`

### 1.3. Trait HasSubscriptionLimits (Company)

Métodos principales:
```php
- canCreateRestaurant(): bool
- canCreateUser(): bool
- canCreateQrCode(): bool
- getRestaurantLimit(): int|null
- getUserLimit(): int|null
- getQrCodeLimit(): int|null
- isOnFreePlan(): bool
- isOnPremiumPlan(): bool
- hasActiveSubscription(): bool
- hasFeature(string $feature): bool
```

### 1.4. Seeder de Planes

Crear seeder que inicialice:
- Plan FREE (precio: 0, límites definidos)
- Plan PREMIUM (precio configurable, límites ilimitados)

---

## 🔌 Fase 2: Integración con MercadoPago

### 2.1. Configuración

**Variables de entorno:**
```env
MP_ACCESS_TOKEN=tu_access_token
MP_PUBLIC_KEY=tu_public_key
MP_WEBHOOK_SECRET=tu_webhook_secret
MP_APP_ID=tu_app_id
MP_ENVIRONMENT=sandbox|production
```

**Instalación de SDK:**
```bash
composer require mercadopago/dx-php
```

### 2.2. MercadoPagoService

Servicio principal para interactuar con la API de MercadoPago.

**Métodos principales:**
- `createPreapproval(Company $company, SubscriptionPlan $plan)`: Crear suscripción recurrente
- `createPaymentPreference(Company $company, SubscriptionPlan $plan)`: Crear preferencia de pago inicial
- `getSubscription(string $mpSubscriptionId)`: Obtener estado de suscripción
- `cancelSubscription(string $mpPreapprovalId)`: Cancelar suscripción
- `processWebhook(array $data)`: Procesar webhook recibido
- `validateWebhookSignature(array $data, string $signature)`: Validar firma del webhook

### 2.3. Flujo de Suscripción

#### Paso 1: Usuario selecciona plan Premium
1. Usuario hace click en "Upgrade to Premium"
2. Frontend llama a `/api/subscriptions/create-intent`
3. Backend crea `Payment Preference` en MercadoPago
4. Retorna `init_point` para redirección

#### Paso 2: Procesamiento del pago inicial
1. Usuario completa pago en checkout de MP
2. MP redirige a `success_url` o envía webhook
3. Webhook `payment.approved` llega al backend
4. Backend crea registro en `subscriptions` con status `active`
5. Crea `preapproval` en MP para cobros recurrentes
6. Actualiza `company.plan_id` y `company.subscription_id`
7. Registra primer pago en `subscription_payments`

#### Paso 3: Cobros recurrentes mensuales
1. MercadoPago cobra automáticamente cada mes
2. Envía webhook `payment` con cada cobro
3. Backend procesa webhook:
   - Verifica que el pago sea para una suscripción activa
   - Registra en `subscription_payments`
   - Actualiza `current_period_end` de la suscripción
   - Envía email de confirmación

#### Paso 4: Manejo de fallos de pago
1. Si pago es rechazado: webhook `payment.rejected`
2. Backend cambia status a `past_due`
3. Usuario tiene 7 días de período de gracia
4. Si no se resuelve, cambiar a `expired` y degradar a FREE
5. Enviar emails de alerta durante período de gracia

### 2.4. Webhooks de MercadoPago

**Endpoint:** `/api/webhooks/mercadopago`

**Eventos a manejar:**
- `payment.approved`: Activar/renovar suscripción
- `payment.rejected`: Marcar como `past_due`
- `payment.cancelled`: Cancelar suscripción
- `subscription.preapproval.cancelled`: Cancelación manual
- `subscription.preapproval.expired`: Expiración

**Seguridad:**
- Validar firma del webhook con `x-signature` header
- Verificar `x-request-id` para evitar duplicados (idempotencia)
- Rate limiting en el endpoint
- Logging de todos los webhooks recibidos

---

## 🛡️ Fase 3: Middleware y Validaciones

### 3.1. Middleware CheckSubscriptionLimits

Middleware que verifica límites antes de crear recursos.

**Aplicación:**
- `RestaurantController@store`
- `UserController@store`
- `QrCodeController@store`

**Lógica:**
```php
1. Obtener company del usuario autenticado
2. Verificar límite correspondiente (restaurants/users/qr_codes)
3. Si límite alcanzado:
   - Retornar 403 con mensaje
   - Incluir flag `requires_upgrade: true`
4. Si no, continuar con la request
```

### 3.2. Middleware RequiresPremium

Middleware que protege rutas premium.

**Rutas protegidas:**
- `/admin/analytics`
- `/admin/personalize`

**Lógica:**
```php
1. Verificar si usuario tiene plan premium activo
2. Si no:
   - Guardar URL intentada en session
   - Redirigir a modal de upgrade
3. Si sí, permitir acceso
```

---

## 🚫 Fase 4: Sistema de Limitaciones

### 4.1. Límites de Planes

**Plan FREE:**
```json
{
  "restaurants": 1,
  "users": 1,
  "qr_codes": 2,
  "branding": false,
  "analytics": false
}
```

**Plan PREMIUM:**
```json
{
  "restaurants": null,  // ilimitado
  "users": null,        // ilimitado
  "qr_codes": null,     // ilimitado
  "branding": true,
  "analytics": true
}
```

### 4.2. Validaciones en Controladores

**Ejemplo RestaurantController@store:**
```php
if (!$request->user()->company->canCreateRestaurant()) {
    return response()->json([
        'error' => 'Límite alcanzado',
        'message' => 'Has alcanzado el límite de restaurantes en tu plan actual.',
        'requires_upgrade' => true,
        'current_limit' => $company->getRestaurantLimit(),
        'current_count' => $company->restaurants()->count()
    ], 403);
}
```

**Ejemplo QrCodeController@store:**
```php
$restaurant = Restaurant::findOrFail($request->restaurant_id);
if (!$restaurant->company->canCreateQrCode()) {
    return response()->json([
        'error' => 'Límite alcanzado',
        'message' => 'Has alcanzado el límite de códigos QR en tu plan actual.',
        'requires_upgrade' => true,
        'current_limit' => $restaurant->company->getQrCodeLimit(),
        'current_count' => $restaurant->company->getTotalQrCodesCount()
    ], 403);
}
```

### 4.3. Contadores Globales

Métodos en Company para contar recursos:
- `getRestaurantsCount()`: Total de restaurantes activos
- `getUsersCount()`: Total de usuarios activos
- `getTotalQrCodesCount()`: Total de códigos QR de todos los restaurantes

---

## 🎨 Fase 5: UI/UX para Usuarios Free

### 5.1. Modal de Upgrade

**Trigger automático:**
- Al intentar crear restaurante cuando ya tiene 1
- Al intentar crear usuario cuando ya tiene 1
- Al intentar crear QR cuando ya tiene 2
- Al acceder a `/admin/analytics` (si es free)
- Al acceder a `/admin/personalize` (si es free)
- Cada 3-5 días en el dashboard (dismissible)

**Contenido del modal:**
- Título: "Upgrade to Premium"
- Comparación de planes (tabla comparativa)
- Beneficios destacados del plan Premium
- Precio mensual en la moneda de la company
- Botón "Upgrade Now" → checkout MercadoPago
- Botón "Maybe Later" (cerrar modal)

**Localización:** `resources/views/components/upgrade-modal.blade.php`

### 5.2. Badges y Banners

**Banner en Dashboard:**
- Ubicación: Parte superior del dashboard
- Contenido: "Upgrade to unlock all features" con botón CTA
- Visibilidad: Solo para usuarios FREE
- Dismissible: Sí, guardar en session/localStorage

**Badge "FREE" en Header:**
- Mostrar badge pequeño con "FREE" o "PREMIUM"
- Color: Gris para FREE, Verde/Dorado para PREMIUM
- Al hacer click, mostrar modal de upgrade o página de suscripción

**Mensajes Inline:**
- En página de Analytics: "Esta función requiere Premium. Upgrade ahora"
- En página de Personalización: "Personalización de marca disponible solo en Premium"
- Con botón de upgrade prominente

**Contadores con Límites:**
- Dashboard: "1/1 Restaurantes", "2/2 Códigos QR"
- Color verde si no está en límite, rojo si está en límite
- Tooltip con mensaje de upgrade

### 5.3. Página de Pricing

**Ruta:** `/pricing` (pública, no requiere auth)

**Contenido:**
- Hero section con título y descripción
- Comparación detallada de planes
- Testimonios de clientes (opcional)
- FAQ sobre suscripciones
- CTAs para registro/upgrade
- Precios en múltiples monedas si aplica

---

## ⭐ Fase 6: Funcionalidades Premium

### 6.1. Personalización de Marca

**Estado Actual:** ✅ Ya implementado en `Restaurant` model y vista `personalize.blade.php`

**Validación de Acceso:**
- Agregar middleware `RequiresPremium` a ruta `/admin/personalize`
- Si usuario es FREE, mostrar modal de upgrade en lugar de contenido

**Aplicación de Personalización:**
- Los estilos personalizados ya se aplican en menús públicos
- Mantener lógica existente, solo agregar validación de acceso

### 6.2. Analytics & Reports

**Validación de Acceso:**
- Middleware `RequiresPremium` en ruta `/admin/analytics`
- Si usuario es FREE, redirigir a modal de upgrade

**Funcionalidades Existentes:**
- Dashboard de analytics con scans por QR
- Gráficos de tendencias
- Filtros por fecha y restaurante

**Mejoras para Premium:**
- Exportación a PDF/Excel
- Filtros avanzados adicionales
- Comparativas entre períodos
- Análisis de picos y tendencias

---

## 🔔 Fase 7: Webhooks de MercadoPago

### 7.1. Endpoint de Webhooks

**Ruta:** `/api/webhooks/mercadopago`

**Método:** POST

**Autenticación:**
- Validar firma del webhook usando `x-signature` header
- Verificar `x-request-id` para idempotencia

### 7.2. Procesamiento de Eventos

**payment.approved:**
1. Buscar subscription por `mp_payment_id` o `mp_subscription_id`
2. Si es pago inicial:
   - Crear registro en `subscriptions`
   - Crear preapproval en MP
   - Actualizar company
3. Si es pago recurrente:
   - Registrar en `subscription_payments`
   - Actualizar `current_period_end`
4. Enviar email de confirmación

**payment.rejected:**
1. Buscar subscription relacionada
2. Cambiar status a `past_due`
3. Registrar intento fallido
4. Enviar email de alerta
5. Programar tarea para verificar después de 7 días

**payment.cancelled:**
1. Buscar subscription relacionada
2. Cambiar status a `cancelled`
3. Si es cancelación voluntaria, mantener acceso hasta `ends_at`
4. Enviar email de confirmación

**subscription.preapproval.cancelled:**
1. Buscar subscription por `mp_preapproval_id`
2. Marcar como `cancelled`
3. Establecer `ends_at` = `current_period_end`
4. Enviar email de confirmación

**subscription.preapproval.expired:**
1. Buscar subscription relacionada
2. Cambiar status a `expired`
3. Degradar company a plan FREE
4. Enviar email de notificación

### 7.3. Idempotencia y Logging

**Idempotencia:**
- Guardar `x-request-id` en tabla `webhook_logs`
- Verificar antes de procesar cada webhook
- Retornar 200 OK si ya fue procesado

**Logging:**
- Registrar todos los webhooks recibidos
- Guardar payload completo
- Registrar errores y excepciones
- Tabla `webhook_logs` para auditoría

---

## ⚙️ Fase 8: Panel de Gestión de Suscripción

### 8.1. Página `/admin/subscription`

**Contenido:**
- **Estado Actual:**
  - Plan actual (FREE/PREMIUM)
  - Status de suscripción (active/cancelled/expired)
  - Fecha de renovación próxima
  - Días restantes en período actual

- **Información de Pago:**
  - Método de pago registrado (últimos 4 dígitos)
  - Monto del plan
  - Moneda

- **Historial de Pagos:**
  - Tabla con todos los pagos
  - Fecha, monto, status
  - Link para descargar factura (si aplica)

- **Acciones:**
  - Botón "Cancelar Suscripción" (si está activa)
  - Botón "Reactivar Suscripción" (si está cancelada)
  - Botón "Actualizar Método de Pago"
  - Botón "Upgrade to Premium" (si es FREE)

### 8.2. Cancelación de Suscripción

**Flujo:**
1. Usuario hace click en "Cancelar Suscripción"
2. Mostrar modal de confirmación con información:
   - Acceso mantendrá hasta fin de período
   - Fecha exacta de finalización
   - Opción de reactivar antes de esa fecha
3. Si confirma:
   - Llamar a MP API para cancelar preapproval
   - Actualizar `cancelled_at` y `ends_at` en subscription
   - Cambiar status a `cancelled`
   - Mantener acceso hasta `ends_at`
4. Enviar email de confirmación

### 8.3. Reactivación

**Flujo:**
1. Usuario con suscripción cancelada ve botón "Reactivar"
2. Crear nuevo preapproval en MP
3. Actualizar subscription:
   - `status` = `active`
   - `cancelled_at` = NULL
   - `ends_at` = NULL
   - Actualizar períodos
4. Enviar email de confirmación

---

## 📧 Fase 9: Emails y Notificaciones

### 9.1. Templates de Email

**Suscripción Activada:**
- Asunto: "¡Bienvenido a Cartify Premium!"
- Contenido: Confirmación de activación, beneficios, próximos pasos

**Pago Exitoso (Mensual):**
- Asunto: "Pago procesado - Cartify Premium"
- Contenido: Confirmación de pago, monto, próxima renovación

**Pago Rechazado:**
- Asunto: "Atención: Problema con tu pago - Cartify"
- Contenido: Alerta, instrucciones para actualizar método de pago, período de gracia

**Suscripción Cancelada:**
- Asunto: "Suscripción cancelada - Cartify"
- Contenido: Confirmación, fecha de finalización, opción de reactivar

**Suscripción Expirando (3 días antes):**
- Asunto: "Tu suscripción expira pronto - Cartify"
- Contenido: Recordatorio, fecha de expiración, renovar ahora

**Suscripción Expirada:**
- Asunto: "Suscripción expirada - Cartify"
- Contenido: Notificación de expiración, degradación a FREE, reactivar

### 9.2. Sistema de Notificaciones In-App

**Notificaciones en Dashboard:**
- Banner cuando pago está pendiente
- Alerta cuando suscripción expira en menos de 7 días
- Confirmación cuando suscripción se activa/cancela

---

## 🧪 Fase 10: Testing y Seguridad

### 10.1. Tests Unitarios

**SubscriptionPlanTest:**
- Test crear plan
- Test obtener límites
- Test obtener features

**SubscriptionTest:**
- Test activar suscripción
- Test cancelar suscripción
- Test renovar suscripción
- Test verificar status

**CompanySubscriptionLimitsTest:**
- Test verificar límites de restaurantes
- Test verificar límites de usuarios
- Test verificar límites de QR codes
- Test verificar features

### 10.2. Tests de Integración

**SubscriptionFlowTest:**
- Test flujo completo de suscripción
- Test procesamiento de webhook de pago
- Test cobro recurrente
- Test cancelación y reactivación

**WebhookProcessingTest:**
- Test procesar payment.approved
- Test procesar payment.rejected
- Test idempotencia de webhooks
- Test validación de firma

**LimitEnforcementTest:**
- Test crear restaurante cuando límite alcanzado
- Test crear usuario cuando límite alcanzado
- Test crear QR cuando límite alcanzado
- Test acceso a rutas premium sin suscripción

### 10.3. Seguridad

**Validaciones:**
- Validar firma de webhooks de MP
- Sanitizar todos los inputs de usuario
- Validar ownership de recursos antes de modificar

**Rate Limiting:**
- Limitar requests a endpoint de webhooks
- Limitar creación de recursos para prevenir abuso

**Logging y Auditoría:**
- Log de todas las acciones críticas
- Log de webhooks recibidos
- Log de cambios de plan
- Log de intentos de acceso no autorizados

**Migración de Usuarios Existentes:**
- Script para asignar plan FREE a todas las companies existentes
- Ejecutar después de crear seeder de planes

---

## 📁 Estructura de Archivos

```
backend/
├── app/
│   ├── Models/
│   │   ├── SubscriptionPlan.php
│   │   ├── Subscription.php
│   │   └── SubscriptionPayment.php
│   ├── Services/
│   │   └── MercadoPagoService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── SubscriptionController.php
│   │   │   ├── MercadoPagoWebhookController.php
│   │   │   └── PricingController.php
│   │   └── Middleware/
│   │       ├── CheckSubscriptionLimits.php
│   │       └── RequiresPremium.php
│   ├── Traits/
│   │   └── HasSubscriptionLimits.php
│   └── Mail/
│       ├── SubscriptionActivated.php
│       ├── PaymentSuccessful.php
│       ├── PaymentRejected.php
│       ├── SubscriptionCancelled.php
│       └── SubscriptionExpiring.php
├── database/
│   ├── migrations/
│   │   ├── create_subscription_plans_table.php
│   │   ├── create_subscriptions_table.php
│   │   ├── create_subscription_payments_table.php
│   │   ├── create_webhook_logs_table.php
│   │   └── add_subscription_to_companies_table.php
│   └── seeders/
│       └── SubscriptionPlanSeeder.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── subscription.blade.php
│       │   └── components/
│       │       └── upgrade-modal.blade.php
│       ├── pricing.blade.php
│       └── emails/
│           ├── subscription-activated.blade.php
│           ├── payment-successful.blade.php
│           ├── payment-rejected.blade.php
│           └── subscription-cancelled.blade.php
└── tests/
    ├── Unit/
    │   ├── SubscriptionPlanTest.php
    │   ├── SubscriptionTest.php
    │   └── CompanySubscriptionLimitsTest.php
    └── Feature/
        ├── SubscriptionFlowTest.php
        ├── WebhookProcessingTest.php
        └── LimitEnforcementTest.php
```

---

## 🔄 Flujos de Usuario Completos

### Usuario Nuevo (FREE)

1. **Registro:**
   - Usuario se registra → Company creada automáticamente
   - Plan FREE asignado automáticamente
   - Recibe bienvenida

2. **Uso Básico:**
   - Crea 1 restaurante ✅
   - Crea 1 usuario adicional ✅
   - Crea 2 códigos QR ✅

3. **Intento de Upgrade:**
   - Intenta crear 2do restaurante → Modal de upgrade aparece
   - Hace click en "Upgrade Now"
   - Redirigido a checkout de MercadoPago
   - Completa pago

4. **Activación Premium:**
   - Webhook procesa pago
   - Suscripción activada
   - Plan actualizado a PREMIUM
   - Recibe email de confirmación
   - Puede crear recursos ilimitados
   - Accede a Analytics y Personalización

### Usuario Premium Existente

1. **Uso Normal:**
   - Crea múltiples restaurantes sin límites
   - Crea usuarios ilimitados
   - Usa Analytics y Personalización

2. **Cobro Recurrente:**
   - Cada mes, MP cobra automáticamente
   - Webhook procesa pago
   - Email de confirmación enviado
   - Período de suscripción renovado

3. **Cancelación:**
   - Va a `/admin/subscription`
   - Hace click en "Cancelar Suscripción"
   - Confirma cancelación
   - Acceso mantiene hasta fin de período
   - Recibe email de confirmación

4. **Reactivación (opcional):**
   - Antes de que expire, puede reactivar
   - Click en "Reactivar Suscripción"
   - Nuevo preapproval creado
   - Suscripción reactivada

### Usuario con Pago Fallido

1. **Pago Rechazado:**
   - MP rechaza pago
   - Webhook procesa rechazo
   - Status cambia a `past_due`
   - Email de alerta enviado

2. **Período de Gracia:**
   - 7 días para resolver el pago
   - Banners de alerta en dashboard
   - Recordatorios por email

3. **Resolución:**
   - Opción A: Actualiza método de pago → Pago aprobado → Status vuelve a `active`
   - Opción B: No resuelve → Status cambia a `expired` → Degradación a FREE

---

## 🚀 Consideraciones Adicionales

### Período de Prueba (Opcional)

Si se implementa:
- 14 días gratis de Premium para nuevos usuarios
- Campo `trial_ends_at` en subscriptions
- Validar en middleware si está en período de prueba
- Email cuando período de prueba está por expirar

### Precios y Monedas

- Precios configurables en `subscription_plans`
- Soporte para múltiples monedas según `company.currency`
- Convertir precios según moneda de la company

### Facturación

- Generar facturas PDF desde `subscription_payments`
- Almacenar facturas en storage
- Endpoint para descargar facturas
- Enviar facturas por email automáticamente

### Métricas y Analytics de Negocio

- Dashboard admin para ver:
  - Total de suscriptores activos
  - MRR (Monthly Recurring Revenue)
  - Churn rate
  - Conversión de FREE a PREMIUM
  - Ingresos por período

### Migración de Usuarios Existentes

Al deployar:
1. Ejecutar migraciones
2. Ejecutar seeder de planes
3. Ejecutar script de migración:
   - Asignar plan FREE a todas las companies existentes
   - Crear subscription con status `active` y plan FREE
   - Mantener todos los recursos existentes (no eliminar nada)

---

## 📝 Notas de Implementación

- **Personalización de Marca:** Ya está implementado en el sistema actual, solo se requiere agregar validación de acceso premium
- **Analytics:** Ya existe funcionalidad básica, solo requiere validación de acceso premium
- **MercadoPago:** Se utilizará el formato "Integración con Suscripciones" configurado en la aplicación Cartify
- **Compatibilidad:** El sistema debe ser backward compatible con usuarios existentes

---

## ✅ Checklist de Implementación

- [ ] Fase 1: Estructura de BD y Modelos
- [ ] Fase 2: Integración MercadoPago
- [ ] Fase 3: Middleware y Validaciones
- [ ] Fase 4: Sistema de Limitaciones
- [ ] Fase 5: UI/UX para usuarios Free
- [ ] Fase 6: Funcionalidades Premium
- [ ] Fase 7: Webhooks
- [ ] Fase 8: Panel de Gestión
- [ ] Fase 9: Emails
- [ ] Fase 10: Testing y Seguridad

---

**Última actualización:** Enero 2026
**Versión:** 1.0


