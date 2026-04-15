<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>{{ $company->name ?? 'Sushi Burger' }} - Pedir ahora</title>

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


        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            color: var(--color-text);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.15;
        }

        .brand-text .line1 { font-size: 0.75rem; opacity: 0.95; }
        .brand-text .line2 { font-size: 1rem; }

        .top-bar-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .icon-cart-wrap {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
        }

        .icon-menu {
            color: var(--color-text);
            padding: 0.5rem;
        }

        /* Hero fullscreen con video de fondo */
        .hero-landing {
            position: relative;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding-bottom: max(80px, env(safe-area-inset-bottom, 0px) + 60px);
            overflow: hidden;
            background: var(--color-bg);
        }

        .hero-video-bg {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center bottom;
            z-index: 0;
            pointer-events: none;
        }

        .hero-landing::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.5) 0%, transparent 40%, rgba(0,0,0,0.3) 100%);
            pointer-events: none;
            z-index: 1;
        }

        @media (min-width: 768px) {
            .hero-video-bg {
                object-position: center center;
            }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding-top: max(100px, env(safe-area-inset-top, 0px) + 60px);
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            padding-bottom: 2rem;
            text-align: start;
        }

        @media (min-width: 768px) {
            .hero-content {
                padding-top: max(60px, env(safe-area-inset-top, 0px) + 60px);
                padding-left: 2rem;
                padding-right: 2rem;
                padding-bottom: 3rem;
                max-width: 720px;
                margin: 0 auto;
            }
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: var(--color-text);
            text-transform: uppercase;
            letter-spacing: 0.02em;
            line-height: 1.1;
            margin-bottom: 2rem;
        }

        .hero-title .line-small {
            font-size: 2.5rem!important;
            display: block;
            margin-bottom: 0.15em;
            margin-top: 0px!important;
            font-family: 'Open Sans', sans-serif;
            font-weight: 300!important;
        }

        .hero-title .line-big {
            font-size: 5rem!important;
            display: block;
            letter-spacing: -0.02em;
            line-height: 70px!important;
        }

        .hero-title .line-end {
            font-size: 2.5rem!important;
            display: block;
            margin-top: 0.1em;
            font-family: 'Open Sans', sans-serif;
            font-weight: 300!important;
        }

        .btn-pedir-ahora {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 2.5rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #fff;
            background: #dc2626;
            border: none;
            border-radius: 9999px;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.4);
        }

        .btn-pedir-ahora:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(220, 38, 38, 0.5);
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

        .line1 {
            font-size: 1.1rem!important;
        }

        .line2 {
            font-size: 1.8rem!important;
        }

    </style>
</head>
<body>

@php
    $companyName = $company->name ?? 'Sushi Burger';
    $nameParts = preg_split('/\s+/', $companyName, 2);
    $brandLine1 = $nameParts[0] ?? $companyName;
    $brandLine2 = $nameParts[1] ?? '';
    $firstRestaurant = $restaurants->first();
@endphp

<!-- Hero landing -->
<div class="hero-landing">
    <video class="hero-video-bg" autoplay muted loop playsinline preload="auto">
        <source src="{{ asset('assets/img/bg-sushiburger-video.mp4') }}" type="video/mp4">
    </video>
    <div class="hero-content">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <div class="brand-text">
                <div class="line1">SUSHIBURGER</div>
                <div class="line2">EXPERIENCE</div>
            </div>
        </div>

        <h1 class="hero-title">
            <span class="line-small">LA PRIMERA</span>
            <span class="line-big">SUSHI BURGER</span>
            <span class="line-end">DE URUGUAY</span>
        </h1>

        @if($firstRestaurant)
            <a href="{{ route('public.menu', $firstRestaurant->slug) }}" class="btn-pedir-ahora">PEDIR AHORA</a>
        @endif
    </div>
</div>

<!-- Footer -->
<footer class="footer-brand">
    <span>Powered by <strong><a href="https://mvdstudio.com.uy" target="_blank" rel="noopener noreferrer" class="cartify-link text-decoration-none" style="color: inherit;">MVD Studio</a></strong></span>
</footer>

<script>
    lucide.createIcons();
</script>
</body>
</html>
