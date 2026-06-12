<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Contracts\Interfaces\data\OrderInterface;
use App\Jobs\ExecuteTopupJob;
use App\Services\DigiflazzService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    public function __construct(
        protected OrderInterface   $orderRepo,
        protected DigiflazzService $digiflazzService
    ) {}

    /**
     * GET /admin/orders
     * Paginated order list with optional filters.
     * Query params: payment_status, topup_status, game_id, per_page
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['payment_status', 'topup_status', 'game_id']);
        $perPage = (int) $request->query('per_page', 15);

        $orders = $this->orderRepo->paginateFiltered($filters, $perPage);

        return $this->successResponse(
            OrderResource::collection($orders)->response()->getData(true),
            'Orders list retrieved successfully'
        );
    }

    /**
     * GET /admin/orders/{id}
     * Get a single order with full detail.
     */
    public function show(int $id): JsonResponse
    {
        $order = $this->orderRepo->find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        $order->load(['product.game', 'logs']);

        return $this->successResponse(
            new OrderResource($order),
            'Order retrieved successfully'
        );
    }

    /**
     * POST /admin/orders/{id}/retry
     * Manually re-dispatch top-up for a failed/stuck order.
     */
    public function retry(int $id): JsonResponse
    {
        $order = $this->orderRepo->find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        // Guard: must be paid before we can retry topup
        if ($order->payment_status->value !== 'paid') {
            return $this->errorResponse('Cannot retry topup: payment is not confirmed.', 422);
        }

        // Guard: don't retry completed orders
        if ($order->topup_status->value === 'completed') {
            return $this->errorResponse('Order topup is already completed.', 422);
        }

        // Reset digiflazz_ref_id so ExecuteTopupJob can re-submit without idempotency block
        $this->orderRepo->updateStatus($order->id, [
            'digiflazz_ref_id' => null,
            'topup_status'     => \App\Enums\TopupStatusEnum::PENDING,
            'failure_reason'   => null,
        ]);

        ExecuteTopupJob::dispatch($order->id);

        Log::info("[AdminOrderController] Manual retry dispatched for order #{$id}.");

        return $this->successResponse([], 'Order topup retry dispatched successfully');
    }

    /**
     * POST /admin/orders/{id}/flag-refund
     * Flag an order for manual refund processing.
     */
    public function flagRefund(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'refund_notes' => 'required|string|max:1000',
        ]);

        $order = $this->orderRepo->find($id);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        if (!empty($order->refund_flagged_at)) {
            return $this->errorResponse('Order is already flagged for refund.', 422);
        }

        $this->orderRepo->updateStatus($order->id, [
            'refund_flagged_at' => now(),
            'refund_notes'      => $request->input('refund_notes'),
        ]);

        return $this->successResponse(
            new OrderResource($order->fresh()),
            'Order flagged for refund successfully'
        );
    }

    /**
     * GET /admin/balance
     * Check current Digiflazz account balance.
     */
    public function balance(): JsonResponse
    {
        try {
            $balanceData = $this->digiflazzService->checkBalance();

            return $this->successResponse([
                'balance'   => $balanceData['deposit'] ?? 0,
                'raw'       => $balanceData,
            ], 'Balance retrieved successfully');

        } catch (\Throwable $e) {
            Log::error('[AdminOrderController] checkBalance failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve balance from Digiflazz.', 502);
        }
    }
}
