<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface ArticleRepositoryInterface
{
    public function upsertMany(array $articles): void;

    public function search(
        ?string $query,
        array $filters = [],
        int $perPage = 15,
        int $page = 1,
        ?string $sortBy = null,
        string $sortDir = 'desc'
    ): LengthAwarePaginator;

    public function find(int $id): ?Model;
}
