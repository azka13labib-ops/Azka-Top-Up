<?php

namespace App\Jobs;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Mail\OrderFailedMail;
use App\Mail\OrderSuccessMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Queueable;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 60, 120];

    public function __construct(
        public int    $orderId,
        public string $mailType // 'success' | 'failed'
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // Job Execution
    // ─────────────────────────────────────────────────────────────────────

    public function handle(OrderInterface $orderRepo): void
    {
        $order = $orderRepo->find($this->orderId);

        if (!$order) {
            Log::error("[SendEmailJob] Order #{$this->orderId} not found.");
            return;
        }

        if (empty($order->email)) {
            Log::warning("[SendEmailJob] Order #{$this->orderId} has no email address. Skipping.");
            return;
        }

        // Load product relation for email template
        $order->loadMissing('product.game');

        match ($this->mailType) {
            'success' => $this->sendSuccessMail($order),
            'failed'  => $this->sendFailedMail($order),
            default   => Log::warning("[SendEmailJob] Unknown mail type '{$this->mailType}' for order #{$this->orderId}."),
        };
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    private function sendSuccessMail($order): void
    {
        Mail::to($order->email)->send(new OrderSuccessMail($order));
        Log::info("[SendEmailJob] Success email sent to {$order->email} for order #{$this->orderId}.");
    }

    private function sendFailedMail($order): void
    {
        Mail::to($order->email)->send(new OrderFailedMail($order));
        Log::info("[SendEmailJob] Failed email sent to {$order->email} for order #{$this->orderId}.");
    }
}
