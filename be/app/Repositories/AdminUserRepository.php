<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\AdminUserInterface;
use App\Models\AdminUser;

class AdminUserRepository implements AdminUserInterface
{
    public function findByEmail(string $email): ?AdminUser
    {
        return AdminUser::where('email', $email)->first();
    }

    public function updateLastLogin(int $adminId): bool
    {
        return AdminUser::where('id', $adminId)->update(['last_login_at' => now()]) > 0;
    }
}
