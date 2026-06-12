<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Contracts\Interfaces\data\OrderInterface;
use App\Jobs\ExecuteTopupJob;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        protected MidtransService $midtransService,
        protected OrderService    $orderService,
        protected OrderInterface  $orderRepo
    ) {}

    /**
     * POST /api/webhook/midtrans
     *
     * Handles payment notifications from Midtrans.
     * Flow: validate signature → find order → update status → dispatch topup job
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('[MidtransWebhook] Received notification.', [
            'order_id'           => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
        ]);

        // ── 1. Signature validation (SHA512) ─────────────────────────────
        if (!$this->midtransService->validateWebhookSignature($payload)) {
            Log::warning('[MidtransWebhook] Invalid signature. Rejecting payload.');
            return $this->errorResponse('Invalid signature', 403);
        }

        // ── 2. Find order by order_id (= our order_code) ─────────────────
        $orderId = $payload['order_id'] ?? null;
        $order   = $this->orderRepo->findByCode((string) $orderId);

        if (!$order) {
            Log::warning("[MidtransWebhook] Order not found for order_id: {$orderId}");
            return $this->errorResponse('Order not found', 404);
        }

        // ── 3. Idempotency: skip if payment already processed ─────────────
        if ($order->payment_status->value === 'paid') {
            Log::info("[MidtransWebhook] Order #{$order->id} already paid. Skipping.");
            return $this->successResponse([], 'Already processed');
        }

        // ── 4. Handle notification based on transaction status ────────────
        if ($this->midtransService->isPaymentSettled($payload)) {
            // Payment confirmed — update order and trigger top-up
            $this->orderService->markAsPaid($order->id);

            // Dispatch top-up execution job
            ExecuteTopupJob::dispatch($order->id);

            Log::info("[MidtransWebhook] Payment SETTLED for order #{$order->id}. ExecuteTopupJob dispatched.");

        } elseif ($this->midtransService->isPaymentExpiredOrCancelled($payload)) {
            $this->orderService->markPaymentExpired($order->id);

            Log::info("[MidtransWebhook] Payment EXPIRED/CANCELLED for order #{$order->id}.");
        } else {
            Log::info("[MidtransWebhook] Unhandled transaction_status: {$payload['transaction_status']} for order #{$order->id}.");
        }

        // Always return 200 to Midtrans to acknowledge receipt
        return $this->successResponse([], 'Webhook notification handled');
    }
}
