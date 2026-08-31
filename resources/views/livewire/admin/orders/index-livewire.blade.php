<div>
    <x-ui.page-header title="Buyurtmalar" subtitle="Barcha savdo tarixi">
        <a href="{{ route('orders.create') }}" class="btn btn-primary" wire:navigate>
            <x-icon name="plus"/>
            Yangi sotuv
        </a>
        <a href="{{ route('orders.deleted') }}" class="btn btn-secondary" wire:navigate>
            <x-icon name="archive"/>
            Arxiv
        </a>
    </x-ui.page-header>

    <div class="mb-4 grid gap-3 sm:grid-cols-2">
        <x-ui.stat label="Topilgan buyurtmalar" :value="number_format($ordersCount, 0, ',', ' ')"
                   icon="receipt" tone="blue"/>
        <x-ui.stat label="Umumiy summa" :value="number_format($revenue, 0, ',', ' ')" suffix="so'm"
                   icon="coins" tone="green"/>
    </div>

    {{-- ------------------------------------------------------------ filtr --}}
    <div class="card mb-4">
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <label class="block">
                <span class="label">Sanadan</span>
                <input type="date" wire:model.live="fromDate" class="input">
            </label>
            <label class="block">
                <span class="label">Sanagacha</span>
                <input type="date" wire:model.live="toDate" class="input">
            </label>
            <label class="block">
                <span class="label">Turi</span>
                <select wire:model.live="type" class="select">
                    <option value="">Barchasi</option>
                    <option value="delivery">Yetkazib berish</option>
                    <option value="takeaway">Olib ketish</option>
                    <option value="cafe">Zalda</option>
                </select>
            </label>
            <label class="block">
                <span class="label">Holati</span>
                <select wire:model.live="status" class="select">
                    <option value="">Barchasi</option>
                    <option value="opened">Ochiq</option>
                    <option value="done">Yopilgan</option>
                    <option value="cancelled">Bekor qilingan</option>
                </select>
            </label>
            <div class="flex items-end">
                <button type="button" class="btn btn-secondary w-full" wire:click="clearFilters">
                    <x-icon name="refresh"/>
                    Tozalash
                </button>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------- ro'yxat --}}
    <div class="card">
        @if($orders->isEmpty())
            <x-ui.empty icon="receipt" title="Buyurtma topilmadi"
                        description="Tanlangan filtr bo'yicha hech narsa yo'q."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Sana</th>
                        <th>Joy</th>
                        <th>Turi</th>
                        <th>Holati</th>
                        <th class="text-right">Summa</th>
                        <th>Kassir</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr wire:key="order-{{ $order->id }}">
                            <td class="tabular font-semibold text-ink-900">{{ $order->id }}</td>
                            <td class="tabular whitespace-nowrap text-ink-600">
                                {{ $order->created_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>
                            <td>{{ $order->place?->name ?? '—' }}</td>
                            <td>@include('livewire.admin.orders.partials.order-type', ['type' => $order->type])</td>
                            <td>@include('livewire.admin.orders.partials.order-status', ['status' => $order->status])</td>
                            <td class="text-right">
                                <span class="tabular font-bold text-ink-900">
                                    {{ number_format((int) $order->total_amount, 0, ',', ' ') }}
                                </span>
                                @if($order->discount)
                                    <span class="block text-xs font-medium text-red-600">-{{ $order->discount }}%</span>
                                @endif
                            </td>
                            <td class="text-ink-600">{{ $order->user?->name ?? '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="toggleDetails({{ $order->id }})">
                                        {{ $expandedOrderId === $order->id ? 'Yopish' : 'Tarkibi' }}
                                    </button>
                                    @if($order->status === \App\Casts\OrderStatusEnum::Done)
                                        <a href="{{ route('admin.orders.print', $order->id) }}" target="_blank"
                                           class="btn btn-sm btn-ghost" title="Chek">
                                            <x-icon name="printer"/>
                                        </a>
                                    @endif
                                    <x-ui.confirm :action="'delete('.$order->id.')'"
                                                  question="Arxivga?"/>
                                </div>
                            </td>
                        </tr>

                        @if($expandedOrderId === $order->id)
                            <tr wire:key="details-{{ $order->id }}">
                                <td colspan="8" class="bg-ink-50/40 p-3">
                                    @include('livewire.admin.orders.partials.details-table', ['details' => $details])
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
