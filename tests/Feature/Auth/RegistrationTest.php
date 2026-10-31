<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('name="_token"', false)
            ->assertSee('Daftar');
    }

    public function test_a_guest_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'rina@catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::firstWhere('email', 'rina@catatku.test');

        $this->assertNotNull($user);
        $this->assertSame('Rina Kartika', $user->name);
        $this->assertSame(UserRole::User, $user->role);
        $this->assertNull($user->deactivated_at);
        $this->assertTrue(Hash::check('Rahasia123', $user->password));
        $this->assertAuthenticatedAs($user);
    }

    public function test_the_email_is_stored_lowercase(): void
    {
        $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'RINA@Catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'rina@catatku.test']);
    }

    public function test_registration_requires_a_name(): void
    {
        $this->post(route('register'), [
            'name' => '',
            'email' => 'rina@catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ])->assertSessionHasErrors('name');

        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_registration_requires_a_valid_email(): void
    {
        $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'bukan-email',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'rina@catatku.test']);

        $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'rina@catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }

    public function test_the_password_must_be_confirmed(): void
    {
        $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'rina@catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia124',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_the_password_must_meet_the_strength_rules(): void
    {
        $this->post(route('register'), [
            'name' => 'Rina Kartika',
            'email' => 'rina@catatku.test',
            'password' => 'rahasiaku',
            'password_confirmation' => 'rahasiaku',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_an_authenticated_user_cannot_open_the_registration_form(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('register'))
            ->assertRedirect(route('dashboard'));
    }
}
