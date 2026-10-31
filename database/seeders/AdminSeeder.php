<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin Catatku',
            'email' => 'admin@catatku.test',
            'password' => 'password',
        ]);

        // `role` is not mass assignable, so it is set on the model directly.
        $admin->role = UserRole::Admin->value;
        $admin->save();
    }
}
