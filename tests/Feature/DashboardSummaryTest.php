<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_totals_follow_the_selected_month(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan, $transport, $gaji] = $this->fixture($user);

        $this->transaction($user, $wallet, $gaji, 1000000, '2026-08-05');
        $this->transaction($user, $wallet, $makan, 200000, '2026-08-10');
        $this->transaction($user, $wallet, $transport, 100000, '2026-08-15');
        $this->transaction($user, $wallet, $gaji, 500000, '2026-09-05');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('Agustus 2026')
            // income, expense and the balance of that month
            ->assertSee('Rp 1.000.000')
            ->assertSee('Rp 300.000')
            ->assertSee('Rp 700.000')
            // September income would push the card to 1.500.000
            ->assertDontSee('Rp 1.500.000');
    }

    public function test_the_expense_per_category_table_matches_the_transactions(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan, $transport] = $this->fixture($user);

        $this->transaction($user, $wallet, $makan, 200000, '2026-08-10');
        $this->transaction($user, $wallet, $transport, 100000, '2026-08-15');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('data-chart="expense-by-category"', false)
            ->assertSee('Makan')
            ->assertSee('Transportasi')
            ->assertSee('Rp 200.000')
            ->assertSee('Rp 100.000');
    }

    public function test_the_wallet_balance_counts_every_month(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan, , $gaji] = $this->fixture($user);

        $this->transaction($user, $wallet, $gaji, 1000000, '2026-08-05');
        $this->transaction($user, $wallet, $makan, 300000, '2026-08-10');
        $this->transaction($user, $wallet, $gaji, 500000, '2026-09-05');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-09']))
            ->assertOk()
            // 1.500.000 income - 300.000 expense, not just September
            ->assertSee('Rp 1.200.000');
    }

    public function test_another_users_transactions_are_not_counted(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan] = $this->fixture($user);

        $other = User::factory()->create();
        [$otherWallet, $otherMakan] = $this->fixture($other);

        $this->transaction($user, $wallet, $makan, 100000, '2026-08-10');
        $this->transaction($other, $otherWallet, $otherMakan, 900000, '2026-08-11');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('Rp 100.000')
            ->assertDontSee('Rp 900.000');
    }

    public function test_only_the_five_latest_transactions_are_listed(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan] = $this->fixture($user);

        foreach (range(1, 6) as $day) {
            $this->transaction($user, $wallet, $makan, $day * 1000, '2026-10-0'.$day, 'Catatan '.$day);
        }

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-10']))
            ->assertOk()
            ->assertSee('Catatan 6')
            ->assertSee('Catatan 2')
            ->assertDontSee('Catatan 1');
    }

    public function test_a_negative_balance_keeps_the_minus_before_the_currency(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan, , $gaji] = $this->fixture($user);

        $this->transaction($user, $wallet, $gaji, 100000, '2026-08-05');
        $this->transaction($user, $wallet, $makan, 250000, '2026-08-10');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => '2026-08']))
            ->assertOk()
            ->assertSee('-Rp 150.000');
    }

    public function test_the_dashboard_defaults_to_the_current_month(): void
    {
        $user = User::factory()->create();
        [$wallet, $makan, , $gaji] = $this->fixture($user);

        $this->transaction($user, $wallet, $gaji, 1000000, now()->toDateString(), 'Bulan ini');
        $this->transaction($user, $wallet, $makan, 120000, now()->toDateString(), 'Bulan ini');
        // subMonth() overflows on the 31st and lands in the same month, so the
        // previous month is reached from the first day instead.
        $this->transaction($user, $wallet, $makan, 500000, now()->startOfMonth()->subDay()->toDateString(), 'Bulan lalu');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee(now()->locale('id')->translatedFormat('F Y'))
            ->assertSee('Rp 880.000')
            // both months would total 620.000 of expenses if the month were ignored
            ->assertDontSee('Rp 620.000');
    }

    public function test_an_invalid_month_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->fixture($user);

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => 'Agustus']))
            ->assertSessionHasErrors('month');
    }

    /**
     * @return array{Wallet, Category, Category, Category}
     */
    private function fixture(User $user): array
    {
        return [
            $user->wallets()->create(['name' => 'Dompet Tunai']),
            $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]),
            $user->categories()->create(['name' => 'Transportasi', 'type' => TransactionType::Expense]),
            $user->categories()->create(['name' => 'Gaji', 'type' => TransactionType::Income]),
        ];
    }

    private function transaction(
        User $user,
        Wallet $wallet,
        Category $category,
        int $amount,
        string $date,
        ?string $note = null,
    ): void {
        $user->transactions()->create([
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => $category->type,
            'amount' => $amount,
            'transacted_on' => $date,
            'note' => $note,
        ]);
    }
}
