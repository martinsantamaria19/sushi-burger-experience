<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>¡Pedido Confirmado! - Sushi Burger Experience</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --color-bg: #07090e;
            --color-card-bg: #121620;
            --color-border: rgba(255, 255, 255, 0.05);
            --color-text: #ffffff;
            --color-text-muted: #94a3b8;
            --color-primary: #7c3aed;
            --color-success: #4caf50;
            --color-btn-bg: #7c3aed;
            --color-btn-text: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
        }

        .success-container {
            max-width: 700px;
            width: 100%;
        }

        .success-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            padding: 50px 40px;
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: rgba(76, 175, 80, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease;
        }

        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon i {
            width: 60px;
            height: 60px;
            color: var(--color-success);
        }

        .success-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--color-success) 0%, #66bb6a 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
            margin: 20px 0;
            padding: 15px;
            background: rgba(124, 58, 237, 0.1);
            border-radius: 12px;
            display: inline-block;
        }

        .info-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: var(--color-text-muted);
            font-weight: 500;
        }

        .info-value {
            font-weight: 600;
            text-align: right;
        }

        .order-items {
            margin-top: 20px;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .btn-primary-custom {
            background: var(--color-btn-bg);
            color: var(--color-btn-text);
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
            color: var(--color-btn-text);
        }

        .btn-secondary-custom {
            background: transparent;
            color: var(--color-text);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            margin: 10px;
        }

        .btn-secondary-custom:hover {
            background: var(--color-card-bg);
            color: var(--color-text);
        }

        .tracking-link {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            word-break: break-all;
            font-size: 0.9rem;
            color: var(--color-text-muted);
        }

        .copy-btn {
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 0.85rem;
            cursor: pointer;
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .copy-btn:hover {
            background: #6d28d9;
        }

        @media (max-width: 768px) {
            .success-card {
                padding: 30px 20px;
            }

            .success-title {
                font-size: 2rem;
            }

            .order-number {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i data-lucide="check-circle"></i>
        </div>
        <h1 class="success-title">¡Pedido Confirmado!</h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); margin-bottom: 30px;">
            Tu pedido ha sido recibido y está siendo procesado
        </p>
        <div class="order-number">
            #{{ $order->order_number }}
        </div>
    </div>

    <div class="info-card">
        <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 20px; font-size: 1.3rem;">Resumen del Pedido</h3>

        <div class="info-row">
            <span class="info-label">Restaurante:</span>
            <span class="info-value">{{ $order->restaurant->name }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span class="info-value">{{ $order->customer_name }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Teléfono:</span>
            <span class="info-value">{{ $order->customer_phone }}</span>
        </div>

        @if($order->customer_email)
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">{{ $order->customer_email }}</span>
        </div>
        @endif

        <div class="info-row">
            <span class="info-label">Dirección:</span>
            <span class="info-value" style="text-align: right; max-width: 60%;">{{ $order->customer_address }}</span>
        </div>

        @if($order->delivery_notes)
        <div class="info-row">
            <span class="info-label">Instrucciones:</span>
            <span class="info-value" style="text-align: right; max-width: 60%;">{{ $order->delivery_notes }}</span>
        </div>
        @endif

        <div class="order-items">
            <h4 style="margin-top: 25px; margin-bottom: 15px; font-size: 1.1rem;">Items del Pedido:</h4>
            @foreach($order->items as $item)
                <div class="order-item">
                    <div>
                        <strong>{{ $item->product_name }}</strong>
                        <div style="color: var(--color-text-muted); font-size: 0.9rem;">Cantidad: {{ $item->quantity }}</div>
                    </div>
                    <div style="font-weight: 600;">
                        ${{ number_format($item->subtotal, 0, ',', '.') }}
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 25px; padding-top: 20px; border-top: 2px solid var(--color-border);">
            <div class="info-row">
                <span class="info-label">Subtotal:</span>
                <span class="info-value">${{ number_format($order->subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->delivery_fee > 0)
            <div class="info-row">
                <span class="info-label">Envío:</span>
                <span class="info-value">${{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="info-row" style="font-size: 1.2rem; font-weight: 700; margin-top: 10px;">
                <span>Total:</span>
                <span style="color: var(--color-primary);">${{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="info-row" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--color-border);">
            <span class="info-label">Método de pago:</span>
            <span class="info-value">
                {{ $order->payment_method === 'mercadopago' ? 'MercadoPago' : 'Transferencia Bancaria' }}
            </span>
        </div>

        <div class="info-row">
            <span class="info-label">Estado:</span>
            <span class="info-value" style="color: var(--color-success);">Pendiente de confirmación</span>
        </div>
    </div>

    <div class="info-card">
        <h3 style="font-family: 'Outfit', sans-serif; margin-bottom: 15px; font-size: 1.2rem;">Seguimiento de tu Pedido</h3>
        <p style="color: var(--color-text-muted); margin-bottom: 15px;">
            Guarda este enlace para seguir el estado de tu pedido:
        </p>
        <div class="tracking-link" id="trackingLink">
            {{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}
        </div>
        <button class="copy-btn" onclick="copyTrackingLink()">
            <i data-lucide="copy" style="width: 14px; height: 14px; margin-right: 5px;"></i>
            Copiar enlace
        </button>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}" class="btn-primary-custom">
            Ver Seguimiento del Pedido
        </a>
        <a href="{{ route('public.menu', $order->restaurant->slug) }}" class="btn-secondary-custom">
            Volver al Menú
        </a>
    </div>

    @if($order->payment_method === 'bank_transfer')
    <div class="info-card" style="background: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.3);">
        <h4 style="color: #ffc107; margin-bottom: 15px;">
            <i data-lucide="info" style="width: 20px; height: 20px; vertical-align: middle;"></i>
            Pago por Transferencia Bancaria
        </h4>
        <p style="color: var(--color-text-muted); margin-bottom: 10px;">
            Te contactaremos pronto con los datos bancarios para realizar la transferencia.
        </p>
        <p style="color: var(--color-text-muted); font-size: 0.9rem;">
            Una vez confirmado el pago, tu pedido será procesado.
        </p>
    </div>
    @endif
</div>

<!-- JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();

    function copyTrackingLink() {
        const link = document.getElementById('trackingLink').textContent;
        navigator.clipboard.writeText(link).then(() => {
            const btn = event.target.closest('.copy-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i data-lucide="check" style="width: 14px; height: 14px; margin-right: 5px;"></i> Copiado!';
            btn.style.background = '#4caf50';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                lucide.createIcons();
            }, 2000);
        }).catch(err => {
            alert('No se pudo copiar el enlace. Por favor, cópialo manualmente.');
        });
    }
</script>

</body>
</html>
