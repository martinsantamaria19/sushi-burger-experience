<!-- Loading State -->
<div id="sales-loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
    <p class="text-muted mt-3">Cargando datos de ventas...</p>
</div>

<!-- Sales Content -->
<div id="sales-content" style="display: none;">
    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4" id="sales-metrics">
        <!-- Total Sales -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i data-lucide="dollar-sign"></i>
                    </div>
                    <span id="sales-growth-badge" class="badge bg-success bg-opacity-10 text-success" style="display: none;">
                        <i data-lucide="trending-up" style="width: 12px; height: 12px;"></i>
                        <span id="sales-growth-value">0%</span>
                    </span>
                </div>
                <h3 class="h4 fw-bold mb-1" id="total-sales-value">$0</h3>
                <p class="text-muted small mb-0">Ventas Totales</p>
                <small class="text-muted" id="compare-sales-text" style="display: none;"></small>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(124, 58, 237, 0.1); color: var(--color-primary-light);">
                        <i data-lucide="shopping-cart"></i>
                    </div>
                    <span id="orders-growth-badge" class="badge bg-primary bg-opacity-10 text-primary-light" style="display: none;">
                        <i data-lucide="trending-up" style="width: 12px; height: 12px;"></i>
                        <span id="orders-growth-value">0%</span>
                    </span>
                </div>
                <h3 class="h4 fw-bold mb-1" id="total-orders-value">0</h3>
                <p class="text-muted small mb-0">Pedidos Totales</p>
                <small class="text-muted" id="compare-orders-text" style="display: none;"></small>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                        <i data-lucide="receipt"></i>
                    </div>
                    <span id="avg-order-growth-badge" class="badge bg-info bg-opacity-10 text-info" style="display: none;">
                        <i data-lucide="trending-up" style="width: 12px; height: 12px;"></i>
                        <span id="avg-order-growth-value">0%</span>
                    </span>
                </div>
                <h3 class="h4 fw-bold mb-1" id="avg-order-value">$0</h3>
                <p class="text-muted small mb-0">Ticket Promedio</p>
                <small class="text-muted" id="compare-avg-order-text" style="display: none;"></small>
            </div>
        </div>

        <!-- Conversion Rate (if applicable) -->
        <div class="col-6 col-md-6 col-lg-3">
            <div class="glass-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center"
                         style="width: 48px; height: 48px; background: rgba(219, 39, 119, 0.1); color: rgba(219, 39, 119, 1);">
                        <i data-lucide="target"></i>
                    </div>
                </div>
                <h3 class="h4 fw-bold mb-1" id="completed-orders-value">0</h3>
                <p class="text-muted small mb-0">Pedidos Completados</p>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Sales by Day Chart -->
        <div class="col-12 col-lg-8">
            <div class="glass-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="h5 fw-bold mb-1">Ventas por Día</h4>
                        <p class="text-muted small mb-0">Evolución de ventas en el período seleccionado</p>
                    </div>
                </div>
                <div class="chart-container" style="height: 350px; position: relative;">
                    <canvas id="salesByDayChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Sales by Payment Method -->
        <div class="col-12 col-lg-4">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Ventas por Método de Pago</h4>
                <div class="chart-container" style="height: 350px; position: relative;">
                    <canvas id="salesByPaymentMethodChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4 mb-4">
        <!-- Sales by Restaurant -->
        <div class="col-12 col-lg-6">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Ventas por Restaurante</h4>
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="salesByRestaurantChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Orders by Status -->
        <div class="col-12 col-lg-6">
            <div class="glass-card p-4">
                <h4 class="h5 fw-bold mb-4">Pedidos por Estado</h4>
                <div class="chart-container" style="height: 300px; position: relative;">
                    <canvas id="ordersByStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="row g-4 mb-4">
        <div class="col-12">
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
                            </tr>
                        </thead>
                        <tbody id="top-products-table-body">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Cargando...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Empty State -->
<div id="sales-empty" class="glass-card p-5 text-center" style="display: none;">
    <div class="mb-4">
        <i data-lucide="trending-up" style="width: 64px; height: 64px; color: var(--color-primary-light); opacity: 0.5;"></i>
    </div>
    <h3 class="h4 fw-bold mb-2" id="sales-empty-title">Aún no hay datos de ventas</h3>
    <p class="text-muted mb-4" id="sales-empty-text">Una vez que comiences a recibir pedidos, verás estadísticas y gráficos de ventas aquí.</p>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-cartify-primary">
        <i data-lucide="shopping-cart" class="me-2" style="width: 18px;"></i>
        Ver Pedidos
    </a>
</div>

<script>
    // Sales tab initialization
    window.initSalesTab = function() {
        loadSalesData();
    };

    window.loadSalesData = async function() {
        const loadingEl = document.getElementById('sales-loading');
        const contentEl = document.getElementById('sales-content');
        const emptyEl = document.getElementById('sales-empty');

        loadingEl.style.display = 'block';
        contentEl.style.display = 'none';
        emptyEl.style.display = 'none';

        try {
            const restaurantId = document.getElementById('restaurant-filter')?.value || '';
            const params = new URLSearchParams({ restaurant_id: restaurantId });

            const response = await fetch(`{{ route('api.sales') }}?${params}`, {
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

            // Tener datos = total_orders > 0 o total_sales > 0 (por si viene como número o string)
            const totalOrders = Number(data.total_orders) || 0;
            const totalSales = Number(data.total_sales) || 0;
            const hasData = totalOrders > 0 || totalSales > 0;

            if (!hasData) {
                loadingEl.style.display = 'none';
                emptyEl.style.display = 'block';
                const titleEl = document.getElementById('sales-empty-title');
                const textEl = document.getElementById('sales-empty-text');
                if (titleEl && textEl) {
                    titleEl.textContent = 'Aún no hay datos de ventas';
                    textEl.textContent = 'Una vez que comiences a recibir pedidos, verás estadísticas y gráficos de ventas aquí.';
                }
                if (window.lucide?.createIcons) window.lucide.createIcons();
                return;
            }

            // Asegurar arrays para no romper .map() en gráficos
            const safe = {
                total_sales: totalSales,
                total_orders: totalOrders,
                avg_order_value: Number(data.avg_order_value) || 0,
                sales_by_day: Array.isArray(data.sales_by_day) ? data.sales_by_day : [],
                sales_by_payment_method: Array.isArray(data.sales_by_payment_method) ? data.sales_by_payment_method : [],
                sales_by_restaurant: Array.isArray(data.sales_by_restaurant) ? data.sales_by_restaurant : [],
                top_products: Array.isArray(data.top_products) ? data.top_products : [],
                orders_by_status: data.orders_by_status && typeof data.orders_by_status === 'object' ? data.orders_by_status : {},
                compare_total_sales: data.compare_total_sales,
                compare_total_orders: data.compare_total_orders,
                compare_avg_order_value: data.compare_avg_order_value,
                sales_growth: data.sales_growth,
                orders_growth: data.orders_growth,
                avg_order_growth: data.avg_order_growth,
            };

            loadingEl.style.display = 'none';
            contentEl.style.display = 'block';

            try { updateSalesMetrics(safe); } catch (e) { console.error('updateSalesMetrics:', e); }
            try { updateSalesCharts(safe); } catch (e) { console.error('updateSalesCharts:', e); }
            try { updateTopProductsTable(safe.top_products); } catch (e) { console.error('updateTopProductsTable:', e); }
        } catch (error) {
            console.error('Error loading sales data:', error);
            loadingEl.style.display = 'none';
            emptyEl.style.display = 'block';
        }
    };

    function updateSalesMetrics(data) {
        // Format currency
        const formatCurrency = (value) => {
            return '$' + new Intl.NumberFormat('es-UY').format(value.toFixed(0));
        };

        const formatPercent = (value) => {
            if (value === null) return '';
            const sign = value >= 0 ? '+' : '';
            return sign + value.toFixed(1) + '%';
        };

        // Total Sales
        const totalSalesEl = document.getElementById('total-sales-value');
        if (totalSalesEl) totalSalesEl.textContent = formatCurrency(Number(data.total_sales) || 0);
        if (data.compare_total_sales != null) {
            const growth = data.sales_growth;
            const badge = document.getElementById('sales-growth-badge');
            badge.style.display = 'inline-flex';
            badge.className = growth >= 0
                ? 'badge bg-success bg-opacity-10 text-success'
                : 'badge bg-danger bg-opacity-10 text-danger';
            document.getElementById('sales-growth-value').textContent = formatPercent(growth);
            document.getElementById('compare-sales-text').textContent =
                `Período anterior: ${formatCurrency(data.compare_total_sales)}`;
            document.getElementById('compare-sales-text').style.display = 'block';
        }

        // Total Orders
        const totalOrdersEl = document.getElementById('total-orders-value');
        if (totalOrdersEl) totalOrdersEl.textContent = (Number(data.total_orders) || 0).toLocaleString();
        if (data.compare_total_orders != null) {
            const growth = data.orders_growth;
            const badge = document.getElementById('orders-growth-badge');
            badge.style.display = 'inline-flex';
            badge.className = growth >= 0
                ? 'badge bg-success bg-opacity-10 text-success'
                : 'badge bg-danger bg-opacity-10 text-danger';
            document.getElementById('orders-growth-value').textContent = formatPercent(growth);
            document.getElementById('compare-orders-text').textContent =
                `Período anterior: ${data.compare_total_orders}`;
            document.getElementById('compare-orders-text').style.display = 'block';
        }

        // Average Order Value
        const avgOrderEl = document.getElementById('avg-order-value');
        if (avgOrderEl) avgOrderEl.textContent = formatCurrency(Number(data.avg_order_value) || 0);
        if (data.compare_avg_order_value != null) {
            const growth = data.avg_order_growth;
            const badge = document.getElementById('avg-order-growth-badge');
            badge.style.display = 'inline-flex';
            badge.className = growth >= 0
                ? 'badge bg-success bg-opacity-10 text-success'
                : 'badge bg-danger bg-opacity-10 text-danger';
            document.getElementById('avg-order-growth-value').textContent = formatPercent(growth);
            document.getElementById('compare-avg-order-text').textContent =
                `Período anterior: ${formatCurrency(data.compare_avg_order_value)}`;
            document.getElementById('compare-avg-order-text').style.display = 'block';
        }

        // Completed Orders
        const completed = data.orders_by_status?.delivered || 0;
        const completedEl = document.getElementById('completed-orders-value');
        if (completedEl) completedEl.textContent = completed.toLocaleString();
    }

    function updateSalesCharts(data) {
        const byDay = Array.isArray(data.sales_by_day) ? data.sales_by_day : [];
        const byPayment = Array.isArray(data.sales_by_payment_method) ? data.sales_by_payment_method : [];
        const byRestaurant = Array.isArray(data.sales_by_restaurant) ? data.sales_by_restaurant : [];

        // Sales by Day Chart
        const salesByDayCtx = document.getElementById('salesByDayChart');
        if (window.salesByDayChart && typeof window.salesByDayChart.destroy === 'function') {
            window.salesByDayChart.destroy();
        }
        window.salesByDayChart = null;
        if (salesByDayCtx && typeof Chart !== 'undefined') {
        window.salesByDayChart = new Chart(salesByDayCtx, {
            type: 'line',
            data: {
                labels: byDay.map(d => d.label),
                datasets: [{
                    label: 'Ventas',
                    data: byDay.map(d => d.sales),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    tension: 0.4,
                    fill: true,
                }, {
                    label: 'Pedidos',
                    data: byDay.map(d => d.orders),
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
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        ticks: { color: '#9ca3af' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
        }

        // Sales by Payment Method Chart
        const salesByPaymentCtx = document.getElementById('salesByPaymentMethodChart');
        if (window.salesByPaymentMethodChart && typeof window.salesByPaymentMethodChart.destroy === 'function') {
            window.salesByPaymentMethodChart.destroy();
        }
        window.salesByPaymentMethodChart = null;
        if (salesByPaymentCtx && typeof Chart !== 'undefined') {
        window.salesByPaymentMethodChart = new Chart(salesByPaymentCtx, {
            type: 'doughnut',
            data: {
                labels: byPayment.map(d => d.payment_method_label),
                datasets: [{
                    data: byPayment.map(d => d.total_sales),
                    backgroundColor: [
                        '#7c3aed',
                        '#10b981',
                        '#3b82f6',
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
                                const label = context.label || '';
                                const value = '$' + context.parsed.toLocaleString();
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percent = ((context.parsed / total) * 100).toFixed(1);
                                return `${label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });
        }

        // Sales by Restaurant Chart
        const salesByRestaurantCtx = document.getElementById('salesByRestaurantChart');
        if (window.salesByRestaurantChart && typeof window.salesByRestaurantChart.destroy === 'function') {
            window.salesByRestaurantChart.destroy();
        }
        window.salesByRestaurantChart = null;
        if (salesByRestaurantCtx && byRestaurant.length > 0 && typeof Chart !== 'undefined') {
            window.salesByRestaurantChart = new Chart(salesByRestaurantCtx, {
                type: 'bar',
                data: {
                    labels: byRestaurant.map(d => d.restaurant_name),
                    datasets: [{
                        label: 'Ventas',
                        data: byRestaurant.map(d => d.total_sales),
                        backgroundColor: '#7c3aed',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#9ca3af' },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        },
                        y: {
                            ticks: {
                                color: '#9ca3af',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.1)' }
                        }
                    }
                }
            });
        }

        // Orders by Status Chart
        const ordersByStatusCtx = document.getElementById('ordersByStatusChart');
        if (window.ordersByStatusChart && typeof window.ordersByStatusChart.destroy === 'function') {
            window.ordersByStatusChart.destroy();
        }
        window.ordersByStatusChart = null;
        const statusLabels = {
            'pending': 'Pendiente',
            'confirmed': 'Confirmado',
            'preparing': 'Preparando',
            'ready': 'Listo',
            'out_for_delivery': 'En camino',
            'delivered': 'Entregado',
            'cancelled': 'Cancelado',
        };
        const statusData = Object.entries(data.orders_by_status || {}).map(([status, count]) => ({
            label: statusLabels[status] || status,
            count: count
        }));

        if (ordersByStatusCtx && statusData.length > 0 && typeof Chart !== 'undefined') {
            window.ordersByStatusChart = new Chart(ordersByStatusCtx, {
                type: 'pie',
                data: {
                    labels: statusData.map(d => d.label),
                    datasets: [{
                        data: statusData.map(d => d.count),
                        backgroundColor: [
                            '#fbbf24',
                            '#3b82f6',
                            '#7c3aed',
                            '#10b981',
                            '#6366f1',
                            '#10b981',
                            '#ef4444',
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
    }

    function updateTopProductsTable(products) {
        const tbody = document.getElementById('top-products-table-body');
        if (!products || products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-4">No hay productos vendidos en este período</td></tr>';
            return;
        }

        tbody.innerHTML = products.map((product, index) => `
            <tr>
                <td>${index + 1}</td>
                <td><strong>${product.product_name}</strong></td>
                <td class="text-end">${product.total_quantity.toLocaleString()}</td>
                <td class="text-end"><strong>$${product.total_revenue.toLocaleString()}</strong></td>
            </tr>
        `).join('');
    }

    // Initialize on tab show (custom tab system)
    // This will be called by the parent dashboard when tab is switched
</script>
