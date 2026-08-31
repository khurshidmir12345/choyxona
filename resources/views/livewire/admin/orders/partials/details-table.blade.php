{{-- Ochilgan buyurtmaning tarkibi. Faqat shu qator uchun yuklanadi. --}}
<div class="rounded-lg border border-ink-200 bg-ink-50/60 p-3">
    <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-ink-500">Tarkibi</p>
    <div class="table-wrap rounded-lg border border-ink-200 bg-white">
        <table class="table">
            <thead>
            <tr>
                <th>Mahsulot</th>
                <th class="text-right">Narxi</th>
                <th class="text-center">Soni</th>
                <th class="text-center">Chegirma</th>
                <th class="text-right">Jami</th>
                <th>Xodim</th>
            </tr>
            </thead>
            <tbody>
            @forelse($details as $detail)
                <tr wire:key="detail-{{ $detail->id }}">
                    <td class="font-medium text-ink-900">{{ $detail->product?->name ?? 'O\'chirilgan mahsulot' }}</td>
                    <td class="tabular text-right">{{ number_format($detail->price, 0, ',', ' ') }}</td>
                    <td class="tabular text-center">{{ $detail->quantity }}</td>
                    <td class="text-center">
                        @if($detail->discount > 0)
                            <span class="badge badge-red">-{{ $detail->discount }}%</span>
                        @else
                            <span class="text-ink-300">—</span>
                        @endif
                    </td>
                    <td class="tabular text-right font-semibold text-ink-900">
                        {{ number_format($detail->total_amount, 0, ',', ' ') }}
                    </td>
                    <td class="text-ink-500">{{ $detail->worker?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-ink-500">Tarkib topilmadi.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
