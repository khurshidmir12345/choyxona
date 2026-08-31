<!DOCTYPE html>
<html lang="uz" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Choyxona POS' }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-950">
<div class="flex min-h-full">

    {{-- Chapdagi brend paneli (katta ekranda) --}}
    <div class="relative hidden w-1/2 flex-col justify-between overflow-hidden bg-ink-950 p-12 lg:flex">
        <div class="absolute -right-24 -top-24 h-96 w-96 rounded-full bg-brand-600/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full bg-brand-500/10 blur-3xl"></div>

        <div class="relative flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white">
                <x-icon name="store" class="h-5 w-5"/>
            </span>
            <span class="text-lg font-bold text-white">Choyxona POS</span>
        </div>

        <div class="relative max-w-md">
            <h1 class="text-3xl font-bold leading-tight text-white">
                Zal, yetkazib berish va olib ketish — bitta tizimda.
            </h1>
            <p class="mt-4 text-ink-400">
                Stollarni boshqaring, chek chiqaring, zaxira va foydani real vaqtda kuzating.
            </p>

            <ul class="mt-8 space-y-3">
                @foreach(['Stollar bo\'yicha ochiq hisoblar', 'Tez sotuv va chek chiqarish', 'Zaxira va xarajat nazorati'] as $feature)
                    <li class="flex items-center gap-3 text-sm text-ink-300">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600/20 text-brand-300">
                            <x-icon name="check" class="h-3 w-3"/>
                        </span>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="relative text-xs text-ink-600">© {{ date('Y') }} Choyxona POS</p>
    </div>

    {{-- Forma --}}
    <div class="flex w-full items-center justify-center bg-ink-50 p-6 lg:w-1/2">
        <div class="w-full max-w-sm">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-white">
                    <x-icon name="store" class="h-5 w-5"/>
                </span>
                <span class="text-lg font-bold text-ink-900">Choyxona POS</span>
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
</body>
</html>
