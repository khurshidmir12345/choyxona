{{--
    Savat tepasidagi mijoz bloki: tanlash, qidirish, tezda yaratish.
    Kutiladi: $showDelivery (bool) — yetkazish manzili maydoni kerakmi.
--}}
@php $customer = $this->selectedCustomer; @endphp

<div class="cust-picker" x-data="{ open: false }" x-on:click.outside="open = false">
    @if($customer)
        <div class="cust-selected">
            <span class="cust-avatar">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
            <div class="min-w-0 flex-grow-1">
                <p class="cust-name text-truncate">{{ $customer->name }}</p>
                <p class="cust-phone">{{ $customer->formattedPhone() ?? 'Telefon yo\'q' }}</p>
            </div>
            <a href="{{ route('customers.show', $customer->id) }}" target="_blank" class="cust-icon-btn"
               title="Mijoz sahifasi"><i class="mdi mdi-open-in-new"></i></a>
            <button type="button" class="cust-icon-btn" wire:click="clearCustomer" title="Olib tashlash">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
    @elseif(! $showCustomerForm)
        <div class="cust-search">
            <i class="mdi mdi-account-search-outline"></i>
            <input type="text" wire:model.live.debounce.250ms="customerSearch"
                   placeholder="Mijoz: ism yoki telefon" autocomplete="off"
                   x-on:focus="open = true" x-on:keydown.escape="open = false">
            <button type="button" class="cust-add" wire:click="startNewCustomer" x-on:click="open = false"
                    title="Yangi mijoz">
                <i class="mdi mdi-account-plus-outline"></i>
            </button>
        </div>

        <div class="cust-dropdown" x-show="open" x-cloak x-transition.opacity>
            @forelse($this->customerResults as $result)
                <button type="button" class="cust-item" wire:key="cust-{{ $result->id }}"
                        wire:click="selectCustomer({{ $result->id }})" x-on:click="open = false">
                    <span class="cust-avatar">{{ mb_strtoupper(mb_substr($result->name, 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="cust-name d-block text-truncate">{{ $result->name }}</span>
                        <span class="cust-phone">{{ $result->formattedPhone() ?? ($result->address ?: '—') }}</span>
                    </span>
                </button>
            @empty
                <div class="cust-empty">
                    {{ trim($customerSearch) === '' ? 'Hali mijoz yo\'q.' : 'Topilmadi.' }}
                    <button type="button" class="btn btn-link btn-sm p-0" wire:click="startNewCustomer"
                            x-on:click="open = false">Yangi mijoz qo'shish</button>
                </div>
            @endforelse
        </div>
    @endif

    @if($showCustomerForm)
        <div class="cust-form">
            <p class="cust-form-title"><i class="mdi mdi-account-plus-outline"></i> Yangi mijoz</p>
            <input type="text" wire:model="newCustomerName" placeholder="Ismi *"
                   x-data x-init="$nextTick(() => $el.focus())"
                   wire:keydown.enter.prevent="createCustomer"
                   class="form-control form-control-sm @error('newCustomerName') is-invalid @enderror">
            @error('newCustomerName') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <input type="tel" wire:model="newCustomerPhone" placeholder="Telefon"
                   wire:keydown.enter.prevent="createCustomer"
                   class="form-control form-control-sm mt-2 tabular">
            <input type="text" wire:model="newCustomerAddress" placeholder="Manzil (ixtiyoriy)"
                   wire:keydown.enter.prevent="createCustomer"
                   class="form-control form-control-sm mt-2">
            <div class="d-flex gap-2 mt-2">
                <button type="button" class="btn btn-primary btn-sm flex-grow-1" wire:click="createCustomer">
                    <i class="mdi mdi-check me-1"></i> Saqlash
                </button>
                <button type="button" class="btn btn-inverse-secondary btn-sm" wire:click="cancelNewCustomer">
                    Bekor
                </button>
            </div>
        </div>
    @endif

    @if($showDelivery ?? false)
        <div class="cust-address">
            <label class="form-label small fw-semibold text-muted mb-1">
                <i class="mdi mdi-map-marker-outline"></i> Yetkazish manzili
            </label>
            <input type="text" wire:model.blur="deliveryAddress" class="form-control form-control-sm"
                   placeholder="Ko'cha, uy, mo'ljal">
            @if($this->customerAddresses)
                <div class="cust-address-chips">
                    @foreach($this->customerAddresses as $i => $address)
                        <button type="button" wire:key="addr-{{ $i }}" wire:click="useAddress({{ $i }})"
                                class="chip {{ trim($deliveryAddress) === $address ? 'active' : '' }}"
                                title="{{ $address }}">
                            <i class="mdi mdi-map-marker-outline"></i>
                            {{ \Illuminate\Support\Str::limit($address, 28) }}
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
