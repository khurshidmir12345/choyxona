<?php

namespace App\Livewire\Admin\ProductStock;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditLivewire extends Component
{
    use WithFileUploads;

    public $stock_id;
    public $product_name;
    public $quantity;

    protected $listeners = [
        'openEditProductModal' => 'listenFromIndex',
    ];
    protected $rules = [
        'product_name' => 'required|string|max:255',
        'quantity' => 'required|min:0',
    ];
    protected $messages = [
        'product_name.required' => 'Nomni kiritish majburiy.',
        'product_name.string' => 'Nom matn bo\'lishi kerak.',
        'product_name.max' => 'Nom 255 ta belgidan oshmasligi kerak.',
        'quantity.numeric' => 'Miqdor raqam bo‘lishi kerak.',
        'quantity.min' => 'Miqdor kamida 1 bo‘lishi kerak.',
        'quantity.required' => 'Miqdor maydoni to‘ldirilishi shart.',
    ];

    public function listenFromIndex($product_id)
    {
        $this->stock_id = $product_id;
        $this->loadProduct();
        $this->dispatch('openEditProductModal');
    }

    protected function loadProduct()
    {
        $productStock = ProductStock::query()->findOrFail($this->stock_id);

        $this->name = $productStock->name;
        $this->quantity = $productStock->quantity;
    }

    public function update()
    {
        $this->validate();

        $product = ProductStock::findOrFail($this->stock_id);


        $product->update([
            'name' => $this->product_name,
            'quantity' => $this->quantity
        ]);

        session()->flash('success', 'Mahsulot muvaffaqiyatli yangilandi.');
        $this->dispatch('productStockUpdated');
    }


    public function render()
    {
        return view('livewire.admin.product-stock.edit-livewire');
    }
}
