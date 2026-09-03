<?php

namespace Tests\Feature;

use App\Casts\ProductStockType;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Oflayn kassa: brauzer uchun ma'lumotlar va to'plangan sotuvlarni sinxronlash.
 */
class OfflinePosTest extends TestCase
{
    use RefreshDatabase;

    public function test_oflayn_sahifa_va_malumotlar(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['sell_price' => 5000, 'current_stock' => 7]);
        Product::factory()->create(); // begona kompaniya
        Customer::factory()->create(['company_id' => $company->id, 'name' => 'Dilnoza']);

        $this->get(route('orders.create'))->assertOk()->assertSee('Sinxronlash');

        $this->getJson(route('pos.snapshot'))
            ->assertOk()
            ->assertJsonCount(1, 'products')
            ->assertJsonPath('products.0.id', $product->id)
            ->assertJsonPath('products.0.price', 5000)
            ->assertJsonPath('products.0.stock', 7)
            ->assertJsonPath('products.0.code', $product->code)
            ->assertJsonPath('customers.0.name', 'Dilnoza')
            ->assertJsonPath('company.business_type', 'cafe');
    }

    public function test_sotuvlar_sinxronlanadi_va_takror_yozilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['sell_price' => 5000, 'current_stock' => 10]);

        $sale = [
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'type' => 'delivery',
            'discount' => 10,
            'created_at' => '2026-09-01T10:15:00+05:00',
            'customer' => ['name' => 'Akmal aka', 'phone' => '90 123 45 67', 'address' => 'Chilonzor 5'],
            'delivery_address' => 'Yunusobod 12',
            'items' => [['product_id' => $product->id, 'quantity' => 2, 'price' => 5000, 'discount' => 0]],
        ];

        $this->postJson(route('pos.sync'), ['sales' => [$sale]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'created');

        $order = Order::sole();
        $customer = Customer::sole();

        $this->assertSame('11111111-1111-4111-8111-111111111111', $order->offline_uuid);
        $this->assertSame(9000, $order->total_amount);
        $this->assertSame('Yunusobod 12', $order->delivery_address);
        $this->assertSame($customer->id, $order->customer_id);
        $this->assertSame('+998901234567', $customer->phone);
        $this->assertSame('2026-09-01 05:15:00', $order->created_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame(8, $product->refresh()->current_stock);
        $this->assertSame(ProductStockType::Sell, ProductStock::sole()->type);

        // Xuddi shu UUID qayta kelsa — ikkinchi buyurtma yaratilmaydi.
        $this->postJson(route('pos.sync'), ['sales' => [$sale]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'duplicate')
            ->assertJsonPath('results.0.order_id', $order->id);

        $this->assertSame(1, Order::count());
        $this->assertSame(8, $product->refresh()->current_stock);
    }

    public function test_begona_mahsulot_bilan_sotuv_rad_etiladi(): void
    {
        $this->actingAsOwner();
        $foreign = Product::factory()->create();

        $this->postJson(route('pos.sync'), ['sales' => [[
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'type' => 'takeaway',
            'items' => [['product_id' => $foreign->id, 'quantity' => 1, 'price' => 1000]],
        ]]])
            ->assertOk()
            ->assertJsonPath('results.0.status', 'error');

        $this->assertSame(0, Order::count());
    }

    public function test_mavjud_mijoz_telefon_orqali_topiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 5]);
        $existing = Customer::factory()->create(['company_id' => $company->id, 'phone' => '+998901234567']);

        $this->postJson(route('pos.sync'), ['sales' => [[
            'uuid' => '33333333-3333-4333-8333-333333333333',
            'type' => 'takeaway',
            'customer' => ['name' => 'Boshqa ism', 'phone' => '+998 90 123-45-67'],
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1000]],
        ]]])->assertOk()->assertJsonPath('results.0.status', 'created');

        $this->assertSame(1, Customer::count());
        $this->assertSame($existing->id, Order::sole()->customer_id);
    }

    public function test_mehmon_api_ga_kira_olmaydi(): void
    {
        $this->get(route('pos.snapshot'))->assertRedirect(route('login'));
    }
}
