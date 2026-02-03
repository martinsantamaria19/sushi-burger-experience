<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Seguimiento de Pedido - {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --color-bg: #07090e;
            --color-card-bg: #121620;
            --color-border: rgba(255, 255, 255, 0.1);
            --color-text: #ffffff;
            --color-text-muted: #cbd5e1;
            --color-primary: #7c3aed;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            padding: 40px 20px;
            min-height: 100vh;
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
        }

        .card h3 {
            color: var(--color-text);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.3rem;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: var(--color-text);
            min-width: 140px;
        }

        .detail-value {
            color: var(--color-text);
            text-align: right;
            flex: 1;
            font-weight: 500;
        }

        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 15px;
        }

        .status-pending {
            background: rgba(255, 193, 7, 0.25);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.4);
        }

        .status-confirmed {
            background: rgba(0, 123, 255, 0.25);
            color: #60a5fa;
            border: 1px solid rgba(0, 123, 255, 0.4);
        }

        .status-preparing {
            background: rgba(255, 152, 0, 0.25);
            color: #fb923c;
            border: 1px solid rgba(255, 152, 0, 0.4);
        }

        .status-ready {
            background: rgba(76, 175, 80, 0.25);
            color: #4ade80;
            border: 1px solid rgba(76, 175, 80, 0.4);
        }

        .status-out_for_delivery {
            background: rgba(33, 150, 243, 0.25);
            color: #60a5fa;
            border: 1px solid rgba(33, 150, 243, 0.4);
        }

        .status-delivered {
            background: rgba(76, 175, 80, 0.25);
            color: #4ade80;
            border: 1px solid rgba(76, 175, 80, 0.4);
        }

        .status-cancelled {
            background: rgba(244, 67, 54, 0.25);
            color: #f87171;
            border: 1px solid rgba(244, 67, 54, 0.4);
        }

        .order-header {
            text-align: center;
            padding-bottom: 20px;
        }

        .order-header h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--color-text);
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-name {
            font-weight: 600;
            color: var(--color-text);
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 700;
            color: var(--color-primary);
            font-size: 1.1rem;
        }

        .history-item {
            padding: 15px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .history-item:last-child {
            border-bottom: none;
        }

        .history-status {
            font-weight: 600;
            color: var(--color-text);
            margin-bottom: 5px;
            font-size: 1rem;
        }

        .history-date {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .btn-back {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 24px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
            color: white;
        }

        .btn-retry-payment {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 28px;
            background: var(--color-primary);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .btn-retry-payment:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
            color: white;
            background: #6d28d9;
        }

        @media (max-width: 768px) {
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .detail-value {
                text-align: left;
            }

            .order-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card order-header">
        <h1>Pedido #{{ $order->order_number }}</h1>
        <span class="status-badge status-{{ $order->status }}">{{ $order->status_label }}</span>
    </div>

    <div class="card">
        <h3>Detalles del Pedido</h3>
        <div class="detail-row">
            <span class="detail-label">Restaurante:</span>
            <span class="detail-value">{{ $order->restaurant->name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Cliente:</span>
            <span class="detail-value">{{ $order->customer_name }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Teléfono:</span>
            <span class="detail-value">{{ $order->customer_phone }}</span>
        </div>
        @if($order->customer_email)
        <div class="detail-row">
            <span class="detail-label">Email:</span>
            <span class="detail-value">{{ $order->customer_email }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Dirección:</span>
            <span class="detail-value" style="text-align: right;">{{ $order->customer_address }}</span>
        </div>
        @if($order->delivery_notes)
        <div class="detail-row">
            <span class="detail-label">Instrucciones:</span>
            <span class="detail-value" style="text-align: right;">{{ $order->delivery_notes }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span class="detail-label">Total:</span>
            <span class="detail-value" style="color: var(--color-primary); font-weight: 700; font-size: 1.2rem;">${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Método de pago:</span>
            <span class="detail-value">{{ $order->payment_method === 'mercadopago' ? 'MercadoPago' : 'Transferencia Bancaria' }}</span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Estado de pago:</span>
            <span class="detail-value">
                <span class="status-badge status-{{ $order->payment_status === 'paid' ? 'delivered' : ($order->payment_status === 'failed' ? 'cancelled' : 'pending') }}" style="font-size: 0.85rem; padding: 6px 12px;">
                    {{ $order->payment_status_label }}
                </span>
            </span>
        </div>

        @if($order->payment_method === 'mercadopago' && $order->payment_status === 'pending')
        <div class="detail-row" style="border-top: 1px solid var(--color-border); margin-top: 15px; padding-top: 20px;">
            <div style="width: 100%; text-align: center;">
                <a href="{{ route('orders.payment', ['order' => $order->id, 'token' => $order->tracking_token]) }}"
                   class="btn-retry-payment"
                   style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; background: var(--color-primary); color: white; text-decoration: none; border-radius: 12px; font-weight: 600; transition: all 0.3s ease;">
                    <i data-lucide="refresh-cw" style="width: 20px; height: 20px;"></i>
                    Reintentar Pago
                </a>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <h3>Items del Pedido</h3>
        @foreach($order->items as $item)
            <div class="item-row">
                <div>
                    <div class="item-name">{{ $item->product_name }}</div>
                    <div class="item-quantity">Cantidad: {{ $item->quantity }}</div>
                </div>
                <div class="item-price">${{ number_format($item->subtotal, 0, ',', '.') }}</div>
            </div>
        @endforeach
    </div>

    @if($order->statusHistory->count() > 0)
    <div class="card">
        <h3>Historial de Estados</h3>
        @foreach($order->statusHistory as $history)
            <div class="history-item">
                <div class="history-status">{{ ucfirst($history->new_status) }}</div>
                <div class="history-date">
                    {{ $history->created_at ? $history->created_at->format('d/m/Y H:i') : 'N/A' }}
                    @if($history->changedBy)
                        - Por: {{ $history->changedBy->name }}
                    @endif
                    @if($history->notes)
                        <br><span style="font-style: italic;">{{ $history->notes }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <div style="text-align: center;">
        <a href="{{ route('public.menu', $order->restaurant->slug) }}" class="btn-back">
            Volver al Menú
        </a>
    </div>
</div>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    // Initialize icons when page loads
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
</body>
</html>
