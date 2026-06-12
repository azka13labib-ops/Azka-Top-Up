<?php

namespace App\Jobs;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Enums\TopupStatusEnum;
use App\Models\Order;
use App\Services\DigiflazzService;
use App\Services\OrderService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteTopupJob implements ShouldQueue
{
    use Queueable;

    /**
     * Max attempts before Laravel marks this job as failed.
     * We handle retry manually via RetryTopupJob.
     */
    public int $tries = 1;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 60;

    public function __construct(
        public int $orderId
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // Job Execution
    // ─────────────────────────────────────────────────────────────────────

    public function handle(
        OrderInterface   $orderRepo,
        OrderService     $orderService,
        DigiflazzService $digiflazz
    ): void {
        /** @var Order|null $order */
        $order = $orderRepo->find($this->orderId);

        if (!$order) {
            Log::error("[ExecuteTopupJob] Order #{$this->orderId} not found.");
            return;
        }

        // Guard: only process paid orders
        if ($order->payment_status->value !== 'paid') {
            Log::warning("[ExecuteTopupJob] Order #{$this->orderId} is not paid yet. Skipping.");
            return;
        }

        // Guard: idempotency — skip if already processing or completed
        if (in_array($order->topup_status->value, ['processing', 'completed'])) {
            Log::info("[ExecuteTopupJob] Order #{$this->orderId} already {$order->topup_status->value}. Skipping.");
            return;
        }

        // Guard: idempotency — skip if digiflazz_ref_id is already set (already submitted)
        if (!empty($order->digiflazz_ref_id)) {
            Log::info("[ExecuteTopupJob] Order #{$this->orderId} already submitted to Digiflazz (ref: {$order->digiflazz_ref_id}).");
            return;
        }

        // Mark as processing
        $orderService->markTopupProcessing($order->id);

        // Set digiflazz_ref_id to our order_code for idempotency
        $orderRepo->updateStatus($order->id, [
            'digiflazz_ref_id' => $order->order_code,
        ]);

        try {
            $result = $digiflazz->createTransaction(
                sku:        $order->product->digiflazz_sku,
                customerNo: $order->customer_no . ($order->zone_id ? '|' . $order->zone_id : ''),
                refId:      $order->order_code
            );

            $digiflazzStatus = $result['status'] ?? 'Gagal';
            $sn              = $result['sn'] ?? null;
            $message         = $result['message'] ?? 'No message';

            if ($digiflazzStatus === 'Sukses') {
                $orderService->markTopupCompleted($order->id, $sn ?? '');

                // Dispatch email notification
                SendEmailJob::dispatch($order->id, 'success');

                Log::info("[ExecuteTopupJob] Order #{$this->orderId} topup SUCCESS. SN: {$sn}");

            } elseif ($digiflazzStatus === 'Pending') {
                // Digiflazz is still processing — will notify via webhook
                Log::info("[ExecuteTopupJob] Order #{$this->orderId} topup PENDING. Waiting for Digiflazz webhook.");

            } else {
                // Failed — schedule retry
                Log::warning("[ExecuteTopupJob] Order #{$this->orderId} topup FAILED: {$message}");

                RetryTopupJob::dispatch($this->orderId, 1)
                    ->delay(now()->addMinutes(2));
            }

        } catch (Throwable $e) {
            Log::error("[ExecuteTopupJob] Exception on order #{$this->orderId}: " . $e->getMessage());

            // Schedule retry on exception
            RetryTopupJob::dispatch($this->orderId, 1)
                ->delay(now()->addMinutes(2));
        }
    }
}
