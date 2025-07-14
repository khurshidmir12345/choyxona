<div class="container-fluid">

    <!-- Date Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <label class="form-label">Davr tanlang:</label>
                            <select wire:model.live="selectedPeriod" class="form-select">
                                <option value="today">Bugun</option>
                                <option value="yesterday">Kecha</option>
                                <option value="week">Bu hafta</option>
                                <option value="month">Bu oy</option>
                                <option value="last_month">O'tgan oy</option>
                                <option value="custom">Maxsus davr</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Boshlang'ich sana:</label>
                            <input type="date" wire:model.live="startDate" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tugash sana:</label>
                            <input type="date" wire:model.live="endDate" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button class="btn btn-primary" wire:click="loadDashboardData">
                                    <i class="fas fa-sync-alt"></i> Yangilash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="dash-widget-header">
                        <span class="dash-widget-icon text-primary">
                            <i class="fas fa-money-bill-wave"></i>
                        </span>
                        <div class="dash-count">
                            <h3>{{ number_format($totalRevenue) }} so'm</h3>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h6 class="text-muted">Umumiy tushum</h6>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-primary" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="dash-widget-header">
                        <span class="dash-widget-icon text-success">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="dash-count">
                            <h3>{{ number_format($totalProfit) }} so'm</h3>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h6 class="text-muted">Umumiy foyda</h6>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="dash-widget-header">
                        <span class="dash-widget-icon text-warning">
                            <i class="fas fa-chart-line"></i>
                        </span>
                        <div class="dash-count">
                            <h3>{{ number_format($totalOrders) }}</h3>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h6 class="text-muted">Buyurtmalar soni</h6>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-warning" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 col-12">
            <div class="card">
                <div class="card-body">
                    <div class="dash-widget-header">
                        <span class="dash-widget-icon text-danger">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        <div class="dash-count">
                            <h3>{{ number_format($totalApprovedExpenses) }} so'm</h3>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h6 class="text-muted">Tasdiqlangan xarajatlar</h6>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products and Categories -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Eng ko'p sotiladigan mahsulotlar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Mahsulot</th>
                                    <th>Soni</th>
                                    <th>Tushum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topProducts as $productId => $product)
                                    <tr>
                                        <td>{{ $product['name'] }}</td>
                                        <td>{{ $product['quantity'] }}</td>
                                        <td>{{ number_format($product['revenue']) }} so'm</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Ma'lumot yo'q</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Eng ko'p sotiladigan kategoriyalar</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kategoriya</th>
                                    <th>Soni</th>
                                    <th>Tushum</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topCategories as $categoryId => $category)
                                    <tr>
                                        <td>{{ $category['name'] }}</td>
                                        <td>{{ $category['quantity'] }}</td>
                                        <td>{{ number_format($category['revenue']) }} so'm</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">Ma'lumot yo'q</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Business Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Biznes ko'rsatkichlari</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-primary">{{ $profitMargin }}%</h4>
                                <p class="text-muted">Foyda marjasi</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-danger">{{ number_format($totalApprovedExpenses) }} so'm</h4>
                                <p class="text-muted">Tasdiqlangan xarajatlar</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-warning">{{ number_format($averageProfit) }} so'm</h4>
                                <p class="text-muted">O'rtacha foyda</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <h4 class="text-info">{{ number_format($dailyAverage) }} so'm</h4>
                                <p class="text-muted">Kunlik o'rtacha</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Creative Charts Section -->
    <div class="row mb-4">
        <!-- Main Sales vs Expenses Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line text-primary me-2"></i>
                        Savdo va Xarajatlar Dinamikasi
                    </h5>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary active" onclick="switchChartType('line')">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="switchChartType('bar')">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary" onclick="switchChartType('area')">
                            <i class="fas fa-chart-area"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 400px;">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mini Charts -->
        <div class="col-lg-4">
            <div class="row">
                <!-- Profit Trend -->
                <div class="col-12 mb-3">
                    <div class="card bg-gradient-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Foyda Trendi</h6>
                                    <h4 class="mb-0">{{ number_format($totalProfit - $totalApprovedExpenses) }} so'm</h4>
                                    <small>Net foyda</small>
                                </div>
                                <div style="width: 80px; height: 60px;">
                                    <canvas id="profitTrendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue vs Expenses Ratio -->
                <div class="col-12 mb-3">
                    <div class="card bg-gradient-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Tushum/Xarajat</h6>
                                    <h4 class="mb-0">{{ $totalApprovedExpenses > 0 ? round($totalRevenue / $totalApprovedExpenses, 1) : 0 }}x</h4>
                                    <small>Nisbat</small>
                                </div>
                                <div style="width: 80px; height: 60px;">
                                    <canvas id="ratioChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Performance -->
                <div class="col-12">
                    <div class="card bg-gradient-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="card-title">Kunlik Faoliyat</h6>
                                    <h4 class="mb-0">{{ $totalOrders }}</h4>
                                    <small>Buyurtmalar</small>
                                </div>
                                <div style="width: 80px; height: 60px;">
                                    <canvas id="performanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Creative Chart Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let mainChart;
    let profitTrendChart;
    let ratioChart;
    let performanceChart;
    let currentChartType = 'line';
    
    const chartData = @json($chartData);
    
    // Format numbers for display
    function formatCurrency(value) {
        return new Intl.NumberFormat('uz-UZ', {
            style: 'currency',
            currency: 'UZS',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(value);
    }
    
    // Create main chart
    function createMainChart() {
        const ctx = document.getElementById('mainChart').getContext('2d');
        
        if (mainChart) {
            mainChart.destroy();
        }
        
        // Debug chart data
        console.log('Chart data:', chartData);
        console.log('Current chart type:', currentChartType);
        
        const gradientSales = ctx.createLinearGradient(0, 0, 0, 400);
        gradientSales.addColorStop(0, 'rgba(40, 167, 69, 0.8)');
        gradientSales.addColorStop(1, 'rgba(40, 167, 69, 0.1)');
        
        const gradientExpenses = ctx.createLinearGradient(0, 0, 0, 400);
        gradientExpenses.addColorStop(0, 'rgba(220, 53, 69, 0.8)');
        gradientExpenses.addColorStop(1, 'rgba(220, 53, 69, 0.1)');
        
        const gradientProfit = ctx.createLinearGradient(0, 0, 0, 400);
        gradientProfit.addColorStop(0, 'rgba(255, 193, 7, 0.8)');
        gradientProfit.addColorStop(1, 'rgba(255, 193, 7, 0.1)');
        
        // Chart type va fill sozlamalari
        const chartConfig = {
            line: {
                type: 'line',
                fill: false,
                backgroundColor: 'transparent'
            },
            bar: {
                type: 'bar',
                fill: false,
                backgroundColor: ['#28a745', '#dc3545', '#ffc107']
            },
            area: {
                type: 'line',
                fill: true,
                backgroundColor: [gradientSales, gradientExpenses, gradientProfit]
            }
        };
        
        const config = chartConfig[currentChartType];
        
        mainChart = new Chart(ctx, {
            type: config.type,
            data: {
                labels: chartData.map(item => item.date),
                datasets: [
                    {
                        label: 'Savdo',
                        data: chartData.map(item => item.sales),
                        borderColor: '#28a745',
                        backgroundColor: currentChartType === 'bar' ? '#28a745' : (currentChartType === 'area' ? gradientSales : 'transparent'),
                        borderWidth: 3,
                        fill: currentChartType === 'area',
                        tension: 0.4,
                        pointBackgroundColor: '#28a745',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Xarajatlar',
                        data: chartData.map(item => item.expenses),
                        borderColor: '#dc3545',
                        backgroundColor: currentChartType === 'bar' ? '#dc3545' : (currentChartType === 'area' ? gradientExpenses : 'transparent'),
                        borderWidth: 3,
                        fill: currentChartType === 'area',
                        tension: 0.4,
                        pointBackgroundColor: '#dc3545',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    },
                    {
                        label: 'Net Foyda',
                        data: chartData.map(item => item.profit),
                        borderColor: '#ffc107',
                        backgroundColor: currentChartType === 'bar' ? '#ffc107' : (currentChartType === 'area' ? gradientProfit : 'transparent'),
                        borderWidth: 3,
                        fill: currentChartType === 'area',
                        tension: 0.4,
                        pointBackgroundColor: '#ffc107',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#28a745',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)',
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            },
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }
    
    // Create mini charts
    function createMiniCharts() {
        // Profit Trend Chart
        const profitCtx = document.getElementById('profitTrendChart').getContext('2d');
        profitTrendChart = new Chart(profitCtx, {
            type: 'doughnut',
            data: {
                labels: ['Foyda', 'Xarajat'],
                datasets: [{
                    data: [{{ $totalProfit }}, {{ $totalApprovedExpenses }}],
                    backgroundColor: ['#28a745', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                cutout: '70%'
            }
        });
        
        // Ratio Chart
        const ratioCtx = document.getElementById('ratioChart').getContext('2d');
        const ratio = {{ $totalApprovedExpenses > 0 ? $totalRevenue / $totalApprovedExpenses : 0 }};
        ratioChart = new Chart(ratioCtx, {
            type: 'doughnut',
            data: {
                labels: ['Tushum', 'Xarajat'],
                datasets: [{
                    data: [{{ $totalRevenue }}, {{ $totalApprovedExpenses }}],
                    backgroundColor: ['#20c997', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                cutout: '70%'
            }
        });
        
        // Performance Chart
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        performanceChart = new Chart(perfCtx, {
            type: 'doughnut',
            data: {
                labels: ['Buyurtmalar', 'Bekor'],
                datasets: [{
                    data: [{{ $totalOrders }}, {{ max(1, $totalOrders * 0.1) }}],
                    backgroundColor: ['#17a2b8', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                },
                cutout: '70%'
            }
        });
    }
    
    // Switch chart type
    window.switchChartType = function(type) {
        currentChartType = type;
        createMainChart();
        
        // Update button states
        document.querySelectorAll('.btn-group .btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Find and activate the correct button
        const buttons = document.querySelectorAll('.btn-group .btn');
        buttons.forEach((btn, index) => {
            if (btn.onclick && btn.onclick.toString().includes(type)) {
                btn.classList.add('active');
            }
        });
    };
    
    // Initialize charts with a small delay to ensure DOM is ready
    setTimeout(() => {
        createMainChart();
        createMiniCharts();
    }, 100);
    
    // Listen for Livewire chart update events
    window.addEventListener('chart-updated', function() {
        setTimeout(() => {
            createMainChart();
            createMiniCharts();
        }, 200);
    });
    
    // Also listen for Livewire component updates
    document.addEventListener('livewire:updated', function() {
        setTimeout(() => {
            createMainChart();
            createMiniCharts();
        }, 200);
    });
});
</script>

