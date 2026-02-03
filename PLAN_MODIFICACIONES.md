# Plan de Modificaciones - Sushi Burger Experience

## 📋 Resumen Ejecutivo

Plan de desarrollo para transformar la plataforma actual (menú + QR) en un sistema completo de pedidos online con carrito de compras, múltiples métodos de pago, gestión de pedidos y reportes de ventas.

---

## 🎯 Estado Actual vs Objetivo

### ✅ Lo que ya existe:
- Sistema de menú (Product, Category, Menu)
- Códigos QR que redirigen al menú público
- Vista pública del menú con precios
- Integración MercadoPago (solo para suscripciones)
- Dashboard básico de administración (menú, usuarios, QR, analytics)
- Sistema de suscripciones

### 🎯 Objetivos a implementar:
1. **Carrito de compras** funcional
2. **Sistema de pedidos** completo
3. **Checkout** con MercadoPago y Transferencia Bancaria
4. **Dashboard de pedidos** y pagos
5. **Cálculo de tiempo de entrega** (delivery time)
6. **Panel de reportes de ventas**

---

## 📊 Fase 1: Sistema de Carrito de Compras

### 1.1 Base de Datos

**Nueva tabla: `cart_items`**
```sql
- id (bigint, PK)
- session_id (string, nullable) - Para carritos no autenticados
- user_id (bigint, nullable, FK users) - Para carritos de usuarios registrados
- restaurant_id (bigint, FK restaurants)
- product_id (bigint, FK products)
- quantity (integer)
- price (decimal 10,2) - Precio al momento de agregar (snapshot)
- notes (text, nullable) - Notas especiales del cliente
- created_at, updated_at
- Índices: session_id, user_id, restaurant_id
```

**Consideraciones:**
- Carrito basado en sesión para usuarios no autenticados
- Carrito persistente para usuarios autenticados
- Precio snapshot para evitar cambios de precio durante el proceso
- Un carrito por restaurante (no mezclar productos de diferentes restaurantes)

### 1.2 Modelos y Relaciones

**Model: `CartItem`**
- Relaciones: `belongsTo(User)`, `belongsTo(Restaurant)`, `belongsTo(Product)`
- Scopes: `forSession()`, `forUser()`, `forRestaurant()`
- Métodos: `getSubtotal()`, `getTotal()`

**Actualizar Model: `Product`**
- Agregar relación `hasMany(CartItem)`
- Método `isAvailable()` para verificar stock/disponibilidad

### 1.3 Controladores y Rutas

**Controller: `CartController`**
- `index()` - Mostrar carrito actual
- `add()` - Agregar producto al carrito
- `update()` - Actualizar cantidad
- `remove()` - Eliminar item
- `clear()` - Vaciar carrito
- `getTotal()` - Calcular total (API)

**Rutas:**
```php
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::put('/{item}', [CartController::class, 'update'])->name('update');
    Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');
    Route::get('/total', [CartController::class, 'getTotal'])->name('total');
});
```

### 1.4 Vistas Frontend

**Vista: `public/cart.blade.php`**
- Lista de items del carrito
- Cantidad editable
- Botón para eliminar items
- Resumen de totales
- Botón "Continuar al checkout"
- Validación de disponibilidad de productos

**Componente JavaScript:**
- Agregar al carrito desde el menú
- Actualizar cantidad sin recargar página
- Calcular totales dinámicamente
- Persistencia en localStorage como backup

---

## 📦 Fase 2: Sistema de Pedidos (Orders)

### 2.1 Base de Datos

**Nueva tabla: `orders`**
```sql
- id (bigint, PK)
- order_number (string, unique) - Ej: ORD-20260103-001
- restaurant_id (bigint, FK restaurants)
- user_id (bigint, nullable, FK users) - Cliente (puede ser guest)
- customer_name (string) - Nombre del cliente
- customer_email (string, nullable)
- customer_phone (string) - Requerido para contacto
- customer_address (text) - Dirección de entrega
- delivery_address_lat (decimal 10,8, nullable)
- delivery_address_lng (decimal 11,8, nullable)
- delivery_notes (text, nullable) - Instrucciones de entrega
- subtotal (decimal 10,2)
- delivery_fee (decimal 10,2, default 0)
- discount (decimal 10,2, default 0)
- total (decimal 10,2)
- status (enum: pending, confirmed, preparing, ready, out_for_delivery, delivered, cancelled)
- payment_method (enum: mercadopago, bank_transfer)
- payment_status (enum: pending, paid, failed, refunded)
- payment_id (string, nullable) - ID de pago en MercadoPago
- estimated_delivery_time (integer, nullable) - Minutos estimados
- actual_delivery_time (timestamp, nullable)
- notes (text, nullable) - Notas del cliente
- created_at, updated_at
- Índices: order_number, restaurant_id, user_id, status, payment_status
```

**Nueva tabla: `order_items`**
```sql
- id (bigint, PK)
- order_id (bigint, FK orders)
- product_id (bigint, FK products)
- product_name (string) - Snapshot del nombre
- product_price (decimal 10,2) - Snapshot del precio
- quantity (integer)
- subtotal (decimal 10,2)
- notes (text, nullable)
- created_at, updated_at
```

**Nueva tabla: `order_status_history`**
```sql
- id (bigint, PK)
- order_id (bigint, FK orders)
- status (enum) - Estado anterior
- new_status (enum) - Estado nuevo
- notes (text, nullable)
- changed_by (bigint, nullable, FK users) - Admin que cambió el estado
- created_at
```

### 2.2 Modelos

**Model: `Order`**
- Relaciones: `belongsTo(Restaurant)`, `belongsTo(User)`, `hasMany(OrderItem)`, `hasMany(OrderStatusHistory)`
- Scopes: `pending()`, `confirmed()`, `delivered()`, `cancelled()`, `byRestaurant()`, `byStatus()`
- Métodos: 
  - `generateOrderNumber()` - Generar número único
  - `calculateTotal()` - Calcular total
  - `updateStatus()` - Cambiar estado y registrar historial
  - `canBeCancelled()` - Validar si puede cancelarse
  - `getEstimatedDeliveryTime()` - Calcular tiempo estimado

**Model: `OrderItem`**
- Relaciones: `belongsTo(Order)`, `belongsTo(Product)`
- Métodos: `getSubtotal()`

**Model: `OrderStatusHistory`**
- Relaciones: `belongsTo(Order)`, `belongsTo(User)`

### 2.3 Controladores

**Controller: `OrderController` (Público)**
- `store()` - Crear pedido desde carrito
- `show()` - Ver detalles del pedido (con token de acceso)
- `track()` - Seguimiento público del pedido

**Controller: `AdminOrderController` (Admin)**
- `index()` - Lista de pedidos con filtros
- `show()` - Detalles del pedido
- `updateStatus()` - Cambiar estado del pedido
- `cancel()` - Cancelar pedido
- `export()` - Exportar pedidos (CSV/Excel)

### 2.4 Rutas

```php
// Públicas
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}/track/{token}', [OrderController::class, 'track'])->name('orders.track');

// Admin
Route::middleware(['auth'])->prefix('admin/orders')->name('admin.orders.')->group(function () {
    Route::get('/', [AdminOrderController::class, 'index'])->name('index');
    Route::get('/{order}', [AdminOrderController::class, 'show'])->name('show');
    Route::put('/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('update-status');
    Route::post('/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('cancel');
    Route::get('/export', [AdminOrderController::class, 'export'])->name('export');
});
```

---

## 💳 Fase 3: Sistema de Pagos

### 3.1 Extender MercadoPago para Pedidos

**Actualizar: `MercadoPagoService`**
- Nuevo método: `createPaymentForOrder(Order $order, array $paymentData)`
- Crear preferencia de pago para pedidos (no suscripciones)
- Manejar webhooks específicos para pagos de pedidos
- Métodos de pago: tarjeta, efectivo, transferencia

**Nueva tabla: `payments`**
```sql
- id (bigint, PK)
- order_id (bigint, FK orders)
- payment_method (enum: mercadopago, bank_transfer)
- amount (decimal 10,2)
- status (enum: pending, approved, rejected, refunded, cancelled)
- mp_payment_id (string, nullable) - ID en MercadoPago
- mp_preference_id (string, nullable)
- bank_transfer_reference (string, nullable) - Referencia de transferencia
- bank_transfer_proof (string, nullable) - URL de comprobante
- notes (text, nullable)
- processed_at (timestamp, nullable)
- created_at, updated_at
```

### 3.2 Transferencia Bancaria

**Nueva tabla: `bank_accounts`**
```sql
- id (bigint, PK)
- restaurant_id (bigint, FK restaurants)
- bank_name (string)
- account_type (enum: checking, savings)
- account_number (string)
- account_holder (string)
- cbu (string, nullable) - Para Argentina
- alias (string, nullable)
- is_active (boolean, default true)
- created_at, updated_at
```

**Controller: `PaymentController`**
- `processMercadoPago()` - Procesar pago con MercadoPago
- `processBankTransfer()` - Registrar transferencia bancaria
- `uploadTransferProof()` - Subir comprobante de transferencia
- `verifyPayment()` - Verificar estado del pago

**Vista: `checkout.blade.php`**
- Selección de método de pago
- Formulario MercadoPago (integración SDK)
- Formulario Transferencia Bancaria (mostrar datos bancarios)
- Upload de comprobante (si transferencia)
- Confirmación de pedido

### 3.3 Webhooks de MercadoPago

**Actualizar: `MercadoPagoWebhookController`**
- Manejar eventos de pago de pedidos (no solo suscripciones)
- Actualizar estado del pedido según estado del pago
- Notificaciones al restaurante y cliente

---

## 🚚 Fase 4: Cálculo de Tiempo de Entrega

### 4.1 Integración con API de Mapas

**Opción 1: Google Maps Distance Matrix API**
- Calcular distancia y tiempo entre restaurante y dirección del cliente
- Considerar tráfico en tiempo real
- Costo: ~$5 por 1000 requests

**Opción 2: OpenRouteService (Gratis)**
- Alternativa open source
- Límite de requests diarios
- Menos preciso que Google Maps

**Nueva tabla: `delivery_zones`**
```sql
- id (bigint, PK)
- restaurant_id (bigint, FK restaurants)
- name (string) - Ej: "Centro", "Cordón"
- polygon_coordinates (json) - Coordenadas del polígono
- base_delivery_time (integer) - Minutos base
- delivery_fee (decimal 10,2)
- is_active (boolean)
- created_at, updated_at
```

**Service: `DeliveryTimeService`**
- `calculateDeliveryTime(Restaurant $restaurant, string $address, float $lat, float $lng): int`
- Usar API de mapas para calcular tiempo
- Agregar tiempo de preparación (configurable por restaurante)
- Retornar tiempo estimado en minutos

**Configuración en `Restaurant`**
- `preparation_time` (integer) - Minutos de preparación
- `delivery_enabled` (boolean)
- `delivery_radius` (integer) - Radio en km

### 4.2 Modelo y Lógica

**Actualizar Model: `Order`**
- Método `calculateEstimatedDeliveryTime()` - Calcular al crear pedido
- Método `updateActualDeliveryTime()` - Registrar cuando se entrega

**Vista: Checkout**
- Mostrar tiempo estimado antes de confirmar
- Actualizar dinámicamente según dirección ingresada

---

## 📊 Fase 5: Dashboard de Pedidos y Pagos

### 5.1 Vista de Pedidos (Admin)

**Controller: `AdminOrderController`**
- `index()` - Lista con filtros (estado, fecha, restaurante)
- `show()` - Detalles completos del pedido
- `updateStatus()` - Cambiar estado con notificaciones
- `export()` - Exportar a CSV/Excel

**Vista: `admin/orders/index.blade.php`**
- Tabla de pedidos con:
  - Número de pedido
  - Cliente
  - Restaurante
  - Total
  - Estado
  - Método de pago
  - Fecha
  - Acciones (ver, cambiar estado, cancelar)
- Filtros: estado, fecha, restaurante
- Búsqueda por número de pedido o cliente
- Paginación
- Vista de cards para móvil

**Vista: `admin/orders/show.blade.php`**
- Información del cliente
- Items del pedido
- Dirección de entrega
- Tiempo estimado de entrega
- Historial de estados
- Información de pago
- Botones de acción (confirmar, preparar, entregar, cancelar)

### 5.2 Vista de Pagos (Admin)

**Controller: `AdminPaymentController`**
- `index()` - Lista de pagos
- `show()` - Detalles del pago
- `verifyTransfer()` - Verificar transferencia bancaria
- `refund()` - Reembolsar pago

**Vista: `admin/payments/index.blade.php`**
- Tabla de pagos con:
  - Pedido asociado
  - Método de pago
  - Monto
  - Estado
  - Fecha
  - Acciones
- Filtros por método, estado, fecha
- Vista de transferencias pendientes de verificación

### 5.3 Notificaciones

**Sistema de notificaciones:**
- Email al cliente cuando cambia el estado del pedido
- Notificación en dashboard cuando hay nuevo pedido
- SMS/WhatsApp (opcional) para estados críticos

---

## 📈 Fase 6: Panel de Reportes de Ventas

### 6.1 Base de Datos

**Nueva tabla: `sales_reports` (opcional - para cache)**
```sql
- id (bigint, PK)
- restaurant_id (bigint, FK restaurants)
- report_date (date)
- total_orders (integer)
- total_revenue (decimal 10,2)
- total_items_sold (integer)
- average_order_value (decimal 10,2)
- payment_methods_breakdown (json)
- top_products (json)
- created_at, updated_at
```

### 6.2 Controlador y Lógica

**Controller: `SalesReportController`**
- `index()` - Vista principal de reportes
- `getData()` - API para obtener datos (AJAX)
- `export()` - Exportar reporte

**Service: `SalesReportService`**
- `getRevenueByPeriod($startDate, $endDate, $restaurantId = null)`
- `getOrdersByPeriod($startDate, $endDate, $restaurantId = null)`
- `getTopProducts($startDate, $endDate, $restaurantId = null, $limit = 10)`
- `getPaymentMethodsBreakdown($startDate, $endDate, $restaurantId = null)`
- `getAverageOrderValue($startDate, $endDate, $restaurantId = null)`
- `getOrdersByStatus($startDate, $endDate, $restaurantId = null)`
- `getDeliveryTimeStats($startDate, $endDate, $restaurantId = null)`

### 6.3 Vistas

**Vista: `admin/reports/sales.blade.php`**
- Selector de período (hoy, semana, mes, personalizado)
- Selector de restaurante (si tiene múltiples)
- Métricas principales:
  - Total de ventas
  - Total de pedidos
  - Ticket promedio
  - Tiempo promedio de entrega
- Gráficos (usar Chart.js o similar):
  - Ventas por día/semana/mes
  - Pedidos por estado
  - Métodos de pago
  - Productos más vendidos
- Tabla de pedidos del período
- Botón de exportar (CSV/PDF)

---

## 🔄 Flujo Completo del Usuario

### Flujo de Compra:
1. Cliente escanea QR → Ve menú público
2. Cliente agrega productos al carrito
3. Cliente hace clic en "Ver carrito"
4. Cliente revisa items y totales
5. Cliente hace clic en "Continuar al checkout"
6. Cliente ingresa datos:
   - Nombre, teléfono, email
   - Dirección de entrega
   - Método de pago (MercadoPago o Transferencia)
7. Sistema calcula tiempo de entrega estimado
8. Si MercadoPago: Redirige a checkout de MP
9. Si Transferencia: Muestra datos bancarios y formulario de comprobante
10. Cliente completa pago
11. Sistema crea pedido con estado "pending"
12. Cliente recibe confirmación con número de pedido
13. Restaurante recibe notificación de nuevo pedido
14. Restaurante confirma pedido → Estado "confirmed"
15. Restaurante prepara pedido → Estado "preparing"
16. Pedido listo → Estado "ready"
17. Repartidor sale → Estado "out_for_delivery"
18. Pedido entregado → Estado "delivered"

---

## 📝 Tareas por Fase

### Fase 1: Carrito (1-2 semanas)
- [ ] Crear migración `cart_items`
- [ ] Crear modelo `CartItem`
- [ ] Crear `CartController`
- [ ] Crear rutas del carrito
- [ ] Crear vista del carrito
- [ ] Agregar botón "Agregar al carrito" en menú público
- [ ] JavaScript para manejo del carrito
- [ ] Tests del carrito

### Fase 2: Pedidos (2-3 semanas)
- [ ] Crear migraciones: `orders`, `order_items`, `order_status_history`
- [ ] Crear modelos: `Order`, `OrderItem`, `OrderStatusHistory`
- [ ] Crear `OrderController` (público)
- [ ] Crear `AdminOrderController`
- [ ] Crear rutas de pedidos
- [ ] Crear vista de checkout
- [ ] Crear vista de seguimiento de pedido
- [ ] Crear vistas admin de pedidos
- [ ] Sistema de notificaciones de pedidos
- [ ] Tests de pedidos

### Fase 3: Pagos (2 semanas)
- [ ] Crear migración `payments`
- [ ] Crear migración `bank_accounts`
- [ ] Crear modelo `Payment`
- [ ] Crear modelo `BankAccount`
- [ ] Extender `MercadoPagoService` para pedidos
- [ ] Crear `PaymentController`
- [ ] Actualizar webhooks de MercadoPago
- [ ] Crear vista de selección de método de pago
- [ ] Integrar SDK de MercadoPago en frontend
- [ ] Crear formulario de transferencia bancaria
- [ ] Sistema de verificación de transferencias
- [ ] Tests de pagos

### Fase 4: Tiempo de Entrega (1 semana)
- [ ] Crear migración `delivery_zones`
- [ ] Crear `DeliveryTimeService`
- [ ] Integrar API de mapas (Google Maps o OpenRouteService)
- [ ] Agregar campos a `restaurants` (preparation_time, delivery_enabled, etc.)
- [ ] Crear vista de configuración de zonas de entrega
- [ ] Actualizar checkout para calcular tiempo
- [ ] Mostrar tiempo estimado en checkout y confirmación
- [ ] Tests de cálculo de tiempo

### Fase 5: Dashboard Pedidos/Pagos (1-2 semanas)
- [ ] Crear vistas admin de pedidos
- [ ] Crear vistas admin de pagos
- [ ] Implementar filtros y búsqueda
- [ ] Implementar cambio de estado con historial
- [ ] Sistema de notificaciones en tiempo real (opcional: WebSockets)
- [ ] Exportación de datos
- [ ] Tests de dashboard

### Fase 6: Reportes (1-2 semanas)
- [ ] Crear `SalesReportService`
- [ ] Crear `SalesReportController`
- [ ] Crear vista de reportes
- [ ] Integrar librería de gráficos (Chart.js)
- [ ] Implementar exportación de reportes
- [ ] Tests de reportes

---

## 🛠️ Tecnologías y Dependencias Adicionales

### Backend:
- **Google Maps API** o **OpenRouteService** - Para cálculo de tiempo de entrega
- **MercadoPago SDK** - Ya existe, extender para pagos de pedidos
- **Excel/CSV Export** - `maatwebsite/excel` para exportar reportes
- **PDF Generation** - `barryvdh/laravel-dompdf` para reportes PDF

### Frontend:
- **Chart.js** o **ApexCharts** - Para gráficos de reportes
- **MercadoPago SDK JS** - Para checkout de MercadoPago
- **Google Maps JavaScript API** - Para selección de dirección en checkout

---

## 🔐 Consideraciones de Seguridad

1. **Validación de carrito**: Verificar que productos existan y estén disponibles
2. **Validación de pedidos**: Prevenir manipulación de precios
3. **Webhooks**: Verificar firma de MercadoPago
4. **Transferencias bancarias**: Verificar comprobantes antes de confirmar pedido
5. **Datos sensibles**: No exponer información bancaria completa
6. **Rate limiting**: Limitar creación de pedidos por IP/usuario

---

## 📱 Mejoras Futuras (Post-MVP)

1. **App móvil** para repartidores
2. **Notificaciones push** en tiempo real
3. **Chat** entre cliente y restaurante
4. **Sistema de calificaciones** y reviews
5. **Programación de pedidos** (pedidos para más tarde)
6. **Cupones de descuento** por pedido (no solo suscripciones)
7. **Programa de fidelidad** (puntos por compra)
8. **Integración con servicios de delivery** (Rappi, PedidosYa, etc.)

---

## 📅 Estimación de Tiempo Total

- **Fase 1 (Carrito)**: 1-2 semanas
- **Fase 2 (Pedidos)**: 2-3 semanas
- **Fase 3 (Pagos)**: 2 semanas
- **Fase 4 (Tiempo entrega)**: 1 semana
- **Fase 5 (Dashboard)**: 1-2 semanas
- **Fase 6 (Reportes)**: 1-2 semanas

**Total estimado: 8-12 semanas** (2-3 meses)

---

## ✅ Checklist de Implementación

### Prioridad Alta (MVP):
- [x] Plan de modificaciones
- [ ] Sistema de carrito
- [ ] Sistema de pedidos básico
- [ ] Checkout con MercadoPago
- [ ] Dashboard de pedidos
- [ ] Notificaciones básicas

### Prioridad Media:
- [ ] Transferencia bancaria
- [ ] Cálculo de tiempo de entrega
- [ ] Dashboard de pagos
- [ ] Panel de reportes básico

### Prioridad Baja:
- [ ] Reportes avanzados
- [ ] Exportación de datos
- [ ] Optimizaciones de performance
- [ ] Mejoras de UX

---

**Última actualización**: Febrero 2026
**Versión del plan**: 1.0
