<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionConfigTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_session_cookie_is_hardened(): void
    {
        $this->assertSame('database', config('session.driver'));
        $this->assertSame(120, config('session.lifetime'));
        $this->assertTrue(config('session.http_only'));
        $this->assertSame('lax', config('session.same_site'));
        $this->assertFalse(config('session.encrypt'));
    }

    public function test_the_session_id_changes_after_login(): void
    {
        User::factory()->create(['email' => 'budi@catatku.test']);

        $this->get(route('login'));

        $sessionId = session()->getId();

        $this->post(route('login'), [
            'email' => 'budi@catatku.test',
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->assertNotSame($sessionId, session()->getId());
    }
}
