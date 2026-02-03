# Checklist de Testing - Sistema de Suscripciones

## ✅ Estado Actual - LISTO PARA TESTEAR

- ✅ Migraciones ejecutadas (todas las tablas creadas)
- ✅ Planes de suscripción creados (FREE y PREMIUM)
- ✅ Rutas registradas correctamente
- ✅ Webhook configurado en MercadoPago
- ✅ Variables de entorno configuradas
- ✅ Sin errores de linter

---

## 🧪 Plan de Testing

### 1. Verificar Configuración Básica

#### A. Verificar que los planes existen:
```bash
docker exec cartify_app php artisan tinker
```
Luego ejecuta:
```php
App\Models\SubscriptionPlan::all(['id', 'name', 'slug', 'price']);
```

**Resultado esperado:** Debe mostrar 2 planes (FREE y PREMIUM)

#### B. Verificar que las companies tienen plan asignado:
```php
App\Models\Company::with('currentPlan')->get(['id', 'name', 'plan_id']);
```

**Resultado esperado:** Todas las companies deben tener `plan_id` asignado (FREE por defecto)

---

### 2. Testing de Rutas API

#### A. Obtener información de suscripción actual (requiere autenticación):
```bash
# Primero, necesitas obtener un token de autenticación o hacer login
# Luego hacer una petición GET a:
GET /subscriptions/current
```

**Headers necesarios:**
- `Authorization: Bearer {token}` (si usas API)
- `Cookie: laravel_session={session}` (si usas web)

**Resultado esperado:**
```json
{
  "subscription": null,
  "plan": {
    "id": 1,
    "name": "Free",
    "slug": "free",
    "price": null,
    "limits": {
      "restaurants": 1,
      "users": 1,
      "qr_codes": 2
    }
  },
  "is_premium": false,
  "is_free": true,
  "limits": {
    "restaurants": {
      "current": 1,
      "limit": 1,
      "remaining": 0
    },
    ...
  }
}
```

#### B. Crear intento de suscripción (crear payment preference):
```bash
POST /subscriptions/create-intent
Content-Type: application/json

{
  "plan_id": 2  // ID del plan PREMIUM
}
```

**Resultado esperado:**
- Si está en modo sandbox: Devuelve `init_point` y `sandbox_init_point`
- Debe redirigir a MercadoPago para el pago

---

### 3. Testing del Flujo Completo

#### Paso 1: Iniciar suscripción Premium
1. Login en la aplicación
2. Ir a la página de suscripción/settings
3. Hacer clic en "Upgrade to Premium" o botón similar
4. Esto debería llamar a `/subscriptions/create-intent` con `plan_id=2`
5. Deberías ser redirigido a MercadoPago

#### Paso 2: Completar pago en MercadoPago (Sandbox)
1. En la página de MercadoPago, usar una tarjeta de prueba
2. Para sandbox, usar estas tarjetas:
   - **Tarjeta aprobada:** `5031 7557 3453 0604`
   - **CVV:** `123`
   - **Fecha:** Cualquiera futura
   - **Titular:** APRO
3. Completar el pago

#### Paso 3: Verificar redirección
- Deberías ser redirigido a `/subscriptions/success`
- Luego redirigido al dashboard con mensaje de éxito

#### Paso 4: Verificar webhook
- MercadoPago debería enviar un webhook a `/api/webhooks/mercadopago`
- Verificar los logs:
```bash
docker exec cartify_app tail -f storage/logs/laravel.log
```

**Buscar en logs:**
- "MercadoPago Webhook Received"
- "Payment status: approved"
- "Subscription created from payment"

#### Paso 5: Verificar que la suscripción se creó
```bash
docker exec cartify_app php artisan tinker
```
```php
$company = App\Models\Company::first();
$company->activeSubscription;
$company->currentPlan;
```

**Resultado esperado:**
- `activeSubscription` debe existir con `status='active'`
- `currentPlan` debe ser PREMIUM

---

### 4. Testing de Webhook Manual (Opcional)

Si quieres simular un webhook sin hacer un pago real, puedes usar:

```bash
curl -X POST http://tu-ngrok-url.ngrok.io/api/webhooks/mercadopago \
  -H "Content-Type: application/json" \
  -d '{
    "type": "payment",
    "data": {
      "id": "123456789"
    }
  }'
```

O usar el MCP de MercadoPago para simular webhooks (si está disponible).

---

### 5. Testing de Límites de Plan FREE

#### A. Intentar crear más de 1 restaurante:
- Si estás en plan FREE, intentar crear un segundo restaurante
- Debería fallar o mostrar mensaje de upgrade

#### B. Intentar crear más de 1 usuario:
- Similar al anterior

#### C. Intentar crear más de 2 QR codes:
- Similar al anterior

---

### 6. Testing de Cancelación de Suscripción

```bash
POST /subscriptions/cancel
```

**Resultado esperado:**
- La suscripción se marca como `cancelled`
- El plan se degrada a FREE al finalizar el período
- O inmediatamente si está configurado así

---

## 🐛 Posibles Problemas y Soluciones

### Problema 1: Error "Access Token inválido"
**Solución:** Verificar que `MP_ACCESS_TOKEN` en `.env` sea correcto

### Problema 2: Webhook no llega
**Solución:**
- Verificar que la URL de ngrok sea pública
- Verificar que la ruta `/api/webhooks/mercadopago` esté excluida de CSRF
- Verificar logs: `storage/logs/laravel.log`

### Problema 3: Error al crear payment preference
**Solución:**
- Verificar que `plan->price` no sea null para plan PREMIUM
- Verificar que la moneda sea válida (UYU, USD, etc.)
- Verificar logs para ver el error específico

### Problema 4: Preapproval no se crea
**Solución:**
- Verificar que el pago inicial sea con tarjeta (no efectivo)
- El preapproval se crea automáticamente después del primer pago aprobado
- Verificar logs para ver si hay error en `createPreapproval`

---

## 📝 Notas Importantes

1. **Sandbox vs Production:**
   - En sandbox, usa tarjetas de prueba de MercadoPago
   - Los webhooks pueden tardar unos segundos en llegar
   - Los pagos no se procesan realmente

2. **Webhook URL:**
   - Asegúrate de que ngrok esté corriendo
   - Si cambias la URL de ngrok, actualízala en MercadoPago
   - MercadoPago puede tardar en validar la URL

3. **Logs:**
   - Todos los eventos importantes se loguean en `storage/logs/laravel.log`
   - Revisa los logs si algo no funciona

4. **Datos de Prueba:**
   - Usa usuarios de prueba de MercadoPago para sandbox
   - Las tarjetas de prueba tienen comportamientos específicos (APRO, CONT, etc.)

---

## ✅ Siguiente Paso

Una vez que todo funcione en sandbox:
1. Cambiar `MP_ENVIRONMENT=production` en `.env`
2. Actualizar credenciales de producción en `.env`
3. Actualizar webhook URL en MercadoPago a URL de producción
4. Repetir testing con datos reales (pequeños montos primero)


