<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\TransactionType;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class MonthlySummary
{
    /**
     * @param  Collection<int, array{name: string, total: int}>  $expenseByCategory
     */
    public function __construct(
        public string $month,
        public int $income,
        public int $expense,
        public Collection $expenseByCategory,
    ) {}

    /**
     * Totals come from the database, never from a loop over the rows.
     */
    public static function for(User $user, string $month): self
    {
        $income = (int) $user->transactions()
            ->forMonth($month)
            ->ofType(TransactionType::Income)
            ->sum('amount');

        $expense = (int) $user->transactions()
            ->forMonth($month)
            ->ofType(TransactionType::Expense)
            ->sum('amount');

        $totals = $user->transactions()
            ->forMonth($month)
            ->ofType(TransactionType::Expense)
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->pluck('total', 'category_id');

        $names = $user->categories()
            ->whereIn('id', $totals->keys())
            ->pluck('name', 'id');

        return new self(
            month: $month,
            income: $income,
            expense: $expense,
            expenseByCategory: $totals
                ->map(fn (mixed $total, mixed $categoryId): array => [
                    'name' => (string) $names->get($categoryId),
                    'total' => (int) $total,
                ])
                ->values(),
        );
    }

    public function balance(): int
    {
        return $this->income - $this->expense;
    }
}
