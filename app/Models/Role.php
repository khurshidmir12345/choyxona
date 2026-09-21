<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Support\Permission;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lavozim: kompaniya ichidagi ruxsatlar to'plami. Xodim lavozimga
 * bog'lanadi va lavozimdagi bo'limlargina unga ochiladi.
 */
class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use BelongsToCompany, HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'permissions',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_default' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** @return list<string> */
    public function permissionKeys(): array
    {
        return array_values(array_intersect(Permission::keys(), (array) $this->permissions));
    }

    public function allows(string $permission): bool
    {
        return in_array($permission, (array) $this->permissions, true);
    }

    /**
     * Kompaniyada standart lavozimlar (ofitsant, kassir, menejer) borligini
     * ta'minlaydi. Yangi kompaniya yaratilganda va xodimlar sahifasi
     * ochilganda chaqiriladi — yo'q bo'lsa yaratadi, bor bo'lsa tegmaydi.
     */
    public static function ensureDefaults(int $companyId): void
    {
        $existing = static::query()
            ->forCompany($companyId)
            ->whereNotNull('slug')
            ->pluck('slug')
            ->all();

        foreach (Permission::defaultRoles() as $slug => $role) {
            if (in_array($slug, $existing, true)) {
                continue;
            }

            static::create([
                'company_id' => $companyId,
                'name' => $role['name'],
                'slug' => $slug,
                'permissions' => $role['permissions'],
                'is_default' => true,
            ]);
        }
    }
}
