<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Tungi/kunduzgi rejim: CSS'dan oldin, yaltirashsiz --}}
    <script src="{{ asset('js/theme.js') }}?v={{ filemtime(public_path('js/theme.js')) }}"></script>

    <title>{{ $title ?? 'Biznes turini tanlang' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pos.css') }}?v={{ filemtime(public_path('css/pos.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}">
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    @livewireStyles
</head>
<body class="setup-body">
{{ $slot }}
<script src="{{ asset('assets/vendors/js/vendor.bundle.base.js') }}"></script>
@livewireScripts
</body>
</html>
