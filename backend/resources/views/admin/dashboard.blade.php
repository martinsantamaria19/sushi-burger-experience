@extends('layouts.admin')

@section('title', 'Dashboard - Sushi Burger Experience')
@section('page_title', 'Dashboard')

@section('content')
<!-- Filters Bar -->
<div class="glass-card p-4 mb-4">
    <div class="d-flex flex-wrap gap-3 align-items-end">
        <div class="filter-group">
            <label class="form-label small">Restaurante</label>
            <select id="restaurant-filter" class="form-control-cartify">
                <option value="">Todos</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}" {{ session('active_restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                        {{ $restaurant->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="glass-card p-0 mb-4">
    <div class="dashboard-tabs">
        <button class="tab-button active" data-tab="qr" id="qr-tab">
            <i data-lucide="qr-code"></i>
            <span>Códigos QR</span>
        </button>
        <button class="tab-button" data-tab="sales" id="sales-tab">
            <i data-lucide="trending-up"></i>
            <span>Ventas</span>
        </button>
        <button class="tab-button" data-tab="products" id="products-tab">
            <i data-lucide="package"></i>
            <span>Productos</span>
        </button>
    </div>
</div>

<!-- Tab Content -->
<div class="tab-content">
    <!-- QR Codes Tab -->
    <div class="tab-pane active" id="qr-pane" data-tab-content="qr">
        @include('admin.dashboard.tabs.qr-codes', ['stats' => $stats, 'restaurants' => $restaurants, 'activeRestaurant' => $activeRestaurant])
    </div>

    <!-- Sales Tab -->
    <div class="tab-pane" id="sales-pane" data-tab-content="sales" style="display: none;">
        @include('admin.dashboard.tabs.sales', ['restaurants' => $restaurants])
    </div>

    <!-- Products Tab -->
    <div class="tab-pane" id="products-pane" data-tab-content="products" style="display: none;">
        @include('admin.dashboard.tabs.products', ['restaurants' => $restaurants])
    </div>
</div>
@endsection

@section('styles')
<style>
    .dashboard-tabs {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        padding: 0;
        margin: 0;
        display: flex;
        gap: 0;
    }

    .dashboard-tabs .nav-item {
        flex: 1;
    }

    .dashboard-tabs .nav-link,
    .dashboard-tabs .tab-button {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 16px 24px;
        border: none;
        border-bottom: 3px solid transparent;
        background: transparent;
        color: var(--color-text-muted);
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 0;
        cursor: pointer;
    }

    .dashboard-tabs .nav-link i,
    .dashboard-tabs .tab-button i {
        width: 20px;
        height: 20px;
    }

    .dashboard-tabs .nav-link:hover,
    .dashboard-tabs .tab-button:hover {
        color: var(--color-primary-light);
        background: rgba(124, 58, 237, 0.05);
    }

    .dashboard-tabs .nav-link.active,
    .dashboard-tabs .tab-button.active {
        color: var(--color-primary-light);
        border-bottom-color: var(--color-primary);
        background: rgba(124, 58, 237, 0.1);
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 200px;
    }

    .filter-group label {
        font-size: 0.813rem;
        font-weight: 500;
        color: var(--color-text-muted);
        margin-bottom: 0;
    }

    .filter-group .form-control-cartify {
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .filter-group .form-check-input {
        margin-top: 0.25rem;
    }

    .tab-pane {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .glass-card {
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }

    /* Premium Lock Overlay */
    .premium-lock-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(5, 5, 5, 0.85);
        backdrop-filter: blur(8px);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
    }

    .premium-lock-content {
        text-align: center;
        padding: 2rem;
        max-width: 400px;
    }

    /* Mobile optimizations */
    @media (max-width: 991.98px) {
        .dashboard-tabs .tab-button {
            padding: 12px 16px;
            font-size: 0.875rem;
        }

        .dashboard-tabs .tab-button span {
            display: none;
        }

        .dashboard-tabs .tab-button i {
            width: 24px;
            height: 24px;
        }

        .filter-group {
            min-width: 150px;
            flex: 1;
        }
    }
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns@3.0.0/dist/chartjs-adapter-date-fns.bundle.min.js"></script>
@vite(['resources/js/dashboard.js'])
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();

        // Tab switching: buttons use .tab-button and data-tab (no Bootstrap)
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane[data-tab-content]');

        function switchTab(tabId) {
            tabButtons.forEach(btn => {
                btn.classList.toggle('active', btn.getAttribute('data-tab') === tabId);
            });
            tabPanes.forEach(pane => {
                const isActive = pane.getAttribute('data-tab-content') === tabId;
                pane.classList.toggle('active', isActive);
                pane.style.display = isActive ? 'block' : 'none';
            });

            if (tabId === 'sales') {
                window.initSalesTab && window.initSalesTab();
            } else if (tabId === 'products') {
                window.initProductsTab && window.initProductsTab();
            }
        }

        tabButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                switchTab(this.getAttribute('data-tab'));
            });
        });

        // Restaurant filter change
        document.getElementById('restaurant-filter')?.addEventListener('change', function() {
            const restaurantId = this.value;
            // Update session via AJAX
            fetch('/admin/restaurants/switch', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ restaurant_id: restaurantId }),
            }).then(() => {
                // Reload current tab data
                const activeTab = document.querySelector('.tab-button.active');
                if (activeTab) {
                    const tabName = activeTab.dataset.tab;
                    if (tabName === 'sales') {
                        window.loadSalesData && window.loadSalesData();
                    } else if (tabName === 'products') {
                        window.loadProductsData && window.loadProductsData();
                    }
                }
            });
        });

    });
</script>
@endsection
