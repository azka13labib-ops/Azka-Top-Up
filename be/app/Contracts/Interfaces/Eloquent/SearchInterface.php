<?php

namespace App\Contracts\Interfaces\Eloquent;

interface SearchInterface
{
    /**
     * Apply a search query filter to the Eloquent query.
     */
    public function search(string $query, array $columns);
}
