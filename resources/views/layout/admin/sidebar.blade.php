@php
    use App\Support\Permission as P;

    $current = request()->route()?->getName();
    $is = fn (string ...$names) => in_array($current, $names, true);
    $t = fn (string $key) => $biz->term($key);
    $hall = $biz->hasHall();
    $user = auth()->user();
    $can = fn (string $perm) => $user->allows($perm);

    /*
     * Menyu tuzilishi bitta joyda. "section" — ajratuvchi sarlavha,
     * "children" bo'lsa yig'iladigan bo'lim. Kafe rejimida zal va joylar
     * qo'shiladi, do'kon rejimida so'zlar va ikonkalar universal.
     * Har bir band "perm" bilan belgilangan: xodimga faqat lavozimida
     * yoqilgan bo'limlar ko'rinadi, bo'sh qolgan bo'lim sarlavhasi ham yo'qoladi.
     */
    $menu = [
        ['type' => 'link', 'perm' => P::DASHBOARD, 'label' => 'Bosh sahifa', 'icon' => 'mdi-view-dashboard-outline', 'route' => 'dashboard'],

        ['type' => 'section', 'label' => 'Savdo'],
        $hall ? ['type' => 'link', 'perm' => P::HALL, 'label' => 'Zal (stollar)', 'icon' => 'mdi-sofa-outline', 'route' => 'cafe.create', 'routes' => ['cafe.create', 'admin.orders.place']] : null,
        ['type' => 'link', 'perm' => P::QUICK_SALE, 'label' => $t('quick_sale'), 'icon' => $t('quick_sale_icon'), 'route' => 'orders.create'],
        ['type' => 'group', 'perm' => P::ORDERS, 'label' => $t('orders'), 'icon' => 'mdi-clipboard-text-outline', 'id' => 'menu-orders', 'children' => [
            ['label' => $t('orders_history'), 'icon' => 'mdi-history', 'route' => 'orders.index'],
            ['label' => 'Arxiv', 'icon' => 'mdi-archive-outline', 'route' => 'orders.deleted'],
        ]],
        ['type' => 'link', 'perm' => P::CUSTOMERS, 'label' => 'Mijozlar', 'icon' => 'mdi-account-group-outline', 'route' => 'customers.index', 'routes' => ['customers.index', 'customers.show']],

        ['type' => 'section', 'label' => 'Katalog'],
        ['type' => 'group', 'label' => 'Mahsulotlar', 'icon' => $t('products_icon'), 'id' => 'menu-catalog', 'children' => [
            ['perm' => P::PRODUCTS, 'label' => 'Barcha mahsulotlar', 'icon' => $t('products_all_icon'), 'route' => 'products.index'],
            ['perm' => P::PRODUCTS, 'label' => 'Kategoriyalar', 'icon' => 'mdi-tag-outline', 'route' => 'categories.index'],
            ['perm' => P::STOCK, 'label' => 'Kirim / chiqim', 'icon' => 'mdi-swap-vertical', 'route' => 'product-stock.index'],
        ]],
        $hall ? ['type' => 'link', 'perm' => P::PLACES, 'label' => 'Joylar', 'icon' => 'mdi-table-furniture', 'route' => 'places.index'] : null,

        ['type' => 'section', 'label' => 'Moliya'],
        ['type' => 'group', 'perm' => P::EXPENSES, 'label' => 'Xarajatlar', 'icon' => 'mdi-wallet-outline', 'id' => 'menu-finance', 'children' => [
            ['label' => 'Barcha xarajatlar', 'icon' => 'mdi-cash-minus', 'route' => 'expenses.index'],
            ['label' => 'Kategoriyalar', 'icon' => 'mdi-folder-outline', 'route' => 'expense-categories.index'],
        ]],

        ['type' => 'section', 'label' => 'Jamoa'],
        ['type' => 'group', 'perm' => P::STAFF, 'label' => 'Xodimlar', 'icon' => 'mdi-badge-account-horizontal-outline', 'id' => 'menu-staff', 'children' => [
            ['label' => 'Xodimlar', 'icon' => 'mdi-account-multiple-outline', 'route' => 'employees.index'],
            ['label' => 'Lavozimlar', 'icon' => 'mdi-shield-account-outline', 'route' => 'roles.index'],
        ]],

        ['type' => 'section', 'label' => 'Sozlamalar'],
        ['type' => 'link', 'perm' => P::SETTINGS, 'label' => 'Profil va kompaniya', 'icon' => 'mdi-account-cog-outline', 'route' => 'admin.profile'],
    ];

    // Ruxsat bo'lmagan bandlarni olib tashlash
    $filtered = [];
    foreach (array_filter($menu) as $item) {
        if ($item['type'] === 'section') {
            $filtered[] = $item;
            continue;
        }
        if (isset($item['perm']) && ! $can($item['perm'])) {
            continue;
        }
        if ($item['type'] === 'group') {
            $item['children'] = array_values(array_filter(
                $item['children'],
                fn ($child) => ! isset($child['perm']) || $can($child['perm']),
            ));
            if (! $item['children']) {
                continue;
            }
        }
        $filtered[] = $item;
    }

    // Ostida band qolmagan sarlavhalarni olib tashlash
    $menu = [];
    foreach ($filtered as $i => $item) {
        if ($item['type'] === 'section') {
            $next = $filtered[$i + 1] ?? null;
            if (! $next || $next['type'] === 'section') {
                continue;
            }
        }
        $menu[] = $item;
    }
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
