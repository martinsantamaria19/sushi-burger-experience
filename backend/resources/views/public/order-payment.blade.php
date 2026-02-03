<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Pagar Pedido - Sushi Burger Experience</title>

    <!-- MercadoPago SDK -->
    <script src="https://sdk.mercadopago.com/js/v2"></script>

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

        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .payment-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .payment-header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .order-info {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .order-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .order-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .order-detail:last-child {
            border-bottom: none;
            font-weight: 700;
            font-size: 1.2rem;
            padding-top: 15px;
        }

        #mercadopago-bricks {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 20px;
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
    </style>
</head>
<body>

<div class="payment-container">
    <div class="payment-header">
        <h1>Completar Pago</h1>
        <p style="color: var(--color-text-muted);">Pedido #{{ $order->order_number }}</p>
    </div>

    <div class="order-info">
        <h2>Resumen del Pedido</h2>
        <div class="order-detail">
            <span>Subtotal</span>
            <span>${{ number_format($order->subtotal, 0, ',', '.') }}</span>
        </div>
        <div class="order-detail">
            <span>Envío</span>
            <span>${{ number_format($order->delivery_fee, 0, ',', '.') }}</span>
        </div>
        <div class="order-detail">
            <span>Total</span>
            <span>${{ number_format($order->total, 0, ',', '.') }}</span>
        </div>
    </div>

    <div id="mercadopago-bricks"></div>

    <div id="error-message" style="display: none; background: var(--color-card-bg); border: 1px solid #ef4444; border-radius: 16px; padding: 20px; margin-bottom: 20px; color: #ef4444;">
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
            <i data-lucide="alert-circle" style="width: 24px; height: 24px;"></i>
            <strong>Ha ocurrido un error</strong>
        </div>
        <p id="error-text" style="margin: 0; color: var(--color-text-muted);">Por favor, vuelve a intentarlo más tarde.</p>
    </div>

    <a href="{{ route('orders.show', ['order' => $order->id, 'token' => $order->tracking_token]) }}" class="back-link">
        ← Volver al pedido
    </a>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();

    // Validate required data
    const publicKey = '{{ $publicKey }}';
    const preferenceId = '{{ $payment->mp_preference_id ?? '' }}';
    // Ensure orderTotal is a number, not a string
    const orderTotal = {{ $order->total }};

    // Debug logging
    console.log('Payment page initialization:', {
        publicKey: publicKey ? publicKey.substring(0, 20) + '...' : 'EMPTY',
        preferenceId: preferenceId || 'EMPTY',
        orderTotal: orderTotal,
        orderTotalType: typeof orderTotal,
        orderNumber: '{{ $order->order_number }}'
    });

    function showError(message) {
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');
        const bricksDiv = document.getElementById('mercadopago-bricks');

        errorText.textContent = message || 'Por favor, vuelve a intentarlo más tarde.';
        errorDiv.style.display = 'block';
        bricksDiv.style.display = 'none';
        lucide.createIcons();
    }

    // Validate data before initializing
    if (!publicKey || publicKey.trim() === '') {
        showError('Error: No se encontró la clave pública de MercadoPago. Por favor, contacta al restaurante.');
    } else if (!preferenceId || preferenceId.trim() === '') {
        showError('Error: No se pudo crear la preferencia de pago. Por favor, intenta nuevamente.');
    } else if (!orderTotal || orderTotal <= 0) {
        showError('Error: El monto del pedido no es válido.');
    } else {
        // Initialize MercadoPago
        try {
            // Get environment from backend (sandbox or production)
            const environment = '{{ $mpAccount->environment ?? "sandbox" }}';

            const mp = new MercadoPago(publicKey, {
                locale: 'es-UY',
                // Ensure SDK uses correct environment
                ...(environment === 'sandbox' ? {} : {})
            });

            console.log('MercadoPago SDK initialized', {
                environment: environment,
                publicKeyPrefix: publicKey.substring(0, 20) + '...'
            });

            const bricksBuilder = mp.bricks();

            const renderPaymentBrick = async (bricksBuilder) => {
                try {
                    // Convert to number explicitly - ensure it's a valid number
                    const amountValue = typeof orderTotal === 'number' ? orderTotal : parseFloat(String(orderTotal));

                    console.log('Creating Payment Brick with:', {
                        preferenceId: preferenceId,
                        orderTotal: orderTotal,
                        orderTotalType: typeof orderTotal,
                        amountValue: amountValue,
                        amountValueType: typeof amountValue,
                        isNaN: isNaN(amountValue)
                    });

                    if (isNaN(amountValue) || !isFinite(amountValue) || amountValue <= 0) {
                        throw new Error('El monto del pedido no es válido: ' + orderTotal + ' (tipo: ' + typeof orderTotal + ')');
                    }

                    // Payment Brick initialization
                    // MercadoPago Payment Brick requires BOTH preferenceId and amount
                    const initData = {
                        preferenceId: preferenceId,
                    };

                    // Explicitly set amount as a number
                    initData.amount = amountValue;

                    console.log('Initialization data:', initData);

                    const settings = {
                        initialization: initData,
                        customization: {
                            visual: {
                                style: {
                                    theme: 'dark',
                                    customVariables: {
                                        formBackgroundColor: '#121620',
                                        baseColor: '#7c3aed',
                                        baseColorFirstVariant: '#6d28d9',
                                        baseColorSecondVariant: '#5b21b6',
                                        errorColor: '#ef4444',
                                        successColor: '#10b981',
                                    }
                                }
                            },
                            paymentMethods: {
                                creditCard: 'all',
                                debitCard: 'all',
                                ticket: 'all',
                                mercadoPago: ['wallet_purchase'],
                            }
                        },
                        callbacks: {
                            onReady: () => {
                                console.log('Payment Brick ready');
                                // Hide any error messages if brick loads successfully
                                document.getElementById('error-message').style.display = 'none';
                            },
                            onSubmit: async (cardFormData) => {
                                console.log('Payment Brick onSubmit called', {
                                    cardFormData: cardFormData,
                                    preferenceId: preferenceId,
                                    timestamp: new Date().toISOString()
                                });

                                // Show loading state
                                const submitButton = document.querySelector('[data-testid="submit-button"]');
                                if (submitButton) {
                                    submitButton.disabled = true;
                                    submitButton.textContent = 'Procesando...';
                                }

                                // Cuando usas preferenceId, el Payment Brick procesa el pago automáticamente
                                // y redirige según las back_urls configuradas en la preferencia
                                // El callback onSubmit solo se usa para notificar que el usuario hizo clic
                                // El brick maneja el procesamiento y la redirección automáticamente

                                // No retornamos nada explícitamente - el brick maneja el flujo completo
                                // Si retornamos una Promise, debe resolverse inmediatamente para no bloquear
                                return Promise.resolve();
                            },
                            onError: (error) => {
                                console.error('Payment Brick error:', error);

                                // Re-enable submit button if exists
                                const submitButton = document.querySelector('[data-testid="submit-button"]');
                                if (submitButton) {
                                    submitButton.disabled = false;
                                    submitButton.textContent = 'Pagar';
                                }

                                // Show detailed error message
                                let errorMessage = 'Error al procesar el pago. ';

                                if (error.cause) {
                                    errorMessage += `Causa: ${error.cause}. `;

                                    // Provide specific guidance for common errors
                                    if (error.cause === 'payment_brick_initialization_failed' ||
                                        error.cause === 'missing_amount_property') {
                                        errorMessage += 'Por favor, recarga la página e intenta nuevamente.';
                                    } else if (error.cause.includes('403') || error.cause.includes('forbidden')) {
                                        errorMessage += 'Error de autorización. Verifica que las credenciales de MercadoPago sean correctas y que la aplicación tenga los permisos necesarios.';
                                    } else if (error.cause.includes('401') || error.cause.includes('unauthorized')) {
                                        errorMessage += 'Credenciales inválidas. El restaurante debe verificar su configuración de MercadoPago.';
                                    }
                                }

                                if (error.message && !errorMessage.includes(error.message)) {
                                    errorMessage += error.message;
                                } else if (typeof error === 'string') {
                                    errorMessage += error;
                                } else if (!error.cause) {
                                    errorMessage += 'Por favor, verifica tus datos e intenta nuevamente.';
                                }

                                // Log full error for debugging
                                console.error('Full error object:', JSON.stringify(error, null, 2));

                                showError(errorMessage);
                            }
                        }
                    };

                    window.paymentBrickController = await bricksBuilder.create('payment', 'mercadopago-bricks', settings);
                    console.log('Payment Brick created successfully');
                } catch (error) {
                    console.error('Error creating Payment Brick:', error);
                    showError('Error al inicializar el formulario de pago: ' + (error.message || JSON.stringify(error) || 'Por favor, recarga la página e intenta nuevamente.'));
                }
            };

            renderPaymentBrick(bricksBuilder).catch(error => {
                console.error('Unhandled error:', error);
                showError('Error inesperado: ' + (error.message || 'Por favor, recarga la página e intenta nuevamente.'));
            });
        } catch (error) {
            console.error('Error initializing MercadoPago SDK:', error);
            showError('Error al cargar MercadoPago: ' + (error.message || 'Por favor, verifica tu conexión e intenta nuevamente.'));
        }
    }
</script>

</body>
</html>
