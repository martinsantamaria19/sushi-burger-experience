@extends('layouts.admin')

@section('title', 'Menú - Sushi Burger Experience')
@section('page_title', 'Gestión de Menú')

@section('content')
<div class="restaurants-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted small mb-0">{{ $menu->description ?? 'Crea tu primera carta digital' }}</p>
    </div>
    @if($menu)
    <button class="btn btn-cartify-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Nueva Categoría
    </button>
    @endif
</div>

@if($menu && $menu->categories->count() > 0)
    <div class="row g-4">
        @foreach($menu->categories as $category)
            <div class="col-12">
                <div class="p-4 rounded-4" style="background: var(--color-surface); border: 1px solid var(--color-border);">
                    <div class="category-header d-flex justify-content-between align-items-start mb-4">
                        <div class="d-flex align-items-center gap-2 gap-md-3 flex-wrap">
                            <h3 class="h5 fw-bold mb-0">{{ $category->name }}</h3>
                            <span class="badge rounded-pill category-badge" style="background: rgba(255, 255, 255, 0.05); color: var(--color-text-muted);">
                                {{ $category->products->count() }} productos
                            </span>
                        </div>
                        <div class="category-actions d-flex gap-2">
                            <button class="btn btn-sm btn-cartify-secondary category-edit-btn category-btn" onclick="editCategory({{ $category->id }}, '{{ $category->name }}')">
                                <i data-lucide="edit-2" style="width: 14px; height: 14px;" class="me-1"></i>
                                Editar
                            </button>
                            <button class="btn btn-sm btn-cartify-secondary category-add-btn category-btn" onclick="openAddProductModal({{ $category->id }})" style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%) !important; border: none !important; color: white !important;">
                                <i data-lucide="plus" style="width: 14px; height: 14px;" class="me-1"></i>
                                Crear Producto
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        @forelse($category->products as $product)
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="p-3 rounded-3 d-flex align-items-center gap-3 transition-all hover-glow product-card"
                                     style="background: rgba(0, 0, 0, 0.2); border: 1px solid var(--color-border);">
                                    @if($product->image_path)
                                        <img src="{{ str_starts_with($product->image_path, 'http') ? $product->image_path : storage_url($product->image_path) }}" class="rounded-3 product-image" style="width: 60px; height: 60px; object-fit: cover; flex-shrink: 0;">
                                    @else
                                        <div class="rounded-3 d-flex align-items-center justify-content-center product-image"
                                             style="width: 60px; height: 60px; background: rgba(255, 255, 255, 0.03); flex-shrink: 0;">
                                            <i data-lucide="image" class="text-muted" style="width: 24px;"></i>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1 min-w-0">
                                        <h4 class="h6 fw-bold mb-1 text-truncate">{{ $product->name }}</h4>
                                        <p class="text-muted small mb-0 text-truncate">{{ $product->description }}</p>
                                    </div>
                                    <div class="text-end product-actions">
                                        <span class="fw-bold d-block product-price" style="color: var(--color-primary-light);">${{ number_format($product->price, 2) }}</span>
                                        <div class="d-flex gap-1 mt-1">
                                            <button class="icon-btn p-1" onclick="editProduct({{ $product->id }}, {{ json_encode($product) }})" title="Editar">
                                                <i data-lucide="edit-3" style="width: 14px;"></i>
                                            </button>
                                            <button class="icon-btn p-1 hover-danger" onclick="deleteProduct({{ $product->id }})" title="Eliminar">
                                                <i data-lucide="trash" style="width: 14px;"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 py-3 text-center text-muted small italic">
                                No hay productos en esta categoría aún.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="p-5 rounded-4 text-center" style="background: var(--color-surface); border: 1px solid var(--color-border);">
        <div class="mb-4">
            <i data-lucide="book-open" class="mb-3" style="width: 64px; height: 64px; color: var(--color-primary-light);"></i>
            <h2 class="h4 fw-bold mb-2">Crea tu carta digital</h2>
            <p class="text-muted mx-auto" style="max-width: 500px;">Aquí podrás organizar tus categorías, productos y precios de forma dinámica y elegante.</p>
        </div>
        @if(!$menu)
            <button class="btn btn-cartify-primary px-5 py-3" onclick="createInitialMenu()">
                Crear mi primer menú
            </button>
        @else
            <button class="btn btn-cartify-primary px-5 py-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                + Agregar primera categoría
            </button>
        @endif
    </div>
@endif

<!-- Modals -->
<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0">Nueva Categoría</h5>
            </div>
            <form id="addCategoryForm" class="p-4">
                <input type="hidden" name="restaurant_id" value="{{ $restaurant->id ?? '' }}">
                <input type="hidden" name="menu_id" value="{{ $menu->id ?? '' }}">
                <div class="mb-4">
                    <label class="form-label">Nombre de la categoría</label>
                    <input type="text" name="name" class="form-control-cartify w-100" placeholder="Ej: Entradas, Postres, etc." required>
                </div>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3">Crear Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0">Editar Categoría</h5>
            </div>
            <form id="editCategoryForm" class="p-4">
                <input type="hidden" name="category_id" id="editCategoryId">
                <div class="mb-4">
                    <label class="form-label">Nombre de la categoría</label>
                    <input type="text" name="name" id="editCategoryName" class="form-control-cartify w-100" placeholder="Ej: Entradas, Postres, etc." required>
                </div>
                <div class="d-flex gap-2 flex-column">
                    <div class="d-flex gap-3">
                        <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3">Guardar Cambios</button>
                    </div>
                    <button type="button" class="btn btn-outline-danger w-100 py-3 mt-2" id="deleteCategoryBtn">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;" class="me-2"></i>
                        Eliminar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="productModalTitle">Añadir Producto</h5>
            </div>
            <form id="productForm" class="p-4">
                <input type="hidden" name="id" id="productId">
                <input type="hidden" name="restaurant_id" value="{{ $restaurant->id ?? '' }}">
                <input type="hidden" name="category_id" id="productCategoryId">

                <div class="mb-4">
                    <label class="form-label">Nombre del producto</label>
                    <input type="text" name="name" id="productName" class="form-control-cartify w-100" placeholder="Ej: Pizza Margherita" required>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Precio</label>
                        <div class="price-input-wrapper">
                            <span class="price-currency">$</span>
                            <input type="number" step="0.01" name="price" id="productPrice" class="form-control-cartify price-input-field" placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Imagen del producto</label>
                        <div class="file-input-wrapper">
                            <input type="file" name="image_path" id="productImagePath" class="file-input-hidden" accept="image/*" onchange="previewImage(this)">
                            <label for="productImagePath" class="file-input-label">
                                <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                                <span class="file-input-text">Seleccionar archivo</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Descripción</label>
                    <textarea name="description" id="productDescription" class="form-control-cartify w-100" rows="4" placeholder="Describe el producto..."></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Cantidad de variaciones seleccionables</label>
                    <input type="number" name="max_variants_selectable" id="productMaxVariantsSelectable" class="form-control-cartify w-100" min="1" max="10" value="1" placeholder="1">
                    <small class="text-muted">Cuántas variantes puede elegir el cliente al pedir (ej: 1 = debe elegir una variante).</small>
                </div>

                <div id="imagePreview" class="mb-4 rounded-3 overflow-hidden d-none" style="height: 200px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-border);">
                    <img src="" class="w-100 h-100" style="object-fit: cover;">
                </div>

                <div id="productVariantsSection" class="mt-4 d-none">
                    <hr style="border-color: var(--color-border);">
                    <h6 class="fw-bold mb-3">Variantes de este producto</h6>
                    <p class="text-muted small mb-3">Los clientes elegirán una variante (nombre, imagen, ingredientes). Opción sin gluten por variante.</p>
                    <div id="productVariantsList" class="mb-3"></div>
                    <button type="button" class="btn btn-sm btn-cartify-secondary" id="btnNewVariant">
                        <i data-lucide="plus" style="width: 14px; height: 14px;" class="me-1"></i>
                        Nueva variante
                    </button>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="productSubmitBtn">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Variant Modal (Add/Edit) -->
<div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 p-0 overflow-hidden">
            <div class="p-4 border-bottom" style="border-color: var(--color-border) !important;">
                <h5 class="fw-bold mb-0" id="variantModalTitle">Nueva variante</h5>
            </div>
            <form id="variantForm" class="p-4">
                <input type="hidden" name="variant_id" id="variantId">
                <input type="hidden" name="product_id" id="variantProductId">
                <div class="mb-3">
                    <label class="form-label">Nombre de la variante</label>
                    <input type="text" name="name" id="variantName" class="form-control-cartify w-100" placeholder="Ej: Clásica, Con extra" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Ingredientes</label>
                    <textarea name="ingredients" id="variantIngredients" class="form-control-cartify w-100" rows="3" placeholder="Lista de ingredientes..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Precio (opcional; si se deja vacío usa el precio del producto)</label>
                    <input type="number" step="0.01" min="0" name="price" id="variantPrice" class="form-control-cartify w-100" placeholder="Igual al producto">
                </div>
                <div class="mb-3">
                    <label class="form-label">Imagen de la variante</label>
                    <div class="file-input-wrapper">
                        <input type="file" name="image_path" id="variantImagePath" class="file-input-hidden" accept="image/*">
                        <label for="variantImagePath" class="file-input-label">
                            <i data-lucide="upload" style="width: 18px; height: 18px;"></i>
                            <span class="file-input-text" id="variantImageText">Seleccionar archivo</span>
                        </label>
                    </div>
                </div>
                <div id="variantImagePreview" class="mb-3 rounded-3 overflow-hidden d-none" style="height: 120px; background: rgba(255,255,255,0.05); border: 1px solid var(--color-border);">
                    <img src="" class="w-100 h-100" style="object-fit: cover;" id="variantImagePreviewImg">
                </div>
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" name="is_gluten_free_available" id="variantGlutenFree" value="1">
                    <label class="form-check-label" for="variantGlutenFree">Disponible opción sin gluten</label>
                </div>
                <div class="d-flex gap-3">
                    <button type="button" class="btn btn-cartify-secondary flex-grow-1 py-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-cartify-primary flex-grow-1 py-3" id="variantSubmitBtn">Guardar variante</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('styles')
<style>
    /* File input personalizado */
    .file-input-wrapper {
        position: relative;
    }

    .file-input-hidden {
        position: absolute;
        width: 0.1px;
        height: 0.1px;
        opacity: 0;
        overflow: hidden;
        z-index: -1;
    }

    .file-input-label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 0.875rem 1rem;
        background: rgba(0, 0, 0, 0.2);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        color: white;
        font-family: var(--font-body);
        font-size: 1rem;
        cursor: pointer;
        transition: all 0.2s ease;
        gap: 0.5rem;
    }

    .file-input-label:hover {
        background: rgba(124, 58, 237, 0.1);
        border-color: var(--color-primary);
    }

    .file-input-label:active {
        transform: scale(0.98);
    }

    .file-input-text {
        flex: 1;
        text-align: left;
        color: var(--color-text-muted);
    }

    .file-input-hidden:focus + .file-input-label {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 4px rgba(124, 58, 237, 0.1);
    }

    .file-input-hidden:valid + .file-input-label .file-input-text {
        color: white;
    }

    /* Input de precio rediseñado */
    .price-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .price-currency {
        position: absolute;
        left: 1rem;
        color: var(--color-text-muted);
        font-weight: 500;
        font-size: 1rem;
        pointer-events: none;
        z-index: 1;
        line-height: 1.5;
    }

    .price-input-field {
        padding-left: 2.5rem !important;
    }

    .price-input-field:focus {
        padding-left: 2.5rem !important;
    }

    /* Desktop: Botones pequeños a la derecha */
    .category-actions {
        flex-shrink: 0;
        margin-left: auto;
    }

    .category-btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        white-space: nowrap;
    }

    /* Mobile optimizations */
    @media (max-width: 991.98px) {
        /* Category header */
        .category-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .category-actions {
            width: 100%;
            margin-left: 0;
        }

        .category-add-btn,
        .category-edit-btn {
            flex: 1;
        }

        /* Product cards */
        .product-card {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .product-card .d-flex.align-items-center.gap-3 {
            width: 100%;
        }

        .product-actions {
            width: 100% !important;
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.75rem;
        }

        .product-image {
            flex-shrink: 0;
        }

        /* Modal adjustments */
        .modal-dialog {
            margin: 1rem;
        }

        .modal-content {
            border-radius: 16px !important;
        }
    }

    @media (max-width: 575.98px) {
        .category-actions {
            flex-wrap: wrap;
        }

        .category-add-btn {
            width: 100%;
        }

        .category-action-btn {
            flex: 1;
            min-width: calc(50% - 0.5rem);
        }
    }
</style>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const apiBase = '/dashboard-api';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Headers para fetch
        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        };

        // --- Categorías ---
        const categoryModal = bootstrap.Modal.getInstance(document.getElementById('addCategoryModal')) || new bootstrap.Modal(document.getElementById('addCategoryModal'));

        document.getElementById('addCategoryForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch(`${apiBase}/categories`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify(data)
                });
                if (response.ok) {
                    // Cerrar el modal inmediatamente
                    categoryModal.hide();

                    const categoryName = data.name || 'Categoría';
                    window.Toast.fire({
                        icon: 'success',
                        title: `¡Categoría "${categoryName}" creada exitosamente!`
                    });
                    setTimeout(() => location.reload(), 1500);
                }
                else {
                    const err = await response.json();
                    window.CartifySwal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Error al guardar categoría'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Fallo de conexión al servidor'
                });
            }
        });

        // --- Productos ---
        if (typeof bootstrap !== 'undefined') {
            const productModal = new bootstrap.Modal(document.getElementById('productModal'));
            window.productModal = productModal; // Expose to global for onclick handlers
        }

        document.getElementById('productForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const id = formData.get('id');

            // Confirmación específica para EDICIÓN
            if (id) {
                const result = await window.CartifySwal.fire({
                    title: '¿Confirmar cambios?',
                    text: "Se actualizará la información del producto.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar'
                });

                if (!result.isConfirmed) return;
            }

            const url = id ? `${apiBase}/products/${id}` : `${apiBase}/products`;

            // Laravel requires _method=PUT for multipart/form-data updates via POST
            if (id) {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(url, {
                    method: 'POST', // Use POST with _method=PUT for file uploads
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                if (response.ok) {
                    // Cerrar el modal inmediatamente
                    if (window.productModal) {
                        window.productModal.hide();
                    }

                    const productName = formData.get('name') || 'Producto';
                    window.Toast.fire({
                        icon: 'success',
                        title: id ? 'Producto actualizado correctamente' : `¡${productName} creado exitosamente!`
                    });
                    setTimeout(() => location.reload(), 1500);
                }
                else {
                    const err = await response.json();
                    window.CartifySwal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: err.message || 'Error al guardar producto'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Fallo de conexión al servidor'
                });
            }
        });

        window.previewImage = function(input) {
            const preview = document.getElementById('imagePreview');
            const img = preview.querySelector('img');
            const fileText = input.parentElement.querySelector('.file-input-text');

            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                if (fileText) {
                    fileText.textContent = fileName.length > 30 ? fileName.substring(0, 30) + '...' : fileName;
                    fileText.style.color = 'white';
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    preview.classList.remove('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                if (fileText) {
                    fileText.textContent = 'Seleccionar archivo';
                    fileText.style.color = 'var(--color-text-muted)';
                }
                preview.classList.add('d-none');
            }
        };

        // Delete Category Function (called from modal button)
        window.deleteCategory = async function(id) {
            const result = await window.CartifySwal.fire({
                title: '¿Estás seguro?',
                text: "Se eliminará esta categoría y todos sus productos. ¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar todo',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`${apiBase}/categories/${id}`, {
                    method: 'DELETE',
                    headers
                });
                if (response.ok) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'Categoría eliminada exitosamente'
                    });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const err = await response.json();
                    window.Toast.fire({
                        icon: 'error',
                        title: err.message || 'Error al eliminar categoría'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Error al eliminar categoría'
                });
            }
        };

        window.openAddProductModal = function(categoryId) {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('productCategoryId').value = categoryId;
            document.getElementById('productMaxVariantsSelectable').value = '1';
            document.getElementById('productModalTitle').innerText = 'Añadir Producto';
            document.getElementById('productSubmitBtn').innerText = 'Crear Producto';
            document.getElementById('imagePreview').classList.add('d-none');
            document.getElementById('productVariantsSection').classList.add('d-none');
            document.getElementById('productVariantsList').innerHTML = '';

            const fileText = document.querySelector('#productImagePath').parentElement.querySelector('.file-input-text');
            if (fileText) {
                fileText.textContent = 'Seleccionar archivo';
                fileText.style.color = 'var(--color-text-muted)';
            }

            window.productModal?.show();
        };

        window.editProduct = async function(id, product) {
            document.getElementById('productId').value = id;
            document.getElementById('productCategoryId').value = product.category_id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productMaxVariantsSelectable').value = product.max_variants_selectable ?? 1;

            // Image preview for existing image
            const preview = document.getElementById('imagePreview');
            const img = preview.querySelector('img');
            const fileText = document.querySelector('#productImagePath').parentElement.querySelector('.file-input-text');

            if (product.image_path) {
                img.src = product.image_path.startsWith('http') ? product.image_path : (window.APP_STORAGE_BASE || '/_storage') + '/' + product.image_path;
                preview.classList.remove('d-none');
                if (fileText) {
                    fileText.textContent = 'Imagen actual (click para cambiar)';
                    fileText.style.color = 'var(--color-text-muted)';
                }
            } else {
                preview.classList.add('d-none');
                if (fileText) {
                    fileText.textContent = 'Seleccionar archivo';
                    fileText.style.color = 'var(--color-text-muted)';
                }
            }

            document.getElementById('productModalTitle').innerText = 'Editar Producto';
            document.getElementById('productSubmitBtn').innerText = 'Guardar Cambios';

            // Mostrar sección variantes y cargar lista
            document.getElementById('productVariantsSection').classList.remove('d-none');
            await window.loadProductVariants(id);

            window.productModal?.show();
        };

        window.deleteProduct = async function(id) {
            const result = await window.CartifySwal.fire({
                title: '¿Eliminar producto?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`${apiBase}/products/${id}`, {
                    method: 'DELETE',
                    headers
                });
                if (response.ok) {
                    window.Toast.fire({
                        icon: 'success',
                        title: 'Producto eliminado exitosamente'
                    });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    const err = await response.json();
                    window.Toast.fire({
                        icon: 'error',
                        title: err.message || 'Error al eliminar producto'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                window.Toast.fire({
                    icon: 'error',
                    title: 'Error al eliminar producto'
                });
            }
        };

        window.createInitialMenu = async function() {
            const restaurantId = {{ $restaurant->id ?? 'null' }};
            if (!restaurantId) return window.Toast.fire({
                icon: 'warning',
                title: 'No hay restaurante vinculado'
            });

            try {
                const response = await fetch(`${apiBase}/menus`, {
                    method: 'POST',
                    headers,
                    body: JSON.stringify({
                        restaurant_id: restaurantId,
                        name: 'Menú Principal',
                        description: 'Carta digital de nuestro restaurante'
                    })
                });
                if (response.ok) location.reload();
            } catch (error) {
                console.error('Error:', error);
            }
        };

        window.editCategory = function(id, name) {
            window.CartifySwal.fire({
                title: 'Editar categoría',
                text: 'Funcionalidad de edición de nombre próximamente.',
                icon: 'info'
            });
        };

        // --- Variantes ---
        const variantModal = document.getElementById('variantModal') ? new bootstrap.Modal(document.getElementById('variantModal')) : null;

        window.loadProductVariants = async function(productId) {
            const listEl = document.getElementById('productVariantsList');
            if (!listEl) return;
            listEl.innerHTML = '<span class="text-muted small">Cargando...</span>';
            try {
                const response = await fetch(`${apiBase}/products/${productId}/variants`, { headers: { 'Accept': 'application/json' } });
                const variants = await response.json();
                if (!Array.isArray(variants)) {
                    listEl.innerHTML = '<span class="text-muted small">Error al cargar variantes</span>';
                    return;
                }
                if (variants.length === 0) {
                    listEl.innerHTML = '<span class="text-muted small">Sin variantes. Agrega una para que los clientes elijan.</span>';
                    return;
                }
                listEl.innerHTML = variants.map(v => `
                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 mb-2" style="background: rgba(0,0,0,0.2); border: 1px solid var(--color-border);">
                        ${v.image_path ? `<img src="${(window.APP_STORAGE_BASE || '/_storage')}/${v.image_path}" class="rounded-2" style="width:40px;height:40px;object-fit:cover;">` : '<div class="rounded-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:rgba(255,255,255,0.05);"><i data-lucide="image" style="width:18px;" class="text-muted"></i></div>'}
                        <div class="flex-grow-1 min-w-0">
                            <span class="fw-bold">${escapeHtml(v.name)}</span>
                            ${v.is_gluten_free_available ? '<span class="badge bg-success ms-1">Sin gluten</span>' : ''}
                        </div>
                        <button type="button" class="icon-btn p-1" onclick="window.editVariant(${v.id}, ${productId})" title="Editar"><i data-lucide="edit-3" style="width:14px;"></i></button>
                        <button type="button" class="icon-btn p-1 hover-danger" onclick="window.deleteVariant(${v.id}, '${escapeHtml(v.name).replace(/'/g, "\\'")}')" title="Eliminar"><i data-lucide="trash" style="width:14px;"></i></button>
                    </div>
                `).join('');
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } catch (e) {
                listEl.innerHTML = '<span class="text-danger small">Error al cargar variantes</span>';
            }
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        document.getElementById('btnNewVariant')?.addEventListener('click', function() {
            const productId = document.getElementById('productId').value;
            if (!productId) return;
            document.getElementById('variantForm').reset();
            document.getElementById('variantId').value = '';
            document.getElementById('variantProductId').value = productId;
            document.getElementById('variantModalTitle').innerText = 'Nueva variante';
            document.getElementById('variantSubmitBtn').innerText = 'Guardar variante';
            document.getElementById('variantImagePreview').classList.add('d-none');
            document.getElementById('variantImageText').textContent = 'Seleccionar archivo';
            variantModal?.show();
        });

        window.editVariant = async function(variantId, productId) {
            const response = await fetch(`${apiBase}/products/${productId}/variants`, { headers: { 'Accept': 'application/json' } });
            const variants = await response.json();
            const v = Array.isArray(variants) ? variants.find(x => x.id === variantId) : null;
            if (!v) return;
            document.getElementById('variantId').value = v.id;
            document.getElementById('variantProductId').value = productId;
            document.getElementById('variantName').value = v.name;
            document.getElementById('variantIngredients').value = v.ingredients || '';
            document.getElementById('variantPrice').value = v.price ?? '';
            document.getElementById('variantGlutenFree').checked = !!v.is_gluten_free_available;
            document.getElementById('variantModalTitle').innerText = 'Editar variante';
            document.getElementById('variantSubmitBtn').innerText = 'Guardar cambios';
            const preview = document.getElementById('variantImagePreview');
            const previewImg = document.getElementById('variantImagePreviewImg');
            if (v.image_path) {
                previewImg.src = (window.APP_STORAGE_BASE || '/_storage') + '/' + v.image_path;
                preview.classList.remove('d-none');
            } else {
                preview.classList.add('d-none');
            }
            document.getElementById('variantImageText').textContent = v.image_path ? 'Cambiar imagen' : 'Seleccionar archivo';
            variantModal?.show();
        };

        window.deleteVariant = async function(variantId, name) {
            const result = await window.CartifySwal.fire({
                title: '¿Eliminar variante?',
                text: name ? `Se eliminará "${name}".` : 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });
            if (!result.isConfirmed) return;
            try {
                const response = await fetch(`${apiBase}/product-variants/${variantId}`, { method: 'DELETE', headers });
                if (response.ok) {
                    window.Toast.fire({ icon: 'success', title: 'Variante eliminada' });
                    const productId = document.getElementById('productId').value;
                    if (productId) await window.loadProductVariants(productId);
                } else {
                    const err = await response.json();
                    window.Toast.fire({ icon: 'error', title: err.message || 'Error al eliminar' });
                }
            } catch (e) {
                window.Toast.fire({ icon: 'error', title: 'Error de conexión' });
            }
        };

        document.getElementById('variantForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.target;
            const variantId = document.getElementById('variantId').value;
            const productId = document.getElementById('variantProductId').value;
            if (!productId) return;
            const formData = new FormData(form);
            formData.delete('variant_id');
            formData.delete('product_id');
            formData.set('is_gluten_free_available', document.getElementById('variantGlutenFree').checked ? '1' : '0');
            const productIdForVariant = document.getElementById('variantProductId').value;
            const url = variantId
                ? `${apiBase}/product-variants/${variantId}`
                : `${apiBase}/products/${productIdForVariant}/variants`;
            if (variantId) formData.append('_method', 'PUT');
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                    body: formData
                });
                if (response.ok) {
                    variantModal?.hide();
                    window.Toast.fire({ icon: 'success', title: variantId ? 'Variante actualizada' : 'Variante creada' });
                    await window.loadProductVariants(productIdForVariant);
                } else {
                    const err = await response.json();
                    window.CartifySwal.fire({ icon: 'error', title: 'Error', text: err.message || JSON.stringify(err) });
                }
            } catch (err) {
                window.Toast.fire({ icon: 'error', title: 'Error de conexión' });
            }
        });

        document.getElementById('variantImagePath')?.addEventListener('change', function() {
            const preview = document.getElementById('variantImagePreview');
            const img = document.getElementById('variantImagePreviewImg');
            const text = document.getElementById('variantImageText');
            if (this.files && this.files[0]) {
                const r = new FileReader();
                r.onload = function() { img.src = r.result; preview.classList.remove('d-none'); };
                r.readAsDataURL(this.files[0]);
                text.textContent = this.files[0].name;
            } else {
                preview.classList.add('d-none');
                text.textContent = 'Seleccionar archivo';
            }
        });

    });
</script>
@endsection
