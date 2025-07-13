<?php

namespace App\Livewire\Admin\ExpenseCategories;

use App\Models\ExpenseCategory;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $showModal = false;
    public $editingCategory = null;
    public $name = '';
    public $description = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
    ];

    public function render()
    {
        $companyId = auth()->user()->getCompany()->id;
        
        $categories = ExpenseCategory::where('company_id', $companyId)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.expense-categories.index-livewire', compact('categories'));
    }

    public function createCategory()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editCategory($id)
    {
        $category = ExpenseCategory::find($id);
        if ($category && $category->company_id === auth()->user()->getCompany()->id) {
            $this->editingCategory = $category;
            $this->name = $category->name;
            $this->description = $category->description;
            $this->showModal = true;
        }
    }

    public function saveCategory()
    {
        $this->validate();

        if ($this->editingCategory) {
            // Update existing category
            $this->editingCategory->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Kategoriya muvaffaqiyatli yangilandi!');
        } else {
            // Create new category
            ExpenseCategory::create([
                'company_id' => auth()->user()->getCompany()->id,
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => true,
            ]);
            session()->flash('message', 'Kategoriya muvaffaqiyatli yaratildi!');
        }

        $this->closeModal();
        $this->dispatch('$refresh');
    }

    public function deleteCategory($id)
    {
        try {
            $category = ExpenseCategory::find($id);
            
            if (!$category) {
                session()->flash('error', 'Kategoriya topilmadi!');
                return;
            }
            
            if ($category->company_id !== auth()->user()->getCompany()->id) {
                session()->flash('error', 'Bu kategoriyani o\'chirish huquqingiz yo\'q!');
                return;
            }
            
            $categoryName = $category->name;
            $category->delete(); // This will now use soft delete
            
            session()->flash('message', "Kategoriya '{$categoryName}' muvaffaqiyatli o'chirildi!");
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function restoreCategory($id)
    {
        try {
            $category = ExpenseCategory::withTrashed()->find($id);
            
            if (!$category) {
                session()->flash('error', 'Kategoriya topilmadi!');
                return;
            }
            
            if ($category->company_id !== auth()->user()->getCompany()->id) {
                session()->flash('error', 'Bu kategoriyani tiklash huquqingiz yo\'q!');
                return;
            }
            
            $category->restore();
            session()->flash('message', "Kategoriya '{$category->name}' muvaffaqiyatli tiklandi!");
            $this->dispatch('$refresh');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $category = ExpenseCategory::find($id);
        if ($category && $category->company_id === auth()->user()->getCompany()->id) {
            $category->update(['is_active' => !$category->is_active]);
            session()->flash('message', 'Kategoriya holati o\'zgartirildi!');
            $this->dispatch('$refresh');
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingCategory = null;
        $this->name = '';
        $this->description = '';
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
