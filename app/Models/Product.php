<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'price', // bu asl narxi
        'sell_price', // shuni ishlatamiz hamma joyda Zuxriddin
        'extra_price', // bu faqat foydani hisoblash uchun keyinchalik ishlatamiz
        'image',
        'discount',
        'current_stock',
        'code',
        'company_id',
        'category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
