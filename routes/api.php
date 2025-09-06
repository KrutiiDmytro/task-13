<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\CategoryController;

Route::prefix('v1')->group(function () {
    Route::apiResource('posts', \App\Http\Controllers\Api\V1\PostController::class);
    Route::apiResource('categories', \App\Http\Controllers\Api\V1\CategoryController::class);
});