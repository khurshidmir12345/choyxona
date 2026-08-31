<div class="flex min-h-[calc(100vh-4rem)] flex-col">

    @if(! $this->activePlace)
        {{-- ------------------------------------------------ stollar taxtasi --}}
        <x-ui.page-header title="Zal" subtitle="Stolni tanlab buyurtma oching yoki ochiq hisobni davom ettiring">
            <a href="{{ route('places.index') }}" class="btn btn-secondary btn-sm" wire:navigate>
                <x-icon name="settings"/>
                Joylarni sozlash
            </a>
        </x-ui.page-header>

        @if($this->places->isEmpty())
            <div class="card">
                <x-ui.empty icon="table" title="Joylar qo'shilmagan"
                            description="Zalda buyurtma qabul qilish uchun avval stol yoki so'ri qo'shing.">
                    <a href="{{ route('places.index') }}" class="btn btn-primary" wire:navigate>
                        <x-icon name="plus"/>
                        Joy qo'shish
                    </a>
                </x-ui.empty>
            </div>
        @else
            @php
                $busy = $this->places->filter->isBusy()->count();
            @endphp

            <div class="mb-4 flex flex-wrap gap-2 text-sm">
                <span class="badge badge-green">Bo'sh: {{ $this->places->count() - $busy }}</span>
                <span class="badge badge-red">Band: {{ $busy }}</span>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @foreach($this->places as $place)
                    @php $isBusy = $place->isBusy(); @endphp
                    <button type="button" wire:key="place-{{ $place->id }}"
                            wire:click="openTable({{ $place->id }})"
                            class="group relative flex flex-col rounded-xl border-2 bg-white p-4 text-left shadow-card
                                   transition-all hover:-translate-y-0.5 hover:shadow-pop
                                   {{ $isBusy ? 'border-red-200 bg-red-50/40' : 'border-emerald-200' }}">
                        <span class="absolute right-3 top-3 flex h-2.5 w-2.5 rounded-full
                                     {{ $isBusy ? 'bg-red-500' : 'bg-emerald-500' }}"></span>

                        <span class="text-base font-bold text-ink-900">{{ $place->name }}</span>

                        <span class="mt-1 flex items-center gap-1.5 text-xs text-ink-500">
                            <x-icon name="users" class="h-3.5 w-3.5"/>
                            {{ $place->capacity }} kishilik
                        </span>

                        <span class="mt-4 flex items-end justify-between gap-2">
                            @if($isBusy)
                                <span>
                                    <span class="tabular block text-lg font-bold leading-none text-ink-900">
                                        {{ number_format((int) $place->open_order_amount, 0, ',', ' ') }}
                                    </span>
                                    <span class="block text-[11px] font-medium text-ink-500">so'm — ochiq hisob</span>
                                </span>
                            @else
                                <span class="text-sm font-semibold text-emerald-600">Bo'sh</span>
                            @endif
                            <x-icon name="chevron-right"
                                    class="h-4 w-4 shrink-0 text-ink-300 transition-transform group-hover:translate-x-0.5 group-hover:text-brand-600"/>
                        </span>

                        @if($isBusy && $place->open_order_since)
                            <span class="mt-2 flex items-center gap-1 text-[11px] text-ink-400">
                                <x-icon name="clock" class="h-3 w-3"/>
                                {{ $place->open_order_since->diffForHumans(short: true) }}
                            </span>
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
