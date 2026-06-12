<?php

namespace App\Console\Commands;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Enums\PaymentStatusEnum;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'topup:check-expired-payments';

    /**
     * The console command description.
     */
    protected $description = 'Poll Midtrans for pending payments that may have expired, and update their status accordingly.';

    public function __construct(
        protected OrderInterface  $orderRepo,
        protected MidtransService $midtransService,
        protected OrderService    $orderService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Scheduled at: every 15 minutes via app/Console/Kernel.php
     */
    public function handle(): void
    {
        // Find all orders that are still pending payment and older than 60 minutes
        $expiredOrders = \App\Models\Order::query()
            ->where('payment_status', PaymentStatusEnum::PENDING)
            ->where('created_at', '<', now()->subMinutes(60))
            ->with('product')
            ->get();

        if ($expiredOrders->isEmpty()) {
            $this->info('No expired pending orders found.');
            return;
        }

        $this->info("Found {$expiredOrders->count()} potentially expired orders. Checking Midtrans...");

        $updated = 0;

        foreach ($expiredOrders as $order) {
            try {
                $status = $this->midtransService->checkTransactionStatus($order->order_code);

                $transactionStatus = $status['transaction_status'] ?? 'pending';

                if ($this->midtransService->isPaymentSettled($status)) {
                    // Edge case: payment was settled but webhook failed
                    $this->orderService->markAsPaid($order->id);

                    \App\Jobs\ExecuteTopupJob::dispatch($order->id);

                    $this->line("  ✓ Order #{$order->id} ({$order->order_code}): PAID (webhook missed). Job dispatched.");
                    $updated++;

                } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {
                    $this->orderService->markPaymentExpired($order->id);

                    $this->line("  ✗ Order #{$order->id} ({$order->order_code}): EXPIRED/CANCELLED.");
                    $updated++;

                } else {
                    $this->line("  ~ Order #{$order->id} ({$order->order_code}): still {$transactionStatus}.");
                }

            } catch (\Throwable $e) {
                Log::warning("[CheckExpiredPayments] Failed to check order #{$order->id}: " . $e->getMessage());
                $this->warn("  ! Order #{$order->id}: error — {$e->getMessage()}");
            }
        }

        $this->info("Done. Updated {$updated} order(s).");
        Log::info("[CheckExpiredPayments] Completed. Updated {$updated} orders.");
    }
}
