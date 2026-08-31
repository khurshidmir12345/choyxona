@php
    $nav = [
        [
            'label' => 'Bosh sahifa',
            'icon' => 'dashboard',
            'route' => 'dashboard',
        ],
        [
            'label' => 'Sotuv',
            'icon' => 'cart',
            'section' => true,
            'children' => [
                ['label' => 'Zal (stollar)', 'route' => 'cafe.create'],
                ['label' => 'Tez sotuv', 'route' => 'orders.create'],
                ['label' => 'Buyurtmalar tarixi', 'route' => 'orders.index'],
                ['label' => 'Arxiv', 'route' => 'orders.deleted'],
            ],
        ],
        [
            'label' => 'Katalog',
            'icon' => 'box',
            'section' => true,
            'children' => [
                ['label' => 'Mahsulotlar', 'route' => 'products.index'],
                ['label' => 'Kategoriyalar', 'route' => 'categories.index'],
                ['label' => 'Kirim / chiqim', 'route' => 'product-stock.index'],
                ['label' => 'Joylar', 'route' => 'places.index'],
            ],
        ],
        [
            'label' => 'Moliya',
            'icon' => 'wallet',
            'section' => true,
            'children' => [
                ['label' => 'Xarajatlar', 'route' => 'expenses.index'],
                ['label' => 'Xarajat kategoriyalari', 'route' => 'expense-categories.index'],
            ],
        ],
    ];

    $current = request()->route()?->getName();
@endphp

<aside class="flex h-full flex-col bg-ink-950">
    <div class="flex h-16 shrink-0 items-center gap-2.5 px-5">
        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">
            <x-icon name="store" class="h-5 w-5"/>
        </span>
        <span class="min-w-0">
            <span class="block truncate text-sm font-bold leading-tight text-white">Choyxona</span>
            <span class="block truncate text-[11px] leading-tight text-ink-500">POS tizimi</span>
        </span>
    </div>

    <nav class="flex-1 space-y-0.5 overflow-y-auto px-3 pb-6">
        @foreach($nav as $item)
            @if(empty($item['children']))
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ $current === $item['route'] ? 'nav-link-active' : '' }}"
                   wire:navigate>
                    <x-icon :name="$item['icon']"/>
                    <span>{{ $item['label'] }}</span>
                </a>
            @else
                @php
                    $childRoutes = array_column($item['children'], 'route');
                    $groupOpen = in_array($current, $childRoutes, true);
                @endphp
                <div x-data="{ open: @js($groupOpen) }" class="pt-2">
                    <button type="button" @click="open = !open"
                            class="nav-link w-full {{ $groupOpen ? 'text-white' : '' }}">
                        <x-icon :name="$item['icon']"/>
                        <span class="flex-1 text-left">{{ $item['label'] }}</span>
                        <x-icon name="chevron-down" class="h-4 w-4 transition-transform"
                                x-bind:class="open ? 'rotate-180' : ''"/>
                    </button>
                    <div x-show="open" x-collapse class="mt-0.5 space-y-0.5">
                        @foreach($item['children'] as $child)
                            <a href="{{ route($child['route']) }}"
                               class="nav-sub-link {{ $current === $child['route'] ? 'nav-sub-link-active' : '' }}"
                               wire:navigate>
                                {{ $child['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3">
        <a href="{{ route('admin.profile') }}" class="nav-link {{ $current === 'admin.profile' ? 'nav-link-active' : '' }}"
           wire:navigate>
            <x-icon name="settings"/>
            <span>Sozlamalar</span>
        </a>
    </div>
</aside>
