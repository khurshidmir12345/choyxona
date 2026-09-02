<div>
    @if(! $this->activePlace)
        {{-- ------------------------------------------------ stollar taxtasi --}}
        @php
            $busy = $this->places->filter->isBusy()->count();
            $free = $this->places->count() - $busy;
            $openAmount = (int) $this->places->sum('open_order_amount');
        @endphp

        <div class="pos-page-head">
            <div class="pos-head-title">
                <h3>Zal</h3>
                <p>Stolni tanlab buyurtma oching yoki ochiq hisobni davom ettiring</p>
            </div>
            @if($this->places->isNotEmpty())
                <div class="pos-head-tools">
                    <div class="stat-strip">
                        <div class="stat-mini tone-green">
                            <i class="mdi mdi-check-circle-outline"></i>
                            <span><small>Bo'sh</small><strong>{{ $free }}</strong></span>
                        </div>
                        <div class="stat-mini tone-clay">
                            <i class="mdi mdi-account-group-outline"></i>
                            <span><small>Band</small><strong>{{ $busy }}</strong></span>
                        </div>
                        <div class="stat-mini tone-blue">
                            <i class="mdi mdi-cash-multiple"></i>
                            <span><small>Ochiq hisob</small><strong>{{ number_format($openAmount, 0, ',', ' ') }}</strong></span>
                        </div>
                    </div>
                </div>
            @endif
            <div class="pos-head-actions">
                <a href="{{ route('places.index') }}" class="btn btn-inverse-primary btn-rounded">
                    <i class="mdi mdi-cog-outline me-1"></i> Joylarni sozlash
                </a>
            </div>
        </div>

        @if($this->places->isEmpty())
            <div class="place-empty">
                <i class="mdi mdi-tea-outline"></i>
                <h6>Joylar qo'shilmagan</h6>
                <p>Zalda buyurtma qabul qilish uchun avval stol yoki so'ri qo'shing.</p>
                <a href="{{ route('places.index') }}" class="btn btn-primary btn-sm mt-2">
                    <i class="mdi mdi-plus me-1"></i> Joy qo'shish
                </a>
            </div>
        @else
            <div class="place-grid">
                @foreach($this->places as $place)
                    @php
                        $isBusy = $place->isBusy();
                        $lower = mb_strtolower($place->name);
                        $icon = match (true) {
                            str_contains($lower, "so'r") || str_contains($lower, 'sori') || str_contains($lower, 'suri') => 'mdi-sofa',
                            str_contains($lower, 'xona') || str_contains($lower, 'kabin') || str_contains($lower, 'vip') => 'mdi-door-closed',
                            default => 'mdi-table-chair',
                        };
                    @endphp
                    <button type="button" wire:key="place-{{ $place->id }}" wire:click="openTable({{ $place->id }})"
                            class="place-card {{ $isBusy ? 'is-busy' : 'is-free' }}">
                        <span class="place-status">{{ $isBusy ? 'Band' : "Bo'sh" }}</span>
                        <span class="place-emblem"><i class="mdi {{ $icon }}"></i></span>
                        <span class="place-name" title="{{ $place->name }}">{{ $place->name }}</span>
                        <span class="place-cap">
                            <i class="mdi mdi-account-multiple-outline"></i> {{ $place->capacity }} kishilik
                        </span>
                        @if($isBusy)
                            <span class="place-amount">{{ number_format((int) $place->open_order_amount, 0, ',', ' ') }} <small>so'm</small></span>
                            @if($place->open_order_since)
                                <span class="place-since"><i class="mdi mdi-clock-outline"></i> {{ $place->open_order_since->diffForHumans(short: true) }}</span>
                            @endif
                        @endif
                    </button>
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
