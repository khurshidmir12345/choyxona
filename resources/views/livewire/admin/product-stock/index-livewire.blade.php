<div>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" wire:model.live="search" class="form-control w-50" placeholder="Mahsulot nomi yoki kodi bo‘yicha qidirish...">
        <a href="#" data-bs-toggle="modal" data-bs-target="#add_product_stock" class="btn btn-success btn-rounded">
            <i class="fa fa-plus">  Yangi mahsulot</i>
        </a>
    </div>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>#</th>
            <th>Nomi</th>
            <th>Soni</th>
            <th>Turi</th>
            <th>Yaratilgan vaqt</th>
            <th>Harakatlar</th>
        </tr>
        </thead>
        <tbody>
        @foreach($productStock as $key => $stock)
            <tr>
                <td>{{ $key + 1}}</td>
                <td>{{ $stock->product->name }}</td>
                <td>{{ $stock->quantity }}</td>
                <td>
                    @if ($stock->type instanceof \App\Casts\ProductStockType)
                        {{ $stock->type->label() }}
                    @else
                        {{ \App\Casts\ProductStockType::from($stock->type)->label() }}
                    @endif
                </td>
                <td>{{ $stock->created_at }}</td>
                <td>
{{--                    <a href="{{ route('product-stock.show', $stock->id) }}" class="btn btn-info btn-sm">Ko‘rish</a>--}}
                    <a href="#" class="btn btn-warning btn-sm" wire:click="edit({{ $stock->id }})">Tahrirlash</a>
                    <button wire:click="delete({{ $stock->id }})" class="btn btn-danger btn-sm">O‘chirish</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="mt-2">
{{--        {{ $products->links() }}--}}
    </div>
</div>
