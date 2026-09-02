<div>
    @php $activeFilters = collect([$selectedCategory, $selectedStatus, $dateFrom, $dateTo])->filter(fn ($v) => $v !== '' && $v !== null)->count(); @endphp
    <div x-data="{ filters: @js($activeFilters > 0) }">
    <div class="pos-page-head">
        <div class="pos-head-title">
            <h3>Xarajatlar</h3>
            <p>Kompaniya chiqimlari</p>
        </div>
        <div class="pos-head-tools">
            <div class="stat-strip">
                <div class="stat-mini tone-indigo">
                    <i class="mdi mdi-wallet-outline"></i>
                    <span><small>Jami</small><strong>{{ number_format($totalAmount, 0, ',', ' ') }}</strong></span>
                </div>
                <div class="stat-mini tone-amber">
                    <i class="mdi mdi-clock-outline"></i>
                    <span><small>Kutilmoqda</small><strong>{{ number_format($pendingAmount, 0, ',', ' ') }}</strong></span>
                </div>
                <div class="stat-mini tone-green">
                    <i class="mdi mdi-check-circle-outline"></i>
                    <span><small>Tasdiqlangan</small><strong>{{ number_format($approvedAmount, 0, ',', ' ') }}</strong></span>
                </div>
            </div>
            <div class="head-search">
                <i class="mdi mdi-magnify"></i>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Nomi...">
            </div>
            <button type="button" class="filter-toggle" :class="{ 'is-open': filters }" x-on:click="filters = !filters">
                <i class="mdi mdi-filter-variant"></i> Filtr
                @if($activeFilters) <span class="filter-count">{{ $activeFilters }}</span> @endif
            </button>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('expense-categories.index') }}" class="btn btn-inverse-primary btn-rounded btn-icon-only"
               title="Xarajat kategoriyalari" aria-label="Xarajat kategoriyalari">
                <i class="mdi mdi-folder-outline"></i>
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createExpense">
                <i class="mdi mdi-plus me-1"></i> Xarajat qo'shish
            </button>
        </div>
    </div>

    <div class="filter-panel" x-show="filters" x-cloak x-transition.opacity.duration.150ms>
        <div class="filter-grid">
            <div>
                <label>Kategoriya</label>
                <select wire:model.live="selectedCategory" class="form-select">
                    <option value="">Barchasi</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Holati</label>
                <select wire:model.live="selectedStatus" class="form-select">
                    <option value="">Barchasi</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="approved">Tasdiqlangan</option>
                    <option value="rejected">Rad etilgan</option>
                </select>
            </div>
            <div>
                <label>Sanadan</label>
                <input type="date" wire:model.live="dateFrom" value="{{ $dateFrom }}" class="form-control">
            </div>
            <div>
                <label>Sanagacha</label>
                <input type="date" wire:model.live="dateTo" value="{{ $dateTo }}" class="form-control">
            </div>
            <div class="filter-actions">
                <button type="button" class="btn btn-inverse-secondary" wire:click="clearFilters" title="Filtrni tozalash">
                    <i class="mdi mdi-refresh me-1"></i> Tozalash
                </button>
            </div>
        </div>
    </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($expenses->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-wallet-outline"></i>
                    <h6>Xarajat yo'q</h6>
                    <p>Tanlangan filtr bo'yicha yozuv topilmadi.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th>Nomi</th>
                            <th>Kategoriya</th>
                            <th>Sana</th>
                            <th>To'lov</th>
                            <th class="text-end">Summa</th>
                            <th>Holati</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($expenses as $expense)
                            @php
                                [$statusLabel, $statusTone] = match($expense->status) {
                                    'approved' => ['Tasdiqlangan', 'badge-success'],
                                    'rejected' => ['Rad etilgan', 'badge-danger'],
                                    default => ['Kutilmoqda', 'badge-warning'],
                                };
                            @endphp
                            <tr wire:key="expense-{{ $expense->id }}">
                                <td>
                                    <span class="fw-semibold">{{ $expense->title }}</span>
                                    @if($expense->description)
                                        <small class="d-block text-muted text-truncate" style="max-width:260px">
                                            {{ $expense->description }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($expense->category)
                                        <span class="badge"
                                              style="background: {{ $expense->category->color }}1f; color: {{ $expense->category->color }}; border: 1px solid {{ $expense->category->color }}">
                                            {{ $expense->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-nowrap tabular text-muted">
                                    {{ $expense->expense_date?->format('d.m.Y') ?? '—' }}
                                </td>
                                <td class="text-muted">{{ $expense->payment_method ?? '—' }}</td>
                                <td class="text-end fw-bold tabular">
                                    {{ number_format((float) $expense->amount, 0, ',', ' ') }}
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="badge {{ $statusTone }} border-0" data-bs-toggle="dropdown"
                                                aria-expanded="false" type="button">
                                            {{ $statusLabel }} <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach(['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'] as $value => $label)
                                                <li>
                                                    <button class="dropdown-item" type="button"
                                                            wire:click="updateStatus({{ $expense->id }}, '{{ $value }}')">
                                                        {{ $label }}
                                                    </button>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $expense->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$expense->id.')'"
                                                      title="Xarajat o'chirilsinmi?"/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        <x-modal :title="$expenseId ? 'Xarajatni tahrirlash' : 'Yangi xarajat'" icon="mdi-wallet-outline"
                 subtitle="Summa so'mda" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nomi</label>
                            <input type="text" wire:model="title" autofocus
                                   class="form-control @error('title') is-invalid @enderror"
                                   placeholder="Masalan: Ijara to'lovi">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kategoriya</label>
                            <select wire:model="expense_category_id"
                                    class="form-select @error('expense_category_id') is-invalid @enderror">
                                <option value="">Tanlang...</option>
                                @foreach($this->categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('expense_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Summa</label>
                            <input type="number" wire:model="amount" min="0" step="0.01" inputmode="decimal"
                                   class="form-control tabular @error('amount') is-invalid @enderror" placeholder="0">
                            @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Sana</label>
                            <input type="date" wire:model="expense_date" value="{{ $expense_date }}"
                                   class="form-control @error('expense_date') is-invalid @enderror">
                            @error('expense_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">To'lov turi</label>
                            <select wire:model="payment_method" class="form-select">
                                @foreach(\App\Livewire\Admin\Expenses\IndexLivewire::PAYMENT_METHODS as $method)
                                    <option value="{{ $method }}">{{ $method }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Izoh</label>
                            <textarea wire:model="description" rows="3" class="form-control"
                                      placeholder="Ixtiyoriy"></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Holati</label>
                            <div class="row g-2">
                                @foreach(['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'] as $value => $label)
                                    <div class="col-4">
                                        <button type="button" wire:click="$set('status', '{{ $value }}')"
                                                class="btn w-100 {{ $status === $value ? 'btn-primary' : 'btn-inverse-primary' }}"
                                                style="font-size:.8rem">
                                            {{ $label }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-inverse-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary"><i class="mdi mdi-check me-1"></i> Saqlash</button>
                </div>
            </form>
        </x-modal>
    @endif
</div>
