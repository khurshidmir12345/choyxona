<?php

namespace App\Filament\Auth;

use App\Models\Admin;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Kirish: login va parol .env dan (ADMIN_LOGIN, ADMIN_PASSWORD).
 * Mos kelsa admins jadvalidagi yozuv yangilanadi va sessiya ochiladi.
 */
class AdminLogin extends Login
{
    public function authenticate(): ?LoginResponse
    {
        $data = $this->form->getState();

        $this->syncAdminFromEnv((string) ($data['login'] ?? ''), (string) ($data['password'] ?? ''));

        return parent::authenticate();
    }

    private function syncAdminFromEnv(string $login, string $password): void
    {
        $envLogin = (string) config('admin.login');
        $envPassword = (string) config('admin.password');

        if ($envLogin === '' || $envPassword === '') {
            return;
        }

        if (! hash_equals($envLogin, $login) || ! hash_equals($envPassword, $password)) {
            return;
        }

        $admin = Admin::query()->firstOrNew(['login' => $envLogin]);
        $admin->name = (string) config('admin.name', 'Administrator');

        if (! $admin->exists || ! Hash::check($password, $admin->password)) {
            $admin->password = $password;
        }

        $admin->save();
    }

    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('login')
            ->label('Login')
            ->required()
            ->autocomplete('username')
            ->autofocus();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'login' => $data['login'],
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => 'Login yoki parol noto\'g\'ri.',
        ]);
    }

    public function getHeading(): string
    {
        return 'Kirish';
    }

    public function getSubheading(): ?string
    {
        return 'Kirish ma\'lumotlari .env faylida (ADMIN_LOGIN, ADMIN_PASSWORD).';
    }
}
