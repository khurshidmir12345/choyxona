<?php

namespace App\Livewire\Admin;

use Livewire\Component;

class Dashboard extends Component
{
    public $categories;
    public $search;

    public function render()
    {
        $this->categories = \App\Models\ProductCategory::all();
        return view('livewire.admin.dashboard');
    }
}
