<div class="setup-wrap">
    <div class="setup-brand">
        <span class="brand-mark"><i class="mdi mdi-storefront-outline"></i></span>
        <span>POS tizimi</span>
    </div>

    <h1 class="setup-title">Biznesingiz turini tanlang</h1>
    <p class="setup-sub">Shunga qarab menyu, ekranlar va so'zlar moslashadi. Keyin sozlamalardan o'zgartirish mumkin.</p>

    <div class="setup-cards">
        @foreach($types as $type)
            <button type="button" class="setup-card is-{{ $type->value }}" wire:click="choose('{{ $type->value }}')"
                    wire:loading.attr="disabled">
                <span class="setup-icon"><i class="mdi {{ $type->icon() }}"></i></span>
                <span class="setup-card-title">{{ $type->label() }}</span>
                <span class="setup-card-desc">{{ $type->description() }}</span>
                <ul class="setup-list">
                    @foreach($type->features() as $feature)
                        <li><i class="mdi mdi-check"></i> {{ $feature }}</li>
                    @endforeach
                </ul>
                <span class="setup-cta">Tanlash <i class="mdi mdi-arrow-right"></i></span>
            </button>
        @endforeach
    </div>

    <form method="POST" action="{{ route('logout') }}" class="mt-4">
        @csrf
        <button type="submit" class="btn btn-link btn-sm text-muted">Chiqish</button>
    </form>
</div>
