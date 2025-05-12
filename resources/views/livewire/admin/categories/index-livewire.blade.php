<div>
    <!-- Button to Open Create Modal -->
    <div class="d-flex justify-content-between align-items-center mb-2">
        <input type="text" wire:model.live="search" class="form-control w-50" placeholder="Kategoriya nomi bo‘yicha qidirish...">
        <button class="btn btn-success btn-rounded mb-2" data-bs-toggle="modal" data-bs-target="#categoryModal" wire:click="$set('category_id', null)">
            <i class="fa fa-plus">  Kategoriya qo‘shish </i>
        </button>
    </div>
    <!-- Category Table -->
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Nomi</th>
            <th>Amallar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $index => $category)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    <button class="btn btn-sm btn-warning" wire:click="edit({{ $category->id }})" data-bs-toggle="modal" data-bs-target="#categoryModal">
                        Tahrirlash
                    </button>
                    <button class="btn btn-sm btn-danger" wire:click="delete({{ $category->id }})" onclick="confirm('Ishonchingiz komilmi?') || event.stopImmediatePropagation()">
                        O‘chirish
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    {{ $categories->links() }}

    <!-- Create/Edit Modal -->
    <div wire:ignore.self class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form wire:submit.prevent="{{ $category_id ? 'update' : 'create' }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">
                            {{ $category_id ? 'Kategoriyani tahrirlash' : 'Yangi kategoriya qo‘shish' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
                    </div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kategoriya nomi</label>
                            <input type="text" wire:model.defer="name" class="form-control" placeholder="Masalan: Mevalar">
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                        <button type="submit" class="btn btn-primary">
                            {{ $category_id ? 'Saqlash' : 'Qo‘shish' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
