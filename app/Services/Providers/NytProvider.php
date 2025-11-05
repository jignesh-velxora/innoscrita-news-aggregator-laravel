<?php

namespace App\Services\Providers;

use App\Services\Contracts\NewsProviderInterface;
use Illuminate\Support\Facades\Http;

class NytProvider implements NewsProviderInterface
{
    public function __construct(private readonly ?string $apiKey = null)
    {
        $this->apiKey ??= (string) config('services.nyt.key');
    }

    public function getName(): string
    {
        return 'nyt';
    }

    public function fetch(array $options = []): array
    {
        $params = array_filter([
            'api-key' => $this->apiKey,
            'q' => $options['q'] ?? null,
            'begin_date' => isset($options['from']) ? date('Ymd', strtotime($options['from'])) : null,
            'end_date' => isset($options['to']) ? date('Ymd', strtotime($options['to'])) : null,
            'page' => 0,
            'sort' => 'newest',
        ]);

        $response = Http::get('https://api.nytimes.com/svc/search/v2/articlesearch.json', $params);
        if (!$response->ok()) {
            return [];
        }

        $articles = [];
        foreach ($response->json('response.docs', []) as $item) {
            $articles[] = [
                'source' => $this->getName(),
                'external_id' => data_get($item, '_id'),
                'title' => data_get($item, 'headline.main'),
                'author' => data_get($item, 'byline.original'),
                'description' => data_get($item, 'abstract'),
                'url' => data_get($item, 'web_url'),
                'image_url' => null,
                'category' => data_get($item, 'section_name'),
                'published_at' => data_get($item, 'pub_date'),
                'content' => null,
            ];
        }

        return $articles;
    }
}
