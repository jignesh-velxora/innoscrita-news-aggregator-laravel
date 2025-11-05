<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function upsertMany(array $articles): void
    {
        foreach ($articles as $data) {
            Article::updateOrCreate(
                ['url' => $data['url']],
                $data
            );
        }
    }

    public function search(
        ?string $query,
        array $filters = [],
        int $perPage = 15,
        int $page = 1,
        ?string $sortBy = null,
        string $sortDir = 'desc'
    ): LengthAwarePaginator {
        $q = Article::query();

        if ($query) {
            $q->where(function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            });
        }

        if (!empty($filters['source'])) {
            $q->whereIn('source', (array) $filters['source']);
        }
        if (!empty($filters['category'])) {
            $q->whereIn('category', (array) $filters['category']);
        }
        if (!empty($filters['author'])) {
            $q->whereIn('author', (array) $filters['author']);
        }
        if (!empty($filters['from'])) {
            $q->where('published_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $q->where('published_at', '<=', $filters['to']);
        }

        // Sorting whitelist mapping to columns
        $sortable = [
            'title' => 'title',
            'source' => 'source',
            'author' => 'author',
            'published_at' => 'published_at',
        ];
        if ($sortBy && isset($sortable[$sortBy])) {
            $dir = strtolower($sortDir) === 'asc' ? 'asc' : 'desc';
            $q->orderBy($sortable[$sortBy], $dir);
        } else {
            $q->orderByDesc('published_at');
        }

        return $q->paginate(perPage: $perPage, page: $page);
    }

    public function find(int $id): ?Model
    {
        return Article::find($id);
    }
}
