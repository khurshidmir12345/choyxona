<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'phone_verified_at',
        'type',
        'balance',
        'role_id',
        'company_id', /* company_id faqat seller uchun bo'ladi */
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Bir so'rov ichida kompaniyani qayta-qayta izlamaslik uchun. */
    private ?int $companyIdCache = null;

    private bool $companyIdResolved = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /** Foydalanuvchi egasi bo'lgan kompaniya (admin uchun). */
    public function ownedCompany(): HasOne
    {
        return $this->hasOne(Company::class, 'user_id');
    }

    /**
     * Egalik qilgan kompaniya, bo'lmasa biriktirilgan kompaniya (sotuvchi).
     * Natija instansiyada keshlanadi — bitta so'rovda ko'pi bilan 1 ta SQL.
     */
    public function companyId(): ?int
    {
        if ($this->companyIdResolved) {
            return $this->companyIdCache;
        }

        $this->companyIdCache = Company::query()
            ->where('user_id', $this->id)
            ->value('id') ?? $this->company_id;
        $this->companyIdResolved = true;

        return $this->companyIdCache;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
