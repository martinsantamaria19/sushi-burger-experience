<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $company->name ?? 'Selecciona tu Local' }} - Menú Digital</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    @php
        // Obtener settings de la compañía, o usar defaults
        $companySettings = $company->settings ?? [];

        // Colores por defecto acordes a la app
        $colorPrimary = $companySettings['brand_color'] ?? '#7c3aed';
        $colorBg = $companySettings['bg_color'] ?? '#050505';
        $colorCardBg = $companySettings['card_bg_color'] ?? '#101010';
        $colorText = $companySettings['text_color'] ?? '#ffffff';
        $colorTextMuted = $companySettings['text_muted_color'] ?? '#94a3b8';
        $logoPath = $companySettings['logo'] ?? null;

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

        $primaryRgb = hexToRgb($colorPrimary);
    @endphp

    <style>
        :root {
            --color-primary: {{ $colorPrimary }};
            --color-primary-rgb: {{ $primaryRgb }};
            --color-bg: {{ $colorBg }};
            --color-card-bg: {{ $colorCardBg }};
            --color-text: {{ $colorText }};
            --color-text-muted: {{ $colorTextMuted }};
            --color-border: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-bg);
            color: var(--color-text);
            overflow-x: hidden;
            min-height: 100vh;
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

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.2); }
        ::-webkit-scrollbar-thumb {
            background: rgba(var(--color-primary-rgb), 0.6);
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(var(--color-primary-rgb), 0.8);
        }

        /* Hero Header */
        .hero-header {
            position: relative;
            padding: 60px 20px 40px;
            background: linear-gradient(to bottom, rgba(var(--color-primary-rgb), 0.15) 0%, transparent 100%);
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo-container {
            position: relative;
            display: inline-block;
            margin-bottom: 24px;
        }

        .company-logo {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            object-fit: cover;
            border: 3px solid rgba(var(--color-primary-rgb), 0.3);
            box-shadow: 0 8px 32px rgba(var(--color-primary-rgb), 0.2);
        }

        .company-logo-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 24px;
            background: rgba(var(--color-primary-rgb), 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(var(--color-primary-rgb), 0.3);
            margin: 0 auto;
        }

        .company-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.5rem;
        }

        .restaurants-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            /* Padding adicional para evitar que el footer fijo tape contenido */
            padding-bottom: max(100px, env(safe-area-inset-bottom, 0px) + 100px);
        }

        .restaurant-card {
            background: var(--color-card-bg);
            border: 1px solid var(--color-border);
            border-radius: 20px;
            padding: 2rem;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: block;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .restaurant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--color-primary) 0%, rgba(var(--color-primary-rgb), 0.5) 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .restaurant-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(var(--color-primary-rgb), 0.2);
            border-color: rgba(var(--color-primary-rgb), 0.5);
            text-decoration: none;
            color: inherit;
        }

        .restaurant-card:hover::before {
            transform: scaleX(1);
        }

        .restaurant-logo {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            object-fit: cover;
            margin-bottom: 1.5rem;
            border: 2px solid var(--color-border);
        }

        .restaurant-logo-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 16px;
            background: rgba(var(--color-primary-rgb), 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            border: 2px solid var(--color-border);
        }

        .restaurant-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 0.75rem;
        }

        .restaurant-address {
            color: var(--color-text-muted);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .restaurant-address i {
            width: 16px;
            height: 16px;
        }

        .footer-brand {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: max(60px, env(safe-area-inset-bottom, 0px) + 60px);
            text-align: center;
            padding: 1rem 20px;
            padding-bottom: max(1rem, env(safe-area-inset-bottom, 0px) + 1rem);
            color: var(--color-text-muted);
            font-size: 0.75rem;
            background: var(--color-bg);
            border-top: 1px solid var(--color-border);
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-brand strong {
            color: var(--color-text);
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
            @if($logoPath)
                <img src="{{ $logoPath }}" alt="{{ $company->name }}" class="company-logo">
            @else
                <div class="company-logo-placeholder">
                    <i data-lucide="building-2" style="width: 48px; color: var(--color-primary);"></i>
                </div>
            @endif
        </div>
        <h1 class="company-name">{{ $company->name ?? 'Nuestros Locales' }}</h1>
        <p style="color: var(--color-text-muted);">Selecciona un local para ver su menú</p>
    </div>
</header>

<!-- Restaurants Grid -->
<div class="restaurants-container">
    <div class="row g-4">
        @foreach($restaurants as $restaurant)
        <div class="col-12 col-md-6 col-lg-4">
            <a href="{{ route('public.menu', $restaurant->slug) }}" class="restaurant-card">
                <div class="d-flex align-items-start gap-3">
                    @if($restaurant->logo_path)
                        <img src="{{ str_starts_with($restaurant->logo_path, 'http') ? $restaurant->logo_path : asset('storage/' . $restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="restaurant-logo">
                    @else
                        <div class="restaurant-logo-placeholder">
                            <i data-lucide="utensils-crossed" style="width: 32px; color: var(--color-primary);"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h3 class="restaurant-name">{{ $restaurant->name }}</h3>
                        @if($restaurant->address)
                        <div class="restaurant-address">
                            <i data-lucide="map-pin"></i>
                            <span>{{ $restaurant->address }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>


<script>
    lucide.createIcons();
</script>
</body>
</html>
