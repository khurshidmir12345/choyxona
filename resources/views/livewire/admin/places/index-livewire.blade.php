<div>
    <div class="pos-page-head">
        <div>
            <h3>Joylar</h3>
            <p>Stol, so'ri va xonalar</p>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('cafe.create') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-sofa-outline me-1"></i> Zalga o'tish
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createPlace">
                <i class="mdi mdi-plus me-1"></i> Joy qo'shish
            </button>
        </div>
    </div>

    @php
        $total = $places->total();
        $busyCount = $places->getCollection()->filter->isBusy()->count();
    @endphp

    <div class="place-toolbar">
        <div class="place-search">
            <i class="mdi mdi-magnify"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Joy nomi...">
        </div>
        @if($total > 0)
            <div class="place-legend">
                <span><i class="dot dot-free"></i> Bo'sh</span>
                <span><i class="dot dot-busy"></i> Band</span>
                <span class="text-muted">Jami {{ $total }} ta</span>
            </div>
        @endif
    </div>

    @if($places->isEmpty())
        <div class="place-empty">
            <i class="mdi mdi-tea-outline"></i>
            <h6>{{ $search ? 'Hech narsa topilmadi' : 'Hali joy yo\'q' }}</h6>
            <p>{{ $search ? 'Boshqa nom bilan qidirib ko\'ring.' : 'Zalda buyurtma qabul qilish uchun stol yoki so\'ri qo\'shing.' }}</p>
            @unless($search)
                <button type="button" class="btn btn-primary btn-sm mt-2" wire:click="createPlace">
                    <i class="mdi mdi-plus me-1"></i> Birinchi joyni qo'shish
                </button>
            @endunless
        </div>
    @else
        <div class="place-grid">
            @foreach($places as $place)
                @php
                    $isBusy = $place->isBusy();
                    $lower = mb_strtolower($place->name);
                    $icon = match (true) {
                        str_contains($lower, "so'r") || str_contains($lower, 'sori') || str_contains($lower, 'suri') => 'mdi-sofa',
                        str_contains($lower, 'xona') || str_contains($lower, 'kabin') || str_contains($lower, 'vip') => 'mdi-door-closed',
                        default => 'mdi-table-chair',
                    };
                @endphp
                <div class="place-card {{ $isBusy ? 'is-busy' : 'is-free' }}" wire:key="place-card-{{ $place->id }}">
                    <span class="place-status">{{ $isBusy ? 'Band' : "Bo'sh" }}</span>

                    <div class="place-actions">
                        <button type="button" class="place-action" wire:click="edit({{ $place->id }})" title="Tahrirlash">
                            <i class="mdi mdi-pencil-outline"></i>
                        </button>
                        <x-confirm-button :call="'delete('.$place->id.')'" title="Joy o'chirilsinmi?"
                                          text="{{ $place->name }} ro'yxatdan olib tashlanadi."
                                          class="place-action is-danger"/>
                    </div>

                    <div class="place-emblem"><i class="mdi {{ $icon }}"></i></div>

                    <p class="place-name" title="{{ $place->name }}">{{ $place->name }}</p>
                    <p class="place-cap">
                        <i class="mdi mdi-account-multiple-outline"></i>
                        {{ $place->capacity }} kishilik
                    </p>

                    @if($isBusy)
                        <a href="{{ route('admin.orders.place', $place->id) }}" class="place-link">
                            Hisobni ochish <i class="mdi mdi-arrow-right"></i>
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        @if($places->hasPages())
            <div class="mt-3">{{ $places->links() }}</div>
        @endif
    @endif

    @if($showForm)
        <x-modal :title="$placeId ? 'Joyni tahrirlash' : 'Yangi joy'" icon="mdi-sofa-outline"
                 subtitle="Stol, so'ri yoki xona" size="modal-sm" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nomi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror" placeholder="Masalan: 1-so'ri">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="form-label">Sig'imi <span class="form-label-note">necha kishilik</span></label>
                        <div class="chip-group mb-2">
                            @foreach(\App\Livewire\Admin\Places\IndexLivewire::CAPACITY_PRESETS as $n)
                                <button type="button" wire:click="$set('capacity', {{ $n }})"
                                        class="chip chip-num {{ (int) $capacity === $n ? 'active' : '' }}">{{ $n }}</button>
                            @endforeach
                        </div>
                        <input type="number" wire:model="capacity" min="1" max="500" inputmode="numeric"
                               class="form-control tabular @error('capacity') is-invalid @enderror"
                               placeholder="Boshqa son">
                        @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
