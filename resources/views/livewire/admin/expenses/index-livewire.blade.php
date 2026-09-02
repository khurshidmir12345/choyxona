<div>
    <div class="pos-page-head">
        <div>
            <h3>Xarajatlar</h3>
            <p>Kompaniya chiqimlari</p>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('expense-categories.index') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-folder-outline me-1"></i> Kategoriyalar
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createExpense">
                <i class="mdi mdi-plus me-1"></i> Xarajat qo'shish
            </button>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-4 grid-margin">
            <div class="card stat-card stat-indigo">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label">Jami</p>
                        <p class="stat-value">{{ number_format($totalAmount, 0, ',', ' ') }} <small>so'm</small></p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-wallet-outline"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card stat-card stat-amber">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label">Kutilmoqda</p>
                        <p class="stat-value">{{ number_format($pendingAmount, 0, ',', ' ') }} <small>so'm</small></p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-clock-outline"></i></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 grid-margin">
            <div class="card stat-card stat-green">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <p class="stat-label">Tasdiqlangan</p>
                        <p class="stat-value">{{ number_format($approvedAmount, 0, ',', ' ') }} <small>so'm</small></p>
                    </div>
                    <span class="stat-icon"><i class="mdi mdi-check-circle-outline"></i></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Qidirish</label>
                    <input type="search" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Nomi...">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Kategoriya</label>
                    <select wire:model.live="selectedCategory" class="form-select">
                        <option value="">Barchasi</option>
                        @foreach($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Holati</label>
                    <select wire:model.live="selectedStatus" class="form-select">
                        <option value="">Barchasi</option>
                        <option value="pending">Kutilmoqda</option>
                        <option value="approved">Tasdiqlangan</option>
                        <option value="rejected">Rad etilgan</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Sanadan</label>
                    <input type="date" wire:model.live="dateFrom" value="{{ $dateFrom }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted">Sanagacha</label>
                    <input type="date" wire:model.live="dateTo" value="{{ $dateTo }}" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-inverse-primary w-100" wire:click="clearFilters"
                            title="Filtrni tozalash">
                        <i class="mdi mdi-refresh"></i>
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
