<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $expandedOrderId = null;

    public function toggleDetails($orderId)
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function render()
    {
        $orders = Order::with(['user', 'place', 'company'])
            ->where('company_id', auth()->user()->getCompany()->id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.orders.index-livewire', compact('orders'));
    }
}
