<x-guest-layout>
    <div class="d-lg-none d-flex align-items-center gap-2 mb-4 text-primary">
        <i class="mdi mdi-store" style="font-size:1.6rem"></i>
        <span class="fw-bold fs-5">Choyxona POS</span>
    </div>

    <div class="d-flex align-items-start justify-content-between">
        <h4 class="fw-bold mb-1">Xush kelibsiz</h4>
        <button type="button" class="theme-toggle" data-theme-toggle onclick="toggleTheme()" aria-label="Rejimni almashtirish">
            <i class="mdi mdi-weather-night"></i>
        </button>
    </div>
    <h6 class="fw-light text-muted mb-4">Davom etish uchun tizimga kiring.</h6>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="pt-2">
        @csrf

        <div class="form-group mb-3">
            <label class="form-label fw-semibold">Telefon raqam</label>
            <div class="input-group">
                <span class="input-group-text bg-light fw-semibold">+998</span>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required autofocus
                       inputmode="numeric" autocomplete="tel" placeholder="90 123 45 67"
                       class="form-control form-control-lg tabular @error('phone_number') is-invalid @enderror">
            </div>
            @error('phone_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-group mb-3">
            <label class="form-label fw-semibold">Parol</label>
            <div class="input-group">
                <input type="password" name="password" id="password" required autocomplete="current-password"
                       placeholder="••••••••"
                       class="form-control form-control-lg @error('password') is-invalid @enderror">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword"
                        aria-label="Parolni ko'rsatish">
                    <i class="mdi mdi-eye-outline"></i>
                </button>
            </div>
            @error('password') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="form-check mb-4">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label text-muted" for="remember">Meni eslab qol</label>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 auth-form-btn">
            Kirish <i class="mdi mdi-arrow-right ms-1"></i>
        </button>
    </form>

    <script>
        // Parolni ko'rsatish/yashirish
        document.getElementById('togglePassword').addEventListener('click', function () {
            const field = document.getElementById('password');
            const shown = field.type === 'text';
            field.type = shown ? 'password' : 'text';
            this.querySelector('i').className = shown ? 'mdi mdi-eye-outline' : 'mdi mdi-eye-off-outline';
        });
    </script>
</x-guest-layout>
