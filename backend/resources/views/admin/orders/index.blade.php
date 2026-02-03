@extends('layouts.admin')

@section('title', 'Pedidos - Sushi Burger Experience')
@section('page_title', 'Pedidos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Gestión de Pedidos</h2>
</div>

<!-- Filters -->
<div class="glass-card p-4 mb-4">
    <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3">
        <div class="col-md-3">
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
        <div class="col-md-3">
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
        <div class="col-md-3">
            <label class="form-label">Estado de pago</label>
            <select name="payment_status" class="form-control-cartify">
                <option value="">Todos</option>
                <option value="pending" {{ request('payment_status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                <option value="paid" {{ request('payment_status') === 'paid' ? 'selected' : '' }}>Pagado</option>
                <option value="failed" {{ request('payment_status') === 'failed' ? 'selected' : '' }}>Fallido</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Buscar</label>
            <input type="text" name="search" class="form-control-cartify" placeholder="Número, nombre, teléfono..." value="{{ request('search') }}">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-cartify-primary">Filtrar</button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-cartify-secondary">Limpiar</a>
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
                    <tr>
                        <td><strong>{{ $order->order_number }}</strong></td>
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
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-cartify-primary">
                                Ver
                            </a>
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
@endsection
