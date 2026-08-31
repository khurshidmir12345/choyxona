@php
    $map = [
        'delivery' => ['Yetkazib berish', 'truck', 'badge-blue'],
        'takeaway' => ['Olib ketish', 'bag', 'badge-amber'],
        'cafe' => ['Zalda', 'store', 'badge-brand'],
    ];
    [$label, $icon, $tone] = $map[$type?->value] ?? ['—', 'info', 'badge-gray'];
@endphp
<span class="badge {{ $tone }}">
    <x-icon :name="$icon" class="h-3.5 w-3.5"/>
    {{ $label }}
</span>
