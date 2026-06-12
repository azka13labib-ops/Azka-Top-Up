<?php

namespace App\Contracts\Interfaces\data;

use App\Contracts\Interfaces\Eloquent\PaginateInterface;
use App\Contracts\Interfaces\Eloquent\SearchInterface;
use App\Models\Game;
use Illuminate\Database\Eloquent\Collection;

interface GameInterface extends PaginateInterface, SearchInterface
{
    public function allActive(): Collection;
    public function findBySlug(string $slug): ?Game;
}
