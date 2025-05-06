<div>
    <!-- Search and Add Button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" wire:model.live="search" class="form-control w-50" placeholder="Joy nomi bo‘yicha qidirish...">
        <button class="btn btn-success btn-rounded ms-2" data-bs-toggle="modal" data-bs-target="#placeModal" wire:click="$set('place_id', null)">
            <i class="fa fa-plus"> Joy qo‘shish</i>
        </button>
    </div>

    <!-- Places Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Nomi</th>
            <th>Xolati</th>
            <th>Sig‘imi</th>
            <th>Amallar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($places as $index => $place)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $place->name }}</td>
                <td>
                    @if($place->status->value == \App\Casts\PlaceStatusEnum::Busy->value)
                        <span class="badge bg-danger">Band</span>
                    @elseif($place->status->value == \App\Casts\PlaceStatusEnum::Empty->value)
                        <span class="badge bg-success">Bo‘sh</span>
                    @else
                        <span class="badge bg-secondary">Noma’lum</span>
                    @endif
                </td>
                <td>{{ $place->capacity }}</td>
                <td>
                    <button class="btn btn-sm btn-warning" wire:click="edit({{ $place->id }})" data-bs-toggle="modal" data-bs-target="#placeModal">
                        Tahrirlash
                    </button>
                    <button class="btn btn-sm btn-danger" wire:click="delete({{ $place->id }})" onclick="confirm('Rostdan ham o‘chirmoqchimisiz?') || event.stopImmediatePropagation()">
                        O‘chirish
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $places->links() }}

    <!-- Create/Edit Modal -->
    <div wire:ignore.self class="modal fade" id="placeModal" tabindex="-1" aria-labelledby="placeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form wire:submit.prevent="{{ $place_id ? 'update' : 'create' }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="placeModalLabel">
                            {{ $place_id ? 'Joyni tahrirlash' : 'Yangi joy qo‘shish' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger mt-2 mx-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Joy nomi</label>
                            <input type="text" wire:model.defer="name" class="form-control" placeholder="Masalan: Zaxira xona">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sig‘imi</label>
                            <input type="number" wire:model.defer="capacity" class="form-control" placeholder="Masalan: 50">
                            @error('capacity') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $place_id ? 'Saqlash' : 'Qo‘shish' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
