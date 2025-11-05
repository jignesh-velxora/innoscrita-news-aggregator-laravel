<?php

namespace App\Providers;

use App\Repositories\ArticleRepository;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Services\NewsAggregatorService;
use App\Services\Providers\GuardianProvider;
use App\Services\Providers\NewsApiProvider;
use App\Services\Providers\NytProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);

        $this->app->singleton(NewsApiProvider::class, function ($app) {
            return new NewsApiProvider(config('services.newsapi.key'));
        });
        $this->app->singleton(GuardianProvider::class, function ($app) {
            return new GuardianProvider(config('services.guardian.key'));
        });
        $this->app->singleton(NytProvider::class, function ($app) {
            return new NytProvider(config('services.nyt.key'));
        });

        $this->app->singleton(NewsAggregatorService::class, function ($app) {
            return new NewsAggregatorService(
                $app->make(ArticleRepositoryInterface::class),
                $app->make(NewsApiProvider::class),
                $app->make(GuardianProvider::class),
                $app->make(NytProvider::class),
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
