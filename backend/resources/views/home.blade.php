@extends('layouts.public')

@section('title', \App\Models\SiteSetting::get('site_name') . ' - Depósitos seguros 24/7')

@section('content')
{{-- Hero --}}
<section class="relative bg-[var(--bc-dark)] text-white py-20 lg:py-28 overflow-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 relative z-10">
        <p class="text-sm uppercase tracking-wider text-gray-400 mb-2">{{ \App\Models\SiteSetting::get('site_name') }}</p>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-semibold max-w-3xl leading-tight">
            {{ \App\Models\SiteSetting::get('hero_title') }}
        </h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl">
            {{ \App\Models\SiteSetting::get('hero_subtitle') }}
        </p>
        <p class="mt-2 text-gray-400">{{ \App\Models\SiteSetting::get('hero_description') }}</p>
        <div class="flex flex-wrap gap-4 mt-8">
            <a href="{{ route('cotizar') }}" class="bg-[var(--bc-red)] hover:bg-[var(--bc-red-hover)] text-white px-6 py-3 rounded font-medium transition">
                Solicitar cotización
            </a>
            <a href="{{ route('soluciones') }}" class="bc-btn-outline border-white/30 text-white hover:bg-white/10 px-6 py-3 rounded font-medium transition">
                Ver soluciones
            </a>
        </div>
    </div>
</section>

{{-- Features --}}
<section class="py-16 border-b border-[var(--bc-border)]">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
            <div>
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-[var(--bc-red)] font-bold text-lg">24</div>
                <h3 class="mt-3 font-semibold">{{ \App\Models\SiteSetting::get('feature_1_title') }}</h3>
            </div>
            <div>
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-[var(--bc-red)] font-bold text-lg">✓</div>
                <h3 class="mt-3 font-semibold">{{ \App\Models\SiteSetting::get('feature_2_title') }}</h3>
            </div>
            <div>
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto text-[var(--bc-red)] font-bold text-lg">🔒</div>
                <h3 class="mt-3 font-semibold">{{ \App\Models\SiteSetting::get('feature_3_title') }}</h3>
            </div>
        </div>
    </div>
</section>

{{-- Intro + CTA --}}
<section class="py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
        <p class="text-[var(--bc-muted)] leading-relaxed">
            {{ \App\Models\SiteSetting::get('intro_text') }}
        </p>
        <a href="{{ route('soluciones') }}" class="inline-block mt-6 text-[var(--bc-red)] font-medium hover:underline">
            {{ \App\Models\SiteSetting::get('intro_cta') }} →
        </a>
    </div>
</section>

{{-- Solutions preview --}}
<section class="py-16 bg-gray-50/50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h2 class="text-2xl font-semibold text-center mb-4">{{ \App\Models\SiteSetting::get('solutions_title') }}</h2>
        <p class="text-[var(--bc-muted)] text-center max-w-2xl mx-auto mb-12">
            {{ \App\Models\SiteSetting::get('solutions_subtitle') }}
        </p>
        <div class="grid md:grid-cols-3 gap-8">
            <a href="{{ route('soluciones') }}#particulares" class="block p-6 bg-white rounded-lg border border-[var(--bc-border)] hover:border-[var(--bc-red)] transition">
                <h3 class="font-semibold text-lg">{{ \App\Models\SiteSetting::get('solution_particulares_title') }}</h3>
                <p class="text-sm text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_particulares_tagline') }}</p>
                <span class="inline-block mt-4 text-[var(--bc-red)] text-sm font-medium">Más información →</span>
            </a>
            <a href="{{ route('soluciones') }}#empresas" class="block p-6 bg-white rounded-lg border border-[var(--bc-border)] hover:border-[var(--bc-red)] transition">
                <h3 class="font-semibold text-lg">{{ \App\Models\SiteSetting::get('solution_empresas_title') }}</h3>
                <p class="text-sm text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_empresas_tagline') }}</p>
                <span class="inline-block mt-4 text-[var(--bc-red)] text-sm font-medium">Más información →</span>
            </a>
            <a href="{{ route('soluciones') }}#oficinas" class="block p-6 bg-white rounded-lg border border-[var(--bc-border)] hover:border-[var(--bc-red)] transition">
                <h3 class="font-semibold text-lg">{{ \App\Models\SiteSetting::get('solution_oficinas_title') }}</h3>
                <p class="text-sm text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_oficinas_tagline') }}</p>
                <span class="inline-block mt-4 text-[var(--bc-red)] text-sm font-medium">Más información →</span>
            </a>
        </div>
    </div>
</section>

{{-- Quote CTA --}}
<section class="py-16">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center">
        <h2 class="text-2xl font-semibold">{{ \App\Models\SiteSetting::get('quote_title') }}</h2>
        <p class="text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('quote_subtitle') }}</p>
        <a href="{{ route('cotizar') }}" class="inline-block mt-6 bc-btn px-6 py-3 rounded font-medium">
            Obtener cotización gratis
        </a>
    </div>
</section>

{{-- Stats --}}
<section class="py-12 bg-[var(--bc-dark)] text-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-3 gap-8 text-center">
            <div>
                <span class="block text-3xl font-bold text-[var(--bc-red)]">{{ \App\Models\SiteSetting::get('stat_years') }}</span>
                <span class="text-sm text-gray-400">{{ \App\Models\SiteSetting::get('stat_years_label') }}</span>
            </div>
            <div>
                <span class="block text-3xl font-bold text-[var(--bc-red)]">{{ \App\Models\SiteSetting::get('stat_days') }}</span>
                <span class="text-sm text-gray-400">{{ \App\Models\SiteSetting::get('stat_days_label') }}</span>
            </div>
            <div>
                <span class="block text-3xl font-bold text-[var(--bc-red)]">{{ \App\Models\SiteSetting::get('stat_hours') }}</span>
                <span class="text-sm text-gray-400">{{ \App\Models\SiteSetting::get('stat_hours_label') }}</span>
            </div>
        </div>
    </div>
</section>
@endsection
