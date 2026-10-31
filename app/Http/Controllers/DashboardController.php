<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('dashboard', [
            'walletCount' => $user->wallets()->count(),
            'categoryCount' => $user->categories()->count(),
            'transactionCount' => $user->transactions()->count(),
        ]);
    }
}
