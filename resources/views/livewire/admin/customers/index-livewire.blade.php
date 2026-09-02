<div>
    <div class="pos-page-head">
        <div>
            <h3>Mijozlar</h3>
            <p>Doimiy xaridorlar, manzillar va savdo tarixi</p>
        </div>
        <div class="pos-head-actions">
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createCustomer">
                <i class="mdi mdi-account-plus-outline me-1"></i> Mijoz qo'shish
            </button>
        </div>
    </div>

    <div class="place-toolbar">
        <div class="place-search">
            <i class="mdi mdi-magnify"></i>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Ism yoki telefon...">
        </div>
        @if($customers->total() > 0)
            <div class="place-legend"><span class="text-muted">Jami {{ $customers->total() }} ta</span></div>
        @endif
    </div>

    <div class="card">
        <div class="card-body">
            @if($customers->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-account-group-outline"></i>
                    <h6>{{ $search ? 'Mijoz topilmadi' : 'Hali mijoz yo\'q' }}</h6>
                    <p>{{ $search ? 'Boshqa ism yoki raqam bilan qidiring.' : 'Sotuv ekranida yoki shu yerda mijoz qo\'shing.' }}</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Mijoz</th>
                            <th>Telefon</th>
                            <th>Manzil</th>
                            <th class="text-center">Buyurtmalar</th>
                            <th class="text-end">Jami xarid</th>
                            <th>Oxirgi</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($customers as $customer)
                            <tr wire:key="customer-{{ $customer->id }}">
                                <td>
                                    <a href="{{ route('customers.show', $customer->id) }}" class="cust-row">
                                        <span class="cust-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                                        <span class="fw-semibold">{{ $customer->name }}</span>
                                    </a>
                                </td>
                                <td class="tabular text-nowrap">{{ $customer->formattedPhone() ?? '—' }}</td>
                                <td class="text-muted">
                                    <span class="d-inline-block text-truncate" style="max-width: 240px">{{ $customer->address ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-outline-primary tabular">{{ (int) $customer->orders_count }}</span>
                                </td>
                                <td class="text-end fw-bold tabular">{{ number_format((int) $customer->total_spent, 0, ',', ' ') }}</td>
                                <td class="text-muted tabular text-nowrap">
                                    {{ $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at)->format('d.m.Y') : '—' }}
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-inverse-primary btn-sm" title="Tarix">
                                        <i class="mdi mdi-history"></i>
                                    </a>
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $customer->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$customer->id.')'"
                                                      title="Mijoz o'chirilsinmi?"
                                                      text="Buyurtmalar tarixi saqlanib qoladi."/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        @include('livewire.admin.customers.partials.form-modal', ['title' => $customerId ? 'Mijozni tahrirlash' : 'Yangi mijoz'])
    @endif
</div>
