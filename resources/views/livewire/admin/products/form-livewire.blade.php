<x-ui.modal :title="$productId ? 'Mahsulotni tahrirlash' : 'Yangi mahsulot'"
            subtitle="Narxlar so'mda kiritiladi" size="lg" close="close">

    <form wire:submit="save" class="space-y-4 p-5">

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block sm:col-span-2">
                <span class="label">Nomi</span>
                <input type="text" wire:model="name" class="input @error('name') input-error @enderror"
                       placeholder="Masalan: Ko'k choy" autofocus>
                @error('name') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="label">Kategoriya</span>
                <select wire:model="category_id" class="select @error('category_id') input-error @enderror">
                    <option value="">Tanlang…</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="label">Kod</span>
                <input type="number" wire:model="code" class="input tabular @error('code') input-error @enderror"
                       inputmode="numeric">
                @error('code') <span class="field-error">{{ $message }}</span> @enderror
                <span class="hint">Skaner uchun ishlatiladi.</span>
            </label>

            <label class="block">
                <span class="label">Tannarx</span>
                <input type="number" wire:model.blur="price" min="0" inputmode="numeric"
                       class="input tabular @error('price') input-error @enderror" placeholder="0">
                @error('price') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="label">Sotuv narxi</span>
                <input type="number" wire:model.blur="sell_price" min="0" inputmode="numeric"
                       class="input tabular @error('sell_price') input-error @enderror" placeholder="0">
                @error('sell_price') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            <label class="block">
                <span class="label">Chegirma %</span>
                <input type="number" wire:model="discount" min="0" max="100" inputmode="numeric"
                       class="input tabular @error('discount') input-error @enderror" placeholder="0">
                @error('discount') <span class="field-error">{{ $message }}</span> @enderror
            </label>

            @if($productId)
                <label class="block">
                    <span class="label">Zaxira</span>
                    <input type="number" wire:model="current_stock" inputmode="numeric" class="input tabular">
                    <span class="hint">Kirim/chiqim orqali o'zgartirish afzalroq.</span>
                </label>
            @endif
        </div>

        @php
            $margin = (int) $sell_price - (int) $price;
        @endphp
        @if($price !== '' && $sell_price !== '')
            <div class="rounded-lg border border-ink-200 bg-ink-50 px-4 py-3 text-sm">
                <span class="text-ink-500">Bir donadan foyda:</span>
                <span class="tabular ml-1 font-bold {{ $margin >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                    {{ number_format($margin, 0, ',', ' ') }} so'm
                </span>
            </div>
        @endif

        <div>
            <span class="label">Rasm</span>
            <div class="flex items-center gap-4">
                <span class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-ink-100">
                    @if($image)
                        <img src="{{ $image->temporaryUrl() }}" alt="" class="h-full w-full object-cover">
                    @elseif($currentImage)
                        <img src="{{ $currentImage }}" alt="" class="h-full w-full object-cover">
                    @else
                        <x-icon name="image" class="h-6 w-6 text-ink-300"/>
                    @endif
                </span>
                <div class="min-w-0 flex-1">
                    <input type="file" wire:model="image" accept="image/*"
                           class="block w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0
                                  file:bg-ink-100 file:px-3 file:py-2 file:text-sm file:font-semibold
                                  file:text-ink-700 hover:file:bg-ink-200">
                    <div wire:loading wire:target="image" class="mt-1 text-xs text-ink-500">Yuklanmoqda…</div>
                    @error('image') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-ink-200/80 pt-4">
            <button type="button" class="btn btn-secondary" wire:click="close">Bekor qilish</button>
            <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="save">
                <x-icon name="check"/>
                Saqlash
            </button>
        </div>
    </form>
</x-ui.modal>
