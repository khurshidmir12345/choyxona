<?php

namespace App\Livewire\Admin\Setup;

use App\Casts\BusinessType;
use App\Livewire\Concerns\WithCompany;
use App\Models\Company;
use App\Support\Business;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Birinchi kirishda biznes turini tanlash ekrani.
 */
#[Layout('layouts.setup')]
class BusinessTypeLivewire extends Component
{
    use WithCompany;

    public function choose(string $type)
    {
        $businessType = BusinessType::tryFrom($type);
        $companyId = $this->companyId();

        if (! $businessType || ! $companyId) {
            $this->dispatch('toast', type: 'error', message: 'Biznes turi tanlanmadi.');

            return null;
        }

        Company::query()->whereKey($companyId)->update(['business_type' => $businessType->value]);
        Business::forget();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.admin.setup.business-type-livewire', [
            'types' => BusinessType::cases(),
        ]);
    }
}
