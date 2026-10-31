<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_guest_forms_ship_a_csrf_token(): void
    {
        $this->get(route('login'))->assertSee('name="_token"', false);
        $this->get(route('register'))->assertSee('name="_token"', false);
    }

    public function test_every_authenticated_page_with_a_form_ships_a_csrf_token(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
        ]);

        $pages = [
            route('dashboard'),
            route('transactions.index'),
            route('transactions.create'),
            route('transactions.edit', $transaction),
            route('wallets.index'),
            route('wallets.create'),
            route('wallets.edit', $wallet),
            route('categories.index'),
            route('categories.create'),
            route('categories.edit', $category),
        ];

        foreach ($pages as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee('name="_token"', false);
        }
    }

    public function test_the_admin_page_ships_a_csrf_token(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_data_changing_forms_use_method_spoofing(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->get(route('wallets.edit', $wallet))
            ->assertSee('name="_method" value="PUT"', false);

        $this->actingAs($user)
            ->get(route('categories.edit', $category))
            ->assertSee('name="_method" value="PUT"', false);

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertSee('name="_method" value="DELETE"', false);
    }

    public function test_logout_is_a_post_request(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertSee('action="'.route('logout').'"', false);
    }
}
