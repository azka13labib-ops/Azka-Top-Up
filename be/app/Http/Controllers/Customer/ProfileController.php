<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Contracts\Interfaces\data\OrderInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        protected OrderInterface $orderRepo
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at?->toIso8601String(),
        ], 'Profile details retrieved successfully');
    }

    public function orders(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $perPage = $request->query('per_page', 10);
        
        $orders = $this->orderRepo->paginateByUserId($userId, (int) $perPage);
        
        return $this->successResponse(
            OrderResource::collection($orders)->response()->getData(true),
            'Order history retrieved successfully'
        );
    }
}
