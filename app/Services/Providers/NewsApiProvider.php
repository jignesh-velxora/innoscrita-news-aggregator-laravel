<?php

namespace App\Services\Providers;

use App\Services\Contracts\NewsProviderInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class NewsApiProvider implements NewsProviderInterface
{
    public function __construct(private readonly ?string $apiKey = null)
    {
        $this->apiKey ??= (string) config('services.newsapi.key');
    }

    public function getName(): string
    {
        return 'newsapi';
    }

    public function fetch(array $options = []): array
    {
        $params = array_filter([
            'apiKey' => $this->apiKey,
            'q' => $options['q'] ?? null,
            'from' => isset($options['from']) ? Carbon::parse($options['from'])->toDateString() : null,
            'to' => isset($options['to']) ? Carbon::parse($options['to'])->toDateString() : null,
            'language' => $options['language'] ?? 'en',
            'pageSize' => $options['pageSize'] ?? 100,
            'sortBy' => 'publishedAt',
        ]);

        $response = Http::get('https://newsapi.org/v2/everything', $params);

        if (!$response->ok()) {
            return [];
        }

        $articles = [];
        foreach ($response->json('articles', []) as $item) {
            $articles[] = [
                'source' => $this->getName(),
                'external_id' => data_get($item, 'url'),
                'title' => data_get($item, 'title'),
                'author' => data_get($item, 'author'),
                'description' => data_get($item, 'description'),
                'url' => data_get($item, 'url'),
                'image_url' => data_get($item, 'urlToImage'),
                'category' => $options['category'] ?? null,
                'published_at' => data_get($item, 'publishedAt'),
                'content' => data_get($item, 'content'),
            ];
        }

        return $articles;
    }
}
