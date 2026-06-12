<?php

namespace App\Models;

use App\Enums\AdminRoleEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'name', 'email', 'password', 'role', 'last_login_at', 'is_active'
])]
#[Hidden([
    'password'
])]
class AdminUser extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected function casts(): array
    {
        return [
            'role' => AdminRoleEnum::class,
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }
}
