<?php

namespace Tests\Feature;

use App\Livewire\Admin\Customers\IndexLivewire as CustomerIndex;
use App\Livewire\Admin\Orders\CreateLivewire as QuickSale;
use App\Livewire\Admin\Orders\OrderInCafeLivewire as HallPos;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Place;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_tez_sotuvda_mijoz_va_manzil_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);

        Livewire::test(QuickSale::class)
            ->call('setOrderType', 'delivery')
            ->call('startNewCustomer')
            ->set('newCustomerName', 'Akmal aka')
            ->set('newCustomerPhone', '90 123 45 67')
            ->set('newCustomerAddress', 'Chilonzor 5')
            ->call('createCustomer')
            ->assertHasNoErrors()
            ->assertSet('deliveryAddress', 'Chilonzor 5')
            ->set('deliveryAddress', 'Yunusobod 12')
            ->call('addProduct', $product->id)
            ->call('saveOrder');

        $customer = Customer::sole();
        $order = Order::sole();

        $this->assertSame('+998901234567', $customer->phone);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('Yunusobod 12', $order->delivery_address);

        // Ikkala manzil ham mijozning tarixida turadi.
        $this->assertEqualsCanonicalizing(['Yunusobod 12', 'Chilonzor 5'], $customer->knownAddresses());
    }

    public function test_olib_ketishda_manzil_saqlanmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        Livewire::test(QuickSale::class)
            ->call('selectCustomer', $customer->id)
            ->set('deliveryAddress', 'Nimadir')
            ->call('addProduct', $product->id)
            ->call('saveOrder');

        $order = Order::sole();

        $this->assertSame($customer->id, $order->customer_id);
        $this->assertNull($order->delivery_address);
    }

    public function test_bir_xil_telefon_bilan_ikkinchi_mijoz_yaratilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $existing = Customer::factory()->create(['company_id' => $company->id, 'phone' => '+998901234567']);

        Livewire::test(QuickSale::class)
            ->call('startNewCustomer')
            ->set('newCustomerName', 'Boshqa ism')
            ->set('newCustomerPhone', '+998 90 123-45-67')
            ->call('createCustomer')
            ->assertSet('customerId', $existing->id);

        $this->assertSame(1, Customer::count());
    }

    public function test_zalda_mijoz_buyurtmaga_biriktiriladi_va_qayta_ochilganda_turadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $place = Place::factory()->for($company)->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        Livewire::test(HallPos::class)
            ->call('openTable', $place->id)
            ->call('addProduct', $product->id)
            ->call('selectCustomer', $customer->id)
            ->call('saveOrder');

        $this->assertSame($customer->id, Order::sole()->customer_id);

        Livewire::test(HallPos::class)
            ->call('openTable', $place->id)
            ->assertSet('customerId', $customer->id)
            ->assertSee($customer->name);
    }

    public function test_boshqa_kompaniyaning_mijozi_tanlanmaydi(): void
    {
        $this->actingAsOwner();
        $foreign = Customer::factory()->create();

        Livewire::test(QuickSale::class)
            ->call('selectCustomer', $foreign->id)
            ->assertSet('customerId', null);
    }

    public function test_mijozlar_ruyxatida_qidiruv_va_tahrirlash(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $customer = Customer::factory()->create(['company_id' => $company->id, 'name' => 'Dilnoza', 'phone' => '+998911112233']);
        Customer::factory()->create(['company_id' => $company->id, 'name' => 'Bobur']);

        Livewire::test(CustomerIndex::class)
            ->set('search', '111 22')
            ->assertSee('Dilnoza')
            ->assertDontSee('Bobur')
            ->call('edit', $customer->id)
            ->set('name', 'Dilnoza opa')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('Dilnoza opa', $customer->refresh()->name);
    }
}
