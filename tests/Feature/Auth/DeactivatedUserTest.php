<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivatedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_already_authenticated_deactivated_user_is_logged_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $user->deactivate();

        $this->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'Akun Anda sudah dinonaktifkan. Hubungi admin.']);

        $this->assertGuest();
    }

    public function test_an_active_user_keeps_their_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }
}
