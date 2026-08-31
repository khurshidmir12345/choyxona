<div>
    <x-ui.page-header title="Mahsulotlar" subtitle="Menyu va narxlar">
        <button type="button" class="btn btn-primary" wire:click="create">
            <x-icon name="plus"/>
            Yangi mahsulot
        </button>
    </x-ui.page-header>

    <div class="card mb-4">
        <div class="grid gap-3 p-4 sm:grid-cols-[1fr_16rem]">
            <label class="relative block">
                <span class="label">Qidirish</span>
                <x-icon name="search" class="pointer-events-none absolute left-3 top-[2.15rem] h-4 w-4 text-ink-400"/>
                <input type="search" wire:model.live.debounce.250ms="search" class="input pl-9"
                       placeholder="Nomi yoki kodi…">
            </label>
            <label class="block">
                <span class="label">Kategoriya</span>
                <select wire:model.live="categoryFilter" class="select">
                    <option value="">Barchasi</option>
                    @foreach($this->categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
    </div>

    <div class="card">
        @if($products->isEmpty())
            <x-ui.empty icon="box" title="Mahsulot yo'q"
                        description="Menyuga birinchi mahsulotni qo'shing."/>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                    <tr>
                        <th class="w-16">Kod</th>
                        <th class="w-16"></th>
                        <th>Nomi</th>
                        <th>Kategoriya</th>
                        <th class="text-right">Tannarx</th>
                        <th class="text-right">Sotuv narxi</th>
                        <th class="text-center">Zaxira</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td class="tabular text-xs font-semibold text-ink-500">{{ $product->formattedCode() }}</td>
                            <td>
                                <span class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-lg bg-ink-100">
                                    @if($product->imageUrl())
                                        <img src="{{ $product->imageUrl() }}" alt="" loading="lazy"
                                             class="h-full w-full object-cover" onerror="this.remove()">
                                    @else
                                        <x-icon name="image" class="h-4 w-4 text-ink-300"/>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="font-semibold text-ink-900">{{ $product->name }}</span>
                                @if($product->discount > 0)
                                    <span class="badge badge-red ml-1">-{{ $product->discount }}%</span>
                                @endif
                            </td>
                            <td>
                                @if($product->category)
                                    <span class="badge badge-brand">{{ $product->category->name }}</span>
                                @else
                                    <span class="text-ink-300">—</span>
                                @endif
                            </td>
                            <td class="tabular text-right text-ink-500">
                                {{ number_format((int) $product->price, 0, ',', ' ') }}
                            </td>
                            <td class="tabular text-right font-bold text-ink-900">
                                {{ number_format((int) $product->sell_price, 0, ',', ' ') }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ ($product->current_stock ?? 0) > 0 ? 'badge-green' : 'badge-red' }} tabular">
                                    {{ (int) ($product->current_stock ?? 0) }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="btn btn-sm btn-ghost"
                                            wire:click="edit({{ $product->id }})" title="Tahrirlash">
                                        <x-icon name="edit"/>
                                    </button>
                                    <x-ui.confirm :action="'delete('.$product->id.')'"/>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-ink-200/80 px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>

    @if($showForm)
        @livewire('admin.products.form-livewire',
                  ['productId' => $editProductId],
                  key('product-form-'.($editProductId ?? 'new')))
    @endif
</div>
