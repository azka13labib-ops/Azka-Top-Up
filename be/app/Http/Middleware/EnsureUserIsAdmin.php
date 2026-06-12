<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lapisan 2 — Admin Guard Middleware
 *
 * Memastikan user yang sedang login adalah AdminUser, bukan User (customer) biasa.
 * Ini menutup celah di mana customer yang punya token valid bisa mencoba mengakses
 * route admin hanya dengan `auth:sanctum` standar.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Pastikan sudah authenticated DAN merupakan instance AdminUser
        if (!$user || !($user instanceof AdminUser)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden. Admin access required.',
                'errors'  => [],
            ], 403);
        }

        // Pastikan akun admin aktif (double-check, karena cek ini juga ada di login)
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your admin account has been deactivated.',
                'errors'  => [],
            ], 403);
        }

        return $next($request);
    }
}
