<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = '123456:TEST-TOKEN';

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set(TelegramService::TOKEN_KEY, self::TOKEN);
    }

    private function initData(array $user = [], ?int $authDate = null): string
    {
        $user += ['id' => 777001, 'first_name' => 'Aziz', 'username' => 'aziz_uz'];

        return app(TelegramService::class)->signInitData([
            'auth_date' => (string) ($authDate ?? now()->timestamp),
            'query_id' => 'AAHdF6IQAAAAAN0XohDhrOrc',
            'user' => json_encode($user, JSON_UNESCAPED_UNICODE),
        ]);
    }

    private function waiter(): User
    {
        $company = Company::factory()->create();
        $role = Role::query()->forCompany($company->id)->where('slug', 'waiter')->first();

        return User::factory()->create([
            'phone_number' => '+998901010101',
            'company_id' => $company->id,
            'role_id' => $role->id,
            'type' => 'staff',
        ]);
    }

    public function test_init_data_imzosi_tekshiriladi(): void
    {
        $service = app(TelegramService::class);

        $valid = $service->validateInitData($this->initData());
        $this->assertNotNull($valid);
        $this->assertSame(777001, $valid['user']['id']);

        $this->assertNull($service->validateInitData($this->initData().'x'));
        $this->assertNull($service->validateInitData('user=%7B%7D&auth_date=1&hash=abc'));
        $this->assertNull($service->validateInitData($this->initData(authDate: now()->subDays(2)->timestamp)));
        $this->assertNull($service->validateInitData(''));
    }

    public function test_kirish_sahifasi_ochiladi(): void
    {
        $this->get(route('telegram.entry'))->assertOk()->assertSee('telegram-web-app.js');
    }

    public function test_ulanmagan_telegram_parol_soraydi(): void
    {
        $this->postJson(route('telegram.auth'), ['init_data' => $this->initData()])
            ->assertOk()
            ->assertJson(['ok' => false, 'need_login' => true, 'name' => 'Aziz']);

        $this->assertGuest();
    }

    public function test_notogri_init_data_rad_etiladi(): void
    {
        $this->postJson(route('telegram.auth'), ['init_data' => 'auth_date=1&hash=zzz'])
            ->assertOk()
            ->assertJson(['ok' => false, 'outside' => true]);
    }

    public function test_telefon_va_parol_bilan_kirib_telegram_boglanadi(): void
    {
        $waiter = $this->waiter();

        $this->postJson(route('telegram.login'), [
            'init_data' => $this->initData(),
            'phone_number' => '901010101',
            'password' => 'password',
        ])->assertOk()->assertJson(['ok' => true, 'redirect' => route('cafe.create')]);

        $this->assertAuthenticatedAs($waiter);
        $this->assertSame(777001, $waiter->fresh()->telegram_id);
        $this->assertSame('aziz_uz', $waiter->fresh()->telegram_username);
        $this->assertTrue(session('telegram_app'));

        // Telegram ichida zal mobil ko'rinishda, to'liq ekran skripti bilan.
        $this->get(route('cafe.create'))->assertRedirect(route('mobile.hall'));
        $this->get(route('mobile.hall'))->assertOk()->assertSee('js/telegram.js')->assertSee('css/mobile.css');
    }

    public function test_boglangan_telegram_parolsiz_kiradi(): void
    {
        $waiter = $this->waiter();
        $waiter->forceFill(['telegram_id' => 777001])->save();

        $this->postJson(route('telegram.auth'), ['init_data' => $this->initData()])
            ->assertOk()
            ->assertJson(['ok' => true, 'redirect' => route('cafe.create')]);

        $this->assertAuthenticatedAs($waiter);
    }

    public function test_nofaol_xodim_telegram_orqali_kira_olmaydi(): void
    {
        $waiter = $this->waiter();
        $waiter->forceFill(['telegram_id' => 777001, 'is_active' => false])->save();

        $this->postJson(route('telegram.auth'), ['init_data' => $this->initData()])
            ->assertOk()
            ->assertJson(['ok' => false, 'need_login' => true]);

        $this->postJson(route('telegram.login'), [
            'init_data' => $this->initData(),
            'phone_number' => '901010101',
            'password' => 'password',
        ])->assertStatus(422);

        $this->assertGuest();
    }

    public function test_notogri_parol_rad_etiladi(): void
    {
        $this->waiter();

        $this->postJson(route('telegram.login'), [
            'init_data' => $this->initData(),
            'phone_number' => '901010101',
            'password' => 'xato',
        ])->assertStatus(422)->assertJson(['ok' => false]);

        $this->assertGuest();
    }

    public function test_webhook_start_ga_tugma_yuboradi(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
        Setting::set(TelegramService::SECRET_KEY, 'sekret');

        $this->postJson(route('telegram.webhook'), [
            'message' => ['chat' => ['id' => 55], 'text' => '/start'],
        ], ['X-Telegram-Bot-Api-Secret-Token' => 'sekret'])->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/bot'.self::TOKEN.'/sendMessage')
                && $request['chat_id'] === 55
                && $request['reply_markup']['inline_keyboard'][0][0]['web_app']['url'] === route('telegram.entry');
        });
    }

    public function test_webhook_notogri_sir_bilan_rad_etiladi(): void
    {
        Http::fake();
        Setting::set(TelegramService::SECRET_KEY, 'sekret');

        $this->postJson(route('telegram.webhook'), [
            'message' => ['chat' => ['id' => 55], 'text' => '/start'],
        ], ['X-Telegram-Bot-Api-Secret-Token' => 'boshqa'])->assertForbidden();

        Http::assertNothingSent();
    }
}
