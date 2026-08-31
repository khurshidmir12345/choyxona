<div>
    <x-ui.page-header title="Kirim / chiqim" subtitle="Zaxira harakatlari jurnali">
        <button type="button" class="btn btn-primary" wire:click="createMovement">
            <x-icon name="plus"/>
            Harakat qo'shish
        </button>
    </x-ui.page-header>

    <div class="card mb-4">
        <div class="grid gap-3 p-4 sm:grid-cols-[1fr_14rem]">
            <label class="relative block">
                <span class="label">Qidirish</span>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-[2.15rem] h-4 w-4 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9"
                       placeholder="Mahsulot nomi yoki kodi…">
            </label>
            <label class="block">
                <span class="label">Turi</span>
                <select wire:model.live="typeFilter" class="select">
                    <option value="">Barchasi</option>
                    @foreach($stockTypes as $case)
                        <option value="{{ $case->value }}">{{ $case->label() }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        @if($movements->isEmpty())
            <x-ui.empty icon="layers" title="Harakat yo'q"
                        description="Mahsulot kelganda yoki chiqib ketganda shu yerda qayd etiladi."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Mahsulot</th>
                        <th>Turi</th>
                        <th class="text-center">Miqdor</th>
                        <th>Sana</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($movements as $movement)
                        @php
                            $tone = match($movement->type) {
                                \App\Casts\ProductStockType::Add => 'badge-green',
                                \App\Casts\ProductStockType::Sell => 'badge-amber',
                                default => 'badge-red',
                            };
                            $isIncome = $movement->type === \App\Casts\ProductStockType::Add;
                        @endphp
                        <tr wire:key="movement-{{ $movement->id }}">
                            <td class="tabular text-ink-400">{{ $loop->iteration }}</td>
                            <td>
                                <span class="font-semibold text-ink-900">{{ $movement->product?->name ?? '—' }}</span>
                                @if($movement->product?->code)
                                    <span class="tabular ml-1 text-xs text-ink-400">
                                        {{ $movement->product->formattedCode() }}
                                    </span>
                                @endif
                            </td>
                            <td><span class="badge {{ $tone }}">{{ $movement->type->label() }}</span></td>
                            <td class="tabular text-center font-bold {{ $isIncome ? 'text-emerald-700' : 'text-red-600' }}">
                                {{ $isIncome ? '+' : '−' }}{{ $movement->quantity }}
                            </td>
                            <td class="tabular text-ink-500">{{ $movement->created_at?->format('d.m.Y H:i') ?? '—' }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="edit({{ $movement->id }})" title="Tahrirlash">
                                        <x-icon name="edit"/>
                                    </button>
                                    <x-ui.confirm :action="'delete('.$movement->id.')'"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    @if($showForm)
        <x-ui.modal :title="$stockId ? 'Harakatni tahrirlash' : 'Yangi harakat'"
                    subtitle="Zaxira avtomatik yangilanadi" close="closeForm">
            <form wire:submit="save" class="space-y-4 p-5">
                <label class="block">
                    <span class="label">Mahsulot</span>
                    <select wire:model="product_id" class="select @error('product_id') input-error @enderror">
                        <option value="">Tanlang…</option>
                        @foreach($this->products as $product)
                            <option value="{{ $product->id }}">
                                {{ $product->name }} — zaxira: {{ (int) $product->current_stock }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div>
                    <span class="label">Turi</span>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($stockTypes as $case)
                            <button type="button" wire:click="$set('type', '{{ $case->value }}')"
                                    class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors
                                           {{ $type === $case->value
                                               ? 'border-brand-600 bg-brand-50 text-brand-700'
                                               : 'border-ink-200 bg-white text-ink-600 hover:bg-ink-50' }}">
                                {{ $case->label() }}
                            </button>
                        @endforeach
                    </div>
                    @error('type') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <label class="block">
                    <span class="label">Miqdor</span>
                    <input type="number" wire:model="quantity" min="1" inputmode="numeric"
                           class="input tabular @error('quantity') input-error @enderror" placeholder="0">
                    @error('quantity') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div class="flex justify-end gap-2 border-t border-ink-200/80 pt-4">
                    <button type="button" class="btn btn-secondary" wire:click="closeForm">Bekor qilish</button>
                    <button type="submit" class="btn btn-primary">
                        <x-icon name="check"/>
                        Saqlash
                    </button>
                </div>
            </form>
        </x-ui.modal>
    @endif
</div>
