<?php

namespace App\Models;

use App\Casts\ProductStockType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    /** @use HasFactory<ProductStockFactory> */
    use HasFactory, BelongsToCompany;

    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id',
        'company_id',
        'quantity',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'type' => ProductStockType::class,
        ];
    }

    /** Zaxiraga qanday ta'sir qiladi: kirim +, chiqim -. */
    public function stockDelta(): int
    {
        return $this->type === ProductStockType::Add
            ? $this->quantity
            : -$this->quantity;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
