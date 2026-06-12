<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class MidtransService
{
    private string $serverKey;
    private string $snapUrl;
    private string $statusBaseUrl;

    public function __construct()
    {
        $this->serverKey     = config('midtrans.server_key');
        $this->snapUrl       = config('midtrans.api_base_url');
        $this->statusBaseUrl = config('midtrans.status_base_url');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Public API Methods
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Create a Midtrans Snap payment token.
     *
     * @param  string  $orderId        Unique order identifier (our order_code)
     * @param  float   $grossAmount    Total payment amount in IDR
     * @param  array   $customerDetails Customer info: name, email, phone
     * @param  array   $itemDetails    List of items: id, price, quantity, name
     * @return array   { token: string, redirect_url: string }
     */
    public function createSnapToken(
        string $orderId,
        float $grossAmount,
        array $customerDetails = [],
        array $itemDetails = []
    ): array {
        $payload = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'item_details'     => $itemDetails,
            'expiry'           => [
                'unit'     => 'minutes',
                'duration' => 60, // 60-minute payment window
            ],
        ];

        $response = $this->snapPost('/transactions', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Midtrans createSnapToken failed: ' . $response->body()
            );
        }

        return [
            'token'        => $response->json('token'),
            'redirect_url' => $response->json('redirect_url'),
        ];
    }

    /**
     * Check transaction status at Midtrans by order ID.
     *
     * @param  string  $orderId  Midtrans order ID (our order_code)
     * @return array   Midtrans transaction status response
     */
    public function checkTransactionStatus(string $orderId): array
    {
        $response = $this->statusGet("/{$orderId}/status");

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Midtrans checkTransactionStatus failed: ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Cancel a pending Midtrans transaction by order ID.
     */
    public function cancelTransaction(string $orderId): array
    {
        $response = $this->statusPost("/{$orderId}/cancel", []);

        if (!$response->successful()) {
            throw new \RuntimeException(
                'Midtrans cancelTransaction failed: ' . $response->body()
            );
        }

        return $response->json();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Webhook Signature Verification
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Validate Midtrans webhook notification signature.
     *
     * Midtrans signs with: SHA512(order_id + status_code + gross_amount + server_key)
     *
     * @param  array  $payload  Raw notification payload from Midtrans
     */
    public function validateWebhookSignature(array $payload): bool
    {
        $orderId     = $payload['order_id'] ?? '';
        $statusCode  = $payload['status_code'] ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';

        $expected = hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
        $incoming = $payload['signature_key'] ?? '';

        return hash_equals($expected, $incoming);
    }

    /**
     * Determine if a Midtrans notification means payment is settled.
     */
    public function isPaymentSettled(array $payload): bool
    {
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? 'accept';

        return ($transactionStatus === 'capture' && $fraudStatus === 'accept')
            || $transactionStatus === 'settlement';
    }

    /**
     * Determine if a Midtrans notification means payment is expired/cancelled.
     */
    public function isPaymentExpiredOrCancelled(array $payload): bool
    {
        $status = $payload['transaction_status'] ?? '';

        return in_array($status, ['expire', 'cancel', 'deny']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    private function authHeader(): string
    {
        return 'Basic ' . base64_encode($this->serverKey . ':');
    }

    private function snapPost(string $endpoint, array $payload): Response
    {
        return Http::timeout(30)
            ->withHeader('Authorization', $this->authHeader())
            ->withHeader('Content-Type', 'application/json')
            ->post($this->snapUrl . $endpoint, $payload);
    }

    private function statusGet(string $endpoint): Response
    {
        return Http::timeout(30)
            ->withHeader('Authorization', $this->authHeader())
            ->get($this->statusBaseUrl . $endpoint);
    }

    private function statusPost(string $endpoint, array $payload): Response
    {
        return Http::timeout(30)
            ->withHeader('Authorization', $this->authHeader())
            ->post($this->statusBaseUrl . $endpoint, $payload);
    }
}
