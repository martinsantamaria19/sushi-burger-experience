@extends('layouts.admin')

@section('title', 'Mejorar Plan - Sushi Burger Experience')
@section('page_title', 'Mejorar Plan')

@section('content')
<div class="row g-4">
    <!-- Plan Actual -->
    <div class="col-12">
        <div class="p-4 p-md-5 rounded-4 glass-card border" style="border-color: var(--color-border) !important;">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="h5 fw-bold mb-2">Plan Actual</h3>
                    <p class="text-muted mb-0">
                        @if($currentPlan)
                            <span class="badge bg-primary me-2">{{ $currentPlan->name }}</span>
                            @if($currentSubscription && $currentSubscription->isActive())
                                @if($currentSubscription->current_period_end)
                                    Renovación: {{ $currentSubscription->current_period_end->format('d/m/Y') }}
                                @endif
                            @endif
                        @else
                            <span class="text-warning">Sin plan asignado</span>
                        @endif
                    </p>
                </div>
                @if($currentPlan && $currentPlan->slug === 'premium' && $currentSubscription && $currentSubscription->isActive())
                <button class="btn btn-cartify-secondary" id="cancelSubscriptionBtn">
                    <i data-lucide="x-circle" style="width: 16px;"></i>
                    Cancelar Suscripción
                </button>
                @endif
            </div>

            <!-- Límites Actuales -->
            <div class="row g-3 mt-3">
                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(124, 58, 237, 0.1); border: 1px solid rgba(124, 58, 237, 0.2);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small text-muted">Restaurantes</span>
                            <i data-lucide="building-2" style="width: 18px; color: var(--color-primary);"></i>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h4 fw-bold mb-0">{{ $currentLimits['restaurants']['current'] }}</span>
                            <span class="text-muted small">
                                @if($currentLimits['restaurants']['limit'] === null)
                                    / ∞
                                @else
                                    / {{ $currentLimits['restaurants']['limit'] }}
                                @endif
                            </span>
                        </div>
                        @if($currentLimits['restaurants']['remaining'] !== null && $currentLimits['restaurants']['remaining'] <= 1)
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">Quedan {{ $currentLimits['restaurants']['remaining'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(124, 58, 237, 0.1); border: 1px solid rgba(124, 58, 237, 0.2);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small text-muted">Usuarios</span>
                            <i data-lucide="users" style="width: 18px; color: var(--color-primary);"></i>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h4 fw-bold mb-0">{{ $currentLimits['users']['current'] }}</span>
                            <span class="text-muted small">
                                @if($currentLimits['users']['limit'] === null)
                                    / ∞
                                @else
                                    / {{ $currentLimits['users']['limit'] }}
                                @endif
                            </span>
                        </div>
                        @if($currentLimits['users']['remaining'] !== null && $currentLimits['users']['remaining'] <= 1)
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">Quedan {{ $currentLimits['users']['remaining'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-3 rounded-3" style="background: rgba(124, 58, 237, 0.1); border: 1px solid rgba(124, 58, 237, 0.2);">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small text-muted">Códigos QR</span>
                            <i data-lucide="qr-code" style="width: 18px; color: var(--color-primary);"></i>
                        </div>
                        <div class="d-flex align-items-baseline gap-2">
                            <span class="h4 fw-bold mb-0">{{ $currentLimits['qr_codes']['current'] }}</span>
                            <span class="text-muted small">
                                @if($currentLimits['qr_codes']['limit'] === null)
                                    / ∞
                                @else
                                    / {{ $currentLimits['qr_codes']['limit'] }}
                                @endif
                            </span>
                        </div>
                        @if($currentLimits['qr_codes']['remaining'] !== null && $currentLimits['qr_codes']['remaining'] <= 1)
                        <div class="mt-2">
                            <span class="badge bg-warning text-dark">Quedan {{ $currentLimits['qr_codes']['remaining'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Planes Disponibles -->
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between mb-4 gap-3">
            <h3 class="h5 fw-bold mb-0">Planes Disponibles</h3>

            <div class="d-flex align-items-center gap-3">
                <!-- Toggle Mensual/Anual -->
                <div class="bg-white p-1 rounded-pill border d-inline-flex position-relative" style="min-width: 240px;">
                    <div class="position-absolute bg-primary rounded-pill transition-all" id="billingToggleBg" style="width: 50%; height: calc(100% - 8px); top: 4px; left: 4px; transition: all 0.3s ease; opacity: 0.1;"></div>
                    <button class="btn btn-sm rounded-pill fw-bold w-50 position-relative z-1 text-primary" id="btnMonthly" onclick="setBillingCycle('monthly')">
                        Mensual
                    </button>
                    <button class="btn btn-sm rounded-pill fw-bold w-50 position-relative z-1 text-muted" id="btnAnnual" onclick="setBillingCycle('annual')">
                        Anual <span class="badge bg-success ms-1" style="font-size: 0.6rem;">-30%</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Input de Cupón -->
        <div class="row justify-content-center mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i data-lucide="ticket" style="width: 18px; color: var(--color-primary);"></i>
                    </span>
                    <input type="text" id="couponCode" class="form-control border-start-0 ps-0" placeholder="¿Tienes un código de descuento?">
                </div>
            </div>
        </div>
    </div>

    @forelse($plans as $plan)
    <div class="col-12 col-md-6">
        <div class="p-4 p-md-5 rounded-4 h-100 position-relative sbe-plan-card cartify-plan-card {{ $plan->slug === 'premium' ? 'glass-card border-primary' : 'glass-card border' }}"
             style="border-color: {{ $plan->slug === 'premium' ? 'var(--color-primary)' : 'var(--color-border)' }} !important; border-width: 2px !important;">
            @if($plan->slug === 'premium')
            <div class="position-absolute top-0 start-50 translate-middle mt-n3">
                <span class="badge bg-primary px-3 py-2">MÁS POPULAR</span>
            </div>
            @endif

            <div class="text-center mb-4">
                <h4 class="h4 fw-bold mb-2">{{ $plan->name }}</h4>
                <div class="mb-3">
                    @if($plan->price)
                        <div class="price-display price-monthly">
                            <span class="h2 fw-bold">${{ number_format($plan->price, 2) }}</span>
                            <span class="text-muted">/mes</span>
                        </div>
                        <div class="price-display price-annual d-none">
                            <span class="h2 fw-bold">${{ number_format($plan->price_annual ?? ($plan->price * 12 * 0.7), 2) }}</span>
                            <span class="text-muted">/año</span>
                            <div class="small text-success fw-bold">Ahorras un 30%</div>
                        </div>
                    @else
                        <span class="h2 fw-bold">Gratis</span>
                    @endif
                </div>
                <p class="text-muted small mb-0">{{ $plan->description }}</p>
            </div>

            <div class="mb-4">
                <h6 class="small fw-bold text-uppercase mb-3" style="color: var(--color-primary);">Características:</h6>
                <ul class="list-unstyled mb-0">
                    @if($plan->getLimits())
                        @if($plan->getLimits()['restaurants'] === null)
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>Restaurantes Ilimitados</span>
                            </li>
                        @else
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>{{ $plan->getLimits()['restaurants'] }} Restaurante(s)</span>
                            </li>
                        @endif

                        @if($plan->getLimits()['users'] === null)
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>Usuarios Ilimitados</span>
                            </li>
                        @else
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>{{ $plan->getLimits()['users'] }} Usuario(s)</span>
                            </li>
                        @endif

                        @if($plan->getLimits()['qr_codes'] === null)
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>Códigos QR Ilimitados</span>
                            </li>
                        @else
                            <li class="mb-2 d-flex align-items-center gap-2">
                                <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                                <span>{{ $plan->getLimits()['qr_codes'] }} Código(s) QR</span>
                            </li>
                        @endif
                    @endif

                    @if($plan->hasFeature('branding'))
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                            <span>Personalización de Marca</span>
                        </li>
                    @endif

                    @if($plan->hasFeature('analytics'))
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                            <span>Analytics & Reportes</span>
                        </li>
                    @endif

                    @if($plan->hasFeature('advanced_reports'))
                        <li class="mb-2 d-flex align-items-center gap-2">
                            <i data-lucide="check" style="width: 18px; color: var(--color-primary);"></i>
                            <span>Reportes Avanzados</span>
                        </li>
                    @endif
                </ul>
            </div>

            <div class="mt-4">
                @if($currentPlan && $currentPlan->id === $plan->id)
                    <button class="btn btn-cartify-secondary w-100" disabled>
                        <i data-lucide="check-circle" style="width: 18px;"></i>
                        Plan Actual
                    </button>
                @elseif($plan->slug === 'free')
                    <button class="btn btn-cartify-secondary w-100" onclick="switchToFreePlan()">
                        Cambiar a Free
                    </button>
                @else
                    <button class="btn btn-cartify-primary w-100" onclick="upgradeToPlan({{ $plan->id }})">
                        <i data-lucide="sparkles" style="width: 18px;"></i>
                        @if($currentPlan && $currentPlan->slug === 'premium')
                            Renovar Suscripción
                        @else
                            Mejorar a {{ $plan->name }}
                        @endif
                    </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="p-4 p-md-5 rounded-4 glass-card border text-center" style="border-color: var(--color-border) !important;">
            <p class="text-muted mb-0">No hay planes disponibles en este momento.</p>
        </div>
    </div>
    @endforelse

    @if($currentSubscription && $currentSubscription->isActive() && $currentPlan && $currentPlan->slug === 'premium')
    <!-- Información de Suscripción Premium -->
    <div class="col-12 mt-4">
        <div class="p-4 p-md-5 rounded-4 glass-card border" style="border-color: var(--color-border) !important;">
            <h3 class="h5 fw-bold mb-4">Información de Suscripción</h3>

            <div class="row g-4">
                <div class="col-md-6">
                    <div>
                        <label class="small text-muted mb-1">Estado</label>
                        <p class="mb-0">
                            @if($currentSubscription->isActive())
                                <span class="badge bg-success">Activa</span>
                            @elseif($currentSubscription->isCancelled())
                                <span class="badge bg-warning">Cancelada</span>
                            @elseif($currentSubscription->isExpired())
                                <span class="badge bg-danger">Expirada</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($currentSubscription->status) }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                @if($currentSubscription->current_period_start)
                <div class="col-md-6">
                    <div>
                        <label class="small text-muted mb-1">Período Actual</label>
                        <p class="mb-0">
                            {{ $currentSubscription->current_period_start->format('d/m/Y') }} -
                            {{ $currentSubscription->current_period_end->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
                @endif

                @if($currentSubscription->ends_at)
                <div class="col-md-6">
                    <div>
                        <label class="small text-muted mb-1">Finaliza</label>
                        <p class="mb-0">{{ $currentSubscription->ends_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    lucide.createIcons();

    lucide.createIcons();

    let currentBillingCycle = 'monthly';

    function setBillingCycle(cycle) {
        currentBillingCycle = cycle;
        const btnMonthly = document.getElementById('btnMonthly');
        const btnAnnual = document.getElementById('btnAnnual');
        const bg = document.getElementById('billingToggleBg');

        if (cycle === 'monthly') {
            btnMonthly.classList.remove('text-muted');
            btnMonthly.classList.add('text-primary');
            btnAnnual.classList.remove('text-primary');
            btnAnnual.classList.add('text-muted');
            bg.style.left = '4px';

            document.querySelectorAll('.price-monthly').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.price-annual').forEach(el => el.classList.add('d-none'));
        } else {
            btnMonthly.classList.remove('text-primary');
            btnMonthly.classList.add('text-muted');
            btnAnnual.classList.remove('text-muted');
            btnAnnual.classList.add('text-primary');
            bg.style.left = '50%';

            document.querySelectorAll('.price-monthly').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.price-annual').forEach(el => el.classList.remove('d-none'));
        }
    }

    // Función para mejorar a un plan
    async function upgradeToPlan(planId) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        const couponCode = document.getElementById('couponCode').value.trim();

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Procesando...';

        try {
            const body = {
                plan_id: planId,
                billing_cycle: currentBillingCycle
            };

            if (couponCode) {
                body.coupon_code = couponCode;
            }

            const response = await fetch('/subscriptions/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(body)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al crear el intento de pago');
            }

            // Redirigir a MercadoPago
            if (data.init_point) {
                // Determinar qué URL usar (sandbox o producción)
                const isProduction = {{ config('services.mercadopago.environment') === 'production' ? 'true' : 'false' }};
                const checkoutUrl = isProduction ? data.init_point : (data.sandbox_init_point || data.init_point);

                window.location.href = checkoutUrl;
            } else {
                throw new Error('No se recibió la URL de checkout');
            }
        } catch (error) {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = originalText;

            window.CartifySwal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Hubo un problema al procesar tu solicitud. Por favor, intenta nuevamente.'
            });
        }
    }

    // Función para cambiar a plan FREE
    async function switchToFreePlan() {
        const freePlanId = {{ $freePlan ? $freePlan->id : 'null' }};

        if (!freePlanId) {
            window.CartifySwal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Plan Free no disponible'
            });
            return;
        }

        const result = await window.CartifySwal.fire({
            icon: 'warning',
            title: '¿Cambiar a Plan Free?',
            text: 'Perderás acceso a todas las características premium. ¿Estás seguro?',
            showCancelButton: true,
            confirmButtonText: 'Sí, cambiar a Free',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        try {
            const response = await fetch('/subscriptions/create-intent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ plan_id: freePlanId })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al cambiar el plan');
            }

            window.Toast.fire({
                icon: 'success',
                title: 'Plan cambiado a Free correctamente'
            });

            // Recargar la página después de un segundo
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error('Error:', error);
            window.CartifySwal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Hubo un problema al cambiar el plan. Por favor, intenta nuevamente.'
            });
        }
    }

    // Función para cancelar suscripción
    @if($currentPlan && $currentPlan->slug === 'premium' && $currentSubscription && $currentSubscription->isActive())
    document.getElementById('cancelSubscriptionBtn')?.addEventListener('click', async function() {
        const result = await window.CartifySwal.fire({
            icon: 'warning',
            title: '¿Cancelar Suscripción?',
            html: `
                <p>Tu suscripción se cancelará pero mantendrás acceso hasta:</p>
                <p class="fw-bold">${new Date('{{ $currentSubscription->current_period_end->toIso8601String() }}').toLocaleDateString('es-ES', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                })}</p>
                <p class="small text-muted">Después de esa fecha, se cambiará automáticamente al plan Free.</p>
            `,
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'Mantener suscripción',
            confirmButtonColor: '#dc3545'
        });

        if (!result.isConfirmed) return;

        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Cancelando...';

        try {
            const response = await fetch('/subscriptions/cancel', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Error al cancelar la suscripción');
            }

            window.Toast.fire({
                icon: 'success',
                title: 'Suscripción cancelada correctamente'
            });

            // Recargar la página después de un segundo
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } catch (error) {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = originalText;

            window.CartifySwal.fire({
                icon: 'error',
                title: 'Error',
                text: error.message || 'Hubo un problema al cancelar la suscripción. Por favor, intenta nuevamente.'
            });
        }
    });
    @endif

    // Actualizar iconos después de cualquier cambio en el DOM
    lucide.createIcons();
</script>
@endsection

