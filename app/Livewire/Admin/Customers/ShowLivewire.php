<?php

namespace App\Livewire\Admin\Customers;

use App\Casts\OrderStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Bitta mijoz: ma'lumotlari, aytgan manzillari va savdo tarixi.
 */
class ShowLivewire extends Component
{
    use WithPagination, WithCompany;

    protected $paginationTheme = 'bootstrap';

    public int $customerId;

    public ?int $expandedOrderId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $note = '';

    public function mount(int $id): void
    {
        $this->customerId = $id;
        $this->customer();
    }

    #[Computed]
    public function customer(): Customer
    {
        return Customer::query()
            ->forCompany($this->companyId())
            ->findOrFail($this->customerId);
    }

    #[Computed]
    public function stats(): array
    {
        $row = Order::query()
            ->where('customer_id', $this->customerId)
            ->where('status', OrderStatusEnum::Done->value)
            ->selectRaw('COUNT(*) as orders_count, COALESCE(SUM(total_amount), 0) as total_spent, MAX(created_at) as last_order_at')
            ->first();

        $count = (int) $row->orders_count;

        return [
            'orders' => $count,
            'total' => (int) $row->total_spent,
            'average' => $count ? (int) round($row->total_spent / $count) : 0,
            'last' => $row->last_order_at ? \Illuminate\Support\Carbon::parse($row->last_order_at) : null,
        ];
    }

    /** @return array<int, string> */
    #[Computed]
    public function addresses(): array
    {
        return $this->customer->knownAddresses();
    }

    public function toggleDetails(int $orderId): void
    {
        $this->expandedOrderId = $this->expandedOrderId === $orderId ? null : $orderId;
    }

    public function edit(): void
    {
        $customer = $this->customer;

        $this->name = (string) $customer->name;
        $this->phone = (string) ($customer->formattedPhone() ?? '');
        $this->address = (string) $customer->address;
        $this->note = (string) $customer->note;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'name.required' => 'Mijoz ismini kiriting.',
        ]);

        $phone = Customer::normalizePhone($data['phone'] ?? null);

        if ($phone) {
            $duplicate = Customer::query()
                ->forCompany($this->companyId())
                ->where('phone', $phone)
                ->whereKeyNot($this->customerId)
                ->exists();

            if ($duplicate) {
                $this->addError('phone', 'Bu raqam boshqa mijozda bor.');

                return;
            }
        }

        $this->customer->update([
            'name' => trim($data['name']),
            'phone' => $phone,
            'address' => trim((string) ($data['address'] ?? '')) ?: null,
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
        ]);

        unset($this->customer, $this->addresses);
        $this->showForm = false;
        $this->dispatch('toast', type: 'success', message: 'Mijoz yangilandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetValidation();
    }

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
            ->select(['id', 'user_id', 'place_id', 'amount', 'total_amount', 'discount', 'type', 'status', 'delivery_address', 'created_at'])
            ->forCompany($this->companyId())
            ->where('customer_id', $this->customerId)
            ->with(['user:id,name', 'place:id,name'])
            ->latest('id')
            ->paginate(15);

        return view('livewire.admin.customers.show-livewire', [
            'orders' => $orders,
            'details' => $this->details(),
        ]);
    }
}
