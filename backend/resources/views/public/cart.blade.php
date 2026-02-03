<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Carrito de Compras - Sushi Burger Experience</title>

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

        .cart-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .cart-header {
            margin-bottom: 40px;
            text-align: center;
        }

        .cart-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-cart i {
            width: 80px;
            height: 80px;
            color: var(--color-text-muted);
            margin-bottom: 20px;
        }

        .empty-cart h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .empty-cart p {
            color: var(--color-text-muted);
            margin-bottom: 30px;
        }

        .restaurant-group {
            margin-bottom: 40px;
        }

        .restaurant-header {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .restaurant-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }

        .cart-item {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 16px;
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            object-fit: cover;
            flex-shrink: 0;
        }

        .cart-item-content {
            flex-grow: 1;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .cart-item-price {
            color: var(--color-primary);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .cart-item-actions {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 8px 12px;
        }

        .quantity-btn {
            background: transparent;
            border: none;
            color: var(--color-text);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .quantity-btn:hover {
            background: var(--color-card-bg);
        }

        .quantity-value {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
        }

        .remove-btn {
            background: transparent;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .remove-btn:hover {
            background: rgba(220, 53, 69, 0.1);
        }

        .cart-summary {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 16px;
            padding: 30px;
            margin-top: 40px;
            position: sticky;
            bottom: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .summary-row.total {
            font-size: 1.5rem;
            font-weight: 700;
            padding-top: 15px;
            border-top: 1px solid var(--color-border);
            margin-top: 15px;
        }

        .btn-checkout {
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

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(124, 58, 237, 0.4);
        }

        .btn-back {
            background: transparent;
            border: 1px solid var(--color-border);
            color: var(--color-text);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-back:hover {
            background: var(--color-card-bg);
            color: var(--color-text);
        }

        @media (max-width: 768px) {
            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .cart-item-actions {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
            }

            .cart-header h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="cart-container">
    <div class="cart-header">
        <h1>Carrito de Compras</h1>
        <a href="javascript:history.back()" class="btn-back">
            <i data-lucide="arrow-left"></i>
            Volver al menú
        </a>
    </div>

    @if($cartItems->isEmpty())
        <div class="empty-cart">
            <i data-lucide="shopping-cart"></i>
            <h2>Tu carrito está vacío</h2>
            <p>Agrega productos desde el menú para comenzar tu pedido</p>
            <a href="javascript:history.back()" class="btn-checkout">Volver al menú</a>
        </div>
    @else
        @foreach($cartItems as $restaurantId => $items)
            @php
                $restaurant = $items->first()->restaurant;
            @endphp
            <div class="restaurant-group">
                <div class="restaurant-header">
                    <h2>{{ $restaurant->name }}</h2>
                </div>

                @foreach($items as $item)
                    <div class="cart-item" data-item-id="{{ $item->id }}">
                        @if($item->product->image_path)
                            <img src="{{ str_starts_with($item->product->image_path, 'http') ? $item->product->image_path : asset('storage/' . $item->product->image_path) }}"
                                 alt="{{ $item->product->name }}"
                                 class="cart-item-image">
                        @else
                            <div class="cart-item-image d-flex align-items-center justify-content-center" style="background: var(--color-bg);">
                                <i data-lucide="image" style="width: 32px; opacity: 0.3;"></i>
                            </div>
                        @endif

                        <div class="cart-item-content">
                            <div class="cart-item-name">{{ $item->product->name }}</div>
                            <div class="cart-item-price">${{ number_format($item->price, 0, ',', '.') }} c/u</div>
                        </div>

                        <div class="cart-item-actions">
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})">
                                    <i data-lucide="minus"></i>
                                </button>
                                <span class="quantity-value" id="qty-{{ $item->id }}">{{ $item->quantity }}</span>
                                <button class="quantity-btn" onclick="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})">
                                    <i data-lucide="plus"></i>
                                </button>
                            </div>
                            <button class="remove-btn" onclick="removeItem({{ $item->id }})" title="Eliminar">
                                <i data-lucide="trash-2"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach

        <div class="cart-summary">
            <div class="summary-row">
                <span>Subtotal:</span>
                <span id="subtotal">${{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <div class="summary-row">
                <span>Envío:</span>
                <span>Se calculará en el checkout</span>
            </div>
            <div class="summary-row total">
                <span>Total:</span>
                <span id="total">${{ number_format($total, 0, ',', '.') }}</span>
            </div>
            <button class="btn-checkout" onclick="proceedToCheckout()">
                Continuar al Checkout
            </button>
        </div>
    @endif
</div>

<!-- JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    lucide.createIcons();

    const SwalCart = Swal.mixin({
        background: 'var(--color-card-bg)',
        color: '#fff',
        confirmButtonColor: 'var(--color-btn-bg)',
    });

    function updateQuantity(itemId, newQuantity) {
        if (newQuantity < 1) {
            removeItem(itemId);
            return;
        }

        if (newQuantity > 99) {
            SwalCart.fire({
                icon: 'warning',
                title: 'Cantidad máxima',
                text: 'La cantidad máxima es 99 unidades'
            });
            return;
        }

        fetch(`/cart/${itemId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                quantity: newQuantity
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById(`qty-${itemId}`).textContent = newQuantity;
                document.getElementById('subtotal').textContent = '$' + data.formatted_total || data.total;
                document.getElementById('total').textContent = '$' + data.formatted_total || data.total;
            } else {
                SwalCart.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'No se pudo actualizar la cantidad'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            SwalCart.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al actualizar la cantidad'
            });
        });
    }

    function removeItem(itemId) {
        SwalCart.fire({
            title: '¿Eliminar producto?',
            text: '¿Estás seguro de que quieres eliminar este producto del carrito?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/cart/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector(`[data-item-id="${itemId}"]`).remove();

                        // Check if cart is empty
                        if (document.querySelectorAll('.cart-item').length === 0) {
                            location.reload();
                        } else {
                            document.getElementById('subtotal').textContent = '$' + (data.formatted_total || data.total);
                            document.getElementById('total').textContent = '$' + (data.formatted_total || data.total);
                        }
                    } else {
                        SwalCart.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'No se pudo eliminar el producto'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    SwalCart.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al eliminar el producto'
                    });
                });
            }
        });
    }

    function proceedToCheckout() {
        window.location.href = '{{ route("orders.checkout") }}';
    }
</script>

</body>
</html>
