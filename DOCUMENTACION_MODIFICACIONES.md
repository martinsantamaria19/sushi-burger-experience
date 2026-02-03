# Documentación de Modificaciones - Sushi Burger Experience

Este documento registra todas las modificaciones realizadas al proyecto, organizadas por fases de implementación.

---

## 📋 Índice

- [Fase 1: Sistema de Carrito de Compras](#fase-1-sistema-de-carrito-de-compras)
- [Fase 2: Sistema de Pedidos](#fase-2-sistema-de-pedidos) (En progreso)

---

## 🛒 Fase 1: Sistema de Carrito de Compras

**Fecha de implementación:** Febrero 2026  
**Estado:** ✅ Completada

### Objetivo
Implementar un sistema completo de carrito de compras que permita a los clientes agregar productos desde el menú público, gestionar cantidades y prepararse para el proceso de checkout.

### Archivos Creados

#### 1. Migración de Base de Datos
**Archivo:** `backend/database/migrations/2026_02_03_162555_create_cart_items_table.php`

**Estructura de la tabla `cart_items`:**
- `id` (bigint, PK)
- `session_id` (string, nullable, index) - Para carritos de usuarios no autenticados
- `user_id` (bigint, nullable, FK users) - Para carritos de usuarios registrados
- `restaurant_id` (bigint, FK restaurants) - Restaurante del producto
- `product_id` (bigint, FK products) - Producto agregado
- `quantity` (integer, default 1) - Cantidad del producto
- `price` (decimal 10,2) - Precio snapshot al momento de agregar
- `notes` (text, nullable) - Notas especiales del cliente
- `created_at`, `updated_at` (timestamps)

**Índices:**
- `session_id` - Para búsquedas rápidas por sesión
- `user_id` - Para búsquedas rápidas por usuario
- `restaurant_id` - Para agrupar por restaurante
- `unique_session_product` - Evita duplicados en carritos de sesión
- `unique_user_product` - Evita duplicados en carritos de usuario

**Características:**
- Soporte dual: carritos basados en sesión (guest) y usuario (autenticado)
- Precio snapshot para evitar cambios de precio durante el proceso
- Un carrito por restaurante (no se mezclan productos de diferentes restaurantes)

#### 2. Modelo CartItem
**Archivo:** `backend/app/Models/CartItem.php`

**Relaciones:**
- `belongsTo(User)` - Usuario propietario del carrito
- `belongsTo(Restaurant)` - Restaurante del producto
- `belongsTo(Product)` - Producto en el carrito

**Scopes:**
- `scopeForSession($query, string $sessionId)` - Filtrar por sesión
- `scopeForUser($query, int $userId)` - Filtrar por usuario
- `scopeForRestaurant($query, int $restaurantId)` - Filtrar por restaurante
- `scopeForCurrentContext($query, ?string $sessionId, ?int $userId)` - Filtrar por contexto actual (sesión o usuario)

**Métodos:**
- `getSubtotalAttribute()` - Calcula subtotal (price * quantity)

#### 3. Modelo Product (Actualizado)
**Archivo:** `backend/app/Models/Product.php`

**Modificaciones:**
- Agregado import `HasMany`
- Agregada relación `cartItems(): HasMany`
- Agregado método `isAvailable(): bool` - Verifica si el producto está disponible para compra

#### 4. Controlador CartController
**Archivo:** `backend/app/Http/Controllers/CartController.php`

**Métodos implementados:**

1. **`index()`** - Muestra el carrito actual
   - Agrupa items por restaurante
   - Calcula totales
   - Retorna vista `public.cart`

2. **`add(Request $request): JsonResponse`** - Agrega producto al carrito
   - Valida que el producto exista y esté disponible
   - Verifica si ya existe en el carrito (actualiza cantidad)
   - Crea nuevo item si no existe
   - Retorna JSON con estado y datos del item

3. **`update(Request $request, CartItem $cartItem): JsonResponse`** - Actualiza cantidad
   - Valida propiedad del item
   - Actualiza cantidad y notas
   - Retorna JSON con nuevos totales

4. **`remove(CartItem $cartItem): JsonResponse`** - Elimina item del carrito
   - Valida propiedad
   - Elimina el item
   - Retorna JSON con nuevos totales

5. **`clear(): JsonResponse`** - Vacía el carrito completo
   - Elimina todos los items del contexto actual

6. **`getTotal(): JsonResponse`** - API para obtener totales
   - Calcula total y cantidad de items
   - Retorna JSON con totales formateados

**Métodos privados:**
- `getCartContext(): array` - Obtiene contexto (session_id o user_id)
- `getCartCount(): int` - Cuenta items en el carrito
- `getCartTotal(): float` - Calcula total del carrito

#### 5. Rutas
**Archivo:** `backend/routes/web.php`

**Rutas agregadas (públicas, sin autenticación):**
```php
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/{cartItem}', [CartController::class, 'update'])->name('update');
    Route::delete('/{cartItem}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::get('/total', [CartController::class, 'getTotal'])->name('total');
});
```

#### 6. Vista del Carrito
**Archivo:** `backend/resources/views/public/cart.blade.php`

**Características:**
- Diseño responsive y moderno
- Agrupación de items por restaurante
- Controles de cantidad con botones +/- 
- Botón para eliminar items
- Resumen de totales (subtotal, envío, total)
- Estado vacío con mensaje amigable
- Botón "Continuar al Checkout" (preparado para Fase 2)

**Funcionalidades JavaScript:**
- Actualización de cantidad sin recargar página
- Eliminación de items con confirmación
- Cálculo dinámico de totales
- Integración con SweetAlert2 para notificaciones

#### 7. Vista del Menú Público (Actualizada)
**Archivo:** `backend/resources/views/public/menu.blade.php`

**Modificaciones:**

1. **Botón "Agregar al carrito"** en cada producto:
   ```html
   <button class="btn-add-cart" data-product-id="{{ $product->id }}" ...>
       <i data-lucide="shopping-cart"></i>
   </button>
   ```

2. **Botón flotante del carrito** con contador:
   ```html
   <a href="{{ route('cart.index') }}" class="cart-floating-btn">
       <i data-lucide="shopping-cart"></i>
       <span class="cart-badge" id="cartBadge">0</span>
   </a>
   ```

3. **Estilos CSS agregados:**
   - `.btn-add-cart` - Estilo del botón de agregar
   - `.cart-floating-btn` - Botón flotante del carrito
   - `.cart-badge` - Badge con contador de items

4. **JavaScript agregado:**
   - Función `updateCartBadge()` - Actualiza contador del carrito
   - Event listeners para botones "Agregar al carrito"
   - Integración con API del carrito
   - Notificaciones con SweetAlert2
   - Animaciones al agregar productos

### Flujo de Usuario

1. **Cliente visita menú público** → Ve productos con precios
2. **Cliente hace clic en "Agregar al carrito"** → Producto se agrega sin recargar página
3. **Badge del carrito se actualiza** → Muestra cantidad de items
4. **Cliente hace clic en botón flotante del carrito** → Va a vista del carrito
5. **Cliente gestiona su carrito** → Actualiza cantidades, elimina items
6. **Cliente hace clic en "Continuar al Checkout"** → (Preparado para Fase 2)

### Consideraciones Técnicas

1. **Sesiones vs Usuarios:**
   - Usuarios no autenticados: carrito basado en `session_id`
   - Usuarios autenticados: carrito basado en `user_id`
   - El sistema detecta automáticamente el contexto

2. **Precio Snapshot:**
   - El precio se guarda al momento de agregar al carrito
   - Evita cambios de precio durante el proceso de compra
   - Garantiza transparencia para el cliente

3. **Validaciones:**
   - Producto debe existir y estar disponible
   - Cantidad mínima: 1, máxima: 99
   - Verificación de propiedad antes de modificar/eliminar

4. **Performance:**
   - Índices en campos clave para consultas rápidas
   - Eager loading de relaciones (product, restaurant)
   - Consultas optimizadas con scopes

### Testing

**Casos de prueba recomendados:**
- [ ] Agregar producto al carrito (usuario no autenticado)
- [ ] Agregar producto al carrito (usuario autenticado)
- [ ] Actualizar cantidad de un item
- [ ] Eliminar item del carrito
- [ ] Vaciar carrito completo
- [ ] Agregar mismo producto dos veces (debe actualizar cantidad)
- [ ] Agregar productos de diferentes restaurantes (debe agrupar)
- [ ] Verificar que precio snapshot funciona correctamente
- [ ] Probar en dispositivos móviles (responsive)

### Dependencias

- Laravel Framework (ya incluido)
- SweetAlert2 (ya incluido en el proyecto)
- Lucide Icons (ya incluido en el proyecto)
- Bootstrap 5.3 (ya incluido en el proyecto)

---

## 📦 Fase 2: Sistema de Pedidos

**Fecha de implementación:** Febrero 2026  
**Estado:** ✅ Completada

### Objetivo
Implementar un sistema completo de pedidos que permita a los clientes convertir su carrito en un pedido, gestionar estados del pedido y realizar seguimiento.

### Archivos Creados

#### 1. Migraciones

**Archivo:** `backend/database/migrations/2026_02_03_163737_create_orders_table.php`

**Estructura de la tabla `orders`:**
- `id` (bigint, PK)
- `order_number` (string, unique) - Ej: ORD-20260203-001
- `restaurant_id` (bigint, FK restaurants)
- `user_id` (bigint, nullable, FK users) - Cliente (puede ser guest)
- `customer_name` (string) - Nombre del cliente
- `customer_email` (string, nullable)
- `customer_phone` (string) - Requerido para contacto
- `customer_address` (text) - Dirección de entrega
- `delivery_address_lat` (decimal 10,8, nullable)
- `delivery_address_lng` (decimal 11,8, nullable)
- `delivery_notes` (text, nullable) - Instrucciones de entrega
- `subtotal` (decimal 10,2)
- `delivery_fee` (decimal 10,2, default 0)
- `discount` (decimal 10,2, default 0)
- `total` (decimal 10,2)
- `status` (enum: pending, confirmed, preparing, ready, out_for_delivery, delivered, cancelled)
- `payment_method` (enum: mercadopago, bank_transfer)
- `payment_status` (enum: pending, paid, failed, refunded)
- `payment_id` (string, nullable) - ID de pago en MercadoPago
- `estimated_delivery_time` (integer, nullable) - Minutos estimados
- `actual_delivery_time` (timestamp, nullable)
- `notes` (text, nullable) - Notas del cliente
- `tracking_token` (string, unique, nullable) - Token para seguimiento público
- `created_at`, `updated_at` (timestamps)

**Índices:** order_number, restaurant_id, user_id, status, payment_status, tracking_token

**Archivo:** `backend/database/migrations/2026_02_03_163745_create_order_items_table.php`

**Estructura de la tabla `order_items`:**
- `id` (bigint, PK)
- `order_id` (bigint, FK orders)
- `product_id` (bigint, nullable, FK products) - Nullable por si se elimina el producto
- `product_name` (string) - Snapshot del nombre
- `product_price` (decimal 10,2) - Snapshot del precio
- `quantity` (integer)
- `subtotal` (decimal 10,2)
- `notes` (text, nullable)
- `created_at`, `updated_at` (timestamps)

**Archivo:** `backend/database/migrations/2026_02_03_163809_create_order_status_history_table.php`

**Estructura de la tabla `order_status_history`:**
- `id` (bigint, PK)
- `order_id` (bigint, FK orders)
- `status` (enum) - Estado anterior
- `new_status` (enum) - Estado nuevo
- `notes` (text, nullable)
- `changed_by` (bigint, nullable, FK users) - Admin que cambió el estado
- `created_at` (timestamp) - Sin updated_at

#### 2. Modelos

**Archivo:** `backend/app/Models/Order.php`

**Relaciones:**
- `belongsTo(Restaurant)` - Restaurante del pedido
- `belongsTo(User)` - Usuario que hizo el pedido (nullable)
- `hasMany(OrderItem)` - Items del pedido
- `hasMany(OrderStatusHistory)` - Historial de estados

**Scopes:**
- `scopePending()` - Pedidos pendientes
- `scopeConfirmed()` - Pedidos confirmados
- `scopeDelivered()` - Pedidos entregados
- `scopeCancelled()` - Pedidos cancelados
- `scopeByRestaurant(int $restaurantId)` - Por restaurante
- `scopeByStatus(string $status)` - Por estado

**Métodos principales:**
- `generateOrderNumber()` - Genera número único (ORD-YYYYMMDD-XXX)
- `calculateTotal()` - Calcula total del pedido
- `updateStatus(string $newStatus, ?string $notes, ?int $changedBy)` - Cambia estado y registra historial
- `canBeCancelled()` - Valida si puede cancelarse
- `cancel(?string $reason, ?int $cancelledBy)` - Cancela el pedido
- `getStatusLabelAttribute()` - Label en español del estado
- `getPaymentStatusLabelAttribute()` - Label en español del estado de pago

**Características:**
- Generación automática de `order_number` y `tracking_token` al crear
- Registro automático de cambios de estado en historial
- Validación de estados permitidos para cancelación

**Archivo:** `backend/app/Models/OrderItem.php`

**Relaciones:**
- `belongsTo(Order)` - Pedido al que pertenece
- `belongsTo(Product)` - Producto (nullable si se elimina)

**Métodos:**
- `getSubtotalAttribute()` - Calcula subtotal (price * quantity)

**Archivo:** `backend/app/Models/OrderStatusHistory.php`

**Relaciones:**
- `belongsTo(Order)` - Pedido
- `belongsTo(User, 'changed_by')` - Usuario que cambió el estado

**Características:**
- Sin timestamps (solo created_at)
- Ordenado por fecha descendente

#### 3. Controladores

**Archivo:** `backend/app/Http/Controllers/OrderController.php` (Público)

**Métodos:**

1. **`checkout()`** - Muestra formulario de checkout
   - Obtiene items del carrito
   - Valida que el carrito no esté vacío
   - Retorna vista `public.checkout`

2. **`store(Request $request)`** - Crea pedido desde carrito
   - Valida datos del cliente y método de pago
   - Verifica disponibilidad de productos
   - Crea orden y order_items en transacción
   - Limpia el carrito
   - Redirige según método de pago

3. **`show(Order $order, string $token)`** - Muestra detalles del pedido
   - Verifica token de seguimiento
   - Carga relaciones necesarias
   - Retorna vista `public.order-tracking`

4. **`track(Request $request)`** - Seguimiento por número y token
   - Busca pedido por número y token
   - Redirige a vista de detalles

**Archivo:** `backend/app/Http/Controllers/AdminOrderController.php` (Admin)

**Métodos:**

1. **`index(Request $request)`** - Lista de pedidos
   - Filtros: estado, restaurante, estado de pago, fechas, búsqueda
   - Paginación (20 por página)
   - Retorna vista `admin.orders.index`

2. **`show(Order $order)`** - Detalles del pedido
   - Verifica acceso (pedido debe pertenecer a restaurante de la compañía)
   - Carga todas las relaciones
   - Retorna vista `admin.orders.show`

3. **`updateStatus(Request $request, Order $order)`** - Cambia estado
   - Valida nuevo estado
   - Usa método `updateStatus()` del modelo
   - Registra cambio en historial

4. **`cancel(Request $request, Order $order)`** - Cancela pedido
   - Valida que pueda cancelarse
   - Usa método `cancel()` del modelo

#### 4. Rutas

**Archivo:** `backend/routes/web.php`

**Rutas públicas agregadas:**
```php
Route::prefix('orders')->name('orders.')->group(function () {
    Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
    Route::post('/', [OrderController::class, 'store'])->name('store');
    Route::get('/track', [OrderController::class, 'track'])->name('track');
    Route::get('/{order}/track/{token}', [OrderController::class, 'show'])->name('show');
});
```

**Rutas admin agregadas:**
```php
Route::prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
    Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
    Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('cancel');
});
```

#### 5. Vistas

**Archivo:** `backend/resources/views/public/checkout.blade.php`

**Características:**
- Formulario de información del cliente (nombre, email, teléfono, dirección)
- Campos opcionales para coordenadas GPS
- Selección de método de pago (MercadoPago o Transferencia Bancaria)
- Resumen del pedido con items del carrito
- Diseño responsive con grid layout
- Validación de formulario

**Archivo:** `backend/resources/views/public/order-tracking.blade.php`

**Características:**
- Muestra detalles del pedido
- Badge de estado con colores
- Lista de items del pedido
- Historial de cambios de estado
- Información de pago
- Diseño simple y claro

**Archivo:** `backend/resources/views/admin/orders/index.blade.php`

**Características:**
- Tabla de pedidos con paginación
- Filtros: estado, restaurante, estado de pago, búsqueda
- Muestra: número, cliente, restaurante, total, estado, pago, fecha
- Botón para ver detalles
- Diseño consistente con el resto del admin

**Archivo:** `backend/resources/views/admin/orders/show.blade.php`

**Características:**
- Vista detallada del pedido
- Información del cliente y dirección
- Tabla de items con totales
- Historial de estados
- Formulario para cambiar estado
- Formulario para cancelar pedido
- Link de seguimiento público (copiable)
- Información de pago

#### 6. Actualizaciones a Modelos Existentes

**Archivo:** `backend/app/Models/Restaurant.php`
- Agregada relación `orders(): HasMany`

**Archivo:** `backend/app/Models/User.php`
- Agregada relación `orders(): HasMany`

**Archivo:** `backend/resources/views/layouts/admin.blade.php`
- Agregado link "Pedidos" en el sidebar

**Archivo:** `backend/resources/views/public/cart.blade.php`
- Actualizado botón "Continuar al Checkout" para redirigir a `orders.checkout`

### Flujo de Usuario

1. **Cliente completa carrito** → Hace clic en "Continuar al Checkout"
2. **Cliente llena formulario** → Información personal y método de pago
3. **Cliente confirma pedido** → Sistema crea orden y limpia carrito
4. **Sistema genera número de pedido** → Ej: ORD-20260203-001
5. **Cliente recibe confirmación** → Con número y token de seguimiento
6. **Restaurante ve nuevo pedido** → En dashboard de pedidos
7. **Restaurante cambia estados** → pending → confirmed → preparing → ready → delivered
8. **Cliente sigue su pedido** → Usando número y token

### Estados del Pedido

- **pending** - Pendiente (recién creado)
- **confirmed** - Confirmado por el restaurante
- **preparing** - En preparación
- **ready** - Listo para entrega
- **out_for_delivery** - En camino
- **delivered** - Entregado
- **cancelled** - Cancelado

### Consideraciones Técnicas

1. **Transacciones:**
   - Creación de orden y items en transacción DB
   - Rollback automático en caso de error

2. **Snapshots:**
   - Nombres y precios de productos se guardan en order_items
   - Permite eliminar productos sin afectar pedidos históricos

3. **Seguimiento Público:**
   - Token único por pedido
   - Acceso sin autenticación pero con token válido
   - Seguridad: solo con token correcto

4. **Validaciones:**
   - Verificación de disponibilidad antes de crear pedido
   - Validación de estados permitidos para cambios
   - Verificación de acceso en admin (pedido debe pertenecer a restaurante de la compañía)

5. **Historial:**
   - Cada cambio de estado se registra automáticamente
   - Incluye usuario que hizo el cambio y notas opcionales

### Testing

**Casos de prueba recomendados:**
- [ ] Crear pedido desde carrito
- [ ] Verificar generación de número único
- [ ] Verificar creación de token de seguimiento
- [ ] Cambiar estado del pedido (admin)
- [ ] Cancelar pedido (admin)
- [ ] Seguimiento público con token válido
- [ ] Seguimiento público con token inválido (debe fallar)
- [ ] Verificar que carrito se limpia después de crear pedido
- [ ] Verificar historial de estados
- [ ] Probar filtros en lista de pedidos
- [ ] Verificar paginación

---

**Última actualización:** Febrero 2026
