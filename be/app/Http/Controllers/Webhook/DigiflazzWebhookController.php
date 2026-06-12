<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Contracts\Interfaces\data\OrderInterface;
use App\Services\DigiflazzService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DigiflazzWebhookController extends Controller
{
    public function __construct(
        protected DigiflazzService $digiflazzService,
        protected OrderService     $orderService,
        protected OrderInterface   $orderRepo
    ) {}

    /**
     * POST /api/webhook/digiflazz
     *
     * Handles top-up status notifications from Digiflazz.
     * Flow: validate MD5 signature → find order → update topup status → send email
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();

        $refId  = $payload['data']['ref_id']  ?? null;
        $status = $payload['data']['status']  ?? null;
        $sn     = $payload['data']['sn']      ?? null;
        $msg    = $payload['data']['message'] ?? 'No message';
        $sign   = $payload['data']['sign']    ?? null;

        Log::info('[DigiflazzWebhook] Received notification.', [
            'ref_id' => $refId,
            'status' => $status,
        ]);

        // ── 1. Validate incoming signature ────────────────────────────────
        if (empty($refId) || empty($sign)) {
            Log::warning('[DigiflazzWebhook] Missing ref_id or signature.');
            return $this->errorResponse('Invalid payload', 400);
        }

        if (!$this->digiflazzService->validateWebhookSignature((string) $refId, (string) $sign)) {
            Log::warning("[DigiflazzWebhook] Invalid MD5 signature for ref_id: {$refId}");
            return $this->errorResponse('Invalid signature', 403);
        }

        // ── 2. Find order by ref_id (= our order_code) ───────────────────
        $order = $this->orderRepo->findByCode((string) $refId);

        if (!$order) {
            Log::warning("[DigiflazzWebhook] Order not found for ref_id: {$refId}");
            return $this->errorResponse('Order not found', 404);
        }

        // ── 3. Idempotency: skip if topup already finalized ──────────────
        if (in_array($order->topup_status->value, ['completed', 'failed'])) {
            Log::info("[DigiflazzWebhook] Order #{$order->id} topup already {$order->topup_status->value}. Skipping.");
            return $this->successResponse([], 'Already processed');
        }

        // ── 4. Handle Digiflazz status ────────────────────────────────────
        if ($status === 'Sukses') {
            $this->orderService->markTopupCompleted($order->id, $sn ?? '');

            // Send success notification email
            \App\Jobs\SendEmailJob::dispatch($order->id, 'success');

            Log::info("[DigiflazzWebhook] Topup SUCCEEDED for order #{$order->id}. SN: {$sn}");

        } elseif ($status === 'Gagal') {
            $this->orderService->markTopupFailed($order->id, $msg);

            // Send failure notification email
            \App\Jobs\SendEmailJob::dispatch($order->id, 'failed');

            Log::error("[DigiflazzWebhook] Topup FAILED for order #{$order->id}. Reason: {$msg}");

        } else {
            // 'Pending' or other unknown statuses — log and ignore
            Log::info("[DigiflazzWebhook] Unhandled status '{$status}' for order #{$order->id}.");
        }

        // Always return 200 to Digiflazz to acknowledge receipt
        return $this->successResponse([], 'Webhook notification handled');
    }
}
