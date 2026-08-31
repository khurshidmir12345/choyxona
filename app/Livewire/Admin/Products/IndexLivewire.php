<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\Concerns\WithCompany;
use App\Models\Product;
use App\Models\ProductCategory;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    /** Shablon Bootstrap 5 asosida — sahifalash ham shunga mos. */
    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public ?int $categoryFilter = null;

    public bool $showForm = false;

    public ?int $editProductId = null;

    protected $listeners = [
        'productSaved' => 'onProductSaved',
        'closeProductForm' => 'closeForm',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
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

    public function onProductSaved(): void
    {
        $this->dispatch('toast', type: 'success', message: 'Mahsulot saqlandi.');
    }

    public function create(): void
    {
        $this->editProductId = null;
        $this->showForm = true;
    }

    public function edit(int $productId): void
    {
        $this->editProductId = $productId;
        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editProductId = null;
    }

    /**
     * Mahsulot soft-delete qilinadi: qattiq o'chirish order_details'ni
     * kaskad bilan olib ketardi va savdo tarixini yo'q qilardi.
     */
    public function delete(int $productId): void
    {
        Product::query()
            ->forCompany($this->companyId())
            ->whereKey($productId)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Mahsulot o\'chirildi.');
    }

    public function render()
    {
        $products = Product::query()
            ->select(['id', 'name', 'code', 'image', 'price', 'sell_price', 'discount', 'current_stock', 'category_id'])
            ->forCompany($this->companyId())
            ->with(['category:id,name'])
            ->search($this->search)
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.products.index-livewire', compact('products'));
    }
}
