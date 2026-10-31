<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unknown_url_renders_the_indonesian_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan')
            ->assertSee('404');
    }

    public function test_a_forbidden_page_renders_the_indonesian_403_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.users.index'))
            ->assertForbidden()
            ->assertSee('Tidak punya akses')
            ->assertSee('403');
    }

    public function test_a_deleted_wallet_renders_the_404_page(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);

        $wallet->delete();

        $this->actingAs($user)
            ->get(route('wallets.edit', $wallet->id))
            ->assertNotFound()
            ->assertSee('Halaman tidak ditemukan');
    }

    public function test_the_remaining_error_pages_render(): void
    {
        $this->assertStringContainsString('Sesi sudah kedaluwarsa', (string) view('errors.419')->render());
        $this->assertStringContainsString('Terlalu banyak percobaan', (string) view('errors.429')->render());
        $this->assertStringContainsString('Terjadi kesalahan', (string) view('errors.500')->render());
    }
}
