<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            // Get authenticated user ID if Sanctum token is present
            $userId = auth('sanctum')->id();

            $order = $this->orderService->createOrder($request->validated(), $userId);
            
            // Load relations to return full resource
            $order->load(['product.game', 'logs']);

            return $this->successResponse(
                new OrderResource($order),
                'Order created successfully',
                201
            );
        } catch (\InvalidArgumentException $e) {
            return $this->errorResponse($e->getMessage(), 400);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create order. Please try again.', 500);
        }
    }

    public function show(string $orderCode): JsonResponse
    {
        $order = $this->orderService->getOrderStatus($orderCode);

        if (!$order) {
            return $this->errorResponse('Order not found', 404);
        }

        // Load relations for timeline and details
        $order->load(['product.game', 'logs']);

        return $this->successResponse(
            new OrderResource($order),
            'Order status retrieved successfully'
        );
    }
}
