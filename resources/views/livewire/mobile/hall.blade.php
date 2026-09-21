@php
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $me = auth()->user();
    $placeIcon = function (string $name): string {
        $lower = mb_strtolower($name);
        return match (true) {
            str_contains($lower, "so'r") || str_contains($lower, 'sori') || str_contains($lower, 'suri') => 'mdi-sofa',
            str_contains($lower, 'xona') || str_contains($lower, 'kabin') || str_contains($lower, 'vip') => 'mdi-door-closed',
            default => 'mdi-table-chair',
        };
    };
@endphp

<div class="m-app">
    <div class="m-progress" wire:loading wire:target="openTable,closeOrder,saveOrder,clearTable,setTab,closePanel,selectCustomer,createCustomer"></div>

    @if(! $this->activePlace)
        {{-- ================================================================ taxta --}}
        @php
            $busy = $this->places->filter->isBusy()->count();
            $free = $this->places->count() - $busy;
            $openAmount = (int) $this->places->sum('open_order_amount');
            $boardPlaces = $this->places->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'st' => $p->isBusy() ? 'busy' : 'free'])->values()->all();
        @endphp

        <div wire:key="board" x-data="mBoard(@js($boardPlaces))">
            <header class="m-top">
                <div class="m-top-row">
                    <div class="m-top-title">
                        <h1>
                            @if($tab === 'places') Stollar
                            @elseif($tab === 'orders') Buyurtmalar
                            @else Profil
                            @endif
                        </h1>
                        <small>
                            @if($tab === 'profile')
                                {{ $me->name }}
                            @else
                                <span class="dot dot-free"></span>{{ $free }} bo'sh
                                <span class="dot dot-busy"></span>{{ $busy }} band
                                @if($openAmount) · {{ $fmt($openAmount) }} so'm ochiq @endif
                            @endif
                        </small>
                    </div>
                    <button type="button" class="m-icon-btn" data-theme-toggle onclick="toggleTheme()" aria-label="Rejim">
                        <i class="mdi mdi-weather-night"></i>
                    </button>
                </div>

                @if($tab === 'places')
                    <div class="m-search">
                        <i class="mdi mdi-magnify"></i>
                        <input type="search" x-model="q" placeholder="Stol yoki xona nomi...">
                        <button type="button" x-show="q" x-cloak x-on:click="q = ''" aria-label="Tozalash"><i class="mdi mdi-close"></i></button>
                    </div>
                    <div class="m-chips">
                        <button type="button" class="m-chip" :class="{ active: f === 'all' }" x-on:click="f = 'all'">
                            Hammasi <b>{{ $this->places->count() }}</b>
                        </button>
                        <button type="button" class="m-chip tone-free" :class="{ active: f === 'free' }" x-on:click="f = 'free'">
                            Bo'sh <b>{{ $free }}</b>
                        </button>
                        <button type="button" class="m-chip tone-busy" :class="{ active: f === 'busy' }" x-on:click="f = 'busy'">
                            Band <b>{{ $busy }}</b>
                        </button>
                    </div>
                @endif
            </header>

            <main class="m-main">
                @if($tab === 'places')
                    @if($lastReceiptId)
                        <a class="m-banner" href="{{ route('admin.orders.print', $lastReceiptId) }}" target="_blank" rel="noopener">
                            <i class="mdi mdi-receipt-text-outline"></i>
                            <span>Oxirgi chek #{{ $lastReceiptId }}</span>
                            <i class="mdi mdi-open-in-new ms-auto"></i>
                        </a>
                    @endif

                    @if($this->places->isEmpty())
                        <div class="m-empty">
                            <i class="mdi mdi-tea-outline"></i>
                            <h6>Joylar qo'shilmagan</h6>
                            <p>Rahbar avval stol yoki xona qo'shishi kerak.</p>
                        </div>
                    @else
                        <div class="m-empty" x-show="visible === 0" x-cloak>
                            <i class="mdi mdi-filter-off-outline"></i>
                            <h6>Mos stol yo'q</h6>
                            <p>Filtrni yoki qidiruvni o'zgartiring.</p>
                        </div>
                        <div class="m-places">
                            @foreach($this->places as $place)
                                @php $isBusy = $place->isBusy(); @endphp
                                <button type="button" wire:key="mp-{{ $place->id }}" wire:click="openTable({{ $place->id }})"
                                        x-show="show({ name: @js(mb_strtolower($place->name)), st: '{{ $isBusy ? 'busy' : 'free' }}' })"
                                        class="m-place {{ $isBusy ? 'is-busy' : 'is-free' }}">
                                    <span class="m-place-head">
                                        <i class="mdi {{ $placeIcon($place->name) }}"></i>
                                        <span class="m-place-status">{{ $isBusy ? 'Band' : "Bo'sh" }}</span>
                                    </span>
                                    <span class="m-place-name">{{ $place->name }}</span>
                                    <span class="m-place-meta">
                                        <i class="mdi mdi-account-multiple-outline"></i> {{ $place->capacity }}
                                        @if($isBusy && $place->open_order_since)
                                            · <i class="mdi mdi-clock-outline"></i> {{ $place->open_order_since->diffForHumans(short: true, syntax: \Carbon\CarbonInterface::DIFF_ABSOLUTE) }}
                                        @endif
                                    </span>
                                    @if($isBusy)
                                        <span class="m-place-amount">{{ $fmt($place->open_order_amount) }} <small>so'm</small></span>
                                    @else
                                        <span class="m-place-cta"><i class="mdi mdi-plus"></i> Buyurtma</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif

                @elseif($tab === 'orders')
                    <h6 class="m-section">Ochiq hisoblar <span>{{ $this->openOrders->count() }}</span></h6>
                    @if($this->openOrders->isEmpty())
                        <div class="m-empty small">
                            <i class="mdi mdi-clipboard-check-outline"></i>
                            <p>Hozir ochiq hisob yo'q.</p>
                        </div>
                    @else
                        <div class="m-list">
                            @foreach($this->openOrders as $order)
                                <button type="button" wire:key="mo-{{ $order->id }}" class="m-row" wire:click="openTable({{ $order->place_id }})">
                                    <span class="m-row-icon busy"><i class="mdi {{ $placeIcon($order->place?->name ?? '') }}"></i></span>
                                    <span class="m-row-body">
                                        <strong>{{ $order->place?->name ?? '—' }} <small>#{{ $order->id }}</small></strong>
                                        <small>
                                            {{ $order->order_details_count }} xil ·
                                            {{ $order->created_at?->diffForHumans(short: true) }}
                                            @if($order->user) · {{ $order->user->name }} @endif
                                            @if($order->customer) · <i class="mdi mdi-account-outline"></i>{{ $order->customer->name }} @endif
                                        </small>
                                    </span>
                                    <span class="m-row-amount">{{ $fmt($order->amount) }}<small>so'm</small></span>
                                    <i class="mdi mdi-chevron-right m-row-arrow"></i>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <h6 class="m-section mt">Bugun yopilgan <span>{{ $this->todayClosed->count() }}</span></h6>
                    @if($this->todayClosed->isEmpty())
                        <div class="m-empty small">
                            <i class="mdi mdi-history"></i>
                            <p>Bugun hali hisob yopilmagan.</p>
                        </div>
                    @else
                        <div class="m-list">
                            @foreach($this->todayClosed as $order)
                                <a wire:key="mc-{{ $order->id }}" class="m-row" href="{{ route('admin.orders.print', $order->id) }}" target="_blank" rel="noopener">
                                    <span class="m-row-icon done"><i class="mdi mdi-check"></i></span>
                                    <span class="m-row-body">
                                        <strong>{{ $order->place?->name ?? '—' }} <small>#{{ $order->id }}</small></strong>
                                        <small>{{ $order->updated_at?->format('H:i') }} @if($order->user) · {{ $order->user->name }} @endif</small>
                                    </span>
                                    <span class="m-row-amount">{{ $fmt($order->total_amount) }}<small>so'm</small></span>
                                    <i class="mdi mdi-receipt-text-outline m-row-arrow"></i>
                                </a>
                            @endforeach
                        </div>
                    @endif

                @else
                    <div class="m-profile">
                        <span class="m-avatar">{{ mb_strtoupper(mb_substr($me->name ?: 'X', 0, 1)) }}</span>
                        <h5>{{ $me->name }}</h5>
                        <p>{{ $me->phone_number }}</p>
                        <span class="m-badge">{{ $me->isOwner() ? 'Ega' : ($me->role?->name ?? 'Xodim') }}</span>
                    </div>

                    <div class="m-list">
                        <div class="m-row static">
                            <span class="m-row-icon"><i class="mdi mdi-store-outline"></i></span>
                            <span class="m-row-body"><strong>{{ $me->company?->name ?? $me->ownedCompany?->name }}</strong><small>Kompaniya</small></span>
                        </div>
                        <button type="button" class="m-row" onclick="toggleTheme()">
                            <span class="m-row-icon"><i class="mdi mdi-theme-light-dark"></i></span>
                            <span class="m-row-body"><strong>Tungi / kunduzgi rejim</strong><small>Ko'rinishni almashtirish</small></span>
                            <i class="mdi mdi-chevron-right m-row-arrow"></i>
                        </button>
                        @if($me->telegram_id)
                            <div class="m-row static">
                                <span class="m-row-icon"><i class="mdi mdi-send"></i></span>
                                <span class="m-row-body"><strong>Telegram ulangan</strong><small>{{ $me->telegram_username ? '@'.$me->telegram_username : 'Parolsiz kirish yoqilgan' }}</small></span>
                            </div>
                        @endif
                        @if($me->allows(\App\Support\Permission::DASHBOARD))
                            <a class="m-row" href="{{ route('dashboard') }}?desktop=1">
                                <span class="m-row-icon"><i class="mdi mdi-monitor"></i></span>
                                <span class="m-row-body"><strong>To'liq panel</strong><small>Kompyuter ko'rinishi</small></span>
                                <i class="mdi mdi-chevron-right m-row-arrow"></i>
                            </a>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="m-logout">
                        @csrf
                        <button type="submit" class="m-btn danger"><i class="mdi mdi-power"></i> Chiqish</button>
                    </form>
                @endif
            </main>

            <nav class="m-tabbar">
                <button type="button" class="{{ $tab === 'places' ? 'active' : '' }}" wire:click="setTab('places')">
                    <i class="mdi mdi-table-chair"></i><span>Stollar</span>
                </button>
                <button type="button" class="{{ $tab === 'orders' ? 'active' : '' }}" wire:click="setTab('orders')">
                    <i class="mdi mdi-clipboard-text-outline"></i><span>Buyurtmalar</span>
                    @if($busy) <em>{{ $busy }}</em> @endif
                </button>
                <button type="button" class="{{ $tab === 'profile' ? 'active' : '' }}" wire:click="setTab('profile')">
                    <i class="mdi mdi-account-circle-outline"></i><span>Profil</span>
                </button>
            </nav>
        </div>

    @else
        {{-- ============================================================ buyurtma --}}
        {{--
            Menyu bir marta JSON bo'lib keladi; savat, filtr, qidiruv va summalar
            brauzerda (mobile-hall.js). cart/discount/givenAmount Livewire bilan
            bog'langan — saqlash/yopishda server bilan birga ketadi.
        --}}
        <div wire:key="order-{{ $placeId }}"
             x-data="mHall({
                products: @js($this->catalog),
                categories: @js($this->categories->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])->values()->all()),
                cart: $wire.entangle('cart'),
                discount: $wire.entangle('discount'),
                given: $wire.entangle('givenAmount'),
             })">
            <header class="m-top">
                <div class="m-top-row">
                    <button type="button" class="m-icon-btn" wire:click="closePanel" aria-label="Orqaga">
                        <i class="mdi mdi-arrow-left"></i>
                    </button>
                    <div class="m-top-title">
                        <h1>{{ $this->activePlace->name }}</h1>
                        <small>{{ $activeOrderId ? 'Ochiq hisob #'.$activeOrderId : 'Yangi buyurtma' }} · {{ $this->activePlace->capacity }} kishilik</small>
                    </div>
                    @if($activeOrderId)
                        <button type="button" class="m-icon-btn danger" aria-label="Stolni bo'shatish"
                                x-on:click="mConfirm('Stol bo\'shatilsinmi?', 'Ochiq hisob bekor qilinadi.', 'Ha, bo\'shat').then(ok => ok && $wire.clearTable())">
                            <i class="mdi mdi-broom"></i>
                        </button>
                    @endif
                </div>

                <div class="m-search">
                    <i class="mdi mdi-magnify"></i>
                    <input type="search" x-model="search" placeholder="Mahsulot nomi yoki kodi...">
                    <button type="button" x-show="search" x-cloak x-on:click="search = ''" aria-label="Tozalash"><i class="mdi mdi-close"></i></button>
                </div>

                <div class="m-chips scroll" wire:ignore>
                    <button type="button" class="m-chip" :class="{ active: category === null && !onlyCart }" x-on:click="setCategory(null)">Barchasi</button>
                    <button type="button" class="m-chip tone-cart" :class="{ active: onlyCart }" x-show="kinds > 0" x-cloak x-on:click="toggleOnlyCart()">
                        <i class="mdi mdi-cart-outline"></i> Savatda <b x-text="kinds"></b>
                    </button>
                    <template x-for="c in categories" :key="c.id">
                        <button type="button" class="m-chip" :class="{ active: category === c.id && !onlyCart }" x-on:click="setCategory(c.id)" x-text="c.name"></button>
                    </template>
                </div>
            </header>

            <main class="m-main has-cartbar" wire:ignore>
                <div class="m-empty" x-show="list.length === 0" x-cloak>
                    <i class="mdi mdi-food-off-outline"></i>
                    <h6>Mahsulot topilmadi</h6>
                    <p>Qidiruv yoki kategoriyani o'zgartiring.</p>
                </div>
                <div class="m-products">
                    <template x-for="p in list" :key="p.id">
                        <div class="m-product" :class="{ 'in-cart': qty(p.id) > 0 }">
                            <button type="button" class="m-product-main" x-on:click="add(p.id)">
                                <span class="m-thumb">
                                    <template x-if="p.image"><img :src="p.image" alt="" loading="lazy" onerror="this.remove()"></template>
                                    <template x-if="!p.image"><i class="mdi mdi-food-outline"></i></template>
                                </span>
                                <span class="m-product-body">
                                    <strong x-text="p.name"></strong>
                                    <small>
                                        <b x-text="fmt(p.price)"></b> so'm
                                        <span class="m-off" x-show="p.discount > 0" x-text="'-' + p.discount + '%'"></span>
                                        <span class="m-stock" :class="{ 'is-empty': p.stock <= 0 }" x-text="'· ' + p.stock + ' dona'"></span>
                                    </small>
                                </span>
                            </button>
                            <div class="m-stepper" x-show="qty(p.id) > 0">
                                <button type="button" x-on:click="dec(p.id)" aria-label="Kamaytirish"><i class="mdi mdi-minus"></i></button>
                                <span x-text="qty(p.id)"></span>
                                <button type="button" x-on:click="inc(p.id)" aria-label="Ko'paytirish"><i class="mdi mdi-plus"></i></button>
                            </div>
                            <button type="button" class="m-add" x-show="qty(p.id) === 0" x-on:click="add(p.id)" aria-label="Qo'shish"><i class="mdi mdi-plus"></i></button>
                        </div>
                    </template>
                </div>
            </main>

            <div class="m-cartbar">
                <template x-if="kinds > 0">
                    <button type="button" class="m-cartbar-info" x-on:click="sheet = 'cart'">
                        <span class="m-cartbar-count" x-text="count"></span>
                        <span class="m-cartbar-text">
                            <strong><span x-text="fmt(total)"></span> so'm</strong>
                            <small><span x-text="kinds"></span> xil · savatni ochish</small>
                        </span>
                        <i class="mdi mdi-chevron-up"></i>
                    </button>
                </template>
                <template x-if="kinds === 0">
                    <div class="m-cartbar-info muted">
                        <i class="mdi mdi-cart-outline"></i>
                        <span class="m-cartbar-text"><strong>Savat bo'sh</strong><small>Mahsulot ustiga bosing</small></span>
                    </div>
                </template>
                <button type="button" class="m-btn primary" x-show="kinds > 0" x-cloak wire:click="saveOrder" wire:loading.attr="disabled">
                    <i class="mdi mdi-content-save-outline"></i> Saqlash
                </button>
            </div>

            {{-- --------------------------------------------------------- savat --}}
            <div x-show="sheet === 'cart' && kinds > 0 && ! @js($showCustomer)" x-cloak>
                <div class="m-sheet-backdrop" x-on:click="sheet = null"></div>
                <section class="m-sheet" role="dialog" aria-modal="true">
                    <div class="m-sheet-handle"></div>
                    <div class="m-sheet-head">
                        <h5><i class="mdi mdi-cart-outline"></i> Savat <span x-text="kinds"></span></h5>
                        <button type="button" class="m-icon-btn" x-on:click="sheet = null" aria-label="Yopish"><i class="mdi mdi-close"></i></button>
                    </div>

                    <div class="m-sheet-body">
                        <div class="m-cart-lines" wire:ignore>
                            <template x-for="l in lines" :key="l.product_id">
                                <div class="m-cart-line">
                                    <span class="m-cart-body">
                                        <strong x-text="l.name"></strong>
                                        <small><span x-text="fmt(l.price)"></span> so'm <span class="m-off" x-show="l.discount > 0" x-text="'-' + l.discount + '%'"></span></small>
                                    </span>
                                    <div class="m-stepper">
                                        <button type="button" x-on:click="dec(l.product_id)"><i class="mdi" :class="Number(l.quantity) <= 1 ? 'mdi-delete-outline' : 'mdi-minus'"></i></button>
                                        <span x-text="l.quantity"></span>
                                        <button type="button" x-on:click="inc(l.product_id)"><i class="mdi mdi-plus"></i></button>
                                    </div>
                                    <span class="m-cart-sum" x-text="fmt(lineTotal(l))"></span>
                                </div>
                            </template>
                        </div>

                        {{-- mijoz --}}
                        <button type="button" class="m-row compact" wire:click="toggleCustomer">
                            <span class="m-row-icon"><i class="mdi mdi-account-outline"></i></span>
                            <span class="m-row-body">
                                @if($this->selectedCustomer)
                                    <strong>{{ $this->selectedCustomer->name }}</strong>
                                    <small>{{ $this->selectedCustomer->phone ?: 'Mijoz' }}</small>
                                @else
                                    <strong>Mijoz</strong>
                                    <small>Ixtiyoriy — doimiy mijozni biriktirish</small>
                                @endif
                            </span>
                            @if($this->selectedCustomer)
                                <span class="m-icon-btn sm" wire:click.stop="clearCustomer" role="button" aria-label="Mijozni olib tashlash"><i class="mdi mdi-close"></i></span>
                            @else
                                <i class="mdi mdi-chevron-right m-row-arrow"></i>
                            @endif
                        </button>

                        <div class="m-fields">
                            <label>
                                <span>Chegirma %</span>
                                <input type="number" min="0" max="100" inputmode="numeric" x-model.number="discount" placeholder="0" onfocus="this.select()">
                            </label>
                            <label>
                                <span>Berilgan pul</span>
                                <input type="number" min="0" inputmode="numeric" x-model.number="given" placeholder="0" onfocus="this.select()">
                            </label>
                        </div>

                        <div class="m-totals">
                            <div><span>Oraliq jami</span><b x-text="fmt(subtotal)"></b></div>
                            <div class="off" x-show="discountAmount > 0"><span>Chegirma <span x-text="discount"></span>%</span><b x-text="'-' + fmt(discountAmount)"></b></div>
                            <div class="grand"><span>To'lov</span><b><span x-text="fmt(total)"></span> so'm</b></div>
                            <div class="change" x-show="Number(given) > 0"><span>Qaytim</span><b x-text="fmt(change)"></b></div>
                        </div>
                    </div>

                    <div class="m-sheet-foot">
                        <button type="button" class="m-btn ghost" wire:click="saveOrder" wire:loading.attr="disabled">
                            <i class="mdi mdi-content-save-outline"></i> Saqlash
                        </button>
                        <button type="button" class="m-btn primary" wire:loading.attr="disabled"
                                x-on:click="mConfirm('Hisob yopilsinmi?', fmt(total) + ' so\'m to\'lov qabul qilinadi.', 'Ha, yopish').then(ok => ok && $wire.closeOrder())">
                            <i class="mdi mdi-cash-check"></i> Hisobni yopish
                        </button>
                    </div>
                </section>
            </div>

            {{-- --------------------------------------------------------- mijoz --}}
            @if($showCustomer)
                <div class="m-sheet-backdrop" wire:click="toggleCustomer"></div>
                <section class="m-sheet" role="dialog" aria-modal="true">
                    <div class="m-sheet-handle"></div>
                    <div class="m-sheet-head">
                        <h5><i class="mdi mdi-account-outline"></i> Mijoz</h5>
                        <button type="button" class="m-icon-btn" wire:click="toggleCustomer" aria-label="Yopish"><i class="mdi mdi-close"></i></button>
                    </div>
                    <div class="m-sheet-body">
                        @if($showCustomerForm)
                            <div class="m-form">
                                <label><span>Ismi</span><input type="text" wire:model="newCustomerName" placeholder="Mijoz ismi" autofocus></label>
                                @error('newCustomerName') <div class="m-error">{{ $message }}</div> @enderror
                                <label><span>Telefon</span><input type="tel" inputmode="numeric" wire:model="newCustomerPhone" placeholder="90 123 45 67"></label>
                                <label><span>Manzil</span><input type="text" wire:model="newCustomerAddress" placeholder="Ixtiyoriy"></label>
                                <div class="m-sheet-foot inline">
                                    <button type="button" class="m-btn ghost" wire:click="cancelNewCustomer">Bekor</button>
                                    <button type="button" class="m-btn primary" wire:click="createCustomer"><i class="mdi mdi-check"></i> Saqlash</button>
                                </div>
                            </div>
                        @else
                            <div class="m-search inset">
                                <i class="mdi mdi-magnify"></i>
                                <input type="search" wire:model.live.debounce.300ms="customerSearch" placeholder="Ism yoki telefon..." autofocus>
                            </div>
                            <div class="m-list">
                                @forelse($this->customerResults as $customer)
                                    <button type="button" wire:key="mcust-{{ $customer->id }}" class="m-row compact {{ $customerId === $customer->id ? 'is-selected' : '' }}"
                                            wire:click="selectCustomer({{ $customer->id }})">
                                        <span class="m-row-icon"><i class="mdi mdi-account-outline"></i></span>
                                        <span class="m-row-body"><strong>{{ $customer->name }}</strong><small>{{ $customer->phone ?: '—' }}</small></span>
                                        @if($customerId === $customer->id)<i class="mdi mdi-check m-row-arrow"></i>@endif
                                    </button>
                                @empty
                                    <div class="m-empty small"><i class="mdi mdi-account-search-outline"></i><p>Mijoz topilmadi.</p></div>
                                @endforelse
                            </div>
                            <button type="button" class="m-btn ghost full" wire:click="startNewCustomer">
                                <i class="mdi mdi-account-plus-outline"></i> Yangi mijoz qo'shish
                            </button>
                        @endif
                    </div>
                </section>
            @endif
        </div>
    @endif
</div>
