<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel') - Admin BoxCenter</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --bc-red: #e63946; }
        .admin-sidebar { background: #0f1419; min-height: 100vh; }
        .admin-link:hover { background: rgba(255,255,255,0.05); }
    </style>
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="flex">
        <aside class="admin-sidebar w-56 text-white flex-shrink-0 flex flex-col">
            <div class="p-4 border-b border-white/10">
                <a href="{{ route('admin.dashboard') }}" class="font-semibold">BoxCenter Admin</a>
            </div>
            <nav class="p-2 flex-1">
                <a href="{{ route('admin.dashboard') }}" class="admin-link block px-3 py-2 rounded text-sm">Dashboard</a>
                <a href="{{ route('admin.contents.index') }}" class="admin-link block px-3 py-2 rounded text-sm">Contenidos del sitio</a>
                <a href="{{ route('admin.contacts.index') }}" class="admin-link block px-3 py-2 rounded text-sm">Mensajes de contacto</a>
                <a href="{{ route('admin.quotes.index') }}" class="admin-link block px-3 py-2 rounded text-sm">Cotizaciones</a>
                <a href="{{ url('/') }}" target="_blank" class="admin-link block px-3 py-2 rounded text-sm">Ver sitio →</a>
            </nav>
            <div class="p-4 border-t border-white/10">
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-400 hover:text-white w-full text-left">Cerrar sesión</button>
                </form>
            </div>
        </aside>
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
