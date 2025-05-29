<div>
    <form wire:submit.prevent="update">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mahsulotni tahrirlash</h5>
                <button wire:click="closeModal" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
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

                {{-- Mahsulot tanlash --}}
                <div class="mb-3">
                    <label>Mahsulot</label>
                    <select wire:model="product_id" class="form-control">
                        <option value="">Tanlang</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                {{-- Holat tanlash --}}
                <div class="mb-3">
                    <label>Mahsulot Holati</label>
                    <select wire:model="type" class="form-control">
                        <option value="">Tanlang</option>
                        @foreach ($stockTypes as $productStatus)
                            <option value="{{ $productStatus->value }}">{{ $productStatus->label() }}</option>
                        @endforeach
                    </select>
                    @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                {{-- Miqdor --}}
                <div class="mb-3">
                    <label>Soni</label>
                    <input type="number" wire:model="quantity" class="form-control">
                    @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal" wire:click="closeModal">Bekor qilish</button>
                <button type="submit" class="btn btn-primary" wire:click="update">Yangilash</button>
            </div>
        </div>
    </form>
</div>
