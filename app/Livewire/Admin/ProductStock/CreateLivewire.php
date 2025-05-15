<?php

namespace App\Livewire\Admin\ProductStock;

use App\Models\Product;
use App\Models\ProductStock;
use Livewire\Component;

class CreateLivewire extends Component
{
    public $product_id, $quantity, $status;

    protected $rules = [
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
        'status' => 'required',
    ];
    protected $messages = [
        'product_id.required' => 'Mahsulot tanlash majburiy',
        'quantity.required' => 'Mahsulot sonini kiriting',
        'status.required' => 'Mahsulot Holatini kiritilmadi',
    ];


    public function save()
    {
        $companyId = auth()->user()->getCompany()->id;
        $this->validate();

//        dd($this->status);

        ProductStock::create([
            'company_id' => $companyId,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'type' => $this->status,
        ]);

        session()->flash('success', 'Mahsulot qo‘shildi.');
        $this->reset();
        $this->dispatch('productStockCreated');
        $this->dispatch('closeModal');
    }


    public function render()
    {
        $companyId = auth()->user()->getCompany()->id;
        $products = Product::where('company_id', $companyId)->get();
        return view('livewire.admin.product-stock.create-livewire' , compact('products'));
    }
}
