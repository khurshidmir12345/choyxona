<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination;

    public $from_date, $to_date, $type;

    protected $listeners = [
        'refresh_index' => 'render',
    ];

    public function clear_to_date()
    {
        $this->to_date = '';
        $this->render();
    }
    public function clear_from_date()
    {
        $this->from_date = '';
        $this->render();
    }

    protected $paginationTheme = 'bootstrap';

    public $expandedOrderId = null;

    public function toggleDetails($orderId)
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function delete($orderId)
    {
        $order = Order::findOrFail($orderId);

        // Order details ni o'chiramiz
        $order->orderDetails()->delete();

        // Order ni o'chiramiz
        $order->delete();

        session()->flash('message', 'Buyurtma muvaffaqiyatli o\'chirildi.');
    }

    public function render()
    {
        $orders = Order::with(['user', 'place', 'company'])
            ->where('company_id', auth()->user()->getCompany()->id)
            ->when($this->from_date, function ($query) {
                return $query->whereDate('created_at', '>=', $this->from_date);
            })
            ->when($this->to_date, function ($query) {
                return $query->whereDate('created_at', '<=', $this->to_date);
            })
            ->when($this->type, function ($query) {
                return $query->where('type', $this->type);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.admin.orders.index-livewire', compact('orders'));
    }
}
