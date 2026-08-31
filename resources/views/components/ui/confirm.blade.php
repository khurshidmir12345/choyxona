{{--
    Tasdiqlash tugmasi. Alpine bilan ishlaydi: birinchi bosishda savol,
    ikkinchisida amal bajariladi. window.confirm dan farqli — sahifani
    to'xtatib qo'ymaydi va uslubga mos keladi.
--}}
@props(['action', 'label' => 'O\'chirish', 'question' => 'O\'chirilsinmi?', 'icon' => 'trash'])

<span x-data="{ armed: false }" class="inline-flex" @click.outside="armed = false">
    <button type="button" x-show="!armed" @click="armed = true"
            class="btn btn-sm btn-ghost text-ink-500 hover:text-red-600" title="{{ $label }}">
        <x-icon :name="$icon"/>
    </button>
    <span x-show="armed" x-cloak class="inline-flex items-center gap-1.5">
        <span class="text-xs font-medium text-ink-600">{{ $question }}</span>
        <button type="button" class="btn btn-sm btn-danger" wire:click="{{ $action }}" @click="armed = false">
            Ha
        </button>
        <button type="button" class="btn btn-sm btn-secondary" @click="armed = false">Yo'q</button>
    </span>
</span>
