<div>
    @php $activeFilters = collect([$fromDate, $toDate, $type])->filter()->count(); @endphp
    <div x-data="{ filters: @js($activeFilters > 0) }">
    <div class="pos-page-head">
        <div class="pos-head-title">
            <h3>Arxiv</h3>
            <p>O'chirilgan buyurtmalar — tiklash yoki butunlay o'chirish</p>
        </div>
        <div class="pos-head-tools">
            <button type="button" class="filter-toggle" :class="{ 'is-open': filters }" x-on:click="filters = !filters">
                <i class="mdi mdi-filter-variant"></i> Filtr
                @if($activeFilters) <span class="filter-count">{{ $activeFilters }}</span> @endif
            </button>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('orders.index') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-arrow-left me-1"></i> Buyurtmalarga
            </a>
        </div>
    </div>

    <div class="filter-panel" x-show="filters" x-cloak x-transition.opacity.duration.150ms>
        <div class="filter-grid">
            <div>
                <label>Sanadan</label>
                <input type="date" wire:model.live="fromDate" value="{{ $fromDate }}" class="form-control">
            </div>
            <div>
                <label>Sanagacha</label>
                <input type="date" wire:model.live="toDate" value="{{ $toDate }}" class="form-control">
            </div>
            <div>
                <label>Turi</label>
                <select wire:model.live="type" class="form-select">
                    <option value="">Barchasi</option>
                    <option value="delivery">Yetkazib berish</option>
                    <option value="takeaway">Olib ketish</option>
                    <option value="cafe">Zalda</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-inverse-secondary" wire:click="clearFilters">
                    <i class="mdi mdi-refresh me-1"></i> Tozalash
                </button>
            </div>
        </div>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($orders->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-archive-outline"></i>
                    <h6>Arxiv bo'sh</h6>
                    <p>O'chirilgan buyurtmalar shu yerda saqlanadi.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Yaratilgan</th>
                            <th>O'chirilgan</th>
                            <th>Joy</th>
                            <th>Turi</th>
                            <th class="text-end">Summa</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orders as $order)
                            <tr wire:key="trashed-{{ $order->id }}">
                                <td class="fw-bold tabular">{{ $order->id }}</td>
                                <td class="text-nowrap tabular text-muted">
                                    {{ $order->created_at?->format('d.m.Y H:i') ?? '—' }}
                                </td>
                                <td class="text-nowrap tabular text-muted">
                                    {{ $order->deleted_at?->format('d.m.Y H:i') ?? '—' }}
                                </td>
                                <td>{{ $order->place?->name ?? '—' }}</td>
                                <td>@include('livewire.admin.orders.partials.order-type', ['type' => $order->type])</td>
                                <td class="text-end fw-bold tabular">
                                    {{ number_format((int) $order->total_amount, 0, ',', ' ') }}
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="toggleDetails({{ $order->id }})">
                                        <i class="mdi mdi-{{ $expandedOrderId === $order->id ? 'chevron-up' : 'format-list-bulleted' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-inverse-success btn-sm"
                                            wire:click="restore({{ $order->id }})" title="Tiklash">
                                        <i class="mdi mdi-restore"></i>
                                    </button>
                                    <x-confirm-button :call="'forceDelete('.$order->id.')'"
                                                      title="Butunlay o'chirilsinmi?"
                                                      text="Buyurtma va uning tarkibi bazadan butunlay yo'q qilinadi."
                                                      confirm-text="Ha, butunlay o'chir"/>
                                </td>
                            </tr>

                            @if($expandedOrderId === $order->id)
                                <tr wire:key="trashed-details-{{ $order->id }}">
                                    <td colspan="7" class="p-2">
                                        @include('livewire.admin.orders.partials.details-table', ['details' => $details])
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>
