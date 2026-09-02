<?php

namespace App\Models;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'company_id',
        'place_id',
        'user_id',
        'customer_id',
        'delivery_address',
        'amount',
        'total_amount',
        'discount',
        'type',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'total_amount' => 'integer',
            'discount' => 'integer',
            'type' => OrderTypeEnum::class,
            'status' => OrderStatusEnum::class,
        ];
    }

    public function scopeOpened(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::Opened);
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', OrderStatusEnum::Done);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    /** O'chirilgan mijoz ham tarixda ko'rinaveradi. */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /** Chegirmadan keyingi summa (so'mda). */
    public function discountAmount(): int
    {
        return max(0, (int) $this->amount - (int) $this->total_amount);
    }
}
