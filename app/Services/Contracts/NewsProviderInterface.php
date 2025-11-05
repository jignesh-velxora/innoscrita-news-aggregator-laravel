<?php

namespace App\Services\Contracts;

interface NewsProviderInterface
{
    /**
     * Fetch latest articles from the provider.
     * Must return an array of normalised article arrays with keys matching Article::$fillable
     *
     * @param array $options
     * @return array<int,array<string,mixed>>
     */
    public function fetch(array $options = []): array;

    /**
     * Returns the provider unique name e.g. 'newsapi', 'guardian', 'nyt'.
     */
    public function getName(): string;
}
