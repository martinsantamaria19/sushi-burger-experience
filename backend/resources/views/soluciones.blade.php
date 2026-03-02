@extends('layouts.public')

@section('title', 'Soluciones - ' . \App\Models\SiteSetting::get('site_name'))

@section('content')
<section class="py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <h1 class="text-3xl font-semibold text-center mb-4">{{ \App\Models\SiteSetting::get('solutions_title') }}</h1>
        <p class="text-[var(--bc-muted)] text-center max-w-2xl mx-auto mb-16">
            {{ \App\Models\SiteSetting::get('solutions_subtitle') }}
        </p>

        <div id="particulares" class="scroll-mt-24 mb-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-2xl font-semibold">{{ \App\Models\SiteSetting::get('solution_particulares_title') }}</h2>
                    <p class="text-lg text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_particulares_tagline') }}</p>
                    <ul class="mt-6 space-y-2 text-[var(--bc-muted)]">
                        @foreach(explode("\n", \App\Models\SiteSetting::get('solution_particulares_points')) as $point)
                            @if(trim($point))
                                <li class="flex items-start gap-2"><span class="text-[var(--bc-red)]">•</span> {{ trim($point) }}</li>
                            @endif
                        @endforeach
                    </ul>
                    <p class="mt-4">{{ \App\Models\SiteSetting::get('solution_particulares_description') }}</p>
                    <a href="{{ route('cotizar') }}" class="inline-block mt-6 bc-btn px-5 py-2 rounded text-sm">Más información</a>
                </div>
                <div class="bg-gray-100 rounded-lg aspect-video flex items-center justify-center text-gray-400">
                    @if(\App\Models\SiteSetting::get('solution_particulares_image'))
                        <img src="{{ Storage::url(\App\Models\SiteSetting::get('solution_particulares_image')) }}" alt="Particulares" class="w-full h-full object-cover rounded-lg">
                    @else
                        <span>Imagen particulares</span>
                    @endif
                </div>
            </div>
        </div>

        <div id="empresas" class="scroll-mt-24 mb-20">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="md:order-2">
                    <h2 class="text-2xl font-semibold">{{ \App\Models\SiteSetting::get('solution_empresas_title') }}</h2>
                    <p class="text-lg text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_empresas_tagline') }}</p>
                    <ul class="mt-6 space-y-2 text-[var(--bc-muted)]">
                        @foreach(explode("\n", \App\Models\SiteSetting::get('solution_empresas_points')) as $point)
                            @if(trim($point))
                                <li class="flex items-start gap-2"><span class="text-[var(--bc-red)]">•</span> {{ trim($point) }}</li>
                            @endif
                        @endforeach
                    </ul>
                    <p class="mt-4">{{ \App\Models\SiteSetting::get('solution_empresas_description') }}</p>
                    <a href="{{ route('cotizar') }}" class="inline-block mt-6 bc-btn px-5 py-2 rounded text-sm">Más información</a>
                </div>
                <div class="md:order-1 bg-gray-100 rounded-lg aspect-video flex items-center justify-center text-gray-400">
                    @if(\App\Models\SiteSetting::get('solution_empresas_image'))
                        <img src="{{ Storage::url(\App\Models\SiteSetting::get('solution_empresas_image')) }}" alt="Empresas" class="w-full h-full object-cover rounded-lg">
                    @else
                        <span>Imagen empresas</span>
                    @endif
                </div>
            </div>
        </div>

        <div id="oficinas" class="scroll-mt-24">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-2xl font-semibold">{{ \App\Models\SiteSetting::get('solution_oficinas_title') }}</h2>
                    <p class="text-lg text-[var(--bc-muted)] mt-2">{{ \App\Models\SiteSetting::get('solution_oficinas_tagline') }}</p>
                    <ul class="mt-6 space-y-2 text-[var(--bc-muted)]">
                        @foreach(explode("\n", \App\Models\SiteSetting::get('solution_oficinas_points')) as $point)
                            @if(trim($point))
                                <li class="flex items-start gap-2"><span class="text-[var(--bc-red)]">•</span> {{ trim($point) }}</li>
                            @endif
                        @endforeach
                    </ul>
                    <p class="mt-4">{{ \App\Models\SiteSetting::get('solution_oficinas_description') }}</p>
                    <a href="{{ route('cotizar') }}" class="inline-block mt-6 bc-btn px-5 py-2 rounded text-sm">Más información</a>
                </div>
                <div class="bg-gray-100 rounded-lg aspect-video flex items-center justify-center text-gray-400">
                    @if(\App\Models\SiteSetting::get('solution_oficinas_image'))
                        <img src="{{ Storage::url(\App\Models\SiteSetting::get('solution_oficinas_image')) }}" alt="Oficinas" class="w-full h-full object-cover rounded-lg">
                    @else
                        <span>Imagen oficinas</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
