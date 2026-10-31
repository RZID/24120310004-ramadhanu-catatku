<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('Masuk');
    }

    public function test_a_user_can_log_in(): void
    {
        $user = User::factory()->create(['email' => 'budi@catatku.test']);

        $this->post(route('login'), [
            'email' => 'budi@catatku.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_wrong_password_keeps_the_guest_out(): void
    {
        User::factory()->create(['email' => 'budi@catatku.test']);

        $this->post(route('login'), [
            'email' => 'budi@catatku.test',
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_an_unknown_email_gives_the_same_message_as_a_wrong_password(): void
    {
        $this->post(route('login'), [
            'email' => 'tidak-ada@catatku.test',
            'password' => 'password',
        ])->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_a_deactivated_user_cannot_log_in(): void
    {
        User::factory()->deactivated()->create(['email' => 'budi@catatku.test']);

        $this->post(route('login'), [
            'email' => 'budi@catatku.test',
            'password' => 'password',
        ])->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);

        $this->assertGuest();
    }

    public function test_the_email_and_password_are_required(): void
    {
        $this->post(route('login'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_an_authenticated_user_cannot_open_the_login_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('login'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_login_attempts_are_throttled(): void
    {
        User::factory()->create(['email' => 'budi@catatku.test']);

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), [
                'email' => 'budi@catatku.test',
                'password' => 'salah-sekali',
            ]);
        }

        $this->post(route('login'), [
            'email' => 'budi@catatku.test',
            'password' => 'password',
        ])->assertStatus(429);
    }
}
