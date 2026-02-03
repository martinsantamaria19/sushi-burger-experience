@extends('layouts.admin')

@section('title', 'Super Admin Dashboard')
@section('page_title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <h6 class="text-muted mb-2 text-uppercase small ls-1">Usuarios Totales</h6>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $usersCount }}</h2>
                <div class="p-2 rounded-circle bg-primary-subtle text-primary">
                    <i data-lucide="users" style="width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div class="mt-3 small text-success">
                <i data-lucide="trending-up" style="width: 14px; display: inline;"></i> +{{ $usersChartData->sum('count') }} este mes
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <h6 class="text-muted mb-2 text-uppercase small ls-1">MRR Estimado</h6>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">${{ number_format($monthlyRevenue, 2) }}</h2>
                <div class="p-2 rounded-circle bg-success-subtle text-success">
                    <i data-lucide="dollar-sign" style="width: 20px; height: 20px;"></i>
                </div>
            </div>
             <div class="mt-3 small text-muted">
                Ingresos recurrentes mensuales
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <h6 class="text-muted mb-2 text-uppercase small ls-1">Suscripciones Activas</h6>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $planDistribution->sum('count') }}</h2>
                <div class="p-2 rounded-circle bg-warning-subtle text-warning">
                    <i data-lucide="crown" style="width: 20px; height: 20px;"></i>
                </div>
            </div>
             <div class="mt-3 small text-muted">
                Usuarios Premium Activos
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="glass-card p-4 h-100">
            <h6 class="text-muted mb-2 text-uppercase small ls-1">Cupones</h6>
            <div class="d-flex align-items-center justify-content-between">
                <h2 class="mb-0 fw-bold">{{ $couponsCount }}</h2>
                <div class="p-2 rounded-circle bg-info-subtle text-info">
                    <i data-lucide="ticket" style="width: 20px; height: 20px;"></i>
                </div>
            </div>
            <div class="mt-3 small text-muted">
                Cupones generados
            </div>
        </div>
    </div>
</div>

<!-- Charts Section -->
<div class="row g-4 mb-4">
    <div class="col-md-8">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4">Crecimiento de Usuarios</h5>
            <div style="height: 300px;">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="glass-card p-4 h-100">
            <h5 class="fw-bold mb-4">Distribución de Planes</h5>
            <div style="height: 300px; position: relative;">
                <canvas id="plansChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Users -->
<div class="glass-card p-4">
    <h5 class="fw-bold mb-4">Usuarios Recientes</h5>
    <div class="table-responsive">
         <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Usuario</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Plan</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Fecha Registro</th>
                </tr>
            </thead>
            <tbody style="border-top: none;">
                @foreach($recentUsers as $user)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3" style="background: transparent;">
                         <div class="d-flex align-items-center gap-3">
                            <div class="profile-circle bg-secondary-subtle text-white" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <span class="fw-bold text-white d-block">{{ $user->name }}</span>
                                <span class="small text-muted">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-3" style="background: transparent;">
                         @if($user->company && $user->company->currentPlan)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                {{ $user->company->currentPlan->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">Free</span>
                        @endif
                    </td>
                    <td class="py-3 text-gray-300" style="background: transparent;">
                        {{ $user->created_at->diffForHumans() }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="text-center mt-3">
        <a href="{{ route('super_admin.users') }}" class="btn btn-sm btn-link text-decoration-none">Ver todos los usuarios -></a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Shared chart options for dark mode
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#94a3b8' }
                }
            },
            scales: {
                y: {
                    grid: { color: 'rgba(255, 255, 255, 0.05)' },
                    ticks: { color: '#94a3b8' }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8' }
                }
            }
        };

        // Users Growth Chart
        const userCtx = document.getElementById('usersChart').getContext('2d');
        new Chart(userCtx, {
            type: 'line',
            data: {
                labels: @json($usersChartData->pluck('date')),
                datasets: [{
                    label: 'Nuevos Usuarios',
                    data: @json($usersChartData->pluck('count')),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#7c3aed',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#7c3aed'
                }]
            },
            options: {
                ...commonOptions,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(255,255,255,0.1)',
                        borderWidth: 1
                    }
                }
            }
        });

        // Plans Distribution Chart
        const plansCtx = document.getElementById('plansChart').getContext('2d');
        const planData = @json($planDistribution);
        
        new Chart(plansCtx, {
            type: 'doughnut',
            data: {
                labels: planData.map(d => d.name),
                datasets: [{
                    data: planData.map(d => d.count),
                    backgroundColor: planData.map(d => d.color),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#94a3b8',
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: { display: false }
                }
            }
        });
    });
</script>
@endsection
