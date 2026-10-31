<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\DashboardRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Support\MonthlySummary;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $month = $request->month();
        $summary = MonthlySummary::for($user, $month);

        return view('dashboard', [
            'month' => $month,
            'monthLabel' => Carbon::parse($month.'-01')->locale('id')->translatedFormat('F Y'),
            'summary' => $summary,
            'wallets' => $this->walletBalances($user),
            'recent' => $user->transactions()
                ->with(['wallet', 'category'])
                ->orderByDesc('transacted_on')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'chart' => [
                'labels' => $summary->expenseByCategory->pluck('name')->all(),
                'values' => $summary->expenseByCategory->pluck('total')->all(),
            ],
        ]);
    }

    /**
     * A wallet balance is all-time income minus all-time expense; there is no
     * opening balance.
     *
     * @return Collection<int, array{name: string, balance: int}>
     */
    private function walletBalances(User $user): Collection
    {
        return $user->wallets()
            ->withSum(
                ['transactions as income_total' => fn ($query) => $query->where('type', TransactionType::Income->value)],
                'amount',
            )
            ->withSum(
                ['transactions as expense_total' => fn ($query) => $query->where('type', TransactionType::Expense->value)],
                'amount',
            )
            ->orderBy('name')
            ->get()
            ->map(fn (Wallet $wallet): array => [
                'name' => $wallet->name,
                'balance' => (int) $wallet->getAttribute('income_total') - (int) $wallet->getAttribute('expense_total'),
            ]);
    }
}
