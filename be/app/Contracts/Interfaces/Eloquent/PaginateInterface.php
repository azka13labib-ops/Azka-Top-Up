<?php

namespace App\Contracts\Interfaces\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PaginateInterface
{
    /**
     * Paginate the Eloquent query.
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null): LengthAwarePaginator;
}
