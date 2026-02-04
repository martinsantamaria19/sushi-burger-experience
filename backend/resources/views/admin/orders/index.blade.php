@extends('layouts.admin')

@section('title', 'Pedidos - Sushi Burger Experience')
@section('page_title', 'Pedidos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Gestión de Pedidos</h2>
</div>

<!-- Filters -->
<div class="glass-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="filters-form">
        <div class="filters-row">
            <div class="filter-group">
                <label class="form-label">Estado</label>
                <select name="status" class="form-control-cartify">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                    <option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>Preparando</option>
                    <option value="ready" {{ request('status') === 'ready' ? 'selected' : '' }}>Listo</option>
                    <option value="out_for_delivery" {{ request('status') === 'out_for_delivery' ? 'selected' : '' }}>En camino</option>
                    <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Entregado</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label">Restaurante</label>
                <select name="restaurant_id" class="form-control-cartify">
                    <option value="">Todos</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" {{ request('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                            {{ $restaurant->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label">Estado de pago</label>
                <select name="payment_status" class="form-control-cartify">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
                    <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Fallido</option>
                </select>
            </div>
            <div class="filter-group filter-group-search">
                <label class="form-label">Buscar</label>
                <input type="text" name="search" class="form-control-cartify" placeholder="Número, nombre, teléfono..." value="{{ request('search') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-cartify-primary">
                    <i data-lucide="filter"></i>
                    Filtrar
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-cartify-secondary">
                    <i data-lucide="x"></i>
                    Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Orders Table -->
<div class="glass-card p-0">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Restaurante</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Pago</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr class="order-row {{ !$order->isViewed() ? 'order-unviewed' : '' }}" data-order-id="{{ $order->id }}">
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                @if(!$order->isViewed())
                                    <span class="badge bg-danger pulse-badge" title="Nuevo pedido">NUEVO</span>
                                @endif
                                <strong>{{ $order->order_number }}</strong>
                            </div>
                        </td>
                        <td>
                            <div>{{ $order->customer_name }}</div>
                            <small class="text-muted">{{ $order->customer_phone }}</small>
                        </td>
                        <td>{{ $order->restaurant->name }}</td>
                        <td>${{ number_format($order->total, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }}">
                                {{ $order->status_label }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'secondary') }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-cartify-primary">
                                    Ver
                                </a>
                                @if($order->status === 'pending')
                                    <button class="btn btn-sm btn-success quick-status-btn" data-order-id="{{ $order->id }}" data-status="confirmed" title="Confirmar">
                                        <i data-lucide="check"></i>
                                    </button>
                                @endif
                                @if($order->status === 'confirmed')
                                    <button class="btn btn-sm btn-warning quick-status-btn" data-order-id="{{ $order->id }}" data-status="preparing" title="Preparando">
                                        <i data-lucide="clock"></i>
                                    </button>
                                @endif
                                @if($order->status === 'preparing')
                                    <button class="btn btn-sm btn-info quick-status-btn" data-order-id="{{ $order->id }}" data-status="ready" title="Listo">
                                        <i data-lucide="check-circle-2"></i>
                                    </button>
                                @endif
                                @if($order->status === 'ready')
                                    <button class="btn btn-sm btn-primary quick-status-btn" data-order-id="{{ $order->id }}" data-status="out_for_delivery" title="En camino">
                                        <i data-lucide="truck"></i>
                                    </button>
                                @endif
                                @if($order->status === 'out_for_delivery')
                                    <button class="btn btn-sm btn-success quick-status-btn" data-order-id="{{ $order->id }}" data-status="delivered" title="Entregado">
                                        <i data-lucide="package-check"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            No se encontraron pedidos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
@if($orders->hasPages())
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endif

<!-- Notification Badge -->
<div id="new-orders-badge" class="position-fixed top-0 end-0 m-4" style="z-index: 1050; display: none;">
    <div class="alert alert-danger d-flex align-items-center gap-2 shadow-lg">
        <i data-lucide="bell" class="icon"></i>
        <span id="new-orders-count">0</span> pedido(s) nuevo(s)
    </div>
</div>

@endsection

@section('styles')
<style>
    /* Filters Styles - Horizontal layout, 100% width */
    .filters-form {
        width: 100%;
    }

    .filters-row {
        display: flex;
        flex-direction: row;
        gap: 16px;
        align-items: flex-end;
        width: 100%;
        flex-wrap: nowrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        flex: 1 1 0;
        min-width: 0;
    }

    .filter-group label {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--color-text-muted);
        margin-bottom: 0;
        white-space: nowrap;
    }

    .filter-group .form-control-cartify {
        width: 100%;
        padding: 10px 14px;
        font-size: 0.9rem;
        box-sizing: border-box;
    }

    .filter-group-search {
        flex: 2 1 0;
    }

    .filter-actions {
        display: flex;
        gap: 10px;
        align-items: flex-end;
        flex-shrink: 0;
        margin-left: auto;
    }

    .filter-actions .btn {
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
    }

    .filter-actions .btn i {
        width: 16px;
        height: 16px;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .filters-row {
            flex-wrap: wrap;
        }
        .filter-group {
            flex: 1 1 calc(50% - 8px);
        }
        .filter-group-search {
            flex: 1 1 100%;
        }
        .filter-actions {
            flex: 1 1 100%;
            margin-left: 0;
            justify-content: flex-start;
        }
    }

    @media (max-width: 768px) {
        .filters-row {
            flex-direction: column;
        }
        .filter-group {
            flex: 1 1 100%;
        }
        .filter-group-search {
            flex: 1 1 100%;
        }
        .filter-actions {
            flex: 1 1 100%;
            width: 100%;
        }
        .filter-actions .btn {
            flex: 1;
            justify-content: center;
        }
    }

    .order-unviewed {
        background-color: rgba(220, 53, 69, 0.1) !important;
        border-left: 4px solid #dc3545;
        animation: pulse-border 2s infinite;
    }

    .pulse-badge {
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    @keyframes pulse-border {
        0%, 100% { border-left-color: #dc3545; }
        50% { border-left-color: rgba(220, 53, 69, 0.5); }
    }

    .quick-status-btn {
        min-width: 35px;
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .quick-status-btn i {
        width: 16px;
        height: 16px;
    }

    #new-orders-badge {
        animation: slideInRight 0.3s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    // Audio para notificaciones
    let notificationSound = null;

    // Crear audio context para sonido de notificación
    function playNotificationSound() {
        try {
            // Crear un sonido simple usando Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800; // Frecuencia del tono
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        } catch (e) {
            console.log('No se pudo reproducir sonido:', e);
        }
    }

    let lastCheckTime = new Date().toISOString();
    let pollingInterval = null;
    let isPolling = false;

    // Función para obtener pedidos nuevos
    async function checkNewOrders() {
        if (isPolling) return;
        isPolling = true;

        try {
            const response = await fetch('{{ route("admin.orders.new.count") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Error al verificar pedidos');
            }

            const data = await response.json();
            const count = data.count || 0;

            // Mostrar badge si hay pedidos nuevos
            const badge = document.getElementById('new-orders-badge');
            const countElement = document.getElementById('new-orders-count');

            if (count > 0) {
                countElement.textContent = count;
                badge.style.display = 'block';

                // Reproducir sonido solo si es la primera vez que detectamos pedidos nuevos
                if (count > 0 && !badge.dataset.played) {
                    playNotificationSound();
                    badge.dataset.played = 'true';
                }
            } else {
                badge.style.display = 'none';
                badge.dataset.played = '';
            }

            // Actualizar filas de la tabla si hay cambios
            if (count > 0) {
                updateOrdersList();
            }
        } catch (error) {
            console.error('Error al verificar pedidos nuevos:', error);
        } finally {
            isPolling = false;
        }
    }

    // Función para actualizar la lista de pedidos
    async function updateOrdersList() {
        try {
            const response = await fetch('{{ route("admin.orders.new.list") }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) return;

            const data = await response.json();
            const orders = data.orders || [];

            // Actualizar filas existentes y agregar nuevas
            orders.forEach(order => {
                const row = document.querySelector(`tr[data-order-id="${order.id}"]`);
                if (row && !row.classList.contains('order-unviewed')) {
                    row.classList.add('order-unviewed');
                    const td = row.querySelector('td:first-child');
                    if (td && !td.querySelector('.badge')) {
                        const badge = document.createElement('span');
                        badge.className = 'badge bg-danger pulse-badge';
                        badge.textContent = 'NUEVO';
                        badge.title = 'Nuevo pedido';
                        td.insertBefore(badge, td.firstChild);
                    }
                }
            });
        } catch (error) {
            console.error('Error al actualizar lista:', error);
        }
    }

    // Botones rápidos de cambio de estado
    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains('quick-status-btn')) {
            const btn = e.target;
            const orderId = btn.dataset.orderId;
            const newStatus = btn.dataset.status;

            btn.disabled = true;
            // Store original content
            btn.dataset.originalContent = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

            try {
                const response = await fetch(`/admin/orders/${orderId}/quick-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ status: newStatus }),
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success) {
                    // Recargar la página para ver los cambios
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'No se pudo actualizar el estado'));
                    btn.disabled = false;
                    // Restore original content
                    if (btn.dataset.originalContent) {
                        btn.innerHTML = btn.dataset.originalContent;
                        if (typeof lucide !== 'undefined') {
                            lucide.createIcons();
                        }
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al actualizar el estado');
                btn.disabled = false;
                // Restore original content
                if (btn.dataset.originalContent) {
                    btn.innerHTML = btn.dataset.originalContent;
                    if (typeof lucide !== 'undefined') {
                        lucide.createIcons();
                    }
                }
            }
        }
    });

    // Iniciar polling cuando la página carga
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Lucide icons after DOM is ready
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();

            // Re-initialize icons after a short delay to ensure all elements are rendered
            setTimeout(() => {
                lucide.createIcons();
            }, 100);
        }

        // Verificar inmediatamente
        checkNewOrders();

        // Verificar cada 5 segundos
        pollingInterval = setInterval(checkNewOrders, 5000);

        // Pausar cuando la pestaña no está activa (opcional)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } else {
                if (!pollingInterval) {
                    checkNewOrders();
                    pollingInterval = setInterval(checkNewOrders, 5000);
                }
            }
        });
    });

    // Limpiar intervalo al salir
    window.addEventListener('beforeunload', function() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });
</script>
@endsection
