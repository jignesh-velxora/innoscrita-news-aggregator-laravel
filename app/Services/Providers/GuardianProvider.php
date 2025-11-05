<?php

namespace App\Services\Providers;

use App\Services\Contracts\NewsProviderInterface;
use Illuminate\Support\Facades\Http;

class GuardianProvider implements NewsProviderInterface
{
    public function __construct(private readonly ?string $apiKey = null)
    {
        $this->apiKey ??= (string) config('services.guardian.key');
    }

    public function getName(): string
    {
        return 'guardian';
    }

    public function fetch(array $options = []): array
    {
        $params = array_filter([
            'api-key' => $this->apiKey,
            'q' => $options['q'] ?? null,
            'from-date' => $options['from'] ?? null,
            'to-date' => $options['to'] ?? null,
            'page-size' => $options['pageSize'] ?? 100,
            'show-fields' => 'trailText,byline,thumbnail,body',
        ]);

        $response = Http::get('https://content.guardianapis.com/search', $params);

        if (!$response->ok()) {
            return [];
        }

        $articles = [];
        foreach ($response->json('response.results', []) as $item) {
            $fields = $item['fields'] ?? [];
            $articles[] = [
                'source' => $this->getName(),
                'external_id' => data_get($item, 'id'),
                'title' => data_get($item, 'webTitle'),
                'author' => data_get($fields, 'byline'),
                'description' => data_get($fields, 'trailText'),
                'url' => data_get($item, 'webUrl'),
                'image_url' => data_get($fields, 'thumbnail'),
                'category' => data_get($item, 'sectionName'),
                'published_at' => data_get($item, 'webPublicationDate'),
                'content' => data_get($fields, 'body'),
            ];
        }

        return $articles;
    }
}
