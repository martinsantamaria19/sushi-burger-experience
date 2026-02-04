<!-- Loading State -->
<div id="products-loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="text-muted mt-3">Cargando datos de productos...</p>
</div>

<!-- Products Content -->
<div id="products-content" style="display: none;">
    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4" id="products-metrics">
        <!-- Total Products -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                        <i data-lucide="package"></i>
                    </div>
                </div>
                <h3 class="h4 fw-bold mb-1" id="total-products-value">0</h3>
                <p class="text-muted small mb-0">Total Productos</p>
            </div>
        </div>

        <!-- Products Sold -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i data-lucide="shopping-bag"></i>
                    </div>
                </div>
                <h3 class="h4 fw-bold mb-1" id="products-sold-value">0</h3>
                <p class="text-muted small mb-0">Productos Vendidos</p>
            </div>
        </div>

        <!-- Total Revenue from Products -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i data-lucide="dollar-sign"></i>
                    </div>
                </div>
                <h3 class="h4 fw-bold mb-1" id="products-revenue-value">$0</h3>
                <p class="text-muted small mb-0">Ingresos por Productos</p>
            </div>
        </div>

        <!-- Products with No Sales -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                        <i data-lucide="alert-circle"></i>
                    </div>
                </div>
                <h3 class="h4 fw-bold mb-1" id="no-sales-products-value">0</h3>
                <p class="text-muted small mb-0">Sin Ventas</p>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Products Sales Over Time -->
        <div class="col-12 col-lg-8">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Ventas de Productos en el Tiempo</h4>
                        <p class="text-muted small mb-0">Evolución de ventas de productos</p>
                    </div>
                </div>
                <div class="chart-container" style="height: 350px; position: relative;">
                    <canvas id="productsSalesOverTimeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Products by Category -->
        <div class="col-12 col-lg-4">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Productos por Categoría</h4>
                <div class="chart-container" style="height: 350px; position: relative;">
                    <canvas id="productsByCategoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Products by Restaurant -->
        <div class="col-12 col-lg-6">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Productos por Restaurante</h4>
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="productsByRestaurantChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Selling Products Chart -->
        <div class="col-12 col-lg-6">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Top 10 Productos Más Vendidos</h4>
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="topSellingProductsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Selling Products Table -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-8">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Productos Más Vendidos</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-hover mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Ingresos</th>
                                <th class="text-end">Pedidos</th>
                                <th class="text-end">Promedio/Pedido</th>
                            </tr>
                        </thead>
                        <tbody id="top-selling-products-table-body">
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Products with No Sales -->
        <div class="col-12 col-lg-4">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Productos Sin Ventas</h4>
                <div class="list-group list-group-flush" id="no-sales-products-list">
                    <div class="text-center text-muted py-4">Cargando...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Empty State -->
<div id="products-empty" class="glass-card p-5 text-center" style="display: none;">
    <div class="mb-4">
        <i data-lucide="package" style="width: 64px; height: 64px; color: var(--color-primary-light); opacity: 0.5;"></i>
    </div>
    <h3 class="h4 fw-bold mb-2">Aún no hay productos</h3>
    <p class="text-muted mb-4">Agrega productos a tu menú para comenzar a ver estadísticas aquí.</p>
    <a href="{{ route('admin.menu') }}" class="btn btn-cartify-primary">
        <i data-lucide="plus" class="me-2" style="width: 18px;"></i>
        Agregar Productos
    </a>
</div>

<script>
    // Products tab initialization
    window.initProductsTab = function() {
        loadProductsData();
    };

    window.loadProductsData = async function() {
        const loadingEl = document.getElementById('products-loading');
        const contentEl = document.getElementById('products-content');
        const emptyEl = document.getElementById('products-empty');

        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';
        emptyEl.style.display = 'none';

        try {
            const restaurantId = document.getElementById('restaurant-filter')?.value || '';
            const params = new URLSearchParams({ restaurant_id: restaurantId });

            const response = await fetch(`{{ route('api.products.analytics') }}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('Error al cargar datos');
            }

            const data = await response.json();

            const totalProducts = Number(data.total_products) || 0;
            const topSelling = Array.isArray(data.top_selling_products) ? data.top_selling_products : [];
            const noSales = Array.isArray(data.products_with_no_sales) ? data.products_with_no_sales : [];
            const hasData = totalProducts > 0 || topSelling.length > 0;

            if (!hasData) {
                loadingEl.style.display = 'none';
                emptyEl.style.display = 'block';
                return;
            }

            const safe = {
                total_products: totalProducts,
                top_selling_products: topSelling,
                products_with_no_sales: noSales,
                products_by_category: Array.isArray(data.products_by_category) ? data.products_by_category : [],
                products_by_restaurant: Array.isArray(data.products_by_restaurant) ? data.products_by_restaurant : [],
                products_sales_over_time: Array.isArray(data.products_sales_over_time) ? data.products_sales_over_time : [],
            };

            loadingEl.style.display = 'none';
            contentEl.style.display = 'block';

            try { updateProductsMetrics(safe); } catch (e) { console.error('updateProductsMetrics:', e); }
            try { updateProductsCharts(safe); } catch (e) { console.error('updateProductsCharts:', e); }
            try { updateTopSellingProductsTable(safe.top_selling_products); } catch (e) { console.error('updateTopSellingProductsTable:', e); }
            try { updateNoSalesProductsList(safe.products_with_no_sales); } catch (e) { console.error('updateNoSalesProductsList:', e); }
        } catch (error) {
            console.error('Error loading products data:', error);
            loadingEl.style.display = 'none';
            emptyEl.style.display = 'block';
        }
    };

    function updateProductsMetrics(data) {
        const formatCurrency = (value) => {
            return '$' + new Intl.NumberFormat('es-UY').format(Number(value || 0).toFixed(0));
        };
        const topSelling = Array.isArray(data.top_selling_products) ? data.top_selling_products : [];
        const noSales = Array.isArray(data.products_with_no_sales) ? data.products_with_no_sales : [];
        const totalProducts = Number(data.total_products) || 0;

        const el1 = document.getElementById('total-products-value');
        if (el1) el1.textContent = totalProducts.toLocaleString();

        const totalSold = topSelling.reduce((sum, p) => sum + (Number(p.total_quantity) || 0), 0);
        const el2 = document.getElementById('products-sold-value');
        if (el2) el2.textContent = totalSold.toLocaleString();

        const totalRevenue = topSelling.reduce((sum, p) => sum + (Number(p.total_revenue) || 0), 0);
        const el3 = document.getElementById('products-revenue-value');
        if (el3) el3.textContent = formatCurrency(totalRevenue);

        const el4 = document.getElementById('no-sales-products-value');
        if (el4) el4.textContent = noSales.length.toLocaleString();
    }

    function updateProductsCharts(data) {
        // Products Sales Over Time Chart
        const productsSalesOverTimeCtx = document.getElementById('productsSalesOverTimeChart');
        if (window.productsSalesOverTimeChart && typeof window.productsSalesOverTimeChart.destroy === 'function') {
            window.productsSalesOverTimeChart.destroy();
        }
        window.productsSalesOverTimeChart = null;
        const salesOverTime = Array.isArray(data.products_sales_over_time) ? data.products_sales_over_time : [];
        if (productsSalesOverTimeCtx && salesOverTime.length > 0 && typeof Chart !== 'undefined') {
            window.productsSalesOverTimeChart = new Chart(productsSalesOverTimeCtx, {
                type: 'line',
                data: {
                    labels: salesOverTime.map(d => d.label),
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: salesOverTime.map(d => d.total_quantity),
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y',
                    }, {
                        label: 'Ingresos',
                        data: salesOverTime.map(d => d.total_revenue),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: '#fff' }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            ticks: {
                                color: '#9ca3af',
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            ticks: {
                                color: '#9ca3af',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        }

        // Products by Category Chart
        const productsByCategoryCtx = document.getElementById('productsByCategoryChart');
        if (window.productsByCategoryChart && typeof window.productsByCategoryChart.destroy === 'function') {
            window.productsByCategoryChart.destroy();
        }
        window.productsByCategoryChart = null;
        const byCategory = Array.isArray(data.products_by_category) ? data.products_by_category : [];
        if (productsByCategoryCtx && byCategory.length > 0 && typeof Chart !== 'undefined') {
            window.productsByCategoryChart = new Chart(productsByCategoryCtx, {
                type: 'doughnut',
                data: {
                    labels: byCategory.map(d => d.category_name),
                    datasets: [{
                        data: byCategory.map(d => d.count),
                        backgroundColor: [
                            '#7c3aed',
                            '#10b981',
                            '#3b82f6',
                            '#f59e0b',
                            '#ef4444',
                            '#8b5cf6',
                            '#06b6d4',
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: '#fff', padding: 15 }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percent = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${percent}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Products by Restaurant Chart
        const productsByRestaurantCtx = document.getElementById('productsByRestaurantChart');
        if (window.productsByRestaurantChart && typeof window.productsByRestaurantChart.destroy === 'function') {
            window.productsByRestaurantChart.destroy();
        }
        window.productsByRestaurantChart = null;
        const byRestaurant = Array.isArray(data.products_by_restaurant) ? data.products_by_restaurant : [];
        if (productsByRestaurantCtx && byRestaurant.length > 0 && typeof Chart !== 'undefined') {
            window.productsByRestaurantChart = new Chart(productsByRestaurantCtx, {
                type: 'bar',
                data: {
                    labels: byRestaurant.map(d => d.restaurant_name),
                    datasets: [{
                        label: 'Productos',
                        data: byRestaurant.map(d => d.count),
                        backgroundColor: '#7c3aed',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        y: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        }
                    }
                }
            });
        }

        // Top Selling Products Chart
        const topSellingProductsCtx = document.getElementById('topSellingProductsChart');
        if (window.topSellingProductsChart && typeof window.topSellingProductsChart.destroy === 'function') {
            window.topSellingProductsChart.destroy();
        }
        window.topSellingProductsChart = null;
        const topSelling = Array.isArray(data.top_selling_products) ? data.top_selling_products : [];
        if (topSellingProductsCtx && topSelling.length > 0 && typeof Chart !== 'undefined') {
            const top10 = topSelling.slice(0, 10);
            window.topSellingProductsChart = new Chart(topSellingProductsCtx, {
                type: 'bar',
                data: {
                    labels: top10.map(p => p.product_name.length > 20 ? p.product_name.substring(0, 20) + '...' : p.product_name),
                    datasets: [{
                        label: 'Cantidad Vendida',
                        data: top10.map(p => p.total_quantity),
                        backgroundColor: '#10b981',
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        y: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        }
                    }
                }
            });
        }
    }

    function updateTopSellingProductsTable(products) {
        const tbody = document.getElementById('top-selling-products-table-body');
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No hay productos vendidos en este período</td></tr>';
            return;
        }

        tbody.innerHTML = products.map((product, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${product.product_name}</strong></td>
                <td class="text-end">${product.total_quantity.toLocaleString()}</td>
                <td class="text-end"><strong>$${product.total_revenue.toLocaleString()}</strong></td>
                <td class="text-end">${product.order_count}</td>
                <td class="text-end">${product.avg_per_order}</td>
            </tr>
        `).join('');
    }

    function updateNoSalesProductsList(products) {
        const list = document.getElementById('no-sales-products-list');
        if (!products || products.length === 0) {
            list.innerHTML = '<div class="text-center text-muted py-3">¡Excelente! Todos los productos tienen ventas</div>';
            return;
        }

        list.innerHTML = products.slice(0, 10).map(product => `
            <div class="list-group-item bg-transparent border-secondary d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">${product.product_name}</div>
                    <small class="text-muted">${product.category_name}</small>
                </div>
                <span class="badge bg-secondary">$${product.price.toLocaleString()}</span>
            </div>
        `).join('');

        if (products.length > 10) {
            list.innerHTML += `<div class="text-center text-muted py-2 small">Y ${products.length - 10} más...</div>`;
        }
    }

    // Initialize on tab show (custom tab system)
    // This will be called by the parent dashboard when tab is switched
</script>
