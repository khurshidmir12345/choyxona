<?php

namespace App\Models\Concerns;

use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Har bir yozuv qaysidir kompaniyaga tegishli. Bu trait tenancy filtrini
 * bitta joyda saqlaydi, shunda hech bir so'rov company_id ni unutib qo'ymaydi.
 */
trait BelongsToCompany
{
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        return $query->where($query->getModel()->qualifyColumn('company_id'), $companyId);
    }
}
