<div>
    <x-ui.page-header title="Arxiv" subtitle="O'chirilgan buyurtmalar — tiklash yoki butunlay o'chirish">
        <a href="{{ route('orders.index') }}" class="btn btn-secondary" wire:navigate>
            <x-icon name="chevron-left"/>
            Buyurtmalarga
        </a>
    </x-ui.page-header>

    <div class="card mb-4">
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <div class="flex items-end">
                <button type="button" class="btn btn-secondary w-full" wire:click="clearFilters">
                    <x-icon name="refresh"/>
                    Tozalash
                </button>
            </div>
        </div>
    </div>

    <div class="card">
        @if($orders->isEmpty())
            <x-ui.empty icon="archive" title="Arxiv bo'sh"
                        description="O'chirilgan buyurtmalar shu yerda saqlanadi."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Yaratilgan</th>
                        <th>O'chirilgan</th>
                        <th>Joy</th>
                        <th>Turi</th>
                        <th class="text-right">Summa</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $order)
                        <tr wire:key="trashed-{{ $order->id }}">
                            <td class="tabular font-semibold text-ink-900">{{ $order->id }}</td>
                            <td class="tabular whitespace-nowrap text-ink-600">
                                {{ $order->created_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>
                            <td class="tabular whitespace-nowrap text-ink-500">
                                {{ $order->deleted_at?->format('d.m.Y H:i') ?? '—' }}
                            </td>
                            <td>{{ $order->place?->name ?? '—' }}</td>
                            <td>@include('livewire.admin.orders.partials.order-type', ['type' => $order->type])</td>
                            <td class="tabular text-right font-bold text-ink-900">
                                {{ number_format((int) $order->total_amount, 0, ',', ' ') }}
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="toggleDetails({{ $order->id }})">
                                        {{ $expandedOrderId === $order->id ? 'Yopish' : 'Tarkibi' }}
                                    </button>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            wire:click="restore({{ $order->id }})">
                                        <x-icon name="undo"/>
                                        Tiklash
                                    </button>
                                    <x-ui.confirm :action="'forceDelete('.$order->id.')'"
                                                  question="Butunlay o'chirilsinmi?"/>
                                </div>
                            </td>
                        </tr>

                        @if($expandedOrderId === $order->id)
                            <tr wire:key="trashed-details-{{ $order->id }}">
                                <td colspan="7" class="bg-ink-50/40 p-3">
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
