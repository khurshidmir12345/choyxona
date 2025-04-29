<?php

namespace App\Models;

use App\Casts\ProductStockType;
use Database\Factories\ProductStockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    /** @use HasFactory<ProductStockFactory> */
    use HasFactory;

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
            'type' => ProductStockType::class
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
