<?php

namespace App\Livewire\Admin\ProductStock;

use App\Casts\ProductStockType;
use App\Models\Product;
use App\Models\ProductStock;
use Livewire\Component;

class EditLivewire extends Component
{
    public $stock_id;
    public $product_id;
    public $type;
    public $quantity;

    public $products = [];

    protected $listeners = [
        'openEditProductModal' => 'listenFromIndex',
    ];

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'type' => 'required',
        'quantity' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'product_id.required' => 'Mahsulot tanlash majburiy.',
        'type.required' => 'Holat tanlash majburiy.',
        'quantity.required' => 'Miqdor maydoni to‘ldirilishi shart.',
        'quantity.numeric' => 'Miqdor raqam bo‘lishi kerak.',
        'quantity.min' => 'Miqdor kamida 0 bo‘lishi kerak.',
    ];

    public function mount($product_id)
    {
        $this->stock_id = $product_id;
        $this->products = Product::all();
        $this->loadProduct();
    }

    protected function loadProduct()
    {
        $productStock = ProductStock::with('product')->findOrFail($this->stock_id);

        $this->product_id = $productStock->product_id;
        $this->type = $productStock->type->value;
        $this->quantity = $productStock->quantity;
    }

    public function update()
    {
        $this->validate();

        $productStock = ProductStock::findOrFail($this->stock_id);

        $productStock->update([
            'product_id' => $this->product_id,
            'type' => $this->type,
            'quantity' => $this->quantity,
        ]);

        session()->flash('success', 'Mahsulot muvaffaqiyatli yangilandi.');
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->dispatch('closeEditModal');
    }

    public function render()
    {
        return view('livewire.admin.product-stock.edit-livewire', [
            'stockTypes' => ProductStockType::cases(),
        ]);
    }
}
