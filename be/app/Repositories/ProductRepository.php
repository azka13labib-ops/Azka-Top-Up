<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\ProductInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductInterface
{
    public function find(int $id): ?Product
    {
        return Product::find($id);
    }

    public function getActiveByGameId(int $gameId): Collection
    {
        return Product::where('game_id', $gameId)->where('is_active', true)->orderBy('selling_price', 'asc')->get();
    }

    public function findBySku(string $sku): ?Product
    {
        return Product::where('digiflazz_sku', $sku)->first();
    }

    public function updateOrCreateFromDigiflazz(array $attributes): Product
    {
        return Product::updateOrCreate(
            ['digiflazz_sku' => $attributes['digiflazz_sku']],
            $attributes
        );
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Product::with('game')->paginate($perPage, $columns, $pageName, $page);
    }

    public function updateBulkStatus(array $ids, bool $isActive): bool
    {
        return Product::whereIn('id', $ids)->update(['is_active' => $isActive]) > 0;
    }

    public function search(string $query, array $columns)
    {
        return Product::where(function ($q) use ($query, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$query}%");
            }
        });
    }
}
