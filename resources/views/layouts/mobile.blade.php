<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1F3BB3">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    {{-- Tungi/kunduzgi rejim: CSS'dan oldin, yaltirashsiz --}}
    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>
    @if(session('telegram_app'))
        <script src="https://telegram.org/js/telegram-web-app.js?59"></script>
        <script src="{{ asset('js/telegram.js') }}?v={{ filemtime(public_path('js/telegram.js')) }}"></script>
    @endif

    <title>{{ $title ?? 'Zal' }} — {{ $biz->term('brand') }}</title>

    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile.css') }}?v={{ filemtime(public_path('css/mobile.css')) }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    @livewireStyles
</head>
<body class="m-body-root">
{{ $slot }}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@livewireScripts
<script>
    document.addEventListener('livewire:init', () => {
        const toast = Swal.mixin({
            toast: true,
            position: 'top',
            showConfirmButton: false,
            timer: 2200,
            timerProgressBar: true,
            customClass: { popup: 'm-toast' },
        });

        Livewire.on('toast', (event) => {
            const data = Array.isArray(event) ? event[0] : event;
            toast.fire({ icon: data.type === 'error' ? 'error' : (data.type === 'warning' ? 'warning' : 'success'), title: data.message });
        });

        @if(session('toast'))
            toast.fire({ icon: @js(session('toast.type') === 'error' ? 'error' : 'success'), title: @js(session('toast.message')) });
        @endif
    });

    // Tasdiqlash oynasi (Alpine ichidan: $confirm('...', () => $wire.clearTable()))
    window.mConfirm = function (title, text, confirmText) {
        return Swal.fire({
            title, text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F3797E',
            cancelButtonColor: '#8e94a9',
            confirmButtonText: confirmText || 'Ha',
            cancelButtonText: 'Bekor qilish',
            reverseButtons: true,
            customClass: { popup: 'm-swal' },
        }).then((r) => r.isConfirmed);
    };
</script>
</body>
</html>
