<?php

namespace App\Contracts\Interfaces\data;

use App\Models\AdminUser;

interface AdminUserInterface
{
    public function findByEmail(string $email): ?AdminUser;
    public function updateLastLogin(int $adminId): bool;
}
