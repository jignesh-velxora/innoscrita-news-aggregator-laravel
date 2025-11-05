<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'external_id',
        'title',
        'author',
        'description',
        'url',
        'image_url',
        'category',
        'published_at',
        'content',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
