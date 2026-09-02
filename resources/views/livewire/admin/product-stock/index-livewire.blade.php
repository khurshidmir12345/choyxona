<div>
    <div class="pos-page-head">
        <div>
            <h3>Kirim / chiqim</h3>
            <p>Zaxira harakatlari jurnali</p>
        </div>
        <div class="pos-head-actions">
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createMovement">
                <i class="mdi mdi-plus me-1"></i> Harakat qo'shish
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-muted">Qidirish</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="mdi mdi-magnify text-muted"></i>
                        </span>
                        <input type="search" wire:model.live.debounce.300ms="search"
                               class="form-control border-start-0 ps-0" placeholder="Mahsulot nomi yoki kodi...">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Turi</label>
                    <select wire:model.live="typeFilter" class="form-select">
                        <option value="">Barchasi</option>
                        @foreach($stockTypes as $case)
                            <option value="{{ $case->value }}">{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($movements->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-package-variant"></i>
                    <h6>Harakat yo'q</h6>
                    <p>Mahsulot kelganda yoki chiqib ketganda shu yerda qayd etiladi.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Mahsulot</th>
                            <th>Turi</th>
                            <th class="text-center">Miqdor</th>
                            <th>Sana</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($movements as $movement)
                            @php
                                $isIncome = $movement->type === \App\Casts\ProductStockType::Add;
                                $tone = match($movement->type) {
                                    \App\Casts\ProductStockType::Add => 'badge-success',
                                    \App\Casts\ProductStockType::Sell => 'badge-warning',
                                    default => 'badge-danger',
                                };
                            @endphp
                            <tr wire:key="movement-{{ $movement->id }}">
                                <td class="text-muted tabular">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $movement->product?->name ?? '—' }}</span>
                                    @if($movement->product?->code)
                                        <small class="text-muted tabular ms-1">
                                            {{ $movement->product->formattedCode() }}
                                        </small>
                                    @endif
                                </td>
                                <td><span class="badge {{ $tone }}">{{ $movement->type->label() }}</span></td>
                                <td class="text-center tabular fw-bold {{ $isIncome ? 'text-success' : 'text-danger' }}">
                                    {{ $isIncome ? '+' : '−' }}{{ $movement->quantity }}
                                </td>
                                <td class="text-muted tabular">{{ $movement->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $movement->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$movement->id.')'"
                                                      title="Harakat o'chirilsinmi?"
                                                      text="Zaxira qoldig'i mos ravishda qaytariladi."/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $movements->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        <x-modal :title="$stockId ? 'Harakatni tahrirlash' : 'Yangi harakat'" icon="mdi-swap-vertical"
                 subtitle="Zaxira avtomatik yangilanadi" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Mahsulot</label>
                        <select wire:model="product_id" class="form-select @error('product_id') is-invalid @enderror">
                            <option value="">Tanlang...</option>
                            @foreach($this->products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} — zaxira: {{ (int) $product->current_stock }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Turi</label>
                        <div class="row g-2">
                            @foreach($stockTypes as $case)
                                <div class="col-4">
                                    <button type="button" wire:click="$set('type', '{{ $case->value }}')"
                                            class="btn w-100 {{ $type === $case->value ? 'btn-primary' : 'btn-inverse-primary' }}"
                                            style="font-size:.8rem">
                                        {{ $case->label() }}
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="form-label">Miqdor</label>
                        <input type="number" wire:model="quantity" min="1" inputmode="numeric"
                               class="form-control tabular @error('quantity') is-invalid @enderror" placeholder="0">
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
