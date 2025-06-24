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
                            <i class="fas fa-shopping-cart"></i>
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
                        <span class="dash-widget-icon text-info">
                            <i class="fas fa-calculator"></i>
                        </span>
                        <div class="dash-count">
                            <h3>{{ number_format($averageOrderValue) }} so'm</h3>
                        </div>
                    </div>
                    <div class="dash-widget-info">
                        <h6 class="text-muted">O'rtacha buyurtma</h6>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-info" style="width: 100%"></div>
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
    <div class="row">
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
                                <h4 class="text-success">{{ number_format($averageOrderValue) }} so'm</h4>
                                <p class="text-muted">O'rtacha buyurtma</p>
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
</div>

