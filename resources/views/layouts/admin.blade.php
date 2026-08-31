<!DOCTYPE html>
<html lang="uz" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#12866f">

    <title>@yield('title', $title ?? 'Choyxona POS')</title>

    <link rel="icon" href="{{ $companyLogo ?? asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="h-full">
<div class="flex h-full" x-data>

    {{-- Yon menyu: katta ekranda doimiy, kichikda chiqib turadi --}}
    <div class="hidden w-60 shrink-0 lg:block">
        @include('layouts.partials.sidebar')
    </div>

    <div x-show="$store.sidebar.open" x-cloak class="fixed inset-0 z-40 lg:hidden">
        <div class="absolute inset-0 bg-ink-950/60" @click="$store.sidebar.open = false"></div>
        <div class="absolute inset-y-0 left-0 w-64" x-show="$store.sidebar.open"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            @include('layouts.partials.sidebar')
        </div>
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
        @include('layouts.partials.topbar')

        <main class="flex-1 overflow-y-auto">
            <div class="{{ $fluid ?? false ? 'p-4 sm:p-5' : 'mx-auto max-w-[90rem] p-4 sm:p-6' }}">
                {{ $slot ?? '' }}
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- Bildirishnomalar --}}
<div class="pointer-events-none fixed bottom-4 right-4 z-[60] flex w-full max-w-sm flex-col gap-2" x-data>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div class="pointer-events-auto flex items-start gap-3 rounded-xl border bg-white p-3.5 shadow-pop animate-slide-up"
             :class="toast.type === 'error' ? 'border-red-200' : 'border-emerald-200'">
            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-white"
                  :class="toast.type === 'error' ? 'bg-red-500' : 'bg-emerald-500'">
                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path x-show="toast.type !== 'error'" d="m5 13 4 4L19 7"/>
                    <path x-show="toast.type === 'error'" d="M18 6 6 18M6 6l12 12"/>
                </svg>
            </span>
            <p class="flex-1 text-sm font-medium text-ink-800" x-text="toast.message"></p>
            <button type="button" class="text-ink-400 hover:text-ink-700"
                    @click="$store.toasts.dismiss(toast.id)" aria-label="Yopish">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>

@livewireScripts
@stack('scripts')
</body>
</html>
