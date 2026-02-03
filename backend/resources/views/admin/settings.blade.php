@extends('layouts.admin')

@section('title', 'Configuración - Sushi Burger Experience')
@section('page_title', 'Configuración')

@section('content')
<div class="row">
    <div class="col-12 col-lg-8">
        <div class="p-4 p-md-5 rounded-4" style="background: var(--color-surface); border: 1px solid var(--color-border);">
            <h3 class="h5 fw-bold mb-4 pb-3 border-bottom" style="border-color: var(--color-border) !important;">General</h3>

            <form id="settingsForm">
                <div class="row g-4 align-items-center mb-4">
                    <div class="col-md-4">
                        <label class="form-label mb-md-0">Nombre del Negocio</label>
                    </div>
                    <div class="col-md-8">
                        <input type="text" class="form-control-cartify w-100" name="name" id="companyName" value="{{ $company->name ?? '' }}" required>
                    </div>
                </div>

                <div class="row g-4 align-items-center mb-4 text-white">
                    <div class="col-md-4">
                        <label class="form-label mb-md-0">Moneda</label>
                    </div>
                    <div class="col-md-8">
                        <select class="form-control-cartify w-100 bg-black text-white" name="currency" id="companyCurrency" required>
                            @php
                                $currentCurrency = $company->currency ?? 'UYU';
                            @endphp
                            <option value="UYU" {{ $currentCurrency === 'UYU' ? 'selected' : '' }}>UYU ($)</option>
                            <option value="USD" {{ $currentCurrency === 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="ARS" {{ $currentCurrency === 'ARS' ? 'selected' : '' }}>ARS ($)</option>
                            <option value="EUR" {{ $currentCurrency === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                        </select>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-top text-end" style="border-color: var(--color-border) !important;">
                    <button type="submit" class="btn btn-cartify-primary px-5" id="saveSettingsBtn">
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('settingsForm');
        const saveBtn = document.getElementById('saveSettingsBtn');
        const apiBase = '/dashboard-api';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = {
                name: document.getElementById('companyName').value,
                currency: document.getElementById('companyCurrency').value,
            };

            // Disable button and show loading state
            saveBtn.disabled = true;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            try {
                const response = await fetch(`${apiBase}/company`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (response.ok) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'Configuración guardada exitosamente'
                    });
                } else {
                    window.CartifySwal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Error al guardar la configuración'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Error de conexión al servidor'
                });
            } finally {
                // Re-enable button
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        });
    });
</script>
@endsection
