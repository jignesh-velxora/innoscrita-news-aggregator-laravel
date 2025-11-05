<?php

namespace App\Console\Commands;

use App\Services\NewsAggregatorService;
use Illuminate\Console\Command;

class RefreshArticles extends Command
{
    protected $signature = 'news:refresh {--q=} {--from=} {--to=} {--category=}';
    protected $description = 'Fetch and store latest articles from configured providers';

    public function handle(NewsAggregatorService $aggregator): int
    {
        $options = [
            'q' => $this->option('q'),
            'from' => $this->option('from'),
            'to' => $this->option('to'),
            'category' => $this->option('category'),
        ];
        $count = $aggregator->aggregate($options);
        $this->info("Stored/updated {$count} articles.");
        return self::SUCCESS;
    }
}
