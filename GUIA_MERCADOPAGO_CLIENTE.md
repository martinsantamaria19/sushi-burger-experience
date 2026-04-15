# Guia para conectar tu cuenta de MercadoPago

## Paso 1: Crear una aplicacion en MercadoPago

1. Ingresa a [mercadopago.com.uy/developers/panel](https://www.mercadopago.com.uy/developers/panel) con tu cuenta de MercadoPago.
2. Haz clic en **"Crear aplicacion"**.
3. Completa los datos:
   - **Nombre:** Ej: "Mi Restaurante - Pagos Online"
   - **Producto:** Selecciona **"Checkout Pro"**
4. Confirma la creacion.

## Paso 2: Obtener tus credenciales

Una vez creada la aplicacion:

1. Dentro del panel de desarrolladores, entra a tu aplicacion.
2. Ve a la seccion **"Credenciales de produccion"** (o "Credenciales de prueba" si quieres probar primero).
3. Copia estos dos datos:
   - **Access Token** - Empieza con `APP_USR-...` (es largo)
   - **Public Key** - Empieza con `APP_USR-...` (es mas corto)

> **Importante:** Nunca compartas tu Access Token con nadie. Es como la contrasena de tu cuenta para pagos.

## Paso 3: Configurar en el panel de administracion

1. Ingresa a tu panel de administracion del sistema.
2. Ve a **Configuracion MercadoPago** en el menu lateral.
3. Completa el formulario:

| Campo | Que poner |
|-------|-----------|
| **Access Token** | Pega el Access Token que copiaste |
| **Public Key** | Pega la Public Key que copiaste |
| **App ID** | (Opcional) El ID de tu aplicacion |
| **User ID** | (Opcional) Tu ID de usuario en MP |
| **Ambiente** | Selecciona **Produccion** para recibir pagos reales |

4. Haz clic en **"Conectar Cuenta"**.
5. Usa el boton **"Probar Conexion"** para verificar que todo esta bien.

## Paso 4: Configurar Webhooks (Notificaciones de pago)

Los webhooks permiten que MercadoPago le avise al sistema cuando un pago se aprueba, rechaza o cambia de estado. **Sin esto, los pedidos no se actualizan automaticamente.**

1. En el [Panel de Desarrolladores](https://www.mercadopago.com.uy/developers/panel), entra a tu aplicacion.
2. Ve a la seccion **"Webhooks"**.
3. Haz clic en **"Configurar notificaciones"**.
4. Configura lo siguiente:

| Campo | Valor |
|-------|-------|
| **URL de produccion** | `https://sushiburgerexperience.com/api/webhooks/mercadopago/orders` |
| **Eventos** | Marca **Payments** (pagos) |

> Reemplaza por `sushiburgerexperience.com`

5. Si tambien usas **suscripciones**, agrega una segunda URL de webhook:

| Campo | Valor |
|-------|-------|
| **URL de produccion** | `https://TU-DOMINIO.com/api/webhooks/mercadopago` |
| **Eventos** | Marca **Subscription** y **Payments** |

6. Guarda los cambios.

> **Nota:** Para pagos de ordenes individuales, el sistema configura los webhooks automaticamente en cada transaccion. Sin embargo, configurarlos en el panel de MercadoPago sirve como respaldo y es necesario para las suscripciones.

## Paso 5: Verificar que funciona

Si la prueba de conexion es exitosa, ya estas listo para recibir pagos. Tus clientes podran pagar con:

- Tarjetas de credito y debito
- Transferencia bancaria
- Otros medios disponibles en MercadoPago

---

## Modo prueba vs produccion

| Modo | Para que sirve |
|------|---------------|
| **Sandbox** | Probar pagos sin cobrar dinero real. Usa credenciales de prueba. |
| **Produccion** | Recibir pagos reales de tus clientes. Usa credenciales de produccion. |

**Recomendacion:** Configura primero en Sandbox para verificar que todo funciona, y luego cambia a Produccion.

---

## Preguntas frecuentes

**Donde llega el dinero de los pagos?**
Directamente a tu cuenta de MercadoPago vinculada a las credenciales.

**Tiene algun costo?**
MercadoPago cobra su comision habitual por cada venta.l.

**Puedo desconectar mi cuenta?**
Si, desde el mismo panel haciendo clic en "Desconectar". Los pagos ya procesados no se ven afectados.

**Que pasa si cambio mis credenciales en MercadoPago?**
Debes actualizar las nuevas credenciales en el panel de administracion para seguir recibiendo pagos.
