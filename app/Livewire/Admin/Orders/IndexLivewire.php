<?php

namespace App\Livewire\Admin\Orders;

use App\Livewire\Concerns\WithCompany;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    #[Url(as: 'dan', except: '')]
    public string $fromDate = '';

    #[Url(as: 'gacha', except: '')]
    public string $toDate = '';

    #[Url(as: 'turi', except: '')]
    public string $type = '';

    #[Url(as: 'holat', except: '')]
    public string $status = '';

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

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['fromDate', 'toDate', 'type', 'status']);
        $this->resetPage();
    }

    public function toggleDetails(int $orderId): void
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function delete(int $orderId, OrderService $orders): void
    {
        $order = Order::query()->forCompany($this->companyId())->find($orderId);

        if (! $order) {
            return;
        }

        $orders->trashOrder($order);
        $this->dispatch('toast', type: 'success', message: 'Buyurtma arxivga o\'tkazildi.');
    }

    /**
     * Faqat ochilgan qator uchun tafsilot yuklanadi.
     * Ilgari sahifadagi 10 ta buyurtmaning hammasi uchun yuklanardi.
     */
    private function details(): Collection
    {
        if (! $this->expandedOrderId) {
            return collect();
        }

        return OrderDetail::query()
            ->select(['id', 'order_id', 'product_id', 'worker_id', 'quantity', 'price', 'discount', 'total_amount'])
            ->where('order_id', $this->expandedOrderId)
            ->with(['product:id,name', 'worker:id,name'])
            ->get();
    }

    public function render()
    {
        $orders = Order::query()
            ->select(['id', 'user_id', 'place_id', 'amount', 'total_amount', 'discount', 'type', 'status', 'created_at'])
            ->forCompany($this->companyId())
            ->with(['user:id,name', 'place:id,name'])
            ->when($this->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(15);

        $summary = Order::query()
            ->forCompany($this->companyId())
            ->when($this->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('created_at', '<=', $this->toDate))
            ->when($this->type, fn ($q) => $q->where('type', $this->type))
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as revenue')
            ->first();

        return view('livewire.admin.orders.index-livewire', [
            'orders' => $orders,
            'details' => $this->details(),
            'ordersCount' => (int) $summary->orders_count,
            'revenue' => (int) $summary->revenue,
        ]);
    }
}
