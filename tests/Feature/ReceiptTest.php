<?php

namespace Tests\Feature;

use App\Livewire\Admin\Orders\OrderCompleted;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReceiptTest extends TestCase
{
    use RefreshDatabase;

    public function test_yopilgan_buyurtma_cheki_chiqadi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();
        $product = Product::factory()->forCompany($company)->create(['name' => 'Somsa']);

        $order = Order::create([
            'company_id' => $company->id, 'user_id' => $user->id,
            'amount' => 30_000, 'total_amount' => 27_000, 'discount' => 10,
            'type' => 'takeaway', 'status' => 'done',
        ]);
        OrderDetail::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'worker_id' => $user->id,
            'quantity' => 3, 'price' => 10_000, 'discount' => 0, 'total_amount' => 30_000,
        ]);

        Livewire::test(OrderCompleted::class, ['id' => $order->id])
            ->assertSee('Somsa')
            ->assertSee('27 000')
            ->assertSee($company->name);
    }

    public function test_ochiq_buyurtma_uchun_chek_berilmaydi(): void
    {
        $user = $this->actingAsOwner();
        $company = Company::where('user_id', $user->id)->first();

        $order = Order::create([
            'company_id' => $company->id, 'user_id' => $user->id,
            'amount' => 0, 'total_amount' => 0, 'discount' => 0,
            'type' => 'cafe', 'status' => 'opened',
        ]);

        Livewire::test(OrderCompleted::class, ['id' => $order->id])
            ->assertSee('hali yopilmagan');
    }

    public function test_begona_kompaniyaning_cheki_kurinmaydi(): void
    {
        $this->actingAsOwner();
        $other = Company::factory()->create();

        $order = Order::create([
            'company_id' => $other->id, 'user_id' => $other->user_id,
            'amount' => 0, 'total_amount' => 0, 'discount' => 0,
            'type' => 'cafe', 'status' => 'done',
        ]);

        Livewire::test(OrderCompleted::class, ['id' => $order->id])
            ->assertSee('topilmadi');
    }
}
