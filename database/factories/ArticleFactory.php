<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'source' => fake()->randomElement(['newsapi','guardian','nyt']),
            'external_id' => fake()->uuid(),
            'title' => fake()->sentence(),
            'author' => fake()->name(),
            'description' => fake()->paragraph(),
            'url' => fake()->unique()->url(),
            'image_url' => fake()->imageUrl(),
            'category' => fake()->randomElement(['World','Business','Tech','Science']),
            'published_at' => now()->subDays(rand(0, 10)),
            'content' => fake()->paragraphs(3, true),
        ];
    }
}
