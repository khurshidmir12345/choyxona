<div>
    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
        <button class="btn btn-success btn-rounded mb-2"
                data-bs-toggle="collapse"
                role="button" aria-expanded="false"
                onclick="document.getElementById('export_filter_div').classList.toggle('d-none')"
                aria-controls="collapseExample">
            <i class="fa fa-filter"></i>
            Filter
        </button>
    </div>
    <div class="d-none mb-3" id="export_filter_div" wire:ignore>
        <div class="card card-body border-0  filter-box-shadow">
            <form class="d-block">
                <div class="row gx-2 gy-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" wire:model.live="from_date" class="form-control" placeholder="Sanadan">
                            <button type="button" class="btn btn-outline-warning" wire:click="clear_from_date()" onclick="this.previousElementSibling.value=''">X</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" wire:model.live="to_date" class="form-control" placeholder="Sanagacha">
                            <button type="button" class="btn btn-outline-warning" wire:click="clear_to_date()" onclick="this.previousElementSibling.value='' ">X</button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select wire:model.live="type" class="form-control">
                            <option value="">Barchasi</option>
                            <option value="delivery">Yetkazib berish</option>
                            <option value="takeaway">Olib ketish</option>
                            <option value="cafe">Choyxonada</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>ID</th>
            <th>Yaratuvchi</th>
            <th>Joy</th>
            <th>Summa</th>
            <th>Turi</th>
            <th>Xolati</th>
            <th>Sana</th>
            <th>Amallar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user->name ?? '-' }}</td>

                <td>{{ $order->place->name ?? '-' }}</td>
                <td><b style="font-size: 16px">{{ number_format($order->total_amount, 0, ',', ' ') }}</b>
                    <span class="text-green"> uzs</span>
                    @if ($order->discount)
                        <small class="text-danger d-block">
                            -{{ $order->discount }}% chegirma
                        </small>
                    @endif
                </td>
                <td>
                    @switch($order->type)
                        @case(\App\Casts\OrderTypeEnum::Delivery)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16">
                                <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5zm1.294 7.456A2 2 0 0 1 4.732 11h5.536a2 2 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456M12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2"/>
                            </svg>
                            @break

                        @case(\App\Casts\OrderTypeEnum::Takeaway)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-cart4" viewBox="0 0 16 16">
                                <path d="M0 2.5A.5.5 0 0 1 .5 2H2a.5.5 0 0 1 .485.379L2.89 4H14.5a.5.5 0 0 1 .485.621l-1.5 6A.5.5 0 0 1 13 11H4a.5.5 0 0 1-.485-.379L1.61 3H.5a.5.5 0 0 1-.5-.5M3.14 5l.5 2H5V5zM6 5v2h2V5zm3 0v2h2V5zm3 0v2h1.36l.5-2zm1.11 3H12v2h.61zM11 8H9v2h2zM8 8H6v2h2zM5 8H3.89l.5 2H5zm0 5a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0m9-1a1 1 0 1 0 0 2 1 1 0 0 0 0-2m-2 1a2 2 0 1 1 4 0 2 2 0 0 1-4 0"/>
                            </svg>
                            @break

                        @case(\App\Casts\OrderTypeEnum::Cafe)
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-shop" viewBox="0 0 16 16">
                                <path d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.37 2.37 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0M1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5M4 15h3v-5H4zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1zm3 0h-2v3h2z"/>
                            </svg>
                            @break
                    @endswitch
                </td>
                <td>
                    @switch($order->status)
                        @case(\App\Casts\OrderStatusEnum::opened)
                            <span class="badge bg-primary text-white">Davom etmoqda</span>
                            @break

                        @case(\App\Casts\OrderStatusEnum::Closed)
                            <span class="badge bg-secondary text-white">Yopilgan</span>
                            @break

                        @case(\App\Casts\OrderStatusEnum::Cancelled)
                            <span class="badge bg-danger text-white">Bekor qilingan</span>
                            @break

                        @case(\App\Casts\OrderStatusEnum::Done)
                            <span class="badge bg-success text-white">Tugatilgan</span>
                            @break
                    @endswitch
                </td>
                <td>{{ $order->getCreatedAtAttribute() ?? '-' }}</td>
                <td>
                    <button wire:click="toggleDetails({{ $order->id }})" class="btn btn-sm btn-primary">
                        {{ $expandedOrderId === $order->id ? 'Yopish' : 'Ko‘rish' }}
                    </button>
                </td>
            </tr>

            @if($expandedOrderId === $order->id)
                <tr>
                    <td colspan="8">
                        <strong>Tafsilotlar:</strong>
                        <table class="table table-sm table-striped mt-2">
                            <thead>
                            <tr>
                                <th>Mahsulot nomi</th>
                                <th>Miqdori</th>
                                <th>Narxi</th>
                                <th>Chegirma</th>
                                <th>Xodim</th>
                                <th>Umumiy narxi</th>
                                <th>Sana</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($order->orderDetails as $detail)
                                @php
                                    $total = $detail->price * $detail->quantity;
                                    $discountAmount = $detail->discount ? ($total * $detail->discount / 100) : 0;
                                    $finalTotal = $total - $discountAmount;
                                @endphp
                                <tr>
                                    <td>{{ $detail->product->name ?? '-' }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                    <td><b>{{ number_format($detail->price, 0, ',', ' ') }}</b> uzs</td>
                                    <td>{{ $detail->discount }} %</td>
                                    <td>{{ $detail->worker->name ?? '-' }}</td>
                                    <td>
                                        {{ number_format($finalTotal, 0, ',', ' ') }}
                                        @if ($detail->discount)
                                            <small class="text-danger d-block">
                                                -{{ $detail->discount }}% chegirma
                                            </small>
                                        @endif
                                    </td>
                                    <td>{{ $detail->getCreatedAtAttribute() ?? '-' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    <div class="mt-2">
        {{ $orders->links() }}
    </div>
</div>
