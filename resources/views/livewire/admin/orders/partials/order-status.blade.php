@php
    $map = [
        'opened' => ['Ochiq', 'badge-info'],
        'done' => ['Yopilgan', 'badge-success'],
        'closed' => ['Yopilgan', 'badge-secondary'],
        'cancelled' => ['Bekor qilingan', 'badge-danger'],
    ];
    [$label, $tone] = $map[$status?->value] ?? ['—', 'badge-secondary'];
@endphp
<span class="badge {{ $tone }}">{{ $label }}</span>
