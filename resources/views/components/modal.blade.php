{{--
    Livewire boshqaradigan modal oyna.
    Bootstrap JS ishlatilmaydi — ko'rinish/yo'qolish Livewire tomonida
    hal qilinadi, shuning uchun holat har doim serverdagi bilan bir xil.
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'size' => '',        // '' | modal-sm | modal-lg | modal-xl
    'close' => null,     // Livewire metodi nomi
])

<div class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true"
     style="background: rgba(30, 40, 61, .55);"
     @if($close) wire:keydown.escape="{{ $close }}" @endif>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable {{ $size }}">
        <div class="modal-content border-0 shadow">
            @if($title)
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title">{{ $title }}</h5>
                        @if($subtitle)
                            <p class="mb-0 text-muted small">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($close)
                        <button type="button" class="btn-close" wire:click="{{ $close }}"
                                aria-label="Yopish"></button>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
