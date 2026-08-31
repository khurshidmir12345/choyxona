<?php

namespace App\Livewire\Admin\Orders;

use App\Casts\OrderStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Company;
use App\Models\Order;
use Livewire\Component;

/**
 * Chek sahifasi. Faqat chekda ko'rinadigan ustunlar tortiladi.
 */
class OrderCompleted extends Component
{
    use WithCompany;

    public int $orderId;

    public function mount(int $id): void
    {
        $this->orderId = $id;
    }

    public function render()
    {
        $order = Order::query()
            ->select(['id', 'company_id', 'user_id', 'place_id', 'amount', 'total_amount', 'discount', 'type', 'status', 'created_at'])
            ->forCompany($this->companyId())
            ->with([
                'user:id,name',
                'place:id,name',
                'orderDetails:id,order_id,product_id,quantity,price,discount,total_amount',
                'orderDetails.product:id,name',
            ])
            ->find($this->orderId);

        if (! $order) {
            return view('livewire.admin.orders.order-error', [
                'error' => "Buyurtma #{$this->orderId} topilmadi.",
            ])->layout('layouts.print');
        }

        if ($order->status !== OrderStatusEnum::Done) {
            return view('livewire.admin.orders.order-error', [
                'error' => "Buyurtma #{$this->orderId} hali yopilmagan.",
            ])->layout('layouts.print');
        }

        $company = Company::query()
            ->select(['id', 'name', 'phone_number', 'address'])
            ->find($order->company_id);

        return view('livewire.admin.orders.order-completed', [
            'order' => $order,
            'company' => $company,
        ])->layout('layouts.print', ['title' => 'Chek #'.$order->id]);
    }
}
