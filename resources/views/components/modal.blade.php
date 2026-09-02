{{--
    Livewire boshqaradigan modal oyna.
    Bootstrap JS ishlatilmaydi — ko'rinish/yo'qolish Livewire tomonida
    hal qilinadi, shuning uchun holat har doim serverdagi bilan bir xil.

    Barcha modallar ixcham va bir xil ko'rinishda: kichik sarlavha,
    ixtiyoriy ikonka, 40px li maydonlar (uslublar pos.css → .pos-modal).

    Misol:
      <x-modal title="Yangi joy" icon="mdi-sofa" close="closeForm">...</x-modal>
--}}
@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,      // mdi ikonka nomi, masalan mdi-tag-outline
    'size' => '',        // '' | modal-sm | modal-lg | modal-xl
    'close' => null,     // Livewire metodi nomi
])

<div class="modal fade show d-block pos-modal" tabindex="-1" role="dialog" aria-modal="true"
     x-data x-init="$nextTick(() => { const f = $el.querySelector('[autofocus]'); f && f.focus() })"
     @if($close) wire:keydown.escape="{{ $close }}" @endif>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable {{ $size }}">
        <div class="modal-content">
            @if($title)
                <div class="modal-header">
                    @if($icon)
                        <span class="modal-icon"><i class="mdi {{ $icon }}"></i></span>
                    @endif
                    <div class="flex-grow-1 min-w-0">
                        <h5 class="modal-title">{{ $title }}</h5>
                        @if($subtitle)
                            <p class="modal-subtitle">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($close)
                        <button type="button" class="modal-close" wire:click="{{ $close }}" aria-label="Yopish">
                            <i class="mdi mdi-close"></i>
                        </button>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
