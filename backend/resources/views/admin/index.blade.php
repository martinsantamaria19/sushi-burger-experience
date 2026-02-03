@extends('layouts.admin')

@section('title', 'Dashboard - Cartify')
@section('page_title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-6 col-md-6 col-lg-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                    <i data-lucide="utensils-crossed"></i>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary-light">{{ $stats['products_count'] }}</span>
            </div>
            <h3 class="h4 fw-bold mb-1">{{ $stats['products_count'] }}</h3>
            <p class="text-muted small mb-0">Productos</p>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width: 48px; height: 48px; background: rgba(219, 39, 119, 0.1); color: rgba(219, 39, 119, 1);">
                    <i data-lucide="qr-code"></i>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $stats['qrcodes_count'] }}</span>
            </div>
            <h3 class="h4 fw-bold mb-1">{{ $stats['qrcodes_count'] }}</h3>
            <p class="text-muted small mb-0">Códigos QR</p>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                    <i data-lucide="scan-line"></i>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary-light">{{ number_format($stats['total_scans']) }}</span>
            </div>
            <h3 class="h4 fw-bold mb-1">{{ number_format($stats['total_scans']) }}</h3>
            <p class="text-muted small mb-0">Total Escaneos</p>
        </div>
    </div>
    <div class="col-6 col-md-6 col-lg-3">
        <div class="glass-card p-4 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center"
                     style="width: 48px; height: 48px; background: rgba(219, 39, 119, 0.1); color: rgba(219, 39, 119, 1);">
                    <i data-lucide="folder"></i>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger">{{ $stats['categories_count'] }}</span>
            </div>
            <h3 class="h4 fw-bold mb-1">{{ $stats['categories_count'] }}</h3>
            <p class="text-muted small mb-0">Categorías</p>
        </div>
    </div>
</div>

<!-- Incentive Cards -->
@if(!$stats['has_menu'] || $stats['products_count'] == 0 || $stats['qrcodes_count'] == 0)
<div class="row g-4 mb-4">
    @if(!$stats['has_menu'])
    <div class="col-12 col-md-6">
        <div class="glass-card p-4 border" style="border-color: var(--color-primary) !important; background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(219, 39, 119, 0.05) 100%);">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 56px; height: 56px; background: rgba(124, 58, 237, 0.2); color: var(--color-primary-light);">
                    <i data-lucide="book-open" style="width: 28px; height: 28px;"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="h5 fw-bold mb-2">Crea tu primer menú</h4>
                    <p class="text-muted small mb-3">Organiza tus productos en categorías para que tus clientes puedan navegar fácilmente.</p>
                    <a href="{{ route('admin.menu') }}" class="btn btn-cartify-primary btn-sm">
                        Crear Menú
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($stats['has_menu'] && $stats['products_count'] == 0)
    <div class="col-12 col-md-6">
        <div class="glass-card p-4 border" style="border-color: var(--color-primary) !important; background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(219, 39, 119, 0.05) 100%);">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 56px; height: 56px; background: rgba(124, 58, 237, 0.2); color: var(--color-primary-light);">
                    <i data-lucide="utensils-crossed" style="width: 28px; height: 28px;"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="h5 fw-bold mb-2">Agrega tus primeros productos</h4>
                    <p class="text-muted small mb-3">Comienza a construir tu menú digital agregando productos con precios y descripciones.</p>
                    <a href="{{ route('admin.menu') }}" class="btn btn-cartify-primary btn-sm">
                        Agregar Productos
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($stats['qrcodes_count'] == 0)
    <div class="col-12 col-md-6">
        <div class="glass-card p-4 border" style="border-color: var(--color-primary) !important; background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(219, 39, 119, 0.05) 100%);">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width: 56px; height: 56px; background: rgba(219, 39, 119, 0.2); color: rgba(219, 39, 119, 1);">
                    <i data-lucide="qr-code" style="width: 28px; height: 28px;"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="h5 fw-bold mb-2">Genera códigos QR</h4>
                    <p class="text-muted small mb-3">Crea códigos QR únicos para rastrear de dónde vienen tus clientes (mesas, flyers, etc.).</p>
                    <a href="{{ route('admin.qrs') }}" class="btn btn-cartify-primary btn-sm">
                        Crear QR
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- Charts Section -->
@php
    $user = Auth::user();
    $company = $user->company;
    $isFreePlan = $company && $company->isOnFreePlan();
@endphp

@if($stats['total_scans'] > 0)
<div class="row g-4 mb-4">
    <!-- Scans by Day Chart -->
    <div class="col-12 col-lg-8">
        <div class="glass-card p-4 position-relative">
            @if($isFreePlan)
            <div class="premium-lock-overlay">
                <div class="premium-lock-content">
                    <i data-lucide="lock" style="width: 48px; height: 48px; color: var(--color-primary-light); margin-bottom: 1rem;"></i>
                    <h4 class="h5 fw-bold mb-2">Desbloquear con Plan Full</h4>
                    <p class="text-muted small mb-3">Accede a gráficos detallados y análisis avanzados con nuestro plan premium.</p>
                    <a href="{{ route('admin.subscription') }}" class="btn btn-cartify-primary">
                        Ver Planes
                    </a>
                </div>
            </div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-4 chart-header" style="{{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">
                <div>
                    <h4 class="h5 fw-bold mb-1">Escaneos por Día</h4>
                    <p class="text-muted small mb-0">Actividad de escaneos en los últimos días</p>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-sm btn-cartify-secondary active" data-period="7days">7 días</button>
                    <button type="button" class="btn btn-sm btn-cartify-secondary" data-period="30days">30 días</button>
                </div>
            </div>
            <div class="chart-container" style="height: 300px; position: relative; {{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">
                <canvas id="scansByDayChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top QR Codes -->
    <div class="col-12 col-lg-4">
        <div class="glass-card p-4 position-relative">
            @if($isFreePlan)
            <div class="premium-lock-overlay">
                <div class="premium-lock-content">
                    <i data-lucide="lock" style="width: 48px; height: 48px; color: var(--color-primary-light); margin-bottom: 1rem;"></i>
                    <h4 class="h5 fw-bold mb-2">Desbloquear con Plan Full</h4>
                    <p class="text-muted small mb-3">Accede a gráficos detallados y análisis avanzados con nuestro plan premium.</p>
                    <a href="{{ route('admin.subscription') }}" class="btn btn-cartify-primary">
                        Ver Planes
                    </a>
                </div>
            </div>
            @endif
            <h4 class="h5 fw-bold mb-4" style="{{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">QR's Más Escaneados</h4>
            <div class="chart-container" style="height: 300px; position: relative; {{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">
                <canvas id="topQrsChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Scans by Hour Chart -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card p-4 position-relative">
            @if($isFreePlan)
            <div class="premium-lock-overlay">
                <div class="premium-lock-content">
                    <i data-lucide="lock" style="width: 48px; height: 48px; color: var(--color-primary-light); margin-bottom: 1rem;"></i>
                    <h4 class="h5 fw-bold mb-2">Desbloquear con Plan Full</h4>
                    <p class="text-muted small mb-3">Accede a gráficos detallados y análisis avanzados con nuestro plan premium.</p>
                    <a href="{{ route('admin.subscription') }}" class="btn btn-cartify-primary">
                        Ver Planes
                    </a>
                </div>
            </div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-4" style="{{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">
                <div>
                    <h4 class="h5 fw-bold mb-1">Escaneos por Hora</h4>
                    <p class="text-muted small mb-0">Actividad de las últimas 24 horas</p>
                </div>
            </div>
            <div class="chart-container" style="height: 250px; position: relative; {{ $isFreePlan ? 'filter: blur(4px); pointer-events: none;' : '' }}">
                <canvas id="scansByHourChart"></canvas>
            </div>
        </div>
    </div>
</div>
@else
<!-- Empty State -->
<div class="glass-card p-5 text-center">
    <div class="mb-4">
        <i data-lucide="bar-chart-2" style="width: 64px; height: 64px; color: var(--color-primary-light); opacity: 0.5;"></i>
    </div>
    <h3 class="h4 fw-bold mb-2">Aún no hay datos de escaneos</h3>
    <p class="text-muted mb-4">Una vez que generes códigos QR y comiences a recibir escaneos, verás gráficos y estadísticas aquí.</p>
    <a href="{{ route('admin.qrs') }}" class="btn btn-cartify-primary">
        <i data-lucide="qr-code" class="me-2" style="width: 18px;"></i>
        Crear Código QR
    </a>
</div>
@endif
@endsection

@section('styles')
<style>
    .glass-card {
        transition: all 0.3s ease;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    /* Premium Lock Overlay */
    .premium-lock-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(5, 5, 5, 0.85);
        backdrop-filter: blur(8px);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .premium-lock-content {
        text-align: center;
        padding: 2rem;
        max-width: 400px;
    }

    /* Mobile optimizations */
    @media (max-width: 991.98px) {
        .glass-card {
            padding: 1.5rem !important;
        }

        /* Chart containers */
        .chart-container {
            height: 250px !important;
        }

        /* Button groups */
        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-group .btn {
            flex: 1 1 auto;
            min-width: auto;
        }

        /* Chart header adjustments */
        .chart-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .chart-header .btn-group {
            width: 100%;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@vite(['resources/js/dashboard.js'])
<script>
    lucide.createIcons();
</script>
@endsection
