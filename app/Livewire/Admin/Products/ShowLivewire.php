<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;

class ShowLivewire extends Component
{
    public $product;

    public $company_id;

    public function mount($product_id)
    {
        $this->product = Product::query()->with('category')->findOrFail($product_id);
        $this->company_id = auth()->user()->getCompany()->id;
    }

    public function closeModal()
    {
        $this->dispatch('closeShowModal');
    }

    public function render()
    {
        return view('livewire.admin.products.show-livewire');
    }
}
