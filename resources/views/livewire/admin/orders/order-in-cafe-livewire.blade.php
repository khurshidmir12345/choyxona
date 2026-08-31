<div>
    @if(! $this->activePlace)
        {{-- ------------------------------------------------ stollar taxtasi --}}
        <div class="pos-page-head">
            <div>
                <h3>Zal</h3>
                <p>Stolni tanlab buyurtma oching yoki ochiq hisobni davom ettiring</p>
            </div>
            <div class="pos-head-actions">
                <a href="{{ route('places.index') }}" class="btn btn-inverse-primary btn-sm">
                    <i class="mdi mdi-cog-outline me-1"></i> Joylarni sozlash
                </a>
            </div>
        </div>

        @if($this->places->isEmpty())
            <div class="card">
                <div class="card-body empty-state">
                    <i class="mdi mdi-table-furniture"></i>
                    <h6>Joylar qo'shilmagan</h6>
                    <p>Zalda buyurtma qabul qilish uchun avval stol yoki so'ri qo'shing.</p>
                    <a href="{{ route('places.index') }}" class="btn btn-primary btn-sm mt-3">
                        <i class="mdi mdi-plus me-1"></i> Joy qo'shish
                    </a>
                </div>
            </div>
        @else
            @php $busy = $this->places->filter->isBusy()->count(); @endphp

            <div class="row mb-4">
                <div class="col-sm-6 col-xl-3 grid-margin grid-margin-sm-0">
                    <div class="card stat-card stat-green">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Bo'sh stollar</p>
                                <p class="stat-value">{{ $this->places->count() - $busy }}</p>
                            </div>
                            <span class="stat-icon"><i class="mdi mdi-check-circle-outline"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 grid-margin grid-margin-sm-0">
                    <div class="card stat-card stat-pink">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Band stollar</p>
                                <p class="stat-value">{{ $busy }}</p>
                            </div>
                            <span class="stat-icon"><i class="mdi mdi-account-group-outline"></i></span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3 grid-margin grid-margin-sm-0">
                    <div class="card stat-card stat-blue">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <p class="stat-label">Ochiq hisoblar</p>
                                <p class="stat-value">
                                    {{ number_format((int) $this->places->sum('open_order_amount'), 0, ',', ' ') }}
                                    <small>so'm</small>
                                </p>
                            </div>
                            <span class="stat-icon"><i class="mdi mdi-cash-multiple"></i></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                @foreach($this->places as $place)
                    @php $isBusy = $place->isBusy(); @endphp
                    <div class="col-6 col-md-4 col-xl-3 col-xxl-2 grid-margin" wire:key="place-{{ $place->id }}">
                        <button type="button" wire:click="openTable({{ $place->id }})"
                                class="table-card {{ $isBusy ? 'is-busy' : 'is-free' }}">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <p class="table-name">{{ $place->name }}</p>
                                    <p class="table-meta">
                                        <i class="mdi mdi-account-multiple-outline"></i>
                                        {{ $place->capacity }} kishilik
                                    </p>
                                </div>
                                <span class="table-dot" style="background: {{ $isBusy ? '#F3797E' : '#4DA761' }}"></span>
                            </div>

                            <div class="mt-3 d-flex align-items-end justify-content-between">
                                @if($isBusy)
                                    <div>
                                        <span class="table-amount">
                                            {{ number_format((int) $place->open_order_amount, 0, ',', ' ') }}
                                        </span>
                                        <span class="d-block table-meta">ochiq hisob</span>
                                    </div>
                                @else
                                    <span class="badge badge-outline-success">Bo'sh</span>
                                @endif
                                <i class="mdi mdi-chevron-right text-muted"></i>
                            </div>

                            @if($isBusy && $place->open_order_since)
                                <p class="table-meta mt-2 mb-0">
                                    <i class="mdi mdi-clock-outline"></i>
                                    {{ $place->open_order_since->diffForHumans(short: true) }}
                                </p>
                            @endif
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        {{-- --------------------------------------------- buyurtma ekrani --}}
        @include('livewire.admin.orders.partials.pos', [
            'heading' => $this->activePlace->name,
            'subheading' => $activeOrderId ? 'Ochiq hisob #'.$activeOrderId : 'Yangi buyurtma',
            'products' => $this->products,
            'categories' => $this->categories,
            'cart' => $cart,
            'subtotal' => $this->subtotal,
            'total' => $this->total,
            'change' => $this->change,
            'discountField' => 'discount',
            'discountValue' => $discount,
            'backAction' => 'closePanel',
        ])
    @endif
</div>
