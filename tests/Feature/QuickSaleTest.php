<?php

namespace Tests\Feature;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Casts\ProductStockType;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Sotuv ekrani brauzerda ishlaydi; server tomoni — /api/pos/sync.
 */
class QuickSaleTest extends TestCase
{
    use RefreshDatabase;

    private function sale(array $items, array $extra = []): array
    {
        return array_merge([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'takeaway',
            'items' => $items,
        ], $extra);
    }

    public function test_olib_ketish_buyurtmasi_yakunlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['sell_price' => 12_000, 'discount' => 0, 'current_stock' => 5]);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            ['product_id' => $product->id, 'quantity' => 3, 'price' => 12_000, 'discount' => 0],
        ])]])->assertOk()->assertJsonPath('results.0.status', 'created');

        $order = Order::sole();
        $this->assertSame(OrderTypeEnum::Takeaway, $order->type);
        $this->assertSame(OrderStatusEnum::Done, $order->status);
        $this->assertSame(36_000, $order->total_amount);
        $this->assertSame(2, $product->refresh()->current_stock);
    }

    public function test_sotuv_zaxira_jurnaliga_yoziladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['sell_price' => 5_000, 'current_stock' => 9]);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 5_000],
        ])]])->assertOk();

        $movement = ProductStock::sole();
        $this->assertSame(ProductStockType::Sell, $movement->type);
        $this->assertSame(1, $movement->quantity);
        $this->assertSame($product->id, $movement->product_id);
        $this->assertSame($user->id, $movement->user_id);
        $this->assertStringContainsString('buyurtma #', $movement->note);
    }

    public function test_mahsulot_chegirmasi_qator_summasiga_tasir_qiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['sell_price' => 10_000, 'discount' => 20, 'current_stock' => 10]);

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10_000, 'discount' => 20],
        ])]])->assertOk();

        // 2 x 10 000 = 20 000, undan 20% chegirma = 16 000.
        $this->assertSame(16_000, Order::sole()->total_amount);
    }

    public function test_bush_savat_bilan_buyurtma_yaratilmaydi(): void
    {
        $this->actingAsOwner();

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([])]])->assertStatus(422);

        $this->assertSame(0, Order::count());
    }

    public function test_boshqa_kompaniyaning_mahsuloti_sotilmaydi(): void
    {
        $this->actingAsOwner();
        $foreign = Product::factory()->create();

        $this->postJson(route('pos.sync'), ['sales' => [$this->sale([
            ['product_id' => $foreign->id, 'quantity' => 1, 'price' => 1_000],
        ])]])->assertOk()->assertJsonPath('results.0.status', 'error');

        $this->assertSame(0, Order::count());
    }

    public function test_sotuv_ekrani_ochiladi(): void
    {
        $this->actingAsOwner();

        $this->get(route('orders.create'))->assertOk()->assertSee('Tez sotuv')->assertSee('quick-sale.js');
    }
}
