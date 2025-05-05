<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditLivewire extends Component
{
    use WithFileUploads;

    public $product_id;
    public $name, $price, $sell_price, $extra_price, $discount, $code, $category_id;
    public $image;
    public $current_image;
    public $company_id;

    protected $listeners = [
        'openEditProductModal' => 'listenFromIndex',
    ];
    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|min:0',
        'sell_price' => 'required|min:0',
        'discount' => 'nullable|numeric|min:0|max:100',
        'code' => 'required|numeric|digits_between:4,5',
        'category_id' => 'required|exists:product_categories,id',
        'image' => 'nullable|image|max:4096',
    ];
    protected $messages = [
        'name.required' => 'Nomni kiritish majburiy.',
        'name.string' => 'Nom matn bo\'lishi kerak.',
        'name.max' => 'Nom 255 ta belgidan oshmasligi kerak.',
        'price.required' => 'Narxni kiritish majburiy.',
        'price.min' => 'Narx 0 dan kam bo`lmasligi kerak.',
        'sell_price.required' => 'Sotuv narxini kiritish majburiy.',
        'sell_price.min' => 'Sotuv narxi 0 dan kam bo`lmasligi kerak.',
        'discount.numeric' => 'Chegirma son bo`lishi kerak.',
        'discount.min' => 'Chegirma 0 dan kam bo`lmasligi kerak.',
        'discount.max' => 'Chegirma 100 dan oshmasligi kerak.',
        'code.numeric' => 'Kod raqam bo`lishi kerak.',
        'code.max' => 'Kod 5 ta raqamdan oshmasligi kerak.',
        'code.min' => 'Kod 4 ta raqamdan kam bo`lmasligi kerak.',
        'category_id.required' => 'Kategoriyani tanlash majburiy.',
        'category_id.exists' => 'Tanlangan kategoriya mavjud emas.',
        'image.image' => 'Fayl rasm bo`lishi kerak.',
        'image.max' => 'Rasm hajmi 4096 KB dan oshmasligi kerak.',
    ];

    public function listenFromIndex($product_id)
    {
        $this->product_id = $product_id;
        $this->company_id = auth()->user()->getCompany()->id;
        $this->loadProduct();
        $this->dispatch('openEditProductModal');
    }

    protected function loadProduct()
    {
        $product = Product::query()->findOrFail($this->product_id);

        $this->name = $product->name;
        $this->price = $product->price;
        $this->sell_price = $product->sell_price;
        $this->discount = $product->discount;
        $this->code = $product->code;
        $this->category_id = $product->category_id;
        $this->current_image = $product->image;
    }

    public function update()
    {
        $price = (int)preg_replace('/\D/', '', $this->price);
        $sell_price = (int)preg_replace('/\D/', '', $this->sell_price);

        $this->validate();

        $product = Product::findOrFail($this->product_id);

        $imagePath = $this->current_image;

        if ($this->image) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $this->image->store('products', 'public');
        }

        $product->update([
            'name' => $this->name,
            'price' => $price,
            'sell_price' => $sell_price,
            'extra_price' => $sell_price - $price,
            'discount' => $this->discount,
            'code' => (int)$this->code,
            'category_id' => $this->category_id,
            'image' => $imagePath,
        ]);

        session()->flash('success', 'Mahsulot muvaffaqiyatli yangilandi.');
        $this->dispatch('productUpdated');
    }


    public function render()
    {
        return view('livewire.admin.products.edit-livewire', [
            'categories' => \App\Models\ProductCategory::all(),
        ]);
    }
}
