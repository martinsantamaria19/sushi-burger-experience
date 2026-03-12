<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $product->name }} - {{ $restaurant->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @php
        $restaurantSettings = $restaurant->settings ?? [];
        $companySettings = $company->settings ?? [];
        $hasEcommerce = $company ? $company->hasEcommerce() : false;
        $colorBtnBg = $restaurantSettings['color_btn_bg'] ?? $companySettings['brand_color'] ?? '#7c3aed';
        $colorBtnText = $restaurantSettings['color_btn_text'] ?? '#ffffff';
        $colorProdTitle = $restaurantSettings['color_prod_title'] ?? '#ffffff';
        $colorPrice = $restaurantSettings['color_price'] ?? $companySettings['brand_color'] ?? '#7c3aed';
        $colorCardBg = $restaurantSettings['color_card_bg'] ?? $companySettings['card_bg_color'] ?? '#121620';
        $colorBg = $restaurantSettings['color_bg'] ?? $companySettings['bg_color'] ?? '#07090e';
        $colorBorder = 'rgba(255, 255, 255, 0.05)';
        $textMuted = '#94a3b8';
        function hexToRgb($hex) {
            $hex = str_replace('#', '', $hex);
            if (strlen($hex) == 3) {
                $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                $b = hexdec(substr($hex,2,1).substr($hex,2,1));
            } else {
                $r = hexdec(substr($hex,0,2));
                $g = hexdec(substr($hex,2,2));
                $b = hexdec(substr($hex,4,2));
            }
            return "$r, $g, $b";
        }
        $btnBgRgb = hexToRgb($colorBtnBg);
        $maxSelectable = (int) ($product->max_variants_selectable ?? 1);
    @endphp

    <style>
        :root {
            --color-btn-bg: {{ $colorBtnBg }};
            --color-btn-bg-rgb: {{ $btnBgRgb }};
            --color-btn-text: {{ $colorBtnText }};
            --color-prod-title: {{ $colorProdTitle }};
            --color-price: {{ $colorPrice }};
            --color-card-bg: {{ $colorCardBg }};
            --color-bg: {{ $colorBg }};
            --color-border: {{ $colorBorder }};
            --text-muted: {{ $textMuted }};
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--color-bg); color: #fff; min-height: 100vh; padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px); }
        h1, h2, h3 { font-family: 'Outfit', sans-serif; }
        .product-page { max-width: 700px; margin: 0 auto; padding: 20px; padding-bottom: max(100px, env(safe-area-inset-bottom, 0px) + 80px); }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; margin-bottom: 24px; font-size: 0.95rem; }
        .back-link:hover { color: #fff; }
        .product-hero { text-align: center; margin-bottom: 32px; }
        .product-hero-img { width: 100%; max-height: 280px; object-fit: cover; border-radius: 20px; background: rgba(255,255,255,0.03); }
        .product-hero-placeholder { width: 100%; height: 200px; border-radius: 20px; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; }
        .product-title { font-weight: 800; font-size: 1.75rem; color: var(--color-prod-title); margin: 16px 0 8px; }
        .product-desc { color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; }
        .max-variants-hint { font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px; }
        .variant-card { background: var(--color-card-bg); border: 1px solid var(--color-border); border-radius: 20px; padding: 20px; margin-bottom: 16px; display: flex; gap: 20px; align-items: flex-start; }
        .variant-card-img { width: 100px; height: 100px; border-radius: 14px; object-fit: cover; flex-shrink: 0; background: rgba(255,255,255,0.03); }
        .variant-card-placeholder { width: 100px; height: 100px; border-radius: 14px; background: rgba(255,255,255,0.05); flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
        .variant-card-body { flex: 1; min-width: 0; }
        .variant-card-name { font-weight: 700; font-size: 1.1rem; color: var(--color-prod-title); margin-bottom: 6px; }
        .variant-card-ingredients { font-size: 0.9rem; color: var(--text-muted); white-space: pre-line; margin-bottom: 10px; }
        .variant-card-price { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.25rem; color: var(--color-price); margin-bottom: 10px; }
        .variant-gluten-label { font-size: 0.9rem; display: flex; align-items: center; gap: 8px; margin-bottom: 12px; cursor: pointer; }
        .btn-add-variant-cart { padding: 10px 20px; border-radius: 12px; background: var(--color-btn-bg); border: none; color: var(--color-btn-text); font-weight: 600; cursor: pointer; transition: all 0.2s; }
        .btn-add-variant-cart:hover { filter: brightness(1.1); }
        .btn-add-variant-cart:disabled { opacity: 0.6; cursor: not-allowed; }
        .no-variants-card { background: var(--color-card-bg); border: 1px solid var(--color-border); border-radius: 20px; padding: 24px; }
        .selection-box { background: var(--color-card-bg); border: 2px solid var(--color-btn-bg); border-radius: 16px; padding: 16px; margin-bottom: 20px; }
        .selection-box h4 { font-size: 1rem; margin-bottom: 12px; color: var(--color-prod-title); }
        .selection-chip { display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); border-radius: 999px; padding: 6px 12px; margin: 4px; font-size: 0.9rem; }
        .selection-chip button { background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0; display: flex; }
        .btn-elegir { padding: 8px 16px; border-radius: 10px; background: var(--color-btn-bg); border: none; color: var(--color-btn-text); font-weight: 600; font-size: 0.9rem; cursor: pointer; }
        .btn-elegir:hover { filter: brightness(1.1); }
        .btn-elegir:disabled { opacity: 0.5; cursor: not-allowed; }
        .product-pack-price { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 1.5rem; color: var(--color-price); margin-bottom: 16px; }
        .cart-float { position: fixed; bottom: max(80px, env(safe-area-inset-bottom, 0px) + 20px); right: 20px; width: 56px; height: 56px; border-radius: 50%; background: var(--color-btn-bg); color: var(--color-btn-text); display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 8px 24px rgba(var(--color-btn-bg-rgb), 0.4); z-index: 999; }
        .cart-float:hover { color: var(--color-btn-text); transform: scale(1.05); }
        .cart-badge { position: absolute; top: -4px; right: -4px; background: #dc3545; color: #fff; border-radius: 50%; width: 22px; height: 22px; font-size: 0.75rem; font-weight: 700; display: flex; align-items: center; justify-content: center; }
        @media (max-width: 576px) {
            .variant-card { flex-direction: column; }
            .variant-card-img, .variant-card-placeholder { width: 100%; height: 160px; }
        }
    </style>
</head>
<body>

<div class="product-page">
    <a href="{{ route('public.menu', $restaurant->slug) }}" class="back-link">
        <i data-lucide="arrow-left" style="width: 20px; height: 20px;"></i>
        Volver al menú
    </a>

    <div class="product-hero">
        @if($product->image_path)
            <img src="{{ str_starts_with($product->image_path, 'http') ? $product->image_path : storage_url($product->image_path) }}" alt="{{ $product->name }}" class="product-hero-img">
        @else
            <div class="product-hero-placeholder">
                <i data-lucide="image" style="width: 48px; opacity: 0.3;"></i>
            </div>
        @endif
        <h1 class="product-title">{{ $product->name }}</h1>
        @if($product->description)
            <p class="product-desc">{{ $product->description }}</p>
        @endif
        @if($product->variants->isNotEmpty())
            <p class="max-variants-hint">
                @if($maxSelectable === 1)
                    Elegí <strong>una</strong> variante para agregar al carrito.
                @else
                    Podés elegir hasta <strong>{{ $maxSelectable }}</strong> variantes para agregar al carrito.
                @endif
            </p>
        @endif
    </div>

    @if($hasEcommerce)
        @if($product->variants->isNotEmpty())
            {{-- Precio del pack (siempre uno solo por producto) --}}
            <div class="product-pack-price">${{ number_format($product->price, 0, ',', '.') }}</div>

            @if($maxSelectable > 1)
                {{-- Multi-variante: elegir N variantes y un solo Agregar al carrito --}}
                <div class="selection-box" id="selectionBox" style="display: none;">
                    <h4>Tu elección (<span id="selectionCount">0</span>/{{ $maxSelectable }})</h4>
                    <div id="selectionList"></div>
                    <button type="button" class="btn-add-variant-cart mt-3 w-100" id="btnAddMultiCart" disabled data-product-id="{{ $product->id }}">
                        Agregar al carrito — ${{ number_format($product->price, 0, ',', '.') }}
                    </button>
                </div>
                @foreach($product->variants as $v)
                    <div class="variant-card" data-variant-id="{{ $v->id }}" data-variant-name="{{ e($v->name) }}" data-variant-gluten-available="{{ $v->is_gluten_free_available ? '1' : '0' }}">
                        @if($v->image_path)
                            <img src="{{ storage_url($v->image_path) }}" alt="{{ $v->name }}" class="variant-card-img">
                        @else
                            <div class="variant-card-placeholder">
                                <i data-lucide="image" style="width: 32px; opacity: 0.4;"></i>
                            </div>
                        @endif
                        <div class="variant-card-body">
                            <div class="variant-card-name">{{ $v->name }}</div>
                            @if($v->ingredients)
                                <div class="variant-card-ingredients">{{ $v->ingredients }}</div>
                            @endif
                            @if($v->is_gluten_free_available)
                                <label class="variant-gluten-label variant-gluten-multi" data-variant-id="{{ $v->id }}">
                                    <input type="checkbox" class="variant-gluten-cb-multi" data-variant-id="{{ $v->id }}"> Sin gluten
                                </label>
                            @endif
                            <button type="button" class="btn-elegir btn-elegir-variant" data-variant-id="{{ $v->id }}">Elegir esta variante</button>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- Una sola variante: botón Agregar al carrito por variante (precio = producto) --}}
                @foreach($product->variants as $v)
                    <div class="variant-card" data-variant-id="{{ $v->id }}">
                        @if($v->image_path)
                            <img src="{{ storage_url($v->image_path) }}" alt="{{ $v->name }}" class="variant-card-img">
                        @else
                            <div class="variant-card-placeholder">
                                <i data-lucide="image" style="width: 32px; opacity: 0.4;"></i>
                            </div>
                        @endif
                        <div class="variant-card-body">
                            <div class="variant-card-name">{{ $v->name }}</div>
                            @if($v->ingredients)
                                <div class="variant-card-ingredients">{{ $v->ingredients }}</div>
                            @endif
                            @if($v->is_gluten_free_available)
                                <label class="variant-gluten-label">
                                    <input type="checkbox" class="variant-gluten-cb" data-variant-id="{{ $v->id }}"> Quiero esta variante sin gluten
                                </label>
                            @endif
                            <button type="button" class="btn-add-variant-cart" data-product-id="{{ $product->id }}" data-variant-id="{{ $v->id }}">
                                Agregar al carrito
                            </button>
                        </div>
                    </div>
                @endforeach
            @endif
        @else
            <div class="no-variants-card">
                <div class="variant-card-price mb-3">${{ number_format($product->price, 0, ',', '.') }}</div>
                @if($hasEcommerce)
                    <button type="button" class="btn-add-variant-cart" data-product-id="{{ $product->id }}" data-variant-id="" data-variant-price="{{ $product->price }}">
                        Agregar al carrito
                    </button>
                @endif
            </div>
        @endif
    @else
        <div class="no-variants-card">
            <div class="variant-card-price">${{ number_format($product->price, 0, ',', '.') }}</div>
        </div>
    @endif
</div>

@if($hasEcommerce)
<a href="{{ route('cart.index') }}" class="cart-float" title="Ver carrito">
    <i data-lucide="shopping-cart" style="width: 24px; height: 24px;"></i>
    <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
</a>
@endif

<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    lucide.createIcons();

    const ProductSwal = Swal.mixin({
        background: 'var(--color-card-bg)',
        color: '#fff',
        confirmButtonColor: 'var(--color-btn-bg)',
    });

    const maxSelectable = {{ $maxSelectable }};
    const productId = {{ $product->id }};

    function updateCartBadge() {
        fetch('/cart/total', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('cartBadge');
                if (data.count > 0 && badge) {
                    badge.textContent = data.count;
                    badge.style.display = 'flex';
                } else if (badge) badge.style.display = 'none';
            });
    }

    if (maxSelectable > 1) {
        var selection = [];
        var selectionBox = document.getElementById('selectionBox');
        var selectionList = document.getElementById('selectionList');
        var selectionCount = document.getElementById('selectionCount');
        var btnAddMultiCart = document.getElementById('btnAddMultiCart');

        function renderSelection() {
            selectionCount.textContent = selection.length;
            selectionList.innerHTML = selection.map(function(s, i) {
                return '<span class="selection-chip">' + s.name + (s.gluten_free ? ' (Sin gluten)' : '') +
                    '<button type="button" onclick="removeSelection(' + i + ')" aria-label="Quitar"><i data-lucide="x" style="width:14px;height:14px;"></i></button></span>';
            }).join('');
            if (typeof lucide !== 'undefined') lucide.createIcons();
            selectionBox.style.display = selection.length > 0 ? 'block' : 'none';
            btnAddMultiCart.disabled = selection.length !== maxSelectable;
        }

        window.removeSelection = function(index) {
            selection.splice(index, 1);
            renderSelection();
        };

        document.querySelectorAll('.btn-elegir-variant').forEach(function(btn) {
            btn.addEventListener('click', function() {
                if (selection.length >= maxSelectable) return;
                var variantId = parseInt(this.dataset.variantId, 10);
                var card = this.closest('.variant-card');
                var name = card.dataset.variantName || 'Variante';
                var glutenCb = document.querySelector('.variant-gluten-cb-multi[data-variant-id="' + variantId + '"]');
                var glutenFree = glutenCb ? glutenCb.checked : false;
                selection.push({ variant_id: variantId, gluten_free: glutenFree, name: name });
                renderSelection();
            });
        });

        btnAddMultiCart.addEventListener('click', function() {
            if (selection.length !== maxSelectable) return;
            this.disabled = true;
            var origText = this.textContent;
            this.textContent = '...';
            var payload = {
                product_id: productId,
                quantity: 1,
                selections: selection.map(function(s) { return { variant_id: s.variant_id, gluten_free: s.gluten_free }; })
            };
            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateCartBadge();
                    selection = [];
                    renderSelection();
                    ProductSwal.fire({
                        icon: 'success',
                        title: '¡Agregado!',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    ProductSwal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo agregar' });
                }
            })
            .catch(() => ProductSwal.fire({ icon: 'error', title: 'Error', text: 'Error al agregar al carrito' }))
            .finally(() => {
                this.disabled = selection.length !== maxSelectable;
                this.textContent = origText;
            });
        });
    } else {
        document.querySelectorAll('.btn-add-variant-cart').forEach(function(btn) {
            if (btn.id === 'btnAddMultiCart') return;
            btn.addEventListener('click', function() {
                var pid = this.dataset.productId;
                var variantId = this.dataset.variantId ? parseInt(this.dataset.variantId, 10) : null;
                var glutenFree = variantId ? (document.querySelector('.variant-gluten-cb[data-variant-id="' + variantId + '"]')?.checked ?? false) : false;

                this.disabled = true;
                var origText = this.textContent;
                this.textContent = '...';

                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        product_id: pid,
                        product_variant_id: variantId || null,
                        quantity: 1,
                        gluten_free: glutenFree
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        updateCartBadge();
                        ProductSwal.fire({
                            icon: 'success',
                            title: '¡Agregado!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        ProductSwal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo agregar' });
                    }
                })
                .catch(() => ProductSwal.fire({ icon: 'error', title: 'Error', text: 'Error al agregar al carrito' }))
                .finally(() => {
                    this.disabled = false;
                    this.textContent = origText;
                });
            });
        });
    }

    updateCartBadge();
</script>
</body>
</html>
