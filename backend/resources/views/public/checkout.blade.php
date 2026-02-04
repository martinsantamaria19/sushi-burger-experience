<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Checkout - Sushi Burger Experience</title>

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
            --color-btn-bg: #7c3aed;
            --color-btn-text: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            padding-bottom: max(100px, env(safe-area-inset-bottom, 0px) + 100px);
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
        }

        .checkout-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .checkout-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .checkout-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .checkout-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .checkout-form {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
        }

        .order-summary {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--color-text);
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
            background: var(--color-bg);
            color: var(--color-text);
        }

        .form-control::placeholder {
            color: var(--color-text-muted);
        }

        .payment-method {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }

        .payment-option {
            flex: 1;
            padding: 20px;
            border: 2px solid var(--color-border);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .payment-option:hover {
            border-color: var(--color-primary);
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option input[type="radio"]:checked + label {
            color: var(--color-primary);
        }

        .payment-option.selected {
            border-color: var(--color-primary);
            background: rgba(124, 58, 237, 0.1);
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-item-name {
            font-weight: 600;
        }

        .order-item-quantity {
            color: var(--color-text-muted);
            font-size: 0.9rem;
        }

        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            padding-top: 15px;
            border-top: 1px solid var(--color-border);
            margin-top: 15px;
        }

        .btn-submit {
            width: 100%;
            background: var(--color-btn-bg);
            color: var(--color-btn-text);
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-weight: 600;
            font-size: 1.1rem;
            margin-top: 20px;
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

        @media (max-width: 968px) {
            .checkout-content {
                grid-template-columns: 1fr;
            }

            .order-summary {
                position: relative;
                top: 0;
            }
        }

        /* Desplegable de Google Places por encima del contenido */
        .pac-container {
            z-index: 1100 !important;
        }
        /* Ocultar desplegable después de elegir dirección para ver el tiempo estimado */
        body.pac-dropdown-hidden .pac-container {
            display: none !important;
            visibility: hidden !important;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <div class="checkout-header">
        <h1>Finalizar Pedido</h1>
        <a href="{{ route('cart.index') }}" style="color: var(--color-text-muted); text-decoration: none;">
            ← Volver al carrito
        </a>
    </div>

    <form action="{{ route('orders.store') }}" method="POST" id="checkoutForm" data-restaurant-id="{{ $restaurant->id }}" data-estimate-url="{{ route('api.delivery-estimate') }}">
        @csrf

        <div class="checkout-content">
            <div class="checkout-form">
                <h2 style="margin-bottom: 30px;">Información de Entrega</h2>

                <div class="form-group">
                    <label class="form-label">Nombre completo *</label>
                    <input type="text" name="customer_name" class="form-control"
                           value="{{ old('customer_name', $user->name ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="customer_email" class="form-control"
                           value="{{ old('customer_email', $user->email ?? '') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Teléfono *</label>
                    <input type="tel" name="customer_phone" class="form-control"
                           value="{{ old('customer_phone') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Dirección de entrega *</label>
                    <input type="text" name="customer_address" id="customer_address" class="form-control"
                           placeholder="Escribí y elegí una sugerencia para una mejor estimación"
                           value="{{ old('customer_address') }}" required autocomplete="off">
                    <input type="hidden" name="estimated_delivery_time" id="estimated_delivery_time" value="{{ old('estimated_delivery_time') }}">
                    <input type="hidden" name="delivery_address_lat" id="delivery_address_lat" value="{{ old('delivery_address_lat') }}">
                    <input type="hidden" name="delivery_address_lng" id="delivery_address_lng" value="{{ old('delivery_address_lng') }}">
                    <p id="delivery-estimate-message" class="mt-2 mb-0" style="font-size: 0.9rem; color: var(--color-text-muted); min-height: 1.4em;"></p>
                </div>

                <div class="form-group">
                    <label class="form-label">Instrucciones de entrega</label>
                    <textarea name="delivery_notes" class="form-control" rows="2"
                              placeholder="Ej: Timbre 3, depto 2B">{{ old('delivery_notes') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Notas adicionales</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Alguna nota especial para tu pedido">{{ old('notes') }}</textarea>
                </div>

                <h2 style="margin-top: 40px; margin-bottom: 20px;">Método de Pago</h2>

                <div class="payment-method">
                    <div class="payment-option" onclick="selectPayment('mercadopago')">
                        <input type="radio" name="payment_method" value="mercadopago" id="mp"
                               {{ old('payment_method', 'mercadopago') === 'mercadopago' ? 'checked' : '' }} required>
                        <label for="mp" style="cursor: pointer;">
                            <i data-lucide="credit-card" style="width: 32px; height: 32px; margin-bottom: 10px;"></i>
                            <div style="font-weight: 600;">MercadoPago</div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 5px;">
                                Tarjeta, efectivo, etc.
                            </div>
                        </label>
                    </div>

                    <div class="payment-option" onclick="selectPayment('bank_transfer')">
                        <input type="radio" name="payment_method" value="bank_transfer" id="transfer"
                               {{ old('payment_method') === 'bank_transfer' ? 'checked' : '' }}>
                        <label for="transfer" style="cursor: pointer;">
                            <i data-lucide="banknote" style="width: 32px; height: 32px; margin-bottom: 10px;"></i>
                            <div style="font-weight: 600;">Transferencia</div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-top: 5px;">
                                Bancaria
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="order-summary">
                <h2 style="margin-bottom: 20px;">Resumen del Pedido</h2>

                <div style="margin-bottom: 20px;">
                    <strong>{{ $restaurant->name }}</strong>
                </div>

                @foreach($cartItems as $item)
                    <div class="order-item">
                        <div>
                            <div class="order-item-name">{{ $item->product->name }}</div>
                            <div class="order-item-quantity">Cantidad: {{ $item->quantity }}</div>
                        </div>
                        <div style="font-weight: 600;">
                            ${{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach

                <div class="order-item">
                    <div>Subtotal</div>
                    <div>${{ number_format($total, 0, ',', '.') }}</div>
                </div>

                <div class="order-item">
                    <div>Envío</div>
                    <div>Se calculará</div>
                </div>

                <div class="order-item summary-total">
                    <div>Total</div>
                    <div>${{ number_format($total, 0, ',', '.') }}</div>
                </div>

                <button type="submit" class="btn-submit" id="submitBtn">
                    Confirmar Pedido
                </button>
            </div>
        </div>
    </form>
</div>

<!-- JS -->
<script src="https://unpkg.com/lucide@latest"></script>
@if(!empty($googleMapsApiKey))
<script>
    window.initCheckoutPlaces = function() {
        const input = document.getElementById('customer_address');
        if (!input) return;
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: ['address'],
            fields: ['formatted_address', 'geometry']
        });
        function hidePacDropdown() {
            document.body.classList.add('pac-dropdown-hidden');
        }
        function showPacDropdown() {
            document.body.classList.remove('pac-dropdown-hidden');
        }
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            if (!place.geometry || !place.geometry.location) return;
            input.value = place.formatted_address || place.name || '';
            const latEl = document.getElementById('delivery_address_lat');
            const lngEl = document.getElementById('delivery_address_lng');
            if (latEl) latEl.value = place.geometry.location.lat();
            if (lngEl) lngEl.value = place.geometry.location.lng();
            hidePacDropdown();
            input.blur();
            input.dispatchEvent(new Event('input', { bubbles: true }));
        });
        input.addEventListener('focus', showPacDropdown);
        input.addEventListener('blur', function() {
            setTimeout(hidePacDropdown, 150);
        });
    };
    (function() {
        const script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initCheckoutPlaces';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    })();
</script>
@endif
<script>
    lucide.createIcons();

    function selectPayment(method) {
        document.querySelectorAll('.payment-option').forEach(opt => {
            opt.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        document.querySelector(`input[value="${method}"]`).checked = true;
    }

    // Initialize selected payment
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        if (radio.checked) {
            radio.closest('.payment-option').classList.add('selected');
        }
    });

    // Estimación de tiempo de entrega al ingresar dirección
    const form = document.getElementById('checkoutForm');
    const addressEl = document.getElementById('customer_address');
    const messageEl = document.getElementById('delivery-estimate-message');
    const estimatedInput = document.getElementById('estimated_delivery_time');
    const restaurantId = form.dataset.restaurantId;
    const estimateUrl = form.dataset.estimateUrl;

    let estimateTimeout = null;
    addressEl.addEventListener('input', function() {
        messageEl.textContent = '';
        estimatedInput.value = '';
        clearTimeout(estimateTimeout);
        const address = (addressEl.value || '').trim();
        if (address.length < 10) return;
        estimateTimeout = setTimeout(function() {
            messageEl.textContent = 'Calculando tiempo de entrega...';
            fetch(estimateUrl + '?restaurant_id=' + encodeURIComponent(restaurantId) + '&destination=' + encodeURIComponent(address))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.ok && data.total_minutes) {
                        messageEl.textContent = data.message || ('Tiempo estimado: ~' + data.total_minutes + ' min');
                        messageEl.style.color = 'var(--color-primary)';
                        estimatedInput.value = data.total_minutes;
                    } else {
                        messageEl.textContent = data.message || 'No se pudo calcular. Verificá la dirección.';
                        messageEl.style.color = 'var(--color-text-muted)';
                        estimatedInput.value = '';
                    }
                })
                .catch(function() {
                    messageEl.textContent = 'No se pudo calcular el tiempo.';
                    messageEl.style.color = 'var(--color-text-muted)';
                    estimatedInput.value = '';
                });
        }, 600);
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Procesando...';
    });
</script>

</body>
</html>
