<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lapisan 4 — Webhook IP Whitelist Middleware
 *
 * Memvalidasi bahwa request webhook hanya berasal dari IP resmi Midtrans dan Digiflazz.
 * Ini adalah lapisan keamanan tambahan di atas validasi signature.
 * Bisa dinonaktifkan via .env (WEBHOOK_IP_WHITELIST_ENABLED=false) untuk testing lokal.
 */
class WebhookIpWhitelist
{
    /**
     * IP Midtrans yang diizinkan (sandbox + production).
     * Sumber: https://docs.midtrans.com/docs/ip-address-whitelist
     */
    private array $midtransIps = [
        '202.152.0.0/16',
        '103.208.23.0/24',
        '103.208.21.0/24',
        '103.208.22.0/24',
        '34.101.187.0/24', // GCP Asia Southeast1 (sandbox)
    ];

    /**
     * IP Digiflazz yang diizinkan.
     * Sumber: Dokumentasi resmi Digiflazz.
     */
    private array $digiflazzIps = [
        '130.255.69.250',
        '103.13.241.92',
        '103.13.241.93',
    ];

    public function handle(Request $request, Closure $next, string $provider = 'midtrans'): Response
    {
        // Fitur bisa dinonaktifkan lewat .env untuk kemudahan pengembangan lokal
        if (!config('app.webhook_ip_whitelist_enabled', false)) {
            return $next($request);
        }

        $clientIp = $request->ip();
        $allowedIps = $provider === 'digiflazz' ? $this->digiflazzIps : $this->midtransIps;

        if (!$this->isIpAllowed($clientIp, $allowedIps)) {
            Log::warning("[WebhookIpWhitelist] Blocked request from IP: {$clientIp}", [
                'provider' => $provider,
                'url'      => $request->fullUrl(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Forbidden.',
                'errors'  => [],
            ], 403);
        }

        return $next($request);
    }

    /**
     * Cek apakah IP diizinkan — mendukung CIDR notation (e.g. 202.152.0.0/16)
     */
    private function isIpAllowed(string $ip, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowed) {
            // Jika ada CIDR notation
            if (str_contains($allowed, '/')) {
                if ($this->ipInCidr($ip, $allowed)) {
                    return true;
                }
            } elseif ($ip === $allowed) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cek apakah IP masuk dalam range CIDR
     */
    private function ipInCidr(string $ip, string $cidr): bool
    {
        [$subnet, $bits] = explode('/', $cidr);

        $ip     = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask   = -1 << (32 - (int) $bits);
        $subnet &= $mask;

        return ($ip & $mask) === $subnet;
    }
}
