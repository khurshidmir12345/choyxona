{{--
    Ikkala sotuv ekrani uchun umumiy tartib: chapda mahsulot to'ri,
    o'ngda savat.

    Kutiladigan o'zgaruvchilar:
      heading, subheading, products, categories, cart, subtotal, total, change,
      discountField, discountValue, backAction, mode ('hall' | 'quick')
--}}
@php
    $mode = $mode ?? 'hall';
    $orderService = app(\App\Services\OrderService::class);
@endphp

<div class="pos-grid">

    {{-- ------------------------------------------------------- mahsulotlar --}}
    <div>
        <div class="pos-page-head">
            <div class="d-flex align-items-center gap-3">
                @if($backAction)
                    <button type="button" class="btn btn-inverse-primary btn-sm rounded-circle"
                            style="width:38px;height:38px;padding:0" wire:click="{{ $backAction }}" title="Orqaga">
                        <i class="mdi mdi-arrow-left"></i>
                    </button>
                @endif
                <div>
                    <h3>{{ $heading }}</h3>
                    <p>{{ $subheading }}</p>
                </div>
            </div>


            @if($mode === 'quick')
                {{-- Buyurtma turi: sarlavha bilan bir qatorda, o'ng tomonda --}}
                <div class="pos-head-actions type-cards" role="radiogroup" aria-label="Buyurtma turi">
                    @foreach(\App\Livewire\Admin\Orders\CreateLivewire::typeMeta() as $value => [$label, $hint, $icon])
                        <button type="button" wire:key="type-{{ $value }}" wire:click="setOrderType('{{ $value }}')"
                                role="radio" aria-checked="{{ $orderType === $value ? 'true' : 'false' }}"
                                class="type-card {{ $orderType === $value ? 'active' : '' }}">
                            <span class="type-icon"><i class="mdi {{ $icon }}"></i></span>
                            <span class="type-text">
                                <strong>{{ $label }}</strong>
                                <small>{{ $hint }}</small>
                            </span>
                            <i class="mdi mdi-check-circle type-check"></i>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
            <input type="search" class="form-control border-start-0 ps-0"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Mahsulot nomi yoki kodi bo'yicha qidirish...">
        </div>

        <div class="pos-categories">
            <button type="button" wire:click="$set('selectedCategory', null)"
                    class="pos-chip {{ $selectedCategory === null ? 'active' : '' }}">
                Barchasi
            </button>
            @foreach($categories as $category)
                <button type="button" wire:key="cat-{{ $category->id }}"
                        wire:click="$set('selectedCategory', {{ $category->id }})"
                        class="pos-chip {{ (int) $selectedCategory === $category->id ? 'active' : '' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        @if($products->isEmpty())
            <div class="card">
                <div class="card-body empty-state">
                    <i class="mdi {{ $biz->term('pos_empty_icon') }}"></i>
                    <h6>Mahsulot topilmadi</h6>
                    <p>Qidiruv so'zini yoki kategoriyani o'zgartirib ko'ring.</p>
                </div>
            </div>
        @else
            <div class="pos-products">
                @foreach($products as $product)
                    @php $inCart = $cart[$product->id]['quantity'] ?? 0; @endphp
                    <button type="button" wire:key="prod-{{ $product->id }}"
                            wire:click="addProduct({{ $product->id }})"
                            class="pos-tile {{ $inCart ? 'in-cart' : '' }}">

                        @if($inCart)
                            <span class="tile-count">{{ $inCart }}</span>
                        @endif

                        <span class="tile-thumb">
                            @if($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="" loading="lazy"
                                     onerror="this.remove()">
                            @else
                                <i class="mdi mdi-image-outline"></i>
                            @endif
                        </span>

                        <p class="tile-name">{{ $product->name }}</p>

                        <p class="tile-price">
                            {{ number_format((int) $product->sell_price, 0, ',', ' ') }}
                            @if($product->discount > 0)
                                <span class="badge badge-danger ms-1" style="font-size:.65rem">
                                    -{{ $product->discount }}%
                                </span>
                            @endif
                        </p>

                        <span class="tile-stock {{ ($product->current_stock ?? 0) > 0 ? '' : 'is-empty' }}">
                            <i class="mdi mdi-package-variant-closed"></i>
                            {{ (int) ($product->current_stock ?? 0) }} dona
                        </span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ------------------------------------------------------------- savat --}}
    <div class="pos-cart-wrap">
        <div class="card pos-cart">
            <div class="cart-head">
                <h6 class="mb-0 fw-bold">
                    <i class="mdi mdi-cart-outline text-primary me-1"></i>
                    Savat
                    <span class="badge badge-outline-primary ms-1">{{ count($cart) }}</span>
                </h6>
                @if(count($cart))
                    <span class="fw-bold tabular">{{ number_format($total, 0, ',', ' ') }} so'm</span>
                @endif
            </div>

            {{-- Mijoz: doimiy xaridorlarga alohida e'tibor --}}
            <div class="cart-customer">
                @include('livewire.admin.orders.partials.customer-picker', [
                    'showDelivery' => $mode === 'quick' && ($orderType ?? null) === 'delivery',
                ])
            </div>

            @if(empty($cart))
                <div class="card-body empty-state">
                    <i class="mdi mdi-cart-off"></i>
                    <h6>Savat bo'sh</h6>
                    <p>Mahsulot ustiga bosing — u shu yerga tushadi.</p>
                </div>
            @else
                <div class="cart-body">
                    @foreach($cart as $line)
                        <div class="cart-line" wire:key="cart-{{ $line['product_id'] }}">
                            <div class="flex-grow-1 min-w-0">
                                <p class="cart-line-name text-truncate">{{ $line['name'] }}</p>
                                <p class="cart-line-price">
                                    {{ number_format($line['price'], 0, ',', ' ') }} so'm
                                    @if($line['discount'] > 0)
                                        <span class="text-danger fw-semibold">-{{ $line['discount'] }}%</span>
                                    @endif
                                </p>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <button type="button" class="btn btn-inverse-primary qty-btn"
                                        wire:click="updateQuantity({{ $line['product_id'] }}, {{ $line['quantity'] - 1 }})">
                                    <i class="mdi mdi-minus"></i>
                                </button>
                                <span class="qty-value">{{ $line['quantity'] }}</span>
                                <button type="button" class="btn btn-inverse-primary qty-btn"
                                        wire:click="updateQuantity({{ $line['product_id'] }}, {{ $line['quantity'] + 1 }})">
                                    <i class="mdi mdi-plus"></i>
                                </button>
                            </div>

                            <div class="text-end flex-shrink-0" style="width: 78px;">
                                <span class="fw-bold tabular d-block">
                                    {{ number_format($orderService->lineTotal($line), 0, ',', ' ') }}
                                </span>
                                <button type="button" class="btn btn-link btn-sm p-0 text-danger"
                                        style="font-size:.72rem"
                                        wire:click="removeProduct({{ $line['product_id'] }})">
                                    o'chirish
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="cart-foot">
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Chegirma %</label>
                            <input type="number" min="0" max="100" inputmode="numeric"
                                   wire:model.live.debounce.500ms="{{ $discountField }}"
                                   class="form-control form-control-sm tabular" placeholder="0"
                                   onfocus="this.select()">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold text-muted mb-1">Berilgan pul</label>
                            <input type="number" min="0" inputmode="numeric"
                                   wire:model.live.debounce.500ms="givenAmount"
                                   class="form-control form-control-sm tabular" placeholder="0"
                                   onfocus="this.select()">
                        </div>
                    </div>

                    <div class="cart-total-row">
                        <span class="text-muted">Oraliq jami</span>
                        <span class="fw-semibold">{{ number_format($subtotal, 0, ',', ' ') }}</span>
                    </div>
                    @if($discountValue > 0)
                        <div class="cart-total-row text-danger">
                            <span>Chegirma {{ $discountValue }}%</span>
                            <span class="fw-semibold">-{{ number_format($subtotal - $total, 0, ',', ' ') }}</span>
                        </div>
                    @endif
                    <div class="cart-total-row grand">
                        <span>To'lov</span>
                        <span class="text-primary">{{ number_format($total, 0, ',', ' ') }}</span>
                    </div>
                    @if((int) $givenAmount > 0)
                        <div class="cart-total-row text-success fw-semibold">
                            <span>Qaytim</span>
                            <span>{{ number_format($change, 0, ',', ' ') }}</span>
                        </div>
                    @endif

                    <div class="d-grid gap-2 mt-3">
                        @if($mode === 'quick')
                            <button type="button" class="btn btn-primary btn-lg" wire:click="saveOrder"
                                    wire:loading.attr="disabled">
                                <i class="mdi mdi-printer me-1"></i> Sotuvni yakunlash
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-lg" wire:click="closeOrder"
                                    wire:loading.attr="disabled">
                                <i class="mdi mdi-printer me-1"></i> Hisobni yopish va chek
                            </button>
                            <button type="button" class="btn btn-inverse-primary" wire:click="saveOrder"
                                    wire:loading.attr="disabled">
                                <i class="mdi mdi-content-save-outline me-1"></i> Saqlab qo'yish
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if($mode === 'hall' && ($activeOrderId ?? null))
                <div class="px-3 py-2 border-top">
                    <x-confirm-button call="clearTable()"
                                      title="Stol bo'shatilsinmi?"
                                      text="Ochiq hisob bekor qilinadi va o'chib ketadi."
                                      confirm-text="Ha, bo'shat"
                                      icon="mdi-broom"
                                      label="Stolni bo'shatish"
                                      class="btn btn-inverse-danger btn-sm w-100"/>
                </div>
            @endif
        </div>
    </div>
</div>
