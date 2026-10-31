<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_logout_invalidates_the_session_and_the_csrf_token(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $sessionId = session()->getId();
        $token = session()->token();

        $this->post(route('logout'));

        $this->assertNotSame($sessionId, session()->getId());
        $this->assertNotSame($token, session()->token());
    }

    public function test_a_guest_cannot_log_out(): void
    {
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_the_logout_form_uses_post_and_csrf(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('name="_token"', false);
    }
}
