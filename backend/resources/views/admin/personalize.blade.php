@extends('layouts.admin')

@section('title', 'Personalizar - Sushi Burger Experience')
@section('page_title', 'Personalización de Marca')

@section('content')
<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="glass-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="h5 fw-bold mb-0">Ajustes Visuales</h3>
                <button type="button" class="btn btn-outline-light btn-sm" id="resetColorsBtn">
                    <i data-lucide="rotate-ccw" style="width: 16px;"></i> Reiniciar Colores
                </button>
            </div>

            <form id="personalizeForm">
                @csrf
                @method('PUT')

                <div class="row g-4">
                    <!-- Logo Section -->
                    <div class="col-12">
                        <label class="form-label">Logo del Restaurante</label>
                        <div class="d-flex align-items-center gap-4 p-3 rounded-4" style="background: rgba(255,255,255,0.03); border: 1px dashed var(--color-border);">
                            <div id="logoPreview" class="rounded-3 overflow-hidden d-flex align-items-center justify-content-center" style="width: 100px; height: 100px; background: var(--bg-dark);">
                                @if(isset($activeRestaurant) && $activeRestaurant && $activeRestaurant->logo_path)
                                    <img src="{{ str_starts_with($activeRestaurant->logo_path, 'http') ? $activeRestaurant->logo_path : asset('storage/' . $activeRestaurant->logo_path) }}" class="w-100 h-100" style="object-fit: cover;">
                                @else
                                    <i data-lucide="image" class="text-muted" style="width: 32px;"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <p class="small text-muted mb-2">Sube una imagen cuadrada (PNG o JPG) de al menos 500x500px.</p>
                                <input type="file" name="logo" class="form-control-cartify w-100" accept="image/*" onchange="previewLogo(this)">
                            </div>
                        </div>
                    </div>

                    <!-- Colors Section -->
                    <div class="col-12 mt-2">
                        <h4 class="h6 fw-bold mb-3 border-bottom pb-2" style="border-color: var(--color-border) !important;">Colores del Menú</h4>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nombre del Restaurant</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_name" id="color_name_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_name'] ?? '#ffffff' }}">
                            <input type="text" name="color_name_hex" id="color_name_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_name'] ?? '#ffffff' }}" placeholder="#ffffff" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Dirección / Subtítulos</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_address" id="color_address_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_address'] ?? '#94a3b8' }}">
                            <input type="text" name="color_address_hex" id="color_address_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_address'] ?? '#94a3b8' }}" placeholder="#94a3b8" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fondo de Botones</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_btn_bg" id="color_btn_bg_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_btn_bg'] ?? '#ffd700' }}">
                            <input type="text" name="color_btn_bg_hex" id="color_btn_bg_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_btn_bg'] ?? '#ffd700' }}" placeholder="#ffd700" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Texto de Botones</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_btn_text" id="color_btn_text_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_btn_text'] ?? '#000000' }}">
                            <input type="text" name="color_btn_text_hex" id="color_btn_text_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_btn_text'] ?? '#000000' }}" placeholder="#000000" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Título de Categorías</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_cat_title" id="color_cat_title_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_cat_title'] ?? '#ffffff' }}">
                            <input type="text" name="color_cat_title_hex" id="color_cat_title_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_cat_title'] ?? '#ffffff' }}" placeholder="#ffffff" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Título de Productos</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_prod_title" id="color_prod_title_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_prod_title'] ?? '#ffffff' }}">
                            <input type="text" name="color_prod_title_hex" id="color_prod_title_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_prod_title'] ?? '#ffffff' }}" placeholder="#ffffff" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Precios</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_price" id="color_price_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_price'] ?? '#ffd700' }}">
                            <input type="text" name="color_price_hex" id="color_price_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_price'] ?? '#ffd700' }}" placeholder="#ffd700" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Fondo de Tarjetas</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_card_bg" id="color_card_bg_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_card_bg'] ?? '#121620' }}">
                            <input type="text" name="color_card_bg_hex" id="color_card_bg_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_card_bg'] ?? '#121620' }}" placeholder="#121620" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Color de Fondo General</label>
                        <div class="d-flex gap-2">
                            <input type="color" name="color_bg" id="color_bg_picker" class="form-control-cartify p-1 color-picker" style="width: 44px; height: 44px;" value="{{ $activeRestaurant->settings['color_bg'] ?? '#07090e' }}">
                            <input type="text" name="color_bg_hex" id="color_bg_hex" class="form-control-cartify flex-grow-1 color-hex-input" value="{{ $activeRestaurant->settings['color_bg'] ?? '#07090e' }}" placeholder="#07090e" pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                    </div>

                    <!-- Social Media -->
                    <div class="col-12 mt-5">
                        <h4 class="h6 fw-bold mb-3 border-bottom pb-2" style="border-color: var(--color-border) !important;">Redes Sociales</h4>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">WhatsApp</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--color-border);"><i data-lucide="message-circle" class="text-success" style="width: 16px;"></i></span>
                            <input type="text" name="whatsapp" class="form-control-cartify border-start-0 w-100" placeholder="Ej: 59899123456" value="{{ $activeRestaurant->settings['whatsapp'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Instagram</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--color-border);"><i data-lucide="instagram" class="text-danger" style="width: 16px;"></i></span>
                            <input type="text" name="instagram" class="form-control-cartify border-start-0 w-100" placeholder="Ej: @sushiburger" value="{{ $activeRestaurant->settings['instagram'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Facebook</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0" style="border-color: var(--color-border);"><i data-lucide="facebook" class="text-primary" style="width: 16px;"></i></span>
                            <input type="text" name="facebook" class="form-control-cartify border-start-0 w-100" placeholder="Ej: page-name" value="{{ $activeRestaurant->settings['facebook'] ?? '' }}">
                        </div>
                    </div>

                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-cartify-primary px-5 py-3" id="saveBtn">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview Box -->
    <div class="col-12 col-xl-4">
        <div class="glass-card p-4 h-100 sticky-top" style="top: 100px; height: fit-content !important;">
            <h3 class="h5 fw-bold mb-4">Vista Previa</h3>
            <div id="phonePreview" style="background: #1a1a1a; border-radius: 24px; padding: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.5);">
                <div id="phoneScreen" style="background: #000; border-radius: 16px; overflow: hidden; height: 600px; position: relative;">
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
                    </style>
                    <div id="menuPreview" style="height: 100%; overflow-y: auto; background: var(--preview-bg, #07090e); font-family: 'Plus Jakarta Sans', sans-serif;">
                        <!-- Header -->
                        <div style="padding: 40px 20px 20px; background: linear-gradient(to bottom, rgba(124, 58, 237, 0.15), transparent); text-align: center;">
                            <div style="width: 60px; height: 60px; border-radius: 16px; background: rgba(255,255,255,0.1); margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                                @if($activeRestaurant->logo_path)
                                    <img src="{{ str_starts_with($activeRestaurant->logo_path, 'http') ? $activeRestaurant->logo_path : asset('storage/' . $activeRestaurant->logo_path) }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px;">
                                @elseif($activeRestaurant->logo_path === null)
                                    <i data-lucide="utensils-crossed" style="width: 28px; color: #7c3aed;"></i>
                                @else
                                    <i data-lucide="utensils-crossed" style="width: 28px; color: #7c3aed;"></i>
                                @endif
                            </div>
                            <h1 style="color: var(--preview-name, #ffffff); font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; font-family: 'Outfit', sans-serif;">{{ $activeRestaurant->name }}</h1>
                            <div style="color: var(--preview-address, #94a3b8); font-size: 0.875rem; display: flex; align-items: center; justify-content: center; gap: 4px;">
                                <i data-lucide="map-pin" style="width: 12px;"></i>
                                <span>{{ $activeRestaurant->address ?: '¡Bienvenidos!' }}</span>
                            </div>
                        </div>

                        <!-- Categories -->
                        <div style="padding: 12px; background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; gap: 8px; overflow-x: auto;">
                            <div style="padding: 8px 16px; background: var(--preview-btn-bg, #ffd700); color: var(--preview-btn-text, #000000); border-radius: 20px; font-size: 0.875rem; font-weight: 600; white-space: nowrap;">Entradas</div>
                            <div style="padding: 8px 16px; background: rgba(255,255,255,0.1); color: var(--preview-address, #94a3b8); border-radius: 20px; font-size: 0.875rem; white-space: nowrap;">Principales</div>
                            <div style="padding: 8px 16px; background: rgba(255,255,255,0.1); color: var(--preview-address, #94a3b8); border-radius: 20px; font-size: 0.875rem; white-space: nowrap;">Postres</div>
                        </div>

                        <!-- Category Section -->
                        <div style="padding: 20px;">
                            <h2 style="color: var(--preview-cat-title, #ffffff); font-size: 1.25rem; font-weight: 700; margin-bottom: 16px; font-family: 'Outfit', sans-serif;">Entradas</h2>

                            <!-- Product Cards -->
                            <div style="margin-bottom: 16px;">
                                <div style="background: var(--preview-card-bg, #121620); border-radius: 12px; padding: 12px; display: flex; gap: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                    <div style="width: 80px; height: 80px; border-radius: 8px; background: rgba(255,255,255,0.05); flex-shrink: 0;"></div>
                                    <div style="flex: 1; min-width: 0;">
                                        <h3 style="color: var(--preview-prod-title, #ffffff); font-size: 1rem; font-weight: 600; margin-bottom: 4px;">Pizza Margherita</h3>
                                        <p style="color: var(--preview-address, #94a3b8); font-size: 0.875rem; margin-bottom: 8px; line-height: 1.4;">Tomate, mozzarella y albahaca fresca</p>
                                        <div style="color: var(--preview-price, #ffd700); font-size: 1rem; font-weight: 700;">$450</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div style="background: var(--preview-card-bg, #121620); border-radius: 12px; padding: 12px; display: flex; gap: 12px; border: 1px solid rgba(255,255,255,0.05);">
                                    <div style="width: 80px; height: 80px; border-radius: 8px; background: rgba(255,255,255,0.05); flex-shrink: 0;"></div>
                                    <div style="flex: 1; min-width: 0;">
                                        <h3 style="color: var(--preview-prod-title, #ffffff); font-size: 1rem; font-weight: 600; margin-bottom: 4px;">Ensalada César</h3>
                                        <p style="color: var(--preview-address, #94a3b8); font-size: 0.875rem; margin-bottom: 8px; line-height: 1.4;">Lechuga romana, pollo y aderezo especial</p>
                                        <div style="color: var(--preview-price, #ffd700); font-size: 1rem; font-weight: 700;">$380</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 text-center">
                <a href="{{ route('public.menu', $activeRestaurant->slug) }}" target="_blank" class="btn btn-outline-light w-100 border-opacity-10 py-2">
                    Ver Menú Completo <i data-lucide="external-link" class="ms-1" style="width: 14px;"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .color-hex-input {
        text-transform: uppercase;
        font-family: monospace;
    }

    .color-hex-input:invalid {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.2) !important;
    }
</style>
@endsection

@section('scripts')
<script>
    // Default colors
    const defaultColors = {
        color_name: '#ffffff',
        color_address: '#94a3b8',
        color_btn_bg: '#8738E1',
        color_btn_text: '#ffffff',
        color_cat_title: '#ffffff',
        color_prod_title: '#ffffff',
        color_price: '#8738E1',
        color_card_bg: '#121620',
        color_bg: '#07090e'
    };

    // Color picker to HEX input sync
    document.querySelectorAll('.color-picker').forEach(picker => {
        const colorName = picker.name;
        const hexInput = document.getElementById(colorName + '_hex');

        picker.addEventListener('input', function() {
            hexInput.value = this.value.toUpperCase();
            updatePreview();
        });
    });

    // HEX input to color picker sync
    document.querySelectorAll('.color-hex-input').forEach(hexInput => {
        hexInput.addEventListener('input', function() {
            let value = this.value.trim().toUpperCase();

            // Add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
            }

            // Validate hex color
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                const colorName = this.name.replace('_hex', '');
                const picker = document.getElementById(colorName + '_picker');
                if (picker) {
                    picker.value = value;
                }
                this.value = value;
                updatePreview();
            }
        });

        hexInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text').trim().toUpperCase();
            let value = pastedText;

            // Add # if missing
            if (value && !value.startsWith('#')) {
                value = '#' + value;
            }

            // Validate and set
            if (/^#[0-9A-F]{6}$/i.test(value)) {
                this.value = value;
                const colorName = this.name.replace('_hex', '');
                const picker = document.getElementById(colorName + '_picker');
                if (picker) {
                    picker.value = value;
                }
                updatePreview();
            }
        });
    });

    // Update preview in real-time
    function updatePreview() {
        const menuPreview = document.getElementById('menuPreview');
        const colors = {
            '--preview-name': document.getElementById('color_name_picker')?.value || defaultColors.color_name,
            '--preview-address': document.getElementById('color_address_picker')?.value || defaultColors.color_address,
            '--preview-btn-bg': document.getElementById('color_btn_bg_picker')?.value || defaultColors.color_btn_bg,
            '--preview-btn-text': document.getElementById('color_btn_text_picker')?.value || defaultColors.color_btn_text,
            '--preview-cat-title': document.getElementById('color_cat_title_picker')?.value || defaultColors.color_cat_title,
            '--preview-prod-title': document.getElementById('color_prod_title_picker')?.value || defaultColors.color_prod_title,
            '--preview-price': document.getElementById('color_price_picker')?.value || defaultColors.color_price,
            '--preview-card-bg': document.getElementById('color_card_bg_picker')?.value || defaultColors.color_card_bg,
            '--preview-bg': document.getElementById('color_bg_picker')?.value || defaultColors.color_bg
        };

        Object.entries(colors).forEach(([key, value]) => {
            menuPreview.style.setProperty(key, value);
        });
    }

    // Reset colors button
    document.getElementById('resetColorsBtn').addEventListener('click', function() {
        window.CartifySwal.fire({
            title: '¿Reiniciar colores?',
            text: 'Se restaurarán los valores por defecto. ¿Continuar?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, reiniciar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                Object.entries(defaultColors).forEach(([colorName, defaultValue]) => {
                    const picker = document.getElementById(colorName + '_picker');
                    const hexInput = document.getElementById(colorName + '_hex');

                    if (picker) picker.value = defaultValue;
                    if (hexInput) hexInput.value = defaultValue.toUpperCase();
                });

                updatePreview();

                window.Toast.fire({
                    icon: 'success',
                    title: 'Colores reiniciados'
                });
            }
        });
    });

    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logoPreview');
                preview.innerHTML = `<img src="${e.target.result}" class="w-100 h-100" style="object-fit: cover;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.getElementById('personalizeForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';

        const formData = new FormData(e.target);
        const restaurantId = "{{ $activeRestaurant->id }}";

        // Remove hex input fields from form data (they're just for display)
        formData.delete('color_name_hex');
        formData.delete('color_address_hex');
        formData.delete('color_btn_bg_hex');
        formData.delete('color_btn_text_hex');
        formData.delete('color_cat_title_hex');
        formData.delete('color_prod_title_hex');
        formData.delete('color_price_hex');
        formData.delete('color_card_bg_hex');
        formData.delete('color_bg_hex');

        // Laravel requires _method=PUT for multipart/form-data updates via POST
        formData.append('_method', 'PUT');

        try {
            const response = await fetch(`/dashboard-api/restaurants/${restaurantId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            if (response.ok) {
                window.Toast.fire({
                    icon: 'success',
                    title: '¡Personalización guardada!'
                });

                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                const err = await response.json();
                window.CartifySwal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Error al guardar la personalización'
                });
            }
        } catch (error) {
            console.error('Error:', error);
            window.CartifySwal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.'
            });
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Initialize preview
    updatePreview();

    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endsection
