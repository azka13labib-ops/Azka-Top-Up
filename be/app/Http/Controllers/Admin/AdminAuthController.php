<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Contracts\Interfaces\data\AdminUserInterface;
use App\Models\AdminUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function __construct(
        protected AdminUserInterface $adminUserRepo
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $admin = $this->adminUserRepo->findByEmail($request->input('email'));

        if (!$admin || !Hash::check($request->input('password'), $admin->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$admin->is_active) {
            return $this->errorResponse('Your account has been deactivated. Please contact support.', 403);
        }

        // Update last login timestamp
        $this->adminUserRepo->updateLastLogin($admin->id);

        // Revoke semua token lama dan buat token baru yang expires dalam 8 jam
        $admin->tokens()->delete();
        $token = $admin->createToken(
            name:      'admin-token',
            abilities: ['role:admin'],
            expiresAt: now()->addHours(8)
        )->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'admin' => [
                'id'    => $admin->id,
                'name'  => $admin->name,
                'email' => $admin->email,
                'role'  => $admin->role,
            ],
        ], 'Admin login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        // Revoke only the current token
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse([], 'Admin logout successful');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var AdminUser $admin */
        $admin = $request->user();

        return $this->successResponse([
            'id'            => $admin->id,
            'name'          => $admin->name,
            'email'         => $admin->email,
            'role'          => $admin->role,
            'last_login_at' => $admin->last_login_at?->toISOString(),
        ], 'Admin profile retrieved successfully');
    }
}
