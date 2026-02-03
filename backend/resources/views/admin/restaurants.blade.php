@extends('layouts.admin')

@section('title', 'Mis Restaurantes - Sushi Burger Experience')
@section('page_title', 'Mis Restaurantes')

@section('content')
<div class="restaurants-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">Gestiona tus locales y sus configuraciones individuales.</p>
    </div>
    <button class="btn btn-cartify-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#restaurantModal" onclick="openCreateModal()">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Nuevo Restaurante
    </button>
</div>

@if($restaurants->count() > 0)
<div class="row g-4">
    @foreach($restaurants as $res)
    <div class="col-12 col-md-6 col-xl-4">
        <div class="glass-card p-4 h-100 d-flex flex-column transition-all hover-glow restaurant-card"
             style="border: 1px solid {{ ($activeRestaurant && $res->id == $activeRestaurant->id) ? 'var(--color-primary)' : 'var(--color-border)' }};">

            <div class="restaurant-card-header d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                        <i data-lucide="building-2"></i>
                    </div>
                    <div>
                        <h3 class="h5 fw-bold mb-1">{{ $res->name }}</h3>
                        <span class="badge {{ $res->is_active ? 'bg-success' : 'bg-secondary' }} bg-opacity-10 text-{{ $res->is_active ? 'success' : 'muted' }} small">
                            {{ $res->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>
                @if($activeRestaurant && $res->id == $activeRestaurant->id)
                <span class="badge bg-primary bg-opacity-25 text-primary-light small border border-primary border-opacity-25">En gestión</span>
                @endif
            </div>

            <p class="text-muted small mb-4 flex-grow-1">
                <i data-lucide="map-pin" class="me-1" style="width: 14px;"></i>
                {{ $res->address ?? 'Sin dirección' }}
            </p>

            <div class="restaurant-card-actions d-flex gap-2">
                @if(!$activeRestaurant || $res->id != $activeRestaurant->id)
                <form action="{{ route('admin.restaurants.switch') }}" method="POST" class="flex-grow-1">
                    @csrf
                    <input type="hidden" name="restaurant_id" value="{{ $res->id }}">
                    <button type="submit" class="btn btn-sm btn-cartify-secondary w-100 py-2">
                        Administrar
                    </button>
                </form>
                @else
                <a href="{{ route('admin.menu') }}" class="btn btn-sm btn-cartify-primary flex-grow-1 py-2">
                    Ir al Menú
                </a>
                @endif

                <button class="btn btn-sm btn-outline-light border-opacity-10 px-3" onclick="openEditModal({{ json_encode($res) }})" title="Editar">
                    <i data-lucide="settings" style="width: 16px;"></i>
                </button>

                <button class="btn btn-sm btn-outline-danger border-opacity-10 px-3" onclick="deleteRestaurant({{ $res->id }}, '{{ addslashes($res->name) }}')" title="Eliminar">
                    <i data-lucide="trash-2" style="width: 16px;"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="p-5 rounded-4 text-center" style="background: var(--color-surface); border: 1px solid var(--color-border);">
    <div class="mb-4">
        <i data-lucide="building-2" class="mb-3" style="width: 64px; height: 64px; color: var(--color-primary-light);"></i>
        <h2 class="h4 fw-bold mb-2">Crea tu primer restaurante</h2>
        <p class="text-muted mx-auto" style="max-width: 500px;">Comienza agregando tu primer local para gestionar menús, códigos QR y configuraciones personalizadas.</p>
    </div>
    <button class="btn btn-cartify-primary px-5 py-3" data-bs-toggle="modal" data-bs-target="#restaurantModal" onclick="openCreateModal()">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Crear mi primer restaurante
    </button>
</div>
@endif

<!-- Restaurant Modal -->
<div class="modal fade" id="restaurantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="modalTitle">Nuevo Restaurante</h5>
            </div>
            <form id="restaurantForm" class="p-4" method="POST" action="#">
                <input type="hidden" name="id" id="resId">
                <div class="mb-3">
                    <label class="form-label">Nombre del Restaurante</label>
                    <input type="text" name="name" id="resName" class="form-control-cartify w-100" placeholder="Ej: Pizzería Dante" required oninput="generateSlug(this.value)">
                </div>
                <div class="mb-3">
                    <label class="form-label">URL Friendly (Slug)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--color-border); color: var(--color-text-muted);">sushiburger.uy/</span>
                        <input type="text" name="slug" id="resSlug" class="form-control-cartify border-start-0 w-100" placeholder="pizzeria-dante" required>
                    </div>
                    <div id="slugError" class="text-danger small mt-2" style="display: none;"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" id="resAddress" class="form-control-cartify w-100" placeholder="Introduzca la dirección de su restaurante">
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" name="is_active" id="resActive" checked>
                    <label class="form-check-label text-muted small">Restaurante visible al público</label>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="submitBtn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
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
    }

    /* Mobile optimizations */
    @media (max-width: 991.98px) {
        /* Restaurant cards padding */
        .restaurant-card {
            padding: 1.5rem !important;
        }

        /* Restaurant card header */
        .restaurant-card-header {
            flex-direction: column;
            gap: 0.75rem;
            align-items: flex-start !important;
        }

        .restaurant-card-header > .badge {
            align-self: flex-end;
            margin-top: 0.5rem;
        }

        /* Restaurant card actions */
        .restaurant-card-actions {
            flex-direction: column;
            gap: 0.75rem !important;
            width: 100%;
        }

        .restaurant-card-actions form,
        .restaurant-card-actions > a,
        .restaurant-card-actions > button {
            width: 100% !important;
            margin: 0 !important;
            min-width: 100% !important;
            flex: none !important;
        }

        .restaurant-card-actions form {
            width: 100%;
        }

        .restaurant-card-actions form .btn {
            width: 100%;
        }

        /* Modal adjustments */
        .modal-dialog {
            margin: 1rem;
        }

        .modal-content {
            border-radius: 16px !important;
        }
    }

    /* Estilos para mensaje de error del slug */
    #slugError {
        font-size: 0.875rem;
        margin-top: 0.5rem;
        color: #dc3545 !important;
        display: none;
    }

    #resSlug.is-invalid,
    #resSlug.is-invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1) !important;
    }

    .input-group #resSlug.is-invalid {
        border-left-color: #dc3545 !important;
    }
</style>
@endsection

@section('scripts')
<script>
    (function() {
        const apiBase = '/dashboard-api';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let restaurantModal;

        function generateSlug(text) {
            const resId = document.getElementById('resId');
            if (resId && resId.value) return; // No auto-slug on edit
            const slug = text.toLowerCase()
                .replace(/[^\w ]+/g, '')
                .replace(/ +/g, '-');
            const resSlug = document.getElementById('resSlug');
            if (resSlug) resSlug.value = slug;
            // Limpiar error cuando se cambia el slug
            clearSlugError();
        }

        function showSlugError(message) {
            const slugError = document.getElementById('slugError');
            const resSlug = document.getElementById('resSlug');
            if (slugError) {
                slugError.textContent = message;
                slugError.style.display = 'block';
            }
            if (resSlug) {
                resSlug.style.borderColor = '#dc3545';
                resSlug.classList.add('is-invalid');
            }
        }

        function clearSlugError() {
            const slugError = document.getElementById('slugError');
            const resSlug = document.getElementById('resSlug');
            if (slugError) {
                slugError.textContent = '';
                slugError.style.display = 'none';
            }
            if (resSlug) {
                resSlug.style.borderColor = '';
                resSlug.classList.remove('is-invalid');
            }
        }

        function openCreateModal() {
            const form = document.getElementById('restaurantForm');
            if (form) form.reset();
            const resId = document.getElementById('resId');
            if (resId) resId.value = '';
            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.innerText = 'Nuevo Restaurante';
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) submitBtn.innerText = 'Crear Restaurante';
            const resActive = document.getElementById('resActive');
            if (resActive) resActive.checked = true;
            // Limpiar errores
            clearSlugError();
        }

        function openEditModal(res) {
            const resId = document.getElementById('resId');
            if (resId) resId.value = res.id;
            const resName = document.getElementById('resName');
            if (resName) resName.value = res.name || '';
            const resSlug = document.getElementById('resSlug');
            if (resSlug) resSlug.value = res.slug || '';
            const resAddress = document.getElementById('resAddress');
            if (resAddress) resAddress.value = res.address || '';
            const resActive = document.getElementById('resActive');
            if (resActive) resActive.checked = res.is_active !== false;

            const modalTitle = document.getElementById('modalTitle');
            if (modalTitle) modalTitle.innerText = 'Configurar Restaurante';
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) submitBtn.innerText = 'Guardar Cambios';
            // Limpiar errores
            clearSlugError();
            if (restaurantModal) restaurantModal.show();
        }

        async function deleteRestaurant(id, name) {
            const result = await window.CartifySwal.fire({
                icon: 'warning',
                title: '¿Eliminar Restaurante?',
                html: `
                    <p class="mb-3">Estás a punto de eliminar el restaurante <strong>"${name}"</strong>.</p>
                    <p class="text-danger mb-0"><strong>Advertencia:</strong> Esta acción eliminará permanentemente:</p>
                    <ul class="text-center mt-2 mb-0" style="list-style: none; padding-left: 0;">
                        <li>• Todos los productos asociados</li>
                        <li>• Todas las categorías del menú</li>
                        <li>• Todos los códigos QR generados</li>
                        <li>• Toda la configuración y personalización</li>
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
                    const response = await fetch(`${apiBase}/restaurants/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const responseData = await response.json();

                    if (response.ok) {
                        await window.CartifySwal.fire({
                            icon: 'success',
                            title: 'Restaurante eliminado',
                            text: 'El restaurante ha sido eliminado correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        location.reload();
                    } else {
                        await window.CartifySwal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: responseData.message || 'No se pudo eliminar el restaurante.'
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

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('restaurantModal');
            if (modalElement) {
                restaurantModal = new bootstrap.Modal(modalElement);
            }

            const form = document.getElementById('restaurantForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    console.log('Form submitted');

                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData.entries());
                    const resActive = document.getElementById('resActive');
                    data.is_active = resActive ? resActive.checked : true;
                    const id = data.id || '';

                    // Remove empty id for new restaurants
                    if (!id) {
                        delete data.id;
                    }

                    const method = id ? 'PUT' : 'POST';
                    const url = id ? `${apiBase}/restaurants/${id}` : `${apiBase}/restaurants`;

                    console.log('Sending request:', method, url, data);

                    try {
                        const response = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(data)
                        });

                        const responseData = await response.json();
                        console.log('Response:', response.status, responseData);

                        if (response.ok) {
                            if (restaurantModal) {
                                restaurantModal.hide();
                            }
                            setTimeout(() => {
                                location.reload();
                            }, 100);
                        } else {
                            // Handle subscription limit errors
                            if (response.status === 403 && responseData.error_code === 'SUBSCRIPTION_LIMIT_EXCEEDED') {
                                if (window.showSubscriptionLimitModal) {
                                    window.showSubscriptionLimitModal(responseData);
                                } else {
                                    alert(responseData.message || 'Has alcanzado el límite de restaurantes permitidos en tu plan.');
                                }
                            } else {
                                // Handle validation errors
                                let errorMessage = 'Error al guardar';
                                let slugError = null;

                                if (responseData.errors) {
                                    // Buscar error específico del slug
                                    if (responseData.errors.slug) {
                                        let errorMsg = Array.isArray(responseData.errors.slug)
                                            ? responseData.errors.slug[0]
                                            : responseData.errors.slug;
                                        // Traducir mensajes comunes en inglés a español
                                        if (errorMsg.includes('has already been taken') || errorMsg.includes('already been taken')) {
                                            slugError = 'Este slug ya está en uso. Por favor, elige otro.';
                                        } else {
                                            slugError = errorMsg;
                                        }
                                    }
                                    // Si hay otros errores además del slug, mostrarlos
                                    const otherErrors = Object.entries(responseData.errors)
                                        .filter(([key]) => key !== 'slug')
                                        .map(([key, value]) => Array.isArray(value) ? value.join(', ') : value);
                                    if (otherErrors.length > 0) {
                                        errorMessage = otherErrors.join('\n');
                                    }
                                } else if (responseData.message) {
                                    // Verificar si el mensaje es sobre el slug
                                    const message = responseData.message.toLowerCase();
                                    if (message.includes('slug') || message.includes('url friendly') || message.includes('ya está en uso')) {
                                        slugError = responseData.message;
                                    } else {
                                        errorMessage = responseData.message;
                                    }
                                }

                                // Mostrar error del slug debajo del campo
                                if (slugError) {
                                    showSlugError(slugError);
                                }

                                // Mostrar otros errores con alert solo si no hay error de slug o si hay otros errores
                                if (!slugError || (responseData.errors && Object.keys(responseData.errors).length > 1)) {
                                    if (errorMessage !== 'Error al guardar' || !slugError) {
                                        alert(errorMessage);
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error de conexión. Por favor, intenta nuevamente.');
                    }

                    return false;
                });
            }

            // Agregar listener al campo slug para limpiar error cuando se escribe
            const resSlug = document.getElementById('resSlug');
            if (resSlug) {
                resSlug.addEventListener('input', function() {
                    clearSlugError();
                });
            }

            // Make functions globally available
            window.generateSlug = generateSlug;
            window.openCreateModal = openCreateModal;
            window.openEditModal = openEditModal;
            window.deleteRestaurant = deleteRestaurant;

            lucide.createIcons();
        });
    })();
</script>
@endsection
