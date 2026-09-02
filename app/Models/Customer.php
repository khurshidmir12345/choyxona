<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory, SoftDeletes, BelongsToCompany;

    protected $fillable = ['company_id', 'name', 'phone', 'address', 'note'];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    /** Ism yoki telefon bo'yicha qidiruv; raqam faqat raqamlar bilan solishtiriladi. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $digits = preg_replace('/\D/', '', $term);

        return $query->where(function (Builder $q) use ($term, $digits) {
            $q->where('name', 'like', '%'.$term.'%');

            if ($digits !== '') {
                $q->orWhere('phone', 'like', '%'.$digits.'%');
            }
        });
    }

    /** Telefon bazada bir xil ko'rinishda saqlanadi: faqat raqamlar va boshida "+". */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (strlen($digits) === 9) {
            $digits = '998'.$digits;
        }

        return '+'.$digits;
    }

    /** Ko'rsatish uchun: +998 90 123 45 67 */
    public function formattedPhone(): ?string
    {
        if (blank($this->phone)) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $this->phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '998')) {
            return sprintf('+998 %s %s %s %s', substr($digits, 3, 2), substr($digits, 5, 3), substr($digits, 8, 2), substr($digits, 10, 2));
        }

        return $this->phone;
    }

    /**
     * Mijoz aytgan barcha yetkazib berish manzillari: asosiy manzil +
     * buyurtmalarda ishlatilganlari (oxirgi ishlatilgani birinchi).
     *
     * @return array<int, string>
     */
    public function knownAddresses(): array
    {
        $fromOrders = $this->orders()
            ->select(['delivery_address'])
            ->whereNotNull('delivery_address')
            ->where('delivery_address', '!=', '')
            ->latest('id')
            ->limit(50)
            ->pluck('delivery_address')
            ->all();

        $all = array_filter(array_merge($fromOrders, [$this->address]), fn ($a) => filled($a));

        return array_values(array_unique(array_map(fn ($a) => trim((string) $a), $all)));
    }
}
