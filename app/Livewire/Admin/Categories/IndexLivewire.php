<?php

namespace App\Livewire\Admin\Categories;

use App\Models\ProductCategory;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $name;

    public $search = '';

    public $category_id;

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        ProductCategory::query()->create([
            'name' => $this->name,
            'company_id' => auth()->user()->getCompany()->id,
        ]);

        $this->dispatch('closeModal');
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->category_id = null;
    }

    public function edit($id)
    {
        $category = ProductCategory::findOrFail($id);
        $this->category_id = $category->id;
        $this->name = $category->name;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = ProductCategory::findOrFail($this->category_id);
        $category->update([
            'name' => $this->name,
        ]);

        $this->resetInputFields();
        $this->dispatch('closeModal');
    }

    public function delete($id)
    {
        ProductCategory::findOrFail($id)->delete();
    }

    public function render()
    {
        $categories = ProductCategory::query()->with('company')
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->where('company_id', auth()->user()->getCompany()->id)
            ->orderByDesc('id')->paginate(15);

        return view('livewire.admin.categories.index-livewire', compact('categories'));
    }
}
