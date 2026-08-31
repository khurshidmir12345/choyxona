@props([
    'label',
    'value',
    'suffix' => null,
    'icon' => null,
    'tone' => 'brand',   // brand | green | red | amber | blue | gray
    'hint' => null,
])

@php
    $tones = [
        'brand' => 'bg-brand-50 text-brand-700',
        'green' => 'bg-emerald-50 text-emerald-700',
        'red'   => 'bg-red-50 text-red-700',
        'amber' => 'bg-amber-50 text-amber-700',
        'blue'  => 'bg-blue-50 text-blue-700',
        'gray'  => 'bg-ink-100 text-ink-600',
    ];
@endphp

<div class="card p-5">
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-semibold uppercase tracking-wide text-ink-500">{{ $label }}</p>
        @if($icon)
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $tones[$tone] ?? $tones['brand'] }}">
                <x-icon :name="$icon" class="h-4 w-4"/>
            </span>
        @endif
    </div>
    <p class="tabular mt-3 text-2xl font-bold leading-none text-ink-900">
        {{ $value }}@if($suffix)<span class="ml-1 text-sm font-semibold text-ink-400">{{ $suffix }}</span>@endif
    </p>
    @if($hint)
        <p class="mt-2 text-xs text-ink-500">{{ $hint }}</p>
    @endif
</div>
