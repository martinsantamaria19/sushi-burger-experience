# Configuración de Webhooks de MercadoPago

## ¿Cómo funcionan los webhooks?

Los webhooks se configuran **automáticamente** en cada preferencia de pago. Cuando creas una orden con MercadoPago, el sistema incluye una `notification_url` en la preferencia, y MercadoPago envía notificaciones a esa URL cuando hay cambios en el estado del pago.

**No necesitas configurar webhooks manualmente en el panel de MercadoPago** - se configuran automáticamente por cada preferencia.

## Configuración para Desarrollo Local

### Opción 1: Usar ngrok (Recomendado)

1. **Instalar ngrok:**
   ```bash
   # Descargar desde https://ngrok.com/download
   # O con npm: npm install -g ngrok
   ```

2. **Iniciar ngrok:**
   ```bash
   ngrok http 8080
   ```

3. **Copiar la URL pública** (ej: `https://abc123.ngrok.io`)

4. **Configurar en `.env`:**
   ```env
   APP_URL=https://abc123.ngrok.io
   ```

5. **Reiniciar la aplicación** para que tome el nuevo `APP_URL`

### Opción 2: Usar otra herramienta de túnel

- **Cloudflare Tunnel** (gratis)
- **LocalTunnel** (gratis)
- **Serveo** (gratis)

## Configuración para Producción

En producción, simplemente configura:

```env
APP_URL=https://tudominio.com
```

## Verificación de Webhooks

Los webhooks se envían automáticamente a:
- **Pagos de órdenes:** `{APP_URL}/api/webhooks/mercadopago/orders`
- **Suscripciones:** `{APP_URL}/api/webhooks/mercadopago`

## Logs de Webhooks

Todos los webhooks se registran en los logs de Laravel. Puedes verlos con:

```bash
# Ver logs en tiempo real
php artisan pail

# O ver el archivo de logs
tail -f storage/logs/laravel.log
```

## Testing de Webhooks

Para probar webhooks localmente:

1. Configura `APP_URL` con tu URL pública (ngrok, etc.)
2. Crea una orden de prueba
3. Completa el pago en MercadoPago
4. Revisa los logs para ver la notificación recibida

## Notas Importantes

- Los webhooks funcionan **solo con URLs públicas** (no localhost)
- MercadoPago envía webhooks automáticamente cuando hay cambios en el estado del pago
- El sistema maneja la idempotencia para evitar procesar el mismo webhook dos veces
- Si el webhook falla, MercadoPago lo reintentará automáticamente

## Troubleshooting

### Webhooks no llegan

1. Verifica que `APP_URL` esté configurado correctamente
2. Asegúrate de que la URL sea accesible desde internet (no localhost)
3. Revisa los logs de Laravel para ver si hay errores
4. Verifica que las rutas de webhooks no requieran autenticación

### Webhooks llegan pero no procesan

1. Revisa los logs para ver el error específico
2. Verifica que las tablas `payments` y `orders` existan
3. Asegúrate de que la cuenta de MercadoPago esté configurada correctamente
