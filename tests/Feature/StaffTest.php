<?php

namespace Tests\Feature;

use App\Livewire\Admin\Employees\IndexLivewire as Employees;
use App\Livewire\Admin\Roles\IndexLivewire as Roles;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    private function staff(Company $company, string $slug, array $attributes = []): User
    {
        $role = Role::query()->forCompany($company->id)->where('slug', $slug)->firstOrFail();

        return User::factory()->create($attributes + [
            'company_id' => $company->id,
            'role_id' => $role->id,
            'type' => 'staff',
        ]);
    }

    public function test_kompaniya_bilan_standart_lavozimlar_yaratiladi(): void
    {
        $company = Company::factory()->create();

        $this->assertEqualsCanonicalizing(
            ['waiter', 'cashier', 'manager'],
            Role::query()->forCompany($company->id)->pluck('slug')->all(),
        );

        $waiter = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();
        $this->assertSame([Permission::HALL], $waiter->permissionKeys());
        $this->assertTrue($waiter->is_default);
    }

    public function test_ega_xodim_yaratadi_va_xodim_kiradi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $waiterRole = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();

        Livewire::test(Employees::class)
            ->call('createEmployee')
            ->set('name', 'Aziz')
            ->set('phone', '90 777 66 55')
            ->set('role_id', (string) $waiterRole->id)
            ->set('password', '12345678')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('showForm', false);

        $staff = User::where('phone_number', '+998907776655')->first();
        $this->assertNotNull($staff);
        $this->assertSame($company->id, $staff->company_id);
        $this->assertSame($waiterRole->id, $staff->role_id);
        $this->assertTrue(Hash::check('12345678', $staff->password));

        auth()->logout();

        // Ofitsant faqat zalga kiradi — kirgandan keyin to'g'ri zalga tushadi.
        $this->post(route('login'), ['phone_number' => '907776655', 'password' => '12345678'])
            ->assertRedirect(route('cafe.create'));
        $this->assertAuthenticatedAs($staff);
    }

    public function test_parol_8_ta_raqam_bolishi_shart(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $roleId = Role::query()->forCompany($company->id)->where('slug', 'waiter')->value('id');

        Livewire::test(Employees::class)
            ->call('createEmployee')
            ->set('name', 'Aziz')
            ->set('phone', '901112233')
            ->set('role_id', (string) $roleId)
            ->set('password', 'abcd1234')
            ->call('save')
            ->assertHasErrors(['password' => 'digits']);

        Livewire::test(Employees::class)
            ->call('createEmployee')
            ->set('name', 'Aziz')
            ->set('phone', '901112233')
            ->set('role_id', (string) $roleId)
            ->set('password', '1234')
            ->call('save')
            ->assertHasErrors(['password' => 'digits']);
    }

    public function test_ofitsant_faqat_zalni_koradi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $waiter = $this->staff($company, 'waiter');

        $this->actingAs($waiter);

        $this->get(route('cafe.create'))->assertOk();

        // Boshqa bo'limlar yopiq — o'ziga ochiq bo'limga qaytariladi.
        $this->get(route('dashboard'))->assertRedirect(route('cafe.create'));
        $this->get(route('orders.index'))->assertRedirect(route('cafe.create'));
        $this->get(route('employees.index'))->assertRedirect(route('cafe.create'));
        $this->get(route('admin.profile'))->assertRedirect(route('cafe.create'));
        $this->get(route('pos.snapshot'), ['Accept' => 'application/json'])->assertForbidden();

        // Sidebar'da faqat zal
        $html = $this->get(route('cafe.create'))->getContent();
        $this->assertStringContainsString('Zal (stollar)', $html);
        $this->assertStringNotContainsString(route('dashboard'), $html);
        $this->assertStringNotContainsString(route('employees.index'), $html);
        $this->assertStringNotContainsString(route('admin.profile'), $html);
    }

    public function test_kassir_hamma_narsani_koradi_lekin_xodim_va_profilni_emas(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $cashier = $this->staff($company, 'cashier');

        $this->actingAs($cashier);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('orders.create'))->assertOk();
        $this->get(route('orders.index'))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('expenses.index'))->assertOk();

        $this->get(route('employees.index'))->assertRedirect(route('dashboard'));
        $this->get(route('roles.index'))->assertRedirect(route('dashboard'));
        $this->get(route('admin.profile'))->assertRedirect(route('dashboard'));
    }

    public function test_menejer_hammasiga_kiradi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $manager = $this->staff($company, 'manager');

        $this->actingAs($manager);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('employees.index'))->assertOk();
        $this->get(route('roles.index'))->assertOk();
        $this->get(route('admin.profile'))->assertOk();
    }

    public function test_lavozim_ruxsati_yoqilsa_bolim_ochiladi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $waiterRole = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();
        $waiter = $this->staff($company, 'waiter');

        // Ega ofitsantga "Sotuv tarixi"ni ochib beradi.
        Livewire::test(Roles::class)
            ->call('edit', $waiterRole->id)
            ->assertSet('permissions.hall', true)
            ->assertSet('permissions.orders', false)
            ->set('permissions.orders', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertEqualsCanonicalizing([Permission::HALL, Permission::ORDERS], $waiterRole->fresh()->permissionKeys());

        $this->actingAs($waiter->fresh());
        $this->get(route('orders.index'))->assertOk();
    }

    public function test_standart_lavozim_ochirilmaydi_yangisi_ochiriladi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $waiterRole = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();

        Livewire::test(Roles::class)->call('delete', $waiterRole->id);
        $this->assertNotNull($waiterRole->fresh());

        Livewire::test(Roles::class)
            ->call('createRole')
            ->set('name', 'Oshpaz')
            ->set('permissions.stock', true)
            ->call('save')
            ->assertHasNoErrors();

        $cook = Role::query()->forCompany($company->id)->where('name', 'Oshpaz')->first();
        $this->assertSame([Permission::STOCK], $cook->permissionKeys());

        Livewire::test(Roles::class)->call('delete', $cook->id);
        $this->assertNull($cook->fresh());
    }

    public function test_nofaol_xodim_kira_olmaydi(): void
    {
        $owner = $this->actingAsOwner();
        $company = Company::where('user_id', $owner->id)->first();
        $waiter = $this->staff($company, 'waiter', ['phone_number' => '+998905551122', 'is_active' => false]);

        auth()->logout();

        $this->post(route('login'), ['phone_number' => '905551122', 'password' => 'password'])
            ->assertSessionHasErrors('phone_number');
        $this->assertGuest();
    }

    public function test_boshqa_kompaniya_xodimini_tahrirlab_bolmaydi(): void
    {
        $this->actingAsOwner();
        $other = Company::factory()->create();
        $stranger = $this->staff($other, 'waiter');

        Livewire::test(Employees::class)
            ->call('edit', $stranger->id)
            ->assertSet('showForm', false);

        Livewire::test(Employees::class)->call('delete', $stranger->id);
        $this->assertNotNull($stranger->fresh());
    }

    public function test_ega_uchun_xodimlar_sahifasi_chiziladi(): void
    {
        $this->actingAsOwner();

        $this->get(route('employees.index'))->assertOk()->assertSee('Xodimlar');
        $this->get(route('roles.index'))->assertOk()->assertSee('Ofitsant')->assertSee('Kassir')->assertSee('Menejer');
    }
}
