@extends('layouts.admin')

@section('title', 'Gestión de Usuarios')
@section('page_title', 'Usuarios')

@section('content')
<div class="glass-card p-4">
    <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0" style="background: transparent;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Usuario</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Email</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Compañía</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Plan Actual</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Estado</th>
                    <th class="py-3 text-muted fw-medium text-uppercase small" style="background: transparent;">Acciones</th>
                </tr>
            </thead>
            <tbody style="border-top: none;">
                @foreach($users as $user)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td class="py-3" style="background: transparent;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="profile-circle bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            <span class="fw-bold text-white">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 text-gray-300" style="background: transparent;">{{ $user->email }}</td>
                    <td class="py-3 text-gray-300" style="background: transparent;">{{ $user->company->name ?? 'N/A' }}</td>
                    <td class="py-3" style="background: transparent;">
                        @if($user->company && $user->company->currentPlan)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                {{ $user->company->currentPlan->name }}
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3">N/A</span>
                        @endif
                    </td>
                    <td class="py-3" style="background: transparent;">
                        @if($user->is_blocked)
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">Bloqueado</span>
                        @else
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">Activo</span>
                        @endif
                    </td>
                    <td class="py-3" style="background: transparent;">
                         <button class="btn btn-sm btn-cartify-primary" onclick="openAssignPlanModal({{ $user->id }}, '{{ $user->name }}')">
                            <i data-lucide="edit-2" style="width: 14px; margin-right: 4px;"></i> Plan
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3">
        {{ $users->links() }}
    </div>
</div>

<!-- Assign Plan Modal -->
<div class="modal fade" id="assignPlanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content glass-card border-0">
      <form id="assignPlanForm" method="POST">
          @csrf
          <div class="modal-header border-0">
            <h5 class="modal-title">Asignar Plan Manualmente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p>Asignar plan a: <strong id="userNameDisplay"></strong></p>
            <div class="mb-3">
                <label class="form-label">Seleccionar Plan</label>
                <select name="plan_id" class="form-select form-control-cartify text-white">
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" class="text-dark">{{ $plan->name }} ({{ $plan->price }})</option>
                    @endforeach
                </select>
                <div class="form-text text-warning mt-2">
                    <i data-lucide="alert-triangle" style="width: 14px;"></i> Esta acción cancelará cualquier suscripción activa y asignará el plan sin costo (bypass de pago).
                </div>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary">Asignar Plan</button>
          </div>
      </form>
    </div>
  </div>
</div>

@section('scripts')
<script>
    function openAssignPlanModal(userId, userName) {
        const form = document.getElementById('assignPlanForm');
        form.action = `/super-admin/users/${userId}/assign-plan`;
        document.getElementById('userNameDisplay').textContent = userName;
        const modal = new bootstrap.Modal(document.getElementById('assignPlanModal'));
        modal.show();
    }
</script>
@endsection

@endsection
