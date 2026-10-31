<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_a_regular_user_gets_a_forbidden_response(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($user)
            ->post(route('admin.users.deactivate', $user))
            ->assertForbidden();
        $this->actingAs($user)
            ->delete(route('admin.users.destroy', $user))
            ->assertForbidden();
    }

    public function test_an_admin_sees_every_account_with_its_role_and_counts(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Catatku']);
        $user = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@catatku.test']);

        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Admin Catatku')
            ->assertSee('budi@catatku.test')
            ->assertSee('Admin')
            ->assertSee('1 transaksi')
            ->assertSee('Aktif')
            ->assertSee('Akun kamu');
    }

    public function test_an_admin_still_cannot_open_another_users_transaction(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'note' => 'Catatan milik Budi',
        ]);

        $this->actingAs($admin)
            ->get(route('transactions.edit', $transaction))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertDontSee('Catatan milik Budi');
    }

    public function test_an_admin_can_deactivate_and_reactivate_a_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Siti Rahayu']);

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $user))
            ->assertSessionHas('status', 'Akun Siti Rahayu dinonaktifkan.');

        $this->assertTrue((bool) $user->fresh()->isDeactivated());

        $this->actingAs($admin)
            ->post(route('admin.users.activate', $user))
            ->assertSessionHas('status', 'Akun Siti Rahayu diaktifkan kembali.');

        $this->assertFalse((bool) $user->fresh()->isDeactivated());
    }

    public function test_the_same_action_twice_is_refused(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.users.activate', $user))
            ->assertSessionHas('error', 'Akun ini sudah aktif.');

        $user->deactivate();

        $this->actingAs($admin)->post(route('admin.users.deactivate', $user))
            ->assertSessionHas('error', 'Akun ini sudah nonaktif.');
    }

    public function test_an_admin_cannot_deactivate_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.users.deactivate', $admin))
            ->assertSessionHas('error', 'Kamu tidak bisa menonaktifkan akunmu sendiri.');

        $this->assertFalse((bool) $admin->fresh()->isDeactivated());
    }

    public function test_an_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error', 'Kamu tidak bisa menghapus akunmu sendiri.');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_deleting_a_user_removes_their_data(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Rina']);

        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);
        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $user))
            ->assertSessionHas('status', 'Akun Rina dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('transactions', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('wallets', ['user_id' => $user->id]);
        $this->assertDatabaseMissing('categories', ['user_id' => $user->id]);
    }

    public function test_a_deactivated_admin_can_be_deleted_while_one_admin_is_still_active(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();
        $otherAdmin->deactivate();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $otherAdmin))
            ->assertSessionHas('status', 'Akun '.$otherAdmin->name.' dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $otherAdmin->id]);
        $this->assertSame(1, User::where('role', UserRole::Admin->value)->count());
    }
}
