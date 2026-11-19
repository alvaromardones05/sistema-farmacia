<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class InactiveUserLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_users_cannot_log_in(): void
    {
        $user = User::factory()->create(['activo' => false]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_active_users_can_still_log_in(): void
    {
        $user = User::factory()->create(['activo' => true]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }
}
