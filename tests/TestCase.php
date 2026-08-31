<?php

namespace Tests;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Kompaniya egasi bo'lgan foydalanuvchi yaratadi va tizimga kiritadi. */
    protected function actingAsOwner(?Company $company = null): User
    {
        $user = User::factory()->create();
        $company ??= Company::factory()->create(['user_id' => $user->id]);

        if ($company->user_id !== $user->id) {
            $company->update(['user_id' => $user->id]);
        }

        $this->actingAs($user);

        return $user;
    }
}
