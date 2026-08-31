<?php

namespace App\Livewire\Admin\Categories;

use App\Livewire\Concerns\WithCompany;
use App\Models\ProductCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    public string $search = '';

    public bool $showForm = false;

    public ?int $categoryId = null;

    public string $name = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createCategory(): void
    {
        $this->reset(['categoryId', 'name']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = ProductCategory::query()
            ->select(['id', 'name'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $category) {
            return;
        }

        $this->categoryId = $category->id;
        $this->name = (string) $category->name;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('product_categories', 'name')
                    ->where('company_id', $this->companyId())
                    ->whereNull('deleted_at')
                    ->ignore($this->categoryId),
            ],
        ], [
            'name.required' => 'Kategoriya nomini kiriting.',
            'name.unique' => 'Bunday kategoriya allaqachon bor.',
        ]);

        if ($this->categoryId) {
            ProductCategory::query()
                ->forCompany($this->companyId())
                ->whereKey($this->categoryId)
                ->update(['name' => $this->name]);
        } else {
            ProductCategory::create([
                'name' => $this->name,
                'company_id' => $this->companyId(),
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Kategoriya saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['categoryId', 'name']);
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $category = ProductCategory::query()
            ->forCompany($this->companyId())
            ->withCount('products')
            ->find($id);

        if (! $category) {
            return;
        }

        if ($category->products_count > 0) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: "Bu kategoriyada {$category->products_count} ta mahsulot bor. Avval ularni ko'chiring.",
            );

            return;
        }

        $category->delete();
        $this->dispatch('toast', type: 'success', message: 'Kategoriya o\'chirildi.');
    }

    public function render()
    {
        $categories = ProductCategory::query()
            ->select(['id', 'name', 'created_at'])
            ->forCompany($this->companyId())
            ->withCount('products')
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.categories.index-livewire', compact('categories'));
    }
}
