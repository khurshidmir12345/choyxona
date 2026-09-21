<?php

namespace App\Livewire\Admin\Employees;

use App\Livewire\Concerns\WithCompany;
use App\Models\Role;
use App\Models\User;
use App\Support\Phone;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Xodimlar: kompaniya egasi telefon raqam va 8 xonali parol bilan hisob
 * ochadi, lavozim biriktiradi. Xodim shu raqam va parol bilan kiradi
 * (brauzerda yoki Telegram mini ilovada), unga faqat lavozimidagi
 * bo'limlar ochiladi.
 */
class IndexLivewire extends Component
{
    use WithCompany, WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $showForm = false;

    public ?int $userId = null;

    public string $name = '';

    public string $phone = '';

    public string $role_id = '';

    public string $password = '';

    public bool $is_active = true;

    public function mount(): void
    {
        Role::ensureDefaults($this->companyId());
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->select(['id', 'name', 'slug', 'is_default'])
            ->forCompany($this->companyId())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    public function createEmployee(): void
    {
        $this->resetForm();
        $this->role_id = (string) ($this->roles->firstWhere('slug', 'waiter')?->id ?? $this->roles->first()?->id ?? '');
        $this->generatePassword();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = $this->staffQuery()->find($id);

        if (! $user) {
            return;
        }

        $this->resetForm();
        $this->userId = $user->id;
        $this->name = (string) $user->name;
        $this->phone = $this->localPhone($user->phone_number);
        $this->role_id = (string) $user->role_id;
        $this->is_active = (bool) $user->is_active;
        $this->showForm = true;
    }

    /** 8 xonali tasodifiy parol — egaga ko'rsatiladi, xodimga aytadi. */
    public function generatePassword(): void
    {
        $this->password = (string) random_int(10000000, 99999999);
    }

    public function save(): void
    {
        $phone = Phone::normalize($this->phone);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^\+?[0-9\s\-()]{9,20}$/'],
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->where('company_id', $this->companyId()),
            ],
            'password' => [$this->userId ? 'nullable' : 'required', 'digits:8'],
            'is_active' => ['boolean'],
        ], [
            'name.required' => 'Xodim ismini kiriting.',
            'phone.required' => 'Telefon raqamni kiriting.',
            'phone.regex' => 'Telefon raqam noto\'g\'ri.',
            'role_id.required' => 'Lavozimni tanlang.',
            'role_id.exists' => 'Bunday lavozim yo\'q.',
            'password.required' => 'Parolni kiriting.',
            'password.digits' => 'Parol 8 ta raqamdan iborat bo\'lsin.',
        ]);

        $taken = User::query()
            ->where('phone_number', $phone)
            ->when($this->userId, fn ($q) => $q->whereKeyNot($this->userId))
            ->exists();

        if ($taken) {
            $this->addError('phone', 'Bu raqam boshqa foydalanuvchida ishlatilgan.');

            return;
        }

        $attributes = [
            'name' => $data['name'],
            'phone_number' => $phone,
            'role_id' => (int) $data['role_id'],
            'is_active' => (bool) $this->is_active,
        ];

        if (filled($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        if ($this->userId) {
            $user = $this->staffQuery()->find($this->userId);

            if (! $user) {
                return;
            }

            $user->update($attributes);
        } else {
            User::create($attributes + [
                'company_id' => $this->companyId(),
                'type' => 'staff',
                'phone_verified_at' => now(),
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Xodim saqlandi.');
    }

    public function toggleActive(int $id): void
    {
        $user = $this->staffQuery()->find($id);

        if (! $user) {
            return;
        }

        $user->update(['is_active' => ! $user->is_active]);
    }

    public function unlinkTelegram(int $id): void
    {
        $user = $this->staffQuery()->find($id);

        if (! $user) {
            return;
        }

        $user->update(['telegram_id' => null, 'telegram_username' => null]);
        $this->dispatch('toast', type: 'success', message: 'Telegram uzildi.');
    }

    public function delete(int $id): void
    {
        $user = $this->staffQuery()->withCount('orders')->find($id);

        if (! $user) {
            return;
        }

        // Buyurtmalari bor xodimni o'chirib bo'lmaydi — tarix buziladi; nofaol qilinadi.
        if ($user->orders_count > 0) {
            $user->update(['is_active' => false]);
            $this->dispatch('toast', type: 'error', message: "Bu xodimda {$user->orders_count} ta buyurtma bor, shuning uchun nofaol qilindi.");

            return;
        }

        $user->forceDelete();
        $this->dispatch('toast', type: 'success', message: 'Xodim o\'chirildi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['userId', 'name', 'phone', 'role_id', 'password']);
        $this->is_active = true;
        $this->resetValidation();
    }

    /** +998901234567 → 901234567 (forma +998 prefiksini o'zi ko'rsatadi). */
    private function localPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return str_starts_with($digits, '998') && strlen($digits) === 12 ? substr($digits, 3) : $digits;
    }

    private function staffQuery()
    {
        return User::query()->where('company_id', $this->companyId());
    }

    public function render()
    {
        $employees = $this->staffQuery()
            ->select(['id', 'name', 'phone_number', 'role_id', 'is_active', 'telegram_id', 'telegram_username', 'created_at'])
            ->with('role:id,name')
            ->when($this->search, function ($q) {
                $digits = preg_replace('/\D/', '', $this->search);

                $q->where(fn ($w) => $w
                    ->where('name', 'like', '%'.$this->search.'%')
                    ->when($digits !== '', fn ($p) => $p->orWhere('phone_number', 'like', '%'.$digits.'%')));
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.employees.index-livewire', [
            'employees' => $employees,
        ]);
    }
}
