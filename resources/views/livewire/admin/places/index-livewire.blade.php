<div>
    <x-ui.page-header title="Joylar" subtitle="Stol, so'ri va xonalar">
        <a href="{{ route('cafe.create') }}" class="btn btn-secondary" wire:navigate>
            <x-icon name="table"/>
            Zalga o'tish
        </a>
        <button type="button" class="btn btn-primary" wire:click="createPlace">
            <x-icon name="plus"/>
            Joy qo'shish
        </button>
    </x-ui.page-header>

    <div class="card mb-4">
        <div class="p-4">
            <label class="relative block max-w-md">
                <x-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9"
                       placeholder="Joy nomi bo'yicha qidirish…">
            </label>
        </div>
    </div>

    <div class="card">
        @if($places->isEmpty())
            <x-ui.empty icon="table" title="Joy yo'q"
                        description="Zalda buyurtma qabul qilish uchun stol qo'shing."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-12">#</th>
                        <th>Nomi</th>
                        <th>Holati</th>
                        <th class="text-center">Sig'imi</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($places as $place)
                        <tr wire:key="place-row-{{ $place->id }}">
                            <td class="tabular text-ink-400">{{ $loop->iteration }}</td>
                            <td class="font-semibold text-ink-900">{{ $place->name }}</td>
                            <td>
                                @if($place->isBusy())
                                    <span class="badge badge-red">Band</span>
                                @else
                                    <span class="badge badge-green">Bo'sh</span>
                                @endif
                            </td>
                            <td class="tabular text-center">{{ $place->capacity }}</td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="edit({{ $place->id }})" title="Tahrirlash">
                                        <x-icon name="edit"/>
                                    </button>
                                    <x-ui.confirm :action="'delete('.$place->id.')'"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $places->links() }}
            </div>
        @endif
    </div>

    @if($showForm)
        <x-ui.modal :title="$placeId ? 'Joyni tahrirlash' : 'Yangi joy'" close="closeForm">
            <form wire:submit="save" class="space-y-4 p-5">
                <label class="block">
                    <span class="label">Nomi</span>
                    <input type="text" wire:model="name" class="input @error('name') input-error @enderror"
                           placeholder="Masalan: 1-so'ri" autofocus>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </label>

                <label class="block">
                    <span class="label">Sig'imi (necha kishilik)</span>
                    <input type="number" wire:model="capacity" min="1" inputmode="numeric"
                           class="input tabular @error('capacity') input-error @enderror">
                    @error('capacity') <span class="field-error">{{ $message }}</span> @enderror
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
