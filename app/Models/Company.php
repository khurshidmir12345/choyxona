<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'business_type',
        'phone_number',
        'email',
        'description',
        'logo',
        'balance',
        'address',
        'latitude',
        'longitude',
        'open_time',
        'close_time',
    ];

    /**
     * open_time / close_time 2025_05_04 migratsiyasidan keyin oddiy matn
     * ("09:00"). Ilgari ular 'timestamp' ga cast qilinardi, natijada
     * Carbon parse qilishga urinib xato berardi.
     */
    protected function casts(): array
    {
        return [
            'business_type' => \App\Casts\BusinessType::class,
        ];
    }

    public function businessType(): \App\Casts\BusinessType
    {
        return $this->business_type ?? \App\Casts\BusinessType::Cafe;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Kompaniyaga biriktirilgan xodimlar (egasi bundan tashqari). */
    public function sellers(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function places(): HasMany
    {
        return $this->hasMany(Place::class);
    }

    public function logoUrl(): ?string
    {
        if (blank($this->logo)) {
            return null;
        }

        if (str_starts_with($this->logo, 'http://') || str_starts_with($this->logo, 'https://')) {
            return $this->logo;
        }

        return asset('storage/'.ltrim($this->logo, '/'));
    }
}
