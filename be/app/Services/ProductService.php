<?php

namespace App\Services;

use App\Contracts\Interfaces\data\ProductInterface;

class ProductService
{
    public function __construct(
        protected ProductInterface $productRepo
    ) {}

    public function getProductsForGame(int $gameId)
    {
        return $this->productRepo->getActiveByGameId($gameId);
    }
}
