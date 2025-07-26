<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;

class DeletedOrdersLivewire extends Component
{
    use WithPagination;

    public $from_date, $to_date, $type;
    public $expandedOrderId = null;

    protected $paginationTheme = 'bootstrap';

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

    public function toggleDetails($orderId)
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
        
        // Debug: Check if order details are loaded
        if ($this->expandedOrderId === $orderId) {
            $order = Order::withTrashed()->with(['orderDetails' => function($query) {
                $query->withTrashed()->with(['product', 'worker' => function($workerQuery) {
                    $workerQuery->withTrashed();
                }]);
            }])->find($orderId);
            
            if ($order && $order->orderDetails->count() === 0) {
                session()->flash('message', 'Buyurtma tafsilotlari topilmadi yoki o\'chirilgan.');
            }
        }
    }

    public function restore($orderId)
    {
        $order = Order::withTrashed()->findOrFail($orderId);
        
        // Order details ni restore qilamiz
        $order->orderDetails()->withTrashed()->restore();
        
        // Order ni restore qilamiz
        $order->restore();
        
        session()->flash('message', 'Buyurtma muvaffaqiyatli tiklandi.');
    }

    public function forceDelete($orderId)
    {
        $order = Order::withTrashed()->findOrFail($orderId);
        
        // Order details ni to'liq o'chiramiz
        $order->orderDetails()->withTrashed()->forceDelete();
        
        // Order ni to'liq o'chiramiz
        $order->forceDelete();
        
        session()->flash('message', 'Buyurtma to\'liq o\'chirildi.');
    }

    public function render()
    {
        $orders = Order::withTrashed()
            ->with(['user', 'place', 'company', 'orderDetails' => function($query) {
                $query->withTrashed()->with(['product', 'worker' => function($workerQuery) {
                    $workerQuery->withTrashed();
                }]);
            }])
            ->where('company_id', auth()->user()->getCompany()->id)
            ->whereNotNull('deleted_at')
            ->when($this->from_date, function ($query) {
                return $query->whereDate('created_at', '>=', $this->from_date);
            })
            ->when($this->to_date, function ($query) {
                return $query->whereDate('created_at', '<=', $this->to_date);
            })
            ->when($this->type, function ($query) {
                return $query->where('type', $this->type);
            })
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('livewire.admin.orders.deleted-orders-livewire', compact('orders'));
    }
} 