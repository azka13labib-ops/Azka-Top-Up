<?php

use App\Console\Commands\CheckExpiredPayments;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────
// Scheduled Jobs (Phase 2 — Polling Fallback)
// ─────────────────────────────────────────────

// Poll Midtrans every 15 minutes for orders stuck in pending payment > 60 minutes.
// This handles cases where Midtrans webhooks were not delivered.
Schedule::command('topup:check-expired-payments')
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/check-expired-payments.log'));

