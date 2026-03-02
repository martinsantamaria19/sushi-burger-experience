<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ \App\Models\SiteSetting::get('meta_description', 'Depósitos seguros en Uruguay. BoxCenter.') }}">
    <title>@yield('title', \App\Models\SiteSetting::get('site_name', 'BoxCenter Uruguay'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        :root {
            --bc-bg: #FDFDFC;
            --bc-dark: #1b1b18;
            --bc-muted: #706f6c;
            --bc-red: #e63946;
            --bc-red-hover: #c1121f;
            --bc-border: rgba(27, 27, 24, 0.12);
        }
        body { font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif; background: var(--bc-bg); color: var(--bc-dark); }
        .bc-nav a:hover { color: var(--bc-red); }
        .bc-btn { background: var(--bc-dark); color: #fff; }
        .bc-btn:hover { background: #000; }
        .bc-btn-outline { border: 1px solid var(--bc-border); color: var(--bc-dark); }
        .bc-btn-outline:hover { border-color: var(--bc-red); color: var(--bc-red); }
    </style>
</head>
<body class="antialiased">
    <header class="border-b border-[var(--bc-border)] bg-white/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="font-semibold text-lg text-[var(--bc-dark)]">
                {{ \App\Models\SiteSetting::get('site_name', 'BoxCenter Uruguay') }}
            </a>
            <nav class="flex items-center gap-6 bc-nav text-sm">
                <a href="{{ route('home') }}">Inicio</a>
                <a href="{{ route('soluciones') }}">Soluciones</a>
                <a href="{{ route('instalaciones') }}">Instalaciones</a>
                <a href="{{ route('contacto') }}">Contacto</a>
                <a href="{{ route('cotizar') }}" class="bc-btn px-4 py-2 rounded text-sm font-medium">Cotizar</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-[var(--bc-dark)] text-gray-300 py-12 mt-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="font-semibold text-white">{{ \App\Models\SiteSetting::get('site_name', 'BoxCenter Uruguay') }}</div>
                <div class="flex gap-6 text-sm">
                    <a href="{{ route('contacto') }}" class="hover:text-white">Contacto</a>
                    <a href="{{ route('cotizar') }}" class="hover:text-white">Cotización</a>
                </div>
            </div>
            <p class="mt-6 text-sm text-gray-500 text-center md:text-left">
                {{ \App\Models\SiteSetting::get('contact_address', 'Montevideo, Uruguay') }}
            </p>
        </div>
    </footer>
</body>
</html>
