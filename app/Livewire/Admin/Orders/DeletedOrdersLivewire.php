<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\Concerns\WithCompany;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class DeletedOrdersLivewire extends Component
{
    use WithPagination, WithCompany;

    public string $fromDate = '';

    public string $toDate = '';

    public string $type = '';

    public ?int $expandedOrderId = null;

    public function updatedFromDate(): void
    {
        $this->resetPage();
    }

    public function updatedToDate(): void
    {
        $this->resetPage();
    }

    public function updatedType(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['fromDate', 'toDate', 'type']);
        $this->resetPage();
    }

    public function toggleDetails(int $orderId): void
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function restore(int $orderId, OrderService $orders): void
    {
        $order = Order::withTrashed()->forCompany($this->companyId())->find($orderId);

        if (! $order) {
            return;
        }

        $orders->restoreOrder($order);
        $this->dispatch('toast', type: 'success', message: 'Buyurtma tiklandi.');
    }

    public function forceDelete(int $orderId, OrderService $orders): void
    {
        $order = Order::withTrashed()->forCompany($this->companyId())->find($orderId);

        if (! $order) {
            return;
        }

        $orders->forceDeleteOrder($order);
        $this->dispatch('toast', type: 'success', message: 'Buyurtma butunlay o\'chirildi.');
    }

    private function details(): Collection
    {
        if (! $this->expandedOrderId) {
            return collect();
        }

        return OrderDetail::withTrashed()
            ->select(['id', 'order_id', 'product_id', 'worker_id', 'quantity', 'price', 'discount', 'total_amount'])
            ->where('order_id', $this->expandedOrderId)
            ->with(['product:id,name', 'worker:id,name'])
            ->get();
    }

    public function render()
    {
        $orders = Order::onlyTrashed()
            ->select(['id', 'user_id', 'place_id', 'amount', 'total_amount', 'discount', 'type', 'status', 'created_at', 'deleted_at'])
            ->forCompany($this->companyId())
            ->with(['user:id,name', 'place:id,name'])
            ->when($this->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->orderByDesc('deleted_at')
            ->paginate(15);

        return view('livewire.admin.orders.deleted-orders-livewire', [
            'orders' => $orders,
            'details' => $this->details(),
        ]);
    }
}
