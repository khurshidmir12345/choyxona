<div>
    <form wire:submit.prevent="update" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mahsulotni tahrirlash</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
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
                    <label>Product Nomi</label>
                    <input type="text" wire:model="product_stock_name" class="form-control">
                    @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label>Soni</label>
                    <input type="text" wire:model="quantity" class="form-control">
                    @error('price') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-primary">Yangilash</button>
            </div>
        </div>
    </form>
</div>
