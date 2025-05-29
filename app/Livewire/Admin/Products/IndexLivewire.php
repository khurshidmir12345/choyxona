<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    public $editProductId = null;
    public  $showProductId = null;


    protected $paginationTheme = 'bootstrap';

    public $search = '';

    protected $listeners = ['productCreated', 'productUpdated', 'closeEditModal', 'closeShowModal'];

    public function productCreated()
    {
        $this->render();
        $this->dispatch('closeModal');
    }

    public function productUpdated()
    {
        $this->render();
        $this->editProductId = null;
    }

    public function edit($product_id)
    {
        $this->editProductId = $product_id;
    }

    public function show($product_id)
    {
        $this->showProductId = $product_id;
    }

    public function delete($id)
    {
        Product::query()->findOrFail($id)->delete();
        session()->flash('message', 'Mahsulot o‘chirildi.');
    }

    public function openCreateModal()
    {
        $this->dispatch('openAddProductModal');
    }

    public function closeEditModal()
    {
        $this->editProductId = null;
    }

    public function closeShowModal()
    {
        $this->showProductId = null;
    }


    public function render()
    {
        $companyId = auth()->user()->getCompany()->id;
        $products = Product::with('category', 'company')
            ->where('company_id', $companyId)
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%')
                    ->orWhere('sell_price', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('id')->paginate(15);

        return view('livewire.admin.products.index-livewire', compact('products'));
    }
}
