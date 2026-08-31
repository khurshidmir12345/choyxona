<div>
    <x-modal :title="$productId ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot'"
             subtitle="Narxlar so'mda kiritiladi" size="modal-lg" close="close">

        <form wire:submit="save">
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nomi</label>
                        <input type="text" wire:model="name" autofocus
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="Masalan: Ko'k choy">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kategoriya</label>
                        <select wire:model="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option value="">Tanlang...</option>
                            @foreach($this->categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Kod</label>
                        <input type="number" wire:model="code" inputmode="numeric"
                               class="form-control tabular @error('code') is-invalid @enderror">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Skaner uchun ishlatiladi.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tannarx</label>
                        <input type="number" wire:model.blur="price" min="0" inputmode="numeric"
                               class="form-control tabular @error('price') is-invalid @enderror" placeholder="0">
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sotuv narxi</label>
                        <input type="number" wire:model.blur="sell_price" min="0" inputmode="numeric"
                               class="form-control tabular @error('sell_price') is-invalid @enderror" placeholder="0">
                        @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Chegirma %</label>
                        <input type="number" wire:model="discount" min="0" max="100" inputmode="numeric"
                               class="form-control tabular @error('discount') is-invalid @enderror" placeholder="0">
                        @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if($productId)
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Zaxira</label>
                            <input type="number" wire:model="current_stock" inputmode="numeric"
                                   class="form-control tabular">
                            <div class="form-text">Kirim/chiqim orqali o'zgartirish afzalroq.</div>
                        </div>
                    @endif

                    @if($price !== '' && $sell_price !== '')
                        @php $margin = (int) $sell_price - (int) $price; @endphp
                        <div class="col-12">
                            <div class="alert {{ $margin >= 0 ? 'alert-success' : 'alert-danger' }} mb-0 py-2">
                                <i class="mdi mdi-trending-up me-1"></i>
                                Bir donadan foyda:
                                <strong class="tabular">{{ number_format($margin, 0, ',', ' ') }} so'm</strong>
                            </div>
                        </div>
                    @endif

                    <div class="col-12">
                        <label class="form-label fw-semibold">Rasm</label>
                        <div class="d-flex align-items-center gap-3">
                            <span class="thumb-sm d-inline-flex align-items-center justify-content-center flex-shrink-0"
                                  style="width:64px;height:64px">
                                @if($image)
                                    <img src="{{ $image->temporaryUrl() }}" alt="" class="thumb-sm"
                                         style="width:64px;height:64px">
                                @elseif($currentImage)
                                    <img src="{{ $currentImage }}" alt="" class="thumb-sm"
                                         style="width:64px;height:64px" onerror="this.remove()">
                                @else
                                    <i class="mdi mdi-image-outline text-muted"></i>
                                @endif
                            </span>
                            <div class="flex-grow-1">
                                <input type="file" wire:model="image" accept="image/*"
                                       class="form-control @error('image') is-invalid @enderror">
                                <div wire:loading wire:target="image" class="form-text">Yuklanmoqda...</div>
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-inverse-secondary" wire:click="close">Bekor qilish</button>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                    <i class="mdi mdi-check me-1"></i> Saqlash
                </button>
            </div>
        </form>
    </x-modal>
</div>
