<?php

namespace App\Livewire\Mobile;

use App\Livewire\Admin\Orders\OrderInCafeLivewire;
use App\Models\Order;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

/**
 * Zal POS — telefon uchun ko'rinish (Telegram mini ilova va mobil brauzer).
 *
 * Tezlik uchun savat, filtrlar va qidiruv brauzerda ishlaydi
 * (public/js/mobile-hall.js): stol ochilganda menyu bir marta JSON bo'lib
 * keladi, "+"/"−" serverga bormaydi. Server faqat saqlash, hisobni yopish,
 * stolni bo'shatish va mijoz qidiruvida ishlaydi; narxlar saqlashda
 * bazadan qayta olinadi (repriceCart).
 */
#[Layout('layouts.mobile')]
class HallLivewire extends OrderInCafeLivewire
{
    /** places | orders | profile */
    public string $tab = 'places';

    public bool $showCustomer = false;

    /** Oxirgi yopilgan hisob — chekni ochish uchun. */
    public ?int $lastReceiptId = null;

    public function mount(?int $place_id = null): void
    {
        if ($place_id) {
            $this->openTable($place_id);
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['places', 'orders', 'profile'], true)) {
            $this->tab = $tab;
        }
    }

    /**
     * Stol ochilganda brauzerga beriladigan menyu: faqat kerakli ustunlar,
     * bitta so'rov. Filtr va qidiruv brauzerda.
     *
     * @return list<array{id:int,name:string,price:int,discount:int,stock:int,image:?string,code:string,category_id:?int}>
     */
    #[Computed]
    public function catalog(): array
    {
        return Product::query()
            ->select(Product::CARD_COLUMNS)
            ->forCompany($this->companyId())
            ->orderBy('name')
            ->limit(500)
            ->get()
            ->map(fn (Product $p) => [
                'id' => (int) $p->id,
                'name' => $p->name,
                'price' => (int) $p->sell_price,
                'discount' => (int) $p->discount,
                'stock' => (int) ($p->current_stock ?? 0),
                'image' => $p->imageUrl(),
                'code' => $p->formattedCode(),
                'category_id' => $p->category_id ? (int) $p->category_id : null,
            ])
            ->all();
    }

    /** Ochiq hisoblar: qaysi stolda, necha xil mahsulot, qancha, kim ochgan. */
    #[Computed]
    public function openOrders(): Collection
    {
        return Order::query()
            ->select(['id', 'place_id', 'user_id', 'customer_id', 'amount', 'created_at'])
            ->forCompany($this->companyId())
            ->opened()
            ->whereNotNull('place_id')
            ->with(['place:id,name', 'user:id,name', 'customer:id,name'])
            ->withCount('orderDetails')
            ->latest('id')
            ->get();
    }

    /** Bugun yopilgan hisoblar (oxirgi 30 tasi). Indeks: company, status, updated_at. */
    #[Computed]
    public function todayClosed(): Collection
    {
        return Order::query()
            ->select(['id', 'place_id', 'user_id', 'total_amount', 'updated_at'])
            ->forCompany($this->companyId())
            ->done()
            ->whereNotNull('place_id')
            ->whereBetween('updated_at', [today(), today()->endOfDay()])
            ->with(['place:id,name', 'user:id,name'])
            ->latest('updated_at')
            ->limit(30)
            ->get();
    }

    public function openTable(int $placeId): void
    {
        parent::openTable($placeId);
        $this->showCustomer = false;
        $this->forgetComputed();
    }

    public function closePanel(): void
    {
        parent::closePanel();
        $this->showCustomer = false;
        $this->forgetComputed();
    }

    public function toggleCustomer(): void
    {
        $this->showCustomer = ! $this->showCustomer;
    }

    public function selectCustomer(int $id): void
    {
        parent::selectCustomer($id);
        $this->showCustomer = false;
    }

    /** Saqlab, stollar taxtasiga qaytadi — ofitsant keyingi stolga o'tadi. */
    public function saveOrder(OrderService $orders): void
    {
        parent::saveOrder($orders);

        if ($this->activeOrderId) {
            $this->closePanel();
        }
    }

    /** Hisobni yopadi va shu yerda qoladi (chek alohida havola bilan). */
    public function closeOrder(OrderService $orders)
    {
        $order = $this->ensureOrder($orders);

        if (! $order) {
            return null;
        }

        $placeName = (string) $this->activePlace?->name;
        $total = $this->total;

        $orders->attachCustomer($order, $this->customerId);
        $orders->closeTableOrder($order, array_values($this->cart), $this->discount, (int) auth()->id());

        $this->closePanel();
        $this->lastReceiptId = $order->id;

        $this->dispatch('toast', type: 'success', message: "{$placeName}: hisob yopildi — ".number_format($total, 0, ',', ' ')." so'm");

        return null;
    }

    public function clearTable(OrderService $orders): void
    {
        parent::clearTable($orders);
        $this->showCustomer = false;
        $this->forgetComputed();
    }

    /**
     * Hisoblangan xususiyatlar so'rov ichida keshlanadi. Stol ochilgan/yopilgan
     * bo'lsa, o'sha so'rovda chizilayotgan ekran eski qiymatni ko'rmasin.
     */
    private function forgetComputed(): void
    {
        unset($this->activePlace, $this->places, $this->openOrders, $this->todayClosed, $this->catalog, $this->subtotal, $this->total, $this->change);
    }

    public function render()
    {
        return view('livewire.mobile.hall');
    }
}
