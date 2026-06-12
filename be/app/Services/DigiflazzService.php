<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Str;

class DigiflazzService
{
    private string $baseUrl;
    private string $username;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl  = config('digiflazz.base_url');
        $this->username = config('digiflazz.username');
        $this->apiKey   = config('digiflazz.api_key');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Public API Methods
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Fetch the full price list from Digiflazz.
     * Returns an array of products with: sku, product_name, price, etc.
     */
    public function fetchPricelist(string $cmd = 'prepaid'): array
    {
        $payload = [
            'cmd'      => $cmd,
            'username' => $this->username,
            'sign'     => $this->generateSign('pricelist'), // MD5(username + api_key + "pricelist")
        ];

        $response = $this->post('/price-list', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Digiflazz fetchPricelist failed: ' . $response->body());
        }

        return $response->json('data', []);
    }

    /**
     * Create a top-up transaction at Digiflazz.
     *
     * @param  string  $sku         Digiflazz product SKU
     * @param  string  $customerNo  Target game user ID / phone
     * @param  string  $refId       Unique reference ID (our order_code)
     * @return array   Raw Digiflazz response data
     */
    public function createTransaction(string $sku, string $customerNo, string $refId): array
    {
        $payload = [
            'username'    => $this->username,
            'buyer_sku_code' => $sku,
            'customer_no' => $customerNo,
            'ref_id'      => $refId,
            'sign'        => $this->generateSign($refId),
            'testing'     => !app()->isProduction(), // use testing mode on non-prod
        ];

        $response = $this->post('/transaction', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Digiflazz createTransaction failed: ' . $response->body());
        }

        $data = $response->json('data');

        if (empty($data)) {
            throw new \RuntimeException('Digiflazz returned empty transaction data.');
        }

        return $data;
    }

    /**
     * Check current account balance at Digiflazz.
     */
    public function checkBalance(): array
    {
        $payload = [
            'cmd'      => 'deposit',
            'username' => $this->username,
            'sign'     => $this->generateSign('depo'), // MD5(username + api_key + "depo") — sesuai dok Digiflazz
        ];

        $response = $this->post('/cek-saldo', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Digiflazz checkBalance failed: ' . $response->body());
        }

        return $response->json('data', []);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Webhook Signature Verification
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Validate incoming Digiflazz webhook signature.
     * Digiflazz signs with: MD5(username + api_key + ref_id)
     */
    public function validateWebhookSignature(string $refId, string $incomingSign): bool
    {
        $expected = md5($this->username . $this->apiKey . $refId);

        return hash_equals($expected, $incomingSign);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Private Helpers
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Generate request signature.
     * MD5(username + api_key + ref_id)
     */
    private function generateSign(string $refId): string
    {
        return md5($this->username . $this->apiKey . $refId);
    }

    /**
     * Execute a POST request to the Digiflazz API.
     */
    private function post(string $endpoint, array $payload): Response
    {
        return Http::timeout(30)
            ->retry(2, 500)
            ->post($this->baseUrl . $endpoint, $payload);
    }
}
