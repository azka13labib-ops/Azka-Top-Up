<?php

namespace App\Services;

use App\Contracts\Interfaces\data\OrderInterface;
use App\Contracts\Interfaces\data\OrderLogInterface;
use App\Contracts\Interfaces\data\ProductInterface;
use App\Enums\PaymentStatusEnum;
use App\Enums\TopupStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected OrderInterface    $orderRepo,
        protected OrderLogInterface $orderLogRepo,
        protected ProductInterface  $productRepo,
        protected MidtransService   $midtransService
    ) {}

    // ─────────────────────────────────────────────────────────────────────
    // Create Order (2.7 — now uses real Midtrans Snap token)
    // ─────────────────────────────────────────────────────────────────────

    public function createOrder(array $data, ?int $userId = null): Order
    {
        // 1. Fetch product & lock selling price
        $product = $this->productRepo->find($data['product_id']);

        if (!$product) {
            throw new \InvalidArgumentException('Product not found.');
        }

        if (!$product->is_active) {
            throw new \InvalidArgumentException('Product is currently unavailable.');
        }

        // 2. Generate unique order code (idempotency loop)
        $orderCode = $this->generateOrderCode();

        // 3. Create Midtrans Snap token
        $snapResult = $this->createMidtransSnapToken(
            orderCode: $orderCode,
            amount:    (float) $product->selling_price,
            data:      $data,
            product:   $product
        );

        // 4. Build order payload with price-locked selling_price
        $orderData = [
            'order_code'           => $orderCode,
            'user_id'              => $userId,
            'product_id'           => $product->id,
            'customer_no'          => $data['customer_no'],
            'zone_id'              => $data['zone_id'] ?? null,
            'email'                => $data['email'],
            'phone'                => $data['phone'] ?? null,
            'selling_price'        => $product->selling_price, // Price Lock ✓
            'payment_status'       => PaymentStatusEnum::PENDING,
            'topup_status'         => TopupStatusEnum::PENDING,
            'midtrans_snap_token'  => $snapResult['token'] ?? null,
        ];

        // 5. Persist to database
        $order = $this->orderRepo->createOrder($orderData);

        // 6. Log initial event
        $this->orderLogRepo->createLog(
            $order->id,
            'order_created',
            [
                'message'       => "Order {$orderCode} created.",
                'snap_token'    => $snapResult['token'] ?? null,
                'redirect_url'  => $snapResult['redirect_url'] ?? null,
            ]
        );

        return $order;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Query Methods
    // ─────────────────────────────────────────────────────────────────────

    public function getOrderStatus(string $orderCode): ?Order
    {
        return $this->orderRepo->findByCode($orderCode);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Status Update Helpers (called from Webhook handlers)
    // ─────────────────────────────────────────────────────────────────────

    public function markAsPaid(int $orderId): bool
    {
        $updated = $this->orderRepo->updateStatus($orderId, [
            'payment_status' => PaymentStatusEnum::PAID,
            'paid_at'        => now(),
        ]);

        if ($updated) {
            $this->orderLogRepo->createLog($orderId, 'payment_confirmed', [
                'message' => 'Payment confirmed by Midtrans webhook.',
            ]);
        }

        return $updated;
    }

    public function markPaymentExpired(int $orderId): bool
    {
        $updated = $this->orderRepo->updateStatus($orderId, [
            'payment_status' => PaymentStatusEnum::EXPIRED,
        ]);

        if ($updated) {
            $this->orderLogRepo->createLog($orderId, 'payment_expired', [
                'message' => 'Payment expired / cancelled.',
            ]);
        }

        return $updated;
    }

    public function markTopupProcessing(int $orderId): bool
    {
        return $this->orderRepo->updateStatus($orderId, [
            'topup_status' => TopupStatusEnum::PROCESSING,
        ]);
    }

    public function markTopupCompleted(int $orderId, string $sn): bool
    {
        $updated = $this->orderRepo->updateStatus($orderId, [
            'topup_status' => TopupStatusEnum::COMPLETED,
            'digiflazz_sn' => $sn,
            'completed_at' => now(),
        ]);

        if ($updated) {
            $this->orderLogRepo->createLog($orderId, 'topup_completed', [
                'message' => 'Top-up delivered successfully.',
                'sn'      => $sn,
            ]);
        }

        return $updated;
    }

    public function markTopupFailed(int $orderId, string $reason): bool
    {
        $updated = $this->orderRepo->updateStatus($orderId, [
            'topup_status'   => TopupStatusEnum::FAILED,
            'failure_reason' => $reason,
        ]);

        if ($updated) {
            $this->orderLogRepo->createLog($orderId, 'topup_failed', [
                'message' => 'Top-up failed.',
                'reason'  => $reason,
            ]);
        }

        return $updated;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    protected function generateOrderCode(): string
    {
        do {
            $code = 'AZKA-' . date('Ymd') . '-' . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while ($this->orderRepo->findByCode($code) !== null);

        return $code;
    }

    protected function createMidtransSnapToken(
        string $orderCode,
        float $amount,
        array $data,
        $product
    ): array {
        try {
            return $this->midtransService->createSnapToken(
                orderId: $orderCode,
                grossAmount: $amount,
                customerDetails: [
                    'first_name' => $data['email'] ?? 'Customer',
                    'email'      => $data['email'] ?? null,
                    'phone'      => $data['phone'] ?? null,
                ],
                itemDetails: [
                    [
                        'id'       => $product->digiflazz_sku,
                        'price'    => (int) $amount,
                        'quantity' => 1,
                        'name'     => $product->name,
                    ]
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('Midtrans Snap token creation failed, proceeding without token.', [
                'order_code' => $orderCode,
                'error'      => $e->getMessage(),
            ]);

            // Fallback: return empty so order can still be created
            return ['token' => null, 'redirect_url' => null];
        }
    }
}
