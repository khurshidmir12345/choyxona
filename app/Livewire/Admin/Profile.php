<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithCompany;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Profil va kompaniya sozlamalari.
 *
 * Diqqat: users jadvalida email ustuni yo'q — ilgari bu forma uni
 * yangilashga urinardi va har safar SQL xatosi bilan tugardi.
 * Email faqat kompaniyada saqlanadi.
 */
class Profile extends Component
{
    use WithFileUploads, WithCompany;

    public string $name = '';

    public string $phone_number = '';

    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public string $company_name = '';

    public string $company_address = '';

    public string $company_phone = '';

    public string $company_email = '';

    public string $company_description = '';

    public string $open_time = '';

    public string $close_time = '';

    public $logo = null;

    public string $tab = 'profile';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = (string) $user->name;
        $this->phone_number = (string) $user->phone_number;

        $company = $this->company();

        if ($company) {
            $this->company_name = (string) $company->name;
            $this->company_address = (string) $company->address;
            $this->company_phone = (string) $company->phone_number;
            $this->company_email = (string) $company->email;
            $this->company_description = (string) $company->description;
            $this->open_time = (string) $company->open_time;
            $this->close_time = (string) $company->close_time;
        }
    }

    private function company(): ?Company
    {
        return Company::query()
            ->select(['id', 'name', 'address', 'phone_number', 'email', 'description', 'logo', 'open_time', 'close_time'])
            ->find($this->companyId());
    }

    public function updateProfile(): void
    {
        $userId = auth()->id();

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => [
                'required', 'string', 'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId)->whereNull('deleted_at'),
            ],
        ], [
            'name.required' => 'Ismni kiriting.',
            'phone_number.required' => 'Telefon raqamni kiriting.',
            'phone_number.unique' => 'Bu raqam boshqa foydalanuvchida ishlatilgan.',
        ]);

        User::query()->whereKey($userId)->update($data);

        $this->dispatch('toast', type: 'success', message: 'Profil yangilandi.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Joriy parolni kiriting.',
            'new_password.required' => 'Yangi parolni kiriting.',
            'new_password.min' => 'Parol kamida 8 ta belgidan iborat bo\'lsin.',
            'new_password.confirmed' => 'Parollar mos kelmadi.',
        ]);

        if (! Hash::check($this->current_password, auth()->user()->password)) {
            $this->addError('current_password', 'Joriy parol noto\'g\'ri.');

            return;
        }

        User::query()->whereKey(auth()->id())->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->dispatch('toast', type: 'success', message: 'Parol yangilandi.');
    }

    public function updateCompany(): void
    {
        $company = $this->company();

        if (! $company) {
            $this->dispatch('toast', type: 'error', message: 'Kompaniya topilmadi.');

            return;
        }

        $data = $this->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:20'],
            'company_email' => ['nullable', 'email', 'max:255'],
            'company_description' => ['nullable', 'string', 'max:1000'],
            'open_time' => ['nullable', 'string', 'max:10'],
            'close_time' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ], [
            'company_name.required' => 'Kompaniya nomini kiriting.',
            'company_email.email' => 'Email to\'g\'ri emas.',
            'logo.uploaded' => 'Logotip yuklanmadi. Kichikroq rasm tanlab qayta urinib ko\'ring.',
            'logo.image' => 'Logotip rasm bo\'lishi kerak.',
            'logo.max' => 'Logotip 2 MB dan katta bo\'lmasin.',
        ]);

        $attributes = [
            'name' => $data['company_name'],
            'address' => $data['company_address'] ?? null,
            'phone_number' => $data['company_phone'] ?: null,
            'email' => $data['company_email'] ?: null,
            'description' => $data['company_description'] ?? null,
            'open_time' => $data['open_time'] ?: null,
            'close_time' => $data['close_time'] ?: null,
        ];

        if ($this->logo) {
            $attributes['logo'] = $this->logo->store('company', 'public');

            $previous = $company->getRawOriginal('logo');
            if (filled($previous) && ! str_starts_with($previous, 'http') && Storage::disk('public')->exists($previous)) {
                Storage::disk('public')->delete($previous);
            }
        }

        $company->update($attributes);
        $this->logo = null;

        $this->dispatch('toast', type: 'success', message: 'Kompaniya ma\'lumotlari yangilandi.');
    }

    public function render()
    {
        return view('livewire.admin.profile', [
            'company' => $this->company(),
        ]);
    }
}
