<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" wire:model.live="search" class="form-control w-50" placeholder="Mahsulot nomi yoki kodi bo‘yicha qidirish...">
        <a href="#" data-bs-toggle="modal" data-bs-target="#add_product" class="btn btn-success btn-rounded">
            <i class="fa fa-plus">  Yangi mahsulot</i>
        </a>
    </div>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Kod</th>
            <th>Rasm</th>
            <th>Nomi</th>
            <th>Narxi</th>
            <th>Soni</th>
            <th>Turi</th>
            <th>Amallar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{str_pad($product->code, 5, '0', STR_PAD_LEFT)}}</td>
                <td>
                    @if ($product->image)
                        <img src="{{ $product->image }}" alt="{{ $product->name }}" style="width: 70px; height: 70px; padding: 0; margin: 0;" class="object-cover rounded">
                    @else
                        <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-400">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    @endif
                </td>
                <td>{{ $product->name }}</td>
                <td>{{ number_format($product->sell_price, 0, ',', ' ') }}</td>
                <td class="fw-bold">{{ $product->current_stock }}</td>
                <td>
                    <p class="category-label" style="background-color: #e0f2fe; color: #1e40af; padding: 8px 12px; border-radius: 6px; font-weight: 500; display: inline-block; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
                        {{ $product->category->name }}
                    </p>
                </td>
                <td>
                    <a href="#" data-bs-toggle="modal" data-bs-target="#show_product"
                       wire:click="show({{ $product->id }})" class="btn btn-info btn-sm">Ko‘rish</a>
                    <a href="#" class="btn btn-warning btn-sm" wire:click="edit({{ $product->id }})"
                       data-bs-toggle="modal" data-bs-target="#edit_product"
                    >Tahrirlash</a>
                    <button wire:click="delete({{ $product->id }})" class="btn btn-danger btn-sm">O‘chirish</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-2">
        {{ $products->links() }}
    </div>

    @if ($showProductId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.products.show-livewire', ['product_id' => $showProductId])
            </div>
        </div>
    @endif
    @if ($editProductId)
        <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5)">
            <div class="modal-dialog modal-md modal-dialog-scrollable">
                @livewire('admin.products.edit-livewire', ['product_id' => $editProductId])
            </div>
        </div>
    @endif
</div>
