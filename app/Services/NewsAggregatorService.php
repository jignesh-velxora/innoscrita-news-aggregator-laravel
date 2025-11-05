<?php

namespace App\Services;

use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Services\Contracts\NewsProviderInterface;

class NewsAggregatorService
{
    /** @var array<int,NewsProviderInterface> */
    private array $providers;

    public function __construct(private ArticleRepositoryInterface $articles, NewsProviderInterface ...$providers)
    {
        $this->providers = $providers;
    }

    public function aggregate(array $options = []): int
    {
        $total = 0;
        foreach ($this->providers as $provider) {
            $items = $provider->fetch($options);
            if (!empty($items)) {
                $this->articles->upsertMany($items);
                $total += count($items);
            }
        }
        return $total;
    }
}
