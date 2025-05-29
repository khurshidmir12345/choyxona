<?php

namespace App\Livewire\Admin\ProductStock;

use App\Models\Product;
use App\Models\ProductStock;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    public $editProductId = null;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['productStockCreated' => 'render', 'closeEditModal'];


    public function edit($product_id)
    {
       $this->editProductId = $product_id;
    }

    public function closeEditModal()
    {
        $this->editProductId = null;
    }

    public function delete($stock_id)
    {
        $productStock = ProductStock::findOrFail($stock_id);
        $productStock->delete();
        $this->render();
    }

    public function render()
    {

        $companyId = auth()->user()->getCompany()->id;
        $productStock = ProductStock::where('company_id', $companyId)->orderByDesc("id")->paginate(15);
        $products = Product::where('company_id', $companyId)->orderByDesc("id")->get();


        return view('livewire.admin.product-stock.index-livewire', compact('productStock', 'products'));
    }
}
