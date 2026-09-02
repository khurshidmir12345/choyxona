<?php

namespace Tests\Feature;

use App\Livewire\Admin\Orders\CreateLivewire as QuickSale;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tez sotuvdagi yorliqlar: bir mijoz savati tugamasdan ikkinchisiga xizmat.
 */
class QuickSaleTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_yangi_yorliqda_savat_alohida_va_qaytganda_eskisi_turadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        [$a, $b] = Product::factory()->count(2)->forCompany($company)->create(['current_stock' => 10]);
        $customer = Customer::factory()->create(['company_id' => $company->id, 'name' => 'Akmal aka']);

        $c = Livewire::test(QuickSale::class)
            ->call('addProduct', $a->id)
            ->call('selectCustomer', $customer->id)
            ->call('newTab')
            ->assertSet('activeTab', 2)
            ->assertSet('cart', [])
            ->assertSet('customerId', null)
            ->call('addProduct', $b->id)
            ->call('addProduct', $b->id)
            ->call('switchTab', 1)
            ->assertSet('activeTab', 1)
            ->assertSet('customerId', $customer->id)
            ->assertSet('cart.'.$a->id.'.quantity', 1)
            ->assertSee('Akmal aka')
            ->call('switchTab', 2)
            ->assertSet('cart.'.$b->id.'.quantity', 2);

        $this->assertCount(2, $c->get('tabs'));
    }

    public function test_sotuv_yakunlanganda_faqat_shu_yorliq_yopiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        [$a, $b] = Product::factory()->count(2)->forCompany($company)->create(['current_stock' => 10]);

        $c = Livewire::test(QuickSale::class)
            ->call('addProduct', $a->id)
            ->call('newTab')
            ->call('addProduct', $b->id)
            ->call('saveOrder');

        $this->assertSame(1, Order::count());

        // Ikkinchi yorliq yopildi, birinchisi (a mahsuloti bilan) faol bo'ldi.
        $c->assertSet('activeTab', 1)
            ->assertSet('cart.'.$a->id.'.quantity', 1);
        $this->assertCount(1, $c->get('tabs'));
    }

    public function test_yorliqlar_sessiyada_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        Livewire::test(QuickSale::class)
            ->call('addProduct', $product->id)
            ->call('newTab')
            ->call('setOrderType', 'delivery');

        // Sahifa qayta ochilganda (yangi komponent) ikkala yorliq ham turadi.
        $c = Livewire::test(QuickSale::class)
            ->assertSet('activeTab', 2)
            ->assertSet('orderType', 'delivery')
            ->call('switchTab', 1)
            ->assertSet('cart.'.$product->id.'.quantity', 1);

        $this->assertCount(2, $c->get('tabs'));
    }

    public function test_faol_yorliq_yopilsa_boshqasiga_utiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 10]);

        $c = Livewire::test(QuickSale::class)
            ->call('addProduct', $product->id)
            ->call('newTab')
            ->call('closeTab', 2)
            ->assertSet('activeTab', 1)
            ->assertSet('cart.'.$product->id.'.quantity', 1)
            ->call('closeTab', 1)
            ->assertSet('cart', []);

        $this->assertCount(1, $c->get('tabs'));
    }
}
