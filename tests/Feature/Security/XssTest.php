<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XssTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_script_tag_in_a_note_is_escaped(): void
    {
        $user = User::factory()->create();
        $wallet = $user->wallets()->create(['name' => 'Dompet Tunai']);
        $category = $user->categories()->create(['name' => 'Makan', 'type' => TransactionType::Expense]);

        Transaction::factory()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => TransactionType::Expense,
            'note' => '<script>alert(1)</script>',
        ]);

        $this->actingAs($user)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_an_html_payload_in_a_wallet_name_is_escaped(): void
    {
        $user = User::factory()->create();
        $user->wallets()->create(['name' => '<img src=x onerror=alert(1)>']);

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertOk()
            ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
            ->assertDontSee('<img src=x', false);
    }

    public function test_an_html_payload_in_a_category_name_is_escaped(): void
    {
        $user = User::factory()->create();
        $user->categories()->create(['name' => '<b>Makan</b>', 'type' => TransactionType::Expense]);

        $this->actingAs($user)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertSee('&lt;b&gt;Makan&lt;/b&gt;', false)
            ->assertDontSee('<b>Makan</b>', false);
    }
}
