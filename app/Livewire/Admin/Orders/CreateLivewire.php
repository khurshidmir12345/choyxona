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
 *
 * Yorliqlar (tabs): bitta mijozning savati tugamasdan ikkinchisiga xizmat
 * ko'rsatish kerak bo'lsa, kassir yangi yorliq ochadi. Har yorliq — alohida
 * savat, mijoz, tur va chegirma. Holat sessiyada saqlanadi, sahifa
 * yangilansa ham yo'qolmaydi.
 */
class CreateLivewire extends Component
{
    use WithCompany, WithCustomerPicker;

    /** Faol bo'lmagan yorliqlarning holati: id => snapshot. */
    public array $tabs = [];

    public int $activeTab = 1;

    private int $nextTabId = 2;

    /** Bitta yorliqda saqlanadigan maydonlar. */
    private const TAB_FIELDS = ['orderType', 'cart', 'orderDiscount', 'givenAmount', 'customerId', 'deliveryAddress'];

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

    public function mount(): void
    {
        $saved = session()->get($this->tabsSessionKey());

        if (is_array($saved) && ! empty($saved['tabs'])) {
            $this->tabs = $saved['tabs'];
            $this->activeTab = (int) ($saved['active'] ?? array_key_first($this->tabs));

            if (! isset($this->tabs[$this->activeTab])) {
                $this->activeTab = (int) array_key_first($this->tabs);
            }

            $this->restoreTab($this->tabs[$this->activeTab]);
        } else {
            $this->tabs = [1 => $this->snapshot()];
            $this->activeTab = 1;
        }

        $this->nextTabId = max(array_keys($this->tabs)) + 1;
    }

    /** Har so'rovdan keyin: faol yorliq holati sessiyaga yoziladi. */
    public function dehydrate(): void
    {
        $this->tabs[$this->activeTab] = $this->snapshot();

        session()->put($this->tabsSessionKey(), [
            'tabs' => $this->tabs,
            'active' => $this->activeTab,
        ]);
    }

    // ------------------------------------------------------------ yorliqlar

    public function newTab(): void
    {
        $this->tabs[$this->activeTab] = $this->snapshot();

        $id = $this->nextFreeTabId();
        $this->resetTabState();
        $this->tabs[$id] = $this->snapshot();
        $this->activeTab = $id;
    }

    public function switchTab(int $id): void
    {
        if ($id === $this->activeTab || ! isset($this->tabs[$id])) {
            return;
        }

        $this->tabs[$this->activeTab] = $this->snapshot();
        $this->restoreTab($this->tabs[$id]);
        $this->activeTab = $id;
    }

    public function closeTab(int $id): void
    {
        if (! isset($this->tabs[$id])) {
            return;
        }

        unset($this->tabs[$id]);

        if ($id !== $this->activeTab) {
            return;
        }

        if ($this->tabs === []) {
            $this->resetTabState();
            $this->tabs[1] = $this->snapshot();
            $this->activeTab = 1;

            return;
        }

        $next = (int) array_key_first($this->tabs);
        $this->restoreTab($this->tabs[$next]);
        $this->activeTab = $next;
    }

    /** Yorliq nomi: mijoz tanlangan bo'lsa uning ismi, aks holda tartib raqami. */
    public function tabLabel(int $id): string
    {
        $snap = $id === $this->activeTab ? $this->snapshot() : ($this->tabs[$id] ?? []);

        if (! empty($snap['customerId'])) {
            $name = \App\Models\Customer::query()
                ->whereKey($snap['customerId'])
                ->forCompany($this->companyId())
                ->value('name');

            if ($name) {
                return $name;
            }
        }

        $position = array_search($id, array_keys($this->tabs), true);

        return 'Mijoz '.(($position === false ? count($this->tabs) : $position) + 1);
    }

    /** @return array<string, mixed> */
    private function snapshot(): array
    {
        $snap = [];

        foreach (self::TAB_FIELDS as $field) {
            $snap[$field] = $this->{$field};
        }

        return $snap;
    }

    private function restoreTab(array $snap): void
    {
        $this->resetTabState();

        foreach (self::TAB_FIELDS as $field) {
            if (array_key_exists($field, $snap)) {
                $this->{$field} = $snap[$field];
            }
        }

        $this->cart = is_array($this->cart) ? $this->cart : [];
        unset($this->selectedCustomer, $this->customerAddresses);
    }

    private function resetTabState(): void
    {
        $this->orderType = 'takeaway';
        $this->cart = [];
        $this->orderDiscount = 0;
        $this->givenAmount = null;
        $this->resetCustomerPicker();
    }

    private function nextFreeTabId(): int
    {
        $id = max($this->nextTabId, $this->tabs === [] ? 1 : max(array_keys($this->tabs)) + 1);
        $this->nextTabId = $id + 1;

        return $id;
    }

    private function tabsSessionKey(): string
    {
        return 'pos.quick.tabs.'.(int) auth()->id();
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

        // Yakunlangan yorliq yopiladi; boshqa mijozlar o'z yorliqlarida qoladi.
        $this->reset(['search', 'selectedCategory']);
        unset($this->tabs[$this->activeTab]);

        if ($this->tabs === []) {
            $this->resetTabState();
            $this->tabs[1] = $this->snapshot();
            $this->activeTab = 1;
        } else {
            $next = (int) array_key_first($this->tabs);
            $this->restoreTab($this->tabs[$next]);
            $this->activeTab = $next;
        }

        return redirect()->route('admin.orders.print', $order->id);
    }

    public function render()
    {
        return view('livewire.admin.orders.create-livewire');
    }
}
