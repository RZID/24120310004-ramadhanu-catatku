<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * The admin area only manages accounts. It deliberately cannot open another
 * user's wallets, categories or transactions: the policies keep those private.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.users.index', [
            'users' => User::query()->withCount('transactions')->orderBy('name')->get(),
            'currentUserId' => $this->actor($request)->id,
        ]);
    }

    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($user->is($this->actor($request))) {
            return back()->with('error', 'Kamu tidak bisa menonaktifkan akunmu sendiri.');
        }

        if ($user->isDeactivated()) {
            return back()->with('error', 'Akun ini sudah nonaktif.');
        }

        if ($this->wouldLeaveNoActiveAdmin($user)) {
            return back()->with('error', 'Harus ada minimal satu admin aktif.');
        }

        $user->deactivate();

        return back()->with('status', 'Akun '.$user->name.' dinonaktifkan.');
    }

    public function activate(Request $request, User $user): RedirectResponse
    {
        if (! $user->isDeactivated()) {
            return back()->with('error', 'Akun ini sudah aktif.');
        }

        $user->activate();

        return back()->with('status', 'Akun '.$user->name.' diaktifkan kembali.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($this->actor($request))) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        if ($this->wouldLeaveNoActiveAdmin($user)) {
            return back()->with('error', 'Harus ada minimal satu admin aktif.');
        }

        $name = $user->name;

        DB::transaction(function () use ($user): void {
            // The wallet and category keys are RESTRICT, so the rows that point at
            // them have to go first even though user_id itself cascades.
            $user->transactions()->delete();
            $user->delete();
        });

        return back()->with('status', 'Akun '.$name.' dihapus.');
    }

    private function actor(Request $request): User
    {
        /** @var User $user */
        $user = $request->user();

        return $user;
    }

    private function wouldLeaveNoActiveAdmin(User $target): bool
    {
        // A deactivated admin does not count towards the active admins, so removing
        // one never makes the system adminless.
        if ($target->role !== UserRole::Admin || $target->isDeactivated()) {
            return false;
        }

        return User::query()
            ->where('role', UserRole::Admin->value)
            ->whereNull('deactivated_at')
            ->count() <= 1;
    }
}
