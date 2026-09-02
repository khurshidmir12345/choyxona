@php $customer = $this->customer; $stats = $this->stats; @endphp
<div>
    <div class="pos-page-head">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('customers.index') }}" class="btn btn-inverse-primary btn-sm rounded-circle"
               style="width:38px;height:38px;padding:0;display:inline-flex;align-items:center;justify-content:center" title="Mijozlar">
                <i class="mdi mdi-arrow-left"></i>
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="cust-avatar cust-avatar-lg">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                <div>
                    <h3>{{ $customer->name }}</h3>
                    <p>
                        {{ $customer->formattedPhone() ?? 'Telefon yo\'q' }}
                        @if($customer->note) · {{ $customer->note }} @endif
                    </p>
                </div>
            </div>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('orders.create') }}" class="btn btn-primary btn-rounded">
                <i class="mdi mdi-cart-outline me-1"></i> Yangi sotuv
            </a>
            <button type="button" class="btn btn-inverse-primary btn-rounded" wire:click="edit">
                <i class="mdi mdi-pencil-outline me-1"></i> Tahrirlash
            </button>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-sm-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-blue">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div><p class="stat-label">Buyurtmalar</p><p class="stat-value">{{ $stats['orders'] }}</p></div>
                    <span class="stat-icon"><i class="mdi mdi-clipboard-text-outline"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-green">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div><p class="stat-label">Jami xarid</p><p class="stat-value">{{ number_format($stats['total'], 0, ',', ' ') }} <small>so'm</small></p></div>
                    <span class="stat-icon"><i class="mdi mdi-cash-multiple"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-violet">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div><p class="stat-label">O'rtacha chek</p><p class="stat-value">{{ number_format($stats['average'], 0, ',', ' ') }} <small>so'm</small></p></div>
                    <span class="stat-icon"><i class="mdi mdi-receipt-text-outline"></i></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3 grid-margin">
            <div class="card stat-card stat-amber">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div><p class="stat-label">Oxirgi tashrif</p><p class="stat-value">{{ $stats['last']?->format('d.m.Y') ?? '—' }}</p></div>
                    <span class="stat-icon"><i class="mdi mdi-calendar-clock"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 grid-margin">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3"><i class="mdi mdi-map-marker-outline text-primary me-1"></i> Manzillar</h6>
                    @if($this->addresses === [])
                        <p class="text-muted small mb-0">Hali manzil aytilmagan. Yetkazib berishda kiritilgan manzillar shu yerda to'planadi.</p>
                    @else
                        <ul class="addr-list">
                            @foreach($this->addresses as $address)
                                <li>
                                    <i class="mdi {{ $address === $customer->address ? 'mdi-home-outline' : 'mdi-map-marker-outline' }}"></i>
                                    <span>{{ $address }}</span>
                                    @if($address === $customer->address)
                                        <span class="badge badge-outline-primary ms-auto">asosiy</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8 grid-margin">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="card-title fw-bold mb-3"><i class="mdi mdi-history text-primary me-1"></i> Savdo tarixi</h6>
                    @if($orders->isEmpty())
                        <div class="empty-state py-4">
                            <i class="mdi mdi-cart-off"></i>
                            <h6>Buyurtma yo'q</h6>
                            <p>Sotuv ekranida mijozni tanlab buyurtma bering.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sana</th>
                                    <th>Turi</th>
                                    <th>Holati</th>
                                    <th class="text-end">Summa</th>
                                    <th class="text-end"></th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($orders as $order)
                                    <tr wire:key="corder-{{ $order->id }}">
                                        <td class="fw-bold tabular">{{ $order->id }}</td>
                                        <td class="text-nowrap tabular text-muted">{{ $order->created_at?->format('d.m.Y H:i') }}</td>
                                        <td>
                                            @include('livewire.admin.orders.partials.order-type', ['type' => $order->type])
                                            @if($order->place) <small class="d-block text-muted">{{ $order->place->name }}</small> @endif
                                            @if($order->delivery_address) <small class="d-block text-muted">{{ $order->delivery_address }}</small> @endif
                                        </td>
                                        <td>@include('livewire.admin.orders.partials.order-status', ['status' => $order->status])</td>
                                        <td class="text-end fw-bold tabular">{{ number_format((int) $order->total_amount, 0, ',', ' ') }}</td>
                                        <td class="text-end text-nowrap">
                                            <button type="button" class="btn btn-inverse-primary btn-sm" wire:click="toggleDetails({{ $order->id }})">
                                                <i class="mdi mdi-{{ $expandedOrderId === $order->id ? 'chevron-up' : 'format-list-bulleted' }}"></i>
                                            </button>
                                            @if($order->status === \App\Casts\OrderStatusEnum::Done)
                                                <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank" class="btn btn-inverse-success btn-sm" title="Chek">
                                                    <i class="mdi mdi-printer"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($expandedOrderId === $order->id)
                                        <tr wire:key="cdetails-{{ $order->id }}">
                                            <td colspan="6" class="p-2">
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
    </div>

    @if($showForm)
        @include('livewire.admin.customers.partials.form-modal', ['title' => 'Mijozni tahrirlash'])
    @endif
</div>
