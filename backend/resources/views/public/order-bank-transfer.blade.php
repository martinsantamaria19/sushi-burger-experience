<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Transferencia Bancaria - Sushi Burger Experience</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg: #07090e;
            --color-card-bg: #121620;
            --color-border: rgba(255, 255, 255, 0.05);
            --color-text: #ffffff;
            --color-text-muted: #94a3b8;
            --color-primary: #7c3aed;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            margin: 0;
            padding: 20px;
        }

        .transfer-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .transfer-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .transfer-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .bank-account-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .bank-account-card h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 20px;
            color: var(--color-primary);
        }

        .account-detail {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .account-detail:last-child {
            border-bottom: none;
        }

        .account-detail-label {
            color: var(--color-text-muted);
            font-weight: 500;
        }

        .account-detail-value {
            font-weight: 600;
            font-family: monospace;
        }

        .transfer-form {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            color: var(--color-text);
            padding: 12px 16px;
            border-radius: 12px;
            width: 100%;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-primary);
        }

        .btn-submit {
            width: 100%;
            background: var(--color-primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .back-link {
            display: inline-block;
            color: var(--color-text-muted);
            text-decoration: none;
            margin-top: 20px;
        }

        .back-link:hover {
            color: var(--color-primary);
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }
    </style>
</head>
<body>

<div class="transfer-container">
    <div class="transfer-header">
        <h1>Transferencia Bancaria</h1>
        <p style="color: var(--color-text-muted);">Pedido #{{ $order->order_number }}</p>
    </div>

    @if($bankAccounts->isEmpty())
        <div class="alert alert-info">
            No hay cuentas bancarias configuradas para este restaurante. Por favor, contacta al restaurante directamente.
        </div>
    @else
        @foreach($bankAccounts as $bankAccount)
            <div class="bank-account-card">
                <h2>{{ $bankAccount->bank_name }}</h2>
                <div class="account-detail">
                    <span class="account-detail-label">Titular</span>
                    <span class="account-detail-value">{{ $bankAccount->account_holder }}</span>
                </div>
                <div class="account-detail">
                    <span class="account-detail-label">Tipo de Cuenta</span>
                    <span class="account-detail-value">{{ $bankAccount->account_type_label }}</span>
                </div>
                <div class="account-detail">
                    <span class="account-detail-label">Número de Cuenta</span>
                    <span class="account-detail-value">{{ $bankAccount->account_number }}</span>
                </div>
                @if($bankAccount->instructions)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--color-border);">
                    <p style="color: var(--color-text-muted); font-size: 0.9rem;">{{ $bankAccount->instructions }}</p>
                </div>
                @endif
            </div>
        @endforeach

        <div class="transfer-form">
            <div class="alert alert-info" style="margin-bottom: 24px;">
                <strong>Importante:</strong> Realizá la transferencia a una de las cuentas de arriba. Luego hacé clic en el botón para avisar al restaurante.
            </div>

            <form id="transferForm">
                @csrf
                <input type="hidden" name="token" value="{{ $order->tracking_token }}">
                <button type="submit" class="btn-submit" id="submitBtn">
                    Confirmar transferencia
                </button>
            </form>
        </div>
    @endif

    <a href="{{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}" class="back-link">
        ← Volver al pedido
    </a>
</div>

<script>
    document.getElementById('transferForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        const formData = new FormData(this);

        try {
            const response = await fetch('/payments/{{ $order->id }}/bank-transfer', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = '{{ route("orders.success", ["order" => $order->id, "token" => $order->tracking_token]) }}';
            } else {
                alert(data.message || 'No se pudo confirmar. Intentá de nuevo.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Confirmar transferencia';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión. Intentá de nuevo.');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar transferencia';
        }
    });
</script>

</body>
</html>
