<?php

namespace App\Livewire\Admin\Orders;

use App\Casts\OrderTypeEnum;
use App\Livewire\Concerns\WithCompany;
use App\Livewire\Concerns\WithCustomerPicker;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Yetkazib berish va olib ketish uchun tez sotuv ekrani.
 * Buyurtma bir urinishda ochiladi va yopiladi.
 */
class CreateLivewire extends Component
{
    use WithCompany, WithCustomerPicker;

    public const TYPES = [
        'takeaway' => 'Olib ketish',
        'delivery' => 'Yetkazib berish',
    ];

    /** Buyurtma turi kartalari: nom, izoh, ikonka — biznes turiga qarab. */
    public static function typeMeta(): array
    {
        $biz = \App\Support\Business::current();

        return [
            'takeaway' => [$biz->term('takeaway'), $biz->term('takeaway_hint'), $biz->term('takeaway_icon')],
            'delivery' => ['Yetkazib berish', 'Manzilga yetkaziladi', 'mdi-moped-outline'],
        ];
    }

    public string $orderType = 'takeaway';

    /** @var array<int, array{product_id:int,name:string,price:int,discount:int,quantity:int}> */
    public array $cart = [];

    public int $orderDiscount = 0;

    public $givenAmount = null;

    public string $search = '';

    public ?int $selectedCategory = null;

    #[Computed]
    public function categories(): Collection
    {
        return ProductCategory::query()
            ->select(['id', 'name'])
            ->forCompany($this->companyId())
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->select(Product::CARD_COLUMNS)
            ->forCompany($this->companyId())
            ->when($this->selectedCategory, fn ($q) => $q->where('category_id', $this->selectedCategory))
            ->search($this->search)
            ->orderBy('name')
            ->limit(200)
            ->get();
    }

    public function selectCategory(?int $categoryId = null): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function setOrderType(string $type): void
    {
        if (array_key_exists($type, self::TYPES)) {
            $this->orderType = $type;
        }
    }

    /** Skaner to'liq kodni (MXS-10001) yozsa, mahsulot darhol savatga tushadi. */
    public function updatedSearch(): void
    {
        $this->addByScannedCode();
    }

    private function addByScannedCode(): void
    {
        $term = trim($this->search);
        $code = Product::normalizeCode($term);

        if (! $code || strlen(preg_replace('/\D/', '', $term)) < 5) {
            return;
        }

        $product = Product::query()
            ->select(['id', 'name'])
            ->forCompany($this->companyId())
            ->where('code', $code)
            ->first();

        if (! $product) {
            return;
        }

        $this->addProduct($product->id);
        $this->search = '';
        $this->dispatch('toast', type: 'success', message: "{$product->name} savatga qo'shildi.");
    }

    public function addProduct(int $productId): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;

            return;
        }

        $product = Product::query()
            ->select(['id', 'name', 'sell_price', 'discount'])
            ->forCompany($this->companyId())
            ->find($productId);

        if (! $product) {
            return;
        }

        $this->cart[$productId] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (int) $product->sell_price,
            'discount' => (int) $product->discount,
            'quantity' => 1,
        ];
    }

    public function updateQuantity(int $productId, int $quantity): void
    {
        if (! isset($this->cart[$productId])) {
            return;
        }

        if ($quantity < 1) {
            $this->removeProduct($productId);

            return;
        }

        $this->cart[$productId]['quantity'] = $quantity;
    }

    public function removeProduct(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function updatedOrderDiscount(): void
    {
        $this->orderDiscount = max(0, min(100, (int) $this->orderDiscount));
    }

    #[Computed]
    public function subtotal(): int
    {
        $service = app(OrderService::class);

        return array_reduce(
            $this->cart,
            fn (int $carry, array $item) => $carry + $service->lineTotal($item),
            0
        );
    }

    #[Computed]
    public function total(): int
    {
        return app(OrderService::class)->applyDiscount($this->subtotal, $this->orderDiscount);
    }

    #[Computed]
    public function change(): int
    {
        return max(0, (int) $this->givenAmount - $this->total);
    }

    public function saveOrder(OrderService $orders)
    {
        if ($this->cart === []) {
            $this->dispatch('toast', type: 'error', message: 'Kamida bitta mahsulot tanlang.');

            return null;
        }

        $order = $orders->createDirectOrder(
            (int) $this->companyId(),
            (int) auth()->id(),
            OrderTypeEnum::from($this->orderType),
            array_values($this->cart),
            $this->orderDiscount,
            $this->customerId,
            trim($this->deliveryAddress) ?: null,
        );

        $this->reset(['cart', 'orderDiscount', 'givenAmount', 'search', 'selectedCategory']);
        $this->resetCustomerPicker();

        return redirect()->route('admin.orders.print', $order->id);
    }

    public function render()
    {
        return view('livewire.admin.orders.create-livewire');
    }
}
