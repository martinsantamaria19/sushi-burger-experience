@extends('layouts.app')

@section('title', 'Dashboard - Sushi Burger Experience')

@section('content')
<div class="card" style="max-width: 400px; text-align: center; padding: 3rem;">
    <div class="loader-container" style="margin-bottom: 2rem;">
        <svg class="spinner" viewBox="0 0 50 50" style="width: 50px; height: 50px; margin: 0 auto; animation: rotate 2s linear infinite;">
            <circle class="path" cx="25" cy="25" r="20" fill="none" stroke="url(#gradient)" stroke-width="4" style="stroke-linecap: round; animation: dash 1.5s ease-in-out infinite;"></circle>
            <defs>
                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#7c3aed" />
                    <stop offset="100%" stop-color="#db2777" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <h2 class="t-gradient" style="font-size: 1.5rem; font-weight: 700;">Redirigiéndote...</h2>
    <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-top: 0.5rem;">Estamos preparando todo...</p>
</div>

<style>
    @keyframes rotate {
        100% { transform: rotate(360deg); }
    }
    @keyframes dash {
        0% { stroke-dasharray: 1, 150; stroke-dashoffset: 0; }
        50% { stroke-dasharray: 90, 150; stroke-dashoffset: -35; }
        100% { stroke-dasharray: 90, 150; stroke-dashoffset: -124; }
    }
</style>

<script>
    setTimeout(() => {
        window.location.href = "{{ route('admin.dashboard') }}";
    }, 2500);
</script>
@endsection
