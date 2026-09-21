<?php

namespace App\Livewire\Admin\Roles;

use App\Livewire\Concerns\WithCompany;
use App\Models\Role;
use App\Support\Permission;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Lavozimlar: ruxsatlar to'plami. Ofitsant, kassir va menejer standart,
 * ularning ruxsatlarini ega o'zgartiradi, lekin o'chira olmaydi.
 * Yangi lavozim ham qo'shiladi (masalan, "Oshpaz").
 */
class IndexLivewire extends Component
{
    use WithCompany;

    public bool $showForm = false;

    public ?int $roleId = null;

    public string $name = '';

    /** @var array<string, bool> ruxsat => yoqilganmi */
    public array $permissions = [];

    public function mount(): void
    {
        Role::ensureDefaults($this->companyId());
    }

    public function createRole(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $role = $this->roleQuery()->find($id);

        if (! $role) {
            return;
        }

        $this->resetForm();
        $this->roleId = $role->id;
        $this->name = (string) $role->name;

        foreach ($role->permissionKeys() as $key) {
            $this->permissions[$key] = true;
        }

        $this->showForm = true;
    }

    public function toggleAll(bool $on): void
    {
        foreach (Permission::keys() as $key) {
            $this->permissions[$key] = $on;
        }
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('roles', 'name')
                    ->where('company_id', $this->companyId())
                    ->ignore($this->roleId),
            ],
        ], [
            'name.required' => 'Lavozim nomini kiriting.',
            'name.unique' => 'Bunday lavozim allaqachon bor.',
        ]);

        $selected = array_values(array_filter(
            Permission::keys(),
            fn (string $key) => ! empty($this->permissions[$key]),
        ));

        if ($this->roleId) {
            $role = $this->roleQuery()->find($this->roleId);

            if (! $role) {
                return;
            }

            $role->update(['name' => $data['name'], 'permissions' => $selected]);
        } else {
            Role::create([
                'company_id' => $this->companyId(),
                'name' => $data['name'],
                'permissions' => $selected,
                'is_default' => false,
            ]);
        }

        $this->closeForm();
        $this->dispatch('toast', type: 'success', message: 'Lavozim saqlandi.');
    }

    public function delete(int $id): void
    {
        $role = $this->roleQuery()->withCount('users')->find($id);

        if (! $role) {
            return;
        }

        if ($role->is_default) {
            $this->dispatch('toast', type: 'error', message: 'Standart lavozimni o\'chirib bo\'lmaydi.');

            return;
        }

        if ($role->users_count > 0) {
            $this->dispatch('toast', type: 'error', message: "Bu lavozimda {$role->users_count} ta xodim bor.");

            return;
        }

        $role->delete();
        $this->dispatch('toast', type: 'success', message: 'Lavozim o\'chirildi.');
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['roleId', 'name']);
        $this->permissions = array_fill_keys(Permission::keys(), false);
        $this->resetValidation();
    }

    private function roleQuery()
    {
        return Role::query()->forCompany($this->companyId());
    }

    public function render()
    {
        $roles = $this->roleQuery()
            ->withCount('users')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        return view('livewire.admin.roles.index-livewire', [
            'roles' => $roles,
            'catalog' => Permission::all(),
        ]);
    }
}
