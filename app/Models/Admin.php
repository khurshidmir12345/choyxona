<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Boshqaruv paneli administratori. Yagona manba — .env (ADMIN_LOGIN, ADMIN_PASSWORD);
 * bu jadval faqat sessiya uchun kerak.
 */
class Admin extends Authenticatable implements FilamentUser
{
    protected $fillable = ['name', 'login', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
