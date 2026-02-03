@extends('layouts.admin')

@section('title', 'Analytics - Cartify')
@section('page_title', 'Analytics')

@section('content')
<div class="p-5 rounded-4 text-center" style="background: var(--color-surface); border: 1px solid var(--color-border);">
    <div class="d-inline-flex align-items-center justify-content-center bg-accent-light rounded-circle mb-4" 
         style="width: 80px; height: 80px; background: rgba(219, 39, 119, 0.1);">
        <i data-lucide="trending-up" style="color: var(--color-accent); width: 40px; height: 40px;"></i>
    </div>
    <h2 class="h4 fw-bold mb-3">Métricas en tiempo real</h2>
    <p class="text-muted mx-auto mb-5" style="max-width: 500px;">
        Estamos conectando tus fuentes de datos para mostrarte información valiosa sobre el rendimiento de tu negocio.
    </p>
    <div class="progress mx-auto bg-dark-subtle rounded-pill" style="height: 4px; width: 200px;">
        <div class="progress-bar" role="progressbar" style="width: 60%; background: var(--gradient-brand);"></div>
    </div>
</div>
@endsection
