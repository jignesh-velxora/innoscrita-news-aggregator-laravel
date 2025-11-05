<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ArticleController;

Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{id}', [ArticleController::class, 'show']);
Route::post('/articles/refresh', [ArticleController::class, 'refresh']);
Route::get('/articles/refresh', [ArticleController::class, 'refresh']);
