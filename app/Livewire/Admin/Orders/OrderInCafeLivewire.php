<?php

namespace App\Livewire\Admin\Orders;

use App\Casts\OrderStatusEnum;
use App\Casts\PlaceStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Order;
use App\Models\Place;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Zal POS: stollar taxtasi + tanlangan stol uchun buyurtma ekrani.
 *
 * Bu yerda birorta Eloquent model public xususiyat sifatida saqlanmaydi.
 * Livewire har bir bosishda public xususiyatlarni to'liq serializatsiya
 * qiladi — ilgari 100 ta mahsulot va ochiq buyurtma har bosishda
 * brauzerga borib kelardi.
 */
class OrderInCafeLivewire extends Component
{
    use WithCompany;

    public ?int $placeId = null;

    public ?int $activeOrderId = null;

    /** @var array<int, array{product_id:int,name:string,price:int,discount:int,quantity:int}> */
    public array $cart = [];

    public ?int $selectedCategory = null;

    public string $search = '';

    public int $discount = 0;

    public $givenAmount = null;

    public function mount(?int $place_id = null): void
    {
        if ($place_id) {
            $this->openTable($place_id);
        }
    }

    // ---------------------------------------------------------------- stollar

    #[Computed]
    public function places(): Collection
    {
        $places = Place::query()
            ->select(['id', 'name', 'status', 'capacity'])
            ->forCompany($this->companyId())
            ->orderBy('name')
            ->get();

        // Band stollar uchun joriy hisob — bitta so'rovda, stol boshiga emas.
        $openOrders = Order::query()
            ->select(['id', 'place_id', 'amount', 'created_at'])
            ->forCompany($this->companyId())
            ->opened()
            ->whereNotNull('place_id')
            ->get()
            ->keyBy('place_id');

        return $places->map(function (Place $place) use ($openOrders) {
            $order = $openOrders->get($place->id);
            $place->setAttribute('open_order_amount', $order?->amount);
            $place->setAttribute('open_order_since', $order?->created_at);

            return $place;
        });
    }

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

    #[Computed]
    public function activePlace(): ?Place
    {
        if (! $this->placeId) {
            return null;
        }

        return Place::query()
            ->select(['id', 'name', 'status', 'capacity'])
            ->forCompany($this->companyId())
            ->find($this->placeId);
    }

    // ---------------------------------------------------------------- amallar

    /**
     * Stolni ochadi. Yangi stol uchun buyurtma darhol yaratilmaydi —
     * u birinchi saqlashda paydo bo'ladi, shunda tasodifiy bosishdan
     * bo'sh buyurtmalar to'planmaydi.
     */
    public function openTable(int $placeId): void
    {
        $place = Place::query()
            ->select(['id', 'name', 'status', 'capacity'])
            ->forCompany($this->companyId())
            ->find($placeId);

        if (! $place) {
            return;
        }

        $this->resetOrderState();
        $this->placeId = $place->id;

        $order = Order::query()
            ->select(['id', 'discount'])
            ->forCompany($this->companyId())
            ->where('place_id', $place->id)
            ->opened()
            ->latest('id')
            ->first();

        if ($order) {
            $this->activeOrderId = $order->id;
            $this->discount = (int) $order->discount;
            $this->cart = $this->loadCart($order->id);
        }
    }

    public function closePanel(): void
    {
        $this->resetOrderState();
        $this->placeId = null;
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

    public function updatedDiscount(): void
    {
        $this->discount = max(0, min(100, (int) $this->discount));
    }

    public function saveOrder(OrderService $orders): void
    {
        $order = $this->ensureOrder($orders);

        if (! $order) {
            return;
        }

        $orders->syncItems($order, array_values($this->cart), $this->discount, (int) auth()->id());
        $this->dispatch('toast', type: 'success', message: 'Buyurtma saqlandi.');
    }

    public function closeOrder(OrderService $orders)
    {
        $order = $this->ensureOrder($orders);

        if (! $order) {
            return null;
        }

        $orders->closeTableOrder($order, array_values($this->cart), $this->discount, (int) auth()->id());

        $this->resetOrderState();
        $this->placeId = null;

        return redirect()->route('admin.orders.print', $order->id);
    }

    /** Stolni bo'shatadi: ochiq hisob bekor qilinadi. */
    public function clearTable(OrderService $orders): void
    {
        if ($this->activeOrderId) {
            $order = Order::query()
                ->forCompany($this->companyId())
                ->find($this->activeOrderId);

            if ($order) {
                $orders->cancelTableOrder($order);
            }
        } elseif ($this->placeId) {
            Place::query()
                ->forCompany($this->companyId())
                ->whereKey($this->placeId)
                ->update(['status' => PlaceStatusEnum::Empty->value]);
        }

        $this->resetOrderState();
        $this->placeId = null;
        $this->dispatch('toast', type: 'success', message: 'Stol bo\'shatildi.');
    }

    // ---------------------------------------------------------------- summalar

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
        return app(OrderService::class)->applyDiscount($this->subtotal, $this->discount);
    }

    #[Computed]
    public function change(): int
    {
        return max(0, (int) $this->givenAmount - $this->total);
    }

    // ---------------------------------------------------------------- ichki

    /** Ochiq buyurtmani topadi, bo'lmasa shu payt yaratadi. */
    private function ensureOrder(OrderService $orders): ?Order
    {
        if ($this->cart === [] || ! $this->placeId) {
            $this->dispatch('toast', type: 'error', message: 'Avval mahsulot tanlang.');

            return null;
        }

        $order = $this->activeOrderId
            ? Order::query()->forCompany($this->companyId())->find($this->activeOrderId)
            : null;

        if ($order) {
            return $order;
        }

        $place = $this->activePlace;

        if (! $place) {
            return null;
        }

        $order = $orders->openTableOrder($place, (int) $this->companyId(), (int) auth()->id());
        $this->activeOrderId = $order->id;
        unset($this->activePlace, $this->places);

        return $order;
    }

    /** @return array<int, array<string, mixed>> */
    private function loadCart(int $orderId): array
    {
        return Order::query()
            ->findOrFail($orderId)
            ->orderDetails()
            ->select(['order_details.product_id', 'order_details.price', 'order_details.discount', 'order_details.quantity'])
            ->with(['product:id,name'])
            ->get()
            ->mapWithKeys(fn ($detail) => [
                (int) $detail->product_id => [
                    'product_id' => (int) $detail->product_id,
                    'name' => $detail->product?->name ?? 'Nomsiz',
                    'price' => (int) $detail->price,
                    'discount' => (int) $detail->discount,
                    'quantity' => (int) $detail->quantity,
                ],
            ])
            ->all();
    }

    private function resetOrderState(): void
    {
        $this->activeOrderId = null;
        $this->cart = [];
        $this->discount = 0;
        $this->givenAmount = null;
        $this->search = '';
        $this->selectedCategory = null;
    }

    public function render()
    {
        return view('livewire.admin.orders.order-in-cafe-livewire');
    }
}
