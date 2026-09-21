<?php

namespace App\Livewire\Mobile;

use App\Livewire\Admin\Orders\OrderInCafeLivewire;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;

/**
 * Zal POS — telefon uchun ko'rinish (Telegram mini ilova va mobil brauzer).
 *
 * Mantiq kompyuter versiyasi bilan bir xil (OrderInCafeLivewire), faqat
 * ekran boshqacha: pastki yorliqlar (stollar / buyurtmalar / profil),
 * stol filtrlari, mahsulot ro'yxati va pastdan chiqadigan savat.
 */
#[Layout('layouts.mobile')]
class HallLivewire extends OrderInCafeLivewire
{
    /** places | orders | profile */
    public string $tab = 'places';

    /** all | free | busy */
    public string $placeFilter = 'all';

    public string $placeSearch = '';

    public bool $onlyCart = false;

    public bool $showCart = false;

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

    public function setPlaceFilter(string $filter): void
    {
        if (in_array($filter, ['all', 'free', 'busy'], true)) {
            $this->placeFilter = $filter;
        }
    }

    #[Computed]
    public function filteredPlaces(): Collection
    {
        $term = mb_strtolower(trim($this->placeSearch));

        return $this->places
            ->filter(fn ($place) => match ($this->placeFilter) {
                'free' => ! $place->isBusy(),
                'busy' => $place->isBusy(),
                default => true,
            })
            ->filter(fn ($place) => $term === '' || str_contains(mb_strtolower($place->name), $term))
            ->values();
    }

    /** Ochiq hisoblar: qaysi stolda, necha xil mahsulot, qancha, kim ochgan. */
    #[Computed]
    public function openOrders(): Collection
    {
        return Order::query()
            ->select(['id', 'place_id', 'user_id', 'customer_id', 'amount', 'total_amount', 'discount', 'created_at'])
            ->forCompany($this->companyId())
            ->opened()
            ->whereNotNull('place_id')
            ->with(['place:id,name', 'user:id,name', 'customer:id,name'])
            ->withCount('orderDetails')
            ->latest('id')
            ->get();
    }

    /** Bugun yopilgan hisoblar (oxirgi 30 tasi). */
    #[Computed]
    public function todayClosed(): Collection
    {
        return Order::query()
            ->select(['id', 'place_id', 'user_id', 'total_amount', 'updated_at'])
            ->forCompany($this->companyId())
            ->done()
            ->whereNotNull('place_id')
            ->whereDate('updated_at', today())
            ->with(['place:id,name', 'user:id,name'])
            ->latest('updated_at')
            ->limit(30)
            ->get();
    }

    /** Mahsulotlar: kategoriya + qidiruv (ota klass) + "faqat savatdagilar". */
    #[Computed]
    public function mobileProducts(): Collection
    {
        $products = $this->products;

        if (! $this->onlyCart) {
            return $products;
        }

        return $products->filter(fn ($product) => isset($this->cart[$product->id]))->values();
    }

    public function openTable(int $placeId): void
    {
        parent::openTable($placeId);
        $this->showCart = false;
        $this->showCustomer = false;
        $this->onlyCart = false;
        $this->forgetComputed();
    }

    public function closePanel(): void
    {
        parent::closePanel();
        $this->showCart = false;
        $this->showCustomer = false;
        $this->onlyCart = false;
        $this->forgetComputed();
    }

    /**
     * Hisoblangan xususiyatlar so'rov ichida keshlanadi. Stol ochilgan/yopilgan
     * bo'lsa, o'sha so'rovda chizilayotgan ekran eski qiymatni ko'rmasin.
     */
    private function forgetComputed(): void
    {
        unset($this->activePlace, $this->places, $this->filteredPlaces, $this->openOrders, $this->todayClosed, $this->mobileProducts, $this->products);
    }

    public function toggleCart(): void
    {
        $this->showCart = ! $this->showCart;
        $this->showCustomer = false;
    }

    public function toggleCustomer(): void
    {
        $this->showCustomer = ! $this->showCustomer;
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
        $this->showCart = false;
        $this->showCustomer = false;
    }

    public function render()
    {
        return view('livewire.mobile.hall');
    }
}
