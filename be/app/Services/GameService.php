<?php

namespace App\Services;

use App\Contracts\Interfaces\data\GameInterface;
use Illuminate\Database\Eloquent\Collection;

class GameService
{
    public function __construct(
        protected GameInterface $gameRepo
    ) {}

    public function getActiveGames(): Collection
    {
        return $this->gameRepo->allActive();
    }

    public function getGameDetails(string $slug)
    {
        return $this->gameRepo->findBySlug($slug);
    }
}
