<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @return array<string,mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'author' => $this->author,
            // Frontend tolerates `source` or `source_name`; we expose `source`
            'source' => $this->source,
            'url' => $this->url,
            'description' => $this->description ?? $this->content,
            'content' => $this->content,
            'image_url' => $this->image_url,
            'category' => $this->category,
            // publish date as ISO string; frontend also tolerates publishedAt
            'published_at' => optional($this->published_at)->toIso8601String(),
        ];
    }
}
