<?php

namespace App\Livewire\Admin\Places;

use App\Models\Place;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $name, $capacity, $company_id;

    public $search = '';

    public $place_id;

    public function mount()
    {
        $this->company_id = auth()->user()->getCompany()->id;
    }

    public function create()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'nullable|integer',
        ]);

        Place::query()->create([
            'name' => $this->name,
            'capacity' => $this->capacity,
            'company_id' => $this->company_id ?? auth()->user()->getCompany()->id,
        ]);

        $this->dispatch('closeModal');
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->capacity = '';
        $this->company_id = '';
        $this->place_id = null;
    }

    public function edit($id)
    {
        $place = Place::query()->findOrFail($id);
        $this->place_id = $place->id;
        $this->name = $place->name;
        $this->capacity = $place->capacity;
        $this->company_id = $place->company_id;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|string|max:255',
        ]);

        $place = Place::query()->findOrFail($this->place_id);
        $place->update([
            'name' => $this->name,
            'capacity' => $this->capacity,
        ]);

        $this->resetInputFields();
        $this->dispatch('closeModal');
    }

    public function delete($id)
    {
        Place::query()->findOrFail($id)->delete();
    }

    public function render()
    {
        $places = Place::query()->with('company')
            ->when($this->search, function ($query) {
                return $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->where('company_id', auth()->user()->getCompany()->id)
            ->orderByDesc('id')->paginate(15);

        return view('livewire.admin.places.index-livewire', compact('places'));
    }
}
