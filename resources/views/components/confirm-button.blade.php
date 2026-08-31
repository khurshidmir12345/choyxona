{{--
    Qaytarib bo'lmaydigan amallar uchun tasdiqlash tugmasi.
    SweetAlert oynasi chiqadi, "ha" bosilsa Livewire metodi chaqiriladi.

    Misol: <x-confirm-button call="delete(5)" title="Mahsulot o'chirilsinmi?"/>
--}}
@props([
    'call',                                   // Livewire metodi, masalan "delete(5)"
    'title' => 'Ishonchingiz komilmi?',
    'text' => 'Bu amalni orqaga qaytarib bo\'lmaydi.',
    'confirmText' => 'Ha, o\'chir',
    'icon' => 'mdi-delete-outline',
    'label' => null,                          // matn kerak bo'lsa
    'class' => 'btn btn-inverse-danger btn-sm',
])

<button type="button" class="{{ $class }}"
        x-on:click="Swal.fire({
            title: @js($title),
            text: @js($text),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#F3797E',
            cancelButtonColor: '#8e94a9',
            confirmButtonText: @js($confirmText),
            cancelButtonText: 'Bekor qilish',
            reverseButtons: true
        }).then((r) => { if (r.isConfirmed) { $wire.{{ $call }} } })"
        title="{{ $label ?? $confirmText }}">
    <i class="mdi {{ $icon }}"></i>{{ $label ? ' '.$label : '' }}
</button>
