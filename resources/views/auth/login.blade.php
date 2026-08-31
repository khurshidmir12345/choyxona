<x-guest-layout>
    <h1 class="text-2xl font-bold tracking-tight text-ink-900">Xush kelibsiz</h1>
    <p class="mt-1.5 text-sm text-ink-500">Davom etish uchun tizimga kiring.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-4">
        @csrf

        <label class="block">
            <span class="label">Telefon raqam</span>
            <div class="flex rounded-lg shadow-sm">
                <span class="inline-flex items-center rounded-l-lg border border-r-0 border-ink-200 bg-ink-100 px-3 text-sm font-semibold text-ink-500">
                    +998
                </span>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required autofocus
                       inputmode="numeric" autocomplete="tel" placeholder="90 123 45 67"
                       class="input tabular rounded-l-none @error('phone_number') input-error @enderror">
            </div>
            @error('phone_number') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label class="block" x-data="{ show: false }">
            <span class="label">Parol</span>
            <div class="relative">
                <input :type="show ? 'text' : 'password'" name="password" required autocomplete="current-password"
                       class="input pr-11 @error('password') input-error @enderror" placeholder="••••••••">
                <button type="button" @click="show = !show"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1.5 text-ink-400 hover:bg-ink-100"
                        aria-label="Parolni ko'rsatish">
                    <x-icon name="user" class="h-4 w-4" x-show="!show"/>
                    <x-icon name="lock" class="h-4 w-4" x-show="show" x-cloak/>
                </button>
            </div>
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
        </label>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="remember"
                   class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
            <span class="text-sm text-ink-600">Meni eslab qol</span>
        </label>

        <button type="submit" class="btn btn-primary btn-lg w-full">
            Kirish
            <x-icon name="chevron-right"/>
        </button>
    </form>
</x-guest-layout>
