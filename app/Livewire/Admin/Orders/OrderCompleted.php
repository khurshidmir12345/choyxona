<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Order;
use Livewire\Component;

class OrderCompleted extends Component
{
    public $order;
    public $orderId;
    public $error = null;

    /**
     * Component mount method - load order data by ID
     * @param int $id - Route parameter name
     */
    public function mount($id)
    {
        $this->orderId = $id;
        $this->loadOrder();
    }

    /**
     * Load order with all related data
     */
    private function loadOrder()
    {
        // First, let's find the order without status restriction
        $order = Order::with([
            'user',
            'place', 
            'company',
            'orderDetails.product',
            'orderDetails.worker'
        ])
        ->where('company_id', auth()->user()->getCompany()->id)
        ->where('id', $this->orderId)
        ->first();

        if (!$order) {
            $this->error = "Buyurtma #{$this->orderId} topilmadi";
            return;
        }

        // Check if order is completed
        if ($order->status !== \App\Casts\OrderStatusEnum::Done) {
            $this->error = "Buyurtma #{$this->orderId} tugatilmagan. Status: " . $order->status->value;
            return;
        }

        $this->order = $order;
    }

    /**
     * Print receipt using browser print function
     */
    public function printReceipt()
    {
        $this->dispatch('print-receipt');
    }

    /**
     * Render the receipt view
     */
    public function render()
    {
        if ($this->error) {
            return view('livewire.admin.orders.order-error', ['error' => $this->error])
                ->layout('layouts.print');
        }

        return view('livewire.admin.orders.order-completed')
            ->layout('layouts.print');
    }
} 