<div>
    <x-ui.page-header title="Xarajat kategoriyalari" subtitle="Chiqimlarni guruhlash">
        <a href="{{ route('expenses.index') }}" class="btn btn-secondary" wire:navigate>
            <x-icon name="wallet"/>
            Xarajatlar
        </a>
        <button type="button" class="btn btn-primary" wire:click="createCategory">
            <x-icon name="plus"/>
            Kategoriya qo'shish
        </button>
    </x-ui.page-header>

    <div class="card mb-4">
        <div class="p-4">
            <label class="relative block max-w-md">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9"
                       placeholder="Nomi yoki izohi bo'yicha qidirish…">
            </label>
        </div>
    </div>

    <div class="card">
        @if($categories->isEmpty())
            <x-ui.empty icon="folder" title="Kategoriya yo'q"
                        description="Xarajatlarni turlarga ajratish uchun kategoriya qo'shing."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-12"></th>
                        <th>Nomi</th>
                        <th>Izoh</th>
                        <th class="text-center">Xarajatlar</th>
                        <th>Holati</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr wire:key="exp-cat-{{ $category->id }}">
                            <td>
                                <span class="block h-6 w-6 rounded-md"
                                      style="background-color: {{ $category->color ?: '#3b82f6' }}"></span>
                            </td>
                            <td class="font-semibold text-ink-900">{{ $category->name }}</td>
                            <td class="max-w-xs truncate text-ink-500">{{ $category->description ?: '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-gray tabular">{{ $category->expenses_count }}</span>
                            </td>
                            <td>
                                <button type="button" wire:click="toggleStatus({{ $category->id }})"
                                        class="badge {{ $category->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $category->is_active ? 'Faol' : 'Nofaol' }}
                                </button>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="edit({{ $category->id }})" title="Tahrirlash">
                                        <x-icon name="edit"/>
                                    </button>
                                    <x-ui.confirm :action="'delete('.$category->id.')'"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $categories->links() }}
            </div>
        @endif
    </div>

    @if($showForm)
        <x-ui.modal :title="$categoryId ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya'" close="closeForm">
            <form wire:submit="save" class="space-y-4 p-5">
                <label class="block">
                    <span class="label">Nomi</span>
                    <input type="text" wire:model="name" class="input @error('name') input-error @enderror"
                           placeholder="Masalan: Kommunal to'lovlar" autofocus>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="label">Izoh</span>
                    <textarea wire:model="description" rows="3" class="textarea" placeholder="Ixtiyoriy"></textarea>
                    @error('description') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <div>
                    <span class="label">Rang</span>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach(['#3b82f6', '#12866f', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9', '#64748b'] as $swatch)
                            <button type="button" wire:click="$set('color', '{{ $swatch }}')"
                                    class="h-8 w-8 rounded-lg ring-offset-2 transition
                                           {{ $color === $swatch ? 'ring-2 ring-ink-900' : '' }}"
                                    style="background-color: {{ $swatch }}"
                                    aria-label="{{ $swatch }}"></button>
                        @endforeach
                        <input type="color" wire:model="color" class="h-8 w-12 cursor-pointer rounded border-ink-200">
                    </div>
                    @error('color') <span class="field-error">{{ $message }}</span> @enderror
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
