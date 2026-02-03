@extends('layouts.admin')

@section('title', 'Configuración MercadoPago - Sushi Burger Experience')
@section('page_title', 'Configuración MercadoPago')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="glass-card p-4">
            <h3 class="h4 mb-4">Conectar Cuenta de MercadoPago</h3>

            <div class="alert alert-info mb-4" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #93c5fd; padding: 15px; border-radius: 12px;">
                <strong>Información:</strong> Conecta tu cuenta de MercadoPago para recibir pagos de tus pedidos.
                Puedes obtener tus credenciales desde el <a href="https://www.mercadopago.com.uy/developers/panel" target="_blank" style="color: #60a5fa; text-decoration: underline;">Panel de Desarrolladores</a> de MercadoPago.
            </div>

            @if($mpAccount && $mpAccount->isConnected())
                <div class="alert alert-success mb-4" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #6ee7b7; padding: 15px; border-radius: 12px;">
                    <strong>✓ Cuenta Conectada</strong><br>
                    Ambiente: <strong>{{ $mpAccount->environment === 'production' ? 'Producción' : 'Sandbox' }}</strong><br>
                    Conectada el: {{ $mpAccount->connected_at->format('d/m/Y H:i') }}
                </div>
            @endif

            <form id="mpAccountForm">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Access Token *</label>
                    <input type="text" name="access_token" class="form-control-cartify"
                           value="{{ $mpAccount->access_token ?? '' }}"
                           placeholder="APP_USR-..." required>
                    <small class="text-muted">Token de acceso de tu aplicación de MercadoPago</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Public Key *</label>
                    <input type="text" name="public_key" class="form-control-cartify"
                           value="{{ $mpAccount->public_key ?? '' }}"
                           placeholder="APP_USR-..." required>
                    <small class="text-muted">Clave pública de tu aplicación</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">App ID (opcional)</label>
                    <input type="text" name="app_id" class="form-control-cartify"
                           value="{{ $mpAccount->app_id ?? '' }}"
                           placeholder="ID de tu aplicación">
                </div>

                <div class="mb-3">
                    <label class="form-label">User ID (opcional)</label>
                    <input type="text" name="user_id" class="form-control-cartify"
                           value="{{ $mpAccount->user_id ?? '' }}"
                           placeholder="ID de usuario en MercadoPago">
                </div>

                <div class="mb-4">
                    <label class="form-label">Ambiente *</label>
                    <select name="environment" class="form-control-cartify" required>
                        <option value="sandbox" {{ ($mpAccount->environment ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>Sandbox (Pruebas)</option>
                        <option value="production" {{ ($mpAccount->environment ?? '') === 'production' ? 'selected' : '' }}>Producción</option>
                    </select>
                    <small class="text-muted">Usa Sandbox para pruebas y Producción para recibir pagos reales</small>
                </div>

                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-cartify-primary" id="saveBtn">
                        {{ $mpAccount ? 'Actualizar' : 'Conectar' }} Cuenta
                    </button>

                    @if($mpAccount && $mpAccount->isConnected())
                        <button type="button" class="btn btn-cartify-secondary" id="testBtn">
                            Probar Conexión
                        </button>
                        <button type="button" class="btn btn-danger" id="disconnectBtn">
                            Desconectar
                        </button>
                    @endif
                </div>
            </form>
        </div>

        <div class="glass-card p-4 mt-4">
            <h4 class="h5 mb-3">Guía de Configuración</h4>
            <ol style="color: var(--color-text-muted); line-height: 2;">
                <li>Accede al <a href="https://www.mercadopago.com.uy/developers/panel" target="_blank" style="color: var(--color-primary);">Panel de Desarrolladores</a> de MercadoPago</li>
                <li>Crea una nueva aplicación o selecciona una existente</li>
                <li>Copia el <strong>Access Token</strong> y la <strong>Public Key</strong></li>
                <li>Pega las credenciales en el formulario de arriba</li>
                <li>Selecciona el ambiente (Sandbox para pruebas, Producción para pagos reales)</li>
                <li>Haz clic en "Conectar Cuenta"</li>
                <li>Usa "Probar Conexión" para verificar que todo funciona correctamente</li>
            </ol>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="glass-card p-4">
            <h4 class="h5 mb-3">Información Importante</h4>
            <div style="color: var(--color-text-muted); font-size: 0.9rem;">
                <p><strong>Seguridad:</strong> Tus credenciales se almacenan de forma segura y solo se usan para procesar pagos.</p>
                <p><strong>Ambiente Sandbox:</strong> Usa credenciales de prueba para desarrollar sin afectar pagos reales.</p>
                <p><strong>Ambiente Producción:</strong> Requiere credenciales reales y recibirás pagos reales.</p>
                <p><strong>Webhooks:</strong> Los webhooks se configuran automáticamente cuando creas un pedido.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('mpAccountForm');
        const saveBtn = document.getElementById('saveBtn');
        const testBtn = document.getElementById('testBtn');
        const disconnectBtn = document.getElementById('disconnectBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Save/Update account
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            saveBtn.disabled = true;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

            try {
                const response = await fetch('{{ route("admin.mercadopago.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    window.SBESwal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: result.message || 'Cuenta configurada correctamente'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    window.SBESwal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message || 'Error al configurar la cuenta'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.SBESwal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al guardar la configuración'
                });
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            }
        });

        // Test connection
        if (testBtn) {
            testBtn.addEventListener('click', async () => {
                testBtn.disabled = true;
                const originalText = testBtn.innerHTML;
                testBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Probando...';

                try {
                    const response = await fetch('{{ route("admin.mercadopago.test") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        window.SBESwal.fire({
                            icon: 'success',
                            title: '¡Conexión Exitosa!',
                            text: result.message || 'La conexión con MercadoPago funciona correctamente'
                        });
                    } else {
                        window.SBESwal.fire({
                            icon: 'error',
                            title: 'Error de Conexión',
                            text: result.message || 'No se pudo conectar con MercadoPago'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.SBESwal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al probar la conexión'
                    });
                } finally {
                    testBtn.disabled = false;
                    testBtn.innerHTML = originalText;
                }
            });
        }

        // Disconnect account
        if (disconnectBtn) {
            disconnectBtn.addEventListener('click', async () => {
                const result = await window.SBESwal.fire({
                    icon: 'warning',
                    title: '¿Desconectar cuenta?',
                    text: 'Esto desactivará la cuenta de MercadoPago. Los pagos pendientes seguirán funcionando.',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, desconectar',
                    cancelButtonText: 'Cancelar'
                });

                if (result.isConfirmed) {
                    disconnectBtn.disabled = true;
                    const originalText = disconnectBtn.innerHTML;
                    disconnectBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Desconectando...';

                    try {
                        const response = await fetch('{{ route("admin.mercadopago.destroy") }}', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            window.SBESwal.fire({
                                icon: 'success',
                                title: 'Desconectado',
                                text: data.message || 'Cuenta desconectada correctamente'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            window.SBESwal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Error al desconectar la cuenta'
                            });
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        window.SBESwal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al desconectar la cuenta'
                        });
                    } finally {
                        disconnectBtn.disabled = false;
                        disconnectBtn.innerHTML = originalText;
                    }
                }
            });
        }
    });
</script>
@endsection
