@extends('layouts.public')

@section('title', 'Contacto - ' . \App\Models\SiteSetting::get('site_name'))

@section('content')
<section class="py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl font-semibold text-center mb-2">{{ \App\Models\SiteSetting::get('contact_title') }}</h1>
        <p class="text-[var(--bc-muted)] text-center mb-12">{{ \App\Models\SiteSetting::get('contact_cta') }}</p>

        @if(session('success'))
            <div class="mb-6 p-4 rounded bg-green-50 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('contacto.enviar') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium mb-1">Nombre *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    class="w-full rounded border border-[var(--bc-border)] px-3 py-2 focus:ring-2 focus:ring-[var(--bc-red)] focus:border-transparent">
                @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="block text-sm font-medium mb-1">Email *</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                    class="w-full rounded border border-[var(--bc-border)] px-3 py-2 focus:ring-2 focus:ring-[var(--bc-red)] focus:border-transparent">
                @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="block text-sm font-medium mb-1">Teléfono</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                    class="w-full rounded border border-[var(--bc-border)] px-3 py-2 focus:ring-2 focus:ring-[var(--bc-red)] focus:border-transparent">
            </div>
            <div>
                <label for="message" class="block text-sm font-medium mb-1">Mensaje *</label>
                <textarea id="message" name="message" rows="4" required
                    class="w-full rounded border border-[var(--bc-border)] px-3 py-2 focus:ring-2 focus:ring-[var(--bc-red)] focus:border-transparent">{{ old('message') }}</textarea>
                @error('message')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full bc-btn py-3 rounded font-medium">Enviar mensaje</button>
        </form>

        <div class="mt-12 pt-8 border-t border-[var(--bc-border)] text-center text-[var(--bc-muted)]">
            @if(\App\Models\SiteSetting::get('contact_email'))
                <p>Email: <a href="mailto:{{ \App\Models\SiteSetting::get('contact_email') }}" class="text-[var(--bc-red)] hover:underline">{{ \App\Models\SiteSetting::get('contact_email') }}</a></p>
            @endif
            @if(\App\Models\SiteSetting::get('contact_phone'))
                <p class="mt-1">Teléfono: {{ \App\Models\SiteSetting::get('contact_phone') }}</p>
            @endif
            <p class="mt-2">{{ \App\Models\SiteSetting::get('contact_address') }}</p>
        </div>
    </div>
</section>
@endsection
