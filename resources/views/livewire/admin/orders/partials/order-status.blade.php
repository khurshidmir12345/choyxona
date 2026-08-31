@php
    $map = [
        'opened' => ['Ochiq', 'badge-blue'],
        'done' => ['Yopilgan', 'badge-green'],
        'closed' => ['Yopilgan', 'badge-gray'],
        'cancelled' => ['Bekor qilingan', 'badge-red'],
    ];
    [$label, $tone] = $map[$status?->value] ?? ['—', 'badge-gray'];
@endphp
<span class="badge {{ $tone }}">{{ $label }}</span>
