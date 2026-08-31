<div>
    <x-ui.page-header title="Xarajatlar" subtitle="Kompaniya chiqimlari">
        <a href="{{ route('expense-categories.index') }}" class="btn btn-secondary" wire:navigate>
            <x-icon name="folder"/>
            Kategoriyalar
        </a>
        <button type="button" class="btn btn-primary" wire:click="createExpense">
            <x-icon name="plus"/>
            Xarajat qo'shish
        </button>
    </x-ui.page-header>

    <div class="mb-4 grid gap-3 sm:grid-cols-3">
        <x-ui.stat label="Jami" :value="number_format($totalAmount, 0, ',', ' ')" suffix="so'm"
                   icon="wallet" tone="blue"/>
        <x-ui.stat label="Kutilmoqda" :value="number_format($pendingAmount, 0, ',', ' ')" suffix="so'm"
                   icon="clock" tone="amber"/>
        <x-ui.stat label="Tasdiqlangan" :value="number_format($approvedAmount, 0, ',', ' ')" suffix="so'm"
                   icon="check" tone="green"/>
    </div>

    <div class="card mb-4">
        <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-5">
            <label class="relative block">
                <span class="label">Qidirish</span>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-[2.15rem] h-4 w-4 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9" placeholder="Nomi…">
            </label>
            <label class="block">
                <span class="label">Kategoriya</span>
                <select wire:model.live="selectedCategory" class="select">
                    <option value="">Barchasi</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="block">
                <span class="label">Holati</span>
                <select wire:model.live="selectedStatus" class="select">
                    <option value="">Barchasi</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="approved">Tasdiqlangan</option>
                    <option value="rejected">Rad etilgan</option>
                </select>
            </label>
            <label class="block">
                <span class="label">Sanadan</span>
                <input type="date" wire:model.live="dateFrom" class="input">
            </label>
            <label class="block">
                <span class="label">Sanagacha</span>
                <input type="date" wire:model.live="dateTo" class="input">
            </label>
        </div>
        <div class="border-t border-ink-200/80 px-4 py-3">
            <button type="button" class="btn btn-secondary btn-sm" wire:click="clearFilters">
                <x-icon name="refresh"/>
                Filtrni tozalash
            </button>
        </div>
    </div>

    <div class="card">
        @if($expenses->isEmpty())
            <x-ui.empty icon="wallet" title="Xarajat yo'q"
                        description="Tanlangan filtr bo'yicha yozuv topilmadi."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Nomi</th>
                        <th>Kategoriya</th>
                        <th>Sana</th>
                        <th>To'lov</th>
                        <th class="text-right">Summa</th>
                        <th>Holati</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($expenses as $expense)
                        @php
                            [$statusLabel, $statusTone] = match($expense->status) {
                                'approved' => ['Tasdiqlangan', 'badge-green'],
                                'rejected' => ['Rad etilgan', 'badge-red'],
                                default => ['Kutilmoqda', 'badge-amber'],
                            };
                        @endphp
                        <tr wire:key="expense-{{ $expense->id }}">
                            <td>
                                <span class="font-semibold text-ink-900">{{ $expense->title }}</span>
                                @if($expense->description)
                                    <span class="block max-w-xs truncate text-xs text-ink-500">
                                        {{ $expense->description }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($expense->category)
                                    <span class="badge" style="background-color: {{ $expense->category->color }}1a; color: {{ $expense->category->color }}">
                                        {{ $expense->category->name }}
                                    </span>
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="tabular whitespace-nowrap text-ink-600">
                                {{ $expense->expense_date?->format('d.m.Y') ?? '—' }}
                            </td>
                            <td class="text-ink-500">{{ $expense->payment_method ?? '—' }}</td>
                            <td class="tabular text-right font-bold text-ink-900">
                                {{ number_format((float) $expense->amount, 0, ',', ' ') }}
                            </td>
                            <td>
                                <div x-data="{ open: false }" class="relative" @click.outside="open = false">
                                    <button type="button" @click="open = !open" class="badge {{ $statusTone }}">
                                        {{ $statusLabel }}
                                        <x-icon name="chevron-down" class="h-3 w-3"/>
                                    </button>
                                    <div x-show="open" x-cloak
                                         class="absolute left-0 z-20 mt-1 w-40 rounded-lg border border-ink-200 bg-white p-1 shadow-pop">
                                        @foreach(['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'] as $value => $label)
                                            <button type="button" @click="open = false"
                                                    wire:click="updateStatus({{ $expense->id }}, '{{ $value }}')"
                                                    class="block w-full rounded px-3 py-1.5 text-left text-sm text-ink-700 hover:bg-ink-100">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="edit({{ $expense->id }})" title="Tahrirlash">
                                        <x-icon name="edit"/>
                                    </button>
                                    <x-ui.confirm :action="'delete('.$expense->id.')'"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    @if($showForm)
        <x-ui.modal :title="$expenseId ? 'Xarajatni tahrirlash' : 'Yangi xarajat'" size="lg" close="closeForm">
            <form wire:submit="save" class="space-y-4 p-5">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block sm:col-span-2">
                        <span class="label">Nomi</span>
                        <input type="text" wire:model="title" class="input @error('title') input-error @enderror"
                               placeholder="Masalan: Ijara to'lovi" autofocus>
                        @error('title') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="label">Kategoriya</span>
                        <select wire:model="expense_category_id"
                                class="select @error('expense_category_id') input-error @enderror">
                            <option value="">Tanlang…</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('expense_category_id') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="label">Summa</span>
                        <input type="number" wire:model="amount" min="0" step="0.01" inputmode="decimal"
                               class="input tabular @error('amount') input-error @enderror" placeholder="0">
                        @error('amount') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="label">Sana</span>
                        <input type="date" wire:model="expense_date"
                               class="input @error('expense_date') input-error @enderror">
                        @error('expense_date') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="block">
                        <span class="label">To'lov turi</span>
                        <select wire:model="payment_method" class="select">
                            @foreach(\App\Livewire\Admin\Expenses\IndexLivewire::PAYMENT_METHODS as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                        @error('payment_method') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <label class="block sm:col-span-2">
                        <span class="label">Izoh</span>
                        <textarea wire:model="description" rows="3" class="textarea"
                                  placeholder="Ixtiyoriy"></textarea>
                        @error('description') <span class="field-error">{{ $message }}</span> @enderror
                    </label>

                    <div class="sm:col-span-2">
                        <span class="label">Holati</span>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach(['pending' => 'Kutilmoqda', 'approved' => 'Tasdiqlangan', 'rejected' => 'Rad etilgan'] as $value => $label)
                                <button type="button" wire:click="$set('status', '{{ $value }}')"
                                        class="rounded-lg border px-3 py-2 text-sm font-semibold transition-colors
                                               {{ $status === $value
                                                   ? 'border-brand-600 bg-brand-50 text-brand-700'
                                                   : 'border-ink-200 bg-white text-ink-600 hover:bg-ink-50' }}">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

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
