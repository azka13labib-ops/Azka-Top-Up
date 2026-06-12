<?php

namespace App\Repositories;

use App\Contracts\Interfaces\data\GameInterface;
use App\Models\Game;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class GameRepository implements GameInterface
{
    public function allActive(): Collection
    {
        return Game::where('is_active', true)->orderBy('sort_order', 'asc')->get();
    }

    public function findBySlug(string $slug): ?Game
    {
        return Game::where('slug', $slug)->first();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator
    {
        return Game::orderBy('sort_order', 'asc')->paginate($perPage, $columns, $pageName, $page);
    }

    public function search(string $query, array $columns)
    {
        return Game::where(function ($q) use ($query, $columns) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$query}%");
            }
        });
    }
}
