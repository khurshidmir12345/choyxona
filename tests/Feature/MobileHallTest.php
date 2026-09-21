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

    public function test_mobil_zal_chiziladi_va_buyurtmalar_yorligi_ishlaydi(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        Place::factory()->for($company)->create(['name' => '1-stol', 'status' => PlaceStatusEnum::Empty]);
        $busy = Place::factory()->for($company)->create(['name' => 'VIP xona', 'status' => PlaceStatusEnum::Busy]);
        Order::factory()->for($company)->create(['place_id' => $busy->id, 'user_id' => $waiter->id, 'status' => OrderStatusEnum::Opened, 'amount' => 50000]);

        $this->withSession(['telegram_app' => true])
            ->get(route('mobile.hall'))
            ->assertOk()
            ->assertSee('css/mobile.css')
            ->assertSee('js/mobile-hall.js')
            ->assertSee('js/telegram.js')
            ->assertSee('1-stol')
            ->assertSee('VIP xona')
            // Filtr brauzerda: stol ro'yxati JSON bo'lib beriladi
            ->assertSee('mBoard(', false)
            ->assertDontSee('sidebar-offcanvas');

        Livewire::test(HallLivewire::class)
            ->call('setTab', 'orders')
            ->assertSee('Ochiq hisoblar')
            ->assertSee('VIP xona')
            ->assertSee('50 000');
    }

    public function test_stol_ochilganda_menyu_json_beriladi_va_savat_brauzerda(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        $place = Place::factory()->for($company)->create(['name' => '2-stol']);
        Product::factory()->for($company)->create(['name' => 'Kok choy', 'sell_price' => 8000, 'discount' => 0, 'current_stock' => 10]);
        Company::factory()->create()->products()->save(Product::factory()->make(['name' => 'Begona mahsulot']));

        Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->assertSee('2-stol')
            ->assertSee('Yangi buyurtma')
            // Menyu JSON: faqat shu kompaniya, savat mantiqi brauzerda (entangle)
            ->assertSee('Kok choy')
            ->assertDontSee('Begona mahsulot')
            ->assertSee('$wire.entangle(\'cart\')', false)
            ->assertSee('mHall(', false);

        // Ro'yxat server tomonida chizilmaydi — har bir bosishda 200 ta mahsulot qayta kelmaydi
        $html = Livewire::test(HallLivewire::class)->call('openTable', $place->id)->html();
        $this->assertStringNotContainsString('wire:click="addProduct', $html);
        $this->assertSame(1, substr_count($html, 'Kok choy'), 'Mahsulot faqat JSON ichida bir marta');
        // @js qo'shtirnoqni \u0022 qilib yozadi
        $this->assertStringContainsString('price\u0022:8000', $html);
    }

    public function test_brauzerdan_kelgan_savat_saqlanadi_narx_bazadan_olinadi(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        $place = Place::factory()->for($company)->create(['name' => '2-stol']);
        $tea = Product::factory()->for($company)->create(['name' => 'Ko\'k choy', 'sell_price' => 8000, 'discount' => 0, 'current_stock' => 10]);
        $osh = Product::factory()->for($company)->create(['name' => 'Osh', 'sell_price' => 38000, 'discount' => 0, 'current_stock' => 10]);
        $foreign = Product::factory()->for(Company::factory()->create())->create(['sell_price' => 1000]);

        // Brauzer narxni 1 so'm deb yuborsa ham, server bazadagi narxni oladi; begona mahsulot tashlab yuboriladi
        $component = Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->set('cart', [
                $tea->id => ['product_id' => $tea->id, 'name' => 'x', 'price' => 1, 'discount' => 90, 'quantity' => 2],
                $osh->id => ['product_id' => $osh->id, 'name' => 'x', 'price' => 1, 'discount' => 0, 'quantity' => 1],
                $foreign->id => ['product_id' => $foreign->id, 'name' => 'x', 'price' => 1, 'discount' => 0, 'quantity' => 5],
            ])
            ->call('saveOrder')
            ->assertSet('placeId', null);

        $this->assertSame(PlaceStatusEnum::Busy, $place->refresh()->status);
        $order = Order::where('place_id', $place->id)->opened()->first();
        $this->assertNotNull($order);
        $this->assertSame(54000, (int) $order->amount);
        $this->assertSame(2, $order->orderDetails()->count());

        // Qayta ochib, hisobni yopish — chek havolasi qoladi, zaxira kamayadi
        Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->assertSet('activeOrderId', $order->id)
            ->assertSet('cart.'.$tea->id.'.quantity', 2)
            ->call('closeOrder')
            ->assertSet('placeId', null)
            ->assertSet('lastReceiptId', $order->id)
            ->assertSee(route('admin.orders.print', $order->id));

        $this->assertSame(OrderStatusEnum::Done, $order->refresh()->status);
        $this->assertSame(PlaceStatusEnum::Empty, $place->refresh()->status);
        $this->assertSame(8, (int) $tea->refresh()->current_stock);
    }

    public function test_bosh_savat_bilan_saqlab_bolmaydi(): void
    {
        $waiter = $this->waiter();
        $company = Company::find($waiter->company_id);
        $place = Place::factory()->for($company)->create();

        Livewire::test(HallLivewire::class)
            ->call('openTable', $place->id)
            ->set('cart', [])
            ->call('saveOrder')
            ->assertSet('placeId', $place->id);

        $this->assertSame(0, Order::count());
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
