<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('wallets.index'))->assertRedirect(route('login'));
        $this->post(route('wallets.store'), ['name' => 'Dompet'])->assertRedirect(route('login'));
    }

    public function test_the_index_lists_only_the_authenticated_users_wallets(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $user->wallets()->create(['name' => 'Dompet Tunai']);
        $other->wallets()->create(['name' => 'Dompet Orang Lain']);

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertOk()
            ->assertSee('Dompet Tunai')
            ->assertDontSee('Dompet Orang Lain');
    }

    public function test_the_index_shows_an_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertOk()
            ->assertSee('Belum ada dompet');
    }

    public function test_a_user_can_create_a_wallet(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => 'BCA'])
            ->assertRedirect(route('wallets.index'))
            ->assertSessionHas('status', 'Dompet berhasil ditambahkan.');

        $this->assertDatabaseHas('wallets', ['name' => 'BCA', 'user_id' => $user->id]);
    }

    public function test_the_name_is_required_and_bounded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => str_repeat('a', 101)])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('wallets', 0);
    }

    public function test_the_name_is_unique_per_user(): void
    {
        $user = User::factory()->create();
        $user->wallets()->create(['name' => 'GoPay']);

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => 'GoPay'])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, $user->wallets()->count());
    }

    public function test_two_users_may_use_the_same_wallet_name(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $other->wallets()->create(['name' => 'Dompet Tunai']);

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => 'Dompet Tunai'])
            ->assertRedirect(route('wallets.index'));

        $this->assertDatabaseHas('wallets', ['name' => 'Dompet Tunai', 'user_id' => $user->id]);
    }

    public function test_a_user_can_update_their_own_wallet(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'BCA']);

        $this->actingAs($user)
            ->get(route('wallets.edit', $wallet))
            ->assertOk()
            ->assertSee('BCA');

        $this->actingAs($user)
            ->put(route('wallets.update', $wallet), ['name' => 'BCA Utama'])
            ->assertRedirect(route('wallets.index'));

        $this->assertSame('BCA Utama', $wallet->fresh()?->name);
    }

    public function test_updating_a_wallet_ignores_its_own_name_in_the_unique_rule(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'BCA']);

        $this->actingAs($user)
            ->put(route('wallets.update', $wallet), ['name' => 'BCA'])
            ->assertRedirect(route('wallets.index'));
    }

    public function test_a_wallet_without_transactions_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'GoPay']);

        $this->actingAs($user)
            ->delete(route('wallets.destroy', $wallet))
            ->assertRedirect(route('wallets.index'))
            ->assertSessionHas('status', 'Dompet berhasil dihapus.');

        $this->assertDatabaseMissing('wallets', ['id' => $wallet->id]);
    }

    public function test_a_wallet_with_transactions_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'BCA']);

        Transaction::factory()->create(['user_id' => $user->id, 'wallet_id' => $wallet->id]);

        $this->actingAs($user)->get(route('wallets.index'))->assertOk();

        $this->actingAs($user)
            ->from(route('wallets.index'))
            ->delete(route('wallets.destroy', $wallet))
            ->assertRedirect(route('wallets.index'));

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertSee('Dompet ini masih punya transaksi. Hapus transaksinya lebih dulu.');

        $this->assertDatabaseHas('wallets', ['id' => $wallet->id]);
    }

    public function test_a_user_cannot_read_update_or_delete_another_users_wallet(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $wallet = $other->wallets()->create(['name' => 'Milik Orang Lain']);

        $this->actingAs($user)->get(route('wallets.edit', $wallet))->assertForbidden();
        $this->actingAs($user)->put(route('wallets.update', $wallet), ['name' => 'Bajakan'])->assertForbidden();
        $this->actingAs($user)->delete(route('wallets.destroy', $wallet))->assertForbidden();

        $this->assertSame('Milik Orang Lain', $wallet->fresh()?->name);
    }
}
