<?php

namespace App\Livewire\Admin\ProductStock;

use App\Casts\ProductStockType;
use App\Livewire\Concerns\WithCompany;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Kirim / chiqim jurnali.
 *
 * Muhim: har bir yozuv products.current_stock ga ta'sir qiladi, shuning uchun
 * tahrirlash va o'chirish eski ta'sirni qaytarib, yangisini qo'llaydi.
 * Ilgari faqat qo'shish zaxirani o'zgartirardi — tahrir va o'chirishdan
 * keyin qoldiq haqiqatdan uzoqlashib ketardi.
 */
class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    /** Shablon Bootstrap 5 asosida — sahifalash ham shunga mos. */
    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $typeFilter = '';

    public bool $showForm = false;

    public ?int $stockId = null;

    public $product_id = '';

    public $quantity = '';

    public string $type = 'add';

    public string $note = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->select(['id', 'name', 'code', 'current_stock'])
            ->forCompany($this->companyId())
            ->orderBy('name')
            ->get();
    }

    public function createMovement(): void
    {
        $this->reset(['stockId', 'product_id', 'quantity', 'note']);
        $this->type = 'add';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $stock = ProductStock::query()
            ->select(['id', 'product_id', 'quantity', 'type', 'note'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $stock) {
            return;
        }

        $this->stockId = $stock->id;
        $this->product_id = $stock->product_id;
        $this->quantity = $stock->quantity;
        $this->type = $stock->type->value;
        $this->note = (string) $stock->note;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where('company_id', $this->companyId()),
            ],
            'quantity' => ['required', 'integer', 'min:1'],
            'type' => ['required', Rule::in(ProductStockType::values())],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'product_id.required' => 'Mahsulotni tanlang.',
            'product_id.exists' => 'Bunday mahsulot yo\'q.',
            'quantity.required' => 'Miqdorni kiriting.',
            'quantity.min' => 'Miqdor kamida 1 bo\'lishi kerak.',
            'type.required' => 'Turini tanlang.',
            'note.max' => 'Izoh 255 belgidan oshmasin.',
        ]);

        $type = ProductStockType::from($data['type']);
        $delta = $type === ProductStockType::Add ? (int) $data['quantity'] : -(int) $data['quantity'];

        $existing = $this->stockId
            ? ProductStock::query()->forCompany($this->companyId())->find($this->stockId)
            : null;

        if ($this->stockId && ! $existing) {
            return;
        }

        $previousDelta = $existing?->stockDelta() ?? 0;
        $previousProductId = $existing?->product_id;

        // Chiqim zaxiradan ko'p bo'lmasin.
        $available = (int) Product::query()
            ->forCompany($this->companyId())
            ->whereKey($data['product_id'])
            ->value('current_stock');

        if ($previousProductId === (int) $data['product_id']) {
            $available -= $previousDelta;
        }

        if ($available + $delta < 0) {
            $this->addError('quantity', "Zaxirada {$available} ta bor, chiqim shundan oshib ketdi.");

            return;
        }

        DB::transaction(function () use ($existing, $data, $type, $delta, $previousDelta, $previousProductId) {
            if ($existing && $previousProductId) {
                $this->applyStockDelta($previousProductId, -$previousDelta);
            }

            if ($existing) {
                // Tahrirlagan odam ham qayd etiladi — izohga qo'shib qo'yiladi.
                $editor = auth()->user()?->name;
                $note = trim((string) ($data['note'] ?? ''));

                $existing->update([
                    'product_id' => (int) $data['product_id'],
                    'quantity' => (int) $data['quantity'],
                    'type' => $type,
                    'note' => mb_substr(trim($note.($editor ? " [tahrirladi: {$editor}]" : '')), 0, 255) ?: null,
                ]);
            } else {
                ProductStock::create([
                    'company_id' => $this->companyId(),
                    'product_id' => (int) $data['product_id'],
                    'user_id' => auth()->id(),
                    'quantity' => (int) $data['quantity'],
                    'type' => $type,
                    'note' => trim((string) ($data['note'] ?? '')) ?: null,
                ]);
            }

            $this->applyStockDelta((int) $data['product_id'], $delta);
        });

        $this->closeForm();
        unset($this->products);
        $this->dispatch('toast', type: 'success', message: 'Harakat saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['stockId', 'product_id', 'quantity', 'note']);
        $this->type = 'add';
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $stock = ProductStock::query()
            ->select(['id', 'product_id', 'quantity', 'type'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $stock) {
            return;
        }

        DB::transaction(function () use ($stock) {
            $this->applyStockDelta((int) $stock->product_id, -$stock->stockDelta());
            $stock->delete();
        });

        unset($this->products);
        $this->dispatch('toast', type: 'success', message: 'Harakat o\'chirildi.');
    }

    private function applyStockDelta(int $productId, int $delta): void
    {
        if ($delta === 0) {
            return;
        }

        Product::query()
            ->whereKey($productId)
            ->update(['current_stock' => DB::raw('COALESCE(current_stock, 0) + '.$delta)]);
    }

    public function render()
    {
        $movements = ProductStock::query()
            ->select(['id', 'product_id', 'user_id', 'quantity', 'type', 'note', 'created_at'])
            ->forCompany($this->companyId())
            ->with(['product:id,name,code', 'user:id,name'])
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->when($this->search, fn ($q) => $q->whereHas(
                'product',
                fn ($p) => $p->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%')
            ))
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.product-stock.index-livewire', [
            'movements' => $movements,
            'stockTypes' => ProductStockType::cases(),
        ]);
    }
}
