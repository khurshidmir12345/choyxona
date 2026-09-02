<?php

namespace App\Livewire\Admin\Places;

use App\Casts\PlaceStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Place;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    /** Shablon Bootstrap 5 asosida — sahifalash ham shunga mos. */
    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $showForm = false;

    public ?int $placeId = null;

    public string $name = '';

    public $capacity = 4;

    /** Modalda tez tanlash uchun sig'imlar. */
    public const CAPACITY_PRESETS = [2, 4, 6, 8, 10, 12];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createPlace(): void
    {
        $this->reset(['placeId', 'name']);
        $this->capacity = 4;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $place = Place::query()
            ->select(['id', 'name', 'capacity'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $place) {
            return;
        }

        $this->placeId = $place->id;
        $this->name = (string) $place->name;
        $this->capacity = $place->capacity;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],
        ], [
            'name.required' => 'Joy nomini kiriting.',
            'capacity.required' => 'Sig\'imni kiriting.',
            'capacity.integer' => 'Sig\'im raqam bo\'lishi kerak.',
        ]);

        if ($this->placeId) {
            Place::query()
                ->forCompany($this->companyId())
                ->whereKey($this->placeId)
                ->update(['name' => $data['name'], 'capacity' => $data['capacity']]);
        } else {
            Place::create([
                'name' => $data['name'],
                'capacity' => $data['capacity'],
                'company_id' => $this->companyId(),
                'status' => PlaceStatusEnum::Empty,
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Joy saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['placeId', 'name']);
        $this->capacity = 4;
        $this->resetValidation();
    }

    /** Band stolni o'chirib bo'lmaydi: ochiq hisob yo'qolib qoladi. */
    public function delete(int $id): void
    {
        $place = Place::query()
            ->select(['id', 'status'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $place) {
            return;
        }

        if ($place->isBusy()) {
            $this->dispatch('toast', type: 'error', message: 'Stol band. Avval hisobni yoping.');

            return;
        }

        $place->delete();
        $this->dispatch('toast', type: 'success', message: 'Joy o\'chirildi.');
    }

    public function render()
    {
        $places = Place::query()
            ->select(['id', 'name', 'status', 'capacity'])
            ->forCompany($this->companyId())
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(24);

        return view('livewire.admin.places.index-livewire', compact('places'));
    }
}
