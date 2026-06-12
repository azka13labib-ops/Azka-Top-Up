<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\ProductResource;
use App\Services\GameService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function __construct(
        protected GameService $gameService,
        protected ProductService $productService
    ) {}

    public function index(): JsonResponse
    {
        $games = $this->gameService->getActiveGames();
        return $this->successResponse(
            GameResource::collection($games),
            'Games retrieved successfully'
        );
    }

    public function show(string $slug): JsonResponse
    {
        $game = $this->gameService->getGameDetails($slug);
        if (!$game) {
            return $this->errorResponse('Game not found', 404);
        }

        return $this->successResponse(
            new GameResource($game),
            'Game retrieved successfully'
        );
    }

    public function products(string $slug): JsonResponse
    {
        $game = $this->gameService->getGameDetails($slug);
        if (!$game) {
            return $this->errorResponse('Game not found', 404);
        }

        $products = $this->productService->getProductsForGame($game->id);
        return $this->successResponse(
            ProductResource::collection($products),
            'Products retrieved successfully'
        );
    }
}
