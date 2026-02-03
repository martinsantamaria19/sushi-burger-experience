@extends('layouts.admin')

@section('title', 'Gestión de Planes')
@section('page_title', 'Planes de Suscripción')

@section('content')
<div class="row g-4">
    @foreach($plans as $plan)
    <div class="col-md-6">
        <div class="glass-card p-4 h-100">
            <form action="{{ route('super_admin.plans.update', $plan->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 {{ $plan->slug === 'premium' ? 'text-primary' : '' }}">{{ $plan->name }}</h5>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $plan->is_active ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label text-muted">Activo</label>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio Mensual</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--color-border);">$</span>
                        <input type="number" step="0.01" name="price" class="form-control-cartify border-start-0 ps-0" value="{{ $plan->price }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Precio Anual</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted" style="border-color: var(--color-border);">$</span>
                        <input type="number" step="0.01" name="price_annual" class="form-control-cartify border-start-0 ps-0" value="{{ $plan->price_annual }}">
                    </div>
                    <div class="form-text text-muted small"><i data-lucide="info" style="width: 12px; display: inline;"></i> El plan debe tener configurado un 'price_annual' para habilitar la suscripción anual.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted small">ID MP Mensual</label>
                    <input type="text" class="form-control-cartify bg-dark text-muted" value="{{ $plan->mp_plan_id }}" readonly disabled style="font-size: 0.8rem;">
                </div>
                
                 <div class="mb-3">
                    <label class="form-label text-muted small">ID MP Anual</label>
                    <input type="text" class="form-control-cartify bg-dark text-muted" value="{{ $plan->mp_annual_plan_id }}" readonly disabled style="font-size: 0.8rem;">
                </div>

                <div class="d-flex justify-content-end pt-3 border-top" style="border-color: rgba(255,255,255,0.05) !important;">
                    <button type="submit" class="btn btn-cartify-primary">
                        <i data-lucide="save" style="width: 16px;"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endsection
