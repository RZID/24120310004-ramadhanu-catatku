<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => fake()->randomElement(TransactionType::cases()),
            'amount' => fake()->numberBetween(5, 500) * 1000,
            'transacted_on' => now()->subDays(fake()->numberBetween(0, 60))->toDateString(),
            'note' => fake()->sentence(3),
        ];
    }

    /**
     * The wallet and the category must belong to the transaction owner and the
     * category type must match, so both are created from the resolved owner
     * unless the caller supplies them explicitly.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Transaction $transaction): void {
            $transaction->wallet_id ??= Wallet::factory()
                ->create(['user_id' => $transaction->user_id])
                ->id;

            $transaction->category_id ??= Category::factory()
                ->create([
                    'user_id' => $transaction->user_id,
                    'type' => $transaction->type,
                ])
                ->id;
        });
    }
}
