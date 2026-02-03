@extends('layouts.admin')

@section('title', 'Pedido #' . $order->order_number . ' - Sushi Burger Experience')
@section('page_title', 'Detalles del Pedido')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <!-- Order Info -->
        <div class="glass-card p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <h3 class="h4 mb-1">Pedido #{{ $order->order_number }}</h3>
                    <span class="badge bg-{{ $order->status === 'delivered' ? 'success' : ($order->status === 'cancelled' ? 'danger' : 'warning') }} mb-2">
                        {{ $order->status_label }}
                    </span>
                </div>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-cartify-secondary btn-sm">
                    ← Volver
                </a>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <strong>Cliente:</strong><br>
                    {{ $order->customer_name }}<br>
                    <small class="text-muted">{{ $order->customer_email }}</small><br>
                    <small class="text-muted">{{ $order->customer_phone }}</small>
                </div>
                <div class="col-md-6">
                    <strong>Dirección:</strong><br>
                    {{ $order->customer_address }}
                    @if($order->delivery_notes)
                        <br><small class="text-muted">{{ $order->delivery_notes }}</small>
                    @endif
                </div>
            </div>

            <!-- Order Items -->
            <h4 class="mb-3">Items del Pedido</h4>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>{{ $item->product_name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->product_price, 0, ',', '.') }}</td>
                                <td>${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td><strong>${{ number_format($order->subtotal, 0, ',', '.') }}</strong></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end">Envío:</td>
                            <td>${{ number_format($order->delivery_fee, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td><strong>${{ number_format($order->total, 0, ',', '.') }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Status History -->
        @if($order->statusHistory->count() > 0)
        <div class="glass-card p-4">
            <h4 class="mb-3">Historial de Estados</h4>
            <div class="timeline">
                @foreach($order->statusHistory as $history)
                    <div class="mb-3 pb-3 border-bottom border-secondary">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>{{ $history->new_status }}</strong>
                                @if($history->changedBy)
                                    <br><small class="text-muted">Por: {{ $history->changedBy->name }}</small>
                                @endif
                                @if($history->notes)
                                    <br><small class="text-muted">{{ $history->notes }}</small>
                                @endif
                            </div>
                            <small class="text-muted">{{ $history->created_at ? $history->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <!-- Actions -->
        <div class="glass-card p-4 mb-4" style="border-left: 3px solid var(--color-primary);">
            <h4 class="mb-4" style="font-family: var(--font-heading); font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="settings" style="width: 20px; height: 20px; color: var(--color-primary);"></i>
                Acciones
            </h4>

            @if($order->canBeCancelled())
                <div class="mb-4 pb-4" style="border-bottom: 1px solid var(--color-border);">
                    <form action="{{ route('admin.orders.cancel', $order) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" style="font-weight: 600; margin-bottom: 8px; display: block;">
                                Motivo de cancelación
                            </label>
                            <textarea name="reason" class="form-control-cartify" rows="4"
                                      placeholder="Ingresa el motivo de la cancelación..."
                                      style="width: 100%; min-height: 100px; resize: vertical; box-sizing: border-box;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100"
                                style="padding: 12px; font-weight: 600; border-radius: 12px;"
                                onclick="return confirm('¿Estás seguro de cancelar este pedido?')">
                            <i data-lucide="x-circle" style="width: 18px; height: 18px; vertical-align: middle; margin-right: 5px;"></i>
                            Cancelar Pedido
                        </button>
                    </form>
                </div>
            @endif

            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 8px; display: block;">
                        Cambiar Estado
                    </label>
                    <select name="status" class="form-control-cartify" required style="width: 100%; padding: 12px; box-sizing: border-box;">
                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                        <option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>Preparando</option>
                        <option value="ready" {{ $order->status === 'ready' ? 'selected' : '' }}>Listo</option>
                        <option value="out_for_delivery" {{ $order->status === 'out_for_delivery' ? 'selected' : '' }}>En camino</option>
                        <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Entregado</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="form-label" style="font-weight: 600; margin-bottom: 8px; display: block;">
                        Notas (opcional)
                    </label>
                    <textarea name="notes" class="form-control-cartify" rows="4"
                              placeholder="Agrega notas sobre el cambio de estado..."
                              style="width: 100%; min-height: 100px; resize: vertical; box-sizing: border-box;"></textarea>
                </div>
                <button type="submit" class="btn btn-cartify-primary w-100"
                        style="padding: 14px; font-weight: 600; font-size: 1rem; border-radius: 12px;">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px; vertical-align: middle; margin-right: 5px;"></i>
                    Actualizar Estado
                </button>
            </form>
        </div>

        <!-- Payment Info -->
        <div class="glass-card p-4 mb-4" style="border-left: 3px solid rgba(76, 175, 80, 0.5);">
            <h4 class="mb-4" style="font-family: var(--font-heading); font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="credit-card" style="width: 20px; height: 20px; color: #4caf50;"></i>
                Información de Pago
            </h4>
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 5px; font-weight: 500;">Método de Pago</div>
                    <div style="font-weight: 600; font-size: 1rem;">
                        {{ $order->payment_method === 'mercadopago' ? 'MercadoPago' : 'Transferencia Bancaria' }}
                    </div>
                </div>
                <div>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 5px; font-weight: 500;">Estado</div>
                    <span class="badge bg-{{ $order->payment_status === 'paid' ? 'success' : ($order->payment_status === 'failed' ? 'danger' : 'secondary') }}"
                          style="font-size: 0.9rem; padding: 8px 14px; border-radius: 8px;">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                @if($order->payment_id)
                <div>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 5px; font-weight: 500;">ID de Pago</div>
                    <div style="font-family: monospace; font-size: 0.9rem; color: var(--color-text); background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 8px; word-break: break-all;">
                        {{ $order->payment_id }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Tracking Link -->
        <div class="glass-card p-4" style="border-left: 3px solid rgba(124, 58, 237, 0.5);">
            <h4 class="mb-3" style="font-family: var(--font-heading); font-weight: 700; display: flex; align-items: center; gap: 10px;">
                <i data-lucide="link" style="width: 20px; height: 20px; color: var(--color-primary);"></i>
                Seguimiento Público
            </h4>
            <p class="small" style="color: var(--color-text-muted); margin-bottom: 15px; line-height: 1.5;">
                Comparte este enlace con el cliente para que pueda seguir el estado de su pedido:
            </p>
            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                <input type="text" class="form-control-cartify"
                       value="{{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}"
                       id="trackingLink" readonly
                       style="flex: 1; font-size: 0.85rem; padding: 10px 12px; font-family: monospace;">
                <button class="btn btn-cartify-secondary" onclick="copyTrackingLink()"
                        style="padding: 10px 14px; border-radius: 12px; flex-shrink: 0;"
                        title="Copiar enlace">
                    <i data-lucide="copy" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <a href="{{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}"
               target="_blank"
               class="btn btn-cartify-secondary w-100"
               style="padding: 10px; font-size: 0.9rem; border-radius: 12px;">
                <i data-lucide="external-link" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 5px;"></i>
                Ver Vista Pública
            </a>
        </div>
    </div>
</div>

@section('styles')
<style>
    .glass-card {
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        border-color: rgba(255, 255, 255, 0.1) !important;
    }

    /* Asegurar que los textareas y selects ocupen el 100% del ancho */
    .glass-card textarea.form-control-cartify,
    .glass-card select.form-control-cartify,
    .glass-card .form-control-cartify {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    textarea.form-control-cartify {
        line-height: 1.5;
    }

    .input-group {
        display: flex;
        align-items: stretch;
    }

    .input-group .form-control-cartify {
        border-top-right-radius: 0;
        border-bottom-right-radius: 0;
    }

    .input-group .btn {
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        border-left: none;
    }

    /* Asegurar que los contenedores de formulario no limiten el ancho */
    .glass-card form,
    .glass-card .mb-3,
    .glass-card .mb-4 {
        width: 100%;
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        function copyTrackingLink(event) {
            const input = document.getElementById('trackingLink');
            input.select();
            input.setSelectionRange(0, 99999); // Para móviles

            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;

            const copyText = async () => {
                try {
                    await navigator.clipboard.writeText(input.value);
                    btn.innerHTML = '<i data-lucide="check" style="width: 18px; height: 18px;"></i>';
                    btn.style.background = 'rgba(76, 175, 80, 0.2)';
                    btn.style.borderColor = '#4caf50';
                    lucide.createIcons();

                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.style.background = '';
                        btn.style.borderColor = '';
                        lucide.createIcons();
                    }, 2000);
                } catch (err) {
                    // Fallback para navegadores antiguos
                    document.execCommand('copy');
                    btn.innerHTML = '<i data-lucide="check" style="width: 18px; height: 18px;"></i>';
                    btn.style.background = 'rgba(76, 175, 80, 0.2)';
                    btn.style.borderColor = '#4caf50';
                    lucide.createIcons();

                    setTimeout(() => {
                        btn.innerHTML = originalHTML;
                        btn.style.background = '';
                        btn.style.borderColor = '';
                        lucide.createIcons();
                    }, 2000);
                }
            };

            copyText();
        }

        const copyBtn = document.querySelector('button[onclick="copyTrackingLink()"]');
        if (copyBtn) {
            copyBtn.removeAttribute('onclick');
            copyBtn.addEventListener('click', copyTrackingLink);
        }
    });
</script>
@endsection
@endsection
