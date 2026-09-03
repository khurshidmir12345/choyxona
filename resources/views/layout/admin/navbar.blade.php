@php
    $company = once(fn () => \App\Models\Company::query()
        ->select(['id', 'name', 'logo'])
        ->find(auth()->user()?->companyId()));
@endphp

{{--
    Yagona yuqori panel. Ilgari bu yerda 28px li "welcome" sarlavha turardi
    va sahifa sarlavhasi bilan birga ikkita ustma-ust panel taassurotini
    berardi. Endi bu panel faqat xizmat elementlarini saqlaydi, sahifadagi
    sarlavha esa yagona yirik matn bo'lib qoladi.
--}}
<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-stretch flex-row">
    <div class="navbar-brand-wrapper d-flex align-items-center">
        <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="mdi {{ $biz->term('brand_icon') }}"></i></span>
            <span class="brand-text">{{ $biz->term('brand') }}</span>
        </a>
        <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
            <span class="brand-mark"><i class="mdi {{ $biz->term('brand_icon') }}"></i></span>
        </a>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-center">
        <button class="navbar-toggler align-self-center d-none d-lg-inline-flex" type="button"
                data-bs-toggle="minimize" aria-label="Menyuni yig'ish">
            <i class="mdi mdi-menu"></i>
        </button>

        <div class="company-chip d-none d-md-flex">
            @if($company?->logoUrl())
                <img src="{{ $company->logoUrl() }}" alt="" onerror="this.remove()">
            @else
                <span class="company-chip-mark">
                    {{ mb_strtoupper(mb_substr($company?->name ?? 'C', 0, 1)) }}
                </span>
            @endif
            <span class="company-chip-name">{{ $company?->name ?? 'Choyxona' }}</span>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2">
            @if($biz->hasHall())
                <a href="{{ route('cafe.create') }}" class="btn btn-primary btn-rounded btn-sm px-3 d-none d-sm-inline-flex">
                    <i class="mdi mdi-sofa-outline me-1"></i> Zal
                </a>
            @endif
            <a href="{{ route('orders.create') }}"
               class="btn {{ $biz->hasHall() ? 'btn-inverse-primary' : 'btn-primary' }} btn-rounded btn-sm px-3 d-none d-sm-inline-flex">
                <i class="mdi {{ $biz->term('quick_sale_icon') }} me-1"></i> {{ $biz->term('quick_sale') }}
            </a>

            <a href="{{ route('pos.offline') }}" class="net-status" data-net-status>
                <i class="mdi mdi-wifi"></i>
                <span class="net-label d-none d-md-inline" data-net-label>Onlayn</span>
                <span class="net-pending" data-net-pending hidden>0</span>
            </a>

            <button type="button" class="theme-toggle" data-theme-toggle onclick="toggleTheme()" aria-label="Rejimni almashtirish">
                <i class="mdi mdi-weather-night"></i>
            </button>

            <div class="nav-item dropdown user-dropdown list-unstyled mb-0">
                <a class="nav-link d-flex align-items-center gap-2 p-0" id="UserDropdown" href="#"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'X', 0, 1)) }}
                    </span>
                    <span class="d-none d-lg-inline text-dark fw-semibold">{{ auth()->user()->name }}</span>
                    <i class="mdi mdi-chevron-down text-muted"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end navbar-dropdown" aria-labelledby="UserDropdown">
                    <div class="dropdown-header text-center">
                        <p class="mb-1 mt-2 fw-semibold">{{ auth()->user()->name }}</p>
                        <p class="fw-light text-muted mb-0">{{ auth()->user()->phone_number }}</p>
                    </div>
                    <a class="dropdown-item" href="{{ route('admin.profile') }}">
                        <i class="dropdown-item-icon mdi mdi-account-outline text-primary me-2"></i> Profil
                    </a>
                    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="d-none">
                        @csrf
                    </form>
                    <a class="dropdown-item" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="dropdown-item-icon mdi mdi-power text-danger me-2"></i> Chiqish
                    </a>
                </div>
            </div>

            <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas" aria-label="Menyu">
                <i class="mdi mdi-menu"></i>
            </button>
        </div>
    </div>
</nav>
