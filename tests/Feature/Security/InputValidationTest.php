<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InputValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_injection_attempt_in_the_month_filter_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('transactions.index', ['month' => "2026-08' OR '1'='1"]))
            ->assertSessionHasErrors('month');

        $this->actingAs($user)
            ->get(route('dashboard', ['month' => "2026-08' OR '1'='1"]))
            ->assertSessionHasErrors('month');
    }

    public function test_an_injection_attempt_in_the_category_filter_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('transactions.index', ['category_id' => '1 OR 1=1']))
            ->assertSessionHasErrors('category_id');

        $this->actingAs($user)
            ->get(route('transactions.index', ['type' => 'expense OR 1=1']))
            ->assertSessionHasErrors('type');
    }

    public function test_strings_are_bounded_so_oversized_input_is_refused(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('wallets.store'), ['name' => str_repeat('a', 101)])
            ->assertSessionHasErrors('name');

        $this->assertSame(0, $user->wallets()->count());
    }
}
