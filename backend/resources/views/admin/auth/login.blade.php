<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso administración - {{ \App\Models\SiteSetting::get('site_name', 'BoxCenter') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bc-bg { background-color: #0f1419; }
        .bc-red { color: #e63946; }
        .bc-input { background: #1a1f26; border: 1px solid #2d3640; color: #e6edf3; }
    </style>
</head>
<body class="bc-bg min-h-screen flex items-center justify-center text-gray-100 p-4">
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-semibold text-center mb-2">Panel de administración</h1>
        <p class="text-gray-400 text-center text-sm mb-6">Ingresá con tu usuario y contraseña</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded bg-red-900/30 border border-red-700 text-red-200 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="bc-input w-full rounded px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                <input id="password" type="password" name="password" required
                    class="bc-input w-full rounded px-3 py-2 focus:ring-2 focus:ring-red-500 focus:border-transparent">
            </div>
            <div class="flex items-center">
                <input id="remember" type="checkbox" name="remember"
                    class="rounded border-gray-600 bg-gray-700 text-red-500 focus:ring-red-500">
                <label for="remember" class="ml-2 text-sm text-gray-400">Recordarme</label>
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center">
            <a href="{{ url('/') }}" class="text-sm text-gray-400 hover:text-white">← Volver al sitio</a>
        </p>
    </div>
</body>
</html>
