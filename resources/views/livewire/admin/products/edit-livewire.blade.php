<div>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Mahsulotni tahrirlash</h5>
            <button type="button" class="btn-close" wire:click="closeModal" data-bs-dismiss="modal"
                    aria-label="Yopish"></button>
        </div>
        <div class="modal-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label>Nomi</label>f
                <input type="text" wire:model="name" class="form-control">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Sof narxi</label>
                <input type="text" wire:model.debounce.500ms="price" class="form-control" oninput="price_format(this)">
                @error('price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Sotish narxi</label>
                <input type="text" class="form-control" wire:model.debounce.500ms="sell_price"
                       oninput="price_format(this)">
                @error('sell_price') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Chegirma % (ixtiyoriy)</label>
                <input type="number" step="1" wire:model="discount" class="form-control">
                @error('discount') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Kod</label>
                <input type="number" wire:model="code" class="form-control" readonly>
                @error('code') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Kategoriya</label>
                <select wire:model="category_id" class="form-control">
                    <option class="fw-bold" value="">Tanlang</option>
                    @foreach ($categories as $category)
                        <option class="fw-bold" value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Rasm yuklash</label>
                <input type="file" wire:model.live="image" class="form-control">
                @error('image') <span class="text-danger">{{ $message }}</span> @enderror

                @if ($image)
                    <img src="{{ $image->temporaryUrl() }}" class="mt-2 rounded" style="width: 100px;">
                @elseif ($current_image)
                    <img src="{{ $current_image }}" class="mt-2 rounded" style="width: 100px;">
                @endif
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" wire:click="closeModal">Bekor qilish</button>
            <button type="submit" class="btn btn-primary" wire:click="updateProduct">Yangilash</button>
        </div>
    </div>
</div>

