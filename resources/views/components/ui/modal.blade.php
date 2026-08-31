@props([
    'title' => null,
    'subtitle' => null,
    'size' => 'md',      // sm | md | lg | xl | full
    'close' => null,     // Livewire metodi nomi, masalan "closeForm"
])

@php
    $width = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl',
        'full' => 'max-w-[96rem]',
    ][$size] ?? 'max-w-lg';
@endphp

<div class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true"
     @if($close) wire:keydown.escape.window="{{ $close }}" @endif>
    <div class="fixed inset-0 bg-ink-950/50 backdrop-blur-[2px] animate-fade-in"
         @if($close) wire:click="{{ $close }}" @endif></div>

    <div class="relative flex min-h-full items-start justify-center p-3 sm:p-6">
        <div class="w-full {{ $width }} animate-slide-up rounded-2xl bg-white shadow-pop">
            @if($title)
                <div class="flex items-start justify-between gap-4 border-b border-ink-200/80 px-5 py-4">
                    <div class="min-w-0">
                        <h2 class="truncate text-base font-semibold text-ink-900">{{ $title }}</h2>
                        @if($subtitle)
                            <p class="mt-0.5 text-sm text-ink-500">{{ $subtitle }}</p>
                        @endif
                    </div>
                    @if($close)
                        <button type="button" class="btn btn-ghost btn-icon -mr-1.5 -mt-1"
                                wire:click="{{ $close }}" aria-label="Yopish">
                            <x-icon name="x" class="h-5 w-5"/>
                        </button>
                    @endif
                </div>
            @endif

            {{ $slot }}

            @isset($footer)
                <div class="flex flex-wrap items-center justify-end gap-2 border-t border-ink-200/80 px-5 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
