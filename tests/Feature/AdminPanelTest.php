<?php

namespace Tests\Feature;

use App\Filament\Auth\AdminLogin;
use App\Filament\Resources\Companies\Pages\CreateCompany;
use App\Filament\Resources\Companies\Pages\ListCompanies;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Admin;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Boshqaruv paneli: .env orqali kirish, biznes + egasi yaratish, sahifalar.
 */
class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['admin.login' => 'boss', 'admin.password' => 'juda-maxfiy', 'admin.name' => 'Boss']);
    }

    private function loginAdmin(): Admin
    {
        $admin = Admin::create(['name' => 'Boss', 'login' => 'boss', 'password' => 'juda-maxfiy']);
        $this->actingAs($admin, 'admin');

        return $admin;
    }

    public function test_env_dagi_login_parol_bilan_kiriladi(): void
    {
        Livewire::test(AdminLogin::class)
            ->fillForm(['login' => 'boss', 'password' => 'juda-maxfiy'])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticated('admin');
        $this->assertTrue(Hash::check('juda-maxfiy', Admin::sole()->password));
    }

    public function test_notugri_parol_bilan_kirilmaydi(): void
    {
        Livewire::test(AdminLogin::class)
            ->fillForm(['login' => 'boss', 'password' => 'xato'])
            ->call('authenticate')
            ->assertHasFormErrors(['login']);

        $this->assertGuest('admin');
        $this->assertSame(0, Admin::count());
    }

    public function test_oddiy_foydalanuvchi_panelga_kira_olmaydi(): void
    {
        $this->actingAsOwner();

        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_panel_sahifalari_ochiladi(): void
    {
        $this->loginAdmin();
        Company::factory()->create();

        $this->get('/admin')->assertOk()->assertSee('Bizneslar');
        $this->get('/admin/companies')->assertOk();
        $this->get('/admin/users')->assertOk();
        $this->get('/admin/orders')->assertOk();

        Livewire::test(ListCompanies::class)->assertOk();
        Livewire::test(ListUsers::class)->assertOk();
    }

    public function test_biznes_va_egasi_bitta_formada_yaratiladi(): void
    {
        $this->loginAdmin();

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Akssesuar do\'koni',
                'business_type' => 'retail',
                'owner_name' => 'Jasur',
                'owner_phone' => '90 111 22 33',
                'owner_password' => 'parol123',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $company = Company::sole();
        $owner = User::sole();

        $this->assertSame('retail', $company->business_type->value);
        $this->assertSame($owner->id, $company->user_id);
        $this->assertSame('+998901112233', $owner->phone_number);
        $this->assertTrue(Hash::check('parol123', $owner->password));

        // Yaratilgan egasi tizimga kira oladi.
        $this->post(route('login'), ['phone_number' => '901112233', 'password' => 'parol123'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_band_telefon_bilan_biznes_yaratilmaydi(): void
    {
        $this->loginAdmin();
        User::factory()->create(['phone_number' => '+998901112233']);

        Livewire::test(CreateCompany::class)
            ->fillForm([
                'name' => 'Ikkinchi',
                'business_type' => 'cafe',
                'owner_name' => 'Kimdir',
                'owner_phone' => '+998 90 111 22 33',
                'owner_password' => 'parol123',
            ])
            ->call('create')
            ->assertHasFormErrors(['owner_phone']);

        $this->assertSame(0, Company::count());
    }
}
