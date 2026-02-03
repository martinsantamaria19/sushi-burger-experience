@extends('layouts.admin')

@section('title', 'Códigos QR - Cartify')
@section('page_title', 'Mis Códigos QR')

@section('content')
<div class="restaurants-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">Crea códigos QR únicos para rastrear el origen de tus clientes (Mesa 1, Flyer Calle, etc.)</p>
    </div>
    <button class="btn btn-cartify-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#qrModal" onclick="openCreateQrModal()">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Nuevo Código QR
    </button>
</div>

<div class="row g-4" id="qrList">
    <!-- QR Cards will appear here -->
    <div class="col-12 text-center py-5" id="loadingState">
        <div class="spinner-border text-primary" role="status"></div>
    </div>
</div>

<!-- QR Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="qrModalTitle">Nuevo Código QR</h5>
            </div>
            <form id="qrForm" class="p-4" method="POST" action="#">
                <div class="mb-3">
                    <label class="form-label">Nombre del Código QR</label>
                    <input type="text" name="name" id="qrName" class="form-control-cartify w-100" placeholder="Ej: Mesa 1, Flyer Calle, etc." required>
                    <small class="text-muted">Este nombre te ayudará a identificar el origen de los escaneos</small>
                </div>
                <div class="mb-4">
                    <label class="form-label">Restaurante</label>
                    <select name="restaurant_id" id="qrRestaurant" class="form-control-cartify w-100" required>
                        <option value="">Selecciona un restaurante</option>
                        @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" {{ ($activeRestaurant && $restaurant->id == $activeRestaurant->id) ? 'selected' : '' }}>
                            {{ $restaurant->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="qrSubmitBtn">Generar QR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    (function() {
        const apiBase = "/dashboard-api/qrcodes";
        const scanUrlBase = "{{ url('/scan') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let qrModal;

        function openCreateQrModal() {
            const form = document.getElementById('qrForm');
            if (form) form.reset();
            const qrModalTitle = document.getElementById('qrModalTitle');
            if (qrModalTitle) qrModalTitle.innerText = 'Nuevo Código QR';
            const qrSubmitBtn = document.getElementById('qrSubmitBtn');
            if (qrSubmitBtn) qrSubmitBtn.innerText = 'Generar QR';
            const qrRestaurant = document.getElementById('qrRestaurant');
            if (qrRestaurant) {
                const activeRestaurantId = {{ $activeRestaurant ? $activeRestaurant->id : 'null' }};
                qrRestaurant.value = activeRestaurantId || '';
            }
        }

        async function loadQrs() {
            try {
                // Load QRs for all restaurants of the user
                const restaurants = @json($restaurants);
                const allQrs = [];

                for (const restaurant of restaurants) {
                    try {
                        const response = await fetch(`${apiBase}?restaurant_id=${restaurant.id}`);
                        const qrs = await response.json();
                        allQrs.push(...qrs);
                    } catch (error) {
                        console.error(`Error loading QRs for restaurant ${restaurant.id}:`, error);
                    }
                }

                // Sort by created_at desc
                allQrs.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                renderQrs(allQrs);
            } catch (error) {
                console.error('Error loading QRs:', error);
            }
        }

        function renderQrs(qrs) {
            const list = document.getElementById('qrList');
            const loadingState = document.getElementById('loadingState');
            if (loadingState) loadingState.style.display = 'none';

            if (qrs.length === 0) {
                list.innerHTML = `
                    <div class="col-12 text-center py-5 opacity-50">
                        <i data-lucide="qr-code" style="width: 48px; height: 48px;" class="mb-3"></i>
                        <p>No tienes códigos QR creados aún.</p>
                    </div>
                `;
                lucide.createIcons();
                return;
            }

            list.innerHTML = qrs.map(qr => `
                <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                    <div class="glass-card p-3 h-100 text-center">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3 py-2 border border-primary border-opacity-10">
                                ${qr.scans_count || 0} escaneos
                            </span>
                            <button class="icon-btn text-danger" onclick="deleteQr('${qr.id}', '${qr.name.replace(/'/g, "\\'")}')" title="Eliminar">
                                <i data-lucide="trash-2" style="width: 18px;"></i>
                            </button>
                        </div>

                        <div class="qr-container bg-white p-3 rounded-4 mx-auto mb-3" id="qrcode-${qr.id}" style="width: fit-content;"></div>

                        <h4 class="h6 fw-bold mb-1">${qr.name}</h4>
                        <p class="small text-muted mb-2">${qr.restaurant?.name || 'Sin restaurante'}</p>
                        <p class="small text-muted mb-3">
                            <a href="${scanUrlBase}/${qr.redirect_slug}" target="_blank" class="text-decoration-none text-primary" style="word-break: break-all;">
                                ${scanUrlBase}/${qr.redirect_slug}
                            </a>
                        </p>

                        <div class="d-flex gap-2">
                            <button class="btn btn-cartify-secondary w-100 py-2 d-flex align-items-center justify-content-center gap-2" onclick="downloadQr('${qr.id}', '${qr.name.replace(/'/g, "\\'")}')">
                                <i data-lucide="download" style="width: 16px;"></i> Descargar
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');

            // Generate actual QRs
            qrs.forEach(qr => {
                const container = document.getElementById(`qrcode-${qr.id}`);
                if (container) {
                    container.innerHTML = ''; // Clear previous content
                    new QRCode(container, {
                        text: `${scanUrlBase}/${qr.redirect_slug}`,
                        width: 150,
                        height: 150,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                }
            });

            lucide.createIcons();
        }

        async function deleteQr(id, name) {
            const result = await window.CartifySwal.fire({
                icon: 'warning',
                title: '¿Eliminar Código QR?',
                html: `
                    <p class="mb-3">Estás a punto de eliminar el código QR <strong>"${name}"</strong>.</p>
                    <p class="text-danger mb-0"><strong>Advertencia:</strong> Esta acción eliminará:</p>
                    <ul class="text-center mt-2 mb-0" style="list-style: none; padding-left: 0;">
                        <li>• El código QR y su configuración</li>
                        <li>• Todos los datos de escaneos asociados</li>
                        <li>• El historial de rastreo</li>
                    </ul>
                    <p class="mt-3 mb-0"><strong>Esta acción no se puede deshacer.</strong></p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'cartify-swal-popup',
                    title: 'cartify-swal-title',
                    confirmButton: 'btn-delete-danger px-4 py-2',
                    cancelButton: 'btn-cartify-secondary px-4 py-2',
                    actions: 'swal2-actions-gap'
                },
                buttonsStyling: false
            });

            if (result.isConfirmed) {
                try {
                    const response = await fetch(`${apiBase}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        await window.CartifySwal.fire({
                            icon: 'success',
                            title: 'Código QR eliminado',
                            text: 'El código QR ha sido eliminado correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        loadQrs();
                    } else {
                        const responseData = await response.json();
                        await window.CartifySwal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: responseData.message || 'No se pudo eliminar el código QR.'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    await window.CartifySwal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.'
                    });
                }
            }
        }

        function downloadQr(id, name) {
            const canvas = document.querySelector(`#qrcode-${id} canvas`);
            const img = document.querySelector(`#qrcode-${id} img`);
            const link = document.createElement('a');
            link.download = `QR-Cartify-${name.replace(/\s+/g, '-')}.png`;

            if (canvas) {
                link.href = canvas.toDataURL("image/png");
            } else if (img) {
                link.href = img.src;
            }

            link.click();
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('qrModal');
            if (modalElement) {
                qrModal = new bootstrap.Modal(modalElement);
            }

            const form = document.getElementById('qrForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const btn = document.getElementById('qrSubmitBtn');
                    const originalText = btn.innerText;
                    btn.disabled = true;
                    btn.innerText = 'Generando...';

                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData.entries());

                    try {
                        const response = await fetch(apiBase, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const responseData = await response.json();

                        if (response.ok) {
                            if (qrModal) {
                                qrModal.hide();
                            }
                            form.reset();
                            await window.CartifySwal.fire({
                                icon: 'success',
                                title: 'Código QR creado',
                                text: 'El código QR ha sido generado correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            loadQrs();
                        } else {
                            // Handle subscription limit errors
                            if (response.status === 403 && responseData.error_code === 'SUBSCRIPTION_LIMIT_EXCEEDED') {
                                if (window.showSubscriptionLimitModal) {
                                    window.showSubscriptionLimitModal(responseData);
                                } else {
                                    await window.CartifySwal.fire({
                                        icon: 'warning',
                                        title: 'Límite de Plan Alcanzado',
                                        text: responseData.message || 'Has alcanzado el límite de códigos QR permitidos en tu plan.',
                                        showCancelButton: true,
                                        confirmButtonText: 'Ver Planes',
                                        cancelButtonText: 'Cancelar',
                                        confirmButtonColor: '#7c3aed',
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location.href = responseData.upgrade_url || '{{ route("admin.subscription") }}';
                                        }
                                    });
                                }
                            } else {
                                let errorMessage = 'Error al crear el código QR';
                                if (responseData.message) {
                                    errorMessage = responseData.message;
                                } else if (responseData.errors) {
                                    const errors = Object.values(responseData.errors).flat();
                                    errorMessage = errors.join('\n');
                                }
                                await window.CartifySwal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        await window.CartifySwal.fire({
                            icon: 'error',
                            title: 'Error de conexión',
                            text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.'
                        });
                    } finally {
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }

                    return false;
                });
            }

            // Make functions globally available
            window.openCreateQrModal = openCreateQrModal;
            window.deleteQr = deleteQr;
            window.downloadQr = downloadQr;

            // Initial load
            loadQrs();
        });
    })();
</script>
@endsection

@section('styles')
<style>
    .qr-container img {
        display: block;
        max-width: 100%;
    }
    .swal2-actions-gap {
        gap: 1rem !important;
        display: flex !important;
    }
    .swal2-actions-gap .swal2-confirm,
    .swal2-actions-gap .swal2-cancel {
        margin: 0 !important;
    }
    .btn-delete-danger {
        background: #dc3545 !important;
        color: white !important;
        border-radius: 9999px !important;
        font-weight: 600 !important;
        border: none !important;
        transition: all 0.2s ease !important;
        box-shadow: 0 0 20px rgba(220, 53, 69, 0.3) !important;
    }
    .btn-delete-danger:hover {
        background: #c82333 !important;
        color: white !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 0 30px rgba(220, 53, 69, 0.5) !important;
    }

    /* Estilos para el select */
    .form-control-cartify select,
    select.form-control-cartify {
        background: rgba(0, 0, 0, 0.2) !important;
        border: 1px solid var(--color-border) !important;
        color: white !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23ffffff' d='M6 9L1 4h10z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.75rem center !important;
        background-size: 12px !important;
        padding-right: 2.5rem !important;
        appearance: none !important;
        -webkit-appearance: none !important;
        -moz-appearance: none !important;
    }

    .form-control-cartify select:focus,
    select.form-control-cartify:focus {
        background: rgba(124, 58, 237, 0.05) !important;
        border-color: var(--color-primary) !important;
        color: white !important;
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1) !important;
        outline: none !important;
    }

    .form-control-cartify select option,
    select.form-control-cartify option {
        background: var(--color-surface) !important;
        color: white !important;
        padding: 0.5rem !important;
    }

    .form-control-cartify select option:checked,
    select.form-control-cartify option:checked {
        background: var(--color-primary) !important;
        color: white !important;
    }

    .form-control-cartify select option:hover,
    select.form-control-cartify option:hover {
        background: rgba(124, 58, 237, 0.2) !important;
    }
</style>
@endsection
