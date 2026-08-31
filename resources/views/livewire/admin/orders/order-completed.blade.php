<div>
    <div class="center">
        <div class="brand">{{ $company?->name ?? 'Choyxona' }}</div>
        @if($company?->address)
            <div class="small muted">{{ $company->address }}</div>
        @endif
        @if($company?->phone_number)
            <div class="small muted">{{ $company->phone_number }}</div>
        @endif
    </div>

    <hr class="rule">

    <div class="small">
        <div style="display:flex;justify-content:space-between">
            <span class="bold">Chek #{{ $order->id }}</span>
            <span>{{ $order->created_at?->format('d.m.Y H:i') }}</span>
        </div>
        <div style="display:flex;justify-content:space-between">
            <span>
                @switch($order->type?->value)
                    @case('delivery') Yetkazib berish @break
                    @case('takeaway') Olib ketish @break
                    @default Zalda
                @endswitch
                @if($order->place) — {{ $order->place->name }} @endif
            </span>
            <span>{{ $order->user?->name }}</span>
        </div>
    </div>

    <hr class="rule">

    <table>
        <thead>
        <tr>
            <th style="width:46%">Mahsulot</th>
            <th class="right" style="width:20%">Narx</th>
            <th class="right" style="width:12%">Soni</th>
            <th class="right" style="width:22%">Jami</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->orderDetails as $detail)
            <tr>
                <td>
                    {{ \Illuminate\Support\Str::limit($detail->product?->name ?? 'Mahsulot', 22) }}
                    @if($detail->discount > 0)
                        <div class="small muted">chegirma -{{ $detail->discount }}%</div>
                    @endif
                </td>
                <td class="right">{{ number_format($detail->price, 0, ',', ' ') }}</td>
                <td class="right">{{ $detail->quantity }}</td>
                <td class="right bold">{{ number_format($detail->total_amount, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>
            <span>Oraliq jami</span>
            <span>{{ number_format((int) $order->amount, 0, ',', ' ') }}</span>
        </div>
        @if($order->discount > 0)
            <div>
                <span>Chegirma ({{ $order->discount }}%)</span>
                <span>−{{ number_format($order->discountAmount(), 0, ',', ' ') }}</span>
            </div>
        @endif
        <div class="grand">
            <span>TO'LOV</span>
            <span>{{ number_format((int) $order->total_amount, 0, ',', ' ') }} so'm</span>
        </div>
    </div>

    <hr class="rule">

    <div class="center small">
        <div class="bold">Rahmat! Yana tashrif buyuring.</div>
        <div class="muted" style="margin-top:4px">{{ now()->format('d.m.Y H:i:s') }}</div>
    </div>
</div>
