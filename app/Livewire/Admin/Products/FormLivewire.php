<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\Concerns\WithCompany;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Mahsulot qo'shish va tahrirlash uchun bitta forma.
 * Ilgari bu ikkita deyarli bir xil komponent edi.
 */
class FormLivewire extends Component
{
    use WithFileUploads, WithCompany;

    public ?int $productId = null;

    public string $name = '';

    public $price = '';

    public $sell_price = '';

    public $discount = 0;

    public $code = '';

    public $category_id = '';

    public $current_stock = 0;

    public $image = null;

    public ?string $currentImage = null;

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;

        if (! $productId) {
            $this->code = $this->nextCode();

            return;
        }

        $product = Product::query()
            ->select(['id', 'name', 'price', 'sell_price', 'discount', 'code', 'category_id', 'current_stock', 'image'])
            ->forCompany($this->companyId())
            ->findOrFail($productId);

        $this->name = (string) $product->name;
        $this->price = $product->price;
        $this->sell_price = $product->sell_price;
        $this->discount = $product->discount ?? 0;
        $this->code = $product->code;
        $this->category_id = $product->category_id;
        $this->current_stock = $product->current_stock ?? 0;
        $this->currentImage = $product->imageUrl();
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::query()
            ->select(['id', 'name'])
            ->forCompany($this->companyId())
            ->orderBy('name')
            ->get();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'sell_price' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'code' => [
                'required', 'integer', 'min:1',
                // Kod kompaniya ichida takrorlanmasligi kerak.
                Rule::unique('products', 'code')
                    ->where('company_id', $this->companyId())
                    ->whereNull('deleted_at')
                    ->ignore($this->productId),
            ],
            'category_id' => [
                'required',
                Rule::exists('product_categories', 'id')->where('company_id', $this->companyId()),
            ],
            'current_stock' => ['nullable', 'integer'],
            'image' => ['nullable', 'image', 'max:4096'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Nomni kiriting.',
            'name.max' => 'Nom 255 ta belgidan oshmasin.',
            'price.required' => 'Tannarxni kiriting.',
            'price.integer' => 'Tannarx raqam bo\'lishi kerak.',
            'sell_price.required' => 'Sotuv narxini kiriting.',
            'sell_price.integer' => 'Sotuv narxi raqam bo\'lishi kerak.',
            'discount.max' => 'Chegirma 100 dan oshmasin.',
            'code.required' => 'Kodni kiriting.',
            'code.unique' => 'Bu kod boshqa mahsulotda ishlatilgan.',
            'category_id.required' => 'Kategoriyani tanlang.',
            'category_id.exists' => 'Bunday kategoriya yo\'q.',
            'image.image' => 'Fayl rasm bo\'lishi kerak.',
            'image.max' => 'Rasm 4 MB dan katta bo\'lmasin.',
        ];
    }

    /** Foydalanuvchi "1 200 000" deb yozsa ham to'g'ri tushunamiz. */
    public function updatedPrice($value): void
    {
        $this->price = $this->digits($value);
    }

    public function updatedSellPrice($value): void
    {
        $this->sell_price = $this->digits($value);
    }

    public function save(): void
    {
        $this->price = $this->digits($this->price);
        $this->sell_price = $this->digits($this->sell_price);

        $data = $this->validate();

        $attributes = [
            'name' => $data['name'],
            'price' => (int) $data['price'],
            'sell_price' => (int) $data['sell_price'],
            'extra_price' => (int) $data['sell_price'] - (int) $data['price'],
            'discount' => (int) ($data['discount'] ?? 0),
            'code' => (int) $data['code'],
            'category_id' => (int) $data['category_id'],
            'company_id' => $this->companyId(),
        ];

        $product = $this->productId
            ? Product::query()->forCompany($this->companyId())->findOrFail($this->productId)
            : new Product(['current_stock' => 0]);

        if ($this->productId) {
            $attributes['current_stock'] = (int) $this->current_stock;
        }

        if ($this->image) {
            // Bazada nisbiy yo'l saqlanadi, shunda domen o'zgarsa ham rasm ishlaydi.
            $path = $this->image->store('products', 'public');

            if ($product->exists && filled($product->getRawOriginal('image'))) {
                $this->deleteStoredImage($product->getRawOriginal('image'));
            }

            $attributes['image'] = $path;
        }

        $product->fill($attributes)->save();

        $this->dispatch('productSaved');
        $this->dispatch('closeProductForm');
    }

    private function digits($value): string
    {
        return (string) preg_replace('/\D/', '', (string) $value);
    }

    private function nextCode(): int
    {
        $last = Product::query()
            ->withTrashed()
            ->forCompany($this->companyId())
            ->max('code');

        return $last ? ((int) $last) + 1 : 10001;
    }

    private function deleteStoredImage(string $stored): void
    {
        $path = $stored;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = (string) parse_url($path, PHP_URL_PATH);
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        if ($path !== '' && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function close(): void
    {
        $this->dispatch('closeProductForm');
    }

    public function render()
    {
        return view('livewire.admin.products.form-livewire');
    }
}
