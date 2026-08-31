<?php

namespace App\Models;

use App\Casts\PlaceStatusEnum;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = ['name', 'company_id', 'status', 'capacity'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'status' => PlaceStatusEnum::class,
        ];
    }

    public function isBusy(): bool
    {
        return $this->status === PlaceStatusEnum::Busy;
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
