<?php

namespace App\Livewire\Admin\Customers;

use App\Casts\OrderStatusEnum;
use App\Livewire\Concerns\WithCompany;
use App\Models\Customer;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Mijozlar ro'yxati: kim, qancha xarid qilgan, oxirgi tashrif.
 */
class IndexLivewire extends Component
{
    use WithPagination, WithCompany;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $showForm = false;

    public ?int $customerId = null;

    public string $name = '';

    public string $phone = '';

    public string $address = '';

    public string $note = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function createCustomer(): void
    {
        $this->reset(['customerId', 'name', 'phone', 'address', 'note']);
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $customer = Customer::query()
            ->select(['id', 'name', 'phone', 'address', 'note'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $customer) {
            return;
        }

        $this->customerId = $customer->id;
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
                ->when($this->customerId, fn ($q) => $q->whereKeyNot($this->customerId))
                ->exists();

            if ($duplicate) {
                $this->addError('phone', 'Bu raqam boshqa mijozda bor.');

                return;
            }
        }

        $attributes = [
            'name' => trim($data['name']),
            'phone' => $phone,
            'address' => trim((string) ($data['address'] ?? '')) ?: null,
            'note' => trim((string) ($data['note'] ?? '')) ?: null,
        ];

        if ($this->customerId) {
            Customer::query()
                ->forCompany($this->companyId())
                ->whereKey($this->customerId)
                ->update($attributes);
        } else {
            Customer::create($attributes + ['company_id' => $this->companyId()]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Mijoz saqlandi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->reset(['customerId', 'name', 'phone', 'address', 'note']);
        $this->resetValidation();
    }

    /** Buyurtmalar tarixi saqlanib qoladi — mijoz faqat ro'yxatdan yashiriladi. */
    public function delete(int $id): void
    {
        Customer::query()
            ->forCompany($this->companyId())
            ->whereKey($id)
            ->delete();

        $this->dispatch('toast', type: 'success', message: 'Mijoz o\'chirildi.');
    }

    public function render()
    {
        $done = OrderStatusEnum::Done->value;

        $customers = Customer::query()
            ->select(['id', 'name', 'phone', 'address', 'created_at'])
            ->forCompany($this->companyId())
            ->search($this->search)
            ->withCount(['orders as orders_count' => fn ($q) => $q->where('status', $done)])
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', $done)], 'total_amount')
            ->withMax('orders as last_order_at', 'created_at')
            ->orderByDesc('last_order_at')
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.admin.customers.index-livewire', compact('customers'));
    }
}
