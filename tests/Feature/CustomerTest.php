<?php

namespace Tests\Feature;

use App\Livewire\Admin\Customers\IndexLivewire as CustomerIndex;
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

    private function sale(array $extra): array
    {
        return array_merge(['uuid' => (string) \Illuminate\Support\Str::uuid(), 'type' => 'takeaway'], $extra);
    }

    public function test_sotuvda_mijoz_va_manzil_saqlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            'type' => 'delivery',
            'customer' => ['name' => 'Akmal aka', 'phone' => '90 123 45 67', 'address' => 'Chilonzor 5'],
            'delivery_address' => 'Yunusobod 12',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1000]],
        ])]])->assertOk()->assertJsonPath('results.0.status', 'created');

        $customer = Customer::sole();
        $order = Order::sole();

        $this->assertSame('+998901234567', $customer->phone);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('Yunusobod 12', $order->delivery_address);
        $this->assertEqualsCanonicalizing(['Yunusobod 12', 'Chilonzor 5'], $customer->knownAddresses());
    }

    public function test_olib_ketishda_manzil_saqlanmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            'customer_id' => $customer->id,
            'delivery_address' => 'Nimadir',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1000]],
        ])]])->assertOk();

        $order = Order::sole();
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertNull($order->delivery_address);
    }

    public function test_bir_xil_telefon_bilan_ikkinchi_mijoz_yaratilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $existing = Customer::factory()->create(['company_id' => $company->id, 'phone' => '+998901234567']);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            'customer' => ['name' => 'Boshqa ism', 'phone' => '+998 90 123-45-67'],
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1000]],
        ])]])->assertOk();

        $this->assertSame(1, Customer::count());
        $this->assertSame($existing->id, Order::sole()->customer_id);
    }

    public function test_boshqa_kompaniyaning_mijozi_biriktirilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $foreign = Customer::factory()->create();

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            'customer_id' => $foreign->id,
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1000]],
        ])]])->assertOk()->assertJsonPath('results.0.status', 'created');

        $this->assertNull(Order::sole()->customer_id);
    }
}
