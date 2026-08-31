<?php

namespace App\Livewire\Admin\ExpenseCategories;

use App\Livewire\Concerns\WithCompany;
use App\Models\ExpenseCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    /** Shablon Bootstrap 5 asosida — sahifalash ham shunga mos. */
    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $showForm = false;

    public ?int $categoryId = null;

    public string $name = '';

    public string $description = '';

    public string $color = '#3b82f6';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createCategory(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = ExpenseCategory::query()
            ->select(['id', 'name', 'description', 'color'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $category) {
            return;
        }

        $this->categoryId = $category->id;
        $this->name = (string) $category->name;
        $this->description = (string) $category->description;
        $this->color = (string) ($category->color ?: '#3b82f6');
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('expense_categories', 'name')
                    ->where('company_id', $this->companyId())
                    ->whereNull('deleted_at')
                    ->ignore($this->categoryId),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'name.required' => 'Kategoriya nomini kiriting.',
            'name.unique' => 'Bunday kategoriya allaqachon bor.',
            'color.regex' => 'Rangni tanlang.',
        ]);

        if ($this->categoryId) {
            ExpenseCategory::query()
                ->forCompany($this->companyId())
                ->whereKey($this->categoryId)
                ->update($data);
        } else {
            ExpenseCategory::create($data + [
                'company_id' => $this->companyId(),
                'is_active' => true,
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Kategoriya saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function toggleStatus(int $id): void
    {
        $category = ExpenseCategory::query()
            ->select(['id', 'is_active'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $category) {
            return;
        }

        $category->update(['is_active' => ! $category->is_active]);
    }

    public function delete(int $id): void
    {
        $category = ExpenseCategory::query()
            ->forCompany($this->companyId())
            ->withCount('expenses')
            ->find($id);

        if (! $category) {
            return;
        }

        if ($category->expenses_count > 0) {
            $this->dispatch(
                'toast',
                type: 'error',
                message: "Bu kategoriyada {$category->expenses_count} ta xarajat bor.",
            );

            return;
        }

        $category->delete();
        $this->dispatch('toast', type: 'success', message: 'Kategoriya o\'chirildi.');
    }

    private function resetForm(): void
    {
        $this->reset(['categoryId', 'name', 'description']);
        $this->color = '#3b82f6';
        $this->resetValidation();
    }

    public function render()
    {
        $categories = ExpenseCategory::query()
            ->select(['id', 'name', 'description', 'color', 'is_active'])
            ->forCompany($this->companyId())
            ->withCount('expenses')
            ->search($this->search)
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.expense-categories.index-livewire', compact('categories'));
    }
}
