<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Error al Procesar Pedido - Sushi Burger Experience</title>

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
            --color-error: #f44336;
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

        .failure-container {
            max-width: 600px;
            width: 100%;
        }

        .failure-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            padding: 50px 40px;
            text-align: center;
            margin-bottom: 30px;
        }

        .failure-icon {
            width: 100px;
            height: 100px;
            background: rgba(244, 67, 54, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .failure-icon i {
            width: 60px;
            height: 60px;
            color: var(--color-error);
        }

        .failure-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            color: var(--color-error);
        }

        .error-message {
            background: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            color: var(--color-text);
            font-size: 1.1rem;
        }

        .info-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
            text-align: left;
        }

        .info-card h3 {
            font-size: 1.2rem;
            margin-bottom: 15px;
            color: var(--color-text);
        }

        .info-card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .info-card li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: var(--color-text-muted);
        }

        .info-card li:before {
            content: "•";
            position: absolute;
            left: 10px;
            color: var(--color-primary);
            font-weight: bold;
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

        @media (max-width: 768px) {
            .failure-card {
                padding: 30px 20px;
            }

            .failure-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="failure-container">
    <div class="failure-card">
        <div class="failure-icon">
            <i data-lucide="x-circle"></i>
        </div>
        <h1 class="failure-title">Error al Procesar Pedido</h1>
        <p style="font-size: 1.1rem; color: var(--color-text-muted); margin-bottom: 20px;">
            Lo sentimos, no pudimos procesar tu pedido en este momento
        </p>
        <div class="error-message">
            {{ $error }}
        </div>
    </div>

    <div class="info-card">
        <h3>¿Qué puedes hacer?</h3>
        <ul>
            <li>Verifica que todos los campos estén completos</li>
            <li>Intenta nuevamente en unos momentos</li>
            <li>Si el problema persiste, contacta al restaurante directamente</li>
            <li>Tu carrito se ha guardado, puedes continuar desde donde lo dejaste</li>
        </ul>
    </div>

    <div class="info-card" style="background: rgba(124, 58, 237, 0.1); border-color: rgba(124, 58, 237, 0.3);">
        <h3 style="color: var(--color-primary);">
            <i data-lucide="info" style="width: 20px; height: 20px; vertical-align: middle;"></i>
            Información Importante
        </h3>
        <p style="color: var(--color-text-muted); margin: 0;">
            No se ha realizado ningún cargo. Tu información está segura y puedes intentar nuevamente cuando estés listo.
        </p>
    </div>

    <div style="text-align: center; margin-top: 30px;">
        <a href="{{ route('cart.index') }}" class="btn-primary-custom">
            Volver al Carrito
        </a>
        @if(isset($input['customer_address']))
            <a href="{{ route('orders.checkout') }}" class="btn-secondary-custom" onclick="restoreFormData()">
                Intentar Nuevamente
            </a>
        @endif
    </div>
</div>

<!-- JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();

    // Guardar datos del formulario en localStorage para restaurarlos
    @if(isset($input))
        const formData = @json($input);
        localStorage.setItem('checkoutFormData', JSON.stringify(formData));
    @endif

    function restoreFormData() {
        const savedData = localStorage.getItem('checkoutFormData');
        if (savedData) {
            // Los datos se restaurarán automáticamente cuando Laravel los pase a la vista
            localStorage.removeItem('checkoutFormData');
        }
    }
</script>

</body>
</html>
