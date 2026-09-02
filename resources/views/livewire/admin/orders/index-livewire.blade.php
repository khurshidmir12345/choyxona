<div>
    @php $activeFilters = collect([$fromDate, $toDate, $type, $status])->filter()->count(); @endphp
    <div x-data="{ filters: @js($activeFilters > 0) }">
    <div class="pos-page-head">
        <div class="pos-head-title">
            <h3>Buyurtmalar</h3>
            <p>Barcha savdo tarixi</p>
        </div>
        <div class="pos-head-tools">
            <div class="stat-strip">
                <div class="stat-mini tone-blue">
                    <i class="mdi mdi-clipboard-text-outline"></i>
                    <span><small>Buyurtmalar</small><strong>{{ number_format($ordersCount, 0, ',', ' ') }}</strong></span>
                </div>
                <div class="stat-mini tone-green">
                    <i class="mdi mdi-cash-multiple"></i>
                    <span><small>Summa</small><strong>{{ number_format($revenue, 0, ',', ' ') }}</strong></span>
                </div>
            </div>
            <button type="button" class="filter-toggle" :class="{ 'is-open': filters }" x-on:click="filters = !filters">
                <i class="mdi mdi-filter-variant"></i> Filtr
                @if($activeFilters) <span class="filter-count">{{ $activeFilters }}</span> @endif
            </button>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('orders.create') }}" class="btn btn-primary btn-rounded">
                <i class="mdi mdi-plus me-1"></i> Yangi sotuv
            </a>
            <a href="{{ route('orders.deleted') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-archive-outline me-1"></i> Arxiv
            </a>
        </div>
    </div>

    <div class="filter-panel" x-show="filters" x-cloak x-transition.opacity.duration.150ms>
        <div class="filter-grid">
            <div>
                <label>Sanadan</label>
                <input type="date" wire:model.live="fromDate" value="{{ $fromDate }}" class="form-control">
            </div>
            <div>
                <label>Sanagacha</label>
                <input type="date" wire:model.live="toDate" value="{{ $toDate }}" class="form-control">
            </div>
            <div>
                <label>Turi</label>
                <select wire:model.live="type" class="form-select">
                    <option value="">Barchasi</option>
                    <option value="delivery">Yetkazib berish</option>
                    <option value="takeaway">Olib ketish</option>
                    <option value="cafe">Zalda</option>
                </select>
            </div>
            <div>
                <label>Holati</label>
                <select wire:model.live="status" class="form-select">
                    <option value="">Barchasi</option>
                    <option value="opened">Ochiq</option>
                    <option value="done">Yopilgan</option>
                    <option value="cancelled">Bekor qilingan</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-inverse-secondary" wire:click="clearFilters">
                    <i class="mdi mdi-refresh me-1"></i> Tozalash
                </button>
            </div>
        </div>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-clipboard-text-outline"></i>
                    <h6>Buyurtma topilmadi</h6>
                    <p>Tanlangan filtr bo'yicha hech narsa yo'q.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Sana</th>
                            <th>Joy</th>
                            <th>Mijoz</th>
                            <th>Turi</th>
                            <th>Holati</th>
                            <th class="text-end">Summa</th>
                            <th>Kassir</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr wire:key="order-{{ $order->id }}">
                                <td class="fw-bold tabular">{{ $order->id }}</td>
                                <td class="text-nowrap tabular text-muted">
                                    {{ $order->created_at?->format('d.m.Y H:i') ?? '—' }}
                                </td>
                                <td>{{ $order->place?->name ?? '—' }}</td>
                                <td>
                                    @if($order->customer)
                                        <a href="{{ route('customers.show', $order->customer->id) }}" class="fw-semibold text-dark">{{ $order->customer->name }}</a>
                                        @if($order->customer->phone)
                                            <small class="d-block text-muted tabular">{{ $order->customer->formattedPhone() }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @include('livewire.admin.orders.partials.order-type', ['type' => $order->type])
                                    @if($order->delivery_address)
                                        <small class="d-block text-muted text-truncate" style="max-width:200px" title="{{ $order->delivery_address }}">
                                            <i class="mdi mdi-map-marker-outline"></i> {{ $order->delivery_address }}
                                        </small>
                                    @endif
                                </td>
                                <td>@include('livewire.admin.orders.partials.order-status', ['status' => $order->status])</td>
                                <td class="text-end">
                                    <span class="fw-bold tabular">
                                        {{ number_format((int) $order->total_amount, 0, ',', ' ') }}
                                    </span>
                                    @if($order->discount)
                                        <small class="d-block text-danger">-{{ $order->discount }}%</small>
                                    @endif
                                </td>
                                <td class="text-muted">{{ $order->user?->name ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="toggleDetails({{ $order->id }})">
                                        <i class="mdi mdi-{{ $expandedOrderId === $order->id ? 'chevron-up' : 'format-list-bulleted' }}"></i>
                                    </button>
                                    @if($order->status === \App\Casts\OrderStatusEnum::Done)
                                        <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank"
                                           class="btn btn-inverse-success btn-sm" title="Chek">
                                            <i class="mdi mdi-printer"></i>
                                        </a>
                                    @endif
                                    <x-confirm-button :call="'delete('.$order->id.')'"
                                                      title="Buyurtma arxivga o'tkazilsinmi?"
                                                      text="Keyinchalik arxivdan tiklash mumkin."
                                                      confirm-text="Ha, arxivga"
                                                      icon="mdi-archive-arrow-down-outline"/>
                                </td>
                            </tr>

                            @if($expandedOrderId === $order->id)
                                <tr wire:key="details-{{ $order->id }}">
                                    <td colspan="9" class="p-2">
                                        @include('livewire.admin.orders.partials.details-table', ['details' => $details])
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
