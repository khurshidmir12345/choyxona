<?php

namespace App\Livewire\Concerns;

use App\Models\Customer;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

/**
 * Sotuv ekranida mijozni tanlash / tezda yaratish.
 * Ikkala POS (zal va tez sotuv) bir xil ishlashi uchun umumiy.
 */
trait WithCustomerPicker
{
    public ?int $customerId = null;

    public string $customerSearch = '';

    public bool $showCustomerForm = false;

    public string $newCustomerName = '';

    public string $newCustomerPhone = '';

    public string $newCustomerAddress = '';

    /** Yetkazib berish manzili (faqat delivery turida saqlanadi). */
    public string $deliveryAddress = '';

    #[Computed]
    public function selectedCustomer(): ?Customer
    {
        if (! $this->customerId) {
            return null;
        }

        return Customer::query()
            ->select(['id', 'name', 'phone', 'address'])
            ->forCompany($this->companyId())
            ->find($this->customerId);
    }

    /** Qidiruv natijalari; bo'sh qidiruvda oxirgi mijozlar. */
    #[Computed]
    public function customerResults(): Collection
    {
        return Customer::query()
            ->select(['id', 'name', 'phone', 'address'])
            ->forCompany($this->companyId())
            ->search($this->customerSearch)
            ->when(
                trim($this->customerSearch) === '',
                fn ($q) => $q->latest('updated_at'),
                fn ($q) => $q->orderBy('name'),
            )
            ->limit(8)
            ->get();
    }

    /** @return array<int, string> */
    #[Computed]
    public function customerAddresses(): array
    {
        return $this->selectedCustomer?->knownAddresses() ?? [];
    }

    public function selectCustomer(int $id): void
    {
        $customer = Customer::query()
            ->select(['id', 'address'])
            ->forCompany($this->companyId())
            ->find($id);

        if (! $customer) {
            return;
        }

        $this->customerId = $customer->id;
        $this->customerSearch = '';
        $this->showCustomerForm = false;

        if (trim($this->deliveryAddress) === '' && filled($customer->address)) {
            $this->deliveryAddress = (string) $customer->address;
        }

        unset($this->selectedCustomer, $this->customerAddresses);
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
        unset($this->selectedCustomer, $this->customerAddresses);
    }

    public function startNewCustomer(): void
    {
        $term = trim($this->customerSearch);
        $isPhone = $term !== '' && preg_match('/^[\d\s+()-]+$/', $term);

        $this->newCustomerName = $isPhone ? '' : $term;
        $this->newCustomerPhone = $isPhone ? $term : '';
        $this->newCustomerAddress = '';
        $this->resetErrorBag(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);
        $this->showCustomerForm = true;
    }

    public function cancelNewCustomer(): void
    {
        $this->showCustomerForm = false;
        $this->resetErrorBag(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);
    }

    /** Shu telefon bilan mijoz bo'lsa, yangisi yaratilmaydi — o'sha tanlanadi. */
    public function createCustomer(): void
    {
        $this->validate([
            'newCustomerName' => ['required', 'string', 'max:255'],
            'newCustomerPhone' => ['nullable', 'string', 'max:32'],
            'newCustomerAddress' => ['nullable', 'string', 'max:500'],
        ], [
            'newCustomerName.required' => 'Mijoz ismini yozing.',
        ]);

        $phone = Customer::normalizePhone($this->newCustomerPhone);

        $existing = $phone
            ? Customer::query()->select(['id'])->forCompany($this->companyId())->where('phone', $phone)->first()
            : null;

        $customer = $existing ?? Customer::create([
            'company_id' => $this->companyId(),
            'name' => trim($this->newCustomerName),
            'phone' => $phone,
            'address' => trim($this->newCustomerAddress) ?: null,
        ]);

        $this->selectCustomer($customer->id);
        $this->showCustomerForm = false;
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);

        $this->dispatch(
            'toast',
            type: 'success',
            message: $existing ? 'Bu raqamli mijoz allaqachon bor edi, u tanlandi.' : 'Mijoz qo\'shildi.',
        );
    }

    public function useAddress(int $index): void
    {
        $addresses = $this->customerAddresses;

        if (isset($addresses[$index])) {
            $this->deliveryAddress = $addresses[$index];
        }
    }

    protected function resetCustomerPicker(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
        $this->showCustomerForm = false;
        $this->deliveryAddress = '';
        $this->reset(['newCustomerName', 'newCustomerPhone', 'newCustomerAddress']);
        unset($this->selectedCustomer, $this->customerAddresses);
    }
}
