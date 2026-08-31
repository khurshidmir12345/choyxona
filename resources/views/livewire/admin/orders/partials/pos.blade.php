{{--
    Ikkala sotuv ekrani uchun umumiy tartib: chapda mahsulot to'ri,
    o'ngda savat. Ilgari bu ikki joyda ikki xil qilib yozilgan edi.

    Kutiladigan o'zgaruvchilar:
      heading, subheading, products, categories, cart, subtotal, total, change,
      discountField, discountValue, backAction, mode ('hall' | 'quick')
--}}
@php
    $mode = $mode ?? 'hall';
    $orderService = app(\App\Services\OrderService::class);
@endphp

<div class="grid flex-1 grid-cols-1 gap-4 lg:grid-cols-[1fr_22rem] xl:grid-cols-[1fr_24rem]">

    {{-- ------------------------------------------------------- mahsulotlar --}}
    <div class="flex min-w-0 flex-col">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            @if($backAction)
                <button type="button" class="btn btn-secondary btn-icon" wire:click="{{ $backAction }}"
                        aria-label="Orqaga">
                    <x-icon name="chevron-left"/>
                </button>
            @endif
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold text-ink-900">{{ $heading }}</h1>
                <p class="text-sm text-ink-500">{{ $subheading }}</p>
            </div>

            @if($mode === 'quick')
                <div class="ml-auto">
                    <select wire:model.live="orderType" class="select w-44 font-semibold">
                        @foreach(\App\Livewire\Admin\Orders\CreateLivewire::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <div class="mb-3 flex flex-wrap items-center gap-2">
            <label class="relative min-w-[14rem] flex-1">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9"
                       placeholder="Nomi yoki kodi bo'yicha qidirish…">
            </label>
        </div>

        <div class="mb-4 flex gap-2 overflow-x-auto pb-1">
            <button type="button" wire:click="$set('selectedCategory', null)"
                    class="chip {{ $selectedCategory === null ? 'chip-active' : '' }}">
                Barchasi
            </button>
            @foreach($categories as $category)
                <button type="button" wire:key="cat-{{ $category->id }}"
                        wire:click="$set('selectedCategory', {{ $category->id }})"
                        class="chip {{ (int) $selectedCategory === $category->id ? 'chip-active' : '' }}">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>

        @if($products->isEmpty())
            <div class="card flex-1">
                <x-ui.empty icon="search" title="Mahsulot topilmadi"
                            description="Qidiruv so'zini yoki kategoriyani o'zgartirib ko'ring."/>
            </div>
        @else
            <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                @foreach($products as $product)
                    @php $inCart = $cart[$product->id]['quantity'] ?? 0; @endphp
                    <button type="button" wire:key="prod-{{ $product->id }}"
                            wire:click="addProduct({{ $product->id }})"
                            class="pos-tile relative {{ $inCart ? 'border-brand-400 ring-1 ring-brand-400' : '' }}">

                        @if($inCart)
                            <span class="tabular absolute -right-1.5 -top-1.5 z-10 flex h-6 min-w-6 items-center justify-center
                                         rounded-full bg-brand-600 px-1.5 text-xs font-bold text-white shadow">
                                {{ $inCart }}
                            </span>
                        @endif

                        <span class="mb-2 flex aspect-[4/3] w-full items-center justify-center overflow-hidden rounded-lg bg-ink-100">
                            @if($product->imageUrl())
                                <img src="{{ $product->imageUrl() }}" alt="" loading="lazy"
                                     class="h-full w-full object-cover" onerror="this.remove()">
                            @else
                                <x-icon name="image" class="h-7 w-7 text-ink-300"/>
                            @endif
                        </span>

                        <span class="line-clamp-2 text-sm font-semibold leading-snug text-ink-900">
                            {{ $product->name }}
                        </span>

                        <span class="mt-2 flex items-end justify-between gap-1">
                            <span>
                                <span class="tabular block text-sm font-bold text-brand-700">
                                    {{ number_format((int) $product->sell_price, 0, ',', ' ') }}
                                </span>
                                @if($product->discount > 0)
                                    <span class="badge badge-red mt-1">-{{ $product->discount }}%</span>
                                @endif
                            </span>
                            <span class="tabular text-[11px] font-medium
                                         {{ ($product->current_stock ?? 0) > 0 ? 'text-ink-400' : 'text-red-500' }}">
                                {{ (int) ($product->current_stock ?? 0) }} dona
                            </span>
                        </span>
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ------------------------------------------------------------- savat --}}
    <div class="order-first lg:order-none lg:sticky lg:top-20 lg:self-start">
        <div class="card flex max-h-[calc(100vh-6rem)] flex-col">
            <div class="card-head">
                <h2 class="card-title flex items-center gap-2">
                    <x-icon name="cart" class="h-4 w-4 text-ink-400"/>
                    Savat
                    <span class="badge badge-gray">{{ count($cart) }}</span>
                </h2>
                @if(count($cart))
                    <span class="tabular text-sm font-bold text-ink-900">
                        {{ number_format($total, 0, ',', ' ') }} so'm
                    </span>
                @endif
            </div>

            @if(empty($cart))
                <x-ui.empty icon="cart" title="Savat bo'sh"
                            description="Mahsulot ustiga bosing — u shu yerga tushadi."/>
            @else
                <div class="min-h-0 flex-1 divide-y divide-ink-100 overflow-y-auto">
                    @foreach($cart as $line)
                        <div class="flex items-start gap-2 px-4 py-3" wire:key="cart-{{ $line['product_id'] }}">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-ink-900">{{ $line['name'] }}</p>
                                <p class="tabular mt-0.5 text-xs text-ink-500">
                                    {{ number_format($line['price'], 0, ',', ' ') }} so'm
                                    @if($line['discount'] > 0)
                                        <span class="ml-1 font-semibold text-red-600">-{{ $line['discount'] }}%</span>
                                    @endif
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-1">
                                <button type="button" class="btn btn-sm btn-secondary h-7 w-7 p-0"
                                        wire:click="updateQuantity({{ $line['product_id'] }}, {{ $line['quantity'] - 1 }})"
                                        aria-label="Kamaytirish">
                                    <x-icon name="minus"/>
                                </button>
                                <span class="tabular w-7 text-center text-sm font-bold">{{ $line['quantity'] }}</span>
                                <button type="button" class="btn btn-sm btn-secondary h-7 w-7 p-0"
                                        wire:click="updateQuantity({{ $line['product_id'] }}, {{ $line['quantity'] + 1 }})"
                                        aria-label="Ko'paytirish">
                                    <x-icon name="plus"/>
                                </button>
                            </div>

                            <div class="w-20 shrink-0 text-right">
                                <span class="tabular text-sm font-bold text-ink-900">
                                    {{ number_format($orderService->lineTotal($line), 0, ',', ' ') }}
                                </span>
                                <button type="button" class="mt-0.5 block w-full text-right text-[11px] font-medium text-ink-400 hover:text-red-600"
                                        wire:click="removeProduct({{ $line['product_id'] }})">
                                    o'chirish
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="shrink-0 space-y-3 border-t border-ink-200/80 bg-ink-50/60 p-4">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block">
                            <span class="label">Chegirma %</span>
                            <input type="number" min="0" max="100" inputmode="numeric"
                                   wire:model.live.debounce.400ms="{{ $discountField }}"
                                   class="input tabular py-1.5" placeholder="0" onfocus="this.select()">
                        </label>
                        <label class="block">
                            <span class="label">Berilgan pul</span>
                            <input type="number" min="0" inputmode="numeric" wire:model.live.debounce.400ms="givenAmount"
                                   class="input tabular py-1.5" placeholder="0" onfocus="this.select()">
                        </label>
                    </div>

                    <dl class="tabular space-y-1.5 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-ink-500">Oraliq jami</dt>
                            <dd class="font-semibold text-ink-800">{{ number_format($subtotal, 0, ',', ' ') }}</dd>
                        </div>
                        @if($discountValue > 0)
                            <div class="flex justify-between text-red-600">
                                <dt>Chegirma {{ $discountValue }}%</dt>
                                <dd class="font-semibold">-{{ number_format($subtotal - $total, 0, ',', ' ') }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between border-t border-ink-200 pt-2 text-base">
                            <dt class="font-bold text-ink-900">To'lov</dt>
                            <dd class="font-bold text-brand-700">{{ number_format($total, 0, ',', ' ') }}</dd>
                        </div>
                        @if((int) $givenAmount > 0)
                            <div class="flex justify-between text-emerald-700">
                                <dt class="font-semibold">Qaytim</dt>
                                <dd class="font-bold">{{ number_format($change, 0, ',', ' ') }}</dd>
                            </div>
                        @endif
                    </dl>

                    <div class="space-y-2">
                        @if($mode === 'quick')
                            <button type="button" class="btn btn-primary btn-lg w-full" wire:click="saveOrder"
                                    wire:loading.attr="disabled">
                                <x-icon name="printer"/>
                                Sotuvni yakunlash
                            </button>
                        @else
                            <button type="button" class="btn btn-primary btn-lg w-full" wire:click="closeOrder"
                                    wire:loading.attr="disabled">
                                <x-icon name="printer"/>
                                Hisobni yopish va chek
                            </button>
                            <button type="button" class="btn btn-secondary w-full" wire:click="saveOrder"
                                    wire:loading.attr="disabled">
                                <x-icon name="check"/>
                                Saqlab qo'yish
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            @if($mode === 'hall' && ($activeOrderId ?? null))
                <div class="shrink-0 border-t border-ink-200/80 p-3">
                    <div x-data="{ armed: false }" @click.outside="armed = false">
                        <button type="button" x-show="!armed" @click="armed = true"
                                class="btn btn-ghost btn-sm w-full text-ink-500 hover:text-red-600">
                            <x-icon name="trash"/>
                            Stolni bo'shatish
                        </button>
                        <div x-show="armed" x-cloak class="space-y-2">
                            <p class="text-xs text-ink-600">Ochiq hisob bekor qilinadi. Davom etilsinmi?</p>
                            <div class="flex gap-2">
                                <button type="button" class="btn btn-danger btn-sm flex-1" wire:click="clearTable"
                                        @click="armed = false">Ha, bo'shat</button>
                                <button type="button" class="btn btn-secondary btn-sm flex-1"
                                        @click="armed = false">Bekor</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
