@extends('layouts.admin')

@section('title', 'Cupones de Descuento')
@section('page_title', 'Cupones')

@section('content')
<div class="glass-card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="mb-0 fw-bold">Listado de Cupones</h5>
        <button class="btn btn-cartify-primary" data-bs-toggle="modal" data-bs-target="#createCouponModal">
            <i data-lucide="plus" style="width: 16px;"></i> Crear Cupón
        </button>
    </div>

    <div class="table-responsive">
         <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Código</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Descuento</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Expira</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Estado</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Acciones</th>
                </tr>
            </thead>
            <tbody style="border-top: none;">
                @forelse($coupons as $coupon)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3 fw-bold font-monospace text-primary" style="background: transparent; letter-spacing: 1px;">{{ $coupon->code }}</td>
                    <td class="py-3" style="background: transparent;">
                        @if($coupon->discount_percentage)
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 rounded-pill">{{ $coupon->discount_percentage }}% OFF</span>
                        @elseif($coupon->discount_amount)
                             <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">${{ $coupon->discount_amount }} OFF</span>
                        @endif
                    </td>
                    <td class="py-3 text-gray-300" style="background: transparent;">{{ $coupon->expires_at ? $coupon->expires_at->format('d/m/Y') : 'Nunca' }}</td>
                    <td class="py-3" style="background: transparent;">
                         @if($coupon->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 rounded-pill">Activo</span>
                         @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 rounded-pill">Inactivo</span>
                         @endif
                    </td>
                    <td class="py-3" style="background: transparent;">
                        <form action="{{ route('super_admin.coupons.toggle', $coupon->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-sm {{ $coupon->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                <i data-lucide="{{ $coupon->is_active ? 'power-off' : 'power' }}" style="width: 14px;"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted" style="background: transparent;">
                        <div class="d-flex flex-column align-items-center">
                            <i data-lucide="ticket" style="width: 48px; height: 48px; opacity: 0.2; margin-bottom: 1rem;"></i>
                            <p class="mb-0">No hay cupones creados</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
     <div class="mt-3">
        {{ $coupons->links() }}
    </div>
</div>

<!-- Create Coupon Modal -->
<div class="modal fade" id="createCouponModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content glass-card border-0">
      <form action="{{ route('super_admin.coupons.create') }}" method="POST">
          @csrf
          <div class="modal-header border-0">
            <h5 class="modal-title">Nuevo Cupón</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Código</label>
                <input type="text" name="code" class="form-control text-uppercase" placeholder="EJ: VERANO30" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Descuento (%)</label>
                    <input type="number" name="discount_percentage" class="form-control" min="1" max="100" placeholder="0-100">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Monto Fijo ($)</label>
                    <input type="number" name="discount_amount" class="form-control" min="0" step="0.01" placeholder="0.00">
                </div>
            </div>
            <div class="form-text mb-3">Completa solo uno de los dos tipos de descuento.</div>
            <div class="mb-3">
                <label class="form-label">Fecha de Expiración (Opcional)</label>
                <input type="date" name="expires_at" class="form-control">
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Crear Cupón</button>
          </div>
      </form>
    </div>
  </div>
</div>
@endsection
