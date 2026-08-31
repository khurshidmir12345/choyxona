@php
    $current = request()->route()?->getName();

    $isActive = fn (array $names) => in_array($current, $names, true);
@endphp

<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav">
        <li class="nav-item {{ $isActive(['dashboard']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('dashboard') }}">
                <i class="mdi mdi-monitor-dashboard menu-icon"></i>
                <span class="menu-title">Bosh sahifa</span>
            </a>
        </li>

        <li class="nav-item nav-category">Savdo</li>

        <li class="nav-item {{ $isActive(['cafe.create']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('cafe.create') }}">
                <i class="mdi mdi-sofa-outline menu-icon"></i>
                <span class="menu-title">Zal (stollar)</span>
            </a>
        </li>

        <li class="nav-item {{ $isActive(['orders.create']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('orders.create') }}">
                <i class="mdi mdi-cart-outline menu-icon"></i>
                <span class="menu-title">Tez sotuv</span>
            </a>
        </li>

        @php $ordersOpen = $isActive(['orders.index', 'orders.deleted']); @endphp
        <li class="nav-item {{ $ordersOpen ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#menu-orders"
               aria-expanded="{{ $ordersOpen ? 'true' : 'false' }}" aria-controls="menu-orders">
                <i class="menu-icon mdi mdi-clipboard-text-outline"></i>
                <span class="menu-title">Buyurtmalar</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ $ordersOpen ? 'show' : '' }}" id="menu-orders">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['orders.index']) ? 'active' : '' }}"
                           href="{{ route('orders.index') }}">Buyurtmalar tarixi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['orders.deleted']) ? 'active' : '' }}"
                           href="{{ route('orders.deleted') }}">Arxiv</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item nav-category">Katalog</li>

        @php $catalogOpen = $isActive(['products.index', 'categories.index', 'product-stock.index']); @endphp
        <li class="nav-item {{ $catalogOpen ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#menu-catalog"
               aria-expanded="{{ $catalogOpen ? 'true' : 'false' }}" aria-controls="menu-catalog">
                <i class="menu-icon mdi mdi-food"></i>
                <span class="menu-title">Mahsulotlar</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ $catalogOpen ? 'show' : '' }}" id="menu-catalog">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['products.index']) ? 'active' : '' }}"
                           href="{{ route('products.index') }}">Mahsulotlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['categories.index']) ? 'active' : '' }}"
                           href="{{ route('categories.index') }}">Kategoriyalar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['product-stock.index']) ? 'active' : '' }}"
                           href="{{ route('product-stock.index') }}">Kirim / chiqim</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item {{ $isActive(['places.index']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('places.index') }}">
                <i class="mdi mdi-table-furniture menu-icon"></i>
                <span class="menu-title">Joylar</span>
            </a>
        </li>

        <li class="nav-item nav-category">Moliya</li>

        @php $financeOpen = $isActive(['expenses.index', 'expense-categories.index']); @endphp
        <li class="nav-item {{ $financeOpen ? 'active' : '' }}">
            <a class="nav-link" data-bs-toggle="collapse" href="#menu-finance"
               aria-expanded="{{ $financeOpen ? 'true' : 'false' }}" aria-controls="menu-finance">
                <i class="menu-icon mdi mdi-wallet-outline"></i>
                <span class="menu-title">Xarajatlar</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse {{ $financeOpen ? 'show' : '' }}" id="menu-finance">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['expenses.index']) ? 'active' : '' }}"
                           href="{{ route('expenses.index') }}">Xarajatlar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-bold {{ $isActive(['expense-categories.index']) ? 'active' : '' }}"
                           href="{{ route('expense-categories.index') }}">Xarajat kategoriyalari</a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="nav-item nav-category">Sozlamalar</li>

        <li class="nav-item {{ $isActive(['admin.profile']) ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.profile') }}">
                <i class="mdi mdi-account-cog-outline menu-icon"></i>
                <span class="menu-title">Profil va kompaniya</span>
            </a>
        </li>
    </ul>
</nav>
