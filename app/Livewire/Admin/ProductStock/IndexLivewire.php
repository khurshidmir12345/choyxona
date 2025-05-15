<?php

namespace App\Livewire\Admin\ProductStock;

use App\Models\Product;
use App\Models\ProductStock;
use Livewire\Component;

class IndexLivewire extends Component
{

    protected $listeners = ['productStockCreated' => 'render'];


    public function edit($product_id)
    {
        $this->dispatch('openEditProductModal', product_id: $product_id);
    }

    public function render()
    {

        $companyId = auth()->user()->getCompany()->id;
        $productStock = ProductStock::where('company_id', $companyId)->orderByDesc("id")->get();
        $products = Product::where('company_id', $companyId)->get();


        return view('livewire.admin.product-stock.index-livewire', compact('productStock', 'products'));
    }
}
