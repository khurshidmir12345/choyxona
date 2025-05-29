<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class CreateLivewire extends Component
{
    use WithFileUploads;

    public $name, $price, $sell_price, $extra_price, $discount, $code, $category_id;
    public $image;
    public $company_id; // mount orqali ulanadi

    public function mount()
    {
        $this->reset();
        $this->company_id = auth()->user()->getCompany()->id;
    }

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|min:0',
        'sell_price' => 'required|min:0',
        'discount' => 'nullable|numeric|min:0|max:100',
        'code' => 'required|numeric|digits_between:4,5',
        'category_id' => 'required|exists:product_categories,id',
        'image' => 'nullable|image|max:4096',
    ];
    public function generateCode()
    {
        $lastProduct = Product::where('company_id', $this->company_id)->orderBy('code', 'desc')->first();
        $code = $lastProduct ? $lastProduct->code + 1 : 1001;
        $this->code = str_pad($code, 5, '0', STR_PAD_LEFT);
    }

protected $messages = [
    'name.required' => 'Nomni kiritish majburiy.',
    'name.string' => 'Nom matn bo‘lishi kerak.',
    'name.max' => 'Nom 255 ta belgidan oshmasligi kerak.',
    'price.required' => 'Narxni kiritish majburiy.',
    'price.min' => 'Narx 0 dan kam bo‘lmasligi kerak.',
    'sell_price.required' => 'Sotuv narxini kiritish majburiy.',
    'sell_price.min' => 'Sotuv narxi 0 dan kam bo‘lmasligi kerak.',
    'discount.numeric' => 'Chegirma son bo‘lishi kerak.',
    'discount.min' => 'Chegirma 0 dan kam bo‘lmasligi kerak.',
    'discount.max' => 'Chegirma 100 dan oshmasligi kerak.',
    'code.numeric' => 'Kod raqam bo`lishi kerak.',
    'code.max' => 'Kod 5 ta raqamdan oshmasligi kerak.',
    'code.min' => 'Kod 4 ta raqamdan kam bo`lmasligi kerak.',
    'category_id.required' => 'Kategoriyani tanlash majburiy.',
    'category_id.exists' => 'Tanlangan kategoriya mavjud emas.',
    'image.image' => 'Fayl rasm bo‘lishi kerak.',
    'image.max' => 'Rasm hajmi 4096 KB dan oshmasligi kerak.',
];

    public function save()
    {
        $price = (int) preg_replace('/\D/', '', $this->price);
        $sell_price = (int) preg_replace('/\D/', '', $this->sell_price);
        $this->generateCode();
        $this->validate();
        if ($this->image) {
            $imagePath = $this->image->store('products', 'public');
            $imageUrl = asset('storage/' . $imagePath);
        }

        Product::create([
            'name' => $this->name,
            'price' => $price,
            'sell_price' => $sell_price,
            'extra_price' => $sell_price - $price,
            'discount' => $this->discount ?? 0,
            'code' => (int) $this->code,
            'category_id' => $this->category_id,
            'company_id' => $this->company_id,
            'image' => $imageUrl ?? null,
        ]);

        session()->flash('success', 'Mahsulot muvaffaqiyatli yaratildi.');
        $this->reset();
        $this->dispatch('productCreated');
    }

    public function render()
    {
        return view('livewire.admin.products.create-livewire',[
            'categories' => \App\Models\ProductCategory::all(),
        ]);
    }
}
