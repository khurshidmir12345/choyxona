@php
    $map = [
        'delivery' => ['Yetkazib berish', 'mdi-truck-delivery-outline', 'badge-outline-info'],
        'takeaway' => ['Olib ketish', 'mdi-shopping-outline', 'badge-outline-warning'],
        'cafe' => ['Zalda', 'mdi-sofa-outline', 'badge-outline-primary'],
    ];
    [$label, $icon, $tone] = $map[$type?->value] ?? ['—', 'mdi-help-circle-outline', 'badge-outline-secondary'];
@endphp
<span class="badge {{ $tone }}"><i class="mdi {{ $icon }} me-1"></i>{{ $label }}</span>
