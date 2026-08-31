<?php

namespace Tests\Feature;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Casts\ProductStockType;
use App\Livewire\Admin\Orders\CreateLivewire;
use App\Models\Company;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuickSaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_olib_ketish_buyurtmasi_yakunlanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 12_000, 'discount' => 0, 'current_stock' => 5,
        ]);

        Livewire::test(CreateLivewire::class)
            ->set('orderType', 'takeaway')
            ->call('addProduct', $product->id)
            ->call('updateQuantity', $product->id, 3)
            ->call('saveOrder')
            ->assertRedirect();

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
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 5_000, 'discount' => 0, 'current_stock' => 9,
        ]);

        Livewire::test(CreateLivewire::class)
            ->call('addProduct', $product->id)
            ->call('saveOrder');

        $movement = ProductStock::sole();
        $this->assertSame(ProductStockType::Sell, $movement->type);
        $this->assertSame(1, $movement->quantity);
        $this->assertSame($product->id, $movement->product_id);
    }

    public function test_mahsulot_chegirmasi_qator_summasiga_tasir_qiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 10_000, 'discount' => 20, 'current_stock' => 10,
        ]);

        Livewire::test(CreateLivewire::class)
            ->call('addProduct', $product->id)
            ->call('updateQuantity', $product->id, 2)
            ->call('saveOrder');

        // 2 x 10 000 = 20 000, undan 20% chegirma = 16 000.
        $this->assertSame(16_000, Order::sole()->total_amount);
    }

    public function test_bush_savat_bilan_buyurtma_yaratilmaydi(): void
    {
        $this->actingAsOwner();

        Livewire::test(CreateLivewire::class)->call('saveOrder');

        $this->assertSame(0, Order::count());
    }

    public function test_boshqa_kompaniyaning_mahsuloti_savatga_tushmaydi(): void
    {
        $this->actingAsOwner();
        $foreign = Product::factory()->create();

        Livewire::test(CreateLivewire::class)
            ->call('addProduct', $foreign->id)
            ->assertSet('cart', []);
    }
}
