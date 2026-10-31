<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\TransactionFilterRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(TransactionFilterRequest $request): View
    {
        /** @var User $user */
        $user = $request->user();

        $filters = $request->validated();

        $query = $user->transactions()->with(['wallet', 'category']);

        if (isset($filters['month'])) {
            $query->forMonth((string) $filters['month']);
        }

        if (isset($filters['type'])) {
            $query->ofType(TransactionType::from((string) $filters['type']));
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        return view('transactions.index', [
            'transactions' => $query
                ->orderByDesc('transacted_on')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString(),
            'categories' => $user->categories()->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->wallets()->doesntExist() || $user->categories()->doesntExist()) {
            return redirect()->route('transactions.index')->with(
                'error',
                'Tambahkan minimal satu dompet dan satu kategori sebelum mencatat transaksi.',
            );
        }

        return view('transactions.create', $this->formOptions($user));
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->transactions()->create($request->validated());

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil dicatat.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        $this->authorize('update', $transaction);

        /** @var User $user */
        $user = $request->user();

        return view('transactions.edit', [
            ...$this->formOptions($user),
            'transaction' => $transaction,
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('status', 'Transaksi berhasil dihapus.');
    }

    /**
     * Select options for the form. The category label carries its type because the
     * same name may exist once as income and once as expense.
     *
     * @return array{categories: array<string, int>, wallets: array<string, int>}
     */
    private function formOptions(User $user): array
    {
        return [
            'categories' => $user->categories()
                ->orderBy('type')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($category): array => [
                    $category->name.' ('.$category->type->label().')' => $category->id,
                ])
                ->all(),
            'wallets' => $user->wallets()
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($wallet): array => [$wallet->name => $wallet->id])
                ->all(),
        ];
    }
}
