<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_ignores_a_role_or_status_in_the_payload(): void
    {
        $this->post(route('register'), [
            'name' => 'Rina',
            'email' => 'rina@catatku.test',
            'password' => 'Rahasia123',
            'password_confirmation' => 'Rahasia123',
            'role' => 'admin',
            'deactivated_at' => now()->toDateTimeString(),
        ])->assertRedirect(route('dashboard'));

        $user = User::firstWhere('email', 'rina@catatku.test');

        $this->assertNotNull($user);
        $this->assertSame(UserRole::User, $user->role);
        $this->assertFalse($user->isDeactivated());
    }

    public function test_a_transaction_cannot_be_assigned_to_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->post(route('transactions.store'), [
                'wallet_id' => $wallet->id,
                'category_id' => $category->id,
                'type' => 'expense',
                'amount' => 45000,
                'transacted_on' => now()->toDateString(),
                'user_id' => $other->id,
            ])->assertRedirect(route('transactions.index'));

        $this->assertSame(1, $user->transactions()->count());
        $this->assertSame(0, $other->transactions()->count());
    }

    public function test_a_wallet_cannot_be_given_to_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => 'BCA', 'user_id' => $other->id])
            ->assertRedirect(route('wallets.index'));

        $wallet = Wallet::firstWhere('name', 'BCA');

        $this->assertNotNull($wallet);
        $this->assertSame($user->id, $wallet->user_id);
    }

    public function test_a_category_cannot_be_given_to_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->post(route('categories.store'), ['name' => 'Hiburan', 'type' => 'expense', 'user_id' => $other->id])
            ->assertRedirect(route('categories.index'));

        $category = Category::firstWhere('name', 'Hiburan');

        $this->assertNotNull($category);
        $this->assertSame($user->id, $category->user_id);
    }
}
