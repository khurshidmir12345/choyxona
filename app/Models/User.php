<?php

namespace App\Models;

use App\Support\Business;
use App\Support\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_active',
        'telegram_id',
        'telegram_username',
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

    private bool $ownsCompany = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'telegram_id' => 'integer',
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

        $owned = Company::query()
            ->where('user_id', $this->id)
            ->value('id');

        $this->ownsCompany = $owned !== null;
        $this->companyIdCache = $owned ?? $this->company_id;
        $this->companyIdResolved = true;

        return $this->companyIdCache;
    }

    /** Kompaniya egasi — hamma bo'limga kiradi, lavozimga bog'liq emas. */
    public function isOwner(): bool
    {
        $this->companyId();

        return $this->ownsCompany;
    }

    /** Xodim — kompaniyaga biriktirilgan, lekin egasi emas. */
    public function isStaff(): bool
    {
        return ! $this->isOwner() && $this->company_id !== null;
    }

    /**
     * Ochiq bo'limlar ro'yxati. Ega uchun hammasi, xodim uchun lavozimdagilar.
     *
     * @return list<string>
     */
    public function permissions(): array
    {
        if ($this->isOwner()) {
            return Permission::keys();
        }

        return $this->role?->permissionKeys() ?? [];
    }

    public function allows(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    /**
     * Kirgandan keyin ochiladigan sahifa: ruxsat berilgan birinchi bo'lim.
     * Hech narsaga ruxsat bo'lmasa — "ruxsat yo'q" sahifasi.
     */
    public function homeRoute(): string
    {
        $allowed = $this->permissions();
        $hasHall = Business::current()->hasHall();

        foreach (Permission::homeRoutes($hasHall) as $permission => $route) {
            if (in_array($permission, $allowed, true)) {
                return $route;
            }
        }

        return 'no-access';
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id');
    }
}
