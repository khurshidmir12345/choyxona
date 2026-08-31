<div>
    <div class="pos-page-head">
        <div>
            <h3>Joylar</h3>
            <p>Stol, so'ri va xonalar</p>
        </div>
        <div class="pos-head-actions">
            <a href="{{ route('cafe.create') }}" class="btn btn-inverse-primary btn-rounded">
                <i class="mdi mdi-sofa-outline me-1"></i> Zalga o'tish
            </a>
            <button type="button" class="btn btn-primary btn-rounded" wire:click="createPlace">
                <i class="mdi mdi-plus me-1"></i> Joy qo'shish
            </button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="input-group" style="max-width: 420px;">
                <span class="input-group-text bg-white border-end-0"><i class="mdi mdi-magnify text-muted"></i></span>
                <input type="search" wire:model.live.debounce.300ms="search"
                       class="form-control border-start-0 ps-0" placeholder="Joy nomi bo'yicha qidirish...">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($places->isEmpty())
                <div class="empty-state">
                    <i class="mdi mdi-table-furniture"></i>
                    <h6>Joy yo'q</h6>
                    <p>Zalda buyurtma qabul qilish uchun stol qo'shing.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                        <tr>
                            <th style="width:60px">#</th>
                            <th>Nomi</th>
                            <th>Holati</th>
                            <th class="text-center">Sig'imi</th>
                            <th class="text-end">Amallar</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($places as $place)
                            <tr wire:key="place-row-{{ $place->id }}">
                                <td class="text-muted tabular">{{ $loop->iteration }}</td>
                                <td class="fw-semibold">{{ $place->name }}</td>
                                <td>
                                    @if($place->isBusy())
                                        <span class="badge badge-danger">Band</span>
                                    @else
                                        <span class="badge badge-success">Bo'sh</span>
                                    @endif
                                </td>
                                <td class="text-center tabular">{{ $place->capacity }}</td>
                                <td class="text-end text-nowrap">
                                    <button type="button" class="btn btn-inverse-primary btn-sm"
                                            wire:click="edit({{ $place->id }})" title="Tahrirlash">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </button>
                                    <x-confirm-button :call="'delete('.$place->id.')'"
                                                      title="Joy o'chirilsinmi?"/>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">{{ $places->links() }}</div>
            @endif
        </div>
    </div>

    @if($showForm)
        <x-modal :title="$placeId ? 'Joyni tahrirlash' : 'Yangi joy'" close="closeForm">
            <form wire:submit="save">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nomi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror" placeholder="Masalan: 1-so'ri">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label class="form-label fw-semibold">Sig'imi (necha kishilik)</label>
                        <input type="number" wire:model="capacity" min="1" inputmode="numeric"
                               class="form-control tabular @error('capacity') is-invalid @enderror">
                        @error('capacity') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
