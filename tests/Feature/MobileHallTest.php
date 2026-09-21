<?php

namespace Tests\Feature;

use App\Casts\OrderStatusEnum;
use App\Casts\PlaceStatusEnum;
use App\Livewire\Mobile\HallLivewire;
use App\Models\Company;
use App\Models\Order;
use App\Models\Place;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MobileHallTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE_UA = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125 Mobile Safari/537.36';

    private function waiter(): User
    {
        $company = Company::factory()->create();
        $role = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();

        $waiter = User::factory()->create(['company_id' => $company->id, 'role_id' => $role->id, 'type' => 'staff']);
        $this->actingAs($waiter);

        return $waiter;
    }

    public function test_telefondan_zal_mobil_korinishga_yonaltiriladi(): void
    {
        $this->waiter();

        // Kompyuterda o'zgarmaydi
        $this->get(route('cafe.create'))->assertOk()->assertSee('Joylarni sozlash');

        $this->withHeader('User-Agent', self::PHONE_UA)
            ->get(route('cafe.create'))
            ->assertRedirect(route('mobile.hall'));

        // ?desktop=1 bilan telefonda ham kompyuter ko'rinishi
        $this->withHeader('User-Agent', self::PHONE_UA)
            ->get(route('cafe.create', ['desktop' => 1]))
            ->assertOk();

        // Telegram sessiyasida ham (sessiya testda saqlanib qoladi — oxirida)
        $this->withSession(['telegram_app' => true])
            ->get(route('cafe.create'))
            ->assertRedirect(route('mobile.hall'));
    }

    public function test_mobil_zal_chiziladi_va_filtrlar_ishlaydi(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        $free = Place::factory()->for($company)->create(['name' => '1-stol', 'status' => PlaceStatusEnum::Empty]);
        $busy = Place::factory()->for($company)->create(['name' => 'VIP xona', 'status' => PlaceStatusEnum::Busy]);
        Order::factory()->for($company)->create(['place_id' => $busy->id, 'user_id' => $waiter->id, 'status' => OrderStatusEnum::Opened, 'amount' => 50000]);

        $this->withSession(['telegram_app' => true])
            ->get(route('mobile.hall'))
            ->assertOk()
            ->assertSee('css/mobile.css')
            ->assertSee('js/telegram.js')
            ->assertSee('1-stol')
            ->assertSee('VIP xona')
            ->assertDontSee('sidebar-offcanvas');

        Livewire::test(HallLivewire::class)
            ->call('setPlaceFilter', 'free')
            ->assertSee('1-stol')
            ->assertDontSee('VIP xona')
            ->call('setPlaceFilter', 'busy')
            ->assertSee('VIP xona')
            ->assertDontSee('1-stol')
            ->call('setPlaceFilter', 'all')
            ->set('placeSearch', 'vip')
            ->assertSee('VIP xona')
            ->assertDontSee('1-stol');

        // Buyurtmalar yorlig'i: ochiq hisob ko'rinadi
        Livewire::test(HallLivewire::class)
            ->call('setTab', 'orders')
            ->assertSee('Ochiq hisoblar')
            ->assertSee('VIP xona')
            ->assertSee('50 000');
    }

    public function test_mobil_buyurtma_savat_va_hisob_yopish(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        $place = Place::factory()->for($company)->create(['name' => '2-stol']);
        $tea = Product::factory()->for($company)->create(['name' => 'Ko\'k choy', 'sell_price' => 8000, 'discount' => 0, 'current_stock' => 10]);
        $osh = Product::factory()->for($company)->create(['name' => 'Osh', 'sell_price' => 38000, 'discount' => 0, 'current_stock' => 10]);

        $component = Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->assertSee('2-stol')
            ->assertSee('Yangi buyurtma')
            ->call('addProduct', $tea->id)
            ->call('addProduct', $tea->id)
            ->call('addProduct', $osh->id)
            ->assertSet('cart.'.$tea->id.'.quantity', 2)
            ->set('onlyCart', true)
            ->assertSee('Osh')
            ->call('toggleCart')
            ->assertSet('showCart', true)
            ->assertSee('54 000');

        // Saqlash — taxtaga qaytadi, stol band bo'ladi
        $component->call('saveOrder')
            ->assertSet('placeId', null)
            ->assertSet('showCart', false);

        $this->assertSame(PlaceStatusEnum::Busy, $place->refresh()->status);
        $order = Order::where('place_id', $place->id)->opened()->first();
        $this->assertNotNull($order);
        $this->assertSame(54000, (int) $order->amount);

        // Qayta ochib, hisobni yopish — chek havolasi qoladi, mahsulot zaxirasi kamayadi
        Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->assertSet('activeOrderId', $order->id)
            ->call('closeOrder')
            ->assertSet('placeId', null)
            ->assertSet('lastReceiptId', $order->id)
            ->assertSee(route('admin.orders.print', $order->id));

        $this->assertSame(OrderStatusEnum::Done, $order->refresh()->status);
        $this->assertSame(PlaceStatusEnum::Empty, $place->refresh()->status);
        $this->assertSame(8, (int) $tea->refresh()->current_stock);
    }

    public function test_ofitsant_kirganda_telegramdan_mobil_zalga_tushadi(): void
    {
        $company = Company::factory()->create();
        $role = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();
        User::factory()->create(['phone_number' => '+998903334455', 'company_id' => $company->id, 'role_id' => $role->id]);

        $this->withHeader('User-Agent', self::PHONE_UA)
            ->post(route('login'), ['phone_number' => '903334455', 'password' => 'password'])
            ->assertRedirect(route('cafe.create'));

        $this->withHeader('User-Agent', self::PHONE_UA)
            ->get(route('cafe.create'))
            ->assertRedirect(route('mobile.hall'));
    }
}
