<?php

namespace App\Models;

use App\Casts\PlaceStatusEnum;
use Database\Factories\PlaceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Place extends Model
{
    /** @use HasFactory<PlaceFactory> */
    use HasFactory;

    protected $fillable = ['name', 'company_id', 'status', 'capacity'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'status' => PlaceStatusEnum::class
        ];
    }

    public function isStatusEmpty(): bool
    {
        return $this->status->value === 'empty';
    }
    public function getStatusColor(): string
    {
        return $this->status->value === 'empty' ? '#52be80' : '#FF6347';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
