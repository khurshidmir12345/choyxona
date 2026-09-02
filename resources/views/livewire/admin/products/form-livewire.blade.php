<div>
    <x-modal :title="$productId ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot'"
             :icon="$productId ? 'mdi-pencil-outline' : 'mdi-food-variant'"
             subtitle="Narxlar so'mda" size="modal-lg" close="close">

        <form wire:submit="save">
            <div class="modal-body">

                {{-- ---------------------------------------------------- nomi --}}
                <div class="mb-3">
                    <label class="form-label">Nomi</label>
                    <input type="text" wire:model="name" autofocus
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="Masalan: Ko'k choy">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- ----------------------------------------------- kategoriya --}}
                <div class="mb-3">
                    <label class="form-label d-flex align-items-center justify-content-between">
                        <span>Kategoriya</span>
                        @if($this->categories->isNotEmpty())
                            <span class="form-label-note">{{ $this->categories->count() }} ta</span>
                        @endif
                    </label>

                    <div class="chip-group @error('category_id') is-invalid @enderror">
                        @foreach($this->categories as $category)
                            <button type="button" wire:key="cat-chip-{{ $category->id }}"
                                    wire:click="$set('category_id', {{ $category->id }})"
                                    class="chip {{ (int) $category_id === $category->id ? 'active' : '' }}">
                                @if((int) $category_id === $category->id)
                                    <i class="mdi mdi-check"></i>
                                @endif
                                {{ $category->name }}
                            </button>
                        @endforeach

                        @if(! $showNewCategory)
                            <button type="button" class="chip chip-add" wire:click="startNewCategory">
                                <i class="mdi mdi-plus"></i> Yangi kategoriya
                            </button>
                        @endif
                    </div>

                    @if($showNewCategory)
                        <div class="chip-new" wire:key="new-category-box">
                            <input type="text" wire:model="newCategoryName"
                                   x-data x-init="$nextTick(() => $el.focus())"
                                   wire:keydown.enter.prevent="createCategory"
                                   wire:keydown.escape.stop="cancelNewCategory"
                                   class="form-control @error('newCategoryName') is-invalid @enderror"
                                   placeholder="Kategoriya nomi, masalan: Salatlar">
                            <button type="button" class="btn btn-primary" wire:click="createCategory"
                                    wire:loading.attr="disabled" wire:target="createCategory">
                                <i class="mdi mdi-check"></i> Qo'shish
                            </button>
                            <button type="button" class="btn btn-inverse-secondary" wire:click="cancelNewCategory"
                                    title="Bekor qilish">
                                <i class="mdi mdi-close"></i>
                            </button>
                        </div>
                        @error('newCategoryName') <div class="field-error">{{ $message }}</div> @enderror
                        <div class="form-text">Enter — qo'shadi, Esc — yopadi.</div>
                    @elseif($this->categories->isEmpty())
                        <div class="form-text">Hali kategoriya yo'q. "Yangi kategoriya" tugmasi bilan yarating.</div>
                    @endif

                    @error('category_id') <div class="field-error">{{ $message }}</div> @enderror
                </div>

                {{-- --------------------------------------------------- narxlar --}}
                <div class="row g-2 mb-3">
                    <div class="col-4">
                        <label class="form-label">Tannarx</label>
                        <input type="number" wire:model.blur="price" min="0" inputmode="numeric"
                               class="form-control tabular @error('price') is-invalid @enderror" placeholder="0">
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label">Sotuv narxi</label>
                        <input type="number" wire:model.blur="sell_price" min="0" inputmode="numeric"
                               class="form-control tabular @error('sell_price') is-invalid @enderror" placeholder="0">
                        @error('sell_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-4">
                        <label class="form-label">Chegirma, %</label>
                        <input type="number" wire:model="discount" min="0" max="100" inputmode="numeric"
                               class="form-control tabular @error('discount') is-invalid @enderror" placeholder="0">
                        @error('discount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    @if($price !== '' && $sell_price !== '')
                        @php $margin = (int) $sell_price - (int) $price; @endphp
                        <div class="col-12">
                            <div class="margin-note {{ $margin >= 0 ? 'is-plus' : 'is-minus' }}">
                                <i class="mdi {{ $margin >= 0 ? 'mdi-trending-up' : 'mdi-trending-down' }}"></i>
                                Bir donadan foyda:
                                <strong class="tabular">{{ number_format($margin, 0, ',', ' ') }} so'm</strong>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ------------------------------------------- kod va zaxira --}}
                <div class="row g-2 mb-3">
                    <div class="{{ $productId ? 'col-6' : 'col-12' }}">
                        <label class="form-label">Kod <span class="form-label-note">skaner uchun</span></label>
                        <input type="number" wire:model="code" inputmode="numeric"
                               class="form-control tabular @error('code') is-invalid @enderror">
                        @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    @if($productId)
                        <div class="col-6">
                            <label class="form-label">Zaxira <span class="form-label-note">dona</span></label>
                            <input type="number" wire:model="current_stock" inputmode="numeric"
                                   class="form-control tabular">
                        </div>
                    @endif
                </div>

                {{-- ------------------------------------------------------ rasm --}}
                <x-image-upload model="image" :preview="$currentImage" size="88"/>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-inverse-secondary" wire:click="close">Bekor qilish</button>
                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save, image">
                    <span wire:loading.remove wire:target="image"><i class="mdi mdi-check me-1"></i> Saqlash</span>
                    <span wire:loading wire:target="image">Rasm yuklanmoqda...</span>
                </button>
            </div>
        </form>
    </x-modal>
</div>
