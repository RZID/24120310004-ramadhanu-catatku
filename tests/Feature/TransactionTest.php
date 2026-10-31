<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('transactions.index'))->assertRedirect(route('login'));
    }

    public function test_the_index_lists_only_the_authenticated_users_transactions(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());
        [$otherWallet, $otherExpense] = $this->fixture(User::factory()->create());

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
            'note' => 'Makan siang saya',
        ]);

        Transaction::factory()->create([
            'user_id' => $otherWallet->user_id,
            'wallet_id' => $otherWallet->id,
            'category_id' => $otherExpense->id,
            'type' => TransactionType::Expense,
            'note' => 'Makan siang orang lain',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('Makan siang saya')
            ->assertDontSee('Makan siang orang lain');
    }

    public function test_the_index_formats_the_amount_as_rupiah(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
            'amount' => 1250000,
            'transacted_on' => '2026-10-05',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertSee('Rp 1.250.000')
            ->assertSee('5 Okt 2026');
    }

    public function test_the_list_can_be_filtered_by_month_type_and_category(): void
    {
        [$wallet, $expense, $income] = $this->fixture($user = User::factory()->create());

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
            'note' => 'Agustus makan',
            'transacted_on' => '2026-08-10',
        ]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $income->id,
            'type' => TransactionType::Income,
            'note' => 'September gaji',
            'transacted_on' => '2026-09-10',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index', ['month' => '2026-08']))
            ->assertSee('Agustus makan')
            ->assertDontSee('September gaji');

        $this->actingAs($user)
            ->get(route('transactions.index', ['type' => 'income']))
            ->assertSee('September gaji')
            ->assertDontSee('Agustus makan');

        $this->actingAs($user)
            ->get(route('transactions.index', ['category_id' => $expense->id]))
            ->assertSee('Agustus makan')
            ->assertDontSee('September gaji');
    }

    public function test_the_filters_survive_pagination(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        Transaction::factory()->count(16)->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
            'transacted_on' => '2026-08-15',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('Halaman 1 dari 2')
            ->assertSee('page=2', false)
            ->assertSee('month=2026-08', false);
    }

    public function test_invalid_filters_are_rejected_before_reaching_the_query(): void
    {
        $user = User::factory()->create();
        $this->fixture($user);

        $this->actingAs($user)
            ->get(route('transactions.index', ['month' => '2026-8-1']))
            ->assertSessionHasErrors('month');

        $this->actingAs($user)
            ->get(route('transactions.index', ['type' => 'transfer']))
            ->assertSessionHasErrors('type');
    }

    public function test_a_filter_may_not_reference_another_users_category(): void
    {
        $user = User::factory()->create();
        $this->fixture($user);

        [, $otherCategory] = $this->fixture(User::factory()->create());

        $this->actingAs($user)
            ->get(route('transactions.index', ['category_id' => $otherCategory->id]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_the_create_form_needs_a_wallet_and_a_category_first(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('transactions.create'))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('error', 'Tambahkan minimal satu dompet dan satu kategori sebelum mencatat transaksi.');
    }

    public function test_a_user_can_create_a_transaction(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status', 'Transaksi berhasil dicatat.');

        $transaction = $user->transactions()->sole();

        $this->assertSame(45000, $transaction->amount);
        $this->assertSame(TransactionType::Expense, $transaction->type);
        $this->assertSame($expense->id, $transaction->category_id);
    }

    public function test_the_amount_must_be_a_plain_integer(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['amount' => '1.250.000']))
            ->assertSessionHasErrors('amount');

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['amount' => '0']))
            ->assertSessionHasErrors('amount');

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['amount' => '2000000000000']))
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_the_date_may_not_be_in_the_future(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, [
                'transacted_on' => now()->addDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('transacted_on');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_the_note_is_limited_and_optional(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, [
                'note' => str_repeat('a', 256),
            ]))
            ->assertSessionHasErrors('note');

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['note' => '']))
            ->assertRedirect(route('transactions.index'));

        $this->assertNull($user->transactions()->sole()->note);
    }

    public function test_the_wallet_and_category_must_belong_to_the_authenticated_user(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());
        [$otherWallet, $otherExpense] = $this->fixture(User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['wallet_id' => $otherWallet->id]))
            ->assertSessionHasErrors('wallet_id');

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, ['category_id' => $otherExpense->id]))
            ->assertSessionHasErrors('category_id');

        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_the_transaction_type_must_match_the_category_type(): void
    {
        [$wallet, $expense, $income] = $this->fixture($user = User::factory()->create());

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $expense, TransactionType::Expense, [
                'type' => TransactionType::Income->value,
            ]))
            ->assertSessionHasErrors(['type' => 'Tipe transaksi harus sama dengan tipe kategori yang dipilih.']);

        $this->assertDatabaseCount('transactions', 0);

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->payload($wallet, $income, TransactionType::Income, [
                'type' => TransactionType::Income->value,
            ]))
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseCount('transactions', 1);
    }

    public function test_a_user_can_update_their_own_transaction(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
            'amount' => 45000,
        ]);

        $this->actingAs($user)
            ->get(route('transactions.edit', $transaction))
            ->assertOk();

        $this->actingAs($user)
            ->put(route('transactions.update', $transaction), $this->payload($wallet, $expense, TransactionType::Expense, ['amount' => 55000]))
            ->assertRedirect(route('transactions.index'));

        $this->assertSame(55000, $transaction->fresh()?->amount);
    }

    public function test_a_user_can_delete_their_own_transaction(): void
    {
        [$wallet, $expense] = $this->fixture($user = User::factory()->create());

        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $expense->id,
            'type' => TransactionType::Expense,
        ]);

        $this->actingAs($user)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect(route('transactions.index'))
            ->assertSessionHas('status', 'Transaksi berhasil dihapus.');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_a_user_cannot_read_update_or_delete_another_users_transaction(): void
    {
        $user = User::factory()->create();
        [$wallet, $expense] = $this->fixture($user);

        [$otherWallet, $otherExpense] = $this->fixture($other = User::factory()->create());

        $transaction = Transaction::factory()->create([
            'user_id' => $other->id,
            'wallet_id' => $otherWallet->id,
            'category_id' => $otherExpense->id,
            'type' => TransactionType::Expense,
            'amount' => 45000,
        ]);

        $this->actingAs($user)->get(route('transactions.edit', $transaction))->assertForbidden();
        $this->actingAs($user)
            ->put(route('transactions.update', $transaction), $this->payload($wallet, $expense, TransactionType::Expense))
            ->assertForbidden();
        $this->actingAs($user)->delete(route('transactions.destroy', $transaction))->assertForbidden();

        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'amount' => 45000]);
    }

    /**
     * @return array{Wallet, Category, Category}
     */
    private function fixture(User $user): array
    {
        return [
            $user->wallets()->create(['name' => 'Dompet Tunai']),
            $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]),
            $user->categories()->create(['name' => 'Gaji', 'type' => TransactionType::Income]),
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Wallet $wallet, Category $category, TransactionType $type, array $overrides = []): array
    {
        return array_merge([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => $type->value,
            'amount' => 45000,
            'transacted_on' => now()->toDateString(),
            'note' => 'Makan siang',
        ], $overrides);
    }
}
