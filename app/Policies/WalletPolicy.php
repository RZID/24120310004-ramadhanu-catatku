<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\Wallet;

class WalletPolicy
{
    public function update(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }

    public function delete(User $user, Wallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }
}
