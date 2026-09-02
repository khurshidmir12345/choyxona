@php
    $current = request()->route()?->getName();
    $is = fn (string ...$names) => in_array($current, $names, true);
    $t = fn (string $key) => $biz->term($key);
    $hall = $biz->hasHall();

    /*
     * Menyu tuzilishi bitta joyda. "section" — ajratuvchi sarlavha,
     * "children" bo'lsa yig'iladigan bo'lim. Kafe rejimida zal va joylar
     * qo'shiladi, do'kon rejimida so'zlar va ikonkalar universal.
     */
    $menu = array_values(array_filter([
        ['type' => 'link', 'label' => 'Bosh sahifa', 'icon' => 'mdi-view-dashboard-outline', 'route' => 'dashboard'],

        ['type' => 'section', 'label' => 'Savdo'],
        $hall ? ['type' => 'link', 'label' => 'Zal (stollar)', 'icon' => 'mdi-sofa-outline', 'route' => 'cafe.create'] : null,
        ['type' => 'link', 'label' => $t('quick_sale'), 'icon' => $t('quick_sale_icon'), 'route' => 'orders.create'],
        ['type' => 'group', 'label' => $t('orders'), 'icon' => 'mdi-clipboard-text-outline', 'id' => 'menu-orders', 'children' => [
            ['label' => $t('orders_history'), 'icon' => 'mdi-history', 'route' => 'orders.index'],
            ['label' => 'Arxiv', 'icon' => 'mdi-archive-outline', 'route' => 'orders.deleted'],
        ]],
        ['type' => 'link', 'label' => 'Mijozlar', 'icon' => 'mdi-account-group-outline', 'route' => 'customers.index', 'routes' => ['customers.index', 'customers.show']],

        ['type' => 'section', 'label' => 'Katalog'],
        ['type' => 'group', 'label' => 'Mahsulotlar', 'icon' => $t('products_icon'), 'id' => 'menu-catalog', 'children' => [
            ['label' => 'Barcha mahsulotlar', 'icon' => $t('products_all_icon'), 'route' => 'products.index'],
            ['label' => 'Kategoriyalar', 'icon' => 'mdi-tag-outline', 'route' => 'categories.index'],
            ['label' => 'Kirim / chiqim', 'icon' => 'mdi-swap-vertical', 'route' => 'product-stock.index'],
        ]],
        $hall ? ['type' => 'link', 'label' => 'Joylar', 'icon' => 'mdi-table-furniture', 'route' => 'places.index'] : null,

        ['type' => 'section', 'label' => 'Moliya'],
        ['type' => 'group', 'label' => 'Xarajatlar', 'icon' => 'mdi-wallet-outline', 'id' => 'menu-finance', 'children' => [
            ['label' => 'Barcha xarajatlar', 'icon' => 'mdi-cash-minus', 'route' => 'expenses.index'],
            ['label' => 'Kategoriyalar', 'icon' => 'mdi-folder-outline', 'route' => 'expense-categories.index'],
        ]],

        ['type' => 'section', 'label' => 'Sozlamalar'],
        ['type' => 'link', 'label' => 'Profil va kompaniya', 'icon' => 'mdi-account-cog-outline', 'route' => 'admin.profile'],
    ]));
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        @foreach($menu as $item)
            @if($item['type'] === 'section')
                <li class="nav-item nav-section"><span>{{ $item['label'] }}</span></li>

            @elseif($item['type'] === 'link')
                <li class="nav-item {{ $is(...($item['routes'] ?? [$item['route']])) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ route($item['route']) }}">
                        <i class="mdi {{ $item['icon'] }} menu-icon"></i>
                        <span class="menu-title">{{ $item['label'] }}</span>
                    </a>
                </li>

            @else
                @php $open = $is(...array_column($item['children'], 'route')); @endphp
                <li class="nav-item {{ $open ? 'active' : '' }}">
                    <a class="nav-link" data-bs-toggle="collapse" href="#{{ $item['id'] }}"
                       aria-expanded="{{ $open ? 'true' : 'false' }}" aria-controls="{{ $item['id'] }}">
                        <i class="mdi {{ $item['icon'] }} menu-icon"></i>
                        <span class="menu-title">{{ $item['label'] }}</span>
                        <i class="menu-arrow"></i>
                    </a>
                    <div class="collapse {{ $open ? 'show' : '' }}" id="{{ $item['id'] }}">
                        <ul class="nav flex-column sub-menu">
                            @foreach($item['children'] as $child)
                                <li class="nav-item">
                                    <a class="nav-link {{ $is($child['route']) ? 'active' : '' }}"
                                       href="{{ route($child['route']) }}">
                                        <i class="mdi {{ $child['icon'] }}"></i>
                                        <span>{{ $child['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endif
        @endforeach
    </ul>
</nav>
