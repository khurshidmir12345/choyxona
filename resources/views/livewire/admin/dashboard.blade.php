<div>
    <div class="pos-page-head">
        <div>
            <h3>Bosh sahifa</h3>
            <p>Savdo va foyda ko'rsatkichlari</p>
        </div>
        <div class="pos-head-actions">
            <select wire:model.live="selectedPeriod" class="form-select" style="width:auto">
                @foreach(\App\Livewire\Admin\Dashboard::PERIODS as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPeriod === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" wire:model.live="startDate" value="{{ $startDate }}" class="form-control" style="width:auto">
            <input type="date" wire:model.live="endDate" value="{{ $endDate }}" class="form-control" style="width:auto">
        </div>
    </div>

    {{-- ----------------------------------------------------- asosiy raqamlar --}}
    <div class="row">
        <div class="col-md-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-blue">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <p class="stat-label">Tushum</p>
                        <p class="stat-value">{{ number_format($revenue, 0, ',', ' ') }} <small>so'm</small></p>
                        <p class="stat-hint">Kuniga o'rtacha {{ number_format($dailyAverage, 0, ',', ' ') }}</p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-cash-multiple"></i></span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-violet">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <p class="stat-label">Yalpi foyda</p>
                        <p class="stat-value">{{ number_format($profit, 0, ',', ' ') }} <small>so'm</small></p>
                        <p class="stat-hint">Rentabellik {{ $profitMargin }}%</p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-trending-up"></i></span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-amber">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <p class="stat-label">Xarajatlar</p>
                        <p class="stat-value">{{ number_format($expenses, 0, ',', ' ') }} <small>so'm</small></p>
                        <p class="stat-hint">Faqat tasdiqlangan</p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-wallet-outline"></i></span>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 grid-margin">
            <div class="card stat-card {{ $netProfit >= 0 ? 'stat-green' : 'stat-pink' }}">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <p class="stat-label">Sof foyda</p>
                        <p class="stat-value">{{ number_format($netProfit, 0, ',', ' ') }} <small>so'm</small></p>
                        <p class="stat-hint">Foyda − xarajat</p>
                    </div>
                    <span class="stat-icon">
                        <i class="mdi mdi-{{ $netProfit >= 0 ? 'trending-up' : 'trending-down' }}"></i>
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- ------------------------------------------------- ikkilamchi raqamlar --}}
    <div class="row">
        <div class="col-6 col-md-4 col-xl-2 grid-margin">
            <div class="card stat-card stat-light">
                <div class="card-body">
                    <p class="stat-label">Buyurtmalar</p>
                    <p class="stat-value">{{ number_format($ordersCount, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-3 grid-margin">
            <div class="card stat-card stat-light">
                <div class="card-body">
                    <p class="stat-label">O'rtacha chek</p>
                    <p class="stat-value">{{ number_format($averageCheck, 0, ',', ' ') }} <small>so'm</small></p>
                </div>
            </div>
        </div>

        @foreach($ordersByType as $row)
            @php
                $typeValue = $row->type?->value ?? $row->type;
                [$typeLabel, $typeIcon] = match($typeValue) {
                    'delivery' => ['Yetkazib berish', 'mdi-truck-delivery-outline'],
                    'takeaway' => ['Olib ketish', 'mdi-shopping-outline'],
                    'cafe' => ['Zalda', 'mdi-sofa-outline'],
                    default => ['Boshqa', 'mdi-help-circle-outline'],
                };
            @endphp
            <div class="col-6 col-md-4 col-xl-2 grid-margin">
                <div class="card stat-card stat-light">
                    <div class="card-body">
                        <p class="stat-label"><i class="mdi {{ $typeIcon }} me-1"></i>{{ $typeLabel }}</p>
                        <p class="stat-value">{{ number_format((int) $row->revenue, 0, ',', ' ') }}</p>
                        <p class="stat-hint">{{ $row->orders_count }} ta buyurtma</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- --------------------------------------------------------------- grafik --}}
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                        <h4 class="card-title mb-0">Sotuv va xarajat dinamikasi</h4>
                        <div class="d-flex align-items-center gap-3 small text-muted">
                            <span><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px;height:10px;background:#7DA0FA"></span>Sotuv</span>
                            <span><span class="d-inline-block rounded-circle me-1"
                                        style="width:10px;height:10px;background:#E29E09"></span>Xarajat</span>
                        </div>
                    </div>

                    @if(empty($chartData))
                        <div class="empty-state">
                            <i class="mdi mdi-chart-bar"></i>
                            <h6>Ma'lumot yo'q</h6>
                        </div>
                    @else
                        @php
                            $peak = max(1, max(array_merge(
                                array_column($chartData, 'sales'),
                                array_column($chartData, 'expenses')
                            )));
                        @endphp
                        <div class="mini-chart">
                            @foreach($chartData as $point)
                                <div class="chart-col">
                                    <div class="chart-bars">
                                        <span class="bar-sales"
                                              style="height: {{ max(1, round($point['sales'] / $peak * 100)) }}%"></span>
                                        <span class="bar-expenses"
                                              style="height: {{ max(1, round($point['expenses'] / $peak * 100)) }}%"></span>
                                    </div>
                                    <div class="chart-tip">
                                        <strong>{{ $point['label'] }}</strong><br>
                                        Sotuv: {{ number_format($point['sales'], 0, ',', ' ') }}<br>
                                        Xarajat: {{ number_format($point['expenses'], 0, ',', ' ') }}
                                    </div>
                                    <span class="chart-label">{{ $point['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- top --}}
    <div class="row">
        @foreach([
            ['Eng ko\'p sotilgan mahsulotlar', $topProducts, 'mdi-food-variant'],
            ['Eng daromadli kategoriyalar', $topCategories, 'mdi-tag-multiple-outline'],
        ] as [$cardTitle, $rows, $cardIcon])
            <div class="col-lg-6 grid-margin">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="mdi {{ $cardIcon }} text-primary me-1"></i> {{ $cardTitle }}
                        </h4>

                        @if(empty($rows))
                            <div class="empty-state">
                                <i class="mdi {{ $cardIcon }}"></i>
                                <h6>Ma'lumot yo'q</h6>
                            </div>
                        @else
                            @php $best = max(1, (float) $rows[0]->revenue); @endphp
                            @foreach($rows as $index => $row)
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-baseline">
                                        <span class="text-truncate">
                                            <span class="text-muted fw-bold me-2 tabular">{{ $index + 1 }}</span>
                                            <span class="fw-semibold">{{ $row->name }}</span>
                                        </span>
                                        <span class="fw-bold tabular ms-2 text-nowrap">
                                            {{ number_format((float) $row->revenue, 0, ',', ' ') }}
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <div class="rank-bar flex-grow-1">
                                            <span style="width: {{ round((float) $row->revenue / $best * 100) }}%"></span>
                                        </div>
                                        <small class="text-muted tabular text-nowrap" style="width:70px;text-align:right">
                                            {{ (int) $row->quantity }} dona
                                        </small>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
