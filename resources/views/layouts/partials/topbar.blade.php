@php
    // Sarlavhadagi kompaniya nomi har sahifada kerak, shuning uchun
    // faqat kerakli ustunlar tortiladi va so'rov davomida keshlanadi.
    $company = once(fn () => \App\Models\Company::query()
        ->select(['id', 'name', 'logo'])
        ->find(auth()->user()?->companyId()));
@endphp

<header class="sticky top-0 z-30 flex h-16 shrink-0 items-center gap-3 border-b border-ink-200 bg-white/90 px-4 backdrop-blur sm:px-6">
    <button type="button" class="btn btn-ghost btn-icon lg:hidden" @click="$store.sidebar.open = true"
            aria-label="Menyu">
        <x-icon name="menu"/>
    </button>

    <div class="flex min-w-0 items-center gap-2.5">
        @if($company?->logoUrl())
            <img src="{{ $company->logoUrl() }}" alt="" class="h-8 w-8 rounded-lg object-cover"
                 onerror="this.remove()">
        @endif
        <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-ink-900">{{ $company?->name ?? 'Choyxona' }}</p>
            <p class="truncate text-xs text-ink-500">{{ now()->format('d.m.Y') }}</p>
        </div>
    </div>

    <div class="ml-auto flex items-center gap-2">
        <a href="{{ route('cafe.create') }}" class="btn btn-primary btn-sm hidden sm:inline-flex" wire:navigate>
            <x-icon name="table"/>
            Zal
        </a>
        <a href="{{ route('orders.create') }}" class="btn btn-secondary btn-sm hidden sm:inline-flex" wire:navigate>
            <x-icon name="bag"/>
            Tez sotuv
        </a>

        <div x-data="{ open: false }" class="relative" @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="flex items-center gap-2 rounded-lg py-1.5 pl-1.5 pr-2 hover:bg-ink-100">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name ?? 'X', 0, 1)) }}
                </span>
                <span class="hidden text-sm font-medium text-ink-700 sm:block">{{ auth()->user()->name }}</span>
                <x-icon name="chevron-down" class="h-4 w-4 text-ink-400"/>
            </button>

            <div x-show="open" x-cloak x-transition.origin.top.right
                 class="absolute right-0 mt-1.5 w-56 rounded-xl border border-ink-200 bg-white p-1.5 shadow-pop">
                <div class="border-b border-ink-100 px-3 py-2">
                    <p class="truncate text-sm font-semibold text-ink-900">{{ auth()->user()->name }}</p>
                    <p class="truncate text-xs text-ink-500">{{ auth()->user()->phone_number }}</p>
                </div>
                <a href="{{ route('admin.profile') }}" class="mt-1 flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-ink-700 hover:bg-ink-100" wire:navigate>
                    <x-icon name="user" class="h-4 w-4 text-ink-400"/>
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                        <x-icon name="logout" class="h-4 w-4"/>
                        Chiqish
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
