<?php

namespace App\Livewire\Admin\Products;

use App\Casts\ProductStockType;
use App\Livewire\Concerns\WithCompany;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Mahsulot qo'shish va tahrirlash uchun bitta forma.
 *
 * Qoldiq bu yerda "shunchaki" o'zgartirilmaydi: boshlang'ich qoldiq ham,
 * tahrirlashdagi tuzatish ham kirim/chiqim jurnaliga kim va nima uchun
 * qilgani bilan yoziladi. Skaner kodi (MXS-10001) id dan avtomatik beriladi.
 */
class FormLivewire extends Component
{
    use WithFileUploads, WithCompany;

    public ?int $productId = null;

    public string $name = '';

    public $price = '';

    public $sell_price = '';

    public $discount = 0;

    /** Faqat ko'rsatish uchun — qo'lda o'zgartirilmaydi. */
    public ?string $code = null;

    public $category_id = '';

    /** Yangi mahsulot uchun boshlang'ich qoldiq. */
    public $initial_stock = 0;

    /** Tahrirlashda: joriy qoldiq va uni o'zgartirish sababi. */
    public $current_stock = 0;

    public int $originalStock = 0;

    public string $stock_note = '';

    public $image = null;

    public ?string $currentImage = null;

    /** Modal ichidan yangi kategoriya yaratish. */
    public bool $showNewCategory = false;

    public string $newCategoryName = '';

    public function mount(?int $productId = null): void
    {
        $this->productId = $productId;

        if (! $productId) {
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
        $this->code = $product->formattedCode();
        $this->category_id = $product->category_id;
        $this->current_stock = (int) ($product->current_stock ?? 0);
        $this->originalStock = (int) ($product->current_stock ?? 0);
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

    /** Tahrirlashda qoldiq o'zgartirilganmi. */
    #[Computed]
    public function stockChanged(): bool
    {
        return $this->productId !== null && (int) $this->current_stock !== $this->originalStock;
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'integer', 'min:0'],
            'sell_price' => ['required', 'integer', 'min:0'],
            'discount' => ['nullable', 'integer', 'min:0', 'max:100'],
            'category_id' => [
                'required',
                Rule::exists('product_categories', 'id')->where('company_id', $this->companyId()),
            ],
            'initial_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'current_stock' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'stock_note' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:10240'],
        ];
    }

    protected function messages(): array
    {
        return [
            'image.uploaded' => 'Rasm yuklanmadi. Kichikroq rasm tanlab qayta urinib ko\'ring.',
            'name.required' => 'Nomni kiriting.',
            'name.max' => 'Nom 255 ta belgidan oshmasin.',
            'price.required' => 'Tannarxni kiriting.',
            'price.integer' => 'Tannarx raqam bo\'lishi kerak.',
            'sell_price.required' => 'Sotuv narxini kiriting.',
            'sell_price.integer' => 'Sotuv narxi raqam bo\'lishi kerak.',
            'discount.max' => 'Chegirma 100 dan oshmasin.',
            'category_id.required' => 'Kategoriyani tanlang.',
            'category_id.exists' => 'Bunday kategoriya yo\'q.',
            'initial_stock.integer' => 'Qoldiq butun son bo\'lishi kerak.',
            'initial_stock.min' => 'Qoldiq manfiy bo\'lmaydi.',
            'current_stock.integer' => 'Qoldiq butun son bo\'lishi kerak.',
            'current_stock.min' => 'Qoldiq manfiy bo\'lmaydi.',
            'image.image' => 'Fayl rasm bo\'lishi kerak.',
            'image.max' => 'Rasm 10 MB dan katta bo\'lmasin.',
        ];
    }

    // ------------------------------------------------------- kategoriya

    public function startNewCategory(): void
    {
        $this->newCategoryName = '';
        $this->resetErrorBag('newCategoryName');
        $this->showNewCategory = true;
    }

    public function cancelNewCategory(): void
    {
        $this->newCategoryName = '';
        $this->resetErrorBag('newCategoryName');
        $this->showNewCategory = false;
    }

    /**
     * Kategoriya esdan chiqqan bo'lsa, modal yopilmasdan shu yerda yaratiladi.
     * Shunday nomli kategoriya bor bo'lsa, yangisi yaratilmaydi — o'sha tanlanadi.
     */
    public function createCategory(): void
    {
        $name = trim(preg_replace('/\s+/', ' ', $this->newCategoryName));

        if ($name === '') {
            $this->addError('newCategoryName', 'Kategoriya nomini yozing.');

            return;
        }

        if (mb_strlen($name) > 255) {
            $this->addError('newCategoryName', 'Nom juda uzun.');

            return;
        }

        $existing = ProductCategory::query()
            ->select(['id'])
            ->forCompany($this->companyId())
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        $category = $existing ?? ProductCategory::create([
            'name' => $name,
            'company_id' => $this->companyId(),
        ]);

        $this->category_id = $category->id;
        $this->resetErrorBag(['category_id', 'newCategoryName']);
        $this->cancelNewCategory();

        unset($this->categories);

        $this->dispatch('categorySaved');
        $this->dispatch(
            'toast',
            type: 'success',
            message: $existing ? "\"{$name}\" kategoriyasi allaqachon bor edi, u tanlandi." : "\"{$name}\" kategoriyasi qo'shildi.",
        );
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
            'category_id' => (int) $data['category_id'],
            'company_id' => $this->companyId(),
        ];

        $product = $this->productId
            ? Product::query()->forCompany($this->companyId())->findOrFail($this->productId)
            : new Product(['current_stock' => 0]);

        if ($this->image) {
            // Bazada nisbiy yo'l saqlanadi, shunda domen o'zgarsa ham rasm ishlaydi.
            $path = $this->image->store('products', 'public');

            if ($product->exists && filled($product->getRawOriginal('image'))) {
                $this->deleteStoredImage($product->getRawOriginal('image'));
            }

            $attributes['image'] = $path;
        }

        DB::transaction(function () use ($product, $attributes, $data) {
            if (! $product->exists) {
                $initial = (int) ($data['initial_stock'] ?? 0);
                $attributes['current_stock'] = $initial;

                $product->fill($attributes)->save();

                if ($initial > 0) {
                    $this->logMovement($product, ProductStockType::Add, $initial, 'Mahsulot yaratilganda boshlang\'ich qoldiq');
                }

                return;
            }

            $before = (int) $product->current_stock;
            $after = (int) ($data['current_stock'] ?? $before);
            $delta = $after - $before;

            if ($delta !== 0) {
                $attributes['current_stock'] = $after;

                $reason = trim((string) ($data['stock_note'] ?? ''));
                $note = "Tahrirlashda qo'lda o'zgartirildi: {$before} → {$after}".($reason !== '' ? " ({$reason})" : '');

                $this->logMovement(
                    $product,
                    $delta > 0 ? ProductStockType::Add : ProductStockType::Waste,
                    abs($delta),
                    $note,
                );
            }

            $product->fill($attributes)->save();
        });

        $this->dispatch('productSaved');
        $this->dispatch('closeProductForm');
    }

    private function logMovement(Product $product, ProductStockType $type, int $quantity, string $note): void
    {
        ProductStock::create([
            'company_id' => $this->companyId(),
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'quantity' => $quantity,
            'type' => $type,
            'note' => mb_substr($note, 0, 255),
        ]);
    }

    private function digits($value): string
    {
        return (string) preg_replace('/\D/', '', (string) $value);
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
