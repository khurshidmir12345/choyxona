<?php

namespace Tests\Feature;

use App\Casts\BusinessType;
use App\Livewire\Admin\Setup\BusinessTypeLivewire;
use App\Models\Company;
use App\Support\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Kafe va do'kon rejimlari: o'rnatish ekrani, zalga kirish, so'zlar.
 */
class BusinessTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_turi_tanlanmagan_kompaniya_ornatish_ekraniga_yonaltiriladi(): void
    {
        $user = $this->actingAsOwner();
        Company::where('user_id', $user->id)->update(['business_type' => null]);

        $this->get(route('dashboard'))->assertRedirect(route('setup.business'));
        $this->get(route('setup.business'))->assertOk()->assertSee('Biznesingiz turini tanlang');
    }

    public function test_ornatish_ekranida_tur_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $company->update(['business_type' => null]);

        Livewire::test(BusinessTypeLivewire::class)
            ->call('choose', 'retail')
            ->assertRedirect(route('dashboard'));

        $this->assertSame(BusinessType::Retail, $company->refresh()->business_type);
        $this->get(route('dashboard'))->assertOk();
    }

    public function test_dokon_rejimida_zal_va_joylar_yopiq(): void
    {
        $user = $this->actingAsOwner();
        Company::where('user_id', $user->id)->update(['business_type' => 'retail']);
        Business::forget();

        $this->get(route('cafe.create'))->assertRedirect(route('dashboard'));
        $this->get(route('places.index'))->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Zal (stollar)')
            ->assertDontSee('>Joylar<', false)
            ->assertSee('Savdo POS')
            ->assertSee('Sotuvlar tarixi');
    }

    public function test_kafe_rejimida_zal_ochiq_va_soezlar_kafecha(): void
    {
        $this->actingAsOwner();

        $this->get(route('cafe.create'))->assertOk();
        $this->get(route('places.index'))->assertOk();
        $this->get(route('dashboard'))->assertOk()->assertSee('Zal (stollar)')->assertSee('Tez sotuv');
    }

    public function test_dokon_rejimida_sotuv_turi_dokonda_deb_ataladi(): void
    {
        $user = $this->actingAsOwner();
        Company::where('user_id', $user->id)->update(['business_type' => 'retail']);
        Business::forget();

        $this->get(route('orders.create'))
            ->assertOk()
            ->assertSee("Do'konda")
            ->assertDontSee('Olib ketish');
    }
}
