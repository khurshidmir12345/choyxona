@php
    // Kompaniya nomi har sahifada kerak — faqat kerakli ustunlar,
    // so'rov davomida bir marta.
    $company = once(fn () => \App\Models\Company::query()
        ->select(['id', 'name', 'logo'])
        ->find(auth()->user()?->companyId()));
@endphp

<nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
    <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <div class="me-3">
            <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-bs-toggle="minimize">
                <span class="icon-menu"></span>
            </button>
        </div>
        <div>
            <a class="navbar-brand brand-logo" href="{{ route('dashboard') }}">
                <div class="text-primary fw-bold fs-5">Choyxona</div>
            </a>
            <a class="navbar-brand brand-logo-mini" href="{{ route('dashboard') }}">
                <i class="mdi mdi-store text-primary" style="font-size: 1.6rem;"></i>
            </a>
        </div>
    </div>

    <div class="navbar-menu-wrapper d-flex align-items-top">
        <ul class="navbar-nav">
            <li class="nav-item fw-semibold d-none d-lg-block ms-0">
                <h1 class="welcome-text">
                    <span class="text-black fw-bold">{{ $company?->name ?? 'Choyxona' }}</span>
                </h1>
                <h3 class="welcome-sub-text">{{ now()->format('d.m.Y') }} — xush kelibsiz!</h3>
            </li>
        </ul>

        <ul class="navbar-nav ms-auto align-items-center">
            {{-- Kassada eng ko'p ishlatiladigan ikkita amal doim ko'rinib tursin --}}
            <li class="nav-item d-none d-md-block me-2">
                <a href="{{ route('cafe.create') }}" class="btn btn-primary btn-rounded btn-sm px-3">
                    <i class="mdi mdi-sofa-outline me-1"></i> Zal
                </a>
            </li>
            <li class="nav-item d-none d-md-block me-3">
                <a href="{{ route('orders.create') }}" class="btn btn-inverse-primary btn-rounded btn-sm px-3">
                    <i class="mdi mdi-cart-outline me-1"></i> Tez sotuv
                </a>
            </li>

            <li class="nav-item dropdown user-dropdown">
                <a class="nav-link d-flex align-items-center" id="UserDropdown" href="#"
                   data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white fw-bold"
                          style="width: 34px; height: 34px;">
                        {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'X', 0, 1)) }}
                    </span>
                    <span class="ms-2 d-none d-lg-inline text-dark fw-semibold">{{ auth()->user()->name }}</span>
                    <i class="mdi mdi-chevron-down ms-1 text-muted"></i>
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
            </li>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                data-bs-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
        </button>
    </div>
</nav>
