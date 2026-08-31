<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_kirish_sahifasi_ochiladi(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Xush kelibsiz');
    }

    public function test_kompaniyaga_boglangan_foydalanuvchi_kira_oladi(): void
    {
        $user = User::factory()->create(['phone_number' => '+998901112233']);
        Company::factory()->create(['user_id' => $user->id]);

        // Ilgari bu yerda "role = director" sharti bor edi va roles jadvali
        // bo'sh bo'lgani uchun hech kim kira olmasdi.
        $this->post(route('login'), [
            'phone_number' => '901112233',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_kompaniyasiz_foydalanuvchi_kira_olmaydi(): void
    {
        User::factory()->create(['phone_number' => '+998905556677']);

        $this->post(route('login'), [
            'phone_number' => '905556677',
            'password' => 'password',
        ])->assertSessionHasErrors('phone_number');

        $this->assertGuest();
    }

    public function test_notugri_parol_rad_etiladi(): void
    {
        $user = User::factory()->create(['phone_number' => '+998907778899']);
        Company::factory()->create(['user_id' => $user->id]);

        $this->post(route('login'), [
            'phone_number' => '907778899',
            'password' => 'xato-parol',
        ])->assertSessionHasErrors('phone_number');

        $this->assertGuest();
    }

    public function test_mehmon_ichki_sahifaga_kira_olmaydi(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
