<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;

class ShowLivewire extends Component
{
    public function render()
    {
        $product = Product::query()->orderByDesc('id')->first();
        return view('livewire.admin.products.show-livewire' , compact('product'));
    }
}
