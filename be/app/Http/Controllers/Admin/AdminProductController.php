<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Contracts\Interfaces\data\ProductInterface;
use App\Services\DigiflazzService;
use App\Enums\MarkupTypeEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminProductController extends Controller
{
    public function __construct(
        protected ProductInterface $productRepo,
        protected DigiflazzService $digiflazzService
    ) {}

    /**
     * GET /admin/products
     * Paginated product listing for admin panel.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage  = $request->query('per_page', 15);
        $products = $this->productRepo->paginate((int) $perPage);

        return $this->successResponse(
            ProductResource::collection($products)->response()->getData(true),
            'Products list retrieved successfully'
        );
    }

    /**
     * PUT /admin/products/{id}
     * Update a single product's markup and active status.
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        $product = $this->productRepo->find($id);

        if (!$product) {
            return $this->errorResponse('Product not found', 404);
        }

        $validated   = $request->validated();
        $basePrice   = (float) $product->base_price;
        $markupVal   = (float) $validated['markup_value'];

        $sellingPrice = match ($validated['markup_type']) {
            'percent' => $basePrice + ($basePrice * ($markupVal / 100)),
            default   => $basePrice + $markupVal, // flat
        };

        $product->update([
            'markup_type'   => $validated['markup_type'],
            'markup_value'  => $markupVal,
            'selling_price' => round($sellingPrice, 2),
            'is_active'     => $validated['is_active'],
        ]);

        return $this->successResponse(
            new ProductResource($product->fresh()),
            'Product updated successfully'
        );
    }

    /**
     * PUT /admin/products/bulk-status
     * Toggle is_active for multiple products at once.
     */
    public function bulkStatus(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids'   => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'is_active'     => 'required|boolean',
        ]);

        $this->productRepo->updateBulkStatus(
            $request->input('product_ids'),
            $request->boolean('is_active')
        );

        return $this->successResponse([], 'Products status updated successfully');
    }

    /**
     * POST /admin/products/sync
     * Fetch pricelist from Digiflazz and upsert into local DB.
     * Calculates markup using a global default flat markup from SiteSettings.
     */
    public function sync(): JsonResponse
    {
        try {
            $pricelist = $this->digiflazzService->fetchPricelist();

            $stats = ['new' => 0, 'updated' => 0, 'unchanged' => 0, 'errors' => 0];

            foreach ($pricelist as $item) {
                try {
                    $sku       = $item['buyer_sku_code'] ?? null;
                    $basePrice = (float) ($item['price'] ?? 0);
                    $name      = $item['product_name'] ?? null;
                    $category  = $item['category'] ?? null;

                    if (empty($sku) || $basePrice <= 0) {
                        continue;
                    }

                    // Check if product already exists
                    $existing = $this->productRepo->findBySku($sku);

                    // Default markup: flat 500 IDR per item
                    $defaultMarkup   = 500;
                    $newSellingPrice = $basePrice + $defaultMarkup;

                    $attributes = [
                        'digiflazz_sku' => $sku,
                        'name'          => $name,
                        'base_price'    => $basePrice,
                        'markup_type'   => MarkupTypeEnum::FLAT,
                        'markup_value'  => $defaultMarkup,
                        'is_active'     => true,
                    ];

                    if ($existing) {
                        // Keep existing selling_price & markup (admin may have customized them)
                        // Only update base_price and name
                        $hasChanged = $existing->base_price != $basePrice || $existing->name !== $name;

                        if ($hasChanged) {
                            $existing->update([
                                'base_price' => $basePrice,
                                'name'       => $name,
                            ]);
                            $stats['updated']++;
                        } else {
                            $stats['unchanged']++;
                        }
                    } else {
                        $attributes['selling_price'] = $newSellingPrice;
                        $this->productRepo->updateOrCreateFromDigiflazz($attributes);
                        $stats['new']++;
                    }

                } catch (\Throwable $e) {
                    Log::warning('[ProductSync] Failed to upsert SKU: ' . ($item['buyer_sku_code'] ?? '?'), [
                        'error' => $e->getMessage(),
                    ]);
                    $stats['errors']++;
                }
            }

            return $this->successResponse($stats, 'Product sync completed');

        } catch (\Throwable $e) {
            Log::error('[ProductSync] Digiflazz fetchPricelist failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to sync from Digiflazz: ' . $e->getMessage(), 502);
        }
    }
}
