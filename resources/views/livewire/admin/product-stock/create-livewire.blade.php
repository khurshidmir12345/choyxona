<div>
    <form wire:submit.prevent="save">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mahsulot zaxirasini qo‘shish</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Yopish"></button>
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

                @if (session()->has('success'))
                    <div id="success-alert" class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div id="error-alert" class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-3">
                    <label>Mahsulotni tanlang</label>
                    <select wire:model="product_id" class="form-control">
                        <option value="">Tanlang</option>

                        @foreach ($products as $product)

                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label>Mahsulot Holati</label>
                    <select wire:model="status" class="form-control">
                        <option value="">Tanlang</option>

                        @foreach (\App\Casts\ProductStockType::cases() as $productStatus)
                            <option value="{{ $productStatus->value }}">
                                {{ $productStatus->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="mb-3">
                    <label>Miqdori</label>
                    <input type="number" wire:model="quantity" class="form-control" min="1">
                    @error('quantity') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bekor qilish</button>
                <button type="submit" class="btn btn-primary">Saqlash</button>
            </div>
        </div>
    </form>
</div>
