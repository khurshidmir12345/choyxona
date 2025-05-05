<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    protected $listeners = ['productCreated'];

    public function productCreated()
    {
        $this->render();
        $this->dispatch('closeModal');
    }

    public function edit($product_id)
    {
        $this->dispatch('openEditProductModal', product_id: $product_id);
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
