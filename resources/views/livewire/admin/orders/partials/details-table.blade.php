{{-- Ochilgan buyurtmaning tarkibi. Faqat shu qator uchun yuklanadi. --}}
<div class="p-3 bg-light rounded">
    <h6 class="fw-bold text-muted mb-3" style="font-size:.75rem;letter-spacing:.05em;text-transform:uppercase">
        Buyurtma tarkibi
    </h6>
    <div class="table-responsive bg-white rounded">
        <table class="table table-sm mb-0">
            <thead>
            <tr>
                <th>Mahsulot</th>
                <th class="text-end">Narxi</th>
                <th class="text-center">Soni</th>
                <th class="text-center">Chegirma</th>
                <th class="text-end">Jami</th>
                <th>Xodim</th>
            </tr>
            </thead>
            <tbody>
            @forelse($details as $detail)
                <tr wire:key="detail-{{ $detail->id }}">
                    <td class="fw-semibold">{{ $detail->product?->name ?? 'O\'chirilgan mahsulot' }}</td>
                    <td class="text-end tabular">{{ number_format($detail->price, 0, ',', ' ') }}</td>
                    <td class="text-center tabular">{{ $detail->quantity }}</td>
                    <td class="text-center">
                        @if($detail->discount > 0)
                            <span class="badge badge-danger">-{{ $detail->discount }}%</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold tabular">{{ number_format($detail->total_amount, 0, ',', ' ') }}</td>
                    <td class="text-muted">{{ $detail->worker?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Tarkib topilmadi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
