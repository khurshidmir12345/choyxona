<?php

namespace App\Models;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'place_id',
        'user_id',
        'total_amount',
        'discount',
        'type',
        'status',
    ];


    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
            'discount' => 'integer',
            'type' => OrderTypeEnum::class,
            'status' => OrderStatusEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }
}
