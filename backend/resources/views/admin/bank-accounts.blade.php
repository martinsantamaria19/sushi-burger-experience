@extends('layouts.admin')

@section('title', 'Cuentas Bancarias - Sushi Burger Experience')
@section('page_title', 'Cuentas Bancarias')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">Configurá las cuentas para recibir transferencias. Se muestran al cliente cuando elige pago por transferencia.</p>
    </div>
    @if($activeRestaurant)
    <button class="btn btn-cartify-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#bankAccountModal" onclick="openCreateModal()">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Nueva Cuenta
    </button>
    @endif
</div>

@if(!$activeRestaurant)
<div class="glass-card p-5 text-center">
    <i data-lucide="building-2" class="mb-3" style="width: 48px; height: 48px; color: var(--color-primary-light);"></i>
    <p class="text-muted mb-0">Seleccioná un restaurante para gestionar sus cuentas bancarias.</p>
    <a href="{{ route('admin.restaurants') }}" class="btn btn-cartify-primary mt-3">Ir a Restaurantes</a>
</div>
@else
<p class="text-muted small mb-3"><strong>Restaurante en gestión:</strong> {{ $activeRestaurant->name }}</p>

@if($bankAccounts->isEmpty())
<div class="glass-card p-5 text-center">
    <i data-lucide="landmark" class="mb-3" style="width: 64px; height: 64px; color: var(--color-primary-light);"></i>
    <h3 class="h5 mb-2">Sin cuentas bancarias</h3>
    <p class="text-muted mb-4">Agregá al menos una cuenta para que los clientes puedan pagar por transferencia.</p>
    <button class="btn btn-cartify-primary" data-bs-toggle="modal" data-bs-target="#bankAccountModal" onclick="openCreateModal()">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Agregar cuenta
    </button>
</div>
@else
<div class="row g-4" id="bankAccountList">
    @foreach($bankAccounts as $acc)
    <div class="col-12 col-md-6 col-xl-4" data-id="{{ $acc->id }}">
        <div class="glass-card p-4 h-100" style="border: 1px solid var(--color-border);">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                        <i data-lucide="landmark" style="width: 20px;"></i>
                    </div>
                    <div>
                        <strong>{{ $acc->bank_name }}</strong>
                        <span class="badge {{ $acc->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 text-{{ $acc->is_active ? 'success' : 'muted' }} small ms-2">{{ $acc->is_active ? 'Activa' : 'Inactiva' }}</span>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-outline-light border-opacity-10 px-2" onclick="openEditModal({{ json_encode($acc) }})" title="Editar"><i data-lucide="pencil" style="width: 14px;"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger border-opacity-10 px-2" onclick="deleteAccount({{ $acc->id }}, '{{ addslashes($acc->bank_name) }}')" title="Eliminar"><i data-lucide="trash-2" style="width: 14px;"></i></button>
                </div>
            </div>
            <div class="small text-muted">
                <div>{{ $acc->account_type_label }} · {{ $acc->account_holder }}</div>
                <div>Nº {{ $acc->account_number }}</div>
                <div>{{ $acc->currency }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- Modal Nueva / Editar Cuenta -->
<div class="modal fade" id="bankAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="bankModalTitle">Nueva Cuenta Bancaria</h5>
            </div>
            <form id="bankAccountForm" class="p-4">
                <input type="hidden" name="id" id="baId">
                <div class="mb-3">
                    <label class="form-label">Banco *</label>
                    <input type="text" name="bank_name" id="baBankName" class="form-control-cartify w-100" placeholder="Ej: BROU, Itaú" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo de cuenta *</label>
                    <select name="account_type" id="baAccountType" class="form-control-cartify w-100" required>
                        <option value="checking">Cuenta Corriente</option>
                        <option value="savings">Caja de Ahorros</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Número de cuenta *</label>
                    <input type="text" name="account_number" id="baAccountNumber" class="form-control-cartify w-100" placeholder="0001234567" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Titular de la cuenta *</label>
                    <input type="text" name="account_holder" id="baAccountHolder" class="form-control-cartify w-100" placeholder="Nombre o razón social" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Moneda</label>
                    <select name="currency" id="baCurrency" class="form-control-cartify w-100">
                        <option value="UYU" selected>UYU</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Instrucciones para el cliente (opcional)</label>
                    <textarea name="instructions" id="baInstructions" class="form-control-cartify w-100" rows="2" placeholder="Ej: Enviar comprobante por WhatsApp"></textarea>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="baIsActive" checked>
                    <label class="form-check-label text-muted small">Mostrar esta cuenta a los clientes</label>
                </div>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="baSubmitBtn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
(function() {
    const apiBase = '/dashboard-api';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    let bankModal;

    function openCreateModal() {
        document.getElementById('bankAccountForm').reset();
        document.getElementById('baId').value = '';
        document.getElementById('baCurrency').value = 'UYU';
        document.getElementById('baIsActive').checked = true;
        document.getElementById('bankModalTitle').textContent = 'Nueva Cuenta Bancaria';
        document.getElementById('baSubmitBtn').textContent = 'Guardar';
    }

    function openEditModal(acc) {
        document.getElementById('baId').value = acc.id;
        document.getElementById('baBankName').value = acc.bank_name || '';
        document.getElementById('baAccountType').value = acc.account_type || 'checking';
        document.getElementById('baAccountNumber').value = acc.account_number || '';
        document.getElementById('baAccountHolder').value = acc.account_holder || '';
        document.getElementById('baCurrency').value = acc.currency || 'UYU';
        document.getElementById('baInstructions').value = acc.instructions || '';
        document.getElementById('baIsActive').checked = acc.is_active !== false;
        document.getElementById('bankModalTitle').textContent = 'Editar Cuenta';
        document.getElementById('baSubmitBtn').textContent = 'Guardar cambios';
        if (bankModal) bankModal.show();
    }

    async function deleteAccount(id, name) {
        if (!confirm('¿Eliminar la cuenta "' + name + '"?')) return;
        try {
            const r = await fetch(apiBase + '/bank-accounts/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
            });
            const data = await r.json();
            if (r.ok) {
                document.querySelector('[data-id="' + id + '"]')?.remove();
                if (document.querySelectorAll('#bankAccountList [data-id]').length === 0) location.reload();
            } else {
                alert(data.message || 'Error al eliminar');
            }
        } catch (e) {
            alert('Error de conexión');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('bankAccountModal');
        if (modalEl) bankModal = new bootstrap.Modal(modalEl);

        const form = document.getElementById('bankAccountForm');
        if (form) {
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const id = document.getElementById('baId').value;
                const payload = {
                    bank_name: document.getElementById('baBankName').value.trim(),
                    account_type: document.getElementById('baAccountType').value,
                    account_number: document.getElementById('baAccountNumber').value.trim(),
                    account_holder: document.getElementById('baAccountHolder').value.trim(),
                    currency: document.getElementById('baCurrency').value,
                    instructions: document.getElementById('baInstructions').value.trim() || null,
                    is_active: document.getElementById('baIsActive').checked
                };

                const url = id ? apiBase + '/bank-accounts/' + id : apiBase + '/bank-accounts';
                const method = id ? 'PUT' : 'POST';

                try {
                    const r = await fetch(url, {
                        method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await r.json();
                    if (r.ok) {
                        if (bankModal) bankModal.hide();
                        location.reload();
                    } else {
                        alert(data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Error al guardar'));
                    }
                } catch (err) {
                    alert('Error de conexión');
                }
            });
        }

        window.openEditModal = openEditModal;
        window.deleteAccount = deleteAccount;
        window.openCreateModal = openCreateModal;

        lucide.createIcons();
    });
})();
</script>
@endsection
