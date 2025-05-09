<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Place;
use Livewire\Component;

class OrderInCafeLivewire extends Component
{
    public $place_id;
    public $showModal = false;
    public $selectedPlace = null;

    public function startOrder($place_id)
    {
        $this->place_id = $place_id;
        $this->selectedPlace = Place::query()->find($place_id);
        $this->showModal = true;
    }

    public function createOrder()
    {
        if (!$this->selectedPlace) {
            return;
        }
        $this->dispatch('openCreateOrderModal', place: $this->selectedPlace);
    }

    public function cancelOrder()
    {
        $this->place_id = '';
        $this->showModal = false;
        $this->selectedPlace = null;
    }

    public function render()
    {
        $places = Place::query()->where('company_id', auth()->user()->getCompany()->id)->get();
        return view('livewire.admin.orders.order-in-cafe-livewire', compact('places'));
    }
}
