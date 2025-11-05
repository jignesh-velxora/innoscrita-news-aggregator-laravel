<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_articles(): void
    {
        Article::factory()->count(3)->create();

        $res = $this->getJson('/api/articles');
        $res->assertOk();
        $res->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'source']
            ]
        ]);
    }

    public function test_refresh_triggers_aggregation(): void
    {
        Http::fake();
        $res = $this->postJson('/api/articles/refresh', ['q' => 'science']);
        $res->assertOk()->assertJsonStructure(['updated']);
    }
}
