// Dashboard Analytics and Charts
// Chart.js is loaded via CDN in the blade template

class DashboardManager {
    constructor() {
        this.apiBase = '/dashboard-api';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        this.charts = {};
        this.currentPeriod = '7days';
    }

    async init() {
        await this.loadAnalytics();
        this.setupPeriodToggle();
        this.updateCharts();
    }

    async loadAnalytics(period = '7days') {
        try {
            const response = await fetch(`${this.apiBase}/analytics?period=${period}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Error loading analytics:', error);
            return null;
        }
    }

    setupPeriodToggle() {
        const periodButtons = document.querySelectorAll('[data-period]');
        periodButtons.forEach(btn => {
            btn.addEventListener('click', async (e) => {
                e.preventDefault();
                const period = btn.getAttribute('data-period');
                this.currentPeriod = period;
                
                // Update active state
                periodButtons.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                // Reload data and update charts
                const data = await this.loadAnalytics(period);
                if (data) {
                    this.updateChartsWithData(data);
                }
            });
        });
    }

    updateCharts() {
        this.loadAnalytics(this.currentPeriod).then(data => {
            if (data) {
                this.updateChartsWithData(data);
            }
        });
    }

    updateChartsWithData(data) {
        this.renderScansByDayChart(data.scans_by_day);
        this.renderScansByHourChart(data.scans_by_hour);
        this.renderTopQrsChart(data.top_qrs);
    }

    renderScansByDayChart(data) {
        const ctx = document.getElementById('scansByDayChart');
        if (!ctx || !data || data.length === 0) return;

        // Destroy existing chart
        if (this.charts.scansByDay) {
            this.charts.scansByDay.destroy();
        }

        this.charts.scansByDay = new window.Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    label: 'Escaneos',
                    data: data.map(d => d.count),
                    borderColor: 'rgb(124, 58, 237)',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(124, 58, 237)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(124, 58, 237, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)'
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    }
                }
            }
        });
    }

    renderScansByHourChart(data) {
        const ctx = document.getElementById('scansByHourChart');
        if (!ctx || !data || data.length === 0) return;

        if (this.charts.scansByHour) {
            this.charts.scansByHour.destroy();
        }

        this.charts.scansByHour = new window.Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.hour),
                datasets: [{
                    label: 'Escaneos por hora',
                    data: data.map(d => d.count),
                    backgroundColor: 'rgba(219, 39, 119, 0.6)',
                    borderColor: 'rgb(219, 39, 119)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(219, 39, 119, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    },
                    x: {
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.6)',
                            maxRotation: 45,
                            minRotation: 45
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.05)'
                        }
                    }
                }
            }
        });
    }

    renderTopQrsChart(data) {
        const ctx = document.getElementById('topQrsChart');
        if (!ctx) return;

        if (this.charts.topQrs) {
            this.charts.topQrs.destroy();
        }

        if (!data || data.length === 0) {
            const container = ctx.parentElement;
            if (container && !container.querySelector('.text-muted')) {
                container.innerHTML = '<p class="text-muted text-center py-5">No hay datos de escaneos aún</p>';
            }
            return;
        }

        this.charts.topQrs = new window.Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(qr => qr.name),
                datasets: [{
                    data: data.map(qr => qr.scans_count),
                    backgroundColor: [
                        'rgba(124, 58, 237, 0.8)',
                        'rgba(219, 39, 119, 0.8)',
                        'rgba(124, 58, 237, 0.6)',
                        'rgba(219, 39, 119, 0.6)',
                        'rgba(124, 58, 237, 0.4)'
                    ],
                    borderColor: [
                        'rgb(124, 58, 237)',
                        'rgb(219, 39, 119)',
                        'rgb(124, 58, 237)',
                        'rgb(219, 39, 119)',
                        'rgb(124, 58, 237)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: 'rgba(255, 255, 255, 0.8)',
                            padding: 15,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: 'rgba(124, 58, 237, 0.5)',
                        borderWidth: 1,
                        padding: 12
                    }
                }
            }
        });
    }
}

// Initialize dashboard when DOM is ready and Chart.js is loaded
function initDashboard() {
    if (typeof window.Chart === 'undefined') {
        setTimeout(initDashboard, 100);
        return;
    }
    
    if (document.getElementById('scansByDayChart')) {
        window.dashboardManager = new DashboardManager();
        window.dashboardManager.init();
    }
}

document.addEventListener('DOMContentLoaded', initDashboard);

// Export for global access
window.DashboardManager = DashboardManager;

