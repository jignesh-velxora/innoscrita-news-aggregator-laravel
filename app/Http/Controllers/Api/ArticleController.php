<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Services\NewsAggregatorService;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(private ArticleRepositoryInterface $articles, private NewsAggregatorService $aggregator)
    {
    }

    // GET /api/articles
    public function index(Request $request)
    {
        // Frontend expects: page, per_page, search, sort_by, sort_dir
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));
        $search = $request->query('search');
        $sortBy = $request->query('sort_by');
        $sortDir = $request->query('sort_dir', 'desc');

        $filters = [];
        // Optional extra filters if backend adds them later
        foreach (['source', 'category', 'author', 'from', 'to'] as $k) {
            if ($request->has($k)) {
                $filters[$k] = $request->query($k);
            }
        }

        $result = $this->articles->search($search, $filters, $perPage, $page, $sortBy, $sortDir);

        // Build explicit response to avoid duplicate pagination keys
        $items = ArticleResource::collection($result->items());
        return response()->json([
            'data' => $items,
            'meta' => [
                'total' => $result->total(),
                'per_page' => $result->perPage(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
            ],
        ]);
    }

    // GET /api/articles/{id}
    public function show(int $id)
    {
        $article = $this->articles->find($id);
        abort_if(!$article, 404);
        return new ArticleResource($article);
    }

    // POST or GET /api/articles/refresh
    public function refresh(Request $request)
    {
        $options = $request->only(['q', 'from', 'to', 'category']);
        $count = $this->aggregator->aggregate($options);
        return response()->json(['updated' => $count]);
    }
}
