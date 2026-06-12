<?php

namespace App\Jobs;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RetryTopupJob implements ShouldQueue
{
    use Queueable;

    /**
     * Max Laravel-level retries (we handle our own attempt counter).
     */
    public int $tries = 1;

    /**
     * Maximum number of business-level retry attempts before marking as failed.
     */
    public const MAX_RETRIES = 3;

    public function __construct(
        public int $orderId,
        public int $attempt = 1
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // Job Execution
    // ─────────────────────────────────────────────────────────────────────

    public function handle(
        OrderInterface $orderRepo,
        OrderService   $orderService
    ): void {
        $order = $orderRepo->find($this->orderId);

        if (!$order) {
            Log::error("[RetryTopupJob] Order #{$this->orderId} not found.");
            return;
        }

        // Guard: don't retry if already completed
        if ($order->topup_status->value === 'completed') {
            Log::info("[RetryTopupJob] Order #{$this->orderId} already completed. Skipping retry.");
            return;
        }

        Log::info("[RetryTopupJob] Attempt #{$this->attempt} for order #{$this->orderId}.");

        if ($this->attempt <= self::MAX_RETRIES) {
            // Re-clear the ref_id so ExecuteTopupJob doesn't skip due to idempotency
            // (only do this if previous attempt definitively failed, not just pending)
            $orderRepo->updateStatus($order->id, [
                'digiflazz_ref_id' => null,
                'topup_status'     => \App\Enums\TopupStatusEnum::PENDING,
            ]);

            // Re-dispatch the main job with exponential backoff
            $delayMinutes = pow(2, $this->attempt - 1); // 1, 2, 4 minutes

            ExecuteTopupJob::dispatch($this->orderId)
                ->delay(now()->addMinutes($delayMinutes));

            Log::info("[RetryTopupJob] Re-dispatched ExecuteTopupJob for order #{$this->orderId} in {$delayMinutes} minute(s).");

        } else {
            // Exhausted all retries — mark as permanently failed
            $orderService->markTopupFailed(
                $this->orderId,
                "Top-up failed after " . self::MAX_RETRIES . " retry attempts."
            );

            // Send failure notification email
            SendEmailJob::dispatch($this->orderId, 'failed');

            Log::error("[RetryTopupJob] Order #{$this->orderId} exhausted all retries. Marked as FAILED.");
        }
    }
}
