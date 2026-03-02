@extends('layouts.public')

@section('title', 'Instalaciones - ' . \App\Models\SiteSetting::get('site_name'))

@section('content')
<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl font-semibold text-center mb-2">{{ \App\Models\SiteSetting::get('installations_title') }}</h1>
        <p class="text-[var(--bc-muted)] text-center mb-16">{{ \App\Models\SiteSetting::get('installations_subtitle') }}</p>

        <div class="grid md:grid-cols-2 gap-8">
            <div class="p-6 rounded-lg border border-[var(--bc-border)]">
                <h2 class="text-xl font-semibold mb-2">{{ \App\Models\SiteSetting::get('install_boxes_title') }}</h2>
                <p class="text-[var(--bc-muted)]">{{ \App\Models\SiteSetting::get('install_boxes_text') }}</p>
            </div>
            <div class="p-6 rounded-lg border border-[var(--bc-border)]">
                <h2 class="text-xl font-semibold mb-2">{{ \App\Models\SiteSetting::get('install_perimeter_title') }}</h2>
                <p class="text-[var(--bc-muted)]">{{ \App\Models\SiteSetting::get('install_perimeter_text') }}</p>
            </div>
            <div class="p-6 rounded-lg border border-[var(--bc-border)]">
                <h2 class="text-xl font-semibold mb-2">{{ \App\Models\SiteSetting::get('install_showroom_title') }}</h2>
                <p class="text-[var(--bc-muted)]">{{ \App\Models\SiteSetting::get('install_showroom_text') }}</p>
            </div>
            <div class="p-6 rounded-lg border border-[var(--bc-border)]">
                <h2 class="text-xl font-semibold mb-2">{{ \App\Models\SiteSetting::get('install_cameras_title') }}</h2>
                <p class="text-[var(--bc-muted)]">{{ \App\Models\SiteSetting::get('install_cameras_text') }}</p>
            </div>
        </div>

        <div class="mt-16 p-6 bg-gray-50 rounded-lg text-center">
            <p class="text-lg font-medium mb-4">{{ \App\Models\SiteSetting::get('contact_cta') }}</p>
            <a href="{{ route('contacto') }}" class="bc-btn px-6 py-3 rounded font-medium">Contactar</a>
        </div>
    </div>
</section>
@endsection
