<?php

namespace App\Contracts\Interfaces\data;

use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductInterface extends PaginateInterface, SearchInterface
{
    public function find(int $id): ?Product;
    public function getActiveByGameId(int $gameId): Collection;
    public function findBySku(string $sku): ?Product;
    public function updateOrCreateFromDigiflazz(array $attributes): Product;
    public function updateBulkStatus(array $ids, bool $isActive): bool;
}
