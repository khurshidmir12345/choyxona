<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Choyxona POS' }}</title>

    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v={{ filemtime(public_path('css/pos.css')) }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
</head>
<body>
<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-stretch auth px-0 py-0">
            <div class="row w-100 mx-0">

                {{-- Brend paneli --}}
                <div class="col-lg-6 d-none d-lg-block px-0 login-brand-bg">
                    <div class="login-brand-inner">
                        <div class="d-flex align-items-center gap-2">
                            <i class="mdi mdi-store" style="font-size:1.8rem"></i>
                            <span class="fw-bold fs-5">Choyxona POS</span>
                        </div>

                        <div>
                            <h1>Savdo, zaxira va mijozlar — bitta tizimda.</h1>
                            <p class="mt-3">
                                Choyxona, kafe yoki oddiy do'kon: chek chiqaring, zaxira va foydani real vaqtda kuzating.
                            </p>
                            <ul>
                                <li><i class="mdi mdi-check"></i> Kafe uchun zal va ochiq hisoblar</li>
                                <li><i class="mdi mdi-check"></i> Do'kon uchun tez kassa va skaner</li>
                                <li><i class="mdi mdi-check"></i> Zaxira, xarajat va mijozlar nazorati</li>
                            </ul>
                        </div>

                        <small style="opacity:.6">© {{ date('Y') }} Choyxona POS</small>
                    </div>
                </div>

                {{-- Forma --}}
                <div class="col-lg-6 col-12 d-flex align-items-center justify-content-center">
                    <div class="auth-form-light w-100 py-5 px-4 px-sm-5" style="max-width: 460px;">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
</body>
</html>
