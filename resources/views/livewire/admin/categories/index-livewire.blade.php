<div>
    <x-ui.page-header title="Kategoriyalar" subtitle="Menyu bo'limlari">
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
                       placeholder="Kategoriya nomi bo'yicha qidirish…">
            </label>
        </div>
    </div>

    <div class="card">
        @if($categories->isEmpty())
            <x-ui.empty icon="tag" title="Kategoriya yo'q"
                        description="Mahsulotlarni guruhlash uchun kategoriya qo'shing."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nomi</th>
                        <th class="text-center">Mahsulotlar</th>
                        <th>Qo'shilgan</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($categories as $category)
                        <tr wire:key="category-{{ $category->id }}">
                            <td class="tabular text-ink-400">{{ $loop->iteration }}</td>
                            <td class="font-semibold text-ink-900">{{ $category->name }}</td>
                            <td class="text-center">
                                <span class="badge badge-gray tabular">{{ $category->products_count }}</span>
                            </td>
                            <td class="tabular text-ink-500">{{ $category->created_at?->format('d.m.Y') ?? '—' }}</td>
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
                           placeholder="Masalan: Issiq ichimliklar" autofocus>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
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
