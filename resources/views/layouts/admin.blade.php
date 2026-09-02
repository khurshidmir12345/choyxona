<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Tungi/kunduzgi rejim: CSS'dan oldin, yaltirashsiz --}}
    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>


    <title>{{ $title ?? $biz->term('brand') }}</title>

    {{-- Shablon uslublari (BootstrapDash Corona) --}}
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    {{-- Shu loyihaga xos qo'shimchalar (POS ekrani, kartalar) --}}
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v={{ filemtime(public_path('css/pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}">

    <link rel="shortcut icon" href="{{ $companyLogo ?? asset('assets/images/favicon.ico') }}">
    @stack('styles')
    @livewireStyles
</head>
<body class="with-welcome-text">
<div class="container-scroller">

    @include('layout.admin.navbar')

    <div class="container-fluid page-body-wrapper">
        @include('layout.admin.sidebar')

        <div class="main-panel">
            <div class="content-wrapper">
                {{ $slot }}
            </div>

            <footer class="footer">
                <div class="d-sm-flex justify-content-center justify-content-sm-between">
                    <span class="text-muted d-block text-center text-sm-start">
                        {{ $biz->term('brand') }} — savdo va boshqaruv tizimi
                    </span>
                    <span class="float-none float-sm-end d-block mt-1 mt-sm-0 text-center text-muted">
                        © {{ date('Y') }}
                    </span>
                </div>
            </footer>
        </div>
    </div>
</div>

{{-- Shablon skriptlari: jQuery + Bootstrap 5 shu bundle ichida --}}
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
<script src="{{ asset('assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('assets/js/template.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
{{-- Rasm yuklash: brauzerda kichraytirish + Livewire upload (Alpine ishga tushishidan oldin yuklanadi) --}}
<script src="{{ asset('js/image-upload.js') }}?v={{ filemtime(public_path('js/image-upload.js')) }}"></script>

@livewireScripts

<script>
    // Livewire'dan keladigan bildirishnomalar shablon uslubida chiqadi.
    document.addEventListener('livewire:init', () => {
        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });

        Livewire.on('toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            toast.fire({ icon: data.type === 'error' ? 'error' : 'success', title: data.message });
        });

    });
</script>

@stack('scripts')
</body>
</html>
