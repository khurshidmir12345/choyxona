<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = [
        'name',
        'price',        // tannarx
        'sell_price',   // mijozga sotiladigan narx
        'extra_price',  // foyda (sell_price - price)
        'image',
        'discount',
        'current_stock',
        'code',
        'company_id',
        'category_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'sell_price' => 'integer',
            'extra_price' => 'integer',
            'discount' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    /**
     * POS ekranida bitta mahsulot uchun kerak bo'ladigan ustunlar.
     * Rasm/kod/zaxira bundan tashqarisi hech qayerda ishlatilmaydi.
     */
    public const CARD_COLUMNS = [
        'id', 'name', 'sell_price', 'discount', 'current_stock', 'image', 'code', 'category_id',
    ];

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        // Guruhlash shart: aks holda "orWhere" company_id filtrini chetlab o'tadi.
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', '%'.$term.'%')
                ->orWhere('code', 'like', '%'.$term.'%');
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /** Bazada nisbiy yo'l saqlanadi; to'liq URL faqat ko'rsatishda yasaladi. */
    public function imageUrl(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return asset('storage/'.ltrim($this->image, '/'));
    }

    public function formattedCode(): string
    {
        return str_pad((string) $this->code, 5, '0', STR_PAD_LEFT);
    }
}
