<div>
    {{-- Receipt Header --}}
    <div class="receipt-header">
        <div class="company-name">
            {{ $order->company->name ?? 'Choyxona' }}
        </div>
        <div class="receipt-title">CHEK</div>
        <div class="order-info">
            Buyurtma #{{ $order->id }}<br>
            {{ \Carbon\Carbon::parse($order->getRawOriginal('created_at'))->format('d.m.Y H:i') }}
            @if($order->place)
                <br>{{ $order->place->name }}
            @endif
        </div>
    </div>

    {{-- Order Type and Place Info --}}
    <div style="margin-bottom: 8px; font-size: 9px;">
        @switch($order->type)
            @case(\App\Casts\OrderTypeEnum::Delivery)
                <strong>Yetkazib berish</strong>
                @break
            @case(\App\Casts\OrderTypeEnum::Takeaway)
                <strong>Olib ketish</strong>
                @break
        @endswitch
    </div>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th class="item-name">Mahsulot</th>
                <th class="item-price-qty" style="padding-left: 15px;">Narxi</th>
                <th class="item-price-qty">Soni</th>
                <th class="item-total">Jami</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="item-name">
                        {{ Str::limit($detail->product->name ?? 'Noma\'lum mahsulot', 20) }}
                        @if($detail->discount > 0)
                            <div class="discount">-{{ $detail->discount }}% chegirma</div>
                        @endif
                    </td>
                    <td class="item-price-qty" style="padding-left: 15px;">
                        {{ number_format($detail->price, 0, ',', ' ') }}
                    </td>
                    <td class="item-price-qty">
                        {{ $detail->quantity }}
                    </td>
                    <td class="item-total">
                        {{ number_format($detail->total_amount, 0, ',', ' ') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Total Section --}}
    <div class="total-section">
        {{-- Subtotal --}}
        <div class="total-row">
            <span>Jami:</span>
            <span>{{ number_format($order->amount, 0, ',', ' ') }} uzs</span>
        </div>

        {{-- Discount if any --}}
        @if($order->discount > 0)
            <div class="total-row">
                <span>Chegirma ({{ $order->discount }}%):</span>
                <span class="discount">-{{ number_format($order->amount - $order->total_amount, 0, ',', ' ') }} uzs</span>
            </div>
        @endif

        {{-- Final Total --}}
        <div class="total-row total-amount">
            <span><strong>TO'LOV:</strong></span>
            <span><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} uzs</strong></span>
        </div>
    </div>

    {{-- Receipt Footer --}}
    <div class="receipt-footer">
        <div>❤️❤️❤️ Rahmat! ❤️❤️❤️</div>
        <div>Tashrifingizdan xursandmiz!</div>
        <div style="margin-top: 3px;">
            ⏰ {{ \Carbon\Carbon::parse($order->getRawOriginal('created_at'))->format('d.m.Y H:i:s') }} ⏰
        </div>
        @if($order->user)
            <div style="margin-top: 2px;">
                Kassir: {{ $order->user->name }}
            </div>
        @endif
    </div>
</div> 