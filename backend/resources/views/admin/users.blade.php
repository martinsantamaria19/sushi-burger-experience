@extends('layouts.admin')

@section('title', 'Usuarios - Cartify')
@section('page_title', 'Usuarios del Sistema')

@section('content')
<div class="restaurants-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">Gestiona los usuarios de tu cuenta y sus permisos.</p>
    </div>
    @if(Auth::user()->is_owner)
    <button class="btn btn-cartify-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openCreateUserModal()">
        <i data-lucide="user-plus" class="me-2" style="width: 18px;"></i>
        Nuevo Usuario
    </button>
    @endif
</div>

<div class="rounded-4 overflow-hidden border" style="background: var(--color-surface); border-color: var(--color-border) !important;">
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0 align-middle">
            <thead class="text-muted small text-uppercase">
                <tr>
                    <th class="px-4 py-3 border-0">Usuario</th>
                    <th class="px-4 py-3 border-0">Email</th>
                    <th class="px-4 py-3 border-0">Rol</th>
                    <th class="px-4 py-3 border-0 text-end">Acciones</th>
                </tr>
            </thead>
            <tbody id="usersTableBody">
                @foreach($users as $userData)
                <tr>
                    <td class="px-4 py-3 border-0">
                        <div class="d-flex align-items-center gap-3">
                            <div class="profile-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                {{ strtoupper(substr($userData->name, 0, 1)) }}
                            </div>
                            <span class="fw-medium">
                                {{ $userData->name }}
                                @if($userData->id == Auth::id())
                                    <span class="text-muted small">(Tú)</span>
                                @endif
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 border-0 text-muted">{{ $userData->email }}</td>
                    <td class="px-4 py-3 border-0">
                        @if($userData->is_owner)
                        <span class="badge rounded-pill px-3" style="background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">Propietario</span>
                        @else
                        <span class="badge rounded-pill px-3" style="background: rgba(255, 255, 255, 0.05); color: var(--color-text-muted);">Usuario</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 border-0 text-end">
                        @if($userData->id == Auth::id())
                        <button class="icon-btn" onclick="openEditUserModal({{ json_encode($userData) }})" title="Editar mi perfil">
                            <i data-lucide="settings" style="width: 16px;"></i>
                        </button>
                        @else
                        <div class="d-flex gap-2 justify-content-end">
                            <button class="icon-btn" onclick="openEditUserModal({{ json_encode($userData) }})" title="Editar">
                                <i data-lucide="edit-2" style="width: 16px;"></i>
                            </button>
                            @if(Auth::user()->is_owner && !$userData->is_owner)
                            <button class="icon-btn text-danger" onclick="deleteUser({{ $userData->id }}, '{{ addslashes($userData->name) }}')" title="Eliminar">
                                <i data-lucide="trash-2" style="width: 16px;"></i>
                            </button>
                            @endif
                        </div>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="userModalTitle">Nuevo Usuario</h5>
            </div>
            <form id="userForm" class="p-4" method="POST" action="#">
                <input type="hidden" name="id" id="userId">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="name" id="userName" class="form-control-cartify w-100" placeholder="Ej: Juan Pérez" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="userEmail" class="form-control-cartify w-100" placeholder="usuario@ejemplo.com" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" id="passwordLabel">Contraseña</label>
                    <input type="password" name="password" id="userPassword" class="form-control-cartify w-100" placeholder="Mínimo 8 caracteres" required>
                    <small class="text-muted d-block mt-1" id="passwordHelp" style="display: none !important;">Deja en blanco si no deseas cambiar la contraseña</small>
                </div>
                <div class="mb-4" id="passwordConfirmDiv">
                    <label class="form-label">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="userPasswordConfirm" class="form-control-cartify w-100" placeholder="Repite la contraseña">
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="userSubmitBtn">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const apiBase = '/dashboard-api/users';
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        let userModal;

        function openCreateUserModal() {
            const form = document.getElementById('userForm');
            if (form) form.reset();
            const userId = document.getElementById('userId');
            if (userId) userId.value = '';
            const modalTitle = document.getElementById('userModalTitle');
            if (modalTitle) modalTitle.innerText = 'Nuevo Usuario';
            const submitBtn = document.getElementById('userSubmitBtn');
            if (submitBtn) submitBtn.innerText = 'Crear Usuario';
            const passwordLabel = document.getElementById('passwordLabel');
            if (passwordLabel) passwordLabel.innerText = 'Contraseña';
            const passwordHelp = document.getElementById('passwordHelp');
            if (passwordHelp) {
                passwordHelp.style.display = 'none';
                passwordHelp.style.visibility = 'hidden';
                passwordHelp.innerText = 'Deja en blanco si no deseas cambiar la contraseña';
            }
            const passwordField = document.getElementById('userPassword');
            if (passwordField) {
                passwordField.required = true;
                passwordField.placeholder = 'Mínimo 8 caracteres';
                passwordField.value = '';
                passwordField.removeAttribute('readonly');
            }
            const passwordConfirmDiv = document.getElementById('passwordConfirmDiv');
            if (passwordConfirmDiv) passwordConfirmDiv.style.display = 'block';
            const passwordConfirmField = document.getElementById('userPasswordConfirm');
            if (passwordConfirmField) {
                passwordConfirmField.required = true;
                passwordConfirmField.value = '';
                passwordConfirmField.placeholder = 'Repite la contraseña';
            }
        }

        function openEditUserModal(user) {
            const userId = document.getElementById('userId');
            if (userId) userId.value = user.id;
            const userName = document.getElementById('userName');
            if (userName) userName.value = user.name || '';
            const userEmail = document.getElementById('userEmail');
            if (userEmail) userEmail.value = user.email || '';
            
            const modalTitle = document.getElementById('userModalTitle');
            if (modalTitle) {
                modalTitle.innerText = user.id == {{ Auth::id() }} ? 'Editar mi Perfil' : 'Editar Usuario';
            }
            
            const submitBtn = document.getElementById('userSubmitBtn');
            if (submitBtn) submitBtn.innerText = 'Guardar Cambios';
            
            const passwordLabel = document.getElementById('passwordLabel');
            if (passwordLabel) passwordLabel.innerText = 'Nueva Contraseña';
            
            const passwordHelp = document.getElementById('passwordHelp');
            if (passwordHelp) {
                passwordHelp.style.display = 'block';
                passwordHelp.style.visibility = 'visible';
                passwordHelp.innerText = 'Deja en blanco si no deseas cambiar la contraseña';
            }
            
            const passwordField = document.getElementById('userPassword');
            if (passwordField) {
                passwordField.required = false;
                passwordField.placeholder = 'Deja en blanco para mantener la actual';
                passwordField.value = '';
            }
            
            const passwordConfirmDiv = document.getElementById('passwordConfirmDiv');
            if (passwordConfirmDiv) passwordConfirmDiv.style.display = 'block';
            
            const passwordConfirmField = document.getElementById('userPasswordConfirm');
            if (passwordConfirmField) {
                passwordConfirmField.required = false;
                passwordConfirmField.value = '';
            }
            
            if (userModal) userModal.show();
        }

        async function deleteUser(id, name) {
            const result = await window.CartifySwal.fire({
                icon: 'warning',
                title: '¿Eliminar Usuario?',
                html: `
                    <p class="mb-3">Estás a punto de eliminar el usuario <strong>"${name}"</strong>.</p>
                    <p class="text-muted mb-0"><strong>Nota:</strong> Los datos creados por este usuario serán reasignados al propietario de la cuenta.</p>
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

                    const responseData = await response.json();

                    if (response.ok) {
                        window.Toast.fire({
                            icon: 'success',
                            title: 'Usuario eliminado exitosamente'
                        });
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        window.CartifySwal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: responseData.message || 'No se pudo eliminar el usuario.'
                        });
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.Toast.fire({
                        icon: 'error',
                        title: 'Error de conexión al servidor'
                    });
                }
            }
        }

        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('userModal');
            if (modalElement) {
                userModal = new bootstrap.Modal(modalElement);
            }

            const form = document.getElementById('userForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const formData = new FormData(e.target);
                    const data = Object.fromEntries(formData.entries());
                    const id = data.id || '';

                    // Remove empty id for new users
                    if (!id) {
                        delete data.id;
                    }

                    // Remove empty password fields for updates
                    if (id && (!data.password || data.password === '')) {
                        delete data.password;
                        delete data.password_confirmation;
                    }

                    const method = id ? 'PUT' : 'POST';
                    const url = id ? `${apiBase}/${id}` : `${apiBase}`;

                    const btn = document.getElementById('userSubmitBtn');
                    const originalText = btn.innerText;
                    btn.disabled = true;
                    btn.innerText = 'Guardando...';

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

                        if (response.ok) {
                            if (userModal) {
                                userModal.hide();
                            }
                            const userName = data.name || 'Usuario';
                            window.Toast.fire({
                                icon: 'success',
                                title: id ? `Usuario "${userName}" actualizado exitosamente` : `¡Usuario "${userName}" creado exitosamente!`
                            });
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            // Handle subscription limit errors
                            if (response.status === 403 && responseData.error_code === 'SUBSCRIPTION_LIMIT_EXCEEDED') {
                                if (window.showSubscriptionLimitModal) {
                                    window.showSubscriptionLimitModal(responseData);
                                } else {
                                    window.CartifySwal.fire({
                                        icon: 'warning',
                                        title: 'Límite de Plan Alcanzado',
                                        html: `
                                            <div class="text-start">
                                                <p class="mb-3">${responseData.message || 'Has alcanzado el límite de usuarios permitidos en tu plan.'}</p>
                                                <div class="bg-light p-3 rounded mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <span>Actual:</span>
                                                        <strong>${responseData.current}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span>Límite del plan:</span>
                                                        <strong>${responseData.limit || 'Ilimitados'}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        `,
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
                                let errorMessage = 'Error al guardar usuario';
                                if (responseData.message) {
                                    errorMessage = responseData.message;
                                } else if (responseData.errors) {
                                    const errors = Object.values(responseData.errors).flat();
                                    errorMessage = errors.join('\n');
                                }
                                window.CartifySwal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        window.Toast.fire({
                            icon: 'error',
                            title: 'Error de conexión al servidor'
                        });
                    } finally {
                        btn.disabled = false;
                        btn.innerText = originalText;
                    }

                    return false;
                });
            }

            // Make functions globally available
            window.openCreateUserModal = openCreateUserModal;
            window.openEditUserModal = openEditUserModal;
            window.deleteUser = deleteUser;

            lucide.createIcons();
        });
    })();
</script>
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
        box-shadow: 0 0 30px rgba(220, 53, 69, 0.5) !important;
    }
</style>
@endsection
