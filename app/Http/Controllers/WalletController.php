<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\WalletRequest;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('wallets.index', [
            'wallets' => $user->wallets()->withCount('transactions')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('wallets.create');
    }

    public function store(WalletRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $user->wallets()->create($request->validated());

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil ditambahkan.');
    }

    public function edit(Wallet $wallet): View
    {
        $this->authorize('update', $wallet);

        return view('wallets.edit', ['wallet' => $wallet]);
    }

    public function update(WalletRequest $request, Wallet $wallet): RedirectResponse
    {
        $this->authorize('update', $wallet);

        $wallet->update($request->validated());

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil diperbarui.');
    }

    public function destroy(Wallet $wallet): RedirectResponse
    {
        $this->authorize('delete', $wallet);

        if ($wallet->transactions()->exists()) {
            return back()->with('error', 'Dompet ini masih punya transaksi. Hapus transaksinya lebih dulu.');
        }

        $wallet->delete();

        return redirect()->route('wallets.index')->with('status', 'Dompet berhasil dihapus.');
    }
}
