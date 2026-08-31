<?php

namespace App\Livewire\Concerns;

/**
 * Joriy foydalanuvchining kompaniyasi. Ilgari har bir chaqiruv alohida
 * SQL so'rov edi (bitta sahifada 10+ marta), endi so'rov davomida bir marta.
 */
trait WithCompany
{
    private ?int $resolvedCompanyId = null;

    protected function companyId(): ?int
    {
        return $this->resolvedCompanyId ??= auth()->user()?->companyId();
    }
}
