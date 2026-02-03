<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $restaurant->name }} - Menú Digital</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    @php
        // Prioridad: settings del restaurante > settings de la compañía > defaults
        $restaurantSettings = $restaurant->settings ?? [];
        $companySettings = isset($company) && $company ? ($company->settings ?? []) : [];

        $colorName = $restaurantSettings['color_name'] ?? $companySettings['brand_color'] ?? '#ffffff';
        $colorAddress = $restaurantSettings['color_address'] ?? $companySettings['text_muted_color'] ?? '#94a3b8';
        $colorBtnBg = $restaurantSettings['color_btn_bg'] ?? $companySettings['brand_color'] ?? '#7c3aed';
        $colorBtnText = $restaurantSettings['color_btn_text'] ?? '#ffffff';
        $colorCatTitle = $restaurantSettings['color_cat_title'] ?? $companySettings['brand_color'] ?? '#ffffff';
        $colorProdTitle = $restaurantSettings['color_prod_title'] ?? '#ffffff';
        $colorPrice = $restaurantSettings['color_price'] ?? $companySettings['brand_color'] ?? '#7c3aed';
        $colorCardBg = $restaurantSettings['color_card_bg'] ?? $companySettings['card_bg_color'] ?? '#121620';
        $colorBg = $restaurantSettings['color_bg'] ?? $companySettings['bg_color'] ?? '#07090e';

        function hexToRgb($hex) {
            $hex = str_replace('#', '', $hex);
            if(strlen($hex) == 3) {
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
    @endphp

    <style>
        :root {
            --color-name: {{ $colorName }};
            --color-address: {{ $colorAddress }};
            --color-btn-bg: {{ $colorBtnBg }};
            --color-btn-bg-rgb: {{ $btnBgRgb }};
            --color-btn-text: {{ $colorBtnText }};
            --color-cat-title: {{ $colorCatTitle }};
            --color-prod-title: {{ $colorProdTitle }};
            --color-price: {{ $colorPrice }};
            --color-card-bg: {{ $colorCardBg }};
            --color-bg: {{ $colorBg }};

            --surface-dark-light: rgba(255, 255, 255, 0.03);
            --color-border: rgba(255, 255, 255, 0.05);
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: #fff;
            overflow-x: hidden;
            /* Padding para evitar que la barra del navegador móvil tape contenido */
            padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px);
        }

        /* Soporte para safe-area en mobile (iOS principalmente) */
        @supports (padding: max(0px)) {
            body {
                padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px);
            }
        }

        h1, h2, h3, h4, .font-heading {
            font-family: 'Outfit', sans-serif;
        }

        /* --- Custom Scrollbar --- */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg-dark); }
        ::-webkit-scrollbar-thumb { background: var(--color-primary); border-radius: 10px; }

        /* --- Hero Header --- */
        .hero-header {
            position: relative;
            padding: 80px 20px 40px;
            background: linear-gradient(to bottom, rgba(var(--color-primary-rgb), 0.15) 0%, transparent 100%);
            text-align: center;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }

        .logo-container::after {
            content: '';
            position: absolute;
            inset: -8px;
            border: 2px solid var(--color-primary);
            border-radius: 28px;
            opacity: 0.3;
            animation: pulse-border 3s infinite;
        }

        @keyframes pulse-border {
            0% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.05); opacity: 0.1; }
            100% { transform: scale(1); opacity: 0.3; }
        }

        .restaurant-logo {
            width: 110px;
            height: 110px;
            object-fit: cover;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            background: var(--color-card-bg);
            position: relative;
            z-index: 1;
        }

        .restaurant-name {
            font-weight: 800;
            font-size: 2.8rem;
            letter-spacing: -0.02em;
            margin-bottom: 8px;
            color: var(--color-name);
        }

        .restaurant-address {
            color: var(--color-address);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        /* --- Category Menu --- */
        .category-wrapper {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: rgba(7, 9, 14, 0.85);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--color-border);
            padding: 12px 0;
        }

        .category-scroll {
            display: flex;
            overflow-x: auto;
            gap: 12px;
            padding: 4px 20px;
            scrollbar-width: none;
        }

        .category-scroll::-webkit-scrollbar { display: none; }

        .btn-category {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            color: var(--color-address);
            padding: 10px 24px;
            border-radius: 100px;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-category.active, .btn-category:hover {
            background: var(--color-btn-bg);
            color: var(--color-btn-text);
            border-color: var(--color-btn-bg);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(var(--color-btn-bg-rgb), 0.3);
        }

        /* --- Grid & Cards --- */
        .menu-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            /* Padding adicional para evitar que el footer fijo tape contenido */
            padding-bottom: max(100px, env(safe-area-inset-bottom, 0px) + 100px);
        }

        .category-section {
            padding-top: 40px;
            margin-bottom: 40px;
        }

        .category-title {
            color: var(--color-cat-title);
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .category-title::after {
            content: '';
            flex-grow: 1;
            height: 1px;
            background: linear-gradient(to right, var(--color-border), transparent);
        }

        .product-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            transition: all 0.4s ease;
            position: relative;
        }

        .product-card:hover {
            border-color: rgba(var(--color-btn-bg-rgb), 0.4);
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        .product-image-box {
            position: relative;
            height: 200px;
            width: 100%;
            background: var(--surface-dark-light);
        }

        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .product-card:hover .product-img {
            transform: scale(1.1);
        }

        .product-content {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .product-name {
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 8px;
            color: var(--color-prod-title);
        }

        .product-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--color-price);
        }

        .btn-add {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--color-btn-bg);
            border: 1px solid var(--color-btn-bg);
            color: var(--color-btn-text);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-add:hover {
            filter: brightness(1.1);
        }

        /* --- Social & Floating --- */
        .floating-social {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 2000;
        }

        .social-link {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 10px 20px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .social-link:hover {
            transform: scale(1.1) rotate(5deg);
            color: #fff;
        }

        /* Responsive Mobile tweaks */
        @media (max-width: 768px) {
            .restaurant-name { font-size: 2.2rem; }
            .product-card-horizontal {
                flex-direction: row !important;
                height: auto !important;
            }
            .product-card-horizontal .product-image-box {
                width: 100px;
                height: 100px;
                flex-shrink: 0;
            }
            .product-card-horizontal .product-content {
                padding: 12px;
            }
            .product-name { font-size: 1rem; margin-bottom: 4px; }
            .product-desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 5px; }
        }

        .cartify-link {
            transition: color 0.2s ease;
        }

        .cartify-link:hover {
            color: #ffffff !important;
        }
    </style>
</head>
<body>

<!-- Hero Header -->
<header class="hero-header">
    <div class="container text-center">
        <div class="logo-container">
            @if($restaurant->logo_path)
                <img src="{{ str_starts_with($restaurant->logo_path, 'http') ? $restaurant->logo_path : asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="restaurant-logo">
            @else
                <div class="restaurant-logo d-flex align-items-center justify-content-center bg-dark">
                    <i data-lucide="utensils-cross-lines" style="width: 44px; color: var(--color-primary);"></i>
                </div>
            @endif
        </div>
        <h1 class="restaurant-name">{{ $restaurant->name }}</h1>
        <div class="restaurant-address">
            <i data-lucide="map-pin" style="width: 14px;"></i>
            {{ $restaurant->address ?: '¡Bienvenidos!' }}
        </div>
    </div>
</header>

<!-- Sticky Categories -->
<div class="category-wrapper">
    <div class="category-scroll" id="categoryScroll">
        @foreach($restaurant->categories as $category)
            <a href="#cat-{{ $category->id }}" class="btn-category {{ $loop->first ? 'active' : '' }}">
                {{ $category->name }}
            </a>
        @endforeach
    </div>
</div>

<!-- Menu Content -->
<main class="menu-container">
    @foreach($restaurant->categories as $category)
        <section class="category-section" id="cat-{{ $category->id }}">
            <h2 class="category-title">{{ $category->name }}</h2>

            <div class="row g-4">
                @foreach($category->products as $product)
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="product-card product-card-horizontal">
                            <div class="product-image-box">
                                @if($product->image_path)
                                    <img src="{{ str_starts_with($product->image_path, 'http') ? $product->image_path : asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="product-img">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center" style="opacity: 0.1;">
                                        <i data-lucide="image" style="width: 40px;"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="product-content">
                                <h3 class="product-name">{{ $product->name }}</h3>
                                <p class="product-desc">{{ $product->description }}</p>
                                <div class="product-footer">
                                    <span class="product-price">${{ number_format($product->price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endforeach

</main>

<!-- Social Floating -->
<div class="floating-social">
    @if(!empty($settings['whatsapp']))
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp']) }}" target="_blank" class="social-link" style="background: #25D366;">
            <i data-lucide="message-circle"></i>
        </a>
    @endif

    @if(!empty($settings['instagram']))
        <a href="https://instagram.com/{{ str_replace('@', '', $settings['instagram']) }}" target="_blank" class="social-link" style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);">
            <i data-lucide="instagram"></i>
        </a>
    @endif

    @if(!empty($settings['facebook']))
        <a href="https://facebook.com/{{ $settings['facebook'] }}" target="_blank" class="social-link" style="background: #1877F2;">
            <i data-lucide="facebook"></i>
        </a>
    @endif
</div>

<!-- JS -->
<script src="https://unpkg.com/lucide@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
    .swal2-popup {
        background: var(--color-card-bg) !important;
        color: #fff !important;
        border-radius: 24px !important;
        border: 1px solid var(--color-border) !important;
        font-family: 'Plus Jakarta Sans', sans-serif !important;
    }
    .swal2-title {
        font-family: 'Outfit', sans-serif !important;
        color: #fff !important;
    }
    .swal2-confirm {
        background-color: var(--color-btn-bg) !important;
        color: var(--color-btn-text) !important;
        border-radius: 12px !important;
    }
</style>
<script>
    lucide.createIcons();

    // Global Swal instance for the menu
    const MenuSwal = Swal.mixin({
        background: 'var(--color-card-bg)',
        color: '#fff',
        confirmButtonColor: 'var(--color-btn-bg)',
        customClass: {
            confirmButton: 'btn-category active px-4 border-0'
        }
    });

    // Smooth scroll and active state management
    const categoryLinks = document.querySelectorAll('.btn-category');
    const sections = document.querySelectorAll('.category-section');

    categoryLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const id = link.getAttribute('href').slice(1);
            const section = document.getElementById(id);

            window.scrollTo({
                top: section.offsetTop - 100,
                behavior: 'smooth'
            });

            categoryLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');
        });
    });

    // Intersection Observer to update active category on scroll
    const observerOptions = {
        root: null,
        rootMargin: '-150px 0px -70% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                categoryLinks.forEach(link => {
                    link.classList.toggle('active', link.getAttribute('href') === `#${id}`);
                    if (link.getAttribute('href') === `#${id}`) {
                        // Scroll category menu locally to keep active button visible
                        link.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
                    }
                });
            }
        });
    }, observerOptions);

    sections.forEach(section => observer.observe(section));
</script>

<!-- Footer para evitar que la barra del navegador móvil tape contenido -->
<footer class="menu-footer" style="
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: max(60px, env(safe-area-inset-bottom, 0px) + 60px);
    background: var(--color-bg);
    border-top: 1px solid var(--color-border);
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-bottom: env(safe-area-inset-bottom, 0px);
">
    <div style="
        text-align: center;
        color: var(--text-muted);
        font-size: 0.75rem;
        padding: 0 20px;
    ">
        <p style="margin: 0; opacity: 0.6;">Powered by Cartify - Creá tu menú digital <a href="https://cartify.uy" target="_blank" style="color: var(--color-primary); text-decoration: none; font-weight: 800;">aquí</a></p>
    </div>
</footer>

</body>
</html>
