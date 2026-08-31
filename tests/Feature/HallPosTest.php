<?php

namespace Tests\Feature;

use App\Casts\OrderStatusEnum;
use App\Casts\OrderTypeEnum;
use App\Casts\PlaceStatusEnum;
use App\Livewire\Admin\Orders\OrderInCafeLivewire;
use App\Models\Company;
use App\Models\Order;
use App\Models\Place;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HallPosTest extends TestCase
{
    use RefreshDatabase;

    public function test_stol_ochilganda_buyurtma_darhol_yaratilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $place = Place::factory()->for($company)->create();

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->assertSet('activeOrderId', null);

        $this->assertSame(0, Order::count());
        $this->assertSame(PlaceStatusEnum::Empty, $place->refresh()->status);
    }

    public function test_hisob_yopilganda_zaxira_kamayadi_va_stol_bushaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $place = Place::factory()->for($company)->create();
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 20_000,
            'discount' => 0,
            'current_stock' => 10,
        ]);

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->call('addProduct', $product->id)
            ->call('addProduct', $product->id)
            ->call('closeOrder')
            ->assertRedirect();

        $order = Order::sole();

        $this->assertSame(OrderStatusEnum::Done, $order->status);
        $this->assertSame(OrderTypeEnum::Cafe, $order->type);
        $this->assertSame(40_000, $order->amount);
        $this->assertSame(40_000, $order->total_amount);
        $this->assertSame(1, $order->orderDetails()->count());
        $this->assertSame(2, $order->orderDetails()->sole()->quantity);

        // Zaxira yechilishi kerak — ilgari zal buyurtmalarida bu bo'lmagan.
        $this->assertSame(8, $product->refresh()->current_stock);
        $this->assertSame(PlaceStatusEnum::Empty, $place->refresh()->status);
    }

    public function test_buyurtma_chegirmasi_qullaniladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $place = Place::factory()->for($company)->create();
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 10_000, 'discount' => 0, 'current_stock' => 50,
        ]);

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->call('addProduct', $product->id)
            ->set('discount', 25)
            ->call('saveOrder');

        $order = Order::sole();
        $this->assertSame(10_000, $order->amount);
        $this->assertSame(7_500, $order->total_amount);
        $this->assertSame(25, $order->discount);
    }

    public function test_saqlangan_hisob_qayta_ochilganda_savat_tiklanadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $place = Place::factory()->for($company)->create();
        $product = Product::factory()->forCompany($company)->create([
            'sell_price' => 15_000, 'discount' => 0, 'current_stock' => 50,
        ]);

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->call('addProduct', $product->id)
            ->call('updateQuantity', $product->id, 3)
            ->set('discount', 10)
            ->call('saveOrder');

        $this->assertSame(PlaceStatusEnum::Busy, $place->refresh()->status);

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->assertSet('discount', 10)
            ->assertSet('cart.'.$product->id.'.quantity', 3);
    }

    public function test_stolni_bushatish_ochiq_hisobni_bekor_qiladi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $place = Place::factory()->for($company)->create();
        $product = Product::factory()->forCompany($company)->create(['current_stock' => 20]);

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $place->id)
            ->call('addProduct', $product->id)
            ->call('saveOrder')
            ->call('clearTable');

        $this->assertSame(0, Order::count());
        $this->assertSame(20, $product->refresh()->current_stock);
        $this->assertSame(PlaceStatusEnum::Empty, $place->refresh()->status);
    }

    public function test_boshqa_kompaniyaning_stoli_ochilmaydi(): void
    {
        $this->actingAsOwner();
        $otherPlace = Place::factory()->create();

        Livewire::test(OrderInCafeLivewire::class)
            ->call('openTable', $otherPlace->id)
            ->assertSet('placeId', null);
    }
}
